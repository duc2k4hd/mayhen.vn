<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Models\AiChatbotLog;
use App\Models\AiCustomerMemory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Authorized Scoped Customer Memory Service.
 * - Cô lập tuyệt đối theo tài khoản (user:{id}) hoặc phiên (session:{hash}) -> chống cross-tenant leakage.
 * - Tuyệt đối KHÔNG lưu PII, mật khẩu hay khẳng định chưa xác minh từ khách hàng -> chống memory poisoning.
 * - Tuân thủ Retention & quyền xóa dữ liệu (DSAR).
 */
class CustomerMemoryService
{
    public function __construct(
        protected AiGovernanceService $governance
    ) {}

    public function buildScopeKey(?int $userId, string $sessionHash): string
    {
        if ($userId !== null && $userId > 0) {
            return 'user:'.$userId;
        }

        return 'session:'.substr($sessionHash, 0, 48);
    }

    /**
     * Đọc ngữ cảnh nhu cầu được phép của đúng phạm vi người dùng hiện tại.
     *
     * @return array{preferred_styles: list<string>, budget_tier: ?string, recent_topics: list<string>, last_intent: ?string, interaction_count: int}|null
     */
    public function recall(?int $userId, string $sessionHash): ?array
    {
        if (! $this->governance->getPolicy()['memory_enabled'] || ! Schema::hasTable('ai_customer_memories')) {
            return null;
        }

        $scopeKey = $this->buildScopeKey($userId, $sessionHash);
        $memory = AiCustomerMemory::query()
            ->where('scope_key', $scopeKey)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $memory) {
            return null;
        }

        return [
            'preferred_styles' => array_values(array_map('strval', (array) ($memory->preferred_styles ?? []))),
            'budget_tier' => $memory->budget_tier ? (string) $memory->budget_tier : null,
            'recent_topics' => array_values(array_map('strval', (array) ($memory->recent_topics ?? []))),
            'last_intent' => $memory->last_intent ? (string) $memory->last_intent : null,
            'interaction_count' => (int) $memory->interaction_count,
        ];
    }

    /**
     * Cập nhật tín hiệu nhu cầu an toàn (chỉ các nhãn enum định sẵn, tuyệt đối không lưu văn bản tự do/PII từ khách).
     */
    public function recordSafeSignals(?int $userId, string $sessionHash, string $cleanMessage, string $intent): void
    {
        if (! $this->governance->getPolicy()['memory_enabled'] || ! Schema::hasTable('ai_customer_memories')) {
            return;
        }

        $scopeKey = $this->buildScopeKey($userId, $sessionHash);
        $ascii = Str::ascii(mb_strtolower($cleanMessage, 'UTF-8'));

        $detectedStyles = [];
        $styleMap = [
            'luxury' => ['sang trong', 'hoang gia', 'luxury', 'cao cap', 'tem sap'],
            'modern' => ['hien dai', 'toi gian', 'minimal', 'don gian'],
            'floral' => ['hoa', 'lang man', 'floral', 'pastel'],
            'traditional' => ['truyen thong', 'mau do', 'song hy', 'gia tien'],
        ];
        foreach ($styleMap as $style => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($ascii, $kw)) {
                    $detectedStyles[] = $style;
                    break;
                }
            }
        }

        $budgetTier = null;
        if (str_contains($ascii, 'mien phi') || str_contains($ascii, 'free') || str_contains($ascii, '0d')) {
            $budgetTier = 'free';
        } elseif (str_contains($ascii, 'pro') || str_contains($ascii, 'plus') || str_contains($ascii, 'premium') || str_contains($ascii, 'nang cap')) {
            $budgetTier = 'paid';
        }

        $retentionDays = $this->governance->getPolicy()['retention_days'];
        $memory = AiCustomerMemory::query()->firstOrNew(['scope_key' => $scopeKey]);

        $existingStyles = array_values(array_map('strval', (array) ($memory->preferred_styles ?? [])));
        $mergedStyles = array_values(array_unique(array_merge($existingStyles, $detectedStyles)));
        $mergedStyles = array_slice($mergedStyles, -4);

        $existingTopics = array_values(array_map('strval', (array) ($memory->recent_topics ?? [])));
        if (! in_array($intent, ['greeting', 'thanks', 'blocked_security'], true)) {
            $existingTopics[] = $intent;
            $existingTopics = array_values(array_unique(array_slice($existingTopics, -5)));
        }

        $memory->fill([
            'user_id' => $userId,
            'session_hash' => $sessionHash,
            'preferred_styles' => $mergedStyles,
            'budget_tier' => $budgetTier ?? $memory->budget_tier,
            'recent_topics' => $existingTopics,
            'last_intent' => $intent,
            'interaction_count' => (int) ($memory->interaction_count ?? 0) + 1,
            'expires_at' => now()->addDays($retentionDays),
        ]);
        $memory->save();
    }

    /**
     * Xóa sạch dữ liệu bộ nhớ theo yêu cầu quyền riêng tư (DSAR / Right to Erasure).
     */
    public function forgetScope(?int $userId, string $sessionHash): int
    {
        if (! Schema::hasTable('ai_customer_memories')) {
            return 0;
        }

        $scopeKey = $this->buildScopeKey($userId, $sessionHash);

        return AiCustomerMemory::query()->where('scope_key', $scopeKey)->delete();
    }

    /**
     * Dọn dẹp tự động bộ nhớ và nhật ký hội thoại đã hết thời hạn Retention.
     *
     * @return array{memories_deleted: int, logs_deleted: int}
     */
    public function purgeExpired(): array
    {
        $retentionDays = $this->governance->getPolicy()['retention_days'];
        $cutoff = now()->subDays($retentionDays);

        $memoriesDeleted = Schema::hasTable('ai_customer_memories')
            ? AiCustomerMemory::query()
                ->where(function ($q) use ($cutoff) {
                    $q->whereNotNull('expires_at')->where('expires_at', '<=', now())
                        ->orWhere('updated_at', '<=', $cutoff);
                })
                ->delete()
            : 0;

        $logsDeleted = Schema::hasTable('ai_chatbot_logs')
            ? AiChatbotLog::query()->where('created_at', '<=', $cutoff)->delete()
            : 0;

        return [
            'memories_deleted' => (int) $memoriesDeleted,
            'logs_deleted' => (int) $logsDeleted,
        ];
    }
}
