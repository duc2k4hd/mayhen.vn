<?php

declare(strict_types=1);

namespace App\Services\Numerology;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Numerology Calculation Engine — 100% Tất Định (Deterministic), Độc Lập Hoàn Toàn Với AI.
 *
 * - Hỗ trợ đầy đủ phương pháp mặc định `pythagorean_decoz_v1` (Hans Decoz) & phương pháp bổ sung `phillips_vn_v1` (David A. Phillips).
 * - Luôn tách bạch lớp Biểu đồ ngày sinh 3×3 (nguồn David A. Phillips) với bộ chỉ số Decoz.
 * - Ngày sinh được xử lý thuần ngày lịch (dd/mm/yyyy), không bị lệch múi giờ UTC.
 */
class NumerologyCalculationEngine
{
    public const MIN_YEAR = 1900;

    public const MAX_YEAR = 2100;

    protected NumerologyMethodRegistry $registry;

    protected VietnameseNameNormalizer $normalizer;

    public function __construct(
        ?NumerologyMethodRegistry $registry = null,
        ?VietnameseNameNormalizer $normalizer = null
    ) {
        $this->registry = $registry ?? new NumerologyMethodRegistry;
        $this->normalizer = $normalizer ?? new VietnameseNameNormalizer;
    }

    /**
     * Tính trọn bộ hồ sơ Thần Số Học từ ngày sinh dương lịch (bắt buộc) và họ tên khai sinh (tùy chọn).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function calculate(array $input): array
    {
        $methodId = $this->registry->resolveMethodId($input['method_id'] ?? null);
        $methodMeta = $this->registry->getMethodMeta($methodId);

        $birth = $this->validateCalendarDate(
            (int) ($input['birth_day'] ?? 0),
            (int) ($input['birth_month'] ?? 0),
            (int) ($input['birth_year'] ?? 0)
        );

        $nowVn = Carbon::now('Asia/Ho_Chi_Minh');
        $viewedYear = max(self::MIN_YEAR, min(self::MAX_YEAR, (int) ($input['viewed_year'] ?? $nowVn->year)));
        $viewedMonth = max(1, min(12, (int) ($input['viewed_month'] ?? $nowVn->month)));
        $viewedDay = max(1, min(31, (int) ($input['viewed_day'] ?? $nowVn->day)));
        if (! checkdate($viewedMonth, $viewedDay, $viewedYear)) {
            $viewedDay = 1;
        }

        // 1. Tính Chỉ số Đường Đời (Decoz) & Số Chủ Đạo (Phillips) + Tổng cộng thẳng chữ số để minh bạch sự khác biệt
        $lifePathData = $this->calculateLifePath($birth['day'], $birth['month'], $birth['year'], $methodId);

        // 2. Chỉ số Ngày sinh (giữ nguyên giá trị ngày gốc 1–31) & Chỉ số Thái Độ (ngày + tháng, rút về 1–9)
        $dayReduced = $this->registry->reduce($birth['day'], [11, 22]);
        $birthdayNumber = [
            'raw_day' => $birth['day'],
            'value' => $birth['day'],
            'base_root' => $dayReduced['base_root'],
            'is_master' => in_array($birth['day'], [11, 22], true),
            'display' => (string) $birth['day'],
            'root_display' => $dayReduced['display'],
            'karmic_debt' => $dayReduced['karmic_debt'],
            'steps' => ["Ngày sinh gốc = {$birth['day']} (số nền rút gọn: {$dayReduced['display']})"],
        ];

        $attitudeNumber = $this->registry->reduce($birth['day'] + $birth['month'], []);
        $attitudeNumber['formula_note'] = "Ngày ({$birth['day']}) + Tháng ({$birth['month']}) = ".($birth['day'] + $birth['month'])." → {$attitudeNumber['display']} (rút về 1 chữ số)";

        // 3. Các chỉ số từ Họ Tên Khai Sinh (chỉ tính khi người dùng cung cấp — tuyệt đối không bịa số mặc định)
        $fullNameRaw = trim((string) ($input['full_birth_name'] ?? ''));
        $yMode = (string) ($input['y_mode'] ?? VietnameseNameNormalizer::Y_MODE_VN);
        $customGroups = is_array($input['name_groups'] ?? null) ? $input['name_groups'] : null;
        $yOverrides = is_array($input['y_overrides'] ?? null) ? $input['y_overrides'] : [];

        $nameAnalysis = null;
        $nameIndicators = null;
        if ($fullNameRaw !== '') {
            $nameAnalysis = $this->normalizer->analyze($fullNameRaw, $yMode, $customGroups, $yOverrides);
            $nameIndicators = $this->calculateNameIndicators($nameAnalysis, $lifePathData['primary'], $birth['day']);
        }

        // 4. Tên thường gọi (tách riêng khỏi họ tên khai sinh)
        $everydayNameRaw = trim((string) ($input['everyday_name'] ?? ''));
        $everydayIndicators = null;
        if ($everydayNameRaw !== '') {
            $everydayAnalysis = $this->normalizer->analyze($everydayNameRaw, $yMode);
            $everydayIndicators = [
                'original_name' => $everydayAnalysis['original_name'],
                'normalized_name' => $everydayAnalysis['normalized_name'],
                'expression' => $this->reduceNameByGroups($everydayAnalysis['groups'], 'raw_all_sum', NumerologyMethodRegistry::DECOZ_MASTERS),
                'soul_urge' => $this->reduceNameByGroups($everydayAnalysis['groups'], 'raw_vowel_sum', NumerologyMethodRegistry::DECOZ_MASTERS),
                'personality' => $this->reduceNameByGroups($everydayAnalysis['groups'], 'raw_consonant_sum', NumerologyMethodRegistry::DECOZ_MASTERS),
            ];
        }

        // 5. Chu kỳ cá nhân (Năm / Tháng / Ngày cá nhân — năm dương lịch 01/01–31/12, rút 1 chữ số)
        $personalCycles = $this->calculatePersonalCycles(
            $birth['day'],
            $birth['month'],
            $viewedYear,
            $viewedMonth,
            $viewedDay
        );

        // 6. Bốn Đỉnh Cao (Pinnacles) & Bốn Thử Thách (Challenges) + 3 Giai Đoạn Cuộc Đời
        $pinnaclesAndChallenges = $this->calculatePinnaclesAndChallenges(
            $birth['day'],
            $birth['month'],
            $birth['year'],
            $lifePathData['primary']['base_root']
        );

        // 7. Biểu đồ ngày sinh 3×3 & 8 Đường Mũi Tên (Lớp độc lập nguồn David A. Phillips)
        $birthChart = $this->calculatePhillipsBirthChart($birth['day'], $birth['month'], $birth['year']);

        return [
            'method_id' => $methodId,
            'method_name' => (string) ($methodMeta['name'] ?? $methodId),
            'method_version' => NumerologyMethodRegistry::FORMULA_VERSION,
            'kb_version' => (string) ($this->registry->getManifest()['kb_version'] ?? '1.0.0'),
            'has_full_name' => $nameAnalysis !== null,
            'completeness_label' => $nameAnalysis !== null
                ? 'Hồ sơ đầy đủ (Ngày sinh dương lịch & Họ tên khai sinh)'
                : 'Hồ sơ theo Ngày sinh (chưa có họ tên khai sinh — chưa tính Sứ mệnh, Linh hồn, Nhân cách)',
            'display_name' => trim(strip_tags((string) ($input['display_name'] ?? ''))) ?: ($nameAnalysis['original_name'] ?? 'Hồ Sơ Con Số'),
            'birth' => $birth,
            'viewed_date' => [
                'day' => $viewedDay,
                'month' => $viewedMonth,
                'year' => $viewedYear,
                'label' => sprintf('%02d/%02d/%04d', $viewedDay, $viewedMonth, $viewedYear),
            ],
            'core_numbers' => [
                'life_path' => $lifePathData['primary'],
                'life_path_comparison' => $lifePathData['comparison'],
                'expression' => $nameIndicators['expression'] ?? null,
                'soul_urge' => $nameIndicators['soul_urge'] ?? null,
                'personality' => $nameIndicators['personality'] ?? null,
                'birthday' => $birthdayNumber,
            ],
            'extended_numbers' => [
                'maturity' => $nameIndicators['maturity'] ?? null,
                'attitude' => $attitudeNumber,
                'balance' => $nameIndicators['balance'] ?? null,
                'hidden_passion' => $nameIndicators['hidden_passion'] ?? null,
                'karmic_lessons' => $nameIndicators['karmic_lessons'] ?? null,
                'rational_thought' => $nameIndicators['rational_thought'] ?? null,
                'subconscious_self' => $nameIndicators['subconscious_self'] ?? null,
                'bridges' => $nameIndicators['bridges'] ?? null,
                'karmic_debts' => $this->collectKarmicDebts($lifePathData['primary'], $birthdayNumber, $nameIndicators),
            ],
            'everyday_name_numbers' => $everydayIndicators,
            'personal_cycles' => $personalCycles,
            'pinnacles' => $pinnaclesAndChallenges['pinnacles'],
            'challenges' => $pinnaclesAndChallenges['challenges'],
            'period_cycles' => $pinnaclesAndChallenges['period_cycles'],
            'birth_chart' => $birthChart,
            'name_breakdown' => $nameAnalysis,
        ];
    }

    /**
     * @return array{day: int, month: int, year: int, iso: string, label: string, digits_string: string}
     */
    public function validateCalendarDate(int $day, int $month, int $year): array
    {
        if ($year < self::MIN_YEAR || $year > self::MAX_YEAR) {
            throw new InvalidArgumentException('Năm sinh phải nằm trong khoảng từ 1900 đến 2100.');
        }
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31 || ! checkdate($month, $day, $year)) {
            throw new InvalidArgumentException(sprintf('Ngày sinh %02d/%02d/%04d không tồn tại trên lịch Dương. Vui lòng kiểm tra lại.', $day, $month, $year));
        }

