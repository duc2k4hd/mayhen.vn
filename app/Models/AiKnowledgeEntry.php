<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class AiKnowledgeEntry extends Model
{
    use HasFactory;

    public const SOURCE_ADMIN = 'admin';

    public const SOURCE_CUSTOMER = 'customer';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    public const CATEGORIES = [
        'templates' => 'Mẫu Thiệp Cưới',
        'docs' => 'Hướng Dẫn Sử Dụng (Docs)',
        'pricing' => 'Bảng Giá & Gói Dịch Vụ',
        'rsvp' => 'Khách Mời, RSVP & Điểm Danh QR',
        'general' => 'Thông Tin Chung & Hỗ Trợ',
    ];

    protected $fillable = [
        'category',
        'title',
        'keywords',
        'answer',
        'template_slug',
        'doc_url',
        'source',
        'status',
        'is_active',
        'priority',
        'security_flags',
        'submitted_by_user_id',
        'submitted_session_hash',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'integer',
            'security_flags' => 'array',
            'approved_at' => 'datetime',
            'usage_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::clearKnowledgeCache());
        static::deleted(fn () => self::clearKnowledgeCache());
    }

    public static function clearKnowledgeCache(): void
    {
        Cache::forget('mayhen_ai_approved_knowledge_entries');
        Cache::forget('mayhen_ai_approved_knowledge_entries_v2');
    }

    /**
     * Chỉ truy xuất các mục tri thức đã được Admin phê duyệt và đang bật.
     *
     * @param  Builder<AiKnowledgeEntry>  $query
     * @return Builder<AiKnowledgeEntry>
     */
    public function scopeLiveApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->where('is_active', true);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'Đã duyệt (Đang chạy)',
            self::STATUS_PENDING => 'Chờ Admin duyệt',
            self::STATUS_REJECTED => 'Đã từ chối',
            default => $this->status,
        };
    }
}
