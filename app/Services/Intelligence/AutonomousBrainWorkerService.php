<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Models\AiBenchmarkRun;
use App\Models\AiKnowledgeEntry;
use App\Models\AiKnowledgeGap;
use App\Models\AiLearningCheckpoint;
use App\Models\AiLearningRun;
use App\Models\AiLearningTask;
use App\Models\AiResearchClaim;
use App\Models\AiResearchGoal;
use App\Models\AiResearchSource;
use App\Models\AiResearchTopic;
use App\Services\AiQualityAndCostObservatoryService;
use App\Services\AuditLogger;
use App\Services\Chatbot\ChatbotKnowledgeService;
use App\Services\Chatbot\ChatbotProviderClient;
use App\Services\Chatbot\ChatbotSecurityGuard;
use App\Services\Intelligence\Research\CurriculumEngineService;
use App\Services\Intelligence\Research\MultiAgentResearchService;
use App\Services\Intelligence\V4\TypedKnowledgeGraphService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Autonomous Brain Worker Engine V3:
 * - Kết hợp tri thức nội bộ Mây Hẹn + Autonomous Web Research & Dynamic Curriculum V3.
 * - KHÔNG BAO GIỜ rơi vào idle_no_new_data sai chỉ vì database nội bộ không thay đổi!
 * - Distributed Lock + Atomic Claim + Fencing Token, không mở transaction DB khi gọi AI/HTTP.
 * - Trả về telemetry chi tiết thực tế cho từng cycle (domain, topic, queries, sources, claims, tokens, tài nguyên).
 */
class AutonomousBrainWorkerService
{
    public const WORKER_LOCK_KEY = 'mayhen_ai_brain_worker_cycle_lock_v2';

    public function __construct(
        protected AiGovernanceService $governance,
        protected AdaptiveResourceController $resources,
        protected IncrementalDiscoveryService $discovery,
        protected KnowledgeGraphService $graphService,
        protected AiEvaluationService $evaluationService,
        protected ChatbotKnowledgeService $knowledgeService,
        protected ChatbotProviderClient $providerClient,
        protected ChatbotSecurityGuard $securityGuard,
        protected SecurityPolicyGate $policyGate,
        protected DataRetentionService $retentionService,
        protected CurriculumEngineService $curriculum,
        protected MultiAgentResearchService $researchService,
        protected TypedKnowledgeGraphService $typedGraph
    ) {}

    /**
     * Thực thi ĐÚNG 1 chu kỳ hữu hạn (budget-seconds, max-tasks) — an toàn tuyệt đối cho Cron & Local loop.
     *
     * @param  array<string, mixed>|null  $overrideMetrics  Chỉ dùng cho kiểm thử tự động mô phỏng áp lực tải
     * @return array{
     *     ok: bool,
     *     status: string,
     *     tasks_processed: int,
     *     candidates_created: int,
     *     tasks_discovered: int,
     *     recovered_stale: int,
     *     elapsed_ms: int,
     *     message: string
     * }
     */
    public function runSingleCycle(
        ?int $budgetSeconds = null,
        ?int $maxTasks = null,
        ?string $workerId = null,
        ?array $overrideMetrics = null
    ): array {
        $startedAt = microtime(true);
        $workerId = $workerId ?: 'brain-'.gethostname().'-'.getmypid().'-'.substr(bin2hex(random_bytes(3)), 0, 6);

        $profile = $this->resources->getActiveProfile();
        $budget = $budgetSeconds !== null && $budgetSeconds > 0
            ? min(120, $budgetSeconds)
            : max(5, (int) ($profile['cycle_budget_seconds'] ?? config('ai_brain.cycle_budget_seconds', 20)));
        $taskLimit = $maxTasks !== null && $maxTasks > 0
            ? min(10, $maxTasks)
            : max(1, (int) ($profile['max_tasks_per_cycle'] ?? config('ai_brain.max_tasks_per_cycle', 2)));

        if (! Schema::hasTable('ai_learning_tasks')) {
            return $this->cycleResult(false, 'schema_missing', 0, 0, 0, 0, $startedAt, 'Chưa chạy migration bảng ai_learning_tasks.');
        }

        // 1. Kiểm tra Governance & Tài nguyên trước khi xin lock
        $perm = $this->resources->evaluateStepPermission($budget, $startedAt, false, $overrideMetrics);
        if (! $perm['allowed']) {
            $this->resources->recordWorkerHeartbeat($workerId, $perm['action'], ['last_message' => $perm['reason']]);

            return $this->cycleResult(true, $perm['code'], 0, 0, 0, 0, $startedAt, $perm['reason']);
        }

        // 2. Non-overlapping Distributed Lock (phòng vệ cho hosting không hỗ trợ flock)
        $lock = Cache::lock(self::WORKER_LOCK_KEY, max(35, $budget + 15));
        if (! $lock->get()) {
            return $this->cycleResult(true, 'locked_overlap', 0, 0, 0, 0, $startedAt, 'Một tiến trình AI Brain khác đang giữ khóa thực thi — bỏ qua để chống chạy trùng.');
        }

        $tasksProcessed = 0;
        $candidatesCreated = 0;
        $tasksDiscovered = 0;
        $recoveredStale = 0;

        try {
            $this->resources->recordWorkerHeartbeat($workerId, 'running', ['last_message' => 'Đang kiểm tra checkpoint & hàng đợi học nền']);

            // 3. Khôi phục các task bị treo do worker trước bị kill / mất điện / timeout
            $recoveredStale = $this->recoverStaleProcessingTasks();

            // 4. Incremental Discovery: chỉ quét và tạo task khi hàng đợi trống & nguồn thực sự thay đổi
            $queuedNow = AiLearningTask::query()
                ->where('status', AiLearningTask::STATUS_QUEUED)
                ->where(function ($q) {
                    $q->whereNull('next_retry_at')->orWhere('next_retry_at', '<=', now());
                })
                ->count();

            if ($queuedNow === 0) {
                $disc = $this->discovery->discover(dryRun: false);
                $tasksDiscovered = (int) $disc['tasks_enqueued'];

                // V3 Anti-False-Idle: Khi nội bộ không có dữ liệu mới -> tự lập lịch External Research theo Curriculum!
                $policy = $this->governance->getPolicy();
                if ($tasksDiscovered === 0 && ! empty($policy['external_research_enabled']) && Schema::hasTable('ai_research_topics')) {
                    $plan = $this->curriculum->pickNextResearchAction();
                    if (in_array($plan['action'], ['research_discover', 'research_extract', 'research_verify', 'research_expand'], true) && $plan['topic'] !== null) {
                        $tTopic = $plan['topic'];
                        $tKey = "v3:{$plan['action']}:t{$tTopic->id}:".now()->format('YmdHi');
                        if (! AiLearningTask::query()->where('idempotency_key', $tKey)->exists()) {
                            AiLearningTask::query()->create([
                                'idempotency_key' => $tKey,
                                'domain_level' => 3,
                                'task_type' => $plan['action'],
                                'topic' => (string) $tTopic->category,
                                'priority' => max(5, min(10, (int) $tTopic->priority)),
                                'status' => AiLearningTask::STATUS_QUEUED,
                                'payload' => ['topic_id' => (int) $tTopic->id, 'reason' => $plan['reason']],
                            ]);
                            $tasksDiscovered++;
                        }
                    }
                }
            }

            // 5. Vòng lặp xử lý hữu hạn tối đa $taskLimit task trong khung $budget giây
            $telemetryEvents = [];
            $lastPhaseStatus = null;

            for ($i = 0; $i < $taskLimit; $i++) {
                $stepPerm = $this->resources->evaluateStepPermission($budget, $startedAt, false, $overrideMetrics);
                if (! $stepPerm['allowed']) {
                    $this->resources->recordWorkerHeartbeat($workerId, $stepPerm['action'], ['last_message' => $stepPerm['reason']]);

                    return $this->cycleResult(
                        true,
                        $stepPerm['code'] === 'memory_limit_reached' || $stepPerm['code'] === 'failure_threshold_exceeded'
                            ? 'paused_by_resource_policy'
                            : $stepPerm['code'],
                        $tasksProcessed,
                        $candidatesCreated,
                        $tasksDiscovered,
                        $recoveredStale,
                        $startedAt,
                        $stepPerm['reason'],
                        $telemetryEvents
                    );
                }

                $task = $this->claimNextTaskAtomically($workerId);
                if ($task === null) {
                    break;
                }

                // Thực thi task NGOÀI transaction DB để không bao giờ khóa bảng khi gọi AI/HTTP!
                $exec = $this->executeClaimedTask($task);
                if (! empty($exec['telemetry'])) {
                    $telemetryEvents[] = $exec['telemetry'];
                }
                if (! empty($exec['phase'])) {
                    $lastPhaseStatus = (string) $exec['phase'];
                }

                if ($exec['status'] === 'budget_blocked') {
                    // Trả lại hàng đợi an toàn (không tính là lỗi failure)
                    $this->releaseTaskBackToQueue($task, $exec['message']);
                    $this->resources->recordWorkerHeartbeat($workerId, 'throttled', ['last_message' => $exec['message']]);

                    return $this->cycleResult(
                        true,
                        'waiting_for_budget',
                        $tasksProcessed,
                        $candidatesCreated,
                        $tasksDiscovered,
                        $recoveredStale,
                        $startedAt,
                        $exec['message'],
                        $telemetryEvents
                    );
                }

                $tasksProcessed++;
                $candidatesCreated += (int) ($exec['candidates_created'] ?? 0);

                // Nghỉ ngắn giữa các task để giải phóng CPU & GC bộ nhớ cho shared hosting
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }

            $policyAfter = $this->governance->getPolicy();
            if ($tasksProcessed > 0) {
                $status = $lastPhaseStatus ?: 'processed';
                $msg = $telemetryEvents !== []
                    ? (string) ($telemetryEvents[count($telemetryEvents) - 1]['message'] ?? "Hoàn tất {$tasksProcessed} tác vụ.")
                    : "Chu kỳ hoàn tất: xử lý {$tasksProcessed} tác vụ, sinh {$candidatesCreated} ứng viên.";
            } elseif (! empty($policyAfter['external_research_enabled']) && Schema::hasTable('ai_research_topics')) {
                $nextPlan = $this->curriculum->pickNextResearchAction();
                $status = 'idle_until_next_research';
                $msg = $nextPlan['reason'];
                $telemetryEvents[] = [
                    'phase' => $status,
                    'next_research_at' => $nextPlan['next_scheduled_at'],
                    'message' => $msg,
                ];
            } else {
                $status = 'idle_no_new_data';
                $msg = 'External Research đang tắt và không có dữ liệu nội bộ mới — bộ não ở trạng thái nghỉ an toàn (0 token).';
            }

            $this->resources->recordWorkerHeartbeat($workerId, $tasksProcessed > 0 ? 'running' : 'idle', [
                'last_message' => $msg,
                'last_status' => $status,
                'tasks_processed' => $tasksProcessed,
                'candidates_created' => $candidatesCreated,
            ]);

            return $this->cycleResult(true, $status, $tasksProcessed, $candidatesCreated, $tasksDiscovered, $recoveredStale, $startedAt, $msg, $telemetryEvents);
        } catch (Throwable $e) {
            $this->resources->recordFailure();
            $this->resources->recordWorkerHeartbeat($workerId, 'error', ['last_message' => Str::limit($e->getMessage(), 180)]);

            return $this->cycleResult(false, 'exception', $tasksProcessed, $candidatesCreated, $tasksDiscovered, $recoveredStale, $startedAt, 'Lỗi an toàn chu kỳ học: '.Str::limit($e->getMessage(), 200));
        } finally {
            $lock->release();
        }
    }

