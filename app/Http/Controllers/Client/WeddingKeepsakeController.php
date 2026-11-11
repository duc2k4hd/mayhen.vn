<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\WeddingKeepsakeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeddingKeepsakeController extends Controller
{
    /**
     * Màn hình Bảng Điều Khiển Kỷ Niệm Hậu Sự Kiện.
     */
    public function index(int $id): View
    {
        $invitation = Invitation::with(['wedding.keepsake', 'template'])->findOrFail($id);
        $wedding = $invitation->wedding;

        if ($wedding->user_id !== Auth::id()) {
            abort(404);
        }

        $keepsake = WeddingKeepsakeService::getOrCreateKeepsake($wedding, $invitation);

        // Khách mời đã xác nhận tham dự hoặc đã check-in tại sảnh
        $guests = $wedding->guests()
            ->where(function ($q) {
                $q->where('is_checked_in', true)
                    ->orWhereHas('response', function ($rq) {
                        $rq->where('attending', true);
                    });
            })
            ->orderBy('name')
            ->paginate(15);

        $stats = [
            'days_since' => $keepsake->daysSinceWedding(),
            'milestone_label' => $keepsake->milestone_label,
            'attended_guests_count' => $wedding->guests()->where('is_checked_in', true)->count(),
            'wishes_count' => $wedding->wishes()->count(),
            'photos_count' => $wedding->guestPhotos()->count(),
        ];

        return view('client.pages.account.keepsake.index', compact('invitation', 'wedding', 'keepsake', 'guests', 'stats'));
    }

    /**
     * Lưu thông tin cấu hình tâm thư cảm ơn và giao diện kỷ niệm.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::with('wedding.keepsake')->findOrFail($id);
        $wedding = $invitation->wedding;

        if ($wedding->user_id !== Auth::id()) {
            abort(404);
        }

        $validated = $request->validate([
            'thank_you_title' => ['required', 'string', 'max:255'],
            'thank_you_message' => ['required', 'string', 'max:5000'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'anniversary_date' => ['nullable', 'date'],
            'show_wishes' => ['nullable', 'boolean'],
            'show_photos' => ['nullable', 'boolean'],
            'show_video' => ['nullable', 'boolean'],
            'hide_rsvp' => ['nullable', 'boolean'],
            'hide_gifts' => ['nullable', 'boolean'],
        ], [
            'thank_you_title.required' => 'Vui lòng nhập tiêu đề lời cảm ơn.',
            'thank_you_message.required' => 'Vui lòng nhập nội dung tâm thư cảm ơn quan khách.',
            'video_url.url' => 'Đường dẫn video highlight phải là URL hợp lệ (YouTube hoặc Vimeo).',
        ]);

        $keepsake = WeddingKeepsakeService::getOrCreateKeepsake($wedding, $invitation);

        WeddingKeepsakeService::updateKeepsake($keepsake, [
            'thank_you_title' => $validated['thank_you_title'],
            'thank_you_message' => $validated['thank_you_message'],
            'video_url' => $validated['video_url'] ?? null,
            'anniversary_date' => $validated['anniversary_date'] ?? null,
            'show_wishes' => $request->boolean('show_wishes', true),
            'show_photos' => $request->boolean('show_photos', true),
            'show_video' => $request->boolean('show_video', true),
            'hide_rsvp' => $request->boolean('hide_rsvp', true),
            'hide_gifts' => $request->boolean('hide_gifts', false),
        ], Auth::user());

        return back()->with('success', 'Đã lưu cấu hình trang kỷ niệm và lời cảm ơn thành công.');
    }

    /**
     * Bật hoặc tắt Chế độ Thiệp Kỷ Niệm (API JSON nhanh).
     */
    public function toggle(Request $request, int $id): JsonResponse
    {
        $invitation = Invitation::with('wedding.keepsake')->findOrFail($id);
        $wedding = $invitation->wedding;

        if ($wedding->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền thao tác'], 403);
        }

        $validated = $request->validate([
            'is_enabled' => ['required', 'boolean'],
        ]);

        $keepsake = WeddingKeepsakeService::getOrCreateKeepsake($wedding, $invitation);
        WeddingKeepsakeService::toggleKeepsakeMode($keepsake, (bool) $validated['is_enabled'], Auth::user());

        return response()->json([
            'success' => true,
            'is_enabled' => $keepsake->is_enabled,
            'message' => $keepsake->is_enabled
                ? 'Đã chuyển thiệp sang Chế Độ Kỷ Niệm Hậu Sự Kiện.'
                : 'Đã chuyển thiệp về Chế Độ Mời Cưới bình thường.',
        ]);
    }

    /**
     * Xuất Sổ Lưu Niệm Ngày Cưới (Memorial Keepsake Book) in ấn trang nhã.
     */
    public function exportBook(int $id): View
    {
        $invitation = Invitation::with(['wedding.wishes', 'wedding.guests'])->findOrFail($id);
        $wedding = $invitation->wedding;

        if ($wedding->user_id !== Auth::id()) {
            abort(404);
        }

        $keepsake = WeddingKeepsakeService::getOrCreateKeepsake($wedding, $invitation);
        $data = WeddingKeepsakeService::getMemorialBookData($wedding, $invitation, $keepsake);

        return view('client.pages.account.keepsake.memorial_book', $data);
    }

    /**
     * Lấy mẫu tin nhắn cảm ơn cá nhân hóa cho từng khách mời (API JSON).
     */
    public function getThankYouMessage(Request $request, int $id, int $guestId): JsonResponse
    {
        $invitation = Invitation::with('wedding.keepsake')->findOrFail($id);
        $wedding = $invitation->wedding;

        if ($wedding->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập'], 403);
        }

        $guest = Guest::where('wedding_id', $wedding->id)->findOrFail($guestId);
        $keepsake = WeddingKeepsakeService::getOrCreateKeepsake($wedding, $invitation);

        $message = WeddingKeepsakeService::generateThankYouMessage($guest, $wedding, $keepsake);

        return response()->json([
            'success' => true,
            'guest_name' => $guest->name,
            'phone' => $guest->phone,
            'message' => $message,
        ]);
    }
}
