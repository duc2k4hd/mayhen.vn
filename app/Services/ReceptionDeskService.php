<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\PlatformSetting;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Deep module owning all wedding-day reception desk operations across a real two-adapter seam:
 * live Eloquent persistence vs isolated Session Dry-Run rehearsal, plus pure QR token parsing
 * and Carbon-safe ICS / Google Calendar formatting.
 */
class ReceptionDeskService
{
    public function __construct(
        protected ReceptionDryRunService $dryRun
    ) {}

    /**
     * Extract a raw guest token from a plain token, `?token=...` URL, or `/diem-danh/...` URL.
     */
    public static function extractQrToken(string $rawInput): string
    {
        $token = trim($rawInput);
        if ($token === '') {
            return '';
        }

        if (str_contains($token, 'token=')) {
            parse_str((string) parse_url($token, PHP_URL_QUERY), $queryParams);
            if (! empty($queryParams['token']) && is_string($queryParams['token'])) {
                return trim($queryParams['token']);
            }
        }

        if (str_contains($token, '/diem-danh/')) {
            $parts = explode('/diem-danh/', $token);
            $last = end($parts);
            if (is_string($last) && trim($last) !== '') {
                $clean = explode('?', $last)[0];

                return trim($clean);
            }
        }

        return $token;
    }

    /**
     * Compute Carbon-safe local event start/end timestamps (prevents hour-overflow past midnight, e.g. 22:00 + 3h).
     *
     * @param  array<string, mixed>  $extra
     * @return array{start: string, end: string}
     */
    public static function buildEventWindow(Wedding $wedding, array $extra, int $durationHours = 3): array
    {
        $dateYmd = $wedding->wedding_date ? $wedding->wedding_date->format('Y-m-d') : date('Y-m-d');
        $rawTime = trim((string) ($extra['party_time'] ?? ($extra['event_party_time'] ?? '18:00')));
        if (! preg_match('/^([01]?\d|2[0-3]):([0-5]\d)/', $rawTime, $m)) {
            $hour = 18;
            $minute = 0;
        } else {
            $hour = (int) $m[1];
            $minute = (int) $m[2];
        }

        $startAt = Carbon::createFromFormat('Y-m-d H:i:s', sprintf('%s %02d:%02d:00', $dateYmd, $hour, $minute), 'Asia/Ho_Chi_Minh');
        $endAt = $startAt->copy()->addHours(max(1, $durationHours));

        return [
            'start' => $startAt->format('Ymd\THis'),
            'end' => $endAt->format('Ymd\THis'),
        ];
    }