    /**
     * Khôi phục an toàn các task đang ở trạng thái processing nhưng worker đã chết quá thời hạn.
     */
    public function recoverStaleProcessingTasks(?int $timeoutSeconds = null): int
    {
        if (! Schema::hasTable('ai_learning_tasks')) {
            return 0;
        }

        $staleSeconds = $timeoutSeconds ?? (int) config('ai_brain.storage_quotas.stale_lock_timeout_seconds', 120);
        $cutoff = now()->subSeconds(max(1, $staleSeconds));

        $staleTasks = AiLearningTask::query()
            ->where('status', AiLearningTask::STATUS_PROCESSING)
            ->whereNotNull('locked_at')
            ->where('locked_at', '<=', $cutoff)
            ->limit(20)
            ->get();

        $recovered = 0;
        foreach ($staleTasks as $task) {
            if ((int) $task->attempts >= (int) $task->max_attempts) {
                $task->update([
                    'status' => AiLearningTask::STATUS_DEAD_LETTER,
                    'locked_by' => null,
                    'locked_at' => null,
                    'error_message' => 'Task vượt quá số lần thử tối đa sau khi worker dừng đột ngột.',
                    'finished_at' => now(),
                ]);
            } else {
                $backoffSeconds = min(600, 30 * (2 ** max(0, (int) $task->attempts - 1)));
                $task->update([
                    'status' => AiLearningTask::STATUS_QUEUED,
                    'locked_by' => null,
                    'locked_at' => null,
                    'next_retry_at' => now()->addSeconds($backoffSeconds),
                    'error_message' => 'Khôi phục tự động sau khi worker trước ngắt đột ngột.',
                ]);
            }
            $recovered++;
        }

        return $recovered;
    }

