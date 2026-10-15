<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\SubmitRsvpRequest;
use App\Http\Requests\SubmitWishRequest;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\WeddingWish;
use App\Rules\NoProfanity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GuestController extends Controller
{
    /**
     * Display guest management and RSVP dashboard.
     */
    public function index(int $invitationId): View
    {
        $invitation = Invitation::with(['wedding.guests.response'])->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $guests = $wedding->guests;

        // Statistics
        $totalInvited = $guests->sum('expected_attendees');
        $totalResponses = $wedding->guestResponses()->count();
        $attendingCount = $wedding->guestResponses()->where('attending_status', 'attending')->sum('adults_count')
            + $wedding->guestResponses()->where('attending_status', 'attending')->sum('children_count');
        $declinedCount = $wedding->guestResponses()->where('attending_status', 'declined')->count();

        return view('client.pages.guests', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'guests' => $guests,
            'totalInvited' => $totalInvited,
            'totalResponses' => $totalResponses,
            'attendingCount' => $attendingCount,
            'declinedCount' => $declinedCount,
        ]);
    }

    /**
     * Store new guest with personalized link.
     */
    public function store(StoreGuestRequest $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;

        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => $request->validated('name'),
            'salutation' => $request->validated('salutation') ?? 'Thân mời',
            'group_name' => $request->validated('group_name') ?? 'Bạn Bè',
            'phone' => $request->validated('phone'),
            'expected_attendees' => $request->validated('expected_attendees') ?? 1,
            'guest_token' => Str::random(32),
        ]);

        return redirect()->route('guests.index', $invitationId)
            ->with('success', 'Đã thêm khách mời thành công!');
    }

    /**
     * Delete guest.
     */
    public function destroy(int $guestId): RedirectResponse
    {
        $guest = Guest::findOrFail($guestId);
        $invitationId = $guest->wedding->invitations()->first()?->id ?? 1;
        $guest->delete();

        return back()->with('success', 'Đã xóa khách mời khỏi danh sách.');
    }

    /**
     * Submit RSVP from public invitation.
     */
    public function submitRsvp(SubmitRsvpRequest $request, string $slug): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        // 0. Chủ nhân thiệp không được phép gửi xác nhận tham dự cho chính mình
        if ($invitation->isOwner()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi xác nhận tham dự cho chính mình.',
                ], 403);
            }

            return back()->with('rsvp_error', 'Bạn là chủ nhân của thiệp cưới này.');
        }

        $guestToken = $request->query('token');
        $guest = $guestToken ? Guest::where('guest_token', $guestToken)->first() : null;
        $ip = $request->ip();

        // 1. Kiểm tra nếu IP này đã từng gửi xác nhận tham dự cho đám cưới này
        $existingResponse = GuestResponse::where('wedding_id', $wedding->id)
            ->where('ip_address', $ip)
            ->first();

        if ($existingResponse) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi và không thể thay đổi.',
                ], 422);
            }

            return back()->with('rsvp_error', 'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi.');
        }

        $response = GuestResponse::create([
            'wedding_id' => $wedding->id,
            'guest_id' => $guest?->id,
            'guest_name' => $request->validated('guest_name'),
            'phone' => $request->validated('phone'),
            'attending_status' => $request->validated('attending_status'),
            'adults_count' => $request->validated('adults_count') ?? 1,
            'children_count' => $request->validated('children_count') ?? 0,
            'dietary_requirements' => $request->validated('dietary_requirements'),
            'wishes' => $request->validated('wishes'),
            'is_wishes_public' => true,
            'ip_address' => $ip,
        ]);

        // Nếu RSVP có kèm lời chúc, tự động lưu hoặc cập nhật vào sổ lưu bút của IP này
        if (! empty($request->validated('wishes'))) {
            WeddingWish::updateOrCreate(
                [
                    'wedding_id' => $wedding->id,
                    'ip_address' => $ip,
                ],
                [
                    'name' => $request->validated('guest_name'),
                    'message' => $request->validated('wishes'),
                    'is_approved' => true,
                ]
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'attending_status' => $request->validated('attending_status'),
                'message' => $request->validated('attending_status') === 'attending'
                    ? 'Cảm ơn quý khách đã xác nhận tham dự! Chúng tôi rất hân hạnh được đón tiếp.'
                    : 'Cảm ơn quý khách đã gửi phản hồi! Chúng tôi rất tiếc vì quý khách chưa thể sắp xếp tham dự.',
            ]);
        }

        return back()->with('rsvp_success', 'Cảm ơn quý khách! Phản hồi đã được ghi nhận.');
    }

    /**
     * Demo RSVP submission for template preview mode.
     */
    public function submitDemoRsvp(SubmitRsvpRequest $request, string $slug): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $request->validated('attending_status') === 'attending'
                ? 'Cảm ơn bạn đã xác nhận tham dự! (Thử nghiệm)'
                : 'Cảm ơn bạn đã gửi phản hồi! (Thử nghiệm)',
        ], 200);
    }

    /**
     * Submit or edit a wish to the wedding guestbook.
     * 1 IP chỉ được gửi 1 lời chúc, nếu gửi lại sẽ cập nhật lời chúc cũ.
     */
    public function submitWish(SubmitWishRequest $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        // Chủ nhân thiệp không gửi lời chúc cho chính mình mà chỉ trả lời lời chúc của khách
        if ($invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi lời chúc. Bạn có thể trả lời các lời chúc của khách mời.',
            ], 403);
        }

        $ip = $request->ip();

        $existingWish = WeddingWish::where('wedding_id', $wedding->id)
            ->where('ip_address', $ip)
            ->first();

        $isUpdate = false;
        if ($existingWish) {
            $existingWish->update([
                'name' => $request->validated('name'),
                'message' => $request->validated('message'),
            ]);
            $wish = $existingWish;
            $isUpdate = true;
            $message = 'Đã cập nhật lời chúc của bạn thành công!';
        } else {
            $wish = WeddingWish::create([
                'wedding_id' => $wedding->id,
                'name' => $request->validated('name'),
                'message' => $request->validated('message'),
                'is_approved' => true,
                'ip_address' => $ip,
            ]);
            $message = 'Cảm ơn bạn đã gửi lời chúc tốt đẹp tới cô dâu & chú rể!';
        }

        return response()->json([
            'success' => true,
            'is_update' => $isUpdate,
            'message' => $message,
            'wish' => [
                'id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'reply' => $wish->reply,
                'created_at_human' => $wish->created_at->diffForHumans(),
                'created_at_date' => $wish->created_at->format('d/m/Y H:i'),
            ],
        ], $isUpdate ? 200 : 201);
    }

    /**
     * Reply to a wish (Owner only).
     */
    public function replyWish(Request $request, string $slug, int $wishId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        if (! $invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền phản hồi lời chúc của thiệp cưới này.',
            ], 403);
        }

        $request->validate([
            'reply' => ['required', 'string', 'max:1000', new NoProfanity],
        ], [
            'reply.required' => 'Vui lòng nhập nội dung phản hồi.',
            'reply.max' => 'Nội dung phản hồi không được vượt quá 1000 ký tự.',
        ]);

        $wedding = $invitation->wedding;
        $wish = WeddingWish::where('wedding_id', $wedding->id)->findOrFail($wishId);

        $wish->update([
            'reply' => $request->input('reply'),
            'replied_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi phản hồi lời chúc thành công!',
            'reply' => $wish->reply,
            'replied_at_human' => 'Vừa xong',
        ]);
    }

    /**
     * Get RSVP responses list and statistics (Owner only).
     */
    public function getRsvpResponses(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        if (! $invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem danh sách phản hồi của thiệp cưới này.',
            ], 403);
        }

        $wedding = $invitation->wedding;
        $responses = GuestResponse::where('wedding_id', $wedding->id)
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'guest_name' => $r->guest_name,
                'phone' => $r->phone,
                'attending_status' => $r->attending_status,
                'adults_count' => $r->adults_count,
                'children_count' => $r->children_count,
                'wishes' => $r->wishes,
                'created_at_human' => $r->created_at?->diffForHumans() ?? 'Vừa xong',
                'created_at_date' => $r->created_at?->format('d/m/Y H:i') ?? '',
            ]);

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $responses->count(),
                'attending' => $responses->where('attending_status', 'attending')->count(),
                'attending_guests' => (int) $responses->where('attending_status', 'attending')->sum('adults_count'),
                'declined' => $responses->where('attending_status', 'declined')->count(),
            ],
            'responses' => $responses,
        ]);
    }

    /**
     * Get wishes list for an invitation.
     */
    public function getWishes(string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wishes = $invitation->wedding->wishes()
            ->where('is_approved', true)
            ->take(50)
            ->get()
            ->map(fn ($wish) => [
                'id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'reply' => $wish->reply,
                'created_at_human' => $wish->created_at->diffForHumans(),
                'created_at_date' => $wish->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'success' => true,
            'wishes' => $wishes,
        ]);
    }

    /**
     * Demo wish submission for template preview mode.
     */
    public function submitDemoWish(SubmitWishRequest $request, string $slug): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã gửi lời chúc thử nghiệm!',
            'wish' => [
                'id' => rand(1000, 9999),
                'name' => $request->validated('name'),
                'message' => $request->validated('message'),
                'reply' => null,
                'created_at_human' => 'Vừa xong',
                'created_at_date' => now()->format('d/m/Y H:i'),
            ],
        ], 201);
    }
}
