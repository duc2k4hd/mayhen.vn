<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\WeddingWish;
use App\Services\TwoFamilyApprovalService;
use App\Services\VendorBriefService;
use App\Services\WeddingConsistencyGuardianService;
use App\Services\WeddingMusicService;
use App\Services\WeddingWeekReadinessService;
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
    public function invitations(Request $request, WeddingConsistencyGuardianService $guardian): View
    {
        $user = $request->user();
        $weddingIds = $user->weddings()->pluck('id');

        $invitations = Invitation::with(['wedding.guests', 'template'])
            ->whereIn('wedding_id', $weddingIds)
            ->latest()
            ->paginate(9);

        $readinessMap = [];
        foreach ($invitations as $inv) {
            if ($inv->wedding) {
                $readinessMap[$inv->id] = $guardian->calculateReadinessChecklist($inv->wedding, $inv);
            }
        }

        return view('client.pages.account.invitations', [
            'user' => $user,
            'invitations' => $invitations,
            'readinessMap' => $readinessMap,
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
        $familyApprovals = app(TwoFamilyApprovalService::class)->getOrCreateApprovalTokens($invitation);

        // Lấy danh sách các nhóm khách mời hiện có của đám cưới này kết hợp nhóm gợi ý chuẩn
        $defaultGroups = ['Bạn Bè', 'Gia Đình & Họ Hàng', 'Đồng Nghiệp', 'Khách VIP', 'Bà Con Lối Xóm'];
        $existingGroups = $wedding
            ? $wedding->guests()->whereNotNull('group_name')->where('group_name', '!=', '')->distinct()->pluck('group_name')->all()
            : [];
        $availableGroups = array_values(array_unique(array_filter(array_merge($defaultGroups, $existingGroups))));

        return view('client.pages.account.settings', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'config' => $config,
            'musics' => $musics,
            'availableGroups' => $availableGroups,
            'familyApprovals' => $familyApprovals,
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
            'persona_enabled' => ['nullable', 'boolean'],
            'persona_messages' => ['nullable', 'array'],
            'persona_messages.groups' => ['nullable', 'array'],
            'persona_messages.groups.*.title' => ['nullable', 'string', 'max:190'],
            'persona_messages.groups.*.subtitle' => ['nullable', 'string', 'max:190'],
            'persona_messages.groups.*.content' => ['nullable', 'string', 'max:2000'],
            'parking_guide' => ['nullable', 'array'],
            'parking_guide.enabled' => ['nullable', 'boolean'],
            'parking_guide.car_parking' => ['nullable', 'string', 'max:500'],
            'parking_guide.motorbike_parking' => ['nullable', 'string', 'max:500'],
            'parking_guide.fee_note' => ['nullable', 'string', 'max:500'],
            'parking_guide.entrance_guide' => ['nullable', 'string', 'max:500'],
            'parking_guide.map_tip' => ['nullable', 'string', 'max:500'],
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

        // Xử lý và làm sạch cấu hình Cá nhân hóa Lời ngỏ theo Nhóm Khách (Invitation Persona)
        $rawPersonaGroups = $request->input('persona_messages.groups', []);
        $cleanedPersonaGroups = [];
        if (is_array($rawPersonaGroups)) {
            foreach ($rawPersonaGroups as $gName => $gData) {
                if (! is_string($gName) || trim($gName) === '' || ! is_array($gData)) {
                    continue;
                }
                $cleanGroupName = strip_tags(trim($gName));
                $title = isset($gData['title']) ? strip_tags(trim((string) $gData['title'])) : null;
                $subtitle = isset($gData['subtitle']) ? strip_tags(trim((string) $gData['subtitle'])) : null;
                $content = isset($gData['content']) ? strip_tags(trim((string) $gData['content'])) : null;

                if (! empty($title) || ! empty($subtitle) || ! empty($content)) {
                    $cleanedPersonaGroups[$cleanGroupName] = [
                        'title' => $title ?: null,
                        'subtitle' => $subtitle ?: null,
                        'content' => $content ?: null,
                    ];
                }
            }
        }

        $config['persona_messages'] = [
            'enabled' => $request->boolean('persona_enabled'),
            'groups' => $cleanedPersonaGroups,
        ];

        // Xử lý và lưu cấu hình Hướng Dẫn Vị Trí & Bãi Đỗ Xe Thông Minh (TASK-VAL-05)
        $rawParking = $request->input('parking_guide', []);
        $config['parking_guide'] = [
            'enabled' => $request->boolean('parking_guide.enabled'),
            'car_parking' => isset($rawParking['car_parking']) ? strip_tags(trim((string) $rawParking['car_parking'])) : null,
            'motorbike_parking' => isset($rawParking['motorbike_parking']) ? strip_tags(trim((string) $rawParking['motorbike_parking'])) : null,
            'fee_note' => isset($rawParking['fee_note']) ? strip_tags(trim((string) $rawParking['fee_note'])) : null,
            'entrance_guide' => isset($rawParking['entrance_guide']) ? strip_tags(trim((string) $rawParking['entrance_guide'])) : null,
            'map_tip' => isset($rawParking['map_tip']) ? strip_tags(trim((string) $rawParking['map_tip'])) : null,
        ];

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
     * Khôi phục khẩn cấp bản xuất bản an toàn trước đó (Emergency Rollback - TASK-VAL-11).
     */
    public function rollback(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $snapshot = $config['_previous_published_snapshot'] ?? null;
        if (! $snapshot || ! is_array($snapshot) || empty($snapshot['wedding'])) {
            return back()->with('error', 'Chưa có bản phát hành an toàn trước đó để khôi phục.');
        }

        DB::transaction(function () use ($invitation, $snapshot) {
            $wedding = $invitation->wedding;
            if ($wedding && ! empty($snapshot['wedding'])) {
                $wData = $snapshot['wedding'];
                $wedding->update([
                    'groom_name' => $wData['groom_name'] ?? $wedding->groom_name,
                    'bride_name' => $wData['bride_name'] ?? $wedding->bride_name,
                    'groom_parents' => $wData['groom_parents'] ?? null,
                    'bride_parents' => $wData['bride_parents'] ?? null,
                    'wedding_date' => $wData['wedding_date'] ?? $wedding->wedding_date,
                    'venue_name' => $wData['venue_name'] ?? $wedding->venue_name,
                    'venue_address' => $wData['venue_address'] ?? $wedding->venue_address,
                    'story' => $wData['story'] ?? null,
                    'cover_photo_url' => $wData['cover_photo_url'] ?? null,
                    'music_url' => $wData['music_url'] ?? null,
                    'bank_name' => $wData['bank_name'] ?? null,
                    'bank_account_number' => $wData['bank_account_number'] ?? null,
                    'bank_account_name' => $wData['bank_account_name'] ?? null,
                    'extra_data' => $wData['extra_data'] ?? [],
                ]);
            }

            $restoredConfig = is_array($snapshot['custom_config']) ? $snapshot['custom_config'] : [];
            unset($restoredConfig['_previous_published_snapshot']);
            $restoredConfig['last_rollback_at'] = now()->toIso8601String();

            $invitation->update([
                'template_id' => $snapshot['template_id'] ?? $invitation->template_id,
                'custom_config' => $restoredConfig,
                'revision_id' => (int) $invitation->revision_id + 1,
            ]);
        });

        return back()->with('success', 'Đã khôi phục thành công thiệp cưới về phiên bản an toàn trước đó!');
    }

    /**
     * Bộ Dự Phòng Ngày Cưới 1 Chạm (Wedding Day Resilience Kit - TASK-VAL-03).
     */
    public function resilienceKit(int $invitationId): View
    {
        $invitation = Invitation::with(['wedding.tables.guests.response', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);
        $wedding = $invitation->wedding;

        $guests = $wedding->guests()->with('response')->orderBy('table_name')->orderBy('name')->get();
        $tables = $wedding->tables()->with(['guests.response'])->orderBy('name')->get();

        $totalGuests = $guests->count();
        $totalExpectedAttendees = (int) $guests->sum(fn (Guest $g) => $g->actualAttendeesCount());
        $attendingGuests = $guests->filter(fn (Guest $g) => $g->response && $g->response->attending_status === 'attending')->count();

        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);
        $checkinPin = $config['checkin_pin'] ?? '123456';

        return view('client.pages.account.resilience-kit', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'guests' => $guests,
            'tables' => $tables,
            'totalGuests' => $totalGuests,
            'totalExpectedAttendees' => $totalExpectedAttendees,
            'attendingGuests' => $attendingGuests,
            'checkinPin' => $checkinPin,
        ]);
    }

    /**
     * Bản Hướng Dẫn Tác Nghiệp Tối Giản Cho MC, Nhà Hàng & Photographer (TASK-VAL-15).
     */
    public function vendorBrief(Request $request, int $invitationId, VendorBriefService $briefService): View
    {
        $user = $request->user();
        $invitation = Invitation::with(['wedding.guests', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $brief = $briefService->generate($invitation->wedding, $invitation);

        return view('client.pages.account.vendor-brief', [
            'user' => $user,
            'invitation' => $invitation,
            'wedding' => $invitation->wedding,
            'brief' => $brief,
        ]);
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

    /**
     * Màn hình Bảng Radar Sẵn Sàng Tuần Lễ Cưới & Cam Kết Hỗ Trợ Kỹ Thuật (TASK-VAL-28).
     */
    public function weddingWeekReadiness(Request $request, int $invitationId, WeddingWeekReadinessService $readinessService): View
    {
        $user = $request->user();
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $radar = $readinessService->assessReadiness($invitation);

        return view('client.pages.account.wedding-week-readiness', [
            'user' => $user,
            'invitation' => $invitation,
            'wedding' => $invitation->wedding,
            'radar' => $radar,
        ]);
    }
}
