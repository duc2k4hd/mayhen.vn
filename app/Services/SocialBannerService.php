<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PlatformSetting;
use App\Services\Admin\ToolsGovernanceService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

/**
 * Hệ thống quản lý Banner Mạng Xã Hội (Open Graph / Zalo / Facebook / X) chuẩn 1200×600
 * cho toàn bộ các trang SEO công khai & từng phân hệ công cụ trên Mây Hẹn (0 DB query trên Client nhờ PlatformSetting cache).
 */
class SocialBannerService
{
    public const WIDTH = 1200;

    public const HEIGHT = 600;

    /**
     * Danh mục đầy đủ 23 trang/phần SEO có thể cài đặt Banner 1200×600 độc lập.
     * Thứ tự match prefix cụ thể trước, trang chủ `/` sau cùng.
     *
     * @var array<string, array{group: string, label: string, path_hint: string, prefixes: list<string>}>
     */
    public const SLOTS = [
        // 1. Nhóm Trang SEO Chính & Marketing
        'home' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Trang Chủ Mây Hẹn', 'path_hint' => '/', 'prefixes' => ['']],
        'templates' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Kho Mẫu Thiệp Cưới Online', 'path_hint' => '/mau-thiep', 'prefixes' => ['mau-thiep']],
        'event_invitations' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Thiệp Sự Kiện PRO (Đầy Tháng, Tân Gia, Lễ Hội)', 'path_hint' => '/thiep-su-kien', 'prefixes' => ['thiep-su-kien']],
        'pricing' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Bảng Giá Dịch Vụ', 'path_hint' => '/bang-gia', 'prefixes' => ['bang-gia', 'tu-van-goi-cuoi']],
        'services' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Dịch Vụ & Tiện Ích', 'path_hint' => '/dich-vu', 'prefixes' => ['dich-vu']],
        'blog' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Cẩm Nang Cưới (Blog)', 'path_hint' => '/cam-nang-cuoi', 'prefixes' => ['cam-nang-cuoi', 'cam-nang']],
        'news' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Tin Tức & Ra Mắt Mẫu Thiệp', 'path_hint' => '/tin-tuc', 'prefixes' => ['tin-tuc']],
        'docs' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Trung Tâm Hướng Dẫn (Docs)', 'path_hint' => '/docs', 'prefixes' => ['docs']],
        'about' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Về Mây Hẹn', 'path_hint' => '/ve-may-hen', 'prefixes' => ['ve-may-hen']],
        'contact' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Liên Hệ', 'path_hint' => '/lien-he', 'prefixes' => ['lien-he']],
        'faq' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Câu Hỏi Thường Gặp (FAQ)', 'path_hint' => '/cau-hoi-thuong-gap', 'prefixes' => ['cau-hoi-thuong-gap']],
        'policies' => ['group' => 'Trang Chính & Thương Hiệu', 'label' => 'Chính Sách Bảo Mật, Cookie & Điều Khoản', 'path_hint' => '/chinh-sach-*', 'prefixes' => ['chinh-sach-bao-mat', 'chinh-sach-cookie', 'dieu-khoan-dich-vu', 'chinh-sach-giai-quyet-khieu-nai', 'chinh-sach-gia', 'chinh-sach-thanh-toan', 'dieu-kien-cung-cap-dich-vu', 'chinh-sach-giao-hang-va-hoan-tien', 'hinh-thuc-ho-tro-truc-tuyen']],

        // 2. Nhóm 11 Phân Hệ & Chuyên Trang SEO Công Cụ
        'wedding_date' => ['group' => 'Công Cụ & Chuyên Trang SEO', 'label' => 'Xem Ngày Cưới Hỏi Online', 'path_hint' => '/xem-ngay-cuoi', 'prefixes' => ['xem-ngay-cuoi']],
        'wedding_planner' => ['group' => 'Công Cụ & Chuyên Trang SEO', 'label' => 'Tổ Chức Đám Cưới & Nghi Lễ 3 Miền', 'path_hint' => '/to-chuc-dam-cuoi', 'prefixes' => ['to-chuc-dam-cuoi']],
        'caoly' => ['group' => 'Công Cụ & Chuyên Trang SEO', 'label' => 'Xem Cao Ly Đầu Hình (120 Tổ Hợp)', 'path_hint' => '/cao-ly-dau-hinh', 'prefixes' => ['cao-ly-dau-hinh']],
        'marriage_compatibility' => ['group' => 'Công Cụ & Chuyên Trang SEO', 'label' => 'Xem Tuổi Vợ Chồng Online', 'path_hint' => '/xem-tuoi-vo-chong', 'prefixes' => ['xem-tuoi-vo-chong']],
        'bazi' => ['group' => 'Công Cụ & Chuyên Trang SEO', 'label' => 'Tra Cứu Bát Tự & Tứ Trụ', 'path_hint' => '/bat-tu', 'prefixes' => ['bat-tu']],
        'calendar' => ['group' => 'Công Cụ & Chuyên Trang SEO', 'label' => 'Xem Lịch Âm & Lịch Vạn Niên', 'path_hint' => '/lich-am', 'prefixes' => ['lich-am']],
        'tarot' => ['group' => 'Công Cụ & Chuyên Trang SEO', 'label' => 'Xem Tarot Online (78 Lá RWS)', 'path_hint' => '/tarot', 'prefixes' => ['tarot']],
        'numerology' => ['group' => 'Công Cụ & Chuyên Trang SEO', 'label' => 'Tra Cứu Thần Số Học', 'path_hint' => '/than-so-hoc', 'prefixes' => ['than-so-hoc']],
        'tuvi' => ['group' => 'Công Cụ & Chuyên Trang SEO', 'label' => 'Lập Lá Số Tử Vi Đẩu Số', 'path_hint' => '/tu-vi', 'prefixes' => ['tu-vi']],
        'iching' => ['group' => 'Công Cụ & Chuyên Trang SEO', 'label' => 'Gieo Quẻ Kinh Dịch', 'path_hint' => '/gieo-que', 'prefixes' => ['gieo-que']],
        'family_tree' => ['group' => 'Công Cụ & Chuyên Trang SEO', 'label' => 'Dựng Gia Phả Dòng Họ', 'path_hint' => '/gia-pha', 'prefixes' => ['gia-pha']],
    ];

