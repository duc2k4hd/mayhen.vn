<?php

declare(strict_types=1);

namespace App\Services\Calendar;

/**
 * Holiday Engine, Tính Ngày Giỗ Âm Lịch & Đếm Ngược Sự Kiện Truyền Thống Việt Nam.
 *
 * - Phân biệt rạch ròi: Ngày nghỉ lễ theo Bộ luật Lao động (is_public_holiday) vs Ngày lễ truyền thống / kỷ niệm.
 * - Hỗ trợ quy ước gia đình khi tính Ngày Giỗ rơi vào tháng nhuận hoặc ngày 30 trong tháng thiếu 29 ngày.
 */
class VietnameseHolidayEngine
{
    /** @var list<array{month: int, day: int, name: string, type: string, is_public_holiday: bool, desc: string}> */
    public const SOLAR_HOLIDAYS = [
        ['month' => 1, 'day' => 1, 'name' => 'Tết Dương Lịch', 'type' => 'nghi_le', 'is_public_holiday' => true, 'desc' => 'Ngày nghỉ lễ chính thức đầu năm Dương lịch theo Bộ luật Lao động Việt Nam.'],
        ['month' => 2, 'day' => 14, 'name' => 'Lễ Tình Nhân (Valentine)', 'type' => 'quoc_te', 'is_public_holiday' => false, 'desc' => 'Ngày kỷ niệm tình yêu của các cặp đôi.'],
        ['month' => 3, 'day' => 8, 'name' => 'Ngày Quốc Tế Phụ Nữ', 'type' => 'quoc_te', 'is_public_holiday' => false, 'desc' => 'Ngày tôn vinh phụ nữ trên toàn thế giới.'],
        ['month' => 4, 'day' => 30, 'name' => 'Ngày Giải Phóng Miền Nam, Thống Nhất Đất Nước', 'type' => 'nghi_le', 'is_public_holiday' => true, 'desc' => 'Ngày nghỉ lễ quốc gia theo Bộ luật Lao động Việt Nam.'],
        ['month' => 5, 'day' => 1, 'name' => 'Ngày Quốc Tế Lao Động', 'type' => 'nghi_le', 'is_public_holiday' => true, 'desc' => 'Ngày nghỉ lễ chính thức tôn vinh người lao động.'],
        ['month' => 6, 'day' => 1, 'name' => 'Ngày Quốc Tế Thiếu Nhi', 'type' => 'quoc_te', 'is_public_holiday' => false, 'desc' => 'Ngày hội dành cho trẻ em.'],
        ['month' => 6, 'day' => 28, 'name' => 'Ngày Gia Đình Việt Nam', 'type' => 'ky_niem', 'is_public_holiday' => false, 'desc' => 'Ngày tôn vinh mái ấm và giá trị truyền thống gia đình Việt Nam.'],
        ['month' => 9, 'day' => 2, 'name' => 'Ngày Quốc Khánh Việt Nam', 'type' => 'nghi_le', 'is_public_holiday' => true, 'desc' => 'Ngày nghỉ lễ Quốc khánh nước Cộng hòa Xã hội Chủ nghĩa Việt Nam.'],
        ['month' => 10, 'day' => 20, 'name' => 'Ngày Phụ Nữ Việt Nam', 'type' => 'ky_niem', 'is_public_holiday' => false, 'desc' => 'Ngày kỷ niệm thành lập Hội Liên hiệp Phụ nữ Việt Nam.'],
        ['month' => 11, 'day' => 20, 'name' => 'Ngày Nhà Giáo Việt Nam', 'type' => 'ky_niem', 'is_public_holiday' => false, 'desc' => 'Ngày tri ân thầy cô giáo Việt Nam.'],
        ['month' => 12, 'day' => 25, 'name' => 'Lễ Giáng Sinh (Noel)', 'type' => 'van_hoa', 'is_public_holiday' => false, 'desc' => 'Ngày lễ văn hóa – tôn giáo kỷ niệm Chúa Giáng Sinh.'],
    ];

