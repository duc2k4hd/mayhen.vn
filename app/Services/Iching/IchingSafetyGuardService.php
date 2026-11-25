<?php

declare(strict_types=1);

namespace App\Services\Iching;

use Illuminate\Support\Facades\File;

/**
 * Lớp bảo vệ an toàn, nhân văn & hậu kiểm AI cho Mây Hẹn Dịch Tâm.
 *
 * - Ngăn gieo quẻ cho tình huống khủng hoảng tâm lý, tự hại, bạo lực hoặc cấp cứu y tế -> điều hướng đường dây hỗ trợ.
 * - Gắn cờ và định hướng lại các chủ đề không thuộc phạm vi dự đoán (chẩn đoán bệnh, tuổi thọ, giới tính thai nhi, cáo buộc ngoại tình, cờ bạc/lô đề).
 * - Hậu kiểm đầu ra AI để chặn mê tín, hù dọa cực đoan hoặc bịa sai tên quẻ.
 */
class IchingSafetyGuardService
{
    /** @var list<string> */
    protected const CRISIS_PATTERNS = [
        '/tự\s*tử|tự\s*sát|muốn\s*chết|không\s*muốn\s*sống|kết\s*thúc\s*cuộc\s*đời|tự\s*làm\s*hại|rạch\s*tay|uống\s*thuốc\s*tự/iu',
        '/bị\s*đánh\s*đập|bạo\s*hành|đe\s*dọa\s*tính\s*mạng|giết\s*người|trả\s*thù\s*bằng\s*bạo\s*lực/iu',
        '/đang\s*đau\s*tim|khó\s*thở\s*nặng|chảy\s*máu\s*nhiều|cấp\s*cứu/iu',
    ];

    /** @var array<string, array{pattern: string, notice: string, suggested: string}> */
    protected const RESTRICTED_CATEGORIES = [
        'medical_lifespan' => [
            'pattern' => '/ung\s*thư|chẩn\s*đoán\s*bệnh|bệnh\s*gì|sống\s*được\s*bao\s*lâu|mấy\s*tuổi\s*chết|bao\s*giờ\s*chết|bỏ\s*thuốc|chữa\s*bệnh\s*bằng|sinh\s*con\s*trai\s*hay\s*gái|thai\s*nhi\s*trai\s*hay\s*gái|nên\s*phá\s*thai/iu',
            'notice' => 'Kinh Dịch không thay thế bác sĩ, chẩn đoán y khoa hay dự đoán tuổi thọ và giới tính thai nhi. Bạn hãy trao đổi trực tiếp với cơ sở y tế chuyên môn; lá quẻ chỉ giúp bạn giữ bình tĩnh và sắp xếp sự chăm sóc tinh thần.',
            'suggested' => 'Tôi nên giữ tâm thế và cách chăm sóc sức khỏe, sắp xếp người thân hỗ trợ như thế nào trong giai đoạn này?',
        ],
        'relationship_accusation' => [
            'pattern' => '/có\s*ngoại\s*tình\s*không|có\s*bồ\s*nhí\s*không|có\s*lừa\s*dối\s*tôi\s*không|đang\s*cắm\s*sừng|chắc\s*chắn\s*ly\s*hôn|có\s*bỏ\s*nhau\s*không|có\s*hủy\s*cưới\s*không/iu',
            'notice' => 'Lá quẻ không thể đọc suy nghĩ người khác hay kết luận chuyện ngoại tình, chia tay. Mây Hẹn sẽ luận theo hướng giúp bạn nhìn lại sự giao tiếp, xây dựng sự rõ ràng và bảo vệ bình an cho chính mình.',
            'suggested' => 'Hai chúng tôi cần trò chuyện thẳng thắn và lắng nghe nhau ở điểm nào để củng cố sự tin tưởng?',
        ],
        'gambling_speculation' => [
            'pattern' => '/lô\s*đề|số\s*đề|đánh\s*đề|xổ\s*số|vietlott|cá\s*độ|đánh\s*bạc|tài\s*xỉu|mã\s*cổ\s*phiếu\s*nào\s*chắc\s*thắng|coin\s*x10|đầu\s*tư\s*chắc\s*lãi/iu',
            'notice' => 'Kinh Dịch không dùng để chọn con số may rủi, cờ bạc hay cam kết lợi nhuận tài chính. Phần luận giải sẽ tập trung vào quản trị rủi ro và kỷ luật tài chính an toàn.',
            'suggested' => 'Tôi cần lưu ý rủi ro và kỷ luật quản lý tài chính nào trước khi đưa ra quyết định tiền bạc sắp tới?',
        ],
    ];

