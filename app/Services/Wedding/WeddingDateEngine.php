<?php

declare(strict_types=1);

namespace App\Services\Wedding;

use App\Services\Bazi\BaziRegistry;
use App\Services\Calendar\CalendarAstronomyEngine;
use App\Services\Calendar\TraditionalAlmanacEngine;
use App\Services\Calendar\VietnameseCalendarEngine;
use App\Services\Marriage\MarriageCompatibilityEngine;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Wedding Date Engine (Mây Hẹn – The Wedding Journey).
 *
 * - Tái sử dụng 100% VietnameseCalendarEngine, CalendarAstronomyEngine, TraditionalAlmanacEngine & MarriageCompatibilityEngine.
 * - Tách bạch 3 lớp đánh giá: (1) Ràng buộc thực tế (Hard/Soft Constraints), (2) Quy tắc Lịch Vạn Niên truyền thống, (3) Tương tác tuổi hai bên & Kim Lâu / Đại Lợi Nguyệt (tùy chọn).
 * - TUYỆT ĐỐI không sinh tỷ lệ % đại cát giả, không dùng AI để chọn ngày, và không dùng tuổi mụ cho điều kiện pháp lý.
 */
class WeddingDateEngine
{
    public const VERSION = 'mayhen_wedding_date_v1.0';

