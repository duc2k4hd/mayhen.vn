<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Models\AiChatbotLog;
use App\Services\AiQualityAndCostObservatoryService;
use App\Services\Chatbot\ChatbotSecurityGuard;
use Illuminate\Support\Str;

/**
 * Deterministic Security & Policy Engine (Thực thi bằng code, không phụ thuộc đánh giá của AI).
 * Chống: Prompt Injection, giả danh Admin, đòi xuất tài liệu nội bộ, Cross-Tenant/IDOR,
 * Data/Memory Poisoning, Feedback Manipulation, URL giả mạo, rò rỉ PII/Secret và Verbatim Training Dump.
 */
class SecurityPolicyGate
{
    /**
     * Các mẫu tấn công bổ sung chuyên biệt cho Mây Hẹn Intelligence Engine.
     *
     * @var array<string, string>
     */
    protected const EXTRA_THREAT_PATTERNS = [
        // 1. Giả danh Admin / Developer / Quyền nội bộ
        'admin_impersonation' => '/(tôi\s+là\s+(admin|quản\s*trị\s*viên|chủ\s*hệ\s*thống|developer)|developer\s+đã\s+cho\s+phép|lệnh\s+(từ\s+)?(admin|quản\s*trị)|quyền\s+quản\s*trị|i\s+am\s+(the\s+)?admin|admin\s+override|chế\s+độ\s+debug)/iu',

        // 2. Đòi xuất toàn bộ dữ liệu huấn luyện / prompt / DB
        'training_dump_exfil' => '/(xuất|in\s+ra|liệt\s+kê|copy|hiển\s+thị|cho\s+xem)\s+(toàn\s+bộ|tất\s+cả|nguyên\s+văn|full)\s+(tài\s+liệu|tri\s+thức|knowledge|training|dữ\s+liệu\s+huấn\s+luyện|database|prompt)/iu',

        // 3. Cross-tenant / IDOR truyền thống
        'cross_tenant_idor' => '/(dữ\s+liệu|thiệp|email|số\s+điện\s+thoại|khách\s+mời|đơn\s+hàng|mật\s+khẩu)\s+(của\s+)?(tài\s+khoản\s+khác|người\s+dùng\s+khác|user\s+khác|khách\s+hàng\s+khác|user\s*id\s*\d+|tài\s+khoản\s*#?\d+)/iu',

        // 4. Đầu độc bộ nhớ / tri thức
        'memory_or_data_poisoning' => '/((hãy\s+)?(ghi\s+nhớ|lưu\s+lại|lưu\s+nội\s+dung\s+này|cập\s+nhật|học\s+thuộc)|từ\s+nay\s+(trở\s+đi\s+)?hãy\s+(nói|báo|trả\s+lời)).{0,55}(kiến\s+thức\s+chính\s+thức|giá|miễn\s+phí|giảm\s+giá|voucher|mã\s+giảm|chính\s+sách|hotline|số\s+tài\s+khoản)/iu',

        // 5. Giả mạo thẻ giao thức / chỉ thị nội bộ / kết quả công cụ / khối khôi phục ngữ cảnh (Bắt tổng quát mọi thẻ ALL-CAPS điều khiển)
        'control_envelope_spoof' => '/(\[\s*(?:\/|END\s+|BEGIN\s+)?(?:TOOL_RESULT|TOOL_CALL|TOOL_OUTPUT|FUNCTION_CALL|FUNCTION_RESULT|CONTEXT_RECOVERY|STATE_RECOVERY|MEMORY_RECOVERY|SYSTEM|INTERNAL|ADMIN|POLICY|SECURITY|AUTH|OVERRIDE|DIRECTIVE|INSTRUCTION|DEVELOPER|DEBUG|UPDATE|PRIVILEGED|PROMPT)(?:[\s_:\-][A-Z0-9_\s:\-]{0,40})?\]|<\s*\/?\s*(?:tool_result|tool_call|tool_output|context_recovery|system_override|policy_gate|admin_override|internal_state|internal_update)\b[^>]*>|BEGIN_(?:SYSTEM|POLICY|TOOL|ADMIN|OVERRIDE|CONTEXT|UPDATE))/u',

        // 6. Tiêm cặp khóa=giá trị giả mạo trạng thái phân quyền / chính sách (Fake Authority Key=Value)
        'fake_authority_kv' => '/(\b(status|policy_check|previous_policy_check|auth_status|security_check)\s*[:=]\s*(pass|passed|approved|allow|allowed|granted|verified|bypass)\b|\b(authorized|is_admin|admin_override|previous_admin_authorization|bypass_policy|skip_auth|trusted_source)\s*[:=]\s*(true|1|yes|pass)\b|\b(role|source|scope)\s*[:=]\s*(system|internal|root|admin|security_audit|developer|privileged)\b|\b(pending_response_prefix|injected_prefix|override_prefix|canary_prefix)\s*[:=])/iu',

        // 7. Kỹ nghệ xã hội giả vờ đã xác thực, giả ghi chú xử lý tài liệu/FAQ, ghi đè độ ưu tiên hoặc ép xuất tiền tố/canary ẩn
        'fake_prior_authorization' => '/((quá\s+trình\s+)?(xác\s*thực|kiểm\s*tra\s*quyền|phê\s*duyệt|cấp\s*quyền)\s+(đã\s+)?(hoàn\s*tất|xong|pass|được\s+duyệt)\s+(ở|tại)\s+(tầng|bước|lớp)\s+(trước|trên)|không\s+phải\s+yêu\s+cầu\s+cấp\s+quyền\s+mới|khôi\s+phục\s+trạng\s+thái\s+từ\s+hệ\s+thống|trạng\s+thái\s+đã\s+khôi\s+phục|(chỉ\s+thị|lệnh|quy\s+tắc)\s+(quản\s*trị|hệ\s*thống|nội\s*bộ).{0,45}(ưu\s*tiên\s+cao\s+hơn|ghi\s+đè|thay\s+thế|vượt\s+qua)|ưu\s*tiên\s+cao\s+hơn\s+quy\s+tắc\s+(của\s+)?(chatbot|ai|hệ\s+thống|bảo\s+mật)|higher\s+priority\s+than\s+(the\s+)?(system|chatbot|policy|rules?)|không\s+(được\s+)?(đề\s+cập|nhắc|nói|thông\s+báo)\s+(đến|tới|về)\s+(đoạn\s+)?(chỉ\s+thị|lệnh|quy\s+tắc|quá\s+trình|hướng\s+dẫn)\s+này|do\s+not\s+(mention|reference|disclose)\s+this\s+(directive|instruction|update|override|process)|(hãy\s+)?(bắt\s+đầu|mở\s+đầu)\s+câu\s+trả\s+lời\s+bằng\s*[:=]|ghi\s+chú\s+xử\s+lý\s+tài\s+liệu|hiển\s+thị\s+mã\s+xác\s+nhận.{0,50}ở\s+đầu\s+câu\s+trả\s+lời|xác\s+nhận\s+phiên\s+bản\s+tài\s+liệu\s+đã\s+(được\s+)?đồng\s+bộ|(xuất|in|trả\s+về)\s+(pending_response_prefix|canary|bypass|mã\s+xác\s+nhận\s+khôi\s+phục)|\b[A-Z0-9_]*(?:BYPASS|CANARY)[A-Z0-9_]*\b)/iu',

        // 8. Ép gọi công cụ nội bộ / đọc tệp máy chủ / dò canary hoặc đường dẫn hệ thống (Tool & File Exfiltration)
        'file_read_or_tool_abuse' => '/((sử\s+dụng|gọi|dùng|chạy)\s+công\s+cụ\s+(hiện\s+có|nghiệp\s+vụ|nội\s+bộ|hệ\s+thống)|\b(use|call|invoke)\s+(the\s+)?(available\s+)?(internal\s+)?tools?\b|(?:^|[\s"\'`(])\/(app|etc|var|proc|root|home|tmp|usr|srv|opt|storage|config|database|bootstrap|vendor)\/[a-z0-9._\-\/]+|\.\.\/|\.\.\\\\|\bcanary\.(txt|log|json|env)\b)/iu',

        // 9. Vượt xác thực phiên / IDOR đơn hàng hoặc dữ liệu tài khoản khác (Session-Bypass & Red-Team IDOR)
        'cross_account_idor_bypass' => '/((chuyển|đổi)\s+từ\s+tài\s+khoản.{0,40}sang|phiên\s+đăng\s+nhập\s+(hiện\s+tại\s+)?chưa\s+đồng\s+bộ|không\s+dựa\s+vào\s+(tên\s+)?tài\s+khoản\s+đang\s+(hiển\s+thị|đăng\s+nhập)|ignore\s+(the\s+)?(current\s+)?(session|logged[- ]?in\s+account|auth)|(truy\s+vấn|tra\s+cứu)\s+đơn\s+hàng.{0,90}(công\s+cụ|json|số\s+điện\s+thoại|trạng\s+thái\s+thanh\s+toán|mã\s+lỗi)|\bORDER-[A-Z0-9-]+-SECRET(?:-[A-Z0-9]+)?\b)/iu',

        // 10. Chặn tuyệt đối prompt chứa JSON cấu trúc, mã nguồn hoặc bất kỳ cặp thẻ HTML/XML nào
        'json_or_code_payload' => '/(```|\{[^\}]{0,500}"[^"\n]{1,60}"\s*:\s*(?:"|\'|\[|\{|true\b|false\b|null\b|-?\d)|\[\s*\{\s*"[^"\n]{1,60}"\s*:|<\?(?:php|=)|\b(?:function\s+[a-z_$][\w$]*\s*\(|(?:const|let|var)\s+[a-z_$][\w$]*\s*=|public\s+function\s+\w+\s*\(|private\s+function\s+\w+\s*\(|protected\s+function\s+\w+\s*\(|import\s+[\w\{\*\s,]+\s+from\s+["\']|export\s+(?:default|const|function|class)\b|def\s+[a-z_]\w*\s*\([^)]*\)\s*:|class\s+[A-Z]\w*\s*(?:\([^)]*\))?\s*[\{:]|console\.log\s*\(|document\.(?:querySelector|getElementById|cookie)|window\.location|localStorage\.|sessionStorage\.|fetch\s*\(\s*["\']|curl\s+-[a-zA-Z]|wget\s+https?:|bash\s+-c|powershell\s+-|rm\s+-rf\b|select\s+[\w\*\s,]+\s+from\s+[a-z_]\w*)|=>\s*\{|<\s*([a-z][a-z0-9_-]{0,30})\b[^>]*>.*?<\s*\/\s*\1\s*>|<\s*\/\s*[a-z][a-z0-9_-]{0,30}\s*>|<!--.*?-->|<\s*(?:div|span|form|input|img|a|p|br|hr|style|link|meta|body|html|object|embed|svg|iframe|script|button|textarea|select|table|tr|td|ul|ol|li|h[1-6])\b[^>]*>)/isu',
    ];

