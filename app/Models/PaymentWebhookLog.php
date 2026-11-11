<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhookLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'gateway',
        'transaction_id',
        'order_code',
        'amount',
        'status',
        'error_reason',
        'raw_payload',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'raw_payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    public function scopeSuccess(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending_review', 'mismatch', 'duplicate']);
    }

    public function scopeMismatch(Builder $query): Builder
    {
        return $query->where('status', 'mismatch');
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount).' đ';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'success' => 'Hợp Lệ (Đã Khớp)',
            'mismatch' => 'Lệch Số Tiền (PAY-03)',
            'duplicate' => 'Gửi Trùng Lặp (Idempotent)',
            'unverified' => 'Sai Chữ Ký / Không Hợp Lệ',
            'pending_review' => 'Chờ Kế Toán Đối Soát',
            default => $this->status,
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'success' => 'success',
            'mismatch', 'unverified' => 'danger',
            'duplicate' => 'warning',
            default => 'info',
        };
    }
}
