<?php

declare(strict_types=1);

namespace App\Services\Numerology;

use App\Models\NumerologyReading;
use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\Contracts\LlmProviderInterface;
use App\Services\Intelligence\ScopedToolAiGuardService;
use Throwable;

/**
 * Dịch vụ AI Luận Giải Thần Số Học 2 Pha Siêu Chi Tiết & Hỏi Sâu Theo Từng Phần/Chỉ Số — Mây Hẹn.
 *
 * - Chỉ gửi dữ liệu con số ẩn danh (KHÔNG gửi họ tên thật, email hay SĐT sang nhà cung cấp AI).
 * - Pha 1: Luận giải sâu 6 tầng + cân bằng tâm thân (chia đoạn mạch lạc, dẫn chứng đủ bộ số).
 * - Pha 2: Luận giải cá nhân hóa riêng từng chỉ số cốt lõi/mở rộng, Biểu đồ 3×3 & Kim Tự Tháp.
 * - Hỏi Sâu AI khóa chặt từng chương (sec:*) hoặc từng chỉ số (ind:*) qua kiểm định kép Zero-Trust.
 */
class NumerologyAiInterpreterService
{
    /**
     * Danh mục các phần luận giải hợp lệ cho cơ chế "Bấm phần nào -> Hỏi sâu AI đúng phần đó"
     * (khóa chặt mã scope <= 48 ký tự để lưu trực tiếp vào numerology_followups.topic_code).
     *
     * @var array<string, array{scope_code: string, label: string, description: string, suggested_questions: list<string>}>
     */
    public const SECTION_DEFINITIONS = [
        'overview' => [
            'scope_code' => 'sec:overview',
            'label' => 'Dấu Ấn Tổng Quan & Trục Đường Đời',
            'description' => 'Phân tích tổng quan Đường Đời, đối chiếu Hans Decoz – David A. Phillips, Master Numbers và bức tranh bản sắc.',
            'suggested_questions' => [
                'Trục Đường Đời này đặt ra bài học lớn nhất gì cho giai đoạn hiện tại của mình?',
                'Sự khác nhau giữa cách tính Hans Decoz và David A. Phillips trên hồ sơ của mình nói lên điều gì?',
            ],
        ],
        'core_numbers' => [
            'scope_code' => 'sec:core_numbers',
            'label' => 'Bộ Chỉ Số Cốt Lõi & Mở Rộng',
            'description' => 'Phân tích tương quan Đường Đời, Sứ Mệnh, Linh Hồn, Nhân Cách, Ngày Sinh, Trưởng Thành, Cầu Nối và Số Thiếu.',
            'suggested_questions' => [
                'Các chỉ số cốt lõi và chỉ số mở rộng trong hồ sơ này đang bổ trợ hay giằng co nhau ở điểm nào?',
                'Số thiếu trong tên và Đam mê tiềm ẩn ảnh hưởng ra sao đến cách mình làm việc?',
            ],
        ],
        'birth_chart' => [
            'scope_code' => 'sec:birth_chart',
            'label' => 'Biểu Đồ Ngày Sinh 3×3 & 8 Đường Mũi Tên',
            'description' => 'Phân tích phân bố các chữ số trên 3 trục Thể chất – Tinh thần – Trí não và ý nghĩa mũi tên đầy/trống.',
            'suggested_questions' => [
                'Các ô nhiều số và ô trống trong Biểu đồ ngày sinh 3×3 của mình cần rèn thế nào để cân bằng?',
                'Những đường mũi tên hiện diện trên biểu đồ tạo thành lợi thế và điểm cần chú ý nào?',
            ],
        ],
        'self_understanding' => [
            'scope_code' => 'sec:self_understanding',
            'label' => 'Hiểu Bản Thân, Điểm Mạnh & Điểm Cần Cân Bằng',
            'description' => 'Đào sâu nội tâm, tương tác giữa Linh Hồn bên trong và Nhân Cách bên ngoài, nhịp cân bằng tâm-thân.',
            'suggested_questions' => [
                'Mình thường bị mất cân bằng khi nào và Chỉ số Cân Bằng gợi ý cách hạ nhiệt ra sao?',
                'Làm sao để sống đúng với Linh Hồn bên trong mà vẫn hài hòa với môi trường xung quanh?',
            ],
        ],
        'career_potential' => [
            'scope_code' => 'sec:career_potential',
            'label' => 'Thiên Hướng Nghề Nghiệp & Năng Lực Thực Thi',
            'description' => 'Phân tích 6 nhóm năng lực, môi trường làm việc phù hợp, phong cách lãnh đạo/chuyên môn và tài chính.',
            'suggested_questions' => [
                'Dựa trên Đường Đời và Sứ Mệnh này, mình phù hợp môi trường làm chuyên môn sâu hay điều phối đội nhóm?',
                'Điểm nghẽn nào khiến sự nghiệp hoặc tài chính của mình dễ chậm lại và cách tháo gỡ?',
            ],
        ],
        'love_family' => [
            'scope_code' => 'sec:love_family',
            'label' => 'Tình Yêu, Giao Tiếp, Hôn Nhân & Gia Đình',
            'description' => 'Phân tích nhu cầu cảm xúc, cách lắng nghe khi bất đồng, xây dựng tổ ấm và hòa hợp gia đình hai bên.',
            'suggested_questions' => [
                'Với bộ số này, mình cần rèn thói quen giao tiếp nào để tình cảm và hôn nhân bền vững hơn?',
                'Khi xảy ra hiểu lầm với người thương, mình hay phản ứng theo kiểu nào và nên điều chỉnh ra sao?',
            ],
        ],
        'life_journey' => [
            'scope_code' => 'sec:life_journey',
            'label' => '3 Giai Đoạn, 4 Đỉnh Cao Kim Tự Tháp & 4 Thử Thách',
            'description' => 'Phân tích hành trình qua các mốc tuổi, đỉnh cao hiện tại và thử thách song hành trên Kim Tự Tháp.',
            'suggested_questions' => [
                'Ở Đỉnh Cao và Thử Thách hiện tại, mình cần chuẩn bị kỹ năng gì để đón trọn cơ hội giai đoạn này?',
                'Giai đoạn cuộc đời mình đang đi qua đòi hỏi sự thay đổi thái độ sống như thế nào?',
            ],
        ],
        'personal_cycles' => [
            'scope_code' => 'sec:personal_cycles',
            'label' => 'Năm Cá Nhân, 12 Tháng & Chu Kỳ 9 Năm',
            'description' => 'Đọc nhịp Năm cá nhân hiện tại trên 4 khía cạnh và lộ trình năng lượng 12 tháng trong năm.',
            'suggested_questions' => [
                'Trong Năm cá nhân hiện tại, mình nên ưu tiên bứt phá mở mới hay củng cố nền tảng?',
                'Các tháng sắp tới trên lộ trình 12 tháng có điểm nhấn nào mình cần lưu tâm nhất?',
            ],
        ],
        'action_plan' => [
            'scope_code' => 'sec:action_plan',
            'label' => 'Kế Hoạch 7–30 Ngày & Thói Quen Thực Tế',
            'description' => 'Chuyển hóa insight con số thành mục tiêu 7 ngày, 30 ngày và thói quen đo lường được.',
            'suggested_questions' => [
                'Đâu là 1 thói quen ưu tiên nhất trong 30 ngày tới phù hợp với bộ số của mình?',
                'Nếu đang đứng trước ngã rẽ quan trọng, mình nên đối chiếu tiêu chí nào từ hồ sơ này?',
            ],
        ],
    ];

