<?php

namespace App\Services;

use App\Models\EventInvitation;
use App\Models\User;
use Illuminate\Support\Str;

class EventInvitationService
{
    /**
     * 5 bảng màu chủ đạo của mặt thiệp & nút tương tác.
     *
     * @var array<string, array<string, string>>
     */
    public const PALETTES = [
        'ruby' => [
            'id' => 'ruby',
            'name' => 'Đỏ Hồng Phúc & Vàng Kim',
            'primary' => '#85182A',
            'accent' => '#CFA860',
            'bg' => '#FAF7F2',
            'surface' => '#FFFFFF',
            'ink' => '#1F1D1A',
        ],
        'dusty-rose' => [
            'id' => 'dusty-rose',
            'name' => 'Hồng Phấn Pastel & Kem Lụa',
            'primary' => '#A26B6B',
            'accent' => '#C5A880',
            'bg' => '#FFFDF9',
            'surface' => '#FFFFFF',
            'ink' => '#2C2824',
        ],
        'sage-green' => [
            'id' => 'sage-green',
            'name' => 'Xanh Rừng Thông & Lá Ô Liu',
            'primary' => '#1E4620',
            'accent' => '#C5A059',
            'bg' => '#F7FAF7',
            'surface' => '#FFFFFF',
            'ink' => '#1B261E',
        ],
        'royal-navy' => [
            'id' => 'royal-navy',
            'name' => 'Xanh Hoàng Gia & Ánh Bạc',
            'primary' => '#1B2A4A',
            'accent' => '#C5A880',
            'bg' => '#F8FAFC',
            'surface' => '#FFFFFF',
            'ink' => '#0F172A',
        ],
        'warm-amber' => [
            'id' => 'warm-amber',
            'name' => 'Vàng Champagne & Hổ Phách Ấm',
            'primary' => '#8C4724',
            'accent' => '#D4A373',
            'bg' => '#FFFBF5',
            'surface' => '#FFFFFF',
            'ink' => '#2C221E',
        ],
    ];

    /**
     * 8 Màu Vỏ Phong Bì 3D (Outer Envelope Colors — Chuẩn Paperless Post).
     *
     * @var array<string, array{id: string, name: string, hex: string, flap_hex: string}>
     */
    public const ENVELOPE_COLORS = [
        'crimson-red' => ['id' => 'crimson-red', 'name' => 'Đỏ Nhung Lễ Hội', 'hex' => '#9E1B22', 'flap_hex' => '#7D1218'],
        'forest-green' => ['id' => 'forest-green', 'name' => 'Xanh Rừng Thông', 'hex' => '#1B4D20', 'flap_hex' => '#123816'],
        'ivory-cream' => ['id' => 'ivory-cream', 'name' => 'Trắng Kem Mỹ Thuật', 'hex' => '#F4EFE6', 'flap_hex' => '#E5DEC9'],
        'blush-pink' => ['id' => 'blush-pink', 'name' => 'Hồng Phấn Pastel', 'hex' => '#F2C6C2', 'flap_hex' => '#E2ABA6'],
        'royal-violet' => ['id' => 'royal-violet', 'name' => 'Tím Violet Ngọt Ngào', 'hex' => '#8C44D8', 'flap_hex' => '#6E2FB5'],
        'espresso-brown' => ['id' => 'espresso-brown', 'name' => 'Nâu Gỗ Trầm Ấm', 'hex' => '#5C2C1D', 'flap_hex' => '#421D11'],
        'midnight-navy' => ['id' => 'midnight-navy', 'name' => 'Xanh Đêm Hoàng Gia', 'hex' => '#1B2A4A', 'flap_hex' => '#111C35'],
        'imperial-gold' => ['id' => 'imperial-gold', 'name' => 'Vàng Hoàng Kim Tết', 'hex' => '#C59B27', 'flap_hex' => '#A37E18'],
    ];

    /**
     * 8 Hoa Văn Lớp Lót Trong Phong Bì (Envelope Liner Patterns — Chuẩn Paperless Post).
     *
     * @var array<string, array{id: string, name: string}>
     */
    public const ENVELOPE_LINERS = [
        'gold-foil' => ['id' => 'gold-foil', 'name' => 'Nhũ Vàng Kim Hoàng Gia'],
        'candy-stripe' => ['id' => 'candy-stripe', 'name' => 'Sọc Kẻ Đỏ Kem Lễ Hội'],
        'tartan-plaid' => ['id' => 'tartan-plaid', 'name' => 'Caro Tartan Ấm Áp'],
        'botanical-holly' => ['id' => 'botanical-holly', 'name' => 'Hoa Lá Thông & Nơ Đỏ'],
        'berry-dots' => ['id' => 'berry-dots', 'name' => 'Chấm Bi Đỏ Trên Nền Kem'],
        'lunar-clouds' => ['id' => 'lunar-clouds', 'name' => 'Vân Mây & Hoa Mai Vàng Tết'],
        'starry-night' => ['id' => 'starry-night', 'name' => 'Bầu Trời Sao Đêm Trăng Rằm'],
        'blush-stripes' => ['id' => 'blush-stripes', 'name' => 'Sọc Hồng Phấn Thanh Lịch'],
    ];

    /**
     * 8 Kiểu Khung Viền Mặt Thiệp (Stationery Card Borders — Chuẩn Paperless Post).
     *
     * @var array<string, array{id: string, name: string}>
     */
    public const CARD_BORDERS = [
        'gold-double' => ['id' => 'gold-double', 'name' => 'Viền Chỉ Vàng Kép Cổ Điển'],
        'candy-stripe' => ['id' => 'candy-stripe', 'name' => 'Viền Sọc Kẹo Đỏ Hồng (Tinsel Stripe)'],
        'pine-garland' => ['id' => 'pine-garland', 'name' => 'Vòng Lá Thông & Nơ Đỏ (Garland)'],
        'scalloped-wave' => ['id' => 'scalloped-wave', 'name' => 'Khung Lượn Sóng Nghệ Thuật'],
        'lunar-brocade' => ['id' => 'lunar-brocade', 'name' => 'Viền Gấm Đỏ Góc Vân Mây Tết'],
        'lantern-arch' => ['id' => 'lantern-arch', 'name' => 'Khung Vòm Trăng Rằm & Hoa Sen'],
        'pastel-confetti' => ['id' => 'pastel-confetti', 'name' => 'Viền Pháo Giấy & Ngôi Sao Nhí'],
        'minimal-editorial' => ['id' => 'minimal-editorial', 'name' => 'Tạp Chí Hiện Đại Thoáng Đãng'],
    ];

    /**
     * 6 Phông Nền Sân Khấu Khi Mở Thiệp (Backdrops — Có Mặt Bàn Gỗ Sồi Bắc Âu chuẩn Video Paperless Post).
     *
     * @var array<string, array{id: string, name: string}>
     */
    public const BACKDROPS = [
        'silk-cream' => ['id' => 'silk-cream', 'name' => 'Gỗ Sồi Sáng & Lụa Kem (Chuẩn Paperless Post)'],
        'festive-velvet' => ['id' => 'festive-velvet', 'name' => 'Đỏ Nhung Tết & Lễ Hội'],
        'pine-forest' => ['id' => 'pine-forest', 'name' => 'Xanh Rừng Thông Giáng Sinh'],
        'moonlight-navy' => ['id' => 'moonlight-navy', 'name' => 'Đêm Trăng Rằm Ánh Vàng'],
        'blush-dream' => ['id' => 'blush-dream', 'name' => 'Gỗ Hồng Phấn Ngọt Ngào'],
        'warm-walnut' => ['id' => 'warm-walnut', 'name' => 'Gỗ Óc Chó Trầm Sang Trọng'],
    ];

