<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Services\WeddingPrivacyConsentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PrivacyConsentController extends Controller
{
    /**
     * Màn hình Trung Tâm Minh Bạch Quyền Riêng Tư & Bảo Vệ Dữ Liệu Cá Nhân (TASK-VAL-24).
     */
    public function show(Request $request, int $id, WeddingPrivacyConsentService $service): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $settings = $service->getPrivacySettings($invitation);
        $report = $service->generatePrivacyAuditReport($invitation);

        return view('client.pages.account.privacy-consent', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'settings' => $settings,
            'report' => $report,
        ]);
    }

    /**
     * Cập nhật cấu hình bảo vệ dữ liệu cá nhân & quyền riêng tư.
     */
    public function update(Request $request, int $id, WeddingPrivacyConsentService $service): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $validated = $request->validate([
            'bank_visibility' => ['required', 'string', 'in:everyone,named_guests_only,hidden'],
            'wishes_visibility' => ['required', 'string', 'in:public,couple_only'],
            'photo_drop_moderation' => ['required', 'string', 'in:require_approval,auto_approve'],
            'phone_retention_days' => ['required', 'integer', 'in:0,30,90,180'],
            'anonymous_rsvp_allowed' => ['nullable', 'boolean'],
        ]);

        $service->updatePrivacySettings($invitation, $validated);

        return redirect()->route('privacy.show', $id)
            ->with('success', 'Đã lưu cấu hình Trung Tâm Quyền Riêng Tư & Bảo Vệ Dữ Liệu thành công!');
    }

    /**
     * API Báo cáo kiểm toán tuân thủ bảo vệ dữ liệu (Privacy Compliance Audit).
     */
    public function auditReport(int $id, WeddingPrivacyConsentService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $report = $service->generatePrivacyAuditReport($invitation);

        return response()->json([
            'success' => true,
            'report' => $report,
            'data' => $report,
        ]);
    }

    /**
     * Xác thực quyền sở hữu thiệp của cặp đôi (IDOR Defense).
     */
    private function authorizeOwner(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        if (auth()->check() && auth()->user()->partner && (int) $wedding->partner_id === (int) auth()->user()->partner->id) {
            return;
        }

        if (auth()->check() && auth()->user()->isAdmin()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        abort(404);
    }
}
