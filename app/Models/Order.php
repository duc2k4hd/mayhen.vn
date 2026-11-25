<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'checkout_token',
        'provider_transaction_id',
        'user_id',
        'plan_id',
        'invitation_id',
        'amount',
        'status',
        'reconciliation_status',
        'payment_method',
        'period_months',
        'paid_at',
        'checkout_expires_at',
        'last_sepay_check_at',
        'last_sepay_check_message',
        'starts_at',
        'expires_at',
        'is_renewal',
        'refunded_at',
        'refund_amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'refund_amount' => 'integer',
            'period_months' => 'integer',
            'is_renewal' => 'boolean',
            'paid_at' => 'datetime',
            'checkout_expires_at' => 'datetime',
            'last_sepay_check_at' => 'datetime',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_code)) {
                $order->order_code = 'MH'.strtoupper(Str::random(10));
            }
            if (empty($order->checkout_token)) {
                $order->checkout_token = bin2hex(random_bytes(32));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(OrderReconciliation::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function isRefundPending(): bool
    {
        return $this->reconciliation_status === 'refund_pending';
    }

    public function isReconciliationPending(): bool
    {
        return $this->reconciliation_status === 'pending_approval';
    }

    public function isCancelled(): bool
    {
        return in_array($this->status, ['cancelled', 'failed'], true);
    }

    public function canBeVerifiedWithSepay(): bool
    {
        return in_array($this->status, ['pending', 'failed', 'cancelled'], true);
    }

    public function hasValidCheckoutToken(?string $token): bool
    {
        if (! is_string($token) || $token === '' || ! is_string($this->checkout_token) || $this->checkout_token === '') {
            return false;
        }

        return hash_equals($this->checkout_token, trim($token));
    }
}
