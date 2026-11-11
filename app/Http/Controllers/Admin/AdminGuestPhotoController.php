<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Services\AdminGuestPhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminGuestPhotoController extends Controller
{
    public function __construct(
        protected AdminGuestPhotoService $photoService
    ) {}

    /**
     * Bảng điều khiển Thư Viện Khoảnh Khắc Khách Gửi & Kiểm Duyệt Tường Ảnh (Mục 217, 218 PRD V3).
     */
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->input('q', ''),
            'wedding_id' => $request->input('wedding_id'),
            'status' => $request->input('status', 'all'),
        ];

        $data = $this->photoService->getGuestPhotosOverview($filters);
        $weddings = Wedding::latest()->limit(50)->get(['id', 'title', 'groom_name', 'bride_name']);

        return view('admin.pages.guest_photos.index', [
            'stats' => $data['stats'],
            'photos' => $data['photos'],
            'filters' => $filters,
            'weddings' => $weddings,
        ]);
    }

    /**
     * Chuyển đổi trạng thái duyệt ảnh 1-chạm (AJAX).
     */
    public function toggleApproval(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $result = $this->photoService->toggleApproval($id, $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Duyệt hiển thị hàng loạt ảnh được chọn.
     */
    public function batchApprove(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:guest_photos,id',
        ]);

        $ids = $request->input('ids', []);
        $operator = $request->user()?->name ?? 'Admin';
        $count = $this->photoService->batchApprove($ids, $operator);

        $msg = "Đã duyệt hiển thị thành công {$count} ảnh của quan khách.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Xóa vĩnh viễn một ảnh khách gửi.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $this->photoService->deletePhoto($id, $operator);

        $msg = 'Đã xóa ảnh thành công.';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Xóa hàng loạt ảnh khách gửi.
     */
    public function batchDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:guest_photos,id',
        ]);

        $ids = $request->input('ids', []);
        $operator = $request->user()?->name ?? 'Admin';
        $count = $this->photoService->batchDelete($ids, $operator);

        $msg = "Đã xóa vĩnh viễn {$count} ảnh được chọn.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Màn hình trình chiếu Live Slideshow Fullscreen phục vụ máy chiếu tiệc cưới.
     */
    public function slideshow(Request $request): View
    {
        $weddingId = $request->input('wedding_id') ? (int) $request->input('wedding_id') : null;
        $wedding = $weddingId ? Wedding::find($weddingId) : null;
        $photos = $this->photoService->getSlideshowPhotos($weddingId, 100);

        return view('admin.pages.guest_photos.slideshow', [
            'wedding' => $wedding,
            'photos' => $photos,
        ]);
    }

    /**
     * Xuất tệp báo cáo danh sách ảnh khách gửi CSV UTF-8 BOM.
     */
    public function export(Request $request): Response
    {
        return $this->photoService->exportPhotosCsv($request->all());
    }
}

