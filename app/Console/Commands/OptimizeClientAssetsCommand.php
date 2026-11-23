<?php

namespace App\Console\Commands;

use App\Http\Controllers\Client\HomeController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OptimizeClientAssetsCommand extends Command
{
    protected $signature = 'mayhen:optimize-assets';

    protected $description = 'Rút gọn CSS/JS client (.min.css / .min.js) và nén tối ưu ảnh Hero Banner / ảnh mockup trang chủ bằng PHP GD';

    /**
     * @var array<int, string>
     */
    private const CSS_TARGETS = [
        'assets/client/css/pages/home.css',
        'assets/client/css/pages/templates.css',
        'assets/client/css/components/footer.css',
        'assets/client/css/components/bottom-nav.css',
        'assets/client/css/components/chatbot.css',
        'assets/client/css/components/atelier-loader.css',
        'assets/client/css/components/forms.css',
        'assets/client/css/components/seasonal-fall.css',
    ];

    /**
     * @var array<int, string>
     */
    private const JS_TARGETS = [
        'assets/client/js/pages/home.js',
        'assets/client/js/components/chatbot.js',
        'assets/client/js/components/seasonal-fall.js',
        'assets/client/js/components/header.js',
        'assets/client/js/components/atelier-loader.js',
        'assets/client/js/common/dom.js',
    ];

    public function handle(): int
    {
        $cssCount = 0;
        foreach (self::CSS_TARGETS as $rel) {
            $src = public_path($rel);
            if (! is_file($src)) {
                continue;
            }
            $dst = preg_replace('/\.css$/i', '.min.css', $src);
            $raw = (string) file_get_contents($src);
            $min = $this->minifyCss($raw);
            file_put_contents($dst, $min);
            $cssCount++;
        }

        $jsCount = 0;
        foreach (self::JS_TARGETS as $rel) {
            $src = public_path($rel);
            if (! is_file($src)) {
                continue;
            }
            $dst = preg_replace('/\.js$/i', '.min.js', $src);
            $raw = (string) file_get_contents($src);
            $min = $this->minifyJsSafely($raw);
            file_put_contents($dst, $min);
            $jsCount++;
        }

        $imgOptimized = $this->optimizeHeroAndMockupImages();

        HomeController::clearMemoization();

        $this->info("Đã rút gọn {$cssCount} tệp CSS, {$jsCount} tệp JS và tối ưu {$imgOptimized} ảnh trọng tâm.");

        return self::SUCCESS;
    }

    private function minifyCss(string $css): string
    {
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css) ?? $css;
        $css = preg_replace('/\s+/', ' ', $css) ?? $css;
        $css = preg_replace('/\s*([{}:;,>~])\s*/', '$1', $css) ?? $css;
        $css = str_replace(';}', '}', $css);

        return trim($css);
    }

    /**
     * Rút gọn JS an toàn (chỉ bỏ comment block/line ngoài chuỗi/regex và khoảng trắng đầu/cuối dòng),
     * giữ nguyên xuống dòng để không bao giờ phá vỡ ASI (Automatic Semicolon Insertion).
     */
    private function minifyJsSafely(string $js): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $js);
        $lines = explode("\n", $normalized);
        $out = [];
        $inBlockComment = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if ($inBlockComment) {
                $endPos = strpos($trimmed, '*/');
                if ($endPos !== false) {
                    $inBlockComment = false;
                    $rest = trim(substr($trimmed, $endPos + 2));
                    if ($rest !== '' && ! str_starts_with($rest, '//')) {
                        $out[] = $rest;
                    }
                }

                continue;
            }

            if (str_starts_with($trimmed, '/*')) {
                $endPos = strpos($trimmed, '*/', 2);
                if ($endPos === false) {
                    $inBlockComment = true;
                } else {
                    $rest = trim(substr($trimmed, $endPos + 2));
                    if ($rest !== '' && ! str_starts_with($rest, '//')) {
                        $out[] = $rest;
                    }
                }

                continue;
            }

            if (str_starts_with($trimmed, '//')) {
                continue;
            }

            $out[] = $trimmed;
        }

        return implode("\n", $out)."\n";
    }

    private function optimizeHeroAndMockupImages(): int
    {
        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            return 0;
        }

        $count = 0;

        // 1. Nén các ảnh Hero Banner quá nặng (> 180 KB) trong storage/app/public/images/client/banners/
        $bannerDirs = array_unique(array_filter([
            storage_path('app/public/images/client/banners'),
            public_path('storage/images/client/banners'),
        ], fn (string $d): bool => File::isDirectory($d)));

        foreach ($bannerDirs as $dir) {
            foreach (File::glob($dir.'/banner-home*.webp') as $file) {
                if (filesize($file) <= 180 * 1024) {
                    continue;
                }
                $isMobile = str_contains(basename($file), 'mobile');
                $maxWidth = $isMobile ? 750 : 1440;
                $quality = $isMobile ? 74 : 78;
                if ($this->resizeAndCompressWebp($file, $file, $maxWidth, $quality)) {
                    $count++;
                }
            }
        }

        // 2. Tạo bản kích thước vừa khít (360px) cho 2 ảnh mockup điện thoại trong Hero
        $mockupTargets = [
            [
                'src' => resource_path('views/client/invitations/giua-muon-ngan-vi-sao/assets/hero-placeholder.webp'),
                'dst' => public_path('assets/client/images/hero-mockup-center.webp'),
                'maxW' => 360,
                'q' => 78,
            ],
            [
                'src' => resource_path('views/client/invitations/hanh-phuc-mang-ten-chung-minh/assets/hero-couple.webp'),
                'dst' => public_path('assets/client/images/hero-mockup-right.webp'),
                'maxW' => 320,
                'q' => 76,
            ],
        ];

        File::ensureDirectoryExists(public_path('assets/client/images'));
        foreach ($mockupTargets as $t) {
            if (is_file($t['src']) && $this->resizeAndCompressWebp($t['src'], $t['dst'], $t['maxW'], $t['q'])) {
                $count++;
            }
        }

        return $count;
    }

    private function resizeAndCompressWebp(string $srcPath, string $dstPath, int $maxWidth, int $quality): bool
    {
        $img = @imagecreatefromwebp($srcPath);
        if (! $img) {
            return false;
        }

        $origW = imagesx($img);
        $origH = imagesy($img);
        if ($origW <= 0 || $origH <= 0) {
            imagedestroy($img);

            return false;
        }

        $targetW = min($origW, $maxWidth);
        $targetH = (int) round($origH * ($targetW / $origW));

        $canvas = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $img, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);

        $ok = @imagewebp($canvas, $dstPath, $quality);
        imagedestroy($canvas);
        imagedestroy($img);

        return (bool) $ok;
    }
}