    /** @var list<string> */
    protected const FORBIDDEN_AI_PHRASES = [
        'giải hạn',
        'cúng sao',
        'giải sao',
        'tai họa chắc chắn',
        'chết chóc',
        'khắc mệnh không được cưới',
        'bỏ cưới ngay',
        'chắc chắn ngoại tình',
        'chắc chắn ly hôn',
        'chắc chắn phá sản',
        'số may mắn đánh đề',
    ];

    /**
     * Đánh giá an toàn câu hỏi trước khi lập quẻ.
     *
     * @return array{
     *     status: 'safe'|'restricted_topic'|'crisis_block',
     *     allow_cast: bool,
     *     category: ?string,
     *     title: ?string,
     *     notice: ?string,
     *     suggested_question: ?string,
     *     resources: list<array{label: string, detail: string}>
     * }
     */
    public function assessQuestion(string $question): array
    {
        $clean = trim($question);

        foreach (self::CRISIS_PATTERNS as $pattern) {
            if (preg_match($pattern, $clean) === 1) {
                return [
                    'status' => 'crisis_block',
                    'allow_cast' => false,
                    'category' => 'crisis',
                    'title' => 'Bạn không cần đối diện chuyện này một mình',
                    'notice' => 'Trong lúc tâm trạng quá nặng nề hoặc có nguy cơ mất an toàn, việc gieo quẻ không phải giải pháp phù hợp. Hãy liên hệ ngay người thân tin cậy, bác sĩ hoặc tổng đài hỗ trợ khẩn cấp tại Việt Nam.',
                    'suggested_question' => null,
                    'resources' => [
                        ['label' => 'Cấp cứu y tế khẩn cấp', 'detail' => 'Gọi 115 (24/7 trên toàn quốc)'],
                        ['label' => 'Tổng đài Quốc gia Bảo vệ Trẻ em & Hỗ trợ tâm lý gia đình', 'detail' => 'Gọi 111 (Miễn phí, 24/7)'],
                        ['label' => 'Hỗ trợ an ninh khẩn cấp', 'detail' => 'Gọi 113 khi có nguy cơ bạo lực'],
                    ],
                ];
            }
        }

        foreach (self::RESTRICTED_CATEGORIES as $category => $meta) {
            if (preg_match($meta['pattern'], $clean) === 1) {
                return [
                    'status' => 'restricted_topic',
                    'allow_cast' => true,
                    'category' => $category,
                    'title' => 'Lưu ý phạm vi chiêm nghiệm',
                    'notice' => $meta['notice'],
                    'suggested_question' => $meta['suggested'],
                    'resources' => [],
                ];
            }
        }

        return [
            'status' => 'safe',
            'allow_cast' => true,
            'category' => null,
            'title' => null,
            'notice' => null,
            'suggested_question' => null,
            'resources' => [],
        ];
    }

