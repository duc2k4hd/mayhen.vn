<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\SubmitRsvpRequest;
use App\Http\Requests\SubmitWishRequest;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Rules\NoProfanity;
use App\Services\AuditLogger;
use App\Services\ContentModerationService;
use App\Services\GentleReminderPlannerService;
use App\Services\GuestInteractionService;
use App\Services\GuestRosterCsvService;
use App\Services\InvitationDeliveryService;
use App\Services\PersonalThankYouService;
use App\Services\PreSendValidationService;
use App\Services\RsvpLedgerService;
use App\Services\WeddingGuestMetricsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestController extends Controller
{
    /**
     * Display guest management and RSVP dashboard.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        // Danh sách nhóm và bàn tiệc độc nhất để hiển thị dropdown bộ lọc
        $availableGroups = Guest::where('wedding_id', $wedding->id)
            ->whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name');

        $availableTables = Guest::where('wedding_id', $wedding->id)
            ->whereNotNull('table_name')
            ->distinct()
            ->pluck('table_name');

        // Thống kê tổng quan chuẩn (toàn bộ đám cưới - khử trùng đa sự kiện & chống vượt 100%)
        $guestMetrics = app(WeddingGuestMetricsService::class)->forWedding($wedding);
        $totalInvited = $guestMetrics['expected_seats_sum'];
        $totalGuestsCount = $guestMetrics['invited_links_count'];
        $totalResponses = $guestMetrics['responded_invited_count'];
        $attendingCount = $guestMetrics['confirmed_people'];
        $declinedCount = $guestMetrics['declined_links_count'];
        $responseRate = $guestMetrics['response_rate'];
        $checkedInCount = $guestMetrics['checked_in_people'];
        $openedCount = $guestMetrics['opened_links_count'];

        // Xử lý bộ lọc danh sách hiển thị
        $guestsQuery = $wedding->guests()->with(['response', 'responses']);

        if ($search = trim((string) $request->query('q'))) {
            $guestsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%");
            });
        }

        if ($groupFilter = $request->query('group')) {
            $guestsQuery->where('group_name', $groupFilter);
        }

        if ($sideFilter = $request->query('side')) {
            if ($sideFilter === 'groom') {
                $guestsQuery->where('group_name', 'like', '%Nhà Trai%');
            } elseif ($sideFilter === 'bride') {
                $guestsQuery->where('group_name', 'like', '%Nhà Gái%');
            }
        }

        if ($tableFilter = $request->query('table')) {
            $guestsQuery->where('table_name', $tableFilter);
        }

        if ($statusFilter = $request->query('status')) {
            if ($statusFilter === 'attending') {
                $guestsQuery->whereHas('responses', fn ($q) => $q->where('attending_status', 'attending'));
            } elseif ($statusFilter === 'declined') {
                $guestsQuery->whereHas('responses', fn ($q) => $q->where('attending_status', 'declined'))
                    ->whereDoesntHave('responses', fn ($q) => $q->where('attending_status', 'attending'));
            } elseif ($statusFilter === 'pending') {
                $guestsQuery->whereDoesntHave('responses');
            } elseif ($statusFilter === 'checked_in') {
                $guestsQuery->whereNotNull('checked_in_at');
            } elseif ($statusFilter === 'not_checked_in') {
                $guestsQuery->whereNull('checked_in_at');
            } elseif ($statusFilter === 'opened') {
                $guestsQuery->where(function ($q) {
                    $q->where('open_count', '>', 0)
                        ->orWhereNotNull('opened_at');
                });
            } elseif ($statusFilter === 'not_opened') {
                $guestsQuery->where(function ($q) {
                    $q->where('open_count', '<=', 0)
                        ->orWhereNull('open_count');
                })->whereNull('opened_at');
            }
        }

        $guests = $guestsQuery->latest()->paginate(100)->withQueryString();

        $deliveryStats = app(InvitationDeliveryService::class)->getDeliveryStats($wedding);
        $preSendReport = app(PreSendValidationService::class)->validate($wedding, $invitation);
        $allGuestIds = $wedding->guests()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $unsentGuestIds = $wedding->guests()->where('is_sent', false)->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        return view('client.pages.guests', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'guests' => $guests,
            'guestMetrics' => $guestMetrics,
            'preSendReport' => $preSendReport,
            'allGuestIds' => $allGuestIds,
            'unsentGuestIds' => $unsentGuestIds,
            'deliveryStats' => $deliveryStats,
            'totalInvited' => $totalInvited,
            'totalGuestsCount' => $totalGuestsCount,
            'totalResponses' => $totalResponses,
            'attendingCount' => $attendingCount,
            'declinedCount' => $declinedCount,
            'responseRate' => $responseRate,
            'checkedInCount' => $checkedInCount,
            'openedCount' => $openedCount,
            'availableGroups' => $availableGroups,
            'availableTables' => $availableTables,
            'filterSearch' => $search,
            'filterGroup' => $groupFilter,
            'filterSide' => $sideFilter,
            'filterTable' => $tableFilter,
            'filterStatus' => $statusFilter,
        ]);
    }

    /**
     * Store new guest with personalized link.
     */
    public function store(StoreGuestRequest $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $tableName = $request->validated('table_name') ? trim((string) $request->validated('table_name')) : null;
        $tableId = null;
        if (! empty($tableName)) {
            $matchedTable = $wedding->tables()->where('name', $tableName)->first();
            $tableId = $matchedTable?->id;
        }

        $validEventIds = null;
        if (is_array($request->validated('event_ids'))) {
            $validEventIds = $wedding->events()
                ->whereIn('id', $request->validated('event_ids'))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => $request->validated('name'),
            'salutation' => $request->validated('salutation') ?? 'Thân mời',
            'group_name' => $request->validated('group_name') ?? 'Bạn Bè',
            'table_name' => $tableName,
            'table_id' => $tableId,
            'phone' => $request->validated('phone'),
            'expected_attendees' => $request->validated('expected_attendees') ?? 1,
            'max_attendees' => $request->validated('max_attendees') ? (int) $request->validated('max_attendees') : null,
            'event_ids' => $validEventIds,
            'guest_token' => Str::random(32),
        ]);

        $redirect = redirect()->route('guests.index', $invitationId)
            ->with('success', 'Đã thêm khách mời thành công!');

        // Cảnh báo quá tải bàn tiệc nếu việc thêm khách vượt quá sức chứa của bàn (Flow 09)
        if ($tableId) {
            $table = $wedding->tables()->find($tableId);
            if ($table && $table->isOverCapacity()) {
                $over = $table->assigned_attendees_count - $table->capacity;
                $redirect->with('warning', "Lưu ý: Bàn \"{$table->name}\" đang bị quá tải ghế sau khi thêm khách ({$table->assigned_attendees_count}/{$table->capacity} người, thừa {$over} chỗ). Vui lòng điều chỉnh lại sơ đồ bàn tiệc.");
            }
        }

        return $redirect;
    }

    /**
     * Tải tệp CSV mẫu chuẩn để chuẩn bị danh sách khách mời (Section 28.3 PRD V3).
     */
    public function downloadCsvTemplate(int $invitationId): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $headers = [
            'Họ và Tên',
            'Danh Xưng',
            'Nhóm Khách',
            'Số Lượng Mời',
            'Số Điện Thoại',
            'Bàn Tiệc',
            'Ghi Chú',
        ];

        $sampleRows = [
            [
                'Bác Nguyễn Văn Hùng',
                'Kính mời Bác',
                'Họ Nhà Trai',
                '2',
                '0912345678',
                'Bàn VIP 01',
                'Người lớn tuổi cần ngồi gần lối đi',
            ],
            [
                'Bạn Lê Thị Mai',
                'Thân mời',
                'Bạn Đại Học',
                '1',
                '0987654321',
                'Bàn 06',
                'Bạn thân cùng lớp đại học',
            ],
            [
                'Gia đình Chú Tuấn',
                'Kính mời Gia đình',
                'Đồng Nghiệp',
                '3',
                '0901234567',
                'Bàn 08',
                'Có 1 em bé đi cùng',
            ],
        ];

        $fp = fopen('php://temp', 'r+');
        // UTF-8 BOM
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $headers);
        foreach ($sampleRows as $row) {
            fputcsv($fp, $row);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mau_danh_sach_khach_moi_mayhen.csv"',
        ]);
    }

    /**
     * Nhập danh sách khách mời thông minh từ tệp CSV (Section 28.3 PRD V3).
     */
    public function importCsv(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $request->validate([
            'csv_file' => ['required', 'file', 'max:5120'],
            'duplicate_mode' => ['nullable', 'string', 'in:skip,update'],
            'default_salutation' => ['nullable', 'string', 'max:50'],
            'default_group' => ['nullable', 'string', 'max:50'],
        ], [
            'csv_file.required' => 'Vui lòng chọn tệp CSV để tải lên.',
            'csv_file.file' => 'Tệp tải lên không hợp lệ.',
            'csv_file.max' => 'Dung lượng tệp CSV tối đa là 5MB.',
        ]);

        $file = $request->file('csv_file');
        $content = file_get_contents($file->getRealPath());
        if ($content === false) {
            return back()->with('error', 'Tệp CSV tải lên bị rỗng.');
        }

        try {
            $result = app(GuestRosterCsvService::class)->import($wedding, $content, [
                'duplicate_mode' => $request->input('duplicate_mode') ?: 'skip',
                'default_salutation' => $request->input('default_salutation') ?: 'Thân mời',
                'default_group' => $request->input('default_group') ?: 'Bạn Bè',
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Nhập nhanh nhiều khách mời cùng lúc (Batch Import).
     */
    public function batchStore(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $request->validate([
            'batch_input' => ['required', 'string', 'max:20000'],
            'default_salutation' => ['nullable', 'string', 'max:50'],
            'default_group' => ['nullable', 'string', 'max:50'],
        ], [
            'batch_input.required' => 'Vui lòng nhập danh sách khách mời.',
        ]);

        $defaultSalutation = $request->input('default_salutation') ?: 'Thân mời';
        $defaultGroup = $request->input('default_group') ?: 'Bạn Bè';

        $rawLines = preg_split("/\r\n|\n|\r/", (string) $request->input('batch_input'));
        $createdCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use ($rawLines, $wedding, $defaultSalutation, $defaultGroup, &$createdCount, &$skippedCount) {
            foreach ($rawLines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                // Hỗ trợ phân cách bằng dấu gạch đứng (|), dấu chấm phẩy (;) hoặc dấu tab
                $parts = preg_split('/[|;\t]/', $line);
                $name = strip_tags(trim($parts[0] ?? ''));
                if ($name === '') {
                    continue;
                }

                // Lọc bỏ các dòng chứa từ ngữ thô tục / cấm kỵ (Content Moderation)
                if (ContentModerationService::containsProfanity($name)) {
                    $skippedCount++;

                    continue;
                }

                $salutation = isset($parts[1]) && trim($parts[1]) !== '' ? strip_tags(trim($parts[1])) : strip_tags(trim($defaultSalutation));
                $group = isset($parts[2]) && trim($parts[2]) !== '' ? strip_tags(trim($parts[2])) : strip_tags(trim($defaultGroup));
                $attendees = isset($parts[3]) && is_numeric(trim($parts[3])) ? min(50, max(1, (int) trim($parts[3]))) : 2;
                $phone = isset($parts[4]) && trim($parts[4]) !== '' ? strip_tags(trim($parts[4])) : null;
                $table = isset($parts[5]) && trim($parts[5]) !== '' ? strip_tags(trim($parts[5])) : null;
                $tableClean = $table ? Str::limit($table, 100, '') : null;
                $tableId = null;
                if (! empty($tableClean)) {
                    $matchedTable = $wedding->tables()->where('name', $tableClean)->first();
                    $tableId = $matchedTable?->id;
                }

                Guest::create([
                    'wedding_id' => $wedding->id,
                    'name' => Str::limit($name, 190, ''),
                    'salutation' => Str::limit($salutation, 50, ''),
                    'group_name' => Str::limit($group, 50, ''),
                    'table_name' => $tableClean,
                    'table_id' => $tableId,
                    'phone' => $phone ? Str::limit($phone, 20, '') : null,
                    'expected_attendees' => $attendees,
                    'guest_token' => Str::random(32),
                ]);

                $createdCount++;
            }
        });

        if ($createdCount === 0) {
            return back()->with('error', 'Không tìm thấy thông tin khách mời hợp lệ để thêm.'.($skippedCount > 0 ? " (Đã lọc bỏ {$skippedCount} dòng chứa ngôn từ không phù hợp)." : ''));
        }

        $redirect = redirect()->route('guests.index', $invitationId)
            ->with('success', "Đã thêm thành công {$createdCount} khách mời vào danh sách!".($skippedCount > 0 ? " (Đã lọc bỏ {$skippedCount} dòng chứa từ ngữ không phù hợp)." : ''));

        // Cảnh báo nếu danh sách thêm vào làm bàn tiệc nào bị quá tải sức chứa
        $overloadedTables = $wedding->tables()->get()->filter->isOverCapacity();
        if ($overloadedTables->isNotEmpty()) {
            $tableDetails = $overloadedTables->map(fn ($t) => "Bàn \"{$t->name}\" ({$t->assigned_attendees_count}/{$t->capacity} người)")->implode(', ');
            $redirect->with('warning', "Lưu ý: Có bàn tiệc đang bị quá tải ghế sau khi thêm khách ({$tableDetails}). Vui lòng điều chỉnh lại sơ đồ bàn tiệc.");
        }

        return $redirect;
    }

    /**
     * Xuất danh sách khách mời dạng CSV UTF-8 BOM cho Excel (Hỗ trợ lọc theo tìm kiếm, nhóm, bàn, trạng thái).
     */
    public function exportCsv(Request $request, int $invitationId): StreamedResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $filters = [
            'q' => $request->query('q'),
            'group' => $request->query('group'),
            'side' => $request->query('side'),
            'table' => $request->query('table'),
            'status' => $request->query('status'),
        ];

        $filename = 'danh-sach-khach-moi-'.Str::slug($wedding->groom_name.'-'.$wedding->bride_name).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($wedding, $invitation, $filters): void {
            $handle = fopen('php://output', 'w');
            app(GuestRosterCsvService::class)->writeExportStream($wedding, $invitation, $filters, $handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Trang in danh sách khách đón tiếp & điểm danh bàn tiệc (Reception Check-in Sheet - Khổ A4).
     */
    public function print(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $filterStatus = $request->query('status', 'all');
        $filterGroup = $request->query('group');
        $filterTable = $request->query('table');
        $search = trim((string) $request->query('q'));

        $query = $wedding->guests()->with('response');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%");
            });
        }

        if ($filterGroup) {
            $query->where('group_name', $filterGroup);
        }

        if ($filterTable) {
            $query->where('table_name', $filterTable);
        }

        if ($filterStatus && $filterStatus !== 'all') {
            if ($filterStatus === 'attending') {
                $query->whereHas('response', fn ($q) => $q->where('attending_status', 'attending'));
            } elseif ($filterStatus === 'declined') {
                $query->whereHas('response', fn ($q) => $q->where('attending_status', 'declined'));
            } elseif ($filterStatus === 'pending') {
                $query->whereDoesntHave('response');
            } elseif ($filterStatus === 'checked_in') {
                $query->whereNotNull('checked_in_at');
            } elseif ($filterStatus === 'not_checked_in') {
                $query->whereNull('checked_in_at');
            }
        }

        $guests = $query->orderBy('group_name')->orderBy('name')->get();

        // Thống kê nhanh toàn diện
        $allGuests = $wedding->guests()->with('response')->get();
        $totalGuestsCount = $allGuests->count();
        $totalExpectedAttendees = (int) $allGuests->sum('expected_attendees');
        $attendingResponses = $wedding->guestResponses()->where('attending_status', 'attending')->get();
        $attendingGuestsCount = $allGuests->filter(fn ($g) => $g->response && $g->response->attending_status === 'attending')->count();
        $totalAdults = (int) $attendingResponses->sum('adults_count');
        $totalChildren = (int) $attendingResponses->sum('children_count');
        $totalVegetarian = (int) $attendingResponses->filter(fn ($r) => ! empty($r->dietary_requirements))->count();

        $availableGroups = Guest::where('wedding_id', $wedding->id)
            ->whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name');

        return view('client.pages.account.guests-print', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'guests' => $guests,
            'filterStatus' => $filterStatus,
            'filterGroup' => $filterGroup,
            'filterTable' => $filterTable,
            'filterSearch' => $search,
            'availableGroups' => $availableGroups,
            'totalGuestsCount' => $totalGuestsCount,
            'totalExpectedAttendees' => $totalExpectedAttendees,
            'attendingGuestsCount' => $attendingGuestsCount,
            'totalAdults' => $totalAdults,
            'totalChildren' => $totalChildren,
            'totalVegetarian' => $totalVegetarian,
        ]);
    }

    /**
     * Cập nhật thông tin khách mời.
     */
    public function update(Request $request, int $guestId): JsonResponse|RedirectResponse
    {
        $guest = Guest::with('wedding')->findOrFail($guestId);
        $this->authorizeGuestManagement($guest->wedding);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'salutation' => ['nullable', 'string', 'max:50'],
            'group_name' => ['nullable', 'string', 'max:50'],
            'table_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'expected_attendees' => ['required', 'integer', 'min:1', 'max:50'],
            'max_attendees' => ['nullable', 'integer', 'min:1', 'max:50'],
            'event_ids' => ['nullable', 'array'],
            'event_ids.*' => ['integer'],
        ], [
            'name.required' => 'Vui lòng nhập tên khách mời.',
            'expected_attendees.required' => 'Vui lòng nhập số người tham dự dự kiến.',
            'expected_attendees.min' => 'Số người tham dự tối thiểu là 1.',
        ]);

        $tableId = $guest->table_id;
        $tableName = $guest->table_name;
        if (array_key_exists('table_name', $validated)) {
            $rawTable = strip_tags(trim((string) $validated['table_name']));
            if ($rawTable === '') {
                $tableName = null;
                $tableId = null;
            } else {
                $tableName = $rawTable;
                $matchedTable = $guest->wedding->tables()->where('name', $rawTable)->first();
                $tableId = $matchedTable?->id;
            }
        }

        $resolvedEventIds = $guest->event_ids;
        if (array_key_exists('event_ids', $validated)) {
            $resolvedEventIds = is_array($validated['event_ids'])
                ? $guest->wedding->events()->whereIn('id', $validated['event_ids'])->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
                : null;
        }

        $guest->update([
            'name' => strip_tags(trim((string) $validated['name'])),
            'salutation' => isset($validated['salutation']) ? strip_tags(trim((string) $validated['salutation'])) : $guest->salutation,
            'group_name' => isset($validated['group_name']) ? strip_tags(trim((string) $validated['group_name'])) : $guest->group_name,
            'table_name' => $tableName,
            'table_id' => $tableId,
            'phone' => array_key_exists('phone', $validated) ? ($validated['phone'] !== null ? strip_tags(trim((string) $validated['phone'])) : null) : $guest->phone,
            'expected_attendees' => (int) $validated['expected_attendees'],
            'max_attendees' => array_key_exists('max_attendees', $validated) ? ($validated['max_attendees'] ? (int) $validated['max_attendees'] : null) : $guest->max_attendees,
            'event_ids' => $resolvedEventIds,
        ]);

        // Cảnh báo quá tải bàn tiệc nếu việc thay đổi số người làm vượt sức chứa của bàn (Flow 09)
        $tableOverloadedWarning = null;
        if ($guest->table_id) {
            $table = $guest->wedding->tables()->find($guest->table_id);
            if ($table && $table->isOverCapacity()) {
                $over = $table->assigned_attendees_count - $table->capacity;
                $tableOverloadedWarning = "Lưu ý: Bàn \"{$table->name}\" đang bị quá tải ghế ({$table->assigned_attendees_count}/{$table->capacity} người, thừa {$over} chỗ). Vui lòng điều chỉnh lại sơ đồ bàn tiệc.";
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật thông tin khách mời thành công.',
                'warning' => $tableOverloadedWarning,
                'is_table_overloaded' => ! empty($tableOverloadedWarning),
                'guest' => $guest,
            ]);
        }

        $redirect = back()->with('success', 'Đã cập nhật thông tin khách mời thành công.');
        if ($tableOverloadedWarning) {
            $redirect->with('warning', $tableOverloadedWarning);
        }

        return $redirect;
    }

    /**
     * Thu hồi link thiệp cũ và tái tạo mã định danh token mới cho khách mời.
     */
    public function regenerateToken(Request $request, int $guestId): JsonResponse|RedirectResponse
    {
        $guest = Guest::with('wedding.invitations')->findOrFail($guestId);
        $this->authorizeGuestManagement($guest->wedding);

        $oldToken = $guest->guest_token;
        $newToken = $guest->revokeAndRegenerateToken();

        AuditLogger::log(
            'GUEST_TOKEN_REVOKED',
            'Guest',
            $guest->id,
            "Chủ tiệc đã thu hồi token cũ [{$oldToken}] và cấp mã mới [{$newToken}] cho khách mời {$guest->name}"
        );

        $invitation = $guest->wedding->invitations()->first();
        $newUrl = $invitation ? route('invitation.show', ['slug' => $invitation->slug, 'token' => $newToken]) : null;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã thu hồi liên kết cũ và tái tạo mã thiệp mới cho khách \"{$guest->name}\" thành công!",
                'guest_id' => $guest->id,
                'new_token' => $newToken,
                'invitation_url' => $newUrl,
            ]);
        }

        return back()->with('success', "Đã thu hồi mã cũ và cấp liên kết thiệp mới cho khách \"{$guest->name}\" thành công!");
    }

    /**
     * Cặp đôi cập nhật trạng thái xác nhận tham dự (RSVP) thủ công hộ khách mời.
     */
    public function manualRsvp(Request $request, int $guestId, RsvpLedgerService $rsvpLedger): JsonResponse|RedirectResponse
    {
        $guest = Guest::with(['wedding', 'response'])->findOrFail($guestId);
        $this->authorizeGuestManagement($guest->wedding);

        $validated = $request->validate([
            'attending_status' => ['required', 'in:attending,declined,pending'],
            'adults_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'dietary_requirements' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $rsvpLedger->recordManualByHost(
            guest: $guest,
            validated: $validated,
            ipAddress: $request->ip(),
            hasExplicitCounts: $request->has('adults_count') || $request->has('children_count'),
        );

        if (! $result['ok']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? [],
                ], $result['status_code']);
            }

            return back()->withErrors($result['errors'] ?? ['adults_count' => $result['message']])->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'warning' => $result['warning'],
                'is_table_overloaded' => $result['is_table_overloaded'],
            ]);
        }

        $redirect = back()->with('success', $result['message']);
        if (! empty($result['warning'])) {
            $redirect->with('warning', $result['warning']);
        }

        return $redirect;
    }

    /**
     * Bật/tắt trạng thái đã gửi thiệp cho khách mời.
     */
    public function toggleSent(Request $request, int $guestId): JsonResponse
    {
        $guest = Guest::with('wedding')->findOrFail($guestId);
        $this->authorizeGuestManagement($guest->wedding);

        $newSent = ! $guest->is_sent;
        $guest->update([
            'is_sent' => $newSent,
            'sent_at' => $newSent ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'is_sent' => $guest->is_sent,
            'sent_at' => $guest->sent_at?->format('H:i d/m/Y'),
            'message' => $guest->is_sent ? 'Đã đánh dấu đã gửi thiệp' : 'Đã hủy đánh dấu đã gửi thiệp',
        ]);
    }

    /**
     * Bàn Soát Lỗi Trước Khi Gửi Thiệp & Trợ Lý Xưng Hô (Pre-Send Validation Desk - TASK-VAL-10).
     */
    public function preSendValidation(int $invitationId, PreSendValidationService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeGuestManagement($invitation->wedding);

        $result = $service->validate($invitation->wedding, $invitation);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * API Kế Hoạch Nhắc Nhở RSVP Tế Nhị Trước Ngày Cưới (TASK-VAL-16).
     */
    public function reminderPlanner(int $invitationId, GentleReminderPlannerService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeGuestManagement($invitation->wedding);

        $result = $service->plan($invitation->wedding, $invitation);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * API Trợ Lý Soạn Lời Cảm Ơn Sau Cưới Cá Nhân Hóa (TASK-VAL-17).
     */
    public function thankYouBuilder(int $invitationId, PersonalThankYouService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeGuestManagement($invitation->wedding);

        $result = $service->buildThankYouPlan($invitation->wedding, $invitation);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Delete guest.
     */
    public function destroy(int $guestId): RedirectResponse
    {
        $guest = Guest::with('wedding')->findOrFail($guestId);
        $this->authorizeGuestManagement($guest->wedding);
        $guest->delete();

        return back()->with('success', 'Đã xóa khách mời khỏi danh sách.');
    }

    private function authorizeGuestManagement(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        abort(404);
    }

    private function ensureInvitationInteractable(Invitation $invitation): void
    {
        app(GuestInteractionService::class)->assertInteractable($invitation);
    }

    /**
     * Submit RSVP from public invitation.
     */
    public function submitRsvp(SubmitRsvpRequest $request, string $slug, RsvpLedgerService $rsvpLedger): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $query = $request->query();
        $hasTokenParam = array_key_exists('token', $query);

        $result = $rsvpLedger->submitPublic(
            invitation: $invitation,
            payload: $request->validated(),
            guestToken: $hasTokenParam ? $query['token'] : null,
            ipAddress: $request->ip(),
            tokenParameterPresent: $hasTokenParam,
        );

        if (! $result['ok']) {
            if ($request->wantsJson()) {
                return response()->json(array_merge([
                    'success' => false,
                    'message' => $result['message'],
                ], $result['extra'] ?? []), $result['status_code']);
            }

            return back()->with('rsvp_error', $result['redirect_message'] ?? $result['message']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_updated' => $result['is_updated'],
                'attending_status' => $result['attending_status'],
                'message' => $result['json_message'],
            ]);
        }

        return back()->with('rsvp_success', $result['flash_message']);
    }

    /**
     * Demo RSVP submission is disabled on template preview pages.
     */
    public function submitDemoRsvp(Request $request, string $slug): JsonResponse
    {
        return response()->json([
            'success' => false,
            'is_preview_disabled' => true,
            'message' => 'Tính năng xác nhận tham dự chỉ hoạt động trên thiệp cưới đã được tạo. Hãy bấm "Tạo thiệp" để sử dụng đầy đủ!',
        ], 403);
    }

    /**
     * Submit a wish to the wedding guestbook.
     * Lời chúc mới được lưu riêng, không dùng IP để xác định người gửi.
     */
    public function submitWish(SubmitWishRequest $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $this->ensureInvitationInteractable($invitation);

        // Chủ nhân thiệp không gửi lời chúc cho chính mình mà chỉ trả lời lời chúc của khách
        if ($invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi lời chúc. Bạn có thể trả lời các lời chúc của khách mời.',
            ], 403);
        }

        $result = app(GuestInteractionService::class)->submitWish(
            $invitation,
            [
                'name' => $request->validated('name'),
                'message' => $request->validated('message'),
                'is_private' => (bool) $request->boolean('is_private'),
                'guest_token' => $request->input('guest_token'),
                'signature_data' => $request->input('signature_data'),
                'photo_data' => $request->input('photo_data'),
            ],
            $request->file('photo'),
            $request->ip()
        );

        $wish = $result['wish'];

        return response()->json([
            'success' => true,
            'is_update' => false,
            'message' => $result['message'],
            'wish' => [
                'id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'reply' => $wish->reply,
                'is_private' => (bool) $wish->is_private,
                'is_approved' => (bool) $wish->is_approved,
                'signature_url' => $wish->signature_url,
                'photo_url' => $wish->photo_url,
                'created_at_human' => $wish->created_at->diffForHumans(),
                'created_at_date' => $wish->created_at->format('d/m/Y H:i'),
            ],
        ], 201);
    }

    /**
     * Reply to a wish (Owner only).
     */
    public function replyWish(Request $request, string $slug, int $wishId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $this->ensureInvitationInteractable($invitation);

        if (! $invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền phản hồi lời chúc của thiệp cưới này.',
            ], 403);
        }

        $request->validate([
            'reply' => ['required', 'string', 'max:1000', new NoProfanity],
        ], [
            'reply.required' => 'Vui lòng nhập nội dung phản hồi.',
            'reply.max' => 'Nội dung phản hồi không được vượt quá 1000 ký tự.',
        ]);

        $wish = app(GuestInteractionService::class)->replyWish(
            $invitation,
            $wishId,
            (string) $request->input('reply')
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi phản hồi lời chúc thành công!',
            'reply' => $wish->reply,
            'replied_at_human' => 'Vừa xong',
        ]);
    }

    /**
     * Get RSVP responses list and statistics (Owner only).
     */
    public function getRsvpResponses(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        if ($invitation->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
            ], 403);
        }

        if (! $invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem danh sách phản hồi của thiệp cưới này.',
            ], 403);
        }

        $wedding = $invitation->wedding;
        $responses = GuestResponse::where('wedding_id', $wedding->id)
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'guest_name' => $r->guest_name,
                'phone' => $r->phone,
                'attending_status' => $r->attending_status,
                'adults_count' => $r->adults_count,
                'children_count' => $r->children_count,
                'wishes' => $r->wishes,
                'created_at_human' => $r->created_at?->diffForHumans() ?? 'Vừa xong',
                'created_at_date' => $r->created_at?->format('d/m/Y H:i') ?? '',
            ]);

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $responses->count(),
                'attending' => $responses->where('attending_status', 'attending')->count(),
                'attending_guests' => (int) ($responses->where('attending_status', 'attending')->sum('adults_count')
                    + $responses->where('attending_status', 'attending')->sum('children_count')),
                'declined' => $responses->where('attending_status', 'declined')->count(),
            ],
            'responses' => $responses,
        ]);
    }

    /**
     * Get wishes list for an invitation.
     */
    public function getWishes(string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $this->ensureInvitationInteractable($invitation);
        $wishes = $invitation->wedding->wishes()
            ->visibleOnInvitation()
            ->orderByDesc('is_pinned')
            ->latest()
            ->take(50)
            ->get()
            ->map(fn ($wish) => [
                'id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'reply' => $wish->reply,
                'is_pinned' => (bool) $wish->is_pinned,
                'signature_url' => $wish->signature_url,
                'created_at_human' => $wish->created_at->diffForHumans(),
                'created_at_date' => $wish->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'success' => true,
            'wishes' => $wishes,
        ]);
    }

    /**
     * Demo wish submission is disabled on template preview pages.
     */
    public function submitDemoWish(Request $request, string $slug): JsonResponse
    {
        return response()->json([
            'success' => false,
            'is_preview_disabled' => true,
            'message' => 'Tính năng gửi lời chúc chỉ hoạt động trên thiệp cưới đã được tạo. Hãy bấm "Tạo thiệp" để sử dụng đầy đủ!',
        ], 403);
    }
}
