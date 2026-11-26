<?php

declare(strict_types=1);

namespace App\Services\Tarot;

use Illuminate\Support\Facades\File;

/**
 * Deck Registry & Spread Registry chuẩn Rider–Waite–Smith (78 lá bài + 9 kiểu trải bài).
 *
 * - Sử dụng Static RAM Memoization trong tiến trình PHP worker (0 truy vấn DB).
 * - Đảm bảo chính xác 22 Major Arcana (Strength VIII, Justice XI) và 56 Minor Arcana (4 bộ x 14 lá).
 */
class TarotDeckRegistry
{
    public const DEFAULT_DECK_ID = 'rws_heritage_v1';

    /** @var array<string, mixed>|null */
    protected static ?array $manifest = null;

    /** @var array<string, array<string, mixed>>|null */
    protected static ?array $cardsById = null;

    /** @var array<string, array<string, mixed>>|null */
    protected static ?array $cardsBySlug = null;

    /** @var array<string, array<string, mixed>>|null */
    protected static ?array $spreadsById = null;

    protected function boot(): void
    {
        if (self::$cardsById !== null) {
            return;
        }

        $base = database_path('data/tarot');
        self::$manifest = $this->readJson("{$base}/manifest.json");

        $files = [
            'major_arcana_22.json',
            'minor_wands_14.json',
            'minor_cups_14.json',
            'minor_swords_14.json',
            'minor_pentacles_14.json',
        ];

        $byId = [];
        $bySlug = [];
        $seq = 0;
        foreach ($files as $file) {
            $rows = $this->readJson("{$base}/{$file}");
            foreach ($rows as $row) {
                if (! is_array($row) || empty($row['card_id'])) {
                    continue;
                }
                $row['deck_id'] = self::DEFAULT_DECK_ID;
                $row['sequence_index'] = $seq++;
                $row['suit_label_vi'] = $this->suitLabelVi((string) ($row['suit'] ?? 'major'));
                $row['source_ids'] = ['waite_1910', 'mayhen_editorial_v1'];
                $byId[(string) $row['card_id']] = $row;
                $bySlug[(string) $row['slug']] = $row;
            }
        }
        self::$cardsById = $byId;
        self::$cardsBySlug = $bySlug;

        $spreadsRaw = $this->readJson("{$base}/spreads.json");
        $spMap = [];
        foreach (($spreadsRaw['spreads'] ?? []) as $sp) {
            if (is_array($sp) && ! empty($sp['spread_id'])) {
                $spMap[(string) $sp['spread_id']] = $sp;
            }
        }
        self::$spreadsById = $spMap;
    }

    /**
     * @return array<string, mixed>
     */
    public function getManifest(): array
    {
        $this->boot();

        return self::$manifest ?? [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAllCards(): array
    {
        $this->boot();

        return array_values(self::$cardsById ?? []);
    }

    /**
     * @return list<string>
     */
    public function getAllCardIds(): array
    {
        $this->boot();

        return array_keys(self::$cardsById ?? []);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findCardById(string $cardId): ?array
    {
        $this->boot();

        return self::$cardsById[$cardId] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findCardBySlug(string $slug): ?array
    {
        $this->boot();

        return self::$cardsBySlug[$slug] ?? null;
    }

    /**
     * @return array{prev: ?array<string, mixed>, next: ?array<string, mixed>}
     */
    public function getAdjacentCards(string $slug): array
    {
        $all = $this->getAllCards();
        $total = count($all);
        foreach ($all as $idx => $card) {
            if ($card['slug'] === $slug) {
                return [
                    'prev' => $all[($idx - 1 + $total) % $total] ?? null,
                    'next' => $all[($idx + 1) % $total] ?? null,
                ];
            }
        }

        return ['prev' => null, 'next' => null];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAllSpreads(): array
    {
        $this->boot();

        return self::$spreadsById ?? [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findSpread(string $spreadId): ?array
    {
        $this->boot();

        return self::$spreadsById[$spreadId] ?? null;
    }

    public function suitLabelVi(string $suit): string
    {
        return match ($suit) {
            'major' => 'Bộ Ẩn Chính (Major Arcana)',
            'wands' => 'Bộ Gậy (Wands · Lửa)',
            'cups' => 'Bộ Cốc (Cups · Nước)',
            'swords' => 'Bộ Kiếm (Swords · Khí)',
            'pentacles' => 'Bộ Tiền (Pentacles · Đất)',
            default => $suit,
        };
    }

    /**
     * @return array<mixed>
     */
    protected function readJson(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }
        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
