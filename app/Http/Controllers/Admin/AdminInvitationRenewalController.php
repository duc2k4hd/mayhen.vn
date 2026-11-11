<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\InvitationRenewalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminInvitationRenewalController extends Controller
{
    public function __construct(
        protected InvitationRenewalService $renewalService
    ) {}

    /**
     * Màn hình quản trị gia hạn, hết hạn thiệp cưới & thời gian ân hạn (Mục 139 PRD V3).
     */
    public function index(Request $request): View
    {
        $kpis = $this->renewalService->getRenewalKpis();
        $invitations = $this->renewalService->getFilteredInvitations($request);
        $graceDays = $this->renewalService->getGraceDays();

        return view('admin.pages.renewals.index', compact(
            'kpis',
            'invitations',
            'graceDays'
        ));
    }

    /**
     * Gia hạn thời hạn lưu trữ thiệp cưới thủ công.
     */
    public function extend(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::findOrFail($id);
        $duration = (string) $request->input('duration', '1_month');
        $reason = (string) $request->input('reason', '');

        $this->renewalService->extendDuration($invitation, $duration, $reason, $request->user());

        return back()->with('success', "Đã gia hạn thành công thời hạn lưu trữ cho thiệp cưới [{$invitation->slug}].");
    }

    /**
     * Gửi email nhắc gia hạn thiệp cưới cho cặp đôi.
     */
    public function sendReminder(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::findOrFail($id);
        $result = $this->renewalService->sendRenewalReminder($invitation, $request->user());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Chuyển thiệp cưới sang trạng thái lưu trữ kỷ niệm (Archive).
     */
    public function archive(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::findOrFail($id);
        $reason = (string) $request->input('reason', '');

        $this->renewalService->archiveInvitation($invitation, $reason, $request->user());

        return back()->with('success', "Đã chuyển thiệp cưới [{$invitation->slug}] sang trạng thái lưu trữ kỷ niệm.");
    }

    /**
     * Khôi phục thiệp cưới từ trạng thái lưu trữ kỷ niệm.
     */
    public function unarchive(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::findOrFail($id);
        $this->renewalService->unarchiveInvitation($invitation, $request->user());

        return back()->with('success', "Đã khôi phục thiệp cưới [{$invitation->slug}] hoạt động bình thường.");
    }

    /**
     * Xuất danh sách theo dõi gia hạn ra tệp CSV UTF-8 BOM.
     */
    public function export(Request $request): StreamedResponse
    {
        return $this->renewalService->exportCsv($request);
    }
}
