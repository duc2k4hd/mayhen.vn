<?php

declare(strict_types=1);

namespace App\Services\Personality;

use App\Models\PersonalitySession;
use App\Services\AiQualityAndCostObservatoryService;
use App\Services\Chatbot\ChatbotProviderClient;
use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\ScopedToolAiGuardService;
use Illuminate\Support\Str;
use Throwable;

/**
 * Grounded AI Interpretation Engine (12 Tầng) + AI Personality Coach + Growth Plan + AI Validation Guard.
 *
 * - TUYỆT ĐỐI KHÔNG tự chọn mã nhóm, không sửa điểm Scoring Engine, không thêm -A/-T, không bịa % Cognitive Function,
 *   không suy đoán bệnh lý tâm thần, không tạo % hợp nhau giả và không khuyên chia tay.
 * - Mọi nhận định bám sát AI Data Contract (scores, boundary flags, relevant responses, limitations).
 * - Luôn có fallback chuẩn học thuật giàu căn cứ nếu LLM chưa cấu hình hoặc tạm gián đoạn.
 */
class PersonalityAiInterpreterService
{
    public function __construct(
        protected ChatbotProviderClient $providerClient,
        protected AiGovernanceService $governance,
        protected ScopedToolAiGuardService $scopedGuard,
        protected PersonalityTypeRegistry $typeRegistry,
        protected PersonalityMethodRegistry $methods
    ) {}

    /**
     * Sinh báo cáo 12 tầng có căn cứ tức thì (< 5ms, không gọi LLM đồng bộ để không bao giờ làm chậm bước nộp bài).
     *
     * @return array{ok: bool, source: string, report: array<string, mixed>}
     */
    public function generateInstant12LayerReport(PersonalitySession $session): array
    {
        $contract = $this->buildDataContract($session);

        return [
            'ok' => true,
            'source' => 'deterministic_grounded',
            'report' => $this->buildDeterministic12LayerFallback($session, $contract),
        ];
    }

