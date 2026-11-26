<?php

declare(strict_types=1);

namespace App\Services\Bazi;

use App\Services\Calendar\CalendarAstronomyEngine;
use App\Services\Calendar\VietnameseCalendarEngine;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * BaZi / Four Pillars of Destiny Calculation Engine (Mây Hẹn – Tứ Trụ Nhân Sinh).
 *
 * - 100% tất định, độc lập hoàn toàn với AI.
 * - Tái sử dụng VietnameseCalendarEngine (lịch âm Việt Nam UTC+7) & CalendarAstronomyEngine (24 Tiết Khí Jean Meeus).
 * - Hỗ trợ Civil Time, Local Mean Solar Time (LMST), True Solar Time (Longitude + Equation of Time).
 * - Hỗ trợ 5 mức độ chính xác giờ sinh (tuyệt đối KHÔNG mặc định 12:00 khi không biết giờ sinh).
 * - Đầy đủ: Tứ Trụ, Tàng Can, Thập Thần, Ngũ Hành 3 chế độ, Nguyệt Lệnh, Thông Căn, Thân Vượng/Nhược minh bạch,
 *   Quan hệ Hợp/Xung/Hình/Hại/Phá, Cách Cục, Dụng Thần đa phương pháp, Thần Sát, Thai Nguyên/Mệnh Cung/Thân Cung,
 *   Evidence Graph (Cây Lập Luận), Đại Vận, Lưu Niên và Lưu Nguyệt.
 */
class BaziCalculationEngine
{
    /**
     * 12 mốc Tiết (Jie) ấn định ranh giới Trụ Tháng Bát Tự (không dùng Trung Khí).
     * Map: solar term index trong CalendarAstronomyEngine::SOLAR_TERMS => branch_index.
     *
     * @var array<int, int>
     */
    protected const JIE_TERM_TO_BRANCH = [
        0 => 2,   // Lập Xuân (315°) -> Dần
        2 => 3,   // Kinh Trập (345°) -> Mão
        4 => 4,   // Thanh Minh (15°) -> Thìn
        6 => 5,   // Lập Hạ (45°) -> Tỵ
        8 => 6,   // Mang Chủng (75°) -> Ngọ
        10 => 7,  // Tiểu Thử (105°) -> Mùi
        12 => 8,  // Lập Thu (135°) -> Thân
        14 => 9,  // Bạch Lộ (165°) -> Dậu
        16 => 10, // Hàn Lộ (195°) -> Tuất
        18 => 11, // Lập Đông (225°) -> Hợi
        20 => 0,  // Đại Tuyết (255°) -> Tý
        22 => 1,  // Tiểu Hàn (285°) -> Sửu
    ];

    public function __construct(
        protected VietnameseCalendarEngine $calendar,
        protected CalendarAstronomyEngine $astronomy
    ) {}