    /** @var list<array{month: int, day: int, name: string, type: string, is_public_holiday: bool, desc: string}> */
    public const LUNAR_HOLIDAYS = [
        ['month' => 1, 'day' => 1, 'name' => 'Mùng 1 Tết Nguyên Đán', 'type' => 'nghi_le', 'is_public_holiday' => true, 'desc' => 'Ngày đầu tiên của năm mới Âm lịch — ngày Tết cổ truyền lớn nhất của dân tộc Việt Nam.'],
        ['month' => 1, 'day' => 2, 'name' => 'Mùng 2 Tết Nguyên Đán', 'type' => 'nghi_le', 'is_public_holiday' => true, 'desc' => 'Ngày thăm hỏi bên ngoại, chúc Tết họ hàng và bạn bè.'],
        ['month' => 1, 'day' => 3, 'name' => 'Mùng 3 Tết Nguyên Đán', 'type' => 'nghi_le', 'is_public_holiday' => true, 'desc' => 'Ngày Tết thầy cô, hóa vàng và du xuân đầu năm.'],
        ['month' => 1, 'day' => 15, 'name' => 'Tết Nguyên Tiêu (Rằm Tháng Giêng)', 'type' => 'truyen_thong', 'is_public_holiday' => false, 'desc' => 'Ngày rằm đầu tiên của năm mới, dâng hương cầu bình an cho gia đạo.'],
        ['month' => 3, 'day' => 3, 'name' => 'Tết Hàn Thực', 'type' => 'truyen_thong', 'is_public_holiday' => false, 'desc' => 'Phong tục làm bánh trôi, bánh chay dâng cúng tổ tiên.'],
        ['month' => 3, 'day' => 10, 'name' => 'Giỗ Tổ Hùng Vương', 'type' => 'nghi_le', 'is_public_holiday' => true, 'desc' => 'Ngày nghỉ lễ quốc gia tưởng nhớ công ơn các Vua Hùng dựng nước.'],
        ['month' => 4, 'day' => 15, 'name' => 'Đại Lễ Phật Đản', 'type' => 'ton_giao', 'is_public_holiday' => false, 'desc' => 'Ngày lễ lớn của Phật giáo kỷ niệm ngày Đức Phật Thích Ca đản sinh.'],
        ['month' => 5, 'day' => 5, 'name' => 'Tết Đoan Ngọ', 'type' => 'truyen_thong', 'is_public_holiday' => false, 'desc' => 'Tết giữa năm theo phong tục dân gian Việt Nam (cơm rượu nếp, trái cây đầu mùa).'],
        ['month' => 7, 'day' => 15, 'name' => 'Lễ Vu Lan Báo Hiếu', 'type' => 'truyen_thong', 'is_public_holiday' => false, 'desc' => 'Mùa hiếu hạnh tưởng nhớ công ơn sinh thành dưỡng dục của cha mẹ và tổ tiên.'],
        ['month' => 8, 'day' => 15, 'name' => 'Tết Trung Thu', 'type' => 'truyen_thong', 'is_public_holiday' => false, 'desc' => 'Tết đoàn viên sum họp gia đình và ngày hội rước đèn phá cỗ của thiếu nhi.'],
        ['month' => 9, 'day' => 9, 'name' => 'Tết Trùng Cửu', 'type' => 'truyen_thong', 'is_public_holiday' => false, 'desc' => 'Ngày lễ truyền thống kính trọng người cao tuổi và thưởng trà mùa thu.'],
        ['month' => 10, 'day' => 15, 'name' => 'Tết Hạ Nguyên', 'type' => 'truyen_thong', 'is_public_holiday' => false, 'desc' => 'Lễ tạ ơn cuối vụ mùa và cầu bình an cuối năm.'],
        ['month' => 12, 'day' => 23, 'name' => 'Ngày Ông Công Ông Táo', 'type' => 'truyen_thong', 'is_public_holiday' => false, 'desc' => 'Phong tục tiễn Táo Quân chầu trời, mở đầu không khí chuẩn bị đón Tết.'],
    ];

