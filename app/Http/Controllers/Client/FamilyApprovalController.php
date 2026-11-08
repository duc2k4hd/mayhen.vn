<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Rules\NoProfanity;
use App\Services\TwoFamilyApprovalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FamilyApprovalController extends Controller
{
    /**
     * Màn hình rà soát & duyệt thiệp cưới dành riêng cho phụ mẫu hai bên (không cần đăng nhập).
     */
    public function review(string $token, TwoFamilyApprovalService $service): View
    {
        $data = $service->getFamilyReviewData($token);

        return view('client.pages.family-review', [
            'data' => $data,
        ]);
    }

    /**
     * Ghi nhận quyết định phê duyệt hoặc góp ý của phụ mẫu.
     */
    public function submitDecision(Request $request, string $token, TwoFamilyApprovalService $service): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:approved,feedback'],
            'note' => ['nullable', 'string', 'max:1000', new NoProfanity],
        ], [
            'decision.required' => 'Vui lòng chọn quyết định phê duyệt hoặc gửi góp ý.',
            'decision.in' => 'Quyết định không hợp lệ.',
            'note.max' => 'Lời nhắn không được vượt quá 1000 ký tự.',
        ]);

        $result = $service->submitDecision($token, $validated['decision'], $validated['note'] ?? null);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $validated['decision'] === 'approved'
                    ? 'Đã ghi nhận chúc phúc và phê duyệt của phụ mẫu thành công!'
                    : 'Đã gửi ý kiến góp ý tới cô dâu chú rể thành công!',
                'data' => $result,
            ]);
        }

        $msg = $validated['decision'] === 'approved'
            ? 'Kính cảm ơn Bố Mẹ! Ý kiến phê duyệt và lời chúc phúc đã được lưu lại.'
            : 'Đã gửi góp ý của Bố Mẹ tới các con để chỉnh sửa lại.';

        return back()->with('success', $msg);
    }

    /**
     * API xem trạng thái phê duyệt của hai nhà dành cho cặp đôi (Chủ sở hữu).
     */
    public function status(int $invitationId, TwoFamilyApprovalService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation->wedding);

        $data = $service->getOrCreateApprovalTokens($invitation);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function authorizeOwner(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        abort(404);
    }
}

