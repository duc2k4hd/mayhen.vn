<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\IchingReading;
use App\Models\User;
use App\Rules\NoProfanity;
use App\Services\Iching\IchingAiInterpreterService;
use App\Services\Iching\IchingHexagramEngine;
use App\Services\PlanAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IchingController extends Controller
{
    public function __construct(
        protected IchingHexagramEngine $engine,
        protected IchingAiInterpreterService $interpreter,
        protected PlanAccessService $planAccess
    ) {}

    /**
     * Trang SEO chính & Bàn Gieo Quẻ Kinh Dịch / Đầu Năm (/gieo-que).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $canCastFree = $this->canCastWithPremiumLogin($user);
        $currentPlanLabel = $this->planAccess->getTierLabel($this->planAccess->getEffectivePlanSlug($user));

        $hexagrams = $this->engine->getAllHexagrams();
        $topics = IchingHexagramEngine::TOPICS;
        $trigrams = IchingHexagramEngine::TRIGRAMS;

        // Dựng 1 lá quẻ mẫu minh họa (Địa Thiên Thái động hào 2 -> Địa Hỏa Minh Di? Chọn Địa Thiên Thái tĩnh hoặc Địa Lôi Phục -> Địa Thiên Thái) để khách chưa đăng nhập xem trước bố cục 4 tầng
        $demoAnalysis = $this->engine->analyzeLines([7, 7, 7, 8, 8, 8], 'coins_3');
        $demoInterpretation = $this->interpreter->interpret(
            $demoAnalysis,
            'Vận trình năm mới và công việc, gia đạo sắp tới ra sao?',
            'tong_quan',
            allowAi: false
        );

        $recentReadings = $canCastFree && $user
            ? IchingReading::where('user_id', $user->id)->latest()->limit(6)->get()
            : collect();

        return view('client.pages.iching.index', compact(
            'canCastFree',
            'currentPlanLabel',
            'hexagrams',
            'topics',
            'trigrams',
            'demoAnalysis',
            'demoInterpretation',
            'recentReadings'
        ));
    }

    /**
     * API Gieo Quẻ & Luận Giải 4 Tầng AI Cá Nhân Hóa (Miễn phí 100% khi đăng nhập tài khoản gói Premium trở lên).
     */
    public function cast(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $this->canCastWithPremiumLogin($user)) {
            return response()->json([
                'success' => false,
                'code' => 'PREMIUM_LOGIN_REQUIRED',
                'message' => 'Tính năng Gieo Quẻ Kinh Dịch & Luận Giải AI 4 Tầng hoàn toàn miễn phí cho thành viên Mây Hẹn. Vui lòng đăng nhập (tự động nhận gói Thành Viên Premium 0đ) để gieo quẻ cá nhân hóa và lưu Sổ Quẻ.',
                'login_url' => route('login', ['redirect' => route('iching.index')]),
                'register_url' => route('register'),
            ], 403);
        }

        $validated = $request->validate([
            'method' => ['required', 'string', 'in:coins_3,mai_hoa_time,mai_hoa_numbers,new_year'],
            'topic' => ['required', 'string', 'in:tong_quan,tinh_duyen,cong_danh,tai_loc,gia_dao'],
            'question' => ['required', 'string', 'min:5', 'max:360', new NoProfanity],
            'lines' => ['nullable', 'array', 'size:6'],
            'lines.*' => ['integer', 'in:6,7,8,9'],
            'number_upper' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'number_lower' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ]);

        $question = trim(strip_tags($validated['question']));
        $analysis = $this->engine->cast(
            method: $validated['method'],
            providedLines: $validated['lines'] ?? null,
            numberUpper: isset($validated['number_upper']) ? (int) $validated['number_upper'] : null,
            numberLower: isset($validated['number_lower']) ? (int) $validated['number_lower'] : null
        );

        $interpretation = $this->interpreter->interpret(
            readingAnalysis: $analysis,
            question: $question,
            topic: $validated['topic'],
            allowAi: true
        );

        $slug = 'que-'.Str::slug($analysis['primary']['name']).'-'.Str::lower(Str::random(6));
        while (IchingReading::where('slug', $slug)->exists()) {
            $slug = 'que-'.Str::slug($analysis['primary']['name']).'-'.Str::lower(Str::random(7));
        }

        $reading = IchingReading::create([
            'user_id' => $user->id,
            'slug' => $slug,
            'method' => $validated['method'],
            'topic' => $validated['topic'],
            'question' => $question,
            'lines_json' => $analysis['lines'],
            'primary_hexagram_no' => $analysis['primary']['no'],
            'mutual_hexagram_no' => $analysis['mutual']['no'],
            'changed_hexagram_no' => $analysis['changed']['no'] ?? null,
            'moving_lines_json' => $analysis['moving_lines'],
            'ti_yong_analysis_json' => $analysis['ti_yong'],
            'interpretation_json' => $interpretation,
            'ai_powered' => $interpretation['ai_powered'],
            'ai_model' => $interpretation['ai_model'],
        ]);

        return response()->json([
            'success' => true,
            'slug' => $reading->slug,
            'result_url' => route('iching.show', $reading->slug),
            'analysis' => $analysis,
            'interpretation' => $interpretation,
            'question' => $question,
            'topic_label' => IchingHexagramEngine::TOPICS[$validated['topic']]['label'] ?? 'Tổng Quan',
        ]);
    }

    /**
     * Xem lại chi tiết 1 lá quẻ đã gieo (/gieo-que/ket-qua/{slug}).
     */
    public function show(string $slug): View
    {
        $reading = IchingReading::where('slug', $slug)->firstOrFail();
        $analysis = $this->engine->analyzeLines(
            is_array($reading->lines_json) ? $reading->lines_json : [7, 7, 7, 7, 7, 7],
            $reading->method,
            $reading->created_at
        );
        $interpretation = is_array($reading->interpretation_json) ? $reading->interpretation_json : [];
        $topicLabel = IchingHexagramEngine::TOPICS[$reading->topic]['label'] ?? 'Tổng Quan';

        return view('client.pages.iching.show', compact(
            'reading',
            'analysis',
            'interpretation',
            'topicLabel'
        ));
    }

    /**
     * Sổ Quẻ Cá Nhân (/gieo-que/so-que) — yêu cầu đăng nhập.
     */
    public function history(Request $request): View
    {
        $user = $request->user();
        $readings = IchingReading::where('user_id', $user->id)
            ->latest()
            ->paginate(12);

        $hexagrams = $this->engine->getAllHexagrams();
        $topics = IchingHexagramEngine::TOPICS;

        return view('client.pages.iching.history', compact('readings', 'hexagrams', 'topics'));
    }

    /**
     * Xóa 1 lá quẻ khỏi Sổ Quẻ của chính chủ.
     */
    public function destroy(Request $request, string $slug): RedirectResponse
    {
        $user = $request->user();
        $reading = IchingReading::where('slug', $slug)->firstOrFail();

        if ($reading->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $reading->delete();

        return redirect()->route('iching.history')
            ->with('success', 'Đã xóa lá quẻ khỏi Sổ Quẻ của bạn.');
    }

    protected function canCastWithPremiumLogin(?User $user): bool
    {
        if (! $user || $this->planAccess->isGuestUser($user)) {
            return false;
        }

        return $this->planAccess->hasPlanAtLeast($user, 'premium');
    }
}
