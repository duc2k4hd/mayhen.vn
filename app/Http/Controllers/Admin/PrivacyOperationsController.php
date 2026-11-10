<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PrivacyErasureRequest;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;
use App\Services\PrivacyRetentionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivacyOperationsController extends Controller
{
    /**
     * Bảng điều khiển Quản trị Quyền Riêng Tư & Lưu Trữ Dữ Liệu.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'all');

        $requestsQuery = PrivacyErasureRequest::with(['user', 'wedding', 'reviewer'])
            ->latest();

        if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected', 'executed'], true)) {
            $requestsQuery->where('status', $status);
        }

        $requests = $requestsQuery->paginate(15)->withQueryString();

        $eligibleWeddings = PrivacyRetentionService::scanRetentionEligibleWeddings(180);

        $kpis = [
            'pending_requests' => PrivacyErasureRequest::where('status', 'pending')->count(),
            'executed_requests' => PrivacyErasureRequest::where('status', 'executed')->count(),
            'eligible_retention_weddings' => $eligibleWeddings->count(),
            'preserved_orders_count' => Order::where('status', 'paid')->count(),
        ];

        return view('admin.pages.privacy.index', [
            'requests' => $requests,
            'eligibleWeddings' => $eligibleWeddings,
            'kpis' => $kpis,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Chạy dọn dẹp hàng loạt PII khách mời các tiệc cưới đã quá hạn lưu trữ.
     */
    public function executeRetentionBatch(Request $request): RedirectResponse
    {
        $weddingIds = $request->input('wedding_ids', []);

        if (empty($weddingIds)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một tiệc cưới để thực hiện dọn dẹp PII.');
        }

        $weddings = Wedding::whereIn('id', $weddingIds)->get();
        $totalGuestsAnonymized = 0;
        $totalResponsesAnonymized = 0;
        $weddingsCount = 0;

        foreach ($weddings as $wedding) {
            $res = PrivacyRetentionService::anonymizeWeddingGuestPii($wedding, false);
            $totalGuestsAnonymized += $res['guests_anonymized_count'];
            $totalResponsesAnonymized += $res['responses_anonymized_count'];
            $weddingsCount++;
        }

        AuditLogger::log(
            action: 'privacy.batch_retention_executed',
            targetType: 'system',
            targetId: 0,
            description: "Thực thi dọn dẹp PII hàng loạt cho {$weddingsCount} tiệc cưới quá hạn. Đã ẩn danh {$totalGuestsAnonymized} số điện thoại khách.",
            details: [
                'weddings_count' => $weddingsCount,
                'total_guests_anonymized' => $totalGuestsAnonymized,
                'total_responses_anonymized' => $totalResponsesAnonymized,
            ]
        );

        return back()->with('success', "Đã hoàn tất dọn dẹp PII cho {$weddingsCount} tiệc cưới ({$totalGuestsAnonymized} SĐT khách mời đã được ẩn danh an toàn).");
    }

    /**
     * Phê duyệt yêu cầu xóa / ẩn danh hóa (Maker-Checker Step 1).
     */
    public function approveErasureRequest(Request $request, int $id): RedirectResponse
    {
        $erasureRequest = PrivacyErasureRequest::findOrFail($id);

        if ($erasureRequest->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể phê duyệt yêu cầu đang ở trạng thái chờ duyệt.');
        }

        $erasureRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes', 'Đã xác minh danh tính và chấp thuận yêu cầu theo Luật 91/2025/QH15.'),
        ]);

        AuditLogger::log(
            action: 'privacy.erasure_request_approved',
            targetType: 'privacy_erasure_request',
            targetId: $erasureRequest->id,
            description: "Phê duyệt yêu cầu xóa dữ liệu {$erasureRequest->request_code} của người dùng #{$erasureRequest->user_id}",
            details: [
                'request_code' => $erasureRequest->request_code,
                'user_id' => $erasureRequest->user_id,
            ]
        );

        return back()->with('success', "Đã phê duyệt yêu cầu {$erasureRequest->request_code}. Quản trị viên có thể bấm Thực thi để hoàn tất.");
    }

    /**
     * Từ chối yêu cầu xóa / ẩn danh hóa.
     */
    public function rejectErasureRequest(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'review_notes' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'review_notes.required' => 'Vui lòng nhập lý do từ chối yêu cầu để bảo đảm minh bạch kiểm toán.',
            'review_notes.min' => 'Lý do từ chối tối thiểu 5 ký tự.',
        ]);

        $erasureRequest = PrivacyErasureRequest::findOrFail($id);

        if ($erasureRequest->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể từ chối yêu cầu đang ở trạng thái chờ duyệt.');
        }

        $erasureRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        AuditLogger::log(
            action: 'privacy.erasure_request_rejected',
            targetType: 'privacy_erasure_request',
            targetId: $erasureRequest->id,
            description: "Từ chối yêu cầu xóa dữ liệu {$erasureRequest->request_code}: {$request->review_notes}",
            details: [
                'request_code' => $erasureRequest->request_code,
                'user_id' => $erasureRequest->user_id,
                'reason' => $request->review_notes,
            ]
        );

        return back()->with('success', "Đã từ chối yêu cầu {$erasureRequest->request_code}.");
    }

    /**
     * Thực thi ẩn danh hóa tài khoản (Maker-Checker Step 2).
     */
    public function executeErasure(Request $request, int $id): RedirectResponse
    {
        $erasureRequest = PrivacyErasureRequest::with('user.weddings')->findOrFail($id);

        if ($erasureRequest->status !== 'approved') {
            return back()->with('error', 'Yêu cầu phải được cấp Quản lý phê duyệt (Approved) trước khi thực thi xóa/ẩn danh.');
        }

        if (! $erasureRequest->user) {
            return back()->with('error', 'Không tìm thấy người dùng gắn liền với yêu cầu này.');
        }

        $res = PrivacyRetentionService::anonymizeUserAccount($erasureRequest->user, $erasureRequest);

        return back()->with('success', "Đã thực thi ẩn danh hóa thành công cho yêu cầu {$erasureRequest->request_code}. Hồ sơ kế toán ({$res['orders_preserved']} đơn hàng) được bảo toàn nguyên vẹn.");
    }

    /**
     * Tải file xuất dữ liệu DSAR an toàn thông qua One-Time / TTL Token.
     */
    public function downloadDsarExport(Request $request, string $token): StreamedResponse|RedirectResponse
    {
        $userId = PrivacyRetentionService::verifyAndConsumeDownloadToken($token);

        if (! $userId) {
            abort(403, 'Đường link tải dữ liệu cá nhân (DSAR) này đã hết hạn sau 24 giờ hoặc đã vượt quá số lần tải cho phép vì lý do an toàn thông tin.');
        }

        $user = User::with(['weddings.invitations', 'weddings.guests', 'orders'])->findOrFail($userId);

        $payload = [
            'compliance' => 'Luật Bảo Vệ Dữ Liệu Cá Nhân (Luật 91/2025/QH15) — Secure DSAR Export',
            'exported_at' => now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'weddings' => $user->weddings->map(fn ($w) => [
                'id' => $w->id,
                'groom_name' => $w->groom_name,
                'bride_name' => $w->bride_name,
                'wedding_date' => $w->wedding_date?->format('Y-m-d'),
                'guests_count' => $w->guests->count(),
            ]),
            'orders' => $user->orders->map(fn ($o) => [
                'id' => $o->id,
                'order_code' => $o->order_code,
                'amount' => $o->amount,
                'status' => $o->status,
                'paid_at' => $o->paid_at?->toIso8601String(),
            ]),
        ];

        AuditLogger::log(
            action: 'privacy.dsar_download_token_consumed',
            targetType: 'user',
            targetId: $user->id,
            description: "Tải file xuất dữ liệu cá nhân DSAR qua secure token cho người dùng #{$user->id}",
            details: ['token_prefix' => substr($token, 0, 12)]
        );

        $fileName = 'dsar_export_user_'.$user->id.'_'.date('Ymd_His').'.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $fileName, ['Content-Type' => 'application/json']);
    }
}
