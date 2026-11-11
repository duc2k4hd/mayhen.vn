<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'invoice_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'company_name',
        'tax_id',
        'company_address',
        'subtotal_amount',
        'vat_rate',
        'vat_amount',
        'total_amount',
        'status',
        'payment_method',
        'issued_at',
        'notes',
        'issued_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'integer',
            'vat_rate' => 'integer',
            'vat_amount' => 'integer',
            'total_amount' => 'integer',
            'issued_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function scopeIssued(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ISSUED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 0, ',', '.').' đ';
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal_amount, 0, ',', '.').' đ';
    }

    public function getFormattedVatAttribute(): string
    {
        return number_format($this->vat_amount, 0, ',', '.').' đ';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ISSUED => 'Đã Phát Hành',
            self::STATUS_PENDING => 'Chờ Xuất Hóa Đơn',
            self::STATUS_CANCELLED => 'Đã Hủy Hóa Đơn',
            default => 'Không Xác Định',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ISSUED => '#2E7D32',
            self::STATUS_PENDING => '#F57F17',
            self::STATUS_CANCELLED => '#C62828',
            default => '#7A756D',
        };
    }
}
