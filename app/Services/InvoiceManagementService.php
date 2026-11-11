<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceManagementService
{
    public function __construct(
        protected EmailTemplateService $emailTemplateService
    ) {}

    /**
     * Sinh số hóa đơn điện tử tự động tăng dần theo năm: MH-{Y}-{00001}.
     */
    public function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = "MH-{$year}-";

        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $lastInvoice || ! $lastInvoice->invoice_number) {
            $nextSequence = 1;
        } else {
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastNum = isset($parts[2]) ? (int) $parts[2] : 0;
            $nextSequence = $lastNum + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Tạo hóa đơn gắn liền với một đơn hàng.
     */
    public function createInvoiceForOrder(Order $order, array $data, ?User $admin = null): Invoice
    {
        $vatRate = (int) ($data['vat_rate'] ?? 8); // Thuế suất mặc định 8% theo quy định hiện hành
        $totalAmount = (int) ($data['total_amount'] ?? $order->amount);

        // Bóc tách tiền trước thuế và tiền thuế VAT
        if ($vatRate > 0) {
            $subtotal = (int) round($totalAmount / (1 + ($vatRate / 100)));
            $vatAmount = $totalAmount - $subtotal;
        } else {
            $subtotal = $totalAmount;
            $vatAmount = 0;
        }

        $customerName = trim((string) ($data['customer_name'] ?? ($order->user->name ?? 'Khách hàng Mây Hẹn')));
        $customerEmail = trim((string) ($data['customer_email'] ?? ($order->user->email ?? '')));
        $customerPhone = trim((string) ($data['customer_phone'] ?? ''));
        $companyName = trim((string) ($data['company_name'] ?? ''));
        $taxId = trim((string) ($data['tax_id'] ?? ''));
        $companyAddress = trim((string) ($data['company_address'] ?? ''));
        $notes = trim((string) ($data['notes'] ?? ''));

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'company_name' => $companyName,
            'tax_id' => $taxId,
            'company_address' => $companyAddress,
            'subtotal_amount' => $subtotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'status' => Invoice::STATUS_PENDING,
            'payment_method' => $order->payment_method ?? 'vietqr',
            'notes' => $notes,
            'issued_by' => $admin?->id ?? auth()->id(),
        ]);

        AuditLogger::log(
            'INVOICE_CREATED',
            Invoice::class,
            $invoice->id,
            "Tạo yêu cầu hóa đơn cho đơn hàng #{$order->order_code} ({$invoice->formatted_total})",
            ['order_id' => $order->id, 'total' => $totalAmount, 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return $invoice;
    }

    /**
     * Phát hành hóa đơn điện tử chính thức (Issue E-Invoice).
     */
    public function issueInvoice(Invoice $invoice, ?User $admin = null): Invoice
    {
        if (! $invoice->invoice_number) {
            $invoice->invoice_number = $this->generateInvoiceNumber();
        }

        $invoice->status = Invoice::STATUS_ISSUED;
        $invoice->issued_at = now();
        $invoice->issued_by = $admin?->id ?? auth()->id();
        $invoice->save();

        // Gửi email thông báo phát hành hóa đơn cho khách nếu có email
        if (! empty($invoice->customer_email)) {
            $template = $this->emailTemplateService->getTemplateByCode('order_success');
            if ($template) {
                $tokens = [
                    'order_code' => $invoice->order->order_code ?? '',
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->customer_name,
                    'total_amount' => $invoice->formatted_total,
                ];
                $this->emailTemplateService->sendTestEmail($template, $invoice->customer_email, $tokens);
            }
        }

        AuditLogger::log(
            'INVOICE_ISSUED',
            Invoice::class,
            $invoice->id,
            "Phát hành hóa đơn điện tử số [{$invoice->invoice_number}] ({$invoice->formatted_total})",
            ['invoice_number' => $invoice->invoice_number, 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return $invoice;
    }

    /**
     * Hủy hóa đơn điện tử kèm lý do kiểm toán.
     */
    public function cancelInvoice(Invoice $invoice, string $reason = '', ?User $admin = null): Invoice
    {
        $invoice->status = Invoice::STATUS_CANCELLED;
        if ($reason) {
            $invoice->notes = ($invoice->notes ? $invoice->notes."\n" : '').'[HỦY HÓA ĐƠN]: '.$reason;
        }
        $invoice->save();

        AuditLogger::log(
            'INVOICE_CANCELLED',
            Invoice::class,
            $invoice->id,
            "Hủy hóa đơn [{$invoice->invoice_number}] - Lý do: {$reason}",
            ['reason' => $reason, 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return $invoice;
    }

    /**
     * Lấy 4 chỉ số KPI tổng quan về hóa đơn & chứng từ tài chính.
     *
     * @return array{total_invoices: int, issued_invoices: int, pending_invoices: int, total_issued_amount: int, total_vat_amount: int}
     */
    public function getInvoiceKpis(): array
    {
        $all = Invoice::all();
        $issued = $all->where('status', Invoice::STATUS_ISSUED);

        return [
            'total_invoices' => $all->count(),
            'issued_invoices' => $issued->count(),
            'pending_invoices' => $all->where('status', Invoice::STATUS_PENDING)->count(),
            'total_issued_amount' => (int) $issued->sum('total_amount'),
            'total_vat_amount' => (int) $issued->sum('vat_amount'),
        ];
    }

    /**
     * Lọc danh sách hóa đơn theo từ khóa và trạng thái.
     */
    public function getFilteredInvoices(Request $request): LengthAwarePaginator
    {
        $query = Invoice::with(['order.user', 'order.plan', 'issuer'])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $keyword = trim((string) $request->input('search'));
            $query->where(function ($q) use ($keyword) {
                $q->where('invoice_number', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_email', 'like', "%{$keyword}%")
                    ->orWhere('company_name', 'like', "%{$keyword}%")
                    ->orWhere('tax_id', 'like', "%{$keyword}%")
                    ->orWhereHas('order', function ($oq) use ($keyword) {
                        $oq->where('order_code', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        return $query->paginate(15);
    }

    /**
     * Xuất danh sách hóa đơn ra tệp CSV kèm UTF-8 BOM.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $invoices = Invoice::with(['order.plan', 'issuer'])->orderBy('id', 'desc')->get();
        $filename = 'mayhen_invoices_'.date('Ymd_His').'.csv';

        AuditLogger::log(
            'INVOICES_EXPORTED',
            Invoice::class,
            0,
            "Xuất danh sách hóa đơn ({$invoices->count()} chứng từ) ra file CSV",
            ['count' => $invoices->count(), 'admin_id' => auth()->id()]
        );

        return response()->streamDownload(function () use ($invoices) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Số Hóa Đơn',
                'Mã Đơn Hàng',
                'Khách Hàng',
                'Email',
                'Số Điện Thoại',
                'Tên Công Ty',
                'Mã Số Thuế',
                'Địa Chỉ Công Ty',
                'Tiền Trước Thuế (VND)',
                'Thuế Suất VAT (%)',
                'Tiền Thuế VAT (VND)',
                'Tổng Tiền Thanh Toán (VND)',
                'Trạng Thái',
                'Hình Thức Thanh Toán',
                'Ngày Phát Hành',
                'Ghi Chú',
            ]);

            foreach ($invoices as $inv) {
                fputcsv($handle, [
                    $inv->id,
                    $inv->invoice_number ?? 'Chưa phát hành',
                    $inv->order->order_code ?? '',
                    $inv->customer_name,
                    $inv->customer_email,
                    $inv->customer_phone,
                    $inv->company_name,
                    $inv->tax_id,
                    $inv->company_address,
                    $inv->subtotal_amount,
                    $inv->vat_rate,
                    $inv->vat_amount,
                    $inv->total_amount,
                    $inv->status_label,
                    strtoupper($inv->payment_method),
                    $inv->issued_at ? Carbon::parse($inv->issued_at)->format('d/m/Y H:i') : '',
                    $inv->notes,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