    /**
     * Sinh báo cáo phân tích 12 tầng có căn cứ từ dữ liệu Scoring Engine (gọi LLM nếu khả dụng).
     *
     * @return array{ok: bool, source: string, report: array<string, mixed>}
     */
    public function generate12LayerReport(PersonalitySession $session): array
    {
        $contract = $this->buildDataContract($session);
        $fallback = $this->buildDeterministic12LayerFallback($session, $contract);

        $policy = $this->governance->getPolicy();
        if ($policy['kill_switch'] || trim((string) ($policy['draft_model'] ?: $policy['reviewer_model'])) === '') {
            return ['ok' => true, 'source' => 'deterministic_fallback', 'report' => $fallback];
        }

        $sys = implode("\n", [
            'Bạn là Chuyên gia Phân tích Tính cách & Giao tiếp có căn cứ dữ liệu của Mây Hẹn (The Art of Personality).',
            'QUY TẮC BẮT BUỘC TUYỆT ĐỐI:',
            '1. CHỈ diễn giải dựa trên JSON <data_contract> do Scoring Engine cung cấp. KHÔNG tự đổi mã nhóm, KHÔNG sửa điểm số.',
            '2. CẤM thêm hậu tố -A hoặc -T (NERIS). CẤM bịa phần trăm chức năng nhận thức (vd "Ni 95%"). CẤM bịa percentile dân số.',
            '3. CẤM chẩn đoán bệnh lý tâm thần, CẤM xếp hạng nhóm cao cấp/thấp kém, CẤM khẳng định nghề nghiệp tuyệt đối hay khuyên chia tay.',
            '4. Nếu có chiều nằm trong vùng ranh giới (is_boundary=true) hoặc điểm hòa (is_tie=true), BẮT BUỘC nhắc rõ điều đó và gợi ý đối chiếu nhóm gần kề.',
            '5. Văn phong tiếng Việt tinh tế, trung lập, giàu tính đối chiếu thực tế, ngắn gọn súc tích (mỗi tầng 2–4 câu có căn cứ).',
            'CHỈ TRẢ VỀ JSON HỢP LỆ:',
            '{"summary":"...","layers":[{"layer":1,"title":"...","content":"...","evidence_note":"..."}],"limitations":["..."]}',
        ]);

        $usr = "<data_contract>\n".json_encode($contract, JSON_UNESCAPED_UNICODE)."\n</data_contract>";

        try {
            $res = $this->providerClient->callJson(
                systemPrompt: $sys,
                userPrompt: $usr,
                model: $policy['draft_model'] ?: $policy['reviewer_model'],
                temperature: 0.25,
                maxTokens: 1600,
                timeoutSeconds: 30
            );

            $tokens = (int) ($res['prompt_tokens'] ?? 0) + (int) ($res['completion_tokens'] ?? 0);
            if ($tokens > 0) {
                $cost = (float) AiQualityAndCostObservatoryService::calculateTokenCost(
                    (string) ($res['model'] ?: 'gemini-1.5-flash'),
                    (int) ($res['prompt_tokens'] ?? 0),
                    (int) ($res['completion_tokens'] ?? 0)
                );
                $this->governance->recordLearningUsage($tokens, $cost);
            }

            if (! empty($res['ok']) && is_array($res['data'] ?? null) && ! empty($res['data']['layers'])) {
                $validated = $this->validateAiReportOutput($res['data'], $contract);
                if ($validated['valid']) {
                    return [
                        'ok' => true,
                        'source' => 'llm_grounded',
                        'report' => [
                            'summary' => (string) ($res['data']['summary'] ?? $fallback['summary']),
                            'layers' => array_slice(array_values(array_filter((array) $res['data']['layers'], 'is_array')), 0, 12),
                            'limitations' => ! empty($res['data']['limitations']) ? (array) $res['data']['limitations'] : $fallback['limitations'],
                            'generated_at' => now()->toIso8601String(),
                            'model' => (string) ($res['model'] ?? ''),
                        ],
                    ];
                }
            }
        } catch (Throwable) {
            // Fall through safely to deterministic grounded report
        }

        return ['ok' => true, 'source' => 'deterministic_fallback', 'report' => $fallback];
    }

