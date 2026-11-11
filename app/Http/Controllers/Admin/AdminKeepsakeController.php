<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Services\AdminKeepsakeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminKeepsakeController extends Controller
{
    public function __construct(
        protected AdminKeepsakeService $keepsakeService
    ) {}

    /**
     * Danh sách tiệc cưới hậu sự kiện & lưu trữ kỷ niệm.
     */
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->input('q', ''),
            'status' => $request->input('status', 'all'),
        ];

        $data = $this->keepsakeService->getKeepsakesOverview($filters);

        return view('admin.pages.keepsakes.index', [
            'stats' => $data['stats'],
            'weddings' => $data['weddings'],
            'filters' => $filters,
        ]);
    }

    /**
     * Xem chi tiết hậu sự kiện & quản trị kỷ niệm của một tiệc cưới.
     */
    public function show(int $weddingId): View
    {
        $wedding = Wedding::findOrFail($weddingId);
        $detail = $this->keepsakeService->getWeddingKeepsakeDetail($wedding);

        return view('admin.pages.keepsakes.show', [
            'wedding' => $detail['wedding'],
            'keepsake' => $detail['keepsake'],
            'timeCapsules' => $detail['timeCapsules'],
            'summary' => $detail['summary'],
        ]);
    }

    /**
     * Cập nhật cấu hình Trang Kỷ Niệm Hậu Sự Kiện.
     */
    public function updateSettings(Request $request, int $weddingId): RedirectResponse
    {
        $wedding = Wedding::findOrFail($weddingId);

        $validated = $request->validate([
            'is_enabled' => 'nullable|boolean',
            'thank_you_title' => 'required|string|max:255',
            'thank_you_message' => 'nullable|string|max:5000',
            'video_url' => 'nullable|url|max:500',
            'anniversary_date' => 'nullable|date',
            'show_wishes' => 'nullable|boolean',
            'show_photos' => 'nullable|boolean',
            'show_video' => 'nullable|boolean',
            'hide_rsvp' => 'nullable|boolean',
            'hide_gifts' => 'nullable|boolean',
            'highlight_photos' => 'nullable|array',
            'highlight_photos.*' => 'nullable|string|max:500',
        ]);

        $operator = $request->user()?->name ?? 'Admin';
        $this->keepsakeService->updateKeepsakeSettings($wedding, $validated, $operator);

        return back()->with('success', 'Đã lưu cấu hình Trang Kỷ Niệm Hậu Sự Kiện thành công.');
    }

    /**
     * Bật/Tắt nhanh chế độ Kỷ niệm.
     */
    public function toggle(Request $request, int $weddingId): JsonResponse|RedirectResponse
    {
        $wedding = Wedding::findOrFail($weddingId);
        $operator = $request->user()?->name ?? 'Admin';
        $result = $this->keepsakeService->toggleKeepsakeMode($wedding, $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Mở niêm phong sớm bức thư Hộp thư thời gian.
     */
    public function unlockCapsule(Request $request, int $capsuleId): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $result = $this->keepsakeService->unlockCapsule($capsuleId, $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Xóa một bức thư thời gian.
     */
    public function destroyCapsule(Request $request, int $capsuleId): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $this->keepsakeService->deleteCapsule($capsuleId, $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa bức thư kỷ niệm thành công.',
            ]);
        }

        return back()->with('success', 'Đã xóa bức thư kỷ niệm thành công.');
    }

    /**
     * Xuất tệp gói đóng gói lưu trữ hậu sự kiện (JSON Wrap-up Archive).
     */
    public function exportArchive(int $weddingId): Response
    {
        $wedding = Wedding::findOrFail($weddingId);

        return $this->keepsakeService->exportWrapUpArchive($wedding);
    }
}
