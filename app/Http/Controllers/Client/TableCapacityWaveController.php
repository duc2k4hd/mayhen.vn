<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\TableCapacityWaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TableCapacityWaveController extends Controller
{
    public function __construct(
        protected TableCapacityWaveService $waveService
    ) {}

    // ==========================================
    // 1. PHÂN HỆ STUDIO CẶP ĐÔI (AUTHENTICATED)
    // ==========================================

    /**
     * Màn hình Studio Điều Phối Sĩ Số Bàn Tiệc & Báo Cáo Sóng Khách.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $filterZone = $request->query('zone');
        $filterStatus = $request->query('status');

        $overview = $this->waveService->getWaveOverview($wedding);
        $tables = $this->waveService->getTablesDetail($wedding, $filterZone, $filterStatus);
        $allZones = $wedding->tables()->whereNotNull('zone')->distinct()->pluck('zone');

        AuditLogger::log(
            'TABLE_CAPACITY_STUDIO_ACCESSED',
            'Invitation',
            $invitation->id,
            "Mở Bảng Điều Phối Sĩ Số Bàn Tiệc thiệp #{$invitation->id}"
        );

        return view('client.pages.account.table_capacity.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'overview' => $overview,
            'tables' => $tables,
            'allZones' => $allZones,
            'currentZone' => $filterZone,
            'currentStatus' => $filterStatus,
        ]);
    }

    /**
     * Kê thêm ghế phụ vào một bàn tiệc (Quick Add Extra Seat).
     */
    public function addExtraSeat(Request $request, int $invitationId, int $tableId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $table = $wedding->tables()->findOrFail($tableId);
        $extraSeats = max(1, min(10, (int) $request->input('seats', 1)));

        $this->waveService->quickAddExtraSeats($table, $extraSeats);

        return back()->with('success', "Đã kê thêm {$extraSeats} ghế phụ vào '{$table->name}'. Sức chứa hiện tại là {$table->capacity} ghế.");
    }

    /**
     * Điều chuyển khách mời sang bàn khác (Dynamic Seat Dispatch).
     */
    public function dispatchGuest(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $validated = $request->validate([
            'guest_id' => ['required', 'integer', 'exists:guests,id'],
            'new_table_id' => ['required', 'integer', 'exists:wedding_tables,id'],
        ]);

        $guest = $wedding->guests()->findOrFail($validated['guest_id']);
        $newTable = $wedding->tables()->findOrFail($validated['new_table_id']);

        $this->waveService->dispatchGuestToTable($guest, $newTable);

        return back()->with('success', "Đã điều chuyển khách '{$guest->name}' sang '{$newTable->name}' thành công.");
    }

    // ==========================================
    // 2. PHÂN HỆ TABLET ĐIỀU PHỐI VIÊN & LỄ TÂN SẢNH TIỆC (USHER VIEW)
    // ==========================================

    /**
     * Màn hình Tablet Đón Khách & Hướng Dẫn Chỗ Ngồi Cơ Động (Full-screen Tablet Usher View).
     */
    public function usherView(Request $request, string $slug): View|RedirectResponse
    {
        $invitation = Invitation::with(['wedding.tables', 'template'])
            ->where('slug', $slug)
            ->firstOrFail();

        $wedding = $invitation->wedding;
        if (! $wedding || $invitation->isLocked() || ! $this->isAuthorizedUsherOrOwner($request, $invitation, $wedding)) {
            return redirect()->route('checkin.portal', $invitation->slug);
        }

        $overview = $this->waveService->getWaveOverview($wedding);
        $tables = $this->waveService->getTablesDetail($wedding);
        $allZones = $wedding->tables()->whereNotNull('zone')->distinct()->pluck('zone');

        return view('client.pages.table_capacity.usher', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'overview' => $overview,
            'tables' => $tables,
            'allZones' => $allZones,
        ]);
    }

    /**
     * API Polling siêu nhẹ cập nhật dữ liệu sĩ số thời gian thực (2s/lần).
     */
    public function feed(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        if (! $wedding) {
            return response()->json(['success' => false]);
        }

        if ($invitation->isLocked() || ! $this->isAuthorizedUsherOrOwner($request, $invitation, $wedding)) {
            return response()->json(['success' => false, 'message' => 'Bạn cần xác thực mã PIN lễ tân để xem dữ liệu bàn tiệc.'], 403);
        }

        $overview = $this->waveService->getWaveOverview($wedding);
        $tables = $this->waveService->getTablesDetail($wedding);

        return response()->json([
            'success' => true,
            'overview' => $overview,
            'tables' => $tables,
            'server_timestamp' => now()->timestamp,
        ]);
    }

    protected function isAuthorizedUsherOrOwner(Request $request, Invitation $invitation, $wedding): bool
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ((int) $wedding->user_id === (int) $user->id || $user->isAdmin()) {
                return true;
            }
            if ($wedding->partner_id && $user->partner && (int) $wedding->partner_id === (int) $user->partner->id) {
                return true;
            }
        }

        $ownedSlugs = (array) $request->session()->get('owned_invitation_slugs', []);
        if (in_array($invitation->slug, $ownedSlugs, true)) {
            return true;
        }

        return $request->session()->get("receptionist_verified_{$invitation->id}") === true;
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

        $invitation = Invitation::with(['wedding.tables', 'template'])->findOrFail($invitationId);

        if (! $user->isAdmin()) {
            if (! $invitation->wedding || (int) $invitation->wedding->user_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền quản lý sĩ số bàn tiệc của thiệp cưới này.');
            }
        }

        return $invitation;
    }
}
