<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\WeddingGalleryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeddingGalleryController extends Controller
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
     * Màn hình Studio quản trị Album 10 Ảnh Cưới Nghệ Thuật (IDEA-A05 & Section 27 PRD V3).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $presetLayouts = WeddingGalleryService::getPresetLayouts();
        $galleryConfig = WeddingGalleryService::resolveGalleryConfig($invitation);

        return view('client.pages.account.gallery.index', compact(
            'invitation',
            'presetLayouts',
            'galleryConfig'
        ));
    }

    /**
     * Cập nhật danh sách 10 ảnh, thứ tự sắp xếp và phong cách bố cục Album.
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'layout_style' => 'required|string|in:editorial_masonry,carousel_coverflow,classic_grid',
            'title' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:255',
            'lightbox_enabled' => 'nullable|boolean',
            'photos' => 'required|array|min:1|max:10',
            'photos.*.url' => 'required|string',
            'photos.*.caption' => 'nullable|string|max:255',
            'photos.*.alt' => 'nullable|string|max:255',
        ]);

        $validated['enabled'] = $request->boolean('enabled');
        $validated['lightbox_enabled'] = $request->boolean('lightbox_enabled');

        WeddingGalleryService::updateGallery($invitation, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật Album 10 ảnh cưới thành công!',
                'gallery' => WeddingGalleryService::resolveGalleryConfig($invitation),
            ]);
        }

        return redirect()->route('account.gallery.index', $invitation->id)
            ->with('success', 'Đã lưu bố cục và danh sách Album 10 ảnh cưới thành công!');
    }

    /**
     * Tải lên một ảnh cưới mới vào kho lưu trữ Album thiệp cưới.
     */
    public function uploadPhoto(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp,avif|max:10240', // Tối đa 10MB
        ]);

        $file = $request->file('photo');
        $photoUrl = WeddingGalleryService::uploadGalleryPhoto($invitation, $file);

        return response()->json([
            'success' => true,
            'url' => $photoUrl,
            'message' => 'Tải ảnh cưới lên thành công!',
        ]);
    }
}
