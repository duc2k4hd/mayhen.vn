<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\SignatureArchiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SignatureArchiveController extends Controller
{
    public function __construct(
        protected SignatureArchiveService $signatureService
    ) {}

    /**
     * Màn hình Studio Kho Lưu Trữ Bút Tích Chữ Ký Tay Quan Khách.
     */
    public function index(Request $request, int|string $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $data = $this->signatureService->getSignaturesSummary($wedding, $invitation);

        AuditLogger::log(
            'SIGNATURE_ARCHIVE_VIEWED',
            'Invitation',
            $invitation->id,
            'Xem kho lưu trữ chữ ký tay: '.$data['kpis']['total_signatures'].' chữ ký'
        );

        return view('client.pages.account.signature_archive.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'kpis' => $data['kpis'],
            'config' => $data['config'],
            'signatures' => $data['signatures'],
        ]);
    }

    /**
     * Màn hình toàn màn hình Trình Chiếu Bức Tranh Ký Tên Vàng (Signature Mosaic Wall).
     */
    public function mosaic(Request $request, int|string $invitationId): View
    {
        $invitation = Invitation::with('wedding.guests')->findOrFail($invitationId);
        $wedding = $invitation->wedding;

        if (! $wedding) {
            abort(404, 'Không tìm thấy thông tin hôn lễ.');
        }

        $mosaicData = $this->signatureService->getMosaicData($wedding, $invitation);

        return view('client.pages.signature_archive.mosaic', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'mosaicData' => $mosaicData,
        ]);
    }

    /**
     * Cập nhật chữ ký tay Cô Dâu & Chú Rể và tùy chọn hiển thị Tranh Ký Tên.
     */
    public function updateCouple(Request $request, int|string $invitationId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);

        $validated = $request->validate([
            'groom_signature_data' => 'nullable|string',
            'bride_signature_data' => 'nullable|string',
            'mosaic_theme' => 'nullable|string|in:cream_gold,ivory_charcoal,obsidian_night,dusty_rose',
            'mosaic_title' => 'nullable|string|max:255',
            'mosaic_quote' => 'nullable|string|max:500',
        ]);

        $updatedInvitation = $this->signatureService->updateCoupleSignatures($invitation, $validated);

        AuditLogger::log(
            'COUPLE_SIGNATURES_UPDATED',
            'Invitation',
            $invitation->id,
            'Đã cập nhật bút tích chữ ký cô dâu chú rể và chủ đề tranh ký tên'
        );

        $summary = $this->signatureService->getSignaturesSummary($invitation->wedding, $updatedInvitation);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu chữ ký tay cặp đôi và cấu hình thành công!',
            'config' => $summary['config'],
        ]);
    }

    /**
     * Ghim hoặc bỏ ghim chữ ký trang trọng.
     */
    public function togglePin(Request $request, int|string $invitationId, int $wishId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $wish = $this->signatureService->togglePin($wedding, $wishId);

        $action = $wish->is_pinned ? 'Đã ghim trang trọng' : 'Đã bỏ ghim';
        AuditLogger::log(
            'SIGNATURE_PIN_TOGGLED',
            'Invitation',
            $invitation->id,
            "{$action} chữ ký của khách #{$wish->id} ({$wish->sender_name})"
        );

        return response()->json([
            'success' => true,
            'is_pinned' => $wish->is_pinned,
            'message' => $action.' chữ ký thành công.',
        ]);
    }

    /**
     * Xóa chữ ký (null hóa URL chữ ký mà vẫn giữ lại lời chúc nếu có).
     */
    public function destroySignature(Request $request, int|string $invitationId, int $wishId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $this->signatureService->removeSignature($wedding, $wishId);

        AuditLogger::log(
            'SIGNATURE_REMOVED',
            'Invitation',
            $invitation->id,
            "Đã gỡ bỏ chữ ký của khách #{$wishId}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã gỡ bỏ chữ ký khỏi kho lưu trữ thành công.',
        ]);
    }

    /**
     * API Polling nạp chữ ký mới thời gian thực cho màn hình Mosaic Wall hoặc Studio.
     */
    public function feed(Request $request, int|string $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding.guests')->findOrFail($invitationId);
        $wedding = $invitation->wedding;

        if (! $wedding) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $data = $this->signatureService->getSignaturesSummary($wedding, $invitation);

        return response()->json([
            'success' => true,
            'kpis' => $data['kpis'],
            'config' => $data['config'],
            'signatures' => $data['signatures'],
        ]);
    }

    /**
     * Xác thực quyền sở hữu thiệp cưới.
     */
    protected function resolveAuthorizedInvitation(Request $request, int|string $invitationId): Invitation
    {
        $user = $request->user();
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);

        if (! $invitation->wedding || ($invitation->wedding->user_id !== $user->id && ! ($user->is_admin ?? false))) {
            abort(403, 'Bạn không có quyền truy cập thiệp cưới này.');
        }

        return $invitation;
    }
}

