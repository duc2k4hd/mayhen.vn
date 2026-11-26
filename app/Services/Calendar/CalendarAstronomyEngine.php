<?php

declare(strict_types=1);

namespace App\Services\Calendar;

use Carbon\Carbon;

/**
 * Engine Thiên Văn Việt Nam: 24 Tiết Khí (kinh độ Mặt Trời 15°), Pha Mặt Trăng Thiên Văn & Mặt Trời Mọc/Lặn.
 *
 * - Dựa trên công thức thiên văn Jean Meeus (Astronomical Algorithms).
 * - Tính chính xác thời điểm giao tiết từng năm (không hard-code ngày 4/2 hay 22/12).
 * - Phân biệt rõ ngày 15 Âm lịch và thời điểm Trăng Tròn thiên văn (Full Moon).
 * - Hỗ trợ giờ Mặt Trời mọc/lặn theo từng thành phố Việt Nam (Hà Nội, TP.HCM, Đà Nẵng, Huế, Cần Thơ, Hải Phòng).
 */
class CalendarAstronomyEngine
{
    /** @var list<array{index: int, deg: int, name: string, season: string, is_major: bool, desc: string}> */
    public const SOLAR_TERMS = [
        ['index' => 0, 'deg' => 315, 'name' => 'Lập Xuân', 'season' => 'Xuân', 'is_major' => false, 'desc' => 'Khởi đầu mùa xuân, vạn vật bắt đầu đâm chồi nảy lộc.'],
        ['index' => 1, 'deg' => 330, 'name' => 'Vũ Thủy', 'season' => 'Xuân', 'is_major' => true, 'desc' => 'Mưa xuân ẩm nhẹ, thời tiết ấm dần lên.'],
        ['index' => 2, 'deg' => 345, 'name' => 'Kinh Trập', 'season' => 'Xuân', 'is_major' => false, 'desc' => 'Sấm xuân đánh thức sâu bọ và sinh khí đất trời.'],
        ['index' => 3, 'deg' => 0, 'name' => 'Xuân Phân', 'season' => 'Xuân', 'is_major' => true, 'desc' => 'Giữa mùa xuân, thời lượng ngày và đêm gần bằng nhau.'],
        ['index' => 4, 'deg' => 15, 'name' => 'Thanh Minh', 'season' => 'Xuân', 'is_major' => false, 'desc' => 'Trời quang mây tạnh, dịp tảo mộ tri ân tổ tiên của người Việt.'],
        ['index' => 5, 'deg' => 30, 'name' => 'Cốc Vũ', 'season' => 'Xuân', 'is_major' => true, 'desc' => 'Mưa rào nuôi dưỡng mùa màng tươi tốt cuối xuân.'],
        ['index' => 6, 'deg' => 45, 'name' => 'Lập Hạ', 'season' => 'Hạ', 'is_major' => false, 'desc' => 'Bắt đầu mùa hạ, nền nhiệt tăng rõ rệt.'],
        ['index' => 7, 'deg' => 60, 'name' => 'Tiểu Mãn', 'season' => 'Hạ', 'is_major' => true, 'desc' => 'Lúa và hoa màu bắt đầu chắc hạt.'],
        ['index' => 8, 'deg' => 75, 'name' => 'Mang Chủng', 'season' => 'Hạ', 'is_major' => false, 'desc' => 'Mùa gieo cấy và thu hoạch vụ chiêm bận rộn.'],
        ['index' => 9, 'deg' => 90, 'name' => 'Hạ Chí', 'season' => 'Hạ', 'is_major' => true, 'desc' => 'Ngày dài nhất năm tại Bắc bán cầu do độ nghiêng trục Trái Đất 23,44°.'],
        ['index' => 10, 'deg' => 105, 'name' => 'Tiểu Thử', 'season' => 'Hạ', 'is_major' => false, 'desc' => 'Nắng nóng bắt đầu gay gắt hơn vào giữa hè.'],
        ['index' => 11, 'deg' => 120, 'name' => 'Đại Thử', 'season' => 'Hạ', 'is_major' => true, 'desc' => 'Đỉnh điểm nắng nóng trong năm.'],
        ['index' => 12, 'deg' => 135, 'name' => 'Lập Thu', 'season' => 'Thu', 'is_major' => false, 'desc' => 'Khởi đầu mùa thu, gió heo may dần xuất hiện.'],
        ['index' => 13, 'deg' => 150, 'name' => 'Xử Thử', 'season' => 'Thu', 'is_major' => true, 'desc' => 'Cái nóng mùa hạ bắt đầu dịu xuống.'],
        ['index' => 14, 'deg' => 165, 'name' => 'Bạch Lộ', 'season' => 'Thu', 'is_major' => false, 'desc' => 'Sương sớm trắng đọng trên lá khi đêm se lạnh.'],
        ['index' => 15, 'deg' => 180, 'name' => 'Thu Phân', 'season' => 'Thu', 'is_major' => true, 'desc' => 'Giữa mùa thu, ngày và đêm cân bằng trở lại.'],
        ['index' => 16, 'deg' => 195, 'name' => 'Hàn Lộ', 'season' => 'Thu', 'is_major' => false, 'desc' => 'Khí thu chuyển lạnh sâu hơn về đêm và sáng sớm.'],
        ['index' => 17, 'deg' => 210, 'name' => 'Sương Giáng', 'season' => 'Thu', 'is_major' => true, 'desc' => 'Cuối thu, sương lạnh đậm trước khi sang đông.'],
        ['index' => 18, 'deg' => 225, 'name' => 'Lập Đông', 'season' => 'Đông', 'is_major' => false, 'desc' => 'Bắt đầu mùa đông, sinh khí thu vào nghỉ ngơi.'],
        ['index' => 19, 'deg' => 240, 'name' => 'Tiểu Tuyết', 'season' => 'Đông', 'is_major' => true, 'desc' => 'Gió mùa đông bắc tăng cường tại miền Bắc.'],
        ['index' => 20, 'deg' => 255, 'name' => 'Đại Tuyết', 'season' => 'Đông', 'is_major' => false, 'desc' => 'Trời rét sâu, ngày ngắn dần.'],
        ['index' => 21, 'deg' => 270, 'name' => 'Đông Chí', 'season' => 'Đông', 'is_major' => true, 'desc' => 'Đêm dài nhất năm tại Bắc bán cầu; mốc ấn định tháng 11 Âm lịch.'],
        ['index' => 22, 'deg' => 285, 'name' => 'Tiểu Hàn', 'season' => 'Đông', 'is_major' => false, 'desc' => 'Giai đoạn rét đậm đầu tháng Chạp.'],
        ['index' => 23, 'deg' => 300, 'name' => 'Đại Hàn', 'season' => 'Đông', 'is_major' => true, 'desc' => 'Tiết khí cuối cùng khép lại chu kỳ 24 tiết khí trước khi đón Xuân mới.'],
    ];

