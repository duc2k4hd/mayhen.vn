<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Services\AiQualityAndCostObservatoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AiObservatoryController extends Controller
{
    /**
     * Bảng điều khiển Đài Quan Sát Chi Phí & Chất Lượng AI.
     */
    public function index(Request $request): View
    {
        $days = $request->integer('days', 30);
        if (! in_array($days, [7, 30, 90], true)) {
            $days = 30;
        }

        $metrics = AiQualityAndCostObservatoryService::getObservatoryMetrics($days);

        $query = AiUsageLog::with('user')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        if ($request->filled('feature_name')) {
            $query->where('feature_name', $request->feature_name);
        }

        $logs = $query->paginate(15)->withQueryString();

        return view('admin.pages.ai_observatory.index', compact('metrics', 'logs', 'days'));
    }

    /**
     * Chạy thử nghiệm mô phỏng và kiểm duyệt Brand Safety trong Sandbox Admin.
     */
    public function simulate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'feature_name' => ['required', 'string', 'in:story_generator,rsvp_wording,concierge_qa'],
            'provider' => ['required', 'string', 'in:gemini-1.5-flash,gemini-1.5-pro,heuristic_fallback'],
            'prompt' => ['required', 'string', 'min:3', 'max:1000'],
            'force_fallback' => ['nullable', 'boolean'],
        ], [
            'prompt.required' => 'Vui lòng nhập nội dung câu lệnh kiểm thử (Prompt).',
        ]);

        $forceFallback = (bool) $request->boolean('force_fallback') || ($validated['provider'] === 'heuristic_fallback');

        $result = AiQualityAndCostObservatoryService::simulateAiGeneration(
            featureName: $validated['feature_name'],
            prompt: $validated['prompt'],
            user: Auth::user(),
            forceFallback: $forceFallback,
            provider: $validated['provider']
        );

        return back()->with('simulation_result', $result)->with('simulated_prompt', $validated['prompt']);
    }

    /**
     * Cập nhật phản hồi hành vi của người dùng đối với nội dung AI tạo ra.
     */
    public function recordAction(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:accepted,edited,rejected'],
        ]);

        $log = AiUsageLog::findOrFail($id);
        $log->update(['user_action' => $request->action]);

        return back()->with('success', "Đã ghi nhận phản hồi hành vi: \"{$request->action}\".");
    }
}
