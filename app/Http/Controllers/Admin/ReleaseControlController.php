<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemFeatureFlag;
use App\Services\AuditLogger;
use App\Services\ReleaseAndRollbackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReleaseControlController extends Controller
{
    /**
     * Bảng điều khiển Kiểm Soát Phát Hành & Khôi Phục (Release & Rollback Cockpit).
     */
    public function index(): View
    {
        ReleaseAndRollbackService::seedDefaultFeatureFlags();

        $flags = SystemFeatureFlag::with('lastToggledBy')
            ->orderBy('category')
            ->get();

        $preflight = ReleaseAndRollbackService::runPreflightChecklist();

        $safeModeFlag = $flags->firstWhere('key', 'high_concurrency_safe_mode');
        $isSafeModeActive = $safeModeFlag && $safeModeFlag->is_enabled && ! $safeModeFlag->kill_switch_active;

        return view('admin.pages.release.index', [
            'flags' => $flags,
            'preflight' => $preflight,
            'isSafeModeActive' => $isSafeModeActive,
        ]);
    }

    /**
     * Bật/tắt hoặc điều chỉnh tỷ lệ rollout của cờ tính năng.
     */
    public function toggleFlag(Request $request, int $id): RedirectResponse
    {
        $flag = SystemFeatureFlag::findOrFail($id);

        $validated = $request->validate([
            'is_enabled' => ['required', 'boolean'],
            'rollout_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $flag->update([
            'is_enabled' => (bool) $validated['is_enabled'],
            'rollout_percentage' => (int) $validated['rollout_percentage'],
            'last_toggled_by' => Auth::id(),
            'last_toggled_at' => now(),
        ]);

        ReleaseAndRollbackService::flushFlagCache();

        AuditLogger::log(
            action: 'release.feature_flag_toggled',
            targetType: 'system_feature_flag',
            targetId: $flag->id,
            description: "Cập nhật cờ tính năng {$flag->key}: Bật = ".($flag->is_enabled ? 'Có' : 'Không').", Rollout = {$flag->rollout_percentage}%",
            details: [
                'key' => $flag->key,
                'is_enabled' => $flag->is_enabled,
                'rollout_percentage' => $flag->rollout_percentage,
                'admin_id' => Auth::id(),
            ]
        );

        return back()->with('success', "Đã cập nhật trạng thái cờ tính năng \"{$flag->name}\" thành công.");
    }

    /**
     * Kích hoạt Kill Switch khẩn cấp cho một tính năng.
     */
    public function triggerKillSwitch(Request $request, int $id): RedirectResponse
    {
        $flag = SystemFeatureFlag::findOrFail($id);

        $validated = $request->validate([
            'kill_switch_reason' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'kill_switch_reason.required' => 'Vui lòng nhập lý do ngắt tính năng khẩn cấp để lưu vết kiểm toán.',
            'kill_switch_reason.min' => 'Lý do ngắt tối thiểu 5 ký tự.',
        ]);

        $flag->update([
            'kill_switch_active' => true,
            'kill_switch_reason' => $validated['kill_switch_reason'],
            'last_toggled_by' => Auth::id(),
            'last_toggled_at' => now(),
        ]);

        ReleaseAndRollbackService::flushFlagCache();

        AuditLogger::log(
            action: 'release.kill_switch_triggered',
            targetType: 'system_feature_flag',
            targetId: $flag->id,
            description: "NGẮT KHẨN CẤP (Kill Switch) tính năng {$flag->key}: {$validated['kill_switch_reason']}",
            details: [
                'key' => $flag->key,
                'reason' => $validated['kill_switch_reason'],
                'admin_id' => Auth::id(),
            ]
        );

        return back()->with('success', "Đã ngắt khẩn cấp tính năng \"{$flag->name}\". Toàn bộ người dùng sẽ không thể truy cập tính năng này.");
    }

    /**
     * Mở lại tính năng sau khi khắc phục xong sự cố Kill Switch.
     */
    public function resetKillSwitch(int $id): RedirectResponse
    {
        $flag = SystemFeatureFlag::findOrFail($id);

        $flag->update([
            'kill_switch_active' => false,
            'kill_switch_reason' => null,
            'last_toggled_by' => Auth::id(),
            'last_toggled_at' => now(),
        ]);

        ReleaseAndRollbackService::flushFlagCache();

        AuditLogger::log(
            action: 'release.kill_switch_reset',
            targetType: 'system_feature_flag',
            targetId: $flag->id,
            description: "Gỡ bỏ Kill Switch cho tính năng {$flag->key}, khôi phục theo cấu hình cờ.",
            details: ['key' => $flag->key, 'admin_id' => Auth::id()]
        );

        return back()->with('success', "Đã gỡ bỏ Kill Switch cho tính năng \"{$flag->name}\".");
    }

    /**
     * Bật / Tắt Chế Độ Phòng Vệ Khẩn Cấp Toàn Hệ Thống (Yêu cầu Super Admin).
     */
    public function toggleSafeMode(Request $request): RedirectResponse
    {
        $action = $request->input('action'); // 'activate' or 'deactivate'
        $admin = Auth::user();

        if ($action === 'activate') {
            $reason = trim((string) $request->input('reason')) ?: 'Lượng truy cập tăng cao đột biến trong ngày cưới';
            ReleaseAndRollbackService::activateEmergencySafeMode($admin, $reason);

            return back()->with('success', 'ĐÃ KÍCH HOẠT CHẾ ĐỘ PHÒNG VỆ KHẨN CẤP TOÀN HỆ THỐNG. Các tác vụ nặng đã được tạm khóa.');
        }

        ReleaseAndRollbackService::deactivateEmergencySafeMode($admin);

        return back()->with('success', 'Đã hủy Chế Độ Phòng Vệ Khẩn Cấp. Hệ thống trở lại vận hành bình thường.');
    }
}
