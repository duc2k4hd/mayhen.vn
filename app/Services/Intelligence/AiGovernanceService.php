<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Models\PlatformSetting;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AiGovernanceService
{
    public const CACHE_KEY = 'mayhen_ai_governance_policy_v2';

    public const SETTING_GROUP = 'ai_governance';

    public const SETTING_PREFIX = 'ai_engine_';

    /**
     * Chính sách quản trị hợp nhất cho Mây Hẹn Autonomous Intelligence Engine V2.
     *
     * @return array{
     *     kill_switch: bool,
     *     draft_model: string,
     *     reviewer_model: string,
     *     enable_reviewer_for_complex: bool,
     *     max_review_rounds: int,
     *     memory_enabled: bool,
     *     retention_days: int,
     *     daily_token_budget: int,
     *     allowed_sources: list<string>,
     *     learning_enabled: bool,
     *     learning_paused: bool,
     *     learning_pause_reason: string,
     *     learning_mode: string,
     *     learning_daily_token_budget: int,
     *     learning_daily_cost_budget: int,
     *     learning_max_memory_mb: int,
     *     learning_request_timeout: int,
     *     learning_auto_pause: bool,
     *     admin_approval_required: bool,
     *     external_research_enabled: bool,
     *     external_retrieval: bool,
     *     allow_unverified_promotion: bool,
     *     respect_robots_and_ssrf_guard: bool,
     *     research_country: string,
     *     research_language: string,
     *     research_autonomy_level: string,
     *     research_max_urls_per_cycle: int,
     *     forbidden_topics: list<string>
     * }
     */
    public function getPolicy(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function (): array {
            $defaultDraft = trim((string) config('services.ninerouter.model', ''));
            $defaultReviewer = trim((string) config('services.ninerouter.reviewer_model', '')) ?: $defaultDraft;

            $allowedSourcesRaw = $this->readSetting('allowed_sources', 'approved_knowledge,templates,plans,client_docs');
            $allowedSources = array_values(array_filter(array_map('trim', explode(',', (string) $allowedSourcesRaw))));
            if ($allowedSources === []) {
                $allowedSources = ['approved_knowledge', 'templates', 'plans', 'client_docs'];
            }

            $forbiddenRaw = $this->readSetting('forbidden_topics', '');
            $forbiddenTopics = array_values(array_filter(array_map('trim', explode(',', $forbiddenRaw))));

            $storedDraft = trim((string) $this->readSetting('draft_model', ''));
            $storedReviewer = trim((string) $this->readSetting('reviewer_model', ''));

            $defaultMode = (string) config('ai_brain.mode', 'conservative');
            $storedMode = trim((string) $this->readSetting('learning_mode', $defaultMode));
            if (! in_array($storedMode, ['conservative', 'balanced', 'performance', 'paused'], true)) {
                $storedMode = 'conservative';
            }

            $externalResearchEnabled = filter_var(
                $this->readSetting('external_research_enabled', config('ai_brain.external_research_enabled', true) ? '1' : '0'),
                FILTER_VALIDATE_BOOL
            );

            $autonomy = trim((string) $this->readSetting('research_autonomy_level', (string) config('ai_brain.research.autonomy_level', 'supervised')));
            if (! in_array($autonomy, ['conservative', 'supervised', 'autonomous'], true)) {
                $autonomy = 'supervised';
            }

            return [
                'kill_switch' => filter_var($this->readSetting('kill_switch', '0'), FILTER_VALIDATE_BOOL),
                'draft_model' => $storedDraft !== '' ? $storedDraft : $defaultDraft,
                'reviewer_model' => $storedReviewer !== '' ? $storedReviewer : ($storedDraft !== '' ? $storedDraft : $defaultReviewer),
                'enable_reviewer_for_complex' => filter_var($this->readSetting('enable_reviewer_for_complex', '1'), FILTER_VALIDATE_BOOL),
                'max_review_rounds' => max(0, min(2, (int) $this->readSetting('max_review_rounds', '1'))),
                'memory_enabled' => filter_var($this->readSetting('memory_enabled', '1'), FILTER_VALIDATE_BOOL),
                'retention_days' => max(7, min(365, (int) $this->readSetting('retention_days', '90'))),
                'daily_token_budget' => max(10_000, min(50_000_000, (int) $this->readSetting('daily_token_budget', '500000'))),
                'allowed_sources' => $allowedSources,

                // Autonomous Learning V2/V3 Governance
                'learning_enabled' => filter_var(
                    $this->readSetting('learning_enabled', config('ai_brain.learning_enabled', false) ? '1' : '0'),
                    FILTER_VALIDATE_BOOL
                ),
                'learning_paused' => filter_var($this->readSetting('learning_paused', '0'), FILTER_VALIDATE_BOOL),
                'learning_pause_reason' => trim((string) $this->readSetting('learning_pause_reason', '')),
                'learning_mode' => $storedMode,
                'learning_daily_token_budget' => max(0, (int) $this->readSetting('learning_daily_token_budget', (string) config('ai_brain.daily_token_budget', 0))),
                'learning_daily_cost_budget' => max(0, (int) $this->readSetting('learning_daily_cost_budget', (string) config('ai_brain.daily_cost_budget', 0))),
                'learning_max_memory_mb' => max(64, min(1024, (int) $this->readSetting('learning_max_memory_mb', (string) config('ai_brain.max_memory_mb', 128)))),
                'learning_request_timeout' => max(5, min(60, (int) $this->readSetting('learning_request_timeout', (string) config('ai_brain.request_timeout_seconds', 25)))),
                'learning_auto_pause' => filter_var(
                    $this->readSetting('learning_auto_pause', config('ai_brain.auto_pause', true) ? '1' : '0'),
                    FILTER_VALIDATE_BOOL
                ),

                // V3 Autonomous Web Research Governance
                'external_research_enabled' => $externalResearchEnabled,
                'external_retrieval' => $externalResearchEnabled,
                'research_country' => strtoupper(trim((string) $this->readSetting('research_country', (string) config('ai_brain.research.country', 'VN')))) ?: 'VN',
                'research_language' => strtolower(trim((string) $this->readSetting('research_language', (string) config('ai_brain.research.language', 'vi')))) ?: 'vi',
                'research_autonomy_level' => $autonomy,
                'research_max_urls_per_cycle' => max(1, min(6, (int) $this->readSetting('research_max_urls_per_cycle', (string) config('ai_brain.research.max_urls_per_cycle', 2)))),

                // Hard safety invariants V3: bắt buộc Admin duyệt tri thức chính thức, cấm quảng bá chưa kiểm chứng, luôn tôn trọng robots.txt & SSRF Guard
                'admin_approval_required' => true,
                'allow_unverified_promotion' => false,
                'respect_robots_and_ssrf_guard' => true,
                'forbidden_topics' => $forbiddenTopics,
            ];
        });
    }

    /**
     * Cập nhật chính sách AI Governance bởi Admin có thẩm quyền và ghi Audit Log.
     *
     * @param  array<string, mixed>  $input
     */
    public function updatePolicy(array $input, ?int $adminUserId = null): void
    {
        $current = $this->getPolicy();
        $allowedList = ['approved_knowledge', 'templates', 'plans', 'client_docs'];
        $selectedSources = array_values(array_intersect(
            $allowedList,
            array_map('strval', (array) ($input['allowed_sources'] ?? $current['allowed_sources']))
        ));
        if ($selectedSources === []) {
            $selectedSources = ['approved_knowledge'];
        }

        $modeInput = array_key_exists('learning_mode', $input) ? (string) $input['learning_mode'] : $current['learning_mode'];
        if (! in_array($modeInput, ['conservative', 'balanced', 'performance', 'paused'], true)) {
            $modeInput = 'conservative';
        }

        $forbiddenInput = array_key_exists('forbidden_topics', $input)
            ? (is_array($input['forbidden_topics'])
                ? implode(',', array_filter(array_map('trim', $input['forbidden_topics'])))
                : trim((string) $input['forbidden_topics']))
            : implode(',', $current['forbidden_topics']);

        $pairs = [
            'kill_switch' => ! empty($input['kill_switch']) ? '1' : '0',
            'draft_model' => array_key_exists('draft_model', $input) ? trim((string) ($input['draft_model'] ?? '')) : $current['draft_model'],
            'reviewer_model' => array_key_exists('reviewer_model', $input) ? trim((string) ($input['reviewer_model'] ?? '')) : $current['reviewer_model'],
            'enable_reviewer_for_complex' => ! empty($input['enable_reviewer_for_complex']) ? '1' : '0',
            'max_review_rounds' => (string) max(0, min(2, (int) ($input['max_review_rounds'] ?? $current['max_review_rounds']))),
            'memory_enabled' => ! empty($input['memory_enabled']) ? '1' : '0',
            'retention_days' => (string) max(7, min(365, (int) ($input['retention_days'] ?? $current['retention_days']))),
            'daily_token_budget' => (string) max(10_000, min(50_000_000, (int) ($input['daily_token_budget'] ?? $current['daily_token_budget']))),
            'allowed_sources' => implode(',', $selectedSources),

            // V2 Learning Policy (bảo toàn giá trị cũ nếu request không truyền các khóa V2)
            'learning_enabled' => array_key_exists('learning_enabled', $input)
                ? (! empty($input['learning_enabled']) ? '1' : '0')
                : ($current['learning_enabled'] ? '1' : '0'),
            'learning_mode' => $modeInput,
            'learning_daily_token_budget' => (string) max(0, (int) ($input['learning_daily_token_budget'] ?? $current['learning_daily_token_budget'])),
            'learning_daily_cost_budget' => (string) max(0, (int) ($input['learning_daily_cost_budget'] ?? $current['learning_daily_cost_budget'])),
            'learning_max_memory_mb' => (string) max(64, min(1024, (int) ($input['learning_max_memory_mb'] ?? $current['learning_max_memory_mb']))),
            'learning_request_timeout' => (string) max(5, min(60, (int) ($input['learning_request_timeout'] ?? $current['learning_request_timeout']))),
            'learning_auto_pause' => array_key_exists('learning_auto_pause', $input)
                ? (! empty($input['learning_auto_pause']) ? '1' : '0')
                : ($current['learning_auto_pause'] ? '1' : '0'),
            'external_research_enabled' => array_key_exists('external_research_enabled', $input)
                ? (! empty($input['external_research_enabled']) ? '1' : '0')
                : ($current['external_research_enabled'] ? '1' : '0'),
            'research_country' => array_key_exists('research_country', $input)
                ? strtoupper(substr(trim((string) $input['research_country']), 0, 10)) ?: 'VN'
                : $current['research_country'],
            'research_language' => array_key_exists('research_language', $input)
                ? strtolower(substr(trim((string) $input['research_language']), 0, 10)) ?: 'vi'
                : $current['research_language'],
            'research_autonomy_level' => array_key_exists('research_autonomy_level', $input)
                && in_array((string) $input['research_autonomy_level'], ['conservative', 'supervised', 'autonomous'], true)
                ? (string) $input['research_autonomy_level']
                : $current['research_autonomy_level'],
            'research_max_urls_per_cycle' => (string) max(1, min(6, (int) ($input['research_max_urls_per_cycle'] ?? $current['research_max_urls_per_cycle']))),
            'forbidden_topics' => $forbiddenInput,
        ];

        $this->persistPairs($pairs, $adminUserId);

        AuditLogger::log(
            'AI_GOVERNANCE_UPDATED',
            'AiGovernance',
            $adminUserId ?? 0,
            'Cập nhật chính sách quản trị Mây Hẹn Intelligence Engine V2',
            $pairs
        );
    }

    /**
     * Emergency Control: PAUSE ALL AI LEARNING / RESUME có hiệu lực tức thì ở tầng backend (DB + Cache).
     * Không làm gián đoạn chatbot real-time phục vụ khách hàng.
     */
    public function setLearningPaused(bool $paused, ?int $adminUserId = null, string $reason = ''): void
    {
        $cleanReason = trim(mb_substr($reason, 0, 190, 'UTF-8'));
        $pairs = [
            'learning_paused' => $paused ? '1' : '0',
            'learning_pause_reason' => $paused ? ($cleanReason !== '' ? $cleanReason : 'Tạm dừng khẩn cấp bởi Quản trị viên') : '',
        ];

        $this->persistPairs($pairs, $adminUserId);

        AuditLogger::log(
            $paused ? 'AI_LEARNING_EMERGENCY_PAUSED' : 'AI_LEARNING_RESUMED',
            'AiGovernance',
            $adminUserId ?? 0,
            $paused
                ? 'Kích hoạt PAUSE ALL AI LEARNING: '.($pairs['learning_pause_reason'])
                : 'Gỡ trạng thái tạm dừng AI Learning',
            $pairs
        );
    }

    /**
     * Kiểm tra toàn diện xem Learning Engine có được phép thực thi hay không.
     * Lưu ý: ngân sách bằng 0 = CHẶN cho đến khi Admin cấu hình rõ ràng.
     *
     * @return array{allowed: bool, code: string, reason: string}
     */
    public function isLearningOperational(bool $requireBudgetForLlm = false): array
    {
        $policy = $this->getPolicy();

        if ($policy['kill_switch']) {
            return ['allowed' => false, 'code' => 'kill_switch', 'reason' => 'AI Kill Switch khẩn cấp đang bật.'];
        }

        if ($policy['learning_paused']) {
            return [
                'allowed' => false,
                'code' => 'learning_paused',
                'reason' => 'AI Learning đang tạm dừng: '.($policy['learning_pause_reason'] ?: 'PAUSE ALL AI LEARNING đang kích hoạt.'),
            ];
        }

        if ($policy['learning_mode'] === 'paused') {
            return ['allowed' => false, 'code' => 'mode_paused', 'reason' => 'Chế độ học nền đang đặt là Paused.'];
        }

        if (! $policy['learning_enabled']) {
            return ['allowed' => false, 'code' => 'learning_disabled', 'reason' => 'AI Learning đang tắt (AI_LEARNING_ENABLED=false).'];
        }

        if ($requireBudgetForLlm) {
            return $this->hasRemainingLearningBudget();
        }

        return ['allowed' => true, 'code' => 'ok', 'reason' => 'Sẵn sàng vận hành'];
    }

    /**
     * Kiểm tra ngân sách dành riêng cho Learning Engine.
     * Giá trị 0 = CHẶN sử dụng LLM cho đến khi Admin cấu hình ngân sách > 0.
     *
     * @return array{allowed: bool, code: string, reason: string}
     */
    public function hasRemainingLearningBudget(): array
    {
        $policy = $this->getPolicy();
        $tokenBudget = (int) $policy['learning_daily_token_budget'];
        $costBudget = (int) $policy['learning_daily_cost_budget'];

        if ($tokenBudget <= 0) {
            return [
                'allowed' => false,
                'code' => 'token_budget_zero',
                'reason' => 'Ngân sách Token học nền hằng ngày đang bằng 0 (chặn mặc định cho tới khi Admin cấu hình > 0).',
            ];
        }

        $usedTokens = $this->getTodayLearningTokensUsed();
        if ($usedTokens >= $tokenBudget) {
            return [
                'allowed' => false,
                'code' => 'token_budget_exceeded',
                'reason' => "Đã hết ngân sách Token học nền hôm nay ({$usedTokens}/{$tokenBudget}).",
            ];
        }

        if ($costBudget <= 0) {
            return [
                'allowed' => false,
                'code' => 'cost_budget_zero',
                'reason' => 'Ngân sách Chi phí học nền hằng ngày đang bằng 0 (chặn mặc định cho tới khi Admin cấu hình > 0).',
            ];
        }

        $usedCost = $this->getTodayLearningCostUsed();
        if ($usedCost >= $costBudget) {
            return [
                'allowed' => false,
                'code' => 'cost_budget_exceeded',
                'reason' => "Đã hết ngân sách Chi phí học nền hôm nay ({$usedCost}/{$costBudget} VNĐ).",
            ];
        }

        return ['allowed' => true, 'code' => 'ok', 'reason' => 'Còn hạn mức học nền'];
    }

    public function recordLearningUsage(int $tokens, float $costVnd = 0.0): void
    {
        if ($tokens > 0) {
            $tokenKey = $this->dailyLearningTokenCacheKey();
            Cache::put($tokenKey, (int) Cache::get($tokenKey, 0) + $tokens, now()->endOfDay());
        }

        if ($costVnd > 0.0) {
            $costKey = $this->dailyLearningCostCacheKey();
            Cache::put($costKey, round((float) Cache::get($costKey, 0.0) + $costVnd, 2), now()->endOfDay());
        }
    }

    public function getTodayLearningTokensUsed(): int
    {
        return (int) Cache::get($this->dailyLearningTokenCacheKey(), 0);
    }

    public function getTodayLearningCostUsed(): float
    {
        return round((float) Cache::get($this->dailyLearningCostCacheKey(), 0.0), 2);
    }

    public function isTopicForbidden(string $topic): bool
    {
        $forbidden = $this->getPolicy()['forbidden_topics'];

        return in_array(strtolower(trim($topic)), array_map('strtolower', $forbidden), true);
    }

    public function isKillSwitchActive(): bool
    {
        return (bool) $this->getPolicy()['kill_switch'];
    }

    public function isSourceAllowed(string $source): bool
    {
        return in_array($source, $this->getPolicy()['allowed_sources'], true);
    }

    public function hasRemainingDailyTokenBudget(): bool
    {
        $used = (int) Cache::get($this->dailyTokenCacheKey(), 0);

        return $used < $this->getPolicy()['daily_token_budget'];
    }

    public function recordDailyTokens(int $tokens): void
    {
        if ($tokens <= 0) {
            return;
        }

        $key = $this->dailyTokenCacheKey();
        $current = (int) Cache::get($key, 0);
        Cache::put($key, $current + $tokens, now()->endOfDay());
    }

    public function getTodayTokensUsed(): int
    {
        return (int) Cache::get($this->dailyTokenCacheKey(), 0);
    }

    /**
     * @param  array<string, string>  $pairs
     */
    protected function persistPairs(array $pairs, ?int $adminUserId = null): void
    {
        if (Schema::hasTable('platform_settings')) {
            foreach ($pairs as $key => $val) {
                $fullKey = self::SETTING_PREFIX.$key;
                $existing = PlatformSetting::query()
                    ->where('group', self::SETTING_GROUP)
                    ->where('key', $fullKey)
                    ->first();

                PlatformSetting::query()->updateOrCreate(
                    ['group' => self::SETTING_GROUP, 'key' => $fullKey],
                    [
                        'label' => 'AI Governance: '.$key,
                        'description' => 'Chính sách vận hành Mây Hẹn Autonomous Intelligence Engine V2',
                        'value' => $val,
                        'is_secret' => false,
                        'version' => ((int) ($existing?->version ?? 0)) + 1,
                        'updated_by' => $adminUserId,
                    ]
                );
            }
            PlatformSetting::clearRuntimeCache();
        }

        Cache::forget(self::CACHE_KEY);
    }

    protected function readSetting(string $shortKey, string $default): string
    {
        if (! Schema::hasTable('platform_settings')) {
            return $default;
        }

        $val = PlatformSetting::getValue(self::SETTING_GROUP, self::SETTING_PREFIX.$shortKey, $default);

        return $val !== null ? (string) $val : $default;
    }

    protected function dailyTokenCacheKey(): string
    {
        return 'mayhen_ai_tokens_used_'.now()->format('Y-m-d');
    }

    protected function dailyLearningTokenCacheKey(): string
    {
        return 'mayhen_ai_learning_tokens_'.now()->format('Y-m-d');
    }

    protected function dailyLearningCostCacheKey(): string
    {
        return 'mayhen_ai_learning_cost_'.now()->format('Y-m-d');
    }
}
