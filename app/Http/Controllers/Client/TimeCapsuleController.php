<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Models\WeddingTimeCapsule;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TimeCapsuleController extends Controller
{
    /**
     * Không gian quản lý Hộp Thời Gian của cặp đôi (/tai-khoan/thiep/{id}/hop-thoi-gian).
     */
    public function index(int $id): View
    {
        $invitation = Invitation::with(['wedding.partner', 'template'])->findOrFail($id);
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $this->authorizeOwner($wedding);

        $capsules = $wedding->timeCapsules()->latest()->get();

        // Thống kê số lượng
        $totalCount = $capsules->count();
        $sealedCount = $capsules->where('is_sealed', true)->count();
        $unlockedCount = $capsules->where('is_sealed', false)->count();

        // Gợi ý ngày mở khóa theo ngày cưới
        $weddingDate = $wedding->wedding_date ? Carbon::parse($wedding->wedding_date) : Carbon::today();
        $suggestedDates = [
            1 => $weddingDate->copy()->addYear()->format('Y-m-d'),
            3 => $weddingDate->copy()->addYears(3)->format('Y-m-d'),
            5 => $weddingDate->copy()->addYears(5)->format('Y-m-d'),
            10 => $weddingDate->copy()->addYears(10)->format('Y-m-d'),
        ];

        return view('client.pages.account.time-capsule', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'capsules' => $capsules,
            'totalCount' => $totalCount,
            'sealedCount' => $sealedCount,
            'unlockedCount' => $unlockedCount,
            'suggestedDates' => $suggestedDates,
            'weddingDate' => $weddingDate,
        ]);
    }

    /**
     * Cặp đôi khởi tạo và niêm phong tâm thư gửi tương lai.
     */
    public function store(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $this->authorizeOwner($wedding);

        $request->validate([
            'sender_type' => 'required|in:groom,bride,couple',
            'sender_name' => 'required|string|max:100',
            'recipient_name' => 'required|string|max:100',
            'title' => 'required|string|max:191',
            'content' => 'required|string|max:20000',
            'milestone_years' => 'required|integer|in:1,3,5,10,25',
            'unlock_date' => 'required|date',
            'seal_color' => 'required|in:ruby,gold,emerald,sapphire',
            'sender_email' => 'nullable|email|max:191',
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề cho phong thư.',
            'content.required' => 'Vui lòng nhập nội dung tâm thư gửi tương lai.',
            'unlock_date.required' => 'Vui lòng chọn ngày mở khóa niêm phong.',
        ]);

        WeddingTimeCapsule::create([
            'wedding_id' => $wedding->id,
            'invitation_id' => $invitation->id,
            'sender_type' => $request->input('sender_type'),
            'sender_name' => trim($request->input('sender_name')),
            'recipient_name' => trim($request->input('recipient_name')),
            'title' => trim($request->input('title')),
            'content' => trim($request->input('content')),
            'milestone_years' => (int) $request->input('milestone_years'),
            'unlock_date' => $request->input('unlock_date'),
            'is_sealed' => true,
            'seal_color' => $request->input('seal_color', 'ruby'),
            'sender_email' => $request->input('sender_email'),
        ]);

        return redirect()->route('account.time_capsule.index', $invitation->id)
            ->with('success', 'Đã niêm phong sáp tâm thư gửi tương lai thành công! Bức thư sẽ được bảo vệ tuyệt đối đến ngày mở.');
    }

    /**
     * Mở niêm phong sáp bức thư (chỉ kích hoạt khi đã tới ngày unlock_date).
     */
    public function unseal(Request $request, int $id, int $capsuleId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $this->authorizeOwner($wedding);

        $capsule = WeddingTimeCapsule::where('wedding_id', $wedding->id)->findOrFail($capsuleId);

        // Kiểm tra an toàn: Không cho phép mở nếu chưa đến ngày
        if (! $capsule->canBeUnlocked()) {
            $msg = "Bức thư này được niêm phong đến ngày {$capsule->unlock_date->format('d/m/Y')}. Chưa thể mở niêm phong sáp lúc này!";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('account.time_capsule.index', $invitation->id)->with('error', $msg);
        }

        $capsule->is_sealed = false;
        $capsule->unlocked_at = now();
        $capsule->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã mở niêm phong sáp thành công!',
                'content' => $capsule->content,
            ]);
        }

        return redirect()->route('account.time_capsule.index', $invitation->id)
            ->with('success', "Đã mở niêm phong sáp thành công! Chúc mừng hai bạn nhân dịp {$capsule->milestoneLabel()}.");
    }

    /**
     * Trang xem danh sách phong bì niêm phong và gửi thư dành cho quan khách (/thiep/{slug}/hop-thoi-gian hoặc /hop-thoi-gian/{slug?}).
     */
    public function showPublic(Request $request, ?string $slug = null): View
    {
        if (! $slug) {
            $customDomain = $request->attributes->get('custom_wedding_domain');
            if ($customDomain && $customDomain->invitation) {
                $slug = $customDomain->invitation->slug;
            }
        }
        abort_unless($slug, 404, 'Không tìm thấy thông tin đám cưới.');

        $invitation = Invitation::with(['wedding.partner', 'template'])->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $isOwner = $invitation->isOwner();

        // Lấy danh sách phong bì (nếu chưa unlock thì ẩn content trên server để bảo mật)
        $capsules = $wedding->timeCapsules()->orderBy('unlock_date')->get()->map(function (WeddingTimeCapsule $c) use ($isOwner) {
            $unlocked = $c->isUnlocked() && $isOwner;

            return [
                'id' => $c->id,
                'sender_type' => $c->sender_type,
                'sender_name' => $c->sender_name,
                'recipient_name' => $c->recipient_name,
                'title' => $c->title,
                'content' => $unlocked ? $c->content : null, // Bảo vệ content nếu chưa unlock
                'milestone_years' => $c->milestone_years,
                'milestone_label' => $c->milestoneLabel(),
                'unlock_date' => $c->unlock_date->format('d/m/Y'),
                'is_sealed' => $c->is_sealed,
                'can_be_unlocked' => $c->canBeUnlocked(),
                'days_remaining' => $c->daysRemaining(),
                'seal_color' => $c->seal_color,
                'seal_hex' => $c->sealColorHex(),
            ];
        });

        $weddingDate = $wedding->wedding_date ? Carbon::parse($wedding->wedding_date) : Carbon::today();
        $suggestedDates = [
            1 => $weddingDate->copy()->addYear()->format('Y-m-d'),
            3 => $weddingDate->copy()->addYears(3)->format('Y-m-d'),
            5 => $weddingDate->copy()->addYears(5)->format('Y-m-d'),
        ];

        return view('client.pages.time-capsule-public', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'capsules' => $capsules,
            'suggestedDates' => $suggestedDates,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Quan khách gửi tâm thư niêm phong sáp cho cặp đôi vào tương lai.
     */
    public function storeGuest(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $request->validate([
            'sender_name' => 'required|string|max:100',
            'recipient_name' => 'required|string|max:100',
            'title' => 'required|string|max:191',
            'content' => 'required|string|max:10000',
            'milestone_years' => 'required|integer|in:1,3,5,10',
            'seal_color' => 'required|in:ruby,gold,emerald,sapphire',
            'sender_email' => 'nullable|email|max:191',
        ]);

        $weddingDate = $wedding->wedding_date ? Carbon::parse($wedding->wedding_date) : Carbon::today();
        $milestone = (int) $request->input('milestone_years', 1);
        $unlockDate = $weddingDate->copy()->addYears($milestone);

        WeddingTimeCapsule::create([
            'wedding_id' => $wedding->id,
            'invitation_id' => $invitation->id,
            'sender_type' => 'guest',
            'sender_name' => trim($request->input('sender_name')),
            'recipient_name' => trim($request->input('recipient_name')),
            'title' => trim($request->input('title')),
            'content' => trim($request->input('content')),
            'milestone_years' => $milestone,
            'unlock_date' => $unlockDate->format('Y-m-d'),
            'is_sealed' => true,
            'seal_color' => $request->input('seal_color', 'ruby'),
            'sender_email' => $request->input('sender_email'),
        ]);

        return redirect()->route('time_capsule.public', $slug)
            ->with('success', "Tâm thư của bạn đã được niêm phong sáp và cất giữ cẩn thận trong Hộp Thời Gian của cô dâu chú rể! Bức thư sẽ được mở vào dịp {$milestone} năm ngày cưới.");
    }

    /**
     * Xóa một bức thư khỏi Hộp Thời Gian.
     */
    public function destroy(Request $request, int $id, int $capsuleId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $this->authorizeOwner($wedding);

        $capsule = WeddingTimeCapsule::where('wedding_id', $wedding->id)->findOrFail($capsuleId);
        $capsule->delete();

        return redirect()->route('account.time_capsule.index', $invitation->id)
            ->with('success', 'Đã xóa bức thư khỏi Hộp Thời Gian.');
    }

    /**
     * Xác thực quyền sở hữu đám cưới chống IDOR.
     */
    private function authorizeOwner(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        if (auth()->check() && auth()->user()->partner && (int) $wedding->partner_id === (int) auth()->user()->partner->id) {
            return;
        }

        if (auth()->check() && auth()->user()->isAdmin()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        abort(404);
    }
}
