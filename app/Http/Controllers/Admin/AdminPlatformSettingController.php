<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\PlatformSettingHistory;
use App\Services\PlatformConfigurationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPlatformSettingController extends Controller
{
    public function __construct(
        protected PlatformConfigurationService $configService
    ) {}

    /**
     * Màn hình thiết lập nền tảng an toàn & chẩn đoán kết nối (Mục 20 & Mục 50 PRD V3).
     */
    public function index(Request $request): View
    {
        $definitions = $this->configService->getDefaultDefinitions();
        $activeGroup = $request->query('group', PlatformSetting::GROUP_BRAND_SEO);

        if (! array_key_exists($activeGroup, $definitions)) {
            $activeGroup = PlatformSetting::GROUP_BRAND_SEO;
        }

        $groupMeta = $definitions[$activeGroup];
        $settings = $this->configService->getGroupSettings($activeGroup);
        $histories = PlatformSettingHistory::with('changer')
            ->where('group', $activeGroup)
            ->latest('id')
            ->take(10)
            ->get();

        return view('admin.pages.settings.platform', compact(
            'definitions',
            'activeGroup',
            'groupMeta',
            'settings',
            'histories'
        ));
    }

    /**
     * Lưu thay đổi cấu hình nhóm kèm bảo vệ masked secret & hiển thị diff.
     */
    public function update(Request $request, string $group): RedirectResponse
    {
        $definitions = $this->configService->getDefaultDefinitions();
        if (! array_key_exists($group, $definitions)) {
            return back()->with('error', 'Nhóm thiết lập không hợp lệ.');
        }

        $result = $this->configService->updateGroupSettings($group, $request->all(), $request->user());

        if ($result['updated'] === 0) {
            return back()->with('info', 'Không có giá trị nào thay đổi trong nhóm thiết lập này.');
        }

        return back()->with('success', "Đã lưu thành công {$result['updated']} mục thiết lập cho nhóm [{$definitions[$group]['title']}].");
    }

    /**
     * Kiểm tra kết nối hạ tầng thật bằng AJAX (Test Connection Diagnostics).
     */
    public function testConnection(Request $request, string $service): JsonResponse
    {
        $result = $this->configService->testConnection($service, $request->all());

        return response()->json($result);
    }

    /**
     * Khôi phục thiết lập về phiên bản trước (Rollback).
     */
    public function rollback(Request $request, int $historyId): RedirectResponse
    {
        $success = $this->configService->rollbackHistory($historyId, $request->user());

        if (! $success) {
            return back()->with('error', 'Không tìm thấy bản ghi lịch sử thiết lập để khôi phục.');
        }

        return back()->with('success', 'Đã khôi phục thiết lập về phiên bản trước thành công.');
    }
}

