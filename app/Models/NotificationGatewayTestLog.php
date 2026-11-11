<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationGatewayTestLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'notification_gateway_test_logs';

    protected $fillable = [
        'channel',
        'provider',
        'recipient',
        'template_code',
        'status',
        'latency_ms',
        'response_code',
        'response_payload',
        'error_message',
        'dispatched_by',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latency_ms' => 'integer',
            'response_payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('id');
    }

    public function scopeForChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    public function getChannelLabelAttribute(): string
    {
        return match ($this->channel) {
            'zalo_zns' => 'Zalo ZNS',
            'sms_brandname' => 'SMS Brandname',
            'email_smtp' => 'Email SMTP',
            default => strtoupper($this->channel),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'success' => 'Thành Công',
            'simulated' => 'Mô Phỏng (Sandbox)',
            'failed' => 'Thất Bại',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'success' => 'mayhen_admin_badge--success',
            'simulated' => 'mayhen_admin_badge--warning',
            'failed' => 'mayhen_admin_badge--danger',
            default => 'mayhen_admin_badge--neutral',
        };
    }
}
