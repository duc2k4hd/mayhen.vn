<?php

declare(strict_types=1);

namespace App\Services\Calendar;

use App\Models\CalendarAiDailyCache;
use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\Contracts\LlmProviderInterface;
use Throwable;

/**
 * AI Giải Thích Lịch Vạn Niên 6 Tầng Có Căn Cứ & Hỏi Đáp Thông Minh.
 *
 * - AI tuyệt đối KHÔNG tự tính ngày âm, Can Chi, Tiết Khí hay Pha Mặt Trăng.
 * - Chỉ diễn giải dữ liệu đã kiểm chứng từ Engine; cache bản công khai 1 lần/ngày trong DB/RAM.
 * - Luôn có fallback chuẩn hóa từ Knowledge Base nếu AI không khả dụng.
 */
class CalendarAiInterpreterService
{
    public function __construct(
        protected LlmProviderInterface $provider,
        protected AiGovernanceService $governance
    ) {}

    /**
     * Lấy hoặc sinh bản giải thích công khai cho 1 ngày (cache 1 lần theo ngày & phiên bản).
     *
     * @param  array<string, mixed>  $bundle  Gồm day, solar_term, moon_phase, almanac, holidays
     * @return array<string, mixed>
     */
    public function getDailyPublicExplanation(array $bundle, bool $allowAi = false): array
    {
        $solarIso = (string) ($bundle['day']['solar']['iso'] ?? '');
        $version = VietnameseCalendarEngine::ENGINE_VERSION.'_'.TraditionalAlmanacEngine::RULE_VERSION;

        $cached = CalendarAiDailyCache::where('solar_date', $solarIso)
            ->where('rule_version', $version)
            ->first();

        if ($cached && is_array($cached->payload_json)) {
            return $cached->payload_json;
        }

        $fallback = $this->buildGroundedFallback($bundle);

        if (! $allowAi) {
            return $fallback;
        }

        try {
            $policy = $this->governance->getPolicy();
            if (empty($policy['kill_switch']) && $this->provider->isAvailable()) {
                $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
                $sys = implode("\n", [
                    'Bạn là Chuyên gia Lịch Pháp & Văn Hóa Việt Nam của Mây Hẹn – Vạn Niên Lịch.',
                    'QUY TẮC BẮT BUỘC: Chỉ diễn giải ĐÚNG dữ liệu trong <verified_calendar_data>. TUYỆT ĐỐI KHÔNG tự tính hay đổi ngày âm/dương, Can Chi, Tiết Khí, Pha Trăng, Trực, Tú.',
                    'KHÔNG dọa nạt mê tín, KHÔNG tạo tỷ lệ % may mắn, KHÔNG khuyên hủy khám bệnh/công việc thiết yếu.',
                    'Trả về DUY NHẤT JSON: {"summary":"...","calendar_note":"...","astronomy_note":"...","traditional_note":"...","practical_tip":"..."}',
                ]);

                $userPrompt = '<verified_calendar_data>'.json_encode([
                    'solar' => $bundle['day']['solar'],
                    'lunar' => $bundle['day']['lunar'],
                    'can_chi' => [
                        'year' => $bundle['day']['can_chi']['year']['label'],
                        'month' => $bundle['day']['can_chi']['month']['label'],
                        'day' => $bundle['day']['can_chi']['day']['label'],
                        'nap_am' => $bundle['day']['can_chi']['day']['nap_am']['name'],
                    ],
                    'solar_term' => $bundle['solar_term']['current']['name'],
                    'moon_phase' => $bundle['moon_phase']['phase_name'].' ('.$bundle['moon_phase']['illumination_pct'].'%)',
                    'day_quality' => $bundle['almanac']['day_quality'],
                    'truc' => $bundle['almanac']['truc']['name'],
                    'mansion' => $bundle['almanac']['mansion']['name'],
                    'cautions' => array_column($bundle['almanac']['folk_cautions'] ?? [], 'name'),
                    'holidays' => array_column($bundle['holidays'] ?? [], 'name'),
                ], JSON_UNESCAPED_UNICODE).'</verified_calendar_data>';

                $res = $this->provider->completeJson(
                    systemPrompt: $sys,
                    userPrompt: $userPrompt,
                    model: $model,
                    temperature: 0.25,
                    maxTokens: 900,
                    timeoutSeconds: 60
                );

                if (! empty($res['ok']) && is_array($res['data']) && ! empty($res['data']['summary'])) {
                    $payload = [
                        'summary' => trim(strip_tags((string) $res['data']['summary'])),
                        'calendar_note' => trim(strip_tags((string) ($res['data']['calendar_note'] ?? $fallback['calendar_note']))),
                        'astronomy_note' => trim(strip_tags((string) ($res['data']['astronomy_note'] ?? $fallback['astronomy_note']))),
                        'traditional_note' => trim(strip_tags((string) ($res['data']['traditional_note'] ?? $fallback['traditional_note']))),
                        'practical_tip' => trim(strip_tags((string) ($res['data']['practical_tip'] ?? $fallback['practical_tip']))),
                        'ai_powered' => true,
                        'ai_model' => (string) ($res['model'] ?? $model ?? 'configured-ai'),
                    ];

                    CalendarAiDailyCache::updateOrCreate(
                        ['solar_date' => $solarIso, 'rule_version' => $version],
                        ['payload_json' => $payload, 'ai_model' => $payload['ai_model']]
                    );

                    return $payload;
                }
            }
        } catch (Throwable) {
            // Fallback chuẩn hóa
        }

        return $fallback;
    }

