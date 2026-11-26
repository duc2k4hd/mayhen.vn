<?php

declare(strict_types=1);

namespace App\Services\Tarot;

/**
 * Rule Engine phân tích liên kết đa lá Tarot hoàn toàn tất định (độc lập với AI).
 */
class TarotInterpretationRuleEngine
{
    public function __construct(protected ?TarotDeckRegistry $registry = null)
    {
        $this->registry = $registry ?? new TarotDeckRegistry;
    }

    /**
     * Làm giàu danh sách lá đã chọn theo đúng Spread + Position + Chiều bài + Chủ đề.
     *
     * @param  list<array{position_index: int, slot_index?: int, card_id: string, is_reversed: bool}>  $selectedCards
     * @param  array<string, mixed>  $spread
     * @return array<string, mixed>
     */
    public function analyzeReading(array $selectedCards, array $spread, string $category = 'general'): array
    {
        $positions = $spread['positions'] ?? [];
        $enrichedCards = [];
        $majorCount = 0;
        $reversedCount = 0;
        $elements = ['Lửa' => 0, 'Nước' => 0, 'Khí' => 0, 'Đất' => 0];
        $suits = ['major' => 0, 'wands' => 0, 'cups' => 0, 'swords' => 0, 'pentacles' => 0];

        $domainKey = match ($category) {
            'love', 'single', 'dating', 'long_distance', 'breakup' => 'love',
            'marriage', 'pre_marriage' => 'marriage',
            'family' => 'family',
            'work' => 'work',
            'finance' => 'finance',
            'growth' => 'growth',
            default => 'general',
        };

        foreach ($selectedCards as $idx => $sel) {
            $card = $this->registry->findCardById((string) ($sel['card_id'] ?? ''));
            if (! $card) {
                continue;
            }
            $pos = $positions[$idx] ?? [
                'index' => $idx + 1,
                'code' => 'pos_'.($idx + 1),
                'label' => 'Vị trí '.($idx + 1),
                'meaning' => 'Góc nhìn cần quan sát trong trải bài.',
            ];
            $isRev = (bool) ($sel['is_reversed'] ?? false);

            if ($card['arcana'] === 'major') {
                $majorCount++;
            }
            if ($isRev) {
                $reversedCount++;
            }
            $el = (string) ($card['element'] ?? 'Khí');
            if (isset($elements[$el])) {
                $elements[$el]++;
            }
            $suit = (string) ($card['suit'] ?? 'major');
            if (isset($suits[$suit])) {
                $suits[$suit]++;
            }

            $contextualMeaning = $this->buildPositionContextualText($card, $pos, $isRev, $domainKey);

            $enrichedCards[] = [
                'position_index' => (int) ($pos['index'] ?? ($idx + 1)),
                'position_code' => (string) ($pos['code'] ?? ''),
                'position_label' => (string) ($pos['label'] ?? ''),
                'position_meaning' => (string) ($pos['meaning'] ?? ''),
                'slot_index' => isset($sel['slot_index']) ? (int) $sel['slot_index'] : null,
                'card_id' => $card['card_id'],
                'slug' => $card['slug'],
                'roman' => $card['roman'],
                'number' => $card['number'],
                'name_en' => $card['name_en'],
                'name_vi' => $card['name_vi'],
                'arcana' => $card['arcana'],
                'suit' => $card['suit'],
                'suit_label_vi' => $card['suit_label_vi'],
                'element' => $card['element'],
                'is_reversed' => $isRev,
                'orientation_label' => $isRev ? 'Ngược (Reversed)' : 'Xuôi (Upright)',
                'symbols' => $card['symbols'] ?? [],
                'keywords' => $isRev ? ($card['keywords_reversed'] ?? []) : ($card['keywords_upright'] ?? []),
                'core_meaning' => $isRev ? ($card['reversed_meaning'] ?? '') : ($card['upright_meaning'] ?? ''),
                'reversed_pattern' => $isRev ? ($card['reversed_pattern'] ?? '') : null,
                'traditional_waite' => $card['traditional_waite'] ?? '',
                'domain_meaning' => (string) ($card['domains'][$domainKey] ?? $card['domains']['general'] ?? ''),
                'position_contextual_meaning' => $contextualMeaning,
                'reflection_question' => $card['reflection_question'] ?? '',
                'common_misconception' => $card['common_misconception'] ?? '',
            ];
        }

        // Phân tích nguyên tố nổi trội & tổng hợp liên kết tất định
        arsort($elements);
        $topElement = (string) array_key_first($elements);
        $topElementCount = (int) ($elements[$topElement] ?? 0);

        $elementNotes = [
            'Lửa' => 'Trọng tâm nghiêng về hành động, ý chí, sự chủ động và nhiệt huyết triển khai.',
            'Nước' => 'Trọng tâm nghiêng về cảm xúc, trực giác, sự thấu cảm và kết nối tình cảm.',
            'Khí' => 'Trọng tâm nghiêng về tư duy, đối thoại minh bạch, phân tích và quyết định lý trí.',
            'Đất' => 'Trọng tâm nghiêng về thực tế, tài chính, công việc bền vững và nền tảng đời sống.',
        ];

        $synthesisParts = [];
        $total = count($enrichedCards);
        if ($total > 1) {
            if ($majorCount > 0) {
                $synthesisParts[] = "Trải bài có {$majorCount}/{$total} lá thuộc Bộ Ẩn Chính (Major Arcana), gợi mở những chủ đề cốt lõi cần sự chiêm nghiệm sâu sắc bên cạnh các diễn biến đời thường.";
            } else {
                $synthesisParts[] = 'Toàn bộ lá bài thuộc Bộ Ẩn Phụ (Minor Arcana), cho thấy vấn đề nằm rất sát các lựa chọn thực tế hằng ngày mà bạn hoàn toàn có thể chủ động điều chỉnh.';
            }
            if ($topElementCount > 0) {
                $synthesisParts[] = "Nguyên tố xuất hiện nổi bật là {$topElement} ({$topElementCount}/{$total} lá): ".($elementNotes[$topElement] ?? '');
            }
            if ($reversedCount > 0) {
                $synthesisParts[] = "Có {$reversedCount}/{$total} lá ở chiều Ngược — không phải điềm xấu cố định, mà chỉ ra vùng năng lượng đang cần giải tỏa, rà soát lại từ bên trong hoặc điều chỉnh nhịp độ.";
            }
        }

        $conditionalSignal = null;
        if (($spread['spread_id'] ?? '') === 'yes_no_one' && isset($enrichedCards[0])) {
            $conditionalSignal = $this->buildConditionalYesNoSignal($enrichedCards[0]);
        }

        return [
            'cards' => $enrichedCards,
            'stats' => [
                'total_cards' => $total,
                'major_count' => $majorCount,
                'minor_count' => $total - $majorCount,
                'reversed_count' => $reversedCount,
                'elements' => $elements,
                'suits' => $suits,
                'dominant_element' => $topElement,
            ],
            'rule_synthesis' => implode(' ', $synthesisParts),
            'conditional_signal' => $conditionalSignal,
        ];
    }

