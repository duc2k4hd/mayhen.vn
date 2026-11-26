<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ToolInsightSnapshot;
use App\Models\ToolRetentionArchive;
use App\Models\UserActivityLog;
use App\Models\UserCookieConsent;
use App\Services\Admin\ToolInsightCollectorService;
use App\Services\Admin\ToolsGovernanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminToolsHubController extends Controller
{
    public function __construct(
        protected ToolsGovernanceService $governance,
        protected ToolInsightCollectorService $insights
    ) {}

    public function index(Request $request): View
    {
        $activeTab = (string) $request->query('tab', 'records');
        $activeTool = (string) $request->query('tool', 'wedding_journey');
        if (! isset(ToolsGovernanceService::TOOL_REGISTRY[$activeTool])) {
            $activeTool = 'wedding_journey';
        }

        $overview = $this->governance->getOverviewStats();

        $recordsData = $this->governance->listToolRecords($activeTool, [
            'q' => $request->query('q'),
            'user_id' => $request->query('user_id'),
            'expired_only' => $request->boolean('expired_only'),
        ], 20);

        $activities = UserActivityLog::query()
            ->with('user:id,name,email')
            ->when($request->query('activity_tool'), fn ($q, $t) => $q->where('tool_key', $t))
            ->when($request->query('user_id'), fn ($q, $uid) => $q->where('user_id', (int) $uid))
            ->latest('id')
            ->paginate(25, ['*'], 'act_page');

        $consents = UserCookieConsent::query()
            ->with('user:id,name,email')
            ->latest('id')
            ->paginate(25, ['*'], 'con_page');

        $archives = ToolRetentionArchive::query()
            ->when($request->query('archive_tool'), fn ($q, $t) => $q->where('tool_key', $t))
            ->latest('id')
            ->paginate(25, ['*'], 'arc_page');

        $insightTool = $request->query('insight_tool') ? (string) $request->query('insight_tool') : null;
        $insightSummary = $this->insights->getDemandSummary($insightTool);
        $insightRows = ToolInsightSnapshot::query()
            ->when($insightTool, fn ($q) => $q->where('tool_key', $insightTool))
            ->latest('id')
            ->paginate(25, ['*'], 'ins_page');

        return view('admin.pages.tools_hub.index', [
            'activeTab' => $activeTab,
            'activeTool' => $activeTool,
            'overview' => $overview,
            'recordsData' => $recordsData,
            'activities' => $activities,
            'consents' => $consents,
            'archives' => $archives,
            'insightTool' => $insightTool,
            'insightSummary' => $insightSummary,
            'insightRows' => $insightRows,
            'toolRegistry' => ToolsGovernanceService::TOOL_REGISTRY,
        ]);
    }

    public function revokeShare(string $toolKey, int $id): RedirectResponse
    {
        $ok = $this->governance->revokeShareLink($toolKey, $id);

        return back()->with(
            $ok ? 'success' : 'error',
            $ok ? "Đã thu hồi link chia sẻ công khai của bản ghi #{$id} ({$toolKey})." : 'Không thể thu hồi hoặc bản ghi không có link chia sẻ.'
        );
    }

    public function runRetentionPrune(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'months' => ['nullable', 'integer', 'min:1', 'max:36'],
            'dry_run' => ['nullable', 'boolean'],
            'tool' => ['nullable', 'string', 'max:50'],
        ]);

        $months = (int) ($validated['months'] ?? ToolsGovernanceService::DEFAULT_RETENTION_MONTHS);
        $dryRun = (bool) ($validated['dry_run'] ?? true);
        $tool = ! empty($validated['tool']) ? (string) $validated['tool'] : null;

        $res = $this->governance->archiveAndPruneExpired($months, $dryRun, $tool);

        $msg = $dryRun
            ? "[CHẠY THỬ DRY-RUN] Phát hiện {$res['total_candidates']} bản ghi/link quá hạn {$months} tháng sẵn sàng lưu phân tích & dọn dẹp."
            : "Đã hoàn tất Cron Lưu trữ {$months} tháng: lưu bản tóm tắt phân tích & xóa {$res['total_archived_and_pruned']}/{$res['total_candidates']} bản ghi quá hạn.";

        return redirect()->route('admin.tools_hub.index', ['tab' => 'retention'])->with('success', $msg);
    }

    public function exportCsv(string $scope): Response
    {
        if (! in_array($scope, ['archives', 'activities', 'consents'], true)) {
            $scope = 'archives';
        }
        $csv = $this->governance->exportCsv($scope);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mayhen-tools-'.$scope.'-'.now()->format('Ymd-His').'.csv"',
        ]);
    }

    public function exportAiJsonl(?string $toolKey = null): Response
    {
        $cleanKey = $toolKey && $toolKey !== 'all' ? preg_replace('/[^a-z0-9_]/', '', $toolKey) : null;
        $jsonl = $this->insights->exportJsonl($cleanKey ?: null);
        $suffix = $cleanKey ?: 'all';

        return response($jsonl, 200, [
            'Content-Type' => 'application/x-ndjson; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mayhen-ai-insights-'.$suffix.'-'.now()->format('Ymd-His').'.jsonl"',
        ]);
    }
}
