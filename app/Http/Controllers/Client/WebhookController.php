<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\OutboundWebhook;
use App\Services\AuditLogger;
use App\Services\OutboundWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebhookController extends Controller
{
    public function __construct(
        protected OutboundWebhookService $webhookService
    ) {}

    /**
     * Bảng điều khiển quản lý Webhook và Tự động hóa thông báo sự kiện cưới.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $webhooks = $wedding->outboundWebhooks()->with(['deliveries' => fn ($q) => $q->take(10)])->get();

        return view('client.pages.account.webhooks.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'webhooks' => $webhooks,
        ]);
    }

    /**
     * Khởi tạo webhook mới cho đám cưới.
     */
    public function store(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'payload_url' => 'required|url|max:1000',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:rsvp.submitted,wish.created,gift.received',
        ], [
            'name.required' => 'Vui lòng đặt tên gợi nhớ cho Webhook (vd: Telegram Bot, Google Sheets...).',
            'payload_url.required' => 'Vui lòng nhập đường dẫn URL nhận dữ liệu webhook.',
            'payload_url.url' => 'Đường dẫn webhook không hợp lệ (bắt buộc phải là HTTP hoặc HTTPS hợp lệ).',
            'events.required' => 'Vui lòng chọn ít nhất 1 sự kiện để nhận thông báo.',
            'events.min' => 'Vui lòng chọn ít nhất 1 sự kiện để nhận thông báo.',
        ]);

        $webhook = $wedding->outboundWebhooks()->create([
            'name' => trim($validated['name']),
            'payload_url' => trim($validated['payload_url']),
            'secret' => OutboundWebhook::generateSecret(),
            'events' => $validated['events'],
            'is_active' => true,
        ]);

        AuditLogger::log(
            'OUTBOUND_WEBHOOK_CREATED',
            'outbound_webhook',
            $webhook->id,
            "Tạo webhook '{$webhook->name}' cho đám cưới #{$wedding->id}",
            ['wedding_id' => $wedding->id, 'url' => $webhook->payload_url]
        );

        return back()->with('success', "Đã tạo webhook '{$webhook->name}' thành công. Bạn có thể bấm 'Gửi Thử (Ping)' để kiểm tra kết nối.");
    }

    /**
     * Cập nhật thông tin webhook.
     */
    public function update(Request $request, int $invitationId, int $webhookId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $webhook = $wedding->outboundWebhooks()->findOrFail($webhookId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'payload_url' => 'required|url|max:1000',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:rsvp.submitted,wish.created,gift.received',
        ]);

        $webhook->update([
            'name' => trim($validated['name']),
            'payload_url' => trim($validated['payload_url']),
            'events' => $validated['events'],
            'is_active' => $request->has('is_active'),
            // Nếu người dùng chủ động sửa webhook, reset lại failure_count
            'failure_count' => 0,
        ]);

        return back()->with('success', "Đã cập nhật cấu hình webhook '{$webhook->name}'.");
    }

    /**
     * Xóa webhook.
     */
    public function destroy(Request $request, int $invitationId, int $webhookId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $webhook = $wedding->outboundWebhooks()->findOrFail($webhookId);
        $name = $webhook->name;
        $webhook->delete();

        return back()->with('success', "Đã xóa webhook '{$name}'.");
    }

    /**
     * Bắn gói tin Ping Test kiểm tra kết nối.
     */
    public function ping(Request $request, int $invitationId, int $webhookId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $webhook = $wedding->outboundWebhooks()->findOrFail($webhookId);

        $result = $this->webhookService->pingTest($webhook);

        return response()->json([
            'success' => $result['is_success'],
            'status' => $result['status'],
            'duration_ms' => $result['duration_ms'],
            'error' => $result['error'],
            'message' => $result['is_success']
                ? 'Gửi gói tin Ping Test thành công (Mã HTTP: '.$result['status'].' trong '.$result['duration_ms'].'ms).'
                : 'Gửi gói tin thất bại: '.($result['error'] ?? 'Không thể kết nối đến máy chủ đối tác.'),
        ]);
    }

    /**
     * Phân quyền bảo mật truy cập thiệp.
     */
    protected function resolveAuthorizedInvitation(Request $request, int $invitationId): Invitation
    {
        $user = $request->user();

        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);

        if (! $user->isAdmin()) {
            if (! $invitation->wedding || (int) $invitation->wedding->user_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền quản lý webhook của thiệp cưới này.');
            }
        }

        return $invitation;
    }
}
