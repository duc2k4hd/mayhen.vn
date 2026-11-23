<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AiKnowledgeEntry;
use Illuminate\Database\Seeder;

class AiKnowledgeEntrySeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            // =========================================================================
            // NHÓM 1: DỊCH VỤ IN THIỆP CƯỚI CAO CẤP (/dich-vu)
            // =========================================================================
            [
                'category' => 'pricing',
                'title' => 'Dịch vụ in thiệp cưới cao cấp và các gói in tại Mây Hẹn',
                'keywords' => 'in thiệp cưới, dịch vụ in thiệp, in thiệp giấy mỹ thuật, các gói in thiệp, bảng giá in thiệp, đặt in thiệp cưới, in thiệp giá bao nhiêu',
                'answer' => 'Mây Hẹn cung cấp dịch vụ in thiệp cưới cao cấp trên phôi giấy mỹ thuật nhập khẩu (300gsm - 450gsm) kết hợp kỹ thuật ép kim, dập nổi và tem sáp thủ công. Có 3 gói in chính: Gói Tối Giản (từ 3.500đ/thiệp), Gói Ép Kim (từ 5.200đ/thiệp) và Gói Hoàng Gia Tem Sáp (từ 8.500đ/thiệp). Khách đặt in gói Ép kim trở lên được tặng kèm thiệp cưới online gói Pro VIP (199.000đ) đồng bộ giao diện.',
                'template_slug' => null,
                'doc_url' => '/dich-vu',
                'priority' => 10,
            ],
            [
                'category' => 'templates',
                'title' => 'Chất liệu giấy mỹ thuật và kỹ thuật gia công in thiệp cưới',
                'keywords' => 'chất liệu giấy in thiệp, giấy mỹ thuật ý, định lượng giấy, ép kim nhũ nhiệt, dập nổi 3d, dập chìm, tem sáp niêm phong, wax seal, nơ lụa',
                'answer' => 'Các mẫu in thiệp cưới tại Mây Hẹn sử dụng giấy mỹ thuật Ý nhập khẩu từ 300gsm đến 450gsm với bề mặt dệt vân sần tự nhiên, đầm chắc tay và chống ố vàng. Kỹ thuật gia công gồm: ép kim nhũ nhiệt sắc nét (vàng cát, đồng cổ, bạc ánh trăng), dập nổi dập chìm 3D không dùng mực, con dấu tem sáp đúc thủ công khắc monogram tên cặp đôi và ruy băng voan lụa tơ tằm thanh lịch.',
                'template_slug' => null,
                'doc_url' => '/dich-vu',
                'priority' => 9,
            ],
            [
                'category' => 'pricing',
                'title' => 'Chính sách nhận in thiệp cưới số lượng ít và bảng giá theo số lượng',
                'keywords' => 'in thiệp số lượng ít, in dưới 100 thiệp, in 50 thiệp cưới, bảng giá in thiệp theo số lượng, in thêm thiệp cưới',
                'answer' => 'Mây Hẹn nhận in thiệp cưới linh hoạt từ 50 thiệp trở lên cho các đám cưới ấm cúng hoặc tiệc báo hỷ. Mức giá in ưu đãi theo số lượng: 50-150 thiệp (4.500đ - 11.500đ), 200-350 thiệp (3.800đ - 9.800đ), 400-600 thiệp (3.200đ - 8.500đ), và từ 700-1.000+ thiệp (2.800đ - 7.500đ/thiệp). Đơn giá đã bao gồm: thiệp chính, phong bì nắp nhọn cao cấp và công in tên khách mời theo danh sách.',
                'template_slug' => null,
                'doc_url' => '/dich-vu',
                'priority' => 9,
            ],
            [
                'category' => 'docs',
                'title' => 'Quy trình 5 bước đặt in thiệp cưới và duyệt bản in mẫu thực tế',
                'keywords' => 'quy trình in thiệp cưới, các bước đặt in thiệp, duyệt bản in mẫu, thời gian in thiệp, đặt in thiệp trước bao lâu',
                'answer' => 'Quy trình đặt in thiệp cưới gồm 5 bước: 1. Chọn mẫu thiết kế & chất liệu giấy -> 2. Thiết kế ma-két thông tin ngày cưới -> 3. Duyệt bản in mẫu thực tế để kiểm tra chất giấy, độ nét và màu sắc ép kim -> 4. Tiến hành in ấn và gia công hàng loạt -> 5. Bàn giao tận nơi trong 3 đến 7 ngày làm việc. Bạn nên đặt in trước ngày cưới từ 1 đến 2 tháng để chủ động gửi thiệp mời.',
                'template_slug' => null,
                'doc_url' => '/dich-vu',
                'priority' => 9,
            ],
            [
                'category' => 'general',
                'title' => 'Giải pháp kép: Kết hợp in thiệp cưới giấy truyền thống và thiệp cưới online',
                'keywords' => 'kết hợp thiệp giấy và thiệp online, nên in thiệp hay làm thiệp online, xu hướng thiệp cưới, thiệp cưới cho người lớn tuổi',
                'answer' => 'Mây Hẹn tiên phong giải pháp kép kết hợp trọn vẹn văn hóa và công nghệ: Thiệp giấy mỹ thuật ép kim sang trọng dùng trao tận tay họ hàng, người lớn tuổi và đối tác của phụ mẫu; song song với Thiệp cưới online gửi nhanh qua Zalo/Messenger cho bạn bè, đồng nghiệp kèm album ảnh, bản đồ Google Maps và nút xác nhận tham dự (RSVP) giúp chốt số bàn tiệc chuẩn xác.',
                'template_slug' => null,
                'doc_url' => '/dich-vu',
                'priority' => 8,
            ],

            // =========================================================================
            // NHÓM 2: HỆ SINH THÁI THIỆP SỰ KIỆN GIA ĐÌNH & MÙA LỄ HỘI (/thiep-su-kien)
            // =========================================================================
            [
                'category' => 'templates',
                'title' => 'Hệ sinh thái Thiệp Sự Kiện Gia Đình & Mùa Lễ Hội (Paperless Post phiên bản Việt)',
                'keywords' => 'thiệp sự kiện, thiep su kien, thiệp mời sự kiện, paperless post việt nam, thiệp gia đình, thiệp lễ hội',
                'answer' => 'Phân hệ Thiệp Sự Kiện (/thiep-su-kien) mang phong cách Digital Stationery cao cấp lấy cảm hứng từ Paperless Post nhưng được Việt hóa 100% theo phong tục Việt Nam. Bao gồm 10 danh mục: Thôi nôi & Đầy tháng bé, Mừng nhà mới (Tân gia), Sinh nhật trẻ em & người lớn, Lễ mừng thọ, Tiệc tất niên công ty (YEP), Khai trương, Giáng sinh & Tết, Lễ tốt nghiệp và Thiệp chúc mừng ngày lễ 8/3, 20/10, 20/11.',
                'template_slug' => null,
                'doc_url' => '/docs/bat-dau-nhanh/thiep-su-kien-va-mua-le-hoi-pro',
                'priority' => 10,
            ],
            [
                'category' => 'pricing',
                'title' => 'Cơ chế trải nghiệm thử miễn phí và đặc quyền xuất bản Thiệp Sự Kiện cho Gói Pro VIP',
                'keywords' => 'tạo thử thiệp sự kiện, xuất bản thiệp sự kiện, quyền lợi gói pro thiệp sự kiện, gói pro có làm được thiệp sự kiện không, giá thiệp sự kiện',
                'answer' => 'Tất cả khách hàng đều có thể vào /thiep-su-kien để tự tạo thử, đổi kiểu viền, lót phong bì, chọn lời văn mẫu và xem trước 100% miễn phí cũng như lưu nháp an toàn. Tuy nhiên, quyền Xuất bản đường dẫn mời chính thức (/loi-moi/{slug}) để gửi khách mời là ĐẶC QUYỀN TRỌN ĐỜI dành riêng cho khách hàng sở hữu Gói Pro VIP (199.000đ).',
                'template_slug' => null,
                'doc_url' => '/docs/bat-dau-nhanh/thiep-su-kien-va-mua-le-hoi-pro',
                'priority' => 10,
            ],
            [
                'category' => 'docs',
                'title' => 'Trải nghiệm thiết kế Digital Stationery 3D trên Studio Thiệp Sự Kiện',
                'keywords' => 'studio thiệp sự kiện, phong bì 3d, lót phong bì, envelope liner, khung viền thiệp, backdrop, tải ảnh bé, tải ảnh gia đình',
                'answer' => 'Studio Thiệp Sự Kiện gồm 5 phân vùng chuyên sâu: 1. Mặt thiệp & Khung viền mỹ thuật (cho phép tải ảnh riêng của bé/gia đình) -> 2. Vỏ phong bì 3D (8 màu) kèm hiệu ứng mở nắp rút thiệp sống động, Lớp lót hoa văn (8 mẫu Liner) và Phông nền sân khấu (Backdrop) -> 3. Kho lời mời mẫu 1 chạm chuẩn văn hóa Việt -> 4. Sự kiện, Lịch trình & Bản đồ Google Maps -> 5. Biểu mẫu RSVP tùy chỉnh & Phong bao lì xì / Hộp mừng quà VietQR.',
                'template_slug' => null,
                'doc_url' => '/docs/bat-dau-nhanh/thiep-su-kien-va-mua-le-hoi-pro',
                'priority' => 9,
            ],
            [
                'category' => 'general',
                'title' => 'Chế độ Thiệp Mời Tiệc và Thiệp Chúc Mừng Lễ Hội Từ Xa',
                'keywords' => 'chế độ thiệp sự kiện, thiệp chúc tết từ xa, thiệp lì xì online, thiệp chúc mừng không có địa điểm, chuyển đổi thiệp mời tiệc',
                'answer' => 'Thiệp Sự Kiện trên Mây Hẹn hỗ trợ 2 chế độ linh hoạt: 1. Chế độ Mời Tiệc (Event Invitation) đầy đủ ngày giờ, đồng hồ đếm ngược, lịch trình, dresscode, bản đồ Google Maps và RSVP; 2. Chế độ Chúc Mừng Từ Xa (Greeting Card) cho phép gạt tắt khối địa điểm để biến thành thiệp chúc Tết, chúc Trung Thu, chúc 8/3, 20/10 hoặc mừng sinh nhật từ xa kèm phong bao lì xì VietQR tinh tế.',
                'template_slug' => null,
                'doc_url' => '/docs/bat-dau-nhanh/thiep-su-kien-va-mua-le-hoi-pro',
                'priority' => 8,
            ],

            // =========================================================================
            // NHÓM 3: BẢNG GIÁ & QUYỀN LỢI CÁC GÓI MÂY HẸN (/bang-gia)
            // =========================================================================
            [
                'category' => 'pricing',
                'title' => 'Bảng giá chi tiết và so sánh quyền lợi các gói dịch vụ Mây Hẹn',
                'keywords' => 'bảng giá mây hẹn, gói miễn phí, gói premium, gói plus, gói pro, so sánh các gói, bao nhiêu tiền',
                'answer' => 'Mây Hẹn có 4 gói dịch vụ rõ ràng: 1. Gói Trải Nghiệm (Free - 0đ): Tạo 1 thiệp trải nghiệm, lưu 90 ngày; 2. Gói Thành Viên (Premium - 0đ khi đăng ký): Tạo tối đa 3 thiệp, mở khóa mẫu Premium, lưu 90 ngày; 3. Gói Hạnh Phúc (Plus - 99.000đ): Lưu 180 ngày, mở khóa toàn bộ mẫu Plus, nhạc nền lãng mạn, album ảnh cưới, quản lý RSVP khách mời; 4. Gói Trọn Vẹn (Wedding Pro - 199.000đ): Lưu trọn đời 365 ngày, mẫu hoàng gia tem sáp 3D, tách link 2 họ riêng, tên miền riêng, không giới hạn khách mời và mở khóa đặc quyền xuất bản toàn bộ Thiệp Sự Kiện Gia Đình & Lễ Hội.',
                'template_slug' => null,
                'doc_url' => '/bang-gia',
                'priority' => 10,
            ],
            [
                'category' => 'pricing',
                'title' => 'Quyền lợi đặc biệt của Gói Trọn Vẹn (Wedding Pro - 199.000đ)',
                'keywords' => 'quyền lợi gói pro, gói pro có gì, gói wedding pro, đặc quyền gói pro 199k, gói cao cấp nhất',
                'answer' => 'Gói Trọn Vẹn (Wedding Pro - 199.000đ) là gói cao cấp nhất của Mây Hẹn với các đặc quyền vượt trội: Mở khóa trọn bộ mẫu thiệp hoàng gia tem sáp niêm phong 3D, tách 2 link thiệp riêng biệt cho Nhà Trai và Nhà Gái, gắn tên miền riêng của cặp đôi (custom domain), không giới hạn khách mời và dung lượng lưu trữ 365 ngày, cùng ĐẶC QUYỀN XUẤT BẢN KHÔNG GIỚI HẠN toàn bộ Thiệp Sự Kiện Gia Đình & Mùa Lễ Hội trọn đời.',
                'template_slug' => null,
                'doc_url' => '/bang-gia',
                'priority' => 10,
            ],
            [
                'category' => 'pricing',
                'title' => 'Hình thức thanh toán chuyển khoản VietQR tự động qua SePay',
                'keywords' => 'thanh toán vietqr, chuyển khoản sepay, thanh toán tự động, kích hoạt gói, bao lâu thì kích hoạt, quét mã qr',
                'answer' => 'Mây Hẹn tích hợp cổng thanh toán chuyển khoản VietQR tự động qua SePay. Khi bấm Nâng cấp gói, hệ thống sinh mã QR kèm đúng số tiền và nội dung chuyển khoản duy nhất của đơn hàng. Ngay khi bạn quét mã chuyển khoản thành công trên ứng dụng ngân hàng, hệ thống tự động đối soát và kích hoạt gói cước ngay tức thì trong vòng 3 đến 5 giây mà không cần chờ duyệt thủ công.',
                'template_slug' => null,
                'doc_url' => '/bang-gia',
                'priority' => 9,
            ],

            // =========================================================================
            // NHÓM 4: QUẢN LÝ KHÁCH MỜI, RSVP, BÀN TIỆC & CHECK-IN QR
            // =========================================================================
            [
                'category' => 'rsvp',
                'title' => 'Quản lý danh sách khách mời và gửi link thiệp Zalo cá nhân hóa',
                'keywords' => 'quản lý khách mời, gửi thiệp zalo cá nhân hóa, nhập danh sách excel, thêm khách mời, lời mời riêng từng khách',
                'answer' => 'Tại mục Tài Khoản -> Khách Mời, bạn có thể nhập nhanh danh sách khách từ file Excel/CSV hoặc nhập tay. Hệ thống tự động tạo đường dẫn thiệp cá nhân hóa kèm tên từng vị khách (ví dụ: \'Thân mời Anh Nam & Gia Đình\'). Khi chia sẻ qua Zalo hoặc Messenger, thiệp tự động hiển thị đúng lời xưng hô trân trọng mà không bị xưng hô chung chung.',
                'template_slug' => null,
                'doc_url' => '/docs/guest-rsvp/CLI-GUEST-001',
                'priority' => 10,
            ],
            [
                'category' => 'rsvp',
                'title' => 'Khảo sát xác nhận tham dự (RSVP) và thu thập thông tin cỗ cưới',
                'keywords' => 'xác nhận rsvp, cách khách xác nhận tham dự, khảo sát ăn chay ăn mặn, xe đưa đón, chốt số lượng khách',
                'answer' => 'Khách mời mở thiệp có thể bấm Xác Nhận Tham Dự (RSVP) trực tiếp: chọn Có tham dự hay Tiếc là không đến được, điền số người lớn và trẻ em đi cùng, ghi chú món ăn chay/dị ứng thực phẩm và nhu cầu đi xe đưa đón. Toàn bộ câu trả lời tự động cập nhật thời gian thực vào bảng thống kê giúp cặp đôi và nhà bếp lên thực đơn cỗ cưới chuẩn xác, không bị dư thừa hay thiếu hụt.',
                'template_slug' => null,
                'doc_url' => '/docs/guest-rsvp/CLI-GUEST-002',
                'priority' => 9,
            ],
            [
                'category' => 'rsvp',
                'title' => 'Sơ đồ xếp bàn tiệc thông minh (Seating Planner) và tra cứu vị trí bàn',
                'keywords' => 'sơ đồ bàn tiệc, xếp chỗ ngồi đám cưới, seating planner, tra cứu bàn tiệc, tìm vị trí bàn ngồi',
                'answer' => 'Mây Hẹn cung cấp công cụ Sơ đồ Bàn Tiệc (/so-do-ban-tiec) trực quan: tạo danh sách bàn tròn/bàn dài (bàn VIP, họ hàng nhà trai, nhà gái, bạn đại học, đồng nghiệp...), kéo thả hoặc phân bổ khách vào từng bàn. Đặc biệt, khách mời chỉ cần quét mã hoặc vào link Tra cứu bàn tiệc (/tra-cuu-ban-tiec) là biết ngay mình ngồi bàn số mấy và vị trí ở đâu trong sảnh cưới.',
                'template_slug' => null,
                'doc_url' => '/docs/seating-planner/CLI-SEAT-001',
                'priority' => 9,
            ],
            [
                'category' => 'rsvp',
                'title' => 'Cổng Check-in QR và Điểm Danh Tiếp Tân Sảnh Cưới bằng mã PIN',
                'keywords' => 'check-in qr, quét mã qr tại rạp cưới, điểm danh sảnh cưới, cockpit lễ tân, mã pin 6 số, tiếp đón khách ngày cưới',
                'answer' => 'Cổng Điểm Danh Tiệc Cưới (/diem-danh-tiec/{slug}) dành riêng cho ban lễ tân đón khách. Được bảo mật bằng mã PIN 6 số, lễ tân có thể dùng camera điện thoại quét mã QR cá nhân trên thiệp của khách để điểm danh trong 1 giây, tự động hiện tên và số bàn tiệc. Hệ thống có sẵn chế độ diễn tập (dry-run) trước giờ G, hỗ trợ ghi nhận ca trực và tiếp nhận khách phát sinh (walk-in) linh hoạt.',
                'template_slug' => null,
                'doc_url' => '/docs/checkin-livewall/CLI-CKIN-001',
                'priority' => 9,
            ],
            [
                'category' => 'rsvp',
                'title' => 'Phân quyền Gia Đình Hai Bên & Cổng Đồng Chủ Trì Quản Lý Khách',
                'keywords' => 'phân quyền gia đình, cổng phụ huynh, nhà trai nhà gái quản lý khách riêng, mã pin cổng gia đình',
                'answer' => 'Để thuận tiện cho phụ mẫu hai bên, Mây Hẹn có Cổng Đồng Chủ Trì Gia Đình (/cong-gia-dinh/{slug}/{side}): Bố mẹ nhà trai và bố mẹ nhà gái nhận mã PIN riêng biệt để tự theo dõi danh sách khách mời và trạng thái RSVP của riêng họ mình trên điện thoại mà không cần đăng nhập tài khoản chính của cô dâu chú rể.',
                'template_slug' => null,
                'doc_url' => '/docs/guest-rsvp/CLI-GUEST-004',
                'priority' => 8,
            ],

            // =========================================================================
            // NHÓM 5: TÍNH NĂNG CÔNG NGHỆ TIỆC CƯỚI REALTIME
            // =========================================================================
            [
                'category' => 'docs',
                'title' => 'Bức tường trình chiếu màn hình LED trực tiếp tại tiệc cưới (Live Projection Wall)',
                'keywords' => 'màn hình led tiệc cưới, chiếu lời chúc lên led, live projection wall, chiếu ảnh cưới trực tiếp, kiểm duyệt lời chúc led',
                'answer' => 'Tính năng Live Wall (/trinh-chieu/{slug}) kết nối trực tiếp với màn hình LED tại sảnh cưới: hiển thị những tấm ảnh cưới đẹp, phát trực tiếp các lời chúc và ảnh do khách mời gửi lên với giao diện Quiet Luxury sang trọng. Cặp đôi có thể bật chế độ kiểm duyệt trước hoặc tự động duyệt để đảm bảo nội dung hiển thị văn minh, trọn vẹn.',
                'template_slug' => null,
                'doc_url' => '/docs/checkin-livewall/CLI-CKIN-003',
                'priority' => 9,
            ],
            [
                'category' => 'docs',
                'title' => 'Tải ảnh khách chụp tại bàn tiệc (Live Guest Photo Drop)',
                'keywords' => 'photo drop, khách quét qr gửi ảnh cưới, chia sẻ ảnh tại bàn tiệc, gom ảnh đám cưới, tải album ảnh khách chụp',
                'answer' => 'Tại mỗi bàn tiệc, khách mời chỉ cần quét mã QR để truy cập Photo Drop (/photo-drop/{slug}) và tải ngay những bức ảnh selfie, khoảnh khắc tự nhiên chụp cùng cô dâu chú rể mà không cần cài app hay kết bạn. Sau đám cưới, cặp đôi có thể duyệt ảnh và tải trọn gói toàn bộ file gốc về máy dưới dạng tệp ZIP chất lượng cao.',
                'template_slug' => null,
                'doc_url' => '/docs/guestbook-photos/CLI-WISH-003',
                'priority' => 9,
            ],
            [
                'category' => 'docs',
                'title' => 'Hộp Thời Gian Kỷ Niệm (Wedding Time Capsule) hẹn mở sau 1 năm',
                'keywords' => 'hộp thời gian, time capsule, gửi thư tương lai đám cưới, kỷ niệm 1 năm ngày cưới, niêm phong lời chúc',
                'answer' => 'Hộp Thời Gian (/hop-thoi-gian/{slug}) là không gian xúc động nơi bạn bè, người thân gửi những bức thư, lời nhắn nhủ bí mật cho tương lai của hai bạn. Toàn bộ thư được niêm phong kỹ thuật số và chỉ tự động mở khóa đúng vào dịp kỷ niệm 1 năm ngày cưới, mang lại trải nghiệm cảm xúc vô giá cho đôi vợ chồng trẻ.',
                'template_slug' => null,
                'doc_url' => '/docs/time-capsule/CLI-TIME-001',
                'priority' => 8,
            ],
            [
                'category' => 'docs',
                'title' => 'Sổ Ký Tên Điện Tử và Bức Tranh Chữ Ký Vàng (Signature Canvas)',
                'keywords' => 'sổ ký tên điện tử, tranh ký tên vàng, signature canvas, chữ ký tay trên thiệp, lưu bút cưới',
                'answer' => 'Khách mời có thể dùng ngón tay hoặc bút cảm ứng ký tên trực tiếp lên màn hình thiệp cưới. Toàn bộ nét ký tay được lưu giữ vĩnh viễn và ghép lại thành một Bức Tranh Chữ Ký Vàng (Signature Mosaic Wall) lộng lẫy, có thể trình chiếu tại sảnh tiệc hoặc in ra làm kỷ vật trưng bày trong tổ ấm.',
                'template_slug' => null,
                'doc_url' => '/docs/guestbook-photos/CLI-WISH-001',
                'priority' => 8,
            ],
            [
                'category' => 'pricing',
                'title' => 'Hộp Mừng Cưới và Mã VietQR Tự Động Gắn Kèm Thiệp',
                'keywords' => 'hộp mừng cưới, mừng cưới online, vietqr mừng cưới, mã qr chuyển khoản thiệp cưới, mừng cưới từ xa',
                'answer' => 'Mây Hẹn hỗ trợ tính năng Hộp Mừng Cưới lịch sự và tinh tế dành cho bạn bè, người thân ở xa không thể đến dự tiệc. Cặp đôi chỉ cần điền số tài khoản ngân hàng, hệ thống tự sinh mã VietQR chuẩn ngân hàng kèm tên chủ tài khoản và lời chúc mừng tân hôn, khách quét mã chuyển khoản nhanh chóng và tiện lợi.',
                'template_slug' => null,
                'doc_url' => '/docs/gift-budget/CLI-GIFT-001',
                'priority' => 8,
            ],
            [
                'category' => 'docs',
                'title' => 'Bác Sĩ Chất Lượng Thiệp (AI Quality Doctor) kiểm tra trước khi gửi',
                'keywords' => 'bác sĩ chất lượng, ai quality doctor, kiểm tra lỗi thiệp, soát lỗi chính tả, nghiệm thu thiệp cưới',
                'answer' => 'Trước khi gửi thiệp cho quan khách, bạn có thể chạy Bác Sĩ Chất Lượng (/bac-si-chat-luong). Hệ thống tự động rà soát toàn bộ thiệp: phát hiện lỗi thiếu ngày giờ âm/dương lịch, cảnh báo ảnh cưới bị mờ hoặc sai tỷ lệ, kiểm tra link bản đồ Google Maps và chạy mô phỏng nghiệm thu trên cả màn hình điện thoại lẫn máy tính.',
                'template_slug' => null,
                'doc_url' => '/docs/studio-editor/CLI-EDIT-009',
                'priority' => 8,
            ],
            [
                'category' => 'docs',
                'title' => 'Gói Dự Phòng Mất Mạng Ngoại Tuyến (Emergency Offline Pack)',
                'keywords' => 'gói dự phòng mất mạng, emergency offline pack, thiệp cưới offline, rạp cưới không có mạng, lưu trữ ngoại tuyến',
                'answer' => 'Đề phòng trường hợp địa điểm tổ chức tiệc cưới hoặc rạp cưới tại tư gia bị mất sóng mạng Internet hoặc Wi-Fi chập chờn, Mây Hẹn cung cấp Gói Ngoại Tuyến (/goi-ngoai-tuyen): Cho phép tải toàn bộ dữ liệu thiệp, danh sách khách mời và bản mã QR về máy dưới dạng HTML/JSON để ban lễ tân tra cứu thông suốt ngay cả khi không có mạng.',
                'template_slug' => null,
                'doc_url' => '/docs/public-invitation/CLI-INVT-005',
                'priority' => 8,
            ],
            [
                'category' => 'templates',
                'title' => 'Cơ chế đổi mẫu thiệp 1 chạm bảo toàn 100% dữ liệu',
                'keywords' => 'đổi mẫu thiệp cưới, chuyển sang mẫu khác, có mất ảnh không, có mất dữ liệu không, thay đổi giao diện thiệp',
                'answer' => 'Toàn bộ mẫu thiệp trên Mây Hẹn tuân thủ nghiêm ngặt chuẩn 10 Section thống nhất. Bạn có thể đổi sang bất kỳ mẫu thiệp nào khác ngay trong Studio chỉ với 1 cú click: toàn bộ họ tên cô dâu chú rể, hai bên gia đình, ngày giờ âm dương, địa chỉ bản đồ, ảnh cưới và nhạc nền sẽ tự động chuyển sang giao diện mẫu mới mà không bị mất một chữ nào.',
                'template_slug' => null,
                'doc_url' => '/docs/studio-editor/CLI-EDIT-011',
                'priority' => 9,
            ],
            [
                'category' => 'docs',
                'title' => 'Tính năng gắn Tên Miền Riêng (Custom Domain) cho thiệp cưới',
                'keywords' => 'tên miền riêng thiệp cưới, custom domain, gắn domain riêng, namlan.wedding, địa chỉ web riêng',
                'answer' => 'Dành riêng cho khách hàng Gói Pro VIP, bạn có thể gắn tên miền riêng cho thiệp cưới của mình (ví dụ: namlan.wedding hoặc minhphuong.love) hoặc dùng subdomain ngắn gọn miễn phí (namlan.mayhen.vn). Hệ thống hỗ trợ chứng chỉ bảo mật SSL miễn phí và hướng dẫn trỏ DNS đơn giản chỉ trong 5 phút.',
                'template_slug' => null,
                'doc_url' => '/docs/public-invitation/CLI-INVT-004',
                'priority' => 8,
            ],

            // =========================================================================
            // NHÓM 6: ĐỐI TÁC STUDIO & KÊNH HỖ TRỢ
            // =========================================================================
            [
                'category' => 'general',
                'title' => 'Chương trình Đối Tác Studio Áo Cưới, Wedding Planner & Photographer',
                'keywords' => 'đối tác studio, đăng ký đối tác, wedding planner, studio áo cưới hợp tác, hoa hồng b2b, bàn giao thiệp cho khách',
                'answer' => 'Mây Hẹn có chương trình hợp tác B2B dành riêng cho Studio Áo Cưới, Wedding Planner và Nhiếp ảnh gia cưới (/doi-tac/dang-ky). Đối tác được cấp Cổng Quản Trị Studio riêng biệt để tạo thiệp hàng loạt cho khách hàng, gắn logo thương hiệu Co-branding của Studio lên chân thiệp, bàn giao thiệp cho cô dâu chú rể bằng Mã Bàn Giao (Handover Code) an toàn và nhận hoa hồng chiết khấu hấp dẫn trên từng đơn hàng.',
                'template_slug' => null,
                'doc_url' => '/doi-tac/dang-ky',
                'priority' => 8,
            ],
            [
                'category' => 'general',
                'title' => 'Kênh hỗ trợ khách hàng và thông tin liên hệ Mây Hẹn',
                'keywords' => 'liên hệ mây hẹn, hotline hỗ trợ, chăm sóc khách hàng, tư vấn trực tiếp, gửi yêu cầu hỗ trợ',
                'answer' => 'Đội ngũ Chăm sóc khách hàng Mây Hẹn luôn sẵn sàng đồng hành cùng các cặp đôi qua nhiều kênh: Trợ lý AI Concierge hoạt động 24/7 trực tiếp trên website, Trung tâm Hướng dẫn chi tiết tại /docs, trang Liên Hệ tại /lien-he, hoặc gửi yêu cầu hỗ trợ trực tiếp từ tài khoản cá nhân tại /tai-khoan/ho-tro. Đội ngũ kỹ thuật cam kết phản hồi nhanh chóng trong vòng 15-30 phút trong giờ làm việc.',
                'template_slug' => null,
                'doc_url' => '/lien-he',
                'priority' => 8,
            ],
        ];

        foreach ($entries as $item) {
            AiKnowledgeEntry::query()->updateOrCreate(
                ['title' => $item['title'], 'source' => AiKnowledgeEntry::SOURCE_ADMIN],
                array_merge($item, [
                    'source' => AiKnowledgeEntry::SOURCE_ADMIN,
                    'status' => AiKnowledgeEntry::STATUS_APPROVED,
                    'is_active' => true,
                    'trust_score' => 100,
                    'knowledge_layer' => AiKnowledgeEntry::LAYER_OFFICIAL_MAYHEN,
                    'approved_at' => now(),
                ])
            );
        }
    }
}
