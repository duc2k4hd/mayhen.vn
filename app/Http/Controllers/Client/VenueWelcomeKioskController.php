<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\VenueWelcomeKioskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VenueWelcomeKioskController extends Controller
{
    public function __construct(
        protected VenueWelcomeKioskService $kioskService
    ) {}

    // ==========================================
    // 1. PHÂN HỆ STUDIO CẶP ĐÔI (AUTHENTICATED)
    // ==========================================

    /**
     * Màn hình Studio cấu hình Màn Hình Chào Mừng Lễ Tân.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $config = $this->kioskService->getConfig($invitation);
        $initData = $this->kioskService->getKioskInitData($invitation);

        AuditLogger::log(
            'WELCOME_KIOSK_STUDIO_ACCESSED',
            'Invitation',
            $invitation->id,
            "Truy cập Studio Màn hình Chào Mừng Sảnh Cưới thiệp #{$invitation->id}"
        );

        return view('client.pages.account.welcome_kiosk.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'config' => $config,
            'initData' => $initData,
        ]);
    }

    /**
     * Lưu cấu hình Màn hình Chào Mừng Lễ Tân.
     */
    public function update(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);

        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'kiosk_title' => ['nullable', 'string', 'max:255'],
            'welcome_duration_seconds' => ['nullable', 'integer', 'min:4', 'max:30'],
            'sound_enabled' => ['nullable', 'boolean'],
            'show_table_name' => ['nullable', 'boolean'],
            'show_countdown' => ['nullable', 'boolean'],
            'welcome_quote' => ['nullable', 'string', 'max:500'],
            'access_pin' => ['nullable', 'string', 'max:20'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['string', 'max:1000'],
        ]);

        $this->kioskService->updateConfig($invitation, $validated);

        return back()->with('success', 'Đã lưu cấu hình Màn Hình Chào Mừng Lễ Tân thành công.');
    }

    /**
     * Kích hoạt một lượt check-in giả lập để kiểm tra hiển thị trên màn hình Kiosk.
     */
    public function simulate(Request $request, int $invitationId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $guest = $this->kioskService->simulateCheckInForTesting($wedding);

        AuditLogger::log(
            'WELCOME_KIOSK_SIMULATION_TRIGGERED',
            'Guest',
            $guest->id,
            "Kích hoạt mô phỏng chào mừng Kiosk cho khách '{$guest->name}'"
        );

        return response()->json([
            'success' => true,
            'message' => "Đã kích hoạt chào mừng thử nghiệm cho khách '{$guest->name}' tại {$guest->table_name}.",
            'guest' => [
                'id' => $guest->id,
                'name' => $guest->name,
                'salutation' => $guest->salutation,
                'table_name' => $guest->table_name,
                'checked_in_at' => $guest->checked_in_at ? $guest->checked_in_at->format('H:i:s') : '',
            ],
        ]);
    }

    // ==========================================
    // 2. PHÂN HỆ MÀN HÌNH KIOSK CÔNG KHAI
    // ==========================================

    /**
     * Màn hình Chào Mừng Kiosk toàn màn hình đặt tại bàn lễ tân sảnh cưới.
     */
    public function show(Request $request, string $slug): View
    {
        $invitation = Invitation::with(['wedding.guests', 'template'])
            ->where('slug', $slug)
            ->firstOrFail();

        $config = $this->kioskService->getConfig($invitation);

        if (! $config['enabled']) {
            abort(404, 'Màn hình chào mừng sảnh cưới của hôn lễ này hiện chưa được kích hoạt.');
        }

        $initData = $this->kioskService->getKioskInitData($invitation);

        return view('client.pages.welcome_kiosk.show', [
            'invitation' => $invitation,
            'wedding' => $invitation->wedding,
            'initData' => $initData,
        ]);
    }

    /**
     * API Polling siêu nhẹ lấy lượt check-in gần nhất theo thời gian thực (1s/lần).
     */
    public function feed(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        if (! $wedding) {
            return response()->json(['success' => false, 'checkin' => null]);
        }

        $sinceTimestampMs = $request->query('since') ? (int) $request->query('since') : null;
        $checkin = $this->kioskService->getLatestCheckInFeed($wedding, $sinceTimestampMs);

        return response()->json([
            'success' => true,
            'checkin' => $checkin,
            'server_timestamp_ms' => (int) now()->getTimestampMs(),
        ]);
    }

    /**
     * Kiểm tra và phân quyền truy cập thiệp cưới của người dùng hiện tại hoặc Admin.
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
                abort(403, 'Bạn không có quyền quản lý Màn Hình Sảnh Cưới của thiệp cưới này.');
            }
        }

        return $invitation;
    }
}
