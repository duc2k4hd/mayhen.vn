<?php

namespace Database\Seeders;

use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', 'admin')->first();

        $posts = [
            [
                'user_id' => $author?->id,
                'title' => 'Ra Mắt Bộ Sưu Tập Thiệp Cưới Hoàng Gia Pro & Hệ Thống Sắp Xếp Mẫu Thiệp Theo Giá Trị',
                'slug' => 'ra-mat-bo-suu-tap-thiep-cuoi-hoang-gia-pro-va-sap-xep-mau-thiep',
                'category_label' => 'Mẫu Thiệp Mới',
                'summary' => 'Mây Hẹn chính thức cập nhật trải nghiệm duyệt kho mẫu thiệp cưới: ưu tiên hiển thị các thiết kế Hoàng Gia Pro và Plus cao cấp nhất ngay đầu trang chủ và trang mẫu thiệp, giúp hai bạn dễ dàng chọn được mẫu thiệp ấn tượng nhất.',
                'content' => '<h2>1. Ưu tiên trải nghiệm các mẫu thiệp cao cấp nhất</h2><p>Để giúp cô dâu chú rể tiết kiệm thời gian khi tìm kiếm phong cách thiệp cưới ưng ý, <strong>Mây Hẹn</strong> đã nâng cấp thuật toán sắp xếp trên cả Trang Chủ và Kho Mẫu Thiệp (<a href="/mau-thiep">/mau-thiep</a>). Toàn bộ các mẫu thiệp thuộc phân hạng <strong>Hoàng Gia (Pro)</strong>, <strong>Nâng Cao (Plus)</strong> và <strong>Cao Cấp (Premium)</strong> sẽ được ưu tiên giới thiệu trước các mẫu Cơ Bản (Free).</p><h2>2. Chuẩn hóa 10 phần nội dung và đồng bộ Studio thời gian thực</h2><p>Mỗi mẫu thiệp trên Mây Hẹn đều hỗ trợ đầy đủ 10 phần nội dung chuẩn mực: từ Phong bì mở thiệp 3D, Câu chuyện tình yêu, Lịch đếm ngược, Bản đồ chỉ đường từng nhà tới Sổ ký tên, Báo hỷ RSVP và Mừng cưới VietQR.</p><h3>Cách trải nghiệm ngay hôm nay</h3><p>Bạn có thể truy cập trực tiếp vào <a href="/mau-thiep">Kho Mẫu Thiệp Mây Hẹn</a>, nhấn xem thử hoặc bắt đầu tự tạo thiệp cưới chỉ trong 5 phút.</p>',
                'cover_image_url' => '/storage/images/client/banners/banner-home.webp',
                'author_name' => 'Ban Phát Triển Mây Hẹn',
                'tags' => ['mẫu thiệp pro', 'cập nhật hệ thống', 'thiệp cưới quiet luxury'],
                'status' => 'published',
                'published_at' => now()->subDay(),
                'reading_time_minutes' => 3,
                'view_count' => 128,
                'is_featured' => true,
                'send_email_on_publish' => false,
                'meta_title' => 'Ra Mắt Bộ Sưu Tập Thiệp Cưới Hoàng Gia Pro & Sắp Xếp Mẫu Thiệp Mới',
                'meta_description' => 'Khám phá bản cập nhật mới từ Mây Hẹn: ưu tiên hiển thị các thiết kế thiệp cưới Hoàng Gia Pro và Plus tinh xảo nhất ngay đầu trang chủ và kho mẫu thiệp.',
            ],
            [
                'user_id' => $author?->id,
                'title' => 'Mây Hẹn Mở Chuyên Mục 380+ Bài Cẩm Nang Cưới Thực Tế Dành Cho Cặp Đôi Việt',
                'slug' => 'may-hen-mo-chuyen-muc-380-bai-cam-nang-cuoi-thuc-te',
                'category_label' => 'Tính Năng Mới',
                'summary' => 'Hơn 380 bài viết hướng dẫn chuẩn bị đám cưới từ nghi lễ truyền thống, cách ghi thiệp mời, quản lý ngân sách đến kinh nghiệm chốt bàn tiệc RSVP đã chính thức có mặt tại chuyên mục Cẩm Nang Cưới Mây Hẹn.',
                'content' => '<h2>1. Kho cẩm nang cưới thực tế, giản dị và dễ áp dụng</h2><p>Chuẩn bị đám cưới luôn có hàng trăm đầu việc khiến hai bạn bối rối. Đội ngũ biên tập <strong>Mây Hẹn</strong> đã biên soạn và xuất bản hơn 380 bài viết chuyên sâu tại <a href="/cam-nang-cuoi">Cẩm Nang Cưới (/cam-nang-cuoi)</a> với văn phong gần gũi, đi thẳng vào kinh nghiệm thực tế tại Việt Nam.</p><h2>2. Kết nối liền mạch với công cụ quản lý khách mời và RSVP</h2><p>Bên cạnh việc đọc kinh nghiệm chuẩn bị lễ cưới, hai bạn có thể áp dụng ngay các mẫu lời mời, danh sách khách mời và sơ đồ bàn tiệc trực tiếp trên bảng điều khiển thiệp cưới Mây Hẹn.</p>',
                'cover_image_url' => '/storage/images/client/banners/banner-home.webp',
                'author_name' => 'Ban Biên Tập Mây Hẹn',
                'tags' => ['cẩm nang cưới', 'tính năng mới', 'kinh nghiệm cưới'],
                'status' => 'published',
                'published_at' => now()->subHours(6),
                'reading_time_minutes' => 3,
                'view_count' => 95,
                'is_featured' => false,
                'send_email_on_publish' => false,
                'meta_title' => 'Mây Hẹn Mở Chuyên Mục 380+ Bài Cẩm Nang Cưới Thực Tế',
                'meta_description' => 'Hơn 380 bài viết hướng dẫn chuẩn bị đám cưới, cách viết thiệp mời và kinh nghiệm quản lý khách mời RSVP đã chính thức ra mắt trên Mây Hẹn.',
            ],
        ];

        foreach ($posts as $post) {
            NewsPost::updateOrCreate(['slug' => $post['slug']], $post);
        }
    }
}
