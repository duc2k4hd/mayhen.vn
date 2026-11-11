<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderReconciliation;
use App\Models\PaymentWebhookLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReconciliationService
{
    /**
     * Lấy 4 Thẻ KPI Chỉ Số Đối Soát & Tài Chính.
     */
    public function getReconciliationKpis(): array
    {
        $pendingCasesCount = OrderReconciliation::pending()->count();

        $amountMismatchCount = OrderReconciliation::where('action_type', 'amount_mismatch_resolution')
            ->where('status', 'pending')
            ->count();

        $refundPendingAmount = (int) OrderReconciliation::where('action_type', 'refund_request')
            ->where('status', 'pending')
            ->sum('amount');

        $startOfMonth = Carbon::now()->startOfMonth();
        $reconciledThisMonthCount = OrderReconciliation::whereIn('status', ['approved', 'completed'])
            ->where('reconciled_at', '>=', $startOfMonth)
            ->count();

        return [
            'pending_cases_count' => $pendingCasesCount,
            'amount_mismatch_count' => $amountMismatchCount,
            'refund_pending_amount' => $refundPendingAmount,
            'reconciled_this_month_count' => $reconciledThisMonthCount,
        ];
    }

    /**
     * Khởi tạo phiếu đối soát khớp lệnh thủ công cho đơn hàng (PAY-01).
     */
    public function createManualReconciliation(array $data, ?User $admin = null): OrderReconciliation
    {
        $order = Order::findOrFail($data['order_id']);

        $actionType = $data['action_type'] ?? 'manual_approval';
        $amount = (int) ($data['amount'] ?? $order->total_amount);

        $reconciliation = OrderReconciliation::create([
            'order_id' => $order->id,
            'creator_id' => $admin?->id,
            'action_type' => $actionType,
            'status' => 'pending',
            'amount' => $amount,
            'reference_code' => trim($data['reference_code']),
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'bank_account_holder' => $data['bank_account_holder'] ?? null,
            'reconciliation_note' => $data['reconciliation_note'] ?? null,
            'metadata' => [
                'order_code' => $order->order_code,
                'customer_email' => $order->user?->email,
                'plan_name' => $order->plan?->name,
                'created_at_iso' => now()->toIso8601String(),
            ],
        ]);

        $order->update(['reconciliation_status' => 'pending']);

        AuditLogger::log(
            'FINANCIAL_RECONCILIATION_CREATED',
            'order_reconciliation',
            $reconciliation->id,
            "Khởi tạo phiếu đối soát tài chính cho đơn hàng #{$order->order_code} (Số tiền: ".number_format($amount).' đ).',
            [
                'order_id' => $order->id,
                'amount' => $amount,
                'reference_code' => $reconciliation->reference_code,
                'action_type' => $actionType,
            ]
        );

        return $reconciliation;
    }

    /**
     * Phê duyệt phiếu đối soát (Maker-Checker quy trình 2 bước).
     * Tự động kích hoạt thanh toán đơn hàng và cấp quyền gói thiệp nếu là khớp lệnh.
     */
    public function approveReconciliation(int $id, ?string $note = null, ?User $admin = null): OrderReconciliation
    {
        return DB::transaction(function () use ($id, $note, $admin) {
            $reconciliation = OrderReconciliation::with('order')->findOrFail($id);

            $reconciliation->update([
                'status' => 'approved',
                'approver_id' => $admin?->id,
                'review_note' => $note,
                'reconciled_at' => Carbon::now(),
            ]);

            $order = $reconciliation->order;

            if ($order && in_array($reconciliation->action_type, ['manual_approval', 'amount_mismatch_resolution'], true)) {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => Carbon::now(),
                    'reconciliation_status' => 'approved',
                ]);

                AuditLogger::log(
                    'ORDER_MANUALLY_PAID_VIA_RECONCILIATION',
                    'order',
                    $order->id,
                    "Đơn hàng #{$order->order_code} được phê duyệt khớp lệnh đối soát thành công bởi {$admin?->name}.",
                    ['reconciliation_id' => $reconciliation->id, 'amount' => $reconciliation->amount]
                );
            }

            AuditLogger::log(
                'FINANCIAL_RECONCILIATION_APPROVED',
                'order_reconciliation',
                $reconciliation->id,
                "Phê duyệt phiếu đối soát #{$reconciliation->id} (Mã GD: {$reconciliation->reference_code}).",
                ['approver_id' => $admin?->id, 'note' => $note]
            );

            return $reconciliation;
        });
    }

    /**
     * Từ chối phiếu đối soát.
     */
    public function rejectReconciliation(int $id, string $reason, ?User $admin = null): OrderReconciliation
    {
        $reconciliation = OrderReconciliation::with('order')->findOrFail($id);

        $reconciliation->update([
            'status' => 'rejected',
            'approver_id' => $admin?->id,
            'review_note' => $reason,
            'reconciled_at' => Carbon::now(),
        ]);

        if ($reconciliation->order) {
            $reconciliation->order->update(['reconciliation_status' => 'rejected']);
        }

        AuditLogger::log(
            'FINANCIAL_RECONCILIATION_REJECTED',
            'order_reconciliation',
            $reconciliation->id,
            "Từ chối phiếu đối soát #{$reconciliation->id}. Lý do: {$reason}.",
            ['approver_id' => $admin?->id, 'reason' => $reason]
        );

        return $reconciliation;
    }

    /**
     * Xác nhận chi trả hoàn tiền thành công (Payout Completed).
     */
    public function confirmRefundPayout(int $id, ?string $note = null, ?User $admin = null): OrderReconciliation
    {
        return DB::transaction(function () use ($id, $note, $admin) {
            $reconciliation = OrderReconciliation::with('order')->findOrFail($id);

            $reconciliation->update([
                'status' => 'completed',
                'approver_id' => $admin?->id,
                'review_note' => $note,
                'reconciled_at' => Carbon::now(),
            ]);

            $order = $reconciliation->order;
            if ($order) {
                $order->update([
                    'status' => 'refunded',
                    'refunded_at' => Carbon::now(),
                    'refund_amount' => $reconciliation->amount,
                    'reconciliation_status' => 'refunded',
                ]);
            }

            AuditLogger::log(
                'REFUND_PAYOUT_COMPLETED',
                'order_reconciliation',
                $reconciliation->id,
                'Xác nhận đã hoàn tiền '.number_format($reconciliation->amount)." đ cho đơn hàng #{$order?->order_code}.",
                ['amount' => $reconciliation->amount, 'bank' => $reconciliation->bank_name]
            );

            return $reconciliation;
        });
    }

    /**
     * Ghi nhận lượt gọi Webhook thanh toán từ cổng trung gian.
     */
    public function recordInboundWebhook(
        string $gateway,
        array $payload,
        string $status = 'success',
        ?string $error = null,
        ?string $ip = null
    ): PaymentWebhookLog {
        return PaymentWebhookLog::create([
            'gateway' => $gateway,
            'transaction_id' => $payload['transaction_id'] ?? ($payload['id'] ?? null),
            'order_code' => $payload['order_code'] ?? ($payload['content'] ?? null),
            'amount' => (int) ($payload['amount'] ?? 0),
            'status' => $status,
            'error_reason' => $error,
            'raw_payload' => $payload,
            'ip_address' => $ip,
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * Danh sách phân trang Phiếu Đối Soát có tìm kiếm và lọc.
     */
    public function getFilteredReconciliations(
        ?string $search = null,
        ?string $status = null,
        ?string $actionType = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = OrderReconciliation::with(['order.user', 'creator', 'approver'])->latest('id');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_code', 'like', "%{$search}%")
                    ->orWhere('bank_account_holder', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_code', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($status)) {
            $query->where('status', $status);
        }

        if (! empty($actionType)) {
            $query->where('action_type', $actionType);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Danh sách phân trang Nhật Ký Webhook Thanh Toán Inbound.
     */
    public function getFilteredWebhookLogs(
        ?string $gateway = null,
        ?string $status = null,
        ?string $search = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = PaymentWebhookLog::latest('id');

        if (! empty($gateway)) {
            $query->where('gateway', $gateway);
        }

        if (! empty($status)) {
            $query->where('status', $status);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('order_code', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Xuất tệp báo cáo đối soát CSV chuẩn UTF-8 BOM cho Kế toán.
     */
    public function exportCsv(?string $status = null): StreamedResponse
    {
        $query = OrderReconciliation::with(['order.user', 'creator', 'approver'])->latest('id');
        if (! empty($status)) {
            $query->where('status', $status);
        }
        $records = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="so_doi_soat_giao_dich_'.now()->format('Ymd_His').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            // Ghi UTF-8 BOM để Excel hiển thị tiếng Việt hoàn hảo
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Mã Đơn Hàng',
                'Loại Nghiệp Vụ',
                'Trạng Thái',
                'Số Tiền (VNĐ)',
                'Mã Tham Chiếu / Giao Dịch',
                'Ngân Hàng',
                'Số Tài Khoản',
                'Chủ Tài Khoản',
                'Người Lập',
                'Người Duyệt',
                'Ngày Lập',
                'Ngày Đối Soát',
                'Ghi Chú Kế Toán',
            ]);

            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->id,
                    $r->order?->order_code ?? 'N/A',
                    $r->action_label,
                    $r->status_label,
                    $r->amount,
                    $r->reference_code,
                    $r->bank_name ?? '',
                    $r->bank_account_number ?? '',
                    $r->bank_account_holder ?? '',
                    $r->creator?->name ?? 'Hệ thống',
                    $r->approver?->name ?? '',
                    $r->created_at->format('d/m/Y H:i'),
                    $r->reconciled_at ? $r->reconciled_at->format('d/m/Y H:i') : '',
                    $r->reconciliation_note ?? $r->review_note ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