    /**
     * AI Hỏi Đáp Riêng Theo Hồ Sơ — đi qua ScopedToolAiGuardService (Zero-Trust Input/Output Gate giống Tử Vi & Thần Số Học).
     *
     * @return array{ok: bool, answer: string, evidence_refs: list<string>, source: string, blocked?: bool}
     */
    public function answerCoachQuestion(PersonalitySession $session, string $question): array
    {
        $contract = $this->buildDataContract($session);
        $evidenceRefs = $this->extractEvidenceRefs($contract);
        $groundedTerms = $this->buildGroundedTerms($session, $contract);

        // 1. Cổng kiểm duyệt đầu vào Zero-Trust (ScopedToolAiGuardService)
        $inputGuard = $this->scopedGuard->inspectScopedQuestion(
            rawQuestion: $question,
            toolDomain: 'personality',
            groundedTerms: $groundedTerms,
            maxLength: 360
        );

        if (! $inputGuard['allowed']) {
            return [
                'ok' => false,
                'blocked' => true,
                'answer' => (string) ($inputGuard['message'] ?: 'Trợ lý chỉ giải đáp các câu hỏi xoay quanh hồ sơ tính cách, giao tiếp, tình yêu và công việc của bạn.'),
                'evidence_refs' => [],
                'source' => 'scoped_guard_blocked',
            ];
        }

        $cleanQ = $inputGuard['clean_question'];
        $fallbackAnswer = $this->buildCoachFallback($session, $contract, $cleanQ);
        $policy = $this->governance->getPolicy();

        if ($policy['kill_switch'] || trim((string) ($policy['draft_model'] ?: $policy['reviewer_model'])) === '') {
            return ['ok' => true, 'answer' => $fallbackAnswer, 'evidence_refs' => $evidenceRefs, 'source' => 'deterministic_fallback'];
        }

        $verifiedJson = (string) json_encode($contract, JSON_UNESCAPED_UNICODE);
        $sys = implode("\n", [
            $this->scopedGuard->buildZeroTrustSystemPreamble('verified_personality_profile'),
            'Bạn là Trợ lý Tâm lý & Giao tiếp của Mây Hẹn, giải đáp bằng tiếng Việt tự nhiên, ấm áp, dễ hiểu cho người mới.',
            'Không dùng thuật ngữ kỹ thuật rườm rà, không thêm hậu tố -A/-T, không bịa điểm phần trăm chức năng nhận thức, không chẩn đoán bệnh lý, không khuyên chia tay.',
            'Trả lời súc tích 4–6 câu, bám sát điểm số và câu trả lời thực tế của hồ sơ, kèm 1–2 gợi ý thực tế dễ áp dụng ngay.',
            'CHỈ TRẢ VỀ JSON: {"answer":"..."}',
        ]);
        $usr = "<verified_personality_profile>\n{$verifiedJson}\n</verified_personality_profile>\n\n{$inputGuard['wrapped_question']}";

        try {
            $res = $this->providerClient->callJson(
                systemPrompt: $sys,
                userPrompt: $usr,
                model: $policy['draft_model'] ?: $policy['reviewer_model'],
                temperature: 0.3,
                maxTokens: 650,
                timeoutSeconds: 25
            );
            if (! empty($res['ok']) && is_string($res['data']['answer'] ?? null)) {
                $rawAns = trim((string) $res['data']['answer']);
                if ($rawAns !== '' && ! $this->containsForbiddenHallucination($rawAns)) {
                    // 2. Cổng kiểm duyệt đầu ra xác định (ScopedToolAiGuardService)
                    $outGuard = $this->scopedGuard->verifyScopedOutput(
                        rawAnswer: $rawAns,
                        verifiedContext: $verifiedJson,
                        toolDomain: 'personality',
                        groundedTerms: $groundedTerms,
                        allowMultiline: true
                    );
                    if ($outGuard['safe']) {
                        return ['ok' => true, 'answer' => $outGuard['clean_answer'], 'evidence_refs' => $evidenceRefs, 'source' => 'llm_grounded'];
                    }
                }
            }
        } catch (Throwable) {
            // Safe fallback
        }

        return ['ok' => true, 'answer' => $fallbackAnswer, 'evidence_refs' => $evidenceRefs, 'source' => 'deterministic_fallback'];
    }

    /**
     * @param  array<string, mixed>  $contract
     * @return list<string>
     */
    protected function buildGroundedTerms(PersonalitySession $session, array $contract): array
    {
        $terms = ['tinh cach', 'giao tiep', 'tinh yeu', 'cong viec', 'ho so'];
        $type = $session->effectiveType();
        if ($type) {
            $terms[] = strtolower($type);
            $info = $this->typeRegistry->getType($type);
            if ($info) {
                $terms[] = Str::ascii(mb_strtolower((string) $info['art_title'], 'UTF-8'));
                $terms[] = Str::ascii(mb_strtolower((string) $info['vietnamese_name'], 'UTF-8'));
            }
        }
        foreach ((array) ($contract['possible_types'] ?? []) as $pt) {
            $terms[] = strtolower((string) $pt);
        }

        return array_values(array_unique(array_filter($terms)));
    }

