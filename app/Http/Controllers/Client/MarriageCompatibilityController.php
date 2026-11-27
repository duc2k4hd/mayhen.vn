<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CoupleCompatibilityFollowup;
use App\Models\CoupleCompatibilityReport;
use App\Models\User;
use App\Services\Bazi\BaziRegistry;
use App\Services\Bazi\BaziSafetyGuardService;
use App\Services\Marriage\CaoLyRegistry;
use App\Services\Marriage\KuaDuNienEngine;
use App\Services\Marriage\MarriageAiInterpreterService;
use App\Services\Marriage\MarriageCompatibilityEngine;
use App\Services\PlanAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Controller Mây Hẹn – Duyên Hợp (/cao-ly-dau-hinh & /xem-tuoi-vo-chong).
 *
 * - Công cụ A (/cao-ly-dau-hinh): Vòng Thiên Văn Đồng Ngọc 10 Can × 12 Chi, Ma trận 120 tổ hợp, 6 Huy Chương Lục Cảnh.
 * - Công cụ B (/xem-tuoi-vo-chong): Hai Vòng Kết Duyên 3D, Đối chiếu đa phương pháp, Vòng Bát Quái Du Niên 8×8, Kim Lâu năm cưới.
 * - Sổ Nhân Duyên cá nhân & AI luận giải áp dụng phân quyền gói công bằng qua PlanAccessService + Cache chống lách quota.
 */
class MarriageCompatibilityController extends Controller
{
    public const TIER_LIMITS = [
        'premium' => ['label' => 'Premium (0đ)', 'daily_limit' => 5, 'cooldown_seconds' => 300, 'cooldown_label' => '5 phút/lượt', 'max_followups' => 3],
        'plus' => ['label' => 'Plus', 'daily_limit' => 10, 'cooldown_seconds' => 60, 'cooldown_label' => '1 phút/lượt', 'max_followups' => 6],
        'pro' => ['label' => 'Pro', 'daily_limit' => 30, 'cooldown_seconds' => 0, 'cooldown_label' => 'Liên tục', 'max_followups' => 8],
    ];

    public function __construct(
        protected MarriageCompatibilityEngine $engine,
        protected KuaDuNienEngine $kuaEngine,
        protected MarriageAiInterpreterService $ai,
        protected BaziSafetyGuardService $safety,
        protected PlanAccessService $planAccess
    ) {}

    /**
     * Công cụ A: Xem Cao Ly Đầu Hình & Xem Tuổi Vợ Chồng Online Miễn Phí (/cao-ly-dau-hinh).
     */
    public function caoLyIndex(Request $request, ?string $subView = null): View
    {
        $all120 = CaoLyRegistry::getAll120();
        $defaultRecord = CaoLyRegistry::lookup(6, 7); // Canh - Mùi (1990 - 1991 minh họa chuẩn ảnh mẫu)

        $pageMeta = match ($subView) {
            'bang-tra' => [
                'title' => 'Bảng Tra 120 Tổ Hợp Cao Ly Đầu Hình (10 Thiên Can × 12 Địa Chi)',
                'h1' => 'Bảng Tra 120 Tổ Hợp Cao Ly Đầu Hình Tương Tác',
                'canonical' => route('marriage.caoly.matrix'),
                'meta_description' => 'Tra cứu đầy đủ 120 tổ hợp Cao Ly Đầu Hình theo quy tắc Nam dụng Thiên Can – Nữ dụng Địa Chi, kèm Lục Cảnh và giải thích hiện đại minh bạch.',
            ],
            'luc-canh' => [
                'title' => 'Lục Cảnh Cao Ly Đầu Hình — Ý Nghĩa 6 Cảnh & Diễn Giải Hiện Đại',
                'h1' => 'Lục Cảnh Cao Ly Đầu Hình (6 Huy Chương Nhân Duyên)',
                'canonical' => route('marriage.caoly.scenes'),
                'meta_description' => 'Giải mã 6 Lục Cảnh Cao Ly Đầu Hình: Nhất Phú Quý, Nhì Bần Tiện, Tam Vinh Hiển, Tứ Đạt Đạo, Ngũ Biệt Ly và Lục Tuyệt Mạng bằng góc nhìn văn hóa nhân văn.',
            ],
            default => [
                'title' => 'Xem Cao Ly Đầu Hình & Xem Tuổi Vợ Chồng Online Miễn Phí',
                'h1' => 'Xem Cao Ly Đầu Hình & Xem Tuổi Vợ Chồng Online Miễn Phí',
                'canonical' => route('marriage.caoly.index'),
                'meta_description' => 'Tra cứu Cao Ly Đầu Hình chuẩn 120 tổ hợp (Can chồng – Chi vợ) với Vòng Thiên Văn Đồng Ngọc tương tác, 6 Huy Chương Lục Cảnh và Bảng Tra 120 quẻ miễn phí tại Mây Hẹn.',
            ],
        };

        return view('client.pages.marriage.cao-ly', [
            'stems' => BaziRegistry::STEMS,
            'branches' => BaziRegistry::BRANCHES,
            'lucCanh' => CaoLyRegistry::LUC_CANH,
            'all120' => $all120,
            'defaultRecord' => $defaultRecord,
            'subView' => $subView,
            'pageMeta' => $pageMeta,
        ]);
    }

