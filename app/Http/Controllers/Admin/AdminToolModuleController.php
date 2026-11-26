<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ToolsGovernanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Controller quản trị độc lập cho từng phân hệ trong 9 công cụ:
 * /admin/family-tree, /admin/iching, /admin/tuvi, /admin/numerology,
 * /admin/tarot, /admin/calendar, /admin/bazi, /admin/marriage-compatibility, /admin/wedding-journey
 */
class AdminToolModuleController extends Controller
{
    public function __construct(
        protected ToolsGovernanceService $governance
    ) {}

    public function index(Request $request, string $moduleSlug): View
    {
        $toolKey = $this->resolveToolKey($moduleSlug);
        $stats = $this->governance->getSingleToolStats($toolKey);
        $recordsData = $this->governance->listToolRecords($toolKey, [
            'q' => $request->query('q'),
            'user_id' => $request->query('user_id'),
            'expired_only' => $request->boolean('expired_only'),
        ], 25);

        return view('admin.pages.tools_hub.module_index', [
            'moduleSlug' => $moduleSlug,
            'toolKey' => $toolKey,
            'stats' => $stats,
            'recordsData' => $recordsData,
        ]);
    }

    public function show(string $moduleSlug, int $id): View
    {
        $toolKey = $this->resolveToolKey($moduleSlug);
        $detail = $this->governance->inspectRecord($toolKey, $id);
        if ($detail === null) {
            abort(404, 'Không tìm thấy bản ghi công cụ này.');
        }

        return view('admin.pages.tools_hub.module_show', [
            'moduleSlug' => $moduleSlug,
            'toolKey' => $toolKey,
            'detail' => $detail,
        ]);
    }

    public function revokeShare(string $moduleSlug, int $id): RedirectResponse
    {
        $toolKey = $this->resolveToolKey($moduleSlug);
        $ok = $this->governance->revokeShareLink($toolKey, $id);

        return back()->with(
            $ok ? 'success' : 'error',
            $ok ? "Đã thu hồi link chia sẻ công khai của bản ghi #{$id}." : 'Bản ghi không có link chia sẻ hoặc đã thu hồi.'
        );
    }

    public function destroy(string $moduleSlug, int $id): RedirectResponse
    {
        $toolKey = $this->resolveToolKey($moduleSlug);
        $ok = $this->governance->archiveAndDeleteSingle($toolKey, $id);

        return redirect()->route('admin.tool_module.index', ['moduleSlug' => $moduleSlug])
            ->with(
                $ok ? 'success' : 'error',
                $ok ? "Đã lưu bản tóm tắt phân tích & xóa bản ghi #{$id}." : 'Không tìm thấy bản ghi để xóa.'
            );
    }

    public function exportCsv(string $moduleSlug): Response
    {
        $toolKey = $this->resolveToolKey($moduleSlug);
        $csv = $this->governance->exportToolCsv($toolKey);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mayhen-'.$moduleSlug.'-'.now()->format('Ymd-His').'.csv"',
        ]);
    }

    protected function resolveToolKey(string $moduleSlug): string
    {
        $toolKey = ToolsGovernanceService::MODULE_URL_MAP[$moduleSlug] ?? null;
        if ($toolKey === null) {
            abort(404);
        }

        return $toolKey;
    }
}
