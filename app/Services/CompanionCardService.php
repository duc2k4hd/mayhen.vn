<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Support\Collection;

class CompanionCardService
{
    /**
     * Kích thước thẻ in chuẩn xưởng in (mm và tỷ lệ).
     */
    public const SIZES = [
        'namecard' => [
            'name' => 'Namecard / Mini Card (90 × 54 mm)',
            'short_name' => 'Namecard 90×54mm',
            'desc' => 'Chuẩn kẹp vừa vặn mọi phong bì thiệp giấy truyền thống',
            'width_mm' => 90,
            'height_mm' => 54,
            'aspect_ratio' => '90 / 54',
            'items_per_a4' => 10, // 2 cột x 5 hàng trên A4
            'cols_per_a4' => 2,
        ],
        'postcard' => [
            'name' => 'Bưu thiếp / Postcard (100 × 150 mm)',
            'short_name' => 'Postcard 100×150mm',
            'desc' => 'Khổ bưu thiếp đứng trang trọng, kẹp thiệp cao cấp hoặc để bàn',
            'width_mm' => 100,
            'height_mm' => 150,
            'aspect_ratio' => '100 / 150',
            'items_per_a4' => 4, // 2 cột x 2 hàng trên A4
            'cols_per_a4' => 2,
        ],
        'square' => [
            'name' => 'Thẻ Vuông Nghệ Thuật (100 × 100 mm)',
            'short_name' => 'Thẻ Vuông 100×100mm',
            'desc' => 'Thiết kế vuông hiện đại, phong cách Editorial tối giản',
            'width_mm' => 100,
            'height_mm' => 100,
            'aspect_ratio' => '1 / 1',
            'items_per_a4' => 6, // 2 cột x 3 hàng trên A4
            'cols_per_a4' => 2,
        ],
    ];

    /**
     * Bảng màu Quiet Luxury tuyển chọn cho thẻ in.
     */
    public const PALETTES = [
        'gold' => [
            'name' => 'Vàng Cung Điện & Giấy Ngà (Royal Ivory & Gold)',
            'bg_color' => '#FFFDF9',
            'border_color' => '#C5A880',
            'text_color' => '#1F1D1A',
            'accent_color' => '#9C7A4A',
            'qr_color' => '#1F1D1A',
            'qr_bg' => '#FFFFFF',
            'badge_bg' => '#F7F1E6',
        ],
        'charcoal' => [
            'name' => 'Than Chì Sâu & Vàng Hoàng Gia (Charcoal Noir & Gold)',
            'bg_color' => '#1F1D1A',
            'border_color' => '#D8B276',
            'text_color' => '#FAF8F5',
            'accent_color' => '#D8B276',
            'qr_color' => '#1F1D1A',
            'qr_bg' => '#FAF8F5',
            'badge_bg' => '#2E2B27',
        ],
        'sage' => [
            'name' => 'Xanh Xô Thơm & Bạc Hà Lụa (Sage Silk & Moss)',
            'bg_color' => '#F4F7F4',
            'border_color' => '#7A917A',
            'text_color' => '#1F291F',
            'accent_color' => '#486648',
            'qr_color' => '#1F291F',
            'qr_bg' => '#FFFFFF',
            'badge_bg' => '#E6EDE6',
        ],
        'ruby' => [
            'name' => 'Đỏ Rượu Vang & Vàng Antique (Velvet Ruby & Amber)',
            'bg_color' => '#5E0E1C',
            'border_color' => '#E8C88A',
            'text_color' => '#FAF7F2',
            'accent_color' => '#E8C88A',
            'qr_color' => '#5E0E1C',
            'qr_bg' => '#FAF7F2',
            'badge_bg' => '#741525',
        ],
        'dusty_rose' => [
            'name' => 'Hồng Tro Lụa & Nâu Trầm (Dusty Rose & Terracotta)',
            'bg_color' => '#FCF6F6',
            'border_color' => '#A26B6B',
            'text_color' => '#3E2424',
            'accent_color' => '#7D4E4E',
            'qr_color' => '#3E2424',
            'qr_bg' => '#FFFFFF',
            'badge_bg' => '#F3E5E5',
        ],
    ];

    /**
     * Phong cách hoa văn viền thẻ (Card Motifs).
     */
    public const MOTIFS = [
        'royal_crest' => 'Khung Viền Góc Hoàng Gia (Royal Crest & Corner Flourish)',
        'minimal_editorial' => 'Tối Giản Hiện Đại Viền Đôi (Minimalist Double Frame)',
        'floral_botanical' => 'Họa Tiết Nhành Lá Tình Yêu (Botanical Laurel Branches)',
    ];

