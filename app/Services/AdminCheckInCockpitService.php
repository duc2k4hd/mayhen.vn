<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Wedding;
use App\Models\WeddingTable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminCheckInCockpitService
{
    /**
     * Trích xuất toàn bộ dữ liệu tổng hành dinh điểm danh đón tiếp ngày cưới.
     */
    public function getCockpitData(Wedding $wedding, array $filters = []): array
    {
        $allGuests = $wedding->guests()->with(['response', 'table'])->get();

        // 1. Tính toán 5 KPI đón tiếp cốt lõi
        $totalExpectedAttendees = (int) $allGuests->sum(fn (Guest $g) => $g->actualAttendeesCount());

        $checkedInGuestsCollection = $allGuests->whereNotNull('checked_in_at');
        $notCheckedInGuestsCollection = $allGuests->whereNull('checked_in_at');

        $checkedInCount = $checkedInGuestsCollection->count();
        $checkedInAttendees = (int) $checkedInGuestsCollection->sum(function (Guest $g) {
            return $g->checked_in_attendees ?: $g->actualAttendeesCount();
        });

        $notCheckedInCount = $notCheckedInGuestsCollection->count();
        $notCheckedInAttendees = (int) $notCheckedInGuestsCollection->sum(fn (Guest $g) => $g->actualAttendeesCount());

        // Khách đến một phần (ví dụ xác nhận 2 nhưng thực tế check-in 1)
        $partiallyCheckedInCount = $checkedInGuestsCollection->filter(function (Guest $g) {
            $expected = $g->actualAttendeesCount();
            $actual = (int) ($g->checked_in_attendees ?: $expected);

            return $actual > 0 && $actual < $expected;
        })->count();

        // Khách vãng lai / phát sinh (Walk-in)
        $walkInCount = $allGuests->filter(function (Guest $g) {
            $group = mb_strtolower((string) $g->group_name);
            $note = mb_strtolower((string) $g->check_in_note);

            return str_contains($group, 'phát sinh')
                || str_contains($group, 'vãng lai')
                || str_contains($group, 'walk-in')
                || str_contains($note, 'phát sinh')
                || str_contains($note, 'vãng lai');
        })->count();

        $turnoutRate = $totalExpectedAttendees > 0
            ? round(($checkedInAttendees / $totalExpectedAttendees) * 100, 1)
            : 0;

        $stats = [
            'total_guests' => $allGuests->count(),
            'total_expected_attendees' => $totalExpectedAttendees,
            'checked_in_guests' => $checkedInCount,
            'checked_in_attendees' => $checkedInAttendees,
            'not_checked_in_guests' => $notCheckedInCount,
            'not_checked_in_attendees' => $notCheckedInAttendees,
            'partially_checked_in_guests' => $partiallyCheckedInCount,
            'walk_in_guests' => $walkInCount,
            'turnout_rate' => $turnoutRate,
        ];

        // 2. Timeline đón khách theo khung giờ
        $timeline = $this->calculateArrivalTimeline($checkedInGuestsCollection);

        // 3. Khách mới check-in gần nhất (Top 8)
        $recentCheckIns = $checkedInGuestsCollection
            ->sortByDesc('checked_in_at')
            ->take(8)
            ->values();

        // 4. Danh sách bàn tiệc kèm số chỗ đã ngồi
        $tables = $wedding->tables()->orderBy('display_order')->get()->map(function (WeddingTable $t) use ($allGuests) {
            $occupants = $allGuests->filter(fn (Guest $g) => $g->table_id === $t->id);
            $occupiedSeats = (int) $occupants->sum(fn (Guest $g) => $g->actualAttendeesCount());

            return [
                'id' => $t->id,
                'name' => $t->name,
                'capacity' => $t->capacity,
                'zone' => $t->zone ?? 'Khu chung',
                'occupied_seats' => $occupiedSeats,
                'available_seats' => max(0, $t->capacity - $occupiedSeats),
                'is_over_capacity' => $occupiedSeats > $t->capacity,
            ];
        });

        // 5. Danh sách các nhóm khách riêng biệt
        $groups = $allGuests->pluck('group_name')->filter()->unique()->values()->all();

        // 6. Danh sách khách có phân trang & áp dụng bộ lọc
        $filteredGuests = $this->getFilteredGuests($wedding, $filters);

        return [
            'wedding' => $wedding,
            'stats' => $stats,
            'timeline' => $timeline,
            'recentCheckIns' => $recentCheckIns,
            'tables' => $tables,
            'groups' => $groups,
            'guests' => $filteredGuests,
        ];
    }

    /**
     * Tính toán biểu đồ luồng khách đến theo khung giờ (Hourly Arrival Flow).
     */
    public function calculateArrivalTimeline(Collection $checkedInGuests): array
    {
        // Định nghĩa các khung giờ tiếp tân chuẩn
        $slots = [
            ['key' => 'before_1700', 'label' => 'Trước 17:00', 'start' => '00:00', 'end' => '17:00'],
            ['key' => '1700_1730', 'label' => '17:00 - 17:30', 'start' => '17:00', 'end' => '17:30'],
            ['key' => '1730_1800', 'label' => '17:30 - 18:00', 'start' => '17:30', 'end' => '18:00'],
            ['key' => '1800_1830', 'label' => '18:00 - 18:30', 'start' => '18:00', 'end' => '18:30'],
            ['key' => '1830_1900', 'label' => '18:30 - 19:00', 'start' => '18:30', 'end' => '19:00'],
            ['key' => '1900_1930', 'label' => '19:00 - 19:30', 'start' => '19:00', 'end' => '19:30'],
            ['key' => 'after_1930', 'label' => 'Sau 19:30', 'start' => '19:30', 'end' => '23:59'],
        ];

        $totalCheckedIn = $checkedInGuests->count();

        return array_map(function ($slot) use ($checkedInGuests, $totalCheckedIn) {
            $matching = $checkedInGuests->filter(function (Guest $g) use ($slot) {
                if (! $g->checked_in_at) {
                    return false;
                }
                $time = $g->checked_in_at->format('H:i');

                return $time >= $slot['start'] && $time < $slot['end'];
            });

            $count = $matching->count();
            $attendees = (int) $matching->sum(fn (Guest $g) => $g->checked_in_attendees ?: $g->actualAttendeesCount());
            $percentage = $totalCheckedIn > 0 ? round(($count / $totalCheckedIn) * 100, 1) : 0;

            return [
                'key' => $slot['key'],
                'label' => $slot['label'],
                'count' => $count,
                'attendees' => $attendees,
                'percentage' => $percentage,
            ];
        }, $slots);
    }

    /**
     * Tìm kiếm nhanh quan khách phục vụ trạm quét mã QR và ô gõ tức thời.
     */
    public function searchGuests(Wedding $wedding, string $query): Collection
    {
        $keyword = trim($query);

        if ($keyword === '') {
            return collect();
        }

        // Bóc tách token nếu quét phải link URL đầy đủ
        if (str_contains($keyword, 'token=')) {
            parse_str((string) parse_url($keyword, PHP_URL_QUERY), $queryParams);
            $keyword = $queryParams['token'] ?? $keyword;
        } elseif (str_contains($keyword, '/diem-danh/')) {
            $parts = explode('/diem-danh/', $keyword);
            $keyword = end($parts);
        }

        return $wedding->guests()
            ->with(['table:id,name,zone', 'response:id,guest_id,attending_status,dietary_requirements,wishes'])
            ->where(function ($q) use ($keyword) {
                $q->where('guest_token', $keyword)
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('table_name', 'like', "%{$keyword}%")
                    ->orWhere('group_name', 'like', "%{$keyword}%")
                    ->orWhere('id', is_numeric($keyword) ? (int) $keyword : 0);
            })
            ->limit(15)
            ->get()
            ->map(function (Guest $g) {
                $actual = $g->actualAttendeesCount();
                $checkedInAttendees = $g->checked_in_attendees ?: $actual;

                return [
                    'id' => $g->id,
                    'name' => $g->name,
                    'salutation' => $g->salutation ?: 'Thân mời',
                    'phone' => $g->phone,
                    'group_name' => $g->group_name ?: 'Khách chung',
                    'table_id' => $g->table_id,
                    'table_name' => $g->table?->name ?? $g->table_name ?? 'Chưa xếp bàn',
                    'expected_attendees' => $actual,
                    'checked_in_attendees' => $checkedInAttendees,
                    'is_checked_in' => $g->checked_in_at !== null,
                    'checked_in_time' => $g->checked_in_at ? $g->checked_in_at->format('H:i d/m') : null,
                    'check_in_note' => $g->check_in_note,
                    'dietary' => $g->response?->dietary_requirements,
                    'wishes' => $g->response?->wishes,
                ];
            });
    }

    /**
     * Xác nhận điểm danh 1 chạm có điều chỉnh sĩ số và bàn tiệc.
     */
    public function checkInGuest(
        int $guestId,
        int $actualAttendees = 1,
        ?string $note = null,
        ?int $tableId = null,
        ?string $operatorName = null
    ): array {
        return DB::transaction(function () use ($guestId, $actualAttendees, $note, $tableId, $operatorName) {
            /** @var Guest $guest */
            $guest = Guest::where('id', $guestId)->lockForUpdate()->firstOrFail();

            $alreadyCheckedIn = $guest->checked_in_at !== null;
            $previousTime = $guest->checked_in_at ? $guest->checked_in_at->format('H:i') : null;

            $updateData = [
                'checked_in_at' => $guest->checked_in_at ?: now(),
                'checked_in_attendees' => max(1, $actualAttendees),
            ];

            // Nếu chỉ định bàn tiệc
            if ($tableId) {
                $table = WeddingTable::where('wedding_id', $guest->wedding_id)
                    ->where('id', $tableId)
                    ->first();
                if ($table) {
                    $updateData['table_id'] = $table->id;
                    $updateData['table_name'] = $table->name;
                }
            }

            if ($note !== null && trim($note) !== '') {
                $operatorPrefix = $operatorName ? "[{$operatorName}] " : '';
                $updateData['check_in_note'] = $operatorPrefix.trim($note);
            }

            $guest->update($updateData);

            $message = $alreadyCheckedIn
                ? "Đã cập nhật điểm danh quan khách \"{$guest->name}\" ({$updateData['checked_in_attendees']} người)."
                : "Đã điểm danh đón tiếp thành công quan khách \"{$guest->name}\" ({$updateData['checked_in_attendees']} người).";

            AuditLogger::log(
                $alreadyCheckedIn ? 'update_check_in' : 'check_in',
                'Guest',
                $guest->id,
                $message,
                [
                    'wedding_id' => $guest->wedding_id,
                    'operator' => $operatorName,
                    'attendees' => $updateData['checked_in_attendees'],
                    'table' => $guest->table_name,
                ]
            );

            return [
                'success' => true,
                'already_checked_in' => $alreadyCheckedIn,
                'previous_time' => $previousTime,
                'guest' => $guest->fresh(['table', 'response']),
                'message' => $message,
            ];
        });
    }

    /**
     * Hủy điểm danh đưa khách về trạng thái chưa đến.
     */
    public function cancelCheckIn(int $guestId, ?string $operatorName = null): array
    {
        return DB::transaction(function () use ($guestId, $operatorName) {
            /** @var Guest $guest */
            $guest = Guest::where('id', $guestId)->lockForUpdate()->firstOrFail();

            $guest->update([
                'checked_in_at' => null,
                'checked_in_attendees' => null,
            ]);

            $message = "Đã hủy điểm danh đón tiếp cho quan khách \"{$guest->name}\".";

            AuditLogger::log(
                'cancel_check_in',
                'Guest',
                $guest->id,
                $message,
                [
                    'wedding_id' => $guest->wedding_id,
                    'operator' => $operatorName,
                ]
            );

            return [
                'success' => true,
                'guest' => $guest->fresh(),
                'message' => $message,
            ];
        });
    }

    /**
     * Tạo khách phát sinh (Walk-in) tại trạm đón tiếp và tự động check-in ngay.
     */
    public function createWalkInGuest(int $weddingId, array $data, ?string $operatorName = null): Guest
    {
        return DB::transaction(function () use ($weddingId, $data, $operatorName) {
            $wedding = Wedding::findOrFail($weddingId);

            $attendees = max(1, (int) ($data['attendees'] ?? 1));
            $tableName = ! empty($data['table_name']) ? trim($data['table_name']) : null;
            $tableId = ! empty($data['table_id']) ? (int) $data['table_id'] : null;

            if ($tableId && ! $tableName) {
                $table = WeddingTable::where('wedding_id', $weddingId)->where('id', $tableId)->first();
                $tableName = $table?->name;
            } elseif ($tableName && ! $tableId) {
                $table = WeddingTable::where('wedding_id', $weddingId)->where('name', $tableName)->first();
                $tableId = $table?->id;
            }

            $groupName = ! empty($data['group_name']) ? trim($data['group_name']) : 'Khách phát sinh';
            $noteText = trim((string) ($data['note'] ?? ''));
            $operatorPrefix = $operatorName ? "[{$operatorName}] " : '';
            $checkInNote = trim("[Khách phát sinh] {$operatorPrefix}{$noteText}");

            $guest = $wedding->guests()->create([
                'name' => trim((string) $data['name']),
                'salutation' => ! empty($data['salutation']) ? trim((string) $data['salutation']) : 'Bạn',
                'phone' => ! empty($data['phone']) ? trim((string) $data['phone']) : null,
                'group_name' => $groupName,
                'table_name' => $tableName,
                'table_id' => $tableId,
                'expected_attendees' => $attendees,
                'checked_in_attendees' => $attendees,
                'checked_in_at' => now(),
                'check_in_note' => $checkInNote,
                'guest_token' => Str::random(32),
            ]);

            AuditLogger::log(
                'create_walk_in_guest',
                'Guest',
                $guest->id,
                "Tạo và đón tiếp thành công khách phát sinh \"{$guest->name}\" ({$attendees} người).",
                [
                    'wedding_id' => $weddingId,
                    'operator' => $operatorName,
                    'attendees' => $attendees,
                    'table_name' => $tableName,
                ]
            );

            return $guest;
        });
    }

    /**
     * Cập nhật nhanh bàn tiệc cho khách tại trạm đón tiếp.
     */
    public function updateGuestTable(int $guestId, int $tableId, ?string $operatorName = null): array
    {
        return DB::transaction(function () use ($guestId, $tableId, $operatorName) {
            /** @var Guest $guest */
            $guest = Guest::where('id', $guestId)->lockForUpdate()->firstOrFail();
            $table = WeddingTable::where('wedding_id', $guest->wedding_id)
                ->where('id', $tableId)
                ->lockForUpdate()
                ->firstOrFail();

            $oldTable = $guest->table_name ?: 'Chưa xếp bàn';
            $guest->update([
                'table_id' => $table->id,
                'table_name' => $table->name,
            ]);

            $message = "Đã chuyển quan khách \"{$guest->name}\" từ bàn \"{$oldTable}\" sang bàn \"{$table->name}\".";

            AuditLogger::log(
                'reassign_guest_table',
                'Guest',
                $guest->id,
                $message,
                [
                    'wedding_id' => $guest->wedding_id,
                    'operator' => $operatorName,
                    'old_table' => $oldTable,
                    'new_table' => $table->name,
                ]
            );

            return [
                'success' => true,
                'table_name' => $table->name,
                'table_id' => $table->id,
                'message' => $message,
            ];
        });
    }

    /**
     * Lọc danh sách khách theo các tiêu chí và phân trang.
     */
    protected function getFilteredGuests(Wedding $wedding, array $filters = []): LengthAwarePaginator
    {
        $query = $wedding->guests()->with(['table:id,name,zone', 'response:id,guest_id,attending_status,dietary_requirements,wishes']);

        // 1. Lọc theo từ khóa tìm kiếm
        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('table_name', 'like', "%{$keyword}%")
                    ->orWhere('group_name', 'like', "%{$keyword}%");
            });
        }

        // 2. Lọc theo trạng thái điểm danh
        $status = $filters['status'] ?? 'all';
        if ($status === 'checked_in') {
            $query->whereNotNull('checked_in_at');
        } elseif ($status === 'not_checked_in') {
            $query->whereNull('checked_in_at');
        } elseif ($status === 'partially_checked_in') {
            $query->whereNotNull('checked_in_at')
                ->whereNotNull('checked_in_attendees')
                ->whereRaw('checked_in_attendees < expected_attendees');
        } elseif ($status === 'walk_in') {
            $query->where(function ($q) {
                $q->where('group_name', 'like', '%phát sinh%')
                    ->orWhere('group_name', 'like', '%vãng lai%')
                    ->orWhere('group_name', 'like', '%walk-in%')
                    ->orWhere('check_in_note', 'like', '%[Khách phát sinh]%');
            });
        }

        // 3. Lọc theo Bàn tiệc
        if (! empty($filters['table_id'])) {
            $query->where('table_id', (int) $filters['table_id']);
        }

        // 4. Lọc theo Nhóm khách
        if (! empty($filters['group_name'])) {
            $query->where('group_name', $filters['group_name']);
        }

        return $query->orderByRaw('checked_in_at IS NULL, checked_in_at DESC, id DESC')
            ->paginate(25)
            ->withQueryString();
    }

    /**
     * Xuất tệp báo cáo điểm danh đón tiếp định dạng CSV UTF-8 BOM.
     */
    public function exportAttendanceCsv(Wedding $wedding, array $filters = []): Response
    {
        $query = $wedding->guests()->with(['table', 'response']);

        if (! empty($filters['status']) && $filters['status'] === 'checked_in') {
            $query->whereNotNull('checked_in_at');
        } elseif (! empty($filters['status']) && $filters['status'] === 'not_checked_in') {
            $query->whereNull('checked_in_at');
        }

        $guests = $query->orderByRaw('checked_in_at IS NULL, checked_in_at DESC, id ASC')->get();
        $filename = 'bao-cao-diem-danh-'.Str::slug("{$wedding->groom_name}-{$wedding->bride_name}").'-'.now()->format('Ymd-His').'.csv';

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'STT',
            'Mã Khách',
            'Họ và Tên',
            'Danh Xưng',
            'Số Điện Thoại',
            'Nhóm Khách',
            'Bàn Tiệc',
            'Khu Vực / Sảnh',
            'Số Suất Dự Kiến',
            'Số Thực Tế Đến',
            'Trạng Thái Đón Tiếp',
            'Thời Điểm Check-in',
            'Ghi Chú Đón Tiếp',
            'Yêu Cầu Ăn Uống',
        ]);

        $index = 1;
        foreach ($guests as $g) {
            $status = $g->checked_in_at !== null ? 'Đã đón tiếp' : 'Chưa đến';
            $checkInTime = $g->checked_in_at ? $g->checked_in_at->format('d/m/Y H:i:s') : '—';
            $tableName = $g->table?->name ?? $g->table_name ?? 'Chưa xếp bàn';
            $zone = $g->table?->zone ?? 'Khu chung';
            $expected = $g->actualAttendeesCount();
            $actual = $g->checked_in_at !== null ? ($g->checked_in_attendees ?: $expected) : 0;

            fputcsv($handle, CsvSanitizerService::row([
                $index++,
                $g->id,
                $g->name,
                $g->salutation ?: 'Thân mời',
                $g->phone ?: '—',
                $g->group_name ?: 'Khách chung',
                $tableName,
                $zone,
                $expected,
                $actual,
                $status,
                $checkInTime,
                $g->check_in_note ?: '',
                $g->response?->dietary_requirements ?: '',
            ]));
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
