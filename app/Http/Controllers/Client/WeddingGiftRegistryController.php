<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\WeddingGiftRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeddingGiftRegistryController extends Controller
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
     * Màn hình Studio cấu hình Hộp Mừng Cưới & Quà Tặng (Section 9 Standards & IDEA-F07).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $boxStyles = WeddingGiftRegistryService::getPresetBoxStyles();
        $etiquetteNotes = WeddingGiftRegistryService::getPresetEtiquetteNotes();
        $banks = WeddingGiftRegistryService::getVietnameseBanks();
        $registryConfig = WeddingGiftRegistryService::resolveGiftRegistryConfig($invitation);

        return view('client.pages.account.gift_registry.index', compact(
            'invitation',
            'boxStyles',
            'etiquetteNotes',
            'banks',
            'registryConfig'
        ));
    }

    /**
     * Cập nhật thông tin Hộp Mừng Cưới & VietQR.
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'box_style' => 'required|string|in:ribbon_box,wax_envelope,editorial_card',
            'kicker' => 'nullable|string|max:100',
            'title' => 'required|string|max:150',
            'etiquette_note' => 'required|string|max:1000',
            'mask_account_numbers' => 'nullable|boolean',
            'active_tab' => 'nullable|string|in:all,groom_only,bride_only',

            // Nhà trai
            'groom_enabled' => 'nullable|boolean',
            'groom_role_label' => 'nullable|string|max:100',
            'groom_bank_name' => 'nullable|string|max:100',
            'groom_bank_bin' => 'nullable|string|max:20',
            'groom_account_number' => 'nullable|string|max:50',
            'groom_account_holder' => 'nullable|string|max:100',
            'groom_transfer_note' => 'nullable|string|max:100',
            'groom_custom_qr_url' => 'nullable|string|max:500',

            // Nhà gái
            'bride_enabled' => 'nullable|boolean',
            'bride_role_label' => 'nullable|string|max:100',
            'bride_bank_name' => 'nullable|string|max:100',
            'bride_bank_bin' => 'nullable|string|max:20',
            'bride_account_number' => 'nullable|string|max:50',
            'bride_account_holder' => 'nullable|string|max:100',
            'bride_transfer_note' => 'nullable|string|max:100',
            'bride_custom_qr_url' => 'nullable|string|max:500',

            // Wishlist quà tặng
            'wishlist_enabled' => 'nullable|boolean',
            'wishlist_title' => 'nullable|string|max:150',
            'wishlist_subtitle' => 'nullable|string|max:255',
            'wishlist_items' => 'nullable|array',
            'wishlist_items.*.id' => 'nullable|integer',
            'wishlist_items.*.name' => 'nullable|string|max:150',
            'wishlist_items.*.category' => 'nullable|string|max:100',
            'wishlist_items.*.estimated_price' => 'nullable|string|max:100',
            'wishlist_items.*.product_link' => 'nullable|string|max:500',
            'wishlist_items.*.status' => 'nullable|string|in:available,reserved',
            'wishlist_items.*.reserved_by' => 'nullable|string|max:100',
        ], [
            'box_style.required' => 'Vui lòng chọn phong cách hiển thị Hộp Mừng Cưới.',
            'title.required' => 'Tiêu đề Hộp Mừng Cưới không được để trống.',
            'etiquette_note.required' => 'Vui lòng nhập lời ngỏ tế nhị cho quan khách.',
        ]);

        $updatedConfig = WeddingGiftRegistryService::updateGiftRegistryConfig(
            $invitation,
            $validated,
            Auth::user()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật Hộp Mừng Cưới & VietQR thành công!',
                'config' => $updatedConfig,
            ]);
        }

        return redirect()
            ->route('account.gift_registry.index', $invitationId)
            ->with('success', 'Đã lưu cấu hình Hộp Mừng Cưới, VietQR và Quà Tặng thành công!');
    }

    /**
     * Tải lên ảnh mã QR tùy chọn qua AJAX.
     */
    public function uploadCustomQr(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $request->validate([
            'qr_file' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'side' => 'required|string|in:groom,bride',
        ], [
            'qr_file.required' => 'Vui lòng chọn tệp ảnh mã QR.',
            'qr_file.image' => 'Tệp tải lên phải là định dạng hình ảnh.',
            'qr_file.max' => 'Dung lượng ảnh tối đa là 5MB.',
        ]);

        $side = $request->input('side', 'groom');
        $url = WeddingGiftRegistryService::uploadCustomQrPhoto(
            $invitation,
            $request->file('qr_file'),
            $side
        );

        return response()->json([
            'success' => true,
            'message' => 'Tải lên ảnh mã QR thành công!',
            'url' => $url,
            'side' => $side,
        ]);
    }

    /**
     * Khách mời đăng ký nhận tặng món quà trong danh sách Wishlist (Public API).
     */
    public function reserveItem(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);

        $validated = $request->validate([
            'item_id' => 'required|integer',
            'guest_name' => 'required|string|max:100',
            'guest_phone' => 'nullable|string|max:20',
        ], [
            'item_id.required' => 'Vui lòng chọn món quà muốn gửi tặng.',
            'guest_name.required' => 'Vui lòng để lại tên của bạn để cô dâu chú rể biết nhé.',
        ]);

        $result = WeddingGiftRegistryService::reserveWishlistItem(
            $invitation,
            (int) $validated['item_id'],
            $validated['guest_name'],
            $validated['guest_phone'] ?? ''
        );

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }
}
