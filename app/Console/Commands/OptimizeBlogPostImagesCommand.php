<?php

namespace App\Console\Commands;

use App\Http\Controllers\Client\BlogController;
use App\Http\Controllers\Client\SitemapController;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\BlogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OptimizeBlogPostImagesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'blog:optimize-post-images
                            {--quality=40 : Chất lượng ảnh WebP đầu ra (1-100, mặc định 40 = giảm 60% chất lượng)}
                            {--max-width=1200 : Chiều rộng tối đa (px) giúp tối ưu tốc độ tải web}
                            {--keep-originals : Giữ lại ảnh gốc sau khi chuyển sang WebP}
                            {--export-seeder : Tự động xuất lại database/seeders/data/blog_posts_380.json.gz sau khi cập nhật DB}';

    /**
     * @var string
     */
    protected $description = 'Chuyển toàn bộ ảnh trong storage/app/public/images/client/posts sang WebP (giảm 60% chất lượng) và cập nhật lại toàn bộ đường dẫn ảnh trong bảng blog_posts';

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('PHP GD trên máy chủ chưa bật hỗ trợ imagewebp().');

            return self::FAILURE;
        }

        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $quality = max(10, min(95, (int) $this->option('quality')));
        $maxWidth = max(400, (int) $this->option('max-width'));
        $keepOriginals = (bool) $this->option('keep-originals');
        $exportSeeder = (bool) $this->option('export-seeder');

        $baseDir = storage_path('app/public/images/client/posts');
        if (! File::isDirectory($baseDir)) {
            $this->error("Không tìm thấy thư mục ảnh: {$baseDir}");

            return self::FAILURE;
        }

        $allFiles = File::allFiles($baseDir);
        $totalFiles = count($allFiles);

        if ($totalFiles === 0) {
            $this->warn('Không có tệp ảnh nào trong thư mục posts.');

            return self::SUCCESS;
        }

        $this->info("Bắt đầu chuyển đổi {$totalFiles} ảnh sang WebP (quality={$quality}, max-width={$maxWidth}px)...");

        $bytesBefore = 0;
        $bytesAfter = 0;
        $converted = 0;
        $recompressed = 0;
        $failed = 0;
        $urlMap = [];

        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        foreach ($allFiles as $file) {
            $absPath = $file->getPathname();
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $bar->advance();

                continue;
            }

            $origSize = (int) filesize($absPath);
            $bytesBefore += $origSize;

            $relFromPosts = str_replace('\\', '/', ltrim(substr($absPath, strlen($baseDir)), '/\\'));
            $oldUrl = '/storage/images/client/posts/'.$relFromPosts;

            $targetAbsPath = preg_replace('/\.[^.]+$/', '.webp', $absPath);
            $targetRel = preg_replace('/\.[^.]+$/', '.webp', $relFromPosts);
            $newUrl = '/storage/images/client/posts/'.$targetRel;

            $image = $this->loadGdImage($absPath, $ext);
            if (! $image) {
                $failed++;
                $bytesAfter += $origSize;
                $bar->advance();

                continue;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            if ($width > $maxWidth && $width > 0 && $height > 0) {
                $newHeight = (int) round($height * ($maxWidth / $width));
                $resized = imagecreatetruecolor($maxWidth, $newHeight);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                imagefilledrectangle($resized, 0, 0, $maxWidth, $newHeight, $transparent);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            } else {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }

            $tmpWebp = $targetAbsPath.'.tmp';
            $ok = @imagewebp($image, $tmpWebp, $quality);
            imagedestroy($image);

            if (! $ok || ! is_file($tmpWebp) || filesize($tmpWebp) === 0) {
                @unlink($tmpWebp);
                $failed++;
                $bytesAfter += $origSize;
                $bar->advance();

                continue;
            }

            $newSize = (int) filesize($tmpWebp);

            // Nếu là file .webp sẵn mà nén lại không nhẹ hơn thì giữ nguyên bản cũ
            if ($ext === 'webp' && $newSize >= $origSize) {
                @unlink($tmpWebp);
                $bytesAfter += $origSize;
                $bar->advance();

                continue;
            }

            @rename($tmpWebp, $targetAbsPath);
            if (is_file($tmpWebp)) {
                File::move($tmpWebp, $targetAbsPath);
            }

            $bytesAfter += (int) filesize($targetAbsPath);

            if ($ext !== 'webp') {
                $urlMap[$oldUrl] = $newUrl;
                $converted++;
                if (! $keepOriginals && $absPath !== $targetAbsPath) {
                    @unlink($absPath);
                }
            } else {
                $recompressed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Cập nhật toàn bộ đường dẫn ảnh trong bảng blog_posts
        $updatedPosts = $this->updateBlogPostsUrls($urlMap, $baseDir);

        BlogService::clearCache();
        BlogController::clearMemoization();
        SitemapController::clearCache(regenerateStaticFiles: false);

        if ($exportSeeder) {
            $this->exportSeederDump();
        }

        $beforeMb = round($bytesBefore / 1048576, 2);
        $afterMb = round($bytesAfter / 1048576, 2);
        $savedPct = $bytesBefore > 0 ? round((1 - ($bytesAfter / $bytesBefore)) * 100, 1) : 0;

        $this->info("✓ Đã chuyển sang WebP: {$converted} ảnh | Nén lại WebP: {$recompressed} ảnh | Lỗi: {$failed}");
        $this->info("✓ Dung lượng: {$beforeMb} MB -> {$afterMb} MB (Tiết kiệm {$savedPct}%)");
        $this->info("✓ Đã cập nhật đường dẫn ảnh .webp cho {$updatedPosts} bài viết trong bảng blog_posts.");

        return self::SUCCESS;
    }

    /**
     * @return \GdImage|false
     */
    protected function loadGdImage(string $absPath, string $ext)
    {
        return match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($absPath),
            'png' => @imagecreatefrompng($absPath),
            'gif' => @imagecreatefromgif($absPath),
            'webp' => @imagecreatefromwebp($absPath),
            default => false,
        };
    }

    /**
     * @param  array<string, string>  $urlMap
     */
    protected function updateBlogPostsUrls(array $urlMap, string $baseDir): int
    {
        $updated = 0;

        BlogPost::withoutEvents(function () use (&$updated, $urlMap, $baseDir): void {
            BlogPost::orderBy('id')->chunk(100, function ($posts) use (&$updated, $urlMap, $baseDir): void {
                foreach ($posts as $post) {
                    $origCover = (string) ($post->cover_image_url ?? '');
                    $origContent = (string) ($post->content ?? '');

                    $newCover = $this->replacePostImageUrls($origCover, $urlMap, $baseDir);
                    $newContent = $this->replacePostImageUrls($origContent, $urlMap, $baseDir);

                    if ($newCover !== $origCover || $newContent !== $origContent) {
                        $post->cover_image_url = $newCover;
                        $post->content = $newContent;
                        $post->save();
                        $updated++;
                    }
                }
            });
        });

        return $updated;
    }

    /**
     * @param  array<string, string>  $urlMap
     */
    protected function replacePostImageUrls(string $text, array $urlMap, string $baseDir): string
    {
        if ($text === '' || ! str_contains($text, '/storage/images/client/posts/')) {
            return $text;
        }

        if (! empty($urlMap)) {
            $text = strtr($text, $urlMap);
        }

        // Bắt nốt các link .png/.jpg/.jpeg/.gif trỏ tới /storage/images/client/posts/ nếu bản .webp đã tồn tại trên đĩa
        return (string) preg_replace_callback(
            '/(\/storage\/images\/client\/posts\/[^"\'\s<>]+?)\.(png|jpe?g|gif)(?=["\'\s<>]|$)/i',
            static function (array $m) use ($baseDir): string {
                $relPath = substr($m[1], strlen('/storage/images/client/posts/'));
                $webpAbs = $baseDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relPath).'.webp';
                if (is_file($webpAbs)) {
                    return $m[1].'.webp';
                }

                return $m[0];
            },
            $text
        );
    }

    protected function exportSeederDump(): void
    {
        $catSlugById = BlogCategory::pluck('slug', 'id')->all();

        $posts = BlogPost::where('status', 'published')
            ->orderBy('id')
            ->get()
            ->map(static function (BlogPost $p) use ($catSlugById): array {
                return [
                    'category_slug' => $catSlugById[$p->category_id] ?? 'ke-hoach-le-nghi-ngay-cuoi',
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'summary' => $p->summary,
                    'content' => $p->content,
                    'cover_image_url' => $p->cover_image_url,
                    'author_name' => $p->author_name,
                    'tags' => $p->tags,
                    'status' => $p->status,
                    'published_at' => $p->published_at?->toDateTimeString(),
                    'reading_time_minutes' => $p->reading_time_minutes,
                    'view_count' => $p->view_count,
                    'is_featured' => (bool) $p->is_featured,
                    'meta_title' => $p->meta_title,
                    'meta_description' => $p->meta_description,
                    'meta_keywords' => $p->meta_keywords,
                ];
            })
            ->values()
            ->all();

        $dir = database_path('seeders/data');
        File::ensureDirectoryExists($dir);

        $gzPath = $dir.DIRECTORY_SEPARATOR.'blog_posts_380.json.gz';
        File::put($gzPath, gzencode((string) json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 9));

        $kb = round(File::size($gzPath) / 1024, 2);
        $this->info("✓ Đã xuất lại gói seeder database/seeders/data/blog_posts_380.json.gz ({$kb} KB).");
    }
}
