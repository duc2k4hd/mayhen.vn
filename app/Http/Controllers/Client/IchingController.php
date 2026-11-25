<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\IchingReading;
use App\Models\IchingReadingFollowup;
use App\Models\IchingReadingJournal;
use App\Models\User;
use App\Rules\NoProfanity;
use App\Services\Iching\IchingAiInterpreterService;
use App\Services\Iching\IchingHexagramEngine;
use App\Services\Iching\IchingSafetyGuardService;
use App\Services\PlanAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IchingController extends Controller
{
    /**
     * Hạn mức gieo quẻ & luận giải AI theo từng gói tài khoản Mây Hẹn:
     * - Premium (0đ): 5 phút/lần, tối đa 5 lượt/ngày
     * - Plus: 1 phút/lần, tối đa 10 lượt/ngày
     * - Pro: luận liên tục (0s chờ), tối đa 30 lượt/ngày
     *
     * @var array<string, array{daily_limit: int, cooldown_seconds: int, cooldown_label: string}>
     */
    public const TIER_CAST_LIMITS = [
        'premium' => [
            'daily_limit' => 5,
            'cooldown_seconds' => 300,
            'cooldown_label' => '5 phút/lần',
        ],
        'plus' => [
            'daily_limit' => 10,
            'cooldown_seconds' => 60,
            'cooldown_label' => '1 phút/lần',
        ],
        'pro' => [
            'daily_limit' => 30,
            'cooldown_seconds' => 0,
            'cooldown_label' => 'luận liên tục',
        ],
    ];

    public function __construct(
        protected IchingHexagramEngine $engine,
        protected IchingAiInterpreterService $interpreter,
        protected IchingSafetyGuardService $safetyGuard,
        protected PlanAccessService $planAccess
    ) {}

    /**
     * Trang SEO chính & Bàn Gieo Quẻ Mây Hẹn Dịch Tâm (/gieo-que).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $canCastFree = $this->canCastWithPremiumLogin($user);
        $currentPlanLabel = $this->planAccess->getTierLabel($this->planAccess->getEffectivePlanSlug($user));

        $hexagrams = $this->engine->getAllHexagrams();
        $topics = IchingHexagramEngine::TOPICS;
        $topicsTaxonomy = $this->engine->getTopicsTaxonomy();
        $trigrams = IchingHexagramEngine::TRIGRAMS;
        $manifest = $this->engine->getManifest();
        $guidanceStates = IchingHexagramEngine::GUIDANCE_STATES;

        // Lá quẻ minh họa chuẩn hóa (Địa Thiên Thái tĩnh — Quẻ #11 đã thẩm định cổ thư)
        $demoAnalysis = $this->engine->analyzeLines([7, 7, 7, 8, 8, 8], IchingHexagramEngine::METHOD_COINS_3);
        $demoInterpretation = $this->interpreter->interpret(
            readingAnalysis: $demoAnalysis,
            question: 'Tôi nên chuẩn bị điều gì để năm mới và gia đạo thêm hài hòa, vững vàng?',
            topic: 'tong_quan',
            allowAi: false,
            subTopic: 'Định hướng năm mới'
        );

        $recentReadings = $canCastFree && $user
            ? IchingReading::where('user_id', $user->id)
                ->withCount(['followups', 'journals'])
                ->latest()
                ->limit(6)
                ->get()
            : collect();

        $castQuota = $this->getUserCastQuota($canCastFree ? $user : null);

        return view('client.pages.iching.index', compact(
            'canCastFree',
            'currentPlanLabel',
            'hexagrams',
            'topics',
            'topicsTaxonomy',
            'trigrams',
            'manifest',
            'guidanceStates',
            'demoAnalysis',
            'demoInterpretation',
            'recentReadings',
            'castQuota'
        ));
    }

    /**
     * Gợi ý làm rõ câu hỏi mở (chủ động, không ép buộc) & kiểm tra an toàn trước khi gieo.
     */
    public function refineQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:360'],
        ]);

        $question = trim(strip_tags($validated['question']));
        $safety = $this->safetyGuard->assessQuestion($question);
        $refinement = $this->safetyGuard->suggestQuestionRefinement($question);

        return response()->json([
            'success' => true,
            'safety' => $safety,
            'refinement' => $refinement,
        ]);
    }

    /**
     * API Gieo Quẻ & Luận Giải 10 Thành Phần Cá Nhân Hóa (Miễn phí 100% khi đăng nhập tài khoản gói Premium trở lên).
     */
    public function cast(Request $request): JsonResponse
    {
        $this->extendIchingExecutionTimeout();

        $user = $request->user();
        if (! $this->canCastWithPremiumLogin($user)) {
            return response()->json([
                'success' => false,
                'code' => 'PREMIUM_LOGIN_REQUIRED',
                'message' => 'Tính năng Gieo Quẻ Kinh Dịch & Luận Giải Cá Nhân Hóa hoàn toàn miễn phí cho thành viên Mây Hẹn. Vui lòng đăng nhập (tự động nhận gói Thành Viên Premium 0đ) để gieo quẻ và lưu vào Sổ Quẻ riêng tư.',
                'login_url' => route('login', ['redirect' => route('iching.index')]),
                'register_url' => route('register'),
            ], 403);
        }

        $validated = $request->validate([
            'method' => ['required', 'string', 'in:coins_3,coins_manual,mai_hoa_time,mai_hoa_numbers,spring_blessing,new_year'],
            'topic' => ['required', 'string', 'in:tong_quan,tinh_duyen,cong_danh,tai_loc,gia_dao'],
            'sub_topic' => ['nullable', 'string', 'max:120'],
            'question' => ['required', 'string', 'min:5', 'max:360', new NoProfanity],
            'context_note' => ['nullable', 'string', 'max:600', new NoProfanity],
            'lines' => ['nullable', 'array', 'size:6'],
            'lines.*' => ['integer', 'in:6,7,8,9'],
            'coin_faces' => ['nullable', 'array', 'size:6'],
            'number_upper' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'number_lower' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'hide_question_in_storage' => ['nullable', 'boolean'],
            'idempotency_key' => ['nullable', 'string', 'max:80'],
        ]);

        $normalizedMethod = $this->engine->normalizeMethod($validated['method']);

        if ($normalizedMethod === IchingHexagramEngine::METHOD_COINS_MANUAL && empty($validated['lines'])) {
            return response()->json([
                'success' => false,
                'code' => 'MANUAL_LINES_REQUIRED',
                'message' => 'Chế độ tự gieo xu thật yêu cầu chọn đủ 6 hào (6, 7, 8 hoặc 9) từ Hào 1 đến Hào 6.',
            ], 422);
        }

        if ($normalizedMethod === IchingHexagramEngine::METHOD_MAI_HOA_NUMBERS && (empty($validated['number_upper']) || empty($validated['number_lower']))) {
            return response()->json([
                'success' => false,
                'code' => 'MAI_HOA_NUMBERS_REQUIRED',
                'message' => 'Phương pháp Mai Hoa 2 số yêu cầu nhập đủ hai số nguyên dương (từ 1 trở lên).',
            ], 422);
        }

        $question = trim(strip_tags($validated['question']));
        $subTopic = isset($validated['sub_topic']) ? trim(strip_tags((string) $validated['sub_topic'])) : null;
        $contextNote = isset($validated['context_note']) ? trim(strip_tags((string) $validated['context_note'])) : null;
        $hideQuestion = (bool) ($validated['hide_question_in_storage'] ?? false);
        $idempotencyKey = ! empty($validated['idempotency_key'])
            ? 'u'.$user->id.'_'.Str::limit(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $validated['idempotency_key']), 64, '')
            : null;

        // 1. Bảo vệ an toàn nhân văn (khủng hoảng tâm lý / cấp cứu -> chặn gieo & điều hướng hỗ trợ)
        $safety = $this->safetyGuard->assessQuestion($question.($contextNote ? ' '.$contextNote : ''));
        if (! $safety['allow_cast']) {
            return response()->json([
                'success' => false,
                'code' => 'SAFETY_CRISIS_BLOCK',
                'message' => $safety['notice'],
                'safety' => $safety,
            ], 422);
        }

        // 2. Chống tạo bản ghi trùng do bấm đúp hoặc lỗi mạng (Idempotency)
        if ($idempotencyKey !== null) {
            $existing = IchingReading::where('idempotency_key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                $existingAnalysis = $this->rebuildAnalysisFromReading($existing);

                return response()->json([
                    'success' => true,
                    'idempotent_replay' => true,
                    'slug' => $existing->slug,
                    'result_url' => route('iching.show', $existing->slug),
                    'analysis' => $existingAnalysis,
                    'interpretation' => $existing->interpretation_json,
                    'safety' => $safety,
                    'duplicate_hint' => null,
                    'question' => $existing->question,
                    'is_question_redacted' => $existing->is_question_redacted,
                    'topic_label' => IchingHexagramEngine::TOPICS[$existing->topic]['label'] ?? 'Tổng Quan',
                    'sub_topic' => $existing->sub_topic,
                    'checklist' => $existing->action_checklist_json ?? [],
                    'endpoints' => $this->buildReadingEndpoints($existing->slug),
                ]);
            }
        }

        // 2.5. Giới hạn tần suất theo gói (Premium: 5p & 5/ngày | Plus: 1p & 10/ngày | Pro: liên tục & 30/ngày)
        $quota = $this->getUserCastQuota($user);

        if ($quota['daily_remaining'] <= 0) {
            return response()->json([
                'success' => false,
                'code' => 'ICHING_DAILY_LIMIT_REACHED',
                'message' => 'Bạn đã sử dụng hết '.$quota['daily_limit'].' lượt gieo quẻ & luận giải trong ngày hôm nay theo gói hiện tại. Hãy dành thời gian chiêm nghiệm các lá quẻ trong Sổ Quẻ và quay lại vào ngày mai nhé.',
                'quota' => $quota,
                'history_url' => route('iching.history'),
            ], 429);
        }

        if ($quota['cooldown_seconds'] > 0) {
            $mins = intdiv($quota['cooldown_seconds'], 60);
            $secs = $quota['cooldown_seconds'] % 60;
            $waitLabel = $mins > 0
                ? ($secs > 0 ? "{$mins} phút {$secs} giây" : "{$mins} phút")
                : "{$secs} giây";
            $intervalMinutes = max(1, intdiv($quota['cooldown_interval_seconds'], 60));

            return response()->json([
                'success' => false,
                'code' => 'ICHING_COOLDOWN_ACTIVE',
                'message' => "Với gói hiện tại, mỗi lượt gieo quẻ & luận giải cách nhau {$intervalMinutes} phút để giữ tâm tĩnh tại. Vui lòng chờ thêm {$waitLabel} hoặc mở Sổ Quẻ để Hỏi Sâu về lá quẻ gần nhất.",
                'retry_after_seconds' => $quota['cooldown_seconds'],
                'quota' => $quota,
                'history_url' => route('iching.history'),
            ], 429);
        }

        // 3. Nhắc nhẹ nếu vừa gieo cùng chủ đề trong 30 phút (chống nghiện gieo lặp, KHÔNG cấm cứng)
        $duplicateHint = null;
        $recentSameTopic = IchingReading::where('user_id', $user->id)
            ->where('topic', $validated['topic'])
            ->where('created_at', '>=', now()->subMinutes(30))
            ->latest()
            ->first();
        if ($recentSameTopic) {
            $duplicateHint = 'Bạn vừa gieo một quẻ thuộc chủ đề này trong vòng 30 phút trước. Kinh Dịch khuyên nên dành thời gian chiêm nghiệm và dùng mục "Hỏi Sâu Về Quẻ" trên lá quẻ trước thay vì gieo nhiều lần liên tiếp cho cùng một chuyện.';
        }

        // 4. Lập quẻ chuẩn hóa theo Rule Engine V2
        $analysis = $this->engine->cast(
            method: $normalizedMethod,
            providedLines: $validated['lines'] ?? null,
            numberUpper: isset($validated['number_upper']) ? (int) $validated['number_upper'] : null,
            numberLower: isset($validated['number_lower']) ? (int) $validated['number_lower'] : null,
            coinFaces: is_array($validated['coin_faces'] ?? null) ? $validated['coin_faces'] : null
        );

        // 5. Luận giải 10 thành phần cá nhân hóa (AI + hậu kiểm an toàn + fallback có căn cứ)
        $interpretation = $this->interpreter->interpret(
            readingAnalysis: $analysis,
            question: $question,
            topic: $validated['topic'],
            allowAi: true,
            subTopic: $subTopic,
            contextNote: $contextNote,
            safetyAssessment: $safety
        );

        $slug = 'que-'.Str::slug($analysis['primary']['name']).'-'.Str::lower(Str::random(6));
        while (IchingReading::where('slug', $slug)->exists()) {
            $slug = 'que-'.Str::slug($analysis['primary']['name']).'-'.Str::lower(Str::random(7));
        }

        $checklist = array_map(
            static fn (string $step): array => ['text' => $step, 'done' => false],
            $interpretation['action_steps'] ?? []
        );

        $storedQuestion = $hideQuestion
            ? '[Nội dung câu hỏi đã được ẩn riêng tư theo yêu cầu của bạn]'
            : $question;

        $reading = IchingReading::create([
            'user_id' => $user->id,
            'slug' => $slug,
            'method' => $normalizedMethod,
            'rule_version' => $analysis['rule_version'],
            'kb_version' => $analysis['kb_version'],
            'topic' => $validated['topic'],
            'sub_topic' => $subTopic !== '' ? $subTopic : null,
            'question' => $storedQuestion,
            'context_note' => $hideQuestion ? null : ($contextNote !== '' ? $contextNote : null),
            'is_question_redacted' => $hideQuestion,
            'is_favorite' => false,
            'privacy_mode' => 'private',
            'idempotency_key' => $idempotencyKey,
            'lines_json' => $analysis['lines'],
            'cast_inputs_json' => $analysis['calculation_trace'],
            'primary_hexagram_no' => $analysis['primary']['no'],
            'mutual_hexagram_no' => $analysis['mutual']['no'],
            'changed_hexagram_no' => $analysis['changed']['no'] ?? null,
            'moving_lines_json' => $analysis['moving_lines'],
            'ti_yong_analysis_json' => $analysis['ti_yong'],
            'selected_texts_json' => $analysis['chu_hy']['selected_texts'] ?? [],
            'guidance_state' => $analysis['guidance_state']['label'] ?? 'Cân bằng',
            'interpretation_json' => $interpretation,
            'ai_powered' => (bool) ($interpretation['ai_powered'] ?? false),
            'ai_model' => $interpretation['ai_model'] ?? null,
            'ai_status' => (string) ($interpretation['ai_status'] ?? 'fallback'),
            'action_checklist_json' => $checklist,
        ]);

        return response()->json([
            'success' => true,
            'idempotent_replay' => false,
            'slug' => $reading->slug,
            'result_url' => route('iching.show', $reading->slug),
            'analysis' => $analysis,
            'interpretation' => $interpretation,
            'safety' => $safety,
            'duplicate_hint' => $duplicateHint,
            'question' => $storedQuestion,
            'is_question_redacted' => $hideQuestion,
            'topic_label' => IchingHexagramEngine::TOPICS[$validated['topic']]['label'] ?? 'Tổng Quan',
            'sub_topic' => $reading->sub_topic,
            'checklist' => $checklist,
            'endpoints' => $this->buildReadingEndpoints($reading->slug),
        ]);
    }

    /**
     * Xem lại chi tiết 1 lá quẻ đã gieo (/gieo-que/ket-qua/{slug}) — RIÊNG TƯ MẶC ĐỊNH (chỉ chính chủ hoặc admin).
     */
    public function show(Request $request, string $slug): View
    {
        $reading = IchingReading::with(['followups', 'journals'])
            ->where('slug', $slug)
            ->firstOrFail();

        $user = $request->user();
        if (! $this->canAccessPrivateReading($user, $reading)) {
            abort(403, 'Lá quẻ này thuộc không gian riêng tư của chủ sở hữu. Vui lòng đăng nhập đúng tài khoản đã gieo quẻ hoặc sử dụng liên kết Thẻ Chia Sẻ ẩn danh.');
        }

        $analysis = $this->rebuildAnalysisFromReading($reading);
        $interpretation = is_array($reading->interpretation_json) ? $reading->interpretation_json : [];
        $topicLabel = IchingHexagramEngine::TOPICS[$reading->topic]['label'] ?? 'Tổng Quan';
        $relatedHexagrams = $this->engine->getRelatedHexagrams($reading->primary_hexagram_no);
        $endpoints = $this->buildReadingEndpoints($reading->slug);
        $shareUrl = $reading->isSharedActive()
            ? route('iching.shared_card', $reading->share_token)
            : null;

        return view('client.pages.iching.show', compact(
            'reading',
            'analysis',
            'interpretation',
            'topicLabel',
            'relatedHexagrams',
            'endpoints',
            'shareUrl'
        ));
    }

    /**
     * Trang Thẻ Chia Sẻ Tóm Tắt (đã ẩn toàn bộ câu hỏi nhạy cảm & thông tin cá nhân, noindex).
     */
    public function sharedCard(string $shareToken): View
    {
        $reading = IchingReading::where('share_token', $shareToken)
            ->where('privacy_mode', 'shared_card')
            ->whereNull('share_revoked_at')
            ->firstOrFail();

        $analysis = $this->rebuildAnalysisFromReading($reading);
        $interpretation = is_array($reading->interpretation_json) ? $reading->interpretation_json : [];
        $topicLabel = IchingHexagramEngine::TOPICS[$reading->topic]['label'] ?? 'Tổng Quan';

        return view('client.pages.iching.shared-card', compact(
            'reading',
            'analysis',
            'interpretation',
            'topicLabel'
        ));
    }

    /**
     * Luận giải lại bằng AI trên chính lá quẻ đã lập (không thay đổi 6 hào hay quẻ).
     */
    public function reinterpret(Request $request, string $slug): JsonResponse
    {
        $this->extendIchingExecutionTimeout();

        $reading = IchingReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivateReading($request->user(), $reading)) {
            abort(403);
        }

        $analysis = $this->rebuildAnalysisFromReading($reading);
        $questionForAi = $reading->is_question_redacted
            ? 'Xin luận giải tổng quan theo chủ đề '.(IchingHexagramEngine::TOPICS[$reading->topic]['label'] ?? 'Tổng Quan')
            : $reading->question;

        $interpretation = $this->interpreter->interpret(
            readingAnalysis: $analysis,
            question: $questionForAi,
            topic: $reading->topic,
            allowAi: true,
            subTopic: $reading->sub_topic,
            contextNote: $reading->context_note
        );

        $reading->update([
            'interpretation_json' => $interpretation,
            'ai_powered' => (bool) ($interpretation['ai_powered'] ?? false),
            'ai_model' => $interpretation['ai_model'] ?? null,
            'ai_status' => (string) ($interpretation['ai_status'] ?? 'fallback'),
        ]);

        return response()->json([
            'success' => true,
            'interpretation' => $interpretation,
            'ai_powered' => $reading->ai_powered,
            'ai_status' => $reading->ai_status,
        ]);
    }

    /**
     * Hỏi Sâu Về Quẻ (Follow-up Q&A) khóa chặt vào lá quẻ gốc.
     */
    public function followup(Request $request, string $slug): JsonResponse
    {
        $this->extendIchingExecutionTimeout();

        $reading = IchingReading::with('followups')->where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivateReading($request->user(), $reading)) {
            abort(403);
        }

        if ($reading->followups->count() >= 8) {
            return response()->json([
                'success' => false,
                'message' => 'Mỗi lá quẻ hỗ trợ tối đa 8 lượt Hỏi Sâu để giữ sự tập trung và tránh suy diễn lan man.',
            ], 422);
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:4', 'max:320', new NoProfanity],
        ]);

        $followupQuestion = trim(strip_tags($validated['question']));
        $analysis = $this->rebuildAnalysisFromReading($reading);
        $history = $reading->followups
            ->map(fn (IchingReadingFollowup $f): array => ['question' => $f->question, 'answer' => $f->answer])
            ->all();

        $reply = $this->interpreter->answerFollowup($reading, $analysis, $followupQuestion, $history);

        $record = IchingReadingFollowup::create([
            'reading_id' => $reading->id,
            'user_id' => $request->user()?->id,
            'question' => $followupQuestion,
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
                'ai_powered' => $record->ai_powered,
                'created_at' => $record->created_at?->format('H:i • d/m/Y'),
            ],
            'safety_notice' => $reply['safety_notice'],
        ]);
    }

    /**
     * Lưu Nhật ký nhìn lại (7 / 30 / 90 ngày) & cập nhật Checklist hành động.
     */
    public function saveJournal(Request $request, string $slug): JsonResponse
    {
        $reading = IchingReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivateReading($request->user(), $reading)) {
            abort(403);
        }

        $validated = $request->validate([
            'milestone_days' => ['nullable', 'integer', 'in:0,7,30,90'],
            'actions_taken' => ['nullable', 'string', 'max:600'],
            'progress_note' => ['nullable', 'string', 'max:600'],
            'mindset_shift' => ['nullable', 'string', 'max:600'],
            'lessons_learned' => ['nullable', 'string', 'max:600'],
            'checklist' => ['nullable', 'array', 'max:5'],
            'checklist.*.text' => ['required_with:checklist', 'string', 'max:300'],
            'checklist.*.done' => ['required_with:checklist', 'boolean'],
        ]);

        if (isset($validated['checklist'])) {
            $reading->update(['action_checklist_json' => $validated['checklist']]);
        }

        $journal = null;
        $hasJournalContent = trim((string) ($validated['actions_taken'] ?? '')) !== ''
            || trim((string) ($validated['progress_note'] ?? '')) !== ''
            || trim((string) ($validated['mindset_shift'] ?? '')) !== ''
            || trim((string) ($validated['lessons_learned'] ?? '')) !== '';

        if ($hasJournalContent) {
            $journal = IchingReadingJournal::create([
                'reading_id' => $reading->id,
                'user_id' => $request->user()?->id,
                'milestone_days' => (int) ($validated['milestone_days'] ?? 7),
                'actions_taken' => trim(strip_tags((string) ($validated['actions_taken'] ?? ''))) ?: null,
                'progress_note' => trim(strip_tags((string) ($validated['progress_note'] ?? ''))) ?: null,
                'mindset_shift' => trim(strip_tags((string) ($validated['mindset_shift'] ?? ''))) ?: null,
                'lessons_learned' => trim(strip_tags((string) ($validated['lessons_learned'] ?? ''))) ?: null,
            ]);
        }

        return response()->json([
            'success' => true,
            'checklist' => $reading->fresh()?->action_checklist_json ?? [],
            'journal' => $journal ? [
                'id' => $journal->id,
                'milestone_days' => $journal->milestone_days,
                'actions_taken' => $journal->actions_taken,
                'progress_note' => $journal->progress_note,
                'mindset_shift' => $journal->mindset_shift,
                'lessons_learned' => $journal->lessons_learned,
                'created_at' => $journal->created_at?->format('H:i • d/m/Y'),
            ] : null,
        ]);
    }

    /**
     * Bật hoặc thu hồi Thẻ Chia Sẻ ẩn thông tin cá nhân.
     */
    public function toggleShare(Request $request, string $slug): JsonResponse
    {
        $reading = IchingReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivateReading($request->user(), $reading)) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:enable,revoke'],
        ]);

        if ($validated['action'] === 'enable') {
            $token = $reading->isSharedActive()
                ? (string) $reading->share_token
                : Str::lower(Str::random(32));

            $reading->update([
                'privacy_mode' => 'shared_card',
                'share_token' => $token,
                'share_revoked_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'shared' => true,
                'share_url' => route('iching.shared_card', $token),
                'message' => 'Đã tạo liên kết Thẻ Chia Sẻ (đã tự động ẩn câu hỏi và ghi chú riêng tư của bạn).',
            ]);
        }

        $reading->update([
            'privacy_mode' => 'private',
            'share_token' => null,
            'share_revoked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'shared' => false,
            'share_url' => null,
            'message' => 'Đã thu hồi liên kết chia sẻ. Lá quẻ hoàn toàn riêng tư trở lại.',
        ]);
    }

    /**
     * Lưu phản hồi mức độ hữu ích (4 mức) và đánh dấu Yêu thích.
     */
    public function saveFeedback(Request $request, string $slug): JsonResponse
    {
        $reading = IchingReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessPrivateReading($request->user(), $reading)) {
            abort(403);
        }

        $validated = $request->validate([
            'feedback_rating' => ['nullable', 'string', 'in:very_helpful,helpful,neutral,not_relevant'],
            'is_favorite' => ['nullable', 'boolean'],
        ]);

        $updates = [];
        if (array_key_exists('feedback_rating', $validated) && $validated['feedback_rating'] !== null) {
            $updates['feedback_rating'] = $validated['feedback_rating'];
        }
        if (array_key_exists('is_favorite', $validated) && $validated['is_favorite'] !== null) {
            $updates['is_favorite'] = (bool) $validated['is_favorite'];
        }

        if ($updates !== []) {
            $reading->update($updates);
        }

        return response()->json([
            'success' => true,
            'feedback_rating' => $reading->feedback_rating,
            'is_favorite' => $reading->is_favorite,
        ]);
    }

    /**
     * Sổ Quẻ Cá Nhân (/gieo-que/so-que) — yêu cầu đăng nhập.
     */
    public function history(Request $request): View
    {
        $user = $request->user();
        $filter = (string) $request->query('filter', 'all');
        $topicFilter = (string) $request->query('topic', '');

        $query = IchingReading::where('user_id', $user->id)
            ->withCount(['followups', 'journals'])
            ->latest();

        if ($filter === 'favorites') {
            $query->where('is_favorite', true);
        }
        if ($topicFilter !== '' && array_key_exists($topicFilter, IchingHexagramEngine::TOPICS)) {
            $query->where('topic', $topicFilter);
        }

        $readings = $query->paginate(12)->withQueryString();
        $hexagrams = $this->engine->getAllHexagrams();
        $topics = IchingHexagramEngine::TOPICS;

        return view('client.pages.iching.history', compact('readings', 'hexagrams', 'topics', 'filter', 'topicFilter'));
    }

    /**
     * Xóa 1 lá quẻ khỏi Sổ Quẻ của chính chủ (xóa luôn follow-up & nhật ký kèm theo).
     */
    public function destroy(Request $request, string $slug): RedirectResponse
    {
        $user = $request->user();
        $reading = IchingReading::where('slug', $slug)->firstOrFail();

        if (! $this->canAccessPrivateReading($user, $reading)) {
            abort(403);
        }

        $reading->delete();

        return redirect()->route('iching.history')
            ->with('success', 'Đã xóa vĩnh viễn lá quẻ cùng nhật ký đi kèm khỏi Sổ Quẻ của bạn.');
    }

    protected function extendIchingExecutionTimeout(int $seconds = 120): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit($seconds);
        }
        @ini_set('max_execution_time', (string) $seconds);
    }

    /**
     * Lấy quy định hạn mức theo gói hiện hành của người dùng.
     *
     * @return array{plan_slug: string, daily_limit: int, cooldown_seconds: int, cooldown_label: string}
     */
    protected function resolveTierCastPolicy(?User $user): array
    {
        if (! $user) {
            return array_merge(['plan_slug' => 'premium'], self::TIER_CAST_LIMITS['premium']);
        }

        $planSlug = $user->isAdmin()
            ? 'pro'
            : $this->planAccess->getEffectivePlanSlug($user);

        $rule = self::TIER_CAST_LIMITS[$planSlug] ?? self::TIER_CAST_LIMITS['premium'];

        return array_merge(['plan_slug' => $planSlug], $rule);
    }

    /**
     * Tính hạn mức gieo quẻ của người dùng theo gói:
     * - Premium: 5 phút/lần & 5 lượt/ngày
     * - Plus: 1 phút/lần & 10 lượt/ngày
     * - Pro: luận liên tục & 30 lượt/ngày
     *
     * @return array{
     *     plan_slug: string,
     *     daily_limit: int,
     *     daily_used: int,
     *     daily_remaining: int,
     *     cooldown_interval_seconds: int,
     *     cooldown_label: string,
     *     cooldown_seconds: int,
     *     next_available_at: ?string,
     *     latest_reading_url: ?string
     * }
     */
    protected function getUserCastQuota(?User $user): array
    {
        $policy = $this->resolveTierCastPolicy($user);

        if (! $user) {
            return [
                'plan_slug' => $policy['plan_slug'],
                'daily_limit' => $policy['daily_limit'],
                'daily_used' => 0,
                'daily_remaining' => $policy['daily_limit'],
                'cooldown_interval_seconds' => $policy['cooldown_seconds'],
                'cooldown_label' => $policy['cooldown_label'],
                'cooldown_seconds' => 0,
                'next_available_at' => null,
                'latest_reading_url' => null,
            ];
        }

        $startOfVnDay = now('Asia/Ho_Chi_Minh')->startOfDay()->setTimezone((string) config('app.timezone', 'UTC'));
        $dailyUsed = IchingReading::where('user_id', $user->id)
            ->where('created_at', '>=', $startOfVnDay)
            ->count();

        $latest = IchingReading::where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        $cooldownSeconds = 0;
        $nextAvailableAt = null;
        if ($policy['cooldown_seconds'] > 0 && $latest && $latest->created_at) {
            $nextTime = $latest->created_at->copy()->addSeconds($policy['cooldown_seconds']);
            if ($nextTime->isFuture()) {
                $cooldownSeconds = max(1, (int) ceil(now()->diffInSeconds($nextTime)));
                $nextAvailableAt = $nextTime->toIso8601String();
            }
        }

        return [
            'plan_slug' => $policy['plan_slug'],
            'daily_limit' => $policy['daily_limit'],
            'daily_used' => $dailyUsed,
            'daily_remaining' => max(0, $policy['daily_limit'] - $dailyUsed),
            'cooldown_interval_seconds' => $policy['cooldown_seconds'],
            'cooldown_label' => $policy['cooldown_label'],
            'cooldown_seconds' => $cooldownSeconds,
            'next_available_at' => $nextAvailableAt,
            'latest_reading_url' => $latest ? route('iching.show', $latest->slug) : null,
        ];
    }

    protected function canCastWithPremiumLogin(?User $user): bool
    {
        if (! $user || $this->planAccess->isGuestUser($user)) {
            return false;
        }

        return $this->planAccess->hasPlanAtLeast($user, 'premium');
    }

    protected function canAccessPrivateReading(?User $user, IchingReading $reading): bool
    {
        if (! $user) {
            return false;
        }

        return $reading->user_id === $user->id || $user->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rebuildAnalysisFromReading(IchingReading $reading): array
    {
        return $this->engine->analyzeLines(
            lines: is_array($reading->lines_json) ? $reading->lines_json : [7, 7, 7, 7, 7, 7],
            method: (string) $reading->method,
            moment: $reading->created_at,
            calculationTrace: is_array($reading->cast_inputs_json) ? $reading->cast_inputs_json : null
        );
    }

    /**
     * @return array<string, string>
     */
    protected function buildReadingEndpoints(string $slug): array
    {
        return [
            'show' => route('iching.show', $slug),
            'reinterpret' => route('iching.reinterpret', $slug),
            'followup' => route('iching.followup', $slug),
            'journal' => route('iching.journal', $slug),
            'toggle_share' => route('iching.toggle_share', $slug),
            'feedback' => route('iching.feedback', $slug),
        ];
    }
}
