<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Wedding;
use App\Models\WeddingTable;
use App\Services\AuditLogger;
use App\Services\CsvSanitizerService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestController extends Controller
{
    /**
     * Display centralized Guest CRM & Wedding Day Check-in Operations (Wave 05).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $weddingId = $request->input('wedding_id');

        $query = Guest::query()->with([
            'wedding:id,user_id,groom_name,bride_name,wedding_date',
            'wedding.invitations:id,wedding_id,slug,is_published',
            'table:id,name,capacity,zone',
            'response',
        ]);

        // 1. Text Search: Guest name, phone, group, table, couple names
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%")
                    ->orWhereHas('wedding', function ($wq) use ($search) {
                        $wq->where('groom_name', 'like', "%{$search}%")
                            ->orWhere('bride_name', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Status Filter
        if ($status === 'checked_in') {
            $query->whereNotNull('checked_in_at');
        } elseif ($status === 'not_checked_in') {
            $query->whereNull('checked_in_at');
        } elseif ($status === 'assigned') {
            $query->where(function ($q) {
                $q->whereNotNull('table_id')->orWhereNotNull('table_name');
            });
        } elseif ($status === 'unassigned') {
            $query->whereNull('table_id')->whereNull('table_name');
        }

        // 3. Wedding Filter
        if (! empty($weddingId)) {
            $query->where('wedding_id', (int) $weddingId);
        }

        $guests = $query->latest('id')->paginate(15)->withQueryString();

        // Platform KPIs
        $totalGuests = Guest::count();
        $checkedInCount = Guest::whereNotNull('checked_in_at')->count();
        $checkInRate = $totalGuests > 0 ? (int) round(($checkedInCount / $totalGuests) * 100) : 0;
        $assignedCount = Guest::where(function ($q) {
            $q->whereNotNull('table_id')->orWhereNotNull('table_name');
        })->count();
        $unassignedCount = max(0, $totalGuests - $assignedCount);
        $totalRsvps = GuestResponse::count();
        $attendingRsvps = GuestResponse::where('attending_status', 'attending')->count();

        $allWeddings = Wedding::query()
            ->select('id', 'groom_name', 'bride_name', 'wedding_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('admin.pages.guests.index', [
            'guests' => $guests,
            'currentSearch' => $search,
            'currentStatus' => $status,
            'currentWeddingId' => $weddingId,
            'totalGuests' => $totalGuests,
            'checkedInCount' => $checkedInCount,
            'checkInRate' => $checkInRate,
            'assignedCount' => $assignedCount,
            'unassignedCount' => $unassignedCount,
            'totalRsvps' => $totalRsvps,
            'attendingRsvps' => $attendingRsvps,
            'allWeddings' => $allWeddings,
        ]);
    }

    /**
     * Toggle check-in state for a guest on wedding day with strict idempotent semantics & locking (NXT-03).
     */
    public function toggleCheckIn(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $action = $request->input('action'); // 'check_in', 'undo_check_in', or null (safe idempotent toggle)
        $idempotencyKey = $request->header('X-Idempotency-Key') ?? $request->input('idempotency_key');
        $note = $request->input('note');

        $result = DB::transaction(function () use ($id, $action, $note) {
            $guest = Guest::where('id', $id)->lockForUpdate()->firstOrFail();
            $alreadyCheckedIn = $guest->isCheckedIn();

            if ($action === 'check_in') {
                if ($alreadyCheckedIn) {
                    return [
                        'guest' => $guest,
                        'is_checked_in' => true,
                        'status_changed' => false,
                        'message' => "Quan khách \"{$guest->name}\" đã được điểm danh đón tiếp trước đó.",
                        'action_logged' => null,
                    ];
                }

                $attendees = $guest->actualAttendeesCount();
                $guest->update([
                    'checked_in_at' => now(),
                    'checked_in_attendees' => $attendees,
                    'check_in_note' => $note ?: $guest->check_in_note,
                ]);

                return [
                    'guest' => $guest,
                    'is_checked_in' => true,
                    'status_changed' => true,
                    'message' => "Đã điểm danh đón tiếp thành công quan khách \"{$guest->name}\" ({$attendees} người).",
                    'action_logged' => 'check_in',
                ];
            }

            if ($action === 'undo_check_in') {
                if (! $alreadyCheckedIn) {
                    return [
                        'guest' => $guest,
                        'is_checked_in' => false,
                        'status_changed' => false,
                        'message' => "Quan khách \"{$guest->name}\" chưa được điểm danh.",
                        'action_logged' => null,
                    ];
                }

                $guest->update([
                    'checked_in_at' => null,
                    'checked_in_attendees' => 0,
                ]);

                return [
                    'guest' => $guest,
                    'is_checked_in' => false,
                    'status_changed' => true,
                    'message' => "Đã hủy điểm danh cho quan khách \"{$guest->name}\".",
                    'action_logged' => 'cancel_check_in',
                ];
            }

            // Fallback toggle nếu không chỉ định rõ action (vẫn bảo vệ lockForUpdate nguyên tử)
            if ($alreadyCheckedIn) {
                $guest->update([
                    'checked_in_at' => null,
                    'checked_in_attendees' => 0,
                ]);

                return [
                    'guest' => $guest,
                    'is_checked_in' => false,
                    'status_changed' => true,
                    'message' => "Đã hủy điểm danh cho quan khách \"{$guest->name}\".",
                    'action_logged' => 'cancel_check_in',
                ];
            }

            $attendees = $guest->actualAttendeesCount();
            $guest->update([
                'checked_in_at' => now(),
                'checked_in_attendees' => $attendees,
                'check_in_note' => $note ?: $guest->check_in_note,
            ]);

            return [
                'guest' => $guest,
                'is_checked_in' => true,
                'status_changed' => true,
                'message' => "Đã điểm danh đón tiếp thành công quan khách \"{$guest->name}\" ({$attendees} người).",
                'action_logged' => 'check_in',
            ];
        });

        $guest = $result['guest'];

        if ($result['action_logged']) {
            AuditLogger::log(
                $result['action_logged'],
                'Guest',
                $guest->id,
                $result['message'],
                [
                    'wedding_id' => $guest->wedding_id,
                    'guest_name' => $guest->name,
                    'is_checked_in' => $result['is_checked_in'],
                    'idempotency_key' => $idempotencyKey,
                ]
            );
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_checked_in' => $result['is_checked_in'],
                'status_changed' => $result['status_changed'],
                'checked_in_at' => $guest->fresh()->checked_in_at?->format('d/m/Y H:i'),
                'message' => $result['message'],
            ]);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Export guests list as UTF-8 BOM CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $weddingId = $request->input('wedding_id');

        $query = Guest::query()->with([
            'wedding:id,user_id,groom_name,bride_name,wedding_date',
            'table:id,name,zone',
            'response',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%")
                    ->orWhereHas('wedding', function ($wq) use ($search) {
                        $wq->where('groom_name', 'like', "%{$search}%")
                            ->orWhere('bride_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status === 'checked_in') {
            $query->whereNotNull('checked_in_at');
        } elseif ($status === 'not_checked_in') {
            $query->whereNull('checked_in_at');
        } elseif ($status === 'assigned') {
            $query->where(function ($q) {
                $q->whereNotNull('table_id')->orWhereNotNull('table_name');
            });
        } elseif ($status === 'unassigned') {
            $query->whereNull('table_id')->whereNull('table_name');
        }

        if (! empty($weddingId)) {
            $query->where('wedding_id', (int) $weddingId);
        }

        $guests = $query->latest('id')->get();
        $filename = 'danh-sach-quan-khach-mayhen-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($guests) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'STT',
                'Mã Khách',
                'Họ và Tên',
                'Số Điện Thoại',
                'Nhóm Quan Khách',
                'Đám Cưới Cặp Đôi',
                'Bàn Tiệc Phân Bổ',
                'Xác Nhận RSVP',
                'Sĩ Số Tham Dự',
                'Trạng Thái Check-in',
                'Thời Gian Check-in',
            ]);

            $index = 1;
            foreach ($guests as $g) {
                $rsvpLabel = match ($g->response?->attending_status) {
                    'attending' => 'Có tham dự',
                    'declined' => 'Không tham dự',
                    'uncertain' => 'Chưa chắc chắn',
                    default => 'Chưa phản hồi',
                };

                $tableName = $g->table?->name ?? $g->table_name ?? 'Chưa xếp bàn';
                $checkInStatus = $g->isCheckedIn() ? 'Đã đón tiếp' : 'Chưa đến';
                $checkInTime = $g->checked_in_at ? $g->checked_in_at->format('d/m/Y H:i:s') : '—';
                $coupleName = $g->wedding ? "{$g->wedding->groom_name} & {$g->wedding->bride_name}" : 'N/A';

                fputcsv($handle, CsvSanitizerService::row([
                    $index++,
                    $g->id,
                    $g->name,
                    $g->phone ?? 'Chưa có',
                    $g->group_name ?? 'Chung',
                    $coupleName,
                    $tableName,
                    $rsvpLabel,
                    $g->actualAttendeesCount(),
                    $checkInStatus,
                    $checkInTime,
                ]));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Wedding Day Rescue Cockpit (Innovation #8).
     * Bảng điều khiển sự cố ngày cưới: mạng chập chờn, khách walk-in chưa có bàn, cảnh báo quá tải sảnh tiệc.
     */
    public function rescueDesk(Request $request): View
    {
        $selectedWeddingId = $request->input('wedding_id');
        $today = Carbon::today();

        // 1. All weddings available for rescue mode (prioritize today's weddings)
        $todayWeddings = Wedding::query()
            ->whereDate('wedding_date', $today)
            ->withCount(['guests', 'tables'])
            ->get();

        $activeWedding = null;
        if (! empty($selectedWeddingId)) {
            $activeWedding = Wedding::with(['tables.guests.response'])->find((int) $selectedWeddingId);
        }

        if (! $activeWedding && $todayWeddings->isNotEmpty()) {
            $activeWedding = Wedding::with(['tables.guests.response'])->find($todayWeddings->first()->id);
        }

        if (! $activeWedding) {
            $activeWedding = Wedding::with(['tables.guests.response'])->latest('wedding_date')->first();
        }

        $allWeddings = Wedding::select('id', 'groom_name', 'bride_name', 'wedding_date')
            ->orderByDesc('wedding_date')
            ->limit(50)
            ->get();

        $tables = collect();
        $unassignedGuests = collect();
        $checkedInCount = 0;
        $totalCapacity = 0;
        $assignedCount = 0;
        $isOverCapacity = false;

        if ($activeWedding) {
            $tables = $activeWedding->tables;
            $totalCapacity = (int) $tables->sum('capacity');

            // Unassigned guests (no table_id and no table_name)
            $unassignedGuests = Guest::where('wedding_id', $activeWedding->id)
                ->whereNull('table_id')
                ->where(function ($q) {
                    $q->whereNull('table_name')->orWhere('table_name', '');
                })
                ->with('response')
                ->latest('id')
                ->get();

            $assignedCount = (int) Guest::where('wedding_id', $activeWedding->id)
                ->where(function ($q) {
                    $q->whereNotNull('table_id')->orWhere(function ($sub) {
                        $sub->whereNotNull('table_name')->where('table_name', '!=', '');
                    });
                })->count();

            $checkedInCount = Guest::where('wedding_id', $activeWedding->id)
                ->whereNotNull('checked_in_at')
                ->count();

            $isOverCapacity = $totalCapacity > 0 && $assignedCount > $totalCapacity;
        }

        return view('admin.pages.guests.rescue', [
            'activeWedding' => $activeWedding,
            'todayWeddings' => $todayWeddings,
            'allWeddings' => $allWeddings,
            'tables' => $tables,
            'unassignedGuests' => $unassignedGuests,
            'totalCapacity' => $totalCapacity,
            'assignedCount' => $assignedCount,
            'checkedInCount' => $checkedInCount,
            'isOverCapacity' => $isOverCapacity,
        ]);
    }

    /**
     * Quick 1-click assign table for walk-in / extra guest in Rescue Mode with Capacity Enforcement (NXT-03).
     */
    public function quickAssignTable(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'table_id' => 'required|integer|exists:wedding_tables,id',
            'allow_overflow' => 'nullable|boolean',
            'overflow_reason' => 'nullable|string|max:255',
        ]);

        $tableId = (int) $request->input('table_id');
        $allowOverflow = $request->boolean('allow_overflow', false);
        $overflowReason = trim((string) $request->input('overflow_reason', ''));

        $result = DB::transaction(function () use ($id, $tableId, $allowOverflow, $overflowReason) {
            $guest = Guest::where('id', $id)->lockForUpdate()->firstOrFail();
            $table = WeddingTable::where('wedding_id', $guest->wedding_id)
                ->where('id', $tableId)
                ->lockForUpdate()
                ->firstOrFail();

            $guestSeats = $guest->actualAttendeesCount();

            // Tính tổng số chỗ đã xếp hiện tại của bàn (loại trừ chính khách này nếu đã ngồi trước)
            $currentOccupants = Guest::where('table_id', $table->id)
                ->where('id', '!=', $guest->id)
                ->get();
            $currentOccupancy = (int) $currentOccupants->sum(fn ($g) => $g->actualAttendeesCount());

            $newTotal = $currentOccupancy + $guestSeats;

            if ($newTotal > $table->capacity && ! $allowOverflow) {
                return [
                    'success' => false,
                    'error_code' => 'OVER_CAPACITY',
                    'capacity' => $table->capacity,
                    'current_occupancy' => $currentOccupancy,
                    'needed_seats' => $guestSeats,
                    'table_name' => $table->name,
                    'message' => "Bàn tiệc \"{$table->name}\" không đủ chỗ (Sức chứa {$table->capacity}, hiện có {$currentOccupancy}, cần {$guestSeats} chỗ). Vui lòng chọn bàn khác hoặc kích hoạt chế độ Kê thêm ghế (Overflow) kèm lý do.",
                ];
            }

            $guest->update([
                'table_id' => $table->id,
                'table_name' => $table->name,
            ]);

            $isOverCapacity = $newTotal > $table->capacity;
            $msg = "Đã xếp quan khách \"{$guest->name}\" ({$guestSeats} người) vào bàn \"{$table->name}\" thành công.".($isOverCapacity ? " (Kê thêm ghế: {$overflowReason})" : '');

            return [
                'success' => true,
                'guest' => $guest,
                'table' => $table,
                'message' => $msg,
                'is_over_capacity' => $isOverCapacity,
                'overflow_reason' => $overflowReason,
            ];
        });

        if (! $result['success']) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json($result, 422);
            }

            return back()->with('error', $result['message']);
        }

        $guest = $result['guest'];
        $table = $result['table'];

        AuditLogger::log(
            'rescue_assign_table',
            'Guest',
            $guest->id,
            $result['message'],
            [
                'wedding_id' => $guest->wedding_id,
                'guest_id' => $guest->id,
                'table_id' => $table->id,
                'table_name' => $table->name,
                'is_over_capacity' => $result['is_over_capacity'],
                'overflow_reason' => $result['overflow_reason'],
            ]
        );

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'table_id' => $table->id,
                'table_name' => $table->name,
            ]);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Create emergency overflow table for walk-in guests on wedding day with race-condition defense (NXT-03).
     */
    public function createEmergencyTable(Request $request, int $weddingId): RedirectResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:100',
            'capacity' => 'nullable|integer|min:1|max:30',
            'zone' => 'nullable|string|max:100',
        ]);

        $nameInput = trim((string) $request->input('name'));
        $capacity = (int) ($request->input('capacity') ?: 10);
        $zone = trim((string) $request->input('zone')) ?: 'Khu Dự Phòng';

        $table = DB::transaction(function () use ($weddingId, $nameInput, $capacity, $zone) {
            // Khóa wedding để tránh race condition khi nhiều lễ tân tạo bàn đồng thời
            $wedding = Wedding::where('id', $weddingId)->lockForUpdate()->firstOrFail();

            $count = $wedding->tables()->count() + 1;
            $name = $nameInput !== '' ? $nameInput : "Bàn Dự Phòng #{$count}";

            // Kiểm tra trùng tên bàn trong wedding, nếu trùng tự đánh số thêm
            $baseName = $name;
            $suffix = 1;
            while ($wedding->tables()->where('name', $name)->exists()) {
                $name = "{$baseName} ({$suffix})";
                $suffix++;
            }

            $nextOrder = (int) ($wedding->tables()->max('display_order') ?? 0) + 1;

            return $wedding->tables()->create([
                'name' => $name,
                'capacity' => $capacity,
                'zone' => $zone,
                'display_order' => $nextOrder,
                'notes' => 'Tạo khẩn cấp từ Rescue Cockpit ngày cưới',
            ]);
        });

        $msg = "Đã tạo khẩn cấp bàn tiệc \"{$table->name}\" ({$capacity} chỗ) thành công.";

        AuditLogger::log(
            'create_emergency_table',
            'WeddingTable',
            $table->id,
            $msg,
            [
                'wedding_id' => $weddingId,
                'table_name' => $table->name,
                'capacity' => $capacity,
            ]
        );

        return back()->with('success', $msg);
    }

    /**
     * Zero-Surprise Campaign Launch (Innovation #15).
     * Xem trước chiến dịch gửi thông báo thiệp cưới: phân đoạn khách, mô phỏng OpenGraph Card Zalo/SMS.
     */
    public function campaignPreview(Request $request): View
    {
        $selectedWeddingId = $request->input('wedding_id');

        $activeWedding = null;
        if (! empty($selectedWeddingId)) {
            $activeWedding = Wedding::with(['guests.response', 'invitations.template', 'user'])->find((int) $selectedWeddingId);
        }

        if (! $activeWedding) {
            $activeWedding = Wedding::with(['guests.response', 'invitations.template', 'user'])->latest('wedding_date')->first();
        }

        $allWeddings = Wedding::select('id', 'groom_name', 'bride_name', 'wedding_date')
            ->orderByDesc('wedding_date')
            ->limit(50)
            ->get();

        $metrics = [
            'total_guests' => 0,
            'sent_count' => 0,
            'unsent_count' => 0,
            'attending_count' => 0,
            'declined_count' => 0,
            'no_response_count' => 0,
            'send_rate' => 0,
        ];

        if ($activeWedding) {
            $guests = $activeWedding->guests;
            $total = $guests->count();
            $sent = $guests->where('is_sent', true)->count();
            $attending = $guests->filter(fn ($g) => $g->response?->attending_status === 'attending')->count();
            $declined = $guests->filter(fn ($g) => $g->response?->attending_status === 'declined')->count();
            $noResponse = $total - $attending - $declined;

            $metrics = [
                'total_guests' => $total,
                'sent_count' => $sent,
                'unsent_count' => max(0, $total - $sent),
                'attending_count' => $attending,
                'declined_count' => $declined,
                'no_response_count' => max(0, $noResponse),
                'send_rate' => $total > 0 ? (int) round(($sent / $total) * 100) : 0,
            ];
        }

        return view('admin.pages.guests.campaign_preview', [
            'activeWedding' => $activeWedding,
            'allWeddings' => $allWeddings,
            'metrics' => $metrics,
        ]);
    }
}
