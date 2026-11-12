<?php

namespace App\Services;

use App\Models\Invitation;

class SmartFocalPointService
{
    /**
     * Danh sách 9 điểm neo tiêu cự vàng chuẩn mực trong nhiếp ảnh cưới.
     *
     * @return array<string, array{label: string, x: int, y: int}>
     */
    public static function getPresetAnchors(): array
    {
        return [
            'top_center' => ['label' => 'Gương Mặt Trên Cao (Khuyên Dùng)', 'x' => 50, 'y' => 20],
            'top_left' => ['label' => 'Góc Trái Trên', 'x' => 25, 'y' => 20],
            'top_right' => ['label' => 'Góc Phải Trên', 'x' => 75, 'y' => 20],
            'center' => ['label' => 'Trung Tâm Mặc Định', 'x' => 50, 'y' => 50],
            'center_left' => ['label' => 'Trung Tâm Bên Trái', 'x' => 25, 'y' => 50],
            'center_right' => ['label' => 'Trung Tâm Bên Phải', 'x' => 75, 'y' => 50],
            'bottom_center' => ['label' => 'Váy Cưới & Dưới Thấp', 'x' => 50, 'y' => 80],
            'bottom_left' => ['label' => 'Góc Trái Dưới', 'x' => 25, 'y' => 80],
            'bottom_right' => ['label' => 'Góc Phải Dưới', 'x' => 75, 'y' => 80],
        ];
    }

    /**
     * Lấy toàn bộ danh sách ảnh và cấu hình tiêu điểm của thiệp cưới.
     *
     * @return array{
     *     images: array<string, array{key: string, title: string, section: string, url: ?string, focal_point: array{x: int, y: int}}>,
     *     presets: array<string, array{label: string, x: int, y: int}>
     * }
     */
    public function getFocalSummary(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $savedFocalPoints = $config['focal_points'] ?? [];

        $images = [];

        // 1. Ảnh bìa Cover chính (Opening / Hero)
        $coverUrl = $wedding?->cover_photo_url ?? ($config['hero_photo_url'] ?? null);
        $images['cover_photo'] = [
            'key' => 'cover_photo',
            'title' => 'Ảnh Bìa Hero / Cổng Cưới Chính',
            'section' => 'Phần 1: Khai Mở & Bìa Thiệp',
            'url' => $coverUrl ?: asset('assets/images/default-wedding-cover.jpg'),
            'focal_point' => $this->normalizePoint($savedFocalPoints['cover_photo'] ?? ['x' => 50, 'y' => 25]),
        ];

        // 2. Chân dung Chú Rể
        $groomPhoto = $config['groom_photo_url'] ?? null;
        $images['groom_photo'] = [
            'key' => 'groom_photo',
            'title' => 'Chân Dung Chú Rể ('.($wedding?->groom_name ?? 'Chú Rể').')',
            'section' => 'Phần 4: Đôi Uyên Ương & Gia Tộc',
            'url' => $groomPhoto ?: asset('assets/images/default-groom.jpg'),
            'focal_point' => $this->normalizePoint($savedFocalPoints['groom_photo'] ?? ['x' => 50, 'y' => 20]),
        ];

        // 3. Chân dung Cô Dâu
        $bridePhoto = $config['bride_photo_url'] ?? null;
        $images['bride_photo'] = [
            'key' => 'bride_photo',
            'title' => 'Chân Dung Cô Dâu ('.($wedding?->bride_name ?? 'Cô Dâu').')',
            'section' => 'Phần 4: Đôi Uyên Ương & Gia Tộc',
            'url' => $bridePhoto ?: asset('assets/images/default-bride.jpg'),
            'focal_point' => $this->normalizePoint($savedFocalPoints['bride_photo'] ?? ['x' => 50, 'y' => 20]),
        ];

        // 4. Các ảnh trong Album Kỷ Niệm (Gallery Photos)
        $galleryPhotos = $config['gallery']['photos'] ?? ($config['gallery_photos'] ?? []);
        if (is_array($galleryPhotos)) {
            foreach ($galleryPhotos as $index => $photo) {
                $photoUrl = is_array($photo) ? ($photo['url'] ?? null) : (string) $photo;
                if (! empty($photoUrl)) {
                    $key = 'gallery_photo_'.$index;
                    $images[$key] = [
                        'key' => $key,
                        'title' => 'Album Ảnh Cưới #'.($index + 1),
                        'section' => 'Phần 9: Album 10 Khoảnh Khắc Vàng',
                        'url' => $photoUrl,
                        'focal_point' => $this->normalizePoint($savedFocalPoints[$key] ?? ['x' => 50, 'y' => 40]),
                    ];
                }
            }
        }

        return [
            'images' => $images,
            'presets' => self::getPresetAnchors(),
        ];
    }

    /**
     * Cập nhật tiêu cự focal point cho một bức ảnh cụ thể.
     */
    public function updateFocalPoint(Invitation $invitation, string $imageKey, int|float $x, int|float $y): Invitation
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $focalPoints = $config['focal_points'] ?? [];

        $clampedX = max(0, min(100, (int) round($x)));
        $clampedY = max(0, min(100, (int) round($y)));

        $focalPoints[$imageKey] = [
            'x' => $clampedX,
            'y' => $clampedY,
        ];

        $config['focal_points'] = $focalPoints;
        $invitation->custom_config = $config;
        $invitation->save();

        return $invitation;
    }

    /**
     * Đưa tiêu cự của ảnh về tâm điểm mặc định (50%, 50%).
     */
    public function resetFocalPoint(Invitation $invitation, string $imageKey): Invitation
    {
        return $this->updateFocalPoint($invitation, $imageKey, 50, 50);
    }

    /**
     * Sinh đoạn mã CSS override tự động cho các mẫu thiệp.
     */
    public function generateCssOverrides(Invitation $invitation): string
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $focalPoints = $config['focal_points'] ?? [];
        if (empty($focalPoints)) {
            return '';
        }

        $css = "/* Smart Focal Point Auto-Injected Rules */\n";

        if (isset($focalPoints['cover_photo'])) {
            $fp = $focalPoints['cover_photo'];
            $css .= ".mayhen_invitation_hero_img, .mayhen_invitation_cover_img, .hero_image { object-position: {$fp['x']}% {$fp['y']}% !important; }\n";
        }

        if (isset($focalPoints['groom_photo'])) {
            $fp = $focalPoints['groom_photo'];
            $css .= ".mayhen_invitation_groom_avatar, .groom_avatar_img { object-position: {$fp['x']}% {$fp['y']}% !important; }\n";
        }

        if (isset($focalPoints['bride_photo'])) {
            $fp = $focalPoints['bride_photo'];
            $css .= ".mayhen_invitation_bride_avatar, .bride_avatar_img { object-position: {$fp['x']}% {$fp['y']}% !important; }\n";
        }

        return $css;
    }

    /**
     * Chuẩn hóa tọa độ tâm ngắm.
     *
     * @param  array<string, mixed>  $point
     * @return array{x: int, y: int}
     */
    protected function normalizePoint(array $point): array
    {
        return [
            'x' => isset($point['x']) ? max(0, min(100, (int) $point['x'])) : 50,
            'y' => isset($point['y']) ? max(0, min(100, (int) $point['y'])) : 50,
        ];
    }
}

