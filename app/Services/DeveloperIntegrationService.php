<?php

namespace App\Services;

use App\Models\DeveloperApiKey;
use App\Models\OutboundWebhook;
use App\Models\OutboundWebhookDelivery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class DeveloperIntegrationService
{
    public function __construct(
        protected OutboundWebhookService $webhookService
    ) {}

    /**
     * Danh mục các quyền hạn (Scopes) API dành cho đối tác / lập trình viên.
     */
    public function getAvailableScopes(): array
    {
        return [
            'templates:read' => [
                'name' => 'Xem mẫu thiệp cưới',
                'description' => 'Truy vấn danh mục mẫu thiệp, phong cách thiết kế, bảng màu và dữ liệu mẫu có sẵn.',
            ],
            'invitations:read' => [
                'name' => 'Truy vấn thiệp cưới',
                'description' => 'Đọc thông tin chi tiết các thiệp cưới đã xuất bản của đối tác hoặc hệ thống.',
            ],
            'orders:read' => [
                'name' => 'Truy vấn đơn hàng',
                'description' => 'Xem danh sách và trạng thái các đơn hàng, giao dịch gói thiệp liên kết.',
            ],
            'guests:read' => [
                'name' => 'Đọc danh bạ khách mời',
                'description' => 'Truy cập danh sách khách mời, nhóm khách và mã định danh khách mời tiệc cưới.',
            ],
            'rsvps:read' => [
                'name' => 'Đọc phản hồi RSVP',
                'description' => 'Đọc xác nhận tham dự, số lượng người đi cùng và chế độ ăn uống của khách.',
            ],
            'webhooks:manage' => [
                'name' => 'Quản lý Webhook',
                'description' => 'Cấu hình, đăng ký hoặc kiểm tra các kết nối Webhook ngoại vi qua API.',
            ],
        ];
    }

    /**
     * Danh mục các sự kiện hệ thống (Events) hỗ trợ Webhook ngoại vi.
     */
    public function getAvailableEvents(): array
    {
        return [
            'order.paid' => [
                'name' => 'Đơn hàng thanh toán thành công',
                'description' => 'Bắn khi đơn hàng được cổng thanh toán xác nhận thanh toán hợp lệ.',
            ],
            'invitation.published' => [
                'name' => 'Thiệp cưới xuất bản',
                'description' => 'Bắn khi khách hàng hoặc studio xuất bản thiệp cưới ra đường dẫn công khai.',
            ],
            'rsvp.submitted' => [
                'name' => 'Khách xác nhận tham dự (RSVP)',
                'description' => 'Bắn khi có khách mời gửi phản hồi tham dự hoặc từ chối tham gia đám cưới.',
            ],
            'wish.created' => [
                'name' => 'Khách gửi lời chúc / ký tên',
                'description' => 'Bắn khi có lời chúc mới được gửi vào Sổ ký tên điện tử.',
            ],
            'gift.received' => [
                'name' => 'Khách mừng cưới / chuyển khoản',
                'description' => 'Bắn khi ghi nhận giao dịch mừng cưới qua mã VietQR của tiệc cưới.',
            ],
            'lead.submitted' => [
                'name' => 'Khách để lại thông tin tư vấn',
                'description' => 'Bắn khi có khách hàng để lại liên hệ tư vấn mẫu thiệp từ trang chủ/landing page.',
            ],
        ];
    }

    /**
     * Tạo mới một API Key bảo mật.
     * Trả về model và token gốc chỉ hiển thị DUY NHẤT 1 LẦN.
     *
     * @return array{apiKey: DeveloperApiKey, plainKey: string}
     */
    public function createApiKey(array $data, ?User $admin = null): array
    {
        $environment = in_array($data['environment'] ?? 'live', ['live', 'test'], true) ? $data['environment'] : 'live';
        $prefix = $environment === 'live' ? 'mh_live_' : 'mh_test_';

        // Sinh 32 ký tự ngẫu nhiên an toàn mật mã
        $randomSecret = Str::random(32);
        $plainKey = $prefix.$randomSecret;

        // Lưu tiền tố để nhận diện (16 ký tự đầu)
        $keyPrefix = substr($plainKey, 0, 16);

        // Mã hóa một chiều SHA-256 để lưu trữ an toàn trong DB
        $keyHash = hash('sha256', $plainKey);

        $apiKey = DeveloperApiKey::create([
            'name' => trim($data['name']),
            'environment' => $environment,
            'key_prefix' => $keyPrefix,
            'key_hash' => $keyHash,
            'scopes' => $data['scopes'] ?? ['templates:read'],
            'rate_limit_per_minute' => (int) ($data['rate_limit_per_minute'] ?? 60),
            'status' => 'active',
            'expires_at' => ! empty($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
            'created_by' => $admin?->id,
        ]);

        AuditLogger::log(
            'DEVELOPER_API_KEY_CREATED',
            'developer_api_key',
            $apiKey->id,
            "Quản trị viên đã phát hành Khóa API mới: '{$apiKey->name}' (Môi trường: {$apiKey->environment}, Tiền tố: {$apiKey->key_prefix}).",
            [
                'api_key_id' => $apiKey->id,
                'name' => $apiKey->name,
                'environment' => $apiKey->environment,
                'scopes' => $apiKey->scopes,
            ]
        );

        return [
            'apiKey' => $apiKey,
            'plainKey' => $plainKey,
        ];
    }

    /**
     * Thu hồi (Revoke) khóa API.
     */
    public function revokeApiKey(int $id, ?User $admin = null): bool
    {
        $apiKey = DeveloperApiKey::findOrFail($id);
        $apiKey->update(['status' => 'revoked']);

        AuditLogger::log(
            'DEVELOPER_API_KEY_REVOKED',
            'developer_api_key',
            $apiKey->id,
            "Quản trị viên đã thu hồi Khóa API: '{$apiKey->name}' (ID: {$apiKey->id}).",
            ['api_key_id' => $apiKey->id]
        );

        return true;
    }

    /**
     * Xóa vĩnh viễn khóa API.
     */
    public function deleteApiKey(int $id, ?User $admin = null): bool
    {
        $apiKey = DeveloperApiKey::findOrFail($id);
        $name = $apiKey->name;
        $apiKey->delete();

        AuditLogger::log(
            'DEVELOPER_API_KEY_DELETED',
            'developer_api_key',
            $id,
            "Quản trị viên đã xóa hoàn toàn Khóa API: '{$name}' (ID: {$id}).",
            ['api_key_id' => $id, 'name' => $name]
        );

        return true;
    }

    /**
     * Tạo mới một Webhook cấp nền tảng (Platform Outbound Webhook).
     */
    public function createPlatformWebhook(array $data, ?User $admin = null): OutboundWebhook
    {
        $webhook = OutboundWebhook::create([
            'wedding_id' => null, // Platform Level
            'name' => trim($data['name']),
            'payload_url' => trim($data['payload_url']),
            'secret' => OutboundWebhook::generateSecret(),
            'events' => $data['events'] ?? ['order.paid', 'invitation.published'],
            'is_active' => true,
            'failure_count' => 0,
            'created_by' => $admin?->id,
        ]);

        AuditLogger::log(
            'PLATFORM_WEBHOOK_CREATED',
            'outbound_webhook',
            $webhook->id,
            "Quản trị viên đã tạo Webhook nền tảng: '{$webhook->name}' -> {$webhook->payload_url}.",
            [
                'webhook_id' => $webhook->id,
                'name' => $webhook->name,
                'url' => $webhook->payload_url,
                'events' => $webhook->events,
            ]
        );

        return $webhook;
    }

    /**
     * Bật / Tắt trạng thái hoạt động của Webhook (đồng thời reset Circuit Breaker nếu bật lại).
     */
    public function toggleWebhook(int $id, ?User $admin = null): OutboundWebhook
    {
        $webhook = OutboundWebhook::findOrFail($id);
        $newActive = ! $webhook->is_active;

        $updateData = ['is_active' => $newActive];
        if ($newActive) {
            $updateData['failure_count'] = 0; // Reset lỗi khi chủ động kích hoạt lại
        }

        $webhook->update($updateData);

        AuditLogger::log(
            'OUTBOUND_WEBHOOK_STATUS_TOGGLED',
            'outbound_webhook',
            $webhook->id,
            'Quản trị viên đã '.($newActive ? 'kích hoạt lại' : 'tạm ngắt')." Webhook '{$webhook->name}'.",
            ['webhook_id' => $webhook->id, 'is_active' => $newActive]
        );

        return $webhook;
    }

    /**
     * Xóa Webhook.
     */
    public function deleteWebhook(int $id, ?User $admin = null): bool
    {
        $webhook = OutboundWebhook::findOrFail($id);
        $name = $webhook->name;
        $webhook->delete();

        AuditLogger::log(
            'OUTBOUND_WEBHOOK_DELETED',
            'outbound_webhook',
            $id,
            "Quản trị viên đã xóa Webhook '{$name}' (ID: {$id}).",
            ['webhook_id' => $id, 'name' => $name]
        );

        return true;
    }

    /**
     * Bắn gói tin Ping Test kiểm tra kết nối Webhook.
     */
    public function pingWebhook(int $id): array
    {
        $webhook = OutboundWebhook::findOrFail($id);

        return $this->webhookService->pingTest($webhook);
    }

    /**
     * Thử gửi lại một lượt giao vận cũ.
     */
    public function retryDelivery(int $deliveryId): array
    {
        $delivery = OutboundWebhookDelivery::with('webhook')->findOrFail($deliveryId);

        return $this->webhookService->resendDelivery($delivery);
    }

    /**
     * Lấy 4 Thẻ KPI Chỉ Số Tích Hợp & Kết Nối Ngoại Vi.
     */
    public function getIntegrationKpis(): array
    {
        $totalApiKeys = DeveloperApiKey::count();
        $activeApiKeys = DeveloperApiKey::active()->count();

        $totalWebhooks = OutboundWebhook::count();
        $platformWebhooks = OutboundWebhook::whereNull('wedding_id')->count();

        // Thống kê deliveries trong 24 giờ qua
        $last24h = Carbon::now()->subHours(24);
        $totalDeliveries24h = OutboundWebhookDelivery::where('created_at', '>=', $last24h)->count();
        $successDeliveries24h = OutboundWebhookDelivery::where('created_at', '>=', $last24h)->where('is_success', true)->count();

        $successRate = $totalDeliveries24h > 0
            ? round(($successDeliveries24h / $totalDeliveries24h) * 100, 1)
            : 100.0;

        $circuitBrokenWebhooks = OutboundWebhook::where('failure_count', '>=', 10)->count();

        return [
            'total_api_keys' => $totalApiKeys,
            'active_api_keys' => $activeApiKeys,
            'total_webhooks' => $totalWebhooks,
            'platform_webhooks' => $platformWebhooks,
            'total_deliveries_24h' => $totalDeliveries24h,
            'success_rate_24h' => $successRate,
            'circuit_broken_webhooks' => $circuitBrokenWebhooks,
        ];
    }

    /**
     * Danh sách phân trang API Keys có hỗ trợ tìm kiếm và lọc.
     */
    public function getFilteredApiKeys(?string $search = null, ?string $environment = null, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = DeveloperApiKey::with('creator')->latest('id');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('key_prefix', 'like', "%{$search}%");
            });
        }

        if (! empty($environment) && in_array($environment, ['live', 'test'], true)) {
            $query->where('environment', $environment);
        }

        if (! empty($status)) {
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'revoked') {
                $query->revoked();
            } elseif ($status === 'expired') {
                $query->where('status', 'active')->whereNotNull('expires_at')->where('expires_at', '<=', Carbon::now());
            }
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Danh sách phân trang Webhooks có hỗ trợ tìm kiếm và lọc.
     */
    public function getFilteredWebhooks(?string $search = null, ?string $scope = null, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = OutboundWebhook::with(['wedding', 'creator'])->latest('id');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('payload_url', 'like', "%{$search}%");
            });
        }

        if ($scope === 'platform') {
            $query->whereNull('wedding_id');
        } elseif ($scope === 'wedding') {
            $query->whereNotNull('wedding_id');
        }

        if ($status === 'active') {
            $query->where('is_active', true)->where('failure_count', '<', 10);
        } elseif ($status === 'paused') {
            $query->where('is_active', false);
        } elseif ($status === 'broken') {
            $query->where('failure_count', '>=', 10);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Danh sách phân trang Lịch sử Giao vận Webhook (Deliveries Log).
     */
    public function getFilteredDeliveries(?int $webhookId = null, ?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = OutboundWebhookDelivery::with('webhook')->latest('id');

        if ($webhookId) {
            $query->where('outbound_webhook_id', $webhookId);
        }

        if ($status === 'success') {
            $query->where('is_success', true);
        } elseif ($status === 'failed') {
            $query->where('is_success', false);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
