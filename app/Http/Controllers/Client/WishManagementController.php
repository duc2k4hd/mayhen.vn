<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\WeddingWish;
use App\Rules\NoProfanity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Cho phép hiển thị toàn bộ lời chúc đang tạm ẩn cùng một lúc.
     */
    public function approveAll(int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeInvitationOwner($wedding);

        $updatedCount = $wedding->wishes()->where('is_approved', false)->update(['is_approved' => true]);

        return back()->with('success', "Đã cho phép hiển thị {$updatedCount} lời chúc mừng trên thiệp cưới thành công!");
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
     * Xuất danh sách Sổ Lưu Bút ra file CSV UTF-8 BOM cho Excel.
     */
    public function exportCsv(int $invitationId): StreamedResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeInvitationOwner($wedding);

        $wishes = $wedding->wishes()->latest()->get();
        $filename = 'so-luu-but-'.Str::slug($wedding->groom_name.'-'.$wedding->bride_name).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($wishes) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'STT',
                'Người Chúc Phúc',
                'Nội Dung Lời Chúc',
                'Lời Cảm Ơn Của Cặp Đôi',
                'Trạng Thái Hiển Thị',
                'Thời Gian Gửi',
                'Thời Gian Cảm Ơn',
            ]);

            $index = 1;
            foreach ($wishes as $w) {
                fputcsv($handle, [
                    $index++,
                    $w->name,
                    $w->message,
                    $w->reply ?? '',
                    $w->is_approved ? 'Hiển thị' : 'Tạm ẩn',
                    $w->created_at ? $w->created_at->format('d/m/Y H:i') : '',
                    $w->replied_at ? $w->replied_at->format('d/m/Y H:i') : '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Trang In Kỷ Yếu Sổ Lưu Bút (Keepsake Guestbook Print View).
     */
    public function print(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeInvitationOwner($wedding);

        $query = $wedding->wishes();

        $statusFilter = $request->query('status', 'approved');
        if ($statusFilter === 'approved') {
            $query->where('is_approved', true);
        }

        $wishes = $query->oldest()->get();

        return view('client.pages.account.wishes-print', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'wishes' => $wishes,
            'statusFilter' => $statusFilter,
            'totalCount' => $wedding->wishes()->count(),
            'approvedCount' => $wedding->wishes()->where('is_approved', true)->count(),
        ]);
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
