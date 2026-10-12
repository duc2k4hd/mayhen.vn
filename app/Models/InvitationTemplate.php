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
                'luxury' => 'Hoàng Gia Tem Sáp',
                'editorial' => 'Tạp Chí Editorial',
                'minimal' => 'Tối Giản Sang Trọng',
                'floral' => 'Vườn Thơ & Hoa Cỏ',
                'romantic' => 'Hồng Khói Lãng Mạn',
                'modern' => 'Điện Ảnh Ký Ức',
                default => 'Thiệp Cưới Cao Cấp',
            },
        );
    }

    protected function accentColor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->default_config['accent_color'] ?? '#C5A880',
        );
    }

    protected function fontSerif(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->default_config['font_serif'] ?? 'Playfair Display',
        );
    }

    protected function animationType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->default_config['animation'] ?? 'envelope-reveal',
        );
    }

    protected function features(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->category) {
                'luxury' => [
                    'Hiệu ứng mở phong thư hoàng gia',
                    'Tem sáp niêm phong 3D nổi',
                    'Nhạc nền lãng mạn tự động',
                    'RSVP xác nhận tham dự theo gia đình',
                ],
                'editorial' => [
                    'Bố cục bìa tạp chí thời trang Vogue',
                    'Cuộn thị giác Parallax nghệ thuật',
                    'Sổ lời chúc online cho bạn bè',
                    'Mã QR mừng cưới tiện lợi',
                ],
                'floral' => [
                    'Hiệu ứng cánh hoa bay lướt nhẹ',
                    'Gam màu Sage Green thơ mộng',
                    'Đếm ngược ngày cưới thanh lịch',
                    'Tích hợp chỉ đường Google Maps',
                ],
                'romantic' => [
                    'Tông màu hồng khói dịu dàng',
                    'Dải ruy băng lụa tháo nơ mượt mà',
                    'Album ảnh kỷ niệm cảm xúc',
                    'Thiệp mời cá nhân hóa từng khách',
                ],
                'modern' => [
                    'Khung hình Cinema thước phim kỷ niệm',
                    'Typography hiện đại sắc nét',
                    'Chế độ xem tối ưu cho iPhone/Android',
                    'Chia sẻ tức thì qua Zalo & Facebook',
                ],
                default => [
                    'Khoảng trắng thở thanh lịch',
                    'Typography chuẩn phong cách Quiet Luxury',
                    'Tải siêu tốc trên mọi dòng điện thoại',
                    'Giao diện thân thiện với gia đình',
                ],
            },
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->category) {
                'luxury' => 'Phong cách hoàng gia quý phái với nắp phong thư gập mở, tem sáp niêm phong dập nổi và nhạc cưới du dương.',
                'editorial' => 'Đậm chất bìa tạp chí thời trang Vogue, tôn vinh bộ ảnh cưới với bố cục typography sắc sảo và cuốn hút.',
                'floral' => 'Mang hơi thở khu vườn mùa hạ thanh bình với sắc xanh Olive, nhành hoa lá nước vẽ tay và cánh hoa phiêu lãng.',
                'romantic' => 'Gam màu hồng khói ấm áp, thơ mộng như một bức thư tình tinh tế gửi trọn sự trân quý đến từng khách mời.',
                'modern' => 'Phong cách thước phim điện ảnh hiện đại, kể lại hành trình tình yêu sống động qua từng khung hình kỷ niệm.',
                default => 'Khoảng trắng phóng khoáng, phong cách kem cát ấm áp, tập trung vào typography và chiều sâu cảm xúc.',
            },
        );
    }
}
