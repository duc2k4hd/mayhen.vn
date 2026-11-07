<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\WeddingWish;
use App\Services\WeddingMusicService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode($invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return back()->with('error', 'Thiệp cưới của bạn hiện đang bị tạm khóa bởi Quản trị viên. Vui lòng liên hệ hỗ trợ để được mở khóa.');
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

    /**
     * Hiển thị trang cấu hình chi tiết thiệp cưới.
     */
    public function settings(Request $request, int $invitationId, WeddingMusicService $musicService): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode($invitation->custom_config, true) ?? []);

        $musics = $musicService->getCatalog();

        return view('client.pages.account.settings', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'config' => $config,
            'musics' => $musics,
        ]);
    }

    /**
     * Cập nhật các thiết lập của thiệp cưới.
     */
    public function updateSettings(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:80',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('invitations', 'slug')->ignore($invitation->id),
            ],
            'title' => ['nullable', 'string', 'max:190'],
            'is_published' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'max:20'],
            'allow_wishes' => ['nullable', 'boolean'],
            'allow_rsvp' => ['nullable', 'boolean'],
            'rsvp_deadline' => ['nullable', 'date'],
            'music_url' => ['nullable', 'string', 'max:500'],
            'music_autoplay' => ['nullable', 'boolean'],
            'og_title' => ['nullable', 'string', 'max:190'],
            'og_description' => ['nullable', 'string', 'max:300'],
        ], [
            'slug.required' => 'Vui lòng nhập đường dẫn thiệp cưới.',
            'slug.min' => 'Đường dẫn thiệp phải có ít nhất 3 ký tự.',
            'slug.max' => 'Đường dẫn thiệp không được vượt quá 80 ký tự.',
            'slug.regex' => 'Đường dẫn chỉ được chứa chữ cái thường (a-z), chữ số (0-9) và dấu gạch nối (-).',
            'slug.unique' => 'Đường dẫn thiệp này đã được cặp đôi khác sử dụng, vui lòng chọn đường dẫn khác.',
        ]);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode($invitation->custom_config, true) ?? []);

        // Cập nhật các trường cấu hình
        $config['password'] = $request->filled('password') ? trim((string) $request->input('password')) : null;
        $config['allow_wishes'] = $request->boolean('allow_wishes', true);
        $config['allow_rsvp'] = $request->boolean('allow_rsvp', true);
        $config['rsvp_deadline'] = $request->input('rsvp_deadline') ?: null;
        $config['music_url'] = $request->input('music_url') ?: null;
        $config['music_autoplay'] = $request->boolean('music_autoplay', true);
        $config['og_title'] = $request->input('og_title') ?: null;
        $config['og_description'] = $request->input('og_description') ?: null;

        $invitation->slug = Str::slug($validated['slug']);
        $invitation->title = $validated['title'] ?? ($invitation->title ?: 'Thiệp Cưới Chính Thức');
        $invitation->custom_config = $config;

        // Nếu thiệp không bị khóa bởi Admin, cho phép cập nhật trạng thái xuất bản
        if (empty($config['is_locked'])) {
            $isPublished = $request->boolean('is_published');
            $invitation->is_published = $isPublished;
            if ($isPublished && ! $invitation->published_at) {
                $invitation->published_at = now();
            }
        }

        $invitation->save();

        return redirect()->route('account.invitations.settings', $invitation->id)
            ->with('success', 'Đã lưu cài đặt thiệp cưới thành công!');
    }

    /**
     * Nhân bản thiệp cưới để tạo phiên bản sao lưu hoặc thiệp riêng cho nhà trai/gái.
     */
    public function duplicate(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $newSlug = Str::limit($invitation->slug, 60, '').'-sao-chep-'.Str::lower(Str::random(5));

        $newInvitation = $invitation->replicate([
            'slug',
            'is_published',
            'published_at',
        ]);

        $newInvitation->slug = $newSlug;
        $newInvitation->title = ($invitation->title ?? 'Thiệp Cưới').' (Bản Sao)';
        $newInvitation->is_published = false;
        $newInvitation->published_at = null;
        $newInvitation->save();

        return redirect()->route('account.invitations')
            ->with('success', "Đã nhân bản thiệp cưới thành công! Bản sao mới có đường dẫn: {$newSlug}");
    }

    /**
     * Xóa thiệp cưới.
     */
    public function destroy(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $invitation->delete();

        return redirect()->route('account.invitations')
            ->with('success', 'Đã xóa thiệp cưới thành công khỏi tài khoản.');
    }

    /**
     * Kiểm tra quyền sở hữu thiệp cưới của người dùng hiện tại.
     */
    private function authorizeInvitationOwner(Invitation $invitation): void
    {
        $wedding = $invitation->wedding;
        if (! $wedding) {
            abort(404);
        }

        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        abort(404);
    }
}
