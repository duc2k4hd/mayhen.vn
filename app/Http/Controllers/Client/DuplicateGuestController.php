<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\DuplicateGuestDetectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DuplicateGuestController extends Controller
{
    public function __construct(
        protected DuplicateGuestDetectorService $detectorService
    ) {}

    /**
     * Màn hình Studio Nhận Diện & Rà Soát Khách Mời Trùng Lặp.
     */
    public function index(Request $request, int|string $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $scanResult = $this->detectorService->scan($wedding);

        AuditLogger::log(
            'DUPLICATE_GUEST_SCAN_ACCESSED',
            'Invitation',
            $invitation->id,
            'Đã rà soát khách mời trùng lặp: phát hiện '.$scanResult['kpis']['duplicate_pairs_count'].' cặp nghi vấn'
        );

        return view('client.pages.account.duplicate_guests.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'kpis' => $scanResult['kpis'],
            'pairs' => $scanResult['pairs'],
        ]);
    }

    /**
     * Quét lại danh sách trùng lặp qua AJAX.
     */
    public function scan(Request $request, int|string $invitationId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $scanResult = $this->detectorService->scan($wedding);

        return response()->json([
            'success' => true,
            'kpis' => $scanResult['kpis'],
            'pairs' => $scanResult['pairs'],
            'message' => 'Đã quét lại danh sách khách mời thành công.',
        ]);
    }

    /**
     * Gộp 2 khách mời thành 1 bản ghi duy nhất qua AJAX.
     */
    public function merge(Request $request, int|string $invitationId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $validated = $request->validate([
            'primary_guest_id' => 'required|integer',
            'secondary_guest_id' => 'required|integer|different:primary_guest_id',
            'name' => 'nullable|string|max:255',
            'salutation' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'group_name' => 'nullable|string|max:100',
            'table_id' => 'nullable|integer',
            'combine_attendees' => 'nullable|boolean',
        ]);

        $mergedGuest = $this->detectorService->merge(
            $wedding,
            (int) $validated['primary_guest_id'],
            (int) $validated['secondary_guest_id'],
            $validated
        );

        AuditLogger::log(
            'GUEST_DUPLICATE_MERGED',
            'Invitation',
            $invitation->id,
            "Đã gộp khách ID #{$validated['secondary_guest_id']} vào khách ID #{$mergedGuest->id} ({$mergedGuest->name})"
        );

        return response()->json([
            'success' => true,
            'guest' => [
                'id' => $mergedGuest->id,
                'name' => $mergedGuest->name,
                'salutation' => $mergedGuest->salutation,
                'phone' => $mergedGuest->phone,
                'group_name' => $mergedGuest->group_name,
                'table_name' => $mergedGuest->table_name,
                'expected_attendees' => $mergedGuest->expected_attendees,
            ],
            'message' => 'Đã gộp 2 bản ghi khách mời thành công!',
        ]);
    }

    /**
     * Xuất danh sách các cặp nghi vấn trùng lặp ra file CSV UTF-8 BOM.
     */
    public function exportCsv(Request $request, int|string $invitationId): StreamedResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $csvContent = $this->detectorService->exportCsv($wedding);
        $fileName = 'danh-sach-khach-trung-lap-'.date('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
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
