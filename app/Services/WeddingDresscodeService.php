<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WeddingDresscodeService
{
    /**
     * 6 Bộ Bảng Màu Quy Chuẩn Trang Phục Quiet Luxury Hôn Lễ.
     *
     * @return array<string, array{name: string, description: string, colors: array<int, array{hex: string, name: string, description: string}>}>
     */
    public static function getPresetPalettes(): array
    {
        return [
            'quiet_luxury_neutral' => [
                'name' => 'Quiet Luxury Neutral (Beige & Warm Sand)',
                'description' => 'Tone màu kem ấm, be lụa và nâu cà phê thanh lịch bậc nhất, tôn vinh nét đẹp trang nhã và cổ điển.',
                'colors' => [
                    ['hex' => '#FAF8F5', 'name' => 'Trắng Ngà (Ivory Cream)', 'description' => 'Nền nã, thuần khiết'],
                    ['hex' => '#EFE8DD', 'name' => 'Be Ấm (Soft Beige)', 'description' => 'Ấm áp, mềm mại'],
                    ['hex' => '#D8CEBD', 'name' => 'Nâu Cát (Warm Sand)', 'description' => 'Mộc mạc, tinh tế'],
                    ['hex' => '#9C7A4A', 'name' => 'Vàng Cổ Điển (Antique Gold)', 'description' => 'Điểm xuyết quý phái'],
                    ['hex' => '#4A453E', 'name' => 'Espresso (Nâu Đậm)', 'description' => 'Lịch lãm, tương phản'],
                ],
            ],
            'royal_velvet_black_tie' => [
                'name' => 'Royal Velvet & Black Tie (Đỏ Nhung & Vàng Hoàng Gia)',
                'description' => 'Sự kết hợp vương giả giữa đỏ nhung quý tộc, vàng ánh kim và đen tuyền dạ tiệc.',
                'colors' => [
                    ['hex' => '#85182A', 'name' => 'Đỏ Rượu (Velvet Ruby)', 'description' => 'Hỷ sự, sang trọng'],
                    ['hex' => '#5E0E1C', 'name' => 'Đỏ Mận Đậm (Deep Crimson)', 'description' => 'Quyến rũ, quyền uy'],
                    ['hex' => '#C5A880', 'name' => 'Vàng Đồng (Champagne Gold)', 'description' => 'Ánh kim rực rỡ'],
                    ['hex' => '#FAF7F2', 'name' => 'Kem Hoàng Gia (Royal Cream)', 'description' => 'Cân bằng trang nhã'],
                    ['hex' => '#1A1A1A', 'name' => 'Đen Dạ Tiệc (Classic Black)', 'description' => 'Trang trọng tuyệt đối'],
                ],
            ],
            'botanical_garden_sage' => [
                'name' => 'Botanical Garden & Sage (Xanh Xô Thơm & Olive)',
                'description' => 'Hoàn hảo cho tiệc cưới sân vườn ngoài trời, hòa mình vào thiên nhiên tươi mát.',
                'colors' => [
                    ['hex' => '#9AA899', 'name' => 'Xanh Xô Thơm (Sage Green)', 'description' => 'Thanh thoát, mát dịu'],
                    ['hex' => '#748E8A', 'name' => 'Xanh Rừng (Olive Moss)', 'description' => 'Trầm lắng, sâu sắc'],
                    ['hex' => '#F5F3EF', 'name' => 'Trắng Hoa Cỏ (Botanical White)', 'description' => 'Trong trẻo, tươi mới'],
                    ['hex' => '#C8B8A6', 'name' => 'Gỗ Mộc (Driftwood Sand)', 'description' => 'Hòa quyện tự nhiên'],
                    ['hex' => '#3E4B42', 'name' => 'Xanh Rêu Đậm (Deep Forest)', 'description' => 'Điểm nhấn mạnh mẽ'],
                ],
            ],
            'romantic_dusty_rose' => [
                'name' => 'Romantic Dusty Rose & Champagne (Hồng Tro & Phấn Lụa)',
                'description' => 'Chất thơ ngọt ngào, lãng mạn như đoá hoa nở trong nắng sớm phương Tây.',
                'colors' => [
                    ['hex' => '#D9B4A7', 'name' => 'Hồng Tro (Dusty Rose)', 'description' => 'Dịu dàng, thơ mộng'],
                    ['hex' => '#F4E8D8', 'name' => 'Rượu Sâm-panh (Champagne)', 'description' => 'Lung linh, ấm áp'],
                    ['hex' => '#E8C5BE', 'name' => 'Hồng Phấn Lụa (Silk Blush)', 'description' => 'Nữ tính, nhẹ nhàng'],
                    ['hex' => '#FFFDF9', 'name' => 'Ngọc Trai (Warm Pearl)', 'description' => 'Thuần khiết'],
                    ['hex' => '#6E5D57', 'name' => 'Nâu Hồng Đất (Muted Cocoa)', 'description' => 'Chiều sâu cảm xúc'],
                ],
            ],
            'sunset_amber_terracotta' => [
                'name' => 'Sunset Amber & Terracotta (Hổ Phách & Hoàng Hôn Ấm)',
                'description' => 'Phong cách Bohemian & Rustic ngập tràn ánh nắng hoàng hôn ấm áp và phong vị nghệ thuật.',
                'colors' => [
                    ['hex' => '#BD6B4D', 'name' => 'Cam Đất (Terracotta)', 'description' => 'Ấm cúng, tự do'],
                    ['hex' => '#D49B55', 'name' => 'Hổ Phách (Warm Amber)', 'description' => 'Rực rỡ nắng chiều'],
                    ['hex' => '#7C4834', 'name' => 'Nâu Gốm Mộc (Earthen Rust)', 'description' => 'Chân thành, đầm ấm'],
                    ['hex' => '#F3EDE2', 'name' => 'Cát Sa Mạc (Desert Cream)', 'description' => 'Dịu mát cân đối'],
                    ['hex' => '#3B2F2F', 'name' => 'Espresso Đậm (Dark Roast)', 'description' => 'Lắng đọng'],
                ],
            ],
            'coastal_dusty_blue' => [
                'name' => 'Coastal Breeze & Dusty Blue (Xanh Lam Biển & Bạc Ánh Trăng)',
                'description' => 'Tone xanh biển thanh khiết dành cho tiệc cưới bãi biển hoặc resort ven sông hồ.',
                'colors' => [
                    ['hex' => '#8FA3B0', 'name' => 'Xanh Khói Biển (Dusty Blue)', 'description' => 'Yên bình, thanh thoát'],
                    ['hex' => '#2A3B4C', 'name' => 'Xanh Đại Dương (Ocean Navy)', 'description' => 'Vững chãi, trang nghiêm'],
                    ['hex' => '#F7F9FA', 'name' => 'Bọt Sóng (Sea Foam White)', 'description' => 'Trong ngần'],
                    ['hex' => '#D5C7B3', 'name' => 'Cát Vàng (Sea Sand)', 'description' => 'Nắng ấm bãi triều'],
                    ['hex' => '#B8C5D0', 'name' => 'Bạc Ánh Trăng (Silver Mist)', 'description' => 'Huyền ảo'],
                ],
            ],
        ];
    }

    /**
     * 5 Phong Cách Trang Phục Đám Cưới Điển Hình.
     *
     * @return array<string, array{name: string, description: string, gentlemen: string, ladies: string}>
     */
    public static function getAttireStyles(): array
    {
        return [
            'semi_formal' => [
                'name' => 'Thanh Lịch & Sang Trọng (Semi-Formal / Cocktail)',
                'description' => 'Phong cách phổ biến và trang nhã nhất, kết hợp giữa sự lịch sự và thoải mái dự tiệc.',
                'gentlemen' => 'Bộ suit complet, áo sơ mi sáng màu, quần âu, có thể thắt cà vạt hoặc blazer trẻ trung.',
                'ladies' => 'Váy cocktail dài qua gối, đầm dạ hội thanh lịch hoặc áo dài cách tân mềm mại.',
            ],
            'formal_black_tie' => [
                'name' => 'Dạ Tiệc Hoàng Gia (Black Tie / Formal)',
                'description' => 'Quy chuẩn trang trọng bậc nhất dành cho hôn lễ tại khách sạn 5 sao cao cấp hoặc sảnh đại tiệc.',
                'gentlemen' => 'Tuxedo hoặc suit đen/xanh navy tối màu, áo sơ mi trắng tinh, nơ bướm hoặc cà vạt lụa cao cấp.',
                'ladies' => 'Đầm dạ hội dáng dài chạm đất (Floor-length gown), trang sức tinh xảo quý phái.',
            ],
            'smart_casual' => [
                'name' => 'Thư Thái & Tinh Tế (Smart Casual / Garden Party)',
                'description' => 'Phong cách trẻ trung cho tiệc cưới ngoài trời, thân mật bên gia đình và bạn bè thân thiết.',
                'gentlemen' => 'Áo sơ mi linen, quần chinos hoặc kaki, giày lười da lịch sự (không mặc quần short, áo thun).',
                'ladies' => 'Đầm maxi nhẹ nhàng, váy hoa nhã nhặn, giày gót thấp hoặc đế xuồng thuận tiện di chuyển trên cỏ.',
            ],
            'traditional_ao_dai' => [
                'name' => 'Áo Dài Truyền Thống / Cổ Phục Việt Nam',
                'description' => 'Tôn vinh nét đẹp văn hóa dân tộc trong nghi lễ gia tiên và hôn lễ truyền thống.',
                'gentlemen' => 'Áo dài nam truyền thống hoặc veston lịch lãm.',
                'ladies' => 'Áo dài lụa tơ tằm, áo ngũ thân hoặc cổ phục truyền thống trang nhã.',
            ],
            'resort_chic' => [
                'name' => 'Nghỉ Dưỡng Sang Trọng (Resort Chic / Beach Wedding)',
                'description' => 'Phong cách tiệc cưới bãi biển, đồi núi lãng mạn, nhẹ mát nhưng vẫn giữ trọn nét thanh lịch.',
                'gentlemen' => 'Sơ mi chất liệu đũi/linen, quần âu sáng màu, gam màu be, cát hoặc xanh biển.',
                'ladies' => 'Váy xòe bồng bềnh chất liệu tơ, voan lụa nhẹ bay trong gió biển, màu sắc tươi sáng nhã nhặn.',
            ],
        ];
    }

    /**
     * Cấu hình mặc định cho Dresscode thiệp cưới.
     *
     * @return array<string, mixed>
     */
    public static function getDefaultConfig(?Invitation $invitation = null): array
    {
        $presets = self::getPresetPalettes();
        $defaultPresetKey = 'quiet_luxury_neutral';
        $defaultPreset = $presets[$defaultPresetKey];

        $wedding = $invitation?->wedding;
        $legacyColors = $wedding?->extra_data['dresscode_colors'] ?? null;
        $legacyNote = $wedding?->extra_data['dresscode_note'] ?? null;

        $colors = $defaultPreset['colors'];
        if (! empty($legacyColors) && is_array($legacyColors)) {
            $parsedColors = [];
            foreach ($legacyColors as $idx => $c) {
                if (is_string($c)) {
                    $parsedColors[] = [
                        'hex' => $c,
                        'name' => 'Tone màu '.($idx + 1),
                        'description' => 'Gợi ý phối đồ',
                    ];
                } elseif (is_array($c) && ! empty($c['hex'])) {
                    $parsedColors[] = [
                        'hex' => $c['hex'],
                        'name' => $c['name'] ?? ('Tone '.($idx + 1)),
                        'description' => $c['description'] ?? '',
                    ];
                }
            }
            if (! empty($parsedColors)) {
                $colors = $parsedColors;
            }
        }

        return [
            'enabled' => true,
            'title' => 'Quy Chuẩn Trang Phục (Dress Code)',
            'subtitle' => 'Bảng màu gợi ý để những bức hình kỷ niệm của chúng ta thêm phần hòa hợp & đồng điệu',
            'preset_key' => $defaultPresetKey,
            'palette_name' => $defaultPreset['name'],
            'attire_style' => 'semi_formal',
            'colors' => $colors,
            'note' => $legacyNote ?: 'Gia đình rất vinh hạnh được đón tiếp quý khách trong trang phục trang nhã theo bảng màu gợi ý. Xin vui lòng tránh trang phục màu đỏ tươi hoặc quá sặc sỡ để cô dâu chú rể được nổi bật nhất.',
            'gentlemen_guide' => 'Vest / Suit lịch thiệp, sơ mi sáng màu, quần âu tối màu.',
            'ladies_guide' => 'Váy dạ hội, đầm dài trang nhã hoặc áo dài truyền thống.',
            'avoid_colors_note' => 'Hạn chế trang phục màu đỏ tươi, dạ quang hoặc quần ngắn, áo thun.',
            'moodboard_image_url' => null,
        ];
    }

    /**
     * Phân giải cấu hình Dresscode hoàn chỉnh từ thiệp cưới.
     *
     * @return array<string, mixed>
     */
    public static function resolveDresscodeConfig(Invitation $invitation): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $default = self::getDefaultConfig($invitation);
        $saved = $customConfig['dresscode'] ?? [];

        $enabled = isset($saved['enabled']) ? (bool) $saved['enabled'] : $default['enabled'];
        $title = ! empty($saved['title']) ? (string) $saved['title'] : $default['title'];
        $subtitle = ! empty($saved['subtitle']) ? (string) $saved['subtitle'] : $default['subtitle'];
        $attireStyle = ! empty($saved['attire_style']) ? (string) $saved['attire_style'] : $default['attire_style'];
        $presetKey = ! empty($saved['preset_key']) ? (string) $saved['preset_key'] : $default['preset_key'];
        $paletteName = ! empty($saved['palette_name']) ? (string) $saved['palette_name'] : $default['palette_name'];

        $colors = ! empty($saved['colors']) && is_array($saved['colors']) ? $saved['colors'] : $default['colors'];
        $note = ! empty($saved['note']) ? (string) $saved['note'] : $default['note'];
        $gentlemenGuide = ! empty($saved['gentlemen_guide']) ? (string) $saved['gentlemen_guide'] : $default['gentlemen_guide'];
        $ladiesGuide = ! empty($saved['ladies_guide']) ? (string) $saved['ladies_guide'] : $default['ladies_guide'];
        $avoidNote = ! empty($saved['avoid_colors_note']) ? (string) $saved['avoid_colors_note'] : $default['avoid_colors_note'];
        $moodboardUrl = ! empty($saved['moodboard_image_url']) ? (string) $saved['moodboard_image_url'] : $default['moodboard_image_url'];

        $styles = self::getAttireStyles();
        $styleInfo = $styles[$attireStyle] ?? $styles['semi_formal'];

        return [
            'enabled' => $enabled,
            'title' => $title,
            'subtitle' => $subtitle,
            'preset_key' => $presetKey,
            'palette_name' => $paletteName,
            'attire_style' => $attireStyle,
            'style_name' => $styleInfo['name'],
            'style_description' => $styleInfo['description'],
            'colors' => $colors,
            'note' => $note,
            'gentlemen_guide' => $gentlemenGuide,
            'ladies_guide' => $ladiesGuide,
            'avoid_colors_note' => $avoidNote,
            'moodboard_image_url' => $moodboardUrl,
        ];
    }

    /**
     * Cập nhật cấu hình Dresscode vào thiệp cưới và đồng bộ dữ liệu đám cưới.
     *
     * @param  array<string, mixed>  $data
     */
    public static function updateDresscode(Invitation $invitation, array $data): Invitation
    {
        $current = self::resolveDresscodeConfig($invitation);

        $enabled = isset($data['enabled']) ? (bool) $data['enabled'] : true;
        $title = ! empty($data['title']) ? trim((string) $data['title']) : $current['title'];
        $subtitle = ! empty($data['subtitle']) ? trim((string) $data['subtitle']) : $current['subtitle'];
        $attireStyle = ! empty($data['attire_style']) ? trim((string) $data['attire_style']) : $current['attire_style'];
        $presetKey = ! empty($data['preset_key']) ? trim((string) $data['preset_key']) : $current['preset_key'];

        $presets = self::getPresetPalettes();
        $paletteName = $presets[$presetKey]['name'] ?? ($data['palette_name'] ?? $current['palette_name']);

        // Xử lý danh sách màu
        $colors = [];
        if (! empty($data['colors']) && is_array($data['colors'])) {
            foreach ($data['colors'] as $c) {
                if (! empty($c['hex'])) {
                    $colors[] = [
                        'hex' => strtoupper(trim((string) $c['hex'])),
                        'name' => trim((string) ($c['name'] ?? 'Màu trang phục')),
                        'description' => trim((string) ($c['description'] ?? '')),
                    ];
                }
            }
        }
        if (empty($colors)) {
            $colors = $current['colors'];
        }

        $note = ! empty($data['note']) ? trim((string) $data['note']) : $current['note'];
        $gentlemenGuide = ! empty($data['gentlemen_guide']) ? trim((string) $data['gentlemen_guide']) : $current['gentlemen_guide'];
        $ladiesGuide = ! empty($data['ladies_guide']) ? trim((string) $data['ladies_guide']) : $current['ladies_guide'];
        $avoidNote = ! empty($data['avoid_colors_note']) ? trim((string) $data['avoid_colors_note']) : $current['avoid_colors_note'];
        $moodboardUrl = array_key_exists('moodboard_image_url', $data) ? $data['moodboard_image_url'] : $current['moodboard_image_url'];

        $dresscodeConfig = [
            'enabled' => $enabled,
            'title' => $title,
            'subtitle' => $subtitle,
            'preset_key' => $presetKey,
            'palette_name' => $paletteName,
            'attire_style' => $attireStyle,
            'colors' => $colors,
            'note' => $note,
            'gentlemen_guide' => $gentlemenGuide,
            'ladies_guide' => $ladiesGuide,
            'avoid_colors_note' => $avoidNote,
            'moodboard_image_url' => $moodboardUrl,
        ];

        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $customConfig['dresscode'] = $dresscodeConfig;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        // Đồng bộ ngược vào wedding->extra_data cho các module báo cáo và mẫu thiệp cũ
        $wedding = $invitation->wedding;
        if ($wedding) {
            $extra = is_array($wedding->extra_data) ? $wedding->extra_data : [];
            $extra['dresscode_colors'] = array_column($colors, 'hex');
            $extra['dresscode_note'] = $note;
            $wedding->extra_data = $extra;
            $wedding->save();
        }

        AuditLogger::log(
            'DRESSCODE_UPDATED',
            'Invitation',
            $invitation->id,
            "Cập nhật bảng màu quy chuẩn trang phục dresscode cho thiệp #{$invitation->id}",
            [
                'invitation_id' => $invitation->id,
                'attire_style' => $attireStyle,
                'palette_name' => $paletteName,
                'colors_count' => count($colors),
            ]
        );

        return $invitation;
    }

    /**
     * Tải lên tệp ảnh moodboard gợi ý trang phục cho khách.
     */
    public static function uploadMoodboardImage(Invitation $invitation, UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = 'moodboard-'.Str::random(16).'.'.$extension;
        $directory = 'invitations/'.$invitation->id.'/dresscode';

        $path = $file->storeAs($directory, $filename, 'public');
        $url = Storage::disk('public')->url($path);

        $config = self::resolveDresscodeConfig($invitation);
        $config['moodboard_image_url'] = $url;

        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $customConfig['dresscode'] = $config;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        AuditLogger::log(
            'DRESSCODE_MOODBOARD_UPLOADED',
            'Invitation',
            $invitation->id,
            "Tải lên ảnh moodboard trang phục cho thiệp #{$invitation->id}",
            ['url' => $url]
        );

        return $url;
    }
}
