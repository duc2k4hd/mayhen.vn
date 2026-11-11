<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\WeddingRescheduleLog;
use App\Services\AdminRescheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminRescheduleController extends Controller
{
    public function __construct(
        protected AdminRescheduleService $rescheduleService
    ) {}

    /**
     * Danh sách nhật ký dời lịch cưới & tái xác nhận RSVP.
     */
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->input('q', ''),
            'wedding_id' => $request->input('wedding_id'),
            'rsvp_action' => $request->input('rsvp_action', 'all'),
        ];

        $data = $this->rescheduleService->getRescheduleOverview($filters);
        $weddings = Wedding::latest()->limit(50)->get(['id', 'title', 'groom_name', 'bride_name', 'wedding_date', 'venue_name']);

        return view('admin.pages.reschedule.index', [
            'stats' => $data['stats'],
            'logs' => $data['logs'],
            'filters' => $filters,
            'weddings' => $weddings,
        ]);
    }

    /**
     * Thực hiện đổi ngày cưới / địa điểm cho tiệc cưới.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wedding_id' => 'required|exists:weddings,id',
            'new_date' => 'required|date',
            'new_venue' => 'nullable|string|max:255',
            'new_venue_address' => 'nullable|string|max:500',
            'reason' => 'required|string|max:1000',
            'rsvp_action' => 'required|in:keep_existing,request_reconfirm',
        ]);

        $wedding = Wedding::findOrFail($validated['wedding_id']);
        $operator = $request->user()?->name ?? 'Admin';

        $log = $this->rescheduleService->rescheduleWedding($wedding, $validated, $operator);

        $msg = "Đã cập nhật ngày cưới mới ({$log->new_date->format('d/m/Y')}) cho lễ cưới \"{$wedding->title}\". Ảnh hưởng {$log->affected_guests_count} khách mời.";

        return back()->with('success', $msg);
    }

    /**
     * Gửi / Ghi nhận thông báo đính chính ngày cưới tới quan khách.
     */
    public function notifyGuests(Request $request, int $logId): JsonResponse|RedirectResponse
    {
        $log = WeddingRescheduleLog::findOrFail($logId);
        $operator = $request->user()?->name ?? 'Admin';

        $notifiedCount = $request->filled('notified_count') ? (int) $request->input('notified_count') : $log->affected_guests_count;
        $this->rescheduleService->broadcastNotification($log, $notifiedCount, $operator);

        $msg = "Đã ghi nhận phát thông báo đính chính ngày cưới tới {$log->notified_guests_count}/{$log->affected_guests_count} quan khách.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'notified_guests_count' => $log->notified_guests_count,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Xuất tệp nhật ký dời lịch cưới CSV UTF-8 BOM.
     */
    public function export(Request $request): Response
    {
        return $this->rescheduleService->exportRescheduleLogsCsv($request->all());
    }
}
