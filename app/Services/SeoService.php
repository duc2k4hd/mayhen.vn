<?php

namespace App\Services;

use App\Models\SeoRedirect;

class SeoService
{
    /**
     * Phát hiện vòng lặp chuyển hướng vô tận (Infinite Redirect Loop Detection).
     * Duyệt qua chuỗi chuyển hướng từ targetPath. Nếu gặp lại sourcePath trong tối đa 15 bước nhảy thì có vòng lặp.
     */
    public static function detectLoop(string $sourcePath, string $targetPath, ?int $ignoreId = null): bool
    {
        $normalizedSource = SeoRedirect::normalizePath($sourcePath);
        $normalizedTarget = SeoRedirect::normalizePath($targetPath);

        // Trường hợp tự trỏ vào chính nó (A -> A)
        if ($normalizedSource === $normalizedTarget) {
            return true;
        }

        // Lấy toàn bộ danh sách redirect đang hoạt động
        $redirects = SeoRedirect::active()
            ->when($ignoreId, function ($q) use ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            })
            ->pluck('target_url', 'source_path')
            ->toArray();

        $current = $normalizedTarget;
        $visited = [$normalizedSource => true];
        $steps = 0;
        $maxSteps = 15;

        while (isset($redirects[$current]) && $steps < $maxSteps) {
            $nextTarget = SeoRedirect::normalizePath($redirects[$current]);

            if (isset($visited[$nextTarget])) {
                return true; // Phát hiện chu trình vòng lặp!
            }

            $visited[$current] = true;
            $current = $nextTarget;
            $steps++;
        }

