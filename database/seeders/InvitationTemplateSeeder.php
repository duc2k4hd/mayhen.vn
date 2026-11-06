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
            [
                'slug' => 'hanh-phuc-mang-ten-chung-minh',
                'name' => 'Hạnh Phúc Mang Tên Chúng Mình',
                'category' => 'modern',
                'thumbnail_url' => '/mau-thiep/hanh-phuc-mang-ten-chung-minh/assets/hero-couple.webp',
                'demo_url' => '/xem-thu/hanh-phuc-mang-ten-chung-minh',
                'default_config' => [
                    'tier' => 'free',
                    'complexity' => 'editorial',
                    'setup_time' => '3 phút',
                    'accent_color' => '#233628',
                    'font_serif' => 'Cormorant Garamond, Playfair Display',
                    'animation' => 'petals-falling-sage',
                    'style_layout' => 'sage-botanical-editorial',
                    'required_inputs' => [
                        'basic_info',
                        'wedding_event',
                        'photos',
                        'story',
                        'rsvp',
                        'gift',
                    ],
                    'features' => [
                        'Cánh cửa mở đôi song hỷ với tem niêm phong sáp cổ điển ấn tượng',
                        'Hiệu ứng cánh lá xô thơm rơi nhẹ nhàng 60FPS tạo cảm xúc mộc mạc thanh lịch',
                        'Sổ lưu bút chữ ký tay tương tác đa màu mực cùng tem wax seal hoàng gia (không ảnh người)',
                        'Biểu mẫu RSVP thông minh xác nhận tham dự và gửi lời chúc phúc chân thành',
                        'Bộ ba ảnh kỷ niệm nghệ thuật và album hình cưới 8 ảnh chất lượng cao',
                        'Hộp mừng cưới trang nhã kèm mã VietQR tự động và tính năng sao chép STK một chạm',
                        'Đồng hồ đếm ngược và lịch tháng Save The Date cập nhật thời gian thực',
                        'Tương thích hoàn hảo trên mọi thiết bị di động, tự động đồng bộ Studio Editor Live Sync',
                    ],
                    'gallery' => [
                        'gallery-1.webp',
                        'gallery-2.webp',
                        'gallery-3.webp',
                        'gallery-4.webp',
                        'gallery-5.webp',
                        'gallery-6.webp',
                        'gallery-7.webp',
                        'gallery-8.webp',
                    ],
                    'description' => 'Mẫu thiệp cưới phong cách Quiet Luxury tone Xanh Xô Thơm & Rừng Thu thanh nhã. Cánh cửa mở đôi ấn tượng, sổ lưu bút chữ ký nghệ thuật và tương tác thiệp cưới hiện đại.',
                    'tags' => ['Free', 'Xanh Sage', 'Quiet Luxury', 'Chữ Ký Tay', 'RSVP', 'Hạnh Phúc Mang Tên Chúng Mình'],
                    'stats' => ['pairs_used' => 125, 'rating' => 4.9],
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'ngay-tinh-yeu-dom-hoa',
                'name' => 'Ngày Tình Yêu Đơm Hoa',
                'category' => 'luxury',
                'thumbnail_url' => '/mau-thiep/ngay-tinh-yeu-dom-hoa/assets/images/hero-couple.webp',
                'demo_url' => '/xem-thu/ngay-tinh-yeu-dom-hoa',
                'default_config' => [
                    'tier' => 'free',
                    'complexity' => 'editorial',
                    'setup_time' => '3 phút',
                    'accent_color' => '#8e0101',
                    'font_serif' => 'MayhenSerif, MayhenScript',
                    'animation' => 'snowflakes-falling-red',
                    'style_layout' => 'red-luxury-calligraphy',
                    'required_inputs' => [
                        'basic_info',
                        'wedding_event',
                        'photos',
                        'story',
                        'rsvp',
                        'gift',
                    ],
                    'features' => [
                        'Cánh cửa đôi Song Hỷ 3D mở bung với hoa tuyết rơi nhẹ nhàng lãng mạn',
                        'Thông báo hôn lễ Ăn Hỏi và Thành Hôn trang trọng kiểu truyền thống',
                        'Bộ ba ảnh kỷ niệm nghệ thuật cùng thư mời tiệc cưới chi tiết',
                        'Đồng hồ đếm ngược và lịch tháng Save The Date cập nhật thời gian thực',
                        'Chân dung Chú Rể và Cô Dâu với lời nhắn tâm tình riêng',
                        'Album 8 ảnh cưới chất lượng cao kèm lightbox phóng to toàn màn hình',
                        'Hộp mừng cưới kèm mã VietQR tự động Nhà Trai/Nhà Gái',
                        'RSVP thông minh, Sổ lưu bút chữ ký tay và Lời chúc từ khách mời',
                    ],
                    'gallery' => [
                        'gallery-01.webp',
                        'gallery-02.webp',
                        'gallery-03.webp',
                        'gallery-04.webp',
                        'gallery-05.webp',
                        'gallery-06.webp',
                        'gallery-07.webp',
                        'gallery-08.webp',
                    ],
                    'description' => 'Mẫu thiệp cưới phong cách Quiet Luxury Đỏ Son truyền thống. Cánh cửa Song Hỷ mở bung, hoa tuyết rơi nhẹ nhàng, bộ ba ảnh nghệ thuật và tương tác hiện đại.',
                    'tags' => ['Free', 'Đỏ Son', 'Thư Pháp', 'Song Hỷ', 'Quiet Luxury', 'Ngày Tình Yêu Đơm Hoa'],
                    'stats' => ['pairs_used' => 0, 'rating' => 5.0],
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
