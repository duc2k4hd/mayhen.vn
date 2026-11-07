<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Plan;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    'cancelled' => 'Đã hủy',
                    'failed' => 'Thất bại',
                    default => $order->status,
                };

                $couple = ($order->invitation && $order->invitation->wedding)
                    ? "{$order->invitation->wedding->groom_name} & {$order->invitation->wedding->bride_name}"
                    : '';

                fputcsv($file, [
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
                ]);
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
        ])->findOrFail($id);

        return view('admin.pages.orders.show', [
            'order' => $order,
            'user' => $order->user,
            'plan' => $order->plan,
            'invitation' => $order->invitation,
        ]);
    }

    /**
     * Manually approve a pending order and activate the wedding invitation plan.
     */
    public function approve(int $id): RedirectResponse
    {
        $order = Order::with(['plan', 'invitation'])->findOrFail($id);

        if ($order->isPaid()) {
            return back()->with('info', "Đơn hàng #{$order->order_code} đã được thanh toán trước đó.");
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            if ($order->invitation) {
                $duration = $order->plan ? $order->plan->duration_days : 365;
                $order->invitation->update([
                    'is_published' => true,
                    'published_at' => $order->invitation->published_at ?? now(),
                    'expires_at' => now()->addDays($duration),
                ]);
            }
        });

        AuditLogger::log(
            'approve_order',
            'Order',
            $order->id,
            "Duyệt thủ công đơn hàng #{$order->order_code} số tiền ".number_format($order->amount, 0, ',', '.').'đ',
            ['order_code' => $order->order_code, 'amount' => $order->amount]
        );

        return back()->with('success', "Đã duyệt thanh toán thủ công cho đơn hàng #{$order->order_code} và kích hoạt bản quyền thiệp cưới thành công.");
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
