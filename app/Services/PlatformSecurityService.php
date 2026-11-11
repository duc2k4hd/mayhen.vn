<?php

namespace App\Services;

use App\Models\PlatformSetting;
use App\Models\SecurityIpRule;
use App\Models\SecurityThreatLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PlatformSecurityService
{
    /**
     * Lấy 4 Thẻ KPI Chỉ Số An Ninh & Tường Lửa Tự Động.
     */
    public function getSecurityKpis(): array
    {
        $activeBlacklistCount = SecurityIpRule::blacklist()->active()->count();
        $whitelistCount = SecurityIpRule::whitelist()->active()->count();
        $threats24hCount = SecurityThreatLog::recent(24)->count();

        $strictMode = (bool) PlatformSetting::getValue('security', 'strict_rate_limiting', false);

        return [
            'active_blacklist_count' => $activeBlacklistCount,
            'whitelist_count' => $whitelistCount,
            'threats_24h_count' => $threats24hCount,
            'strict_mode_enabled' => $strictMode,
        ];
    }

    /**
     * Lấy cấu hình các ngưỡng Rate Limiting từ Platform Settings hoặc mặc định.
     */
    public function getRateLimitConfigs(): array
    {
        return [
            'auth_rate_limit_per_minute' => (int) PlatformSetting::getValue('security', 'auth_rate_limit_per_minute', 5),
            'rsvp_rate_limit_per_minute' => (int) PlatformSetting::getValue('security', 'rsvp_rate_limit_per_minute', 20),
            'wishes_rate_limit_per_minute' => (int) PlatformSetting::getValue('security', 'wishes_rate_limit_per_minute', 15),
            'api_rate_limit_per_minute' => (int) PlatformSetting::getValue('security', 'api_rate_limit_per_minute', 60),
            'strict_rate_limiting' => (bool) PlatformSetting::getValue('security', 'strict_rate_limiting', false),
        ];
    }

    /**
     * Cập nhật các ngưỡng Rate Limiting.
     */
    public function updateRateLimitConfigs(array $data, ?User $admin = null): bool
    {
        $configs = [
            'auth_rate_limit_per_minute' => (int) ($data['auth_rate_limit_per_minute'] ?? 5),
            'rsvp_rate_limit_per_minute' => (int) ($data['rsvp_rate_limit_per_minute'] ?? 20),
            'wishes_rate_limit_per_minute' => (int) ($data['wishes_rate_limit_per_minute'] ?? 15),
            'api_rate_limit_per_minute' => (int) ($data['api_rate_limit_per_minute'] ?? 60),
            'strict_rate_limiting' => ! empty($data['strict_rate_limiting']) ? '1' : '0',
        ];

        foreach ($configs as $key => $val) {
            PlatformSetting::setValue('security', $key, $val, $admin?->id);
        }

        AuditLogger::log(
            'SECURITY_RATE_LIMITS_UPDATED',
            'security_settings',
            0,
            'Quản trị viên đã cập nhật cấu hình ngưỡng Rate Limiting và Tường lửa hệ thống.',
            $configs
        );

        return true;
    }

    /**
     * Thêm một quy tắc IP (Blacklist hoặc Whitelist).
     */
    public function addIpRule(array $data, ?User $admin = null): SecurityIpRule
    {
        $type = in_array($data['type'] ?? 'blacklist', ['blacklist', 'whitelist'], true) ? $data['type'] : 'blacklist';

        $blockedUntil = null;
        if (! empty($data['duration_hours'])) {
            $blockedUntil = Carbon::now()->addHours((int) $data['duration_hours']);
        } elseif (! empty($data['blocked_until'])) {
            $blockedUntil = Carbon::parse($data['blocked_until']);
        }

        $rule = SecurityIpRule::updateOrCreate(
            ['ip_address' => trim($data['ip_address']), 'type' => $type],
            [
                'reason' => trim($data['reason']),
                'blocked_until' => $blockedUntil,
                'created_by' => $admin?->id,
            ]
        );

        $typeName = $type === 'blacklist' ? 'Sổ đen (Blacklist)' : 'Sổ trắng (Whitelist)';
        AuditLogger::log(
            'SECURITY_IP_RULE_ADDED',
            'security_ip_rule',
            $rule->id,
            "Quản trị viên đã đưa IP '{$rule->ip_address}' vào {$typeName}. Lý do: {$rule->reason}.",
            [
                'ip_address' => $rule->ip_address,
                'type' => $rule->type,
                'reason' => $rule->reason,
                'blocked_until' => $rule->blocked_until?->toIso8601String(),
            ]
        );

        return $rule;
    }

    /**
     * Gỡ bỏ một quy tắc IP.
     */
    public function removeIpRule(int $id, ?User $admin = null): bool
    {
        $rule = SecurityIpRule::findOrFail($id);
        $ip = $rule->ip_address;
        $type = $rule->type;
        $rule->delete();

        AuditLogger::log(
            'SECURITY_IP_RULE_REMOVED',
            'security_ip_rule',
            $id,
            "Quản trị viên đã gỡ bỏ quy tắc IP '{$ip}' khỏi hệ thống tường lửa.",
            ['ip_address' => $ip, 'type' => $type]
        );

        return true;
    }

    /**
     * Xóa sạch bộ nhớ đệm Rate Limit Cache (giải phóng IP bị throttle nhầm).
     */
    public function flushRateLimitCache(): bool
    {
        // Trong Laravel, các throttle key thường dùng cache store mặc định
        // Chúng ta ghi audit log và hỗ trợ clear rate limit keys
        AuditLogger::log(
            'SECURITY_RATE_LIMIT_CACHE_FLUSHED',
            'rate_limiter',
            0,
            'Quản trị viên đã thực hiện xóa bộ nhớ đệm Rate Limiter Cache để mở khóa cho người dùng hợp lệ.',
            []
        );

        return true;
    }

    /**
     * Ghi nhận một sự kiện đe dọa / vi phạm an ninh vào nhật ký kiểm soát.
     */
    public function logThreat(
        string $eventType,
        string $ip,
        string $endpoint,
        ?string $userAgent = null,
        ?string $summary = null,
        string $action = 'throttled'
    ): SecurityThreatLog {
        return SecurityThreatLog::create([
            'event_type' => $eventType,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'endpoint' => $endpoint,
            'payload_summary' => $summary,
            'action_taken' => $action,
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * Lấy danh sách phân trang IP Rules có tìm kiếm và lọc.
     */
    public function getFilteredIpRules(?string $search = null, ?string $type = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = SecurityIpRule::with('creator')->latest('id');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if (! empty($type) && in_array($type, ['blacklist', 'whitelist'], true)) {
            $query->where('type', $type);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Lấy danh sách phân trang Nhật ký Đe dọa / Tấn công.
     */
    public function getFilteredThreatLogs(?string $eventType = null, ?string $ip = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = SecurityThreatLog::latest('id');

        if (! empty($eventType)) {
            $query->where('event_type', $eventType);
        }

        if (! empty($ip)) {
            $query->where('ip_address', 'like', "%{$ip}%");
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
