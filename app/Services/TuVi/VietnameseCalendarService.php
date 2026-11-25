<?php

declare(strict_types=1);

namespace App\Services\TuVi;

use App\Services\Iching\VietnameseLunarCalendarService;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

/**
 * Engine Lịch Pháp 2 Chiều (Dương lịch <-> Âm lịch Việt Nam) phục vụ Mây Hẹn – Thiên Mệnh (Tử Vi Đẩu Số).
 *
 * - Hỗ trợ 1900–2100, xử lý múi giờ IANA nơi sinh, 60 Hoa Giáp, 30 Nạp Âm, Can Chi Năm/Tháng/Ngày/Giờ.
 * - Hỗ trợ rõ ràng 4 chế độ giờ sinh: exact_time, earthly_branch, time_window, unknown (không bao giờ âm thầm gán 12:00).
 * - Minh bạch quy ước Giờ Tý muộn (23:00–23:59) và Tháng Nhuận (split_15 / keep_month).
 */
class VietnameseCalendarService
{
    public const MIN_YEAR = 1900;

    public const MAX_YEAR = 2100;

    public const RULE_VERSION = 'vn_calendar_tuvi_v1';

    /** @var array<int, string> 0..9 */
    public const STEMS = [
        0 => 'Giáp',
        1 => 'Ất',
        2 => 'Bính',
        3 => 'Đinh',
        4 => 'Mậu',
        5 => 'Kỷ',
        6 => 'Canh',
        7 => 'Tân',
        8 => 'Nhâm',
        9 => 'Quý',
    ];

    /** @var array<int, string> 0..11 (Tý = 0 .. Hợi = 11) */
    public const BRANCHES = [
        0 => 'Tý',
        1 => 'Sửu',
        2 => 'Dần',
        3 => 'Mão',
        4 => 'Thìn',
        5 => 'Tỵ',
        6 => 'Ngọ',
        7 => 'Mùi',
        8 => 'Thân',
        9 => 'Dậu',
        10 => 'Tuất',
        11 => 'Hợi',
    ];

    /** @var array<int, array{index: int, name: string, range: string, sample_hour: int}> */
    public const HOUR_BRANCH_META = [
        0 => ['index' => 0, 'name' => 'Tý', 'range' => '23:00 – 00:59', 'sample_hour' => 0],
        1 => ['index' => 1, 'name' => 'Sửu', 'range' => '01:00 – 02:59', 'sample_hour' => 2],
        2 => ['index' => 2, 'name' => 'Dần', 'range' => '03:00 – 04:59', 'sample_hour' => 4],
        3 => ['index' => 3, 'name' => 'Mão', 'range' => '05:00 – 06:59', 'sample_hour' => 6],
        4 => ['index' => 4, 'name' => 'Thìn', 'range' => '07:00 – 08:59', 'sample_hour' => 8],
        5 => ['index' => 5, 'name' => 'Tỵ', 'range' => '09:00 – 10:59', 'sample_hour' => 10],
        6 => ['index' => 6, 'name' => 'Ngọ', 'range' => '11:00 – 12:59', 'sample_hour' => 12],
        7 => ['index' => 7, 'name' => 'Mùi', 'range' => '13:00 – 14:59', 'sample_hour' => 14],
        8 => ['index' => 8, 'name' => 'Thân', 'range' => '15:00 – 16:59', 'sample_hour' => 16],
        9 => ['index' => 9, 'name' => 'Dậu', 'range' => '17:00 – 18:59', 'sample_hour' => 18],
        10 => ['index' => 10, 'name' => 'Tuất', 'range' => '19:00 – 20:59', 'sample_hour' => 20],
        11 => ['index' => 11, 'name' => 'Hợi', 'range' => '21:00 – 22:59', 'sample_hour' => 22],
    ];

    /** @var array<string, array{label: string, branches: list<int>}> */
    public const TIME_WINDOWS = [
        'early_morning' => ['label' => 'Rạng sáng (01:00 – 05:00)', 'branches' => [1, 2]],
        'morning' => ['label' => 'Buổi sáng (05:00 – 11:00)', 'branches' => [3, 4, 5]],
        'noon' => ['label' => 'Buổi trưa (11:00 – 15:00)', 'branches' => [6, 7]],
        'afternoon' => ['label' => 'Buổi chiều (15:00 – 19:00)', 'branches' => [8, 9]],
        'evening' => ['label' => 'Buổi tối (19:00 – 23:00)', 'branches' => [10, 11]],
        'late_night' => ['label' => 'Nửa đêm (23:00 – 01:00)', 'branches' => [0]],
    ];

