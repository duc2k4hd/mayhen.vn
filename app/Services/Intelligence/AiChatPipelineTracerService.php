<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Models\AiChatbotLog;
use App\Models\AiChatTestScenario;
use App\Services\AiQualityAndCostObservatoryService;
use App\Services\Chatbot\ChatbotKnowledgeService;
use App\Services\Chatbot\ChatbotProviderClient;
use App\Services\Chatbot\ChatbotSecurityGuard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Phòng Kiểm Thử & Truy Vết Trực Tiếp 7 Bước (Admin Live AI Pipeline Tracer).
 * Chạy trực tiếp trên đúng các Service thật của hệ thống Mây Hẹn (0 giả lập ảo),
 * cô lập phiên sandbox để không làm bẩn AiChatbotLog / AiKnowledgeGap của khách hàng thật.
 */
class AiChatPipelineTracerService
{
    public function __construct(
        protected ChatbotSecurityGuard $securityGuard,
        protected SecurityPolicyGate $policyGate,
        protected ChatbotKnowledgeService $knowledgeService,
        protected ChatbotProviderClient $providerClient,
        protected AiGovernanceService $governance
    ) {}

    /**
     * Chạy thật toàn bộ quy trình 7 bước và trả về trace chi tiết kèm kết quả PASS/FAIL từng bước.
     *
     * @param  array<string, mixed>|null  $expectations
     * @return array<string, mixed>
     */
    public function runLiveTrace(
        string $rawMessage,
        int $adminUserId,
        string $sandboxSession = 'default',
        ?array $expectations = null,
        ?AiChatTestScenario $scenario = null
    ): array {
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        $totalStartedAt = microtime(true);
        $policy = $this->governance->getPolicy();
        $sessionKey = $this->historyCacheKey($adminUserId, $sandboxSession);
        $sessionHash = hash('sha256', 'admin-sandbox:'.$adminUserId.':'.$sandboxSession);

        $steps = [];
        $pipelineFailedStep = null;
        $haltedAtStep = null;
        $totalPromptTokens = 0;
        $totalCompletionTokens = 0;
        $draftModelUsed = (string) ($policy['draft_model'] ?? '');
        $reviewerModelUsed = null;
        $reviewScore = null;

        // =====================================================================
        // BƯỚC 1: TIẾP NHẬN & KIỂM TRA HỢP LỆ ĐẦU VÀO
        // =====================================================================
        $s1Start = microtime(true);
        $trimmedRaw = trim($rawMessage);
        $charLen = mb_strlen($trimmedRaw, 'UTF-8');
        $step1Valid = $charLen >= 2 && $charLen <= 600;

        $steps[1] = [
            'step' => 1,
            'code' => 'intake',
            'title' => 'Tiếp nhận & Chuẩn hóa',
            'component' => 'AiChatbotController::ask',
            'status' => $step1Valid ? 'passed' : 'failed',
            'duration_ms' => $this->elapsedMs($s1Start),
            'headline' => $step1Valid
                ? "Hợp lệ ({$charLen} ký tự) • Phiên sandbox cô lập"
                : ($charLen < 2 ? 'Tin nhắn quá ngắn (< 2 ký tự)' : 'Tin nhắn vượt quá giới hạn 600 ký tự'),
            'summary' => $step1Valid
                ? 'Đầu vào đạt chuẩn độ dài 2–600 ký tự, đã cấp mã phiên ẩn danh SHA-256 và chạy tách biệt hoàn toàn khỏi nhật ký khách hàng thật.'
                : 'Hệ thống từ chối ngay tại cổng tiếp nhận do độ dài tin nhắn không nằm trong khoảng cho phép (2–600 ký tự).',
            'error_reason' => $step1Valid ? null : 'Độ dài tin nhắn không hợp lệ (2–600 ký tự).',
            'metrics' => [
                ['label' => 'Độ dài', 'value' => $charLen.' ký tự', 'tone' => $step1Valid ? 'ok' : 'fail'],
                ['label' => 'Phiên ẩn danh', 'value' => substr($sessionHash, 0, 12).'...', 'tone' => 'neutral'],
                ['label' => 'Chế độ', 'value' => 'Sandbox thực thi thật', 'tone' => 'gold'],
            ],
            'details' => [
                'char_length' => $charLen,
                'session_hash_prefix' => substr($sessionHash, 0, 16),
            ],
        ];

        if (! $step1Valid) {
            $pipelineFailedStep = 1;
            $haltedAtStep = 1;
            $this->fillHaltedSteps($steps, 2, 'Dừng sớm vì Bước 1 không đạt chuẩn đầu vào.');

            return $this->finalizeResponse(
                steps: $steps,
                reply: 'Tin nhắn cần có độ dài từ 2 đến 600 ký tự để Trợ lý AI tiếp nhận xử lý.',
                intent: 'invalid_input',
                mode: 'invalid_input',
                riskLevel: AiChatbotLog::RISK_BLOCKED,
                templates: [],
                docs: [],
                plans: [],
                allowFeedback: false,
                pipelineFailedStep: $pipelineFailedStep,
                haltedAtStep: $haltedAtStep,
                totalStartedAt: $totalStartedAt,
                totalPromptTokens: 0,
                totalCompletionTokens: 0,
                draftModelUsed: $draftModelUsed,
                reviewerModelUsed: null,
                reviewScore: null,
                expectations: $expectations,
                scenario: $scenario
            );
        }

        // =====================================================================
        // BƯỚC 2: CỔNG BẢO MẬT, CHỦ ĐỀ CẤM & PHÂN TẦNG RỦI RO
        // =====================================================================
        $s2Start = microtime(true);
        $inputCheck = $this->policyGate->inspectAndClassifyInput($trimmedRaw);
        $cleanMessage = $inputCheck['clean_text'];
        $matchedForbiddenTopic = ! $inputCheck['blocked']
            ? $this->policyGate->matchForbiddenTopic($cleanMessage, (array) ($policy['forbidden_topics'] ?? []))
            : null;

        if ($inputCheck['blocked']) {
            $flags = $inputCheck['flags'];
            $safeRefusal = (string) $inputCheck['safe_refusal'];
            $steps[2] = [
                'step' => 2,
                'code' => 'security_gate',
                'title' => 'Cổng Bảo mật & Phân tầng',
                'component' => 'SecurityPolicyGate',
                'status' => 'failed',
                'duration_ms' => $this->elapsedMs($s2Start),
                'headline' => 'CHẶN BẢO MẬT: '.implode(', ', $flags),
                'summary' => 'Code cứng phát hiện mẫu tấn công/vi phạm bảo mật và chặn ngay trước khi gọi AI (tiêu tốn 0 token).',
                'error_reason' => 'Chặn bảo mật đầu vào: '.implode(', ', $flags),
                'metrics' => [
                    ['label' => 'Trạng thái', 'value' => 'BLOCKED', 'tone' => 'fail'],
                    ['label' => 'Cờ bảo mật', 'value' => implode(', ', $flags), 'tone' => 'fail'],
                    ['label' => 'Token tiêu thụ', 'value' => '0 token', 'tone' => 'ok'],
                ],
                'details' => [
                    'blocked' => true,
                    'flags' => $flags,
                    'risk_level' => AiChatbotLog::RISK_BLOCKED,
                ],
            ];
            $pipelineFailedStep = 2;
            $haltedAtStep = 2;
            $this->fillHaltedSteps($steps, 3, 'Ngắt luồng tại Bước 2 do Cổng Bảo mật Zero-Trust chặn đầu vào.');

            return $this->finalizeResponse(
                steps: $steps,
                reply: $safeRefusal,
                intent: 'blocked_security',
                mode: AiChatbotLog::MODE_BLOCKED_SECURITY,
                riskLevel: AiChatbotLog::RISK_BLOCKED,
                templates: [],
                docs: [],
                plans: [],
                allowFeedback: false,
                pipelineFailedStep: $pipelineFailedStep,
                haltedAtStep: $haltedAtStep,
                totalStartedAt: $totalStartedAt,
                totalPromptTokens: 0,
                totalCompletionTokens: 0,
                draftModelUsed: $draftModelUsed,
                reviewerModelUsed: null,
                reviewScore: null,
                expectations: $expectations,
                scenario: $scenario
            );
        }

        if ($matchedForbiddenTopic !== null) {
            $refusal = $this->buildOutOfScopeRefusal();
            $steps[2] = [
                'step' => 2,
                'code' => 'security_gate',
                'title' => 'Cổng Bảo mật & Phân tầng',
                'component' => 'SecurityPolicyGate',
                'status' => 'failed',
                'duration_ms' => $this->elapsedMs($s2Start),
                'headline' => "CHẶN CHỦ ĐỀ CẤM: \"{$matchedForbiddenTopic}\"",
                'summary' => 'Tin nhắn chạm danh sách forbidden_topics do Quản trị viên cấu hình nên bị từ chối ngay trước khi gọi AI (0 token).',
                'error_reason' => "Khớp chủ đề cấm cấu hình bởi Admin: {$matchedForbiddenTopic}",
                'metrics' => [
                    ['label' => 'Trạng thái', 'value' => 'OUT OF SCOPE', 'tone' => 'fail'],
                    ['label' => 'Chủ đề cấm', 'value' => $matchedForbiddenTopic, 'tone' => 'fail'],
                    ['label' => 'Token tiêu thụ', 'value' => '0 token', 'tone' => 'ok'],
                ],
                'details' => [
                    'blocked' => true,
                    'matched_forbidden_topic' => $matchedForbiddenTopic,
                ],
            ];
            $pipelineFailedStep = 2;
            $haltedAtStep = 2;
            $this->fillHaltedSteps($steps, 3, 'Ngắt luồng tại Bước 2 vì chạm chủ đề cấm do Admin cấu hình.');

            return $this->finalizeResponse(
                steps: $steps,
                reply: $refusal,
                intent: 'out_of_scope',
                mode: AiChatbotLog::MODE_OUT_OF_SCOPE,
                riskLevel: AiChatbotLog::RISK_SIMPLE,
                templates: [],
                docs: [],
                plans: [],
                allowFeedback: false,
                pipelineFailedStep: $pipelineFailedStep,
                haltedAtStep: $haltedAtStep,
                totalStartedAt: $totalStartedAt,
                totalPromptTokens: 0,
                totalCompletionTokens: 0,
                draftModelUsed: $draftModelUsed,
                reviewerModelUsed: null,
                reviewScore: null,
                expectations: $expectations,
                scenario: $scenario
            );
        }

        $s2Elapsed = $this->elapsedMs($s2Start);

        // =====================================================================
        // BƯỚC 3: ĐỌC NGỮ CẢNH ĐA LƯỢT & 3 CHỈ MỤC NGƯỢC BM25
        // =====================================================================
        $s3Start = microtime(true);
        /** @var array<int, array{user: string, bot: string, intent: string}> $recentHistory */
        $recentHistory = (array) Cache::get($sessionKey, []);
        $retrieval = $this->knowledgeService->retrieve($cleanMessage, $recentHistory, null);

        $knowledge = $retrieval['knowledge'];
        $fallbackTemplates = $retrieval['templates'];
        $fallbackDocs = $retrieval['docs'];
        $candidateDocs = $retrieval['candidate_docs'];
        $plans = $retrieval['plans'];
        $intent = $retrieval['intent'];
        $hasContext = (bool) $retrieval['has_context'];
        $hasDirectContext = (bool) ($retrieval['has_direct_context'] ?? $hasContext);
        $evidenceRefs = $retrieval['evidence_refs'] ?? [];
        $relations = $retrieval['relations'] ?? [];

        // Cập nhật risk_level chính xác theo intent thực tế
        $riskCheck = $this->policyGate->inspectAndClassifyInput($cleanMessage, $intent);
        $riskLevel = $riskCheck['risk_level'];
        $isElevatedRisk = in_array($riskLevel, [AiChatbotLog::RISK_COMPLEX, AiChatbotLog::RISK_HIGH], true);
        $reviewerEnabled = (bool) $policy['enable_reviewer_for_complex'];
        $maxReviewRounds = (int) $policy['max_review_rounds'];
        $willUseCritic = $reviewerEnabled && $isElevatedRisk && $maxReviewRounds > 0;

        $step2Summary = match (true) {
            $willUseCritic => 'Không phát hiện tấn công hay chủ đề cấm. Câu hỏi được phân tầng '.strtoupper($riskLevel).' nên sẽ tự động kích hoạt Critic AI kiểm định tại Bước 5.',
            $isElevatedRisk && ! $reviewerEnabled => 'Không phát hiện tấn công. Câu hỏi đã phân tầng '.strtoupper($riskLevel).' nhưng công tắc "Tự động kiểm tra chéo câu trả lời quan trọng" đang TẮT trong tab Cài đặt AI nên Bước 5 sẽ không chạy.',
            $isElevatedRisk && $maxReviewRounds <= 0 => 'Không phát hiện tấn công. Câu hỏi đã phân tầng '.strtoupper($riskLevel).' nhưng số vòng kiểm định (max_review_rounds) đang đặt bằng 0 trong Cài đặt AI.',
            default => 'Không phát hiện tấn công hay chủ đề cấm. Câu hỏi thuộc nhóm cơ bản (SIMPLE), chạy luồng nhanh tối ưu độ trễ.',
        };

        $criticMetricValue = match (true) {
            $willUseCritic => 'Bật (Bước 5)',
            $isElevatedRisk && ! $reviewerEnabled => 'Đang TẮT ở Cài đặt AI',
            $isElevatedRisk && $maxReviewRounds <= 0 => 'Đang đặt 0 vòng',
            default => 'Không cần (SIMPLE)',
        };

        $criticMetricTone = match (true) {
            $willUseCritic => 'blue',
            $isElevatedRisk => 'warn',
            default => 'neutral',
        };

        $steps[2] = [
            'step' => 2,
            'code' => 'security_gate',
            'title' => 'Cổng Bảo mật & Phân tầng',
            'component' => 'SecurityPolicyGate',
            'status' => 'passed',
            'duration_ms' => $s2Elapsed,
            'headline' => 'An toàn Zero-Trust • Phân tầng '.strtoupper($riskLevel),
            'summary' => $step2Summary,
            'error_reason' => null,
            'metrics' => [
                ['label' => 'Bảo mật đầu vào', 'value' => 'An toàn ✓', 'tone' => 'ok'],
                ['label' => 'Phân tầng rủi ro', 'value' => strtoupper($riskLevel), 'tone' => $riskLevel === AiChatbotLog::RISK_SIMPLE ? 'ok' : 'gold'],
                ['label' => 'Kích hoạt Critic', 'value' => $criticMetricValue, 'tone' => $criticMetricTone],
            ],
            'details' => [
                'blocked' => false,
                'risk_level' => $riskLevel,
                'will_use_critic' => $willUseCritic,
                'reviewer_enabled' => $reviewerEnabled,
                'max_review_rounds' => $maxReviewRounds,
            ],
        ];

        $verifiedContextText = $retrieval['verified_context_text'];
        if ($verifiedContextText === '' || in_array($intent, ['greeting', 'thanks', 'general'], true)) {
            $coreSummary = $this->knowledgeService->buildCoreCatalogSummary();
            $verifiedContextText = $verifiedContextText !== ''
                ? $verifiedContextText."\n\n".$coreSummary
                : $coreSummary;
        }

        $topDocsPreview = array_map(fn (array $d): array => [
            'title' => (string) $d['title'],
            'url' => (string) $d['url'],
            'score' => (float) ($d['score'] ?? 0),
        ], array_slice($candidateDocs, 0, 3));

        $topKnowledgePreview = array_map(fn (array $k): array => [
            'id' => (int) $k['id'],
            'title' => (string) $k['title'],
            'score' => (float) ($k['score'] ?? 0),
        ], array_slice($knowledge, 0, 3));

        $topTemplatesPreview = array_map(fn (array $t): array => [
            'name' => (string) $t['name'],
            'slug' => (string) $t['slug'],
            'score' => (float) ($t['score'] ?? 0),
        ], array_slice($fallbackTemplates, 0, 3));

        $step3Status = ($hasDirectContext || in_array($intent, ['greeting', 'thanks'], true)) ? 'passed' : 'warn';
        $steps[3] = [
            'step' => 3,
            'code' => 'bm25_retrieval',
            'title' => '3 Chỉ mục BM25 & Ngữ cảnh',
            'component' => 'ChatbotKnowledgeService::retrieve',
            'status' => $step3Status,
            'duration_ms' => $this->elapsedMs($s3Start),
            'headline' => $hasDirectContext
                ? 'Khớp '.count($evidenceRefs).' bằng chứng • intent = '.$intent
                : 'Chưa khớp trực tiếp trong DB • chuyển AI kiểm tra phạm vi',
            'summary' => $hasDirectContext
                ? 'Đã đọc '.count($recentHistory).' lượt hội thoại trước và truy xuất 3 chỉ mục ngược BM25 phân biệt dấu tiếng Việt (Tri thức DB, Mẫu thiệp, Client Docs).'
                : 'Không có bản ghi nào khớp trực tiếp với câu hiện tại; hệ thống gắn nhãn NO_DIRECT_DATABASE_MATCH để AI siết chặt kiểm tra phạm vi Mây Hẹn.',
            'error_reason' => null,
            'metrics' => [
                ['label' => 'Ý định (Intent)', 'value' => $intent, 'tone' => 'blue'],
                ['label' => 'Bằng chứng khớp', 'value' => count($evidenceRefs).' refs', 'tone' => $hasDirectContext ? 'ok' : 'warn'],
                ['label' => 'Tri thức / Docs / Mẫu', 'value' => count($knowledge).' / '.count($candidateDocs).' / '.count($fallbackTemplates), 'tone' => 'neutral'],
            ],
            'details' => [
                'history_turns' => count($recentHistory),
                'intent' => $intent,
                'has_context' => $hasContext,
                'has_direct_context' => $hasDirectContext,
                'evidence_count' => count($evidenceRefs),
                'top_docs' => $topDocsPreview,
                'top_knowledge' => $topKnowledgePreview,
                'top_templates' => $topTemplatesPreview,
                'relations_count' => count($relations),
            ],
        ];

        // =====================================================================
        // BƯỚC 4: AI TƯ VẤN TỔNG HỢP JSON 1 LƯỢT
        // =====================================================================
        $s4Start = microtime(true);
        $reply = '';
        $isOutOfScope = false;
        $isStructuredDecision = false;
        $aiSelectedTemplateSlugs = [];
        $aiSelectedDocUrls = [];
        $mode = AiChatbotLog::MODE_FALLBACK_GROUNDED;
        $providerStatus = 'not_called';
        $candidateReply = '';
        $draftOk = false;

        if ($policy['kill_switch']) {
            $providerStatus = 'kill_switch_active';
            $steps[4] = [
                'step' => 4,
                'code' => 'draft_llm',
                'title' => 'AI Tư vấn Tổng hợp JSON',
                'component' => 'ChatbotProviderClient::synthesize',
                'status' => 'failed',
                'duration_ms' => $this->elapsedMs($s4Start),
                'headline' => 'Kill-Switch đang bật — Không gọi AI bên ngoài',
                'summary' => 'Công tắc tạm tắt AI bên ngoài đang được kích hoạt trong Cài đặt AI. Hệ thống chuyển sang câu trả lời dự phòng an toàn từ dữ liệu gốc.',
                'error_reason' => 'AI Kill-Switch đang bật.',
                'metrics' => [
                    ['label' => 'Trạng thái', 'value' => 'Kill-Switch ON', 'tone' => 'fail'],
                    ['label' => 'Token', 'value' => '0', 'tone' => 'neutral'],
                    ['label' => 'Chế độ', 'value' => 'Safe Fallback', 'tone' => 'warn'],
                ],
                'details' => ['provider_status' => $providerStatus],
            ];
            $pipelineFailedStep = $pipelineFailedStep ?? 4;
            $reply = $this->buildConciseSalesFallback($knowledge, $fallbackTemplates, $fallbackDocs, $plans, $intent, $hasContext);
            $mode = $hasContext ? AiChatbotLog::MODE_FALLBACK_GROUNDED : AiChatbotLog::MODE_NO_CONTEXT_FALLBACK;
        } elseif (! $this->governance->hasRemainingDailyTokenBudget()) {
            $providerStatus = 'daily_budget_exceeded';
            $steps[4] = [
                'step' => 4,
                'code' => 'draft_llm',
                'title' => 'AI Tư vấn Tổng hợp JSON',
                'component' => 'ChatbotProviderClient::synthesize',
                'status' => 'failed',
                'duration_ms' => $this->elapsedMs($s4Start),
                'headline' => 'Hết ngân sách Token hằng ngày',
                'summary' => 'Đã đạt hạn mức Token trong ngày nên hệ thống ngừng gọi LLM và trả về phương án dự phòng xác thực.',
                'error_reason' => 'Vượt ngân sách Token hằng ngày.',
                'metrics' => [
                    ['label' => 'Trạng thái', 'value' => 'Hết ngân sách', 'tone' => 'fail'],
                    ['label' => 'Token', 'value' => '0', 'tone' => 'neutral'],
                    ['label' => 'Chế độ', 'value' => 'Safe Fallback', 'tone' => 'warn'],
                ],
                'details' => ['provider_status' => $providerStatus],
            ];
            $pipelineFailedStep = $pipelineFailedStep ?? 4;
            $reply = $this->buildConciseSalesFallback($knowledge, $fallbackTemplates, $fallbackDocs, $plans, $intent, $hasContext);
            $mode = $hasContext ? AiChatbotLog::MODE_FALLBACK_GROUNDED : AiChatbotLog::MODE_NO_CONTEXT_FALLBACK;
        } else {
            $draftResult = $this->providerClient->synthesize(
                sanitizedUserMessage: $cleanMessage,
                verifiedContext: $verifiedContextText,
                recentHistory: $recentHistory,
                customerMemory: null,
                intent: $intent,
                hasDirectContext: $hasDirectContext
            );

            $providerStatus = (string) $draftResult['status'];
            $draftModelUsed = (string) ($draftResult['model'] ?: $draftModelUsed);
            $totalPromptTokens += (int) $draftResult['prompt_tokens'];
            $totalCompletionTokens += (int) $draftResult['completion_tokens'];

            if (! $draftResult['ok']) {
                $steps[4] = [
                    'step' => 4,
                    'code' => 'draft_llm',
                    'title' => 'AI Tư vấn Tổng hợp JSON',
                    'component' => 'ChatbotProviderClient::synthesize',
                    'status' => 'failed',
                    'duration_ms' => $this->elapsedMs($s4Start),
                    'headline' => 'Lỗi gọi mô hình AI: '.$providerStatus,
                    'summary' => 'Nhà cung cấp AI không phản hồi hoặc trả dữ liệu rỗng. Hệ thống tự động chuyển sang câu trả lời dự phòng xác thực để khách không bị gián đoạn.',
                    'error_reason' => 'Provider AI thất bại: '.$providerStatus,
                    'metrics' => [
                        ['label' => 'Model', 'value' => $draftModelUsed ?: 'Chưa cấu hình', 'tone' => 'fail'],
                        ['label' => 'Trạng thái', 'value' => $providerStatus, 'tone' => 'fail'],
                        ['label' => 'Token', 'value' => '0', 'tone' => 'neutral'],
                    ],
                    'details' => ['provider_status' => $providerStatus, 'model' => $draftModelUsed],
                ];
                $pipelineFailedStep = $pipelineFailedStep ?? 4;
                $reply = $this->buildConciseSalesFallback($knowledge, $fallbackTemplates, $fallbackDocs, $plans, $intent, $hasContext);
                $mode = $hasContext ? AiChatbotLog::MODE_FALLBACK_GROUNDED : AiChatbotLog::MODE_NO_CONTEXT_FALLBACK;
            } else {
                $draftOk = true;
                $candidateReply = (string) $draftResult['text'];
                $isStructuredDecision = (bool) ($draftResult['structured'] ?? false);
                $aiSelectedTemplateSlugs = (array) ($draftResult['selected_template_slugs'] ?? []);
                $aiSelectedDocUrls = (array) ($draftResult['selected_doc_urls'] ?? []);
                $llmFlaggedOutOfScope = (bool) ($draftResult['out_of_scope'] ?? false);

                if ($llmFlaggedOutOfScope) {
                    $isOutOfScope = true;
                    $intent = 'out_of_scope';
                    $mode = AiChatbotLog::MODE_OUT_OF_SCOPE;
                    $reply = $this->buildOutOfScopeRefusal();
                    $aiSelectedTemplateSlugs = [];
                    $aiSelectedDocUrls = [];
                    $plans = [];
                    $pipelineFailedStep = 4;
                    $haltedAtStep = 4;

                    $steps[4] = [
                        'step' => 4,
                        'code' => 'draft_llm',
                        'title' => 'AI Tư vấn Tổng hợp JSON',
                        'component' => 'ChatbotProviderClient::synthesize',
                        'status' => 'failed',
                        'duration_ms' => $this->elapsedMs($s4Start),
                        'headline' => 'NGOÀI PHẠM VI (out_of_scope = true) — Từ chối lịch sự',
                        'summary' => 'Mô hình AI phát hiện tin nhắn nằm ngoài phạm vi Mây Hẹn & cưới hỏi nên bật cờ out_of_scope = true, xóa toàn bộ thẻ gợi ý và áp dụng câu từ chối chuẩn thương hiệu.',
                        'error_reason' => 'Nội dung ngoài phạm vi Mây Hẹn (AI bật out_of_scope = true).',
                        'metrics' => [
                            ['label' => 'Model', 'value' => $draftModelUsed ?: 'default', 'tone' => 'neutral'],
                            ['label' => 'Phạm vi', 'value' => 'out_of_scope = true', 'tone' => 'fail'],
                            ['label' => 'Token', 'value' => (string) ($draftResult['prompt_tokens'] + $draftResult['completion_tokens']), 'tone' => 'neutral'],
                        ],
                        'details' => [
                            'model' => $draftModelUsed,
                            'structured' => $isStructuredDecision,
                            'out_of_scope' => true,
                            'selected_template_slugs' => [],
                            'selected_doc_urls' => [],
                            'draft_preview' => Str::limit($candidateReply, 220),
                        ],
                    ];

                    $steps[5] = $this->skippedStep(5, 'critic_review', 'AI Phản biện (Critic)', 'reviewDraftWithCritic', 'Bỏ qua Critic AI vì đã từ chối ngoài phạm vi tại Bước 4 (tiết kiệm token).');
                    $steps[6] = [
                        'step' => 6,
                        'code' => 'output_policy_gate',
                        'title' => 'Hậu kiểm Code & Allowlist',
                        'component' => 'SecurityPolicyGate + resolveDisplayCards',
                        'status' => 'passed',
                        'duration_ms' => 1,
                        'headline' => 'Cưỡng chế từ chối chuẩn • 0 thẻ Mẫu • 0 thẻ Docs',
                        'summary' => 'Hệ thống thay bằng câu từ chối chuẩn Mây Hẹn, tắt phản hồi đánh giá và không đưa lượt này vào lịch sử hội thoại kế tiếp.',
                        'error_reason' => null,
                        'metrics' => [
                            ['label' => 'Phạm vi', 'value' => 'Đã cưỡng chế ✓', 'tone' => 'ok'],
                            ['label' => 'Thẻ hiển thị', 'value' => '0 Mẫu • 0 Docs', 'tone' => 'ok'],
                            ['label' => 'Cô lập lịch sử', 'value' => 'Không lưu vào context', 'tone' => 'ok'],
                        ],
                        'details' => ['out_of_scope_enforced' => true],
                    ];
                } else {
                    $steps[4] = [
                        'step' => 4,
                        'code' => 'draft_llm',
                        'title' => 'AI Tư vấn Tổng hợp JSON',
                        'component' => 'ChatbotProviderClient::synthesize',
                        'status' => 'passed',
                        'duration_ms' => $this->elapsedMs($s4Start),
                        'headline' => 'Tổng hợp thành công • chọn '.count($aiSelectedTemplateSlugs).' mẫu, '.count($aiSelectedDocUrls).' docs',
                        'summary' => 'AI trả về bản nháp câu trả lời kèm danh sách thẻ liên quan trong 1 lượt gọi duy nhất (out_of_scope = false).',
                        'error_reason' => null,
                        'metrics' => [
                            ['label' => 'Model', 'value' => $draftModelUsed ?: 'default', 'tone' => 'gold'],
                            ['label' => 'JSON có cấu trúc', 'value' => $isStructuredDecision ? 'Có ✓' : 'Văn bản thuần', 'tone' => 'ok'],
                            ['label' => 'Thẻ AI chọn', 'value' => count($aiSelectedTemplateSlugs).' Mẫu • '.count($aiSelectedDocUrls).' Docs', 'tone' => 'blue'],
                        ],
                        'details' => [
                            'model' => $draftModelUsed,
                            'structured' => $isStructuredDecision,
                            'out_of_scope' => false,
                            'selected_template_slugs' => $aiSelectedTemplateSlugs,
                            'selected_doc_urls' => $aiSelectedDocUrls,
                            'draft_preview' => Str::limit($candidateReply, 260),
                            'tokens' => $draftResult['prompt_tokens'] + $draftResult['completion_tokens'],
                        ],
                    ];
                }
            }
        }

        // =====================================================================
        // BƯỚC 5: AI PHẢN BIỆN KIỂM ĐỊNH (CRITIC REVIEWER)
        // =====================================================================
        if (! isset($steps[5])) {
            $s5Start = microtime(true);
            if (! $draftOk) {
                $steps[5] = $this->skippedStep(5, 'critic_review', 'AI Phản biện (Critic)', 'reviewDraftWithCritic', 'Bỏ qua vì Bước 4 không có bản nháp LLM (đang dùng dự phòng an toàn).');
            } elseif (! $willUseCritic) {
                $mode = AiChatbotLog::MODE_LLM_GROUNDED;
                if ($isElevatedRisk && ! $reviewerEnabled) {
                    $steps[5] = [
                        'step' => 5,
                        'code' => 'critic_review',
                        'title' => 'AI Phản biện (Critic)',
                        'component' => 'reviewDraftWithCritic',
                        'status' => 'warn',
                        'duration_ms' => 0,
                        'headline' => 'Chưa chạy vì công tắc Critic AI đang TẮT trong Cài đặt AI',
                        'summary' => 'Câu hỏi đã được phân tầng '.strtoupper($riskLevel).' (đủ điều kiện kiểm định Bước 5), nhưng công tắc "Tự động kiểm tra chéo câu trả lời quan trọng" trong tab Cài đặt AI đang TẮT. Hãy bật lại và Lưu cài đặt để Critic AI hoạt động.',
                        'error_reason' => null,
                        'metrics' => [
                            ['label' => 'Phân tầng câu hỏi', 'value' => strtoupper($riskLevel), 'tone' => 'gold'],
                            ['label' => 'Công tắc Critic', 'value' => 'Đang TẮT (0)', 'tone' => 'warn'],
                            ['label' => 'Cách bật', 'value' => 'Tab Cài đặt AI', 'tone' => 'blue'],
                        ],
                        'details' => [
                            'skipped_reason' => 'reviewer_disabled_in_governance',
                            'risk_level' => $riskLevel,
                        ],
                    ];
                } elseif ($isElevatedRisk && $maxReviewRounds <= 0) {
                    $steps[5] = [
                        'step' => 5,
                        'code' => 'critic_review',
                        'title' => 'AI Phản biện (Critic)',
                        'component' => 'reviewDraftWithCritic',
                        'status' => 'warn',
                        'duration_ms' => 0,
                        'headline' => 'Chưa chạy vì số vòng phản biện đang đặt = 0',
                        'summary' => 'Câu hỏi đã phân tầng '.strtoupper($riskLevel).' nhưng thiết lập max_review_rounds đang bằng 0 trong tab Cài đặt AI.',
                        'error_reason' => null,
                        'metrics' => [
                            ['label' => 'Phân tầng câu hỏi', 'value' => strtoupper($riskLevel), 'tone' => 'gold'],
                            ['label' => 'Vòng phản biện', 'value' => '0 vòng', 'tone' => 'warn'],
                            ['label' => 'Cách bật', 'value' => 'Đặt ≥ 1 ở Cài đặt AI', 'tone' => 'blue'],
                        ],
                        'details' => [
                            'skipped_reason' => 'max_review_rounds_zero',
                            'risk_level' => $riskLevel,
                        ],
                    ];
                } else {
                    $steps[5] = $this->skippedStep(5, 'critic_review', 'AI Phản biện (Critic)', 'reviewDraftWithCritic', 'Câu hỏi thuộc mức cơ bản (SIMPLE) nên đi thẳng sang Bước 6 để trả lời nhanh nhất.');
                }
            } else {
                $review = $this->providerClient->reviewDraftWithCritic($cleanMessage, $verifiedContextText, $candidateReply, $recentHistory);
                $reviewerModelUsed = (string) ($review['model'] ?: $policy['reviewer_model']);
                $totalPromptTokens += (int) $review['prompt_tokens'];
                $totalCompletionTokens += (int) $review['completion_tokens'];

                if (! $review['ok']) {
                    $isHighRisk = $riskLevel === AiChatbotLog::RISK_HIGH;
                    if ($isHighRisk) {
                        $candidateReply = '';
                        $pipelineFailedStep = $pipelineFailedStep ?? 5;
                    }
                    $steps[5] = [
                        'step' => 5,
                        'code' => 'critic_review',
                        'title' => 'AI Phản biện (Critic)',
                        'component' => 'reviewDraftWithCritic',
                        'status' => $isHighRisk ? 'failed' : 'warn',
                        'duration_ms' => $this->elapsedMs($s5Start),
                        'headline' => $isHighRisk
                            ? 'Critic không khả dụng trên câu RỦI RO CAO → Hủy bản nháp'
                            : 'Critic tạm không phản hồi (chuyển tiếp có hậu kiểm Bước 6)',
                        'summary' => $isHighRisk
                            ? 'Vì câu hỏi thuộc nhóm High-Risk (tài chính/hoàn tiền), hệ thống tuyệt đối không tự hạ chuẩn kiểm định mà chuyển sang fallback an toàn.'
                            : 'Mô hình Reviewer tạm thời không phản hồi; bản nháp tiếp tục đi qua lớp hậu kiểm code cứng ở Bước 6.',
                        'error_reason' => $isHighRisk ? 'Reviewer AI không khả dụng cho câu hỏi High-Risk.' : null,
                        'metrics' => [
                            ['label' => 'Reviewer', 'value' => $reviewerModelUsed ?: 'N/A', 'tone' => 'warn'],
                            ['label' => 'Trạng thái', 'value' => (string) $review['status'], 'tone' => $isHighRisk ? 'fail' : 'warn'],
                            ['label' => 'Xử lý', 'value' => $isHighRisk ? 'Ép Fallback' : 'Qua Bước 6', 'tone' => $isHighRisk ? 'fail' : 'neutral'],
                        ],
                        'details' => ['status' => $review['status']],
                    ];
                } else {
                    $reviewScore = (int) $review['grounded_score'];
                    $verdict = (string) $review['verdict'];
                    $issues = (array) $review['issues'];

                    if ($verdict === 'approve') {
                        $mode = AiChatbotLog::MODE_LLM_REVIEWED;
                        $steps[5] = [
                            'step' => 5,
                            'code' => 'critic_review',
                            'title' => 'AI Phản biện (Critic)',
                            'component' => 'reviewDraftWithCritic',
                            'status' => 'passed',
                            'duration_ms' => $this->elapsedMs($s5Start),
                            'headline' => "APPROVE • Điểm tin cậy {$reviewScore}/100",
                            'summary' => 'Critic AI đã đối chiếu đủ 5 tiêu chí (sự thật DB, ngữ cảnh, bảo mật Zero-Trust, phạm vi Mây Hẹn, trải nghiệm) và phê duyệt bản nháp.',
                            'error_reason' => null,
                            'metrics' => [
                                ['label' => 'Điểm tin cậy', 'value' => "{$reviewScore}/100", 'tone' => 'ok'],
                                ['label' => 'Phán quyết', 'value' => 'APPROVE ✓', 'tone' => 'ok'],
                                ['label' => 'Reviewer', 'value' => $reviewerModelUsed ?: 'default', 'tone' => 'blue'],
                            ],
                            'details' => [
                                'verdict' => 'approve',
                                'grounded_score' => $reviewScore,
                                'issues' => $issues,
                                'reviewer_model' => $reviewerModelUsed,
                            ],
                        ];
                    } elseif ($verdict === 'revise') {
                        $guidance = $review['revision_guidance'] ?: implode('; ', $issues);
                        $revised = $this->providerClient->reviseDraftWithFeedback($cleanMessage, $verifiedContextText, $candidateReply, $guidance, $recentHistory);
                        $totalPromptTokens += (int) $revised['prompt_tokens'];
                        $totalCompletionTokens += (int) $revised['completion_tokens'];

                        if ($revised['ok']) {
                            $candidateReply = (string) $revised['text'];
                            $mode = AiChatbotLog::MODE_LLM_REVIEWED;
                            $steps[5] = [
                                'step' => 5,
                                'code' => 'critic_review',
                                'title' => 'AI Phản biện (Critic)',
                                'component' => 'reviewDraftWithCritic',
                                'status' => 'warn',
                                'duration_ms' => $this->elapsedMs($s5Start),
                                'headline' => "REVISE ({$reviewScore}/100) → Đã tự sửa lại bản nháp",
                                'summary' => 'Critic AI phát hiện điểm cần tinh chỉnh và đã yêu cầu viết lại 1 vòng sát dữ liệu nguồn hơn: '.Str::limit($guidance, 140),
                                'error_reason' => null,
                                'metrics' => [
                                    ['label' => 'Điểm vòng đầu', 'value' => "{$reviewScore}/100", 'tone' => 'warn'],
                                    ['label' => 'Phán quyết', 'value' => 'REVISE → Đã sửa', 'tone' => 'gold'],
                                    ['label' => 'Số góp ý', 'value' => count($issues).' mục', 'tone' => 'neutral'],
                                ],
                                'details' => [
                                    'verdict' => 'revise',
                                    'grounded_score' => $reviewScore,
                                    'issues' => $issues,
                                    'revision_guidance' => $guidance,
                                ],
                            ];
                        } else {
                            $candidateReply = '';
                            $pipelineFailedStep = $pipelineFailedStep ?? 5;
                            $steps[5] = [
                                'step' => 5,
                                'code' => 'critic_review',
                                'title' => 'AI Phản biện (Critic)',
                                'component' => 'reviewDraftWithCritic',
                                'status' => 'failed',
                                'duration_ms' => $this->elapsedMs($s5Start),
                                'headline' => 'REVISE thất bại khi viết lại → Chuyển Fallback',
                                'summary' => 'Critic yêu cầu viết lại nhưng quá trình sửa gặp lỗi nên hệ thống hủy bản nháp.',
                                'error_reason' => 'Không thể hoàn tất vòng sửa bản nháp theo góp ý Critic.',
                                'metrics' => [
                                    ['label' => 'Điểm', 'value' => "{$reviewScore}/100", 'tone' => 'fail'],
                                    ['label' => 'Phán quyết', 'value' => 'REVISE FAIL', 'tone' => 'fail'],
                                    ['label' => 'Xử lý', 'value' => 'Safe Fallback', 'tone' => 'warn'],
                                ],
                                'details' => ['verdict' => 'revise_failed', 'issues' => $issues],
                            ];
                        }
                    } else {
                        $candidateReply = '';
                        $pipelineFailedStep = $pipelineFailedStep ?? 5;
                        $steps[5] = [
                            'step' => 5,
                            'code' => 'critic_review',
                            'title' => 'AI Phản biện (Critic)',
                            'component' => 'reviewDraftWithCritic',
                            'status' => 'failed',
                            'duration_ms' => $this->elapsedMs($s5Start),
                            'headline' => "REJECT ({$reviewScore}/100) — Bác bỏ bản nháp",
                            'summary' => 'Critic AI bác bỏ bản nháp do sai sự thật, ngoài phạm vi hoặc thiếu bằng chứng: '.Str::limit(implode('; ', $issues), 150),
                            'error_reason' => 'Critic AI từ chối bản nháp: '.(implode('; ', $issues) ?: 'Thiếu bằng chứng xác thực'),
                            'metrics' => [
                                ['label' => 'Điểm tin cậy', 'value' => "{$reviewScore}/100", 'tone' => 'fail'],
                                ['label' => 'Phán quyết', 'value' => 'REJECT ✗', 'tone' => 'fail'],
                                ['label' => 'Xử lý', 'value' => 'Ép Safe Fallback', 'tone' => 'warn'],
                            ],
                            'details' => [
                                'verdict' => 'reject',
                                'grounded_score' => $reviewScore,
                                'issues' => $issues,
                            ],
                        ];
                    }
                }
            }
        }

        // =====================================================================
        // BƯỚC 6: HẬU KIỂM CODE CỨNG, PHẠM VI 3 LỚP & ALLOWLIST DB
        // =====================================================================
        $templates = [];
        $docs = [];
        if (! isset($steps[6])) {
            $s6Start = microtime(true);
            if ($draftOk && $candidateReply !== '') {
                $driftReason = $this->policyGate->detectOutOfScopeDrift($cleanMessage, $candidateReply, $hasDirectContext, $intent);
                if ($driftReason !== null) {
                    $isOutOfScope = true;
                    $intent = 'out_of_scope';
                    $mode = AiChatbotLog::MODE_OUT_OF_SCOPE;
                    $reply = $this->buildOutOfScopeRefusal();
                    $plans = [];
                    $pipelineFailedStep = $pipelineFailedStep ?? 6;

                    $steps[6] = [
                        'step' => 6,
                        'code' => 'output_policy_gate',
                        'title' => 'Hậu kiểm Code & Allowlist',
                        'component' => 'SecurityPolicyGate + resolveDisplayCards',
                        'status' => 'failed',
                        'duration_ms' => $this->elapsedMs($s6Start),
                        'headline' => 'CHẶN TRƯỢT PHẠM VI BẰNG CODE CỨNG ('.$driftReason.')',
                        'summary' => 'Lớp hậu kiểm phát hiện bản nháp AI trượt khỏi phạm vi Mây Hẹn/đám cưới nên lập tức thay bằng câu từ chối chuẩn và ẩn mọi thẻ gợi ý.',
                        'error_reason' => 'Hậu kiểm chặn trượt phạm vi: '.$driftReason,
                        'metrics' => [
                            ['label' => 'Phát hiện', 'value' => $driftReason, 'tone' => 'fail'],
                            ['label' => 'Cưỡng chế', 'value' => 'Out-of-Scope Refusal', 'tone' => 'gold'],
                            ['label' => 'Thẻ trả về', 'value' => '0 Mẫu • 0 Docs', 'tone' => 'ok'],
                        ],
                        'details' => ['drift_reason' => $driftReason],
                    ];
                } else {
                    $outputCheck = $this->policyGate->inspectFinalOutput($candidateReply, $verifiedContextText, $knowledge);
                    $verbatimShortened = false;
                    if ($outputCheck['safe'] && $outputCheck['verbatim_dump']) {
                        $outputCheck['clean_text'] = $this->summarizeTextSafely($outputCheck['clean_text'], 260);
                        $verbatimShortened = true;
                    }

                    if (! $outputCheck['safe']) {
                        $flags = $outputCheck['flags'];
                        $pipelineFailedStep = $pipelineFailedStep ?? 6;
                        $isStructuredDecision = false;
                        $reply = $this->buildConciseSalesFallback($knowledge, $fallbackTemplates, $fallbackDocs, $plans, $intent, $hasContext);
                        $mode = $hasContext ? AiChatbotLog::MODE_FALLBACK_GROUNDED : AiChatbotLog::MODE_NO_CONTEXT_FALLBACK;

                        $resolved = $this->knowledgeService->resolveDisplayCards($reply, false, [], [], $fallbackTemplates, $fallbackDocs);
                        $reply = $resolved['reply'];
                        $templates = $resolved['templates'];
                        $docs = $resolved['docs'];

                        $steps[6] = [
                            'step' => 6,
                            'code' => 'output_policy_gate',
                            'title' => 'Hậu kiểm Code & Allowlist',
                            'component' => 'SecurityPolicyGate + resolveDisplayCards',
                            'status' => 'failed',
                            'duration_ms' => $this->elapsedMs($s6Start),
                            'headline' => 'CHẶN ĐẦU RA VI PHẠM: '.implode(', ', $flags),
                            'summary' => 'Code cứng phát hiện vi phạm đầu ra (URL ngoài, PII không xác thực hoặc Brand Safety) và đã thay thế bằng phản hồi dự phòng an toàn.',
                            'error_reason' => 'Output Policy Gate chặn: '.implode(', ', $flags),
                            'metrics' => [
                                ['label' => 'Cờ vi phạm', 'value' => implode(', ', $flags), 'tone' => 'fail'],
                                ['label' => 'Biện pháp', 'value' => 'Thay Safe Fallback', 'tone' => 'warn'],
                                ['label' => 'Thẻ Allowlist', 'value' => count($templates).' Mẫu • '.count($docs).' Docs', 'tone' => 'neutral'],
                            ],
                            'details' => ['output_flags' => $flags],
                        ];
                    } else {
                        $reply = $outputCheck['clean_text'];
                        if ($mode !== AiChatbotLog::MODE_LLM_REVIEWED) {
                            $mode = AiChatbotLog::MODE_LLM_GROUNDED;
                        }

                        $resolved = $this->knowledgeService->resolveDisplayCards(
                            reply: $reply,
                            isStructuredDecision: $isStructuredDecision,
                            aiSelectedTemplateSlugs: $aiSelectedTemplateSlugs,
                            aiSelectedDocUrls: $aiSelectedDocUrls,
                            fallbackTemplates: $fallbackTemplates,
                            fallbackDocs: $fallbackDocs
                        );
                        $reply = $resolved['reply'];
                        $templates = $resolved['templates'];
                        $docs = $resolved['docs'];

                        $resolvedSlugs = array_column($templates, 'slug');
                        $resolvedDocUrls = array_column($docs, 'url');
                        $droppedSlugs = array_values(array_diff($aiSelectedTemplateSlugs, $resolvedSlugs));
                        $droppedDocs = array_values(array_diff($aiSelectedDocUrls, $resolvedDocUrls));

                        $steps[6] = [
                            'step' => 6,
                            'code' => 'output_policy_gate',
                            'title' => 'Hậu kiểm Code & Allowlist',
                            'component' => 'SecurityPolicyGate + resolveDisplayCards',
                            'status' => 'passed',
                            'duration_ms' => $this->elapsedMs($s6Start),
                            'headline' => 'Sạch bảo mật • Allowlist: '.count($templates).' Mẫu, '.count($docs).' Docs',
                            'summary' => ($droppedSlugs !== [] || $droppedDocs !== [])
                                ? 'Đã chặn '.(count($droppedSlugs) + count($droppedDocs)).' slug/link ảo không tồn tại trong DB và chuẩn hóa toàn bộ đường dẫn thành nhãn tiếng Việt gọn đẹp.'
                                : 'Đầu ra đạt 100% chuẩn Zero-Trust (0 link ngoài, 0 PII, sạch JSON) và đã gắn nhãn liên kết ngắn gọn.',
                            'error_reason' => null,
                            'metrics' => [
                                ['label' => 'Output Gate', 'value' => $verbatimShortened ? 'Đạt (Đã rút gọn)' : 'An toàn 100% ✓', 'tone' => 'ok'],
                                ['label' => 'Thẻ hiển thị', 'value' => count($templates).' Mẫu • '.count($docs).' Docs', 'tone' => 'blue'],
                                ['label' => 'Lọc ảo (Allowlist)', 'value' => (count($droppedSlugs) + count($droppedDocs)).' bị loại', 'tone' => ($droppedSlugs !== [] || $droppedDocs !== []) ? 'warn' : 'ok'],
                            ],
                            'details' => [
                                'resolved_template_slugs' => $resolvedSlugs,
                                'resolved_doc_urls' => $resolvedDocUrls,
                                'dropped_fake_slugs' => $droppedSlugs,
                                'dropped_fake_docs' => $droppedDocs,
                            ],
                        ];
                    }
                }
            } else {
                // Đang ở chế độ fallback từ Bước 4 hoặc Bước 5
                if ($reply === '') {
                    $reply = $this->buildConciseSalesFallback($knowledge, $fallbackTemplates, $fallbackDocs, $plans, $intent, $hasContext);
                    $mode = $hasContext ? AiChatbotLog::MODE_FALLBACK_GROUNDED : AiChatbotLog::MODE_NO_CONTEXT_FALLBACK;
                }
                $resolved = $this->knowledgeService->resolveDisplayCards($reply, false, [], [], $fallbackTemplates, $fallbackDocs);
                $reply = $resolved['reply'];
                $templates = $resolved['templates'];
                $docs = $resolved['docs'];

                $steps[6] = [
                    'step' => 6,
                    'code' => 'output_policy_gate',
                    'title' => 'Hậu kiểm Code & Allowlist',
                    'component' => 'SecurityPolicyGate + resolveDisplayCards',
                    'status' => 'warn',
                    'duration_ms' => $this->elapsedMs($s6Start),
                    'headline' => 'Phản hồi dự phòng xác thực • '.count($templates).' Mẫu, '.count($docs).' Docs',
                    'summary' => 'Đã áp dụng câu trả lời dự phòng từ dữ liệu gốc và xác minh thẻ hiển thị qua Allowlist.',
                    'error_reason' => null,
                    'metrics' => [
                        ['label' => 'Chế độ', 'value' => $mode, 'tone' => 'warn'],
                        ['label' => 'Thẻ hiển thị', 'value' => count($templates).' Mẫu • '.count($docs).' Docs', 'tone' => 'blue'],
                        ['label' => 'An toàn', 'value' => '100% xác thực', 'tone' => 'ok'],
                    ],
                    'details' => ['fallback_applied' => true],
                ];
            }
        }

        // Lưu lịch sử phiên sandbox nếu lượt này trong phạm vi (để Admin test chat đa lượt thật)
        if (! $isOutOfScope) {
            $recentHistory[] = [
                'user' => $this->securityGuard->sanitizePlainText($cleanMessage, 300),
                'bot' => $this->securityGuard->sanitizePlainText($reply, 420),
                'intent' => $intent,
            ];
            Cache::put($sessionKey, array_slice($recentHistory, -8), now()->addHours(2));
        }

        return $this->finalizeResponse(
            steps: $steps,
            reply: $reply,
            intent: $intent,
            mode: $mode,
            riskLevel: $riskLevel,
            templates: $templates,
            docs: $docs,
            plans: $plans,
            allowFeedback: ! $isOutOfScope,
            pipelineFailedStep: $pipelineFailedStep,
            haltedAtStep: $haltedAtStep,
            totalStartedAt: $totalStartedAt,
            totalPromptTokens: $totalPromptTokens,
            totalCompletionTokens: $totalCompletionTokens,
            draftModelUsed: $draftModelUsed,
            reviewerModelUsed: $reviewerModelUsed,
            reviewScore: $reviewScore,
            expectations: $expectations,
            scenario: $scenario
        );
    }

