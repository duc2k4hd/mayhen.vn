<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Models\WeddingTable;

class GuestTableFinderService
{
    /**
     * Cấu hình mặc định cho tính năng Tra cứu bàn tiệc.
     *
     * @return array<string, mixed>
     */
    public static function getDefaultConfig(): array
    {
        return [
            'is_enabled' => true,
            'title' => 'Vị Trí Bàn Tiệc Của Quý Khách',
            'subtitle' => 'Tra cứu vị trí bàn tiệc và khu vực an tọa để buổi tiệc thêm phần chu đáo, ấm cúng',
            'show_table_name' => true,
            'show_table_zone' => true,
            'show_table_mates' => true,
            'allow_search_by_phone' => true,
            'welcome_message' => 'Kính mời Quý khách an tọa tại vị trí bàn tiệc đã chuẩn bị riêng, cùng nâng ly chung vui và chia sẻ khoảnh khắc ngọt ngào với gia đình!',
            'empty_result_message' => 'Chưa tìm thấy thông tin sắp bàn tương ứng. Quý khách vui lòng liên hệ Ban Lễ Tân tại sảnh tiệc để được hướng dẫn chu đáo nhất.',
        ];
    }

    /**
     * Lấy cấu hình đầy đủ của thiệp cưới.
     *
     * @return array<string, mixed>
     */
    public function getConfig(Invitation $invitation): array
    {
        $custom = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        return array_merge(self::getDefaultConfig(), (array) ($custom['table_finder'] ?? []));
    }

    /**
     * Cập nhật cấu hình Tra cứu bàn tiệc trong Studio.
     *
     * @param  array<string, mixed>  $params
     */
    public function updateConfig(Invitation $invitation, array $params): array
    {
        $custom = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $current = array_merge(self::getDefaultConfig(), (array) ($custom['table_finder'] ?? []));

        $updated = [
            'is_enabled' => (bool) ($params['is_enabled'] ?? $current['is_enabled']),
            'title' => trim((string) ($params['title'] ?? $current['title'])),
            'subtitle' => trim((string) ($params['subtitle'] ?? $current['subtitle'])),
            'show_table_name' => (bool) ($params['show_table_name'] ?? $current['show_table_name']),
            'show_table_zone' => (bool) ($params['show_table_zone'] ?? $current['show_table_zone']),
            'show_table_mates' => (bool) ($params['show_table_mates'] ?? $current['show_table_mates']),
            'allow_search_by_phone' => (bool) ($params['allow_search_by_phone'] ?? $current['allow_search_by_phone']),
            'welcome_message' => trim((string) ($params['welcome_message'] ?? $current['welcome_message'])),
            'empty_result_message' => trim((string) ($params['empty_result_message'] ?? $current['empty_result_message'])),
        ];

        $custom['table_finder'] = $updated;
        $invitation->custom_config = $custom;
        $invitation->save();

        return $updated;
    }

    /**
     * Thống kê hiện trạng sắp xếp bàn tiệc cho Studio.
     *
     * @return array{
     *     total_tables: int,
     *     total_capacity: int,
     *     assigned_tables_count: int,
     *     total_guests: int,
     *     seated_guests_count: int,
     *     unseated_guests_count: int,
     *     seating_rate: float,
     *     tables: array<int, array<string, mixed>>
     * }
     */
    public function getSeatingOverview(Invitation $invitation): array
    {
        $wedding = $invitation->wedding ?? Wedding::find($invitation->wedding_id);

        if (! $wedding) {
            return [
                'total_tables' => 0,
                'total_capacity' => 0,
                'assigned_tables_count' => 0,
                'total_guests' => 0,
                'seated_guests_count' => 0,
                'unseated_guests_count' => 0,
                'seating_rate' => 0.0,
                'tables' => [],
            ];
        }

        $tables = $wedding->tables()->with('guests')->orderBy('display_order')->get();
        $totalTables = $tables->count();
        $totalCapacity = (int) $tables->sum('capacity');

        $assignedTablesCount = $tables->filter(fn ($t) => $t->guests->count() > 0)->count();

        $allGuests = $wedding->guests()->get();
        $totalGuests = $allGuests->count();
        $seatedGuestsCount = $allGuests->filter(fn ($g) => ! empty($g->table_id) || ! empty($g->table_name))->count();
        $unseatedGuestsCount = max(0, $totalGuests - $seatedGuestsCount);
        $seatingRate = $totalGuests > 0 ? round(($seatedGuestsCount / $totalGuests) * 100, 1) : 0.0;

        $tableSummaries = $tables->map(function (WeddingTable $table) {
            return [
                'id' => $table->id,
                'name' => $table->name,
                'zone' => $table->zone ?? 'Sảnh Chính',
                'capacity' => $table->capacity,
                'assigned_count' => $table->guests->count(),
                'is_over_capacity' => $table->guests->count() > $table->capacity,
                'guests_preview' => $table->guests->take(5)->pluck('name')->toArray(),
            ];
        })->toArray();

        return [
            'total_tables' => $totalTables,
            'total_capacity' => $totalCapacity,
            'assigned_tables_count' => $assignedTablesCount,
            'total_guests' => $totalGuests,
            'seated_guests_count' => $seatedGuestsCount,
            'unseated_guests_count' => $unseatedGuestsCount,
            'seating_rate' => $seatingRate,
            'tables' => $tableSummaries,
        ];
    }

