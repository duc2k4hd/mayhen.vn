<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'integer',
            'min_order_amount' => 'integer',
            'max_discount_amount' => 'integer',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope coupon đang được kích hoạt và còn trong thời gian hiệu lực.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope coupon hợp lệ tại thời điểm hiện tại (còn lượt, chưa hết hạn).
     */
    public function scopeValid(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->where(function (Builder $q) {
                $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            });
    }

    /**
     * Kiểm tra tính hợp lệ của coupon đối với giá trị đơn hàng cụ thể.
     */
    public function isValid(?int $orderAmount = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($orderAmount !== null && $orderAmount < $this->min_order_amount) {
            return false;
        }

        return true;
    }

    /**
     * Tính toán số tiền được giảm giá thực tế (chống âm tiền, chống giảm vượt đơn hàng).
     */
    public function calculateDiscount(int $orderAmount): int
    {
        if (! $this->isValid($orderAmount) || $orderAmount <= 0) {
            return 0;
        }

        if ($this->discount_type === 'percent') {
            $rawDiscount = (int) round(($orderAmount * $this->discount_value) / 100);

            if ($this->max_discount_amount !== null && $this->max_discount_amount > 0) {
                $rawDiscount = min($rawDiscount, $this->max_discount_amount);
            }

            return min($rawDiscount, $orderAmount);
        }

        // Loại giảm số tiền cố định (fixed)
        return min($this->discount_value, $orderAmount);
    }

    /**
     * Tăng số lần sử dụng của coupon lên 1.
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Định dạng chuỗi hiển thị mức giảm giá (VD: "20%" hoặc "50.000đ").
     */
    protected function formattedDiscount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->discount_type === 'percent'
                ? $this->discount_value.'%'
                : number_format($this->discount_value, 0, ',', '.').'đ',
        );
    }

    /**
     * Trạng thái text và nhãn màu để hiển thị badge giao diện.
     */
    public function getStatusMeta(): array
    {
        if (! $this->is_active) {
            return ['label' => 'Đã vô hiệu', 'class' => 'mayhen_admin_badge_neutral'];
        }

        $now = Carbon::now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return ['label' => 'Sắp diễn ra', 'class' => 'mayhen_admin_badge_info'];
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return ['label' => 'Đã hết hạn', 'class' => 'mayhen_admin_badge_danger'];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['label' => 'Hết lượt dùng', 'class' => 'mayhen_admin_badge_warning'];
        }

        return ['label' => 'Đang hoạt động', 'class' => 'mayhen_admin_badge_success'];
    }
}
