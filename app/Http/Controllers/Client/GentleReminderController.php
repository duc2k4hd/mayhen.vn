<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\GentleReminderPlannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GentleReminderController extends Controller
{
    public function __construct(
        protected GentleReminderPlannerService $plannerService
    ) {}

    /**
     * Màn hình Studio Trợ Lý Nhắc Hẹn RSVP (IDEA-B08 & Section 30/31 PRD V3).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation);

        $selectedGroup = $request->query('group', 'all');
        $selectedStatus = $request->query('status', 'all');

        $planData = $this->plannerService->plan($invitation->wedding, $invitation, $selectedGroup);

        // Lọc theo trạng thái nhắc nếu có
        $filteredReminders = $planData['reminders'];
        if ($selectedStatus !== 'all') {
            $filteredReminders = array_values(array_filter($filteredReminders, function ($item) use ($selectedStatus) {
                return $item['status_type'] === $selectedStatus;
            }));
        }

        return view('client.pages.account.reminder.index', [
            'invitation' => $invitation,
            'wedding' => $invitation->wedding,
            'stats' => $planData['stats'],
            'recommendedTime' => $planData['recommended_time'],
            'groups' => $planData['groups'],
            'presets' => $planData['presets'],
            'reminders' => $filteredReminders,
            'selectedGroup' => $selectedGroup,
            'selectedStatus' => $selectedStatus,
        ]);
    }

    /**
     * Xem trước tin nhắn mẫu cho 1 khách mời theo preset (AJAX).
     */
    public function previewMessage(Request $request, int $invitationId, int $guestId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation);

        $guest = Guest::where('wedding_id', $invitation->wedding_id)->findOrFail($guestId);
        $presetKey = $request->input('preset', 'intimate');

        $rendered = GentleReminderPlannerService::renderMessageForGuest(
            $guest,
            $invitation->wedding,
            $invitation,
            $presetKey
        );

        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $guest->phone);
        $zaloLink = $cleanPhone ? "https://zalo.me/{$cleanPhone}" : null;

        return response()->json([
            'success' => true,
            'guest_id' => $guest->id,
            'guest_name' => $guest->name,
            'phone' => $guest->phone,
            'message' => $rendered,
            'zalo_link' => $zaloLink,
        ]);
    }

    /**
     * Ghi nhận đã gửi nhắc hẹn cho 1 khách mời (AJAX).
     */
    public function record(Request $request, int $invitationId, int $guestId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation);

        $guest = Guest::where('wedding_id', $invitation->wedding_id)->findOrFail($guestId);
        $channel = $request->input('channel', 'zalo');
        $customMessage = $request->input('message');

        $updatedGuest = GentleReminderPlannerService::recordReminder(
            $guest,
            $channel,
            $customMessage,
            Auth::user()
        );

        return response()->json([
            'success' => true,
            'message' => "Đã ghi nhận nhắc hẹn lần thứ {$updatedGuest->reminder_count} cho khách {$updatedGuest->name}.",
            'reminder_count' => $updatedGuest->reminder_count,
            'reminded_at' => $updatedGuest->reminded_at ? $updatedGuest->reminded_at->format('d/m H:i') : null,
        ]);
    }

    /**
     * Ghi nhận nhắc hẹn hàng loạt cho danh sách khách mời (AJAX).
     */
    public function batchRecord(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation);

        $request->validate([
            'guest_ids' => 'required|array',
            'guest_ids.*' => 'integer|exists:guests,id',
            'channel' => 'nullable|string|max:50',
        ]);

        $guestIds = $request->input('guest_ids', []);
        $channel = $request->input('channel', 'zalo');

        $count = GentleReminderPlannerService::batchRecordReminders(
            $invitation->wedding,
            $guestIds,
            $channel,
            Auth::user()
        );

        return response()->json([
            'success' => true,
            'message' => "Đã ghi nhận nhắc hẹn cho {$count} khách mời thành công!",
            'updated_count' => $count,
        ]);
    }

    /**
     * Xuất danh sách khách cần nhắc hẹn ra file CSV UTF-8 BOM.
     */
    public function exportCsv(int $invitationId): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation);

        $csvContent = GentleReminderPlannerService::exportReminderCsv(
            $invitation->wedding,
            $invitation
        );

        $fileName = 'danh-sach-nhac-hen-rsvp-thiep-'.$invitation->id.'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Kiểm tra quyền sở hữu thiệp cưới an toàn.
     */
    protected function authorizeOwner(Invitation $invitation): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Vui lòng đăng nhập để tiếp tục.');
        }

        if ($user->role === 'admin') {
            return;
        }

        if ($invitation->wedding && (int) $invitation->wedding->user_id === (int) $user->id) {
            return;
        }

        abort(403, 'Bạn không có quyền quản lý thiệp cưới này.');
    }
}
