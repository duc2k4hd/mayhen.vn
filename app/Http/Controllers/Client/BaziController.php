<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\BaziChart;
use App\Models\BaziFollowup;
use App\Services\Bazi\BaziAiInterpreterService;
use App\Services\Bazi\BaziCalculationEngine;
use App\Services\Bazi\BaziRegistry;
use App\Services\Bazi\BaziRelationshipEngine;
use App\Services\Bazi\BaziReverseLookupService;
use App\Services\Bazi\BaziSafetyGuardService;
use App\Services\Calendar\VietnameseCalendarEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Controller Mây Hẹn – Tứ Trụ Nhân Sinh (/bat-tu).
 *
 * - Tra cứu Bát Tự cơ bản miễn phí tức thời cho cả khách và thành viên.
 * - Hồ sơ cá nhân, AI luận giải 10 tầng, Hỏi Đáp Bát Tự và chia sẻ riêng tư bảo vệ qua auth + 403 isolation.
 */
class BaziController extends Controller
{
    public function __construct(
        protected BaziCalculationEngine $engine,
        protected BaziRelationshipEngine $relationshipEngine,
        protected BaziReverseLookupService $reverseLookup,
        protected BaziAiInterpreterService $ai,
        protected BaziSafetyGuardService $safety,
        protected VietnameseCalendarEngine $calendar
    ) {}

    /**
     * Trang chính SEO & Lập Lá Số Tứ Trụ Miễn Phí (/bat-tu + các trang chuyên đề có canonical riêng).
     */
    public function index(Request $request, ?string $topic = null): View
    {
        $sampleChart = $this->engine->calculate([
            'name' => 'Lá số minh họa',
            'calendar_type' => 'solar',
            'day' => 15,
            'month' => 5,
            'year' => 1995,
            'hour' => 9,
            'minute' => 30,
            'time_accuracy' => 'exact',
            'gender' => 'female',
            'location_key' => 'ha-noi',
            'method_id' => 'tu_binh_default_v1',
        ]);
        $sampleReport = $this->ai->interpretChart($sampleChart, allowAi: false);

        $pageMeta = match ($topic) {
            'dung-than' => [
                'title' => 'Dụng Thần Là Gì? Cách Xem Dụng Thần, Hỷ Thần Bát Tự Chính Xác',
                'h1' => 'Tra Cứu Dụng Thần & Hỷ Thần Bát Tự Đa Phương Pháp',
                'canonical' => route('bazi.topic', ['topic' => 'dung-than']),
                'meta_description' => 'Phân biệt rõ Dụng Thần theo Phù Ức, Điều Hậu (Cùng Thông Bảo Giám) và Cách Cục (Tử Bình Chân Thuyên). Tra cứu miễn phí có căn cứ tại Mây Hẹn.',
            ],
            'nhat-chu' => [
                'title' => 'Nhật Chủ Là Gì? Ý Nghĩa 10 Thiên Can Nhật Chủ Trong Bát Tự',
                'h1' => 'Tra Cứu Nhật Chủ (Day Master) & 10 Thiên Can Bát Tự',
                'canonical' => route('bazi.topic', ['topic' => 'nhat-chu']),
                'meta_description' => 'Tìm hiểu 10 Nhật Chủ từ Giáp Mộc đến Quý Thủy, phân biệt Nhật Chủ với Nạp Âm năm sinh và lập lá số Tứ Trụ miễn phí.',
            ],
            'thap-than' => [
                'title' => 'Thập Thần Trong Bát Tự Là Gì? Bảng Tra 10 Thập Thần Chính Xác',
                'h1' => 'Tra Cứu Thập Thần & Thập Thần Orbit Bát Tự',
                'canonical' => route('bazi.topic', ['topic' => 'thap-than']),
                'meta_description' => 'Giải mã 10 Thập Thần (Tỷ Kiên, Kiếp Tài, Thực Thần, Thương Quan, Chính/Thiên Tài, Chính Quan, Thất Sát, Chính/Thiên Ấn) theo ma trận 10×10 chuẩn Tử Bình.',
            ],
            'ngu-hanh' => [
                'title' => 'Ngũ Hành Trong Bát Tự — Phân Bố 8 Chữ, Tàng Can & Nguyệt Lệnh',
                'h1' => 'Phân Tích Ngũ Hành Tương Sinh Tương Khắc Trong Bát Tự',
                'canonical' => route('bazi.topic', ['topic' => 'ngu-hanh']),
                'meta_description' => 'Khám phá Ngũ Hành Bát Tự tách rõ 3 lớp: phân bố 8 chữ, mở rộng Tàng Can và khí thế theo Nguyệt Lệnh.',
            ],
            'dai-van' => [
                'title' => 'Đại Vận & Lưu Niên Bát Tự Là Gì? Cách Tính Khởi Vận Chuẩn Tiết Khí',
                'h1' => 'Tính Đại Vận 10 Năm, Khởi Vận & Lưu Niên Bát Tự',
                'canonical' => route('bazi.topic', ['topic' => 'dai-van']),
                'meta_description' => 'Tính chính xác chiều Đại Vận (thuận/nghịch), tuổi khởi vận theo khoảng cách tới 12 mốc Tiết và dòng thời gian Lưu Niên, Lưu Nguyệt.',
            ],
            'gio-mat-troi' => [
                'title' => 'Giờ Mặt Trời Thực (True Solar Time) Trong Bát Tự — Cách Hiệu Chỉnh',
                'h1' => 'So Sánh Giờ Dân Dụng & Giờ Mặt Trời Thực Trong Bát Tự',
                'canonical' => route('bazi.topic', ['topic' => 'gio-mat-troi']),
                'meta_description' => 'Tính hiệu chỉnh kinh độ địa phương và Phương trình thời gian (Equation of Time), so sánh ảnh hưởng tới Trụ Giờ và Trụ Ngày.',
            ],
            default => [
                'title' => 'Tra Cứu Bát Tự & Lập Lá Số Tứ Trụ Online Miễn Phí',
                'h1' => 'Tra Cứu Bát Tự & Lập Lá Số Tứ Trụ Online Miễn Phí',
                'canonical' => route('bazi.index'),
                'meta_description' => 'Tra cứu Bát Tự và lập lá số Tứ Trụ online miễn phí chuẩn Tử Bình: đổi năm tại Lập Xuân, 12 mốc Tiết, True Solar Time, Tàng Can, Thập Thần, Cách Cục, Dụng Thần đa phương pháp, Đại Vận và Tứ Trụ 3D tại Mây Hẹn.',
            ],
        };

        return view('client.pages.bazi.index', [
            'sampleChart' => $sampleChart,
            'sampleReport' => $sampleReport,
            'methods' => BaziRegistry::METHOD_PROFILES,
            'locations' => BaziRegistry::BIRTH_LOCATIONS,
            'stems' => BaziRegistry::STEMS,
            'branches' => BaziRegistry::BRANCHES,
            'fiveElements' => BaziRegistry::FIVE_ELEMENTS,
            'topic' => $topic,
            'pageMeta' => $pageMeta,
        ]);
    }