    /**
     * Tra cứu vị trí bàn tiệc của khách mời theo token cá nhân hóa.
     *
     * @return array<string, mixed>|null
     */
    public function findSeatingByGuestToken(Invitation $invitation, string $guestToken): ?array
    {
        $wedding = $invitation->wedding ?? Wedding::find($invitation->wedding_id);
        if (! $wedding) {
            return null;
        }

        $guest = Guest::where('wedding_id', $wedding->id)
            ->where('guest_token', $guestToken)
            ->first();

        if (! $guest) {
            return null;
        }

        return $this->formatSeatingResult($guest, $wedding, $this->getConfig($invitation));
    }

    /**
     * Tra cứu vị trí bàn tiệc theo từ khóa (họ tên hoặc số điện thoại) cho khách mở thiệp chung.
     *
     * @return array{
     *     found: bool,
     *     message: string,
     *     results: array<int, array<string, mixed>>
     * }
     */
    public function searchSeatingByKeyword(Invitation $invitation, string $keyword): array
    {
        $config = $this->getConfig($invitation);

        if (! $config['is_enabled']) {
            return [
                'found' => false,
                'message' => 'Tính năng tra cứu vị trí bàn tiệc đang tạm đóng.',
                'results' => [],
            ];
        }

        $wedding = $invitation->wedding ?? Wedding::find($invitation->wedding_id);
        if (! $wedding) {
            return [
                'found' => false,
                'message' => 'Không tìm thấy hồ sơ đám cưới tương ứng.',
                'results' => [],
            ];
        }

        $cleanKeyword = trim($keyword);
        if (mb_strlen($cleanKeyword) < 2) {
            return [
                'found' => false,
                'message' => 'Vui lòng nhập tối thiểu 2 ký tự tên hoặc 4 số cuối điện thoại.',
                'results' => [],
            ];
        }

        // Tìm kiếm khách mời theo Tên hoặc Số Điện Thoại
        $query = Guest::where('wedding_id', $wedding->id);

        $isDigits = ctype_digit($cleanKeyword);
        if ($isDigits && $config['allow_search_by_phone']) {
            $query->where('phone', 'like', "%{$cleanKeyword}%");
        } else {
            $query->where('name', 'like', "%{$cleanKeyword}%");
        }

        $guests = $query->take(6)->get();

        if ($guests->isEmpty()) {
            return [
                'found' => false,
                'message' => $config['empty_result_message'],
                'results' => [],
            ];
        }

        $results = [];
        foreach ($guests as $guest) {
            $formatted = $this->formatSeatingResult($guest, $wedding, $config);
            if ($formatted) {
                $results[] = $formatted;
            }
        }

        if (empty($results)) {
            return [
                'found' => false,
                'message' => $config['empty_result_message'],
                'results' => [],
            ];
        }

        return [
            'found' => true,
            'message' => 'Đã tìm thấy vị trí bàn tiệc của Quý Khách!',
            'results' => $results,
        ];
    }

    /**
     * Định dạng kết quả bàn tiệc kèm bạn cùng bàn và bảo vệ dữ liệu cá nhân.
     *
     * @return array<string, mixed>|null
     */
    protected function formatSeatingResult(Guest $guest, Wedding $wedding, array $config): ?array
    {
        $table = null;
        if ($guest->table_id) {
            $table = WeddingTable::find($guest->table_id);
        } elseif (! empty($guest->table_name)) {
            $table = WeddingTable::where('wedding_id', $wedding->id)
                ->where('name', trim((string) $guest->table_name))
                ->first();
        }

        $tableName = $table?->name ?? ($guest->table_name ?: 'Chưa phân bàn cụ thể');
        $zoneName = $table?->zone ?? 'Sảnh Chính';
        $tableNotes = $table?->notes ?? null;

        $tableMates = [];
        if ($config['show_table_mates'] && $table) {
            $mates = Guest::where('wedding_id', $wedding->id)
                ->where('table_id', $table->id)
                ->where('id', '!=', $guest->id)
                ->take(12)
                ->get();

            foreach ($mates as $mate) {
                $tableMates[] = [
                    'name' => $mate->name,
                    'salutation' => $mate->salutation,
                    'group_name' => $mate->group_name,
                ];
            }
        }

        return [
            'guest_id' => $guest->id,
            'guest_name' => $guest->name,
            'salutation' => $guest->salutation,
            'group_name' => $guest->group_name,
            'table_id' => $table?->id,
            'table_name' => $config['show_table_name'] ? $tableName : null,
            'zone' => $config['show_table_zone'] ? $zoneName : null,
            'table_notes' => $tableNotes,
            'table_mates' => $tableMates,
            'welcome_message' => $config['welcome_message'],
            'is_seated' => ! empty($table) || ! empty($guest->table_name),
        ];
    }
}