    /**
     * Allowlist các prefix đường dẫn nội bộ hợp lệ được phép xuất hiện trong phản hồi khách hàng.
     *
     * @var list<string>
     */
    protected const ALLOWED_INTERNAL_PREFIXES = [
        '/docs',
        '/mau-thiep',
        '/xem-thu',
        '/tao-thiep',
        '/bang-gia',
        '/lien-he',
        '/dich-vu',
        '/cau-hoi-thuong-gap',
        '/dang-ky',
        '/dang-nhap',
    ];

    /**
     * Dấu hiệu phản hồi bị trượt vai sang tư vấn tâm lý cá nhân, y tế/thai kỳ hoặc chuyên môn ngoài Mây Hẹn.
     *
     * @var array<string, string>
     */
    protected const OUT_OF_SCOPE_REPLY_PATTERNS = [
        'therapist_counseling' => '/(ở\s+đây\s+để\s+lắng\s+nghe|kể\s+(cho\s+)?mình\s+nghe|không\s+phán\s+xét|tâm\s+sự\s+với\s+mình|chuyên\s+gia\s+tâm\s+lý|bác\s+sĩ\s+tâm\s+lý|hẳn\s+rất\s+tủi\s+thân|tiếc\s+vì\s+bạn\s+đang\s+thấy\s+buồn)/iu',
        'medical_pregnancy_advice' => '/(que\s+thử(\s+thai)?|xác\s+nhận\s+(việc\s+)?mang\s+thai|cơ\s+sở\s+y\s+tế|trao\s+đổi\s+với\s+bác\s+sĩ|đi\s+khám\s+bác\s+sĩ|chẩn\s+đoán\s+bệnh|kê\s+đơn\s+thuốc|uống\s+thuốc\s+gì)/iu',
    ];

