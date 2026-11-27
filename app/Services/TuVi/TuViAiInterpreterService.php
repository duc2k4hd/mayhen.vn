<?php

declare(strict_types=1);

namespace App\Services\TuVi;

use App\Models\TuViChart;
use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\Contracts\LlmProviderInterface;
use App\Services\Intelligence\ScopedToolAiGuardService;
use Illuminate\Support\Str;
use Throwable;

/**
 * Dịch vụ AI Luận Giải Tử Vi Chuyên Sâu & Hỏi Đáp Theo Từng Phần — Mây Hẹn Thiên Mệnh.
 *
 * - Chỉ gửi dữ liệu lá số ẩn danh (KHÔNG gửi tên thật/email/SĐT sang nhà cung cấp AI).
 * - Luận giải 2 pha chuyên sâu phủ toàn bộ 6 tầng + 12 cung đầy đủ, diễn giải rõ sao/Tứ Hóa/Tam Phương Tứ Chính.
 * - Mọi câu hỏi Hỏi Sâu & đầu ra AI bắt buộc qua kiểm định kép TuViSafetyGuardService + ScopedToolAiGuardService.
 */
class TuViAiInterpreterService
{
    /**
     * Danh mục các phần luận giải hợp lệ cho cơ chế "Bấm phần nào -> Hỏi sâu AI đúng phần đó"
     * (khóa chặt mã scope <= 32 ký tự để lưu trực tiếp vào tuvi_chart_followups.palace_code).
     *
     * @var array<string, array{scope_code: string, label: string, description: string, suggested_questions: list<string>}>
     */
    public const SECTION_DEFINITIONS = [
        'core_overview' => [
            'scope_code' => 'sec:core_overview',
            'label' => 'Tổng Quan Bản Mệnh, Cục & Trục Mệnh – Thân',
            'description' => 'Phân tích sâu Bản Mệnh Nạp Âm, Ngũ Hành Cục, quan hệ Cục–Mệnh, Chủ Mệnh/Chủ Thân và Cung Mệnh–Thân.',
            'suggested_questions' => [
                'Cục và Bản Mệnh của mình tương tác ra sao trong công việc và tâm lý?',
                'Cung Thân cư tại đây ảnh hưởng gì tới giai đoạn sau 30 tuổi của mình?',
            ],
        ],
        'combinations_tu_hoa' => [
            'scope_code' => 'sec:combinations',
            'label' => 'Tam Phương Tứ Chính, Tứ Hóa & Cách Cục',
            'description' => 'Phân tích liên kết giữa 3 cung Tam Hợp, cung Xung Chiếu, bộ Tứ Hóa (Lộc/Quyền/Khoa/Kỵ) và Tuần/Triệt.',
            'suggested_questions' => [
                'Hóa Lộc và Hóa Kỵ trong lá số của mình đang kích hoạt điểm mạnh và điểm cần tránh nào?',
                'Tam Phương Tứ Chính quanh Cung Mệnh bổ trợ hoặc kéo lùi nhau ra sao?',
            ],
        ],
        'career_orientation' => [
            'scope_code' => 'sec:career_axis',
            'label' => 'Thiên Hướng Nghề Nghiệp & Tài Lộc (Mệnh – Tài – Quan)',
            'description' => 'Phân tích con đường sự nghiệp, môi trường phát triển, phong cách kiếm tiền và quản trị tài chính.',
            'suggested_questions' => [
                'Với trục Mệnh – Tài – Quan này, mình hợp làm chuyên môn sâu hay quản lý/kinh doanh độc lập?',
                'Điểm nghẽn nào khiến tài chính hoặc thăng tiến của mình dễ chậm lại và cách khắc phục?',
            ],
        ],
        'love_family' => [
            'scope_code' => 'sec:love_family',
            'label' => 'Nhân Duyên, Hôn Nhân & Gia Đình Hai Bên',
            'description' => 'Phân tích Cung Phu Thê phối hợp Phúc Đức, Điền Trạch và Tử Tức.',
            'suggested_questions' => [
                'Mình nên rèn thói quen giao tiếp nào để tình cảm và hôn nhân bền vững hơn theo lá số này?',
                'Khi kết hợp giữa Phu Thê và Phúc Đức, điểm nào dễ nảy sinh hiểu lầm giữa hai người?',
            ],
        ],
        'health_network' => [
            'scope_code' => 'sec:health_network',
            'label' => 'Thể Trạng Tinh Thần & Mạng Lưới Quan Hệ (Tật · Nô · Phụ · Huynh)',
            'description' => 'Góc nhìn cân bằng nhịp sống, quản trị áp lực, hợp tác cộng sự và gia phong.',
            'suggested_questions' => [
                'Áp lực tinh thần của mình thường đến từ đâu và nên giữ nhịp sinh hoạt thế nào?',
                'Trong hợp tác với bạn bè, đồng nghiệp ở Cung Nô Bộc, mình cần lưu ý nguyên tắc nào?',
            ],
        ],
        'life_stages' => [
            'scope_code' => 'sec:life_stages',
            'label' => 'Đại Hạn, Tiểu Hạn & Lưu Niên Năm Xem',
            'description' => 'Đọc nhịp chuyển động 10 năm Đại Hạn, Tiểu Hạn và Lưu Thái Tuế trong năm hiện tại.',
            'suggested_questions' => [
                'Trong năm xem vận này, mình nên ưu tiên mở rộng bước mới hay củng cố nền tảng cũ?',
                'Chủ đề trọng tâm của Đại Hạn 10 năm hiện tại đòi hỏi mình chuẩn bị kỹ năng gì?',
            ],
        ],
        'action_plan' => [
            'scope_code' => 'sec:action_plan',
            'label' => 'Lộ Trình Hành Động & Tự Chiêm Nghiệm',
            'description' => 'Cụ thể hóa thông điệp lá số thành việc làm theo ngày, tuần, 30 ngày và dài hạn.',
            'suggested_questions' => [
                'Đâu là 1 thói quen ưu tiên nhất trong 30 ngày tới để chuyển hóa điểm yếu của lá số?',
                'Nếu đang phân vân giữa hai hướng đi lớn, mình nên đối chiếu tiêu chí nào từ lá số?',
            ],
        ],
        'dai_han_timeline' => [
            'scope_code' => 'sec:dai_han',
            'label' => 'Dòng Thời Gian 12 Đại Hạn Cuộc Đời',
            'description' => 'So sánh bước chuyển giữa Đại Hạn hiện tại với các giai đoạn 10 năm kế tiếp.',
            'suggested_questions' => [
                'Bước chuyển từ Đại Hạn hiện tại sang Đại Hạn kế tiếp sẽ thay đổi trọng tâm cuộc sống ra sao?',
                'Giai đoạn Đại Hạn nào trong lá số thuận lợi nhất để ổn định sự nghiệp và gia đình?',
            ],
        ],
        'verified_patterns' => [
            'scope_code' => 'sec:patterns',
            'label' => 'Cách Cục & Cấu Trúc Sao Đã Xác Minh',
            'description' => 'Giải thích chi tiết các Cách Cục, Tứ Hóa và thế đứng đặc biệt hiện diện trên Thiên Bàn.',
            'suggested_questions' => [
                'Cách cục nổi bật nhất trên lá số của mình phát huy tốt nhất trong điều kiện nào?',
                'Nếu có Tuần hoặc Triệt đóng tại cung quan trọng, ảnh hưởng thực tế là tốt hay cần cẩn trọng?',
            ],
        ],
        'thien_ban_overview' => [
            'scope_code' => 'sec:thien_ban',
            'label' => 'Thiên Bàn, Lai Nhân Cung & Cân Lượng Tử Vi',
            'description' => 'Tổng hợp 6 trụ Thiên Bàn, Lai Nhân cung, Chủ Mệnh/Chủ Thân và Tam Hợp tuổi.',
            'suggested_questions' => [
                'Lai Nhân cung đóng ở vị trí này cho thấy nhân duyên và nghiệp quả trọng tâm nằm ở đâu?',
                'Ý nghĩa thực tế của sao Chủ Mệnh và Chủ Thân trên lá số của mình là gì?',
            ],
        ],
    ];

    protected TuViSafetyGuardService $safetyGuard;

    protected ScopedToolAiGuardService $scopedGuard;

    protected TuViKnowledgeRuleEngine $ruleEngine;

    public function __construct(
        protected LlmProviderInterface $provider,
        protected AiGovernanceService $governance,
        ?TuViSafetyGuardService $safetyGuard = null,
        ?ScopedToolAiGuardService $scopedGuard = null,
        ?TuViKnowledgeRuleEngine $ruleEngine = null
    ) {
        $this->safetyGuard = $safetyGuard ?? new TuViSafetyGuardService;
        $this->scopedGuard = $scopedGuard ?? app(ScopedToolAiGuardService::class);
        $this->ruleEngine = $ruleEngine ?? app(TuViKnowledgeRuleEngine::class);
    }