    /** @var array<string, array{name: string, lat: float, lng: float}> */
    public const VN_CITIES = [
        'ha-noi' => ['name' => 'Hà Nội', 'lat' => 21.0285, 'lng' => 105.8542],
        'tp-hcm' => ['name' => 'TP. Hồ Chí Minh', 'lat' => 10.8231, 'lng' => 106.6297],
        'da-nang' => ['name' => 'Đà Nẵng', 'lat' => 16.0544, 'lng' => 108.2022],
        'hue' => ['name' => 'Huế', 'lat' => 16.4637, 'lng' => 107.5909],
        'hai-phong' => ['name' => 'Hải Phòng', 'lat' => 20.8449, 'lng' => 106.6881],
        'can-tho' => ['name' => 'Cần Thơ', 'lat' => 10.0452, 'lng' => 105.7469],
    ];

    /** @var array<int, list<array<string, mixed>>> */
    protected static array $yearTermsCache = [];

    /**
     * Kinh độ hoàng đạo biểu kiến Mặt Trời (độ, 0..360) tại thời điểm JD (UTC).
     */
    public function sunLongitudeDeg(float $jd): float
    {
        $t = ($jd - 2451545.0) / 36525.0;
        $t2 = $t * $t;
        $dr = M_PI / 180.0;

        $m = 357.52910 + 35999.05030 * $t - 0.0001559 * $t2 - 0.00000048 * $t * $t2;
        $l0 = 280.46645 + 36000.76983 * $t + 0.0003032 * $t2;
        $dl = (1.914600 - 0.004817 * $t - 0.000014 * $t2) * sin($dr * $m)
            + (0.019993 - 0.000101 * $t) * sin($dr * 2.0 * $m)
            + 0.000290 * sin($dr * 3.0 * $m);

        $omega = 125.04 - 1934.136 * $t;
        $lambda = $l0 + $dl - 0.00569 - 0.00478 * sin($dr * $omega);

        return fmod(fmod($lambda, 360.0) + 360.0, 360.0);
    }

