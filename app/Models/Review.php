<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wedding_id',
        'invitation_id',
        'template_id',
        'couple_name',
        'wedding_date',
        'rating',
        'title',
        'content',
        'photo_url',
        'status',
        'is_featured',
        'display_order',
        'verified_purchase',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'wedding_date' => 'date',
            'rating' => 'integer',
            'is_featured' => 'boolean',
            'verified_purchase' => 'boolean',
            'reviewed_at' => 'datetime',
            'display_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InvitationTemplate::class, 'template_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope lọc các đánh giá đã được duyệt xuất bản.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope lọc các đánh giá ghim nổi bật trên trang chủ.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('status', 'approved')->where('is_featured', true)->orderBy('display_order')->latest('reviewed_at');
    }

    /**
     * Scope lọc các đánh giá đang chờ duyệt.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Nhãn trạng thái tiếng Việt thân thiện.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'Đã Duyệt (Hiển Thị)',
            'rejected' => 'Đã Ẩn / Từ Chối',
            default => 'Chờ Kiểm Duyệt',
        };
    }

    /**
     * Style CSS màu sắc trạng thái.
     */
    public function getStatusBadgeStyle(): array
    {
        return match ($this->status) {
            'approved' => ['bg' => '#E8F5E9', 'color' => '#1B5E20', 'border' => '#A5D6A7'],
            'rejected' => ['bg' => '#FDEDEC', 'color' => '#922B21', 'border' => '#F5B7B1'],
            default => ['bg' => '#FFF8E1', 'color' => '#F57F17', 'border' => '#FFE082'],
        };
    }

    /**
     * Tên mẫu thiệp cưới hiển thị.
     */
    public function getTemplateNameAttribute(): string
    {
        return $this->template?->name ?? 'Mẫu Thiệp Mây Hẹn Atelier';
    }
}
