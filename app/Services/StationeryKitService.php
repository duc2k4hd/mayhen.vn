<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Wedding;
use Carbon\Carbon;

class StationeryKitService
{
    /**
     * Danh sách 5 Bảng Màu Quiet Luxury đồng bộ của Mây Hẹn.
     *
     * @return array<string, array{id: string, name: string, bg: string, card_bg: string, primary: string, accent: string, text: string, muted: string, border: string}>
     */
    public static function getColorPalettes(): array
    {
        return [
            'velvet_crimson' => [
                'id' => 'velvet_crimson',
                'name' => 'Đỏ Nhung Hoàng Gia & Gold',
                'bg' => '#FAF7F2',
                'card_bg' => '#5E0E1C',
                'primary' => '#FFFFFF',
                'accent' => '#CFA860',
                'text' => '#FBF6F0',
                'muted' => '#D9C5B2',
                'border' => '#CFA860',
            ],
            'dusty_rose' => [
                'id' => 'dusty_rose',
                'name' => 'Hồng Tro & Phấn Lụa Champagne',
                'bg' => '#FFFDF9',
                'card_bg' => '#FAF4F0',
                'primary' => '#4A352F',
                'accent' => '#B88272',
                'text' => '#2E221E',
                'muted' => '#8C746E',
                'border' => '#D9B4A7',
            ],
            'sage_green' => [
                'id' => 'sage_green',
                'name' => 'Xanh Xô Thơm & Rừng Thu',
                'bg' => '#FAF9F5',
                'card_bg' => '#F2F5F2',
                'primary' => '#23382D',
                'accent' => '#9C7A4A',
                'text' => '#1F2E26',
                'muted' => '#6A7D73',
                'border' => '#8DA396',
            ],
            'royal_navy' => [
                'id' => 'royal_navy',
                'name' => 'Xanh Hoàng Gia & Ánh Bạc',
                'bg' => '#FAF8F5',
                'card_bg' => '#141E30',
                'primary' => '#FFFFFF',
                'accent' => '#D4AF37',
                'text' => '#F4F6F9',
                'muted' => '#A0ABC0',
                'border' => '#D4AF37',
            ],
            'warm_amber' => [
                'id' => 'warm_amber',
                'name' => 'Hổ Phách & Nâu Kem Cổ Điển',
                'bg' => '#FAF8F5',
                'card_bg' => '#F7F3ED',
                'primary' => '#3D2817',
                'accent' => '#A47148',
                'text' => '#2A1B0E',
                'muted' => '#7A6453',
                'border' => '#C5A880',
            ],
        ];
    }