    /**
     * Xóa sạch bộ nhớ hội thoại của phiên sandbox hiện tại.
     */
    public function resetSandboxSession(int $adminUserId, string $sandboxSession = 'default'): void
    {
        Cache::forget($this->historyCacheKey($adminUserId, $sandboxSession));
    }

    /**
     * Hoàn tất Bước 7, đối chiếu tiêu chí kịch bản test (nếu có) và tự động cập nhật trạng thái vào DB.
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @param  array<int, array<string, mixed>>  $templates
     * @param  array<int, array<string, mixed>>  $docs
     * @param  array<int, array<string, mixed>>  $plans
     * @param  array<string, mixed>|null  $expectations
     * @return array<string, mixed>
     */
    protected function finalizeResponse(
        array $steps,
        string $reply,
        string $intent,
        string $mode,
        string $riskLevel,
        array $templates,
        array $docs,
        array $plans,
        bool $allowFeedback,
        ?int $pipelineFailedStep,
        ?int $haltedAtStep,
        float $totalStartedAt,
        int $totalPromptTokens,
        int $totalCompletionTokens,
        string $draftModelUsed,
        ?string $reviewerModelUsed,
        ?int $reviewScore,
        ?array $expectations,
        ?AiChatTestScenario $scenario
    ): array {
        $assertions = $this->evaluateAssertions($expectations, $reply, $mode, $templates, $docs);
        $hasAssertions = $assertions !== [];
        $failedAssertions = array_values(array_filter($assertions, fn (array $a): bool => ! $a['passed']));

        $totalLatencyMs = $this->elapsedMs($totalStartedAt);
        $costVnd = AiQualityAndCostObservatoryService::calculateTokenCost(
            $draftModelUsed ?: 'gemini-1.5-flash',
            $totalPromptTokens,
            $totalCompletionTokens
        );

        // Nếu có kịch bản kiểm thử (expectations), đánh giá PASS/FAIL theo đúng tiêu chí kỳ vọng!
        // (Ví dụ: test Prompt Injection kỳ vọng blocked_security tại Bước 2 -> Pipeline dừng ở Bước 2 là ĐÚNG KỲ VỌNG = PASS!)
        if ($hasAssertions) {
            $overallPassed = $failedAssertions === [];
            $finalFailedStep = null;

            if (! $overallPassed) {
                $expectedMode = (string) ($expectations['expected_mode'] ?? 'any_answer');
                if ($expectedMode === 'any_answer' && $pipelineFailedStep !== null) {
                    $finalFailedStep = $pipelineFailedStep;
                } else {
                    $finalFailedStep = 7;
                }
            }
        } else {
            $overallPassed = $pipelineFailedStep === null;
            $finalFailedStep = $pipelineFailedStep;
        }

        if (! isset($steps[7])) {
            $step7Status = $hasAssertions
                ? ($failedAssertions === [] ? 'passed' : 'failed')
                : ($pipelineFailedStep === null ? 'passed' : 'warn');

            $steps[7] = [
                'step' => 7,
                'code' => 'final_delivery',
                'title' => 'Trả UI & Chấm Kết quả',
                'component' => 'AiChatbotLog + AssertionGate',
                'status' => $step7Status,
                'duration_ms' => 1,
                'headline' => $hasAssertions
                    ? ($failedAssertions === []
                        ? 'PASS '.count($assertions).'/'.count($assertions).' tiêu chí kịch bản ✓'
                        : 'FAIL '.count($failedAssertions).'/'.count($assertions).' tiêu chí kịch bản ✗')
                    : ($pipelineFailedStep === null
                        ? "Hoàn tất ({$mode}) • {$totalLatencyMs}ms"
                        : "Hoàn tất kèm cảnh báo tại Bước {$pipelineFailedStep}"),
                'summary' => $hasAssertions
                    ? ($failedAssertions === []
                        ? 'Toàn bộ tiêu chí kịch bản test đều đạt. Câu trả lời sẵn sàng hiển thị an toàn lên khung chat khách hàng.'
                        : 'Có '.count($failedAssertions).' tiêu chí không khớp kỳ vọng: '.implode(' | ', array_map(fn (array $a) => $a['label'], $failedAssertions)))
                    : 'Trả kết quả an toàn ra giao diện khách hàng (ẩn trace_metadata nội bộ, che giấu PII theo Luật 91/2025/QH15).',
                'error_reason' => $failedAssertions !== []
                    ? implode('; ', array_map(fn (array $a) => $a['detail'], $failedAssertions))
                    : null,
                'metrics' => [
                    ['label' => 'Chế độ cuối', 'value' => $mode, 'tone' => $overallPassed ? 'ok' : 'fail'],
                    ['label' => 'Tổng thời gian', 'value' => $totalLatencyMs.' ms', 'tone' => 'neutral'],
                    ['label' => 'Tổng Token', 'value' => (string) ($totalPromptTokens + $totalCompletionTokens), 'tone' => 'gold'],
                ],
                'details' => [
                    'mode' => $mode,
                    'intent' => $intent,
                    'total_latency_ms' => $totalLatencyMs,
                    'total_tokens' => $totalPromptTokens + $totalCompletionTokens,
                    'cost_vnd' => $costVnd,
                ],
            ];
        }

        ksort($steps);
        $orderedSteps = array_values($steps);

        $overallStatus = $overallPassed ? 'passed' : 'failed';
        $verdictLabel = $this->buildVerdictLabel($overallPassed, $hasAssertions, $finalFailedStep, $mode, $failedAssertions);

        // Tự động lưu kết quả chạy gần nhất vào kịch bản test (Auto-save Run Result)
        if ($scenario !== null) {
            $scenario->update([
                'last_status' => $overallStatus,
                'last_failed_step' => $finalFailedStep,
                'last_summary' => Str::limit($verdictLabel, 250, ''),
                'last_latency_ms' => $totalLatencyMs,
                'last_run_at' => now(),
            ]);
        }

        return [
            'ok' => true,
            'overall_status' => $overallStatus,
            'verdict_label' => $verdictLabel,
            'failed_step' => $finalFailedStep,
            'pipeline_failed_step' => $pipelineFailedStep,
            'halted_at_step' => $haltedAtStep,
            'reply' => $reply,
            'intent' => $intent,
            'mode' => $mode,
            'risk_level' => $riskLevel,
            'review_score' => $reviewScore,
            'allow_feedback' => $allowFeedback,
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
            'steps' => $orderedSteps,
            'assertions' => $assertions,
            'telemetry' => [
                'total_latency_ms' => $totalLatencyMs,
                'prompt_tokens' => $totalPromptTokens,
                'completion_tokens' => $totalCompletionTokens,
                'total_tokens' => $totalPromptTokens + $totalCompletionTokens,
                'cost_vnd' => $costVnd,
                'draft_model' => $draftModelUsed,
                'reviewer_model' => $reviewerModelUsed,
            ],
            'scenario_id' => $scenario?->id,
        ];
    }

