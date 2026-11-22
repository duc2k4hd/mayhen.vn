<?php

namespace App\Services;

use App\Mail\ProjectNewsSummaryMail;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class NewsService
{
    /**
     * Bộ nhớ đệm Static RAM cho tin tức nổi bật trong worker.
     *
     * @var Collection<int, NewsPost>|null
     */
    protected static ?Collection $memoizedFeaturedNews = null;

    /**
     * Xóa sạch RAM memoization & Cache khi tin tức thay đổi.
     */
    public static function clearCache(): void
    {
        self::$memoizedFeaturedNews = null;
        Cache::forget('mayhen_news_featured_posts');
        Cache::forget('mayhen_news_category_counts');
    }

    /**
     * Lấy danh sách nhãn chuyên mục kèm số bài đã xuất bản.
     *
     * @return array<string, int>
     */
    public static function getCategoryCounts(): array
    {
        $counts = [];
        foreach (NewsPost::CATEGORY_LABELS as $label) {
            $counts[$label] = 0;
        }

        $rawCounts = NewsPost::published()
            ->selectRaw('category_label, COUNT(*) as total')
            ->groupBy('category_label')
            ->pluck('total', 'category_label')
            ->all();

        foreach ($rawCounts as $label => $total) {
            $counts[(string) $label] = (int) $total;
        }

        return $counts;
    }

    /**
     * Lấy danh sách bài tin tức đã xuất bản kèm lọc chuyên mục, tìm kiếm và phân trang.
     */
    public static function getPublishedNews(int $perPage = 9, ?string $categoryLabel = null, ?string $search = null, int $page = 1): LengthAwarePaginator
    {
        $query = NewsPost::query()
            ->published()
            ->latest('published_at');

        if (! empty($categoryLabel)) {
            $query->where('category_label', $categoryLabel);
        }

        if (! empty($search)) {
            $search = trim($search);
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
    }

    /**
     * Lấy các bài tin tức nổi bật (Featured).
     *
     * @return Collection<int, NewsPost>
     */
    public static function getFeaturedNews(int $limit = 3): Collection
    {
        if (self::$memoizedFeaturedNews !== null) {
            return self::$memoizedFeaturedNews->take($limit);
        }

        self::$memoizedFeaturedNews = NewsPost::query()
            ->published()
            ->featured()
            ->latest('published_at')
            ->take($limit)
            ->get();

        return self::$memoizedFeaturedNews->take($limit);
    }

    /**
     * Lấy các tin tức liên quan hoặc mới nhất.
     *
     * @return Collection<int, NewsPost>
     */
    public static function getRelatedNews(NewsPost $news, int $limit = 3): Collection
    {
        return NewsPost::query()
            ->published()
            ->where('id', '!=', $news->id)
            ->latest('published_at')
            ->take($limit)
            ->get();
    }

    /**
     * Tăng lượt xem bài tin tức an toàn theo phiên.
     */
    public static function recordView(NewsPost $news): void
    {
        $sessionKey = 'viewed_news_post_'.$news->id;
        if (! session()->has($sessionKey)) {
            $news->increment('view_count');
            session()->put($sessionKey, true);
        }
    }

    /**
     * Đưa email tóm tắt bài viết tin tức vào hàng đợi (Queue) gửi tới toàn bộ User đã đăng ký trong hệ thống Mây Hẹn.
     * Trả về tổng số lượng email thành viên đã được đưa vào hàng đợi.
     */
    public static function queueSummaryBroadcastToAllUsers(NewsPost $news): int
    {
        $queuedCount = 0;

        User::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function ($q): void {
                $q->where('is_locked', false)->orWhereNull('is_locked');
            })
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($news, &$queuedCount): void {
                foreach ($users as $user) {
                    if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }

                    Mail::to($user->email)->queue(new ProjectNewsSummaryMail($news, $user));
                    $queuedCount++;
                }
            });

        $news->forceFill([
            'email_broadcasted_at' => now(),
            'email_recipients_count' => $queuedCount,
        ])->saveQuietly();

        return $queuedCount;
    }
}
