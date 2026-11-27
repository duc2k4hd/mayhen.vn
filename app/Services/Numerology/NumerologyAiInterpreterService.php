<?php

declare(strict_types=1);

namespace App\Services\Numerology;

use App\Models\NumerologyReading;
use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\Contracts\LlmProviderInterface;
use App\Services\Intelligence\ScopedToolAiGuardService;
use Throwable;

/**
 * Dịch vụ AI Luận Giải Thần Số Học 6 Tầng Có Căn Cứ & Tình Duyên 10 Chủ Đề.
 *
 * - Chỉ gửi dữ liệu con số ẩn danh (KHÔNG gửi họ tên thật, email hay SĐT sang nhà cung cấp AI).
 * - Hậu kiểm bắt buộc qua NumerologySafetyGuardService & ScopedToolAiGuardService.
 * - Khi AI tắt hoặc quá tải, trả về bản luận giải 6 tầng chuẩn hóa hoàn chỉnh từ kho tri thức đã thẩm định.
 */
class NumerologyAiInterpreterService
{
    protected NumerologySafetyGuardService $safetyGuard;

    protected ScopedToolAiGuardService $scopedGuard;

    public function __construct(
        protected LlmProviderInterface $provider,
        protected AiGovernanceService $governance,
        ?NumerologySafetyGuardService $safetyGuard = null,
        ?ScopedToolAiGuardService $scopedGuard = null
    ) {
        $this->safetyGuard = $safetyGuard ?? new NumerologySafetyGuardService;
        $this->scopedGuard = $scopedGuard ?? app(ScopedToolAiGuardService::class);
    }