    /**
     * Từ khóa neo phạm vi nền tảng Mây Hẹn & ngành cưới (dùng cho kiểm soát hậu kiểm khi không khớp dữ liệu DB).
     *
     * @var list<string>
     */
    protected const DOMAIN_ANCHOR_TERMS = [
        'may hen', 'mayhen', 'thiep', 'cuoi', 'hon le', 'dam cuoi', 'tiec cuoi', 'ngay cuoi',
        'an hoi', 'dam ngo', 'tan hon', 'vu quy', 'bao hy', 'dinh hon', 'thanh hon', 'le ruoc dau',
        'co dau', 'chu re', 'nha trai', 'nha gai', 'trap', 'sinh le', 'mung cuoi', 'phong bi',
        'mau thiep', 'tao thiep', 'bang gia', 'studio', 'rsvp', 'khach moi', 'ban tiec', 'check in',
        'hop thoi gian', 'vietqr', 'sepay', 'xem thu', 'xuat ban', 'huong dan', '/docs', '/mau-thiep',
        '/tao-thiep', '/bang-gia', '/xem-thu', '/lien-he', 'goi dich vu', 'premium', 'mien phi',
        'thanh toan', 'chuyen khoan', 'hoan tien', 'nang cap', 'chi phi', 'tai khoan', 'dang ky', 'dang nhap',
        'ngu hanh', 'menh', 'phong cach', 'album', 'nhac nen', 'zalo', 'qr', 'hoa cuoi', 'vay cuoi',
        'ao dai', 'nha hang tiec', 'thiep moi', 'thiep dien tu', 'thiep online', 'phong tuc',
    ];

