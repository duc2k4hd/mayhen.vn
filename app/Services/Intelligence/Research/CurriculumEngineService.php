<?php

declare(strict_types=1);

namespace App\Services\Intelligence\Research;

use App\Models\AiResearchClaim;
use App\Models\AiResearchGoal;
use App\Models\AiResearchSource;
use App\Models\AiResearchTopic;
use App\Services\AuditLogger;
use App\Services\Chatbot\ChatbotProviderClient;
use App\Services\Intelligence\AiGovernanceService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Dynamic Learning Curriculum Engine V3:
 * - Quản lý cây chủ đề nghiên cứu theo 7 giai đoạn (L1 Foundation -> L7 Expansion).
 * - Khởi tạo lộ trình chuyên sâu Ngành Cưới & Thiệp Cưới Điện Tử Việt Nam (4 Domains cốt lõi).
 * - Cho phép Admin giao bất kỳ mục tiêu nghiên cứu mới nào mà không cần sửa code.
 * - Tự điều phối hành động nghiên cứu tiếp theo (xóa bỏ tình trạng idle_no_new_data sai!).
 */
class CurriculumEngineService
{
    public function __construct(
        protected AiGovernanceService $governance,
        protected ChatbotProviderClient $providerClient
    ) {}

    /**
     * Khởi tạo / đồng bộ idempotent Giáo án 8 Học phần (64 Chủ đề chuyên sâu Ngành Cưới & Sự Kiện Việt Nam).
     * Bảo toàn 100% tiến độ các chủ đề đã học trước đó và nạp bổ sung toàn bộ học phần còn thiếu.
     *
     * @return array{goal_id: int, goals_created: int, total_goals: int, topics_created: int, total_topics: int}
     */
    public function ensureVietnamWeddingCurriculum(?int $adminUserId = null): array
    {
        if (! Schema::hasTable('ai_research_goals') || ! Schema::hasTable('ai_research_topics')) {
            return ['goal_id' => 0, 'goals_created' => 0, 'total_goals' => 0, 'topics_created' => 0, 'total_topics' => 0];
        }

        $autonomyLevel = $this->governance->getPolicy()['research_autonomy_level'];
        $pillars = VietnameseWeddingCurriculumBlueprint::pillars();

        $primaryGoalId = 0;
        $goalsCreated = 0;
        $topicsCreated = 0;

        foreach ($pillars as $pillar) {
            $goal = AiResearchGoal::query()->where('slug', $pillar['slug'])->first();

            if (! $goal) {
                $goal = AiResearchGoal::query()->create([
                    'slug' => $pillar['slug'],
                    'title' => $pillar['title'],
                    'domain' => $pillar['domain'],
                    'description' => $pillar['description'],
                    'priority' => $pillar['priority'],
                    'country' => 'VN',
                    'language' => 'vi',
                    'max_depth' => 7,
                    'autonomy_level' => $autonomyLevel,
                    'daily_url_limit' => $pillar['daily_url_limit'],
                    'allow_auto_expand' => true,
                    'status' => AiResearchGoal::STATUS_ACTIVE,
                    'created_by_user_id' => $adminUserId,
                ]);
                $goalsCreated++;
            }

            if ($primaryGoalId === 0) {
                $primaryGoalId = (int) $goal->id;
            }

            foreach ($pillar['topics'] as $topicSpec) {
                $existing = AiResearchTopic::query()->where('slug', $topicSpec['slug'])->first();
                if (! $existing) {
                    AiResearchTopic::query()->create(array_merge($topicSpec, [
                        'goal_id' => $goal->id,
                        'learning_stage' => 1,
                        'status' => AiResearchTopic::STATUS_PENDING,
                        'review_state' => 'unreviewed',
                        'next_research_at' => now(),
                    ]));
                    $topicsCreated++;
                }
            }
        }

        return [
            'goal_id' => $primaryGoalId,
            'goals_created' => $goalsCreated,
            'total_goals' => AiResearchGoal::query()->count(),
            'topics_created' => $topicsCreated,
            'total_topics' => AiResearchTopic::query()->count(),
        ];
    }

