<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TarotReading;
use App\Models\TarotReadingFollowup;
use App\Models\TarotReadingJournal;
use App\Services\Tarot\TarotAiInterpreterService;
use App\Services\Tarot\TarotDeckRegistry;
use App\Services\Tarot\TarotInterpretationRuleEngine;
use App\Services\Tarot\TarotRandomizationEngine;
use App\Services\Tarot\TarotSafetyGuardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Controller Mây Hẹn – The Art of Tarot (/tarot).
 *
 * - Hỗ trợ trải bài miễn phí thực chất cho cả khách (1 lá, 3 lá) và thành viên đăng nhập (AI 6 tầng, Celtic Cross 10 lá, Mây Hẹn Nhân Duyên 7 lá, Sổ Bài Tarot).
 * - Fisher–Yates CSPRNG chạy phía máy chủ; người dùng chọn từng vị trí thật từ 78 lá đã xáo (rút không hoàn lại).
 * - Trang kết quả cá nhân riêng tư tuyệt đối (403 với người lạ, noindex).
 */
class TarotController extends Controller
{
    public const CATEGORIES = [
        'general' => 'Tổng quan & Định hướng',
        'love' => 'Tình yêu & Nhân duyên',
        'marriage' => 'Chuẩn bị kết hôn & Hôn nhân',
        'family' => 'Gia đình & Tổ ấm',
        'work' => 'Công việc & Sự nghiệp',
        'finance' => 'Tài chính & Quản trị',
        'growth' => 'Phát triển bản thân',
    ];

    public const RELATIONSHIP_STAGES = [
        'single' => 'Độc thân',
        'dating' => 'Đang tìm hiểu / Đang yêu',
        'long_distance' => 'Yêu xa',
        'pre_marriage' => 'Chuẩn bị kết hôn',
        'married' => 'Vợ chồng',
        'breakup' => 'Sau chia tay / Chữa lành',
    ];

    public function __construct(
        protected TarotDeckRegistry $registry,
        protected TarotRandomizationEngine $randomizer,
        protected TarotInterpretationRuleEngine $ruleEngine,
        protected TarotAiInterpreterService $interpreter,
        protected TarotSafetyGuardService $safetyGuard
    ) {}

    /**
     * Landing page chính (/tarot) & các điểm vào theo chủ đề (/tarot/tinh-yeu, /tarot/hom-nay, /tarot/celtic-cross).
     */
    public function index(Request $request, ?string $presetSpread = null, ?string $presetCategory = null): View
    {
        $user = $request->user();
        $canUseAi = $user !== null;
        $spreads = $this->registry->getAllSpreads();
        $manifest = $this->registry->getManifest();
        $cardsLight = array_map(fn (array $c): array => [
            'card_id' => $c['card_id'],
            'slug' => $c['slug'],
            'roman' => $c['roman'],
            'name_en' => $c['name_en'],
            'name_vi' => $c['name_vi'],
            'arcana' => $c['arcana'],
            'suit' => $c['suit'],
            'element' => $c['element'],
        ], $this->registry->getAllCards());

        $recentReadings = $user
            ? TarotReading::where('user_id', $user->id)
                ->where('status', 'COMPLETED')
                ->latest('id')
                ->limit(4)
                ->get()
            : collect();

        $todayKey = now('Asia/Ho_Chi_Minh')->format('Y-m-d');
        $todayDaily = $user
            ? TarotReading::where('user_id', $user->id)
                ->where('spread_id', 'daily_one')
                ->where('daily_key', $todayKey)
                ->where('status', 'COMPLETED')
                ->first()
            : null;

        return view('client.pages.tarot.index', [
            'manifest' => $manifest,
            'spreads' => $spreads,
            'categories' => self::CATEGORIES,
            'relationshipStages' => self::RELATIONSHIP_STAGES,
            'cardsLight' => $cardsLight,
            'canUseAi' => $canUseAi,
            'presetSpread' => $presetSpread ?: 'three_love',
            'presetCategory' => $presetCategory ?: 'love',
            'recentReadings' => $recentReadings,
            'todayDaily' => $todayDaily,
        ]);
    }

