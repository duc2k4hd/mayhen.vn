<?php

namespace App\Services;

use App\Models\Invitation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class OpenGraphService
{
    /**
     * Sinh vector SVG thẻ chia sẻ OpenGraph 1200x630px chuẩn Quiet Luxury.
     */
    public static function generateSvg(Invitation $invitation): string
    {
        $wedding = $invitation->wedding;
        $invitationUrl = url('/thiep/'.$invitation->slug);

        // Khóa cache dựa trên thời gian cập nhật của thiệp và tiệc cưới
        $cacheKey = 'og_card_svg_'.$invitation->id.'_'.($invitation->updated_at?->timestamp ?? 0).'_'.($wedding?->updated_at?->timestamp ?? 0);

        return Cache::remember($cacheKey, 86400, function () use ($wedding, $invitationUrl) {
            $groomName = htmlspecialchars($wedding?->groom_name ?: 'Minh Quân', ENT_QUOTES, 'UTF-8');
            $brideName = htmlspecialchars($wedding?->bride_name ?: 'Hoàng Yến', ENT_QUOTES, 'UTF-8');

            $weddingDate = $wedding?->wedding_date instanceof Carbon
                ? $wedding->wedding_date
                : ($wedding?->wedding_date ? Carbon::parse($wedding->wedding_date) : Carbon::today()->addMonths(2));

            $dayOfWeek = match ($weddingDate->dayOfWeek) {
                0 => 'CHỦ NHẬT',
                1 => 'THỨ HAI',
                2 => 'THỨ BA',
                3 => 'THỨ TƯ',
                4 => 'THỨ NĂM',
                5 => 'THỨ SÁU',
                6 => 'THỨ BẢY',
            };

            $solarDateFormatted = "{$dayOfWeek}, NGÀY {$weddingDate->format('d')} THÁNG {$weddingDate->format('m')} NĂM {$weddingDate->format('Y')}";

            $extra = is_array($wedding?->extra_data)
                ? $wedding->extra_data
                : (json_decode($wedding?->extra_data ?? '[]', true) ?: []);

            $lunarDate = htmlspecialchars($extra['event_party_lunar'] ?? ($extra['lunar_date'] ?? 'Ngày 18 tháng 11 năm Bính Ngọ'), ENT_QUOTES, 'UTF-8');
            $venueName = htmlspecialchars($wedding?->venue_name ?: ($extra['event_party_venue'] ?? 'Trung Tâm Hội Nghị Tiệc Cưới White Palace'), ENT_QUOTES, 'UTF-8');
            $venueAddress = htmlspecialchars($wedding?->venue_address ?: ($extra['event_party_address'] ?? 'TP. Hồ Chí Minh'), ENT_QUOTES, 'UTF-8');

            // Cắt ngắn địa chỉ nếu quá dài
            if (mb_strlen($venueAddress) > 55) {
                $venueAddress = mb_substr($venueAddress, 0, 52).'…';
            }

            // Sinh mã QR vector 200x200
            $qrSvg = QrCodeService::svg($invitationUrl, 180, '#1F1D1A', '#FAF8F5');
            // Trích xuất phần ruột SVG QR (bỏ thẻ svg ngoài để nhúng vào thẻ g)
            $qrInner = preg_replace('/^<svg[^>]*>|<\/svg>$/i', '', $qrSvg);

            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630" width="1200" height="630" style="background:#FAF7F2; font-family:'Cormorant Garamond', 'Playfair Display', 'Times New Roman', serif;">
    <defs>
        <linearGradient id="bgGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#FAF7F2"/>
            <stop offset="50%" stop-color="#F5F0E6"/>
            <stop offset="100%" stop-color="#EFE8DD"/>
        </linearGradient>
        <linearGradient id="goldGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#D4AF37"/>
            <stop offset="50%" stop-color="#C5A880"/>
            <stop offset="100%" stop-color="#9C7A4A"/>
        </linearGradient>
        <pattern id="subtleGrid" width="60" height="60" patternUnits="userSpaceOnUse">
            <circle cx="30" cy="30" r="0.75" fill="#C5A880" opacity="0.15"/>
        </pattern>
    </defs>

    <!-- Nền tổng thể -->
    <rect width="1200" height="630" fill="url(#bgGradient)"/>
    <rect width="1200" height="630" fill="url(#subtleGrid)"/>

    <!-- Khung viền kép Hoàng Gia (Double Ornamental Border) -->
    <rect x="36" y="36" width="1128" height="558" fill="none" stroke="#C5A880" stroke-width="1.5" opacity="0.6"/>
    <rect x="46" y="46" width="1108" height="538" fill="none" stroke="#C5A880" stroke-width="2.5"/>

    <!-- 4 Góc hoa văn Hoàng Gia (Art Deco Corners) -->
    <!-- Top-Left -->
    <path d="M46,80 L80,46 M46,95 L95,46 M46,56 L56,46" stroke="#9C7A4A" stroke-width="1.5" fill="none"/>
    <circle cx="68" cy="68" r="4" fill="#C5A880"/>

    <!-- Top-Right -->
    <path d="M1154,80 L1120,46 M1154,95 L1105,46 M1154,56 L1144,46" stroke="#9C7A4A" stroke-width="1.5" fill="none"/>
    <circle cx="1132" cy="68" r="4" fill="#C5A880"/>

    <!-- Bottom-Left -->
    <path d="M46,550 L80,584 M46,535 L95,584 M46,574 L56,584" stroke="#9C7A4A" stroke-width="1.5" fill="none"/>
    <circle cx="68" cy="562" r="4" fill="#C5A880"/>

    <!-- Bottom-Right -->
    <path d="M1154,550 L1120,584 M1154,535 L1105,584 M1154,574 L1144,584" stroke="#9C7A4A" stroke-width="1.5" fill="none"/>
    <circle cx="1132" cy="562" r="4" fill="#C5A880"/>

    <!-- Eyebrow: Huy Hiệu & Lời Tựa -->
    <g transform="translate(600, 110)" text-anchor="middle">
        <text font-family="'Inter', sans-serif" font-size="13" font-weight="600" fill="#9C7A4A" letter-spacing="6">
            ✦ LỜI MỜI THÀNH HÔN TRÂN QUÝ ✦
        </text>
        <line x1="-140" y1="20" x2="140" y2="20" stroke="#C5A880" stroke-width="1" opacity="0.5"/>
    </g>

    <!-- Tên Chú Rể & Cô Dâu -->
    <g transform="translate(600, 230)" text-anchor="middle">
        <text font-size="64" font-weight="700" fill="#1F1D1A" letter-spacing="1">
            {$groomName}
        </text>
        <text y="50" font-family="'Inter', sans-serif" font-size="28" font-style="italic" fill="#C5A880">
            &amp;
        </text>
        <text y="125" font-size="64" font-weight="700" fill="#1F1D1A" letter-spacing="1">
            {$brideName}
        </text>
    </g>

    <!-- Phân cách hoa văn ở giữa -->
    <g transform="translate(600, 405)" text-anchor="middle">
        <line x1="-220" y1="0" x2="-35" y2="0" stroke="#C5A880" stroke-width="1.5"/>
        <text y="6" font-size="20" fill="#9C7A4A">❦</text>
        <line x1="35" y1="0" x2="220" y2="0" stroke="#C5A880" stroke-width="1.5"/>
    </g>

    <!-- Thời gian & Địa điểm cưới -->
    <g transform="translate(600, 455)" text-anchor="middle">
        <text font-family="'Inter', sans-serif" font-size="16" font-weight="700" fill="#85182A" letter-spacing="2">
            {$solarDateFormatted}
        </text>
        <text y="28" font-family="'Inter', sans-serif" font-size="14" fill="#6A655C">
            (Âm lịch: {$lunarDate})
        </text>
        <text y="62" font-family="'Inter', sans-serif" font-size="18" font-weight="600" fill="#1F1D1A">
            {$venueName}
        </text>
        <text y="88" font-family="'Inter', sans-serif" font-size="13" fill="#7A756D">
            {$venueAddress}
        </text>
    </g>

    <!-- Thẻ QR Code Đặt Góc Phải -->
    <g transform="translate(930, 395)">
        <rect width="180" height="180" rx="10" fill="#FFFFFF" stroke="#E0D7CB" stroke-width="1.5"/>
        <g transform="translate(0, 0)">
            {$qrInner}
        </g>
        <rect x="0" y="158" width="180" height="22" rx="0" fill="#1F1D1A" opacity="0.85"/>
        <text x="90" y="173" font-family="'Inter', sans-serif" font-size="10" font-weight="600" fill="#FAF7F2" text-anchor="middle" letter-spacing="1">
            QUÉT ĐỂ MỞ THIỆP
        </text>
    </g>

    <!-- Footer: Thương Hiệu Mây Hẹn Quiet Luxury -->
    <g transform="translate(100, 560)">
        <text font-family="'Inter', sans-serif" font-size="12" font-weight="600" fill="#9C7A4A" letter-spacing="3">
            MÂY HẸN ATELIER
        </text>
        <text x="145" y="0" font-family="'Inter', sans-serif" font-size="11" fill="#A89F91" letter-spacing="1">
            • mayhen.vn
        </text>
    </g>
</svg>
SVG;
        });
    }

    /**
     * Danh sách 4 Mẫu Kịch Bản Lời Mời Zalo Tiêu Chuẩn (Mục 2.5 Master DOCX).
     *
     * @return array<string, array{id: string, name: string, icon: string, audience: string, content: string}>
     */
    public static function getZaloTemplates(): array
    {
        return [
            'formal' => [
                'id' => 'formal',
                'name' => 'Trang Trọng & Tôn Kính',
                'icon' => '👑',
                'audience' => 'Gửi Bề Trên, Họ Hàng, Người Lớn Tuổi',
                'content' => "Kính gửi {salutation} {guest_name},\n\nĐược sự đồng thuận của hai bên gia đình, {couple_names} trân trọng kính mời {salutation} và gia đình tới tham dự Lễ Thành Hôn cùng chung vui ngày hạnh phúc của chúng con vào ngày {wedding_date} tại {venue_name}.\n\nKính mời {salutation} mở thiệp cưới online và gửi lời chúc phúc cho chúng con tại liên kết dưới đây:\n{invitation_url}\n\nSự hiện diện của {salutation} là niềm vinh hạnh lớn lao cho gia đình chúng con!",
            ],
            'colleague' => [
                'id' => 'colleague',
                'name' => 'Lịch Thiệp & Ấm Áp',
                'icon' => '💼',
                'audience' => 'Gửi Đồng Nghiệp, Đối Tác, Cấp Trên',
                'content' => "Thân gửi {salutation} {guest_name},\n\nSau chặng đường đồng hành, {couple_names} chuẩn bị bước vào một hành trình mới. Chúng mình trân trọng kính mời {salutation} tới chung vui trong bữa tiệc cưới ấm cúng vào ngày {wedding_date} tại {venue_name}.\n\n{salutation} xem thiệp mời chi tiết và xác nhận tham dự giúp chúng mình tại đây nhé:\n{invitation_url}\n\nRất mong được đón tiếp {salutation} trong ngày vui này!",
            ],
            'intimate' => [
                'id' => 'intimate',
                'name' => 'Thân Mật & Trẻ Trung',
                'icon' => '✨',
                'audience' => 'Gửi Bạn Bè Thân Thiết, Hội Bạn',
                'content' => "Alo {salutation} {guest_name} ơi,\n\nNgày đặc biệt nhất của {couple_names} đã đến rồi! Rất mong {salutation} sẽ bớt chút thời gian đến quẩy cùng chúng mình vào ngày {wedding_date} tại {venue_name}.\n\nNhấn vào link để xem thiệp cưới và nhớ xác nhận RSVP sớm để chúng mình chuẩn bị chỗ ngồi chu đáo nhất nha:\n{invitation_url}\n\nHẹn gặp {salutation} ngày chung đôi!",
            ],
            'faraway' => [
                'id' => 'faraway',
                'name' => 'Báo Hỷ & Bạn Phương Xa',
                'icon' => '🕊️',
                'audience' => 'Gửi Bạn Hữu Ở Xa, Không Tiện Dự Trực Tiếp',
                'content' => "Thân gửi {salutation} {guest_name},\n\nDù khoảng cách địa lý không thể sum vầy trực tiếp, {couple_names} vẫn muốn gửi tới {salutation} tấm thiệp Báo Hỷ này như một lời tri ân tình cảm thân thương đã dành cho chúng mình suốt thời gian qua.\n\n{salutation} có thể mở thiệp để ngắm album ảnh cưới và gửi lời chúc mừng cho chúng mình tại link:\n{invitation_url}\n\nCảm ơn {salutation} rất nhiều vì luôn dõi theo hạnh phúc của chúng mình!",
            ],
        ];
    }
}