    /**
     * Tạo Personality Growth Plan (7, 14 hoặc 30 ngày) tùy theo mục tiêu người dùng chọn.
     *
     * @return array<string, mixed>
     */
    public function buildGrowthPlan(PersonalitySession $session, string $goalKey, int $days = 14): array
    {
        $days = in_array($days, [7, 14, 30], true) ? $days : 14;
        $goals = [
            'communication' => 'Giao tiếp rõ ràng & thấu cảm hơn',
            'time_management' => 'Quản lý thời gian & giữ nhịp ổn định',
            'listening' => 'Lắng nghe sâu và giảm hiểu lầm',
            'relationship' => 'Thấu hiểu người yêu & chuẩn bị hôn nhân',
            'work_collab' => 'Phối hợp hiệu quả trong công việc',
            'self_strengths' => 'Phát huy điểm mạnh cá nhân',
        ];
        $goalLabel = $goals[$goalKey] ?? $goals['communication'];
        $typeCode = $session->effectiveType() ?? 'UNDECIDED';
        $typeInfo = $this->typeRegistry->getType($typeCode);
        $boundaryDims = array_keys((array) ($session->boundary_flags_json ?? []));

        $milestones = [
            [
                'phase' => 'Giai đoạn 1 (Ngày 1–'.min(3, $days).')',
                'title' => 'Quan sát thói quen thực tế không phán xét',
                'actions' => [
                    'Ghi chú ngắn 1 tình huống trong ngày liên quan đến mục tiêu "'.$goalLabel.'".',
                    $boundaryDims !== []
                        ? 'Quan sát sự linh hoạt của bạn ở chiều gần ranh giới ('.implode(', ', $boundaryDims).') tùy theo bối cảnh công việc hay gia đình.'
                        : 'Nhận diện khoảnh khắc bạn cảm thấy năng lượng tự nhiên nhất và khoảnh khắc dễ mệt mỏi nhất.',
                ],
            ],
            [
                'phase' => 'Giai đoạn 2 (Ngày '.min(4, $days).'–'.(int) ceil($days * 0.7).')',
                'title' => 'Thực hành 1 điều chỉnh nhỏ mỗi ngày',
                'actions' => array_merge(
                    (array) ($typeInfo['growth_prompts'] ?? ['Tập nói rõ mong muốn của bạn bằng câu ngắn gọn, tích cực trước khi kỳ vọng người khác tự hiểu.']),
                    ['Thử nghiệm ít nhất 1 cuộc trò chuyện chủ động áp dụng nguyên tắc lắng nghe trước khi phản hồi.']
                ),
            ],
            [
                'phase' => 'Giai đoạn 3 (Ngày '.((int) ceil($days * 0.7) + 1).'–'.$days.')',
                'title' => 'Tổng kết & Giữ lại thói quen hợp với bạn',
                'actions' => [
                    'Đánh dấu 1–2 thói quen giúp bạn thấy thoải mái và hiệu quả nhất; bỏ qua các gợi ý không phù hợp hoàn cảnh hiện tại.',
                    'Ghi lại một đoạn nhật ký ngắn trong Hồ sơ Lịch sử Tính cách để đối chiếu sau 1–3 tháng.',
                ],
            ],
        ];

        return [
            'goal_key' => $goalKey,
            'goal_label' => $goalLabel,
            'days' => $days,
            'type_code' => $typeCode,
            'note' => 'Kế hoạch linh hoạt có thể tùy chỉnh theo nhịp sống của bạn — không áp lực streak và không nhằm biến đổi bạn thành nhóm tính cách khác.',
            'milestones' => $milestones,
            'created_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Xây dựng AI Data Contract ẩn danh (không gửi email/SĐT/họ tên thật).
     *
     * @return array<string, mixed>
     */
    public function buildDataContract(PersonalitySession $session): array
    {
        $method = $this->methods->get($session->assessment_version);
        $scores = (array) ($session->scores_json ?? []);
        $effectiveType = $session->effectiveType();
        $typeMeta = $effectiveType ? $this->typeRegistry->getType($effectiveType) : null;

        return [
            'assessment_version' => $session->assessment_version,
            'method_version' => $session->method_version,
            'scoring_version' => $session->scoring_version,
            'model_code' => $session->model_code,
            'suggested_type' => $session->suggested_type,
            'best_fit_type' => $session->best_fit_type,
            'possible_types' => $session->possible_types_json ?? [],
            'alternative_types' => $scores['alternative_types'] ?? [],
            'boundary_flags' => $session->boundary_flags_json ?? [],
            'quality_flags' => $session->quality_flags_json ?? [],
            'dimensions' => $scores['dimensions'] ?? [],
            'theoretical_function_stack' => $typeMeta['function_stack'] ?? [],
            'sources' => $method['sources'] ?? [],
            'limitations' => [
                $method['validation_note'] ?? '',
                $method['trademark_disclaimer'] ?? '',
            ],
        ];
    }

    /**
     * AI Validation Guard — kiểm tra đầu ra LLM không vi phạm nguyên tắc khoa học/đạo đức.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $contract
     * @return array{valid: bool, reason: string}
     */
    public function validateAiReportOutput(array $data, array $contract): array
    {
        $blob = (string) json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($this->containsForbiddenHallucination($blob)) {
            return ['valid' => false, 'reason' => 'forbidden_hallucination_or_at_suffix'];
        }

        $suggested = (string) ($contract['suggested_type'] ?? '');
        if ($suggested !== '' && $suggested !== 'UNDECIDED' && $suggested !== 'OCEAN' && ! str_contains(strtoupper($blob), $suggested)) {
            return ['valid' => false, 'reason' => 'missing_suggested_type'];
        }

        return ['valid' => true, 'reason' => 'ok'];
    }

    public function containsForbiddenHallucination(string $text): bool
    {
        // Chặn hậu tố -A / -T của NERIS (ví dụ INFP-T, INTJ-A)
        if (preg_match('/\b[EI][NS][TF][JP]-(A|T)\b/u', $text) === 1) {
            return true;
        }
        // Chặn bịa điểm % Cognitive Function (vd "Ni 95%", "Fe 20%")
        if (preg_match('/\b(Ni|Ne|Si|Se|Ti|Te|Fi|Fe)\s*[:=]?\s*\d{1,3}%/u', $text) === 1) {
            return true;
        }
        // Chặn tỷ lệ hợp nhau / kết hôn / ly hôn giả hoặc chẩn đoán tâm thần
        $lower = mb_strtolower($text, 'UTF-8');
        foreach (['hợp nhau 9', 'hợp nhau 8', 'tỷ lệ kết hôn', 'khả năng ly hôn', 'bạn bị trầm cảm', 'rối loạn lo âu', 'nên chia tay ngay'] as $forbidden) {
            if (str_contains($lower, $forbidden)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $contract
     * @return array<string, mixed>
     */
    protected function buildDeterministic12LayerFallback(PersonalitySession $session, array $contract): array
    {
        $dims = (array) ($contract['dimensions'] ?? []);
        $effectiveType = $session->effectiveType() ?? 'UNDECIDED';
        $typeInfo = $this->typeRegistry->getType($effectiveType);
        $boundaryDims = array_keys((array) ($contract['boundary_flags'] ?? []));

        $dimSummary = function (string $dim) use ($dims): string {
            $d = (array) ($dims[$dim] ?? []);
            if (empty($d) || ($d['tendency'] ?? '') === 'insufficient_data') {
                return 'Chiều này hiện chưa đủ câu trả lời hợp lệ để diễn giải chi tiết.';
            }
            $pos = $d['positive_code'] ?? '';
            $neg = $d['negative_code'] ?? '';
            $pIdx = $d['positive_index'] ?? 50;
            $nIdx = $d['opposite_index'] ?? 50;
            $label = (string) ($d['tendency_label'] ?? '');
            $topEv = (array) (($d['evidence'][0] ?? []));
            $evText = ! empty($topEv['text']) ? ' (căn cứ từ câu '.$topEv['question_id'].': "'.$topEv['text'].'" — mức '.$topEv['raw_answer'].'/7)' : '';

            return "Chỉ số trên thang nội bộ: {$pos} {$pIdx} / {$neg} {$nIdx}. {$label}{$evText}.";
        };

        $boundaryNotice = $boundaryDims !== []
            ? 'Lưu ý quan trọng: Chiều '.implode(', ', $boundaryDims).' nằm gần điểm giữa (vùng 45–55 hoặc hòa 50), vì vậy bạn có xu hướng linh hoạt tùy hoàn cảnh và nên đối chiếu thêm nhóm gần kề trong mục Best-Fit.'
            : 'Cả 4 chiều đều thể hiện xu hướng tương đối rõ ràng trên phiên trả lời hiện tại.';

        $stackText = $typeInfo
            ? implode(' → ', (array) $typeInfo['function_stack']).' (dữ liệu lý thuyết tham chiếu theo khung Jung–Myers, không phải kết quả đo trực tiếp 8 chức năng nhận thức).'
            : 'Chưa xác định 1 mã nhóm duy nhất do có chiều cân bằng 50/50 — hãy dùng mục Best-Fit để tự đối chiếu.';

        $layers = [
            ['layer' => 1, 'title' => '1. Bức chân dung tổng quan của bạn', 'content' => ($typeInfo ? "Nhóm tính cách nổi bật của bạn là {$effectiveType} ({$typeInfo['vietnamese_name']} — hình tượng {$typeInfo['art_title']}). {$typeInfo['theory_summary']} " : 'Các xu hướng của bạn đang khá cân bằng và linh hoạt theo từng hoàn cảnh. ').$boundaryNotice, 'evidence_note' => ''],
            ['layer' => 2, 'title' => '2. Cách bạn nạp năng lượng (Hướng ngoại / Hướng nội)', 'content' => $dimSummary('EI'), 'evidence_note' => ''],
            ['layer' => 3, 'title' => '3. Cách bạn quan sát & tiếp nhận thông tin (Giác quan / Trực giác)', 'content' => $dimSummary('SN'), 'evidence_note' => ''],
            ['layer' => 4, 'title' => '4. Cách bạn cân nhắc & ra quyết định (Lý trí / Cảm xúc)', 'content' => $dimSummary('TF'), 'evidence_note' => ''],
            ['layer' => 5, 'title' => '5. Cách bạn sắp xếp cuộc sống (Nguyên tắc / Linh hoạt)', 'content' => $dimSummary('JP'), 'evidence_note' => ''],
            ['layer' => 6, 'title' => '6. Cách tư duy & cảm nhận sâu bên trong', 'content' => 'Chuỗi nét tư duy đặc trưng của bạn: '.$stackText, 'evidence_note' => ''],
            ['layer' => 7, 'title' => '7. Những điểm mạnh dễ nhận thấy ở bạn', 'content' => $typeInfo ? implode(' • ', (array) $typeInfo['strengths']) : 'Bạn có khả năng thích nghi linh hoạt — hãy tự chọn 2 điểm mạnh thấy đúng với bản thân nhất.', 'evidence_note' => ''],
            ['layer' => 8, 'title' => '8. Tình huống nhỏ bạn nên lưu tâm', 'content' => $typeInfo ? implode(' • ', (array) $typeInfo['watch_outs']) : 'Khi cân bằng giữa hai phía, phong cách của bạn có thể thay đổi khá rõ giữa lúc làm việc và lúc ở nhà.', 'evidence_note' => ''],
            ['layer' => 9, 'title' => '9. Trong giao tiếp & tình yêu', 'content' => $typeInfo ? $typeInfo['communication_style'].' Trong tình yêu: '.$typeInfo['love_theme'] : 'Hãy chia sẻ nhẹ nhàng với người thương về nhu cầu lắng nghe, khoảng lặng riêng và cách lên kế hoạch của bạn.', 'evidence_note' => ''],
            ['layer' => 10, 'title' => '10. Trong công việc & học tập', 'content' => $typeInfo ? $typeInfo['work_learning_style'] : 'Ưu tiên môi trường giúp bạn phát huy đúng nhịp độ tự nhiên của bản thân.', 'evidence_note' => ''],
            ['layer' => 11, 'title' => '11. Gợi ý phát triển bản thân', 'content' => $typeInfo ? implode(' • ', (array) $typeInfo['growth_prompts']) : 'Bắt đầu từ việc ghi nhận những khoảnh khắc khiến bạn cảm thấy hứng khởi và bình yên nhất trong tuần.', 'evidence_note' => ''],
            ['layer' => 12, 'title' => '12. Một bước nhỏ bạn có thể thử tuần này', 'content' => 'Chọn 1 thói quen nhỏ trong 7–14 ngày (ví dụ: nói rõ mong muốn của mình trước, hoặc lắng nghe trọn vẹn 2 phút trước khi góp ý) rồi xem bản thân cảm thấy thoải mái hơn thế nào.', 'evidence_note' => ''],
        ];

        return [
            'summary' => $layers[0]['content'],
            'layers' => $layers,
            'limitations' => [
                'Kết quả phản ánh câu trả lời tự báo cáo trong phiên bản '.$session->assessment_version.' ở thời điểm hiện tại, không phải nhãn cố định suốt đời.',
                'Công cụ trắc nghiệm 16 nhóm tính cách độc lập của Mây Hẹn (Pilot Beta v1.0), không phải bài đánh giá MBTI® chính thức có cấp phép và không thay thế tư vấn tâm lý chuyên môn.',
            ],
            'generated_at' => now()->toIso8601String(),
            'model' => 'deterministic_grounded_v1',
        ];
    }

    /**
     * @param  array<string, mixed>  $contract
     */
    protected function buildCoachFallback(PersonalitySession $session, array $contract, string $question): string
    {
        $type = $session->effectiveType() ?? 'UNDECIDED';
        $boundaryDims = array_keys((array) ($contract['boundary_flags'] ?? []));
        $boundaryText = $boundaryDims !== []
            ? ' Hiện tại chiều '.implode(', ', $boundaryDims).' của bạn nằm gần ranh giới (45–55 hoặc hòa 50), nên hoàn toàn bình thường nếu bạn thấy mình mang nét của cả hai phía tùy ngữ cảnh.'
            : '';

        return "Dựa trên hồ sơ phiên {$session->assessment_version} (nhóm đề xuất: {$type}): kết quả này được tính xác định từ câu trả lời thực tế của bạn ở 4 chiều E/I, S/N, T/F, J/P.{$boundaryText} Với câu hỏi \"{$question}\", bạn có thể bấm nút 'Xem căn cứ' ở từng chiều để kiểm tra những câu trả lời có tín hiệu mạnh nhất, và tự đối chiếu xem hành vi đó xảy ra thường xuyên ở công việc hay đời sống tình cảm. Lưu ý rằng trắc nghiệm tính cách chỉ là công cụ gợi mở tự hiểu mình, không phải kết luận cố định hay đánh giá chuyên môn sức khỏe tâm thần.";
    }

    /**
     * @param  array<string, mixed>  $contract
     * @return list<string>
     */
    protected function extractEvidenceRefs(array $contract): array
    {
        $refs = ['method:'.($contract['assessment_version'] ?? 'standard')];
        foreach ((array) ($contract['dimensions'] ?? []) as $dim => $data) {
            if (isset($data['positive_index'])) {
                $refs[] = "{$dim}:{$data['positive_index']}";
            }
            foreach (array_slice((array) ($data['evidence'] ?? []), 0, 2) as $ev) {
                if (! empty($ev['question_id'])) {
                    $refs[] = 'q:'.$ev['question_id'].'='.$ev['raw_answer'];
                }
            }
        }

        return array_values(array_unique($refs));
    }
}
