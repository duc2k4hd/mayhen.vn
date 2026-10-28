<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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
        'current_version',
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

    protected static function booted(): void
    {
        static::saved(function (InvitationTemplate $template) {
            static::clearTemplateCaches($template);
        });

        static::deleted(function (InvitationTemplate $template) {
            static::clearTemplateCaches($template);
        });
    }

    public static function clearTemplateCaches(?InvitationTemplate $template = null): void
    {
        Cache::forget('mayhen_home_featured_templates');
        Cache::forget('mayhen_home_gallery_templates');
        Cache::forget('mayhen_templates_total_count');
        Cache::forget('mayhen_sitemap_xml');
        if ($template) {
            Cache::forget("mayhen_template_detail_{$template->slug}");
        }
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
            get: function () {
                if ($this->is_featured) {
                    return 'Nổi Bật Nhất';
                }
                $tier = $this->tier;
                if ($tier === 'free') {
                    return 'Miễn Phí 100%';
                }
                if ($tier === 'pro') {
                    return 'Bản VIP Pro';
                }

                return 'Được Chuộng Nhất';
            },
        );
    }

    protected function tier(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->default_config['tier'] ?? 'plus',
        );
    }

    protected function tierLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->tier) {
                'free' => 'Gói Miễn Phí',
                'pro' => 'Gói Pro VIP',
                default => 'Gói Plus',
            },
        );
    }

    protected function tierBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->tier) {
                'free' => 'mayhen_tier_free',
                'pro' => 'mayhen_tier_pro',
                default => 'mayhen_tier_plus',
            },
        );
    }

    protected function complexity(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->default_config['complexity'] ?? 'standard',
        );
    }

    protected function setupTime(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->default_config['setup_time'] ?? match ($this->complexity) {
                'quick' => '2 phút',
                'deluxe' => '10 phút',
                default => '5 phút',
            },
        );
    }

    protected function complexityLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->complexity) {
                'quick' => 'Setup 2 phút (Ít thông tin)',
                'deluxe' => 'Setup đầy đủ (Nhiều ảnh & timeline)',
                default => 'Setup chuẩn 5 phút',
            },
        );
    }

    protected function requiredInputs(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->default_config['required_inputs'] ?? ['basic_info', 'wedding_event'],
        );
    }

    protected function styleLayout(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->default_config['style_layout'] ?? 'standard',
        );
    }

    protected function usageCount(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) ($this->default_config['stats']['pairs_used'] ?? 100),
        );
    }

    protected function tags(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->default_config['tags'] ?? [],
        );
    }

    protected function categoryLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->category) {
                'minimal' => 'Tối Giản Tinh Tế',
                'floral' => 'Hoa Lá & Thiên Nhiên',
                'traditional' => 'Truyền Thống Á Đông',
                'luxury' => 'Hoàng Gia Tem Sáp',
                'modern' => 'Hiện Đại Sắc Nét',
                'editorial' => 'Tạp Chí Editorial',
                'creative' => 'Độc Đáo (Passport / Lá Thư)',
                'cute' => 'Dễ Thương & Chibi',
                'romantic' => 'Lãng Mạn Pastel',
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
            get: fn () => $this->default_config['features'] ?? match ($this->category) {
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
                'traditional' => [
                    'Chữ Song Hỷ đỏ truyền thống',
                    'Lời kính báo hai họ trang nghiêm',
                    'Sơ đồ đường đi chi tiết',
                    'Hộp mừng cưới riêng biệt 2 nhà',
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
                'minimal' => [
                    'Nhập thông tin siêu tốc trong 2 phút',
                    'Typography chuẩn phong cách Quiet Luxury',
                    'Tải nhanh tức thì trên mọi thiết bị',
                    'Dễ đọc, phù hợp cả người lớn tuổi',
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
            get: fn () => $this->default_config['description'] ?? match ($this->category) {
                'luxury' => 'Phong cách hoàng gia quý phái với nắp phong thư gập mở, tem sáp niêm phong dập nổi và nhạc cưới du dương.',
                'editorial' => 'Đậm chất bìa tạp chí thời trang Vogue, tôn vinh bộ ảnh cưới với bố cục typography sắc sảo và cuốn hút.',
                'floral' => 'Mang hơi thở khu vườn mùa hạ thanh bình với sắc xanh Olive, nhành hoa lá nước vẽ tay và cánh hoa phiêu lãng.',
                'traditional' => 'Trang trọng, chuẩn mực truyền thống với sắc đỏ thắm hoàng hỷ, gắn kết hai họ bền chặt trăm năm.',
                'romantic' => 'Gam màu hồng khói ấm áp, thơ mộng như một bức thư tình tinh tế gửi trọn sự trân quý đến từng khách mời.',
                'modern' => 'Phong cách thước phim điện ảnh hiện đại, kể lại hành trình tình yêu sống động qua từng khung hình kỷ niệm.',
                'minimal' => 'Khoảng trắng phóng khoáng, phong cách kem cát ấm áp, tập trung vào sự tinh gọn và tốc độ setup.',
                default => 'Khoảng trắng phóng khoáng, phong cách kem cát ấm áp, tập trung vào typography và chiều sâu cảm xúc.',
            },
        );
    }
}
