<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Support\Str;

class VenueWelcomeKioskService
{
    /**
     * Lấy cấu hình Màn hình Chào mừng Lễ tân, tự động khởi tạo mặc định.
     *
     * @return array{
     *     enabled: bool,
     *     kiosk_title: string,
     *     welcome_duration_seconds: int,
     *     sound_enabled: bool,
     *     show_table_name: bool,
     *     show_countdown: bool,
     *     welcome_quote: string,
     *     access_pin: string,
     *     photos: array<int, string>
     * }
     */
    /**
     * Lấy cấu hình Kiosk hiện tại (Static Helper).
     */
    public static function getKioskConfig(Invitation $invitation): array
    {
        return (new static)->getConfig($invitation);
    }

    /**
     * Cập nhật cấu hình Kiosk (Static Helper).
     */
    public static function setKioskConfig(Invitation $invitation, array $data): array
    {
        return (new static)->updateConfig($invitation, $data);
    }

    public function getConfig(Invitation $invitation): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $kioskConfig = $customConfig['welcome_kiosk'] ?? [];
        $wedding = $invitation->wedding;

        // Thu thập ảnh cưới mặc định nếu chưa cấu hình
        $defaultPhotos = [];
        if ($wedding) {
            if ($wedding->cover_photo_url) {
                $defaultPhotos[] = $wedding->cover_photo_url;
            }
            $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?: []);
            if (! empty($extra['hero_photo_url'])) {
                $defaultPhotos[] = $extra['hero_photo_url'];
            }
            if (! empty($customConfig['gallery']['photos']) && is_array($customConfig['gallery']['photos'])) {
                foreach ($customConfig['gallery']['photos'] as $item) {
                    $url = is_array($item) ? ($item['url'] ?? null) : $item;
                    if ($url && ! in_array($url, $defaultPhotos, true)) {
                        $defaultPhotos[] = $url;
                    }
                }
            }
        }

        if (empty($defaultPhotos)) {
            $defaultPhotos = ['/assets/templates/celestial.jpg'];
        }

        return [
            'enabled' => (bool) ($kioskConfig['enabled'] ?? true),
            'kiosk_title' => (string) ($kioskConfig['kiosk_title'] ?? 'Chào Mừng Quý Khách Tới Dự Lễ Thành Hôn'),
            'welcome_duration_seconds' => max(4, min(30, (int) ($kioskConfig['welcome_duration_seconds'] ?? 8))),
            'sound_enabled' => (bool) ($kioskConfig['sound_enabled'] ?? true),
            'show_table_name' => (bool) ($kioskConfig['show_table_name'] ?? true),
            'show_countdown' => (bool) ($kioskConfig['show_countdown'] ?? true),
            'welcome_quote' => (string) ($kioskConfig['welcome_quote'] ?? 'Sự hiện diện của Quý Khách là niềm vinh hạnh và hạnh phúc lớn lao của gia đình chúng em!'),
            'access_pin' => (string) ($kioskConfig['access_pin'] ?? '1234'),
            'photos' => ! empty($kioskConfig['photos']) && is_array($kioskConfig['photos']) ? $kioskConfig['photos'] : $defaultPhotos,
        ];
    }

    /**
     * Cập nhật cấu hình Kiosk từ Studio của cặp đôi.
     */
    public function updateConfig(Invitation $invitation, array $data): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $current = $this->getConfig($invitation);

        $current['enabled'] = isset($data['enabled']) ? (bool) $data['enabled'] : false;
        if (! empty($data['kiosk_title'])) {
            $current['kiosk_title'] = trim((string) $data['kiosk_title']);
        }
        if (isset($data['welcome_duration_seconds'])) {
            $current['welcome_duration_seconds'] = max(4, min(30, (int) $data['welcome_duration_seconds']));
        }
        $current['sound_enabled'] = isset($data['sound_enabled']) ? (bool) $data['sound_enabled'] : false;
        $current['show_table_name'] = isset($data['show_table_name']) ? (bool) $data['show_table_name'] : false;
        $current['show_countdown'] = isset($data['show_countdown']) ? (bool) $data['show_countdown'] : false;
        if (! empty($data['welcome_quote'])) {
            $current['welcome_quote'] = trim((string) $data['welcome_quote']);
        }
        if (! empty($data['access_pin'])) {
            $current['access_pin'] = trim((string) $data['access_pin']);
        }
        if (isset($data['photos']) && is_array($data['photos'])) {
            $current['photos'] = array_values(array_filter(array_map('trim', $data['photos'])));
        }

        $customConfig['welcome_kiosk'] = $current;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        return $current;
    }

    /**
     * Lấy lượt check-in mới nhất kể từ dấu mốc thời gian (phục vụ realtime polling).
     *
     * @return array{
     *     id: int,
     *     name: string,
     *     salutation: string,
     *     group_name: string,
     *     table_name: string,
     *     attendees_count: int,
     *     checked_in_at: string,
     *     checked_in_formatted: string,
     *     timestamp_ms: int
     * }|null
     */
    public function getLatestCheckInFeed(Wedding $wedding, ?int $sinceTimestampMs = null): ?array
    {
        $query = $wedding->guests()
            ->whereNotNull('checked_in_at')
            ->orderByDesc('checked_in_at');

        if ($sinceTimestampMs) {
            $sinceCarbon = Carbon::createFromTimestampMs($sinceTimestampMs);
            $query->where('checked_in_at', '>', $sinceCarbon);
        }

        $guest = $query->first();
        if (! $guest) {
            return null;
        }

        $attendees = $guest->checked_in_attendees ?: $guest->actualAttendeesCount();
        $carbon = $guest->checked_in_at;

        return [
            'id' => $guest->id,
            'name' => $guest->name,
            'salutation' => $guest->salutation ?: 'Quý khách',
            'group_name' => $guest->group_name ?: 'Quan Khách Quý',
            'table_name' => $guest->table_name ?: 'Chưa xếp bàn',
            'attendees_count' => $attendees,
            'checked_in_at' => $carbon ? $carbon->toIso8601String() : now()->toIso8601String(),
            'checked_in_formatted' => $carbon ? $carbon->format('H:i:s') : now()->format('H:i:s'),
            'timestamp_ms' => $carbon ? (int) $carbon->getTimestampMs() : (int) now()->getTimestampMs(),
        ];
    }

    /**
     * Dữ liệu khởi tạo toàn diện cho Màn hình Kiosk Chào mừng.
     */
    public function getKioskInitData(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        $config = $this->getConfig($invitation);

        $recentGuests = $wedding ? $wedding->guests()
            ->whereNotNull('checked_in_at')
            ->orderByDesc('checked_in_at')
            ->limit(5)
            ->get()
            ->map(fn (Guest $g) => [
                'id' => $g->id,
                'name' => $g->name,
                'salutation' => $g->salutation ?: 'Quý khách',
                'table_name' => $g->table_name ?: 'Chưa xếp bàn',
                'checked_in_time' => $g->checked_in_at ? $g->checked_in_at->format('H:i') : '',
            ])->all() : [];

        $totalCheckedIn = $wedding ? (int) $wedding->guests()->whereNotNull('checked_in_at')->count() : 0;
        $totalGuests = $wedding ? (int) $wedding->guests()->count() : 0;

        $weddingDate = $wedding?->wedding_date ? $wedding->wedding_date->format('Y-m-d') : null;
        $weddingDateFormatted = $wedding?->wedding_date ? $wedding->wedding_date->format('d/m/Y') : '';

        $extra = $wedding && is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) ($wedding?->extra_data ?? '{}'), true) ?: []);
        $partyTime = $extra['party_time'] ?? ($extra['event_party_time'] ?? '11:00');

        return [
            'groom_name' => $wedding?->groom_name ?? 'Chú Rể',
            'bride_name' => $wedding?->bride_name ?? 'Cô Dâu',
            'venue_name' => $wedding?->venue_name ?: ($wedding?->venue_address ?? 'Trung Tâm Tiệc Cưới'),
            'wedding_date' => $weddingDate,
            'wedding_date_formatted' => $weddingDateFormatted,
            'party_time' => $partyTime,
            'config' => $config,
            'recent_guests' => $recentGuests,
            'total_checked_in' => $totalCheckedIn,
            'total_guests' => $totalGuests,
            'server_timestamp_ms' => (int) now()->getTimestampMs(),
        ];
    }

    /**
     * Kích hoạt một lượt check-in mẫu để cặp đôi kiểm tra màn hình Kiosk (Diễn tập thử).
     */
    public function simulateCheckInForTesting(Wedding $wedding): Guest
    {
        $guest = $wedding->guests()->first();

        if (! $guest) {
            $guest = Guest::create([
                'wedding_id' => $wedding->id,
                'name' => 'Nguyễn Hoàng Khang',
                'salutation' => 'Anh',
                'group_name' => 'Bạn Cấp 3',
                'table_name' => 'Bàn 08 (VIP)',
                'expected_attendees' => 2,
                'guest_token' => Str::random(32),
            ]);
        }

        $guest->update([
            'checked_in_at' => now(),
            'checked_in_attendees' => 2,
            'check_in_note' => '[Mô Phỏng Chào Mừng Kiosk]',
        ]);

        return $guest;
    }
}
