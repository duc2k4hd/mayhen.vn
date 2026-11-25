<?php

declare(strict_types=1);

namespace App\Services\Iching;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Bộ chuyển đổi Dương lịch -> Âm lịch Việt Nam chuẩn múi giờ UTC+7 (Asia/Ho_Chi_Minh).
 * Dựa trên thuật toán thiên văn tính Sóc & Trung Khí (Hồ Ngọc Đức / Jean Meeus), hỗ trợ chính xác giai đoạn 1900–2100.
 *
 * Quy ước minh bạch phiên bản `vn_lunar_v1`:
 * - Múi giờ chuẩn: Asia/Ho_Chi_Minh (UTC+7).
 * - Đổi Địa Chi năm tại Mùng 1 Tết Nguyên Đán Âm lịch.
 * - Giờ Tý đêm (23:00–23:59) được tính sang ngày Âm lịch kế tiếp với Giờ Địa Chi = 1 (Tý).
 * - Tháng nhuận giữ nguyên số tháng gốc (1..12) kèm cờ `is_leap_month = true`.
 */
class VietnameseLunarCalendarService
{
    public const MIN_YEAR = 1900;

    public const MAX_YEAR = 2100;

    public const RULE_VERSION = 'vn_lunar_v1';

    /** @var array<int, string> */
    public const EARTHLY_BRANCHES = [
        1 => 'Tý',
        2 => 'Sửu',
        3 => 'Dần',
        4 => 'Mão',
        5 => 'Thìn',
        6 => 'Tỵ',
        7 => 'Ngọ',
        8 => 'Mùi',
        9 => 'Thân',
        10 => 'Dậu',
        11 => 'Tuất',
        12 => 'Hợi',
    ];

    /** @var array<int, string> */
    public const HEAVENLY_STEMS = [
        0 => 'Canh',
        1 => 'Tân',
        2 => 'Nhâm',
        3 => 'Quý',
        4 => 'Giáp',
        5 => 'Ất',
        6 => 'Bính',
        7 => 'Đinh',
        8 => 'Mậu',
        9 => 'Kỷ',
    ];

    /**
     * Chuyển một mốc thời gian bất kỳ về dữ liệu Âm lịch Việt Nam phục vụ Mai Hoa Dịch Số.
     *
     * @return array{
     *     rule_version: string,
     *     timezone: string,
     *     solar_datetime: string,
     *     effective_solar_date: string,
     *     is_late_rat_hour: bool,
     *     lunar_day: int,
     *     lunar_month: int,
     *     lunar_year: int,
     *     is_leap_month: bool,
     *     lunar_date_label: string,
     *     year_can_chi: string,
     *     year_branch_index: int,
     *     year_branch_name: string,
     *     hour_branch_index: int,
     *     hour_branch_name: string
     * }
     */
    public function fromCarbon(Carbon $moment): array
    {
        $vnMoment = $moment->copy()->setTimezone('Asia/Ho_Chi_Minh');
        $year = $vnMoment->year;

        if ($year < self::MIN_YEAR || $year > self::MAX_YEAR) {
            throw new InvalidArgumentException(
                'Dữ liệu chuyển đổi Âm lịch Việt Nam chỉ hỗ trợ giai đoạn từ năm '.self::MIN_YEAR.' đến '.self::MAX_YEAR.'.'
            );
        }

        // Quy ước Giờ Tý Đêm (23:00–23:59): tính sang ngày Âm lịch kế tiếp với giờ Tý (1)
        $isLateRatHour = $vnMoment->hour >= 23;
        $effectiveDate = $isLateRatHour ? $vnMoment->copy()->addDay() : $vnMoment->copy();

        if ($effectiveDate->year > self::MAX_YEAR) {
            throw new InvalidArgumentException(
                'Thời điểm sau chuyển ranh giới giờ Tý đêm vượt quá phạm vi lịch hỗ trợ ('.self::MAX_YEAR.').'
            );
        }

        $lunar = $this->convertSolarToLunar(
            $effectiveDate->day,
            $effectiveDate->month,
            $effectiveDate->year,
            7.0
        );

        $yearBranchIndex = (($lunar['year'] - 4) % 12 + 12) % 12 + 1;
        $yearStem = self::HEAVENLY_STEMS[$lunar['year'] % 10];
        $yearBranchName = self::EARTHLY_BRANCHES[$yearBranchIndex];

        $hourBranchIndex = $this->getHourBranchIndex($vnMoment->hour);
        $hourBranchName = self::EARTHLY_BRANCHES[$hourBranchIndex];

        $monthText = $lunar['month'].($lunar['leap'] ? ' (nhuận)' : '');
        $lunarDateLabel = sprintf(
            'Ngày %02d tháng %s năm %s (%d)',
            $lunar['day'],
            $monthText,
            $yearStem.' '.$yearBranchName,
            $lunar['year']
        );

        return [
            'rule_version' => self::RULE_VERSION,
            'timezone' => 'Asia/Ho_Chi_Minh',
            'solar_datetime' => $vnMoment->format('H:i • d/m/Y'),
            'effective_solar_date' => $effectiveDate->format('d/m/Y'),
            'is_late_rat_hour' => $isLateRatHour,
            'lunar_day' => $lunar['day'],
            'lunar_month' => $lunar['month'],
            'lunar_year' => $lunar['year'],
            'is_leap_month' => $lunar['leap'],
            'lunar_date_label' => $lunarDateLabel,
            'year_can_chi' => $yearStem.' '.$yearBranchName,
            'year_branch_index' => $yearBranchIndex,
            'year_branch_name' => $yearBranchName,
            'hour_branch_index' => $hourBranchIndex,
            'hour_branch_name' => $hourBranchName,
        ];
    }