    /**
     * Tính toàn bộ 24 mốc thời điểm giao Tiết Khí trong 1 năm Dương lịch (UTC+7).
     *
     * @return list<array<string, mixed>>
     */
    public function getYearSolarTerms(int $year): array
    {
        if (isset(self::$yearTermsCache[$year])) {
            return self::$yearTermsCache[$year];
        }

        // Bắt đầu từ Tiểu Hàn (285°, đầu tháng 1) -> Đông Chí (270°, cuối tháng 12)
        $orderedIndices = array_merge([22, 23], range(0, 21));
        $results = [];

        foreach ($orderedIndices as $idx) {
            $term = self::SOLAR_TERMS[$idx];
            $targetDeg = (float) $term['deg'];

            // Ước lượng thô ngày giao tiết trong năm Dương lịch
            $daysSinceVernal = fmod($targetDeg + 360.0, 360.0) * (365.2422 / 360.0);
            $approxDayOfYear = 79.5 + $daysSinceVernal;
            if ($approxDayOfYear > 366.0) {
                $approxDayOfYear -= 365.2422;
            }
            $jd = $this->jdFromUtcComponents($year, 1, 1, 0, 0) + $approxDayOfYear - 1.0;

            // Lặp Newton-Raphson hội tụ chính xác tới dưới 1 phút
            for ($iter = 0; $iter < 6; $iter++) {
                $cur = $this->sunLongitudeDeg($jd);
                $diff = $targetDeg - $cur;
                if ($diff > 180.0) {
                    $diff -= 360.0;
                }
                if ($diff < -180.0) {
                    $diff += 360.0;
                }
                $jd += $diff * (365.2422 / 360.0);
                if (abs($diff) < 0.0001) {
                    break;
                }
            }

            $vnDt = $this->jdToVnCarbon($jd);
            $results[] = [
                'index' => $term['index'],
                'name' => $term['name'],
                'deg' => $term['deg'],
                'season' => $term['season'],
                'is_major' => $term['is_major'],
                'desc' => $term['desc'],
                'jd' => $jd,
                'datetime_vn' => $vnDt->format('H:i d/m/Y'),
                'date_iso' => $vnDt->format('Y-m-d'),
                'time_vn' => $vnDt->format('H:i'),
            ];
        }

        usort($results, fn (array $a, array $b): int => $a['jd'] <=> $b['jd']);
        self::$yearTermsCache[$year] = $results;

        return $results;
    }

    /**
     * Xác định Tiết Khí hiện hành và Tiết Khí kế tiếp tại một ngày Dương lịch (tính ở 12:00 UTC+7).
     *
     * @return array<string, mixed>
     */
    public function getSolarTermForDate(int $day, int $month, int $year): array
    {
        $targetJd = $this->jdFromUtcComponents($year, $month, $day, 5, 0); // 12:00 UTC+7 = 05:00 UTC
        $all = array_merge(
            $this->getYearSolarTerms($year - 1),
            $this->getYearSolarTerms($year),
            $this->getYearSolarTerms($year + 1)
        );

        $current = $all[0];
        $next = $all[1];
        foreach ($all as $i => $t) {
            if ($t['jd'] <= $targetJd) {
                $current = $t;
                $next = $all[$i + 1] ?? $t;
            } else {
                break;
            }
        }

        $daysUntilNext = max(0, (int) round($next['jd'] - $targetJd));
        $sunDeg = round($this->sunLongitudeDeg($targetJd), 2);

        return [
            'current' => $current,
            'next' => $next,
            'days_until_next' => $daysUntilNext,
            'sun_longitude_deg' => $sunDeg,
            'solar_month_branch_index' => $this->solarTermIndexToMonthBranch($current['index']),
        ];
    }

