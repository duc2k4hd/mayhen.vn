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
        return DB::transaction(function () use ($order, $data, $creator): OrderReconciliation {
            $lockedOrder = Order::with('user')->lockForUpdate()->findOrFail($order->id);

            if (! $lockedOrder->isPaid()) {
                throw new \InvalidArgumentException('Chỉ có thể yêu cầu hoàn tiền cho đơn hàng đã thanh toán thành công.');
            }

            $existingPending = OrderReconciliation::where('order_id', $lockedOrder->id)
                ->whereIn('action_type', ['refund_request', 'refund_payout'])
                ->where('status', 'pending')
                ->exists();

            if ($existingPending || $lockedOrder->isRefundPending()) {
                throw new \InvalidArgumentException('Đơn hàng này đang có một yêu cầu hoàn tiền chờ thẩm định.');
            }

            $requestedAmount = isset($data['amount']) && $data['amount'] !== '' && $data['amount'] !== null
                ? (int) $data['amount']
                : (int) $lockedOrder->amount;

            if ($requestedAmount <= 0 || $requestedAmount > (int) $lockedOrder->amount) {
                throw new \InvalidArgumentException('Số tiền hoàn không được vượt quá giá trị đơn hàng.');
            }

            $refCode = trim((string) ($data['reference_code'] ?? ''))
                ?: 'REF-REQ-'.strtoupper(bin2hex(random_bytes(4)));

            $reconciliation = OrderReconciliation::create([
                'order_id' => $lockedOrder->id,
                'creator_id' => $creator->id,
                'action_type' => 'refund_request',
                'status' => 'pending',
                'amount' => $requestedAmount,
                'reference_code' => $refCode,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'bank_account_holder' => $data['bank_account_holder'] ?? null,
                'reconciliation_note' => trim((string) ($data['reason'] ?? 'Khách hàng yêu cầu hoàn tiền theo chính sách')),
                'metadata' => [
                    'customer_contact' => $data['customer_contact'] ?? ($lockedOrder->user?->phone ?? $lockedOrder->user?->email),
                    'hours_since_payment' => $lockedOrder->paid_at ? (int) now()->diffInHours($lockedOrder->paid_at) : null,
                    'policy_clause' => $data['policy_clause'] ?? '24h_pre_publish',
                ],
            ]);

            $lockedOrder->update([
                'reconciliation_status' => 'refund_pending',
                'refund_amount' => $requestedAmount,
            ]);

            AuditLogger::log(
                action: 'order.refund_requested',
                targetType: 'order',
                targetId: $lockedOrder->id,
                description: "Nhân viên {$creator->name} đề xuất hoàn tiền {$requestedAmount}đ cho đơn hàng #{$lockedOrder->order_code}.",
                details: [
                    'order_id' => $lockedOrder->id,
                    'amount' => $requestedAmount,
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
        return DB::transaction(function () use ($reconciliation, $data, $approver): OrderReconciliation {
            $lockedRecon = OrderReconciliation::lockForUpdate()->findOrFail($reconciliation->id);

            if (! $lockedRecon->isPending()) {
                throw new \InvalidArgumentException('Yêu cầu hoàn tiền này đã được xử lý trước đó.');
            }

            // Two-man rule: Người duyệt không được là người đề xuất (trừ Super Admin)
            if ($approver->id === $lockedRecon->creator_id && ! $approver->isSuperAdmin()) {
                throw new \InvalidArgumentException('Nguyên tắc kiểm soát kép: Người phê duyệt không được trùng với người đề xuất hoàn tiền.');
            }

            $lockedOrder = Order::with(['invitation', 'plan'])->lockForUpdate()->findOrFail($lockedRecon->order_id);
            $payoutRef = trim((string) ($data['payout_reference'] ?? $lockedRecon->reference_code));
            $reviewNote = trim((string) ($data['review_note'] ?? 'Đã xác minh và chuyển khoản hoàn tiền thành công.'));
            $refundAmount = $lockedRecon->amount ?: $lockedOrder->amount;

            $lockedRecon->update([
                'status' => 'completed',
                'approver_id' => $approver->id,
                'reconciled_at' => now(),
                'reference_code' => $payoutRef,
                'review_note' => $reviewNote,
            ]);

            $lockedOrder->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'refund_amount' => $refundAmount,
                'reconciliation_status' => 'refund_completed',
            ]);

            $this->revokeOrderEntitlementsAndCommissions($lockedOrder, $approver);

            AuditLogger::log(
                action: 'order.refund_approved',
                targetType: 'order',
                targetId: $lockedOrder->id,
                description: "Quản lý {$approver->name} đã duyệt hoàn tiền {$refundAmount}đ cho đơn #{$lockedOrder->order_code}. Mã CK: {$payoutRef}.",
                details: [
                    'order_id' => $lockedOrder->id,
                    'reconciliation_id' => $lockedRecon->id,
                    'amount' => $refundAmount,
                    'payout_reference' => $payoutRef,
                    'approver_id' => $approver->id,
                ]
            );

            return $lockedRecon;
        });
    }

    /**
     * Xác nhận chi trả hoàn tiền thật kèm chứng từ ngân hàng (NXT-01) qua cùng seam hoàn tiền.
     */
    public function confirmRefundPayout(int $orderId, string $payoutRef, string $payoutNote, User $actor): Order
    {
        $payoutRef = trim($payoutRef);
        $payoutNote = trim($payoutNote) ?: 'Đã chuyển tiền hoàn qua Internet Banking';

        return DB::transaction(function () use ($orderId, $payoutRef, $payoutNote, $actor): Order {
            $order = Order::with(['invitation', 'plan'])->lockForUpdate()->findOrFail($orderId);

            if (! $order->isPaid() && ! $order->isRefundPending()) {
                throw new \InvalidArgumentException('Chỉ có thể xác nhận hoàn tiền cho đơn hàng đã thanh toán hoặc đang chờ hoàn tiền.');
            }

            $pendingRequests = OrderReconciliation::where('order_id', $order->id)
                ->where('action_type', 'refund_request')
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            foreach ($pendingRequests as $pendingReq) {
                if ($actor->id === $pendingReq->creator_id && ! $actor->isSuperAdmin()) {
                    throw new \InvalidArgumentException('Nguyên tắc kiểm soát kép: Người phê duyệt không được trùng với người đề xuất hoàn tiền.');
                }
            }

            $refundAmount = $order->refund_amount ?: $order->amount;

            $order->update([
                'status' => 'refunded',
                'reconciliation_status' => 'refund_completed',
                'refunded_at' => now(),
                'refund_amount' => $refundAmount,
            ]);

            $this->revokeOrderEntitlementsAndCommissions($order, $actor);

            OrderReconciliation::create([
                'order_id' => $order->id,
                'creator_id' => $actor->id,
                'approver_id' => $actor->id,
                'action_type' => 'refund_payout',
                'status' => 'completed',
                'amount' => $refundAmount,
                'reference_code' => $payoutRef,
                'reconciliation_note' => $payoutNote,
                'reconciled_at' => now(),
            ]);

            OrderReconciliation::where('order_id', $order->id)
                ->where('action_type', 'refund_request')
                ->where('status', 'pending')
                ->update([
                    'status' => 'completed',
                    'approver_id' => $actor->id,
                    'reconciled_at' => now(),
                    'review_note' => "Đã chi trả thành công với mã GD {$payoutRef}",
                ]);

            AuditLogger::log(
                'refund_order',
                'Order',
                $order->id,
                "Xác nhận đã chuyển khoản hoàn tiền cho đơn #{$order->order_code} số tiền ".number_format($refundAmount, 0, ',', '.')."đ (Mã GD hoàn: {$payoutRef}).",
                ['order_code' => $order->order_code, 'payout_reference' => $payoutRef, 'amount' => $refundAmount]
            );

            return $order;
        });
    }

    /**
     * Hoàn tiền trực tiếp (Super Admin / backward-compatible endpoint) qua cùng seam hoàn tiền.
     */
    public function directRefund(int $orderId, string $reason, ?string $refCode, ?int $refundAmount, User $actor): Order
    {
        $reason = trim($reason) ?: 'Hoàn tiền theo yêu cầu khách hàng hoặc chính sách bảo hành';
        $refCode = trim((string) $refCode) ?: ('REF-PAY-'.now()->format('YmdHis').'-'.strtoupper(bin2hex(random_bytes(2))));

        return DB::transaction(function () use ($orderId, $reason, $refCode, $refundAmount, $actor): Order {
            $order = Order::with(['invitation', 'plan'])->lockForUpdate()->findOrFail($orderId);

            if (! $order->isPaid() && ! $order->isRefundPending()) {
                throw new \InvalidArgumentException('Chỉ có thể hoàn tiền cho đơn hàng đã thanh toán thành công.');
            }

            $finalAmount = ($refundAmount !== null && $refundAmount > 0 && $refundAmount <= $order->amount)
                ? $refundAmount
                : (int) $order->amount;

            $order->update([
                'status' => 'refunded',
                'reconciliation_status' => 'refund_completed',
                'refunded_at' => now(),
                'refund_amount' => $finalAmount,
            ]);

            $this->revokeOrderEntitlementsAndCommissions($order, $actor);

            OrderReconciliation::create([
                'order_id' => $order->id,
                'creator_id' => $actor->id,
                'approver_id' => $actor->id,
                'action_type' => 'refund_payout',
                'status' => 'completed',
                'amount' => $finalAmount,
                'reference_code' => $refCode,
                'reconciliation_note' => $reason,
                'reconciled_at' => now(),
            ]);

            // Đóng toàn bộ phiếu refund_request đang mở để tránh phiếu treo
            OrderReconciliation::where('order_id', $order->id)
                ->where('action_type', 'refund_request')
                ->where('status', 'pending')
                ->update([
                    'status' => 'completed',
                    'approver_id' => $actor->id,
                    'reconciled_at' => now(),
                    'review_note' => "Hoàn tiền trực tiếp với mã GD {$refCode}",
                ]);

            AuditLogger::log(
                'refund_order',
                'Order',
                $order->id,
                "Hoàn tiền đơn hàng #{$order->order_code} số tiền ".number_format($finalAmount, 0, ',', '.')."đ. Lý do: {$reason}",
                ['order_code' => $order->order_code, 'amount' => $finalAmount, 'reason' => $reason, 'reference_code' => $refCode]
            );

            return $order;
        });
    }

    /**
     * Từ chối yêu cầu hoàn tiền kèm lý do minh bạch.
     */
    public function rejectRefund(OrderReconciliation $reconciliation, string $reason, User $reviewer): OrderReconciliation
    {
        return $this->rejectReconciliation(
            $reconciliation->id,
            $reason,
            $reviewer,
            orderReconciliationStatus: 'refund_rejected',
            auditAction: 'order.refund_rejected'
        );
    }

    /**
     * Seam từ chối phiếu đối soát (manual approval hoặc refund request) trong transaction có lock.
     */
    public function rejectReconciliation(
        int $reconciliationId,
        string $reason,
        User $reviewer,
        string $orderReconciliationStatus = 'rejected',
        string $auditAction = 'reject_dual_approval'
    ): OrderReconciliation {
        $reason = trim($reason);

        return DB::transaction(function () use ($reconciliationId, $reason, $reviewer, $orderReconciliationStatus, $auditAction): OrderReconciliation {
            $lockedRecon = OrderReconciliation::lockForUpdate()->findOrFail($reconciliationId);

            if (! $lockedRecon->isPending()) {
                throw new \InvalidArgumentException('Yêu cầu đối soát này đã được xử lý trước đó.');
            }

            $lockedOrder = Order::lockForUpdate()->findOrFail($lockedRecon->order_id);

            $lockedRecon->update([
                'status' => 'rejected',
                'approver_id' => $reviewer->id,
                'reconciled_at' => now(),
                'review_note' => $reason,
            ]);

            $lockedOrder->update([
                'reconciliation_status' => $orderReconciliationStatus,
            ]);

            AuditLogger::log(
                action: $auditAction,
                targetType: 'Order',
                targetId: $lockedOrder->id,
                description: "Từ chối đối soát/hoàn tiền đơn #{$lockedOrder->order_code} bởi {$reviewer->name}. Lý do: {$reason}",
                details: [
                    'order_id' => $lockedOrder->id,
                    'reconciliation_id' => $lockedRecon->id,
                    'reason' => $reason,
                    'reviewer_id' => $reviewer->id,
                ]
            );

            return $lockedRecon;
        });
    }

    /**
     * Đường thu hồi thống nhất: đồng thời thu hồi quyền thiệp (EntitlementStateMachine + gỡ xuất bản)
     * và thu hồi hoa hồng/số dư ví đối tác (PartnerCommission).
     */
    protected function revokeOrderEntitlementsAndCommissions(Order $order, User $actor): void
    {
        $this->downgradeOrderServices($order);
        $this->revertPartnerCommission($order, $actor);
    }

    /**
     * Hạ cấp an toàn gói thiệp cưới sau khi hoàn tiền: thu hồi expires_at + xóa cache + gỡ xuất bản.
     */
    protected function downgradeOrderServices(Order $order): void
    {
        $invitation = $order->invitation;
        if (! $invitation) {
            return;
        }

        EntitlementStateMachineService::applyRefundedOrder($invitation, $order);

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
            ->lockForUpdate()
            ->get();

        foreach ($commissions as $comm) {
            $partner = $comm->partner;
            $clawbackAmount = (float) ($comm->amount ?? 0);
            if ($partner && in_array($comm->status, ['credited', 'approved'], true) && $clawbackAmount > 0) {
                // Trừ lại số dư ví nếu hoa hồng đã được ghi có/duyệt vào ví đối tác
                $partner->decrement('wallet_balance', $clawbackAmount);
            }

            $existingNote = trim((string) ($comm->reconciliation_note ?? ''));
            $comm->update([
                'status' => 'cancelled',
                'reconciliation_note' => ($existingNote !== '' ? $existingNote.' | ' : '')."Hủy do hoàn tiền đơn hàng #{$order->order_code} bởi {$admin->name}.",
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
