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
use App\Models\WeddingWish;
use App\Rules\NoProfanity;
use App\Services\ContentModerationService;
use App\Services\CsvSanitizerService;
use App\Services\GentleReminderPlannerService;
use App\Services\InvitationDeliveryService;
use App\Services\OutboundWebhookService;
use App\Services\PersonalThankYouService;
use App\Services\PreSendValidationService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        // Thống kê tổng quan (toàn bộ đám cưới)
        $allGuests = $wedding->guests()->with('response')->get();
        $totalInvited = (int) $allGuests->sum('expected_attendees');
        $totalGuestsCount = $allGuests->count();
        $totalResponses = $wedding->guestResponses()->count();
        $attendingCount = (int) ($wedding->guestResponses()->where('attending_status', 'attending')->sum('adults_count')
            + $wedding->guestResponses()->where('attending_status', 'attending')->sum('children_count'));
        $declinedCount = (int) $wedding->guestResponses()->where('attending_status', 'declined')->count();
        $responseRate = $totalGuestsCount > 0 ? (int) round(($totalResponses / $totalGuestsCount) * 100) : 0;
        $checkedInCount = (int) $allGuests->whereNotNull('checked_in_at')->sum(function (Guest $g) {
            return $g->checked_in_attendees ?: $g->expected_attendees;
        });
        $openedCount = $allGuests->filter(fn (Guest $g) => $g->isOpened())->count();

        // Xử lý bộ lọc danh sách hiển thị
        $guestsQuery = $wedding->guests()->with('response');

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

        if ($tableFilter = $request->query('table')) {
            $guestsQuery->where('table_name', $tableFilter);
        }

        if ($statusFilter = $request->query('status')) {
            if ($statusFilter === 'attending') {
                $guestsQuery->whereHas('response', fn ($q) => $q->where('attending_status', 'attending'));
            } elseif ($statusFilter === 'declined') {
                $guestsQuery->whereHas('response', fn ($q) => $q->where('attending_status', 'declined'));
            } elseif ($statusFilter === 'pending') {
                $guestsQuery->whereDoesntHave('response');
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

        $guests = $guestsQuery->latest()->get();

        $deliveryStats = app(InvitationDeliveryService::class)->getDeliveryStats($wedding);

        return view('client.pages.guests', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'guests' => $guests,
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

        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => $request->validated('name'),
            'salutation' => $request->validated('salutation') ?? 'Thân mời',
            'group_name' => $request->validated('group_name') ?? 'Bạn Bè',
            'table_name' => $tableName,
            'table_id' => $tableId,
            'phone' => $request->validated('phone'),
            'expected_attendees' => $request->validated('expected_attendees') ?? 1,
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
                $name = trim($parts[0] ?? '');
                if ($name === '') {
                    continue;
                }

                // Lọc bỏ các dòng chứa từ ngữ thô tục / cấm kỵ (Content Moderation)
                if (ContentModerationService::containsProfanity($name)) {
                    $skippedCount++;

                    continue;
                }

                $salutation = isset($parts[1]) && trim($parts[1]) !== '' ? trim($parts[1]) : $defaultSalutation;
                $group = isset($parts[2]) && trim($parts[2]) !== '' ? trim($parts[2]) : $defaultGroup;
                $attendees = isset($parts[3]) && is_numeric(trim($parts[3])) ? max(1, (int) trim($parts[3])) : 2;
                $phone = isset($parts[4]) && trim($parts[4]) !== '' ? trim($parts[4]) : null;
                $table = isset($parts[5]) && trim($parts[5]) !== '' ? trim($parts[5]) : null;
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

        $guestsQuery = $wedding->guests()->with('response');

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

        if ($tableFilter = $request->query('table')) {
            $guestsQuery->where('table_name', $tableFilter);
        }

        if ($statusFilter = $request->query('status')) {
            if ($statusFilter === 'attending') {
                $guestsQuery->whereHas('response', fn ($q) => $q->where('attending_status', 'attending'));
            } elseif ($statusFilter === 'declined') {
                $guestsQuery->whereHas('response', fn ($q) => $q->where('attending_status', 'declined'));
            } elseif ($statusFilter === 'pending') {
                $guestsQuery->whereDoesntHave('response');
            } elseif ($statusFilter === 'checked_in') {
                $guestsQuery->whereNotNull('checked_in_at');
            } elseif ($statusFilter === 'not_checked_in') {
                $guestsQuery->whereNull('checked_in_at');
            }
        }

        $guests = $guestsQuery->orderBy('group_name')->orderBy('name')->get();

        $filename = 'danh-sach-khach-moi-'.Str::slug($wedding->groom_name.'-'.$wedding->bride_name).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($guests, $invitation) {
            $handle = fopen('php://output', 'w');
            // Ghi UTF-8 BOM để Excel tiếng Việt không bị lỗi font
            fwrite($handle, "\xEF\xBB\xBF");

            // Header CSV
            fputcsv($handle, [
                'STT',
                'Tên Khách Mời',
                'Cách Xưng Hô',
                'Nhóm Khách',
                'Bàn Tiệc',
                'Số Điện Thoại',
                'Số Người Dự Kiến',
                'Trạng Thái RSVP',
                'Người Lớn (RSVP)',
                'Trẻ Em (RSVP)',
                'Điểm Danh Ngày Cưới',
                'Thời Điểm Điểm Danh',
                'Yêu Cầu Ăn Uống',
                'Lời Chúc / Ghi Chú',
                'Link Thiệp Cá Nhân Hóa',
            ]);

            $index = 1;
            foreach ($guests as $g) {
                $response = $g->response;
                $rsvpStatus = 'Chưa phản hồi';
                $adults = '';
                $children = '';
                $dietary = '';
                $wishes = '';

                if ($response) {
                    $rsvpStatus = $response->attending_status === 'attending' ? 'Đã xác nhận tham dự' : 'Từ chối / Không tham gia';
                    $adults = $response->adults_count;
                    $children = $response->children_count;
                    $dietary = $response->dietary_requirements ?? '';
                    $wishes = $response->wishes ?? '';
                }

                $checkInStatus = $g->checked_in_at ? 'Đã có mặt' : 'Chưa điểm danh';
                $checkInTime = $g->checked_in_at ? $g->checked_in_at->format('H:i d/m/Y') : '';
                $personalUrl = route('invitation.show', ['slug' => $invitation->slug, 'token' => $g->guest_token]);

                fputcsv($handle, CsvSanitizerService::row([
                    $index++,
                    $g->name,
                    $g->salutation,
                    $g->group_name,
                    $g->table_name ?? 'Chưa xếp bàn',
                    $g->phone ?? '',
                    $g->expected_attendees,
                    $rsvpStatus,
                    $adults,
                    $children,
                    $checkInStatus,
                    $checkInTime,
                    $dietary,
                    $wishes,
                    $personalUrl,
                ]));
            }

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
        ], [
            'name.required' => 'Vui lòng nhập tên khách mời.',
            'expected_attendees.required' => 'Vui lòng nhập số người tham dự dự kiến.',
            'expected_attendees.min' => 'Số người tham dự tối thiểu là 1.',
        ]);

        $tableId = $guest->table_id;
        $tableName = $guest->table_name;
        if (array_key_exists('table_name', $validated)) {
            $rawTable = trim((string) $validated['table_name']);
            if ($rawTable === '') {
                $tableName = null;
                $tableId = null;
            } else {
                $tableName = $rawTable;
                $matchedTable = $guest->wedding->tables()->where('name', $rawTable)->first();
                $tableId = $matchedTable?->id;
            }
        }

        $guest->update([
            'name' => $validated['name'],
            'salutation' => $validated['salutation'] ?? $guest->salutation,
            'group_name' => $validated['group_name'] ?? $guest->group_name,
            'table_name' => $tableName,
            'table_id' => $tableId,
            'phone' => array_key_exists('phone', $validated) ? ($validated['phone'] ?? null) : $guest->phone,
            'expected_attendees' => (int) $validated['expected_attendees'],
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
     * Cặp đôi cập nhật trạng thái xác nhận tham dự (RSVP) thủ công hộ khách mời.
     */
    public function manualRsvp(Request $request, int $guestId): JsonResponse|RedirectResponse
    {
        $guest = Guest::with(['wedding', 'response'])->findOrFail($guestId);
        $this->authorizeGuestManagement($guest->wedding);

        $validated = $request->validate([
            'attending_status' => ['required', 'in:attending,declined,pending'],
            'adults_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'dietary_requirements' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $status = $validated['attending_status'];

        if ($status === 'pending') {
            if ($guest->response) {
                $guest->response->delete();
            }
        } else {
            $adults = $status === 'attending' ? (int) ($validated['adults_count'] ?? $guest->expected_attendees) : 0;
            $children = $status === 'attending' ? (int) ($validated['children_count'] ?? 0) : 0;

            GuestResponse::updateOrCreate(
                ['guest_id' => $guest->id],
                [
                    'wedding_id' => $guest->wedding_id,
                    'guest_name' => $guest->name,
                    'attending_status' => $status,
                    'adults_count' => $adults,
                    'children_count' => $children,
                    'dietary_requirements' => $validated['dietary_requirements'] ?? null,
                    'wishes' => $validated['notes'] ?? null,
                    'needs_reconfirmation' => false,
                    'reconfirmed_at' => now(),
                    'ip_address' => $request->ip(),
                ]
            );
        }

        // Cảnh báo quá tải bàn tiệc nếu việc thay đổi trạng thái RSVP làm vượt sức chứa của bàn (Flow 09)
        $tableOverloadedWarning = null;
        if ($guest->table_id) {
            $table = $guest->wedding->tables()->find($guest->table_id);
            if ($table && $table->isOverCapacity()) {
                $over = $table->assigned_attendees_count - $table->capacity;
                $tableOverloadedWarning = "Lưu ý: Bàn \"{$table->name}\" đang bị quá tải ghế sau khi cập nhật RSVP ({$table->assigned_attendees_count}/{$table->capacity} người, thừa {$over} chỗ). Vui lòng điều chỉnh lại sơ đồ bàn tiệc.";
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật trạng thái RSVP cho khách mời thành công.',
                'warning' => $tableOverloadedWarning,
                'is_table_overloaded' => ! empty($tableOverloadedWarning),
            ]);
        }

        $redirect = back()->with('success', 'Đã cập nhật trạng thái RSVP cho khách mời thành công.');
        if ($tableOverloadedWarning) {
            $redirect->with('warning', $tableOverloadedWarning);
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
        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode($invitation->custom_config, true) ?? []);
        if (! empty($config['is_locked'])) {
            abort(403, 'Thiệp cưới tạm thời bị khóa.');
        }

        if (! $invitation->is_published) {
            abort(404, 'Thiệp cưới chưa được xuất bản.');
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            abort(410, 'Thiệp cưới đã hết hạn lưu trữ.');
        }
    }

    /**
     * Submit RSVP from public invitation.
     */
    public function submitRsvp(SubmitRsvpRequest $request, string $slug): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $this->ensureInvitationInteractable($invitation);
        $wedding = $invitation->wedding;

        // 0. Chủ nhân thiệp không được phép gửi xác nhận tham dự cho chính mình
        if ($invitation->isOwner()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi xác nhận tham dự cho chính mình.',
                ], 403);
            }

            return back()->with('rsvp_error', 'Bạn là chủ nhân của thiệp cưới này.');
        }

        // 0.1. Kiểm tra chính sách và hạn chót RSVP (RSVP-03 Policy)
        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);
        $allowRsvp = $config['allow_rsvp'] ?? true;
        if (! $allowRsvp) {
            $msg = 'Tính năng xác nhận tham dự (RSVP) hiện đang tạm đóng theo cài đặt của cô dâu chú rể.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('rsvp_error', $msg);
        }

        $rsvpDeadlineStr = $config['rsvp_deadline'] ?? null;
        $lockAfterDeadline = ! empty($config['rsvp_lock_after_deadline']);
        $isLateRsvp = false;

        if ($rsvpDeadlineStr) {
            $deadline = Carbon::parse($rsvpDeadlineStr)->endOfDay();
            if (Carbon::now()->isAfter($deadline)) {
                if ($lockAfterDeadline) {
                    $closedNotice = ! empty($config['rsvp_closed_notice'])
                        ? $config['rsvp_closed_notice']
                        : "Hạn chót xác nhận tham dự đã khép lại vào ngày {$deadline->format('d/m/Y')} để gia đình chốt số lượng bàn tiệc. Xin vui lòng liên hệ trực tiếp cô dâu chú rể nếu bạn muốn điều chỉnh thông tin!";

                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'is_closed' => true,
                            'message' => $closedNotice,
                        ], 422);
                    }

                    return back()->with('rsvp_error', $closedNotice);
                }

                $isLateRsvp = true;
            }
        }

        $query = $request->query();
        $guestToken = null;
        if (array_key_exists('token', $query)) {
            $guestToken = $query['token'];
            if (! is_string($guestToken) || $guestToken === '' || strlen($guestToken) > 64) {
                abort(404);
            }
        }
        $ip = $request->ip();

        $created = DB::transaction(function () use ($wedding, $guestToken, $ip, $request, $isLateRsvp): bool {
            $lockedWedding = Wedding::whereKey($wedding->id)->lockForUpdate()->firstOrFail();
            $guest = $guestToken === null
                ? null
                : $lockedWedding->guests()->where('guest_token', $guestToken)->firstOrFail();

            $existingResponse = GuestResponse::where('wedding_id', $lockedWedding->id);
            if ($guest) {
                $existingResponse->where('guest_id', $guest->id);
            } else {
                $phone = $request->validated('phone');
                if (! empty($phone)) {
                    $existingResponse->whereNull('guest_id')->where('phone', $phone);
                } else {
                    $existingResponse->whereNull('guest_id')
                        ->where('guest_name', $request->validated('guest_name'))
                        ->where('ip_address', $ip);
                }
            }

            if ($existingResponse->exists()) {
                $existing = $existingResponse->first();
                if ($existing && $existing->needs_reconfirmation) {
                    $existing->update([
                        'attending_status' => $request->validated('attending_status'),
                        'adults_count' => $request->validated('adults_count') ?? 1,
                        'children_count' => $request->validated('children_count') ?? 0,
                        'dietary_requirements' => $request->validated('dietary_requirements'),
                        'wishes' => $request->validated('wishes'),
                        'needs_reconfirmation' => false,
                        'is_late' => $isLateRsvp,
                        'reconfirmed_at' => now(),
                        'ip_address' => $ip,
                    ]);

                    return true;
                }

                return false;
            }

            GuestResponse::create([
                'wedding_id' => $lockedWedding->id,
                'guest_id' => $guest?->id,
                'guest_name' => $request->validated('guest_name'),
                'phone' => $request->validated('phone'),
                'attending_status' => $request->validated('attending_status'),
                'adults_count' => $request->validated('adults_count') ?? 1,
                'children_count' => $request->validated('children_count') ?? 0,
                'dietary_requirements' => $request->validated('dietary_requirements'),
                'wishes' => $request->validated('wishes'),
                'is_late' => $isLateRsvp,
                'is_wishes_public' => true,
                'ip_address' => $ip,
            ]);

            if (! empty($request->validated('wishes'))) {
                WeddingWish::create([
                    'wedding_id' => $lockedWedding->id,
                    'ip_address' => $ip,
                    'name' => $request->validated('guest_name'),
                    'message' => $request->validated('wishes'),
                    'is_approved' => true,
                ]);
            }

            return true;
        });

        if (! $created) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi và không thể thay đổi.',
                ], 422);
            }

            return back()->with('rsvp_error', 'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi.');
        }

        // Bắn Outbound Webhook (nếu có cấu hình)
        try {
            app(OutboundWebhookService::class)->dispatch($wedding, 'rsvp.submitted', [
                'guest_name' => $request->validated('guest_name'),
                'phone' => $request->validated('phone'),
                'attending_status' => $request->validated('attending_status'),
                'adults_count' => (int) ($request->validated('adults_count') ?? 1),
                'children_count' => (int) ($request->validated('children_count') ?? 0),
                'dietary_requirements' => $request->validated('dietary_requirements'),
                'wishes' => $request->validated('wishes'),
            ]);
        } catch (\Throwable $e) {
            // Safe fallback
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'attending_status' => $request->validated('attending_status'),
                'message' => $request->validated('attending_status') === 'attending'
                    ? 'Cảm ơn quý khách đã xác nhận tham dự! Chúng tôi rất hân hạnh được đón tiếp.'
                    : 'Cảm ơn quý khách đã gửi phản hồi! Chúng tôi rất tiếc vì quý khách chưa thể sắp xếp tham dự.',
            ]);
        }

        return back()->with('rsvp_success', 'Cảm ơn quý khách! Phản hồi đã được ghi nhận.');
    }

    /**
     * Demo RSVP submission for template preview mode.
     */
    public function submitDemoRsvp(SubmitRsvpRequest $request, string $slug): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $request->validated('attending_status') === 'attending'
                ? 'Cảm ơn bạn đã xác nhận tham dự! (Thử nghiệm)'
                : 'Cảm ơn bạn đã gửi phản hồi! (Thử nghiệm)',
        ], 200);
    }

    /**
     * Submit a wish to the wedding guestbook.
     * Lời chúc mới được lưu riêng, không dùng IP để xác định người gửi.
     */
    public function submitWish(SubmitWishRequest $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $this->ensureInvitationInteractable($invitation);
        $wedding = $invitation->wedding;

        // Chủ nhân thiệp không gửi lời chúc cho chính mình mà chỉ trả lời lời chúc của khách
        if ($invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi lời chúc. Bạn có thể trả lời các lời chúc của khách mời.',
            ], 403);
        }

        $ip = $request->ip();

        $wish = WeddingWish::create([
            'wedding_id' => $wedding->id,
            'name' => $request->validated('name'),
            'message' => $request->validated('message'),
            'is_approved' => true,
            'ip_address' => $ip,
        ]);

        // Bắn Outbound Webhook thông báo lời chúc mới
        try {
            app(OutboundWebhookService::class)->dispatch($wedding, 'wish.created', [
                'wish_id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
            ]);
        } catch (\Throwable $e) {
            // Safe fallback
        }

        return response()->json([
            'success' => true,
            'is_update' => false,
            'message' => 'Cảm ơn bạn đã gửi lời chúc tốt đẹp tới cô dâu & chú rể!',
            'wish' => [
                'id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'reply' => $wish->reply,
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

        $wedding = $invitation->wedding;
        $wish = WeddingWish::where('wedding_id', $wedding->id)->findOrFail($wishId);

        $wish->update([
            'reply' => $request->input('reply'),
            'replied_at' => now(),
        ]);

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
            ->where('is_approved', true)
            ->take(50)
            ->get()
            ->map(fn ($wish) => [
                'id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'reply' => $wish->reply,
                'created_at_human' => $wish->created_at->diffForHumans(),
                'created_at_date' => $wish->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'success' => true,
            'wishes' => $wishes,
        ]);
    }

    /**
     * Demo wish submission for template preview mode.
     */
    public function submitDemoWish(SubmitWishRequest $request, string $slug): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã gửi lời chúc thử nghiệm!',
            'wish' => [
                'id' => rand(1000, 9999),
                'name' => $request->validated('name'),
                'message' => $request->validated('message'),
                'reply' => null,
                'created_at_human' => 'Vừa xong',
                'created_at_date' => now()->format('d/m/Y H:i'),
            ],
        ], 201);
    }
}
