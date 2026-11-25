<?php

declare(strict_types=1);

namespace App\Services\TuVi;

/**
 * Lớp bảo vệ an toàn, nhân văn & hậu kiểm đầu ra AI cho Mây Hẹn – Thiên Mệnh.
 *
 * - Chặn tình huống khủng hoảng tâm lý/tự hại/bạo lực -> điều hướng đường dây hỗ trợ khẩn cấp.
 * - Hậu kiểm AI: chặn chẩn đoán bệnh, tuổi thọ, giới tính thai nhi, gán nhãn trẻ em khắc cha mẹ,
 *   khẳng định ly hôn/ngoại tình/không được cưới, cúng giải hạn hoặc chấm % hợp tuổi/ly hôn.
 */
class TuViSafetyGuardService
{
    /** @var list<string> */
    protected const CRISIS_PATTERNS = [
        '/tự\s*tử|tự\s*sát|muốn\s*chết|không\s*muốn\s*sống|kết\s*thúc\s*cuộc\s*đời|tự\s*làm\s*hại/iu',
        '/bạo\s*hành|bị\s*đánh\s*đập|đe\s*dọa\s*tính\s*mạng|giết\s*người/iu',
    ];

    /** @var list<string> */
    protected const FORBIDDEN_AI_PHRASES = [
        'giải hạn',
        'cúng sao',
        'cúng giải',
        'chắc chắn ly hôn',
        'chắc chắn đổ vỡ',
        'không được cưới',
        'bỏ cưới ngay',
        'chắc chắn ngoại tình',
        'khắc cha mẹ',
        'con khắc bố mẹ',
        'chắc chắn phá sản',
        'chắc chắn tai nạn',
        'chết yểu',
        'đoản thọ',
        'vô sinh chắc chắn',
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
                    'notice' => 'Trong lúc tâm trạng quá nặng nề hoặc có nguy cơ mất an toàn, xem Tử Vi không phải giải pháp phù hợp. Hãy liên hệ ngay người thân tin cậy, bác sĩ hoặc tổng đài hỗ trợ khẩn cấp tại Việt Nam.',
                    'resources' => [
                        ['label' => 'Cấp cứu y tế khẩn cấp', 'detail' => 'Gọi 115 (24/7)'],
                        ['label' => 'Tổng đài Quốc gia Bảo vệ Trẻ em & Hỗ trợ tâm lý gia đình', 'detail' => 'Gọi 111 (Miễn phí, 24/7)'],
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
     * Hậu kiểm nội dung luận giải AI (cả lá số cá nhân lẫn Nhân Duyên Song Mệnh).
     *
     * @param  array<string, mixed>  $output
     * @return array{valid: bool, issues: list<string>}
     */
    public function validateAiOutput(array $output): array
    {
        $issues = [];
        $haystack = mb_strtolower(json_encode($output, JSON_UNESCAPED_UNICODE) ?: '', 'UTF-8');

        foreach (self::FORBIDDEN_AI_PHRASES as $phrase) {
            if (str_contains($haystack, $phrase)) {
                $issues[] = "Vi phạm chuẩn nhân văn Mây Hẹn: {$phrase}";
            }
        }

        // Chặn bịa tỷ lệ % hợp tuổi / % ly hôn / % chính xác giả
        if (preg_match('/(hợp\s*nhau|khả\s*năng\s*ly\s*hôn|độ\s*chính\s*xác|hợp\s*tuổi)\s*\d{1,3}\s*%/iu', $haystack) === 1) {
            $issues[] = 'AI gán tỷ lệ phần trăm hợp tuổi/ly hôn trái quy chuẩn Mây Hẹn.';
        }

        return [
            'valid' => $issues === [],
            'issues' => $issues,
        ];
    }
}