    /**
     * Luận giải 6 tầng cho hồ sơ cá nhân.
     *
     * @param  array<string, mixed>  $calc
     * @param  array<string, mixed>  $enriched
     * @return array<string, mixed>
     */
    public function interpretProfile(
        array $calc,
        array $enriched,
        string $focusTopic = 'toan_bo',
        string $userContext = '',
        bool $allowAi = true
    ): array {
        $fallback = $this->buildGroundedProfileFallback($calc, $enriched, $focusTopic, $userContext);

        if (! $allowAi) {
            return $fallback;
        }

        try {
            $policy = $this->governance->getPolicy();
            if (! empty($policy['kill_switch']) || ! $this->provider->isAvailable()) {
                return $fallback;
            }

            $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
            $brief = $this->buildAnonymousProfileBrief($calc, $enriched, $focusTopic, $userContext);

            $systemPrompt = implode("\n", [
                'Bạn là Chuyên gia Thần Số Học biên tập theo phong cách tạp chí cao cấp Quiet Luxury của Mây Hẹn – Dấu Ấn Con Số.',
                'NGUYÊN TẮC BẮT BUỘC:',
                '1. CHỈ sử dụng đúng các con số đã tính trong <verified_numerology_profile>. TUYỆT ĐỐI không tự sửa số, không bịa chỉ số còn thiếu (nếu chưa có họ tên thì nói rõ giới hạn).',
                '2. VĂN PHONG: Điềm đạm, sâu sắc, cụ thể, có dẫn chứng rõ từ tổ hợp con số, không lặp mẫu, không dùng emoji. CẤM TUYỆT ĐỐI các câu văn mẫu AI như "Vũ trụ đang gửi đến bạn một thông điệp", "Bạn sinh ra để làm những điều phi thường", "Bạn vừa mạnh mẽ vừa nhạy cảm", "Bạn mang nguồn năng lượng vô cùng đặc biệt".',
                '3. AN TOÀN: CẤM phán đoán bệnh tật, tuổi thọ, ly hôn, ngoại tình, đầu tư sinh lời, nợ nghiệp hù dọa, đổi tên đổi mệnh hay chấm % hợp tuổi.',
                '4. ĐỘ SÂU: Viết đầy đặn, phân tích rõ ràng từng khía cạnh để người đọc thực sự thấu hiểu bản thân và ứng dụng được ngay.',
                '5. Trả về DUY NHẤT 1 JSON hợp lệ theo schema:',
                '{"headline":"Tiêu đề ngắn gọn đậm chất biên tập","summary":"3-4 câu tổng quan sắc nét gắn đúng tổ hợp chỉ số","layer_1_signature":"Tầng 1 (5-6 câu): Bản sắc cốt lõi & cách Đường Đời + Ngày Sinh định hình tư duy, nhịp sống và bài học lớn","layer_2_self_understanding":"Tầng 2 (5-6 câu): Phân tích sâu sự tương tác hoặc giằng co giữa Đường Đời, Sứ Mệnh, Linh Hồn, Nhân Cách và Thái Độ","layer_3_potential":"Tầng 3 (5-6 câu): Năng lực nổi trội, phong cách làm việc, định hướng nghề nghiệp, quản trị tài chính và thói quen nên rèn","layer_4_love_family":"Tầng 4 (5-6 câu): Nhu cầu tình cảm, ngôn ngữ yêu thương, giao tiếp khi bất đồng, hôn nhân và gắn kết gia đình","layer_5_life_journey":"Tầng 5 (5-6 câu): Nhìn xuyên suốt 3 Giai đoạn cuộc đời, 4 Đỉnh cao Kim Tự Tháp, 4 Thử thách và trọng tâm Năm cá nhân đang xem","strengths_list":["Thế mạnh cụ thể 1","Thế mạnh cụ thể 2","Thế mạnh cụ thể 3","Thế mạnh cụ thể 4"],"watch_outs":["Điểm cần lưu tâm 1","Điểm cần lưu tâm 2","Điểm cần lưu tâm 3"],"career_directions":["Định hướng nghề nghiệp / vai trò 1","Định hướng nghề nghiệp / vai trò 2","Định hướng nghề nghiệp / vai trò 3"],"year_aspects":{"career":"2-3 câu về sự nghiệp năm nay","finance":"2-3 câu về tài chính năm nay","love":"2-3 câu về tình cảm/gia đạo năm nay","growth":"2-3 câu về học hỏi/nội lực năm nay"},"layer_6_plan":{"actions":["Hành động 1","Hành động 2","Hành động 3"],"goal_7_days":"1 mục tiêu 7 ngày cụ thể","goal_30_days":"1 mục tiêu 30 ngày cụ thể","couple_family_prompt":"1 gợi ý trao đổi chân thành với người yêu hoặc gia đình"},"reflection_question":"1 câu hỏi tự nhìn lại sâu sắc"}',
            ]);

            $res = $this->provider->completeJson(
                systemPrompt: $systemPrompt,
                userPrompt: $brief,
                model: $model,
                temperature: 0.32,
                maxTokens: 3600,
                timeoutSeconds: 110
            );

            if (! empty($res['ok']) && is_array($res['data'])) {
                $d = $res['data'];
                $headline = trim(strip_tags((string) ($d['headline'] ?? '')));
                $summary = trim(strip_tags((string) ($d['summary'] ?? '')));
                $l1 = trim(strip_tags((string) ($d['layer_1_signature'] ?? '')));

                if ($headline !== '' && $summary !== '' && $l1 !== '') {
                    $planRaw = is_array($d['layer_6_plan'] ?? null) ? $d['layer_6_plan'] : [];
                    $actions = is_array($planRaw['actions'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => trim(strip_tags((string) $s)), $planRaw['actions'])))
                        : $fallback['layer_6_plan']['actions'];
                    $strengthsList = is_array($d['strengths_list'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => trim(strip_tags((string) $s)), $d['strengths_list'])))
                        : $fallback['strengths_list'];
                    $watchOuts = is_array($d['watch_outs'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => trim(strip_tags((string) $s)), $d['watch_outs'])))
                        : $fallback['watch_outs'];
                    $careerDirs = is_array($d['career_directions'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => trim(strip_tags((string) $s)), $d['career_directions'])))
                        : $fallback['career_directions'];
                    $yearAspectsRaw = is_array($d['year_aspects'] ?? null) ? $d['year_aspects'] : [];
                    $yearAspects = [
                        'career' => trim(strip_tags((string) ($yearAspectsRaw['career'] ?? ''))) ?: $fallback['year_aspects']['career'],
                        'finance' => trim(strip_tags((string) ($yearAspectsRaw['finance'] ?? ''))) ?: $fallback['year_aspects']['finance'],
                        'love' => trim(strip_tags((string) ($yearAspectsRaw['love'] ?? ''))) ?: $fallback['year_aspects']['love'],
                        'growth' => trim(strip_tags((string) ($yearAspectsRaw['growth'] ?? ''))) ?: $fallback['year_aspects']['growth'],
                    ];

