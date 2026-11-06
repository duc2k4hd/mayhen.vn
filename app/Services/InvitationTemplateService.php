<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationTemplateService
{
    /**
     * Cache static in-memory để không đọc lặp lại file JSON trong cùng request.
     */
    protected static array $schemaCache = [];

    /**
     * Danh sách 10 Section chuẩn hóa của hệ thống Mây Hẹn (Đầy đủ 10 phần tiêu chuẩn).
     */
    public const CANONICAL_SECTION_ORDER = [
        'hero',
        'story',
        'parents',
        'countdown_calendar',
        'events',
        'timeline',
        'dresscode',
        'album',
        'gift',
        'ending',
    ];

    /**
     * Danh sách key Section legacy để tương thích ngược.
     */
    public const LEGACY_SECTION_KEYS = [
        'couple',
        'groom_family',
        'bride_family',
    ];

    /**
     * Định nghĩa chuẩn mực 10 phần của một mẫu thiệp cưới Mây Hẹn.
     */
    public static function getCanonicalSections(): array
    {
        return [
            'hero' => [
                'id' => 'hero',
                'title' => 'Trang Bìa Chính',
                'short_title' => 'Trang Bìa',
                'icon' => 'sparkles',
                'description' => 'Tên cô dâu chú rể, loại lễ và ảnh đại diện bìa thiệp',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.ceremony_type',
                        'label' => 'Loại hình lễ cưới',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Lễ Thành Hôn',
                        'placeholder' => 'Ví dụ: Lễ Thành Hôn, Lễ Vu Quy...',
                        'max' => 50,
                    ],
                    [
                        'key' => 'wedding.groom_name',
                        'label' => 'Họ và tên Chú rể',
                        'type' => 'text',
                        'required' => true,
                        'default' => 'Minh Tuấn',
                        'placeholder' => 'Ví dụ: Minh Tuấn',
                        'max' => 30,
                    ],
                    [
                        'key' => 'wedding.bride_name',
                        'label' => 'Họ và tên Cô dâu',
                        'type' => 'text',
                        'required' => true,
                        'default' => 'Thu Hà',
                        'placeholder' => 'Ví dụ: Thu Hà',
                        'max' => 30,
                    ],
                    [
                        'key' => 'wedding.wedding_date',
                        'label' => 'Ngày cử hành hôn lễ',
                        'type' => 'date',
                        'required' => true,
                        'default' => '2026-12-20',
                        'placeholder' => 'YYYY-MM-DD',
                    ],
                    [
                        'key' => 'wedding.extra_data.wedding_lunar_date',
                        'label' => 'Ngày âm lịch cử hành hôn lễ',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Nhằm ngày 12 tháng 11 năm Bính Ngọ',
                        'placeholder' => 'Ví dụ: Nhằm ngày 12 tháng 11 năm Bính Ngọ',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.cover_photo_url',
                        'label' => 'Ảnh bìa đại diện thiệp (Tỉ lệ chuẩn 3:4)',
                        'type' => 'image',
                        'required' => false,
                        'default' => '7da1e3a6d59c1a98.webp',
                        'placeholder' => 'Đường dẫn ảnh bìa',
                    ],
                    [
                        'key' => 'wedding.extra_data.slogan',
                        'label' => 'Câu slogan mở màn',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Giữa muôn ngàn vì sao, chúng ta đã tìm thấy nhau',
                        'placeholder' => 'Câu thông điệp mở màn thiệp cưới...',
                        'max' => 200,
                    ],
                ],
            ],
            'story' => [
                'id' => 'story',
                'title' => 'Lời Ngỏ & Chuyện Tình Yêu',
                'short_title' => 'Lời Ngỏ',
                'icon' => 'heart',
                'description' => 'Ảnh kỷ niệm, câu trích dẫn lãng mạn và thông điệp tình yêu',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.save_date_photo_url',
                        'label' => 'Ảnh cưới kỷ niệm (Khung vòm nghệ thuật)',
                        'type' => 'image',
                        'required' => false,
                        'default' => '40f8fb60afcb59af.webp',
                        'placeholder' => 'Đường dẫn ảnh kỷ niệm',
                    ],
                    [
                        'key' => 'wedding.extra_data.story_title',
                        'label' => 'Tiêu đề mục Lời ngỏ',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Chuyện Tình Yêu',
                        'placeholder' => 'Ví dụ: Chuyện Tình Yêu hoặc Love Story',
                        'max' => 50,
                    ],
                    [
                        'key' => 'wedding.extra_data.story_subtitle',
                        'label' => 'Tiêu đề phụ câu chuyện',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Hành Trình Gặp Gỡ Giữa Ngân Hà',
                        'placeholder' => 'Ví dụ: Hành Trình Gặp Gỡ Giữa Ngân Hà',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.couple_quote',
                        'label' => 'Lời ngỏ / Thông điệp tình yêu',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Giữa muôn ngàn vì sao sáng trên bầu trời, em là ngôi sao duy nhất chiếu rọi trái tim anh.',
                        'placeholder' => 'Chia sẻ ngắn gọn lời ngỏ gửi đến quan khách...',
                        'max' => 500,
                    ],
                    [
                        'key' => 'wedding.extra_data.story_content',
                        'label' => 'Đoạn tâm sự tình yêu',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Vũ trụ bao la với hàng triệu vì tinh tú, nhưng định mệnh đã dẫn lối để hai tâm hồn đồng điệu tìm thấy nhau. Cảm ơn vì đã cùng nhau đi qua những năm tháng thanh xuân, và hôm nay, chúng ta cùng viết tiếp chương mới rạng rỡ nhất.',
                        'placeholder' => 'Kể ngắn gọn câu chuyện hoặc cảm xúc của hai bạn...',
                        'max' => 1000,
                    ],
                ],
            ],
            'parents' => [
                'id' => 'parents',
                'title' => 'Song Thân & Đôi Uyên Ương',
                'short_title' => 'Gia Đình & Uyên Ương',
                'icon' => 'home',
                'description' => 'Thông tin phụ mẫu hai bên, chân dung và lời tự bạch của cô dâu chú rể',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.groom_father',
                        'label' => 'Họ tên Cha (Nhà Trai)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Ông Nguyễn Văn Hùng',
                        'placeholder' => 'Ví dụ: Ông Nguyễn Văn Hùng',
                        'max' => 30,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_mother',
                        'label' => 'Họ tên Mẹ (Nhà Trai)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Bà Lê Thị Thu',
                        'placeholder' => 'Ví dụ: Bà Lê Thị Thu',
                        'max' => 30,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_parents_address',
                        'label' => 'Địa chỉ tư gia Nhà Trai',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Tư gia Nhà Trai - Đống Đa, Hà Nội',
                        'placeholder' => 'Địa chỉ chi tiết tư gia Nhà Trai',
                        'max' => 255,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_father',
                        'label' => 'Họ tên Cha (Nhà Gái)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Ông Trần Quốc Bảo',
                        'placeholder' => 'Ví dụ: Ông Trần Quốc Bảo',
                        'max' => 30,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_mother',
                        'label' => 'Họ tên Mẹ (Nhà Gái)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Bà Phạm Thị Mai',
                        'placeholder' => 'Ví dụ: Bà Phạm Thị Mai',
                        'max' => 30,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_parents_address',
                        'label' => 'Địa chỉ tư gia Nhà Gái',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Tư gia Nhà Gái - Ba Đình, Hà Nội',
                        'placeholder' => 'Địa chỉ chi tiết tư gia Nhà Gái',
                        'max' => 255,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_photo_url',
                        'label' => 'Ảnh chân dung Chú rể (Tỉ lệ chuẩn 3:4)',
                        'type' => 'image',
                        'required' => false,
                        'default' => 'b058b38c28460637.webp',
                        'placeholder' => 'Đường dẫn ảnh Chú rể',
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_bio',
                        'label' => 'Giới thiệu / Lời tự bạch Chú rể',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Một chàng trai điềm đạm, chân thành và luôn sống trọn vẹn vì tình yêu thương gia đình.',
                        'placeholder' => 'Vài dòng chia sẻ ngắn về chú rể...',
                        'max' => 255,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_photo_url',
                        'label' => 'Ảnh chân dung Cô dâu (Tỉ lệ chuẩn 3:4)',
                        'type' => 'image',
                        'required' => false,
                        'default' => '307b8d7ea14424b9.webp',
                        'placeholder' => 'Đường dẫn ảnh Cô dâu',
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_bio',
                        'label' => 'Giới thiệu / Lời tự bạch Cô dâu',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Một người con gái dịu dàng, tinh tế, luôn mang đến nụ cười ấm áp và bến đỗ bình yên.',
                        'placeholder' => 'Vài dòng chia sẻ ngắn về cô dâu...',
                        'max' => 255,
                    ],
                ],
            ],
            'countdown_calendar' => [
                'id' => 'countdown_calendar',
                'title' => 'Đếm Ngược & Lịch Tháng',
                'short_title' => 'Đếm Ngược',
                'icon' => 'calendar',
                'description' => 'Đồng hồ đếm ngược thời gian thực và lịch tháng lưu ngày trọng đại',
                'fields' => [
                    [
                        'key' => 'wedding.wedding_date',
                        'label' => 'Ngày đếm ngược đến hôn lễ',
                        'type' => 'date',
                        'required' => true,
                        'default' => '2026-12-20',
                    ],
                    [
                        'key' => 'wedding.extra_data.event_target_time',
                        'label' => 'Giờ mục tiêu đếm ngược',
                        'type' => 'time',
                        'required' => false,
                        'default' => '18:00',
                    ],
                ],
            ],
            'events' => [
                'id' => 'events',
                'title' => 'Thông Tin Sự Kiện & Bản Đồ',
                'short_title' => 'Sự Kiện & Bản Đồ',
                'icon' => 'map-pin',
                'description' => 'Chi tiết tiệc cưới chính, nghi lễ gia tiên và bản đồ Google Maps',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.party_time',
                        'label' => 'Giờ tổ chức tiệc cưới chính',
                        'type' => 'time',
                        'required' => false,
                        'default' => '18:00',
                        'placeholder' => 'Ví dụ: 18:00',
                    ],
                    [
                        'key' => 'wedding.extra_data.party_venue',
                        'label' => 'Tên nơi tổ chức / Trung tâm tiệc cưới',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Trung Tâm Hội Nghị Tiệc Cưới Ngân Hà',
                        'placeholder' => 'Ví dụ: Trung Tâm Hội Nghị Tiệc Cưới Ngân Hà',
                        'max' => 150,
                    ],
                    [
                        'key' => 'wedding.extra_data.event_party_address',
                        'label' => 'Địa chỉ nơi tổ chức tiệc cưới',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Tầng 3, Sảnh Star Palace - 88 Phố Vọng, Hai Bà Trưng, Hà Nội',
                        'placeholder' => 'Địa chỉ chi tiết sảnh tiệc hoặc nhà hàng tiệc cưới...',
                        'max' => 300,
                    ],
                    [
                        'key' => 'wedding.extra_data.ceremony_time',
                        'label' => 'Giờ tổ chức Lễ Vu Quy (Nhà Gái)',
                        'type' => 'time',
                        'required' => false,
                        'default' => '08:00',
                    ],
                    [
                        'key' => 'wedding.extra_data.ceremony_address',
                        'label' => 'Địa chỉ Lễ Vu Quy (Nhà Gái)',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Tư gia Nhà Gái - Ba Đình, Hà Nội',
                        'max' => 255,
                    ],
                    [
                        'key' => 'wedding.extra_data.ceremony_groom_time',
                        'label' => 'Giờ tổ chức Lễ Thành Hôn (Nhà Trai)',
                        'type' => 'time',
                        'required' => false,
                        'default' => '10:00',
                    ],
                    [
                        'key' => 'wedding.extra_data.ceremony_groom_address',
                        'label' => 'Địa chỉ Lễ Thành Hôn (Nhà Trai)',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Tư gia Nhà Trai - Đống Đa, Hà Nội',
                        'max' => 255,
                    ],
                    [
                        'key' => 'wedding.extra_data.event_party_map_link',
                        'label' => 'Đường dẫn bản đồ Google Maps',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'https://maps.google.com',
                        'placeholder' => 'https://maps.google.com/...',
                    ],
                ],
            ],
            'timeline' => [
                'id' => 'timeline',
                'title' => 'Lịch Trình Hôn Lễ',
                'short_title' => 'Lịch Trình',
                'icon' => 'clock',
                'description' => 'Các mốc thời gian diễn ra hôn lễ và tiệc mừng',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.timeline',
                        'label' => 'Lịch trình các mốc thời gian',
                        'type' => 'repeater',
                        'required' => false,
                        'max_items' => 6,
                        'default' => [
                            ['time' => '17:30', 'title' => 'Đón khách'],
                            ['time' => '18:00', 'title' => 'Cử hành hôn lễ'],
                            ['time' => '18:30', 'title' => 'Khai tiệc'],
                            ['time' => '20:00', 'title' => 'Giao lưu & chụp ảnh'],
                        ],
                        'item_structure' => [
                            ['key' => 'time', 'label' => 'Giờ', 'type' => 'time'],
                            ['key' => 'title', 'label' => 'Nội dung', 'type' => 'text'],
                        ],
                    ],
                ],
            ],
            'dresscode' => [
                'id' => 'dresscode',
                'title' => 'Quy Định Trang Phục',
                'short_title' => 'Trang Phục',
                'icon' => 'palette',
                'description' => 'Gợi ý bảng màu trang phục dự tiệc cưới cho khách quý',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.dresscode_colors',
                        'label' => 'Bảng màu trang phục gợi ý (Mã HEX cách nhau bởi dấu phẩy)',
                        'type' => 'text',
                        'required' => false,
                        'default' => '#FCE39E,#FFFFFF,#1B2A4A,#0D1322',
                        'placeholder' => '#FCE39E,#FFFFFF,#1B2A4A,#0D1322',
                    ],
                    [
                        'key' => 'wedding.extra_data.dresscode_note',
                        'label' => 'Ghi chú trang phục',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Khuyến khích trang phục thanh lịch mang tông màu ấm áp để những bức ảnh kỷ niệm thêm phần lung linh.',
                        'placeholder' => 'Ví dụ: Khuyến khích trang phục màu vàng gold, trắng hoặc đen...',
                        'max' => 255,
                    ],
                ],
            ],
            'album' => [
                'id' => 'album',
                'title' => 'Khoảnh Khắc Hạnh Phúc (10 Ảnh Cưới)',
                'short_title' => 'Ảnh Cưới',
                'icon' => 'image',
                'description' => 'Bộ sưu tập đúng 10 ảnh cưới ngọt ngào và đáng nhớ',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.gallery',
                        'label' => 'Album 10 Ảnh Cưới',
                        'type' => 'gallery',
                        'required' => false,
                        'max_items' => 10,
                    ],
                ],
            ],
            'gift' => [
                'id' => 'gift',
                'title' => 'Hộp Mừng Cưới & VietQR',
                'short_title' => 'Mừng Cưới',
                'icon' => 'gift',
                'description' => 'Mã QR mừng cưới ngân hàng tiện lợi và an toàn',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.qr_groom_image',
                        'label' => 'Mã QR Mừng Cưới Chú Rể',
                        'type' => 'image',
                        'required' => false,
                    ],
                    [
                        'key' => 'wedding.extra_data.qr_bride_image',
                        'label' => 'Mã QR Mừng Cưới Cô Dâu',
                        'type' => 'image',
                        'required' => false,
                    ],
                ],
            ],
            'ending' => [
                'id' => 'ending',
                'title' => 'Tương Tác, Lời Chúc & Cảm Ơn',
                'short_title' => 'Cảm Ơn',
                'icon' => 'check-circle',
                'description' => 'Xác nhận tham dự (RSVP), sổ lưu bút và lời cảm tạ chân thành',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.thank_you_title',
                        'label' => 'Tiêu đề lời tri ân kết thiệp',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Thank You',
                        'placeholder' => 'Thank You',
                    ],
                    [
                        'key' => 'wedding.extra_data.thank_you_quote',
                        'label' => 'Thông điệp cảm ơn gửi quan khách',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Sự hiện diện của quý khách là niềm vinh hạnh lớn nhất của chúng tôi!',
                        'max' => 500,
                    ],
                ],
            ],
            // Khối legacy hỗ trợ tương thích ngược
            'couple' => [
                'id' => 'couple',
                'title' => 'Đôi Uyên Ương',
                'short_title' => 'Đôi Uyên Ương',
                'icon' => 'heart',
                'description' => 'Thông tin cô dâu, chú rể, ngày cưới và ảnh đại diện',
                'fields' => [
                    [
                        'key' => 'wedding.groom_name',
                        'label' => 'Họ và tên Chú rể',
                        'type' => 'text',
                        'required' => true,
                        'default' => 'Minh Tuấn',
                        'placeholder' => 'Ví dụ: Minh Tuấn',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.bride_name',
                        'label' => 'Họ và tên Cô dâu',
                        'type' => 'text',
                        'required' => true,
                        'default' => 'Thu Hà',
                        'placeholder' => 'Ví dụ: Thu Hà',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_photo_url',
                        'label' => 'Ảnh chân dung Chú rể (Tỉ lệ chuẩn 3:4)',
                        'type' => 'image',
                        'required' => false,
                        'default' => 'assets/images/groom.webp',
                        'placeholder' => 'Đường dẫn ảnh Chú rể',
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_photo_url',
                        'label' => 'Ảnh chân dung Cô dâu (Tỉ lệ chuẩn 3:4)',
                        'type' => 'image',
                        'required' => false,
                        'default' => 'assets/images/bride.webp',
                        'placeholder' => 'Đường dẫn ảnh Cô dâu',
                    ],
                    [
                        'key' => 'wedding.wedding_date',
                        'label' => 'Ngày cử hành hôn lễ',
                        'type' => 'date',
                        'required' => true,
                        'default' => '2026-12-20',
                        'placeholder' => 'YYYY-MM-DD',
                    ],
                    [
                        'key' => 'wedding.extra_data.wedding_lunar_date',
                        'label' => 'Ngày âm lịch cử hành hôn lễ',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Nhằm ngày 12 tháng 11 năm Bính Ngọ',
                        'placeholder' => 'Ví dụ: Nhằm ngày 12 tháng 11 năm Bính Ngọ',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.cover_photo_url',
                        'label' => 'Ảnh bìa đại diện thiệp',
                        'type' => 'image',
                        'required' => false,
                        'default' => 'assets/images/cover.webp',
                        'placeholder' => 'Đường dẫn ảnh bìa',
                    ],
                    [
                        'key' => 'wedding.extra_data.party_time',
                        'label' => 'Giờ tổ chức tiệc cưới chính',
                        'type' => 'time',
                        'required' => false,
                        'default' => '18:00',
                        'placeholder' => 'Ví dụ: 18:00',
                        'max' => 50,
                    ],
                    [
                        'key' => 'wedding.extra_data.party_venue',
                        'label' => 'Tên nơi tổ chức / Trung tâm tiệc cưới',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Trung tâm Tiệc cưới Ngân Hà',
                        'placeholder' => 'Ví dụ: Trung tâm Tiệc cưới Ngân Hà',
                        'max' => 150,
                    ],
                    [
                        'key' => 'wedding.extra_data.event_party_address',
                        'label' => 'Địa chỉ nơi tổ chức tiệc cưới',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Số 88 Trần Duy Hưng, Cầu Giấy, Hà Nội',
                        'placeholder' => 'Địa chỉ chi tiết sảnh tiệc hoặc nhà hàng tiệc cưới...',
                        'max' => 300,
                    ],
                    [
                        'key' => 'wedding.extra_data.couple_quote',
                        'label' => 'Lời ngỏ / Thông điệp tình yêu',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Tình yêu không phải là nhìn nhau, mà là cùng nhau nhìn về một hướng.',
                        'placeholder' => 'Chia sẻ ngắn gọn lời ngỏ gửi đến quan khách...',
                        'max' => 500,
                    ],
                ],
            ],
            'groom_family' => [
                'id' => 'groom_family',
                'title' => 'Nhà Trai',
                'short_title' => 'Nhà Trai',
                'icon' => 'home',
                'description' => 'Thông tin phụ mẫu, ngày giờ và địa chỉ hôn lễ nhà trai',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.groom_father',
                        'label' => 'Họ tên Cha (Nhà Trai)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Ông Nguyễn Thăng Long',
                        'placeholder' => 'Ví dụ: Ông Nguyễn Thăng Long',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_mother',
                        'label' => 'Họ tên Mẹ (Nhà Trai)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Bà Nguyễn Thị Mai',
                        'placeholder' => 'Ví dụ: Bà Nguyễn Thị Mai',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_party_time',
                        'label' => 'Giờ tổ chức hôn lễ / tiệc Nhà Trai',
                        'type' => 'time',
                        'required' => false,
                        'default' => '11:00',
                        'placeholder' => 'Ví dụ: 11:00',
                        'max' => 50,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_party_date',
                        'label' => 'Ngày tổ chức hôn lễ Nhà Trai',
                        'type' => 'date',
                        'required' => false,
                        'default' => '2027-03-07',
                        'placeholder' => 'YYYY-MM-DD',
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_party_lunar',
                        'label' => 'Ngày âm lịch Nhà Trai',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Ngày 29 tháng 01 năm Đinh Mùi',
                        'placeholder' => 'Ví dụ: Ngày 29 tháng 01 năm Đinh Mùi',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_party_address',
                        'label' => 'Địa chỉ hôn lễ / Tiệc cưới Nhà Trai',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Tư gia Nhà Trai - Xã Tân Dân, Huyện Phú Xuyên, Hà Nội',
                        'placeholder' => 'Địa chỉ chi tiết sảnh tiệc hoặc tư gia Nhà Trai',
                        'max' => 300,
                    ],
                ],
            ],
            'bride_family' => [
                'id' => 'bride_family',
                'title' => 'Nhà Gái',
                'short_title' => 'Nhà Gái',
                'icon' => 'home',
                'description' => 'Thông tin phụ mẫu, ngày giờ và địa chỉ hôn lễ nhà gái',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.bride_father',
                        'label' => 'Họ tên Cha (Nhà Gái)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Ông Trần Đình Thắng',
                        'placeholder' => 'Ví dụ: Ông Trần Đình Thắng',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_mother',
                        'label' => 'Họ tên Mẹ (Nhà Gái)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Bà Lê Thị Hồng',
                        'placeholder' => 'Ví dụ: Bà Lê Thị Hồng',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_party_time',
                        'label' => 'Giờ tổ chức hôn lễ / tiệc Nhà Gái',
                        'type' => 'time',
                        'required' => false,
                        'default' => '09:00',
                        'placeholder' => 'Ví dụ: 09:00',
                        'max' => 50,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_party_date',
                        'label' => 'Ngày tổ chức hôn lễ Nhà Gái',
                        'type' => 'date',
                        'required' => false,
                        'default' => '2027-03-07',
                        'placeholder' => 'YYYY-MM-DD',
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_party_lunar',
                        'label' => 'Ngày âm lịch Nhà Gái',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Ngày 29 tháng 01 năm Đinh Mùi',
                        'placeholder' => 'Ví dụ: Ngày 29 tháng 01 năm Đinh Mùi',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_party_address',
                        'label' => 'Địa chỉ hôn lễ / Tiệc cưới Nhà Gái',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Tư gia Nhà Gái - Phường Cống Vị, Ba Đình, Hà Nội',
                        'placeholder' => 'Địa chỉ chi tiết sảnh tiệc hoặc tư gia Nhà Gái',
                        'max' => 300,
                    ],
                ],
            ],
        ];
    }

    /**
     * Chuẩn hóa Schema đảm bảo tuân thủ đầy đủ 10 Section tiêu chuẩn.
     */
    public function normalizeSchema(array $rawSchema, ?string $slug = null): array
    {
        $canonical = self::getCanonicalSections();
        $supportedKeys = $rawSchema['supported_sections'] ?? null;

        // Nếu chưa có supported_sections, lấy từ các section ids hiện có trong file JSON
        if (! is_array($supportedKeys) || empty($supportedKeys)) {
            $supportedKeys = array_column($rawSchema['sections'] ?? [], 'id');
        }

        // Hỗ trợ cả 10 canonical sections và các key legacy tương thích ngược
        $allowedKeys = array_merge(self::CANONICAL_SECTION_ORDER, self::LEGACY_SECTION_KEYS);
        $validSupported = array_values(array_intersect((array) $supportedKeys, $allowedKeys));
        if (empty($validSupported)) {
            $validSupported = self::CANONICAL_SECTION_ORDER;
        }

        // Tạo mảng lookup các field overrides từ raw schema nếu có
        $rawSectionsById = [];
        foreach ($rawSchema['sections'] ?? [] as $sec) {
            if (isset($sec['id'])) {
                $rawSectionsById[$sec['id']] = $sec;
            }
        }

        // Danh sách các trường text ngân hàng thủ công cũ cần loại bỏ triệt để (thay bằng khối QR)
        $legacyBankFields = [
            'wedding.extra_data.groom_bank_name',
            'wedding.extra_data.groom_bank_account',
            'wedding.extra_data.groom_bank_holder',
            'wedding.extra_data.bride_bank_name',
            'wedding.extra_data.bride_bank_account',
            'wedding.extra_data.bride_bank_holder',
            'groom_bank_name',
            'groom_bank_account',
            'groom_bank_holder',
            'bride_bank_name',
            'bride_bank_account',
            'bride_bank_holder',
        ];

        // Dựng danh sách sections chuẩn theo đúng thứ tự logic (1 -> 6)
        $normalizedSections = [];
        foreach ($validSupported as $sectionId) {
            if (! isset($canonical[$sectionId])) {
                continue;
            }

            $secDef = $canonical[$sectionId];

            // Nếu trong file JSON có cung cấp title/description/fields riêng, hợp nhất một cách an toàn
            $rawSec = $rawSectionsById[$sectionId] ?? null;
            if ($rawSec) {
                if (! empty($rawSec['title'])) {
                    $secDef['title'] = $rawSec['title'];
                }
                if (! empty($rawSec['short_title'])) {
                    $secDef['short_title'] = $rawSec['short_title'];
                }
                if (! empty($rawSec['description'])) {
                    $secDef['description'] = $rawSec['description'];
                }

                // Cập nhật defaults cho các fields từ raw schema nếu trùng key
                $rawFieldsByKey = [];
                $hasGalleryType = false;
                foreach ($rawSec['fields'] ?? [] as $rf) {
                    $k = $rf['key'] ?? $rf['name'] ?? null;
                    if ($rf['type'] ?? '' === 'gallery' || $k === 'wedding.extra_data.gallery') {
                        $hasGalleryType = true;
                    }
                    if ($k && ! in_array($k, $legacyBankFields, true)) {
                        $rawFieldsByKey[$k] = $rf;
                    }
                }

                // Tương thích ngược: nếu raw schema dùng ending_title/ending_text thì map sang thank_you_title/thank_you_quote
                if (isset($rawFieldsByKey['wedding.extra_data.ending_title']) && ! isset($rawFieldsByKey['wedding.extra_data.thank_you_title'])) {
                    $rawFieldsByKey['wedding.extra_data.thank_you_title'] = $rawFieldsByKey['wedding.extra_data.ending_title'];
                }
                if (isset($rawFieldsByKey['wedding.extra_data.ending_text']) && ! isset($rawFieldsByKey['wedding.extra_data.thank_you_quote'])) {
                    $rawFieldsByKey['wedding.extra_data.thank_you_quote'] = $rawFieldsByKey['wedding.extra_data.ending_text'];
                }

                $processedKeys = [];
                $mergedFields = [];

                foreach ($secDef['fields'] as $fDef) {
                    $fKey = $fDef['key'];
                    $processedKeys[] = $fKey;
                    if (isset($rawFieldsByKey[$fKey])) {
                        $rf = $rawFieldsByKey[$fKey];
                        $fDef = array_merge($fDef, $rf);
                    }
                    $mergedFields[] = $fDef;
                }

                // Bảo tồn toàn bộ các fields riêng do mẫu thiệp định nghĩa mà canonical chưa có
                // (Loại trừ các trường trùng lặp: gallery.* nếu đã có gallery, ending_title/ending_text nếu đã có thank_you_*)
                foreach ($rawSec['fields'] ?? [] as $rf) {
                    $k = $rf['key'] ?? $rf['name'] ?? null;
                    if (! $k || in_array($k, $legacyBankFields, true)) {
                        continue;
                    }
                    // Bỏ qua các trường lẻ gallery.0 ... gallery.9 nếu đã dùng type gallery
                    if ($sectionId === 'album' && preg_match('/gallery\.\d+$/', $k)) {
                        continue;
                    }
                    // Bỏ qua ending_title / ending_text vì đã chuẩn hóa sang thank_you_*
                    if ($sectionId === 'ending' && in_array($k, ['wedding.extra_data.ending_title', 'wedding.extra_data.ending_text', 'ending_title', 'ending_text'], true)) {
                        continue;
                    }
                    if (! in_array($k, $processedKeys, true)) {
                        $mergedFields[] = $rf;
                        $processedKeys[] = $k;
                    }
                }

                $secDef['fields'] = $mergedFields;
            }

            $normalizedSections[] = $secDef;
        }

        return [
            'slug' => $rawSchema['slug'] ?? ($slug ?? 'default'),
            'version' => $rawSchema['version'] ?? 'v1',
            'name' => $rawSchema['name'] ?? 'Mẫu Thiệp Mây Hẹn',
            'tier' => $rawSchema['tier'] ?? 'free',
            'supported_sections' => $validSupported,
            'sections' => $normalizedSections,
        ];
    }

    /**
     * Đọc Schema cấu hình của mẫu thiệp (Schema-Driven Template Engine).
     */
    public function getSchema(string $slug, string $version = 'v1'): array
    {
        $cacheKey = "{$slug}:{$version}";
        if (isset(self::$schemaCache[$cacheKey])) {
            return self::$schemaCache[$cacheKey];
        }

        $schemaPath = resource_path("views/client/invitations/{$slug}/schema.json");

        // Tương thích ngược nếu còn ở đường dẫn cũ
        if (! File::exists($schemaPath)) {
            $schemaPath = resource_path("invitation-templates/{$slug}/schema.json");
        }

        if (File::exists($schemaPath)) {
            $json = File::get($schemaPath);
            $rawSchema = json_decode($json, true) ?? [];
            $schema = $this->normalizeSchema($rawSchema, $slug);
            self::$schemaCache[$cacheKey] = $schema;

            return $schema;
        }

        // Nếu chưa có file schema.json riêng, tự sinh schema dựa theo template trong DB
        $template = InvitationTemplate::where('slug', $slug)->first();
        $fallbackSchema = $this->generateFallbackSchema($template);
        self::$schemaCache[$cacheKey] = $fallbackSchema;

        return $fallbackSchema;
    }

    /**
     * Sinh Schema mặc định dựa trên cấu hình template trong database (Backward Compatible).
     */
    public function generateFallbackSchema(?InvitationTemplate $template): array
    {
        $canonical = self::getCanonicalSections();

        return [
            'slug' => $template?->slug ?? 'default',
            'version' => $template?->current_version ?? 'v1',
            'name' => $template?->name ?? 'Mẫu Thiệp Mây Hẹn',
            'tier' => $template?->default_config['tier'] ?? 'free',
            'supported_sections' => self::CANONICAL_SECTION_ORDER,
            'sections' => array_values($canonical),
        ];
    }

    /**
     * Tạo quy tắc validate động dựa trên schema của mẫu.
     */
    public function getValidationRules(InvitationTemplate $template): array
    {
        $schema = $this->getSchema($template->slug, $template->current_version ?? 'v1');
        $rules = [
            'template_id' => 'required|exists:invitation_templates,id',
            'wedding.groom_name' => 'required|string|max:100',
            'wedding.bride_name' => 'required|string|max:100',
            'wedding.wedding_date' => 'required|date',
            'wedding.venue_name' => 'nullable|string|max:255',
            'wedding.venue_address' => 'nullable|string|max:500',
            'wedding.groom_parents' => 'nullable|string|max:255',
            'wedding.bride_parents' => 'nullable|string|max:255',
            'wedding.story' => 'nullable|string|max:3000',
            'wedding.cover_photo_url' => 'nullable|string',
            'wedding.music_url' => 'nullable|url|max:500',
            'wedding.bank_name' => 'nullable|string|max:100',
            'wedding.bank_account_number' => 'nullable|string|max:50',
            'wedding.bank_account_name' => 'nullable|string|max:100',
            'wedding.extra_data' => 'nullable|array',
            'wedding.extra_data.gallery' => 'nullable|array|max:20',
            'wedding.extra_data.gallery.*' => 'nullable|string',
            'wedding.extra_data.timeline' => 'nullable|array|max:10',
            'invitation.custom_config' => 'nullable|array',
        ];

        return $rules;
    }

    /**
     * Lưu thông tin thiệp cưới an toàn trong Transaction.
     * Tách bạch dữ liệu chung vào bảng weddings và cấu hình riêng vào invitations.
     */
    public function saveInvitation(User $user, int $templateId, array $data, ?int $invitationId = null, ?int $expectedRevision = null): Invitation
    {
        return DB::transaction(function () use ($user, $templateId, $data, $invitationId, $expectedRevision) {
            $weddingData = $data['wedding'] ?? [];
            $extraData = $weddingData['extra_data'] ?? [];

            // Nếu người dùng đã có wedding trước đó thì cập nhật, nếu chưa thì tạo mới
            $existingInvitation = $invitationId ? Invitation::whereKey($invitationId)->lockForUpdate()->firstOrFail() : null;
            if ($existingInvitation && $expectedRevision !== (int) $existingInvitation->revision_id) {
                throw ValidationException::withMessages([
                    'revision_id' => 'Thiệp đã được thay đổi ở phiên khác. Hãy giữ bản nháp và kiểm tra trước khi tiếp tục.',
                ]);
            }
            $wedding = $existingInvitation?->wedding;

            if (! $wedding) {
                $wedding = Wedding::create([
                    'user_id' => $user->id,
                    'groom_name' => $weddingData['groom_name'] ?? '',
                    'bride_name' => $weddingData['bride_name'] ?? '',
                    'groom_parents' => $weddingData['groom_parents'] ?? null,
                    'bride_parents' => $weddingData['bride_parents'] ?? null,
                    'wedding_date' => $weddingData['wedding_date'] ?? now()->addMonths(2)->format('Y-m-d'),
                    'venue_name' => $weddingData['venue_name'] ?? 'Trung Tâm Tiệc Cưới',
                    'venue_address' => $weddingData['venue_address'] ?? 'TP. Hồ Chí Minh',
                    'story' => $weddingData['story'] ?? null,
                    'cover_photo_url' => $weddingData['cover_photo_url'] ?? null,
                    'music_url' => $weddingData['music_url'] ?? null,
                    'bank_name' => $weddingData['bank_name'] ?? null,
                    'bank_account_number' => $weddingData['bank_account_number'] ?? null,
                    'bank_account_name' => $weddingData['bank_account_name'] ?? null,
                    'extra_data' => $extraData,
                ]);
            } else {
                // Cập nhật wedding hiện có
                $wedding->update([
                    'groom_name' => $weddingData['groom_name'] ?? $wedding->groom_name,
                    'bride_name' => $weddingData['bride_name'] ?? $wedding->bride_name,
                    'groom_parents' => $weddingData['groom_parents'] ?? $wedding->groom_parents,
                    'bride_parents' => $weddingData['bride_parents'] ?? $wedding->bride_parents,
                    'wedding_date' => $weddingData['wedding_date'] ?? $wedding->wedding_date,
                    'venue_name' => $weddingData['venue_name'] ?? $wedding->venue_name,
                    'venue_address' => $weddingData['venue_address'] ?? $wedding->venue_address,
                    'story' => $weddingData['story'] ?? $wedding->story,
                    'cover_photo_url' => $weddingData['cover_photo_url'] ?? $wedding->cover_photo_url,
                    'music_url' => $weddingData['music_url'] ?? $wedding->music_url,
                    'bank_name' => $weddingData['bank_name'] ?? $wedding->bank_name,
                    'bank_account_number' => $weddingData['bank_account_number'] ?? $wedding->bank_account_number,
                    'bank_account_name' => $weddingData['bank_account_name'] ?? $wedding->bank_account_name,
                    'extra_data' => array_merge($wedding->extra_data ?? [], $extraData),
                ]);
            }

            $template = InvitationTemplate::findOrFail($templateId);
            $customConfig = $data['invitation']['custom_config'] ?? [];

            if ($existingInvitation) {
                $existingInvitation->update([
                    'template_id' => $template->id,
                    'template_version' => $template->current_version ?? 'v1',
                    'custom_config' => array_merge($existingInvitation->custom_config ?? [], $customConfig),
                    'revision_id' => (int) $existingInvitation->revision_id + 1,
                ]);

                return $existingInvitation;
            }

            // Tạo link thiệp mới với slug độc nhất có hậu tố kép chống trùng (ví dụ: dang-minh-quan-le-ai-nhu-edsra-94y9rwhfget)
            $slugBase = Str::slug($wedding->groom_name.'-'.$wedding->bride_name);
            if (empty($slugBase)) {
                $slugBase = 'thiep-cuoi';
            }
            $trimmedBase = Str::limit($slugBase, 70, '');

            do {
                $suffix = Str::lower(Str::random(5)).'-'.Str::lower(Str::random(11));
                $uniqueSlug = $trimmedBase.'-'.$suffix;
            } while (Invitation::where('slug', $uniqueSlug)->exists());

            $invitation = Invitation::create([
                'wedding_id' => $wedding->id,
                'template_id' => $template->id,
                'slug' => $uniqueSlug,
                'title' => 'Thiệp Cưới '.$wedding->groom_name.' & '.$wedding->bride_name,
                'version_type' => 'general',
                'template_version' => $template->current_version ?? 'v1',
                'custom_config' => $customConfig,
                'is_published' => true,
                'published_at' => now(),
                'expires_at' => now()->addDays(60),
            ]);

            return $invitation;
        });
    }

    /**
     * Lưu bản nháp định kỳ kèm kiểm tra khóa lạc quan (Optimistic Concurrency Control).
     *
     * @param  array<string, mixed>  $draftConfig
     * @param  array<string, mixed>  $weddingDelta
     * @return array{success: bool, conflict: bool, new_revision_id?: int, current_revision?: int, saved_at?: string, message?: string}
     */
    public function saveDraftWithLock(Invitation $invitation, int $clientRevision, array $draftConfig, array $weddingDelta = []): array
    {
        return DB::transaction(function () use ($invitation, $clientRevision, $draftConfig, $weddingDelta) {
            // Khóa dòng để đọc revision mới nhất chống race condition
            $currentInvitation = Invitation::where('id', $invitation->id)->lockForUpdate()->first();
            if (! $currentInvitation) {
                return [
                    'success' => false,
                    'conflict' => true,
                    'message' => 'Không tìm thấy thiệp cưới tương ứng.',
                ];
            }

            $currentRevision = (int) ($currentInvitation->revision_id ?? 0);

            // Kiểm tra xung đột phiên bản (Optimistic Concurrency Check)
            if ($clientRevision !== $currentRevision) {
                return [
                    'success' => false,
                    'conflict' => true,
                    'current_revision' => $currentRevision,
                    'message' => 'Phiên bản chỉnh sửa không khớp với máy chủ. Vui lòng kiểm tra bản nháp trước khi làm mới trang.',
                ];
            }

            // Khử blob URLs trong dữ liệu nếu có
            $sanitize = function (mixed $value) use (&$sanitize): mixed {
                if (is_array($value)) {
                    return array_map($sanitize, $value);
                }
                if (is_string($value) && str_starts_with(trim($value), 'blob:')) {
                    return null;
                }

                return $value;
            };

            $cleanDraft = $sanitize($draftConfig);
            $cleanWeddingDelta = $sanitize($weddingDelta);

            // Cập nhật wedding nếu có thay đổi delta
            $wedding = $currentInvitation->wedding;
            if ($wedding && ! empty($cleanWeddingDelta)) {
                $allowedFields = [
                    'groom_name', 'bride_name', 'groom_parents', 'bride_parents',
                    'wedding_date', 'venue_name', 'venue_address', 'story',
                    'cover_photo_url', 'music_url', 'bank_name',
                    'bank_account_number', 'bank_account_name',
                ];

                $fieldsToUpdate = [];
                foreach ($allowedFields as $f) {
                    if (array_key_exists($f, $cleanWeddingDelta)) {
                        $fieldsToUpdate[$f] = $cleanWeddingDelta[$f];
                    }
                }

                if (isset($cleanWeddingDelta['extra_data']) && is_array($cleanWeddingDelta['extra_data'])) {
                    $fieldsToUpdate['extra_data'] = array_merge($wedding->extra_data ?? [], $cleanWeddingDelta['extra_data']);
                }

                if (! empty($fieldsToUpdate)) {
                    $wedding->update($fieldsToUpdate);
                }
            }

            $newRevision = $currentRevision + 1;
            $mergedDraft = array_merge($currentInvitation->draft_config ?? [], $cleanDraft);

            $currentInvitation->update([
                'draft_config' => $mergedDraft,
                'revision_id' => $newRevision,
                'last_autosaved_at' => now(),
            ]);

            return [
                'success' => true,
                'conflict' => false,
                'new_revision_id' => $newRevision,
                'saved_at' => now()->toIso8601String(),
            ];
        });
    }
}
