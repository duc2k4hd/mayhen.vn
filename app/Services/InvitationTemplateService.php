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
        'countdown',
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

        $legacyKeyMap = [
            'countdown' => 'countdown_calendar',
            'couple' => 'hero',
            'groom_family' => 'parents',
            'bride_family' => 'parents',
        ];

        // Dựng danh sách sections chuẩn theo đúng thứ tự logic (1 -> 6)
        $normalizedSections = [];
        foreach ($validSupported as $sectionId) {
            $canonicalKey = $legacyKeyMap[$sectionId] ?? $sectionId;
            if (! isset($canonical[$canonicalKey])) {
                continue;
            }

            $secDef = $canonical[$canonicalKey];
            $secDef['id'] = $sectionId;

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
                    // Bỏ qua các trường lẻ timeline.0 ... timeline.N nếu đã dùng canonical repeater
                    if ($sectionId === 'timeline' && preg_match('/timeline\.\d+/', $k)) {
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
            $weddingData = $this->sanitizePendingAssets($data['wedding'] ?? []);
            $extraData = $weddingData['extra_data'] ?? [];

            // Nếu người dùng đã có wedding trước đó thì cập nhật, nếu chưa thì tạo mới
            $weddingId = $invitationId ? Invitation::whereKey($invitationId)->value('wedding_id') : null;
            $wedding = $weddingId ? Wedding::whereKey($weddingId)->lockForUpdate()->firstOrFail() : null;
            $existingInvitation = $invitationId ? Invitation::whereKey($invitationId)->lockForUpdate()->firstOrFail() : null;
            $existingConfig = [];
            if ($existingInvitation) {
                $existingConfig = is_array($existingInvitation->custom_config)
                    ? $existingInvitation->custom_config
                    : (json_decode((string) $existingInvitation->custom_config, true) ?? []);

                if (! empty($existingConfig['is_locked'])) {
                    throw ValidationException::withMessages([
                        'is_locked' => 'Thiệp cưới tạm thời bị khóa bởi Quản trị viên, không thể lưu hoặc xuất bản thay đổi.',
                    ]);
                }
            }
            if ($existingInvitation && $existingInvitation->wedding_id !== $wedding?->id) {
                throw ValidationException::withMessages(['revision_id' => 'Dữ liệu thiệp đã thay đổi. Vui lòng tải lại trước khi tiếp tục.']);
            }
            if ($existingInvitation && $expectedRevision !== (int) $existingInvitation->revision_id) {
                throw ValidationException::withMessages([
                    'revision_id' => 'Thiệp đã được thay đổi ở phiên khác. Hãy giữ bản nháp và kiểm tra trước khi tiếp tục.',
                ]);
            }

            $previousSnapshot = null;
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
                // Lưu bản snapshot của bản xuất bản trước khi ghi đè (Emergency Rollback Safeguard - TASK-VAL-11)
                if ($existingInvitation) {
                    $previousSnapshot = [
                        'saved_at' => now()->toIso8601String(),
                        'template_id' => $existingInvitation->template_id,
                        'wedding' => [
                            'groom_name' => $wedding->groom_name,
                            'bride_name' => $wedding->bride_name,
                            'groom_parents' => $wedding->groom_parents,
                            'bride_parents' => $wedding->bride_parents,
                            'wedding_date' => $wedding->wedding_date ? $wedding->wedding_date->format('Y-m-d') : null,
                            'venue_name' => $wedding->venue_name,
                            'venue_address' => $wedding->venue_address,
                            'story' => $wedding->story,
                            'cover_photo_url' => $wedding->cover_photo_url,
                            'music_url' => $wedding->music_url,
                            'bank_name' => $wedding->bank_name,
                            'bank_account_number' => $wedding->bank_account_number,
                            'bank_account_name' => $wedding->bank_account_name,
                            'extra_data' => is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []),
                        ],
                        'custom_config' => is_array($existingInvitation->custom_config) ? $existingInvitation->custom_config : (json_decode((string) $existingInvitation->custom_config, true) ?? []),
                    ];
                }

                // Cập nhật wedding hiện có
                $fieldsToUpdate = array_intersect_key($weddingData, array_flip([
                    'groom_name', 'bride_name', 'groom_parents', 'bride_parents',
                    'wedding_date', 'venue_name', 'venue_address', 'story',
                    'cover_photo_url', 'music_url', 'bank_name',
                    'bank_account_number', 'bank_account_name',
                ]));
                $fieldsToUpdate['extra_data'] = $this->mergeWeddingExtraData($wedding->extra_data ?? [], $extraData);
                $wedding->update($fieldsToUpdate);
            }

            $template = InvitationTemplate::findOrFail($templateId);
            $customConfig = $this->sanitizePendingAssets($data['invitation']['custom_config'] ?? []);

            if ($existingInvitation) {
                $mergedConfig = array_merge($existingInvitation->custom_config ?? [], $existingInvitation->draft_config ?? [], $customConfig);
                if (! empty($existingConfig['is_locked'])) {
                    $mergedConfig['is_locked'] = true;
                    $mergedConfig['lock_reason'] = $existingConfig['lock_reason'] ?? null;
                    $mergedConfig['locked_at'] = $existingConfig['locked_at'] ?? null;
                }
                if ($previousSnapshot) {
                    $mergedConfig['_previous_published_snapshot'] = $previousSnapshot;
                }

                $existingInvitation->update([
                    'template_id' => $template->id,
                    'template_version' => $template->current_version ?? 'v1',
                    'custom_config' => $mergedConfig,
                    'draft_config' => null,
                    'revision_id' => (int) $existingInvitation->revision_id + 1,
                ]);
                if ($wedding->wasChanged()) {
                    Invitation::where('wedding_id', $wedding->id)->whereKeyNot($existingInvitation->id)->increment('revision_id');
                }

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
            $weddingId = Invitation::whereKey($invitation->id)->value('wedding_id');
            $wedding = $weddingId ? Wedding::whereKey($weddingId)->lockForUpdate()->first() : null;
            // Always lock the shared wedding before its invitations.
            $currentInvitation = Invitation::where('id', $invitation->id)->lockForUpdate()->first();
            if (! $currentInvitation) {
                return [
                    'success' => false,
                    'conflict' => true,
                    'message' => 'Không tìm thấy thiệp cưới tương ứng.',
                ];
            }

            $currentConfig = is_array($currentInvitation->custom_config)
                ? $currentInvitation->custom_config
                : (json_decode((string) $currentInvitation->custom_config, true) ?? []);

            if (! empty($currentConfig['is_locked'])) {
                return [
                    'success' => false,
                    'conflict' => true,
                    'locked' => true,
                    'message' => 'Thiệp cưới đang bị khóa bởi Quản trị viên, không thể tự động lưu nháp.',
                ];
            }

            $currentRevision = (int) ($currentInvitation->revision_id ?? 0);
            if ($currentInvitation->wedding_id !== $wedding?->id) {
                return ['success' => false, 'conflict' => true, 'current_revision' => $currentRevision];
            }

            // Kiểm tra xung đột phiên bản (Optimistic Concurrency Check)
            if ($clientRevision !== $currentRevision) {
                return [
                    'success' => false,
                    'conflict' => true,
                    'current_revision' => $currentRevision,
                    'message' => 'Phiên bản chỉnh sửa không khớp với máy chủ. Vui lòng kiểm tra bản nháp trước khi làm mới trang.',
                ];
            }

            $cleanDraft = $this->sanitizePendingAssets($draftConfig);
            $cleanWeddingDelta = $this->sanitizePendingAssets($weddingDelta);

            // Cập nhật wedding nếu có thay đổi delta
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
                    $fieldsToUpdate['extra_data'] = $this->mergeWeddingExtraData($wedding->extra_data ?? [], $cleanWeddingDelta['extra_data']);
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
            if ($wedding?->wasChanged()) {
                Invitation::where('wedding_id', $wedding->id)->whereKeyNot($currentInvitation->id)->increment('revision_id');
            }

            return [
                'success' => true,
                'conflict' => false,
                'new_revision_id' => $newRevision,
                'saved_at' => now()->toIso8601String(),
            ];
        });
    }

    /** @param array<string|int, mixed> $values */
    private function sanitizePendingAssets(array $values): array
    {
        $sanitized = [];
        foreach ($values as $key => $value) {
            if (is_string($value) && str_starts_with(trim($value), 'blob:')) {
                continue;
            }
            $cleanValue = is_array($value) ? $this->sanitizePendingAssets($value) : $value;
            if (is_array($value) && $value !== [] && $cleanValue === []) {
                continue;
            }
            $sanitized[$key] = $cleanValue;
        }

        return $sanitized;
    }

    /**
     * @param  array<string, mixed>  $existing
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private function mergeWeddingExtraData(array $existing, array $changes): array
    {
        foreach (['gallery', 'timeline'] as $listKey) {
            if (isset($changes[$listKey]) && is_array($changes[$listKey])
                && $changes[$listKey] !== [] && is_array($existing[$listKey] ?? null)) {
                $changes[$listKey] = array_replace_recursive($existing[$listKey], $changes[$listKey]);
            }
        }

        return array_merge($existing, $changes);
    }

    /**
     * Comprehensive QA Audit of an Invitation Template (Wave 04 - Atelier Governance).
     * Inspects 10 Canonical Sections, Contract Live Sync data-sync-target, Local Assets Defense, CSS BEM Prefix, and AI Curated Parity.
     *
     * @return array<string, mixed>
     */
    public static function inspectTemplateIntegrity(string $slug): array
    {
        $templateDir = resource_path("views/client/invitations/{$slug}");
        $schemaPath = "{$templateDir}/schema.json";
        $bladePath = "{$templateDir}/template.blade.php";
        $cssPath = "{$templateDir}/styles.css";
        $jsPath = "{$templateDir}/script.js";
        $assetsDir = "{$templateDir}/assets";

        $schemaExists = File::exists($schemaPath);
        $bladeExists = File::exists($bladePath);
        $cssExists = File::exists($cssPath);
        $jsExists = File::exists($jsPath);
        $assetsExists = File::isDirectory($assetsDir);

        // 1. Files & Structure Check
        $filesReport = [
            'schema_json' => ['exists' => $schemaExists, 'path' => "resources/views/client/invitations/{$slug}/schema.json"],
            'template_blade' => ['exists' => $bladeExists, 'path' => "resources/views/client/invitations/{$slug}/template.blade.php"],
            'styles_css' => ['exists' => $cssExists, 'path' => "resources/views/client/invitations/{$slug}/styles.css"],
            'script_js' => ['exists' => $jsExists, 'path' => "resources/views/client/invitations/{$slug}/script.js"],
            'assets_dir' => ['exists' => $assetsExists, 'path' => "resources/views/client/invitations/{$slug}/assets"],
        ];

        // 2. Canonical Sections (10/10)
        $rawSchema = [];
        $sectionsReport = [];
        $galleryValid = false;

        if ($schemaExists) {
            $rawSchema = json_decode(File::get($schemaPath), true) ?? [];
            $presentSectionIds = collect($rawSchema['sections'] ?? [])->pluck('id')->filter()->all();

            $canonicalSections = self::CANONICAL_SECTION_ORDER;
            $canonicalDefinitions = self::getCanonicalSections();

            foreach ($canonicalSections as $canonId) {
                $isPassed = in_array($canonId, $presentSectionIds, true);
                $title = $canonicalDefinitions[$canonId]['title'] ?? ucfirst(str_replace('_', ' ', $canonId));

                $sectionsReport[] = [
                    'id' => $canonId,
                    'title' => $title,
                    'passed' => $isPassed,
                ];
            }

            foreach ($rawSchema['sections'] ?? [] as $sec) {
                foreach ($sec['fields'] ?? [] as $f) {
                    if (($f['key'] ?? '') === 'wedding.extra_data.gallery') {
                        $galleryValid = true;
                        break 2;
                    }
                }
            }
        }

        $canonicalCount = collect($sectionsReport)->where('passed', true)->count();
        $isCanonicalValid = ($canonicalCount >= 10);

        // 3. Contract Live Sync Check (data-sync-target)
        $bladeContent = $bladeExists ? File::get($bladePath) : '';
        $cssContent = $cssExists ? File::get($cssPath) : '';

        preg_match_all('/data-sync-target=["\']([^"\']+)["\']/', $bladeContent, $syncMatches);
        $syncTargets = array_values(array_unique($syncMatches[1] ?? []));
        $syncCount = count($syncTargets);
        $hasEssentialSync = in_array('groom_name', $syncTargets, true) && in_array('bride_name', $syncTargets, true);

        // 4. Layout Inheritance & Embed Defense
        $extendsInvitation = str_contains($bladeContent, "@extends('client.layouts.invitation')")
            || str_contains($bladeContent, '@extends("client.layouts.invitation")');
        $hasEmbedGuard = str_contains($bladeContent, '$isEmbed')
            || str_contains($cssContent, '.card-wrapper')
            || $extendsInvitation;

        // 5. CSS Prefixing Check (mayhen_invitation_*)
        $usesBEMPrefix = str_contains($cssContent, 'mayhen_invitation_')
            || str_contains($bladeContent, 'mayhen_invitation_');

        // 6. 100% Local Assets Defense (Detection of external domains)
        $externalDomains = ['ladicdn.com', 'builder.27biggroup.com', 'unsplash.com', 'iili.io', 'imgbb.com', 'cloudinary.com'];
        $externalViolations = [];

        foreach ([$bladeContent, $cssContent, json_encode($rawSchema)] as $corpus) {
            foreach ($externalDomains as $domain) {
                if (str_contains($corpus, $domain)) {
                    $externalViolations[] = $domain;
                }
            }
        }
        $externalViolations = array_values(array_unique($externalViolations));
        $isLocalAssetsOnly = empty($externalViolations);

        $localAssetFiles = $assetsExists ? File::allFiles($assetsDir) : [];
        $localAssetCount = count($localAssetFiles);

        // 7. Curated Native Parity (7 Sections Subset)
        $curatedCanonical = ['hero', 'story', 'parents', 'countdown_calendar', 'events', 'timeline', 'album'];
        $curatedPassedCount = collect($sectionsReport)
            ->whereIn('id', $curatedCanonical)
            ->where('passed', true)
            ->count();
        $curatedCompatible = ($curatedPassedCount >= 7);

        // 8. Overall Quality Score (0 - 100%)
        $checksPassed = 0;
        $totalChecks = 6;

        if ($schemaExists && $bladeExists && $cssExists) {
            $checksPassed++;
        }
        if ($isCanonicalValid) {
            $checksPassed++;
        }
        if ($hasEssentialSync) {
            $checksPassed++;
        }
        if ($extendsInvitation) {
            $checksPassed++;
        }
        if ($isLocalAssetsOnly) {
            $checksPassed++;
        }
        if ($curatedCompatible) {
            $checksPassed++;
        }

        $score = (int) round(($checksPassed / $totalChecks) * 100);

        return [
            'slug' => $slug,
            'score' => $score,
            'files' => $filesReport,
            'raw_schema' => $rawSchema,
            'sections_report' => $sectionsReport,
            'canonical_passed_count' => $canonicalCount,
            'is_canonical_valid' => $isCanonicalValid,
            'gallery_valid' => $galleryValid,
            'sync_targets' => $syncTargets,
            'sync_count' => $syncCount,
            'has_essential_sync' => $hasEssentialSync,
            'extends_invitation' => $extendsInvitation,
            'has_embed_guard' => $hasEmbedGuard,
            'uses_bem_prefix' => $usesBEMPrefix,
            'is_local_assets_only' => $isLocalAssetsOnly,
            'external_violations' => $externalViolations,
            'local_asset_count' => $localAssetCount,
            'curated_compatible' => $curatedCompatible,
        ];
    }

    /**
     * Extract Template Visual DNA (Innovation #10).
     * Phân tích dấu ấn Art Direction: Font chữ, bảng màu, spacing rhythm, paper texture.
     *
     * @return array<string, mixed>
     */
    public static function extractVisualDna(string $slug): array
    {
        $templateDir = resource_path("views/client/invitations/{$slug}");
        $cssPath = "{$templateDir}/styles.css";
        $schemaPath = "{$templateDir}/schema.json";

        $cssContent = File::exists($cssPath) ? File::get($cssPath) : '';
        $schemaData = File::exists($schemaPath) ? (json_decode(File::get($schemaPath), true) ?? []) : [];

        // 1. Detect Typography (Heading Serif & Body Sans)
        $fonts = [];
        if (preg_match_all('/font-family:\s*([^;\}]+)/i', $cssContent, $fontMatches)) {
            foreach ($fontMatches[1] as $fm) {
                $clean = trim(str_replace(['"', "'"], '', explode(',', $fm)[0]));
                if ($clean && ! in_array($clean, $fonts, true) && ! in_array(strtolower($clean), ['inherit', 'sans-serif', 'serif', 'system-ui'])) {
                    $fonts[] = $clean;
                }
            }
        }
        $headingFont = $fonts[0] ?? 'Playfair Display';
        $bodyFont = $fonts[1] ?? 'Inter';

        // 2. Detect Color Palette (HEX Colors)
        $colors = [];
        if (preg_match_all('/#([a-f0-9]{6}|[a-f0-9]{3})\b/i', $cssContent, $colorMatches)) {
            $hexCounts = array_count_values(array_map('strtoupper', $colorMatches[0]));
            arsort($hexCounts);
            $colors = array_slice(array_keys($hexCounts), 0, 6);
        }

        if (empty($colors)) {
            $colors = ['#FAF8F5', '#1F1D1A', '#C5A880', '#EFE8DD'];
        }

        // 3. Detect Theme Classification
        $category = $schemaData['category'] ?? '';
        if (str_contains(strtolower($cssContent), 'gold') || str_contains(strtolower($slug), 'gold') || str_contains(strtolower($category), 'luxury')) {
            $theme = 'Muted Gold / Hoàng Gia Sang Trọng';
        } elseif (str_contains(strtolower($cssContent), 'rose') || str_contains(strtolower($slug), 'rose') || str_contains(strtolower($category), 'romantic')) {
            $theme = 'Dusty Rose / Lãng Mạn Thơ Mộng';
        } elseif (str_contains(strtolower($cssContent), 'sage') || str_contains(strtolower($slug), 'sage') || str_contains(strtolower($category), 'minimal')) {
            $theme = 'Sage Green / Tối Giản Thiên Nhiên';
        } else {
            $theme = 'Warm Cream & Ivory / Quiet Luxury Cổ Điển';
        }

        // 4. Paper Texture & Border Style
        $hasBorder = str_contains($cssContent, 'border:') || str_contains($cssContent, 'border-radius');
        $hasPaperTexture = str_contains($cssContent, 'texture') || str_contains($cssContent, 'paper') || str_contains($cssContent, 'noise');

        // 5. Brand Harmony Score (0-100)
        $brandHarmony = 100;
        $violations = [];
        foreach ($colors as $c) {
            if (in_array(strtoupper($c), ['#FF00FF', '#00FFFF', '#00FF00', '#FF0000'])) {
                $brandHarmony -= 20;
                $violations[] = "Màu {$c} có độ chói cao, không phù hợp với chuẩn Quiet Luxury.";
            }
        }

        return [
            'slug' => $slug,
            'heading_font' => $headingFont,
            'body_font' => $bodyFont,
            'fonts' => $fonts,
            'palette' => $colors,
            'theme' => $theme,
            'has_border' => $hasBorder,
            'has_paper_texture' => $hasPaperTexture,
            'brand_harmony' => max(0, $brandHarmony),
            'violations' => $violations,
        ];
    }

    /**
     * Chạy kiểm định toàn diện Preflight Quality Gate trước khi phát hành phiên bản mẫu thiệp (NXT-04).
     *
     * @return array{
     *     slug: string,
     *     is_ready_for_release: bool,
     *     readiness_score: int,
     *     blockers: array<int, string>,
     *     warnings: array<int, string>,
     *     metrics: array<string, mixed>,
     *     viewports: array<int, int>
     * }
     */
    public static function runPreflightAudit(string $slug): array
    {
        $integrity = self::inspectTemplateIntegrity($slug);
        $visualDna = self::extractVisualDna($slug);

        $blockers = [];
        $warnings = [];

        // 1. Files tồn tại
        if (! ($integrity['files']['schema_json']['exists'] ?? false)) {
            $blockers[] = 'Thiếu tệp schema.json khai báo cấu trúc dữ liệu.';
        }
        if (! ($integrity['files']['template_blade']['exists'] ?? false)) {
            $blockers[] = 'Thiếu tệp template.blade.php giao diện chính.';
        }
        if (! ($integrity['files']['styles_css']['exists'] ?? false)) {
            $blockers[] = 'Thiếu tệp styles.css định kiểu mỹ thuật.';
        }

        // 2. 10 Section Tiêu chuẩn
        if (! ($integrity['is_canonical_valid'] ?? false)) {
            $missingCount = 10 - ($integrity['canonical_passed_count'] ?? 0);
            $blockers[] = "Mẫu thiệp chưa đủ 10 Section tiêu chuẩn (hiện có {$integrity['canonical_passed_count']}/10, thiếu {$missingCount} section).";
        }

        // 3. Hợp đồng Live Sync (data-sync-target)
        if (($integrity['sync_count'] ?? 0) < 2 || ! ($integrity['has_essential_sync'] ?? false)) {
            $blockers[] = 'Thiếu hợp đồng Live Sync tối thiểu (bắt buộc data-sync-target="groom_name" và "bride_name").';
        }

        // 4. Zero External CDN (100% Local Assets)
        if (! ($integrity['is_local_assets_only'] ?? true)) {
            $violationsList = implode(', ', $integrity['external_violations'] ?? []);
            $blockers[] = "Phát hiện URL liên kết server bên thứ ba vi phạm quy chuẩn 100% Local Assets: {$violationsList}.";
        }

        // 5. Layout Kế Thừa & Phòng Vệ Embed
        if (! ($integrity['extends_invitation'] ?? false)) {
            $warnings[] = 'Template chưa kế thừa layout chuẩn @extends("client.layouts.invitation").';
        }

        // 6. Brand Harmony
        if (($visualDna['brand_harmony'] ?? 100) < 80) {
            $warnings[] = 'Bảng màu có một số sắc độ chói cần tinh chỉnh thêm theo phong cách Quiet Luxury.';
        }

        $isReady = empty($blockers);

        // Tính điểm Readiness (0 - 100)
        $score = $integrity['score'] ?? 0;
        if (! $isReady) {
            $score = min($score, 65);
        }

        return [
            'slug' => $slug,
            'is_ready_for_release' => $isReady,
            'readiness_score' => $score,
            'blockers' => $blockers,
            'warnings' => $warnings,
            'metrics' => [
                'canonical_sections' => ($integrity['canonical_passed_count'] ?? 0).'/10',
                'sync_targets_count' => $integrity['sync_count'] ?? 0,
                'local_assets_count' => $integrity['local_asset_count'] ?? 0,
                'brand_harmony_score' => $visualDna['brand_harmony'] ?? 100,
                'theme' => $visualDna['theme'] ?? 'Quiet Luxury',
            ],
            'viewports' => [375, 430, 768, 1440],
        ];
    }
}
