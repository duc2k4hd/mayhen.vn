<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\FamilyCoHostPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FamilyPortalController extends Controller
{
    public function __construct(
        protected FamilyCoHostPortalService $portalService
    ) {}

    // ==========================================
    // 1. PHÂN HỆ QUẢN TRỊ CẶP ĐÔI (STUDIO)
    // ==========================================

    /**
     * Màn hình Studio quản lý Cổng Phụ Huynh & Phân Quyền Gia Đình Hai Bên.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $config = $this->portalService->getConfig($invitation);

        $groomStats = $this->portalService->getSideStats($wedding, $config['groom']['allowed_groups']);
        $brideStats = $this->portalService->getSideStats($wedding, $config['bride']['allowed_groups']);

        // Thu thập các nhóm khách hiện có trong danh bạ
        $existingGroups = $wedding->guests()
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->distinct()
            ->pluck('group_name')
            ->all();

        AuditLogger::log(
            'FAMILY_PORTAL_STUDIO_ACCESSED',
            'Invitation',
            $invitation->id,
            "Cặp đôi truy cập quản trị Cổng Gia Đình thiệp #{$invitation->id}"
        );

        return view('client.pages.account.family_portal.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'config' => $config,
            'groomStats' => $groomStats,
            'brideStats' => $brideStats,
            'existingGroups' => $existingGroups,
        ]);
    }

    /**
     * Cập nhật cấu hình phân quyền gia đình hai bên.
     */
    public function update(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);

        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'groom' => ['nullable', 'array'],
            'groom.enabled' => ['nullable', 'boolean'],
            'groom.manager_name' => ['nullable', 'string', 'max:100'],
            'groom.pin' => ['nullable', 'string', 'min:4', 'max:10'],
            'groom.default_group' => ['nullable', 'string', 'max:50'],
            'groom.allowed_groups' => ['nullable', 'array'],
            'bride' => ['nullable', 'array'],
            'bride.enabled' => ['nullable', 'boolean'],
            'bride.manager_name' => ['nullable', 'string', 'max:100'],
            'bride.pin' => ['nullable', 'string', 'min:4', 'max:10'],
            'bride.default_group' => ['nullable', 'string', 'max:50'],
            'bride.allowed_groups' => ['nullable', 'array'],
        ]);

        $this->portalService->updateConfig($invitation, $validated);

        return back()->with('success', 'Đã lưu cấu hình Cổng Gia Đình và phân quyền khách mời thành công.');
    }

    /**
     * Tái tạo mã Token truy cập trực tiếp 1-chạm (thu hồi link cũ).
     */
    public function regenerateToken(Request $request, int $invitationId, string $side): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);

        if (! in_array($side, [FamilyCoHostPortalService::SIDE_GROOM, FamilyCoHostPortalService::SIDE_BRIDE], true)) {
            return response()->json(['success' => false, 'message' => 'Phía gia đình không hợp lệ.'], 400);
        }

        $newToken = $this->portalService->regenerateToken($invitation, $side);

        $sideLabel = $side === FamilyCoHostPortalService::SIDE_GROOM ? 'Nhà Trai' : 'Nhà Gái';
        $newUrl = route('family_portal.show', [
            'slug' => $invitation->slug,
            'side' => $side,
            'token' => $newToken,
        ]);

        return response()->json([
            'success' => true,
            'token' => $newToken,
            'url' => $newUrl,
            'message' => "Đã tạo mã truy cập mới cho phía {$sideLabel}.",
        ]);
    }

    // ==========================================
    // 2. PHÂN HỆ CỔNG PHỤ HUYNH (SENIOR-FRIENDLY PORTAL)
    // ==========================================

    /**
     * Màn hình Cổng Khách Mời dành riêng cho Phụ Huynh / Đồng Chủ Trì.
     */
    public function show(Request $request, string $slug, string $side): View|RedirectResponse
    {
        $invitation = Invitation::with(['wedding', 'template'])->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $side = $this->validateSide($side);
        $config = $this->portalService->getConfig($invitation);

        if (! $config['enabled']) {
            abort(404, 'Cổng Gia Đình của thiệp cưới này hiện chưa được kích hoạt.');
        }

        $sideKey = $side === FamilyCoHostPortalService::SIDE_GROOM ? 'groom' : 'bride';
        $sideConfig = $config[$sideKey];

        if (! $sideConfig['enabled']) {
            abort(404, 'Cổng quản lý này hiện đang tạm đóng.');
        }

        // Kiểm tra truy cập 1-chạm qua mã Token trên URL
        $token = $request->query('token');
        if ($token && $this->portalService->authenticate($invitation, $side, $token)) {
            $request->session()->put("family_portal_{$invitation->id}_{$side}", true);
        }

        // Nếu chưa xác thực qua session, chuyển sang màn hình nhập PIN
        if (! $this->isSideAuthenticated($request, $invitation->id, $side)) {
            return view('client.pages.family_portal.pin', [
                'invitation' => $invitation,
                'wedding' => $wedding,
                'side' => $side,
                'sideConfig' => $sideConfig,
            ]);
        }

        $search = $request->query('search');
        $status = $request->query('status');

        $stats = $this->portalService->getSideStats($wedding, $sideConfig['allowed_groups']);
        $guests = $this->portalService->getSideGuests($wedding, $sideConfig['allowed_groups'], $search, $status);

        return view('client.pages.family_portal.show', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'side' => $side,
            'sideConfig' => $sideConfig,
            'stats' => $stats,
            'guests' => $guests,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Xác thực mã PIN phụ huynh.
     */
    public function verifyPin(Request $request, string $slug, string $side): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $side = $this->validateSide($side);

        $request->validate([
            'pin' => ['required', 'string', 'max:20'],
        ], [
            'pin.required' => 'Vui lòng nhập mã PIN bảo mật.',
        ]);

        $pin = trim((string) $request->input('pin'));

        if (! $this->portalService->authenticate($invitation, $side, null, $pin)) {
            return back()->with('error', 'Mã PIN không chính xác. Quý phụ huynh vui lòng hỏi lại cô dâu chú rể.');
        }

        $request->session()->put("family_portal_{$invitation->id}_{$side}", true);

        return redirect()->route('family_portal.show', ['slug' => $slug, 'side' => $side]);
    }

    /**
     * Đăng xuất khỏi Cổng Phụ Huynh.
     */
    public function logout(Request $request, string $slug, string $side): RedirectResponse
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();
        $side = $this->validateSide($side);

        $request->session()->forget("family_portal_{$invitation->id}_{$side}");

        return redirect()->route('family_portal.show', ['slug' => $slug, 'side' => $side])
            ->with('success', 'Đã đăng xuất khỏi cổng gia đình an toàn.');
    }

    /**
     * Phụ huynh thêm khách mới vào danh sách phía mình.
     */
    public function storeGuest(Request $request, string $slug, string $side): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;
        $side = $this->validateSide($side);

        if (! $this->isSideAuthenticated($request, $invitation->id, $side)) {
            abort(403, 'Phiên làm việc đã hết hạn. Vui lòng nhập lại mã PIN.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'salutation' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'expected_attendees' => ['nullable', 'integer', 'min:1', 'max:20'],
            'group_name' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên khách mời.',
        ]);

        $guest = $this->portalService->createSideGuest($wedding, $validated, $side);

        return back()->with('success', "Đã thêm khách mời '{$guest->salutation} {$guest->name}' vào danh sách thành công.");
    }

    /**
     * Phụ huynh cập nhật trạng thái tham dự RSVP hộ quan khách.
     */
    public function updateRsvp(Request $request, string $slug, string $side, int $guestId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;
        $side = $this->validateSide($side);

        if (! $this->isSideAuthenticated($request, $invitation->id, $side)) {
            abort(403, 'Phiên làm việc đã hết hạn.');
        }

        $config = $this->portalService->getConfig($invitation);
        $sideKey = $side === FamilyCoHostPortalService::SIDE_GROOM ? 'groom' : 'bride';
        $sideConfig = $config[$sideKey];

        // Đảm bảo khách thuộc danh sách nhóm được ủy quyền
        $guest = $this->portalService->getSideGuests($wedding, $sideConfig['allowed_groups'])
            ->firstWhere('id', $guestId);

        if (! $guest) {
            abort(404, 'Không tìm thấy khách mời hoặc khách không thuộc phạm vi quản lý của phía gia đình này.');
        }

        $validated = $request->validate([
            'attending_status' => ['required', 'in:attending,declined,pending'],
            'adults_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->portalService->updateSideGuestRsvp(
            $guest,
            $validated['attending_status'],
            isset($validated['adults_count']) ? (int) $validated['adults_count'] : null,
            $validated['note'] ?? null,
            $side
        );

        return back()->with('success', "Đã cập nhật trạng thái tham dự cho khách '{$guest->name}'.");
    }

    /**
     * Trang in danh sách khách A4 chữ to cho phụ huynh mang theo tiếp khách ngày cưới.
     */
    public function print(Request $request, string $slug, string $side): View
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;
        $side = $this->validateSide($side);

        if (! $this->isSideAuthenticated($request, $invitation->id, $side)) {
            abort(403, 'Vui lòng xác thực mã PIN trước khi in danh sách.');
        }

        $config = $this->portalService->getConfig($invitation);
        $sideKey = $side === FamilyCoHostPortalService::SIDE_GROOM ? 'groom' : 'bride';
        $sideConfig = $config[$sideKey];

        $guests = $this->portalService->getSideGuests($wedding, $sideConfig['allowed_groups']);
        $stats = $this->portalService->getSideStats($wedding, $sideConfig['allowed_groups']);

        return view('client.pages.family_portal.print', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'side' => $side,
            'sideConfig' => $sideConfig,
            'guests' => $guests,
            'stats' => $stats,
        ]);
    }

    // ==========================================
    // 3. CÁC HÀM TIỆN ÍCH NỘI BỘ
    // ==========================================

    /**
     * Xác thực quyền sở hữu thiệp của người dùng hiện tại hoặc Admin.
     */
    protected function resolveAuthorizedInvitation(int $invitationId): Invitation
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        $invitation = Invitation::with(['wedding.guests', 'template'])->findOrFail($invitationId);

        if (! $user->isAdmin()) {
            if (! $invitation->wedding || (int) $invitation->wedding->user_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền quản lý Cổng Gia Đình của thiệp cưới này.');
            }
        }

        return $invitation;
    }

    /**
     * Chuẩn hóa và kiểm tra phía gia đình.
     */
    protected function validateSide(string $side): string
    {
        $side = trim(mb_strtolower($side));
        if (! in_array($side, [FamilyCoHostPortalService::SIDE_GROOM, FamilyCoHostPortalService::SIDE_BRIDE], true)) {
            abort(404, 'Đường dẫn cổng gia đình không hợp lệ.');
        }

        return $side;
    }

    /**
     * Kiểm tra phiên đăng nhập của phụ huynh theo phía.
     */
    protected function isSideAuthenticated(Request $request, int $invitationId, string $side): bool
    {
        // 1. Chủ nhân thiệp hoặc Admin luôn có quyền xem
        $user = Auth::user();
        if ($user) {
            $invitation = Invitation::with('wedding')->find($invitationId);
            if ($user->isAdmin() || ($invitation && $invitation->wedding && (int) $invitation->wedding->user_id === (int) $user->id)) {
                return true;
            }
        }

        // 2. Kiểm tra session xác thực của phụ huynh
        return $request->session()->get("family_portal_{$invitationId}_{$side}") === true;
    }
}
