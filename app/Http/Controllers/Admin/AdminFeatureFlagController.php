<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemFeatureFlag;
use App\Services\FeatureFlagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminFeatureFlagController extends Controller
{
    public function __construct(
        protected FeatureFlagService $flagService
    ) {}

    /**
     * Hiển thị bảng điều khiển danh sách Cờ Tính Năng & An Toàn Phát Hành
     */
    public function index(Request $request): View
    {
        $filters = [
            'category' => $request->input('category', 'all'),
            'status' => $request->input('status', 'all'),
            'search' => $request->input('search', ''),
        ];

        $flags = $this->flagService->getAllFlags($filters);
        $metrics = $this->flagService->getMetrics();

        return view('admin.pages.settings.flags.index', compact('flags', 'metrics', 'filters'));
    }

    /**
     * Tạo mới một cờ tính năng
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|alpha_dash|max:50|unique:system_feature_flags,key',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'category' => 'required|in:core,ai,commerce,collaboration,safety',
            'is_enabled' => 'nullable|boolean',
            'rollout_percentage' => 'required|integer|min:0|max:100',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'string|in:all,admin,partner_studio,couple,customer',
        ], [
            'key.required' => 'Mã định danh cờ là bắt buộc.',
            'key.alpha_dash' => 'Mã cờ chỉ được chứa chữ cái, số, dấu gạch nối và gạch dưới.',
            'key.unique' => 'Mã định danh cờ này đã tồn tại trên hệ thống.',
            'name.required' => 'Tên hiển thị tính năng là bắt buộc.',
            'category.required' => 'Danh mục tính năng là bắt buộc.',
            'rollout_percentage.required' => 'Tỷ lệ phát hành là bắt buộc.',
            'rollout_percentage.min' => 'Tỷ lệ phát hành tối thiểu là 0%.',
            'rollout_percentage.max' => 'Tỷ lệ phát hành tối đa là 100%.',
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');

        $this->flagService->createFlag($validated, Auth::id());

        return redirect()->route('admin.settings.flags.index')
            ->with('success', "Đã tạo mới cờ tính năng [{$validated['name']}] thành công.");
    }

    /**
     * Bật / Tắt nhanh trạng thái cờ tính năng
     */
    public function toggle(Request $request, string $key): RedirectResponse|JsonResponse
    {
        $flag = SystemFeatureFlag::where('key', $key)->firstOrFail();
        $newState = ! $flag->is_enabled;

        $updatedFlag = $this->flagService->toggle($key, $newState, Auth::id());

        $message = $newState
            ? "Đã bật tính năng [{$updatedFlag->name}] thành công."
            : "Đã tạm dừng tính năng [{$updatedFlag->name}].";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_enabled' => $updatedFlag->is_enabled,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Cập nhật tỷ lệ phát hành an toàn (Phased Rollout %) và vai trò
     */
    public function updateRollout(Request $request, string $key): RedirectResponse
    {
        $validated = $request->validate([
            'rollout_percentage' => 'required|integer|min:0|max:100',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'string|in:all,admin,partner_studio,couple,customer',
        ], [
            'rollout_percentage.required' => 'Vui lòng nhập tỷ lệ phát hành.',
            'rollout_percentage.min' => 'Tỷ lệ phát hành tối thiểu là 0%.',
            'rollout_percentage.max' => 'Tỷ lệ phát hành tối đa là 100%.',
        ]);

        $targetRoles = $validated['target_roles'] ?? null;
        $updatedFlag = $this->flagService->updateRollout($key, (int) $validated['rollout_percentage'], $targetRoles, Auth::id());

        return back()->with('success', "Đã cập nhật tỷ lệ phát hành cho tính năng [{$updatedFlag->name}] thành {$updatedFlag->rollout_percentage}%.");
    }

    /**
     * Kích hoạt hoặc hủy Cầu Dao Khẩn Cấp (Kill Switch)
     */
    public function toggleKillSwitch(Request $request, string $key): RedirectResponse
    {
        $flag = SystemFeatureFlag::where('key', $key)->firstOrFail();

        if ($flag->kill_switch_active) {
            // Hủy kích hoạt cầu dao khẩn cấp
            $this->flagService->deactivateKillSwitch($key, Auth::id());

            return back()->with('success', "Đã gỡ bỏ khóa khẩn cấp cho tính năng [{$flag->name}]. Tính năng đã khôi phục trạng thái vận hành.");
        }

        // Kích hoạt cầu dao khẩn cấp, yêu cầu lý do
        $validated = $request->validate([
            'kill_switch_reason' => 'required|string|min:5|max:255',
        ], [
            'kill_switch_reason.required' => 'Vui lòng nhập lý do ngắt cầu dao khẩn cấp.',
            'kill_switch_reason.min' => 'Lý do ngắt khẩn cấp phải có ít nhất 5 ký tự để phục vụ hậu kiểm.',
        ]);

        $this->flagService->activateKillSwitch($key, $validated['kill_switch_reason'], Auth::id());

        return back()->with('warning', "CẢNH BÁO: Đã kích hoạt Cầu Dao Khẩn Cấp ngắt tính năng [{$flag->name}] ngay lập tức trên toàn hệ thống!");
    }

    /**
     * Xóa cờ tính năng (Bảo vệ cờ Core không cho xóa)
     */
    public function destroy(string $key): RedirectResponse
    {
        $flag = SystemFeatureFlag::where('key', $key)->firstOrFail();

        if ($flag->category === 'core') {
            return back()->with('error', 'Không thể xóa cờ tính năng thuộc danh mục [Hệ thống cốt lõi] để bảo đảm an toàn vận hành.');
        }

        $flagName = $flag->name;
        $this->flagService->deleteFlag($key, Auth::id());

        return back()->with('success', "Đã xóa cờ tính năng [{$flagName}] khỏi hệ thống.");
    }
}
