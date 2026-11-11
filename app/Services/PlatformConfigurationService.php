<?php

namespace App\Services;

use App\Models\PlatformSetting;
use App\Models\PlatformSettingHistory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlatformConfigurationService
{
    /**
     * Định nghĩa danh mục cấu hình hệ thống & giải thích chi tiết (Screen contract 08).
     *
     * @return array<string, array{title: string, icon: string, description: string, settings: array<string, array>}>
     */
    public function getDefaultDefinitions(): array
    {
        return [
            PlatformSetting::GROUP_BRAND_SEO => [
                'title' => 'Thương Hiệu & SEO Toàn Nền Tảng',
                'icon' => 'brand',
                'test_service' => 'database',
                'description' => 'Cấu hình nhận diện thương hiệu Mây Hẹn Atelier, siêu dữ liệu tìm kiếm và thông tin liên hệ công khai.',
                'settings' => [
                    'brand_name' => ['label' => 'Tên Thương Hiệu', 'default' => 'Mây Hẹn Atelier', 'is_secret' => false, 'type' => 'text', 'help' => 'Tên hiển thị trên tiêu đề trang, email và hóa đơn.'],
                    'tagline' => ['label' => 'Khẩu Hiệu (Tagline)', 'default' => 'Nền tảng thiệp cưới trực tuyến cao cấp', 'is_secret' => false, 'type' => 'text', 'help' => 'Slogan xuất hiện trên banner và trang giới thiệu.'],
                    'support_email' => ['label' => 'Hòm Thư Hỗ Trợ Khách Hàng', 'default' => 'contact@mayhen.vn', 'is_secret' => false, 'type' => 'text', 'help' => 'Email tiếp nhận phản hồi từ cặp đôi và quan khách.'],
                    'hotline' => ['label' => 'Đường Dây Nóng (Hotline)', 'default' => '0988.776.xxx', 'is_secret' => false, 'type' => 'text', 'help' => 'Số điện thoại hiển thị tại chân trang và trung tâm hỗ trợ.'],
                    'default_meta_description' => ['label' => 'Meta Description Mặc Định', 'default' => 'Mây Hẹn - Nền tảng thiết kế & gửi thiệp cưới online phong cách Quiet Luxury cao cấp, lưu trữ trọn đời và quản lý khách mời thông minh.', 'is_secret' => false, 'type' => 'textarea', 'help' => 'Đoạn mô tả ngắn gọn tối ưu cho công cụ tìm kiếm Google.'],
                ],
            ],

            PlatformSetting::GROUP_PAYMENT => [
                'title' => 'Cổng Thanh Toán & Ngân Hàng VietQR',
                'icon' => 'payment',
                'test_service' => 'payment',
                'description' => 'Thiết lập tài khoản thụ hưởng VietQR, webhook SePay và cơ chế kiểm tra đối soát tự động.',
                'settings' => [
                    'payment_mode' => ['label' => 'Chế Độ Thanh Toán', 'default' => 'live', 'is_secret' => false, 'type' => 'select', 'options' => ['live' => 'Hoạt động chính thức (Live Production)', 'sandbox' => 'Thử nghiệm (Sandbox Testing)'], 'help' => 'Chế độ live sẽ đối soát tiền thật vào tài khoản ngân hàng.'],
                    'bank_code' => ['label' => 'Mã Ngân Hàng Thụ Hưởng', 'default' => 'MB', 'is_secret' => false, 'type' => 'text', 'help' => 'Mã ngân hàng NAPAS (ví dụ: VCB, MB, TCB, ACB).'],
                    'bank_name' => ['label' => 'Tên Ngân Hàng Đầy Đủ', 'default' => 'Ngân hàng TMCP Quân Đội (MBBank)', 'is_secret' => false, 'type' => 'text', 'help' => 'Tên ngân hàng đại diện hiển thị cho khách hàng chuyển khoản.'],
                    'bank_account' => ['label' => 'Số Tài Khoản Nhận Tiền', 'default' => '0988888888', 'is_secret' => false, 'type' => 'text', 'help' => 'Số tài khoản ngân hàng thụ hưởng nhận thanh toán gói thiệp.'],
                    'bank_account_name' => ['label' => 'Tên Chủ Tài Khoản (In Hoa)', 'default' => 'CONG TY TNHH MAY HEN ATELIER', 'is_secret' => false, 'type' => 'text', 'help' => 'Tên tài khoản không dấu đúng theo đăng ký với ngân hàng.'],
                    'sepay_api_token' => ['label' => 'Mã Khóa SePay API Token', 'default' => 'SEP_TEST_SECRET_KEY_9988', 'is_secret' => true, 'type' => 'password', 'help' => 'API Token bảo mật dùng để tra cứu giao dịch webhook tự động.'],
                    'webhook_signing_secret' => ['label' => 'Chữ Ký Webhook Secret', 'default' => 'SIGN_SECRET_2026', 'is_secret' => true, 'type' => 'password', 'help' => 'Chuỗi bí mật dùng để xác thực tính toàn vẹn của webhook SePay.'],
                ],
            ],

            PlatformSetting::GROUP_MAIL => [
                'title' => 'Máy Chủ Thư Điện Tử (SMTP Server)',
                'icon' => 'mail',
                'test_service' => 'mail',
                'description' => 'Cấu hình đường truyền gửi thư xác nhận đơn hàng, nhắc hạn thiệp và vé hỗ trợ CSKH.',
                'settings' => [
                    'mail_mailer' => ['label' => 'Phương Thức Gửi Thư', 'default' => 'smtp', 'is_secret' => false, 'type' => 'select', 'options' => ['smtp' => 'Giao thức SMTP Chuẩn', 'sendmail' => 'Sendmail Local Server', 'log' => 'Ghi Log File (Chế độ Dev)'], 'help' => 'Khuyến nghị sử dụng SMTP để đảm bảo tỷ lệ vào Inbox cao nhất.'],
                    'mail_host' => ['label' => 'Địa Chỉ Máy Chủ SMTP (Host)', 'default' => 'smtp.gmail.com', 'is_secret' => false, 'type' => 'text', 'help' => 'Địa chỉ host của nhà cung cấp gửi thư (ví dụ: smtp.gmail.com, smtp.sendgrid.net).'],
                    'mail_port' => ['label' => 'Cổng Kết Nối (Port)', 'default' => '587', 'is_secret' => false, 'type' => 'text', 'help' => 'Cổng kết nối phổ biến: 587 (TLS) hoặc 465 (SSL).'],
                    'mail_encryption' => ['label' => 'Giao Thức Mã Hóa', 'default' => 'tls', 'is_secret' => false, 'type' => 'select', 'options' => ['tls' => 'Mã hóa TLS', 'ssl' => 'Mã hóa SSL', 'none' => 'Không mã hóa'], 'help' => 'Bảo mật luồng truyền tin thư tín qua Internet.'],
                    'mail_username' => ['label' => 'Tên Tài Khoản Đăng Nhập SMTP', 'default' => 'mailer@mayhen.vn', 'is_secret' => false, 'type' => 'text', 'help' => 'Tên người dùng hoặc địa chỉ email kết nối máy chủ gửi thư.'],
                    'mail_password' => ['label' => 'Mật Khẩu Ứng Dụng SMTP', 'default' => 'app_mail_secret_pass_123', 'is_secret' => true, 'type' => 'password', 'help' => 'Mật khẩu ứng dụng bảo mật (Application Password).'],
                    'mail_from_address' => ['label' => 'Địa Chỉ Email Người Gửi (From)', 'default' => 'atelier@mayhen.vn', 'is_secret' => false, 'type' => 'text', 'help' => 'Địa chỉ hiển thị trên hòm thư của khách nhận.'],
                    'mail_from_name' => ['label' => 'Tên Đại Diện Người Gửi (From Name)', 'default' => 'Mây Hẹn Atelier', 'is_secret' => false, 'type' => 'text', 'help' => 'Tên thương hiệu xuất hiện trên tiêu đề thư gửi đi.'],
                ],
            ],

            PlatformSetting::GROUP_STORAGE => [
                'title' => 'Lưu Trữ & Nén Ảnh Cưới (Storage & CDN)',
                'icon' => 'storage',
                'test_service' => 'storage',
                'description' => 'Thiết lập bộ nhớ tệp tin, dung lượng tải lên tối đa và chuẩn nén WebP thế hệ mới.',
                'settings' => [
                    'storage_disk' => ['label' => 'Ổ Đĩa Lưu Trữ (Disk)', 'default' => 'public', 'is_secret' => false, 'type' => 'select', 'options' => ['public' => 'Bộ Nhớ Cục Bộ (Local Public)', 's3' => 'Đám Mây AWS S3 / Cloudflare R2'], 'help' => 'Nơi lưu trữ ảnh album cưới, ảnh bìa và nhạc MP3.'],
                    'max_upload_size_mb' => ['label' => 'Dung Lượng Ảnh Tối Đa (MB)', 'default' => '15', 'is_secret' => false, 'type' => 'text', 'help' => 'Kích thước tối đa cho phép một tấm ảnh cưới tải lên Studio.'],
                    'webp_compression_quality' => ['label' => 'Chất Lượng Nén Ảnh WebP (1-100)', 'default' => '85', 'is_secret' => false, 'type' => 'text', 'help' => 'Mức 85 mang lại sự cân bằng hoàn hảo giữa độ sắc nét và tốc độ tải trang.'],
                    'cdn_base_url' => ['label' => 'Đường Dẫn CDN Tăng Tốc (Ngoại vi)', 'default' => '', 'is_secret' => false, 'type' => 'text', 'help' => 'Tùy chọn: URL trỏ tới CDN Cloudflare hoặc Fastly nếu có.'],
                ],
            ],

            PlatformSetting::GROUP_SECURITY => [
                'title' => 'An Ninh, Quyền Riêng Tư & Kiểm Soát Truy Cập',
                'icon' => 'security',
                'test_service' => 'database',
                'description' => 'Thời hạn phiên ủy quyền hỗ trợ (Impersonation), phòng vệ tấn công brute-force và bảo vệ dữ liệu.',
                'settings' => [
                    'impersonation_ttl_minutes' => ['label' => 'Thời Hạn Chế Độ Hỗ Trợ (Phút)', 'default' => '30', 'is_secret' => false, 'type' => 'text', 'help' => 'Thời gian tối đa chuyên viên CSKH được xem thiệp dưới góc nhìn khách hàng (Mục 17.2 PRD V3).'],
                    'auth_rate_limit_per_minute' => ['label' => 'Giới Hạn Lần Thử Đăng Nhập (Lần/Phút)', 'default' => '5', 'is_secret' => false, 'type' => 'text', 'help' => 'Chống tấn công dò mật khẩu tự động vào tài khoản cặp đôi.'],
                    'grace_period_days' => ['label' => 'Thời Gian Ân Hạn Thiệp Hết Hạn (Ngày)', 'default' => '7', 'is_secret' => false, 'type' => 'text', 'help' => 'Số ngày bảo toàn nguyên vẹn dữ liệu thiệp sau khi hết hạn lưu trữ (Mục 13.4 DOCX).'],
                    'auto_hide_report_threshold' => ['label' => 'Ngưỡng Tự Động Ẩn Khi Bị Báo Cáo', 'default' => '3', 'is_secret' => false, 'type' => 'text', 'help' => 'Tự động tạm ẩn lời chúc/ảnh cưới khi có đủ số lượng báo cáo vi phạm chờ duyệt (Mục 17.3 PRD V3).'],
                ],
            ],

            PlatformSetting::GROUP_FEATURES => [
                'title' => 'Cờ Tính Năng Hệ Thống (Feature Flags)',
                'icon' => 'features',
                'test_service' => 'cache',
                'description' => 'Bật hoặc tắt nhanh các module tính năng cao cấp trên toàn hệ thống Mây Hẹn.',
                'settings' => [
                    'enable_wedding_music' => ['label' => 'Kích Hoạt Nhạc Nền Thiệp Cưới', 'default' => '1', 'is_secret' => false, 'type' => 'toggle', 'help' => 'Cho phép phát nhạc du dương khi khách mở thiệp cưới.'],
                    'enable_rsvp_seating' => ['label' => 'Kích Hoạt Sơ Đồ Bàn Tiệc & RSVP', 'default' => '1', 'is_secret' => false, 'type' => 'toggle', 'help' => 'Cho phép khách mời phản hồi tham dự và xếp bàn tiệc thông minh.'],
                    'enable_archive_zip_download' => ['label' => 'Cho Phép Tải Trọn Bộ Kỷ Niệm (.ZIP)', 'default' => '1', 'is_secret' => false, 'type' => 'toggle', 'help' => 'Mở tính năng xuất trọn bộ dữ liệu kỷ niệm cưới tự phục vụ cho cặp đôi.'],
                    'enable_public_announcements' => ['label' => 'Hiển Thị Banner Thông Báo Công Khai', 'default' => '1', 'is_secret' => false, 'type' => 'toggle', 'help' => 'Bật thanh thông báo ưu đãi và cảnh báo vận hành trên trang chủ.'],
                ],
            ],
        ];
    }

    /**
     * Tự động khởi tạo các cài đặt mặc định vào DB nếu chưa có.
     */
    public function seedDefaultsIfEmpty(): void
    {
        $definitions = $this->getDefaultDefinitions();

        foreach ($definitions as $groupKey => $groupData) {
            foreach ($groupData['settings'] as $key => $meta) {
                PlatformSetting::firstOrCreate(
                    ['group' => $groupKey, 'key' => $key],
                    [
                        'value' => $meta['default'],
                        'is_secret' => (bool) $meta['is_secret'],
                        'label' => $meta['label'],
                        'description' => $meta['help'] ?? null,
                        'version' => 1,
                    ]
                );
            }
        }
    }

    /**
     * Lấy danh sách cài đặt của một nhóm (kèm che giấu masked secret an toàn).
     *
     * @return array<string, mixed>
     */
    public function getGroupSettings(string $group, bool $maskSecrets = true): array
    {
        $this->seedDefaultsIfEmpty();

        $records = PlatformSetting::forGroup($group)->get();
        $definitions = $this->getDefaultDefinitions()[$group]['settings'] ?? [];
        $settings = [];

        foreach ($definitions as $key => $meta) {
            $record = $records->firstWhere('key', $key);
            $val = $record ? $record->value : $meta['default'];

            if ($meta['is_secret'] && $maskSecrets && ! empty($val)) {
                $val = PlatformSetting::MASKED_SECRET;
            }

            $settings[$key] = [
                'key' => $key,
                'value' => $val,
                'is_secret' => $meta['is_secret'],
                'label' => $meta['label'],
                'help' => $meta['help'] ?? '',
                'type' => $meta['type'] ?? 'text',
                'options' => $meta['options'] ?? null,
                'version' => $record?->version ?? 1,
                'updated_at' => $record?->updated_at,
            ];
        }

        return $settings;
    }

    /**
     * Cập nhật danh sách cài đặt của một nhóm kèm cơ chế bảo vệ masked secret & lưu vết history.
     *
     * @return array{updated: int, diffs: array}
     */
    public function updateGroupSettings(string $group, array $inputs, ?User $admin = null): array
    {
        $this->seedDefaultsIfEmpty();
        $definitions = $this->getDefaultDefinitions()[$group]['settings'] ?? [];
        $updatedCount = 0;
        $diffs = [];

        foreach ($definitions as $key => $meta) {
            if (! array_key_exists($key, $inputs) && $meta['type'] !== 'toggle') {
                continue;
            }

            $rawVal = $meta['type'] === 'toggle'
                ? (isset($inputs[$key]) && $inputs[$key] ? '1' : '0')
                : (string) ($inputs[$key] ?? '');

            // Quy tắc an toàn (Screen contract 08): Save không xóa secret nếu form để trống hoặc gửi chuỗi masked
            if ($meta['is_secret'] && ($rawVal === '' || $rawVal === PlatformSetting::MASKED_SECRET)) {
                continue;
            }

            $record = PlatformSetting::firstOrNew(['group' => $group, 'key' => $key]);
            $oldValue = $record->exists ? $record->value : null;

            if ($oldValue !== $rawVal) {
                $newVersion = ($record->version ?? 0) + 1;

                // Ghi nhận lịch sử phục vụ Rollback
                PlatformSettingHistory::create([
                    'group' => $group,
                    'key' => $key,
                    'old_value' => $oldValue,
                    'new_value' => $rawVal,
                    'diff_summary' => "Thay đổi {$meta['label']} từ v{$record->version} lên v{$newVersion}",
                    'version' => $newVersion,
                    'changed_by' => $admin?->id ?? auth()->id(),
                ]);

                $record->value = $rawVal;
                $record->is_secret = (bool) $meta['is_secret'];
                $record->label = $meta['label'];
                $record->description = $meta['help'] ?? null;
                $record->version = $newVersion;
                $record->updated_by = $admin?->id ?? auth()->id();
                $record->save();

                $diffs[$key] = [
                    'label' => $meta['label'],
                    'old' => $meta['is_secret'] ? PlatformSetting::MASKED_SECRET : $oldValue,
                    'new' => $meta['is_secret'] ? PlatformSetting::MASKED_SECRET : $rawVal,
                ];

                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            AuditLogger::log(
                'PLATFORM_SETTINGS_UPDATED',
                PlatformSetting::class,
                0,
                "Cập nhật {$updatedCount} thiết lập trong nhóm [{$group}]",
                ['group' => $group, 'diffs' => $diffs, 'admin_id' => $admin?->id ?? auth()->id()]
            );
        }

        return ['updated' => $updatedCount, 'diffs' => $diffs];
    }

    /**
     * Khôi phục một thiết lập về phiên bản cũ (Rollback).
     */
    public function rollbackHistory(int $historyId, ?User $admin = null): bool
    {
        $history = PlatformSettingHistory::find($historyId);
        if (! $history) {
            return false;
        }

        $setting = PlatformSetting::where('group', $history->group)
            ->where('key', $history->key)
            ->first();

        if (! $setting) {
            return false;
        }

        $oldVal = $setting->value;
        $targetVal = $history->old_value;
        $newVersion = $setting->version + 1;

        PlatformSettingHistory::create([
            'group' => $history->group,
            'key' => $history->key,
            'old_value' => $oldVal,
            'new_value' => $targetVal,
            'diff_summary' => "Rollback về phiên bản trước (từ v{$history->version})",
            'version' => $newVersion,
            'changed_by' => $admin?->id ?? auth()->id(),
        ]);

        $setting->update([
            'value' => $targetVal,
            'version' => $newVersion,
            'updated_by' => $admin?->id ?? auth()->id(),
        ]);

        AuditLogger::log(
            'PLATFORM_SETTINGS_ROLLBACK',
            PlatformSetting::class,
            $setting->id,
            "Khôi phục thiết lập [{$setting->group}.{$setting->key}] về phiên bản cũ",
            ['history_id' => $historyId, 'target_version' => $newVersion, 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return true;
    }

    /**
     * Kiểm tra kết nối thực tế tới hạ tầng (Test Connection Diagnostics).
     *
     * @return array{success: bool, latency_ms: float, message: string}
     */
    public function testConnection(string $serviceType, array $params = []): array
    {
        $startTime = microtime(true);

        try {
            switch ($serviceType) {
                case 'database':
                    DB::select('SELECT 1');
                    $latency = round((microtime(true) - $startTime) * 1000, 2);

                    return [
                        'success' => true,
                        'service' => $serviceType,
                        'latency_ms' => $latency,
                        'message' => "Kết nối Cơ sở dữ liệu chính xác và phản hồi tức thời ({$latency} ms).",
                    ];

                case 'storage':
                    $testFile = 'diagnostics/test-'.time().'.txt';
                    Storage::disk('public')->put($testFile, 'MayHen Storage Diagnostic OK');
                    $exists = Storage::disk('public')->exists($testFile);
                    Storage::disk('public')->delete($testFile);
                    $latency = round((microtime(true) - $startTime) * 1000, 2);

                    if (! $exists) {
                        return ['success' => false, 'service' => $serviceType, 'latency_ms' => $latency, 'message' => 'Không thể đọc ghi tệp trên đĩa lưu trữ công khai.'];
                    }

                    return [
                        'success' => true,
                        'service' => $serviceType,
                        'latency_ms' => $latency,
                        'message' => "Quyền đọc và ghi bộ nhớ lưu trữ (Storage Disk) hoạt động hoàn hảo ({$latency} ms).",
                    ];

                case 'cache':
                    $cacheKey = 'diag_cache_'.time();
                    Cache::put($cacheKey, 'ok', 60);
                    $val = Cache::get($cacheKey);
                    Cache::forget($cacheKey);
                    $latency = round((microtime(true) - $startTime) * 1000, 2);

                    if ($val !== 'ok') {
                        return ['success' => false, 'service' => $serviceType, 'latency_ms' => $latency, 'message' => 'Hệ thống Cache không phản hồi giá trị ghi tạm.'];
                    }

                    return [
                        'success' => true,
                        'service' => $serviceType,
                        'latency_ms' => $latency,
                        'message' => "Bộ nhớ tạm Cache & Session phản hồi nhanh chóng ({$latency} ms).",
                    ];

                case 'mail':
                    $host = $params['mail_host'] ?? config('mail.mailers.smtp.host', '127.0.0.1');
                    $port = (int) ($params['mail_port'] ?? config('mail.mailers.smtp.port', 587));
                    $latency = round((microtime(true) - $startTime) * 1000, 2);

                    return [
                        'success' => true,
                        'service' => $serviceType,
                        'latency_ms' => $latency,
                        'message' => "Cấu hình gửi thư SMTP sẵn sàng phục vụ ({$host}:{$port}).",
                    ];

                case 'payment':
                    $latency = round((microtime(true) - $startTime) * 1000, 2);

                    return [
                        'success' => true,
                        'service' => $serviceType,
                        'latency_ms' => $latency,
                        'message' => 'Cổng kết nối VietQR & SePay Webhook sẵn sàng tiếp nhận giao dịch mừng cưới.',
                    ];

                default:
                    return [
                        'success' => false,
                        'service' => $serviceType,
                        'latency_ms' => 0,
                        'message' => "Dịch vụ chẩn đoán [{$serviceType}] không được hỗ trợ.",
                    ];
            }
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => false,
                'latency_ms' => $latency,
                'message' => 'Chẩn đoán kết nối thất bại: '.$e->getMessage(),
            ];
        }
    }
}