    /**
     * Đối chiếu thực tế với các tiêu chí của kịch bản test.
     *
     * @param  array<string, mixed>|null  $expectations
     * @param  array<int, array<string, mixed>>  $templates
     * @param  array<int, array<string, mixed>>  $docs
     * @return list<array{code: string, label: string, passed: bool, detail: string}>
     */
    protected function evaluateAssertions(
        ?array $expectations,
        string $reply,
        string $mode,
        array $templates,
        array $docs
    ): array {
        if ($expectations === null) {
            return [];
        }

        $results = [];
        $expectedMode = (string) ($expectations['expected_mode'] ?? 'any_answer');
        $expectedCards = (string) ($expectations['expected_cards'] ?? 'any');
        $mustContainRaw = trim((string) ($expectations['must_contain'] ?? ''));
        $mustNotContainRaw = trim((string) ($expectations['must_not_contain'] ?? ''));

        // 1. Kiểm tra chế độ phản hồi
        $modeOk = match ($expectedMode) {
            'llm_reviewed' => $mode === AiChatbotLog::MODE_LLM_REVIEWED,
            'out_of_scope' => $mode === AiChatbotLog::MODE_OUT_OF_SCOPE,
            'blocked_security' => $mode === AiChatbotLog::MODE_BLOCKED_SECURITY,
            default => in_array($mode, [
                AiChatbotLog::MODE_LLM_GROUNDED,
                AiChatbotLog::MODE_LLM_REVIEWED,
                AiChatbotLog::MODE_FALLBACK_GROUNDED,
            ], true),
        };
        $results[] = [
            'code' => 'expected_mode',
            'label' => 'Chế độ phản hồi ('.$expectedMode.')',
            'passed' => $modeOk,
            'detail' => $modeOk ? "Khớp chế độ thực tế: {$mode}" : "Kỳ vọng {$expectedMode}, thực tế nhận {$mode}",
        ];

        // 2. Kiểm tra thẻ hiển thị (Mẫu / Docs)
        if ($expectedCards !== 'any') {
            $tplCount = count($templates);
            $docCount = count($docs);
            $cardsOk = match ($expectedCards) {
                'none' => $tplCount === 0 && $docCount === 0,
                'templates_only' => $tplCount > 0 && $docCount === 0,
                'docs_only' => $tplCount === 0 && $docCount > 0,
                'both' => $tplCount > 0 && $docCount > 0,
                default => true,
            };
            $results[] = [
                'code' => 'expected_cards',
                'label' => 'Thẻ hiển thị ('.$expectedCards.')',
                'passed' => $cardsOk,
                'detail' => $cardsOk
                    ? "Đúng cấu trúc thẻ: {$tplCount} Mẫu, {$docCount} Docs"
                    : "Kỳ vọng {$expectedCards}, thực tế trả {$tplCount} Mẫu & {$docCount} Docs",
            ];
        }

        $replyLower = mb_strtolower($reply, 'UTF-8');

        // 3. Cụm từ bắt buộc có
        if ($mustContainRaw !== '') {
            $phrases = array_values(array_filter(array_map('trim', explode(',', $mustContainRaw))));
            $missing = [];
            foreach ($phrases as $phrase) {
                if (! str_contains($replyLower, mb_strtolower($phrase, 'UTF-8'))) {
                    $missing[] = $phrase;
                }
            }
            $results[] = [
                'code' => 'must_contain',
                'label' => 'Phải chứa từ khóa bắt buộc',
                'passed' => $missing === [],
                'detail' => $missing === []
                    ? 'Có đủ '.count($phrases).' cụm từ bắt buộc'
                    : 'Thiếu cụm từ: '.implode(', ', $missing),
            ];
        }

        // 4. Cụm từ cấm xuất hiện
        if ($mustNotContainRaw !== '') {
            $forbidden = array_values(array_filter(array_map('trim', explode(',', $mustNotContainRaw))));
            $leaked = [];
            foreach ($forbidden as $phrase) {
                if (str_contains($replyLower, mb_strtolower($phrase, 'UTF-8'))) {
                    $leaked[] = $phrase;
                }
            }
            $results[] = [
                'code' => 'must_not_contain',
                'label' => 'Không được chứa cụm từ cấm',
                'passed' => $leaked === [],
                'detail' => $leaked === []
                    ? 'Sạch 100% các cụm từ cấm'
                    : 'Lọt cụm từ cấm: '.implode(', ', $leaked),
            ];
        }

        return $results;
    }

