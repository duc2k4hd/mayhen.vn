<?php

namespace App\Services;

use Illuminate\Support\Str;

class VisualEditorSanitizerService
{
    public const SCHEMA_VERSION = 1;

    public const MAX_OVERLAYS_TOTAL = 100;

    public const MAX_OVERLAYS_PER_SECTION = 20;

    public const MAX_OVERLAY_TEXT_LENGTH = 500;

    private const ALLOWED_SECTIONS = [
        'opening',
        'hero',
        'story',
        'parents',
        'countdown_calendar',
        'events',
        'timeline',
        'dresscode',
        'album',
        'gift',
        'signature',
        'rsvp',
        'wishes',
        'ending',
    ];

    private const ALLOWED_OVERLAY_TYPES = [
        'overlay_text',
        'overlay_sticker',
        'overlay_shape',
        'overlay_frame',
        'overlay_divider',
        'overlay_image',
    ];

    private const ALLOWED_TEXT_ALIGNS = ['left', 'center', 'right', 'justify'];

    private const ALLOWED_EASINGS = [
        'ease',
        'ease-out',
        'ease-in-out',
        'linear',
        'cubic-bezier(0.22, 1, 0.36, 1)',
        'cubic-bezier(0.16, 1, 0.3, 1)',
    ];

    private const ALLOWED_TRIGGERS = ['on_load', 'viewport', 'after_open'];

    private const ALLOWED_REPEATS = ['once', 'infinite'];

    /**
     * Cấu trúc rỗng chuẩn phiên bản 1.
     *
     * @return array<string, mixed>
     */
    public static function emptyState(): array
    {
        return [
            'version' => self::SCHEMA_VERSION,
            'global_style' => [],
            'instance_styles' => [],
            'image_overrides' => [],
            'instance_animations' => [],
            'overlays' => [],
        ];
    }