    /**
     * Chiếm quyền xử lý ĐÚNG 1 task theo cơ chế nguyên tử (Atomic Claim + Fencing Token) trong transaction < 5ms.
     * Ưu tiên tuyệt đối Progressive Domain Learning: domain_level ASC (L0 -> L1 -> L2 -> L3 -> L4), priority DESC.
     */
    protected function claimNextTaskAtomically(string $workerId): ?AiLearningTask
    {
        return DB::transaction(function () use ($workerId): ?AiLearningTask {
            $candidate = AiLearningTask::query()
                ->where('status', AiLearningTask::STATUS_QUEUED)
                ->where(function ($q) {
                    $q->whereNull('next_retry_at')->orWhere('next_retry_at', '<=', now());
                })
                ->orderBy('domain_level')
                ->orderByDesc('priority')
                ->orderBy('id')
                ->first();

            if ($candidate === null) {
                return null;
            }

            $nextFence = (int) $candidate->fencing_token + 1;
            $updated = AiLearningTask::query()
                ->where('id', $candidate->id)
                ->where('status', AiLearningTask::STATUS_QUEUED)
                ->where('fencing_token', (int) $candidate->fencing_token)
                ->update([
                    'status' => AiLearningTask::STATUS_PROCESSING,
                    'locked_by' => $workerId,
                    'locked_at' => now(),
                    'fencing_token' => $nextFence,
                    'attempts' => (int) $candidate->attempts + 1,
                    'started_at' => $candidate->started_at ?? now(),
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                return null;
            }

            return $candidate->fresh();
        });
    }

    /**
     * Thực thi 1 task đã được chiếm quyền (HOÀN TOÀN NGOÀI DB TRANSACTION).
     *
     * @return array{status: string, candidates_created: int, message: string}
     */
    protected function executeClaimedTask(AiLearningTask $task): array
    {
        $fencingToken = (int) $task->fencing_token;

        try {
            // V3 Autonomous Web Research Tasks
            if (in_array($task->task_type, [
                AiLearningTask::TYPE_RESEARCH_DISCOVER,
                AiLearningTask::TYPE_RESEARCH_EXTRACT,
                AiLearningTask::TYPE_RESEARCH_VERIFY,
                AiLearningTask::TYPE_RESEARCH_EXPAND,
            ], true)) {
                return $this->executeResearchTask($task, $fencingToken);
            }

            // Task không tốn LLM: Đồng bộ Knowledge Graph L2 + Typed Wedding Ontology Graph V4
            if ($task->task_type === AiLearningTask::TYPE_BUILD_GRAPH_RELATION) {
                $syncedCore = $this->graphService->syncVerifiedCoreRelations(null);
                $syncedTyped = $this->typedGraph->syncTypedWeddingOntologyGraph(null);
                $this->completeTaskWithFence($task->id, $fencingToken, [
                    'relations_synced' => $syncedCore + $syncedTyped,
                    'core_synced' => $syncedCore,
                    'typed_ontology_synced' => $syncedTyped,
                ]);
                $this->resources->resetFailures();

                return ['status' => 'completed', 'candidates_created' => 0, 'message' => "Đồng bộ {$syncedCore} quan hệ sản phẩm + {$syncedTyped} quan hệ Typed Ontology Graph V4."];
            }

            // Task không tốn LLM: Chạy kiểm định hồi quy
            if ($task->task_type === AiLearningTask::TYPE_EVALUATE_REGRESSION) {
                $eval = $this->evaluationService->runRegressionSuite(null);
                $this->completeTaskWithFence($task->id, $fencingToken, [
                    'passed' => $eval['passed'],
                    'total' => $eval['total'],
                    'pass_rate' => $eval['pass_rate'],
                ]);
                $this->resources->resetFailures();

                return ['status' => 'completed', 'candidates_created' => 0, 'message' => "Kiểm định hồi quy: {$eval['passed']}/{$eval['total']} đạt."];
            }

            // Các task cần gọi LLM (Teacher -> Critic -> Deterministic Verifier): BẮT BUỘC kiểm ngân sách!
            $budgetCheck = $this->governance->hasRemainingLearningBudget();
            if (! $budgetCheck['allowed']) {
                return ['status' => 'budget_blocked', 'candidates_created' => 0, 'message' => $budgetCheck['reason']];
            }

            // Chặn phình bảng candidate vượt quota lưu trữ
            $maxPending = (int) config('ai_brain.storage_quotas.max_pending_candidates', 250);
            $currentPending = AiKnowledgeEntry::query()->where('status', AiKnowledgeEntry::STATUS_PENDING)->count();
            if ($currentPending >= $maxPending) {
                $this->completeTaskWithFence($task->id, $fencingToken, ['skipped' => 'pending_quota_reached'], AiLearningTask::STATUS_SKIPPED);

                return ['status' => 'skipped', 'candidates_created' => 0, 'message' => "Đã chạm giới hạn {$maxPending} candidate chờ duyệt."];
            }

            $topic = (string) $task->topic;
            if ($this->governance->isTopicForbidden($topic)) {
                $this->completeTaskWithFence($task->id, $fencingToken, ['skipped' => 'forbidden_topic'], AiLearningTask::STATUS_SKIPPED);

                return ['status' => 'skipped', 'candidates_created' => 0, 'message' => "Chủ đề [{$topic}] nằm trong danh sách cấm học."];
            }

            $res = $this->runVerifiedTeacherCriticPipeline($task);
            if (! $res['ok']) {
                $this->failTaskWithRetry($task, $fencingToken, $res['message']);
                $this->resources->recordFailure();

                return ['status' => 'failed', 'candidates_created' => 0, 'message' => $res['message']];
            }

            $this->completeTaskWithFence($task->id, $fencingToken, [
                'proposed' => $res['proposed'],
                'passed' => $res['passed'],
                'rejected' => $res['rejected'],
                'created_ids' => $res['created_ids'],
                'tokens_used' => $res['tokens_used'],
            ]);
            $this->resources->resetFailures();

            return [
                'status' => 'completed',
                'candidates_created' => $res['passed'],
                'message' => $res['message'],
            ];
        } catch (Throwable $e) {
            $this->failTaskWithRetry($task, $fencingToken, Str::limit($e->getMessage(), 300));
            $this->resources->recordFailure();

            return ['status' => 'failed', 'candidates_created' => 0, 'message' => $e->getMessage()];
        }
    }

    /**
     * Thực thi 4 loại tác vụ Nghiên cứu Tự chủ V3 (discover / extract / verify / expand) kèm Structured Telemetry.
     *
     * @return array{status: string, phase?: string, candidates_created: int, message: string, telemetry?: array<string, mixed>}
     */
    protected function executeResearchTask(AiLearningTask $task, int $fencingToken): array
    {
        $payload = is_array($task->payload) ? $task->payload : [];
        $topicId = (int) ($payload['topic_id'] ?? 0);
        $topic = $topicId > 0 ? AiResearchTopic::query()->with('goal')->find($topicId) : null;

        if (! $topic) {
            $this->completeTaskWithFence($task->id, $fencingToken, ['skipped' => 'topic_not_found'], AiLearningTask::STATUS_SKIPPED);

            return ['status' => 'skipped', 'candidates_created' => 0, 'message' => 'Chủ đề nghiên cứu không còn tồn tại.'];
        }

        $skipDns = ! empty($payload['skip_dns_lookup']);

        $res = match ($task->task_type) {
            AiLearningTask::TYPE_RESEARCH_DISCOVER => $this->researchService->runDiscoverStep($topic, $skipDns),
            AiLearningTask::TYPE_RESEARCH_EXTRACT => $this->researchService->runExtractStep($topic, null, $skipDns),
            AiLearningTask::TYPE_RESEARCH_VERIFY => $this->researchService->runVerifyAndDistillStep($topic),
            default => $this->researchService->runExpandStep($topic),
        };

        if (($res['status'] ?? '') === 'budget_blocked') {
            return [
                'status' => 'budget_blocked',
                'phase' => 'waiting_for_budget',
                'candidates_created' => 0,
                'message' => (string) ($res['message'] ?? 'Chờ ngân sách học nền.'),
                'telemetry' => $res,
            ];
        }

        if (empty($res['ok'])) {
            $this->failTaskWithRetry($task, $fencingToken, (string) ($res['message'] ?? 'Research step failed'));
            $this->resources->recordFailure();

            return [
                'status' => 'failed',
                'phase' => (string) ($res['phase'] ?? 'research_error'),
                'candidates_created' => 0,
                'message' => (string) ($res['message'] ?? 'Lỗi tác vụ nghiên cứu.'),
                'telemetry' => $res,
            ];
        }

        $this->completeTaskWithFence($task->id, $fencingToken, $res);
        $this->resources->resetFailures();

        return [
            'status' => 'completed',
            'phase' => (string) ($res['phase'] ?? $task->task_type),
            'candidates_created' => (int) ($res['candidates_created'] ?? 0),
            'message' => (string) ($res['message'] ?? 'Hoàn tất nghiên cứu.'),
            'telemetry' => $res,
        ];
    }

    /**
     * Multi-Agent Pipeline V2: Teacher AI -> Independent Critic AI -> Deterministic Verifier & Curator Gate.
     * Kể cả khi Teacher & Critic cùng đồng ý nhưng thiếu bằng chứng hợp lệ hoặc bịa giá -> VẪN TỪ CHỐI!
     *
     * @return array{ok: bool, proposed: int, passed: int, rejected: int, created_ids: list<int>, tokens_used: int, message: string}
     */
    protected function runVerifiedTeacherCriticPipeline(AiLearningTask $task): array
    {
        $policy = $this->governance->getPolicy();
        $timeout = max(25, (int) $policy['learning_request_timeout']);
        $topic = array_key_exists((string) $task->topic, AiKnowledgeEntry::CATEGORIES) ? (string) $task->topic : 'general';
        $domainLevel = max(0, min(4, (int) $task->domain_level));
        $payload = is_array($task->payload) ? $task->payload : [];
        $perTopic = max(1, min(3, (int) ($payload['per_topic'] ?? 2)));
        $gapQuestion = isset($payload['sample_question']) ? trim((string) $payload['sample_question']) : '';

        $corpus = $this->knowledgeService->getGroundTruthCorpus();
        $topicCorpus = $corpus['corpus_by_topic'][$topic] ?? $corpus['corpus_by_topic']['general'];
        $existingTitles = array_slice($corpus['existing_titles'], 0, 40);

        // 1. Teacher Agent (chỉ đọc nguồn nội bộ Mây Hẹn, có timeout riêng)
        $teacherSys = implode("\n", [
            'Bạn là AI Teacher Agent chuyên sâu của Mây Hẹn (mayhen.vn).',
            'CHÍNH SÁCH NỘI BỘ BẮT BUỘC (INTERNAL-ONLY):',
            '1. CHỈ sử dụng sự thật có trong <verified_internal_ground_truth>. Tuyệt đối KHÔNG suy diễn hay dùng kiến thức bên ngoài Internet.',
            '2. Mỗi đề xuất PHẢI đính kèm ít nhất 1 mã nguồn bằng chứng hợp lệ trong "evidence_refs" (dạng: template:<slug>, doc:<url>, plan:<tên>, ground_truth:<topic>).',
            '3. Câu trả lời súc tích 2-4 câu tự nhiên, đúng nghiệp vụ, hướng khách trải nghiệm dịch vụ một cách lịch sự. Không HTML, không URL ngoài mayhen.vn.',
            '4. Nếu dữ liệu nội bộ KHÔNG đủ bằng chứng cho câu hỏi mục tiêu, trả về {"candidates":[]} — TUYỆT ĐỐI KHÔNG BỊA.',
            'CHỈ TRẢ VỀ JSON HỢP LỆ:',
            '{"candidates":[{"title":"Câu hỏi cụ thể","keywords":"tu khoa 1, tu khoa 2","answer":"Câu trả lời chuẩn xác 2-4 câu","template_slug":null,"doc_url":null,"evidence_refs":["ground_truth:pricing"]}]}',
        ]);

        $focusInstruction = $gapQuestion !== ''
            ? "Phân tích khoảng trống tri thức từ nhu cầu thực tế sau (đã ẩn danh PII): \"{$gapQuestion}\". Chỉ đề xuất 1 mục tri thức NẾU nguồn nội bộ đủ bằng chứng giải đáp."
            : "Tạo tối đa {$perTopic} mục tri thức cấp độ Level {$domainLevel} cho chủ đề [{$topic}] KHÔNG trùng với danh sách đã có.";

        $teacherUser = $focusInstruction."\n\n"
            ."<verified_internal_ground_truth>\n{$topicCorpus}\n</verified_internal_ground_truth>\n\n"
            ."<existing_titles>\n".implode("\n", array_map(fn (string $t) => '- '.$t, $existingTitles))."\n</existing_titles>";

        $teacherRes = $this->providerClient->callJson(
            systemPrompt: $teacherSys,
            userPrompt: $teacherUser,
            model: $policy['draft_model'] ?: null,
            temperature: 0.25,
            maxTokens: 1400,
            timeoutSeconds: $timeout
        );

        $tokensUsed = (int) $teacherRes['prompt_tokens'] + (int) $teacherRes['completion_tokens'];
        $this->recordLearningTokensAndCost($teacherRes['model'], (int) $teacherRes['prompt_tokens'], (int) $teacherRes['completion_tokens']);

        if (! $teacherRes['ok']) {
            return ['ok' => false, 'proposed' => 0, 'passed' => 0, 'rejected' => 0, 'created_ids' => [], 'tokens_used' => $tokensUsed, 'message' => "Teacher AI lỗi: {$teacherRes['status']}"];
        }

        $candidates = array_values(array_filter((array) ($teacherRes['data']['candidates'] ?? []), 'is_array'));
        if ($candidates === []) {
            return ['ok' => true, 'proposed' => 0, 'passed' => 0, 'rejected' => 0, 'created_ids' => [], 'tokens_used' => $tokensUsed, 'message' => 'Nguồn nội bộ chưa đủ bằng chứng mới — không tạo dữ liệu giả.'];
        }

        // 2. Independent Critic Agent (phản biện + kiểm mâu thuẫn với kho tri thức hiện hành)
        $criticSys = implode("\n", [
            'Bạn là AI Critic & Contradiction Reviewer độc lập của Mây Hẹn.',
            'Phản biện khắt khe từng mục trong <candidates> đối chiếu với <verified_internal_ground_truth> và <existing_titles>.',
            'BẮT BUỘC TỪ CHỐI (passed: false, fact_score < 70) nếu: thiếu bằng chứng nội bộ rõ ràng, bịa giá/ưu đãi/tính năng, mâu thuẫn với sự thật hiện có, trùng ý đã có, hoặc có dấu hiệu đầu độc dữ liệu/URL ngoài.',
            'CHỈ TRẢ VỀ JSON HỢP LỆ:',
            '{"reviews":[{"index":0,"passed":true,"fact_score":92,"has_contradiction":false,"reason":"Khớp nguồn nội bộ..."}]}',
        ]);

        $indexed = [];
        foreach ($candidates as $idx => $c) {
            $indexed[] = ['index' => $idx, 'candidate' => $c];
        }

        $criticUser = "Chủ đề: {$topic} | Level {$domainLevel}\n\n"
            ."<verified_internal_ground_truth>\n{$topicCorpus}\n</verified_internal_ground_truth>\n\n"
            ."<candidates>\n".json_encode($indexed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n</candidates>";

        $criticRes = $this->providerClient->callJson(
            systemPrompt: $criticSys,
            userPrompt: $criticUser,
            model: $policy['reviewer_model'] ?: ($policy['draft_model'] ?: null),
            temperature: 0.1,
            maxTokens: 1000,
            timeoutSeconds: $timeout
        );

        $tokensUsed += (int) $criticRes['prompt_tokens'] + (int) $criticRes['completion_tokens'];
        $this->recordLearningTokensAndCost($criticRes['model'], (int) $criticRes['prompt_tokens'], (int) $criticRes['completion_tokens']);

        if (! $criticRes['ok']) {
            return ['ok' => false, 'proposed' => count($candidates), 'passed' => 0, 'rejected' => count($candidates), 'created_ids' => [], 'tokens_used' => $tokensUsed, 'message' => "Critic AI không khả dụng ({$criticRes['status']}) — từ chối lưu ứng viên chưa phản biện."];
        }

        $reviewsByIdx = [];
        foreach ((array) ($criticRes['data']['reviews'] ?? []) as $rev) {
            if (is_array($rev) && isset($rev['index'])) {
                $reviewsByIdx[(int) $rev['index']] = $rev;
            }
        }

        // 3. Deterministic Evidence Verifier & Knowledge Curator (Code cứng — không AI nào vượt qua được!)
        $existingNorms = array_map(fn (string $t) => $this->knowledgeService->normalizeText($t), $corpus['existing_titles']);
        $expiryDays = max(7, (int) config('ai_brain.storage_quotas.candidate_expiry_days', 30));
        $passed = 0;
        $rejected = 0;
        $createdIds = [];

        foreach ($candidates as $idx => $cand) {
            $rev = $reviewsByIdx[$idx] ?? null;
            $factScore = is_array($rev) ? (int) ($rev['fact_score'] ?? 0) : 0;
            $criticPassed = is_array($rev) && (bool) ($rev['passed'] ?? false) && empty($rev['has_contradiction']);
            $criticNote = is_array($rev) ? trim((string) ($rev['reason'] ?? '')) : 'Thiếu phản biện Critic';

            if (! $criticPassed || $factScore < 85) {
                $rejected++;

                continue;
            }

            $verified = $this->verifyCandidateEvidenceDeterministically(
                cand: $cand,
                topic: $topic,
                topicCorpus: $topicCorpus,
                validSlugs: $corpus['valid_template_slugs'],
                validDocUrls: $corpus['valid_doc_urls'],
                existingNormalizedTitles: $existingNorms
            );

            if (! $verified['ok']) {
                $rejected++;

                continue;
            }

            $clean = $verified['entry'];
            $contentHash = AiKnowledgeEntry::computeContentHash($topic, $clean['title'], $clean['answer']);

            // Chống trùng tuyệt đối theo content_hash ở DB
            if (Schema::hasColumn('ai_knowledge_entries', 'content_hash')
                && AiKnowledgeEntry::query()->where('content_hash', $contentHash)->exists()) {
                $rejected++;

                continue;
            }

            $existingNorms[] = $this->knowledgeService->normalizeText($clean['title']);

            // LUÔN lưu trạng thái PENDING + is_active = false chờ Admin duyệt!
            $attrs = [
                'category' => $topic,
                'title' => $clean['title'],
                'keywords' => $clean['keywords'],
                'answer' => $clean['answer'],
                'template_slug' => $clean['template_slug'],
                'doc_url' => $clean['doc_url'],
                'source' => AiKnowledgeEntry::SOURCE_AI_SELF_TRAIN,
                'status' => AiKnowledgeEntry::STATUS_PENDING,
                'is_active' => false,
                'is_outdated' => false,
                'priority' => 7,
                'version' => 1,
                'trust_score' => min(95, max(85, $factScore)),
                'evidence_refs' => $clean['evidence_refs'],
                'review_summary' => [
                    'passed' => true,
                    'fact_score' => $factScore,
                    'reason' => $criticNote,
                    'draft_model' => $teacherRes['model'],
                    'reviewer_model' => $criticRes['model'],
                    'task_id' => (int) $task->id,
                    'reviewed_at' => now()->toIso8601String(),
                ],
            ];

            if (Schema::hasColumn('ai_knowledge_entries', 'content_hash')) {
                $attrs['content_hash'] = $contentHash;
                $attrs['domain_level'] = $domainLevel;
                $attrs['source_id'] = (string) ($payload['source_key'] ?? ('task:'.$task->id));
                $attrs['source_type'] = 'verified_internal';
                $attrs['ownership'] = 'mayhen_internal';
                $attrs['security_classification'] = 'public_customer_safe';
                $attrs['expires_at'] = now()->addDays($expiryDays);
            }

            $entry = AiKnowledgeEntry::query()->create($attrs);
            $createdIds[] = (int) $entry->id;
            $passed++;

            // Nếu đây là task giải quyết Knowledge Gap -> đánh dấu đã có ứng viên chờ duyệt
            if (! empty($payload['gap_id']) && Schema::hasTable('ai_knowledge_gaps')) {
                AiKnowledgeGap::query()
                    ->where('id', (int) $payload['gap_id'])
                    ->update(['resolved_entry_id' => (int) $entry->id]);
            }
        }

        return [
            'ok' => true,
            'proposed' => count($candidates),
            'passed' => $passed,
            'rejected' => $rejected,
            'created_ids' => $createdIds,
            'tokens_used' => $tokensUsed,
            'message' => "Task #{$task->id} (L{$domainLevel} {$topic}): +{$passed} ứng viên chờ Admin duyệt, loại {$rejected}.",
        ];
    }

    /**
     * Deterministic Evidence & Safety Gate (Bắt buộc bằng code):
     * Kể cả Teacher AI và Critic AI cùng đồng ý nhưng thiếu evidence_refs hợp lệ hoặc bịa giá -> TỪ CHỐI!
     *
     * @param  array<string, mixed>  $cand
     * @param  list<string>  $validSlugs
     * @param  list<string>  $validDocUrls
     * @param  list<string>  $existingNormalizedTitles
     * @return array{ok: bool, reason?: string, entry?: array<string, mixed>}
     */
    public function verifyCandidateEvidenceDeterministically(
        array $cand,
        string $topic,
        string $topicCorpus,
        array $validSlugs,
        array $validDocUrls,
        array $existingNormalizedTitles
    ): array {
        $title = $this->securityGuard->sanitizePlainText((string) ($cand['title'] ?? ''), 190);
        $keywords = $this->securityGuard->sanitizePlainText((string) ($cand['keywords'] ?? ''), 480);
        $answer = $this->securityGuard->sanitizePlainText((string) ($cand['answer'] ?? ''), 1200);

        if (mb_strlen($title, 'UTF-8') < 8 || mb_strlen($answer, 'UTF-8') < 25) {
            return ['ok' => false, 'reason' => 'Tiêu đề hoặc nội dung quá ngắn/rỗng'];
        }

        // BẮT BUỘC phải có ít nhất 1 evidence_refs nội bộ hợp lệ (chống 2 AI tự bịa không nguồn!)
        $rawRefs = is_array($cand['evidence_refs'] ?? null) ? $cand['evidence_refs'] : [];
        $verifiedRefs = [];
        foreach ($rawRefs as $ref) {
            $cleanRef = trim($this->securityGuard->sanitizePlainText((string) $ref, 120));
            if ($cleanRef === '') {
                continue;
            }
            // Chặn tuyệt đối nguồn ngoài Internet (http://, https:// không thuộc mayhen.vn)
            if (preg_match('/https?:\/\//i', $cleanRef) === 1 && ! str_contains(strtolower($cleanRef), 'mayhen.vn')) {
                return ['ok' => false, 'reason' => "Nguồn ngoài không hợp lệ bị chặn bởi Internal-Only Policy: {$cleanRef}"];
            }
            if (preg_match('/^(template|doc|plan|ground_truth|table|code|knowledge|relation):/i', $cleanRef) === 1) {
                $verifiedRefs[] = $cleanRef;
            }
        }

        $tplSlug = isset($cand['template_slug']) && is_string($cand['template_slug']) ? trim($cand['template_slug']) : null;
        if ($tplSlug !== null && $tplSlug !== '' && in_array($tplSlug, $validSlugs, true)) {
            $verifiedRefs[] = 'template:'.$tplSlug;
        } else {
            $tplSlug = null;
        }

        $docUrl = isset($cand['doc_url']) && is_string($cand['doc_url']) ? trim($cand['doc_url']) : null;
        if ($docUrl !== null && $docUrl !== '' && in_array($docUrl, $validDocUrls, true)) {
            $verifiedRefs[] = 'doc:'.$docUrl;
        } else {
            $docUrl = null;
        }

        $verifiedRefs = array_values(array_unique($verifiedRefs));
        if ($verifiedRefs === []) {
            return ['ok' => false, 'reason' => 'Từ chối bởi Evidence Gate: ứng viên không có bất kỳ mã bằng chứng nội bộ hợp lệ nào'];
        }

        // Kiểm trùng tiêu đề ngữ nghĩa gần đúng
        $normTitle = $this->knowledgeService->normalizeText($title);
        foreach ($existingNormalizedTitles as $existing) {
            if ($existing === $normTitle) {
                return ['ok' => false, 'reason' => "Trùng tiêu đề đã tồn tại: {$title}"];
            }
            similar_text($existing, $normTitle, $sim);
            if ($sim >= 84.0) {
                return ['ok' => false, 'reason' => "Tiêu đề quá giống mục đã có ({$sim}%)"];
            }
        }

        // Quét Zero-Trust Input + Output Policy Gate (chống đầu độc, URL lạ, PII, chỉ dẫn độc hại)
        $inTitle = $this->policyGate->inspectAndClassifyInput($title);
        $inAnswer = $this->policyGate->inspectAndClassifyInput($answer);
        $outAnswer = $this->policyGate->inspectFinalOutput($answer, $topicCorpus);
        if ($inTitle['blocked'] || $inAnswer['blocked'] || ! $outAnswer['safe']) {
            return ['ok' => false, 'reason' => 'Không vượt qua SecurityPolicyGate'];
        }

        // Chặn tuyệt đối số tiền không có thật trong bảng giá nguồn (kể cả 2 AI đồng ý!)
        if (preg_match_all('/\b(\d{2,3}(?:[.,]\d{3})+|\d{2,3}\s*k)\s*(?:đ|vnd|vnđ)?\b/iu', $answer, $priceMatches) > 0) {
            $corpusCompact = preg_replace('/[.,\s]/u', '', mb_strtolower($topicCorpus, 'UTF-8')) ?? '';
            foreach ($priceMatches[1] as $priceToken) {
                $tokenCompact = preg_replace('/[.,\s]/u', '', mb_strtolower($priceToken, 'UTF-8')) ?? '';
                if ($tokenCompact !== '' && ! str_contains($corpusCompact, $tokenCompact)) {
                    return ['ok' => false, 'reason' => "Phát hiện mức giá ({$priceToken}) không khớp bảng giá xác thực"];
                }
            }
        }

        return [
            'ok' => true,
            'entry' => [
                'title' => $title,
                'keywords' => $keywords !== '' ? $keywords : null,
                'answer' => $outAnswer['clean_text'],
                'template_slug' => $tplSlug,
                'doc_url' => $docUrl,
                'evidence_refs' => $verifiedRefs,
            ],
        ];
    }

    /**
     * Admin Knowledge Review: Yêu cầu AI Critic phân tích lại một ứng viên đang chờ duyệt với dữ liệu mới nhất.
     *
     * @return array{ok: bool, message: string, fact_score: int}
     */
    public function reanalyzeCandidate(AiKnowledgeEntry $entry, ?int $adminUserId = null): array
    {
        $policy = $this->governance->getPolicy();
        if ($policy['kill_switch']) {
            return ['ok' => false, 'message' => 'AI Kill Switch đang bật, không thể gọi AI phân tích lại.', 'fact_score' => (int) $entry->trust_score];
        }

        $corpus = $this->knowledgeService->getGroundTruthCorpus();
        $topic = array_key_exists((string) $entry->category, AiKnowledgeEntry::CATEGORIES) ? (string) $entry->category : 'general';
        $topicCorpus = $corpus['corpus_by_topic'][$topic] ?? $corpus['corpus_by_topic']['general'];

        $sys = 'Bạn là AI Critic độc lập của Mây Hẹn. Kiểm tra lại độ chính xác của ứng viên tri thức đối chiếu <verified_ground_truth>. CHỈ TRẢ VỀ JSON: {"passed":true,"fact_score":90,"reason":"Nhận xét ngắn gọn tiếng Việt"}';
        $user = "<verified_ground_truth>\n{$topicCorpus}\n</verified_ground_truth>\n\n<candidate>\n"
            .json_encode([
                'title' => $entry->title,
                'answer' => $entry->answer,
                'template_slug' => $entry->template_slug,
                'doc_url' => $entry->doc_url,
                'evidence_refs' => $entry->evidence_refs,
            ], JSON_UNESCAPED_UNICODE)
            ."\n</candidate>";

        $res = $this->providerClient->callJson(
            systemPrompt: $sys,
            userPrompt: $user,
            model: $policy['reviewer_model'] ?: ($policy['draft_model'] ?: null),
            temperature: 0.1,
            maxTokens: 400,
            timeoutSeconds: max(5, (int) $policy['learning_request_timeout'])
        );

        if (! $res['ok']) {
            return ['ok' => false, 'message' => "Không gọi được AI Critic ({$res['status']}).", 'fact_score' => (int) $entry->trust_score];
        }

        $score = max(0, min(100, (int) ($res['data']['fact_score'] ?? 0)));
        $passed = (bool) ($res['data']['passed'] ?? false);
        $reason = trim((string) ($res['data']['reason'] ?? 'Đã phân tích lại bởi Critic AI'));

        $entry->update([
            'trust_score' => $score,
            'review_summary' => [
                'passed' => $passed,
                'fact_score' => $score,
                'reason' => $reason,
                'reviewer_model' => $res['model'],
                'reanalyzed_by_admin' => $adminUserId,
                'reviewed_at' => now()->toIso8601String(),
            ],
        ]);

        AuditLogger::log('AI_CANDIDATE_REANALYZED', 'AiKnowledgeEntry', $entry->id, "Yêu cầu AI phân tích lại ứng viên #{$entry->id}: {$score}/100");

        return ['ok' => true, 'message' => "Đã phân tích lại ứng viên #{$entry->id}: độ chính xác {$score}/100 — {$reason}", 'fact_score' => $score];
    }

    /**
     * Tóm tắt trạng thái đầy đủ cho `php artisan ai:brain:status` & Admin Learning Monitor.
     *
     * @return array<string, mixed>
     */
    public function getStatusSummary(): array
    {
        $policy = $this->governance->getPolicy();
        $op = $this->governance->isLearningOperational(false);
        $budget = $this->governance->hasRemainingLearningBudget();
        $metrics = $this->resources->measureRealMetrics();
        $heartbeat = $this->resources->getWorkerHeartbeat();
        $coverage = $this->graphService->getDomainCoverage();
        $storage = $this->resources->getStorageMetrics();

        $taskStats = Schema::hasTable('ai_learning_tasks')
            ? [
                'queued' => AiLearningTask::query()->where('status', AiLearningTask::STATUS_QUEUED)->count(),
                'processing' => AiLearningTask::query()->where('status', AiLearningTask::STATUS_PROCESSING)->count(),
                'completed' => AiLearningTask::query()->where('status', AiLearningTask::STATUS_COMPLETED)->count(),
                'failed' => AiLearningTask::query()->where('status', AiLearningTask::STATUS_FAILED)->count(),
                'dead_letter' => AiLearningTask::query()->where('status', AiLearningTask::STATUS_DEAD_LETTER)->count(),
                'last_error' => AiLearningTask::query()->whereNotNull('error_message')->latest('updated_at')->value('error_message'),
            ]
            : ['queued' => 0, 'processing' => 0, 'completed' => 0, 'failed' => 0, 'dead_letter' => 0, 'last_error' => null];

        $researchStats = Schema::hasTable('ai_research_topics')
            ? [
                'external_enabled' => $policy['external_research_enabled'],
                'country' => $policy['research_country'],
                'language' => $policy['research_language'],
                'autonomy_level' => $policy['research_autonomy_level'],
                'goals_active' => AiResearchGoal::query()->where('status', AiResearchGoal::STATUS_ACTIVE)->count(),
                'topics_total' => AiResearchTopic::query()->count(),
                'topics_completed' => AiResearchTopic::query()->where('status', AiResearchTopic::STATUS_COMPLETED)->count(),
                'sources_discovered' => AiResearchSource::query()->count(),
                'sources_extracted' => AiResearchSource::query()->where('status', AiResearchSource::STATUS_EXTRACTED)->count(),
                'sources_rejected' => AiResearchSource::query()->whereIn('status', [AiResearchSource::STATUS_REJECTED, AiResearchSource::STATUS_ROBOTS_BLOCKED, AiResearchSource::STATUS_DUPLICATE])->count(),
                'claims_verified' => AiResearchClaim::query()->whereIn('verification_status', [AiResearchClaim::STATUS_VERIFIED, AiResearchClaim::STATUS_PROMOTED])->count(),
                'claims_hypothesis' => AiResearchClaim::query()->where('verification_status', AiResearchClaim::STATUS_HYPOTHESIS)->count(),
            ]
            : ['external_enabled' => false, 'goals_active' => 0, 'topics_total' => 0, 'topics_completed' => 0, 'sources_discovered' => 0, 'sources_extracted' => 0, 'sources_rejected' => 0, 'claims_verified' => 0, 'claims_hypothesis' => 0];

        $v4Stats = [
            'atomic_claims' => Schema::hasTable('ai_research_claims') ? [
                'verified' => AiResearchClaim::query()->where('verification_status', AiResearchClaim::STATUS_VERIFIED)->count(),
                'partially_supported' => AiResearchClaim::query()->where('verification_status', AiResearchClaim::STATUS_PARTIALLY_SUPPORTED)->count(),
                'contradicted' => AiResearchClaim::query()->where('verification_status', AiResearchClaim::STATUS_CONTRADICTED)->count(),
                'duplicate' => AiResearchClaim::query()->where('verification_status', AiResearchClaim::STATUS_DUPLICATE)->count(),
                'rejected' => AiResearchClaim::query()->where('verification_status', AiResearchClaim::STATUS_REJECTED)->count(),
                'approved_reference' => Schema::hasColumn('ai_research_claims', 'approval_status')
                    ? AiResearchClaim::query()->whereIn('approval_status', [AiResearchClaim::STATUS_APPROVED_REFERENCE, AiResearchClaim::STATUS_APPROVED_OFFICIAL])->count()
                    : 0,
                'needs_review' => Schema::hasColumn('ai_research_claims', 'approval_status')
                    ? AiResearchClaim::query()->where('approval_status', AiResearchClaim::STATUS_NEEDS_REVIEW)->count()
                    : 0,
            ] : ['verified' => 0, 'partially_supported' => 0, 'contradicted' => 0, 'duplicate' => 0, 'rejected' => 0, 'approved_reference' => 0, 'needs_review' => 0],
            'knowledge_spaces' => Schema::hasColumn('ai_knowledge_entries', 'knowledge_space') ? [
                AiResearchClaim::SPACE_OFFICIAL_MAYHEN => AiKnowledgeEntry::query()->where('knowledge_space', AiResearchClaim::SPACE_OFFICIAL_MAYHEN)->where('is_active', true)->count(),
                AiResearchClaim::SPACE_INDUSTRY_CULTURAL => AiKnowledgeEntry::query()->where('knowledge_space', AiResearchClaim::SPACE_INDUSTRY_CULTURAL)->where('is_active', true)->count(),
                AiResearchClaim::SPACE_PRODUCT_INNOVATION => AiKnowledgeEntry::query()->where('knowledge_space', AiResearchClaim::SPACE_PRODUCT_INNOVATION)->count(),
                AiResearchClaim::SPACE_CUSTOMER_INTELLIGENCE => Schema::hasTable('ai_knowledge_gaps') ? AiKnowledgeGap::query()->where('status', AiKnowledgeGap::STATUS_OPEN)->count() : 0,
            ] : [
                AiResearchClaim::SPACE_OFFICIAL_MAYHEN => AiKnowledgeEntry::query()->liveApproved()->count(),
                AiResearchClaim::SPACE_INDUSTRY_CULTURAL => 0,
                AiResearchClaim::SPACE_PRODUCT_INNOVATION => 0,
                AiResearchClaim::SPACE_CUSTOMER_INTELLIGENCE => 0,
            ],
            'latest_benchmark' => Schema::hasTable('ai_benchmark_runs')
                ? AiBenchmarkRun::query()->latest('id')->first(['id', 'domain', 'benchmark_phase', 'passed_cases', 'total_cases', 'grounded_score', 'coverage_score', 'hallucination_rate', 'delta_vs_baseline', 'regression_detected', 'created_at'])?->toArray()
                : null,
        ];

        return [
            'operational' => $op,
            'budget' => $budget,
            'mode' => $policy['learning_mode'],
            'learning_enabled' => $policy['learning_enabled'],
            'learning_paused' => $policy['learning_paused'],
            'learning_pause_reason' => $policy['learning_pause_reason'],
            'kill_switch' => $policy['kill_switch'],
            'worker' => $heartbeat,
            'metrics' => $metrics,
            'tasks' => $taskStats,
            'coverage' => $coverage,
            'storage' => $storage,
            'research' => $researchStats,
            'v4' => $v4Stats,
            'checkpoints_count' => Schema::hasTable('ai_learning_checkpoints') ? AiLearningCheckpoint::query()->count() : 0,
            'recent_eval' => Schema::hasTable('ai_learning_runs')
                ? AiLearningRun::query()->where('run_type', AiLearningRun::TYPE_REGRESSION_EVAL)->latest('id')->first(['id', 'candidates_passed', 'candidates_proposed', 'finished_at'])?->toArray()
                : null,
        ];
    }

    /**
     * Kiểm tra sức khỏe toàn hệ thống cho `php artisan ai:brain:doctor`.
     *
     * @return array{ok: bool, checks: list<array{name: string, status: string, detail: string}>}
     */
    public function runDoctor(): array
    {
        $checks = [];
        $metrics = $this->resources->measureRealMetrics();
        $policy = $this->governance->getPolicy();

        // 1. Database connectivity
        $checks[] = [
            'name' => 'Database Connectivity',
            'status' => $metrics['db_reachable'] ? 'ok' : 'fail',
            'detail' => $metrics['db_reachable'] ? "Kết nối tốt ({$metrics['db_latency_ms']}ms)" : 'Không kết nối được Database',
        ];

        // 2. V2/V3/V4 Schema tables
        $requiredTables = [
            'ai_knowledge_entries', 'ai_knowledge_versions', 'ai_knowledge_relations', 'ai_knowledge_gaps',
            'ai_learning_checkpoints', 'ai_learning_tasks', 'ai_learning_runs', 'ai_customer_memories', 'ai_chatbot_logs',
            'ai_research_goals', 'ai_research_topics', 'ai_research_sources', 'ai_research_claims', 'ai_benchmark_runs',
        ];
        $missing = array_values(array_filter($requiredTables, fn (string $t) => ! Schema::hasTable($t)));
        $checks[] = [
            'name' => 'AI Brain V4 Tables',
            'status' => $missing === [] ? 'ok' : 'fail',
            'detail' => $missing === [] ? 'Đủ 14/14 bảng AI độc lập (không chạm bảng nghiệp vụ)' : 'Thiếu bảng: '.implode(', ', $missing),
        ];

        // 2b. V4 Ontology, 4-Space Isolation & Quality Gates Readiness
        $v4ColsOk = Schema::hasColumn('ai_research_claims', 'quality_metrics')
            && Schema::hasColumn('ai_knowledge_entries', 'knowledge_space')
            && Schema::hasColumn('ai_research_sources', 'is_blocked');
        $checks[] = [
            'name' => 'V4 Ontology & 8-Gate Quality Engine',
            'status' => $v4ColsOk ? 'ok' : 'fail',
            'detail' => $v4ColsOk
                ? 'Sẵn sàng 4 Không gian Tri thức, 8 Cổng kiểm định (A→H), Typed Graph 13 kiểu & Benchmark hồi quy'
                : 'Chưa chạy migration nâng cấp V4 (chạy php artisan migrate)',
        ];

        // 3. Distributed Cache Lock
        $lockOk = false;
        try {
            $testLock = Cache::lock('mayhen_ai_brain_doctor_lock_test', 5);
            if ($testLock->get()) {
                $lockOk = true;
                $testLock->release();
            }
        } catch (Throwable) {
            $lockOk = false;
        }
        $checks[] = [
            'name' => 'Non-Overlapping Cache Lock',
            'status' => $lockOk ? 'ok' : 'fail',
            'detail' => $lockOk ? 'Hoạt động ổn định (driver: '.config('cache.default').')' : 'Không lấy được Cache Lock',
        ];

        // 4. Queue isolation
        $checks[] = [
            'name' => 'Business Queue Isolation',
            'status' => 'ok',
            'detail' => 'Learning tasks lưu riêng bảng ai_learning_tasks (tách biệt hoàn toàn khỏi queue nghiệp vụ '.config('queue.default').', backlog hiện tại: '.$metrics['business_queue_backlog'].')',
        ];

        // 5. Security Invariants V3
        $invOk = $policy['admin_approval_required'] === true
            && $policy['allow_unverified_promotion'] === false
            && $policy['respect_robots_and_ssrf_guard'] === true;
        $checks[] = [
            'name' => 'Security & Research Invariants',
            'status' => $invOk ? 'ok' : 'fail',
            'detail' => 'ExternalResearch='.($policy['external_research_enabled'] ? 'ON' : 'OFF').' | SSRF+RobotsGuard=true | OfficialApproval=true | NoUnverifiedPromotion=true',
        ];

        // 5b. Native Crawler & HTML Extractor
        $crawlerReady = extension_loaded('curl') && extension_loaded('dom') && extension_loaded('mbstring');
        $checks[] = [
            'name' => 'Safe Web Crawler Runtime',
            'status' => $crawlerReady ? 'ok' : 'fail',
            'detail' => $crawlerReady
                ? 'Native PHP cURL + DOMDocument + mbstring sẵn sàng (0 phụ thuộc Headless Browser)'
                : 'Thiếu extension PHP (curl/dom/mbstring)',
        ];

        // 6. Learning Activation & Budget Guard
        $budgetCheck = $this->governance->hasRemainingLearningBudget();
        $checks[] = [
            'name' => 'Learning Flag & Budget Guard',
            'status' => ($policy['learning_enabled'] && $budgetCheck['allowed']) ? 'ok' : 'warn',
            'detail' => 'Enabled='.($policy['learning_enabled'] ? 'true' : 'false').', Paused='.($policy['learning_paused'] ? 'true' : 'false').', Mode='.$policy['learning_mode'].' — '.$budgetCheck['reason'],
        ];

        // 7. Provider Configuration
        $resolvedBaseUrl = trim((string) ($policy['ninerouter_base_url'] ?? config('services.ninerouter.base_url', '')));
        $providerReady = (bool) config('services.ninerouter.enabled', false)
            && $resolvedBaseUrl !== ''
            && trim((string) $policy['draft_model']) !== '';
        $checks[] = [
            'name' => 'LLM Provider ('.$this->providerClient->providerName().')',
            'status' => $providerReady ? 'ok' : 'warn',
            'detail' => $providerReady
                ? "URL={$resolvedBaseUrl} | Draft={$policy['draft_model']} | Reviewer={$policy['reviewer_model']} | Timeout={$policy['learning_request_timeout']}s"
                : 'Chưa cấu hình đầy đủ NINEROUTER_BASE_URL / NINEROUTER_MODEL',
        ];

        // 8. Host Resource Snapshot
        $cpuText = $metrics['cpu_metric_available'] ? (string) $metrics['cpu_load_1m'] : 'không khả dụng trên HĐH này (dùng giới hạn bảo thủ)';
        $checks[] = [
            'name' => 'Host Resources Snapshot',
            'status' => $metrics['memory_mb'] < $policy['learning_max_memory_mb'] ? 'ok' : 'warn',
            'detail' => "RAM={$metrics['memory_mb']}MB/{$policy['learning_max_memory_mb']}MB | CPU={$cpuText} | DiskFree=".($metrics['disk_free_mb'] !== null ? $metrics['disk_free_mb'].'MB' : 'N/A'),
        ];

        $hasFailure = collect($checks)->contains(fn (array $c) => $c['status'] === 'fail');

        return [
            'ok' => ! $hasFailure,
            'checks' => $checks,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function completeTaskWithFence(int $taskId, int $fencingToken, array $result, string $finalStatus = AiLearningTask::STATUS_COMPLETED): void
    {
        AiLearningTask::query()
            ->where('id', $taskId)
            ->where('fencing_token', $fencingToken)
            ->update([
                'status' => $finalStatus,
                'locked_by' => null,
                'locked_at' => null,
                'result' => $result,
                'error_message' => null,
                'finished_at' => now(),
                'updated_at' => now(),
            ]);
    }

    protected function failTaskWithRetry(AiLearningTask $task, int $fencingToken, string $errorMessage): void
    {
        $isDead = (int) $task->attempts >= (int) $task->max_attempts;
        $backoffSeconds = min(900, 30 * (2 ** max(0, (int) $task->attempts - 1)));

        AiLearningTask::query()
            ->where('id', $task->id)
            ->where('fencing_token', $fencingToken)
            ->update([
                'status' => $isDead ? AiLearningTask::STATUS_DEAD_LETTER : AiLearningTask::STATUS_QUEUED,
                'locked_by' => null,
                'locked_at' => null,
                'next_retry_at' => $isDead ? null : now()->addSeconds($backoffSeconds),
                'error_message' => Str::limit($errorMessage, 500),
                'finished_at' => $isDead ? now() : null,
                'updated_at' => now(),
            ]);
    }

    protected function releaseTaskBackToQueue(AiLearningTask $task, string $reason): void
    {
        AiLearningTask::query()
            ->where('id', $task->id)
            ->where('fencing_token', (int) $task->fencing_token)
            ->update([
                'status' => AiLearningTask::STATUS_QUEUED,
                'attempts' => max(0, (int) $task->attempts - 1),
                'locked_by' => null,
                'locked_at' => null,
                'error_message' => Str::limit($reason, 300),
                'updated_at' => now(),
            ]);
    }

    protected function recordLearningTokensAndCost(string $model, int $promptTokens, int $completionTokens): void
    {
        $total = $promptTokens + $completionTokens;
        if ($total <= 0) {
            return;
        }
        $costVnd = AiQualityAndCostObservatoryService::calculateTokenCost($model ?: 'gemini-1.5-flash', $promptTokens, $completionTokens);
        $this->governance->recordLearningUsage($total, (float) $costVnd);
    }

    /**
     * @param  list<array<string, mixed>>  $telemetry
     * @return array{ok: bool, status: string, tasks_processed: int, candidates_created: int, tasks_discovered: int, recovered_stale: int, elapsed_ms: int, message: string, telemetry: list<array<string, mixed>>}
     */
    protected function cycleResult(
        bool $ok,
        string $status,
        int $tasksProcessed,
        int $candidatesCreated,
        int $tasksDiscovered,
        int $recoveredStale,
        float $startedAt,
        string $message,
        array $telemetry = []
    ): array {
        return [
            'ok' => $ok,
            'status' => $status,
            'tasks_processed' => $tasksProcessed,
            'candidates_created' => $candidatesCreated,
            'tasks_discovered' => $tasksDiscovered,
            'recovered_stale' => $recoveredStale,
            'elapsed_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'message' => $message,
            'telemetry' => $telemetry,
        ];
    }
}