    /**
     * Xác định số thứ tự 12 giờ Địa Chi (1 = Tý [23h–01h], 2 = Sửu [01h–03h] ... 12 = Hợi [21h–23h]).
     */
    public function getHourBranchIndex(int $hour24): int
    {
        $h = (($hour24 % 24) + 24) % 24;

        return (int) floor((($h + 1) % 24) / 2) + 1;
    }

    /**
     * Chuyển đổi ngày Dương lịch sang Âm lịch theo thuật toán thiên văn chuẩn Việt Nam.
     *
     * @return array{day: int, month: int, year: int, leap: bool}
     */
    public function convertSolarToLunar(int $dd, int $mm, int $yy, float $timeZone = 7.0): array
    {
        if (! checkdate($mm, $dd, $yy) || $yy < self::MIN_YEAR || $yy > self::MAX_YEAR) {
            throw new InvalidArgumentException('Ngày Dương lịch không hợp lệ hoặc nằm ngoài phạm vi 1900–2100.');
        }

        $dayNumber = $this->jdFromDate($dd, $mm, $yy);
        $k = (int) floor(($dayNumber - 2415021.076998695) / 29.530588853);
        $monthStart = $this->getNewMoonDay($k + 1, $timeZone);
        if ($monthStart > $dayNumber) {
            $monthStart = $this->getNewMoonDay($k, $timeZone);
        }

        $a11 = $this->getLunarMonth11($yy, $timeZone);
        $b11 = $a11;
        if ($a11 >= $monthStart) {
            $lunarYear = $yy;
            $a11 = $this->getLunarMonth11($yy - 1, $timeZone);
        } else {
            $lunarYear = $yy + 1;
            $b11 = $this->getLunarMonth11($yy + 1, $timeZone);
        }

        $lunarDay = $dayNumber - $monthStart + 1;
        $diff = (int) floor(($monthStart - $a11) / 29);
        $lunarLeap = false;
        $lunarMonth = $diff + 11;

        if ($b11 - $a11 > 365) {
            $leapMonthDiff = $this->getLeapMonthOffset($a11, $timeZone);
            if ($diff >= $leapMonthDiff) {
                $lunarMonth = $diff + 10;
                if ($diff === $leapMonthDiff) {
                    $lunarLeap = true;
                }
            }
        }

        if ($lunarMonth > 12) {
            $lunarMonth -= 12;
        }
        if ($lunarMonth >= 11 && $diff < 4) {
            $lunarYear -= 1;
        }

        return [
            'day' => $lunarDay,
            'month' => $lunarMonth,
            'year' => $lunarYear,
            'leap' => $lunarLeap,
        ];
    }

    protected function jdFromDate(int $dd, int $mm, int $yy): int
    {
        $a = (int) floor((14 - $mm) / 12);
        $y = $yy + 4800 - $a;
        $m = $mm + 12 * $a - 3;
        $jd = $dd + (int) floor((153 * $m + 2) / 5) + 365 * $y + (int) floor($y / 4) - (int) floor($y / 100) + (int) floor($y / 400) - 32045;

        if ($jd < 2299161) {
            $jd = $dd + (int) floor((153 * $m + 2) / 5) + 365 * $y + (int) floor($y / 4) - 32083;
        }

        return $jd;
    }

