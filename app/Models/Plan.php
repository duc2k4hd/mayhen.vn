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
            get: fn () => $this->slug === 'premium',
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->slug) {
                'free' => 'Tạo thiệp dùng thử và xem trước đầy đủ tính năng.',
                'premium' => 'Gói phổ biến nhất, đáp ứng hoàn hảo cho mọi cặp đôi.',
                default => 'Dành cho tiệc cưới quy mô lớn, cần cá nhân hóa tối đa.',
            },
        );
    }

    protected function period(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price === 0 ? '' : '/đám cưới',
        );
    }

    protected function cta(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->slug) {
                'free' => 'Tạo Thiệp Miễn Phí',
                'premium' => 'Chọn Gói Premium',
                default => 'Chọn Gói Pro',
            },
        );
    }
}
