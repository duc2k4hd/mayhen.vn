<?php

namespace Database\Seeders;

use App\Models\InvitationTemplate;
use Illuminate\Database\Seeder;

class InvitationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds the flagship wedding invitation template: "Nét Mực Tình Yêu".
     */
    public function run(): void
    {
        $templates = [
            [
                'slug' => 'net-muc-tinh-yeu',
                'name' => 'Nét Mực Tình Yêu',
                'category' => 'luxury',
                'thumbnail_url' => '/mau-thiep/net-muc-tinh-yeu/assets/assets/3ed9dcf0-26a2-47ad-af9e-8895bff01519.jpg',
                'demo_url' => '/xem-thu/net-muc-tinh-yeu',
                'default_config' => [
                    'tier' => 'pro',
                    'complexity' => 'deluxe',
                    'setup_time' => '5 phút',
                    'accent_color' => '#151515',
                    'font_serif' => 'Quarzo',
                    'animation' => 'editorial-scroll',
                    'style_layout' => 'ink-classic',
                    'required_inputs' => [
                        'basic_info',
                        'wedding_event',
                        'photos',
                        'story',
                        'timeline',
                        'dresscode',
                        'rsvp',
                        'gift',
                    ],
                    'features' => [
                        'Thiết kế Quiet Luxury typography cao cấp kết hợp thư pháp phương Tây',
                        'Tự động mở nhạc nền lãng mạn kèm phím điều khiển đĩa than tinh xảo',
                        'Hai tab sự kiện riêng biệt cho Tiệc Cưới Nhà Gái & Nhà Trai',
                        'Album ảnh cưới 10 bức tối ưu hóa Lightbox chạm phóng to sắc nét',
                        'Đếm ngược ngày cưới & Lịch ngày hạnh phúc độc quyền',
                        'Sổ lưu bút lời chúc Online & Phản hồi tham dự (RSVP) tích hợp',
                        'Mã QR mừng cưới VietQR riêng cho Chú rể và Cô dâu',
                        'Toàn bộ nội dung chữ, ảnh, timeline và dresscode đều có thể tùy chỉnh 100%',
                    ],
                    'description' => 'Mẫu thiệp cưới mang phong cách Quiet Luxury tối giản, sang trọng tựa những trang tạp chí nghệ thuật cao cấp. Đầy đủ tính năng thiệp báo hỷ hiện đại, tương thích hoàn hảo mọi thiết bị di động.',
                    'tags' => ['Luxury', 'Nét Mực', 'Editorial', 'Thư pháp', 'Đầy đủ tính năng'],
                    'stats' => ['pairs_used' => 248, 'rating' => 5.0],
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'thuoc-phim-tinh-yeu',
                'name' => 'Thước Phim Tình Yêu',
                'category' => 'modern',
                'thumbnail_url' => '/mau-thiep/thuoc-phim-tinh-yeu/assets/3ed9dcf0-26a2-47ad-af9e-8895bff01519.jpg',
                'demo_url' => '/xem-thu/thuoc-phim-tinh-yeu',
                'default_config' => [
                    'tier' => 'pro',
                    'complexity' => 'deluxe',
                    'setup_time' => '5 phút',
                    'accent_color' => '#4A3B32',
                    'font_serif' => 'Cormorant Garamond',
                    'animation' => 'film-reel-scroll',
                    'style_layout' => 'film-strip',
                    'required_inputs' => [
                        'basic_info',
                        'wedding_event',
                        'photos',
                        'story',
                        'timeline',
                        'dresscode',
                        'rsvp',
                        'gift',
                    ],
                    'features' => [
                        'Thiết kế phong cách thước phim điện ảnh lãng mạn kết hợp cuộn Film strip chạy vòng độc đáo',
                        'Hiệu ứng rèm mở đầu (Door opening) kèm nhạc nền cưới du dương',
                        'Hai tab sự kiện riêng biệt cho Lễ Vu Quy & Tiệc Cưới',
                        'Album ảnh cưới 10 bức với Lightbox vuốt chạm phóng to sắc nét',
                        'Đếm ngược ngày cưới & Lịch tháng điểm trái tim ngày trọng đại',
                        'Sổ lưu bút lời chúc Online kèm canvas ký tên tay cảm ứng',
                        'Phản hồi tham dự (RSVP) trực tuyến tiện lợi',
                        'Hộp mừng cưới thông minh hiển thị mã QR và số tài khoản Chú rể / Cô dâu',
                        'Toàn bộ nội dung chữ, ảnh, timeline và dresscode đều có thể tùy chỉnh 100%',
                    ],
                    'description' => 'Mẫu thiệp cưới tái hiện thước phim tình yêu điện ảnh sống động, kết hợp dải film trượt vòng, hiệu ứng mở cửa rèm ấn tượng, sổ lưu bút chữ ký tay và quản lý khách mời chu đáo.',
                    'tags' => ['Modern', 'Thước Phim', 'Điện Ảnh', 'Film Strip', 'Chữ ký tay'],
                    'stats' => ['pairs_used' => 186, 'rating' => 4.9],
                ],
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'slug' => 'tung-trang-hanh-phuc',
                'name' => 'Từng Trang Hạnh Phúc',
                'category' => 'editorial',
                'thumbnail_url' => '/mau-thiep/tung-trang-hanh-phuc/assets/thumbnail.png',
                'demo_url' => '/xem-thu/tung-trang-hanh-phuc',
                'default_config' => [
                    'tier' => 'free',
                    'complexity' => 'deluxe',
                    'setup_time' => '5 phút',
                    'accent_color' => '#8f533a',
                    'font_serif' => 'Cinzel, Playfair Display',
                    'animation' => 'envelope-reveal-luxury',
                    'style_layout' => 'editorial-storybook',
                    'required_inputs' => [
                        'basic_info',
                        'wedding_event',
                        'photos',
                        'story',
                        'timeline',
                        'dresscode',
                        'rsvp',
                        'gift',
                    ],
                    'features' => [
                        'Hiệu ứng phong bì thư sang trọng với tem sáp niêm phong và thư trồi lên 3D êm ái',
                        'Tự động phát nhạc cưới du dương kèm phím xoay đĩa than tinh xảo',
                        'Hai tab sự kiện chi tiết: Lễ Thành Hôn & Tiệc Cưới kèm bản đồ chỉ đường một chạm',
                        'Album kỷ niệm tình yêu 6 khung hình kèm Lightbox phóng to sắc nét chuẩn màn hình Retina',
                        'Đếm ngược ngày trọng đại theo thời gian thực (Ngày, Giờ, Phút, Giây)',
                        'Sổ lưu bút chúc phúc trực tuyến AJAX mượt mà không tải lại trang',
                        'Xác nhận tham dự (RSVP) chọn số lượng khách và nhắn gửi chu đáo',
                        'Hộp mừng cưới thông minh hiển thị mã QR VietQR và sao chép số tài khoản nhanh chóng',
                    ],
                    'description' => 'Mẫu thiệp cưới phong cách Editorial Storybook cao cấp. Từng trang tình yêu mở ra với hiệu ứng phong bì tem sáp nghệ thuật, kết hợp âm nhạc, bản đồ, album ảnh và sổ lưu bút điện tử tiện lợi.',
                    'tags' => ['Editorial', 'Storybook', 'Phong bì tem sáp', 'Quiet Luxury', 'Từng Trang Hạnh Phúc'],
                    'stats' => ['pairs_used' => 312, 'rating' => 5.0],
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