    /**
     * Gợi ý viết lại câu hỏi mở (chủ động, không ép buộc) từ topics_taxonomy.json.
     *
     * @return array{has_suggestion: bool, suggested: ?string, reason: ?string}
     */
    public function suggestQuestionRefinement(string $question): array
    {
        $clean = trim($question);
        if ($clean === '') {
            return ['has_suggestion' => false, 'suggested' => null, 'reason' => null];
        }

        $safety = $this->assessQuestion($clean);
        if ($safety['status'] === 'restricted_topic' && ! empty($safety['suggested_question'])) {
            return [
                'has_suggestion' => true,
                'suggested' => $safety['suggested_question'],
                'reason' => $safety['notice'],
            ];
        }

        $path = database_path('data/iching/topics_taxonomy.json');
        if (! File::exists($path)) {
            return ['has_suggestion' => false, 'suggested' => null, 'reason' => null];
        }

        $taxonomy = json_decode((string) File::get($path), true);
        foreach (($taxonomy['question_refinements'] ?? []) as $rule) {
            $pattern = (string) ($rule['pattern'] ?? '');
            if ($pattern !== '' && @preg_match('/'.$pattern.'/iu', $clean) === 1) {
                return [
                    'has_suggestion' => true,
                    'suggested' => (string) ($rule['suggested'] ?? ''),
                    'reason' => 'Câu hỏi mở theo hướng "nên chuẩn bị hoặc điều chỉnh điều gì" giúp bài luận Kinh Dịch sâu sắc và hữu ích hơn câu hỏi có/không.',
                ];
            }
        }

        return ['has_suggestion' => false, 'suggested' => null, 'reason' => null];
    }

    /**
     * Hậu kiểm nội dung AI trước khi trả về người dùng.
     *
     * @param  array<string, mixed>  $output
     * @param  array<string, mixed>  $analysis
     * @return array{valid: bool, issues: list<string>}
     */
    public function validateAiOutput(array $output, array $analysis): array
    {
        $issues = [];

        $combinedParts = [];
        foreach (['headline', 'direct_answer', 'plain_summary', 'layer_1_image', 'layer_2_moving_lines', 'layer_3_evolution', 'layer_4_timing_actions', 'caution_note', 'reflection_question', 'closing_note'] as $field) {
            if (! empty($output[$field]) && is_string($output[$field])) {
                $combinedParts[] = $output[$field];
            }
        }
        foreach (($output['caution_notes'] ?? []) as $note) {
            if (is_string($note)) {
                $combinedParts[] = $note;
            }
        }
        $haystack = mb_strtolower(implode(' ', $combinedParts), 'UTF-8');

        // 1. Chặn cụm từ mê tín, hù dọa, chia rẽ hôn nhân
        foreach (self::FORBIDDEN_AI_PHRASES as $phrase) {
            if (str_contains($haystack, mb_strtolower($phrase, 'UTF-8'))) {
                $issues[] = "Chứa nội dung không phù hợp chuẩn nhân văn Mây Hẹn: {$phrase}";
            }
        }

        // 2. Kiểm tra tính nhất quán cơ bản: không nhắc nhầm "Quẻ Chủ" bằng tên quẻ khác hoàn toàn
        $primaryName = mb_strtolower((string) ($analysis['primary']['name'] ?? ''), 'UTF-8');
        if ($primaryName !== '' && ! str_contains($haystack, $primaryName)) {
            $shortName = trim((string) preg_replace('/^.*\s+(\S+)$/u', '$1', $primaryName));
            if ($shortName === '' || ! str_contains($haystack, $shortName)) {
                $issues[] = 'Bài luận chưa quy chiếu đúng tên Quẻ Chủ '.$analysis['primary']['name'];
            }
        }

        // 3. Nếu không phải Mai Hoa mà AI tự gán "điểm cát khí /100" hoặc bịa "Thể – Dụng"
        if (($analysis['school'] ?? 'chu_dich') !== 'mai_hoa') {
            if (preg_match('/\b\d{1,3}\s*\/\s*100\b/u', $haystack) === 1) {
                $issues[] = 'AI gán thang điểm may rủi /100 trái chuẩn Mây Hẹn.';
            }
        }

        return [
            'valid' => $issues === [],
            'issues' => $issues,
        ];
    }
}
