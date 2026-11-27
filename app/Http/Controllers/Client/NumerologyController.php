<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\NumerologyFollowup;
use App\Models\NumerologyJournal;
use App\Models\NumerologyProfile;
use App\Models\NumerologyReading;
use App\Models\NumerologyRelationshipReport;
use App\Models\User;
use App\Rules\NoProfanity;
use App\Services\Numerology\NumerologyAiInterpreterService;
use App\Services\Numerology\NumerologyCalculationEngine;
use App\Services\Numerology\NumerologyKnowledgeService;
use App\Services\Numerology\NumerologyMethodRegistry;
use App\Services\Numerology\NumerologySafetyGuardService;
use App\Services\Numerology\VietnameseNameNormalizer;
use App\Services\PlanAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class NumerologyController extends Controller
{
    /**
     * Hạn mức luận giải AI đồng bộ toàn hệ sinh thái Mây Hẹn (Dịch Tâm / Tử Vi / Thần Số Học):
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
        'toan_bo' => 'Toàn bộ bộ số cá nhân',
        'hieu_ban_than' => 'Hiểu bản thân & nội tâm',
        'tinh_yeu' => 'Tình yêu & nhân duyên',
        'hon_nhan' => 'Hôn nhân & gia đình',
        'su_nghiep' => 'Công việc & phát triển kỹ năng',
        'chu_ky_nam' => 'Chu kỳ năm & kế hoạch 30 ngày',
    ];

    /** @var array<string, string> */
    public const RELATIONSHIP_STAGES = [
        'dang_tim_hieu' => 'Đang tìm hiểu',
        'dang_yeu' => 'Đang yêu',
        'yeu_xa' => 'Yêu xa',
        'da_dinh_hon' => 'Đã đính hôn',
        'chuan_bi_ket_hon' => 'Chuẩn bị kết hôn',
        'da_ket_hon' => 'Đã kết hôn',
        'cai_thien_quan_he' => 'Đang muốn cải thiện mối quan hệ',
    ];

    public function __construct(
        protected NumerologyMethodRegistry $registry,
        protected NumerologyCalculationEngine $engine,
        protected NumerologyKnowledgeService $knowledge,
        protected NumerologyAiInterpreterService $interpreter,
        protected NumerologySafetyGuardService $safetyGuard,
        protected PlanAccessService $planAccess
    ) {}

    /**
     * Trang chính SEO: Tra Cứu Thần Số Học Online & Thần Số Học Tình Duyên Miễn Phí (/than-so-hoc).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $canUseAi = $this->canUseWithPremiumLogin($user);
        $currentPlanLabel = $this->planAccess->getTierLabel($this->planAccess->getEffectivePlanSlug($user));
        $quota = $this->getUserQuota($canUseAi ? $user : null);

        $manifest = $this->registry->getManifest();
        $archetypes = $this->knowledge->getArchetypes();
        $focusTopics = self::FOCUS_TOPICS;
        $relationshipStages = self::RELATIONSHIP_STAGES;

        // Hồ sơ mẫu minh họa chuẩn xác (không dùng PII thật)
        $sampleCalc = $this->engine->calculate([
            'display_name' => 'Hồ Sơ Mẫu — Minh An',
            'full_birth_name' => 'Đỗ Minh An',
            'birth_day' => 15,
            'birth_month' => 5,
            'birth_year' => 1995,
            'method_id' => NumerologyMethodRegistry::DEFAULT_METHOD,
            'y_mode' => VietnameseNameNormalizer::Y_MODE_VN,
        ]);
        $sampleEnriched = $this->knowledge->enrichCalculation($sampleCalc);

        $recentReadings = $canUseAi && $user
            ? NumerologyReading::with('profile')->where('user_id', $user->id)->latest()->limit(4)->get()
            : collect();

        return view('client.pages.numerology.index', compact(
            'canUseAi',
            'currentPlanLabel',
            'quota',
            'manifest',
            'archetypes',
            'focusTopics',
            'relationshipStages',
            'sampleCalc',
            'sampleEnriched',
            'recentReadings'
        ));
    }

    /**
     * API Tra cứu nhanh miễn phí (CẢ KHÁCH CHƯA ĐĂNG NHẬP) — trả toàn bộ chỉ số, bảng Cách tính của tôi, Biểu đồ 3×3, Kim tự tháp & nội dung kho tri thức chuẩn hóa.
     */
    public function calculatePreview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'display_name' => ['nullable', 'string', 'max:100', new NoProfanity],
            'full_birth_name' => ['nullable', 'string', 'max:160', new NoProfanity],
            'everyday_name' => ['nullable', 'string', 'max:100', new NoProfanity],
            'birth_day' => ['required', 'integer', 'min:1', 'max:31'],
            'birth_month' => ['required', 'integer', 'min:1', 'max:12'],
            'birth_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'method_id' => ['nullable', 'in:pythagorean_decoz_v1,phillips_vn_v1'],
            'y_mode' => ['nullable', 'in:vn_phonetic_y_v1,en_phonetic_y_v1'],
            'name_groups' => ['nullable', 'array'],
            'y_overrides' => ['nullable', 'array'],
            'viewed_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'focus_topic' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::FOCUS_TOPICS))],
        ]);

        try {
            $calc = $this->engine->calculate($validated);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $enriched = $this->knowledge->enrichCalculation($calc);
        $interpretation = $this->interpreter->interpretProfile(
            $calc,
            $enriched,
            (string) ($validated['focus_topic'] ?? 'toan_bo'),
            '',
            allowAi: false
        );

        return response()->json([
            'success' => true,
            'calculation' => $calc,
            'enriched' => $enriched,
            'interpretation' => $interpretation,
        ]);
    }

    /**
     * Lưu Hồ Sơ Thần Số Học & Chạy Luận Giải AI 6 Tầng (Miễn phí cho thành viên đăng nhập từ Premium 0đ trở lên).
     */
    public function store(Request $request): JsonResponse
    {
        $this->extendExecutionTimeout(300);

        $user = $request->user();
        if (! $this->canUseWithPremiumLogin($user)) {
            return response()->json([
                'success' => false,
                'code' => 'PREMIUM_LOGIN_REQUIRED',
                'message' => 'Bạn có thể tra cứu chỉ số miễn phí ngay trên trang. Để lưu Hồ Sơ riêng tư và mở Luận Giải AI 6 Tầng miễn phí, vui lòng đăng nhập (tự động cấp gói Thành Viên Premium 0đ).',
                'login_url' => route('login', ['redirect' => route('numerology.index')]),
                'register_url' => route('register'),
            ], 403);
        }

        $validated = $request->validate([
            'profile_id' => ['nullable', 'integer'],
            'display_name' => ['nullable', 'string', 'max:100', new NoProfanity],
            'full_birth_name' => ['nullable', 'string', 'max:160', new NoProfanity],
            'everyday_name' => ['nullable', 'string', 'max:100', new NoProfanity],
            'birth_day' => ['required', 'integer', 'min:1', 'max:31'],
            'birth_month' => ['required', 'integer', 'min:1', 'max:12'],
            'birth_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'method_id' => ['nullable', 'in:pythagorean_decoz_v1,phillips_vn_v1'],
            'y_mode' => ['nullable', 'in:vn_phonetic_y_v1,en_phonetic_y_v1'],
            'name_groups' => ['nullable', 'array'],
            'y_overrides' => ['nullable', 'array'],
            'viewed_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'focus_topic' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::FOCUS_TOPICS))],
            'relationship_status' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::RELATIONSHIP_STAGES))],
            'user_context_note' => ['nullable', 'string', 'max:500', new NoProfanity],
            'idempotency_key' => ['nullable', 'string', 'max:80'],
        ]);

        $idempotencyKey = ! empty($validated['idempotency_key'])
            ? 'num_u'.$user->id.'_'.Str::limit(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $validated['idempotency_key']), 60, '')
            : null;

        if ($idempotencyKey !== null) {
            $existing = NumerologyReading::where('idempotency_key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'idempotent_replay' => true,
                    'slug' => $existing->slug,
                    'result_url' => route('numerology.show', $existing->slug),
                ]);
            }
        }

        $quota = $this->getUserQuota($user);
        if ($quota['daily_remaining'] <= 0) {
            return response()->json([
                'success' => false,
                'code' => 'NUMEROLOGY_DAILY_LIMIT_REACHED',
                'message' => 'Bạn đã dùng hết '.$quota['daily_limit'].' lượt luận giải AI Thần Số Học trong ngày hôm nay theo gói hiện tại. Bạn vẫn có thể tra cứu nhanh miễn phí hoặc xem lại các hồ sơ đã lưu.',
                'quota' => $quota,
            ], 429);
        }
        if ($quota['cooldown_seconds'] > 0) {
            $mins = intdiv($quota['cooldown_seconds'], 60);
            $secs = $quota['cooldown_seconds'] % 60;
            $waitLabel = $mins > 0 ? ($secs > 0 ? "{$mins} phút {$secs} giây" : "{$mins} phút") : "{$secs} giây";

            return response()->json([
                'success' => false,
                'code' => 'NUMEROLOGY_COOLDOWN_ACTIVE',
                'message' => "Theo gói hiện tại ({$quota['cooldown_label']}), vui lòng chờ thêm {$waitLabel} trước lượt luận giải AI tiếp theo.",
                'retry_after_seconds' => $quota['cooldown_seconds'],
                'quota' => $quota,
            ], 429);
        }

        $contextNote = trim(strip_tags((string) ($validated['user_context_note'] ?? '')));
        if ($contextNote !== '') {
            $safety = $this->safetyGuard->assessText($contextNote);
            if (! $safety['allow']) {
                return response()->json([
                    'success' => false,
                    'code' => 'SAFETY_CRISIS_BLOCK',
                    'message' => $safety['notice'],
                ], 422);
            }
        }

        try {
            $calc = $this->engine->calculate($validated);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $focusTopic = (string) ($validated['focus_topic'] ?? 'toan_bo');
        $enriched = $this->knowledge->enrichCalculation($calc);
        $interpretation = $this->interpreter->interpretProfile($calc, $enriched, $focusTopic, $contextNote, allowAi: true);

        // Quản lý phiên bản hồ sơ (không âm thầm ghi đè bản cũ khi sửa tên/ngày sinh/phương pháp)
        $profile = ! empty($validated['profile_id'])
            ? NumerologyProfile::where('id', (int) $validated['profile_id'])->where('user_id', $user->id)->first()
            : null;

        $displayName = trim(strip_tags((string) ($validated['display_name'] ?? '')))
            ?: (trim(strip_tags((string) ($validated['full_birth_name'] ?? ''))) ?: 'Hồ Sơ Con Số');

        if ($profile) {
            $profile->update([
                'display_name' => $displayName,
                'full_birth_name' => trim(strip_tags((string) ($validated['full_birth_name'] ?? ''))) ?: null,
                'everyday_name' => trim(strip_tags((string) ($validated['everyday_name'] ?? ''))) ?: null,
                'name_groups_json' => $validated['name_groups'] ?? null,
                'y_mode' => (string) ($validated['y_mode'] ?? VietnameseNameNormalizer::Y_MODE_VN),
                'y_overrides_json' => $validated['y_overrides'] ?? null,
                'birth_date' => $calc['birth']['iso'],
                'relationship_status' => $validated['relationship_status'] ?? $profile->relationship_status,
                'method_id' => $calc['method_id'],
                'method_version' => $calc['method_version'],
            ]);
            $nextVersion = ((int) $profile->readings()->max('version_number')) + 1;
        } else {
            $profile = NumerologyProfile::create([
                'user_id' => $user->id,
                'slug' => 'tsh-'.Str::slug($displayName ?: 'ho-so').'-'.Str::lower(Str::random(6)),
                'display_name' => $displayName,
                'full_birth_name' => trim(strip_tags((string) ($validated['full_birth_name'] ?? ''))) ?: null,
                'everyday_name' => trim(strip_tags((string) ($validated['everyday_name'] ?? ''))) ?: null,
                'name_groups_json' => $validated['name_groups'] ?? null,
                'y_mode' => (string) ($validated['y_mode'] ?? VietnameseNameNormalizer::Y_MODE_VN),
                'y_overrides_json' => $validated['y_overrides'] ?? null,
                'birth_date' => $calc['birth']['iso'],
                'calendar_input_type' => 'solar',
                'relationship_status' => $validated['relationship_status'] ?? null,
                'method_id' => $calc['method_id'],
                'method_version' => $calc['method_version'],
                'is_primary_self' => ! NumerologyProfile::where('user_id', $user->id)->where('is_primary_self', true)->exists(),
            ]);
            $nextVersion = 1;
        }

        $inputHash = hash('sha256', json_encode([
            $calc['birth']['iso'],
            $validated['full_birth_name'] ?? '',
            $validated['everyday_name'] ?? '',
            $validated['y_mode'] ?? VietnameseNameNormalizer::Y_MODE_VN,
            $validated['y_overrides'] ?? [],
            $validated['name_groups'] ?? null,
            $calc['method_id'],
            $calc['viewed_date']['year'],
        ]));

        $reading = NumerologyReading::create([
            'profile_id' => $profile->id,
            'user_id' => $user->id,
            'slug' => 'ho-so-'.Str::slug($displayName ?: 'con-so').'-v'.$nextVersion.'-'.Str::lower(Str::random(5)),
            'version_number' => $nextVersion,
            'input_hash' => $inputHash,
            'method_id' => $calc['method_id'],
            'method_version' => $calc['method_version'],
            'kb_version' => $calc['kb_version'],
            'viewed_year' => $calc['viewed_date']['year'],
            'focus_topic' => $focusTopic,
            'user_context_note' => $contextNote !== '' ? $contextNote : null,
            'calculation_payload_json' => $calc,
            'interpretation_json' => $interpretation,
            'ai_powered' => (bool) ($interpretation['ai_powered'] ?? false),
            'ai_model' => $interpretation['ai_model'] ?? null,
            'ai_status' => (string) ($interpretation['ai_status'] ?? 'fallback'),
            'privacy_mode' => 'private',
            'share_hide_birth_date' => true,
            'idempotency_key' => $idempotencyKey,
        ]);

        return response()->json([
            'success' => true,
            'idempotent_replay' => false,
            'slug' => $reading->slug,
            'result_url' => route('numerology.show', $reading->slug),
            'calculation' => $calc,
            'interpretation' => $interpretation,
        ]);
    }

    /**
     * Trang Kết Quả Hồ Sơ Nghệ Thuật 10 Chương (/than-so-hoc/ket-qua/{slug}) — Riêng tư 403 + noindex.
     */
    public function show(Request $request, string $slug): View
    {
        $reading = NumerologyReading::with(['profile', 'followups', 'journals'])
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $this->canAccessPrivate($request->user(), $reading->user_id)) {
            abort(403, 'Hồ sơ Thần Số Học này thuộc không gian riêng tư của chủ sở hữu.');
        }

        $calc = is_array($reading->calculation_payload_json) ? $reading->calculation_payload_json : [];
        $enriched = $this->knowledge->enrichCalculation($calc);
        $interpretation = is_array($reading->interpretation_json) ? $reading->interpretation_json : [];
        $archetypes = $this->knowledge->getArchetypes();
        $focusTopicLabel = self::FOCUS_TOPICS[$reading->focus_topic] ?? 'Toàn bộ bộ số cá nhân';
        $shareUrl = $reading->isSharedActive() ? route('numerology.shared_card', $reading->share_token) : null;
        $versions = NumerologyReading::where('profile_id', $reading->profile_id)
            ->orderByDesc('version_number')
            ->get(['id', 'slug', 'version_number', 'method_id', 'viewed_year', 'created_at']);

        $sectionDefinitions = NumerologyAiInterpreterService::SECTION_DEFINITIONS;
        $indicatorLabels = NumerologyAiInterpreterService::INDICATOR_LABELS;

        $scopeLabelsByCode = [];
        foreach ($sectionDefinitions as $def) {
            $scopeLabelsByCode[$def['scope_code']] = $def['label'];
        }
        foreach ($indicatorLabels as $indCode => $indLabel) {
            $scopeLabelsByCode['ind:'.$indCode] = $indLabel;
            $scopeLabelsByCode[$indCode] = $indLabel;
        }

        $followupsPayload = $reading->followups
            ->map(function (NumerologyFollowup $f) use ($scopeLabelsByCode): array {
                $scopeCode = $f->topic_code ?: null;

                return [
                    'id' => $f->id,
                    'topic_code' => $scopeCode,
                    'scope_label' => $scopeCode ? ($scopeLabelsByCode[$scopeCode] ?? $scopeCode) : 'Tổng thể hồ sơ',
                    'question' => $f->question,
                    'answer' => $f->answer,
                    'ai_powered' => (bool) $f->ai_powered,
                    'created_at' => $f->created_at?->format('H:i • d/m/Y'),
                ];
            })
            ->values()
            ->all();

        $endpoints = [
            'followup' => route('numerology.followup', $reading->slug),
            'journal' => route('numerology.journal', $reading->slug),
            'toggle_share' => route('numerology.toggle_share', $reading->slug),
            'feedback' => route('numerology.feedback', $reading->slug),
        ];

        return view('client.pages.numerology.show', compact(
            'reading',
            'calc',
            'enriched',
            'interpretation',
            'archetypes',
            'focusTopicLabel',
            'shareUrl',
            'versions',
            'endpoints',
            'sectionDefinitions',
            'indicatorLabels',
            'followupsPayload'
        ));
    }

    /**
     * Thẻ Chia Sẻ Nghệ Thuật Ẩn Ngày Sinh (/than-so-hoc/chia-se/{shareToken}) — noindex, thu hồi 404.
     */
    public function sharedCard(string $shareToken): View
    {
        $reading = NumerologyReading::where('share_token', $shareToken)
            ->where('privacy_mode', 'shared_card')
            ->whereNull('share_revoked_at')
            ->firstOrFail();

        $calc = is_array($reading->calculation_payload_json) ? $reading->calculation_payload_json : [];
        $interpretation = is_array($reading->interpretation_json) ? $reading->interpretation_json : [];

        return view('client.pages.numerology.shared-card', compact('reading', 'calc', 'interpretation'));
    }

    public function followup(Request $request, string $slug): JsonResponse
    {
        $this->extendExecutionTimeout(300);

        $reading = NumerologyReading::with('followups')->where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $reading->user_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:4', 'max:600', new NoProfanity],
            'topic_code' => ['nullable', 'string', 'max:48'],
            'section_key' => ['nullable', 'string', 'max:40'],
        ]);

        $question = trim(strip_tags($validated['question']));
        $safety = $this->safetyGuard->assessText($question);
        if (! $safety['allow']) {
            return response()->json(['success' => false, 'code' => 'SAFETY_CRISIS_BLOCK', 'message' => $safety['notice']], 422);
        }

        $calc = is_array($reading->calculation_payload_json) ? $reading->calculation_payload_json : [];
        $resolvedScope = $this->interpreter->resolveQuestionScope(
            $calc,
            $validated['topic_code'] ?? null,
            $validated['section_key'] ?? null
        );
        if (! $resolvedScope['valid']) {
            return response()->json([
                'success' => false,
                'code' => 'INVALID_SCOPE',
                'message' => 'Mục hoặc chỉ số bạn chọn để hỏi sâu chưa hợp lệ trên hồ sơ này.',
            ], 422);
        }

        $maxFollowups = 16;
        if ($reading->followups->count() >= $maxFollowups) {
            return response()->json(['success' => false, 'message' => "Mỗi hồ sơ hỗ trợ tối đa {$maxFollowups} lượt Hỏi Sâu AI để giữ chiều sâu mạch lạc."], 422);
        }

        $history = $reading->followups->map(fn (NumerologyFollowup $f): array => [
            'question' => $f->question,
            'answer' => $f->answer,
            'scope_code' => $f->topic_code,
        ])->all();

        $reply = $this->interpreter->answerFollowup(
            $reading,
            $question,
            $history,
            $validated['topic_code'] ?? null,
            $validated['section_key'] ?? null
        );
        if (! empty($reply['blocked'])) {
            return response()->json([
                'success' => false,
                'code' => $reply['code'] ?? 'SECURITY_POLICY_BLOCK',
                'message' => $reply['answer'],
            ], 422);
        }

        $record = NumerologyFollowup::create([
            'reading_id' => $reading->id,
            'user_id' => $request->user()?->id,
            'topic_code' => $reply['scope_code'] ?? null,
            'question' => $question,
            'answer' => $reply['answer'],
            'ai_powered' => $reply['ai_powered'],
            'ai_model' => $reply['ai_model'],
        ]);

        return response()->json([
            'success' => true,
            'followup' => [
                'id' => $record->id,
                'topic_code' => $record->topic_code,
                'scope_label' => $reply['scope_label'] ?? ($record->topic_code ?: 'Tổng thể hồ sơ'),
                'question' => $record->question,
                'answer' => $record->answer,
                'ai_powered' => (bool) $record->ai_powered,
                'created_at' => $record->created_at?->format('H:i • d/m/Y'),
            ],
        ]);
    }

    public function saveJournal(Request $request, string $slug): JsonResponse
    {
        $reading = NumerologyReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $reading->user_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'milestone_days' => ['nullable', 'integer', 'in:0,7,30,90'],
            'current_goal' => ['nullable', 'string', 'max:500'],
            'actions_taken' => ['nullable', 'string', 'max:500'],
            'lessons_learned' => ['nullable', 'string', 'max:500'],
            'relationship_note' => ['nullable', 'string', 'max:500'],
        ]);

        $journal = NumerologyJournal::create([
            'reading_id' => $reading->id,
            'user_id' => $request->user()?->id,
            'milestone_days' => (int) ($validated['milestone_days'] ?? 30),
            'current_goal' => trim(strip_tags((string) ($validated['current_goal'] ?? ''))) ?: null,
            'actions_taken' => trim(strip_tags((string) ($validated['actions_taken'] ?? ''))) ?: null,
            'lessons_learned' => trim(strip_tags((string) ($validated['lessons_learned'] ?? ''))) ?: null,
            'relationship_note' => trim(strip_tags((string) ($validated['relationship_note'] ?? ''))) ?: null,
        ]);

        return response()->json([
            'success' => true,
            'journal' => [
                'id' => $journal->id,
                'milestone_days' => $journal->milestone_days,
                'current_goal' => $journal->current_goal,
                'actions_taken' => $journal->actions_taken,
                'created_at' => $journal->created_at?->format('H:i • d/m/Y'),
            ],
        ]);
    }

    public function toggleShare(Request $request, string $slug): JsonResponse
    {
        $reading = NumerologyReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $reading->user_id)) {
            abort(403);
        }

        $validated = $request->validate(['action' => ['required', 'in:enable,revoke']]);

        if ($validated['action'] === 'enable') {
            $token = $reading->isSharedActive() ? (string) $reading->share_token : Str::lower(Str::random(32));
            $reading->update([
                'privacy_mode' => 'shared_card',
                'share_token' => $token,
                'share_revoked_at' => null,
                'share_hide_birth_date' => true,
            ]);

            return response()->json(['success' => true, 'shared' => true, 'share_url' => route('numerology.shared_card', $token)]);
        }

        $reading->update(['privacy_mode' => 'private', 'share_token' => null, 'share_revoked_at' => now()]);

        return response()->json(['success' => true, 'shared' => false, 'share_url' => null]);
    }

    public function saveFeedback(Request $request, string $slug): JsonResponse
    {
        $reading = NumerologyReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $reading->user_id)) {
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
            $reading->update($updates);
        }

        return response()->json(['success' => true, 'feedback_rating' => $reading->feedback_rating, 'is_favorite' => $reading->is_favorite]);
    }

    public function destroy(Request $request, string $slug): RedirectResponse
    {
        $reading = NumerologyReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $reading->user_id)) {
            abort(403);
        }

        $reading->delete();

        return redirect()->route('numerology.profiles')->with('success', 'Đã xóa vĩnh viễn hồ sơ Thần Số Học khỏi danh sách của bạn.');
    }

    /**
     * Hồ sơ Thần Số Học đã lưu (/than-so-hoc/ho-so).
     */
    public function profiles(Request $request): View
    {
        $user = $request->user();
        $readings = NumerologyReading::with('profile')
            ->withCount(['followups', 'journals'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12);

        $loveReports = NumerologyRelationshipReport::with(['readingA.profile', 'readingB.profile'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(8)
            ->get();

        return view('client.pages.numerology.profiles', compact('readings', 'loveReports'));
    }

    /**
     * Thần Số Học Tình Duyên (/than-so-hoc/tinh-duyen).
     */
    public function loveIndex(Request $request): View
    {
        $user = $request->user();
        $canUseAi = $this->canUseWithPremiumLogin($user);
        $readings = $canUseAi && $user
            ? NumerologyReading::with('profile')->where('user_id', $user->id)->latest()->get()
            : collect();
        $loveTopics = NumerologyKnowledgeService::LOVE_TOPICS;
        $relationshipStages = self::RELATIONSHIP_STAGES;

        return view('client.pages.numerology.love', compact('canUseAi', 'readings', 'loveTopics', 'relationshipStages'));
    }

    /**
     * Tạo Báo Cáo Thần Số Học Tình Duyên (nhập trực tiếp 2 người HOẶC chọn 2 hồ sơ đã lưu).
     */
    public function loveStore(Request $request): JsonResponse
    {
        $this->extendExecutionTimeout(120);

        $user = $request->user();
        if (! $this->canUseWithPremiumLogin($user)) {
            return response()->json([
                'success' => false,
                'code' => 'PREMIUM_LOGIN_REQUIRED',
                'message' => 'Vui lòng đăng nhập tài khoản Mây Hẹn (miễn phí gói Thành Viên Premium 0đ) để tạo và lưu Báo Cáo Thần Số Học Tình Duyên riêng tư.',
                'login_url' => route('login', ['redirect' => route('numerology.love.index')]),
            ], 403);
        }

        $validated = $request->validate([
            'person_a_name' => ['nullable', 'string', 'max:160', new NoProfanity],
            'person_a_day' => ['required', 'integer', 'min:1', 'max:31'],
            'person_a_month' => ['required', 'integer', 'min:1', 'max:12'],
            'person_a_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'person_b_name' => ['nullable', 'string', 'max:160', new NoProfanity],
            'person_b_day' => ['required', 'integer', 'min:1', 'max:31'],
            'person_b_month' => ['required', 'integer', 'min:1', 'max:12'],
            'person_b_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'relationship_stage' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::RELATIONSHIP_STAGES))],
            'partner_consent_confirmed' => ['required', 'accepted'],
            'couple_note' => ['nullable', 'string', 'max:500', new NoProfanity],
        ]);

        try {
            $calcA = $this->engine->calculate([
                'display_name' => trim(strip_tags((string) ($validated['person_a_name'] ?? ''))) ?: 'Người thứ nhất',
                'full_birth_name' => trim(strip_tags((string) ($validated['person_a_name'] ?? ''))),
                'birth_day' => $validated['person_a_day'],
                'birth_month' => $validated['person_a_month'],
                'birth_year' => $validated['person_a_year'],
            ]);
            $calcB = $this->engine->calculate([
                'display_name' => trim(strip_tags((string) ($validated['person_b_name'] ?? ''))) ?: 'Người thứ hai',
                'full_birth_name' => trim(strip_tags((string) ($validated['person_b_name'] ?? ''))),
                'birth_day' => $validated['person_b_day'],
                'birth_month' => $validated['person_b_month'],
                'birth_year' => $validated['person_b_year'],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $readingA = $this->persistStandaloneReading($user, $calcA);
        $readingB = $this->persistStandaloneReading($user, $calcB);

        $stage = (string) ($validated['relationship_stage'] ?? 'dang_yeu');
        $comparison = $this->knowledge->buildRelationshipComparison($calcA, $calcB, $stage);
        $coupleNote = trim(strip_tags((string) ($validated['couple_note'] ?? '')));
        $coupleContext = $coupleNote !== '' ? ['note' => $coupleNote] : [];
        $interpretation = $this->interpreter->interpretRelationship($comparison, $coupleContext, allowAi: true);

        $report = NumerologyRelationshipReport::create([
            'user_id' => $user->id,
            'slug' => 'tinh-duyen-'.Str::lower(Str::random(8)),
            'reading_a_id' => $readingA->id,
            'reading_b_id' => $readingB->id,
            'relationship_stage' => $stage,
            'partner_consent_confirmed' => true,
            'couple_answers_json' => $coupleContext,
            'comparison_payload_json' => $comparison,
            'interpretation_json' => $interpretation,
            'ai_powered' => (bool) ($interpretation['ai_powered'] ?? false),
            'ai_model' => $interpretation['ai_model'] ?? null,
            'privacy_mode' => 'private',
        ]);

        return response()->json([
            'success' => true,
            'slug' => $report->slug,
            'result_url' => route('numerology.love.show', $report->slug),
        ]);
    }

    /**
     * Xem chi tiết Báo Cáo Tình Duyên & Không gian "Câu chuyện hai chúng ta" (Riêng tư 403).
     */
    public function loveShow(Request $request, string $slug): View
    {
        $report = NumerologyRelationshipReport::with(['readingA.profile', 'readingB.profile'])
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $this->canAccessPrivate($request->user(), $report->user_id)) {
            abort(403, 'Báo cáo Thần Số Học Tình Duyên này thuộc không gian riêng tư của chủ sở hữu.');
        }

        $stageLabel = self::RELATIONSHIP_STAGES[$report->relationship_stage] ?? 'Đang yêu';
        $coupleQuestions = NumerologyKnowledgeService::COUPLE_QUESTIONS;

        return view('client.pages.numerology.love-show', compact('report', 'stageLabel', 'coupleQuestions'));
    }

    /**
     * Lưu tương tác Không gian "Câu chuyện hai chúng ta" (5 câu hỏi, Lời nhắn & Lời hẹn 30 ngày).
     */
    public function saveCoupleStory(Request $request, string $slug): JsonResponse
    {
        $report = NumerologyRelationshipReport::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivate($request->user(), $report->user_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'answers' => ['nullable', 'array'],
            'promise_a' => ['nullable', 'string', 'max:320', new NoProfanity],
            'promise_b' => ['nullable', 'string', 'max:320', new NoProfanity],
            'message_note' => ['nullable', 'string', 'max:400', new NoProfanity],
        ]);

        $cleanAnswers = [];
        foreach (($validated['answers'] ?? []) as $k => $v) {
            $cleanAnswers[(string) $k] = trim(strip_tags((string) $v));
        }

        $messages = is_array($report->couple_messages_json) ? $report->couple_messages_json : [];
        $newMsg = trim(strip_tags((string) ($validated['message_note'] ?? '')));
        if ($newMsg !== '') {
            $messages[] = [
                'text' => $newMsg,
                'created_at' => now('Asia/Ho_Chi_Minh')->format('H:i • d/m/Y'),
            ];
        }

        $report->update([
            'couple_answers_json' => array_merge(is_array($report->couple_answers_json) ? $report->couple_answers_json : [], ['questions' => $cleanAnswers]),
            'promise_30_days_json' => [
                'person_a' => trim(strip_tags((string) ($validated['promise_a'] ?? ''))),
                'person_b' => trim(strip_tags((string) ($validated['promise_b'] ?? ''))),
                'updated_at' => now('Asia/Ho_Chi_Minh')->format('d/m/Y'),
            ],
            'couple_messages_json' => array_slice($messages, -10),
        ]);

        return response()->json([
            'success' => true,
            'messages' => $report->couple_messages_json,
            'promise_30_days' => $report->promise_30_days_json,
        ]);
    }

    /**
     * Bách Khoa Thần Số Học công khai chuẩn SEO.
     */
    public function library(string $section = 'thu-vien'): View
    {
        $manifest = $this->registry->getManifest();
        $archetypes = $this->knowledge->getArchetypes();
        $indicatorLenses = $this->knowledge->getIndicatorLenses();
        $cyclesCatalog = $this->knowledge->getCyclesCatalog();
        $arrowsCatalog = $this->knowledge->getBirthChartArrowsCatalog();
        $lovePairs = $this->knowledge->getRelationshipPairs();

        return view('client.pages.numerology.library', compact(
            'section',
            'manifest',
            'archetypes',
            'indicatorLenses',
            'cyclesCatalog',
            'arrowsCatalog',
            'lovePairs'
        ));
    }

    /**
     * @param  array<string, mixed>  $calc
     */
    protected function persistStandaloneReading(User $user, array $calc): NumerologyReading
    {
        $profile = NumerologyProfile::create([
            'user_id' => $user->id,
            'slug' => 'tsh-'.Str::slug($calc['display_name'] ?: 'doi-tac').'-'.Str::lower(Str::random(6)),
            'display_name' => $calc['display_name'],
            'full_birth_name' => $calc['name_breakdown']['original_name'] ?? null,
            'y_mode' => VietnameseNameNormalizer::Y_MODE_VN,
            'birth_date' => $calc['birth']['iso'],
            'method_id' => $calc['method_id'],
            'method_version' => $calc['method_version'],
            'is_primary_self' => false,
        ]);

        $enriched = $this->knowledge->enrichCalculation($calc);
        $fallbackInterp = $this->interpreter->interpretProfile($calc, $enriched, 'tinh_yeu', '', allowAi: false);

        return NumerologyReading::create([
            'profile_id' => $profile->id,
            'user_id' => $user->id,
            'slug' => 'ho-so-'.Str::slug($calc['display_name'] ?: 'doi-tac').'-v1-'.Str::lower(Str::random(5)),
            'version_number' => 1,
            'input_hash' => hash('sha256', $calc['birth']['iso'].'|'.($calc['name_breakdown']['normalized_name'] ?? '')),
            'method_id' => $calc['method_id'],
            'method_version' => $calc['method_version'],
            'kb_version' => $calc['kb_version'],
            'viewed_year' => $calc['viewed_date']['year'],
            'focus_topic' => 'tinh_yeu',
            'calculation_payload_json' => $calc,
            'interpretation_json' => $fallbackInterp,
            'ai_powered' => false,
            'ai_status' => 'fallback',
            'privacy_mode' => 'private',
            'share_hide_birth_date' => true,
        ]);
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
     * @return array{plan_slug: string, daily_limit: int, daily_used: int, daily_remaining: int, cooldown_interval_seconds: int, cooldown_label: string, cooldown_seconds: int}
     */
    protected function getUserQuota(?User $user): array
    {
        $planSlug = $user
            ? ($user->isAdmin() ? 'pro' : $this->planAccess->getEffectivePlanSlug($user))
            : 'premium';
        $tier = self::TIER_LIMITS[$planSlug] ?? self::TIER_LIMITS['premium'];

        if (! $user) {
            return [
                'plan_slug' => $planSlug,
                'daily_limit' => $tier['daily_limit'],
                'daily_used' => 0,
                'daily_remaining' => $tier['daily_limit'],
                'cooldown_interval_seconds' => $tier['cooldown_seconds'],
                'cooldown_label' => $tier['cooldown_label'],
                'cooldown_seconds' => 0,
            ];
        }

        $todayStart = now('Asia/Ho_Chi_Minh')->startOfDay()->setTimezone(config('app.timezone', 'UTC'));
        $dailyUsed = NumerologyReading::where('user_id', $user->id)
            ->where('ai_powered', true)
            ->where('created_at', '>=', $todayStart)
            ->count();

        $cooldownSeconds = 0;
        if ($tier['cooldown_seconds'] > 0) {
            $latest = NumerologyReading::where('user_id', $user->id)
                ->where('ai_powered', true)
                ->latest('created_at')
                ->first();
            if ($latest && $latest->created_at) {
                $elapsed = (int) $latest->created_at->diffInSeconds(now(), false);
                if ($elapsed >= 0 && $elapsed < $tier['cooldown_seconds']) {
                    $cooldownSeconds = $tier['cooldown_seconds'] - $elapsed;
                }
            }
        }

        return [
            'plan_slug' => $planSlug,
            'daily_limit' => $tier['daily_limit'],
            'daily_used' => $dailyUsed,
            'daily_remaining' => max(0, $tier['daily_limit'] - $dailyUsed),
            'cooldown_interval_seconds' => $tier['cooldown_seconds'],
            'cooldown_label' => $tier['cooldown_label'],
            'cooldown_seconds' => $cooldownSeconds,
        ];
    }
}
