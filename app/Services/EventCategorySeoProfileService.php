<?php

namespace App\Services;

class EventCategorySeoProfileService
{
    /**
     * Hồ sơ bố cục & nội dung chuẩn SEO Google Top 1 riêng biệt cho từng trong 10 danh mục Thiệp Sự Kiện & Lễ Hội.
     *
     * @return array<string, mixed>
     */
    public function getProfile(string $categorySlug): array
    {
        $profiles = $this->allProfiles();

        return $profiles[$categorySlug] ?? $profiles['tet-nguyen-dan'];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function allProfiles(): array
    {
        return [
            'tet-nguyen-dan' => [
                'layout_variant' => 'festive-imperial',
                'section_order' => ['pillars', 'special', 'wording', 'editorial', 'steps', 'faq'],
                'hero_kicker' => 'Bộ Sưu Tập Xuân Hoàng Gia • Chuẩn Phong Tục Tết Việt',
                'quick_highlights' => [
                    'Mở phong bì gấm đỏ lót hoa mai 3D mượt mà',
                    'Tích hợp mã VietQR nhận lì xì lấy lộc đầu năm',
                    'Chuyển đổi 1 chạm giữa Thiệp Chúc Tết & Thiệp Mời Tân Niên',
                ],
                'pillars_title' => 'Vì sao thiệp chúc Tết & Tân Niên trên Mây Hẹn được ưa chuộng?',
                'pillars' => [
                    [
                        'icon' => '🧧',
                        'title' => 'Hai chế độ linh hoạt: Chúc Tết & Mời Tiệc',
                        'desc' => 'Tắt địa điểm để gửi thiệp chúc Tết kèm phong bao lì xì từ xa, hoặc bật đầy đủ ngày giờ, bản đồ cho tiệc Tất Niên & Tân Niên Khai Xuân.',
                    ],
                    [
                        'icon' => '✨',
                        'title' => 'Cá nhân hóa tên từng đối tác, người thân',
                        'desc' => 'Tạo hàng loạt đường dẫn riêng mang tên từng Quý đối tác, khách hàng hoặc ông bà, cô chú gửi qua Zalo trong vài giây.',
                    ],
                    [
                        'icon' => '📲',
                        'title' => 'Tương thích hoàn hảo khi gửi qua Zalo & Messenger',
                        'desc' => 'Mở ngay trên điện thoại không cần tải ứng dụng, hiển thị sắc nét như một tấm thiệp giấy mỹ thuật ngoài đời thực.',
                    ],
                ],
                'special_block' => [
                    'type' => 'comparison',
                    'kicker' => 'Phân Biệt Nhanh Theo Nhu Cầu',
                    'title' => 'Nên chọn Thiệp Chúc Tết Hay Thiệp Mời Tiệc Tân Niên?',
                    'columns' => ['Tiêu chí', 'Thiệp Chúc Tết & Lì Xì Từ Xa', 'Thiệp Mời Tiệc Tân Niên / Sum Vầy'],
                    'rows' => [
                        ['Mục đích sử dụng', 'Gửi lời chúc An Khang Thịnh Vượng tới đối tác, khách hàng, họ hàng ở xa', 'Mời họp mặt đầu xuân, tiệc khai xuân công ty, bữa cơm gia đình'],
                        ['Thông tin trên mặt thiệp', 'Lời chúc tứ tuyệt, tên gia đình/công ty, mã VietQR lì xì may mắn', 'Tên sự kiện, ngày giờ âm - dương lịch, địa điểm & bản đồ chỉ đường'],
                        ['Tương tác người nhận', 'Mở phong bì gấm đỏ, gửi lại lời chúc đầu năm & lì xì lấy lộc', 'Xác nhận tham dự (RSVP), số lượng khách đi cùng & xem chỉ đường'],
                    ],
                ],
                'editorial_sections' => [
                    [
                        'heading' => 'Cách viết lời chúc Tết Nguyên Đán tinh tế cho đối tác và người thân',
                        'body' => 'Một tấm thiệp chúc Tết điện tử đẹp không cần quá dài dòng mà quan trọng nhất là đúng vai vế và truyền tải được không khí ấm áp ngày xuân. Khi gửi đối tác hoặc khách hàng doanh nghiệp, bạn nên ưu tiên các cụm từ trang trọng như "An Khang Thịnh Vượng", "Vạn Sự Hanh Thông", "Hợp Tác Bền Vững". Với ông bà, cha mẹ và họ hàng, lời chúc nên gần gũi, nhấn mạnh sức khỏe dồi dào, gia đạo bình an và niềm vui sum vầy bên con cháu.',
                    ],
                    [
                        'heading' => 'Mẹo chọn màu phong bì và hoa văn chuẩn phong thủy ngày Tết',
                        'body' => 'Trong văn hóa Việt Nam, sắc Đỏ Nhung (tượng trưng cho hỷ sự, may mắn) kết hợp cùng lớp lót Nhũ Vàng Kim hoặc Vân Mây Hoa Mai luôn là lựa chọn hàng đầu giúp tấm thiệp toát lên vẻ sang trọng. Nếu tổ chức tiệc Tân Niên doanh nghiệp mang phong cách hiện đại, bạn có thể phối phong bì Vàng Hoàng Kim cùng khung viền chỉ vàng kép để tăng độ nhận diện chuyên nghiệp.',
                    ],
                ],
                'steps_title' => '3 bước tạo thiệp chúc Tết & mời Tân Niên online trong 2 phút',
                'steps' => [
                    ['title' => 'Chọn mẫu thiệp Tết ưng ý', 'desc' => 'Duyệt các mẫu thuần Việt bên trên, bấm "Tạo Thử & Sửa Ngay" để mở Studio thiết kế riêng biệt.'],
                    ['title' => 'Nhập lời chúc & bật lì xì VietQR', 'desc' => 'Chạm trực tiếp vào dòng chữ trên mặt thiệp để sửa tên gia đình/công ty, chọn lời chúc mẫu và gắn mã QR lì xì nếu cần.'],
                    ['title' => 'Xuất bản & gửi hàng loạt qua Zalo', 'desc' => 'Tạo đường dẫn mang tên từng người nhận ngay trong Tab Khách Mời để gửi lời chúc Tết chu đáo nhất.'],
                ],
                'faqs' => [
                    [
                        'q' => 'Tôi chỉ muốn gửi thiệp chúc Tết kèm lì xì chứ không tổ chức tiệc thì làm thế nào?',
                        'a' => 'Trong Tab 4 (Bản Đồ) của Studio, bạn chỉ cần tắt tùy chọn hiển thị ngày giờ và địa điểm tiệc. Tấm thiệp sẽ tự động rút gọn thành thiệp chúc Tết và lì xì đầu xuân tinh gọn.',
                    ],
                    [
                        'q' => 'Doanh nghiệp có thể gửi thiệp chúc Tết kèm tên riêng từng khách hàng không?',
                        'a' => 'Hoàn toàn được. Tại Tab 6 (Khách Mời) trong Studio, bạn nhập danh sách tên đối tác hoặc khách hàng, hệ thống tự sinh đường dẫn riêng có sẵn lời chào kính gửi đúng tên từng vị khách.',
                    ],
                    [
                        'q' => 'Mã QR lì xì đầu năm có hỗ trợ tất cả ngân hàng tại Việt Nam không?',
                        'a' => 'Có. Tính năng Phong Bao Lì Xì sử dụng chuẩn VietQR hỗ trợ quét trực tiếp từ mọi ứng dụng ngân hàng và ví điện tử phổ biến tại Việt Nam.',
                    ],
                    [
                        'q' => 'Có thể tạo thử thiệp chúc Tết miễn phí trước khi nâng cấp gói Pro không?',
                        'a' => 'Có 100%. Bạn được thoải mái chọn mẫu, đổi màu phong bì, sửa toàn bộ lời chúc và xem hiệu ứng rút thiệp hoàn toàn miễn phí trước khi quyết định xuất bản.',
                    ],
                ],
                'related_slugs' => ['tat-nien-khai-truong', 'tan-gia-nha-moi', 'tet-trung-thu'],
            ],

            'tet-trung-thu' => [
                'layout_variant' => 'moonlight-editorial',
                'section_order' => ['pillars', 'special', 'editorial', 'wording', 'steps', 'faq'],
                'hero_kicker' => 'Nghệ Thuật Ánh Trăng Rằm • Tết Đoàn Viên Tháng Tám',
                'quick_highlights' => [
                    'Tông xanh đêm trăng & vàng thu thanh lịch chuẩn Á Đông',
                    'Phù hợp cả thiệp doanh nghiệp lẫn đêm hội phá cỗ thiếu nhi',
                    'Tích hợp xác nhận tham dự & bản đồ tới địa điểm tổ chức',
                ],
                'pillars_title' => 'Điểm nhấn riêng của bộ sưu tập Thiệp Tết Trung Thu',
                'pillars' => [
                    [
                        'icon' => '🌕',
                        'title' => 'Thẩm mỹ Á Đông tinh tế, không lòe loẹt',
                        'desc' => 'Họa tiết trăng rằm, hoa sen, đèn lồng cá chép được vẽ nét mảnh thanh tao trên nền giấy kem lụa hoặc xanh đêm sao.',
                    ],
                    [
                        'icon' => '🏢',
                        'title' => 'Chuẩn mực cho thiệp tri ân đối tác & khách hàng',
                        'desc' => 'Gửi kèm hộp bánh Trung Thu qua mã QR hoặc tin nhắn Zalo để tăng dấu ấn thương hiệu và sự trân trọng.',
                    ],
                    [
                        'icon' => '🏮',
                        'title' => 'Sẵn sàng cho Đêm Hội Trăng Rằm & Tiệc Trà',
                        'desc' => 'Hiển thị rõ lịch rước đèn, múa lân, giờ phá cỗ và thu thập nhanh số lượng phụ huynh, em nhỏ tham dự.',
                    ],
                ],
                'special_block' => [
                    'type' => 'checklist',
                    'kicker' => 'Cẩm Nang Chuẩn Bị Thiệp Trung Thu',
                    'title' => '4 yếu tố giúp thiệp Tết Trung Thu ghi điểm tuyệt đối',
                    'items' => [
                        ['title' => 'Gửi sớm trước Rằm tháng Tám 7–14 ngày', 'desc' => 'Giúp khách hàng, đối tác và phụ huynh chủ động sắp xếp lịch trình đoàn viên bên gia đình.'],
                        ['title' => 'Chọn đúng tông màu theo đối tượng nhận', 'desc' => 'Xanh Đêm Hoàng Gia & Vàng Trăng hợp doanh nghiệp; Đỏ Gấm & Đèn Lồng hợp đêm hội thiếu nhi và khu dân cư.'],
                        ['title' => 'Ghi rõ lưu ý cho các bé khi mời phá cỗ', 'desc' => 'Nêu ngắn gọn thời gian đón khách, hoạt động rước đèn ông sao hoặc quy ước trang phục áo dài nếu có.'],
                        ['title' => 'Lời chúc ngắn gọn, giàu hình ảnh đoàn viên', 'desc' => 'Ưu tiên các thông điệp về sự sum vầy, bình an và viên mãn thay vì câu chữ quảng cáo khô khan.'],
                    ],
                ],
                'editorial_sections' => [
                    [
                        'heading' => 'Xu hướng gửi thiệp Trung Thu điện tử cho doanh nghiệp & gia đình',
                        'body' => 'Thay vì những tấm thiệp in giấy dễ thất lạc, thiệp Tết Trung Thu online giúp doanh nghiệp gửi lời tri ân tới hàng trăm khách hàng và đối tác ở khắp mọi miền chỉ trong vài phút. Khi người nhận mở đường dẫn trên điện thoại, phong bì xanh đêm lót bầu trời sao mở ra nhẹ nhàng, tạo cảm giác trang trọng và chu đáo hơn hẳn một tin nhắn chữ thông thường.',
                    ],
                    [
                        'heading' => 'Cách tổ chức Đêm Hội Trăng Rằm và quản lý danh sách tham dự gọn nhẹ',
                        'body' => 'Với ban quản trị chung cư, trường học hoặc công đoàn công ty tổ chức phá cỗ Trung Thu, tính năng Xác nhận tham dự (RSVP) ngay trên thiệp giúp thống kê chính xác số lượng phụ huynh và số bé đi cùng. Nhờ đó ban tổ chức chuẩn bị đủ đèn lồng, phần quà và mâm cỗ mà không lo thiếu hụt hay lãng phí.',
                    ],
                ],
                'steps_title' => '3 bước hoàn thiện thiệp Tết Trung Thu đẹp mắt',
                'steps' => [
                    ['title' => 'Chọn phong cách Trăng Rằm', 'desc' => 'Chọn mẫu thiệp chúc đoàn viên cho đối tác hoặc mẫu thiệp mời Đêm Hội Trăng Rằm, Tiệc Trà Thưởng Nguyệt.'],
                    ['title' => 'Tùy biến phong bì & lời chúc', 'desc' => 'Phối phong bì Xanh Đêm hoặc Đỏ Nhung, chọn sẵn lời chúc Trung Thu mẫu rồi điều chỉnh tên đơn vị của bạn.'],
                    ['title' => 'Gửi Zalo hoặc gắn mã QR lên hộp quà', 'desc' => 'Xuất bản thiệp để lấy đường dẫn gửi trực tiếp cho từng khách hàng hoặc in mã QR dán kèm hộp bánh Trung Thu.'],
                ],
                'faqs' => [
                    [
                        'q' => 'Thiệp Trung Thu có thể dùng gửi tri ân khách hàng doanh nghiệp không?',
                        'a' => 'Rất phù hợp. Bạn có thể tắt phần địa điểm tiệc để thiệp trở thành thiệp chúc Tết Đoàn Viên trang trọng, đồng thời cá nhân hóa tên từng đối tác trên đường dẫn gửi đi.',
                    ],
                    [
                        'q' => 'Tôi tổ chức Đêm Hội Trăng Rằm cho khu dân cư thì quản lý số bé tham gia ra sao?',
                        'a' => 'Khách mời bấm nút Xác nhận tham dự trên thiệp để điền tên phụ huynh, số người lớn và số em nhỏ đi cùng. Bạn có thể thêm câu hỏi tùy chỉnh như độ tuổi của bé để chuẩn bị quà phù hợp.',
                    ],
                    [
                        'q' => 'Có thể tải ảnh gia đình hoặc logo công ty lên mặt thiệp Trung Thu không?',
                        'a' => 'Có. Trong Tab 1 (Mặt Thiệp) của Studio, bạn có thể tải trực tiếp ảnh chân dung gia đình hoặc hình ảnh sự kiện lên khung tròn trung tâm mặt thiệp.',
                    ],
                    [
                        'q' => 'Người nhận mở thiệp Trung Thu trên điện thoại có bị nặng hay chậm không?',
                        'a' => 'Không. Toàn bộ thiệp được tối ưu hình ảnh cục bộ gọn nhẹ, mở mượt mà ngay trên trình duyệt Zalo, Facebook và Safari trong tích tắc.',
                    ],
                ],
                'related_slugs' => ['tet-nguyen-dan', 'giang-sinh-nam-moi', 'ngay-le-chuc-mung'],
            ],

            'giang-sinh-nam-moi' => [
                'layout_variant' => 'winter-pine',
                'section_order' => ['special', 'pillars', 'wording', 'editorial', 'steps', 'faq'],
                'hero_kicker' => 'Không Khí Lễ Hội Mùa Đông • Giáng Sinh & Năm Mới',
                'quick_highlights' => [
                    'Họa tiết cây thông, vòng nguyệt quế, sọc đỏ kem chuẩn lễ hội',
                    'Phong bì xanh rừng thông & đỏ nhung mở nắp mềm mại',
                    'Thống kê khách tham dự tiệc tối & đón giao thừa dễ dàng',
                ],
                'pillars_title' => 'Tạo không khí lễ hội ngay từ khoảnh khắc khách mở thiệp',
                'pillars' => [
                    [
                        'icon' => '🎄',
                        'title' => 'Chất liệu mùa đông sang trọng, hiện đại',
                        'desc' => 'Kết hợp hài hòa giữa xanh lá thông, đỏ trầm lễ hội, ánh nhũ vàng và khung viền sọc kẻ đặc trưng của mùa Giáng Sinh.',
                    ],
                    [
                        'icon' => '🥂',
                        'title' => 'Phù hợp mọi quy mô tiệc cuối năm',
                        'desc' => 'Từ bữa tối Giáng Sinh ấm cúng tại nhà, tiệc trao quà hội bạn thân cho đến đêm đếm ngược đón chào năm mới.',
                    ],
                    [
                        'icon' => '🎁',
                        'title' => 'Hỏi nhanh khẩu vị & ghi chú trao quà',
                        'desc' => 'Tùy chỉnh câu hỏi xác nhận tham dự để biết khách có ăn kiêng hay đăng ký tham gia bốc thăm trao quà hay không.',
                    ],
                ],
                'special_block' => [
                    'type' => 'checklist',
                    'kicker' => 'Bí Kíp Tổ Chức Đêm Tiệc Giáng Sinh Trọn Vẹn',
                    'title' => '4 lưu ý nên ghi rõ trên thiệp mời Giáng Sinh & Năm Mới',
                    'items' => [
                        ['title' => 'Quy ước trang phục (Đỏ, Xanh thông, Trắng kem)', 'desc' => 'Ghi rõ tông màu trang phục ngay trên mặt thiệp giúp bức ảnh kỷ niệm đêm tiệc đồng điệu và nổi bật hơn.'],
                        ['title' => 'Gợi ý ngân sách trò chơi trao quà bí mật', 'desc' => 'Nếu có tiết mục đổi quà, hãy nhắc ngắn gọn mức ngân sách gợi ý trong lời mời để mọi người dễ chuẩn bị.'],
                        ['title' => 'Thời gian đón khách và giờ khai tiệc', 'desc' => 'Đêm 24/12 và 31/12 thường đông xe cộ, ghi rõ khung giờ đón khách sớm 30 phút giúp bữa tiệc bắt đầu đúng nhịp.'],
                        ['title' => 'Ghim sẵn vị trí Google Maps chính xác', 'desc' => 'Giúp bạn bè và người thân tìm thẳng tới nhà hàng hoặc căn hộ tổ chức tiệc chỉ bằng 1 chạm.'],
                    ],
                ],
                'editorial_sections' => [
                    [
                        'heading' => 'Tạo thiệp Giáng Sinh online đẹp: Chúc an lành hay mời tiệc cuối năm?',
                        'body' => 'Mùa Giáng Sinh cuối tháng 12 là khoảng thời gian gắn kết đáng nhớ nhất trong năm. Với các mẫu thiệp trên Mây Hẹn, bạn có thể linh hoạt chọn kiểu thiệp chúc Giáng Sinh gửi tới bạn bè, đồng nghiệp ở xa hoặc chọn thiệp mời tiệc tối Giáng Sinh, tiệc đếm ngược đón năm mới có đủ ngày giờ, địa điểm và nút xác nhận tham dự trực tuyến.',
                    ],
                    [
                        'heading' => 'Gợi ý phối màu phong bì và lớp lót chuẩn phong cách lễ hội',
                        'body' => 'Ba bộ phối màu được yêu thích nhất cho thiệp Giáng Sinh là: (1) Phong bì Đỏ Nhung lót Nhũ Vàng Kim kết hợp mặt thiệp cây thông sao vàng; (2) Phong bì Trắng Kem lót Chấm Bi Đỏ kết hợp vòng nguyệt quế nơ đỏ cổ điển; và (3) Phong bì Xanh Rừng Thông lót Sọc Đỏ Kem cho tiệc họp mặt hội bạn thân trẻ trung.',
                    ],
                ],
                'steps_title' => '3 bước tự thiết kế thiệp Giáng Sinh & Năm Mới cực nhanh',
                'steps' => [
                    ['title' => 'Chọn mẫu thiệp mùa lễ hội', 'desc' => 'Lựa chọn phong cách cây thông, vòng lá thông hoặc tiệc tối hiện đại trong danh sách mẫu bên trên.'],
                    ['title' => 'Phối màu phong bì & viết lời mời', 'desc' => 'Đổi màu phong bì, lớp lót sọc lễ hội, nhập thời gian, địa điểm và lưu ý trang phục ngay trên bản xem trực tiếp.'],
                    ['title' => 'Gửi thiệp & theo dõi danh sách khách', 'desc' => 'Gửi đường dẫn thiệp vào nhóm bạn thân hoặc từng người qua Zalo và theo dõi lượt xác nhận tham dự theo thời gian thực.'],
                ],
                'faqs' => [
                    [
                        'q' => 'Tôi có thể đổi màu phong bì sang xanh rừng thông hoặc đỏ nhung không?',
                        'a' => 'Có đầy đủ. Trong Tab 2 (Phong Bì) của Studio, bạn tự do phối 8 màu vỏ phong bì và 8 lớp lót hoa văn lễ hội chỉ bằng 1 lần chạm.',
                    ],
                    [
                        'q' => 'Thiệp Giáng Sinh có dùng để gửi lời chúc an lành không kèm tiệc được không?',
                        'a' => 'Được. Bạn chỉ cần chọn các mẫu thuộc nhóm Thiệp Chúc hoặc gạt tắt phần Ngày giờ & Địa điểm trong Tab Bản Đồ của Studio.',
                    ],
                    [
                        'q' => 'Khách mời có thể gửi lại lời chúc Giáng Sinh trực tiếp trên thiệp không?',
                        'a' => 'Có. Nút Xác Nhận Tham Dự & Lời Chúc dưới chân thiệp cho phép người nhận gửi lời chúc an lành và xác nhận có mặt ngay lập tức.',
                    ],
                    [
                        'q' => 'Tôi có thể xem trước hiệu ứng mở phong bì trên điện thoại ngay khi đang thiết kế không?',
                        'a' => 'Có. Studio hỗ trợ chuyển đổi khung xem Máy Tính / Điện Thoại và nút Rút Thiệp để bạn kiểm tra trải nghiệm thực tế trước khi gửi.',
                    ],
                ],
                'related_slugs' => ['tat-nien-khai-truong', 'tet-nguyen-dan', 'sinh-nhat-nguoi-lon'],
            ],

            'day-thang-thoi-noi' => [
                'layout_variant' => 'baby-pastel',
                'section_order' => ['pillars', 'special', 'wording', 'editorial', 'steps', 'faq'],
                'hero_kicker' => 'Kỷ Niệm Cột Mốc Đầu Đời • Dành Riêng Cho Gia Đình Việt',
                'quick_highlights' => [
                    'Tải trực tiếp ảnh đáng yêu của bé lên mặt thiệp',
                    'Có sẵn ô ghi tên khai sinh, tên ở nhà & tên ba mẹ',
                    'Tích hợp bản đồ nhà hàng, RSVP và lì xì chúc bé mau lớn',
                ],
                'pillars_title' => 'Thiết kế thấu hiểu trọn vẹn lễ Đầy Tháng & Thôi Nôi của bé',
                'pillars' => [
                    [
                        'icon' => '👶',
                        'title' => 'Tải ảnh thật của bé sắc nét trên mặt thiệp',
                        'desc' => 'Khung tròn mềm mại đặt ngay trung tâm giúp bức ảnh bé cười rạng rỡ trở thành điểm nhấn khiến ông bà, cô chú thích mê.',
                    ],
                    [
                        'icon' => '🧸',
                        'title' => 'Đầy đủ tên khai sinh & tên gọi thân mật ở nhà',
                        'desc' => 'Hiển thị tự nhiên tên bé (ví dụ: Bé Nguyễn Minh Khôi — Bé Bơ) cùng tên Ba Mẹ để hai bên nội ngoại dễ nhận diện.',
                    ],
                    [
                        'icon' => '🎁',
                        'title' => 'Lì xì chúc bé & bản đồ chỉ đường tiện lợi',
                        'desc' => 'Khách ở xa vẫn có thể gửi lời chúc và lì xì lấy vía hay ăn chóng lớn cho bé qua mã VietQR an toàn.',
                    ],
                ],
                'special_block' => [
                    'type' => 'comparison',
                    'kicker' => 'Chuẩn Phong Tục Gia Đình Việt Nam',
                    'title' => 'Khác biệt khi soạn Thiệp Mời Đầy Tháng và Thiệp Mời Thôi Nôi',
                    'columns' => ['Hạng mục', 'Lễ Đầy Tháng (Tròn 1 Tháng)', 'Tiệc Thôi Nôi (Tròn 1 Tuổi)'],
                    'rows' => [
                        ['Cách tính ngày', 'Thường tính theo lịch âm ("gái lùi hai, trai lùi một")', 'Tính tròn 1 năm ngày sinh của bé (âm hoặc dương lịch)'],
                        ['Không khí bữa tiệc', 'Bữa cơm thân mật cúng Mụ, chủ yếu mời ông bà và người thân', 'Tiệc sinh nhật đầu đời rộn ràng, có tiết mục bốc đồ chọn nghề'],
                        ['Gợi ý mẫu thiệp', 'Tông Mây Trắng, Trăng Sao, màu kem dịu dàng thanh thoát', 'Tông Rừng Cổ Tích, Vườn Hoa, Pháo Giấy tươi vui có ảnh bé'],
                    ],
                ],
                'editorial_sections' => [
                    [
                        'heading' => 'Nên gửi thiệp mời Đầy Tháng, Thôi Nôi trước bao lâu là hợp lý?',
                        'body' => 'Ba mẹ nên gửi thiệp mời trước ngày tiệc khoảng 5 đến 10 ngày để ông bà, cô chú và bạn bè đồng nghiệp tiện sắp xếp công việc cuối tuần. Nhờ sử dụng thiệp điện tử, nếu cần điều chỉnh giờ đón khách hoặc đổi sảnh nhà hàng, bạn chỉ cần sửa lại trong Studio là toàn bộ đường dẫn đã gửi sẽ tự động cập nhật ngay lập tức.',
                    ],
                    [
                        'heading' => 'Cách chọn ảnh bé tải lên thiệp để hiển thị đẹp nhất',
                        'body' => 'Hãy chọn một bức ảnh chân dung rõ khuôn mặt bé, nền sáng dịu và có khoảng trống nhỏ quanh đầu bé để khi đặt vào khung tròn trên mặt thiệp không bị cắt mất chi tiết. Kết hợp ảnh bé cùng phong bì Hồng Phấn, Kem Lụa hoặc Xanh Đêm lót sao sẽ tạo nên một tấm thiệp kỷ niệm lưu giữ mãi về sau.',
                    ],
                ],
                'steps_title' => '3 bước tạo thiệp Đầy Tháng & Thôi Nôi cực dễ cho ba mẹ bận rộn',
                'steps' => [
                    ['title' => 'Chọn mẫu hợp bé trai hoặc bé gái', 'desc' => 'Chọn mẫu khu rừng cổ tích, mây trắng trăng sao hoặc vườn hoa ngọt ngào phù hợp với chủ đề trang trí tiệc.'],
                    ['title' => 'Tải ảnh bé & điền tên ở nhà', 'desc' => 'Tải ảnh từ điện thoại lên mặt thiệp, nhập tên bé, ngày giờ tiệc và ghim địa chỉ nhà hàng hoặc tư gia.'],
                    ['title' => 'Gửi Zalo nội ngoại & thống kê bàn tiệc', 'desc' => 'Gửi đường dẫn thiệp vào nhóm gia đình và xem danh sách xác nhận tham dự để đặt số mâm cỗ thật chuẩn xác.'],
                ],
                'faqs' => [
                    [
                        'q' => 'Tôi có thể ghi cả ngày âm lịch và ngày dương lịch trên thiệp Đầy Tháng không?',
                        'a' => 'Có. Trong Tab Bản Đồ của Studio có sẵn ô Ghi chú ngày âm lịch hiển thị ngay dưới ngày giờ dương lịch trên mặt thiệp.',
                    ],
                    [
                        'q' => 'Tải ảnh của bé từ điện thoại lên thiệp có bị mờ không?',
                        'a' => 'Không. Hệ thống tự động căn khung ảnh tròn sắc nét ở trung tâm thiệp và hiển thị mượt mà trên mọi dòng điện thoại.',
                    ],
                    [
                        'q' => 'Nếu gia đình không muốn hiện mã QR lì xì thì có tắt đi được không?',
                        'a' => 'Hoàn toàn được. Bạn chỉ cần gạt tắt mục Hộp Quà / Lì Xì trong Tab 5 (RSVP & QR) của Studio.',
                    ],
                    [
                        'q' => 'Có thể hỏi khách mời số trẻ nhỏ đi cùng để nhà hàng chuẩn bị ghế ăn dặm không?',
                        'a' => 'Có. Bạn chỉ cần nhập câu hỏi tùy chỉnh trong Tab RSVP & QR, khách mời sẽ trả lời trực tiếp ngay khi xác nhận tham dự.',
                    ],
                ],
                'related_slugs' => ['sinh-nhat-tre-em', 'tan-gia-nha-moi', 'sinh-nhat-nguoi-lon'],
            ],

            'sinh-nhat-tre-em' => [
                'layout_variant' => 'kids-playful',
                'section_order' => ['pillars', 'special', 'wording', 'editorial', 'steps', 'faq'],
                'hero_kicker' => 'Thế Giới Tuổi Thơ Rực Rỡ • Dành Cho Bé Trai & Bé Gái',
                'quick_highlights' => [
                    'Đa dạng chủ đề: Công Chúa Kẹo Ngọt, Phi Hành Gia, Khu Rừng Xanh',
                    'Tải ảnh bé cười rạng rỡ ngay trên mặt thiệp bo sóng',
                    'Hỏi nhanh phụ huynh số bé đi cùng & lưu ý dị ứng đồ ăn',
                ],
                'pillars_title' => 'Vì sao phụ huynh chọn tạo thiệp sinh nhật bé trên Mây Hẹn?',
                'pillars' => [
                    [
                        'icon' => '🎂',
                        'title' => 'Màu sắc tươi sáng nhưng vẫn tinh tế',
                        'desc' => 'Tông hồng kẹo ngọt, xanh vũ trụ hay xanh khu rừng được tiết chế hài hòa, nhìn hiện đại và đáng yêu trên màn hình điện thoại.',
                    ],
                    [
                        'icon' => '🚀',
                        'title' => 'Đúng sở thích của bé từ 2 đến 12 tuổi',
                        'desc' => 'Dễ dàng chọn chủ đề phù hợp cho bé gái thích bánh kem công chúa hay bé trai mê phi thuyền khám phá ngân hà.',
                    ],
                    [
                        'icon' => '📝',
                        'title' => 'Thu thập RSVP từ nhóm phụ huynh cực gọn',
                        'desc' => 'Chỉ cần gửi 1 đường dẫn vào nhóm Zalo lớp học, phụ huynh xác nhận ngay để ba mẹ chốt suất ăn và túi quà cảm ơn.',
                    ],
                ],
                'special_block' => [
                    'type' => 'timeline',
                    'kicker' => 'Gợi Ý Lịch Trình Tiệc Sinh Nhật 2 Tiếng Hoàn Hảo',
                    'title' => 'Bố trí khung giờ để các bé vui hết mình mà không bị mệt',
                    'items' => [
                        ['time' => '30 phút đầu', 'title' => 'Đón bạn nhỏ & chụp ảnh check-in', 'desc' => 'Cho các bé làm quen, chụp ảnh kỷ niệm cùng bé chủ tiệc trước khi bắt đầu trò chơi.'],
                        ['time' => '45 phút tiếp', 'title' => 'Trò chơi tập thể hoặc chú hề tạo hình', 'desc' => 'Tổ chức trò chơi nhẹ nhàng, tô tượng hoặc đố vui có thưởng để khuấy động không khí.'],
                        ['time' => '25 phút giữa', 'title' => 'Hát mừng sinh nhật & thổi nến cắt bánh', 'desc' => 'Khoảnh khắc đáng nhớ nhất để ghi hình gia đình và toàn thể các bạn nhỏ vây quanh bánh kem.'],
                        ['time' => '20 phút cuối', 'title' => 'Dùng tiệc nhẹ & trao túi quà cảm ơn', 'desc' => 'Phục vụ đồ ăn nhẹ hợp khẩu vị trẻ em và gửi tặng túi quà nhỏ khi phụ huynh đón bé ra về.'],
                    ],
                ],
                'editorial_sections' => [
                    [
                        'heading' => 'Mẹo viết thiệp mời sinh nhật cho bé để phụ huynh dễ nắm thông tin',
                        'body' => 'Khi mời các bạn cùng lớp mẫu giáo hoặc tiểu học, phụ huynh thường quan tâm nhất đến 4 thông tin: tên bé kèm tên ở nhà để nhận ra bạn cùng lớp, khung giờ bắt đầu và kết thúc dự kiến, địa chỉ có bản đồ chỉ đường rõ ràng, và số điện thoại liên hệ của ba mẹ chủ tiệc. Trình bày gọn gàng trên 1 trang thiệp duy nhất giúp phụ huynh đọc xong chỉ trong 5 giây.',
                    ],
                    [
                        'heading' => 'Gửi thiệp sinh nhật online vừa tiết kiệm vừa giữ trọn kỷ niệm',
                        'body' => 'So với việc in thiệp giấy phát tay dễ bị rơi mất trong cặp sách của bé, thiệp điện tử gửi qua Zalo đảm bảo 100% phụ huynh nhận được thông tin chính xác. Sau bữa tiệc, gia đình vẫn có thể lưu lại toàn bộ lời chúc dễ thương từ cô chú và các bạn nhỏ như một cuốn nhật ký tuổi thơ của con.',
                    ],
                ],
                'steps_title' => '3 bước tạo thiệp mời sinh nhật bé yêu trong tích tắc',
                'steps' => [
                    ['title' => 'Cùng bé chọn chủ đề yêu thích', 'desc' => 'Mở danh sách mẫu sinh nhật trẻ em để bé tự tay chọn kiểu bóng bay, bánh kem, phi hành gia hay khu rừng cổ tích.'],
                    ['title' => 'Thêm ảnh bé & thông tin tiệc', 'desc' => 'Tải ảnh chân dung của bé, điền số tuổi, ngày giờ, địa điểm và câu hỏi ghi chú khẩu vị cho phụ huynh.'],
                    ['title' => 'Gửi vào nhóm Zalo lớp & gia đình', 'desc' => 'Xuất bản để nhận đường dẫn gọn đẹp, chia sẻ một lần và theo dõi số lượng bạn nhỏ tham dự thật nhàn tênh.'],
                ],
                'faqs' => [
                    [
                        'q' => 'Tôi có thể đổi khung tranh minh họa từ công chúa sang phi hành gia ngay trong Studio không?',
                        'a' => 'Có. Trong Tab 1 (Mặt Thiệp) có sẵn 15 chủ đề khung tranh minh họa sự kiện để bạn đổi qua lại chỉ với 1 lần chạm.',
                    ],
                    [
                        'q' => 'Làm sao biết có bao nhiêu bạn nhỏ sẽ tới dự để đặt suất ăn và bánh kem?',
                        'a' => 'Tính năng Xác nhận tham dự (RSVP) trên thiệp ghi nhận đầy đủ tên phụ huynh, số lượng người tham dự và ghi chú đi kèm.',
                    ],
                    [
                        'q' => 'Có cần tắt phần mã QR mừng quà trên thiệp sinh nhật trẻ em không?',
                        'a' => 'Mặc định các mẫu sinh nhật trẻ em đã tắt sẵn phần mã QR để giữ sự tự nhiên khi mời bạn cùng lớp, bạn có thể bật lại bất cứ lúc nào nếu muốn.',
                    ],
                    [
                        'q' => 'Gửi đường dẫn thiệp vào nhóm phụ huynh trên Zalo có hiện ảnh xem trước đẹp không?',
                        'a' => 'Có. Đường dẫn thiệp có sẵn tiêu đề và mô tả rõ ràng giúp phụ huynh nhận ra ngay thiệp mời sinh nhật của bé.',
                    ],
                ],
                'related_slugs' => ['day-thang-thoi-noi', 'sinh-nhat-nguoi-lon', 'tet-trung-thu'],
            ],

            'sinh-nhat-nguoi-lon' => [
                'layout_variant' => 'editorial-milestone',
                'section_order' => ['special', 'pillars', 'wording', 'editorial', 'steps', 'faq'],
                'hero_kicker' => 'Từ Tiệc Sinh Nhật 25-30-40 Hiện Đại Đến Đại Lễ Mừng Thọ',
                'quick_highlights' => [
                    'Phong cách tạp chí tối giản sang trọng cho sinh nhật người lớn',
                    'Bộ mẫu Đại Lễ Mừng Thọ 70, 80, 90 tuổi trang nghiêm chuẩn mực',
                    'Xác nhận tham dự nhanh & ghim bản đồ nhà hàng chỉ 1 chạm',
                ],
                'pillars_title' => 'Trọn vẹn hai phong cách: Hiện đại tinh tế & Trang nghiêm báo hiếu',
                'pillars' => [
                    [
                        'icon' => '🥂',
                        'title' => 'Sinh nhật cột mốc 25, 30, 40 tuổi cực gu',
                        'desc' => 'Bố cục thoáng đãng kiểu tạp chí kết hợp phong bì xanh đêm, hồng phấn hoặc vàng ấm dành cho bữa tối thân mật.',
                    ],
                    [
                        'icon' => '🍑',
                        'title' => 'Đại Lễ Mừng Thọ Ông Bà chuẩn truyền thống',
                        'desc' => 'Họa tiết đôi hạc đào tiên, viền gấm đỏ hoàng gia và chữ Thọ trang trọng thể hiện trọn vẹn lòng hiếu thảo của con cháu.',
                    ],
                    [
                        'icon' => '📍',
                        'title' => 'Gửi khách ở xa & quản lý bàn tiệc chuẩn xác',
                        'desc' => 'Đặc biệt hữu ích khi tổ chức tiệc mừng thọ quy tụ đông đảo con cháu, họ hàng từ nhiều tỉnh thành về chung vui.',
                    ],
                ],
                'special_block' => [
                    'type' => 'comparison',
                    'kicker' => 'Chọn Đúng Phong Cách Theo Độ Tuổi',
                    'title' => 'So sánh bố cục Thiệp Sinh Nhật Người Lớn và Thiệp Mừng Thọ',
                    'columns' => ['Yếu tố thiết kế', 'Sinh Nhật Tuổi 20 – 50 Hiện Đại', 'Đại Lễ Mừng Thọ 70 – 90 Tuổi'],
                    'rows' => [
                        ['Bảng màu khuyên dùng', 'Xanh Đêm Ánh Bạc, Vàng Ấm, Hồng Phấn Tối Giản', 'Đỏ Gấm Hoàng Gia, Vàng Kim, Nâu Gỗ Trầm Ấm'],
                        ['Văn phong lời mời', 'Gần gũi, hiện đại, nhấn mạnh niềm vui hội ngộ bạn bè', 'Kính cẩn, trang trọng, nêu rõ tuổi thọ & lời chúc Phúc Thọ'],
                        ['Thông tin cần lưu ý', 'Quy ước trang phục, giờ khai tiệc, bản đồ nhà hàng', 'Tên Cụ Ông/Cụ Bà, ngày âm - dương lịch, sơ đồ sảnh tiệc'],
                    ],
                ],
                'editorial_sections' => [
                    [
                        'heading' => 'Cách viết thiệp mời Đại Lễ Mừng Thọ chuẩn mực, giữ tròn chữ Hiếu',
                        'body' => 'Khi soạn thiệp mời mừng thọ (thất tuần 70 tuổi, bát tuần 80 tuổi hay cửu tuần 90 tuổi), gia đình cần ghi đầy đủ danh xưng kính trọng của Cụ Ông, Cụ Bà cùng đại diện con cháu đứng tên kính mời. Lời mời nên trang nhã, thể hiện niềm tự hào về tấm gương sống thọ, gia phong nề nếp và sự trân quý khi đón tiếp họ hàng, thông gia, láng giềng tới chung vui.',
                    ],
                    [
                        'heading' => 'Mời tiệc sinh nhật người lớn tinh tế như thế nào để khách hào hứng tham dự?',
                        'body' => 'Với tiệc sinh nhật tuổi 30 hoặc 40 bên bạn bè và đồng nghiệp thân thiết, một tấm thiệp một trang tinh gọn kèm hiệu ứng mở phong bì sang trọng sẽ tạo cảm giác được trân trọng hơn rất nhiều so với tin nhắn mời vội. Bạn chỉ cần giữ lời mời ngắn gọn 2–3 câu chân thành, ghi rõ thời gian, địa điểm và mở nút xác nhận tham dự để dễ dàng đặt bàn riêng.',
                    ],
                ],
                'steps_title' => '3 bước tạo thiệp Sinh Nhật hoặc Đại Lễ Mừng Thọ sang trọng',
                'steps' => [
                    ['title' => 'Chọn đúng phong cách bữa tiệc', 'desc' => 'Chọn mẫu tối giản sang trọng cho sinh nhật bạn bè hoặc mẫu gấm đỏ hạc tiên cho lễ mừng thọ Ông Bà.'],
                    ['title' => 'Chỉnh lời mời & chọn phông chữ', 'desc' => 'Tùy chọn phông chữ cổ điển hoặc hiện đại, điền tên nhân vật chính, ngày giờ và địa điểm nhà hàng.'],
                    ['title' => 'Xuất bản & gửi riêng cho từng khách', 'desc' => 'Tạo danh sách đường dẫn cá nhân hóa tên từng vị khách quý để gửi qua Zalo thật trang trọng và chu đáo.'],
                ],
                'faqs' => [
                    [
                        'q' => 'Thiệp Mừng Thọ có thể dùng chung cho cả Cụ Ông và Cụ Bà không?',
                        'a' => 'Có. Bạn có thể nhập tên cả Cụ Ông và Cụ Bà trên cùng dòng tiêu đề chính, bố cục mặt thiệp sẽ tự động căn giữa cân đối và dễ đọc.',
                    ],
                    [
                        'q' => 'Người lớn tuổi mở thiệp trên điện thoại có dễ đọc chữ không?',
                        'a' => 'Rất dễ đọc. Thiệp được thiết kế 1 trang rõ ràng, tương phản chữ cao, không có hiệu ứng rối mắt và hiển thị đầy đủ thông tin ngay khi phong bì mở ra.',
                    ],
                    [
                        'q' => 'Tôi có thể thêm ảnh chân dung nhân vật chính lên thiệp sinh nhật không?',
                        'a' => 'Có. Trong Tab Mặt Thiệp, bạn có thể tải ảnh chân dung lên khung tròn tinh tế hoặc để trống để thiệp hiển thị thuần nghệ thuật chữ.',
                    ],
                    [
                        'q' => 'Khách ở xa không về dự lễ mừng thọ có gửi lời chúc online được không?',
                        'a' => 'Được. Khách mời bấm vào nút Xác Nhận Tham Dự & Lời Chúc để gửi lời chúc trường thọ tới Ông Bà ngay trên tấm thiệp.',
                    ],
                ],
                'related_slugs' => ['ngay-le-chuc-mung', 'tan-gia-nha-moi', 'tet-nguyen-dan'],
            ],

            'tan-gia-nha-moi' => [
                'layout_variant' => 'warm-home',
                'section_order' => ['pillars', 'special', 'editorial', 'wording', 'steps', 'faq'],
                'hero_kicker' => 'An Cư Lạc Nghiệp • Mừng Tổ Ấm Mới Đong Đầy Hạnh Phúc',
                'quick_highlights' => [
                    'Ghim bản đồ Google Maps dẫn thẳng tới địa chỉ nhà mới',
                    'Phù hợp cả biệt thự, nhà phố lẫn căn hộ chung cư hiện đại',
                    'Tích hợp RSVP thống kê mâm cỗ & hộp quà mừng tân gia VietQR',
                ],
                'pillars_title' => 'Giải quyết mọi bất tiện khi mời khách tới nhà mới lần đầu',
                'pillars' => [
                    [
                        'icon' => '🗺️',
                        'title' => 'Bản đồ chỉ đường chính xác từng số nhà, tòa căn hộ',
                        'desc' => 'Khách lần đầu tới nhà mới không cần gọi điện hỏi đường nhờ nút Chỉ Đường Bản Đồ ghim tọa độ trực tiếp dưới chân thiệp.',
                    ],
                    [
                        'icon' => '🏡',
                        'title' => 'Ghi rõ hướng dẫn gửi xe & mã sảnh chung cư',
                        'desc' => 'Dễ dàng ghi thêm số tầng, tên tòa nhà hoặc bãi đỗ ô tô ngay trong phần địa điểm để khách tới nhà thật thoải mái.',
                    ],
                    [
                        'icon' => '🥂',
                        'title' => 'Chủ động đặt bàn tiệc nhờ xác nhận tham dự',
                        'desc' => 'Nắm rõ số lượng khách người lớn, trẻ nhỏ và khẩu vị chay/mặn trước ngày tân gia để gia chủ đón tiếp chu toàn.',
                    ],
                ],
                'special_block' => [
                    'type' => 'checklist',
                    'kicker' => 'Kinh Nghiệm Mời Tân Gia Chu Đáo',
                    'title' => '4 chi tiết nhỏ giúp khách tới dự tiệc tân gia cảm thấy ấm lòng',
                    'items' => [
                        ['title' => 'Ghi cụ thể tòa nhà, số căn hộ hoặc ngõ dễ tìm', 'desc' => 'Nhà mới xây hoặc khu đô thị mới thường chưa quen tên đường, hãy kèm một mốc địa danh gần nhất cạnh địa chỉ.'],
                        ['title' => 'Ghi rõ cả ngày âm lịch nhập trạch và ngày dương lịch', 'desc' => 'Giúp họ hàng lớn tuổi và bạn bè trẻ đều nắm lịch chính xác, tránh nhầm lẫn ngày cuối tuần.'],
                        ['title' => 'Gửi thiệp trước tiệc tân gia từ 5–7 ngày', 'desc' => 'Đủ thời gian để người thân sắp xếp công việc và chọn món quà mừng tân gia ý nghĩa cho gia đình.'],
                        ['title' => 'Cập nhật ngay nếu đổi giờ tiệc hoặc khu vực đỗ xe', 'desc' => 'Với thiệp online, bạn sửa một lần trong Studio là toàn bộ khách mời đều thấy thông tin mới nhất.'],
                    ],
                ],
                'editorial_sections' => [
                    [
                        'heading' => 'Nên chọn mẫu thiệp tân gia truyền thống hay hiện đại?',
                        'body' => 'Nếu tiệc tân gia có đông đủ họ hàng hai bên và tổ chức mâm cỗ trang trọng, các mẫu phong bì Đỏ Nhung lót Nhũ Vàng Kim với viền chỉ vàng kép (như Phúc Lộc An Gia) sẽ mang lại cảm giác ấm cúng và cát tường. Ngược lại, với tiệc tân gia căn hộ chung cư hoặc nhà phố hiện đại mời chủ yếu bạn bè, đồng nghiệp, các tông Xanh Rừng Thông hoặc Nâu Gỗ Trầm sẽ toát lên gu thẩm mỹ tinh tế của gia chủ.',
                    ],
                    [
                        'heading' => 'Lời mời tân gia hay: Chân thành, ấm áp và không cầu kỳ',
                        'body' => 'Một lời mời tân gia chạm đến cảm xúc người đọc thường bắt đầu từ niềm vui hoàn thiện tổ ấm sau thời gian vun đắp, sau đó bày tỏ mong muốn được đón người thân tới thăm nhà và dùng bữa cơm thân mật. Bạn có thể chọn ngay các đoạn lời văn mẫu chuẩn mực bên dưới để đưa vào thiệp chỉ bằng 1 chạm.',
                    ],
                ],
                'steps_title' => '3 bước tạo thiệp mời Tân Gia kèm bản đồ chỉ đường',
                'steps' => [
                    ['title' => 'Chọn mẫu thiệp hợp phong cách ngôi nhà', 'desc' => 'Lựa chọn mẫu truyền thống đỏ vàng, tổ ấm xanh bình yên hoặc gỗ trầm ấm cúng ngay phía trên.'],
                    ['title' => 'Điền địa chỉ nhà mới & dán link Google Maps', 'desc' => 'Nhập tên vợ chồng/gia đình, ngày giờ dùng tiệc, địa chỉ chi tiết và đường dẫn bản đồ trong Tab Bản Đồ.'],
                    ['title' => 'Gửi thiệp mời & đón khách tới chung vui', 'desc' => 'Gửi đường dẫn cá nhân hóa cho từng gia đình khách mời qua Zalo và theo dõi danh sách xác nhận tham dự.'],
                ],
                'faqs' => [
                    [
                        'q' => 'Tôi có thể dán trực tiếp link Google Maps nhà mới vào thiệp tân gia không?',
                        'a' => 'Có. Trong Tab 4 (Bản Đồ) của Studio, bạn dán đường dẫn chia sẻ từ Google Maps, nút Chỉ Đường Bản Đồ trên thiệp sẽ dẫn thẳng tới đúng vị trí nhà bạn.',
                    ],
                    [
                        'q' => 'Thiệp tân gia có thể tải ảnh chụp mặt tiền ngôi nhà mới lên không?',
                        'a' => 'Hoàn toàn được. Bạn có thể tải ảnh ngôi nhà mới hoặc ảnh gia đình tại Tab 1 (Mặt Thiệp) để tạo dấu ấn riêng biệt.',
                    ],
                    [
                        'q' => 'Bạn bè ở xa không tới dự tân gia được có thể gửi lời chúc và quà mừng không?',
                        'a' => 'Có. Khách ở xa có thể gửi lời chúc mừng tân gia trực tiếp trên thiệp và quét mã VietQR nếu gia đình bật tính năng Hộp Quà Mừng.',
                    ],
                    [
                        'q' => 'Có thể tạo riêng link mời ghi "Kính mời Gia đình Anh Minh" không?',
                        'a' => 'Có. Tính năng tạo link hàng loạt ở Tab 6 (Khách Mời) giúp bạn gắn đúng danh xưng từng gia đình lên đầu tấm thiệp.',
                    ],
                ],
                'related_slugs' => ['tet-nguyen-dan', 'tat-nien-khai-truong', 'sinh-nhat-nguoi-lon'],
            ],

            'tat-nien-khai-truong' => [
                'layout_variant' => 'corporate-executive',
                'section_order' => ['pillars', 'special', 'editorial', 'wording', 'steps', 'faq'],
                'hero_kicker' => 'Chuẩn Mực Doanh Nghiệp • Tất Niên, Khai Trương & Tri Ân',
                'quick_highlights' => [
                    'Thiết kế sang trọng nâng tầm hình ảnh thương hiệu',
                    'Tạo hàng trăm đường dẫn mời mang tên riêng từng Đối tác',
                    'Thống kê danh sách khách xác nhận tham dự tập trung',
                ],
                'pillars_title' => 'Giải pháp thiệp mời sự kiện doanh nghiệp chuyên nghiệp & tiết kiệm',
                'pillars' => [
                    [
                        'icon' => '🏆',
                        'title' => 'Ấn tượng ngay từ phong bì mở nắp 3D',
                        'desc' => 'Thay thế file ảnh tĩnh nhàm chán bằng trải nghiệm mở phong bì xanh hoàng gia hoặc đỏ nhung lót nhũ vàng đẳng cấp.',
                    ],
                    [
                        'icon' => '🤝',
                        'title' => 'Cá nhân hóa tên từng Quý Đối Tác & Khách Hàng',
                        'desc' => 'Nhập danh sách khách mời một lần để xuất hàng loạt đường dẫn riêng có sẵn lời chào kính gửi đúng chức danh, tên công ty.',
                    ],
                    [
                        'icon' => '📊',
                        'title' => 'Chốt danh sách tham dự nhanh cho đội ngũ sự kiện',
                        'desc' => 'Khách mời xác nhận tham dự trực tiếp trên điện thoại, giúp bộ phận hành chính nhân sự nắm chắc sĩ số đặt sảnh tiệc.',
                    ],
                ],
                'special_block' => [
                    'type' => 'comparison',
                    'kicker' => 'Tối Ưu Theo Từng Sự Kiện Công Ty',
                    'title' => 'Chọn đúng mẫu cho Tiệc Tất Niên, Lễ Khai Trương & Hội Nghị Tri Ân',
                    'columns' => ['Sự kiện doanh nghiệp', 'Tông màu & phong bì khuyên dùng', 'Điểm cần nhấn mạnh trên mặt thiệp'],
                    'rows' => [
                        ['Tiệc Tất Niên Cuối Năm', 'Xanh Đêm Hoàng Gia lót Nhũ Vàng Kim', 'Chủ đề năm, giờ đón khách, quy ước trang phục & sảnh tiệc'],
                        ['Lễ Khai Trương Cửa Hàng / Văn Phòng', 'Đỏ Nhung Hoàng Gia lót Vàng Kim', 'Giờ cắt băng khai trương, địa chỉ cơ sở mới & nút Google Maps'],
                        ['Đêm Tiệc Tri Ân Đối Tác', 'Vàng Hoàng Kim / Kem Lụa Tối Giản', 'Danh xưng trân trọng từng khách mời & thông điệp đồng hành'],
                    ],
                ],
                'editorial_sections' => [
                    [
                        'heading' => 'Vì sao doanh nghiệp chuyển sang thiệp mời điện tử cho Tất Niên & Khai Trương?',
                        'body' => 'Với các sự kiện có từ 50 đến 500 khách mời gồm đối tác, khách hàng thân thiết và cán bộ nhân viên, việc in ấn và gửi chuyển phát nhanh thiệp giấy vừa tốn kém chi phí vừa khó thống kê ai sẽ tham dự. Thiệp mời điện tử chuẩn mực giúp đội ngũ tổ chức phát hành thiệp chỉ trong một buổi sáng, đồng thời vẫn giữ nguyên sự trang trọng khi gửi qua Zalo và Email.',
                    ],
                    [
                        'heading' => 'Chuẩn mực soạn lời mời Khai Trương & Tất Niên ghi điểm với đối tác',
                        'body' => 'Lời mời doanh nghiệp cần ngắn gọn, chuẩn văn phong tiếng Việt hành chính – đối ngoại: nêu rõ tên đơn vị tổ chức, ý nghĩa cột mốc (tổng kết năm cũ hoặc khai trương cơ sở mới), thời gian chính xác (tách rõ giờ đón khách và giờ khai mạc) cùng địa điểm đầy đủ tên sảnh, tầng và bản đồ dẫn đường.',
                    ],
                ],
                'steps_title' => '3 bước phát hành thiệp mời Tất Niên hoặc Khai Trương chuyên nghiệp',
                'steps' => [
                    ['title' => 'Chọn mẫu thiệp hợp nhận diện thương hiệu', 'desc' => 'Chọn mẫu Xanh Hoàng Gia cho tiệc Tất Niên, Đỏ Gấm cho Lễ Khai Trương hoặc Vàng Kim cho đêm tiệc Tri Ân.'],
                    ['title' => 'Nhập thông tin sự kiện & tải logo/ảnh', 'desc' => 'Cập nhật tên công ty, chủ đề sự kiện, thời gian đón khách, sảnh tiệc và dán đường dẫn Google Maps trung tâm hội nghị.'],
                    ['title' => 'Tạo danh sách link riêng gửi từng đối tác', 'desc' => 'Dán danh sách tên Quý đối tác vào Tab Khách Mời để sao chép nhanh đường dẫn mời riêng biệt cho từng vị khách.'],
                ],
                'faqs' => [
                    [
                        'q' => 'Công ty có thể tải logo thương hiệu lên mặt thiệp Tất Niên hoặc Khai Trương không?',
                        'a' => 'Có. Bạn có thể tải ảnh logo công ty lên khung trung tâm ở Tab 1 (Mặt Thiệp) để tăng độ nhận diện thương hiệu ngay khi mở phong bì.',
                    ],
                    [
                        'q' => 'Có gửi được đường dẫn ghi riêng tên từng công ty đối tác không?',
                        'a' => 'Có. Tại Tab 6 (Khách Mời), bạn chỉ cần nhập mỗi dòng một tên đối tác, hệ thống tự tạo đường dẫn mời có hiển thị đúng dòng "Kính gửi: ..." trên đầu thiệp.',
                    ],
                    [
                        'q' => 'Nếu địa điểm hoặc giờ đón khách thay đổi gấp trước sự kiện thì sao?',
                        'a' => 'Bạn chỉ cần chỉnh sửa lại trong bản nháp đã xuất bản và lưu lại, toàn bộ đường dẫn đã gửi trước đó sẽ tự động cập nhật thông tin mới nhất.',
                    ],
                    [
                        'q' => 'Thiệp Khai Trương có nên bật phần mã QR không?',
                        'a' => 'Tùy nhu cầu sự kiện: lễ khai trương cửa hàng cá nhân có thể bật nếu bạn bè muốn gửi quà mừng từ xa; tiệc doanh nghiệp lớn nên tắt để giữ sự trang trọng tuyệt đối.',
                    ],
                ],
                'related_slugs' => ['tet-nguyen-dan', 'tan-gia-nha-moi', 'tot-nghiep-thanh-tuu'],
            ],

            'tot-nghiep-thanh-tuu' => [
                'layout_variant' => 'academic-honor',
                'section_order' => ['pillars', 'special', 'wording', 'editorial', 'steps', 'faq'],
                'hero_kicker' => 'Cột Mốc Thanh Xuân Rực Rỡ • Lễ Tốt Nghiệp & Vinh Danh',
                'quick_highlights' => [
                    'Họa tiết mũ cử nhân, vòng nguyệt quế & sao vinh danh tinh tế',
                    'Tải trực tiếp ảnh mặc áo cử nhân lên mặt thiệp mời',
                    'Ghim đúng cổng trường, hội trường & khung giờ chụp ảnh',
                ],
                'pillars_title' => 'Tấm thiệp kỷ niệm xứng tầm ngày nhận bằng tốt nghiệp',
                'pillars' => [
                    [
                        'icon' => '🎓',
                        'title' => 'Lưu giữ trọn vẹn khoảnh khắc thanh xuân',
                        'desc' => 'Tải ngay bức ảnh kỷ yếu hoặc ảnh khoác áo cử nhân lên mặt thiệp để gửi mời gia đình, thầy cô và hội bạn thân.',
                    ],
                    [
                        'icon' => '🏛️',
                        'title' => 'Chỉ rõ hội trường, cổng trường & giờ chụp ảnh',
                        'desc' => 'Khuôn viên đại học thường rất rộng; nút Chỉ Đường Bản Đồ giúp ba mẹ và bạn bè tìm đúng khu vực hội trường không bị lạc.',
                    ],
                    [
                        'icon' => '🌟',
                        'title' => 'Phù hợp cả lễ tốt nghiệp Thạc sĩ & tiệc vinh danh',
                        'desc' => 'Có đầy đủ mẫu trang trọng cho lễ nhận bằng Cao học, tiệc thăng chức hoặc tri ân lãnh đạo nghỉ hưu.',
                    ],
                ],
                'special_block' => [
                    'type' => 'checklist',
                    'kicker' => 'Bí Kíp Cho Ngày Lễ Tốt Nghiệp Suôn Sẻ',
                    'title' => '4 thông tin tân cử nhân nhất định phải ghi trên thiệp mời',
                    'items' => [
                        ['title' => 'Tách rõ giờ làm lễ trong hội trường và giờ chụp ảnh ngoài sân', 'desc' => 'Nhiều trường giới hạn người vào hội trường; ghi rõ khung giờ chụp ảnh ngoài sân giúp bạn bè tới đúng lúc gặp được bạn.'],
                        ['title' => 'Ghi cụ thể tên cổng vào & tòa nhà giảng đường', 'desc' => 'Kèm theo đường dẫn Google Maps ghim chính xác cổng gửi xe gần hội trường nhất.'],
                        ['title' => 'Số điện thoại liên hệ nhanh (hoặc số bạn thân đi cùng)', 'desc' => 'Lúc lên sân khấu nhận bằng bạn không thể nghe máy, ghi chú ngắn gọn giúp gia đình liên lạc thuận tiện.'],
                        ['title' => 'Thêm địa điểm bữa tiệc ăn mừng sau buổi lễ nếu có', 'desc' => 'Giúp người thân và nhóm bạn dễ sắp xếp lịch trình ở lại chung vui trọn buổi.'],
                    ],
                ],
                'editorial_sections' => [
                    [
                        'heading' => 'Tại sao nên gửi thiệp mời tốt nghiệp online thay vì nhắn tin rời rạc?',
                        'body' => 'Ngày tốt nghiệp đại học chỉ có một lần trong đời và là dịp hiếm hoi cả gia đình, bạn cấp ba, bạn đại học cùng quy tụ. Một tấm thiệp mời tốt nghiệp chỉn chu với màu xanh học thuật và mũ cử nhân mạ vàng giúp lời mời của bạn trở nên đặc biệt hơn, đồng thời gom gọn toàn bộ thời gian, địa điểm hội trường và nút bản đồ trong một đường dẫn duy nhất.',
                    ],
                    [
                        'heading' => 'Mở rộng cho tiệc tri ân chặng đường cống hiến & lễ nghỉ hưu',
                        'body' => 'Bên cạnh lễ tốt nghiệp Đại học, Cao học, bộ sưu tập này còn có những thiết kế tông gỗ trầm và vàng ấm trang nhã dành riêng cho tiệc vinh danh thành tựu, tiệc thăng chức hoặc tiệc chia tay tri ân lãnh đạo nghỉ hưu sau nhiều năm cống hiến.',
                    ],
                ],
                'steps_title' => '3 bước tạo thiệp mời Lễ Tốt Nghiệp cực chất trong 2 phút',
                'steps' => [
                    ['title' => 'Chọn mẫu thiệp tốt nghiệp yêu thích', 'desc' => 'Chọn tông Xanh Đêm Hoàng Gia, Xanh Nguyệt Quế hoặc Nâu Gỗ Vinh Danh phù hợp với phong cách của bạn.'],
                    ['title' => 'Tải ảnh kỷ yếu & ghi giờ chụp ảnh', 'desc' => 'Thêm ảnh mặc áo cử nhân lên mặt thiệp, điền tên trường, hội trường, giờ làm lễ và dán link bản đồ trường.'],
                    ['title' => 'Gửi mời gia đình & hội bạn thân', 'desc' => 'Gửi đường dẫn thiệp qua Zalo, Messenger và nhận trước thật nhiều lời chúc mừng tốt nghiệp ý nghĩa.'],
                ],
                'faqs' => [
                    [
                        'q' => 'Tôi có thể tải ảnh chụp kỷ yếu hoặc ảnh mặc lễ phục tốt nghiệp lên thiệp không?',
                        'a' => 'Có. Trong Tab 1 (Mặt Thiệp), bạn tải trực tiếp ảnh từ điện thoại hoặc máy tính lên khung tròn trung tâm mặt thiệp.',
                    ],
                    [
                        'q' => 'Làm sao để bạn bè tìm đúng hội trường trong khuôn viên trường đại học rộng lớn?',
                        'a' => 'Bạn chỉ cần ghim chính xác tòa nhà hội trường trên Google Maps rồi dán đường dẫn vào Tab 4 (Bản Đồ) của Studio.',
                    ],
                    [
                        'q' => 'Nếu lễ tốt nghiệp không tổ chức tiệc ăn uống mà chỉ mời tới chụp ảnh thì ghi thế nào?',
                        'a' => 'Bạn chỉ cần sửa dòng lời mời thành khung giờ chụp ảnh kỷ niệm tại sân trường và giữ nguyên phần ngày giờ, địa điểm hội trường.',
                    ],
                    [
                        'q' => 'Bạn bè ở xa không tới dự lễ tốt nghiệp được có gửi lời chúc trên thiệp được không?',
                        'a' => 'Có. Nút Xác Nhận Tham Dự & Lời Chúc giúp bạn bè từ xa gửi những lời chúc mừng tốt nghiệp chân thành lưu lại trên thiệp của bạn.',
                    ],
                ],
                'related_slugs' => ['ngay-le-chuc-mung', 'sinh-nhat-nguoi-lon', 'tat-nien-khai-truong'],
            ],

            'ngay-le-chuc-mung' => [
                'layout_variant' => 'floral-tribute',
                'section_order' => ['special', 'pillars', 'wording', 'editorial', 'steps', 'faq'],
                'hero_kicker' => 'Nghệ Thuật Gửi Trao Yêu Thương • 8/3, 20/10, 20/11 & Kỷ Niệm',
                'quick_highlights' => [
                    'Đủ 3 chủ đề lớn: Tôn Vinh Phái Đẹp, Tri Ân Thầy Cô & Kỷ Niệm Ngày Cưới',
                    'Họa tiết hoa mẫu đơn màu nước & nhẫn vàng son lãng mạn',
                    'Có thể gửi thiệp chúc từ xa hoặc mời bữa tối kỷ niệm ấm cúng',
                ],
                'pillars_title' => 'Một tấm thiệp tinh tế thay ngàn lời muốn nói trong những ngày đặc biệt',
                'pillars' => [
                    [
                        'icon' => '💐',
                        'title' => 'Thiệp chúc mừng 8/3 & 20/10 dịu dàng, cuốn hút',
                        'desc' => 'Hoa mẫu đơn màu nước trên nền hồng phấn kem lụa kèm lời chúc tinh tế gửi tặng Mẹ, Vợ, Người yêu và đồng nghiệp nữ.',
                    ],
                    [
                        'icon' => '📖',
                        'title' => 'Thiệp tri ân Thầy Cô 20/11 trang nhã, sâu sắc',
                        'desc' => 'Phù hợp để cá nhân hoặc tập thể lớp cũ gửi lời chúc Ngày Nhà Giáo Việt Nam hoặc mời họp lớp thăm thầy cô.',
                    ],
                    [
                        'icon' => '💍',
                        'title' => 'Thiệp kỷ niệm ngày cưới 1 năm, 5 năm, 10 năm, 25 năm',
                        'desc' => 'Hâm nóng tình yêu vợ chồng hoặc mời người thân dự bữa tối kỷ niệm ngày chung đôi trong không gian ấm áp.',
                    ],
                ],
                'special_block' => [
                    'type' => 'comparison',
                    'kicker' => 'Chọn Đúng Mẫu Cho 3 Dịp Quan Trọng Nhất',
                    'title' => 'Gợi ý sử dụng bộ sưu tập Thiệp Chúc Mừng & Kỷ Niệm',
                    'columns' => ['Dịp lễ / Kỷ niệm', 'Chế độ thiệp khuyên dùng', 'Điểm nhấn nên thêm vào thiệp'],
                    'rows' => [
                        ['Quốc Tế Phụ Nữ 8/3 & Phụ Nữ VN 20/10', 'Thiệp Chúc Từ Xa (Tắt địa điểm tiệc)', 'Ảnh bó hoa/người thương, lời chúc chân thành & quà bất ngờ'],
                        ['Ngày Nhà Giáo Việt Nam 20/11', 'Thiệp Tri Ân hoặc Mời Họp Lớp', 'Tên tập thể lớp/niên khóa, câu thơ tri ân thầy cô kính mến'],
                        ['Kỷ Niệm Ngày Cưới (1, 5, 10, 25 năm)', 'Thiệp Mời Bữa Tối Kỷ Niệm', 'Ảnh hai vợ chồng, số năm bên nhau, nhà hàng & bản đồ'],
                    ],
                ],
                'editorial_sections' => [
                    [
                        'heading' => 'Bí quyết chọn lời chúc 8/3, 20/10 và 20/11 chạm đến trái tim người nhận',
                        'body' => 'Người nhận luôn cảm nhận được sự chân thành qua những chi tiết được cá nhân hóa. Khi tặng thiệp 8/3 hay 20/10 cho Mẹ hoặc Vợ, hãy nhắc đến một kỷ niệm nhỏ hoặc lời cảm ơn cụ thể thay vì sao chép câu chúc chung chung. Với thiệp 20/11 gửi Thầy Cô, đừng quên ghi rõ tên lớp và niên khóa để Thầy Cô nhận ra ngay những cô cậu học trò thân thương năm nào.',
                    ],
                    [
                        'heading' => 'Kỷ niệm ngày cưới: Lưu giữ hành trình trăm năm đầy lãng mạn',
                        'body' => 'Dù là kỷ niệm 1 năm ngày cưới ngọt ngào hay lễ cưới bạc 25 năm son sắt, một tấm thiệp kỷ niệm ngày cưới có in ảnh cặp đôi cùng lời hẹn ước mới sẽ là món quà tinh thần vô giá. Bạn có thể gửi riêng cho người bạn đời hoặc gửi mời hai bên gia đình cùng bạn bè thân thiết tới dự bữa tối kỷ niệm ấm cúng.',
                    ],
                ],
                'steps_title' => '3 bước tạo thiệp Chúc Mừng 8/3, 20/10, 20/11 hoặc Kỷ Niệm Ngày Cưới',
                'steps' => [
                    ['title' => 'Chọn mẫu đúng dịp lễ của bạn', 'desc' => 'Chọn mẫu hoa mẫu đơn cho 8/3 & 20/10, mẫu trang nhã cho 20/11 hoặc mẫu nhẫn vàng son cho kỷ niệm ngày cưới.'],
                    ['title' => 'Chọn lời chúc mẫu & cá nhân hóa tên', 'desc' => 'Dùng 1 chạm chọn lời chúc có sẵn trong Studio, sau đó thêm tên người nhận và ảnh kỷ niệm nếu muốn.'],
                    ['title' => 'Gửi ngay qua Zalo tạo bất ngờ ngọt ngào', 'desc' => 'Xuất bản thiệp để gửi trực tiếp tới người phụ nữ bạn yêu thương, thầy cô kính mến hoặc khách mời bữa tối kỷ niệm.'],
                ],
                'faqs' => [
                    [
                        'q' => 'Tôi muốn gửi thiệp chúc 8/3 và 20/10 cho từng chị em trong công ty có được không?',
                        'a' => 'Rất dễ dàng. Bạn chỉ cần tạo 1 mẫu thiệp duy nhất rồi nhập danh sách tên từng đồng nghiệp nữ trong Tab Khách Mời để xuất đường dẫn chúc riêng từng người.',
                    ],
                    [
                        'q' => 'Thiệp 20/11 có thể dùng để mời họp lớp tri ân thầy cô không?',
                        'a' => 'Có. Bạn chỉ cần bật công tắc hiển thị Ngày giờ & Địa điểm trong Tab Bản Đồ để biến thiệp chúc 20/11 thành thiệp mời họp lớp có bản đồ và RSVP.',
                    ],
                    [
                        'q' => 'Thiệp kỷ niệm ngày cưới có hỗ trợ tải ảnh cưới của hai vợ chồng lên không?',
                        'a' => 'Có. Bạn tải ảnh cặp đôi tại Tab 1 (Mặt Thiệp), ảnh sẽ hiển thị sắc nét trong khung tròn mềm mại giữa mặt thiệp.',
                    ],
                    [
                        'q' => 'Thiệp chúc mừng gửi qua Zalo có mất phí mở xem với người nhận không?',
                        'a' => 'Hoàn toàn miễn phí cho người nhận. Chỉ cần bấm vào đường dẫn là tấm thiệp mở phong bì ngay trên điện thoại không cần đăng nhập.',
                    ],
                ],
                'related_slugs' => ['sinh-nhat-nguoi-lon', 'tot-nghiep-thanh-tuu', 'tet-nguyen-dan'],
            ],
        ];
    }
}