    /**
     * Trang chi tiết 1 tổ hợp trong 120 tổ hợp Cao Ly (/cao-ly-dau-hinh/{slug}).
     */
    public function caoLyDetail(string $slug): View
    {
        $record = CaoLyRegistry::findBySlug($slug);
        if (! $record) {
            abort(404);
        }

        return view('client.pages.marriage.cao-ly-detail', [
            'record' => $record,
            'lucCanh' => CaoLyRegistry::LUC_CANH,
            'pageMeta' => [
                'title' => "Cao Ly Đầu Hình {$record['combination_name']} ({$record['scene']['name']}) — Ý Nghĩa & Luận Giải",
                'h1' => "Cao Ly Đầu Hình {$record['combination_name']} ({$record['hanzi']}) — {$record['scene']['name']}",
                'canonical' => route('marriage.caoly.detail', ['slug' => $record['slug']]),
                'meta_description' => "Tra cứu tổ hợp Cao Ly Đầu Hình Can chồng {$record['husband_stem']['name']} – Chi vợ {$record['wife_branch']['name']} ({$record['scene']['name']}): ý nghĩa truyền thống, giới hạn và gợi ý vun đắp hôn nhân.",
            ],
        ]);
    }

    /**
     * Công cụ B: Xem Tuổi Vợ Chồng & Tra Cứu Tuổi Kết Hôn Online Miễn Phí (/xem-tuoi-vo-chong).
     */
    public function compatibilityIndex(Request $request): View
    {
        $sample = $this->engine->analyze(
            ['name' => 'Chồng Canh Ngọ', 'year' => 1990, 'calendar_type' => 'solar'],
            ['name' => 'Vợ Tân Mùi', 'year' => 1991, 'calendar_type' => 'solar'],
            now('Asia/Ho_Chi_Minh')->year + 1
        );
        $sampleAi = $this->ai->interpret($sample, allowAi: false);
        $quota = $this->getUserQuota($request->user());

        return view('client.pages.marriage.compatibility', [
            'sample' => $sample,
            'sampleAi' => $sampleAi,
            'trigrams' => KuaDuNienEngine::TRIGRAMS,
            'duNienMatrix' => KuaDuNienEngine::MATRIX,
            'duNienMeta' => KuaDuNienEngine::DU_NIEN_META,
            'lucCanh' => CaoLyRegistry::LUC_CANH,
            'marriageQuota' => $quota,
            'pageMeta' => [
                'title' => 'Xem Tuổi Vợ Chồng & Tra Cứu Tuổi Kết Hôn Online Miễn Phí',
                'h1' => 'Xem Tuổi Vợ Chồng & Tra Cứu Tuổi Kết Hôn Online Miễn Phí',
                'canonical' => route('marriage.compatibility.index'),
                'meta_description' => 'Xem tuổi vợ chồng đa phương pháp miễn phí: Hai Vòng Kết Duyên 3D, Can Chi, Nạp Âm, Cung Phi & Vòng Bát Quái Du Niên 8×8, Cao Ly Đầu Hình và xem năm cưới Kim Lâu tại Mây Hẹn.',
            ],
        ]);
    }

