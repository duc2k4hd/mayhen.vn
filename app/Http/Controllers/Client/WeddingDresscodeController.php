<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\WeddingDresscodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeddingDresscodeController extends Controller
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
     * Màn hình Studio cấu hình Dresscode & Bảng Màu Hôn Lễ (Section 26 PRD V3).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $presets = WeddingDresscodeService::getPresetPalettes();
        $attireStyles = WeddingDresscodeService::getAttireStyles();
        $dresscodeConfig = WeddingDresscodeService::resolveDresscodeConfig($invitation);

        return view('client.pages.account.dresscode.index', compact(
            'invitation',
            'presets',
            'attireStyles',
            'dresscodeConfig'
        ));
    }

    /**
     * Cập nhật bảng màu và hướng dẫn trang phục cưới.
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'title' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:255',
            'preset_key' => 'required|string|max:50',
            'attire_style' => 'required|string|max:50',
            'colors' => 'required|array|min:1|max:10',
            'colors.*.hex' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'colors.*.name' => 'required|string|max:100',
            'colors.*.description' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
            'gentlemen_guide' => 'nullable|string|max:500',
            'ladies_guide' => 'nullable|string|max:500',
            'avoid_colors_note' => 'nullable|string|max:500',
            'moodboard_image_url' => 'nullable|string|max:500',
        ]);

        $validated['enabled'] = $request->boolean('enabled');

        WeddingDresscodeService::updateDresscode($invitation, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật bảng màu quy chuẩn trang phục thành công!',
                'dresscode' => WeddingDresscodeService::resolveDresscodeConfig($invitation),
            ]);
        }

        return redirect()->route('account.dresscode.index', $invitation->id)
            ->with('success', 'Đã cập nhật bảng màu quy chuẩn trang phục dresscode thành công!');
    }

    /**
     * Tải lên ảnh moodboard trang phục tiệc cưới qua AJAX.
     */
    public function uploadMoodboard(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $request->validate([
            'moodboard_file' => 'required|image|mimes:jpeg,jpg,png,webp,heic|max:10240',
        ]);

        $file = $request->file('moodboard_file');
        $url = WeddingDresscodeService::uploadMoodboardImage($invitation, $file);

        return response()->json([
            'success' => true,
            'url' => $url,
            'message' => 'Tải lên ảnh moodboard gợi ý trang phục thành công!',
        ]);
    }
}
