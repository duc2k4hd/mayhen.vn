<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Support\Str;

class WeddingWaxSealService
{
    /**
     * Danh mục 6 bảng màu sáp hoàng gia Quiet Luxury.
     *
     * @return array<string, array{name: string, primary_color: string, shadow_color: string, gold_foil: bool, description: string}>
     */
    public static function getColorPalettes(): array
    {
        return [
            'burgundy' => [
                'name' => 'Đỏ Rượu Bordeaux (Burgundy)',
                'primary_color' => '#8C1D24',
                'shadow_color' => '#560E13',
                'gold_foil' => true,
                'description' => 'Màu đỏ nhung trầm quý phái truyền thống của hoàng gia châu Âu, tượng trưng cho tình yêu nồng cháy và trường tồn.',
            ],
            'royal_gold' => [
                'name' => 'Vàng Kim Hoàng Gia (Royal Gold)',
                'primary_color' => '#D4AF37',
                'shadow_color' => '#8C6D15',
                'gold_foil' => false,
                'description' => 'Ánh kim vàng 24K lấp lánh dập nổi, biểu trưng cho sự thịnh vượng, viên mãn và trang trọng tuyệt đối.',
            ],
            'sage_green' => [
                'name' => 'Xanh Rêu Nguyệt Quế (Sage Green)',
                'primary_color' => '#586E5F',
                'shadow_color' => '#37453B',
                'gold_foil' => true,
                'description' => 'Màu xanh thảo mộc tự nhiên mang hơi thở Bohemian thanh bình, tượng trưng cho khởi đầu tươi mới và bình yên.',
            ],
            'dusty_rose' => [
                'name' => 'Hồng Tro Thơ Mộng (Dusty Rose)',
                'primary_color' => '#B76E79',
                'shadow_color' => '#7A4048',
                'gold_foil' => true,
                'description' => 'Tông hồng tro pastel thanh lịch, ấm áp và lãng mạn như những cánh hoa hồng sớm mai.',
            ],
            'midnight_navy' => [
                'name' => 'Xanh Navy Cung Đình (Midnight Navy)',
                'primary_color' => '#1B2A4A',
                'shadow_color' => '#0D172B',
                'gold_foil' => true,
                'description' => 'Màu xanh bóng đêm thâm trầm, mang chiều sâu sang trọng và phong thái vương giả kín đáo.',
            ],
            'pearl_white' => [
                'name' => 'Trắng Ngọc Trai (Pearl Ivory)',
                'primary_color' => '#F4EFE6',
                'shadow_color' => '#C4B9A7',
                'gold_foil' => true,
                'description' => 'Sắc trắng ngà tinh khôi tựa ngọc trai, hiện thân của tình yêu thuần khiết và thanh tao.',
            ],
        ];
    }

    /**
     * Danh mục 6 mẫu biểu tượng dập nổi trung tâm con dấu sáp.
     *
     * @return array<string, array{name: string, icon_type: string, description: string}>
     */
    public static function getSealEmblems(): array
    {
        return [
            'monogram' => [
                'name' => 'Monogram 2 Chữ Cái Đầu (Initials)',
                'icon_type' => 'text',
                'description' => 'Khắc 2 chữ cái viết tắt tên Cô Dâu & Chú Rể đan lồng nghệ thuật (Ví dụ: T & N).',
            ],
            'double_happiness' => [
                'name' => 'Chữ Song Hỷ Hoàng Gia (囍)',
                'icon_type' => 'symbol',
                'description' => 'Biểu tượng Song Hỷ truyền thống Á Đông được cách điệu theo phong cách Quiet Luxury tối giản.',
            ],
            'olive_branch' => [
                'name' => 'Cành Lá Nguyệt Quế (Olive Wreath)',
                'icon_type' => 'svg',
                'description' => 'Vòng lá nguyệt quế Hy Lạp biểu trưng cho sự hòa thuận, phước lành và viên mãn.',
            ],
            'intertwined_rings' => [
                'name' => 'Cặp Nhẫn Cưới Đính Ước (Wedding Rings)',
                'icon_type' => 'svg',
                'description' => 'Hai chiếc nhẫn cưới lồng vào nhau, tượng trưng cho lời thề nguyền gắn kết trọn đời.',
            ],
            'love_birds' => [
                'name' => 'Đôi Chim Uyên Ương (Love Birds)',
                'icon_type' => 'svg',
                'description' => 'Hình ảnh đôi chim liền cánh bay về tổ ấm, biểu tượng vĩnh cửu của tình phu thê.',
            ],
            'hydrangea_flower' => [
                'name' => 'B đóa Hoa Cẩm Tú Cầu (Hydrangea)',
                'icon_type' => 'svg',
                'description' => 'Đóa cẩm tú cầu dịu dàng tượng trưng cho lòng biết ơn và những cảm xúc chân thành.',
            ],
        ];
    }

    /**
     * Danh mục 3 kiểu viền sáp chảy.
     *
     * @return array<string, array{name: string, description: string}>
     */
    public static function getRimStyles(): array
    {
        return [
            'vintage_deckled' => [
                'name' => 'Viền Sáp Chảy Tự Nhiên (Vintage Organic)',
                'description' => 'Các giọt sáp tràn ngẫu nhiên không đối xứng, tạo cảm giác niêm phong thủ công tỉ mỉ.',
            ],
            'royal_beaded' => [
                'name' => 'Viền Hạt Cườm Hoàng Tộc (Royal Beaded)',
                'description' => 'Viền tròn đính hạt cườm ngọc trai dập nổi đều đặn, phong cách cung đình cổ điển.',
            ],
            'minimal_smooth' => [
                'name' => 'Viền Tròn Tối Giản (Modern Minimalist)',
                'description' => 'Đường viền tròn mịn màng, dứt khoát, tôn vinh họa tiết trung tâm thanh lịch.',
            ],
        ];
    }

