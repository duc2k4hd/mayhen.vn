<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\SubmitRsvpRequest;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

        $guestToken = $request->query('token');
        $guest = $guestToken ? Guest::where('guest_token', $guestToken)->first() : null;

        $response = GuestResponse::create([
            'wedding_id' => $wedding->id,
            'guest_id' => $guest?->id,
            'guest_name' => $request->validated('guest_name'),
            'attending_status' => $request->validated('attending_status'),
            'adults_count' => $request->validated('adults_count') ?? 1,
            'children_count' => $request->validated('children_count') ?? 0,
            'dietary_requirements' => $request->validated('dietary_requirements'),
            'wishes' => $request->validated('wishes'),
            'is_wishes_public' => true,
            'ip_address' => $request->ip(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn quý khách! Phản hồi RSVP đã được ghi nhận.',
            ]);
        }

        return back()->with('rsvp_success', 'Cảm ơn quý khách! Phản hồi đã được ghi nhận.');
    }
}