                    $candidate = array_merge($fallback, [
                        'headline' => $headline,
                        'summary' => $summary,
                        'layer_1_signature' => $l1,
                        'layer_2_self_understanding' => trim(strip_tags((string) ($d['layer_2_self_understanding'] ?? ''))) ?: $fallback['layer_2_self_understanding'],
                        'layer_3_potential' => trim(strip_tags((string) ($d['layer_3_potential'] ?? ''))) ?: $fallback['layer_3_potential'],
                        'layer_4_love_family' => trim(strip_tags((string) ($d['layer_4_love_family'] ?? ''))) ?: $fallback['layer_4_love_family'],
                        'layer_5_life_journey' => trim(strip_tags((string) ($d['layer_5_life_journey'] ?? ''))) ?: $fallback['layer_5_life_journey'],
                        'strengths_list' => array_slice($strengthsList !== [] ? $strengthsList : $fallback['strengths_list'], 0, 5),
                        'career_directions' => array_slice($careerDirs !== [] ? $careerDirs : $fallback['career_directions'], 0, 5),
                        'year_aspects' => $yearAspects,
                        'layer_6_plan' => [
                            'actions' => array_slice($actions !== [] ? $actions : $fallback['layer_6_plan']['actions'], 0, 3),
                            'goal_7_days' => trim(strip_tags((string) ($planRaw['goal_7_days'] ?? ''))) ?: $fallback['layer_6_plan']['goal_7_days'],
                            'goal_30_days' => trim(strip_tags((string) ($planRaw['goal_30_days'] ?? ''))) ?: $fallback['layer_6_plan']['goal_30_days'],
                            'couple_family_prompt' => trim(strip_tags((string) ($planRaw['couple_family_prompt'] ?? ''))) ?: $fallback['layer_6_plan']['couple_family_prompt'],
                        ],
                        'watch_outs' => array_slice($watchOuts !== [] ? $watchOuts : $fallback['watch_outs'], 0, 4),
                        'reflection_question' => trim(strip_tags((string) ($d['reflection_question'] ?? ''))) ?: $fallback['reflection_question'],
                        'ai_powered' => true,
                        'ai_model' => (string) ($res['model'] ?? $model ?? 'configured-ai'),
                        'ai_status' => 'completed',
                        'provenance' => 'ai_personalized',
                    ]);

