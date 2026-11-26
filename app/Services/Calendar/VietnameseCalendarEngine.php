<?php

declare(strict_types=1);

namespace App\Services\Calendar;

use App\Services\Iching\VietnameseLunarCalendarService;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Vietnamese Lunisolar Calendar Engine dùng chung cho toàn bộ hệ sinh thái Mây Hẹn (1900–2100).
 *
 * - Tái sử dụng lõi thiên văn Sóc & Trung Khí (Hồ Ngọc Đức / Jean Meeus) từ VietnameseLunarCalendarService.
 * - Hỗ trợ 2 chế độ: `vn_modern_utc7` (chuẩn hiện hành UTC+7) và `vn_historical` (minh bạch múi giờ lịch sử).
 * - Chuẩn hóa 10 Thiên Can, 12 Địa Chi (Mão = Mèo, Sửu = Trâu chuẩn văn hóa Việt Nam), 60 Hoa Giáp & 30 Nạp Âm.
 * - Kiểm tra chặt tính hợp lệ của cả Dương lịch (29/02/1900, 31/04) lẫn Âm lịch (tháng thiếu 29 ngày, tháng nhuận).
 */
class VietnameseCalendarEngine
{
    public const ENGINE_VERSION = 'mayhen_vn_calendar_v1.0';

    public const MIN_YEAR = 1900;

    public const MAX_YEAR = 2100;

    public const MODE_MODERN = 'vn_modern_utc7';

    public const MODE_HISTORICAL = 'vn_historical';

    /** @var array<int, array{index: int, name: string, yin_yang: string, element: string}> */
    public const STEMS = [
        0 => ['index' => 0, 'name' => 'Giáp', 'yin_yang' => 'Dương', 'element' => 'Mộc'],
        1 => ['index' => 1, 'name' => 'Ất', 'yin_yang' => 'Âm', 'element' => 'Mộc'],
        2 => ['index' => 2, 'name' => 'Bính', 'yin_yang' => 'Dương', 'element' => 'Hỏa'],
        3 => ['index' => 3, 'name' => 'Đinh', 'yin_yang' => 'Âm', 'element' => 'Hỏa'],
        4 => ['index' => 4, 'name' => 'Mậu', 'yin_yang' => 'Dương', 'element' => 'Thổ'],
        5 => ['index' => 5, 'name' => 'Kỷ', 'yin_yang' => 'Âm', 'element' => 'Thổ'],
        6 => ['index' => 6, 'name' => 'Canh', 'yin_yang' => 'Dương', 'element' => 'Kim'],
        7 => ['index' => 7, 'name' => 'Tân', 'yin_yang' => 'Âm', 'element' => 'Kim'],
        8 => ['index' => 8, 'name' => 'Nhâm', 'yin_yang' => 'Dương', 'element' => 'Thủy'],
        9 => ['index' => 9, 'name' => 'Quý', 'yin_yang' => 'Âm', 'element' => 'Thủy'],
    ];

