<?php

namespace App\Console\Commands;

use App\Http\Controllers\Client\BlogController;
use App\Http\Controllers\Client\SitemapController;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\BlogService;
use Database\Seeders\BlogSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportRewrittenBlogPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:import-rewritten
                            {--source=D:/Project/PHP/Cào dữ liệu cho Mây Hẹn/paperlesspost.com/output_mayhen/articles : Thư mục chứa các file JSON bài viết đã biên tập}
                            {--images-base=D:/Project/PHP/Cào dữ liệu cho Mây Hẹn/paperlesspost.com : Thư mục gốc chứa thư mục ảnh imgs/main và imgs/extra}
                            {--skip-images : Bỏ qua bước sao chép file ảnh vật lý vào storage}
                            {--verify-only : Chỉ kiểm định dữ liệu bài viết, ảnh và internal link đã có trong CSDL}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nhập toàn bộ 377 bài viết Cẩm nang cưới chuẩn SEO từ bộ JSON đã kiểm định vào CSDL và xuất bản công khai kèm đồng bộ hình ảnh.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourceDir = rtrim((string) $this->option('source'), '/\\');
        $imagesBaseDir = rtrim((string) $this->option('images-base'), '/\\');
        $skipImages = (bool) $this->option('skip-images');

        if ((bool) $this->option('verify-only')) {
            return $this->verifyPublishedDatabasePosts();
        }

        if (! is_dir($sourceDir)) {
            $this->error("Không tìm thấy thư mục nguồn JSON: {$sourceDir}");

            return self::FAILURE;
        }

        $jsonFiles = glob($sourceDir.'/*.json') ?: [];
        sort($jsonFiles);

        if (empty($jsonFiles)) {
            $this->error("Không tìm thấy file JSON nào trong: {$sourceDir}");

            return self::FAILURE;
        }

        $this->info('1. Khởi tạo 4 chuyên mục chuẩn và 3 bài viết nền tảng (BlogSeeder)...');
        app(BlogSeeder::class)->run();

        $categoriesBySlug = BlogCategory::all()->keyBy('slug');
        $adminUserId = User::where('role', 'admin')->value('id') ?? User::value('id');

        $targetImageRoot = storage_path('app/public/images/client/posts');
        File::ensureDirectoryExists($targetImageRoot.'/main');
        File::ensureDirectoryExists($targetImageRoot.'/extra');

        $copiedImages = [];
        $copiedCount = 0;
        $importedCount = 0;
        $now = now();

        $featuredOrders = [1, 2, 9];

        $categoryTagMap = [
            'y-tuong-phong-cach-thiep' => ['Ý Tưởng Thiệp Cưới', 'Phong Cách Thiệp', 'Thiệp Mời Online'],
            'ke-hoach-le-nghi-ngay-cuoi' => ['Kế Hoạch Cưới', 'Lễ Nghi Ngày Cưới', 'Chuẩn Bị Đám Cưới'],
            'kinh-nghiem-khach-moi-rsvp' => ['Khách Mời & RSVP', 'Lời Mời Tiệc', 'Quản Lý Khách Mời'],
            'xu-huong-cuoi-toi-gian' => ['Xu Hướng Cưới', 'Tiệc Tối Giản', 'Cẩm Nang Tổ Chức Tiệc'],
        ];

        $this->info(sprintf('2. Đang xử lý %d bài viết và đồng bộ hình ảnh...', count($jsonFiles)));

        // Đẩy 3 bài viết gốc trong BlogSeeder lùi thời gian xuất bản để 73 bài Cưới Ưu tiên 1 hiển thị trên trang đầu
        BlogPost::withoutEvents(function () use ($now) {
            BlogPost::whereIn('slug', [
                'nghe-thuat-thiep-cuoi-quiet-luxury-khi-su-tinh-te-len-ngoi',
                'thoi-diem-vang-de-gui-thiep-cuoi-online',
                'bi-quyet-quan-ly-ban-tiec-bang-rsvp-thong-minh',
            ])->update([
                'is_featured' => false,
                'published_at' => $now->copy()->subHours(800),
            ]);
        });

        DB::beginTransaction();

        try {
            BlogPost::withoutEvents(function () use (
                $jsonFiles,
                $imagesBaseDir,
                $targetImageRoot,
                $skipImages,
                $categoriesBySlug,
                $categoryTagMap,
                $adminUserId,
                $now,
                $featuredOrders,
                &$copiedImages,
                &$copiedCount,
                &$importedCount
            ) {
                foreach ($jsonFiles as $file) {
                    $raw = (string) file_get_contents($file);
                    $data = json_decode($raw, true);

                    if (! is_array($data) || empty($data['slug']) || empty($data['title'])) {
                        throw new \RuntimeException('Dữ liệu JSON không hợp lệ tại file: '.basename($file));
                    }

                    $queueOrder = (int) ($data['queue_order'] ?? ($importedCount + 1));
                    $catSlug = (string) ($data['category_slug'] ?? 'y-tuong-phong-cach-thiep');
                    $category = $categoriesBySlug->get($catSlug) ?? $categoriesBySlug->first();

                    // 1. Chuẩn hóa & copy ảnh bìa
                    $rawCover = ltrim(str_replace('\\', '/', (string) ($data['cover_image'] ?? '')), '/');
                    $coverImageUrl = '/assets/client/images/hero-default.webp';

                    if (preg_match('#^imgs/(main|extra)/(.+)$#i', $rawCover, $m)) {
                        $subFolder = strtolower($m[1]);
                        $fileName = $m[2];
                        $relKey = $subFolder.'/'.$fileName;

                        if (! $skipImages && ! isset($copiedImages[$relKey])) {
                            $srcPath = $imagesBaseDir.'/imgs/'.$relKey;
                            $dstPath = $targetImageRoot.'/'.$relKey;
                            if (is_file($srcPath)) {
                                if (! is_file($dstPath) || filesize($dstPath) !== filesize($srcPath)) {
                                    File::copy($srcPath, $dstPath);
                                    $copiedCount++;
                                }
                                $copiedImages[$relKey] = true;
                            }
                        }

                        $coverImageUrl = '/storage/images/client/posts/'.$relKey;
                    }

                    // 2. Chuẩn hóa & copy toàn bộ ảnh inline trong content_html
                    $contentHtml = (string) ($data['content_html'] ?? '');
                    $contentHtml = (string) preg_replace_callback(
                        '#\bsrc=["\']/?imgs/(main|extra)/([^"\']+)["\']#i',
                        function (array $matches) use ($imagesBaseDir, $targetImageRoot, $skipImages, &$copiedImages, &$copiedCount): string {
                            $subFolder = strtolower($matches[1]);
                            $fileName = $matches[2];
                            $relKey = $subFolder.'/'.$fileName;

                            if (! $skipImages && ! isset($copiedImages[$relKey])) {
                                $srcPath = $imagesBaseDir.'/imgs/'.$relKey;
                                $dstPath = $targetImageRoot.'/'.$relKey;
                                if (is_file($srcPath)) {
                                    if (! is_file($dstPath) || filesize($dstPath) !== filesize($srcPath)) {
                                        File::copy($srcPath, $dstPath);
                                        $copiedCount++;
                                    }
                                    $copiedImages[$relKey] = true;
                                }
                            }

                            return 'src="/storage/images/client/posts/'.$relKey.'"';
                        },
                        $contentHtml
                    );

                    $readingTime = BlogService::calculateReadingTime($contentHtml);
                    $tags = $categoryTagMap[$catSlug] ?? ['Cẩm Nang Cưới', 'Thiệp Cưới Online'];
                    $metaKeywords = implode(', ', array_merge([$data['title']], $tags, ['Mây Hẹn']));
                    $publishedAt = $now->copy()->subHours(max(1, $queueOrder * 2));

                    BlogPost::updateOrCreate(
                        ['slug' => (string) $data['slug']],
                        [
                            'user_id' => $adminUserId,
                            'category_id' => $category?->id,
                            'title' => (string) $data['title'],
                            'summary' => (string) ($data['summary'] ?? ''),
                            'content' => $contentHtml,
                            'cover_image_url' => $coverImageUrl,
                            'author_name' => 'Ban Biên Tập Mây Hẹn',
                            'tags' => $tags,
                            'status' => 'published',
                            'published_at' => $publishedAt,
                            'reading_time_minutes' => $readingTime,
                            'is_featured' => in_array($queueOrder, $featuredOrders, true),
                            'meta_title' => (string) ($data['meta_title'] ?? $data['title']),
                            'meta_description' => (string) ($data['meta_description'] ?? ($data['summary'] ?? '')),
                            'meta_keywords' => mb_substr($metaKeywords, 0, 250),
                        ]
                    );

                    $importedCount++;
                }
            });

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Lỗi khi nhập bài viết: '.$e->getMessage());

            return self::FAILURE;
        }

        // 3. Xóa sạch cache & RAM memoization để website và Sitemap nhận ngay dữ liệu mới
        BlogService::clearCache();
        BlogController::clearMemoization();
        SitemapController::clearCache();

        $totalPublished = BlogPost::published()->count();
        $this->info(sprintf(
            'Hoàn tất! Đã nhập/cập nhật %d bài viết, sao chép %d file ảnh mới (%d ảnh duy nhất). Tổng số bài đang xuất bản công khai: %d.',
            $importedCount,
            $copiedCount,
            count($copiedImages),
            $totalPublished
        ));

        return $this->verifyPublishedDatabasePosts();
    }

    /**
     * Kiểm định toàn diện bài viết đã xuất bản trong CSDL: chuyên mục, ảnh bìa, ảnh nội dung và internal link.
     */
    protected function verifyPublishedDatabasePosts(): int
    {
        $posts = BlogPost::published()->with('category')->get();
        $slugSet = $posts->pluck('slug')->flip();

        $missingCovers = 0;
        $totalInlineImages = 0;
        $missingInlineImages = 0;
        $brokenBlogLinks = 0;

        foreach ($posts as $post) {
            $coverRel = ltrim((string) $post->cover_image_url, '/');
            if ($coverRel === '' || ! is_file(public_path($coverRel))) {
                $missingCovers++;
                $this->warn("Thiếu ảnh bìa [{$post->slug}]: {$post->cover_image_url}");
            }

            if (preg_match_all('#\bsrc=["\']([^"\']+)["\']#i', (string) $post->content, $imgMatches)) {
                foreach ($imgMatches[1] as $src) {
                    $totalInlineImages++;
                    $imgRel = ltrim($src, '/');
                    if (! is_file(public_path($imgRel))) {
                        $missingInlineImages++;
                        $this->warn("Thiếu ảnh nội dung [{$post->slug}]: {$src}");
                    }
                }
            }

            if (preg_match_all('#\bhref=["\']/cam-nang-cuoi/([a-z0-9-]+)["\']#i', (string) $post->content, $linkMatches)) {
                foreach ($linkMatches[1] as $targetSlug) {
                    if (! $slugSet->has($targetSlug)) {
                        $brokenBlogLinks++;
                        $this->warn("Internal link blog chưa tồn tại [{$post->slug}] -> /cam-nang-cuoi/{$targetSlug}");
                    }
                }
            }
        }

        $categories = BlogCategory::active()->withCount('publishedPosts')->get();
        foreach ($categories as $cat) {
            $this->line(sprintf(' - Chuyên mục [%s] (%s): %d bài xuất bản', $cat->name, $cat->slug, $cat->published_posts_count));
        }

        $this->info(sprintf(
            'Kiểm định CSDL: %d bài published | Ảnh bìa lỗi: %d | Ảnh nội dung: %d (lỗi: %d) | Link blog lỗi: %d',
            $posts->count(),
            $missingCovers,
            $totalInlineImages,
            $missingInlineImages,
            $brokenBlogLinks
        ));

        return ($missingCovers === 0 && $missingInlineImages === 0 && $brokenBlogLinks === 0)
            ? self::SUCCESS
            : self::FAILURE;
    }
}
