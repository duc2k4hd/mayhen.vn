<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SystemFeatureFlag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

class FeatureFlagService
{
    protected const CACHE_PREFIX = 'system_ff_';

    protected const CACHE_TTL = 600; // 10 phút

    /**
     * Kiểm tra trạng thái cờ tính năng cho một người dùng hoặc phiên hiện tại
     */
    public function isEnabled(string $key, ?User $user = null): bool
    {
        $flag = $this->findCachedFlag($key);

        if (! $flag) {
            return false;
        }

        // Cầu dao khẩn cấp (Kill Switch) có quyền ưu tiên cao nhất
        if ($flag->kill_switch_active) {
            return false;
        }

        // Trạng thái bật/tắt toàn cục
        if (! $flag->is_enabled) {
            return false;
        }

        $user = $user ?: Auth::user();

        // Kiểm tra phạm vi vai trò (Target Roles) nếu có quy định
        if (! empty($flag->target_roles) && is_array($flag->target_roles)) {
            if (! $user) {
                return false;
            }

            $userRole = $user->role ?? 'customer';
            if (! in_array($userRole, $flag->target_roles, true) && ! in_array('all', $flag->target_roles, true)) {
                return false;
            }
        }

        // Tỷ lệ phát hành an toàn (Rollout Percentage)
        $percentage = (int) $flag->rollout_percentage;

        if ($percentage >= 100) {
            return true;
        }

        if ($percentage <= 0) {
            return false;
        }

        // Thuật toán băm nhất quán (Consistent Hashing) để cùng 1 user/guest có trải nghiệm đồng nhất
        $identifier = $user?->id ?: (Request::session()?->getId() ?: Request::ip() ?: 'guest');
        $hashValue = abs(crc32($key.':'.$identifier)) % 100;

        return $hashValue < $percentage;
    }

    /**
     * Lấy bản ghi cờ tính năng từ Cache hoặc Database
     */
    public function findCachedFlag(string $key): ?SystemFeatureFlag
    {
        return Cache::remember(self::CACHE_PREFIX.$key, self::CACHE_TTL, function () use ($key) {
            return SystemFeatureFlag::where('key', $key)->first();
        });
    }

