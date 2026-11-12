<?php

namespace App\Services;

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\InvitationDeliveryService;
use App\Services\OpenGraphService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ZaloShareController extends Controller
{
    public function __construct(
        protected InvitationDeliveryService $deliveryService
    ) {}

    /**
     * Xuất ảnh thẻ OpenGraph 1200x630px định dạng SVG công khai (Public Endpoint cho Zalo/Facebook Crawler).
     */
    public function shareCardSvg(string $slug): Response
    {
        $invitation = Invitation::with('wedding')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $svgContent = OpenGraphService::generateSvg($invitation);

        return response($svgContent, 200, [
            'Content-Type' => 'image/svg+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400, s-maxage=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Màn hình Studio Chia Sẻ Zalo & Mạng Xã Hội (Mục 2.5 Master DOCX).
     */
    public function index(int $id): View
    {
        $invitation = Invitation::with(['wedding.guests'])->findOrFail($id);
        $wedding = $invitation->wedding;

        if ($wedding->user_id !== Auth::id()) {
            abort(404);
        }

        $templates = OpenGraphService::getZaloTemplates();
        $shareCardUrl = route('invitation.share_card', $invitation->slug);
        $invitationUrl = url('/thiep/'.$invitation->slug);

        // Danh sách khách mời sẵn có để chọn nhanh
        $guests = $wedding->guests()
            ->orderBy('name')
            ->limit(50)
            ->get();

        return view('client.pages.account.share.index', compact(
            'invitation',
            'wedding',
            'templates',
            'shareCardUrl',
            'invitationUrl',
            'guests'
        ));
    }

    /**
     * Tạo tin nhắn cá nhân hóa tức thời khi người dùng nhập tên khách hoặc chọn mẫu.
     */
    public function compose(Request $request, int $id): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;

        if ($wedding->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập'], 403);
        }

        $validated = $request->validate([
            'salutation' => ['nullable', 'string', 'max:50'],
            'guest_name' => ['nullable', 'string', 'max:100'],
            'template_key' => ['required', 'string', 'in:formal,colleague,intimate,faraway,custom'],
            'custom_content' => ['nullable', 'string', 'max:2000'],
            'guest_id' => ['nullable', 'integer'],
        ]);

        $templates = OpenGraphService::getZaloTemplates();
        $templateText = $validated['template_key'] === 'custom'
            ? ($validated['custom_content'] ?? '')
            : ($templates[$validated['template_key']]['content'] ?? $templates['formal']['content']);

        $salutation = trim($validated['salutation'] ?? 'Bạn');
        $guestName = trim($validated['guest_name'] ?? 'Khách Quý');

        // Tạo dummy guest hoặc dùng guest có sẵn để tạo URL token
        $guest = null;
        if (! empty($validated['guest_id'])) {
            $guest = $wedding->guests()->find($validated['guest_id']);
        }

        if (! $guest) {
            $guest = new Guest([
                'wedding_id' => $wedding->id,
                'name' => $guestName,
                'salutation' => $salutation,
                'guest_token' => Str::random(12),
            ]);
        }

        $personalizedMessage = $this->deliveryService->buildPersonalizedMessage(
            $guest,
            $wedding,
            $invitation,
            $templateText
        );

        $targetUrl = url('/thiep/'.$invitation->slug.'?khach='.urlencode($salutation.' '.$guestName));
        if ($guest->guest_token) {
            $targetUrl = route('invitation.show', ['slug' => $invitation->slug, 'token' => $guest->guest_token]);
        }

        $zaloShareUrl = 'https://zalo.me/share?url='.urlencode($targetUrl);

        return response()->json([
            'success' => true,
            'message' => $personalizedMessage,
            'target_url' => $targetUrl,
            'zalo_share_url' => $zaloShareUrl,
        ]);
    }

    /**
     * Ghi nhận lượt chia sẻ Zalo / Copy / WebShare vào AuditLogger.
     */
    public function logShare(Request $request, int $id): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;

        if ($wedding->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập'], 403);
        }

        $validated = $request->validate([
            'channel' => ['required', 'string', 'in:zalo,facebook,clipboard,web_share'],
            'template_key' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['nullable', 'string', 'max:100'],
        ]);

        AuditLogger::log(
            'INVITATION_SHARED',
            'Invitation',
            $invitation->id,
            "Chủ tiệc chia sẻ thiệp cưới qua kênh [{$validated['channel']}]",
            [
                'channel' => $validated['channel'],
                'template_key' => $validated['template_key'] ?? 'formal',
                'recipient_name' => $validated['recipient_name'] ?? null,
                'wedding_id' => $wedding->id,
                'user_id' => Auth::id(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã ghi nhận lượt chia sẻ thành công.',
        ]);
    }
}