    /**
     * Admin-Defined Learning Goal (Mục X): Tạo mục tiêu bất kỳ & tự sinh cây Curriculum con không cần sửa code.
     *
     * @param  array<string, mixed>  $input
     * @return array{goal: AiResearchGoal, topics_created: int}
     */
    public function createAdminGoal(array $input, ?int $adminUserId = null): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $domain = Str::slug((string) ($input['domain'] ?? $title), '_');
        if ($domain === '') {
            $domain = 'custom_domain';
        }
        $domain = Str::limit($domain, 55, '');

        $baseSlug = Str::slug($title, '-');
        $slug = Str::limit($baseSlug !== '' ? $baseSlug : 'goal', 80, '').'-'.substr(md5($title.microtime(true)), 0, 6);

        $allowedDomains = $this->parseDomainList($input['allowed_domains'] ?? null);
        $disallowedDomains = $this->parseDomainList($input['disallowed_domains'] ?? null);

        $goal = AiResearchGoal::query()->create([
            'slug' => $slug,
            'title' => Str::limit($title, 185, ''),
            'domain' => $domain,
            'description' => isset($input['description']) ? Str::limit(trim((string) $input['description']), 1000, '') : null,
            'priority' => max(1, min(10, (int) ($input['priority'] ?? 8))),
            'country' => strtoupper(Str::limit(trim((string) ($input['country'] ?? 'VN')), 10, '')) ?: 'VN',
            'language' => strtolower(Str::limit(trim((string) ($input['language'] ?? 'vi')), 10, '')) ?: 'vi',
            'max_depth' => max(1, min(7, (int) ($input['max_depth'] ?? 7))),
            'autonomy_level' => in_array((string) ($input['autonomy_level'] ?? ''), ['conservative', 'supervised', 'autonomous'], true)
                ? (string) $input['autonomy_level']
                : 'supervised',
            'allowed_domains' => $allowedDomains !== [] ? $allowedDomains : null,
            'disallowed_domains' => $disallowedDomains !== [] ? $disallowedDomains : null,
            'daily_url_limit' => max(2, min(100, (int) ($input['daily_url_limit'] ?? 20))),
            'allow_auto_expand' => array_key_exists('allow_auto_expand', $input) ? (bool) $input['allow_auto_expand'] : true,
            'status' => AiResearchGoal::STATUS_ACTIVE,
            'created_by_user_id' => $adminUserId,
        ]);

        $subtopics = $this->decomposeGoalIntoSubtopics($goal);
        $created = 0;

        foreach ($subtopics as $idx => $sub) {
            $subTitle = Str::limit(trim((string) ($sub['title'] ?? '')), 185, '');
            if ($subTitle === '') {
                continue;
            }
            $subSlug = Str::limit(Str::slug($subTitle, '-') ?: 'topic', 95, '').'-'.$goal->id.'-'.($idx + 1);
            AiResearchTopic::query()->create([
                'goal_id' => $goal->id,
                'slug' => $subSlug,
                'title' => $subTitle,
                'domain' => $goal->domain,
                'category' => 'general',
                'priority' => max(1, min(10, (int) $goal->priority - ($idx > 2 ? 1 : 0))),
                'learning_stage' => 1,
                'learning_objectives' => array_values(array_map('strval', (array) ($sub['objectives'] ?? []))),
                'search_queries' => array_values(array_map('strval', (array) ($sub['queries'] ?? [$subTitle]))),
                'unknown_concepts' => array_values(array_map('strval', (array) ($sub['unknown_concepts'] ?? []))),
                'status' => AiResearchTopic::STATUS_PENDING,
                'review_state' => 'unreviewed',
                'next_research_at' => now(),
            ]);
            $created++;
        }

        AuditLogger::log(
            'AI_RESEARCH_GOAL_CREATED',
            'AiResearchGoal',
            $goal->id,
            "Admin thêm mục tiêu nghiên cứu mới: {$goal->title} ({$created} chủ đề con)",
            ['domain' => $goal->domain, 'topics_created' => $created]
        );

