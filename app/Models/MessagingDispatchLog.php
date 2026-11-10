<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessagingDispatchLog extends Model
{
    use HasFactory;

    protected $table = 'messaging_dispatch_logs';

    protected $fillable = [
        'dispatch_id',
        'guest_id',
        'phone',
        'guest_name',
        'guest_group',
        'rendered_message',
        'status',
        'error_message',
        'provider_ref',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(MessagingDispatch::class, 'dispatch_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'dry_run' => 'Kiểm Thử (Dry-Run)',
            'sent' => 'Đã Gửi Thành Công',
            'delivered' => 'Đã Nhận Tin',
            'failed' => 'Gửi Thất Bại',
            'skipped_duplicate' => 'Đã Bỏ Qua (Chống Trùng Số)',
            default => $this->status,
        };
    }
}

