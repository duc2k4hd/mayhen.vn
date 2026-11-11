<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Ý Tưởng & Phong Cách Thiệp',
                'slug' => 'y-tuong-phong-cach-thiep',
                'description' => 'Khám phá xu hướng thiết kế thiệp cưới số Quiet Luxury, phong bì tem sáp hoàng gia và cách phối màu tinh tế.',
                'icon' => 'sparkles',
                'sort_order' => 1,
            ],
            [
                'name' => 'Kế Hoạch & Lễ Nghi Ngày Cưới',
                'slug' => 'ke-hoach-le-nghi-ngay-cuoi',
                'description' => 'Cẩm nang chuẩn bị cưới, nghi thức gia tiên, thời gian gửi thiệp và nguyên tắc đón tiếp quan khách chu đáo.',
                'icon' => 'calendar',
                'sort_order' => 2,
            ],
            [
                'name' => 'Kinh Nghiệm Khách Mời & RSVP',
                'slug' => 'kinh-nghiem-khach-moi-rsvp',
                'description' => 'Giải pháp quản lý bàn tiệc, xác nhận tham dự 1 chạm và cách gửi thiệp cưới cá nhân hóa qua Zalo.',
                'icon' => 'users',
                'sort_order' => 3,
            ],
            [
                'name' => 'Xu Hướng Cưới Tối Giản Hiện Đại',
                'slug' => 'xu-huong-cuoi-toi-gian',
                'description' => 'Nghệ thuật tổ chức đám cưới thân mật (Intimate Wedding), tiệc cưới sân vườn và phong cách tối giản.',
                'icon' => 'feather',
                'sort_order' => 4,
            ],
        ];

        $categoryMap = [];
        foreach ($categories as $catData) {
            $cat = BlogCategory::updateOrCreate(['slug' => $catData['slug']], $catData);
            $categoryMap[$catData['slug']] = $cat->id;
        }

        $posts = [
            [
                'category_id' => $categoryMap['y-tuong-phong-cach-thiep'],
                'title' => 'Nghệ Thuật Thiệp Cưới Quiet Luxury: Khi Sự Tinh Tế Lên Ngôi',
                'slug' => 'nghe-thuat-thiep-cuoi-quiet-luxury-khi-su-tinh-te-len-ngoi',
                'summary' => 'Trong kỷ nguyên số, một cánh thiệp cưới cao cấp không cần quá nhiều chi tiết cầu kỳ hay hiệu ứng chói lóa. Phong cách Quiet Luxury chính là câu trả lời cho sự trang nhã và trân quý cảm xúc.',
                'cover_image_url' => '/assets/client/images/home/hero-wedding-card.webp',
                'author_name' => 'Mây Hẹn Editorial',
                'tags' => ['Quiet Luxury', 'Thiệp Cưới Online', 'Phong Cách Sống'],
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'reading_time_minutes' => 4,
                'is_featured' => true,
                'meta_title' => 'Nghệ Thuật Thiệp Cưới Quiet Luxury — Mây Hẹn',
                'meta_description' => 'Khám phá triết lý Quiet Luxury trong thiệp cưới online cao cấp: tối giản, trang nhã, chạm tới xúc cảm người nhận.',
                'content' => '
<h2>1. Khái niệm Quiet Luxury trong không gian cưới hiện đại</h2>
<p>Quiet Luxury — sự xa xỉ thầm lặng — không phô trương bằng những màu sắc chói lọi hay hoa văn rườm rà. Trong thiệp cưới, điều này thể hiện qua sự cân đối tuyệt đối của tỷ lệ typographic, khoảng trắng (whitespace) thoáng đãng như một trang tạp chí nghệ thuật, và những chuyển động mượt mà của phong bì tem sáp 3D.</p>

<h2>2. Bảng màu trầm ấm và chất liệu thị giác</h2>
<p>Thay vì sử dụng màu sắc gắt, Mây Hẹn ưu tiên gam màu kem ấm (Warm Cream), ngà voi lụa (Ivory), và điểm xuyết ánh kim cổ điển (Muted Gold). Khi quan khách mở thiệp trên điện thoại, cảm giác như họ đang lật mở một lá thư tay được viết riêng cho chính mình.</p>

<h2>3. Cá nhân hóa từng lời mời</h2>
<p>Một điểm khác biệt lớn của thiệp cưới số Mây Hẹn là khả năng cá nhân hóa danh xưng quan khách: "Kính mời Chú Bác", "Thân mời Bạn hiền". Khách mời nhận được liên kết thiệp sẽ thấy tên mình xuất hiện trang trọng ngay trên phong bì và lời mời, mang lại cảm giác được nâng niu và tôn trọng.</p>
',
            ],
            [
                'category_id' => $categoryMap['ke-hoach-le-nghi-ngay-cuoi'],
                'title' => 'Thời Điểm Vàng Để Gửi Thiệp Cưới Online Giúp Tỷ Lệ Tham Dự Cao Nhất',
                'slug' => 'thoi-diem-vang-de-gui-thiep-cuoi-online',
                'summary' => 'Gửi thiệp quá sớm khách dễ quên, gửi quá muộn khách không kịp sắp xếp lịch trình. Hãy cùng Mây Hẹn tìm hiểu mốc thời gian hoàn hảo để phát đi cánh thiệp cưới số.',
                'cover_image_url' => '/assets/client/images/home/wedding-couple-editorial.webp',
                'author_name' => 'Cố Vấn Lễ Nghi Mây Hẹn',
                'tags' => ['Lễ Nghi Cưới', 'Lịch Trình', 'RSVP'],
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'reading_time_minutes' => 5,
                'is_featured' => true,
                'meta_title' => 'Thời Điểm Gửi Thiệp Cưới Online Hoàn Hảo — Mây Hẹn',
                'meta_description' => 'Bí quyết chọn ngày giờ gửi thiệp cưới online và Save The Date để quan khách hào hứng tham dự đông đủ.',
                'content' => '
<h2>1. Save The Date: 2 đến 3 tháng trước ngày cưới</h2>
<p>Với những đám cưới tổ chức ở điểm đến xa (Destination Wedding) hoặc rơi vào mùa cao điểm cưới hỏi cuối năm, việc phát thiệp thông báo Save The Date trước 2-3 tháng là vô cùng cần thiết để khách mời sắp xếp công việc và phương tiện di chuyển.</p>

<h2>2. Thiệp Mời Chính Thức: 3 đến 4 tuần trước sự kiện</h2>
<p>Đây là "thời điểm vàng" để gửi thiệp cưới online chính thức kèm theo form xác nhận tham dự (RSVP). Khoảng thời gian này vừa đủ để khách ghi nhớ ngày vui và phản hồi số lượng người tham dự, giúp cô dâu chú rể chốt danh sách bàn tiệc chính xác.</p>

<h2>3. Lời Nhắc Nhẹ Nhàng: 3 ngày trước lễ thành hôn</h2>
<p>Hệ thống Mây Hẹn hỗ trợ tính năng gửi tin nhắn nhắc hẹn (Wedding Reminder) kèm mã QR bản đồ chỉ đường và thông tin bàn tiệc. Một lời nhắn chu đáo sẽ giúp khách mời cảm thấy được tiếp đón tận tâm.</p>
',
            ],
            [
                'category_id' => $categoryMap['kinh-nghiem-khach-moi-rsvp'],
                'title' => 'Bí Quyết Quản Lý Bàn Tiệc & Tránh Lãng Phí Bằng Hệ Thống RSVP Thông Minh',
                'slug' => 'bi-quyet-quan-ly-ban-tiec-bang-rsvp-thong-minh',
                'summary' => 'Nỗi lo lớn nhất của các cặp đôi là đặt dư bàn tiệc gây lãng phí chi phí, hoặc thiếu bàn khiến khách mời không có chỗ ngồi thoải mái. Giải pháp RSVP số chính là chiếc chìa khóa vạn năng.',
                'cover_image_url' => '/assets/client/images/home/hero-wedding-card.webp',
                'author_name' => 'Chuyên Gia Tổ Chức Cưới',
                'tags' => ['RSVP', 'Sắp Xếp Bàn Tiệc', 'Tiết Kiệm Chi Phí'],
                'status' => 'published',
                'published_at' => now()->subDays(7),
                'reading_time_minutes' => 4,
                'is_featured' => false,
                'meta_title' => 'Quản Lý Bàn Tiệc & RSVP Cưới Thông Minh — Mây Hẹn',
                'meta_description' => 'Cách dùng tính năng RSVP và cổng tiếp tân thông minh của Mây Hẹn để tối ưu chi phí bàn tiệc ngày cưới.',
                'content' => '
<h2>1. Thực trạng đau đầu về số lượng bàn tiệc</h2>
<p>Trung bình mỗi đám cưới truyền thống có độ lệch từ 10% đến 20% giữa số lượng thiệp phát ra và số khách thực tế có mặt. Điều này dẫn đến tình trạng phát sinh hàng chục triệu đồng tiền cỗ dự phòng hoặc sự bối rối khi khách đến quá đông.</p>

<h2>2. RSVP 1 chạm: Đơn giản cho khách, an tâm cho gia đình</h2>
<p>Với thiệp cưới số Mây Hẹn, khách mời chỉ cần nhấn "Sẽ Tham Dự" hoặc "Rất tiếc không thể đến" ngay trên điện thoại mà không cần đăng nhập hay thao tác phức tạp. Số liệu phản hồi lập tức được đồng bộ theo thời gian thực về Dashboard quản lý của cô dâu chú rể.</p>

<h2>3. Tiếp tân quét mã QR tại sảnh tiệc</h2>
<p>Đặc biệt, hệ thống cổng tiếp tân đa sảnh của Mây Hẹn cho phép quét mã QR trên thẻ tham dự của khách để hiển thị ngay vị trí bàn tiệc (ví dụ: Bàn 08 - Bạn Đại Học Chú Rể), mang lại trải nghiệm chuyên nghiệp tựa như một sự kiện quốc tế đẳng cấp.</p>
',
            ],
        ];

        foreach ($posts as $postData) {
            BlogPost::updateOrCreate(['slug' => $postData['slug']], $postData);
        }
    }
}
