<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\WeddingBroadcastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeddingBroadcastController extends Controller
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
     * Màn hình Studio cấu hình thông báo khẩn cấp ngày cưới (IDEA-A07 & Mục 4.3 DOCX).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $presets = WeddingBroadcastService::getPresetTemplates();
        $broadcastConfig = WeddingBroadcastService::resolveBroadcastConfig($invitation);

        return view('client.pages.account.broadcast.index', compact('invitation', 'presets', 'broadcastConfig'));
    }

    /**
     * Cập nhật và phát sóng thông báo khẩn cấp lên thiệp cưới.
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'is_active' => 'nullable|boolean',
            'template_key' => 'required|string|max:50',
            'urgency_level' => 'required|string|in:critical,warning,info',
            'display_type' => 'required|string|in:sticky_banner,modal_popup,both',
            'title' => 'required|string|max:150',
            'message' => 'required|string|max:1000',
            'action_text' => 'nullable|string|max:60',
            'action_url' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $updated = WeddingBroadcastService::updateBroadcast($invitation, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật thông báo khẩn cấp ngày cưới thành công!',
                'data' => $updated,
            ]);
        }

        return redirect()->route('account.broadcast.index', $invitation->id)
            ->with('success', 'Đã lưu và phát thông báo khẩn cấp lên thiệp cưới thành công!');
    }

    /**
     * Tắt thông báo khẩn cấp trên thiệp cưới.
     */
    public function deactivate(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $updated = WeddingBroadcastService::deactivateBroadcast($invitation);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã tắt thông báo khẩn cấp trên thiệp cưới.',
                'data' => $updated,
            ]);
        }

        return redirect()->route('account.broadcast.index', $invitation->id)
            ->with('success', 'Đã tắt thông báo khẩn cấp trên thiệp cưới thành công.');
    }
}
