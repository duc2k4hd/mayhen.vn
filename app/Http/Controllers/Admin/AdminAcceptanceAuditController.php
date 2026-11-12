<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAcceptanceAuditService;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminAcceptanceAuditController extends Controller
{
    /**
     * Màn hình Trạm Chỉ Huy Kiểm Định Chất Lượng Toàn Diện (Section 62 & 63 PRD V3).
     */
    public function index(Request $request): View
    {
        $audit = AdminAcceptanceAuditService::runFullAudit();

        return view('admin.pages.system.acceptance', compact('audit'));
    }

    /**
     * Kích hoạt chạy lại kiểm tra toàn diện và ghi nhận Audit Log.
     */
    public function triggerAudit(Request $request): JsonResponse|RedirectResponse
    {
        $audit = AdminAcceptanceAuditService::runFullAudit();

        AuditLogger::log(
            'SYSTEM_ACCEPTANCE_AUDIT_RUN',
            'System',
            '0',
            "Quản trị viên kích hoạt chạy kiểm định nghiệm thu hệ thống (Điểm: {$audit['compliance_score']}%)",
            [
                'compliance_score' => $audit['compliance_score'],
                'grade' => $audit['grade'],
                'passed_count' => $audit['passed_count'],
                'warning_count' => $audit['warning_count'],
                'critical_count' => $audit['critical_count'],
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã hoàn thành kiểm định chất lượng toàn diện.',
                'data' => $audit,
            ]);
        }

        return redirect()->route('admin.system.acceptance.index')
            ->with('success', "Đã hoàn thành kiểm tra chất lượng hệ thống! Điểm tuân thủ: {$audit['compliance_score']}% ({$audit['grade']})");
    }

    /**
     * Xuất Báo Cáo Kiểm Định Chất Lượng định dạng CSV UTF-8 BOM.
     */
    public function exportCsv(): Response
    {
        $csvData = AdminAcceptanceAuditService::exportAuditCsv();
        $filename = 'mayhen-bao-cao-kiem-dinh-nghiem-thu-'.now()->format('Ymd-His').'.csv';

        AuditLogger::log(
            'SYSTEM_ACCEPTANCE_AUDIT_EXPORTED',
            'System',
            '0',
            'Quản trị viên xuất báo cáo kiểm định chất lượng toàn diện ra tệp CSV'
        );

        return response($csvData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
