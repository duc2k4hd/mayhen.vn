<?php

declare(strict_types=1);

namespace App\Services\Bazi;

use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\Contracts\LlmProviderInterface;
use App\Services\Intelligence\ScopedToolAiGuardService;
use Throwable;

/**
 * AI Luận Giải 10 Tầng & Trợ Lý Bát Tự Có Căn Cứ (Mây Hẹn – Tứ Trụ Nhân Sinh).
 *
 * - AI tuyệt đối KHÔNG lập lá số, KHÔNG sửa Can Chi / Thập Thần / Đại Vận.
 * - Chỉ diễn giải từ dữ liệu Engine & Evidence Graph; kiểm định kép qua ScopedToolAiGuardService & BaziSafetyGuardService.
 * - Luôn có bản luận giải biên tập 10 tầng chuẩn hóa làm fallback tức thời.
 */
class BaziAiInterpreterService
{
    protected ScopedToolAiGuardService $scopedGuard;

    public function __construct(
        protected LlmProviderInterface $provider,
        protected AiGovernanceService $governance,
        protected BaziSafetyGuardService $safety,
        ?ScopedToolAiGuardService $scopedGuard = null
    ) {
        $this->scopedGuard = $scopedGuard ?? app(ScopedToolAiGuardService::class);
    }

    /**
     * Sinh báo cáo luận giải 10 tầng cho lá số đã tính.
     *
     * @param  array<string, mixed>  $chart
     * @return array<string, mixed>
     */
    public function interpretChart(array $chart, bool $allowAi = false): array
    {
        $fallback = $this->buildGroundedTenLayerReport($chart);

        if (! $allowAi) {
            return $fallback;
        }

        try {
            $policy = $this->governance->getPolicy();
            if (empty($policy['kill_switch']) && $this->provider->isAvailable()) {
                $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
                $sys = implode("\n", [
                    'Bạn là Chuyên gia Mệnh Lý Tử Bình biên tập theo phong cách cổ học cao cấp Quiet Luxury của Mây Hẹn – Tứ Trụ Nhân Sinh.',
                    'QUY TẮC CỨNG: Chỉ luận đúng dữ liệu trong <verified_bazi_chart>. TUYỆT ĐỐI không đổi Can Chi, Nhật Chủ, Tàng Can, Thập Thần, Đại Vận. Không bịa Cách Cục hoặc Dụng Thần.',
                    'CẤM tiên đoán bệnh tật/tử vong/ngoại tình/ly hôn/đầu tư chắc thắng, CẤM chấm % hợp nhau, CẤM văn mẫu AI ("Vũ trụ đang gửi...").',
                    'Trả về DUY NHẤT JSON: {"headline":"...","executive_summary":"...","day_master_analysis":"...","seasonal_analysis":"...","ten_gods_analysis":"...","structure_and_useful_god":"...","career_and_growth":"...","love_and_family":"...","timing_analysis":"...","practical_reflections":["...","...","..."]}',
                ]);

                $payload = $this->buildAnonymousPromptPayload($chart);
                $res = $this->provider->completeJson(
                    systemPrompt: $sys,
                    userPrompt: '<verified_bazi_chart>'.json_encode($payload, JSON_UNESCAPED_UNICODE).'</verified_bazi_chart>',
                    model: $model,
                    temperature: 0.3,
                    maxTokens: 2800,
                    timeoutSeconds: 90
                );

                if (! empty($res['ok']) && is_array($res['data']) && ! empty($res['data']['executive_summary'])) {
                    $combinedText = json_encode($res['data'], JSON_UNESCAPED_UNICODE) ?: '';
                    if ($this->safety->isOutputSafe($combinedText)) {
                        return array_merge($fallback, [
                            'headline' => trim(strip_tags((string) ($res['data']['headline'] ?? $fallback['headline']))),
                            'executive_summary' => trim(strip_tags((string) $res['data']['executive_summary'])),
                            'day_master_analysis' => trim(strip_tags((string) ($res['data']['day_master_analysis'] ?? $fallback['day_master_analysis']))),
                            'seasonal_analysis' => trim(strip_tags((string) ($res['data']['seasonal_analysis'] ?? $fallback['seasonal_analysis']))),
                            'ten_gods_analysis' => trim(strip_tags((string) ($res['data']['ten_gods_analysis'] ?? $fallback['ten_gods_analysis']))),
                            'structure_and_useful_god' => trim(strip_tags((string) ($res['data']['structure_and_useful_god'] ?? $fallback['structure_and_useful_god']))),
                            'career_and_growth' => trim(strip_tags((string) ($res['data']['career_and_growth'] ?? $fallback['career_and_growth']))),
                            'love_and_family' => trim(strip_tags((string) ($res['data']['love_and_family'] ?? $fallback['love_and_family']))),
                            'timing_analysis' => trim(strip_tags((string) ($res['data']['timing_analysis'] ?? $fallback['timing_analysis']))),
                            'practical_reflections' => is_array($res['data']['practical_reflections'] ?? null)
                                ? array_values(array_filter(array_map(fn ($s) => trim(strip_tags((string) $s)), $res['data']['practical_reflections'])))
                                : $fallback['practical_reflections'],
                            'ai_powered' => true,
                            'ai_model' => (string) ($res['model'] ?? $model ?? 'configured-ai'),
                        ]);
                    }
                }
            }
        } catch (Throwable) {
            // Fallback chuẩn hóa
        }

        return $fallback;
    }

