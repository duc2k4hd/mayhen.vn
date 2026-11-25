<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentWebhookLog;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\OrderSettlementService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Single Source of Truth cho toàn bộ giao tiếp SePay & VietQR:
 * - Hợp nhất cấu hình tài khoản ngân hàng, Webhook API Key và SePay User API Token (chống giá trị mẫu giả).
 * - Xác thực Webhook chuẩn SePay (Authorization: Apikey <key>) kèm tương thích ngược HMAC SHA-256.
 * - Gọi chủ động SePay User API (/userapi/transactions/list) để đối soát 5 điều kiện bất biến tài chính.
 * - Ghi nhật ký đồng bộ vào cả bảng payment_webhook_logs lẫn storage/logs/payment_webhooks.jsonl.
 */
class SepayGatewayService
{
    /**
     * Các giá trị placeholder mặc định trong PlatformConfigurationService tuyệt đối không dùng làm cấu hình thật.
     *
     * @var list<string>
     */
    private const PLACEHOLDER_VALUES = [
        'SEP_TEST_SECRET_KEY_9988',
        'SIGN_SECRET_2026',
        '0988888888',
        'CONG TY TNHH MAY HEN ATELIER',
    ];

    /**
     * Lấy toàn bộ cấu hình thanh toán SePay/VietQR đã chuẩn hóa.
     *
     * @return array{
     *     bank_code: string,
     *     bank_name: string,
     *     bank_account: string,
     *     bank_account_name: string,
     *     qr_template: string,
     *     webhook_secret: string,
     *     api_token: string,
     *     api_base_url: string
     * }
     */
    public function getConfig(): array
    {
        $envConfig = [
            'bank_code' => $this->normalizeString(config('services.sepay.bank_code')),
            'bank_name' => $this->normalizeString(config('services.sepay.bank_name')),
            'bank_account' => $this->normalizeString(config('services.sepay.bank_account')),
            'bank_account_name' => $this->normalizeString(config('services.sepay.bank_account_name')),
            'qr_template' => $this->normalizeString(config('services.sepay.qr_template')) ?: 'compact2',
            'webhook_secret' => $this->normalizeString(config('services.sepay.webhook_secret')),
            'api_token' => $this->normalizeString(config('services.sepay.api_token')),
            'api_base_url' => rtrim($this->normalizeString(config('services.sepay.api_base_url')) ?: 'https://my.sepay.vn/userapi', '/'),
        ];

        // Trong môi trường Unit/Feature test: cô lập 100% theo config('services.sepay.*')
        if (app()->runningUnitTests()) {
            return $envConfig;
        }

        $fileConfig = $this->readBankingJsonFile();
        $dbConfig = $this->readPlatformPaymentSettings();

        $resolve = function (string $key, string $default = '') use ($fileConfig, $envConfig, $dbConfig): string {
            foreach ([$fileConfig[$key] ?? '', $envConfig[$key] ?? '', $dbConfig[$key] ?? ''] as $candidate) {
                $clean = $this->normalizeString($candidate);
                if ($clean !== '' && ! $this->isPlaceholder($clean)) {
                    return $clean;
                }
            }

            return $default;
        };

        return [
            'bank_code' => $resolve('bank_code'),
            'bank_name' => $resolve('bank_name'),
            'bank_account' => $resolve('bank_account'),
            'bank_account_name' => $resolve('bank_account_name'),
            'qr_template' => $resolve('qr_template', 'compact2'),
            'webhook_secret' => $resolve('webhook_secret'),
            'api_token' => $resolve('api_token'),
            'api_base_url' => rtrim($resolve('api_base_url', 'https://my.sepay.vn/userapi'), '/'),
        ];
    }

    /**
     * Kiểm tra tài khoản ngân hàng nhận thanh toán đã hợp lệ chưa.
     */
    public function hasValidBankAccount(?array $config = null): bool
    {
        $cfg = $config ?? $this->getConfig();

        return preg_match('/\A[A-Za-z0-9]{1,20}\z/', $cfg['bank_code']) === 1
            && preg_match('/\A[A-Za-z0-9]{1,19}\z/', $cfg['bank_account']) === 1
            && $cfg['bank_name'] !== ''
            && $cfg['bank_account_name'] !== '';
    }

    /**
     * Kiểm tra hệ thống có thể chủ động gọi SePay User API để đối soát hay không.
     */
    public function canQuerySepayApi(?array $config = null): bool
    {
        $cfg = $config ?? $this->getConfig();

        return $cfg['api_token'] !== '' && $cfg['bank_account'] !== '';
    }

    /**
     * Nhận diện chuẩn SePay API v2 (https://userapi.sepay.vn/v2 hoặc sandbox v2) so với v1 legacy (/userapi).
     */
    public function isApiV2(?string $apiBaseUrl = null): bool
    {
        $base = strtolower(rtrim($apiBaseUrl ?? $this->getConfig()['api_base_url'], '/'));

        return str_ends_with($base, '/v2')
            || str_contains($base, '/v2/')
            || str_contains($base, 'userapi.sepay.vn')
            || str_contains($base, 'userapi-sandbox.sepay.vn');
    }