    /**
     * Ánh xạ các slot SEO công cụ -> tool_key tương ứng trong Admin Tool Hub.
     *
     * @var array<string, string>
     */
    public const SLOT_TO_TOOL_KEY = [
        'wedding_date' => 'wedding_journey',
        'wedding_planner' => 'wedding_journey',
        'caoly' => 'marriage_compatibility',
        'marriage_compatibility' => 'marriage_compatibility',
        'bazi' => 'bazi',
        'calendar' => 'calendar',
        'tarot' => 'tarot',
        'numerology' => 'numerology',
        'tuvi' => 'tuvi',
        'iching' => 'iching',
        'family_tree' => 'family_tree',
    ];

    /**
     * Ánh xạ ngược tool_key -> danh sách slot SEO cần đồng bộ 2 chiều.
     *
     * @var array<string, list<string>>
     */
    public const TOOL_TO_SLOTS = [
        'wedding_journey' => ['wedding_date', 'wedding_planner'],
        'marriage_compatibility' => ['caoly', 'marriage_compatibility'],
        'bazi' => ['bazi'],
        'calendar' => ['calendar'],
        'tarot' => ['tarot'],
        'numerology' => ['numerology'],
        'tuvi' => ['tuvi'],
        'iching' => ['iching'],
        'family_tree' => ['family_tree'],
    ];

    public static function getRawSlotBanner(string $slotKey): ?string
    {
        $val = trim((string) PlatformSetting::getValue(
            PlatformSetting::GROUP_BRAND_SEO,
            'seo_social_banner_'.$slotKey,
            ''
        ));

        return $val !== '' ? $val : null;
    }

    public static function getBanner(string $slotKey): ?string
    {
        $direct = self::getRawSlotBanner($slotKey);
        if ($direct !== null) {
            return $direct;
        }

        $mappedTool = self::SLOT_TO_TOOL_KEY[$slotKey] ?? null;
        if ($mappedTool !== null) {
            return ToolsGovernanceService::getRawToolSocialBanner($mappedTool);
        }

        return null;
    }