    /**
     * Làm sạch toàn diện payload `visual_editor` và gộp sâu có kiểm soát với trạng thái hiện tại.
     * Hỗ trợ giá trị `null` hoặc `['__reset' => true]` để xóa sạch override của một phần tử.
     *
     * @param  array<string, mixed>|null  $existing
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>
     */
    public static function sanitizeAndMerge(?array $existing, array $incoming): array
    {
        $base = self::sanitize($existing ?? []);

        if (! empty($incoming['__replace_all'])) {
            return self::sanitize($incoming);
        }

        // 1. Global style
        if (array_key_exists('global_style', $incoming)) {
            if ($incoming['global_style'] === null) {
                $base['global_style'] = [];
            } elseif (is_array($incoming['global_style'])) {
                $mergedGlobal = $base['global_style'];
                foreach (['heading_font_id', 'body_font_id', 'accent_font_id'] as $roleKey) {
                    if (array_key_exists($roleKey, $incoming['global_style'])) {
                        $rawVal = $incoming['global_style'][$roleKey];
                        if ($rawVal === null || $rawVal === '' || $rawVal === 'template_default') {
                            unset($mergedGlobal[$roleKey]);
                        } else {
                            $resolved = InvitationFontRegistryService::resolveFontId((string) $rawVal);
                            if ($resolved !== null) {
                                $mergedGlobal[$roleKey] = $resolved;
                            }
                        }
                    }
                }
                $base['global_style'] = $mergedGlobal;
            }
        }

        // 2. Instance styles
        if (array_key_exists('instance_styles', $incoming)) {
            if ($incoming['instance_styles'] === null) {
                $base['instance_styles'] = [];
            } elseif (is_array($incoming['instance_styles'])) {
                foreach ($incoming['instance_styles'] as $instanceId => $styleRule) {
                    $cleanId = self::sanitizeInstanceId((string) $instanceId);
                    if ($cleanId === null) {
                        continue;
                    }

                    if ($styleRule === null || (is_array($styleRule) && ! empty($styleRule['__reset']))) {
                        unset($base['instance_styles'][$cleanId]);

                        continue;
                    }

                    if (is_array($styleRule)) {
                        $mergedRule = array_merge($base['instance_styles'][$cleanId] ?? [], $styleRule);
                        foreach ($styleRule as $prop => $val) {
                            if ($val === null || $val === '') {
                                unset($mergedRule[$prop]);
                            }
                        }
                        $cleanRule = self::sanitizeInstanceStyle($mergedRule);
                        if ($cleanRule === []) {
                            unset($base['instance_styles'][$cleanId]);
                        } else {
                            $base['instance_styles'][$cleanId] = $cleanRule;
                        }
                    }
                }
            }
        }

        // 3. Image overrides (focal point, zoom, aspect ratio, opacity)
        if (array_key_exists('image_overrides', $incoming)) {
            if ($incoming['image_overrides'] === null) {
                $base['image_overrides'] = [];
            } elseif (is_array($incoming['image_overrides'])) {
                foreach ($incoming['image_overrides'] as $instanceId => $imgOverride) {
                    $cleanId = self::sanitizeInstanceId((string) $instanceId);
                    if ($cleanId === null) {
                        continue;
                    }

                    if ($imgOverride === null || (is_array($imgOverride) && ! empty($imgOverride['__reset']))) {
                        unset($base['image_overrides'][$cleanId]);

                        continue;
                    }

                    if (is_array($imgOverride)) {
                        $mergedImg = array_merge($base['image_overrides'][$cleanId] ?? [], $imgOverride);
                        $cleanImg = self::sanitizeImageOverride($mergedImg);
                        if ($cleanImg === []) {
                            unset($base['image_overrides'][$cleanId]);
                        } else {
                            $base['image_overrides'][$cleanId] = $cleanImg;
                        }
                    }
                }
            }
        }

        // 4. Instance animations
        if (array_key_exists('instance_animations', $incoming)) {
            if ($incoming['instance_animations'] === null) {
                $base['instance_animations'] = [];
            } elseif (is_array($incoming['instance_animations'])) {
                foreach ($incoming['instance_animations'] as $instanceId => $motionRule) {
                    $cleanId = self::sanitizeInstanceId((string) $instanceId);
                    if ($cleanId === null) {
                        continue;
                    }

                    if ($motionRule === null || (is_array($motionRule) && (! empty($motionRule['__reset']) || ($motionRule['preset'] ?? '') === 'none'))) {
                        unset($base['instance_animations'][$cleanId]);

                        continue;
                    }

                    if (is_array($motionRule)) {
                        $cleanMotion = self::sanitizeMotion($motionRule);
                        if ($cleanMotion === null) {
                            unset($base['instance_animations'][$cleanId]);
                        } else {
                            $base['instance_animations'][$cleanId] = $cleanMotion;
                        }
                    }
                }
            }
        }

        // 5. Overlays array (canonical replacement of array when provided)
        if (array_key_exists('overlays', $incoming)) {
            if ($incoming['overlays'] === null) {
                $base['overlays'] = [];
            } elseif (is_array($incoming['overlays'])) {
                $base['overlays'] = self::sanitizeOverlaysList($incoming['overlays']);
            }
        }

        $base['version'] = self::SCHEMA_VERSION;

        return $base;
    }

