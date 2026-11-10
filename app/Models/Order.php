<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'provider_transaction_id',
        'user_id',
        'plan_id',
        'invitation_id',
        'amount',
        'status',
        'reconciliation_status',
        'payment_method',
        'paid_at',
        'refunded_at',
        'refund_amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'refund_amount' => 'integer',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_code)) {
                $order->order_code = 'MH'.strtoupper(Str::random(10));
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
}
