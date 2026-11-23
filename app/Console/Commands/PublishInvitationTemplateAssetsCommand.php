<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Đồng bộ toàn bộ tệp tĩnh (CSS, JS, WebP, Font, Audio) của các mẫu thiệp cưới
 * từ resources/views/client/invitations/{slug}/ ra public/assets/invitations/{slug}/
 * để Web Server (Apache/LiteSpeed/Nginx) phục vụ trực tiếp với 0ms PHP-FPM.
 */
class PublishInvitationTemplateAssetsCommand extends Command
{
    protected $signature = 'templates:publish-assets
                            {--slug= : Chỉ đồng bộ một mẫu thiệp cụ thể}
                            {--force : Ghi đè toàn bộ tệp bất kể thời gian sửa đổi}';

    protected $description = 'Đồng bộ tài nguyên tĩnh (ảnh, font, CSS, JS, nhạc) của mẫu thiệp ra public/assets/invitations để tải tức thì không qua PHP-FPM';

    /** @var list<string> */
    protected const ALLOWED_EXTENSIONS = [
        'css', 'js', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'gif',
        'mp3', 'woff', 'woff2', 'ttf', 'otf',
    ];

    public function handle(): int
    {
        $sourceRoot = resource_path('views/client/invitations');
        $targetRoot = public_path('assets/invitations');

        if (! is_dir($sourceRoot)) {
            $this->error("Không tìm thấy thư mục mẫu thiệp: {$sourceRoot}");

            return self::FAILURE;
        }

        $onlySlug = trim((string) $this->option('slug'));
        $force = (bool) $this->option('force');

        $slugDirs = $onlySlug !== ''
            ? [$sourceRoot.DIRECTORY_SEPARATOR.$onlySlug]
            : (glob($sourceRoot.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR) ?: []);

        $copied = 0;
        $skipped = 0;
        $templatesProcessed = 0;

        foreach ($slugDirs as $tplDir) {
            if (! is_dir($tplDir)) {
                continue;
            }
            $slug = basename($tplDir);
            if (! preg_match('/^[a-z0-9_-]+$/', $slug)) {
                continue;
            }

            $templatesProcessed++;
            $destBase = $targetRoot.DIRECTORY_SEPARATOR.$slug;
            File::ensureDirectoryExists($destBase);

            // 1. Copy styles.css và script.js gốc của mẫu
            foreach (['styles.css', 'script.js'] as $rootAsset) {
                $src = $tplDir.DIRECTORY_SEPARATOR.$rootAsset;
                if (is_file($src)) {
                    $dst = $destBase.DIRECTORY_SEPARATOR.$rootAsset;
                    if ($this->copyIfNewer($src, $dst, $force)) {
                        $copied++;
                    } else {
                        $skipped++;
                    }
                }
            }

            // 2. Copy toàn bộ tệp tĩnh hợp lệ trong thư mục assets/
            $assetsDir = $tplDir.DIRECTORY_SEPARATOR.'assets';
            if (! is_dir($assetsDir)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($assetsDir, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }
                $ext = strtolower($file->getExtension());
                if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                    continue;
                }

                $relative = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($assetsDir))), '/');
                $dst = $destBase.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

                if ($this->copyIfNewer($file->getPathname(), $dst, $force)) {
                    $copied++;
                } else {
                    $skipped++;
                }
            }
        }

        $this->info("Đã đồng bộ {$templatesProcessed} mẫu thiệp -> public/assets/invitations/ (cập nhật: {$copied} tệp, giữ nguyên: {$skipped} tệp).");

        return self::SUCCESS;
    }

    protected function copyIfNewer(string $src, string $dst, bool $force): bool
    {
        if (! $force && is_file($dst) && filemtime($dst) >= filemtime($src) && filesize($dst) === filesize($src)) {
            return false;
        }

        $dir = dirname($dst);
        if (! is_dir($dir)) {
            File::ensureDirectoryExists($dir);
        }

        return copy($src, $dst);
    }
}