    /**
     * Làm sạch một cấu trúc `visual_editor` độc lập.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public static function sanitize(array $raw): array
    {
        $clean = self::emptyState();

        if (! empty($raw['global_style']) && is_array($raw['global_style'])) {
            foreach (['heading_font_id', 'body_font_id', 'accent_font_id'] as $roleKey) {
                $resolved = InvitationFontRegistryService::resolveFontId($raw['global_style'][$roleKey] ?? null);
                if ($resolved !== null) {
                    $clean['global_style'][$roleKey] = $resolved;
                }
            }
        }

        if (! empty($raw['instance_styles']) && is_array($raw['instance_styles'])) {
            foreach ($raw['instance_styles'] as $id => $rule) {
                $cleanId = self::sanitizeInstanceId((string) $id);
                if ($cleanId !== null && is_array($rule)) {
                    $cleanRule = self::sanitizeInstanceStyle($rule);
                    if ($cleanRule !== []) {
                        $clean['instance_styles'][$cleanId] = $cleanRule;
                    }
                }
            }
        }

        if (! empty($raw['image_overrides']) && is_array($raw['image_overrides'])) {
            foreach ($raw['image_overrides'] as $id => $override) {
                $cleanId = self::sanitizeInstanceId((string) $id);
                if ($cleanId !== null && is_array($override)) {
                    $cleanOverride = self::sanitizeImageOverride($override);
                    if ($cleanOverride !== []) {
                        $clean['image_overrides'][$cleanId] = $cleanOverride;
                    }
                }
            }
        }

        if (! empty($raw['instance_animations']) && is_array($raw['instance_animations'])) {
            foreach ($raw['instance_animations'] as $id => $motion) {
                $cleanId = self::sanitizeInstanceId((string) $id);
                if ($cleanId !== null && is_array($motion)) {
                    $cleanMotion = self::sanitizeMotion($motion);
                    if ($cleanMotion !== null) {
                        $clean['instance_animations'][$cleanId] = $cleanMotion;
                    }
                }
            }
        }

        if (! empty($raw['overlays']) && is_array($raw['overlays'])) {
            $clean['overlays'] = self::sanitizeOverlaysList($raw['overlays']);
        }

        return $clean;
    }

    /**
     * Tái sinh định danh `id` mới cho toàn bộ phần tử `overlays` khi nhân bản thiệp.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public static function regenerateOverlayIdsInConfig(array $config): array
    {
        if (empty($config['visual_editor']['overlays']) || ! is_array($config['visual_editor']['overlays'])) {
            return $config;
        }

        foreach ($config['visual_editor']['overlays'] as $idx => $overlay) {
            if (is_array($overlay)) {
                $config['visual_editor']['overlays'][$idx]['id'] = 'ov_'.Str::lower(Str::random(10));
            }
        }

        return $config;
    }

    /**
     * Trích xuất toàn bộ URL tài nguyên `/storage/...` bên trong `custom_config` / `draft_config`
     * để hệ thống kiểm tra hạn mức và dọn tệp mồ côi không bao giờ xóa nhầm ảnh Visual Editor.
     *
     * @param  array<string, mixed>|null  $config
     * @return array<int, string>
     */
    public static function extractReferencedAssetUrls(?array $config): array
    {
        if (empty($config) || ! is_array($config)) {
            return [];
        }

        $urls = [];
        $overlays = $config['visual_editor']['overlays'] ?? [];
        if (is_array($overlays)) {
            foreach ($overlays as $ov) {
                if (is_array($ov) && ! empty($ov['image_url']) && is_string($ov['image_url'])) {
                    $urls[] = $ov['image_url'];
                }
            }
        }

        return array_values(array_unique($urls));
    }

    public static function sanitizeInstanceId(string $id): ?string
    {
        $trimmed = trim($id);
        if ($trimmed === '' || strlen($trimmed) > 120) {
            return null;
        }

        if (! preg_match('/^[a-zA-Z0-9_.\-:]+$/', $trimmed)) {
            return null;
        }

        return $trimmed;
    }

