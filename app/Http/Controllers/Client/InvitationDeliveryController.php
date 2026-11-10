<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestDelivery;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Services\InvitationDeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationDeliveryController extends Controller
{
    public function __construct(
        protected InvitationDeliveryService $deliveryService
    ) {}

    /**
     * Xem trước nội dung lời mời thiệp cưới cho khách được chỉ định.
     */
    public function preview(Request $request, int $guestId): JsonResponse
    {
        $guest = Guest::with('wedding')->findOrFail($guestId);
        $this->authorizeAccess($guest->wedding);

        $channel = (string) $request->query('channel', 'copy');
        $customTemplate = $request->query('template');
        $invitation = $guest->wedding->invitations()->first();

        $message = $this->deliveryService->buildPersonalizedMessage(
            $guest,
            $guest->wedding,
            $invitation,
            $customTemplate
        );

        return response()->json([
            'success' => true,
            'guest' => [
                'id' => $guest->id,
                'name' => $guest->name,
                'salutation' => $guest->salutation,
                'phone' => $guest->phone,
                'guest_token' => $guest->guest_token,
            ],
            'channel' => $channel,
            'message' => $message,
        ]);
    }

    /**
     * Gửi / Ghi nhận phân phối thiệp cưới cho 1 khách.
     */
    public function dispatchSingle(Request $request, int $guestId): JsonResponse
    {
        $guest = Guest::with('wedding')->findOrFail($guestId);
        $this->authorizeAccess($guest->wedding);

        $validated = $request->validate([
            'channel' => 'required|string|in:copy,zalo,sms,email',
            'custom_message' => 'nullable|string|max:2000',
        ]);

        $delivery = $this->deliveryService->dispatchSingle(
            $guest,
            $validated['channel'],
            $validated['custom_message'] ?? null
        );

        return response()->json([
            'success' => true,
            'delivery' => $delivery,
            'guest' => [
                'id' => $guest->id,
                'is_sent' => true,
                'sent_at' => $guest->fresh()->sent_at?->format('H:i d/m/Y'),
            ],
            'message' => 'Đã gửi thiệp cưới thành công qua kênh '.strtoupper($validated['channel']),
        ]);
    }

    /**
     * Phân phối thiệp cưới hàng loạt cho nhiều khách đã chọn.
     */
    public function dispatchBatch(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeAccess($invitation->wedding);

        $validated = $request->validate([
            'guest_ids' => 'required|array|min:1',
            'guest_ids.*' => 'integer|exists:guests,id',
            'channel' => 'required|string|in:copy,zalo,sms,email',
            'custom_template' => 'nullable|string|max:2000',
        ]);

        $result = $this->deliveryService->dispatchBatch(
            $invitation->wedding,
            $validated['guest_ids'],
            $validated['channel'],
            $validated['custom_template'] ?? null,
            $invitation
        );

        return response()->json([
            'success' => true,
            'result' => $result,
            'message' => "Đã phân phối thành công {$result['successful']}/{$result['total']} thiệp cưới.",
        ]);
    }

    /**
     * Thống kê hiệu suất phân phối thiệp cưới.
     */
    public function stats(int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeAccess($invitation->wedding);

        $stats = $this->deliveryService->getDeliveryStats($invitation->wedding);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Lịch sử các lần gửi thiệp cưới.
     */
    public function history(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeAccess($invitation->wedding);

        $query = GuestDelivery::where('wedding_id', $invitation->wedding->id)
            ->with('guest:id,name,salutation,phone,group_name');

        if ($request->filled('channel')) {
            $query->where('channel', $request->query('channel'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $deliveries = $query->latest('id')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $deliveries,
        ]);
    }

    /**
     * Kiểm tra quyền quản lý thiệp & khách mời.
     */
    protected function authorizeAccess(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        if ($wedding->partner_id && auth()->guard('partner')->check() && (int) $wedding->partner_id === (int) auth()->guard('partner')->id()) {
            return;
        }

        abort(403, 'Bạn không có quyền truy cập quản lý phân phối thiệp cưới này.');
    }
}