    /**
     * Lấy cấu hình đầy đủ của thẻ kẹp cho một thiệp cưới.
     */
    public static function resolveCardConfig(Invitation $invitation, array $overrides = []): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) ($invitation->custom_config ?? '{}'), true) ?? []);

        $saved = $customConfig['companion_card'] ?? [];
        $wedding = $invitation->wedding;

        $default = [
            'size' => 'namecard',
            'palette' => 'gold',
            'motif' => 'royal_crest',
            'kicker' => 'THƯ MỜI HÔN LỄ TRỰC TUYẾN',
            'headline' => 'Trân Trọng Kính Mời',
            'instructions' => 'Quét mã QR để xem Album ảnh cưới, bản đồ chỉ đường & gửi lời chúc phúc cùng đôi uyên ương:',
            'couple_title' => $wedding ? "{$wedding->groom_name} & {$wedding->bride_name}" : 'Cô Dâu & Chú Rể',
            'show_date' => true,
            'show_venue' => true,
            'show_qr_label' => true,
            'qr_label' => 'Quét QR Xem Thiệp',
            'include_guest_name' => false,
            'footer_brand' => 'MÂY HẸN • MAYHEN.VN',
        ];

        return array_merge($default, $saved, $overrides);
    }

    /**
     * Tạo mã SVG của thẻ kẹp cho xem trước hoặc xuất vector.
     */
    public static function generateCardSvg(Invitation $invitation, array $config, ?Guest $guest = null): string
    {
        $wedding = $invitation->wedding;
        $sizeMeta = self::SIZES[$config['size'] ?? 'namecard'] ?? self::SIZES['namecard'];
        $palette = self::PALETTES[$config['palette'] ?? 'gold'] ?? self::PALETTES['gold'];
        $motif = $config['motif'] ?? 'royal_crest';

        $wMm = $sizeMeta['width_mm'];
        $hMm = $sizeMeta['height_mm'];

        // Chuyển mm sang pixel hệ số hiển thị (1mm ≈ 3.78px, hệ 300DPI in: 1mm = 11.81px)
        $scale = 4;
        $svgW = $wMm * $scale;
        $svgH = $hMm * $scale;

        // Sinh mã QR target URL
        $targetUrl = $guest
            ? url("/thiep/{$invitation->slug}?guest={$guest->guest_token}")
            : url("/thiep/{$invitation->slug}");

        $qrSvg = QrCodeService::svg($targetUrl, 160, $palette['qr_color'], $palette['qr_bg']);

        $coupleName = htmlspecialchars($config['couple_title'] ?: ($wedding ? "{$wedding->groom_name} & {$wedding->bride_name}" : 'Cô Dâu & Chú Rể'));
        $kicker = htmlspecialchars($config['kicker'] ?: 'THƯ MỜI HÔN LỄ TRỰC TUYẾN');
        $instructions = htmlspecialchars($config['instructions'] ?: 'Quét mã QR để xem trọn vẹn thiệp mời & xác nhận tham dự:');
        $dateText = ($wedding && $wedding->wedding_date) ? date('d.m.Y', strtotime($wedding->wedding_date)) : '';
        $venueText = htmlspecialchars($wedding->venue_name ?? '');
        $guestName = $guest ? htmlspecialchars("Kính gửi: {$guest->name}") : '';

        // Tùy theo khổ in (Namecard ngang hay Postcard đứng), tính toán layout
        $isVertical = $hMm > $wMm;

        // Bọc QR SVG vào một thẻ g transform
        $qrSizePx = $isVertical ? 150 : 130;
        $qrX = (int) ($isVertical ? ($svgW / 2 - $qrSizePx / 2) : ($svgW - $qrSizePx - 24));
        $qrY = (int) ($isVertical ? ($svgH / 2 - 10) : ($svgH / 2 - $qrSizePx / 2 + 5));

        $bg = $palette['bg_color'];
        $border = $palette['border_color'];
        $text = $palette['text_color'];
        $accent = $palette['accent_color'];

        $rectW1 = $svgW - 16;
        $rectH1 = $svgH - 16;
        $rectW2 = $svgW - 24;
        $rectH2 = $svgH - 24;

        $borderInner = <<<SVG
        <rect x="8" y="8" width="{$rectW1}" height="{$rectH1}" fill="none" stroke="{$border}" stroke-width="1.2" stroke-opacity="0.8" />
        <rect x="12" y="12" width="{$rectW2}" height="{$rectH2}" fill="none" stroke="{$border}" stroke-width="0.6" stroke-dasharray="3,2" stroke-opacity="0.6" />
        SVG;

        if ($motif === 'minimal_editorial') {
            $minW1 = $svgW - 12;
            $minH1 = $svgH - 12;
            $minW2 = $svgW - 20;
            $minH2 = $svgH - 20;
            $borderInner = <<<SVG
            <rect x="6" y="6" width="{$minW1}" height="{$minH1}" fill="none" stroke="{$border}" stroke-width="1.5" />
            <rect x="10" y="10" width="{$minW2}" height="{$minH2}" fill="none" stroke="{$border}" stroke-width="0.8" stroke-opacity="0.5" />
            SVG;
        }

        // Layout nội dung theo khổ
        if ($isVertical) {
            // Khổ đứng (Postcard)
            $midX = (int) ($svgW / 2);
            $lineX1 = $midX - 30;
            $lineX2 = $midX + 30;
            $qrBoxW = $qrSizePx + 16;
            $qrBoxH = $qrSizePx + 16;
            $yLabel = $svgH - 65;
            $yDesc = $svgH - 42;
            $yBrand = $svgH - 18;

            $contentSvg = <<<SVG
            <text x="{$midX}" y="45" font-family="'Cormorant Garamond', Georgia, serif" font-size="12" font-weight="600" letter-spacing="3" fill="{$accent}" text-anchor="middle">{$kicker}</text>
            <text x="{$midX}" y="78" font-family="'Cormorant Garamond', Georgia, serif" font-size="24" font-weight="700" fill="{$text}" text-anchor="middle">{$coupleName}</text>
            <line x1="{$lineX1}" y1="92" x2="{$lineX2}" y2="92" stroke="{$border}" stroke-width="1" />
            <text x="{$midX}" y="114" font-family="'Inter', sans-serif" font-size="11" fill="{$text}" text-anchor="middle">{$dateText} • {$venueText}</text>
            
            <g transform="translate({$qrX}, {$qrY})">
                <rect x="-8" y="-8" width="{$qrBoxW}" height="{$qrBoxH}" rx="10" fill="{$palette['qr_bg']}" stroke="{$border}" stroke-width="1" />
                {$qrSvg}
            </g>
            
            <text x="{$midX}" y="{$yLabel}" font-family="'Inter', sans-serif" font-size="11" font-weight="500" fill="{$accent}" text-anchor="middle">{$config['qr_label']}</text>
            <text x="{$midX}" y="{$yDesc}" font-family="'Inter', sans-serif" font-size="9.5" fill="{$text}" opacity="0.8" text-anchor="middle">Quét mã để xem thiệp mời &amp; chỉ đường</text>
            <text x="{$midX}" y="{$yBrand}" font-family="'Inter', sans-serif" font-size="8" letter-spacing="2" fill="{$border}" text-anchor="middle">{$config['footer_brand']}</text>
            SVG;
        } else {
            // Khổ ngang (Namecard 90x54mm hoặc Square 100x100mm)
            $leftW = $svgW - $qrSizePx - 40;
            $qrBoxW = $qrSizePx + 12;
            $qrBoxH = $qrSizePx + 12;
            $qrCenterX = (int) ($qrX + $qrSizePx / 2);
            $qrLabelY = (int) ($qrY + $qrSizePx + 16);
            $yBrand = $svgH - 18;

            $contentSvg = <<<SVG
            <text x="26" y="34" font-family="'Cormorant Garamond', Georgia, serif" font-size="9" font-weight="600" letter-spacing="2.5" fill="{$accent}">{$kicker}</text>
            <text x="26" y="62" font-family="'Cormorant Garamond', Georgia, serif" font-size="19" font-weight="700" fill="{$text}">{$coupleName}</text>
            <line x1="26" y1="72" x2="80" y2="72" stroke="{$border}" stroke-width="0.8" />
            <text x="26" y="92" font-family="'Inter', sans-serif" font-size="9.5" fill="{$text}">{$dateText}  |  {$venueText}</text>
            
            <text x="26" y="125" font-family="'Inter', sans-serif" font-size="8.5" fill="{$text}" opacity="0.85">Xem album ảnh, bản đồ &amp; xác nhận tham dự:</text>
            <text x="26" y="145" font-family="'Inter', sans-serif" font-size="8.5" font-weight="600" fill="{$accent}">🌐 mayhen.vn/{$invitation->slug}</text>
            
            <g transform="translate({$qrX}, {$qrY})">
                <rect x="-6" y="-6" width="{$qrBoxW}" height="{$qrBoxH}" rx="8" fill="{$palette['qr_bg']}" stroke="{$border}" stroke-width="0.8" />
                {$qrSvg}
            </g>
            <text x="{$qrCenterX}" y="{$qrLabelY}" font-family="'Inter', sans-serif" font-size="8" font-weight="600" fill="{$accent}" text-anchor="middle">{$config['qr_label']}</text>
            
            <text x="26" y="{$yBrand}" font-family="'Inter', sans-serif" font-size="7.5" letter-spacing="1.5" fill="{$border}">{$config['footer_brand']}</text>
            SVG;
        }

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$svgW} {$svgH}" width="{$svgW}" height="{$svgH}">
            <defs>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&amp;family=Inter:wght@400;500;600&amp;display=swap');
                </style>
            </defs>
            <rect width="{$svgW}" height="{$svgH}" fill="{$bg}" />
            {$borderInner}
            {$contentSvg}
        </svg>
        SVG;
    }

    /**
     * Lấy danh sách khách mời để in ấn hàng loạt thẻ kèm tên khách.
     */
    public static function getGuestsForPrinting(Invitation $invitation, int $limit = 100): Collection
    {
        return Guest::where('wedding_id', $invitation->wedding_id)
            ->orderBy('group_name')
            ->orderBy('name')
            ->take($limit)
            ->get();
    }
}
