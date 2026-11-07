<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\WeddingWish;
use App\Rules\NoProfanity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishManagementController extends Controller
{
    /**
     * Hiển thị danh sách sổ lưu bút và lời chúc của thiệp cưới.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeInvitationOwner($wedding);

        // Thống kê tổng quan
        $totalWishes = $wedding->wishes()->count();
        $repliedCount = $wedding->wishes()->whereNotNull('reply')->count();
        $pendingReplyCount = $totalWishes - $repliedCount;
        $hiddenCount = $wedding->wishes()->where('is_approved', false)->count();

        // Xử lý bộ lọc và tìm kiếm
        $query = $wedding->wishes();

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $filterStatus = $request->query('status');
        if ($filterStatus === 'replied') {
            $query->whereNotNull('reply');
        } elseif ($filterStatus === 'pending') {
            $query->whereNull('reply');
        } elseif ($filterStatus === 'hidden') {
            $query->where('is_approved', false);
        }

        $wishes = $query->latest()->paginate(12)->withQueryString();

        return view('client.pages.account.wishes', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'wishes' => $wishes,
            'totalWishes' => $totalWishes,
            'repliedCount' => $repliedCount,
            'pendingReplyCount' => $pendingReplyCount,
            'hiddenCount' => $hiddenCount,
            'filterSearch' => $search,
            'filterStatus' => $filterStatus,
        ]);
    }

    /**
     * Gửi hoặc cập nhật câu trả lời cảm ơn tới khách mời.
     */
    public function reply(Request $request, int $invitationId, int $wishId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeInvitationOwner($wedding);

        $request->validate([
            'reply' => ['required', 'string', 'max:1000', new NoProfanity],
        ], [
            'reply.required' => 'Vui lòng nhập nội dung phản hồi cảm ơn khách mời.',
            'reply.max' => 'Nội dung phản hồi không được vượt quá 1000 ký tự.',
        ]);

        $wish = WeddingWish::where('wedding_id', $wedding->id)->findOrFail($wishId);

        $wish->update([
            'reply' => $request->input('reply'),
            'replied_at' => now(),
        ]);

        return back()->with('success', "Đã gửi phản hồi cảm ơn tới khách mời \"{$wish->name}\" thành công!");
    }

    /**
     * Bật / Tắt ẩn hiển thị lời chúc trên thiệp cưới công khai.
     */
    public function toggleVisibility(int $invitationId, int $wishId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeInvitationOwner($wedding);

        $wish = WeddingWish::where('wedding_id', $wedding->id)->findOrFail($wishId);

        $newStatus = ! $wish->is_approved;
        $wish->update(['is_approved' => $newStatus]);

        $message = $newStatus
            ? "Đã cho phép hiển thị lời chúc của \"{$wish->name}\" trên thiệp cưới."
            : "Đã tạm ẩn lời chúc của \"{$wish->name}\" khỏi thiệp cưới công khai.";

        return back()->with('success', $message);
    }

    /**
     * Xóa vĩnh viễn một lời chúc khỏi sổ lưu bút.
     */
    public function destroy(int $invitationId, int $wishId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeInvitationOwner($wedding);

        $wish = WeddingWish::where('wedding_id', $wedding->id)->findOrFail($wishId);
        $wish->delete();

        return back()->with('success', 'Đã xóa lời chúc khỏi sổ lưu bút thành công.');
    }

    /**
     * Xác thực quyền sở hữu thiệp cưới của người dùng đăng nhập.
     */
    private function authorizeInvitationOwner($wedding): void
    {
        if (! auth()->check() || (int) $wedding->user_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền quản lý sổ lưu bút của thiệp cưới này.');
        }
    }
}

