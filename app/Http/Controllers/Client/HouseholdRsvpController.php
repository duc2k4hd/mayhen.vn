<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\WeddingTable;
use App\Services\AuditLogger;
use App\Services\HouseholdRsvpService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HouseholdRsvpController extends Controller
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
     * Màn hình Báo Cáo Khách Mời Hộ Gia Đình & Suất Ăn Bếp Tiệc (Mục 2.4 DOCX).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $metrics = HouseholdRsvpService::computeMetrics($wedding);

        $tables = WeddingTable::with(['guests.response'])
            ->where('wedding_id', $wedding->id)
            ->orderBy('id')
            ->get();

        return view('client.pages.account.household.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'metrics' => $metrics,
            'tables' => $tables,
        ]);
    }

    /**
     * Màn hình in khổ A4 Bảng Kê Suất Ăn & Bàn Tiệc cho Bếp Trưởng & Quản Lý Nhà Hàng.
     */
    public function printKitchenBrief(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $metrics = HouseholdRsvpService::computeMetrics($wedding);

        $tables = WeddingTable::with(['guests.response'])
            ->where('wedding_id', $wedding->id)
            ->orderBy('id')
            ->get();

        AuditLogger::log(
            'KITCHEN_BRIEF_PRINTED',
            'Invitation',
            $invitation->id,
            "Chủ tiệc in Bảng kê suất ăn Bếp trưởng cho đám cưới {$wedding->groom_name} & {$wedding->bride_name}"
        );

        return view('client.pages.account.household.print', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'metrics' => $metrics,
            'tables' => $tables,
        ]);
    }

    /**
     * Tải tệp CSV Bảng Kê Suất Ăn & Bàn Tiệc UTF-8 BOM.
     */
    public function exportKitchenCsv(Request $request, int $invitationId): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $csvContent = HouseholdRsvpService::exportKitchenBriefCsv($wedding);

        AuditLogger::log(
            'KITCHEN_BRIEF_EXPORTED',
            'Invitation',
            $invitation->id,
            "Chủ tiệc xuất CSV Bảng kê suất ăn Bếp trưởng cho thiệp #{$invitation->id}"
        );

        $filename = 'bang-ke-suat-an-bep-truong-'.str_replace(' ', '-', strtolower($wedding->groom_name.'-'.$wedding->bride_name)).'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
