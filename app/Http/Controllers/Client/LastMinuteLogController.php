<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\LastMinuteLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LastMinuteLogController extends Controller
{
    public function __construct(
        protected LastMinuteLogService $logService
    ) {}

    /**
     * Màn hình Studio Nhật Ký Phát Sinh Giờ Chót Ngày Cưới.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $filters = [
            'category' => $request->query('category'),
            'status' => $request->query('status'),
            'priority' => $request->query('priority'),
            'search' => $request->query('search'),
        ];

        $logs = $this->logService->getLogs($invitation, $filters);
        $stats = $this->logService->getStats($invitation);

        AuditLogger::log(
            'LAST_MINUTE_LOGS_STUDIO_ACCESSED',
            'Invitation',
            $invitation->id,
            "Mở Nhật Ký Phát Sinh Giờ Chót thiệp #{$invitation->id}"
        );

        return view('client.pages.account.last_minute_logs.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'logs' => $logs,
            'stats' => $stats,
            'categories' => LastMinuteLogService::CATEGORIES,
            'priorities' => LastMinuteLogService::PRIORITIES,
            'statuses' => LastMinuteLogService::STATUSES,
            'filters' => $filters,
        ]);
    }

    /**
     * Thêm mới một bản ghi phát sinh.
     */
    public function store(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:seating,dietary,concierge,stage,logistics,other'],
            'priority' => ['required', 'string', 'in:urgent,high,normal'],
            'assignee_name' => ['nullable', 'string', 'max:100'],
            'related_table' => ['nullable', 'string', 'max:100'],
            'related_guest_name' => ['nullable', 'string', 'max:100'],
            'cost_impact' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->logService->createLog($invitation, $validated, Auth::user());

        return back()->with('success', 'Đã ghi nhận phát sinh mới thành công.');
    }

    /**
     * Cập nhật thông tin bản ghi phát sinh.
     */
    public function update(Request $request, int $invitationId, string $logId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:seating,dietary,concierge,stage,logistics,other'],
            'priority' => ['required', 'string', 'in:urgent,high,normal'],
            'assignee_name' => ['nullable', 'string', 'max:100'],
            'related_table' => ['nullable', 'string', 'max:100'],
            'related_guest_name' => ['nullable', 'string', 'max:100'],
            'cost_impact' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->logService->updateLog($invitation, $logId, $validated, Auth::user());

        return back()->with('success', 'Đã cập nhật thông tin phát sinh thành công.');
    }

    /**
     * Chuyển trạng thái xử lý 1-chạm (Chờ -> Đang làm -> Đã xong).
     */
    public function updateStatus(Request $request, int $invitationId, string $logId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,in_progress,resolved,cancelled'],
        ]);

        $this->logService->toggleStatus($invitation, $logId, $validated['status'], Auth::user());

        return back()->with('success', 'Đã chuyển trạng thái phát sinh thành công.');
    }

    /**
     * Xóa một bản ghi phát sinh.
     */
    public function destroy(Request $request, int $invitationId, string $logId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);

        $this->logService->deleteLog($invitation, $logId, Auth::user());

        return back()->with('success', 'Đã xóa bản ghi phát sinh thành công.');
    }

    /**
     * Xuất danh sách nhật ký phát sinh sang file CSV UTF-8 BOM.
     */
    public function export(Request $request, int $invitationId): Response
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $csvData = $this->logService->exportCsv($invitation);

        $filename = 'nhat-ky-phat-sinh-ngay-cuoi-'.($invitation->slug ?: $invitation->id).'-'.date('Ymd-His').'.csv';

        return response($csvData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * API JSON lấy danh sách phát sinh thời gian thực phục vụ Tablet/Dashboard.
     */
    public function apiList(Request $request, int $invitationId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $logs = $this->logService->getLogs($invitation);
        $stats = $this->logService->getStats($invitation);

        return response()->json([
            'success' => true,
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }

    /**
     * Kiểm tra và phân quyền truy cập thiệp cưới của người dùng hiện tại hoặc Admin.
     */
    protected function resolveAuthorizedInvitation(int $invitationId): Invitation
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);

        if (! $user->isAdmin()) {
            if (! $invitation->wedding || (int) $invitation->wedding->user_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền quản lý nhật ký phát sinh của thiệp cưới này.');
            }
        }

        return $invitation;
    }
}