    /**
     * Lập trọn bộ lá số Bát Tự xác định từ dữ liệu sinh đầu vào.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function calculate(array $input): array
    {
        $methodId = (string) ($input['method_id'] ?? 'tu_binh_default_v1');
        $method = BaziRegistry::METHOD_PROFILES[$methodId] ?? BaziRegistry::METHOD_PROFILES['tu_binh_default_v1'];

        $calendarType = (string) ($input['calendar_type'] ?? 'solar');
        $day = (int) ($input['day'] ?? 1);
        $month = (int) ($input['month'] ?? 1);
        $year = (int) ($input['year'] ?? 1995);
        $isLeapMonth = (bool) ($input['is_leap_month'] ?? false);

        if ($year < VietnameseCalendarEngine::MIN_YEAR || $year > VietnameseCalendarEngine::MAX_YEAR) {
            throw new InvalidArgumentException('Năm sinh hỗ trợ nằm trong phạm vi 1900–2100.');
        }

        // 1. Chuyển đổi ngày Âm Việt Nam -> Dương lịch qua VietnameseCalendarEngine
        $lunarMeta = null;
        if ($calendarType === 'lunar') {
            $conv = $this->calendar->convertLunarToSolar($day, $month, $year, $isLeapMonth);
            $solarDay = $conv['solar']['day'];
            $solarMonth = $conv['solar']['month'];
            $solarYear = $conv['solar']['year'];
            $lunarMeta = $conv['lunar'];
        } else {
            if (! checkdate($month, $day, $year)) {
                throw new InvalidArgumentException('Ngày sinh Dương lịch không tồn tại.');
            }
            $solarDay = $day;
            $solarMonth = $month;
            $solarYear = $year;
            $lunarMeta = $this->calendar->convertSolarToLunar($solarDay, $solarMonth, $solarYear)['lunar'];
        }

        // 2. Địa điểm sinh & Múi giờ IANA
        $locKey = (string) ($input['location_key'] ?? 'ha-noi');
        $loc = BaziRegistry::BIRTH_LOCATIONS[$locKey] ?? BaziRegistry::BIRTH_LOCATIONS['ha-noi'];
        $tzName = (string) ($input['timezone'] ?? $loc['tz']);
        $longitude = isset($input['longitude']) && is_numeric($input['longitude']) ? (float) $input['longitude'] : (float) $loc['lng'];

        // 3. Độ chính xác giờ sinh (không bao giờ mặc định 12:00 để bịa Trụ Giờ)
        $timeAccuracy = (string) ($input['time_accuracy'] ?? 'exact');
        if (! in_array($timeAccuracy, ['exact', 'approx', 'window', 'session', 'unknown'], true)) {
            $timeAccuracy = 'exact';
        }
        $hasHour = $timeAccuracy !== 'unknown' && isset($input['hour']) && $input['hour'] !== '' && $input['hour'] !== null;
        $rawHour = $hasHour ? max(0, min(23, (int) $input['hour'])) : 12;
        $rawMinute = $hasHour ? max(0, min(59, (int) ($input['minute'] ?? 0))) : 0;
        $rawSecond = $hasHour ? max(0, min(59, (int) ($input['second'] ?? 0))) : 0;

        $civilCarbon = Carbon::create($solarYear, $solarMonth, $solarDay, $rawHour, $rawMinute, $rawSecond, $tzName);
        $utcCarbon = $civilCarbon->copy()->setTimezone('UTC');
        $utcOffsetMinutes = (int) round($civilCarbon->offset / 60);

        // 4. Tính True Solar Time & LMST (luôn minh bạch công thức)
        $solarTimeInfo = $this->computeSolarTime($civilCarbon, $longitude, $utcOffsetMinutes, $method['solar_time_mode']);
        $effectiveLocal = $hasHour
            ? Carbon::parse($solarTimeInfo['effective_iso'], $tzName)
            : $civilCarbon->copy();

        $warnings = [];
        if (! $hasHour) {
            $warnings[] = 'Chưa có giờ sinh cụ thể: hệ thống giữ trống Trụ Giờ (không mặc định 12:00) và chỉ tính 3 trụ Năm – Tháng – Ngày cùng 12 phương án Trụ Giờ khả dĩ.';
        }
        if ($hasHour && $solarTimeInfo['shifted_hour_branch']) {
            $warnings[] = 'Hiệu chỉnh Giờ Mặt Trời làm thay đổi Địa Chi giờ sinh so với giờ dân dụng.';
        }
        if ($hasHour && $solarTimeInfo['shifted_date']) {
            $warnings[] = 'Hiệu chỉnh Giờ Mặt Trời làm dịch chuyển ngày sinh sang ngày liền kề.';
        }

        // 5. So sánh trực tiếp trên thang thời gian tuyệt đối UTC với các mốc Tiết Khí
        $jieMoments = $this->getSurroundingJieMoments($utcCarbon);
        $lapXuanUtc = $this->getLapXuanUtc($solarYear);
        $baziYear = $utcCarbon->lt($lapXuanUtc) ? $solarYear - 1 : $solarYear;

        $prevJie = $jieMoments['prev'];
        $nextJie = $jieMoments['next'];
        $monthBranchIdx = $prevJie['branch_index'];

        // Cảnh báo nếu sinh sát Lập Xuân hoặc sát mốc Tiết trong vòng 24 giờ
        $hoursToLapXuan = abs($utcCarbon->diffInSeconds($lapXuanUtc)) / 3600.0;
        if ($hoursToLapXuan <= 24.0) {
            $warnings[] = 'Thời điểm sinh nằm sát mốc Lập Xuân trong vòng 24 giờ — cần xác nhận chính xác giờ phút sinh vì Trụ Năm đổi ngay tại giây giao Lập Xuân.';
        }

        // 6. Trụ Năm (mốc 1984 = Giáp Tý: index 0)
        $yearStemIdx = (($baziYear - 4) % 10 + 10) % 10;
        $yearBranchIdx = (($baziYear - 4) % 12 + 12) % 12;

        // 7. Trụ Tháng (Ngũ Hổ Độn từ Can Năm Bát Tự)
        $monthStemIdx = $this->computeMonthStemIndex($yearStemIdx, $monthBranchIdx);

        // 8. Trụ Ngày & Xử lý ranh giới Giờ Tý (23:00 vs 00:00)
        $effY = $effectiveLocal->year;
        $effM = $effectiveLocal->month;
        $effD = $effectiveLocal->day;
        $effH = $effectiveLocal->hour;

        $isLateRat = $hasHour && $effH === 23;
        $dayPillarDate = ($isLateRat && $method['day_boundary'] === '23:00')
            ? $effectiveLocal->copy()->addDay()
            : $effectiveLocal->copy();

        $dayJdn = $this->calendar->jdFromDate($dayPillarDate->day, $dayPillarDate->month, $dayPillarDate->year);
        $dayStemIdx = (($dayJdn + 9) % 10 + 10) % 10;
        $dayBranchIdx = (($dayJdn + 1) % 12 + 12) % 12;

        // Nhật Can dùng để lập Trụ Giờ (ở quy tắc 00:00, giờ 23:00–23:59 lấy Can của ngày kế tiếp theo Ngũ Thử Độn)
        $hourCalcDayStemIdx = ($isLateRat && $method['day_boundary'] === '00:00')
            ? ($dayStemIdx + 1) % 10
            : $dayStemIdx;

        // 9. Trụ Giờ (Ngũ Thử Độn)
        $hourPillar = null;
        $possibleHours = [];
        if ($hasHour) {
            $hourBranchIdx = $this->hourToBranchIndex($effH);
            $hourStemIdx = $this->computeHourStemIndex($hourCalcDayStemIdx, $hourBranchIdx);
            $hourPillar = $this->buildPillar('hour', 'Trụ Giờ', $hourStemIdx, $hourBranchIdx, $dayStemIdx);
        } else {
            for ($hb = 0; $hb < 12; $hb++) {
                $hs = $this->computeHourStemIndex($dayStemIdx, $hb);
                $possibleHours[] = $this->buildPillar('hour_candidate', 'Giờ '.BaziRegistry::BRANCHES[$hb]['name'], $hs, $hb, $dayStemIdx);
            }
        }

        $yearPillar = $this->buildPillar('year', 'Trụ Năm', $yearStemIdx, $yearBranchIdx, $dayStemIdx);
        $monthPillar = $this->buildPillar('month', 'Trụ Tháng', $monthStemIdx, $monthBranchIdx, $dayStemIdx);
        $dayPillar = $this->buildPillar('day', 'Trụ Ngày', $dayStemIdx, $dayBranchIdx, $dayStemIdx, isDayMaster: true);

        $pillars = [
            'year' => $yearPillar,
            'month' => $monthPillar,
            'day' => $dayPillar,
            'hour' => $hourPillar,
        ];

        // 10. Ngũ Hành 3 chế độ độc lập
        $elementsAnalysis = $this->analyzeFiveElements($pillars);

        // 11. Nguyệt Lệnh, Thông Căn, Thấu Can & Thân Vượng / Nhược minh bạch
        $dayMaster = BaziRegistry::STEMS[$dayStemIdx];
        $seasonalQi = BaziRegistry::getSeasonalState($dayMaster['element'], $monthBranchIdx);
        $rootAnalysis = $this->analyzeRootsAndTransparency($pillars, $dayStemIdx);
        $strengthAnalysis = $this->analyzeDayMasterStrength($pillars, $dayStemIdx, $seasonalQi, $rootAnalysis, $hasHour);

        // 12. Quan hệ Thiên Can & Địa Chi (Hợp / Xung / Hình / Hại / Phá)
        $relationships = $this->analyzeRelationships($pillars, $monthBranchIdx);

        // 13. Cách Cục (Chart Structure) & Dụng Thần đa phương pháp
        $structure = $this->analyzeChartStructure($pillars, $dayStemIdx, $monthBranchIdx);
        $usefulGods = $this->analyzeUsefulGods($dayMaster, $monthBranchIdx, $strengthAnalysis, $elementsAnalysis, $structure);

        // 14. Thần Sát Bát Tự + Thai Nguyên, Mệnh Cung, Thân Cung Bát Tự
        $symbolicStars = $this->analyzeSymbolicStars($pillars, $yearBranchIdx, $dayStemIdx, $dayBranchIdx);
        $auxiliaryPillars = $this->computeAuxiliaryPillars($monthStemIdx, $monthBranchIdx, $hasHour ? $hourPillar['branch_index'] : null, $yearStemIdx, $dayStemIdx);

        // 15. Đại Vận (Luck Pillars) & Lưu Niên / Lưu Nguyệt hiện tại
        $gender = in_array(($input['gender'] ?? ''), ['male', 'female'], true) ? (string) $input['gender'] : null;
        $luckPillars = $this->computeLuckPillars($utcCarbon, $yearStemIdx, $monthStemIdx, $monthBranchIdx, $dayStemIdx, $gender, $prevJie, $nextJie);

        $targetYear = (int) ($input['target_year'] ?? $this->calendar->todayVn()->year);
        $annualFlow = $this->computeAnnualAndMonthlyFlow($targetYear, $dayStemIdx, $pillars);

        // 16. Thập Thần tổng hợp cho Thập Thần Orbit
        $tenGodsSummary = $this->summarizeTenGods($pillars);

        // 17. Cây Lập Luận (Evidence Graph)
        $evidenceGraph = $this->buildEvidenceGraph($dayMaster, $monthPillar, $seasonalQi, $rootAnalysis, $strengthAnalysis, $relationships, $structure, $usefulGods);

        $calcHash = hash('sha256', json_encode([
            'v' => BaziRegistry::ENGINE_VERSION,
            'm' => $method['id'],
            'y' => $yearPillar['label'],
            'mo' => $monthPillar['label'],
            'd' => $dayPillar['label'],
            'h' => $hourPillar['label'] ?? 'unknown',
            'g' => $gender ?? 'unspecified',
        ]));

        return [
            'engine_version' => BaziRegistry::ENGINE_VERSION,
            'rule_version' => BaziRegistry::RULE_VERSION,
            'calendar_version' => VietnameseCalendarEngine::ENGINE_VERSION,
            'calculation_hash' => $calcHash,
            'calculated_at' => Carbon::now('Asia/Ho_Chi_Minh')->toIso8601String(),
            'method' => $method,
            'warnings' => $warnings,
            'birth' => [
                'name' => trim((string) ($input['name'] ?? '')),
                'gender' => $gender,
                'calendar_type' => $calendarType,
                'solar_label' => sprintf('%02d/%02d/%04d', $solarDay, $solarMonth, $solarYear),
                'solar_iso' => sprintf('%04d-%02d-%02d', $solarYear, $solarMonth, $solarDay),
                'lunar' => $lunarMeta,
                'time_accuracy' => $timeAccuracy,
                'has_hour' => $hasHour,
                'civil_time' => $hasHour ? $civilCarbon->format('H:i:s') : null,
                'utc_iso' => $utcCarbon->toIso8601String(),
                'location_key' => $locKey,
                'location_name' => $loc['name'].', '.$loc['country'],
                'timezone' => $tzName,
                'utc_offset_minutes' => $utcOffsetMinutes,
                'longitude' => $longitude,
                'solar_time' => $solarTimeInfo,
                'lap_xuan_vn' => $lapXuanUtc->copy()->setTimezone($tzName)->format('H:i d/m/Y'),
                'bazi_year_number' => $baziYear,
                'prev_jie' => $prevJie,
                'next_jie' => $nextJie,
            ],
            'pillars' => $pillars,
            'possible_hours' => $possibleHours,
            'day_master' => $dayMaster,
            'elements' => $elementsAnalysis,
            'seasonal_qi' => $seasonalQi,
            'roots' => $rootAnalysis,
            'strength' => $strengthAnalysis,
            'relationships' => $relationships,
            'structure' => $structure,
            'useful_gods' => $usefulGods,
            'ten_gods_summary' => $tenGodsSummary,
            'symbolic_stars' => $symbolicStars,
            'auxiliary_pillars' => $auxiliaryPillars,
            'luck_pillars' => $luckPillars,
            'annual_flow' => $annualFlow,
            'evidence_graph' => $evidenceGraph,
        ];
    }

    /**
     * Ngũ Hổ Độn: Thiên Can tháng từ Thiên Can năm Bát Tự & Địa Chi tháng (Dần=2..Sửu=1).
     */
    public function computeMonthStemIndex(int $yearStemIdx, int $monthBranchIdx): int
    {
        $danStartStem = (2 * ($yearStemIdx % 5) + 2) % 10;
        $offsetFromDan = ($monthBranchIdx - 2 + 12) % 12;

        return ($danStartStem + $offsetFromDan) % 10;
    }

