<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvitationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'category',
        'thumbnail_url',
        'demo_url',
        'default_config',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'default_config' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'template_id');
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->thumbnail_url,
        );
    }

    protected function badge(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_featured ? 'Nổi Bật Nhất' : 'Phong Cách Mới',
        );
    }

    protected function categoryLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->category) {
                'luxury' => 'Quiet Luxury',
                'editorial' => 'Editorial Style',
                'minimal' => 'Tối Giản Tinh Tế',
                default => 'Thiệp Cưới Cao Cấp',
            },
        );
    }

    protected function features(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->category) {
                'luxury' => [
                    'Hiệu ứng mở phong thư hoàng gia',
                    'Nhạc nền lãng mạn tự động',
                    'RSVP xác nhận tham dự theo gia đình',
                    'Tích hợp bản đồ Google Maps',
                ],
                'editorial' => [
                    'Bố cục phong cách tạp chí cưới',
                    'Cuộn thị giác nghệ thuật',
                    'Sổ lời chúc online cho bạn bè',
                    'Mã QR mừng cưới chính chủ',
                ],
                default => [
                    'Khoảng trắng thở thanh lịch',
                    'Typography chuẩn quốc tế',
                    'Tải nhanh trên mọi dòng điện thoại',
                    'Thân thiện với người lớn tuổi',
                ],
            },
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->category) {
                'luxury' => 'Hiệu ứng mở phong thư sáp niêm phong cao cấp, thiệp nâng nhẹ nhàng với cánh hoa rơi.',
                'editorial' => 'Bố cục tạp chí thời trang nghệ thuật, tôn vinh hình ảnh và câu chuyện tình yêu mộc mạc.',
                default => 'Khoảng trắng phóng khoáng, phong cách kem cát ấm áp, tập trung vào typography và cảm xúc.',
            },
        );
    }
}
