<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class WeddingGalleryService
{
    /**
     * 3 Bố Cục Album Ảnh Cưới Nghệ Thuật Chuẩn Quiet Luxury.
     *
     * @return array<string, array{name: string, description: string, icon: string}>
     */
    public static function getPresetLayouts(): array
    {
        return [
            'editorial_masonry' => [
                'name' => 'Tạp Chí Nghệ Thuật (Editorial Masonry)',
                'description' => 'Bố cục so le cao thấp lấy cảm hứng từ tạp chí Vogue/Elle, tôn vinh chiều sâu cảm xúc của từng khung hình.',
                'icon' => '📰',
            ],
            'carousel_coverflow' => [
                'name' => 'Lướt Ngang 3D (Coverflow Carousel)',
                'description' => 'Trình diễn cuộn ngang mềm mại, ảnh trung tâm phóng nhẹ 3D, tối ưu vuốt chạm mượt mà trên di động.',
                'icon' => '🎞️',
            ],
            'classic_grid' => [
                'name' => 'Lưới Cổ Điển Sang Trọng (Classic Luxury Grid)',
                'description' => 'Bố cục lưới 2 cột tinh tế bo góc thanh lịch, phù hợp với phong cách cưới truyền thống và trang nhã.',
                'icon' => '🖼️',
            ],
        ];
    }

    /**
     * Danh sách 10 ảnh mẫu nội bộ dự phòng với độ phân giải cao.
     *
     * @return list<array{url: string, caption: string, alt: string}>
     */
    public static function getDefaultPhotos(?Invitation $invitation = null): array
    {
        $wedding = $invitation?->wedding;
        $couple = ($wedding->groom_name ?? 'Chú Rể').' & '.($wedding->bride_name ?? 'Cô Dâu');

        $defaultUrls = [
            '/assets/client/images/sample-wedding-1.webp',
            '/assets/client/images/sample-wedding-2.webp',
            '/assets/client/images/sample-wedding-3.webp',
            '/assets/client/images/sample-wedding-4.webp',
            '/assets/client/images/sample-wedding-5.webp',
            '/assets/client/images/sample-wedding-6.webp',
            '/assets/client/images/sample-wedding-7.webp',
            '/assets/client/images/sample-wedding-8.webp',
            '/assets/client/images/sample-wedding-9.webp',
            '/assets/client/images/sample-wedding-10.webp',
        ];

        $defaultCaptions = [
            'Khoảnh khắc hẹn ước trăm năm',
            'Nụ cười rạng ngời ngày chung đôi',
            'Từng ánh mắt trao trọn niềm tin',
            'Bước chân an yên bên người thương',
            'Hoàng hôn buông trên lời hẹn thề',
            'Nắm tay nhau đi qua năm tháng',
            'Bình yên trong vòng tay ấm áp',
            'Ngày của yêu thương và hạnh phúc',
            'Hành trình ngọt ngào chỉ mới bắt đầu',
            'Trọn vẹn một tình yêu vĩnh cửu',
        ];

        // Nếu wedding đã có gallery cũ trong extra_data thì ưu tiên lấy
        $existingGallery = $wedding?->extra_data['gallery'] ?? [];
        $photos = [];

        for ($i = 0; $i < 10; $i++) {
            $url = ! empty($existingGallery[$i]) ? (string) $existingGallery[$i] : $defaultUrls[$i];
            $caption = $defaultCaptions[$i];

            $photos[] = [
                'url' => $url,
                'caption' => $caption,
                'alt' => "Ảnh cưới {$couple} - Bức số ".($i + 1),
            ];
        }

        return $photos;
    }

    /**
     * Phân giải cấu hình Album 10 ảnh cưới hoàn chỉnh.
     *
     * @return array<string, mixed>
     */
    public static function resolveGalleryConfig(Invitation $invitation): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $saved = $customConfig['gallery'] ?? [];

        $enabled = isset($saved['enabled']) ? (bool) $saved['enabled'] : true;
        $layoutStyle = ! empty($saved['layout_style']) ? (string) $saved['layout_style'] : 'editorial_masonry';
        if (! array_key_exists($layoutStyle, self::getPresetLayouts())) {
            $layoutStyle = 'editorial_masonry';
        }

        $title = ! empty($saved['title']) ? (string) $saved['title'] : 'Khoảnh Khắc Hạnh Phúc';
        $subtitle = ! empty($saved['subtitle']) ? (string) $saved['subtitle'] : 'Những thước ảnh ghi dấu tình yêu và lời hẹn ước trăm năm của chúng mình';
        $lightboxEnabled = isset($saved['lightbox_enabled']) ? (bool) $saved['lightbox_enabled'] : true;

        $defaultPhotos = self::getDefaultPhotos($invitation);
        $savedPhotos = (array) ($saved['photos'] ?? []);

        $photos = [];
        for ($i = 0; $i < 10; $i++) {
            $item = $savedPhotos[$i] ?? null;

            if (is_array($item) && ! empty($item['url'])) {
                $photos[] = [
                    'url' => (string) $item['url'],
                    'caption' => ! empty($item['caption']) ? (string) $item['caption'] : $defaultPhotos[$i]['caption'],
                    'alt' => ! empty($item['alt']) ? (string) $item['alt'] : $defaultPhotos[$i]['alt'],
                ];
            } elseif (is_string($item) && ! empty($item)) {
                $photos[] = [
                    'url' => $item,
                    'caption' => $defaultPhotos[$i]['caption'],
                    'alt' => $defaultPhotos[$i]['alt'],
                ];
            } else {
                $photos[] = $defaultPhotos[$i];
            }
        }

        return [
            'enabled' => $enabled,
            'layout_style' => $layoutStyle,
            'title' => $title,
            'subtitle' => $subtitle,
            'lightbox_enabled' => $lightboxEnabled,
            'photos' => $photos,
        ];
    }

    /**
     * Cập nhật cấu hình Album 10 ảnh cưới và đồng bộ vào extra_data['gallery'].
     *
     * @param  array<string, mixed>  $data
     */
    public static function updateGallery(Invitation $invitation, array $data): Invitation
    {
        $current = self::resolveGalleryConfig($invitation);

        $enabled = isset($data['enabled']) ? (bool) $data['enabled'] : true;
        $layoutStyle = ! empty($data['layout_style']) ? trim((string) $data['layout_style']) : $current['layout_style'];
        if (! array_key_exists($layoutStyle, self::getPresetLayouts())) {
            $layoutStyle = 'editorial_masonry';
        }

        $title = ! empty($data['title']) ? trim((string) $data['title']) : $current['title'];
        $subtitle = ! empty($data['subtitle']) ? trim((string) $data['subtitle']) : $current['subtitle'];
        $lightboxEnabled = isset($data['lightbox_enabled']) ? (bool) $data['lightbox_enabled'] : true;

        $inputPhotos = (array) ($data['photos'] ?? []);
        $photos = [];
        $simpleUrlList = [];

        for ($i = 0; $i < 10; $i++) {
            $item = $inputPhotos[$i] ?? null;
            $fallback = $current['photos'][$i] ?? self::getDefaultPhotos($invitation)[$i];

            if (is_array($item) && ! empty($item['url'])) {
                $url = trim((string) $item['url']);
                $caption = ! empty($item['caption']) ? trim((string) $item['caption']) : $fallback['caption'];
                $alt = ! empty($item['alt']) ? trim((string) $item['alt']) : $fallback['alt'];
            } elseif (is_string($item) && ! empty($item)) {
                $url = trim($item);
                $caption = $fallback['caption'];
                $alt = $fallback['alt'];
            } else {
                $url = $fallback['url'];
                $caption = $fallback['caption'];
                $alt = $fallback['alt'];
            }

            $photos[] = [
                'url' => $url,
                'caption' => $caption,
                'alt' => $alt,
            ];
            $simpleUrlList[] = $url;
        }

        $galleryConfig = [
            'enabled' => $enabled,
            'layout_style' => $layoutStyle,
            'title' => $title,
            'subtitle' => $subtitle,
            'lightbox_enabled' => $lightboxEnabled,
            'photos' => $photos,
        ];

        // 1. Cập nhật vào custom_config của Invitation
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $customConfig['gallery'] = $galleryConfig;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        // 2. Đồng bộ danh sách URL ảnh vào wedding->extra_data['gallery']
        $wedding = $invitation->wedding;
        if ($wedding) {
            $extraData = is_array($wedding->extra_data) ? $wedding->extra_data : [];
            $extraData['gallery'] = $simpleUrlList;
            $wedding->extra_data = $extraData;
            $wedding->save();
        }

        AuditLogger::log(
            'GALLERY_UPDATED',
            'Invitation',
            $invitation->id,
            "Cập nhật Album 10 ảnh cưới nghệ thuật cho thiệp #{$invitation->id} (Bố cục: {$layoutStyle})",
            [
                'invitation_id' => $invitation->id,
                'layout_style' => $layoutStyle,
                'photos_count' => count($photos),
            ]
        );

        return $invitation;
    }

    /**
     * Tải lên ảnh cưới vào kho lưu trữ nội bộ của thiệp.
     */
    public static function uploadGalleryPhoto(Invitation $invitation, UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'avif'])) {
            $extension = 'webp';
        }

        $filename = 'gallery_'.$invitation->id.'_'.Str::random(12).'.'.$extension;
        $directory = public_path('storage/invitations/'.$invitation->id.'/gallery');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $file->move($directory, $filename);

        return '/storage/invitations/'.$invitation->id.'/gallery/'.$filename;
    }
}
