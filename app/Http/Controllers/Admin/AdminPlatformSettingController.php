<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\PlatformSettingHistory;
use App\Services\PlatformConfigurationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $inputs = $request->all();

        // Xử lý tải lên tệp ảnh (ví dụ: home_hero_banner_file, brand_logo_file, brand_favicon_file)
        foreach ($request->allFiles() as $fileKey => $file) {
            $settingKey = str_ends_with($fileKey, '_file') ? substr($fileKey, 0, -5) : $fileKey;
            if ($file && $file->isValid()) {
                // Danh sách tệp mặc định hệ thống không bao giờ được xóa
                $systemDefaults = [
                    '/storage/images/client/banners/banner-home.webp',
                    '/storage/images/client/business/mayhen-logo.png',
                    '/storage/images/client/business/mayhen-favicon.png',
                ];

                // Xóa ảnh cũ trong Storage nếu có và là ảnh upload do người dùng thay thế
                $oldValue = PlatformSetting::getValue($group, $settingKey);
                if ($oldValue && str_starts_with($oldValue, '/storage/') && ! in_array($oldValue, $systemDefaults, true)) {
                    $relPath = substr(parse_url($oldValue, PHP_URL_PATH), strlen('/storage/'));
                    if (Storage::disk('public')->exists($relPath)) {
                        Storage::disk('public')->delete($relPath);
                    }
                }

                $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
                $directory = match ($settingKey) {
                    'brand_logo', 'brand_favicon' => 'images/client/business',
                    default => 'images/client/banners',
                };
                $prefix = match ($settingKey) {
                    'brand_logo' => 'brand-logo-',
                    'brand_favicon' => 'brand-favicon-',
                    default => 'banner-home-',
                };

                $filename = $prefix.Str::random(10).'.'.$ext;
                $path = $file->storeAs($directory, $filename, 'public');
                $inputs[$settingKey] = '/storage/'.$path;
            }
        }

        $result = $this->configService->updateGroupSettings($group, $inputs, $request->user());

        // Xóa memoization và cache liên quan để cập nhật ngay lập tức
        HomeController::clearMemoization();
        Cache::forget('mayhen_home_hero_banner');
        Cache::forget('platform_setting_brand_seo_brand_logo');
        Cache::forget('platform_setting_brand_seo_brand_favicon');

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
