<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsReportService $analyticsService
    ) {}

    /**
     * Bảng điều khiển Trung Tâm Phân Tích & Báo Cáo Toàn Diện (Mục 19 & 49 PRD V3).
     */
    public function index(Request $request): View
    {
        $activeTab = $request->query('tab', 'revenue');
        $range = $request->query('range', '30_days');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $rangeData = $this->analyticsService->parseDateRange($range, $startDate, $endDate);
        $start = $rangeData['start'];
        $end = $rangeData['end'];
        $prevStart = $rangeData['prev_start'];
        $prevEnd = $rangeData['prev_end'];

        $revenueData = null;
        $funnelData = null;
        $templateData = null;
        $rsvpData = null;
        $operationsData = null;

        if ($activeTab === 'revenue') {
            $revenueData = $this->analyticsService->getRevenueReport($start, $end, $prevStart, $prevEnd);
        } elseif ($activeTab === 'funnel') {
            $funnelData = $this->analyticsService->getAcquisitionFunnel($start, $end);
        } elseif ($activeTab === 'templates') {
            $templateData = $this->analyticsService->getTemplatePerformance($start, $end);
        } elseif ($activeTab === 'rsvp') {
            $rsvpData = $this->analyticsService->getRsvpAnalytics($start, $end);
        } elseif ($activeTab === 'operations') {
            $operationsData = $this->analyticsService->getOperationsMetrics($start, $end);
        } else {
            $revenueData = $this->analyticsService->getRevenueReport($start, $end, $prevStart, $prevEnd);
        }

        return view('admin.pages.analytics.index', compact(
            'activeTab',
            'range',
            'rangeData',
            'revenueData',
            'funnelData',
            'templateData',
            'rsvpData',
            'operationsData'
        ));
    }

    /**
     * Xuất dữ liệu báo cáo dạng CSV chuẩn UTF-8 BOM.
     */
    public function export(Request $request): StreamedResponse
    {
        $tab = $request->query('tab', 'revenue');
        $range = $request->query('range', '30_days');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $rangeData = $this->analyticsService->parseDateRange($range, $startDate, $endDate);
        $exportData = $this->analyticsService->getExportData($tab, $rangeData['start'], $rangeData['end'], $request->user());

        $filename = "mayhen-report-{$tab}-".date('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($exportData) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM cho Microsoft Excel

            if (! empty($exportData)) {
                fputcsv($handle, array_keys($exportData[0]));
                foreach ($exportData as $row) {
                    fputcsv($handle, array_values($row));
                }
            } else {
                fputcsv($handle, ['Thông Báo', 'Không có dữ liệu trong khoảng thời gian đã chọn']);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