    /**
     * Ngũ Thử Độn: Thiên Can giờ từ Nhật Can & Địa Chi giờ (Tý=0..Hợi=11).
     */
    public function computeHourStemIndex(int $dayStemIdx, int $hourBranchIdx): int
    {
        return (2 * ($dayStemIdx % 5) + $hourBranchIdx) % 10;
    }

    /**
     * Quy đổi giờ 0..23 sang Địa Chi giờ (0 = Tý [23:00–00:59] .. 11 = Hợi [21:00–22:59]).
     */
    public function hourToBranchIndex(int $hour24): int
    {
        $h = (($hour24 % 24) + 24) % 24;

        return (int) floor((($h + 1) % 24) / 2);
    }

    /**
     * Tính Giờ Mặt Trời Trung Bình (LMST) & Giờ Mặt Trời Thực (True Solar Time = Civil + 4*Lng - UTC_offset_min + EoT).
     *
     * @return array<string, mixed>
     */
    public function computeSolarTime(Carbon $civil, float $longitude, int $utcOffsetMinutes, string $mode): array
    {
        // Phương trình thời gian (Equation of Time, phút) theo công thức Spencer / NOAA
        $dayOfYear = $civil->dayOfYear;
        $b = 2.0 * M_PI * ($dayOfYear - 81) / 365.0;
        $eotMinutes = 9.87 * sin(2.0 * $b) - 7.53 * cos($b) - 1.5 * sin($b);

        $longitudeCorrectionMin = 4.0 * $longitude - (float) $utcOffsetMinutes;
        $lmst = $civil->copy()->addSeconds((int) round($longitudeCorrectionMin * 60));
        $trueSolar = $civil->copy()->addSeconds((int) round(($longitudeCorrectionMin + $eotMinutes) * 60));

        $effective = match ($mode) {
            'lmst' => $lmst,
            'true_solar' => $trueSolar,
            default => $civil,
        };

        return [
            'mode' => $mode,
            'civil_label' => $civil->format('H:i:s d/m/Y'),
            'longitude_correction_min' => round($longitudeCorrectionMin, 2),
            'eot_min' => round($eotMinutes, 2),
            'total_correction_min' => round($longitudeCorrectionMin + $eotMinutes, 2),
            'lmst_label' => $lmst->format('H:i:s d/m/Y'),
            'true_solar_label' => $trueSolar->format('H:i:s d/m/Y'),
            'effective_label' => $effective->format('H:i:s d/m/Y'),
            'effective_iso' => $effective->format('Y-m-d H:i:s'),
            'shifted_hour_branch' => $this->hourToBranchIndex($effective->hour) !== $this->hourToBranchIndex($civil->hour),
            'shifted_date' => $effective->format('Y-m-d') !== $civil->format('Y-m-d'),
        ];
    }

