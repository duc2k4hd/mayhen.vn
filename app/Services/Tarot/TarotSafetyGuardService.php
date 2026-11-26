<?php

declare(strict_types=1);

namespace App\Services\Tarot;

/**
 * Lớp Bảo Vệ An Toàn, Nhân Văn & Kiểm Định Đầu Ra AI cho Mây Hẹn – The Art of Tarot.
 */
class TarotSafetyGuardService
{
    /** @var list<string> */
    protected const CRISIS_PATTERNS = [
        '/tự\s*tử|tự\s*sát|muốn\s*chết|không\s*muốn\s*sống|kết\s*thúc\s*cuộc\s*đời|tự\s*làm\s*hại/iu',
        '/bạo\s*hành|bị\s*đánh\s*đập|đe\s*dọa\s*tính\s*mạng|giết\s*người/iu',
    ];

    /** @var list<string> */
    protected const FORBIDDEN_PHRASES = [
        'vũ trụ đang gửi đến bạn',
        'năng lượng của bạn đang rung động mạnh mẽ',
        'định mệnh đã sắp đặt hai bạn gặp nhau',
        'người ấy chắc chắn đang nhớ đến bạn',
        'một điều kỳ diệu sắp xảy ra',
        'hãy tin tưởng vào vũ trụ',
        'bạn đang bị nguyền rủa',
        'bạn cần giải hạn',
        'cúng giải hạn',
        'cúng sao giải hạn',
        'chắc chắn ngoại tình',
        'chắc chắn ly hôn',
        'chắc chắn tử vong',
        'chết yểu',
        'kết quả xổ số',
        'chắc chắn sinh lời',
    ];

    /**
     * @return array{status: 'safe'|'crisis_block', allow: bool, title: ?string, notice: ?string, resources: list<array{label: string, detail: string}>}
     */
    public function assessText(string $text): array
    {
        $clean = trim($text);
        foreach (self::CRISIS_PATTERNS as $pattern) {
            if (preg_match($pattern, $clean) === 1) {
                return [
                    'status' => 'crisis_block',
                    'allow' => false,
                    'title' => 'Bạn không cần đối diện chuyện này một mình',
                    'notice' => 'Khi tâm trạng quá nặng nề hoặc có nguy cơ mất an toàn, trải bài Tarot không phải giải pháp phù hợp. Hãy liên hệ ngay người thân tin cậy, bác sĩ hoặc tổng đài hỗ trợ khẩn cấp tại Việt Nam.',
                    'resources' => [
                        ['label' => 'Cấp cứu y tế khẩn cấp', 'detail' => 'Gọi 115 (24/7)'],
                        ['label' => 'Tổng đài Quốc gia Bảo vệ Trẻ em & Hỗ trợ tâm lý', 'detail' => 'Gọi 111 (Miễn phí, 24/7)'],
                        ['label' => 'Hỗ trợ an ninh khẩn cấp', 'detail' => 'Gọi 113'],
                    ],
                ];
            }
        }

        return [
            'status' => 'safe',
            'allow' => true,
            'title' => null,
            'notice' => null,
            'resources' => [],
        ];
    }

    /**
     * Kiểm định chặt đầu ra AI: cấm văn mẫu, cấm tiên đoán nguy hiểm & khớp đúng số lá/Card ID đã rút.
     *
     * @param  array<string, mixed>  $output
     * @param  list<array<string, mixed>>  $expectedCards
     * @return array{valid: bool, issues: list<string>}
     */
    public function validateAiOutput(array $output, array $expectedCards = []): array
    {
        $issues = [];
        $haystack = mb_strtolower(json_encode($output, JSON_UNESCAPED_UNICODE) ?: '', 'UTF-8');

        foreach (self::FORBIDDEN_PHRASES as $phrase) {
            if (str_contains($haystack, $phrase)) {
                $issues[] = "Vi phạm chuẩn ngôn ngữ/nhân văn Mây Hẹn: {$phrase}";
            }
        }

        if (preg_match('/(chính\s*xác|hợp\s*nhau|khả\s*năng\s*ly\s*hôn)\s*\d{1,3}\s*%/iu', $haystack) === 1) {
            $issues[] = 'AI gán tỷ lệ % trái quy chuẩn Mây Hẹn.';
        }

        if ($expectedCards !== [] && isset($output['card_interpretations']) && is_array($output['card_interpretations'])) {
            if (count($output['card_interpretations']) !== count($expectedCards)) {
                $issues[] = 'Số lượng lá bài trong luận giải AI không khớp với trải bài thực tế.';
            } else {
                foreach ($expectedCards as $idx => $exp) {
                    $got = $output['card_interpretations'][$idx] ?? [];
                    if (($got['card_id'] ?? '') !== ($exp['card_id'] ?? '')) {
                        $issues[] = 'Card ID trong luận giải AI không khớp lá đã rút.';
                        break;
                    }
                }
            }
        }

        return [
            'valid' => $issues === [],
            'issues' => $issues,
        ];
    }
}
