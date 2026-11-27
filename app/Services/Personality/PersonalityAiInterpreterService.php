<?php

declare(strict_types=1);

namespace App\Services\Personality;

use App\Models\PersonalitySession;
use App\Services\AiQualityAndCostObservatoryService;
use App\Services\Chatbot\ChatbotProviderClient;
use App\Services\Intelligence\AiGovernanceService;
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
     * AI Personality Coach — trả lời câu hỏi sâu bám sát hồ sơ kết quả thật.
     *
     * @return array{answer: string, evidence_refs: list<string>, source: string}
     */
    public function answerCoachQuestion(PersonalitySession $session, string $question): array
    {
        $cleanQ = trim(Str::limit(strip_tags($question), 400, ''));
        $contract = $this->buildDataContract($session);
        $evidenceRefs = $this->extractEvidenceRefs($contract);

        $fallbackAnswer = $this->buildCoachFallback($session, $contract, $cleanQ);
        $policy = $this->governance->getPolicy();
        if ($policy['kill_switch'] || trim((string) ($policy['draft_model'] ?: $policy['reviewer_model'])) === '') {
            return ['answer' => $fallbackAnswer, 'evidence_refs' => $evidenceRefs, 'source' => 'deterministic_fallback'];
        }

        $sys = implode("\n", [
            'Bạn là AI Personality Coach của Mây Hẹn — hỗ trợ người dùng hiểu rõ hơn hồ sơ tính cách của chính họ.',
            'CHỈ trả lời dựa trên <data_contract>. Không tự thêm -A/-T, không chẩn đoán bệnh lý, không bịa điểm Cognitive Function, không khuyên chia tay.',
            'Trả lời tự nhiên bằng tiếng Việt, súc tích 4–6 câu, có gợi ý thực hành hoặc câu hỏi tự đối chiếu cụ thể.',
            'CHỈ TRẢ VỀ JSON: {"answer":"..."}',
        ]);
        $usr = "Câu hỏi người dùng: {$cleanQ}\n<data_contract>\n".json_encode($contract, JSON_UNESCAPED_UNICODE)."\n</data_contract>";

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
                $ans = trim((string) $res['data']['answer']);
                if ($ans !== '' && ! $this->containsForbiddenHallucination($ans)) {
                    return ['answer' => $ans, 'evidence_refs' => $evidenceRefs, 'source' => 'llm_grounded'];
                }
            }
        } catch (Throwable) {
            // Safe fallback
        }

        return ['answer' => $fallbackAnswer, 'evidence_refs' => $evidenceRefs, 'source' => 'deterministic_fallback'];
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
            ['layer' => 1, 'title' => 'Tầng 1 — Bức chân dung tổng quan', 'content' => ($typeInfo ? "Nhóm đề xuất từ thuật toán: {$effectiveType} ({$typeInfo['vietnamese_name']} — hình tượng {$typeInfo['art_title']}). {$typeInfo['theory_summary']} " : 'Hồ sơ hiện có chiều cân bằng đúng điểm giữa nên có nhiều nhóm khả dĩ. ').$boundaryNotice, 'evidence_note' => 'Phương pháp: '.$session->assessment_version.' (hash '.substr((string) $session->calculation_hash, 0, 10).')'],
            ['layer' => 2, 'title' => 'Tầng 2 — Nguồn năng lượng (E / I)', 'content' => $dimSummary('EI'), 'evidence_note' => 'Chiều EI — Hướng ngoại không đồng nghĩa nói nhiều; Hướng nội không đồng nghĩa nhút nhát.'],
            ['layer' => 3, 'title' => 'Tầng 3 — Tiếp nhận thông tin (S / N)', 'content' => $dimSummary('SN'), 'evidence_note' => 'Chiều SN — Giác quan và Trực giác đều có giá trị ngang nhau.'],
            ['layer' => 4, 'title' => 'Tầng 4 — Ra quyết định (T / F)', 'content' => $dimSummary('TF'), 'evidence_note' => 'Chiều TF — T không vô cảm; F không thiếu lý trí.'],
            ['layer' => 5, 'title' => 'Tầng 5 — Tổ chức cuộc sống (J / P)', 'content' => $dimSummary('JP'), 'evidence_note' => 'Chiều JP — J không phải luôn ngăn nắp; P không phải thiếu trách nhiệm.'],
            ['layer' => 6, 'title' => 'Tầng 6 — Lý thuyết Chức năng Nhận thức', 'content' => 'Chuỗi chức năng tham chiếu theo quy ước phổ biến: '.$stackText, 'evidence_note' => 'Nguồn lý thuyết: SRC-JUNG-1921, SRC-MYERS-1980.'],
            ['layer' => 7, 'title' => 'Tầng 7 — Điểm mạnh tiềm năng', 'content' => $typeInfo ? implode(' • ', (array) $typeInfo['strengths']) : 'Bạn có khả năng thích nghi linh hoạt ở các chiều cân bằng — hãy tự chọn 2 điểm mạnh đúng với trải nghiệm thực tế nhất của bạn.', 'evidence_note' => 'Giả thuyết lý thuyết cần bạn tự đối chiếu.'],
            ['layer' => 8, 'title' => 'Tầng 8 — Tình huống cần lưu ý', 'content' => $typeInfo ? implode(' • ', (array) $typeInfo['watch_outs']) : 'Khi một chiều gần điểm giữa, bạn có thể thay đổi phong cách rõ rệt giữa môi trường công việc và gia đình.', 'evidence_note' => 'Khác biệt không phải khuyết điểm.'],
            ['layer' => 9, 'title' => 'Tầng 9 — Giao tiếp & Tình yêu', 'content' => $typeInfo ? $typeInfo['communication_style'].' Trong tình yêu: '.$typeInfo['love_theme'] : 'Hãy chia sẻ trực tiếp nhu cầu lắng nghe, không gian riêng và cách lập kế hoạch với người thương.', 'evidence_note' => 'Tham khảo thêm tính năng Personality for Couples để đối chiếu 2 hồ sơ độc lập.'],
            ['layer' => 10, 'title' => 'Tầng 10 — Công việc & Học tập', 'content' => $typeInfo ? $typeInfo['work_learning_style'] : 'Ưu tiên môi trường cho phép bạn phát huy đúng nhịp làm việc mà bạn đã xác nhận ở 4 chiều.', 'evidence_note' => 'Không dùng kết quả trắc nghiệm tính cách để giới hạn nghề nghiệp hay tuyển dụng.'],
            ['layer' => 11, 'title' => 'Tầng 11 — Phát triển bản thân', 'content' => $typeInfo ? implode(' • ', (array) $typeInfo['growth_prompts']) : 'Bắt đầu từ việc ghi nhận những tình huống khiến bạn có năng lượng tốt nhất trong tuần.', 'evidence_note' => 'Tự điều chỉnh theo mục tiêu cá nhân.'],
            ['layer' => 12, 'title' => 'Tầng 12 — Gợi ý hành động thực tế', 'content' => 'Chọn 1 thói quen nhỏ trong 7–14 ngày (ví dụ: nói rõ nhu cầu không gian riêng hoặc lắng nghe 2 phút trước khi góp ý) và đối chiếu lại cảm nhận thực tế của bạn.', 'evidence_note' => 'Sử dụng công cụ Growth Plan bên dưới để lưu lịch 7 / 14 / 30 ngày.'],
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
