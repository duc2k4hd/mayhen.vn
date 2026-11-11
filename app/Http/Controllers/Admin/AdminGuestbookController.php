<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Services\AdminGuestbookService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminGuestbookController extends Controller
{
    public function __construct(
        protected AdminGuestbookService $guestbookService
    ) {}

    /**
     * Hiển thị Album Sổ Lưu Bút Kỷ Niệm & Chữ Ký Điện Tử.
     */
    public function index(Request $request): View
    {
        $selectedWeddingId = $request->input('wedding_id');

        $activeWedding = null;
        if (! empty($selectedWeddingId)) {
            $activeWedding = Wedding::find((int) $selectedWeddingId);
        }

        if (! $activeWedding) {
            $activeWedding = Wedding::has('wishes')->latest('wedding_date')->first();
        }

        if (! $activeWedding) {
            $activeWedding = Wedding::latest('wedding_date')->first();
        }

        $allWeddings = Wedding::select('id', 'groom_name', 'bride_name', 'wedding_date')
            ->withCount('wishes')
            ->orderByDesc('wedding_date')
            ->limit(50)
            ->get();

        $guestbookData = null;
        if ($activeWedding) {
            $filters = [
                'tab' => $request->input('tab', 'all'),
                'q' => $request->input('q', ''),
            ];
            $guestbookData = $this->guestbookService->getGuestbookData($activeWedding, $filters);
        }

        return view('admin.pages.wishes.guestbook', [
            'activeWedding' => $activeWedding,
            'allWeddings' => $allWeddings,
            'guestbook' => $guestbookData,
            'filters' => [
                'tab' => $request->input('tab', 'all'),
                'q' => $request->input('q', ''),
            ],
        ]);
    }

    /**
     * Ghim hoặc bỏ ghim lời chúc nổi bật.
     */
    public function togglePin(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $result = $this->guestbookService->togglePin($id, $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Duyệt hoặc tạm ẩn lời chúc.
     */
    public function toggleApproval(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $result = $this->guestbookService->toggleApproval($id, $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Chuyển đổi trạng thái riêng tư / công khai của lời chúc.
     */
    public function togglePrivacy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $result = $this->guestbookService->togglePrivacy($id, $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Viết lời đáp lễ / hồi đáp tri ân của cặp đôi.
     */
    public function reply(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'reply' => 'required|string|max:1000',
        ], [
            'reply.required' => 'Vui lòng nhập nội dung lời đáp lễ.',
            'reply.max' => 'Lời đáp lễ tối đa 1000 ký tự.',
        ]);

        $operator = $request->user()?->name ?? 'Admin';
        $wish = $this->guestbookService->replyWish($id, (string) $request->input('reply'), $operator);

        $msg = "Đã gửi lời đáp lễ tri ân tới \"{$wish->sender_name}\" thành công.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'wish' => $wish,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Xóa lời đáp lễ của cặp đôi.
     */
    public function deleteReply(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $wish = $this->guestbookService->deleteReply($id, $operator);

        $msg = "Đã xóa lời đáp lễ gửi tới \"{$wish->sender_name}\".";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'wish' => $wish,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Xóa vĩnh viễn lời chúc khỏi sổ lưu bút.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $this->guestbookService->deleteWish($id, $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa vĩnh viễn lời chúc khỏi sổ lưu bút.',
            ]);
        }

        return back()->with('success', 'Đã xóa vĩnh viễn lời chúc khỏi sổ lưu bút.');
    }

    /**
     * Xem bản in lưu niệm trang nhã (Printable HTML Keepsake Album).
     */
    public function exportHtml(Request $request, int $weddingId): View
    {
        $wedding = Wedding::with(['wishes' => function ($q) {
            $q->orderByDesc('is_pinned')->oldest('created_at');
        }])->findOrFail($weddingId);

        return view('admin.pages.wishes.keepsake_print', [
            'wedding' => $wedding,
            'wishes' => $wedding->wishes,
        ]);
    }

    public function keepsakePrint(Request $request, int $weddingId): View
    {
        return $this->exportHtml($request, $weddingId);
    }

    /**
     * Xuất tệp Sổ Lưu Bút Kỷ Niệm định dạng CSV UTF-8 BOM.
     */
    public function exportCsv(Request $request, int $weddingId): Response
    {
        $wedding = Wedding::findOrFail($weddingId);

        return $this->guestbookService->exportGuestbookCsv($wedding, $request->all());
    }

    public function export(Request $request, int $weddingId): Response
    {
        return $this->exportCsv($request, $weddingId);
    }
}
