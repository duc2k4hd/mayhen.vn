<?php

declare(strict_types=1);

namespace App\Services\Numerology;

use Illuminate\Support\Facades\File;

/**
 * Kho quản lý phiên bản phương pháp Thần Số Học & công cụ rút gọn có dấu vết minh bạch.
 *
 * - Mặc định: `pythagorean_decoz_v1` (Pythagorean hiện đại tham chiếu Hans Decoz)
 * - Bổ sung: `phillips_vn_v1` (Nhân số học tham chiếu Dr. David A. Phillips)
 */
class NumerologyMethodRegistry
{
    public const DEFAULT_METHOD = 'pythagorean_decoz_v1';

    public const PHILLIPS_METHOD = 'phillips_vn_v1';

    public const FORMULA_VERSION = '1.0.0';

    /** @var array<string, int> Bảng quy đổi 26 chữ cái Latin Pythagorean hiện đại */
    public const PYTHAGOREAN_MAP = [
        'A' => 1, 'J' => 1, 'S' => 1,
        'B' => 2, 'K' => 2, 'T' => 2,
        'C' => 3, 'L' => 3, 'U' => 3,
        'D' => 4, 'M' => 4, 'V' => 4,
        'E' => 5, 'N' => 5, 'W' => 5,
        'F' => 6, 'O' => 6, 'X' => 6,
        'G' => 7, 'P' => 7, 'Y' => 7,
        'H' => 8, 'Q' => 8, 'Z' => 8,
        'I' => 9, 'R' => 9,
    ];

    /** @var list<int> */
    public const DECOZ_MASTERS = [11, 22, 33];

    /** @var list<int> */
    public const KARMIC_DEBT_COMPOUNDS = [13, 14, 16, 19];

    /** @var array<string, mixed>|null */
    protected static ?array $manifest = null;

    /**
     * @return array<string, mixed>
     */
    public function getManifest(): array
    {
        if (self::$manifest === null) {
            $path = database_path('data/numerology/manifest.json');
            self::$manifest = File::exists($path)
                ? (json_decode((string) File::get($path), true) ?: [])
                : [];
        }

        return self::$manifest;
    }

    public function resolveMethodId(?string $methodId): string
    {
        return $methodId === self::PHILLIPS_METHOD
            ? self::PHILLIPS_METHOD
            : self::DEFAULT_METHOD;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMethodMeta(string $methodId): array
    {
        $id = $this->resolveMethodId($methodId);
        $methods = $this->getManifest()['methods'] ?? [];

        return is_array($methods[$id] ?? null) ? $methods[$id] : [
            'id' => $id,
            'name' => $id === self::PHILLIPS_METHOD
                ? 'Nhân Số Học (Tham chiếu David A. Phillips)'
                : 'Pythagorean Hiện Đại (Tham chiếu Hans Decoz)',
            'formula_version' => self::FORMULA_VERSION,
        ];
    }

    /**
     * Rút gọn số tự nhiên có lưu đầy đủ dấu vết từng bước, số nền và trạng thái Master.
     *
     * @param  list<int>  $keepMasters
     * @return array{raw_sum: int, steps: list<string>, intermediate_values: list<int>, value: int, base_root: int, is_master: bool, display: string, master_rule: string, karmic_debt: ?int}
     */
    public function reduce(int $number, array $keepMasters = [], bool $allowZero = false): array
    {
        $raw = max(0, $number);
        if ($raw === 0) {
            return [
                'raw_sum' => 0,
                'steps' => ['0'],
                'intermediate_values' => [0],
                'value' => 0,
                'base_root' => 0,
                'is_master' => false,
                'display' => '0',
                'master_rule' => $allowZero ? 'allow_zero' : 'single_digit',
                'karmic_debt' => null,
            ];
        }

        $current = $raw;
        $steps = [];
        $intermediates = [$current];
        $karmicDebt = in_array($current, self::KARMIC_DEBT_COMPOUNDS, true) ? $current : null;

        while ($current > 9 && ! in_array($current, $keepMasters, true)) {
            $digits = array_map('intval', str_split((string) $current));
            $next = array_sum($digits);
            $steps[] = implode(' + ', $digits).' = '.$next;
            $current = $next;
            $intermediates[] = $current;
            if ($karmicDebt === null && in_array($current, self::KARMIC_DEBT_COMPOUNDS, true)) {
                $karmicDebt = $current;
            }
        }

        $isMaster = in_array($current, $keepMasters, true) && $current >= 10;
        $baseRoot = $current;
        while ($baseRoot > 9) {
            $baseRoot = array_sum(array_map('intval', str_split((string) $baseRoot)));
        }

        return [
            'raw_sum' => $raw,
            'steps' => $steps !== [] ? $steps : [(string) $raw],
            'intermediate_values' => $intermediates,
            'value' => $current,
            'base_root' => $baseRoot,
            'is_master' => $isMaster,
            'display' => $isMaster ? "{$current}/{$baseRoot}" : (string) $current,
            'master_rule' => $keepMasters !== [] ? 'keep_'.implode('_', $keepMasters) : 'single_digit',
            'karmic_debt' => $karmicDebt,
        ];
    }

    /**
     * Tính Số Chủ Đạo theo quy tắc David A. Phillips (cộng thẳng toàn bộ chữ số ngày sinh, giữ 10, 11, 22/4; tập giá trị 2..11 & 22/4).
     *
     * @return array{raw_sum: int, steps: list<string>, intermediate_values: list<int>, value: int, base_root: int, is_master: bool, display: string, master_rule: string, karmic_debt: ?int}
     */
    public function reducePhillipsRuling(int $digitSum): array
    {
        $current = max(2, $digitSum);
        $steps = [];
        $intermediates = [$current];

        while ($current > 11 && $current !== 22) {
            $digits = array_map('intval', str_split((string) $current));
            $next = array_sum($digits);
            $steps[] = implode(' + ', $digits).' = '.$next;
            $current = $next;
            $intermediates[] = $current;
        }

        $baseRoot = $current;
        while ($baseRoot > 9) {
            $baseRoot = array_sum(array_map('intval', str_split((string) $baseRoot)));
        }

        $isSpecial = in_array($current, [10, 11, 22], true);
        $display = match ($current) {
            22 => '22/4',
            11 => '11',
            10 => '10',
            default => (string) $current,
        };

        return [
            'raw_sum' => $digitSum,
            'steps' => $steps !== [] ? $steps : [(string) $digitSum],
            'intermediate_values' => $intermediates,
            'value' => $current,
            'base_root' => $baseRoot,
            'is_master' => in_array($current, [11, 22], true),
            'display' => $display,
            'master_rule' => $isSpecial ? 'phillips_keep_10_11_22' : 'phillips_2_to_11',
            'karmic_debt' => null,
        ];
    }
}
