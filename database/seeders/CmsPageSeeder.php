<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class CmsPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'dieu-khoan-dich-vu',
                'title' => 'Điều Khoản Dịch Vụ',
                'lead_summary' => 'Chào mừng bạn đến với Mây Hẹn (mayhen.vn). Xin vui lòng đọc kỹ các điều khoản sử dụng nền tảng tạo và quản lý thiệp cưới trực tuyến của chúng tôi.',
                'content' => '
<h2>1. Chấp thuận các điều khoản</h2>
<p>Bằng việc đăng ký tài khoản, khởi tạo hoặc sử dụng dịch vụ trên nền tảng Mây Hẹn (mayhen.vn), bạn đồng ý chịu sự ràng buộc bởi các Điều khoản dịch vụ này và các quy chế vận hành có liên quan.</p>

<h2>2. Dịch vụ cung cấp</h2>
<p>Mây Hẹn cung cấp công cụ số hóa thiệp cưới cao cấp, hỗ trợ các cặp đôi thiết kế thiệp, quản lý danh sách khách mời, thu nhận phản hồi RSVP và lưu giữ kỷ niệm sau hôn lễ.</p>

<h2>3. Quyền sở hữu trí tuệ</h2>
<p>Toàn bộ mẫu thiết kế thiệp cưới, mã nguồn, đồ họa, hiệu ứng chuyển động tem sáp và biểu tượng thương hiệu trên hệ thống đều thuộc quyền sở hữu trí tuệ độc quyền của Mây Hẹn. Người dùng được cấp quyền sử dụng cho mục đích đám cưới cá nhân phi thương mại.</p>

<h2>4. Bảo đảm quyền riêng tư và dữ liệu</h2>
<p>Chúng tôi cam kết bảo vệ dữ liệu khách mời của bạn, không bán hay chia sẻ số điện thoại, danh tính của bạn cho bất kỳ bên thứ ba nào cho mục đích quảng cáo rác.</p>
',
                'is_system' => true,
                'is_published' => true,
                'published_at' => now(),
                'meta_title' => 'Điều Khoản Dịch Vụ — Mây Hẹn',
                'meta_description' => 'Điều khoản sử dụng dịch vụ nền tảng thiệp cưới trực tuyến cao cấp Mây Hẹn.',
            ],
            [
                'slug' => 'chinh-sach-bao-mat',
                'title' => 'Chính Sách Bảo Mật Dữ Liệu Cá Nhân',
                'lead_summary' => 'Mây Hẹn cam kết bảo vệ toàn vẹn thông tin cá nhân và dữ liệu ngày cưới của các cặp đôi theo Nghị định 13/2023/NĐ-CP và Luật Bảo vệ dữ liệu cá nhân (Luật 91/2025/QH15).',
                'content' => '
<h2>1. Dữ liệu chúng tôi thu thập</h2>
<p>Chúng tôi chỉ thu thập các thông tin tối thiểu cần thiết để vận hành thiệp cưới: họ tên cô dâu chú rể, số điện thoại liên hệ, tên khách mời do người dùng tự nhập, và lời chúc mừng của quan khách.</p>

<h2>2. Mục đích xử lý dữ liệu</h2>
<p>Dữ liệu được xử lý nhằm hiển thị thiệp cưới cá nhân hóa, điểm danh khách mời tại sảnh cưới và gửi tin nhắn cảm ơn sau hôn lễ. Chúng tôi không sử dụng dữ liệu khách mời cho bất kỳ mục đích nào ngoài sự đồng thuận của chủ thiệp.</p>

<h2>3. Quyền của chủ thể dữ liệu (DSAR)</h2>
<p>Người dùng và khách mời có đầy đủ các quyền: quyền được biết, quyền đồng ý, quyền truy cập, quyền yêu cầu chỉnh sửa, và quyền yêu cầu xóa vĩnh viễn dữ liệu (Right to be Forgotten) thông qua Trung tâm Quyền riêng tư của Mây Hẹn.</p>

<h2>4. Biện pháp bảo vệ & Mã hóa</h2>
<p>Toàn bộ dữ liệu được truyền tải qua giao thức mã hóa TLS 1.3 và lưu trữ an toàn với chính sách hạn chế truy cập nghiêm ngặt của đội ngũ kỹ thuật.</p>
',
                'is_system' => true,
                'is_published' => true,
                'published_at' => now(),
                'meta_title' => 'Chính Sách Bảo Mật Dữ Liệu Cá Nhân — Mây Hẹn',
                'meta_description' => 'Chính sách bảo mật và bảo vệ dữ liệu cá nhân theo Luật 91/2025/QH15 tại Mây Hẹn.',
            ],
            [
                'slug' => 've-chung-toi',
                'title' => 'Về Mây Hẹn — Câu Chuyện Thương Hiệu',
                'lead_summary' => 'Mây Hẹn ra đời từ niềm tin rằng: Mỗi hôn lễ là một tác phẩm nghệ thuật vô giá, và cánh thiệp cưới là giai điệu mở đầu cho bản giao hưởng hạnh phúc.',
                'content' => '
<h2>1. Khởi nguồn từ sự trân quý</h2>
<p>Trong một thế giới vận động không ngừng, chúng tôi khao khát mang lại một khoảnh khắc tĩnh lặng và lắng đọng. Từng tấm thiệp tại Mây Hẹn được chăm chút như một bức thư tay hoàng gia, nơi tình yêu được tôn vinh bằng sự mực thước và tinh tế nhất.</p>

<h2>2. Triết lý Quiet Luxury</h2>
<p>Không ồn ào phô trương, sự xa xỉ của Mây Hẹn nằm ở tỷ lệ thị giác hoàn hảo, chất liệu giấy lụa kỹ thuật số, và những cái chạm êm ái trên màn hình điện thoại.</p>

<h2>3. Sứ mệnh của chúng tôi</h2>
<p>Đồng hành cùng hàng nghìn cặp đôi Việt Nam lưu giữ từng kỷ niệm, kết nối người thân và bạn bè một cách chu đáo, văn minh và trang nhã nhất.</p>
',
                'is_system' => true,
                'is_published' => true,
                'published_at' => now(),
                'meta_title' => 'Về Mây Hẹn — Câu Chuyện Thương Hiệu & Triết Lý Quiet Luxury',
                'meta_description' => 'Khám phá câu chuyện thương hiệu và sứ mệnh của Mây Hẹn trong hành trình đồng hành cùng ngày cưới của bạn.',
            ],
        ];

        foreach ($pages as $pageData) {
            CmsPage::updateOrCreate(['slug' => $pageData['slug']], $pageData);
        }
    }
}
