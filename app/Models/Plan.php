<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

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
            get: fn () => in_array($this->slug, ['plus', 'premium']),
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->slug) {
                'free' => 'Dành cho cặp đôi muốn tạo thử trước hoặc thích phong cách thiệp tinh gọn, setup 2 phút.',
                'plus', 'premium' => 'Gói được 85% cặp đôi chọn: mở khóa toàn bộ mẫu hoa lá, nhạc nền, album ảnh & RSVP.',
                default => 'Gói cao cấp nhất: mở khóa mẫu phong thư hoàng gia tem sáp 3D, tạp chí Vogue, passport & tách link 2 nhà.',
            },
        );
    }

    protected function period(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price === 0 ? '' : '/đám cưới (dùng trọn đời)',
        );
    }

    protected function cta(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->slug) {
                'free' => 'Tạo Thiệp Miễn Phí',
                'plus', 'premium' => 'Chọn Gói Hạnh Phúc',
                default => 'Chọn Gói Trọn Vẹn (Pro)',
            },
        );
    }
}
