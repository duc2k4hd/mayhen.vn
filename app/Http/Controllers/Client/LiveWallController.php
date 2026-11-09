<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Models\WeddingWish;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LiveWallController extends Controller
{
    /**
     * Màn hình trình chiếu lễ cưới trực tiếp (Live Wedding Projection Wall).
     */
    public function show(Request $request, ?string $slug = null): View|RedirectResponse
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
        abort_unless($wedding, 404, 'Không tìm thấy thông tin đám cưới.');

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return view('client.pages.invitation-locked', [
                'invitation' => $invitation,
                'lockReason' => $config['lock_reason'] ?? 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
            ]);
        }

        $wallConfig = $config['projection_wall'] ?? [];

        // 1. Kiểm tra khóa bảo mật PIN nếu được thiết lập
        $isOwner = $invitation->isOwner();
        if (! empty($wallConfig['pin']) && ! $isOwner) {
            $sessionKey = "unlocked_live_wall_{$invitation->id}";
            if (! session($sessionKey)) {
                return view('client.pages.live-wall-pin', [
                    'invitation' => $invitation,
                    'wedding' => $wedding,
                ]);
            }
        }

        // 2. Thu thập danh sách ảnh ban đầu (Ảnh cưới chính + Ảnh khách chụp đã duyệt)
        $photos = $this->collectProjectionPhotos($wedding);

        // 3. Lời chúc đã duyệt mới nhất
        $wishes = $wedding->wishes()
            ->where('is_approved', true)
            ->latest()
            ->limit(30)
            ->get();

        $formattedWishes = $wishes->map(function (WeddingWish $w) {
            return [
                'id' => $w->id,
                'name' => $w->sender_name,
                'message' => $w->message,
                'time_ago' => $w->created_at ? Carbon::parse($w->created_at)->diffForHumans() : 'Vừa xong',
            ];
        });

        // 4. Tạo mã QR góc màn hình (trỏ về trang gửi lời chúc / thiệp hoặc photo drop)
        $qrTargetUrl = url('/thiep/'.$invitation->slug);
        $qrCodeSvg = QrCodeService::svg($qrTargetUrl, 160, '#FAF8F5', '#1F1D1A');

        return view('client.pages.live-wall', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'wallConfig' => $wallConfig,
            'photos' => $photos,
            'wishes' => $wishes,
            'formattedWishes' => $formattedWishes,
            'qrCodeSvg' => $qrCodeSvg,
            'qrTargetUrl' => $qrTargetUrl,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Xác thực mã PIN để mở màn hình trình chiếu.
     */
    public function verifyPin(Request $request, string $slug): View|RedirectResponse
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return view('client.pages.invitation-locked', [
                'invitation' => $invitation,
                'lockReason' => $config['lock_reason'] ?? 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
            ]);
        }

        $wallConfig = $config['projection_wall'] ?? [];
        $expectedPin = (string) ($wallConfig['pin'] ?? '');

        $inputPin = trim((string) $request->input('pin'));

        if ($expectedPin !== '' && $inputPin === $expectedPin) {
            session(["unlocked_live_wall_{$invitation->id}" => true]);

            return redirect()->route('live_wall.show', $slug);
        }

        return redirect()->route('live_wall.show', $slug)
            ->withErrors(['pin' => 'Mã PIN bảo vệ không chính xác.']);
    }

    /**
     * API Realtime Live Feed (Polling 8-10 giây) cập nhật ảnh & lời chúc mới nhất.
     */
    public function liveFeed(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with(['wedding'])->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        $photos = $this->collectProjectionPhotos($wedding);

        $wishes = $wedding->wishes()
            ->where('is_approved', true)
            ->latest()
            ->limit(30)
            ->get()
            ->map(function (WeddingWish $wish) {
                return [
                    'id' => $wish->id,
                    'name' => $wish->sender_name,
                    'message' => $wish->message,
                    'time_ago' => $wish->created_at ? Carbon::parse($wish->created_at)->diffForHumans() : 'Vừa xong',
                ];
            });

        $approvedPhotosCount = $wedding->guestPhotos()->approved()->count();
        $approvedWishesCount = $wedding->wishes()->where('is_approved', true)->count();

        return response()->json([
            'success' => true,
            'photos' => $photos,
            'wishes' => $wishes,
            'stats' => [
                'photos_count' => $approvedPhotosCount,
                'wishes_count' => $approvedWishesCount,
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Giao diện cấu hình Màn Chiếu của Cặp Đôi (/tai-khoan/thiep/{id}/man-hinh-chieu).
     */
    public function settings(int $id): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($id);
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $this->authorizeOwner($wedding);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $wallConfig = $config['projection_wall'] ?? [
            'theme' => 'dark',
            'display_mode' => 'mixed',
            'speed' => 6,
            'show_qr' => true,
            'pin' => '',
        ];

        return view('client.pages.account.live-wall-settings', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'wallConfig' => $wallConfig,
        ]);
    }

    /**
     * Lưu cấu hình Màn Chiếu của Cặp Đôi.
     */
    public function updateSettings(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::with(['wedding'])->findOrFail($id);
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $this->authorizeOwner($wedding);

        $request->validate([
            'theme' => 'required|in:dark,light',
            'display_mode' => 'required|in:mixed,photos_only,wishes_only',
            'speed' => 'required|integer|in:4,6,8,12',
            'show_qr' => 'nullable|boolean',
            'pin' => 'nullable|string|max:10|regex:/^[0-9]*$/',
        ], [
            'pin.regex' => 'Mã PIN bảo vệ chỉ bao gồm các chữ số (ví dụ: 1234).',
        ]);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $config['projection_wall'] = [
            'theme' => $request->input('theme', 'dark'),
            'display_mode' => $request->input('display_mode', 'mixed'),
            'speed' => (int) $request->input('speed', 6),
            'show_qr' => $request->boolean('show_qr', true),
            'pin' => trim((string) $request->input('pin', '')),
        ];

        $invitation->custom_config = $config;
        $invitation->save();

        return redirect()->route('live_wall.settings', $invitation->id)
            ->with('success', 'Đã lưu cấu hình Bức Tường Trình Chiếu thành công!');
    }

    /**
     * Tổng hợp danh sách ảnh cưới và ảnh khách đã duyệt để trình chiếu.
     */
    private function collectProjectionPhotos(Wedding $wedding): array
    {
        $photos = [];

        // 1. Ảnh bìa chính của đám cưới
        if (! empty($wedding->cover_photo_url)) {
            $photos[] = [
                'url' => $wedding->cover_photo_url,
                'author' => $wedding->groom_name.' & '.$wedding->bride_name,
                'type' => 'wedding_cover',
                'caption' => 'Khoảnh khắc hạnh phúc của đôi uyên ương',
            ];
        }

        // 2. Ảnh album từ extra_data của wedding nếu có
        $extraData = is_array($wedding->extra_data)
            ? $wedding->extra_data
            : (json_decode((string) $wedding->extra_data, true) ?? []);

        if (! empty($extraData['gallery']) && is_array($extraData['gallery'])) {
            foreach ($extraData['gallery'] as $idx => $imgUrl) {
                if (! empty($imgUrl)) {
                    $photos[] = [
                        'url' => $imgUrl,
                        'author' => $wedding->groom_name.' & '.$wedding->bride_name,
                        'type' => 'wedding_album',
                        'caption' => "Album kỷ niệm #{$idx}",
                    ];
                }
            }
        }

        // 3. Ảnh khách chụp tại bàn tiệc đã được duyệt (Guest Photo Drop)
        $guestPhotos = $wedding->guestPhotos()
            ->approved()
            ->latest()
            ->limit(40)
            ->get();

        foreach ($guestPhotos as $gp) {
            $photos[] = [
                'url' => $gp->photo_url,
                'author' => $gp->uploader_name ?: 'Khách mời tiệc cưới',
                'type' => 'guest_photo',
                'caption' => $gp->caption ?: 'Ảnh chụp tự nhiên tại bàn tiệc',
            ];
        }

        return $photos;
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
