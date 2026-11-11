<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminSystemHealthController extends Controller
{
    public function __construct(
        protected SystemHealthService $healthService
    ) {}

    /**
     * Bảng điều khiển Giám Sát Trạng Thái Dịch Vụ & Nhật Ký Lỗi Hệ Thống (Mục 158 PRD V3).
     */
    public function index(Request $request): View
    {
        $currentTab = $request->query('tab', 'services-health');
        if (! in_array($currentTab, ['services-health', 'error-logs', 'server-metrics'], true)) {
            $currentTab = 'services-health';
        }

        $kpis = $this->healthService->getHealthKpis();
        $healthSummary = $this->healthService->getHealthSummary();

        $logs = [];
        if ($currentTab === 'error-logs') {
            $logs = $this->healthService->parseLaravelLogs(
                limit: 50,
                level: $request->query('level'),
                search: $request->query('search')
            );
        }

        // Lấy thông số môi trường PHP chi tiết cho tab metrics
        $serverMetrics = [
            'php_version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time').'s',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_file_uploads' => ini_get('max_file_uploads'),
            'extensions' => [
                'pdo_sqlite' => extension_loaded('pdo_sqlite'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'curl' => extension_loaded('curl'),
                'gd' => extension_loaded('gd'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
                'zip' => extension_loaded('zip'),
                'intl' => extension_loaded('intl'),
            ],
        ];

        return view('admin.pages.system.health.index', compact(
            'currentTab',
            'kpis',
            'healthSummary',
            'logs',
            'serverMetrics'
        ));
    }

    /**
     * Làm sạch tệp log hệ thống.
     */
    public function clearLog(Request $request): RedirectResponse
    {
        $this->healthService->clearLaravelLog($request->user());

        return redirect()->route('admin.system.health.index', ['tab' => 'error-logs'])
            ->with('success', 'Đã làm sạch tệp nhật ký lỗi hệ thống thành công.');
    }

    /**
     * Tải tệp log gốc về máy.
     */
    public function downloadLog(): BinaryFileResponse
    {
        return $this->healthService->downloadLog();
    }
}