                    if ($this->safetyGuard->validateAiOutput($candidate)['valid']) {
                        return $candidate;
                    }
                }
            }
        } catch (Throwable) {
            // Fallback chuẩn hóa từ kho tri thức đã kiểm định
        }

        return $fallback;
    }

    /**
     * Trợ lý Thần Số Học AI — Hỏi sâu khóa chặt vào 1 hồ sơ đã tính toán (Zero-Trust Gate).
     *
     * @param  list<array{question: string, answer: string}>  $history
     * @return array{answer: string, ai_powered: bool, ai_model: ?string, blocked?: bool, code?: ?string}
     */
    public function answerFollowup(NumerologyReading $reading, string $question, array $history = []): array
    {
        $calc = is_array($reading->calculation_payload_json) ? $reading->calculation_payload_json : [];
        $lp = $calc['core_numbers']['life_path']['display'] ?? '—';
        $exp = $calc['core_numbers']['expression']['display'] ?? 'chưa có họ tên';
        $soul = $calc['core_numbers']['soul_urge']['display'] ?? 'chưa có họ tên';
        $py = $calc['personal_cycles']['personal_year']['value'] ?? '—';

        $groundedTerms = $this->scopedGuard->extractGroundedTerms([
            $calc['method_name'] ?? '',
            "Đường đời {$lp}",
            "Sứ mệnh {$exp}",
            "Linh hồn {$soul}",
            "Năm cá nhân {$py}",
        ]);
        $inputGuard = $this->scopedGuard->inspectScopedQuestion($question, 'numerology', $groundedTerms, 320);
        if (! $inputGuard['allowed']) {
            return [
                'answer' => (string) ($inputGuard['message'] ?? 'Câu hỏi nằm ngoài phạm vi hồ sơ Thần Số Học hiện tại.'),
                'ai_powered' => false,
                'ai_model' => null,
                'blocked' => true,
                'code' => $inputGuard['code'],
            ];
        }

        $cleanQuestion = $inputGuard['clean_question'];
        $fallbackAnswer = sprintf(
            'Đối chiếu trên hồ sơ %s của bạn (Đường đời %s, Sứ mệnh %s, Linh hồn %s, Năm cá nhân %s): với câu hỏi "%s", trọng tâm nằm ở cách bạn quan sát sự khác biệt giữa các chỉ số cốt lõi và biến hiểu biết thành một thay đổi nhỏ đo được trong đời sống thực tế.',
            $calc['method_name'] ?? 'Pythagorean Hans Decoz',
            $lp,
            $exp,
            $soul,
            $py,
            $cleanQuestion
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
                    'Bạn là Trợ lý Thần Số Học điềm đạm của Mây Hẹn – Dấu Ấn Con Số.',
                    $this->scopedGuard->buildZeroTrustSystemPreamble('profile_context'),
                    'QUY TẮC: Khóa chặt các chỉ số trong <profile_context>, KHÔNG sửa phương pháp hay con số, KHÔNG bịa chỉ số thiếu, KHÔNG dùng văn mẫu AI, KHÔNG chấm % hay tiên đoán y khoa/ly hôn.',
                    'Trả lời 4–6 câu súc tích, rõ căn cứ. Trả về DUY NHẤT JSON: {"answer":"..."}',
                ]);

                $verifiedContext = implode("\n", [
                    '<profile_context>',
                    'Phương pháp: '.($calc['method_name'] ?? '').' (v'.($calc['method_version'] ?? '1.0.0').')',
                    "Đường đời: {$lp} | Sứ mệnh: {$exp} | Linh hồn: {$soul} | Năm cá nhân: {$py}",
                    'So sánh phương pháp: '.($calc['core_numbers']['life_path_comparison']['explanation'] ?? ''),
                    $histText !== '' ? "Hội thoại gần nhất:\n{$histText}" : '',
                    '</profile_context>',
                ]);

                $res = $this->provider->completeJson(
                    systemPrompt: $systemPrompt,
                    userPrompt: $verifiedContext."\n".$inputGuard['wrapped_question'],
                    model: $model,
                    temperature: 0.28,
                    maxTokens: 650,
                    timeoutSeconds: 100
                );

                if (! empty($res['ok']) && is_array($res['data'])) {
                    $verify = $this->scopedGuard->verifyScopedOutput(
                        (string) ($res['data']['answer'] ?? ''),
                        $verifiedContext,
                        'numerology',
                        $groundedTerms
                    );
                    if ($verify['safe'] && $this->safetyGuard->validateAiOutput(['answer' => $verify['clean_answer']])['valid']) {
                        return [
                            'answer' => $verify['clean_answer'],
                            'ai_powered' => true,
                            'ai_model' => (string) ($res['model'] ?? $model ?? 'configured-ai'),
                            'blocked' => false,
                        ];
                    }
                }
            }
        } catch (Throwable) {
            // Giữ fallback chuẩn hóa
        }

        return ['answer' => $fallbackAnswer, 'ai_powered' => false, 'ai_model' => null, 'blocked' => false];
    }

    /**
     * Luận giải Thần Số Học Tình Duyên 10 chủ đề — KHÔNG chấm % hợp nhau, KHÔNG phán định chia ly.
     *
     * @param  array<string, mixed>  $comparison
     * @param  array<string, string>  $coupleContext
     * @return array<string, mixed>
     */
    public function interpretRelationship(array $comparison, array $coupleContext = [], bool $allowAi = true): array
    {
        $pA = $comparison['person_a'];
        $pB = $comparison['person_b'];
        $pair = $comparison['base_pair'];

        $topicsOut = [];
        foreach (NumerologyKnowledgeService::LOVE_TOPICS as $t) {
            $topicsOut[$t['key']] = [
                'label' => $t['label'],
                'insight' => sprintf(
                    'Trong khía cạnh %s giữa %s (Đường đời %s) và %s (Đường đời %s): %s. Nhịp cặp số %s gợi mở hướng trọng tâm: %s',
                    mb_strtolower($t['label'], 'UTF-8'),
                    $pA['display_name'],
                    $pA['life_path_display'],
                    $pB['display_name'],
                    $pB['life_path_display'],
                    $t['focus'],
                    $pair['dynamic'],
                    $pair['nurture']
                ),
            ];
        }

        $fallback = [
            'headline' => "{$pA['display_name']} ({$pA['life_path_display']}) & {$pB['display_name']} ({$pB['life_path_display']}) — {$pair['dynamic']}",
            'overview' => "{$pair['harmony']} {$pair['difference']} Đây là khung gợi ý đối thoại dựa trên số nền {$pA['life_path_root']} và {$pB['life_path_root']}, không phải phán định cố định về tình cảm của hai bạn.",
            'things_to_nurture' => [
                $pair['nurture'],
                'Đặt lịch trò chuyện chân thành không thiết bị mỗi tuần để giữ mạch kết nối.',
                'Ghi nhận nỗ lực cụ thể của đối phương thay vì chỉ nhắc tới điểm chưa hài lòng.',
            ],
            'things_to_discuss' => [
                'Cách mỗi người cần được lắng nghe khi gặp áp lực công việc hoặc gia đình.',
                'Nguyên tắc minh bạch tài chính chung – riêng và mục tiêu tiết kiệm 3 năm tới.',
                'Cách ứng xử ấm áp và giữ ranh giới hợp lý với hai bên gia đình nội ngoại.',
            ],
            'weekly_action' => 'Tuần này, hai bạn hãy dành 25 phút cùng trả lời 5 câu hỏi trong mục “Câu chuyện hai chúng ta” và chọn mỗi người 1 lời hẹn thực tế cho 30 ngày tới.',
            'topics' => $topicsOut,
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
                $ctxText = $coupleContext !== []
                    ? json_encode($coupleContext, JSON_UNESCAPED_UNICODE)
                    : 'Cặp đôi chưa bổ sung bối cảnh tự nguyện.';

                $systemPrompt = implode("\n", [
                    'Bạn là Chuyên gia Thần Số Học Tình Duyên & Hôn nhân của Mây Hẹn – Dấu Ấn Con Số.',
                    'QUY TẮC TUYỆT ĐỐI:',
                    '1. CẤM chấm % hợp nhau, % kết hôn, % ly hôn, CẤM nói "không hợp số không nên cưới" hay "chắc chắn là định mệnh".',
                    '2. Giữ chính xác vai trò Người 1 và Người 2, tôn trọng dữ liệu thực tế cặp đôi chia sẻ hơn suy đoán từ con số.',
                    '3. Văn phong điềm đạm, sâu sắc, thiết thực, không dùng văn mẫu AI hay emoji.',
                    '4. Trả về DUY NHẤT JSON: {"headline":"...","overview":"3-4 câu tổng quan","things_to_nurture":["...","...","..."],"things_to_discuss":["...","...","..."],"weekly_action":"1 hành động cụ thể trong tuần"}',
                ]);

                $userPrompt = implode("\n", [
                    '<relationship_basis>',
                    "Người 1: Đường đời {$pA['life_path_display']} (nền {$pA['life_path_root']}, {$pA['life_path_title']}) | Sứ mệnh: ".($pA['expression_display'] ?? 'thiếu tên').' | Năm cá nhân: '.$pA['personal_year'],
                    "Người 2: Đường đời {$pB['life_path_display']} (nền {$pB['life_path_root']}, {$pB['life_path_title']}) | Sứ mệnh: ".($pB['expression_display'] ?? 'thiếu tên').' | Năm cá nhân: '.$pB['personal_year'],
                    "Động lực cặp số nền ({$comparison['pair_key']}): {$pair['dynamic']} — Đồng điệu: {$pair['harmony']} — Khác biệt: {$pair['difference']}",
                    'Giai đoạn mối quan hệ: '.$comparison['relationship_stage'],
                    '</relationship_basis>',
                    "<couple_real_context>{$ctxText}</couple_real_context>",
                ]);

                $res = $this->provider->completeJson(
                    systemPrompt: $systemPrompt,
                    userPrompt: $userPrompt,
                    model: $model,
                    temperature: 0.32,
                    maxTokens: 1200,
                    timeoutSeconds: 105
                );

                if (! empty($res['ok']) && is_array($res['data'])) {
                    $d = $res['data'];
                    $candidate = array_merge($fallback, [
                        'headline' => trim(strip_tags((string) ($d['headline'] ?? ''))) ?: $fallback['headline'],
                        'overview' => trim(strip_tags((string) ($d['overview'] ?? ''))) ?: $fallback['overview'],
                        'things_to_nurture' => is_array($d['things_to_nurture'] ?? null) ? array_values(array_map(fn ($s) => trim(strip_tags((string) $s)), $d['things_to_nurture'])) : $fallback['things_to_nurture'],
                        'things_to_discuss' => is_array($d['things_to_discuss'] ?? null) ? array_values(array_map(fn ($s) => trim(strip_tags((string) $s)), $d['things_to_discuss'])) : $fallback['things_to_discuss'],
                        'weekly_action' => trim(strip_tags((string) ($d['weekly_action'] ?? ''))) ?: $fallback['weekly_action'],
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
            // Giữ fallback
        }

        return $fallback;
    }

    /**
     * @param  array<string, mixed>  $calc
     * @param  array<string, mixed>  $enriched
     * @return array<string, mixed>
     */
    protected function buildGroundedProfileFallback(array $calc, array $enriched, string $focusTopic, string $userContext): array
    {
        $lp = $calc['core_numbers']['life_path'];
        $lpMeta = $enriched['indicator_meanings']['life_path'] ?? [];
        $expMeta = $enriched['indicator_meanings']['expression'] ?? null;
        $soulMeta = $enriched['indicator_meanings']['soul_urge'] ?? null;
        $persMeta = $enriched['indicator_meanings']['personality'] ?? null;
        $pyMeta = $enriched['personal_year_meaning'] ?? ['theme' => '', 'summary' => '', 'focus' => ''];
        $p1 = $enriched['pinnacles_enriched'][0] ?? null;

        $selfText = $expMeta && $soulMeta && $persMeta
            ? sprintf(
                'Đường đời %s (%s) đặt trục bài học dài hạn, trong khi Sứ mệnh %s (%s) định hình cách bạn hành động ra bên ngoài, Linh hồn %s phản ánh động lực sâu bên trong và Nhân cách %s tạo ấn tượng ban đầu. Nhận rõ chỗ đồng điệu lẫn khác biệt giữa bốn chỉ số này giúp bạn đỡ tự mâu thuẫn khi ra quyết định.',
                $lp['display'],
                $lpMeta['title'] ?? '',
                $calc['core_numbers']['expression']['display'],
                $expMeta['title'] ?? '',
                $calc['core_numbers']['soul_urge']['display'],
                $calc['core_numbers']['personality']['display']
            )
            : 'Hồ sơ hiện đang tính dựa trên Ngày sinh dương lịch nên chưa có các chỉ số từ họ tên khai sinh (Sứ mệnh, Linh hồn, Nhân cách). Bạn có thể bổ sung đầy đủ họ tên khai sinh để quan sát sự tương tác giữa nội tâm và biểu hiện bên ngoài.';

        $contextLine = $userContext !== '' ? ' Bối cảnh bạn chia sẻ đã được ghi nhận để soi chiếu ưu tiên thực tế.' : '';

        $strengthsList = array_values(array_filter([
            ! empty($lpMeta['strengths']) ? "Từ Đường Đời {$lp['display']}: {$lpMeta['strengths']}" : null,
            ! empty($expMeta['strengths']) ? "Từ Sứ Mệnh {$calc['core_numbers']['expression']['display']}: {$expMeta['strengths']}" : null,
            ! empty($soulMeta['strengths']) ? "Từ Linh Hồn {$calc['core_numbers']['soul_urge']['display']}: {$soulMeta['strengths']}" : null,
            ! empty($persMeta['strengths']) ? "Từ Nhân Cách {$calc['core_numbers']['personality']['display']}: {$persMeta['strengths']}" : null,
        ]));
        if ($strengthsList === []) {
            $strengthsList = ['Tư duy chủ động và khả năng tự điều chỉnh qua từng chặng phát triển.'];
        }

        $watchOutsList = array_values(array_filter([
            ! empty($lpMeta['watch_outs']) ? (string) $lpMeta['watch_outs'] : null,
            ! empty($expMeta['watch_outs']) ? "Khi thực thi mục tiêu (Sứ Mệnh {$calc['core_numbers']['expression']['display']}): {$expMeta['watch_outs']}" : null,
            ! empty($soulMeta['watch_outs']) ? "Về cân bằng nội tâm (Linh Hồn {$calc['core_numbers']['soul_urge']['display']}): {$soulMeta['watch_outs']}" : null,
        ]));
        if ($watchOutsList === []) {
            $watchOutsList = ['Giữ sự cân bằng giữa lý tưởng và nhịp sống thực tế.'];
        }

        $careerDirections = $enriched['career_domains']['top_roles'] ?? [
            'Môi trường phát huy rõ thế mạnh Đường Đời '.$lp['display'],
            'Vai trò kết hợp tốt giữa tư duy độc lập và phối hợp nhóm',
        ];
        $yearAspects = $enriched['year_aspects_default'] ?? [
            'career' => (string) ($pyMeta['summary'] ?? ''),
            'finance' => 'Giữ kỷ luật ngân sách và phân bổ nguồn lực hợp lý theo mục tiêu năm.',
            'love' => 'Ưu tiên đối thoại chân thành và tôn trọng nhịp sống của nhau.',
            'growth' => (string) ($pyMeta['focus'] ?? ''),
        ];

        return [
            'headline' => "Đường Đời {$lp['display']} — ".($lpMeta['title'] ?? 'Dấu Ấn Con Số'),
            'summary' => ($lpMeta['contextual_meaning'] ?? '').$contextLine,
            'layer_1_signature' => ($lpMeta['contextual_meaning'] ?? '').' '.($lpMeta['traditional_view'] ?? ''),
            'layer_2_self_understanding' => $selfText,
            'layer_3_potential' => ($lpMeta['work_meaning'] ?? '').' Thế mạnh tự nhiên: '.($lpMeta['strengths'] ?? ''),
            'layer_4_love_family' => ($lpMeta['love_meaning'] ?? '').' '.($lpMeta['marriage_meaning'] ?? '').' '.($lpMeta['family_meaning'] ?? ''),
            'layer_5_life_journey' => sprintf(
                'Năm cá nhân %d (%s): %s Đỉnh cao đầu tiên mang số %s (%s): %s',
                $calc['personal_cycles']['personal_year']['value'],
                $pyMeta['theme'] ?? '',
                $pyMeta['summary'] ?? '',
                $p1['display'] ?? '',
                $p1['age_range'] ?? '',
                $p1['meaning'] ?? ''
            ),
            'strengths_list' => $strengthsList,
            'career_directions' => $careerDirections,
            'year_aspects' => $yearAspects,
            'layer_6_plan' => [
                'actions' => array_merge(
                    $lpMeta['action_hints'] ?? [],
                    ['Viết lại 1 ưu tiên rõ ràng cho năm cá nhân '.($calc['personal_cycles']['personal_year']['value'] ?? '')]
                ),
                'goal_7_days' => 'Chọn 1 thói quen nhỏ phù hợp với nhịp Năm cá nhân hiện tại và duy trì liên tục 7 ngày.',
                'goal_30_days' => 'Theo dõi tiến độ mục tiêu chính trong Nhật ký phát triển và đánh giá lại sau 30 ngày.',
                'couple_family_prompt' => 'Chia sẻ với người thương hoặc gia đình một điều bạn cần được thấu hiểu hơn trong giai đoạn này.',
            ],
            'watch_outs' => $watchOutsList,
            'reflection_question' => (string) ($lpMeta['reflection_question'] ?? 'Thói quen nhỏ nào trong 30 ngày tới sẽ mang lại sự bình an rõ rệt nhất cho bạn?'),
            'limitations' => $calc['completeness_label'],
            'source_ref' => $calc['method_name'].' (v'.$calc['method_version'].')',
            'ai_powered' => false,
            'ai_model' => null,
            'ai_status' => 'fallback',
            'provenance' => 'verified_kb',
        ];
    }

    /**
     * @param  array<string, mixed>  $calc
     * @param  array<string, mixed>  $enriched
     */
    protected function buildAnonymousProfileBrief(array $calc, array $enriched, string $focusTopic, string $userContext): string
    {
        $core = $calc['core_numbers'];
        $ext = $calc['extended_numbers'];
        $py = $calc['personal_cycles']['personal_year'];

        $lines = [
            '<verified_numerology_profile>',
            "Phương pháp: {$calc['method_name']} ({$calc['method_id']} v{$calc['method_version']})",
            "Trạng thái dữ liệu: {$calc['completeness_label']}",
            "Đường Đời (Life Path): {$core['life_path']['display']} — ".($enriched['indicator_meanings']['life_path']['title'] ?? ''),
            "Ngày Sinh gốc: {$core['birthday']['raw_day']} (nền {$core['birthday']['root_display']})",
            "Thái Độ: {$ext['attitude']['display']}",
            'Sứ Mệnh (Expression): '.($core['expression']['display'] ?? 'chưa có họ tên'),
            'Linh Hồn (Soul Urge): '.($core['soul_urge']['display'] ?? 'chưa có họ tên'),
            'Nhân Cách (Personality): '.($core['personality']['display'] ?? 'chưa có họ tên'),
            'Trưởng Thành (Maturity): '.($ext['maturity']['display'] ?? 'chưa có họ tên'),
            "Năm cá nhân {$py['year']}: {$py['value']} — ".($enriched['personal_year_meaning']['theme'] ?? ''),
            '4 Đỉnh cao: '.implode(', ', array_map(fn (array $p): string => "P{$p['order']}={$p['display']} ({$p['age_range']})", $calc['pinnacles'])),
            '4 Thử thách: '.implode(', ', array_map(fn (array $c): string => "C{$c['order']}={$c['value']}", $calc['challenges'])),
            "Chủ đề ưu tiên: {$focusTopic}",
            $userContext !== '' ? "Hoàn cảnh thực tế người dùng chia sẻ: {$userContext}" : '',
            '</verified_numerology_profile>',
        ];

        return implode("\n", array_filter($lines));
    }
}
