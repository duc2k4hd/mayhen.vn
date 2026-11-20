<?php

declare(strict_types=1);

namespace App\Services\Intelligence\Contracts;

interface LlmProviderInterface
{
    /**
     * Tên định danh nhà cung cấp mô hình hiện tại (ví dụ: ninerouter, openai, anthropic, gemini).
     */
    public function providerName(): string;

    /**
     * Kiểm tra nhà cung cấp đã được cấu hình sẵn sàng và không bị mạch ngắt (circuit breaker) chặn.
     */
    public function isAvailable(): bool;

    /**
     * Gọi hoàn tất hội thoại (Chat Completions) trả về văn bản thuần.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{ok: bool, text: string, model: string, status: string, prompt_tokens: int, completion_tokens: int}
     */
    public function completeChat(
        array $messages,
        ?string $model = null,
        float $temperature = 0.25,
        int $maxTokens = 400,
        ?int $timeoutSeconds = null
    ): array;

    /**
     * Gọi hoàn tất hội thoại có cấu trúc JSON (phục vụ Critic/Reviewer Agent và Multi-Agent Learning).
     *
     * @return array{ok: bool, data: array<string, mixed>, raw: string, model: string, status: string, prompt_tokens: int, completion_tokens: int}
     */
    public function completeJson(
        string $systemPrompt,
        string $userPrompt,
        ?string $model = null,
        float $temperature = 0.2,
        int $maxTokens = 1600,
        ?int $timeoutSeconds = null
    ): array;

    /**
     * Lấy danh sách model khả dụng từ nhà cung cấp (dùng cho Admin Governance chọn Draft/Reviewer model).
     *
     * @return array{ok: bool, models: list<string>, status: string}
     */
    public function listModels(): array;
}
