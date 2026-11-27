<?php

declare(strict_types=1);

namespace App\Services\Personality;

use App\Models\PersonalitySession;

/**
 * Relationship Comparison Engine (Ma trận 16×16 — 136 cặp không thứ tự / 256 tổ hợp).
 *
 * - TUYỆT ĐỐI KHÔNG tạo tỷ lệ hợp nhau giả ("hợp nhau 98%", "tỷ lệ kết hôn 95%", "khắc nhau").
 * - Chỉ mô tả dữ liệu thực tế: số chiều giống/khác, độ chênh lệch điểm nội bộ, giả thuyết giao tiếp và câu hỏi thảo luận cặp đôi.
 */
class PersonalityCoupleEngine
{
    public function __construct(
        protected PersonalityTypeRegistry $typeRegistry
    ) {}

    /**
     * So sánh 2 phiên trắc nghiệm độc lập của hai người.
     *
     * @param  array<string, string>  $answersA
     * @param  array<string, string>  $answersB
     * @return array<string, mixed>
     */
    public function compareSessions(
        PersonalitySession $sessionA,
        PersonalitySession $sessionB,
        array $answersA = [],
        array $answersB = []
    ): array {
        $typeA = $sessionA->effectiveType() ?? 'UNDECIDED';
        $typeB = $sessionB->effectiveType() ?? 'UNDECIDED';
        $dimsA = (array) ($sessionA->scores_json['dimensions'] ?? []);
        $dimsB = (array) ($sessionB->scores_json['dimensions'] ?? []);
        $dimsMeta = $this->typeRegistry->dimensions16();

        $dimensionComparisons = [];
        $sharedDimensions = 0;
        $differentDimensions = 0;

        foreach (['EI', 'SN', 'TF', 'JP'] as $dim) {
            $meta = $dimsMeta[$dim];
            $dA = (array) ($dimsA[$dim] ?? []);
            $dB = (array) ($dimsB[$dim] ?? []);
            $tendA = (string) ($dA['tendency'] ?? 'undecided');
            $tendB = (string) ($dB['tendency'] ?? 'undecided');
            $idxA = isset($dA['positive_index']) ? (float) $dA['positive_index'] : null;
            $idxB = isset($dB['positive_index']) ? (float) $dB['positive_index'] : null;
            $diff = ($idxA !== null && $idxB !== null) ? round(abs($idxA - $idxB), 1) : null;

            $isSame = $tendA !== 'undecided' && $tendA !== 'insufficient_data' && $tendA === $tendB;
            if ($isSame) {
                $sharedDimensions++;
            } elseif ($tendA !== 'undecided' && $tendB !== 'undecided') {
                $differentDimensions++;
            }

            $dimensionComparisons[$dim] = [
                'dimension' => $dim,
                'name' => $meta['name'],
                'tendency_a' => $tendA,
                'tendency_b' => $tendB,
                'index_a' => $idxA,
                'index_b' => $idxB,
                'index_gap' => $diff,
                'is_same_side' => $isSame,
                'summary' => $this->describeDimensionPair($dim, $tendA, $tendB, $diff),
                'dialogue_prompt' => $this->dialoguePromptForDimension($dim, $isSame),
            ];
        }

        // So sánh trực tiếp 6 câu hỏi thấu hiểu cặp đôi (từ câu trả lời thực tế, không suy đoán từ 4 chữ cái)
        $reflectionComparisons = [];
        foreach ($this->typeRegistry->coupleReflectionQuestions() as $q) {
            $qid = $q['id'];
            $ansA = trim((string) ($answersA[$qid] ?? ''));
            $ansB = trim((string) ($answersB[$qid] ?? ''));
            if ($ansA !== '' || $ansB !== '') {
                $reflectionComparisons[] = [
                    'id' => $qid,
                    'question' => $q['label'],
                    'answer_a' => $ansA !== '' ? $ansA : 'Chưa chia sẻ',
                    'answer_b' => $ansB !== '' ? $ansB : 'Chưa chia sẻ',
                    'aligned' => $ansA !== '' && $ansA === $ansB,
                ];
            }
        }

        return [
            'type_a' => $typeA,
            'type_b' => $typeB,
            'shared_dimensions_count' => $sharedDimensions,
            'different_dimensions_count' => $differentDimensions,
            'factual_headline' => "Hai bạn có cùng xu hướng ở {$sharedDimensions}/4 chiều phân loại và bổ trợ khác biệt ở {$differentDimensions}/4 chiều.",
            'ethical_disclaimer' => 'Mọi nhóm tính cách đều có thể xây dựng mối quan hệ hạnh phúc. Bảng đối chiếu dưới đây chỉ mô tả sự tương đồng và khác biệt về thói quen giao tiếp để hai bạn dễ thấu hiểu nhau hơn — hoàn toàn không phải tỷ lệ hợp nhau hay dự đoán hôn nhân.',
            'dimensions' => $dimensionComparisons,
            'reflections' => $reflectionComparisons,
            'twelve_love_topics' => $this->twelveLoveTopics($dimensionComparisons),
        ];
    }