    /**
     * @param  list<array{code: string, label: string, passed: bool, detail: string}>  $failedAssertions
     */
    protected function buildVerdictLabel(
        bool $overallPassed,
        bool $hasAssertions,
        ?int $finalFailedStep,
        string $mode,
        array $failedAssertions
    ): string {
        if ($hasAssertions) {
            if ($overallPassed) {
                return "PASS Kịch bản ✓ ({$mode})";
            }

            $first = $failedAssertions[0]['detail'] ?? 'Không khớp kỳ vọng';

            return "FAIL tại Bước {$finalFailedStep}: {$first}";
        }

        if ($overallPassed) {
            return "PASS toàn luồng 7/7 bước ({$mode})";
        }

        return match ($finalFailedStep) {
            1 => 'FAIL tại Bước 1 — Đầu vào không hợp lệ',
            2 => $mode === AiChatbotLog::MODE_OUT_OF_SCOPE
                ? 'DỪNG tại Bước 2 — Chặn chủ đề cấm Admin'
                : 'DỪNG tại Bước 2 — Chặn bảo mật Zero-Trust',
            4 => $mode === AiChatbotLog::MODE_OUT_OF_SCOPE
                ? 'DỪNG tại Bước 4 — Ngoài phạm vi Mây Hẹn'
                : 'FAIL tại Bước 4 — Không gọi được mô hình AI',
            5 => 'FAIL tại Bước 5 — Critic AI bác bỏ bản nháp',
            6 => $mode === AiChatbotLog::MODE_OUT_OF_SCOPE
                ? 'DỪNG tại Bước 6 — Hậu kiểm chặn trượt phạm vi'
                : 'FAIL tại Bước 6 — Output Policy Gate chặn vi phạm',
            default => 'FAIL — Không đạt kiểm duyệt',
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $steps
     */
    protected function fillHaltedSteps(array &$steps, int $fromStep, string $reason): void
    {
        $defs = [
            2 => ['security_gate', 'Cổng Bảo mật & Phân tầng', 'SecurityPolicyGate'],
            3 => ['bm25_retrieval', '3 Chỉ mục BM25 & Ngữ cảnh', 'ChatbotKnowledgeService::retrieve'],
            4 => ['draft_llm', 'AI Tư vấn Tổng hợp JSON', 'ChatbotProviderClient::synthesize'],
            5 => ['critic_review', 'AI Phản biện (Critic)', 'reviewDraftWithCritic'],
            6 => ['output_policy_gate', 'Hậu kiểm Code & Allowlist', 'SecurityPolicyGate + resolveDisplayCards'],
            7 => ['final_delivery', 'Trả UI & Chấm Kết quả', 'AiChatbotLog + AssertionGate'],
        ];

        for ($i = $fromStep; $i <= 7; $i++) {
            [$code, $title, $comp] = $defs[$i];
            $steps[$i] = [
                'step' => $i,
                'code' => $code,
                'title' => $title,
                'component' => $comp,
                'status' => 'halted',
                'duration_ms' => 0,
                'headline' => 'Không chạy (Luồng đã dừng ở bước trước)',
                'summary' => $reason,
                'error_reason' => null,
                'metrics' => [],
                'details' => ['halted' => true],
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function skippedStep(int $step, string $code, string $title, string $component, string $reason): array
    {
        return [
            'step' => $step,
            'code' => $code,
            'title' => $title,
            'component' => $component,
            'status' => 'skipped',
            'duration_ms' => 0,
            'headline' => 'Bỏ qua hợp lệ (Không cần bước này)',
            'summary' => $reason,
            'error_reason' => null,
            'metrics' => [
                ['label' => 'Trạng thái', 'value' => 'SKIPPED', 'tone' => 'neutral'],
            ],
            'details' => ['skipped' => true],
        ];
    }

    protected function buildOutOfScopeRefusal(): string
    {
        return 'Mình là Trợ lý AI chuyên hỗ trợ về thiệp cưới online và chuẩn bị đám cưới tại Mây Hẹn, nên rất tiếc không thể tư vấn các vấn đề ngoài phạm vi này. Nếu bạn cần chọn mẫu tại [Kho Mẫu Thiệp](/mau-thiep), xem [Bảng Giá](/bang-gia) hoặc tạo thử thiệp tại [Studio Tạo Thiệp](/tao-thiep), mình luôn sẵn sàng hỗ trợ ạ!';
    }

    /**
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
            return 'Xin chào bạn! Mình là Trợ lý AI của Mây Hẹn, có thể gợi ý mẫu thiệp cưới phù hợp phong cách và hỗ trợ bạn tạo thiệp online nhanh chóng.';
        }
        if ($intent === 'thanks') {
            return 'Rất vui được đồng hành cùng bạn! Khi cần xem thêm mẫu thiệp hoặc mở nhanh [Studio Tạo Thiệp](/tao-thiep), bạn cứ nhắn mình nhé.';
        }
        if (! $hasContext) {
            return 'Hiện mình chưa có thông tin xác thực chính xác cho câu hỏi này trên hệ thống Mây Hẹn. Bạn có thể xem trực tiếp [Kho Mẫu Thiệp](/mau-thiep), [Bảng Giá](/bang-gia) hoặc tra cứu nhanh tại [Trung tâm Hướng dẫn](/docs) nhé.';
        }
        if ($intent === 'pricing' && $plans !== []) {
            $summary = implode('; ', array_map(fn (array $p) => "Gói {$p['name']} ({$p['formatted_price']})", array_slice($plans, 0, 3)));

            return "Mây Hẹn hiện có các gói dịch vụ chính thức: {$summary}. Bạn có thể tạo thử thiệp miễn phí ngay tại [Studio Tạo Thiệp](/tao-thiep) hoặc xem chi tiết tại [Bảng Giá](/bang-gia) nhé.";
        }
        if ($templates !== [] && ($intent === 'templates' || $knowledge === [])) {
            $names = implode(', ', array_map(fn (array $t) => $t['name'], array_slice($templates, 0, 3)));

            return "Mây Hẹn có các mẫu thiệp rất phù hợp với nhu cầu của bạn như: {$names}. Bạn bấm Xem thử hoặc Tạo thiệp ngay trên các thẻ bên dưới nhé!";
        }
        if ($knowledge !== []) {
            return $this->summarizeTextSafely((string) $knowledge[0]['answer'], 240);
        }
        if ($docs !== []) {
            return "Mình đã tìm thấy bài hướng dẫn chính thức \"{$docs[0]['title']}\" đúng với thắc mắc của bạn. Bạn mở thẻ hướng dẫn bên dưới để xem chi tiết nhé.";
        }

        return 'Bạn có thể khám phá [Kho Mẫu Thiệp](/mau-thiep) hoặc tạo thử thiệp cưới miễn phí ngay tại [Studio Tạo Thiệp](/tao-thiep) nhé.';
    }

    protected function summarizeTextSafely(string $text, int $maxChars = 240): string
    {
        $clean = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if (mb_strlen($clean, 'UTF-8') <= $maxChars) {
            return $clean;
        }

        return Str::limit($clean, $maxChars);
    }

    protected function elapsedMs(float $startedAt): int
    {
        return max(1, (int) round((microtime(true) - $startedAt) * 1000));
    }

    protected function historyCacheKey(int $adminUserId, string $sandboxSession): string
    {
        $cleanSess = preg_replace('/[^a-zA-Z0-9_\-]/', '', $sandboxSession) ?: 'default';

        return 'mayhen_admin_sim_history:'.$adminUserId.':'.substr($cleanSess, 0, 40);
    }
}
