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

        // 1. Kích hoạt / gia hạn gói tài khoản (Plus: 6 tháng / 10 thiệp; Pro: 1 năm / 30 thiệp)
        app(PlanAccessService::class)->activateUserPlanFromOrder($order);

        if (! $order->invitation) {
            return;
        }

        $planSlug = app(PlanAccessService::class)->normalizeTier($order->plan?->slug ?? 'plus');
        $invUpdates = [
            'is_published' => true,
            'published_at' => $order->invitation->published_at ?? now(),
            'purge_queued_at' => null,
        ];

        if (in_array($planSlug, ['plus', 'pro'], true)) {
            $invUpdates['plan_slug'] = $planSlug;
            $invUpdates['plan_cycle_order_id'] = $order->id;
        }

        $order->invitation->update($invUpdates);

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
     * Đối soát & kích hoạt đơn hàng từ kết quả xác minh trực tiếp qua SePay User API.
     * Hỗ trợ phục hồi các đơn ở trạng thái pending, failed, cancelled khi có chứng từ giao dịch thật từ SePay
     * và tự động nâng cấp ĐÚNG gói dịch vụ trên đơn hàng (Plus / Pro).
     *
     * @param  array<string, mixed>  $txMeta
     * @return array{
     *     ok: bool,
     *     is_paid: bool,
     *     status: string,
     *     http_status: int,
     *     flash_type: string,
     *     log_level: string,
     *     db_log_status: string,
     *     log_message: string,
     *     message: string
     * }
     */
    public function settleFromSepayApi(
        int $orderId,
        int $amount,
        ?string $providerTransId,
        array $txMeta = [],
        ?User $actor = null,
        string $trigger = 'admin'
    ): array {
        $normalizedTransId = $providerTransId !== null && trim($providerTransId) !== ''
            ? trim($providerTransId)
            : null;

        $outcome = DB::transaction(function () use ($orderId, $amount, $normalizedTransId, $txMeta, $actor, $trigger): array {
            $order = Order::with(['plan', 'invitation', 'user'])
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->isPaid()) {
                return [
                    'ok' => true,
                    'is_paid' => true,
                    'status' => 'already_paid',
                    'http_status' => 200,
                    'flash_type' => 'info',
                    'log_level' => 'info',
                    'db_log_status' => 'duplicate',
                    'log_message' => "Đơn hàng #{$order->order_code} đã được thanh toán trước đó (idempotent)",
                    'message' => "Đơn hàng #{$order->order_code} đã được thanh toán trước đó.",
                    'audit' => null,
                ];
            }

            if (! $order->canBeVerifiedWithSepay()) {
                return [
                    'ok' => false,
                    'is_paid' => false,
                    'status' => 'invalid_status',
                    'http_status' => 409,
                    'flash_type' => 'error',
                    'log_level' => 'error',
                    'db_log_status' => 'pending_review',
                    'log_message' => "Đơn hàng #{$order->order_code} không ở trạng thái cho phép kích hoạt (hiện tại: {$order->status})",
                    'message' => "Đơn hàng #{$order->order_code} đang ở trạng thái [{$order->status}], không thể kích hoạt.",
                    'audit' => null,
                ];
            }

            if ($this->isDuplicateTransactionId($normalizedTransId, $order->id)) {
                return [
                    'ok' => false,
                    'is_paid' => false,
                    'status' => 'duplicate_transaction',
                    'http_status' => 409,
                    'flash_type' => 'error',
                    'log_level' => 'error',
                    'db_log_status' => 'duplicate',
                    'log_message' => "Mã giao dịch SePay #{$normalizedTransId} đã được dùng cho một đơn hàng khác",
                    'message' => "Mã giao dịch SePay [{$normalizedTransId}] đã được gắn cho một đơn hàng khác. Tuyệt đối không kích hoạt trùng lặp.",
                    'audit' => null,
                ];
            }

            if ($amount !== $order->amount) {
                $diffText = $amount < $order->amount ? 'thiếu' : 'thừa';

                return [
                    'ok' => false,
                    'is_paid' => false,
                    'status' => 'amount_mismatch',
                    'http_status' => 400,
                    'flash_type' => 'error',
                    'log_level' => 'error',
                    'db_log_status' => 'mismatch',
                    'log_message' => "Số tiền trên SePay {$diffText} ({$amount}đ) không khớp giá trị đơn ({$order->amount}đ)",
                    'message' => "Số tiền chuyển khoản {$diffText} (".number_format($amount, 0, ',', '.').'đ / '.number_format($order->amount, 0, ',', '.').'đ). Không tự động kích hoạt.',
                    'audit' => null,
                ];
            }

            $previousStatus = $order->status;
            $refCode = $normalizedTransId ?: ('SEPAY-'.$order->order_code);

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'provider_transaction_id' => $normalizedTransId,
                'reconciliation_status' => 'approved',
            ]);

            // Tự động kích hoạt ĐÚNG gói dịch vụ trên đơn hàng (Plus 6 tháng / Pro 1 năm) + thiệp đính kèm
            $this->activateOrderEntitlement($order);

            $planLabel = $order->plan?->name ?? strtoupper((string) ($order->plan?->slug ?? 'Gói dịch vụ'));
            $sourceLabel = match ($trigger) {
                'admin' => 'Admin kiểm tra trực tiếp SePay API',
                'cron_reconcile' => 'Cron tự động đối soát bù SePay API (vớt đơn lỡ webhook)',
                'shared_payer' => 'Người thanh toán hộ bấm kiểm tra SePay API',
                default => 'Khách hàng bấm kiểm tra SePay API',
            };

            OrderReconciliation::create([
                'order_id' => $order->id,
                'creator_id' => $actor?->id,
                'approver_id' => $actor?->id,
                'action_type' => 'sepay_auto_reconcile',
                'status' => 'approved',
                'amount' => $amount,
                'reference_code' => $refCode,
                'bank_name' => $txMeta['bank_brand_name'] ?? null,
                'bank_account_number' => $txMeta['account_number'] ?? null,
                'reconciliation_note' => "{$sourceLabel} — khớp mã đơn #{$order->order_code} & số tiền ".number_format($amount, 0, ',', '.')."đ (trạng thái cũ: {$previousStatus}).",
                'review_note' => "Xác minh tự động 5 điều kiện qua SePay User API (GD: {$refCode})",
                'reconciled_at' => now(),
                'metadata' => [
                    'trigger' => $trigger,
                    'previous_status' => $previousStatus,
                    'sepay_transaction_id' => $normalizedTransId,
                    'sepay_reference_number' => $txMeta['reference_number'] ?? null,
                    'sepay_transaction_date' => $txMeta['transaction_date'] ?? null,
                    'sepay_content' => $txMeta['content'] ?? null,
                    'activated_plan_slug' => $order->plan?->slug,
                ],
            ]);

            $successMessage = "Đã đối soát khớp giao dịch SePay [{$refCode}] (".number_format($amount, 0, ',', '.')."đ) cho đơn #{$order->order_code}. Trạng thái đã chuyển sang Đã Thanh Toán và tự động kích hoạt {$planLabel} cho tài khoản khách hàng.";

            return [
                'ok' => true,
                'is_paid' => true,
                'status' => 'settled',
                'http_status' => 200,
                'flash_type' => 'success',
                'log_level' => 'success',
                'db_log_status' => 'success',
                'log_message' => $successMessage,
                'message' => $successMessage,
                'audit' => [
                    'order_id' => $order->id,
                    'description' => "{$sourceLabel} xác nhận khớp giao dịch [{$refCode}] cho đơn #{$order->order_code} (từ trạng thái {$previousStatus} → paid) và kích hoạt {$planLabel}.",
                    'meta' => [
                        'order_code' => $order->order_code,
                        'amount' => $amount,
                        'previous_status' => $previousStatus,
                        'provider_transaction_id' => $normalizedTransId,
                        'plan_slug' => $order->plan?->slug,
                        'trigger' => $trigger,
                    ],
                ],
            ];
        });

        if (! empty($outcome['audit'])) {
            AuditLogger::log(
                'sepay_api_reconcile_order',
                'Order',
                $outcome['audit']['order_id'],
                $outcome['audit']['description'],
                $outcome['audit']['meta']
            );
        }

        unset($outcome['audit']);

        return $outcome;
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