    protected function describeDimensionPair(string $dim, string $tendA, string $tendB, ?float $diff): string
    {
        $gapText = $diff !== null ? " (chênh lệch {$diff} điểm trên thang nội bộ)" : '';

        return match ($dim) {
            'EI' => $tendA === $tendB
                ? "Cả hai cùng nghiêng về {$tendA}{$gapText}: dễ đồng điệu nhịp giao tiếp xã hội và nhu cầu nạp năng lượng."
                : "Một người nghiêng {$tendA}, người kia nghiêng {$tendB}{$gapText}: nên thống nhất rõ thời gian gặp gỡ bạn bè và khoảng lặng riêng mỗi tuần.",
            'SN' => $tendA === $tendB
                ? "Cả hai cùng nghiêng về {$tendA}{$gapText}: dễ hiểu chung cách kể chuyện và tiếp cận thông tin."
                : "Một người chú trọng dữ kiện cụ thể (S), người kia thích bức tranh tổng thể (N){$gapText}: rất tốt khi bổ trợ nhau giữa ý tưởng lớn và kế hoạch chi tiết.",
            'TF' => $tendA === $tendB
                ? "Cả hai cùng nghiêng về {$tendA}{$gapText}: tương đồng cách cân nhắc quyết định quan trọng."
                : "Một người ưu tiên tiêu chí logic (T), người kia ưu tiên cảm nhận và giá trị (F){$gapText}: khi tranh luận nên lắng nghe cảm xúc trước rồi mới thống nhất phương án.",
            default => $tendA === $tendB
                ? "Cả hai cùng nghiêng về {$tendA}{$gapText}: dễ hòa nhịp trong việc sắp xếp lịch trình và chuẩn bị sự kiện."
                : "Một người thích chốt kế hoạch sớm (J), người kia thích giữ sự linh hoạt (P){$gapText}: nên chốt trước các mốc lớn (ngày cưới, ngân sách) và để thoáng các chi tiết nhỏ.",
        };
    }

    protected function dialoguePromptForDimension(string $dim, bool $isSame): string
    {
        return match ($dim) {
            'EI' => $isSame
                ? 'Khi cả hai cùng muốn nghỉ ngơi hoặc cùng muốn đi chơi, chúng ta sẽ giữ cân bằng với gia đình hai bên thế nào?'
                : 'Dấu hiệu nào cho thấy một trong hai người đang cần 30 phút yên tĩnh để nạp lại năng lượng mà người kia không hiểu nhầm là giận dỗi?',
            'SN' => $isSame
                ? 'Khi lên kế hoạch lớn (du lịch, cưới hỏi), làm sao để không bỏ sót góc nhìn còn lại (ý tưởng mới hoặc chi tiết thực tế)?'
                : 'Khi thảo luận kế hoạch, người thích cụ thể cần thông tin gì trước và người thích bức tranh lớn cần được nghe điều gì trước?',
            'TF' => $isSame
                ? 'Khi xảy ra bất đồng, chúng ta cần nguyên tắc gì để vừa công bằng vừa không làm tổn thương nhau?'
                : 'Trong một buổi trò chuyện căng thẳng, làm sao để biết đối phương đang cần được đồng cảm lắng nghe hay đang cần cùng tìm giải pháp?',
            default => $isSame
                ? 'Chúng ta sẽ phân chia đầu việc chuẩn bị cưới hỏi hoặc sinh hoạt hằng ngày thế nào để cả hai cùng nhẹ nhàng?'
                : 'Những đầu việc nào cần chốt lịch cố định từ sớm, và những phần nào cả hai đồng ý giữ linh hoạt theo cảm hứng?',
        };
    }

