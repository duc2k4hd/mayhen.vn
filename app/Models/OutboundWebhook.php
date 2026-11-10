<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OutboundWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'name',
        'payload_url',
        'secret',
        'events',
        'is_active',
        'failure_count',
        'last_dispatched_at',
        'last_status_code',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
            'failure_count' => 'integer',
            'last_dispatched_at' => 'datetime',
            'last_status_code' => 'integer',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(OutboundWebhookDelivery::class, 'outbound_webhook_id')->latest('id');
    }

    /**
     * Sinh khóa bí mật ngẫu nhiên cho chữ ký HMAC-SHA256.
     */
    public static function generateSecret(): string
    {
        return 'whsec_'.Str::random(32);
    }

    /**
     * Kiểm tra trạng thái Circuit Breaker (ngắt mạch nếu thất bại liên tiếp >= 10 lần).
     */
    public function isCircuitBroken(): bool
    {
        return $this->failure_count >= 10;
    }

    /**
     * Kiểm tra xem webhook có đăng ký lắng nghe sự kiện cụ thể này không.
     */
    public function shouldDeliver(string $eventType): bool
    {
        if (! $this->is_active || $this->isCircuitBroken()) {
            return false;
        }

        $registeredEvents = $this->events ?? [];

        return in_array($eventType, $registeredEvents, true) || in_array('*', $registeredEvents, true);
    }
}