    /**
     * 10 Danh mục Thiệp Sự Kiện, Mùa Lễ Hội & Thiệp Chúc Mừng chuẩn văn hóa Việt Nam (Tuyệt đối không chứa Thiệp Cưới).
     *
     * @return array<string, array<string, mixed>>
     */
    public function getCategories(): array
    {
        return [
            'tet-nguyen-dan' => [
                'slug' => 'tet-nguyen-dan',
                'name' => 'Thiệp Chúc Tết Nguyên Đán & Tiệc Tân Niên Khai Xuân',
                'short_name' => 'Tết & Tân Niên',
                'group' => 'holiday',
                'season_badge' => 'Cung Chúc Tân Xuân',
                'icon_symbol' => '🧧',
                'seo_title' => 'Tạo Thiệp Chúc Tết Nguyên Đán Online, Thiệp Mời Tân Niên Khai Xuân Đẹp',
                'seo_description' => 'Thiết kế thiệp chúc Tết Nguyên Đán, thiệp mời tiệc Tân Niên khai xuân đầu năm gửi đối tác, khách hàng và người thân. Có hiệu ứng mở phong bì gấm đỏ và lì xì VietQR.',
                'seo_keywords' => 'thiệp chúc tết online, tạo thiệp chúc tết nguyên đán, mẫu thiệp mời tân niên, thiệp khai xuân đầu năm, thiệp chúc mừng năm mới kèm lì xì',
                'hero_subtitle' => 'Gửi lời chúc An Khang Thịnh Vượng, Vạn Sự Như Ý đầu xuân hoặc thư mời Tiệc Tân Niên Khai Xuân kèm phong bao lì xì may mắn.',
                'cultural_intro' => 'Tết Nguyên Đán là dịp lễ lớn nhất trong năm của người Việt. Với Mây Hẹn, bạn có thể tạo cả Thiệp Chúc Tết gửi từ xa kèm mã QR lì xì lấy lộc đầu năm lẫn Thiệp Mời Tiệc Tân Niên, Họp Mặt Đầu Xuân cho gia đình và doanh nghiệp.',
                'field_labels' => [
                    'host_name' => 'Tên Gia Đình / Doanh Nghiệp Gửi Chúc Tết',
                    'sub_host_name' => 'Lời Chúc Đại Cát (Ví dụ: Cung Chúc Tân Xuân • An Khang Thịnh Vượng)',
                    'event_badge' => 'Dịp Lễ Tết (Ví dụ: Xuân Bính Ngọ • Tiệc Tân Niên Khai Xuân)',
                    'gift_title' => 'Phong Bao Lì Xì Đầu Xuân Lấy Lộc (VietQR)',
                ],
                'wording_presets' => [
                    [
                        'label' => 'Chúc Tết Đối Tác & Khách Hàng',
                        'badge' => 'CUNG CHÚC TÂN XUÂN • VẠN SỰ NHƯ Ý',
                        'sub' => 'Kính chúc Quý Đối Tác & Gia Đình Năm Mới An Khang Thịnh Vượng',
                        'msg' => 'Nhân dịp năm mới Xuân về, chúng tôi xin gửi lời cảm ơn chân thành nhất tới Quý khách hàng và Đối tác đã luôn tin tưởng đồng hành. Kính chúc Quý vị cùng gia đình một năm mới Dồi Dào Sức Khỏe, Vạn Sự Hanh Thông, Tài Lộc Viên Mãn!',
                    ],
                    [
                        'label' => 'Mời Tiệc Tân Niên Khai Xuân',
                        'badge' => 'TIỆC TÂN NIÊN KHAI XUÂN ĐẦU NĂM',
                        'sub' => 'Gặp mặt đầu xuân • Nâng ly chúc mừng năm mới đại thắng',
                        'msg' => 'Trong không khí rộn ràng của những ngày đầu xuân mới, trân trọng kính mời Quý vị tới dự buổi Tiệc Tân Niên Khai Xuân thân mật để cùng nâng ly chúc nhau một năm mới thuận buồm xuôi gió, gắn kết bền chặt.',
                    ],
                    [
                        'label' => 'Chúc Tết Ông Bà & Người Thân',
                        'badge' => 'MỪNG XUÂN ĐOÀN VIÊN • PHÚC LỘC THỌ TOÀN',
                        'sub' => 'Kính chúc Ông Bà, Cô Chú năm mới Bách Niên Giai Lão, Bình An Hạnh Phúc',
                        'msg' => 'Mùa xuân mới lại về bên hiên nhà, con cháu kính chúc Ông Bà và đại gia đình luôn mạnh khỏe,笑口常開 (luôn nở nụ cười), gia đạo bình an, sum vầy ấm áp bên con cháu.',
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Tôi chỉ muốn gửi Thiệp Chúc Tết kèm Lì Xì chứ không tổ chức tiệc thì làm thế nào?',
                        'a' => 'Rất đơn giản! Trong Tab 4 (Sự Kiện & Bản Đồ) của Studio, bạn chỉ cần gạt tắt công tắc "Hiển thị Thời gian & Địa điểm tổ chức tiệc". Tấm thiệp sẽ trở thành Thiệp Chúc Tết & Lì Xì thuần túy cực kỳ sang trọng.',
                    ],
                ],
            ],
            'tet-trung-thu' => [
                'slug' => 'tet-trung-thu',
                'name' => 'Thiệp Tết Trung Thu & Đêm Hội Trăng Rằm Đoàn Viên',
                'short_name' => 'Tết Trung Thu',
                'group' => 'holiday',
                'season_badge' => 'Tết Đoàn Viên',
                'icon_symbol' => '🥮',
                'seo_title' => 'Mẫu Thiệp Chúc Tết Trung Thu Online, Thiệp Mời Đêm Hội Trăng Rằm',
                'seo_description' => 'Tạo thiệp chúc Tết Trung Thu gửi khách hàng, đối tác và thiệp mời Đêm Hội Trăng Rằm, tiệc trà thưởng nguyệt ấm cúng theo phong cách Á Đông tinh tế.',
                'seo_keywords' => 'thiệp chúc tết trung thu, mẫu thiệp trung thu đẹp, thiệp mời đêm hội trăng rằm, thiệp trung thu gửi khách hàng, tạo thiệp trung thu online',
                'hero_subtitle' => 'Gửi gắm lời chúc Đoàn Viên viên mãn dưới ánh trăng rằm tháng Tám hoặc mời tham dự Đêm Hội Trăng Rằm, Tiệc Trà Thưởng Nguyệt.',
                'cultural_intro' => 'Tết Trung Thu (Rằm tháng Tám) là Tết Đoàn Viên của người Việt, là dịp để doanh nghiệp gửi thiệp tri ân khách hàng, đối tác cùng hộp bánh trung thu, và cũng là dịp tổ chức Đêm Hội Trăng Rằm cho các em nhỏ.',
                'field_labels' => [
                    'host_name' => 'Tên Đơn Vị / Gia Đình Gửi Thiệp Trung Thu',
                    'sub_host_name' => 'Thông điệp Đoàn Viên (Ví dụ: Nguyệt Viên Hoa Hảo • Đoàn Viên Viên Mãn)',
                    'event_badge' => 'Dịp lễ (Ví dụ: Tết Trung Thu Đoàn Viên / Đêm Hội Trăng Rằm)',
                    'gift_title' => 'Quà Tặng Đêm Trăng Rằm (VietQR)',
                ],
                'wording_presets' => [
                    [
                        'label' => 'Chúc Trung Thu Khách Hàng & Đối Tác',
                        'badge' => 'TẾT ĐOÀN VIÊN • TRĂNG RẰM VIÊN MÃN',
                        'sub' => 'Kính chúc Quý Khách Hàng & Gia Đình mùa Trung Thu ấm áp, hạnh phúc',
                        'msg' => 'Ánh trăng rằm tháng Tám tròn đầy là biểu tượng của sự sum vầy và thịnh vượng. Xin gửi tới Quý đối tác và gia đình lời chúc mùa Tết Đoàn Viên ngập tràn niềm vui, hạnh phúc vẹn tròn và thành công bền vững.',
                    ],
                    [
                        'label' => 'Mời Đêm Hội Trăng Rằm & Phá Cỗ',
                        'badge' => 'ĐÊM HỘI TRĂNG RẰM • RƯỚC ĐÈN PHÁ CỖ',
                        'sub' => 'Trân trọng kính mời các bé và phụ huynh tới chung vui',
                        'msg' => 'Hòa chung niềm vui Tết Trung Thu, trân trọng mời gia đình và các bé tới tham dự Đêm Hội Trăng Rằm để cùng rước đèn ông sao, xem múa lân sư rồng và phá cỗ trông trăng.',
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Thiệp Trung Thu có thể dùng để gửi kèm quà tặng cho đối tác doanh nghiệp không?',
                        'a' => 'Rất phù hợp. Bạn có thể cá nhân hóa tên từng đối tác trên link thiệp (?guest=Quý+Đối+Tác+...) để gửi qua Zalo hoặc Email kèm lời tri ân trang trọng.',
                    ],
                ],
            ],
            'giang-sinh-nam-moi' => [
                'slug' => 'giang-sinh-nam-moi',
                'name' => 'Thiệp Tiệc Giáng Sinh & Đếm Ngược Đón Năm Mới',
                'short_name' => 'Giáng Sinh & Năm Mới',
                'group' => 'holiday',
                'season_badge' => 'Mùa Lễ Hội An Lành',
                'icon_symbol' => '🎄',
                'seo_title' => 'Mẫu Thiệp Mời Tiệc Giáng Sinh (Christmas Party) & Chúc Mừng Năm Mới',
                'seo_description' => 'Bộ sưu tập thiệp mời tiệc Giáng Sinh, thiệp chúc Giáng Sinh an lành và tiệc Countdown đón năm mới với phong bì lót nhũ vàng, vòng lá thông và sọc lễ hội.',
                'seo_keywords' => 'thiệp mời tiệc giáng sinh, thiệp giáng sinh online, mẫu thiệp noel đẹp, thiệp chúc mừng giáng sinh và năm mới, thiệp mời christmas party',
                'hero_subtitle' => 'Những thiết kế mang đậm hơi thở mùa lễ hội: cây thông nhũ vàng, vòng nguyệt quế màu nước và đôi ly cocktail ấm áp bên người thân, bạn bè.',
                'cultural_intro' => 'Mùa Giáng Sinh (Noel 24/12) và Tết Dương Lịch (1/1) tại Việt Nam là dịp tuyệt vời cho những buổi tiệc tối ấm cúng tại gia đình, nhà hàng hoặc buổi gặp gỡ bạn bè thân thiết (Friendsmas & Countdown Party).',
                'field_labels' => [
                    'host_name' => 'Tên Chủ Tiệc / Người Gửi (Ví dụ: Gia đình Minh Tuấn & Thu Hà)',
                    'sub_host_name' => 'Chủ đề đêm tiệc (Ví dụ: Merry Christmas & Happy New Year)',
                    'event_badge' => 'Sự kiện (Ví dụ: Đêm Tiệc Giáng Sinh Ấm Áp • Christmas Dinner)',
                    'gift_title' => 'Hộp Quà Ông Già Noel (VietQR)',
                ],
                'wording_presets' => [
                    [
                        'label' => 'Mời Tiệc Tối Giáng Sinh Ấm Cúng',
                        'badge' => 'CHRISTMAS CHEER • ĐÊM TIỆC GIÁNG SINH',
                        'sub' => 'Cùng nâng ly rượu vang ấm và trao nhau những món quà bất ngờ',
                        'msg' => 'Mùa Giáng Sinh an lành đã gõ cửa! Vợ chồng mình thân mời bạn tới dự bữa tiệc tối Giáng Sinh ấm cúng tại nhà, cùng thưởng thức rượu vang nóng, nhạc Jazz mùa đông và trao nhau những lời chúc tốt đẹp nhất.',
                    ],
                    [
                        'label' => 'Thiệp Chúc Giáng Sinh An Lành Từ Xa',
                        'badge' => 'MERRY CHRISTMAS & HAPPY NEW YEAR',
                        'sub' => 'Chúc bạn và gia đình một mùa Giáng Sinh bình an, ngập tràn yêu thương',
                        'msg' => 'Chúc bạn cùng những người thân yêu một mùa Giáng Sinh ấm áp bên ánh nến lung linh và một năm mới tràn đầy năng lượng, may mắn và hạnh phúc!',
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Các mẫu thiệp Giáng Sinh có đổi được màu phong bì xanh thông hoặc đỏ nhung và lớp lót kẻ sọc không?',
                        'a' => 'Có đầy đủ! Trong Tab 2 (Phong Bì & Nền) của Studio, bạn có thể phối tự do giữa phong bì Xanh Rừng Thông, Đỏ Nhung với lớp lót Nhũ Vàng Kim, Sọc Kẹo Đỏ Kem hoặc Caro Tartan giống hệt phong cách Paperless Post.',
                    ],
                ],
            ],
            'day-thang-thoi-noi' => [
                'slug' => 'day-thang-thoi-noi',
                'name' => 'Thiệp Đầy Tháng & Thôi Nôi Cho Bé Yêu',
                'short_name' => 'Đầy Tháng & Thôi Nôi',
                'group' => 'family',
                'season_badge' => 'Mừng Thiên Thần Nhỏ',
                'icon_symbol' => '👶',
                'seo_title' => 'Tạo Thiệp Mời Đầy Tháng, Thiệp Thôi Nôi Online Đẹp Cho Bé Trai & Bé Gái',
                'seo_description' => 'Tự tạo thiệp mời đầy tháng (tròn 1 tháng), thiệp mời thôi nôi (tròn 1 tuổi) online cho bé. Hỗ trợ tải ảnh bé lên mặt thiệp, ghi tên ở nhà, bản đồ và lì xì VietQR.',
                'seo_keywords' => 'thiệp mời đầy tháng online, thiệp mời thôi nôi cho bé, mẫu thiệp thôi nôi đẹp, tạo thiệp đầy tháng cho bé, thiệp thôi nôi điện tử',
                'hero_subtitle' => 'Kỷ niệm cột mốc tròn 1 tháng tuổi (Đầy Tháng) và tròn 1 tuổi (Thôi Nôi) đầu đời của con yêu bằng tấm thiệp mời ngọt ngào, dễ thương.',
                'cultural_intro' => 'Lễ cúng Mụ Đầy Tháng và tiệc bốc đồ chọn nghề Thôi Nôi là truyền thống đẹp đẽ của mọi gia đình Việt. Bạn có thể tải trực tiếp bức ảnh đáng yêu nhất của bé lên mặt thiệp, kèm tên khai sinh và tên gọi thân mật ở nhà.',
                'field_labels' => [
                    'host_name' => 'Tên của Bé (Ví dụ: Bé Nguyễn Minh Khôi)',
                    'sub_host_name' => 'Tên ở nhà & Tên Ba Mẹ (Ví dụ: Bé Bơ • Con của Ba Tuấn & Mẹ Hà)',
                    'event_badge' => 'Nghi lễ (Ví dụ: Tiệc Mừng Thôi Nôi Tròn 1 Tuổi / Lễ Đầy Tháng)',
                    'gift_title' => 'Lì Xì Chúc Bé Hay Ăn Chóng Lớn (VietQR)',
                ],
                'wording_presets' => [
                    [
                        'label' => 'Lời Mời Tiệc Thôi Nôi (Bé Tròn 1 Tuổi)',
                        'badge' => 'TIỆC MỪNG THÔI NÔI • BÉ TRÒN 1 TUỔI',
                        'sub' => 'Trân trọng kính mời Ông Bà, Cô Chú tới dự tiệc mừng tuổi đầu đời của con',
                        'msg' => 'Tròn 365 ngày kể từ khoảnh khắc con cất tiếng khóc chào đời trong vòng tay yêu thương của đại gia đình. Ba mẹ trân trọng kính mời Ông Bà, Cô Chú và Anh Chị tới dự bữa tiệc ấm cúng mừng lễ Thôi Nôi của con.',
                    ],
                    [
                        'label' => 'Lời Mời Lễ Đầy Tháng (Bé Tròn 1 Tháng)',
                        'badge' => 'LỄ MỪNG ĐẦY THÁNG • CHÀO ĐÓN THIÊN THẦN NHỎ',
                        'sub' => 'Mừng thành viên nhí của gia đình tròn 1 tháng tuổi bình an',
                        'msg' => 'Gia đình nhỏ của chúng mình vô cùng hạnh phúc báo tin thiên thần nhỏ đã tròn một tháng tuổi. Kính mời Ông Bà, Cô Chú tới dùng bữa cơm thân mật mừng lễ Đầy Tháng và gửi lời chúc bình an cho bé.',
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Tôi có thể tải ảnh chụp thật của bé lên giữa mặt thiệp Đầy Tháng / Thôi Nôi được không?',
                        'a' => 'Có! Trong Tab 1 (Thiệp & Minh Họa), bạn chỉ cần bấm chọn ảnh của bé từ điện thoại hoặc máy tính là bức ảnh sẽ hiển thị ngay trong khung vòm nghệ thuật trên mặt thiệp.',
                    ],
                ],
            ],
            'sinh-nhat-tre-em' => [
                'slug' => 'sinh-nhat-tre-em',
                'name' => 'Thiệp Mời Sinh Nhật Trẻ Em (Bé Trai & Bé Gái)',
                'short_name' => 'Sinh Nhật Trẻ Em',
                'group' => 'family',
                'season_badge' => 'Thế Giới Tuổi Thơ',
                'icon_symbol' => '🎈',
                'seo_title' => 'Mẫu Thiệp Mời Sinh Nhật Cho Bé Trai, Bé Gái Dễ Thương Online',
                'seo_description' => 'Tạo thiệp mời sinh nhật cho bé từ 2 đến 12 tuổi với màu sắc tươi sáng, pháo giấy, lâu đài cổ tích và phi hành gia nhí. Có bản đồ, đếm ngược và xác nhận số bạn nhỏ tham dự.',
                'seo_keywords' => 'thiệp mời sinh nhật cho bé, mẫu thiệp sinh nhật bé gái, mẫu thiệp sinh nhật bé trai, tạo thiệp mời sinh nhật trẻ em online',
                'hero_subtitle' => 'Dành riêng cho tiệc sinh nhật tuổi thơ đầy ắp tiếng cười của các bé với phong bì sắc màu, pháo giấy và khung ảnh kỷ niệm.',
                'cultural_intro' => 'Khác với tiệc sinh nhật người lớn, tiệc sinh nhật của các bé cần sự rực rỡ, hồn nhiên và có phần xác nhận số lượng bạn nhỏ đi kèm phụ huynh để chuẩn bị quà mang về (goodie bags) chu đáo.',
                'field_labels' => [
                    'host_name' => 'Tên Bé & Số Tuổi (Ví dụ: Sinh Nhật 5 Tuổi Bé Bảo Ngọc)',
                    'sub_host_name' => 'Tên thân mật ở nhà (Ví dụ: Công Chúa Nhỏ Bé Dâu Tây)',
                    'event_badge' => 'Chủ đề bữa tiệc (Ví dụ: Bữa Tiệc Sinh Nhật Cổ Tích)',
                    'gift_title' => 'Quà Mừng Tuổi Mới Cho Bé (VietQR)',
                ],
                'wording_presets' => [
                    [
                        'label' => 'Mời Các Bạn Nhỏ & Phụ Huynh Dự Sinh Nhật Bé',
                        'badge' => 'HAPPY BIRTHDAY • BỮA TIỆC TUỔI THƠ',
                        'sub' => 'Cùng thổi nến, ăn bánh kem và tham gia trò chơi vui nhộn!',
                        'msg' => 'Loa loa loa! Tháng này con chính thức thêm một tuổi mới rồi ạ. Ba mẹ và con thân mời Cô Chú cùng các bạn nhỏ tới dự bữa tiệc sinh nhật thật vui với bánh kem, chú hề vặn bóng và thật nhiều phần quà dễ thương!',
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Làm sao để biết phụ huynh dẫn theo mấy bé đi dự sinh nhật?',
                        'a' => 'Biểu mẫu RSVP trên thiệp cho phép khách mời chọn rõ tổng số người tham dự và ghi chú số bé đi cùng để gia đình chuẩn bị phần ăn và quà cho các bé chính xác.',
                    ],
                ],
            ],
            'sinh-nhat-nguoi-lon' => [
                'slug' => 'sinh-nhat-nguoi-lon',
                'name' => 'Thiệp Sinh Nhật Người Lớn & Đại Lễ Mừng Thọ',
                'short_name' => 'Sinh Nhật & Mừng Thọ',
                'group' => 'family',
                'season_badge' => 'Dấu Ấn Tuổi Vàng',
                'icon_symbol' => '🥂',
                'seo_title' => 'Tạo Thiệp Mời Sinh Nhật Sang Trọng (Adult Birthday) & Thiệp Mừng Thọ Ông Bà',
                'seo_description' => 'Thiết kế thiệp mời tiệc sinh nhật tuổi 18, 25, 30, 40, 50 phong cách Cocktail Editorial và thiệp mời Lễ Mừng Thọ ông bà cha mẹ trang trọng.',
                'seo_keywords' => 'thiệp mời sinh nhật online, mẫu thiệp mời sinh nhật sang trọng, thiệp mời mừng thọ ông bà, tạo thiệp sinh nhật người lớn',
                'hero_subtitle' => 'Từ đêm tiệc Cocktail tuổi mới thời thượng bên hội bạn thân đến Đại lễ Mừng Thọ Thượng Thọ trang nghiêm của ông bà, cha mẹ.',
                'cultural_intro' => 'Bộ sưu tập kết hợp cả phong cách Tạp chí hiện đại (Editorial Cocktail Night như mẫu "A Tini Bit Older" nổi tiếng trên Paperless Post) lẫn phong cách Truyền thống hiếu nghĩa dành cho Lễ Mừng Thọ 60, 70, 80, 90 tuổi tại Việt Nam.',
                'field_labels' => [
                    'host_name' => 'Tên Nhân Vật Chính (Ví dụ: Trần Thu Hà / Cụ Ông Nguyễn Văn Phúc)',
                    'sub_host_name' => 'Cột mốc tuổi & Chủ đề (Ví dụ: Kỷ Niệm Tuổi 30 • Golden Cocktail Night)',
                    'event_badge' => 'Buổi tiệc (Ví dụ: Birthday Dinner / Đại Lễ Mừng Thọ 80 Tuổi)',
                    'gift_title' => 'Gửi Quà Chúc Mừng (VietQR)',
                ],
                'wording_presets' => [
                    [
                        'label' => 'Tiệc Sinh Nhật Tối Thân Mật (Private Dinner / Cocktail)',
                        'badge' => 'A LITTLE BIT OLDER • BIRTHDAY DINNER & DRINKS',
                        'sub' => 'Một buổi tối nâng ly ấm cúng bên những người bạn thân thiết nhất',
                        'msg' => 'Thêm một tuổi mới lại gõ cửa! Điều mình mong chờ nhất là được cùng những người bạn thương quý ngồi lại bên nhau, nâng ly cocktail, thưởng thức bữa tối ngon lành và lưu giữ thêm một kỷ niệm đẹp.',
                    ],
                    [
                        'label' => 'Thư Mời Đại Lễ Mừng Thọ Ông Bà / Cha Mẹ',
                        'badge' => 'ĐẠI LỄ MỪNG THỌ • PHÚC NHƯ ĐÔNG HẢI, THỌ TỶ NAM SƠN',
                        'sub' => 'Con cháu thành tâm tổ chức lễ Mừng Thọ báo hiếu đấng sinh thành',
                        'msg' => 'Cây có cội mới trổ cành xanh lá, Nước có nguồn mới bủa khắp rạch sông. Nhân dịp Ông Bà bước sang tuổi Thượng Thọ, đại gia đình chúng tôi trân trọng kính mời Quý vị tới dự buổi tiệc Mừng Thọ ấm cúng cùng gia đình.',
                    ],
                ],
                'faqs' => [],
            ],
            'tan-gia-nha-moi' => [
                'slug' => 'tan-gia-nha-moi',
                'name' => 'Thiệp Mời Tân Gia & Mừng Tổ Ấm Mới',
                'short_name' => 'Tân Gia Nhà Mới',
                'group' => 'family',
                'season_badge' => 'An Cư Lạc Nghiệp',
                'icon_symbol' => '🏡',
                'seo_title' => 'Mẫu Thiệp Mời Tân Gia Nhà Mới Online Có Bản Đồ Google Maps',
                'seo_description' => 'Tạo thiệp mời tân gia nhà mới, căn hộ mới trang trọng. Tích hợp bản đồ Google Maps chỉ đường 1 chạm tới tận cửa, hướng dẫn đỗ xe và RSVP chốt mâm.',
                'seo_keywords' => 'thiệp mời tân gia online, mẫu thiệp mời tân gia nhà mới, cách viết thiệp mời tân gia, thiệp tân gia có bản đồ',
                'hero_subtitle' => 'Chia sẻ niềm vui hoàn thiện tổ ấm mới cùng họ hàng, bạn bè, đồng nghiệp với bản đồ dẫn đường 1 chạm cực kỳ tiện lợi.',
                'cultural_intro' => 'Ngôi nhà mới là thành quả ngọt ngào của gia đình. Tấm thiệp mời Tân Gia giúp khách mời nắm rõ địa chỉ mới, sảnh chung cư hoặc vị trí đỗ ô tô/xe máy mà không sợ lạc đường.',
                'field_labels' => [
                    'host_name' => 'Tên Gia Chủ (Ví dụ: Gia đình Minh Tuấn & Thu Hà)',
                    'sub_host_name' => 'Lời đề tặng (Ví dụ: Trân trọng kính mời Quý khách tới tham quan tổ ấm mới)',
                    'event_badge' => 'Buổi lễ (Ví dụ: Tiệc Mừng Tân Gia • An Cư Lạc Nghiệp)',
                    'gift_title' => 'Mừng Tân Gia Gia Chủ (VietQR)',
                ],
                'wording_presets' => [
                    [
                        'label' => 'Mời Tiệc Tân Gia Ấm Cúng Tại Tư Gia',
                        'badge' => 'LỄ MỪNG TÂN GIA • AN CƯ LẠC NGHIỆP',
                        'sub' => 'Trân trọng kính mời Quý khách & Gia đình tới chung vui bữa cơm thân mật',
                        'msg' => 'Sau thời gian ấp ủ và vun đắp, gia đình chúng mình đã hoàn thiện ngôi nhà nhỏ ấm cúng. Vợ chồng mình trân trọng kính mời bạn và gia đình tới tham quan tổ ấm mới và dùng bữa cơm thân mật cùng gia đình.',
                    ],
                ],
                'faqs' => [],
            ],
            'tat-nien-khai-truong' => [
                'slug' => 'tat-nien-khai-truong',
                'name' => 'Thiệp Tất Niên Công Ty (YEP) & Lễ Khai Trương',
                'short_name' => 'Tất Niên & Khai Trương',
                'group' => 'corporate',
                'season_badge' => 'Sự Kiện Doanh Nghiệp',
                'icon_symbol' => '🏆',
                'seo_title' => 'Tạo Thiệp Mời Tất Niên Công Ty (Year End Party), Thiệp Mời Khai Trương',
                'seo_description' => 'Mẫu thiệp mời tiệc Tất Niên cuối năm (Year End Party), Gala Dinner và Thiệp Mời Lễ Khai Trương cửa hàng, showroom, văn phòng chuyên nghiệp.',
                'seo_keywords' => 'thiệp mời tất niên công ty, mẫu thiệp mời year end party, thiệp mời khai trương online, mẫu thiệp khai trương hồng phát',
                'hero_subtitle' => 'Giải pháp thiệp mời sự kiện chuyên nghiệp cho doanh nghiệp, cửa hàng & studio: Tiệc Tất Niên (YEP), Lễ Khai Trương Hồng Phát và Hội Nghị Khách Hàng.',
                'cultural_intro' => 'Thay vì gửi ảnh tĩnh rời rạc, thiệp mời sự kiện doanh nghiệp trên Mây Hẹn cung cấp đầy đủ kịch bản chương trình (Agenda), Dresscode, bản đồ tới trung tâm hội nghị và thống kê danh sách khách VIP xác nhận tham dự (RSVP).',
                'field_labels' => [
                    'host_name' => 'Tên Doanh Nghiệp / Thương Hiệu Tổ Chức',
                    'sub_host_name' => 'Chủ đề sự kiện (Ví dụ: Gala Tất Niên 2026 / Lễ Khai Trương Showroom Mới)',
                    'event_badge' => 'Hạng mục sự kiện (Ví dụ: YEAR END PARTY / GRAND OPENING)',
                    'gift_title' => 'Mừng Khai Trương Hồng Phát (VietQR)',
                ],
                'wording_presets' => [
                    [
                        'label' => 'Thư Mời Gala Tất Niên Cuối Năm (Year End Party)',
                        'badge' => 'YEAR END PARTY • VƯƠN TẦM RỰC RỠ',
                        'sub' => 'Đêm tiệc tri ân và vinh danh tập thể & Quý đối tác',
                        'msg' => 'Khép lại một năm nỗ lực không ngừng nghỉ, Ban Lãnh Đạo công ty trân trọng kính mời Quý vị tới tham dự đêm Gala Tất Niên để cùng nhìn lại những cột mốc tự hào, vinh danh cá nhân xuất sắc và nâng ly đón chào năm mới bứt phá.',
                    ],
                    [
                        'label' => 'Thư Mời Lễ Khai Trương (Grand Opening)',
                        'badge' => 'LỄ KHAI TRƯƠNG • KHAI TRƯƠNG HỒNG PHÁT',
                        'sub' => 'Trân trọng kính mời Quý khách & Đối tác tới dự lễ cắt băng khai trương',
                        'msg' => 'Đánh dấu bước phát triển mới của thương hiệu, chúng tôi trân trọng kính mời Quý vị tới tham dự Lễ Khai Trương cơ sở mới, cùng nâng ly chúc mừng và trải nghiệm không gian dịch vụ cùng những phần quà tri ân đặc biệt.',
                    ],
                ],
                'faqs' => [],
            ],
            'tot-nghiep-thanh-tuu' => [
                'slug' => 'tot-nghiep-thanh-tuu',
                'name' => 'Thiệp Mời Lễ Tốt Nghiệp & Vinh Danh Thành Tựu',
                'short_name' => 'Lễ Tốt Nghiệp',
                'group' => 'milestone',
                'season_badge' => 'Chạm Tới Ước Mơ',
                'icon_symbol' => '🎓',
                'seo_title' => 'Tạo Thiệp Mời Lễ Tốt Nghiệp Đại Học Online Đẹp & Ấn Tượng',
                'seo_description' => 'Thiết kế thiệp mời lễ tốt nghiệp đại học, thạc sĩ, cấp 3 để mời gia đình, thầy cô và bạn bè tới dự lễ trao bằng và chụp ảnh kỷ niệm tại trường.',
                'seo_keywords' => 'thiệp mời lễ tốt nghiệp, tạo thiệp mời tốt nghiệp đại học, mẫu thiệp tốt nghiệp đẹp, lời mời dự lễ tốt nghiệp',
                'hero_subtitle' => 'Mời bố mẹ, người thương và bạn bè tới chứng kiến khoảnh khắc khoác áo cử nhân, nhận bằng tốt nghiệp với bản đồ vị trí hội trường rõ ràng.',
                'cultural_intro' => 'Trong ngày lễ tốt nghiệp đông đúc tại khuôn viên trường đại học, tấm thiệp có gắn sẵn giờ ra khỏi hội trường, vị trí sân chụp ảnh và số điện thoại liên hệ giúp người thân và bạn bè tìm thấy tân cử nhân nhanh nhất.',
                'field_labels' => [
                    'host_name' => 'Tên Tân Cử Nhân / Thạc Sĩ (Ví dụ: Tân Cử Nhân Nguyễn Minh Anh)',
                    'sub_host_name' => 'Trường & Chuyên ngành (Ví dụ: Đại học Ngoại Thương • Khóa K60)',
                    'event_badge' => 'Buổi lễ (Ví dụ: Lễ Trao Bằng Tốt Nghiệp • Graduation Ceremony)',
                    'gift_title' => 'Quà Chúc Mừng Tân Cử Nhân (VietQR)',
                ],
                'wording_presets' => [
                    [
                        'label' => 'Mời Gia Đình & Bạn Bè Tới Chụp Ảnh Lễ Tốt Nghiệp',
                        'badge' => 'GRADUATION CEREMONY • LỄ TỐT NGHIỆP',
                        'sub' => 'Khép lại 4 năm thanh xuân giảng đường, mở ra hành trình mới',
                        'msg' => 'Hành trình 4 năm đại học của mình đã chính thức cán đích! Mình rất mong có sự hiện diện của gia đình và những người bạn yêu quý nhất trong buổi lễ trao bằng để cùng chụp những bức ảnh kỷ niệm thật đẹp tại sân trường.',
                    ],
                ],
                'faqs' => [],
            ],
            'ngay-le-chuc-mung' => [
                'slug' => 'ngay-le-chuc-mung',
                'name' => 'Thiệp Chúc Mừng 8/3, 20/10, 20/11 & Kỷ Niệm Ngày Cưới',
                'short_name' => '8/3, 20/10, 20/11 & Kỷ Niệm',
                'group' => 'greeting',
                'season_badge' => 'Tri Ân & Yêu Thương',
                'icon_symbol' => '💐',
                'seo_title' => 'Tạo Thiệp Chúc Mừng 8/3, 20/10, Thiệp Tri Ân Thầy Cô 20/11 & Kỷ Niệm Ngày Cưới',
                'seo_description' => 'Bộ sưu tập thiệp chúc mừng Quốc tế Phụ nữ 8/3, Phụ nữ Việt Nam 20/10, Ngày Nhà giáo Việt Nam 20/11 và Thiệp Mời Kỷ Niệm Ngày Cưới tinh tế.',
                'seo_keywords' => 'thiệp chúc mừng 8/3 online, thiệp chúc mừng 20/10, thiệp chúc mừng 20/11 ngày nhà giáo việt nam, thiệp mời kỷ niệm ngày cưới',
                'hero_subtitle' => 'Gửi trọn tâm tình trong những ngày lễ tri ân đặc biệt tại Việt Nam: Ngày Phụ Nữ 8/3 & 20/10, Ngày Nhà Giáo 20/11 và Kỷ Niệm Ngày Chung Đôi.',
                'cultural_intro' => 'Phù hợp cho cả cá nhân gửi tặng mẹ, vợ, cô giáo lẫn doanh nghiệp gửi thiệp chúc mừng 8/3, 20/10 tới toàn bộ nữ nhân sự và khách hàng nữ, hoặc tổ chức bữa tối Kỷ Niệm Ngày Cưới.',
                'field_labels' => [
                    'host_name' => 'Tên Người Nhận / Cặp Đôi Kỷ Niệm',
                    'sub_host_name' => 'Lời đề tặng (Ví dụ: Gửi người phụ nữ tuyệt vời nhất / Kính tặng Thầy Cô)',
                    'event_badge' => 'Dịp kỷ niệm (Ví dụ: Chúc Mừng Ngày 8/3 & 20/10 / Tri Ân 20/11 / Kỷ Niệm Ngày Cưới)',
                    'gift_title' => 'Quà Tặng Yêu Thương & Tri Ân (VietQR)',
                ],
                'wording_presets' => [
                    [
                        'label' => 'Lời Chúc Tặng Phái Đẹp Ngày 8/3 & 20/10',
                        'badge' => 'HAPPY WOMEN’S DAY • TÔN VINH PHÁI ĐẸP',
                        'sub' => 'Chúc một nửa thế giới luôn rạng rỡ, kiêu hãnh và hạnh phúc',
                        'msg' => 'Nhân ngày đặc biệt tôn vinh phái đẹp, xin gửi tới bạn những đóa hoa tươi thắm cùng lời chúc luôn xinh đẹp rạng ngời, bình yên trong tâm hồn và ngập tràn yêu thương mỗi ngày.',
                    ],
                    [
                        'label' => 'Lời Tri Ân Thầy Cô Ngày Nhà Giáo Việt Nam 20/11',
                        'badge' => 'CHÀO MỪNG NGÀY NHÀ GIÁO VIỆT NAM 20/11',
                        'sub' => 'Kính gửi Thầy Cô lời tri ân sâu sắc nhất từ các thế hệ học trò',
                        'msg' => 'Nhân ngày Nhà giáo Việt Nam 20/11, chúng em xin kính chúc Thầy Cô luôn dồi dào sức khỏe, giữ mãi ngọn lửa nhiệt huyết trên bục giảng và gặt hái thật nhiều niềm vui trong sự nghiệp trồng người.',
                    ],
                    [
                        'label' => 'Thư Mời Tiệc Kỷ Niệm Ngày Cưới (1 Năm, 5 Năm, 10 Năm)',
                        'badge' => 'WEDDING ANNIVERSARY • KỶ NIỆM NGÀY CHUNG ĐÔI',
                        'sub' => 'Hành trình yêu thương và gắn bó bền chặt qua năm tháng',
                        'msg' => 'Năm tháng đi qua càng làm sâu sắc thêm lời hẹn ước ngày hôm ấy. Vợ chồng chúng mình trân trọng kính mời những người thân thương nhất tới dự bữa tối kỷ niệm ngày cưới ấm cúng cùng gia đình.',
                    ],
                ],
                'faqs' => [],
            ],
        ];
    }

