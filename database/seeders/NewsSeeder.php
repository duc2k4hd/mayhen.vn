<?php

namespace Database\Seeders;

use App\Models\InvitationTemplate;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', 'admin')->first();
        $imgBase = '/assets/client/images/news';

        $figure = static function (string $file, string $alt, string $caption, bool $isMobile = false) use ($imgBase): string {
            $class = $isMobile ? ' style="max-width:360px;margin:24px auto;text-align:center;"' : ' style="margin:24px 0;"';
            $imgStyle = $isMobile
                ? 'width:100%;height:auto;border-radius:18px;border:1px solid #E5DDD0;box-shadow:0 12px 30px rgba(31,29,26,0.12);'
                : 'width:100%;height:auto;border-radius:14px;border:1px solid #E5DDD0;box-shadow:0 10px 26px rgba(31,29,26,0.08);';

            return '<figure'.$class.'>'
                .'<img src="'.$imgBase.'/'.$file.'" alt="'.e($alt).'" loading="lazy" decoding="async" style="'.$imgStyle.'">'
                .'<figcaption style="margin-top:8px;font-size:13px;color:#6E655D;text-align:center;font-style:italic;">'.e($caption).'</figcaption>'
                .'</figure>';
        };

        $posts = [
            // =========================================================================
            // 1. BÀI GIỚI THIỆU NỀN TẢNG THIỆP CƯỚI MÂY HẸN
            // =========================================================================
            [
                'user_id' => $author?->id,
                'title' => 'Giới Thiệu Nền Tảng Thiệp Cưới Online Mây Hẹn — Chuẩn Mực Quiet Luxury Cho Ngày Trọng Đại',
                'slug' => 'gioi-thieu-nen-tang-thiep-cuoi-online-may-hen',
                'category_label' => 'Cập Nhật Hệ Thống',
                'summary' => 'Khám phá Mây Hẹn (mayhen.vn) — nền tảng thiết kế thiệp cưới online cao cấp theo phong cách Quiet Luxury, tích hợp Studio kéo-chỉnh trực quan thời gian thực, trình tạo thiệp tự động AI, quản lý khách mời RSVP và mừng cưới VietQR minh bạch.',
                'content' => '<p>Trong hành trình chuẩn bị cho ngày trọng đại, tấm thiệp cưới không chỉ là lời báo hỷ mà còn là ấn tượng đầu tiên về phong cách và sự trân trọng mà cô dâu chú rể dành cho từng vị khách quý. <strong>Mây Hẹn (mayhen.vn)</strong> ra đời với sứ mệnh mang đến trải nghiệm <strong>thiệp cưới điện tử chuẩn Quiet Luxury</strong> — sang trọng, tinh giản, giàu cảm xúc và dễ sử dụng trên mọi thiết bị.</p>'
                    .$figure('platform-home-hero.webp', 'Giao diện trang chủ nền tảng thiệp cưới online Mây Hẹn', 'Trang chủ Mây Hẹn với thẩm mỹ Quiet Luxury ấm áp, tối giản và thanh lịch.')
                    .'<h2>1. Kiến trúc 10 phần nội dung chuẩn mực trên mọi mẫu thiệp</h2>'
                    .'<p>Khác với các mẫu thiệp kéo-thả rời rạc dễ vỡ khung trên điện thoại, 100% mẫu thiệp cưới tại Mây Hẹn được xây dựng theo cấu trúc 10 phần liền mạch: từ màn mở phong bì nghệ thuật, trang bìa Hero, Câu chuyện tình yêu, Thông tin hai bên gia đình, Đồng hồ đếm ngược, Bản đồ chỉ đường, Lịch trình lễ tiệc, Bảng màu trang phục (Dresscode), Album 10 ảnh cưới đến Sổ ký tên, Xác nhận tham dự (RSVP) và Hộp quà mừng cưới VietQR.</p>'
                    .$figure('platform-home-templates.webp', 'Bộ sưu tập mẫu thiệp cưới nổi bật ngay trên trang chủ Mây Hẹn', 'Các thiết kế thiệp cưới từ cổ điển truyền thống đến hiện đại tối giản đều được sắp xếp khoa học.')
                    .$figure('platform-templates-catalog.webp', 'Kho mẫu thiệp cưới online đa dạng tại Mây Hẹn', 'Kho Mẫu Thiệp Cưới (/mau-thiep) hỗ trợ lọc nhanh theo phong cách và phân hạng gói.')
                    .'<h2>2. Studio Tạo Thiệp Trực Quan V2 & Trình Tạo Thiệp Tự Động AI</h2>'
                    .'<p>Cô dâu chú rể chỉ mất 5 phút để tự hoàn thiện tấm thiệp mang đậm dấu ấn cá nhân trong <a href="/tao-thiep">Studio Tạo Thiệp V2</a>: chỉnh chữ đến đâu thấy ngay trên khung xem trực tiếp đến đó, đổi tiêu điểm ảnh thông minh (Smart Focal Point), chọn nhạc nền cưới lãng mạn và kiểm tra độ hoàn thiện tự động.</p>'
                    .$figure('platform-editor-studio.webp', 'Giao diện Studio Tạo Thiệp Cưới Trực Quan V2 của Mây Hẹn', 'Studio Tạo Thiệp V2 đồng bộ thời gian thực 2 chiều, xem trực tiếp trên cả khung máy tính và điện thoại.')
                    .'<p>Bên cạnh đó, tính năng <a href="/tao-thiep-tu-dong">Tạo Thiệp Tự Động Bằng AI</a> cho phép hai bạn chọn nhanh phong cách và bảng màu yêu thích để hệ thống tự động dựng nên một bố cục thiệp độc bản chỉ trong vài chục giây.</p>'
                    .$figure('platform-ai-curated.webp', 'Công cụ Tạo Thiệp Cưới Tự Động Bằng AI trên Mây Hẹn', 'Trình tạo thiệp tự động AI giúp cặp đôi phối màu, bố cục và lời mời tinh tế không cần kỹ năng thiết kế.')
                    .'<h2>3. Quản lý khách mời, RSVP thông minh và Trung tâm Hướng dẫn chi tiết</h2>'
                    .'<p>Mây Hẹn không dừng lại ở việc tạo thiệp đẹp mà còn đồng hành xuyên suốt khâu tổ chức: cá nhân hóa tên từng vị khách trên đường dẫn gửi qua Zalo, gom nhóm khách theo hộ gia đình, thống kê khẩu vị ăn chay/mặn, sơ đồ bàn tiệc và toàn bộ kho <a href="/docs">Trung Tâm Hướng Dẫn (/docs)</a> trực quan.</p>'
                    .$figure('platform-home-features.webp', 'Các tính năng nổi bật giúp quản lý đám cưới nhẹ nhàng trên Mây Hẹn', 'Bộ công cụ hỗ trợ toàn diện từ gửi thiệp cá nhân hóa, RSVP đến quản lý tiền mừng VietQR.')
                    .$figure('platform-docs-hub.webp', 'Trung tâm hướng dẫn sử dụng chi tiết cho cô dâu chú rể trên Mây Hẹn', 'Hệ thống tài liệu hướng dẫn từng bước giúp bất kỳ ai cũng dễ dàng làm chủ toàn bộ tính năng.')
                    .'<h3>Bắt đầu trải nghiệm miễn phí ngay hôm nay</h3>'
                    .'<p>Bạn có thể truy cập <a href="/mau-thiep">Kho Mẫu Thiệp Cưới</a> hoặc mở ngay <a href="/tao-thiep">Studio Tạo Thiệp</a> để trải nghiệm miễn phí toàn bộ quy trình thiết kế trước khi xuất bản.</p>',
                'cover_image_url' => $imgBase.'/platform-home-hero.webp',
                'author_name' => 'Ban Phát Triển Mây Hẹn',
                'tags' => ['giới thiệu mây hẹn', 'thiệp cưới online', 'quiet luxury', 'nền tảng thiệp cưới'],
                'status' => 'published',
                'published_at' => now()->subHours(1),
                'reading_time_minutes' => 5,
                'view_count' => 342,
                'is_featured' => true,
                'send_email_on_publish' => false,
                'meta_title' => 'Giới Thiệu Nền Tảng Thiệp Cưới Online Mây Hẹn Chuẩn Quiet Luxury',
                'meta_description' => 'Tìm hiểu nền tảng thiệp cưới online Mây Hẹn (mayhen.vn) với 23+ mẫu thiệp nghệ thuật, Studio Tạo Thiệp V2, AI tạo thiệp tự động, quản lý RSVP và mừng cưới VietQR.',
            ],

            // =========================================================================
            // 2. BÀI BẢNG GIÁ THIỆP CƯỚI MÂY HẸN (ONLINE & IN THIỆP THẬT)
            // =========================================================================
            [
                'user_id' => $author?->id,
                'title' => 'Bảng Giá Thiệp Cưới Mây Hẹn Chi Tiết: Trọn Bộ Từ Thiệp Online 0đ – 199.000đ Đến Dịch Vụ In Thiệp Thật Cao Cấp',
                'slug' => 'bang-gia-thiep-cuoi-may-hen-online-va-in-thiep-giay-cao-cap',
                'category_label' => 'Ưu Đãi & Sự Kiện',
                'summary' => 'Công khai minh bạch bảng giá thiệp cưới online tại Mây Hẹn (Free 0đ, Premium 0đ, Plus 99.000đ, Pro 199.000đ — thanh toán 1 lần, không phí ẩn) cùng các gói dịch vụ in thiệp cưới giấy mỹ thuật cao cấp ép kim, tem sáp.',
                'content' => '<p>Một trong những điều các cặp đôi quan tâm nhất khi chuẩn bị cưới là bài toán chi phí sao cho vừa sang trọng vừa tối ưu ngân sách. Tại <strong>Mây Hẹn</strong>, toàn bộ <strong>bảng giá thiệp cưới online</strong> và <strong>dịch vụ in thiệp giấy cao cấp</strong> đều được niêm yết rõ ràng, minh bạch và không phát sinh chi phí ẩn.</p>'
                    .$figure('pricing-online-plans.webp', 'Bảng giá 4 gói thiệp cưới online tại Mây Hẹn', '4 gói thiệp cưới online linh hoạt từ 0đ đến 199.000đ, thanh toán 1 lần duy nhất cho cả đám cưới.')
                    .'<h2>1. Bảng giá 4 gói Thiệp Cưới Online tại Mây Hẹn</h2>'
                    .'<ul>'
                    .'<li><strong>Gói Trải Nghiệm (Free — 0đ):</strong> Tạo và xem thử ngay không cần đăng nhập, phù hợp cho cặp đôi muốn trải nghiệm trước giao diện và các mẫu thiệp cơ bản.</li>'
                    .'<li><strong>Gói Thành Viên (Premium — 0đ):</strong> Dành cho tài khoản đã đăng ký, mở khóa thêm nhiều mẫu thiệp thanh lịch, lưu trữ 30 ngày và chia sẻ miễn phí.</li>'
                    .'<li><strong>Gói Hạnh Phúc (Plus — 99.000đ):</strong> Thanh toán 1 lần, thời hạn lưu trữ 180 ngày, mở khóa kho mẫu nâng cao, nhạc nền cưới, cá nhân hóa tên khách mời và thống kê RSVP.</li>'
                    .'<li><strong>Gói Trọn Vẹn (Wedding Pro VIP — 199.000đ):</strong> Gói cao cấp nhất với thời hạn 730 ngày (2 năm), cho phép tạo tới 3 thiệp (tách riêng đường dẫn Nhà Trai Lễ Thành Hôn & Nhà Gái Lễ Vu Quy), mở khóa toàn bộ mẫu Hoàng Gia Pro, Tạo Thiệp Tự Động AI và <strong>đặc quyền xuất bản toàn bộ 138 mẫu Thiệp Sự Kiện & Mùa Lễ Hội</strong>.</li>'
                    .'</ul>'
                    .$figure('pricing-compare-table.webp', 'Bảng so sánh chi tiết quyền lợi giữa các gói thiệp cưới online Mây Hẹn', 'So sánh minh bạch từng tính năng giúp hai bạn chọn đúng gói phù hợp nhất với quy mô lễ cưới.')
                    .'<p>Đặc biệt, Mây Hẹn cam kết <strong>0% phí giao dịch tiền mừng cưới</strong> — mã VietQR chuyển thẳng 100% vào tài khoản ngân hàng chính chủ của cô dâu chú rể.</p>'
                    .$figure('pricing-faq.webp', 'Giải đáp các câu hỏi thường gặp về bảng giá thiệp cưới online', 'Mọi thắc mắc về chi phí, thời hạn lưu trữ và việc tách thiệp 2 bên nội ngoại đều được giải đáp rõ ràng.')
                    .'<h2>2. Bảng dịch vụ In Thiệp Cưới Giấy Cao Cấp (Thiệp Thật)</h2>'
                    .'<p>Bên cạnh thiệp điện tử gửi nhanh qua Zalo cho bạn bè và đồng nghiệp, rất nhiều gia đình vẫn cần những tấm thiệp giấy cầm tay trang trọng để kính biếu ông bà, họ hàng và khách quý lớn tuổi. <a href="/dich-vu">Dịch vụ In Thiệp Cưới Cao Cấp Mây Hẹn</a> đáp ứng trọn vẹn nhu cầu này với 3 dòng sản phẩm chủ lực:</p>'
                    .$figure('services-print-hero.webp', 'Trang giới thiệu dịch vụ in thiệp cưới cao cấp tại Mây Hẹn', 'Dịch vụ in thiệp cưới giấy mỹ thuật dệt gân, ép kim vàng cát và phong thư tem sáp thủ công.')
                    .'<ul>'
                    .'<li><strong>Dòng Tối Giản (Minimalist):</strong> In sắc nét trên phôi giấy mỹ thuật định lượng cao, bố cục hiện đại thanh thoát.</li>'
                    .'<li><strong>Dòng Ép Kim (Foil Stamping):</strong> Gia công ép kim ánh vàng/bạc nổi bật tên cô dâu chú rể và họa tiết lễ cưới.</li>'
                    .'<li><strong>Dòng Phong Thư Hoàng Gia (Wax Seal):</strong> Trọn bộ thiệp chính, phong bì nắp nhọn cao cấp và dấu sáp niêm phong thủ công sang trọng.</li>'
                    .'</ul>'
                    .$figure('services-print-packages.webp', 'Các dòng gói in thiệp cưới thật đa dạng chất liệu tại Mây Hẹn', 'Nhận in số lượng linh hoạt từ 50 thiệp, hỗ trợ in sẵn tên khách mời lên phong bì và duyệt bản mẫu trước khi in.')
                    .$figure('services-print-process.webp', 'Quy trình tư vấn, duyệt bản mẫu và in ấn thiệp cưới đồng bộ tại Mây Hẹn', 'Kết hợp song song thiệp in thật cho họ hàng và thiệp online đồng bộ giúp tiết kiệm tới 70% tổng ngân sách thiệp.')
                    .'<h3>Xem chi tiết và nhận tư vấn ngay</h3>'
                    .'<p>Truy cập <a href="/bang-gia">Bảng Giá Thiệp Cưới Online (/bang-gia)</a> hoặc <a href="/dich-vu">Dịch Vụ In Thiệp Cưới Cao Cấp (/dich-vu)</a> để lựa chọn phương án tối ưu nhất cho ngày vui của hai bạn.</p>',
                'cover_image_url' => $imgBase.'/pricing-online-plans.webp',
                'author_name' => 'Ban Phát Triển Mây Hẹn',
                'tags' => ['bảng giá thiệp cưới', 'in thiệp cưới cao cấp', 'gói pro mây hẹn', 'thiệp cưới 0đ'],
                'status' => 'published',
                'published_at' => now()->subHours(2),
                'reading_time_minutes' => 5,
                'view_count' => 289,
                'is_featured' => true,
                'send_email_on_publish' => false,
                'meta_title' => 'Bảng Giá Thiệp Cưới Mây Hẹn: Online 0đ - 199.000đ & In Thiệp Giấy Cao Cấp',
                'meta_description' => 'Cập nhật bảng giá thiệp cưới online Mây Hẹn (Free 0đ, Plus 99k, Pro 199k) và các gói dịch vụ in thiệp cưới giấy mỹ thuật ép kim, tem sáp cao cấp.',
            ],

            // =========================================================================
            // 3. BÀI DỊCH VỤ THEO TỪNG LOẠI THIỆP (THIỆP CƯỚI, IN THẬT & 10 DÒNG THIỆP SỰ KIỆN)
            // =========================================================================
            [
                'user_id' => $author?->id,
                'title' => 'Hệ Sinh Thái Dịch Vụ Theo Từng Loại Thiệp Tại Mây Hẹn: Thiệp Cưới Online, In Thiệp Thật & 10 Dòng Thiệp Sự Kiện',
                'slug' => 'he-sinh-thai-dich-vu-theo-tung-loai-thiep-tai-may-hen',
                'category_label' => 'Tính Năng Mới',
                'summary' => 'Tổng quan toàn bộ dịch vụ thiết kế & in ấn theo từng dòng thiệp tại Mây Hẹn: Thiệp Cưới Online 10 phần, Dịch Vụ In Thiệp Cưới Thủ Công và 138 mẫu Thiệp Sự Kiện & Mùa Lễ Hội Việt Nam với Studio 6 Tab chuyên biệt.',
                'content' => '<p>Không chỉ đồng hành cùng các cặp đôi trong ngày cưới, <strong>Mây Hẹn</strong> xây dựng hệ sinh thái dịch vụ trọn vẹn cho mọi khoảnh khắc đáng nhớ của gia đình và doanh nghiệp Việt Nam — chia thành 3 nhóm dịch vụ chuyên sâu, tách biệt rõ ràng về giao diện và công năng.</p>'
                    .'<h2>1. Dịch vụ Thiệp Cưới Online Cao Cấp (/mau-thiep & /tao-thiep)</h2>'
                    .'<p>Dành riêng cho lễ ăn hỏi, lễ vu quy, lễ thành hôn và tiệc báo hỷ. Hệ thống cung cấp hơn 23 thiết kế độc bản với đầy đủ hiệu ứng mở cửa 3D, đĩa than nhạc cưới, album ảnh Coverflow, lịch đếm ngược, bản đồ 2 nhà, sổ ký tên tay, xác nhận tham dự RSVP và quản lý danh sách khách mời chuyên sâu.</p>'
                    .$figure('platform-templates-grid.webp', 'Kho mẫu thiệp cưới online đa phong cách tại Mây Hẹn', 'Từng mẫu thiệp cưới được chăm chút từ cổ phục truyền thống Việt Nam tới phong cách châu Âu hiện đại.')
                    .'<h2>2. Dịch vụ In Thiệp Cưới Giấy Mỹ Thuật Thủ Công (/dich-vu)</h2>'
                    .'<p>Dành cho gia đình muốn sở hữu những tấm thiệp giấy sắc nét, giấy dệt gân cao cấp, ép kim vàng ánh kim và đóng dấu sáp thủ công. Mây Hẹn hỗ trợ in số lượng ít từ 50 thiệp, duyệt bản mẫu thực tế trước khi in hàng loạt và in đồng bộ tên từng khách mời lên phong bì.</p>'
                    .$figure('services-print-hero.webp', 'Dịch vụ in thiệp cưới cao cấp đồng bộ tại Mây Hẹn', 'Chất liệu giấy mỹ thuật nhập khẩu kết hợp kỹ thuật ép kim và niêm phong dấu sáp tỉ mỉ.')
                    .'<h2>3. Hệ sinh thái 10 dòng Thiệp Sự Kiện & Mùa Lễ Hội Việt Nam (/thiep-su-kien)</h2>'
                    .'<p>Tách biệt hoàn toàn với thiệp cưới, <a href="/thiep-su-kien">Kho Thiệp Sự Kiện & Lễ Hội Mây Hẹn</a> quy tụ <strong>138 mẫu thiệp thuần Việt</strong> theo cấu trúc <strong>1 trang tinh gọn kèm hiệu ứng mở phong bì 3 lớp mềm mại</strong>, phủ kín 10 dịp quan trọng nhất của người Việt:</p>'
                    .'<ul>'
                    .'<li><strong>Tết Nguyên Đán & Tiệc Tân Niên Khai Xuân</strong> (<a href="/thiep-su-kien/tet-nguyen-dan">/thiep-su-kien/tet-nguyen-dan</a>)</li>'
                    .'<li><strong>Tết Trung Thu & Đêm Hội Trăng Rằm</strong> (<a href="/thiep-su-kien/tet-trung-thu">/thiep-su-kien/tet-trung-thu</a>)</li>'
                    .'<li><strong>Tiệc Giáng Sinh & Đón Năm Mới</strong> (<a href="/thiep-su-kien/giang-sinh-nam-moi">/thiep-su-kien/giang-sinh-nam-moi</a>)</li>'
                    .'<li><strong>Đầy Tháng & Thôi Nôi Em Bé</strong> (<a href="/thiep-su-kien/day-thang-thoi-noi">/thiep-su-kien/day-thang-thoi-noi</a>)</li>'
                    .'<li><strong>Sinh Nhật Trẻ Em</strong> (<a href="/thiep-su-kien/sinh-nhat-tre-em">/thiep-su-kien/sinh-nhat-tre-em</a>) &amp; <strong>Sinh Nhật Người Lớn, Đại Lễ Mừng Thọ</strong> (<a href="/thiep-su-kien/sinh-nhat-nguoi-lon">/thiep-su-kien/sinh-nhat-nguoi-lon</a>)</li>'
                    .'<li><strong>Tân Gia & Mừng Nhà Mới</strong> (<a href="/thiep-su-kien/tan-gia-nha-moi">/thiep-su-kien/tan-gia-nha-moi</a>)</li>'
                    .'<li><strong>Tất Niên Công Ty & Lễ Khai Trương</strong> (<a href="/thiep-su-kien/tat-nien-khai-truong">/thiep-su-kien/tat-nien-khai-truong</a>)</li>'
                    .'<li><strong>Lễ Tốt Nghiệp & Vinh Danh Thành Tựu</strong> (<a href="/thiep-su-kien/tot-nghiep-thanh-tuu">/thiep-su-kien/tot-nghiep-thanh-tuu</a>)</li>'
                    .'<li><strong>Thiệp Chúc Mừng 8/3, 20/10, 20/11 & Kỷ Niệm Ngày Cưới</strong> (<a href="/thiep-su-kien/ngay-le-chuc-mung">/thiep-su-kien/ngay-le-chuc-mung</a>)</li>'
                    .'</ul>'
                    .$figure('events-hub-hero.webp', 'Trang chủ hệ sinh thái Thiệp Sự Kiện & Lễ Hội Mây Hẹn', '138 mẫu thiệp sự kiện thuần Việt phân chia khoa học theo 10 dịp lễ văn hóa Việt Nam.')
                    .$figure('events-cat-tet.webp', 'Trang danh mục Thiệp Chúc Tết Nguyên Đán & Tân Niên', 'Mỗi danh mục thiệp sự kiện đều sở hữu bố cục, bảng màu và kho lời mời mẫu riêng biệt.')
                    .$figure('events-cat-thoi-noi.webp', 'Trang danh mục Thiệp Đầy Tháng & Thôi Nôi Cho Bé Yêu', 'Hỗ trợ tải ảnh bé lên mặt thiệp, ghi tên ở nhà và bản đồ nhà hàng chỉ đường.')
                    .$figure('events-cat-tan-gia.webp', 'Trang danh mục Thiệp Mời Tân Gia & Mừng Nhà Mới', 'Tích hợp tọa độ Google Maps dẫn đường tới nhà mới và RSVP thống kê mâm cỗ.')
                    .'<h2>4. Trải nghiệm mở phong bì 3D & Studio Thiết Kế 6 Tab độc lập</h2>'
                    .'<p>Mỗi mẫu thiệp sự kiện đều sở hữu hiệu ứng rút thiệp ra khỏi phong bì giấy mềm mại như thật và đi kèm <strong>Studio Thiết Kế 6 Tab độc lập</strong>: tự đổi khuôn cắt mặt thiệp, 8 màu phong bì, 8 lớp lót hoa văn, chạm trực tiếp vào chữ để sửa, xem thử popup RSVP/VietQR và xuất danh sách đường dẫn cá nhân hóa hàng loạt.</p>'
                    .$figure('events-preview-card.webp', 'Trải nghiệm xem thử mẫu thiệp Tân Gia 1 trang mở phong bì 3D', 'Mặt thiệp 1 trang rõ ràng, tinh gọn kết hợp hiệu ứng mở phong bì vật lý 3 lớp tự nhiên.')
                    .$figure('events-studio-6tab.webp', 'Giao diện Studio Thiết Kế Thiệp Sự Kiện 6 Tab độc lập trên Mây Hẹn', 'Studio 6 Tab tách biệt hoàn toàn, cho phép thử thiết kế và phối màu miễn phí 100%.'),
                'cover_image_url' => $imgBase.'/events-hub-hero.webp',
                'author_name' => 'Ban Phát Triển Mây Hẹn',
                'tags' => ['dịch vụ thiệp cưới', 'thiệp sự kiện', 'in thiệp giấy', 'studio 6 tab'],
                'status' => 'published',
                'published_at' => now()->subHours(3),
                'reading_time_minutes' => 6,
                'view_count' => 264,
                'is_featured' => true,
                'send_email_on_publish' => false,
                'meta_title' => 'Hệ Sinh Thái Dịch Vụ Theo Từng Loại Thiệp Tại Mây Hẹn',
                'meta_description' => 'Khám phá trọn bộ dịch vụ theo từng loại thiệp tại Mây Hẹn: Thiệp Cưới Online, In Thiệp Cưới Cao Cấp và 10 dòng Thiệp Sự Kiện & Lễ Hội Việt Nam.',
            ],
        ];

        // =========================================================================
        // 4. 23 BÀI TIN TỨC RA MẮT RIÊNG TỪNG MẪU THIỆP CƯỚI (MỖI MẪU 4 ẢNH CHỤP THẬT)
        // =========================================================================
        $tierNames = [
            'pro' => 'Hoàng Gia (Wedding Pro)',
            'plus' => 'Hạnh Phúc (Plus)',
            'premium' => 'Thành Viên (Premium — 0đ)',
            'free' => 'Trải Nghiệm (Free — 0đ)',
        ];

        $categoryNames = [
            'luxury' => 'Sang Trọng Hoàng Gia (Quiet Luxury)',
            'romantic' => 'Lãng Mạn Nghệ Thuật',
            'traditional' => 'Truyền Thống Việt Nam',
            'modern' => 'Hiện Đại Tối Giản',
            'curated' => 'Thiết Kế Thông Minh Tự Phối AI',
        ];

        $templates = InvitationTemplate::where('is_active', true)->orderBy('id')->get();
        $hourOffset = 4;

        foreach ($templates as $tpl) {
            $tierLabel = $tierNames[$tpl->tier] ?? 'Cao Cấp';
            $styleLabel = $categoryNames[$tpl->category] ?? 'Nghệ Thuật Hiện Đại';
            $desc = $tpl->description ?: ('Mẫu thiệp cưới '.$tpl->name.' mang phong cách '.$styleLabel.' tinh tế chuẩn Mây Hẹn.');

            $content = '<p><strong>Mây Hẹn</strong> chính thức giới thiệu mẫu thiệp cưới online <strong>'.$tpl->name.'</strong> thuộc bộ sưu tập <strong>'.$styleLabel.'</strong> (Phân hạng <strong>'.$tierLabel.'</strong>). Đây là một trong những thiết kế được đội ngũ mỹ thuật chăm chút tỉ mỉ từng đường nét nhằm mang đến trải nghiệm báo hỷ tinh tế và giàu cảm xúc cho cô dâu chú rể.</p>'
                .$figure('tpl-'.$tpl->slug.'-hero.webp', 'Giao diện trang bìa mẫu thiệp cưới '.$tpl->name.' trên Mây Hẹn', 'Trang bìa mở đầu ấn tượng của mẫu thiệp cưới '.$tpl->name.' khi hiển thị trên màn hình lớn.')
                .'<h2>1. Điểm nhấn thiết kế nổi bật của mẫu thiệp '.$tpl->name.'</h2>'
                .'<p>'.e($desc).'</p>'
                .'<ul>'
                .'<li><strong>Phong cách chủ đạo:</strong> '.$styleLabel.' — bảng màu hài hòa, phông chữ tiếng Việt sắc nét không lỗi dấu.</li>'
                .'<li><strong>Bố cục tự co giãn 100%:</strong> Hiển thị chuẩn đẹp trên cả máy tính lẫn mọi dòng điện thoại khi khách mời mở trực tiếp từ Zalo, Messenger.</li>'
                .'<li><strong>Đồng bộ Studio Tạo Thiệp V2:</strong> Mọi thông tin tên cô dâu chú rể, ngày cưới, địa chỉ hai họ, ảnh cưới và nhạc nền đều thay đổi trực tiếp theo thời gian thực.</li>'
                .'</ul>'
                .$figure('tpl-'.$tpl->slug.'-mid.webp', 'Phần nội dung câu chuyện tình yêu và lịch đếm ngược của mẫu '.$tpl->name, 'Khu vực giới thiệu câu chuyện tình yêu, thông tin hai gia đình và lịch đếm ngược trên mẫu '.$tpl->name.'.')
                .'<h2>2. Trọn bộ 10 phần nội dung chuẩn mực tích hợp sẵn</h2>'
                .'<p>Khi sử dụng mẫu <strong>'.$tpl->name.'</strong>, hai bạn không cần phải thiết kế lại từ đầu mà đã có sẵn đầy đủ 10 phần nội dung chuẩn mực của một tấm thiệp cưới điện tử hiện đại: Màn mở thiệp ấn tượng, Trang bìa Hero, Lời ngỏ & Câu chuyện tình yêu, Thông tin gia đình hai họ, Lịch đếm ngược ngày cưới, Thông tin tiệc & Bản đồ Google Maps, Lịch trình lễ cưới, Bảng màu trang phục (Dresscode), Album 10 ảnh cưới, Sổ ký tên nghệ thuật, Xác nhận tham dự (RSVP) và Hộp quà mừng cưới VietQR 0% phí.</p>'
                .$figure('tpl-'.$tpl->slug.'-lower.webp', 'Phần sự kiện, album ảnh và tương tác RSVP trên mẫu thiệp '.$tpl->name, 'Các khu vực sự kiện, bản đồ chỉ đường, album ảnh và hộp quà mừng cưới được bố trí mạch lạc, dễ thao tác.')
                .'<h2>3. Trải nghiệm hiển thị trên điện thoại khi gửi qua Zalo</h2>'
                .'<p>Vì hơn 85% khách mời sẽ mở thiệp trực tiếp trên điện thoại thông qua Zalo, mẫu <strong>'.$tpl->name.'</strong> đã được tối ưu riêng cho màn hình dọc: cỡ chữ dễ đọc với cả người lớn tuổi, khoảng cách thoáng đãng và tốc độ tải cực nhanh nhờ 100% tài nguyên lưu trữ cục bộ.</p>'
                .$figure('tpl-'.$tpl->slug.'-mobile.webp', 'Giao diện điện thoại thực tế của mẫu thiệp cưới '.$tpl->name, 'Trải nghiệm thực tế trên màn hình điện thoại (430px) khi khách mời bấm mở link thiệp từ Zalo.', true)
                .'<h2>4. Xem thử trực tiếp & tự tạo thiệp '.$tpl->name.' miễn phí</h2>'
                .'<p>Hai bạn có thể trải nghiệm trực tiếp mẫu thiệp này ngay bây giờ mà chưa cần thanh toán:</p>'
                .'<ul>'
                .'<li><a href="/xem-thu/'.$tpl->slug.'">Xem Thử Bản Demo Đầy Đủ Mẫu '.$tpl->name.'</a></li>'
                .'<li><a href="/mau-thiep/'.$tpl->slug.'">Xem Chi Tiết Thông Số Mẫu '.$tpl->name.'</a></li>'
                .'<li><a href="/tao-thiep?template='.$tpl->slug.'">Mở Ngay Trong Studio Tạo Thiệp &amp; Thay Ảnh Của Hai Bạn</a></li>'
                .'</ul>';

            $posts[] = [
                'user_id' => $author?->id,
                'title' => 'Ra Mắt Mẫu Thiệp Cưới '.$tpl->name.' — Thiết Kế '.$styleLabel.' Chuẩn Mây Hẹn',
                'slug' => 'ra-mat-mau-thiep-cuoi-'.$tpl->slug,
                'category_label' => 'Mẫu Thiệp Mới',
                'summary' => 'Khám phá chi tiết mẫu thiệp cưới '.$tpl->name.' ('.$tierLabel.'): '.$desc,
                'content' => $content,
                'cover_image_url' => $imgBase.'/tpl-'.$tpl->slug.'-hero.webp',
                'author_name' => 'Đội Ngũ Thiết Kế Mây Hẹn',
                'tags' => ['mẫu thiệp mới', mb_strtolower($tpl->name), 'thiệp cưới '.$tpl->category, 'gói '.$tpl->tier],
                'status' => 'published',
                'published_at' => now()->subHours($hourOffset++),
                'reading_time_minutes' => 4,
                'view_count' => 80 + ($tpl->id * 7),
                'is_featured' => false,
                'send_email_on_publish' => false,
                'meta_title' => 'Ra Mắt Mẫu Thiệp Cưới '.$tpl->name.' | Mây Hẹn',
                'meta_description' => 'Chi tiết mẫu thiệp cưới online '.$tpl->name.' phong cách '.$styleLabel.' trên Mây Hẹn kèm ảnh thực tế, trọn bộ 10 phần nội dung, RSVP và VietQR.',
            ];
        }

        foreach ($posts as $post) {
            NewsPost::updateOrCreate(['slug' => $post['slug']], $post);
        }
    }
}
