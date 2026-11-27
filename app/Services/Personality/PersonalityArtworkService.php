<?php

declare(strict_types=1);

namespace App\Services\Personality;

/**
 * The Art of Personality — 16 Portraits Fine Art SVG Sculpture Generator.
 *
 * - Tạo 16 tác phẩm biểu trưng nghệ thuật độc quyền của Mây Hẹn (Cẩm thạch, Đồng cổ, Ngọc, Hoa sen, La bàn, Lăng kính...)
 * - Không sao chép nhân vật low-poly của 16Personalities, không gắn giới tính hay chủng tộc.
 * - Hiển thị sắc nét ở cả chế độ 2D Editorial và lớp tương tác 3D Tilt/Orbit (không cần tải 16 file GLB nặng khi làm bài).
 */
class PersonalityArtworkService
{
    public function __construct(
        protected PersonalityTypeRegistry $typeRegistry
    ) {}

    public function renderSvg(string $typeCode, int $size = 240): string
    {
        $type = $this->typeRegistry->getType($typeCode);
        $code = $type ? $type['code'] : 'UNDECIDED';
        $primary = $type['palette']['primary'] ?? '#1E4D3B';
        $accent = $type['palette']['accent'] ?? '#CFA860';
        $surface = $type['palette']['surface'] ?? '#FAF5EB';
        $key = $type['sculpture_key'] ?? 'default_emblem';

        $inner = match ($key) {
            'architect_monolith' => '<rect x="64" y="42" width="52" height="156" rx="2" fill="'.$primary.'" opacity="0.92"/><rect x="108" y="74" width="68" height="124" rx="2" fill="none" stroke="'.$accent.'" stroke-width="2.5"/><line x1="50" y1="198" x2="190" y2="198" stroke="'.$primary.'" stroke-width="2"/><polygon points="108,74 142,46 176,74" fill="'.$accent.'" opacity="0.28"/>',
            'prism_crystal' => '<polygon points="120,34 184,174 56,174" fill="none" stroke="'.$primary.'" stroke-width="2.4"/><polygon points="120,34 150,174 120,196 90,174" fill="'.$accent.'" opacity="0.25"/><line x1="36" y1="112" x2="96" y2="112" stroke="'.$accent.'" stroke-width="2"/><line x1="144" y1="112" x2="204" y2="92" stroke="'.$primary.'" stroke-width="1.8"/><line x1="144" y1="112" x2="204" y2="128" stroke="'.$accent.'" stroke-width="1.8"/>',
            'brass_compass' => '<circle cx="120" cy="120" r="72" fill="none" stroke="'.$primary.'" stroke-width="2.2"/><circle cx="120" cy="120" r="56" fill="none" stroke="'.$accent.'" stroke-width="1.5" stroke-dasharray="4 5"/><polygon points="120,42 132,120 120,198 108,120" fill="'.$primary.'" opacity="0.88"/><polygon points="42,120 120,108 198,120 120,132" fill="'.$accent.'" opacity="0.65"/><circle cx="120" cy="120" r="7" fill="'.$surface.'" stroke="'.$primary.'" stroke-width="2"/>',
            'kinetic_sculpture' => '<ellipse cx="120" cy="120" rx="74" ry="28" fill="none" stroke="'.$primary.'" stroke-width="2" transform="rotate(-24 120 120)"/><ellipse cx="120" cy="120" rx="74" ry="28" fill="none" stroke="'.$accent.'" stroke-width="2" transform="rotate(34 120 120)"/><circle cx="120" cy="120" r="24" fill="'.$primary.'" opacity="0.16" stroke="'.$primary.'" stroke-width="1.8"/><circle cx="164" cy="96" r="7" fill="'.$accent.'"/><circle cx="78" cy="144" r="6" fill="'.$primary.'"/>',
            'lotus_halo' => '<circle cx="120" cy="112" r="68" fill="none" stroke="'.$accent.'" stroke-width="1.6" stroke-dasharray="3 5"/><path d="M120 56 C142 92 142 138 120 166 C98 138 98 92 120 56 Z" fill="'.$primary.'" opacity="0.85"/><path d="M120 74 C158 96 174 134 148 168 C132 146 126 116 120 74 Z" fill="'.$accent.'" opacity="0.35" stroke="'.$primary.'" stroke-width="1.4"/><path d="M120 74 C82 96 66 134 92 168 C108 146 114 116 120 74 Z" fill="'.$accent.'" opacity="0.35" stroke="'.$primary.'" stroke-width="1.4"/>',
            'book_petals' => '<path d="M46 164 Q83 144 120 164 Q157 144 194 164 L194 176 Q157 156 120 176 Q83 156 46 176 Z" fill="'.$primary.'" opacity="0.88"/><path d="M120 164 L120 82" stroke="'.$accent.'" stroke-width="1.6" stroke-dasharray="3 4"/><path d="M120 78 C134 62 152 68 142 86 C132 100 120 96 120 78 Z" fill="'.$accent.'" opacity="0.72"/><path d="M98 104 C82 92 78 110 92 118 C104 124 108 112 98 104 Z" fill="'.$primary.'" opacity="0.45"/>',
            'interlocking_rings' => '<circle cx="96" cy="116" r="46" fill="none" stroke="'.$primary.'" stroke-width="2.4"/><circle cx="144" cy="116" r="46" fill="none" stroke="'.$accent.'" stroke-width="2.4"/><circle cx="120" cy="146" r="34" fill="none" stroke="'.$primary.'" stroke-width="1.6" opacity="0.65"/><circle cx="120" cy="116" r="8" fill="'.$accent.'"/>',
            'blooming_petals' => '<circle cx="120" cy="120" r="14" fill="'.$accent.'"/><g fill="none" stroke="'.$primary.'" stroke-width="2"><path d="M120 44 C140 72 140 94 120 106 C100 94 100 72 120 44 Z"/><path d="M120 196 C140 168 140 146 120 134 C100 146 100 168 120 196 Z"/><path d="M44 120 C72 100 94 100 106 120 C94 140 72 140 44 120 Z"/><path d="M196 120 C168 100 146 100 134 120 C146 140 168 140 196 120 Z"/></g><circle cx="120" cy="120" r="64" fill="none" stroke="'.$accent.'" stroke-width="1.2" stroke-dasharray="2 6"/>',
            'stone_chronometer' => '<rect x="68" y="48" width="104" height="144" rx="52" fill="none" stroke="'.$primary.'" stroke-width="2.4"/><circle cx="120" cy="112" r="36" fill="'.$accent.'" opacity="0.2" stroke="'.$accent.'" stroke-width="2"/><line x1="120" y1="112" x2="120" y2="86" stroke="'.$primary.'" stroke-width="2.4" stroke-linecap="round"/><line x1="120" y1="112" x2="140" y2="122" stroke="'.$primary.'" stroke-width="2.2" stroke-linecap="round"/><line x1="76" y1="176" x2="164" y2="176" stroke="'.$primary.'" stroke-width="2"/>',
            'keepsake_vessel' => '<rect x="58" y="92" width="124" height="84" rx="10" fill="'.$primary.'" opacity="0.14" stroke="'.$primary.'" stroke-width="2.2"/><path d="M52 92 L188 92 L176 68 L64 68 Z" fill="'.$primary.'" opacity="0.88"/><circle cx="120" cy="134" r="14" fill="none" stroke="'.$accent.'" stroke-width="2.4"/><line x1="120" y1="92" x2="120" y2="120" stroke="'.$accent.'" stroke-width="2"/>',
            'classical_arch' => '<path d="M58 186 L58 106 A62 62 0 0 1 182 106 L182 186" fill="none" stroke="'.$primary.'" stroke-width="2.6"/><path d="M82 186 L82 112 A38 38 0 0 1 158 112 L158 186" fill="'.$accent.'" opacity="0.22" stroke="'.$accent.'" stroke-width="2"/><line x1="46" y1="186" x2="194" y2="186" stroke="'.$primary.'" stroke-width="2.6"/>',
            'botanical_wreath' => '<circle cx="120" cy="120" r="62" fill="none" stroke="'.$primary.'" stroke-width="2"/><circle cx="120" cy="120" r="50" fill="none" stroke="'.$accent.'" stroke-width="1.6" stroke-dasharray="8 6"/><circle cx="120" cy="58" r="7" fill="'.$accent.'"/><circle cx="182" cy="120" r="7" fill="'.$primary.'"/><circle cx="120" cy="182" r="7" fill="'.$accent.'"/><circle cx="58" cy="120" r="7" fill="'.$primary.'"/>',
            'precision_gear' => '<circle cx="120" cy="120" r="58" fill="none" stroke="'.$primary.'" stroke-width="2.4" stroke-dasharray="14 8"/><circle cx="120" cy="120" r="36" fill="none" stroke="'.$accent.'" stroke-width="2.2"/><circle cx="120" cy="120" r="12" fill="'.$primary.'"/><line x1="120" y1="54" x2="120" y2="84" stroke="'.$primary.'" stroke-width="2"/><line x1="120" y1="156" x2="120" y2="186" stroke="'.$primary.'" stroke-width="2"/>',
            'silk_ribbon' => '<path d="M46 154 C76 74 114 74 122 122 C130 168 168 164 194 86" fill="none" stroke="'.$primary.'" stroke-width="3.2" stroke-linecap="round"/><path d="M52 172 C84 96 120 96 130 136 C140 176 174 166 196 106" fill="none" stroke="'.$accent.'" stroke-width="2.2" stroke-linecap="round"/><circle cx="120" cy="120" r="66" fill="none" stroke="'.$primary.'" stroke-width="1" opacity="0.25"/>',
            'dynamic_arc' => '<path d="M48 166 Q104 54 192 78" fill="none" stroke="'.$primary.'" stroke-width="3" stroke-linecap="round"/><path d="M58 184 Q122 86 196 112" fill="none" stroke="'.$accent.'" stroke-width="2.2" stroke-linecap="round"/><polygon points="192,78 176,72 182,88" fill="'.$primary.'"/>',
            'radiant_prism' => '<circle cx="120" cy="120" r="30" fill="'.$accent.'" opacity="0.32" stroke="'.$primary.'" stroke-width="2.2"/><g stroke="'.$primary.'" stroke-width="1.8" stroke-linecap="round"><line x1="120" y1="44" x2="120" y2="74"/><line x1="120" y1="166" x2="120" y2="196"/><line x1="44" y1="120" x2="74" y2="120"/><line x1="166" y1="120" x2="196" y2="120"/><line x1="66" y1="66" x2="88" y2="88"/><line x1="152" y1="152" x2="174" y2="174"/><line x1="174" y1="66" x2="152" y2="88"/><line x1="88" y1="152" x2="66" y2="174"/></g>',
            default => '<circle cx="120" cy="120" r="64" fill="none" stroke="'.$primary.'" stroke-width="2"/><circle cx="120" cy="120" r="38" fill="none" stroke="'.$accent.'" stroke-width="2" stroke-dasharray="5 5"/><circle cx="120" cy="120" r="10" fill="'.$primary.'"/>',
        };

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240" width="'.$size.'" height="'.$size.'" role="img" aria-label="Hình tượng nghệ thuật '.$code.'">'
            .'<rect width="240" height="240" rx="20" fill="'.$surface.'"/>'
            .'<rect x="10" y="10" width="220" height="220" rx="14" fill="none" stroke="'.$accent.'" stroke-opacity="0.35" stroke-width="1"/>'
            .$inner
            .'</svg>';
    }
}
