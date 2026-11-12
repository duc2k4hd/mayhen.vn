<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\EmergencyOfflinePackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmergencyOfflinePackController extends Controller
{
    public function __construct(
        protected EmergencyOfflinePackService $offlineService
    ) {}

    /**
     * Màn hình Studio Gói Dự Phòng Mất Mạng Ngày Cưới (IDEA-C07 & TASK-VAL-11).
     */
    public function index(int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $snapshot = $this->offlineService->getOfflineSnapshot($invitation);

        return view('client.pages.account.offline_pack.index', compact(
            'invitation',
            'wedding',
            'snapshot'
        ));
    }

    /**
     * Tải tệp HTML độc lập chạy ngoại tuyến 100% không cần server (Single-File Offline Bundle).
     */
    public function downloadHtml(int $invitationId): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $htmlContent = $this->offlineService->generateStandaloneHtml($invitation);
        $filename = "MayHen_Goi_Ngoai_Tuyen_{$invitation->slug}.html";

        AuditLogger::log(
            'OFFLINE_HTML_PACK_DOWNLOADED',
            'Invitation',
            $invitation->id,
            "Người dùng đã tải tệp HTML ngoại tuyến dự phòng mất mạng [{$filename}]",
            ['user_id' => Auth::id()]
        );

        return response($htmlContent, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Tải tệp JSON dữ liệu snapshot ngoại tuyến.
     */
    public function downloadJson(int $invitationId): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $snapshot = $this->offlineService->getOfflineSnapshot($invitation);
        $jsonContent = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = "MayHen_Du_Lieu_Khach_{$invitation->slug}.json";

        return response($jsonContent, 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Đồng bộ danh sách khách check-in ngoại tuyến từ lễ tân lên hệ thống.
     */
    public function sync(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'checkins' => ['required', 'array'],
        ]);

        $result = $this->offlineService->syncOfflineCheckins($invitation, $validated['checkins']);

        return response()->json($result);
    }

    /**
     * Xác thực quyền sở hữu thiệp cưới chống IDOR.
     */
    private function authorizeInvitationOwner(Invitation $invitation): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Bạn chưa đăng nhập.');
        }

        if (! $invitation->wedding || ((int) $invitation->wedding->user_id !== (int) $user->id && ! ($user->is_admin ?? false))) {
            abort(403, 'Bạn không có quyền truy cập gói ngoại tuyến của thiệp cưới này.');
        }
    }
}

