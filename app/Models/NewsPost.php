<?php

namespace App\Models;

use App\Http\Controllers\Client\NewsController;
use App\Http\Controllers\Client\SitemapController;
use App\Services\NewsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NewsPost extends Model
{
    use HasFactory;

    /**
     * Danh sách nhãn chuyên mục tin tức chuẩn của Mây Hẹn.
     *
     * @var array<string, string>
     */
    public const CATEGORY_LABELS = [
        'Cập Nhật Hệ Thống' => 'Cập Nhật Hệ Thống',
        'Tính Năng Mới' => 'Tính Năng Mới',
        'Mẫu Thiệp Mới' => 'Mẫu Thiệp Mới',
        'Ưu Đãi & Sự Kiện' => 'Ưu Đãi & Sự Kiện',
        'Thông Báo Vận Hành' => 'Thông Báo Vận Hành',
    ];

    /**
     * Chuyển nhãn chuyên mục tin tức sang slug URL không dấu chuẩn SEO (ví dụ: mau-thiep-moi).
     */
    public static function categorySlug(?string $label): string
    {
        return Str::slug((string) $label);
    }

    /**
     * Giải mã tham số slug (hoặc nhãn cũ) thành nhãn category_label thực tế lưu trong CSDL.
     */
    public static function resolveCategoryLabelFromSlug(?string $slugOrLabel): ?string
    {
        $value = trim((string) $slugOrLabel);
        if ($value === '') {
            return null;
        }

        if (isset(self::CATEGORY_LABELS[$value])) {
            return self::CATEGORY_LABELS[$value];
        }

        $normalized = Str::slug($value);
        foreach (self::CATEGORY_LABELS as $label) {
            if (self::categorySlug($label) === $normalized) {
                return $label;
            }
        }

        $distinctLabels = static::published()
            ->whereNotNull('category_label')
            ->distinct()
            ->pluck('category_label');

        foreach ($distinctLabels as $label) {
            if (self::categorySlug((string) $label) === $normalized || (string) $label === $value) {
                return (string) $label;
            }
        }

        return $value;
    }

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'category_label',
        'summary',
        'content',
        'cover_image_url',
        'author_name',
        'tags',
        'status',
        'published_at',
        'reading_time_minutes',
        'view_count',
        'is_featured',
        'send_email_on_publish',
        'email_broadcasted_at',
        'email_recipients_count',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'published_at' => 'datetime',
            'reading_time_minutes' => 'integer',
            'view_count' => 'integer',
            'is_featured' => 'boolean',
            'send_email_on_publish' => 'boolean',
            'email_broadcasted_at' => 'datetime',
            'email_recipients_count' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $post): void {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            if ($post->status === 'published' && empty($post->published_at)) {
                $post->published_at = now();
            }
        });
    }

    protected static function booted(): void
    {
        static::saved(function (): void {
            NewsService::clearCache();
            NewsController::clearMemoization();
            SitemapController::clearCache();
        });

        static::deleted(function (): void {
            NewsService::clearCache();
            NewsController::clearMemoization();
            SitemapController::clearCache();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null && $this->published_at->isPast();
    }

    public function getFormattedPublishedDateAttribute(): string
    {
        if (! $this->published_at) {
            return 'Bản nháp';
        }

        return Carbon::parse($this->published_at)->locale('vi')->isoFormat('D MMMM, YYYY');
    }
}
