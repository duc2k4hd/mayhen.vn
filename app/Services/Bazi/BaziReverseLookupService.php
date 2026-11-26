<?php

declare(strict_types=1);

namespace App\Services\Bazi;

use App\Services\Calendar\CalendarAstronomyEngine;
use App\Services\Calendar\VietnameseCalendarEngine;
use Carbon\Carbon;

/**
 * Tra cứu ngược Bát Tự (Reverse Lookup) cho người nghiên cứu.
 *
 * - Tìm tất cả các ngày giờ sinh hợp lệ khớp bộ Tứ Trụ (Năm, Tháng, Ngày, Giờ) trong khoảng năm chỉ định.
 * - Tối ưu tốc độ O(years * 1) bằng cách thu hẹp thẳng vào năm trùng Can Chi Trụ Năm (mỗi 60 năm 1 lần)
 *   và đúng cửa sổ ~30 ngày của tháng tiết khí tương ứng.
 */
class BaziReverseLookupService
{
    public function __construct(
        protected VietnameseCalendarEngine $calendar,
        protected CalendarAstronomyEngine $astronomy,
        protected BaziCalculationEngine $engine
    ) {}

    /**
     * @return list<array{solar_iso: string, solar_label: string, hour_range: string, year_pillar: string, month_pillar: string, day_pillar: string, hour_pillar: string}>
     */
    public function findMatchingBirthTimes(
        int $yearStem,
        int $yearBranch,
        int $monthStem,
        int $monthBranch,
        int $dayStem,
        int $dayBranch,
        int $hourStem,
        int $hourBranch,
        int $fromYear = 1940,
        int $toYear = 2040
    ): array {
        $fromYear = max(1900, min(2100, $fromYear));
        $toYear = max($fromYear, min(2100, $toYear));

        // Kiểm tra tính hợp lệ Ngũ Hổ Độn & Ngũ Thử Độn trước khi quét
        if ($this->engine->computeMonthStemIndex($yearStem, $monthBranch) !== $monthStem) {
            return [];
        }
        if ($this->engine->computeHourStemIndex($dayStem, $hourBranch) !== $hourStem) {
            return [];
        }

        // Lọc các năm Bát Tự khớp Trụ Năm
        $matchingYears = [];
        for ($y = $fromYear; $y <= $toYear; $y++) {
            $ys = (($y - 4) % 10 + 10) % 10;
            $yb = (($y - 4) % 12 + 12) % 12;
            if ($ys === $yearStem && $yb === $yearBranch) {
                $matchingYears[] = $y;
            }
        }

        $results = [];
        $sampleHour = $hourBranch === 0 ? 0 : ($hourBranch * 2 - 1);

        foreach ($matchingYears as $baziYear) {
            // Quét khoảng 40 ngày quanh tháng tiết khí tương ứng (Dần=tháng 2 .. Sửu=tháng 1 năm sau)
            $offsetFromDan = ($monthBranch - 2 + 12) % 12;
            $approxMonth = ($offsetFromDan + 2);
            $scanYear = $baziYear;
            if ($approxMonth > 12) {
                $approxMonth -= 12;
                $scanYear++;
            }
            if ($scanYear > 2100) {
                continue;
            }

            $start = Carbon::create($scanYear, $approxMonth, 1, 12, 0, 0, 'Asia/Ho_Chi_Minh')->subDays(5);
            for ($d = 0; $d < 45; $d++) {
                $cur = $start->copy()->addDays($d);
                if ($cur->year < 1900 || $cur->year > 2100) {
                    continue;
                }
                $jdn = $this->calendar->jdFromDate($cur->day, $cur->month, $cur->year);
                $ds = (($jdn + 9) % 10 + 10) % 10;
                $db = (($jdn + 1) % 12 + 12) % 12;
                if ($ds !== $dayStem || $db !== $dayBranch) {
                    continue;
                }

                // Kiểm chứng lại bằng BaziCalculationEngine thực tế
                $check = $this->engine->calculate([
                    'calendar_type' => 'solar',
                    'day' => $cur->day,
                    'month' => $cur->month,
                    'year' => $cur->year,
                    'hour' => $sampleHour,
                    'minute' => 30,
                    'time_accuracy' => 'exact',
                    'location_key' => 'ha-noi',
                    'method_id' => 'tu_binh_default_v1',
                ]);

                if (
                    $check['pillars']['year']['stem_index'] === $yearStem &&
                    $check['pillars']['year']['branch_index'] === $yearBranch &&
                    $check['pillars']['month']['stem_index'] === $monthStem &&
                    $check['pillars']['month']['branch_index'] === $monthBranch &&
                    $check['pillars']['day']['stem_index'] === $dayStem &&
                    $check['pillars']['day']['branch_index'] === $dayBranch &&
                    $check['pillars']['hour']['stem_index'] === $hourStem &&
                    $check['pillars']['hour']['branch_index'] === $hourBranch
                ) {
                    $results[] = [
                        'solar_iso' => $cur->format('Y-m-d'),
                        'solar_label' => $cur->format('d/m/Y'),
                        'hour_range' => BaziRegistry::BRANCHES[$hourBranch]['hours'].' (Giờ '.BaziRegistry::BRANCHES[$hourBranch]['name'].')',
                        'year_pillar' => $check['pillars']['year']['label'],
                        'month_pillar' => $check['pillars']['month']['label'],
                        'day_pillar' => $check['pillars']['day']['label'],
                        'hour_pillar' => $check['pillars']['hour']['label'],
                    ];
                }
            }
        }

        return $results;
    }
}
