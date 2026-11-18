<?php

namespace App\Services;

class StudioDecorationLibraryService
{
    /**
     * Danh mục Sticker SVG nghệ thuật lưu cục bộ (hỗ trợ đổi màu stroke/fill qua `currentColor`).
     *
     * @return array<int, array{id: string, label: string, category: string, default_color: string, aspect: float, svg: string}>
     */
    public static function stickers(): array
    {
        return [
            [
                'id' => 'wax-seal-champagne',
                'label' => 'Dấu Sáp Hoàng Gia',
                'category' => 'wax_seal',
                'default_color' => '#9E2A3B',
                'aspect' => 1.0,
                'svg' => '<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="60" cy="60" r="52" fill="currentColor" opacity="0.92"/><circle cx="60" cy="60" r="42" stroke="#F8EFE2" stroke-width="2" stroke-dasharray="4 3"/><path d="M60 38C53 30 39 34 39 46C39 59 60 72 60 80C60 72 81 59 81 46C81 34 67 30 60 38Z" fill="#F8EFE2" opacity="0.9"/></svg>',
            ],
            [
                'id' => 'wedding-rings-gold',
                'label' => 'Nhẫn Cưới Giao Duyên',
                'category' => 'symbol',
                'default_color' => '#C5A059',
                'aspect' => 1.35,
                'svg' => '<svg viewBox="0 0 140 104" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="52" cy="58" r="28" stroke="currentColor" stroke-width="4.5"/><circle cx="88" cy="58" r="28" stroke="currentColor" stroke-width="4.5"/><path d="M88 22L95 30L88 38L81 30L88 22Z" fill="currentColor"/></svg>',
            ],
            [
                'id' => 'botanical-olive',
                'label' => 'Nhành Ô Liu Bình Yên',
                'category' => 'botanical',
                'default_color' => '#5C735B',
                'aspect' => 1.6,
                'svg' => '<svg viewBox="0 0 160 100" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 78C56 64 98 44 142 20" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><ellipse cx="48" cy="52" rx="14" ry="6" transform="rotate(-32 48 52)" fill="currentColor" opacity="0.82"/><ellipse cx="66" cy="66" rx="14" ry="6" transform="rotate(18 66 66)" fill="currentColor" opacity="0.82"/><ellipse cx="86" cy="36" rx="14" ry="6" transform="rotate(-28 86 36)" fill="currentColor" opacity="0.82"/><ellipse cx="104" cy="48" rx="14" ry="6" transform="rotate(20 104 48)" fill="currentColor" opacity="0.82"/><ellipse cx="124" cy="22" rx="12" ry="5" transform="rotate(-24 124 22)" fill="currentColor" opacity="0.82"/></svg>',
            ],
            [
                'id' => 'double-happiness-royal',
                'label' => 'Song Hỷ Truyền Thống',
                'category' => 'traditional',
                'default_color' => '#9E1B2A',
                'aspect' => 1.0,
                'svg' => '<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="60" cy="60" r="54" stroke="currentColor" stroke-width="3"/><path d="M26 36H54M40 24V56M28 48H52M30 62H50V74H30V62ZM24 82H96M30 90H50V102H30V90ZM66 36H94M80 24V56M68 48H92M70 62H90V74H70V62ZM70 90H90V102H70V90Z" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            ],
            [
                'id' => 'celestial-starburst',
                'label' => 'Ánh Sao Bắc Đẩu',
                'category' => 'celestial',
                'default_color' => '#C5A059',
                'aspect' => 1.0,
                'svg' => '<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M60 10L68 48L106 60L68 72L60 110L52 72L14 60L52 48L60 10Z" fill="currentColor"/><circle cx="28" cy="28" r="3.5" fill="currentColor"/><circle cx="92" cy="32" r="2.8" fill="currentColor"/><circle cx="90" cy="92" r="3.2" fill="currentColor"/></svg>',
            ],
            [
                'id' => 'silk-ribbon-bow',
                'label' => 'Nơ Lụa Tinh Khôi',
                'category' => 'ribbon',
                'default_color' => '#B77B82',
                'aspect' => 1.4,
                'svg' => '<svg viewBox="0 0 140 100" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M70 46C52 22 20 20 20 42C20 62 50 58 70 46ZM70 46C88 22 120 20 120 42C120 62 90 58 70 46ZM70 46L44 88M70 46L96 88" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="70" cy="46" r="7" fill="currentColor"/></svg>',
            ],
            [
                'id' => 'love-heart-minimal',
                'label' => 'Trái Tim Nét Mảnh',
                'category' => 'symbol',
                'default_color' => '#A63A4B',
                'aspect' => 1.1,
                'svg' => '<svg viewBox="0 0 120 110" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M60 94C60 94 18 66 18 38C18 23 30 14 43 14C51 14 57 19 60 25C63 19 69 14 77 14C90 14 102 23 102 38C102 66 60 94 60 94Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/></svg>',
            ],
            [
                'id' => 'vintage-corner-flourish',
                'label' => 'Hoa Văn Góc Cổ Điển',
                'category' => 'ornament',
                'default_color' => '#C5A059',
                'aspect' => 1.0,
                'svg' => '<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 104V28C16 21.3726 21.3726 16 28 16H104" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M28 84V34C28 30.6863 30.6863 28 34 28H84" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" opacity="0.75"/><circle cx="28" cy="28" r="5" fill="currentColor"/></svg>',
            ],
            [
                'id' => 'dove-pair',
                'label' => 'Đôi Chim Bồ Câu',
                'category' => 'symbol',
                'default_color' => '#8A6F4D',
                'aspect' => 1.5,
                'svg' => '<svg viewBox="0 0 150 100" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M58 58C44 58 30 48 22 30C36 32 46 38 52 44C50 32 54 22 64 18C64 30 68 42 68 50C68 56 64 58 58 58Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/><path d="M92 58C106 58 120 48 128 30C114 32 104 38 98 44C100 32 96 22 86 18C86 30 82 42 82 50C82 56 86 58 92 58Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/></svg>',
            ],
            [
                'id' => 'divider-floral-ornate',
                'label' => 'Đường Ngăn Hoa Lá',
                'category' => 'divider',
                'default_color' => '#C5A059',
                'aspect' => 4.2,
                'svg' => '<svg viewBox="0 0 240 56" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 28H92M148 28H228" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M120 12L130 28L120 44L110 28L120 12Z" fill="currentColor"/><circle cx="100" cy="28" r="3" fill="currentColor"/><circle cx="140" cy="28" r="3" fill="currentColor"/></svg>',
            ],
        ];
    }

    /**
     * Danh mục Hình dạng cơ bản (`overlay_shape`).
     *
     * @return array<int, array{id: string, label: string, fill: string, stroke: string, radius: string, aspect: float}>
     */
    public static function shapes(): array
    {
        return [
            ['id' => 'pill-badge', 'label' => 'Nhãn Viên Thuốc', 'fill' => 'rgba(255,253,249,0.88)', 'stroke' => '#C5A059', 'radius' => '999px', 'aspect' => 2.8],
            ['id' => 'arch-dome', 'label' => 'Vòm Cung Nghệ Thuật', 'fill' => 'rgba(248,242,232,0.82)', 'stroke' => '#C5A059', 'radius' => '999px 999px 12px 12px', 'aspect' => 0.75],
            ['id' => 'circle-medallion', 'label' => 'Huy Chương Tròn', 'fill' => 'rgba(255,253,249,0.85)', 'stroke' => '#C5A059', 'radius' => '50%', 'aspect' => 1.0],
            ['id' => 'rect-editorial', 'label' => 'Khung Chữ Nhật Mảnh', 'fill' => 'rgba(255,253,249,0.78)', 'stroke' => '#8A6F4D', 'radius' => '12px', 'aspect' => 1.5],
            ['id' => 'line-gold', 'label' => 'Đường Kẻ Vàng Kim', 'fill' => '#C5A059', 'stroke' => '#C5A059', 'radius' => '999px', 'aspect' => 12.0],
        ];
    }

    /**
     * Danh mục Khung ảnh trang trí (`overlay_frame` — tối thiểu 8 khung theo Master Prompt).
     *
     * @return array<int, array{id: string, label: string, css_modifier: string, aspect_ratio: string, aspect: float, default_stroke: string}>
     */
    public static function frames(): array
    {
        return [
            ['id' => 'frame-arch', 'label' => 'Khung Vòm Cổ Điển', 'css_modifier' => 'arch', 'aspect_ratio' => '3:4', 'aspect' => 0.75, 'default_stroke' => '#C5A059'],
            ['id' => 'frame-oval', 'label' => 'Khung Oval Quý Phái', 'css_modifier' => 'oval', 'aspect_ratio' => '3:4', 'aspect' => 0.78, 'default_stroke' => '#C5A059'],
            ['id' => 'frame-circle', 'label' => 'Khung Tròn Tinh Tế', 'css_modifier' => 'circle', 'aspect_ratio' => '1:1', 'aspect' => 1.0, 'default_stroke' => '#C5A059'],
            ['id' => 'frame-polaroid', 'label' => 'Khung Ảnh Polaroid', 'css_modifier' => 'polaroid', 'aspect_ratio' => '4:5', 'aspect' => 0.8, 'default_stroke' => '#E8DFD3'],
            ['id' => 'frame-royal-gold', 'label' => 'Khung Viền Đôi Hoàng Gia', 'css_modifier' => 'royal-gold', 'aspect_ratio' => '3:4', 'aspect' => 0.75, 'default_stroke' => '#C5A059'],
            ['id' => 'frame-scallop', 'label' => 'KhungTem Thư Cổ Điển', 'css_modifier' => 'scallop', 'aspect_ratio' => '4:5', 'aspect' => 0.8, 'default_stroke' => '#B77B82'],
            ['id' => 'frame-film-strip', 'label' => 'Khung Thước Phim Điện Ảnh', 'css_modifier' => 'film-strip', 'aspect_ratio' => '4:3', 'aspect' => 1.33, 'default_stroke' => '#1F1C1A'],
            ['id' => 'frame-soft-rect', 'label' => 'Khung Bo Góc Hiện Đại', 'css_modifier' => 'soft-rect', 'aspect_ratio' => '3:4', 'aspect' => 0.75, 'default_stroke' => '#D8C8B4'],
        ];
    }

    /**
     * Danh mục Hiệu ứng chuyển động GPU-only (`Motion Registry`).
     *
     * @return array<int, array{id: string, label: string, group: string, default_duration_ms: int, default_repeat: string}>
     */
    public static function motionPresets(): array
    {
        return [
            ['id' => 'none', 'label' => 'Không hiệu ứng', 'group' => 'none', 'default_duration_ms' => 0, 'default_repeat' => 'once'],
            ['id' => 'fade-in', 'label' => 'Hiện Dịu (Fade In)', 'group' => 'entrance', 'default_duration_ms' => 750, 'default_repeat' => 'once'],
            ['id' => 'soft-rise', 'label' => 'Trồi Nhẹ Lên (Soft Rise)', 'group' => 'entrance', 'default_duration_ms' => 800, 'default_repeat' => 'once'],
            ['id' => 'slide-left', 'label' => 'Lướt Từ Phải Sang', 'group' => 'entrance', 'default_duration_ms' => 800, 'default_repeat' => 'once'],
            ['id' => 'slide-right', 'label' => 'Lướt Từ Trái Sang', 'group' => 'entrance', 'default_duration_ms' => 800, 'default_repeat' => 'once'],
            ['id' => 'gentle-zoom', 'label' => 'Phóng Nhẹ Tinh Tế', 'group' => 'entrance', 'default_duration_ms' => 850, 'default_repeat' => 'once'],
            ['id' => 'mask-reveal', 'label' => 'Mở Màn Lụa (Mask Reveal)', 'group' => 'entrance', 'default_duration_ms' => 950, 'default_repeat' => 'once'],
            ['id' => 'soft-pulse', 'label' => 'Nhịp Thở Dịu (Ambient)', 'group' => 'ambient', 'default_duration_ms' => 2400, 'default_repeat' => 'infinite'],
            ['id' => 'slow-float', 'label' => 'Bồng Bềnh Mây Nhẹ', 'group' => 'ambient', 'default_duration_ms' => 3000, 'default_repeat' => 'infinite'],
            ['id' => 'tiny-sway', 'label' => 'Đung Đưa Nhành Hoa', 'group' => 'ambient', 'default_duration_ms' => 2800, 'default_repeat' => 'infinite'],
        ];
    }

    /**
     * Gói cấu hình tĩnh xuất sang trình duyệt cho Studio V3 (0 truy vấn DB).
     *
     * @return array<string, mixed>
     */
    public static function frontendCatalog(): array
    {
        return [
            'fonts' => InvitationFontRegistryService::all(),
            'stickers' => self::stickers(),
            'shapes' => self::shapes(),
            'frames' => self::frames(),
            'motions' => self::motionPresets(),
        ];
    }

    public static function isValidStickerId(?string $id): bool
    {
        if (! is_string($id) || $id === '') {
            return false;
        }

        foreach (self::stickers() as $item) {
            if ($item['id'] === $id) {
                return true;
            }
        }

        return false;
    }

    public static function isValidShapeId(?string $id): bool
    {
        if (! is_string($id) || $id === '') {
            return false;
        }

        foreach (self::shapes() as $item) {
            if ($item['id'] === $id) {
                return true;
            }
        }

        return false;
    }

    public static function isValidFrameId(?string $id): bool
    {
        if (! is_string($id) || $id === '') {
            return false;
        }

        foreach (self::frames() as $item) {
            if ($item['id'] === $id) {
                return true;
            }
        }

        return false;
    }

    public static function isValidMotionPreset(?string $id): bool
    {
        if (! is_string($id) || $id === '') {
            return false;
        }

        foreach (self::motionPresets() as $item) {
            if ($item['id'] === $id) {
                return true;
            }
        }

        return false;
    }
}