    /**
     * Tạo liên kết mã QR thanh toán VietQR (chính) và SePay QR (dự phòng).
     *
     * @return array{vietqr_url: string, sepay_qr_url: string, bank_info: array{bank_code: string, bank_name: string, account_number: string, account_name: string, content: string}}
     */
    public function buildQrPayload(Order $order, ?array $config = null): array
    {
        $cfg = $config ?? $this->getConfig();
        $template = preg_match('/\A[A-Za-z0-9_-]{1,30}\z/', $cfg['qr_template']) === 1
            ? $cfg['qr_template']
            : 'compact2';

        $vietQrUrl = 'https://img.vietqr.io/image/'
            .rawurlencode($cfg['bank_code']).'-'
            .rawurlencode($cfg['bank_account']).'-'
            .rawurlencode($template).'.png?'
            .http_build_query([
                'amount' => $order->amount,
                'addInfo' => $order->order_code,
                'accountName' => $cfg['bank_account_name'],
            ]);

        $sepayQrUrl = 'https://qr.sepay.vn/img?'.http_build_query([
            'acc' => $cfg['bank_account'],
            'bank' => $cfg['bank_code'],
            'amount' => $order->amount,
            'des' => $order->order_code,
            'template' => 'compact',
        ]);

        return [
            'vietqr_url' => $vietQrUrl,
            'sepay_qr_url' => $sepayQrUrl,
            'bank_info' => [
                'bank_code' => $cfg['bank_code'],
                'bank_name' => $cfg['bank_name'],
                'account_number' => $cfg['bank_account'],
                'account_name' => $cfg['bank_account_name'],
                'content' => $order->order_code,
            ],
        ];
    }

    /**
     * Xác thực request Webhook từ SePay:
     * 1. Chuẩn chính thức của SePay: Header `Authorization: Apikey <WEBHOOK_SECRET>` (hoặc `X-SePay-Api-Key`).
     * 2. Chuẩn HMAC SHA-256 nội bộ / legacy: `X-SePay-Timestamp` + `X-SePay-Signature: sha256=...`.
     *
     * @return array{ok: bool, http_status: int, method: string, error: ?string}
     */
    public function verifyWebhookRequest(Request $request): array
    {
        $cfg = $this->getConfig();
        $secret = $cfg['webhook_secret'];

        if ($secret === '') {
            return [
                'ok' => false,
                'http_status' => 503,
                'method' => 'none',
                'error' => 'Webhook is not configured',
            ];
        }

        // 1. Chuẩn SePay thật: Authorization: Apikey <API_KEY>
        $authHeader = $this->extractAuthorizationHeader($request);
        if ($authHeader !== '' && preg_match('/\AApikey\s+(.+)\z/i', $authHeader, $matches) === 1) {
            $providedKey = trim($matches[1]);
            if ($providedKey !== '' && hash_equals($secret, $providedKey)) {
                return [
                    'ok' => true,
                    'http_status' => 200,
                    'method' => 'sepay_apikey',
                    'error' => null,
                ];
            }

            return [
                'ok' => false,
                'http_status' => 401,
                'method' => 'sepay_apikey',
                'error' => 'Unauthorized',
            ];
        }

        // 1b. Header dự phòng X-SePay-Api-Key (khi proxy/CGI ẩn Authorization)
        $apiKeyHeader = $this->normalizeString($request->header('X-SePay-Api-Key'));
        if ($apiKeyHeader !== '') {
            if (hash_equals($secret, $apiKeyHeader)) {
                return [
                    'ok' => true,
                    'http_status' => 200,
                    'method' => 'sepay_apikey_header',
                    'error' => null,
                ];
            }

            return [
                'ok' => false,
                'http_status' => 401,
                'method' => 'sepay_apikey_header',
                'error' => 'Unauthorized',
            ];
        }

        // 2. Chuẩn HMAC SHA-256 (X-SePay-Timestamp + X-SePay-Signature)
        $timestamp = $request->header('X-SePay-Timestamp');
        $signature = $request->header('X-SePay-Signature');
        if (! is_string($timestamp) || preg_match('/\A[0-9]{1,12}\z/', $timestamp) !== 1
            || abs(now()->timestamp - (int) $timestamp) > 300
            || ! is_string($signature) || preg_match('/\Asha256=[a-f0-9]{64}\z/', $signature) !== 1) {
            return [
                'ok' => false,
                'http_status' => 401,
                'method' => 'hmac',
                'error' => 'Unauthorized',
            ];
        }

        $expectedSignature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);
        if (! hash_equals($expectedSignature, $signature)) {
            return [
                'ok' => false,
                'http_status' => 401,
                'method' => 'hmac',
                'error' => 'Unauthorized',
            ];
        }

