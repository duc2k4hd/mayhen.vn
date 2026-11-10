<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFeedback extends Model
{
    use HasFactory;

    protected $table = 'customer_feedbacks';

    protected $fillable = [
        'user_id',
        'wedding_id',
        'milestone',
        'rating',
        'sentiment',
        'category',
        'comment',
        'linked_ticket_id',
        'action_status',
        'action_plan_note',
        'handled_by',
        'handled_at',
        'metadata',
    ];

    protected $casts = [
        'rating' => 'integer',
        'handled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'linked_ticket_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isLowRating(): bool
    {
        return $this->rating <= 2;
    }

    public function requiresAction(): bool
    {
        return $this->isLowRating() && $this->action_status === 'pending_review';
    }

    public function getMilestoneLabelAttribute(): string
    {
        return match ($this->milestone) {
            'after_publish' => 'Sau Xuất Bản Thiệp',
            'after_payment' => 'Sau Thanh Toán Gói',
            'post_wedding' => 'Sau Ngày Cưới (3 Ngày)',
            default => 'Trải Nghiệm Chung',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'studio_editor' => 'Trình Tạo Thiệp Studio',
            'pricing' => 'Gói Cước & Thanh Toán',
            'customer_service' => 'Chăm Sóc & Hỗ Trợ',
            'guest_rsvp' => 'Khách Mời & RSVP',
            'template_design' => 'Thiết Kế Mẫu Thiệp',
            default => 'Ý Kiến Khác',
        };
    }

    public function getSentimentBadgeClass(): string
    {
        return match ($this->sentiment) {
            'delighted' => 'mayhen_admin_badge_success',
            'satisfied' => 'mayhen_admin_badge_info',
            'neutral' => 'mayhen_admin_badge_warning',
            'frustrated' => 'mayhen_admin_badge_danger',
            default => 'mayhen_admin_badge_secondary',
        };
    }

    public function scopeLowRating(Builder $query): Builder
    {
        return $query->where('rating', '<=', 2);
    }

    public function scopeMilestone(Builder $query, string $milestone): Builder
    {
        return $query->where('milestone', $milestone);
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('action_status', 'pending_review');
    }
}
