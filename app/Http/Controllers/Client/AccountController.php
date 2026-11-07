<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\WeddingWish;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Dashboard tổng quan tài khoản cặp đôi.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $weddingIds = $user->weddings()->pluck('id');

        $invitations = Invitation::with(['wedding', 'template'])
            ->whereIn('wedding_id', $weddingIds)
            ->latest()
            ->get();

        $totalInvitations = $invitations->count();
        $totalGuests = Guest::whereIn('wedding_id', $weddingIds)->sum('expected_attendees');
        $attendingGuests = (int) GuestResponse::whereIn('wedding_id', $weddingIds)
            ->where('attending_status', 'attending')
            ->sum(DB::raw('adults_count + children_count'));
        $totalWishes = WeddingWish::whereIn('wedding_id', $weddingIds)->count();

        $activeInvitation = $invitations->first();

        $recentWishes = WeddingWish::with('wedding')
            ->whereIn('wedding_id', $weddingIds)
            ->latest()
            ->limit(5)
            ->get();

        return view('client.pages.account.dashboard', [
            'user' => $user,
            'invitations' => $invitations,
            'activeInvitation' => $activeInvitation,
            'totalInvitations' => $totalInvitations,
            'totalGuests' => $totalGuests,
            'attendingGuests' => $attendingGuests,
            'totalWishes' => $totalWishes,
            'recentWishes' => $recentWishes,
        ]);
    }

    /**
     * Danh sách tất cả thiệp cưới của người dùng.
     */
    public function invitations(Request $request): View
    {
        $user = $request->user();
        $weddingIds = $user->weddings()->pluck('id');

        $invitations = Invitation::with(['wedding.guests', 'template'])
            ->whereIn('wedding_id', $weddingIds)
            ->latest()
            ->paginate(9);

        return view('client.pages.account.invitations', [
            'user' => $user,
            'invitations' => $invitations,
        ]);
    }

    /**
     * Bật / Tắt trạng thái xuất bản thiệp cưới.
     */
    public function togglePublish(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);

        if (! $invitation->wedding || (int) $invitation->wedding->user_id !== (int) $request->user()->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa thiệp cưới này.');
        }

        $newStatus = ! $invitation->is_published;
        $invitation->update([
            'is_published' => $newStatus,
            'published_at' => $newStatus ? ($invitation->published_at ?? now()) : $invitation->published_at,
        ]);

        $message = $newStatus
            ? 'Đã xuất bản thiệp cưới thành công! Khách mời giờ đây có thể truy cập qua liên kết công khai.'
            : 'Đã tạm dừng xuất bản thiệp cưới. Thiệp đã được chuyển về chế độ riêng tư.';

        return back()->with('success', $message);
    }

    /**
     * Danh sách đơn hàng & lịch sử gói cước của người dùng.
     */
    public function orders(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders()
            ->with(['plan', 'invitation.template'])
            ->latest()
            ->paginate(10);

        return view('client.pages.account.orders', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }

    /**
     * Trang xem thông tin cá nhân & đổi mật khẩu.
     */
    public function profile(Request $request): View
    {
        return view('client.pages.account.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Cập nhật thông tin hồ sơ cá nhân.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s\-()]{8,20}$/'],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng bởi tài khoản khác.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.',
        ]);

        $user->update($validated);

        return back()->with('success', 'Đã cập nhật thông tin hồ sơ thành công!');
    }

    /**
     * Đổi mật khẩu tài khoản.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Đổi mật khẩu tài khoản thành công!');
    }
}