    /**
     * Tính Lưu Niên & 12 Lưu Nguyệt (theo 12 mốc Tiết) cho 1 năm bất kỳ — không ghi đè lá số gốc.
     *
     * @param  array<string, mixed>  $pillars
     * @return array<string, mixed>
     */
    public function computeAnnualAndMonthlyFlow(int $year, int $dayStemIdx, array $pillars): array
    {
        $year = max(1900, min(2100, $year));
        $yStem = (($year - 4) % 10 + 10) % 10;
        $yBranch = (($year - 4) % 12 + 12) % 12;
        $annualPillar = $this->buildPillar('annual', "Lưu Niên {$year}", $yStem, $yBranch, $dayStemIdx);

        // Kiểm tra quan hệ Lưu Niên với 4 trụ gốc
        $interactions = [];
        foreach (['year' => 'Trụ Năm', 'month' => 'Trụ Tháng', 'day' => 'Trụ Ngày', 'hour' => 'Trụ Giờ'] as $k => $pLabel) {
            if (empty($pillars[$k])) {
                continue;
            }
            $pb = (int) $pillars[$k]['branch_index'];
            foreach (BaziRegistry::BRANCH_CLASHES as $cl) {
                if (in_array($yBranch, $cl['branches'], true) && in_array($pb, $cl['branches'], true) && $yBranch !== $pb) {
                    $interactions[] = "{$cl['label']} với {$pLabel} (biến động/chuyển dịch cần chủ động thích nghi).";
                }
            }
            foreach (BaziRegistry::BRANCH_SIX_COMBINATIONS as $cb) {
                if (in_array($yBranch, $cb['branches'], true) && in_array($pb, $cb['branches'], true) && $yBranch !== $pb) {
                    $interactions[] = "{$cb['label']} với {$pLabel} (tăng kết nối và hợp tác).";
                }
            }
        }

        // 12 Lưu Nguyệt (Dần -> Sửu theo 12 mốc Tiết)
        $monthlyPillars = [];
        $terms = $this->astronomy->getYearSolarTerms($year);
        $jieDatesByBranch = [];
        foreach ($terms as $t) {
            if (isset(self::JIE_TERM_TO_BRANCH[$t['index']])) {
                $jieDatesByBranch[self::JIE_TERM_TO_BRANCH[$t['index']]] = $t;
            }
        }

        for ($i = 0; $i < 12; $i++) {
            $mb = (2 + $i) % 12;
            $ms = $this->computeMonthStemIndex($yStem, $mb);
            $p = $this->buildPillar('monthly', 'Tháng '.BaziRegistry::BRANCHES[$mb]['name'], $ms, $mb, $dayStemIdx);
            $p['jie_start'] = $jieDatesByBranch[$mb]['datetime_vn'] ?? '';
            $p['jie_name'] = $jieDatesByBranch[$mb]['name'] ?? '';
            $monthlyPillars[] = $p;
        }

        return [
            'year' => $year,
            'lap_xuan_vn' => $this->getLapXuanUtc($year)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y'),
            'annual_pillar' => $annualPillar,
            'interactions' => $interactions,
            'monthly_pillars' => $monthlyPillars,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildPillar(string $key, string $title, int $stemIdx, int $branchIdx, int $dayStemIdx, bool $isDayMaster = false): array
    {
        $stem = BaziRegistry::STEMS[$stemIdx];
        $branch = BaziRegistry::BRANCHES[$branchIdx];
        $label = $stem['name'].' '.$branch['name'];
        $tenGod = $isDayMaster
            ? ['code' => 'nhat_chu', 'name' => 'Nhật Chủ', 'short' => 'Nhật Chủ', 'hanzi' => '日主', 'group' => 'Bản Mệnh', 'desc' => 'Điểm tham chiếu trung tâm của toàn bộ lá số Bát Tự.']
            : BaziRegistry::getTenGod($dayStemIdx, $stemIdx);

        $hidden = [];
        foreach (BaziRegistry::HIDDEN_STEMS[$branchIdx] as $hs) {
            $hStem = BaziRegistry::STEMS[$hs['stem_index']];
            $hidden[] = [
                'stem_index' => $hs['stem_index'],
                'name' => $hStem['name'],
                'hanzi' => $hStem['hanzi'],
                'element' => $hStem['element'],
                'yin_yang' => $hStem['yin_yang'],
                'role' => $hs['role'],
                'role_label' => $hs['role_label'],
                'ten_god' => BaziRegistry::getTenGod($dayStemIdx, $hs['stem_index']),
            ];
        }

        return [
            'key' => $key,
            'title' => $title,
            'stem_index' => $stemIdx,
            'branch_index' => $branchIdx,
            'stem' => $stem,
            'branch' => $branch,
            'label' => $label,
            'hanzi' => $stem['hanzi'].$branch['hanzi'],
            'ten_god' => $tenGod,
            'hidden_stems' => $hidden,
            'nap_am' => $this->calendar->getNapAm($label),
            'life_stage' => BaziRegistry::getLifeStage($dayStemIdx, $branchIdx),
            'kong_wang' => BaziRegistry::getKongWang($stemIdx, $branchIdx),
        ];
    }

    /**
     * Lấy thời điểm Lập Xuân (315°) của 1 năm Dương lịch ở UTC.
     */
    public function getLapXuanUtc(int $year): Carbon
    {
        foreach ($this->astronomy->getYearSolarTerms($year) as $t) {
            if ($t['index'] === 0) {
                return Carbon::createFromFormat('Y-m-d H:i', $t['date_iso'].' '.$t['time_vn'], 'Asia/Ho_Chi_Minh')->setTimezone('UTC');
            }
        }

        return Carbon::create($year, 2, 4, 4, 0, 0, 'UTC');
    }

    /**
     * Tìm chính xác mốc Tiết (Jie) liền trước và liền sau thời điểm sinh UTC.
     *
     * @return array{prev: array<string, mixed>, next: array<string, mixed>}
     */
    protected function getSurroundingJieMoments(Carbon $utcMoment): array
    {
        $year = $utcMoment->year;
        $candidates = [];

        foreach ([$year - 1, $year, $year + 1] as $y) {
            if ($y < 1899 || $y > 2101) {
                continue;
            }
            foreach ($this->astronomy->getYearSolarTerms($y) as $t) {
                if (! isset(self::JIE_TERM_TO_BRANCH[$t['index']])) {
                    continue;
                }
                $cUtc = Carbon::createFromFormat('Y-m-d H:i', $t['date_iso'].' '.$t['time_vn'], 'Asia/Ho_Chi_Minh')->setTimezone('UTC');
                $candidates[] = [
                    'name' => $t['name'],
                    'index' => $t['index'],
                    'branch_index' => self::JIE_TERM_TO_BRANCH[$t['index']],
                    'branch_name' => BaziRegistry::BRANCHES[self::JIE_TERM_TO_BRANCH[$t['index']]]['name'],
                    'datetime_vn' => $t['datetime_vn'],
                    'utc_ts' => $cUtc->timestamp,
                    'utc_iso' => $cUtc->toIso8601String(),
                ];
            }
        }

        usort($candidates, fn (array $a, array $b): int => $a['utc_ts'] <=> $b['utc_ts']);

        $prev = $candidates[0];
        $next = $candidates[count($candidates) - 1];
        $ts = $utcMoment->timestamp;

        foreach ($candidates as $i => $c) {
            if ($c['utc_ts'] <= $ts) {
                $prev = $c;
                $next = $candidates[$i + 1] ?? $c;
            } else {
                $next = $c;
                break;
            }
        }

        return ['prev' => $prev, 'next' => $next];
    }

    /**
     * Phân tích Ngũ Hành theo 3 chế độ độc lập (không trộn chung một biểu đồ).
     *
     * @param  array<string, mixed>  $pillars
     * @return array<string, mixed>
     */
    protected function analyzeFiveElements(array $pillars): array
    {
        $eightChars = ['Mộc' => 0, 'Hỏa' => 0, 'Thổ' => 0, 'Kim' => 0, 'Thủy' => 0];
        $hiddenIncluded = ['Mộc' => 0.0, 'Hỏa' => 0.0, 'Thổ' => 0.0, 'Kim' => 0.0, 'Thủy' => 0.0];
        $seasonalWeighted = ['Mộc' => 0.0, 'Hỏa' => 0.0, 'Thổ' => 0.0, 'Kim' => 0.0, 'Thủy' => 0.0];

        $roleWeight = ['ban_khi' => 1.0, 'trung_khi' => 0.5, 'du_khi' => 0.3];

        foreach ($pillars as $k => $p) {
            if (! $p) {
                continue;
            }
            $sEl = $p['stem']['element'];
            $bEl = $p['branch']['element'];

            $eightChars[$sEl]++;
            $eightChars[$bEl]++;

            $hiddenIncluded[$sEl] += 1.0;
            $pillarMult = $k === 'month' ? 2.2 : ($k === 'day' ? 1.4 : 1.0);
            $seasonalWeighted[$sEl] += 1.0 * ($k === 'day' ? 1.0 : 1.0);

            foreach ($p['hidden_stems'] as $hs) {
                $w = $roleWeight[$hs['role']] ?? 0.3;
                $hiddenIncluded[$hs['element']] += $w;
                $seasonalWeighted[$hs['element']] += $w * $pillarMult;
            }
        }

        $total8 = max(1, array_sum($eightChars));
        $totalHidden = max(0.01, array_sum($hiddenIncluded));
        $totalWeighted = max(0.01, array_sum($seasonalWeighted));

        $missing = [];
        foreach ($eightChars as $el => $cnt) {
            if ($cnt === 0 && $hiddenIncluded[$el] == 0.0) {
                $missing[] = $el;
            }
        }

        return [
            'eight_chars' => [
                'label' => 'Phân bố 8 chữ (Can + Ngũ Hành chính Địa Chi)',
                'counts' => $eightChars,
                'percentages' => array_map(fn ($v) => round($v / $total8 * 100, 1), $eightChars),
            ],
            'with_hidden' => [
                'label' => 'Phân bố mở rộng Tàng Can (quy ước Bản Khí 1.0 · Trung Khí 0.5 · Dư Khí 0.3)',
                'counts' => array_map(fn ($v) => round($v, 2), $hiddenIncluded),
                'percentages' => array_map(fn ($v) => round($v / $totalHidden * 100, 1), $hiddenIncluded),
            ],
            'seasonal_qi' => [
                'label' => 'Khí thế theo Nguyệt Lệnh (nhân hệ số Nguyệt Lệnh ×2.2, Nhật Chi ×1.4)',
                'counts' => array_map(fn ($v) => round($v, 2), $seasonalWeighted),
                'percentages' => array_map(fn ($v) => round($v / $totalWeighted * 100, 1), $seasonalWeighted),
            ],
            'missing_elements' => $missing,
            'disclaimer' => 'Tỷ lệ phần trăm là chỉ số đếm/trọng số quy ước theo phiên bản tu_binh_mayhen_v1.0, không phải phép đo năng lượng vật lý.',
        ];
    }

    /**
     * Phân tích Thông Căn & Thấu Can của Nhật Chủ.
     *
     * @param  array<string, mixed>  $pillars
     * @return array<string, mixed>
     */
    protected function analyzeRootsAndTransparency(array $pillars, int $dayStemIdx): array
    {
        $dayEl = BaziRegistry::STEMS[$dayStemIdx]['element'];
        $roots = [];
        $supportingStems = [];

        foreach ($pillars as $k => $p) {
            if (! $p) {
                continue;
            }
            if ($k !== 'day') {
                $sEl = $p['stem']['element'];
                if ($sEl === $dayEl || BaziRegistry::FIVE_ELEMENTS[$sEl]['generates'] === $dayEl) {
                    $supportingStems[] = [
                        'pillar' => $p['title'],
                        'stem' => $p['stem']['name'],
                        'ten_god' => $p['ten_god']['name'],
                    ];
                }
            }
            foreach ($p['hidden_stems'] as $hs) {
                if ($hs['element'] === $dayEl) {
                    $roots[] = [
                        'pillar' => $p['title'],
                        'branch' => $p['branch']['name'],
                        'hidden_stem' => $hs['name'],
                        'role' => $hs['role_label'],
                    ];
                }
            }
        }

        return [
            'has_root' => $roots !== [],
            'root_count' => count($roots),
            'roots' => $roots,
            'supporting_stems' => $supportingStems,
        ];
    }

    /**
     * Đánh giá Thân Vượng / Thân Nhược minh bạch công thức & trọng số (không ép mọi lá số vào 2 cực).
     *
     * @param  array<string, mixed>  $pillars
     * @param  array<string, mixed>  $seasonalQi
     * @param  array<string, mixed>  $roots
     * @return array<string, mixed>
     */
    protected function analyzeDayMasterStrength(array $pillars, int $dayStemIdx, array $seasonalQi, array $roots, bool $hasHour): array
    {
        $dayEl = BaziRegistry::STEMS[$dayStemIdx]['element'];
        $elMeta = BaziRegistry::FIVE_ELEMENTS[$dayEl];
        $supportEls = [$dayEl, $elMeta['generated_by']];

        $supportScore = $seasonalQi['is_dac_lenh'] ? 35.0 : 0.0;
        $drainScore = $seasonalQi['is_dac_lenh'] ? 0.0 : 35.0;

        foreach ($pillars as $k => $p) {
            if (! $p) {
                continue;
            }
            if ($k !== 'day') {
                $sWeight = $k === 'month' ? 12.0 : 9.0;
                if (in_array($p['stem']['element'], $supportEls, true)) {
                    $supportScore += $sWeight;
                } else {
                    $drainScore += $sWeight;
                }
            }
            $bWeight = $k === 'month' ? 15.0 : ($k === 'day' ? 14.0 : 10.0);
            if (in_array($p['branch']['element'], $supportEls, true)) {
                $supportScore += $bWeight;
            } else {
                $drainScore += $bWeight;
            }
        }

        $total = max(1.0, $supportScore + $drainScore);
        $supportRatio = round($supportScore / $total * 100, 1);

        if (! $hasHour && $supportRatio >= 40.0 && $supportRatio <= 60.0) {
            $verdict = 'uncertain_missing_hour';
            $label = 'Chưa đủ căn cứ chốt (Thiếu Trụ Giờ)';
        } elseif ($supportRatio >= 58.0) {
            $verdict = 'strong';
            $label = 'Thiên về Thân Vượng (theo phương pháp Phù Ức)';
        } elseif ($supportRatio <= 42.0) {
            $verdict = 'weak';
            $label = 'Thiên về Thân Nhược (theo phương pháp Phù Ức)';
        } else {
            $verdict = 'balanced';
            $label = 'Tương đối Cân Bằng (Trung Hòa)';
        }

        return [
            'verdict' => $verdict,
            'label' => $label,
            'support_score' => round($supportScore, 1),
            'drain_score' => round($drainScore, 1),
            'support_ratio' => $supportRatio,
            'formula_note' => 'Quy ước tu_binh_mayhen_v1.0: Nguyệt Lệnh 35đ + Can lộ (Tháng 12đ, Năm/Giờ 9đ) + Chi (Tháng 15đ, Ngày 14đ, Năm/Giờ 10đ). Đây là mô hình tham khảo, không phải chuẩn tuyệt đối duy nhất của mọi trường phái.',
        ];
    }

    /**
     * Phân tích Quan hệ Thiên Can (Ngũ Hợp) và Địa Chi (Lục Hợp, Tam Hợp, Tam Hội, Lục Xung, Lục Hại, Lục Phá, Tự Hình).
     * Phân biệt rõ: Có quan hệ hợp vs Đủ điều kiện hóa khí.
     *
     * @param  array<string, mixed>  $pillars
     * @return array<string, mixed>
     */
    protected function analyzeRelationships(array $pillars, int $monthBranchIdx): array
    {
        $activePillars = array_values(array_filter($pillars));
        $monthEl = BaziRegistry::BRANCHES[$monthBranchIdx]['element'];

        $stemRels = [];
        $branchRels = [];

        $n = count($activePillars);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $p1 = $activePillars[$i];
                $p2 = $activePillars[$j];

                // Thiên Can Ngũ Hợp
                foreach (BaziRegistry::STEM_COMBINATIONS as $sc) {
                    if (
                        ($p1['stem_index'] === $sc['stems'][0] && $p2['stem_index'] === $sc['stems'][1]) ||
                        ($p1['stem_index'] === $sc['stems'][1] && $p2['stem_index'] === $sc['stems'][0])
                    ) {
                        $adjacent = abs($i - $j) === 1;
                        $canTransform = $adjacent && ($monthEl === $sc['target_element']);
                        $stemRels[] = [
                            'rule_id' => $sc['rule_id'],
                            'type' => 'ngu_hop',
                            'label' => $sc['label'],
                            'pillars' => [$p1['title'], $p2['title']],
                            'transformed' => $canTransform,
                            'status_note' => $canTransform
                                ? "Đứng cạnh nhau và được Nguyệt Lệnh hành {$sc['target_element']} trợ lực (có điều kiện hóa khí)."
                                : 'Có quan hệ hợp nhưng chưa đủ điều kiện hóa hoàn toàn (hợp nhi bất hóa).',
                        ];
                    }
                }

                // Địa Chi Lục Hợp
                foreach (BaziRegistry::BRANCH_SIX_COMBINATIONS as $bc) {
                    if (
                        ($p1['branch_index'] === $bc['branches'][0] && $p2['branch_index'] === $bc['branches'][1]) ||
                        ($p1['branch_index'] === $bc['branches'][1] && $p2['branch_index'] === $bc['branches'][0])
                    ) {
                        $branchRels[] = [
                            'rule_id' => $bc['rule_id'],
                            'type' => 'luc_hop',
                            'label' => $bc['label'],
                            'pillars' => [$p1['title'], $p2['title']],
                            'status_note' => 'Lục Hợp giúp kết nối khí giữa hai trụ; việc hóa khí còn tùy Nguyệt Lệnh và Can dẫn.',
                        ];
                    }
                }

                // Lục Xung
                foreach (BaziRegistry::BRANCH_CLASHES as $cl) {
                    if (
                        ($p1['branch_index'] === $cl['branches'][0] && $p2['branch_index'] === $cl['branches'][1]) ||
                        ($p1['branch_index'] === $cl['branches'][1] && $p2['branch_index'] === $cl['branches'][0])
                    ) {
                        $branchRels[] = [
                            'rule_id' => $cl['rule_id'],
                            'type' => 'luc_xung',
                            'label' => $cl['label'],
                            'pillars' => [$p1['title'], $p2['title']],
                            'status_note' => 'Lục Xung biểu thị sự dịch chuyển, thay đổi hoặc lực đối lập cần điều hòa — không đồng nghĩa chia ly hay tai họa.',
                        ];
                    }
                }

                // Lục Hại
                foreach (BaziRegistry::BRANCH_HARMS as $hm) {
                    if (
                        ($p1['branch_index'] === $hm['branches'][0] && $p2['branch_index'] === $hm['branches'][1]) ||
                        ($p1['branch_index'] === $hm['branches'][1] && $p2['branch_index'] === $hm['branches'][0])
                    ) {
                        $branchRels[] = [
                            'rule_id' => $hm['rule_id'],
                            'type' => 'luc_hai',
                            'label' => $hm['label'],
                            'pillars' => [$p1['title'], $p2['title']],
                            'status_note' => 'Lục Hại nhắc sự tinh tế trong kỳ vọng và giao tiếp giữa các mối quan hệ.',
                        ];
                    }
                }

                // Tự Hình (Thìn-Thìn, Ngọ-Ngọ, Dậu-Dậu, Hợi-Hợi)
                if ($p1['branch_index'] === $p2['branch_index'] && in_array($p1['branch_index'], [4, 6, 9, 11], true)) {
                    $bName = BaziRegistry::BRANCHES[$p1['branch_index']]['name'];
                    $branchRels[] = [
                        'rule_id' => 'br_self_punish_'.$p1['branch_index'],
                        'type' => 'tu_hinh',
                        'label' => "{$bName} – {$bName} Tự Hình",
                        'pillars' => [$p1['title'], $p2['title']],
                        'status_note' => 'Tự Hình gợi nhắc tránh tự tạo áp lực quá mức hoặc cầu toàn cực đoan.',
                    ];
                }
            }
        }

        // Tam Hợp & Tam Hội (đủ bộ 3 Chi)
        $presentBranches = array_unique(array_map(fn ($p) => (int) $p['branch_index'], $activePillars));
        foreach (BaziRegistry::BRANCH_THREE_HARMONIES as $th) {
            if (count(array_intersect($th['branches'], $presentBranches)) === 3) {
                $branchRels[] = [
                    'rule_id' => $th['rule_id'],
                    'type' => 'tam_hop',
                    'label' => $th['label'],
                    'pillars' => ['Toàn cục'],
                    'status_note' => "Đủ bộ Tam Hợp hướng về hành {$th['target_element']}.",
                ];
            }
        }
        foreach (BaziRegistry::BRANCH_SEASONAL_MEETINGS as $sm) {
            if (count(array_intersect($sm['branches'], $presentBranches)) === 3) {
                $branchRels[] = [
                    'rule_id' => $sm['rule_id'],
                    'type' => 'tam_hoi',
                    'label' => $sm['label'],
                    'pillars' => ['Toàn cục'],
                    'status_note' => "Đủ bộ Tam Hội tập trung khí phương vị hành {$sm['target_element']}.",
                ];
            }
        }

        return [
            'stems' => $stemRels,
            'branches' => $branchRels,
        ];
    }

