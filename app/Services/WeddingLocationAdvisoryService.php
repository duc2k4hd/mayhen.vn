<?php

namespace App\Services;

use App\Models\Invitation;

class WeddingLocationAdvisoryService
{
    /**
     * 6 Trạng Thái Thời Tiết Điển Hình Hôn Lễ Tại Việt Nam.
     *
     * @return array<string, array{name: string, icon: string, temp_default: int, description: string}>
     */
    public static function getPresetWeatherConditions(): array
    {
        return [
            'sunny_clear' => [
                'name' => 'Nắng Ấm & Trời Quang Đãng',
                'icon' => '☀️',
                'temp_default' => 28,
                'description' => 'Tiết trời nắng ấm rực rỡ, không khí thoáng đãng lý tưởng cho tiệc cưới ngoài trời và lưu giữ những bức ảnh hoàng hôn tráng lệ.',
            ],
            'breezy_cool' => [
                'name' => 'Gió Mát Thu & Dịu Nhẹ',
                'icon' => '🍃',
                'temp_default' => 24,
                'description' => 'Gió thu thanh mát dễ chịu, quý khách có thể thoải mái diện trang phục dạ tiệc thanh lịch và di chuyển thong thả.',
            ],
            'romantic_chill' => [
                'name' => 'Se Lạnh Lãng Mạn (Mùa Đông / Cao Nguyên)',
                'icon' => '🧣',
                'temp_default' => 18,
                'description' => 'Nhiệt độ se lạnh về chiều tối, quý khách nên mang theo áo khoác nhẹ hoặc khăn choàng ấm để giữ ấm cơ thể.',
            ],
            'sunset_golden' => [
                'name' => 'Hoàng Hôn Nắng Vàng (Golden Hour)',
                'icon' => '🌅',
                'temp_default' => 27,
                'description' => 'Thời khắc hoàng hôn vàng rực rỡ, gió chiều mát rượi, không khí ấm cúng và ngập tràn cảm xúc lãng mạn.',
            ],
            'coastal_breeze' => [
                'name' => 'Gió Biển Nghỉ Dưỡng Mát Lành',
                'icon' => '🌊',
                'temp_default' => 26,
                'description' => 'Gió biển nhẹ nhàng trong lành, quý khách nên ưu tiên trang phục chất liệu lụa/linen nhẹ mát và giày đế thấp thuận tiện dạo bờ cát.',
            ],
            'light_drizzle' => [
                'name' => 'Mưa Bay Lãng Mạn / Có Mái Che Lụa',
                'icon' => '🌧️',
                'temp_default' => 23,
                'description' => 'Tiết trời có mưa lất phất may mắn. Ban tổ chức đã chuẩn bị sẵn ô che trong suốt và lối đi mái vòm lụa đón tiếp chu đáo.',
            ],
        ];
    }

    /**
     * Cấu hình mặc định cho phân hệ Chỉ dẫn địa điểm, Bãi đỗ xe & Thời tiết.
     *
     * @return array<string, mixed>
     */
    public static function getDefaultConfig(?Invitation $invitation = null): array
    {
        $wedding = $invitation?->wedding;

        return [
            'enabled' => true,
            'title' => 'Chỉ Dẫn Địa Điểm &amp; Bãi Đỗ Xe',
            'subtitle' => 'Thông tin dẫn đường chi tiết, vị trí đỗ xe và lưu ý thời tiết để quý khách thuận tiện di chuyển',
            'venue_name' => $wedding->venue_name ?? 'Trung Tâm Tiệc Cưới',
            'venue_address' => $wedding->venue_address ?? 'TP. Hồ Chí Minh, Việt Nam',
            'map_url' => $wedding?->extra_data['event_party_map_url'] ?? '',
            'latitude' => null,
            'longitude' => null,
            'reception_phone' => $wedding?->extra_data['groom_phone'] ?? ($wedding?->extra_data['bride_phone'] ?? ''),
            'reception_coordinator' => 'Ban Lễ Tân Đón Tiếp',
            'parking' => [
                'car_parking' => 'Bãi đỗ xe ô tô tầng hầm sảnh tiệc (Miễn phí vé gửi xe cho toàn thể khách mời tham dự)',
                'bike_parking' => 'Bãi đỗ xe máy bên hông sảnh chính, có đội ngũ bảo vệ trông giữ an toàn 24/7',
                'accessibility_note' => 'Có thang máy và dốc di chuyển riêng cho xe lăn, người lớn tuổi và trẻ nhỏ tại cửa số 1',
            ],
            'weather' => [
                'enabled' => true,
                'condition_key' => 'sunny_clear',
                'temperature_celsius' => 28,
                'advisory_note' => 'Thời tiết ngày cưới dự kiến nắng ấm ráo. Ban lễ tân đã chuẩn bị sẵn ô che lụa và nước mát đón tiếp tại sảnh.',
            ],
        ];
    }