    /**
     * Hỏi đáp AI thông minh dựa trên dữ liệu ngày đang xem (không tự tính lịch).
     *
     * @param  array<string, mixed>  $bundle
     * @return array{answer: string, ai_powered: bool, ai_model: ?string}
     */
    public function answerQuestion(string $question, array $bundle): array
    {
        $day = $bundle['day'];
        $alm = $bundle['almanac'];
        $moon = $bundle['moon_phase'];
        $term = $bundle['solar_term']['current'];

        $fallback = sprintf(
            'Ngày %s (%s) là %s, Can Chi ngày %s (Nạp Âm %s), thuộc tiết khí %s, pha Mặt Trăng %s (%s%% sáng). Theo Ngọc Hạp Thông Thư đây là %s (%s), Trực %s, Tú %s. Bạn nên kết hợp giữa thông tin tham khảo truyền thống và lịch rảnh thực tế của gia đình để sắp xếp công việc thuận tiện nhất.',
            $day['solar']['label'],
            $day['solar']['weekday_name'],
            $day['lunar']['label_full'],
            $day['can_chi']['day']['label'],
            $day['can_chi']['day']['nap_am']['name'],
            $term['name'],
            $moon['phase_name'],
            $moon['illumination_pct'],
            $alm['day_quality']['label'],
            $alm['day_quality']['god_name'],
            $alm['truc']['name'],
            $alm['mansion']['name']
        );

        try {
            $policy = $this->governance->getPolicy();
            if (empty($policy['kill_switch']) && $this->provider->isAvailable()) {
                $model = ($policy['draft_model'] ?? '') !== '' ? (string) $policy['draft_model'] : null;
                $sys = implode("\n", [
                    'Bạn là Trợ lý Vạn Niên Lịch điềm đạm của Mây Hẹn.',
                    'Chỉ trả lời dựa trên dữ liệu chuẩn trong <calendar_context>. KHÔNG tự đổi ngày âm/dương hay bịa quy tắc/tỷ lệ %.',
                    'Trả lời ngắn gọn 3–5 câu, nhân văn, rõ ràng. Trả về DUY NHẤT JSON: {"answer":"..."}',
                ]);
                $userPrompt = '<calendar_context>'.json_encode([
                    'solar' => $day['solar'],
                    'lunar' => $day['lunar'],
                    'can_chi_day' => $day['can_chi']['day']['label'],
                    'nap_am' => $day['can_chi']['day']['nap_am']['name'],
                    'solar_term' => $term['name'],
                    'moon_phase' => $moon['phase_name'].' ('.$moon['illumination_pct'].'%)',
                    'day_quality' => $alm['day_quality']['label'].' ('.$alm['day_quality']['god_name'].')',
                    'truc' => $alm['truc']['name'].' — '.$alm['truc']['desc'],
                    'hoang_dao_hours' => array_map(fn ($h) => $h['branch'].' ('.$h['range'].')', $alm['hoang_dao_hours'] ?? []),
                    'folk_cautions' => array_column($alm['folk_cautions'] ?? [], 'name'),
                    'holidays' => array_column($bundle['holidays'] ?? [], 'name'),
                ], JSON_UNESCAPED_UNICODE)."</calendar_context>\n<user_question>{$question}</user_question>";

                $res = $this->provider->completeJson(
                    systemPrompt: $sys,
                    userPrompt: $userPrompt,
                    model: $model,
                    temperature: 0.25,
                    maxTokens: 600,
                    timeoutSeconds: 60
                );

                if (! empty($res['ok']) && is_array($res['data']) && ! empty($res['data']['answer'])) {
                    return [
                        'answer' => trim(strip_tags((string) $res['data']['answer'])),
                        'ai_powered' => true,
                        'ai_model' => (string) ($res['model'] ?? $model ?? 'configured-ai'),
                    ];
                }
            }
        } catch (Throwable) {
            // Fallback
        }

        return ['answer' => $fallback, 'ai_powered' => false, 'ai_model' => null];
    }

