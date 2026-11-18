<?php

namespace App\Services;

class InvitationFontRegistryService
{
    public const SAMPLE_TEXT = 'Trăm năm hạnh phúc — Huyền Trang & Thành Đạt';

    /**
     * Danh mục font tiếng Việt lưu trữ 100% cục bộ (không phụ thuộc Google Fonts CDN).
     *
     * @var array<string, array{
     *     id: string,
     *     label: string,
     *     category: string,
     *     family: string,
     *     stack: string,
     *     weights: array<int, int>,
     *     supports_italic: bool,
     *     vietnamese: bool,
     *     sample: string
     * }>
     */
    private const FONTS = [
        'editorial-serif' => [
            'id' => 'editorial-serif',
            'label' => 'Cormorant Thanh Lịch',
            'category' => 'serif',
            'family' => 'MayhenV3EditorialSerif',
            'stack' => "'MayhenV3EditorialSerif', Georgia, 'Times New Roman', serif",
            'weights' => [400, 700],
            'supports_italic' => false,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
        'royal-serif' => [
            'id' => 'royal-serif',
            'label' => 'Hoàng Gia Cổ Điển',
            'category' => 'serif',
            'family' => 'MayhenV3RoyalSerif',
            'stack' => "'MayhenV3RoyalSerif', Georgia, 'Times New Roman', serif",
            'weights' => [400, 700],
            'supports_italic' => true,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
        'classic-display' => [
            'id' => 'classic-display',
            'label' => 'Playfair Tạp Chí',
            'category' => 'serif',
            'family' => 'MayhenV3ClassicDisplay',
            'stack' => "'MayhenV3ClassicDisplay', Georgia, 'Times New Roman', serif",
            'weights' => [400, 700],
            'supports_italic' => false,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
        'philosopher-serif' => [
            'id' => 'philosopher-serif',
            'label' => 'Philosopher Thơ Mộng',
            'category' => 'serif',
            'family' => 'MayhenV3Philosopher',
            'stack' => "'MayhenV3Philosopher', Georgia, serif",
            'weights' => [400, 700],
            'supports_italic' => true,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
        'modern-sans' => [
            'id' => 'modern-sans',
            'label' => 'Be Vietnam Hiện Đại',
            'category' => 'sans',
            'family' => 'MayhenV3ModernSans',
            'stack' => "'MayhenV3ModernSans', system-ui, -apple-system, sans-serif",
            'weights' => [400, 600, 700],
            'supports_italic' => false,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
        'humanist-sans' => [
            'id' => 'humanist-sans',
            'label' => 'Plus Jakarta Ấm Áp',
            'category' => 'sans',
            'family' => 'MayhenV3HumanistSans',
            'stack' => "'MayhenV3HumanistSans', system-ui, -apple-system, sans-serif",
            'weights' => [400, 600, 700],
            'supports_italic' => false,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
        'jura-geometric' => [
            'id' => 'jura-geometric',
            'label' => 'Jura Thanh Mảnh',
            'category' => 'sans',
            'family' => 'MayhenV3Jura',
            'stack' => "'MayhenV3Jura', system-ui, sans-serif",
            'weights' => [400, 600],
            'supports_italic' => false,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
        'romantic-calligraphy' => [
            'id' => 'romantic-calligraphy',
            'label' => 'Thư Pháp Đơm Hoa',
            'category' => 'script',
            'family' => 'MayhenV3Calligraphy',
            'stack' => "'MayhenV3Calligraphy', cursive",
            'weights' => [400],
            'supports_italic' => false,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
        'wedding-signature' => [
            'id' => 'wedding-signature',
            'label' => 'Chữ Ký Tình Yêu',
            'category' => 'script',
            'family' => 'MayhenV3Signature',
            'stack' => "'MayhenV3Signature', cursive",
            'weights' => [400],
            'supports_italic' => false,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
        'indulge-script' => [
            'id' => 'indulge-script',
            'label' => 'Indulge Bút Sắt',
            'category' => 'script',
            'family' => 'MayhenV3Indulge',
            'stack' => "'MayhenV3Indulge', cursive",
            'weights' => [400],
            'supports_italic' => false,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
        'pattaya-script' => [
            'id' => 'pattaya-script',
            'label' => 'Pattaya Cọ Mềm',
            'category' => 'script',
            'family' => 'MayhenV3Pattaya',
            'stack' => "'MayhenV3Pattaya', cursive",
            'weights' => [400],
            'supports_italic' => false,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
        'editorial-condensed' => [
            'id' => 'editorial-condensed',
            'label' => 'Condensed Ngày Cưới',
            'category' => 'display',
            'family' => 'MayhenV3Condensed',
            'stack' => "'MayhenV3Condensed', Georgia, serif",
            'weights' => [400],
            'supports_italic' => false,
            'vietnamese' => true,
            'sample' => self::SAMPLE_TEXT,
        ],
    ];

    /**
     * Ánh xạ an toàn từ tên font cũ trong DB/config sang `font_id` cục bộ.
     *
     * @var array<string, string>
     */
    private const LEGACY_NAME_ALIASES = [
        'cormorant garamond' => 'editorial-serif',
        'cormorant' => 'editorial-serif',
        'playfair display' => 'classic-display',
        'playfair' => 'classic-display',
        'cinzel' => 'royal-serif',
        'philosopher' => 'philosopher-serif',
        'lustria' => 'royal-serif',
        'be vietnam pro' => 'modern-sans',
        'inter' => 'modern-sans',
        'montserrat' => 'humanist-sans',
        'plus jakarta sans' => 'humanist-sans',
        'jura' => 'jura-geometric',
        'great vibes' => 'romantic-calligraphy',
        'dancing script' => 'wedding-signature',
        'alex brush' => 'indulge-script',
        'pattaya' => 'pattaya-script',
        'w17 indulge' => 'indulge-script',
    ];

    /**
     * Trả về toàn bộ danh sách font được phép sử dụng trong Studio V3.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return array_values(self::FONTS);
    }

    /**
     * Trả về mảng tra cứu theo `font_id`.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function map(): array
    {
        return self::FONTS;
    }

    /**
     * Kiểm tra `font_id` có nằm trong danh sách cho phép hay không.
     */
    public static function isValidFontId(?string $fontId): bool
    {
        if (! is_string($fontId) || $fontId === '') {
            return false;
        }

        return isset(self::FONTS[$fontId]);
    }

    /**
     * Phân giải `font_id` hoặc tên font cũ sang `font_id` hợp lệ (trả về null nếu không hợp lệ).
     */
    public static function resolveFontId(?string $raw): ?string
    {
        if (! is_string($raw)) {
            return null;
        }

        $clean = trim($raw);
        if ($clean === '' || $clean === 'template_default') {
            return null;
        }

        if (isset(self::FONTS[$clean])) {
            return $clean;
        }

        $lower = mb_strtolower($clean, 'UTF-8');
        if (isset(self::LEGACY_NAME_ALIASES[$lower])) {
            return self::LEGACY_NAME_ALIASES[$lower];
        }

        foreach (self::LEGACY_NAME_ALIASES as $needle => $mappedId) {
            if (str_contains($lower, $needle)) {
                return $mappedId;
            }
        }

        return null;
    }

    /**
     * Trả về chuỗi `font-family` an toàn tuyệt đối (không bao giờ trả về chuỗi thô từ người dùng).
     */
    public static function resolveStack(?string $fontIdOrName, string $fallbackRole = 'serif'): ?string
    {
        $resolvedId = self::resolveFontId($fontIdOrName);
        if ($resolvedId !== null && isset(self::FONTS[$resolvedId])) {
            return self::FONTS[$resolvedId]['stack'];
        }

        return match ($fallbackRole) {
            'sans', 'body' => self::FONTS['modern-sans']['stack'],
            'script', 'accent' => self::FONTS['romantic-calligraphy']['stack'],
            default => self::FONTS['editorial-serif']['stack'],
        };
    }

    /**
     * Kết xuất khối CSS biến font toàn thiệp khi người dùng tùy chỉnh `global_style`
     * (đồng bộ cho toàn bộ 7 mẫu thiệp mà không chạm Google Fonts CDN).
     *
     * @param  array<string, mixed>  $globalStyle
     */
    public static function buildGlobalOverrideCss(array $globalStyle): string
    {
        $headingId = self::resolveFontId($globalStyle['heading_font_id'] ?? null);
        $bodyId = self::resolveFontId($globalStyle['body_font_id'] ?? null);
        $accentId = self::resolveFontId($globalStyle['accent_font_id'] ?? null);

        if ($headingId === null && $bodyId === null && $accentId === null) {
            return '';
        }

        $declarations = [];

        if ($headingId !== null) {
            $stack = self::FONTS[$headingId]['stack'];
            $declarations[] = "--font-heading: {$stack} !important;";
            $declarations[] = "--font-serif: {$stack} !important;";
            $declarations[] = "--f-than: {$stack} !important;";
            $declarations[] = "--mayhen-font-serif: {$stack} !important;";
            $declarations[] = "--mayhen-font-display: {$stack} !important;";
            $declarations[] = "--mayhen-invitation-heading-font: {$stack} !important;";
            $declarations[] = "--mayhen-invitation-serif-font: {$stack} !important;";
            $declarations[] = "--mh-f-phi: {$stack} !important;";
            $declarations[] = "--mh-f-lus: {$stack} !important;";
        }

        if ($bodyId !== null) {
            $stack = self::FONTS[$bodyId]['stack'];
            $declarations[] = "--font-body: {$stack} !important;";
            $declarations[] = "--font-sans: {$stack} !important;";
            $declarations[] = "--mayhen-font-sans: {$stack} !important;";
            $declarations[] = "--mayhen-invitation-body-font: {$stack} !important;";
            $declarations[] = "--mh-f-jura: {$stack} !important;";
        }

        if ($accentId !== null) {
            $stack = self::FONTS[$accentId]['stack'];
            $declarations[] = "--font-script: {$stack} !important;";
            $declarations[] = "--f-script: {$stack} !important;";
            $declarations[] = "--mayhen-font-script: {$stack} !important;";
            $declarations[] = "--mayhen-invitation-script-font: {$stack} !important;";
            $declarations[] = "--mh-f-script: {$stack} !important;";
            $declarations[] = "--mh-f-pattaya: {$stack} !important;";
        }

        $joined = implode(' ', $declarations);

        return ":root, html body, .mayhen_invitation_root, .mayhen_invitation_app, .mayhen_invitation_ladi_wraper { {$joined} }";
    }
}