    /** @var array<string, array{name: string, element: string, hanzi: string}>|null */
    protected static ?array $sexagenaryMap = null;

    protected VietnameseLunarCalendarService $lunarCore;

    public function __construct(?VietnameseLunarCalendarService $lunarCore = null)
    {
        $this->lunarCore = $lunarCore ?? new VietnameseLunarCalendarService;
    }

    /**
     * Chuẩn hóa dữ liệu sinh đầu vào thành bộ thông số lịch pháp đầy đủ cho Tử Vi Đẩu Số.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function normalizeBirthData(array $input): array
    {
        $calendarType = ($input['calendar_type'] ?? 'solar') === 'lunar' ? 'lunar' : 'solar';
        $year = (int) ($input['birth_year'] ?? 0);
        $month = (int) ($input['birth_month'] ?? 0);
        $day = (int) ($input['birth_day'] ?? 0);
        $isLeapInput = (bool) ($input['is_leap_month'] ?? false);
        $timeMode = (string) ($input['birth_time_mode'] ?? 'exact_time');
        if (! in_array($timeMode, ['exact_time', 'earthly_branch', 'time_window', 'unknown'], true)) {
            $timeMode = 'exact_time';
        }

        $timezone = $this->resolveValidTimezone((string) ($input['birth_timezone'] ?? 'Asia/Ho_Chi_Minh'));
        $tzOffsetHours = $this->getTimezoneOffsetHours($timezone, max(self::MIN_YEAR, min(self::MAX_YEAR, $year ?: 2000)), max(1, min(12, $month ?: 1)), max(1, min(28, $day ?: 1)));
        $lateRatRule = ($input['late_rat_rule'] ?? 'next_lunar_day') === 'keep_day' ? 'keep_day' : 'next_lunar_day';
        $leapMonthRule = ($input['leap_month_rule'] ?? 'keep_month') === 'keep_month' ? 'keep_month' : 'split_15';
        if (! isset($input['leap_month_rule'])) {
            $leapMonthRule = 'split_15';
        }

        if ($year < self::MIN_YEAR || $year > self::MAX_YEAR) {
            throw new InvalidArgumentException('Năm sinh được hỗ trợ trong khoảng từ '.self::MIN_YEAR.' đến '.self::MAX_YEAR.'.');
        }

        $hour = isset($input['birth_hour']) && $input['birth_hour'] !== '' ? (int) $input['birth_hour'] : null;
        $minute = isset($input['birth_minute']) && $input['birth_minute'] !== '' ? (int) $input['birth_minute'] : 0;
        $branchIndex = isset($input['birth_branch_index']) && $input['birth_branch_index'] !== '' ? (int) $input['birth_branch_index'] : null;
        $timeWindowKey = isset($input['birth_time_window']) ? (string) $input['birth_time_window'] : null;

        $isLateRatHour = false;
        if ($timeMode === 'exact_time') {
            if ($hour === null || $hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
                throw new InvalidArgumentException('Giờ phút sinh không hợp lệ (giờ 0–23, phút 0–59).');
            }
            $isLateRatHour = $hour === 23;
            $branchIndex = $this->hourToBranchIndex($hour);
        } elseif ($timeMode === 'earthly_branch') {
            if ($branchIndex === null || $branchIndex < 0 || $branchIndex > 11) {
                throw new InvalidArgumentException('Giờ Địa Chi sinh không hợp lệ (0 Tý .. 11 Hợi).');
            }
            $hour = self::HOUR_BRANCH_META[$branchIndex]['sample_hour'];
            $minute = 0;
        } elseif ($timeMode === 'time_window') {
            if (! $timeWindowKey || ! isset(self::TIME_WINDOWS[$timeWindowKey])) {
                throw new InvalidArgumentException('Khoảng thời gian sinh không hợp lệ.');
            }
            $branchIndex = null;
        } else {
            $branchIndex = null;
            $hour = null;
            $minute = null;
        }

        // Quy đổi 2 chiều Âm <-> Dương
        if ($calendarType === 'solar') {
            if (! checkdate($month, $day, $year)) {
                throw new InvalidArgumentException('Ngày tháng năm Dương lịch không tồn tại (ví dụ 30/02 hoặc 31/04).');
            }
            $solarCarbon = Carbon::create($year, $month, $day, $hour ?? 12, $minute ?? 0, 0, $timezone);
            $effectiveSolar = ($isLateRatHour && $lateRatRule === 'next_lunar_day')
                ? $solarCarbon->copy()->addDay()
                : $solarCarbon->copy();

            if ($effectiveSolar->year > self::MAX_YEAR) {
                throw new InvalidArgumentException('Ngày sau khi chuyển ranh giới giờ Tý vượt quá phạm vi năm '.self::MAX_YEAR.'.');
            }

            $lunar = $this->lunarCore->convertSolarToLunar(
                $effectiveSolar->day,
                $effectiveSolar->month,
                $effectiveSolar->year,
                $tzOffsetHours
            );
            $solarY = $solarCarbon->year;
            $solarM = $solarCarbon->month;
            $solarD = $solarCarbon->day;
        } else {
            if ($month < 1 || $month > 12 || $day < 1 || $day > 30) {
                throw new InvalidArgumentException('Ngày tháng Âm lịch không hợp lệ (tháng 1–12, ngày 1–30).');
            }
            $solarFound = $this->convertLunarToSolar($day, $month, $year, $isLeapInput, $tzOffsetHours);
            if ($solarFound === null) {
                throw new InvalidArgumentException(
                    $isLeapInput
                        ? "Năm Âm lịch {$year} không có ngày {$day} tháng {$month} nhuận."
                        : "Không tìm thấy ngày {$day} tháng {$month} trong năm Âm lịch {$year}."
                );
            }
            $solarY = $solarFound['year'];
            $solarM = $solarFound['month'];
            $solarD = $solarFound['day'];
            $lunar = [
                'day' => $day,
                'month' => $month,
                'year' => $year,
                'leap' => $isLeapInput,
            ];
            if ($isLateRatHour && $lateRatRule === 'next_lunar_day') {
                $nextSolar = Carbon::create($solarY, $solarM, $solarD, 0, 0, 0, $timezone)->addDay();
                $lunar = $this->lunarCore->convertSolarToLunar($nextSolar->day, $nextSolar->month, $nextSolar->year, $tzOffsetHours);
            }
        }

        // Quy ước tháng nhuận cho phép an cung & an sao (Tử Vi Tân Biên: split_15)
        $effectiveLunarMonth = $lunar['month'];
        $leapNote = null;
        if ($lunar['leap']) {
            if ($leapMonthRule === 'split_15' && $lunar['day'] >= 16) {
                $effectiveLunarMonth = ($lunar['month'] % 12) + 1;
                $leapNote = "Sinh từ ngày 16 tháng {$lunar['month']} nhuận: theo quy ước Nam Phái (split_15), tháng an sao chuyển sang tháng {$effectiveLunarMonth}.";
            } else {
                $leapNote = "Sinh tháng {$lunar['month']} nhuận (ngày {$lunar['day']}): tính theo tháng {$effectiveLunarMonth} ({$leapMonthRule}).";
            }
        }

        // Can Chi Năm Âm lịch
        $yearStemIndex = (($lunar['year'] - 4) % 10 + 10) % 10;
        $yearBranchIndex = (($lunar['year'] - 4) % 12 + 12) % 12;
        $yearCanChi = self::STEMS[$yearStemIndex].' '.self::BRANCHES[$yearBranchIndex];
        $napAm = $this->getNapAm($yearStemIndex, $yearBranchIndex);

        // Can Chi Tháng Âm lịch (Ngũ Hổ Độn: tháng Giêng khởi Dần = branch 2)
        $monthStemStart = (($yearStemIndex % 5) * 2 + 2) % 10;
        $monthStemIndex = ($monthStemStart + ($lunar['month'] - 1)) % 10;
        $monthBranchIndex = ($lunar['month'] + 1) % 12;
        $monthCanChi = self::STEMS[$monthStemIndex].' '.self::BRANCHES[$monthBranchIndex];

        // Can Chi Ngày (theo Julian Day Number chuẩn thiên văn)
        $jdn = $this->jdFromDate($solarD, $solarM, $solarY);
        if ($isLateRatHour && $lateRatRule === 'next_lunar_day') {
            $jdn += 1;
        }
        $dayStemIndex = (($jdn + 9) % 10 + 10) % 10;
        $dayBranchIndex = (($jdn + 1) % 12 + 12) % 12;
        $dayCanChi = self::STEMS[$dayStemIndex].' '.self::BRANCHES[$dayBranchIndex];

        // Can Chi Giờ (Ngũ Thử Độn)
        $hourStemIndex = null;
        $hourCanChi = null;
        if ($branchIndex !== null) {
            $hourStemStart = ($dayStemIndex % 5) * 2;
            $hourStemIndex = ($hourStemStart + $branchIndex) % 10;
            $hourCanChi = self::STEMS[$hourStemIndex].' '.self::BRANCHES[$branchIndex];
        }

        $notes = array_values(array_filter([
            $isLateRatHour
                ? ($lateRatRule === 'next_lunar_day'
                    ? 'Sinh vào Giờ Tý muộn (23:00–23:59): quy ước mặc định tính sang ngày Âm lịch kế tiếp với Giờ Tý.'
                    : 'Sinh vào Giờ Tý muộn (23:00–23:59): giữ nguyên ngày Âm lịch cũ theo tùy chọn nâng cao.')
                : null,
            $leapNote,
            ($solarY < 1975 && $timezone === 'Asia/Ho_Chi_Minh')
                ? 'Lưu ý lịch sử trước năm 1975: một số giai đoạn tại miền Nam Việt Nam từng sử dụng múi giờ UTC+8; nếu biết chính xác giờ đồng hồ địa phương, bạn có thể đối chiếu lại ở mục Múi giờ.'
                : null,
        ]));

        return [
            'rule_version' => self::RULE_VERSION,
            'calendar_type' => $calendarType,
            'timezone' => $timezone,
            'tz_offset_hours' => $tzOffsetHours,
            'birth_time_mode' => $timeMode,
            'birth_time_window' => $timeWindowKey,
            'late_rat_rule' => $lateRatRule,
            'leap_month_rule' => $leapMonthRule,
            'is_late_rat_hour' => $isLateRatHour,
            'solar' => [
                'day' => $solarD,
                'month' => $solarM,
                'year' => $solarY,
                'hour' => $hour,
                'minute' => $minute,
                'label' => sprintf('%02d/%02d/%04d', $solarD, $solarM, $solarY),
            ],
            'lunar' => [
                'day' => $lunar['day'],
                'month' => $lunar['month'],
                'effective_month' => $effectiveLunarMonth,
                'year' => $lunar['year'],
                'is_leap_month' => $lunar['leap'],
                'label' => sprintf(
                    'Ngày %02d tháng %d%s năm %s (%d)',
                    $lunar['day'],
                    $lunar['month'],
                    $lunar['leap'] ? ' nhuận' : '',
                    $yearCanChi,
                    $lunar['year']
                ),
            ],
            'pillars' => [
                'year' => ['stem_index' => $yearStemIndex, 'branch_index' => $yearBranchIndex, 'stem' => self::STEMS[$yearStemIndex], 'branch' => self::BRANCHES[$yearBranchIndex], 'label' => $yearCanChi, 'is_yang_stem' => ($yearStemIndex % 2) === 0],
                'month' => ['stem_index' => $monthStemIndex, 'branch_index' => $monthBranchIndex, 'stem' => self::STEMS[$monthStemIndex], 'branch' => self::BRANCHES[$monthBranchIndex], 'label' => $monthCanChi],
                'day' => ['stem_index' => $dayStemIndex, 'branch_index' => $dayBranchIndex, 'stem' => self::STEMS[$dayStemIndex], 'branch' => self::BRANCHES[$dayBranchIndex], 'label' => $dayCanChi],
                'hour' => $branchIndex !== null
                    ? ['stem_index' => $hourStemIndex, 'branch_index' => $branchIndex, 'stem' => self::STEMS[$hourStemIndex], 'branch' => self::BRANCHES[$branchIndex], 'label' => $hourCanChi, 'range' => self::HOUR_BRANCH_META[$branchIndex]['range']]
                    : null,
            ],
            'nap_am' => $napAm,
            'notes' => $notes,
        ];
    }

    /**
     * Quy đổi giờ 24h (0..23) sang chỉ số 12 Giờ Địa Chi (0 = Tý [23h–01h], 1 = Sửu [01h–03h] .. 11 = Hợi [21h–23h]).
     */
    public function hourToBranchIndex(int $hour24): int
    {
        $h = (($hour24 % 24) + 24) % 24;

        return (int) floor((($h + 1) % 24) / 2);
    }

