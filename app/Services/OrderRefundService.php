<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderReconciliation;
use App\Models\PartnerCommission;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderRefundService
{
    /**
     * Thống kê 4 thẻ KPI chỉ số hoàn tiền & tranh chấp.
     *
     * @return array{total_refunded_amount: int, completed_count: int, pending_count: int, refund_rate: float}
     */
    public function getRefundKpis(): array
    {
        $totalRefundedAmount = (int) Order::where('status', 'refunded')->sum('refund_amount');
        if ($totalRefundedAmount === 0) {
            $totalRefundedAmount = (int) OrderReconciliation::where('action_type', 'refund_payout')
                ->whereIn('status', ['approved', 'completed'])
                ->sum('amount');
        }

        $completedCount = Order::where('status', 'refunded')->count();
        $pendingCount = OrderReconciliation::whereIn('action_type', ['refund_request', 'refund_payout'])
            ->where('status', 'pending')
            ->count();

        $totalRevenue = (int) Order::whereIn('status', ['paid', 'refunded'])->sum('amount');
        $refundRate = $totalRevenue > 0 ? round(($totalRefundedAmount / $totalRevenue) * 100, 2) : 0.0;

        return [
            'total_refunded_amount' => $totalRefundedAmount,
            'completed_count' => $completedCount,
            'pending_count' => $pendingCount,
            'refund_rate' => $refundRate,
        ];
    }

    /**
     * Lấy danh sách yêu cầu hoàn tiền và lịch sử phân trang.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFilteredRefundRequests(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = OrderReconciliation::with(['order.user', 'order.plan', 'order.invitation', 'creator', 'approver'])
            ->whereIn('action_type', ['refund_request', 'refund_payout'])
            ->latest('id');

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('reference_code', 'like', "%{$search}%")
                    ->orWhere('reconciliation_note', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_code', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($uq) use ($search) {
                                $uq->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            });
                    });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Tạo phiếu yêu cầu hoàn tiền mới (Two-man rule: Bước 1 CSKH đề xuất).
     *
     * @param  array<string, mixed>  $data
     */
    public function createRefundRequest(Order $order, array $data, User $creator): OrderReconciliation
    {
        if (! $order->isPaid() && $order->status !== 'refunded') {
            throw new \InvalidArgumentException('Chỉ có thể yêu cầu hoàn tiền cho đơn hàng đã thanh toán thành công.');
        }

        $existingPending = OrderReconciliation::where('order_id', $order->id)
            ->whereIn('action_type', ['refund_request', 'refund_payout'])
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            throw new \InvalidArgumentException('Đơn hàng này đang có một yêu cầu hoàn tiền chờ thẩm định.');
        }

        $amount = isset($data['amount']) ? (int) $data['amount'] : (int) $order->amount;
        if ($amount <= 0 || $amount > $order->amount) {
            $amount = (int) $order->amount;
        }

        return DB::transaction(function () use ($order, $data, $creator, $amount) {
            $refCode = 'REF-REQ-'.strtoupper(bin2hex(random_bytes(4)));

            $reconciliation = OrderReconciliation::create([
                'order_id' => $order->id,
                'creator_id' => $creator->id,
                'action_type' => 'refund_request',
                'status' => 'pending',
                'amount' => $amount,
                'reference_code' => $refCode,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'bank_account_holder' => $data['bank_account_holder'] ?? null,
                'reconciliation_note' => $data['reason'] ?? 'Khách hàng yêu cầu hoàn tiền theo chính sách',
                'metadata' => [
                    'customer_contact' => $data['customer_contact'] ?? ($order->user?->phone ?? $order->user?->email),
                    'hours_since_payment' => $order->paid_at ? (int) now()->diffInHours($order->paid_at) : null,
                    'policy_clause' => $data['policy_clause'] ?? '24h_pre_publish',
                ],
            ]);

            $order->update([
                'reconciliation_status' => 'refund_pending',
            ]);

            AuditLogger::log(
                action: 'order.refund_requested',
                targetType: 'order',
                targetId: $order->id,
                description: "Nhân viên {$creator->name} đề xuất hoàn tiền {$amount}đ cho đơn hàng #{$order->order_code}.",
                details: [
                    'order_id' => $order->id,
                    'amount' => $amount,
                    'reference_code' => $refCode,
                    'reason' => $data['reason'] ?? '',
                ]
            );

            return $reconciliation;
        });
    }

    /**
     * Thẩm định & Phê duyệt chi trả hoàn tiền (Two-man rule: Bước 2 Manager duyệt).
     *
     * @param  array<string, mixed>  $data
     */
    public function approveRefund(OrderReconciliation $reconciliation, array $data, User $approver): OrderReconciliation
    {
        if (! $reconciliation->isPending()) {
            throw new \InvalidArgumentException('Yêu cầu hoàn tiền này đã được xử lý trước đó.');
        }

        // Two-man rule: Người duyệt không được là người đề xuất (trừ Super Admin)
        if ($approver->id === $reconciliation->creator_id && ! $approver->isSuperAdmin()) {
            throw new \InvalidArgumentException('Nguyên tắc kiểm soát kép: Người phê duyệt không được trùng với người đề xuất hoàn tiền.');
        }

        $order = $reconciliation->order;
        $payoutRef = trim((string) ($data['payout_reference'] ?? $reconciliation->reference_code));
        $reviewNote = trim((string) ($data['review_note'] ?? 'Đã xác minh và chuyển khoản hoàn tiền thành công.'));

        return DB::transaction(function () use ($reconciliation, $order, $approver, $payoutRef, $reviewNote) {
            $refundAmount = $reconciliation->amount ?: $order->amount;

            // 1. Cập nhật OrderReconciliation
            $reconciliation->update([
                'status' => 'completed',
                'approver_id' => $approver->id,
                'reconciled_at' => now(),
                'reference_code' => $payoutRef,
                'review_note' => $reviewNote,
            ]);

            // 2. Cập nhật Order sang 'refunded'
            $order->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'refund_amount' => $refundAmount,
                'reconciliation_status' => 'refund_approved',
            ]);

            // 3. Hạ cấp quyền thiệp cưới nếu có
            $this->downgradeOrderServices($order);

            // 4. Thu hồi hoa hồng đối tác nếu có
            $this->revertPartnerCommission($order, $approver);

            // 5. Ghi nhật ký kiểm toán
            AuditLogger::log(
                action: 'order.refund_approved',
                targetType: 'order',
                targetId: $order->id,
                description: "Quản lý {$approver->name} đã duyệt hoàn tiền {$refundAmount}đ cho đơn #{$order->order_code}. Mã CK: {$payoutRef}.",
                details: [
                    'order_id' => $order->id,
                    'reconciliation_id' => $reconciliation->id,
                    'amount' => $refundAmount,
                    'payout_reference' => $payoutRef,
                    'approver_id' => $approver->id,
                ]
            );

            return $reconciliation;
        });
    }

    /**
     * Từ chối yêu cầu hoàn tiền kèm lý do minh bạch.
     */
    public function rejectRefund(OrderReconciliation $reconciliation, string $reason, User $reviewer): OrderReconciliation
    {
        if (! $reconciliation->isPending()) {
            throw new \InvalidArgumentException('Yêu cầu hoàn tiền này đã được xử lý trước đó.');
        }

        $order = $reconciliation->order;

        return DB::transaction(function () use ($reconciliation, $order, $reason, $reviewer) {
            $reconciliation->update([
                'status' => 'rejected',
                'approver_id' => $reviewer->id,
                'reconciled_at' => now(),
                'review_note' => $reason,
            ]);

            $order->update([
                'reconciliation_status' => 'refund_rejected',
            ]);

            AuditLogger::log(
                action: 'order.refund_rejected',
                targetType: 'order',
                targetId: $order->id,
                description: "Từ chối yêu cầu hoàn tiền đơn #{$order->order_code} bởi {$reviewer->name}. Lý do: {$reason}",
                details: [
                    'order_id' => $order->id,
                    'reconciliation_id' => $reconciliation->id,
                    'reason' => $reason,
                    'reviewer_id' => $reviewer->id,
                ]
            );

            return $reconciliation;
        });
    }

    /**
     * Hạ cấp an toàn gói thiệp cưới sau khi hoàn tiền.
     */
    protected function downgradeOrderServices(Order $order): void
    {
        $invitation = $order->invitation;
        if (! $invitation) {
            return;
        }

        // Nếu thiệp đã xuất bản, gỡ xuất bản để bảo vệ bản quyền
        if ($invitation->is_published) {
            $invitation->update([
                'is_published' => false,
            ]);
        }
    }

    /**
     * Thu hồi hoa hồng đối tác nếu đơn hàng này phát sinh qua referral.
     */
    protected function revertPartnerCommission(Order $order, User $admin): void
    {
        $commissions = PartnerCommission::where('order_id', $order->id)
            ->where('status', '!=', 'cancelled')
            ->get();

        foreach ($commissions as $comm) {
            $partner = $comm->partner;
            if ($partner && $comm->status === 'credited') {
                // Trừ lại số dư ví nếu đã cộng
                $partner->decrement('wallet_balance', (int) $comm->commission_amount);
            }

            $comm->update([
                'status' => 'cancelled',
                'note' => ($comm->note ? $comm->note.' | ' : '')."Hủy do hoàn tiền đơn hàng #{$order->order_code} bởi {$admin->name}.",
            ]);
        }
    }

    /**
     * Xuất danh sách sổ hoàn tiền ra tệp CSV cho phòng kế toán.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportCsv(array $filters = [], ?User $admin = null): StreamedResponse
    {
        $query = OrderReconciliation::with(['order.user', 'order.plan', 'creator', 'approver'])
            ->whereIn('action_type', ['refund_request', 'refund_payout'])
            ->latest('id');

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        $records = $query->get();

        if ($admin) {
            AuditLogger::log(
                action: 'order.refund_csv_exported',
                targetType: 'system',
                targetId: 0,
                description: "Xuất dữ liệu CSV sổ hoàn tiền ({$records->count()} bản ghi) bởi {$admin->name}.",
            );
        }

        $filename = 'mayhen_so_hoan_tien_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'ID Yêu Cầu',
                'Mã Đơn Hàng',
                'Khách Hàng',
                'Gói Dịch Vụ',
                'Số Tiền Hoàn (VNĐ)',
                'Mã Tham Chiếu / CK',
                'Ngân Hàng Thụ Hưởng',
                'Số Tài Khoản',
                'Chủ Tài Khoản',
                'Trạng Thái',
                'Lý Do Khiếu Nại',
                'Người Đề Xuất',
                'Người Thẩm Định',
                'Thời Điểm Hoàn Tiền',
            ]);

            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->id,
                    $r->order?->order_code ?? 'N/A',
                    $r->order?->user?->name ?? 'N/A',
                    $r->order?->plan?->name ?? 'N/A',
                    number_format($r->amount, 0, '', ''),
                    $r->reference_code,
                    $r->bank_name ?? '',
                    $r->bank_account_number ?? '',
                    $r->bank_account_holder ?? '',
                    $r->status,
                    $r->reconciliation_note ?? '',
                    $r->creator?->name ?? 'N/A',
                    $r->approver?->name ?? 'N/A',
                    $r->reconciled_at ? $r->reconciled_at->format('d/m/Y H:i:s') : '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