    /**
     * Xác định Cách Cục theo Tử Bình Chân Thuyên (Nguyệt Lệnh + Thấu Can).
     *
     * @param  array<string, mixed>  $pillars
     * @return array<string, mixed>
     */
    protected function analyzeChartStructure(array $pillars, int $dayStemIdx, int $monthBranchIdx): array
    {
        $monthHidden = BaziRegistry::HIDDEN_STEMS[$monthBranchIdx];
        $exposedStems = [];
        foreach (['year', 'month', 'hour'] as $k) {
            if (! empty($pillars[$k])) {
                $exposedStems[] = (int) $pillars[$k]['stem_index'];
            }
        }

        $chosenHidden = $monthHidden[0];
        $transparent = false;
        foreach ($monthHidden as $hs) {
            if (in_array($hs['stem_index'], $exposedStems, true)) {
                $chosenHidden = $hs;
                $transparent = true;
                break;
            }
        }

        $tg = BaziRegistry::getTenGod($dayStemIdx, $chosenHidden['stem_index']);
        if ($tg['code'] === 'ty_kien') {
            $structureName = 'Kiến Lộc Cách';
        } elseif ($tg['code'] === 'kiep_tai') {
            $structureName = 'Nguyệt Kiếp / Dương Nhẫn Cách';
        } else {
            $structureName = $tg['name'].' Cách';
        }

        return [
            'name' => $structureName,
            'anchor_ten_god' => $tg['name'],
            'anchor_hidden_stem' => BaziRegistry::STEMS[$chosenHidden['stem_index']]['name'],
            'is_transparent' => $transparent,
            'source' => 'Tử Bình Chân Thuyên (Thẩm Hiếu Chiêm) — Định Cách theo Nguyệt Lệnh & Thấu Can',
            'note' => $transparent
                ? "Tàng Can {$chosenHidden['role_label']} ({$this->stemName($chosenHidden['stem_index'])}) trong Nguyệt Lệnh thấu lộ lên Thiên Can, lập thành {$structureName}."
                : "Tàng Can trong Nguyệt Lệnh chưa thấu lộ rõ trên Thiên Can; tạm lấy Bản Khí Nguyệt Lệnh ({$this->stemName($chosenHidden['stem_index'])}) để định hướng {$structureName}.",
        ];
    }

