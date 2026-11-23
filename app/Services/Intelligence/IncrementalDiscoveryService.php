<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Models\AiChatbotLog;
use App\Models\AiKnowledgeEntry;
use App\Models\AiKnowledgeGap;
use App\Models\AiLearningCheckpoint;
use App\Models\AiLearningTask;
use App\Models\InvitationTemplate;
use App\Models\Plan;
use App\Services\DocumentationService;
use Illuminate\Support\Facades\Schema;

/**
 * Internal-Only Incremental Discovery & Progressive Domain Planner (Level 0 -> Level 4).
 * - Chỉ đọc READ-ONLY nguồn nội bộ hợp lệ của Mây Hẹn (chunkById, không dùng Model::all()).
 * - Dùng checkpoint + content_hash để CHỈ tạo task khi có dữ liệu mới/thay đổi hoặc khoảng trống mới.
 * - Khi đã khai thác hết dữ liệu hợp lệ -> trả về 0 task mới (KHÔNG tự sinh task giả vô hạn!).
 */
class IncrementalDiscoveryService
{
    public function __construct(
        protected DocumentationService $documentationService,
        protected KnowledgeGraphService $graphService,
        protected AiGovernanceService $governance,
        protected SecurityPolicyGate $policyGate
    ) {}

    /**
     * Xem trước hoặc thực thi thu thập incremental theo Progressive Domain Learning (L0 -> L4).
     *
     * @return array{
     *     dry_run: bool,
     *     unlocked_level: int,
     *     tasks_planned: int,
     *     tasks_enqueued: int,
     *     sources_checked: list<array{source_key: string, level: int, records: int, changed: bool, action: string}>
     * }
     */
    public function discover(bool $dryRun = false, ?int $onlyLevel = null): array
    {
        if (! Schema::hasTable('ai_learning_tasks') || ! Schema::hasTable('ai_learning_checkpoints')) {
            return [
                'dry_run' => $dryRun,
                'unlocked_level' => 0,
                'tasks_planned' => 0,
                'tasks_enqueued' => 0,
                'sources_checked' => [],
            ];
        }

        $coverage = $this->graphService->getDomainCoverage();
        $unlockedLevel = $coverage['current_unlocked_level'];
        $sourcesChecked = [];
        $tasksPlanned = 0;
        $tasksEnqueued = 0;

        // =========================================================================
        // LEVEL 0: Core Identity & Active Plans (Bảng giá & Chính sách chính thức)
        // =========================================================================
        if ($onlyLevel === null || $onlyLevel === 0) {
            $planFingerprints = [];
            $planCount = 0;
            $latestPlanUpdated = null;

            Plan::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->chunkById(20, function ($chunk) use (&$planFingerprints, &$planCount, &$latestPlanUpdated): void {
                    foreach ($chunk as $plan) {
                        $planCount++;
                        $planFingerprints[] = $plan->id.'|'.$plan->slug.'|'.$plan->price.'|'.$plan->duration_days.'|'.($plan->updated_at?->timestamp ?? 0);
                        if ($plan->updated_at && ($latestPlanUpdated === null || $plan->updated_at->gt($latestPlanUpdated))) {
                            $latestPlanUpdated = $plan->updated_at;
                        }
                    }
                });

            $plansHash = hash('sha256', implode("\n", $planFingerprints));
            $planChanged = $this->hasSourceChanged('internal:plans:l0', $plansHash);
            $action = 'no_change';

            if ($planChanged && $planCount > 0 && ! $this->governance->isTopicForbidden('pricing')) {
                $tasksPlanned++;
                $action = 'enqueue_pricing_l0';
                if (! $dryRun) {
                    if ($this->enqueueIdempotentTask(
                        idempotencyKey: 'discover:l0:plans:'.substr($plansHash, 0, 16),
                        domainLevel: 0,
                        taskType: AiLearningTask::TYPE_LEARN_DOMAIN_MODULE,
                        topic: 'pricing',
                        priority: 10,
                        payload: ['source_key' => 'internal:plans:l0', 'source_hash' => $plansHash, 'per_topic' => 2]
                    )) {
                        $tasksEnqueued++;
                    }
                    $this->saveCheckpoint('internal:plans:l0', 0, $planCount, $plansHash, $planCount, $latestPlanUpdated);
                }
            }

            $sourcesChecked[] = [
                'source_key' => 'internal:plans:l0',
                'level' => 0,
                'records' => $planCount,
                'changed' => $planChanged,
                'action' => $action,
            ];
        }

        // =========================================================================
        // LEVEL 1: Product & Feature Deep Learning (Templates & Client Docs)
        // =========================================================================
        if (($onlyLevel === null && $unlockedLevel >= 0) || $onlyLevel === 1) {
            // 1A. Invitation Templates (chunkById)
            $tplFingerprints = [];
            $tplCount = 0;
            $latestTplUpdated = null;

            InvitationTemplate::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->chunkById(20, function ($chunk) use (&$tplFingerprints, &$tplCount, &$latestTplUpdated): void {
                    foreach ($chunk as $tpl) {
                        $tplCount++;
                        $tplFingerprints[] = $tpl->id.'|'.$tpl->slug.'|'.$tpl->name.'|'.$tpl->category.'|'.($tpl->updated_at?->timestamp ?? 0);
                        if ($tpl->updated_at && ($latestTplUpdated === null || $tpl->updated_at->gt($latestTplUpdated))) {
                            $latestTplUpdated = $tpl->updated_at;
                        }
                    }
                });

            $tplHash = hash('sha256', implode("\n", $tplFingerprints));
            $tplChanged = $this->hasSourceChanged('internal:templates:l1', $tplHash);
            $tplAction = 'no_change';

            if ($tplChanged && $tplCount > 0 && ! $this->governance->isTopicForbidden('templates')) {
                $tasksPlanned++;
                $tplAction = 'enqueue_templates_l1';
                if (! $dryRun) {
                    if ($this->enqueueIdempotentTask(
                        idempotencyKey: 'discover:l1:templates:'.substr($tplHash, 0, 16),
                        domainLevel: 1,
                        taskType: AiLearningTask::TYPE_LEARN_DOMAIN_MODULE,
                        topic: 'templates',
                        priority: 9,
                        payload: ['source_key' => 'internal:templates:l1', 'source_hash' => $tplHash, 'per_topic' => 2]
                    )) {
                        $tasksEnqueued++;
                    }
                    $this->saveCheckpoint('internal:templates:l1', 1, $tplCount, $tplHash, $tplCount, $latestTplUpdated);
                }
            }

            $sourcesChecked[] = [
                'source_key' => 'internal:templates:l1',
                'level' => 1,
                'records' => $tplCount,
                'changed' => $tplChanged,
                'action' => $tplAction,
            ];

            // 1B. Client Docs (DocumentationService — read-only frontmatter)
            $docFingerprints = [];
            $docCount = 0;
            foreach ($this->documentationService->getCategories('client') as $cat) {
                foreach ($cat['articles'] ?? [] as $art) {
                    $docCount++;
                    $docFingerprints[] = ($art['url'] ?? '').'|'.($art['title'] ?? '').'|'.($art['updated_at'] ?? '');
                }
            }
            $docsHash = hash('sha256', implode("\n", $docFingerprints));
            $docsChanged = $this->hasSourceChanged('internal:client_docs:l1', $docsHash);
            $docsAction = 'no_change';

            if ($docsChanged && $docCount > 0) {
                foreach (['docs', 'rsvp'] as $docTopic) {
                    if ($this->governance->isTopicForbidden($docTopic)) {
                        continue;
                    }
                    $tasksPlanned++;
                    $docsAction = 'enqueue_docs_rsvp_l1';
                    if (! $dryRun) {
                        if ($this->enqueueIdempotentTask(
                            idempotencyKey: "discover:l1:{$docTopic}:".substr($docsHash, 0, 16),
                            domainLevel: 1,
                            taskType: AiLearningTask::TYPE_LEARN_DOMAIN_MODULE,
                            topic: $docTopic,
                            priority: 8,
                            payload: ['source_key' => 'internal:client_docs:l1', 'source_hash' => $docsHash, 'per_topic' => 2]
                        )) {
                            $tasksEnqueued++;
                        }
                    }
                }
                if (! $dryRun) {
                    $this->saveCheckpoint('internal:client_docs:l1', 1, $docCount, $docsHash, $docCount, now());
                }
            }

            $sourcesChecked[] = [
                'source_key' => 'internal:client_docs:l1',
                'level' => 1,
                'records' => $docCount,
                'changed' => $docsChanged,
                'action' => $docsAction,
            ];
        }

        // =========================================================================
        // LEVEL 2: Relationships & Workflows (Knowledge Graph — học sau L0/L1)
        // =========================================================================
        if (($onlyLevel === null && $unlockedLevel >= 1) || $onlyLevel === 2) {
            $graphVersionHash = hash('sha256', 'mayhen-core-graph-v2.0');
            $graphChanged = $this->hasSourceChanged('internal:workflow_graph:l2', $graphVersionHash);
            $graphAction = 'no_change';

            if ($graphChanged) {
                $tasksPlanned++;
                $graphAction = 'enqueue_graph_sync_l2';
                if (! $dryRun) {
                    if ($this->enqueueIdempotentTask(
                        idempotencyKey: 'discover:l2:graph:'.substr($graphVersionHash, 0, 16),
                        domainLevel: 2,
                        taskType: AiLearningTask::TYPE_BUILD_GRAPH_RELATION,
                        topic: 'general',
                        priority: 7,
                        payload: ['source_key' => 'internal:workflow_graph:l2', 'source_hash' => $graphVersionHash]
                    )) {
                        $tasksEnqueued++;
                    }
                    $this->saveCheckpoint('internal:workflow_graph:l2', 2, 10, $graphVersionHash, 10, now());
                }
            }

            $sourcesChecked[] = [
                'source_key' => 'internal:workflow_graph:l2',
                'level' => 2,
                'records' => 10,
                'changed' => $graphChanged,
                'action' => $graphAction,
            ];
        }

        // =========================================================================
        // LEVEL 3 & 4: Customer Intent & Verified Knowledge Gaps (chỉ chạy sau L2)
        // =========================================================================
        if (($onlyLevel === null && $unlockedLevel >= 2) || in_array($onlyLevel, [3, 4], true)) {
            $checkpoint = AiLearningCheckpoint::query()->where('source_key', 'internal:customer_signals:l3')->first();
            $lastLogId = (int) ($checkpoint?->last_cursor ?? 0);
            $newMaxLogId = $lastLogId;
            $scannedLogs = 0;

            if (Schema::hasTable('ai_chatbot_logs')) {
                $hasFeedbackValid = Schema::hasColumn('ai_chatbot_logs', 'feedback_valid');
                AiChatbotLog::query()
                    ->where('id', '>', $lastLogId)
                    ->whereNotIn('response_mode', [
                        AiChatbotLog::MODE_BLOCKED_SECURITY,
                        AiChatbotLog::MODE_OUT_OF_SCOPE,
                    ])
                    ->orderBy('id')
                    ->chunkById(50, function ($chunk) use (&$newMaxLogId, &$scannedLogs, $dryRun, $hasFeedbackValid): void {
                        foreach ($chunk as $log) {
                            $scannedLogs++;
                            $newMaxLogId = max($newMaxLogId, (int) $log->id);

                            // Chỉ ghi nhận khoảng trống từ câu không tìm thấy ngữ cảnh hoặc feedback unhelpful hợp lệ
                            $isUnhelpfulValid = $log->feedback === 'unhelpful' && (! $hasFeedbackValid || $log->feedback_valid !== false);
                            $isGap = $log->response_mode === AiChatbotLog::MODE_NO_CONTEXT_FALLBACK || $isUnhelpfulValid;

                            if ($isGap && ! $dryRun) {
                                $masked = $this->policyGate->maskPiiForStorage((string) $log->user_message);
                                $topic = array_key_exists((string) $log->intent, AiKnowledgeEntry::CATEGORIES)
                                    ? (string) $log->intent
                                    : 'general';
                                AiKnowledgeGap::recordGap($masked, $topic, 3, [
                                    'source_log_id' => (int) $log->id,
                                    'reason' => $isUnhelpfulValid ? 'unhelpful_feedback' : 'no_context_fallback',
                                ]);
                            }
                        }
                    });
            }

            $minSampleSize = (int) config('ai_brain.domain_levels.3.min_sample_size', 2);
            $openGaps = Schema::hasTable('ai_knowledge_gaps')
                ? AiKnowledgeGap::query()
                    ->where('status', AiKnowledgeGap::STATUS_OPEN)
                    ->where('occurrence_count', '>=', $minSampleSize)
                    ->orderByDesc('occurrence_count')
                    ->limit(3)
                    ->get()
                : collect();

            $gapAction = 'no_change';
            foreach ($openGaps as $gap) {
                if ($this->governance->isTopicForbidden((string) $gap->topic)) {
                    continue;
                }
                $tasksPlanned++;
                $gapAction = 'enqueue_knowledge_gaps';
                if (! $dryRun) {
                    $targetLevel = $unlockedLevel >= 4 ? 4 : 3;
                    if ($this->enqueueIdempotentTask(
                        idempotencyKey: 'gap:'.$gap->id.':occ:'.$gap->occurrence_count,
                        domainLevel: $targetLevel,
                        taskType: AiLearningTask::TYPE_ANALYZE_KNOWLEDGE_GAP,
                        topic: (string) $gap->topic,
                        priority: 6,
                        payload: [
                            'gap_id' => (int) $gap->id,
                            'sample_question' => (string) $gap->sample_question,
                            'occurrence_count' => (int) $gap->occurrence_count,
                        ]
                    )) {
                        $gap->update(['status' => AiKnowledgeGap::STATUS_QUEUED]);
                        $tasksEnqueued++;
                    }
                }
            }

            if (! $dryRun && $newMaxLogId > $lastLogId) {
                $this->saveCheckpoint(
                    'internal:customer_signals:l3',
                    3,
                    $newMaxLogId,
                    hash('sha256', 'log_cursor:'.$newMaxLogId),
                    $scannedLogs,
                    now()
                );
            }

            $sourcesChecked[] = [
                'source_key' => 'internal:customer_signals:l3',
                'level' => 3,
                'records' => $scannedLogs,
                'changed' => $scannedLogs > 0 || $openGaps->isNotEmpty(),
                'action' => $gapAction,
            ];
        }

        return [
            'dry_run' => $dryRun,
            'unlocked_level' => $unlockedLevel,
            'tasks_planned' => $tasksPlanned,
            'tasks_enqueued' => $tasksEnqueued,
            'sources_checked' => $sourcesChecked,
        ];
    }

