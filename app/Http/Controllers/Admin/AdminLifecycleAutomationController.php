<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LifecycleAutomationRule;
use App\Services\WeddingLifecycleAutomationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLifecycleAutomationController extends Controller
{
    public function __construct(
        protected WeddingLifecycleAutomationService $automationService
    ) {}

    /**
     * Màn hình chỉ huy Chiến Dịch Tự Động Hóa Vòng Đời Cưới (Mục 149 PRD V3).
     */
    public function index(Request $request): View
    {
        $currentTab = $request->query('tab', 'rules');
        if (! in_array($currentTab, ['rules', 'scanner', 'logs'], true)) {
            $currentTab = 'rules';
        }

        $kpis = $this->automationService->getAutomationKpis();
        $rules = $this->automationService->getRules();
        $logs = $this->automationService->getRecentLogs(30);
        $scanResult = session('scan_result');

        return view('admin.pages.campaigns.automation.index', compact(
            'currentTab',
            'kpis',
            'rules',
            'logs',
            'scanResult'
        ));
    }

    /**
     * Cập nhật kịch bản tự động hóa.
     */
    public function update(Request $request, LifecycleAutomationRule $rule): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'channel' => ['required', 'string', 'in:email,zalo_zns,sms_brandname,in_app'],
            'send_time' => ['required', 'string', 'max:10'],
            'offset_days' => ['required', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Vui lòng nhập tên kịch bản.',
            'channel.required' => 'Vui lòng chọn kênh phát tin.',
            'send_time.required' => 'Vui lòng thiết lập giờ gửi trong ngày.',
            'offset_days.required' => 'Vui lòng thiết lập số ngày lệch offset.',
        ]);

        try {
            $this->automationService->updateRule($rule, $request->all(), $request->user());

            return redirect()
                ->route('admin.campaigns.automation.index', ['tab' => 'rules'])
                ->with('success', "Đã cập nhật kịch bản \"{$rule->name}\" thành công.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi cập nhật kịch bản: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Bật / Tắt kích hoạt kịch bản.
     */
    public function toggle(Request $request, LifecycleAutomationRule $rule): RedirectResponse
    {
        try {
            $this->automationService->toggleRule($rule, $request->user());
            $status = $rule->is_active ? 'kích hoạt' : 'tạm dừng';

            return redirect()
                ->route('admin.campaigns.automation.index', ['tab' => 'rules'])
                ->with('success', "Đã {$status} kịch bản \"{$rule->name}\".");
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi đổi trạng thái: '.$e->getMessage());
        }
    }

    /**
     * Quét và thực thi tự động hóa vòng đời (Dry-Run hoặc Phát tin thật).
     */
    public function scan(Request $request): JsonResponse|RedirectResponse
    {
        $dryRun = $request->boolean('dry_run', true);
        $ruleId = $request->filled('rule_id') ? (int) $request->input('rule_id') : null;

        try {
            $result = $this->automationService->scanAndExecute(
                dryRun: $dryRun,
                ruleId: $ruleId,
                admin: $request->user()
            );

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            $modeText = $dryRun ? 'Quét thử nghiệm (Dry-Run)' : 'Thực thi phát tin tự động';
            $msg = "{$modeText} hoàn tất: Tìm thấy {$result['matched_weddings']} tiệc cưới khớp mốc, {$result['dispatched']} lượt xử lý, {$result['skipped']} bỏ qua do chống trùng lặp.";

            return redirect()
                ->route('admin.campaigns.automation.index', ['tab' => 'scanner'])
                ->with('scan_result', $result)
                ->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()
                ->route('admin.campaigns.automation.index', ['tab' => 'scanner'])
                ->with('error', 'Lỗi khi quét tự động: '.$e->getMessage());
        }
    }
}