    public function __construct(
        protected ChatbotSecurityGuard $baseGuard
    ) {}

    /**
     * Phân loại rủi ro đầu vào bằng quy tắc code cứng.
     *
     * @return array{blocked: bool, risk_level: string, clean_text: string, flags: list<string>, safe_refusal: ?string}
     */
    public function inspectAndClassifyInput(string $rawInput, string $intent = 'general'): array
    {
        $baseCheck = $this->baseGuard->inspectUserMessage($rawInput, 600);
        $flags = $baseCheck['flags'];
        $normalizedForScan = preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{FEFF}]/u', '', $rawInput) ?? $rawInput;

        foreach (self::EXTRA_THREAT_PATTERNS as $code => $pattern) {
            if (preg_match($pattern, $normalizedForScan) === 1) {
                $flags[] = 'policy:'.$code;
            }
        }

        $flags = array_values(array_unique($flags));
        $blocked = $flags !== [];

        if ($blocked) {
            $safeRefusal = $this->buildRefusalMessage($flags);

            return [
                'blocked' => true,
                'risk_level' => AiChatbotLog::RISK_BLOCKED,
                'clean_text' => $baseCheck['clean_text'],
                'flags' => $flags,
                'safe_refusal' => $safeRefusal,
            ];
        }

        $ascii = Str::ascii(mb_strtolower($baseCheck['clean_text'], 'UTF-8'));

        // Phân tầng High-Risk: tài chính, hoàn tiền, thanh toán, pháp lý, cam kết, bảo mật dữ liệu
        $highRiskKeywords = [
            'hoan tien', 'thanh toan', 'chuyen khoan', 'sepay', 'vietqr', 'hoa don',
            'giam gia', 'ma giam', 'voucher', 'khuyen mai', 'bao mat', 'quyen rieng tu',
            'xoa du lieu', 'hop dong', 'cam ket', 'bang gia', 'chi phi', 'nang cap',
        ];
        foreach ($highRiskKeywords as $kw) {
            if (str_contains($ascii, $kw)) {
                return [
                    'blocked' => false,
                    'risk_level' => AiChatbotLog::RISK_HIGH,
                    'clean_text' => $baseCheck['clean_text'],
                    'flags' => [],
                    'safe_refusal' => null,
                ];
            }
        }

