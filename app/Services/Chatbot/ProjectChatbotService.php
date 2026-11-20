<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Models\AiChatbotLog;
use App\Models\AiKnowledgeEntry;
use App\Models\AiKnowledgeGap;
use App\Models\AiUsageLog;
use App\Services\AiQualityAndCostObservatoryService;
use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\CustomerMemoryService;
use App\Services\Intelligence\SecurityPolicyGate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Mây Hẹn Intelligence Engine — Central Brain Orchestrator.
 * Điều phối quy trình trả lời đa tầng (10 bước):
 * Governance Kill-Switch -> Input Risk Classification -> Intent & Scoped Memory ->
 * Retrieval & Evidence -> Sales Draft Agent -> Critic/Reviewer Agent (có giới hạn vòng lặp) ->
 * Deterministic Security & Policy Gate -> PII-Masked Logging & Memory Update.
 */
class ProjectChatbotService
{
    public function __construct(
        protected ChatbotSecurityGuard $securityGuard,
        protected SecurityPolicyGate $policyGate,
        protected ChatbotKnowledgeService $knowledgeService,
        protected ChatbotProviderClient $providerClient,
        protected AiGovernanceService $governance,
        protected CustomerMemoryService $customerMemory
    ) {}

    /**
     * @return array{
     *     log_id: int,
     *     reply: string,
     *     intent: string,
     *     mode: string,
     *     verified_source: bool,
     *     templates: array<int, array<string, mixed>>,
     *     docs: array<int, array<string, mixed>>,
     *     plans: array<int, array<string, mixed>>,
     *     can_train: bool,
     *     allow_feedback: bool
     * }
     */
    public function ask(
        string $rawMessage,
        ?int $userId,
        string $sessionId,
        ?string $ipAddress
    ): array {
        $startedAt = microtime(true);
        // Đánh dấu nhẹ (0 DB query) có khách thật đang chat để Learning Runner chủ động nhường CPU/PHP-FPM
        Cache::put('mayhen_client_chat_active_at', time(), 5);
        $sessionHash = hash('sha256', $sessionId !== '' ? $sessionId : 'guest');
        $ipHash = $ipAddress ? hash('sha256', $ipAddress) : null;
        $policy = $this->governance->getPolicy();

        // Bước 1 & 2: Input Risk Classification & Deterministic Security Gate
        $prelimNorm = $this->knowledgeService->normalizeText($rawMessage);
        $inputCheck = $this->policyGate->inspectAndClassifyInput($rawMessage);
        if ($inputCheck['blocked']) {
            $safeRefusal = (string) $inputCheck['safe_refusal'];

            $log = $this->storeLog([
                'user_id' => $userId,
                'session_hash' => $sessionHash,
                'ip_hash' => $ipHash,
                'user_message' => $this->policyGate->maskPiiForStorage($inputCheck['clean_text'] !== '' ? $inputCheck['clean_text'] : '[Blocked payload]'),
                'bot_reply' => $safeRefusal,
                'intent' => 'blocked_security',
                'risk_level' => AiChatbotLog::RISK_BLOCKED,
                'response_mode' => AiChatbotLog::MODE_BLOCKED_SECURITY,
                'review_status' => 'blocked_input_gate',
                'security_flags' => $inputCheck['flags'],
                'trace_metadata' => [
                    'provider' => $this->providerClient->providerName(),
                    'policy_decisions' => $inputCheck['flags'],
                ],
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
                'allow_feedback' => false,
            ];
        }

        $cleanMessage = $inputCheck['clean_text'];

        // Bước 3 & 4: Central Brain Retrieval + Authorized Scoped Memory + Recent Session Turns
        $retrieval = $this->knowledgeService->retrieve($cleanMessage);
        $knowledge = $retrieval['knowledge'];
        $templates = $retrieval['templates'];
        $docs = $retrieval['docs'];
        $plans = $retrieval['plans'];
        $intent = $retrieval['intent'];
        $evidenceRefs = $retrieval['evidence_refs'] ?? [];

        // Tinh chỉnh lại risk_level dựa theo intent thực tế sau khi phân tích
        $riskCheck = $this->policyGate->inspectAndClassifyInput($cleanMessage, $intent);
        $riskLevel = $riskCheck['risk_level'];

        $scopedMemory = $this->customerMemory->recall($userId, $sessionHash);
        $recentHistory = $this->loadSanitizedRecentHistory($sessionHash);

        // Ngữ cảnh xác thực truyền cho AI (nếu câu chào hỏi/hỏi mở thì nạp thêm tóm tắt danh mục cốt lõi đã xác thực)
        $verifiedContextText = $retrieval['verified_context_text'];
        if ($verifiedContextText === '' || in_array($intent, ['greeting', 'thanks', 'general'], true)) {
            $coreSummary = $this->knowledgeService->buildCoreCatalogSummary();
            $verifiedContextText = $verifiedContextText !== ''
                ? $verifiedContextText."\n\n".$coreSummary
                : $coreSummary;
        }

        $reply = '';
        $mode = AiChatbotLog::MODE_FALLBACK_GROUNDED;
        $reviewStatus = 'skipped_simple';
        $providerStatus = 'not_called';
        $outputFlags = [];
        $totalPromptTokens = 0;
        $totalCompletionTokens = 0;
        $draftModelUsed = $policy['draft_model'];
        $reviewerModelUsed = null;
        $reviewScore = null;
        $policyDecisions = [];

        // Kiểm tra Governance Kill Switch & Daily Token Budget
        if ($policy['kill_switch']) {
            $providerStatus = 'kill_switch_active';
            $policyDecisions[] = 'kill_switch_enforced';
            $reviewStatus = 'kill_switch_fallback';
            $reply = $this->buildConciseSalesFallback($knowledge, $templates, $docs, $plans, $intent, $retrieval['has_context']);
            $mode = $retrieval['has_context'] ? AiChatbotLog::MODE_FALLBACK_GROUNDED : AiChatbotLog::MODE_NO_CONTEXT_FALLBACK;
        } elseif (! $this->governance->hasRemainingDailyTokenBudget()) {
            $providerStatus = 'daily_budget_exceeded';
            $policyDecisions[] = 'token_budget_enforced';
            $reviewStatus = 'budget_fallback';
            $reply = $this->buildConciseSalesFallback($knowledge, $templates, $docs, $plans, $intent, $retrieval['has_context']);
            $mode = $retrieval['has_context'] ? AiChatbotLog::MODE_FALLBACK_GROUNDED : AiChatbotLog::MODE_NO_CONTEXT_FALLBACK;
        } else {
            // Bước 5 & 6: Reasoning & Sales Draft Agent (MỌI câu hợp lệ đều đi qua AI để trả lời ngắn gọn, tự nhiên, hướng bán dịch vụ)
            $draftResult = $this->providerClient->synthesize(
                sanitizedUserMessage: $cleanMessage,
                verifiedContext: $verifiedContextText,
                recentHistory: $recentHistory,
                customerMemory: $scopedMemory,
                intent: $intent
            );

            $providerStatus = $draftResult['status'];
            $draftModelUsed = $draftResult['model'] ?: $draftModelUsed;
            $totalPromptTokens += $draftResult['prompt_tokens'];
            $totalCompletionTokens += $draftResult['completion_tokens'];

            if ($draftResult['ok']) {
                $candidateReply = $draftResult['text'];
                $needsCritic = $policy['enable_reviewer_for_complex']
                    && in_array($riskLevel, [AiChatbotLog::RISK_COMPLEX, AiChatbotLog::RISK_HIGH], true)
                    && $policy['max_review_rounds'] > 0;

                // Bước 7: Critic & Verification Agent (Reviewer AI mạnh nhất cho luồng phức tạp / rủi ro cao)
                if ($needsCritic) {
                    $review = $this->providerClient->reviewDraftWithCritic($cleanMessage, $verifiedContextText, $candidateReply);
                    $reviewerModelUsed = $review['model'] ?: $policy['reviewer_model'];
                    $totalPromptTokens += $review['prompt_tokens'];
                    $totalCompletionTokens += $review['completion_tokens'];

                    if (! $review['ok']) {
                        $reviewStatus = 'reviewer_unavailable';
                        $policyDecisions[] = 'reviewer_unavailable';
                        // Đối với thao tác rủi ro cao, KHÔNG tự hạ chuẩn kiểm duyệt -> dùng fallback có bằng chứng
                        if ($riskLevel === AiChatbotLog::RISK_HIGH) {
                            $candidateReply = '';
                            $providerStatus = 'high_risk_reviewer_unavailable';
                        }
                    } else {
                        $reviewScore = $review['grounded_score'];
                        if ($review['verdict'] === 'approve') {
                            $reviewStatus = 'approved';
                            $mode = AiChatbotLog::MODE_LLM_REVIEWED;
                        } elseif ($review['verdict'] === 'revise') {
                            $policyDecisions[] = 'critic_requested_revision';
                            $revised = $this->providerClient->reviseDraftWithFeedback(
                                $cleanMessage,
                                $verifiedContextText,
                                $candidateReply,
                                $review['revision_guidance'] ?: implode('; ', $review['issues'])
                            );
                            $totalPromptTokens += $revised['prompt_tokens'];
                            $totalCompletionTokens += $revised['completion_tokens'];

                            if ($revised['ok']) {
                                $candidateReply = $revised['text'];
                                $reviewStatus = 'revised';
                                $mode = AiChatbotLog::MODE_LLM_REVIEWED;
                            } else {
                                $candidateReply = '';
                                $reviewStatus = 'rejected_fallback';
                            }
                        } else {
                            // Reviewer từ chối (bịa đặt / rủi ro / thiếu bằng chứng)
                            $policyDecisions[] = 'critic_rejected_draft';
                            $reviewStatus = 'rejected_fallback';
                            $candidateReply = '';
                            $providerStatus = 'critic_rejected';
                        }
                    }
                } else {
                    $mode = AiChatbotLog::MODE_LLM_GROUNDED;
                }

                // Bước 8: Deterministic Security & Policy Output Gate (Bắt buộc bằng code, không AI nào vượt qua được)
                if ($candidateReply !== '') {
                    $outputCheck = $this->policyGate->inspectFinalOutput($candidateReply, $verifiedContextText, $knowledge);

                    // Chống sao chép nguyên văn bài training dài -> yêu cầu tóm gọn lại 1 lần
                    if ($outputCheck['safe'] && $outputCheck['verbatim_dump']) {
                        $policyDecisions[] = 'verbatim_dump_prevented';
                        $shortened = $this->providerClient->reviseDraftWithFeedback(
                            $cleanMessage,
                            $verifiedContextText,
                            $outputCheck['clean_text'],
                            'Câu trả lời đang giống nguyên văn tài liệu nội bộ quá dài. Hãy diễn đạt lại cực kỳ tự nhiên và súc tích trong đúng 2-3 câu hướng khách trải nghiệm dịch vụ.'
                        );
                        $totalPromptTokens += $shortened['prompt_tokens'];
                        $totalCompletionTokens += $shortened['completion_tokens'];
                        if ($shortened['ok']) {
                            $reCheck = $this->policyGate->inspectFinalOutput($shortened['text'], $verifiedContextText, $knowledge);
                            if ($reCheck['safe'] && ! $reCheck['verbatim_dump']) {
                                $outputCheck = $reCheck;
                            } else {
                                $outputCheck['clean_text'] = $this->summarizeTextSafely($outputCheck['clean_text'], 260);
                            }
                        } else {
                            $outputCheck['clean_text'] = $this->summarizeTextSafely($outputCheck['clean_text'], 260);
                        }
                    }

                    if ($outputCheck['safe']) {
                        $reply = $outputCheck['clean_text'];
                        if ($mode !== AiChatbotLog::MODE_LLM_REVIEWED) {
                            $mode = AiChatbotLog::MODE_LLM_GROUNDED;
                        }
                    } else {
                        $outputFlags = $outputCheck['flags'];
                        $policyDecisions = array_merge($policyDecisions, $outputFlags);
                        $providerStatus = 'output_rejected_policy_gate';
                        $reviewStatus = 'rejected_fallback';
                        $reply = $this->buildConciseSalesFallback($knowledge, $templates, $docs, $plans, $intent, $retrieval['has_context']);
                        $mode = $retrieval['has_context'] ? AiChatbotLog::MODE_FALLBACK_GROUNDED : AiChatbotLog::MODE_NO_CONTEXT_FALLBACK;
                    }
                } else {
                    $reply = $this->buildConciseSalesFallback($knowledge, $templates, $docs, $plans, $intent, $retrieval['has_context']);
                    $mode = $retrieval['has_context'] ? AiChatbotLog::MODE_FALLBACK_GROUNDED : AiChatbotLog::MODE_NO_CONTEXT_FALLBACK;
                }
            } else {
                // Provider lỗi hoặc tắt -> Safe Concise Fallback 100% từ dữ liệu đã xác thực
                $reply = $this->buildConciseSalesFallback($knowledge, $templates, $docs, $plans, $intent, $retrieval['has_context']);
                $mode = $retrieval['has_context'] ? AiChatbotLog::MODE_FALLBACK_GROUNDED : AiChatbotLog::MODE_NO_CONTEXT_FALLBACK;
            }
        }

        // Ghi nhận lượt sử dụng cho các mục tri thức đã khớp
        $matchedIds = array_values(array_map(fn (array $k) => (int) $k['id'], $knowledge));
        if ($matchedIds !== []) {
            AiKnowledgeEntry::query()->whereIn('id', $matchedIds)->increment('usage_count');
        }

        $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);
        $costVnd = AiQualityAndCostObservatoryService::calculateTokenCost($draftModelUsed ?: 'gemini-1.5-flash', $totalPromptTokens, $totalCompletionTokens);

        $this->logAiObservatoryUsage(
            $userId,
            $mode,
            $providerStatus,
            $draftModelUsed,
            $totalPromptTokens,
            $totalCompletionTokens,
            $costVnd,
            $latencyMs,
            $cleanMessage,
            $reply
        );

        // Bước 9: Privacy-Controlled Logging (Che giấu PII + lưu metadata truy vết nội bộ)
        $log = $this->storeLog([
            'user_id' => $userId,
            'session_hash' => $sessionHash,
            'ip_hash' => $ipHash,
            'user_message' => $this->policyGate->maskPiiForStorage($cleanMessage),
            'bot_reply' => $reply,
            'intent' => $intent,
            'risk_level' => $riskLevel,
            'response_mode' => $mode,
            'review_status' => $reviewStatus,
            'matched_knowledge_ids' => $matchedIds,
            'matched_template_slugs' => array_values(array_map(fn (array $t) => (string) $t['slug'], $templates)),
            'matched_doc_urls' => array_values(array_map(fn (array $d) => (string) $d['url'], $docs)),
            'security_flags' => $outputFlags !== [] ? $outputFlags : null,
            'trace_metadata' => [
                'provider' => $this->providerClient->providerName(),
                'draft_model' => $draftModelUsed,
                'reviewer_model' => $reviewerModelUsed,
                'review_score' => $reviewScore,
                'evidence_refs' => $evidenceRefs,
                'policy_decisions' => array_values(array_unique($policyDecisions)),
                'prompt_tokens' => $totalPromptTokens,
                'completion_tokens' => $totalCompletionTokens,
                'cost_vnd' => $costVnd,
            ],
            'provider_status' => $providerStatus,
            'latency_ms' => $latencyMs,
        ]);

        // Bước 10: Cập nhật Scoped Customer Memory (chỉ tín hiệu enum hợp lệ, 0 PII)
        $this->customerMemory->recordSafeSignals($userId, $sessionHash, $cleanMessage, $intent);

        // Nếu câu hỏi hợp lệ thiếu bằng chứng xác thực -> ghi nhận Knowledge Gap có chống trùng thay vì bịa đặt
        if ($mode === AiChatbotLog::MODE_NO_CONTEXT_FALLBACK && ! in_array($intent, ['greeting', 'thanks'], true)) {
            AiKnowledgeGap::recordGap(
                $this->policyGate->maskPiiForStorage($cleanMessage),
                $intent,
                3,
                ['log_id' => (int) $log->id]
            );
        }

        // Tuyệt đối KHÔNG trả trace_metadata / suy luận nội bộ ra ngoài trình duyệt khách hàng
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
            'can_train' => false,
            'allow_feedback' => true,
        ];
    }

    /**
     * Xây dựng phản hồi dự phòng NGẮN GỌN & HƯỚNG DỊCH VỤ khi LLM không khả dụng / bị Reviewer từ chối.
     * TUYỆT ĐỐI KHÔNG in nguyên văn tài liệu nội bộ dài.
     *
     * @param  array<int, array<string, mixed>>  $knowledge
     * @param  array<int, array<string, mixed>>  $templates
     * @param  array<int, array<string, mixed>>  $docs
     * @param  array<int, array<string, mixed>>  $plans
     */
    protected function buildConciseSalesFallback(
        array $knowledge,
        array $templates,
        array $docs,
        array $plans,
        string $intent,
        bool $hasContext
    ): string {
        if ($intent === 'greeting') {
            return 'Xin chào bạn! Mình là Trợ lý AI của Mây Hẹn, có thể gợi ý mẫu thiệp cưới phù hợp phong cách và hỗ trợ bạn tạo thiệp online nhanh chóng. Bạn đang thích phong cách sang trọng, tối giản hay truyền thống để mình chọn giúp nhé?';
        }

        if ($intent === 'thanks') {
            return 'Rất vui được đồng hành cùng bạn! Khi cần xem thêm mẫu thiệp hoặc mở nhanh [Studio Tạo Thiệp](/tao-thiep), bạn cứ nhắn mình nhé.';
        }

        if (! $hasContext) {
            return 'Hiện mình chưa có thông tin xác thực chính xác cho câu hỏi này trên hệ thống Mây Hẹn. Bạn có thể xem trực tiếp [Kho Mẫu Thiệp](/mau-thiep), [Bảng Giá](/bang-gia) hoặc tra cứu nhanh tại [Trung tâm Hướng dẫn](/docs) nhé.';
        }

        if ($intent === 'pricing' && $plans !== []) {
            $summary = implode('; ', array_map(
                fn (array $p) => "Gói {$p['name']} ({$p['formatted_price']})",
                array_slice($plans, 0, 3)
            ));

            return "Mây Hẹn hiện có các gói dịch vụ chính thức: {$summary}. Bạn có thể tạo thử thiệp miễn phí ngay tại [Studio Tạo Thiệp](/tao-thiep) hoặc xem chi tiết quyền lợi tại [Bảng Giá](/bang-gia) nhé.";
        }

        if ($templates !== [] && ($intent === 'templates' || $knowledge === [])) {
            $names = implode(', ', array_map(fn (array $t) => $t['name'], array_slice($templates, 0, 3)));

            return "Mây Hẹn có các mẫu thiệp rất phù hợp với nhu cầu của bạn như: {$names}. Bạn bấm Xem thử hoặc Tạo thiệp ngay trên các thẻ bên dưới để trải nghiệm miễn phí nhé!";
        }

        if ($knowledge !== []) {
            // Tóm gọn tối đa 2 câu, tuyệt đối không đổ nguyên bài training dài
            $brief = $this->summarizeTextSafely((string) $knowledge[0]['answer'], 240);

            return "{$brief} Bạn có thể trải nghiệm ngay tại [Studio Tạo Thiệp](/tao-thiep) hoặc xem thêm hướng dẫn chi tiết bên dưới nhé.";
        }

        if ($docs !== []) {
            return "Mình đã tìm thấy bài hướng dẫn chính thức \"{$docs[0]['title']}\" đúng với thắc mắc của bạn. Bạn mở thẻ hướng dẫn bên dưới để thao tác chi tiết từng bước nhé.";
        }

        return 'Bạn có thể khám phá [Kho Mẫu Thiệp](/mau-thiep) hoặc tạo thử thiệp cưới miễn phí ngay tại [Studio Tạo Thiệp](/tao-thiep) để trải nghiệm thực tế nhé.';
    }

    /**
     * Đọc tối đa 3 lượt hội thoại hợp lệ gần nhất trong đúng phiên hiện tại (cô lập session_hash).
     *
     * @return array<int, array{user: string, bot: string}>
     */
    protected function loadSanitizedRecentHistory(string $sessionHash): array
    {
        if (! Schema::hasTable('ai_chatbot_logs')) {
            return [];
        }

        $rows = AiChatbotLog::query()
            ->where('session_hash', $sessionHash)
            ->where('response_mode', '!=', AiChatbotLog::MODE_BLOCKED_SECURITY)
            ->latest('id')
            ->limit(3)
            ->get(['user_message', 'bot_reply'])
            ->reverse();

        $history = [];
        foreach ($rows as $row) {
            $history[] = [
                'user' => $this->securityGuard->sanitizePlainText((string) $row->user_message, 240),
                'bot' => $this->securityGuard->sanitizePlainText((string) $row->bot_reply, 280),
            ];
        }

        return $history;
    }

    protected function summarizeTextSafely(string $text, int $maxChars = 240): string
    {
        $clean = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if (mb_strlen($clean, 'UTF-8') <= $maxChars) {
            return $clean;
        }

        $sentences = preg_split('/(?<=[.!?])\s+/u', $clean) ?: [$clean];
        $built = '';
        foreach ($sentences as $sentence) {
            if ($built !== '' && mb_strlen($built.' '.$sentence, 'UTF-8') > $maxChars) {
                break;
            }
            $built = $built === '' ? $sentence : $built.' '.$sentence;
        }

        return $built !== '' ? $built : Str::limit($clean, $maxChars);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function storeLog(array $attributes): AiChatbotLog
    {
        // Phòng vệ tương thích nếu môi trường chưa chạy migration mở rộng
        foreach (['risk_level', 'review_status', 'trace_metadata', 'feedback_valid'] as $col) {
            if (! Schema::hasColumn('ai_chatbot_logs', $col)) {
                unset($attributes[$col]);
            }
        }

        return AiChatbotLog::query()->create($attributes);
    }

    protected function logAiObservatoryUsage(
        ?int $userId,
        string $mode,
        string $providerStatus,
        string $modelUsed,
        int $promptTokens,
        int $completionTokens,
        float $costVnd,
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
            'provider' => Str::limit($modelUsed !== '' ? $modelUsed : 'grounded-local', 50, ''),
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'cost_vnd' => $costVnd,
            'latency_ms' => $latencyMs,
            'status' => in_array($mode, [AiChatbotLog::MODE_LLM_GROUNDED, AiChatbotLog::MODE_LLM_REVIEWED], true) ? 'success' : 'fallback_applied',
            'safety_rating' => 'verified_zero_trust',
            'prompt_preview' => mb_substr($this->policyGate->maskPiiForStorage($promptPreview), 0, 180, 'UTF-8'),
            'output_preview' => mb_substr($this->policyGate->maskPiiForStorage($outputPreview), 0, 180, 'UTF-8'),
            'error_message' => $providerStatus === 'ok' ? null : Str::limit($providerStatus, 240, ''),
        ]);
    }
}
