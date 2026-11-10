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
        if (! in_array($activeTab, ['checklist', 'budget', 'vendors'])) {
            $activeTab = 'checklist';
        }

        $summary = $this->plannerService->getPlanSummary($wedding);
        $tasks = $wedding->checklistTasks()->get();
        $budgetItems = $wedding->budgetItems()->get();
        $vendors = $wedding->vendors()->get();

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
            'vendors' => $vendors,
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

    /**
     * Thêm nhà cung cấp dịch vụ cưới mới.
     */
    public function storeVendor(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $validated = $request->validate([
            'name' => 'required|string|max:190',
            'category' => 'required|string|in:venue,catering,photography,photo_video,attire,bridal,makeup,decor,music_mc,wedding_planner,favors,invitation_print,transport,car_transport,trap_an_hoi,other',
            'contact_person' => 'nullable|string|max:190',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:190',
            'address' => 'nullable|string|max:255',
            'contract_status' => 'required|string|in:contacting,contract_signed,in_progress,completed,cancelled',
            'total_amount' => 'required|integer|min:0',
            'deposit_paid' => 'nullable|integer|min:0',
            'payment_due_date' => 'nullable|date',
            'service_timeline' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vendor = $wedding->vendors()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm nhà cung cấp dịch vụ thành công.',
                'vendor' => $vendor,
            ]);
        }

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'vendors'])
            ->with('success', 'Đã thêm nhà cung cấp dịch vụ thành công.');
    }

    /**
     * Cập nhật thông tin đối tác / nhà cung cấp dịch vụ.
     */
    public function updateVendor(Request $request, int $id, int $vendorId): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $vendor = $wedding->vendors()->findOrFail($vendorId);

        $validated = $request->validate([
            'name' => 'required|string|max:190',
            'category' => 'required|string|in:venue,catering,photography,photo_video,attire,bridal,makeup,decor,music_mc,wedding_planner,favors,invitation_print,transport,car_transport,trap_an_hoi,other',
            'contact_person' => 'nullable|string|max:190',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:190',
            'address' => 'nullable|string|max:255',
            'contract_status' => 'required|string|in:contacting,contract_signed,in_progress,completed,cancelled',
            'total_amount' => 'required|integer|min:0',
            'deposit_paid' => 'nullable|integer|min:0',
            'payment_due_date' => 'nullable|date',
            'service_timeline' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vendor->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật nhà cung cấp thành công.',
                'vendor' => $vendor->fresh(),
            ]);
        }

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'vendors'])
            ->with('success', 'Đã cập nhật nhà cung cấp thành công.');
    }

    /**
     * Xóa nhà cung cấp khỏi danh sách.
     */
    public function destroyVendor(Request $request, int $id, int $vendorId): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $vendor = $wedding->vendors()->findOrFail($vendorId);
        $vendor->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa nhà cung cấp khỏi danh mục.',
            ]);
        }

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'vendors'])
            ->with('success', 'Đã xóa nhà cung cấp khỏi danh mục.');
    }

    /**
     * Cập nhật nhanh số tiền đã đặt cọc cho nhà cung cấp.
     */
    public function depositVendor(Request $request, int $id, int $vendorId): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $vendor = $wedding->vendors()->findOrFail($vendorId);

        $validated = $request->validate([
            'deposit_paid' => 'required|integer|min:0',
        ]);

        $vendor->update(['deposit_paid' => $validated['deposit_paid']]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật số tiền cọc cho nhà cung cấp.',
                'deposit_paid' => $vendor->deposit_paid,
                'balance_due' => $vendor->balance_due,
            ]);
        }

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'vendors'])
            ->with('success', 'Đã cập nhật số tiền cọc cho nhà cung cấp.');
    }

    /**
     * Khởi tạo sẵn bộ đối tác / nhà cung cấp cưới mẫu.
     */
    public function seedDefaultVendors(int $id): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $count = $this->plannerService->seedDefaultVendors($wedding);

        return redirect()->route('account.planner.index', ['id' => $id, 'tab' => 'vendors'])
            ->with('success', "Đã khởi tạo thành công {$count} nhà cung cấp dịch vụ cưới mẫu.");
    }

    /**
     * Xuất danh bạ nhà cung cấp cưới ra file CSV UTF-8 BOM.
     */
    public function exportVendorsCsv(int $id): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        $this->authorizeAccess($wedding);

        $csv = $this->plannerService->exportVendorsCsv($wedding);
        $filename = 'danh-ba-doi-tac-nha-cung-cap-cuoi-'.($wedding->groom_name ? Str::slug($wedding->groom_name.'-'.$wedding->bride_name) : 'mayhen').'.csv';

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
