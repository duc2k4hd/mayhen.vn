<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaperInvitationScan;
use App\Services\AdminPaperScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminPaperScanController extends Controller
{
    public function __construct(
        protected AdminPaperScanService $paperScanService
    ) {}

    /**
     * Bảng điều khiển Đài Giám Sát Quét Thiệp Giấy AI OCR (Mục 18, 23 PRD V3 & ADM-OCR-01).
     */
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->input('q', ''),
            'status' => $request->input('status', 'all'),
            'confidence' => $request->input('confidence', ''),
        ];

        $data = $this->paperScanService->getScansOverview($filters);

        return view('admin.pages.paper_scans.index', [
            'stats' => $data['stats'],
            'scans' => $data['scans'],
            'filters' => $filters,
        ]);
    }

    /**
     * Xem chi tiết một lượt quét thiệp giấy (hỗ trợ JSON API cho Modal).
     */
    public function show(Request $request, int $id): JsonResponse|View
    {
        $scan = PaperInvitationScan::with(['user', 'wedding', 'invitation'])->findOrFail($id);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'scan' => [
                    'id' => $scan->id,
                    'user_name' => $scan->user?->name,
                    'user_email' => $scan->user?->email,
                    'image_url' => $scan->image_url,
                    'confidence_score' => $scan->confidence_score,
                    'confidence_percent' => round($scan->confidence_score * 100),
                    'status' => $scan->status,
                    'ai_model' => $scan->ai_model,
                    'raw_extracted_text' => $scan->raw_extracted_text,
                    'parsed_data' => $scan->parsed_data,
                    'verified_data' => $scan->verified_data,
                    'wedding_title' => $scan->wedding?->title,
                    'created_at' => $scan->created_at?->format('d/m/Y H:i:s'),
                    'applied_at' => $scan->applied_at?->format('d/m/Y H:i:s'),
                ],
            ]);
        }

        return view('admin.pages.paper_scans.show', compact('scan'));
    }

    /**
     * Cập nhật dữ liệu đã hiệu đính cho lượt quét.
     */
    public function updateVerified(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $scan = PaperInvitationScan::findOrFail($id);

        $validated = $request->validate([
            'verified_data' => 'required|array',
            'verified_data.groom_name' => 'nullable|string|max:100',
            'verified_data.bride_name' => 'nullable|string|max:100',
            'verified_data.wedding_date' => 'nullable|date',
            'verified_data.venue_name' => 'nullable|string|max:255',
            'verified_data.venue_address' => 'nullable|string|max:500',
        ]);

        $operator = $request->user()?->name ?? 'Admin';
        $this->paperScanService->updateVerifiedData($scan, $validated['verified_data'], $operator);

        $msg = 'Đã cập nhật dữ liệu hiệu đính cho lượt quét #'.$scan->id.' thành công.';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Áp dụng dữ liệu trích xuất vào tiệc cưới thật (Wedding).
     */
    public function apply(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $scan = PaperInvitationScan::findOrFail($id);
        $operator = $request->user()?->name ?? 'Admin';

        $wedding = $this->paperScanService->applyScanToWedding($scan, $request->input('override_data'), $operator);

        $msg = "Đã áp dụng dữ liệu quét thành công vào tiệc cưới \"{$wedding->title}\".";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'wedding_id' => $wedding->id,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Xóa lượt quét thiệp giấy.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $scan = PaperInvitationScan::findOrFail($id);
        $operator = $request->user()?->name ?? 'Admin';

        $this->paperScanService->deleteScan($scan, $operator);

        $msg = 'Đã xóa lượt quét thiệp giấy #'.$id.' thành công.';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Xuất tệp báo cáo danh sách lượt quét CSV UTF-8 BOM.
     */
    public function export(Request $request): Response
    {
        return $this->paperScanService->exportScansCsv($request->all());
    }
}
