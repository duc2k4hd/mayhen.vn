<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderReconciliation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Deep module owning all order settlement and entitlement activation transitions:
 * SePay webhook settlement, Admin manual approval, and Maker-Checker dual approval.
 * Enforces one row-locking, anti-replay, entitlement-activation, and cache-invalidation path.
 */
class OrderSettlementService
{
    /**
     * Single anti-replay invariant: a provider transaction ID can never be bound to two different orders.
     */
    public function isDuplicateTransactionId(?string $transactionId, int $excludeOrderId): bool
    {
        if ($transactionId === null || trim($transactionId) === '') {
            return false;
        }

        return Order::where('provider_transaction_id', trim($transactionId))
            ->where('id', '!=', $excludeOrderId)
            ->exists();
    }

    /**
     * Single entitlement activation path shared by webhook, manual approval, and dual approval.
     */
    protected function activateOrderEntitlement(Order $order): void
    {
        $order->loadMissing(['invitation', 'plan']);

        if (! $order->invitation) {
            return;
        }

        $order->invitation->update([
            'is_published' => true,
            'published_at' => $order->invitation->published_at ?? now(),
        ]);

        EntitlementStateMachineService::applyPaidOrder($order->invitation, $order);
    }

    /**
     * Settle an order from a verified SePay webhook payload inside a locked transaction.
     *
     * @return array{http_status: int, log_level: string, log_message: string, payload: array<string, mixed>}
     */
    public function settleFromWebhook(string $orderCode, int $amount, ?string $providerTransId): array
    {
        $normalizedCode = strtoupper(trim($orderCode));
        $normalizedTransId = $providerTransId !== null && trim($providerTransId) !== ''
            ? trim($providerTransId)
            : null;

        return DB::transaction(function () use ($normalizedCode, $amount, $normalizedTransId): array {
            $order = Order::with(['plan', 'invitation'])
                ->where('order_code', $normalizedCode)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                return [
                    'http_status' => 200,
                    'log_level' => 'warning',
                    'log_message' => "Không tìm thấy đơn hàng #{$normalizedCode} trong cơ sở dữ liệu",
                    'payload' => ['success' => false, 'message' => 'Order not found'],
                ];
            }

            if ($this->isDuplicateTransactionId($normalizedTransId, $order->id)) {
                return [
                    'http_status' => 409,
                    'log_level' => 'error',
                    'log_message' => "Mã giao dịch SePay #{$normalizedTransId} đã được sử dụng cho một đơn hàng khác",
                    'payload' => ['success' => false, 'message' => 'Transaction ID already used for another order'],
                ];
            }

            if ($order->isPaid()) {
                return [
                    'http_status' => 200,
                    'log_level' => 'info',
                    'log_message' => "Đơn hàng #{$normalizedCode} đã được thanh toán trước đó (idempotent)",
                    'payload' => ['success' => true, 'message' => 'Order already processed (idempotent)'],
                ];
            }

            if ($order->status !== 'pending' && $order->status !== 'cancelled') {
                return [
                    'http_status' => 409,
                    'log_level' => 'error',
                    'log_message' => "Đơn hàng #{$normalizedCode} không ở trạng thái có thể thanh toán (hiện tại: {$order->status})",
                    'payload' => ['success' => false, 'message' => 'Order cannot be paid in its current status'],
                ];
            }

            if ($amount !== $order->amount) {
                $diffText = $amount < $order->amount ? 'thiếu' : 'thừa';

                return [
                    'http_status' => 400,
                    'log_level' => 'error',
                    'log_message' => "Số tiền chuyển khoản {$diffText} ({$amount}đ) không khớp giá trị đơn ({$order->amount}đ)",
                    'payload' => ['success' => false, 'message' => 'Payment amount mismatch'],
                ];
            }

            $wasCancelled = $order->status === 'cancelled';

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'provider_transaction_id' => $normalizedTransId,
            ]);

            if ($wasCancelled) {
                AuditLogger::log(
                    'revive_cancelled_order_payment',
                    'Order',
                    $order->id,
                    "Đơn hàng #{$normalizedCode} đã hủy trước đó nhận được chuyển khoản thực tế thành công, tự động chuyển trạng thái sang Paid.",
                    ['amount' => $amount, 'provider_transaction_id' => $normalizedTransId]
                );
            }

            $this->activateOrderEntitlement($order);

