<?php

namespace Database\Seeders;

use App\Models\InvitationTemplate;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class MayHenInitialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Initial Plans
        $plans = [
            [
                'slug' => 'free',
                'name' => 'Trải Nghiệm (Free)',
                'price' => 0,
                'duration_days' => 7,
                'max_invitations' => 1,
                'features' => [
                    'Tạo 1 thiệp cưới cơ bản',
                    'Xem trước không giới hạn trên mobile',
                    'Hiển thị thông tin cưới & bản đồ',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'premium',
                'name' => 'Hạnh Phúc (Premium)',
                'price' => 149000,
                'duration_days' => 180,
                'max_invitations' => 1,
                'features' => [
                    'Xuất bản link thiệp riêng chính thức',
                    'Mở khóa toàn bộ 3 mẫu thiệp cao cấp',
                    'Hiệu ứng phong thư & nhạc nền lãng mạn',
                    'RSVP xác nhận tham dự theo gia đình',
                    'QR mừng cưới chính chủ không thu phí',
                    'Lưu giữ kỷ niệm 6 tháng sau cưới',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'pro',
                'name' => 'Trọn Vẹn (Wedding Pro)',
                'price' => 299000,
                'duration_days' => 730,
                'max_invitations' => 3,
                'features' => [
                    'Tất cả tính năng của gói Premium',
                    'Nhân bản thiệp Nhà Trai / Nhà Gái riêng biệt',
                    'Công cụ gửi thiệp cá nhân hóa qua Zalo',
                    'Quản lý danh sách khách mời & xuất Excel',
                    'Sổ lời chúc online & album ảnh kỷ niệm',
                    'Lưu giữ kỷ niệm vĩnh viễn 2 năm',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(['slug' => $planData['slug']], $planData);
        }

        // 2. Initial Curated Templates
        $templates = [
            [
                'slug' => 'envelope-reveal-luxury',
                'name' => 'Hoàng Gia Cung Đình (Envelope Reveal)',
                'category' => 'luxury',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/envelope-reveal-luxury',
                'default_config' => [
                    'accent_color' => '#C5A880',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'envelope-reveal',
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'editorial-scroll-vogue',
                'name' => 'Tạp Chí Vogue Cưới (Editorial)',
                'category' => 'editorial',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/editorial-scroll-vogue',
                'default_config' => [
                    'accent_color' => '#1C1917',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'editorial-scroll',
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'minimal-sand-beige',
                'name' => 'Ánh Dương Tối Giản (Minimal Luxury)',
                'category' => 'minimal',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/minimal-sand-beige',
                'default_config' => [
                    'accent_color' => '#9FAF90',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'minimal-luxury',
                ],
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'slug' => 'botanical-sage-garden',
                'name' => 'Vườn Xanh Thơ Mộng (Botanical Garden)',
                'category' => 'floral',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/botanical-sage-garden',
                'default_config' => [
                    'accent_color' => '#879883',
                    'font_serif' => 'Cormorant Garamond',
                    'animation' => 'gentle-float',
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'romantic-dusty-rose',
                'name' => 'Hồng Khói Tình Thơ (Dusty Rose)',
                'category' => 'romantic',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/romantic-dusty-rose',
                'default_config' => [
                    'accent_color' => '#C48B8B',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'petals-fall',
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'cinema-modern-lovestory',
                'name' => 'Thước Phim Ký Ức (Cinema Story)',
                'category' => 'modern',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/cinema-modern-lovestory',
                'default_config' => [
                    'accent_color' => '#B89B72',
                    'font_serif' => 'Cinzel',
                    'animation' => 'film-slide',
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'song-hy-red',
                'name' => 'Song Hỷ - Đỏ Hoàng Gia',
                'category' => 'luxury',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/song-hy-red',
                'default_config' => [
                    'accent_color' => '#B32B2B',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'envelope-reveal',
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'song-hy-green',
                'name' => 'Song Hỷ - Xanh Ngọc Bích',
                'category' => 'luxury',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/song-hy-green',
                'default_config' => [
                    'accent_color' => '#3D6B52',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'envelope-reveal',
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'song-hy-v2-red',
                'name' => 'Song Hỷ V2 - Đỏ Nhung Quý Phái',
                'category' => 'luxury',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/song-hy-v2-red',
                'default_config' => [
                    'accent_color' => '#8C1D24',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'envelope-reveal',
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'boho-floral-green',
                'name' => 'Hoa Mộc - Xanh Thảo Mộc',
                'category' => 'floral',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/boho-floral-green',
                'default_config' => [
                    'accent_color' => '#6B8E68',
                    'font_serif' => 'Cormorant Garamond',
                    'animation' => 'gentle-float',
                ],
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'slug' => 'minimalism-darkred',
                'name' => 'Tối Giản - Đỏ Rượu Vang',
                'category' => 'minimal',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/minimalism-darkred',
                'default_config' => [
                    'accent_color' => '#721C24',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'minimal-luxury',
                ],
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'slug' => 'minimalism-darkbrown',
                'name' => 'Tối Giản - Nâu Cà Phê Ấm',
                'category' => 'minimal',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/minimalism-darkbrown',
                'default_config' => [
                    'accent_color' => '#5C4033',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'minimal-luxury',
                ],
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'slug' => 'mai-lan-white',
                'name' => 'Mai Lan - Trắng Tinh Khôi',
                'category' => 'floral',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/mai-lan-white',
                'default_config' => [
                    'accent_color' => '#D4AF37',
                    'font_serif' => 'Cormorant Garamond',
                    'animation' => 'gentle-float',
                ],
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'slug' => 'to-duyen-green',
                'name' => 'Tơ Duyên - Xanh Lam Tinh Tế',
                'category' => 'romantic',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/to-duyen-green',
                'default_config' => [
                    'accent_color' => '#4A6984',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'petals-fall',
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'vuon-kinh-pink',
                'name' => 'Vườn Kính - Hồng Phấn Dịu Ngọt',
                'category' => 'romantic',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/vuon-kinh-pink',
                'default_config' => [
                    'accent_color' => '#D88A96',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'petals-fall',
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'long-phung-red',
                'name' => 'Long Phụng - Cung Đình Đỏ Vàng',
                'category' => 'luxury',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?auto=format&fit=crop&w=800&q=80',
                'demo_url' => '/xem-thu/long-phung-red',
                'default_config' => [
                    'accent_color' => '#A62121',
                    'font_serif' => 'Playfair Display',
                    'animation' => 'envelope-reveal',
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
        ];

        foreach ($templates as $tplData) {
            InvitationTemplate::updateOrCreate(['slug' => $tplData['slug']], $tplData);
        }
    }
}
