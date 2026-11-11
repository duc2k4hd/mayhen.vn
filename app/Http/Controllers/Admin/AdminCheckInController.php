<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Wedding;
use App\Services\AdminCheckInCockpitService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminCheckInController extends Controller
{
    public function __construct(
        protected AdminCheckInCockpitService $cockpitService
    ) {}

    /**
     * Hiển thị bảng điều khiển trạm giám sát đón tiếp & điểm danh ngày cưới.
     */
    public function index(Request $request): View
    {
        $weddingId = $request->input('wedding_id');
        $today = Carbon::today();

        // 1. Tìm tiệc cưới hoạt động (Ưu tiên tiệc hôm nay, hoặc tiệc gần nhất)
        $activeWedding = null;
        if (! empty($weddingId)) {
            $activeWedding = Wedding::find((int) $weddingId);
        }

        if (! $activeWedding) {
            $activeWedding = Wedding::whereDate('wedding_date', $today)->first();
        }

        if (! $activeWedding) {
            $activeWedding = Wedding::latest('wedding_date')->first();
        }

        // Danh sách đám cưới phục vụ bộ chọn
        $allWeddings = Wedding::select('id', 'groom_name', 'bride_name', 'wedding_date')
            ->orderByDesc('wedding_date')
            ->limit(50)
            ->get();

        $cockpitData = null;
        if ($activeWedding) {
            $filters = [
                'status' => $request->input('status', 'all'),
                'table_id' => $request->input('table_id'),
                'group_name' => $request->input('group_name'),
                'q' => $request->input('q'),
            ];
            $cockpitData = $this->cockpitService->getCockpitData($activeWedding, $filters);
        }

        return view('admin.pages.guests.checkin.index', [
            'activeWedding' => $activeWedding,
            'allWeddings' => $allWeddings,
            'cockpit' => $cockpitData,
            'filters' => [
                'status' => $request->input('status', 'all'),
                'table_id' => $request->input('table_id'),
                'group_name' => $request->input('group_name'),
                'q' => $request->input('q', ''),
            ],
        ]);
    }

    /**
     * API JSON Live Polling: Cập nhật nhanh số liệu KPI, Timeline và danh sách đón tiếp.
     */
    public function liveStats(Request $request, int $weddingId): JsonResponse
    {
        $wedding = Wedding::findOrFail($weddingId);
        $data = $this->cockpitService->getCockpitData($wedding, $request->all());

        return response()->json([
            'success' => true,
            'stats' => $data['stats'],
            'timeline' => $data['timeline'],
            'recentCheckIns' => $data['recentCheckIns']->map(function (Guest $g) {
                return [
                    'id' => $g->id,
                    'name' => $g->name,
                    'salutation' => $g->salutation ?: 'Thân mời',
                    'group_name' => $g->group_name ?: 'Khách chung',
                    'table_name' => $g->table?->name ?? $g->table_name ?? 'Chưa xếp bàn',
                    'attendees' => $g->checked_in_attendees ?: $g->actualAttendeesCount(),
                    'time' => $g->checked_in_at?->format('H:i'),
                ];
            }),
        ]);
    }

    /**
     * API JSON tìm kiếm tức thời quan khách theo mã token, tên, SĐT, bàn.
     */
    public function search(Request $request, int $weddingId): JsonResponse
    {
        $wedding = Wedding::findOrFail($weddingId);
        $query = (string) $request->input('q', '');
        $results = $this->cockpitService->searchGuests($wedding, $query);

        return response()->json([
            'success' => true,
            'guests' => $results,
        ]);
    }

    /**
     * Điểm danh 1 chạm có điều chỉnh sĩ số và bàn tiệc.
     */
    public function checkIn(Request $request, int $guestId): JsonResponse|RedirectResponse
    {
        $request->validate([
            'attendees' => 'nullable|integer|min:1|max:50',
            'note' => 'nullable|string|max:255',
            'table_id' => 'nullable|integer|exists:wedding_tables,id',
        ]);

        $attendees = (int) ($request->input('attendees') ?: 1);
        $note = $request->input('note');
        $tableId = $request->input('table_id') ? (int) $request->input('table_id') : null;
        $operator = $request->user()?->name ?? 'Admin';

        $result = $this->cockpitService->checkInGuest($guestId, $attendees, $note, $tableId, $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Hủy điểm danh đưa quan khách về trạng thái chưa đến.
     */
    public function cancel(Request $request, int $guestId): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $result = $this->cockpitService->cancelCheckIn($guestId, $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Đổi bàn tiệc trực tiếp tại trạm đón tiếp.
     */
    public function updateTable(Request $request, int $guestId): JsonResponse|RedirectResponse
    {
        $request->validate([
            'table_id' => 'required|integer|exists:wedding_tables,id',
        ]);

        $operator = $request->user()?->name ?? 'Admin';
        $result = $this->cockpitService->updateGuestTable($guestId, (int) $request->input('table_id'), $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Đón tiếp khách phát sinh (Walk-in) tại cửa sự kiện.
     */
    public function walkIn(Request $request, int $weddingId): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'salutation' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'group_name' => 'nullable|string|max:100',
            'table_id' => 'nullable|integer|exists:wedding_tables,id',
            'attendees' => 'required|integer|min:1|max:20',
            'note' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Vui lòng nhập họ tên khách phát sinh.',
            'attendees.required' => 'Vui lòng nhập số người đi cùng.',
        ]);

        $operator = $request->user()?->name ?? 'Admin';
        $guest = $this->cockpitService->createWalkInGuest($weddingId, $validated, $operator);

        $msg = "Đã đón tiếp thành công khách phát sinh \"{$guest->name}\" ({$guest->checked_in_attendees} người).";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'guest' => $guest,
                'message' => $msg,
            ], 201);
        }

        return back()->with('success', $msg);
    }

    /**
     * Xuất tệp báo cáo điểm danh đón tiếp định dạng CSV UTF-8 BOM.
     */
    public function export(Request $request, int $weddingId): Response
    {
        $wedding = Wedding::findOrFail($weddingId);

        return $this->cockpitService->exportAttendanceCsv($wedding, $request->all());
    }
}
