<?php

namespace App\Services;

use App\Models\OutboundWebhook;
use App\Models\OutboundWebhookDelivery;
use App\Models\Wedding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class OutboundWebhookService
{
    /**
     * Bắn webhook thông báo sự kiện ra các hệ thống ngoại vi (Telegram, Discord, n8n, CRM).
     */
    public function dispatch(Wedding $wedding, string $eventType, array $eventData): array
    {
        $webhooks = $wedding->outboundWebhooks()
            ->where('is_active', true)
            ->get()
            ->filter(fn (OutboundWebhook $w) => $w->shouldDeliver($eventType));

        $results = [];

        foreach ($webhooks as $webhook) {
            $results[] = $this->sendPayload($webhook, $eventType, $eventData, $wedding);
        }

        return $results;
    }

    /**
     * Gửi gói tin thử nghiệm (Ping Test) để kiểm tra tính sẵn sàng của endpoint.
     */
    public function pingTest(OutboundWebhook $webhook): array
    {
        $wedding = $webhook->wedding;

        $pingData = [
            'message' => 'Đây là gói tin kiểm tra kết nối (Ping Test) từ nền tảng Mây Hẹn (mayhen.vn).',
            'webhook_id' => $webhook->id,
            'webhook_name' => $webhook->name,
            'test_time' => now()->toIso8601String(),
        ];

        return $this->sendPayload($webhook, 'ping', $pingData, $wedding);
    }

    /**
     * Thực hiện gửi POST payload có chữ ký HMAC SHA-256 bảo vệ tính toàn vẹn.
     */
    protected function sendPayload(OutboundWebhook $webhook, string $eventType, array $data, ?Wedding $wedding = null): array
    {
        $deliveryId = (string) Str::uuid();

        $payload = [
            'id' => $deliveryId,
            'event' => $eventType,
            'timestamp' => now()->toIso8601String(),
            'wedding' => $wedding ? [
                'id' => $wedding->id,
                'couple' => "{$wedding->groom_name} & {$wedding->bride_name}",
                'wedding_date' => $wedding->wedding_date ? $wedding->wedding_date->format('Y-m-d') : null,
            ] : null,
            'data' => $data,
        ];

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $signature = hash_hmac('sha256', $jsonPayload, $webhook->secret);

        $startTime = microtime(true);
        $statusCode = null;
        $responseBody = null;
        $errorMessage = null;
        $isSuccess = false;

        try {
            $response = Http::timeout(4)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MayHen-Webhook-Dispatcher/3.0 (+https://mayhen.vn)',
                    'X-MayHen-Event' => $eventType,
                    'X-MayHen-Delivery' => $deliveryId,
                    'X-MayHen-Signature' => 'sha256='.$signature,
                ])
                ->post($webhook->payload_url, $payload);

            $statusCode = $response->status();
            $responseBody = Str::limit($response->body(), 1000);
            $isSuccess = $response->successful();

            if (! $isSuccess) {
                $errorMessage = "Máy chủ đối tác trả về mã lỗi HTTP {$statusCode}.";
            }
        } catch (Throwable $e) {
            $statusCode = 0;
            $errorMessage = Str::limit($e->getMessage(), 500);
            $isSuccess = false;
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Lưu vết lịch sử Delivery
        $delivery = OutboundWebhookDelivery::create([
            'outbound_webhook_id' => $webhook->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'response_status' => $statusCode,
            'response_body' => $responseBody,
            'duration_ms' => $durationMs,
            'error_message' => $errorMessage,
            'is_success' => $isSuccess,
            'created_at' => now(),
        ]);

        // Cập nhật trạng thái và Circuit Breaker trên Webhook
        if ($isSuccess) {
            $webhook->update([
                'failure_count' => 0,
                'last_dispatched_at' => now(),
                'last_status_code' => $statusCode,
            ]);
        } else {
            $newFailureCount = $webhook->failure_count + 1;
            $webhook->update([
                'failure_count' => $newFailureCount,
                'last_dispatched_at' => now(),
                'last_status_code' => $statusCode,
            ]);

            if ($newFailureCount >= 10) {
                AuditLogger::log(
                    'WEBHOOK_CIRCUIT_BROKEN',
                    'outbound_webhook',
                    $webhook->id,
                    "Webhook '{$webhook->name}' bị ngắt mạch tự động do lỗi liên tiếp 10 lần.",
                    ['webhook_id' => $webhook->id, 'failure_count' => $newFailureCount]
                );
            }
        }

        return [
            'delivery_id' => $delivery->id,
            'status' => $statusCode,
            'is_success' => $isSuccess,
            'duration_ms' => $durationMs,
            'error' => $errorMessage,
        ];
    }
}
