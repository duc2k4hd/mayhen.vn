<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\WeddingWish;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishController extends Controller
{
    /**
     * Common sensitive / spam keywords to flag.
     */
    public const SPAM_KEYWORDS = [
        'casino', 'cá độ', 'tai xiu', 'tài xỉu', 'bet88', 'kubet', 'shbet',
        'lừa đảo', 'sex', 'đụ', 'đm', 'vcl', 'clgt', 'vay tiền', 'tín dụng đen',
    ];

    /**
     * Display centralized guestbook wishes with moderation controls.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $weddingId = $request->input('wedding_id');

        $query = WeddingWish::query()->with([
            'wedding:id,user_id,groom_name,bride_name',
            'wedding.invitations:id,wedding_id,slug,is_published',
        ]);

        // 1. Text Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('wedding', function ($wq) use ($search) {
                        $wq->where('groom_name', 'like', "%{$search}%")
                            ->orWhere('bride_name', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Status Filter
        if ($status === 'approved') {
            $query->where('is_approved', true);
        } elseif ($status === 'pending') {
            $query->where('is_approved', false);
        } elseif ($status === 'flagged') {
            $query->where(function ($q) {
                foreach (self::SPAM_KEYWORDS as $kw) {
                    $q->orWhere('message', 'like', "%{$kw}%");
                }
            });
        }

        // 3. Wedding Filter
        if (! empty($weddingId)) {
            $query->where('wedding_id', (int) $weddingId);
        }

        $wishes = $query->latest('id')->paginate(15)->withQueryString();

        // System KPIs
        $totalWishes = WeddingWish::count();
        $approvedCount = WeddingWish::where('is_approved', true)->count();
        $pendingCount = WeddingWish::where('is_approved', false)->count();

        $totalGuests = Guest::count();
        $totalRsvps = GuestResponse::count();
        $attendingCount = GuestResponse::where('attending_status', 'attending')->count();
        $attendanceRate = $totalRsvps > 0 ? round(($attendingCount / $totalRsvps) * 100) : 0;

        return view('admin.pages.wishes.index', [
            'wishes' => $wishes,
            'currentSearch' => $search,
            'currentStatus' => $status,
            'currentWeddingId' => $weddingId,
            'totalWishes' => $totalWishes,
            'approvedCount' => $approvedCount,
            'pendingCount' => $pendingCount,
            'totalGuests' => $totalGuests,
            'totalRsvps' => $totalRsvps,
            'attendingCount' => $attendingCount,
            'attendanceRate' => $attendanceRate,
            'spamKeywords' => self::SPAM_KEYWORDS,
        ]);
    }

    /**
     * Toggle approval status of a wedding wish.
     */
    public function toggleApproval(int $id): RedirectResponse
    {
        $wish = WeddingWish::findOrFail($id);
        $newStatus = ! $wish->is_approved;

        $wish->update(['is_approved' => $newStatus]);

        $msg = $newStatus
            ? "Đã duyệt hiển thị lời chúc của \"{$wish->sender_name}\"."
            : "Đã tạm ẩn lời chúc của \"{$wish->sender_name}\" khỏi thiệp cưới.";

        return back()->with('success', $msg);
    }

    /**
     * Delete an inappropriate wish permanently.
     */
    public function destroy(int $id): RedirectResponse
    {
        $wish = WeddingWish::findOrFail($id);
        $sender = $wish->sender_name;
        $wish->delete();

        return back()->with('success', "Đã xóa vĩnh viễn lời chúc từ \"{$sender}\".");
    }

    /**
     * Display centralized RSVP report across the platform.
     */
    public function rsvps(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');

        $query = GuestResponse::query()->with([
            'wedding:id,user_id,groom_name,bride_name',
            'wedding.invitations:id,wedding_id,slug',
            'guest',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('wishes', 'like', "%{$search}%");
            });
        }

        if (! empty($status)) {
            $query->where('attending_status', $status);
        }

        $rsvps = $query->latest('id')->paginate(20)->withQueryString();

        $totalRsvps = GuestResponse::count();
        $attendingCount = GuestResponse::where('attending_status', 'attending')->count();
        $declinedCount = GuestResponse::where('attending_status', 'declined')->count();
        $uncertainCount = GuestResponse::where('attending_status', 'uncertain')->count();

        return view('admin.pages.wishes.rsvps', [
            'rsvps' => $rsvps,
            'currentSearch' => $search,
            'currentStatus' => $status,
            'totalRsvps' => $totalRsvps,
            'attendingCount' => $attendingCount,
            'declinedCount' => $declinedCount,
            'uncertainCount' => $uncertainCount,
        ]);
    }
}
