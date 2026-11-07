<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'order_id',
        'wedding_id',
        'amount',
        'type',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->type === 'payout' ? '-' : '+';

        return $sign.number_format((float) $this->amount, 0, ',', '.').' ₫';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'Đã Duyệt',
            'paid' => 'Đã Chi Trả',
            default => 'Chờ Xử Lý',
        };
    }
}