    protected function hasSourceChanged(string $sourceKey, string $currentHash): bool
    {
        $checkpoint = AiLearningCheckpoint::query()->where('source_key', $sourceKey)->first();

        return $checkpoint === null || (string) $checkpoint->content_hash !== $currentHash;
    }

    protected function saveCheckpoint(
        string $sourceKey,
        int $domainLevel,
        int $lastCursor,
        string $contentHash,
        int $recordsScanned,
        ?\DateTimeInterface $sourceUpdatedAt = null
    ): void {
        AiLearningCheckpoint::query()->updateOrCreate(
            ['source_key' => $sourceKey],
            [
                'domain_level' => $domainLevel,
                'last_cursor' => $lastCursor,
                'content_hash' => $contentHash,
                'records_scanned' => $recordsScanned,
                'last_source_updated_at' => $sourceUpdatedAt,
                'last_run_at' => now(),
            ]
        );
    }

    /**
     * Tạo task idempotent 100% chống duplicate dù Cron chạy trùng.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function enqueueIdempotentTask(
        string $idempotencyKey,
        int $domainLevel,
        string $taskType,
        string $topic,
        int $priority,
        array $payload
    ): bool {
        if (AiLearningTask::query()->where('idempotency_key', $idempotencyKey)->exists()) {
            return false;
        }

        AiLearningTask::query()->create([
            'idempotency_key' => $idempotencyKey,
            'domain_level' => $domainLevel,
            'task_type' => $taskType,
            'topic' => $topic,
            'priority' => $priority,
            'status' => AiLearningTask::STATUS_QUEUED,
            'attempts' => 0,
            'max_attempts' => 3,
            'payload' => $payload,
        ]);

        return true;
    }
}