    /**
     * Phân giải cấu hình con dấu sáp hoàn chỉnh cho thiệp cưới.
     *
     * @return array{
     *     color_palette: string,
     *     primary_color: string,
     *     shadow_color: string,
     *     emblem: string,
     *     rim_style: string,
     *     monogram_text: string,
     *     crack_sound: bool,
     *     shimmer_effect: bool,
     *     motto_text: string
     * }
     */
    public static function resolveWaxSealConfig(?Invitation $invitation = null, ?array $customConfig = null): array
    {
        $waxData = [];

        if ($invitation && is_array($invitation->custom_config) && isset($invitation->custom_config['wax_seal'])) {
            $waxData = $invitation->custom_config['wax_seal'];
        } elseif (is_array($customConfig) && isset($customConfig['wax_seal'])) {
            $waxData = $customConfig['wax_seal'];
        }

        $palettes = self::getColorPalettes();
        $selectedPaletteKey = isset($waxData['color_palette']) && array_key_exists($waxData['color_palette'], $palettes)
            ? $waxData['color_palette']
            : 'burgundy';
        $palette = $palettes[$selectedPaletteKey];

        $emblems = self::getSealEmblems();
        $selectedEmblemKey = isset($waxData['emblem']) && array_key_exists($waxData['emblem'], $emblems)
            ? $waxData['emblem']
            : 'monogram';

        $rimStyles = self::getRimStyles();
        $selectedRimKey = isset($waxData['rim_style']) && array_key_exists($waxData['rim_style'], $rimStyles)
            ? $waxData['rim_style']
            : 'vintage_deckled';

        // Tạo monogram mặc định từ tên chú rể & cô dâu nếu chưa có
        $defaultMonogram = 'M & H';
        if ($invitation) {
            $groomFirst = mb_strtoupper(mb_substr($invitation->wedding->groom_name ?? $invitation->groom_name ?? 'M', 0, 1));
            $brideFirst = mb_strtoupper(mb_substr($invitation->wedding->bride_name ?? $invitation->bride_name ?? 'H', 0, 1));
            if (! empty($groomFirst) && ! empty($brideFirst)) {
                $defaultMonogram = $groomFirst.' & '.$brideFirst;
            }
        }

        $monogramText = ! empty(trim($waxData['monogram_text'] ?? ''))
            ? Str::limit(trim($waxData['monogram_text']), 7, '')
            : $defaultMonogram;

        $mottoText = ! empty(trim($waxData['motto_text'] ?? ''))
            ? Str::limit(trim($waxData['motto_text']), 16, '')
            : 'MAYHEN ATELIER';

        return [
            'color_palette' => $selectedPaletteKey,
            'primary_color' => $palette['primary_color'],
            'shadow_color' => $palette['shadow_color'],
            'emblem' => $selectedEmblemKey,
            'rim_style' => $selectedRimKey,
            'monogram_text' => $monogramText,
            'motto_text' => $mottoText,
            'crack_sound' => array_key_exists('crack_sound', $waxData) ? (bool) $waxData['crack_sound'] : true,
            'shimmer_effect' => array_key_exists('shimmer_effect', $waxData) ? (bool) $waxData['shimmer_effect'] : true,
        ];
    }

    /**
     * Cập nhật cấu hình con dấu sáp hoàng gia.
     */
    public static function updateWaxSeal(Invitation $invitation, array $data): array
    {
        $current = self::resolveWaxSealConfig($invitation);

        $palettes = self::getColorPalettes();
        $colorPalette = isset($data['color_palette']) && array_key_exists($data['color_palette'], $palettes)
            ? $data['color_palette']
            : $current['color_palette'];

        $emblems = self::getSealEmblems();
        $emblem = isset($data['emblem']) && array_key_exists($data['emblem'], $emblems)
            ? $data['emblem']
            : $current['emblem'];

        $rimStyles = self::getRimStyles();
        $rimStyle = isset($data['rim_style']) && array_key_exists($data['rim_style'], $rimStyles)
            ? $data['rim_style']
            : $current['rim_style'];

        $monogramText = isset($data['monogram_text']) && ! empty(trim($data['monogram_text']))
            ? Str::limit(trim($data['monogram_text']), 7, '')
            : $current['monogram_text'];

        $mottoText = isset($data['motto_text']) && ! empty(trim($data['motto_text']))
            ? Str::limit(trim($data['motto_text']), 16, '')
            : $current['motto_text'];

        $crackSound = array_key_exists('crack_sound', $data)
            ? filter_var($data['crack_sound'], FILTER_VALIDATE_BOOLEAN)
            : $current['crack_sound'];

        $shimmerEffect = array_key_exists('shimmer_effect', $data)
            ? filter_var($data['shimmer_effect'], FILTER_VALIDATE_BOOLEAN)
            : $current['shimmer_effect'];

        $updated = [
            'color_palette' => $colorPalette,
            'emblem' => $emblem,
            'rim_style' => $rimStyle,
            'monogram_text' => $monogramText,
            'motto_text' => $mottoText,
            'crack_sound' => $crackSound,
            'shimmer_effect' => $shimmerEffect,
        ];

        $customConfig = is_array($invitation->custom_config) ? $invitation->custom_config : [];
        $customConfig['wax_seal'] = $updated;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        AuditLogger::log(
            'INVITATION_WAX_SEAL_UPDATE',
            'Invitation',
            $invitation->id,
            "Cập nhật con dấu sáp hoàng gia: [{$colorPalette}] Emblem: {$emblem} - Monogram: {$monogramText}",
            $updated
        );

        return self::resolveWaxSealConfig($invitation);
    }
}

