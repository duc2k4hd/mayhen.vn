<?php

namespace App\Services;

class PremiumValuePreviewService
{
    /**
     * Danh sách các tính năng cao cấp cho phép cặp đôi trải nghiệm thử (Sandbox Preview).
     *
     * @var array<string, array{title: string, subtitle: string, plan_required: string}>
     */
    protected array $supportedFeatures = [
        'seating' => [
            'title' => 'Sơ Đồ Xếp Bàn Tiệc Studio Trực Quan',
            'subtitle' => 'Kéo thả sắp xếp bàn tròn, bàn dài, quản lý sức chứa và phòng chống quá tải bàn tiệc.',
            'plan_required' => 'Gói Trọn Vẹn (Wedding Pro)',
        ],
        'checkin' => [
            'title' => 'Cổng Tiếp Tân Điểm Danh & Quét Mã QR',
            'subtitle' => 'Quét mã QR trên điện thoại khách để tìm bàn trong 2 giây, điều phối bàn tiệc và xử lý khách phát sinh.',
            'plan_required' => 'Gói Trọn Vẹn (Wedding Pro)',
        ],
        'live-wall' => [
            'title' => 'Bức Tường Trình Chiếu Lễ Cưới Trực Tiếp (Live Wall)',
            'subtitle' => 'Màn hình LED chiếu trực tiếp ảnh khách chụp tại bàn tiệc và lời chúc phúc theo thời gian thực.',
            'plan_required' => 'Gói Trọn Vẹn (Wedding Pro)',
        ],
        'memory-vault' => [
            'title' => 'Két Kỷ Niệm Số & Xuất Gói Ngoại Tuyến ZIP',
            'subtitle' => 'Bảo quản toàn bộ ảnh cưới, chữ ký, lời chúc và xuất tệp ZIP ngoại tuyến mở xem trọn đời.',
            'plan_required' => 'Gói Trọn Vẹn (Wedding Pro)',
        ],
        'welcome-kiosk' => [
            'title' => 'Màn Hình Chào Mừng Sảnh Cưới & Kiosk Đón Tiếp (Venue Welcome Kiosk)',
            'subtitle' => 'Màn hình iPad hoặc TV đặt tại cửa đón tiếp: Slideshow ảnh cưới Ken Burns, chuông pha lê Web Audio và popup chào đón khách tức thời khi quét mã.',
            'plan_required' => 'Gói Trọn Vẹn (Wedding Pro)',
        ],
        'signature-mosaic' => [
            'title' => 'Bức Tranh Ký Tên Vàng Quan Khách Lưu Niệm (Signature Mosaic Wall)',
            'subtitle' => 'Bảo tồn nét ký tay độc bản của từng quan khách thành ảnh PNG trong suốt và ghép thành tác phẩm nghệ thuật hoàng gia trình chiếu toàn màn hình.',
            'plan_required' => 'Gói Trọn Vẹn (Wedding Pro)',
        ],
    ];

    /**
     * Kiểm tra tính năng có được hỗ trợ xem thử không.
     */
    public function isSupported(string $feature): bool
    {
        return array_key_exists($feature, $this->supportedFeatures);
    }

    /**
     * Lấy thông tin giới thiệu tính năng.
     *
     * @return array{title: string, subtitle: string, plan_required: string}|null
     */
    public function getFeatureMeta(string $feature): ?array
    {
        return $this->supportedFeatures[$feature] ?? null;
    }