    /**
     * 8 Hồ sơ nghi lễ cưới hỏi chuyên biệt (Activity Profiles).
     *
     * @var array<string, array{label: string, short: string, preferred_truc: list<string>, avoid_truc: list<string>, check_tam_nuong: bool, check_nguyet_ky: bool, default_duration_min: int, note: string}>
     */
    public const ACTIVITY_PROFILES = [
        'dam_ngo' => [
            'label' => 'Lễ Dạm Ngõ',
            'short' => 'Dạm ngõ',
            'preferred_truc' => ['Định', 'Thành', 'Mãn', 'Khai', 'Bình'],
            'avoid_truc' => ['Phá', 'Nguy'],
            'check_tam_nuong' => true,
            'check_nguyet_ky' => false,
            'default_duration_min' => 90,
            'note' => 'Buổi gặp mặt chính thức đầu tiên giữa hai gia đình, ưu tiên ngày thuận tiện cuối tuần và không khí ấm cúng.',
        ],
        'an_hoi' => [
            'label' => 'Lễ Ăn Hỏi (Đính Hôn)',
            'short' => 'Ăn hỏi',
            'preferred_truc' => ['Định', 'Thành', 'Mãn', 'Khai'],
            'avoid_truc' => ['Phá', 'Nguy', 'Bế'],
            'check_tam_nuong' => true,
            'check_nguyet_ky' => true,
            'default_duration_min' => 150,
            'note' => 'Nghi lễ trao tráp lễ vật và ra mắt họ hàng hai bên, cần cân nhắc giờ đẹp buổi sáng và quãng đường di chuyển.',
        ],
        'le_gia_tien' => [
            'label' => 'Lễ Gia Tiên',
            'short' => 'Gia tiên',
            'preferred_truc' => ['Thành', 'Định', 'Khai', 'Mãn'],
            'avoid_truc' => ['Phá', 'Nguy', 'Bế'],
            'check_tam_nuong' => true,
            'check_nguyet_ky' => true,
            'default_duration_min' => 120,
            'note' => 'Nghi thức bái lễ tổ tiên tại tư gia hai họ; nên chọn khung giờ Hoàng Đạo ban ngày để đón khách chu đáo.',
        ],
        'don_dau' => [
            'label' => 'Lễ Đón Dâu (Rước Dâu)',
            'short' => 'Đón dâu',
            'preferred_truc' => ['Thành', 'Khai', 'Định', 'Mãn'],
            'avoid_truc' => ['Phá', 'Nguy', 'Bế'],
            'check_tam_nuong' => true,
            'check_nguyet_ky' => true,
            'default_duration_min' => 120,
            'note' => 'Cần tính chuẩn giờ khởi hành, thời gian di chuyển dự phòng (kẹt xe) và giờ vào nhà trai.',
        ],
        'le_thanh_hon' => [
            'label' => 'Lễ Thành Hôn / Lễ Vu Quy',
            'short' => 'Thành hôn',
            'preferred_truc' => ['Thành', 'Khai', 'Định', 'Mãn'],
            'avoid_truc' => ['Phá', 'Nguy', 'Bế'],
            'check_tam_nuong' => true,
            'check_nguyet_ky' => true,
            'default_duration_min' => 240,
            'note' => 'Ngày trọng đại nhất của hai gia đình; kết hợp hài hòa giữa yếu tố truyền thống và lịch rảnh thực tế của khách mời.',
        ],
        'tiec_cuoi' => [
            'label' => 'Tiệc Cưới (Báo Hỷ / Nhà Hàng)',
            'short' => 'Tiệc cưới',
            'preferred_truc' => ['Mãn', 'Thành', 'Khai', 'Định', 'Bình'],
            'avoid_truc' => ['Phá'],
            'check_tam_nuong' => false,
            'check_nguyet_ky' => false,
            'default_duration_min' => 240,
            'note' => 'Ưu tiên hàng đầu là cuối tuần, thời tiết và tình trạng sảnh tiệc để khách mời tham dự đông đủ.',
        ],
        'dang_ky_ket_hon' => [
            'label' => 'Đăng Ký Kết Hôn (UBND)',
            'short' => 'Đăng ký KH',
            'preferred_truc' => ['Định', 'Thành', 'Khai', 'Bình'],
            'avoid_truc' => ['Phá'],
            'check_tam_nuong' => false,
            'check_nguyet_ky' => false,
            'default_duration_min' => 90,
            'note' => 'Lưu ý hành chính: UBND cấp xã/phường làm việc từ Thứ 2 đến Thứ 6 (và sáng Thứ 7 tại một số địa phương). Độ tuổi pháp lý tính theo ngày sinh Dương lịch tròn.',
        ],
        'lai_mat' => [
            'label' => 'Lễ Lại Mặt (Nhị Hỷ)',
            'short' => 'Lại mặt',
            'preferred_truc' => ['Bình', 'Mãn', 'Định', 'Thành', 'Thu'],
            'avoid_truc' => [],
            'check_tam_nuong' => false,
            'check_nguyet_ky' => false,
            'default_duration_min' => 120,
            'note' => 'Thường diễn ra sau lễ cưới 1–3 ngày, hai vợ chồng về thăm nhà gái; ưu tiên sự thoải mái và sức khỏe cặp đôi.',
        ],
    ];

    /**
     * Bảng Hành Giá Đại Lợi Nguyệt (tháng Âm lịch dân gian theo Địa Chi tuổi cô dâu: 0 Tý..11 Hợi).
     * Ghi rõ nguồn dân gian & tồn tại dị bản giữa các sách Ngọc Hạp Thông Thư.
     *
     * @var array<int, array{dai_loi: list<int>, tieu_loi: list<int>}>
     */
    public const DAI_LOI_MONTHS_BY_BRIDE_BRANCH = [
        0 => ['dai_loi' => [6, 12], 'tieu_loi' => [1, 7]],   // Tý
        1 => ['dai_loi' => [5, 11], 'tieu_loi' => [4, 10]],  // Sửu
        2 => ['dai_loi' => [2, 8],  'tieu_loi' => [3, 9]],   // Dần
        3 => ['dai_loi' => [1, 7],  'tieu_loi' => [6, 12]],  // Mão
        4 => ['dai_loi' => [4, 10], 'tieu_loi' => [5, 11]],  // Thìn
        5 => ['dai_loi' => [3, 9],  'tieu_loi' => [2, 8]],   // Tỵ
        6 => ['dai_loi' => [6, 12], 'tieu_loi' => [1, 7]],   // Ngọ
        7 => ['dai_loi' => [5, 11], 'tieu_loi' => [4, 10]],  // Mùi
        8 => ['dai_loi' => [2, 8],  'tieu_loi' => [3, 9]],   // Thân
        9 => ['dai_loi' => [1, 7],  'tieu_loi' => [6, 12]],  // Dậu
        10 => ['dai_loi' => [4, 10], 'tieu_loi' => [5, 11]], // Tuất
        11 => ['dai_loi' => [3, 9],  'tieu_loi' => [2, 8]],  // Hợi
    ];