    /** @var array<int, array{index: int, name: string, zodiac: string, yin_yang: string, element: string, hours: string, start_hour: int, end_hour: int}> */
    public const BRANCHES = [
        0 => ['index' => 0, 'name' => 'Tý', 'zodiac' => 'Chuột', 'yin_yang' => 'Dương', 'element' => 'Thủy', 'hours' => '23:00–01:00', 'start_hour' => 23, 'end_hour' => 1],
        1 => ['index' => 1, 'name' => 'Sửu', 'zodiac' => 'Trâu', 'yin_yang' => 'Âm', 'element' => 'Thổ', 'hours' => '01:00–03:00', 'start_hour' => 1, 'end_hour' => 3],
        2 => ['index' => 2, 'name' => 'Dần', 'zodiac' => 'Hổ', 'yin_yang' => 'Dương', 'element' => 'Mộc', 'hours' => '03:00–05:00', 'start_hour' => 3, 'end_hour' => 5],
        3 => ['index' => 3, 'name' => 'Mão', 'zodiac' => 'Mèo', 'yin_yang' => 'Âm', 'element' => 'Mộc', 'hours' => '05:00–07:00', 'start_hour' => 5, 'end_hour' => 7],
        4 => ['index' => 4, 'name' => 'Thìn', 'zodiac' => 'Rồng', 'yin_yang' => 'Dương', 'element' => 'Thổ', 'hours' => '07:00–09:00', 'start_hour' => 7, 'end_hour' => 9],
        5 => ['index' => 5, 'name' => 'Tỵ', 'zodiac' => 'Rắn', 'yin_yang' => 'Âm', 'element' => 'Hỏa', 'hours' => '09:00–11:00', 'start_hour' => 9, 'end_hour' => 11],
        6 => ['index' => 6, 'name' => 'Ngọ', 'zodiac' => 'Ngựa', 'yin_yang' => 'Dương', 'element' => 'Hỏa', 'hours' => '11:00–13:00', 'start_hour' => 11, 'end_hour' => 13],
        7 => ['index' => 7, 'name' => 'Mùi', 'zodiac' => 'Dê', 'yin_yang' => 'Âm', 'element' => 'Thổ', 'hours' => '13:00–15:00', 'start_hour' => 13, 'end_hour' => 15],
        8 => ['index' => 8, 'name' => 'Thân', 'zodiac' => 'Khỉ', 'yin_yang' => 'Dương', 'element' => 'Kim', 'hours' => '15:00–17:00', 'start_hour' => 15, 'end_hour' => 17],
        9 => ['index' => 9, 'name' => 'Dậu', 'zodiac' => 'Gà', 'yin_yang' => 'Âm', 'element' => 'Kim', 'hours' => '17:00–19:00', 'start_hour' => 17, 'end_hour' => 19],
        10 => ['index' => 10, 'name' => 'Tuất', 'zodiac' => 'Chó', 'yin_yang' => 'Dương', 'element' => 'Thổ', 'hours' => '19:00–21:00', 'start_hour' => 19, 'end_hour' => 21],
        11 => ['index' => 11, 'name' => 'Hợi', 'zodiac' => 'Lợn', 'yin_yang' => 'Âm', 'element' => 'Thủy', 'hours' => '21:00–23:00', 'start_hour' => 21, 'end_hour' => 23],
    ];

    /** @var array<int, string> 0 = Chủ Nhật .. 6 = Thứ Bảy */
    public const WEEKDAYS_VI = [
        0 => 'Chủ Nhật',
        1 => 'Thứ Hai',
        2 => 'Thứ Ba',
        3 => 'Thứ Tư',
        4 => 'Thứ Năm',
        5 => 'Thứ Sáu',
        6 => 'Thứ Bảy',
    ];

    /** @var array<string, array{generates: string, controls: string, generated_by: string, controlled_by: string, color: string}> */
    public const FIVE_ELEMENTS = [
        'Mộc' => ['generates' => 'Hỏa', 'controls' => 'Thổ', 'generated_by' => 'Thủy', 'controlled_by' => 'Kim', 'color' => '#1E6B45'],
        'Hỏa' => ['generates' => 'Thổ', 'controls' => 'Kim', 'generated_by' => 'Mộc', 'controlled_by' => 'Thủy', 'color' => '#A62332'],
        'Thổ' => ['generates' => 'Kim', 'controls' => 'Thủy', 'generated_by' => 'Hỏa', 'controlled_by' => 'Mộc', 'color' => '#A67C2E'],
        'Kim' => ['generates' => 'Thủy', 'controls' => 'Mộc', 'generated_by' => 'Thổ', 'controlled_by' => 'Hỏa', 'color' => '#5C677D'],
        'Thủy' => ['generates' => 'Mộc', 'controls' => 'Hỏa', 'generated_by' => 'Kim', 'controlled_by' => 'Thổ', 'color' => '#1D4E89'],
    ];

    /** @var array<string, array{name: string, element: string, hanzi: string}>|null */
    protected static ?array $sexagenaryMap = null;

    /** @var array<string, list<array<string, mixed>>> */
    protected static array $lunarYearMonthsCache = [];

    public function __construct(protected ?VietnameseLunarCalendarService $lunarCore = null)
    {
        $this->lunarCore = $lunarCore ?? new VietnameseLunarCalendarService;
    }

    /**
     * Kiểm tra năm nhuận Dương lịch Gregory.
     */
    public function isGregorianLeapYear(int $year): bool
    {
        return ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
    }

