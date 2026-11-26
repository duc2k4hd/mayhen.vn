<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CalendarFamilyEvent;
use App\Services\Calendar\CalendarAiInterpreterService;
use App\Services\Calendar\CalendarAstronomyEngine;
use App\Services\Calendar\TraditionalAlmanacEngine;
use App\Services\Calendar\VietnameseCalendarEngine;
use App\Services\Calendar\VietnameseHolidayEngine;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Controller Mây Hẹn – Vạn Niên Lịch (/lich-am).
 *
 * - Tra cứu Lịch Âm hôm nay, Lịch tháng, Lịch năm, Đổi ngày 2 chiều, 24 Tiết Khí, Pha Mặt Trăng 3D,
 *   Đồng hồ 12 thời thần, Xem ngày theo mục đích, Tính ngày giỗ & Lịch gia đình riêng tư.
 * - Mọi phép tính đều lấy từ VietnameseCalendarEngine (100% độc lập AI).
 */
class CalendarController extends Controller
{
    public function __construct(
        protected VietnameseCalendarEngine $calendar,
        protected CalendarAstronomyEngine $astronomy,
        protected TraditionalAlmanacEngine $almanac,
        protected VietnameseHolidayEngine $holidays,
        protected CalendarAiInterpreterService $ai
    ) {}

    /**
     * Trang chính SEO & Lịch Âm Hôm Nay (/lich-am).
     */
    public function index(Request $request, ?string $focusSection = null): View
    {
        $today = $this->calendar->todayVn();
        $city = (string) $request->query('city', 'ha-noi');
        $mode = (string) $request->query('mode', VietnameseCalendarEngine::MODE_MODERN);
        if (! in_array($mode, [VietnameseCalendarEngine::MODE_MODERN, VietnameseCalendarEngine::MODE_HISTORICAL], true)) {
            $mode = VietnameseCalendarEngine::MODE_MODERN;
        }

        $dayBundle = $this->buildDayBundle($today->day, $today->month, $today->year, $city, $mode);
        $monthGrid = $this->buildMonthGrid($today->year, $today->month, $mode);
        $yearTerms = $this->astronomy->getYearSolarTerms($today->year);
        $countdowns = $this->holidays->getUpcomingCountdowns($today->day, $today->month, $today->year);
        $dailyExplain = $this->ai->getDailyPublicExplanation($dayBundle, allowAi: false);

        $pageMeta = match ($focusSection) {
            'converter' => [
                'title' => 'Đổi Ngày Âm Dương Online Chính Xác 1900–2100 (Có Tháng Nhuận)',
                'h1' => 'Đổi Ngày Âm Dương Online Chính Xác',
                'canonical' => route('calendar.converter'),
                'meta_description' => 'Công cụ đổi ngày Dương sang Âm và Âm sang Dương chuẩn lịch Việt Nam UTC+7 (1900–2100), hỗ trợ tháng nhuận, độ dài tháng 29/30 ngày và Can Chi đầy đủ.',
            ],
            'solar_terms' => [
                'title' => '24 Tiết Khí Trong Năm '.$today->year.' — Thời Điểm Giao Tiết Chính Xác',
                'h1' => '24 Tiết Khí Năm '.$today->year.' & Vòng Hoàng Đạo',
                'canonical' => route('calendar.solar_terms'),
                'meta_description' => 'Tra cứu chính xác thời điểm giao 24 Tiết Khí năm '.$today->year.' theo múi giờ Việt Nam UTC+7 (Lập Xuân, Xuân Phân, Hạ Chí, Thu Phân, Đông Chí) cùng ý nghĩa văn hóa.',
            ],
            'anniversary' => [
                'title' => 'Tính Ngày Giỗ Âm Lịch & Sinh Nhật Âm Lịch Theo Năm Chính Xác',
                'h1' => 'Tính Ngày Giỗ & Sinh Nhật Âm Lịch Nhiều Năm',
                'canonical' => route('calendar.anniversary'),
                'meta_description' => 'Quy đổi chính xác ngày giỗ và sinh nhật Âm lịch sang Dương lịch cho nhiều năm tiếp theo, hỗ trợ quy ước gia đình khi gặp tháng nhuận hoặc tháng thiếu 29 ngày.',
            ],
            default => [
                'title' => 'Xem Lịch Âm & Lịch Vạn Niên Online Miễn Phí Hôm Nay',
                'h1' => 'Xem Lịch Âm & Lịch Vạn Niên Online Miễn Phí Hôm Nay',
                'canonical' => route('calendar.index'),
                'meta_description' => sprintf(
                    'Xem Lịch Âm hôm nay %s (%s): đổi ngày âm dương chuẩn Việt Nam UTC+7, giờ Hoàng Đạo, 12 Trực, 28 Tú, pha Mặt Trăng 3D, 24 tiết khí và xem ngày cưới hỏi miễn phí tại Mây Hẹn.',
                    $dayBundle['day']['solar']['label'],
                    $dayBundle['day']['lunar']['label_short']
                ),
            ],
        };

        return view('client.pages.calendar.index', [
            'todayIso' => $today->format('Y-m-d'),
            'dayBundle' => $dayBundle,
            'monthGrid' => $monthGrid,
            'yearTerms' => $yearTerms,
            'countdowns' => $countdowns,
            'dailyExplain' => $dailyExplain,
            'cities' => CalendarAstronomyEngine::VN_CITIES,
            'activities' => TraditionalAlmanacEngine::ACTIVITY_PROFILES,
            'fiveElements' => VietnameseCalendarEngine::FIVE_ELEMENTS,
            'focusSection' => $focusSection,
            'pageMeta' => $pageMeta,
            'isIndexable' => true,
        ]);
    }

