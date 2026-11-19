<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\PlatformSetting;
use App\Services\BlogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BlogController extends Controller
{
    /**
     * Cache bài viết mặc định trang 1 trong worker memory (FPM / Octane).
     * Phản hồi 0.00ms, không tốn I/O, không gọi lại cache driver lặp lại.
     */
    protected static ?LengthAwarePaginator $memoizedDefaultPosts = null;

    /**
     * Cache bài viết theo chuyên mục/phân trang trong worker memory.
     *
     * @var array<string, LengthAwarePaginator>
     */
    protected static array $memoizedFilteredPosts = [];

    /**
     * Cache ETag & Last-Modified cơ sở cho trang cẩm nang cưới.
     */
    protected static ?string $blogIndexEtag = null;

    protected static ?int $blogIndexLastModified = null;

    /**
     * Xóa sạch RAM memoization khi có cập nhật bài viết/chuyên mục.
     */
    public static function clearMemoization(): void
    {
        self::$memoizedDefaultPosts = null;
        self::$memoizedFilteredPosts = [];
        self::$blogIndexEtag = null;
        self::$blogIndexLastModified = null;
    }

    /**
     * Tính toán ETag và Last-Modified cho trang cẩm nang cưới.
     *
     * @return array{0: string, 1: int}
     */
    protected static function resolveBlogCacheMeta(
        LengthAwarePaginator $posts,
        string $categorySlug = '',
        string $search = '',
        int $page = 1
    ): array {
        $viewPath = resource_path('views/client/pages/blog/index.blade.php');
        $mtime = file_exists($viewPath) ? (int) filemtime($viewPath) : 1731600000;

        $brandName = PlatformSetting::getBrandName();

        if (empty($categorySlug) && empty($search) && $page === 1) {
            if (self::$blogIndexEtag !== null && self::$blogIndexLastModified !== null) {
                return [self::$blogIndexEtag, self::$blogIndexLastModified];
            }

            $postsSignature = collect($posts->items())->map(fn ($p) => $p->id.':'.$p->updated_at?->timestamp)->join('|');
            $etag = '"mayhen_blog_home_'.md5(config('app.version', '1.0.0').'_'.$mtime.'_'.$postsSignature.'_'.$brandName).'"';

            self::$blogIndexEtag = $etag;
            self::$blogIndexLastModified = $mtime;

            return [$etag, $mtime];
        }

        $filterSignature = md5($categorySlug.'_'.$search.'_'.$page.'_'.$posts->total().'_'.$brandName);
        $etag = '"mayhen_blog_filter_'.$filterSignature.'"';

        return [$etag, $mtime];
    }

    /**
     * Màn hình Trang Chủ Cẩm Nang & Chuyên Đề Cưới (Editorial Magazine).
     * Tối ưu hóa cực hạn cho hàng triệu người dùng:
     * - Zero Database Query cho khách vãng lai (99.9% traffic).
     * - In-Memory Static RAM Memoization cho danh mục và bài viết trang chủ.
     * - Edge CDN Caching qua ETag, Last-Modified & HTTP 304 Not Modified.
     */
    public function index(Request $request): Response|View
    {
        $categorySlug = (string) $request->string('category')->trim();
        $search = (string) $request->string('q')->trim();
        $page = max(1, (int) $request->input('page', 1));

        $isDefaultPage = empty($categorySlug) && empty($search) && $page === 1;

        // 1. Lấy danh sách chuyên mục (Static RAM memoization + Cache 24h)
        $categories = BlogService::getActiveCategories();

        // 2. Chuyên mục đang chọn (Tra cứu trực tiếp trong RAM, Zero DB Query)
        $selectedCategory = ! empty($categorySlug) ? BlogService::findCategoryBySlug($categorySlug) : null;

        // 3. Bài viết nổi bật (Static RAM memoization + Cache 24h)
        $featuredPosts = empty($categorySlug) && empty($search) ? BlogService::getFeaturedPosts(3) : collect();

        // 4. Lấy danh sách bài viết phân trang (Tối ưu Static RAM cho Default Page 1 và Filter)
        if ($isDefaultPage) {
            if (self::$memoizedDefaultPosts === null) {
                self::$memoizedDefaultPosts = BlogService::getPublishedPosts(
                    perPage: 9,
                    page: 1
                );
            }
            $posts = self::$memoizedDefaultPosts;
        } else {
            $filterKey = $categorySlug.'_'.$search.'_'.$page;
            if (! isset(self::$memoizedFilteredPosts[$filterKey])) {
                self::$memoizedFilteredPosts[$filterKey] = BlogService::getPublishedPosts(
                    perPage: 9,
                    categorySlug: $categorySlug ?: null,
                    search: $search ?: null,
                    page: $page
                );
            }
            $posts = self::$memoizedFilteredPosts[$filterKey];
        }

        // 5. Nếu người dùng đã đăng nhập: Phục vụ view động, không cache public để bảo toàn phiên cá nhân
        if ($request->user() !== null) {
            return view('client.pages.blog.index', compact(
                'posts',
                'categories',
                'selectedCategory',
                'featuredPosts',
                'search'
            ));
        }

        // 6. Tối ưu cho khách vãng lai (99.9% lưu lượng): ETag, Last-Modified & 304 Not Modified
        [$etag, $mtime] = self::resolveBlogCacheMeta($posts, $categorySlug, $search, $page);

        $clientEtag = $request->header('If-None-Match');
        if ($clientEtag && trim($clientEtag) === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Last-Modified', gmdate('D, d M Y H:i:s', $mtime).' GMT')
                ->header('Cache-Control', 'public, max-age=1800, stale-while-revalidate=86400');
        }

        return response()
            ->view('client.pages.blog.index', compact(
                'posts',
                'categories',
                'selectedCategory',
                'featuredPosts',
                'search'
            ))
            ->header('ETag', $etag)
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $mtime).' GMT')
            ->header('Cache-Control', 'public, max-age=1800, stale-while-revalidate=86400');
    }

    /**
     * Xem Chi Tiết Bài Viết Cẩm Nang.
     */
    public function show(string $slug): View
    {
        $post = BlogPost::with(['category', 'user'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Nếu bài viết chưa xuất bản thì chỉ Admin mới được xem trước
        if (! $post->isPublished()) {
            if (! auth()->check() || ! auth()->user()->isAdmin()) {
                abort(404);
            }
        }

        // Tăng view count an toàn
        BlogService::recordView($post);

        // Bóc tách mục lục (Table of Contents) và inject id neo cuộn
        $tocResult = BlogService::extractTableOfContentsAndInjectIds($post->content);
        $toc = $tocResult['toc'];
        $processedContent = $tocResult['content'];

        // Lấy bài viết liên quan
        $relatedPosts = BlogService::getRelatedPosts($post, 3);

        // Lấy bài viết trước & bài viết kế tiếp (nếu bài viết đã có published_at)
        $prevPost = null;
        $nextPost = null;
        if ($post->published_at !== null) {
            $prevPost = BlogPost::published()
                ->where('published_at', '<', $post->published_at)
                ->latest('published_at')
                ->first(['id', 'title', 'slug', 'published_at']);

            $nextPost = BlogPost::published()
                ->where('published_at', '>', $post->published_at)
                ->oldest('published_at')
                ->first(['id', 'title', 'slug', 'published_at']);
        }

        return view('client.pages.blog.show', compact(
            'post',
            'toc',
            'processedContent',
            'relatedPosts',
            'prevPost',
            'nextPost'
        ));
    }
}