    public function __construct(
        protected VietnameseCalendarEngine $calendar,
        protected CalendarAstronomyEngine $astronomy,
        protected TraditionalAlmanacEngine $almanac,
        protected MarriageCompatibilityEngine $marriageEngine
    ) {}

    /**
     * Tìm kiếm, lọc theo Ràng buộc Thực tế (Hard Constraints) và xếp hạng minh bạch các ngày cưới trong một tháng hoặc khoảng ngày.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function findCandidateDates(array $params): array
    {
        $activityKey = (string) ($params['activity'] ?? 'le_thanh_hon');
        if (! isset(self::ACTIVITY_PROFILES[$activityKey])) {
            $activityKey = 'le_thanh_hon';
        }
        $profile = self::ACTIVITY_PROFILES[$activityKey];

        $year = max(2024, min(2035, (int) ($params['year'] ?? $this->calendar->todayVn()->year)));
        $month = max(1, min(12, (int) ($params['month'] ?? $this->calendar->todayVn()->month)));

        // Ràng buộc thực tế (Hard Constraints)
        $weekendsOnly = (bool) ($params['weekends_only'] ?? false);
        $weekdaysOnly = (bool) ($params['weekdays_only'] ?? ($activityKey === 'dang_ky_ket_hon'));
        $minPrepDays = max(0, min(730, (int) ($params['min_prep_days'] ?? 0)));
        $excludedDates = array_values(array_filter(array_map(
            static fn ($d) => trim((string) $d),
            (array) ($params['excluded_dates'] ?? [])
        )));

        // Tùy chọn truyền thống (Toggles minh bạch)
        $checkKimLau = (bool) ($params['check_kim_lau'] ?? true);
        $checkDaiLoi = (bool) ($params['check_dai_loi'] ?? false);
        $avoidClashCouple = (bool) ($params['avoid_clash_couple'] ?? true);
        $preferDaylightHours = (bool) ($params['prefer_daylight_hours'] ?? true);

        // Chuẩn hóa tuổi cô dâu / chú rể (tùy chọn)
        $groom = ! empty($params['groom_year'])
            ? $this->marriageEngine->normalizePerson([
                'year' => (int) $params['groom_year'],
                'month' => $params['groom_month'] ?? null,
                'day' => $params['groom_day'] ?? null,
                'calendar_type' => (string) ($params['groom_calendar'] ?? 'solar'),
            ], 'male')
            : null;

        $bride = ! empty($params['bride_year'])
            ? $this->marriageEngine->normalizePerson([
                'year' => (int) $params['bride_year'],
                'month' => $params['bride_month'] ?? null,
                'day' => $params['bride_day'] ?? null,
                'calendar_type' => (string) ($params['bride_calendar'] ?? 'solar'),
            ], 'female')
            : null;

        // Phân tích Kim Lâu & Đại Lợi Nguyệt (nếu có tuổi cô dâu)
        $kimLauInfo = null;
        $daiLoiInfo = null;
        if ($bride !== null) {
            $kimLauInfo = $this->evaluateKimLau($bride['lunar_year'], $year, $checkKimLau);
            $daiLoiInfo = $this->evaluateDaiLoiMonths($bride['branch_index'], $checkDaiLoi);
        }

        $today = $this->calendar->todayVn()->startOfDay();
        $minAllowedDate = $today->copy()->addDays($minPrepDays);

        $daysInMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Ho_Chi_Minh')->daysInMonth;
        $calendarDays = [];
        $eligibleCandidates = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $isoDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $carbon = Carbon::create($year, $month, $d, 0, 0, 0, 'Asia/Ho_Chi_Minh');
            $dayOfWeek = $carbon->dayOfWeek; // 0 CN, 6 T7
            $isWeekend = in_array($dayOfWeek, [0, 6], true);

            $evaluated = $this->evaluateSingleDate($d, $month, $year, $activityKey, $groom, $bride, $daiLoiInfo, $avoidClashCouple, $preferDaylightHours);

            // Kiểm tra Hard Constraints (không bao giờ vi phạm trong danh sách đề xuất)
            $hardViolations = [];
            if ($weekendsOnly && ! $isWeekend) {
                $hardViolations[] = 'Không thuộc cuối tuần (Thứ 7 / Chủ nhật)';
            }
            if ($weekdaysOnly && $isWeekend) {
                $hardViolations[] = 'Rơi vào cuối tuần (UBND thường nghỉ Thứ 7 / Chủ nhật)';
            }
            if ($minPrepDays > 0 && $carbon->lt($minAllowedDate)) {
                $hardViolations[] = "Chưa đủ {$minPrepDays} ngày chuẩn bị tối thiểu";
            }
            if (in_array($isoDate, $excludedDates, true)) {
                $hardViolations[] = 'Thuộc danh sách ngày bận/tránh của gia đình';
            }

            $evaluated['hard_constraint_violations'] = $hardViolations;
            $evaluated['passes_hard_constraints'] = $hardViolations === [];

            $calendarDays[] = $evaluated;
            if ($evaluated['passes_hard_constraints']) {
                $eligibleCandidates[] = $evaluated;
            }
        }

        // Sắp xếp danh sách ứng viên đạt Hard Constraints theo thứ tự minh bạch:
        // 1. Tier (favorable > balanced > caution)
        // 2. Không xung tuổi cô dâu / chú rể
        // 3. Số tiêu chí thuận lợi nhiều hơn, ít lưu ý hơn
        // 4. Cuối tuần (trừ đăng ký kết hôn) & có giờ Hoàng Đạo ban ngày
        usort($eligibleCandidates, static function (array $a, array $b) use ($activityKey): int {
            if ($a['tier_rank'] !== $b['tier_rank']) {
                return $b['tier_rank'] <=> $a['tier_rank'];
            }
            if ($a['couple_clash_count'] !== $b['couple_clash_count']) {
                return $a['couple_clash_count'] <=> $b['couple_clash_count'];
            }
            if ($a['favorable_count'] !== $b['favorable_count']) {
                return $b['favorable_count'] <=> $a['favorable_count'];
            }
            if ($a['caution_count'] !== $b['caution_count']) {
                return $a['caution_count'] <=> $b['caution_count'];
            }
            if ($activityKey !== 'dang_ky_ket_hon' && $a['is_weekend'] !== $b['is_weekend']) {
                return ($b['is_weekend'] ? 1 : 0) <=> ($a['is_weekend'] ? 1 : 0);
            }

            return $a['iso_date'] <=> $b['iso_date'];
        });

        return [
            'version' => self::VERSION,
            'activity' => array_merge(['key' => $activityKey], $profile),
            'year' => $year,
            'month' => $month,
            'constraints' => [
                'weekends_only' => $weekendsOnly,
                'weekdays_only' => $weekdaysOnly,
                'min_prep_days' => $minPrepDays,
                'excluded_dates' => $excludedDates,
                'check_kim_lau' => $checkKimLau,
                'check_dai_loi' => $checkDaiLoi,
                'avoid_clash_couple' => $avoidClashCouple,
                'prefer_daylight_hours' => $preferDaylightHours,
            ],
            'couple' => [
                'groom' => $groom,
                'bride' => $bride,
                'kim_lau' => $kimLauInfo,
                'dai_loi' => $daiLoiInfo,
            ],
            'summary' => [
                'total_days' => count($calendarDays),
                'eligible_count' => count($eligibleCandidates),
                'favorable_count' => count(array_filter($eligibleCandidates, static fn ($d) => $d['tier'] === 'favorable')),
                'balanced_count' => count(array_filter($eligibleCandidates, static fn ($d) => $d['tier'] === 'balanced')),
            ],
            'recommended' => array_slice($eligibleCandidates, 0, 8),
            'calendar_days' => $calendarDays,
            'disclaimer' => 'Mây Hẹn phân tách rõ ràng giữa ràng buộc thực tế (lịch rảnh, cuối tuần, thời gian chuẩn bị) và quy tắc lịch pháp truyền thống (Ngọc Hạp Thông Thư, Thập Nhị Kiến Trừ, dân gian). Không chấm điểm % may mắn giả và không thay thế sự thống nhất giữa hai gia đình.',
        ];
    }

    /**
     * Đánh giá chi tiết một ngày cụ thể cho một nghi lễ cưới hỏi.
     *
     * @param  array<string, mixed>|null  $groom
     * @param  array<string, mixed>|null  $bride
     * @param  array<string, mixed>|null  $daiLoiInfo
     * @return array<string, mixed>
     */
    public function evaluateSingleDate(
        int $day,
        int $month,
        int $year,
        string $activityKey = 'le_thanh_hon',
        ?array $groom = null,
        ?array $bride = null,
        ?array $daiLoiInfo = null,
        bool $avoidClashCouple = true,
        bool $preferDaylightHours = true
    ): array {
        $profile = self::ACTIVITY_PROFILES[$activityKey] ?? self::ACTIVITY_PROFILES['le_thanh_hon'];
        $dayData = $this->calendar->convertSolarToLunar($day, $month, $year);
        $solarTerm = $this->astronomy->getSolarTermForDate($day, $month, $year);
        $almanacData = $this->almanac->analyzeDay($dayData, $solarTerm);

        $isoDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $carbon = Carbon::create($year, $month, $day, 0, 0, 0, 'Asia/Ho_Chi_Minh');
        $isWeekend = in_array($carbon->dayOfWeek, [0, 6], true);

        $favorable = [];
        $cautions = [];
        $conflicts = [];

        // 1. Ngày Hoàng Đạo / Hắc Đạo
        $isHoangDao = ($almanacData['day_quality']['type'] ?? '') === 'hoang_dao';
        if ($isHoangDao) {
            $favorable[] = "Ngày Hoàng Đạo ({$almanacData['day_quality']['god_name']}): {$almanacData['day_quality']['desc']}";
        } else {
            $cautions[] = "Ngày Hắc Đạo ({$almanacData['day_quality']['god_name']}): vẫn có 6 khung giờ Hoàng Đạo trong ngày nếu cần chọn giờ.";
        }

        // 2. Thập Nhị Kiến Trừ (12 Trực)
        $trucName = (string) ($almanacData['truc']['name'] ?? '');
        if (in_array($trucName, $profile['preferred_truc'], true)) {
            $favorable[] = "Trực {$trucName}: thuộc nhóm Trực phù hợp với {$profile['label']}.";
        } elseif (in_array($trucName, $profile['avoid_truc'], true)) {
            $cautions[] = "Trực {$trucName}: theo Thập Nhị Kiến Trừ thường tránh dùng cho {$profile['label']}.";
        }

        // 3. Dân gian (Tam Nương, Nguyệt Kỵ)
        foreach (($almanacData['folk_cautions'] ?? []) as $fc) {
            $ruleId = (string) ($fc['rule_id'] ?? '');
            if ($ruleId === 'folk_tam_nuong' && $profile['check_tam_nuong']) {
                $cautions[] = "{$fc['name']} ({$fc['source']}).";
            } elseif ($ruleId === 'folk_nguyet_ky' && $profile['check_nguyet_ky']) {
                $cautions[] = "{$fc['name']} ({$fc['source']}).";
            }
        }

        // 4. Hành Giá Đại Lợi Nguyệt (tùy chọn theo tuổi cô dâu)
        $lunarMonth = (int) $dayData['lunar']['month'];
        $daiLoiStatus = null;
        if ($daiLoiInfo !== null && ($daiLoiInfo['enabled'] ?? false)) {
            if (in_array($lunarMonth, $daiLoiInfo['dai_loi'], true)) {
                $daiLoiStatus = 'dai_loi';
                $favorable[] = "Tháng {$lunarMonth} Âm lịch thuộc Đại Lợi Nguyệt theo tuổi cô dâu (quy tắc dân gian tùy chọn).";
            } elseif (in_array($lunarMonth, $daiLoiInfo['tieu_loi'], true)) {
                $daiLoiStatus = 'tieu_loi';
                $favorable[] = "Tháng {$lunarMonth} Âm lịch thuộc Tiểu Lợi Nguyệt theo tuổi cô dâu (quy tắc dân gian tùy chọn).";
            } else {
                $daiLoiStatus = 'neutral';
            }
        }

        // 5. Xung Địa Chi ngày với tuổi cô dâu / chú rể (Lục Xung)
        $dayBranchIdx = (int) $dayData['can_chi']['day']['branch_index'];
        $clashBranchIdx = ($dayBranchIdx + 6) % 12;
        $coupleClashes = [];
        if ($groom !== null && $groom['branch_index'] === $clashBranchIdx) {
            $coupleClashes[] = "Ngày {$dayData['can_chi']['day']['label']} Lục Xung với Địa Chi tuổi chú rể ({$groom['can_chi']}).";
        }
        if ($bride !== null && $bride['branch_index'] === $clashBranchIdx) {
            $coupleClashes[] = "Ngày {$dayData['can_chi']['day']['label']} Lục Xung với Địa Chi tuổi cô dâu ({$bride['can_chi']}).";
        }
        if ($avoidClashCouple && $coupleClashes !== []) {
            foreach ($coupleClashes as $cc) {
                $cautions[] = $cc;
            }
        }

        // 6. Khung giờ Hoàng Đạo ban ngày (07:00 - 17:00) thuận tiện di chuyển & lễ nghi
        $daylightHoangDao = array_values(array_filter(
            $almanacData['hoang_dao_hours'] ?? [],
            static fn (array $h): bool => in_array((int) ($h['branch_index'] ?? -1), [4, 5, 6, 7, 8], true) // Thìn 7-9, Tỵ 9-11, Ngọ 11-13, Mùi 13-15, Thân 15-17
        ));
        if ($preferDaylightHours && count($daylightHoangDao) >= 2) {
            $favorable[] = 'Có '.count($daylightHoangDao).' khung giờ Hoàng Đạo ban ngày (07:00–17:00), thuận tiện sắp xếp lịch di chuyển hai họ.';
        }

        // 7. Ghi nhận xung đột quy tắc minh bạch
        if ($favorable !== [] && $cautions !== []) {
            $conflicts[] = 'Ngày này có cả tiêu chí thuận lợi và lưu ý truyền thống đan xen; gia đình nên cân đối cùng lịch rảnh của khách mời và thời gian đặt sảnh.';
        }

        // Phân bậc minh bạch (không chấm % giả)
        $tier = 'balanced';
        $tierRank = 2;
        $tierLabel = 'Cân nhắc linh hoạt';
        if ($cautions === [] && count($favorable) >= 2) {
            $tier = 'favorable';
            $tierRank = 3;
            $tierLabel = 'Thuận lợi';
        } elseif ($favorable === [] || count($cautions) >= 3 || ($avoidClashCouple && $coupleClashes !== [])) {
            $tier = 'caution';
            $tierRank = 1;
            $tierLabel = 'Có lưu ý cần cân nhắc';
        }

        // Gợi ý khung giờ thực tế có đệm di chuyển 30 phút
        $ceremonyWindows = $this->buildPracticalCeremonyWindows($daylightHoangDao, $profile['default_duration_min']);

        return [
            'iso_date' => $isoDate,
            'solar' => $dayData['solar'],
            'lunar' => $dayData['lunar'],
            'weekday' => $dayData['solar']['weekday'] ?? $carbon->isoFormat('dddd'),
            'is_weekend' => $isWeekend,
            'can_chi_day' => $dayData['can_chi']['day']['label'],
            'can_chi_month' => $dayData['can_chi']['month']['label'],
            'can_chi_year' => $dayData['can_chi']['year']['label'],
            'day_quality' => $almanacData['day_quality'],
            'truc' => $almanacData['truc'],
            'mansion' => $almanacData['mansion'],
            'luc_dieu' => $almanacData['luc_dieu'],
            'directions' => $almanacData['directions'],
            'clash_branch' => $almanacData['clash_branch'],
            'hoang_dao_hours' => $almanacData['hoang_dao_hours'],
            'daylight_hoang_dao_hours' => $daylightHoangDao,
            'ceremony_windows' => $ceremonyWindows,
            'dai_loi_status' => $daiLoiStatus,
            'couple_clashes' => $coupleClashes,
            'couple_clash_count' => count($coupleClashes),
            'favorable_rules' => $favorable,
            'caution_rules' => $cautions,
            'conflicts' => $conflicts,
            'favorable_count' => count($favorable),
            'caution_count' => count($cautions),
            'tier' => $tier,
            'tier_rank' => $tierRank,
            'tier_label' => $tierLabel,
        ];
    }