    /**
     * Số ngày trong tháng Dương lịch.
     */
    public function getGregorianMonthLength(int $year, int $month): int
    {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException('Tháng Dương lịch phải nằm trong 1..12.');
        }

        return match ($month) {
            2 => $this->isGregorianLeapYear($year) ? 29 : 28,
            4, 6, 9, 11 => 30,
            default => 31,
        };
    }

    /**
     * Tính Julian Day Number (JDN) cho một civil date Dương lịch.
     */
    public function jdFromDate(int $day, int $month, int $year): int
    {
        $a = (int) floor((14 - $month) / 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;
        $jd = $day + (int) floor((153 * $m + 2) / 5) + 365 * $y + (int) floor($y / 4) - (int) floor($y / 100) + (int) floor($y / 400) - 32045;
        if ($jd < 2299161) {
            $jd = $day + (int) floor((153 * $m + 2) / 5) + 365 * $y + (int) floor($y / 4) - 32083;
        }

        return $jd;
    }

    /**
     * Chuyển đổi ngược Julian Day Number (JDN) về civil date Dương lịch.
     *
     * @return array{day: int, month: int, year: int}
     */
    public function dateFromJd(int $jd): array
    {
        if ($jd > 2299160) {
            $a = $jd + 32044;
            $b = (int) floor((4 * $a + 3) / 146097);
            $c = $a - (int) floor(($b * 146097) / 4);
        } else {
            $b = 0;
            $c = $jd + 32082;
        }

        $d = (int) floor((4 * $c + 3) / 1461);
        $e = $c - (int) floor((1461 * $d) / 4);
        $m = (int) floor((5 * $e + 2) / 153);
        $day = $e - (int) floor((153 * $m + 2) / 5) + 1;
        $month = $m + 3 - 12 * (int) floor($m / 10);
        $year = $b * 100 + $d - 4800 + (int) floor($m / 10);

        return ['day' => $day, 'month' => $month, 'year' => $year];
    }

    /**
     * Xác định múi giờ quy chiếu theo Calendar Mode (UTC+7 hiện hành hoặc lịch sử).
     *
     * @return array{offset: float, warning: ?string}
     */
    public function resolveCalendarTimeZone(int $year, string $mode = self::MODE_MODERN): array
    {
        if ($mode === self::MODE_HISTORICAL) {
            if ($year >= 1960 && $year <= 1975) {
                return [
                    'offset' => 8.0,
                    'warning' => 'Chế độ lịch sử (1960–1975): Miền Nam Việt Nam sử dụng múi giờ UTC+8 trong khi miền Bắc từ 1968 áp dụng UTC+7. Đang hiển thị theo mốc UTC+8 lịch sử miền Nam.',
                ];
            }
            if ($year < 1960) {
                return [
                    'offset' => 7.0,
                    'warning' => 'Chế độ lịch sử trước năm 1960: Một số giai đoạn lịch sử Việt Nam có sự thay đổi múi giờ và bảng lịch triều đình; kết quả được quy chiếu theo UTC+7 kèm lưu ý minh bạch.',
                ];
            }
        }

        return [
            'offset' => 7.0,
            'warning' => null,
        ];
    }

    /**
     * Chuyển đổi Dương lịch -> Âm lịch Việt Nam đầy đủ kèm độ dài tháng, ngày đầu/cuối tháng âm & Can Chi.
     *
     * @return array<string, mixed>
     */
    public function convertSolarToLunar(int $day, int $month, int $year, string $mode = self::MODE_MODERN): array
    {
        if (! checkdate($month, $day, $year) || $year < self::MIN_YEAR || $year > self::MAX_YEAR) {
            throw new InvalidArgumentException("Ngày Dương lịch {$day}/{$month}/{$year} không hợp lệ hoặc nằm ngoài phạm vi ".self::MIN_YEAR.'–'.self::MAX_YEAR.'.');
        }

        $tzMeta = $this->resolveCalendarTimeZone($year, $mode);
        $tz = $tzMeta['offset'];
        $jdn = $this->jdFromDate($day, $month, $year);
        $raw = $this->lunarCore->convertSolarToLunar($day, $month, $year, $tz);

        // Xác định độ dài tháng âm (29 hay 30 ngày) & ngày dương đầu/cuối tháng âm
        $monthStartJdn = $jdn - ($raw['day'] - 1);
        $day29 = $this->dateFromJd($monthStartJdn + 29);
        $check30 = $day29['year'] <= self::MAX_YEAR
            ? $this->lunarCore->convertSolarToLunar($day29['day'], $day29['month'], $day29['year'], $tz)
            : null;
        $isLeap = (bool) $raw['leap'];
        $monthLength = ($check30 && $check30['month'] === $raw['month'] && (bool) $check30['leap'] === $isLeap && $check30['day'] === 30) ? 30 : 29;
        $monthEndJdn = $monthStartJdn + ($monthLength - 1);

        $startSolar = $this->dateFromJd($monthStartJdn);
        $endSolar = $this->dateFromJd($monthEndJdn);

        $weekdayIdx = ($jdn + 1) % 7;
        $canChi = $this->computeCanChi($jdn, $raw['year'], $raw['month']);

        return [
            'engine_version' => self::ENGINE_VERSION,
            'calendar_mode' => $mode,
            'tz_offset' => $tz,
            'jdn' => $jdn,
            'solar' => [
                'day' => $day,
                'month' => $month,
                'year' => $year,
                'iso' => sprintf('%04d-%02d-%02d', $year, $month, $day),
                'label' => sprintf('%02d/%02d/%04d', $day, $month, $year),
                'weekday_index' => $weekdayIdx,
                'weekday_name' => self::WEEKDAYS_VI[$weekdayIdx],
            ],
            'lunar' => [
                'day' => $raw['day'],
                'month' => $raw['month'],
                'year' => $raw['year'],
                'is_leap_month' => $isLeap,
                'month_length' => $monthLength,
                'month_size_label' => $monthLength === 30 ? 'Tháng đủ (30 ngày)' : 'Tháng thiếu (29 ngày)',
                'solar_month_start' => sprintf('%04d-%02d-%02d', $startSolar['year'], $startSolar['month'], $startSolar['day']),
                'solar_month_end' => sprintf('%04d-%02d-%02d', $endSolar['year'], $endSolar['month'], $endSolar['day']),
                'label_short' => sprintf('%02d/%02d%s', $raw['day'], $raw['month'], $isLeap ? ' (Nhuận)' : ''),
                'label_full' => sprintf(
                    'Ngày %02d tháng %d%s năm %s (%d)',
                    $raw['day'],
                    $raw['month'],
                    $isLeap ? ' nhuận' : '',
                    $canChi['year']['label'],
                    $raw['year']
                ),
            ],
            'can_chi' => $canChi,
            'warning' => $tzMeta['warning'],
        ];
    }

    /**
     * Chuyển đổi ngược Âm lịch Việt Nam -> Dương lịch (nhanh O(13) theo danh sách tháng âm, báo lỗi rõ nếu sai tháng nhuận / ngày 30).
     *
     * @return array<string, mixed>
     */
    public function convertLunarToSolar(int $lunarDay, int $lunarMonth, int $lunarYear, bool $isLeapMonth = false, string $mode = self::MODE_MODERN): array
    {
        if ($lunarYear < self::MIN_YEAR || $lunarYear > self::MAX_YEAR) {
            throw new InvalidArgumentException('Năm Âm lịch hỗ trợ từ '.self::MIN_YEAR.' đến '.self::MAX_YEAR.'.');
        }
        if ($lunarMonth < 1 || $lunarMonth > 12 || $lunarDay < 1 || $lunarDay > 30) {
            throw new InvalidArgumentException('Ngày (1–30) hoặc tháng (1–12) Âm lịch không hợp lệ.');
        }

        $months = $this->getLunarYearMonths($lunarYear, $mode);
        foreach ($months as $m) {
            if ($m['month'] === $lunarMonth && $m['is_leap'] === $isLeapMonth) {
                if ($lunarDay > $m['days']) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Tháng %d%s năm Âm lịch %d là tháng thiếu, chỉ có %d ngày (không có ngày %d).',
                            $lunarMonth,
                            $isLeapMonth ? ' nhuận' : '',
                            $lunarYear,
                            $m['days'],
                            $lunarDay
                        )
                    );
                }

                $targetJdn = $m['start_jdn'] + ($lunarDay - 1);
                $solar = $this->dateFromJd($targetJdn);
                if ($solar['year'] > self::MAX_YEAR) {
                    throw new InvalidArgumentException('Ngày Dương lịch quy đổi vượt quá phạm vi hỗ trợ ('.self::MAX_YEAR.').');
                }

                return $this->convertSolarToLunar($solar['day'], $solar['month'], $solar['year'], $mode);
            }
        }

        throw new InvalidArgumentException(
            $isLeapMonth
                ? "Năm Âm lịch {$lunarYear} không có tháng {$lunarMonth} nhuận."
                : "Không tìm thấy tháng {$lunarMonth} trong năm Âm lịch {$lunarYear}."
        );
    }

    /**
     * Lấy trọn bộ 12 hoặc 13 tháng của một năm Âm lịch (gồm cờ nhuận, số ngày 29/30, ngày dương đầu & cuối).
     *
     * @return list<array{month: int, is_leap: bool, days: int, start_jdn: int, solar_start: string, solar_end: string, label: string}>
     */
    public function getLunarYearMonths(int $lunarYear, string $mode = self::MODE_MODERN): array
    {
        $cacheKey = "{$lunarYear}_{$mode}";
        if (isset(self::$lunarYearMonthsCache[$cacheKey])) {
            return self::$lunarYearMonthsCache[$cacheKey];
        }

        $tz = $this->resolveCalendarTimeZone($lunarYear, $mode)['offset'];

        // Tìm Mùng 1 Tết Nguyên Đán năm $lunarYear (nằm trong khoảng 20/01..21/02)
        $tetJdn = null;
        $startScan = $this->jdFromDate(18, 1, $lunarYear);
        for ($j = $startScan; $j <= $startScan + 40; $j++) {
            $d = $this->dateFromJd($j);
            $c = $this->lunarCore->convertSolarToLunar($d['day'], $d['month'], $d['year'], $tz);
            if ($c['year'] === $lunarYear && $c['month'] === 1 && $c['day'] === 1 && ! $c['leap']) {
                $tetJdn = $j;
                break;
            }
        }

        if ($tetJdn === null) {
            return [];
        }

        $months = [];
        $cursorJdn = $tetJdn;
        for ($i = 0; $i < 13; $i++) {
            $d = $this->dateFromJd($cursorJdn);
            if ($d['year'] > self::MAX_YEAR) {
                break;
            }
            $c = $this->lunarCore->convertSolarToLunar($d['day'], $d['month'], $d['year'], $tz);
            if ($c['year'] !== $lunarYear) {
                break;
            }

            $cLeap = (bool) $c['leap'];

            // Kiểm tra tháng 29 hay 30 ngày
            $d29 = $this->dateFromJd($cursorJdn + 29);
            $days = 29;
            if ($d29['year'] <= self::MAX_YEAR) {
                $c29 = $this->lunarCore->convertSolarToLunar($d29['day'], $d29['month'], $d29['year'], $tz);
                if ($c29['year'] === $lunarYear && $c29['month'] === $c['month'] && (bool) $c29['leap'] === $cLeap && $c29['day'] === 30) {
                    $days = 30;
                }
            }

            $endD = $this->dateFromJd($cursorJdn + $days - 1);
            $months[] = [
                'month' => $c['month'],
                'is_leap' => $cLeap,
                'days' => $days,
                'start_jdn' => $cursorJdn,
                'solar_start' => sprintf('%04d-%02d-%02d', $d['year'], $d['month'], $d['day']),
                'solar_end' => sprintf('%04d-%02d-%02d', $endD['year'], $endD['month'], $endD['day']),
                'label' => 'Tháng '.$c['month'].($cLeap ? ' nhuận' : ''),
            ];

            $cursorJdn += $days;
        }

        self::$lunarYearMonthsCache[$cacheKey] = $months;

        return $months;
    }

    /**
     * Kiểm tra năm Âm lịch có tháng nhuận không (trả về số tháng nhuận 1..12 hoặc null).
     */
    public function getLeapMonth(int $lunarYear, string $mode = self::MODE_MODERN): ?int
    {
        foreach ($this->getLunarYearMonths($lunarYear, $mode) as $m) {
            if ($m['is_leap']) {
                return $m['month'];
            }
        }

        return null;
    }

    /**
     * Tính Can Chi Năm, Tháng (Âm lịch), Ngày (JDN), 12 Giờ cổ truyền & Nạp Âm.
     *
     * @return array<string, mixed>
     */
    public function computeCanChi(int $jdn, int $lunarYear, int $lunarMonth): array
    {
        $yearStemIdx = (($lunarYear - 4) % 10 + 10) % 10;
        $yearBranchIdx = (($lunarYear - 4) % 12 + 12) % 12;

        // Ngũ Hổ Độn cho Can Chi tháng Âm lịch (Tháng 1 khởi Dần = index 2)
        $monthStemStart = (($yearStemIdx % 5) * 2 + 2) % 10;
        $monthStemIdx = ($monthStemStart + ($lunarMonth - 1)) % 10;
        $monthBranchIdx = ($lunarMonth + 1) % 12;

        // Can Chi Ngày theo JDN
        $dayStemIdx = (($jdn + 9) % 10 + 10) % 10;
        $dayBranchIdx = (($jdn + 1) % 12 + 12) % 12;

        // Ngũ Thử Độn cho 12 giờ trong ngày
        $hourStemStart = ($dayStemIdx % 5) * 2;
        $hours = [];
        for ($b = 0; $b < 12; $b++) {
            $hStem = ($hourStemStart + $b) % 10;
            $hours[] = [
                'branch_index' => $b,
                'stem_index' => $hStem,
                'label' => self::STEMS[$hStem]['name'].' '.self::BRANCHES[$b]['name'],
                'branch' => self::BRANCHES[$b]['name'],
                'zodiac' => self::BRANCHES[$b]['zodiac'],
                'range' => self::BRANCHES[$b]['hours'],
                'start_hour' => self::BRANCHES[$b]['start_hour'],
                'end_hour' => self::BRANCHES[$b]['end_hour'],
            ];
        }

        $dayLabel = self::STEMS[$dayStemIdx]['name'].' '.self::BRANCHES[$dayBranchIdx]['name'];
        $yearLabel = self::STEMS[$yearStemIdx]['name'].' '.self::BRANCHES[$yearBranchIdx]['name'];
        $monthLabel = self::STEMS[$monthStemIdx]['name'].' '.self::BRANCHES[$monthBranchIdx]['name'];

        return [
            'year' => [
                'stem_index' => $yearStemIdx,
                'branch_index' => $yearBranchIdx,
                'stem' => self::STEMS[$yearStemIdx]['name'],
                'branch' => self::BRANCHES[$yearBranchIdx]['name'],
                'zodiac' => self::BRANCHES[$yearBranchIdx]['zodiac'],
                'label' => $yearLabel,
                'nap_am' => $this->getNapAm($yearLabel),
            ],
            'month' => [
                'stem_index' => $monthStemIdx,
                'branch_index' => $monthBranchIdx,
                'stem' => self::STEMS[$monthStemIdx]['name'],
                'branch' => self::BRANCHES[$monthBranchIdx]['name'],
                'label' => $monthLabel,
            ],
            'day' => [
                'stem_index' => $dayStemIdx,
                'branch_index' => $dayBranchIdx,
                'stem' => self::STEMS[$dayStemIdx]['name'],
                'branch' => self::BRANCHES[$dayBranchIdx]['name'],
                'zodiac' => self::BRANCHES[$dayBranchIdx]['zodiac'],
                'stem_element' => self::STEMS[$dayStemIdx]['element'],
                'branch_element' => self::BRANCHES[$dayBranchIdx]['element'],
                'label' => $dayLabel,
                'nap_am' => $this->getNapAm($dayLabel),
            ],
            'hours' => $hours,
        ];
    }

    /**
     * @return array{name: string, element: string, hanzi: string}
     */
    public function getNapAm(string $canChiLabel): array
    {
        if (self::$sexagenaryMap === null) {
            $path = dirname(__DIR__, 3).'/database/data/tuvi/sexagenary_60.json';
            $raw = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];
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

        return self::$sexagenaryMap[$canChiLabel] ?? ['name' => 'Hải Trung Kim', 'element' => 'Kim', 'hanzi' => '海中金'];
    }

    /**
     * Ngày hiện tại theo múi giờ chuẩn Việt Nam Asia/Ho_Chi_Minh (không phụ thuộc timezone server).
     */
    public function todayVn(): Carbon
    {
        return Carbon::now('Asia/Ho_Chi_Minh');
    }
}
