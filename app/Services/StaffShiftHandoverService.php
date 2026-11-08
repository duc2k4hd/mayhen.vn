<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use Carbon\Carbon;
use Illuminate\Support\Str;

class StaffShiftHandoverService
{
    /**
     * Lấy danh sách toàn bộ ca trực lễ tân của thiệp cưới.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getShifts(Invitation $invitation): array
    {
        $config = $invitation->custom_config ?? [];

        return $config['reception_shifts'] ?? [];
    }

    /**
     * Tạo mới hoặc cập nhật thông tin ca trực lễ tân.
     *
     * @param  array{id?: string, name: string, staff_name: string, staff_phone?: string, pin: string, start_time?: string, end_time?: string, is_active?: bool}  $data
     * @return array<string, mixed> Ca trực đã lưu
     */
    public function saveShift(Invitation $invitation, array $data): array
    {
        $config = $invitation->custom_config ?? [];
        $shifts = $config['reception_shifts'] ?? [];

        $shiftId = $data['id'] ?? ('shift_'.Str::random(10));
        $now = Carbon::now()->toIso8601String();

        $existingIndex = null;
        foreach ($shifts as $idx => $s) {
            if (($s['id'] ?? '') === $shiftId) {
                $existingIndex = $idx;
                break;
            }
        }

        $shiftData = [
            'id' => $shiftId,
            'name' => trim((string) ($data['name'] ?? 'Ca Trực Đón Tiếp')),
            'staff_name' => trim((string) ($data['staff_name'] ?? 'Lễ Tân')),
            'staff_phone' => trim((string) ($data['staff_phone'] ?? '')),
            'pin' => preg_replace('/\D/', '', (string) ($data['pin'] ?? '1234')),
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_at' => $existingIndex !== null ? ($shifts[$existingIndex]['created_at'] ?? $now) : $now,
            'updated_at' => $now,
            'handovers' => $existingIndex !== null ? ($shifts[$existingIndex]['handovers'] ?? []) : [],
        ];

        if ($existingIndex !== null) {
            $shifts[$existingIndex] = array_merge($shifts[$existingIndex], $shiftData);
        } else {
            $shifts[] = $shiftData;
        }

        $config['reception_shifts'] = $shifts;
        $invitation->custom_config = $config;
        $invitation->save();

        return $shiftData;
    }

    /**
     * Xóa một ca trực lễ tân.
     */
    public function deleteShift(Invitation $invitation, string $shiftId): bool
    {
        $config = $invitation->custom_config ?? [];
        $shifts = $config['reception_shifts'] ?? [];

        $filtered = array_values(array_filter($shifts, fn ($s) => ($s['id'] ?? '') !== $shiftId));

        if (count($filtered) !== count($shifts)) {
            $config['reception_shifts'] = $filtered;
            $invitation->custom_config = $config;
            $invitation->save();

            return true;
        }

        return false;
    }

    /**
     * Xác thực đăng nhập cổng điểm danh bằng mã PIN ca trực.
     *
     * @return array<string, mixed>|null Trả về thông tin ca trực nếu hợp lệ
     */
    public function authenticateShiftPin(Invitation $invitation, string $inputPin): ?array
    {
        $cleanPin = preg_replace('/\D/', '', $inputPin);
        if ($cleanPin === '') {
            return null;
        }

        $shifts = $this->getShifts($invitation);

        foreach ($shifts as $shift) {
            if (! empty($shift['is_active']) && ($shift['pin'] ?? '') === $cleanPin) {
                return $shift;
            }
        }

        return null;
    }