    /**
     * Xem chi tiết một ngày cụ thể (/lich-am/ngay/{date}).
     */
    public function showDay(Request $request, string $date): View
    {
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) {
            abort(404);
        }
        $y = (int) $m[1];
        $mo = (int) $m[2];
        $d = (int) $m[3];
        if (! checkdate($mo, $d, $y) || $y < VietnameseCalendarEngine::MIN_YEAR || $y > VietnameseCalendarEngine::MAX_YEAR) {
            abort(404);
        }

        $today = $this->calendar->todayVn();
        $city = (string) $request->query('city', 'ha-noi');
        $dayBundle = $this->buildDayBundle($d, $mo, $y, $city);
        $monthGrid = $this->buildMonthGrid($y, $mo);
        $yearTerms = $this->astronomy->getYearSolarTerms($y);
        $countdowns = $this->holidays->getUpcomingCountdowns($d, $mo, $y);
        $dailyExplain = $this->ai->getDailyPublicExplanation($dayBundle, allowAi: false);

        // Chống crawl trap 73.000 trang: chỉ index các ngày trong vòng năm hiện tại +/- 1
        $isIndexable = abs($y - $today->year) <= 1;

        $pageMeta = [
            'title' => sprintf('Lịch Âm Ngày %02d/%02d/%04d (%s) — Lịch Vạn Niên Mây Hẹn', $d, $mo, $y, $dayBundle['day']['lunar']['label_short']),
            'h1' => sprintf('Lịch Âm Ngày %02d/%02d/%04d — %s', $d, $mo, $y, $dayBundle['day']['lunar']['label_full']),
            'canonical' => route('calendar.day', ['date' => $date]),
            'meta_description' => sprintf(
                'Tra cứu lịch âm ngày %02d/%02d/%04d (%s): ngày %s, %s (%s), Trực %s, giờ Hoàng Đạo và tiết khí %s.',
                $d,
                $mo,
                $y,
                $dayBundle['day']['lunar']['label_short'],
                $dayBundle['day']['can_chi']['day']['label'],
                $dayBundle['almanac']['day_quality']['label'],
                $dayBundle['almanac']['day_quality']['god_name'],
                $dayBundle['almanac']['truc']['name'],
                $dayBundle['solar_term']['current']['name']
            ),
        ];

