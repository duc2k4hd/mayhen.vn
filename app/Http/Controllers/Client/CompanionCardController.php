<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\CompanionCardService;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompanionCardController extends Controller
{
    /**
     * Màn hình Studio Thiết Kế Thẻ Kẹp Thiệp Giấy (Printable Companion & QR Insert Card Studio - IDEA-A06).
     */
    public function index(int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $config = CompanionCardService::resolveCardConfig($invitation);
        $sizes = CompanionCardService::SIZES;
        $palettes = CompanionCardService::PALETTES;
        $motifs = CompanionCardService::MOTIFS;
        $guests = CompanionCardService::getGuestsForPrinting($invitation, 50);

        // Sinh SVG QR xem trước
        $targetUrl = url("/thiep/{$invitation->slug}");
        $paletteMeta = $palettes[$config['palette']] ?? $palettes['gold'];
        $qrSvg = QrCodeService::svg($targetUrl, 160, $paletteMeta['qr_color'], $paletteMeta['qr_bg']);

        return view('client.pages.account.companion_card.index', compact(
            'invitation',
            'wedding',
            'config',
            'sizes',
            'palettes',
            'motifs',
            'guests',
            'qrSvg',
            'targetUrl'
        ));
    }

    /**
     * Lưu cấu hình Thẻ Kẹp Thiệp Giấy.
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'size' => ['required', 'string', 'in:namecard,postcard,square'],
            'palette' => ['required', 'string', 'in:gold,charcoal,sage,ruby,dusty_rose'],
            'motif' => ['required', 'string', 'in:royal_crest,minimal_editorial,floral_botanical'],
            'kicker' => ['nullable', 'string', 'max:100'],
            'couple_title' => ['required', 'string', 'max:150'],
            'instructions' => ['nullable', 'string', 'max:300'],
            'qr_label' => ['required', 'string', 'max:80'],
            'show_date' => ['nullable', 'boolean'],
            'show_venue' => ['nullable', 'boolean'],
            'include_guest_name' => ['nullable', 'boolean'],
        ]);

        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) ($invitation->custom_config ?? '{}'), true) ?? []);

        $customConfig['companion_card'] = [
            'size' => $validated['size'],
            'palette' => $validated['palette'],
            'motif' => $validated['motif'],
            'kicker' => trim($validated['kicker'] ?? ''),
            'couple_title' => trim($validated['couple_title']),
            'instructions' => trim($validated['instructions'] ?? ''),
            'qr_label' => trim($validated['qr_label']),
            'show_date' => (bool) ($validated['show_date'] ?? true),
            'show_venue' => (bool) ($validated['show_venue'] ?? true),
            'include_guest_name' => (bool) ($validated['include_guest_name'] ?? false),
            'footer_brand' => 'MÂY HẸN • MAYHEN.VN',
        ];

        $invitation->custom_config = $customConfig;
        $invitation->save();

        AuditLogger::log(
            'COMPANION_CARD_CONFIG_UPDATED',
            'Invitation',
            $invitation->id,
            "Cập nhật cấu hình Thẻ Kẹp Kèm Thiệp Giấy khổ [{$validated['size']}] tông màu [{$validated['palette']}]",
            ['user_id' => Auth::id()]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã lưu cấu hình thẻ kẹp thiệp giấy thành công!',
                'config' => $customConfig['companion_card'],
            ]);
        }

        return redirect()->route('account.companion_card.index', $invitation->id)
            ->with('status', 'Đã lưu cấu hình thẻ kẹp thiệp giấy thành công!');
    }

    /**
     * Trang In Khổ A4 Dàn Trang Chuẩn Xưởng In (Print Sheet with Crop Marks).
     */
    public function print(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $config = CompanionCardService::resolveCardConfig($invitation, $request->query());
        $sizeKey = $config['size'];
        $sizeMeta = CompanionCardService::SIZES[$sizeKey] ?? CompanionCardService::SIZES['namecard'];
        $paletteMeta = CompanionCardService::PALETTES[$config['palette']] ?? CompanionCardService::PALETTES['gold'];

        $mode = $request->query('mode', 'single_repeat'); // single_repeat hoặc guest_batch
        $guestItems = [];

        if ($mode === 'guest_batch') {
            $guests = Guest::where('wedding_id', $invitation->wedding_id)
                ->orderBy('group_name')
                ->orderBy('name')
                ->get();

            foreach ($guests as $guest) {
                $targetUrl = url("/thiep/{$invitation->slug}?guest={$guest->guest_token}");
                $guestItems[] = [
                    'guest' => $guest,
                    'qr_svg' => QrCodeService::svg($targetUrl, 160, $paletteMeta['qr_color'], $paletteMeta['qr_bg']),
                ];
            }
        } else {
            // Lặp lại thẻ chung
            $targetUrl = url("/thiep/{$invitation->slug}");
            $qrSvg = QrCodeService::svg($targetUrl, 160, $paletteMeta['qr_color'], $paletteMeta['qr_bg']);
            $count = (int) $request->query('count', $sizeMeta['items_per_a4']);
            for ($i = 0; $i < $count; $i++) {
                $guestItems[] = [
                    'guest' => null,
                    'qr_svg' => $qrSvg,
                ];
            }
        }

        return view('client.pages.account.companion_card.print', compact(
            'invitation',
            'wedding',
            'config',
            'sizeMeta',
            'paletteMeta',
            'guestItems',
            'mode'
        ));
    }

    /**
     * Tải tệp vector SVG độc lập độ phân giải cao cho xưởng in.
     */
    public function downloadSvg(Request $request, int $invitationId): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $config = CompanionCardService::resolveCardConfig($invitation, $request->query());
        $svgContent = CompanionCardService::generateCardSvg($invitation, $config);

        $filename = "The_Kep_Thiep_{$invitation->slug}_{$config['size']}.svg";

        return response($svgContent, 200, [
            'Content-Type' => 'image/svg+xml; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Xác thực quyền sở hữu thiệp cưới chống IDOR.
     */
    private function authorizeInvitationOwner(Invitation $invitation): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Bạn chưa đăng nhập.');
        }

        if (! $invitation->wedding || ((int) $invitation->wedding->user_id !== (int) $user->id && ! ($user->is_admin ?? false))) {
            abort(403, 'Bạn không có quyền truy cập thẻ kẹp của thiệp cưới này.');
        }
    }
}