    /**
     * Tính Pha Mặt Trăng thiên văn tại một ngày Dương lịch (12:00 UTC+7).
     *
     * @return array<string, mixed>
     */
    public function getMoonPhaseForDate(int $day, int $month, int $year): array
    {
        $jd = $this->jdFromUtcComponents($year, $month, $day, 5, 0); // 12:00 UTC+7
        $synodic = 29.530588853;
        $kApprox = floor(($jd - 2451550.09765) / $synodic);

        $prevNewMoonJd = $this->preciseMoonPhaseJd((int) $kApprox, 0.0);
        if ($prevNewMoonJd > $jd) {
            $kApprox -= 1;
            $prevNewMoonJd = $this->preciseMoonPhaseJd((int) $kApprox, 0.0);
        }
        $nextNewMoonJd = $this->preciseMoonPhaseJd((int) $kApprox + 1, 0.0);
        $fullMoonJd = $this->preciseMoonPhaseJd((int) $kApprox, 0.5);
        if ($fullMoonJd < $jd) {
            $fullMoonJd = $this->preciseMoonPhaseJd((int) $kApprox + 1, 0.5);
        }

        $ageDays = max(0.0, $jd - $prevNewMoonJd);
        $cycleFraction = fmod($ageDays / $synodic, 1.0);
        $phaseAngleDeg = $cycleFraction * 360.0;
        $illuminationPct = round((1.0 - cos($cycleFraction * 2.0 * M_PI)) / 2.0 * 100.0, 1);
        $isWaxing = $cycleFraction < 0.5;

        [$phaseKey, $phaseName] = $this->classifyMoonPhase($cycleFraction);

        return [
            'phase_key' => $phaseKey,
            'phase_name' => $phaseName,
            'cycle_fraction' => round($cycleFraction, 4),
            'phase_angle_deg' => round($phaseAngleDeg, 1),
            'illumination_pct' => $illuminationPct,
            'age_days' => round($ageDays, 1),
            'trend_label' => $isWaxing ? 'Trăng đang tròn dần (Waxing)' : 'Trăng đang khuyết dần (Waning)',
            'next_new_moon_vn' => $this->jdToVnCarbon($nextNewMoonJd)->format('H:i d/m/Y'),
            'next_full_moon_vn' => $this->jdToVnCarbon($fullMoonJd)->format('H:i d/m/Y'),
            'model_note' => 'Mô hình pha Mặt Trăng thiên văn tiêu chuẩn (Bắc bán cầu), tính theo góc pha Mặt Trời – Mặt Trăng, phân biệt với ngày 15 Âm lịch.',
        ];
    }

