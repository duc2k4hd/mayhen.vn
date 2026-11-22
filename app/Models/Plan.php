<?php

namespace App\Models;

use App\Http\Controllers\Client\PageController;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Plan extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('mayhen_pricing_plans');
            PageController::clearPricingMemoization();
        });
        static::deleted(function () {
            Cache::forget('mayhen_pricing_plans');
            PageController::clearPricingMemoization();
        });
    }

    protected $fillable = [
        'slug',
        'name',
        'price',
        'duration_days',
        'max_invitations',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'duration_days' => 'integer',
            'max_invitations' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price === 0 ? '0đ' : number_format($this->price, 0, ',', '.').'đ',
        );
    }

    protected function isFeatured(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->slug === 'plus',
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->slug) {
                'free' => 'Tạo nhanh tối đa 1 thiệp không cần đăng nhập, link lưu trữ 3 tháng (tự động dọn khi hết hạn).',
                'premium' => 'Mặc định miễn phí khi đăng ký tài khoản: tạo tối đa 3 thiệp, mở khóa mẫu Premium, lưu trữ 3 tháng.',
                'plus' => 'Gói Plus đăng ký & gia hạn theo chu kỳ 6 tháng: tạo tối đa 10 thiệp, mỗi thiệp tồn tại trọn vẹn 6 tháng.',
                default => 'Gói Pro đăng ký & gia hạn theo chu kỳ 1 năm: tạo tối đa 30 thiệp, mỗi thiệp tồn tại trọn vẹn 1 năm & mở khóa Thiệp Sự Kiện PRO.',
            },
        );
    }

    protected function period(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->slug) {
                'free' => '/ 3 tháng (tối đa 1 thiệp)',
                'premium' => '/ 3 tháng (tối đa 3 thiệp)',
                'plus' => '/ 6 tháng (tối đa 10 thiệp)',
                'pro' => '/ 1 năm (tối đa 30 thiệp)',
                default => $this->price === 0 ? '' : '/ chu kỳ',
            },
        );
    }

    protected function cta(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->slug) {
                'free' => 'Tạo Thiệp Ngay (0đ)',
                'premium' => 'Đăng Nhập & Tạo Thiệp',
                'plus' => 'Chọn Gói Plus (99k)',
                default => 'Chọn Gói Pro (199k)',
            },
        );
    }
}