    /**
     * Chuyển đổi ngược Âm lịch Việt Nam -> Dương lịch (tìm chính xác trong cửa sổ thiên văn 1900–2100).
     *
     * @return array{day: int, month: int, year: int}|null
     */
    public function convertLunarToSolar(int $lunarDay, int $lunarMonth, int $lunarYear, bool $isLeapMonth = false, float $timeZone = 7.0): ?array
    {
        if ($lunarYear < self::MIN_YEAR || $lunarYear > self::MAX_YEAR) {
            return null;
        }

        // Ngày Âm lịch của năm Y luôn nằm trong khoảng từ 15/01/Y đến 28/02/(Y+1) Dương lịch
        $cursor = Carbon::create($lunarYear, 1, 15, 12, 0, 0, 'UTC');
        $end = Carbon::create(min(self::MAX_YEAR, $lunarYear + 1), 2, 28, 12, 0, 0, 'UTC');

        while ($cursor->lte($end)) {
            $conv = $this->lunarCore->convertSolarToLunar($cursor->day, $cursor->month, $cursor->year, $timeZone);
            if (
                $conv['year'] === $lunarYear
                && $conv['month'] === $lunarMonth
                && $conv['day'] === $lunarDay
                && $conv['leap'] === $isLeapMonth
            ) {
                return [
                    'day' => $cursor->day,
                    'month' => $cursor->month,
                    'year' => $cursor->year,
                ];
            }
            $cursor->addDay();
        }

        return null;
    }

