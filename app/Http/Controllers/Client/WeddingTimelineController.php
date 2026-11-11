<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\WeddingTimelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeddingTimelineController extends Controller
{
    /**
     * Xác thực quyền sở hữu thiệp cưới của người dùng đang đăng nhập.
     */
    protected function authorizeInvitationOwner(Invitation $invitation): void
    {
        $user = Auth::user();
        if (! $user || ! $invitation->wedding || $invitation->wedding->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền thao tác trên thiệp cưới này.');
        }
    }

    /**
     * Màn hình Studio cấu hình Lịch Trình Ngày Cưới (Section 25 PRD V3 & IDEA-A06).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $presets = WeddingTimelineService::getPresetScenarios();
        $availableIcons = WeddingTimelineService::getAvailableIcons();
        $timelineConfig = WeddingTimelineService::resolveTimelineConfig($invitation);

        return view('client.pages.account.timeline.index', compact(
            'invitation',
            'presets',
            'availableIcons',
            'timelineConfig'
        ));
    }

    /**
     * Cập nhật danh sách các mốc thời gian và hoạt động ngày cưới.
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'title' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:255',
            'scenario_key' => 'required|string|max:50',
            'calendar_reminder_hours' => 'nullable|integer|min:1|max:48',
            'items' => 'required|array|min:1|max:15',
            'items.*.time' => 'required|string|max:30',
            'items.*.title' => 'required|string|max:150',
            'items.*.location' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.icon' => 'nullable|string|max:50',
        ]);

        $validated['enabled'] = $request->boolean('enabled');

        WeddingTimelineService::updateTimeline($invitation, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật lịch trình ngày cưới thành công!',
                'timeline' => WeddingTimelineService::resolveTimelineConfig($invitation),
            ]);
        }

        return redirect()->route('account.timeline.index', $invitation->id)
            ->with('success', 'Đã cập nhật lịch trình hôn lễ ngày cưới thành công!');
    }

    /**
     * Tải tệp iCalendar (.ics) cho Apple Calendar và điện thoại thông minh.
     */
    public function downloadIcs(Request $request, string $slugOrId): Response
    {
        $invitation = is_numeric($slugOrId)
            ? Invitation::with('wedding')->findOrFail((int) $slugOrId)
            : Invitation::with('wedding')->where('slug', $slugOrId)->firstOrFail();

        $icsContent = WeddingTimelineService::generateIcsContent($invitation);
        $filename = 'wedding-timeline-'.($invitation->slug ?: $invitation->id).'.ics';

        return response($icsContent, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
