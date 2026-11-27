<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ToolsGovernanceService;
use App\Services\SocialBannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
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
            'socialBanner' => ToolsGovernanceService::getToolSocialBanner($toolKey),
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

    /**
     * Thêm, sửa hoặc xóa Banner Mạng Xã Hội chuẩn kích thước 1200×600 cho từng trang công cụ.
     */
    public function updateSocialBanner(Request $request, string $moduleSlug): RedirectResponse
    {
        $toolKey = $this->resolveToolKey($moduleSlug);

        if ($request->boolean('remove_banner')) {
            $oldPath = ToolsGovernanceService::getToolSocialBanner($toolKey);
            $this->governance->saveToolSocialBanner($toolKey, null, $request->user()?->id);
            $this->deleteLocalBannerIfOwned($oldPath);

            return back()->with('success', 'Đã gỡ banner riêng (quay về banner mặc định của hệ thống).');
        }

        $validated = $request->validate([
            'banner_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'banner_url' => ['nullable', 'string', 'max:350'],
        ]);

        $newPath = null;
        if ($request->hasFile('banner_file')) {
            $file = $request->file('banner_file');
            $targetDir = public_path('storage/images/client/banners/tools');
            if (! File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            $ts = now()->format('YmdHis');
            $savedName = $this->saveResized1200x600($file->getRealPath(), $targetDir, "{$moduleSlug}-social-1200x600-{$ts}", $file->getClientOriginalExtension());
            $newPath = '/storage/images/client/banners/tools/'.$savedName;

            $oldPath = ToolsGovernanceService::getRawToolSocialBanner($toolKey);
        } elseif (! empty($validated['banner_url'])) {
            $oldPath = null;
            $newPath = trim((string) $validated['banner_url']);
        } else {
            return back()->with('error', 'Vui lòng chọn file ảnh hoặc nhập đường dẫn URL banner 1200×600.');
        }

        $this->governance->saveToolSocialBanner($toolKey, $newPath, $request->user()?->id);
        if (! empty($oldPath) && $oldPath !== $newPath) {
            $this->deleteLocalBannerIfOwned($oldPath);
        }

        return back()->with('success', 'Đã cập nhật Banner Mạng Xã Hội (1200×600) thành công!');
    }

    /**
     * Tự động center-crop & resize ảnh tải lên đúng kích thước chuẩn 1200×600 (tỉ lệ 2:1) bằng GD nếu máy chủ hỗ trợ.
     */
    protected function saveResized1200x600(string $sourcePath, string $targetDir, string $baseName, string $fallbackExt): string
    {
        $targetW = ToolsGovernanceService::SOCIAL_BANNER_WIDTH;
        $targetH = ToolsGovernanceService::SOCIAL_BANNER_HEIGHT;

        if (function_exists('imagecreatefromstring') && function_exists('imagecreatetruecolor')) {
            $raw = @file_get_contents($sourcePath);
            $src = $raw !== false ? @imagecreatefromstring($raw) : false;
            if ($src !== false) {
                $srcW = imagesx($src);
                $srcH = imagesy($src);
                if ($srcW > 0 && $srcH > 0) {
                    $targetRatio = $targetW / $targetH;
                    $srcRatio = $srcW / $srcH;
                    if ($srcRatio > $targetRatio) {
                        $cropH = $srcH;
                        $cropW = (int) round($srcH * $targetRatio);
                        $cropX = (int) round(($srcW - $cropW) / 2);
                        $cropY = 0;
                    } else {
                        $cropW = $srcW;
                        $cropH = (int) round($srcW / $targetRatio);
                        $cropX = 0;
                        $cropY = (int) round(($srcH - $cropH) / 2);
                    }

                    $dst = imagecreatetruecolor($targetW, $targetH);
                    imagealphablending($dst, true);
                    imagesavealpha($dst, true);
                    imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);

                    if (function_exists('imagewebp')) {
                        $filename = $baseName.'.webp';
                        imagewebp($dst, $targetDir.DIRECTORY_SEPARATOR.$filename, 88);
                        imagedestroy($dst);
                        imagedestroy($src);

                        return $filename;
                    }

                    $filename = $baseName.'.jpg';
                    imagejpeg($dst, $targetDir.DIRECTORY_SEPARATOR.$filename, 90);
                    imagedestroy($dst);
                    imagedestroy($src);

                    return $filename;
                }
                imagedestroy($src);
            }
        }

        $ext = strtolower($fallbackExt) ?: 'jpg';
        $filename = $baseName.'.'.$ext;
        File::copy($sourcePath, $targetDir.DIRECTORY_SEPARATOR.$filename);

        return $filename;
    }

    protected function deleteLocalBannerIfOwned(?string $bannerPath): void
    {
        app(SocialBannerService::class)->deleteLocalIfOwned($bannerPath);
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