    /**
     * API: Phân tích Xem Tuổi Vợ Chồng & Cao Ly (miễn phí cơ bản; lưu Sổ Nhân Duyên & AI theo gói cước).
     */
    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'husband_name' => ['nullable', 'string', 'max:100'],
            'husband_calendar_type' => ['nullable', 'in:solar,lunar'],
            'husband_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'husband_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'husband_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'husband_is_leap' => ['nullable', 'boolean'],
            'wife_name' => ['nullable', 'string', 'max:100'],
            'wife_calendar_type' => ['nullable', 'in:solar,lunar'],
            'wife_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'wife_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'wife_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'wife_is_leap' => ['nullable', 'boolean'],
            'wedding_year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'save_report' => ['nullable', 'boolean'],
            'use_ai' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $wantsSave = ! empty($validated['save_report']);
        $wantsAi = ! empty($validated['use_ai']);

        if (($wantsSave || $wantsAi) && $user === null) {
            return response()->json([
                'success' => false,
                'login_required' => true,
                'login_url' => route('login', ['redirect' => route('marriage.compatibility.index')]),
                'message' => 'Vui lòng đăng nhập tài khoản Mây Hẹn (miễn phí gói Premium 0đ) để lưu Sổ Nhân Duyên và sử dụng AI luận giải 10 tầng.',
            ], 401);
        }

        $quota = $this->getUserQuota($user);
        if (($wantsSave || $wantsAi) && $user !== null) {
            if ($quota['remaining_today'] <= 0) {
                return response()->json([
                    'success' => false,
                    'quota_exceeded' => true,
                    'pricing_url' => route('pricing'),
                    'message' => "Bạn đã dùng hết {$quota['daily_limit']} lượt lưu Sổ Nhân Duyên & AI hôm nay của gói {$quota['plan_label']}.",
                ], 429);
            }
            if ($quota['cooldown_remaining'] > 0) {
                $mins = (int) ceil($quota['cooldown_remaining'] / 60);

                return response()->json([
                    'success' => false,
                    'cooldown_remaining' => $quota['cooldown_remaining'],
                    'message' => "Gói {$quota['plan_label']} có thời gian giãn cách {$quota['cooldown_label']}. Vui lòng đợi khoảng {$mins} phút nữa hoặc nâng cấp gói Pro.",
                ], 429);
            }
        }