    /**
     * Tính giờ Mặt Trời mọc, lặn, Chính Ngọ & thời lượng ban ngày theo thành phố Việt Nam (NOAA).
     *
     * @return array<string, mixed>
     */
    public function getSunTimes(int $day, int $month, int $year, string $cityKey = 'ha-noi'): array
    {
        $city = self::VN_CITIES[$cityKey] ?? self::VN_CITIES['ha-noi'];
        $lat = $city['lat'];
        $lng = $city['lng'];
        $tz = 7.0;

        $dayOfYear = (int) Carbon::create($year, $month, $day, 12, 0, 0, 'Asia/Ho_Chi_Minh')->dayOfYear;
        $gamma = (2.0 * M_PI / 365.0) * ($dayOfYear - 1);

        $eqTime = 229.18 * (0.000075 + 0.001868 * cos($gamma) - 0.032077 * sin($gamma)
            - 0.014615 * cos(2 * $gamma) - 0.040849 * sin(2 * $gamma));

        $decl = 0.006918 - 0.399912 * cos($gamma) + 0.070257 * sin($gamma)
            - 0.006758 * cos(2 * $gamma) + 0.000907 * sin(2 * $gamma)
            - 0.002697 * cos(3 * $gamma) + 0.00148 * sin(3 * $gamma);

        $latRad = deg2rad($lat);
        $zenithRad = deg2rad(90.833);
        $cosHa = (cos($zenithRad) / (cos($latRad) * cos($decl))) - tan($latRad) * tan($decl);
        $cosHa = max(-1.0, min(1.0, $cosHa));
        $haDeg = rad2deg(acos($cosHa));

        $solarNoonMin = 720.0 - 4.0 * $lng - $eqTime + $tz * 60.0;
        $sunriseMin = $solarNoonMin - $haDeg * 4.0;
        $sunsetMin = $solarNoonMin + $haDeg * 4.0;
        $daylightMin = (int) round($sunsetMin - $sunriseMin);

        return [
            'city_key' => array_key_exists($cityKey, self::VN_CITIES) ? $cityKey : 'ha-noi',
            'city_name' => $city['name'],
            'sunrise' => $this->minutesToHi($sunriseMin),
            'solar_noon' => $this->minutesToHi($solarNoonMin),
            'sunset' => $this->minutesToHi($sunsetMin),
            'daylight_label' => sprintf('%d giờ %02d phút', intdiv($daylightMin, 60), $daylightMin % 60),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function classifyMoonPhase(float $f): array
    {
        if ($f < 0.03 || $f >= 0.97) {
            return ['new_moon', 'Trăng Non (Sóc)'];
        }
        if ($f < 0.22) {
            return ['waxing_crescent', 'Trăng Lưỡi Liềm Đầu Tháng'];
        }
        if ($f < 0.28) {
            return ['first_quarter', 'Thượng Huyền'];
        }
        if ($f < 0.47) {
            return ['waxing_gibbous', 'Trăng Khuyết Lồi Đầu Tháng'];
        }
        if ($f < 0.53) {
            return ['full_moon', 'Trăng Tròn (Vọng)'];
        }
        if ($f < 0.72) {
            return ['waning_gibbous', 'Trăng Khuyết Lồi Cuối Tháng'];
        }
        if ($f < 0.78) {
            return ['last_quarter', 'Hạ Huyền'];
        }

        return ['waning_crescent', 'Trăng Lưỡi Liềm Cuối Tháng'];
    }

    protected function preciseMoonPhaseJd(int $kBase, float $quarterOffset): float
    {
        $k = $kBase + $quarterOffset;
        $t = $k / 1236.85;
        $t2 = $t * $t;
        $t3 = $t2 * $t;
        $dr = M_PI / 180.0;

        $jde = 2451550.09765 + 29.530588853 * $k + 0.0001337 * $t2 - 0.000000150 * $t3;
        $m = (2.5534 + 29.10535669 * $k - 0.0000218 * $t2) * $dr;
        $mpr = (201.5643 + 385.81693528 * $k + 0.0107438 * $t2) * $dr;
        $f = (160.7108 + 390.67050274 * $k - 0.0016341 * $t2) * $dr;

        if ($quarterOffset === 0.0) {
            $corr = -0.40720 * sin($mpr) + 0.17241 * sin($m) + 0.01608 * sin(2 * $mpr)
                + 0.01039 * sin(2 * $f) + 0.00739 * sin($mpr - $m) - 0.00514 * sin($mpr + $m);
        } else {
            $corr = -0.40614 * sin($mpr) + 0.17302 * sin($m) + 0.01614 * sin(2 * $mpr)
                + 0.01043 * sin(2 * $f) + 0.00734 * sin($mpr - $m) - 0.00515 * sin($mpr + $m);
        }

        return $jde + $corr;
    }

    protected function solarTermIndexToMonthBranch(int $termIndex): int
    {
        // Lập Xuân (0) & Vũ Thủy (1) -> Tháng Dần (2), Kinh Trập (2) & Xuân Phân (3) -> Tháng Mão (3)...
        return (intdiv($termIndex, 2) + 2) % 12;
    }

    protected function jdFromUtcComponents(int $y, int $m, int $d, int $h, int $min): float
    {
        $a = (int) floor((14 - $m) / 12);
        $yy = $y + 4800 - $a;
        $mm = $m + 12 * $a - 3;
        $jdn = $d + (int) floor((153 * $mm + 2) / 5) + 365 * $yy + (int) floor($yy / 4) - (int) floor($yy / 100) + (int) floor($yy / 400) - 32045;

        return $jdn - 0.5 + ($h + $min / 60.0) / 24.0;
    }

    protected function jdToVnCarbon(float $jd): Carbon
    {
        $unix = (int) round(($jd - 2440587.5) * 86400.0);

        return Carbon::createFromTimestampUTC($unix)->setTimezone('Asia/Ho_Chi_Minh');
    }

    protected function minutesToHi(float $mins): string
    {
        $total = ((int) round($mins) % 1440 + 1440) % 1440;

        return sprintf('%02d:%02d', intdiv($total, 60), $total % 60);
    }
}
