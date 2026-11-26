<?php

declare(strict_types=1);

namespace App\Services\Bazi;

/**
 * Kiểm định an toàn & chất lượng nội dung Bát Tự (Safety Guard).
 *
 * - Chặn nội dung khủng hoảng tâm lý (tự hại).
 * - Chặn mê tín hù dọa, tiên đoán bệnh/tử vong/ngoại tình/ly hôn, chấm % hợp nhau giả và văn mẫu AI.
 */
class BaziSafetyGuardService
{
    /** @var list<string> */
    protected const CRISIS_PATTERNS = [
        '/tự\s*tử/iu',
        '/muốn\s*chết/iu',
        '/kết\s*thúc\s*cuộc\s*đời/iu',
        '/tự\s*làm\s*hại/iu',
    ];

    /** @var list<string> */
    protected const BANNED_OUTPUT_PATTERNS = [
        '/hợp\s*nhau\s*\d{1,3}\s*%/iu',
        '/tỷ\s*lệ\s*(ly\s*hôn|kết\s*hôn)\s*\d{1,3}\s*%/iu',
        '/chắc\s*chắn\s*(ly\s*hôn|ngoại\s*tình|phá\s*sản|mắc\s*bệnh|chết)/iu',
        '/cúng\s*giải\s*hạn/iu',
        '/vũ\s*trụ\s*đang\s*gửi\s*đến\s*bạn/iu',
    ];

    /**
     * Kiểm tra câu hỏi người dùng trước khi gọi AI.
     *
     * @return array{safe: bool, code: ?string, message: ?string}
     */
    public function inspectQuestion(string $text): array
    {
        foreach (self::CRISIS_PATTERNS as $p) {
            if (preg_match($p, $text)) {
                return [
                    'safe' => false,
                    'code' => 'SAFETY_CRISIS_BLOCK',
                    'message' => 'Nếu bạn đang trải qua giai đoạn rất khó khăn hoặc có ý nghĩ tự làm hại bản thân, xin hãy liên hệ ngay người thân hoặc đường dây hỗ trợ tâm lý / y tế gần nhất. Bát Tự không thể thay thế sự đồng hành chuyên môn.',
                ];
            }
        }

        return ['safe' => true, 'code' => null, 'message' => null];
    }

    /**
     * Kiểm tra đầu ra AI có vi phạm quy tắc an toàn hoặc văn mẫu không.
     */
    public function isOutputSafe(string $text): bool
    {
        foreach (self::BANNED_OUTPUT_PATTERNS as $p) {
            if (preg_match($p, $text)) {
                return false;
            }
        }

        return true;
    }
}
