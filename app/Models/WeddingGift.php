<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingGift extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'guest_id',
        'sender_name',
        'sender_group',
        'amount',
        'payment_method',
        'notes',
        'is_acknowledged',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'is_acknowledged' => 'boolean',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'bank_transfer' => 'Chuyển khoản VietQR',
            'other' => 'Quà mừng hiện vật',
            default => 'Tiền mặt / Phong bì',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        if ($this->payment_method === 'other' && $this->amount == 0) {
            return 'Hiện vật';
        }

        return number_format($this->amount, 0, ',', '.').' đ';
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('is_acknowledged', true);
    }

    public function scopePendingAcknowledgement($query)
    {
        return $query->where('is_acknowledged', false);
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }
}
