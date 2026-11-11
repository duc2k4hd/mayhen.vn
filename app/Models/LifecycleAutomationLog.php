<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifecycleAutomationLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'lifecycle_automation_logs';

    protected $fillable = [
        'rule_id',
        'wedding_id',
        'user_id',
        'channel',
        'recipient',
        'status',
        'message_summary',
        'dispatched_at',
        'error_message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dispatched_at' => 'datetime',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(LifecycleAutomationRule::class, 'rule_id');
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class, 'wedding_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'dispatched' => 'Đã Phát Tin',
            'simulated' => 'Mô Phỏng (Dry-Run)',
            'skipped_duplicate' => 'Bỏ Qua (Đã Gửi)',
            'failed' => 'Lỗi Chuyển Phát',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'dispatched' => 'mayhen_admin_badge--success',
            'simulated' => 'mayhen_admin_badge--warning',
            'skipped_duplicate' => 'mayhen_admin_badge--neutral',
            'failed' => 'mayhen_admin_badge--danger',
            default => 'mayhen_admin_badge--neutral',
        };
    }
}

