<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Services\WeddingPlannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WeddingPlannerController extends Controller
{
    public function __construct(
        protected WeddingPlannerService $plannerService
    ) {}

    /**
     * Giao diện Bảng Kế Hoạch & Sổ Dự Toán Ngân Sách Cưới.
     */
    public function index(Request $request, int $id)
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $activeTab = $request->query('tab', 'checklist');
        if (! in_array($activeTab, ['checklist', 'budget'])) {
            $activeTab = 'checklist';
        }

        $summary = $this->plannerService->getPlanSummary($wedding);
        $tasks = $wedding->checklistTasks()->get();
        $budgetItems = $wedding->budgetItems()->get();

        // Nhóm task theo giai đoạn (Phase)
        $tasksByPhase = [
            '12_months' => $tasks->where('phase', '12_months'),
            '6_months' => $tasks->where('phase', '6_months'),
            '3_months' => $tasks->where('phase', '3_months'),
            '1_month' => $tasks->where('phase', '1_month'),
            '1_week' => $tasks->where('phase', '1_week'),
            'wedding_day' => $tasks->where('phase', 'wedding_day'),
            'post_wedding' => $tasks->where('phase', 'post_wedding'),
        ];

        return view('client.pages.account.planner.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'summary' => $summary,
            'tasks' => $tasks,
            'tasksByPhase' => $tasksByPhase,
            'budgetItems' => $budgetItems,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Tự động khởi tạo bộ checklist chuẩn mực văn hóa cưới hỏi Việt Nam.
     */
    public function seedDefaults(int $id): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $count = $this->plannerService->seedDefaultChecklist($wedding);

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'checklist'])
            ->with('success', "Đã khởi tạo thành công {$count} công việc chuẩn bị cưới chuẩn Việt Nam!");
    }

    /**
     * Thêm công việc mới vào kế hoạch.
     */
    public function storeTask(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'phase' => 'required|string|in:12_months,6_months,3_months,1_month,1_week,wedding_day,post_wedding',
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'assignee_name' => 'nullable|string|max:100',
            'due_date' => 'nullable|date',
            'priority' => 'required|string|in:urgent,high,medium,low',
        ]);

        $task = $this->plannerService->createTask($wedding, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm công việc thành công.',
                'task' => $task,
            ]);
        }

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'checklist'])
            ->with('success', 'Đã thêm công việc thành công.');
    }

    /**
     * Cập nhật công việc.
     */
    public function updateTask(Request $request, int $id, int $taskId): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $task = $wedding->checklistTasks()->findOrFail($taskId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'phase' => 'required|string|in:12_months,6_months,3_months,1_month,1_week,wedding_day,post_wedding',
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'assignee_name' => 'nullable|string|max:100',
            'due_date' => 'nullable|date',
            'priority' => 'required|string|in:urgent,high,medium,low',
            'status' => 'nullable|string|in:pending,in_progress,completed,cancelled',
        ]);

        $task = $this->plannerService->updateTask($task, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật công việc thành công.',
                'task' => $task,
            ]);
        }

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'checklist'])
            ->with('success', 'Đã cập nhật công việc thành công.');
    }

    /**
     * Bật/tắt trạng thái hoàn thành task realtime.
     */
    public function toggleTask(Request $request, int $id, int $taskId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $task = $wedding->checklistTasks()->findOrFail($taskId);
        $task = $this->plannerService->toggleTask($task);

        $summary = $this->plannerService->getPlanSummary($wedding);

        return response()->json([
            'success' => true,
            'is_completed' => $task->isCompleted(),
            'status' => $task->status,
            'completed_at' => $task->completed_at?->format('H:i d/m/Y'),
            'summary' => $summary['tasks'],
            'message' => $task->isCompleted() ? 'Đã hoàn thành công việc!' : 'Đã chuyển về việc chưa xong.',
        ]);
    }

    /**
     * Xóa công việc.
     */
    public function destroyTask(int $id, int $taskId): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $task = $wedding->checklistTasks()->findOrFail($taskId);
        $this->plannerService->deleteTask($task);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa công việc khỏi danh sách.',
            ]);
        }

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'checklist'])
            ->with('success', 'Đã xóa công việc khỏi danh sách.');
    }

    /**
     * Thêm mới khoản mục dự toán chi phí.
     */
    public function storeBudgetItem(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $validated = $request->validate([
            'category' => 'required|string|in:venue_catering,attire_jewelry,photography_video,invitations_favors,decor_flowers,ceremony_offerings,entertainment_music,contingency,other',
            'item_name' => 'required|string|max:255',
            'estimated_cost' => 'required|integer|min:0',
            'actual_cost' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|integer|min:0',
            'vendor_name' => 'nullable|string|max:255',
            'vendor_contact' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_paid_in_full' => 'nullable|boolean',
        ]);

        $item = $this->plannerService->createBudgetItem($wedding, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm khoản chi thành công.',
                'item' => $item,
            ]);
        }

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'budget'])
            ->with('success', 'Đã thêm khoản chi thành công.');
    }

    /**
     * Cập nhật khoản mục chi phí.
     */
    public function updateBudgetItem(Request $request, int $id, int $itemId): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $item = $wedding->budgetItems()->findOrFail($itemId);

        $validated = $request->validate([
            'category' => 'required|string|in:venue_catering,attire_jewelry,photography_video,invitations_favors,decor_flowers,ceremony_offerings,entertainment_music,contingency,other',
            'item_name' => 'required|string|max:255',
            'estimated_cost' => 'required|integer|min:0',
            'actual_cost' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|integer|min:0',
            'vendor_name' => 'nullable|string|max:255',
            'vendor_contact' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_paid_in_full' => 'nullable|boolean',
        ]);

        $item = $this->plannerService->updateBudgetItem($item, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật khoản chi thành công.',
                'item' => $item,
            ]);
        }

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'budget'])
            ->with('success', 'Đã cập nhật khoản chi thành công.');
    }

    /**
     * Xóa khoản mục chi phí.
     */
    public function destroyBudgetItem(int $id, int $itemId): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $item = $wedding->budgetItems()->findOrFail($itemId);
        $this->plannerService->deleteBudgetItem($item);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa khoản chi khỏi danh sách.',
            ]);
        }

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'budget'])
            ->with('success', 'Đã xóa khoản chi khỏi danh sách.');
    }

    /**
     * Cập nhật hạn mức ngân sách mục tiêu.
     */
    public function updateTargetBudget(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $validated = $request->validate([
            'target_budget' => 'required|integer|min:0|max:100000000000',
        ]);

        $this->plannerService->updateTargetBudget($wedding, $validated['target_budget']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật hạn mức ngân sách mục tiêu.',
                'target_budget' => $validated['target_budget'],
                'target_budget_formatted' => number_format($validated['target_budget']).' đ',
            ]);
        }

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'budget'])
            ->with('success', 'Đã cập nhật hạn mức ngân sách mục tiêu.');
    }

    /**
     * Xuất file CSV dự toán chi phí đám cưới.
     */
    public function exportBudgetCsv(int $id): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $csv = $this->plannerService->exportBudgetCsv($wedding);
        $filename = 'du-toan-ngan-sach-cuoi-'.($wedding->groom_name ? Str::slug($wedding->groom_name.'-'.$wedding->bride_name) : 'mayhen').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function authorizeAccess(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        abort(404);
    }
}
