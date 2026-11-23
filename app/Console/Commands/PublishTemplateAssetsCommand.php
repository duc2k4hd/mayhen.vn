<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishTemplateAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mayhen:publish-template-assets
                            {--copy : Sao chép tệp tĩnh thay vì tạo symlink (dùng cho hosting chặn symlink)}';

    /**
     * The console command description.
     */
    protected $description = 'Xuất bản tài nguyên tĩnh (ảnh, CSS, JS, font) của các mẫu thiệp sang public/assets/invitations/{slug}/assets để web server phục vụ trực tiếp không qua PHP-FPM và không xung đột route /mau-thiep';

    /**
     * Danh sách đuôi tệp tĩnh an toàn được phép sao chép khi chạy chế độ --copy.
     *
     * @var array<int, string>
     */
    private const ALLOWED_EXTENSIONS = [
        'webp', 'png', 'jpg', 'jpeg', 'gif', 'svg',
        'css', 'js', 'woff', 'woff2', 'ttf', 'otf', 'mp3',
    ];

    public function handle(): int
    {
        $sourceRoot = resource_path('views/client/invitations');
        $publicRoot = public_path('assets/invitations');

        // Dọn dẹp thư mục cũ public/mau-thiep nếu từng tạo trước đó (tránh đè route Laravel /mau-thiep)
        $legacyDir = public_path('mau-thiep');
        if (File::isDirectory($legacyDir)) {
            File::deleteDirectory($legacyDir);
        }

        if (! File::isDirectory($sourceRoot)) {
            $this->error("Không tìm thấy thư mục nguồn: {$sourceRoot}");

            return self::FAILURE;
        }

        File::ensureDirectoryExists($publicRoot);

        $forceCopy = (bool) $this->option('copy');
        $linkedCount = 0;
        $copiedCount = 0;

        foreach (File::directories($sourceRoot) as $templateDir) {
            $slug = basename($templateDir);
            if (! preg_match('/^[a-z0-9_-]+$/', $slug)) {
                continue;
            }

            $assetsSource = $templateDir.DIRECTORY_SEPARATOR.'assets';
            if (! File::isDirectory($assetsSource)) {
                continue;
            }

            $slugPublicDir = $publicRoot.DIRECTORY_SEPARATOR.$slug;
            $assetsTarget = $slugPublicDir.DIRECTORY_SEPARATOR.'assets';
            File::ensureDirectoryExists($slugPublicDir);

            // Nếu đã có symlink hợp lệ và không ép --copy thì bỏ qua
            if (! $forceCopy && is_link($assetsTarget)) {
                $linkedCount++;

                continue;
            }

            if (! $forceCopy) {
                if (File::isDirectory($assetsTarget) && ! is_link($assetsTarget)) {
                    File::deleteDirectory($assetsTarget);
                }

                if (@symlink($assetsSource, $assetsTarget)) {
                    $linkedCount++;

                    continue;
                }
            }

            // Fallback an toàn: sao chép chỉ các file tĩnh trong whitelist (không bao giờ copy PHP/Blade/JSON)
            $this->copySafeStaticFiles($assetsSource, $assetsTarget);
            $copiedCount++;
        }

        $this->info("Hoàn tất xuất bản tài nguyên mẫu thiệp: {$linkedCount} symlink, {$copiedCount} thư mục sao chép tĩnh.");

        return self::SUCCESS;
    }

    private function copySafeStaticFiles(string $sourceDir, string $targetDir): void
    {
        File::ensureDirectoryExists($targetDir);

        foreach (File::allFiles($sourceDir) as $file) {
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            $relativePath = $file->getRelativePathname();
            $destination = $targetDir.DIRECTORY_SEPARATOR.$relativePath;
            File::ensureDirectoryExists(dirname($destination));

            if (! file_exists($destination) || filemtime($file->getPathname()) > filemtime($destination)) {
                File::copy($file->getPathname(), $destination);
            }
        }
    }
}
