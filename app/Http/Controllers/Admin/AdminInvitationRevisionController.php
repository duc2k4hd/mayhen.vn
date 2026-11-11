<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\InvitationRevisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminInvitationRevisionController extends Controller
{
    public function __construct(
        protected InvitationRevisionService $revisionService
    ) {}

    /**
     * Hiển thị bảng thanh tra lịch sử phiên bản của một thiệp cưới
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding.user', 'template', 'latestRevision'])->findOrFail($invitationId);

        $filters = [
            'type' => $request->input('type', 'all'),
        ];

        $revisions = $this->revisionService->getRevisions($invitationId, $filters, 20);

        return view('admin.pages.invitations.revisions', compact('invitation', 'revisions', 'filters'));
    }

    /**
     * Lấy dữ liệu so sánh sai khác (Change Diff) giữa 2 phiên bản
     */
    public function diff(Request $request, int $invitationId, int $revisionId): JsonResponse
    {
        $compareWithId = $request->query('compare_with') ? (int) $request->query('compare_with') : null;
        $diffData = $this->revisionService->getRevisionDiff($invitationId, $revisionId, $compareWithId);

        return response()->json([
            'success' => true,
            'data' => $diffData,
        ]);
    }

    /**
     * Phục hồi an toàn thiệp cưới về phiên bản được chọn
     */
    public function restore(Request $request, int $invitationId, int $revisionId): RedirectResponse
    {
        $invitation = Invitation::findOrFail($invitationId);
        $newRevision = $this->revisionService->restoreRevision($invitationId, $revisionId, Auth::id());

        return redirect()->route('admin.invitations.revisions.index', $invitationId)
            ->with('success', "Đã phục hồi thành công thiệp cưới [{$invitation->title}] về phiên bản v{$newRevision->version_number}. Toàn bộ nội dung đã được cập nhật an toàn.");
    }
}