    /**
     * Danh sách 6 Định dạng Ấn phẩm Đồng bộ tiêu chuẩn (Mục 2.7 DOCX).
     *
     * @return array<string, array{id: string, name: string, ratio: string, width: int, height: int, icon: string, description: string, tag: string}>
     */
    public static function getPresets(): array
    {
        return [
            'story' => [
                'id' => 'story',
                'name' => 'Ảnh Chia Sẻ Story 9:16',
                'ratio' => '9:16',
                'width' => 1080,
                'height' => 1920,
                'icon' => '📱',
                'description' => 'Tỷ lệ dọc 9:16 chuẩn nét cho Story Instagram, Facebook, Zalo kèm mã QR dẫn đến thiệp cưới.',
                'tag' => 'Mạng Xã Hội',
            ],
            'save_the_date' => [
                'id' => 'save_the_date',
                'name' => 'Ảnh Báo Ngày Save The Date',
                'ratio' => '9:16',
                'width' => 1080,
                'height' => 1920,
                'icon' => '💌',
                'description' => 'Ảnh thông báo ngày cưới sớm gửi bạn bè thân thiết để lưu giữ lịch hẹn trước lễ cưới.',
                'tag' => 'Gửi Trước 1-2 Tháng',
            ],
            'vertical_card' => [
                'id' => 'vertical_card',
                'name' => 'Thiệp Ảnh Dọc Lưu Điện Thoại',
                'ratio' => '9:16',
                'width' => 1080,
                'height' => 1920,
                'icon' => '🎴',
                'description' => 'Bản thiệp cưới dạng ảnh dọc đầy đủ thông tin cha mẹ, giờ tiệc và sảnh đón tiếp sang trọng.',
                'tag' => 'Lưu Giữ & In Ấn',
            ],
            'table_qr' => [
                'id' => 'table_qr',
                'name' => 'Standee QR Đặt Bàn Tiệc (Place Card)',
                'ratio' => 'A5 Đứng (1:1.41)',
                'width' => 1200,
                'height' => 1700,
                'icon' => '🏷️',
                'description' => 'Biển đặt tại bàn tiệc cưới để quan khách quét mã xem album ảnh, gửi lời chúc mừng và mừng cưới.',
                'tag' => 'Sảnh Tiệc & Bàn Cưới',
            ],
            'timeline' => [
                'id' => 'timeline',
                'name' => 'Ảnh Lịch Trình Tiệc Cưới (Timeline)',
                'ratio' => '9:16',
                'width' => 1080,
                'height' => 1920,
                'icon' => '⏳',
                'description' => 'Lịch trình trực quan các mốc: Đón khách, Nghi thức lễ, Khai tiệc mừng và Chụp ảnh kỷ niệm.',
                'tag' => 'Lịch Trình Hôn Lễ',
            ],
            'bao_hy' => [
                'id' => 'bao_hy',
                'name' => 'Thiệp Báo Hỷ Phương Xa',
                'ratio' => '9:16',
                'width' => 1080,
                'height' => 1920,
                'icon' => '🕊️',
                'description' => 'Thiệp báo hỷ trang trọng gửi họ hàng, đồng nghiệp hoặc bạn hữu ở xa không tiện dự tiệc trực tiếp.',
                'tag' => 'Báo Hỷ & Tri Ân',
            ],
            'thank_you' => [
                'id' => 'thank_you',
                'name' => 'Thiệp Cảm Ơn Quan Khách (Thank You)',
                'ratio' => '9:16',
                'width' => 1080,
                'height' => 1920,
                'icon' => '🌸',
                'description' => 'Thiệp cảm ơn tinh tế gửi sau ngày chung đôi tri ân sự hiện diện và tình cảm của quan khách.',
                'tag' => 'Hậu Sự Kiện',
            ],
        ];
    }