    /**
     * So sánh song song tối đa 5 ngày ứng viên.
     *
     * @param  list<string>  $isoDates
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function compareCandidateDates(array $isoDates, array $context = []): array
    {
        $cleanDates = array_slice(array_values(array_unique(array_filter($isoDates))), 0, 5);
        if ($cleanDates === []) {
            throw new InvalidArgumentException('Vui lòng chọn ít nhất 1 ngày ứng viên để so sánh.');
        }

        $activityKey = (string) ($context['activity'] ?? 'le_thanh_hon');
        $groom = ! empty($context['groom_year'])
            ? $this->marriageEngine->normalizePerson(['year' => (int) $context['groom_year']], 'male')
            : null;
        $bride = ! empty($context['bride_year'])
            ? $this->marriageEngine->normalizePerson(['year' => (int) $context['bride_year']], 'female')
            : null;
        $daiLoiInfo = $bride !== null
            ? $this->evaluateDaiLoiMonths($bride['branch_index'], (bool) ($context['check_dai_loi'] ?? false))
            : null;

        $compared = [];
        foreach ($cleanDates as $iso) {
            if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $iso, $m)) {
                continue;
            }
            $compared[] = $this->evaluateSingleDate(
                (int) $m[3],
                (int) $m[2],
                (int) $m[1],
                $activityKey,
                $groom,
                $bride,
                $daiLoiInfo
            );
        }

        return [
            'activity' => self::ACTIVITY_PROFILES[$activityKey] ?? self::ACTIVITY_PROFILES['le_thanh_hon'],
            'candidates' => $compared,
            'count' => count($compared),
        ];
    }

    /**
     * Tính tuổi Kim Lâu năm cưới theo tuổi mụ cô dâu (mod 9 ∈ {1, 3, 6, 8}) kèm công tắc tùy chọn.
     *
     * @return array<string, mixed>
     */
    public function evaluateKimLau(int $brideLunarYear, int $weddingYear, bool $enabled = true): array
    {
        $tuoiMu = $weddingYear - $brideLunarYear + 1;
        $remainder = (($tuoiMu % 9) + 9) % 9;
        $kimLauTypes = [
            1 => 'Kim Lâu Thân',
            3 => 'Kim Lâu Thê',
            6 => 'Kim Lâu Tử',
            8 => 'Kim Lâu Lục Súc',
        ];
        $isKimLau = isset($kimLauTypes[$remainder]);

        return [
            'enabled' => $enabled,
            'wedding_year' => $weddingYear,
            'bride_lunar_year' => $brideLunarYear,
            'tuoi_mu' => $tuoiMu,
            'remainder' => $remainder,
            'is_kim_lau' => $isKimLau,
            'type' => $kimLauTypes[$remainder] ?? null,
            'label' => $isKimLau
                ? "Tuổi mụ {$tuoiMu} rơi vào {$kimLauTypes[$remainder]} (dư {$remainder} khi chia 9)"
                : "Tuổi mụ {$tuoiMu} không phạm Kim Lâu theo quy tắc chia 9",
            'note' => 'Quy tắc Kim Lâu dân gian chỉ mang tính tham khảo văn hóa (nhiều gia đình hiện đại chọn qua Đông Chí hoặc ưu tiên thời điểm sẵn sàng của cặp đôi). TUYỆT ĐỐI không dùng tuổi mụ để xác định độ tuổi đăng ký kết hôn hợp pháp.',
        ];
    }