    /**
     * Trợ lý Hỏi Đáp Bát Tự AI căn cứ trên lá số & Evidence Graph (Zero-Trust Input & Output Gate).
     *
     * @param  array<string, mixed>  $chart
     * @return array{answer: string, ai_powered: bool, ai_model: ?string, blocked?: bool, code?: ?string}
     */
    public function answerFollowup(string $question, array $chart): array
    {
        $payload = $this->buildAnonymousPromptPayload($chart);
        $groundedTerms = $this->scopedGuard->extractGroundedTerms($payload);
        $inputGuard = $this->scopedGuard->inspectScopedQuestion($question, 'bazi', $groundedTerms, 400);
        if (! $inputGuard['allowed']) {
            return [
                'answer' => (string) ($inputGuard['message'] ?? 'Câu hỏi nằm ngoài phạm vi lá số Bát Tự hiện tại.'),
                'ai_powered' => false,
                'ai_model' => null,
                'blocked' => true,
                'code' => $inputGuard['code'],
            ];
        }

        $cleanQuestion = $inputGuard['clean_question'];
        $dm = $chart['day_master'];
        $monthP = $chart['pillars']['month'];
        $struct = $chart['structure'];
        $ug = $chart['useful_gods'];

        $fallback = sprintf(
            'Căn cứ lá số %s: Nhật Chủ của bạn là %s (%s %s), sinh tháng %s (%s — %s). Theo Tử Bình Chân Thuyên lá số định hướng %s; về Dụng Thần, Phù Ức nhận định "%s" còn Điều Hậu nhận định "%s". Bạn có thể đối chiếu thêm từng nút trong Cây Lập Luận để xem rõ căn cứ Can Chi.',
            $chart['pillars']['year']['label'].' · '.$monthP['label'].' · '.$chart['pillars']['day']['label'].($chart['pillars']['hour'] ? ' · '.$chart['pillars']['hour']['label'] : ' (chưa có Trụ Giờ)'),
            $dm['name'],
            $dm['yin_yang'],
            $dm['element'],
            $monthP['branch']['name'],
            $chart['seasonal_qi']['state'],
            $chart['strength']['label'],
            $struct['name'],
            $ug['phu_uc']['note'],
            $ug['dieu_hau']['note']
        );

        try {
            $policy = $this->governance->getPolicy();
            if (empty($policy['kill_switch']) && $this->provider->isAvailable()) {
                $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
                $sys = implode("\n", [
                    'Bạn là Trợ lý Mệnh Lý Tử Bình điềm đạm của Mây Hẹn.',
                    $this->scopedGuard->buildZeroTrustSystemPreamble('verified_bazi_chart'),
                    'Chỉ trả lời dựa trên <verified_bazi_chart>. Không đổi Can Chi, không tiên đoán bệnh/tử vong/ngoại tình, không chấm % hợp nhau.',
                    'Trả lời rõ căn cứ trong 4–6 câu. Trả về DUY NHẤT JSON: {"answer":"..."}',
                ]);

                $verifiedContext = '<verified_bazi_chart>'.json_encode($payload, JSON_UNESCAPED_UNICODE).'</verified_bazi_chart>';
                $userPrompt = $verifiedContext."\n".$inputGuard['wrapped_question'];
                $res = $this->provider->completeJson(
                    systemPrompt: $sys,
                    userPrompt: $userPrompt,
                    model: $model,
                    temperature: 0.25,
                    maxTokens: 700,
                    timeoutSeconds: 60
                );

                if (! empty($res['ok']) && is_array($res['data']) && ! empty($res['data']['answer'])) {
                    $verify = $this->scopedGuard->verifyScopedOutput(
                        (string) $res['data']['answer'],
                        $verifiedContext,
                        'bazi',
                        $groundedTerms
                    );
                    if ($verify['safe'] && $this->safety->isOutputSafe($verify['clean_answer'])) {
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
            // Fallback
        }

        return ['answer' => $fallback, 'ai_powered' => false, 'ai_model' => null, 'blocked' => false];
    }

    /**
     * @param  array<string, mixed>  $chart
     * @return array<string, mixed>
     */
    protected function buildAnonymousPromptPayload(array $chart): array
    {
        return [
            'method_id' => $chart['method']['id'],
            'has_hour' => $chart['birth']['has_hour'],
            'pillars' => [
                'year' => $chart['pillars']['year']['label'].' ('.$chart['pillars']['year']['ten_god']['name'].')',
                'month' => $chart['pillars']['month']['label'].' ('.$chart['pillars']['month']['ten_god']['name'].')',
                'day' => $chart['pillars']['day']['label'].' (Nhật Chủ)',
                'hour' => $chart['pillars']['hour'] ? $chart['pillars']['hour']['label'].' ('.$chart['pillars']['hour']['ten_god']['name'].')' : 'Chưa cung cấp',
            ],
            'day_master' => $chart['day_master']['name'].' '.$chart['day_master']['yin_yang'].' '.$chart['day_master']['element'],
            'seasonal_qi' => $chart['seasonal_qi'],
            'strength' => $chart['strength']['label'],
            'structure' => $chart['structure'],
            'useful_gods' => $chart['useful_gods'],
            'relationships' => $chart['relationships'],
            'evidence_graph' => $chart['evidence_graph'],
            'annual_year' => $chart['annual_flow']['year'].' — '.$chart['annual_flow']['annual_pillar']['label'],
        ];
    }

    /**
     * @param  array<string, mixed>  $chart
     * @return array<string, mixed>
     */
    protected function buildGroundedTenLayerReport(array $chart): array
    {
        $dm = $chart['day_master'];
        $yP = $chart['pillars']['year'];
        $mP = $chart['pillars']['month'];
        $dP = $chart['pillars']['day'];
        $hP = $chart['pillars']['hour'];
        $sq = $chart['seasonal_qi'];
        $st = $chart['strength'];
        $struct = $chart['structure'];
        $ug = $chart['useful_gods'];

        $presentTenGods = array_values(array_filter($chart['ten_gods_summary'], fn ($t) => $t['present']));
        $tgNames = implode(', ', array_map(fn ($t) => $t['name'], array_slice($presentTenGods, 0, 5)));

        return [
            'headline' => "Nhật Chủ {$dm['name']} {$dm['element']} ({$dm['symbol']}) — {$struct['name']}",
            'executive_summary' => sprintf(
                'Lá số gồm %s (phương pháp %s). Nhật Chủ là %s (%s %s), sinh vào tiết tháng %s (%s). %s',
                $yP['label'].' · '.$mP['label'].' · '.$dP['label'].($hP ? ' · '.$hP['label'] : ' — chưa có Trụ Giờ'),
                $chart['method']['name'],
                $dm['name'],
                $dm['yin_yang'],
                $dm['element'],
                $mP['branch']['name'],
                $mP['branch']['season'],
                $hP ? 'Đủ 8 chữ Tứ Trụ để phân tích toàn diện cấu trúc và chu kỳ Đại Vận.' : 'Đang phân tích trên 6 chữ (Năm – Tháng – Ngày); các nhận định phụ thuộc Trụ Giờ đều được ghi chú giới hạn.'
            ),
            'day_master_analysis' => "{$dm['name']} ({$dm['hanzi']}) thuộc hành {$dm['element']} ({$dm['yin_yang']}), tượng trưng cho {$dm['symbol']}. {$dm['nature']} Trong lá số này, Nhật Chủ có {$chart['roots']['root_count']} căn trong Địa Chi và ".count($chart['roots']['supporting_stems']).' Thiên Can lộ sinh trợ.',
            'seasonal_analysis' => "Nguyệt Lệnh tại {$mP['branch']['name']} ({$mP['branch']['season']}) đặt Nhật Chủ ở trạng thái {$sq['state']}: {$sq['note']} Theo mô hình Phù Ức ({$st['support_ratio']}% tỷ trọng trợ lực), lá số được đánh giá là {$st['label']}.",
            'ten_gods_analysis' => "Các Thập Thần hiện diện nổi bật trong lá số gồm: {$tgNames}. Mỗi Thập Thần đại diện cho một vai trò ứng xử và nguồn lực khác nhau (từ học hỏi, sáng tạo đến quản trị và hợp tác), không phân định tốt xấu tuyệt đối.",
            'structure_and_useful_god' => "{$struct['note']} Về Dụng Thần: (1) Phù Ức: {$ug['phu_uc']['note']} (2) Điều Hậu: {$ug['dieu_hau']['note']} (3) Cách Cục: {$ug['cach_cuc']['note']}",
            'career_and_growth' => "Dựa trên nền tảng {$struct['name']} và khí chất {$dm['name']} {$dm['element']}, bạn phù hợp phát triển các công việc đòi hỏi đúng thế mạnh tổ chức/sáng tạo của Thập Thần trọng tâm ({$struct['anchor_ten_god']}), kết hợp rèn luyện kỹ năng bổ trợ cho phần Ngũ Hành còn ít lộ.",
            'love_and_family' => "Cung Phối Ngẫu tọa tại Địa Chi ngày {$dP['branch']['name']} (hành {$dP['branch']['element']}). Trong đời sống gia đình, việc thấu hiểu nhịp điệu cảm xúc riêng của Nhật Chủ {$dm['name']} và duy trì đối thoại chân thành là nền tảng bền vững hơn mọi quy ước hình thức.",
            'timing_analysis' => sprintf(
                'Chu kỳ Đại Vận khởi lúc %s (%s). Lưu Niên %d là năm %s (%s với Nhật Chủ). Khi xem vận, cần đặt Lưu Niên trong bối cảnh Đại Vận và Tứ Trụ gốc.',
                $chart['luck_pillars']['primary']['start_age_label'],
                $chart['luck_pillars']['primary']['direction_label'],
                $chart['annual_flow']['year'],
                $chart['annual_flow']['annual_pillar']['label'],
                $chart['annual_flow']['annual_pillar']['ten_god']['name']
            ),
            'practical_reflections' => [
                'Quan sát cách bạn phản ứng khi gặp áp lực để điều hòa giữa ưu điểm và điểm cần tiết chế của Nhật Chủ.',
                'Đặt mục tiêu công việc và học tập theo từng chu kỳ 1–3 năm phù hợp với giai đoạn Đại Vận hiện tại.',
                'Dùng các góc nhìn Bát Tự như tấm gương tự chiêm nghiệm, luôn ưu tiên thực tế và quyết định chủ động của bản thân.',
            ],
            'limitations' => 'Luận giải Bát Tự là tri thức văn hóa cổ học phương Đông dùng để chiêm nghiệm bản thân và đối thoại gia đình, không thay thế tư vấn y tế, tâm lý, pháp lý hoặc tài chính.',
            'ai_powered' => false,
            'ai_model' => null,
        ];
    }
}
