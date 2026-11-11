<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\JobCenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobMonitorController extends Controller
{
    public function __construct(
        protected JobCenterService $jobCenterService
    ) {}

    /**
     * Bảng điều khiển Trung Tâm Chỉ Huy Tác Vụ Nền & Hàng Đợi (Mục 18 & Mục 48 PRD V3).
     */
    public function index(Request $request): View
    {
        $currentTab = $request->query('tab', 'batches');
        $kpis = $this->jobCenterService->getQueueStatistics();

        // Lấy dữ liệu theo tab để tối ưu hiệu năng truy vấn
        $batches = null;
        $failedJobs = null;
        $pendingJobs = null;

        if ($currentTab === 'batches') {
            $batches = $this->jobCenterService->getBatches($request->all(), 15);
        } elseif ($currentTab === 'failed') {
            $failedJobs = $this->jobCenterService->getFailedJobs($request->all(), 15);
        } elseif ($currentTab === 'pending') {
            $pendingJobs = $this->jobCenterService->getPendingJobs($request->all(), 15);
        } else {
            $batches = $this->jobCenterService->getBatches($request->all(), 15);
        }

        return view('admin.pages.system.jobs', compact(
            'currentTab',
            'kpis',
            'batches',
            'failedJobs',
            'pendingJobs'
        ));
    }

    /**
     * Xem chi tiết một failed job với Sanitized Stack Trace (bảo vệ thông tin bí mật).
     */
    public function details(Request $request, string $uuid): JsonResponse|View
    {
        $job = $this->jobCenterService->getFailedJobDetails($uuid);

        if (! $job) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Không tìm thấy tác vụ này.'], 404);
            }
            abort(404, 'Không tìm thấy tác vụ này.');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'job' => $job,
            ]);
        }

        return view('admin.pages.system.job-details', compact('job'));
    }

    /**
     * Retry một failed job theo UUID kèm Idempotency Warning.
     */
    public function retry(Request $request, string $uuid): RedirectResponse
    {
        $success = $this->jobCenterService->retryJob($uuid, $request->user());

        if (! $success) {
            return back()->with('error', 'Không tìm thấy tác vụ này trong danh sách lỗi.');
        }

        return back()->with('success', "Đã đẩy lại tác vụ #{$uuid} vào hàng đợi xử lý.");
    }

    /**
     * Retry toàn bộ failed jobs (hoặc theo queue).
     */
    public function retryAll(Request $request): RedirectResponse
    {
        $queue = $request->input('queue');
        $count = $this->jobCenterService->retryAllFailed($request->user(), $queue);

        if ($count === 0) {
            return back()->with('info', 'Không có tác vụ lỗi nào để thử lại.');
        }

        $msg = "Đã đẩy lại {$count} tác vụ lỗi vào hàng đợi".($queue ? " ({$queue})" : '').'.';

        return back()->with('success', $msg);
    }

    /**
     * Xóa một failed job theo UUID.
     */
    public function destroy(Request $request, string $uuid): RedirectResponse
    {
        $success = $this->jobCenterService->deleteFailedJob($uuid, $request->user());

        if (! $success) {
            return back()->with('error', 'Không tìm thấy tác vụ cần xóa.');
        }

        return back()->with('success', "Đã xóa bỏ tác vụ lỗi #{$uuid} khỏi hệ thống.");
    }

    /**
     * Xóa sạch toàn bộ tác vụ lỗi (Purge).
     */
    public function purge(Request $request): RedirectResponse
    {
        $count = $this->jobCenterService->purgeFailedJobs($request->user());

        if ($count === 0) {
            return back()->with('info', 'Danh sách tác vụ lỗi hiện đang trống.');
        }

        return back()->with('success', "Đã dọn dẹp sạch toàn bộ {$count} tác vụ lỗi khỏi hệ thống.");
    }

    /**
     * Hủy một Lô công việc (Job Batch) đang hoạt động.
     */
    public function cancelBatch(Request $request, string $id): RedirectResponse
    {
        $success = $this->jobCenterService->cancelBatch($id, $request->user());

        if (! $success) {
            return back()->with('error', "Không thể hủy bỏ lô công việc #{$id} (có thể lô đã hoàn tất hoặc không tồn tại).");
        }

        return back()->with('success', "Đã ra lệnh hủy bỏ lô công việc #{$id} thành công.");
    }

    /**
     * Thử lại các job thất bại trong một batch.
     */
    public function retryBatch(Request $request, string $id): RedirectResponse
    {
        $success = $this->jobCenterService->retryBatch($id, $request->user());

        if (! $success) {
            return back()->with('error', "Không tìm thấy job thất bại nào trong lô #{$id} để thử lại.");
        }

        return back()->with('success', "Đã đẩy các tác vụ lỗi trong lô #{$id} vào hàng đợi thử lại.");
    }

    /**
     * Xuất danh sách tác vụ lỗi ra file CSV UTF-8.
     */
    public function exportErrors(Request $request): StreamedResponse
    {
        $reportData = $this->jobCenterService->getErrorReportData($request->all());
        $filename = 'mayhen-failed-jobs-'.date('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($reportData) {
            $handle = fopen('php://output', 'w');
            // Thêm UTF-8 BOM cho Excel hiển thị chuẩn tiếng Việt
            fwrite($handle, "\xEF\xBB\xBF");

            if (! empty($reportData)) {
                fputcsv($handle, array_keys($reportData[0]));
                foreach ($reportData as $row) {
                    fputcsv($handle, array_values($row));
                }
            } else {
                fputcsv($handle, ['Thông báo', 'Không có dữ liệu tác vụ lỗi']);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
