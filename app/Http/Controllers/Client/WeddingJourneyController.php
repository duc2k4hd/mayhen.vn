<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use App\Models\WeddingProject;
use App\Models\WeddingScript;
use App\Services\PlanAccessService;
use App\Services\Wedding\WeddingAiConciergeService;
use App\Services\Wedding\WeddingBudgetEngine;
use App\Services\Wedding\WeddingChecklistService;
use App\Services\Wedding\WeddingDateEngine;
use App\Services\Wedding\WeddingSeatingAndTimelineEngine;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Controller cho hệ sinh thái Mây Hẹn – The Wedding Journey:
 * - A. Public Wedding Date Tools (`/xem-ngay-cuoi` + sub-routes chuyên biệt)
 * - B. Public Wedding Planner Hub & Ceremony/Legal Library (`/to-chuc-dam-cuoi`, `/to-chuc-dam-cuoi/nghi-le`)
 * - C. Private Personal Wedding Workspace (`/to-chuc-dam-cuoi/ho-so`, `/to-chuc-dam-cuoi/du-an/{slug}` — auth, 403 isolation, noindex)
 */
class WeddingJourneyController extends Controller
{
    /** @var array<string, array{max_projects: int, daily_ai: int, cooldown_sec: int}> */
    public const PLAN_QUOTAS = [
        'free' => ['max_projects' => 1, 'daily_ai' => 2, 'cooldown_sec' => 30],
        'premium' => ['max_projects' => 2, 'daily_ai' => 5, 'cooldown_sec' => 15],
        'plus' => ['max_projects' => 5, 'daily_ai' => 15, 'cooldown_sec' => 8],
        'pro' => ['max_projects' => 20, 'daily_ai' => 50, 'cooldown_sec' => 3],
    ];

    public function __construct(
        protected WeddingDateEngine $dateEngine,
        protected WeddingChecklistService $checklistService,
        protected WeddingBudgetEngine $budgetEngine,
        protected WeddingSeatingAndTimelineEngine $seatingTimelineEngine,
        protected WeddingAiConciergeService $aiConcierge,
        protected PlanAccessService $planAccess
    ) {}

    /* =========================================================================
     * A. PUBLIC WEDDING DATE TOOLS (/xem-ngay-cuoi)
     * ========================================================================= */

    public function dateFinder(Request $request, ?string $focus = null): View
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $year = max(2024, min(2035, (int) $request->query('year', $now->year)));
        $month = max(1, min(12, (int) $request->query('month', $now->month)));
        $activity = match ($focus) {
            'ngay-an-hoi' => 'an_hoi',
            'ngay-dam-ngo' => 'dam_ngo',
            'gio-don-dau' => 'don_dau',
            default => (string) $request->query('activity', 'le_thanh_hon'),
        };

        $initialResult = $this->dateEngine->findCandidateDates([
            'year' => $year,
            'month' => $month,
            'activity' => $activity,
            'weekends_only' => $request->boolean('weekends_only', false),
            'groom_year' => $request->query('groom_year') ? (int) $request->query('groom_year') : null,
            'bride_year' => $request->query('bride_year') ? (int) $request->query('bride_year') : null,
            'check_kim_lau' => true,
            'check_dai_loi' => $focus === 'thang-dai-loi',
        ]);

