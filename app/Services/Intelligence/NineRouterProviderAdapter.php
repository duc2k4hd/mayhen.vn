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
        $baseUrl = $this->resolveBaseUrl();
        $resolvedModel = trim((string) ($model ?: $this->governance->getPolicy()['draft_model'] ?: config('services.ninerouter.model', '')));
        $apiKey = $this->resolveApiKey();
        $timeout = max(5, min(90, (int) ($timeoutSeconds ?? config('services.ninerouter.timeout', 20))));

        if (! (bool) config('services.ninerouter.enabled', false) || $baseUrl === '' || $resolvedModel === '') {
            return $this->failChat($resolvedModel, 'disabled_or_unconfigured');
        }

        if (! $this->isAvailable()) {
            return $this->failChat($resolvedModel, 'circuit_open');
        }

        try {
            $http = Http::timeout($timeout)->acceptJson();
            if ($apiKey !== '') {
                $http = $http->withToken($apiKey);
            }

            $response = $http->post($baseUrl.'/chat/completions', [
                'model' => $resolvedModel,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'stream' => false,
            ]);

            if (! $response->successful()) {
                if ($response->status() >= 500) {
                    $this->recordFailure();
                }

                $errorCode = (string) ($response->json('error.code') ?? $response->json('error.type') ?? 'error');
                $cleanCode = substr(preg_replace('/[^a-zA-Z0-9_]/', '', $errorCode) ?? 'err', 0, 32);

                return $this->failChat($resolvedModel, 'http_'.$response->status().'_'.$cleanCode);
            }

            $this->resetCircuit();

            $json = $response->json();
            $text = $this->extractText(is_array($json) ? $json : []);
            $promptTokens = (int) ($json['usage']['prompt_tokens'] ?? $json['usage']['input_tokens'] ?? 0);
            $completionTokens = (int) ($json['usage']['completion_tokens'] ?? $json['usage']['output_tokens'] ?? 0);

            $this->governance->recordDailyTokens($promptTokens + $completionTokens);

            if ($text === '') {
                return [
                    'ok' => false,
                    'text' => '',
                    'model' => $resolvedModel,
                    'status' => 'empty_response',
                    'prompt_tokens' => $promptTokens,
                    'completion_tokens' => $completionTokens,
                ];
            }

            return [
                'ok' => true,
                'text' => $text,
                'model' => $resolvedModel,
                'status' => 'ok',
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
            ];
        } catch (Throwable $e) {
            $errMsg = strtolower($e->getMessage());
            $isTimeout = str_contains($errMsg, 'timed out') || str_contains($errMsg, 'curl error 28') || str_contains($errMsg, 'timeout');
            if (! $isTimeout) {
                $this->recordFailure();
            }

            return $this->failChat($resolvedModel, $isTimeout ? 'request_timeout' : 'connection_error');
        }
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
