<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Services\SystemResilienceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SystemResilienceController extends Controller
{
    public function __construct(
        protected SystemResilienceService $resilienceService
    ) {}

    /**
     * Bảng điều khiển khả năng chống chịu và chịu tải hệ thống.
     */
    public function index(): View
    {
        $metrics = $this->resilienceService->getResilienceMetrics();

        // Lấy các đám cưới đang diễn ra hoặc sắp diễn ra trong 14 ngày tới
        $upcomingWeddings = Wedding::with(['user', 'guests'])
            ->whereNotNull('wedding_date')
            ->where('wedding_date', '>=', Carbon::today()->subDays(2))
            ->where('wedding_date', '<=', Carbon::today()->addDays(14))
            ->orderBy('wedding_date', 'asc')
            ->take(10)
            ->get();

        return view('admin.pages.resilience.index', [
            'metrics' => $metrics,
            'upcomingWeddings' => $upcomingWeddings,
        ]);
    }

    /**
     * Chạy giả lập bão Check-in đồng thời tại cổng tiếp tân.
     */
    public function simulateCheckIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wedding_id' => 'required|integer|exists:weddings,id',
            'concurrency' => 'nullable|integer|min:5|max:100',
        ]);

        $concurrency = (int) ($validated['concurrency'] ?? 20);
        $result = $this->resilienceService->simulateConcurrencyCheckIn($validated['wedding_id'], $concurrency);

        return response()->json($result);
    }

    /**
     * Chạy giả lập bão Webhook SePay chống Replay Attack và Double-Spending.
     */
    public function simulateWebhook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'requests_count' => 'nullable|integer|min:10|max:100',
        ]);

        $count = (int) ($validated['requests_count'] ?? 30);
        $result = $this->resilienceService->simulateWebhookStorm($count);

        return response()->json($result);
    }

    /**
     * Chạy mô phỏng đứt gãy mạng dịch vụ bên ngoài (External Dependency Chaos).
     */
    public function simulateChaos(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service' => 'required|string|in:zalo_zns,sms_gateway,ai_provider',
        ]);

        $result = $this->resilienceService->simulateExternalDependencyChaos($validated['service']);

        return response()->json($result);
    }

    /**
     * Tạo bản snapshot dữ liệu khẩn cấp có mã băm SHA-256.
     */
    public function createSnapshot(Request $request): JsonResponse
    {
        $result = $this->resilienceService->createEmergencyBackupSnapshot(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo bản snapshot khẩn cấp xác thực SHA-256 thành công!',
            'data' => $result,
        ]);
    }
}
