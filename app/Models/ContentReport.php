<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentReport extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_HIDDEN = 'hidden';

    public const STATUS_DELETED = 'deleted';

    public const STATUS_DISMISSED = 'dismissed';

    public const REASON_SPAM = 'spam';

    public const REASON_HARASSMENT = 'harassment';

    public const REASON_INAPPROPRIATE = 'inappropriate';

    public const REASON_PRIVACY = 'privacy_violation';

    public const REASON_OTHER = 'other';

    protected $fillable = [
        'reportable_type',
        'reportable_id',
        'wedding_id',
        'reporter_name',
        'reporter_phone',
        'reporter_ip',
        'reason',
        'details',
        'status',
        'action_taken',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', '!=', self::STATUS_PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            self::REASON_SPAM => 'Quảng cáo rác / Lừa đảo',
            self::REASON_HARASSMENT => 'Quấy rối / Xúc phạm',
            self::REASON_INAPPROPRIATE => 'Nội dung thô tục / Khiêu dâm',
            self::REASON_PRIVACY => 'Vi phạm quyền riêng tư',
            default => 'Lý do khác',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_HIDDEN => 'Đã ẩn nội dung',
            self::STATUS_DELETED => 'Đã xóa vĩnh viễn',
            self::STATUS_DISMISSED => 'Đã bác bỏ (An toàn)',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'mayhen_admin_badge_warning',
            self::STATUS_HIDDEN => 'mayhen_admin_badge_secondary',
            self::STATUS_DELETED => 'mayhen_admin_badge_danger',
            self::STATUS_DISMISSED => 'mayhen_admin_badge_success',
            default => '',
        };
    }
}
