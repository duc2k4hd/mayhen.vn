<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminInvoiceController extends Controller
{
    public function __construct(
        protected InvoiceManagementService $invoiceService
    ) {}

    /**
     * Màn hình quản trị hóa đơn điện tử & phiếu thu VAT (Mục 141 PRD V3).
     */
    public function index(Request $request): View
    {
        $kpis = $this->invoiceService->getInvoiceKpis();
        $invoices = $this->invoiceService->getFilteredInvoices($request);

        return view('admin.pages.invoices.index', compact('kpis', 'invoices'));
    }

    /**
     * Xem chi tiết hóa đơn.
     */
    public function show(int $id): View
    {
        $invoice = Invoice::with(['order.user', 'order.plan', 'issuer'])->findOrFail($id);

        return view('admin.pages.invoices.show', compact('invoice'));
    }

    /**
     * Màn hình tạo hóa đơn cho đơn hàng.
     */
    public function create(Request $request): View
    {
        $orderId = $request->input('order_id');
        $order = null;
        if ($orderId) {
            $order = Order::with(['user', 'plan'])->findOrFail($orderId);
        }

        $paidOrders = Order::with('user')
            ->where('status', 'paid')
            ->whereDoesntHave('invoice')
            ->latest('id')
            ->take(50)
            ->get();

        return view('admin.pages.invoices.create', compact('order', 'paidOrders'));
    }

    /**
     * Lưu thông tin hóa đơn mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:150'],
            'vat_rate' => ['nullable', 'integer', 'in:0,8,10'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
        ], [
            'order_id.required' => 'Vui lòng chọn đơn hàng cần lập hóa đơn.',
            'customer_name.required' => 'Vui lòng nhập tên khách hàng nhận hóa đơn.',
            'customer_email.required' => 'Vui lòng nhập email nhận hóa đơn điện tử.',
        ]);

        $order = Order::findOrFail($request->input('order_id'));

        // Kiểm tra xem đơn hàng đã có hóa đơn chưa
        if ($order->invoice()->exists()) {
            return back()->with('error', "Đơn hàng #{$order->order_code} đã có hóa đơn trong hệ thống.");
        }

        $invoice = $this->invoiceService->createInvoiceForOrder($order, $request->all(), $request->user());

        if ($request->boolean('issue_now')) {
            $this->invoiceService->issueInvoice($invoice, $request->user());

            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('success', "Đã tạo và phát hành hóa đơn số [{$invoice->invoice_number}] thành công.");
        }

        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Đã lưu yêu cầu hóa đơn thành công (Chờ duyệt phát hành).');
    }

    /**
     * Phát hành hóa đơn điện tử chính thức.
     */
    public function issue(Request $request, int $id): RedirectResponse
    {
        $invoice = Invoice::findOrFail($id);
        $this->invoiceService->issueInvoice($invoice, $request->user());

        return back()->with('success', "Đã phát hành thành công hóa đơn số [{$invoice->invoice_number}].");
    }

    /**
     * Hủy hóa đơn điện tử.
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $invoice = Invoice::findOrFail($id);
        $reason = (string) $request->input('reason', '');

        $this->invoiceService->cancelInvoice($invoice, $reason, $request->user());

        return back()->with('success', "Đã hủy hóa đơn số [{$invoice->invoice_number}] thành công.");
    }

    /**
     * Trang in phiếu thu / Hóa đơn điện tử Quiet Luxury khổ A4.
     */
    public function print(int $id): View
    {
        $invoice = Invoice::with(['order.user', 'order.plan', 'issuer'])->findOrFail($id);

        return view('admin.pages.invoices.print', compact('invoice'));
    }

    /**
     * Xuất danh sách hóa đơn ra tệp CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        return $this->invoiceService->exportCsv($request);
    }
}
