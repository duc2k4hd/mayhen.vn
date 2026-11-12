<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\LoveStoryTimelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoveStoryController extends Controller
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
     * Màn hình Studio quản trị Hành Trình Yêu (IDEA-A08 & Section 24 PRD V3).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $storyConfig = LoveStoryTimelineService::resolveLoveStoryConfig($invitation);
        $availableIcons = LoveStoryTimelineService::getAvailableIcons();

        return view('client.pages.account.love_story.index', compact('invitation', 'storyConfig', 'availableIcons'));
    }

    /**
     * Cập nhật danh sách các cột mốc tình yêu.
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'title' => 'required|string|max:120',
            'subtitle' => 'nullable|string|max:255',
            'milestones' => 'nullable|array',
            'milestones.*.id' => 'nullable|string|max:50',
            'milestones.*.date_label' => 'nullable|string|max:80',
            'milestones.*.title' => 'required|string|max:120',
            'milestones.*.description' => 'nullable|string|max:1000',
            'milestones.*.photo_url' => 'nullable|string|max:500',
            'milestones.*.icon' => 'nullable|string|max:30',
        ]);

        $validated['enabled'] = $request->boolean('enabled');

        $updated = LoveStoryTimelineService::updateLoveStory($invitation, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã lưu danh sách cột mốc tình yêu thành công!',
                'data' => $updated,
            ]);
        }

        return redirect()->route('account.love_story.index', $invitation->id)
            ->with('success', 'Đã lưu và cập nhật Hành Trình Tình Yêu lên thiệp cưới thành công!');
    }

    /**
     * Tải lên ảnh kỷ niệm cho một cột mốc.
     */
    public function uploadPhoto(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp,heic|max:10240',
        ]);

        try {
            $url = LoveStoryTimelineService::uploadMilestonePhoto($invitation, $request->file('photo'));

            return response()->json([
                'success' => true,
                'url' => $url,
                'message' => 'Tải ảnh kỷ niệm thành công!',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