    /**
     * Bát Tự Tình Duyên & Hôn Nhân — Mây Hẹn – Tứ Trụ Kết Duyên (/bat-tu/tinh-duyen).
     */
    public function love(): View
    {
        return view('client.pages.bazi.love', [
            'methods' => BaziRegistry::METHOD_PROFILES,
            'locations' => BaziRegistry::BIRTH_LOCATIONS,
            'pageMeta' => [
                'title' => 'Bát Tự Tình Duyên & Hôn Nhân — Mây Hẹn Tứ Trụ Kết Duyên',
                'h1' => 'Bát Tự Tình Duyên & Hôn Nhân — Tứ Trụ Kết Duyên',
                'canonical' => route('bazi.love'),
                'meta_description' => 'Phân tích Bát Tự hai người độc lập theo 12 chủ đề tình duyên & hôn nhân, khám phá tương tác Nhật Chủ, Cung Phối Ngẫu và câu hỏi trao đổi thực tế — không chấm % hợp nhau giả.',
            ],
        ]);
    }

    /**
     * Công cụ Tra Cứu Ngược Bát Tự (/bat-tu/tra-cuu-nguoc).
     */
    public function reverseLookup(): View
    {
        return view('client.pages.bazi.reverse', [
            'stems' => BaziRegistry::STEMS,
            'branches' => BaziRegistry::BRANCHES,
            'pageMeta' => [
                'title' => 'Tra Cứu Ngược Bát Tự — Tìm Ngày Giờ Sinh Từ Tứ Trụ',
                'h1' => 'Tra Cứu Ngược Bát Tự (Dành Cho Người Nghiên Cứu)',
                'canonical' => route('bazi.reverse'),
                'meta_description' => 'Nhập 4 trụ Can Chi (Năm, Tháng, Ngày, Giờ) để tìm tất cả các mốc ngày giờ sinh Dương lịch tương ứng trong phạm vi năm nghiên cứu.',
            ],
        ]);
    }