    public function __construct(protected VietnameseCalendarEngine $calendar) {}

    /**
     * Lấy tất cả ngày lễ & sự kiện trùng với 1 ngày cụ thể (gồm cả Giao Thừa, Mùng 1, Ngày Rằm).
     *
     * @param  array<string, mixed>  $dayData
     * @return list<array{name: string, type: string, is_public_holiday: bool, calendar_type: string, desc: string}>
     */
    public function getHolidaysForDay(array $dayData): array
    {
        $events = [];
        $sM = (int) $dayData['solar']['month'];
        $sD = (int) $dayData['solar']['day'];
        $lM = (int) $dayData['lunar']['month'];
        $lD = (int) $dayData['lunar']['day'];
        $lLeap = (bool) $dayData['lunar']['is_leap_month'];
        $lLen = (int) $dayData['lunar']['month_length'];

        foreach (self::SOLAR_HOLIDAYS as $h) {
            if ($h['month'] === $sM && $h['day'] === $sD) {
                $events[] = array_merge($h, ['calendar_type' => 'solar']);
            }
        }

        if (! $lLeap) {
            foreach (self::LUNAR_HOLIDAYS as $h) {
                if ($h['month'] === $lM && $h['day'] === $lD) {
                    $events[] = array_merge($h, ['calendar_type' => 'lunar']);
                }
            }
            if ($lM === 12 && $lD === $lLen) {
                $events[] = [
                    'name' => 'Ngày Tất Niên & Đêm Giao Thừa',
                    'type' => 'truyen_thong',
                    'is_public_holiday' => true,
                    'calendar_type' => 'lunar',
                    'desc' => 'Ngày cuối cùng của năm Âm lịch, bữa cơm tất niên sum vầy đón năm mới.',
                ];
            }
        }

        if ($lD === 1 && ! ($lM === 1 && ! $lLeap)) {
            $events[] = [
                'name' => 'Ngày Mùng 1 Âm Lịch'.($lLeap ? ' (Tháng Nhuận)' : ''),
                'type' => 'nghi_le_thang',
                'is_public_holiday' => false,
                'calendar_type' => 'lunar',
                'desc' => 'Ngày đầu tháng Âm lịch (Sóc), thắp hương gia tiên cầu bình an.',
            ];
        } elseif ($lD === 15 && ! in_array($lM, [1, 4, 7, 8, 10], true)) {
            $events[] = [
                'name' => 'Ngày Rằm Âm Lịch'.($lLeap ? ' (Tháng Nhuận)' : ''),
                'type' => 'nghi_le_thang',
                'is_public_holiday' => false,
                'calendar_type' => 'lunar',
                'desc' => 'Ngày giữa tháng Âm lịch (Vọng), dịp sum họp và dâng lễ gia tiên.',
            ];
        }

        return $events;
    }

    /**
     * Đếm ngược số ngày lịch tới các mốc lớn sắp tới tính từ 1 ngày Dương lịch.
     *
     * @return list<array{key: string, name: string, target_solar: string, target_lunar: string, days_remaining: int}>
     */
    public function getUpcomingCountdowns(int $day, int $month, int $year): array
    {
        $todayJdn = $this->calendar->jdFromDate($day, $month, $year);
        $todayData = $this->calendar->convertSolarToLunar($day, $month, $year);
        $lunarYear = (int) $todayData['lunar']['year'];

        $targets = [
            ['key' => 'tet', 'name' => 'Tết Nguyên Đán', 'l_month' => 1, 'l_day' => 1],
            ['key' => 'gio_to', 'name' => 'Giỗ Tổ Hùng Vương', 'l_month' => 3, 'l_day' => 10],
            ['key' => 'vu_lan', 'name' => 'Lễ Vu Lan Báo Hiếu', 'l_month' => 7, 'l_day' => 15],
            ['key' => 'trung_thu', 'name' => 'Tết Trung Thu', 'l_month' => 8, 'l_day' => 15],
        ];

        $out = [];
        foreach ($targets as $t) {
            $y = $lunarYear;
            $conv = $this->calendar->convertLunarToSolar($t['l_day'], $t['l_month'], $y, false);
            if ($conv['jdn'] < $todayJdn && $y + 1 <= VietnameseCalendarEngine::MAX_YEAR) {
                $y++;
                $conv = $this->calendar->convertLunarToSolar($t['l_day'], $t['l_month'], $y, false);
            }
            $out[] = [
                'key' => $t['key'],
                'name' => $t['name'].' '.$conv['can_chi']['year']['label'],
                'target_solar' => $conv['solar']['label'],
                'target_lunar' => sprintf('%02d/%02d Âm lịch', $t['l_day'], $t['l_month']),
                'days_remaining' => max(0, $conv['jdn'] - $todayJdn),
            ];
        }

        usort($out, fn (array $a, array $b): int => $a['days_remaining'] <=> $b['days_remaining']);

        return $out;
    }

