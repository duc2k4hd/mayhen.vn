<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeveloperIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDeveloperIntegrationController extends Controller
{
    public function __construct(
        protected DeveloperIntegrationService $integrationService
    ) {}

    /**
     * Bảng điều khiển Quản trị Khóa API, Webhook Ngoại Vi & Nhật Ký Giao Vận.
     */
    public function index(Request $request): View
    {
        $currentTab = $request->query('tab', 'api-keys');
        if (! in_array($currentTab, ['api-keys', 'webhooks', 'deliveries'], true)) {
            $currentTab = 'api-keys';
        }

        $kpis = $this->integrationService->getIntegrationKpis();
        $availableScopes = $this->integrationService->getAvailableScopes();
        $availableEvents = $this->integrationService->getAvailableEvents();

        $apiKeys = null;
        $webhooks = null;
        $deliveries = null;

        if ($currentTab === 'api-keys') {
            $apiKeys = $this->integrationService->getFilteredApiKeys(
                search: $request->query('search'),
                environment: $request->query('environment'),
                status: $request->query('status'),
                perPage: 15
            );
        } elseif ($currentTab === 'webhooks') {
            $webhooks = $this->integrationService->getFilteredWebhooks(
                search: $request->query('search'),
                scope: $request->query('scope'),
                status: $request->query('status'),
                perPage: 15
            );
        } elseif ($currentTab === 'deliveries') {
            $deliveries = $this->integrationService->getFilteredDeliveries(
                webhookId: $request->filled('webhook_id') ? (int) $request->query('webhook_id') : null,
                status: $request->query('status'),
                perPage: 20
            );
        }

        return view('admin.pages.settings.developer.index', compact(
            'currentTab',
            'kpis',
            'availableScopes',
            'availableEvents',
            'apiKeys',
            'webhooks',
            'deliveries'
        ));
    }

    /**
     * Tạo mới một Khóa API.
     */
    public function storeApiKey(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'environment' => ['required', 'in:live,test'],
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => ['string'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:10', 'max:1000'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $result = $this->integrationService->createApiKey($validated, $request->user());

        return redirect()->route('admin.settings.developer.index', ['tab' => 'api-keys'])
            ->with('success', "Khóa API '{$result['apiKey']->name}' đã được khởi tạo thành công.")
            ->with('new_plain_key', $result['plainKey'])
            ->with('new_key_name', $result['apiKey']->name);
    }

    /**
     * Thu hồi Khóa API.
     */
    public function revokeApiKey(int $id, Request $request): RedirectResponse
    {
        $this->integrationService->revokeApiKey($id, $request->user());

        return redirect()->route('admin.settings.developer.index', ['tab' => 'api-keys'])
            ->with('success', 'Đã thu hồi Khóa API thành công. Khóa này sẽ không thể tiếp tục gửi yêu cầu vào hệ thống.');
    }

    /**
     * Xóa vĩnh viễn Khóa API.
     */
    public function destroyApiKey(int $id, Request $request): RedirectResponse
    {
        $this->integrationService->deleteApiKey($id, $request->user());

        return redirect()->route('admin.settings.developer.index', ['tab' => 'api-keys'])
            ->with('success', 'Đã xóa hoàn toàn Khóa API khỏi cơ sở dữ liệu.');
    }

    /**
     * Đăng ký mới Webhook cấp Nền tảng (Platform Webhook).
     */
    public function storeWebhook(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'payload_url' => ['required', 'url', 'max:1000'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string'],
        ]);

        $webhook = $this->integrationService->createPlatformWebhook($validated, $request->user());

        return redirect()->route('admin.settings.developer.index', ['tab' => 'webhooks'])
            ->with('success', "Webhook nền tảng '{$webhook->name}' đã được đăng ký thành công.");
    }

    /**
     * Bật / Tắt trạng thái hoạt động của Webhook.
     */
    public function toggleWebhook(int $id, Request $request): RedirectResponse
    {
        $webhook = $this->integrationService->toggleWebhook($id, $request->user());
        $statusText = $webhook->is_active ? 'kích hoạt' : 'tạm ngắt';

        return redirect()->route('admin.settings.developer.index', ['tab' => 'webhooks'])
            ->with('success', "Đã {$statusText} Webhook '{$webhook->name}' thành công.");
    }

    /**
     * Bắn gói tin Ping Test kiểm tra kết nối Webhook.
     */
    public function pingWebhook(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $result = $this->integrationService->pingWebhook($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result);
        }

        if ($result['is_success']) {
            return redirect()->route('admin.settings.developer.index', ['tab' => 'webhooks'])
                ->with('success', "Kiểm tra kết nối thành công! Mã HTTP {$result['status']} ({$result['duration_ms']}ms).");
        }

        return redirect()->route('admin.settings.developer.index', ['tab' => 'webhooks'])
            ->with('error', 'Kiểm tra kết nối thất bại: '.($result['error'] ?? 'Không nhận được phản hồi hợp lệ')." (Mã HTTP {$result['status']}).");
    }

    /**
     * Xóa Webhook.
     */
    public function destroyWebhook(int $id, Request $request): RedirectResponse
    {
        $this->integrationService->deleteWebhook($id, $request->user());

        return redirect()->route('admin.settings.developer.index', ['tab' => 'webhooks'])
            ->with('success', 'Đã xóa Webhook thành công.');
    }

    /**
     * Thử gửi lại một lượt giao vận cũ.
     */
    public function retryDelivery(int $id, Request $request): RedirectResponse
    {
        $result = $this->integrationService->retryDelivery($id);

        if ($result['is_success']) {
            return redirect()->route('admin.settings.developer.index', ['tab' => 'deliveries'])
                ->with('success', "Đã gửi lại webhook thành công! Máy chủ đối tác phản hồi HTTP {$result['status']} ({$result['duration_ms']}ms).");
        }

        return redirect()->route('admin.settings.developer.index', ['tab' => 'deliveries'])
            ->with('error', 'Gửi lại webhook thất bại: '.($result['error'] ?? 'Lỗi không xác định')." (Mã HTTP {$result['status']}).");
    }
}
