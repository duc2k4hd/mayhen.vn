<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use App\Services\BlogService;
use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NewsController extends Controller
{
    /**
     * Bộ nhớ đệm HTML trang chủ Tin tức trong RAM worker.
     */
    protected static ?string $memoizedIndexHtml = null;

    protected static ?string $memoizedIndexEtag = null;

    /**
     * Xóa sạch RAM memoization khi tin tức thay đổi.
     */
    public static function clearMemoization(): void
    {
        self::$memoizedIndexHtml = null;
        self::$memoizedIndexEtag = null;
    }

    /**
     * Hiển thị trang danh sách Tin Tức & Cập Nhật Dự Án (/tin-tuc).
     */
    public function index(Request $request): View|Response
    {
        $categoryLabel = $request->query('category');
        $search = $request->query('q');
        $page = max(1, (int) $request->query('page', 1));

        $isDefaultGuestView = empty($categoryLabel)
            && empty($search)
            && $page === 1
            && ! Auth::check();

        if ($isDefaultGuestView && self::$memoizedIndexHtml !== null && self::$memoizedIndexEtag !== null) {
            if ($request->header('If-None-Match') === self::$memoizedIndexEtag) {
                return response('', 304, [
                    'ETag' => self::$memoizedIndexEtag,
                    'Cache-Control' => 'public, max-age=60, stale-while-revalidate=300',
                ]);
            }

            return response(self::$memoizedIndexHtml, 200, [
                'ETag' => self::$memoizedIndexEtag,
                'Cache-Control' => 'public, max-age=60, stale-while-revalidate=300',
            ]);
        }

        $categoryCounts = NewsService::getCategoryCounts();
        $featuredNews = ($page === 1 && empty($categoryLabel) && empty($search))
            ? NewsService::getFeaturedNews(2)
            : collect();
        $posts = NewsService::getPublishedNews(9, $categoryLabel, $search, $page);

        $view = view('client.pages.news.index', compact(
            'categoryCounts',
            'featuredNews',
            'posts',
            'categoryLabel',
            'search'
        ));

        if ($isDefaultGuestView) {
            $html = $view->render();
            $etag = '"mayhen_news_idx_'.md5($html).'"';

            self::$memoizedIndexHtml = $html;
            self::$memoizedIndexEtag = $etag;

            if ($request->header('If-None-Match') === $etag) {
                return response('', 304, [
                    'ETag' => $etag,
                    'Cache-Control' => 'public, max-age=60, stale-while-revalidate=300',
                ]);
            }

            return response($html, 200, [
                'ETag' => $etag,
                'Cache-Control' => 'public, max-age=60, stale-while-revalidate=300',
            ]);
        }

        return $view;
    }

    /**
     * Hiển thị chi tiết bài viết Tin Tức (/tin-tuc/{slug}).
     */
    public function show(string $slug): View
    {
        $post = NewsPost::with(['user'])
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $post->isPublished()) {
            $user = Auth::user();
            if (! $user || ! $user->isAdmin()) {
                abort(404);
            }
        }

        NewsService::recordView($post);

        $tocData = BlogService::extractTableOfContentsAndInjectIds($post->content);
        $toc = $tocData['toc'];
        $processedContent = $tocData['content'];

        $relatedPosts = NewsService::getRelatedNews($post, 3);

        $prevPost = NewsPost::published()
            ->where('id', '!=', $post->id)
            ->where('published_at', '<=', $post->published_at ?? now())
            ->orderByDesc('published_at')
            ->first(['id', 'title', 'slug']);

        $nextPost = NewsPost::published()
            ->where('id', '!=', $post->id)
            ->where('published_at', '>', $post->published_at ?? now())
            ->orderBy('published_at')
            ->first(['id', 'title', 'slug']);

        return view('client.pages.news.show', compact(
            'post',
            'toc',
            'processedContent',
            'relatedPosts',
            'prevPost',
            'nextPost'
        ));
    }
}
