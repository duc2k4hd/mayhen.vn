<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\PublishDiffInspectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PublishDiffController extends Controller
{
    public function __construct(
        protected PublishDiffInspectorService $diffService
    ) {}

    /**
     * Màn hình Studio Thanh Tra & So Sánh Bản Nháp vs Bản Đang Xuất Bản.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $diff = $this->diffService->computeDiff($invitation);
        $liveSnapshot = $this->diffService->getLiveSnapshot($invitation);
        $draftSnapshot = $this->diffService->getDraftSnapshot($invitation);
        $history = $this->diffService->getPublishHistory($invitation);

        AuditLogger::log(
            'PUBLISH_DIFF_INSPECTOR_ACCESSED',
            'Invitation',
            $invitation->id,
            "Mở Thanh Tra So Sánh Bản Nháp thiệp #{$invitation->id} (Có {$diff['total_changes']} thay đổi)"
        );

        return view('client.pages.account.publish_diff.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'diff' => $diff,
            'liveSnapshot' => $liveSnapshot,
            'draftSnapshot' => $draftSnapshot,
            'history' => $history,
            'categoryLabels' => PublishDiffInspectorService::CATEGORY_LABELS,
        ]);
    }

    /**
     * Xác nhận xuất bản bản nháp thành bản live chính thức (Commit Publish).
     */
    public function publish(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);

        $validated = $request->validate([
            'change_note' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->diffService->commitPublish(
            $invitation,
            $validated['change_note'] ?? null,
            Auth::user()
        );

        AuditLogger::log(
            'INVITATION_PUBLISHED_FROM_DIFF',
            'Invitation',
            $invitation->id,
            "Xuất bản thành công phiên bản v{$result['revision_id']} với {$result['changes_count']} thay đổi"
        );

        return redirect()->route('account.publish_diff.index', $invitation->id)
            ->with('success', "Đã xuất bản thành công phiên bản v{$result['revision_id']} vào lúc {$result['published_at']}. Khách mời giờ đây sẽ thấy phiên bản mới nhất.");
    }

    /**
     * Hủy bỏ toàn bộ các thay đổi nháp, hoàn tác về đúng bản Live đang phát hành.
     */
    public function discard(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);

        $this->diffService->discardDraftChanges($invitation);

        AuditLogger::log(
            'INVITATION_DRAFT_DISCARDED',
            'Invitation',
            $invitation->id,
            "Đã hủy toàn bộ thay đổi nháp, hoàn tác về bản Live thiệp #{$invitation->id}"
        );

        return redirect()->route('account.publish_diff.index', $invitation->id)
            ->with('info', 'Đã hủy bỏ toàn bộ thay đổi trong bản nháp. Dữ liệu đã được khôi phục về bản đang phát hành.');
    }

    /**
     * Khôi phục dữ liệu về một phiên bản lịch sử cụ thể.
     */
    public function rollback(Request $request, int $invitationId, int $revisionId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);

        $success = $this->diffService->rollbackToRevision($invitation, $revisionId, Auth::user());

        if (! $success) {
            return back()->with('error', 'Không tìm thấy dữ liệu lịch sử của phiên bản này để khôi phục.');
        }

        AuditLogger::log(
            'INVITATION_ROLLBACK_TO_REVISION',
            'Invitation',
            $invitation->id,
            "Đã khôi phục dữ liệu nháp về phiên bản v{$revisionId}"
        );

        return redirect()->route('account.publish_diff.index', $invitation->id)
            ->with('success', "Đã khôi phục dữ liệu của phiên bản v{$revisionId} vào bản nháp. Bạn hãy kiểm tra lại bảng so sánh và bấm 'Xuất Bản' để áp dụng chính thức.");
    }

    /**
     * API JSON lấy kết quả Diff phục vụ nhúng vào Studio Editor.
     */
    public function apiDiff(Request $request, int $invitationId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $diff = $this->diffService->computeDiff($invitation);

        return response()->json([
            'success' => true,
            'diff' => $diff,
            'revision_id' => $invitation->revision_id ?? 1,
            'published_at' => $invitation->published_at ? $invitation->published_at->format('d/m/Y H:i') : null,
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

        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);

        if (! $user->isAdmin()) {
            if (! $invitation->wedding || (int) $invitation->wedding->user_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền quản lý phiên bản thiệp cưới này.');
            }
        }

        return $invitation;
    }
}

