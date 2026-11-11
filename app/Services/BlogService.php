<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class BlogService
{
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
     * Lấy danh sách bài viết đã xuất bản kèm phân trang và tìm kiếm.
     */
    public static function getPublishedPosts(int $perPage = 9, ?string $categorySlug = null, ?string $search = null): LengthAwarePaginator
    {
        $query = BlogPost::with(['category', 'user'])
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

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Lấy các bài viết nổi bật (Featured).
     */
    public static function getFeaturedPosts(int $limit = 3): Collection
    {
        return BlogPost::with(['category', 'user'])
            ->published()
            ->featured()
            ->latest('published_at')
            ->take($limit)
            ->get();
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