    /**
     * Tạo biên bản tóm tắt sĩ số và diễn biến ca trực (Shift Handover Summary).
     *
     * @return array{
     *     shift: array<string, mixed>,
     *     summary: array{
     *         total_checked_in: int,
     *         total_attendees: int,
     *         walk_in_count: int,
     *         remaining_uncheckin_guests: int
     *     },
     *     recent_guests_in_shift: array<int, array<string, mixed>>
     * }
     */
    public function generateHandoverSummary(Invitation $invitation, string $shiftId, ?Carbon $since = null): array
    {
        $shifts = $this->getShifts($invitation);
        $shift = collect($shifts)->firstWhere('id', $shiftId) ?? [
            'id' => $shiftId,
            'name' => 'Ca hiện tại',
            'staff_name' => 'Lễ Tân',
        ];

        $wedding = $invitation->wedding;
        $allGuests = $wedding ? $wedding->guests()->with('response')->get() : collect();

        // Lọc các khách đã check-in
        $checkedInGuests = $allGuests->filter(fn (Guest $g) => $g->isCheckedIn());

        if ($since !== null) {
            $checkedInGuests = $checkedInGuests->filter(function (Guest $g) use ($since) {
                return $g->checked_in_at && Carbon::parse($g->checked_in_at)->gte($since);
            });
        }

        $totalCheckedIn = $checkedInGuests->count();
        $totalAttendees = (int) $checkedInGuests->sum(function (Guest $g) {
            return $g->checked_in_attendees ?: ($g->actualAttendeesCount() ?: 1);
        });

        // Khách phát sinh tại bàn lễ tân (có ghi chú ngoại lệ hoặc vãng lai)
        $walkInCount = $checkedInGuests->filter(function (Guest $g) {
            $note = (string) $g->check_in_note;

            return str_contains($note, 'Khách phát sinh') || str_contains($note, 'vãng lai') || str_contains($note, 'Bàn Xử Lý Ngoại Lệ');
        })->count();

        $uncheckinCount = $allGuests->filter(fn (Guest $g) => ! $g->isCheckedIn())->count();

        $recentList = $checkedInGuests->sortByDesc('checked_in_at')->take(10)->values()->map(function (Guest $g) {
            return [
                'id' => $g->id,
                'name' => $g->name,
                'table_name' => $g->table_name ?: ($g->table ? $g->table->name : 'Chưa xếp bàn'),
                'attendees' => $g->checked_in_attendees ?: 1,
                'checked_in_at' => $g->checked_in_at ? Carbon::parse($g->checked_in_at)->format('H:i') : '',
                'note' => $g->check_in_note,
            ];
        })->all();

        return [
            'shift' => $shift,
            'summary' => [
                'total_checked_in' => $totalCheckedIn,
                'total_attendees' => $totalAttendees,
                'walk_in_count' => $walkInCount,
                'remaining_uncheckin_guests' => $uncheckinCount,
            ],
            'recent_guests_in_shift' => $recentList,
        ];
    }

    /**
     * Ghi nhận biên bản bàn giao ca trực vào lịch sử.
     *
     * @param  array{handed_to_staff: string, notes?: string, summary?: array<string, mixed>}  $payload
     * @return array<string, mixed> Biên bản bàn giao đã lưu
     */
    public function recordHandover(Invitation $invitation, string $shiftId, array $payload): array
    {
        $config = $invitation->custom_config ?? [];
        $shifts = $config['reception_shifts'] ?? [];

        $handoverRecord = [
            'handover_id' => 'hnd_'.Str::random(10),
            'handover_at' => Carbon::now()->toIso8601String(),
            'handed_from_staff' => '',
            'handed_to_staff' => trim((string) ($payload['handed_to_staff'] ?? 'Ca Tiếp Theo')),
            'guests_checked_in' => (int) ($payload['summary']['total_checked_in'] ?? 0),
            'attendees_checked_in' => (int) ($payload['summary']['total_attendees'] ?? 0),
            'walk_in_count' => (int) ($payload['summary']['walk_in_count'] ?? 0),
            'notes' => trim((string) ($payload['notes'] ?? '')),
        ];

        foreach ($shifts as $idx => $s) {
            if (($s['id'] ?? '') === $shiftId) {
                $handoverRecord['handed_from_staff'] = $s['staff_name'] ?? 'Lễ Tân';
                $sHandovers = $s['handovers'] ?? [];
                $sHandovers[] = $handoverRecord;
                $shifts[$idx]['handovers'] = $sHandovers;
                break;
            }
        }

        $config['reception_shifts'] = $shifts;
        $invitation->custom_config = $config;
        $invitation->save();

        return $handoverRecord;
    }
}