    /**
     * Phân tích Dụng Thần đa phương pháp độc lập (Phù Ức, Điều Hậu, Thông Quan, Cách Cục).
     *
     * @param  array<string, mixed>  $dayMaster
     * @param  array<string, mixed>  $strength
     * @param  array<string, mixed>  $elements
     * @param  array<string, mixed>  $structure
     * @return array<string, mixed>
     */
    protected function analyzeUsefulGods(array $dayMaster, int $monthBranchIdx, array $strength, array $elements, array $structure): array
    {
        $el = $dayMaster['element'];
        $meta = BaziRegistry::FIVE_ELEMENTS[$el];
        $season = BaziRegistry::BRANCHES[$monthBranchIdx]['season'];

        // A. Phù Ức
        if ($strength['verdict'] === 'strong') {
            $phuUc = [
                'method' => 'Phù Ức (Cân bằng Vượng – Nhược)',
                'suggested_elements' => [$meta['generates'], $meta['controls'], $meta['controlled_by']],
                'note' => "Nhật Chủ thiên vượng: theo Phù Ức ưu tiên tiết tú ({$meta['generates']}), hao khí ({$meta['controls']}) hoặc chế ước ({$meta['controlled_by']}) để lá số thanh thoáng.",
            ];
        } elseif ($strength['verdict'] === 'weak') {
            $phuUc = [
                'method' => 'Phù Ức (Cân bằng Vượng – Nhược)',
                'suggested_elements' => [$meta['generated_by'], $el],
                'note' => "Nhật Chủ thiên nhược: theo Phù Ức ưu tiên sinh trợ ({$meta['generated_by']}) và đồng hành ({$el}) để củng cố nội lực.",
            ];
        } else {
            $phuUc = [
                'method' => 'Phù Ức (Cân bằng Vượng – Nhược)',
                'suggested_elements' => [],
                'note' => 'Nhật Chủ tương đối cân bằng hoặc thiếu giờ sinh nên không ép chọn 1 hành Phù Ức duy nhất; cần xét thêm Điều Hậu và Đại Vận.',
            ];
        }

        // B. Điều Hậu (Cùng Thông Bảo Giám)
        $dieuHauEls = match (true) {
            str_contains($season, 'Đông') => ['Hỏa', 'Mộc'],
            str_contains($season, 'Hạ') => ['Thủy', 'Kim'],
            default => ['Thủy', 'Hỏa'],
        };
        $dieuHauNote = match (true) {
            str_contains($season, 'Đông') => 'Sinh mùa Đông khí hàn lạnh: theo Cùng Thông Bảo Giám ưu tiên Hỏa để sưởi ấm (Điều Hậu), Mộc trợ Hỏa.',
            str_contains($season, 'Hạ') => 'Sinh mùa Hạ khí nóng táo: theo Cùng Thông Bảo Giám ưu tiên Thủy để nhuận trạch, Kim sinh Thủy.',
            default => 'Sinh mùa Xuân/Thu khí hậu ôn hòa hơn: Điều Hậu xét cân bằng giữa Thủy và Hỏa tùy cục diện khô/ẩm thực tế.',
        };

        return [
            'phu_uc' => $phuUc,
            'dieu_hau' => [
                'method' => 'Điều Hậu (Cùng Thông Bảo Giám — Hàn/Nhiệt/Táo/Thấp)',
                'suggested_elements' => $dieuHauEls,
                'note' => $dieuHauNote,
            ],
            'cach_cuc' => [
                'method' => 'Dụng Thần Cách Cục (Tử Bình Chân Thuyên)',
                'suggested_elements' => [],
                'note' => "Định điểm tựa theo {$structure['name']}: ưu tiên yếu tố giúp Thành Cách và giữ thanh khí cho Thập Thần trọng tâm ({$structure['anchor_ten_god']}).",
            ],
            'disclaimer' => 'Các trường phái Dụng Thần (Phù Ức, Điều Hậu, Cách Cục) giải quyết các mục tiêu khác nhau và có thể đề xuất Ngũ Hành khác nhau — Mây Hẹn trình bày độc lập để bạn đối chiếu minh bạch.',
        ];
    }