        return view('client.pages.wedding.date-finder', [
            'focus' => $focus,
            'initialResult' => $initialResult,
            'activityProfiles' => WeddingDateEngine::ACTIVITY_PROFILES,
            'planSummary' => $this->getUserQuotaSummary($request->user()),
        ]);
    }

    public function apiFindDates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2024', 'max:2035'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'activity' => ['nullable', 'string', 'max:40'],
            'weekends_only' => ['nullable', 'boolean'],
            'weekdays_only' => ['nullable', 'boolean'],
            'min_prep_days' => ['nullable', 'integer', 'min:0', 'max:730'],
            'excluded_dates' => ['nullable', 'array', 'max:60'],
            'excluded_dates.*' => ['string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'groom_year' => ['nullable', 'integer', 'min:1950', 'max:2015'],
            'bride_year' => ['nullable', 'integer', 'min:1950', 'max:2015'],
            'check_kim_lau' => ['nullable', 'boolean'],
            'check_dai_loi' => ['nullable', 'boolean'],
            'avoid_clash_couple' => ['nullable', 'boolean'],
            'prefer_daylight_hours' => ['nullable', 'boolean'],
        ]);

        $result = $this->dateEngine->findCandidateDates($validated);

        return response()->json(['ok' => true, 'data' => $result]);
    }

    public function apiCompareDates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dates' => ['required', 'array', 'min:1', 'max:5'],
            'dates.*' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'activity' => ['nullable', 'string', 'max:40'],
            'groom_year' => ['nullable', 'integer', 'min:1950', 'max:2015'],
            'bride_year' => ['nullable', 'integer', 'min:1950', 'max:2015'],
            'check_dai_loi' => ['nullable', 'boolean'],
        ]);

        $result = $this->dateEngine->compareCandidateDates($validated['dates'], $validated);

        return response()->json(['ok' => true, 'data' => $result]);
    }

    /* =========================================================================
     * B. PUBLIC WEDDING PLANNER LANDING & CEREMONY LIBRARY (/to-chuc-dam-cuoi)
     * ========================================================================= */

    public function plannerLanding(Request $request): View
    {
        $sampleBudget = $this->budgetEngine->suggestAllocation(150_000_000);
        $sampleChecklist = $this->checklistService->generateStarterTasks(
            Carbon::now('Asia/Ho_Chi_Minh')->addMonths(6)->toDateString(),
            'bac',
            'nha_hang'
        );

        $userProjects = $request->user()
            ? WeddingProject::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        return view('client.pages.wedding.planner-landing', [
            'sampleBudget' => $sampleBudget,
            'sampleChecklist' => $sampleChecklist,
            'scriptTypes' => WeddingAiConciergeService::SCRIPT_TYPES,
            'userProjects' => $userProjects,
            'planSummary' => $this->getUserQuotaSummary($request->user()),
        ]);
    }

    public function ceremonies(): View
    {
        return view('client.pages.wedding.ceremonies');
    }

    public function apiQuickBudget(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'total_budget_vnd' => ['required', 'integer', 'min:10000000', 'max:50000000000'],
            'guest_count' => ['nullable', 'integer', 'min:10', 'max:5000'],
            'seats_per_table' => ['nullable', 'integer', 'min:4', 'max:12'],
        ]);

        $budget = (int) $validated['total_budget_vnd'];
        $guests = (int) ($validated['guest_count'] ?? 150);
        $seatsPerTable = (int) ($validated['seats_per_table'] ?? 10);
        $tables = (int) ceil($guests / $seatsPerTable);
        $reserveTables = max(1, (int) ceil($tables * 0.1));

        return response()->json([
            'ok' => true,
            'data' => [
                'total_budget_vnd' => $budget,
                'guest_count' => $guests,
                'seats_per_table' => $seatsPerTable,
                'estimated_tables' => $tables,
                'reserve_tables' => $reserveTables,
                'cost_per_guest_vnd' => $guests > 0 ? intdiv($budget, $guests) : 0,
                'allocations' => $this->budgetEngine->suggestAllocation($budget),
            ],
        ]);
    }

    public function apiGenerateScript(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'script_type' => ['required', 'string', 'in:mc_ceremony,speech_groom,speech_bride,vows,thank_you,workspace_advice'],
            'groom_name' => ['nullable', 'string', 'max:120'],
            'bride_name' => ['nullable', 'string', 'max:120'],
            'wedding_date' => ['nullable', 'string', 'max:40'],
            'region' => ['nullable', 'string', 'in:bac,trung,nam'],
            'tone' => ['nullable', 'string', 'max:80'],
            'custom_notes' => ['nullable', 'string', 'max:600'],
            'use_ai' => ['nullable', 'boolean'],
            'project_slug' => ['nullable', 'string', 'max:120'],
        ]);

        $useAi = (bool) ($validated['use_ai'] ?? false);
        $user = $request->user();

        if ($useAi) {
            if (! $user) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Vui lòng đăng nhập để sử dụng AI Wedding Concierge biên soạn cá nhân hóa (bạn vẫn có thể dùng Mẫu Biên Tập Chuẩn miễn phí ngay lập tức).',
                    'login_url' => route('login', ['redirect' => route('wedding.planner.index')]),
                ], 401);
            }

            $quotaError = $this->checkAndConsumeAiQuota($user);
            if ($quotaError !== null) {
                return response()->json(['ok' => false, 'message' => $quotaError], 429);
            }
        }

        $project = null;
        if (! empty($validated['project_slug']) && $user) {
            $project = WeddingProject::query()
                ->where('slug', $validated['project_slug'])
                ->where('user_id', $user->id)
                ->first();
        }

        $context = $validated;
        if ($project !== null && $validated['script_type'] === 'workspace_advice') {
            $budgetSummary = $this->budgetEngine->summarize(
                (int) $project->budget_total_vnd,
                $project->expenses()->get()->toArray()
            );
            $tasks = $project->tasks()->get();
            $context['workspace_summary'] = [
                'title' => $project->title,
                'wedding_date' => $project->wedding_date?->toDateString(),
                'tasks_total' => $tasks->count(),
                'tasks_done' => $tasks->where('status', 'done')->count(),
                'budget_total_vnd' => $budgetSummary['project_budget_vnd'],
                'committed_vnd' => $budgetSummary['total_committed_vnd'],
                'paid_vnd' => $budgetSummary['total_paid_vnd'],
                'outstanding_vnd' => $budgetSummary['total_outstanding_vnd'],
                'guest_target' => $project->guest_target,
            ];
        }

        $generated = $this->aiConcierge->generate($validated['script_type'], $context, $useAi);

        if ($user && $project !== null) {
            WeddingScript::create([
                'wedding_project_id' => $project->id,
                'user_id' => $user->id,
                'script_type' => $generated['type'],
                'title' => $generated['title'],
                'content' => $generated['content'],
                'source' => $generated['source'],
            ]);
        }

        return response()->json(['ok' => true, 'data' => $generated]);
    }

    /* =========================================================================
     * C. PRIVATE PERSONAL WEDDING WORKSPACE (/to-chuc-dam-cuoi/ho-so & /du-an/{slug})
     * ========================================================================= */

    public function myProjects(Request $request): View
    {
        $user = $request->user();
        $projects = WeddingProject::query()
            ->where('user_id', $user->id)
            ->withCount(['tasks', 'expenses', 'vendors'])
            ->latest()
            ->get();

        return view('client.pages.wedding.projects', [
            'projects' => $projects,
            'planSummary' => $this->getUserQuotaSummary($user),
        ]);
    }

    public function createProject(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $quota = $this->getUserQuotaSummary($user);

        $currentCount = WeddingProject::query()->where('user_id', $user->id)->count();
        if ($currentCount >= $quota['max_projects']) {
            $msg = "Gói {$quota['tier_label']} cho phép tối đa {$quota['max_projects']} hồ sơ tổ chức đám cưới. Hãy nâng cấp gói hoặc xóa bớt hồ sơ cũ.";
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 403);
            }

            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:180'],
            'groom_name' => ['required', 'string', 'max:120'],
            'bride_name' => ['required', 'string', 'max:120'],
            'groom_birth_year' => ['nullable', 'integer', 'min:1950', 'max:2015'],
            'bride_birth_year' => ['nullable', 'integer', 'min:1950', 'max:2015'],
            'wedding_date' => ['nullable', 'date'],
            'region_style' => ['nullable', 'string', 'in:bac,trung,nam'],
            'wedding_style' => ['nullable', 'string', 'in:nha_hang,ngoai_troi,toi_gian'],
            'budget_total_vnd' => ['nullable', 'integer', 'min:10000000', 'max:50000000000'],
            'guest_target' => ['nullable', 'integer', 'min:10', 'max:5000'],
            'candidates_json' => ['nullable', 'array', 'max:5'],
        ]);

        $title = trim((string) ($validated['title'] ?? ''));
        if ($title === '') {
            $title = "Đám cưới {$validated['groom_name']} & {$validated['bride_name']}";
        }

        $region = (string) ($validated['region_style'] ?? 'bac');
        $style = (string) ($validated['wedding_style'] ?? 'nha_hang');
        $budgetTotal = (int) ($validated['budget_total_vnd'] ?? 150_000_000);
        $slug = Str::slug($title).'-'.Str::lower(Str::random(6));

        $project = WeddingProject::create([
            'user_id' => $user->id,
            'slug' => $slug,
            'title' => $title,
            'groom_name' => $validated['groom_name'],
            'bride_name' => $validated['bride_name'],
            'groom_birth_year' => $validated['groom_birth_year'] ?? null,
            'bride_birth_year' => $validated['bride_birth_year'] ?? null,
            'wedding_date' => $validated['wedding_date'] ?? null,
            'status' => ! empty($validated['wedding_date']) ? 'tentative' : 'draft',
            'region_style' => $region,
            'wedding_style' => $style,
            'budget_total_vnd' => $budgetTotal,
            'guest_target' => (int) ($validated['guest_target'] ?? 150),
            'candidates_json' => $validated['candidates_json'] ?? [],
            'floorplan_json' => $this->seatingTimelineEngine->createDefaultFloorPlan(),
        ]);

        // Khởi tạo Checklist thông minh & Phân bổ ngân sách mặc định (số nguyên VND chính xác)
        $starterTasks = $this->checklistService->generateStarterTasks($project->wedding_date?->toDateString(), $region, $style);
        $codeToId = [];
        foreach ($starterTasks as $t) {
            $created = $project->tasks()->create([
                'code' => $t['code'],
                'phase' => $t['phase'],
                'title' => $t['title'],
                'due_date' => $t['due_date'],
                'priority' => $t['priority'],
                'status' => 'todo',
                'assignee' => $t['assignee'],
                'depends_on_task_id' => $t['depends_on_code'] && isset($codeToId[$t['depends_on_code']])
                    ? $codeToId[$t['depends_on_code']]
                    : null,
                'sort_order' => $t['sort_order'],
            ]);
            $codeToId[$t['code']] = $created->id;
        }

        foreach ($this->budgetEngine->suggestAllocation($budgetTotal) as $alloc) {
            $project->expenses()->create([
                'category' => $alloc['category'],
                'title' => $alloc['label'],
                'payer_side' => 'couple',
                'status' => 'planned',
                'budgeted_vnd' => $alloc['budgeted_vnd'],
                'estimated_vnd' => $alloc['budgeted_vnd'],
                'committed_vnd' => 0,
                'deposit_vnd' => 0,
                'paid_vnd' => 0,
                'refunded_vnd' => 0,
            ]);
        }

        // Lịch trình ngày cưới mẫu có khoảng đệm di chuyển hợp lý
        $defaultTimeline = [
            ['title' => 'Trang điểm cô dâu & chuẩn bị tư gia', 'start_time' => '05:30', 'end_time' => '07:30', 'location' => 'Nhà gái', 'assignee' => 'Makeup & Cô dâu'],
            ['title' => 'Nhà trai xuất phát đón dâu', 'start_time' => '08:00', 'end_time' => '08:45', 'location' => 'Di chuyển', 'assignee' => 'Đoàn nhà trai'],
            ['title' => 'Lễ Gia Tiên & xin dâu tại nhà gái', 'start_time' => '09:00', 'end_time' => '10:00', 'location' => 'Nhà gái', 'assignee' => 'Hai gia đình'],
            ['title' => 'Rước dâu về nhà trai & làm lễ gia tiên', 'start_time' => '10:30', 'end_time' => '11:30', 'location' => 'Nhà trai', 'assignee' => 'Hai gia đình'],
            ['title' => 'Đón khách & Tiệc cưới tại nhà hàng', 'start_time' => '17:30', 'end_time' => '21:00', 'location' => 'Trung tâm tiệc cưới', 'assignee' => 'MC & Ekip'],
        ];
        foreach ($defaultTimeline as $i => $slot) {
            $project->timelineItems()->create(array_merge($slot, ['sort_order' => ($i + 1) * 10]));
        }

        $redirectUrl = route('wedding.workspace.show', ['slug' => $project->slug]);
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'redirect_url' => $redirectUrl, 'project' => $project]);
        }

        return redirect($redirectUrl);
    }

    public function showWorkspace(Request $request, string $slug): View
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $project->load(['tasks', 'expenses', 'timelineItems', 'vendors', 'scripts']);

        $budgetSummary = $this->budgetEngine->summarize(
            (int) $project->budget_total_vnd,
            $project->expenses->toArray()
        );
        $floorplanValidation = $this->seatingTimelineEngine->validateFloorPlan(
            (array) ($project->floorplan_json ?: $this->seatingTimelineEngine->createDefaultFloorPlan())
        );
        $timelineAnalysis = $this->seatingTimelineEngine->detectTimelineConflicts(
            $project->timelineItems->toArray()
        );

        $userInvitations = Invitation::query()
            ->where('user_id', $request->user()->id)
            ->select(['id', 'slug', 'title'])
            ->latest()
            ->limit(10)
            ->get();

        $daysLeft = $project->wedding_date
            ? (int) Carbon::now('Asia/Ho_Chi_Minh')->startOfDay()->diffInDays($project->wedding_date->startOfDay(), false)
            : null;

        return view('client.pages.wedding.workspace', [
            'project' => $project,
            'budgetSummary' => $budgetSummary,
            'floorplanValidation' => $floorplanValidation,
            'timelineAnalysis' => $timelineAnalysis,
            'categories' => WeddingBudgetEngine::DEFAULT_CATEGORIES,
            'scriptTypes' => WeddingAiConciergeService::SCRIPT_TYPES,
            'userInvitations' => $userInvitations,
            'daysLeft' => $daysLeft,
            'planSummary' => $this->getUserQuotaSummary($request->user()),
        ]);
    }

    public function updateProject(Request $request, string $slug): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:180'],
            'groom_name' => ['sometimes', 'string', 'max:120'],
            'bride_name' => ['sometimes', 'string', 'max:120'],
            'wedding_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'in:draft,candidate,tentative,confirmed,completed'],
            'budget_total_vnd' => ['sometimes', 'integer', 'min:0', 'max:50000000000'],
            'guest_target' => ['sometimes', 'integer', 'min:1', 'max:10000'],
            'invitation_id' => ['nullable', 'integer'],
            'candidates_json' => ['nullable', 'array', 'max:5'],
        ]);

        $project->update($validated);

        return response()->json(['ok' => true, 'project' => $project->fresh()]);
    }

    public function destroyProject(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $project->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('wedding.workspace.index')->with('success', 'Đã xóa hồ sơ tổ chức đám cưới.');
    }

    /* --- Tasks CRUD + Dependency Cycle Check --- */

    public function storeTask(Request $request, string $slug): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $validated = $request->validate([
            'phase' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:220'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', 'in:low,medium,high'],
            'status' => ['nullable', 'string', 'in:todo,in_progress,done'],
            'assignee' => ['nullable', 'string', 'max:120'],
            'depends_on_task_id' => ['nullable', 'integer'],
        ]);

        $task = $project->tasks()->create([
            'phase' => $validated['phase'] ?? 'Bổ sung',
            'title' => $validated['title'],
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'status' => $validated['status'] ?? 'todo',
            'assignee' => $validated['assignee'] ?? 'Cặp đôi',
            'depends_on_task_id' => $validated['depends_on_task_id'] ?? null,
            'sort_order' => ((int) $project->tasks()->max('sort_order')) + 10,
        ]);

        return response()->json(['ok' => true, 'task' => $task]);
    }

    public function updateTask(Request $request, string $slug, int $taskId): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $task = $project->tasks()->where('id', $taskId)->firstOrFail();

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:220'],
            'status' => ['sometimes', 'string', 'in:todo,in_progress,done'],
            'priority' => ['sometimes', 'string', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
            'assignee' => ['nullable', 'string', 'max:120'],
            'depends_on_task_id' => ['nullable', 'integer'],
        ]);

        if (array_key_exists('depends_on_task_id', $validated)) {
            $depMap = $project->tasks()->pluck('depends_on_task_id', 'id')->toArray();
            try {
                $this->checklistService->assertValidDependency($task->id, $validated['depends_on_task_id'], $depMap);
            } catch (InvalidArgumentException $e) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }
        }

        $task->update($validated);

        return response()->json(['ok' => true, 'task' => $task->fresh()]);
    }

    public function destroyTask(Request $request, string $slug, int $taskId): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $task = $project->tasks()->where('id', $taskId)->firstOrFail();
        $project->tasks()->where('depends_on_task_id', $task->id)->update(['depends_on_task_id' => null]);
        $task->delete();

        return response()->json(['ok' => true]);
    }

    /* --- Budget CRUD + Export CSV --- */

    public function storeExpense(Request $request, string $slug): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:180'],
            'payer_side' => ['nullable', 'string', 'in:couple,groom_family,bride_family,other'],
            'status' => ['nullable', 'string', 'in:planned,quoted,committed,partially_paid,paid,cancelled'],
            'budgeted_vnd' => ['nullable', 'integer', 'min:0'],
            'estimated_vnd' => ['nullable', 'integer', 'min:0'],
            'committed_vnd' => ['nullable', 'integer', 'min:0'],
            'deposit_vnd' => ['nullable', 'integer', 'min:0'],
            'paid_vnd' => ['nullable', 'integer', 'min:0'],
            'refunded_vnd' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $deposit = (int) ($validated['deposit_vnd'] ?? 0);
        $paid = max($deposit, (int) ($validated['paid_vnd'] ?? 0));

        $expense = $project->expenses()->create([
            'category' => $validated['category'],
            'title' => $validated['title'],
            'payer_side' => $validated['payer_side'] ?? 'couple',
            'status' => $validated['status'] ?? 'planned',
            'budgeted_vnd' => (int) ($validated['budgeted_vnd'] ?? 0),
            'estimated_vnd' => (int) ($validated['estimated_vnd'] ?? 0),
            'committed_vnd' => (int) ($validated['committed_vnd'] ?? 0),
            'deposit_vnd' => $deposit,
            'paid_vnd' => $paid,
            'refunded_vnd' => (int) ($validated['refunded_vnd'] ?? 0),
            'notes' => $validated['notes'] ?? null,
        ]);

        $summary = $this->budgetEngine->summarize((int) $project->budget_total_vnd, $project->expenses()->get()->toArray());

        return response()->json(['ok' => true, 'expense' => $expense, 'summary' => $summary]);
    }

    public function updateExpense(Request $request, string $slug, int $expenseId): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $expense = $project->expenses()->where('id', $expenseId)->firstOrFail();

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:180'],
            'payer_side' => ['sometimes', 'string', 'in:couple,groom_family,bride_family,other'],
            'status' => ['sometimes', 'string', 'in:planned,quoted,committed,partially_paid,paid,cancelled'],
            'budgeted_vnd' => ['sometimes', 'integer', 'min:0'],
            'estimated_vnd' => ['sometimes', 'integer', 'min:0'],
            'committed_vnd' => ['sometimes', 'integer', 'min:0'],
            'deposit_vnd' => ['sometimes', 'integer', 'min:0'],
            'paid_vnd' => ['sometimes', 'integer', 'min:0'],
            'refunded_vnd' => ['sometimes', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if (isset($validated['deposit_vnd']) || isset($validated['paid_vnd'])) {
            $dep = (int) ($validated['deposit_vnd'] ?? $expense->deposit_vnd);
            $paid = (int) ($validated['paid_vnd'] ?? $expense->paid_vnd);
            $validated['paid_vnd'] = max($dep, $paid);
        }

        $expense->update($validated);
        $summary = $this->budgetEngine->summarize((int) $project->budget_total_vnd, $project->expenses()->get()->toArray());

        return response()->json(['ok' => true, 'expense' => $expense->fresh(), 'summary' => $summary]);
    }

    public function destroyExpense(Request $request, string $slug, int $expenseId): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $project->expenses()->where('id', $expenseId)->firstOrFail()->delete();
        $summary = $this->budgetEngine->summarize((int) $project->budget_total_vnd, $project->expenses()->get()->toArray());

        return response()->json(['ok' => true, 'summary' => $summary]);
    }

    public function exportBudgetCsv(Request $request, string $slug): Response
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $csv = $this->budgetEngine->exportCsv($project->title, $project->expenses()->get()->toArray());
        $filename = 'ngan-sach-cuoi-'.$project->slug.'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /* --- 2D Floor Plan & Seating Studio --- */

    public function saveFloorPlan(Request $request, string $slug): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $validated = $request->validate([
            'floorplan' => ['required', 'array'],
            'floorplan.room_width' => ['required', 'integer', 'min:300', 'max:2000'],
            'floorplan.room_height' => ['required', 'integer', 'min:240', 'max:1600'],
            'floorplan.tables' => ['required', 'array', 'max:120'],
        ]);

        $validation = $this->seatingTimelineEngine->validateFloorPlan($validated['floorplan']);
        $project->update(['floorplan_json' => $validated['floorplan']]);

        return response()->json(['ok' => true, 'validation' => $validation]);
    }

    /* --- Timeline CRUD + Conflict Detector --- */

    public function storeTimelineItem(Request $request, string $slug): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'start_time' => ['required', 'string', 'regex:/^([01]?\d|2[0-3]):[0-5]\d$/'],
            'end_time' => ['required', 'string', 'regex:/^([01]?\d|2[0-3]):[0-5]\d$/'],
            'location' => ['nullable', 'string', 'max:180'],
            'assignee' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:400'],
        ]);

        $item = $project->timelineItems()->create($validated);
        $analysis = $this->seatingTimelineEngine->detectTimelineConflicts($project->timelineItems()->get()->toArray());

        return response()->json(['ok' => true, 'item' => $item, 'analysis' => $analysis]);
    }

    public function destroyTimelineItem(Request $request, string $slug, int $itemId): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $project->timelineItems()->where('id', $itemId)->firstOrFail()->delete();
        $analysis = $this->seatingTimelineEngine->detectTimelineConflicts($project->timelineItems()->get()->toArray());

        return response()->json(['ok' => true, 'analysis' => $analysis]);
    }

    /* --- Vendor Quote Comparison CRUD --- */

    public function storeVendor(Request $request, string $slug): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'quote_vnd' => ['nullable', 'integer', 'min:0'],
            'deposit_vnd' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'in:shortlisted,contacted,quoted,booked,rejected'],
            'pros' => ['nullable', 'string', 'max:400'],
            'cons' => ['nullable', 'string', 'max:400'],
        ]);

        $vendor = $project->vendors()->create([
            'category' => $validated['category'],
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'quote_vnd' => (int) ($validated['quote_vnd'] ?? 0),
            'deposit_vnd' => (int) ($validated['deposit_vnd'] ?? 0),
            'status' => $validated['status'] ?? 'shortlisted',
            'pros' => $validated['pros'] ?? null,
            'cons' => $validated['cons'] ?? null,
        ]);

        return response()->json(['ok' => true, 'vendor' => $vendor]);
    }

    public function destroyVendor(Request $request, string $slug, int $vendorId): JsonResponse
    {
        $project = $this->findOwnedProjectOrAbort($slug, $request->user());
        $project->vendors()->where('id', $vendorId)->firstOrFail()->delete();

        return response()->json(['ok' => true]);
    }

    /* =========================================================================
     * HELPERS (Project Isolation + Fair Plan Quotas with Cache Anti-Bypass)
     * ========================================================================= */

    protected function findOwnedProjectOrAbort(string $slug, ?User $user): WeddingProject
    {
        $project = WeddingProject::query()->where('slug', $slug)->firstOrFail();
        if (! $user || ($project->user_id !== $user->id && ! $user->isAdmin())) {
            abort(403, 'Bạn không có quyền truy cập hồ sơ tổ chức đám cưới này.');
        }

        return $project;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getUserQuotaSummary(?User $user): array
    {
        $tier = 'free';
        if ($user) {
            if ($user->isAdmin()) {
                $tier = 'pro';
            } else {
                $this->planAccess->syncExpiredUserPlanIfNeeded($user);
                $tier = $this->planAccess->normalizeTier($user->current_plan_slug ?? 'premium');
            }
        }
        $cfg = self::PLAN_QUOTAS[$tier] ?? self::PLAN_QUOTAS['free'];
        $todayKey = $user ? "wedding_ai_daily:{$user->id}:".now('Asia/Ho_Chi_Minh')->toDateString() : '';
        $usedAi = $user ? (int) Cache::get($todayKey, 0) : 0;

        return [
            'tier' => $tier,
            'tier_label' => $this->planAccess->getTierLabel($tier),
            'max_projects' => $cfg['max_projects'],
            'daily_ai' => $cfg['daily_ai'],
            'used_ai_today' => $usedAi,
            'remaining_ai_today' => max(0, $cfg['daily_ai'] - $usedAi),
        ];
    }

    protected function checkAndConsumeAiQuota(User $user): ?string
    {
        $summary = $this->getUserQuotaSummary($user);
        $cfg = self::PLAN_QUOTAS[$summary['tier']] ?? self::PLAN_QUOTAS['free'];

        $cooldownKey = "wedding_ai_cooldown:{$user->id}";
        if (Cache::has($cooldownKey)) {
            return "Vui lòng đợi {$cfg['cooldown_sec']} giây giữa các lượt gọi AI Wedding Concierge.";
        }

        $todayKey = "wedding_ai_daily:{$user->id}:".now('Asia/Ho_Chi_Minh')->toDateString();
        $dbCount = WeddingScript::query()
            ->where('user_id', $user->id)
            ->where('source', 'ai')
            ->whereDate('created_at', now('Asia/Ho_Chi_Minh')->toDateString())
            ->count();
        $cacheCount = (int) Cache::get($todayKey, 0);
        $used = max($dbCount, $cacheCount);

        if ($used >= $cfg['daily_ai']) {
            return "Bạn đã dùng hết {$cfg['daily_ai']} lượt AI Wedding Concierge hôm nay của {$summary['tier_label']}. Bạn vẫn có thể sử dụng Mẫu Biên Tập Chuẩn miễn phí không giới hạn.";
        }

        Cache::put($todayKey, $used + 1, now('Asia/Ho_Chi_Minh')->endOfDay());
        Cache::put($cooldownKey, 1, now()->addSeconds($cfg['cooldown_sec']));

        return null;
    }
}
