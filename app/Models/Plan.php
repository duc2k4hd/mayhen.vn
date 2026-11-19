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
                'free' => 'Tạo thử ngay không cần đăng nhập, phù hợp cho cặp đôi thích phong cách tinh gọn, setup 2 phút.',
                'premium' => 'Miễn phí cho thành viên đã đăng nhập tài khoản: mở khóa kho mẫu Premium, lưu nháp & quản lý dễ dàng.',
                'plus' => 'Gói được 85% cặp đôi chọn: mở khóa toàn bộ mẫu hoa lá, nhạc nền, album ảnh & RSVP chỉ 99.000đ.',
                default => 'Gói cao cấp nhất: mở khóa mẫu phong thư hoàng gia tem sáp 3D, tạp chí Vogue, passport & tách link 2 nhà.',
            },
        );
    }

    protected function period(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->slug) {
                'free' => '/không cần đăng nhập',
                'premium' => '/miễn phí khi đăng nhập',
                default => $this->price === 0 ? '' : '/đám cưới (dùng trọn đời)',
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