    /**
     * Ánh xạ các phạm vi từng chỉ số cụ thể để hỗ trợ nút "? Giải mã" & "✦ Hỏi sâu AI chỉ số này".
     *
     * @var array<string, string>
     */
    public const INDICATOR_LABELS = [
        'life_path' => 'Chỉ Số Đường Đời (Life Path)',
        'expression' => 'Chỉ Số Sứ Mệnh (Expression)',
        'soul_urge' => 'Chỉ Số Linh Hồn (Soul Urge)',
        'personality' => 'Chỉ Số Nhân Cách (Personality)',
        'birthday' => 'Chỉ Số Ngày Sinh (Birthday)',
        'maturity' => 'Chỉ Số Trưởng Thành (Maturity)',
        'attitude' => 'Chỉ Số Thái Độ (Attitude)',
        'balance' => 'Chỉ Số Cân Bằng (Balance)',
        'rational_thought' => 'Chỉ Số Tư Duy Lý Trí',
        'hidden_passion' => 'Đam Mê Tiềm Ẩn',
        'karmic_lessons' => 'Bài Học Rèn Luyện (Số Thiếu)',
        'subconscious_self' => 'Sức Mạnh Tiềm Thức',
        'bridges' => 'Chỉ Số Cầu Nối',
        'karmic_debts' => 'Chỉ Số Nợ Nghiệp & Master Number',
        'pinnacles' => '4 Đỉnh Cao Kim Tự Tháp',
        'challenges' => '4 Thử Thách Trưởng Thành',
    ];

    protected NumerologySafetyGuardService $safetyGuard;

    protected ScopedToolAiGuardService $scopedGuard;

    protected NumerologyKnowledgeService $knowledge;

    public function __construct(
        protected LlmProviderInterface $provider,
        protected AiGovernanceService $governance,
        ?NumerologySafetyGuardService $safetyGuard = null,
        ?ScopedToolAiGuardService $scopedGuard = null,
        ?NumerologyKnowledgeService $knowledge = null
    ) {
        $this->safetyGuard = $safetyGuard ?? new NumerologySafetyGuardService;
        $this->scopedGuard = $scopedGuard ?? app(ScopedToolAiGuardService::class);
        $this->knowledge = $knowledge ?? app(NumerologyKnowledgeService::class);
    }

