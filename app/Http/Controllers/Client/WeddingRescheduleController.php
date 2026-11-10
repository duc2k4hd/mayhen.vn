<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Services\WeddingRescheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeddingRescheduleController extends Controller
{
    public function __construct(
        protected WeddingRescheduleService $rescheduleService
    ) {}

    /**
     * Màn hình Trung Tâm Thông Báo Dời Lịch Cưới & Khảo Sát Lại RSVP (Luồng G).
     */
    public function index(int $id): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $data = $this->rescheduleService->getRescheduleData($wedding, $invitation);
        $guests = $wedding->guests()->with('response')->latest()->get();

        return view('client.pages.account.reschedule.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'data' => $data,
            'guests' => $guests,
        ]);
    }

    /**
     * Thực hiện điều chỉnh ngày cưới và xử lý tái xác nhận RSVP.
     */
    public function submit(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $validated = $request->validate([
            'new_date' => 'required|date|after_or_equal:today',
            'new_venue' => 'nullable|string|max:255',
            'new_venue_address' => 'nullable|string|max:3000',
            'reason' => 'nullable|string|max:500',
            'rsvp_action' => 'required|string|in:keep_existing,request_reconfirm',
        ]);

        $log = $this->rescheduleService->executeReschedule($wedding, $invitation, $validated);

        return redirect()->route('account.reschedule.index', $id)
            ->with('success', "Đã cập nhật ngày cưới mới sang {$log->new_date->format('d/m/Y')} thành công! Bạn có thể sao chép tin nhắn thông báo bên dưới để gửi đến từng quan khách.");
    }

    /**
     * API trích xuất nội dung tin nhắn Zalo thông báo dời lịch cá nhân hóa cho từng khách.
     */
    public function message(int $id, int $guestId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $guest = $wedding->guests()->findOrFail($guestId);
        $latestLog = $wedding->rescheduleLogs()->first();

        $message = $this->rescheduleService->generateZaloMessage($guest, $wedding, $latestLog);

        return response()->json([
            'success' => true,
            'guest_name' => $guest->name,
            'message' => $message,
        ]);
    }

    private function authorizeAccess(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        abort(404);
    }
}