    /**
     * @param  array<string, mixed>  $rule
     * @return array<string, mixed>
     */
    private static function sanitizeInstanceStyle(array $rule): array
    {
        $clean = [];

        if (isset($rule['scope']) && in_array($rule['scope'], ['instance', 'sync_key', 'section'], true)) {
            $clean['scope'] = $rule['scope'];
        }

        if (! empty($rule['font_id'])) {
            $fontId = InvitationFontRegistryService::resolveFontId((string) $rule['font_id']);
            if ($fontId !== null) {
                $clean['font_id'] = $fontId;
            }
        }

        if (isset($rule['font_size_px']) && is_numeric($rule['font_size_px'])) {
            $clean['font_size_px'] = (int) round(self::clamp((float) $rule['font_size_px'], 10.0, 96.0));
        }

        if (isset($rule['font_weight']) && is_numeric($rule['font_weight'])) {
            $weight = (int) $rule['font_weight'];
            if (in_array($weight, [300, 400, 500, 600, 700], true)) {
                $clean['font_weight'] = $weight;
            }
        }

        if (isset($rule['font_style']) && in_array($rule['font_style'], ['normal', 'italic'], true)) {
            $clean['font_style'] = $rule['font_style'];
        }

        if (isset($rule['line_height']) && is_numeric($rule['line_height'])) {
            $clean['line_height'] = round(self::clamp((float) $rule['line_height'], 0.9, 2.5), 2);
        }

        if (isset($rule['letter_spacing_em']) && is_numeric($rule['letter_spacing_em'])) {
            $clean['letter_spacing_em'] = round(self::clamp((float) $rule['letter_spacing_em'], -0.08, 0.5), 3);
        }

        if (isset($rule['text_align']) && in_array($rule['text_align'], self::ALLOWED_TEXT_ALIGNS, true)) {
            $clean['text_align'] = $rule['text_align'];
        }

        if (isset($rule['text_transform']) && in_array($rule['text_transform'], ['none', 'uppercase', 'capitalize'], true)) {
            $clean['text_transform'] = $rule['text_transform'];
        }

        if (! empty($rule['color'])) {
            $color = self::sanitizeColor((string) $rule['color']);
            if ($color !== null) {
                $clean['color'] = $color;
            }
        }

        if (isset($rule['opacity']) && is_numeric($rule['opacity'])) {
            $clean['opacity'] = (int) round(self::clamp((float) $rule['opacity'], 10.0, 100.0));
        }

        return $clean;
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    private static function sanitizeImageOverride(array $override): array
    {
        $clean = [];

        if (isset($override['focal_x']) && is_numeric($override['focal_x'])) {
            $clean['focal_x'] = round(self::clamp((float) $override['focal_x'], 0.0, 100.0), 1);
        }

        if (isset($override['focal_y']) && is_numeric($override['focal_y'])) {
            $clean['focal_y'] = round(self::clamp((float) $override['focal_y'], 0.0, 100.0), 1);
        }

        if (isset($override['zoom']) && is_numeric($override['zoom'])) {
            $clean['zoom'] = round(self::clamp((float) $override['zoom'], 1.0, 3.0), 2);
        }

        if (isset($override['aspect_ratio']) && is_string($override['aspect_ratio'])) {
            $ar = trim($override['aspect_ratio']);
            if (in_array($ar, ['free', '1:1', '3:4', '4:3', '4:5', '9:16', '16:9'], true)) {
                $clean['aspect_ratio'] = $ar;
            }
        }

        if (isset($override['opacity']) && is_numeric($override['opacity'])) {
            $clean['opacity'] = (int) round(self::clamp((float) $override['opacity'], 10.0, 100.0));
        }

        return $clean;
    }

    /**
     * @param  array<string, mixed>  $motion
     * @return array<string, mixed>|null
     */
    private static function sanitizeMotion(array $motion): ?array
    {
        $preset = isset($motion['preset']) ? trim((string) $motion['preset']) : '';
        if ($preset === '' || $preset === 'none' || ! StudioDecorationLibraryService::isValidMotionPreset($preset)) {
            return null;
        }

        $duration = isset($motion['duration_ms']) && is_numeric($motion['duration_ms'])
            ? (int) round(self::clamp((float) $motion['duration_ms'], 150.0, 4000.0))
            : 800;

        $delay = isset($motion['delay_ms']) && is_numeric($motion['delay_ms'])
            ? (int) round(self::clamp((float) $motion['delay_ms'], 0.0, 2000.0))
            : 0;

        $easing = isset($motion['easing']) && in_array($motion['easing'], self::ALLOWED_EASINGS, true)
            ? $motion['easing']
            : 'cubic-bezier(0.22, 1, 0.36, 1)';

        $trigger = isset($motion['trigger']) && in_array($motion['trigger'], self::ALLOWED_TRIGGERS, true)
            ? $motion['trigger']
            : 'viewport';

        $repeat = isset($motion['repeat']) && in_array($motion['repeat'], self::ALLOWED_REPEATS, true)
            ? $motion['repeat']
            : (in_array($preset, ['soft-pulse', 'slow-float', 'tiny-sway'], true) ? 'infinite' : 'once');

        return [
            'preset' => $preset,
            'duration_ms' => $duration,
            'delay_ms' => $delay,
            'easing' => $easing,
            'trigger' => $trigger,
            'repeat' => $repeat,
        ];
    }

    /**
     * @param  array<int, mixed>  $list
     * @return array<int, array<string, mixed>>
     */
    private static function sanitizeOverlaysList(array $list): array
    {
        $cleanList = [];
        $sectionCounts = [];
        $seenIds = [];

        foreach ($list as $item) {
            if (count($cleanList) >= self::MAX_OVERLAYS_TOTAL) {
                break;
            }
            if (! is_array($item)) {
                continue;
            }

            $type = isset($item['type']) ? (string) $item['type'] : '';
            if (! in_array($type, self::ALLOWED_OVERLAY_TYPES, true)) {
                continue;
            }

            $sectionId = isset($item['section_id']) ? trim((string) $item['section_id']) : 'hero';
            if (! in_array($sectionId, self::ALLOWED_SECTIONS, true)) {
                $sectionId = 'hero';
            }

            $sectionCounts[$sectionId] = ($sectionCounts[$sectionId] ?? 0) + 1;
            if ($sectionCounts[$sectionId] > self::MAX_OVERLAYS_PER_SECTION) {
                continue;
            }

            $rawId = isset($item['id']) ? (string) $item['id'] : '';
            $id = (preg_match('/^[a-zA-Z0-9_-]{3,48}$/', $rawId) && ! isset($seenIds[$rawId]))
                ? $rawId
                : ('ov_'.Str::lower(Str::random(10)));
            $seenIds[$id] = true;

            $assetId = isset($item['asset_id']) ? trim((string) $item['asset_id']) : '';
            if ($type === 'overlay_sticker' || $type === 'overlay_divider') {
                if (! StudioDecorationLibraryService::isValidStickerId($assetId)) {
                    $assetId = 'wax-seal-champagne';
                }
            } elseif ($type === 'overlay_shape') {
                if (! StudioDecorationLibraryService::isValidShapeId($assetId)) {
                    $assetId = 'pill-badge';
                }
            } elseif ($type === 'overlay_frame') {
                if (! StudioDecorationLibraryService::isValidFrameId($assetId)) {
                    $assetId = 'frame-arch';
                }
            } else {
                $assetId = '';
            }

            $text = '';
            if ($type === 'overlay_text') {
                $plain = strip_tags((string) ($item['text'] ?? ''));
                $plain = preg_replace("/\r\n|\r/", "\n", $plain) ?? '';
                $text = mb_substr(trim($plain), 0, self::MAX_OVERLAY_TEXT_LENGTH, 'UTF-8');
            }

            $imageUrl = '';
            if (in_array($type, ['overlay_frame', 'overlay_image'], true) && ! empty($item['image_url'])) {
                $safeUrl = self::sanitizeAssetUrl((string) $item['image_url']);
                if ($safeUrl !== null) {
                    $imageUrl = $safeUrl;
                }
            }

            $x = round(self::clamp((float) ($item['x'] ?? 0.5), 0.02, 0.98), 4);
            $y = round(self::clamp((float) ($item['y'] ?? 0.5), 0.02, 0.98), 4);
            $w = round(self::clamp((float) ($item['w'] ?? 0.28), 0.05, 0.96), 4);
            $h = round(self::clamp((float) ($item['h'] ?? 0.18), 0.03, 0.96), 4);

            $desktopOverride = null;
            if (! empty($item['desktop_override']) && is_array($item['desktop_override'])) {
                $desktopOverride = [
                    'x' => round(self::clamp((float) ($item['desktop_override']['x'] ?? $x), 0.02, 0.98), 4),
                    'y' => round(self::clamp((float) ($item['desktop_override']['y'] ?? $y), 0.02, 0.98), 4),
                    'w' => round(self::clamp((float) ($item['desktop_override']['w'] ?? $w), 0.05, 0.96), 4),
                    'h' => round(self::clamp((float) ($item['desktop_override']['h'] ?? $h), 0.03, 0.96), 4),
                ];
            }

            $style = [];
            if (! empty($item['style']) && is_array($item['style'])) {
                $style = self::sanitizeInstanceStyle($item['style']);
                if (! empty($item['style']['fill'])) {
                    $fill = self::sanitizeColor((string) $item['style']['fill']);
                    if ($fill !== null) {
                        $style['fill'] = $fill;
                    }
                }
                if (! empty($item['style']['stroke'])) {
                    $stroke = self::sanitizeColor((string) $item['style']['stroke']);
                    if ($stroke !== null) {
                        $style['stroke'] = $stroke;
                    }
                }
            }

            $crop = null;
            if (! empty($item['crop']) && is_array($item['crop'])) {
                $cleanCrop = self::sanitizeImageOverride($item['crop']);
                if ($cleanCrop !== []) {
                    $crop = $cleanCrop;
                }
            }

            $motion = null;
            if (! empty($item['motion']) && is_array($item['motion'])) {
                $motion = self::sanitizeMotion($item['motion']);
            }

            $cleanList[] = [
                'id' => $id,
                'type' => $type,
                'section_id' => $sectionId,
                'asset_id' => $assetId,
                'text' => $text,
                'image_url' => $imageUrl,
                'x' => $x,
                'y' => $y,
                'w' => $w,
                'h' => $h,
                'desktop_override' => $desktopOverride,
                'rotation_deg' => round(self::clamp((float) ($item['rotation_deg'] ?? 0.0), -360.0, 360.0), 1),
                'z_index' => (int) round(self::clamp((float) ($item['z_index'] ?? 12.0), 1.0, 60.0)),
                'opacity' => (int) round(self::clamp((float) ($item['opacity'] ?? 100.0), 10.0, 100.0)),
                'locked' => (bool) ($item['locked'] ?? false),
                'hidden' => (bool) ($item['hidden'] ?? false),
                'style' => $style,
                'crop' => $crop,
                'motion' => $motion,
            ];
        }

        return $cleanList;
    }

    public static function sanitizeColor(string $color): ?string
    {
        $trimmed = trim($color);
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $trimmed)) {
            return $trimmed;
        }

        if (preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)$/', $trimmed)) {
            return $trimmed;
        }

        return null;
    }

    public static function sanitizeAssetUrl(string $url): ?string
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        $lower = strtolower($trimmed);
        if (
            str_starts_with($lower, 'blob:')
            || str_starts_with($lower, 'javascript:')
            || str_starts_with($lower, 'vbscript:')
            || str_starts_with($lower, 'data:')
        ) {
            return null;
        }

        if (str_starts_with($trimmed, '/storage/') || str_starts_with($trimmed, '/assets/') || str_starts_with($trimmed, '/mau-thiep/')) {
            if (str_contains($trimmed, '..')) {
                return null;
            }

            return $trimmed;
        }

        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://')) {
            $parsed = parse_url($trimmed);
            $path = $parsed['path'] ?? '';
            if (
                is_string($path)
                && (str_starts_with($path, '/storage/') || str_starts_with($path, '/assets/') || str_starts_with($path, '/mau-thiep/'))
                && ! str_contains($path, '..')
            ) {
                return $trimmed;
            }
        }

        return null;
    }

    private static function clamp(float $val, float $min, float $max): float
    {
        return max($min, min($max, $val));
    }
}
