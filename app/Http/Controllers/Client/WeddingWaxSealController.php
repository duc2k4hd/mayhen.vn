<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\WeddingWaxSealService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeddingWaxSealController extends Controller
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
     * Màn hình Studio thiết kế con dấu sáp hoàng gia (IDEA-A03 & Mục 4.1 DOCX).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $palettes = WeddingWaxSealService::getColorPalettes();
        $emblems = WeddingWaxSealService::getSealEmblems();
        $rimStyles = WeddingWaxSealService::getRimStyles();
        $sealConfig = WeddingWaxSealService::resolveWaxSealConfig($invitation);

        return view('client.pages.account.wax_seal.index', compact('invitation', 'palettes', 'emblems', 'rimStyles', 'sealConfig'));
    }

    /**
     * Cập nhật cấu hình con dấu sáp hoàng gia.
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'color_palette' => 'required|string|in:burgundy,royal_gold,sage_green,dusty_rose,midnight_navy,pearl_white',
            'emblem' => 'required|string|in:monogram,double_happiness,olive_branch,intertwined_rings,love_birds,hydrangea_flower',
            'rim_style' => 'required|string|in:vintage_deckled,royal_beaded,minimal_smooth',
            'monogram_text' => 'nullable|string|max:7',
            'motto_text' => 'nullable|string|max:20',
            'crack_sound' => 'nullable|boolean',
            'shimmer_effect' => 'nullable|boolean',
        ]);

        $validated['crack_sound'] = $request->boolean('crack_sound');
        $validated['shimmer_effect'] = $request->boolean('shimmer_effect');

        $updated = WeddingWaxSealService::updateWaxSeal($invitation, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã lưu cấu hình con dấu sáp hoàng gia thành công!',
                'data' => $updated,
            ]);
        }

        return redirect()->route('account.wax_seal.index', $invitation->id)
            ->with('success', 'Đã lưu và áp dụng con dấu sáp hoàng gia lên thiệp cưới thành công!');
    }
}
