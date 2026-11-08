<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\PlanFitAdvisorService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanAdvisorController extends Controller
{
    /**
     * Màn hình Khảo Sát & Tư Vấn Chọn Gói Thiệp Cưới Phù Hợp (TASK-VAL-25).
     */
    public function index(Request $request, PlanFitAdvisorService $service): View
    {
        $plans = Plan::where('is_active', true)->orderBy('price')->get();
        $initialAnalysis = $service->analyzeNeeds([
            'guest_scale' => 'medium',
            'events_count' => 'dual',
            'operations_need' => 'advanced',
            'memory_retention' => 'long',
        ]);

        return view('client.pages.plan-advisor', [
            'plans' => $plans,
            'initialAnalysis' => $initialAnalysis,
        ]);
    }

    /**
     * API Phân tích câu trả lời và đề xuất gói dịch vụ tối ưu.
     */
    public function analyze(Request $request, PlanFitAdvisorService $service): JsonResponse
    {
        $validated = $request->validate([
            'guest_scale' => ['required', 'string', 'in:small,medium,large'],
            'events_count' => ['required', 'string', 'in:single,dual,multi'],
            'operations_need' => ['required', 'string', 'in:basic,advanced'],
            'memory_retention' => ['required', 'string', 'in:short,long'],
        ]);

        $analysis = $service->analyzeNeeds($validated);

        return response()->json([
            'success' => true,
            'data' => $analysis,
            'analysis' => $analysis,
        ]);
    }
}