    /**
     * Thần Sát Bát Tự có nguồn (Thiên Ất Quý Nhân, Văn Xương, Đào Hoa, Dịch Mã, Hoa Cái, Lộc Thần, Dương Nhẫn).
     *
     * @param  array<string, mixed>  $pillars
     * @return list<array{name: string, branch: string, pillar: string, source: string, desc: string}>
     */
    protected function analyzeSymbolicStars(array $pillars, int $yearBranchIdx, int $dayStemIdx, int $dayBranchIdx): array
    {
        $stars = [];
        $thienAtMap = [0 => [1, 7], 4 => [1, 7], 6 => [1, 7], 1 => [0, 8], 5 => [0, 8], 2 => [11, 9], 3 => [11, 9], 7 => [2, 6], 8 => [3, 5], 9 => [3, 5]];
        $vanXuongMap = [0 => 5, 1 => 6, 2 => 8, 3 => 9, 4 => 8, 5 => 9, 6 => 11, 7 => 0, 8 => 2, 9 => 3];
        $locThanMap = [0 => 2, 1 => 3, 2 => 5, 3 => 6, 4 => 5, 5 => 6, 6 => 8, 7 => 9, 8 => 11, 9 => 0];
        $daoHoaByTri = [8 => 9, 0 => 9, 4 => 9, 2 => 3, 6 => 3, 10 => 3, 5 => 6, 9 => 6, 1 => 6, 11 => 0, 3 => 0, 7 => 0];
        $dichMaByTri = [8 => 2, 0 => 2, 4 => 2, 2 => 8, 6 => 8, 10 => 8, 5 => 11, 9 => 11, 1 => 11, 11 => 5, 3 => 5, 7 => 5];

        foreach ($pillars as $p) {
            if (! $p) {
                continue;
            }
            $b = (int) $p['branch_index'];
            if (in_array($b, $thienAtMap[$dayStemIdx] ?? [], true)) {
                $stars[] = ['name' => 'Thiên Ất Quý Nhân', 'branch' => $p['branch']['name'], 'pillar' => $p['title'], 'source' => 'Tam Mệnh Thông Hội (theo Nhật Can)', 'desc' => 'Biểu tượng của sự tương trợ, dễ gặp người đồng hành thiện chí khi cần.'];
            }
            if (($vanXuongMap[$dayStemIdx] ?? -1) === $b) {
                $stars[] = ['name' => 'Văn Xương Quý Nhân', 'branch' => $p['branch']['name'], 'pillar' => $p['title'], 'source' => 'Tam Mệnh Thông Hội (theo Nhật Can)', 'desc' => 'Biểu trưng cho khả năng học hỏi, diễn đạt và cảm thụ văn chương, tri thức.'];
            }
            if (($locThanMap[$dayStemIdx] ?? -1) === $b) {
                $stars[] = ['name' => 'Lộc Thần', 'branch' => $p['branch']['name'], 'pillar' => $p['title'], 'source' => 'Uyên Hải Tử Bình (theo Nhật Can)', 'desc' => 'Biểu trưng cho nền tảng tự lực, năng lực tạo giá trị và ổn định cuộc sống.'];
            }
            if (($daoHoaByTri[$dayBranchIdx] ?? -1) === $b || ($daoHoaByTri[$yearBranchIdx] ?? -1) === $b) {
                $stars[] = ['name' => 'Đào Hoa (Hàm Trì)', 'branch' => $p['branch']['name'], 'pillar' => $p['title'], 'source' => 'Tam Mệnh Thông Hội (theo Chi Năm/Ngày)', 'desc' => 'Biểu trưng cho duyên giao tiếp, sức hút thẩm mỹ và sự tinh tế trong nghệ thuật (không phải dấu hiệu ngoại tình).'];
            }
            if (($dichMaByTri[$dayBranchIdx] ?? -1) === $b || ($dichMaByTri[$yearBranchIdx] ?? -1) === $b) {
                $stars[] = ['name' => 'Dịch Mã', 'branch' => $p['branch']['name'], 'pillar' => $p['title'], 'source' => 'Tam Mệnh Thông Hội (theo Chi Năm/Ngày)', 'desc' => 'Biểu trưng cho sự năng động, dịch chuyển, đi xa học hỏi hoặc mở rộng công việc.'];
            }
        }

        return $stars;
    }

    /**
     * Tính Thai Nguyên, Mệnh Cung & Thân Cung Bát Tự (phân biệt hoàn toàn với Tử Vi Đẩu Số).
     *
     * @return array<string, mixed>
     */
    protected function computeAuxiliaryPillars(int $monthStemIdx, int $monthBranchIdx, ?int $hourBranchIdx, int $yearStemIdx, int $dayStemIdx): array
    {
        $thaiStem = ($monthStemIdx + 1) % 10;
        $thaiBranch = ($monthBranchIdx + 3) % 12;
        $thaiNguyen = $this->buildPillar('thai_nguyen', 'Thai Nguyên', $thaiStem, $thaiBranch, $dayStemIdx);

        $menhCung = null;
        $thanCung = null;
        if ($hourBranchIdx !== null) {
            // Công thức truyền thống Tam Mệnh Thông Hội: Mệnh Cung từ số tháng (Dần=1..Sửu=12) + số giờ (Dần=1..Sửu=12)
            $mNum = ($monthBranchIdx - 2 + 12) % 12 + 1;
            $hNum = ($hourBranchIdx - 2 + 12) % 12 + 1;
            $sum = $mNum + $hNum;
            $menhNum = $sum < 14 ? (14 - $sum) : (26 - $sum);
            $menhBranch = ($menhNum + 1) % 12;
            $menhStem = $this->computeMonthStemIndex($yearStemIdx, $menhBranch);
            $menhCung = $this->buildPillar('menh_cung', 'Mệnh Cung Bát Tự', $menhStem, $menhBranch, $dayStemIdx);

            $thanBranch = (($mNum + $hNum) % 12 + 1) % 12;
            $thanStem = $this->computeMonthStemIndex($yearStemIdx, $thanBranch);
            $thanCung = $this->buildPillar('than_cung', 'Thân Cung Bát Tự', $thanStem, $thanBranch, $dayStemIdx);
        }

        return [
            'thai_nguyen' => $thaiNguyen,
            'menh_cung' => $menhCung,
            'than_cung' => $thanCung,
        ];
    }

