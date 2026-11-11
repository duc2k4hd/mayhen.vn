<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'creator_id',
        'approver_id',
        'action_type',
        'status',
        'amount',
        'reference_code',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'reconciliation_note',
        'review_note',
        'reconciled_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'reconciled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved' || $this->status === 'completed';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['approved', 'completed']);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRefunds($query)
    {
        return $query->whereIn('action_type', ['refund_request', 'refund_payout']);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount).' đ';
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action_type) {
            'manual_approval' => 'Duyệt Khớp Lệnh Thủ Công (PAY-01)',
            'amount_mismatch_resolution' => 'Xử Lý Lệch Tiền (PAY-03)',
            'refund_request' => 'Yêu Cầu Hoàn Tiền',
            'refund_payout' => 'Chi Trả Hoàn Tiền',
            default => $this->action_type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ Kế Toán Duyệt',
            'approved' => 'Đã Phê Duyệt',
            'rejected' => 'Bị Từ Chối',
            'completed' => 'Đã Hoàn Tất Chi Trả',
            default => $this->status,
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'approved', 'completed' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'info',
        };
    }
}