    /**
     * Phân giải cấu hình hoàn chỉnh cho thiệp cưới.
     *
     * @return array<string, mixed>
     */
    public static function resolveAdvisoryConfig(Invitation $invitation): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $default = self::getDefaultConfig($invitation);
        $saved = $customConfig['location_advisory'] ?? [];

        $enabled = isset($saved['enabled']) ? (bool) $saved['enabled'] : $default['enabled'];
        $title = ! empty($saved['title']) ? (string) $saved['title'] : $default['title'];
        $subtitle = ! empty($saved['subtitle']) ? (string) $saved['subtitle'] : $default['subtitle'];

        $venueName = ! empty($saved['venue_name']) ? (string) $saved['venue_name'] : $default['venue_name'];
        $venueAddress = ! empty($saved['venue_address']) ? (string) $saved['venue_address'] : $default['venue_address'];
        $mapUrl = ! empty($saved['map_url']) ? (string) $saved['map_url'] : $default['map_url'];
        $lat = ! empty($saved['latitude']) ? (float) $saved['latitude'] : null;
        $lng = ! empty($saved['longitude']) ? (float) $saved['longitude'] : null;

        $phone = ! empty($saved['reception_phone']) ? (string) $saved['reception_phone'] : $default['reception_phone'];
        $coordinator = ! empty($saved['reception_coordinator']) ? (string) $saved['reception_coordinator'] : $default['reception_coordinator'];

        $parking = array_merge($default['parking'], (array) ($saved['parking'] ?? []));
        $weather = array_merge($default['weather'], (array) ($saved['weather'] ?? []));

        $weatherPresets = self::getPresetWeatherConditions();
        $conditionKey = $weather['condition_key'] ?? 'sunny_clear';
        $weatherInfo = $weatherPresets[$conditionKey] ?? $weatherPresets['sunny_clear'];