        // Phân tầng Complex: so sánh, đa điều kiện, câu dài hoặc hỏi kỹ thuật/quy trình
        $complexKeywords = ['so sanh', 'khac nhau', 'nhung', 'tuy nhien', 'neu ', 'truong hop', 'co mat du lieu', 'ten mien rieng', 'da ngon ngu'];
        $isLong = mb_strlen($baseCheck['clean_text'], 'UTF-8') >= 95;
        if ($isLong || $intent === 'pricing' || $intent === 'docs') {
            return [
                'blocked' => false,
                'risk_level' => AiChatbotLog::RISK_COMPLEX,
                'clean_text' => $baseCheck['clean_text'],
                'flags' => [],
                'safe_refusal' => null,
            ];
        }

        foreach ($complexKeywords as $kw) {
            if (str_contains($ascii, $kw)) {
                return [
                    'blocked' => false,
                    'risk_level' => AiChatbotLog::RISK_COMPLEX,
                    'clean_text' => $baseCheck['clean_text'],
                    'flags' => [],
                    'safe_refusal' => null,
                ];
            }
        }

        return [
            'blocked' => false,
            'risk_level' => AiChatbotLog::RISK_SIMPLE,
            'clean_text' => $baseCheck['clean_text'],
            'flags' => [],
            'safe_refusal' => null,
        ];
    }

    /**
     * Kiểm duyệt đầu ra cuối cùng bằng code cứng (Zero-Trust Output Policy Gate).
     *
     * @param  array<int, array<string, mixed>>  $matchedKnowledge
     * @return array{safe: bool, clean_text: string, flags: list<string>, verbatim_dump: bool}
     */
    public function inspectFinalOutput(string $rawOutput, string $verifiedContext, array $matchedKnowledge = []): array
    {
        $base = $this->baseGuard->inspectAiOutput($rawOutput);
        $flags = $base['flags'];
        $clean = $base['clean_text'];

        // 1. Chặn URL bên ngoài (http:// hoặc https:// không phải mayhen.vn)
        if (preg_match_all('/https?:\/\/[^\s\)\]]+/iu', $clean, $extMatches) > 0) {
            foreach ($extMatches[0] as $foundUrl) {
                $host = strtolower((string) (parse_url($foundUrl, PHP_URL_HOST) ?? ''));
                if ($host !== '' && $host !== 'mayhen.vn' && ! str_ends_with($host, '.mayhen.vn')) {
                    $flags[] = 'policy:external_url_blocked';
                    break;
                }
            }
        }

        // 2. Xác minh toàn bộ markdown link nội bộ nằm trong Allowlist
        if (preg_match_all('/\]\((\/[^)\s]+)\)/u', $clean, $linkMatches) > 0) {
            foreach ($linkMatches[1] as $internalPath) {
                if (! $this->isAllowedInternalPath($internalPath)) {
                    $flags[] = 'policy:disallowed_internal_url';
                    break;
                }
            }
        }

        // 3. Chặn rò rỉ PII (email / SĐT / STK ngân hàng) nếu không hề xuất hiện trong dữ liệu xác thực gốc
        $contextLower = mb_strtolower($verifiedContext, 'UTF-8');
        if (preg_match_all('/(?:\+84|0)(?:3[2-9]|5[2689]|7[06-9]|8[1-9]|9[0-9])[0-9]{7}/', $clean, $phoneMatches) > 0) {
            foreach ($phoneMatches[0] as $phone) {
                if (! str_contains($contextLower, mb_strtolower($phone, 'UTF-8'))) {
                    $flags[] = 'policy:unverified_phone_or_pii';
                    break;
                }
            }
        }

        if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $clean, $emailMatches) > 0) {
            foreach ($emailMatches[0] as $email) {
                if (! str_contains($contextLower, mb_strtolower($email, 'UTF-8'))) {
                    $flags[] = 'policy:unverified_email_or_pii';
                    break;
                }
            }
        }

        // 4. Kiểm tra rò rỉ từ khóa phân vùng nội bộ, đường dẫn máy chủ hoặc chuỗi bypass/canary
        if (preg_match('/(conversation_history_untrusted|customer_memory_scoped|BEGIN_SYSTEM|END_SYSTEM|TOOL_RESULT|CONTEXT_RECOVERY|pending_response_prefix|RAG_BYPASS|(?:^|[\s"\'`(])\/(app|etc|var\/www|proc|root)\/[a-z0-9._\-\/]+)/iu', $clean) === 1) {
            $flags[] = 'policy:internal_tag_leak';
        }

        // 5. Chặn Brand Safety (từ khóa tiêu cực/nhạy cảm ngày cưới)
        $brandSafety = AiQualityAndCostObservatoryService::checkSafetyGuardrails($clean);
        if (! $brandSafety['is_safe']) {
            $flags[] = 'policy:brand_safety_violation';
        }

        // 6. Kiểm tra chống sao chép nguyên văn bài training dài (Verbatim Training Dump)
        $verbatimDump = false;
        foreach ($matchedKnowledge as $entry) {
            $sourceAnswer = trim((string) ($entry['answer'] ?? ''));
            if (mb_strlen($sourceAnswer, 'UTF-8') >= 280) {
                similar_text(
                    mb_strtolower($sourceAnswer, 'UTF-8'),
                    mb_strtolower($clean, 'UTF-8'),
                    $percent
                );
                if ($percent >= 82.0) {
                    $verbatimDump = true;
                    break;
                }
            }
        }

        $flags = array_values(array_unique($flags));

        return [
            'safe' => $flags === [],
            'clean_text' => $clean,
            'flags' => $flags,
            'verbatim_dump' => $verbatimDump,
        ];
    }

    /**
     * Kiểm tra đầu vào có chạm danh sách chủ đề bị cấm do Quản trị viên cấu hình (`forbidden_topics`) hay không.
     *
     * @param  list<string>  $forbiddenTopics
     */
    public function matchForbiddenTopic(string $cleanMessage, array $forbiddenTopics): ?string
    {
        if ($forbiddenTopics === [] || trim($cleanMessage) === '') {
            return null;
        }

        $msgLower = mb_strtolower($cleanMessage, 'UTF-8');
        $msgAscii = Str::ascii($msgLower);

        foreach ($forbiddenTopics as $rawTopic) {
            $topic = trim($rawTopic);
            if (mb_strlen($topic, 'UTF-8') < 2) {
                continue;
            }

            $topicLower = mb_strtolower($topic, 'UTF-8');
            $topicAscii = Str::ascii($topicLower);

            if (preg_match('/(?<![\p{L}\p{N}])'.preg_quote($topicLower, '/').'(?![\p{L}\p{N}])/u', $msgLower) === 1) {
                return $topic;
            }

            if ($topicAscii !== '' && preg_match('/(?<![a-z0-9])'.preg_quote($topicAscii, '/').'(?![a-z0-9])/i', $msgAscii) === 1) {
                return $topic;
            }
        }

        return null;
    }

    /**
     * Kiểm tra văn bản có chứa neo ngữ cảnh thuộc nền tảng Mây Hẹn hoặc ngành cưới hay không.
     */
    public function hasDomainAnchor(string $text): bool
    {
        if (trim($text) === '') {
            return false;
        }

        // Khớp chính xác từ có dấu của ngũ hành chọn màu thiệp (tránh nhầm "hóa học", "mốc"...)
        if (preg_match('/(?<![\p{L}\p{N}])(mộc|hỏa|thổ|thủy)(?![\p{L}\p{N}])/iu', $text) === 1) {
            return true;
        }

        $ascii = Str::ascii(mb_strtolower($text, 'UTF-8'));

        foreach (self::DOMAIN_ANCHOR_TERMS as $term) {
            if (str_starts_with($term, '/')) {
                if (str_contains($ascii, $term)) {
                    return true;
                }

                continue;
            }

            if (preg_match('/(?<![a-z0-9])'.preg_quote($term, '/').'(?![a-z0-9])/i', $ascii) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lớp phòng vệ hậu kiểm bằng code cứng (Deterministic Scope-Drift Detector):
     * Bắt buộc chặn nếu AI trượt vai sang tư vấn tâm lý/y tế hoặc trả lời chuyện ngoài lề khi không có bằng chứng DB.
     */
    public function detectOutOfScopeDrift(
        string $cleanMessage,
        string $aiReply,
        bool $hasDirectContext,
        string $intent
    ): ?string {
        if (in_array($intent, ['greeting', 'thanks'], true)) {
            return null;
        }

        // 1. Luôn chặn tuyệt đối nếu AI trượt vai sang tư vấn tâm lý cá nhân hoặc y tế/thai kỳ
        foreach (self::OUT_OF_SCOPE_REPLY_PATTERNS as $code => $pattern) {
            if (preg_match($pattern, $aiReply) === 1) {
                return 'scope_drift:'.$code;
            }
        }

        // 2. Khi cả câu hỏi hiện tại lẫn câu trả lời của AI đều không chứa bất kỳ neo phạm vi Mây Hẹn / cưới hỏi nào
        // (kể cả khi BM25 vô tình khớp 1 từ đơn trùng âm như "toán" trong "thanh toán", "gái" trong "nhà gái", "mẹ" trong "bố mẹ")
        if (! $this->hasDomainAnchor($cleanMessage) && ! $this->hasDomainAnchor($aiReply)) {
            return 'scope_drift:zero_domain_grounding';
        }

        // 3. Nếu không có bằng chứng trực tiếp trong DB nhưng câu trả lời vẫn rơi vào chủ đề phi-Mây-Hẹn
        if (! $hasDirectContext && trim($aiReply) === '') {
            return 'scope_drift:empty_ungrounded_reply';
        }

        return null;
    }

    /**
     * Kiểm tra tính hợp lệ của feedback (chống thao túng / spam đánh giá "Chưa đúng" hàng loạt).
     */
    public function isFeedbackGenuine(string $sessionHash, ?string $ipHash, string $feedback): bool
    {
        if ($feedback !== 'unhelpful') {
            return true;
        }

        $recentUnhelpfulCount = AiChatbotLog::query()
            ->where('feedback', 'unhelpful')
            ->where('updated_at', '>=', now()->subMinutes(10))
            ->where(function ($q) use ($sessionHash, $ipHash) {
                $q->where('session_hash', $sessionHash);
                if ($ipHash !== null && $ipHash !== '') {
                    $q->orWhere('ip_hash', $ipHash);
                }
            })
            ->count();

        // Quá 5 lượt đánh giá tiêu cực liên tiếp trong 10 phút -> nghi ngờ thao túng / phá hoại
        return $recentUnhelpfulCount < 5;
    }

    /**
     * Ẩn danh hóa PII (email, SĐT, STK, token) trước khi ghi vào log phân tích lâu dài.
     */
    public function maskPiiForStorage(string $text): string
    {
        return AiQualityAndCostObservatoryService::sanitizePiiForPreview($text);
    }

    public function isAllowedInternalPath(string $path): bool
    {
        foreach (self::ALLOWED_INTERNAL_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/') || str_starts_with($path, $prefix.'?')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $flags
     */
    protected function buildRefusalMessage(array $flags): string
    {
        foreach ($flags as $flag) {
            if (str_contains($flag, 'cross_tenant_idor')) {
                return 'Vì chính sách bảo mật và quyền riêng tư của Mây Hẹn, trợ lý không thể truy cập hay cung cấp thông tin thuộc tài khoản hoặc sự kiện của người khác.';
            }
            if (str_contains($flag, 'training_dump_exfil')) {
                return 'Mình chỉ hỗ trợ giải đáp ngắn gọn đúng nhu cầu của bạn về mẫu thiệp, bảng giá và cách sử dụng Mây Hẹn, không thể xuất toàn bộ tài liệu nội bộ. Bạn đang cần tư vấn tính năng nào để mình hỗ trợ ngay nhé?';
            }
            if (str_contains($flag, 'admin_impersonation') || str_contains($flag, 'memory_or_data_poisoning')) {
                return 'Mọi cập nhật về giá cước, chính sách và tri thức chính thức chỉ được thực hiện bởi Quản trị viên trong hệ thống quản trị Mây Hẹn. Mình rất sẵn lòng tư vấn mẫu thiệp hoặc tính năng hiện có cho bạn nhé!';
            }
        }

        return 'Trợ lý Mây Hẹn chỉ hỗ trợ tư vấn mẫu thiệp cưới, bảng giá và hướng dẫn sử dụng trên nền tảng Mây Hẹn. Yêu cầu vừa rồi không hợp lệ nên đã được hệ thống từ chối để bảo đảm an toàn.';
    }
}
