<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\WeddingWish;
use App\Services\WeddingInteractiveEndingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeddingInteractiveEndingController extends Controller
{
    /**
     * Hiển thị màn hình Studio Quản trị Sổ Ký Tên, Lưu Bút & Lời Cảm Ơn.
     */
    public function index(int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $endingStyles = WeddingInteractiveEndingService::getPresetEndingStyles();
        $thankYouPresets = WeddingInteractiveEndingService::getPresetThankYouMessages();
        $bgStyles = WeddingInteractiveEndingService::SIGNATURE_BG_STYLES;
        $config = WeddingInteractiveEndingService::resolveInteractiveEndingConfig($invitation);

        // Lấy danh sách lời chúc của đám cưới
        $wishes = WeddingWish::where('wedding_id', $invitation->wedding_id)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total' => WeddingWish::where('wedding_id', $invitation->wedding_id)->count(),
            'approved' => WeddingWish::where('wedding_id', $invitation->wedding_id)->where('is_approved', true)->count(),
            'with_signature' => WeddingWish::where('wedding_id', $invitation->wedding_id)->whereNotNull('signature_url')->count(),
            'with_photo' => WeddingWish::where('wedding_id', $invitation->wedding_id)->whereNotNull('photo_url')->count(),
            'private' => WeddingWish::where('wedding_id', $invitation->wedding_id)->where('is_private', true)->count(),
        ];

        return view('client.pages.account.interactive_ending.index', compact(
            'invitation',
            'endingStyles',
            'thankYouPresets',
            'bgStyles',
            'config',
            'wishes',
            'stats'
        ));
    }

    /**
     * Cập nhật cấu hình Sổ Ký Tên & Lời Cảm Ơn.
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'ending_style' => 'required|string|in:intimate_editorial,royal_seal,minimalist_modern',
            'ending_kicker' => 'nullable|string|max:100',
            'ending_title' => 'required|string|max:150',
            'thank_you_message' => 'required|string|max:1000',
            'couple_signature_text' => 'nullable|string|max:150',
            'ending_photo_url' => 'nullable|string|max:500',

            // Sổ ký tên
            'signature_enabled' => 'nullable|boolean',
            'signature_title' => 'required|string|max:150',
            'signature_subtitle' => 'nullable|string|max:255',
            'signature_bg_style' => 'required|string|in:stardust,wax_seal,gold_mesh',

            // Sổ lưu bút
            'wishes_enabled' => 'nullable|boolean',
            'wishes_title' => 'required|string|max:150',
            'wishes_subtitle' => 'nullable|string|max:255',
            'require_approval' => 'nullable|boolean',
            'allow_private_wishes' => 'nullable|boolean',
        ], [
            'ending_style.required' => 'Vui lòng chọn phong cách hiển thị Lời Cảm Ơn.',
            'ending_title.required' => 'Tiêu đề Lời Cảm Ơn không được để trống.',
            'thank_you_message.required' => 'Vui lòng nhập nội dung lời tri ân gửi đến quan khách.',
            'signature_title.required' => 'Tiêu đề Sổ Ký Tên không được để trống.',
            'wishes_title.required' => 'Tiêu đề Sổ Lưu Bút không được để trống.',
        ]);

        $updatedConfig = WeddingInteractiveEndingService::updateInteractiveEndingConfig(
            $invitation,
            $validated,
            Auth::user()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật Sổ Ký Tên & Lời Cảm Ơn thành công!',
                'config' => $updatedConfig,
            ]);
        }

        return redirect()
            ->route('account.interactive_ending.index', $invitationId)
            ->with('success', 'Đã lưu cấu hình Sổ Ký Tên, Lưu Bút và Lời Cảm Ơn thành công!');
    }

    /**
     * Kiểm duyệt lời chúc từ Studio (Ghim, Duyệt/Ẩn, Trả lời, Xóa).
     */
    public function moderateWish(Request $request, int $invitationId, int $wishId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $request->validate([
            'action' => 'required|string|in:pin,approve,hide,reply,delete',
            'reply_text' => 'nullable|string|max:500',
        ]);

        $result = WeddingInteractiveEndingService::moderateWish(
            $invitation,
            $wishId,
            $request->input('action'),
            $request->input('reply_text')
        );

        return response()->json($result);
    }

    /**
     * Xuất danh sách lời chúc kỷ niệm ra file CSV.
     */
    public function exportWishes(int $invitationId): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $csvData = WeddingInteractiveEndingService::exportWishesToCsv($invitation);
        $fileName = 'so-luu-but-chuc-phuc-thiep-'.$invitation->id.'.csv';

        return response($csvData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * API Khách Mời Lưu Chữ Ký Điện Tử Từ Canvas (Public API).
     */
    public function saveSignature(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);

        $request->validate([
            'guest_name' => 'required|string|max:100',
            'signature_data' => 'required|string',
            'wish_id' => 'nullable|integer',
        ], [
            'guest_name.required' => 'Vui lòng nhập tên của bạn trước khi lưu chữ ký.',
            'signature_data.required' => 'Vui lòng ký tên trên bảng vẽ trước khi bấm lưu.',
        ]);

        try {
            $url = WeddingInteractiveEndingService::saveSignatureData(
                $invitation,
                $request->input('guest_name'),
                $request->input('signature_data'),
                $request->input('wish_id') ? (int) $request->input('wish_id') : null
            );

            return response()->json([
                'success' => true,
                'message' => 'Chữ ký kỷ niệm của bạn đã được lưu lại thành công!',
                'signature_url' => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Kiểm tra quyền sở hữu thiệp cưới an toàn.
     */
    protected function authorizeInvitationOwner(Invitation $invitation): void
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
