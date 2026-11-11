<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GuestPhoto;
use App\Models\WeddingWish;
use App\Services\AbuseReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentReportController extends Controller
{
    public function __construct(
        protected AbuseReportService $abuseReportService
    ) {}

    /**
     * Khách mời hoặc Chủ thiệp báo cáo lời chúc vi phạm (Mục 17.3 PRD V3).
     */
    public function reportWish(Request $request, int $wishId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|in:spam,harassment,inappropriate,privacy_violation,other',
            'details' => 'nullable|string|max:500',
            'reporter_name' => 'nullable|string|max:100',
            'reporter_phone' => 'nullable|string|max:20',
        ], [
            'reason.required' => 'Vui lòng chọn lý do báo cáo vi phạm.',
            'reason.in' => 'Lý do báo cáo không hợp lệ.',
        ]);

        $wish = WeddingWish::findOrFail($wishId);

        $report = $this->abuseReportService->createReport($wish, $validated, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã gửi báo cáo. Hệ thống và cô dâu chú rể sẽ xem xét xử lý nhanh chóng.',
            'report_id' => $report->id,
            'is_hidden' => ! (bool) $wish->fresh()->is_approved,
        ]);
    }

    /**
     * Báo cáo ảnh do khách tải lên có dấu hiệu phản cảm hoặc vi phạm quyền riêng tư.
     */
    public function reportPhoto(Request $request, int $photoId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|in:spam,harassment,inappropriate,privacy_violation,other',
            'details' => 'nullable|string|max:500',
            'reporter_name' => 'nullable|string|max:100',
            'reporter_phone' => 'nullable|string|max:20',
        ], [
            'reason.required' => 'Vui lòng chọn lý do báo cáo ảnh vi phạm.',
            'reason.in' => 'Lý do báo cáo không hợp lệ.',
        ]);

        $photo = GuestPhoto::findOrFail($photoId);

        $report = $this->abuseReportService->createReport($photo, $validated, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã gửi báo cáo. Ảnh vi phạm sẽ được kiểm duyệt kịp thời.',
            'report_id' => $report->id,
            'is_hidden' => ! (bool) $photo->fresh()->is_approved,
        ]);
    }
}
