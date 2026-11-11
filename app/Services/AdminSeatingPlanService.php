<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Wedding;
use App\Models\WeddingTable;

class AdminSeatingPlanService
{
    public function __construct(
        protected SeatingCapacityGuardianService $capacityGuardian
    ) {}

    /**
     * Lấy toàn bộ dữ liệu sơ đồ bàn tiệc, danh sách khách và chỉ số sức khỏe của đám cưới
     *
     * @return array<string, mixed>
     */
    public function getSeatingPlanData(Wedding $wedding): array
    {
        $tables = $wedding->tables()
            ->with(['guests'])
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        $unassignedGuests = $wedding->guests()
            ->whereNull('table_id')
            ->orderBy('group_name')
            ->orderBy('name')
            ->get();

        $healthAnalysis = $this->capacityGuardian->analyzeSeatingHealth($wedding);

        // Nhóm các bàn theo Khu vực / Sảnh tiệc (Zone)
        $tablesByZone = [];
        foreach ($tables as $table) {
            $zone = $table->zone ?: 'Sảnh Tiệc Chính';
            if (! isset($tablesByZone[$zone])) {
                $tablesByZone[$zone] = [];
            }
            $tablesByZone[$zone][] = $table;
        }

        return [
            'wedding' => $wedding,
            'tables' => $tables,
            'tables_by_zone' => $tablesByZone,
            'unassigned_guests' => $unassignedGuests,
            'health' => $healthAnalysis,
        ];
    }

    /**
     * Tạo mới một bàn tiệc
     *
     * @param  array<string, mixed>  $data
     */
    public function createTable(Wedding $wedding, array $data): WeddingTable
    {
        $maxOrder = (int) $wedding->tables()->max('display_order');

        return WeddingTable::create([
            'wedding_id' => $wedding->id,
            'name' => $data['name'],
            'shape' => $data['shape'] ?? 'round',
            'capacity' => (int) ($data['capacity'] ?? 10),
            'pos_x' => (int) ($data['pos_x'] ?? 0),
            'pos_y' => (int) ($data['pos_y'] ?? 0),
            'zone' => ! empty($data['zone']) ? trim((string) $data['zone']) : 'Sảnh Tiệc Chính',
            'notes' => $data['notes'] ?? null,
            'display_order' => $maxOrder + 1,
        ]);
    }

    /**
     * Cập nhật thông tin bàn tiệc
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTable(WeddingTable $table, array $data): WeddingTable
    {
        $table->update([
            'name' => $data['name'] ?? $table->name,
            'shape' => $data['shape'] ?? $table->shape,
            'capacity' => isset($data['capacity']) ? (int) $data['capacity'] : $table->capacity,
            'zone' => isset($data['zone']) ? trim((string) $data['zone']) : $table->zone,
            'notes' => $data['notes'] ?? $table->notes,
        ]);

        return $table;
    }

    /**
     * Xóa bàn tiệc (tự động gỡ khách về trạng thái chưa xếp)
     */
    public function deleteTable(WeddingTable $table): bool
    {
        return (bool) $table->delete();
    }

    /**
     * Gán khách vào bàn tiệc
     *
     * @param  array<int>  $guestIds
     * @return array{assigned_count: int, table_name: string}
     */
    public function assignGuestsToTable(WeddingTable $table, array $guestIds): array
    {
        $updated = Guest::where('wedding_id', $table->wedding_id)
            ->whereIn('id', $guestIds)
            ->update([
                'table_id' => $table->id,
                'table_name' => $table->name,
            ]);

        return [
            'assigned_count' => $updated,
            'table_name' => $table->name,
        ];
    }

    /**
     * Gỡ khách khỏi bàn tiệc về trạng thái chưa xếp
     */
    public function unassignGuest(Guest $guest): bool
    {
        return (bool) $guest->update([
            'table_id' => null,
            'table_name' => null,
        ]);
    }

    /**
     * Xuất danh sách sơ đồ bàn tiệc và khách mời sang định dạng CSV có UTF-8 BOM
     */
    public function exportSeatingPlanCsv(Wedding $wedding): string
    {
        $tables = $wedding->tables()->with('guests')->orderBy('zone')->orderBy('name')->get();
        $bom = "\xEF\xBB\xBF";
        $csv = $bom."Khu Vực / Sảnh,Tên Bàn,Hình Dáng,Sức Chứa,Số Khách Ngồi,Tên Khách Mời,Danh Xưng,Số Điện Thoại,Nhóm Khách,Số Suất Mời\n";

        foreach ($tables as $table) {
            if ($table->guests->isEmpty()) {
                $csv .= sprintf(
                    "\"%s\",\"%s\",\"%s\",%d,%d,\"%s\",\"%s\",\"%s\",\"%s\",%d\n",
                    str_replace('"', '""', $table->zone ?: 'Sảnh Chính'),
                    str_replace('"', '""', $table->name),
                    $table->shape_label,
                    $table->capacity,
                    0,
                    '-- Bàn trống chưa xếp khách --',
                    '',
                    '',
                    '',
                    0
                );
            } else {
                foreach ($table->guests as $g) {
                    $csv .= sprintf(
                        "\"%s\",\"%s\",\"%s\",%d,%d,\"%s\",\"%s\",\"%s\",\"%s\",%d\n",
                        str_replace('"', '""', $table->zone ?: 'Sảnh Chính'),
                        str_replace('"', '""', $table->name),
                        $table->shape_label,
                        $table->capacity,
                        $table->assigned_attendees_count,
                        str_replace('"', '""', $g->name),
                        str_replace('"', '""', (string) $g->salutation),
                        str_replace('"', '""', (string) $g->phone),
                        str_replace('"', '""', (string) $g->group_name),
                        $g->expected_attendees ?: 1
                    );
                }
            }
        }

        return $csv;
    }
}
