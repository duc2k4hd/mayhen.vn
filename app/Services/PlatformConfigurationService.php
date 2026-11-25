<?php

namespace App\Services;

use App\Mail\AdminPaymentTestMail;
use App\Mail\AdminSmtpTestMail;
use App\Models\PlatformSetting;
use App\Models\PlatformSettingHistory;
use App\Models\User;
use App\Services\Payments\SepayGatewayService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlatformConfigurationService
{
    /**
     * Định nghĩa danh mục cấu hình hệ thống & giải thích chi tiết (Screen contract 08).
     *
     * @return array<string, array{title: string, icon: string, description: string, settings: array<string, array>}>
     */
    public function getDefaultDefinitions(): array
    {
        $brandName = PlatformSetting::getBrandName();

        return [
            PlatformSetting::GROUP_BRAND_SEO => [
                'title' => 'Thương Hiệu & SEO Toàn Nền Tảng',
                'icon' => 'brand',
                'test_service' => 'database',
                'description' => "Cấu hình nhận diện thương hiệu {$brandName}, siêu dữ liệu tìm kiếm và thông tin liên hệ công khai.",
                'settings' => [
                    'brand_name' => ['label' => 'Tên Thương Hiệu', 'default' => 'Mây Hẹn Atelier', 'is_secret' => false, 'type' => 'text', 'help' => 'Tên hiển thị trên tiêu đề trang, email và hóa đơn.'],
                    'tagline' => ['label' => 'Khẩu Hiệu (Tagline)', 'default' => 'Nền tảng thiệp cưới trực tuyến cao cấp', 'is_secret' => false, 'type' => 'text', 'help' => 'Slogan xuất hiện trên banner và trang giới thiệu.'],
                    'brand_logo' => [
                        'label' => 'Ảnh Logo Thương Hiệu (Brand Logo)',
                        'default' => '/storage/images/client/business/mayhen-logo.png',
                        'is_secret' => false,
                        'type' => 'image',
                        'help' => "Ảnh logo chính thức của {$brandName} hiển thị trên thanh Header toàn website (kích thước hiển thị 230x55px, PNG/WebP/SVG). Tự động dọn ảnh cũ trong Storage khi thay thế.",
                    ],
                    'brand_favicon' => [
                        'label' => 'Biểu Tượng Favicon (Brand Favicon)',
                        'default' => '/storage/images/client/business/mayhen-favicon.png',
                        'is_secret' => false,
                        'type' => 'image',
                        'help' => 'Biểu tượng favicon hiển thị trên tab trình duyệt, bookmark và nhận diện di động (PNG/ICO tỉ lệ 1:1). Tự động dọn ảnh cũ trong Storage khi thay thế.',
                    ],
                    'home_hero_banner' => [
                        'label' => 'Ảnh Nền Hero Trang Chủ (Banner Home)',
                        'default' => '/storage/images/client/banners/banner-home.webp',
                        'is_secret' => false,
                        'type' => 'image',
                        'aspect_ratio' => '16/9',
                        'ratio_hint' => 'Tỉ lệ khuyến nghị 16:9 ngang cho máy tính (1920x1080), định dạng WebP, JPG, PNG.',
                        'help' => 'Đường dẫn hoặc tải lên tệp ảnh nền máy tính (Desktop) cho khu vực mayhen_home_hero trên trang chủ (WebP, JPG, PNG). Tự động dọn ảnh cũ trong Storage khi thay thế.',
                    ],
                    'home_hero_banner_mobile' => [
                        'label' => 'Ảnh Nền Hero Trang Chủ Mobile (Tỉ Lệ 9:16 TikTok)',
                        'default' => '/storage/images/client/banners/banner-home-mobile.webp',
                        'is_secret' => false,
                        'type' => 'image',
                        'aspect_ratio' => '9/16',
                        'ratio_hint' => 'Tỉ lệ khuyến nghị 9:16 dọc chuẩn màn hình TikTok (1080x1920), định dạng WebP, JPG, PNG.',
                        'help' => 'Đường dẫn hoặc tải lên tệp ảnh nền riêng cho khu vực mayhen_home_hero trên điện thoại (tỉ lệ dọc 9:16 màn hình TikTok). Hỗ trợ thay thế trực tiếp trên cả điện thoại và máy tính.',
                    ],
                    'support_email' => ['label' => 'Hòm Thư Hỗ Trợ Khách Hàng', 'default' => 'support@mayhen.vn', 'is_secret' => false, 'type' => 'text', 'help' => 'Email tiếp nhận phản hồi từ cặp đôi và quan khách (mặc định support@mayhen.vn).'],
                    'hotline' => ['label' => 'Đường Dây Nóng (Hotline)', 'default' => '0988.776.xxx', 'is_secret' => false, 'type' => 'text', 'help' => 'Số điện thoại hiển thị tại chân trang và trung tâm hỗ trợ.'],
                    'company_name' => ['label' => 'Tên Đơn Vị Chủ Quản / Pháp Nhân', 'default' => "{$brandName} (mayhen.vn)", 'is_secret' => false, 'type' => 'text', 'help' => 'Tên doanh nghiệp hoặc hộ kinh doanh công bố ở chân trang theo quy định Bộ Công Thương.'],
                    'business_license' => ['label' => 'Mã Số Thuế / Số ĐKKD', 'default' => '', 'is_secret' => false, 'type' => 'text', 'help' => 'Nhập mã số doanh nghiệp / hộ kinh doanh và nơi cấp (để trống nếu đang chờ cập nhật).'],
                    'business_address' => ['label' => 'Địa Chỉ Trụ Sở / Studio', 'default' => 'Hà Nội, Việt Nam', 'is_secret' => false, 'type' => 'text', 'help' => 'Địa chỉ liên hệ công khai hiển thị ở chân trang và trang Liên hệ.'],
                    'working_hours' => ['label' => 'Giờ Làm Việc CSKH', 'default' => '08:00 - 22:00 (Thứ 2 - Chủ Nhật)', 'is_secret' => false, 'type' => 'text', 'help' => 'Khung giờ tiếp nhận tư vấn và xử lý khiếu nại trực tuyến.'],
                    'facebook_url' => ['label' => 'Liên Kết Facebook Fanpage', 'default' => '', 'is_secret' => false, 'type' => 'text', 'help' => 'URL đầy đủ trang Facebook chính thức (để trống nếu chưa gắn).'],
                    'instagram_url' => ['label' => 'Liên Kết Instagram', 'default' => '', 'is_secret' => false, 'type' => 'text', 'help' => 'URL đầy đủ tài khoản Instagram chính thức.'],
                    'tiktok_url' => ['label' => 'Liên Kết TikTok', 'default' => '', 'is_secret' => false, 'type' => 'text', 'help' => 'URL đầy đủ kênh TikTok chính thức.'],
                    'zalo_url' => ['label' => 'Liên Kết Zalo OA / Tư Vấn', 'default' => '', 'is_secret' => false, 'type' => 'text', 'help' => 'URL Zalo OA hoặc zalo.me/... để khách nhắn tin nhanh.'],
                    'default_meta_description' => ['label' => 'Meta Description Mặc Định', 'default' => "{$brandName} - Nền tảng thiết kế & gửi thiệp cưới online phong cách Quiet Luxury cao cấp, lưu trữ trọn đời và quản lý khách mời thông minh.", 'is_secret' => false, 'type' => 'textarea', 'help' => 'Đoạn mô tả ngắn gọn tối ưu cho công cụ tìm kiếm Google.'],
                    'gtm_container_id' => [
                        'label' => 'Mã Google Tag Manager (GTM Container ID)',
                        'default' => 'GTM-PQF4ZWBG',
                        'is_secret' => false,
                        'type' => 'text',
                        'help' => 'Mã vùng chứa GTM (ví dụ: GTM-PQF4ZWBG) tự động chèn mã theo dõi vào thẻ <head> của toàn bộ website và thiệp cưới. Hỗ trợ nhập mã ID hoặc dán cả đoạn thẻ script. Để trống nếu muốn tắt theo dõi.',
                    ],
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
                    'mail_from_address' => ['label' => 'Địa Chỉ Email Người Gửi Chung (From)', 'default' => 'no-reply@mayhen.vn', 'is_secret' => false, 'type' => 'text', 'help' => 'Địa chỉ email mặc định hiển thị trên hòm thư của khách nhận.'],
                    'mail_from_name' => ['label' => 'Tên Đại Diện Người Gửi (From Name)', 'default' => $brandName, 'is_secret' => false, 'type' => 'text', 'help' => 'Tên thương hiệu xuất hiện trên tiêu đề thư gửi đi.'],
                    'mail_from_admin' => [
                        'label' => '1. Email Hệ Thống & Gửi Nội Bộ (Internal System)',
                        'default' => 'admin@mayhen.vn',
                        'is_secret' => false,
                        'type' => 'text',
                        'help' => 'Hòm thư quản trị hệ thống dùng để gửi/nhận cảnh báo hạ tầng, sao lưu, lỗi máy chủ và thông báo nội bộ ban điều hành.',
                    ],
                    'mail_from_support' => [
                        'label' => '2. Email Hỗ Trợ Khách Hàng (Support / CSKH)',
                        'default' => 'support@mayhen.vn',
                        'is_secret' => false,
                        'type' => 'text',
                        'help' => 'Hòm thư tiếp nhận phản hồi, tư vấn hỗ trợ cô dâu chú rể và giải quyết sự cố thiệp cưới.',
                    ],
                    'mail_from_noreply' => [
                        'label' => '3. Email Chỉ Gửi Thông Báo Tự Động (No-Reply)',
                        'default' => 'no-reply@mayhen.vn',
                        'is_secret' => false,
                        'type' => 'text',
                        'help' => 'Hòm thư gửi thông báo tự động (quên mật khẩu, OTP xác thực, xác nhận tài khoản), tuyệt đối không nhận mail gửi về.',
                    ],
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
                    'google_client_id' => ['label' => 'Google OAuth Client ID', 'default' => '', 'is_secret' => false, 'type' => 'text', 'help' => 'Client ID từ Google Cloud Console để bật đăng nhập bằng Google. Nếu để trống sẽ tự lấy biến GOOGLE_CLIENT_ID trong .env.'],
                    'google_client_secret' => ['label' => 'Google OAuth Client Secret', 'default' => '', 'is_secret' => true, 'type' => 'password', 'help' => 'Client Secret bí mật từ Google Cloud Console (lưu bảo mật). Nếu để trống sẽ tự lấy biến GOOGLE_CLIENT_SECRET trong .env.'],
                ],
            ],

            PlatformSetting::GROUP_FEATURES => [
                'title' => 'Cờ Tính Năng Hệ Thống (Feature Flags)',
                'icon' => 'features',
                'test_service' => 'cache',
                'description' => "Bật hoặc tắt nhanh các module tính năng cao cấp trên toàn hệ thống {$brandName}.",
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
                    $r2 = app(CloudflareR2StorageService::class);
                    if ($r2->isConfigured()) {
                        $r2Result = $r2->testConnection();
                        if (! $r2Result['success']) {
                            return $r2Result;
                        }

                        return [
                            'success' => true,
                            'service' => $serviceType,
                            'latency_ms' => $r2Result['latency_ms'],
                            'message' => "Cloudflare R2 Object Storage (Bucket: {$r2->getBucket()}) và bộ nhớ đĩa phản hồi hoàn hảo ({$r2Result['latency_ms']} ms).",
                        ];
                    }

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
                    $sepayGateway = app(SepayGatewayService::class);
                    if ($sepayGateway->canQuerySepayApi()) {
                        $probe = $sepayGateway->testApiConnection();

                        return [
                            'success' => $probe['ok'],
                            'service' => $serviceType,
                            'latency_ms' => $probe['latency_ms'],
                            'message' => $probe['message'],
                        ];
                    }

                    $latency = round((microtime(true) - $startTime) * 1000, 2);

                    return [
                        'success' => true,
                        'service' => $serviceType,
                        'latency_ms' => $latency,
                        'message' => 'Cổng VietQR & SePay Webhook đã sẵn sàng (chưa cấu hình SEPAY_API_TOKEN để đối soát API chủ động).',
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

    /**
     * Gửi email kiểm thử trực tiếp qua máy chủ SMTP (Live SMTP Dispatcher).
     *
     * @param  array<string, mixed>  $customConfig
     * @return array{success: bool, latency_ms: float, message: string}
     */
    public function sendSmtpTestEmail(string $recipientEmail, array $customConfig = [], ?string $adminName = null): array
    {
        $startTime = microtime(true);

        try {
            PlatformSetting::applyMailRuntimeConfiguration();

            if (! empty($customConfig['mail_host'])) {
                $encryption = $customConfig['mail_encryption'] ?? 'tls';
                if ($encryption === 'none') {
                    $encryption = null;
                }
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.transport' => 'smtp',
                    'mail.mailers.smtp.host' => $customConfig['mail_host'],
                    'mail.mailers.smtp.port' => (int) ($customConfig['mail_port'] ?? 587),
                    'mail.mailers.smtp.encryption' => $encryption,
                ]);

                if (! empty($customConfig['mail_username'])) {
                    config(['mail.mailers.smtp.username' => $customConfig['mail_username']]);
                }
                if (! empty($customConfig['mail_password']) && $customConfig['mail_password'] !== PlatformSetting::MASKED_SECRET) {
                    config(['mail.mailers.smtp.password' => $customConfig['mail_password']]);
                }
                if (! empty($customConfig['mail_from_address'])) {
                    config(['mail.from.address' => $customConfig['mail_from_address']]);
                }
                if (! empty($customConfig['mail_from_name'])) {
                    config(['mail.from.name' => $customConfig['mail_from_name']]);
                }
            }

            Mail::purge('smtp');

            $smtpDetails = [
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption') ?? 'none',
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'sent_at' => now()->format('d/m/Y H:i:s'),
                'client_ip' => request()->ip() ?? '127.0.0.1',
            ];

            Mail::mailer('smtp')
                ->to($recipientEmail)
                ->send(new AdminSmtpTestMail($recipientEmail, $smtpDetails, $adminName));

            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => true,
                'latency_ms' => $latency,
                'message' => "Đã gửi email thử nghiệm thành công tới [{$recipientEmail}] ({$latency} ms). Vui lòng kiểm tra Hộp thư đến (Inbox) hoặc Thư rác (Spam).",
            ];
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            $errorMsg = $e->getMessage();
            if (str_contains($errorMsg, '535') || str_contains($errorMsg, 'authentication')) {
                $friendlyReason = 'Xác thực tài khoản SMTP thất bại: Tên đăng nhập hoặc Mật khẩu ứng dụng không chính xác.';
            } elseif (str_contains($errorMsg, 'Connection timed out') || str_contains($errorMsg, 'Connection could not be established')) {
                $friendlyReason = 'Không thể kết nối đến máy chủ SMTP: Kiểm tra lại Host, Port hoặc tường lửa máy chủ.';
            } else {
                $friendlyReason = $errorMsg;
            }

            return [
                'success' => false,
                'latency_ms' => $latency,
                'message' => "Gửi thư thử nghiệm thất bại ({$latency} ms): {$friendlyReason}",
            ];
        }
    }

    /**
     * Tạo mã VietQR mẫu và thông tin đối soát chuyển khoản thử nghiệm.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function generateSampleVietQr(array $params = []): array
    {
        $bankCode = trim((string) ($params['bank_code'] ?? PlatformSetting::getValue(PlatformSetting::GROUP_PAYMENT, 'bank_code', 'MB')));
        $bankName = trim((string) ($params['bank_name'] ?? PlatformSetting::getValue(PlatformSetting::GROUP_PAYMENT, 'bank_name', 'Ngân hàng TMCP Quân Đội (MBBank)')));
        $bankAccount = trim((string) ($params['bank_account'] ?? PlatformSetting::getValue(PlatformSetting::GROUP_PAYMENT, 'bank_account', '0988888888')));
        $bankAccountName = trim((string) ($params['bank_account_name'] ?? PlatformSetting::getValue(PlatformSetting::GROUP_PAYMENT, 'bank_account_name', 'CONG TY TNHH MAY HEN ATELIER')));
        $amount = (int) ($params['amount'] ?? 50000);
        if ($amount < 0) {
            $amount = 0;
        }
        $memo = trim((string) ($params['memo'] ?? 'MH TEST 9988'));

        $bin = VietQrService::resolveBankBin($bankCode ?: $bankName);
        $qrDataUri = VietQrService::generateDataUri($bin, $bankAccount, $amount, $memo, 320);
        $payload = VietQrService::generatePayload($bin, $bankAccount, $amount, $memo);

        return [
            'success' => true,
            'bank_code' => $bankCode,
            'bank_name' => $bankName,
            'bank_bin' => $bin,
            'bank_account' => $bankAccount,
            'bank_account_name' => $bankAccountName,
            'amount' => $amount,
            'amount_formatted' => number_format($amount, 0, ',', '.').' đ',
            'memo' => $memo,
            'qr_data_uri' => $qrDataUri,
            'payload' => $payload,
            'message' => 'Đã tạo mã VietQR chuẩn Napas 24/7 thành công. Bạn có thể dùng ứng dụng ngân hàng bất kỳ để quét thử.',
        ];
    }

    /**
     * Gửi email xác nhận thanh toán mẫu tới người nhận kiểm thử.
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, latency_ms: float, message: string}
     */
    public function sendPaymentSampleEmail(string $recipientEmail, array $params = [], ?string $adminName = null): array
    {
        $startTime = microtime(true);

        try {
            PlatformSetting::applyMailRuntimeConfiguration();
            Mail::purge('smtp');

            $sampleQr = $this->generateSampleVietQr($params);

            $orderCode = 'MH-TEST-'.strtoupper(Str::random(6));
            $planName = $params['plan_name'] ?? 'Gói Thiết Kế Thiệp Cưới Vĩnh Cửu (Lưu Trữ Không Giới Hạn)';
            $amount = (int) ($params['amount'] ?? 399000);
            $amountFormatted = number_format($amount, 0, ',', '.').' đ';

            $paymentDetails = [
                'order_code' => $orderCode,
                'plan_name' => $planName,
                'amount' => $amount,
                'amount_formatted' => $amountFormatted,
                'bank_code' => $sampleQr['bank_code'],
                'bank_name' => $sampleQr['bank_name'],
                'bank_account' => $sampleQr['bank_account'],
                'bank_account_name' => $sampleQr['bank_account_name'],
                'memo' => $sampleQr['memo'],
                'paid_at' => now()->format('d/m/Y H:i:s'),
                'qr_data_uri' => $sampleQr['qr_data_uri'],
            ];

            Mail::mailer('smtp')
                ->to($recipientEmail)
                ->send(new AdminPaymentTestMail($recipientEmail, $paymentDetails, $adminName));

            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => true,
                'latency_ms' => $latency,
                'message' => "Đã gửi email hóa đơn thanh toán mẫu thành công tới [{$recipientEmail}] ({$latency} ms). Vui lòng kiểm tra Hộp thư đến (Inbox) hoặc Thư rác (Spam).",
            ];
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => false,
                'latency_ms' => $latency,
                'message' => "Gửi email thanh toán mẫu thất bại ({$latency} ms): ".$e->getMessage(),
            ];
        }
    }
}
