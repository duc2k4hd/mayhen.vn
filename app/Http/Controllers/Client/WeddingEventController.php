<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\WeddingEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeddingEventController extends Controller
{
    public function __construct(
        protected WeddingEventService $eventService
    ) {}

    /**
     * Danh sách và quản lý các sự kiện của thiệp cưới.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $events = $this->eventService->getEventsForWedding($wedding);

        return view('client.pages.account.events.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'events' => $events,
        ]);
    }

    /**
     * Thêm sự kiện con mới (Lễ Vu Quy, Lễ Thành Hôn, Tiệc Cưới...).
     */
    public function store(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_type' => 'required|string|in:ceremony_bride,ceremony_groom,engagement,banquet,after_party,other',
            'event_date' => 'required|date',
            'event_time' => 'nullable|string|max:20',
            'lunar_date' => 'nullable|string|max:255',
            'venue_name' => 'required|string|max:255',
            'venue_address' => 'nullable|string|max:1000',
            'map_url' => 'nullable|url|max:1000',
            'dress_code' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Vui lòng nhập tên sự kiện (ví dụ: Lễ Vu Quy, Tiệc Cưới).',
            'event_date.required' => 'Vui lòng chọn ngày tổ chức sự kiện.',
            'venue_name.required' => 'Vui lòng nhập tên địa điểm tổ chức.',
            'map_url.url' => 'Đường dẫn bản đồ Google Maps không hợp lệ.',
        ]);

        $this->eventService->createEvent($wedding, $validated, $request->user()->id);

        return back()->with('success', "Đã thêm sự kiện '{$validated['name']}' vào lịch trình đám cưới.");
    }

    /**
     * Cập nhật thông tin sự kiện con.
     */
    public function update(Request $request, int $invitationId, int $eventId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $event = $wedding->events()->findOrFail($eventId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_type' => 'required|string|in:ceremony_bride,ceremony_groom,engagement,banquet,after_party,other',
            'event_date' => 'required|date',
            'event_time' => 'nullable|string|max:20',
            'lunar_date' => 'nullable|string|max:255',
            'venue_name' => 'required|string|max:255',
            'venue_address' => 'nullable|string|max:1000',
            'map_url' => 'nullable|url|max:1000',
            'dress_code' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Vui lòng nhập tên sự kiện.',
            'event_date.required' => 'Vui lòng chọn ngày tổ chức sự kiện.',
            'venue_name.required' => 'Vui lòng nhập tên địa điểm tổ chức.',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->eventService->updateEvent($event, $validated, $request->user()->id);

        return back()->with('success', "Đã cập nhật sự kiện '{$event->name}' thành công.");
    }

    /**
     * Xóa một sự kiện con.
     */
    public function destroy(Request $request, int $invitationId, int $eventId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $event = $wedding->events()->findOrFail($eventId);

        if ($wedding->events()->count() <= 1) {
            return back()->with('error', 'Đám cưới cần có tối thiểu 1 sự kiện chính, không thể xóa sự kiện cuối cùng.');
        }

        $eventName = $event->name;
        $this->eventService->deleteEvent($event, $request->user()->id);

        return back()->with('success', "Đã xóa sự kiện '{$eventName}' khỏi lịch trình.");
    }

    /**
     * API JSON lấy danh sách sự kiện cho Studio Editor và Live Sync.
     */
    public function apiEvents(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;

        $payload = $this->eventService->getPublicEventsPayload($wedding);

        return response()->json([
            'success' => true,
            'events' => $payload,
        ]);
    }

    /**
     * Kiểm tra quyền sở hữu thiệp của người dùng hiện tại hoặc Admin.
     */
    protected function resolveAuthorizedInvitation(Request $request, int $invitationId): Invitation
    {
        $user = $request->user();

        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);

        if (! $user->isAdmin()) {
            if (! $invitation->wedding || (int) $invitation->wedding->user_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền quản lý sự kiện của thiệp cưới này.');
            }
        }

        return $invitation;
    }
}
