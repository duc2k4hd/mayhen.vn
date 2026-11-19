<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Models\AiChatbotLog;
use App\Models\AiKnowledgeEntry;
use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Schema;

class ProjectChatbotService
{
    public function __construct(
        protected ChatbotSecurityGuard $securityGuard,
        protected ChatbotKnowledgeService $knowledgeService,
        protected ChatbotProviderClient $providerClient
    ) {}

    /**
     * Xử lý câu hỏi từ khách hàng theo kiến trúc Zero-Trust Grounded AI.
     *
     * @return array{
     *     log_id: int,
     *     reply: string,
     *     intent: string,
     *     mode: string,
     *     verified_source: bool,
     *     templates: array<int, array<string, mixed>>,
     *     docs: array<int, array<string, mixed>>,
     *     plans: array<int, array<string, mixed>>,
     *     can_train: bool
     * }
     */
    public function ask(
        string $rawMessage,
        ?int $userId,
        string $sessionId,
        ?string $ipAddress
    ): array {
        $startedAt = microtime(true);
        $sessionHash = hash('sha256', $sessionId !== '' ? $sessionId : 'guest');
        $ipHash = $ipAddress ? hash('sha256', $ipAddress) : null;

        // 1. Tường lửa Zero-Trust kiểm tra đầu vào (không bao giờ tin prompt từ khách hàng)
        $inspection = $this->securityGuard->inspectUserMessage($rawMessage);
        if ($inspection['blocked']) {
            $safeRefusal = 'Trợ lý Mây Hẹn chỉ hỗ trợ tư vấn mẫu thiệp cưới, bảng giá và hướng dẫn sử dụng trên nền tảng Mây Hẹn. Yêu cầu vừa rồi chứa mẫu ký tự không hợp lệ nên đã được chặn để bảo vệ an toàn hệ thống.';

            $log = AiChatbotLog::query()->create([
                'user_id' => $userId,
                'session_hash' => $sessionHash,
                'ip_hash' => $ipHash,
                'user_message' => $inspection['clean_text'] !== '' ? $inspection['clean_text'] : '[Blocked payload]',
                'bot_reply' => $safeRefusal,
                'intent' => 'blocked_security',
                'response_mode' => AiChatbotLog::MODE_BLOCKED_SECURITY,
                'security_flags' => $inspection['flags'],
                'provider_status' => 'blocked_before_llm',
                'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);

            return [
                'log_id' => (int) $log->id,
                'reply' => $safeRefusal,
                'intent' => 'blocked_security',
                'mode' => AiChatbotLog::MODE_BLOCKED_SECURITY,
                'verified_source' => true,
                'templates' => [],
                'docs' => [
                    [
                        'title' => 'Trung tâm Hướng dẫn Khách hàng Mây Hẹn',
                        'category' => 'Tài liệu hướng dẫn',
                        'summary' => 'Xem hướng dẫn tạo thiệp, quản lý khách mời, RSVP và xuất bản thiệp cưới.',
                        'url' => '/docs',
                    ],
                ],
                'plans' => [],
                'can_train' => false,
            ];
        }

        $cleanMessage = $inspection['clean_text'];

        // 2. Truy xuất dữ liệu ĐÃ XÁC THỰC từ dự án (Knowledge đã duyệt, Mẫu thiệp, Client Docs, Bảng giá)
        $retrieval = $this->knowledgeService->retrieve($cleanMessage);
        $knowledge = $retrieval['knowledge'];
        $templates = $retrieval['templates'];
        $docs = $retrieval['docs'];
        $plans = $retrieval['plans'];
        $intent = $retrieval['intent'];

        $reply = '';
        $mode = AiChatbotLog::MODE_FALLBACK_GROUNDED;
        $providerStatus = 'not_called';
        $outputFlags = [];

        // 3. Chào hỏi / Cảm ơn tự nhiên từ dữ liệu xác thực của hệ thống
        if ($intent === 'greeting') {
            $mode = AiChatbotLog::MODE_FALLBACK_GROUNDED;
            $reply = 'Xin chào bạn! Mình là Trợ lý AI của Mây Hẹn. Bạn đang muốn tìm mẫu thiệp cưới theo phong cách nào (sang trọng, hiện đại, hoa lá, truyền thống, miễn phí) hay cần hướng dẫn sử dụng Studio tạo thiệp & quản lý khách mời? Cứ nhắn nhu cầu để mình gợi ý ngay nhé!';
        } elseif ($intent === 'thanks') {
            $mode = AiChatbotLog::MODE_FALLBACK_GROUNDED;
            $reply = 'Rất vui được hỗ trợ bạn! Nếu cần tìm thêm mẫu thiệp cưới hoặc xem hướng dẫn chi tiết ở bất kỳ bước nào, bạn cứ nhắn lại cho mình bất cứ lúc nào nhé.';
        } elseif (! $retrieval['has_context']) {
            // Nếu KHÔNG có bất kỳ dữ liệu xác thực nào khớp -> từ chối bịa đặt, điều hướng về Docs & Kho mẫu
            $mode = AiChatbotLog::MODE_NO_CONTEXT_FALLBACK;
            $reply = 'Hiện tại mình chưa tìm thấy thông tin đã xác thực trong dữ liệu Mây Hẹn khớp chính xác với câu hỏi này. Để tránh trả lời sai lệch, bạn có thể xem nhanh Kho Mẫu Thiệp hoặc Trung tâm Hướng dẫn bên dưới, hoặc bấm "Góp ý dạy AI" để đội ngũ Mây Hẹn bổ sung câu trả lời chuẩn xác nhất nhé!';
        } else {
            // 4. Có dữ liệu xác thực -> gọi LLM tổng hợp nếu khả dụng, sau đó KIỂM DUYỆT ĐẦU RA ZERO-TRUST
            $llmResult = $this->providerClient->synthesize($cleanMessage, $retrieval['verified_context_text']);
            $providerStatus = $llmResult['status'];

            if ($llmResult['ok']) {
                $outputCheck = $this->securityGuard->inspectAiOutput($llmResult['text']);
                if ($outputCheck['safe']) {
                    $reply = $outputCheck['clean_text'];
                    $mode = AiChatbotLog::MODE_LLM_GROUNDED;
                } else {
                    $outputFlags = $outputCheck['flags'];
                    $providerStatus = 'output_rejected_zero_trust';
                    $reply = $this->buildDeterministicGroundedReply($knowledge, $templates, $docs, $plans, $intent);
                    $mode = AiChatbotLog::MODE_FALLBACK_GROUNDED;
                }
            } else {
                // Fallback 100% từ dữ liệu đã xác thực trong DB & Docs -> luôn thông minh, chính xác, không sập
                $reply = $this->buildDeterministicGroundedReply($knowledge, $templates, $docs, $plans, $intent);
                $mode = AiChatbotLog::MODE_FALLBACK_GROUNDED;
            }

            // Ghi nhận lượt sử dụng cho các mục tri thức đã khớp
            $matchedIds = array_values(array_map(fn (array $k) => (int) $k['id'], $knowledge));
            if ($matchedIds !== []) {
                AiKnowledgeEntry::query()->whereIn('id', $matchedIds)->increment('usage_count');
            }

            $this->logAiObservatoryUsage(
                $userId,
                $mode,
                $providerStatus,
                $llmResult['prompt_tokens'],
                $llmResult['completion_tokens'],
                (int) round((microtime(true) - $startedAt) * 1000),
                $cleanMessage,
                $reply
            );
        }

        $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

        $log = AiChatbotLog::query()->create([
            'user_id' => $userId,
            'session_hash' => $sessionHash,
            'ip_hash' => $ipHash,
            'user_message' => $cleanMessage,
            'bot_reply' => $reply,
            'intent' => $intent,
            'response_mode' => $mode,
            'matched_knowledge_ids' => array_values(array_map(fn (array $k) => (int) $k['id'], $knowledge)),
            'matched_template_slugs' => array_values(array_map(fn (array $t) => (string) $t['slug'], $templates)),
            'matched_doc_urls' => array_values(array_map(fn (array $d) => (string) $d['url'], $docs)),
            'security_flags' => $outputFlags !== [] ? $outputFlags : null,
            'provider_status' => $providerStatus,
            'latency_ms' => $latencyMs,
        ]);

        return [
            'log_id' => (int) $log->id,
            'reply' => $reply,
            'intent' => $intent,
            'mode' => $mode,
            'verified_source' => true,
            'templates' => array_values(array_map(fn (array $t) => [
                'slug' => $t['slug'],
                'name' => $t['name'],
                'category' => $t['category'],
                'tier' => $t['tier'],
                'thumbnail_url' => $t['thumbnail_url'],
                'preview_url' => $t['preview_url'],
                'editor_url' => $t['editor_url'],
            ], $templates)),
            'docs' => array_values(array_map(fn (array $d) => [
                'title' => $d['title'],
                'category' => $d['category'],
                'summary' => $d['summary'],
                'url' => $d['url'],
            ], $docs)),
            'plans' => $plans,
            'can_train' => true,
        ];
    }

    /**
     * Khách hàng gửi góp ý dạy thêm cho AI — BẮT BUỘC lưu ở trạng thái chờ Admin duyệt (status = pending, is_active = false).
     *
     * @return array{accepted: bool, message: string, entry_id: ?int}
     */
    public function submitCustomerTraining(
        string $rawQuestion,
        string $rawSuggestedAnswer,
        string $category,
        ?int $userId,
        string $sessionId
    ): array {
        $qCheck = $this->securityGuard->inspectUserMessage($rawQuestion, 190);
        $aCheck = $this->securityGuard->inspectUserMessage($rawSuggestedAnswer, 1000);

        $allFlags = array_values(array_unique(array_merge($qCheck['flags'], $aCheck['flags'])));
        if ($allFlags !== []) {
            return [
                'accepted' => false,
                'message' => 'Nội dung góp ý chứa ký tự hoặc cú pháp không an toàn nên hệ thống đã từ chối tiếp nhận.',
                'entry_id' => null,
            ];
        }

        if ($qCheck['clean_text'] === '' || $aCheck['clean_text'] === '') {
            return [
                'accepted' => false,
                'message' => 'Vui lòng nhập đầy đủ câu hỏi và nội dung góp ý rõ ràng.',
                'entry_id' => null,
            ];
        }

        $safeCategory = array_key_exists($category, AiKnowledgeEntry::CATEGORIES) ? $category : 'general';

        $entry = AiKnowledgeEntry::query()->create([
            'category' => $safeCategory,
            'title' => $qCheck['clean_text'],
            'keywords' => null,
            'answer' => $aCheck['clean_text'],
            'source' => AiKnowledgeEntry::SOURCE_CUSTOMER,
            'status' => AiKnowledgeEntry::STATUS_PENDING,
            'is_active' => false,
            'priority' => 5,
            'security_flags' => null,
            'submitted_by_user_id' => $userId,
            'submitted_session_hash' => hash('sha256', $sessionId !== '' ? $sessionId : 'guest'),
        ]);

        return [
            'accepted' => true,
            'message' => 'Cảm ơn bạn đã đóng góp! Đề xuất này đã được chuyển vào hàng đợi kiểm duyệt của Quản trị viên Mây Hẹn và chỉ được kích hoạt sau khi xác thực độ chính xác.',
            'entry_id' => (int) $entry->id,
        ];
    }

    /**
     * Xây dựng câu trả lời Tiếng Việt tự nhiên 100% từ dữ liệu đã xác thực trong hệ thống (không bịa đặt).
     *
     * @param  array<int, array<string, mixed>>  $knowledge
     * @param  array<int, array<string, mixed>>  $templates
     * @param  array<int, array<string, mixed>>  $docs
     * @param  array<int, array<string, mixed>>  $plans
     */
    protected function buildDeterministicGroundedReply(
        array $knowledge,
        array $templates,
        array $docs,
        array $plans,
        string $intent
    ): string {
        $parts = [];

        if ($knowledge !== []) {
            $parts[] = (string) $knowledge[0]['answer'];
        }

        if ($intent === 'pricing' && $plans !== []) {
            $planLines = array_map(
                fn (array $p) => "• Gói {$p['name']}: {$p['formatted_price']} ({$p['duration_days']} ngày) — {$p['description']}",
                $plans
            );
            $parts[] = "Dưới đây là bảng giá chính thức hiện hành trên Mây Hẹn:\n".implode("\n", $planLines);
        }

        if ($templates !== [] && ($intent === 'templates' || $knowledge === [])) {
            $names = implode(', ', array_map(fn (array $t) => "“{$t['name']}”", $templates));
            $parts[] = "Mình đã lọc sẵn các mẫu thiệp đang có trên Mây Hẹn phù hợp với nhu cầu của bạn: {$names}. Bạn có thể bấm Xem thử hoặc Tạo thiệp ngay trên từng thẻ bên dưới.";
        }

        if ($docs !== [] && ($intent === 'docs' || $parts === [])) {
            $docTitles = implode(', ', array_map(fn (array $d) => "“{$d['title']}”", $docs));
            $parts[] = "Để thao tác chuẩn xác từng bước, bạn có thể xem ngay tài liệu hướng dẫn chính thức của Mây Hẹn: {$docTitles} ở các thẻ bên dưới.";
        }

        return implode("\n\n", $parts);
    }

    protected function logAiObservatoryUsage(
        ?int $userId,
        string $mode,
        string $providerStatus,
        int $promptTokens,
        int $completionTokens,
        int $latencyMs,
        string $promptPreview,
        string $outputPreview
    ): void {
        if (! Schema::hasTable('ai_usage_logs')) {
            return;
        }

        AiUsageLog::query()->create([
            'user_id' => $userId,
            'feature_name' => 'project_chatbot',
            'provider' => (string) config('services.mayhen_ai.model', 'grounded-local'),
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'cost_vnd' => 0,
            'latency_ms' => $latencyMs,
            'status' => $mode === AiChatbotLog::MODE_LLM_GROUNDED ? 'success' : 'fallback_applied',
            'safety_rating' => 'verified_zero_trust',
            'prompt_preview' => mb_substr($promptPreview, 0, 180, 'UTF-8'),
            'output_preview' => mb_substr($outputPreview, 0, 180, 'UTF-8'),
            'error_message' => $providerStatus === 'ok' ? null : $providerStatus,
        ]);
    }
}
