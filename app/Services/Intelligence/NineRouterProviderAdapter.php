<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Services\Intelligence\Contracts\LlmProviderInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class NineRouterProviderAdapter implements LlmProviderInterface
{
    protected const CIRCUIT_FAILURES_KEY = 'mayhen_ninerouter_circuit_failures';

    protected const CIRCUIT_OPEN_UNTIL_KEY = 'mayhen_ninerouter_circuit_open_until';

    protected const CIRCUIT_THRESHOLD = 6;

    protected const CIRCUIT_COOLDOWN_SECONDS = 45;

    public function __construct(
        protected AiGovernanceService $governance
    ) {}

    public function providerName(): string
    {
        return 'ninerouter';
    }

    public function isAvailable(): bool
    {
        if (! (bool) config('services.ninerouter.enabled', false)) {
            return false;
        }

        if ($this->resolveBaseUrl() === '') {
            return false;
        }

        $openUntil = Cache::get(self::CIRCUIT_OPEN_UNTIL_KEY);
        if (is_int($openUntil) && $openUntil > time()) {
            return false;
        }

        return true;
    }

    public function resolveBaseUrl(): string
    {
        $policyUrl = trim((string) ($this->governance->getPolicy()['ninerouter_base_url'] ?? ''));
        $raw = $policyUrl !== '' ? $policyUrl : (string) config('services.ninerouter.base_url', '');

        return $this->normalizeBaseUrl($raw);
    }

    protected function resolveApiKey(): string
    {
        $policyKey = trim((string) ($this->governance->getPolicy()['ninerouter_api_key'] ?? ''));

        return $policyKey !== '' ? $policyKey : trim((string) config('services.ninerouter.api_key', ''));
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{ok: bool, text: string, model: string, status: string, prompt_tokens: int, completion_tokens: int}
     */
    public function completeChat(
        array $messages,
        ?string $model = null,
        float $temperature = 0.25,
        int $maxTokens = 400,
        ?int $timeoutSeconds = null
    ): array {
        $policy = $this->governance->getPolicy();
        $baseUrl = $this->resolveBaseUrl();
        $resolvedModel = trim((string) ($model ?: $policy['draft_model'] ?: config('services.ninerouter.model', '')));
        $apiKey = $this->resolveApiKey();
        $timeout = max(5, min(90, (int) ($timeoutSeconds ?? config('services.ninerouter.timeout', 20))));

        if (! (bool) config('services.ninerouter.enabled', false) || $baseUrl === '' || $resolvedModel === '') {
            return $this->failChat($resolvedModel, 'disabled_or_unconfigured');
        }

        if (! $this->isAvailable()) {
            return $this->failChat($resolvedModel, 'circuit_open');
        }

        $candidateModels = array_values(array_unique(array_filter([
            $resolvedModel,
            trim((string) ($policy['reviewer_model'] ?? '')),
            trim((string) ($policy['draft_model'] ?? '')),
            trim((string) config('services.ninerouter.model', '')),
            trim((string) config('services.ninerouter.reviewer_model', '')),
        ], fn (string $m): bool => $m !== '')));

        $lastFailure = null;

        foreach ($candidateModels as $idx => $activeModel) {
            try {
                $http = Http::timeout($timeout)->acceptJson();
                if ($apiKey !== '') {
                    $http = $http->withToken($apiKey);
                }

                $response = $http->post($baseUrl.'/chat/completions', [
                    'model' => $activeModel,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                    'stream' => false,
                ]);

                if (! $response->successful()) {
                    $status = $response->status();
                    $errorCode = (string) ($response->json('error.code') ?? $response->json('error.type') ?? 'error');
                    $errorMsg = trim((string) ($response->json('error.message') ?? $response->json('message') ?? ''));
                    $cleanCode = substr(preg_replace('/[^a-zA-Z0-9_]/', '', $errorCode) ?? 'err', 0, 32) ?: 'error';
                    $shortReason = $errorMsg !== ''
                        ? 'http_'.$status.'_'.$cleanCode.': '.mb_substr($errorMsg, 0, 90, 'UTF-8')
                        : 'http_'.$status.'_'.$cleanCode;

                    $lastFailure = $this->failChat($activeModel, $shortReason);

                    // Nếu model đầu tiên bị lỗi 500/502/503/504/429/404 và còn model dự phòng khác (ví dụ my-combo lỗi 503 -> tự chuyển sang nobi/gpt-6.1-sol)
                    if (in_array($status, [404, 429, 500, 502, 503, 504], true) && isset($candidateModels[$idx + 1])) {
                        continue;
                    }

                    if ($status >= 500) {
                        $this->recordFailure();
                    }

                    return $lastFailure;
                }

                $this->resetCircuit();

                $json = $response->json();
                $text = $this->extractText(is_array($json) ? $json : []);
                $promptTokens = (int) ($json['usage']['prompt_tokens'] ?? $json['usage']['input_tokens'] ?? 0);
                $completionTokens = (int) ($json['usage']['completion_tokens'] ?? $json['usage']['output_tokens'] ?? 0);

                $this->governance->recordDailyTokens($promptTokens + $completionTokens);

                if ($text === '') {
                    $lastFailure = [
                        'ok' => false,
                        'text' => '',
                        'model' => $activeModel,
                        'status' => 'empty_response',
                        'prompt_tokens' => $promptTokens,
                        'completion_tokens' => $completionTokens,
                    ];

                    if (isset($candidateModels[$idx + 1])) {
                        continue;
                    }

                    return $lastFailure;
                }

                return [
                    'ok' => true,
                    'text' => $text,
                    'model' => $activeModel,
                    'status' => 'ok',
                    'prompt_tokens' => $promptTokens,
                    'completion_tokens' => $completionTokens,
                ];
            } catch (Throwable $e) {
                $errMsg = strtolower($e->getMessage());
                $isTimeout = str_contains($errMsg, 'timed out') || str_contains($errMsg, 'curl error 28') || str_contains($errMsg, 'timeout');
                $lastFailure = $this->failChat($activeModel, $isTimeout ? 'request_timeout' : 'connection_error');

                if (isset($candidateModels[$idx + 1])) {
                    continue;
                }

                if (! $isTimeout) {
                    $this->recordFailure();
                }

                return $lastFailure;
            }
        }

        return $lastFailure ?? $this->failChat($resolvedModel, 'unknown_error');
    }

    /**
     * @return array{ok: bool, data: array<string, mixed>, raw: string, model: string, status: string, prompt_tokens: int, completion_tokens: int}
     */
    public function completeJson(
        string $systemPrompt,
        string $userPrompt,
        ?string $model = null,
        float $temperature = 0.2,
        int $maxTokens = 1600,
        ?int $timeoutSeconds = null
    ): array {
        $timeout = $timeoutSeconds ?? max(20, min(90, (int) config('services.ninerouter.timeout', 20) * 2));
        $result = $this->completeChat(
            messages: [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            model: $model,
            temperature: $temperature,
            maxTokens: $maxTokens,
            timeoutSeconds: $timeout
        );

        if (! $result['ok']) {
            return [
                'ok' => false,
                'data' => [],
                'raw' => '',
                'model' => $result['model'],
                'status' => $result['status'],
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
            ];
        }

        $decoded = $this->decodeJsonBlock($result['text']);
        if (! is_array($decoded)) {
            return [
                'ok' => false,
                'data' => [],
                'raw' => $result['text'],
                'model' => $result['model'],
                'status' => 'invalid_json',
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
            ];
        }

        return [
            'ok' => true,
            'data' => $decoded,
            'raw' => $result['text'],
            'model' => $result['model'],
            'status' => 'ok',
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
        ];
    }

    /**
     * @return array{ok: bool, models: list<string>, status: string}
     */
    public function listModels(): array
    {
        $baseUrl = $this->resolveBaseUrl();
        $apiKey = $this->resolveApiKey();
        $timeout = max(5, min(15, (int) config('services.ninerouter.timeout', 20)));

        if ($baseUrl === '') {
            return ['ok' => false, 'models' => [], 'status' => 'unconfigured'];
        }

        try {
            $http = Http::timeout($timeout)->acceptJson();
            if ($apiKey !== '') {
                $http = $http->withToken($apiKey);
            }

            $response = $http->get($baseUrl.'/models');
            if (! $response->successful()) {
                return ['ok' => false, 'models' => [], 'status' => 'http_'.$response->status()];
            }

            $data = $response->json('data');
            $models = [];
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (is_array($item) && isset($item['id']) && is_string($item['id'])) {
                        $models[] = $item['id'];
                    }
                }
            }

            return ['ok' => true, 'models' => $models, 'status' => 'ok'];
        } catch (Throwable) {
            return ['ok' => false, 'models' => [], 'status' => 'connection_error'];
        }
    }

    /**
     * Chẩn đoán trực tiếp kết nối tới Endpoint 9Router / OpenAI API và chạy thử cả 2 mô hình (Draft + Reviewer).
     * Cho phép Admin kiểm tra trước khi lưu hoặc trước khi bật Tự học.
     *
     * @return array{
     *     ok: bool,
     *     partial_ok: bool,
     *     base_url: string,
     *     has_api_key: bool,
     *     endpoint_check: array{ok: bool, http_status: int, latency_ms: float, models_count: int, models: list<string>, message: string},
     *     draft_check: array{ok: bool, model: string, http_status: int, latency_ms: float, reply: string, message: string},
     *     reviewer_check: array{ok: bool, model: string, http_status: int, latency_ms: float, reply: string, message: string},
     *     summary: string
     * }
     */
    public function diagnoseConnection(
        ?string $overrideBaseUrl = null,
        ?string $overrideApiKey = null,
        ?string $overrideDraftModel = null,
        ?string $overrideReviewerModel = null
    ): array {
        $policy = $this->governance->getPolicy();
        $rawUrl = trim((string) ($overrideBaseUrl ?? '')) !== ''
            ? trim((string) $overrideBaseUrl)
            : $this->resolveBaseUrl();
        $baseUrl = $this->normalizeBaseUrl($rawUrl);

        $apiKey = trim((string) ($overrideApiKey ?? '')) !== ''
            ? trim((string) $overrideApiKey)
            : $this->resolveApiKey();

        $draftModel = trim((string) ($overrideDraftModel ?? '')) !== ''
            ? trim((string) $overrideDraftModel)
            : trim((string) ($policy['draft_model'] ?: config('services.ninerouter.model', '')));

        $reviewerModel = trim((string) ($overrideReviewerModel ?? '')) !== ''
            ? trim((string) $overrideReviewerModel)
            : trim((string) ($policy['reviewer_model'] ?: $draftModel));

        if ($baseUrl === '') {
            $emptyCheck = [
                'ok' => false,
                'model' => '',
                'http_status' => 0,
                'latency_ms' => 0.0,
                'reply' => '',
                'message' => 'Chưa nhập Địa chỉ máy chủ AI (NINEROUTER_BASE_URL).',
            ];

            return [
                'ok' => false,
                'partial_ok' => false,
                'base_url' => '',
                'has_api_key' => $apiKey !== '',
                'endpoint_check' => [
                    'ok' => false,
                    'http_status' => 0,
                    'latency_ms' => 0.0,
                    'models_count' => 0,
                    'models' => [],
                    'message' => 'Chưa cấu hình URL máy chủ AI.',
                ],
                'draft_check' => $emptyCheck,
                'reviewer_check' => $emptyCheck,
                'summary' => 'Chưa có địa chỉ máy chủ AI (NINEROUTER_BASE_URL).',
            ];
        }

        // 1. Ping GET /models
        $t0 = microtime(true);
        $models = [];
        $endpointCheck = [
            'ok' => false,
            'http_status' => 0,
            'latency_ms' => 0.0,
            'models_count' => 0,
            'models' => [],
            'message' => '',
        ];

        try {
            $http = Http::timeout(8)->acceptJson();
            if ($apiKey !== '') {
                $http = $http->withToken($apiKey);
            }
            $res = $http->get($baseUrl.'/models');
            $endpointCheck['latency_ms'] = round((microtime(true) - $t0) * 1000, 1);
            $endpointCheck['http_status'] = $res->status();

            if ($res->successful()) {
                $data = $res->json('data');
                if (is_array($data)) {
                    foreach ($data as $item) {
                        if (is_array($item) && isset($item['id']) && is_string($item['id'])) {
                            $models[] = $item['id'];
                        }
                    }
                }
                $endpointCheck['ok'] = true;
                $endpointCheck['models_count'] = count($models);
                $endpointCheck['models'] = array_slice($models, 0, 60);
                $endpointCheck['message'] = 'Kết nối máy chủ thành công ('.count($models).' models khả dụng).';
            } else {
                $errDetail = trim((string) ($res->json('error.message') ?? $res->json('message') ?? ''));
                $endpointCheck['message'] = 'HTTP '.$res->status().($errDetail !== '' ? ': '.$errDetail : ' khi gọi /models.');
            }
        } catch (Throwable $e) {
            $endpointCheck['latency_ms'] = round((microtime(true) - $t0) * 1000, 1);
            $endpointCheck['message'] = 'Không kết nối được tới '.$baseUrl.' ('.mb_substr($e->getMessage(), 0, 110, 'UTF-8').').';
        }

        // 2. Test Draft Model
        $draftCheck = $this->probeSingleModel($baseUrl, $apiKey, $draftModel);

        // 3. Test Reviewer Model (nếu trùng Draft Model thì dùng chung kết quả để tiết kiệm thời gian)
        $reviewerCheck = ($reviewerModel !== '' && $reviewerModel !== $draftModel)
            ? $this->probeSingleModel($baseUrl, $apiKey, $reviewerModel)
            : $draftCheck;

        if ($draftCheck['ok'] || $reviewerCheck['ok']) {
            $this->resetCircuit();
        }

        $allOk = $draftCheck['ok'] && $reviewerCheck['ok'];
        $partialOk = $draftCheck['ok'] || $reviewerCheck['ok'];

        if ($allOk) {
            $summary = 'Tất cả mô hình AI hoạt động tốt! Bạn có thể Lưu cài đặt và chạy Tự học ngay.';
        } elseif ($partialOk) {
            $workingModel = $draftCheck['ok'] ? $draftCheck['model'] : $reviewerCheck['model'];
            $failedModel = ! $draftCheck['ok'] ? $draftCheck['model'] : $reviewerCheck['model'];
            $summary = "Mô hình [{$workingModel}] hoạt động tốt, nhưng mô hình [{$failedModel}] đang bị lỗi. Hệ thống sẽ tự động dùng [{$workingModel}] làm dự phòng, hoặc bạn nên đổi cả 2 ô sang [{$workingModel}].";
        } else {
            $summary = 'Cả 2 mô hình AI đều chưa phản hồi thành công. Vui lòng kiểm tra lại URL máy chủ, Khóa API hoặc chọn tên mô hình khác trong danh sách khả dụng bên dưới.';
        }

        return [
            'ok' => $allOk,
            'partial_ok' => $partialOk,
            'base_url' => $baseUrl,
            'has_api_key' => $apiKey !== '',
            'endpoint_check' => $endpointCheck,
            'draft_check' => $draftCheck,
            'reviewer_check' => $reviewerCheck,
            'summary' => $summary,
        ];
    }

    /**
     * @return array{ok: bool, model: string, http_status: int, latency_ms: float, reply: string, message: string}
     */
    protected function probeSingleModel(string $baseUrl, string $apiKey, string $model): array
    {
        if ($model === '') {
            return [
                'ok' => false,
                'model' => '(Chưa chọn)',
                'http_status' => 0,
                'latency_ms' => 0.0,
                'reply' => '',
                'message' => 'Chưa nhập tên mô hình AI.',
            ];
        }

        $t0 = microtime(true);
        try {
            $http = Http::timeout(15)->acceptJson();
            if ($apiKey !== '') {
                $http = $http->withToken($apiKey);
            }

            $response = $http->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => 'Trả lời đúng 1 chữ: OK'],
                ],
                'temperature' => 0,
                'max_tokens' => 16,
                'stream' => false,
            ]);

            $latency = round((microtime(true) - $t0) * 1000, 1);
            $status = $response->status();

            if (! $response->successful()) {
                $errCode = trim((string) ($response->json('error.code') ?? $response->json('error.type') ?? ''));
                $errMsg = trim((string) ($response->json('error.message') ?? $response->json('message') ?? ''));
                $rawBody = $errMsg === '' ? trim(mb_substr($response->body(), 0, 120, 'UTF-8')) : $errMsg;
                $detail = 'Lỗi HTTP '.$status.($errCode !== '' ? " ({$errCode})" : '').($rawBody !== '' ? ': '.$rawBody : '');

                return [
                    'ok' => false,
                    'model' => $model,
                    'http_status' => $status,
                    'latency_ms' => $latency,
                    'reply' => '',
                    'message' => $detail,
                ];
            }

            $json = $response->json();
            $reply = $this->extractText(is_array($json) ? $json : []);

            return [
                'ok' => true,
                'model' => $model,
                'http_status' => $status,
                'latency_ms' => $latency,
                'reply' => mb_substr($reply !== '' ? $reply : 'OK', 0, 80, 'UTF-8'),
                'message' => "Hoạt động tốt ({$latency}ms) — Phản hồi: \"".mb_substr($reply !== '' ? $reply : 'OK', 0, 40, 'UTF-8').'"',
            ];
        } catch (Throwable $e) {
            $latency = round((microtime(true) - $t0) * 1000, 1);

            return [
                'ok' => false,
                'model' => $model,
                'http_status' => 0,
                'latency_ms' => $latency,
                'reply' => '',
                'message' => 'Lỗi kết nối / Timeout ('.$latency.'ms): '.mb_substr($e->getMessage(), 0, 110, 'UTF-8'),
            ];
        }
    }

    protected function recordFailure(): void
    {
        $failures = (int) Cache::get(self::CIRCUIT_FAILURES_KEY, 0) + 1;
        Cache::put(self::CIRCUIT_FAILURES_KEY, $failures, 300);

        if ($failures >= self::CIRCUIT_THRESHOLD) {
            Cache::put(self::CIRCUIT_OPEN_UNTIL_KEY, time() + self::CIRCUIT_COOLDOWN_SECONDS, self::CIRCUIT_COOLDOWN_SECONDS + 10);
        }
    }

    protected function resetCircuit(): void
    {
        Cache::forget(self::CIRCUIT_FAILURES_KEY);
        Cache::forget(self::CIRCUIT_OPEN_UNTIL_KEY);
    }

    /**
     * @return array{ok: bool, text: string, model: string, status: string, prompt_tokens: int, completion_tokens: int}
     */
    protected function failChat(string $model, string $status): array
    {
        return [
            'ok' => false,
            'text' => '',
            'model' => $model,
            'status' => $status,
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
        ];
    }

    protected function normalizeBaseUrl(string $rawUrl): string
    {
        $url = rtrim(trim($rawUrl), '/');
        if ($url === '') {
            return '';
        }

        if (! str_ends_with($url, '/v1')) {
            $url .= '/v1';
        }

        return $url;
    }

    /**
     * @param  array<string, mixed>  $json
     */
    protected function extractText(array $json): string
    {
        if (isset($json['choices'][0]['message']['content']) && is_string($json['choices'][0]['message']['content'])) {
            return trim($json['choices'][0]['message']['content']);
        }

        if (isset($json['output_text']) && is_string($json['output_text'])) {
            return trim($json['output_text']);
        }

        return '';
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function decodeJsonBlock(string $raw): ?array
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/```(?:json)?\s*(\{.*\}|\[.*\])\s*```/s', $trimmed, $matches) === 1) {
            $trimmed = trim($matches[1]);
        }

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $startObj = strpos($trimmed, '{');
        $endObj = strrpos($trimmed, '}');
        if ($startObj !== false && $endObj !== false && $endObj > $startObj) {
            $candidate = substr($trimmed, $startObj, $endObj - $startObj + 1);
            $decoded = json_decode($candidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