    /**
     * Bách Khoa Bát Tự (/bat-tu/thu-vien).
     */
    public function library(): View
    {
        return view('client.pages.bazi.library', [
            'stems' => BaziRegistry::STEMS,
            'branches' => BaziRegistry::BRANCHES,
            'hiddenStems' => BaziRegistry::HIDDEN_STEMS,
            'tenGods' => BaziRegistry::TEN_GODS,
            'sixtyPillars' => BaziRegistry::getSixtyPillars($this->calendar),
            'methods' => BaziRegistry::METHOD_PROFILES,
            'pageMeta' => [
                'title' => 'Bách Khoa Bát Tự — 10 Thiên Can, 12 Địa Chi, 60 Hoa Giáp & Thập Thần',
                'h1' => 'Bách Khoa Bát Tự & Hệ Thống Quy Ước Tử Bình',
                'canonical' => route('bazi.library'),
                'meta_description' => 'Thư viện tra cứu chuẩn hóa 10 Thiên Can, 12 Địa Chi, 60 Hoa Giáp, 30 Nạp Âm, bảng Tàng Can, 10 Thập Thần và quy ước phương pháp Tử Bình tại Mây Hẹn.',
            ],
        ]);
    }

    /**
     * API: Lập lá số Bát Tự tức thời (miễn phí cho khách; hỗ trợ lưu hồ sơ & AI 10 tầng cho thành viên).
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'calendar_type' => ['required', 'in:solar,lunar'],
            'day' => ['required', 'integer', 'min:1', 'max:31'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'is_leap_month' => ['nullable', 'boolean'],
            'time_accuracy' => ['required', 'in:exact,approx,window,session,unknown'],
            'hour' => ['nullable', 'integer', 'min:0', 'max:23'],
            'minute' => ['nullable', 'integer', 'min:0', 'max:59'],
            'gender' => ['nullable', 'in:male,female,unspecified'],
            'location_key' => ['nullable', 'string', 'max:40'],
            'method_id' => ['nullable', 'string', 'max:60'],
            'save_profile' => ['nullable', 'boolean'],
            'use_ai' => ['nullable', 'boolean'],
        ]);

        try {
            $chart = $this->engine->calculate($validated);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $allowAi = (bool) ($validated['use_ai'] ?? false) && $request->user() !== null;
        $report = $this->ai->interpretChart($chart, allowAi: $allowAi);

        $savedSlug = null;
        if (! empty($validated['save_profile']) && $request->user() !== null) {
            $saved = BaziChart::create([
                'user_id' => $request->user()->id,
                'slug' => Str::lower(Str::random(12)),
                'profile_name' => trim((string) ($validated['name'] ?? '')) ?: 'Hồ sơ Bát Tự '.$chart['birth']['solar_label'],
                'gender' => $chart['birth']['gender'],
                'method_id' => $chart['method']['id'],
                'engine_version' => $chart['engine_version'],
                'rule_version' => $chart['rule_version'],
                'calendar_version' => $chart['calendar_version'],
                'calculation_hash' => $chart['calculation_hash'],
                'day_master_label' => $chart['day_master']['name'].' '.$chart['day_master']['element'],
                'four_pillars_summary' => implode(' · ', array_filter([
                    $chart['pillars']['year']['label'],
                    $chart['pillars']['month']['label'],
                    $chart['pillars']['day']['label'],
                    $chart['pillars']['hour']['label'] ?? null,
                ])),
                'input_json' => $validated,
                'chart_json' => $chart,
                'ai_report_json' => $report,
            ]);
            $savedSlug = $saved->slug;
        }

        return response()->json([
            'success' => true,
            'chart' => $chart,
            'report' => $report,
            'saved_slug' => $savedSlug,
            'saved_url' => $savedSlug ? route('bazi.show', ['slug' => $savedSlug]) : null,
        ]);
    }

    /**
     * API: So sánh trường phái (Giờ dân dụng vs True Solar Time, ranh giới 23:00 vs 00:00).
     */
    public function compareMethods(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'calendar_type' => ['required', 'in:solar,lunar'],
            'day' => ['required', 'integer', 'min:1', 'max:31'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'is_leap_month' => ['nullable', 'boolean'],
            'time_accuracy' => ['required', 'in:exact,approx,window,session,unknown'],
            'hour' => ['nullable', 'integer', 'min:0', 'max:23'],
            'minute' => ['nullable', 'integer', 'min:0', 'max:59'],
            'gender' => ['nullable', 'in:male,female,unspecified'],
            'location_key' => ['nullable', 'string', 'max:40'],
        ]);

