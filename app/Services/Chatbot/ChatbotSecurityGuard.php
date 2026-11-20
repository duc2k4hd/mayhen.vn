<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

class ChatbotSecurityGuard
{
    /**
     * Các mẫu Prompt Injection / Jailbreak / Trích xuất System Prompt.
     *
     * @var array<string, string>
     */
    protected const PROMPT_INJECTION_PATTERNS = [
        'ignore_instructions' => '/\b(ignore|disregard|forget|override|bypass)\b.{0,40}\b(previous|above|prior|all|system|rules?|instructions?|prompt)\b/iu',
        'vn_ignore_instructions' => '/(bỏ\s*qua|quên\s*(hết|đi|toàn\s*bộ)|phớt\s*lờ|vượt\s*rào).{0,35}(hướng\s*dẫn|chỉ\s*thị|quy\s*tắc|lệnh|prompt|hệ\s*thống)/iu',
        'system_prompt_leak' => '/\b(system\s*prompt|developer\s*prompt|hidden\s*instructions?|reveal\s*(your|the)\s*prompt|show\s*your\s*instructions?)\b/iu',
        'vn_system_prompt_leak' => '/(lộ|hiển\s*thị|in\s*ra|đọc\s*lại|cho\s*xem).{0,25}(system\s*prompt|prompt\s*gốc|chỉ\s*thị\s*hệ\s*thống|câu\s*lệnh\s*gốc)/iu',
        'role_override' => '/\b(you\s*are\s*now|act\s*as\s*(a|an)?\s*(root|admin|hacker|dan|unrestricted)|developer\s*mode|jailbreak|do\s*anything\s*now)\b/iu',
        'delimiter_spoof' => '/<\s*\/?\s*(system|verified_project_data|untrusted_user_message|instructions?)\s*>/iu',
    ];

    /**
     * Các mẫu dò tìm khóa bí mật, file .env, thực thi mã độc, XSS, SQL Injection.
     *
     * @var array<string, string>
     */
    protected const EXPLOIT_AND_SECRET_PATTERNS = [
        'secret_fishing' => '/(\.env\b|NINEROUTER_|BEARER_TOKEN|API_KEY|DB_PASSWORD|APP_KEY|SEPAY_WEBHOOK|SECRET_KEY|sk-[a-zA-Z0-9]{16,})/iu',
        'xss_payload' => '/(<\s*script\b|javascript\s*:|on(error|load|click|mouseover)\s*=|<\s*iframe\b|<\s*svg\b[^>]*on)/iu',
        'sqli_payload' => '/(\bunion\s+all?\s+select\b|\bdrop\s+table\b|\bdelete\s+from\b|\binsert\s+into\b|\bor\s+1\s*=\s*1\b)/iu',
        'code_exec' => '/(\b(eval|exec|shell_exec|system|passthru|phpinfo|proc_open)\s*\(|\/etc\/passwd|\bcat\s+\.env\b)/iu',
    ];

    /**
     * Các chuỗi tuyệt đối không bao giờ được xuất hiện trong câu trả lời của AI.
     *
     * @var array<string, string>
     */
    protected const FORBIDDEN_OUTPUT_PATTERNS = [
        'leaked_secret' => '/(sk-[a-zA-Z0-9]{16,}|base64:[A-Za-z0-9+\/=]{20,}|whsec_[A-Za-z0-9]+|NINEROUTER_API_KEY|DB_PASSWORD|CLOUDFLARE_R2_SECRET_ACCESS_KEY|CLOUDFLARE_API_TOKEN)/iu',
        'raw_html_script' => '/(<\s*script\b|javascript\s*:|<\s*iframe\b|on(error|load|click)\s*=)/iu',
        'prompt_boundary_leak' => '/(verified_project_data|untrusted_user_message|BEGIN_SYSTEM_RULES)/iu',
    ];

    /**
     * Làm sạch văn bản thuần, loại bỏ thẻ HTML và ký tự điều khiển ẩn.
     */
    public function sanitizePlainText(string $input, int $maxLength = 600): string
    {
        // Loại bỏ ký tự vô hình (zero-width / bidi override) thường dùng để lách bộ lọc
        $cleaned = preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{FEFF}]/u', '', $input) ?? $input;
        $cleaned = strip_tags($cleaned);
        $cleaned = preg_replace('/\s+/u', ' ', $cleaned) ?? $cleaned;
        $cleaned = trim($cleaned);

        if (mb_strlen($cleaned, 'UTF-8') > $maxLength) {
            $cleaned = mb_substr($cleaned, 0, $maxLength, 'UTF-8');
        }

        return $cleaned;
    }

    /**
     * Kiểm tra đầu vào của khách hàng theo nguyên tắc Zero-Trust (không bao giờ tin prompt khách hàng).
     *
     * @return array{blocked: bool, clean_text: string, flags: array<int, string>}
     */
    public function inspectUserMessage(string $rawInput, int $maxLength = 600): array
    {
        $flags = [];
        $normalizedForScan = preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{FEFF}]/u', '', $rawInput) ?? $rawInput;

        foreach (self::PROMPT_INJECTION_PATTERNS as $code => $pattern) {
            if (preg_match($pattern, $normalizedForScan) === 1) {
                $flags[] = 'injection:'.$code;
            }
        }

        foreach (self::EXPLOIT_AND_SECRET_PATTERNS as $code => $pattern) {
            if (preg_match($pattern, $normalizedForScan) === 1) {
                $flags[] = 'exploit:'.$code;
            }
        }

        $cleanText = $this->sanitizePlainText($rawInput, $maxLength);

        return [
            'blocked' => $flags !== [],
            'clean_text' => $cleanText,
            'flags' => array_values(array_unique($flags)),
        ];
    }

    /**
     * Kiểm duyệt đầu ra từ AI trước khi trả về trình duyệt (Zero-Trust Output Verification).
     *
     * @return array{safe: bool, clean_text: string, flags: array<int, string>}
     */
    public function inspectAiOutput(string $rawOutput): array
    {
        $flags = [];

        foreach (self::FORBIDDEN_OUTPUT_PATTERNS as $code => $pattern) {
            if (preg_match($pattern, $rawOutput) === 1) {
                $flags[] = 'output:'.$code;
            }
        }

        // Chặn markdown link nguy hiểm [text](javascript:...) hoặc URL lạ ngoài mayhen.vn
        if (preg_match('/\]\(\s*(javascript|data|vbscript)\s*:/iu', $rawOutput) === 1) {
            $flags[] = 'output:unsafe_markdown_link';
        }

        $clean = strip_tags($rawOutput);
        $clean = preg_replace('/\r\n|\r/', "\n", $clean) ?? $clean;
        $clean = preg_replace('/\n{3,}/', "\n\n", $clean) ?? $clean;
        $clean = trim($clean);

        if ($clean === '') {
            $flags[] = 'output:empty';
        }

        if (mb_strlen($clean, 'UTF-8') > 1800) {
            $clean = mb_substr($clean, 0, 1800, 'UTF-8').'...';
        }

        return [
            'safe' => $flags === [],
            'clean_text' => $clean,
            'flags' => array_values(array_unique($flags)),
        ];
    }
}