        return view('client.pages.calendar.index', [
            'todayIso' => $today->format('Y-m-d'),
            'dayBundle' => $dayBundle,
            'monthGrid' => $monthGrid,
            'yearTerms' => $yearTerms,
            'countdowns' => $countdowns,
            'dailyExplain' => $dailyExplain,
            'cities' => CalendarAstronomyEngine::VN_CITIES,
            'activities' => TraditionalAlmanacEngine::ACTIVITY_PROFILES,
            'fiveElements' => VietnameseCalendarEngine::FIVE_ELEMENTS,
            'focusSection' => null,
            'pageMeta' => $pageMeta,
            'isIndexable' => $isIndexable,
        ]);
    }

    /**
     * Lịch Năm 12 Tháng Dương Lịch + 12/13 Tháng Âm Lịch (/lich-am/nam/{year}).
     */
    public function showYear(int $year): View
    {
        if ($year < VietnameseCalendarEngine::MIN_YEAR || $year > VietnameseCalendarEngine::MAX_YEAR) {
            abort(404);
        }

        $lunarMonths = $this->calendar->getLunarYearMonths($year);
        $leapMonth = $this->calendar->getLeapMonth($year);
        $solarTerms = $this->astronomy->getYearSolarTerms($year);
        $monthsGrid = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthsGrid[$m] = $this->buildMonthGrid($year, $m);
        }
        $tetDay = $this->calendar->convertLunarToSolar(1, 1, $year, false);
        $today = $this->calendar->todayVn();

        return view('client.pages.calendar.year', [
            'year' => $year,
            'tetDay' => $tetDay,
            'lunarMonths' => $lunarMonths,
            'leapMonth' => $leapMonth,
            'solarTerms' => $solarTerms,
            'monthsGrid' => $monthsGrid,
            'isIndexable' => abs($year - $today->year) <= 5,
        ]);
    }

    /**
     * Lịch Gia Đình Riêng Tư (/lich-am/lich-cua-toi — yêu cầu đăng nhập, noindex).
     */
    public function myEvents(Request $request): View
    {
        $user = $request->user();
        $today = $this->calendar->todayVn();
        $events = CalendarFamilyEvent::where('user_id', $user->id)
            ->orderBy('event_month')
            ->orderBy('event_day')
            ->get();

        $upcoming = [];
        foreach ($events as $ev) {
            $nextSolar = null;
            if ($ev->calendar_type === 'lunar') {
                $calc = $this->holidays->calculateLunarAnniversary(
                    $ev->event_day,
                    $ev->event_month,
                    $today->year,
                    2,
                    $ev->short_month_rule,
                    $ev->leap_month_rule
                );
                foreach ($calc as $row) {
                    if ($row['solar_iso'] >= $today->format('Y-m-d')) {
                        $nextSolar = $row;
                        break;
                    }
                }
            } else {
                $y = $today->year;
                $day = min($ev->event_day, $this->calendar->getGregorianMonthLength($y, $ev->event_month));
                $iso = sprintf('%04d-%02d-%02d', $y, $ev->event_month, $day);
                if ($iso < $today->format('Y-m-d')) {
                    $y++;
                    $day = min($ev->event_day, $this->calendar->getGregorianMonthLength($y, $ev->event_month));
                    $iso = sprintf('%04d-%02d-%02d', $y, $ev->event_month, $day);
                }
                $info = $this->calendar->convertSolarToLunar($day, $ev->event_month, $y);
                $nextSolar = [
                    'solar_date' => $info['solar']['label'],
                    'solar_iso' => $iso,
                    'weekday' => $info['solar']['weekday_name'],
                    'lunar_label' => $info['lunar']['label_short'].' Âm lịch',
                    'note' => null,
                ];
            }

            $daysLeft = $nextSolar
                ? max(0, (int) Carbon::parse($today->format('Y-m-d'))->diffInDays(Carbon::parse($nextSolar['solar_iso']), false))
                : null;

            $upcoming[] = [
                'event' => $ev,
                'next' => $nextSolar,
                'days_left' => $daysLeft,
            ];
        }

        usort($upcoming, fn (array $a, array $b): int => ($a['days_left'] ?? 9999) <=> ($b['days_left'] ?? 9999));

        return view('client.pages.calendar.my-events', [
            'upcoming' => $upcoming,
            'todayIso' => $today->format('Y-m-d'),
        ]);
    }

    public function storeEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:160'],
            'event_type' => ['required', 'in:birthday,anniversary,death_anniversary,wedding,family'],
            'calendar_type' => ['required', 'in:lunar,solar'],
            'event_day' => ['required', 'integer', 'min:1', 'max:31'],
            'event_month' => ['required', 'integer', 'min:1', 'max:12'],
            'event_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'is_leap_month' => ['nullable', 'boolean'],
            'short_month_rule' => ['nullable', 'in:last_day_of_month,skip'],
            'leap_month_rule' => ['nullable', 'in:regular_only,leap_if_exists,both'],
            'notes' => ['nullable', 'string', 'max:600'],
        ]);

        if ($validated['calendar_type'] === 'solar') {
            $checkYear = (int) ($validated['event_year'] ?: 2024);
            if (! checkdate((int) $validated['event_month'], (int) $validated['event_day'], $checkYear)) {
                return response()->json(['success' => false, 'message' => 'Ngày Dương lịch không hợp lệ.'], 422);
            }
        } elseif ((int) $validated['event_day'] > 30) {
            return response()->json(['success' => false, 'message' => 'Ngày Âm lịch chỉ từ 1 đến 30.'], 422);
        }

        $event = CalendarFamilyEvent::create([
            'user_id' => $request->user()->id,
            'title' => trim($validated['title']),
            'event_type' => $validated['event_type'],
            'calendar_type' => $validated['calendar_type'],
            'event_day' => (int) $validated['event_day'],
            'event_month' => (int) $validated['event_month'],
            'event_year' => $validated['event_year'] ?? null,
            'is_leap_month' => (bool) ($validated['is_leap_month'] ?? false),
            'is_recurring' => true,
            'short_month_rule' => $validated['short_month_rule'] ?? 'last_day_of_month',
            'leap_month_rule' => $validated['leap_month_rule'] ?? 'regular_only',
            'notes' => isset($validated['notes']) ? trim($validated['notes']) : null,
        ]);

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function destroyEvent(Request $request, int $id): JsonResponse
    {
        $event = CalendarFamilyEvent::where('id', $id)->firstOrFail();
        if ((int) $event->user_id !== (int) $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }
        $event->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Xuất file ICS Lịch Gia Đình: quy đổi chính xác sang ngày Dương lịch từng năm trong 5 năm tới (không dùng RRULE Gregory sai cho sự kiện Âm lịch).
     */
    public function exportIcs(Request $request): Response
    {
        $user = $request->user();
        $today = $this->calendar->todayVn();
        $events = CalendarFamilyEvent::where('user_id', $user->id)->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//May Hen//Van Nien Lich//VI',
            'CALSCALE:GREGORIAN',
            'X-WR-CALNAME:Lich Gia Dinh May Hen',
        ];

        foreach ($events as $ev) {
            if ($ev->calendar_type === 'lunar') {
                $occ = $this->holidays->calculateLunarAnniversary(
                    $ev->event_day,
                    $ev->event_month,
                    $today->year,
                    5,
                    $ev->short_month_rule,
                    $ev->leap_month_rule
                );
                foreach ($occ as $row) {
                    $dtStart = str_replace('-', '', $row['solar_iso']);
                    $dtEnd = Carbon::parse($row['solar_iso'])->addDay()->format('Ymd');
                    $uid = "mayhen-ev-{$ev->id}-{$row['lunar_year']}@mayhen.vn";
                    $summary = str_replace(["\r", "\n", ','], ' ', $ev->title.' ('.$row['lunar_label'].')');
                    $lines[] = 'BEGIN:VEVENT';
                    $lines[] = "UID:{$uid}";
                    $lines[] = "DTSTART;VALUE=DATE:{$dtStart}";
                    $lines[] = "DTEND;VALUE=DATE:{$dtEnd}";
                    $lines[] = "SUMMARY:{$summary}";
                    $lines[] = 'END:VEVENT';
                }
            } else {
                for ($y = $today->year; $y < $today->year + 5; $y++) {
                    $d = min($ev->event_day, $this->calendar->getGregorianMonthLength($y, $ev->event_month));
                    $dtStart = sprintf('%04d%02d%02d', $y, $ev->event_month, $d);
                    $dtEnd = Carbon::create($y, $ev->event_month, $d)->addDay()->format('Ymd');
                    $uid = "mayhen-ev-{$ev->id}-{$y}@mayhen.vn";
                    $summary = str_replace(["\r", "\n", ','], ' ', $ev->title);
                    $lines[] = 'BEGIN:VEVENT';
                    $lines[] = "UID:{$uid}";
                    $lines[] = "DTSTART;VALUE=DATE:{$dtStart}";
                    $lines[] = "DTEND;VALUE=DATE:{$dtEnd}";
                    $lines[] = "SUMMARY:{$summary}";
                    $lines[] = 'END:VEVENT';
                }
            }
        }

        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="mayhen-lich-gia-dinh.ics"',
        ]);
    }

    /**
     * API: Trả đầy đủ dữ liệu 1 ngày cho lật lịch 3D mượt không tải lại trang.
     */
    public function apiDay(Request $request): JsonResponse
    {
        $date = (string) $request->query('date', $this->calendar->todayVn()->format('Y-m-d'));
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) {
            return response()->json(['success' => false, 'message' => 'Định dạng ngày không hợp lệ (YYYY-MM-DD).'], 422);
        }
        $y = (int) $m[1];
        $mo = (int) $m[2];
        $d = (int) $m[3];
        if (! checkdate($mo, $d, $y) || $y < VietnameseCalendarEngine::MIN_YEAR || $y > VietnameseCalendarEngine::MAX_YEAR) {
            return response()->json(['success' => false, 'message' => 'Ngày Dương lịch không tồn tại hoặc ngoài phạm vi 1900–2100.'], 422);
        }

        $city = (string) $request->query('city', 'ha-noi');
        $mode = (string) $request->query('mode', VietnameseCalendarEngine::MODE_MODERN);
        $bundle = $this->buildDayBundle($d, $mo, $y, $city, $mode);
        $explain = $this->ai->getDailyPublicExplanation($bundle, allowAi: false);

        return response()->json([
            'success' => true,
            'bundle' => $bundle,
            'explain' => $explain,
        ]);
    }

    /**
     * API: Trả lưới lịch tháng tương tác.
     */
    public function apiMonth(Request $request): JsonResponse
    {
        $year = (int) $request->query('year', $this->calendar->todayVn()->year);
        $month = (int) $request->query('month', $this->calendar->todayVn()->month);
        if ($year < VietnameseCalendarEngine::MIN_YEAR || $year > VietnameseCalendarEngine::MAX_YEAR || $month < 1 || $month > 12) {
            return response()->json(['success' => false, 'message' => 'Tháng hoặc năm ngoài phạm vi 1900–2100.'], 422);
        }
        $mode = (string) $request->query('mode', VietnameseCalendarEngine::MODE_MODERN);

        return response()->json([
            'success' => true,
            'month_grid' => $this->buildMonthGrid($year, $month, $mode),
        ]);
    }

    /**
     * API: Chuyển đổi 2 chiều Dương <-> Âm nghiêm ngặt.
     */
    public function apiConvert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'direction' => ['required', 'in:solar_to_lunar,lunar_to_solar'],
            'day' => ['required', 'integer', 'min:1', 'max:31'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'is_leap_month' => ['nullable', 'boolean'],
            'mode' => ['nullable', 'in:vn_modern_utc7,vn_historical'],
        ]);

        $mode = (string) ($validated['mode'] ?? VietnameseCalendarEngine::MODE_MODERN);

        try {
            if ($validated['direction'] === 'solar_to_lunar') {
                $dayData = $this->calendar->convertSolarToLunar(
                    (int) $validated['day'],
                    (int) $validated['month'],
                    (int) $validated['year'],
                    $mode
                );
            } else {
                $dayData = $this->calendar->convertLunarToSolar(
                    (int) $validated['day'],
                    (int) $validated['month'],
                    (int) $validated['year'],
                    (bool) ($validated['is_leap_month'] ?? false),
                    $mode
                );
            }

            $solarTerm = $this->astronomy->getSolarTermForDate($dayData['solar']['day'], $dayData['solar']['month'], $dayData['solar']['year']);
            $almanac = $this->almanac->analyzeDay($dayData, $solarTerm);

            return response()->json([
                'success' => true,
                'result' => $dayData,
                'almanac' => $almanac,
                'solar_term' => $solarTerm['current'],
                'leap_month_of_year' => $this->calendar->getLeapMonth($dayData['lunar']['year'], $mode),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * API: Tìm ngày phù hợp theo mục đích (ưu tiên điều kiện thực tế như cuối tuần, không chấm % giả).
     */
    public function apiFindDates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'activity' => ['required', 'string', 'max:40'],
            'from_date' => ['required', 'date_format:Y-m-d'],
            'to_date' => ['required', 'date_format:Y-m-d'],
            'weekends_only' => ['nullable', 'boolean'],
        ]);

        $from = Carbon::createFromFormat('Y-m-d', $validated['from_date']);
        $to = Carbon::createFromFormat('Y-m-d', $validated['to_date']);
        if ($to->lt($from) || $from->diffInDays($to) > 95) {
            return response()->json(['success' => false, 'message' => 'Khoảng tìm kiếm hợp lệ tối đa 95 ngày (khoảng 3 tháng).'], 422);
        }

        $weekendsOnly = (bool) ($validated['weekends_only'] ?? false);
        $activity = $validated['activity'];
        $matches = [];

        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            if ($cursor->year < VietnameseCalendarEngine::MIN_YEAR || $cursor->year > VietnameseCalendarEngine::MAX_YEAR) {
                $cursor->addDay();

                continue;
            }
            if ($weekendsOnly && ! in_array($cursor->dayOfWeek, [0, 6], true)) {
                $cursor->addDay();

                continue;
            }

            $dayData = $this->calendar->convertSolarToLunar($cursor->day, $cursor->month, $cursor->year);
            $term = $this->astronomy->getSolarTermForDate($cursor->day, $cursor->month, $cursor->year);
            $alm = $this->almanac->analyzeDay($dayData, $term);
            $eval = $this->almanac->evaluateDateForActivity($alm, $activity);

            if ($eval['status'] !== 'caution') {
                $matches[] = [
                    'solar_iso' => $dayData['solar']['iso'],
                    'solar_label' => $dayData['solar']['label'],
                    'weekday' => $dayData['solar']['weekday_name'],
                    'lunar_label' => $dayData['lunar']['label_short'],
                    'day_can_chi' => $dayData['can_chi']['day']['label'],
                    'day_quality' => $alm['day_quality']['label'].' ('.$alm['day_quality']['god_name'].')',
                    'truc' => 'Trực '.$alm['truc']['name'],
                    'hoang_dao_hours' => array_map(fn ($h) => $h['branch'].' ('.$h['range'].')', array_slice($alm['hoang_dao_hours'], 0, 4)),
                    'evaluation' => $eval,
                ];
            }

            $cursor->addDay();
        }

        // Sắp xếp ngày thuận lợi lên đầu
        usort($matches, fn (array $a, array $b): int => ($a['evaluation']['status'] === 'favorable' ? 0 : 1) <=> ($b['evaluation']['status'] === 'favorable' ? 0 : 1));

        return response()->json([
            'success' => true,
            'count' => count($matches),
            'dates' => array_slice($matches, 0, 24),
        ]);
    }

    /**
     * API: Tính Ngày Giỗ / Sinh Nhật Âm Lịch cho nhiều năm tới.
     */
    public function apiAnniversary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lunar_day' => ['required', 'integer', 'min:1', 'max:30'],
            'lunar_month' => ['required', 'integer', 'min:1', 'max:12'],
            'from_year' => ['nullable', 'integer', 'min:1900', 'max:2095'],
            'years_count' => ['nullable', 'integer', 'min:1', 'max:12'],
            'short_month_rule' => ['nullable', 'in:last_day_of_month,skip'],
            'leap_month_rule' => ['nullable', 'in:regular_only,leap_if_exists,both'],
        ]);

        $fromYear = (int) ($validated['from_year'] ?? $this->calendar->todayVn()->year);
        $rows = $this->holidays->calculateLunarAnniversary(
            (int) $validated['lunar_day'],
            (int) $validated['lunar_month'],
            $fromYear,
            (int) ($validated['years_count'] ?? 6),
            (string) ($validated['short_month_rule'] ?? 'last_day_of_month'),
            (string) ($validated['leap_month_rule'] ?? 'regular_only')
        );

        return response()->json(['success' => true, 'rows' => $rows]);
    }

    /**
     * API: AI Giải Thích & Hỏi Đáp Vạn Niên Lịch (căn cứ 100% dữ liệu Engine).
     */
    public function apiAskAi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'question' => ['required', 'string', 'min:3', 'max:360'],
        ]);

        [$y, $mo, $d] = array_map('intval', explode('-', $validated['date']));
        if (! checkdate($mo, $d, $y) || $y < VietnameseCalendarEngine::MIN_YEAR || $y > VietnameseCalendarEngine::MAX_YEAR) {
            return response()->json(['success' => false, 'message' => 'Ngày tra cứu không hợp lệ.'], 422);
        }

        $bundle = $this->buildDayBundle($d, $mo, $y);
        $res = $this->ai->answerQuestion(trim($validated['question']), $bundle);

        return response()->json([
            'success' => true,
            'answer' => $res['answer'],
            'ai_powered' => $res['ai_powered'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildDayBundle(int $day, int $month, int $year, string $city = 'ha-noi', string $mode = VietnameseCalendarEngine::MODE_MODERN): array
    {
        $cacheKey = sprintf('mayhen_cal_day_%04d%02d%02d_%s_%s_%s', $year, $month, $day, $city, $mode, VietnameseCalendarEngine::ENGINE_VERSION);

        return Cache::remember($cacheKey, 21600, function () use ($day, $month, $year, $city, $mode): array {
            $dayData = $this->calendar->convertSolarToLunar($day, $month, $year, $mode);
            $solarTerm = $this->astronomy->getSolarTermForDate($day, $month, $year);
            $moonPhase = $this->astronomy->getMoonPhaseForDate($day, $month, $year);
            $sunTimes = $this->astronomy->getSunTimes($day, $month, $year, $city);
            $almanac = $this->almanac->analyzeDay($dayData, $solarTerm);
            $holidays = $this->holidays->getHolidaysForDay($dayData);

            return [
                'day' => $dayData,
                'solar_term' => $solarTerm,
                'moon_phase' => $moonPhase,
                'sun_times' => $sunTimes,
                'almanac' => $almanac,
                'holidays' => $holidays,
                'wedding_eval' => $this->almanac->evaluateDateForActivity($almanac, 'cuoi_hoi'),
            ];
        });
    }

    /**
     * Xây dựng lưới Lịch Tháng (khởi Thứ Hai = cột 0 .. Chủ Nhật = cột 6).
     *
     * @return array<string, mixed>
     */
    protected function buildMonthGrid(int $year, int $month, string $mode = VietnameseCalendarEngine::MODE_MODERN): array
    {
        $cacheKey = sprintf('mayhen_cal_month_%04d%02d_%s_%s', $year, $month, $mode, VietnameseCalendarEngine::ENGINE_VERSION);

        return Cache::remember($cacheKey, 43200, function () use ($year, $month, $mode): array {
            $daysInMonth = $this->calendar->getGregorianMonthLength($year, $month);
            $firstJdn = $this->calendar->jdFromDate(1, $month, $year);
            $firstWeekdaySun0 = ($firstJdn + 1) % 7; // 0 = CN, 1 = T2..6 = T7
            $leadingBlanks = ($firstWeekdaySun0 + 6) % 7; // Chuyển sang T2 = 0 .. CN = 6

            $cells = [];
            for ($i = 0; $i < $leadingBlanks; $i++) {
                $cells[] = null;
            }

            $solarTermsInYear = $this->astronomy->getYearSolarTerms($year);
            $termByIso = [];
            foreach ($solarTermsInYear as $t) {
                $termByIso[$t['date_iso']] = $t['name'];
            }

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dayData = $this->calendar->convertSolarToLunar($d, $month, $year, $mode);
                $term = $this->astronomy->getSolarTermForDate($d, $month, $year);
                $alm = $this->almanac->analyzeDay($dayData, $term);
                $hols = $this->holidays->getHolidaysForDay($dayData);
                $iso = $dayData['solar']['iso'];

                $cells[] = [
                    'iso' => $iso,
                    'solar_day' => $d,
                    'lunar_day' => $dayData['lunar']['day'],
                    'lunar_month' => $dayData['lunar']['month'],
                    'is_leap' => $dayData['lunar']['is_leap_month'],
                    'lunar_short' => $dayData['lunar']['day'] === 1
                        ? sprintf('1/%d%s', $dayData['lunar']['month'], $dayData['lunar']['is_leap_month'] ? 'N' : '')
                        : (string) $dayData['lunar']['day'],
                    'is_hoang_dao' => $alm['day_quality']['type'] === 'hoang_dao',
                    'is_weekend' => in_array($dayData['solar']['weekday_index'], [0, 6], true),
                    'holiday_name' => $hols[0]['name'] ?? null,
                    'is_public_holiday' => ! empty(array_filter($hols, fn ($h) => $h['is_public_holiday'])),
                    'solar_term_name' => $termByIso[$iso] ?? null,
                ];
            }

            return [
                'year' => $year,
                'month' => $month,
                'label' => sprintf('Tháng %02d / %04d', $month, $year),
                'cells' => $cells,
            ];
        });
    }
}
