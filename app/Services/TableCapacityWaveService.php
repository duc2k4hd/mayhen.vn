<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Wedding;
use App\Models\WeddingTable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TableCapacityWaveService
{
    /**
     * Lấy báo cáo tổng quan tình hình sĩ số bàn tiệc thời gian thực (Sóng đón khách).
     *
     * @return array{
     *     total_tables: int,
     *     total_capacity: int,
     *     total_checked_in: int,
     *     total_expected: int,
     *     venue_occupancy_percent: float,
     *     ready_to_serve_tables: int,
     *     active_tables: int,
     *     empty_tables: int,
     *     overflow_tables: int,
     *     waves_15m: array<string, int>,
     *     server_time: string
     * }
     */
    public function getWaveOverview(Wedding $wedding): array
    {
        $tables = $wedding->tables()->with(['guests'])->get();

        $totalTables = $tables->count();
        $totalCapacity = (int) $tables->sum('capacity');

        $totalCheckedIn = 0;
        $totalExpected = 0;
        $readyToServeCount = 0;
        $activeCount = 0;
        $emptyCount = 0;
        $overflowCount = 0;

        foreach ($tables as $table) {
            $capacity = max(1, (int) $table->capacity);
            $checkedInInTable = 0;
            $expectedInTable = 0;

            foreach ($table->guests as $guest) {
                $actual = (int) ($guest->checked_in_attendees ?: ($guest->expected_attendees ?: 1));
                $expected = (int) ($guest->expected_attendees ?: 1);

                $expectedInTable += $expected;
                if ($guest->checked_in_at !== null) {
                    $checkedInInTable += $actual;
                }
            }

            $totalCheckedIn += $checkedInInTable;
            $totalExpected += $expectedInTable;

            if ($checkedInInTable === 0) {
                $emptyCount++;
            } elseif ($checkedInInTable >= $capacity) {
                if ($checkedInInTable > $capacity) {
                    $overflowCount++;
                } else {
                    $readyToServeCount++;
                }
            } else {
                $activeCount++;
            }
        }

        // Bổ sung số khách check-in nhưng chưa được gán bàn
        $unassignedCheckedIn = (int) $wedding->guests()
            ->whereNull('table_id')
            ->whereNotNull('checked_in_at')
            ->sum('checked_in_attendees');

        $totalCheckedIn += $unassignedCheckedIn;

        $occupancyPercent = $totalCapacity > 0
            ? round(($totalCheckedIn / $totalCapacity) * 100, 1)
            : 0.0;

        // Thống kê sóng khách 15 phút gần nhất (Last 1 hour in 15-minute buckets)
        $waves = $this->calculate15MinuteWaves($wedding);

        return [
            'total_tables' => $totalTables,
            'total_capacity' => $totalCapacity,
            'total_checked_in' => $totalCheckedIn,
            'total_expected' => $totalExpected,
            'venue_occupancy_percent' => min(100.0, $occupancyPercent),
            'ready_to_serve_tables' => $readyToServeCount,
            'active_tables' => $activeCount,
            'empty_tables' => $emptyCount,
            'overflow_tables' => $overflowCount,
            'waves_15m' => $waves,
            'server_time' => now()->format('H:i:s'),
        ];
    }

    /**
     * Lấy danh sách chi tiết từng bàn tiệc kèm trạng thái lấp đầy và danh sách khách.
     *
     * @return Collection<int, array{
     *     id: int,
     *     name: string,
     *     zone: string,
     *     capacity: int,
     *     checked_in_count: int,
     *     expected_count: int,
     *     available_seats: int,
     *     overflow_count: int,
     *     occupancy_percent: float,
     *     status: string,
     *     status_label: string,
     *     status_color: string,
     *     guests: array<int, array{
     *         id: int,
     *         name: string,
     *         salutation: string,
     *         attendees: int,
     *         is_checked_in: bool,
     *         checked_in_at: string|null
     *     }>
     * }>
     */
    public function getTablesDetail(Wedding $wedding, ?string $filterZone = null, ?string $filterStatus = null): Collection
    {
        $query = $wedding->tables()->with(['guests' => function ($q) {
            $q->orderBy('is_checked_in', 'desc')->orderBy('name', 'asc');
        }])->orderBy('display_order', 'asc')->orderBy('id', 'asc');

        if (! empty($filterZone)) {
            $query->where('zone', $filterZone);
        }

        $tables = $query->get();

        $result = $tables->map(function (WeddingTable $table) {
            $capacity = max(1, (int) $table->capacity);
            $checkedInCount = 0;
            $expectedCount = 0;
            $guestItems = [];

            foreach ($table->guests as $guest) {
                $isCheckedIn = $guest->checked_in_at !== null;
                $attendees = (int) ($guest->checked_in_attendees ?: ($guest->expected_attendees ?: 1));
                $expected = (int) ($guest->expected_attendees ?: 1);

                $expectedCount += $expected;
                if ($isCheckedIn) {
                    $checkedInCount += $attendees;
                }

                $guestItems[] = [
                    'id' => $guest->id,
                    'name' => (string) $guest->name,
                    'salutation' => (string) ($guest->salutation ?: 'Quý khách'),
                    'attendees' => $attendees,
                    'is_checked_in' => $isCheckedIn,
                    'checked_in_at' => $guest->checked_in_at ? Carbon::parse($guest->checked_in_at)->format('H:i') : null,
                ];
            }

            $availableSeats = max(0, $capacity - $checkedInCount);
            $overflowCount = max(0, $checkedInCount - $capacity);
            $occupancyPercent = round(($checkedInCount / $capacity) * 100, 1);

            // Xác định trạng thái của bàn
            if ($checkedInCount === 0) {
                $status = 'empty';
                $statusLabel = 'Bàn Đang Trống';
                $statusColor = '#9CA3AF'; // Xám
            } elseif ($overflowCount > 0) {
                $status = 'overflow';
                $statusLabel = "Quá Tải (+{$overflowCount} ghế)";
                $statusColor = '#EF4444'; // Đỏ
            } elseif ($checkedInCount >= $capacity) {
                $status = 'ready_to_serve';
                $statusLabel = 'Đủ Mâm (Lên Món)';
                $statusColor = '#10B981'; // Xanh ngọc
            } else {
                $status = 'available';
                $statusLabel = "Còn {$availableSeats} Chỗ";
                $statusColor = '#F59E0B'; // Vàng cam
            }

            return [
                'id' => $table->id,
                'name' => (string) $table->name,
                'zone' => (string) ($table->zone ?: 'Chung'),
                'capacity' => $capacity,
                'checked_in_count' => $checkedInCount,
                'expected_count' => $expectedCount,
                'available_seats' => $availableSeats,
                'overflow_count' => $overflowCount,
                'occupancy_percent' => min(150.0, $occupancyPercent),
                'status' => $status,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'guests' => $guestItems,
            ];
        });

        if (! empty($filterStatus)) {
            $result = $result->filter(function ($item) use ($filterStatus) {
                return $item['status'] === $filterStatus;
            })->values();
        }

        return $result;
    }

    /**
     * Tìm các bàn gợi ý còn chỗ trống cho nhóm khách vừa tới (Thuật toán Dispatcher).
     *
     * @return array<int, array{
     *     table_id: int,
     *     table_name: string,
     *     zone: string,
     *     available_seats: int,
     *     capacity: int,
     *     checked_in_count: int
     * }>
     */
    public function findAvailableTablesForGroup(Wedding $wedding, ?string $zone = null, int $attendeesNeeded = 1): array
    {
        $tables = $this->getTablesDetail($wedding);

        $suitable = $tables->filter(function ($table) use ($zone, $attendeesNeeded) {
            $hasEnoughSeats = $table['available_seats'] >= $attendeesNeeded;
            if (! $hasEnoughSeats) {
                return false;
            }

            if (! empty($zone) && $zone !== 'Chung' && $zone !== 'all') {
                return $table['zone'] === $zone;
            }

            return true;
        });

        if ($suitable->isEmpty() && ! empty($zone)) {
            // Nếu không còn bàn trong cùng zone, fallback sang các bàn mở khác còn đủ ghế
            $suitable = $tables->filter(function ($table) use ($attendeesNeeded) {
                return $table['available_seats'] >= $attendeesNeeded;
            });
        }

        return $suitable->sortByDesc('available_seats')->values()->all();
    }

    /**
     * Kê thêm ghế phụ vào bàn tiệc tức thời (Quick Add Extra Seat).
     */
    public function quickAddExtraSeats(WeddingTable $table, int $extraSeats = 1): bool
    {
        $newCapacity = max(1, ((int) $table->capacity) + $extraSeats);
        $table->update(['capacity' => $newCapacity]);

        AuditLogger::log(
            'TABLE_CAPACITY_EXPANDED',
            'WeddingTable',
            $table->id,
            "Kê thêm {$extraSeats} ghế phụ cho bàn '{$table->name}' (Sức chứa mới: {$newCapacity})"
        );

        return true;
    }

    /**
     * Chuyển khách sang bàn mới khi bàn cũ đã quá tải (Dynamic Seat Dispatch).
     */
    public function dispatchGuestToTable(Guest $guest, WeddingTable $newTable): bool
    {
        $oldTableName = $guest->table_name ?: 'Chưa xếp bàn';

        $guest->update([
            'table_id' => $newTable->id,
            'table_name' => $newTable->name,
        ]);

        AuditLogger::log(
            'GUEST_DISPATCHED_TO_NEW_TABLE',
            'Guest',
            $guest->id,
            "Điều chuyển khách '{$guest->name}' từ '{$oldTableName}' sang bàn '{$newTable->name}'"
        );

        return true;
    }

    /**
     * Tính toán số lượt khách đến trong từng khung 15 phút gần nhất.
     *
     * @return array<string, int>
     */
    protected function calculate15MinuteWaves(Wedding $wedding): array
    {
        $now = now();
        $buckets = [];

        for ($i = 3; $i >= 0; $i--) {
            $start = $now->copy()->subMinutes(($i + 1) * 15);
            $end = $now->copy()->subMinutes($i * 15);
            $label = $start->format('H:i').' - '.$end->format('H:i');

            $count = (int) $wedding->guests()
                ->whereBetween('checked_in_at', [$start, $end])
                ->count();

            $buckets[$label] = $count;
        }

        return $buckets;
    }
}
