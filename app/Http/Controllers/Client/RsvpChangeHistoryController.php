<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\RsvpChangeHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RsvpChangeHistoryController extends Controller
{
    public function __construct(
        protected RsvpChangeHistoryService $historyService
    ) {}

    /**
     * Màn hình Studio Nhật Ký & Sổ Kiểm Toán Biến Động RSVP.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $filters = $request->only(['search', 'source', 'type', 'time_range']);
        $summary = $this->historyService->getVolatilitySummary($wedding);
        $logs = $this->historyService->getFilteredLogs($wedding, $filters);

        AuditLogger::log(
            'RSVP_CHANGE_HISTORY_ACCESSED',
            'Invitation',
            $invitation->id,
            "Truy cập sổ nhật ký biến động RSVP thiệp #{$invitation->id} ({$wedding->groom_name} & {$wedding->bride_name})"
        );

        return view('client.pages.account.rsvp_history.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'summary' => $summary,
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }

    /**
     * API JSON trích xuất dòng thời gian cá nhân hóa của 1 khách mời.
     */
    public function guestTimeline(Request $request, int $invitationId, int $guestId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $guest = $wedding->guests()->findOrFail($guestId);
        $timeline = $this->historyService->getGuestTimeline($guest);

        return response()->json([
            'success' => true,
            'guest' => [
                'id' => $guest->id,
                'name' => $guest->name,
                'salutation' => $guest->salutation,
                'group_name' => $guest->group_name,
                'table_name' => $guest->table_name,
                'expected_attendees' => $guest->expected_attendees,
            ],
            'timeline' => $timeline,
        ]);
    }

    /**
     * Xuất tệp CSV Nhật ký biến động phục vụ đối soát mâm cỗ với trung tâm tiệc cưới.
     */
    public function export(Request $request, int $invitationId): StreamedResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $filters = $request->only(['search', 'source', 'type', 'time_range']);

        AuditLogger::log(
            'RSVP_CHANGE_HISTORY_EXPORTED',
            'Invitation',
            $invitation->id,
            "Xuất tệp CSV sổ kiểm toán biến động RSVP cho thiệp #{$invitation->id}"
        );

        return $this->historyService->exportLogsToCsv($wedding, $filters);
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

        $invitation = Invitation::with(['wedding.guests', 'template'])->findOrFail($invitationId);

        if (! $user->isAdmin()) {
            if (! $invitation->wedding || (int) $invitation->wedding->user_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền quản lý lịch sử biến động RSVP của thiệp cưới này.');
            }
        }

        return $invitation;
    }
}
