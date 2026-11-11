<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReconciliation;
use App\Services\OrderRefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOrderRefundController extends Controller
{
    public function __construct(
        protected OrderRefundService $refundService
    ) {}

    /**
     * Màn hình chỉ huy Sổ Hoàn Tiền & Xử Lý Khiếu Nại (Mục 140 PRD V3 & Mục 9.3 DOCX).
     */
    public function index(Request $request): View
    {
        $currentTab = $request->query('tab', 'requests');
        if (! in_array($currentTab, ['requests', 'policy', 'fast_refund'], true)) {
            $currentTab = 'requests';
        }

        $filters = [
            'status' => $request->query('status', 'all'),
            'search' => $request->query('search', ''),
        ];

        $kpis = $this->refundService->getRefundKpis();
        $requests = $this->refundService->getFilteredRefundRequests($filters, 15);

        // Nạp danh sách đơn hàng đã thanh toán gần đây cho tab Tạo nhanh yêu cầu
        $eligibleOrders = collect();
        if ($currentTab === 'fast_refund') {
            $eligibleOrders = Order::with('user', 'plan')
                ->whereIn('status', ['paid', 'refunded'])
                ->latest('id')
                ->take(20)
                ->get();
        }

        return view('admin.pages.orders.refunds.index', compact(
            'currentTab',
            'filters',
            'kpis',
            'requests',
            'eligibleOrders'
        ));
    }

    /**
     * Tạo yêu cầu hoàn tiền mới (Two-man rule: Bước 1 CSKH đề xuất).
     */
    public function storeRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_code' => ['required', 'string'],
            'amount' => ['nullable', 'numeric', 'min:1000'],
            'reason' => ['required', 'string', 'max:500'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:64'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'policy_clause' => ['required', 'string', 'in:24h_pre_publish,system_incident,force_majeure,other'],
        ], [
            'order_code.required' => 'Vui lòng nhập mã đơn hàng cần hoàn tiền.',
            'reason.required' => 'Vui lòng ghi rõ lý do khiếu nại của khách hàng.',
            'policy_clause.required' => 'Vui lòng chọn điều khoản chính sách áp dụng.',
        ]);

        $order = Order::where('order_code', trim($validated['order_code']))
            ->orWhere('id', trim($validated['order_code']))
            ->first();

        if (! $order) {
            return back()->with('error', 'Không tìm thấy đơn hàng tương ứng với mã cung cấp.')->withInput();
        }

        try {
            $this->refundService->createRefundRequest($order, $validated, $request->user());

            return redirect()
                ->route('admin.refunds.index', ['tab' => 'requests'])
                ->with('success', "Đã tạo phiếu yêu cầu hoàn tiền cho đơn hàng #{$order->order_code}. Chờ quản lý thẩm định.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi tạo yêu cầu hoàn tiền: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Thẩm định & Phê duyệt chi trả hoàn tiền (Two-man rule: Bước 2 Manager duyệt).
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'payout_reference' => ['required', 'string', 'max:100'],
            'review_note' => ['nullable', 'string', 'max:500'],
        ], [
            'payout_reference.required' => 'Vui lòng nhập mã giao dịch chuyển khoản ngân hàng thực tế.',
        ]);

        $reconciliation = OrderReconciliation::findOrFail($id);

        try {
            $this->refundService->approveRefund($reconciliation, $validated, $request->user());

            return redirect()
                ->route('admin.refunds.index', ['tab' => 'requests'])
                ->with('success', "Đã thẩm định và xác nhận hoàn tiền thành công cho đơn hàng #{$reconciliation->order->order_code}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi thẩm định hoàn tiền: '.$e->getMessage());
        }
    }

    /**
     * Từ chối yêu cầu hoàn tiền.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:500'],
        ], [
            'reject_reason.required' => 'Vui lòng ghi rõ lý do từ chối yêu cầu hoàn tiền.',
        ]);

        $reconciliation = OrderReconciliation::findOrFail($id);

        try {
            $this->refundService->rejectRefund($reconciliation, $validated['reject_reason'], $request->user());

            return redirect()
                ->route('admin.refunds.index', ['tab' => 'requests'])
                ->with('success', "Đã từ chối yêu cầu hoàn tiền cho đơn #{$reconciliation->order->order_code}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi từ chối hoàn tiền: '.$e->getMessage());
        }
    }

    /**
     * Xuất tệp CSV đối soát hoàn tiền.
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = [
            'status' => $request->query('status', 'all'),
            'search' => $request->query('search', ''),
        ];

        return $this->refundService->exportCsv($filters, $request->user());
    }
}
