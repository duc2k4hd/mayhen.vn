<?php

declare(strict_types=1);

namespace App\Services\Marriage;

use App\Services\Bazi\BaziSafetyGuardService;
use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\Contracts\LlmProviderInterface;
use App\Services\Intelligence\ScopedToolAiGuardService;
use Throwable;

/**
 * AI Luận Giải 10 Tầng Nhân Duyên, Hỏi Đáp Cặp Đôi & Trợ Lý Trao Đổi Với Gia Đình (Mây Hẹn – Duyên Hợp).
 *
 * - AI tuyệt đối KHÔNG tính lại Can Chi, Nạp Âm, Cao Ly hay Cung Phi.
 * - Mọi đầu vào & đầu ra đều kiểm định qua ScopedToolAiGuardService & BaziSafetyGuardService.
 */
class MarriageAiInterpreterService
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
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function interpret(array $data, bool $allowAi = false): array
    {
        $fallback = $this->buildGroundedTenLayers($data);
        if (! $allowAi) {
            return $fallback;
        }

        try {
            $policy = $this->governance->getPolicy();
            if (empty($policy['kill_switch']) && $this->provider->isAvailable()) {
                $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
                $sys = implode("\n", [
                    'Bạn là Chuyên gia Văn Hóa Hôn Nhân & Hợp Hôn Phương Đông của Mây Hẹn – Duyên Hợp.',
                    'QUY TẮC CỨNG: Chỉ diễn giải ĐÚNG dữ liệu trong <verified_marriage_data>. TUYỆT ĐỐI không đổi Can Chi, Nạp Âm, Cao Ly, Cung Phi, Du Niên.',
                    'CẤM tạo % hợp nhau, CẤM khẳng định ly hôn/tử vong/ngoại tình/hủy cưới/cúng giải hạn.',
                    'Trả về DUY NHẤT JSON: {"summary":"...","caoly_layer":"...","canchi_layer":"...","napam_and_kua":"...","conflict_synthesis":"...","family_dialogue_guide":"...","practical_actions":["...","...","..."]}',
                ]);

                $payload = [
                    'husband' => ['can_chi' => $data['husband']['can_chi'], 'nap_am' => $data['husband']['nap_am']['name'], 'kua' => $data['husband']['kua']['name']],
                    'wife' => ['can_chi' => $data['wife']['can_chi'], 'nap_am' => $data['wife']['nap_am']['name'], 'kua' => $data['wife']['kua']['name']],
                    'cao_ly' => ['combination' => $data['cao_ly']['combination_name'], 'scene' => $data['cao_ly']['scene']['name']],
                    'stem_relation' => $data['stem_relation']['label'],
                    'branch_relation' => $data['branch_relation']['primary_label'],
                    'nap_am_relation' => $data['nap_am_relation']['label'],
                    'du_nien' => $data['du_nien']['name'].' ('.$data['du_nien']['group'].')',
                    'wedding_year' => $data['wedding_year'],
                ];

                $res = $this->provider->completeJson(
                    systemPrompt: $sys,
                    userPrompt: '<verified_marriage_data>'.json_encode($payload, JSON_UNESCAPED_UNICODE).'</verified_marriage_data>',
                    model: $model,
                    temperature: 0.3,
                    maxTokens: 2200,
                    timeoutSeconds: 85
                );

                if (! empty($res['ok']) && is_array($res['data']) && ! empty($res['data']['summary'])) {
                    $rawText = json_encode($res['data'], JSON_UNESCAPED_UNICODE) ?: '';
                    if ($this->safety->isOutputSafe($rawText)) {
                        return array_merge($fallback, [
                            'summary' => trim(strip_tags((string) $res['data']['summary'])),
                            'caoly_layer' => trim(strip_tags((string) ($res['data']['caoly_layer'] ?? $fallback['caoly_layer']))),
                            'canchi_layer' => trim(strip_tags((string) ($res['data']['canchi_layer'] ?? $fallback['canchi_layer']))),
                            'napam_and_kua' => trim(strip_tags((string) ($res['data']['napam_and_kua'] ?? $fallback['napam_and_kua']))),
                            'conflict_synthesis' => trim(strip_tags((string) ($res['data']['conflict_synthesis'] ?? $fallback['conflict_synthesis']))),
                            'family_dialogue_guide' => trim(strip_tags((string) ($res['data']['family_dialogue_guide'] ?? $fallback['family_dialogue_guide']))),
                            'practical_actions' => is_array($res['data']['practical_actions'] ?? null)
                                ? array_values(array_filter(array_map(fn ($s) => trim(strip_tags((string) $s)), $res['data']['practical_actions'])))
                                : $fallback['practical_actions'],
                            'ai_powered' => true,
                            'ai_model' => (string) ($res['model'] ?? $model ?? 'configured-ai'),
                        ]);
                    }
                }
            }
        } catch (Throwable) {
            // Fallback
        }

        return $fallback;
    }

    /**
     * Hỏi đáp cặp đôi & Trợ lý soạn lời trao đổi tôn trọng với gia đình (Zero-Trust Gate).
     *
     * @param  array<string, mixed>  $data
     * @return array{answer: string, ai_powered: bool, ai_model: ?string, blocked?: bool, code?: ?string}
     */
    public function answerQuestion(string $question, array $data): array
    {
        $groundedTerms = $this->scopedGuard->extractGroundedTerms([
            $data['husband']['can_chi'] ?? '',
            $data['wife']['can_chi'] ?? '',
            $data['husband']['nap_am']['name'] ?? '',
            $data['wife']['nap_am']['name'] ?? '',
            $data['cao_ly']['combination_name'] ?? '',
            $data['du_nien']['name'] ?? '',
            $data['branch_relation']['primary_label'] ?? '',
        ]);
        $inputGuard = $this->scopedGuard->inspectScopedQuestion($question, 'marriage', $groundedTerms, 400);
        if (! $inputGuard['allowed']) {
            return [
                'answer' => (string) ($inputGuard['message'] ?? 'Câu hỏi nằm ngoài phạm vi đối chiếu cặp đôi hiện tại.'),
                'ai_powered' => false,
                'ai_model' => null,
                'blocked' => true,
                'code' => $inputGuard['code'],
            ];
        }

        $fallback = sprintf(
            'Với cặp tuổi Chồng %s (%s · Cung %s) và Vợ %s (%s · Cung %s): Cao Ly Đầu Hình tra Can %s – Chi %s cho nhóm %s; Bát Trạch cho Du Niên %s; Địa Chi ở trạng thái %s. Khi trao đổi với bố mẹ, hai bạn có thể trình bày tôn trọng rằng mỗi phương pháp cổ chỉ xét một lát cắt riêng (nên kết quả có thể khác nhau), đồng thời chia sẻ kế hoạch công việc, tài chính và sự chuẩn bị chu đáo cho hôn nhân để gia đình hoàn toàn yên tâm.',
            $data['husband']['can_chi'],
            $data['husband']['nap_am']['name'],
            $data['husband']['kua']['name'],
            $data['wife']['can_chi'],
            $data['wife']['nap_am']['name'],
            $data['wife']['kua']['name'],
            $data['husband']['stem']['name'],
            $data['wife']['branch']['name'],
            $data['cao_ly']['scene']['name'],
            $data['du_nien']['name'],
            $data['branch_relation']['primary_label']
        );

        try {
            $policy = $this->governance->getPolicy();
            if (empty($policy['kill_switch']) && $this->provider->isAvailable()) {
                $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
                $sys = implode("\n", [
                    'Bạn là Trợ Lý Duyên Hợp điềm đạm, tôn trọng gia đình của Mây Hẹn.',
                    $this->scopedGuard->buildZeroTrustSystemPreamble('verified_marriage_data'),
                    'Chỉ trả lời dựa trên <verified_marriage_data>. Không chấm % hợp nhau, không phán ly hôn/tử vong, không xúc phạm niềm tin gia đình.',
                    'Trả lời 4–6 câu ấm áp, thực tế. Trả về DUY NHẤT JSON: {"answer":"..."}',
                ]);
                $verifiedContext = '<verified_marriage_data>'.json_encode([
                    'husband' => $data['husband']['can_chi'].' / '.$data['husband']['nap_am']['name'].' / Cung '.$data['husband']['kua']['name'],
                    'wife' => $data['wife']['can_chi'].' / '.$data['wife']['nap_am']['name'].' / Cung '.$data['wife']['kua']['name'],
                    'cao_ly' => $data['cao_ly']['combination_name'].' -> '.$data['cao_ly']['scene']['name'],
                    'branch' => $data['branch_relation']['primary_label'],
                    'du_nien' => $data['du_nien']['name'],
                ], JSON_UNESCAPED_UNICODE).'</verified_marriage_data>';

                $res = $this->provider->completeJson(
                    systemPrompt: $sys,
                    userPrompt: $verifiedContext."\n".$inputGuard['wrapped_question'],
                    model: $model,
                    temperature: 0.25,
                    maxTokens: 650,
                    timeoutSeconds: 60
                );
                if (! empty($res['ok']) && is_array($res['data']) && ! empty($res['data']['answer'])) {
                    $verify = $this->scopedGuard->verifyScopedOutput(
                        (string) $res['data']['answer'],
                        $verifiedContext,
                        'marriage',
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
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function buildGroundedTenLayers(array $data): array
    {
        $h = $data['husband'];
        $w = $data['wife'];
        $cl = $data['cao_ly'];
        $dn = $data['du_nien'];

        return [
            'summary' => "Đối chiếu đa phương pháp cho Chồng {$h['can_chi']} ({$h['nap_am']['name']}) và Vợ {$w['can_chi']} ({$w['nap_am']['name']}): hệ thống kiểm tra độc lập 6 lớp gồm Cao Ly Đầu Hình ({$cl['combination_name']} — {$cl['scene']['name']}), Thiên Can ({$data['stem_relation']['label']}), Địa Chi ({$data['branch_relation']['primary_label']}), Ngũ Hành Nạp Âm ({$data['nap_am_relation']['label']}) và Cung Phi Bát Trạch ({$dn['name']}).",
            'caoly_layer' => "{$cl['tradition_summary']} {$cl['modern_guidance']}",
            'canchi_layer' => "Thiên Can {$h['stem']['name']} – {$w['stem']['name']} ở trạng thái {$data['stem_relation']['label']}. Địa Chi {$h['branch']['name']} ({$h['zodiac']}) – {$w['branch']['name']} ({$w['zodiac']}): ".implode(' ', $data['branch_relation']['details']),
            'napam_and_kua' => "Ngũ Hành Nạp Âm: {$data['nap_am_relation']['detail']} Về Cung Phi Bát Trạch: Chồng cung {$h['kua']['name']} ({$h['kua']['group']}), Vợ cung {$w['kua']['name']} ({$w['kua']['group']}) phối thành {$dn['name']} — {$dn['modern_note']}",
            'conflict_synthesis' => 'Mỗi phương pháp cổ học sử dụng một lớp dữ liệu khác nhau (Can–Chi có hướng, Nạp Âm năm sinh, Quẻ Bát Trạch) nên hoàn toàn bình thường khi một lớp thuận và một lớp nhắc lưu tâm. Không nên cộng trừ thành tỷ lệ % mà hãy dùng để nhận diện sớm điều cần thấu hiểu.',
            'family_dialogue_guide' => "Nếu gia đình có băn khoăn về tuổi tác hoặc năm cưới ({$data['wedding_year']['kim_lau_label']}), hai bạn nên lắng nghe với thái độ trân trọng, giải thích rõ bức tranh đa phương pháp và chủ động chọn ngày cưới thuận hòa cả phong tục lẫn lịch rảnh hai nhà.",
            'practical_actions' => [
                $cl['couple_tip'],
                'Cùng trả lời 6 câu hỏi trước hôn nhân về tài chính, nơi ở và cách xử lý bất đồng.',
                'Khi đã thống nhất năm cưới, dùng công cụ Lịch Vạn Niên Mây Hẹn để chọn ngày đẹp rơi vào cuối tuần thuận tiện cho khách mời.',
            ],
            'ai_powered' => false,
            'ai_model' => null,
        ];
    }
}
