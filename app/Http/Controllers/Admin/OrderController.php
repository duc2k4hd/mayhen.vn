<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Plan;
use App\Services\AuditLogger;
use App\Services\CsvSanitizerService;
use App\Services\OrderRefundService;
use App\Services\OrderSettlementService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /**
     * Display all orders with deep filtering, KPIs, and search.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $planId = $request->input('plan_id');
        $paymentMethod = $request->input('payment_method');
        $dateRange = $request->input('date_range');

        $query = Order::query()->with([
            'user:id,name,email,phone',
            'plan:id,slug,name,price,duration_days',
            'invitation:id,slug,wedding_id,is_published,expires_at',
            'invitation.wedding:id,groom_name,bride_name',
        ]);

        // 1. Text Search: Order code, Customer info
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Status Filter
        if (! empty($status)) {
            $query->where('status', $status);
        }

        // 3. Plan Filter
        if (! empty($planId)) {
            $query->where('plan_id', (int) $planId);
        }

        // 4. Payment Method Filter
        if (! empty($paymentMethod)) {
            $query->where('payment_method', $paymentMethod);
        }

        // 5. Date Range Filter
        if ($dateRange === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateRange === 'this_week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($dateRange === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
        }

        $orders = $query->latest('id')->paginate(15)->withQueryString();

        // Summary KPI Metrics
        $totalRevenue = Order::where('status', 'paid')->sum('amount');
        $paidCount = Order::where('status', 'paid')->count();
        $pendingCount = Order::where('status', 'pending')->count();
        $cancelledCount = Order::whereIn('status', ['cancelled', 'failed'])->count();
        $refundedCount = Order::where('status', 'refunded')->count();

        $plans = Plan::select('id', 'name')->get();

        return view('admin.pages.orders.index', [
            'orders' => $orders,
            'plans' => $plans,
            'currentSearch' => $search,
            'currentStatus' => $status,
            'currentPlanId' => $planId,
            'currentPaymentMethod' => $paymentMethod,
            'currentDateRange' => $dateRange,
            'totalRevenue' => $totalRevenue,
            'paidCount' => $paidCount,
            'pendingCount' => $pendingCount,
            'cancelledCount' => $cancelledCount,
            'refundedCount' => $refundedCount,
        ]);
    }

    /**
     * Export orders list as UTF-8 CSV with BOM for Excel.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $planId = $request->input('plan_id');
        $paymentMethod = $request->input('payment_method');
        $dateRange = $request->input('date_range');

        $query = Order::query()->with([
            'user:id,name,email,phone',
            'plan:id,slug,name,price,duration_days',
            'invitation:id,slug,wedding_id,is_published,expires_at',
            'invitation.wedding:id,groom_name,bride_name',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($status)) {
            $query->where('status', $status);
        }

        if (! empty($planId)) {
            $query->where('plan_id', (int) $planId);
        }

        if (! empty($paymentMethod)) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($dateRange === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateRange === 'this_week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($dateRange === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
        }

        $orders = $query->latest('id')->get();
        $filename = 'bao-cao-don-hang-mayhen-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, [
                'STT',
                'Mã Đơn Hàng',
                'Khách Hàng',
                'Email',
                'Số Điện Thoại',
                'Gói Dịch Vụ',
                'Số Tiền (VNĐ)',
                'Cổng Thanh Toán',
                'Mã Thiệp Liên Kết',
                'Cặp Đôi Hôn Lễ',
                'Trạng Thái',
                'Thời Gian Tạo',
                'Thời Gian Thanh Toán',
            ]);

            foreach ($orders as $idx => $order) {
                $statusLabel = match ($order->status) {
                    'paid' => 'Đã thanh toán',
                    'pending' => 'Chờ thanh toán',
                    'refunded' => 'Đã hoàn tiền',
                    'cancelled' => 'Đã hủy',
                    'failed' => 'Thất bại',
                    default => $order->status,
                };

                $couple = ($order->invitation && $order->invitation->wedding)
                    ? "{$order->invitation->wedding->groom_name} & {$order->invitation->wedding->bride_name}"
                    : '';

                fputcsv($file, CsvSanitizerService::row([
                    $idx + 1,
                    $order->order_code,
                    $order->user?->name ?? '—',
                    $order->user?->email ?? '',
                    $order->user?->phone ?? '',
                    $order->plan?->name ?? 'Gói tùy chỉnh',
                    number_format($order->amount, 0, ',', '.'),
                    strtoupper($order->payment_method ?? 'VIETQR'),
                    $order->invitation?->slug ?? '',
                    $couple,
                    $statusLabel,
                    $order->created_at ? $order->created_at->format('d/m/Y H:i:s') : '',
                    $order->paid_at ? $order->paid_at->format('d/m/Y H:i:s') : '',
                ]));
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Display detailed order view.
     */
    public function show(int $id): View
    {
        $order = Order::with([
            'user',
            'plan',
            'invitation.wedding',
            'invitation.template',
            'reconciliations.creator',
            'reconciliations.approver',
        ])->findOrFail($id);

        return view('admin.pages.orders.show', [
            'order' => $order,
            'user' => $order->user,
            'plan' => $order->plan,
            'invitation' => $order->invitation,
            'reconciliations' => $order->reconciliations->sortByDesc('id'),
        ]);
    }

    /**
     * Manually approve a pending order with strict financial evidence and Dual Approval support (NXT-01).
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        if (! $request->filled('reconciliation_note')) {
            $fallbackNote = $request->input('notes') ?? $request->input('note') ?? $request->input('reason');
            if ($fallbackNote) {
                $request->merge(['reconciliation_note' => $fallbackNote]);
            }
        }

        if (! $request->filled('reference_code')) {
            $fallbackRef = $request->input('transaction_id') ?? $request->input('ref_code');
            if ($fallbackRef) {
                $request->merge(['reference_code' => $fallbackRef]);
            }
        }

        $request->validate([
            'reference_code' => ['required', 'string', 'min:4', 'max:100'],
            'reconciliation_note' => ['required', 'string', 'min:6', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:64'],
        ], [
            'reference_code.required' => 'Mã tham chiếu / mã giao dịch ngân hàng là bắt buộc để đảm bảo tính toàn vẹn tài chính.',
            'reference_code.min' => 'Mã tham chiếu ngân hàng phải có tối thiểu 4 ký tự.',
            'reconciliation_note.required' => 'Lý do đối soát chứng từ là bắt buộc đối với phê duyệt thủ công.',
            'reconciliation_note.min' => 'Lý do đối soát phải có tối thiểu 6 ký tự để phục vụ kiểm toán.',
        ]);

        $outcome = app(OrderSettlementService::class)->approveManually(
            $id,
            Auth::user(),
            (string) $request->input('reference_code'),
            (string) $request->input('reconciliation_note'),
            trim((string) $request->input('bank_name')) ?: null,
            trim((string) $request->input('bank_account_number')) ?: null,
            $request->boolean('dual_approval', false)
        );

        return back()->with($outcome['flash_type'], $outcome['message']);
    }

    /**
     * Super Admin phê duyệt đề xuất đối soát từ Maker (Dual Approval - NXT-01).
     */
    public function confirmApproval(Request $request, int $reconciliationId): RedirectResponse
    {
        $outcome = app(OrderSettlementService::class)->confirmReconciliation(
            $reconciliationId,
            Auth::user(),
            (string) $request->input('review_note', 'Super Admin phê duyệt hợp lệ')
        );

        return back()->with($outcome['flash_type'], $outcome['message']);
    }

    /**
     * Super Admin từ chối đề xuất đối soát từ Maker.
     */
    public function rejectApproval(Request $request, int $reconciliationId): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:4', 'max:255'],
        ]);

        try {
            $reconciliation = app(OrderRefundService::class)->rejectReconciliation(
                $reconciliationId,
                (string) $request->input('reason'),
                Auth::user()
            );

            return back()->with('warning', "Đã từ chối đề xuất đối soát cho đơn #{$reconciliation->order->order_code}.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('info', $e->getMessage());
        }
    }

    /**
     * Tạo đề nghị hoàn tiền có chứng từ và thông tin thụ hưởng (NXT-01).
     */
    public function requestRefund(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:6', 'max:255'],
            'customer_bank_name' => ['required', 'string', 'max:100'],
            'customer_bank_account' => ['required', 'string', 'max:64'],
            'customer_bank_holder' => ['required', 'string', 'max:100'],
            'refund_amount' => ['nullable', 'integer', 'min:1000'],
        ], [
            'reason.required' => 'Vui lòng cung cấp lý do đề nghị hoàn tiền.',
            'customer_bank_name.required' => 'Tên ngân hàng nhận lại tiền của khách là bắt buộc.',
            'customer_bank_account.required' => 'Số tài khoản nhận lại tiền của khách là bắt buộc.',
            'customer_bank_holder.required' => 'Tên chủ tài khoản nhận lại tiền là bắt buộc.',
        ]);

        $order = Order::findOrFail($id);

        try {
            app(OrderRefundService::class)->createRefundRequest($order, [
                'amount' => $request->input('refund_amount') ?: $order->amount,
                'reason' => (string) $request->input('reason'),
                'reference_code' => 'REQ-REF-'.$order->order_code,
                'bank_name' => trim((string) $request->input('customer_bank_name')),
                'bank_account_number' => trim((string) $request->input('customer_bank_account')),
                'bank_account_holder' => trim((string) $request->input('customer_bank_holder')),
            ], Auth::user());

            return back()->with('success', "Đã tạo hồ sơ đề nghị hoàn tiền cho đơn #{$order->order_code}. Vui lòng thực hiện chuyển khoản và xác nhận chứng từ chi trả.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Xác nhận đã chuyển khoản ngân hàng hoàn tiền thật và thu hồi dịch vụ (NXT-01).
     */
    public function confirmRefundPayout(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'payout_reference_code' => ['required', 'string', 'min:4', 'max:100'],
            'payout_note' => ['nullable', 'string', 'max:255'],
        ], [
            'payout_reference_code.required' => 'Mã giao dịch chuyển khoản hoàn tiền thực tế là bắt buộc để chứng minh tiền đã hoàn về tài khoản khách.',
            'payout_reference_code.min' => 'Mã giao dịch chuyển khoản phải có tối thiểu 4 ký tự.',
        ]);

        try {
            $order = app(OrderRefundService::class)->confirmRefundPayout(
                $id,
                (string) $request->input('payout_reference_code'),
                (string) ($request->input('payout_note') ?: 'Đã chuyển tiền hoàn qua Internet Banking'),
                Auth::user()
            );

            return back()->with('success', "Đã ghi nhận chứng từ và hoàn tất hoàn tiền cho đơn hàng #{$order->order_code}.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hoàn tiền cho đơn hàng có chứng từ đối soát (tương thích ngược và an toàn tài chính).
     */
    public function refund(Request $request, int $id): RedirectResponse
    {
        if ($request->filled('payout_reference_code')) {
            return $this->confirmRefundPayout($request, $id);
        }

        if ($request->filled('customer_bank_account')) {
            return $this->requestRefund($request, $id);
        }

        try {
            $order = app(OrderRefundService::class)->directRefund(
                $id,
                (string) $request->input('reason', 'Hoàn tiền theo yêu cầu khách hàng hoặc chính sách bảo hành'),
                $request->input('reference_code') ? (string) $request->input('reference_code') : null,
                $request->filled('refund_amount') ? (int) $request->input('refund_amount') : null,
                Auth::user()
            );

            return back()->with('success', "Đã hoàn tiền đơn hàng #{$order->order_code} và cập nhật trạng thái lưu trữ thành công.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a pending order.
     */
    public function cancel(int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->isPaid()) {
            return back()->with('error', 'Không thể hủy đơn hàng đã thanh toán thành công.');
        }

        $order->update(['status' => 'cancelled']);

        AuditLogger::log(
            'cancel_order',
            'Order',
            $order->id,
            "Hủy đơn hàng #{$order->order_code}",
            ['order_code' => $order->order_code]
        );

        return back()->with('success', "Đã hủy đơn hàng #{$order->order_code}.");
    }

    /**
     * Display payment gateway webhook audit logs.
     */
    public function webhooks(): View
    {
        $logPath = storage_path('logs/payment_webhooks.jsonl');
        $logs = [];

        if (File::exists($logPath)) {
            $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                $lines = array_reverse($lines);
                $lines = array_slice($lines, 0, 100);
                foreach ($lines as $line) {
                    $decoded = json_decode($line, true);
                    if (is_array($decoded)) {
                        $logs[] = $decoded;
                    }
                }
            }
        }

        return view('admin.pages.orders.webhooks', [
            'logs' => $logs,
        ]);
    }
}