    /**
     * Generate a standards-compliant iCalendar (.ics) file payload for a wedding guest.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function buildIcsContent(Wedding $wedding, array $extra, Guest $guest): string
    {
        $window = self::buildEventWindow($wedding, $extra, 3);
        $title = "Hôn Lễ: {$wedding->groom_name} & {$wedding->bride_name}";
        $venue = trim((string) ($wedding->venue_name ?? ($extra['party_venue'] ?? ($extra['event_party_venue'] ?? 'Sảnh Tiệc Cưới'))));
        $address = trim((string) ($wedding->venue_address ?? ($extra['event_party_address'] ?? '')));
        $location = $venue.($address !== '' ? ', '.$address : '');
        $description = "Trân trọng kính mời {$guest->salutation} {$guest->name} tới chung vui cùng đôi uyên ương tại {$venue}.";

        return "BEGIN:VCALENDAR\r\n"
            ."VERSION:2.0\r\n"
            .'PRODID:-//'.Str::ascii(PlatformSetting::getBrandName())."//Wedding Concierge//VI\r\n"
            ."CALSCALE:GREGORIAN\r\n"
            ."METHOD:PUBLISH\r\n"
            ."BEGIN:VEVENT\r\n"
            .'UID:'.md5($guest->id.$wedding->id)."@mayhen.vn\r\n"
            .'DTSTAMP:'.gmdate('Ymd\THis\Z')."\r\n"
            .'DTSTART;TZID=Asia/Ho_Chi_Minh:'.$window['start']."\r\n"
            .'DTEND;TZID=Asia/Ho_Chi_Minh:'.$window['end']."\r\n"
            .'SUMMARY:'.$title."\r\n"
            .'DESCRIPTION:'.$description."\r\n"
            .'LOCATION:'.$location."\r\n"
            ."STATUS:CONFIRMED\r\n"
            ."END:VEVENT\r\n"
            ."END:VCALENDAR\r\n";
    }

    /**
     * Build a 1-tap Google Calendar URL with Carbon-safe midnight rollover.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function buildGoogleCalendarUrl(Wedding $wedding, array $extra, Guest $guest): string
    {
        $window = self::buildEventWindow($wedding, $extra, 3);
        $title = "Hôn Lễ: {$wedding->groom_name} & {$wedding->bride_name}";
        $venue = trim((string) ($wedding->venue_name ?? ($extra['party_venue'] ?? ($extra['event_party_venue'] ?? 'Sảnh Tiệc Cưới'))));
        $address = trim((string) ($wedding->venue_address ?? ($extra['event_party_address'] ?? '')));
        $location = $venue.($address !== '' ? ', '.$address : '');
        $details = "Trân trọng kính mời {$guest->salutation} {$guest->name} tới chung vui cùng đôi uyên ương {$wedding->groom_name} & {$wedding->bride_name} tại {$venue}.";

        return 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            .'&text='.urlencode($title)
            .'&dates='.$window['start'].'/'.$window['end']
            .'&details='.urlencode($details)
            .'&location='.urlencode($location);
    }

    /**
     * Compute live reception check-in stats for a wedding.
     *
     * @return array{checked_in_count: int, total_expected: int, checked_in_guests: int, total_guests: int}
     */
    public function computeLiveStats(Wedding $wedding): array
    {
        $allGuests = $wedding->guests()->with(['response', 'responses'])->get();
        $totalExpected = (int) $allGuests->sum(fn (Guest $g): int => $g->actualAttendeesCount());
        $checkedIn = $allGuests->whereNotNull('checked_in_at');
        $checkedInCount = (int) $checkedIn->sum(fn (Guest $g): int => $g->checked_in_attendees ?: $g->actualAttendeesCount());

        return [
            'checked_in_count' => $checkedInCount,
            'total_expected' => $totalExpected,
            'checked_in_guests' => $checkedIn->count(),
            'total_guests' => $allGuests->count(),
        ];
    }

    /**
     * Scan QR code or check-in by guest ID across either Dry-Run or Live Eloquent adapter.
     *
     * @param  array{token?: ?string, guest_id?: ?int, attendees?: ?int, note?: ?string}  $params
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function scan(Invitation $invitation, array $params, bool $isDryRun, ?string $staffName = null): array
    {
        if ($isDryRun) {
            $dryRunResult = $this->dryRun->processScan($invitation, $params);

            return [
                'status' => $dryRunResult['success'] ? 200 : 404,
                'payload' => $dryRunResult,
            ];
        }

        $wedding = $invitation->wedding;
        $query = $wedding->guests()->with('response');

        if (! empty($params['token'])) {
            $query->where('guest_token', self::extractQrToken((string) $params['token']));
        } elseif (! empty($params['guest_id'])) {
            $query->where('id', (int) $params['guest_id']);
        } else {
            return [
                'status' => 422,
                'payload' => [
                    'success' => false,
                    'message' => 'Vui lòng cung cấp mã QR hoặc ID khách mời.',
                ],
            ];
        }

        /** @var Guest|null $guest */
        $guest = $query->first();
        if (! $guest) {
            return [
                'status' => 404,
                'payload' => [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách mời trong danh sách đám cưới này.',
                ],
            ];
        }

        $alreadyCheckedIn = false;
        $previousCheckInTime = null;