    /**
     * Tra cứu Hành Giá Đại Lợi Nguyệt (quy tắc dân gian tùy chọn theo Địa Chi tuổi cô dâu).
     *
     * @return array<string, mixed>
     */
    public function evaluateDaiLoiMonths(int $brideBranchIdx, bool $enabled = false): array
    {
        $rule = self::DAI_LOI_MONTHS_BY_BRIDE_BRANCH[$brideBranchIdx] ?? ['dai_loi' => [], 'tieu_loi' => []];
        $branchName = BaziRegistry::BRANCHES[$brideBranchIdx]['name'] ?? '';

        return [
            'enabled' => $enabled,
            'bride_branch' => $branchName,
            'dai_loi' => $rule['dai_loi'],
            'tieu_loi' => $rule['tieu_loi'],
            'source' => 'Hành Giá Đại Lợi Nguyệt — quy ước dân gian phổ biến trong Ngọc Hạp Thông Thư (tồn tại dị bản theo từng vùng miền; chỉ nên dùng làm tham khảo phụ).',
        ];
    }

    /**
     * Xây dựng gợi ý khung giờ lễ thực tế có tính đệm di chuyển 30 phút.
     *
     * @param  list<array<string, mixed>>  $daylightHoangDao
     * @return list<array{slot: string, range: string, suggested_departure: string, note: string}>
     */
    protected function buildPracticalCeremonyWindows(array $daylightHoangDao, int $durationMin): array
    {
        $windows = [];
        foreach (array_slice($daylightHoangDao, 0, 3) as $h) {
            $branchIdx = (int) ($h['branch_index'] ?? 4);
            $startHour = match ($branchIdx) {
                4 => 7,
                5 => 9,
                6 => 11,
                7 => 13,
                8 => 15,
                default => 9,
            };
            $depHour = max(5, $startHour - 1);
            $windows[] = [
                'slot' => ($h['branch_name'] ?? '').' ('.($h['god_name'] ?? 'Hoàng Đạo').')',
                'range' => sprintf('%02d:00 – %02d:00', $startHour, $startHour + 2),
                'suggested_departure' => sprintf('%02d:30', $depHour),
                'note' => "Dự trù khởi hành trước 30 phút và tổng thời lượng nghi lễ khoảng {$durationMin} phút.",
            ];
        }

        return $windows;
    }
}
