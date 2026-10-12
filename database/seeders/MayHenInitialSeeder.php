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
        ];

        foreach ($templates as $tplData) {
            InvitationTemplate::updateOrCreate(['slug' => $tplData['slug']], $tplData);
        }
    }
}
