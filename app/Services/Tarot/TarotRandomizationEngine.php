<?php

declare(strict_types=1);

namespace App\Services\Tarot;

use InvalidArgumentException;

/**
 * Động cơ Xáo Bài & Rút Bài Tarot Công Bằng (Fisher–Yates CSPRNG via PHP random_int).
 *
 * Nguyên tắc bất biến:
 * - Luôn xáo đủ 78 Card ID duy nhất từ Deck Registry.
 * - Duyệt ngược từ n-1 về 1, chọn chỉ số j phân phối đều trong [0, i] bằng random_int() (CSPRNG).
 * - Rút không hoàn lại 100%: mỗi lá chỉ xuất hiện tối đa 1 lần trong một phiên.
 * - Chiều bài: `upright_only` (100% xuôi) hoặc `upright_reversed` (50/50 độc lập bằng random_int(0, 1)).
 * - Hỗ trợ Commitment Hash SHA-256 (hash của danh sách bài đã xáo + nonce) trước khi người dùng chọn lá.
 */
class TarotRandomizationEngine
{
    public const VERSION = 'fisher_yates_csprng_v1';

    public function __construct(protected ?TarotDeckRegistry $registry = null)
    {
        $this->registry = $registry ?? new TarotDeckRegistry;
    }

    /**
     * Xáo đủ 78 lá bài bằng Fisher–Yates CSPRNG và gán chiều bài cố định trước khi người dùng chọn.
     *
     * @return array{
     *     deck: list<array{slot_index: int, card_id: string, is_reversed: bool}>,
     *     commitment_hash: string,
     *     commitment_nonce: string,
     *     randomization_version: string
     * }
     */
    public function createShuffledDeck(string $orientationMode = 'upright_reversed'): array
    {
        $cardIds = $this->registry->getAllCardIds();
        $n = count($cardIds);
        if ($n !== 78) {
            throw new InvalidArgumentException("Bộ bài RWS phải có chính xác 78 lá (hiện có {$n}).");
        }

        // Fisher–Yates shuffle chuẩn: duyệt từ cuối về đầu, không thiên lệch modulo
        for ($i = $n - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            if ($i !== $j) {
                $tmp = $cardIds[$i];
                $cardIds[$i] = $cardIds[$j];
                $cardIds[$j] = $tmp;
            }
        }

        $allowReversed = $orientationMode === 'upright_reversed';
        $deck = [];
        foreach ($cardIds as $slotIdx => $cardId) {
            $deck[] = [
                'slot_index' => $slotIdx,
                'card_id' => $cardId,
                'is_reversed' => $allowReversed ? (random_int(0, 1) === 1) : false,
            ];
        }

        $nonce = bin2hex(random_bytes(16));
        $commitmentHash = $this->computeCommitmentHash($deck, $nonce);

        return [
            'deck' => $deck,
            'commitment_hash' => $commitmentHash,
            'commitment_nonce' => $nonce,
            'randomization_version' => self::VERSION,
        ];
    }

    /**
     * Cắt bài (Deck Cut) tại vị trí hợp lệ 1..77 — thực sự thay đổi thứ tự bộ bài theo quy tắc cố định.
     *
     * @param  list<array{slot_index: int, card_id: string, is_reversed: bool}>  $deck
     * @return list<array{slot_index: int, card_id: string, is_reversed: bool}>
     */
    public function cutDeck(array $deck, int $cutIndex): array
    {
        $n = count($deck);
        if ($n < 2) {
            return $deck;
        }
        $cut = max(1, min($n - 1, $cutIndex));
        $reordered = array_merge(array_slice($deck, $cut), array_slice($deck, 0, $cut));
        foreach ($reordered as $idx => &$item) {
            $item['slot_index'] = $idx;
        }
        unset($item);

        return $reordered;
    }

    /**
     * @param  list<array{slot_index: int, card_id: string, is_reversed: bool}>  $deck
     */
    public function computeCommitmentHash(array $deck, string $nonce): string
    {
        $canonical = implode('|', array_map(
            static fn (array $item): string => $item['card_id'].':'.($item['is_reversed'] ? 'R' : 'U'),
            $deck
        ));

        return hash('sha256', $canonical.'::'.$nonce);
    }
}
