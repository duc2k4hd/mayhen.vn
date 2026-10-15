<?php

use App\Models\InvitationTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tierMap = [
            // Gói Pro VIP (Tem sáp hoàng gia, tạp chí editorial, cung đình)
            'envelope-reveal-luxury' => [
                'tier' => 'pro',
                'complexity' => 'deluxe',
                'setup_time' => '10 phút',
                'description' => 'Mẫu thiệp hoàng gia mở phong thư lụa kèm dấu sáp 3D dập nổi, nhạc nền du dương và album ảnh cưới không giới hạn.',
                'stats' => ['pairs_used' => 248, 'rating' => 4.9],
            ],
            'editorial-scroll-vogue' => [
                'tier' => 'pro',
                'complexity' => 'deluxe',
                'setup_time' => '10 phút',
                'description' => 'Thiết kế phong cách tạp chí thời trang Vogue cao cấp, font chữ Editorial tinh tế dành cho cặp đôi hiện đại.',
                'stats' => ['pairs_used' => 195, 'rating' => 4.9],
            ],
            'song-hy-red' => [
                'tier' => 'pro',
                'complexity' => 'deluxe',
                'setup_time' => '10 phút',
                'description' => 'Song Hỷ cổ điển tone đỏ hoàng gia, hoa văn gấm dệt cung đình tôn vinh nghi thức cưới hỏi truyền thống sang trọng.',
                'stats' => ['pairs_used' => 312, 'rating' => 5.0],
            ],
            'song-hy-green' => [
                'tier' => 'pro',
                'complexity' => 'deluxe',
                'setup_time' => '10 phút',
                'description' => 'Sắc xanh ngọc cẩm thạch quý phái kết hợp chữ Hỷ vàng kim dát nổi, mang lại cảm giác thanh nhã và may mắn.',
                'stats' => ['pairs_used' => 164, 'rating' => 4.9],
            ],
            'song-hy-v2-red' => [
                'tier' => 'pro',
                'complexity' => 'deluxe',
                'setup_time' => '10 phút',
                'description' => 'Phiên bản Song Hỷ cách tân hiện đại, viền chỉ vàng kim sắc sảo và bố cục trang nhã.',
                'stats' => ['pairs_used' => 142, 'rating' => 4.8],
            ],
            'long-phung-red' => [
                'tier' => 'pro',
                'complexity' => 'deluxe',
                'setup_time' => '10 phút',
                'description' => 'Họa tiết Long Phụng sum vầy dát vàng trên nền đỏ nhung, tượng trưng cho tình duyên sắt son vĩnh cửu.',
                'stats' => ['pairs_used' => 278, 'rating' => 4.9],
            ],

            // Gói Miễn Phí (Setup siêu tốc 2 phút, tối giản tinh tế)
            'minimal-sand-beige' => [
                'tier' => 'free',
                'complexity' => 'quick',
                'setup_time' => '2 phút',
                'description' => 'Phong cách tối giản trên nền giấy màu be cát ấm áp, chỉ cần nhập tên và địa chỉ là hoàn thành ngay thiệp.',
                'stats' => ['pairs_used' => 420, 'rating' => 4.8],
            ],
            'minimalism-darkred' => [
                'tier' => 'free',
                'complexity' => 'quick',
                'setup_time' => '2 phút',
                'description' => 'Thiết kế tối giản gam màu đỏ rượu vang thanh lịch, tạo thiệp cưới miễn phí siêu tốc dưới 2 phút.',
                'stats' => ['pairs_used' => 186, 'rating' => 4.7],
            ],
            'minimalism-darkbrown' => [
                'tier' => 'free',
                'complexity' => 'quick',
                'setup_time' => '2 phút',
                'description' => 'Tone nâu trầm ấm áp, phong cách mộc mạc tinh tế, đầy đủ bản đồ chỉ đường và thông tin cưới cốt lõi.',
                'stats' => ['pairs_used' => 155, 'rating' => 4.8],
            ],
            'mai-lan-white' => [
                'tier' => 'free',
                'complexity' => 'quick',
                'setup_time' => '2 phút',
                'description' => 'Họa tiết hoa cúc trắng và nhành lan tao nhã, nhẹ nhàng tinh khôi, miễn phí trọn vẹn.',
                'stats' => ['pairs_used' => 210, 'rating' => 4.8],
            ],

            // Gói Plus (Hoa lá thiên nhiên, lãng mạn, album ảnh)
            'botanical-sage-garden' => [
                'tier' => 'plus',
                'complexity' => 'standard',
                'setup_time' => '5 phút',
                'description' => 'Hương sắc thảo mộc và lá xô thơm tự nhiên, mang lại cảm giác bình yên, tươi mát cho tiệc cưới sân vườn.',
                'stats' => ['pairs_used' => 265, 'rating' => 4.9],
            ],
            'romantic-dusty-rose' => [
                'tier' => 'plus',
                'complexity' => 'standard',
                'setup_time' => '5 phút',
                'description' => 'Sắc hoa hồng tro dịu ngọt lãng mạn với hiệu ứng cánh hoa rơi nhẹ nhàng, chạm đến trái tim khách mời.',
                'stats' => ['pairs_used' => 340, 'rating' => 4.9],
            ],
            'cinema-modern-lovestory' => [
                'tier' => 'plus',
                'complexity' => 'standard',
                'setup_time' => '5 phút',
                'description' => 'Tái hiện câu chuyện tình yêu như một thước phim điện ảnh lãng mạn với timeline tình yêu và album ảnh HD.',
                'stats' => ['pairs_used' => 285, 'rating' => 4.9],
            ],
            'boho-floral-green' => [
                'tier' => 'plus',
                'complexity' => 'standard',
                'setup_time' => '5 phút',
                'description' => 'Phong cách Bohemian tự do phóng khoáng, kết hợp nhành hoa dại đồng nội mộc mạc và cuốn hút.',
                'stats' => ['pairs_used' => 172, 'rating' => 4.8],
            ],
            'to-duyen-green' => [
                'tier' => 'plus',
                'complexity' => 'standard',
                'setup_time' => '5 phút',
                'description' => 'Tơ Duyên màu xanh biếc thanh tao, tôn vinh sợi dây gắn kết nhân duyên đôi lứa.',
                'stats' => ['pairs_used' => 138, 'rating' => 4.8],
            ],
            'vuon-kinh-pink' => [
                'tier' => 'plus',
                'complexity' => 'standard',
                'setup_time' => '5 phút',
                'description' => 'Không gian nhà kính pha lê trong suốt hòa quyện sắc hồng phấn mơ mộng, lãng mạn như cổ tích.',
                'stats' => ['pairs_used' => 198, 'rating' => 4.9],
            ],
        ];

        foreach ($tierMap as $slug => $data) {
            $template = InvitationTemplate::where('slug', $slug)->first();
            if ($template) {
                $config = $template->default_config ?? [];
                $config['tier'] = $data['tier'];
                $config['complexity'] = $data['complexity'];
                $config['setup_time'] = $data['setup_time'];
                $config['description'] = $data['description'];
                $config['stats'] = $data['stats'];

                $template->default_config = $config;
                $template->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