        return false;
    }

    /**
     * Khớp đường dẫn yêu cầu với danh sách redirect.
     */
    public static function matchRedirect(string $requestUri): ?SeoRedirect
    {
        $normalized = SeoRedirect::normalizePath($requestUri);

        return SeoRedirect::active()
            ->where('source_path', $normalized)
            ->first();
    }

    /**
     * Ghi nhận lượt chuyển hướng (hits).
     */
    public static function recordHit(SeoRedirect $redirect): void
    {
        $redirect->increment('hits');
        $redirect->updateQuietly(['last_hit_at' => now()]);
    }

    /**
     * Thanh tra sức khỏe SEO (SEO Health & Audit Scanner) cho các trang trọng yếu.
     *
     * @return array{score: int, checks: array<int, array<string, mixed>>, warnings_count: int, passed_count: int}
     */
    public static function runHealthAudit(): array
    {
        $pagesToAudit = [
            [
                'name' => 'Trang Chủ',
                'url' => route('home'),
                'title' => 'Tạo Thiệp Cưới Online Miễn Phí, Đẹp & Sang Trọng | Mây Hẹn',
                'description' => 'Mây Hẹn — Nền tảng tạo thiệp cưới online cao cấp, phong cách Quiet Luxury, hiệu ứng mở phong bì hoàng gia, tối ưu điện thoại và quản lý khách mời chu đáo.',
                'canonical' => route('home'),
                'og_image' => asset('storage/images/client/banners/banner-home.webp'),
                'robots' => 'index, follow',
            ],
            [
                'name' => 'Kho Mẫu Thiệp',
                'url' => route('templates.index'),
                'title' => '99+ Mẫu Thiệp Cưới Đẹp, Mẫu Thiệp Cưới Online Xu Hướng '.date('Y').' | Mây Hẹn',
                'description' => 'Bộ sưu tập mẫu thiệp cưới online phong cách Quiet Luxury, thiệp cưới hoa cỏ Flora, thiệp tạp chí Editorial và phong bì tem sáp hoàng gia.',
                'canonical' => route('templates.index'),
                'og_image' => asset('assets/client/images/home/hero-wedding-card.webp'),
                'robots' => 'index, follow',
            ],
            [
                'name' => 'Bảng Giá & Gói Cưới',
                'url' => route('pricing'),
                'title' => 'Bảng Giá Thiệp Cưới Online Chi Tiết '.date('Y').' | Mây Hẹn',
                'description' => 'Bảng giá dịch vụ tạo thiệp cưới online Mây Hẹn: Miễn phí trọn đời (Free), Nâng cấp cao cấp (Plus, Pro) không giới hạn tính năng.',
                'canonical' => route('pricing'),
                'og_image' => asset('assets/client/images/home/hero-wedding-card.webp'),
                'robots' => 'index, follow',
            ],
            [
                'name' => 'Dịch Vụ & Tiện Ích Cưới',
                'url' => route('services'),
                'title' => 'Dịch Vụ In Thiệp Cưới Cao Cấp '.date('Y').' — In Thiệp Cưới Đẹp, Tinh Tế | Mây Hẹn',
                'description' => 'Khám phá các dịch vụ in ấn thiệp cưới cao cấp, phong bì tem sáp 3D, thiệp cưới số và giải pháp quản lý khách mời thông minh.',
                'canonical' => route('services'),
                'og_image' => asset('assets/client/images/home/hero-wedding-card.webp'),
                'robots' => 'index, follow',
            ],
            [
                'name' => 'Cẩm Nang Cưới & Chuyên Đề',
                'url' => route('blog.index'),
                'title' => 'Cẩm Nang Cưới — Kinh Nghiệm & Ý Tưởng Cưới Tinh Tế | Mây Hẹn',
                'description' => 'Khám phá cẩm nang cưới phong cách Quiet Luxury từ Mây Hẹn: ý tưởng thiết kế thiệp, nghi thức lễ cưới, quản lý bàn tiệc.',
                'canonical' => route('blog.index'),
                'og_image' => asset('assets/client/images/home/hero-wedding-card.webp'),
                'robots' => 'index, follow',
            ],
            [
                'name' => 'Điều Khoản Dịch Vụ',
                'url' => route('terms'),
                'title' => 'Điều Khoản Dịch Vụ — Mây Hẹn',
                'description' => 'Quy định và điều khoản sử dụng nền tảng thiệp cưới online Mây Hẹn.',
                'canonical' => route('terms'),
                'og_image' => asset('storage/images/client/business/mayhen-favicon.png'),
                'robots' => 'index, follow',
            ],
            [
                'name' => 'Chính Sách Bảo Mật (Luật 91/2025/QH15)',
                'url' => route('privacy'),
                'title' => 'Chính Sách Bảo Mật Dữ Liệu Cá Nhân — Mây Hẹn',
                'description' => 'Chính sách bảo vệ dữ liệu cá nhân theo quy định pháp luật Việt Nam tại Mây Hẹn (mayhen.vn).',
                'canonical' => route('privacy'),
                'og_image' => asset('storage/images/client/business/mayhen-favicon.png'),
                'robots' => 'index, follow',
            ],
        ];

        $checks = [];
        $totalChecks = 0;
        $passedChecks = 0;

        foreach ($pagesToAudit as $page) {
            $issues = [];

            // 1. Kiểm tra Title
            $titleLen = mb_strlen($page['title']);
            if (empty($page['title'])) {
                $issues[] = 'Thiếu thẻ Meta Title';
            } elseif ($titleLen < 30) {
                $issues[] = "Tiêu đề hơi ngắn ({$titleLen} ký tự, khuyến nghị 40-70)";
            } elseif ($titleLen > 80) {
                $issues[] = "Tiêu đề hơi dài ({$titleLen} ký tự, Google có thể cắt bớt)";
            }

            // 2. Kiểm tra Meta Description
            $descLen = mb_strlen($page['description']);
            if (empty($page['description'])) {
                $issues[] = 'Thiếu thẻ Meta Description';
            } elseif ($descLen < 80) {
                $issues[] = "Mô tả hơi ngắn ({$descLen} ký tự, khuyến nghị 120-160)";
            } elseif ($descLen > 200) {
                $issues[] = "Mô tả vượt quá 200 ký tự ({$descLen} ký tự)";
            }

            // 3. Kiểm tra Canonical & OpenGraph
            if (empty($page['canonical'])) {
                $issues[] = 'Thiếu đường dẫn Canonical';
            }
            if (empty($page['og_image'])) {
                $issues[] = 'Thiếu ảnh chia sẻ mạng xã hội (og:image)';
            }

            $isHealthy = empty($issues);
            $totalChecks += 4;
            $passedChecks += (4 - count($issues));

            $checks[] = [
                'name' => $page['name'],
                'url' => $page['url'],
                'title' => $page['title'],
                'title_length' => $titleLen,
                'description' => $page['description'],
                'description_length' => $descLen,
                'has_canonical' => ! empty($page['canonical']),
                'has_og_image' => ! empty($page['og_image']),
                'robots' => $page['robots'],
                'is_healthy' => $isHealthy,
                'issues' => $issues,
            ];
        }

        $score = $totalChecks > 0 ? (int) round(($passedChecks / $totalChecks) * 100) : 100;
        $warningsCount = count(array_filter($checks, fn ($c) => ! $c['is_healthy']));
        $passedCount = count($checks) - $warningsCount;

        return [
            'score' => $score,
            'checks' => $checks,
            'warnings_count' => $warningsCount,
            'passed_count' => $passedCount,
        ];
    }
}
