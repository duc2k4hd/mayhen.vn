<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PersonalityCoupleReport;
use App\Models\PersonalityFollowup;
use App\Models\PersonalitySession;
use App\Services\Personality\PersonalityAiInterpreterService;
use App\Services\Personality\PersonalityArtworkService;
use App\Services\Personality\PersonalityCoupleEngine;
use App\Services\Personality\PersonalityMethodRegistry;
use App\Services\Personality\PersonalityQuestionBankService;
use App\Services\Personality\PersonalityScoringEngine;
use App\Services\Personality\PersonalitySeoContentService;
use App\Services\Personality\PersonalityTypeRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PersonalityController extends Controller
{
    public const GUEST_COOKIE_NAME = 'mayhen_personality_guest';

    public function __construct(
        protected PersonalityMethodRegistry $methods,
        protected PersonalityTypeRegistry $typeRegistry,
        protected PersonalityQuestionBankService $questionBank,
        protected PersonalityScoringEngine $scoringEngine,
        protected PersonalityCoupleEngine $coupleEngine,
        protected PersonalityAiInterpreterService $aiService,
        protected PersonalityArtworkService $artworkService,
        protected PersonalitySeoContentService $seoService
    ) {}

    public function index(): View
    {
        $allTypes = $this->typeRegistry->allTypes();
        $artworks = [];
        foreach ($allTypes as $code => $meta) {
            $artworks[$code] = $this->artworkService->renderSvg($code, 160);
        }

        return view('client.pages.personality.index', [
            'methods' => $this->methods->all(),
            'dimensions' => $this->typeRegistry->dimensions16(),
            'types' => $allTypes,
            'artworks' => $artworks,
            'sources' => $this->methods->sources(),
            'seoPillar' => $this->seoService->landingPillar(),
        ]);
    }

    public function bigFive(): View
    {
        return view('client.pages.personality.big-five', [
            'method' => $this->methods->get(PersonalityMethodRegistry::BIG_FIVE_IPIP50),
            'dimensions' => $this->typeRegistry->dimensionsBigFive(),
            'sources' => $this->methods->sources(),
            'seoPillar' => $this->seoService->subpagePillar('big_five'),
        ]);
    }

    public function start(Request $request): View
    {
        $selectedMethod = (string) $request->query('method', PersonalityMethodRegistry::STANDARD_16);
        if (! array_key_exists($selectedMethod, $this->methods->all())) {
            $selectedMethod = PersonalityMethodRegistry::STANDARD_16;
        }

        return view('client.pages.personality.start', [
            'methods' => $this->methods->all(),
            'selectedMethod' => $selectedMethod,
        ]);
    }

    public function createSession(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'method_id' => ['required', 'string', 'in:mayhen_16types_quick_v1,mayhen_16types_standard_v1,mayhen_16types_deep_v1,mayhen_bigfive_ipip50_v1'],
            'display_name' => ['nullable', 'string', 'max:100'],
        ]);

        $method = $this->methods->get($validated['method_id']);
        $guestToken = $this->resolveOrCreateGuestToken($request);
        $request->session()->put('personality_guest_token', $guestToken);
        $sessionToken = Str::random(40);
        $slug = 'ps-'.strtolower(Str::random(12));
        $questionOrder = $this->questionBank->buildDeterministicOrder($method['method_id'], $sessionToken);

        $session = PersonalitySession::query()->create([
            'user_id' => auth()->id(),
            'guest_token' => $guestToken,
            'session_token' => $sessionToken,
            'slug' => $slug,
            'model_code' => $method['model_code'],
            'assessment_version' => $method['method_id'],
            'method_version' => $method['version'],
            'scoring_version' => $method['scoring_version'],
            'status' => PersonalitySession::STATUS_IN_PROGRESS,
            'display_name' => isset($validated['display_name']) ? trim(strip_tags((string) $validated['display_name'])) : null,
            'question_count' => count($questionOrder),
            'answered_count' => 0,
            'question_order_json' => $questionOrder,
            'responses_json' => [],
            'started_at' => now(),
            'expires_at' => now()->addMonths(6),
        ]);

        $takeUrl = route('personality.take', ['sessionToken' => $session->session_token]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'redirect_url' => $takeUrl, 'session_token' => $session->session_token])
                ->cookie(self::GUEST_COOKIE_NAME, $guestToken, 60 * 24 * 180);
        }

        return redirect($takeUrl)->cookie(self::GUEST_COOKIE_NAME, $guestToken, 60 * 24 * 180);
    }

    public function take(Request $request, string $sessionToken): View|RedirectResponse
    {
        $session = PersonalitySession::query()->where('session_token', $sessionToken)->firstOrFail();
        $this->authorizeSessionAccess($request, $session);

        if (in_array($session->status, [PersonalitySession::STATUS_SCORED, PersonalitySession::STATUS_REPORT_READY], true)) {
            return redirect()->route('personality.result', ['slug' => $session->slug]);
        }

        $method = $this->methods->get($session->assessment_version);
        $questionMap = $this->questionBank->mapById($session->assessment_version);
        $orderedIds = (array) ($session->question_order_json ?? array_keys($questionMap));

        $orderedQuestions = [];
        foreach ($orderedIds as $qid) {
            if (isset($questionMap[$qid])) {
                $q = $questionMap[$qid];
                // KHÔNG lộ tên chiều (dimension) ra client khi làm bài!
                $orderedQuestions[] = [
                    'id' => $q['id'],
                    'text' => $q['text'],
                ];
            }
        }

        return view('client.pages.personality.take', [
            'session' => $session,
            'method' => $method,
            'questions' => $orderedQuestions,
            'savedResponses' => (array) ($session->responses_json ?? []),
        ]);
    }

    public function saveProgress(Request $request, string $sessionToken): JsonResponse
    {
        $session = PersonalitySession::query()->where('session_token', $sessionToken)->firstOrFail();
        $this->authorizeSessionAccess($request, $session);

        if (in_array($session->status, [PersonalitySession::STATUS_SCORED, PersonalitySession::STATUS_REPORT_READY], true)) {
            return response()->json(['ok' => true, 'already_submitted' => true, 'redirect_url' => route('personality.result', ['slug' => $session->slug])]);
        }

        $incoming = $request->input('responses', []);
        if (! is_array($incoming)) {
            $incoming = [];
        }

        $questionMap = $this->questionBank->mapById($session->assessment_version);
        $method = $this->methods->get($session->assessment_version);
        $min = (int) $method['scale_min'];
        $max = (int) $method['scale_max'];
        $allowUnsure = (bool) $method['allow_unsure'];

        $merged = (array) ($session->responses_json ?? []);
        foreach ($incoming as $qid => $val) {
            if (! isset($questionMap[$qid])) {
                continue;
            }
            if ($val === 'unsure' && $allowUnsure) {
                $merged[$qid] = 'unsure';
            } elseif (is_numeric($val) && (int) $val >= $min && (int) $val <= $max) {
                $merged[$qid] = (int) $val;
            }
        }

        $duration = max((int) $session->duration_seconds, (int) $request->input('duration_seconds', 0));
        $session->update([
            'user_id' => $session->user_id ?? auth()->id(),
            'responses_json' => $merged,
            'answered_count' => count($merged),
            'duration_seconds' => min(86400, $duration),
            'status' => PersonalitySession::STATUS_IN_PROGRESS,
        ]);

        return response()->json([
            'ok' => true,
            'answered_count' => $session->answered_count,
            'question_count' => $session->question_count,
        ]);
    }

    public function submit(Request $request, string $sessionToken): RedirectResponse|JsonResponse
    {
        $session = PersonalitySession::query()->where('session_token', $sessionToken)->firstOrFail();
        $this->authorizeSessionAccess($request, $session);

        // Idempotent submit: đã chấm xong thì trả thẳng trang kết quả, không tạo báo cáo lệch
        if (in_array($session->status, [PersonalitySession::STATUS_SCORED, PersonalitySession::STATUS_REPORT_READY], true)) {
            $url = route('personality.result', ['slug' => $session->slug]);

            return $request->expectsJson()
                ? response()->json(['ok' => true, 'redirect_url' => $url])
                : redirect($url);
        }

        $incoming = $request->input('responses', []);
        $responses = array_merge((array) ($session->responses_json ?? []), is_array($incoming) ? $incoming : []);
        $duration = max(
            (int) $session->duration_seconds,
            (int) $request->input('duration_seconds', 0),
            $session->started_at ? (int) $session->started_at->diffInSeconds(now()) : 0
        );

        $scored = $this->scoringEngine->score($session->assessment_version, $responses, min(86400, $duration));

        $session->update([
            'user_id' => $session->user_id ?? auth()->id(),
            'responses_json' => $responses,
            'answered_count' => (int) $scored['answered_count'],
            'duration_seconds' => min(86400, $duration),
            'scores_json' => $scored,
            'suggested_type' => (string) $scored['suggested_type'],
            'possible_types_json' => $scored['possible_types'],
            'boundary_flags_json' => $scored['boundary_flags'],
            'quality_flags_json' => $scored['quality_flags'],
            'calculation_hash' => (string) $scored['calculation_hash'],
            'status' => PersonalitySession::STATUS_SCORED,
            'submitted_at' => now(),
        ]);

        // Tạo sẵn báo cáo 12 tầng có căn cứ (fallback an toàn nếu AI chưa kích hoạt — không bao giờ làm mất kết quả Scoring!)
        if ($session->model_code === PersonalitySession::MODEL_SIXTEEN_TYPES) {
            $aiRes = $this->aiService->generate12LayerReport($session->fresh());
            $session->update([
                'ai_report_json' => $aiRes['report'],
                'ai_status' => $aiRes['source'] === 'llm_grounded' ? 'ready' : 'fallback',
                'status' => PersonalitySession::STATUS_REPORT_READY,
            ]);
        }

        $resultUrl = route('personality.result', ['slug' => $session->slug]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'redirect_url' => $resultUrl, 'slug' => $session->slug]);
        }

        return redirect($resultUrl);
    }

    public function result(Request $request, string $slug): View
    {
        $session = PersonalitySession::query()->with('followups')->where('slug', $slug)->firstOrFail();
        $this->authorizeSessionAccess($request, $session);

        $method = $this->methods->get($session->assessment_version);
        $effectiveCode = $session->effectiveType();
        $typeInfo = $effectiveCode ? $this->typeRegistry->getType($effectiveCode) : null;
        $artworkSvg = $this->artworkService->renderSvg($effectiveCode ?? 'UNDECIDED', 240);

        $alternativeInfos = [];
        foreach ((array) ($session->scores_json['alternative_types'] ?? []) as $altCode) {
            $info = $this->typeRegistry->getType((string) $altCode);
            if ($info) {
                $alternativeInfos[$altCode] = $info;
            }
        }

        return view('client.pages.personality.result', [
            'session' => $session,
            'method' => $method,
            'typeInfo' => $typeInfo,
            'artworkSvg' => $artworkSvg,
            'alternativeInfos' => $alternativeInfos,
            'cognitiveFunctions' => $this->typeRegistry->cognitiveFunctions(),
            'sources' => $this->methods->sources(),
        ]);
    }

    public function saveBestFit(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $session = PersonalitySession::query()->where('slug', $slug)->firstOrFail();
        $this->authorizeSessionAccess($request, $session);

        $validCodes = array_keys($this->typeRegistry->allTypes());
        $validated = $request->validate([
            'best_fit_type' => ['required', 'string', 'in:'.implode(',', $validCodes)],
            'user_notes' => ['nullable', 'string', 'max:1500'],
        ]);

        // Tuyệt đối KHÔNG sửa điểm gốc hay suggested_type!
        $session->update([
            'best_fit_type' => strtoupper($validated['best_fit_type']),
            'user_notes' => $validated['user_notes'] ?? $session->user_notes,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'best_fit_type' => $session->best_fit_type]);
        }

        return back()->with('success', 'Đã lưu nhóm tự đối chiếu (Best-Fit) '.$session->best_fit_type.' mà vẫn giữ nguyên điểm trắc nghiệm gốc.');
    }

    public function askCoach(Request $request, string $slug): JsonResponse
    {
        $session = PersonalitySession::query()->where('slug', $slug)->firstOrFail();
        $this->authorizeSessionAccess($request, $session);

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:4', 'max:400'],
        ]);

        $res = $this->aiService->answerCoachQuestion($session, $validated['question']);
        $followup = PersonalityFollowup::query()->create([
            'session_id' => $session->id,
            'user_id' => auth()->id(),
            'topic_code' => 'coach',
            'question' => trim(strip_tags($validated['question'])),
            'answer' => $res['answer'],
            'evidence_refs_json' => $res['evidence_refs'],
        ]);

        return response()->json([
            'ok' => true,
            'answer' => $followup->answer,
            'evidence_refs' => $followup->evidence_refs_json,
            'created_at' => $followup->created_at?->format('H:i d/m/Y'),
        ]);
    }

    public function generateGrowthPlan(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $session = PersonalitySession::query()->where('slug', $slug)->firstOrFail();
        $this->authorizeSessionAccess($request, $session);

        $validated = $request->validate([
            'goal_key' => ['required', 'string', 'in:communication,time_management,listening,relationship,work_collab,self_strengths'],
            'days' => ['required', 'integer', 'in:7,14,30'],
        ]);

        $plan = $this->aiService->buildGrowthPlan($session, $validated['goal_key'], (int) $validated['days']);
        $session->update(['growth_plan_json' => $plan]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'plan' => $plan]);
        }

        return back()->with('success', 'Đã tạo Kế hoạch phát triển bản thân '.$validated['days'].' ngày.');
    }

    public function toggleShare(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $session = PersonalitySession::query()->where('slug', $slug)->firstOrFail();
        $this->authorizeSessionAccess($request, $session);

        $enable = $request->boolean('enable', ! $session->is_shared);
        $token = $enable ? ($session->share_token ?: Str::random(28)) : null;

        $session->update([
            'is_shared' => $enable,
            'share_token' => $token,
        ]);

        $shareUrl = $token ? route('personality.shared_card', ['shareToken' => $token]) : null;

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'is_shared' => $enable, 'share_url' => $shareUrl]);
        }

        return back()->with('success', $enable ? 'Đã bật liên kết thẻ chia sẻ công khai.' : 'Đã thu hồi liên kết chia sẻ.');
    }

    public function sharedCard(string $shareToken): View
    {
        $session = PersonalitySession::query()
            ->where('share_token', $shareToken)
            ->where('is_shared', true)
            ->firstOrFail();

        $effectiveCode = $session->effectiveType();
        $typeInfo = $effectiveCode ? $this->typeRegistry->getType($effectiveCode) : null;

        return view('client.pages.personality.shared', [
            'session' => $session,
            'typeInfo' => $typeInfo,
            'artworkSvg' => $this->artworkService->renderSvg($effectiveCode ?? 'UNDECIDED', 240),
        ]);
    }

    public function destroy(Request $request, string $slug): RedirectResponse
    {
        $session = PersonalitySession::query()->where('slug', $slug)->firstOrFail();
        $this->authorizeSessionAccess($request, $session);

        PersonalityFollowup::query()->where('session_id', $session->id)->delete();
        $session->delete();

        return redirect()->route('personality.index')->with('success', 'Đã xóa hồ sơ trắc nghiệm tính cách khỏi hệ thống.');
    }

    public function gallery(): View
    {
        $types = $this->typeRegistry->allTypes();
        $artworks = [];
        foreach ($types as $code => $meta) {
            $artworks[$code] = $this->artworkService->renderSvg($code, 200);
        }

        return view('client.pages.personality.gallery', [
            'types' => $types,
            'artworks' => $artworks,
            'seoPillar' => $this->seoService->subpagePillar('gallery'),
        ]);
    }

    public function typeDetail(string $typeCode): View
    {
        $typeInfo = $this->typeRegistry->getType($typeCode);
        if (! $typeInfo) {
            abort(404);
        }

        $adjacentInfos = [];
        foreach ((array) $typeInfo['adjacent_types'] as $adj) {
            $info = $this->typeRegistry->getType((string) $adj);
            if ($info) {
                $adjacentInfos[$adj] = $info;
            }
        }

        return view('client.pages.personality.type-detail', [
            'typeInfo' => $typeInfo,
            'artworkSvg' => $this->artworkService->renderSvg($typeInfo['code'], 260),
            'adjacentInfos' => $adjacentInfos,
            'cognitiveFunctions' => $this->typeRegistry->cognitiveFunctions(),
            'dimensions' => $this->typeRegistry->dimensions16(),
            'seoPillar' => $this->seoService->typeDetailPillar($typeInfo),
        ]);
    }

    public function cognitiveFunctions(): View
    {
        return view('client.pages.personality.cognitive-functions', [
            'functions' => $this->typeRegistry->cognitiveFunctions(),
            'types' => $this->typeRegistry->allTypes(),
            'sources' => $this->methods->sources(),
            'seoPillar' => $this->seoService->subpagePillar('cognitive'),
        ]);
    }

    public function introversionExtraversion(): View
    {
        $allTypes = $this->typeRegistry->allTypes();

        return view('client.pages.personality.ei-guide', [
            'dimension' => $this->typeRegistry->dimensions16()['EI'],
            'eTypes' => array_filter($allTypes, fn (array $t): bool => str_starts_with($t['code'], 'E')),
            'iTypes' => array_filter($allTypes, fn (array $t): bool => str_starts_with($t['code'], 'I')),
            'seoPillar' => $this->seoService->subpagePillar('ei'),
        ]);
    }

    public function methodology(): View
    {
        return view('client.pages.personality.methodology', [
            'methods' => $this->methods->all(),
            'sources' => $this->methods->sources(),
            'dimensions' => $this->typeRegistry->dimensions16(),
            'seoPillar' => $this->seoService->subpagePillar('methodology'),
        ]);
    }

    public function history(Request $request): View
    {
        $sessions = PersonalitySession::query()
            ->where('user_id', auth()->id())
            ->whereIn('status', [PersonalitySession::STATUS_SCORED, PersonalitySession::STATUS_REPORT_READY])
            ->latest('submitted_at')
            ->limit(30)
            ->get();

        return view('client.pages.personality.history', [
            'sessions' => $sessions,
            'types' => $this->typeRegistry->allTypes(),
        ]);
    }

    public function coupleIndex(Request $request): View
    {
        $guestToken = (string) $request->cookie(self::GUEST_COOKIE_NAME, '');
        $mySessions = PersonalitySession::query()
            ->where('model_code', PersonalitySession::MODEL_SIXTEEN_TYPES)
            ->whereIn('status', [PersonalitySession::STATUS_SCORED, PersonalitySession::STATUS_REPORT_READY])
            ->where(function ($q) use ($guestToken): void {
                if (auth()->check()) {
                    $q->where('user_id', auth()->id());
                }
                if ($guestToken !== '') {
                    $q->orWhere('guest_token', $guestToken);
                }
            })
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        return view('client.pages.personality.couple-index', [
            'mySessions' => $mySessions,
            'reflectionQuestions' => $this->typeRegistry->coupleReflectionQuestions(),
            'types' => $this->typeRegistry->allTypes(),
            'seoPillar' => $this->seoService->subpagePillar('couple'),
        ]);
    }

    public function createCoupleInvite(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'session_a_id' => ['required', 'integer', 'exists:personality_sessions,id'],
            'partner_a_name' => ['nullable', 'string', 'max:80'],
            'answers' => ['nullable', 'array'],
        ]);

        $sessionA = PersonalitySession::query()->findOrFail($validated['session_a_id']);
        $this->authorizeSessionAccess($request, $sessionA);

        $report = PersonalityCoupleReport::query()->create([
            'slug' => 'cp-'.strtolower(Str::random(12)),
            'invite_code' => strtoupper(Str::random(8)),
            'user_a_id' => auth()->id(),
            'session_a_id' => $sessionA->id,
            'partner_a_name' => trim(strip_tags((string) ($validated['partner_a_name'] ?? 'Bạn A'))) ?: 'Bạn A',
            'type_a' => $sessionA->effectiveType(),
            'status' => PersonalityCoupleReport::STATUS_WAITING_PARTNER,
            'share_consent_a' => true,
            'couple_answers_a_json' => is_array($validated['answers'] ?? null) ? $validated['answers'] : [],
        ]);

        return redirect()->route('personality.couple.show', ['slug' => $report->slug])
            ->with('success', 'Đã tạo mã mời kết nối cặp đôi: '.$report->invite_code);
    }

    public function joinCouple(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invite_code' => ['required', 'string', 'max:32'],
            'session_b_id' => ['required', 'integer', 'exists:personality_sessions,id'],
            'partner_b_name' => ['nullable', 'string', 'max:80'],
            'answers' => ['nullable', 'array'],
        ]);

        $report = PersonalityCoupleReport::query()
            ->where('invite_code', strtoupper(trim($validated['invite_code'])))
            ->firstOrFail();

        $sessionB = PersonalitySession::query()->findOrFail($validated['session_b_id']);
        $this->authorizeSessionAccess($request, $sessionB);
        $sessionA = PersonalitySession::query()->findOrFail($report->session_a_id);

        $answersB = is_array($validated['answers'] ?? null) ? $validated['answers'] : [];
        $comparison = $this->coupleEngine->compareSessions(
            $sessionA,
            $sessionB,
            (array) ($report->couple_answers_a_json ?? []),
            $answersB
        );

        $report->update([
            'user_b_id' => auth()->id(),
            'session_b_id' => $sessionB->id,
            'partner_b_name' => trim(strip_tags((string) ($validated['partner_b_name'] ?? 'Bạn B'))) ?: 'Bạn B',
            'type_b' => $sessionB->effectiveType(),
            'share_consent_b' => true,
            'couple_answers_b_json' => $answersB,
            'comparison_json' => $comparison,
            'status' => PersonalityCoupleReport::STATUS_COMPLETED,
        ]);

        return redirect()->route('personality.couple.show', ['slug' => $report->slug])
            ->with('success', 'Đã kết nối thành công hai hồ sơ độc lập!');
    }

    public function showCouple(string $slug): View
    {
        $report = PersonalityCoupleReport::query()->where('slug', $slug)->firstOrFail();

        return view('client.pages.personality.couple-show', [
            'report' => $report,
            'artworkA' => $this->artworkService->renderSvg((string) ($report->type_a ?? 'UNDECIDED'), 180),
            'artworkB' => $report->type_b ? $this->artworkService->renderSvg((string) $report->type_b, 180) : null,
            'typeInfoA' => $report->type_a ? $this->typeRegistry->getType((string) $report->type_a) : null,
            'typeInfoB' => $report->type_b ? $this->typeRegistry->getType((string) $report->type_b) : null,
        ]);
    }

    protected function resolveOrCreateGuestToken(Request $request): string
    {
        $existing = trim((string) $request->cookie(self::GUEST_COOKIE_NAME, ''));
        if ($existing !== '' && strlen($existing) >= 16) {
            return $existing;
        }

        return Str::random(36);
    }

    protected function authorizeSessionAccess(Request $request, PersonalitySession $session): void
    {
        if (auth()->check() && auth()->user()?->isAdmin()) {
            return;
        }
        if ($session->user_id !== null) {
            if (auth()->check() && (int) $session->user_id === (int) auth()->id()) {
                return;
            }
            abort(403, 'Hồ sơ trắc nghiệm tính cách này ở chế độ riêng tư.');
        }
        $cookieToken = trim((string) $request->cookie(self::GUEST_COOKIE_NAME, ''));
        if ($cookieToken !== '' && $session->guest_token !== null && hash_equals((string) $session->guest_token, $cookieToken)) {
            return;
        }
        if ($session->guest_token !== null && $request->session()->get('personality_guest_token') === $session->guest_token) {
            return;
        }

        abort(403, 'Hồ sơ trắc nghiệm tính cách này ở chế độ riêng tư.');
    }
}