    /**
     * Luận giải 2 pha siêu chi tiết cho hồ sơ cá nhân (ngân sách tối đa 275s trong cửa sổ 5 phút).
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

        $startedAt = microtime(true);
        $totalBudgetSeconds = 275;

        try {
            $policy = $this->governance->getPolicy();
            if (! empty($policy['kill_switch']) || ! $this->provider->isAvailable()) {
                return $fallback;
            }

            $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
            $brief = $this->buildAnonymousProfileBrief($calc, $enriched, $focusTopic, $userContext);

            $systemPrompt = implode("\n", [
                'Bạn là Đại Học Giả Thần Số Học Pythagorean & Nhân Số Học điềm đạm, sâu sắc theo phong cách Quiet Luxury của Mây Hẹn – Dấu Ấn Con Số.',
                'NGUYÊN TẮC BẮT BUỘC:',
                '1. CHỈ sử dụng đúng các con số, biểu đồ 3×3, mũi tên, 4 Đỉnh cao, 4 Thử thách, 3 Giai đoạn và Năm cá nhân đã tính trong <verified_numerology_profile>. TUYỆT ĐỐI không tự sửa số, không bịa chỉ số còn thiếu (nếu chưa có họ tên thì nói rõ giới hạn).',
                '2. VĂN PHONG & ĐỘ SÂU SIÊU CHI TIẾT: Viết phân tích sâu sắc, dẫn chứng rõ ràng tổ hợp các con số thật, chia nhiều đoạn mạch lạc bằng \\n\\n cho dễ đọc. CẤM TUYỆT ĐỐI các câu sáo rỗng AI như "Vũ trụ đang gửi đến bạn một thông điệp", "Bạn sinh ra để làm những điều phi thường", "Bạn mang nguồn năng lượng vô cùng đặc biệt".',
                '3. AN TOÀN: CẤM phán đoán bệnh tật, tuổi thọ, ly hôn, ngoại tình, đầu tư sinh lời, nợ nghiệp hù dọa mê tín, đổi tên đổi mệnh hay chấm % hợp tuổi.',
                '4. Trả về DUY NHẤT 1 JSON hợp lệ theo schema:',
                '{"headline":"Tiêu đề chân dung sắc nét gắn với Đường Đời & Sứ Mệnh","summary":"6-8 câu tổng hợp tinh túy cả hồ sơ và trả lời trực tiếp chủ đề người dùng ưu tiên","layer_1_signature":"3 đoạn (~320-420 chữ, tách đoạn bằng \\n\\n): Đoạn 1 về Đường Đời + cách tính Decoz/Phillips + Master/Nợ nghiệp nếu có; Đoạn 2 về Ngày Sinh gốc + Thái Độ; Đoạn 3 về nhịp Biểu đồ 3×3 & mũi tên bẩm sinh","layer_2_self_understanding":"3 đoạn (~320-420 chữ): Phân tích tương tác Sứ Mệnh – Linh Hồn – Nhân Cách – Trưởng Thành, chỉ số Cầu Nối, Đam Mê Tiềm Ẩn, Số Thiếu & Tiềm Thức","layer_3_potential":"3 đoạn (~300-400 chữ): Năng lực nổi trội, môi trường làm việc phù hợp theo 6 nhóm năng lực, Tư Duy Lý Trí, phong cách tạo & giữ tài chính","layer_4_love_family":"3 đoạn (~300-400 chữ): Nhu cầu tình cảm, ngôn ngữ yêu thương, phản ứng khi xung đột và cách nuôi dưỡng hôn nhân/gia đình hai bên","inner_balance_and_environment":"2 đoạn (~220-280 chữ): Phân tích Chỉ số Cân Bằng, cách quản trị căng thẳng tâm-thân an toàn (không phán y khoa) và chọn môi trường bạn bè/cộng sự","layer_5_life_journey":"3 đoạn (~300-400 chữ): Phân tích xuyên suốt 3 Giai Đoạn (Gieo Hạt/Kiến Tạo/Thu Hoạch), 4 Đỉnh Cao Kim Tự Tháp, 4 Thử Thách và trọng tâm Năm Cá Nhân đang xem","strengths_list":["Thế mạnh gắn tên chỉ số 1","Thế mạnh 2","Thế mạnh 3","Thế mạnh 4","Thế mạnh 5"],"watch_outs":["Lưu tâm gắn tên chỉ số 1","Lưu tâm 2","Lưu tâm 3","Lưu tâm 4"],"career_directions":["Định hướng vai trò/ngành 1","Định hướng 2","Định hướng 3","Định hướng 4"],"year_aspects":{"career":"3-4 câu về sự nghiệp năm cá nhân này","finance":"3-4 câu về tài chính năm cá nhân này","love":"3-4 câu về tình cảm/gia đạo năm cá nhân này","growth":"3-4 câu về học hỏi/nội lực năm cá nhân này"},"layer_6_plan":{"actions":["Hành động 1 cụ thể","Hành động 2 cụ thể","Hành động 3 cụ thể"],"goal_7_days":"2-3 câu mục tiêu 7 ngày đo lường được","goal_30_days":"2-3 câu mục tiêu 30 ngày thực tế","couple_family_prompt":"2 câu gợi ý trao đổi chân thành cùng người thân"},"reflection_question":"1 câu hỏi tự chiêm nghiệm sâu sắc"}',
            ]);

            $res = $this->provider->completeJson(
                systemPrompt: $systemPrompt,
                userPrompt: $brief,
                model: $model,
                temperature: 0.32,
                maxTokens: 4600,
                timeoutSeconds: 140
            );

            if (! empty($res['ok']) && is_array($res['data'])) {
                $d = $res['data'];
                $headline = $this->cleanMultilineText((string) ($d['headline'] ?? ''));
                $summary = $this->cleanMultilineText((string) ($d['summary'] ?? ''));
                $l1 = $this->cleanMultilineText((string) ($d['layer_1_signature'] ?? ''));

                if ($headline !== '' && $summary !== '' && $l1 !== '') {
                    $deepIndicators = $fallback['indicator_deep_insights'];
                    $remainingBudget = (int) floor($totalBudgetSeconds - (microtime(true) - $startedAt));
                    if ($remainingBudget >= 25) {
                        $aiDeepIndicators = $this->interpretDeepIndicatorsWithAi(
                            $brief,
                            $calc,
                            $model,
                            min(120, $remainingBudget)
                        );
                        foreach ($aiDeepIndicators as $code => $prose) {
                            $deepIndicators[$code] = $prose;
                        }
                    }

                    $planRaw = is_array($d['layer_6_plan'] ?? null) ? $d['layer_6_plan'] : [];
                    $actions = is_array($planRaw['actions'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => $this->cleanMultilineText((string) $s), $planRaw['actions'])))
                        : $fallback['layer_6_plan']['actions'];
                    $strengthsList = is_array($d['strengths_list'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => $this->cleanMultilineText((string) $s), $d['strengths_list'])))
                        : $fallback['strengths_list'];
                    $watchOuts = is_array($d['watch_outs'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => $this->cleanMultilineText((string) $s), $d['watch_outs'])))
                        : $fallback['watch_outs'];
                    $careerDirs = is_array($d['career_directions'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => $this->cleanMultilineText((string) $s), $d['career_directions'])))
                        : $fallback['career_directions'];
                    $yearAspectsRaw = is_array($d['year_aspects'] ?? null) ? $d['year_aspects'] : [];
                    $yearAspects = [
                        'career' => $this->cleanMultilineText((string) ($yearAspectsRaw['career'] ?? '')) ?: $fallback['year_aspects']['career'],
                        'finance' => $this->cleanMultilineText((string) ($yearAspectsRaw['finance'] ?? '')) ?: $fallback['year_aspects']['finance'],
                        'love' => $this->cleanMultilineText((string) ($yearAspectsRaw['love'] ?? '')) ?: $fallback['year_aspects']['love'],
                        'growth' => $this->cleanMultilineText((string) ($yearAspectsRaw['growth'] ?? '')) ?: $fallback['year_aspects']['growth'],
                    ];

                    $candidate = array_merge($fallback, [
                        'headline' => $headline,
                        'summary' => $summary,
                        'layer_1_signature' => $l1,
                        'layer_2_self_understanding' => $this->cleanMultilineText((string) ($d['layer_2_self_understanding'] ?? '')) ?: $fallback['layer_2_self_understanding'],
                        'layer_3_potential' => $this->cleanMultilineText((string) ($d['layer_3_potential'] ?? '')) ?: $fallback['layer_3_potential'],
                        'layer_4_love_family' => $this->cleanMultilineText((string) ($d['layer_4_love_family'] ?? '')) ?: $fallback['layer_4_love_family'],
                        'inner_balance_and_environment' => $this->cleanMultilineText((string) ($d['inner_balance_and_environment'] ?? '')) ?: $fallback['inner_balance_and_environment'],
                        'layer_5_life_journey' => $this->cleanMultilineText((string) ($d['layer_5_life_journey'] ?? '')) ?: $fallback['layer_5_life_journey'],
                        'indicator_deep_insights' => $deepIndicators,
                        'strengths_list' => array_slice($strengthsList !== [] ? $strengthsList : $fallback['strengths_list'], 0, 5),
                        'career_directions' => array_slice($careerDirs !== [] ? $careerDirs : $fallback['career_directions'], 0, 5),
                        'year_aspects' => $yearAspects,
                        'layer_6_plan' => [
                            'actions' => array_slice($actions !== [] ? $actions : $fallback['layer_6_plan']['actions'], 0, 3),
                            'goal_7_days' => $this->cleanMultilineText((string) ($planRaw['goal_7_days'] ?? '')) ?: $fallback['layer_6_plan']['goal_7_days'],
                            'goal_30_days' => $this->cleanMultilineText((string) ($planRaw['goal_30_days'] ?? '')) ?: $fallback['layer_6_plan']['goal_30_days'],
                            'couple_family_prompt' => $this->cleanMultilineText((string) ($planRaw['couple_family_prompt'] ?? '')) ?: $fallback['layer_6_plan']['couple_family_prompt'],
                        ],
                        'watch_outs' => array_slice($watchOuts !== [] ? $watchOuts : $fallback['watch_outs'], 0, 4),
                        'reflection_question' => $this->cleanMultilineText((string) ($d['reflection_question'] ?? '')) ?: $fallback['reflection_question'],
                        'ai_powered' => true,
                        'ai_model' => (string) ($res['model'] ?? $model ?? 'configured-ai'),
                        'ai_status' => 'completed',
                        'provenance' => 'ai_personalized',
                    ]);

                    $groundedTerms = $this->collectGroundedTermsForProfile($calc, $enriched);
                    $outputVerify = $this->scopedGuard->verifyScopedOutput(
                        $summary."\n\n".$l1."\n\n".($candidate['layer_2_self_understanding'] ?? ''),
                        $brief,
                        'numerology',
                        $groundedTerms,
                        allowMultiline: true
                    );

                    if ($this->safetyGuard->validateAiOutput($candidate)['valid'] && $outputVerify['safe']) {
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
     * Pha 2: Luận giải AI cá nhân hóa riêng cho từng chỉ số hiện diện trong hồ sơ + Biểu đồ 3×3 + Kim Tự Tháp + Năm Cá Nhân.
     *
     * @param  array<string, mixed>  $calc
     * @return array<string, string>
     */
    protected function interpretDeepIndicatorsWithAi(string $brief, array $calc, ?string $model, int $timeoutSeconds = 115): array
    {
        try {
            $codesToExplain = ['life_path', 'birthday', 'attitude', 'birth_chart', 'pinnacles_challenges', 'personal_year'];
            if (! empty($calc['has_full_name'])) {
                $codesToExplain = array_merge($codesToExplain, [
                    'expression', 'soul_urge', 'personality', 'maturity',
                    'balance', 'rational_thought', 'hidden_passion', 'karmic_lessons', 'subconscious_self',
                ]);
            }
            $codesList = implode(', ', $codesToExplain);

            $sys = implode("\n", [
                'Bạn là Chuyên gia Thần Số Học của Mây Hẹn – Dấu Ấn Con Số.',
                'NHIỆM VỤ: Dựa ĐÚNG vào <verified_numerology_profile>, viết phân tích cá nhân hóa chuyên sâu riêng từng chỉ số sau: '.$codesList.'.',
                'YÊU CẦU TỪNG MỤC: Mỗi khóa viết 4–6 câu (~100–150 chữ), chỉ rõ con số thật trong hồ sơ của người này tương tác với các số khác ra sao (ví dụ kết hợp với Đường Đời/Sứ Mệnh/Năm cá nhân nào), tránh văn mẫu sáo rỗng, không phán y khoa hay bói toán.',
                'Trả về DUY NHẤT 1 JSON object chứa các khóa đó (giá trị string): {"life_path":"...", ...}',
            ]);

            $res = $this->provider->completeJson(
                systemPrompt: $sys,
                userPrompt: $brief,
                model: $model,
                temperature: 0.3,
                maxTokens: 4200,
                timeoutSeconds: max(25, $timeoutSeconds)
            );

            if (empty($res['ok']) || ! is_array($res['data'])) {
                return [];
            }

            $out = [];
            foreach ($codesToExplain as $code) {
                $text = $this->cleanMultilineText((string) ($res['data'][$code] ?? ''));
                if ($text === '' || mb_strlen($text, 'UTF-8') < 40) {
                    continue;
                }
                if (! $this->safetyGuard->validateAiOutput(['indicator' => $text])['valid']) {
                    continue;
                }
                $verify = $this->scopedGuard->verifyScopedOutput($text, $brief, 'numerology', [$code], allowMultiline: true);
                if ($verify['safe']) {
                    $out[$code] = $verify['clean_answer'];
                }
            }

            return $out;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Xác định phạm vi hỏi đáp hợp lệ (phạm vi chương sec:*, phạm vi chỉ số ind:* hoặc tổng thể hồ sơ).
     *
     * @param  array<string, mixed>  $calc
     * @return array{
     *     valid: bool,
     *     scope_code: ?string,
     *     scope_label: string,
     *     scope_type: 'general'|'indicator'|'section',
     *     indicator_code: ?string,
     *     section_key: ?string
     * }
     */
    public function resolveQuestionScope(array $calc, ?string $topicCode = null, ?string $sectionKey = null): array
    {
        $rawSection = trim((string) ($sectionKey ?? ''));
        $rawTopic = trim((string) ($topicCode ?? ''));

        if ($rawSection !== '') {
            if (isset(self::SECTION_DEFINITIONS[$rawSection])) {
                $def = self::SECTION_DEFINITIONS[$rawSection];

                return [
                    'valid' => true,
                    'scope_code' => $def['scope_code'],
                    'scope_label' => $def['label'],
                    'scope_type' => 'section',
                    'indicator_code' => null,
                    'section_key' => $rawSection,
                ];
            }

            return [
                'valid' => false,
                'scope_code' => null,
                'scope_label' => 'Phạm vi không hợp lệ',
                'scope_type' => 'general',
                'indicator_code' => null,
                'section_key' => null,
            ];
        }

        if ($rawTopic !== '' && str_starts_with($rawTopic, 'sec:')) {
            foreach (self::SECTION_DEFINITIONS as $key => $def) {
                if ($def['scope_code'] === $rawTopic) {
                    return [
                        'valid' => true,
                        'scope_code' => $def['scope_code'],
                        'scope_label' => $def['label'],
                        'scope_type' => 'section',
                        'indicator_code' => null,
                        'section_key' => $key,
                    ];
                }
            }

            return [
                'valid' => false,
                'scope_code' => null,
                'scope_label' => 'Phạm vi không hợp lệ',
                'scope_type' => 'general',
                'indicator_code' => null,
                'section_key' => null,
            ];
        }

        if ($rawTopic !== '') {
            $cleanCode = str_starts_with($rawTopic, 'ind:') ? substr($rawTopic, 4) : $rawTopic;
            if (isset(self::INDICATOR_LABELS[$cleanCode])) {
                $valDisplay = $this->resolveIndicatorValueDisplay($calc, $cleanCode);
                $label = self::INDICATOR_LABELS[$cleanCode].($valDisplay !== '' ? " ({$valDisplay})" : '');

                return [
                    'valid' => true,
                    'scope_code' => 'ind:'.$cleanCode,
                    'scope_label' => $label,
                    'scope_type' => 'indicator',
                    'indicator_code' => $cleanCode,
                    'section_key' => null,
                ];
            }

            return [
                'valid' => false,
                'scope_code' => null,
                'scope_label' => 'Chỉ số không hợp lệ',
                'scope_type' => 'general',
                'indicator_code' => null,
                'section_key' => null,
            ];
        }

        return [
            'valid' => true,
            'scope_code' => null,
            'scope_label' => 'Tổng thể hồ sơ',
            'scope_type' => 'general',
            'indicator_code' => null,
            'section_key' => null,
        ];
    }

    /**
     * Trợ lý Thần Số Học AI — Hỏi sâu khóa chặt vào từng chương / từng chỉ số trên hồ sơ (Zero-Trust Gate).
     *
     * @param  list<array{question: string, answer: string, scope_code?: ?string}>  $history
     * @return array{answer: string, ai_powered: bool, ai_model: ?string, blocked?: bool, code?: ?string, scope_code?: ?string, scope_label?: string}
     */
    public function answerFollowup(
        NumerologyReading $reading,
        string $question,
        array $history = [],
        ?string $topicCode = null,
        ?string $sectionKey = null
    ): array {
        $calc = is_array($reading->calculation_payload_json) ? $reading->calculation_payload_json : [];
        $interpretation = is_array($reading->interpretation_json) ? $reading->interpretation_json : [];
        $enriched = $this->knowledge->enrichCalculation($calc);
        $scope = $this->resolveQuestionScope($calc, $topicCode, $sectionKey);

        if (! $scope['valid']) {
            return [
                'answer' => 'Mục hoặc chỉ số bạn chọn để hỏi sâu chưa hợp lệ trên hồ sơ này.',
                'ai_powered' => false,
                'ai_model' => null,
                'blocked' => true,
                'code' => 'INVALID_SCOPE',
                'scope_code' => null,
                'scope_label' => 'Không hợp lệ',
            ];
        }

        $matchedConcepts = $this->knowledge->lookupConceptsInText($question);
        $groundedTerms = array_values(array_unique(array_merge(
            $this->collectGroundedTermsForProfile($calc, $enriched),
            $this->scopedGuard->extractGroundedTerms([
                $scope['scope_label'],
                $matchedConcepts,
            ])
        )));

        $inputGuard = $this->scopedGuard->inspectScopedQuestion($question, 'numerology', $groundedTerms, 600);
        if (! $inputGuard['allowed']) {
            return [
                'answer' => (string) ($inputGuard['message'] ?? 'Câu hỏi nằm ngoài phạm vi hồ sơ Thần Số Học hiện tại.'),
                'ai_powered' => false,
                'ai_model' => null,
                'blocked' => true,
                'code' => $inputGuard['code'],
                'scope_code' => $scope['scope_code'],
                'scope_label' => $scope['scope_label'],
            ];
        }

        $cleanQuestion = $inputGuard['clean_question'];
        $verifiedContext = $this->buildScopedSectionContext($calc, $enriched, $interpretation, $scope, $history, $cleanQuestion);
        $fallbackAnswer = $this->buildScopedSectionFallback($calc, $enriched, $interpretation, $scope, $cleanQuestion);

        try {
            $policy = $this->governance->getPolicy();
            if (empty($policy['kill_switch']) && $this->provider->isAvailable()) {
                $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;

                $systemPrompt = implode("\n", [
                    'Bạn là Trợ lý Thần Số Học Chuyên Sâu & Nhân Văn của Mây Hẹn – Dấu Ấn Con Số, đang giải đáp sâu ĐÚNG PHẠM VI: "'.$scope['scope_label'].'".',
                    $this->scopedGuard->buildZeroTrustSystemPreamble('verified_numerology_section_context'),
                    'QUY TẮC CHUYÊN MÔN:',
                    '1. Trả lời sâu sắc, trực diện vào phạm vi đang hỏi ("'.$scope['scope_label'].'") trong 6–8 câu (có thể chia 2 đoạn bằng \\n\\n cho dễ đọc).',
                    '2. Bắt buộc dẫn chứng chính xác con số, cách tính, mũi tên 3×3, đỉnh cao/thử thách hoặc khái niệm có trong <verified_numerology_section_context>. Nếu người dùng hỏi khái niệm ("là gì", "ý nghĩa"), hãy giải thích dễ hiểu rồi gắn thẳng vào chỉ số thật của hồ sơ.',
                    '3. TUYỆT ĐỐI không sửa con số, không bịa chỉ số thiếu khi chưa có họ tên, không dùng văn mẫu AI, không phán y khoa/ly hôn/bói toán hay chấm % hợp tuổi.',
                    '4. Trả về DUY NHẤT JSON: {"answer":"Câu trả lời sâu sắc, bám sát phần đã luận"}',
                ]);

                $res = $this->provider->completeJson(
                    systemPrompt: $systemPrompt,
                    userPrompt: $verifiedContext."\n".$inputGuard['wrapped_question'],
                    model: $model,
                    temperature: 0.28,
                    maxTokens: 1200,
                    timeoutSeconds: 100
                );

                if (! empty($res['ok']) && is_array($res['data'])) {
                    $verify = $this->scopedGuard->verifyScopedOutput(
                        (string) ($res['data']['answer'] ?? ''),
                        $verifiedContext,
                        'numerology',
                        $groundedTerms,
                        allowMultiline: true
                    );
                    if ($verify['safe'] && $verify['clean_answer'] !== '' && $this->safetyGuard->validateAiOutput(['answer' => $verify['clean_answer']])['valid']) {
                        return [
                            'answer' => $verify['clean_answer'],
                            'ai_powered' => true,
                            'ai_model' => (string) ($res['model'] ?? $model ?? 'configured-ai'),
                            'blocked' => false,
                            'scope_code' => $scope['scope_code'],
                            'scope_label' => $scope['scope_label'],
                        ];
                    }
                }
            }
        } catch (Throwable) {
            // Giữ fallback chuẩn hóa
        }

        return [
            'answer' => $fallbackAnswer,
            'ai_powered' => false,
            'ai_model' => null,
            'blocked' => false,
            'scope_code' => $scope['scope_code'],
            'scope_label' => $scope['scope_label'],
        ];
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

        $lifeJourneyParagraphs = [
            sprintf(
                'Năm cá nhân %d (%s): %s Trọng tâm phát triển năm nay: %s',
                $calc['personal_cycles']['personal_year']['value'],
                $pyMeta['theme'] ?? '',
                $pyMeta['summary'] ?? '',
                $pyMeta['focus'] ?? ''
            ),
            $p1
                ? sprintf('Đỉnh cao đầu tiên mang số %s (%s): %s', $p1['display'] ?? '', $p1['age_range'] ?? '', $p1['meaning'] ?? '')
                : '',
        ];

        $balanceText = '';
        if (! empty($enriched['indicator_meanings']['balance'])) {
            $balMeta = $enriched['indicator_meanings']['balance'];
            $balanceText = "Khi đối mặt áp lực, Chỉ số Cân Bằng {$calc['extended_numbers']['balance']['display']} ({$balMeta['title']}) gợi ý bạn quay về nhịp điềm tĩnh: {$balMeta['contextual_meaning']}";
        } else {
            $balanceText = "Để giữ cân bằng tâm – thân bền vững theo Đường Đời {$lp['display']}, hãy duy trì thói quen nghỉ ngơi có nhịp điệu và tránh dồn nén cảm xúc khi khối lượng công việc tăng cao.";
        }

        return [
            'headline' => "Đường Đời {$lp['display']} — ".($lpMeta['title'] ?? 'Dấu Ấn Con Số'),
            'summary' => ($lpMeta['contextual_meaning'] ?? '').$contextLine,
            'layer_1_signature' => ($lpMeta['contextual_meaning'] ?? '')."\n\n".($lpMeta['traditional_view'] ?? ''),
            'layer_2_self_understanding' => $selfText,
            'layer_3_potential' => ($lpMeta['work_meaning'] ?? '')."\n\nThế mạnh tự nhiên: ".($lpMeta['strengths'] ?? ''),
            'layer_4_love_family' => ($lpMeta['love_meaning'] ?? '')."\n\n".($lpMeta['marriage_meaning'] ?? '').' '.($lpMeta['family_meaning'] ?? ''),
            'inner_balance_and_environment' => $balanceText,
            'layer_5_life_journey' => implode("\n\n", array_filter($lifeJourneyParagraphs)),
            'indicator_deep_insights' => $this->buildDeepIndicatorInsightsFallback($calc, $enriched),
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
     * Xây dựng bài luận dự phòng chuyên sâu cho từng chỉ số và biểu đồ dựa 100% trên dữ liệu thật.
     *
     * @param  array<string, mixed>  $calc
     * @param  array<string, mixed>  $enriched
     * @return array<string, string>
     */
    protected function buildDeepIndicatorInsightsFallback(array $calc, array $enriched): array
    {
        $out = [];
        foreach ($enriched['detailed_indicators'] ?? [] as $ind) {
            $code = (string) ($ind['code'] ?? '');
            if ($code === '') {
                continue;
            }
            $parts = array_filter([
                (string) ($ind['contextual_meaning'] ?? ''),
                ! empty($ind['strengths']) ? 'Điểm sáng: '.$ind['strengths'] : '',
                ! empty($ind['watch_outs']) ? 'Lưu tâm: '.$ind['watch_outs'] : '',
            ]);
            $out[$code] = implode("\n\n", $parts);
        }

        $activeArrows = array_values(array_filter(
            $enriched['arrows_enriched'] ?? [],
            fn (array $a): bool => ($a['status'] ?? 'partial') !== 'partial' && ! empty($a['name'])
        ));
        if ($activeArrows !== []) {
            $firstArrow = $activeArrows[0];
            $out['birth_chart'] = sprintf(
                'Trên Biểu đồ ngày sinh 3×3 của bạn, nổi bật là %s (%s — %s): %s Các ô trống trên lưới không phải khuyết điểm cố định mà là vùng năng lượng mở để bạn chủ động bồi đắp qua thói quen sống.',
                $firstArrow['status'] === 'full' ? 'Mũi tên đủ' : 'Mũi tên trống',
                $firstArrow['code'],
                $firstArrow['name'],
                $firstArrow['meaning']
            );
        } else {
            $out['birth_chart'] = 'Các chữ số trong ngày sinh của bạn phân bổ đan xen trên lưới 3×3 nên không tạo mũi tên trống tuyệt đối — dấu hiệu của sự cân bằng tự nhiên giữa suy nghĩ, cảm xúc và hành động thực tế.';
        }

        $p1 = $enriched['pinnacles_enriched'][0] ?? null;
        $c1 = $enriched['challenges_enriched'][0] ?? null;
        if ($p1) {
            $out['pinnacles_challenges'] = sprintf(
                'Chuỗi 4 Đỉnh Cao khởi mở với Đỉnh 1 số %s (%s): %s Song hành cùng Thử Thách %s: %s',
                $p1['display'],
                $p1['age_range'],
                $p1['meaning'],
                $c1['value'] ?? '0',
                $c1['meaning'] ?? ''
            );
        }

        $py = $calc['personal_cycles']['personal_year'] ?? [];
        $pyMeta = $enriched['personal_year_meaning'] ?? [];
        $out['personal_year'] = sprintf(
            'Năm cá nhân %d mang năng lượng Số %d (%s): %s %s',
            (int) ($py['year'] ?? now('Asia/Ho_Chi_Minh')->year),
            (int) ($py['value'] ?? 1),
            (string) ($pyMeta['theme'] ?? ''),
            (string) ($pyMeta['summary'] ?? ''),
            (string) ($pyMeta['focus'] ?? '')
        );

        return $out;
    }

    /**
     * Tổng hợp bản tóm tắt hồ sơ ẩn danh đầy đủ (Đường Đời, Mở rộng, Biểu đồ 3×3, Kim Tự Tháp, 12 Tháng) gửi sang AI.
     *
     * @param  array<string, mixed>  $calc
     * @param  array<string, mixed>  $enriched
     */
    protected function buildAnonymousProfileBrief(array $calc, array $enriched, string $focusTopic, string $userContext): string
    {
        $core = $calc['core_numbers'];
        $ext = $calc['extended_numbers'];
        $py = $calc['personal_cycles']['personal_year'];

        $arrowsBrief = [];
        foreach ($enriched['arrows_enriched'] ?? [] as $ar) {
            if (($ar['status'] ?? 'partial') !== 'partial' && ! empty($ar['name'])) {
                $arrowsBrief[] = ($ar['status'] === 'full' ? 'Đủ ' : 'Trống ')."{$ar['code']} ({$ar['name']})";
            }
        }

        $chartDigitsBrief = [];
        foreach ($calc['birth_chart']['cells'] ?? [] as $d => $cell) {
            if ((int) ($cell['count'] ?? 0) > 0) {
                $chartDigitsBrief[] = "{$d}×{$cell['count']}";
            }
        }

        $bridges = $ext['bridges'] ?? [];
        $bridgesStr = isset($bridges['life_path_expression'])
            ? 'Cầu nối Đường Đời-Sứ Mệnh='.$bridges['life_path_expression'].(isset($bridges['soul_personality']) ? ', Linh Hồn-Nhân Cách='.$bridges['soul_personality'] : '')
            : 'chưa có họ tên';

        $kdList = array_map(fn (array $kd): string => "{$kd['code']} ({$kd['location']})", $enriched['karmic_master_insights']['karmic_debts'] ?? []);
        $topDomains = array_map(
            fn (array $d): string => "{$d['label']} ({$d['score']}/100)",
            array_slice($enriched['career_domains']['domains'] ?? [], 0, 3)
        );

        $lines = [
            '<verified_numerology_profile>',
            "Phương pháp: {$calc['method_name']} ({$calc['method_id']} v{$calc['method_version']})",
            "Trạng thái dữ liệu: {$calc['completeness_label']} | Tuổi hiện tại theo năm xem: ".($enriched['current_age'] ?? 0),
            "Đường Đời (Life Path): {$core['life_path']['display']} — ".($enriched['indicator_meanings']['life_path']['title'] ?? ''),
            'Đối chiếu phương pháp: '.($core['life_path_comparison']['explanation'] ?? ''),
            "Ngày Sinh gốc: {$core['birthday']['raw_day']} (nền {$core['birthday']['root_display']}) | Thái Độ: {$ext['attitude']['display']}",
            'Sứ Mệnh (Expression): '.($core['expression']['display'] ?? 'chưa có họ tên'),
            'Linh Hồn (Soul Urge): '.($core['soul_urge']['display'] ?? 'chưa có họ tên'),
            'Nhân Cách (Personality): '.($core['personality']['display'] ?? 'chưa có họ tên'),
            'Trưởng Thành (Maturity): '.($ext['maturity']['display'] ?? 'chưa có họ tên'),
            'Cân Bằng: '.($ext['balance']['display'] ?? 'chưa có họ tên').' | Tư Duy Lý Trí: '.($ext['rational_thought']['display'] ?? 'chưa có họ tên'),
            'Đam Mê Tiềm Ẩn: '.(! empty($ext['hidden_passion']['numbers']) ? implode(',', $ext['hidden_passion']['numbers']) : 'chưa có').' | Tiềm Thức: '.($ext['subconscious_self']['value'] ?? 'chưa có'),
            'Số thiếu trong tên: '.(! empty($ext['karmic_lessons']['missing_numbers']) ? implode(',', $ext['karmic_lessons']['missing_numbers']) : (! empty($calc['has_full_name']) ? 'Đủ 1–9' : 'chưa có họ tên')),
            'Cầu nối: '.$bridgesStr,
            $kdList !== [] ? 'Nợ nghiệp lưu ý: '.implode(', ', $kdList) : 'Nợ nghiệp chính: Không phát sinh 13/14/16/19',
            'Biểu đồ ngày sinh 3×3: ['.implode(', ', $chartDigitsBrief).'] | Mũi tên: '.($arrowsBrief !== [] ? implode('; ', $arrowsBrief) : 'Phân bổ cân bằng'),
            '3 Giai đoạn cuộc đời: '.implode(', ', array_map(fn (array $pc): string => "{$pc['stage_name']}={$pc['value']} ({$pc['age_range']})", $enriched['period_cycles_enriched'] ?? [])),
            '4 Đỉnh cao: '.implode(', ', array_map(fn (array $p): string => "P{$p['order']}={$p['display']} ({$p['age_range']})", $calc['pinnacles'])),
            '4 Thử thách: '.implode(', ', array_map(fn (array $c): string => "C{$c['order']}={$c['value']}", $calc['challenges'])),
            "Năm cá nhân {$py['year']}: {$py['value']} — ".($enriched['personal_year_meaning']['theme'] ?? '').' | Tháng CN: '.($calc['personal_cycles']['personal_month']['value'] ?? '').' | Ngày CN: '.($calc['personal_cycles']['personal_day']['value'] ?? ''),
            'Top 3 nhóm năng lực: '.implode(', ', $topDomains),
            "Chủ đề ưu tiên: {$focusTopic}",
            $userContext !== '' ? "Hoàn cảnh thực tế người dùng chia sẻ: {$userContext}" : '',
            '</verified_numerology_profile>',
        ];

        return implode("\n", array_filter($lines));
    }

    /**
     * Xây dựng ngữ cảnh khóa chặt đúng phạm vi đang hỏi (kèm từ điển khái niệm nếu người dùng thắc mắc thuật ngữ).
     *
     * @param  array<string, mixed>  $calc
     * @param  array<string, mixed>  $enriched
     * @param  array<string, mixed>  $interpretation
     * @param  array<string, mixed>  $scope
     * @param  list<array{question: string, answer: string, scope_code?: ?string}>  $history
     */
    protected function buildScopedSectionContext(
        array $calc,
        array $enriched,
        array $interpretation,
        array $scope,
        array $history,
        string $question
    ): string {
        $brief = $this->buildAnonymousProfileBrief($calc, $enriched, 'toan_bo', '');
        $sectionText = '';

        if ($scope['scope_type'] === 'indicator' && ! empty($scope['indicator_code'])) {
            $code = (string) $scope['indicator_code'];
            $indItem = collect($enriched['detailed_indicators'] ?? [])->firstWhere('code', $code);
            $deepText = (string) ($interpretation['indicator_deep_insights'][$code] ?? '');
            $glossaryItem = $this->knowledge->getConceptGlossary()[$code] ?? null;
            $lines = ["[CHỈ SỐ ĐANG KHÓA PHẠM VI: {$scope['scope_label']}]"];
            if (is_array($indItem)) {
                $lines[] = 'Giá trị: '.$indItem['display'].' — '.($indItem['title'] ?? '');
                $lines[] = 'Ý nghĩa cốt lõi: '.($indItem['contextual_meaning'] ?? '');
                $lines[] = 'Điểm mạnh: '.($indItem['strengths'] ?? '');
                $lines[] = 'Lưu tâm: '.($indItem['watch_outs'] ?? '');
            }
            if ($deepText !== '') {
                $lines[] = 'Luận giải chi tiết đã xuất: '.$deepText;
            }
            if (is_array($glossaryItem)) {
                $lines[] = "Định nghĩa khái niệm: {$glossaryItem['summary']} Công thức: {$glossaryItem['formula']}";
            }
            $sectionText = implode("\n", array_filter($lines));
        } elseif ($scope['scope_type'] === 'section' && ! empty($scope['section_key'])) {
            $sKey = (string) $scope['section_key'];
            $sectionText = match ($sKey) {
                'overview' => (string) ($interpretation['headline'] ?? '')."\n".($interpretation['summary'] ?? '')."\n".($interpretation['layer_1_signature'] ?? ''),
                'core_numbers' => "Sứ mệnh/Linh hồn/Nhân cách/Mở rộng:\n".($interpretation['layer_2_self_understanding'] ?? ''),
                'birth_chart' => (string) ($interpretation['indicator_deep_insights']['birth_chart'] ?? ''),
                'self_understanding' => ($interpretation['layer_2_self_understanding'] ?? '')."\n".($interpretation['inner_balance_and_environment'] ?? ''),
                'career_potential' => ($interpretation['layer_3_potential'] ?? '')."\nĐịnh hướng: ".implode(', ', $interpretation['career_directions'] ?? []),
                'love_family' => (string) ($interpretation['layer_4_love_family'] ?? ''),
                'life_journey' => ($interpretation['layer_5_life_journey'] ?? '')."\n".($interpretation['indicator_deep_insights']['pinnacles_challenges'] ?? ''),
                'personal_cycles' => ($interpretation['indicator_deep_insights']['personal_year'] ?? '')."\n".json_encode($interpretation['year_aspects'] ?? [], JSON_UNESCAPED_UNICODE),
                'action_plan' => json_encode($interpretation['layer_6_plan'] ?? [], JSON_UNESCAPED_UNICODE),
                default => (string) ($interpretation['summary'] ?? ''),
            };
        } else {
            $sectionText = (string) ($interpretation['summary'] ?? '');
        }

        $glossaryHits = $this->knowledge->lookupConceptsInText($question);
        $glossaryLines = [];
        foreach ($glossaryHits as $g) {
            $glossaryLines[] = "• {$g['title']} ({$g['group']}): {$g['summary']} [Công thức: {$g['formula']}] — Ứng dụng: {$g['how_to_apply']}";
        }

        $histLines = [];
        foreach (array_slice($history, -4) as $h) {
            $histLines[] = "Hỏi: {$h['question']}\nĐáp: {$h['answer']}";
        }

        return implode("\n", array_filter([
            '<verified_numerology_section_context>',
            $brief,
            "Phạm vi đang khóa chặt: {$scope['scope_label']} (code=".($scope['scope_code'] ?? 'general').')',
            $sectionText !== '' ? "Nội dung đã luận trong phạm vi:\n{$sectionText}" : '',
            $glossaryLines !== [] ? "Từ điển giải thích khái niệm liên quan:\n".implode("\n", $glossaryLines) : '',
            $histLines !== [] ? "Hội thoại gần nhất:\n".implode("\n---\n", $histLines) : '',
            '</verified_numerology_section_context>',
        ]));
    }

    /**
     * Câu trả lời dự phòng chuẩn hóa (luôn trả lời trực tiếp khái niệm + số thật của phần đang xem).
     *
     * @param  array<string, mixed>  $calc
     * @param  array<string, mixed>  $enriched
     * @param  array<string, mixed>  $interpretation
     * @param  array<string, mixed>  $scope
     */
    protected function buildScopedSectionFallback(
        array $calc,
        array $enriched,
        array $interpretation,
        array $scope,
        string $cleanQuestion
    ): string {
        $glossaryHits = $this->knowledge->lookupConceptsInText($cleanQuestion);
        $glossaryIntro = '';
        if ($glossaryHits !== []) {
            $first = $glossaryHits[0];
            $glossaryIntro = "Giải thích dễ hiểu về {$first['title']}: {$first['summary']} ({$first['how_to_apply']})\n\n";
        }

        $lp = $calc['core_numbers']['life_path']['display'] ?? '—';
        $exp = $calc['core_numbers']['expression']['display'] ?? 'chưa có họ tên';
        $soul = $calc['core_numbers']['soul_urge']['display'] ?? 'chưa có họ tên';
        $py = $calc['personal_cycles']['personal_year']['value'] ?? '—';

        if ($scope['scope_type'] === 'indicator' && ! empty($scope['indicator_code'])) {
            $indCode = (string) $scope['indicator_code'];
            $indDetail = $interpretation['indicator_deep_insights'][$indCode] ?? '';
            if ($indDetail !== '') {
                return trim($glossaryIntro."Trong phạm vi {$scope['scope_label']} trên hồ sơ của bạn (Đường Đời {$lp}, Năm cá nhân {$py}):\n\n{$indDetail}");
            }
        }

        return trim($glossaryIntro.sprintf(
            'Trong phạm vi "%s" trên hồ sơ %s (Đường Đời %s, Sứ Mệnh %s, Linh Hồn %s, Năm cá nhân %s): câu hỏi "%s" tập trung vào việc quan sát cách chỉ số này vận hành trong thói quen hằng ngày và lựa chọn một bước điều chỉnh nhỏ đo lường được trong 30 ngày tới.',
            $scope['scope_label'],
            $calc['method_name'] ?? 'Pythagorean Hans Decoz',
            $lp,
            $exp,
            $soul,
            $py,
            $cleanQuestion
        ));
    }

    /**
     * Gom danh sách từ neo dữ liệu thật để ScopedToolAiGuardService kiểm định input/output.
     *
     * @param  array<string, mixed>  $calc
     * @param  array<string, mixed>  $enriched
     * @return list<string>
     */
    protected function collectGroundedTermsForProfile(array $calc, array $enriched): array
    {
        return $this->scopedGuard->extractGroundedTerms([
            $calc['method_name'] ?? '',
            $calc['core_numbers'] ?? [],
            $calc['extended_numbers'] ?? [],
            $calc['personal_cycles']['personal_year'] ?? [],
            $enriched['detailed_indicators'] ?? [],
            $enriched['arrows_enriched'] ?? [],
            $enriched['pinnacles_enriched'] ?? [],
            $enriched['challenges_enriched'] ?? [],
            array_values($this->knowledge->getConceptGlossary()),
        ], 600);
    }

    protected function resolveIndicatorValueDisplay(array $calc, string $code): string
    {
        $core = $calc['core_numbers'] ?? [];
        $ext = $calc['extended_numbers'] ?? [];

        return match ($code) {
            'life_path', 'expression', 'soul_urge', 'personality', 'birthday' => (string) ($core[$code]['display'] ?? ''),
            'maturity', 'attitude', 'balance', 'rational_thought' => (string) ($ext[$code]['display'] ?? ''),
            'hidden_passion' => ! empty($ext['hidden_passion']['numbers']) ? implode(', ', $ext['hidden_passion']['numbers']) : '',
            'karmic_lessons' => ! empty($ext['karmic_lessons']['missing_numbers']) ? 'Thiếu '.implode(', ', $ext['karmic_lessons']['missing_numbers']) : '',
            'subconscious_self' => isset($ext['subconscious_self']['value']) ? $ext['subconscious_self']['value'].'/9' : '',
            default => '',
        };
    }

    protected function cleanMultilineText(string $raw): string
    {
        $stripped = trim(strip_tags($raw));
        $normalized = str_replace(["\r\n", "\r"], "\n", $stripped);
        $lines = array_map(
            static fn (string $line): string => trim((string) preg_replace('/[ \t]+/u', ' ', $line)),
            explode("\n", $normalized)
        );
        $joined = implode("\n", $lines);

        return trim((string) preg_replace("/\n{3,}/", "\n\n", $joined));
    }
}