    protected function newMoon(int $k): float
    {
        $t = $k / 1236.85;
        $t2 = $t * $t;
        $t3 = $t2 * $t;
        $dr = M_PI / 180.0;

        $jd1 = 2415020.75933 + 29.53058868 * $k + 0.0001178 * $t2 - 0.000000155 * $t3;
        $jd1 += 0.00033 * sin((166.56 + 132.87 * $t - 0.009173 * $t2) * $dr);

        $m = 359.2242 + 29.10535608 * $k - 0.0000333 * $t2 - 0.00000347 * $t3;
        $mpr = 306.0253 + 385.81691806 * $k + 0.0107306 * $t2 + 0.00001236 * $t3;
        $f = 21.2964 + 390.67050646 * $k - 0.0016528 * $t2 - 0.00000239 * $t3;

        $c1 = (0.1734 - 0.000393 * $t) * sin($m * $dr) + 0.0021 * sin(2 * $dr * $m);
        $c1 = $c1 - 0.4068 * sin($mpr * $dr) + 0.0161 * sin($dr * 2 * $mpr);
        $c1 -= 0.0004 * sin($dr * 3 * $mpr);
        $c1 = $c1 + 0.0104 * sin($dr * 2 * $f) - 0.0051 * sin($dr * ($m + $mpr));
        $c1 = $c1 - 0.0074 * sin($dr * ($m - $mpr)) + 0.0004 * sin($dr * (2 * $f + $m));
        $c1 = $c1 - 0.0004 * sin($dr * (2 * $f - $m)) - 0.0006 * sin($dr * (2 * $f + $mpr));
        $c1 = $c1 + 0.0010 * sin($dr * (2 * $f - $mpr)) + 0.0005 * sin($dr * (2 * $mpr + $m));

        if ($t < -11) {
            $deltat = 0.001 + 0.000839 * $t + 0.0002261 * $t2 - 0.00000845 * $t3 - 0.000000081 * $t * $t3;
        } else {
            $deltat = -0.000278 + 0.000265 * $t + 0.000262 * $t2;
        }

        return $jd1 + $c1 - $deltat;
    }

    protected function sunLongitude(float $jdn): float
    {
        $t = ($jdn - 2451545.0) / 36525.0;
        $t2 = $t * $t;
        $dr = M_PI / 180.0;
        $m = 357.52910 + 35999.05030 * $t - 0.0001559 * $t2 - 0.00000048 * $t * $t2;
        $l0 = 280.46645 + 36000.76983 * $t + 0.0003032 * $t2;
        $dl = (1.914600 - 0.004817 * $t - 0.000014 * $t2) * sin($dr * $m);
        $dl += (0.019993 - 0.000101 * $t) * sin($dr * 2 * $m) + 0.000290 * sin($dr * 3 * $m);
        $l = ($l0 + $dl) * $dr;
        $l -= M_PI * 2 * floor($l / (M_PI * 2));

        return $l;
    }

    protected function getSunLongitude(int $dayNumber, float $timeZone): int
    {
        return (int) floor($this->sunLongitude($dayNumber - 0.5 - $timeZone / 24.0) / M_PI * 6.0);
    }

    protected function getNewMoonDay(int $k, float $timeZone): int
    {
        return (int) floor($this->newMoon($k) + 0.5 + $timeZone / 24.0);
    }

    protected function getLunarMonth11(int $yy, float $timeZone): int
    {
        $off = $this->jdFromDate(31, 12, $yy) - 2415021;
        $k = (int) floor($off / 29.530588853);
        $nm = $this->getNewMoonDay($k, $timeZone);
        $sunLong = $this->getSunLongitude($nm, $timeZone);
        if ($sunLong >= 9) {
            $nm = $this->getNewMoonDay($k - 1, $timeZone);
        }

        return $nm;
    }

    protected function getLeapMonthOffset(int $a11, float $timeZone): int
    {
        $k = (int) floor(($a11 - 2415021.076998695) / 29.530588853 + 0.5);
        $i = 1;
        $arc = $this->getSunLongitude($this->getNewMoonDay($k + $i, $timeZone), $timeZone);
        do {
            $last = $arc;
            $i++;
            $arc = $this->getSunLongitude($this->getNewMoonDay($k + $i, $timeZone), $timeZone);
        } while ($arc !== $last && $i < 14);

        return $i - 1;
    }
}
