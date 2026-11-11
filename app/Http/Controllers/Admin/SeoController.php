<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoRedirect;
use App\Services\AuditLogger;
use App\Services\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SeoController extends Controller
{
    /**
     * Trung tâm chỉ huy SEO, Thanh tra Sức khỏe SEO & Quản lý Chuyển hướng 301/302.
     */
    public function index(): View
    {
        $redirects = SeoRedirect::latest('id')->paginate(15);
        $healthAudit = SeoService::runHealthAudit();

        $hasSitemapCache = Cache::has('mayhen_sitemap_xml');
        $sitemapUrl = url('/sitemap.xml');
        $robotsUrl = url('/robots.txt');

        $stats = [
            'redirects_total' => SeoRedirect::count(),
            'redirects_active' => SeoRedirect::active()->count(),
            'total_hits' => SeoRedirect::sum('hits'),
            'health_score' => $healthAudit['score'],
        ];

        return view('admin.pages.seo.index', compact(
            'redirects',
            'healthAudit',
            'hasSitemapCache',
            'sitemapUrl',
            'robotsUrl',
            'stats'
        ));
    }

    /**
     * Tạo mới chuyển hướng SEO 301/302 kèm kiểm tra phát hiện vòng lặp vô tận (Loop Detection).
     */
    public function storeRedirect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_path' => ['required', 'string', 'max:255'],
            'target_url' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'in:301,302'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'source_path.required' => 'Vui lòng nhập đường dẫn nguồn cần chuyển hướng.',
            'target_url.required' => 'Vui lòng nhập đường dẫn đích đến.',
        ]);

        $normalizedSource = SeoRedirect::normalizePath($validated['source_path']);
        $normalizedTarget = SeoRedirect::normalizePath($validated['target_url']);

        // 1. Kiểm tra xem đường dẫn nguồn đã tồn tại chưa
        if (SeoRedirect::where('source_path', $normalizedSource)->exists()) {
            return back()->withInput()->with('error', "Đường dẫn nguồn '{$normalizedSource}' đã tồn tại trong danh sách chuyển hướng.");
        }

        // 2. Phát hiện vòng lặp vô tận (Loop Detection)
        if (SeoService::detectLoop($normalizedSource, $normalizedTarget)) {
            return back()->withInput()->with('error', "Phát hiện vòng lặp chuyển hướng vô tận (Infinite Redirect Loop)! Tuyến đường {$normalizedSource} -> {$normalizedTarget} sẽ gây lỗi treo trình duyệt người dùng.");
        }

        $redirect = SeoRedirect::create([
            'source_path' => $normalizedSource,
            'target_url' => $validated['target_url'],
            'status_code' => (int) $validated['status_code'],
            'is_active' => true,
            'note' => $validated['note'] ?? null,
        ]);

        AuditLogger::log(
            action: 'SEO_REDIRECT_CREATED',
            targetType: 'SeoRedirect',
            targetId: $redirect->id,
            description: "Tạo chuyển hướng {$redirect->status_code}: {$redirect->source_path} -> {$redirect->target_url}"
        );

        return back()->with('success', "Đã tạo chuyển hướng {$redirect->status_code} thành công.");
    }

    /**
     * Cập nhật chuyển hướng SEO.
     */
    public function updateRedirect(Request $request, int $id): RedirectResponse
    {
        $redirect = SeoRedirect::findOrFail($id);

        $validated = $request->validate([
            'source_path' => ['required', 'string', 'max:255'],
            'target_url' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'in:301,302'],
            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $normalizedSource = SeoRedirect::normalizePath($validated['source_path']);
        $normalizedTarget = SeoRedirect::normalizePath($validated['target_url']);

        // Kiểm tra trùng lặp
        $exists = SeoRedirect::where('source_path', $normalizedSource)->where('id', '!=', $id)->exists();
        if ($exists) {
            return back()->with('error', "Đường dẫn nguồn '{$normalizedSource}' đã tồn tại.");
        }

        // Kiểm tra vòng lặp
        if (SeoService::detectLoop($normalizedSource, $normalizedTarget, $id)) {
            return back()->with('error', 'Phát hiện vòng lặp chuyển hướng vô tận! Vui lòng kiểm tra lại đích đến.');
        }

        $redirect->update([
            'source_path' => $normalizedSource,
            'target_url' => $validated['target_url'],
            'status_code' => (int) $validated['status_code'],
            'is_active' => $request->boolean('is_active', true),
            'note' => $validated['note'] ?? null,
        ]);

        return back()->with('success', 'Đã cập nhật chuyển hướng thành công.');
    }

    /**
     * Xóa chuyển hướng.
     */
    public function destroyRedirect(int $id): RedirectResponse
    {
        $redirect = SeoRedirect::findOrFail($id);
        $redirect->delete();

        return back()->with('success', 'Đã xóa chuyển hướng thành công.');
    }

    /**
     * Bật/tắt nhanh chuyển hướng.
     */
    public function toggleRedirect(Request $request, int $id): JsonResponse
    {
        $redirect = SeoRedirect::findOrFail($id);
        $redirect->is_active = ! $redirect->is_active;
        $redirect->save();

        return response()->json([
            'success' => true,
            'is_active' => $redirect->is_active,
            'message' => $redirect->is_active ? 'Đã kích hoạt chuyển hướng.' : 'Đã tạm ngưng chuyển hướng.',
        ]);
    }

    /**
     * Xóa bộ nhớ đệm XML Sitemap.
     */
    public function clearSitemap(): RedirectResponse
    {
        Cache::forget('mayhen_sitemap_xml');

        AuditLogger::log(
            action: 'SEO_SITEMAP_CACHE_CLEARED',
            targetType: 'System',
            targetId: 0,
            description: 'Xóa bộ nhớ đệm XML Sitemap toàn hệ thống'
        );

        return back()->with('success', 'Đã xóa bộ nhớ đệm Sitemap XML. File sitemap.xml sẽ được tái tạo tự động vào lượt truy cập tiếp theo.');
    }
}