    /**
     * Trích xuất và chuẩn hóa toàn bộ dữ liệu hôn lễ để nạp vào Canvas Studio.
     *
     * @return array<string, mixed>
     */
    public static function getStationeryData(Wedding $wedding, Invitation $invitation): array
    {
        $extra = is_array($wedding->extra_data)
            ? $wedding->extra_data
            : (json_decode($wedding->extra_data ?? '[]', true) ?: []);

        // Ngày cưới
        $weddingDate = $wedding->wedding_date instanceof Carbon
            ? $wedding->wedding_date
            : ($wedding->wedding_date ? Carbon::parse($wedding->wedding_date) : Carbon::today()->addMonths(2));

        // Format ngày tháng tiếng Việt
        $dayOfWeek = match ($weddingDate->dayOfWeek) {
            0 => 'Chủ Nhật',
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
        };

        $solarDateFormatted = "{$dayOfWeek}, Ngày {$weddingDate->format('d')} Tháng {$weddingDate->format('m')} Năm {$weddingDate->format('Y')}";
        $solarDateShort = $weddingDate->format('d . m . Y');

        // Danh sách mốc sự kiện Timeline (Lấy từ bảng WeddingEvent hoặc extra_data)
        $events = $wedding->events()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('event_time')
            ->get();

        $timelineItems = [];
        if ($events->isNotEmpty()) {
            foreach ($events as $event) {
                $timelineItems[] = [
                    'time' => $event->event_time ? Carbon::parse($event->event_time)->format('H:i') : '11:00',
                    'title' => $event->name,
                    'desc' => $event->description ?: $event->type_label,
                ];
            }
        } else {
            // Mẫu Timeline mặc định chuẩn văn hóa cưới Việt Nam
            $partyTime = $extra['event_party_time'] ?? '11:30';
            $timelineItems = [
                ['time' => '10:30', 'title' => 'Đón Tiếp Quan Khách', 'desc' => 'Chụp ảnh lưu niệm tại Backdrop sảnh đón'],
                ['time' => '11:15', 'title' => 'Nghi Thức Lễ Thành Hôn', 'desc' => 'Trao nhẫn cưới & cắt bánh hạnh phúc'],
                ['time' => $partyTime, 'title' => 'Khai Tiệc Mừng Hạnh Phúc', 'desc' => 'Nâng ly chúc mừng hạnh phúc đôi uyên ương'],
                ['time' => '13:00', 'title' => 'Lời Tri Ân & Tiễn Khách', 'desc' => 'Gửi quà cảm ơn & lưu lại khoảnh khắc đẹp'],
            ];
        }

        // Link thiệp cưới công khai
        $invitationUrl = url('/thiep/'.$invitation->slug);

        // QR Code SVG chuẩn Pure PHP vector sắc nét
        $qrSvg = QrCodeService::svg($invitationUrl, 320, '#1F1D1A', '#FFFFFF');
        $qrCodeDataUri = 'data:image/svg+xml;base64,'.base64_encode($qrSvg);

        return [
            'invitation_id' => $invitation->id,
            'invitation_slug' => $invitation->slug,
            'invitation_url' => $invitationUrl,
            'qr_code_svg' => $qrSvg,
            'qr_code_data_uri' => $qrCodeDataUri,
            'groom_name' => $wedding->groom_name ?: 'Minh Quân',
            'bride_name' => $wedding->bride_name ?: 'Hoàng Yến',
            'groom_initial' => mb_substr($wedding->groom_name ?: 'Q', 0, 1, 'UTF-8'),
            'bride_initial' => mb_substr($wedding->bride_name ?: 'Y', 0, 1, 'UTF-8'),
            'groom_father' => $extra['groom_father'] ?? ($extra['event_groom_father'] ?? 'Nguyễn Văn An'),
            'groom_mother' => $extra['groom_mother'] ?? ($extra['event_groom_mother'] ?? 'Trần Thị Bình'),
            'bride_father' => $extra['bride_father'] ?? ($extra['event_bride_father'] ?? 'Lê Văn Cường'),
            'bride_mother' => $extra['bride_mother'] ?? ($extra['event_bride_mother'] ?? 'Phạm Thị Dung'),
            'wedding_date_raw' => $weddingDate->toDateString(),
            'wedding_date_formatted' => $solarDateFormatted,
            'wedding_date_short' => $solarDateShort,
            'lunar_date' => $extra['event_party_lunar'] ?? ($extra['lunar_date'] ?? 'Ngày 18 tháng 11 năm Bính Ngọ'),
            'party_time' => $extra['event_party_time'] ?? '11:30',
            'venue_name' => $wedding->venue_name ?: ($extra['event_party_venue'] ?? 'Trung Tâm Hội Nghị Tiệc Cưới White Palace'),
            'venue_address' => $wedding->venue_address ?: ($extra['event_party_address'] ?? '194 Hoàng Văn Thụ, Phường 9, Quận Phú Nhuận, TP. Hồ Chí Minh'),
            'quote' => $extra['quote'] ?? 'Tình yêu không phải là nhìn nhau, mà là cùng nhau nhìn về một hướng.',
            'cover_photo' => $wedding->cover_photo_url ?: ($extra['hero_photo_url'] ?? asset('assets/client/images/template-placeholder.webp')),
            'timeline' => $timelineItems,
        ];
    }
}