        return [
            'ok' => true,
            'http_status' => 200,
            'method' => 'hmac',
            'error' => null,
        ];
    }

    /**
     * Bóc tách chính xác mã đơn hàng Mây Hẹn (MH + 10 ký tự hoặc legacy 8 ký tự) theo ranh giới từ nghiêm ngặt.
     */
    public function extractOrderCode(?string $paymentCode, string $content): ?string
    {
        if (is_string($paymentCode)) {
            $trimmedCode = trim($paymentCode);
            if (preg_match('/\AMH(?:[A-Z0-9]{10}|[A-Z0-9]{8})\z/i', $trimmedCode) === 1) {
                return strtoupper($trimmedCode);
            }
        }

        if (preg_match('/(?<![A-Z0-9])MH(?:[A-Z0-9]{10}|[A-Z0-9]{8})(?![A-Z0-9])/i', $content, $matches) === 1) {
            return strtoupper($matches[0]);
        }

        return null;
    }

    /**
     * Parse số tiền dương VNĐ từ giao dịch SePay User API (chấp nhận "199000.00" hoặc 199000, từ chối số lẻ ".50").
     */
    public function parseApiAmountVnd(mixed $rawAmount): ?int
    {
        if (is_bool($rawAmount) || $rawAmount === null) {
            return null;
        }

        if (is_int($rawAmount)) {
            return $rawAmount > 0 ? $rawAmount : null;
        }

        if (is_float($rawAmount)) {
            if ($rawAmount <= 0 || floor($rawAmount) !== $rawAmount) {
                return null;
            }

            return (int) $rawAmount;
        }

        if (is_string($rawAmount)) {
            $trimmed = trim($rawAmount);
            if (preg_match('/\A([0-9]+)(?:\.0+)?\z/', $trimmed, $m) === 1) {
                $val = (int) $m[1];

                return $val > 0 ? $val : null;
            }
        }

        return null;
    }

    /**
     * Kiểm tra kết nối trực tiếp tới SePay API (hỗ trợ cả v1 legacy & v2 chuẩn mới).
     *
     * @return array{ok: bool, latency_ms: float, transaction_count: int, api_version: string, message: string}
     */
    public function testApiConnection(): array
    {
        $start = microtime(true);
        $cfg = $this->getConfig();
        $isV2 = $this->isApiV2($cfg['api_base_url']);
        $versionLabel = $isV2 ? 'v2' : 'v1';

        if ($cfg['api_token'] === '') {
            return [
                'ok' => false,
                'latency_ms' => 0.0,
                'transaction_count' => 0,
                'api_version' => $versionLabel,
                'message' => 'Chưa cấu hình SePay User API Token (SEPAY_API_TOKEN).',
            ];
        }

        $query = $isV2 ? ['per_page' => 5] : ['limit' => 5];
        if (! $isV2 && $cfg['bank_account'] !== '') {
            $query['account_number'] = $cfg['bank_account'];
        }

        $result = $this->executeTransactionsRequest($cfg, $query, timeoutSeconds: 10, allowRetry: false);
        $latency = round((microtime(true) - $start) * 1000, 2);

        if (! $result['ok']) {
            return [
                'ok' => false,
                'latency_ms' => $latency,
                'transaction_count' => 0,
                'api_version' => $versionLabel,
                'message' => (string) $result['error'],
            ];
        }

        $count = count($result['transactions']);

        return [
            'ok' => true,
            'latency_ms' => $latency,
            'transaction_count' => $count,
            'api_version' => $versionLabel,
            'message' => "Kết nối SePay API {$versionLabel} thành công ({$latency} ms) — truy vấn được {$count} giao dịch gần nhất.",
        ];
    }

    /**
     * Gọi SePay API (v1 hoặc v2) lấy danh sách giao dịch gần đây cho 1 đơn hàng cụ thể.
     *
     * @return array{ok: bool, http_status: int, retry_after: ?int, error: ?string, transactions: array<int, array<string, mixed>>, has_more: bool}
     */
    public function fetchRecentTransactions(Order $order, ?array $config = null): array
    {
        $cfg = $config ?? $this->getConfig();
        if (! $this->canQuerySepayApi($cfg)) {
            return [
                'ok' => false,
                'http_status' => 503,
                'retry_after' => null,
                'error' => 'Chưa cấu hình SePay User API Token hoặc Số tài khoản thụ hưởng.',
                'transactions' => [],
                'has_more' => false,
            ];
        }

        $minDate = ($order->created_at ? $order->created_at->copy()->subDay() : now()->subDays(7))->format('Y-m-d');
        $isV2 = $this->isApiV2($cfg['api_base_url']);

        $query = $isV2
            ? [
                'q' => strtoupper($order->order_code),
                'transfer_type' => 'in',
                'transaction_date_from' => $minDate,
                'per_page' => 50,
            ]
            : [
                'account_number' => $cfg['bank_account'],
                'transaction_date_min' => $minDate,
                'limit' => 100,
            ];

        return $this->executeTransactionsRequest($cfg, $query, timeoutSeconds: 12, allowRetry: true);
    }

    /**
     * Đối soát hàng loạt các đơn hàng đang chờ (pending) trong Cron Production chỉ bằng 1–2 HTTP call duy nhất.
     * - Tối ưu O(M + N) trong RAM, tuân thủ nghiêm giới hạn 3 req/giây của SePay & xử lý HTTP 429 Retry-After.
     * - Áp dụng chính xác cùng 5 điều kiện bất biến tài chính như webhook và Admin Check SePay.
     *
     * @param  iterable<int, Order>  $orders
     * @return array{
     *     ok: bool,
     *     api_requests: int,
     *     scanned_transactions: int,
     *     settled_count: int,
     *     mismatch_count: int,
     *     rate_limited: bool,
     *     error: ?string,
     *     settled_order_codes: array<int, string>
     * }
     */
    public function reconcilePendingOrdersBatch(iterable $orders, ?array $config = null, int $maxPages = 2): array
    {
        $orderList = $orders instanceof Collection ? $orders->values() : collect($orders)->values();
        if ($orderList->isEmpty()) {
            return [
                'ok' => true,
                'api_requests' => 0,
                'scanned_transactions' => 0,
                'settled_count' => 0,
                'mismatch_count' => 0,
                'rate_limited' => false,
                'error' => null,
                'settled_order_codes' => [],
            ];
        }

        $cfg = $config ?? $this->getConfig();
        if (! $this->canQuerySepayApi($cfg)) {
            return [
                'ok' => false,
                'api_requests' => 0,
                'scanned_transactions' => 0,
                'settled_count' => 0,
                'mismatch_count' => 0,
                'rate_limited' => false,
                'error' => 'Chưa cấu hình SePay User API Token hoặc Số tài khoản thụ hưởng.',
                'settled_order_codes' => [],
            ];
        }

        $oldestCreatedAt = $orderList
            ->map(fn (Order $o) => $o->created_at)
            ->filter()
            ->sort()
            ->first();
        $minDate = ($oldestCreatedAt ? $oldestCreatedAt->copy()->subDay() : now()->subDays(2))->format('Y-m-d');

        $isV2 = $this->isApiV2($cfg['api_base_url']);
        $maxPages = max(1, min(3, $maxPages));
        $allRawTransactions = [];
        $apiRequests = 0;

        for ($page = 1; $page <= $maxPages; $page++) {
            // Giữ nhịp dưới ngưỡng 3 req/giây của SePay API khi đọc trang tiếp theo
            if ($page > 1 && ! app()->runningUnitTests()) {
                usleep(360000);
            }

            $query = $isV2
                ? [
                    'transfer_type' => 'in',
                    'transaction_date_from' => $minDate,
                    'per_page' => 100,
                    'page' => $page,
                ]
                : [
                    'account_number' => $cfg['bank_account'],
                    'transaction_date_min' => $minDate,
                    'limit' => 100,
                ];

            $fetched = $this->executeTransactionsRequest($cfg, $query, timeoutSeconds: 12, allowRetry: true);
            $apiRequests++;

            if (! $fetched['ok']) {
                return [
                    'ok' => false,
                    'api_requests' => $apiRequests,
                    'scanned_transactions' => count($allRawTransactions),
                    'settled_count' => 0,
                    'mismatch_count' => 0,
                    'rate_limited' => $fetched['http_status'] === 429,
                    'error' => $fetched['error'],
                    'settled_order_codes' => [],
                ];
            }

            foreach ($fetched['transactions'] as $tx) {
                $allRawTransactions[] = $tx;
            }

            if (! $isV2 || ! $fetched['has_more']) {
                break;
            }
        }

        // Lập chỉ mục giao dịch tiền vào hợp lệ theo mã đơn hàng MH... một lần duy nhất O(M)
        $indexedByCode = $this->indexValidIncomingTransactions($allRawTransactions, $cfg['bank_account']);

        $settledCount = 0;
        $mismatchCount = 0;
        $settledOrderCodes = [];

        foreach ($orderList as $order) {
            $order->refresh();
            if ($order->status !== 'pending') {
                continue;
            }

            $targetCode = strtoupper($order->order_code);
            $candidates = $indexedByCode[$targetCode] ?? [];
            if ($candidates === []) {
                continue;
            }

            [$exactMatch, $amountMismatchCandidate] = $this->selectOrderTransactionMatch($order, $candidates);

            if ($exactMatch !== null) {
                $providerTransId = $exactMatch['provider_transaction_id'];
                $settlement = app(OrderSettlementService::class)->settleFromSepayApi(
                    $order->id,
                    (int) $exactMatch['amount_in'],
                    $providerTransId,
                    $exactMatch,
                    null,
                    'cron_reconcile'
                );

                $this->recordAuditLog(
                    status: $settlement['log_level'],
                    dbStatus: $settlement['db_log_status'],
                    message: $settlement['log_message'],
                    orderCode: $order->order_code,
                    amount: (int) $exactMatch['amount_in'],
                    transactionId: $providerTransId,
                    ip: '127.0.0.1',
                    content: $exactMatch['content'],
                    rawPayload: $exactMatch['raw'],
                    gateway: 'SePay-Cron'
                );

                $this->stampOrderCheck($order, $settlement['message']);

                if ($settlement['ok'] && $settlement['is_paid']) {
                    $settledCount++;
                    $settledOrderCodes[] = $order->order_code;
                }

                continue;
            }

            if ($amountMismatchCandidate !== null) {
                $mismatchCount++;
                $received = (int) $amountMismatchCandidate['amount_in'];
                $diffLabel = $received < $order->amount ? 'thiếu' : 'thừa';
                $receivedFmt = number_format($received, 0, ',', '.');
                $expectedFmt = number_format($order->amount, 0, ',', '.');
                $ref = $amountMismatchCandidate['provider_transaction_id'] ?? 'N/A';

                $msg = "Cron đối soát phát hiện giao dịch SePay [{$ref}] chứa mã đơn #{$order->order_code} nhưng số tiền chuyển {$diffLabel} (nhận {$receivedFmt}đ / yêu cầu {$expectedFmt}đ). Không tự động kích hoạt.";

                $this->stampOrderCheck($order, $msg);
                $this->recordAuditLog(
                    status: 'error',
                    dbStatus: 'mismatch',
                    message: $msg,
                    orderCode: $order->order_code,
                    amount: $received,
                    transactionId: $amountMismatchCandidate['provider_transaction_id'],
                    ip: '127.0.0.1',
                    content: $amountMismatchCandidate['content'],
                    rawPayload: $amountMismatchCandidate['raw'],
                    gateway: 'SePay-Cron'
                );
            }
        }

        return [
            'ok' => true,
            'api_requests' => $apiRequests,
            'scanned_transactions' => count($allRawTransactions),
            'settled_count' => $settledCount,
            'mismatch_count' => $mismatchCount,
            'rate_limited' => false,
            'error' => null,
            'settled_order_codes' => $settledOrderCodes,
        ];
    }

    /**
     * Thực thi HTTP GET tới endpoint danh sách giao dịch SePay (tự động chọn v1/v2 & xử lý 401/422/429).
     *
     * @param  array<string, string>  $cfg
     * @param  array<string, mixed>  $query
     * @return array{ok: bool, http_status: int, retry_after: ?int, error: ?string, transactions: array<int, array<string, mixed>>, has_more: bool}
     */
    protected function executeTransactionsRequest(array $cfg, array $query, int $timeoutSeconds = 12, bool $allowRetry = true): array
    {
        $isV2 = $this->isApiV2($cfg['api_base_url']);
        $endpoint = $cfg['api_base_url'].($isV2 ? '/transactions' : '/transactions/list');

        try {
            $client = Http::withToken($cfg['api_token'])
                ->acceptJson()
                ->timeout($timeoutSeconds);

            if ($allowRetry) {
                // Chỉ retry khi lỗi kết nối mạng hoặc 5xx từ SePay — tuyệt đối không spam khi gặp 401/422/429
                $client = $client->retry(
                    1,
                    250,
                    fn (\Throwable $exception): bool => $exception instanceof ConnectionException,
                    throw: false
                );
            }

            /** @var Response $response */
            $response = $client->get($endpoint, $query);

            if ($response->status() === 429) {
                $retryAfterRaw = $response->header('Retry-After') ?: $response->header('x-sepay-userapi-retry-after');
                $retryAfter = is_numeric($retryAfterRaw) ? max(1, (int) $retryAfterRaw) : 5;

                return [
                    'ok' => false,
                    'http_status' => 429,
                    'retry_after' => $retryAfter,
                    'error' => "SePay API tạm giới hạn tốc độ (HTTP 429 — 3 req/giây). Vui lòng thử lại sau {$retryAfter} giây.",
                    'transactions' => [],
                    'has_more' => false,
                ];
            }

            if ($response->status() === 401) {
                return [
                    'ok' => false,
                    'http_status' => 401,
                    'retry_after' => null,
                    'error' => 'SePay API Token không hợp lệ hoặc đã hết hạn (HTTP 401). Vui lòng cập nhật lại tại Cài đặt Ngân hàng.',
                    'transactions' => [],
                    'has_more' => false,
                ];
            }

            if (! $response->successful()) {
                $errJson = $response->json();
                $detail = is_array($errJson)
                    ? ($errJson['message'] ?? $errJson['error_code'] ?? $errJson['error'] ?? null)
                    : null;
                $suffix = is_string($detail) && trim($detail) !== '' ? ' — '.trim($detail) : '';

                return [
                    'ok' => false,
                    'http_status' => $response->status(),
                    'retry_after' => null,
                    'error' => "SePay API phản hồi lỗi HTTP {$response->status()}{$suffix}. Vui lòng kiểm tra lại cấu hình SePay User API Token.",
                    'transactions' => [],
                    'has_more' => false,
                ];
            }

            $json = $response->json();
            if (! is_array($json)) {
                return [
                    'ok' => false,
                    'http_status' => 502,
                    'retry_after' => null,
                    'error' => 'Dữ liệu phản hồi từ SePay API không đúng định dạng JSON.',
                    'transactions' => [],
                    'has_more' => false,
                ];
            }

            if (isset($json['error']) && $json['error'] !== null && $json['error'] !== '') {
                return [
                    'ok' => false,
                    'http_status' => 502,
                    'retry_after' => null,
                    'error' => 'SePay API báo lỗi: '.(is_string($json['error']) ? $json['error'] : json_encode($json['error'])),
                    'transactions' => [],
                    'has_more' => false,
                ];
            }

            $hasMore = (bool) data_get($json, 'meta.pagination.has_more', false);

            return [
                'ok' => true,
                'http_status' => $response->status(),
                'retry_after' => null,
                'error' => null,
                'transactions' => $this->extractTransactionsArray($json),
                'has_more' => $hasMore,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'http_status' => 504,
                'retry_after' => null,
                'error' => 'Không thể kết nối SePay API: '.$e->getMessage(),
                'transactions' => [],
                'has_more' => false,
            ];
        }
    }

    /**
     * Lọc giao dịch tiền vào thỏa 3 điều kiện đầu (tiền vào thật, đúng STK, khớp mã đơn MH...) và lập chỉ mục theo mã đơn.
     *
     * @param  array<int, mixed>  $rawTransactions
     * @return array<string, array<int, array<string, mixed>>>
     */
    protected function indexValidIncomingTransactions(array $rawTransactions, string $expectedAccount): array
    {
        $indexed = [];

        foreach ($rawTransactions as $rawTx) {
            if (! is_array($rawTx)) {
                continue;
            }

            $tx = $this->normalizeApiTransaction($rawTx);

            // Điều kiện 1: Phải là giao dịch tiền vào thật (amount_in > 0 và không phải tiền ra)
            if ($tx['is_outgoing'] || $tx['amount_in'] === null || $tx['amount_in'] <= 0 || $tx['amount_out'] > 0) {
                continue;
            }

            // Điều kiện 2: Đúng số tài khoản thụ hưởng (hoặc tài khoản ảo sub_account/va gắn cùng tài khoản)
            $accountMatches = $tx['account_number'] === $expectedAccount
                || ($tx['sub_account'] !== '' && $tx['sub_account'] === $expectedAccount);
            if (! $accountMatches) {
                continue;
            }

            // Điều kiện 3: Khớp chính xác mã đơn hàng theo ranh giới từ
            $detectedCode = $this->extractOrderCode($tx['code'], $tx['content']);
            if ($detectedCode === null) {
                continue;
            }

            $indexed[$detectedCode][] = $tx;
        }

        return $indexed;
    }

    /**
     * Điều kiện 4: Chọn giao dịch khớp chính xác số tiền của đơn hàng (hoặc ghi nhận giao dịch lệch tiền).
     *
     * @param  array<int, array<string, mixed>>  $candidates
     * @return array{0: ?array<string, mixed>, 1: ?array<string, mixed>}
     */
    protected function selectOrderTransactionMatch(Order $order, array $candidates): array
    {
        $mismatch = null;

        foreach ($candidates as $tx) {
            if ((int) $tx['amount_in'] === $order->amount) {
                return [$tx, null];
            }

            $mismatch = $tx;
        }

        return [null, $mismatch];
    }

    /**
     * Đối soát chủ động một đơn hàng trực tiếp với SePay User API theo đủ 5 điều kiện bất biến tài chính:
     * 1. Giao dịch tiền vào thật (amount_in > 0, amount_out = 0).
     * 2. Đúng số tài khoản thụ hưởng (account_number / sub_account).
     * 3. Khớp chính xác mã đơn hàng (MH...) theo ranh giới từ nghiêm ngặt.
     * 4. Khớp chính xác số tiền (amount_in === order.amount); nếu lệch số tiền tuyệt đối không tự kích hoạt.
     * 5. Chống tái sử dụng mã giao dịch cho 2 đơn (anti-replay + DB lock) & kích hoạt đúng gói của đơn (Plus/Pro).
     *
     * @return array{
     *     ok: bool,
     *     matched: bool,
     *     is_paid: bool,
     *     status: string,
     *     flash_type: string,
     *     message: string,
     *     http_status: int,
     *     transaction: ?array<string, mixed>
     * }
     */
    public function reconcileOrderWithSepay(Order $order, ?User $actor = null, string $trigger = 'admin'): array
    {
        $order->refresh();
        $order->loadMissing(['plan', 'user', 'invitation']);

        if ($order->isPaid()) {
            return [
                'ok' => true,
                'matched' => true,
                'is_paid' => true,
                'status' => 'already_paid',
                'flash_type' => 'info',
                'message' => "Đơn hàng #{$order->order_code} đã được thanh toán và kích hoạt trước đó.",
                'http_status' => 200,
                'transaction' => null,
            ];
        }

        if ($order->isRefunded()) {
            return [
                'ok' => false,
                'matched' => false,
                'is_paid' => false,
                'status' => 'blocked_refunded',
                'flash_type' => 'error',
                'message' => "Đơn hàng #{$order->order_code} đã hoàn tiền, không thể đối soát kích hoạt lại.",
                'http_status' => 409,
                'transaction' => null,
            ];
        }

        if (! $order->canBeVerifiedWithSepay()) {
            return [
                'ok' => false,
                'matched' => false,
                'is_paid' => false,
                'status' => 'invalid_status',
                'flash_type' => 'error',
                'message' => "Đơn hàng #{$order->order_code} đang ở trạng thái [{$order->status}], không thể kiểm tra kích hoạt.",
                'http_status' => 409,
                'transaction' => null,
            ];
        }

        $cfg = $this->getConfig();
        if (! $this->canQuerySepayApi($cfg)) {
            $msg = 'Chưa cấu hình SePay User API Token hoặc Số tài khoản thụ hưởng. Vui lòng thiết lập tại Cài đặt Ngân hàng hoặc SEPAY_API_TOKEN trong .env.';
            $this->stampOrderCheck($order, $msg);

            return [
                'ok' => false,
                'matched' => false,
                'is_paid' => false,
                'status' => 'not_configured',
                'flash_type' => 'error',
                'message' => $msg,
                'http_status' => 503,
                'transaction' => null,
            ];
        }

        $fetched = $this->fetchRecentTransactions($order, $cfg);
        if (! $fetched['ok']) {
            $msg = (string) $fetched['error'];
            $this->stampOrderCheck($order, $msg);

            return [
                'ok' => false,
                'matched' => false,
                'is_paid' => false,
                'status' => $fetched['http_status'] === 429 ? 'rate_limited' : 'api_error',
                'flash_type' => $fetched['http_status'] === 429 ? 'warning' : 'error',
                'message' => $msg,
                'http_status' => $fetched['http_status'] === 429 ? 429 : 502,
                'transaction' => null,
            ];
        }

        $targetOrderCode = strtoupper($order->order_code);
        $expectedAccount = $cfg['bank_account'];
        $transactions = $fetched['transactions'];
        $indexed = $this->indexValidIncomingTransactions($transactions, $expectedAccount);
        [$exactMatch, $amountMismatchCandidate] = $this->selectOrderTransactionMatch(
            $order,
            $indexed[$targetOrderCode] ?? []
        );

        if ($exactMatch !== null) {
            $providerTransId = $exactMatch['provider_transaction_id'];
            $settlement = app(OrderSettlementService::class)->settleFromSepayApi(
                $order->id,
                $exactMatch['amount_in'],
                $providerTransId,
                $exactMatch,
                $actor,
                $trigger
            );

            $this->recordAuditLog(
                status: $settlement['log_level'],
                dbStatus: $settlement['db_log_status'],
                message: $settlement['log_message'],
                orderCode: $order->order_code,
                amount: $exactMatch['amount_in'],
                transactionId: $providerTransId,
                ip: request()->ip(),
                content: $exactMatch['content'],
                rawPayload: $exactMatch['raw'],
                gateway: 'SePay-API'
            );

            $this->stampOrderCheck($order, $settlement['message']);

            return [
                'ok' => $settlement['ok'],
                'matched' => $settlement['ok'],
                'is_paid' => $settlement['is_paid'],
                'status' => $settlement['status'],
                'flash_type' => $settlement['flash_type'],
                'message' => $settlement['message'],
                'http_status' => $settlement['http_status'],
                'transaction' => $exactMatch,
            ];
        }

        if ($amountMismatchCandidate !== null) {
            $received = (int) $amountMismatchCandidate['amount_in'];
            $diffLabel = $received < $order->amount ? 'thiếu' : 'thừa';
            $receivedFmt = number_format($received, 0, ',', '.');
            $expectedFmt = number_format($order->amount, 0, ',', '.');
            $ref = $amountMismatchCandidate['provider_transaction_id'] ?? 'N/A';

            $msg = "Tìm thấy giao dịch SePay [{$ref}] chứa mã đơn #{$order->order_code} nhưng số tiền chuyển {$diffLabel} (nhận {$receivedFmt}đ / yêu cầu {$expectedFmt}đ). Hệ thống không tự động kích hoạt để bảo vệ đối soát tài chính.";

            $this->stampOrderCheck($order, $msg);
            $this->recordAuditLog(
                status: 'error',
                dbStatus: 'mismatch',
                message: $msg,
                orderCode: $order->order_code,
                amount: $received,
                transactionId: $amountMismatchCandidate['provider_transaction_id'],
                ip: request()->ip(),
                content: $amountMismatchCandidate['content'],
                rawPayload: $amountMismatchCandidate['raw'],
                gateway: 'SePay-API'
            );

            return [
                'ok' => false,
                'matched' => false,
                'is_paid' => false,
                'status' => 'amount_mismatch',
                'flash_type' => 'error',
                'message' => $msg,
                'http_status' => 400,
                'transaction' => $amountMismatchCandidate,
            ];
        }

        $expectedFmt = number_format($order->amount, 0, ',', '.');
        $scannedCount = count($transactions);
        $msg = "Đã đối soát trực tiếp với SePay ({$scannedCount} giao dịch gần nhất) nhưng chưa tìm thấy giao dịch tiền vào khớp mã đơn #{$order->order_code} và số tiền {$expectedFmt}đ.";
        $this->stampOrderCheck($order, $msg);

        return [
            'ok' => false,
            'matched' => false,
            'is_paid' => false,
            'status' => 'not_found',
            'flash_type' => 'warning',
            'message' => $msg,
            'http_status' => 200,
            'transaction' => null,
        ];
    }

    /**
     * Ghi đồng bộ nhật ký Webhook / Đối soát SePay vào cả DB (payment_webhook_logs) lẫn file JSONL.
     *
     * @param  array<string, mixed>|null  $rawPayload
     */
    public function recordAuditLog(
        string $status,
        string $dbStatus,
        string $message,
        ?string $orderCode = null,
        ?int $amount = null,
        ?string $transactionId = null,
        ?string $ip = null,
        ?string $content = null,
        ?array $rawPayload = null,
        string $gateway = 'SePay'
    ): void {
        try {
            $logPath = storage_path('logs/payment_webhooks.jsonl');
            $dir = dirname($logPath);
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $entry = json_encode([
                'timestamp' => now()->toIso8601String(),
                'ip' => $ip ?? request()->ip(),
                'status' => $status,
                'message' => $message,
                'order_code' => $orderCode,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'content' => $content ?? '',
                'gateway' => $gateway,
            ], JSON_UNESCAPED_UNICODE);

            File::append($logPath, $entry."\n");
        } catch (\Throwable) {
            // Không làm gián đoạn phản hồi webhook nếu lỗi ghi file log
        }

        try {
            PaymentWebhookLog::create([
                'gateway' => strtolower($gateway),
                'transaction_id' => $transactionId ? mb_substr($transactionId, 0, 100) : null,
                'order_code' => $orderCode ? mb_substr(strtoupper($orderCode), 0, 50) : null,
                'amount' => max(0, (int) ($amount ?? 0)),
                'status' => $dbStatus,
                'error_reason' => in_array($dbStatus, ['success', 'duplicate'], true) ? null : mb_substr($message, 0, 255),
                'raw_payload' => $rawPayload ?? [
                    'message' => $message,
                    'content' => $content ?? '',
                ],
                'ip_address' => $ip ?? request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Không làm gián đoạn phản hồi webhook nếu lỗi ghi log DB
        }
    }

    /**
     * Lưu thời điểm và thông điệp lần kiểm tra SePay gần nhất trên đơn hàng.
     */
    protected function stampOrderCheck(Order $order, string $message): void
    {
        try {
            $order->forceFill([
                'last_sepay_check_at' => now(),
                'last_sepay_check_message' => mb_substr($message, 0, 255),
            ])->save();
        } catch (\Throwable) {
            // Best-effort metadata update
        }
    }

    /**
     * Trích xuất danh sách giao dịch từ response SePay API (hỗ trợ cả v1 `transactions` và v2 `data`).
     *
     * @param  array<string, mixed>  $json
     * @return array<int, array<string, mixed>>
     */
    protected function extractTransactionsArray(array $json): array
    {
        if (isset($json['transactions']) && is_array($json['transactions'])) {
            return $json['transactions'];
        }

        if (isset($json['data']) && is_array($json['data'])) {
            return $json['data'];
        }

        return [];
    }

    /**
     * Chuẩn hóa một bản ghi giao dịch từ SePay API (hỗ trợ cả v1 legacy lẫn v2 UUID/integer).
     *
     * @param  array<string, mixed>  $raw
     * @return array{
     *     provider_transaction_id: ?string,
     *     reference_number: ?string,
     *     account_number: string,
     *     sub_account: string,
     *     bank_brand_name: string,
     *     transaction_date: ?string,
     *     is_outgoing: bool,
     *     amount_in: ?int,
     *     amount_out: float,
     *     code: ?string,
     *     content: string,
     *     raw: array<string, mixed>
     * }
     */
    protected function normalizeApiTransaction(array $raw): array
    {
        $id = $this->normalizeString($raw['id'] ?? null);
        $reference = $this->normalizeString($raw['reference_number'] ?? $raw['referenceCode'] ?? null);
        $providerTransId = $id !== '' ? $id : ($reference !== '' ? $reference : null);

        $transferType = strtolower($this->normalizeString($raw['transfer_type'] ?? $raw['transferType'] ?? null));
        $isOutgoing = $transferType === 'out';

        $rawAmountIn = $raw['amount_in'] ?? ($transferType === 'in' ? ($raw['transferAmount'] ?? null) : null);
        $rawAmountOut = $raw['amount_out'] ?? ($isOutgoing ? ($raw['transferAmount'] ?? 0) : 0);

        return [
            'provider_transaction_id' => $providerTransId,
            'reference_number' => $reference !== '' ? $reference : null,
            'account_number' => $this->normalizeString($raw['account_number'] ?? $raw['accountNumber'] ?? null),
            'sub_account' => $this->normalizeString($raw['sub_account'] ?? $raw['subAccount'] ?? $raw['va'] ?? null),
            'bank_brand_name' => $this->normalizeString($raw['bank_brand_name'] ?? $raw['gateway'] ?? null),
            'transaction_date' => $this->normalizeString($raw['transaction_date'] ?? $raw['transactionDate'] ?? null) ?: null,
            'is_outgoing' => $isOutgoing,
            'amount_in' => $this->parseApiAmountVnd($rawAmountIn),
            'amount_out' => is_numeric($rawAmountOut) ? (float) $rawAmountOut : 0.0,
            'code' => $this->normalizeString($raw['code'] ?? null) ?: null,
            'content' => $this->normalizeString($raw['transaction_content'] ?? $raw['content'] ?? $raw['description'] ?? null),
            'raw' => $raw,
        ];
    }

    protected function extractAuthorizationHeader(Request $request): string
    {
        $candidates = [
            $request->header('Authorization'),
            $request->server('HTTP_AUTHORIZATION'),
            $request->server('REDIRECT_HTTP_AUTHORIZATION'),
        ];

        foreach ($candidates as $header) {
            if (is_string($header) && trim($header) !== '') {
                return trim($header);
            }
        }

        return '';
    }

    /**
     * @return array<string, string>
     */
    protected function readBankingJsonFile(): array
    {
        $path = storage_path('app/settings/banking.json');
        if (! File::exists($path)) {
            return [];
        }

        try {
            $decoded = json_decode((string) File::get($path), true);
            if (! is_array($decoded)) {
                return [];
            }

            return [
                'bank_code' => $this->normalizeString($decoded['bank_code'] ?? null),
                'bank_name' => $this->normalizeString($decoded['bank_name'] ?? null),
                'bank_account' => $this->normalizeString($decoded['bank_account'] ?? null),
                'bank_account_name' => $this->normalizeString($decoded['bank_account_name'] ?? null),
                'qr_template' => $this->normalizeString($decoded['qr_template'] ?? null),
                'webhook_secret' => $this->normalizeString($decoded['webhook_secret'] ?? null),
                'api_token' => $this->normalizeString($decoded['api_token'] ?? null),
                'api_base_url' => $this->normalizeString($decoded['api_base_url'] ?? null),
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, string>
     */
    protected function readPlatformPaymentSettings(): array
    {
        try {
            $rows = PlatformSetting::query()
                ->where('group', PlatformSetting::GROUP_PAYMENT)
                ->get();

            $map = [];
            foreach ($rows as $row) {
                $val = $row->is_secret ? $row->decrypted_value : $row->value;
                $map[$row->key] = $this->normalizeString($val);
            }

            return [
                'bank_code' => $map['bank_code'] ?? '',
                'bank_name' => $map['bank_name'] ?? '',
                'bank_account' => $map['bank_account'] ?? '',
                'bank_account_name' => $map['bank_account_name'] ?? '',
                'webhook_secret' => $map['webhook_signing_secret'] ?? '',
                'api_token' => $map['sepay_api_token'] ?? '',
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    protected function normalizeString(mixed $value): string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return '';
        }

        return trim((string) $value);
    }

    protected function isPlaceholder(string $value): bool
    {
        return in_array($value, self::PLACEHOLDER_VALUES, true);
    }
}