        return ['goal' => $goal, 'topics_created' => $created];
    }

    /**
     * Bộ lập lịch chống trạng thái idle giả (Mục VI):
     * Luôn tìm đúng việc có giá trị nhất cần làm tiếp theo trong Curriculum.
     *
     * @return array{action: string, topic: ?AiResearchTopic, reason: string, next_scheduled_at: ?string}
     */
    public function pickNextResearchAction(): array
    {
        if (! Schema::hasTable('ai_research_goals') || ! Schema::hasTable('ai_research_topics')) {
            return ['action' => 'schema_missing', 'topic' => null, 'reason' => 'Chưa chạy migration bảng Research V3.', 'next_scheduled_at' => null];
        }

        // Tự khởi tạo / đồng bộ bổ sung lộ trình 18 Học phần (144 Chủ đề) khi DB chưa đủ học phần chuẩn (100% idempotent, giữ nguyên tiến độ cũ)
        $expectedPillarSlugs = array_column(VietnameseWeddingCurriculumBlueprint::pillars(), 'slug');
        if (AiResearchGoal::query()->whereIn('slug', $expectedPillarSlugs)->count() < count($expectedPillarSlugs)) {
            $this->ensureVietnamWeddingCurriculum(null);
        }

        $activeGoalIds = AiResearchGoal::query()
            ->where('status', AiResearchGoal::STATUS_ACTIVE)
            ->orderByDesc('priority')
            ->pluck('id')
            ->all();

        if ($activeGoalIds === []) {
            return ['action' => 'no_active_goals', 'topic' => null, 'reason' => 'Không có mục tiêu nghiên cứu nào đang kích hoạt.', 'next_scheduled_at' => null];
        }

        // 1. Ưu tiên cao nhất: Nguồn đã phát hiện (discovered) hoặc đã tải chờ trích (fetched)
        $sourceNeedingExtract = AiResearchSource::query()
            ->whereIn('status', [AiResearchSource::STATUS_DISCOVERED, AiResearchSource::STATUS_FETCHED])
            ->whereHas('topic', fn ($q) => $q->whereIn('goal_id', $activeGoalIds))
            ->orderByDesc('relevance_score')
            ->orderByDesc('trust_score')
            ->orderBy('id')
            ->first();

        if ($sourceNeedingExtract && $sourceNeedingExtract->topic) {
            return [
                'action' => 'research_extract',
                'topic' => $sourceNeedingExtract->topic,
                'reason' => "Có nguồn web mới cần thu thập & trích xuất cho chủ đề [{$sourceNeedingExtract->topic->title}].",
                'next_scheduled_at' => null,
            ];
        }

        // 2. Các claim giả thuyết (hypothesis) cần kiểm chứng chéo & phản biện
        $claimNeedingVerify = AiResearchClaim::query()
            ->where('verification_status', AiResearchClaim::STATUS_HYPOTHESIS)
            ->whereHas('topic', fn ($q) => $q->whereIn('goal_id', $activeGoalIds))
            ->orderBy('id')
            ->first();

        if ($claimNeedingVerify && $claimNeedingVerify->topic) {
            return [
                'action' => 'research_verify',
                'topic' => $claimNeedingVerify->topic,
                'reason' => "Có luận điểm cần đối chiếu bằng chứng & phản biện cho chủ đề [{$claimNeedingVerify->topic->title}].",
                'next_scheduled_at' => null,
            ];
        }

        // 2b. Các claim đã qua 8 cổng kiểm định (verified / partially_supported) nhưng chưa đóng gói thành kiến thức chờ Admin duyệt
        $claimNeedingPromote = AiResearchClaim::query()
            ->whereIn('verification_status', [AiResearchClaim::STATUS_VERIFIED, AiResearchClaim::STATUS_PARTIALLY_SUPPORTED])
            ->whereNull('promoted_entry_id')
            ->whereHas('topic', fn ($q) => $q->whereIn('goal_id', $activeGoalIds))
            ->orderBy('id')
            ->first();

        if ($claimNeedingPromote && $claimNeedingPromote->topic) {
            return [
                'action' => 'research_promote',
                'topic' => $claimNeedingPromote->topic,
                'reason' => "Tổng hợp các luận điểm đã kiểm định thành kiến thức chờ Admin duyệt cho chủ đề [{$claimNeedingPromote->topic->title}].",
                'next_scheduled_at' => null,
            ];
        }

        // 3. Chủ đề chưa đạt bao phủ nguồn hoặc đã tới lịch nghiên cứu lại (next_research_at <= now)
        $topicNeedingDiscover = AiResearchTopic::query()
            ->whereIn('goal_id', $activeGoalIds)
            ->where(function ($q) {
                $q->whereNull('next_research_at')->orWhere('next_research_at', '<=', now());
            })
            ->where('status', '!=', AiResearchTopic::STATUS_COMPLETED)
            ->orderByDesc('priority')
            ->orderBy('learning_stage')
            ->orderBy('source_coverage')
            ->orderBy('id')
            ->first();

        if ($topicNeedingDiscover) {
            return [
                'action' => 'research_discover',
                'topic' => $topicNeedingDiscover,
                'reason' => "Chủ đề [{$topicNeedingDiscover->title}] (L{$topicNeedingDiscover->learning_stage}, coverage={$topicNeedingDiscover->source_coverage}) cần khám phá thêm nguồn xác thực.",
                'next_scheduled_at' => null,
            ];
        }

        // 4. Mở rộng chủ đề con có kiểm soát cho các topic đã đạt độ sâu và có unknown_concepts
        $topicForExpand = AiResearchTopic::query()
            ->whereIn('goal_id', $activeGoalIds)
            ->whereHas('goal', fn ($q) => $q->where('allow_auto_expand', true))
            ->where('learning_stage', '>=', 2)
            ->where('learning_stage', '<', 7)
            ->where('status', '!=', AiResearchTopic::STATUS_EXPANDING)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->first();

        if ($topicForExpand && ! empty($topicForExpand->unknown_concepts)) {
            return [
                'action' => 'research_expand',
                'topic' => $topicForExpand,
                'reason' => "Mở rộng chủ đề liên quan từ [{$topicForExpand->title}] theo khái niệm còn thiếu.",
                'next_scheduled_at' => null,
            ];
        }

        // 4b. Tự động nạp tiếp các Học phần còn thiếu trong Giáo án 8 Học phần (64 chủ đề) nếu trước đó chỉ mới có bản 16 chủ đề cũ
        $expectedPillarSlugs = array_column(VietnameseWeddingCurriculumBlueprint::pillars(), 'slug');
        $existingPillarCount = AiResearchGoal::query()->whereIn('slug', $expectedPillarSlugs)->count();
        if ($existingPillarCount < count($expectedPillarSlugs)) {
            $seeded = $this->ensureVietnamWeddingCurriculum(null);
            if ($seeded['topics_created'] > 0) {
                $nextFreshTopic = AiResearchTopic::query()
                    ->where('status', AiResearchTopic::STATUS_PENDING)
                    ->orderByDesc('priority')
                    ->orderBy('id')
                    ->first();

                if ($nextFreshTopic) {
                    return [
                        'action' => 'research_discover',
                        'topic' => $nextFreshTopic,
                        'reason' => "Tự động mở Học phần tiếp theo trong Giáo án 8 Học phần (+{$seeded['topics_created']} chủ đề mới): [{$nextFreshTopic->title}].",
                        'next_scheduled_at' => null,
                    ];
                }
            }
        }

        // 4c. Chế độ Tự học Liên tục (Continuous Learning): Không bao giờ ngủ chờ lịch nếu vẫn còn ngân sách token/tiền ngày!
        $budget = $this->governance->hasRemainingLearningBudget();
        if (! empty($budget['allowed'])) {
            $continuousTopic = AiResearchTopic::query()
                ->whereIn('goal_id', $activeGoalIds)
                ->where('status', '!=', AiResearchTopic::STATUS_COMPLETED)
                ->orderByRaw('CASE WHEN last_researched_at IS NULL THEN 0 ELSE 1 END ASC')
                ->orderBy('last_researched_at')
                ->orderBy('learning_stage')
                ->orderBy('source_coverage')
                ->orderByDesc('priority')
                ->orderBy('id')
                ->first();

            if (! $continuousTopic) {
                $continuousTopic = AiResearchTopic::query()
                    ->whereIn('goal_id', $activeGoalIds)
                    ->orderByRaw('CASE WHEN last_researched_at IS NULL THEN 0 ELSE 1 END ASC')
                    ->orderBy('last_researched_at')
                    ->orderBy('id')
                    ->first();
            }

            if ($continuousTopic) {
                return [
                    'action' => 'research_discover',
                    'topic' => $continuousTopic,
                    'reason' => "Học liên tục theo vòng xoay lộ trình (còn ngân sách ngày): [{$continuousTopic->title}] (L{$continuousTopic->learning_stage}).",
                    'next_scheduled_at' => null,
                ];
            }
        }

        // 5. Hết ngân sách token/tiền ngày hoặc không có chủ đề khả dụng -> dừng an toàn
        $nextAt = AiResearchTopic::query()
            ->whereIn('goal_id', $activeGoalIds)
            ->whereNotNull('next_research_at')
            ->orderBy('next_research_at')
            ->value('next_research_at');

        return [
            'action' => 'idle_until_next_research',
            'topic' => null,
            'reason' => empty($budget['allowed'])
                ? (string) ($budget['reason'] ?? 'Đã chạm hạn mức token/chi phí học nền trong ngày — tự động học tiếp vào ngày mới.')
                : ($nextAt
                    ? "Các chủ đề hiện tại đã hoàn thành lượt nghiên cứu gần nhất — lịch nghiên cứu kế tiếp vào {$nextAt}."
                    : 'Tất cả mục tiêu đang ở trạng thái đã lên lịch chờ chu kỳ tái xác minh.'),
            'next_scheduled_at' => $nextAt ? (string) $nextAt : null,
        ];
    }

    /**
     * Mở rộng có kiểm soát các chủ đề con liên quan nằm trong ranh giới Goal/Domain được Admin cho phép.
     *
     * @return array{created: int, topics: list<string>}
     */
    public function expandTopic(AiResearchTopic $topic): array
    {
        $goal = $topic->goal;
        if (! $goal || ! $goal->allow_auto_expand) {
            return ['created' => 0, 'topics' => []];
        }

        // Giới hạn tổng số topic con trên mỗi goal tránh phình curriculum vô hạn
        if (AiResearchTopic::query()->where('goal_id', $goal->id)->count() >= 60) {
            $topic->update(['learning_stage' => min(7, $topic->learning_stage + 1), 'next_research_at' => now()->addDays(14)]);

            return ['created' => 0, 'topics' => []];
        }

        $unknowns = array_values(array_filter(array_map('trim', (array) ($topic->unknown_concepts ?? []))));
        $createdNames = [];
        $remainingUnknowns = [];

        foreach ($unknowns as $idx => $concept) {
            if (count($createdNames) >= 2) {
                $remainingUnknowns[] = $concept;

                continue;
            }
            $slug = Str::limit(Str::slug($concept, '-') ?: 'subtopic', 95, '').'-p'.$topic->id;
            if (AiResearchTopic::query()->where('slug', $slug)->exists()) {
                continue;
            }

            AiResearchTopic::query()->create([
                'goal_id' => $goal->id,
                'parent_id' => $topic->id,
                'slug' => $slug,
                'title' => Str::limit(ucfirst($concept), 185, ''),
                'domain' => $topic->domain,
                'category' => $topic->category,
                'priority' => max(4, (int) $topic->priority - 1),
                'learning_stage' => 1,
                'learning_objectives' => ["Nghiên cứu chuyên sâu về {$concept} trong ngữ cảnh {$goal->title}"],
                'prerequisites' => [$topic->slug],
                'search_queries' => [$concept.' Việt Nam', $concept.' '.$topic->title],
                'status' => AiResearchTopic::STATUS_PENDING,
                'review_state' => 'unreviewed',
                'next_research_at' => now(),
            ]);
            $createdNames[] = ucfirst($concept);
        }

        $topic->update([
            'unknown_concepts' => $remainingUnknowns,
            'learning_stage' => min(7, max(2, (int) $topic->learning_stage + 1)),
            'status' => AiResearchTopic::STATUS_SCHEDULED,
            'next_research_at' => now()->addDays(7),
        ]);

        return ['created' => count($createdNames), 'topics' => $createdNames];
    }

    /**
     * Cập nhật khách quan tiến độ & cấp độ (L1 -> L7) dựa trên nguồn và bằng chứng thực tế.
     */
    public function refreshTopicProgress(AiResearchTopic $topic): void
    {
        $fetchedSources = AiResearchSource::query()
            ->where('topic_id', $topic->id)
            ->whereIn('status', [AiResearchSource::STATUS_FETCHED, AiResearchSource::STATUS_EXTRACTED])
            ->count();

        $verifiedClaims = AiResearchClaim::query()
            ->where('topic_id', $topic->id)
            ->whereIn('verification_status', [AiResearchClaim::STATUS_VERIFIED, AiResearchClaim::STATUS_PROMOTED])
            ->count();

        $acceptedClaims = AiResearchClaim::query()
            ->where('topic_id', $topic->id)
            ->where(function ($q) {
                $q->whereIn('verification_status', [AiResearchClaim::STATUS_VERIFIED, AiResearchClaim::STATUS_PROMOTED])
                    ->orWhere(function ($sub) {
                        $sub->where('verification_status', AiResearchClaim::STATUS_PARTIALLY_SUPPORTED)
                            ->whereNotNull('promoted_entry_id');
                    });
            })
            ->count();

        $distinctDomains = AiResearchSource::query()
            ->where('topic_id', $topic->id)
            ->whereIn('status', [AiResearchSource::STATUS_FETCHED, AiResearchSource::STATUS_EXTRACTED])
            ->distinct('domain')
            ->count('domain');

        $stage = 1;
        if ($fetchedSources >= 1 && $acceptedClaims >= 1) {
            $stage = 2;
        }
        if ($distinctDomains >= 2 && $acceptedClaims >= 2) {
            $stage = 3;
        }
        if ($distinctDomains >= 2 && $acceptedClaims >= 4) {
            $stage = 4;
        }
        if ($distinctDomains >= 3 && $acceptedClaims >= 6) {
            $stage = 5;
        }
        if ($distinctDomains >= 3 && $acceptedClaims >= 8) {
            $stage = 6;
        }
        if ($distinctDomains >= 4 && $acceptedClaims >= 10) {
            $stage = 7;
        }

        $qualityScore = min(100, ($fetchedSources * 12) + ($acceptedClaims * 9) + ($verifiedClaims * 6) + ($distinctDomains * 10));
        $recheckDays = (int) config('ai_brain.research.recheck_days', 14);
        $goalDepth = (int) ($topic->goal?->max_depth ?? 7);
        $resolvedStage = min($goalDepth, max((int) $topic->learning_stage, $stage));
        $isCompleted = $resolvedStage >= $goalDepth;

        // Chủ đề CHƯA hoàn thành lộ trình sẽ học liên tục (30p - 2h), chỉ giãn lịch nhiều ngày sau khi đã hoàn thành!
        $nextResearchAt = $isCompleted
            ? now()->addDays($recheckDays)
            : ($fetchedSources < 3 ? now()->addMinutes(30) : now()->addHours(2));

        $topic->update([
            'source_coverage' => $fetchedSources,
            'evidence_count' => $acceptedClaims,
            'quality_score' => $qualityScore,
            'learning_stage' => $resolvedStage,
            'review_state' => $acceptedClaims > 0 ? 'verified' : 'unreviewed',
            'last_researched_at' => now(),
            'status' => $isCompleted ? AiResearchTopic::STATUS_COMPLETED : AiResearchTopic::STATUS_SCHEDULED,
            'next_research_at' => $nextResearchAt,
        ]);
    }

    /**
     * Phân rã mục tiêu Admin nhập thành 4 chủ đề con có cấu trúc (dùng Planner AI nếu sẵn sàng, fallback sạch bằng code).
     *
     * @return list<array{title: string, objectives: list<string>, queries: list<string>, unknown_concepts: list<string>}>
     */
    protected function decomposeGoalIntoSubtopics(AiResearchGoal $goal): array
    {
        $policy = $this->governance->getPolicy();
        $budget = $this->governance->hasRemainingLearningBudget();

        if (! $policy['kill_switch'] && $budget['allowed'] && trim((string) $policy['draft_model']) !== '') {
            $sys = 'Bạn là Curriculum Planner AI. Hãy phân rã mục tiêu học thành 4 chủ đề con nghiên cứu từ nền tảng đến ứng dụng bằng tiếng Việt. CHỈ TRẢ VỀ JSON: {"topics":[{"title":"...","objectives":["..."],"queries":["truy vấn tiếng Việt 1","truy vấn 2"],"unknown_concepts":["khái niệm mở rộng"]}]}';
            $user = "Mục tiêu: {$goal->title}\nLĩnh vực: {$goal->domain}\nMô tả: {$goal->description}\nQuốc gia: {$goal->country}, Ngôn ngữ: {$goal->language}";

            $res = $this->providerClient->callJson($sys, $user, $policy['draft_model'], 0.25, 900, 10);
            if ($res['ok'] && ! empty($res['data']['topics']) && is_array($res['data']['topics'])) {
                $valid = array_values(array_filter($res['data']['topics'], fn ($x) => is_array($x) && ! empty($x['title'])));
                if ($valid !== []) {
                    return array_slice($valid, 0, 5);
                }
            }
        }

        // Deterministic fallback phân rã có cấu trúc theo 4 trụ cột khi chưa gọi LLM
        $t = $goal->title;
        $ctx = strtoupper((string) $goal->country) === 'VN' ? ' tại Việt Nam' : '';

        return [
            [
                'title' => "Khái niệm nền tảng và thuật ngữ cốt lõi về {$t}",
                'objectives' => ["Hiểu khái niệm cơ bản và phạm vi của {$t}"],
                'queries' => ["{$t} là gì{$ctx}", "tổng quan {$t}{$ctx}"],
                'unknown_concepts' => ["phân loại {$t}"],
            ],
            [
                'title' => "Quy trình thực hành và phương pháp chuẩn cho {$t}",
                'objectives' => ["Nắm các bước triển khai và quy chuẩn phổ biến về {$t}"],
                'queries' => ["quy trình {$t}{$ctx}", "hướng dẫn {$t} chi tiết"],
                'unknown_concepts' => ["lỗi thường gặp khi làm {$t}"],
            ],
            [
                'title' => "Xu hướng thị trường và kinh nghiệm thực tế về {$t}",
                'objectives' => ["Tổng hợp xu hướng mới và nhu cầu khách hàng xoay quanh {$t}"],
                'queries' => ["xu hướng {$t}{$ctx}", "kinh nghiệm {$t} thực tế"],
                'unknown_concepts' => ["tối ưu trải nghiệm người dùng {$t}"],
            ],
            [
                'title' => "Ứng dụng và cải tiến dịch vụ liên quan đến {$t}",
                'objectives' => ['Đánh giá các ý tưởng áp dụng hợp lý cho sản phẩm dịch vụ'],
                'queries' => ["giải pháp công nghệ {$t}{$ctx}", "cải thiện hiệu quả {$t}"],
                'unknown_concepts' => ["đo lường hiệu quả {$t}"],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    protected function parseDomainList(mixed $raw): array
    {
        if (is_array($raw)) {
            $items = $raw;
        } else {
            $items = preg_split('/[\s,;]+/', trim((string) $raw)) ?: [];
        }

        return array_values(array_unique(array_filter(array_map(
            fn ($v) => strtolower(trim((string) $v)),
            $items
        ))));
    }
}
