<?php

namespace Database\Seeders;

use App\Models\InvitationTemplate;
use App\Models\NewsPost;
use App\Models\User;
use App\Services\TemplateLaunchNewsSpecs;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', 'admin')->first();
        $imgBase = '/assets/client/images/news';

        $figure = static function (string $file, string $alt, string $caption, bool $isMobile = false) use ($imgBase): string {
            $class = $isMobile ? ' style="max-width:360px;margin:26px auto;text-align:center;"' : ' style="margin:26px 0;"';
            $imgStyle = $isMobile
                ? 'width:100%;height:auto;border-radius:18px;border:1px solid #E5DDD0;box-shadow:0 12px 30px rgba(31,29,26,0.12);'
                : 'width:100%;height:auto;border-radius:14px;border:1px solid #E5DDD0;box-shadow:0 10px 26px rgba(31,29,26,0.08);';

            return '<figure'.$class.'>'
                .'<img src="'.$imgBase.'/'.$file.'" alt="'.e($alt).'" loading="lazy" decoding="async" style="'.$imgStyle.'">'
                .'<figcaption style="margin-top:8px;font-size:13px;color:#6E655D;text-align:center;font-style:italic;">'.e($caption).'</figcaption>'
                .'</figure>';
        };

        $tableStyle = 'width:100%;border-collapse:collapse;margin:20px 0 24px;font-size:14.5px;border:1px solid #E5DDD0;';
        $thStyle = 'background:#FAF6EE;color:#1F1D1A;font-weight:700;padding:12px 14px;border:1px solid #E5DDD0;text-align:left;';
        $tdStyle = 'padding:12px 14px;border:1px solid #E5DDD0;color:#3D362F;vertical-align:top;line-height:1.6;';

        $posts = [
            // =========================================================================
            // 1. BÀI CHUẨN SEO VỀ NỀN TẢNG THIỆP CƯỚI MÂY HẸN
            // =========================================================================
            [
                'user_id' => $author?->id,
                'title' => 'Giới Thiệu Nền Tảng Thiệp Cưới Online Mây Hẹn — Chuẩn Mực Quiet Luxury Cho Ngày Trọng Đại',
                'slug' => 'gioi-thieu-nen-tang-thiep-cuoi-online-may-hen',
                'category_label' => 'Cập Nhật Hệ Thống',
                'summary' => 'Tìm hiểu toàn diện nền tảng thiệp cưới online Mây Hẹn (mayhen.vn): thẩm mỹ Quiet Luxury tinh tế, 23+ mẫu thiệp cưới 10 phần chuẩn mực, Studio Tạo Thiệp V2 đồng bộ thời gian thực, AI tạo thiệp tự động, quản lý khách mời RSVP, mừng cưới VietQR 0% phí và dịch vụ in thiệp giấy cao cấp.',
                'content' => '<p>Trong xu hướng tổ chức đám cưới hiện đại tại Việt Nam, <strong>thiệp cưới online</strong> đã vượt xa vai trò một đường dẫn thông báo ngày giờ đơn thuần. Cô dâu chú rể ngày nay tìm kiếm một tấm thiệp vừa <strong>sang trọng, có gu thẩm mỹ riêng</strong>, vừa tích hợp đủ công cụ thông minh để gửi mời qua Zalo, thống kê bàn tiệc chính xác và lưu giữ lời chúc mãi về sau. Đó chính là lý do <strong>Mây Hẹn (<a href="/">mayhen.vn</a>)</strong> được xây dựng theo triết lý <em>Quiet Luxury</em> — tinh giản, ấm áp, lãng mạn và chỉn chu đến từng điểm chạm.</p>'
                    .$figure('platform-home-hero.webp', 'Giao diện trang chủ nền tảng thiệp cưới online Mây Hẹn phong cách Quiet Luxury', 'Trang chủ Mây Hẹn với bảng màu kem lụa, vàng champagne và bố cục tinh giản chuẩn Quiet Luxury.')
                    .'<h2>1. Mây Hẹn là gì? Vì sao các cặp đôi Việt lựa chọn Mây Hẹn?</h2>'
                    .'<p><strong>Mây Hẹn</strong> là hệ sinh thái thiết kế thiệp cưới điện tử, in thiệp giấy cao cấp và thiệp sự kiện gia đình được tối ưu riêng cho phong tục cưới hỏi Việt Nam. Khác với các công cụ kéo-thả tự do kiểu cũ thường khiến chữ bị xô lệch khi mở trên màn hình điện thoại nhỏ, toàn bộ nền tảng Mây Hẹn được lập trình bằng kiến trúc HTML/CSS/JavaScript thuần tốc độ cao, <strong>100% tài nguyên lưu trữ cục bộ</strong> giúp thiệp mở tức thì trên Zalo, Facebook, Messenger và Safari mà không giật lag hay chèn quảng cáo bên thứ ba.</p>'
                    .'<table style="'.$tableStyle.'">'
                    .'<thead><tr><th style="'.$thStyle.'">Hạng mục cốt lõi</th><th style="'.$thStyle.'">Giải pháp tại Mây Hẹn (mayhen.vn)</th></tr></thead>'
                    .'<tbody>'
                    .'<tr><td style="'.$tdStyle.'"><strong>Kho mẫu thiệp cưới</strong></td><td style="'.$tdStyle.'">23+ thiết kế độc bản từ Cổ phục Việt, Đỏ Son Song Hỷ, Sài Gòn Xưa, Xứ Huế đến Minimalist Hàn Quốc &amp; Luxury Châu Âu.</td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>Cấu trúc nội dung</strong></td><td style="'.$tdStyle.'">Chuẩn hóa 10 phần đầy đủ: Màn mở thiệp 3D, Hero, Chuyện tình yêu, Hai bên gia đình, Đếm ngược &amp; Lịch tháng, Bản đồ 2 nhà, Lịch trình, Dresscode, Album 10 ảnh, Ký tên – RSVP – VietQR.</td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>Công cụ thiết kế</strong></td><td style="'.$tdStyle.'">Studio Tạo Thiệp V2 đồng bộ thời gian thực 2 chiều + Công cụ Tạo Thiệp Tự Động AI dựng bố cục theo gu riêng.</td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>Quản lý khách mời</strong></td><td style="'.$tdStyle.'">Tạo link cá nhân hóa tên từng khách, gom nhóm hộ gia đình, ghi nhận khẩu vị chay/mặn, sơ đồ bàn tiệc &amp; xuất Excel.</td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>Tiền mừng cưới VietQR</strong></td><td style="'.$tdStyle.'">Chuyển thẳng 100% vào tài khoản chính chủ của cô dâu chú rể — Mây Hẹn không giữ tiền và thu 0% phí giao dịch.</td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>Hệ sinh thái mở rộng</strong></td><td style="'.$tdStyle.'">Tích hợp thêm Dịch vụ In Thiệp Cưới Giấy Cao Cấp (/dich-vu) và 138 mẫu Thiệp Sự Kiện &amp; Lễ Hội (/thiep-su-kien).</td></tr>'
                    .'</tbody></table>'
                    .'<h2>2. Kho 23+ mẫu thiệp cưới nghệ thuật được thiết kế riêng cho đám cưới Việt</h2>'
                    .'<p>Tại <a href="/mau-thiep">Kho Mẫu Thiệp Cưới Mây Hẹn (/mau-thiep)</a>, mỗi mẫu thiệp không chỉ khác nhau về màu sắc mà còn sở hữu <strong>ngôn ngữ đồ họa và hiệu ứng tương tác riêng biệt</strong>: từ màn mở 12 dải lụa rút sợi thủ công, tờ Nhật Báo Sài Gòn Xưa kèm xe Vespa cổ, Bản đồ cửa tre 2 cánh xứ Huế cho tới Máy đĩa than Vinyl xoay 360° đồng bộ nhạc cưới.</p>'
                    .$figure('platform-home-templates.webp', 'Bộ sưu tập mẫu thiệp cưới nổi bật trên trang chủ Mây Hẹn', 'Các thiết kế thiệp cưới được sắp xếp khoa học từ phân hạng Hoàng Gia Pro đến Miễn Phí để cặp đôi dễ so sánh.')
                    .$figure('platform-templates-catalog.webp', 'Trang Kho Mẫu Thiệp Cưới Online với bộ lọc phong cách và phân hạng', 'Người dùng có thể xem thử bản demo hoàn chỉnh của từng mẫu trên cả máy tính và điện thoại trước khi tạo.')
                    .'<h2>3. Studio Tạo Thiệp Trực Quan V2 & Công nghệ Tạo Thiệp Tự Động Bằng AI</h2>'
                    .'<p>Để cô dâu chú rể không cần biết thiết kế đồ họa vẫn tạo được tấm thiệp đẹp như studio chuyên nghiệp, Mây Hẹn phát triển hai công cụ song hành:</p>'
                    .'<h3>3.1. Studio Tạo Thiệp V2 đồng bộ thời gian thực (/tao-thiep)</h3>'
                    .'<p>Mọi thao tác sửa họ tên hai họ, đổi ngày âm – dương lịch, tải ảnh cưới với tính năng căn tiêu điểm thông minh (<em>Smart Focal Point</em>), chọn nhạc nền cưới hay phối bảng màu Dresscode đều hiển thị ngay lập tức trên khung xem trước bên cạnh. Hai bạn còn có thể chuyển đổi qua lại giữa hơn 23 mẫu thiệp mà không bị mất dữ liệu đã nhập.</p>'
                    .$figure('platform-editor-studio.webp', 'Giao diện Studio Tạo Thiệp Cưới Trực Quan V2 của Mây Hẹn', 'Studio V2 chia bảng chỉnh sửa trực quan bên trái và bản xem trực tiếp thời gian thực bên phải.')
                    .'<h3>3.2. Trình Tạo Thiệp Cưới Tự Động Bằng AI (/tao-thiep-tu-dong)</h3>'
                    .'<p>Với những cặp đôi bận rộn, chỉ cần nhập thông tin cơ bản và chọn phong cách yêu thích tại <a href="/tao-thiep-tu-dong">Tạo Thiệp Tự Động AI</a>, hệ thống sẽ tự động gợi ý lời ngỏ, phối bảng màu hài hòa và trình diễn quá trình dựng từng phần tử của tấm thiệp ngay trước mắt bạn.</p>'
                    .$figure('platform-ai-curated.webp', 'Công cụ Tạo Thiệp Cưới Tự Động Bằng AI trên Mây Hẹn', 'Trình tạo thiệp tự động AI giúp rút ngắn thời gian hoàn thiện một tấm thiệp độc bản xuống chỉ còn vài phút.')
                    .'<h2>4. Bộ công cụ quản lý khách mời, Cổng Gia Đình & Trung tâm Hướng dẫn</h2>'
                    .'<p>Một điểm mạnh hiếm có của Mây Hẹn là hệ thống hậu cần ngày cưới đi kèm từng tấm thiệp: tách riêng 2 đường dẫn cho <strong>Lễ Vu Quy (Nhà Gái)</strong> và <strong>Lễ Thành Hôn (Nhà Trai)</strong>, cấp quyền Cổng Gia Đình để ba mẹ hoặc người thân phụ cập nhật danh sách khách mời, theo dõi lượt mở thiệp và chốt số lượng mâm cỗ chính xác qua RSVP.</p>'
                    .$figure('platform-home-features.webp', 'Hệ thống tính năng quản lý khách mời, RSVP và mừng cưới VietQR trên Mây Hẹn', 'Tối ưu toàn bộ quy trình gửi thiệp qua Zalo, quản lý phản hồi khách mời và kiểm soát ngân sách tiệc.')
                    .$figure('platform-docs-hub.webp', 'Trung tâm Hướng dẫn Sử dụng (/docs) chi tiết cho cô dâu chú rể trên Mây Hẹn', 'Kho tài liệu hướng dẫn từng bước kết hợp hơn 380 bài Cẩm Nang Cưới thực tế tại /cam-nang-cuoi.')
                    .'<h2>5. Câu hỏi thường gặp về nền tảng thiệp cưới Mây Hẹn</h2>'
                    .'<h3>Mây Hẹn có cho phép tạo thử thiệp cưới miễn phí không?</h3>'
                    .'<p>Có 100%. Bạn có thể vào ngay <a href="/tao-thiep">Studio Tạo Thiệp</a> để trải nghiệm miễn phí (gói Free 0đ không cần đăng nhập hoặc Premium 0đ khi tạo tài khoản) trước khi quyết định nâng cấp lên gói Plus hoặc Pro.</p>'
                    .'<h3>Quyền riêng tư của cô dâu chú rể và khách mời được bảo vệ ra sao?</h3>'
                    .'<p>Toàn bộ đường dẫn thiệp cưới riêng tư (<code>/thiep/{slug}</code>) đều được gắn chỉ thị <code>noindex, nofollow</code> để không bị công cụ tìm kiếm lập chỉ mục công khai, tuân thủ chặt chẽ quy định bảo vệ dữ liệu cá nhân tại Việt Nam.</p>'
                    .'<h3>Khách mời lớn tuổi không rành công nghệ có xem được thiệp Mây Hẹn không?</h3>'
                    .'<p>Rất dễ xem. Khách chỉ cần chạm vào đường dẫn gửi trên Zalo là thiệp mở trực tiếp bằng trình duyệt có sẵn, không cần cài ứng dụng hay đăng nhập, cỡ chữ lớn và có sẵn nút chỉ đường Google Maps 1 chạm.</p>',
                'cover_image_url' => $imgBase.'/platform-home-hero.webp',
                'author_name' => 'Ban Phát Triển Mây Hẹn',
                'tags' => ['giới thiệu mây hẹn', 'thiệp cưới online', 'quiet luxury', 'nền tảng thiệp cưới'],
                'status' => 'published',
                'published_at' => now()->subHours(1),
                'reading_time_minutes' => 7,
                'view_count' => 385,
                'is_featured' => true,
                'send_email_on_publish' => false,
                'meta_title' => 'Giới Thiệu Nền Tảng Thiệp Cưới Online Mây Hẹn Chuẩn Quiet Luxury',
                'meta_description' => 'Tìm hiểu toàn diện nền tảng thiệp cưới online Mây Hẹn (mayhen.vn): 23+ mẫu thiệp nghệ thuật, Studio V2, AI tạo thiệp, RSVP, VietQR 0% phí và in thiệp giấy cao cấp.',
            ],

            // =========================================================================
            // 2. BÀI CHUẨN SEO VỀ BẢNG GIÁ (ONLINE & IN THIỆP THẬT)
            // =========================================================================
            [
                'user_id' => $author?->id,
                'title' => 'Bảng Giá Thiệp Cưới Mây Hẹn Chi Tiết: Trọn Bộ Từ Thiệp Online 0đ – 199.000đ Đến Dịch Vụ In Thiệp Thật Cao Cấp',
                'slug' => 'bang-gia-thiep-cuoi-may-hen-online-va-in-thiep-giay-cao-cap',
                'category_label' => 'Ưu Đãi & Sự Kiện',
                'summary' => 'Tổng hợp đầy đủ và minh bạch bảng giá thiệp cưới online Mây Hẹn (Free 0đ, Premium 0đ, Plus 99.000đ, Pro 199.000đ — trả 1 lần, không phí ẩn) kết hợp bảng dịch vụ in thiệp cưới giấy mỹ thuật cao cấp ép kim, tem sáp và bí quyết phối hợp tiết kiệm 70% chi phí.',
                'content' => '<p>Chi phí in thiệp cưới truyền thống cho một đám cưới từ 300 đến 500 khách thường dao động từ 3.000.000đ đến 6.000.000đ, chưa kể công sức viết tay và đi gửi từng nhà. Hiểu rõ nỗi lo ngân sách của các cặp đôi trẻ, <strong>Mây Hẹn</strong> xây dựng hệ thống <strong>bảng giá thiệp cưới minh bạch</strong> kết hợp linh hoạt giữa <strong>4 gói Thiệp Cưới Online (chỉ từ 0đ đến 199.000đ)</strong> và <strong>Dịch vụ In Thiệp Cưới Giấy Cao Cấp</strong> nhận in từ số lượng ít.</p>'
                    .$figure('pricing-online-plans.webp', 'Bảng giá 4 gói thiệp cưới online công khai minh bạch tại Mây Hẹn', '4 phân hạng gói thiệp cưới online rõ ràng tại /bang-gia, thanh toán một lần duy nhất cho cả lễ cưới.')
                    .'<h2>1. Chi tiết bảng giá 4 gói Thiệp Cưới Online tại Mây Hẹn (/bang-gia)</h2>'
                    .'<p>Toàn bộ các gói thiệp cưới điện tử tại <a href="/bang-gia">Bảng Giá Mây Hẹn</a> đều áp dụng nguyên tắc <strong>thanh toán 1 lần duy nhất</strong>, không thu phí duy trì hàng tháng, không gắn quảng cáo và cam kết <strong>0% phí giao dịch tiền mừng cưới VietQR</strong>:</p>'
                    .'<table style="'.$tableStyle.'">'
                    .'<thead><tr><th style="'.$thStyle.'">Tên gói</th><th style="'.$thStyle.'">Chi phí trọn gói</th><th style="'.$thStyle.'">Thời hạn lưu trữ</th><th style="'.$thStyle.'">Số thiệp</th><th style="'.$thStyle.'">Quyền lợi nổi bật</th></tr></thead>'
                    .'<tbody>'
                    .'<tr><td style="'.$tdStyle.'"><strong>Trải Nghiệm (Free)</strong></td><td style="'.$tdStyle.'"><strong>0đ</strong></td><td style="'.$tdStyle.'">7 ngày</td><td style="'.$tdStyle.'">1 thiệp</td><td style="'.$tdStyle.'">Tạo và xem thử ngay không cần đăng nhập, chia sẻ link không giới hạn lượt xem.</td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>Thành Viên (Premium)</strong></td><td style="'.$tdStyle.'"><strong>0đ</strong></td><td style="'.$tdStyle.'">30 ngày</td><td style="'.$tdStyle.'">1 thiệp</td><td style="'.$tdStyle.'">Mở khóa toàn bộ mẫu nhóm Thành Viên (Sài Gòn Xưa, Xứ Huế, Đĩa Than Vinyl, Túi Gấm...), lưu nháp trên tài khoản.</td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>Hạnh Phúc (Plus)</strong></td><td style="'.$tdStyle.'"><strong>99.000đ</strong></td><td style="'.$tdStyle.'">180 ngày (6 tháng)</td><td style="'.$tdStyle.'">1 thiệp</td><td style="'.$tdStyle.'">Mở khóa kho nhạc nền cưới, cá nhân hóa tên khách mời hàng loạt, quản lý RSVP &amp; sơ đồ bàn tiệc.</td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>Trọn Vẹn (Wedding Pro VIP)</strong></td><td style="'.$tdStyle.'"><strong>199.000đ</strong></td><td style="'.$tdStyle.'">730 ngày (2 năm)</td><td style="'.$tdStyle.'">Tối đa 3 thiệp</td><td style="'.$tdStyle.'">Tách riêng 2 link Nhà Trai (Lễ Thành Hôn) &amp; Nhà Gái (Lễ Vu Quy), mở khóa mẫu Hoàng Gia Pro, AI Tạo Thiệp Tự Động &amp; <strong>xuất bản trọn bộ 138 mẫu Thiệp Sự Kiện &amp; Lễ Hội</strong>.</td></tr>'
                    .'</tbody></table>'
                    .$figure('pricing-compare-table.webp', 'Bảng so sánh quyền lợi chi tiết giữa 4 gói thiệp cưới online Mây Hẹn', 'Từng tính năng từ album ảnh, nhạc nền, tách thiệp 2 nhà đến quyền lợi thiệp sự kiện đều được liệt kê rõ ràng.')
                    .'<p>Với mức giá <strong>199.000đ cho Gói Pro VIP</strong>, hai bạn không chỉ có trọn vẹn bộ thiệp cưới cao cấp nhất dùng trong 2 năm mà còn được tặng kèm đặc quyền xuất bản toàn bộ kho <a href="/thiep-su-kien">Thiệp Sự Kiện Gia Đình &amp; Mùa Lễ Hội</a> (chúc Tết, Trung Thu, Tân Gia, Đầy Tháng, Thôi Nôi, Sinh Nhật...).</p>'
                    .$figure('pricing-faq.webp', 'Phần giải đáp câu hỏi thường gặp về bảng giá và chính sách thanh toán Mây Hẹn', 'Thanh toán nhanh qua chuyển khoản VietQR tự động kích hoạt gói chỉ trong vài giây.')
                    .'<h2>2. Bảng dịch vụ In Thiệp Cưới Thật Cao Cấp (/dich-vu)</h2>'
                    .'<p>Nhiều cặp đôi vẫn muốn chuẩn bị từ 50 đến 150 bộ thiệp giấy thật thật đẹp để trực tiếp trao tay ông bà, cô bác lớn tuổi, thông gia và khách quý quan trọng. <a href="/dich-vu">Dịch Vụ In Thiệp Cưới Cao Cấp Mây Hẹn</a> cung cấp 3 phân khúc gia công chuẩn mực:</p>'
                    .$figure('services-print-hero.webp', 'Dịch vụ in thiệp cưới giấy mỹ thuật cao cấp đồng bộ tại Mây Hẹn', 'Phôi giấy mỹ thuật nhập khẩu dệt gân kết hợp kỹ thuật ép kim và dấu sáp thủ công.')
                    .'<h3>2.1. Dòng In Thiệp Cưới Tối Giản (Minimalist)</h3>'
                    .'<p>Sử dụng phôi giấy mỹ thuật định lượng dày dặn (300–350gsm), in kỹ thuật số sắc nét từng nét chữ tiếng Việt, đi kèm phong bì nắp nhọn đồng màu. Phù hợp với cặp đôi yêu phong cách hiện đại, gọn gàng và tối ưu ngân sách.</p>'
                    .'<h3>2.2. Dòng In Thiệp Cưới Ép Kim (Foil Stamping)</h3>'
                    .'<p>Điểm nhấn là kỹ thuật ép nhũ vàng cát, vàng hồng hoặc ánh bạc trực tiếp lên tên cô dâu chú rể và họa tiết khung viền, tạo hiệu ứng bắt sáng sang trọng khi cầm trên tay.</p>'
                    .'<h3>2.3. Dòng Phong Thư Hoàng Gia Đóng Dấu Sáp (Wax Seal)</h3>'
                    .'<p>Trọn bộ gồm thiệp mời chính, thẻ thông tin lịch trình/bản đồ, phong bì giấy mỹ thuật lót hoa văn bên trong và <strong>tem sáp niêm phong thủ công</strong> khắc chữ cái đầu tên hai bạn.</p>'
                    .$figure('services-print-packages.webp', 'Chi tiết các dòng gói in thiệp cưới thật tại Mây Hẹn', 'Mây Hẹn nhận in linh hoạt từ số lượng ít (từ 50 thiệp) và hỗ trợ in sẵn tên từng khách mời lên phong bì.')
                    .$figure('services-print-process.webp', 'Quy trình đặt in thiệp cưới và duyệt bản mẫu thực tế tại Mây Hẹn', 'Khách hàng luôn được duyệt bản maket đồ họa và kiểm tra chất giấy trước khi gia công hàng loạt.')
                    .'<h2>3. Mẹo kết hợp Thiệp Online + In Thiệp Thật tiết kiệm 70% chi phí</h2>'
                    .'<p>Thay vì in 500 bộ thiệp giấy tốn 5–6 triệu đồng, phương án thông minh nhất được hơn 80% cặp đôi trên Mây Hẹn áp dụng là: <strong>đặt in 80–120 bộ thiệp giấy cao cấp</strong> để trao tận tay họ hàng lớn tuổi và đối tác quan trọng, đồng thời sử dụng <strong>Gói Wedding Pro (199.000đ)</strong> để gửi thiệp cưới online cá nhân hóa qua Zalo cho toàn bộ 350–400 bạn bè, đồng nghiệp và khách ở xa. Tổng chi phí giảm hơn một nửa nhưng sự chỉn chu lại tăng gấp đôi.</p>'
                    .'<h2>4. Câu hỏi thường gặp về bảng giá & in thiệp cưới Mây Hẹn</h2>'
                    .'<h3>Nên đặt in thiệp cưới thật trước ngày cưới bao lâu?</h3>'
                    .'<p>Thời điểm lý tưởng nhất là trước ngày cưới <strong>1 đến 2 tháng</strong> để thoải mái duyệt maket, in thử bản mẫu, gia công ép kim/tem sáp và đi phát thiệp trước lễ cưới 2–3 tuần.</p>'
                    .'<h3>Giá in thiệp giấy đã bao gồm phong bì và in tên khách chưa?</h3>'
                    .'<p>Tất cả các gói in tại Mây Hẹn đều đã bao gồm trọn bộ thiệp chính, phong bì nắp nhọn và công hoàn thiện; đồng thời hỗ trợ in trực tiếp danh sách tên khách mời lên từng phong bì theo file của hai bạn.</p>'
                    .'<h3>Thanh toán nâng cấp gói Online xong bao lâu thì được mở khóa?</h3>'
                    .'<p>Hệ thống đối soát chuyển khoản VietQR tự động kích hoạt gói Plus hoặc Pro chỉ sau <strong>5 đến 15 giây</strong> kể từ khi chuyển khoản thành công.</p>',
                'cover_image_url' => $imgBase.'/pricing-online-plans.webp',
                'author_name' => 'Ban Phát Triển Mây Hẹn',
                'tags' => ['bảng giá thiệp cưới', 'in thiệp cưới cao cấp', 'gói pro mây hẹn', 'thiệp cưới 0đ'],
                'status' => 'published',
                'published_at' => now()->subHours(2),
                'reading_time_minutes' => 7,
                'view_count' => 312,
                'is_featured' => true,
                'send_email_on_publish' => false,
                'meta_title' => 'Bảng Giá Thiệp Cưới Mây Hẹn: Online 0đ - 199.000đ & In Thiệp Giấy Cao Cấp',
                'meta_description' => 'Chi tiết bảng giá thiệp cưới online Mây Hẹn (Free 0đ, Premium 0đ, Plus 99k, Pro 199k) và các gói dịch vụ in thiệp cưới thật ép kim, tem sáp cao cấp.',
            ],

            // =========================================================================
            // 3. BÀI CHUẨN SEO VỀ DỊCH VỤ THEO TỪNG LOẠI THIỆP
            // =========================================================================
            [
                'user_id' => $author?->id,
                'title' => 'Hệ Sinh Thái Dịch Vụ Theo Từng Loại Thiệp Tại Mây Hẹn: Thiệp Cưới Online, In Thiệp Thật & 10 Dòng Thiệp Sự Kiện',
                'slug' => 'he-sinh-thai-dich-vu-theo-tung-loai-thiep-tai-may-hen',
                'category_label' => 'Tính Năng Mới',
                'summary' => 'Khám phá trọn bộ dịch vụ chuyên biệt theo từng loại thiệp tại Mây Hẹn: Thiệp Cưới Online 10 phần, Dịch vụ In Thiệp Giấy Thủ Công và 10 dòng Thiệp Sự Kiện & Mùa Lễ Hội Việt Nam (138 mẫu thuần Việt + Studio 6 Tab độc lập).',
                'content' => '<p>Mỗi cột mốc trong cuộc đời đều mang một sắc thái cảm xúc riêng: lễ cưới cần sự trang trọng sâu lắng, tiệc thôi nôi cần nét ngọt ngào rộn ràng, lễ tân gia cần bản đồ chỉ đường rõ ràng, còn tiệc tất niên doanh nghiệp đòi hỏi sự chuyên nghiệp và tốc độ. Thay vì gộp chung tất cả vào một khuôn mẫu nhàm chán, <strong>Mây Hẹn</strong> tách biệt thành <strong>3 hệ dịch vụ chuyên sâu theo từng loại thiệp</strong>, mỗi loại có giao diện, công cụ chỉnh sửa và tiện ích riêng.</p>'
                    .'<h2>1. Dịch vụ Thiệp Cưới Online Cao Cấp (/mau-thiep & /tao-thiep)</h2>'
                    .'<p>Là dịch vụ trọng tâm làm nên thương hiệu Mây Hẹn, dành riêng cho Lễ Ăn Hỏi, Lễ Vu Quy, Lễ Thành Hôn và Tiệc Báo Hỷ. Cô dâu chú rể được sử dụng hơn 23 thiết kế độc quyền theo cấu trúc 10 phần liền mạch, kết hợp nhạc cưới, album ảnh 3D Coverflow, sổ ký tên tay HTML5, xác nhận tham dự RSVP theo từng lễ và Cổng Gia Đình hỗ trợ quản lý khách mời hai bên nội ngoại.</p>'
                    .$figure('platform-templates-grid.webp', 'Kho mẫu thiệp cưới online đa phong cách tại Mây Hẹn', 'Hơn 23 mẫu thiệp cưới nghệ thuật chia theo phong cách Hoàng Gia, Truyền Thống, Lãng Mạn và Hiện Đại.')
                    .'<h2>2. Dịch vụ In Thiệp Cưới Giấy Mỹ Thuật Thủ Công (/dich-vu)</h2>'
                    .'<p>Dành cho các gia đình yêu cảm giác cầm nắm tờ giấy mỹ thuật dệt gân thật trên tay. Mây Hẹn cung cấp giải pháp đồng bộ từ thiết kế maket, in thử kiểm tra màu sắc, gia công ép kim – dập nổi – đóng tem sáp thủ công cho đến giao tận nơi trên toàn quốc, nhận in linh hoạt từ 50 bộ thiệp.</p>'
                    .$figure('services-print-hero.webp', 'Dịch vụ in thiệp cưới cao cấp giấy mỹ thuật ép kim tem sáp tại Mây Hẹn', 'Đồng bộ ngôn ngữ thiết kế giữa thiệp giấy cầm tay và thiệp điện tử gửi qua Zalo.')
                    .'<h2>3. Hệ sinh thái 10 dòng Thiệp Sự Kiện & Mùa Lễ Hội Việt Nam (/thiep-su-kien)</h2>'
                    .'<p>Tách biệt hoàn toàn khỏi thiệp cưới, <a href="/thiep-su-kien">Hệ Sinh Thái Thiệp Sự Kiện &amp; Lễ Hội Mây Hẹn</a> sở hữu <strong>138 mẫu thiệp thuần Việt</strong> thiết kế theo chuẩn <strong>1 trang tinh gọn duy nhất</strong> đi kèm hiệu ứng <strong>mở phong bì giấy 3 lớp mềm mại 60fps</strong>. Toàn bộ thông tin ngày giờ, địa điểm và lời mời nằm gọn trên 1 mặt thiệp sắc nét; bản đồ chỉ đường, xác nhận RSVP và lì xì VietQR mở bằng cửa sổ nổi gọn gàng.</p>'
                    .$figure('events-hub-hero.webp', 'Trang chủ Kho Thiệp Sự Kiện & Lễ Hội Việt Nam trên Mây Hẹn', '138 mẫu thiệp thuần Việt với bộ lọc thông minh theo kiểu thiệp, màu phong bì và kiểu viền.')
                    .'<table style="'.$tableStyle.'">'
                    .'<thead><tr><th style="'.$thStyle.'">Dòng thiệp sự kiện</th><th style="'.$thStyle.'">Đặc trưng &amp; Tiện ích đi kèm</th><th style="'.$thStyle.'">Khám phá ngay</th></tr></thead>'
                    .'<tbody>'
                    .'<tr><td style="'.$tdStyle.'"><strong>1. Tết Nguyên Đán &amp; Tân Niên</strong></td><td style="'.$tdStyle.'">Chuyển đổi 1 chạm giữa Thiệp Chúc Tết kèm lì xì VietQR và Thiệp Mời Tiệc Tân Niên Khai Xuân.</td><td style="'.$tdStyle.'"><a href="/thiep-su-kien/tet-nguyen-dan">Xem mẫu Tết</a></td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>2. Tết Trung Thu &amp; Trăng Rằm</strong></td><td style="'.$tdStyle.'">Thiệp tri ân đối tác doanh nghiệp kèm hộp bánh &amp; thiệp mời Đêm Hội Trăng Rằm phá cỗ.</td><td style="'.$tdStyle.'"><a href="/thiep-su-kien/tet-trung-thu">Xem mẫu Trung Thu</a></td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>3. Giáng Sinh &amp; Đón Năm Mới</strong></td><td style="'.$tdStyle.'">Họa tiết cây thông, vòng nguyệt quế, phong bì xanh thông – đỏ nhung cho tiệc cuối năm.</td><td style="'.$tdStyle.'"><a href="/thiep-su-kien/giang-sinh-nam-moi">Xem mẫu Giáng Sinh</a></td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>4. Đầy Tháng &amp; Thôi Nôi Bé</strong></td><td style="'.$tdStyle.'">Tải ảnh thật của bé lên mặt thiệp, ghi tên ở nhà, ngày âm lịch và lì xì chúc bé mau lớn.</td><td style="'.$tdStyle.'"><a href="/thiep-su-kien/day-thang-thoi-noi">Xem mẫu Thôi Nôi</a></td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>5. Sinh Nhật Trẻ Em</strong></td><td style="'.$tdStyle.'">Chủ đề Công Chúa Kẹo Ngọt, Phi Hành Gia, Khu Rừng Xanh; chốt suất ăn dễ qua nhóm Zalo lớp.</td><td style="'.$tdStyle.'"><a href="/thiep-su-kien/sinh-nhat-tre-em">Xem mẫu Bé Yêu</a></td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>6. Sinh Nhật Người Lớn &amp; Mừng Thọ</strong></td><td style="'.$tdStyle.'">Hai nhánh riêng: Tiệc sinh nhật 25–40 tuổi kiểu tạp chí và Đại Lễ Mừng Thọ 70–90 tuổi trang nghiêm.</td><td style="'.$tdStyle.'"><a href="/thiep-su-kien/sinh-nhat-nguoi-lon">Xem mẫu Mừng Thọ</a></td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>7. Tân Gia &amp; Mừng Nhà Mới</strong></td><td style="'.$tdStyle.'">Ghim tọa độ Google Maps dẫn thẳng tới biệt thự/chung cư mới, thống kê mâm cỗ qua RSVP.</td><td style="'.$tdStyle.'"><a href="/thiep-su-kien/tan-gia-nha-moi">Xem mẫu Tân Gia</a></td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>8. Tất Niên Công Ty &amp; Khai Trương</strong></td><td style="'.$tdStyle.'">Tạo hàng trăm đường dẫn mời mang tên riêng từng Đối tác, chốt danh sách tham dự tập trung.</td><td style="'.$tdStyle.'"><a href="/thiep-su-kien/tat-nien-khai-truong">Xem mẫu Doanh Nghiệp</a></td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>9. Lễ Tốt Nghiệp &amp; Vinh Danh</strong></td><td style="'.$tdStyle.'">Tải ảnh mặc lễ phục cử nhân, ghim chính xác cổng trường – hội trường và giờ chụp ảnh.</td><td style="'.$tdStyle.'"><a href="/thiep-su-kien/tot-nghiep-thanh-tuu">Xem mẫu Tốt Nghiệp</a></td></tr>'
                    .'<tr><td style="'.$tdStyle.'"><strong>10. Chúc Mừng 8/3, 20/10, 20/11 &amp; Kỷ Niệm</strong></td><td style="'.$tdStyle.'">Thiệp hoa mẫu đơn tặng phái đẹp, tri ân Thầy Cô 20/11 và mời bữa tối kỷ niệm ngày cưới.</td><td style="'.$tdStyle.'"><a href="/thiep-su-kien/ngay-le-chuc-mung">Xem mẫu Chúc Mừng</a></td></tr>'
                    .'</tbody></table>'
                    .$figure('events-cat-tet.webp', 'Trang danh mục Thiệp Chúc Tết Nguyên Đán & Tân Niên với bố cục riêng', 'Mỗi trang danh mục thiệp sự kiện đều sở hữu bảng màu, bộ lọc nhanh và cẩm nang lời mời riêng biệt.')
                    .$figure('events-cat-thoi-noi.webp', 'Trang danh mục Thiệp Đầy Tháng & Thôi Nôi Cho Bé Yêu', 'Phân biệt rõ lễ Đầy Tháng 1 tháng và tiệc Thôi Nôi 1 tuổi đúng phong tục gia đình Việt.')
                    .$figure('events-cat-tan-gia.webp', 'Trang danh mục Thiệp Mời Tân Gia & Mừng Nhà Mới', 'Tối ưu cho nhu cầu ghim bản đồ nhà mới, ghi mã tòa căn hộ và nhận lời chúc mừng tân gia.')
                    .'<h2>4. Studio Thiết Kế 6 Tab Độc Lập & Hiệu Ứng Mở Phong Bì 3D</h2>'
                    .'<p>Tất cả 138 mẫu thiệp sự kiện đều được chỉnh sửa trong một <strong>Studio 6 Tab hoàn toàn tách biệt</strong> (không bị thanh điều hướng web đè lên sân khấu thiết kế): (1) <em>Mặt Thiệp</em> với 8 khuôn cắt và 15 khung tranh SVG, (2) <em>Phong Bì</em> với 8 màu vỏ, 8 lớp lót hoa văn &amp; 6 mặt bàn, (3) <em>Lời Văn</em> hỗ trợ chạm trực tiếp lên chữ để sửa, (4) <em>Bản Đồ</em>, (5) <em>RSVP &amp; QR</em> có xem thử Popup ngay tại chỗ, và (6) <em>Khách Mời</em> xuất đường dẫn cá nhân hóa hàng loạt.</p>'
                    .$figure('events-preview-card.webp', 'Trải nghiệm xem thử mẫu thiệp Tân Gia 1 trang mở phong bì 3D mềm mại', 'Hiệu ứng mở nắp phong bì và trượt thiệp 3 lớp tự nhiên kèm nút xem lại ▷ bên trái tấm thiệp.')
                    .$figure('events-studio-6tab.webp', 'Giao diện Studio Thiết Kế Thiệp Sự Kiện 6 Tab độc lập trên Mây Hẹn', 'Dùng thử miễn phí 100% toàn bộ tính năng phối màu, đổi phong bì và sửa lời mời trên mọi thiết bị.')
                    .'<h2>5. Câu hỏi thường gặp về hệ sinh thái dịch vụ Mây Hẹn</h2>'
                    .'<h3>Mua Gói Wedding Pro (199.000đ) có dùng được cả Thiệp Cưới lẫn Thiệp Sự Kiện không?</h3>'
                    .'<p>Có. Khi sở hữu Gói Pro VIP tại <a href="/bang-gia">/bang-gia</a>, bạn được toàn quyền xuất bản cả Thiệp Cưới Online lẫn toàn bộ 138 mẫu Thiệp Sự Kiện &amp; Mùa Lễ Hội trong suốt thời hạn gói.</p>'
                    .'<h3>Khách chưa mua gói có thể thử thiết kế Thiệp Sự Kiện trước không?</h3>'
                    .'<p>Hoàn toàn được. Mọi khách truy cập đều được vào Studio 6 Tab phối màu, tải ảnh, sửa lời mời và lưu bản nháp miễn phí 100% trước khi quyết định xuất bản.</p>',
                'cover_image_url' => $imgBase.'/events-hub-hero.webp',
                'author_name' => 'Ban Phát Triển Mây Hẹn',
                'tags' => ['dịch vụ thiệp cưới', 'thiệp sự kiện', 'in thiệp giấy', 'studio 6 tab'],
                'status' => 'published',
                'published_at' => now()->subHours(3),
                'reading_time_minutes' => 8,
                'view_count' => 296,
                'is_featured' => true,
                'send_email_on_publish' => false,
                'meta_title' => 'Hệ Sinh Thái Dịch Vụ Theo Từng Loại Thiệp Tại Mây Hẹn',
                'meta_description' => 'Chi tiết 3 hệ dịch vụ tại Mây Hẹn: Thiệp Cưới Online 10 phần, Dịch vụ In Thiệp Cưới Cao Cấp và 10 dòng Thiệp Sự Kiện & Lễ Hội Việt Nam (138 mẫu).',
            ],
        ];

        // =========================================================================
        // 4. 23 BÀI TIN TỨC RA MẮT CHUẨN SEO CHI TIẾT TỪNG PHẦN CHO TỪNG MẪU THIỆP CƯỚI
        // =========================================================================
        $tierNames = [
            'pro' => 'Hoàng Gia (Wedding Pro — 199.000đ)',
            'plus' => 'Hạnh Phúc (Plus — 99.000đ)',
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

        $specsMap = TemplateLaunchNewsSpecs::all();
        $templates = InvitationTemplate::where('is_active', true)->orderBy('id')->get();
        $hourOffset = 4;

        foreach ($templates as $tpl) {
            $tierLabel = $tierNames[$tpl->tier] ?? 'Cao Cấp';
            $styleLabel = $categoryNames[$tpl->category] ?? 'Nghệ Thuật Hiện Đại';
            $desc = $tpl->description ?: ('Mẫu thiệp cưới '.$tpl->name.' mang phong cách '.$styleLabel.' tinh tế chuẩn Mây Hẹn.');
            $spec = $specsMap[$tpl->slug] ?? [
                'theme_palette' => 'Kem Lụa, Vàng Champagne & Sắc Nhấn Nghệ Thuật',
                'opening_effect' => 'Màn mở thiệp mượt mà chuẩn Quiet Luxury đồng bộ cùng nhạc nền cưới',
                'signature_parts' => [
                    ['name' => 'Trang bìa Hero & Câu chuyện tình yêu', 'detail' => $desc],
                    ['name' => 'Song thân hai họ & Thông tin lễ cưới', 'detail' => 'Trình bày rõ ràng Lễ Vu Quy, Lễ Thành Hôn, Tiệc Cưới và bản đồ chỉ đường Google Maps.'],
                    ['name' => 'Đếm ngược, Lịch tháng & Dresscode', 'detail' => 'Lịch tháng cưới tự động khớp ngày cưới, đồng hồ đếm ngược và bảng màu trang phục hài hòa.'],
                    ['name' => 'Album 10 ảnh, Sổ ký tên, RSVP & VietQR', 'detail' => 'Album ảnh phóng to sắc nét, sổ ký tên tay, xác nhận tham dự và mừng cưới VietQR 0% phí.'],
                ],
                'best_for' => 'Cặp đôi tìm kiếm một mẫu thiệp cưới online tinh tế, dễ xem trên điện thoại và đầy đủ tiện ích quản lý khách mời.',
            ];

            $partsHtml = '';
            foreach ($spec['signature_parts'] as $idx => $part) {
                $num = $idx + 1;
                $partsHtml .= '<h3>2.'.$num.'. '.e($part['name']).'</h3>'
                    .'<p>'.e($part['detail']).'</p>';
                if ($idx === 1) {
                    $partsHtml .= $figure(
                        'tpl-'.$tpl->slug.'-mid.webp',
                        'Chi tiết phần nội dung giữa của mẫu thiệp cưới '.$tpl->name,
                        'Ảnh chụp thực tế khu vực nội dung trung tâm (Song thân, Chuyện tình yêu & Lịch đếm ngược) trên mẫu '.$tpl->name.'.'
                    );
                }
                if ($idx === 3) {
                    $partsHtml .= $figure(
                        'tpl-'.$tpl->slug.'-lower.webp',
                        'Chi tiết phần sự kiện, album ảnh và tương tác RSVP trên mẫu '.$tpl->name,
                        'Ảnh chụp thực tế khu vực Sự kiện, Album ảnh, Dresscode và Tương tác khách mời trên mẫu '.$tpl->name.'.'
                    );
                }
            }

            $content = '<p><strong>Mây Hẹn (<a href="/">mayhen.vn</a>)</strong> chính thức ra mắt mẫu thiệp cưới online <strong>'.$tpl->name.'</strong> thuộc dòng thiết kế <strong>'.$styleLabel.'</strong> (áp dụng từ phân hạng <strong>'.$tierLabel.'</strong>). Không phải là một bản sao chép rập khuôn, mẫu thiệp được xây dựng tỉ mỉ từng lớp đồ họa, bảng màu <strong>'.e($spec['theme_palette']).'</strong> và cấu trúc 10 phần liền mạch giúp cô dâu chú rể gửi lời báo hỷ thật tinh tế qua Zalo và mạng xã hội.</p>'
                .$figure('tpl-'.$tpl->slug.'-hero.webp', 'Ảnh chụp thực tế trang bìa Hero mẫu thiệp cưới '.$tpl->name.' trên Mây Hẹn', 'Giao diện trang bìa chính thực tế của mẫu thiệp cưới '.$tpl->name.' khi mở trên màn hình rộng.')
                .'<h2>1. Bảng thông số thiết kế & điểm nhấn của mẫu '.$tpl->name.'</h2>'
                .'<p>'.e($desc).'</p>'
                .'<table style="'.$tableStyle.'">'
                .'<thead><tr><th style="'.$thStyle.'">Thông số mẫu thiệp</th><th style="'.$thStyle.'">Chi tiết thực tế trên Mây Hẹn</th></tr></thead>'
                .'<tbody>'
                .'<tr><td style="'.$tdStyle.'"><strong>Tên mẫu chính thức</strong></td><td style="'.$tdStyle.'">'.$tpl->name.'</td></tr>'
                .'<tr><td style="'.$tdStyle.'"><strong>Phong cách mỹ thuật</strong></td><td style="'.$tdStyle.'">'.$styleLabel.'</td></tr>'
                .'<tr><td style="'.$tdStyle.'"><strong>Bảng màu chủ đạo</strong></td><td style="'.$tdStyle.'">'.e($spec['theme_palette']).'</td></tr>'
                .'<tr><td style="'.$tdStyle.'"><strong>Màn mở đầu &amp; Hiệu ứng</strong></td><td style="'.$tdStyle.'">'.e($spec['opening_effect']).'</td></tr>'
                .'<tr><td style="'.$tdStyle.'"><strong>Phân hạng áp dụng</strong></td><td style="'.$tdStyle.'">'.$tierLabel.'</td></tr>'
                .'<tr><td style="'.$tdStyle.'"><strong>Tương tác tích hợp</strong></td><td style="'.$tdStyle.'">Nhạc cưới đồng bộ, Bản đồ Google Maps, Album 10 ảnh, Sổ ký tên HTML5, RSVP &amp; VietQR 0% phí.</td></tr>'
                .'</tbody></table>'
                .'<h2>2. Giải phẫu chi tiết từng phần đặc trưng trên mẫu thiệp '.$tpl->name.'</h2>'
                .'<p>Điểm tạo nên sức hút riêng biệt cho <strong>'.$tpl->name.'</strong> nằm ở cách bố trí nhịp nhàng từng phần nội dung — mọi chi tiết trang trí đều phục vụ câu chuyện của cô dâu chú rể thay vì gây rối mắt:</p>'
                .$partsHtml
                .'<h2>3. Trải nghiệm hiển thị trên điện thoại khi gửi qua Zalo</h2>'
                .'<p>Với hơn 85% khách mời tại Việt Nam mở thiệp cưới trực tiếp trên điện thoại thông qua Zalo, <strong>'.$tpl->name.'</strong> được thiết kế theo nguyên tắc <em>Mobile-First Flow Layout</em>: tự co giãn hoàn hảo trên mọi kích thước màn hình từ 360px đến 430px, phông chữ tiếng Việt có dấu rõ ràng dễ đọc với cả ông bà lớn tuổi, 100% ảnh và font tải cục bộ giúp mở trang gần như tức thì.</p>'
                .$figure('tpl-'.$tpl->slug.'-mobile.webp', 'Giao diện điện thoại thực tế của mẫu thiệp cưới '.$tpl->name.' khi mở qua Zalo', 'Ảnh chụp thực tế hiển thị di động (430px) của mẫu thiệp cưới '.$tpl->name.' — sắc nét, cân đối và không vỡ khung.', true)
                .'<h2>4. Mẫu thiệp '.$tpl->name.' phù hợp nhất với cặp đôi nào?</h2>'
                .'<p>'.e($spec['best_for']).'</p>'
                .'<h2>5. Hướng dẫn 3 bước tự tạo mẫu thiệp '.$tpl->name.' trong 5 phút</h2>'
                .'<ol>'
                .'<li><strong>Bước 1 — Mở mẫu trong Studio V2:</strong> Nhấn vào đường dẫn <a href="/tao-thiep?template='.$tpl->slug.'">Tạo Thiệp Với Mẫu '.$tpl->name.'</a> để khởi tạo ngay không gian chỉnh sửa trực quan.</li>'
                .'<li><strong>Bước 2 — Thay thông tin &amp; ảnh cưới của hai bạn:</strong> Nhập họ tên cô dâu chú rể, song thân hai họ, ngày âm – dương lịch, địa chỉ bản đồ và tải lên tối đa 10 ảnh cưới đẹp nhất (hỗ trợ căn tiêu điểm ảnh thông minh).</li>'
                .'<li><strong>Bước 3 — Kiểm tra &amp; gửi link cá nhân hóa qua Zalo:</strong> Xem thử trên cả khung máy tính và điện thoại, sau đó xuất bản để tạo đường dẫn mang tên riêng từng vị khách và theo dõi xác nhận tham dự RSVP.</li>'
                .'</ol>'
                .'<h2>6. Câu hỏi thường gặp về mẫu thiệp '.$tpl->name.'</h2>'
                .'<h3>Tôi có thể đổi sang mẫu khác sau khi đã nhập thông tin vào '.$tpl->name.' không?</h3>'
                .'<p>Có 100%. Trong Studio Tạo Thiệp V2, toàn bộ thông tin hai họ, lịch trình và album ảnh của bạn được đồng bộ tự động — bạn có thể thử đổi sang bất kỳ mẫu nào khác trong kho <a href="/mau-thiep">23+ Mẫu Thiệp Cưới</a> mà không cần nhập lại.</p>'
                .'<h3>Mẫu '.$tpl->name.' có hỗ trợ tách riêng thiệp Nhà Trai và Nhà Gái không?</h3>'
                .'<p>Có. Khi nâng cấp lên <a href="/bang-gia">Gói Wedding Pro (199.000đ)</a>, hệ thống cho phép tách riêng 2 đường dẫn Lễ Thành Hôn (Nhà Trai) và Lễ Vu Quy (Nhà Gái) với ngày giờ và bản đồ riêng biệt.</p>'
                .'<h3>Trải nghiệm ngay bản Demo &amp; Studio của mẫu '.$tpl->name.'</h3>'
                .'<ul>'
                .'<li><a href="/xem-thu/'.$tpl->slug.'">Xem Thử Bản Demo Trực Tiếp Mẫu '.$tpl->name.'</a></li>'
                .'<li><a href="/mau-thiep/'.$tpl->slug.'">Xem Trang Chi Tiết &amp; Thông Số Mẫu '.$tpl->name.'</a></li>'
                .'<li><a href="/tao-thiep?template='.$tpl->slug.'">Tự Tạo Thiệp Với Mẫu '.$tpl->name.' Miễn Phí Ngay</a></li>'
                .'</ul>';

            $posts[] = [
                'user_id' => $author?->id,
                'title' => 'Ra Mắt Mẫu Thiệp Cưới '.$tpl->name.' — Thiết Kế '.$styleLabel.' Chuẩn Mây Hẹn',
                'slug' => 'ra-mat-mau-thiep-cuoi-'.$tpl->slug,
                'category_label' => 'Mẫu Thiệp Mới',
                'summary' => 'Review chi tiết từng phần mẫu thiệp cưới '.$tpl->name.' ('.$tierLabel.') trên Mây Hẹn: bảng màu '.$spec['theme_palette'].', hiệu ứng mở thiệp độc bản, 4 ảnh chụp thực tế và hướng dẫn tạo thiệp nhanh trong 5 phút.',
                'content' => $content,
                'cover_image_url' => $imgBase.'/tpl-'.$tpl->slug.'-hero.webp',
                'author_name' => 'Đội Ngũ Thiết Kế Mây Hẹn',
                'tags' => ['mẫu thiệp mới', mb_strtolower($tpl->name), 'thiệp cưới '.$tpl->category, 'gói '.$tpl->tier],
                'status' => 'published',
                'published_at' => now()->subHours($hourOffset++),
                'reading_time_minutes' => 6,
                'view_count' => 95 + ($tpl->id * 9),
                'is_featured' => false,
                'send_email_on_publish' => false,
                'meta_title' => 'Ra Mắt Mẫu Thiệp Cưới '.$tpl->name.' Chi Tiết Từng Phần | Mây Hẹn',
                'meta_description' => 'Khám phá chi tiết từng phần mẫu thiệp cưới online '.$tpl->name.' ('.$spec['theme_palette'].') trên Mây Hẹn kèm 4 ảnh thực tế, bảng thông số, RSVP và VietQR.',
            ];
        }

        foreach ($posts as $post) {
            NewsPost::updateOrCreate(['slug' => $post['slug']], $post);
        }
    }
}