    /**
     * @param  array<string, mixed>  $card
     * @param  array<string, mixed>  $pos
     */
    protected function buildPositionContextualText(array $card, array $pos, bool $isRev, string $domainKey): string
    {
        $posLabel = (string) ($pos['label'] ?? 'Vị trí này');
        $base = $isRev ? (string) ($card['reversed_meaning'] ?? '') : (string) ($card['upright_meaning'] ?? '');
        $dom = (string) ($card['domains'][$domainKey] ?? $card['domains']['general'] ?? '');

        return "Tại vị trí \"{$posLabel}\", lá {$card['name_en']} ({$card['name_vi']} — ".($isRev ? 'Ngược' : 'Xuôi').") gợi mở: {$base} Trong góc nhìn thực tế: {$dom}";
    }

    /**
     * Tín hiệu Có/Không mang tính điều kiện (tuyệt đối không gán bảng cứng hay khẳng định tuyệt đối).
     *
     * @param  array<string, mixed>  $card
     * @return array{tone: string, label: string, explanation: string}
     */
    protected function buildConditionalYesNoSignal(array $card): array
    {
        $favorableIds = [
            'rws_major_01', 'rws_major_03', 'rws_major_06', 'rws_major_07', 'rws_major_08',
            'rws_major_14', 'rws_major_17', 'rws_major_19', 'rws_major_21',
            'rws_wands_01', 'rws_wands_03', 'rws_wands_04', 'rws_wands_06', 'rws_wands_08',
            'rws_cups_01', 'rws_cups_02', 'rws_cups_03', 'rws_cups_06', 'rws_cups_09', 'rws_cups_10',
            'rws_swords_01', 'rws_pentacles_01', 'rws_pentacles_03', 'rws_pentacles_06', 'rws_pentacles_09', 'rws_pentacles_10',
        ];
        $cautionIds = [
            'rws_major_15', 'rws_major_16', 'rws_major_18',
            'rws_wands_05', 'rws_wands_10', 'rws_cups_05', 'rws_swords_03', 'rws_swords_05', 'rws_swords_07', 'rws_swords_08', 'rws_swords_09', 'rws_swords_10', 'rws_pentacles_05',
        ];

        $isRev = (bool) $card['is_reversed'];
        $id = (string) $card['card_id'];

        if (! $isRev && in_array($id, $favorableIds, true)) {
            return [
                'tone' => 'favorable',
                'label' => 'Có xu hướng thuận lợi nếu đáp ứng điều kiện thực tế',
                'explanation' => "Lá {$card['name_en']} (Xuôi) nghiêng về tín hiệu tích cực, miễn là bạn chủ động chuẩn bị kỹ và giữ sự minh bạch trong hành động.",
            ];
        }
        if (in_array($id, $cautionIds, true)) {
            return [
                'tone' => 'caution',
                'label' => 'Nên thận trọng và kiểm tra kỹ thêm trước khi chốt',
                'explanation' => "Lá {$card['name_en']} nhắc có yếu tố cần tháo gỡ hoặc dữ kiện chưa thật sáng tỏ — không nên quyết định vội vàng lúc này.",
            ];
        }

        return [
            'tone' => 'consider',
            'label' => 'Cần xem xét thêm bối cảnh & điều chỉnh cách tiếp cận',
            'explanation' => "Lá {$card['name_en']} cho thấy kết quả phụ thuộc mạnh vào cách bạn chuẩn bị và đối thoại thực tế, chưa nên đóng khung cứng nhắc Có hoặc Không.",
        ];
    }
}