    /**
     * @param  array<string, array<string, mixed>>  $dimensionComparisons
     * @return list<array{index: int, title: string, guidance: string}>
     */
    protected function twelveLoveTopics(array $dimensionComparisons): array
    {
        $eiSame = ! empty($dimensionComparisons['EI']['is_same_side']);
        $tfSame = ! empty($dimensionComparisons['TF']['is_same_side']);
        $jpSame = ! empty($dimensionComparisons['JP']['is_same_side']);

        return [
            ['index' => 1, 'title' => 'Cách thể hiện tình cảm', 'guidance' => $tfSame ? 'Hai bạn có nền tảng biểu đạt tương đồng, nhưng vẫn nên hỏi rõ hành động cụ thể khiến đối phương thấy được trân trọng.' : 'Một người thường thể hiện qua hành động/giải pháp, người kia qua sự lắng nghe và lời ấm áp — hãy học cách nhận diện cả hai ngôn ngữ.'],
            ['index' => 2, 'title' => 'Nhu cầu được lắng nghe', 'guidance' => 'Trước khi góp ý, thử hỏi một câu đơn giản: "Hôm nay em/anh muốn mình chỉ lắng nghe hay cùng bàn cách giải quyết?"'],
            ['index' => 3, 'title' => 'Nhu cầu không gian riêng', 'guidance' => $eiSame ? 'Nhịp sinh hoạt tương đồng giúp hai bạn dễ thống nhất giờ nghỉ ngơi.' : 'Tôn trọng khoảng lặng cá nhân là cách bảo vệ năng lượng cho mối quan hệ, không phải sự xa cách.'],
            ['index' => 4, 'title' => 'Thói quen giao tiếp', 'guidance' => 'Thống nhất nguyên tắc không suy đoán ý ngầm: khi điều gì chưa rõ, hãy hỏi lại bằng giọng bình tĩnh.'],
            ['index' => 5, 'title' => 'Cách đưa ra quyết định', 'guidance' => 'Với việc lớn (tài chính, tổ chức đám cưới), hãy đặt song song hai cột: Tiêu chí khách quan và Cảm nhận của hai người.'],
            ['index' => 6, 'title' => 'Cách giải quyết bất đồng', 'guidance' => 'Cho phép tạm dừng 20 phút khi cảm xúc lên cao, kèm lời hẹn rõ giờ quay lại nói chuyện tiếp.'],
            ['index' => 7, 'title' => 'Lối sống hằng ngày', 'guidance' => 'Trao đổi trực tiếp về giờ giấc nghỉ ngơi, thói quen cuối tuần thay vì suy luận từ 4 chữ cái.'],
            ['index' => 8, 'title' => 'Cách lập kế hoạch', 'guidance' => $jpSame ? 'Nhịp lên kế hoạch khá đồng đều; chỉ cần thêm một danh sách kiểm tra ngắn gọn.' : 'Chốt sớm 3 mốc không thể đổi, để linh hoạt các hạng mục còn lại để cả hai cùng thấy thoải mái.'],
            ['index' => 9, 'title' => 'Sự độc lập & Ranh giới', 'guidance' => 'Duy trì sở thích cá nhân và nhóm bạn riêng lành mạnh giúp tình yêu luôn tươi mới.'],
            ['index' => 10, 'title' => 'Những giá trị chung', 'guidance' => 'Cùng viết ra 3 giá trị không thỏa hiệp của gia đình nhỏ (ví dụ: trung thực, tôn trọng, sẻ chia).'],
            ['index' => 11, 'title' => 'Mong muốn trong hôn nhân', 'guidance' => 'Chủ động trao đổi về vai trò gia đình hai bên, kế hoạch tổ ấm và cách chuẩn bị lễ cưới trên Wedding Workspace.'],
            ['index' => 12, 'title' => 'Mục tiêu tương lai', 'guidance' => 'Đặt lịch hẹn 6 tháng/lần để cùng nhìn lại chặng đường đã qua và điều chỉnh kế hoạch năm tới.'],
        ];
    }
}
