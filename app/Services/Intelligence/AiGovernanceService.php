<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Models\PlatformSetting;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AiGovernanceService
{
    public const CACHE_KEY = 'mayhen_ai_governance_policy_v1';

    public const SETTING_GROUP = 'ai_governance';

    public const SETTING_PREFIX = 'ai_engine_';

    /**
     * Chính sách mặc định an toàn cho Mây Hẹn Intelligence Engine.
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
     *     allowed_sources: list<string>
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

            $storedDraft = trim((string) $this->readSetting('draft_model', ''));
            $storedReviewer = trim((string) $this->readSetting('reviewer_model', ''));

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
        $allowedList = ['approved_knowledge', 'templates', 'plans', 'client_docs'];
        $selectedSources = array_values(array_intersect(
            $allowedList,
            array_map('strval', (array) ($input['allowed_sources'] ?? $allowedList))
        ));
        if ($selectedSources === []) {
            $selectedSources = ['approved_knowledge'];
        }

        $pairs = [
            'kill_switch' => ! empty($input['kill_switch']) ? '1' : '0',
            'draft_model' => trim((string) ($input['draft_model'] ?? '')),
            'reviewer_model' => trim((string) ($input['reviewer_model'] ?? '')),
            'enable_reviewer_for_complex' => ! empty($input['enable_reviewer_for_complex']) ? '1' : '0',
            'max_review_rounds' => (string) max(0, min(2, (int) ($input['max_review_rounds'] ?? 1))),
            'memory_enabled' => ! empty($input['memory_enabled']) ? '1' : '0',
            'retention_days' => (string) max(7, min(365, (int) ($input['retention_days'] ?? 90))),
            'daily_token_budget' => (string) max(10_000, min(50_000_000, (int) ($input['daily_token_budget'] ?? 500_000))),
            'allowed_sources' => implode(',', $selectedSources),
        ];

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
                        'description' => 'Chính sách vận hành Mây Hẹn Intelligence Engine',
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

        AuditLogger::log(
            'AI_GOVERNANCE_UPDATED',
            'AiGovernance',
            $adminUserId ?? 0,
            'Cập nhật chính sách quản trị Mây Hẹn Intelligence Engine',
            $pairs
        );
    }

    public function isKillSwitchActive(): bool
    {
        return (bool) $this->getPolicy()['kill_switch'];
    }

    public function isSourceAllowed(string $source): bool
    {
        return in_array($source, $this->getPolicy()['allowed_sources'], true);
    }

    /**
     * Kiểm tra và theo dõi hạn mức token sử dụng trong ngày (tránh vượt chi phí ngoài kiểm soát).
     */
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
}
