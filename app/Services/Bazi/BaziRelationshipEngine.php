<?php

declare(strict_types=1);

namespace App\Services\Bazi;

/**
 * Engine Bát Tự Tình Duyên & Hôn Nhân — Mây Hẹn – Tứ Trụ Kết Duyên.
 *
 * - Lập và giữ nguyên 2 lá số độc lập (không trộn 16 chữ thành 1 lá số).
 * - Tổ chức 12 chủ đề tình duyên ứng dụng (cấu trúc Mây Hẹn, không gọi là 12 phép cổ điển).
 * - TUYỆT ĐỐI không sinh tỷ lệ % hợp nhau giả, không kết luận ngoại tình / ly hôn / hủy cưới.
 */
class BaziRelationshipEngine
{
    public function __construct(protected BaziCalculationEngine $engine) {}

    /**
     * @param  array<string, mixed>  $personAInput
     * @param  array<string, mixed>  $personBInput
     * @return array<string, mixed>
     */
    public function analyze(array $personAInput, array $personBInput): array
    {
        $chartA = $this->engine->calculate($personAInput);
        $chartB = $this->engine->calculate($personBInput);

        $dmA = $chartA['day_master'];
        $dmB = $chartB['day_master'];
        $spBranchA = $chartA['pillars']['day']['branch'];
        $spBranchB = $chartB['pillars']['day']['branch'];

        // 1. Quan hệ 2 chiều giữa 2 Nhật Chủ (có hướng)
        $aSeesB = BaziRegistry::getTenGod($dmA['index'], $dmB['index']);
        $bSeesA = BaziRegistry::getTenGod($dmB['index'], $dmA['index']);

        // 2. Quan hệ đối xứng Thiên Can Nhật Chủ & Cung Phối Ngẫu (Nhật Chi)
        $detectedRelations = [];
        foreach (BaziRegistry::STEM_COMBINATIONS as $sc) {
            if (
                ($dmA['index'] === $sc['stems'][0] && $dmB['index'] === $sc['stems'][1]) ||
                ($dmA['index'] === $sc['stems'][1] && $dmB['index'] === $sc['stems'][0])
            ) {
                $detectedRelations[] = [
                    'type' => 'stem_combine',
                    'label' => "Hai Nhật Chủ {$sc['label']}",
                    'nature' => 'Kết nối tự nhiên, dễ tìm thấy sức hút và sự bổ trợ nhau trong nhịp sống.',
                ];
            }
        }

        foreach (BaziRegistry::BRANCH_SIX_COMBINATIONS as $bc) {
            if (
                ($spBranchA['index'] === $bc['branches'][0] && $spBranchB['index'] === $bc['branches'][1]) ||
                ($spBranchA['index'] === $bc['branches'][1] && $spBranchB['index'] === $bc['branches'][0])
            ) {
                $detectedRelations[] = [
                    'type' => 'spouse_combine',
                    'label' => "Cung Phối Ngẫu (Nhật Chi) {$bc['label']}",
                    'nature' => 'Dễ đồng điệu trong sinh hoạt gia đình và cách chia sẻ không gian chung.',
                ];
            }
        }

        foreach (BaziRegistry::BRANCH_CLASHES as $cl) {
            if (
                ($spBranchA['index'] === $cl['branches'][0] && $spBranchB['index'] === $cl['branches'][1]) ||
                ($spBranchA['index'] === $cl['branches'][1] && $spBranchB['index'] === $cl['branches'][0])
            ) {
                $detectedRelations[] = [
                    'type' => 'spouse_clash',
                    'label' => "Cung Phối Ngẫu (Nhật Chi) {$cl['label']}",
                    'nature' => 'Khác biệt rõ về nhịp sinh hoạt hoặc phản xạ khi căng thẳng — nên thống nhất nguyên tắc giao tiếp sớm, không phải dấu hiệu chia tay.',
                ];
            }
        }

        if ($detectedRelations === []) {
            $detectedRelations[] = [
                'type' => 'steady_flow',
                'label' => "Tương tác {$dmA['name']} {$dmA['element']} ↔ {$dmB['name']} {$dmB['element']}",
                'nature' => 'Không có xung khắc trực diện ở Nhật Chủ và Cung Phối Ngẫu; mối quan hệ phát triển bền bỉ nhờ sự chủ động nuôi dưỡng từ cả hai phía.',
            ];
        }

        // 3. Bổ trợ Ngũ Hành giữa hai lá số
        $complementary = [];
        foreach ($chartA['elements']['missing_elements'] as $missingEl) {
            if (($chartB['elements']['eight_chars']['counts'][$missingEl] ?? 0) > 0) {
                $complementary[] = "Lá số người B có hành {$missingEl}, tạo điểm bổ sung tự nhiên cho phần ít lộ của người A.";
            }
        }
        foreach ($chartB['elements']['missing_elements'] as $missingEl) {
            if (($chartA['elements']['eight_chars']['counts'][$missingEl] ?? 0) > 0) {
                $complementary[] = "Lá số người A có hành {$missingEl}, tạo điểm bổ sung tự nhiên cho phần ít lộ của người B.";
            }
        }

        // 4. 12 Chủ Đề Phân Tích Tình Duyên (cấu trúc ứng dụng Mây Hẹn)
        $themes = [
            ['id' => 1, 'title' => 'Dấu ấn cá nhân', 'insight' => "Người A mang khí chất {$dmA['name']} {$dmA['element']} ({$dmA['symbol']}); người B mang khí chất {$dmB['name']} {$dmB['element']} ({$dmB['symbol']})."],
            ['id' => 2, 'title' => 'Nhu cầu tình cảm', 'insight' => "Trong góc nhìn Thập Thần, người B gợi năng lượng {$aSeesB['name']} với A, còn người A gợi năng lượng {$bSeesA['name']} với B."],
            ['id' => 3, 'title' => 'Cách thể hiện sự quan tâm', 'insight' => "Cung Phối Ngẫu của A tại {$spBranchA['name']} ({$spBranchA['element']}) và của B tại {$spBranchB['name']} ({$spBranchB['element']}) phản ánh cách mỗi người vun vén mái ấm."],
            ['id' => 4, 'title' => 'Giá trị chung', 'insight' => 'Sự bền vững đến từ việc tôn trọng điểm giống và xem điểm khác như mảnh ghép học hỏi thay vì ép nhau thay đổi.'],
            ['id' => 5, 'title' => 'Giao tiếp', 'insight' => 'Nên chia sẻ trực tiếp nhu cầu và cảm xúc thay vì kỳ vọng đối phương tự đoán, đặc biệt khi hai người thuộc hai nhóm Ngũ Hành khác nhịp.'],
            ['id' => 6, 'title' => 'Sự độc lập', 'insight' => 'Duy trì không gian phát triển sự nghiệp và sở thích cá nhân giúp cả hai giữ được sức hút lâu dài.'],
            ['id' => 7, 'title' => 'Sự gắn kết', 'insight' => 'Các thói quen nhỏ hằng tuần (bữa cơm chung, trò chuyện cuối ngày) tạo nền tảng ổn định hơn mọi yếu tố bên ngoài.'],
            ['id' => 8, 'title' => 'Cách xử lý bất đồng', 'insight' => 'Khi tranh luận, thống nhất quy tắc tạm dừng 15 phút để lắng dịu trước khi giải quyết vấn đề chung.'],
            ['id' => 9, 'title' => 'Cam kết lâu dài', 'insight' => 'Thống nhất rõ kế hoạch tài chính, nơi ở và nhịp sống 3–5 năm tới giúp hai người cùng nhìn về một hướng.'],
            ['id' => 10, 'title' => 'Quan hệ gia đình hai bên', 'insight' => 'Trụ Năm và Trụ Tháng phản ánh nền tảng gia đình; sự tôn trọng và ranh giới lành mạnh giúp hai bên nội ngoại hòa thuận.'],
            ['id' => 11, 'title' => 'Chuẩn bị trước hôn nhân', 'insight' => 'Nên cùng rà soát lịch trình cưới hỏi, ngân sách và phân công việc rõ ràng để ngày vui nhẹ nhàng trọn vẹn.'],
            ['id' => 12, 'title' => 'Những điều có thể cùng vun đắp', 'insight' => 'Ghi nhận điều biết ơn về nhau mỗi tháng và cùng tạo những cột mốc kỷ niệm đáng nhớ.'],
        ];

        $dialogueQuestions = [
            'Khi gặp áp lực công việc, bạn muốn mình lắng nghe im lặng hay cùng đưa giải pháp?',
            'Trong kế hoạch tài chính chung của gia đình, đâu là ranh giới an toàn khiến cả hai yên tâm nhất?',
            'Những dịp lễ Tết và giỗ chạp hai bên gia đình, chúng ta nên sắp xếp thế nào để cả hai đều thoải mái?',
            'Điều gì ở cách thể hiện tình cảm của đối phương khiến bạn cảm thấy được trân trọng nhất?',
        ];

        $missingNotes = [];
        if (! $chartA['birth']['has_hour']) {
            $missingNotes[] = 'Người A chưa có giờ sinh: phần đánh giá chưa bao gồm Trụ Giờ của người A.';
        }
        if (! $chartB['birth']['has_hour']) {
            $missingNotes[] = 'Người B chưa có giờ sinh: phần đánh giá chưa bao gồm Trụ Giờ của người B.';
        }

        return [
            'person_a' => $chartA,
            'person_b' => $chartB,
            'directed_ten_gods' => [
                'a_sees_b' => $aSeesB,
                'b_sees_a' => $bSeesA,
            ],
            'detected_relations' => $detectedRelations,
            'complementary_elements' => $complementary,
            'themes' => $themes,
            'dialogue_questions' => $dialogueQuestions,
            'missing_data_notes' => $missingNotes,
            'disclaimer' => 'Cấu trúc 12 chủ đề tình duyên do Mây Hẹn biên soạn nhằm hỗ trợ hai người đối thoại thấu hiểu nhau, không chấm điểm % hợp nhau và không thay thế quyết định hôn nhân.',
        ];
    }
}