    /**
     * Tra Nạp Âm Bản Mệnh từ Can (0..9) và Chi (0..11).
     *
     * @return array{name: string, element: string, hanzi: string}
     */
    public function getNapAm(int $stemIndex, int $branchIndex): array
    {
        $this->bootSexagenary();
        $key = self::STEMS[$stemIndex].' '.self::BRANCHES[$branchIndex];

        return self::$sexagenaryMap[$key] ?? ['name' => 'Hải Trung Kim', 'element' => 'Kim', 'hanzi' => '海中金'];
    }

    /**
     * Lấy Can Chi của một năm xem bất kỳ (Lưu Niên).
     *
     * @return array{year: int, stem_index: int, branch_index: int, stem: string, branch: string, label: string}
     */
    public function getYearCanChi(int $year): array
    {
        $s = (($year - 4) % 10 + 10) % 10;
        $b = (($year - 4) % 12 + 12) % 12;

        return [
            'year' => $year,
            'stem_index' => $s,
            'branch_index' => $b,
            'stem' => self::STEMS[$s],
            'branch' => self::BRANCHES[$b],
            'label' => self::STEMS[$s].' '.self::BRANCHES[$b],
        ];
    }

    protected function resolveValidTimezone(string $tz): string
    {
        $tz = trim($tz);
        if ($tz !== '' && in_array($tz, DateTimeZone::listIdentifiers(), true)) {
            return $tz;
        }

        return 'Asia/Ho_Chi_Minh';
    }

    protected function getTimezoneOffsetHours(string $timezone, int $year, int $month, int $day): float
    {
        try {
            $dt = Carbon::create($year, $month, $day, 12, 0, 0, $timezone);

            return round($dt->offset / 3600, 2);
        } catch (\Throwable) {
            return 7.0;
        }
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

    protected function bootSexagenary(): void
    {
        if (self::$sexagenaryMap !== null) {
            return;
        }
        $path = database_path('data/tuvi/sexagenary_60.json');
        $raw = File::exists($path) ? json_decode((string) File::get($path), true) : [];
        $map = [];
        foreach (($raw['items'] ?? []) as $item) {
            $map[(string) $item['can_chi']] = [
                'name' => (string) $item['nap_am'],
                'element' => (string) $item['element'],
                'hanzi' => (string) ($item['hanzi'] ?? ''),
            ];
        }
        self::$sexagenaryMap = $map;
    }
}