    /**
     * Lấy thông tin 1 danh mục theo slug.
     *
     * @return array<string, mixed>|null
     */
    public function getCategory(string $categorySlug): ?array
    {
        $categories = $this->getCategories();

        return $categories[$categorySlug] ?? null;
    }

    /**
     * Cache tĩnh trong RAM worker cho kho mẫu thiệp đã cào & chuyển hóa từ Paperless Post.
     *
     * @var array<string, array<string, mixed>>|null
     */
    protected static ?array $memoizedImportedTemplates = null;

    public static function clearImportedTemplatesCache(): void
    {
        self::$memoizedImportedTemplates = null;
    }

    /**
     * Nạp các mẫu thiệp đã cào từ Paperless Post (lưu tại storage/app/event-templates-catalog.json).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function loadImportedTemplates(): array
    {
        if (self::$memoizedImportedTemplates !== null) {
            return array_values(self::$memoizedImportedTemplates);
        }

        $catalogPath = storage_path('app/event-templates-catalog.json');
        if (! is_file($catalogPath)) {
            self::$memoizedImportedTemplates = [];

            return [];
        }

        $decoded = json_decode((string) file_get_contents($catalogPath), true);
        if (! is_array($decoded)) {
            self::$memoizedImportedTemplates = [];

            return [];
        }

        self::$memoizedImportedTemplates = $decoded;

        return array_values($decoded);
    }

    /**
     * Bộ sưu tập Mẫu Thiệp Sự Kiện, Lễ Hội & Chúc Mừng (30 mẫu bản địa cốt lõi + toàn bộ kho mẫu chuyển hóa từ Paperless Post).
     *
     * @return array<string, array<string, mixed>>
     */
    public function getTemplates(): array
    {
        $rawTemplates = [
            // ================= 1. TẾT NGUYÊN ĐÁN & TÂN NIÊN (3 MẪU) =================
            [
                'slug' => 'tet-xuan-phu-quy-mai-vang',
                'category_slug' => 'tet-nguyen-dan',
                'name' => 'Thiệp Chúc Tết Xuân Phú Quý Mai Vàng',
                'tagline' => 'Thiệp chúc Tết Nguyên Đán và lì xì đầu xuân với phong bì đỏ nhung lót vân mây hoa mai vàng',
                'card_mode' => 'greeting',
                'default_palette' => 'ruby',
                'envelope_color' => 'crimson-red',
                'envelope_liner' => 'lunar-clouds',
                'card_border' => 'lunar-brocade',
                'artwork_motif' => 'tet-blossom',
                'backdrop' => 'festive-velvet',
                'seal_icon' => '🧧',
                'seal_label' => 'Chạm Mở Thiệp Xuân & Lộc Đầu Năm',
                'host_name' => 'Gia Đình Minh Tuấn & Thu Hà',
                'sub_host_name' => 'Kính chúc Năm Mới An Khang Thịnh Vượng • Vạn Sự Như Ý',
                'event_badge' => 'CUNG CHÚC TÂN XUÂN • XUÂN BÍNH NGỌ',
                'invitation_message' => 'Nhân dịp Xuân mới gõ cửa, gia đình chúng mình xin gửi tới Ông Bà, Cô Chú và những người thân thương lời chúc một năm mới Dồi Dào Sức Khỏe, Gia Đạo Bình An, Tài Lộc Hanh Thông, Vạn Sự Cát Tường!',
                'show_datetime_venue' => false,
                'enable_gift_qr' => true,
            ],
            [
                'slug' => 'tan-nien-khai-xuan-nhu-y',
                'category_slug' => 'tet-nguyen-dan',
                'name' => 'Thiệp Mời Tiệc Tân Niên Khai Xuân Như Ý',
                'tagline' => 'Thiệp mời tiệc Tân Niên, họp mặt đầu xuân cho doanh nghiệp và đại gia đình',
                'card_mode' => 'invitation',
                'default_palette' => 'ruby',
                'envelope_color' => 'imperial-gold',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'gold-double',
                'artwork_motif' => 'tet-swallows',
                'backdrop' => 'silk-cream',
                'seal_icon' => '🌸',
                'seal_label' => 'Chạm Mở Thư Mời Tân Niên',
                'host_name' => 'Tập Thể & Ban Lãnh Đạo Mây Hẹn',
                'sub_host_name' => 'Tiệc Khai Xuân Đón Lộc • Thuận Buồm Xuôi Gió',
                'event_badge' => 'TIỆC TÂN NIÊN KHAI XUÂN ĐẦU NĂM',
                'invitation_message' => 'Khởi đầu một năm mới với khí thế và vận hội mới, trân trọng kính mời Quý vị tới tham dự buổi Tiệc Tân Niên Khai Xuân để cùng nâng ly chúc mừng năm mới đại thắng và nhận phong bao lì xì may mắn.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'tet-doan-vien-sum-vay',
                'category_slug' => 'tet-nguyen-dan',
                'name' => 'Thiệp Mời Bữa Cơm Tết Đoàn Viên Sum Vầy',
                'tagline' => 'Khung viền sọc lễ hội kết hợp phong bì đỏ lót họa tiết kẻ ấm áp cho bữa tiệc sum họp Tết',
                'card_mode' => 'invitation',
                'default_palette' => 'warm-amber',
                'envelope_color' => 'crimson-red',
                'envelope_liner' => 'tartan-plaid',
                'card_border' => 'scalloped-wave',
                'artwork_motif' => 'tet-blossom',
                'backdrop' => 'silk-cream',
                'seal_icon' => '🏮',
                'seal_label' => 'Chạm Mở Lời Mời Sum Họp Tết',
                'host_name' => 'Đại Gia Đình Nội Ngoại',
                'sub_host_name' => 'Bữa Cơm Sum Họp Đầu Xuân • Gắn Kết Yêu Thương',
                'event_badge' => 'HỌP MẶT ĐẦU XUÂN NĂM MỚI',
                'invitation_message' => 'Không gì quý giá hơn khoảnh khắc cả đại gia đình quây quần bên mâm cỗ ngày Tết. Thân mời các thành viên trong gia đình về tụ họp đông đủ để cùng chúc Tết ông bà và chụp ảnh kỷ niệm đầu năm.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => true,
            ],

            // ================= 2. TẾT TRUNG THU & ĐÊM HỘI TRĂNG RẰM (3 MẪU) =================
            [
                'slug' => 'trung-thu-nguyet-vu-doan-vien',
                'category_slug' => 'tet-trung-thu',
                'name' => 'Thiệp Chúc Tết Trung Thu Trăng Vàng Sen Ngọc',
                'tagline' => 'Thiệp chúc Tết Trung Thu sang trọng gửi khách hàng, đối tác với phong bì xanh đêm sao',
                'card_mode' => 'greeting',
                'default_palette' => 'royal-navy',
                'envelope_color' => 'midnight-navy',
                'envelope_liner' => 'starry-night',
                'card_border' => 'lantern-arch',
                'artwork_motif' => 'moon-lotus',
                'backdrop' => 'moonlight-navy',
                'seal_icon' => '🥮',
                'seal_label' => 'Chạm Mở Thiệp Tết Đoàn Viên',
                'host_name' => 'Công Ty TNHH Công Nghệ Mây Hẹn',
                'sub_host_name' => 'Kính chúc Quý Đối Tác & Gia Đình mùa Trăng Rằm Viên Mãn',
                'event_badge' => 'TẾT TRUNG THU • NGUYỆT VIÊN HOA HẢO',
                'invitation_message' => 'Dưới ánh trăng rằm tháng Tám sáng trong, chúng tôi xin gửi tới Quý khách hàng và Đối tác lời tri ân sâu sắc cùng lời chúc một mùa Tết Đoàn Viên an yên, hạnh phúc viên mãn bên những người thân yêu.',
                'show_datetime_venue' => false,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'trung-thu-dem-hoi-trang-ram',
                'category_slug' => 'tet-trung-thu',
                'name' => 'Thiệp Mời Đêm Hội Trăng Rằm Phá Cỗ Trung Thu',
                'tagline' => 'Thiệp mời Đêm Hội Trung Thu, rước đèn ông sao và múa lân cho thiếu nhi và khu dân cư',
                'card_mode' => 'invitation',
                'default_palette' => 'ruby',
                'envelope_color' => 'crimson-red',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'lunar-brocade',
                'artwork_motif' => 'moon-lantern',
                'backdrop' => 'festive-velvet',
                'seal_icon' => '🏮',
                'seal_label' => 'Chạm Mở Thiệp Đêm Hội Trăng Rằm',
                'host_name' => 'Ban Tổ Chức Đêm Hội Trăng Rằm',
                'sub_host_name' => 'Rước đèn ông sao • Xem múa lân • Phá cỗ cùng Chị Hằng & Chú Cuội',
                'event_badge' => 'ĐÊM HỘI TRĂNG RẰM • RẰM THÁNG TÁM',
                'invitation_message' => 'Trân trọng kính mời Quý phụ huynh cùng các bé tới tham dự chương trình Đêm Hội Trăng Rằm để các con được hòa mình vào tiếng trống lân rộn rã, rước đèn lung linh và nhận quà Trung Thu.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'trung-thu-tiec-tra-thuong-nguyet',
                'category_slug' => 'tet-trung-thu',
                'name' => 'Thiệp Mời Tiệc Trà Thưởng Nguyệt Đêm Rằm Tháng Tám',
                'tagline' => 'Thiệp mời buổi tiệc trà tối thưởng trăng, nếm bánh nướng bánh dẻo bên gia đình',
                'card_mode' => 'invitation',
                'default_palette' => 'warm-amber',
                'envelope_color' => 'espresso-brown',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'gold-double',
                'artwork_motif' => 'moon-lotus',
                'backdrop' => 'warm-walnut',
                'seal_icon' => '🍵',
                'seal_label' => 'Chạm Mở Thư Mời Thưởng Nguyệt',
                'host_name' => 'Gia Đình Minh Tuấn & Thu Hà',
                'sub_host_name' => 'Thưởng trà sen • Nếm bánh thu • Ngắm trăng rằm tháng Tám',
                'event_badge' => 'TIỆC TRÀ THƯỞNG NGUYỆT ĐOÀN VIÊN',
                'invitation_message' => 'Đêm rằm tháng Tám gió thu mát lành, gia đình mình thân mời bạn ghé sân vườn dùng chén trà sen ấm, thưởng thức bánh trung thu truyền thống và hàn huyên dưới ánh trăng tròn.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],

            // ================= 3. GIÁNG SINH & ĐÓN NĂM MỚI (3 MẪU) =================
            [
                'slug' => 'giang-sinh-thong-xanh-nhu-vang',
                'category_slug' => 'giang-sinh-nam-moi',
                'name' => 'Thiệp Mời Giáng Sinh Cây Thông Xanh Ánh Sao Vàng',
                'tagline' => 'Cây thông Giáng Sinh đính sao vàng trên nền viền sọc kẻ cùng phong bì đỏ lót nhũ vàng kim',
                'card_mode' => 'invitation',
                'default_palette' => 'sage-green',
                'envelope_color' => 'crimson-red',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'candy-stripe',
                'artwork_motif' => 'christmas-tree',
                'backdrop' => 'pine-forest',
                'seal_icon' => '🎄',
                'seal_label' => 'Chạm Mở Thiệp Giáng Sinh',
                'host_name' => 'Đêm Tiệc Giáng Sinh Gia Đình Tuấn Hà',
                'sub_host_name' => 'Mùa Lễ Hội An Lành • Tiệc Tối Sum Vầy Ấm Áp',
                'event_badge' => 'GIÁNG SINH AN LÀNH • ĐÊM TIỆC MÙA ĐÔNG',
                'invitation_message' => 'Mùa lễ hội lung linh nhất trong năm đã về! Thân mời bạn tới dự đêm tiệc Giáng Sinh ấm áp bên ánh nến, cây thông và những bản nhạc mùa đông ngọt ngào.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'giang-sinh-vong-nguyet-que',
                'category_slug' => 'giang-sinh-nam-moi',
                'name' => 'Thiệp Mời Giáng Sinh Vòng Nguyệt Quế Nơ Đỏ',
                'tagline' => 'Khung vòng lá thông màu nước điểm nơ đỏ cổ điển kết hợp phong bì kem lót chấm bi đỏ',
                'card_mode' => 'invitation',
                'default_palette' => 'ruby',
                'envelope_color' => 'ivory-cream',
                'envelope_liner' => 'berry-dots',
                'card_border' => 'pine-garland',
                'artwork_motif' => 'holly-wreath',
                'backdrop' => 'silk-cream',
                'seal_icon' => '🎀',
                'seal_label' => 'Chạm Mở Thiệp Mùa Lễ Hội',
                'host_name' => 'Gia Đình Minh Khôi & Lan Anh',
                'sub_host_name' => 'Tiệc Giáng Sinh Thường Niên • Đêm Hội Sum Vầy Cuối Năm',
                'event_badge' => 'ĐÊM TIỆC LỄ HỘI • GIÁNG SINH AN LÀNH',
                'invitation_message' => 'Trân trọng mời bạn và người thương tới chung vui đêm tiệc cuối năm thường niên của gia đình chúng mình. Cùng nâng ly chúc nhau một mùa Giáng Sinh bình an và năm mới rực rỡ!',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'giang-sinh-cocktail-friendsmas',
                'category_slug' => 'giang-sinh-nam-moi',
                'name' => 'Thiệp Mời Đêm Tiệc Giáng Sinh Hội Bạn Thân',
                'tagline' => 'Viền lượn sóng sọc đỏ hồng trẻ trung, đôi ly tiệc lễ hội và phong bì xanh thông',
                'card_mode' => 'invitation',
                'default_palette' => 'ruby',
                'envelope_color' => 'forest-green',
                'envelope_liner' => 'candy-stripe',
                'card_border' => 'scalloped-wave',
                'artwork_motif' => 'cocktail-cheers',
                'backdrop' => 'silk-cream',
                'seal_icon' => '🍸',
                'seal_label' => 'Chạm Mở Thiệp Tiệc Lễ Hội',
                'host_name' => 'Hội Bạn Thân Đón Giáng Sinh',
                'sub_host_name' => 'Gặp Gỡ Cuối Năm • Trao Quà & Đếm Ngược Năm Mới',
                'event_badge' => 'TIỆC GIÁNG SINH & ĐÓN CHÀO NĂM MỚI',
                'invitation_message' => 'Cuối năm bận rộn đến mấy cũng không thể thiếu buổi hẹn hò của hội chúng mình! Lên đồ thật đẹp tới nâng ly, trao quà bí mật và đếm ngược đón năm mới nhé.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],

            // ================= 4. ĐẦY THÁNG & THÔI NÔI EM BÉ (3 MẪU) =================
            [
                'slug' => 'day-thang-hoang-gia-nhi',
                'category_slug' => 'day-thang-thoi-noi',
                'name' => 'Thiệp Mời Thôi Nôi Khu Rừng Cổ Tích Mùa Đông',
                'tagline' => 'Thiệp khuôn bo lượn sóng với cáo nhỏ, sóc nâu, gấu con, nai vàng và phong bì hồng phấn lót chấm tuyết',
                'card_mode' => 'invitation',
                'default_palette' => 'dusty-rose',
                'envelope_color' => 'blush-pink',
                'envelope_liner' => 'berry-dots',
                'card_border' => 'scalloped-wave',
                'artwork_motif' => 'woodland-animals',
                'backdrop' => 'silk-cream',
                'seal_icon' => '👶',
                'seal_label' => 'Chạm Mở Thiệp Mừng Bé Yêu',
                'host_name' => 'Bé Nguyễn Minh Khôi',
                'sub_host_name' => 'Tên ở nhà: Bé Bơ • Con của Ba Minh Tuấn & Mẹ Thu Hà',
                'event_badge' => 'TIỆC MỪNG THÔI NÔI TRÒN 1 TUỔI',
                'invitation_message' => 'Cùng gia đình đón mừng cột mốc tròn 1 tuổi đầu đời của thiên thần nhỏ Bé Bơ.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => true,
            ],
            [
                'slug' => 'thoi-noi-may-trang-bong-benh',
                'category_slug' => 'day-thang-thoi-noi',
                'name' => 'Thiệp Mời Đầy Tháng Mây Trắng Và Trăng Sao',
                'tagline' => 'Phong bì xanh đêm lót bầu trời sao dịu dàng dành cho lễ Đầy Tháng bé trai và bé gái',
                'card_mode' => 'invitation',
                'default_palette' => 'royal-navy',
                'envelope_color' => 'midnight-navy',
                'envelope_liner' => 'starry-night',
                'card_border' => 'lantern-arch',
                'artwork_motif' => 'baby-cradle',
                'backdrop' => 'silk-cream',
                'seal_icon' => '🌙',
                'seal_label' => 'Chạm Mở Thiệp Đầy Tháng',
                'host_name' => 'Bé Trần Bảo An (Bé Sóc)',
                'sub_host_name' => 'Mừng thiên thần nhỏ tròn 1 tháng tuổi bình an',
                'event_badge' => 'LỄ MỪNG ĐẦY THÁNG CỦA BÉ',
                'invitation_message' => 'Trân trọng kính mời Ông Bà, Cô Chú tới dùng bữa cơm thân mật và chúc phúc cho Bé Sóc.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => true,
            ],
            [
                'slug' => 'thoi-noi-vuon-co-tich-nhi',
                'category_slug' => 'day-thang-thoi-noi',
                'name' => 'Thiệp Mời Thôi Nôi Mừng Bé Yêu Tròn Một Tuổi',
                'tagline' => 'Viền pháo giấy sắc màu tươi vui kết hợp khung tải ảnh thật của bé ngay trên mặt thiệp',
                'card_mode' => 'invitation',
                'default_palette' => 'warm-amber',
                'envelope_color' => 'ivory-cream',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'pastel-confetti',
                'artwork_motif' => 'baby-thoi-noi-items',
                'backdrop' => 'silk-cream',
                'seal_icon' => '🧸',
                'seal_label' => 'Chạm Mở Thiệp Mừng 1 Tuổi',
                'host_name' => 'Bé Lê Tuệ Nhi (Bé Cam)',
                'sub_host_name' => 'Mừng Con Yêu Tròn 1 Tuổi • Bốc Đồ Chọn Nghề Tương Lai',
                'event_badge' => 'TIỆC THÔI NÔI • MỪNG BÉ TRÒN 1 TUỔI',
                'invitation_message' => 'Thân mời Cô Chú tới dự tiệc thôi nôi, xem Bé Cam bốc đồ chọn nghề và thổi cây nến đầu tiên!',
                'show_datetime_venue' => true,
                'enable_gift_qr' => true,
            ],

            // ================= 5. SINH NHẬT TRẺ EM (3 MẪU) =================
            [
                'slug' => 'sinh-nhat-be-vuong-quoc-keo-ngot',
                'category_slug' => 'sinh-nhat-tre-em',
                'name' => 'Thiệp Mời Sinh Nhật Bé Vương Quốc Kẹo Ngọt',
                'tagline' => 'Thiệp hồng ngọt ngào kết hợp phong bì tím hoa cà rực rỡ cho tiệc sinh nhật công chúa nhỏ',
                'card_mode' => 'invitation',
                'default_palette' => 'dusty-rose',
                'envelope_color' => 'royal-violet',
                'envelope_liner' => 'blush-stripes',
                'card_border' => 'scalloped-wave',
                'artwork_motif' => 'kids-pamper-party',
                'backdrop' => 'blush-dream',
                'seal_icon' => '🎂',
                'seal_label' => 'Chạm Mở Thiệp Sinh Nhật Bé',
                'host_name' => 'Sinh Nhật 6 Tuổi Bé Khánh Ngân',
                'sub_host_name' => 'Công Chúa Nhỏ Bé Bông • Tiệc Sinh Nhật Ngọt Ngào',
                'event_badge' => 'TIỆC SINH NHẬT BÉ YÊU • MỪNG TUỔI MỚI',
                'invitation_message' => 'Cùng nhau thổi nến, ăn bánh kem dâu tây và tham gia trò chơi vui nhộn mừng Bé Bông 6 tuổi!',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'sinh-nhat-be-phi-hanh-gia-nhi',
                'category_slug' => 'sinh-nhat-tre-em',
                'name' => 'Thiệp Mời Sinh Nhật Phi Hành Gia Nhí Khám Phá',
                'tagline' => 'Phong bì xanh vũ trụ lót bầu trời sao dành cho tiệc sinh nhật bé trai năng động',
                'card_mode' => 'invitation',
                'default_palette' => 'royal-navy',
                'envelope_color' => 'midnight-navy',
                'envelope_liner' => 'starry-night',
                'card_border' => 'scalloped-wave',
                'artwork_motif' => 'kids-space-galaxy',
                'backdrop' => 'moonlight-navy',
                'seal_icon' => '🚀',
                'seal_label' => 'Chạm Mở Chuyến Tàu Sinh Nhật',
                'host_name' => 'Sinh Nhật 7 Tuổi Bé Gia Bảo (Bé Ken)',
                'sub_host_name' => 'Chuyến phiêu lưu tuổi lên 7 cùng các bạn nhỏ',
                'event_badge' => 'TIỆC SINH NHẬT KHÁM PHÁ VŨ TRỤ',
                'invitation_message' => 'Phi thuyền sinh nhật của Bé Ken chuẩn bị cất cánh! Mời các phi hành gia nhí tới dự tiệc nhé.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'sinh-nhat-be-khu-vuon-co-tich',
                'category_slug' => 'sinh-nhat-tre-em',
                'name' => 'Thiệp Mời Sinh Nhật Bé Khu Vườn Cổ Tích Xanh',
                'tagline' => 'Muông thú rừng xanh màu nước dịu dàng cùng phong bì kem lót chấm bi đỏ đáng yêu',
                'card_mode' => 'invitation',
                'default_palette' => 'sage-green',
                'envelope_color' => 'ivory-cream',
                'envelope_liner' => 'berry-dots',
                'card_border' => 'scalloped-wave',
                'artwork_motif' => 'woodland-animals',
                'backdrop' => 'silk-cream',
                'seal_icon' => '🎈',
                'seal_label' => 'Chạm Mở Thiệp Sinh Nhật',
                'host_name' => 'Sinh Nhật 4 Tuổi Bé Minh Châu',
                'sub_host_name' => 'Bữa tiệc sân vườn cuối tuần của Bé Châu',
                'event_badge' => 'TIỆC SINH NHẬT SÂN VƯỜN CỦA BÉ',
                'invitation_message' => 'Thân mời Cô Chú cùng các bé tới dự tiệc sinh nhật ngoài trời và cắt bánh kem mừng con tròn 4 tuổi.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],

            // ================= 6. SINH NHẬT NGƯỜI LỚN & LỄ MỪNG THỌ (3 MẪU) =================
            [
                'slug' => 'sinh-nhat-anh-kim-champagne',
                'category_slug' => 'sinh-nhat-nguoi-lon',
                'name' => 'Thiệp Mời Sinh Nhật Tuổi Mới Rực Rỡ Lung Linh',
                'tagline' => 'Phong cách tạp chí tối giản thanh lịch cùng phong bì hồng phấn lót sọc tinh tế',
                'card_mode' => 'invitation',
                'default_palette' => 'warm-amber',
                'envelope_color' => 'blush-pink',
                'envelope_liner' => 'blush-stripes',
                'card_border' => 'minimal-editorial',
                'artwork_motif' => 'adult-martini-editorial',
                'backdrop' => 'silk-cream',
                'seal_icon' => '🍸',
                'seal_label' => 'Chạm Mở Thiệp Sinh Nhật',
                'host_name' => 'Trần Thu Hà • Mừng Tuổi 30',
                'sub_host_name' => 'Đêm Tiệc Sinh Nhật Ấm Cúng Bên Hội Bạn Thân',
                'event_badge' => 'MỪNG SINH NHẬT • DẤU ẤN TUỔI MỚI',
                'invitation_message' => 'Cùng những người bạn thân quý nâng ly cocktail và lưu giữ một tối sinh nhật tuổi 30 đáng nhớ.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'sinh-nhat-dau-an-tuoi-vang',
                'category_slug' => 'sinh-nhat-nguoi-lon',
                'name' => 'Thiệp Mời Tiệc Sinh Nhật Dấu Ấn Tuổi Vàng',
                'tagline' => 'Khung viền chỉ vàng kép sang trọng trên phong bì xanh đêm lót nhũ vàng kim',
                'card_mode' => 'invitation',
                'default_palette' => 'royal-navy',
                'envelope_color' => 'midnight-navy',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'gold-double',
                'artwork_motif' => 'adult-martini-editorial',
                'backdrop' => 'moonlight-navy',
                'seal_icon' => '🥂',
                'seal_label' => 'Chạm Mở Thư Mời Sinh Nhật',
                'host_name' => 'Nguyễn Hoàng Nam',
                'sub_host_name' => 'Kỷ Niệm Tuổi 40 • Đêm Tiệc Tri Ân Bạn Bè & Gia Đình',
                'event_badge' => 'ĐÊM TIỆC SINH NHẬT • DẤU ẤN TUỔI VÀNG',
                'invitation_message' => 'Trân trọng mời bạn tới dự bữa tiệc tối thân mật đánh dấu cột mốc tuổi 40 cùng mình và gia đình.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'mung-tho-bach-nien-giai-lao',
                'category_slug' => 'sinh-nhat-nguoi-lon',
                'name' => 'Thiệp Mời Đại Lễ Mừng Thọ Bách Niên Trường Thọ',
                'tagline' => 'Viền gấm đỏ hoàng gia, đôi hạc đào tiên và phong bì vàng kim trang nghiêm cho Lễ Mừng Thọ',
                'card_mode' => 'invitation',
                'default_palette' => 'ruby',
                'envelope_color' => 'crimson-red',
                'envelope_liner' => 'lunar-clouds',
                'card_border' => 'lunar-brocade',
                'artwork_motif' => 'elder-longevity-crane',
                'backdrop' => 'festive-velvet',
                'seal_icon' => '🍑',
                'seal_label' => 'Chạm Mở Thiệp Đại Lễ Mừng Thọ',
                'host_name' => 'Mừng Thọ Cụ Ông Nguyễn Văn Phúc & Cụ Bà Lê Thị Lộc',
                'sub_host_name' => 'Đại Lễ Mừng Thọ 80 Tuổi • Phúc Như Đông Hải, Thọ Tỷ Nam Sơn',
                'event_badge' => 'ĐẠI LỄ MỪNG THỌ THƯỢNG THỌ',
                'invitation_message' => 'Con cháu thành tâm tổ chức tiệc mừng thọ báo hiếu Ông Bà, trân trọng kính mời Quý khách tới chung vui.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => true,
            ],

            // ================= 7. TÂN GIA & MỪNG NHÀ MỚI (3 MẪU) =================
            [
                'slug' => 'tan-gia-phuc-loc-tho',
                'category_slug' => 'tan-gia-nha-moi',
                'name' => 'Thiệp Mời Tân Gia Nhà Mới Phúc Lộc An Gia',
                'tagline' => 'Thiệp mời tân gia trang trọng tích hợp bản đồ định vị dẫn đường tới tổ ấm mới',
                'card_mode' => 'invitation',
                'default_palette' => 'ruby',
                'envelope_color' => 'crimson-red',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'gold-double',
                'artwork_motif' => 'house-key',
                'backdrop' => 'silk-cream',
                'seal_icon' => '🏡',
                'seal_label' => 'Chạm Mở Thư Mời Tân Gia',
                'host_name' => 'Gia Đình Minh Tuấn & Thu Hà',
                'sub_host_name' => 'Trân trọng kính mời Quý khách & Gia đình tới chung vui',
                'event_badge' => 'LỄ MỪNG TÂN GIA • AN CƯ LẠC NGHIỆP',
                'invitation_message' => 'Sau thời gian ấp ủ và vun đắp, vợ chồng chúng mình đã hoàn thiện ngôi nhà nhỏ ấm cúng. Gia đình rất mong được đón tiếp những người thân yêu tới tham quan tổ ấm mới và dùng bữa cơm thân mật cùng gia đình.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => true,
            ],
            [
                'slug' => 'tan-gia-to-am-xanh-binh-yen',
                'category_slug' => 'tan-gia-nha-moi',
                'name' => 'Thiệp Mời Tân Gia Tổ Ấm Xanh Bình Yên',
                'tagline' => 'Vòng lá xanh màu nước thanh nhã cùng phong bì xanh rừng thông lót nhũ vàng',
                'card_mode' => 'invitation',
                'default_palette' => 'sage-green',
                'envelope_color' => 'forest-green',
                'envelope_liner' => 'botanical-holly',
                'card_border' => 'pine-garland',
                'artwork_motif' => 'house-key',
                'backdrop' => 'pine-forest',
                'seal_icon' => '🔑',
                'seal_label' => 'Chạm Mở Thiệp Mừng Nhà Mới',
                'host_name' => 'Gia Đình Anh Khoa & Chị Thảo',
                'sub_host_name' => 'Mừng Tổ Ấm Mới • Chào Đón Bạn Bè Tới Thăm Nhà',
                'event_badge' => 'TIỆC MỪNG NHÀ MỚI • TÂN GIA BÌNH YÊN',
                'invitation_message' => 'Ngôi nhà mới của gia đình mình đã sẵn sàng mở cửa đón những vị khách quý đầu tiên. Thân mời bạn ghé thăm nhà, thưởng trà và dùng bữa tối ấm cúng cùng vợ chồng mình.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => true,
            ],
            [
                'slug' => 'tan-gia-sum-vay-am-cung',
                'category_slug' => 'tan-gia-nha-moi',
                'name' => 'Thiệp Mời Tân Gia Bữa Cơm Sum Vầy Ấm Cúng',
                'tagline' => 'Phong bì gỗ trầm ấm áp lót họa tiết kẻ phù hợp cho tiệc tân gia căn hộ và nhà phố',
                'card_mode' => 'invitation',
                'default_palette' => 'warm-amber',
                'envelope_color' => 'espresso-brown',
                'envelope_liner' => 'tartan-plaid',
                'card_border' => 'scalloped-wave',
                'artwork_motif' => 'house-key',
                'backdrop' => 'warm-walnut',
                'seal_icon' => '🥂',
                'seal_label' => 'Chạm Mở Lời Mời Tân Gia',
                'host_name' => 'Vợ Chồng Đức Anh & Khánh Linh',
                'sub_host_name' => 'Mừng căn hộ mới • Chung vui cùng gia đình nhỏ',
                'event_badge' => 'TIỆC MỪNG TÂN GIA CĂN HỘ MỚI',
                'invitation_message' => 'Niềm vui về nhà mới sẽ trọn vẹn hơn rất nhiều khi có tiếng cười của những người thân thiết. Mời bạn cuối tuần này ghé căn hộ mới của tụi mình chung vui nhé!',
                'show_datetime_venue' => true,
                'enable_gift_qr' => true,
            ],

            // ================= 8. TẤT NIÊN CÔNG TY & LỄ KHAI TRƯƠNG (3 MẪU) =================
            [
                'slug' => 'giang-sinh-tat-nien-am-ap',
                'category_slug' => 'tat-nien-khai-truong',
                'name' => 'Thiệp Mời Tiệc Tất Niên Doanh Nghiệp Rực Rỡ',
                'tagline' => 'Thiệp mời tiệc tất niên cuối năm cho doanh nghiệp với chương trình vinh danh và bốc thăm may mắn',
                'card_mode' => 'invitation',
                'default_palette' => 'royal-navy',
                'envelope_color' => 'midnight-navy',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'gold-double',
                'artwork_motif' => 'cocktail-cheers',
                'backdrop' => 'moonlight-navy',
                'seal_icon' => '🏆',
                'seal_label' => 'Chạm Mở Thư Mời Tiệc Tất Niên',
                'host_name' => 'Công Ty Cổ Phần Tập Đoàn Mây Hẹn',
                'sub_host_name' => 'Tổng Kết Cuối Năm 2026 • Gắn Kết Nội Lực, Bứt Phá Thành Công',
                'event_badge' => 'ĐÊM TIỆC TẤT NIÊN & VINH DANH CUỐI NĂM',
                'invitation_message' => 'Khép lại một năm với biết bao nỗ lực và thành tựu đáng tự hào, Ban Lãnh Đạo trân trọng kính mời Quý đối tác cùng toàn thể cán bộ nhân viên tới tham dự Đêm Tiệc Tất Niên cuối năm.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'khai-truong-hong-phat-hoang-gia',
                'category_slug' => 'tat-nien-khai-truong',
                'name' => 'Thiệp Mời Lễ Khai Trương Hồng Phát Hoàng Gia',
                'tagline' => 'Phong bì đỏ hoàng gia lót nhũ vàng kim dành cho Lễ Khai Trương cửa hàng, thẩm mỹ viện và văn phòng',
                'card_mode' => 'invitation',
                'default_palette' => 'ruby',
                'envelope_color' => 'crimson-red',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'lunar-brocade',
                'artwork_motif' => 'tet-swallows',
                'backdrop' => 'festive-velvet',
                'seal_icon' => '🎊',
                'seal_label' => 'Chạm Mở Thư Mời Khai Trương',
                'host_name' => 'Thương Hiệu Thời Trang Mây Hẹn',
                'sub_host_name' => 'Lễ Cắt Băng Khai Trương Cửa Hàng Mới • Khai Trương Hồng Phát',
                'event_badge' => 'LỄ KHAI TRƯƠNG HỒNG PHÁT • ĐẠI CÁT ĐẠI LỢI',
                'invitation_message' => 'Đánh dấu cột mốc phát triển mới, chúng tôi trân trọng kính mời Quý khách hàng và Đối tác tới tham dự Lễ Cắt Băng Khai Trương cơ sở mới và dự tiệc trà chúc mừng.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => true,
            ],
            [
                'slug' => 'hoi-nghi-tri-an-khach-hang-vip',
                'category_slug' => 'tat-nien-khai-truong',
                'name' => 'Thiệp Mời Đêm Tiệc Tri Ân Khách Hàng Thân Thiết',
                'tagline' => 'Thiết kế tối giản sang trọng dành cho sự kiện ra mắt sản phẩm và tri ân khách hàng thân thiết',
                'card_mode' => 'invitation',
                'default_palette' => 'warm-amber',
                'envelope_color' => 'imperial-gold',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'minimal-editorial',
                'artwork_motif' => 'graduation-star',
                'backdrop' => 'silk-cream',
                'seal_icon' => '✦',
                'seal_label' => 'Chạm Mở Thiệp Mời Danh Dự',
                'host_name' => 'Ban Giám Đốc & Hội Đồng Quản Trị',
                'sub_host_name' => 'Đêm Tiệc Tri Ân Đối Tác • Đồng Hành Kiến Tạo Tương Lai',
                'event_badge' => 'ĐÊM TIỆC TRI ÂN KHÁCH HÀNG & ĐỐI TÁC',
                'invitation_message' => 'Sự tin tưởng của Quý vị là nền tảng cho mọi thành công của chúng tôi. Trân trọng kính mời Quý vị tới dự đêm tiệc tri ân đặc biệt dành riêng cho các đối tác chiến lược.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],

            // ================= 9. LỄ TỐT NGHIỆP & VINH DANH (3 MẪU) =================
            [
                'slug' => 'tot-nghiep-cham-toi-uoc-mo',
                'category_slug' => 'tot-nghiep-thanh-tuu',
                'name' => 'Thiệp Mời Lễ Tốt Nghiệp Chạm Tới Ước Mơ',
                'tagline' => 'Phong bì xanh đêm hoàng gia và mũ cử nhân mạ vàng mời người thân tới dự lễ trao bằng',
                'card_mode' => 'invitation',
                'default_palette' => 'royal-navy',
                'envelope_color' => 'midnight-navy',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'gold-double',
                'artwork_motif' => 'graduation-cap',
                'backdrop' => 'moonlight-navy',
                'seal_icon' => '🎓',
                'seal_label' => 'Chạm Mở Thiệp Mời Tốt Nghiệp',
                'host_name' => 'Tân Cử Nhân Nguyễn Minh Anh',
                'sub_host_name' => 'Lễ Trao Bằng Tốt Nghiệp Đại Học • Khóa 2022 – 2026',
                'event_badge' => 'LỄ TRAO BẰNG TỐT NGHIỆP ĐẠI HỌC',
                'invitation_message' => 'Sau 4 năm nỗ lực trên giảng đường, khoảnh khắc mình chính thức khoác lên mình tấm áo cử nhân đã tới. Rất mong có sự hiện diện của gia đình và bạn bè để cùng lưu giữ những bức ảnh thanh xuân đẹp nhất!',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'tot-nghiep-hanh-trinh-ruc-ro',
                'category_slug' => 'tot-nghiep-thanh-tuu',
                'name' => 'Thiệp Mời Lễ Tốt Nghiệp Hành Trình Rực Rỡ',
                'tagline' => 'Khung vòm lá nguyệt quế vinh quang kết hợp phong bì xanh rừng thông thanh lịch',
                'card_mode' => 'invitation',
                'default_palette' => 'sage-green',
                'envelope_color' => 'forest-green',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'pine-garland',
                'artwork_motif' => 'graduation-cap',
                'backdrop' => 'silk-cream',
                'seal_icon' => '🌿',
                'seal_label' => 'Chạm Mở Lời Mời Tốt Nghiệp',
                'host_name' => 'Tân Thạc Sĩ Trần Quốc Bảo',
                'sub_host_name' => 'Chúc mừng hoàn thành chương trình Cao học & Nhận bằng Thạc sĩ',
                'event_badge' => 'TIỆC MỪNG LỄ TỐT NGHIỆP & VINH DANH',
                'invitation_message' => 'Để đánh dấu cột mốc hoàn thành chặng đường học tập đầy thử thách, mình thân mời những người thân yêu tới dự lễ nhận bằng và bữa tiệc chúc mừng ấm cúng.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'vinh-danh-chang-duong-cong-hien',
                'category_slug' => 'tot-nghiep-thanh-tuu',
                'name' => 'Thiệp Mời Tiệc Tri Ân Chặng Đường Cống Hiến',
                'tagline' => 'Thiệp mời tiệc tri ân lãnh đạo nghỉ hưu, thăng chức hoặc chia tay đồng nghiệp',
                'card_mode' => 'invitation',
                'default_palette' => 'warm-amber',
                'envelope_color' => 'espresso-brown',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'gold-double',
                'artwork_motif' => 'graduation-star',
                'backdrop' => 'warm-walnut',
                'seal_icon' => '🌟',
                'seal_label' => 'Chạm Mở Thư Mời Tri Ân',
                'host_name' => 'Vinh Danh Ông Nguyễn Văn Hùng',
                'sub_host_name' => 'Tri Ân 30 Năm Tâm Huyết & Cống Hiến',
                'event_badge' => 'TIỆC VINH DANH & TRI ÂN CỐNG HIẾN',
                'invitation_message' => 'Trân trọng kính mời các anh chị em đồng nghiệp tới dự buổi tiệc ấm cúng tri ân chặng đường cống hiến tận tụy và gửi những lời chúc tốt đẹp nhất cho hành trình mới.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],

            // ================= 10. THIỆP CHÚC MỪNG 8/3, 20/10, 20/11 & KỶ NIỆM (3 MẪU) =================
            [
                'slug' => 'chuc-mung-phai-dep-8-3-va-20-10',
                'category_slug' => 'ngay-le-chuc-mung',
                'name' => 'Thiệp Chúc Mừng Phái Đẹp Đóa Hoa Rạng Rỡ',
                'tagline' => 'Thiệp hoa mẫu đơn màu nước kết hợp phong bì hồng phấn dịu dàng tôn vinh phái đẹp Việt Nam',
                'card_mode' => 'greeting',
                'default_palette' => 'dusty-rose',
                'envelope_color' => 'blush-pink',
                'envelope_liner' => 'blush-stripes',
                'card_border' => 'scalloped-wave',
                'artwork_motif' => 'floral-bouquet',
                'backdrop' => 'blush-dream',
                'seal_icon' => '💐',
                'seal_label' => 'Chạm Mở Thiệp Chúc Mừng Phái Đẹp',
                'host_name' => 'Gửi Người Phụ Nữ Tuyệt Vời Nhất',
                'sub_host_name' => 'Chúc mừng ngày tôn vinh phái đẹp 8/3 & 20/10',
                'event_badge' => 'CHÚC MỪNG PHÁI ĐẸP • YÊU THƯƠNG & TRÂN TRỌNG',
                'invitation_message' => 'Nhân ngày đặc biệt dành riêng cho một nửa dịu dàng của thế giới, xin gửi tới bạn những đóa hoa tươi thắm nhất cùng lời chúc luôn xinh đẹp, rạng rỡ, tự tin và ngập tràn hạnh phúc mỗi ngày.',
                'show_datetime_venue' => false,
                'enable_gift_qr' => true,
            ],
            [
                'slug' => 'tri-an-thay-co-20-11',
                'category_slug' => 'ngay-le-chuc-mung',
                'name' => 'Thiệp Tri Ân Thầy Cô Người Lái Đò Thầm Lặng',
                'tagline' => 'Thiệp chúc mừng Ngày Nhà Giáo Việt Nam 20/11 trang nhã hoặc mời họp lớp tri ân thầy cô',
                'card_mode' => 'greeting',
                'default_palette' => 'warm-amber',
                'envelope_color' => 'ivory-cream',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'gold-double',
                'artwork_motif' => 'floral-bouquet',
                'backdrop' => 'silk-cream',
                'seal_icon' => '📖',
                'seal_label' => 'Chạm Mở Thiệp Tri Ân 20/11',
                'host_name' => 'Kính Tặng Thầy Cô Giáo Đáng Kính',
                'sub_host_name' => 'Tri ân Ngày Nhà Giáo Việt Nam 20 tháng 11',
                'event_badge' => 'CHÀO MỪNG NGÀY NHÀ GIÁO VIỆT NAM 20/11',
                'invitation_message' => 'Muốn sang thì bắc cầu kiều, Muốn con hay chữ thì yêu lấy thầy. Nhân ngày Nhà giáo Việt Nam 20/11, tập thể học trò chúng em xin kính chúc Thầy Cô luôn mạnh khỏe, hạnh phúc và giữ mãi ngọn lửa đam mê trên bục giảng.',
                'show_datetime_venue' => false,
                'enable_gift_qr' => false,
            ],
            [
                'slug' => 'ky-niem-ngay-cuoi-vang-son',
                'category_slug' => 'ngay-le-chuc-mung',
                'name' => 'Thiệp Kỷ Niệm Ngày Cưới Khúc Nhạc Trăm Năm',
                'tagline' => 'Kỷ niệm 1 năm, 5 năm, 10 năm hay 25 năm ngày chung đôi trong không gian ấm áp sang trọng',
                'card_mode' => 'invitation',
                'default_palette' => 'royal-navy',
                'envelope_color' => 'midnight-navy',
                'envelope_liner' => 'gold-foil',
                'card_border' => 'gold-double',
                'artwork_motif' => 'rings-anniversary',
                'backdrop' => 'moonlight-navy',
                'seal_icon' => '💍',
                'seal_label' => 'Chạm Mở Thiệp Kỷ Niệm',
                'host_name' => 'Minh Tuấn & Thu Hà',
                'sub_host_name' => 'Kỷ Niệm 5 Năm Ngày Chung Đôi • Hành Trình Yêu Thương',
                'event_badge' => 'KỶ NIỆM NGÀY CƯỚI • HÀNH TRÌNH TRĂM NĂM',
                'invitation_message' => 'Năm tháng đi qua càng làm sâu sắc thêm lời hẹn ước ngày hôm ấy. Vợ chồng chúng mình trân trọng kính mời những người thân thương nhất tới dự bữa tối kỷ niệm ngày cưới ấm cúng cùng gia đình.',
                'show_datetime_venue' => true,
                'enable_gift_qr' => false,
            ],
        ];

        $allRawTemplates = array_merge($rawTemplates, $this->loadImportedTemplates());

        $indexed = [];
        foreach ($allRawTemplates as $tpl) {
            if (empty($tpl['slug']) || empty($tpl['category_slug'])) {
                continue;
            }
            $indexed[$tpl['slug']] = [
                'slug' => $tpl['slug'],
                'category_slug' => $tpl['category_slug'],
                'name' => $tpl['name'],
                'tagline' => $tpl['tagline'],
                'tier' => 'pro',
                'tier_badge' => 'Đặc Quyền Gói Pro',
                'card_mode' => $tpl['card_mode'] ?? 'invitation',
                'default_palette' => $tpl['default_palette'] ?? 'ruby',
                'envelope_color' => $tpl['envelope_color'] ?? 'crimson-red',
                'envelope_liner' => $tpl['envelope_liner'] ?? 'gold-foil',
                'card_border' => $tpl['card_border'] ?? 'gold-double',
                'artwork_motif' => $tpl['artwork_motif'] ?? 'tet-blossom',
                'backdrop' => $tpl['backdrop'] ?? 'silk-cream',
                'envelope_back_img' => $tpl['envelope_back_img'] ?? null,
                'backdrop_img' => $tpl['backdrop_img'] ?? null,
                'seal_icon' => $tpl['seal_icon'] ?? '✦',
                'seal_label' => $tpl['seal_label'] ?? 'Chạm Mở Thiệp',
                'defaults' => [
                    'title' => $tpl['name'].' — '.$tpl['host_name'],
                    'event_badge' => $tpl['event_badge'],
                    'host_name' => $tpl['host_name'],
                    'sub_host_name' => $tpl['sub_host_name'],
                    'invitation_message' => $tpl['invitation_message'],
                    'event_date' => now()->addDays(18)->format('Y-m-d'),
                    'welcome_time' => '17:30',
                    'party_time' => '18:00',
                    'lunar_note' => 'Nhằm ngày lành tháng tốt • Rất hân hạnh được đón tiếp',
                    'venue_name' => 'Nhà Hàng Maison De Charme — Sảnh Hoàng Gia (Tầng 2)',
                    'venue_address' => 'Số 68 Nguyễn Du, Quận Hai Bà Trưng, Hà Nội',
                    'map_url' => 'https://maps.google.com/?q=68+Nguyen+Du+Ha+Noi',
                    'dresscode_note' => 'Trang phục lịch sự, thanh nhã theo tông màu chủ đạo của thiệp',
                    'show_datetime_venue' => (bool) ($tpl['show_datetime_venue'] ?? true),
                    'enable_rsvp' => true,
                    'enable_gift_qr' => (bool) ($tpl['enable_gift_qr'] ?? false),
                    'bank_name' => 'Vietcombank',
                    'bank_account_number' => '0123456789',
                    'bank_account_holder' => 'NGUYEN MINH TUAN',
                    'gift_note' => 'Cảm ơn tình cảm và lời chúc tốt đẹp của Quý vị dành cho gia đình',
                    'rsvp_question' => 'Bạn có lưu ý gì về thực đơn (chay/mặn) hoặc chỗ đỗ xe không?',
                    'envelope_color' => $tpl['envelope_color'] ?? 'crimson-red',
                    'envelope_liner' => $tpl['envelope_liner'] ?? 'gold-foil',
                    'card_border' => $tpl['card_border'] ?? 'gold-double',
                    'artwork_motif' => $tpl['artwork_motif'] ?? 'tet-blossom',
                    'backdrop' => $tpl['backdrop'] ?? 'silk-cream',
                    'envelope_back_img' => $tpl['envelope_back_img'] ?? null,
                    'backdrop_img' => $tpl['backdrop_img'] ?? null,
                    'font_style' => 'serif',
                    'custom_photo_data' => '',
                    'schedule_items' => [
                        ['time' => '17:30', 'title' => 'Đón tiếp khách quý & Chụp hình lưu niệm'],
                        ['time' => '18:00', 'title' => 'Nghi thức chúc mừng chính thức & Khai tiệc'],
                        ['time' => '19:45', 'title' => 'Giao lưu ấm cúng & Trao quà kỷ niệm'],
                    ],
                ],
            ];
        }

        return $indexed;
    }

    /**
     * Lọc danh sách mẫu thiệp theo danh mục, chế độ (invitation/greeting), màu phong bì, khung viền hoặc từ khóa tìm kiếm.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function getTemplatesByCategory(?string $categorySlug = null, array $filters = []): array
    {
        $templates = array_values($this->getTemplates());

        if (! empty($categorySlug)) {
            $templates = array_values(array_filter(
                $templates,
                static fn (array $tpl): bool => $tpl['category_slug'] === $categorySlug
            ));
        }

        if (! empty($filters['mode']) && in_array($filters['mode'], ['invitation', 'greeting'], true)) {
            $mode = $filters['mode'];
            $templates = array_values(array_filter(
                $templates,
                static fn (array $tpl): bool => $tpl['card_mode'] === $mode
            ));
        }

        if (! empty($filters['envelope_color'])) {
            $envColor = (string) $filters['envelope_color'];
            $templates = array_values(array_filter(
                $templates,
                static fn (array $tpl): bool => $tpl['envelope_color'] === $envColor
            ));
        }

        if (! empty($filters['card_border'])) {
            $border = (string) $filters['card_border'];
            $templates = array_values(array_filter(
                $templates,
                static fn (array $tpl): bool => $tpl['card_border'] === $border
            ));
        }

        if (! empty($filters['q'])) {
            $q = mb_strtolower(trim((string) $filters['q']), 'UTF-8');
            $templates = array_values(array_filter(
                $templates,
                static fn (array $tpl): bool => str_contains(mb_strtolower($tpl['name'].' '.$tpl['tagline'].' '.$tpl['defaults']['event_badge'], 'UTF-8'), $q)
            ));
        }

        return $templates;
    }

    /**
     * Lấy chi tiết 1 mẫu thiệp sự kiện theo slug.
     *
     * @return array<string, mixed>|null
     */
    public function getTemplate(string $templateSlug): ?array
    {
        $templates = $this->getTemplates();

        return $templates[$templateSlug] ?? null;
    }

    /**
     * Danh sách 12 Chủ Đề Tranh Minh Họa Bao Quanh Mặt Thiệp (Cho phép người dùng đổi trực tiếp trong Studio).
     *
     * @var array<string, array{id: string, name: string}>
     */
    public const ARTWORK_MOTIFS = [
        'woodland-animals' => ['id' => 'woodland-animals', 'name' => 'Rừng Thông & Muông Thú Cổ Tích'],
        'baby-cradle' => ['id' => 'baby-cradle', 'name' => 'Trăng Sao, Nôi Mây & Ngựa Gỗ'],
        'baby-thoi-noi-items' => ['id' => 'baby-thoi-noi-items', 'name' => 'Bộ Đồ Bốc Thôi Nôi Chọn Nghề'],
        'kids-pamper-party' => ['id' => 'kids-pamper-party', 'name' => 'Bánh Kem, Vương Miện & Cờ Lễ Hội'],
        'kids-space-galaxy' => ['id' => 'kids-space-galaxy', 'name' => 'Tên Lửa & Phi Hành Gia Ngân Hà'],
        'tet-blossom' => ['id' => 'tet-blossom', 'name' => 'Mai Vàng, Đào Thắm & Đèn Lồng Tết'],
        'tet-swallows' => ['id' => 'tet-swallows', 'name' => 'Chim Én Mùa Xuân & Lộc Khai Xuân'],
        'moon-lotus' => ['id' => 'moon-lotus', 'name' => 'Trăng Rằm, Thỏ Ngọc & Hoa Sen Thu'],
        'christmas-tree' => ['id' => 'christmas-tree', 'name' => 'Vòng Nguyệt Quế Nơ Đỏ & Cây Thông'],
        'cocktail-cheers' => ['id' => 'cocktail-cheers', 'name' => 'Đôi Ly Cocktail Martini Lễ Hội'],
        'elder-longevity-crane' => ['id' => 'elder-longevity-crane', 'name' => 'Hạc Trường Thọ, Đào Tiên & Ấn Thọ'],
        'house-key' => ['id' => 'house-key', 'name' => 'Tổ Ấm Mới & Chìa Khóa Vàng Tân Gia'],
        'corp-gala-trophy' => ['id' => 'corp-gala-trophy', 'name' => 'Cúp Vàng Vinh Danh & Pháo Hoa Gala'],
        'graduation-cap' => ['id' => 'graduation-cap', 'name' => 'Mũ Cử Nhân & Cuộn Bằng Tốt Nghiệp'],
        'floral-bouquet' => ['id' => 'floral-bouquet', 'name' => 'Vòm Hoa Mẫu Đơn & Nhẫn Kỷ Niệm'],
    ];

    /**
     * @return array<string, array{id: string, name: string}>
     */
    public function getArtworkMotifs(): array
    {
        return self::ARTWORK_MOTIFS;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getPalettes(): array
    {
        return self::PALETTES;
    }

    /**
     * @return array<string, array{id: string, name: string, hex: string, flap_hex: string}>
     */
    public function getEnvelopeColors(): array
    {
        return self::ENVELOPE_COLORS;
    }

    /**
     * @return array<string, array{id: string, name: string}>
     */
    public function getEnvelopeLiners(): array
    {
        return self::ENVELOPE_LINERS;
    }

    /**
     * @return array<string, array{id: string, name: string}>
     */
    public function getCardBorders(): array
    {
        return self::CARD_BORDERS;
    }

    /**
     * @return array<string, array{id: string, name: string}>
     */
    public function getBackdrops(): array
    {
        return self::BACKDROPS;
    }

    /**
     * Trả về khung tranh minh họa SVG bao quanh 4 cạnh ngoài của tờ thiệp 1 trang (Full-Card Outer Frame Scene)
     * — Tọa độ được căn sát mép viền ngoài (Top y:10..50, Sides x:16..42 / 378..404, Bottom y:506..550) để TUYỆT ĐỐI KHÔNG ĐÈ LÊN CHỮ ở trung tâm.
     */
    public static function renderCardSceneSvg(string $motif): string
    {
        return match ($motif) {
            // 1. Rừng Thông Cổ Tích & Muông Thú (Cáo cam, Sóc nâu, Gấu con, Thỏ trắng, Nai vàng, Cây thông — Y hệt video Winter One-derland)
            'woodland-animals' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <!-- Hàng cây thông pastel sát đỉnh vòm -->
                <polygon points="155,14 142,46 168,46" fill="#BFDCE0" opacity="0.85"/>
                <polygon points="195,10 180,44 210,44" fill="#C9E2DF" opacity="0.9"/>
                <polygon points="272,16 258,48 286,48" fill="#F7D6D6" opacity="0.85"/>
                <!-- Sóc nâu & chim đỏ sát đỉnh phải -->
                <circle cx="240" cy="36" r="8" fill="#B68D6A"/>
                <circle cx="235" cy="27" r="5.5" fill="#B68D6A"/>
                <path d="M247 41 C260 37, 262 21, 250 19 C244 19, 245 31, 247 41 Z" fill="#9E7352"/>
                <circle cx="215" cy="38" r="4.5" fill="#D94B43"/>
                <!-- Chú Cáo Cam sát mép trái -->
                <path d="M26 146 L36 120 L48 146 Z" fill="#E07A48"/>
                <ellipse cx="35" cy="164" rx="11" ry="18" fill="#E07A48"/>
                <ellipse cx="37" cy="162" rx="6" ry="12" fill="#FFF7F0"/>
                <path d="M24 176 C10 170, 8 152, 19 148 C25 146, 26 162, 24 176 Z" fill="#D16432"/>
                <!-- Cây thông xanh sát mép hai bên -->
                <polygon points="384,116 366,154 402,154" fill="#B5D3C8"/>
                <polygon points="384,136 362,178 406,178" fill="#9AC0B2"/>
                <polygon points="34,250 18,288 50,288" fill="#F5D5D5"/>
                <polygon points="34,270 14,312 54,312" fill="#EBBFBF"/>
                <!-- Chú Gấu Nâu quàng khăn đỏ sát mép phải -->
                <circle cx="374" cy="272" r="7" fill="#6E4E37"/>
                <circle cx="396" cy="272" r="7" fill="#6E4E37"/>
                <circle cx="385" cy="284" r="14" fill="#825E44"/>
                <ellipse cx="385" cy="316" rx="16" ry="22" fill="#825E44"/>
                <path d="M371 296 Q385 303 399 296 L396 309 Q385 306 371 303 Z" fill="#D94B43"/>
                <!-- Thỏ trắng sát góc dưới trái -->
                <ellipse cx="44" cy="416" rx="11" ry="15" fill="#E2C7B0"/>
                <circle cx="47" cy="398" r="9" fill="#E2C7B0"/>
                <ellipse cx="43" cy="382" rx="3" ry="10" transform="rotate(-12 43 382)" fill="#E2C7B0"/>
                <ellipse cx="50" cy="382" rx="3" ry="10" transform="rotate(10 50 382)" fill="#F3D6CE"/>
                <!-- Hàng thông và Chú Nai Vàng sát đáy thiệp (y: 504..552) -->
                <polygon points="118,504 100,542 136,542" fill="#A9C8C0"/>
                <polygon points="302,504 284,542 320,542" fill="#A9C8C0"/>
                <rect x="194" y="518" width="36" height="17" rx="8.5" fill="#B88B61"/>
                <rect x="197" y="532" width="3.5" height="18" rx="1.5" fill="#9E734B"/>
                <rect x="206" y="532" width="3.5" height="18" rx="1.5" fill="#9E734B"/>
                <rect x="217" y="532" width="3.5" height="18" rx="1.5" fill="#9E734B"/>
                <rect x="225" y="532" width="3.5" height="18" rx="1.5" fill="#9E734B"/>
                <circle cx="192" cy="507" r="9" fill="#B88B61"/>
                <path d="M188 498 L183 487 M195 498 L200 487" stroke="#8C633E" stroke-width="2" stroke-linecap="round"/>
                <circle cx="212" cy="513" r="4" fill="#D94B43"/>
            </svg>',

            // 2. Tết Nguyên Đán — Mai Vàng, Đào Thắm, Đôi Chim Én & Đèn Lồng Đỏ (Sát mép khung ngoài)
            'tet-blossom', 'tet-swallows' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 72 Q68 42 128 20" stroke="#5C2C1D" stroke-width="3.5" stroke-linecap="round"/>
                <circle cx="44" cy="54" r="12" fill="#FFD54F"/><circle cx="44" cy="54" r="4" fill="#B71C1C"/>
                <circle cx="84" cy="34" r="13" fill="#FFCA28"/><circle cx="84" cy="34" r="4.5" fill="#B71C1C"/>
                <circle cx="122" cy="22" r="10" fill="#FFE082"/><circle cx="122" cy="22" r="3.5" fill="#B71C1C"/>
                <path d="M408 72 Q352 42 292 20" stroke="#5C2C1D" stroke-width="3.5" stroke-linecap="round"/>
                <circle cx="376" cy="54" r="12" fill="#F48FB1"/><circle cx="376" cy="54" r="4" fill="#880E4F"/>
                <circle cx="336" cy="34" r="13" fill="#F06292"/><circle cx="336" cy="34" r="4.5" fill="#880E4F"/>
                <circle cx="298" cy="22" r="10" fill="#F8BBD0"/><circle cx="298" cy="22" r="3.5" fill="#880E4F"/>
                <!-- Đèn lồng đỏ treo sát mép 2 bên -->
                <line x1="28" y1="82" x2="28" y2="128" stroke="#CFA860" stroke-width="2"/>
                <ellipse cx="28" cy="144" rx="15" ry="13" fill="#C62828" stroke="#FFD54F" stroke-width="1.8"/>
                <line x1="28" y1="157" x2="28" y2="176" stroke="#C62828" stroke-width="2"/>
                <line x1="392" y1="82" x2="392" y2="128" stroke="#CFA860" stroke-width="2"/>
                <ellipse cx="392" cy="144" rx="15" ry="13" fill="#C62828" stroke="#FFD54F" stroke-width="1.8"/>
                <line x1="392" y1="157" x2="392" y2="176" stroke="#C62828" stroke-width="2"/>
                <!-- Đôi chim én & đồng tiền vàng sát đáy thiệp (y: 515..548) -->
                <path d="M122 522 C138 510, 154 510, 168 520 C154 520, 144 528, 134 538 Z" fill="#85182A"/>
                <path d="M298 522 C282 510, 266 510, 252 520 C266 520, 276 528, 286 538 Z" fill="#85182A"/>
                <circle cx="210" cy="528" r="15" fill="#FFCA28" stroke="#B71C1C" stroke-width="2"/>
                <rect x="204.5" y="522.5" width="11" height="11" fill="#FFF8E1" stroke="#B71C1C" stroke-width="1.6"/>
            </svg>',

            // 3. Tết Trung Thu — Trăng Rằm Vàng, Đèn Lồng Cá Chép, Thỏ Ngọc & Hoa Sen
            'moon-lotus', 'moon-lantern' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="210" cy="34" r="22" fill="#FFE082" opacity="0.95"/>
                <path d="M148 44 Q172 36 194 44 T238 44" stroke="#CFA860" stroke-width="2" fill="none"/>
                <line x1="42" y1="14" x2="42" y2="54" stroke="#CFA860" stroke-width="1.8"/>
                <ellipse cx="42" cy="70" rx="18" ry="15" fill="#D84315" stroke="#FFD54F" stroke-width="1.8"/>
                <line x1="378" y1="14" x2="378" y2="54" stroke="#CFA860" stroke-width="1.8"/>
                <ellipse cx="378" cy="70" rx="18" ry="15" fill="#C62828" stroke="#FFD54F" stroke-width="1.8"/>
                <!-- Đầm hoa sen & Thỏ ngọc sát đáy (y: 508..548) -->
                <path d="M70 538 Q140 524 210 538 T350 538" stroke="#CFA860" stroke-width="1.8" fill="none"/>
                <path d="M210 502 C199 516, 199 532, 210 540 C221 532, 221 516, 210 502 Z" fill="#F48FB1"/>
                <path d="M192 512 C186 524, 195 536, 206 540 C198 528, 196 520, 192 512 Z" fill="#EC407A"/>
                <path d="M228 512 C234 524, 225 536, 214 540 C222 528, 224 520, 228 512 Z" fill="#EC407A"/>
                <ellipse cx="128" cy="526" rx="12" ry="9" fill="#FFF8EE" stroke="#CFA860" stroke-width="1.5"/>
                <ellipse cx="292" cy="526" rx="12" ry="9" fill="#FFF8EE" stroke="#CFA860" stroke-width="1.5"/>
            </svg>',

            // 4. Giáng Sinh — Cây Thông Tinsel Tree & Vòng Nguyệt Quế Nơ Đỏ
            'christmas-tree', 'holly-wreath', 'holly-garland' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M36 96 C36 36, 114 22, 210 22 C306 22, 384 36, 384 96" stroke="#255828" stroke-width="7" stroke-linecap="round" stroke-dasharray="12 7"/>
                <path d="M36 472 C36 526, 114 540, 210 540 C306 540, 384 526, 384 472" stroke="#255828" stroke-width="7" stroke-linecap="round" stroke-dasharray="12 7"/>
                <path d="M196 22 C176 6, 162 24, 186 34 L210 24 L234 34 C258 24, 244 6, 224 22 Z" fill="#C62828"/>
                <path d="M202 26 L186 52 M218 26 L234 52" stroke="#C62828" stroke-width="5" stroke-linecap="round"/>
                <circle cx="210" cy="24" r="6" fill="#8E0000"/>
                <!-- Cây thông nhỏ sát đáy (y: 488..544) -->
                <polygon points="210,486 190,512 230,512" fill="#1E4620"/>
                <polygon points="210,502 182,534 238,534" fill="#255828"/>
                <rect x="204" y="534" width="12" height="10" rx="2" fill="#6E3B1E"/>
                <circle cx="32" cy="190" r="4" fill="#C62828"/><circle cx="388" cy="190" r="4" fill="#C62828"/>
                <circle cx="32" cy="360" r="4" fill="#C62828"/><circle cx="388" cy="360" r="4" fill="#C62828"/>
            </svg>',

            // 5. Đêm Tiệc Cocktail & Sinh Nhật Người Lớn (Coupe & Martini / A Tini Bit Older)
            'cocktail-cheers', 'adult-martini-editorial' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M54 44 L58 56 L70 60 L58 64 L54 76 L50 64 L38 60 L50 56 Z" fill="#CFA860"/>
                <path d="M366 44 L370 56 L382 60 L370 64 L366 76 L362 64 L350 60 L362 56 Z" fill="#CFA860"/>
                <!-- Đôi ly Cocktail Martini sát đáy (y: 492..546) -->
                <path d="M154 494 H196 L175 518 Z" fill="#FAD2D4" stroke="#85182A" stroke-width="2"/>
                <line x1="175" y1="518" x2="175" y2="544" stroke="#85182A" stroke-width="2"/>
                <line x1="163" y1="544" x2="187" y2="544" stroke="#85182A" stroke-width="2" stroke-linecap="round"/>
                <path d="M224 498 H266 L245 522 Z" fill="#FDECC8" stroke="#85182A" stroke-width="2"/>
                <line x1="245" y1="522" x2="245" y2="546" stroke="#85182A" stroke-width="2"/>
                <line x1="233" y1="546" x2="257" y2="546" stroke="#85182A" stroke-width="2" stroke-linecap="round"/>
            </svg>',

            // 6. Đầy Tháng & Thôi Nôi — Nôi Mây, Trăng Sao & Bộ Đồ Bốc Thôi Nôi Chọn Nghề
            'baby-cradle', 'baby-thoi-noi-items' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M220 16 A20 20 0 1 0 220 56 A15 15 0 1 1 220 16 Z" fill="#F3D19C" stroke="#C5A059" stroke-width="1.8"/>
                <polygon points="88,42 95,28 103,36 111,24 119,36 127,28 134,42" fill="#FFE082" stroke="#C5A059" stroke-width="1.8"/>
                <circle cx="322" cy="38" r="11" fill="#E3F2FD"/><circle cx="336" cy="35" r="13" fill="#E3F2FD"/>
                <!-- Ngựa gỗ & Khối đồ chơi sát đáy (y: 506..546) -->
                <path d="M126 538 Q148 548 170 538" stroke="#C5A059" stroke-width="3.5" stroke-linecap="round" fill="none"/>
                <rect x="196" y="512" width="28" height="28" rx="5" fill="#FFF3E0" stroke="#CFA860" stroke-width="1.8"/>
                <circle cx="282" cy="524" r="12" fill="#D7B89C"/>
            </svg>',

            // 7. Sinh Nhật Trẻ Em — Pamper Sweet Party (Cờ lễ hội sát đỉnh, bóng bay sát mép ngoài, bánh kem sát đáy y:506..548 không đè chữ!)
            'kids-pamper-party', 'birthday-cake' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <!-- Dải cờ tam giác Bunting sát đỉnh vòm (y: 18..54) -->
                <path d="M48 24 Q210 56 372 24" stroke="#EC407A" stroke-width="1.8" fill="none"/>
                <polygon points="88,30 106,33 97,50" fill="#F48FB1"/>
                <polygon points="138,37 156,39 147,56" fill="#CE93D8"/>
                <polygon points="188,40 206,40 197,58" fill="#FFE082"/>
                <polygon points="238,40 256,38 247,56" fill="#80DEEA"/>
                <polygon points="288,35 306,31 297,50" fill="#F48FB1"/>
                <!-- Bóng bay sát mép ngoài cùng (x=28 và x=392, không chạm chữ tiêu đề!) -->
                <ellipse cx="28" cy="230" rx="13" ry="17" fill="#F48FB1"/><path d="M28 247 Q25 264 31 278" stroke="#AD1457" stroke-width="1.4"/>
                <ellipse cx="392" cy="230" rx="13" ry="17" fill="#CE93D8"/><path d="M392 247 Q395 264 389 278" stroke="#6A1B9A" stroke-width="1.4"/>
                <!-- Bánh sinh nhật 2 tầng nằm sát đáy thiệp (y: 508..548) dưới hẳn khối địa điểm -->
                <rect x="180" y="528" width="60" height="18" rx="5" fill="#F8BBD0" stroke="#AD1457" stroke-width="1.8"/>
                <rect x="190" y="512" width="40" height="16" rx="4" fill="#FFF9C4" stroke="#AD1457" stroke-width="1.8"/>
                <rect x="208" y="498" width="4" height="14" fill="#EC407A"/>
                <circle cx="210" cy="494" r="3.5" fill="#FFB300"/>
            </svg>',

            'kids-space-galaxy', 'graduation-star' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="76" cy="44" r="14" fill="#FFB74D"/>
                <ellipse cx="76" cy="44" rx="26" ry="6" transform="rotate(-18 76 44)" stroke="#FFE082" stroke-width="2.5" fill="none"/>
                <circle cx="344" cy="44" r="11" fill="#64B5F6"/>
                <path d="M210 14 L213 25 L224 28 L213 31 L210 42 L207 31 L196 28 L207 25 Z" fill="#FFD54F"/>
                <!-- Tên lửa sát đáy (y: 496..548) -->
                <path d="M210 496 C198 510, 198 532, 198 544 H222 C222 532, 222 510, 210 496 Z" fill="#ECEFF1" stroke="#1B2A4A" stroke-width="2"/>
                <circle cx="210" cy="524" r="5" fill="#4FC3F7" stroke="#1B2A4A" stroke-width="1.8"/>
                <polygon points="198,532 186,546 198,544" fill="#E53935"/>
                <polygon points="222,532 234,546 222,544" fill="#E53935"/>
            </svg>',

            // 8. Đại Lễ Mừng Thọ Ông Bà — Đôi Hạc Trường Thọ, Đào Tiên & Ấn Triện Chữ Thọ
            'elder-longevity-crane' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="210" cy="34" r="18" fill="#9E1B22" stroke="#FFD54F" stroke-width="2"/>
                <path d="M202 28 H218 M210 24 V44 M204 34 H216 M202 40 H218" stroke="#FFD54F" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M104 526 C120 510, 138 510, 152 522 C138 524, 124 532, 112 542 Z" fill="#FFF8E7" stroke="#CFA860" stroke-width="1.8"/>
                <path d="M316 526 C300 510, 282 510, 268 522 C282 524, 296 532, 308 542 Z" fill="#FFF8E7" stroke="#CFA860" stroke-width="1.8"/>
                <path d="M210 504 C194 508, 190 530, 210 544 C230 530, 226 508, 210 504 Z" fill="#F48FB1" stroke="#C2185B" stroke-width="1.8"/>
            </svg>',

            // 9. Tân Gia Nhà Mới — Mái Ấm Ánh Đèn Vàng, Chìa Khóa Vàng & Nhánh Khuynh Diệp
            'house-key' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M84 42 Q210 12 336 42" stroke="#689F38" stroke-width="2.2" fill="none"/>
                <circle cx="202" cy="30" r="7" stroke="#CFA860" stroke-width="2.5" fill="#FFF8E1"/>
                <line x1="209" y1="30" x2="225" y2="30" stroke="#CFA860" stroke-width="2.5" stroke-linecap="round"/>
                <!-- Ngôi nhà mới sát đáy (y: 500..548) -->
                <path d="M176 518 L210 494 L244 518" stroke="#85182A" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="184" y="516" width="52" height="32" rx="3" fill="#FFF8EE" stroke="#CFA860" stroke-width="2"/>
                <rect x="203" y="528" width="14" height="20" rx="7" fill="#85182A"/>
            </svg>',

            // 10. Tất Niên Công Ty (YEP) & Khai Trương Hồng Phát — Cúp Vinh Danh & Pháo Hoa
            'corp-gala-trophy' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="96" cy="38" r="2.5" fill="#CFA860"/>
                <path d="M96 24 V30 M96 46 V52 M82 38 H88 M104 38 H110" stroke="#CFA860" stroke-width="1.8" stroke-linecap="round"/>
                <circle cx="324" cy="38" r="2.5" fill="#CFA860"/>
                <path d="M324 24 V30 M324 46 V52 M310 38 H316 M332 38 H338" stroke="#CFA860" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M194 504 H226 V520 C226 530, 194 530, 194 520 Z" fill="#FFD54F" stroke="#B7892B" stroke-width="1.8"/>
                <line x1="210" y1="528" x2="210" y2="540" stroke="#B7892B" stroke-width="2.5"/>
                <rect x="196" y="540" width="28" height="6" rx="2" fill="#B7892B"/>
            </svg>',

            // 11. Lễ Tốt Nghiệp Đại Học & Thạc Sĩ — Mũ Cử Nhân & Cuộn Bằng Đỏ
            'graduation-cap' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <polygon points="210,14 166,32 210,50 254,32" fill="#1B2A4A" stroke="#CFA860" stroke-width="1.8"/>
                <path d="M184 40 V54 C184 60, 236 60, 236 54 V40" fill="#253961" stroke="#CFA860" stroke-width="1.8"/>
                <rect x="178" y="520" width="64" height="14" rx="7" fill="#FFF8E7" stroke="#CFA860" stroke-width="1.8"/>
                <rect x="205" y="518" width="10" height="18" rx="2" fill="#C62828"/>
            </svg>',

            // 12. Hoa Hồng & Mẫu Đơn 8/3, 20/10, 20/11 & Kỷ Niệm Ngày Cưới
            'floral-bouquet', 'rings-anniversary' => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="186" cy="30" r="11" fill="#F8BBD0"/>
                <circle cx="210" cy="26" r="14" fill="#F48FB1"/>
                <circle cx="234" cy="30" r="11" fill="#F8BBD0"/>
                <circle cx="200" cy="526" r="13" stroke="#CFA860" stroke-width="2.5" fill="none"/>
                <circle cx="220" cy="526" r="13" stroke="#85182A" stroke-width="2.5" fill="none"/>
            </svg>',

            default => '<svg class="mayhen_event_scene_svg" viewBox="0 0 420 560" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="210" cy="32" r="12" stroke="#CFA860" stroke-width="1.8"/>
                <circle cx="210" cy="528" r="12" stroke="#CFA860" stroke-width="1.8"/>
            </svg>',
        };
    }

    /**
     * Trả về hình vẽ SVG biểu tượng sự kiện (100% Pure Inline SVG, không phụ thuộc ảnh ngoài).
     */
    public static function renderArtworkSvg(string $motif): string
    {
        return match ($motif) {
            'woodland-animals' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><polygon points="34,36 16,76 52,76" fill="#B5D3C8"/><polygon points="88,40 72,78 104,78" fill="#F5D5D5"/><rect x="46" y="72" width="32" height="16" rx="8" fill="#B88B61"/><circle cx="44" cy="60" r="9" fill="#B88B61"/><path d="M40 52 L34 40 M46 52 L52 40" stroke="#8C633E" stroke-width="2.2" stroke-linecap="round"/><line x1="50" y1="86" x2="50" y2="104" stroke="#9E734B" stroke-width="3" stroke-linecap="round"/><line x1="72" y1="86" x2="72" y2="104" stroke="#9E734B" stroke-width="3" stroke-linecap="round"/><circle cx="62" cy="66" r="4" fill="#D94B43"/></svg>',
            'christmas-tree' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><polygon points="60,10 64,20 75,20 66,26 69,36 60,30 51,36 54,26 45,20 56,20" fill="#CFA860"/><polygon points="60,26 34,60 86,60" fill="#1E4620"/><polygon points="60,44 26,82 94,82" fill="#255828"/><polygon points="60,62 18,102 102,102" fill="#1E4620"/><rect x="52" y="102" width="16" height="12" rx="2" fill="#6E3B1E"/><circle cx="48" cy="54" r="3.5" fill="#C62828"/><circle cx="70" cy="52" r="3" fill="#F4E2B8"/><circle cx="42" cy="74" r="3.5" fill="#CFA860"/><circle cx="62" cy="72" r="4" fill="#C62828"/><circle cx="78" cy="76" r="3" fill="#F4E2B8"/></svg>',
            'holly-wreath', 'holly-garland' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="60" cy="62" r="36" stroke="#1E4620" stroke-width="7" stroke-dasharray="12 6"/><path d="M52 26 C44 16, 36 26, 48 32 L60 28 L72 32 C84 26, 76 16, 68 26 Z" fill="#B71C1C"/><path d="M56 28 L46 46 M64 28 L74 46" stroke="#B71C1C" stroke-width="4" stroke-linecap="round"/><circle cx="60" cy="27" r="4" fill="#85182A"/></svg>',
            'cocktail-cheers', 'adult-martini-editorial' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 36 H58 L40 60 Z" fill="#F8D7DA" stroke="#85182A" stroke-width="2.2"/><line x1="40" y1="60" x2="40" y2="92" stroke="#85182A" stroke-width="2.2"/><line x1="28" y1="92" x2="52" y2="92" stroke="#85182A" stroke-width="2.2" stroke-linecap="round"/><path d="M62 42 H98 L80 66 Z" fill="#FDECC8" stroke="#85182A" stroke-width="2.2"/><line x1="80" y1="66" x2="80" y2="96" stroke="#85182A" stroke-width="2.2"/><line x1="68" y1="96" x2="92" y2="96" stroke="#85182A" stroke-width="2.2" stroke-linecap="round"/></svg>',
            'tet-blossom', 'elder-longevity-crane' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="60" cy="60" r="42" fill="#9E1B22" stroke="#CFA860" stroke-width="2.5"/><path d="M28 84 Q56 64 88 38" stroke="#5C2C1D" stroke-width="3.5" stroke-linecap="round"/><circle cx="48" cy="64" r="10" fill="#FFD54F"/><circle cx="48" cy="64" r="3.5" fill="#B71C1C"/><circle cx="72" cy="46" r="12" fill="#FFCA28"/><circle cx="72" cy="46" r="4" fill="#B71C1C"/><circle cx="64" cy="74" r="8" fill="#FFE082"/></svg>',
            'tet-swallows', 'corp-gala-trophy' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="60" cy="60" r="40" stroke="#CFA860" stroke-width="2" fill="#FFF8E7"/><path d="M30 62 C44 46, 58 46, 72 56 C58 56, 48 64, 38 76 L34 66 L24 68 Z" fill="#85182A"/><path d="M62 40 C72 30, 84 30, 94 38 C84 38, 76 44, 70 52 Z" fill="#CFA860"/></svg>',
            'moon-lotus' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="60" cy="52" r="32" fill="#FFE082"/><path d="M24 72 Q42 66 56 74 T92 70" stroke="#CFA860" stroke-width="2" fill="none"/><path d="M60 66 C52 78, 52 92, 60 98 C68 92, 68 78, 60 66 Z" fill="#F48FB1"/><path d="M46 74 C42 84, 48 94, 58 98 C52 88, 50 80, 46 74 Z" fill="#F06292"/><path d="M74 74 C78 84, 72 94, 62 98 C68 88, 70 80, 74 74 Z" fill="#F06292"/></svg>',
            'moon-lantern' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="60" y1="12" x2="60" y2="28" stroke="#CFA860" stroke-width="2.5"/><ellipse cx="60" cy="56" rx="30" ry="26" fill="#C62828" stroke="#CFA860" stroke-width="2.5"/><ellipse cx="60" cy="56" rx="16" ry="26" stroke="#FFD54F" stroke-width="1.5" fill="none"/><rect x="48" y="26" width="24" height="6" rx="2" fill="#CFA860"/><rect x="48" y="80" width="24" height="6" rx="2" fill="#CFA860"/></svg>',
            'baby-cradle', 'baby-thoi-noi-items' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M74 26 A28 28 0 1 0 74 88 A22 22 0 1 1 74 26 Z" fill="#F3D19C" stroke="#C5A059" stroke-width="2"/><polygon points="82,42 84,48 90,48 85,52 87,58 82,54 77,58 79,52 74,48 80,48" fill="#CFA860"/><path d="M26 88 Q42 78 58 88 T90 88" stroke="#C5A880" stroke-width="2.5" stroke-linecap="round"/></svg>',
            'birthday-cake', 'kids-pamper-party' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="26" y="66" width="68" height="28" rx="6" fill="#F8BBD0" stroke="#880E4F" stroke-width="2"/><rect x="36" y="44" width="48" height="22" rx="5" fill="#FFF3E0" stroke="#880E4F" stroke-width="2"/><rect x="57" y="26" width="6" height="18" fill="#CFA860"/><path d="M60 14 C55 20, 57 25, 60 25 C63 25, 65 20, 60 14 Z" fill="#FF8F00"/></svg>',
            'house-key' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M24 58 L60 26 L96 58" stroke="#85182A" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="34" y="54" width="52" height="42" rx="4" fill="#FFF8EE" stroke="#CFA860" stroke-width="2.5"/><rect x="52" y="70" width="16" height="26" rx="8" fill="#85182A"/></svg>',
            'graduation-cap', 'kids-space-galaxy' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><polygon points="60,26 14,48 60,70 106,48" fill="#1B2A4A" stroke="#CFA860" stroke-width="2.2"/><path d="M32 58 V80 C32 88, 88 88, 88 80 V58" fill="#253961" stroke="#CFA860" stroke-width="2"/><path d="M96 53 V84" stroke="#CFA860" stroke-width="3" stroke-linecap="round"/></svg>',
            'floral-bouquet' => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="48" cy="48" r="14" fill="#F48FB1"/><circle cx="72" cy="46" r="13" fill="#F8BBD0"/><circle cx="60" cy="62" r="15" fill="#EC407A"/><path d="M46 72 L60 102 L74 72" stroke="#2E7D32" stroke-width="3" fill="#A5D6A7"/></svg>',
            default => '<svg viewBox="0 0 120 120" width="76" height="76" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="48" cy="60" r="22" stroke="#CFA860" stroke-width="4"/><circle cx="72" cy="60" r="22" stroke="#85182A" stroke-width="4"/></svg>',
        };
    }

    /**
     * Lưu bản nháp Thiệp Sự Kiện (Cho phép tất cả người dùng test và lưu nháp thoải mái).
     *
     * @param  array<string, mixed>  $input
     */
    public function saveDraft(?User $user, array $input, ?EventInvitation $existing = null): EventInvitation
    {
        $templateSlug = (string) ($input['template_slug'] ?? ($existing?->template_slug ?? 'day-thang-hoang-gia-nhi'));
        $template = $this->getTemplate($templateSlug) ?? $this->getTemplate('day-thang-hoang-gia-nhi');
        $defaults = $template['defaults'];

        $hostName = trim((string) ($input['host_name'] ?? ($existing?->host_name ?? $defaults['host_name'])));
        $title = trim((string) ($input['title'] ?? ($existing?->title ?? ('Thiệp Mời '.$hostName))));
        $palette = (string) ($input['color_palette'] ?? ($existing?->color_palette ?? $template['default_palette']));
        if (! array_key_exists($palette, self::PALETTES)) {
            $palette = $template['default_palette'];
        }

        $scheduleItems = $defaults['schedule_items'];
        if (! empty($input['schedule_items']) && is_array($input['schedule_items'])) {
            $cleanedSchedule = [];
            foreach ($input['schedule_items'] as $item) {
                $time = trim((string) ($item['time'] ?? ''));
                $itemTitle = trim((string) ($item['title'] ?? ''));
                if ($time !== '' || $itemTitle !== '') {
                    $cleanedSchedule[] = [
                        'time' => $time ?: '18:00',
                        'title' => $itemTitle ?: 'Hoạt động chính',
                    ];
                }
            }
            if (! empty($cleanedSchedule)) {
                $scheduleItems = array_slice($cleanedSchedule, 0, 5);
            }
        }

        $existingCustom = is_array($existing?->custom_data) ? $existing->custom_data : [];

        $envelopeColor = (string) ($input['envelope_color'] ?? ($existingCustom['envelope_color'] ?? $template['envelope_color']));
        if (! array_key_exists($envelopeColor, self::ENVELOPE_COLORS)) {
            $envelopeColor = $template['envelope_color'];
        }

        $envelopeLiner = (string) ($input['envelope_liner'] ?? ($existingCustom['envelope_liner'] ?? $template['envelope_liner']));
        if (! array_key_exists($envelopeLiner, self::ENVELOPE_LINERS)) {
            $envelopeLiner = $template['envelope_liner'];
        }

        $cardBorder = (string) ($input['card_border'] ?? ($existingCustom['card_border'] ?? $template['card_border']));
        if (! array_key_exists($cardBorder, self::CARD_BORDERS)) {
            $cardBorder = $template['card_border'];
        }

        $artworkMotif = (string) ($input['artwork_motif'] ?? ($existingCustom['artwork_motif'] ?? $template['artwork_motif']));
        if (! array_key_exists($artworkMotif, self::ARTWORK_MOTIFS)) {
            $artworkMotif = $template['artwork_motif'];
        }

        $backdrop = (string) ($input['backdrop'] ?? ($existingCustom['backdrop'] ?? $template['backdrop']));
        if (! array_key_exists($backdrop, self::BACKDROPS)) {
            $backdrop = $template['backdrop'];
        }

        $fontStyle = (string) ($input['font_style'] ?? ($existingCustom['font_style'] ?? 'serif'));
        if (! in_array($fontStyle, ['serif', 'sans', 'script'], true)) {
            $fontStyle = 'serif';
        }

        $showDatetimeVenue = array_key_exists('show_datetime_venue', $input)
            ? (bool) $input['show_datetime_venue']
            : (bool) ($existingCustom['show_datetime_venue'] ?? $defaults['show_datetime_venue']);

        $rsvpQuestion = trim((string) ($input['rsvp_question'] ?? ($existingCustom['rsvp_question'] ?? $defaults['rsvp_question'])));
        $customPhotoData = trim((string) ($input['custom_photo_data'] ?? ($existingCustom['custom_photo_data'] ?? '')));

        $payload = [
            'user_id' => $user?->id ?? $existing?->user_id,
            'template_slug' => $template['slug'],
            'category_slug' => $template['category_slug'],
            'title' => $title,
            'event_badge' => trim((string) ($input['event_badge'] ?? $defaults['event_badge'])),
            'host_name' => $hostName,
            'sub_host_name' => trim((string) ($input['sub_host_name'] ?? $defaults['sub_host_name'])),
            'invitation_message' => trim((string) ($input['invitation_message'] ?? $defaults['invitation_message'])),
            'event_date' => ! empty($input['event_date']) ? $input['event_date'] : $defaults['event_date'],
            'welcome_time' => trim((string) ($input['welcome_time'] ?? $defaults['welcome_time'])),
            'party_time' => trim((string) ($input['party_time'] ?? $defaults['party_time'])),
            'lunar_note' => trim((string) ($input['lunar_note'] ?? $defaults['lunar_note'])),
            'venue_name' => trim((string) ($input['venue_name'] ?? $defaults['venue_name'])),
            'venue_address' => trim((string) ($input['venue_address'] ?? $defaults['venue_address'])),
            'map_url' => trim((string) ($input['map_url'] ?? $defaults['map_url'])),
            'dresscode_note' => trim((string) ($input['dresscode_note'] ?? $defaults['dresscode_note'])),
            'color_palette' => $palette,
            'enable_rsvp' => array_key_exists('enable_rsvp', $input) ? (bool) $input['enable_rsvp'] : true,
            'enable_gift_qr' => array_key_exists('enable_gift_qr', $input) ? (bool) $input['enable_gift_qr'] : (bool) $defaults['enable_gift_qr'],
            'bank_name' => trim((string) ($input['bank_name'] ?? $defaults['bank_name'])),
            'bank_account_number' => trim((string) ($input['bank_account_number'] ?? $defaults['bank_account_number'])),
            'bank_account_holder' => trim((string) ($input['bank_account_holder'] ?? $defaults['bank_account_holder'])),
            'gift_note' => trim((string) ($input['gift_note'] ?? $defaults['gift_note'])),
            'custom_data' => [
                'schedule_items' => $scheduleItems,
                'envelope_color' => $envelopeColor,
                'envelope_liner' => $envelopeLiner,
                'card_border' => $cardBorder,
                'artwork_motif' => $artworkMotif,
                'backdrop' => $backdrop,
                'font_style' => $fontStyle,
                'show_datetime_venue' => $showDatetimeVenue,
                'rsvp_question' => $rsvpQuestion,
                'custom_photo_data' => $customPhotoData,
            ],
        ];

        if ($existing) {
            $existing->fill($payload);
            $existing->save();

            return $existing;
        }

        $baseSlug = Str::slug($hostName.'-'.$template['category_slug']) ?: 'loi-moi-su-kien';
        $uniqueSlug = $baseSlug.'-'.Str::lower(Str::random(6));

        $payload['slug'] = $uniqueSlug;
        $payload['status'] = 'draft';
        $payload['published_at'] = null;

        return EventInvitation::create($payload);
    }

    /**
     * Xuất bản chính thức Thiệp Sự Kiện (Chỉ gọi khi người dùng đã được xác thực có Gói Pro).
     */
    public function publishForProUser(User $user, EventInvitation $invitation): EventInvitation
    {
        $invitation->user_id = $user->id;
        $invitation->status = 'published';
        $invitation->published_at = $invitation->published_at ?? now();
        $invitation->save();

        return $invitation;
    }
}