    /**
     * Tính Đại Vận (10 năm/chu kỳ) theo quy tắc Dương Nam/Âm Nữ thuận, Âm Nam/Dương Nữ nghịch (căn cứ Can Năm).
     * Khởi vận: 3 ngày = 1 năm, 1 ngày = 4 tháng, 1 giờ = 5 ngày.
     *
     * @param  array<string, mixed>  $prevJie
     * @param  array<string, mixed>  $nextJie
     * @return array<string, mixed>
     */
    protected function computeLuckPillars(
        Carbon $birthUtc,
        int $yearStemIdx,
        int $monthStemIdx,
        int $monthBranchIdx,
        int $dayStemIdx,
        ?string $gender,
        array $prevJie,
        array $nextJie
    ): array {
        $isYangYear = BaziRegistry::STEMS[$yearStemIdx]['yin_yang'] === 'Dương';

        $buildSequence = function (bool $isForward) use ($birthUtc, $monthStemIdx, $monthBranchIdx, $dayStemIdx, $prevJie, $nextJie): array {
            $targetTs = $isForward ? (int) $nextJie['utc_ts'] : (int) $prevJie['utc_ts'];
            $diffSeconds = abs($targetTs - $birthUtc->timestamp);
            $diffDays = $diffSeconds / 86400.0;

            // Quy đổi 3 ngày = 1 năm (12 tháng = 360 ngày ước tính)
            $startYearsFloat = $diffDays / 3.0;
            $startYears = (int) floor($startYearsFloat);
            $remMonthsFloat = ($startYearsFloat - $startYears) * 12.0;
            $startMonths = (int) floor($remMonthsFloat);
            $startDays = (int) round(($remMonthsFloat - $startMonths) * 30.0);

            $startMoment = $birthUtc->copy()->addYears($startYears)->addMonths($startMonths)->addDays($startDays);
            $dir = $isForward ? 1 : -1;

            $cycles = [];
            for ($i = 1; $i <= 8; $i++) {
                $s = (($monthStemIdx + $dir * $i) % 10 + 10) % 10;
                $b = (($monthBranchIdx + $dir * $i) % 12 + 12) % 12;
                $p = $this->buildPillar("luck_{$i}", "Đại Vận {$i}", $s, $b, $dayStemIdx);
                $fromYear = $startMoment->year + ($i - 1) * 10;
                $toYear = $fromYear + 9;
                $ageStart = $startYears + ($i - 1) * 10;
                $cycles[] = [
                    'index' => $i,
                    'pillar' => $p,
                    'start_year' => $fromYear,
                    'end_year' => $toYear,
                    'age_start' => $ageStart,
                    'age_end' => $ageStart + 9,
                ];
            }

            return [
                'direction' => $isForward ? 'forward' : 'backward',
                'direction_label' => $isForward ? 'Thuận hành (tiến từ Trụ Tháng)' : 'Nghịch hành (lùi từ Trụ Tháng)',
                'reference_jie' => $isForward ? $nextJie['name'].' ('.$nextJie['datetime_vn'].')' : $prevJie['name'].' ('.$prevJie['datetime_vn'].')',
                'distance_days' => round($diffDays, 2),
                'start_age_label' => sprintf('%d tuổi %d tháng %d ngày', $startYears, $startMonths, $startDays),
                'start_date_iso' => $startMoment->format('Y-m-d'),
                'cycles' => $cycles,
            ];
        };

        if ($gender === null) {
            return [
                'gender_specified' => false,
                'note' => 'Bạn chưa chọn phân loại Nam/Nữ nên hệ thống hiển thị song song cả hai chuỗi Đại Vận Thuận và Nghịch để nghiên cứu.',
                'primary' => $buildSequence(true),
                'alternate' => $buildSequence(false),
            ];
        }

        $isForward = ($isYangYear && $gender === 'male') || (! $isYangYear && $gender === 'female');

        return [
            'gender_specified' => true,
            'note' => sprintf(
                'Năm sinh %s (%s) + %s → %s theo quy ước Tử Bình truyền thống.',
                BaziRegistry::STEMS[$yearStemIdx]['name'],
                $isYangYear ? 'Dương Can' : 'Âm Can',
                $gender === 'male' ? 'Nam' : 'Nữ',
                $isForward ? 'Thuận hành' : 'Nghịch hành'
            ),
            'primary' => $buildSequence($isForward),
            'alternate' => null,
        ];
    }

    /**
     * Tổng hợp 10 Thập Thần phục vụ Thập Thần Orbit (phân biệt có mặt vs thư viện tham khảo).
     *
     * @param  array<string, mixed>  $pillars
     * @return list<array<string, mixed>>
     */
    protected function summarizeTenGods(array $pillars): array
    {
        $map = [];
        foreach (BaziRegistry::TEN_GODS as $code => $tg) {
            $map[$code] = array_merge($tg, [
                'present' => false,
                'exposed_count' => 0,
                'hidden_count' => 0,
                'locations' => [],
            ]);
        }

        foreach (['year', 'month', 'day', 'hour'] as $k) {
            $p = $pillars[$k] ?? null;
            if (! $p) {
                continue;
            }
            if ($k !== 'day' && isset($map[$p['ten_god']['code']])) {
                $code = $p['ten_god']['code'];
                $map[$code]['present'] = true;
                $map[$code]['exposed_count']++;
                $map[$code]['locations'][] = $p['title'].' (Can lộ: '.$p['stem']['name'].')';
            }
            foreach ($p['hidden_stems'] as $hs) {
                $code = $hs['ten_god']['code'];
                if (isset($map[$code])) {
                    $map[$code]['present'] = true;
                    $map[$code]['hidden_count']++;
                    $map[$code]['locations'][] = $p['title'].' (Tàng Can: '.$hs['name'].')';
                }
            }
        }

        return array_values($map);
    }

    /**
     * Xây dựng Cây Lập Luận (Evidence Graph) có Rule ID & Source để người dùng bấm "Xem căn cứ".
     *
     * @param  array<string, mixed>  $dayMaster
     * @param  array<string, mixed>  $monthPillar
     * @param  array<string, mixed>  $seasonalQi
     * @param  array<string, mixed>  $roots
     * @param  array<string, mixed>  $strength
     * @param  array<string, mixed>  $relationships
     * @param  array<string, mixed>  $structure
     * @param  array<string, mixed>  $usefulGods
     * @return list<array{step: int, node: string, rule_id: string, source: string, conclusion: string}>
     */
    protected function buildEvidenceGraph(
        array $dayMaster,
        array $monthPillar,
        array $seasonalQi,
        array $roots,
        array $strength,
        array $relationships,
        array $structure,
        array $usefulGods
    ): array {
        return [
            [
                'step' => 1,
                'node' => 'Nhật Chủ (Day Master)',
                'rule_id' => 'bazi_dm_'.$dayMaster['index'],
                'source' => 'Uyên Hải Tử Bình',
                'conclusion' => "Thiên Can Trụ Ngày là {$dayMaster['name']} ({$dayMaster['yin_yang']} {$dayMaster['element']}) — biểu tượng: {$dayMaster['symbol']}.",
            ],
            [
                'step' => 2,
                'node' => 'Nguyệt Lệnh & Mùa Sinh',
                'rule_id' => 'bazi_month_cmd_'.$monthPillar['branch_index'],
                'source' => 'Tử Bình Chân Thuyên',
                'conclusion' => "Sinh tháng {$monthPillar['branch']['name']} ({$monthPillar['branch']['season']}): trạng thái {$seasonalQi['state']} — {$seasonalQi['note']}",
            ],
            [
                'step' => 3,
                'node' => 'Thông Căn & Trợ Lực',
                'rule_id' => 'bazi_roots_check',
                'source' => 'Trích Thiên Tủy',
                'conclusion' => $roots['has_root']
                    ? "Nhật Chủ có {$roots['root_count']} căn trong Địa Chi và ".count($roots['supporting_stems']).' Can lộ sinh trợ.'
                    : 'Nhật Chủ chưa thấy căn trực tiếp trong Địa Chi hiện có; cần xét thêm sinh trợ từ Ấn Tinh.',
            ],
            [
                'step' => 4,
                'node' => 'Vượng Suy (Phù Ức)',
                'rule_id' => 'bazi_strength_'.$strength['verdict'],
                'source' => 'Quy ước tu_binh_mayhen_v1.0',
                'conclusion' => "{$strength['label']} (Tỷ trọng trợ lực: {$strength['support_ratio']}%).",
            ],
            [
                'step' => 5,
                'node' => 'Quan Hệ Can Chi',
                'rule_id' => 'bazi_relations_scan',
                'source' => 'Tam Mệnh Thông Hội',
                'conclusion' => 'Phát hiện '.count($relationships['stems']).' quan hệ Thiên Can và '.count($relationships['branches']).' quan hệ Địa Chi.',
            ],
            [
                'step' => 6,
                'node' => 'Cách Cục & Dụng Thần Đa Phương Pháp',
                'rule_id' => 'bazi_structure_useful',
                'source' => 'Tử Bình Chân Thuyên & Cùng Thông Bảo Giám',
                'conclusion' => "{$structure['name']}. Phù Ức: {$usefulGods['phu_uc']['note']} Điều Hậu: {$usefulGods['dieu_hau']['note']}",
            ],
        ];
    }

    protected function stemName(int $idx): string
    {
        return BaziRegistry::STEMS[$idx]['name'] ?? '';
    }
}
