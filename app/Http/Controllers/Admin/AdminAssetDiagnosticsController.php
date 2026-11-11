<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Services\AdminAssetDiagnosticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminAssetDiagnosticsController extends Controller
{
    public function __construct(
        protected AdminAssetDiagnosticsService $diagnosticsService
    ) {}

    /**
     * Bảng điều khiển Chẩn Đoán Lỗi Ảnh Thiệp & Phục Hồi Tài Nguyên (Mục 60 PRD V3 & ADM-AST-01).
     */
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->input('q', ''),
            'template_id' => $request->input('template_id'),
        ];

        $data = $this->diagnosticsService->getDiagnosticsOverview($filters);
        $templates = InvitationTemplate::all(['id', 'name', 'slug']);

        return view('admin.pages.assets.diagnostics', [
            'stats' => $data['stats'],
            'paginator' => $data['paginator'],
            'inspected' => $data['inspected'],
            'filters' => $filters,
            'templates' => $templates,
        ]);
    }

    /**
     * Quét và trả về kết quả chẩn đoán chi tiết của một thiệp cưới (JSON API).
     */
    public function scanSingle(int $id): JsonResponse
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($id);
        $report = $this->diagnosticsService->inspectInvitationAssets($invitation);

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Áp dụng ảnh fallback mẫu cho một trường bị lỗi.
     */
    public function applyFallback(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::findOrFail($id);
        $field = $request->input('field');
        $fallbackUrl = $request->input('fallback_url');

        if (empty($field)) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Trường dữ liệu không hợp lệ.'], 422);
            }

            return back()->with('error', 'Trường dữ liệu không hợp lệ.');
        }

        $this->diagnosticsService->applyFallbackAsset($invitation, $field, $fallbackUrl);
        $msg = "Đã áp dụng ảnh fallback an toàn cho trường {$field} thành công.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Tải và nội địa hóa một link ảnh ngoại vi (100% Local Asset).
     */
    public function localizeAsset(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::findOrFail($id);
        $field = $request->input('field');
        $externalUrl = $request->input('external_url');

        if (empty($field) || empty($externalUrl)) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Thông tin tài nguyên không đầy đủ.'], 422);
            }

            return back()->with('error', 'Thông tin tài nguyên không đầy đủ.');
        }

        $result = $this->diagnosticsService->localizeExternalAsset($invitation, $field, $externalUrl);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Sửa nhanh toàn bộ các lỗi asset của một thiệp cưới bằng fallback mẫu.
     */
    public function batchRepair(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::findOrFail($id);
        $count = $this->diagnosticsService->batchRepairInvitation($invitation);

        $msg = "Đã phục hồi nhanh {$count} tài nguyên cho thiệp \"{$invitation->title}\".";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'repaired_count' => $count,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Xuất tệp báo cáo chẩn đoán lỗi asset CSV UTF-8 BOM.
     */
    public function export(): Response
    {
        return $this->diagnosticsService->exportDiagnosticsCsv();
    }
}
