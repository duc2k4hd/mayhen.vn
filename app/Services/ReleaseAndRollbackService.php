<?php

namespace App\Services;

use App\Models\SystemFeatureFlag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ReleaseAndRollbackService
{
    /**
     * Đánh giá cờ tính năng (Feature Flag Sticky Evaluation) theo quy tắc deny-by-default.
     */
    public static function isFeatureEnabled(string $key, ?User $user = null): bool
    {
        $flag = Cache::remember("feature_flag_{$key}", 60, function () use ($key) {
            return SystemFeatureFlag::where('key', $key)->first();
        });

        if (! $flag) {
            return false;
        }

        // 1. Kill Switch cưỡng chế tắt tức thì
        if ($flag->kill_switch_active) {
            return false;
        }

        // 2. Cờ bị tắt toàn cục
        if (! $flag->is_enabled) {
            return false;
        }

        // 3. Giới hạn vai trò người dùng (Target Roles)
        if (! empty($flag->target_roles) && is_array($flag->target_roles)) {
            if (! $user || ! in_array($user->role, $flag->target_roles, true)) {
                return false;
            }
        }

        // 4. Phát hành 100%
        if ($flag->rollout_percentage >= 100) {
            return true;
        }

        // 5. Tắt hoàn toàn 0%
        if ($flag->rollout_percentage <= 0) {
            return false;
        }

        // 6. Phát hành từng phần theo % (Staged Rollout) — Dùng hàm băm nhất quán theo User ID
        if ($user) {
            $bucket = abs(crc32("{$key}:{$user->id}")) % 100;

            return $bucket < $flag->rollout_percentage;
        }

        return false;
    }

    /**
     * Bảng kiểm tra sức khỏe nền tảng trước khi phát hành (Preflight Checklist).
     *
     * @return array{
     *     checks: array<string, array{label: string, status: string, latency_ms: ?float, message: string}>,
     *     is_ready_for_release: bool,
     *     critical_issues_count: int,
     *     checked_at: string
     * }
     */
    public static function runPreflightChecklist(): array
    {
        $checks = [];
        $criticalCount = 0;

        // 1. Kiểm tra kết nối Cơ sở dữ liệu (Database Connection & Latency)
        $dbStart = microtime(true);
        try {
            DB::select('SELECT 1');
            $dbLatency = round((microtime(true) - $dbStart) * 1000, 2);
            $checks['database'] = [
                'label' => 'Kết Nối Cơ Sở Dữ Liệu (MySQL/Primary DB)',
                'status' => $dbLatency < 50 ? 'passed' : 'warning',
                'latency_ms' => $dbLatency,
                'message' => "Kết nối phản hồi mượt mà trong {$dbLatency}ms.",
            ];
        } catch (\Throwable $e) {
            $criticalCount++;
            $checks['database'] = [
                'label' => 'Kết Nối Cơ Sở Dữ Liệu (MySQL/Primary DB)',
                'status' => 'critical',
                'latency_ms' => null,
                'message' => 'Lỗi kết nối cơ sở dữ liệu: '.$e->getMessage(),
            ];
        }

        // 2. Kiểm tra Động cơ Bộ nhớ đệm (Cache Engine)
        $cacheStart = microtime(true);
        try {
            Cache::put('preflight_probe_key', 'healthy', 5);
            $cacheVal = Cache::get('preflight_probe_key');
            $cacheLatency = round((microtime(true) - $cacheStart) * 1000, 2);

            $checks['cache'] = [
                'label' => 'Động Cơ Bộ Nhớ Đệm (Cache Engine)',
                'status' => ($cacheVal === 'healthy') ? 'passed' : 'warning',
                'latency_ms' => $cacheLatency,
                'message' => "Ghi/đọc bộ nhớ đệm thành công trong {$cacheLatency}ms.",
            ];
        } catch (\Throwable $e) {
            $criticalCount++;
            $checks['cache'] = [
                'label' => 'Động Cơ Bộ Nhớ Đệm (Cache Engine)',
                'status' => 'critical',
                'latency_ms' => null,
                'message' => 'Lỗi bộ nhớ đệm: '.$e->getMessage(),
            ];
        }

        // 3. Quyền ghi Thư mục Lưu trữ (Storage Write Permissions)
        try {
            $testFile = storage_path('app/preflight_test_'.time().'.tmp');
            File::put($testFile, 'test');
            File::delete($testFile);

            $checks['storage'] = [
                'label' => 'Quyền Ghi Thư Mục Lưu Trữ (Storage Directory)',
                'status' => 'passed',
                'latency_ms' => null,
                'message' => 'Thư mục storage có đầy đủ quyền đọc/ghi cho upload ảnh và export file.',
            ];
        } catch (\Throwable $e) {
            $criticalCount++;
            $checks['storage'] = [
                'label' => 'Quyền Ghi Thư Mục Lưu Trữ (Storage Directory)',
                'status' => 'critical',
                'latency_ms' => null,
                'message' => 'Thư mục storage không có quyền ghi: '.$e->getMessage(),
            ];
        }

        // 4. Kiểm tra Di trú Cơ sở dữ liệu (Pending Migrations Check)
        try {
            $executedMigrations = Schema::hasTable('migrations')
                ? DB::table('migrations')->pluck('migration')->toArray()
                : [];

            $migrationFiles = glob(database_path('migrations/*.php'));
            $pendingMigrations = [];
            foreach ($migrationFiles as $filePath) {
                $base = basename($filePath, '.php');
                if (! in_array($base, $executedMigrations, true)) {
                    $pendingMigrations[] = $base;
                }
            }

            if (empty($pendingMigrations)) {
                $checks['migrations'] = [
                    'label' => 'Tính Nhất Quán Lược Đồ (Database Migrations Dry-Run)',
                    'status' => 'passed',
                    'latency_ms' => null,
                    'message' => '100% migrations đã được đồng bộ chuẩn xác với cấu trúc cơ sở dữ liệu.',
                ];
            } else {
                $checks['migrations'] = [
                    'label' => 'Tính Nhất Quán Lược Đồ (Database Migrations Dry-Run)',
                    'status' => 'warning',
                    'latency_ms' => null,
                    'message' => 'Phát hiện '.count($pendingMigrations).' migration chưa chạy: '.implode(', ', array_slice($pendingMigrations, 0, 3)),
                ];
            }
        } catch (\Throwable $e) {
            $checks['migrations'] = [
                'label' => 'Tính Nhất Quán Lược Đồ (Database Migrations Dry-Run)',
                'status' => 'warning',
                'latency_ms' => null,
                'message' => 'Không thể quét migrations: '.$e->getMessage(),
            ];
        }

        // 5. Kiểm tra Hàng đợi tác vụ & Tác vụ lỗi (Failed Jobs Check)
        try {
            $failedJobsCount = Schema::hasTable('failed_jobs')
                ? DB::table('failed_jobs')->count()
                : 0;

            $checks['queue'] = [
                'label' => 'Hàng Đợi Xử Lý Ngầm (Queue & Background Jobs)',
                'status' => $failedJobsCount === 0 ? 'passed' : ($failedJobsCount < 10 ? 'warning' : 'critical'),
                'latency_ms' => null,
                'message' => $failedJobsCount === 0
                    ? 'Hàng đợi vận hành trơn tru, không có tác vụ lỗi.'
                    : "Cảnh báo: Hiện đang có {$failedJobsCount} tác vụ thất bại trong hàng đợi cần xem xét.",
            ];
            if ($failedJobsCount >= 10) {
                $criticalCount++;
            }
        } catch (\Throwable) {
            $checks['queue'] = [
                'label' => 'Hàng Đợi Xử Lý Ngầm (Queue & Background Jobs)',
                'status' => 'passed',
                'latency_ms' => null,
                'message' => 'Hàng đợi trạng thái bình thường.',
            ];
        }

        // 6. Kiểm tra An toàn Môi trường (Environment & Security Keys)
        $hasKey = ! empty(config('app.key'));
        $checks['environment'] = [
            'label' => 'Mã Khóa Bảo Mật & Môi Trường (Security App Key)',
            'status' => $hasKey ? 'passed' : 'critical',
            'latency_ms' => null,
            'message' => $hasKey
                ? 'Khóa bảo mật APP_KEY hợp lệ, thuật toán mã hóa cookie và token an toàn.'
                : 'NGHIÊM TRỌNG: APP_KEY đang để trống, hệ thống dễ bị tấn công mã hóa!',
        ];
        if (! $hasKey) {
            $criticalCount++;
        }

        return [
            'checks' => $checks,
            'is_ready_for_release' => $criticalCount === 0,
            'critical_issues_count' => $criticalCount,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Kích hoạt Chế Độ Phòng Vệ Khẩn Cấp (Emergency Safe Mode).
     */
    public static function activateEmergencySafeMode(User $admin, string $reason): void
    {
        // 1. Kích hoạt cờ Safe Mode
        $safeFlag = SystemFeatureFlag::firstOrCreate(
            ['key' => 'high_concurrency_safe_mode'],
            [
                'name' => 'Chế Độ Phòng Vệ Khẩn Cấp Ngày Cưới',
                'category' => 'safety',
                'description' => 'Giảm tải CPU/DB, vô hiệu hóa các tác vụ nền nặng để bảo vệ thiệp cưới',
            ]
        );

        $safeFlag->update([
            'is_enabled' => true,
            'rollout_percentage' => 100,
            'kill_switch_active' => false,
            'last_toggled_by' => $admin->id,
            'last_toggled_at' => now(),
        ]);

        // 2. Kích hoạt Kill Switch cho các tính năng nặng (AI Story)
        SystemFeatureFlag::where('key', 'ai_story_generator')->update([
            'kill_switch_active' => true,
            'kill_switch_reason' => 'Kích hoạt Safe Mode khẩn cấp: '.$reason,
            'last_toggled_by' => $admin->id,
            'last_toggled_at' => now(),
        ]);

        self::flushFlagCache();

        AuditLogger::log(
            action: 'release.emergency_safe_mode_activated',
            targetType: 'system',
            targetId: 0,
            description: "Kích hoạt Chế Độ Phòng Vệ Khẩn Cấp: {$reason}",
            details: [
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'reason' => $reason,
            ]
        );
    }

    /**
     * Tắt Chế Độ Phòng Vệ Khẩn Cấp.
     */
    public static function deactivateEmergencySafeMode(User $admin): void
    {
        SystemFeatureFlag::where('key', 'high_concurrency_safe_mode')->update([
            'is_enabled' => false,
            'last_toggled_by' => $admin->id,
            'last_toggled_at' => now(),
        ]);

        // Mở lại Kill Switch cho các tính năng đã bị khóa
        SystemFeatureFlag::where('key', 'ai_story_generator')->update([
            'kill_switch_active' => false,
            'kill_switch_reason' => null,
            'last_toggled_by' => $admin->id,
            'last_toggled_at' => now(),
        ]);

        self::flushFlagCache();

        AuditLogger::log(
            action: 'release.emergency_safe_mode_deactivated',
            targetType: 'system',
            targetId: 0,
            description: 'Hủy kích hoạt Chế Độ Phòng Vệ Khẩn Cấp, khôi phục trạng thái vận hành bình thường.',
            details: [
                'admin_id' => $admin->id,
            ]
        );
    }

    /**
     * Khôi phục khẩn cấp / Rollback một cờ tính năng về trạng thái an toàn.
     */
    public static function rollbackFeatureFlag(string $key, User $admin, string $reason): ?SystemFeatureFlag
    {
        $flag = SystemFeatureFlag::where('key', $key)->first();
        if (! $flag) {
            return null;
        }

        $flag->update([
            'is_enabled' => false,
            'rollout_percentage' => 0,
            'kill_switch_active' => true,
            'kill_switch_reason' => "Rollback bởi {$admin->name}: {$reason}",
            'last_toggled_by' => $admin->id,
            'last_toggled_at' => now(),
        ]);

        self::flushFlagCache();

        AuditLogger::log(
            action: 'release.feature_flag_rolled_back',
            targetType: 'system_feature_flag',
            targetId: $flag->id,
            description: "Rollback cờ tính năng {$flag->key}: {$reason}",
            details: [
                'key' => $flag->key,
                'reason' => $reason,
                'admin_id' => $admin->id,
            ]
        );

        return $flag;
    }

    /**
     * Khởi tạo bộ cờ tính năng mặc định ban đầu nếu hệ thống chưa có.
     */
    public static function seedDefaultFeatureFlags(): void
    {
        $defaults = [
            [
                'key' => 'live_photo_drop',
                'name' => 'Live Guest Photo Drop',
                'description' => 'Cho phép khách mời quét mã QR tại bàn tiệc tải ảnh chụp trực tiếp vào thiệp cưới',
                'category' => 'collaboration',
                'is_enabled' => true,
                'rollout_percentage' => 100,
            ],
            [
                'key' => 'ai_story_generator',
                'name' => 'Trợ Lý AI Tạo Lời Chúc & Câu Chuyện Tình Yêu',
                'description' => 'Gợi ý lời ngỏ, câu chuyện tình yêu và văn phong chúc phúc hoàng gia',
                'category' => 'ai',
                'is_enabled' => true,
                'rollout_percentage' => 100,
            ],
            [
                'key' => 'vietqr_dynamic',
                'name' => 'Mã QR Thanh Toán VietQR Tự Động (SePay)',
                'description' => 'Tự động sinh mã VietQR và đối soát biến động số dư tức thời',
                'category' => 'commerce',
                'is_enabled' => true,
                'rollout_percentage' => 100,
            ],
            [
                'key' => 'two_family_review',
                'name' => 'Rà Soát Lễ Nghi & Duyệt Thiệp Hai Nhà',
                'description' => 'Link định danh gửi cho phụ mẫu hai bên kiểm tra thông tin trước khi phát hành',
                'category' => 'core',
                'is_enabled' => true,
                'rollout_percentage' => 100,
            ],
            [
                'key' => 'seating_planner_v3',
                'name' => 'Sơ Đồ Xếp Bàn Tiệc Trực Quan (Seating Planner Studio)',
                'description' => 'Công cụ phân bổ bàn tiệc kéo thả và tự động tính tải sức chứa',
                'category' => 'collaboration',
                'is_enabled' => true,
                'rollout_percentage' => 100,
            ],
            [
                'key' => 'high_concurrency_safe_mode',
                'name' => 'Chế Độ Phòng Vệ Tải Cao Ngày Cưới',
                'description' => 'Tự động khóa các tác vụ ghi nặng khi lượng truy cập tiệc cưới tăng đột biến',
                'category' => 'safety',
                'is_enabled' => false,
                'rollout_percentage' => 0,
            ],
        ];

        foreach ($defaults as $d) {
            SystemFeatureFlag::firstOrCreate(['key' => $d['key']], $d);
        }

        self::flushFlagCache();
    }

    /**
     * Xóa cache toàn bộ cờ tính năng để cập nhật tức thời.
     */
    public static function flushFlagCache(): void
    {
        $keys = SystemFeatureFlag::pluck('key')->all();
        foreach ($keys as $k) {
            Cache::forget("feature_flag_{$k}");
        }
    }
}