        DB::transaction(function () use ($guest, $params, $staffName, &$alreadyCheckedIn, &$previousCheckInTime): void {
            /** @var Guest $lockedGuest */
            $lockedGuest = Guest::where('id', $guest->id)->lockForUpdate()->first();

            if ($lockedGuest->checked_in_at !== null) {
                $alreadyCheckedIn = true;
                $previousCheckInTime = $lockedGuest->checked_in_at->format('H:i');

                return;
            }

            $attendeesCount = ! empty($params['attendees'])
                ? (int) $params['attendees']
                : $lockedGuest->actualAttendeesCount();

            $staffPrefix = ($staffName !== null && $staffName !== '') ? "[{$staffName}] " : '';
            $checkInNote = $params['note'] ?? $lockedGuest->check_in_note;
            if ($staffPrefix !== '' && (! $checkInNote || ! str_contains((string) $checkInNote, $staffPrefix))) {
                $checkInNote = $staffPrefix.($checkInNote ?: 'Điểm danh đón tiếp');
            }

            $lockedGuest->update([
                'checked_in_at' => now(),
                'checked_in_attendees' => $attendeesCount,
                'check_in_note' => $checkInNote,
            ]);
        });

        $guest->refresh();

        return [
            'status' => 200,
            'payload' => [
                'success' => true,
                'already_checked_in' => $alreadyCheckedIn,
                'previous_time' => $previousCheckInTime,
                'guest' => [
                    'id' => $guest->id,
                    'name' => $guest->name,
                    'salutation' => $guest->salutation ?: 'Thân mời',
                    'group_name' => $guest->group_name ?: 'Khách chung',
                    'table_name' => $guest->table_name ?: 'Chưa xếp bàn',
                    'expected_attendees' => $guest->actualAttendeesCount(),
                    'checked_in_attendees' => $guest->checked_in_attendees ?: $guest->actualAttendeesCount(),
                    'checked_in_at' => $guest->checked_in_at ? $guest->checked_in_at->format('H:i d/m/Y') : null,
                    'dietary' => $guest->response?->dietary_requirements,
                    'wishes' => $guest->response?->wishes,
                ],
                'stats' => $this->computeLiveStats($wedding),
            ],
        ];
    }

    /**
     * Quick-search guests by name, phone, table, or group across Live or Dry-Run adapter.
     *
     * @return array<string, mixed>
     */
    public function quickSearch(Invitation $invitation, string $keyword, bool $isDryRun): array
    {
        $cleanKeyword = trim($keyword);

        if ($isDryRun) {
            return [
                'guests' => $this->dryRun->quickSearch($invitation, $cleanKeyword),
                'is_dry_run' => true,
            ];
        }

        if (mb_strlen($cleanKeyword) < 1) {
            return ['guests' => []];
        }

        $guests = $invitation->wedding->guests()
            ->with(['response', 'responses'])
            ->where(function ($q) use ($cleanKeyword): void {
                $q->where('name', 'like', "%{$cleanKeyword}%")
                    ->orWhere('phone', 'like', "%{$cleanKeyword}%")
                    ->orWhere('table_name', 'like', "%{$cleanKeyword}%")
                    ->orWhere('group_name', 'like', "%{$cleanKeyword}%");
            })
            ->limit(15)
            ->get()
            ->map(function (Guest $g): array {
                return [
                    'id' => $g->id,
                    'name' => $g->name,
                    'salutation' => $g->salutation,
                    'group_name' => $g->group_name,
                    'table_name' => $g->table_name ?: 'Chưa xếp bàn',
                    'expected_attendees' => $g->actualAttendeesCount(),
                    'rsvp_status' => $g->response?->attending_status ?? 'pending',
                    'is_checked_in' => $g->checked_in_at !== null,
                    'checked_in_time' => $g->checked_in_at ? $g->checked_in_at->format('H:i') : null,
                ];
            });

        return ['guests' => $guests];
    }

    /**
     * Undo check-in across Live or Dry-Run adapter (Dry-Run never touches live DB).
     *
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function undo(Invitation $invitation, int $guestId, bool $isDryRun): array
    {
        if ($isDryRun) {
            $res = $this->dryRun->undoCheckIn($invitation, $guestId);

            return [
                'status' => $res['success'] ? 200 : 404,
                'payload' => $res,
            ];
        }

        $wedding = $invitation->wedding;
        $guest = $wedding->guests()->findOrFail($guestId);
        $guest->update([
            'checked_in_at' => null,
            'checked_in_attendees' => null,
        ]);

        return [
            'status' => 200,
            'payload' => [
                'success' => true,
                'message' => "Đã hủy điểm danh cho khách {$guest->name}.",
                'stats' => $this->computeLiveStats($wedding),
            ],
        ];
    }

    /**
     * Assign or update a guest's table across Live or Dry-Run adapter.
     *
     * @return array<string, mixed>
     */
    public function assignTable(Invitation $invitation, int $guestId, string $tableName, bool $isDryRun): array
    {
        $cleanTableName = trim($tableName);

        if ($isDryRun) {
            $this->dryRun->updateTable($invitation, $guestId, $cleanTableName);

            return [
                'success' => true,
                'is_dry_run' => true,
                'message' => "[Diễn Tập] Đã cập nhật bàn tiệc: {$cleanTableName}",
                'table_name' => $cleanTableName,
            ];
        }

        $wedding = $invitation->wedding;
        $guest = $wedding->guests()->findOrFail($guestId);
        $matchedTable = $wedding->tables()->where('name', $cleanTableName)->first();
        $tableId = $matchedTable?->id;

        $guest->update([
            'table_name' => $cleanTableName,
            'table_id' => $tableId,
        ]);

        return [
            'success' => true,
            'message' => "Đã cập nhật bàn tiệc: {$guest->table_name}",
            'table_name' => $guest->table_name,
            'table_id' => $tableId,
        ];
    }

    /**
     * Register and check in a walk-in guest across Live (atomic transaction) or Dry-Run adapter.
     *
     * @param  array<string, mixed>  $validated
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function walkIn(Invitation $invitation, array $validated, bool $isDryRun): array
    {
        if ($isDryRun) {
            return [
                'status' => 201,
                'payload' => $this->dryRun->walkInGuest($invitation, $validated),
            ];
        }

        $wedding = $invitation->wedding;
        $attendees = (int) $validated['attendees'];
        $tableName = ! empty($validated['table_name']) ? trim((string) $validated['table_name']) : null;

        /** @var Guest $guest */
        $guest = DB::transaction(function () use ($wedding, $validated, $attendees, $tableName): Guest {
            $tableId = null;
            if ($tableName !== null) {
                $matchedTable = $wedding->tables()->where('name', $tableName)->first();
                $tableId = $matchedTable?->id;
            }

            $created = Guest::create([
                'wedding_id' => $wedding->id,
                'name' => trim((string) $validated['name']),
                'salutation' => trim((string) ($validated['salutation'] ?? 'Bạn')),
                'group_name' => trim((string) ($validated['group_name'] ?? 'Khách phát sinh')),
                'table_name' => $tableName,
                'table_id' => $tableId,
                'expected_attendees' => $attendees,
                'is_sent' => true,
                'guest_token' => Str::random(32),
                'checked_in_at' => now(),
                'checked_in_attendees' => $attendees,
                'check_in_note' => trim('[Khách phát sinh] '.($validated['note'] ?? '')),
            ]);

            GuestResponse::create([
                'wedding_id' => $wedding->id,
                'guest_id' => $created->id,
                'guest_name' => $created->name,
                'attending_status' => 'attending',
                'adults_count' => $attendees,
                'children_count' => 0,
            ]);

            return $created;
        });

        return [
            'status' => 201,
            'payload' => [
                'success' => true,
                'message' => "Đã đón tiếp khách phát sinh {$guest->name} vào bàn thành công!",
                'guest' => [
                    'id' => $guest->id,
                    'name' => $guest->name,
                    'salutation' => $guest->salutation,
                    'group_name' => $guest->group_name,
                    'table_name' => $guest->table_name ?: 'Chưa xếp bàn',
                    'expected_attendees' => $guest->actualAttendeesCount(),
                    'checked_in_attendees' => $guest->checked_in_attendees,
                    'checked_in_at' => $guest->checked_in_at->format('H:i d/m/Y'),
                ],
            ],
        ];
    }

    /**
     * Adjust checked-in attendee headcount across Live or Dry-Run adapter.
     *
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function adjustAttendees(Invitation $invitation, int $guestId, int $attendees, ?string $note, bool $isDryRun): array
    {
        if ($isDryRun) {
            $res = $this->dryRun->adjustAttendees($invitation, $guestId, $attendees);

            return [
                'status' => $res['success'] ? 200 : 404,
                'payload' => $res,
            ];
        }

        $wedding = $invitation->wedding;
        $guest = $wedding->guests()->findOrFail($guestId);
        $oldCheckInAttendees = $guest->checked_in_attendees ?: $guest->actualAttendeesCount();
        $cleanNote = ($note !== null && trim($note) !== '')
            ? trim($note)
            : "Điều chỉnh sĩ số từ {$oldCheckInAttendees} lên {$attendees} suất";

        $guest->update([
            'checked_in_at' => $guest->checked_in_at ?: now(),
            'checked_in_attendees' => $attendees,
            'check_in_note' => trim(($guest->check_in_note ? $guest->check_in_note.' | ' : '').$cleanNote),
        ]);

        AuditLogger::log(
            'RECEPTION_ATTENDEES_ADJUSTED',
            Guest::class,
            $guest->id,
            "Lễ tân điều chỉnh số khách của {$guest->name} từ {$oldCheckInAttendees} thành {$attendees} suất.",
            ['previous' => $oldCheckInAttendees, 'new' => $attendees, 'note' => $cleanNote]
        );

        return [
            'status' => 200,
            'payload' => [
                'success' => true,
                'message' => "Đã điều chỉnh sĩ số khách {$guest->name} thành {$attendees} suất.",
                'checked_in_attendees' => $guest->checked_in_attendees,
                'checked_in_at' => $guest->checked_in_at->format('H:i d/m/Y'),
            ],
        ];
    }

    /**
     * Convert a previously declined guest to attending & checked-in across Live (transactional) or Dry-Run adapter.
     *
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function convertDeclined(Invitation $invitation, int $guestId, ?string $tableName, ?int $attendees, bool $isDryRun): array
    {
        if ($isDryRun) {
            $res = $this->dryRun->convertDeclined($invitation, $guestId, $tableName, $attendees);

            return [
                'status' => $res['success'] ? 200 : 404,
                'payload' => $res,
            ];
        }

        $wedding = $invitation->wedding;

        /** @var Guest $guest */
        $guest = DB::transaction(function () use ($wedding, $guestId, $tableName, $attendees): Guest {
            $lockedGuest = $wedding->guests()->with('response')->lockForUpdate()->findOrFail($guestId);
            $finalAttendees = ($attendees !== null && $attendees > 0)
                ? $attendees
                : max(1, (int) ($lockedGuest->expected_attendees ?: 1));

            $updateData = [
                'checked_in_at' => now(),
                'checked_in_attendees' => $finalAttendees,
                'check_in_note' => trim(($lockedGuest->check_in_note ? $lockedGuest->check_in_note.' | ' : '').'[Khách đổi ý sang tham dự]'),
            ];

            if ($tableName !== null && trim($tableName) !== '') {
                $rawTable = trim($tableName);
                $matchedTable = $wedding->tables()->where('name', $rawTable)->first();
                $updateData['table_name'] = $rawTable;
                $updateData['table_id'] = $matchedTable?->id;
            }

            $lockedGuest->update($updateData);

            GuestResponse::updateOrCreate(
                ['guest_id' => $lockedGuest->id],
                [
                    'wedding_id' => $wedding->id,
                    'guest_name' => $lockedGuest->name,
                    'attending_status' => 'attending',
                    'adults_count' => $finalAttendees,
                    'children_count' => 0,
                ]
            );

            return $lockedGuest->fresh();
        });

        return [
            'status' => 200,
            'payload' => [
                'success' => true,
                'message' => "Đã chuyển trạng thái đón tiếp cho khách {$guest->name} thành công!",
                'guest' => [
                    'id' => $guest->id,
                    'name' => $guest->name,
                    'table_name' => $guest->table_name ?: 'Chưa xếp bàn',
                    'checked_in_attendees' => $guest->checked_in_attendees,
                    'checked_in_at' => $guest->checked_in_at->format('H:i d/m/Y'),
                ],
            ],
        ];
    }
}
