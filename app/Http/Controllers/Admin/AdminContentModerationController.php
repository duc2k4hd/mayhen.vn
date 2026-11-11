<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentReport;
use App\Services\AbuseReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminContentModerationController extends Controller
{
    public function __construct(
        protected AbuseReportService $abuseReportService
    ) {}

    /**
     * Màn hình Trung tâm kiểm duyệt nội dung & xử lý báo cáo vi phạm (Mục 17.3 PRD V3).
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', ContentReport::STATUS_PENDING);
        $reason = $request->query('reason');

        $query = ContentReport::with(['wedding.user', 'resolver'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($reason) {
            $query->where('reason', $reason);
        }

        $reports = $query->paginate(15)->withQueryString();
        $stats = $this->abuseReportService->getStatistics();

        return view('admin.pages.moderation.index', compact('reports', 'stats', 'status', 'reason'));
    }

    /**
     * Xử lý một hồ sơ báo cáo vi phạm (Ẩn / Xóa / Bác bỏ).
     */
    public function resolve(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|in:hide,delete,dismiss',
            'notes' => 'nullable|string|max:500',
        ]);

        $report = ContentReport::findOrFail($id);

        $this->abuseReportService->resolveReport(
            $report,
            $validated['action'],
            auth()->user(),
            $validated['notes'] ?? null
        );

        $actionText = match ($validated['action']) {
            'hide' => 'ẩn khỏi thiệp cưới',
            'delete' => 'xóa vĩnh viễn',
            'dismiss' => 'bác bỏ báo cáo và khôi phục hiển thị',
            default => 'xử lý',
        };

        return back()->with('success', "Đã {$actionText} nội dung của hồ sơ vi phạm #{$report->id} thành công.");
    }
}