    /**
     * Tạo dữ liệu mẫu mô phỏng (Sandbox Mock Data) để cặp đôi trải nghiệm trực quan trước khi mua gói.
     *
     * @return array<string, mixed>
     */
    public function getSandboxData(string $feature): array
    {
        return match ($feature) {
            'seating' => [
                'tables' => [
                    ['id' => 101, 'name' => 'Bàn VIP 01 - Họ Hàng Nhà Trai', 'shape' => 'round', 'capacity' => 10, 'assigned' => 8, 'status' => 'optimal'],
                    ['id' => 102, 'name' => 'Bàn VIP 02 - Họ Hàng Nhà Gái', 'shape' => 'round', 'capacity' => 10, 'assigned' => 10, 'status' => 'optimal'],
                    ['id' => 103, 'name' => 'Bàn 03 - Bạn Cấp 3 Chú Rể', 'shape' => 'round', 'capacity' => 10, 'assigned' => 12, 'status' => 'overfilled'],
                    ['id' => 104, 'name' => 'Bàn 04 - Đồng Nghiệp Công Ty', 'shape' => 'long', 'capacity' => 12, 'assigned' => 6, 'status' => 'underfilled'],
                ],
                'unassigned_guests' => [
                    ['id' => 201, 'name' => 'Anh Hoàng Nam', 'group' => 'Bạn Đại Học', 'headcount' => 2],
                    ['id' => 202, 'name' => 'Chị Bích Thảo', 'group' => 'Đồng Nghiệp', 'headcount' => 1],
                    ['id' => 203, 'name' => 'Bác Trần Văn Phú', 'group' => 'Họ Hàng', 'headcount' => 2],
                ],
                'stats' => ['total_tables' => 4, 'total_capacity' => 42, 'assigned_seats' => 36, 'unassigned_seats' => 5],
            ],
            'checkin' => [
                'recent_checkins' => [
                    ['name' => 'Bác Lê Hoàng Long', 'table' => 'Bàn VIP 01', 'headcount' => 2, 'time' => '11:15', 'note' => 'Đã vào bàn'],
                    ['name' => 'Anh Nguyễn Văn An', 'table' => 'Bàn 03', 'headcount' => 1, 'time' => '11:18', 'note' => 'Khách đến đúng giờ'],
                    ['name' => 'Chị Phạm Thu Hương', 'table' => 'Bàn 04', 'headcount' => 2, 'time' => '11:22', 'note' => 'Khách phát sinh'],
                ],
                'stats' => ['checked_in_count' => 128, 'total_expected' => 250, 'percent' => 51],
            ],
            'live-wall' => [
                'photos' => [
                    ['id' => 1, 'url' => '/assets/client/images/banners/banner-home.webp', 'author' => 'Anh Tuấn', 'caption' => 'Chúc mừng đôi uyên ương trăm năm hạnh phúc!'],
                    ['id' => 2, 'url' => '/assets/client/images/banners/banner-home.webp', 'author' => 'Bạn Mai', 'caption' => 'Cô dâu xinh lung linh luôn nè!'],
                ],
                'stats' => ['photos_count' => 24, 'wishes_count' => 86],
            ],
            'memory-vault' => [
                'archive_files' => [
                    ['name' => 'index.html', 'desc' => 'Trang thiệp tĩnh tự chứa, mở xem không cần internet', 'size' => '2.4 MB'],
                    ['name' => 'danh_sach_khach_moi_va_rsvp.csv', 'desc' => 'Bảng tính Excel danh bạ và kết quả điểm danh', 'size' => '45 KB'],
                    ['name' => 'loi_chuc_va_chu_ky.json', 'desc' => 'Dữ liệu nguyên bản lời chúc và hồi đáp cặp đôi', 'size' => '82 KB'],
                    ['name' => 'wedding_manifest.json', 'desc' => 'Mã băm SHA-256 bảo đảm tính toàn vẹn kỹ thuật số', 'size' => '8 KB'],
                ],
                'stats' => ['total_wishes' => 142, 'total_photos' => 38, 'retention' => '2 Năm (Gói Pro)'],
            ],
            'welcome-kiosk' => [
                'recent_welcomes' => [
                    ['name' => 'Bác Trần Văn Phú & Gia Đình', 'table' => 'Bàn VIP 01 - Họ Nhà Trai', 'time' => '11:28', 'salutation' => 'Kính chào'],
                    ['name' => 'Anh Nguyễn Hoàng Nam', 'table' => 'Bàn 03 - Bạn Cấp 3 Chú Rể', 'time' => '11:32', 'salutation' => 'Chào mừng'],
                    ['name' => 'Chị Bích Thảo & Bạn', 'table' => 'Bàn 04 - Đồng Nghiệp', 'time' => '11:35', 'salutation' => 'Nhiệt liệt chào đón'],
                ],
                'stats' => ['active_screens' => 2, 'audio_effects' => 'Crystal Chimes (Web Audio API)', 'mode' => 'Ken Burns Slideshow 60fps'],
            ],
            'signature-mosaic' => [
                'couple' => [
                    'groom' => 'Nguyễn Tuấn',
                    'bride' => 'Trần Lan',
                    'quote' => 'Mỗi nét chữ ký là một mảnh ghép thiêng liêng đong đầy yêu thương cho hạnh phúc của chúng tôi.',
                ],
                'guest_signatures' => [
                    ['name' => 'Lê Hoàng Minh', 'group' => 'Bạn Thân', 'time' => '11:25', 'is_pinned' => true],
                    ['name' => 'Bác Nguyễn Văn An', 'group' => 'Họ Hàng Nhà Trai', 'time' => '11:30', 'is_pinned' => true],
                    ['name' => 'Chị Phạm Thu Hương', 'group' => 'Đồng Nghiệp', 'time' => '11:34', 'is_pinned' => false],
                    ['name' => 'Vũ Đình Tuấn', 'group' => 'Bạn Đại Học', 'time' => '11:38', 'is_pinned' => false],
                ],
                'stats' => ['total_signatures' => 168, 'pinned_count' => 12, 'theme' => 'Vàng Kim Hoàng Gia (Royal Cream & Gold)'],
            ],
            default => [],
        };
    }
}
