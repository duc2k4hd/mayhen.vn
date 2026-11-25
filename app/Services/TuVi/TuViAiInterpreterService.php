<?php

declare(strict_types=1);

namespace App\Services\TuVi;

use App\Models\TuViChart;
use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\Contracts\LlmProviderInterface;
use Throwable;

/**
 * Dịch vụ AI Luận Giải Tử Vi 6 Tầng Có Căn Cứ & Nhân Duyên Song Mệnh — Mây Hẹn Thiên Mệnh.
 *
 * - Chỉ gửi dữ liệu lá số ẩn danh (KHÔNG gửi tên thật/email/SĐT sang nhà cung cấp AI).
 * - Hậu kiểm bắt buộc qua TuViSafetyGuardService; fallback hoàn chỉnh 6 tầng từ kho tri thức đã thẩm định.
 */
class TuViAiInterpreterService
{
    protected TuViSafetyGuardService $safetyGuard;

    public function __construct(
        protected LlmProviderInterface $provider,
        protected AiGovernanceService $governance,
        ?TuViSafetyGuardService $safetyGuard = null
    ) {
        $this->safetyGuard = $safetyGuard ?? new TuViSafetyGuardService;
    }

    /**
     * Luận giải 6 tầng đầy đủ cho 1 lá số cá nhân.
     *
     * @param  array<string, mixed>  $chart
     * @param  array<string, mixed>  $ruleAnalysis
     * @return array<string, mixed>
     */
    public function interpretChart(
        array $chart,
        array $ruleAnalysis,
        string $focusTopic = 'toan_bo',
        bool $allowAi = true
    ): array {
        $fallback = $this->buildGroundedFallback($chart, $ruleAnalysis, $focusTopic);

        if (! $allowAi || ($chart['status'] ?? '') === 'partial_no_birth_hour') {
            return $fallback;
        }

        try {
            $policy = $this->governance->getPolicy();
            if (! empty($policy['kill_switch']) || ! $this->provider->isAvailable()) {
                return $fallback;
            }

            $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
            $promptContext = $this->buildAnonymousChartBrief($chart, $ruleAnalysis, $focusTopic);

            $systemPrompt = implode("\n", [
                'Bạn là Học giả Tử Vi Đẩu Số Nam Phái Việt Nam điềm đạm, sâu sắc và nhân văn của Mây Hẹn – Thiên Mệnh.',
                'THÔNG ĐIỆP: "Hiểu mình, hiểu người, vun đắp nhân duyên."',
                'NGUYÊN TẮC CỨNG:',
                '1. TUYỆT ĐỐI chỉ luận dựa trên các cung, sao, Tứ Hóa, cách cục và vận hạn có trong <verified_tuvi_chart>. KHÔNG tự thêm sao, KHÔNG tự gán tên cách cục ngoài danh sách đã xác minh.',
                '2. TUYỆT ĐỐI KHÔNG chẩn đoán bệnh, KHÔNG dự đoán tuổi thọ/tử vong/sinh con/giới tính thai nhi, KHÔNG khẳng định ngoại tình/ly hôn/không được cưới, KHÔNG gạ cúng giải hạn, KHÔNG chấm % hợp tuổi hay độ chính xác 98%.',
                '3. Văn phong tiếng Việt hiện đại, tinh tế, tránh câu văn mẫu chung chung ("bạn mạnh mẽ nhưng đôi khi yếu đuối"). Nêu rõ căn cứ cung/sao đang dùng.',
                '4. Trả về DUY NHẤT 1 JSON hợp lệ theo schema:',
                '{"headline":"1 câu chân dung ngắn gọn, mang tính cá nhân","core_message":"2-4 câu trả lời trực tiếp chủ đề người dùng quan tâm","layer_1_overview":"Tầng 1 (3-4 câu): Tổng quan Bản Mệnh, Ngũ Hành Cục, Cung Mệnh và Cung Thân","layer_2_palace_highlights":{"menh":"2 câu về Cung Mệnh","phu_the":"2 câu về Cung Phu Thê","quan_loc":"2 câu về Cung Quan Lộc","tai_bach":"2 câu về Cung Tài Bạch","dien_trach":"2 câu về Cung Điền Trạch","phuc_duc":"2 câu về Cung Phúc Đức"},"layer_3_combinations":"Tầng 3 (3-4 câu): Phân tích Tam Phương Tứ Chính, Tứ Hóa và Cách Cục đã xác minh","layer_4_love_family":"Tầng 4 (3-4 câu): Tình yêu, giao tiếp, chuẩn bị hôn nhân & hòa hợp gia đình hai bên","layer_5_life_stages":"Tầng 5 (3 câu): Chủ đề chiêm nghiệm của Đại Hạn, Tiểu Hạn và Lưu Niên năm đang xem","layer_6_action_plan":{"today":"1 việc nhỏ hôm nay","this_week":"1 cuộc trò chuyện hoặc việc ưu tiên trong tuần","next_30_days":"1 mục tiêu thực tế trong 30 ngày","long_term":"1 định hướng dài hạn"},"strengths":["Điểm phát huy 1","Điểm phát huy 2","Điểm phát huy 3"],"watch_outs":["Điều cần lưu tâm 1","Điều cần lưu tâm 2"],"reflection_question":"1 câu hỏi tự chiêm nghiệm sâu sắc"}',
            ]);

            $result = $this->provider->completeJson(
                systemPrompt: $systemPrompt,
                userPrompt: $promptContext,
                model: $model,
                temperature: 0.32,
                maxTokens: 2000,
                timeoutSeconds: 110
            );

            if (! empty($result['ok']) && is_array($result['data'])) {
                $d = $result['data'];
                $headline = trim(strip_tags((string) ($d['headline'] ?? '')));
                $core = trim(strip_tags((string) ($d['core_message'] ?? '')));
                $l1 = trim(strip_tags((string) ($d['layer_1_overview'] ?? '')));

                if ($headline !== '' && $core !== '' && $l1 !== '') {
                    $palaceHighlights = $fallback['layer_2_palace_highlights'];
                    if (is_array($d['layer_2_palace_highlights'] ?? null)) {
                        foreach ($d['layer_2_palace_highlights'] as $pCode => $text) {
                            $clean = trim(strip_tags((string) $text));
                            if ($clean !== '') {
                                $palaceHighlights[(string) $pCode] = $clean;
                            }
                        }
                    }

                    $planRaw = is_array($d['layer_6_action_plan'] ?? null) ? $d['layer_6_action_plan'] : [];
                    $plan = [
                        'today' => trim(strip_tags((string) ($planRaw['today'] ?? ''))) ?: $fallback['layer_6_action_plan']['today'],
                        'this_week' => trim(strip_tags((string) ($planRaw['this_week'] ?? ''))) ?: $fallback['layer_6_action_plan']['this_week'],
                        'next_30_days' => trim(strip_tags((string) ($planRaw['next_30_days'] ?? ''))) ?: $fallback['layer_6_action_plan']['next_30_days'],
                        'long_term' => trim(strip_tags((string) ($planRaw['long_term'] ?? ''))) ?: $fallback['layer_6_action_plan']['long_term'],
                    ];

                    $strengths = is_array($d['strengths'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => trim(strip_tags((string) $s)), $d['strengths'])))
                        : $fallback['strengths'];
                    $watchOuts = is_array($d['watch_outs'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => trim(strip_tags((string) $s)), $d['watch_outs'])))
                        : $fallback['watch_outs'];

                    $candidate = array_merge($fallback, [
                        'headline' => $headline,
                        'core_message' => $core,
                        'layer_1_overview' => $l1,
                        'layer_2_palace_highlights' => $palaceHighlights,
                        'layer_3_combinations' => trim(strip_tags((string) ($d['layer_3_combinations'] ?? ''))) ?: $fallback['layer_3_combinations'],
                        'layer_4_love_family' => trim(strip_tags((string) ($d['layer_4_love_family'] ?? ''))) ?: $fallback['layer_4_love_family'],
                        'layer_5_life_stages' => trim(strip_tags((string) ($d['layer_5_life_stages'] ?? ''))) ?: $fallback['layer_5_life_stages'],
                        'layer_6_action_plan' => $plan,
                        'strengths' => array_slice($strengths !== [] ? $strengths : $fallback['strengths'], 0, 4),
                        'watch_outs' => array_slice($watchOuts !== [] ? $watchOuts : $fallback['watch_outs'], 0, 3),
                        'reflection_question' => trim(strip_tags((string) ($d['reflection_question'] ?? ''))) ?: $fallback['reflection_question'],
                        'ai_powered' => true,
                        'ai_model' => (string) ($result['model'] ?? $model ?? 'configured-ai'),
                        'ai_status' => 'completed',
                        'provenance' => 'ai_personalized',
                    ]);

                    if ($this->safetyGuard->validateAiOutput($candidate)['valid']) {
                        return $candidate;
                    }
                }
            }
        } catch (Throwable) {
            // Giữ nguyên bản luận giải 6 tầng chuẩn hóa từ kho dữ liệu đã thẩm định
        }

        return $fallback;
    }

    /**
     * Trợ Lý Tử Vi AI — Hỏi sâu khóa chặt vào 1 lá số cụ thể.
     *
     * @param  list<array{question: string, answer: string}>  $history
     * @return array{answer: string, ai_powered: bool, ai_model: ?string}
     */
    public function answerFollowup(TuViChart $chartModel, string $question, ?string $palaceCode = null, array $history = []): array
    {
        $chart = is_array($chartModel->chart_payload_json) ? $chartModel->chart_payload_json : [];
        $palace = $palaceCode ? ($chart['palaces_by_code'][$palaceCode] ?? null) : null;
        $menh = $chart['palaces_by_code']['menh'] ?? null;

        $targetLabel = $palace ? $palace['name'] : ($menh['name'] ?? 'lá số của bạn');
        $targetStars = $palace
            ? implode(', ', array_map(fn (array $s): string => $s['name'].' ('.$s['brightness'].')', $palace['major_stars'] ?? []))
            : implode(', ', array_map(fn (array $s): string => $s['name'].' ('.$s['brightness'].')', $menh['major_stars'] ?? []));
        if ($targetStars === '') {
            $targetStars = 'Vô Chính Diệu (xem chính tinh cung xung chiếu)';
        }

        $fallbackAnswer = sprintf(
            'Bám sát %s (%s) trên lá số %s của bạn: với câu hỏi "%s", dữ liệu cho thấy trọng tâm nằm ở cách bạn phát huy điểm mạnh tự nhiên và giữ sự đối thoại minh bạch trong thực tế. Lá số chỉ ra khuynh hướng và điểm cần lưu tâm theo quy tắc Nam Phái — quyết định chủ động vẫn luôn nằm ở lựa chọn của chính bạn.',
            $targetLabel,
            $targetStars,
            $chart['school_name'] ?? 'Nam Phái Việt Nam',
            $question
        );

        try {
            $policy = $this->governance->getPolicy();
            if (empty($policy['kill_switch']) && $this->provider->isAvailable()) {
                $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
                $histText = '';
                foreach (array_slice($history, -3) as $h) {
                    $histText .= "Hỏi: {$h['question']}\nĐáp: {$h['answer']}\n";
                }

                $systemPrompt = implode("\n", [
                    'Bạn là Trợ lý Tử Vi AI nhân văn của Mây Hẹn – Thiên Mệnh, trả lời câu hỏi đào sâu trên MỘT lá số đã tính toán.',
                    'QUY TẮC: Chỉ dùng dữ liệu trong <chart_context>; KHÔNG tự sửa lá số; KHÔNG dự đoán y khoa/tuổi thọ/ly hôn/ngoại tình; trả lời 3–5 câu rõ căn cứ và hữu ích.',
                    'Trả về DUY NHẤT JSON: {"answer":"Câu trả lời điềm đạm, sâu sắc"}',
                ]);

                $userPrompt = implode("\n", [
                    '<chart_context>',
                    'Trường phái: '.($chart['school_name'] ?? 'vn_nam_phai_v1'),
                    'Bản mệnh: '.($chart['birth']['nap_am']['name'] ?? '').' | Cục: '.($chart['summary']['cuc']['name'] ?? ''),
                    "Cung trọng tâm: {$targetLabel} — Chính tinh: {$targetStars}",
                    $histText !== '' ? "Hội thoại gần nhất:\n{$histText}" : '',
                    '</chart_context>',
                    "<user_question>{$question}</user_question>",
                ]);

                $res = $this->provider->completeJson(
                    systemPrompt: $systemPrompt,
                    userPrompt: $userPrompt,
                    model: $model,
                    temperature: 0.3,
                    maxTokens: 650,
                    timeoutSeconds: 100
                );

                if (! empty($res['ok']) && is_array($res['data'])) {
                    $ans = trim(strip_tags((string) ($res['data']['answer'] ?? '')));
                    if ($ans !== '' && $this->safetyGuard->validateAiOutput(['answer' => $ans])['valid']) {
                        return [
                            'answer' => $ans,
                            'ai_powered' => true,
                            'ai_model' => (string) ($res['model'] ?? $model ?? 'configured-ai'),
                        ];
                    }
                }
            }
        } catch (Throwable) {
            // Fallback chuẩn hóa
        }

        return [
            'answer' => $fallbackAnswer,
            'ai_powered' => false,
            'ai_model' => null,
        ];
    }

    /**
     * Luận giải Nhân Duyên Song Mệnh (10 chủ đề cặp đôi — KHÔNG chấm điểm % hợp tuổi hay % ly hôn).
     *
     * @param  array<string, mixed>  $synastryBasis
     * @param  array<string, string>  $coupleAnswers
     * @return array<string, mixed>
     */
    public function interpretSynastry(array $synastryBasis, array $coupleAnswers = [], bool $allowAi = true): array
    {
        $pA = $synastryBasis['person_a'];
        $pB = $synastryBasis['person_b'];
        $dyn = $synastryBasis['element_dynamic'];

        $fallbackTopics = [];
        foreach (TuViKnowledgeRuleEngine::SYNASTRY_TOPICS as $t) {
            $fallbackTopics[$t['key']] = [
                'label' => $t['label'],
                'insight' => sprintf(
                    'Đối chiếu nhịp %s giữa %s (%s) và %s (%s): trọng tâm là %s. Sự thấu hiểu bền lâu đến từ việc cả hai nói rõ kỳ vọng thay vì đoán ý nhau.',
                    $dyn['label'],
                    $pA['nickname'],
                    $pA['menh_stars'],
                    $pB['nickname'],
                    $pB['menh_stars'],
                    mb_strtolower($t['focus'], 'UTF-8')
                ),
            ];
        }

        $fallback = [
            'headline' => "Nhân Duyên Song Mệnh: {$pA['nickname']} & {$pB['nickname']} — {$dyn['label']}",
            'overview' => "{$dyn['summary']} Lá số của {$pA['nickname']} (Mệnh: {$pA['menh_stars']}, Phu Thê: {$pA['phu_the_stars']}) khi song hành cùng {$pB['nickname']} (Mệnh: {$pB['menh_stars']}, Phu Thê: {$pB['phu_the_stars']}) tạo nên bức tranh nhiều điểm bổ trợ nếu hai bạn giữ thói quen lắng nghe chân thành.",
            'shared_strengths' => [
                'Cả hai đều có điểm tựa để bổ trợ góc nhìn cho nhau trong kế hoạch sống chung.',
                'Cùng nhận diện sớm ngôn ngữ yêu thương giúp giảm hiểu lầm không đáng có.',
                'Sự chủ động trao đổi về gia đình hai bên sẽ tạo nền móng rất vững cho hôn nhân.',
            ],
            'differences_to_discuss' => [
                'Nhịp độ ra quyết định giữa một người thích chắc chắn và người kia thích linh hoạt.',
                'Cách thể hiện sự quan tâm khi một trong hai đang chịu áp lực công việc.',
                'Kế hoạch quản trị tài chính chung và ngân sách tổ ấm trong 3 năm đầu.',
            ],
            'pre_marriage_questions' => [
                'Khi bất đồng quan điểm, mỗi người cần khoảng lặng bao lâu trước khi ngồi xuống trao đổi lại?',
                'Hai bạn mong muốn nếp sống và cách thăm hỏi hai bên nội ngoại sau cưới ra sao?',
                'Nguyên tắc minh bạch tài chính chung – riêng nào khiến cả hai đều thấy thoải mái?',
            ],
            'topics' => $fallbackTopics,
            'ai_powered' => false,
            'ai_model' => null,
            'provenance' => 'mayhen_modern_guide',
        ];

        if (! $allowAi) {
            return $fallback;
        }

        try {
            $policy = $this->governance->getPolicy();
            if (empty($policy['kill_switch']) && $this->provider->isAvailable()) {
                $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
                $answersText = $coupleAnswers !== []
                    ? json_encode($coupleAnswers, JSON_UNESCAPED_UNICODE)
                    : 'Cặp đôi chưa bổ sung ghi chú thực tế.';

                $systemPrompt = implode("\n", [
                    'Bạn là Chuyên gia Tử Vi Nhân Duyên & Tư vấn Hôn nhân của Mây Hẹn – Nhân Duyên Song Mệnh.',
                    'QUY TẮC TUYỆT ĐỐI:',
                    '1. KHÔNG BAO GIỜ chấm % hợp nhau, % ly hôn hay phán "không hợp tuổi / không nên cưới".',
                    '2. Ưu tiên thông tin thực tế hai bạn chia sẻ hơn suy đoán từ sao.',
                    '3. Văn phong ấm áp, sâu sắc, thiết thực cho các cặp đôi Việt Nam chuẩn bị cưới hoặc xây dựng tổ ấm.',
                    '4. Trả về DUY NHẤT JSON: {"headline":"...","overview":"3-4 câu tổng quan","shared_strengths":["...","...","..."],"differences_to_discuss":["...","...","..."],"pre_marriage_questions":["Câu hỏi 1","Câu hỏi 2","Câu hỏi 3"]}',
                ]);

                $userPrompt = implode("\n", [
                    '<synastry_basis>',
                    "Người 1 ({$pA['nickname']}): Bản mệnh {$pA['nap_am']} ({$pA['element']}) | Mệnh: {$pA['menh_stars']} | Phu Thê: {$pA['phu_the_stars']} | Điền Trạch: {$pA['dien_trach_stars']}",
                    "Người 2 ({$pB['nickname']}): Bản mệnh {$pB['nap_am']} ({$pB['element']}) | Mệnh: {$pB['menh_stars']} | Phu Thê: {$pB['phu_the_stars']} | Điền Trạch: {$pB['dien_trach_stars']}",
                    "Động lực ngũ hành bản mệnh: {$dyn['label']} — {$dyn['summary']}",
                    '</synastry_basis>',
                    "<couple_real_context>{$answersText}</couple_real_context>",
                ]);

                $res = $this->provider->completeJson(
                    systemPrompt: $systemPrompt,
                    userPrompt: $userPrompt,
                    model: $model,
                    temperature: 0.35,
                    maxTokens: 1200,
                    timeoutSeconds: 105
                );

                if (! empty($res['ok']) && is_array($res['data'])) {
                    $d = $res['data'];
                    $candidate = array_merge($fallback, [
                        'headline' => trim(strip_tags((string) ($d['headline'] ?? ''))) ?: $fallback['headline'],
                        'overview' => trim(strip_tags((string) ($d['overview'] ?? ''))) ?: $fallback['overview'],
                        'shared_strengths' => is_array($d['shared_strengths'] ?? null) ? array_values(array_map(fn ($s) => trim(strip_tags((string) $s)), $d['shared_strengths'])) : $fallback['shared_strengths'],
                        'differences_to_discuss' => is_array($d['differences_to_discuss'] ?? null) ? array_values(array_map(fn ($s) => trim(strip_tags((string) $s)), $d['differences_to_discuss'])) : $fallback['differences_to_discuss'],
                        'pre_marriage_questions' => is_array($d['pre_marriage_questions'] ?? null) ? array_values(array_map(fn ($s) => trim(strip_tags((string) $s)), $d['pre_marriage_questions'])) : $fallback['pre_marriage_questions'],
                        'ai_powered' => true,
                        'ai_model' => (string) ($res['model'] ?? $model ?? 'configured-ai'),
                        'provenance' => 'ai_personalized',
                    ]);
                    if ($this->safetyGuard->validateAiOutput($candidate)['valid']) {
                        return $candidate;
                    }
                }
            }
        } catch (Throwable) {
            // Fallback chuẩn hóa
        }

        return $fallback;
    }

    /**
     * Bản luận giải 6 tầng chuẩn hóa hoàn toàn từ kho dữ liệu đã thẩm định (Zero Hallucination).
     *
     * @param  array<string, mixed>  $chart
     * @param  array<string, mixed>  $ruleAnalysis
     * @return array<string, mixed>
     */
    protected function buildGroundedFallback(array $chart, array $ruleAnalysis, string $focusTopic): array
    {
        if (($chart['status'] ?? '') === 'partial_no_birth_hour') {
            $napAm = $chart['birth']['nap_am'] ?? ['name' => '', 'element' => ''];
            $z = $chart['zodiac_overview'] ?? [];

            return [
                'headline' => "Nền tảng Bản Mệnh {$napAm['name']} — ".($z['animal'] ?? $chart['birth']['pillars']['year']['label']),
                'core_message' => ($z['traits'] ?? '').' Do chưa có giờ sinh, hệ thống chỉ trình bày các đặc điểm không phụ thuộc giờ sinh và giữ minh bạch thay vì suy đoán 12 cung.',
                'layer_1_overview' => "Năm sinh Âm lịch {$chart['birth']['pillars']['year']['label']} thuộc bản mệnh {$napAm['name']} (hành {$napAm['element']}). Để mở trọn bộ 12 Thiên Mệnh Bài và Đại Hạn, bạn có thể bổ sung giờ sinh hoặc chọn khoảng giờ sinh gần đúng.",
                'layer_2_palace_highlights' => [],
                'layer_3_combinations' => 'Chưa đủ dữ liệu giờ sinh để xác định Tam Phương Tứ Chính và Cách Cục 12 cung.',
                'layer_4_love_family' => (string) ($z['love'] ?? 'Sự thấu hiểu và trò chuyện chân thành luôn là gốc rễ bền vững của tình yêu.'),
                'layer_5_life_stages' => 'Đại Hạn và Tiểu Hạn phụ thuộc chính xác vào Cung Mệnh & Ngũ Hành Cục nên cần có giờ sinh để tính.',
                'layer_6_action_plan' => [
                    'today' => 'Hỏi lại gia đình hoặc xem giấy khai sinh để xác nhận khoảng giờ sinh gần đúng nhất.',
                    'this_week' => (string) ($z['advice'] ?? 'Giữ nhịp sống cân bằng và tập trung vào ưu tiên quan trọng nhất.'),
                    'next_30_days' => 'Khi có khoảng giờ sinh, quay lại chế độ So sánh lá số khả dĩ để khám phá 12 cung.',
                    'long_term' => 'Nuôi dưỡng các giá trị sống bền vững và mối quan hệ gia đình ấm áp.',
                ],
                'strengths' => [
                    "Khí chất bản mệnh {$napAm['name']} (hành {$napAm['element']}).",
                    (string) ($z['traits'] ?? 'Năng lực thích ứng và học hỏi bền bỉ.'),
                ],
                'watch_outs' => [
                    'Không nên tin các lời phán 12 cung khi chưa có giờ sinh xác thực.',
                ],
                'reflection_question' => 'Nếu nhìn lại 1 năm vừa qua, thói quen tốt nào đã mang lại cho bạn cảm giác bình an nhất?',
                'ecosystem_suggestions' => $this->buildEcosystemLinks($focusTopic),
                'ai_powered' => false,
                'ai_model' => null,
                'ai_status' => 'fallback',
                'provenance' => 'mayhen_modern_guide',
            ];
        }

        $sum = $chart['summary'];
        $digests = $ruleAnalysis['palace_digests'] ?? [];
        $patterns = $ruleAnalysis['verified_patterns'] ?? [];

        $menhD = $digests['menh'] ?? [];
        $phuTheD = $digests['phu_the'] ?? [];
        $quanD = $digests['quan_loc'] ?? [];
        $taiD = $digests['tai_bach'] ?? [];
        $dienD = $digests['dien_trach'] ?? [];
        $phucD = $digests['phuc_duc'] ?? [];

        $patternNames = array_map(fn (array $p): string => $p['name'], $patterns);
        $patternText = $patternNames !== []
            ? 'Lá số ghi nhận các cấu trúc đã xác minh theo Nam Phái: '.implode(', ', $patternNames).'. '.($patterns[0]['plain_explanation'] ?? '')
            : 'Các cung trên lá số vận hành cân bằng theo tương tác Tam Phương Tứ Chính và Tứ Hóa nguyên cục, không bị chi phối bởi một cách cục cực đoan đơn lẻ.';

        $dh = $sum['current_dai_han'] ?? null;
        $ln = $sum['current_luu_nien'] ?? null;

        return [
            'headline' => sprintf(
                'Thiên Bàn %s • %s — Mệnh tại %s (%s)',
                $sum['nap_am']['name'],
                $sum['cuc']['name'],
                $sum['menh_branch_name'],
                $menhD['stars_headline'] ?? ''
            ),
            'core_message' => sprintf(
                'Bản mệnh %s phối hợp %s, Cung Mệnh an tại %s (%s) và Cung Thân cư %s. %s',
                $sum['nap_am']['name'],
                $sum['cuc']['name'],
                $sum['menh_branch_name'],
                $menhD['stars_headline'] ?? '',
                $sum['than_palace_name'],
                $menhD['lead_star_insight'] ?? ''
            ),
            'layer_1_overview' => sprintf(
                'Đương số mang bản mệnh %s (hành %s), %s, %s. Trục Mệnh tại %s phản ánh tư chất nền tảng: %s Cung Thân đồng cung %s cho thấy từ giai đoạn trưởng thành trở đi, bạn ngày càng đặt trọng tâm vào lĩnh vực của %s.',
                $sum['nap_am']['name'],
                $sum['nap_am']['element'],
                $sum['cuc']['name'],
                $chart['yin_yang_gender_label'] ?? '',
                $sum['menh_branch_name'],
                $menhD['strength_hint'] ?? '',
                $sum['than_palace_name'],
                $sum['than_palace_name']
            ),
            'layer_2_palace_highlights' => [
                'menh' => ($menhD['stars_headline'] ?? '').' — '.($menhD['lead_star_insight'] ?? ''),
                'phu_the' => ($phuTheD['stars_headline'] ?? '').' — '.($phuTheD['lead_star_insight'] ?? '').' '.($phuTheD['modern_meaning'] ?? ''),
                'quan_loc' => ($quanD['stars_headline'] ?? '').' — '.($quanD['lead_star_insight'] ?? ''),
                'tai_bach' => ($taiD['stars_headline'] ?? '').' — '.($taiD['lead_star_insight'] ?? ''),
                'dien_trach' => ($dienD['stars_headline'] ?? '').' — '.($dienD['modern_meaning'] ?? ''),
                'phuc_duc' => ($phucD['stars_headline'] ?? '').' — '.($phucD['modern_meaning'] ?? ''),
            ],
            'layer_3_combinations' => $patternText.' Tứ Hóa nguyên cục kích hoạt Hóa Lộc tại '.($sum['tu_hoa']['loc']['palace_name'] ?? 'lá số').' và Hóa Khoa tại '.($sum['tu_hoa']['khoa']['palace_name'] ?? 'lá số').', tạo điểm sáng về cơ hội và trí tuệ giải quyết vấn đề.',
            'layer_4_love_family' => sprintf(
                'Xét Cung Phu Thê tại %s (%s) phối hợp Cung Phúc Đức và Điền Trạch: %s Điều quan trọng nhất để tình yêu và hôn nhân bền vững là giữ nhịp trò chuyện cởi mở, thống nhất rõ kỳ vọng giữa hai người và tôn trọng văn hóa gia đình hai bên.',
                $phuTheD['branch_name'] ?? '',
                $phuTheD['stars_headline'] ?? '',
                $phuTheD['lead_star_insight'] ?? ''
            ),
            'layer_5_life_stages' => sprintf(
                'Ở tuổi %d (năm %d — %s), Đại Hạn 10 năm (%d–%d tuổi) đi qua %s; Tiểu Hạn tại %s và Lưu Thái Tuế đóng tại %s. Đây là giai đoạn phù hợp để tập trung củng cố các chủ đề trọng tâm của %s bằng kế hoạch thực tế.',
                $chart['age_mu'] ?? 1,
                $chart['viewed_year'] ?? now()->year,
                $ln['can_chi'] ?? '',
                $dh['age_start'] ?? 0,
                $dh['age_end'] ?? 0,
                $dh['palace_name'] ?? '',
                $sum['current_tieu_han']['palace_name'] ?? '',
                $ln['palace_name'] ?? '',
                $dh['palace_name'] ?? 'Cung Mệnh'
            ),
            'layer_6_action_plan' => [
                'today' => 'Mở xem 3 cung bạn quan tâm nhất trên bộ 12 Thiên Mệnh Bài và ghi lại 1 điều khiến bạn thấy đúng với trải nghiệm thực tế.',
                'this_week' => 'Dành 30 phút trò chuyện thẳng thắn, ấm áp với người thương hoặc người thân về một ưu tiên chung trong thời gian tới.',
                'next_30_days' => 'Chọn 1 điểm mạnh nổi bật từ Cung Mệnh & Quan Lộc để biến thành thói quen hành động đều đặn mỗi tuần.',
                'long_term' => 'Bám sát chủ đề Đại Hạn hiện tại tại '.($dh['palace_name'] ?? 'lá số').', xây dựng vùng đệm tài chính và nền tảng tổ ấm vững vàng.',
            ],
            'strengths' => [
                $menhD['strength_hint'] ?? 'Tư chất ổn định, có năng lực tự nhận thức tốt.',
                'Điểm sáng Hóa Lộc tại '.($sum['tu_hoa']['loc']['palace_name'] ?? 'lá số').' giúp mở rộng nguồn lực thuận lợi.',
                'Cung Thân cư '.$sum['than_palace_name'].' tạo định hướng rõ ràng cho chặng đường trưởng thành.',
            ],
            'watch_outs' => [
                $menhD['watch_out_hint'] ?? 'Tránh ôm đồm quá nhiều mục tiêu cùng lúc.',
                'Chú ý giữ sự điềm đạm và minh bạch ở lĩnh vực '.($sum['tu_hoa']['ky']['palace_name'] ?? 'có Hóa Kỵ').' thay vì suy diễn trong im lặng.',
            ],
            'reflection_question' => 'Giữa mong muốn của bản thân và kỳ vọng của những người xung quanh, đâu là giá trị cốt lõi bạn muốn ưu tiên gìn giữ trong 3 năm tới?',
            'ecosystem_suggestions' => $this->buildEcosystemLinks($focusTopic),
            'ai_powered' => false,
            'ai_model' => null,
            'ai_status' => 'fallback',
            'provenance' => 'mayhen_modern_guide',
        ];
    }

    /**
     * @param  array<string, mixed>  $chart
     * @param  array<string, mixed>  $ruleAnalysis
     */
    protected function buildAnonymousChartBrief(array $chart, array $ruleAnalysis, string $focusTopic): string
    {
        $sum = $chart['summary'];
        $lines = [
            '<verified_tuvi_chart>',
            'Trường phái: '.$chart['school_name'].' ('.$chart['rule_version'].')',
            'Chủ đề trọng tâm người dùng chọn: '.$focusTopic,
            'Âm dương giới tính tính vận: '.$chart['yin_yang_gender_label'],
            'Bản mệnh Nạp Âm: '.$sum['nap_am']['name'].' ('.$sum['nap_am']['element'].') | Cục: '.$sum['cuc']['name'],
            'Cung Mệnh: '.$sum['menh_branch_name'].' | Cung Thân cư: '.$sum['than_palace_name'].' ('.$sum['than_branch_name'].')',
            'Tuần tại: '.implode(', ', $sum['tuan_branches']).' | Triệt tại: '.implode(', ', $sum['triet_branches']),
            '12 CUNG & CHÍNH TINH:',
        ];

        foreach ($chart['palaces_by_code'] as $p) {
            $majors = $p['major_stars'] !== []
                ? implode(', ', array_map(fn (array $s): string => $s['name'].'('.$s['brightness'].')', $p['major_stars']))
                : 'Vô Chính Diệu';
            $tuHoa = $p['tu_hoa'] !== []
                ? ' | Tứ Hóa: '.implode(', ', array_map(fn (array $h): string => $h['name'].'('.$h['attached_star_name'].')', $p['tu_hoa']))
                : '';
            $aux = implode(', ', array_slice(array_map(fn (array $a): string => $a['name'], $p['aux_stars']), 0, 8));
            $lines[] = "- {$p['name']} ({$p['can_chi']}".($p['is_than'] ? ', THÂN' : '')."): {$majors}{$tuHoa} | Phụ tinh nổi bật: {$aux}";
        }

        $patterns = $ruleAnalysis['verified_patterns'] ?? [];
        if ($patterns !== []) {
            $lines[] = 'CÁCH CỤC ĐÃ XÁC MINH BẰNG RULE:';
            foreach ($patterns as $pt) {
                $lines[] = "- {$pt['name']}: {$pt['evidence']}";
            }
        } else {
            $lines[] = 'CÁCH CỤC ĐÃ XÁC MINH BẰNG RULE: Không có cách cục đặc thù nổi trội; luận theo tương tác Tam Phương Tứ Chính.';
        }

        $dh = $sum['current_dai_han'];
        $ln = $sum['current_luu_nien'];
        $lines[] = "VẬN HẠN NĂM {$chart['viewed_year']} (tuổi mụ {$chart['age_mu']}): Đại Hạn ({$dh['age_start']}-{$dh['age_end']}) tại {$dh['palace_name']}; Tiểu Hạn tại {$sum['current_tieu_han']['palace_name']}; Lưu Thái Tuế tại {$ln['palace_name']}.";
        $lines[] = '</verified_tuvi_chart>';

        return implode("\n", $lines);
    }

    /**
     * @return list<array{title: string, description: string, url: string, cta: string}>
     */
    protected function buildEcosystemLinks(string $focusTopic): array
    {
        return match ($focusTopic) {
            'tinh_yeu', 'hon_nhan' => [
                [
                    'title' => 'Nhân Duyên Song Mệnh — Khám Phá Hai Lá Số',
                    'description' => 'Soi chiếu 10 chủ đề tình yêu, giao tiếp và chuẩn bị hôn nhân cùng người thương.',
                    'url' => route('tuvi.synastry.index'),
                    'cta' => 'Mở Song Mệnh',
                ],
                [
                    'title' => 'Kho Mẫu Thiệp Cưới Mây Hẹn',
                    'description' => 'Khi hai bạn sẵn sàng cho ngày trọng đại, khám phá bộ sưu tập thiệp cưới tinh tế.',
                    'url' => route('templates.index'),
                    'cta' => 'Xem Mẫu Thiệp',
                ],
            ],
            'gia_dinh', 'to_am' => [
                [
                    'title' => 'Gia Phả Mây Hẹn — Lưu Giữ Cội Nguồn',
                    'description' => 'Ghi chép phả hệ nhiều thế hệ và ngày giỗ chạp trang trọng cho đại gia đình.',
                    'url' => route('family_tree.index'),
                    'cta' => 'Khám Phá Gia Phả',
                ],
                [
                    'title' => 'Cẩm Nang Cưới Hỏi & Đời Sống Gia Đình',
                    'description' => 'Những chia sẻ sâu lắng về văn hóa gia đình và nghệ thuật giữ hòa khí hai bên.',
                    'url' => route('blog.index'),
                    'cta' => 'Đọc Cẩm Nang',
                ],
            ],
            default => [
                [
                    'title' => 'Mây Hẹn Dịch Tâm — Gieo Quẻ Kinh Dịch',
                    'description' => 'Chiêm nghiệm thêm một quyết định cụ thể trước mắt qua 64 quẻ Chu Dịch chuẩn cổ học.',
                    'url' => route('iching.index'),
                    'cta' => 'Gieo Quẻ Kinh Dịch',
                ],
                [
                    'title' => 'Nhân Duyên Song Mệnh Dành Cho Cặp Đôi',
                    'description' => 'Khám phá sự đồng điệu và cách thấu hiểu lẫn nhau giữa hai lá số.',
                    'url' => route('tuvi.synastry.index'),
                    'cta' => 'Khám Phá Song Mệnh',
                ],
            ],
        };
    }
}
