<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TuViChart;
use App\Models\TuViChartFollowup;
use App\Models\TuViChartJournal;
use App\Models\TuViProfile;
use App\Models\TuViSynastryReport;
use App\Models\User;
use App\Rules\NoProfanity;
use App\Services\PlanAccessService;
use App\Services\TuVi\TuViAiInterpreterService;
use App\Services\TuVi\TuViCalculationEngine;
use App\Services\TuVi\TuViKnowledgeRuleEngine;
use App\Services\TuVi\TuViSafetyGuardService;
use App\Services\TuVi\VietnameseCalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class TuViController extends Controller
{
    /**
     * Hạn mức luận giải AI Tử Vi đồng bộ chính sách phân tầng Mây Hẹn:
     * - Premium (0đ): 5 phút/lần, tối đa 5 lượt/ngày
     * - Plus: 1 phút/lần, tối đa 10 lượt/ngày
     * - Pro & Admin: luận liên tục, tối đa 30 lượt/ngày
     *
     * @var array<string, array{daily_limit: int, cooldown_seconds: int, cooldown_label: string}>
     */
    public const TIER_LIMITS = [
        'premium' => ['daily_limit' => 5, 'cooldown_seconds' => 300, 'cooldown_label' => '5 phút/lần'],
        'plus' => ['daily_limit' => 10, 'cooldown_seconds' => 60, 'cooldown_label' => '1 phút/lần'],
        'pro' => ['daily_limit' => 30, 'cooldown_seconds' => 0, 'cooldown_label' => 'luận liên tục'],
    ];

    /** @var array<string, string> */
    public const FOCUS_TOPICS = [
        'toan_bo' => 'Khám phá toàn bộ lá số',
        'ban_than' => 'Hiểu bản thân',
        'tinh_yeu' => 'Tình yêu & Nhân duyên',
        'hon_nhan' => 'Chuẩn bị hôn nhân',
        'gia_dinh' => 'Gia đình & Tổ ấm',
        'su_nghiep' => 'Sự nghiệp & Chí hướng',
        'tai_chinh' => 'Tài chính & Tích lũy',
        'nam_moi' => 'Định hướng năm mới',
    ];

    public function __construct(
        protected VietnameseCalendarService $calendar,
        protected TuViCalculationEngine $engine,
        protected TuViKnowledgeRuleEngine $ruleEngine,
        protected TuViAiInterpreterService $interpreter,
        protected TuViSafetyGuardService $safetyGuard,
        protected PlanAccessService $planAccess
    ) {}

    /**
     * Trang chính SEO & Cổng Khám Phá Mây Hẹn – Thiên Mệnh (/tu-vi).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $canUseFree = $this->canUseWithPremiumLogin($user);
        $currentPlanLabel = $this->planAccess->getTierLabel($this->planAccess->getEffectivePlanSlug($user));
        $castQuota = $this->getUserQuota($canUseFree ? $user : null);

        $palacesMeta = $this->engine->getPalacesMeta();
        $majorStarsMeta = $this->engine->getMajorStarsMeta();
        $patternsCatalog = $this->ruleEngine->getPatternsCatalog();
        $manifest = $this->engine->getManifest();
        $focusTopics = self::FOCUS_TOPICS;
        $hourBranches = VietnameseCalendarService::HOUR_BRANCH_META;
        $timeWindows = VietnameseCalendarService::TIME_WINDOWS;

        // Lá số mẫu minh họa (15/08/1995 Dương lịch, giờ Mão, Nữ — không dùng dữ liệu cá nhân thật)
        $demoChart = $this->engine->generateChart([
            'nickname' => 'Lá Số Mẫu Mây Hẹn',
            'gender_yin_yang' => 'female',
            'calendar_type' => 'solar',
            'birth_year' => 1995,
            'birth_month' => 8,
            'birth_day' => 15,
            'birth_time_mode' => 'earthly_branch',
            'birth_branch_index' => 3,
            'birth_timezone' => 'Asia/Ho_Chi_Minh',
            'viewed_year' => now('Asia/Ho_Chi_Minh')->year,
        ]);
        $demoRuleAnalysis = $this->ruleEngine->analyzeChart($demoChart);
        $demoInterpretation = $this->interpreter->interpretChart($demoChart, $demoRuleAnalysis, 'toan_bo', allowAi: false);

        $recentCharts = $canUseFree && $user
            ? TuViChart::with('profile')->where('user_id', $user->id)->latest()->limit(5)->get()
            : collect();

        return view('client.pages.tuvi.index', compact(
            'canUseFree',
            'currentPlanLabel',
            'castQuota',
            'palacesMeta',
            'majorStarsMeta',
            'patternsCatalog',
            'manifest',
            'focusTopics',
            'hourBranches',
            'timeWindows',
            'demoChart',
            'demoRuleAnalysis',
            'demoInterpretation',
            'recentCharts'
        ));
    }

    /**
     * API Xem trước quy đổi lịch Âm/Dương trước khi bấm lập lá số (không lưu DB).
     */
    public function previewCalendar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'calendar_type' => ['required', 'in:solar,lunar'],
            'birth_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'birth_month' => ['required', 'integer', 'min:1', 'max:12'],
            'birth_day' => ['required', 'integer', 'min:1', 'max:31'],
            'is_leap_month' => ['nullable', 'boolean'],
            'birth_time_mode' => ['required', 'in:exact_time,earthly_branch,time_window,unknown'],
            'birth_hour' => ['nullable', 'integer', 'min:0', 'max:23'],
            'birth_minute' => ['nullable', 'integer', 'min:0', 'max:59'],
            'birth_branch_index' => ['nullable', 'integer', 'min:0', 'max:11'],
            'birth_time_window' => ['nullable', 'string', 'max:32'],
            'birth_timezone' => ['nullable', 'string', 'max:64'],
            'leap_month_rule' => ['nullable', 'in:split_15,keep_month'],
            'late_rat_rule' => ['nullable', 'in:next_lunar_day,keep_day'],
        ]);

        try {
            $birth = $this->calendar->normalizeBirthData($validated);

            return response()->json(['success' => true, 'birth' => $birth]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * API Lập Lá Số Tử Vi & Luận Giải AI 6 Tầng (Miễn phí khi đăng nhập gói Premium trở lên).
     */
    public function generate(Request $request): JsonResponse
    {
        $this->extendExecutionTimeout(120);

        $user = $request->user();
        if (! $this->canUseWithPremiumLogin($user)) {
            return response()->json([
                'success' => false,
                'code' => 'PREMIUM_LOGIN_REQUIRED',
                'message' => 'Tính năng Lập Lá Số Tử Vi & Luận Giải AI 6 Tầng hoàn toàn miễn phí cho thành viên Mây Hẹn. Vui lòng đăng nhập (tự động nhận gói Thành Viên Premium 0đ) để lưu Hồ Sơ Thiên Mệnh riêng tư.',
                'login_url' => route('login', ['redirect' => route('tuvi.index')]),
                'register_url' => route('register'),
            ], 403);
        }

        $validated = $request->validate([
            'profile_id' => ['nullable', 'integer'],
            'nickname' => ['nullable', 'string', 'max:80', new NoProfanity],
            'relationship_label' => ['nullable', 'in:self,partner,family,other'],
            'gender_yin_yang' => ['required', 'in:male,female'],
            'calendar_type' => ['required', 'in:solar,lunar'],
            'birth_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'birth_month' => ['required', 'integer', 'min:1', 'max:12'],
            'birth_day' => ['required', 'integer', 'min:1', 'max:31'],
            'is_leap_month' => ['nullable', 'boolean'],
            'birth_time_mode' => ['required', 'in:exact_time,earthly_branch,time_window,unknown'],
            'birth_hour' => ['nullable', 'integer', 'min:0', 'max:23'],
            'birth_minute' => ['nullable', 'integer', 'min:0', 'max:59'],
            'birth_branch_index' => ['nullable', 'integer', 'min:0', 'max:11'],
            'birth_time_window' => ['nullable', 'string', 'max:32'],
            'birth_timezone' => ['nullable', 'string', 'max:64'],
            'leap_month_rule' => ['nullable', 'in:split_15,keep_month'],
            'late_rat_rule' => ['nullable', 'in:next_lunar_day,keep_day'],
            'viewed_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'focus_topic' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::FOCUS_TOPICS))],
            'idempotency_key' => ['nullable', 'string', 'max:80'],
        ]);

        $idempotencyKey = ! empty($validated['idempotency_key'])
            ? 'tuvi_u'.$user->id.'_'.Str::limit(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $validated['idempotency_key']), 60, '')
            : null;

        // 1. Idempotency check
        if ($idempotencyKey !== null) {
            $existing = TuViChart::where('idempotency_key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'idempotent_replay' => true,
                    'slug' => $existing->slug,
                    'result_url' => route('tuvi.show', $existing->slug),
                ]);
            }
        }

        // 2. Hạn mức phân tầng (Premium 5p & 5/ngày | Plus 1p & 10/ngày | Pro liên tục & 30/ngày)
        $quota = $this->getUserQuota($user);
        if ($quota['daily_remaining'] <= 0) {
            return response()->json([
                'success' => false,
                'code' => 'TUVI_DAILY_LIMIT_REACHED',
                'message' => 'Bạn đã sử dụng hết '.$quota['daily_limit'].' lượt lập lá số & luận giải AI trong ngày hôm nay theo gói hiện tại. Bạn vẫn có thể mở lại các lá số đã lưu trong Hồ Sơ Thiên Mệnh.',
                'quota' => $quota,
                'profiles_url' => route('tuvi.profiles'),
            ], 429);
        }
        if ($quota['cooldown_seconds'] > 0) {
            $mins = intdiv($quota['cooldown_seconds'], 60);
            $secs = $quota['cooldown_seconds'] % 60;
            $waitLabel = $mins > 0 ? ($secs > 0 ? "{$mins} phút {$secs} giây" : "{$mins} phút") : "{$secs} giây";

            return response()->json([
                'success' => false,
                'code' => 'TUVI_COOLDOWN_ACTIVE',
                'message' => "Theo gói hiện tại ({$quota['cooldown_label']}), vui lòng chờ thêm {$waitLabel} hoặc mở lại lá số gần nhất trong Hồ Sơ Thiên Mệnh.",
                'retry_after_seconds' => $quota['cooldown_seconds'],
                'quota' => $quota,
            ], 429);
        }

        $nickname = trim(strip_tags((string) ($validated['nickname'] ?? '')));
        $focusTopic = (string) ($validated['focus_topic'] ?? 'toan_bo');
        $viewedYear = (int) ($validated['viewed_year'] ?? now('Asia/Ho_Chi_Minh')->year);

        try {
            $chartData = $this->engine->generateChart(array_merge($validated, [
                'nickname' => $nickname,
                'viewed_year' => $viewedYear,
            ]));
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $ruleAnalysis = $this->ruleEngine->analyzeChart($chartData);
        $interpretation = $this->interpreter->interpretChart($chartData, $ruleAnalysis, $focusTopic, allowAi: true);

        // Quản lý hồ sơ & đánh số phiên bản lá số (không âm thầm ghi đè khi sửa giờ sinh)
        $profile = null;
        if (! empty($validated['profile_id'])) {
            $profile = TuViProfile::where('id', (int) $validated['profile_id'])
                ->where('user_id', $user->id)
                ->first();
        }

        if ($profile) {
            $profile->update([
                'nickname' => $nickname !== '' ? $nickname : $profile->nickname,
                'gender_yin_yang' => $validated['gender_yin_yang'],
                'calendar_type' => $validated['calendar_type'],
                'birth_year' => (int) $validated['birth_year'],
                'birth_month' => (int) $validated['birth_month'],
                'birth_day' => (int) $validated['birth_day'],
                'is_leap_month' => (bool) ($validated['is_leap_month'] ?? false),
                'birth_time_mode' => $validated['birth_time_mode'],
                'birth_hour' => $validated['birth_hour'] ?? null,
                'birth_minute' => $validated['birth_minute'] ?? null,
                'birth_branch_index' => $validated['birth_branch_index'] ?? null,
                'birth_time_window' => $validated['birth_time_window'] ?? null,
                'birth_timezone' => (string) ($validated['birth_timezone'] ?? 'Asia/Ho_Chi_Minh'),
                'leap_month_rule' => (string) ($validated['leap_month_rule'] ?? 'split_15'),
                'late_rat_rule' => (string) ($validated['late_rat_rule'] ?? 'next_lunar_day'),
            ]);
            $nextVersion = ((int) $profile->charts()->max('version_number')) + 1;
        } else {
            $profileSlug = 'hs-'.Str::slug($nickname !== '' ? $nickname : 'thien-menh').'-'.Str::lower(Str::random(6));
            $profile = TuViProfile::create([
                'user_id' => $user->id,
                'slug' => $profileSlug,
                'nickname' => $nickname !== '' ? $nickname : 'Hồ Sơ Thiên Mệnh',
                'relationship_label' => (string) ($validated['relationship_label'] ?? 'self'),
                'gender_yin_yang' => $validated['gender_yin_yang'],
                'calendar_type' => $validated['calendar_type'],
                'birth_year' => (int) $validated['birth_year'],
                'birth_month' => (int) $validated['birth_month'],
                'birth_day' => (int) $validated['birth_day'],
                'is_leap_month' => (bool) ($validated['is_leap_month'] ?? false),
                'birth_time_mode' => $validated['birth_time_mode'],
                'birth_hour' => $validated['birth_hour'] ?? null,
                'birth_minute' => $validated['birth_minute'] ?? null,
                'birth_branch_index' => $validated['birth_branch_index'] ?? null,
                'birth_time_window' => $validated['birth_time_window'] ?? null,
                'birth_timezone' => (string) ($validated['birth_timezone'] ?? 'Asia/Ho_Chi_Minh'),
                'school_code' => TuViCalculationEngine::SCHOOL_CODE,
                'school_version' => TuViCalculationEngine::RULE_VERSION,
                'leap_month_rule' => (string) ($validated['leap_month_rule'] ?? 'split_15'),
                'late_rat_rule' => (string) ($validated['late_rat_rule'] ?? 'next_lunar_day'),
                'is_primary_self' => ($validated['relationship_label'] ?? 'self') === 'self'
                    && ! TuViProfile::where('user_id', $user->id)->where('is_primary_self', true)->exists(),
            ]);
            $nextVersion = 1;
        }

        $inputHash = hash('sha256', json_encode([
            $validated['gender_yin_yang'],
            $validated['calendar_type'],
            $validated['birth_year'],
            $validated['birth_month'],
            $validated['birth_day'],
            (bool) ($validated['is_leap_month'] ?? false),
            $validated['birth_time_mode'],
            $validated['birth_hour'] ?? null,
            $validated['birth_branch_index'] ?? null,
            $validated['birth_time_window'] ?? null,
            $validated['birth_timezone'] ?? 'Asia/Ho_Chi_Minh',
            $validated['leap_month_rule'] ?? 'split_15',
            $validated['late_rat_rule'] ?? 'next_lunar_day',
            $viewedYear,
            TuViCalculationEngine::RULE_VERSION,
        ]));

        $chartSlug = 'la-so-'.Str::slug($profile->nickname ?: 'thien-menh').'-v'.$nextVersion.'-'.Str::lower(Str::random(5));

        $chartModel = TuViChart::create([
            'profile_id' => $profile->id,
            'user_id' => $user->id,
            'slug' => $chartSlug,
            'version_number' => $nextVersion,
            'input_hash' => $inputHash,
            'school_code' => $chartData['school_code'],
            'rule_version' => $chartData['rule_version'],
            'kb_version' => $chartData['kb_version'],
            'viewed_year' => $viewedYear,
            'focus_topic' => $focusTopic,
            'chart_payload_json' => $chartData,
            'patterns_json' => $ruleAnalysis['verified_patterns'] ?? [],
            'interpretation_json' => $interpretation,
            'ai_powered' => (bool) ($interpretation['ai_powered'] ?? false),
            'ai_model' => $interpretation['ai_model'] ?? null,
            'ai_status' => (string) ($interpretation['ai_status'] ?? 'fallback'),
            'privacy_mode' => 'private',
            'share_hide_birth_info' => true,
            'idempotency_key' => $idempotencyKey,
        ]);

        return response()->json([
            'success' => true,
            'idempotent_replay' => false,
            'slug' => $chartModel->slug,
            'result_url' => route('tuvi.show', $chartModel->slug),
            'chart' => $chartData,
            'rule_analysis' => $ruleAnalysis,
            'interpretation' => $interpretation,
        ]);
    }

    /**
     * Trang chi tiết Lá Số Thiên Mệnh riêng tư (/tu-vi/la-so/{slug}) — Bắt buộc chính chủ (403 cho người khác).
     */
    public function show(Request $request, string $slug): View
    {
        $chartModel = TuViChart::with(['profile', 'followups', 'journals'])
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $this->canAccessPrivate($request->user(), $chartModel->user_id)) {
            abort(403, 'Lá số Tử Vi này thuộc không gian riêng tư của chủ sở hữu.');
        }

        $chart = is_array($chartModel->chart_payload_json) ? $chartModel->chart_payload_json : [];
        $ruleAnalysis = $this->ruleEngine->analyzeChart($chart);
        $interpretation = is_array($chartModel->interpretation_json) ? $chartModel->interpretation_json : [];
        $palacesMeta = $this->engine->getPalacesMeta();
        $focusTopicLabel = self::FOCUS_TOPICS[$chartModel->focus_topic] ?? 'Khám phá toàn bộ lá số';
        $shareUrl = $chartModel->isSharedActive() ? route('tuvi.shared_card', $chartModel->share_token) : null;
        $endpoints = [
            'reinterpret' => route('tuvi.reinterpret', $chartModel->slug),
            'followup' => route('tuvi.followup', $chartModel->slug),
            'journal' => route('tuvi.journal', $chartModel->slug),
            'toggle_share' => route('tuvi.toggle_share', $chartModel->slug),
            'feedback' => route('tuvi.feedback', $chartModel->slug),
        ];

        $versions = TuViChart::where('profile_id', $chartModel->profile_id)
            ->orderByDesc('version_number')
            ->get(['id', 'slug', 'version_number', 'viewed_year', 'created_at']);

        return view('client.pages.tuvi.show', compact(
            'chartModel',
            'chart',
            'ruleAnalysis',
            'interpretation',
            'palacesMeta',
            'focusTopicLabel',
            'shareUrl',
            'endpoints',
            'versions'
        ));
    }

    /**
     * Thẻ Chia Sẻ ẩn danh (/tu-vi/chia-se/{shareToken}) — Ẩn tên thật & ngày giờ sinh, noindex.
     */
    public function sharedCard(string $shareToken): View
    {
        $chartModel = TuViChart::where('share_token', $shareToken)
            ->where('privacy_mode', 'shared_card')
            ->whereNull('share_revoked_at')
            ->firstOrFail();

        $chart = is_array($chartModel->chart_payload_json) ? $chartModel->chart_payload_json : [];
        $interpretation = is_array($chartModel->interpretation_json) ? $chartModel->interpretation_json : [];

        return view('client.pages.tuvi.shared-card', compact('chartModel', 'chart', 'interpretation'));
    }

    /**
     * Luận giải lại AI trên chính lá số đã lưu (không đổi lá số).
     */
    public function reinterpret(Request $request, string $slug): JsonResponse
    {
        $this->extendExecutionTimeout(120);

        $chartModel = TuViChart::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $chartModel->user_id)) {
            abort(403);
        }

        $chart = is_array($chartModel->chart_payload_json) ? $chartModel->chart_payload_json : [];
        $ruleAnalysis = $this->ruleEngine->analyzeChart($chart);
        $interpretation = $this->interpreter->interpretChart($chart, $ruleAnalysis, $chartModel->focus_topic, allowAi: true);

        $chartModel->update([
            'interpretation_json' => $interpretation,
            'ai_powered' => (bool) ($interpretation['ai_powered'] ?? false),
            'ai_model' => $interpretation['ai_model'] ?? null,
            'ai_status' => (string) ($interpretation['ai_status'] ?? 'fallback'),
        ]);

        return response()->json([
            'success' => true,
            'ai_powered' => $chartModel->ai_powered,
            'interpretation' => $interpretation,
        ]);
    }

    /**
     * Trợ Lý Tử Vi AI — Hỏi sâu khóa chặt lá số cá nhân (tối đa 8 lượt/lá số).
     */
    public function followup(Request $request, string $slug): JsonResponse
    {
        $this->extendExecutionTimeout(120);

        $chartModel = TuViChart::with('followups')->where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $chartModel->user_id)) {
            abort(403);
        }

        if ($chartModel->followups->count() >= 8) {
            return response()->json([
                'success' => false,
                'message' => 'Mỗi lá số hỗ trợ tối đa 8 lượt Hỏi Sâu AI để giữ sự mạch lạc và tập trung.',
            ], 422);
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:4', 'max:320', new NoProfanity],
            'palace_code' => ['nullable', 'string', 'max:32'],
        ]);

        $question = trim(strip_tags($validated['question']));
        $safety = $this->safetyGuard->assessText($question);
        if (! $safety['allow']) {
            return response()->json([
                'success' => false,
                'code' => 'SAFETY_CRISIS_BLOCK',
                'message' => $safety['notice'],
                'safety' => $safety,
            ], 422);
        }

        $history = $chartModel->followups
            ->map(fn (TuViChartFollowup $f): array => ['question' => $f->question, 'answer' => $f->answer])
            ->all();

        $reply = $this->interpreter->answerFollowup($chartModel, $question, $validated['palace_code'] ?? null, $history);

        $record = TuViChartFollowup::create([
            'chart_id' => $chartModel->id,
            'user_id' => $request->user()?->id,
            'palace_code' => $validated['palace_code'] ?? null,
            'question' => $question,
            'answer' => $reply['answer'],
            'ai_powered' => $reply['ai_powered'],
            'ai_model' => $reply['ai_model'],
        ]);

        return response()->json([
            'success' => true,
            'followup' => [
                'id' => $record->id,
                'question' => $record->question,
                'answer' => $record->answer,
                'created_at' => $record->created_at?->format('H:i • d/m/Y'),
            ],
        ]);
    }

    /**
     * Lưu Nhật ký chiêm nghiệm 30 / 90 ngày.
     */
    public function saveJournal(Request $request, string $slug): JsonResponse
    {
        $chartModel = TuViChart::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $chartModel->user_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'milestone_days' => ['nullable', 'integer', 'in:0,30,90'],
            'focus_note' => ['nullable', 'string', 'max:500'],
            'actions_taken' => ['nullable', 'string', 'max:500'],
            'lessons_learned' => ['nullable', 'string', 'max:500'],
        ]);

        $journal = TuViChartJournal::create([
            'chart_id' => $chartModel->id,
            'user_id' => $request->user()?->id,
            'milestone_days' => (int) ($validated['milestone_days'] ?? 30),
            'focus_note' => trim(strip_tags((string) ($validated['focus_note'] ?? ''))) ?: null,
            'actions_taken' => trim(strip_tags((string) ($validated['actions_taken'] ?? ''))) ?: null,
            'lessons_learned' => trim(strip_tags((string) ($validated['lessons_learned'] ?? ''))) ?: null,
        ]);

        return response()->json([
            'success' => true,
            'journal' => [
                'id' => $journal->id,
                'milestone_days' => $journal->milestone_days,
                'focus_note' => $journal->focus_note,
                'actions_taken' => $journal->actions_taken,
                'lessons_learned' => $journal->lessons_learned,
                'created_at' => $journal->created_at?->format('H:i • d/m/Y'),
            ],
        ]);
    }

    /**
     * Bật hoặc thu hồi Thẻ Chia Sẻ ẩn thông tin sinh.
     */
    public function toggleShare(Request $request, string $slug): JsonResponse
    {
        $chartModel = TuViChart::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $chartModel->user_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:enable,revoke'],
        ]);

        if ($validated['action'] === 'enable') {
            $token = $chartModel->isSharedActive() ? (string) $chartModel->share_token : Str::lower(Str::random(32));
            $chartModel->update([
                'privacy_mode' => 'shared_card',
                'share_token' => $token,
                'share_revoked_at' => null,
                'share_hide_birth_info' => true,
            ]);

            return response()->json([
                'success' => true,
                'shared' => true,
                'share_url' => route('tuvi.shared_card', $token),
            ]);
        }

        $chartModel->update([
            'privacy_mode' => 'private',
            'share_token' => null,
            'share_revoked_at' => now(),
        ]);

        return response()->json(['success' => true, 'shared' => false, 'share_url' => null]);
    }

    /**
     * Phản hồi hữu ích & Đánh dấu Yêu thích.
     */
    public function saveFeedback(Request $request, string $slug): JsonResponse
    {
        $chartModel = TuViChart::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $chartModel->user_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'feedback_rating' => ['nullable', 'in:very_helpful,helpful,neutral,not_relevant'],
            'is_favorite' => ['nullable', 'boolean'],
        ]);

        $updates = [];
        if (isset($validated['feedback_rating'])) {
            $updates['feedback_rating'] = $validated['feedback_rating'];
        }
        if (isset($validated['is_favorite'])) {
            $updates['is_favorite'] = (bool) $validated['is_favorite'];
        }
        if ($updates !== []) {
            $chartModel->update($updates);
        }

        return response()->json([
            'success' => true,
            'feedback_rating' => $chartModel->feedback_rating,
            'is_favorite' => $chartModel->is_favorite,
        ]);
    }

    /**
     * Xóa lá số khỏi Hồ Sơ Thiên Mệnh của chính chủ.
     */
    public function destroy(Request $request, string $slug): RedirectResponse
    {
        $chartModel = TuViChart::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $chartModel->user_id)) {
            abort(403);
        }

        $chartModel->delete();

        return redirect()->route('tuvi.profiles')->with('success', 'Đã xóa vĩnh viễn lá số khỏi Hồ Sơ Thiên Mệnh của bạn.');
    }

    /**
     * Hồ Sơ Thiên Mệnh cá nhân (/tu-vi/ho-so) — yêu cầu đăng nhập.
     */
    public function profiles(Request $request): View
    {
        $user = $request->user();
        $charts = TuViChart::with('profile')
            ->withCount(['followups', 'journals'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12);

        $synastryReports = TuViSynastryReport::with(['chartA.profile', 'chartB.profile'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(8)
            ->get();

        return view('client.pages.tuvi.profiles', compact('charts', 'synastryReports'));
    }

    /**
     * Trang Nhân Duyên Song Mệnh (/tu-vi/nhan-duyen).
     */
    public function synastryIndex(Request $request): View
    {
        $user = $request->user();
        $canUseFree = $this->canUseWithPremiumLogin($user);
        $charts = $canUseFree && $user
            ? TuViChart::with('profile')->where('user_id', $user->id)->latest()->get()
            : collect();
        $synastryTopics = TuViKnowledgeRuleEngine::SYNASTRY_TOPICS;

        return view('client.pages.tuvi.synastry', compact('canUseFree', 'charts', 'synastryTopics'));
    }

    /**
     * Tạo báo cáo Nhân Duyên Song Mệnh từ 2 lá số độc lập của chính chủ (có xác nhận đồng thuận).
     */
    public function synastryCreate(Request $request): JsonResponse
    {
        $this->extendExecutionTimeout(120);

        $user = $request->user();
        if (! $this->canUseWithPremiumLogin($user)) {
            return response()->json(['success' => false, 'code' => 'PREMIUM_LOGIN_REQUIRED', 'message' => 'Vui lòng đăng nhập để sử dụng Nhân Duyên Song Mệnh miễn phí.'], 403);
        }

        $validated = $request->validate([
            'chart_a_id' => ['required', 'integer'],
            'chart_b_id' => ['required', 'integer', 'different:chart_a_id'],
            'partner_consent_confirmed' => ['required', 'accepted'],
            'couple_note' => ['nullable', 'string', 'max:500', new NoProfanity],
        ]);

        $chartA = TuViChart::where('id', (int) $validated['chart_a_id'])->where('user_id', $user->id)->firstOrFail();
        $chartB = TuViChart::where('id', (int) $validated['chart_b_id'])->where('user_id', $user->id)->firstOrFail();

        $basis = $this->ruleEngine->buildSynastryBasis(
            is_array($chartA->chart_payload_json) ? $chartA->chart_payload_json : [],
            is_array($chartB->chart_payload_json) ? $chartB->chart_payload_json : []
        );

        $coupleAnswers = ! empty($validated['couple_note'])
            ? ['couple_note' => trim(strip_tags((string) $validated['couple_note']))]
            : [];

        $interpretation = $this->interpreter->interpretSynastry($basis, $coupleAnswers, allowAi: true);

        $report = TuViSynastryReport::create([
            'user_id' => $user->id,
            'slug' => 'song-menh-'.Str::lower(Str::random(8)),
            'chart_a_id' => $chartA->id,
            'chart_b_id' => $chartB->id,
            'partner_consent_confirmed' => true,
            'couple_answers_json' => $coupleAnswers,
            'comparison_payload_json' => $basis,
            'interpretation_json' => $interpretation,
            'ai_powered' => (bool) ($interpretation['ai_powered'] ?? false),
            'ai_model' => $interpretation['ai_model'] ?? null,
            'privacy_mode' => 'private',
        ]);

        return response()->json([
            'success' => true,
            'slug' => $report->slug,
            'result_url' => route('tuvi.synastry.show', $report->slug),
        ]);
    }

    /**
     * Xem chi tiết báo cáo Nhân Duyên Song Mệnh (riêng tư 403).
     */
    public function synastryShow(Request $request, string $slug): View
    {
        $report = TuViSynastryReport::with(['chartA.profile', 'chartB.profile'])
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $this->canAccessPrivate($request->user(), $report->user_id)) {
            abort(403, 'Báo cáo Nhân Duyên Song Mệnh này thuộc không gian riêng tư của chủ sở hữu.');
        }

        return view('client.pages.tuvi.synastry-show', compact('report'));
    }

    /**
     * Bách Khoa Tử Vi công khai chuẩn SEO: 12 Cung / 14 Chính Tinh & Phụ Tinh / Cách Cục / 12 Con Giáp / Tử Vi Năm.
     */
    public function encyclopedia(string $section = '12-cung'): View
    {
        $palacesMeta = $this->engine->getPalacesMeta();
        $majorStarsMeta = $this->engine->getMajorStarsMeta();
        $auxStarsMeta = $this->engine->getAuxiliaryStarsMeta();
        $patternsCatalog = $this->ruleEngine->getPatternsCatalog();
        $zodiacsMeta = $this->engine->getZodiacsMeta();
        $manifest = $this->engine->getManifest();

        return view('client.pages.tuvi.encyclopedia', compact(
            'section',
            'palacesMeta',
            'majorStarsMeta',
            'auxStarsMeta',
            'patternsCatalog',
            'zodiacsMeta',
            'manifest'
        ));
    }

    protected function extendExecutionTimeout(int $seconds = 120): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit($seconds);
        }
        @ini_set('max_execution_time', (string) $seconds);
    }

    protected function canUseWithPremiumLogin(?User $user): bool
    {
        if (! $user || $this->planAccess->isGuestUser($user)) {
            return false;
        }

        return $this->planAccess->hasPlanAtLeast($user, 'premium');
    }

    protected function canAccessPrivate(?User $user, int $ownerId): bool
    {
        if (! $user) {
            return false;
        }

        return $user->id === $ownerId || $user->isAdmin();
    }

    /**
     * @return array{plan_slug: string, daily_limit: int, daily_used: int, daily_remaining: int, cooldown_interval_seconds: int, cooldown_label: string, cooldown_seconds: int, latest_chart_url: ?string}
     */
    protected function getUserQuota(?User $user): array
    {
        $planSlug = $user
            ? ($user->isAdmin() ? 'pro' : $this->planAccess->getEffectivePlanSlug($user))
            : 'premium';
        $policy = self::TIER_LIMITS[$planSlug] ?? self::TIER_LIMITS['premium'];

        if (! $user) {
            return [
                'plan_slug' => $planSlug,
                'daily_limit' => $policy['daily_limit'],
                'daily_used' => 0,
                'daily_remaining' => $policy['daily_limit'],
                'cooldown_interval_seconds' => $policy['cooldown_seconds'],
                'cooldown_label' => $policy['cooldown_label'],
                'cooldown_seconds' => 0,
                'latest_chart_url' => null,
            ];
        }

        $startOfVnDay = now('Asia/Ho_Chi_Minh')->startOfDay()->setTimezone((string) config('app.timezone', 'UTC'));
        $dailyUsed = TuViChart::where('user_id', $user->id)
            ->where('created_at', '>=', $startOfVnDay)
            ->count();

        $latest = TuViChart::where('user_id', $user->id)->latest('created_at')->first();
        $cooldownSeconds = 0;
        if ($policy['cooldown_seconds'] > 0 && $latest && $latest->created_at) {
            $nextTime = $latest->created_at->copy()->addSeconds($policy['cooldown_seconds']);
            if ($nextTime->isFuture()) {
                $cooldownSeconds = max(1, (int) ceil(now()->diffInSeconds($nextTime)));
            }
        }

        return [
            'plan_slug' => $planSlug,
            'daily_limit' => $policy['daily_limit'],
            'daily_used' => $dailyUsed,
            'daily_remaining' => max(0, $policy['daily_limit'] - $dailyUsed),
            'cooldown_interval_seconds' => $policy['cooldown_seconds'],
            'cooldown_label' => $policy['cooldown_label'],
            'cooldown_seconds' => $cooldownSeconds,
            'latest_chart_url' => $latest ? route('tuvi.show', $latest->slug) : null,
        ];
    }
}
