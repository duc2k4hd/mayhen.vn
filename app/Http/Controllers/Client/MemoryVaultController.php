<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Rules\NoProfanity;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemoryVaultController extends Controller
{
    /**
     * Hiển thị Bảo Tàng Kỷ Niệm Ngày Cưới Số (Digital Memory Vault).
     */
    public function show(Request $request, string $slug): View|RedirectResponse
    {
        $invitation = Invitation::with(['wedding', 'template'])
            ->where('slug', $slug)
            ->firstOrFail();

        $wedding = $invitation->wedding;
        abort_unless($wedding, 404, 'Không tìm thấy thông tin đám cưới.');

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $vaultConfig = $config['memory_vault'] ?? [];
        $isOwner = $invitation->isOwner();

        // Kiểm tra điều kiện mở Két Kỷ Niệm:
        // Cặp đôi chủ động bật hoặc đám cưới đã diễn ra và được phép truy cập
        $isVaultEnabled = ! empty($vaultConfig['enabled']) || ($wedding->wedding_date && $wedding->wedding_date->isPast());

        if (! $isVaultEnabled && ! $isOwner) {
            // Nếu chưa mở vault và không phải chủ thiệp, chuyển hướng về thiệp cưới công khai
            return redirect()->route('invitation.show', $slug);
        }

        // Tính số ngày kỷ niệm hôn nhân
        $anniversaryDays = null;
        $isAnniversaryPassed = false;
        if ($wedding->wedding_date) {
            $now = Carbon::now()->startOfDay();
            $weddingDay = $wedding->wedding_date->copy()->startOfDay();
            if ($now->gte($weddingDay)) {
                $anniversaryDays = (int) $weddingDay->diffInDays($now);
                $isAnniversaryPassed = true;
            } else {
                $anniversaryDays = (int) $now->diffInDays($weddingDay);
                $isAnniversaryPassed = false;
            }
        }

        // Thống kê kỷ niệm hạnh phúc
        $totalWishesCount = $wedding->wishes()->where('is_approved', true)->count();
        $totalGuestsAttended = (int) $wedding->guests()
            ->whereNotNull('checked_in_at')
            ->get()
            ->sum(function (Guest $g) {
                return $g->checked_in_attendees ?: $g->actualAttendeesCount();
            });
        if ($totalGuestsAttended === 0) {
            $totalGuestsAttended = (int) ($wedding->guestResponses()->where('attending_status', 'attending')->sum('adults_count')
                + $wedding->guestResponses()->where('attending_status', 'attending')->sum('children_count'));
        }

        // Danh sách lời chúc đã duyệt để trưng bày trong Sổ Lưu Bút Kỷ Niệm
        $wishes = $wedding->wishes()
            ->where('is_approved', true)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Ảnh kỷ niệm do khách mời chụp đã duyệt
        $guestPhotos = $wedding->guestPhotos()
            ->approved()
            ->latest()
            ->limit(12)
            ->get();
        $totalGuestPhotosCount = $wedding->guestPhotos()->approved()->count();

        // Thư cảm ơn tri ân
        $thankYouLetter = ! empty($vaultConfig['thank_you_letter'])
            ? $vaultConfig['thank_you_letter']
            : 'Chúng mình xin gửi lời cảm tạ chân thành và sâu sắc nhất tới gia đình, người thân và toàn thể bạn bè thân quý đã dành thời gian quý báu đến chung vui, nâng ly chúc phúc và gửi gắm những lời chúc tốt đẹp nhất trong ngày trọng đại của hai đứa. Sự hiện diện và tình cảm ấm áp của mọi người là món quà vô giá mà chúng mình sẽ mãi trân trọng và khắc ghi suốt cuộc đời này.';

        $coverImage = ! empty($vaultConfig['cover_image'])
            ? $vaultConfig['cover_image']
            : ($wedding->cover_photo_url ?: null);

        return view('client.pages.memory-vault', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'wishes' => $wishes,
            'guestPhotos' => $guestPhotos,
            'totalGuestPhotosCount' => $totalGuestPhotosCount,
            'totalWishesCount' => $totalWishesCount,
            'totalGuestsAttended' => $totalGuestsAttended,
            'anniversaryDays' => $anniversaryDays,
            'isAnniversaryPassed' => $isAnniversaryPassed,
            'thankYouLetter' => $thankYouLetter,
            'coverImage' => $coverImage,
            'vaultConfig' => $vaultConfig,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Giao diện cấu hình Két Kỷ Niệm dành cho cặp đôi.
     */
    public function settings(int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $vaultConfig = $config['memory_vault'] ?? [];

        return view('client.pages.account.memory-vault-settings', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'vaultConfig' => $vaultConfig,
        ]);
    }

    /**
     * Cập nhật cấu hình Két Kỷ Niệm.
     */
    public function updateSettings(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'thank_you_letter' => ['nullable', 'string', 'max:3000', new NoProfanity],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'allow_guest_wishes' => ['nullable', 'boolean'],
        ]);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $config['memory_vault'] = [
            'enabled' => $request->boolean('enabled'),
            'thank_you_letter' => $validated['thank_you_letter'] ? strip_tags(trim($validated['thank_you_letter'])) : null,
            'cover_image' => $validated['cover_image'] ? trim($validated['cover_image']) : null,
            'allow_guest_wishes' => $request->boolean('allow_guest_wishes'),
            'updated_at' => now()->toIso8601String(),
        ];

        $invitation->update(['custom_config' => $config]);

        return back()->with('success', 'Đã lưu cấu hình Két Lưu Trữ Kỷ Niệm Số thành công!');
    }

    /**
     * Cuốn Sách Kỷ Yếu Lưu Bút Dàn Trang Chuẩn In Ấn / PDF (Editorial Keepsake Book).
     */
    public function keepsakeBook(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $vaultConfig = $config['memory_vault'] ?? [];

        $thankYouLetter = ! empty($vaultConfig['thank_you_letter'])
            ? $vaultConfig['thank_you_letter']
            : 'Chúng mình xin gửi lời cảm tạ chân thành và sâu sắc nhất tới gia đình, người thân và toàn thể bạn bè thân quý đã dành thời gian quý báu đến chung vui và chúc phúc.';

        // Toàn bộ lời chúc đã duyệt theo thứ tự thời gian
        $wishes = $wedding->wishes()
            ->where('is_approved', true)
            ->oldest()
            ->get();

        return view('client.pages.keepsake-book', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'wishes' => $wishes,
            'thankYouLetter' => $thankYouLetter,
            'totalCount' => $wishes->count(),
        ]);
    }

    private function authorizeOwner(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        abort(404);
    }
}
