<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\RsvpEnforcementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RsvpEnforcementController extends Controller
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
     * Màn hình Quản lý Hạn chót RSVP, Khảo sát Xe đưa đón & Thực đơn tiệc cưới (Section 31 PRD V3).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $enforcementConfig = RsvpEnforcementService::resolveConfig($invitation);
        $deadlineStatus = RsvpEnforcementService::getDeadlineStatus($invitation);
        $shuttleReport = RsvpEnforcementService::getShuttleBusReport($invitation);
        $dietaryReport = RsvpEnforcementService::getDietaryReport($invitation);

        return view('client.pages.account.rsvp_enforcement.index', compact(
            'invitation',
            'enforcementConfig',
            'deadlineStatus',
            'shuttleReport',
            'dietaryReport'
        ));
    }

    /**
     * Cập nhật cấu hình Hạn chót & Khảo sát đưa đón / thực đơn.
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'deadline' => 'nullable|date',
            'is_manually_closed' => 'nullable|boolean',
            'lock_after_deadline' => 'nullable|boolean',
            'closed_notice' => 'nullable|string|max:1000',
            'contact_phone' => 'nullable|string|max:30',

            // Xe đưa đón
            'shuttle_bus_enabled' => 'nullable|boolean',
            'shuttle_bus_title' => 'nullable|string|max:150',
            'shuttle_bus_description' => 'nullable|string|max:500',
            'pickup_points' => 'nullable|array',
            'pickup_points.*.id' => 'nullable|string|max:50',
            'pickup_points.*.name' => 'nullable|string|max:150',
            'pickup_points.*.address' => 'nullable|string|max:255',
            'pickup_points.*.departure_time' => 'nullable|string|max:30',
            'pickup_points.*.coordinator_name' => 'nullable|string|max:100',
            'pickup_points.*.coordinator_phone' => 'nullable|string|max:30',

            // Khảo sát thực đơn
            'dietary_survey_enabled' => 'nullable|boolean',
            'dietary_survey_title' => 'nullable|string|max:150',
            'dietary_survey_description' => 'nullable|string|max:500',
        ]);

        $validated['is_manually_closed'] = $request->boolean('is_manually_closed');
        $validated['lock_after_deadline'] = $request->has('lock_after_deadline') ? $request->boolean('lock_after_deadline') : true;
        $validated['shuttle_bus_enabled'] = $request->boolean('shuttle_bus_enabled');
        $validated['dietary_survey_enabled'] = $request->boolean('dietary_survey_enabled');

        RsvpEnforcementService::saveConfig($invitation, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật cấu hình hạn chót và khảo sát dịch vụ thành công!',
                'status' => RsvpEnforcementService::getDeadlineStatus($invitation),
            ]);
        }

        return redirect()->route('account.rsvp_enforcement.index', $invitation->id)
            ->with('success', 'Đã lưu cấu hình hạn chót RSVP và khảo sát dịch vụ thành công!');
    }

    /**
     * Xuất tệp CSV Danh Sách Xe Buýt Đưa Đón (Shuttle Bus Manifest) UTF-8 BOM.
     */
    public function exportShuttleManifest(int $invitationId): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $csvContent = RsvpEnforcementService::exportShuttleBusCsv($invitation);
        $fileName = 'danh-sach-xe-dua-don-'.($invitation->slug ?: $invitation->id).'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Xuất tệp CSV Bảng Kê Thực Đơn Tiệc Cho Bếp Trưởng (Kitchen Brief) UTF-8 BOM.
     */
    public function exportDietaryManifest(int $invitationId): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $csvContent = RsvpEnforcementService::exportDietaryCsv($invitation);
        $fileName = 'bang-ke-thuc-don-bep-'.($invitation->slug ?: $invitation->id).'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
