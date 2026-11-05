<?php

namespace Database\Seeders;

use App\Models\InvitationTemplate;
use Illuminate\Database\Seeder;

class InvitationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds the wedding invitation template: "Giữa Muôn Ngàn Vì Sao" (Template kiểm thử chuẩn).
     */
    public function run(): void
    {
        $templates = [
            [
                'slug' => 'giua-muon-ngan-vi-sao',
                'name' => 'Giữa Muôn Ngàn Vì Sao',
                'category' => 'luxury',
                'thumbnail_url' => '/mau-thiep/giua-muon-ngan-vi-sao/assets/thumbnail.webp',
                'demo_url' => '/xem-thu/giua-muon-ngan-vi-sao',
                'default_config' => [
                    'tier' => 'free',
                    'complexity' => 'deluxe',
                    'setup_time' => '5 phút',
                    'accent_color' => '#fce39e',
                    'font_serif' => 'Cormorant Garamond, Great Vibes',
                    'animation' => 'canvas-shooting-stars-deepspace',
                    'style_layout' => 'celestial-starlight',
                    'required_inputs' => [
                        'basic_info',
                        'wedding_event',
                        'photos',
                        'story',
                        'timeline',
                        'rsvp',
                        'gift',
                    ],
                    'features' => [
                        'Màn sao rơi lung linh huyền ảo với hiệu ứng canvas tương tác chuyển động mượt mà',
                        'Bản đồ chỉ đường 3D hai cánh mở độc đáo kèm chỉ dẫn Google Maps trực tiếp',
                        'Album ảnh cưới 3D Coverflow nghệ thuật cùng trình xem phóng to toàn màn hình',
                        'Sổ lưu bút trực tuyến kèm bảng ký tên tay công nghệ cảm ứng mượt mà',
                        'Hộp mừng cưới 3D mở bung tia sao kèm mã VietQR tự động và sao chép số tài khoản',
                        'Đồng hồ đếm ngược thiên hà theo thời gian thực và lịch cưới Google Calendar',
                        'Tối ưu hóa 100% trên điện thoại di động, tự động đồng bộ Live Sync trong Studio Editor',
                        'Hoàn toàn MIỄN PHÍ (Free) dành tặng các cặp đôi của Mây Hẹn',
                    ],
                    'timeline' => [
                        ['time' => '17:30', 'title' => 'Đón khách'],
                        ['time' => '18:00', 'title' => 'Cử hành hôn lễ'],
                        ['time' => '18:30', 'title' => 'Khai tiệc'],
                        ['time' => '20:00', 'title' => 'Giao lưu & chụp ảnh'],
                    ],
                    'gallery' => [
                        'c3655bd60267c1b2.webp',
                        'a5a53978e8384d26.webp',
                        '7da1e3a6d59c1a98.webp',
                        '40f8fb60afcb59af.webp',
                        '4d93c5df689bbae4.webp',
                        '872462fa026e4df9.webp',
                        '14325e164a136da2.webp',
                        'f5cbab06395d882b.webp',
                        '686ca1bfd6d65f13.webp',
                        'fbf493cec2de5fdc.webp',
                    ],
                    'description' => 'Mẫu thiệp cưới phong cách Giữa Muôn Ngàn Vì Sao huyền ảo, lãng mạn. Hiệu ứng hạt sao rơi tương tác, bản đồ 3D cánh mở, album ảnh Coverflow và sổ lưu bút điện tử tiện lợi.',
                    'tags' => ['Free', 'Vì Sao', 'Thiên Hà', 'Bản Đồ 3D', 'Chữ Ký Tay', 'Giữa Muôn Ngàn Vì Sao'],
                    'stats' => ['pairs_used' => 198, 'rating' => 5.0],
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
        ];

        foreach ($templates as $template) {
            InvitationTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