        return [
            'enabled' => $enabled,
            'title' => $title,
            'subtitle' => $subtitle,
            'venue_name' => $venueName,
            'venue_address' => $venueAddress,
            'map_url' => $mapUrl,
            'latitude' => $lat,
            'longitude' => $lng,
            'reception_phone' => $phone,
            'reception_coordinator' => $coordinator,
            'parking' => $parking,
            'weather' => array_merge($weather, [
                'condition_name' => $weatherInfo['name'],
                'icon' => $weatherInfo['icon'],
            ]),
        ];
    }

    /**
     * Cập nhật cấu hình Chỉ dẫn địa điểm, Bãi đỗ xe & Thời tiết vào thiệp cưới.
     *
     * @param  array<string, mixed>  $data
     */
    public static function updateAdvisoryConfig(Invitation $invitation, array $data): Invitation
    {
        $current = self::resolveAdvisoryConfig($invitation);

        $enabled = isset($data['enabled']) ? (bool) $data['enabled'] : true;
        $title = ! empty($data['title']) ? trim((string) $data['title']) : $current['title'];
        $subtitle = ! empty($data['subtitle']) ? trim((string) $data['subtitle']) : $current['subtitle'];
        $venueName = ! empty($data['venue_name']) ? trim((string) $data['venue_name']) : $current['venue_name'];
        $venueAddress = ! empty($data['venue_address']) ? trim((string) $data['venue_address']) : $current['venue_address'];
        $mapUrl = ! empty($data['map_url']) ? trim((string) $data['map_url']) : $current['map_url'];

        $lat = ! empty($data['latitude']) ? (float) $data['latitude'] : null;
        $lng = ! empty($data['longitude']) ? (float) $data['longitude'] : null;
        $phone = ! empty($data['reception_phone']) ? trim((string) $data['reception_phone']) : '';
        $coordinator = ! empty($data['reception_coordinator']) ? trim((string) $data['reception_coordinator']) : 'Ban Lễ Tân';

        $parking = [
            'car_parking' => ! empty($data['car_parking']) ? trim((string) $data['car_parking']) : $current['parking']['car_parking'],
            'bike_parking' => ! empty($data['bike_parking']) ? trim((string) $data['bike_parking']) : $current['parking']['bike_parking'],
            'accessibility_note' => ! empty($data['accessibility_note']) ? trim((string) $data['accessibility_note']) : $current['parking']['accessibility_note'],
        ];

        $weatherEnabled = ! empty($data['weather_enabled']);
        $conditionKey = ! empty($data['condition_key']) ? trim((string) $data['condition_key']) : 'sunny_clear';
        $temp = ! empty($data['temperature_celsius']) ? (int) $data['temperature_celsius'] : 28;
        $advisoryNote = ! empty($data['advisory_note']) ? trim((string) $data['advisory_note']) : $current['weather']['advisory_note'];

        $advisoryConfig = [
            'enabled' => $enabled,
            'title' => $title,
            'subtitle' => $subtitle,
            'venue_name' => $venueName,
            'venue_address' => $venueAddress,
            'map_url' => $mapUrl,
            'latitude' => $lat,
            'longitude' => $lng,
            'reception_phone' => $phone,
            'reception_coordinator' => $coordinator,
            'parking' => $parking,
            'weather' => [
                'enabled' => $weatherEnabled,
                'condition_key' => $conditionKey,
                'temperature_celsius' => $temp,
                'advisory_note' => $advisoryNote,
            ],
        ];

        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $customConfig['location_advisory'] = $advisoryConfig;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        // Đồng bộ ngược địa chỉ vào wedding nếu cần
        $wedding = $invitation->wedding;
        if ($wedding && (! empty($venueAddress) || ! empty($venueName))) {
            if (! empty($venueName)) {
                $wedding->venue_name = $venueName;
            }
            if (! empty($venueAddress)) {
                $wedding->venue_address = $venueAddress;
            }
            $wedding->save();
        }

        AuditLogger::log(
            'LOCATION_ADVISORY_UPDATED',
            'Invitation',
            $invitation->id,
            "Cập nhật chỉ dẫn địa điểm, bãi đỗ xe và thời tiết hôn lễ cho thiệp #{$invitation->id}",
            [
                'invitation_id' => $invitation->id,
                'venue_name' => $venueName,
                'condition_key' => $conditionKey,
            ]
        );

        return $invitation;
    }

    /**
     * Tạo đường dẫn điều hướng bản đồ đa ứng dụng (Google Maps, Apple Maps, Grab, Taxi).
     *
     * @return array<string, string>
     */
    public static function generateNavigationLinks(Invitation $invitation): array
    {
        $config = self::resolveAdvisoryConfig($invitation);
        $venueQuery = urlencode($config['venue_name'].', '.$config['venue_address']);

        // 1. Google Maps
        $googleMapsUrl = ! empty($config['map_url'])
            ? $config['map_url']
            : "https://www.google.com/maps/search/?api=1&query={$venueQuery}";

        if ($config['latitude'] && $config['longitude']) {
            $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query={$config['latitude']},{$config['longitude']}";
        }

        // 2. Apple Maps
        $appleMapsUrl = "https://maps.apple.com/?q={$venueQuery}";
        if ($config['latitude'] && $config['longitude']) {
            $appleMapsUrl = "https://maps.apple.com/?ll={$config['latitude']},{$config['longitude']}&q=".urlencode($config['venue_name']);
        }

        // 3. Grab Deeplink (mở ứng dụng Grab đặt xe tới địa điểm)
        $grabUrl = 'https://grab.onelink.me/2695613898?pid=web&c=mayhen_wedding&drop_off_name='.urlencode($config['venue_name']);
        if ($config['latitude'] && $config['longitude']) {
            $grabUrl .= "&drop_off_latitude={$config['latitude']}&drop_off_longitude={$config['longitude']}";
        }

        // 4. Hotline Taxi Mai Linh / Vinasun
        $taxiMaiLinh = 'tel:1055';
        $taxiVinasun = 'tel:02838272727';

        return [
            'google_maps' => $googleMapsUrl,
            'apple_maps' => $appleMapsUrl,
            'grab' => $grabUrl,
            'taxi_mailinh' => $taxiMaiLinh,
            'taxi_vinasun' => $taxiVinasun,
        ];
    }
}