        try {
            $results = [];
            foreach (['tu_binh_default_v1', 'tu_binh_midnight_v1', 'tu_binh_true_solar_v1'] as $mid) {
                $c = $this->engine->calculate(array_merge($validated, ['method_id' => $mid]));
                $results[] = [
                    'method_id' => $mid,
                    'method_name' => $c['method']['name'],
                    'year' => $c['pillars']['year']['label'],
                    'month' => $c['pillars']['month']['label'],
                    'day' => $c['pillars']['day']['label'],
                    'hour' => $c['pillars']['hour']['label'] ?? 'Chưa có',
                    'effective_time' => $c['birth']['solar_time']['effective_label'],
                    'day_master' => $c['day_master']['name'].' '.$c['day_master']['element'],
                    'structure' => $c['structure']['name'],
                ];
            }
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'comparisons' => $results]);
    }

    /**
     * API: Time Travel — xem Lưu Niên & 12 Lưu Nguyệt cho năm bất kỳ mà không sửa lá số gốc.
     */
    public function annualFlow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'day_stem_index' => ['required', 'integer', 'min:0', 'max:9'],
            'year_branch_index' => ['required', 'integer', 'min:0', 'max:11'],
            'month_branch_index' => ['required', 'integer', 'min:0', 'max:11'],
            'day_branch_index' => ['required', 'integer', 'min:0', 'max:11'],
            'hour_branch_index' => ['nullable', 'integer', 'min:0', 'max:11'],
        ]);

        $stubPillars = [
            'year' => ['branch_index' => (int) $validated['year_branch_index']],
            'month' => ['branch_index' => (int) $validated['month_branch_index']],
            'day' => ['branch_index' => (int) $validated['day_branch_index']],
            'hour' => isset($validated['hour_branch_index']) ? ['branch_index' => (int) $validated['hour_branch_index']] : null,
        ];

        $flow = $this->engine->computeAnnualAndMonthlyFlow(
            (int) $validated['target_year'],
            (int) $validated['day_stem_index'],
            $stubPillars
        );

        return response()->json(['success' => true, 'annual_flow' => $flow]);
    }

    /**
     * API: Phân tích Bát Tự Tình Duyên hai người độc lập.
     */
    public function analyzeLove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'person_a' => ['required', 'array'],
            'person_b' => ['required', 'array'],
            'person_a.calendar_type' => ['required', 'in:solar,lunar'],
            'person_a.day' => ['required', 'integer', 'min:1', 'max:31'],
            'person_a.month' => ['required', 'integer', 'min:1', 'max:12'],
            'person_a.year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'person_b.calendar_type' => ['required', 'in:solar,lunar'],
            'person_b.day' => ['required', 'integer', 'min:1', 'max:31'],
            'person_b.month' => ['required', 'integer', 'min:1', 'max:12'],
            'person_b.year' => ['required', 'integer', 'min:1900', 'max:2100'],
        ]);

        try {
            $analysis = $this->relationshipEngine->analyze($validated['person_a'], $validated['person_b']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'analysis' => $analysis]);
    }

    /**
     * API: Tra cứu ngược Bát Tự.
     */
    public function searchReverse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year_stem' => ['required', 'integer', 'min:0', 'max:9'],
            'year_branch' => ['required', 'integer', 'min:0', 'max:11'],
            'month_stem' => ['required', 'integer', 'min:0', 'max:9'],
            'month_branch' => ['required', 'integer', 'min:0', 'max:11'],
            'day_stem' => ['required', 'integer', 'min:0', 'max:9'],
            'day_branch' => ['required', 'integer', 'min:0', 'max:11'],
            'hour_stem' => ['required', 'integer', 'min:0', 'max:9'],
            'hour_branch' => ['required', 'integer', 'min:0', 'max:11'],
            'from_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'to_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
        ]);

        $fromY = (int) ($validated['from_year'] ?? 1940);
        $toY = min($fromY + 120, (int) ($validated['to_year'] ?? 2040));

        $matches = $this->reverseLookup->findMatchingBirthTimes(
            (int) $validated['year_stem'],
            (int) $validated['year_branch'],
            (int) $validated['month_stem'],
            (int) $validated['month_branch'],
            (int) $validated['day_stem'],
            (int) $validated['day_branch'],
            (int) $validated['hour_stem'],
            (int) $validated['hour_branch'],
            $fromY,
            $toY
        );

        return response()->json(['success' => true, 'count' => count($matches), 'matches' => $matches]);
    }

    /**
     * Hồ sơ Bát Tự cá nhân (auth, riêng tư).
     */
    public function profiles(Request $request): View
    {
        $charts = BaziChart::where('user_id', $request->user()->id)->latest()->get();

        return view('client.pages.bazi.profiles', [
            'charts' => $charts,
        ]);
    }

    /**
     * Xem chi tiết hồ sơ Bát Tự đã lưu (chỉ chủ sở hữu hoặc admin, noindex).
     */
    public function show(Request $request, string $slug): View
    {
        $record = BaziChart::where('slug', $slug)->with('followups')->firstOrFail();
        if ((int) $record->user_id !== (int) $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        return view('client.pages.bazi.show', [
            'record' => $record,
            'chart' => $record->chart_json,
            'report' => $record->ai_report_json ?? $this->ai->interpretChart($record->chart_json, allowAi: false),
            'fiveElements' => BaziRegistry::FIVE_ELEMENTS,
        ]);
    }

    /**
     * Xóa hồ sơ Bát Tự đã lưu (chủ sở hữu).
     */
    public function destroy(Request $request, string $slug): JsonResponse
    {
        $record = BaziChart::where('slug', $slug)->firstOrFail();
        if ((int) $record->user_id !== (int) $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }
        BaziFollowup::where('bazi_chart_id', $record->id)->delete();
        $record->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Hỏi sâu AI trên hồ sơ Bát Tự đã lưu.
     */
    public function followup(Request $request, string $slug): JsonResponse
    {
        $record = BaziChart::where('slug', $slug)->firstOrFail();
        if ((int) $record->user_id !== (int) $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:4', 'max:400'],
        ]);

        $safety = $this->safety->inspectQuestion($validated['question']);
        if (! $safety['safe']) {
            return response()->json(['success' => false, 'code' => $safety['code'], 'message' => $safety['message']], 422);
        }

        $res = $this->ai->answerFollowup(trim($validated['question']), $record->chart_json);
        $fu = BaziFollowup::create([
            'bazi_chart_id' => $record->id,
            'user_id' => $request->user()->id,
            'question' => trim($validated['question']),
            'answer' => $res['answer'],
            'ai_powered' => $res['ai_powered'],
            'ai_model' => $res['ai_model'],
        ]);

        return response()->json(['success' => true, 'followup' => $fu]);
    }

    /**
     * Bật/tắt link chia sẻ ẩn danh (mặc định ẩn ngày giờ sinh).
     */
    public function toggleShare(Request $request, string $slug): JsonResponse
    {
        $record = BaziChart::where('slug', $slug)->firstOrFail();
        if ((int) $record->user_id !== (int) $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $record->is_shared = ! $record->is_shared;
        if ($record->is_shared && ! $record->share_token) {
            $record->share_token = Str::lower(Str::random(24));
        }
        $record->save();

        return response()->json([
            'success' => true,
            'is_shared' => $record->is_shared,
            'share_url' => $record->is_shared ? route('bazi.shared', ['shareToken' => $record->share_token]) : null,
        ]);
    }

    /**
     * Trang thẻ chia sẻ Bát Tự ẩn danh (ẩn ngày sinh, giờ sinh, địa điểm; thu hồi trả 404).
     */
    public function sharedCard(string $shareToken): View
    {
        $record = BaziChart::where('share_token', $shareToken)->where('is_shared', true)->firstOrFail();

        return view('client.pages.bazi.shared', [
            'record' => $record,
            'chart' => $record->chart_json,
            'report' => $record->ai_report_json ?? [],
        ]);
    }
}