    /**
     * @param  array<string, mixed>  $bundle
     * @return array<string, mixed>
     */
    protected function buildGroundedFallback(array $bundle): array
    {
        $day = $bundle['day'];
        $alm = $bundle['almanac'];
        $moon = $bundle['moon_phase'];
        $term = $bundle['solar_term'];

        return [
            'summary' => sprintf(
                'Hôm nay %s (%s) là %s — ngày %s (%s). Đây là %s (%s), Trực %s, thuộc tiết khí %s.',
                $day['solar']['weekday_name'],
                $day['solar']['label'],
                $day['lunar']['label_full'],
                $day['can_chi']['day']['label'],
                $day['can_chi']['day']['nap_am']['name'],
                $alm['day_quality']['label'],
                $alm['day_quality']['god_name'],
                $alm['truc']['name'],
                $term['current']['name']
            ),
            'calendar_note' => sprintf(
                'Tháng %d%s năm %s là %s (từ %s đến %s Dương lịch).',
                $day['lunar']['month'],
                $day['lunar']['is_leap_month'] ? ' nhuận' : '',
                $day['can_chi']['year']['label'],
                $day['lunar']['month_size_label'],
                $day['lunar']['solar_month_start'],
                $day['lunar']['solar_month_end']
            ),
            'astronomy_note' => sprintf(
                'Mặt Trời đang ở kinh độ %.1f° (tiết %s, còn %d ngày tới tiết %s vào %s). Mặt Trăng ở pha %s với %s%% bề mặt được chiếu sáng.',
                $term['sun_longitude_deg'],
                $term['current']['name'],
                $term['days_until_next'],
                $term['next']['name'],
                $term['next']['datetime_vn'],
                $moon['phase_name'],
                $moon['illumination_pct']
            ),
            'traditional_note' => sprintf(
                '%s (%s) kết hợp Trực %s (%s) và Tú %s (%s). %s',
                $alm['day_quality']['label'],
                $alm['day_quality']['god_name'],
                $alm['truc']['name'],
                $alm['truc']['desc'],
                $alm['mansion']['name'],
                $alm['mansion']['nature'],
                ! empty($alm['folk_cautions']) ? 'Lưu ý dân gian: '.implode(', ', array_column($alm['folk_cautions'], 'name')).'.' : 'Không rơi vào các ngày kiêng kỵ dân gian lớn.'
            ),
            'practical_tip' => 'Lịch vạn niên là khung tham khảo văn hóa; với lịch cưới hỏi, họp mặt hay công việc quan trọng, hãy ưu tiên sự thuận tiện của gia đình và kết hợp chọn khung giờ Hoàng Đạo trong ngày.',
            'ai_powered' => false,
            'ai_model' => null,
        ];
    }
}
