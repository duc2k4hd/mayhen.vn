<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessagingDispatch extends Model
{
    use HasFactory;

    protected $table = 'messaging_dispatches';

    protected $fillable = [
        'wedding_id',
        'campaign_name',
        'channel',
        'status',
        'filter_group',
        'total_recipients',
        'deduplicated_count',
        'successful_count',
        'failed_count',
        'unit_cost_vnd',
        'total_cost_vnd',
        'message_template',
        'created_by',
        'approved_by',
        'scheduled_at',
        'dispatched_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_recipients' => 'integer',
            'deduplicated_count' => 'integer',
            'successful_count' => 'integer',
            'failed_count' => 'integer',
            'unit_cost_vnd' => 'decimal:2',
            'total_cost_vnd' => 'decimal:2',
            'scheduled_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MessagingDispatchLog::class, 'dispatch_id');
    }

    public function getChannelLabelAttribute(): string
    {
        return match ($this->channel) {
            'zalo_zns' => 'Zalo ZNS Thông Báo',
            'sms_brandname' => 'SMS Brandname',
            'invitation_link' => 'Link Thiệp Định Danh',
            default => 'Mặc Định',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Bản Nháp',
            'dry_run_ready' => 'Đã Kiểm Thử Dry-Run (Sẵn Sàng Duyệt)',
            'processing' => 'Đang Phát Tin',
            'completed' => 'Đã Hoàn Tất',
            'failed' => 'Gặp Sự Cố',
            'cancelled' => 'Đã Hủy',
            default => $this->status,
        };
    }
}