    /**
     * Lấy toàn bộ danh sách 23 phần SEO kèm đường dẫn banner hiện tại (nhóm theo group).
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public static function getAllGrouped(): array
    {
        $grouped = [];
        foreach (self::SLOTS as $key => $meta) {
            $banner = self::getBanner($key);
            $grouped[$meta['group']][] = array_merge($meta, [
                'key' => $key,
                'banner' => $banner,
                'preview_url' => $banner
                    ? (str_starts_with($banner, 'http') ? $banner : asset(ltrim($banner, '/')))
                    : null,
            ]);
        }

        return $grouped;
    }

    /**
     * Lưu hoặc gỡ Banner 1200×600 cho một phần SEO bất kỳ (đồng bộ 2 chiều sang module công cụ tương ứng).
     */
    public function saveBanner(string $slotKey, ?string $bannerPath, ?int $updatedBy = null, bool $syncTool = true): void
    {
        if (! isset(self::SLOTS[$slotKey])) {
            return;
        }
        $previousSlotBanner = self::getRawSlotBanner($slotKey);
        $cleanPath = $bannerPath ? trim($bannerPath) : '';
        $label = self::SLOTS[$slotKey]['label'];
        PlatformSetting::updateOrCreate(
            [
                'group' => PlatformSetting::GROUP_BRAND_SEO,
                'key' => 'seo_social_banner_'.$slotKey,
            ],
            [
                'value' => $cleanPath,
                'label' => "Banner Social 1200×600 — {$label}",
                'is_secret' => false,
                'updated_by' => $updatedBy,
            ]
        );

        if ($syncTool && isset(self::SLOT_TO_TOOL_KEY[$slotKey])) {
            $toolKey = self::SLOT_TO_TOOL_KEY[$slotKey];
            $toolSlots = self::TOOL_TO_SLOTS[$toolKey] ?? [];
            $isPrimaryForTool = count($toolSlots) === 1 || in_array($slotKey, ['marriage_compatibility', 'wedding_planner'], true);
            $currentToolBanner = ToolsGovernanceService::getRawToolSocialBanner($toolKey);

            if ($cleanPath !== '' && ($isPrimaryForTool || $currentToolBanner === null)) {
                app(ToolsGovernanceService::class)->saveToolSocialBanner($toolKey, $cleanPath, $updatedBy, syncSeoSlots: false);
            } elseif ($cleanPath === '' && $currentToolBanner !== null && ($isPrimaryForTool || $currentToolBanner === $previousSlotBanner)) {
                app(ToolsGovernanceService::class)->saveToolSocialBanner($toolKey, null, $updatedBy, syncSeoSlots: false);
            }
        }

        PlatformSetting::clearRuntimeCache();
    }

    /**
     * Upload file ảnh, tự động center-crop & resize chuẩn 1200×600px (WebP/JPG) và xóa file cũ nếu có.
     */
    public function storeUploadedBanner(string $slotKey, UploadedFile $file): string
    {
        $targetDir = public_path('storage/images/client/banners/seo');
        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $oldPath = self::getBanner($slotKey);
        $baseName = $slotKey.'-social-1200x600-'.now()->format('YmdHis');
        $savedFilename = $this->resize1200x600($file->getRealPath(), $targetDir, $baseName, (string) $file->getClientOriginalExtension());
        $newPath = '/storage/images/client/banners/seo/'.$savedFilename;

        $this->deleteLocalIfOwned($oldPath);

        return $newPath;
    }

    public function deleteLocalIfOwned(?string $path): void
    {
        if (! $path) {
            return;
        }
        $isOwned = str_starts_with($path, '/storage/images/client/banners/seo/')
            || str_starts_with($path, '/storage/images/client/banners/tools/');
        if (! $isOwned) {
            return;
        }
        if (self::isBannerPathReferencedElsewhere($path)) {
            return;
        }
        $abs = public_path(ltrim($path, '/'));
        if (File::exists($abs)) {
            File::delete($abs);
        }
    }