        try {
            $analysis = $this->engine->analyze(
                [
                    'name' => $validated['husband_name'] ?? '',
                    'calendar_type' => $validated['husband_calendar_type'] ?? 'solar',
                    'year' => (int) $validated['husband_year'],
                    'month' => $validated['husband_month'] ?? null,
                    'day' => $validated['husband_day'] ?? null,
                    'is_leap_month' => (bool) ($validated['husband_is_leap'] ?? false),
                ],
                [
                    'name' => $validated['wife_name'] ?? '',
                    'calendar_type' => $validated['wife_calendar_type'] ?? 'solar',
                    'year' => (int) $validated['wife_year'],
                    'month' => $validated['wife_month'] ?? null,
                    'day' => $validated['wife_day'] ?? null,
                    'is_leap_month' => (bool) ($validated['wife_is_leap'] ?? false),
                ],
                isset($validated['wedding_year']) ? (int) $validated['wedding_year'] : null
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $aiReport = $this->ai->interpret($analysis, allowAi: $wantsAi && $user !== null);

        $savedSlug = null;
        if (($wantsSave || $wantsAi) && $user !== null) {
            $title = sprintf(
                '%s (%s) & %s (%s)',
                $analysis['husband']['name'] ?: 'Chồng '.$analysis['husband']['lunar_year'],
                $analysis['husband']['can_chi'],
                $analysis['wife']['name'] ?: 'Vợ '.$analysis['wife']['lunar_year'],
                $analysis['wife']['can_chi']
            );

            $saved = CoupleCompatibilityReport::create([
                'user_id' => $user->id,
                'slug' => Str::lower(Str::random(12)),
                'title' => $title,
                'husband_can_chi' => $analysis['husband']['can_chi'],
                'wife_can_chi' => $analysis['wife']['can_chi'],
                'cao_ly_id' => $analysis['cao_ly']['id'],
                'du_nien_code' => $analysis['du_nien']['code'],
                'engine_version' => $analysis['version'],
                'calculation_hash' => $analysis['calculation_hash'],
                'report_json' => $analysis,
                'ai_report_json' => $aiReport,
            ]);
            $savedSlug = $saved->slug;

            if (! $user->isAdmin()) {
                $todayKey = now('Asia/Ho_Chi_Minh')->format('Y-m-d');
                $countKey = "marriage_daily_used_{$user->id}_{$todayKey}";
                $lastKey = "marriage_last_cast_{$user->id}";
                Cache::put($countKey, max((int) Cache::get($countKey, 0), $quota['used_today']) + 1, now('Asia/Ho_Chi_Minh')->endOfDay());
                Cache::put($lastKey, now()->timestamp, now()->addSeconds(max(60, $quota['cooldown_seconds'] + 60)));
            }
        }

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
            'ai_report' => $aiReport,
            'saved_slug' => $savedSlug,
            'saved_url' => $savedSlug ? route('marriage.book.show', ['slug' => $savedSlug]) : null,
            'quota' => $this->getUserQuota($user),
        ]);
    }

    /**
     * Sổ Nhân Duyên cá nhân (auth, riêng tư).
     */
    public function myBook(Request $request): View
    {
        $reports = CoupleCompatibilityReport::where('user_id', $request->user()->id)->latest()->get();

        return view('client.pages.marriage.book', [
            'reports' => $reports,
        ]);
    }

    /**
     * Trang chi tiết Sổ Nhân Duyên đã lưu (auth + 403 isolation, noindex).
     */
    public function showReport(Request $request, string $slug): View
    {
        $record = CoupleCompatibilityReport::where('slug', $slug)->with('followups')->firstOrFail();
        if ((int) $record->user_id !== (int) $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        return view('client.pages.marriage.show', [
            'record' => $record,
            'analysis' => $record->report_json,
            'aiReport' => $record->ai_report_json ?? $this->ai->interpret($record->report_json, allowAi: false),
            'trigrams' => KuaDuNienEngine::TRIGRAMS,
            'duNienMatrix' => KuaDuNienEngine::MATRIX,
            'duNienMeta' => KuaDuNienEngine::DU_NIEN_META,
            'marriageQuota' => $this->getUserQuota($request->user()),
        ]);
    }

    /**
     * Hỏi Đáp Cặp Đôi & Trợ Lý Trao Đổi Với Gia Đình trên Sổ Nhân Duyên đã lưu.
     */
    public function followup(Request $request, string $slug): JsonResponse
    {
        $record = CoupleCompatibilityReport::where('slug', $slug)->firstOrFail();
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

        $quota = $this->getUserQuota($request->user());
        $usedFollowups = CoupleCompatibilityFollowup::where('report_id', $record->id)->count();
        if ($usedFollowups >= $quota['max_followups']) {
            return response()->json([
                'success' => false,
                'message' => "Sổ Nhân Duyên này đã đạt giới hạn {$quota['max_followups']} lượt hỏi đáp của gói {$quota['plan_label']}.",
            ], 429);
        }

        $res = $this->ai->answerQuestion(trim($validated['question']), $record->report_json);
        if (! empty($res['blocked'])) {
            return response()->json([
                'success' => false,
                'code' => $res['code'] ?? 'SECURITY_POLICY_BLOCK',
                'message' => $res['answer'],
            ], 422);
        }

        $fu = CoupleCompatibilityFollowup::create([
            'report_id' => $record->id,
            'user_id' => $request->user()->id,
            'question' => trim($validated['question']),
            'answer' => $res['answer'],
            'ai_powered' => $res['ai_powered'],
            'ai_model' => $res['ai_model'],
        ]);

        return response()->json(['success' => true, 'followup' => $fu]);
    }

    /**
     * Bật/tắt chia sẻ ẩn danh (mặc định ẩn ngày sinh).
     */
    public function toggleShare(Request $request, string $slug): JsonResponse
    {
        $record = CoupleCompatibilityReport::where('slug', $slug)->firstOrFail();
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
            'share_url' => $record->is_shared ? route('marriage.shared', ['shareToken' => $record->share_token]) : null,
        ]);
    }

    public function sharedCard(string $shareToken): View
    {
        $record = CoupleCompatibilityReport::where('share_token', $shareToken)->where('is_shared', true)->firstOrFail();

        return view('client.pages.marriage.shared', [
            'record' => $record,
            'analysis' => $record->report_json,
        ]);
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $record = CoupleCompatibilityReport::where('slug', $slug)->firstOrFail();
        if ((int) $record->user_id !== (int) $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }
        CoupleCompatibilityFollowup::where('report_id', $record->id)->delete();
        $record->delete();

        return response()->json(['success' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getUserQuota(?User $user): array
    {
        if ($user === null || $this->planAccess->isGuestUser($user)) {
            return [
                'is_logged_in' => false,
                'plan_slug' => 'guest',
                'plan_label' => 'Khách (Miễn phí cơ bản)',
                'daily_limit' => 0,
                'used_today' => 0,
                'remaining_today' => 0,
                'cooldown_seconds' => 0,
                'cooldown_remaining' => 0,
                'cooldown_label' => 'Cần đăng nhập',
                'max_followups' => 0,
            ];
        }

        $slug = $user->isAdmin() ? 'pro' : $this->planAccess->getEffectivePlanSlug($user);
        $tierKey = array_key_exists($slug, self::TIER_LIMITS) ? $slug : 'premium';
        $cfg = self::TIER_LIMITS[$tierKey];

        $todayStart = now('Asia/Ho_Chi_Minh')->startOfDay()->setTimezone(config('app.timezone', 'UTC'));
        $dbUsed = CoupleCompatibilityReport::where('user_id', $user->id)->where('created_at', '>=', $todayStart)->count();
        $todayKey = now('Asia/Ho_Chi_Minh')->format('Y-m-d');
        $usedToday = max($dbUsed, (int) Cache::get("marriage_daily_used_{$user->id}_{$todayKey}", 0));

        $cooldownRemaining = 0;
        if ($cfg['cooldown_seconds'] > 0 && ! $user->isAdmin()) {
            $lastTs = (int) Cache::get("marriage_last_cast_{$user->id}", 0);
            $last = CoupleCompatibilityReport::where('user_id', $user->id)->latest('id')->first();
            if ($last && $last->created_at) {
                $lastTs = max($lastTs, $last->created_at->timestamp);
            }
            if ($lastTs > 0) {
                $cooldownRemaining = max(0, $cfg['cooldown_seconds'] - max(0, now()->timestamp - $lastTs));
            }
        }

        return [
            'is_logged_in' => true,
            'plan_slug' => $tierKey,
            'plan_label' => $user->isAdmin() ? 'Pro (Admin)' : $cfg['label'],
            'daily_limit' => $cfg['daily_limit'],
            'used_today' => $usedToday,
            'remaining_today' => max(0, $cfg['daily_limit'] - $usedToday),
            'cooldown_seconds' => $cfg['cooldown_seconds'],
            'cooldown_remaining' => $cooldownRemaining,
            'cooldown_label' => $cfg['cooldown_label'],
            'max_followups' => $cfg['max_followups'],
        ];
    }
}
