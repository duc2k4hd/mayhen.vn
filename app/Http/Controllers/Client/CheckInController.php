<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Services\ReceptionDryRunService;
use App\Services\StaffShiftHandoverService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CheckInController extends Controller
{
    /**
     * Hiển thị Cổng Tiếp Tân Điểm Danh & Quét Mã QR Ngày Cưới.
     */
    public function showPortal(Request $request, ?string $slug = null): View|RedirectResponse
    {
        if (! $slug) {
            $customDomain = $request->attributes->get('custom_wedding_domain');
            if ($customDomain && $customDomain->invitation) {
                $slug = $customDomain->invitation->slug;
            }
        }
        abort_unless($slug, 404, 'Không tìm thấy thông tin đám cưới.');

        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return view('client.pages.invitation-locked', [
                'invitation' => $invitation,
                'lockReason' => $config['lock_reason'] ?? 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
            ]);
        }

        // Kiểm tra quyền: Chủ tiệc hoặc Tiếp tân đã nhập đúng PIN
        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return view('client.pages.checkin-pin', [
                'invitation' => $invitation,
                'wedding' => $wedding,
            ]);
        }

        $isDryRun = $request->boolean('dry_run') || (bool) $request->session()->get('is_dry_run_'.$invitation->id, false);
        if ($request->boolean('dry_run')) {
            $request->session()->put('is_dry_run_'.$invitation->id, true);
            $isDryRun = true;
        }

        $currentShift = $request->session()->get("reception_shift_{$invitation->id}");

        if ($isDryRun) {
            $dryRunService = app(ReceptionDryRunService::class);
            $dryRunState = $dryRunService->getDryRunState($invitation);

            return view('client.pages.checkin', [
                'invitation' => $invitation,
                'wedding' => $wedding,
                'totalExpected' => $dryRunState['stats']['total_expected'],
                'checkedInCount' => $dryRunState['stats']['checked_in_count'],
                'checkedInGuestsCount' => $dryRunState['stats']['checked_in_guests'],
                'totalGuestsCount' => $dryRunState['stats']['total_guests'],
                'recentCheckIns' => collect($dryRunState['guests'])->whereNotNull('checked_in_at')->values(),
                'tables' => $dryRunState['tables'],
                'isDryRun' => true,
                'dryRunGuests' => $dryRunState['guests'],
                'currentShift' => $currentShift,
            ]);
        }

        // Thống kê nhanh đón tiếp
        $allGuests = $wedding->guests()->with('response')->get();
        $totalExpected = (int) $allGuests->sum(function (Guest $g) {
            return $g->actualAttendeesCount();
        });
        $checkedInCount = (int) $allGuests->whereNotNull('checked_in_at')->sum(function (Guest $g) {
            return $g->checked_in_attendees ?: $g->actualAttendeesCount();
        });
        $checkedInGuestsCount = $allGuests->whereNotNull('checked_in_at')->count();
        $totalGuestsCount = $allGuests->count();

        // 10 khách mới check-in gần nhất
        $recentCheckIns = $wedding->guests()
            ->whereNotNull('checked_in_at')
            ->orderByDesc('checked_in_at')
            ->limit(10)
            ->get();

        // Danh sách bàn tiệc để chọn nhanh (kết hợp từ Seating Planner và Guest table_name)
        $plannerTables = $wedding->tables()->pluck('name')->filter()->toArray();
        $guestTables = $wedding->guests()
            ->whereNotNull('table_name')
            ->distinct()
            ->pluck('table_name')
            ->toArray();
        $tables = array_values(array_unique(array_merge($plannerTables, $guestTables)));

        return view('client.pages.checkin', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'totalExpected' => $totalExpected,
            'checkedInCount' => $checkedInCount,
            'checkedInGuestsCount' => $checkedInGuestsCount,
            'totalGuestsCount' => $totalGuestsCount,
            'recentCheckIns' => $recentCheckIns,
            'tables' => $tables,
            'isDryRun' => false,
            'dryRunGuests' => [],
            'currentShift' => $currentShift,
        ]);
    }

    /**
     * Xác thực mã PIN tiếp tân.
     */
    public function verifyPin(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        $request->validate([
            'pin' => ['required', 'string', 'max:20'],
        ], [
            'pin.required' => 'Vui lòng nhập mã PIN tiếp tân.',
        ]);

        $inputPin = trim((string) $request->input('pin'));
        $correctPin = $this->getReceptionistPin($invitation);

        if (! hash_equals($correctPin, $inputPin)) {
            // Kiểm tra mã PIN ca trực tiếp tân (TASK-VAL-23)
            $shift = app(StaffShiftHandoverService::class)->authenticateShiftPin($invitation, $inputPin);
            if ($shift) {
                $request->session()->put("receptionist_verified_{$invitation->id}", true);
                $request->session()->put("reception_shift_{$invitation->id}", $shift);

                return redirect()->route('checkin.portal', $slug)
                    ->with('success', 'Đã đăng nhập ca trực: '.$shift['name'].' ('.$shift['staff_name'].')');
            }

            return back()->with('error', 'Mã PIN tiếp tân không chính xác. Vui lòng hỏi cô dâu chú rể.');
        }

        $request->session()->put("receptionist_verified_{$invitation->id}", true);
        $request->session()->forget("reception_shift_{$invitation->id}");

        return redirect()->route('checkin.portal', $slug)
            ->with('success', 'Đã mở khóa Cổng Tiếp Tân Điểm Danh thành công!');
    }

    /**
     * Xử lý quét mã QR điểm danh (Gọi bằng AJAX từ Camera hoặc nhập Token).
     */
    public function processScan(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc tiếp tân đã hết hạn. Vui lòng tải lại trang và nhập lại mã PIN.',
            ], 403);
        }

        $validated = $request->validate([
            'token' => ['nullable', 'string', 'max:64'],
            'guest_id' => ['nullable', 'integer'],
            'attendees' => ['nullable', 'integer', 'min:1', 'max:50'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->session()->get('is_dry_run_'.$invitation->id, false)) {
            $dryRunResult = app(ReceptionDryRunService::class)->processScan($invitation, $validated);

            return response()->json($dryRunResult, $dryRunResult['success'] ? 200 : 404);
        }

        $query = $wedding->guests()->with('response');

        if (! empty($validated['token'])) {
            $token = trim($validated['token']);
            // Nếu token chứa URL đầy đủ (ví dụ quét trực tiếp link), bóc tách token
            if (str_contains($token, 'token=')) {
                parse_str((string) parse_url($token, PHP_URL_QUERY), $queryParams);
                $token = $queryParams['token'] ?? $token;
            } elseif (str_contains($token, '/diem-danh/')) {
                $parts = explode('/diem-danh/', $token);
                $token = end($parts);
            }
            $query->where('guest_token', $token);
        } elseif (! empty($validated['guest_id'])) {
            $query->where('id', (int) $validated['guest_id']);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp mã QR hoặc ID khách mời.',
            ], 422);
        }

        /** @var Guest|null $guest */
        $guest = $query->first();

        if (! $guest) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin khách mời trong danh sách đám cưới này.',
            ], 404);
        }

        $alreadyCheckedIn = $guest->checked_in_at !== null;
        $previousCheckInTime = $guest->checked_in_at ? $guest->checked_in_at->format('H:i') : null;

        if (! $alreadyCheckedIn) {
            $attendeesCount = ! empty($validated['attendees'])
                ? (int) $validated['attendees']
                : $guest->actualAttendeesCount();

            $currentShift = $request->session()->get("reception_shift_{$invitation->id}");
            $staffPrefix = $currentShift ? "[{$currentShift['staff_name']}] " : '';
            $checkInNote = $validated['note'] ?? $guest->check_in_note;
            if ($staffPrefix !== '' && (! $checkInNote || ! str_contains((string) $checkInNote, $staffPrefix))) {
                $checkInNote = $staffPrefix.($checkInNote ?: 'Điểm danh đón tiếp');
            }

            $guest->update([
                'checked_in_at' => now(),
                'checked_in_attendees' => $attendeesCount,
                'check_in_note' => $checkInNote,
            ]);
        }

        // Cập nhật lại thống kê
        $allGuests = $wedding->guests()->with('response')->get();
        $totalExpected = (int) $allGuests->sum(function (Guest $g) {
            return $g->actualAttendeesCount();
        });
        $checkedInCount = (int) $allGuests->whereNotNull('checked_in_at')->sum(function (Guest $g) {
            return $g->checked_in_attendees ?: $g->actualAttendeesCount();
        });

        return response()->json([
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
            'stats' => [
                'checked_in_count' => $checkedInCount,
                'total_expected' => $totalExpected,
                'checked_in_guests' => $allGuests->whereNotNull('checked_in_at')->count(),
                'total_guests' => $allGuests->count(),
            ],
        ]);
    }

    /**
     * Tìm kiếm nhanh khách mời khi tiếp tân gõ tên hoặc SĐT.
     */
    public function quickSearch(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $keyword = trim((string) $request->query('q', ''));

        if ($request->session()->get('is_dry_run_'.$invitation->id, false)) {
            $dryRunGuests = app(ReceptionDryRunService::class)->quickSearch($invitation, $keyword);

            return response()->json(['guests' => $dryRunGuests, 'is_dry_run' => true]);
        }

        if (mb_strlen($keyword) < 1) {
            return response()->json(['guests' => []]);
        }

        $guests = $wedding->guests()
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('table_name', 'like', "%{$keyword}%")
                    ->orWhere('group_name', 'like', "%{$keyword}%");
            })
            ->limit(15)
            ->get()
            ->map(function (Guest $g) {
                return [
                    'id' => $g->id,
                    'name' => $g->name,
                    'salutation' => $g->salutation,
                    'group_name' => $g->group_name,
                    'table_name' => $g->table_name ?: 'Chưa xếp bàn',
                    'expected_attendees' => $g->expected_attendees,
                    'is_checked_in' => $g->checked_in_at !== null,
                    'checked_in_time' => $g->checked_in_at ? $g->checked_in_at->format('H:i') : null,
                ];
            });

        return response()->json(['guests' => $guests]);
    }

    /**
     * Hủy điểm danh cho khách (trường hợp tiếp tân bấm nhầm).
     */
    public function undo(Request $request, string $slug, int $guestId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $guest = $wedding->guests()->findOrFail($guestId);
        $guest->update([
            'checked_in_at' => null,
            'checked_in_attendees' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã hủy điểm danh cho khách {$guest->name}.",
        ]);
    }

    /**
     * Cập nhật nhanh vị trí bàn tiệc cho khách.
     */
    public function updateTable(Request $request, string $slug, int $guestId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'table_name' => ['required', 'string', 'max:100'],
        ]);

        if ($request->session()->get('is_dry_run_'.$invitation->id, false)) {
            app(ReceptionDryRunService::class)->updateTable($invitation, $guestId, $validated['table_name']);

            return response()->json([
                'success' => true,
                'is_dry_run' => true,
                'message' => "[Diễn Tập] Đã cập nhật bàn tiệc: {$validated['table_name']}",
                'table_name' => trim($validated['table_name']),
            ]);
        }

        $guest = $wedding->guests()->findOrFail($guestId);
        $newTableName = trim($validated['table_name']);
        $matchedTable = $wedding->tables()->where('name', $newTableName)->first();
        $tableId = $matchedTable?->id;

        $guest->update([
            'table_name' => $newTableName,
            'table_id' => $tableId,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật bàn tiệc: {$guest->table_name}",
            'table_name' => $guest->table_name,
            'table_id' => $tableId,
        ]);
    }

    /**
     * Bàn Xử Lý Ngoại Lệ: Thêm khách phát sinh (Walk-in) tại cửa tiệc (TASK-VAL-21).
     */
    public function walkInGuest(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'salutation' => ['nullable', 'string', 'max:50'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'table_name' => ['nullable', 'string', 'max:100'],
            'attendees' => ['required', 'integer', 'min:1', 'max:20'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Vui lòng nhập tên khách phát sinh.',
            'attendees.required' => 'Vui lòng chọn số lượng khách đi cùng.',
        ]);

        if ($request->session()->get('is_dry_run_'.$invitation->id, false)) {
            $dryRunGuest = [
                'id' => rand(9100, 9999),
                'name' => trim($validated['name']),
                'salutation' => trim($validated['salutation'] ?? 'Bạn'),
                'group_name' => trim($validated['group_name'] ?? 'Khách phát sinh'),
                'table_name' => trim($validated['table_name'] ?? 'Chưa xếp bàn'),
                'expected_attendees' => (int) $validated['attendees'],
                'checked_in_attendees' => (int) $validated['attendees'],
                'checked_in_at' => now()->format('H:i'),
                'check_in_note' => trim('[Khách phát sinh] '.($validated['note'] ?? '')),
                'guest_token' => 'dryrun_walkin_'.Str::random(8),
                'dietary' => null,
                'wishes' => null,
            ];
            $state = app(ReceptionDryRunService::class)->getDryRunState($invitation);
            $state['guests'][] = $dryRunGuest;
            $state['stats']['total_guests']++;
            $state['stats']['total_expected'] += (int) $validated['attendees'];
            $state['stats']['checked_in_guests']++;
            $state['stats']['checked_in_count'] += (int) $validated['attendees'];
            session()->put('reception_dry_run_'.$invitation->id, $state);

            return response()->json([
                'success' => true,
                'is_dry_run' => true,
                'message' => '[Diễn Tập] Đã thêm và đón khách phát sinh vào bàn thành công!',
                'guest' => $dryRunGuest,
            ], 201);
        }

        $attendees = (int) $validated['attendees'];
        $tableName = ! empty($validated['table_name']) ? trim($validated['table_name']) : null;
        $tableId = null;
        if ($tableName !== null) {
            $matchedTable = $wedding->tables()->where('name', $tableName)->first();
            $tableId = $matchedTable?->id;
        }

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => trim($validated['name']),
            'salutation' => trim($validated['salutation'] ?? 'Bạn'),
            'group_name' => trim($validated['group_name'] ?? 'Khách phát sinh'),
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
            'guest_id' => $guest->id,
            'guest_name' => $guest->name,
            'attending_status' => 'attending',
            'adults_count' => $attendees,
            'children_count' => 0,
        ]);

        return response()->json([
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
        ], 201);
    }

    /**
     * Bàn Xử Lý Ngoại Lệ: Điều chỉnh sĩ số thực tế khách đi kèm (+1/+2) (TASK-VAL-21).
     */
    public function adjustAttendees(Request $request, string $slug, int $guestId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'attendees' => ['required', 'integer', 'min:1', 'max:50'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $attendees = (int) $validated['attendees'];

        if ($request->session()->get('is_dry_run_'.$invitation->id, false)) {
            $state = app(ReceptionDryRunService::class)->getDryRunState($invitation);
            foreach ($state['guests'] as $k => $g) {
                if ($g['id'] === $guestId) {
                    $oldAttendees = $g['checked_in_attendees'] ?? $g['expected_attendees'];
                    $diff = $attendees - $oldAttendees;
                    $state['guests'][$k]['checked_in_attendees'] = $attendees;
                    $state['guests'][$k]['checked_in_at'] = $g['checked_in_at'] ?: now()->format('H:i');
                    $state['stats']['checked_in_count'] += $diff;
                    session()->put('reception_dry_run_'.$invitation->id, $state);

                    return response()->json([
                        'success' => true,
                        'is_dry_run' => true,
                        'message' => "[Diễn Tập] Đã điều chỉnh sĩ số khách thành {$attendees} suất.",
                        'checked_in_attendees' => $attendees,
                    ]);
                }
            }
        }

        $guest = $wedding->guests()->findOrFail($guestId);
        $oldCheckInAttendees = $guest->checked_in_attendees ?: $guest->actualAttendeesCount();
        $note = ! empty($validated['note']) ? trim($validated['note']) : "Điều chỉnh sĩ số từ {$oldCheckInAttendees} lên {$attendees} suất";

        $guest->update([
            'checked_in_at' => $guest->checked_in_at ?: now(),
            'checked_in_attendees' => $attendees,
            'check_in_note' => trim(($guest->check_in_note ? $guest->check_in_note.' | ' : '').$note),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã điều chỉnh sĩ số khách {$guest->name} thành {$attendees} suất.",
            'checked_in_attendees' => $guest->checked_in_attendees,
            'checked_in_at' => $guest->checked_in_at->format('H:i d/m/Y'),
        ]);
    }

    /**
     * Bàn Xử Lý Ngoại Lệ: Chuyển khách báo bận (declined) sang tham dự (TASK-VAL-21).
     */
    public function convertDeclined(Request $request, string $slug, int $guestId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'table_name' => ['nullable', 'string', 'max:100'],
            'attendees' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $guest = $wedding->guests()->with('response')->findOrFail($guestId);
        $attendees = ! empty($validated['attendees']) ? (int) $validated['attendees'] : $guest->actualAttendeesCount();

        $updateData = [
            'checked_in_at' => now(),
            'checked_in_attendees' => $attendees,
            'check_in_note' => trim(($guest->check_in_note ? $guest->check_in_note.' | ' : '').'[Khách đổi ý sang tham dự]'),
        ];
        if (! empty($validated['table_name'])) {
            $updateData['table_name'] = trim($validated['table_name']);
        }
        $guest->update($updateData);

        if ($guest->response) {
            $guest->response->update([
                'attending_status' => 'attending',
                'adults_count' => $attendees,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Đã chuyển trạng thái đón tiếp cho khách {$guest->name} thành công!",
            'guest' => [
                'id' => $guest->id,
                'name' => $guest->name,
                'table_name' => $guest->table_name ?: 'Chưa xếp bàn',
                'checked_in_attendees' => $guest->checked_in_attendees,
                'checked_in_at' => $guest->checked_in_at->format('H:i d/m/Y'),
            ],
        ]);
    }

    /**
     * Bật chế độ diễn tập đón tiếp (TASK-VAL-20).
     */
    public function enableDryRun(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            abort(403, 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.');
        }

        $request->session()->put('is_dry_run_'.$invitation->id, true);

        return redirect()->route('checkin.portal', ['slug' => $slug, 'dry_run' => 1])
            ->with('success', 'Đã chuyển sang Chế Độ Diễn Tập Lễ Tân. Dữ liệu thử nghiệm hoàn toàn cách ly.');
    }

    /**
     * Tắt chế độ diễn tập đón tiếp.
     */
    public function disableDryRun(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            abort(403, 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.');
        }

        $request->session()->forget('is_dry_run_'.$invitation->id);
        app(ReceptionDryRunService::class)->resetDryRun($invitation);

        return redirect()->route('checkin.portal', ['slug' => $slug])
            ->with('success', 'Đã quay trở lại Cổng Tiếp Tân Đón Tiếp Thực Tế.');
    }

    /**
     * Làm mới dữ liệu diễn tập đón tiếp.
     */
    public function resetDryRun(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            abort(403, 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.');
        }

        app(ReceptionDryRunService::class)->resetDryRun($invitation);

        return redirect()->route('checkin.portal', ['slug' => $slug, 'dry_run' => 1])
            ->with('success', 'Đã làm mới toàn bộ 5 kịch bản khách mẫu diễn tập.');
    }

    /**
     * Khách quét mã QR bằng camera thường của điện thoại: Mở trang Concierge đón tiếp VIP.
     */
    public function directScan(Request $request, string $guestToken): View|RedirectResponse
    {
        return $this->guestConcierge($request, $guestToken);
    }

    /**
     * Trang Hành Trình Khách Mời Cá Nhân Hóa (One-Page Guest Concierge - TASK-VAL-13).
     * Trợ lý số 1 trang cho khách mời: Lời chào cá nhân, bàn tiệc, QR Check-in,
     * thời gian, chỉ đường Google Maps, hướng dẫn bãi đỗ xe, lưu lịch Calendar và VietQR mừng cưới.
     */
    public function guestConcierge(Request $request, string $guestToken): View
    {
        $guest = Guest::with(['wedding.invitations.template'])->where('guest_token', $guestToken)->firstOrFail();
        $wedding = $guest->wedding;
        $invitation = $wedding->invitations()->first();

        if (! $invitation) {
            abort(404, 'Không tìm thấy thiệp cưới.');
        }

        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);
        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return view('client.pages.invitation-locked', [
                'invitation' => $invitation,
                'lockReason' => $config['lock_reason'] ?? 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
            ]);
        }

        // 1. Lời nhắn riêng cho nhóm khách (Persona message)
        $personaMessage = ! empty($guest->group_name) && ! empty($config['persona_messages'][$guest->group_name])
            ? $config['persona_messages'][$guest->group_name]
            : null;

        // 2. Hướng dẫn bãi đỗ xe & lối vào sảnh tiệc
        $parkingGuide = is_array($config['parking_guide'] ?? null) ? $config['parking_guide'] : [];

        // 3. Phản hồi xác nhận tham dự (RSVP) hiện tại
        $existingResponse = GuestResponse::where('wedding_id', $wedding->id)->where('guest_id', $guest->id)->first();

        // 4. Link Google Calendar 1 chạm
        $googleCalendarUrl = $this->buildGoogleCalendarUrl($wedding, $extra, $guest);

        return view('client.pages.guest-concierge', [
            'guest' => $guest,
            'wedding' => $wedding,
            'invitation' => $invitation,
            'extra' => $extra,
            'config' => $config,
            'personaMessage' => $personaMessage,
            'parkingGuide' => $parkingGuide,
            'existingResponse' => $existingResponse,
            'googleCalendarUrl' => $googleCalendarUrl,
        ]);
    }

    /**
     * Tải file iCalendar (.ics) cho khách thêm vào Apple Calendar / Outlook 1 chạm.
     */
    public function downloadIcs(Request $request, string $guestToken): Response
    {
        $guest = Guest::with(['wedding.invitations'])->where('guest_token', $guestToken)->firstOrFail();
        $wedding = $guest->wedding;

        $invitation = $wedding->invitations()->first();
        if ($invitation) {
            $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);
            if (! empty($config['is_locked'])) {
                abort(403, 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.');
            }
        }

        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);

        $dateStr = $wedding->wedding_date ? $wedding->wedding_date->format('Ymd') : date('Ymd');
        $timeStr = trim((string) ($extra['party_time'] ?? ($extra['event_party_time'] ?? '18:00')));
        $cleanTime = str_replace(':', '', substr($timeStr, 0, 5)).'00';
        if (strlen($cleanTime) !== 6) {
            $cleanTime = '180000';
        }

        $startUtc = $dateStr.'T'.$cleanTime;
        $endHour = str_pad((int) substr($cleanTime, 0, 2) + 3, 2, '0', STR_PAD_LEFT);
        $endUtc = $dateStr.'T'.$endHour.substr($cleanTime, 2);

        $title = "Hôn Lễ: {$wedding->groom_name} & {$wedding->bride_name}";
        $venue = trim((string) ($wedding->venue_name ?? ($extra['party_venue'] ?? ($extra['event_party_venue'] ?? 'Sảnh Tiệc Cưới'))));
        $address = trim((string) ($wedding->venue_address ?? ($extra['event_party_address'] ?? '')));
        $location = $venue.($address !== '' ? ', '.$address : '');
        $description = "Trân trọng kính mời {$guest->salutation} {$guest->name} tới chung vui cùng đôi uyên ương tại {$venue}.";

        $icsContent = "BEGIN:VCALENDAR\r\n"
            ."VERSION:2.0\r\n"
            ."PRODID:-//May Hen//Wedding Concierge//VI\r\n"
            ."CALSCALE:GREGORIAN\r\n"
            ."METHOD:PUBLISH\r\n"
            ."BEGIN:VEVENT\r\n"
            .'UID:'.md5($guest->id.$wedding->id)."@mayhen.vn\r\n"
            .'DTSTAMP:'.gmdate('Ymd\THis\Z')."\r\n"
            .'DTSTART;TZID=Asia/Ho_Chi_Minh:'.$startUtc."\r\n"
            .'DTEND;TZID=Asia/Ho_Chi_Minh:'.$endUtc."\r\n"
            .'SUMMARY:'.$title."\r\n"
            .'DESCRIPTION:'.$description."\r\n"
            .'LOCATION:'.$location."\r\n"
            ."STATUS:CONFIRMED\r\n"
            ."END:VEVENT\r\n"
            ."END:VCALENDAR\r\n";

        $filename = 'thiep-cuoi-'.Str::slug($wedding->groom_name.'-'.$wedding->bride_name).'.ics';

        return response($icsContent, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Tạo đường dẫn thêm vào Google Calendar 1 chạm.
     *
     * @param  array<string, mixed>  $extra
     */
    private function buildGoogleCalendarUrl(Wedding $wedding, array $extra, Guest $guest): string
    {
        $title = "Hôn Lễ: {$wedding->groom_name} & {$wedding->bride_name}";
        $venue = trim((string) ($wedding->venue_name ?? ($extra['party_venue'] ?? ($extra['event_party_venue'] ?? 'Sảnh Tiệc Cưới'))));
        $address = trim((string) ($wedding->venue_address ?? ($extra['event_party_address'] ?? '')));
        $location = $venue.($address !== '' ? ', '.$address : '');
        $details = "Trân trọng kính mời {$guest->salutation} {$guest->name} tới chung vui cùng đôi uyên ương {$wedding->groom_name} & {$wedding->bride_name} tại {$venue}.";

        $dateStr = $wedding->wedding_date ? $wedding->wedding_date->format('Ymd') : date('Ymd');
        $timeStr = trim((string) ($extra['party_time'] ?? ($extra['event_party_time'] ?? '18:00')));
        $cleanTime = str_replace(':', '', substr($timeStr, 0, 5)).'00';
        if (strlen($cleanTime) !== 6) {
            $cleanTime = '180000';
        }
        $start = $dateStr.'T'.$cleanTime;
        $endHour = str_pad((int) substr($cleanTime, 0, 2) + 3, 2, '0', STR_PAD_LEFT);
        $end = $dateStr.'T'.$endHour.substr($cleanTime, 2);

        return 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            .'&text='.urlencode($title)
            .'&dates='.$start.'/'.$end
            .'&details='.urlencode($details)
            .'&location='.urlencode($location);
    }

    /**
     * Kiểm tra quyền của ban tiếp tân hoặc cặp đôi.
     */
    private function isAuthorizedReceptionist(Request $request, Invitation $invitation, Wedding $wedding): bool
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return false;
        }

        // 1. Cặp đôi đã đăng nhập
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return true;
        }

        // 2. Cặp đôi có cookie/session sở hữu thiệp
        $ownedSlugs = (array) $request->session()->get('owned_invitation_slugs', []);
        if (in_array($invitation->slug, $ownedSlugs, true)) {
            return true;
        }

        // 3. Tiếp tân đã nhập đúng PIN trong phiên làm việc
        return $request->session()->get("receptionist_verified_{$invitation->id}") === true;
    }

    /**
     * Lấy mã PIN tiếp tân (cấu hình hoặc sinh tự động).
     */
    private function getReceptionistPin(Invitation $invitation): string
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['checkin_pin'])) {
            return (string) $config['checkin_pin'];
        }

        // Bảo mật: Sinh mã PIN ngẫu nhiên bảo mật 6 chữ số và lưu vào cấu hình thiệp
        // Không dùng ngày cưới hay ID công khai để chống lộ dữ liệu khách mời (Luật 91/2025/QH15)
        $securePin = (string) random_int(100000, 999999);
        $config['checkin_pin'] = $securePin;
        $invitation->custom_config = $config;
        $invitation->saveQuietly();

        return $securePin;
    }

    /**
     * Xác thực quyền chủ sở hữu thiệp cưới của cặp đôi (IDOR Defense).
     */
    private function authorizeOwner(Invitation $invitation): void
    {
        $wedding = $invitation->wedding;
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        if (auth()->check() && auth()->user()->partner && (int) $wedding->partner_id === (int) auth()->user()->partner->id) {
            return;
        }

        if (auth()->check() && auth()->user()->isAdmin()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if (in_array($invitation->slug, $ownedSlugs, true)) {
            return;
        }

        abort(404);
    }

    /**
     * API Lấy biên bản tóm tắt ca trực hiện tại để bàn giao (TASK-VAL-23).
     */
    public function getHandoverSummary(Request $request, string $slug, StaffShiftHandoverService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc tiếp tân đã hết hạn.',
            ], 403);
        }

        $currentShift = $request->session()->get("reception_shift_{$invitation->id}");
        $shiftId = $request->input('shift_id') ?: ($currentShift['id'] ?? 'current_shift');

        $summary = $service->generateHandoverSummary($invitation, $shiftId);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data' => $summary,
        ]);
    }

    /**
     * API Lưu biên bản bàn giao ca trực (TASK-VAL-23).
     */
    public function submitShiftHandover(Request $request, string $slug, StaffShiftHandoverService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc tiếp tân đã hết hạn.',
            ], 403);
        }

        $validated = $request->validate([
            'handed_to_staff' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'shift_id' => ['nullable', 'string', 'max:50'],
        ]);

        $currentShift = $request->session()->get("reception_shift_{$invitation->id}");
        $shiftId = $validated['shift_id'] ?? ($currentShift['id'] ?? 'shift_default');

        $summaryData = $service->generateHandoverSummary($invitation, $shiftId)['summary'] ?? [];

        $record = $service->recordHandover($invitation, $shiftId, [
            'handed_to_staff' => $validated['handed_to_staff'],
            'notes' => $validated['notes'] ?? '',
            'summary' => $summaryData,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu biên bản bàn giao ca trực thành công!',
            'handover' => $record,
            'data' => $record,
        ]);
    }

    /**
     * API Cặp đôi xem danh sách ca trực lễ tân (TASK-VAL-23).
     */
    public function getShifts(int $invitationId, StaffShiftHandoverService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation);

        $shifts = $service->getShifts($invitation);

        return response()->json([
            'success' => true,
            'shifts' => $shifts,
            'data' => $shifts,
        ]);
    }

    /**
     * API Cặp đôi tạo hoặc sửa ca trực lễ tân (TASK-VAL-23).
     */
    public function saveShift(Request $request, int $invitationId, StaffShiftHandoverService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation);

        $validated = $request->validate([
            'id' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'staff_name' => ['required', 'string', 'max:100'],
            'staff_phone' => ['nullable', 'string', 'max:20'],
            'pin' => ['required', 'string', 'min:4', 'max:8'],
            'start_time' => ['nullable', 'string', 'max:10'],
            'end_time' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $savedShift = $service->saveShift($invitation, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu thông tin ca trực lễ tân thành công!',
            'shift' => $savedShift,
            'data' => $savedShift,
        ]);
    }

    /**
     * API Cặp đôi xóa ca trực lễ tân (TASK-VAL-23).
     */
    public function deleteShift(int $invitationId, string $shiftId, StaffShiftHandoverService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation);

        $deleted = $service->deleteShift($invitation, $shiftId);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Đã xóa ca trực lễ tân.' : 'Không tìm thấy ca trực cần xóa.',
        ]);
    }
}
