<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FinancialReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminFinancialReconciliationController extends Controller
{
    public function __construct(
        protected FinancialReconciliationService $reconciliationService
    ) {}

    /**
     * Bảng điều khiển Sổ Đối Soát Giao Dịch & Webhook Tài Chính (Mục 137 PRD V3).
     */
    public function index(Request $request): View
    {
        $currentTab = $request->query('tab', 'reconciliations');
        if (! in_array($currentTab, ['reconciliations', 'webhook-logs', 'refunds'], true)) {
            $currentTab = 'reconciliations';
        }

        $kpis = $this->reconciliationService->getReconciliationKpis();

        $reconciliations = null;
        $webhookLogs = null;
        $refunds = null;

        if ($currentTab === 'reconciliations') {
            $reconciliations = $this->reconciliationService->getFilteredReconciliations(
                search: $request->query('search'),
                status: $request->query('status'),
                actionType: $request->query('action_type'),
                perPage: 15
            );
        } elseif ($currentTab === 'webhook-logs') {
            $webhookLogs = $this->reconciliationService->getFilteredWebhookLogs(
                gateway: $request->query('gateway'),
                status: $request->query('status'),
                search: $request->query('search'),
                perPage: 20
            );
        } elseif ($currentTab === 'refunds') {
            $refunds = $this->reconciliationService->getFilteredReconciliations(
                search: $request->query('search'),
                status: $request->query('status'),
                actionType: 'refund_request',
                perPage: 15
            );
        }

        // Lấy danh sách 20 đơn hàng pending gần nhất để phục vụ modal tạo đối soát
        $pendingOrders = Order::with('user')
            ->whereIn('status', ['pending', 'failed'])
            ->latest('id')
            ->take(20)
            ->get();

        return view('admin.pages.reconciliations.index', compact(
            'currentTab',
            'kpis',
            'reconciliations',
            'webhookLogs',
            'refunds',
            'pendingOrders'
        ));
    }

    /**
     * Lập phiếu đối soát khớp lệnh thủ công cho đơn hàng.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'action_type' => ['required', 'in:manual_approval,amount_mismatch_resolution'],
            'amount' => ['required', 'integer', 'min:1000'],
            'reference_code' => ['required', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'reconciliation_note' => ['required', 'string', 'max:500'],
        ]);

        $reconciliation = $this->reconciliationService->createManualReconciliation($validated, $request->user());

        return redirect()->route('admin.reconciliations.index', ['tab' => 'reconciliations'])
            ->with('success', "Phiếu đối soát #{$reconciliation->id} đã được tạo thành công và đang chờ Kế toán trưởng phê duyệt.");
    }

    /**
     * Phê duyệt phiếu đối soát (Maker-Checker).
     */
    public function approve(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'review_note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->reconciliationService->approveReconciliation($id, $validated['review_note'] ?? null, $request->user());

        return redirect()->route('admin.reconciliations.index', ['tab' => 'reconciliations'])
            ->with('success', 'Đã phê duyệt đối soát thành công. Đơn hàng đã được đánh dấu thanh toán và kích hoạt gói cước.');
    }

    /**
     * Từ chối phiếu đối soát.
     */
    public function reject(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->reconciliationService->rejectReconciliation($id, $validated['reason'], $request->user());

        return redirect()->route('admin.reconciliations.index', ['tab' => 'reconciliations'])
            ->with('success', 'Đã từ chối phiếu đối soát.');
    }

    /**
     * Xác nhận chi trả hoàn tiền cho khách hàng.
     */
    public function confirmRefund(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'review_note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->reconciliationService->confirmRefundPayout($id, $validated['review_note'] ?? null, $request->user());

        return redirect()->route('admin.reconciliations.index', ['tab' => 'refunds'])
            ->with('success', 'Đã xác nhận hoàn tiền thành công. Trạng thái đơn hàng đã được cập nhật.');
    }

    /**
     * Xuất tệp báo cáo đối soát CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        return $this->reconciliationService->exportCsv($request->query('status'));
    }
}