    /**
     * Luận giải chuyên sâu 6 tầng + trọn bộ 12 cung cho 1 lá số cá nhân (2 pha AI siêu chi tiết).
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

        if (! $allowAi) {
            return $fallback;
        }

        $isPartialNoHour = ($chart['status'] ?? '') === 'partial_no_birth_hour';

        try {
            if (function_exists('set_time_limit') && (int) ini_get('max_execution_time') > 0 && (int) ini_get('max_execution_time') < 300) {
                @set_time_limit(300);
            }
            $startedAt = microtime(true);
            $totalBudgetSeconds = 275; // Giữ đệm an toàn dưới ngưỡng 300s (5 phút) của PHP

            $policy = $this->governance->getPolicy();
            if (! empty($policy['kill_switch']) || ! $this->provider->isAvailable()) {
                return $fallback;
            }

            $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
            $promptContext = $isPartialNoHour
                ? $this->buildAnonymousPartialBrief($chart, $focusTopic)
                : $this->buildAnonymousChartBrief($chart, $ruleAnalysis, $focusTopic);

            $systemPrompt = $isPartialNoHour
                ? implode("\n", [
                    'Bạn là Học giả Tử Vi & Ngũ Hành Nạp Âm Nam Phái Việt Nam điềm đạm, uyên bác và nhân văn của Mây Hẹn – Thiên Mệnh.',
                    'TRẠNG THÁI ĐẶC BIỆT: Người dùng CHƯA BIẾT GIỜ SINH (chế độ partial_no_birth_hour).',
                    'NGUYÊN TẮC CỨNG:',
                    '1. TUYỆT ĐỐI KHÔNG BỊA 12 Cung, Cung Mệnh, Cung Thân, Ngũ Hành Cục, 14 Chính Tinh, Tứ Hóa hay Đại Hạn. Luôn minh bạch rằng phần luận dựa trên Bản Mệnh Nạp Âm, Can Chi Năm/Tháng/Ngày, Địa Chi Con Giáp và Năm xem hạn.',
                    '2. TUYỆT ĐỐI KHÔNG chẩn đoán bệnh, KHÔNG dự đoán tuổi thọ/tử vong/sinh con/giới tính thai nhi/ly hôn/ngoại tình, KHÔNG gạ cúng giải hạn, KHÔNG chấm % hợp tuổi.',
                    '3. VIẾT SIÊU CHI TIẾT, SÂU SẮC, CHIA ĐOẠN MẠCH LẠC (\n\n): giải thích rõ cơ chế Ngũ Hành, Thiên Can, Địa Chi và ứng dụng thực tế. Không viết chung chung.',
                    '4. Trả về DUY NHẤT 1 JSON hợp lệ theo schema:',
                    '{"headline":"1 câu chân dung tinh tế gắn với Bản Mệnh Nạp Âm & Tuổi","core_message":"6-8 câu trả lời trực tiếp chủ đề quan tâm và giải thích giá trị khi bổ sung giờ sinh","layer_1_overview":"3 đoạn chi tiết (~300 chữ) về Bản Mệnh Nạp Âm, Can Chi Năm-Tháng-Ngày và nội lực tự nhiên","layer_3_combinations":"2 đoạn chi tiết (~220 chữ) phân tích tương tác Can Chi, Tam Hợp, Lục Hợp, Xung nhịp và cách tiết chế","career_orientation":"2 đoạn chi tiết (~220 chữ) định hướng công việc, phong cách làm việc và cách tích lũy theo Ngũ Hành Bản Mệnh","layer_4_love_family":"2 đoạn chi tiết (~220 chữ) về tâm lý tình cảm, giao tiếp hôn nhân và hòa hợp gia đình","health_and_network":"2 đoạn chi tiết (~180 chữ) về cân bằng năng lượng tinh thần và ứng xử bạn bè/cộng sự","layer_5_life_stages":"2 đoạn chi tiết (~200 chữ) về nhịp vận trình năm xem xét theo Ngũ Hành & Địa Chi","layer_6_action_plan":{"today":"2 câu hành động hôm nay","this_week":"2 câu ưu tiên tuần này","next_30_days":"2 câu mục tiêu 30 ngày","long_term":"2 câu định hướng dài hạn"},"strengths":["Thế mạnh cụ thể 1","Thế mạnh cụ thể 2","Thế mạnh cụ thể 3","Thế mạnh cụ thể 4","Thế mạnh cụ thể 5"],"watch_outs":["Lưu tâm cụ thể 1","Lưu tâm cụ thể 2","Lưu tâm cụ thể 3","Lưu tâm cụ thể 4"],"reflection_question":"1 câu hỏi tự chiêm nghiệm sâu sắc"}',
                ])
                : implode("\n", [
                    'Bạn là Đại Học Giả Tử Vi Đẩu Số Nam Phái Việt Nam uyên thâm, điềm đạm và nhân văn của Mây Hẹn – Thiên Mệnh.',
                    'THÔNG ĐIỆP: "Hiểu mình, hiểu người, vun đắp nhân duyên."',
                    'NGUYÊN TẮC CỨNG:',
                    '1. TUYỆT ĐỐI chỉ luận dựa trên các cung, sao, độ sáng, Tứ Hóa, phụ tinh, vòng sao, Tuần/Triệt, cách cục và vận hạn có trong <verified_tuvi_chart>. KHÔNG tự thêm sao, KHÔNG tự bịa cách cục ngoài danh sách đã xác minh.',
                    '2. TUYỆT ĐỐI KHÔNG chẩn đoán bệnh cụ thể, KHÔNG dự đoán tuổi thọ/tử vong/sinh con/giới tính thai nhi, KHÔNG khẳng định ngoại tình/ly hôn/không được cưới, KHÔNG gạ cúng giải hạn, KHÔNG chấm % hợp tuổi.',
                    '3. ĐỘ DÀI & ĐỘ SÂU SIÊU CHI TIẾT: Viết phân tích sâu sắc, có dẫn chứng sao thật (kèm độ sáng M/V/Đ/B/H), chia đoạn bằng \\n\\n, phân tích cả mặt thuận (Cát tinh/Đắc địa/Hóa Lộc-Quyền-Khoa) lẫn mặt cần rèn (Sát tinh/Hãm địa/Hóa Kỵ/Tuần-Triệt) và giải pháp thực tế.',
                    '4. Trả về DUY NHẤT 1 JSON hợp lệ theo schema:',
                    '{"headline":"1 câu chân dung tinh tế, cá nhân hóa","core_message":"6-8 câu tổng hợp tinh túy lá số, trục Mệnh-Thân-Lai Nhân và giải đáp trực tiếp trọng tâm người dùng chọn","layer_1_overview":"3 đoạn phân tích sâu (~350-450 chữ): Đoạn 1 về Bản Mệnh Nạp Âm, Ngũ Hành Cục, quan hệ Cục-Mệnh và Âm Dương thuận/nghịch lý; Đoạn 2 về Cung Mệnh (chính tinh, phụ tinh, độ sáng) & Chủ Mệnh/Chủ Thân; Đoạn 3 về Cung Thân, Lai Nhân cung và định hướng trưởng thành","layer_3_combinations":"3 đoạn phân tích sâu (~320-400 chữ): Phân tích chi tiết Tam Phương Tứ Chính quanh Mệnh, tác động của 4 sao Tứ Hóa (Lộc/Quyền/Khoa/Kỵ), ảnh hưởng của Tuần Không / Triệt Lộ và các Cách Cục đã xác minh","career_orientation":"3 đoạn phân tích sâu (~300-380 chữ): Phân tích trục Mệnh-Quan Lộc-Tài Bạch-Thiên Di, môi trường công việc phù hợp, cách tạo & giữ tài sản, phong cách lãnh đạo hoặc chuyên môn sâu","layer_4_love_family":"3 đoạn phân tích sâu (~300-380 chữ): Phân tích Cung Phu Thê, sự nâng đỡ của Phúc Đức, nền tảng tổ ấm Điền Trạch và Tử Tức; giao tiếp cặp đôi và ứng xử gia đình hai bên","health_and_network":"2 đoạn phân tích sâu (~220-280 chữ): Phân tích cân bằng tâm-thân ở Tật Ách-Phúc Đức (tuyệt đối không phán bệnh y khoa) và mạng lưới đồng nghiệp, bạn bè, cha mẹ, anh em ở Nô Bộc-Phụ Mẫu-Huynh Đệ","layer_5_life_stages":"3 đoạn phân tích sâu (~300-380 chữ): Đánh giá Đại Hạn 10 năm hiện tại, Tiểu Hạn và Lưu Thái Tuế năm đang xem, kèm nhịp chiến lược cho 4 giai đoạn trong năm nay","layer_6_action_plan":{"today":"2-3 câu hành động thực tế hôm nay","this_week":"2-3 câu trao đổi/việc ưu tiên tuần này","next_30_days":"2-3 câu kế hoạch rèn luyện 30 ngày","long_term":"2-3 câu định hướng 3-5 năm theo Đại Hạn"},"strengths":["Thế mạnh chi tiết gắn tên sao 1","Thế mạnh 2","Thế mạnh 3","Thế mạnh 4","Thế mạnh 5"],"watch_outs":["Lưu tâm gắn tên sao 1","Lưu tâm 2","Lưu tâm 3","Lưu tâm 4"],"reflection_question":"1 câu hỏi tự chiêm nghiệm sâu sắc"}',
                ]);

            $result = $this->provider->completeJson(
                systemPrompt: $systemPrompt,
                userPrompt: $promptContext,
                model: $model,
                temperature: 0.34,
                maxTokens: 4600,
                timeoutSeconds: 140
            );

            if (! empty($result['ok']) && is_array($result['data'])) {
                $d = $result['data'];
                $headline = $this->cleanMultilineText((string) ($d['headline'] ?? ''));
                $core = $this->cleanMultilineText((string) ($d['core_message'] ?? ''));
                $l1 = $this->cleanMultilineText((string) ($d['layer_1_overview'] ?? ''));

                if ($headline !== '' && $core !== '' && $l1 !== '') {
                    $palaceHighlights = $fallback['layer_2_palace_highlights'];

                    // Pha 2: Luận chuyên sâu ĐỦ 12 CUNG riêng biệt trong phạm vi ngân sách thời gian còn lại
                    $remainingBudget = (int) floor($totalBudgetSeconds - (microtime(true) - $startedAt));
                    if (! $isPartialNoHour && $remainingBudget >= 25) {
                        $aiPalaces = $this->interpretAllTwelvePalacesWithAi($promptContext, $model, min(145, $remainingBudget));
                        foreach ($aiPalaces as $pCode => $pText) {
                            $palaceHighlights[$pCode] = $pText;
                        }
                    }

                    $planRaw = is_array($d['layer_6_action_plan'] ?? null) ? $d['layer_6_action_plan'] : [];
                    $plan = [
                        'today' => $this->cleanMultilineText((string) ($planRaw['today'] ?? '')) ?: $fallback['layer_6_action_plan']['today'],
                        'this_week' => $this->cleanMultilineText((string) ($planRaw['this_week'] ?? '')) ?: $fallback['layer_6_action_plan']['this_week'],
                        'next_30_days' => $this->cleanMultilineText((string) ($planRaw['next_30_days'] ?? '')) ?: $fallback['layer_6_action_plan']['next_30_days'],
                        'long_term' => $this->cleanMultilineText((string) ($planRaw['long_term'] ?? '')) ?: $fallback['layer_6_action_plan']['long_term'],
                    ];

                    $strengths = is_array($d['strengths'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => $this->cleanMultilineText((string) $s), $d['strengths'])))
                        : $fallback['strengths'];
                    $watchOuts = is_array($d['watch_outs'] ?? null)
                        ? array_values(array_filter(array_map(fn ($s) => $this->cleanMultilineText((string) $s), $d['watch_outs'])))
                        : $fallback['watch_outs'];

                    $candidate = array_merge($fallback, [
                        'headline' => $headline,
                        'core_message' => $core,
                        'layer_1_overview' => $l1,
                        'layer_2_palace_highlights' => $palaceHighlights,
                        'layer_3_combinations' => $this->cleanMultilineText((string) ($d['layer_3_combinations'] ?? '')) ?: $fallback['layer_3_combinations'],
                        'career_orientation' => $this->cleanMultilineText((string) ($d['career_orientation'] ?? '')) ?: $fallback['career_orientation'],
                        'layer_4_love_family' => $this->cleanMultilineText((string) ($d['layer_4_love_family'] ?? '')) ?: $fallback['layer_4_love_family'],
                        'health_and_network' => $this->cleanMultilineText((string) ($d['health_and_network'] ?? '')) ?: $fallback['health_and_network'],
                        'layer_5_life_stages' => $this->cleanMultilineText((string) ($d['layer_5_life_stages'] ?? '')) ?: $fallback['layer_5_life_stages'],
                        'layer_6_action_plan' => $plan,
                        'strengths' => array_slice($strengths !== [] ? $strengths : $fallback['strengths'], 0, 5),
                        'watch_outs' => array_slice($watchOuts !== [] ? $watchOuts : $fallback['watch_outs'], 0, 4),
                        'reflection_question' => $this->cleanMultilineText((string) ($d['reflection_question'] ?? '')) ?: $fallback['reflection_question'],
                        'ai_powered' => true,
                        'ai_model' => (string) ($result['model'] ?? $model ?? 'configured-ai'),
                        'ai_status' => 'completed',
                        'provenance' => 'ai_personalized',
                    ]);

                    $groundedTerms = $this->scopedGuard->extractGroundedTerms([
                        $chart['summary'] ?? [],
                        $chart['birth'] ?? [],
                        $chart['palaces_by_code'] ?? [],
                    ]);
                    $outputVerify = $this->scopedGuard->verifyScopedOutput(
                        $core."\n\n".$l1."\n\n".($candidate['layer_3_combinations'] ?? ''),
                        $promptContext,
                        'tuvi',
                        $groundedTerms,
                        allowMultiline: true
                    );

                    if ($this->safetyGuard->validateAiOutput($candidate)['valid'] && $outputVerify['safe']) {
                        return $candidate;
                    }
                }
            }
        } catch (Throwable) {
            // Giữ nguyên bản luận giải chuyên sâu chuẩn hóa từ kho dữ liệu đã thẩm định
        }

        return $fallback;
    }

    /**
     * Pha 2: Yêu cầu AI viết luận giải chuyên sâu 5-7 câu cho ĐỦ 12 CUNG trên Thiên Bàn.
     *
     * @return array<string, string>
     */
    protected function interpretAllTwelvePalacesWithAi(string $promptContext, ?string $model, int $timeoutSeconds = 135): array
    {
        try {
            $sys = implode("\n", [
                'Bạn là Đại Học Giả Tử Vi Đẩu Số Nam Phái Việt Nam của Mây Hẹn – Thiên Mệnh.',
                'NHIỆM VỤ: Dựa ĐÚNG vào <verified_tuvi_chart>, hãy viết bài luận chuyên sâu riêng biệt cho ĐỦ CẢ 12 CUNG Tử Vi.',
                'YÊU CẦU TỪNG CUNG: Mỗi cung viết 5-7 câu sâu sắc (~130-170 chữ), bắt buộc nhắc đúng Chính tinh & độ sáng (hoặc Vô Chính Diệu + cung xung chiếu), Phụ tinh nổi bật, Tứ Hóa (nếu có), Tam Phương Tứ Chính, Tuần/Triệt (nếu có) và gợi ý ứng dụng thực tế. Không chẩn đoán bệnh, không phán ly hôn/tử vong/cúng giải hạn.',
                'Trả về DUY NHẤT JSON đúng 12 khóa:',
                '{"menh":"...","phu_mau":"...","phuc_duc":"...","dien_trach":"...","quan_loc":"...","no_boc":"...","thien_di":"...","tat_ach":"...","tai_bach":"...","tu_tuc":"...","phu_the":"...","huynh_de":"..."}',
            ]);

            $res = $this->provider->completeJson(
                systemPrompt: $sys,
                userPrompt: $promptContext,
                model: $model,
                temperature: 0.32,
                maxTokens: 4600,
                timeoutSeconds: max(25, $timeoutSeconds)
            );

            if (empty($res['ok']) || ! is_array($res['data'])) {
                return [];
            }

            $validCodes = ['menh', 'phu_mau', 'phuc_duc', 'dien_trach', 'quan_loc', 'no_boc', 'thien_di', 'tat_ach', 'tai_bach', 'tu_tuc', 'phu_the', 'huynh_de'];
            $validated = [];
            foreach ($validCodes as $code) {
                $text = $this->cleanMultilineText((string) ($res['data'][$code] ?? ''));
                if ($text === '' || mb_strlen($text, 'UTF-8') < 40) {
                    continue;
                }
                if (! $this->safetyGuard->validateAiOutput(['palace' => $text])['valid']) {
                    continue;
                }
                $verify = $this->scopedGuard->verifyScopedOutput($text, $promptContext, 'tuvi', [$code], allowMultiline: true);
                if ($verify['safe']) {
                    $validated[$code] = $verify['clean_answer'];
                }
            }

            return $validated;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Xác định phạm vi hỏi đáp hợp lệ (cung cụ thể hoặc từng mục luận giải).
     *
     * @param  array<string, mixed>  $chart
     * @return array{
     *     valid: bool,
     *     scope_code: ?string,
     *     scope_label: string,
     *     scope_type: 'general'|'palace'|'section',
     *     palace_code: ?string,
     *     section_key: ?string
     * }
     */
    public function resolveQuestionScope(array $chart, ?string $palaceCode = null, ?string $sectionKey = null): array
    {
        $palacesByCode = is_array($chart['palaces_by_code'] ?? null) ? $chart['palaces_by_code'] : [];
        $rawSection = trim((string) ($sectionKey ?? ''));
        $rawPalace = trim((string) ($palaceCode ?? ''));

        // Hỗ trợ gửi section_key trực tiếp hoặc gửi scope_code 'sec:...' trong trường palace_code
        if ($rawSection !== '') {
            if (isset(self::SECTION_DEFINITIONS[$rawSection])) {
                $def = self::SECTION_DEFINITIONS[$rawSection];

                return [
                    'valid' => true,
                    'scope_code' => $def['scope_code'],
                    'scope_label' => $def['label'],
                    'scope_type' => 'section',
                    'palace_code' => null,
                    'section_key' => $rawSection,
                ];
            }

            return [
                'valid' => false,
                'scope_code' => null,
                'scope_label' => 'Phạm vi không hợp lệ',
                'scope_type' => 'general',
                'palace_code' => null,
                'section_key' => null,
            ];
        }

        if ($rawPalace !== '' && str_starts_with($rawPalace, 'sec:')) {
            foreach (self::SECTION_DEFINITIONS as $key => $def) {
                if ($def['scope_code'] === $rawPalace) {
                    return [
                        'valid' => true,
                        'scope_code' => $def['scope_code'],
                        'scope_label' => $def['label'],
                        'scope_type' => 'section',
                        'palace_code' => null,
                        'section_key' => $key,
                    ];
                }
            }

            return [
                'valid' => false,
                'scope_code' => null,
                'scope_label' => 'Phạm vi không hợp lệ',
                'scope_type' => 'general',
                'palace_code' => null,
                'section_key' => null,
            ];
        }

        if ($rawPalace !== '') {
            $p = $palacesByCode[$rawPalace] ?? null;
            $validPalaceCodes = ['menh', 'phu_mau', 'phuc_duc', 'dien_trach', 'quan_loc', 'no_boc', 'thien_di', 'tat_ach', 'tai_bach', 'tu_tuc', 'phu_the', 'huynh_de'];
            if (in_array($rawPalace, $validPalaceCodes, true)) {
                return [
                    'valid' => true,
                    'scope_code' => $rawPalace,
                    'scope_label' => (string) ($p['name'] ?? $rawPalace),
                    'scope_type' => 'palace',
                    'palace_code' => $rawPalace,
                    'section_key' => null,
                ];
            }

            return [
                'valid' => false,
                'scope_code' => null,
                'scope_label' => 'Cung không hợp lệ',
                'scope_type' => 'general',
                'palace_code' => null,
                'section_key' => null,
            ];
        }

        return [
            'valid' => true,
            'scope_code' => null,
            'scope_label' => 'Tổng thể lá số',
            'scope_type' => 'general',
            'palace_code' => null,
            'section_key' => null,
        ];
    }

    /**
     * Trợ Lý Tử Vi AI — Hỏi sâu khóa chặt vào từng phần luận giải / từng cung cụ thể trên lá số.
     * Mọi input & output đều bắt buộc đi qua kiểm định Zero-Trust (ScopedToolAiGuardService + TuViSafetyGuardService).
     *
     * @param  list<array{question: string, answer: string, scope_code?: ?string}>  $history
     * @return array{
     *     answer: string,
     *     ai_powered: bool,
     *     ai_model: ?string,
     *     blocked?: bool,
     *     code?: ?string,
     *     scope_code?: ?string,
     *     scope_label?: string
     * }
     */
    public function answerFollowup(
        TuViChart $chartModel,
        string $question,
        ?string $palaceCode = null,
        array $history = [],
        ?string $sectionKey = null
    ): array {
        $chart = is_array($chartModel->chart_payload_json) ? $chartModel->chart_payload_json : [];
        $interpretation = is_array($chartModel->interpretation_json) ? $chartModel->interpretation_json : [];
        $scope = $this->resolveQuestionScope($chart, $palaceCode, $sectionKey);

        if (! $scope['valid']) {
            return [
                'answer' => 'Mục hoặc cung bạn chọn để hỏi sâu chưa hợp lệ trên lá số này.',
                'ai_powered' => false,
                'ai_model' => null,
                'blocked' => true,
                'code' => 'INVALID_SCOPE',
                'scope_code' => null,
                'scope_label' => 'Không hợp lệ',
            ];
        }

        // Ưu tiên đưa đúng cung đang chọn + toàn bộ phụ tinh/vòng sao/lưu niên lên đầu danh sách neo dữ kiện
        $focusedPalace = ($scope['scope_type'] === 'palace' && ! empty($scope['palace_code']))
            ? ($chart['palaces_by_code'][$scope['palace_code']] ?? null)
            : null;
        $matchedStars = $this->ruleEngine->lookupStarsInText($question);

        $groundedTerms = $this->scopedGuard->extractGroundedTerms([
            $focusedPalace,
            $matchedStars,
            $scope['scope_label'],
            $chart['summary'] ?? [],
            $chart['birth'] ?? [],
            $chart['palaces_by_code'] ?? [],
        ]);

        // Lớp 1: Kiểm tra đầu vào Zero-Trust & Khóa phạm vi câu hỏi
        $inputGuard = $this->scopedGuard->inspectScopedQuestion($question, 'tuvi', $groundedTerms, 320);
        if (! $inputGuard['allowed']) {
            return [
                'answer' => (string) ($inputGuard['message'] ?? 'Câu hỏi nằm ngoài phạm vi lá số Tử Vi hiện tại.'),
                'ai_powered' => false,
                'ai_model' => null,
                'blocked' => true,
                'code' => $inputGuard['code'],
                'scope_code' => $scope['scope_code'],
                'scope_label' => $scope['scope_label'],
            ];
        }

        $cleanQuestion = $inputGuard['clean_question'];
        $verifiedContext = $this->buildScopedSectionContext($chart, $interpretation, $scope, $history, $cleanQuestion);
        $fallbackAnswer = $this->buildScopedSectionFallback($chart, $interpretation, $scope, $cleanQuestion);

        try {
            $policy = $this->governance->getPolicy();
            if (empty($policy['kill_switch']) && $this->provider->isAvailable()) {
                $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;

                $systemPrompt = implode("\n", [
                    'Bạn là Trợ lý Tử Vi Chuyên Sâu & Nhân Văn của Mây Hẹn – Thiên Mệnh, đang giải đáp sâu ĐÚNG MỘT PHẦN / MỘT CUNG trên lá số đã luận.',
                    $this->scopedGuard->buildZeroTrustSystemPreamble('verified_tuvi_section_context'),
                    'QUY TẮC CHUYÊN MÔN:',
                    '1. Trả lời sâu sắc, trực diện vào phạm vi đang hỏi ("'.$scope['scope_label'].'") trong 6–8 câu (có thể chia 2 đoạn bằng \\n\\n cho dễ đọc).',
                    '2. Bắt buộc viện dẫn chính xác tên Chính tinh, độ sáng, Phụ tinh, Tứ Hóa, Tam Phương Tứ Chính hoặc nội dung đã luận có trong <verified_tuvi_section_context>. TUYỆT ĐỐI không tự sửa lá số hay bịa sao mới.',
                    '3. Không chẩn đoán bệnh, không dự đoán tuổi thọ/tử vong/ly hôn/ngoại tình/sinh con, không xúi cúng giải hạn, không chấm % hợp tuổi.',
                    '4. Trả về DUY NHẤT JSON: {"answer":"Câu trả lời sâu sắc, bám sát phần đã luận"}',
                ]);

                $userPrompt = $verifiedContext."\n".$inputGuard['wrapped_question'];

                $res = $this->provider->completeJson(
                    systemPrompt: $systemPrompt,
                    userPrompt: $userPrompt,
                    model: $model,
                    temperature: 0.26,
                    maxTokens: 1100,
                    timeoutSeconds: 100
                );

                if (! empty($res['ok']) && is_array($res['data'])) {
                    $rawAns = (string) ($res['data']['answer'] ?? '');
                    // Lớp 2: Kiểm tra đầu ra bắt buộc bằng code cứng (Deterministic Gate) trước khi trả khách
                    $outputVerify = $this->scopedGuard->verifyScopedOutput(
                        $rawAns,
                        $verifiedContext,
                        'tuvi',
                        $groundedTerms,
                        allowMultiline: true
                    );
                    $tuviSafety = $this->safetyGuard->validateAiOutput(['answer' => $outputVerify['clean_answer']]);

                    if ($outputVerify['safe'] && $tuviSafety['valid'] && $outputVerify['clean_answer'] !== '') {
                        return [
                            'answer' => $outputVerify['clean_answer'],
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
            // Fallback chuẩn hóa đã được kiểm chứng từ dữ liệu gốc
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
     * Bản luận giải chuyên sâu 6 tầng + trọn 12 cung chuẩn hóa hoàn toàn từ kho dữ liệu đã thẩm định (Zero Hallucination).
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
            $pillars = $chart['birth']['pillars'] ?? [];

            return [
                'headline' => "Nền tảng Bản Mệnh {$napAm['name']} — ".($z['animal'] ?? ($pillars['year']['label'] ?? '')),
                'core_message' => ($z['traits'] ?? '')." Với Bản Mệnh {$napAm['name']} (hành {$napAm['element']}), khí chất nổi bật là cách bạn tiếp cận vấn đề từ giá trị cốt lõi. Do chưa có giờ sinh chính xác, hệ thống chỉ trình bày những luận giải trung thực từ năm/tháng/ngày sinh và Bản Mệnh Nạp Âm, tuyệt đối không suy đoán 12 cung hay gán giờ giả. Khi tìm được khoảng giờ sinh, bạn có thể lập phiên bản mới để mở khóa đủ 12 cung và Đại Hạn.",
                'layer_1_overview' => 'Năm sinh Âm lịch '.($pillars['year']['label'] ?? '').' (tháng '.($pillars['month']['label'] ?? '').', ngày '.($pillars['day']['label'] ?? '').") mang Bản Mệnh {$napAm['name']} thuộc hành {$napAm['element']}.\n\nNgười mang hành {$napAm['element']} theo Ngũ Hành Nạp Âm thường có nhịp cảm nhận đời sống đặc trưng: ".($z['traits'] ?? 'bền bỉ, coi trọng chữ tín và từng bước tích lũy nội lực.').' Để kích hoạt trọn vẹn 12 Thiên Mệnh Bài, Cung Mệnh, Cung Thân và dòng thời gian Đại Hạn 10 năm, bạn nên đối chiếu giấy khai sinh hoặc chọn chế độ khoảng giờ sinh.',
                'layer_2_palace_highlights' => [],
                'layer_3_combinations' => 'Chưa đủ dữ liệu giờ sinh để an định Cung Mệnh, Tam Phương Tứ Chính, Tứ Hóa theo cung và Cách Cục 12 cung. Trọng tâm hiện tại nằm ở sự điều hòa giữa Thiên Can và Địa Chi năm–tháng–ngày sinh.',
                'career_orientation' => "Dựa trên Bản Mệnh {$napAm['name']} (hành {$napAm['element']}), bạn phát huy tốt nhất trong những môi trường tôn trọng giá trị thực chất, có lộ trình học hỏi rõ ràng và không quá phụ thuộc vào quyết định bốc đồng. Hãy ưu tiên xây dựng thế mạnh chuyên môn lõi trước khi mở rộng nhiều nhánh.",
                'layer_4_love_family' => (string) ($z['love'] ?? 'Sự thấu hiểu, tôn trọng khác biệt tính khí và trò chuyện minh bạch luôn là gốc rễ bền vững của tình yêu và hôn nhân.'),
                'health_and_network' => 'Khi chưa có giờ sinh, điểm cốt yếu để cân bằng tinh thần và quan hệ xã hội là giữ nhịp nghỉ ngơi điều độ, hạn chế gồng gánh kỳ vọng quá mức từ người xung quanh và chọn bạn đồng hành có chung hệ giá trị.',
                'layer_5_life_stages' => 'Đại Hạn 10 năm và Tiểu Hạn từng năm phụ thuộc chính xác vào Cung Mệnh & Ngũ Hành Cục nên cần có giờ sinh để tính toán. Hiện tại bạn có thể tập trung quan sát nhịp tương tác Ngũ Hành của năm đang xem so với hành Bản Mệnh '.$napAm['element'].'.',
                'layer_6_action_plan' => [
                    'today' => 'Hỏi lại gia đình hoặc xem giấy khai sinh, sổ y bạ để xác nhận khoảng giờ sinh gần đúng nhất.',
                    'this_week' => (string) ($z['advice'] ?? 'Giữ nhịp sống cân bằng và tập trung vào ưu tiên quan trọng nhất trong tuần này.'),
                    'next_30_days' => 'Khi có khoảng giờ sinh (ví dụ từ 5h đến 9h sáng), quay lại chế độ So sánh lá số khả dĩ để khám phá sự thay đổi 12 cung.',
                    'long_term' => 'Nuôi dưỡng các giá trị sống bền vững, nâng cao nội lực chuyên môn và giữ mối quan hệ gia đình ấm áp.',
                ],
                'strengths' => [
                    "Khí chất bản mệnh {$napAm['name']} (hành {$napAm['element']}) rõ ràng, dễ nhận diện thế mạnh nền tảng.",
                    (string) ($z['traits'] ?? 'Năng lực thích ứng và học hỏi bền bỉ theo thời gian.'),
                    'Minh bạch giới hạn dữ liệu, tránh bị dẫn dắt bởi các kết quả suy đoán thiếu giờ sinh.',
                ],
                'watch_outs' => [
                    'Không nên tin các lời phán 12 cung khi chưa có giờ sinh xác thực.',
                    'Tránh quyết định lớn chỉ dựa vào phỏng đoán cảm tính ngắn hạn.',
                ],
                'reflection_question' => 'Nếu nhìn lại 1 năm vừa qua, thói quen tốt nào đã mang lại cho bạn cảm giác bình an và tự chủ nhất?',
                'ecosystem_suggestions' => $this->buildEcosystemLinks($focusTopic),
                'ai_powered' => false,
                'ai_model' => null,
                'ai_status' => 'fallback',
                'provenance' => 'mayhen_modern_guide',
            ];
        }

        $sum = $chart['summary'];
        $digests = $ruleAnalysis['palace_digests'] ?? [];
        $deepReports = [];
        foreach ($ruleAnalysis['palace_deep_reports'] ?? [] as $rep) {
            if (! empty($rep['code'])) {
                $deepReports[$rep['code']] = $rep;
            }
        }
        $patterns = $ruleAnalysis['verified_patterns'] ?? [];
        $tb = $ruleAnalysis['thien_ban_overview'] ?? [];
        $career = $ruleAnalysis['career_orientation'] ?? [];

        $menhD = $digests['menh'] ?? [];
        $phuTheD = $digests['phu_the'] ?? [];
        $quanD = $digests['quan_loc'] ?? [];
        $taiD = $digests['tai_bach'] ?? [];
        $tatD = $digests['tat_ach'] ?? [];
        $noD = $digests['no_boc'] ?? [];

        // Tạo diễn giải chi tiết cho ĐỦ 12 CUNG từ ruleAnalysis (chính tinh + điểm sáng + lưu tâm + Tam Phương Tứ Chính)
        $all12Codes = ['menh', 'phu_mau', 'phuc_duc', 'dien_trach', 'quan_loc', 'no_boc', 'thien_di', 'tat_ach', 'tai_bach', 'tu_tuc', 'phu_the', 'huynh_de'];
        $palaceHighlights = [];
        foreach ($all12Codes as $pCode) {
            $d = $digests[$pCode] ?? [];
            $rep = $deepReports[$pCode] ?? [];
            $pName = $rep['name'] ?? ($chart['palaces_by_code'][$pCode]['name'] ?? $pCode);
            $canChi = $rep['can_chi'] ?? ($chart['palaces_by_code'][$pCode]['can_chi'] ?? '');
            $posText = '';
            if (! empty($rep['positive_points'][0])) {
                $posText = 'Điểm sáng nổi bật đến từ '.$rep['positive_points'][0]['star'].': '.$rep['positive_points'][0]['text'];
            }
            $cautionText = '';
            if (! empty($rep['caution_points'][0])) {
                $cautionText = 'Điều cần điều tiết nằm ở '.$rep['caution_points'][0]['star'].': '.$rep['caution_points'][0]['text'];
            }

            $parts = array_filter([
                "{$pName} ({$canChi}) hội tụ ".($d['stars_headline'] ?? 'các sao bản cung').'.',
                (string) ($d['lead_star_insight'] ?? ''),
                (string) ($d['modern_meaning'] ?? ''),
                $posText,
                $cautionText,
                ! empty($rep['tptc_summary']) ? $rep['tptc_summary'].'.' : '',
            ]);
            $palaceHighlights[$pCode] = implode(' ', $parts);
        }

        $patternNames = array_map(fn (array $p): string => $p['name'], $patterns);
        $patternText = $patternNames !== []
            ? 'Lá số ghi nhận các cấu trúc đã xác minh theo Nam Phái: '.implode(', ', $patternNames).'. '.($patterns[0]['plain_explanation'] ?? '').(! empty($patterns[0]['expert_explanation']) ? ' '.$patterns[0]['expert_explanation'] : '')
            : 'Các cung trên lá số vận hành cân bằng theo tương tác Tam Phương Tứ Chính và Tứ Hóa nguyên cục, không bị chi phối bởi một cách cục cực đoan đơn lẻ.';

        $dh = $sum['current_dai_han'] ?? null;
        $ln = $sum['current_luu_nien'] ?? null;
        $thLoc = $sum['tu_hoa']['loc'] ?? [];
        $thQuyen = $sum['tu_hoa']['quyen'] ?? [];
        $thKhoa = $sum['tu_hoa']['khoa'] ?? [];
        $thKy = $sum['tu_hoa']['ky'] ?? [];

        return [
            'headline' => sprintf(
                'Thiên Bàn %s • %s — Mệnh tại %s (%s)',
                $sum['nap_am']['name'],
                $sum['cuc']['name'],
                $sum['menh_branch_name'],
                $menhD['stars_headline'] ?? ''
            ),
            'core_message' => sprintf(
                'Đương số mang Bản Mệnh %s (hành %s) phối hợp %s, Cung Mệnh an tại %s (%s), Cung Thân cư %s và Lai Nhân cung tại %s. %s %s Hệ thống đã tổng hợp đầy đủ cả 12 cung, trục Tam Phương Tứ Chính, Tứ Hóa và Đại Hạn %d–%d tuổi để bạn soi chiếu chi tiết từng khía cạnh đời sống.',
                $sum['nap_am']['name'],
                $sum['nap_am']['element'],
                $sum['cuc']['name'],
                $sum['menh_branch_name'],
                $menhD['stars_headline'] ?? '',
                $sum['than_palace_name'],
                $tb['lai_nhan_palace_name'] ?? 'lá số',
                $menhD['lead_star_insight'] ?? '',
                $tb['cuc_menh_relation'] ?? '',
                $dh['age_start'] ?? 0,
                $dh['age_end'] ?? 0
            ),
            'layer_1_overview' => sprintf(
                "Đương số mang Bản Mệnh %s (hành %s), %s, %s (%s). %s\n\nCung Mệnh đóng tại %s (%s) phản ánh nền tảng tính cách và nội lực bẩm sinh: %s Chủ Mệnh %s và Chủ Thân %s là hai trụ điều tiết khí chất từ bên trong.\n\nCung Thân đồng cung %s và Lai Nhân cung tại %s cho thấy từ giai đoạn trưởng thành trở đi, tâm thế sống và những bước ngoặt lớn sẽ xoay quanh chính lĩnh vực của cung này.",
                $sum['nap_am']['name'],
                $sum['nap_am']['element'],
                $sum['cuc']['name'],
                $chart['yin_yang_gender_label'] ?? '',
                $tb['am_duong_ly'] ?? '',
                $tb['cuc_menh_relation'] ?? '',
                $sum['menh_branch_name'],
                $menhD['stars_headline'] ?? '',
                $menhD['strength_hint'] ?? '',
                $tb['chu_menh'] ?: '—',
                $tb['chu_than'] ?: '—',
                $sum['than_palace_name'],
                $tb['lai_nhan_palace_name'] ?? 'lá số'
            ),
            'layer_2_palace_highlights' => $palaceHighlights,
            'layer_3_combinations' => sprintf(
                "%s\n\nVề bộ Tứ Hóa nguyên cục: Hóa Lộc đi cùng %s tại %s mở ra nguồn lực thuận lợi; Hóa Quyền cùng %s tại %s tạo năng lực chủ động và sức ảnh hưởng; Hóa Khoa cùng %s tại %s mang lại tư duy giải quyết vấn đề và quý nhân; trong khi Hóa Kỵ cùng %s tại %s nhắc nhở sự thận trọng, minh bạch và rà soát kỹ chi tiết thay vì chủ quan.",
                $patternText,
                $thLoc['star_name'] ?? '—',
                $thLoc['palace_name'] ?? 'lá số',
                $thQuyen['star_name'] ?? '—',
                $thQuyen['palace_name'] ?? 'lá số',
                $thKhoa['star_name'] ?? '—',
                $thKhoa['palace_name'] ?? 'lá số',
                $thKy['star_name'] ?? '—',
                $thKy['palace_name'] ?? 'lá số'
            ),
            'career_orientation' => sprintf(
                "%s\n\nCung Quan Lộc (%s) và Cung Tài Bạch (%s) phối hợp tạo nên nhịp phát triển riêng: %s Điểm then chốt để giữ vững thành quả tài chính là quản trị rủi ro tại vị trí có sát tinh hoặc Hóa Kỵ, ưu tiên năng lực chuyên môn lõi và cộng tác với người đáng tin.",
                (string) ($career['summary'] ?? ''),
                $quanD['stars_headline'] ?? '',
                $taiD['stars_headline'] ?? '',
                ($quanD['lead_star_insight'] ?? '').' '.($taiD['modern_meaning'] ?? '')
            ),
            'layer_4_love_family' => sprintf(
                "Xét Cung Phu Thê tại %s (%s): %s %s\n\nKhi đặt trong tam giác Phu Thê – Phúc Đức – Điền Trạch, đời sống tình cảm và hôn nhân được nâng đỡ mạnh nhất khi cả hai duy trì giao tiếp thẳng thắn trước các áp lực công việc, thống nhất nguyên tắc tài chính chung – riêng và tôn trọng nếp sinh hoạt gia đình hai bên.",
                $phuTheD['branch_name'] ?? '',
                $phuTheD['stars_headline'] ?? '',
                $phuTheD['lead_star_insight'] ?? '',
                $phuTheD['modern_meaning'] ?? ''
            ),
            'health_and_network' => sprintf(
                "Xét sự cân bằng tâm – thân tại Cung Tật Ách (%s): %s Điều quan trọng nhất là duy trì nhịp nghỉ ngơi khoa học, giải tỏa căng thẳng tinh thần kịp thời.\n\nVề mạng lưới quan hệ xã hội tại Cung Nô Bộc (%s): %s Kết hợp đối thoại ôn hòa với Phụ Mẫu và Huynh Đệ giúp bạn xây dựng hậu phương tinh thần vững chãi.",
                $tatD['stars_headline'] ?? '',
                $tatD['modern_meaning'] ?? 'Giữ tâm thế điềm tĩnh giúp cân bằng năng lượng nội tại.',
                $noD['stars_headline'] ?? '',
                $noD['modern_meaning'] ?? 'Chọn bạn bè và cộng sự đồng điệu về chữ tín sẽ giúp đi đường dài.'
            ),
            'layer_5_life_stages' => sprintf(
                "Ở tuổi %d (năm %d — %s), Đại Hạn 10 năm (%d–%d tuổi) đi qua %s; Tiểu Hạn đóng tại %s và Lưu Thái Tuế vận hành tại %s.\n\nĐây là giai đoạn phù hợp để tập trung củng cố các chủ đề trọng tâm của %s bằng kế hoạch từng quý rõ ràng, ưu tiên làm chắc những việc thuộc thế mạnh trước khi mở rộng quá nhiều hướng cùng lúc.",
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
                'today' => 'Mở xem 3 cung bạn quan tâm nhất (Mệnh, Quan Lộc, Phu Thê) trên bộ 12 Thiên Mệnh Bài và ghi lại 1 điểm khiến bạn thấy khớp với thực tế hiện tại.',
                'this_week' => 'Dành 30 phút trò chuyện thẳng thắn, ấm áp với người thương hoặc người thân về một ưu tiên chung trong công việc hoặc đời sống.',
                'next_30_days' => 'Chọn 1 điểm mạnh nổi bật từ trục Mệnh – Quan để biến thành thói quen hành động đo lường được mỗi tuần.',
                'long_term' => 'Bám sát chủ đề Đại Hạn hiện tại tại '.($dh['palace_name'] ?? 'lá số').', xây dựng quỹ dự phòng tài chính và nền tảng tổ ấm vững vàng.',
            ],
            'strengths' => [
                $menhD['strength_hint'] ?? 'Tư chất ổn định, có năng lực tự nhận thức và quan sát sắc bén.',
                'Điểm sáng Hóa Lộc đi cùng '.($thLoc['star_name'] ?? 'sao bản cung').' tại '.($thLoc['palace_name'] ?? 'lá số').' giúp mở rộng nguồn lực thuận lợi.',
                'Hóa Khoa tại '.($thKhoa['palace_name'] ?? 'lá số').' hỗ trợ tư duy mạch lạc và khả năng tự học vượt khó.',
                'Cung Thân cư '.$sum['than_palace_name'].' tạo định hướng rõ ràng cho chặng đường trưởng thành.',
            ],
            'watch_outs' => [
                $menhD['watch_out_hint'] ?? 'Tránh ôm đồm quá nhiều mục tiêu cùng lúc khiến phân tán nội lực.',
                'Chú ý giữ sự điềm đạm và minh bạch ở lĩnh vực '.($thKy['palace_name'] ?? 'có Hóa Kỵ').' thay vì suy diễn trong im lặng.',
                'Cân đối giữa tham vọng công việc và thời gian phục hồi năng lượng tinh thần.',
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
     * Tạo bản tóm tắt đầy đủ thông số ẩn danh cho 12 cung + Tam Phương Tứ Chính + Đại Hạn để LLM luận siêu chi tiết.
     *
     * @param  array<string, mixed>  $chart
     * @param  array<string, mixed>  $ruleAnalysis
     */
    protected function buildAnonymousChartBrief(array $chart, array $ruleAnalysis, string $focusTopic): string
    {
        $sum = $chart['summary'];
        $tb = $ruleAnalysis['thien_ban_overview'] ?? [];
        $relations = $ruleAnalysis['relations_by_palace'] ?? [];

        $lines = [
            '<verified_tuvi_chart>',
            'Trường phái: '.$chart['school_name'].' ('.$chart['rule_version'].')',
            'Chủ đề trọng tâm người dùng chọn: '.$focusTopic,
            'Âm dương giới tính tính vận: '.$chart['yin_yang_gender_label'].' ('.($tb['am_duong_ly'] ?? '').')',
            'Bản mệnh Nạp Âm: '.$sum['nap_am']['name'].' ('.$sum['nap_am']['element'].') | Cục: '.$sum['cuc']['name'].' ('.($tb['cuc_menh_relation'] ?? '').')',
            'Chủ Mệnh: '.($tb['chu_menh'] ?? '').' | Chủ Thân: '.($tb['chu_than'] ?? ''),
            'Cung Mệnh: '.$sum['menh_branch_name'].' | Cung Thân cư: '.$sum['than_palace_name'].' ('.$sum['than_branch_name'].') | Lai Nhân Cung: '.($tb['lai_nhan_palace_name'] ?? ''),
            'Tuần tại: '.implode(', ', $sum['tuan_branches']).' | Triệt tại: '.implode(', ', $sum['triet_branches']),
            'Cân lượng Tử Vi: '.($tb['can_luong']['label'] ?? '').' ('.($tb['can_luong']['tier'] ?? '').')',
            '12 CUNG CHI TIẾT (Đủ Chính tinh, Độ sáng, Tứ Hóa, Toàn bộ Phụ tinh, Vòng sao, Tuần/Triệt & Tam Phương Tứ Chính):',
        ];

        foreach ($chart['palaces_by_code'] as $code => $p) {
            $majors = $p['major_stars'] !== []
                ? implode(', ', array_map(fn (array $s): string => $s['name'].'('.$s['brightness'].')', $p['major_stars']))
                : 'Vô Chính Diệu';
            $tuHoa = $p['tu_hoa'] !== []
                ? ' | Tứ Hóa: '.implode(', ', array_map(fn (array $h): string => $h['name'].'('.$h['attached_star_name'].')', $p['tu_hoa']))
                : '';
            $aux = implode(', ', array_map(fn (array $a): string => $a['name'], $p['aux_stars'] ?? []));
            $kv = implode('+', array_filter([
                ! empty($p['has_tuan']) ? 'TUẦN' : '',
                ! empty($p['has_triet']) ? 'TRIỆT' : '',
            ]));
            $rel = $relations[$code] ?? [];
            $triadNames = implode(' & ', array_map(fn ($c) => $chart['palaces_by_code'][$c]['name'] ?? $c, $rel['triad_palace_codes'] ?? []));
            $oppName = $chart['palaces_by_code'][$rel['opposite_palace_code'] ?? '']['name'] ?? '';
            $flags = implode(', ', array_filter([
                $p['is_than'] ? 'THÂN' : '',
                ! empty($p['is_lai_nhan']) ? 'LAI NHÂN' : '',
                $kv,
                'ĐH '.$p['dai_han_start'].'-'.$p['dai_han_end'],
            ]));

            $lines[] = "- [{$code}] {$p['name']} ({$p['can_chi']} | {$flags}): Chính tinh: {$majors}{$tuHoa} | Phụ tinh: {$aux} | Thái Tuế: {$p['ring_thai_tue']} | Tràng Sinh: {$p['ring_trang_sinh']} | Tam hợp: {$triadNames} | Xung: {$oppName}";
        }

        $patterns = $ruleAnalysis['verified_patterns'] ?? [];
        if ($patterns !== []) {
            $lines[] = 'CÁCH CỤC ĐÃ XÁC MINH BẰNG RULE ENGINE:';
            foreach ($patterns as $pt) {
                $lines[] = "- {$pt['name']}: {$pt['evidence']} — {$pt['plain_explanation']}";
            }
        } else {
            $lines[] = 'CÁCH CỤC ĐÃ XÁC MINH BẰNG RULE ENGINE: Không có cách cục đặc thù nổi trội; luận theo tương tác Tam Phương Tứ Chính.';
        }

        $dh = $sum['current_dai_han'];
        $ln = $sum['current_luu_nien'];
        $lines[] = "VẬN HẠN NĂM {$chart['viewed_year']} (tuổi mụ {$chart['age_mu']}): Đại Hạn ({$dh['age_start']}-{$dh['age_end']}) tại {$dh['palace_name']}; Tiểu Hạn tại {$sum['current_tieu_han']['palace_name']}; Lưu Thái Tuế tại {$ln['palace_name']}.";
        $lines[] = '</verified_tuvi_chart>';

        return implode("\n", $lines);
    }

    /**
     * Xây dựng ngữ cảnh xác thực cho riêng phần / cung người dùng bấm vào để Hỏi Sâu AI.
     *
     * @param  array<string, mixed>  $chart
     * @param  array<string, mixed>  $interpretation
     * @param  array<string, mixed>  $scope
     * @param  list<array<string, mixed>>  $history
     */
    protected function buildScopedSectionContext(array $chart, array $interpretation, array $scope, array $history, string $cleanQuestion = ''): string
    {
        $sum = $chart['summary'] ?? [];
        $birth = $chart['birth'] ?? [];
        $palacesByCode = is_array($chart['palaces_by_code'] ?? null) ? $chart['palaces_by_code'] : [];
        $isPartial = ($chart['status'] ?? '') === 'partial_no_birth_hour';
        $glossary = $this->ruleEngine->getStarGlossary();

        $lines = [
            '<verified_tuvi_section_context>',
            'Phạm vi đang hỏi sâu: '.$scope['scope_label'].' (loại: '.$scope['scope_type'].')',
            'Trường phái: '.($chart['school_name'] ?? 'Nam Phái Việt Nam'),
            'Bản mệnh Nạp Âm: '.($birth['nap_am']['name'] ?? '').' ('.($birth['nap_am']['element'] ?? '').')',
        ];

        if (! $isPartial && $sum !== []) {
            $lines[] = 'Cục: '.($sum['cuc']['name'] ?? '').' | Mệnh tại: '.($sum['menh_branch_name'] ?? '').' | Thân cư: '.($sum['than_palace_name'] ?? '');
            $dh = $sum['current_dai_han'] ?? null;
            $ln = $sum['current_luu_nien'] ?? null;
            if ($dh || $ln) {
                $lines[] = sprintf(
                    'Vận hạn năm %s (tuổi %s): Đại Hạn %s–%s tại %s | Tiểu Hạn tại %s | Lưu Niên tại %s',
                    $chart['viewed_year'] ?? '',
                    $chart['age_mu'] ?? '',
                    $dh['age_start'] ?? '',
                    $dh['age_end'] ?? '',
                    $dh['palace_name'] ?? '',
                    $sum['current_tieu_han']['palace_name'] ?? '',
                    $ln['palace_name'] ?? ''
                );
            }
        }

        if ($scope['scope_type'] === 'palace' && ! empty($scope['palace_code'])) {
            $pCode = (string) $scope['palace_code'];
            $palace = $palacesByCode[$pCode] ?? null;
            if (is_array($palace)) {
                $majors = ! empty($palace['major_stars'])
                    ? implode(', ', array_map(fn (array $s): string => $s['name'].' ('.$s['brightness'].')', $palace['major_stars']))
                    : 'Vô Chính Diệu';
                $aux = implode(', ', array_map(fn (array $a): string => $a['name'], $palace['aux_stars'] ?? []));
                $tuHoa = ! empty($palace['tu_hoa'])
                    ? implode(', ', array_map(fn (array $h): string => $h['name'].' ('.$h['attached_star_name'].')', $palace['tu_hoa']))
                    : 'Không';
                $luuNien = implode(', ', $palace['luu_nien_stars'] ?? []);

                $lines[] = "CHI TIẾT CUNG ĐANG HỎI [{$palace['name']} - {$palace['can_chi']} | Đại Hạn {$palace['dai_han_start']}-{$palace['dai_han_end']}]:";
                $lines[] = "- Chính tinh: {$majors}";
                $lines[] = "- Tứ Hóa: {$tuHoa}";
                $lines[] = '- Phụ tinh: '.($aux !== '' ? $aux : 'Không có');
                $lines[] = '- Lưu Niên: '.($luuNien !== '' ? $luuNien : 'Không có');
                $lines[] = '- Vòng Thái Tuế: '.($palace['ring_thai_tue'] ?? '').' | Vòng Bác Sĩ: '.($palace['ring_bac_si'] ?? '').' | Vòng Tràng Sinh: '.($palace['ring_trang_sinh'] ?? '');
                $lines[] = '- Tuần/Triệt: '.(! empty($palace['has_tuan']) ? 'Có Tuần Không; ' : '').(! empty($palace['has_triet']) ? 'Có Triệt Lộ' : 'Không');

                // Bơm từ điển giải nghĩa các sao hiện diện trong cung để AI giải thích chính xác cho người mới
                $palaceStarNames = array_merge(
                    array_map(fn (array $s): string => (string) $s['name'], $palace['major_stars'] ?? []),
                    array_map(fn (array $h): string => (string) $h['name'], $palace['tu_hoa'] ?? []),
                    array_map(fn (array $a): string => (string) $a['name'], $palace['aux_stars'] ?? []),
                    $palace['luu_nien_stars'] ?? [],
                    array_filter([
                        $palace['ring_thai_tue'] ?? '',
                        $palace['ring_bac_si'] ?? '',
                        $palace['ring_trang_sinh'] ?? '',
                        ! empty($palace['has_tuan']) ? 'Tuần Không' : '',
                        ! empty($palace['has_triet']) ? 'Triệt Lộ' : '',
                    ])
                );
                $glossaryNotes = [];
                foreach (array_unique($palaceStarNames) as $sName) {
                    if (isset($glossary[$sName])) {
                        $g = $glossary[$sName];
                        $glossaryNotes[] = "{$sName} ({$g['group_label']}, hành {$g['element']}): {$g['meaning']}";
                    }
                }
                if ($glossaryNotes !== []) {
                    $lines[] = "TỪ ĐIỂN SAO TRONG CUNG NÀY:\n- ".implode("\n- ", $glossaryNotes);
                }
            }

            $existingPalaceText = (string) ($interpretation['layer_2_palace_highlights'][$pCode] ?? '');
            if ($existingPalaceText !== '') {
                $lines[] = "NỘI DUNG ĐÃ LUẬN CỦA CUNG NÀY:\n{$existingPalaceText}";
            }
        } elseif ($scope['scope_type'] === 'section' && ! empty($scope['section_key'])) {
            $sKey = (string) $scope['section_key'];
            $sectionMap = [
                'core_overview' => (string) ($interpretation['core_message'] ?? '')."\n".(string) ($interpretation['layer_1_overview'] ?? ''),
                'combinations_tu_hoa' => (string) ($interpretation['layer_3_combinations'] ?? ''),
                'career_orientation' => (string) ($interpretation['career_orientation'] ?? ''),
                'love_family' => (string) ($interpretation['layer_4_love_family'] ?? ''),
                'health_network' => (string) ($interpretation['health_and_network'] ?? ''),
                'life_stages' => (string) ($interpretation['layer_5_life_stages'] ?? ''),
                'action_plan' => json_encode($interpretation['layer_6_action_plan'] ?? [], JSON_UNESCAPED_UNICODE),
                'dai_han_timeline' => (string) ($interpretation['layer_5_life_stages'] ?? ''),
                'verified_patterns' => (string) ($interpretation['layer_3_combinations'] ?? ''),
                'thien_ban_overview' => (string) ($interpretation['core_message'] ?? '')."\n".(string) ($interpretation['layer_1_overview'] ?? ''),
            ];
            $sectionContent = trim((string) ($sectionMap[$sKey] ?? ''));
            if ($sectionContent !== '') {
                $lines[] = "NỘI DUNG ĐÃ LUẬN TRONG MỤC NÀY:\n{$sectionContent}";
            }
        } else {
            $lines[] = 'Thông điệp cốt lõi đã luận: '.(string) ($interpretation['core_message'] ?? '');
            $lines[] = 'Tầng 1 đã luận: '.(string) ($interpretation['layer_1_overview'] ?? '');
        }

        if ($cleanQuestion !== '') {
            $askedStars = $this->ruleEngine->lookupStarsInText($cleanQuestion);
            if ($askedStars !== []) {
                $askedLines = array_map(
                    fn (array $g): string => "{$g['name']} ({$g['group_label']} • Hành {$g['element']}): {$g['meaning']} {$g['advice']}",
                    $askedStars
                );
                $lines[] = "SAO ĐƯỢC NHẮC TRONG CÂU HỎI:\n- ".implode("\n- ", $askedLines);
            }
        }

        if ($history !== []) {
            $histLines = [];
            foreach (array_slice($history, -3) as $h) {
                $histLines[] = 'Hỏi: '.($h['question'] ?? '').' -> Đáp: '.Str::limit((string) ($h['answer'] ?? ''), 240);
            }
            $lines[] = "Hội thoại gần nhất trong hồ sơ:\n".implode("\n", $histLines);
        }

        $lines[] = '</verified_tuvi_section_context>';

        return implode("\n", $lines);
    }

    /**
     * Câu trả lời fallback chuẩn hóa có căn cứ sao & dữ liệu đúng theo từng mục/cung đang bấm (giải thích trực tiếp ngôi sao được hỏi).
     *
     * @param  array<string, mixed>  $chart
     * @param  array<string, mixed>  $interpretation
     * @param  array<string, mixed>  $scope
     */
    protected function buildScopedSectionFallback(array $chart, array $interpretation, array $scope, string $question): string
    {
        $school = (string) ($chart['school_name'] ?? 'Nam Phái Việt Nam');
        $label = (string) ($scope['scope_label'] ?? 'lá số');
        $askedStars = $this->ruleEngine->lookupStarsInText($question);

        if ($askedStars !== []) {
            $starExplanations = [];
            foreach ($askedStars as $g) {
                $starExplanations[] = sprintf(
                    '• **%s** (%s%s): %s %s',
                    $g['name'],
                    $g['group_label'],
                    $g['element'] !== '' ? ' • Hành '.$g['element'] : '',
                    $g['meaning'],
                    $g['advice']
                );
            }
            $starText = implode("\n", $starExplanations);

            if ($scope['scope_type'] === 'palace' && ! empty($scope['palace_code'])) {
                $pCode = (string) $scope['palace_code'];
                $palace = $chart['palaces_by_code'][$pCode] ?? [];
                $majors = ! empty($palace['major_stars'])
                    ? implode(', ', array_map(fn (array $s): string => $s['name'].' ('.$s['brightness'].')', $palace['major_stars']))
                    : 'Vô Chính Diệu';

                return trim(sprintf(
                    "Giải mã trực tiếp cho %s trên lá số %s (Chính tinh bản cung: %s):\n\n%s\n\nKhi đóng tại %s, các sao này tương tác trực tiếp với %s để tạo sắc thái ứng xử thực tế cho lĩnh vực của cung này — bạn hãy phát huy điểm thuận của cát tinh và giữ sự điềm tĩnh ở phần cần lưu tâm.",
                    $label,
                    $school,
                    $majors,
                    $starText,
                    $label,
                    $majors
                ));
            }

            return trim(sprintf(
                "Giải mã chi tiết từ dữ liệu chuẩn Nam Phái trên lá số của bạn (%s):\n\n%s\n\nBạn có thể đối chiếu thêm vị trí cung mà sao này đang an để thấy tác động rõ nhất lên công việc, tài chính và các mối quan hệ.",
                $label,
                $starText
            ));
        }

        if ($scope['scope_type'] === 'palace' && ! empty($scope['palace_code'])) {
            $pCode = (string) $scope['palace_code'];
            $palace = $chart['palaces_by_code'][$pCode] ?? [];
            $majors = ! empty($palace['major_stars'])
                ? implode(', ', array_map(fn (array $s): string => $s['name'].' ('.$s['brightness'].')', $palace['major_stars']))
                : 'Vô Chính Diệu (xem chính tinh cung xung chiếu)';
            $existing = trim((string) ($interpretation['layer_2_palace_highlights'][$pCode] ?? ''));

            return trim(sprintf(
                "Bám sát đúng %s — Chính tinh %s trên lá số %s: với băn khoăn \"%s\", nền tảng đã luận chỉ ra: %s\n\nĐiểm cốt lõi cần nắm là phát huy các cát tinh trợ lực tại cung này, đồng thời chủ động giữ nhịp giao tiếp minh bạch và kỷ luật thực tế ở các khía cạnh có sát tinh hoặc Hóa Kỵ chiếu tới — quyết định chủ động luôn nằm ở hành động của bạn.",
                $label,
                $majors,
                $school,
                $question,
                $existing !== '' ? $existing : 'Cần cân bằng giữa nội lực bản cung và sự tương trợ của Tam Phương Tứ Chính.'
            ));
        }

        $sectionSnippet = match ($scope['section_key'] ?? null) {
            'career_orientation' => (string) ($interpretation['career_orientation'] ?? $interpretation['core_message'] ?? ''),
            'love_family' => (string) ($interpretation['layer_4_love_family'] ?? ''),
            'combinations_tu_hoa', 'verified_patterns' => (string) ($interpretation['layer_3_combinations'] ?? ''),
            'health_network' => (string) ($interpretation['health_and_network'] ?? ''),
            'life_stages', 'dai_han_timeline' => (string) ($interpretation['layer_5_life_stages'] ?? ''),
            default => (string) ($interpretation['core_message'] ?? $interpretation['layer_1_overview'] ?? ''),
        };

        return trim(sprintf(
            "Dựa trên dữ liệu đã xác minh thuộc mục \"%s\" (%s): với câu hỏi \"%s\", nội dung trọng tâm của lá số cho thấy %s\n\nBạn hãy ưu tiên áp dụng từng bước nhỏ đo được từ dữ kiện thực tế của phần này thay vì suy diễn rộng ra ngoài phạm vi lá số.",
            $label,
            $school,
            $question,
            Str::limit(trim($sectionSnippet), 480)
        ));
    }

    protected function cleanMultilineText(string $text): string
    {
        $cleaned = preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{FEFF}]/u', '', $text) ?? $text;
        $cleaned = strip_tags($cleaned);
        $cleaned = str_replace(["\r\n", "\r"], "\n", $cleaned);
        $cleaned = preg_replace("/[ \t]+/u", ' ', $cleaned) ?? $cleaned;
        $cleaned = preg_replace("/\n{3,}/u", "\n\n", $cleaned) ?? $cleaned;

        return trim($cleaned);
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
