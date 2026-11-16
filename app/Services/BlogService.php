<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BlogService
{
    /**
     * Bộ nhớ đệm Static RAM trong tiến trình PHP worker (FPM / Octane).
     *
     * @var Collection<int, BlogCategory>|null
     */
    protected static ?Collection $memoizedCategories = null;

    /**
     * @var Collection<int, BlogPost>|null
     */
    protected static ?Collection $memoizedFeaturedPosts = null;

    /**
     * Xóa sạch RAM memoization khi bài viết/chuyên mục thay đổi.
     */
    public static function clearCache(): void
    {
        self::$memoizedCategories = null;
        self::$memoizedFeaturedPosts = null;
        Cache::forget('mayhen_blog_categories_raw');
        Cache::forget('mayhen_blog_categories_with_count');
        Cache::forget('mayhen_blog_featured_raw');
        Cache::forget('mayhen_blog_featured_posts');
    }

    /**
     * Lấy toàn bộ danh mục hoạt động kèm số lượng bài viết xuất bản (Static RAM Memoization trong worker).
     *
     * @return Collection<int, BlogCategory>
     */
    public static function getActiveCategories(): Collection
    {
        if (self::$memoizedCategories !== null) {
            return self::$memoizedCategories;
        }

        self::$memoizedCategories = BlogCategory::active()
            ->withCount('publishedPosts')
            ->get();

        return self::$memoizedCategories;
    }

    /**
     * Tìm danh mục theo slug trực tiếp từ danh mục đã memoize trong RAM mà không cần query lại DB.
     */
    public static function findCategoryBySlug(string $slug): ?BlogCategory
    {
        $categories = self::getActiveCategories();

        return $categories->firstWhere('slug', $slug);
    }

    /**
     * Tính toán thời gian đọc trung bình dựa trên tổng số từ (200 từ/phút).
     */
    public static function calculateReadingTime(string $content): int
    {
        $plainText = strip_tags($content);
        $wordCount = str_word_count($plainText);
        $minutes = (int) ceil($wordCount / 200);

        return max(1, $minutes);
    }

    /**
     * Bóc tách mục lục (Table of Contents) từ thẻ <h2> và <h3> trong nội dung bài viết,
     * đồng thời bổ sung thuộc tính id="{slug}" vào thẻ heading để phục vụ neo cuộn mượt mà.
     *
     * @return array{toc: array<int, array{id: string, text: string, level: int}>, content: string}
     */
    public static function extractTableOfContentsAndInjectIds(string $content): array
    {
        $toc = [];
        $index = 0;

        $processedContent = preg_replace_callback('/<h([23])(.*?)>(.*?)<\/h\1>/i', function ($matches) use (&$toc, &$index) {
            $level = (int) $matches[1];
            $attrs = $matches[2];
            $innerHtml = $matches[3];
            $title = strip_tags($innerHtml);

            // Kiểm tra xem đã có id chưa
            if (preg_match('/id=[\'"]([^\'"]+)[\'"]/i', $attrs, $idMatch)) {
                $id = $idMatch[1];
            } else {
                $index++;
                $slug = Str::slug($title);
                $id = ! empty($slug) ? "muc-{$index}-{$slug}" : "muc-{$index}";
                $attrs = ' id="'.$id.'"'.$attrs;
            }

            $toc[] = [
                'id' => $id,
                'text' => $title,
                'level' => $level,
            ];

            return "<h{$level}{$attrs}>{$innerHtml}</h{$level}>";
        }, $content);

        return [
            'toc' => $toc,
            'content' => $processedContent ?? $content,
        ];
    }

    /**
     * Lấy danh sách bài viết đã xuất bản kèm phân trang và tìm kiếm (truy vấn tối ưu, không serialize Paginator vào cache để triệt tiêu lỗi cache).
     */
    public static function getPublishedPosts(int $perPage = 9, ?string $categorySlug = null, ?string $search = null, int $page = 1): LengthAwarePaginator
    {
        $query = BlogPost::with(['category'])
            ->published()
            ->latest('published_at');

        if (! empty($categorySlug)) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if (! empty($search)) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
    }

    /**
     * Lấy các bài viết nổi bật (Featured) - Static RAM Memoization trong worker.
     */
    public static function getFeaturedPosts(int $limit = 3): Collection
    {
        if (self::$memoizedFeaturedPosts !== null) {
            return self::$memoizedFeaturedPosts->take($limit);
        }

        self::$memoizedFeaturedPosts = BlogPost::with(['category', 'user'])
            ->published()
            ->featured()
            ->latest('published_at')
            ->take($limit)
            ->get();

        return self::$memoizedFeaturedPosts->take($limit);
    }

    /**
     * Lấy danh sách bài viết liên quan theo cùng chuyên mục hoặc mới nhất.
     */
    public static function getRelatedPosts(BlogPost $post, int $limit = 3): Collection
    {
        return BlogPost::with(['category'])
            ->published()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, function ($q) use ($post) {
                $q->where('category_id', $post->category_id);
            })
            ->latest('published_at')
            ->take($limit)
            ->get();
    }

    /**
     * Tăng số lượt xem bài viết một cách an toàn.
     */
    public static function recordView(BlogPost $post): void
    {
        $sessionKey = 'viewed_blog_post_'.$post->id;
        if (! session()->has($sessionKey)) {
            $post->increment('view_count');
            session()->put($sessionKey, true);
        }
    }
}
