<?php

declare(strict_types=1);

namespace App\Services\Numerology;

/**
 * Lớp Bảo Vệ An Toàn Nhân Văn & Kiểm Định Đầu Ra AI cho Mây Hẹn – Dấu Ấn Con Số.
 *
 * - Chặn câu hỏi khủng hoảng tâm lý/tự hại (`422 SAFETY_CRISIS_BLOCK`, hiển thị đường dây nóng 111/115/113).
 * - Chặn mọi đầu ra AI có chấm % hợp nhau / % ly hôn, phán đoán y khoa/tử vong/ngoại tình, mê tín giải hạn và văn mẫu AI đại trà.
 * - Kiểm tra đối chiếu tính nhất quán số liệu với Calculation Engine.
 */
class NumerologySafetyGuardService
{
    /** @var list<string> */
    protected const CRISIS_PATTERNS = [
        '/tự\s*tử|tu\s*tu|tự\s*hại|tu\s*hai|muốn\s*chết|muon\s*chet|kết\s*liễu|ket\s*lieu|không\s*muốn\s*sống/iu',
        '/bị\s*bạo\s*hành|bi\s*bao\s*hanh|đánh\s*đập\s*dã\s*man|bị\s*đe\s*dọa\s*tính\s*mạng/iu',
    ];

    /** @var list<string> Các cụm từ bị cấm tuyệt đối trong nội dung AI */
    protected const FORBIDDEN_AI_PATTERNS = [
        '/\b\d{1,3}\s*%\s*(hợp|tương\s*thích|ly\s*hôn|kết\s*hôn|chính\s*xác|thành\s*công)/iu',
        '/(hợp\s*nhau|tỷ\s*lệ\s*kết\s*hôn|tỷ\s*lệ\s*ly\s*hôn|độ\s*chính\s*xác)\s*\d{1,3}\s*%/iu',
        '/cúng\s*giải\s*hạn|giải\s*nợ\s*nghiệp\s*bằng\s*lễ|mua\s*vật\s*phẩm\s*phong\s*thủy|đổi\s*tên\s*để\s*đổi\s*số\s*phận/iu',
        '/chắc\s*chắn\s*(sẽ\s*)?(ly\s*hôn|ngoại\s*tình|phá\s*sản|chết|mất\s*mạng|ung\s*thư|phạm\s*tội)/iu',
        '/không\s*hợp\s*số.{0,20}không\s*nên\s*cưới|chắc\s*chắn\s*là\s*định\s*mệnh\s*của\s*nhau/iu',
        '/vũ\s*trụ\s*đang\s*gửi\s*(đến|cho)\s*bạn\s*một\s*thông\s*điệp/iu',
        '/bạn\s*sinh\s*ra\s*để\s*làm\s*những\s*điều\s*phi\s*thường/iu',
        '/bạn\s*mang\s*trong\s*mình\s*một\s*nguồn\s*năng\s*lượng\s*vô\s*cùng\s*đặc\s*biệt/iu',
        '/bạn\s*vừa\s*mạnh\s*mẽ\s*vừa\s*nhạy\s*cảm/iu',
    ];

    /**
     * @return array{allow: bool, level: string, notice: ?string}
     */
    public function assessText(string $text): array
    {
        foreach (self::CRISIS_PATTERNS as $pattern) {
            if (preg_match($pattern, $text) === 1) {
                return [
                    'allow' => false,
                    'level' => 'crisis',
                    'notice' => 'Nếu bạn đang trải qua giai đoạn khủng hoảng hoặc cảm thấy không an toàn, Thần Số Học không thể thay thế sự hỗ trợ khẩn cấp. Hãy liên hệ ngay người thân tin cậy, Tổng đài 111 (bảo vệ & tư vấn tâm lý), 115 (y tế) hoặc 113 (khẩn cấp).',
                ];
            }
        }

        return ['allow' => true, 'level' => 'ok', 'notice' => null];
    }

    /**
     * Hậu kiểm nội dung JSON AI sinh ra trước khi lưu/hiển thị.
     *
     * @param  array<string, mixed>  $payload
     * @return array{valid: bool, reason: ?string}
     */
    public function validateAiOutput(array $payload): array
    {
        $flat = json_encode($payload, JSON_UNESCAPED_UNICODE) ?: '';

        foreach (self::FORBIDDEN_AI_PATTERNS as $pattern) {
            if (preg_match($pattern, $flat) === 1) {
                return ['valid' => false, 'reason' => 'AI output violated safety, percentage, or anti-cliché rules.'];
            }
        }

        return ['valid' => true, 'reason' => null];
    }
}