            return [
                'http_status' => 200,
                'log_level' => 'success',
                'log_message' => "Xác nhận thanh toán và kích hoạt gói cho đơn #{$normalizedCode} thành công",
                'payload' => ['success' => true, 'message' => 'Payment confirmed and plan activated'],
            ];
        });
    }

    /**
     * Admin manual approval (direct or Maker-Checker dual-approval proposal).
     *
     * @return array{flash_type: string, message: string}
     */
    public function approveManually(
        int $orderId,
        User $actor,
        string $refCode,
        string $note,
        ?string $bankName = null,
        ?string $bankAccount = null,
        bool $requestDualApproval = false
    ): array {
        $refCode = trim($refCode);
        $note = trim($note);
        $isSuperAdmin = $actor->isSuperAdmin();

        $outcome = DB::transaction(function () use ($orderId, $actor, $refCode, $note, $bankName, $bankAccount, $requestDualApproval, $isSuperAdmin): array {
            $order = Order::with(['plan', 'invitation'])->lockForUpdate()->findOrFail($orderId);

            if ($order->isPaid()) {
                return [
                    'flash_type' => 'info',
                    'message' => "Đơn hàng #{$order->order_code} đã được thanh toán trước đó.",
                    'audit' => null,
                ];
            }

            if ($this->isDuplicateTransactionId($refCode, $order->id)) {
                return [
                    'flash_type' => 'error',
                    'message' => "Mã giao dịch ngân hàng [{$refCode}] đã được ghi nhận cho một đơn hàng khác. Tuyệt đối không đối soát trùng lặp.",
                    'audit' => null,
                ];
            }

            if ($requestDualApproval && ! $isSuperAdmin) {
                OrderReconciliation::create([
                    'order_id' => $order->id,
                    'creator_id' => $actor->id,
                    'approver_id' => null,
                    'action_type' => 'manual_approval',
                    'status' => 'pending',
                    'amount' => $order->amount,
                    'reference_code' => $refCode,
                    'bank_name' => $bankName,
                    'bank_account_number' => $bankAccount,
                    'reconciliation_note' => $note,
                ]);

                $order->update(['reconciliation_status' => 'pending_approval']);

                return [
                    'flash_type' => 'success',
                    'message' => "Đã tạo hồ sơ đề xuất đối soát cho đơn #{$order->order_code}. Yêu cầu đang chờ Super Admin phê duyệt (Dual Approval).",
                    'audit' => [
                        'action' => 'request_order_approval',
                        'order_id' => $order->id,
                        'description' => "Quản lý đề xuất đối soát thủ công đơn #{$order->order_code} (Mã GD: {$refCode}). Chờ Super Admin phê duyệt.",
                        'meta' => [
                            'order_code' => $order->order_code,
                            'reference_code' => $refCode,
                            'note' => $note,
                        ],
                    ],
                ];
            }

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'provider_transaction_id' => $refCode,
                'reconciliation_status' => 'approved',
            ]);

            $this->activateOrderEntitlement($order);

            OrderReconciliation::create([
                'order_id' => $order->id,
                'creator_id' => $actor->id,
                'approver_id' => $actor->id,
                'action_type' => 'manual_approval',
                'status' => 'approved',
                'amount' => $order->amount,
                'reference_code' => $refCode,
                'bank_name' => $bankName,
                'bank_account_number' => $bankAccount,
                'reconciliation_note' => $note,
                'review_note' => $isSuperAdmin ? 'Super Admin trực tiếp đối soát và phê duyệt' : 'Quản lý đối soát và phê duyệt trực tiếp',
                'reconciled_at' => now(),
            ]);

            return [
                'flash_type' => 'success',
                'message' => "Đã duyệt thanh toán thủ công cho đơn hàng #{$order->order_code} kèm chứng từ đối soát thành công.",
                'audit' => [
                    'action' => 'approve_order',
                    'order_id' => $order->id,
                    'description' => ($isSuperAdmin ? 'Super Admin' : 'Quản lý')." duyệt đối soát thành công đơn #{$order->order_code} (Mã GD: {$refCode}). Lý do: {$note}",
                    'meta' => [
                        'order_code' => $order->order_code,
                        'amount' => $order->amount,
                        'reference_code' => $refCode,
                        'note' => $note,
                    ],
                ],
            ];
        });

        if (! empty($outcome['audit'])) {
            AuditLogger::log(
                $outcome['audit']['action'],
                'Order',
                $outcome['audit']['order_id'],
                $outcome['audit']['description'],
                $outcome['audit']['meta']
            );
        }

        return [
            'flash_type' => $outcome['flash_type'],
            'message' => $outcome['message'],
        ];
    }

    /**
     * Super Admin confirmation of a pending Maker-Checker reconciliation.
     *
     * @return array{flash_type: string, message: string}
     */
    public function confirmReconciliation(int $reconciliationId, User $approver, string $reviewNote = 'Super Admin phê duyệt hợp lệ'): array
    {
        $outcome = DB::transaction(function () use ($reconciliationId, $approver, $reviewNote): array {
            $reconciliation = OrderReconciliation::with(['order.plan', 'order.invitation'])
                ->lockForUpdate()
                ->findOrFail($reconciliationId);

            if (! $reconciliation->isPending()) {
                return [
                    'flash_type' => 'info',
                    'message' => 'Yêu cầu đối soát này đã được xử lý trước đó.',
                    'audit' => null,
                ];
            }

            $order = Order::with(['plan', 'invitation'])->lockForUpdate()->findOrFail($reconciliation->order_id);

            if ($this->isDuplicateTransactionId($reconciliation->reference_code, $order->id)) {
                return [
                    'flash_type' => 'error',
                    'message' => "Mã giao dịch ngân hàng [{$reconciliation->reference_code}] đã được ghi nhận cho một đơn hàng khác. Tuyệt đối không đối soát trùng lặp.",
                    'audit' => null,
                ];
            }

            $reconciliation->update([
                'approver_id' => $approver->id,
                'status' => 'approved',
                'reconciled_at' => now(),
                'review_note' => $reviewNote,
            ]);

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'provider_transaction_id' => $reconciliation->reference_code,
                'reconciliation_status' => 'approved',
            ]);

            $this->activateOrderEntitlement($order);

            return [
                'flash_type' => 'success',
                'message' => "Đã phê duyệt đối soát và kích hoạt đơn hàng #{$order->order_code} thành công.",
                'audit' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'reference_code' => $reconciliation->reference_code,
                    'reconciliation_id' => $reconciliation->id,
                ],
            ];
        });

        if (! empty($outcome['audit'])) {
            AuditLogger::log(
                'confirm_dual_approval',
                'Order',
                $outcome['audit']['order_id'],
                "Super Admin phê duyệt (Dual Approval) đối soát đơn #{$outcome['audit']['order_code']} (Mã GD: {$outcome['audit']['reference_code']}).",
                [
                    'order_code' => $outcome['audit']['order_code'],
                    'reconciliation_id' => $outcome['audit']['reconciliation_id'],
                ]
            );
        }

        return [
            'flash_type' => $outcome['flash_type'],
            'message' => $outcome['message'],
        ];
    }
}