    public static function isBannerPathReferencedElsewhere(string $path, ?string $ignoreSlotKey = null, ?string $ignoreToolKey = null): bool
    {
        foreach (array_keys(self::SLOTS) as $slotKey) {
            if ($ignoreSlotKey !== null && $slotKey === $ignoreSlotKey) {
                continue;
            }
            if (self::getRawSlotBanner($slotKey) === $path) {
                return true;
            }
        }
        foreach (array_keys(ToolsGovernanceService::TOOL_REGISTRY) as $toolKey) {
            if ($ignoreToolKey !== null && $toolKey === $ignoreToolKey) {
                continue;
            }
            if (ToolsGovernanceService::getRawToolSocialBanner($toolKey) === $path) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tự động nhận diện Banner 1200×600 theo đường dẫn trang hiện tại (ưu tiên cấu hình riêng từng phần SEO, sau đó fallback sang banner module công cụ).
     *
     * @return array{slot_key: string, url: string, width: int, height: int, alt: string}|null
     */
    public static function resolveForPath(string $requestPath): ?array
    {
        $path = trim($requestPath, '/');

        foreach (self::SLOTS as $key => $meta) {
            foreach ($meta['prefixes'] as $prefix) {
                $matched = $prefix === ''
                    ? ($path === '')
                    : ($path === $prefix || str_starts_with($path, $prefix.'/'));

                if (! $matched) {
                    continue;
                }

                $stored = self::getBanner($key);
                if ($stored !== null) {
                    $absUrl = str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://')
                        ? $stored
                        : asset(ltrim($stored, '/'));

                    return [
                        'slot_key' => $key,
                        'url' => $absUrl,
                        'width' => self::WIDTH,
                        'height' => self::HEIGHT,
                        'alt' => "{$meta['label']} — Mây Hẹn (1200×600)",
                    ];
                }
            }
        }

        // Fallback tương thích ngược với banner lưu trực tiếp trong từng trang /admin/{moduleSlug}
        $toolBanner = ToolsGovernanceService::resolveSocialBannerForPath($requestPath);
        if ($toolBanner !== null) {
            return [
                'slot_key' => $toolBanner['tool_key'],
                'url' => $toolBanner['url'],
                'width' => $toolBanner['width'],
                'height' => $toolBanner['height'],
                'alt' => $toolBanner['alt'],
            ];
        }

        return null;
    }

    protected function resize1200x600(string $sourcePath, string $targetDir, string $baseName, string $fallbackExt): string
    {
        if (function_exists('imagecreatefromstring') && function_exists('imagecreatetruecolor')) {
            $raw = @file_get_contents($sourcePath);
            $src = $raw !== false ? @imagecreatefromstring($raw) : false;
            if ($src !== false) {
                $srcW = imagesx($src);
                $srcH = imagesy($src);
                if ($srcW > 0 && $srcH > 0) {
                    $targetRatio = self::WIDTH / self::HEIGHT;
                    $srcRatio = $srcW / $srcH;
                    if ($srcRatio > $targetRatio) {
                        $cropH = $srcH;
                        $cropW = (int) round($srcH * $targetRatio);
                        $cropX = (int) round(($srcW - $cropW) / 2);
                        $cropY = 0;
                    } else {
                        $cropW = $srcW;
                        $cropH = (int) round($srcW / $targetRatio);
                        $cropX = 0;
                        $cropY = (int) round(($srcH - $cropH) / 2);
                    }

                    $dst = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
                    imagealphablending($dst, true);
                    imagesavealpha($dst, true);
                    imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, self::WIDTH, self::HEIGHT, $cropW, $cropH);

                    if (function_exists('imagewebp')) {
                        $filename = $baseName.'.webp';
                        imagewebp($dst, $targetDir.DIRECTORY_SEPARATOR.$filename, 88);
                        imagedestroy($dst);
                        imagedestroy($src);

                        return $filename;
                    }

                    $filename = $baseName.'.jpg';
                    imagejpeg($dst, $targetDir.DIRECTORY_SEPARATOR.$filename, 90);
                    imagedestroy($dst);
                    imagedestroy($src);

                    return $filename;
                }
                imagedestroy($src);
            }
        }

        $ext = strtolower($fallbackExt) ?: 'jpg';
        $filename = $baseName.'.'.$ext;
        File::copy($sourcePath, $targetDir.DIRECTORY_SEPARATOR.$filename);

        return $filename;
    }
}