    /**
     * Tính lịch Ngày Giỗ / Sinh Nhật Âm Lịch cho N năm tiếp theo theo quy ước gia đình chọn.
     *
     * @param  string  $shortMonthRule  'last_day_of_month' | 'skip'
     * @param  string  $leapMonthRule  'regular_only' | 'leap_if_exists' | 'both'
     * @return list<array{lunar_year: int, year_can_chi: string, solar_date: string, solar_iso: string, weekday: string, lunar_label: string, note: ?string}>
     */
    public function calculateLunarAnniversary(
        int $lunarDay,
        int $lunarMonth,
        int $fromYear,
        int $yearsCount = 6,
        string $shortMonthRule = 'last_day_of_month',
        string $leapMonthRule = 'regular_only'
    ): array {
        $rows = [];
        $limit = max(1, min(15, $yearsCount));

        for ($offset = 0; $offset < $limit; $offset++) {
            $y = $fromYear + $offset;
            if ($y < VietnameseCalendarEngine::MIN_YEAR || $y > VietnameseCalendarEngine::MAX_YEAR) {
                continue;
            }
            $months = $this->calendar->getLunarYearMonths($y);
            $matching = array_values(array_filter($months, fn (array $m): bool => $m['month'] === $lunarMonth));

            foreach ($matching as $m) {
                if ($m['is_leap'] && $leapMonthRule === 'regular_only') {
                    continue;
                }
                if (! $m['is_leap'] && $leapMonthRule === 'leap_if_exists' && count($matching) > 1) {
                    continue;
                }

                $effectiveDay = $lunarDay;
                $note = null;
                if ($lunarDay > $m['days']) {
                    if ($shortMonthRule === 'skip') {
                        continue;
                    }
                    $effectiveDay = $m['days'];
                    $note = "Tháng {$lunarMonth} năm {$y} là tháng thiếu ({$m['days']} ngày): quy đổi về ngày cuối tháng (29/{$lunarMonth} Âm) theo quy ước gia đình.";
                }
                if ($m['is_leap']) {
                    $note = trim(($note ? $note.' ' : '').'Rơi vào tháng nhuận.');
                }

                $targetJdn = $m['start_jdn'] + ($effectiveDay - 1);
                $solar = $this->calendar->dateFromJd($targetJdn);
                if ($solar['year'] > VietnameseCalendarEngine::MAX_YEAR) {
                    continue;
                }
                $info = $this->calendar->convertSolarToLunar($solar['day'], $solar['month'], $solar['year']);

                $rows[] = [
                    'lunar_year' => $y,
                    'year_can_chi' => $info['can_chi']['year']['label'],
                    'solar_date' => $info['solar']['label'],
                    'solar_iso' => $info['solar']['iso'],
                    'weekday' => $info['solar']['weekday_name'],
                    'lunar_label' => sprintf('%02d/%02d%s Âm lịch', $effectiveDay, $lunarMonth, $m['is_leap'] ? ' (Nhuận)' : ''),
                    'note' => $note,
                ];
            }
        }

        return $rows;
    }
}