        $todayVn = Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d');
        $iso = sprintf('%04d-%02d-%02d', $year, $month, $day);
        if ($iso > $todayVn) {
            throw new InvalidArgumentException('Ngày sinh không được nằm trong tương lai.');
        }

        return [
            'day' => $day,
            'month' => $month,
            'year' => $year,
            'iso' => $iso,
            'label' => sprintf('%02d/%02d/%04d', $day, $month, $year),
            'digits_string' => sprintf('%02d%02d%04d', $day, $month, $year),
        ];
    }

    /**
     * Tính Life Path (Decoz: rút gọn từng thành phần Ngày/Tháng/Năm giữ Master 11, 22, 33)
     * và đối chiếu với cách cộng thẳng toàn bộ chữ số & David A. Phillips.
     *
     * @return array{primary: array<string, mixed>, comparison: array<string, mixed>}
     */
    protected function calculateLifePath(int $day, int $month, int $year, string $methodId): array
    {
        // 1. Công thức Hans Decoz (phân tách Ngày / Tháng / Năm)
        $dRed = $this->registry->reduce($day, [11, 22]);
        $mRed = $this->registry->reduce($month, [11]);
        $ySum = array_sum(array_map('intval', str_split((string) $year)));
        $yRed = $this->registry->reduce($ySum, NumerologyMethodRegistry::DECOZ_MASTERS);

        $decozTotal = $dRed['value'] + $mRed['value'] + $yRed['value'];
        $decozFinal = $this->registry->reduce($decozTotal, NumerologyMethodRegistry::DECOZ_MASTERS);
        $decozFinal['components'] = [
            'day' => $dRed,
            'month' => $mRed,
            'year' => array_merge($yRed, ['original_year' => $year]),
        ];
        $decozFinal['formula_steps'] = [
            "Ngày {$day} → {$dRed['display']}",
            "Tháng {$month} → {$mRed['display']}",
            "Năm {$year} (tổng chữ số = {$ySum}) → {$yRed['display']}",
            "Cộng 3 thành phần: {$dRed['value']} + {$mRed['value']} + {$yRed['value']} = {$decozTotal} → {$decozFinal['display']}",
        ];

        // 2. Cách cộng thẳng toàn bộ chữ số (dùng cho Phillips và giải thích khác biệt)
        $allDigits = array_map('intval', str_split(sprintf('%02d%02d%04d', $day, $month, $year)));
        $directDigitSum = array_sum($allDigits);
        $directSingle = $this->registry->reduce($directDigitSum, []);
        $phillipsRuling = $this->registry->reducePhillipsRuling($directDigitSum);
        $phillipsRuling['formula_steps'] = [
            'Cộng thẳng từng chữ số ngày sinh: '.implode(' + ', $allDigits).' = '.$directDigitSum,
            'Rút gọn theo David A. Phillips (giữ 10, 11, 22/4): '.$phillipsRuling['display'],
        ];

        $differsFromDirect = $decozFinal['display'] !== $directSingle['display'];

        return [
            'primary' => $methodId === NumerologyMethodRegistry::PHILLIPS_METHOD ? $phillipsRuling : $decozFinal,
            'comparison' => [
                'decoz_display' => $decozFinal['display'],
                'phillips_display' => $phillipsRuling['display'],
                'direct_digit_sum' => $directDigitSum,
                'direct_single_root' => $directSingle['value'],
                'differs_by_method' => $differsFromDirect || ($decozFinal['display'] !== $phillipsRuling['display']),
                'explanation' => $differsFromDirect
                    ? "Theo Hans Decoz (rút gọn riêng Ngày/Tháng/Năm giữ Master): {$decozFinal['display']}. Nếu cộng thẳng toàn bộ chữ số ({$directDigitSum}) ra số nền {$directSingle['value']} (và theo David A. Phillips là {$phillipsRuling['display']}). Hai phương pháp có cùng số nền {$decozFinal['base_root']} nhưng khác quy tắc giữ Master Number."
                    : "Cả hai cách tính phân thành phần (Hans Decoz) và cộng thẳng chữ số đều hội tụ tại số nền {$decozFinal['base_root']} (Phillips: {$phillipsRuling['display']}).",
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $nameAnalysis
     * @param  array<string, mixed>  $lifePath
     * @return array<string, mixed>
     */
    protected function calculateNameIndicators(array $nameAnalysis, array $lifePath, int $rawDay): array
    {
        $groups = $nameAnalysis['groups'];
        $letters = $nameAnalysis['letters'];

        $expression = $this->reduceNameByGroups($groups, 'raw_all_sum', NumerologyMethodRegistry::DECOZ_MASTERS);
        $soulUrge = $this->reduceNameByGroups($groups, 'raw_vowel_sum', NumerologyMethodRegistry::DECOZ_MASTERS);
        $personality = $this->reduceNameByGroups($groups, 'raw_consonant_sum', NumerologyMethodRegistry::DECOZ_MASTERS);

        // Maturity = Life Path + Expression (giữ Master 11, 22, 33)
        $maturityRaw = $lifePath['value'] + $expression['value'];
        $maturity = $this->registry->reduce($maturityRaw, NumerologyMethodRegistry::DECOZ_MASTERS);
        $maturity['formula_note'] = "Đường Đời ({$lifePath['value']}) + Sứ Mệnh ({$expression['value']}) = {$maturityRaw} → {$maturity['display']}";

        // Balance = Tổng chữ cái đầu các từ trong họ tên lúc sinh (rút về 1 chữ số, không giữ Master)
        $initials = [];
        $initialSum = 0;
        foreach ($groups as $g) {
            foreach ($g['words'] as $w) {
                $firstChar = $w[0] ?? '';
                $val = NumerologyMethodRegistry::PYTHAGOREAN_MAP[$firstChar] ?? 0;
                if ($val > 0) {
                    $initials[] = "{$firstChar}({$val})";
                    $initialSum += $val;
                }
            }
        }
        $balance = $this->registry->reduce($initialSum, []);
        $balance['initials'] = $initials;
        $balance['formula_note'] = 'Chữ cái đầu các từ: '.implode(' + ', $initials)." = {$initialSum} → {$balance['display']}";

        // Tần suất 1..9 trong họ tên -> Hidden Passion, Karmic Lessons, Subconscious Self
        $freq = array_fill(1, 9, 0);
        foreach ($letters as $l) {
            $freq[(int) $l['value']]++;
        }
        $maxFreq = max($freq);
        $hiddenPassions = [];
        $karmicLessons = [];
        for ($n = 1; $n <= 9; $n++) {
            if ($freq[$n] === $maxFreq && $maxFreq > 0) {
                $hiddenPassions[] = $n;
            }
            if ($freq[$n] === 0) {
                $karmicLessons[] = $n;
            }
        }
        $subconsciousSelf = 9 - count($karmicLessons);

        // Rational Thought = Tổng chữ cái tên chính + Ngày sinh gốc (giữ 11, 22)
        $firstGroup = $nameAnalysis['first_name_group'];
        $firstSum = (int) ($firstGroup['raw_all_sum'] ?? 0);
        $rationalRaw = $firstSum + $rawDay;
        $rationalThought = $this->registry->reduce($rationalRaw, [11, 22]);
        $rationalThought['formula_note'] = 'Tên chính ('.($firstGroup['text'] ?? '')." = {$firstSum}) + Ngày sinh ({$rawDay}) = {$rationalRaw} → {$rationalThought['display']}";

        // Bridge Numbers (hiệu tuyệt đối số nền 1..9)
        $bridgeLpExp = abs($lifePath['base_root'] - $expression['base_root']);
        $bridgeSoulPers = abs($soulUrge['base_root'] - $personality['base_root']);

        return [
            'expression' => $expression,
            'soul_urge' => $soulUrge,
            'personality' => $personality,
            'maturity' => $maturity,
            'balance' => $balance,
            'hidden_passion' => [
                'numbers' => $hiddenPassions,
                'max_frequency' => $maxFreq,
                'frequency_map' => $freq,
            ],
            'karmic_lessons' => [
                'missing_numbers' => $karmicLessons,
                'note' => 'Các giá trị chưa xuất hiện trong bảng quy đổi tên — chỉ mang tính gợi ý rèn luyện thêm, không phải khuyết điểm cố định.',
            ],
            'subconscious_self' => [
                'value' => $subconsciousSelf,
                'formula_note' => '9 − '.count($karmicLessons)." (số bài học) = {$subconsciousSelf}",
            ],
            'rational_thought' => $rationalThought,
            'bridges' => [
                'life_path_expression' => $bridgeLpExp,
                'soul_personality' => $bridgeSoulPers,
            ],
        ];
    }

    /**
     * Cộng riêng từng nhóm họ/đệm/tên rồi mới cộng tổng cuối theo chuẩn Hans Decoz.
     *
     * @param  list<array<string, mixed>>  $groups
     * @param  list<int>  $keepMasters
     * @return array<string, mixed>
     */
    protected function reduceNameByGroups(array $groups, string $sumKey, array $keepMasters): array
    {
        $groupTraces = [];
        $sumOfReducedGroups = 0;
        $rawTotal = 0;

        foreach ($groups as $g) {
            $gRaw = (int) ($g[$sumKey] ?? 0);
            $rawTotal += $gRaw;
            if ($gRaw === 0) {
                $groupTraces[] = [
                    'group' => $g['text'],
                    'label' => $g['label'],
                    'raw_sum' => 0,
                    'value' => 0,
                    'display' => '0',
                ];

                continue;
            }
            $gRed = $this->registry->reduce($gRaw, $keepMasters);
            $sumOfReducedGroups += $gRed['value'];
            $groupTraces[] = [
                'group' => $g['text'],
                'label' => $g['label'],
                'raw_sum' => $gRaw,
                'value' => $gRed['value'],
                'display' => $gRed['display'],
            ];
        }

        if ($sumOfReducedGroups === 0) {
            return array_merge($this->registry->reduce(0, [], allowZero: true), [
                'group_traces' => $groupTraces,
                'raw_letters_total' => 0,
                'is_empty_category' => true,
            ]);
        }

        $final = $this->registry->reduce($sumOfReducedGroups, $keepMasters);
        $final['group_traces'] = $groupTraces;
        $final['raw_letters_total'] = $rawTotal;
        $final['is_empty_category'] = false;

        return $final;
    }

    /**
     * @return array<string, mixed>
     */
    protected function calculatePersonalCycles(int $birthDay, int $birthMonth, int $viewedYear, int $viewedMonth, int $viewedDay): array
    {
        // Năm cá nhân: Ngày sinh + Tháng sinh + Năm xem -> rút về 1 chữ số (1..9)
        $py = $this->registry->reduce($birthDay + $birthMonth + $viewedYear, []);
        $pm = $this->registry->reduce($py['value'] + $viewedMonth, []);
        $pd = $this->registry->reduce($pm['value'] + $viewedDay, []);

        // Dòng thời gian chu kỳ 9 năm xoay quanh năm xem (-2 đến +6)
        $nineYearTimeline = [];
        for ($offset = -2; $offset <= 6; $offset++) {
            $y = $viewedYear + $offset;
            $r = $this->registry->reduce($birthDay + $birthMonth + $y, []);
            $nineYearTimeline[] = [
                'year' => $y,
                'value' => $r['value'],
                'is_current' => $y === $viewedYear,
            ];
        }

        // Lịch 12 tháng cá nhân trong năm đang xem
        $months12 = [];
        for ($m = 1; $m <= 12; $m++) {
            $mr = $this->registry->reduce($py['value'] + $m, []);
            $months12[] = [
                'month' => $m,
                'value' => $mr['value'],
                'is_current' => $m === $viewedMonth,
            ];
        }

        return [
            'year_mode_note' => 'Áp dụng năm dương lịch 01/01–31/12 theo múi giờ Asia/Ho_Chi_Minh (không chuyển theo Tết Âm lịch).',
            'personal_year' => array_merge($py, ['year' => $viewedYear, 'formula' => "{$birthDay} + {$birthMonth} + {$viewedYear} = ".($birthDay + $birthMonth + $viewedYear)." → {$py['value']}"]),
            'personal_month' => array_merge($pm, ['month' => $viewedMonth, 'formula' => "{$py['value']} + {$viewedMonth} → {$pm['value']}"]),
            'personal_day' => array_merge($pd, ['day' => $viewedDay, 'formula' => "{$pm['value']} + {$viewedDay} → {$pd['value']}"]),
            'nine_year_timeline' => $nineYearTimeline,
            'twelve_months' => $months12,
        ];
    }

    /**
     * Tính 4 Đỉnh Cao (Pinnacles), 4 Thử Thách (Challenges) & 3 Giai Đoạn Cuộc Đời chuẩn Hans Decoz.
     *
     * @return array{pinnacles: list<array<string, mixed>>, challenges: list<array<string, mixed>>, period_cycles: list<array<string, mixed>>}
     */
    protected function calculatePinnaclesAndChallenges(int $day, int $month, int $year, int $lifePathSingleRoot): array
    {
        $m = $this->registry->reduce($month, [])['value'];
        $d = $this->registry->reduce($day, [])['value'];
        $y = $this->registry->reduce($year, [])['value'];

        // Pinnacles (giữ Master 11, 22)
        $p1 = $this->registry->reduce($m + $d, [11, 22]);
        $p2 = $this->registry->reduce($d + $y, [11, 22]);
        $p3 = $this->registry->reduce($p1['value'] + $p2['value'], [11, 22]);
        $p4 = $this->registry->reduce($m + $y, [11, 22]);

        $firstEnd = 36 - $lifePathSingleRoot;
        $pinnacles = [
            ['order' => 1, 'label' => 'Đỉnh Cao 1', 'age_start' => 0, 'age_end' => $firstEnd, 'age_range' => "0 – {$firstEnd} tuổi", 'formula' => "Tháng ({$m}) + Ngày ({$d})", 'trace' => $p1, 'value' => $p1['value'], 'display' => $p1['display']],
            ['order' => 2, 'label' => 'Đỉnh Cao 2', 'age_start' => $firstEnd + 1, 'age_end' => $firstEnd + 9, 'age_range' => ($firstEnd + 1).' – '.($firstEnd + 9).' tuổi', 'formula' => "Ngày ({$d}) + Năm ({$y})", 'trace' => $p2, 'value' => $p2['value'], 'display' => $p2['display']],
            ['order' => 3, 'label' => 'Đỉnh Cao 3', 'age_start' => $firstEnd + 10, 'age_end' => $firstEnd + 18, 'age_range' => ($firstEnd + 10).' – '.($firstEnd + 18).' tuổi', 'formula' => "Đỉnh 1 ({$p1['value']}) + Đỉnh 2 ({$p2['value']})", 'trace' => $p3, 'value' => $p3['value'], 'display' => $p3['display']],
            ['order' => 4, 'label' => 'Đỉnh Cao 4', 'age_start' => $firstEnd + 19, 'age_end' => null, 'age_range' => 'Từ '.($firstEnd + 19).' tuổi trở đi', 'formula' => "Tháng ({$m}) + Năm ({$y})", 'trace' => $p4, 'value' => $p4['value'], 'display' => $p4['display']],
        ];

        // Challenges (giá trị tuyệt đối 0..8, KHÔNG đổi 0 thành 9)
        $c1 = abs($m - $d);
        $c2 = abs($d - $y);
        $c3 = abs($c1 - $c2);
        $c4 = abs($m - $y);

        $challenges = [
            ['order' => 1, 'label' => 'Thử Thách 1 (Khởi đầu)', 'value' => $c1, 'formula' => "|{$m} − {$d}| = {$c1}"],
            ['order' => 2, 'label' => 'Thử Thách 2 (Trưởng thành)', 'value' => $c2, 'formula' => "|{$d} − {$y}| = {$c2}"],
            ['order' => 3, 'label' => 'Thử Thách 3 (Chủ đạo)', 'value' => $c3, 'formula' => "|{$c1} − {$c2}| = {$c3}"],
            ['order' => 4, 'label' => 'Thử Thách 4 (Hậu vận)', 'value' => $c4, 'formula' => "|{$m} − {$y}| = {$c4}"],
        ];

        $periodCycles = [
            ['order' => 1, 'label' => 'Giai Đoạn Hình Thành (Tháng sinh)', 'value' => $m],
            ['order' => 2, 'label' => 'Giai Đoạn Kiến Tạo (Ngày sinh)', 'value' => $d],
            ['order' => 3, 'label' => 'Giai Đoạn Thu Hoạch (Năm sinh)', 'value' => $y],
        ];

        return [
            'pinnacles' => $pinnacles,
            'challenges' => $challenges,
            'period_cycles' => $periodCycles,
        ];
    }

    /**
     * Biểu đồ ngày sinh 3×3 & 8 Đường Mũi Tên (Nguồn độc lập: David A. Phillips).
     *
     * @return array<string, mixed>
     */
    protected function calculatePhillipsBirthChart(int $day, int $month, int $year): array
    {
        $rawDigitsStr = sprintf('%02d%02d%04d', $day, $month, $year);
        $counts = array_fill(1, 9, 0);
        $zeroCount = 0;

        foreach (str_split($rawDigitsStr) as $ch) {
            $n = (int) $ch;
            if ($n === 0) {
                $zeroCount++;
            } elseif ($n >= 1 && $n <= 9) {
                $counts[$n]++;
            }
        }

        $cells = [];
        for ($n = 1; $n <= 9; $n++) {
            $cells[$n] = [
                'digit' => $n,
                'count' => $counts[$n],
                'display' => $counts[$n] > 0 ? str_repeat((string) $n, $counts[$n]) : '',
            ];
        }

        $arrowDefs = [
            ['code' => '1-2-3', 'digits' => [1, 2, 3]],
            ['code' => '4-5-6', 'digits' => [4, 5, 6]],
            ['code' => '7-8-9', 'digits' => [7, 8, 9]],
            ['code' => '1-4-7', 'digits' => [1, 4, 7]],
            ['code' => '2-5-8', 'digits' => [2, 5, 8]],
            ['code' => '3-6-9', 'digits' => [3, 6, 9]],
            ['code' => '1-5-9', 'digits' => [1, 5, 9]],
            ['code' => '3-5-7', 'digits' => [3, 5, 7]],
        ];

        $arrows = [];
        foreach ($arrowDefs as $def) {
            $present = 0;
            foreach ($def['digits'] as $d) {
                if ($counts[$d] > 0) {
                    $present++;
                }
            }
            $status = $present === 3 ? 'full' : ($present === 0 ? 'empty' : 'partial');
            $arrows[] = [
                'code' => $def['code'],
                'digits' => $def['digits'],
                'present_count' => $present,
                'status' => $status,
            ];
        }

        return [
            'provenance' => 'phillips_layer',
            'source_note' => 'Lớp Biểu đồ ngày sinh 3×3 độc lập tham chiếu Dr. David A. Phillips (bỏ số 0 khỏi lưới ô, giữ nguyên ngày sinh gốc).',
            'original_digits_string' => $rawDigitsStr,
            'zero_count_in_birth_date' => $zeroCount,
            'grid_layout' => [[3, 6, 9], [2, 5, 8], [1, 4, 7]],
            'cells' => $cells,
            'arrows' => $arrows,
        ];
    }

    /**
     * @param  array<string, mixed>  $lifePath
     * @param  array<string, mixed>  $birthday
     * @param  array<string, mixed>|null  $nameIndicators
     * @return list<array{compound: int, indicator: string}>
     */
    protected function collectKarmicDebts(array $lifePath, array $birthday, ?array $nameIndicators): array
    {
        $found = [];
        $check = [
            'Đường Đời' => $lifePath['karmic_debt'] ?? null,
            'Ngày Sinh' => $birthday['karmic_debt'] ?? null,
            'Sứ Mệnh' => $nameIndicators['expression']['karmic_debt'] ?? null,
            'Linh Hồn' => $nameIndicators['soul_urge']['karmic_debt'] ?? null,
            'Nhân Cách' => $nameIndicators['personality']['karmic_debt'] ?? null,
        ];
        foreach ($check as $label => $compound) {
            if ($compound !== null) {
                $found[] = ['compound' => (int) $compound, 'indicator' => $label];
            }
        }

        return $found;
    }
}
