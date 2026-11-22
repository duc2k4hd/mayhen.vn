<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class CrawlPaperlessPostTemplatesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'events:crawl-paperlesspost
                            {--per-category=6 : Số mẫu thiệp tối đa cào cho mỗi nguồn danh mục Paperless Post (1-12)}
                            {--fresh : Xóa catalog cũ trước khi cào mới}
                            {--localize-only : Chỉ chuẩn hóa lại 100% tên Tiếng Việt (5-10 từ, không tiếng Anh) cho toàn bộ kho mẫu đã cào mà không cần tải lại ảnh}';

    /**
     * Kho tên Tiếng Việt thuần túy, đơn giản, hiện đại, giàu ý nghĩa (chính xác 5-10 từ, 0% tiếng Anh) cho từng danh mục.
     *
     * @var array<string, array<int, string>>
     */
    protected array $vietnameseNameBank = [
        'tet-nguyen-dan' => [
            'Thiệp Chúc Tết An Khang Thịnh Vượng Đầu Xuân',
            'Thiệp Chúc Tết Cánh Én Mùa Xuân Tài Lộc',
            'Thiệp Chúc Tết Nắng Xuân Ấm Áp Gia Đạo',
            'Thiệp Chúc Tết Hoa Đào Thắm Sắc Bình An',
            'Thiệp Chúc Tết Lộc Biếc Chồi Non Phú Quý',
            'Thiệp Chúc Tết Vạn Sự Như Ý Cát Tường',
            'Thiệp Mời Tân Niên Khai Xuân Hồng Phát Rực Rỡ',
            'Thiệp Mời Tân Niên Nâng Ly Đón Lộc Đầu Năm',
            'Thiệp Mời Tân Niên Họp Mặt Đồng Nghiệp Ấm Áp',
            'Thiệp Mời Tân Niên Thuận Buồm Xuôi Gió Thành Công',
            'Thiệp Mời Tân Niên Gặp Gỡ Đầu Xuân Sum Vầy',
            'Thiệp Mời Tân Niên Khởi Đầu Năm Mới Hanh Thông',
        ],
        'tet-trung-thu' => [
            'Thiệp Chúc Trung Thu Ánh Trăng Rằm Bình An',
            'Thiệp Chúc Trung Thu Sắc Thu Vàng Viên Mãn',
            'Thiệp Chúc Trung Thu Đóa Sen Ngọc Đoàn Viên',
            'Thiệp Chúc Trung Thu Gửi Trọn Tâm Tình Mùa Thu',
            'Thiệp Chúc Trung Thu Trăng Sáng Tỏa Ngàn Yêu Thương',
            'Thiệp Chúc Trung Thu Hạnh Phúc Tròn Đầy Sum Họp',
            'Thiệp Mời Tiệc Trà Thưởng Nguyệt Đêm Rằm',
            'Thiệp Mời Đêm Hội Trăng Rằm Rước Đèn Ông Sao',
            'Thiệp Mời Phá Cỗ Trung Thu Ấm Áp Gia Đình',
            'Thiệp Mời Thưởng Trà Nếm Bánh Ngắm Trăng Thu',
            'Thiệp Mời Trung Thu Đèn Lồng Lung Linh Phố Hội',
            'Thiệp Mời Đêm Trăng Đoàn Viên Cho Bé Yêu',
        ],
        'giang-sinh-nam-moi' => [
            'Thiệp Mời Giáng Sinh Ánh Nến Đêm Đông Ấm Áp',
            'Thiệp Mời Giáng Sinh Bông Tuyết Trắng Bình An',
            'Thiệp Mời Giáng Sinh Chuông Ngân Mùa Lễ Hội',
            'Thiệp Mời Giáng Sinh Tiệc Tối Sum Vầy Gia Đình',
            'Thiệp Mời Giáng Sinh Dạ Tiệc Mùa Đông Sang Trọng',
            'Thiệp Mời Giáng Sinh Nâng Ly Chúc Mừng Năm Mới',
            'Thiệp Chúc Giáng Sinh An Lành Ngập Tràn Yêu Thương',
            'Thiệp Chúc Giáng Sinh Ấm Áp Niềm Vui Cuối Năm',
            'Thiệp Chúc Giáng Sinh Hạnh Phúc Bên Người Thân',
            'Thiệp Chúc Giáng Sinh Ánh Sao Hy Vọng Rạng Ngời',
            'Thiệp Chúc Giáng Sinh Bình Yên Đón Năm Mới',
            'Thiệp Chúc Giáng Sinh Gửi Trao Lời Chúc Tốt Lành',
        ],
        'day-thang-thoi-noi' => [
            'Thiệp Mời Đầy Tháng Thiên Thần Nhỏ Bình An',
            'Thiệp Mời Đầy Tháng Nắng Mai Rạng Rỡ Bé Yêu',
            'Thiệp Mời Đầy Tháng Ánh Trăng Dịu Êm Cho Bé',
            'Thiệp Mời Đầy Tháng Chào Đón Thành Viên Nhí',
            'Thiệp Mời Đầy Tháng Nụ Cười Ngọt Ngào Đầu Đời',
            'Thiệp Mời Đầy Tháng Bình An Cả Đời Cho Con',
            'Thiệp Mời Thôi Nôi Chúc Bé Hay Ăn Chóng Lớn',
            'Thiệp Mời Thôi Nôi Bước Chân Đầu Đời Rạng Rỡ',
            'Thiệp Mời Thôi Nôi Nến Hồng Mừng Bé Một Tuổi',
            'Thiệp Mời Thôi Nôi Vườn Hoa Cổ Tích Bé Yêu',
            'Thiệp Mời Thôi Nôi Ngôi Sao Nhỏ Của Gia Đình',
            'Thiệp Mời Thôi Nôi Trọn Vẹn Yêu Thương Tuổi Một',
        ],
        'sinh-nhat-tre-em' => [
            'Thiệp Mời Sinh Nhật Bé Bóng Bay Sắc Màu',
            'Thiệp Mời Sinh Nhật Bé Bánh Kem Ngọt Ngào',
            'Thiệp Mời Sinh Nhật Bé Chuyến Tàu Tuổi Thơ',
            'Thiệp Mời Sinh Nhật Bé Khu Rừng Nhỏ Dễ Thương',
            'Thiệp Mời Sinh Nhật Bé Cầu Vồng Rực Rỡ',
            'Thiệp Mời Sinh Nhật Bé Ngôi Sao Sáng Lung Linh',
        ],
        'sinh-nhat-nguoi-lon' => [
            'Thiệp Mời Sinh Nhật Bữa Tối Ấm Cúng Bạn Thân',
            'Thiệp Mời Sinh Nhật Nâng Ly Chào Tuổi Mới',
            'Thiệp Mời Sinh Nhật Đêm Tiệc Tinh Tế Hiện Đại',
            'Thiệp Mời Sinh Nhật Khoảnh Khắc Đáng Nhớ Tuổi Xuân',
            'Thiệp Mời Sinh Nhật Sắc Vàng Lấp Lánh Sang Trọng',
            'Thiệp Mời Sinh Nhật Gặp Gỡ Trọn Vẹn Niềm Vui',
            'Thiệp Mời Mừng Thọ Phúc Đức An Khang Trường Thọ',
            'Thiệp Mời Mừng Thọ Báo Hiếu Đấng Sinh Thành',
            'Thiệp Mời Mừng Thọ Tùng Hạc Diên Niên Sum Vầy',
            'Thiệp Mời Mừng Thọ Hồng Phúc Trăm Năm Gia Đạo',
            'Thiệp Mời Mừng Thọ Chúc Ông Bà Trường Thọ',
            'Thiệp Mời Mừng Thọ Đại Gia Đình Sum Vầy',
        ],
        'tan-gia-nha-moi' => [
            'Thiệp Mời Tân Gia Ngôi Nhà Mới Ngập Nắng',
            'Thiệp Mời Tân Gia Khởi Đầu Tổ Ấm Hạnh Phúc',
            'Thiệp Mời Tân Gia An Cư Lạc Nghiệp Vững Bền',
            'Thiệp Mời Tân Gia Đón Khách Quý Thăm Nhà Mới',
            'Thiệp Mời Tân Gia Vạn Sự Hanh Thông Đủ Đầy',
            'Thiệp Mời Tân Gia Chén Trà Ấm Mừng Tổ Ấm',
        ],
        'tat-nien-khai-truong' => [
            'Thiệp Mời Tất Niên Tổng Kết Năm Cũ Thành Công',
            'Thiệp Mời Tất Niên Đêm Hội Tri Ân Đồng Hành',
            'Thiệp Mời Tất Niên Gắn Kết Tập Thể Vững Mạnh',
            'Thiệp Mời Tất Niên Vinh Danh Thành Tựu Rực Rỡ',
            'Thiệp Mời Tất Niên Tiệc Mừng Cuối Năm Trang Trọng',
            'Thiệp Mời Tất Niên Chung Bước Đón Năm Mới',
            'Thiệp Mời Khai Trương Khởi Đầu Thịnh Vượng Bền Lâu',
            'Thiệp Mời Khai Trương Cắt Băng Đón Lộc May Mắn',
            'Thiệp Mời Khai Trương Buôn May Bán Đắt Phát Tài',
            'Thiệp Mời Khai Trương Chào Đón Đối Tác Khách Quý',
            'Thiệp Mời Khai Trương Mở Rộng Không Gian Mới',
            'Thiệp Mời Khai Trương Thuận Lợi Rực Rỡ Thành Công',
        ],
        'tot-nghiep-thanh-tuu' => [
            'Thiệp Mời Tốt Nghiệp Khép Lại Thanh Xuân Giảng Đường',
            'Thiệp Mời Tốt Nghiệp Tự Hào Khoác Áo Cử Nhân',
            'Thiệp Mời Tốt Nghiệp Mở Ra Chặng Đường Tươi Sáng',
            'Thiệp Mời Tốt Nghiệp Chia Sẻ Niềm Vui Nhận Bằng',
            'Thiệp Mời Tốt Nghiệp Lưu Giữ Kỷ Niệm Đẹp Nhất',
            'Thiệp Mời Tốt Nghiệp Tri Ân Gia Đình Bạn Bè',
        ],
        'ngay-le-chuc-mung' => [
            'Thiệp Tri Ân Thầy Cô Ngọn Lửa Bục Giảng',
            'Thiệp Tri Ân Thầy Cô Tôn Sư Trọng Đạo',
            'Thiệp Tri Ân Thầy Cô Gửi Trọn Lòng Biết Ơn',
            'Thiệp Tri Ân Thầy Cô Đóa Hoa Nghĩa Tình',
            'Thiệp Tri Ân Thầy Cô Kính Chúc Bình An',
            'Thiệp Tri Ân Thầy Cô Nhớ Mãi Công Ơn',
            'Thiệp Kỷ Niệm Ngày Cưới Trọn Vẹn Lời Hẹn Ước',
            'Thiệp Kỷ Niệm Ngày Cưới Hành Trình Bền Chặt',
            'Thiệp Kỷ Niệm Ngày Cưới Bữa Tối Lãng Mạn',
            'Thiệp Kỷ Niệm Ngày Cưới Gắn Bó Sắt Son',
            'Thiệp Kỷ Niệm Ngày Cưới Ngọt Ngào Ban Đầu',
            'Thiệp Kỷ Niệm Ngày Cưới Mừng Chặng Đường Hạnh Phúc',
            'Thiệp Chúc Mừng Phái Đẹp Luôn Rạng Rỡ Tự Tin',
            'Thiệp Chúc Mừng Phái Đẹp Ngập Tràn Niềm Vui',
            'Thiệp Chúc Mừng Phái Đẹp Sắc Hoa Thắm Tươi',
            'Thiệp Chúc Mừng Phái Đẹp Gửi Trao Yêu Thương',
            'Thiệp Chúc Mừng Phái Đẹp Tỏa Sáng Mỗi Ngày',
            'Thiệp Chúc Mừng Phái Đẹp Tôn Vinh Nét Việt',
        ],
    ];

    /**
     * @var string
     */
    protected $description = 'Cào mẫu thiệp thực tế từ Paperless Post, tải 100% tài nguyên phong bì & phông nền về local và chuyển hóa thành mẫu thiệp Tiếng Việt chuẩn Mây Hẹn (loại trừ Thiệp Cưới)';

    /**
     * Ánh xạ danh mục gốc Paperless Post -> 10 danh mục văn hóa & mùa lễ hội Việt Nam trên Mây Hẹn.
     *
     * @var array<int, array{source: string, category_slug: string, vi_prefix: string, card_mode: string, motif: string, seal_icon: string, badge: string, host: string, sub: string, msg: string, show_venue: bool, gift_qr: bool}>
     */
    protected array $sourceMap = [
        // 1. Tết Nguyên Đán & Tân Niên
        [
            'source' => 'new-year-cards',
            'category_slug' => 'tet-nguyen-dan',
            'vi_prefix' => 'Chúc Tết Tân Xuân',
            'card_mode' => 'greeting',
            'motif' => 'tet-blossom',
            'seal_icon' => '🧧',
            'badge' => 'CUNG CHÚC TÂN XUÂN • AN KHANG THỊNH VƯỢNG',
            'host' => 'Gia Đình Minh Tuấn & Thu Hà',
            'sub' => 'Kính chúc Năm Mới Vạn Sự Như Ý • Tài Lộc Hanh Thông',
            'msg' => 'Kính chúc Ông Bà, Cô Chú và Quý thân hữu năm mới Sức Khỏe Dồi Dào, Gia Đạo Bình An, Vạn Sự Cát Tường!',
            'show_venue' => false,
            'gift_qr' => true,
        ],
        [
            'source' => 'new-years-eve-invitations',
            'category_slug' => 'tet-nguyen-dan',
            'vi_prefix' => 'Tiệc Tân Niên Khai Xuân',
            'card_mode' => 'invitation',
            'motif' => 'tet-swallows',
            'seal_icon' => '🌸',
            'badge' => 'TIỆC TÂN NIÊN KHAI XUÂN ĐẦU NĂM',
            'host' => 'Họp Mặt Khai Xuân Đầu Năm',
            'sub' => 'Nâng Ly Chúc Mừng Năm Mới Đại Thắng • Thuận Buồm Xuôi Gió',
            'msg' => 'Trân trọng kính mời Quý vị tới dự Tiệc Tân Niên Khai Xuân và nhận lộc may mắn đầu năm.',
            'show_venue' => true,
            'gift_qr' => false,
        ],

        // 2. Tết Trung Thu & Đêm Hội Trăng Rằm
        [
            'source' => 'fall-entertaining-invitations',
            'category_slug' => 'tet-trung-thu',
            'vi_prefix' => 'Trăng Rằm Đoàn Viên',
            'card_mode' => 'greeting',
            'motif' => 'moon-lotus',
            'seal_icon' => '🥮',
            'badge' => 'TẾT TRUNG THU • NGUYỆT VIÊN HOA HẢO',
            'host' => 'Tết Đoàn Viên Tháng Tám',
            'sub' => 'Kính chúc Quý Đối Tác & Gia Đình mùa Trăng Rằm Viên Mãn',
            'msg' => 'Gửi tới Quý vị lời chúc Tết Trung Thu ấm áp, sum vầy hạnh phúc dưới ánh trăng rằm tròn đầy.',
            'show_venue' => false,
            'gift_qr' => false,
        ],
        [
            'source' => 'tea-party-invitations',
            'category_slug' => 'tet-trung-thu',
            'vi_prefix' => 'Tiệc Trà Thưởng Nguyệt',
            'card_mode' => 'invitation',
            'motif' => 'moon-lantern',
            'seal_icon' => '🏮',
            'badge' => 'TIỆC TRÀ THƯỞNG NGUYỆT • RẰM THÁNG TÁM',
            'host' => 'Đêm Hội Trăng Rằm & Tiệc Trà',
            'sub' => 'Thưởng Trà Sen • Nếm Bánh Trung Thu • Rước Đèn Phá Cỗ',
            'msg' => 'Thân mời bạn ghé thưởng chén trà sen ấm, nếm bánh trung thu và ngắm trăng rằm đoàn viên.',
            'show_venue' => true,
            'gift_qr' => false,
        ],

        // 3. Giáng Sinh & Năm Mới
        [
            'source' => 'christmas-party-invitations',
            'category_slug' => 'giang-sinh-nam-moi',
            'vi_prefix' => 'Tiệc Giáng Sinh',
            'card_mode' => 'invitation',
            'motif' => 'christmas-tree',
            'seal_icon' => '🎄',
            'badge' => 'GIÁNG SINH AN LÀNH • ĐÊM TIỆC MÙA ĐÔNG',
            'host' => 'Đêm Tiệc Giáng Sinh An Lành',
            'sub' => 'Mùa Lễ Hội Sum Vầy • Bữa Tối Ấm Áp Bên Người Thân',
            'msg' => 'Cùng quây quần bên cây thông ấm áp, nâng ly rượu vang nóng và đón đêm Giáng Sinh an lành.',
            'show_venue' => true,
            'gift_qr' => false,
        ],
        [
            'source' => 'christmas-cards',
            'category_slug' => 'giang-sinh-nam-moi',
            'vi_prefix' => 'Chúc Mừng Giáng Sinh',
            'card_mode' => 'greeting',
            'motif' => 'holly-wreath',
            'seal_icon' => '🎀',
            'badge' => 'CHÚC MỪNG GIÁNG SINH & NĂM MỚI AN LÀNH',
            'host' => 'Giáng Sinh An Lành & Ấm Áp',
            'sub' => 'Gửi Trọn Yêu Thương Mùa Lễ Hội Cuối Năm',
            'msg' => 'Chúc bạn và gia đình một mùa Giáng Sinh bình an, ngập tràn tiếng cười và năm mới rực rỡ.',
            'show_venue' => false,
            'gift_qr' => false,
        ],

        // 4. Đầy Tháng & Thôi Nôi Em Bé
        [
            'source' => '100-day-celebration-invitations',
            'category_slug' => 'day-thang-thoi-noi',
            'vi_prefix' => 'Lễ Đầy Tháng Bé Yêu',
            'card_mode' => 'invitation',
            'motif' => 'baby-cradle',
            'seal_icon' => '👶',
            'badge' => 'LỄ MỪNG ĐẦY THÁNG THIÊN THẦN NHỎ',
            'host' => 'Bé Nguyễn Minh Khôi (Bé Bơ)',
            'sub' => 'Mừng Con Yêu Tròn Tháng Bình An • Con Của Ba Tuấn & Mẹ Hà',
            'msg' => 'Trân trọng kính mời Ông Bà, Cô Chú tới dự bữa cơm thân mật mừng lễ Đầy Tháng của bé.',
            'show_venue' => true,
            'gift_qr' => true,
        ],
        [
            'source' => 'first-birthday-invitations',
            'category_slug' => 'day-thang-thoi-noi',
            'vi_prefix' => 'Tiệc Thôi Nôi 1 Tuổi',
            'card_mode' => 'invitation',
            'motif' => 'woodland-animals',
            'seal_icon' => '🦌',
            'badge' => 'TIỆC MỪNG THÔI NÔI • BÉ TRÒN 1 TUỔI',
            'host' => 'Bé Lê Tuệ Nhi (Bé Cam)',
            'sub' => 'Mừng Thiên Thần Nhỏ Tròn 1 Tuổi • Bốc Đồ Chọn Nghề',
            'msg' => 'Cùng gia đình đón mừng cột mốc tròn 1 tuổi đầu đời và thổi cây nến đầu tiên của bé yêu.',
            'show_venue' => true,
            'gift_qr' => true,
        ],

        // 5. Sinh Nhật Trẻ Em
        [
            'source' => 'kids-birthday-invitations',
            'category_slug' => 'sinh-nhat-tre-em',
            'vi_prefix' => 'Sinh Nhật Bé Yêu',
            'card_mode' => 'invitation',
            'motif' => 'kids-pamper-party',
            'seal_icon' => '🎈',
            'badge' => 'TIỆC SINH NHẬT BÉ YÊU • MỪNG TUỔI MỚI',
            'host' => 'Sinh Nhật 6 Tuổi Bé Khánh Ngân',
            'sub' => 'Bữa Tiệc Tuổi Thơ Ngọt Ngào Cùng Các Bạn Nhỏ',
            'msg' => 'Thân mời Cô Chú và các bạn nhỏ tới dự tiệc sinh nhật, thổi nến và chơi trò chơi vui nhộn!',
            'show_venue' => true,
            'gift_qr' => false,
        ],

        // 6. Sinh Nhật Người Lớn & Mừng Thọ
        [
            'source' => 'adult-birthday-invitations',
            'category_slug' => 'sinh-nhat-nguoi-lon',
            'vi_prefix' => 'Tiệc Sinh Nhật Sang Trọng',
            'card_mode' => 'invitation',
            'motif' => 'adult-martini-editorial',
            'seal_icon' => '🍸',
            'badge' => 'ĐÊM TIỆC SINH NHẬT • MỪNG TUỔI MỚI',
            'host' => 'Trần Thu Hà • Mừng Tuổi 30',
            'sub' => 'Bữa Tối Ấm Cúng & Đêm Tiệc Vui Vẻ Bên Hội Bạn Thân',
            'msg' => 'Hẹn gặp những người bạn thương quý trong đêm tiệc sinh nhật đáng nhớ này nhé!',
            'show_venue' => true,
            'gift_qr' => false,
        ],
        [
            'source' => '80th-birthday-invitations',
            'category_slug' => 'sinh-nhat-nguoi-lon',
            'vi_prefix' => 'Đại Lễ Mừng Thọ',
            'card_mode' => 'invitation',
            'motif' => 'elder-longevity-crane',
            'seal_icon' => '🍑',
            'badge' => 'ĐẠI LỄ MỪNG THỌ • PHÚC NHƯ ĐÔNG HẢI',
            'host' => 'Mừng Thọ Cụ Ông Nguyễn Văn Phúc',
            'sub' => 'Đại Lễ Mừng Thọ 80 Tuổi • Con Cháu Thành Tâm Báo Hiếu',
            'msg' => 'Trân trọng kính mời Quý khách tới chung vui cùng đại gia đình trong Đại lễ Mừng Thọ Ông Bà.',
            'show_venue' => true,
            'gift_qr' => true,
        ],

        // 7. Tân Gia & Nhà Mới
        [
            'source' => 'housewarming-party-invitations',
            'category_slug' => 'tan-gia-nha-moi',
            'vi_prefix' => 'Tiệc Mừng Tân Gia',
            'card_mode' => 'invitation',
            'motif' => 'house-key',
            'seal_icon' => '🏡',
            'badge' => 'LỄ MỪNG TÂN GIA • AN CƯ LẠC NGHIỆP',
            'host' => 'Gia Đình Minh Tuấn & Thu Hà',
            'sub' => 'Tiệc Mừng Nhà Mới • Hoàn Thiện Tổ Ấm Bình Yên',
            'msg' => 'Hân hạnh đón tiếp Quý vị tới tham quan ngôi nhà mới và dùng bữa cơm thân mật cùng gia đình.',
            'show_venue' => true,
            'gift_qr' => true,
        ],

        // 8. Tất Niên Công Ty & Khai Trương
        [
            'source' => 'business-holiday-invitations',
            'category_slug' => 'tat-nien-khai-truong',
            'vi_prefix' => 'Tiệc Tất Niên Doanh Nghiệp',
            'card_mode' => 'invitation',
            'motif' => 'corp-gala-trophy',
            'seal_icon' => '🏆',
            'badge' => 'ĐÊM TIỆC TẤT NIÊN & VINH DANH CUỐI NĂM',
            'host' => 'Tập Đoàn Công Nghệ Mây Hẹn',
            'sub' => 'Tổng Kết Cuối Năm • Vinh Danh & Bứt Phá Tương Lai',
            'msg' => 'Trân trọng kính mời Quý Đối Tác và CBNV tới tham dự đêm tiệc tất niên tổng kết cuối năm.',
            'show_venue' => true,
            'gift_qr' => false,
        ],
        [
            'source' => 'launch-party-invitations',
            'category_slug' => 'tat-nien-khai-truong',
            'vi_prefix' => 'Lễ Khai Trương Hồng Phát',
            'card_mode' => 'invitation',
            'motif' => 'corp-gala-trophy',
            'seal_icon' => '🎊',
            'badge' => 'LỄ KHAI TRƯƠNG HỒNG PHÁT • ĐẠI CÁT ĐẠI LỢI',
            'host' => 'Thương Hiệu Mây Hẹn',
            'sub' => 'Lễ Cắt Băng Khai Trương Cơ Sở Mới • Đại Cát Đại Lợi',
            'msg' => 'Trân trọng kính mời Quý khách và Đối tác tới dự lễ cắt băng khai trương và tiệc trà chúc mừng.',
            'show_venue' => true,
            'gift_qr' => true,
        ],

        // 9. Lễ Tốt Nghiệp & Vinh Danh
        [
            'source' => 'graduation-party-invitations',
            'category_slug' => 'tot-nghiep-thanh-tuu',
            'vi_prefix' => 'Lễ Tốt Nghiệp Đại Học',
            'card_mode' => 'invitation',
            'motif' => 'graduation-cap',
            'seal_icon' => '🎓',
            'badge' => 'LỄ TRAO BẰNG TỐT NGHIỆP ĐẠI HỌC',
            'host' => 'Tân Cử Nhân Nguyễn Minh Anh',
            'sub' => 'Lễ Trao Bằng Tốt Nghiệp & Chụp Ảnh Kỷ Niệm Thanh Xuân',
            'msg' => 'Thân mời gia đình và những người bạn yêu quý tới dự lễ trao bằng tốt nghiệp cùng mình!',
            'show_venue' => true,
            'gift_qr' => false,
        ],

        // 10. Ngày Lễ 8/3, 20/10, 20/11 & Kỷ Niệm
        [
            'source' => 'teacher-appreciation',
            'category_slug' => 'ngay-le-chuc-mung',
            'vi_prefix' => 'Tri Ân Thầy Cô 20/11',
            'card_mode' => 'greeting',
            'motif' => 'floral-bouquet',
            'seal_icon' => '📖',
            'badge' => 'CHÀO MỪNG NGÀY NHÀ GIÁO VIỆT NAM 20/11',
            'host' => 'Kính Tặng Thầy Cô Đáng Kính',
            'sub' => 'Lời Tri Ân Sâu Sắc Từ Tập Thể Học Trò',
            'msg' => 'Kính chúc Thầy Cô luôn mạnh khỏe, bình an và giữ mãi ngọn lửa đam mê trên bục giảng.',
            'show_venue' => false,
            'gift_qr' => false,
        ],
        [
            'source' => 'anniversary-party-invitations',
            'category_slug' => 'ngay-le-chuc-mung',
            'vi_prefix' => 'Kỷ Niệm Ngày Cưới',
            'card_mode' => 'invitation',
            'motif' => 'rings-anniversary',
            'seal_icon' => '💍',
            'badge' => 'KỶ NIỆM NGÀY CƯỚI • HÀNH TRÌNH CHUNG ĐÔI',
            'host' => 'Minh Tuấn & Thu Hà',
            'sub' => 'Kỷ Niệm 10 Năm Hành Trình Yêu Thương Vàng Son',
            'msg' => 'Trân trọng mời những người thân thương nhất tới dự bữa tối kỷ niệm ngày cưới ấm cúng.',
            'show_venue' => true,
            'gift_qr' => false,
        ],
        [
            'source' => 'congratulations-cards',
            'category_slug' => 'ngay-le-chuc-mung',
            'vi_prefix' => 'Chúc Mừng 8/3 & 20/10',
            'card_mode' => 'greeting',
            'motif' => 'floral-bouquet',
            'seal_icon' => '💐',
            'badge' => 'CHÚC MỪNG PHÁI ĐẸP • TÔN VINH YÊU THƯƠNG',
            'host' => 'Gửi Người Phụ Nữ Tuyệt Vời',
            'sub' => 'Chúc Mừng Ngày Quốc Tế Phụ Nữ 8/3 & 20/10',
            'msg' => 'Chúc bạn luôn xinh đẹp rạng rỡ, kiêu hãnh và ngập tràn hạnh phúc mỗi ngày!',
            'show_venue' => false,
            'gift_qr' => true,
        ],
    ];

    public function handle(): int
    {
        $perCategory = max(1, min(12, (int) $this->option('per-category')));
        $catalogPath = storage_path('app/event-templates-catalog.json');
        $assetsBaseDir = public_path('assets/client/events/pp');

        if ($this->option('fresh') && File::exists($catalogPath)) {
            File::delete($catalogPath);
        }

        File::ensureDirectoryExists($assetsBaseDir);

        $existingCatalog = [];
        if (File::exists($catalogPath)) {
            $decoded = json_decode((string) File::get($catalogPath), true);
            if (is_array($decoded)) {
                $existingCatalog = $decoded;
            }
        }

        if ($this->option('localize-only')) {
            return $this->localizeExistingCatalog($catalogPath);
        }

        $importedCount = 0;
        $seenPackageIds = [];
        $categoryCounters = [];
        foreach ($existingCatalog as $item) {
            if (! empty($item['source_package_id'])) {
                $seenPackageIds[(string) $item['source_package_id']] = true;
            }
            $cat = (string) ($item['category_slug'] ?? '');
            if ($cat !== '') {
                $categoryCounters[$cat] = ($categoryCounters[$cat] ?? 0) + 1;
            }
        }

        foreach ($this->sourceMap as $src) {
            $url = 'https://www.paperlesspost.com/cards/category/'.$src['source'];
            $this->info("→ Đang cào danh mục [{$src['source']}] -> [{$src['category_slug']}]...");

            $html = $this->fetchUrl($url);
            if ($html === '') {
                $this->warn("  ! Không tải được trang {$url}");

                continue;
            }

            $edges = $this->extractCardEdges($html);
            if (empty($edges)) {
                $this->warn("  ! Không tìm thấy dữ liệu mẫu thiệp trong {$src['source']}");

                continue;
            }

            $taken = 0;
            foreach ($edges as $edge) {
                if ($taken >= $perCategory) {
                    break;
                }

                $comp = $edge['node']['composition'] ?? null;
                if (! is_array($comp)) {
                    continue;
                }

                $packageId = (string) ($comp['packageId'] ?? '');
                if ($packageId === '' || isset($seenPackageIds[$packageId])) {
                    continue;
                }

                $rawTitle = trim((string) ($comp['family']['meta']['label'] ?? ''));
                if ($rawTitle === '' || str_contains(mb_strtolower($rawTitle), 'upload your own')) {
                    continue;
                }

                $slug = 'pp-'.$src['category_slug'].'-'.Str::slug($rawTitle).'-'.$packageId;
                $slug = Str::limit($slug, 90, '');

                $envUrl = $this->pickArtifactUrl($comp['artifacts']['envelopeBack'] ?? null, ['pngMedium', 'jpegMedium', 'pngSmall']);
                $bgUrl = $this->pickArtifactUrl($comp['artifacts']['backdropDesktop'] ?? null, ['jpegMedium', 'jpegSmall']);

                if ($envUrl === '') {
                    continue;
                }

                $tplAssetDir = $assetsBaseDir.DIRECTORY_SEPARATOR.$slug;
                File::ensureDirectoryExists($tplAssetDir);

                $localEnvRel = null;
                $envBytes = $this->fetchUrl($envUrl);
                if (strlen($envBytes) > 1000) {
                    $ext = str_contains($envUrl, 'png') ? 'png' : 'jpg';
                    File::put($tplAssetDir.DIRECTORY_SEPARATOR.'envelope-back.'.$ext, $envBytes);
                    $localEnvRel = 'assets/client/events/pp/'.$slug.'/envelope-back.'.$ext;
                }

                $localBgRel = null;
                if ($bgUrl !== '') {
                    $bgBytes = $this->fetchUrl($bgUrl);
                    if (strlen($bgBytes) > 1000) {
                        File::put($tplAssetDir.DIRECTORY_SEPARATOR.'backdrop.jpg', $bgBytes);
                        $localBgRel = 'assets/client/events/pp/'.$slug.'/backdrop.jpg';
                    }
                }

                if (! $localEnvRel) {
                    continue;
                }

                $dominantColor = $this->mapColorToMayHen($comp['colors'] ?? []);
                $cardBorder = $this->mapShapeToMayHenBorder((string) ($comp['shape']['name'] ?? 'tall'), $src['category_slug']);
                $catIndex = $categoryCounters[$src['category_slug']] ?? 0;
                $categoryCounters[$src['category_slug']] = $catIndex + 1;
                $vietnameseName = $this->resolveVietnameseName($src['category_slug'], $catIndex);

                $existingCatalog[$slug] = [
                    'slug' => $slug,
                    'source_package_id' => $packageId,
                    'source_category' => $src['source'],
                    'category_slug' => $src['category_slug'],
                    'name' => $vietnameseName,
                    'tagline' => 'Thiết kế thiệp một trang hiện đại kèm phong bì mở nắp mềm mại và phông nền tinh tế chuẩn Mây Hẹn',
                    'card_mode' => $src['card_mode'],
                    'default_palette' => $dominantColor['palette'],
                    'envelope_color' => $dominantColor['envelope_color'],
                    'envelope_liner' => $dominantColor['envelope_liner'],
                    'card_border' => $cardBorder,
                    'artwork_motif' => $src['motif'],
                    'backdrop' => $dominantColor['backdrop'],
                    'envelope_back_img' => $localEnvRel,
                    'backdrop_img' => $localBgRel,
                    'seal_icon' => $src['seal_icon'],
                    'seal_label' => 'Chạm Mở '.$src['vi_prefix'],
                    'host_name' => $src['host'],
                    'sub_host_name' => $src['sub'],
                    'event_badge' => $src['badge'],
                    'invitation_message' => $src['msg'],
                    'show_datetime_venue' => $src['show_venue'],
                    'enable_gift_qr' => $src['gift_qr'],
                ];

                $seenPackageIds[$packageId] = true;
                $taken++;
                $importedCount++;
            }

            $this->line("  ✓ Đã nhập {$taken} mẫu từ {$src['source']}");
        }

        File::put(
            $catalogPath,
            json_encode($existingCatalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $total = count($existingCatalog);
        $this->info("Hoàn tất! Đã cào mới {$importedCount} mẫu (Tổng kho chuyển hóa từ Paperless Post: {$total} mẫu) và lưu tại storage/app/event-templates-catalog.json.");

        return self::SUCCESS;
    }

    protected function localizeExistingCatalog(string $catalogPath): int
    {
        if (! File::exists($catalogPath)) {
            $this->error('Không tìm thấy tệp storage/app/event-templates-catalog.json');

            return self::FAILURE;
        }

        $catalog = json_decode((string) File::get($catalogPath), true);
        if (! is_array($catalog)) {
            $this->error('Dữ liệu event-templates-catalog.json không hợp lệ.');

            return self::FAILURE;
        }

        $sourceByKey = [];
        $sourceByCategory = [];
        foreach ($this->sourceMap as $src) {
            $sourceByKey[$src['source']] = $src;
            $sourceByCategory[$src['category_slug']] ??= $src;
        }

        $categoryCounters = [];
        $updated = 0;

        foreach ($catalog as $slug => $item) {
            if (! is_array($item)) {
                continue;
            }

            $categorySlug = (string) ($item['category_slug'] ?? 'tet-nguyen-dan');
            $sourceCategory = (string) ($item['source_category'] ?? '');
            $src = $sourceByKey[$sourceCategory] ?? ($sourceByCategory[$categorySlug] ?? null);

            $index = $categoryCounters[$categorySlug] ?? 0;
            $categoryCounters[$categorySlug] = $index + 1;

            $item['name'] = $this->resolveVietnameseName($categorySlug, $index);
            $item['tagline'] = 'Thiết kế thiệp một trang hiện đại kèm phong bì mở nắp mềm mại và phông nền tinh tế chuẩn Mây Hẹn';

            if ($src !== null) {
                $item['seal_label'] = 'Chạm Mở '.$src['vi_prefix'];
                $item['host_name'] = $src['host'];
                $item['sub_host_name'] = $src['sub'];
                $item['event_badge'] = $src['badge'];
                $item['invitation_message'] = $src['msg'];
            }

            $catalog[$slug] = $item;
            $updated++;
        }

        File::put(
            $catalogPath,
            json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->info("Đã chuẩn hóa 100% tên Tiếng Việt (5-10 từ) cho {$updated} mẫu thiệp trong storage/app/event-templates-catalog.json.");

        return self::SUCCESS;
    }

    protected function resolveVietnameseName(string $categorySlug, int $index): string
    {
        $pool = $this->vietnameseNameBank[$categorySlug] ?? [];
        if (isset($pool[$index])) {
            return $pool[$index];
        }

        $fallbackByCategory = [
            'tet-nguyen-dan' => 'Thiệp Chúc Tết Xuân Sang Phú Quý An Khang',
            'tet-trung-thu' => 'Thiệp Tết Trung Thu Trăng Rằm Đoàn Viên Ấm Áp',
            'giang-sinh-nam-moi' => 'Thiệp Mời Giáng Sinh Ấm Áp Đón Chào Năm Mới',
            'day-thang-thoi-noi' => 'Thiệp Mời Thôi Nôi Mừng Thiên Thần Nhỏ Bình An',
            'sinh-nhat-tre-em' => 'Thiệp Mời Sinh Nhật Tuổi Thơ Ngọt Ngào Rực Rỡ',
            'sinh-nhat-nguoi-lon' => 'Thiệp Mời Sinh Nhật Thanh Lịch Đánh Dấu Tuổi Mới',
            'tan-gia-nha-moi' => 'Thiệp Mời Tân Gia Nhà Mới Sum Vầy Hạnh Phúc',
            'tat-nien-khai-truong' => 'Thiệp Mời Sự Kiện Doanh Nghiệp Khởi Sắc Thành Công',
            'tot-nghiep-thanh-tuu' => 'Thiệp Mời Lễ Tốt Nghiệp Rực Rỡ Vươn Tới Tương Lai',
            'ngay-le-chuc-mung' => 'Thiệp Chúc Mừng Yêu Thương Gửi Trao Kỷ Niệm Đẹp',
        ];

        return $fallbackByCategory[$categorySlug] ?? 'Thiệp Mời Sự Kiện Tinh Tế Sang Trọng Hiện Đại';
    }

    protected function fetchUrl(string $url): string
    {
        $process = new Process([
            'curl',
            '-sL',
            '--max-time',
            '20',
            '-A',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            $url,
        ]);
        $process->setTimeout(25);
        $process->run();

        if ($process->isSuccessful()) {
            return $process->getOutput();
        }

        return '';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function extractCardEdges(string $html): array
    {
        if (! preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $m)) {
            return [];
        }

        $data = json_decode($m[1], true);
        $rq = $data['props']['pageProps']['_apolloState']['ROOT_QUERY'] ?? [];
        if (! is_array($rq)) {
            return [];
        }

        foreach ($rq as $k => $v) {
            if (is_string($k) && str_contains($k, '"productType":"card"') && is_array($v) && ! empty($v['edges'])) {
                return $v['edges'];
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>|null  $artifact
     * @param  array<int, string>  $preferredKeys
     */
    protected function pickArtifactUrl(?array $artifact, array $preferredKeys): string
    {
        if (! is_array($artifact) || empty($artifact['asset']) || ! is_array($artifact['asset'])) {
            return '';
        }

        foreach ($artifact['asset'] as $rk => $rv) {
            if (is_string($rk) && str_starts_with($rk, 'renditions') && is_array($rv)) {
                foreach ($preferredKeys as $key) {
                    if (! empty($rv[$key]['url'])) {
                        return (string) $rv[$key]['url'];
                    }
                }
            }
        }

        return '';
    }

    /**
     * @param  array<int, array<string, mixed>>  $colors
     * @return array{palette: string, envelope_color: string, envelope_liner: string, backdrop: string}
     */
    protected function mapColorToMayHen(array $colors): array
    {
        $names = array_map(
            static fn (array $c): string => mb_strtolower((string) ($c['name'] ?? '')),
            $colors
        );

        if (in_array('green', $names, true)) {
            return ['palette' => 'sage-green', 'envelope_color' => 'forest-green', 'envelope_liner' => 'botanical-holly', 'backdrop' => 'silk-cream'];
        }
        if (in_array('red', $names, true) || in_array('burgundy', $names, true)) {
            return ['palette' => 'ruby', 'envelope_color' => 'crimson-red', 'envelope_liner' => 'gold-foil', 'backdrop' => 'silk-cream'];
        }
        if (in_array('pink', $names, true) || in_array('blush', $names, true)) {
            return ['palette' => 'dusty-rose', 'envelope_color' => 'blush-pink', 'envelope_liner' => 'berry-dots', 'backdrop' => 'blush-dream'];
        }
        if (in_array('purple', $names, true) || in_array('lavender', $names, true)) {
            return ['palette' => 'dusty-rose', 'envelope_color' => 'royal-violet', 'envelope_liner' => 'blush-stripes', 'backdrop' => 'blush-dream'];
        }
        if (in_array('blue', $names, true) || in_array('navy', $names, true) || in_array('black', $names, true)) {
            return ['palette' => 'royal-navy', 'envelope_color' => 'midnight-navy', 'envelope_liner' => 'starry-night', 'backdrop' => 'moonlight-navy'];
        }
        if (in_array('gold', $names, true) || in_array('yellow', $names, true) || in_array('orange', $names, true)) {
            return ['palette' => 'warm-amber', 'envelope_color' => 'imperial-gold', 'envelope_liner' => 'gold-foil', 'backdrop' => 'silk-cream'];
        }

        return ['palette' => 'warm-amber', 'envelope_color' => 'ivory-cream', 'envelope_liner' => 'gold-foil', 'backdrop' => 'silk-cream'];
    }

    protected function mapShapeToMayHenBorder(string $shape, string $categorySlug): string
    {
        return match (true) {
            str_contains($shape, 'oval') || str_contains($shape, 'scallop') => 'scalloped-wave',
            str_contains($shape, 'arch') => 'lantern-arch',
            $categorySlug === 'tet-nguyen-dan' => 'lunar-brocade',
            $categorySlug === 'giang-sinh-nam-moi' => 'candy-stripe',
            $categorySlug === 'day-thang-thoi-noi' || $categorySlug === 'sinh-nhat-tre-em' => 'scalloped-wave',
            $categorySlug === 'sinh-nhat-nguoi-lon' => 'minimal-editorial',
            default => 'gold-double',
        };
    }
}