    /**
     * Tạo phiên trải bài mới: xáo đủ 78 lá RWS bằng Fisher–Yates CSPRNG và khóa bộ bài trên server.
     */
    public function createSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'spread_id' => ['required', 'string', 'max:60'],
            'orientation_mode' => ['nullable', 'in:upright_only,upright_reversed'],
            'selection_mode' => ['nullable', 'in:interactive,quick'],
            'category' => ['nullable', 'string', 'max:40'],
            'relationship_stage' => ['nullable', 'string', 'max:40'],
            'question' => ['nullable', 'string', 'max:360'],
            'context_note' => ['nullable', 'string', 'max:700'],
            'is_question_redacted' => ['nullable', 'boolean'],
            'cut_index' => ['nullable', 'integer', 'min:1', 'max:77'],
        ]);

        $spread = $this->registry->findSpread($validated['spread_id']);
        if (! $spread) {
            return response()->json(['success' => false, 'message' => 'Kiểu trải bài không tồn tại.'], 422);
        }

        $user = $request->user();
        if (! empty($spread['requires_auth']) && ! $user) {
            return response()->json([
                'success' => false,
                'code' => 'AUTH_REQUIRED',
                'message' => 'Trải bài chuyên sâu này miễn phí cho thành viên Mây Hẹn. Vui lòng đăng nhập để bắt đầu.',
            ], 401);
        }

        $question = trim((string) ($validated['question'] ?? ''));
        $contextNote = trim((string) ($validated['context_note'] ?? ''));
        $safety = $this->safetyGuard->assessText($question.' '.$contextNote);
        if (! $safety['allow']) {
            return response()->json([
                'success' => false,
                'code' => 'SAFETY_CRISIS_BLOCK',
                'safety' => $safety,
                'message' => $safety['notice'],
            ], 422);
        }

        $orientationMode = (string) ($validated['orientation_mode'] ?? 'upright_reversed');
        $selectionMode = (string) ($validated['selection_mode'] ?? 'interactive');
        $todayKey = now('Asia/Ho_Chi_Minh')->format('Y-m-d');

        // Tarot Hôm Nay: giữ ổn định 1 lá/ngày cho thành viên (không đổi khi tải lại trang)
        if ($spread['spread_id'] === 'daily_one' && $user) {
            $existingDaily = TarotReading::where('user_id', $user->id)
                ->where('spread_id', 'daily_one')
                ->where('daily_key', $todayKey)
                ->where('status', 'COMPLETED')
                ->first();
            if ($existingDaily) {
                return response()->json([
                    'success' => true,
                    'already_completed_today' => true,
                    'result_url' => route('tarot.show', $existingDaily->slug),
                ]);
            }
        }

        // Chống rút lặp nhẹ nhàng nếu vừa hỏi cùng câu hỏi trong 20 phút gần nhất
        $repeatHint = null;
        if ($user && mb_strlen($question, 'UTF-8') >= 8) {
            $recentSame = TarotReading::where('user_id', $user->id)
                ->where('status', 'COMPLETED')
                ->where('question', $question)
                ->where('created_at', '>=', now()->subMinutes(20))
                ->latest('id')
                ->first();
            if ($recentSame) {
                $repeatHint = 'Bạn đã có một trải bài cho câu hỏi này gần đây. Bạn vẫn có thể tiếp tục hoặc đọc lại trải bài trước để chiêm nghiệm sâu hơn.';
            }
        }

        $shuffled = $this->randomizer->createShuffledDeck($orientationMode);
        $deck = $shuffled['deck'];
        if (! empty($validated['cut_index'])) {
            $deck = $this->randomizer->cutDeck($deck, (int) $validated['cut_index']);
            $shuffled['commitment_hash'] = $this->randomizer->computeCommitmentHash($deck, $shuffled['commitment_nonce']);
        }

        $sessionToken = Str::random(40);
        $slug = 'tarot-'.Str::slug($spread['short_name']).'-'.Str::lower(Str::random(7));

        $reading = TarotReading::create([
            'user_id' => $user?->id,
            'guest_session_id' => $user ? null : $request->session()->getId(),
            'session_token' => $sessionToken,
            'slug' => $slug,
            'status' => 'SHUFFLED',
            'deck_id' => TarotDeckRegistry::DEFAULT_DECK_ID,
            'deck_version' => (string) ($this->registry->getManifest()['deck_version'] ?? '1.0.0'),
            'spread_id' => $spread['spread_id'],
            'spread_version' => '1.0.0',
            'randomization_version' => TarotRandomizationEngine::VERSION,
            'orientation_mode' => $orientationMode,
            'selection_mode' => $selectionMode,
            'category' => (string) ($validated['category'] ?? ($spread['category'] ?? 'general')),
            'relationship_stage' => $validated['relationship_stage'] ?? null,
            'question' => ! empty($validated['is_question_redacted']) ? null : ($question !== '' ? $question : null),
            'is_question_redacted' => (bool) ($validated['is_question_redacted'] ?? false),
            'context_note' => $contextNote !== '' ? $contextNote : null,
            'commitment_hash' => $shuffled['commitment_hash'],
            'commitment_nonce' => $shuffled['commitment_nonce'],
            'shuffled_deck_json' => $deck,
            'selected_cards_json' => [],
            'privacy_mode' => 'private',
            'daily_key' => $spread['spread_id'] === 'daily_one' ? $todayKey : null,
        ]);

        // Nếu chế độ Rút Nhanh: tự lấy N lá đầu tiên từ bộ bài đã xáo Fisher–Yates
        if ($selectionMode === 'quick') {
            $selected = [];
            for ($i = 0; $i < (int) $spread['card_count']; $i++) {
                $slot = $deck[$i];
                $selected[] = [
                    'position_index' => $i + 1,
                    'slot_index' => $slot['slot_index'],
                    'card_id' => $slot['card_id'],
                    'is_reversed' => $slot['is_reversed'],
                ];
            }
            $reading->update([
                'selected_cards_json' => $selected,
                'status' => 'SELECTED',
            ]);
        }

        return response()->json([
            'success' => true,
            'session_token' => $sessionToken,
            'slug' => $reading->slug,
            'spread' => $spread,
            'total_slots' => count($deck),
            'required_count' => (int) $spread['card_count'],
            'selected_count' => count($reading->selected_cards_json ?? []),
            'selection_mode' => $selectionMode,
            'orientation_mode' => $orientationMode,
            'commitment_hash' => $shuffled['commitment_hash'],
            'repeat_hint' => $repeatHint,
        ]);
    }

    /**
     * Người dùng chọn thật 1 lá bài từ 78 vị trí úp (rút không hoàn lại, chống chọn trùng).
     */
    public function selectSlot(Request $request, string $sessionToken): JsonResponse
    {
        $validated = $request->validate([
            'slot_index' => ['required', 'integer', 'min:0', 'max:77'],
        ]);

        $reading = TarotReading::where('session_token', $sessionToken)->firstOrFail();
        if ($reading->user_id !== null && ! $this->canAccessReading($request, $reading)) {
            abort(403);
        }
        if ($reading->user_id === null && $reading->guest_session_id !== $request->session()->getId()) {
            $reading->update(['guest_session_id' => $request->session()->getId()]);
        }
        if (in_array($reading->status, ['COMPLETED', 'CANCELLED'], true)) {
            return response()->json(['success' => false, 'message' => 'Phiên trải bài này đã khóa kết quả.'], 409);
        }

        $spread = $this->registry->findSpread($reading->spread_id);
        $requiredCount = (int) ($spread['card_count'] ?? 1);
        $deck = is_array($reading->shuffled_deck_json) ? $reading->shuffled_deck_json : [];
        $selected = is_array($reading->selected_cards_json) ? $reading->selected_cards_json : [];

        if (count($selected) >= $requiredCount) {
            return response()->json([
                'success' => true,
                'selected_count' => count($selected),
                'required_count' => $requiredCount,
                'ready_to_reveal' => true,
            ]);
        }

        $slotIdx = (int) $validated['slot_index'];
        foreach ($selected as $s) {
            if ((int) ($s['slot_index'] ?? -1) === $slotIdx) {
                return response()->json(['success' => false, 'message' => 'Lá bài tại vị trí này đã được rút.'], 409);
            }
        }

        $chosen = $deck[$slotIdx] ?? null;
        if (! $chosen) {
            return response()->json(['success' => false, 'message' => 'Vị trí lá bài không hợp lệ.'], 422);
        }

        $selected[] = [
            'position_index' => count($selected) + 1,
            'slot_index' => $slotIdx,
            'card_id' => $chosen['card_id'],
            'is_reversed' => (bool) $chosen['is_reversed'],
        ];

        $ready = count($selected) >= $requiredCount;
        $reading->update([
            'selected_cards_json' => $selected,
            'status' => $ready ? 'SELECTED' : 'SELECTING',
        ]);

        $cardMeta = $this->registry->findCardById((string) $chosen['card_id']) ?? [];

        return response()->json([
            'success' => true,
            'slot_index' => $slotIdx,
            'position_index' => count($selected),
            'selected_count' => count($selected),
            'required_count' => $requiredCount,
            'ready_to_reveal' => $ready,
            'card' => [
                'card_id' => $chosen['card_id'],
                'slug' => $cardMeta['slug'] ?? '',
                'roman' => $cardMeta['roman'] ?? '',
                'name_en' => $cardMeta['name_en'] ?? $chosen['card_id'],
                'name_vi' => $cardMeta['name_vi'] ?? '',
                'arcana' => $cardMeta['arcana'] ?? 'major',
                'suit' => $cardMeta['suit'] ?? 'major',
                'number' => $cardMeta['number'] ?? 1,
                'orientation' => $chosen['is_reversed'] ? 'reversed' : 'upright',
            ],
        ]);
    }

    /**
     * Khóa kết quả, mở toàn bộ lá bài và thực hiện luận giải 6 tầng (AI cho thành viên, KB chuẩn cho khách).
     */
    public function completeSession(Request $request, string $sessionToken): JsonResponse
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }

        $reading = TarotReading::where('session_token', $sessionToken)->firstOrFail();
        if ($reading->user_id !== null && ! $this->canAccessReading($request, $reading)) {
            abort(403);
        }
        if ($reading->user_id === null && $reading->guest_session_id !== $request->session()->getId()) {
            $reading->update(['guest_session_id' => $request->session()->getId()]);
        }

        $spread = $this->registry->findSpread($reading->spread_id);
        if (! $spread) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy cấu hình trải bài.'], 422);
        }

        // Nếu đã hoàn tất trước đó -> trả về đúng kết quả đã khóa (idempotent)
        if ($reading->status === 'COMPLETED' && ! empty($reading->interpretation_json)) {
            return response()->json([
                'success' => true,
                'result_url' => route('tarot.show', $reading->slug),
                'cards' => $reading->selected_cards_json,
                'interpretation' => $reading->interpretation_json,
            ]);
        }

        $selected = is_array($reading->selected_cards_json) ? $reading->selected_cards_json : [];
        if (count($selected) < (int) $spread['card_count']) {
            return response()->json(['success' => false, 'message' => 'Bạn chưa chọn đủ số lá bài của trải bài này.'], 422);
        }

        $analysis = $this->ruleEngine->analyzeReading($selected, $spread, (string) $reading->category);
        $allowAi = $request->user() !== null;
        $interpretation = $this->interpreter->interpretReading($spread, $analysis, [
            'orientation_mode' => $reading->orientation_mode,
            'category' => $reading->category,
            'relationship_stage' => $reading->relationship_stage,
            'question' => $reading->question,
            'context_note' => $reading->context_note,
        ], $allowAi);

        $reading->update([
            'selected_cards_json' => $analysis['cards'],
            'interpretation_json' => $interpretation,
            'ai_powered' => (bool) ($interpretation['ai_powered'] ?? false),
            'ai_model' => $interpretation['ai_model'] ?? null,
            'ai_status' => (string) ($interpretation['ai_status'] ?? 'fallback'),
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);

        if ($reading->user_id === null) {
            $owned = (array) $request->session()->get('tarot_guest_slugs', []);
            if (! in_array($reading->slug, $owned, true)) {
                $owned[] = $reading->slug;
                $request->session()->put('tarot_guest_slugs', array_slice($owned, -20));
            }
        }

        return response()->json([
            'success' => true,
            'slug' => $reading->slug,
            'result_url' => route('tarot.show', $reading->slug),
            'redirect_url' => route('tarot.show', $reading->slug),
            'cards' => $analysis['cards'],
            'stats' => $analysis['stats'],
            'interpretation' => $interpretation,
        ]);
    }

    /**
     * Chế độ Tự Nhập Bài Tarot Vật Lý (không xáo thêm, kiểm tra trùng lặp).
     */
    public function manualPhysicalReading(Request $request): JsonResponse
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }

        $validated = $request->validate([
            'spread_id' => ['required', 'string', 'max:60'],
            'category' => ['nullable', 'string', 'max:40'],
            'relationship_stage' => ['nullable', 'string', 'max:40'],
            'question' => ['nullable', 'string', 'max:360'],
            'context_note' => ['nullable', 'string', 'max:700'],
            'cards' => ['required', 'array', 'min:1', 'max:10'],
            'cards.*.card_id' => ['required', 'string', 'max:40'],
            'cards.*.is_reversed' => ['nullable', 'boolean'],
        ]);

        $spread = $this->registry->findSpread($validated['spread_id']);
        if (! $spread || count($validated['cards']) !== (int) $spread['card_count']) {
            return response()->json(['success' => false, 'message' => 'Số lượng lá nhập vào phải khớp chính xác với trải bài đã chọn.'], 422);
        }

        $question = trim((string) ($validated['question'] ?? ''));
        $safety = $this->safetyGuard->assessText($question.' '.($validated['context_note'] ?? ''));
        if (! $safety['allow']) {
            return response()->json(['success' => false, 'code' => 'SAFETY_CRISIS_BLOCK', 'safety' => $safety, 'message' => $safety['notice']], 422);
        }

        $seenIds = [];
        $selected = [];
        foreach ($validated['cards'] as $idx => $item) {
            $cid = (string) $item['card_id'];
            if (! $this->registry->findCardById($cid)) {
                return response()->json(['success' => false, 'message' => "Mã lá bài {$cid} không tồn tại trong bộ Rider–Waite–Smith."], 422);
            }
            if (in_array($cid, $seenIds, true)) {
                return response()->json(['success' => false, 'message' => 'Trong một bộ bài vật lý tiêu chuẩn, không thể có hai lá bài giống hệt nhau.'], 422);
            }
            $seenIds[] = $cid;
            $selected[] = [
                'position_index' => $idx + 1,
                'card_id' => $cid,
                'is_reversed' => (bool) ($item['is_reversed'] ?? false),
            ];
        }

        $user = $request->user();
        $category = (string) ($validated['category'] ?? ($spread['category'] ?? 'general'));
        $analysis = $this->ruleEngine->analyzeReading($selected, $spread, $category);
        $interpretation = $this->interpreter->interpretReading($spread, $analysis, [
            'orientation_mode' => 'manual_physical',
            'category' => $category,
            'relationship_stage' => $validated['relationship_stage'] ?? null,
            'question' => $question,
            'context_note' => trim((string) ($validated['context_note'] ?? '')),
        ], $user !== null);

        $slug = 'tarot-vat-ly-'.Str::lower(Str::random(8));
        $reading = TarotReading::create([
            'user_id' => $user?->id,
            'guest_session_id' => $user ? null : $request->session()->getId(),
            'session_token' => Str::random(40),
            'slug' => $slug,
            'status' => 'COMPLETED',
            'deck_id' => TarotDeckRegistry::DEFAULT_DECK_ID,
            'deck_version' => (string) ($this->registry->getManifest()['deck_version'] ?? '1.0.0'),
            'spread_id' => $spread['spread_id'],
            'spread_version' => '1.0.0',
            'randomization_version' => 'manual_physical_input',
            'orientation_mode' => 'upright_reversed',
            'selection_mode' => 'manual_physical',
            'category' => $category,
            'relationship_stage' => $validated['relationship_stage'] ?? null,
            'question' => $question !== '' ? $question : null,
            'context_note' => trim((string) ($validated['context_note'] ?? '')) ?: null,
            'selected_cards_json' => $analysis['cards'],
            'interpretation_json' => $interpretation,
            'ai_powered' => (bool) ($interpretation['ai_powered'] ?? false),
            'ai_model' => $interpretation['ai_model'] ?? null,
            'ai_status' => (string) ($interpretation['ai_status'] ?? 'fallback'),
            'privacy_mode' => 'private',
            'completed_at' => now(),
        ]);

        if ($reading->user_id === null) {
            $owned = (array) $request->session()->get('tarot_guest_slugs', []);
            if (! in_array($reading->slug, $owned, true)) {
                $owned[] = $reading->slug;
                $request->session()->put('tarot_guest_slugs', array_slice($owned, -20));
            }
        }

        return response()->json([
            'success' => true,
            'slug' => $reading->slug,
            'result_url' => route('tarot.show', $reading->slug),
            'redirect_url' => route('tarot.show', $reading->slug),
        ]);
    }

    /**
     * Gợi ý làm rõ câu hỏi trước khi rút bài.
     */
    public function suggestQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:4', 'max:360'],
        ]);

        $safety = $this->safetyGuard->assessText($validated['question']);
        if (! $safety['allow']) {
            return response()->json(['success' => false, 'code' => 'SAFETY_CRISIS_BLOCK', 'safety' => $safety, 'message' => $safety['notice']], 422);
        }

        return response()->json(array_merge(
            ['success' => true],
            $this->interpreter->suggestRefinedQuestion($validated['question'])
        ));
    }

    /**
     * Trang kết quả riêng tư (/tarot/ket-qua/{slug}).
     */
    public function show(Request $request, string $slug): View
    {
        $reading = TarotReading::with(['followups', 'journals'])
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $this->canAccessReading($request, $reading)) {
            abort(403, 'Trải bài Tarot này thuộc không gian riêng tư của chủ sở hữu.');
        }

        $spread = $this->registry->findSpread($reading->spread_id) ?? ['spread_id' => $reading->spread_id, 'name' => 'Trải Bài Tarot', 'layout' => 'row_3', 'positions' => []];
        $cards = is_array($reading->selected_cards_json) ? $reading->selected_cards_json : [];
        $analysis = $this->ruleEngine->analyzeReading($cards, $spread, (string) $reading->category);
        $interpretation = is_array($reading->interpretation_json) ? $reading->interpretation_json : [];
        $shareUrl = $reading->isSharedActive() ? route('tarot.shared_card', $reading->share_token) : null;

        return view('client.pages.tarot.show', [
            'reading' => $reading,
            'spread' => $spread,
            'analysis' => $analysis,
            'interpretation' => $interpretation,
            'manifest' => $this->registry->getManifest(),
            'categoryLabel' => self::CATEGORIES[$reading->category] ?? 'Tổng quan',
            'shareUrl' => $shareUrl,
            'endpoints' => [
                'followup' => route('tarot.followup', $reading->slug),
                'journal' => route('tarot.journal.store', $reading->slug),
                'toggle_share' => route('tarot.toggle_share', $reading->slug),
                'reinterpret' => route('tarot.reinterpret', $reading->slug),
                'feedback' => route('tarot.feedback', $reading->slug),
            ],
        ]);
    }

    /**
     * Bách Khoa 78 Lá Bài Tarot (/tarot/78-la-bai).
     */
    public function library(): View
    {
        return view('client.pages.tarot.library', [
            'cards' => $this->registry->getAllCards(),
            'manifest' => $this->registry->getManifest(),
        ]);
    }

    /**
     * Chi tiết 1 lá bài Tarot chuẩn SEO (/tarot/la-bai/{slug}).
     */
    public function cardDetail(string $slug): View
    {
        $card = $this->registry->findCardBySlug($slug);
        if (! $card) {
            abort(404);
        }

        return view('client.pages.tarot.card-detail', [
            'card' => $card,
            'adjacent' => $this->registry->getAdjacentCards($slug),
            'manifest' => $this->registry->getManifest(),
        ]);
    }

    /**
     * Mây Hẹn – Sổ Bài Tarot (/tarot/nhat-ky).
     */
    public function journalIndex(Request $request): View
    {
        $user = $request->user();
        $readings = TarotReading::withCount('journals')
            ->where('user_id', $user->id)
            ->where('status', 'COMPLETED')
            ->latest('id')
            ->get();

        // Thống kê thực tế (không suy diễn mê tín)
        $cardFreq = [];
        foreach ($readings as $r) {
            foreach (($r->selected_cards_json ?? []) as $c) {
                $name = (string) ($c['name_en'] ?? $c['card_id'] ?? '');
                if ($name !== '') {
                    $cardFreq[$name] = ($cardFreq[$name] ?? 0) + 1;
                }
            }
        }
        arsort($cardFreq);

        return view('client.pages.tarot.journal', [
            'readings' => $readings,
            'topCards' => array_slice($cardFreq, 0, 5, true),
            'spreads' => $this->registry->getAllSpreads(),
            'categories' => self::CATEGORIES,
        ]);
    }

    public function followup(Request $request, string $slug): JsonResponse
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:4', 'max:360'],
        ]);

        $reading = TarotReading::with('followups')->where('slug', $slug)->firstOrFail();
        if (! $this->canAccessReading($request, $reading)) {
            abort(403);
        }
        if ($reading->followups->count() >= 8) {
            return response()->json(['success' => false, 'message' => 'Bạn đã đạt giới hạn 8 lượt hỏi sâu cho trải bài này.'], 429);
        }

        $safety = $this->safetyGuard->assessText($validated['question']);
        if (! $safety['allow']) {
            return response()->json(['success' => false, 'code' => 'SAFETY_CRISIS_BLOCK', 'safety' => $safety, 'message' => $safety['notice']], 422);
        }

        $history = $reading->followups->map(fn ($f) => ['question' => $f->question, 'answer' => $f->answer])->all();
        $res = $this->interpreter->answerFollowup($reading, $validated['question'], $history);

        $fu = TarotReadingFollowup::create([
            'tarot_reading_id' => $reading->id,
            'user_id' => $request->user()?->id,
            'question' => $validated['question'],
            'answer' => $res['answer'],
            'ai_powered' => $res['ai_powered'],
            'ai_model' => $res['ai_model'],
        ]);

        return response()->json(['success' => true, 'followup' => $fu]);
    }

    public function saveJournal(Request $request, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'feeling_note' => ['nullable', 'string', 'max:500'],
            'realization_note' => ['nullable', 'string', 'max:500'],
            'action_note' => ['nullable', 'string', 'max:500'],
            'outcome_note' => ['nullable', 'string', 'max:500'],
        ]);

        $reading = TarotReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessReading($request, $reading)) {
            abort(403);
        }

        $jn = TarotReadingJournal::create(array_merge($validated, [
            'tarot_reading_id' => $reading->id,
            'user_id' => $request->user()->id,
        ]));

        return response()->json([
            'success' => true,
            'journal' => [
                'id' => $jn->id,
                'feeling_note' => $jn->feeling_note,
                'realization_note' => $jn->realization_note,
                'action_note' => $jn->action_note,
                'outcome_note' => $jn->outcome_note,
                'created_at' => $jn->created_at?->format('H:i · d/m/Y'),
            ],
        ]);
    }

    public function toggleShare(Request $request, string $slug): JsonResponse
    {
        $reading = TarotReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessReading($request, $reading)) {
            abort(403);
        }

        $action = (string) $request->input('action', 'enable');
        if ($action === 'revoke') {
            $reading->update(['privacy_mode' => 'private', 'share_token' => null]);

            return response()->json(['success' => true, 'share_url' => null]);
        }

        $token = $reading->share_token ?: Str::random(24);
        $reading->update(['privacy_mode' => 'shared_card', 'share_token' => $token, 'share_hide_question' => true]);

        return response()->json([
            'success' => true,
            'share_url' => route('tarot.shared_card', $token),
        ]);
    }

    public function sharedCard(string $shareToken): View
    {
        $reading = TarotReading::where('share_token', $shareToken)
            ->where('privacy_mode', 'shared_card')
            ->firstOrFail();

        $spread = $this->registry->findSpread($reading->spread_id) ?? ['name' => 'Trải Bài Tarot', 'layout' => 'row_3'];

        return view('client.pages.tarot.shared-card', [
            'reading' => $reading,
            'spread' => $spread,
            'cards' => is_array($reading->selected_cards_json) ? $reading->selected_cards_json : [],
            'interpretation' => is_array($reading->interpretation_json) ? $reading->interpretation_json : [],
        ]);
    }

    public function reinterpret(Request $request, string $slug): JsonResponse
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }

        $reading = TarotReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessReading($request, $reading)) {
            abort(403);
        }

        $spread = $this->registry->findSpread($reading->spread_id);
        if (! $spread) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy kiểu trải bài.'], 422);
        }

        $analysis = $this->ruleEngine->analyzeReading($reading->selected_cards_json ?? [], $spread, (string) $reading->category);
        $interpretation = $this->interpreter->interpretReading($spread, $analysis, [
            'orientation_mode' => $reading->orientation_mode,
            'category' => $reading->category,
            'relationship_stage' => $reading->relationship_stage,
            'question' => $reading->question,
            'context_note' => $reading->context_note,
        ], allowAi: true);

        $reading->update([
            'interpretation_json' => $interpretation,
            'ai_powered' => (bool) ($interpretation['ai_powered'] ?? false),
            'ai_model' => $interpretation['ai_model'] ?? null,
            'ai_status' => (string) ($interpretation['ai_status'] ?? 'fallback'),
        ]);

        return response()->json(['success' => true, 'ai_powered' => $reading->ai_powered]);
    }

    public function saveFeedback(Request $request, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'feedback_rating' => ['nullable', 'in:very_helpful,helpful,unclear,mismatch'],
            'is_favorite' => ['nullable', 'boolean'],
        ]);

        $reading = TarotReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessReading($request, $reading)) {
            abort(403);
        }

        $reading->update(array_filter($validated, fn ($v) => $v !== null));

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $reading = TarotReading::where('slug', $slug)->firstOrFail();
        if (! $this->canAccessReading($request, $reading)) {
            abort(403);
        }
        $reading->followups()->delete();
        $reading->journals()->delete();
        $reading->delete();

        return response()->json(['success' => true]);
    }

    protected function canAccessReading(Request $request, TarotReading $reading): bool
    {
        $user = Auth::user();
        if ($reading->user_id !== null) {
            return $user !== null && ((int) $user->id === (int) $reading->user_id || $user->isAdmin());
        }

        $ownedSlugs = (array) $request->session()->get('tarot_guest_slugs', []);
        if (in_array($reading->slug, $ownedSlugs, true)) {
            return true;
        }

        return $reading->guest_session_id !== null
            && hash_equals((string) $reading->guest_session_id, (string) $request->session()->getId());
    }
}