    /**
     * Xóa cache của 1 cờ tính năng hoặc toàn bộ cờ
     */
    public function clearCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX.$key);
    }

    /**
     * Lấy danh sách cờ tính năng theo bộ lọc
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, SystemFeatureFlag>
     */
    public function getAllFlags(array $filters = []): Collection
    {
        $query = SystemFeatureFlag::with('lastToggledBy')->orderBy('category')->orderBy('name');

        if (! empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            match ($filters['status']) {
                'active' => $query->where('is_enabled', true)->where('rollout_percentage', 100)->where('kill_switch_active', false),
                'beta' => $query->where('is_enabled', true)->where('rollout_percentage', '<', 100)->where('kill_switch_active', false),
                'kill_switched' => $query->where('kill_switch_active', true),
                'disabled' => $query->where('is_enabled', false)->where('kill_switch_active', false),
                default => null,
            };
        }

        if (! empty($filters['search'])) {
            $keyword = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('key', 'like', $keyword)
                    ->orWhere('name', 'like', $keyword)
                    ->orWhere('description', 'like', $keyword);
            });
        }

        return $query->get();
    }

    /**
     * Thống kê các chỉ số sức khỏe & phát hành cờ tính năng
     *
     * @return array<string, int>
     */
    public function getMetrics(): array
    {
        $total = SystemFeatureFlag::count();
        $killSwitched = SystemFeatureFlag::where('kill_switch_active', true)->count();
        $active = SystemFeatureFlag::where('is_enabled', true)
            ->where('rollout_percentage', 100)
            ->where('kill_switch_active', false)
            ->count();
        $beta = SystemFeatureFlag::where('is_enabled', true)
            ->where('rollout_percentage', '<', 100)
            ->where('kill_switch_active', false)
            ->count();
        $disabled = SystemFeatureFlag::where('is_enabled', false)
            ->where('kill_switch_active', false)
            ->count();

        return [
            'total_flags' => $total,
            'fully_active' => $active,
            'beta_testing' => $beta,
            'kill_switched' => $killSwitched,
            'disabled' => $disabled,
        ];
    }

    /**
     * Bật/tắt trạng thái cờ tính năng
     */
    public function toggle(string $key, bool $enabled, ?int $adminId = null): SystemFeatureFlag
    {
        $flag = SystemFeatureFlag::where('key', $key)->firstOrFail();
        $oldState = $flag->is_enabled;

        $flag->is_enabled = $enabled;
        $flag->last_toggled_by = $adminId ?: Auth::id();
        $flag->last_toggled_at = now();
        $flag->save();

        $this->clearCache($key);
        $this->logAudit('toggle_feature_flag', $flag, [
            'key' => $key,
            'old_state' => $oldState,
            'new_state' => $enabled,
        ], $adminId);

        return $flag;
    }

    /**
     * Cập nhật tỷ lệ phát hành an toàn và danh sách vai trò áp dụng
     *
     * @param  array<string>|null  $targetRoles
     */
    public function updateRollout(string $key, int $percentage, ?array $targetRoles = null, ?int $adminId = null): SystemFeatureFlag
    {
        $flag = SystemFeatureFlag::where('key', $key)->firstOrFail();

        $oldPercentage = $flag->rollout_percentage;
        $oldRoles = $flag->target_roles;

        $flag->rollout_percentage = max(0, min(100, $percentage));
        $flag->target_roles = $targetRoles;
        $flag->last_toggled_by = $adminId ?: Auth::id();
        $flag->last_toggled_at = now();
        $flag->save();

        $this->clearCache($key);
        $this->logAudit('update_feature_rollout', $flag, [
            'key' => $key,
            'old_percentage' => $oldPercentage,
            'new_percentage' => $flag->rollout_percentage,
            'old_roles' => $oldRoles,
            'new_roles' => $targetRoles,
        ], $adminId);

        return $flag;
    }

    /**
     * Kích hoạt Cầu Dao Khẩn Cấp (Kill Switch) để đóng băng tính năng ngay lập tức
     */
    public function activateKillSwitch(string $key, string $reason, ?int $adminId = null): SystemFeatureFlag
    {
        $flag = SystemFeatureFlag::where('key', $key)->firstOrFail();

        $flag->kill_switch_active = true;
        $flag->kill_switch_reason = $reason;
        $flag->last_toggled_by = $adminId ?: Auth::id();
        $flag->last_toggled_at = now();
        $flag->save();

        $this->clearCache($key);
        $this->logAudit('activate_kill_switch', $flag, [
            'key' => $key,
            'reason' => $reason,
        ], $adminId);

        return $flag;
    }

    /**
     * Hủy kích hoạt Cầu Dao Khẩn Cấp để khôi phục tính năng về trạng thái bình thường
     */
    public function deactivateKillSwitch(string $key, ?int $adminId = null): SystemFeatureFlag
    {
        $flag = SystemFeatureFlag::where('key', $key)->firstOrFail();

        $flag->kill_switch_active = false;
        $flag->kill_switch_reason = null;
        $flag->last_toggled_by = $adminId ?: Auth::id();
        $flag->last_toggled_at = now();
        $flag->save();

        $this->clearCache($key);
        $this->logAudit('deactivate_kill_switch', $flag, [
            'key' => $key,
        ], $adminId);

        return $flag;
    }

    /**
     * Tạo mới một cờ tính năng
     *
     * @param  array<string, mixed>  $data
     */
    public function createFlag(array $data, ?int $adminId = null): SystemFeatureFlag
    {
        $flag = SystemFeatureFlag::create([
            'key' => $data['key'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'core',
            'is_enabled' => $data['is_enabled'] ?? false,
            'rollout_percentage' => $data['rollout_percentage'] ?? 0,
            'target_roles' => $data['target_roles'] ?? null,
            'kill_switch_active' => false,
            'kill_switch_reason' => null,
            'last_toggled_by' => $adminId ?: Auth::id(),
            'last_toggled_at' => now(),
        ]);

        $this->clearCache($flag->key);
        $this->logAudit('create_feature_flag', $flag, [
            'key' => $flag->key,
            'name' => $flag->name,
            'category' => $flag->category,
        ], $adminId);

        return $flag;
    }

    /**
     * Xóa cờ tính năng an toàn
     */
    public function deleteFlag(string $key, ?int $adminId = null): bool
    {
        $flag = SystemFeatureFlag::where('key', $key)->firstOrFail();

        $this->clearCache($key);
        $this->logAudit('delete_feature_flag', $flag, [
            'key' => $key,
            'name' => $flag->name,
        ], $adminId);

        return (bool) $flag->delete();
    }

    /**
     * Ghi nhật ký kiểm toán hệ thống
     *
     * @param  array<string, mixed>  $details
     */
    protected function logAudit(string $action, SystemFeatureFlag $flag, array $details, ?int $adminId = null): void
    {
        if (class_exists(AuditLog::class)) {
            try {
                AuditLog::create([
                    'actor_id' => $adminId ?: Auth::id(),
                    'actor_type' => User::class,
                    'action' => $action,
                    'auditable_type' => SystemFeatureFlag::class,
                    'auditable_id' => $flag->id,
                    'new_values' => $details,
                    'ip_address' => Request::ip() ?? '127.0.0.1',
                    'user_agent' => Request::userAgent() ?? 'System',
                ]);
            } catch (\Throwable $e) {
                // Không ngắt luồng chính nếu lưu audit log gặp ngoại lệ
            }
        }
    }
}
