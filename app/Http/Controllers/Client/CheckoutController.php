<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Services\PostPaymentOnboardingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Show checkout page for selected plan.
     */
    public function show(Request $request, string $planSlug): View
    {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        $invitation = null;
        $invParam = $request->query('invitation_id') ?: $request->query('invitation');
        if ($invParam) {
            $inv = Invitation::with(['wedding', 'template'])
                ->where(is_numeric($invParam) ? 'id' : 'slug', $invParam)
                ->first();
            if ($inv && $inv->isOwner()) {
                $invitation = $inv;
            }
        }

        return view('client.pages.checkout', [
            'plan' => $plan,
            'invitation' => $invitation,
        ]);
    }

    /**
     * Create an order and generate VietQR code.
     */
    public function process(CreateOrderRequest $request): JsonResponse
    {
        $plan = Plan::where('is_active', true)->findOrFail($request->validated('plan_id'));

        $invitationId = $request->validated('invitation_id');
        if ($invitationId !== null) {
            $invitation = Invitation::with('wedding')->findOrFail($invitationId);
            abort_unless($invitation->isOwner(), 404);
        }

        $bankCode = config('services.sepay.bank_code');
        $bankName = config('services.sepay.bank_name');
        $bankAccount = config('services.sepay.bank_account');
        $bankAccountName = config('services.sepay.bank_account_name');
        if (! is_string($bankCode) || ! preg_match('/\A[A-Za-z0-9]{1,20}\z/', $bankCode)
            || ! is_string($bankAccount) || ! preg_match('/\A[A-Za-z0-9]{1,19}\z/', $bankAccount)
            || ! is_string($bankName) || trim($bankName) === ''
            || ! is_string($bankAccountName) || trim($bankAccountName) === '') {
            return response()->json(['success' => false, 'message' => 'Chưa cấu hình tài khoản nhận thanh toán.'], 503);
        }

        $user = auth()->user() ?? User::firstOrCreate(
            ['email' => $request->validated('customer_email')],
            [
                'name' => $request->validated('customer_name'),
                'phone' => $request->validated('customer_phone'),
                'password' => bcrypt(Str::random(16)),
                'role' => 'customer',
            ]
        );

        $orderCode = 'MH'.strtoupper(Str::random(10));

        $order = Order::create([
            'order_code' => $orderCode,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'invitation_id' => $invitationId,
            'amount' => $plan->price,
            'status' => 'pending',
            'payment_method' => $request->validated('payment_method'),
        ]);

        // VietQR payload
        $vietQrUrl = 'https://img.vietqr.io/image/'.rawurlencode($bankCode).'-'.rawurlencode($bankAccount).'-compact2.png?'.http_build_query([
            'amount' => $order->amount,
            'addInfo' => $order->order_code,
            'accountName' => $bankAccountName,
        ]);

        return response()->json([
            'success' => true,
            'order_code' => $order->order_code,
            'amount' => $order->amount,
            'formatted_amount' => number_format($order->amount, 0, ',', '.').'đ',
            'vietqr_url' => $vietQrUrl,
            'bank_info' => [
                'bank_name' => $bankName,
                'account_number' => $bankAccount,
                'account_name' => $bankAccountName,
                'content' => $order->order_code,
            ],
        ]);
    }

    /**
     * Polling endpoint to check if order has been paid.
     */
    public function checkStatus(string $orderCode): JsonResponse
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        return response()->json([
            'order_code' => $order->order_code,
            'status' => $order->status,
            'is_paid' => $order->isPaid(),
        ]);
    }

    /**
     * Webhook receiver from payment gateway (SePay / VietQR).
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $secret = config('services.sepay.webhook_secret');
        if (! is_string($secret) || trim($secret) === '') {
            return response()->json(['error' => 'Webhook is not configured'], 503);
        }

        $timestamp = $request->header('X-SePay-Timestamp');
        $signature = $request->header('X-SePay-Signature');
        if (! is_string($timestamp) || ! preg_match('/\A[0-9]{1,12}\z/', $timestamp)
            || abs(now()->timestamp - (int) $timestamp) > 300
            || ! is_string($signature) || ! preg_match('/\Asha256=[a-f0-9]{64}\z/', $signature)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $expectedSignature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);
        if (! hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $transferType = $request->input('transferType');
        if ($transferType === 'out') {
            return response()->json(['success' => true]);
        }
        if ($transferType !== 'in') {
            return response()->json(['success' => false, 'message' => 'Invalid transfer type'], 422);
        }

        $bankAccount = config('services.sepay.bank_account');
        if (! is_string($bankAccount) || $bankAccount === '') {
            return response()->json(['error' => 'Payment account is not configured'], 503);
        }
        $receivedAccount = $request->input('accountNumber');
        if (! is_string($receivedAccount) || $receivedAccount !== $bankAccount) {
            return response()->json(['success' => false, 'message' => 'Invalid payment account'], 422);
        }

        $content = $request->input('content') ?? $request->input('description') ?? '';
        $rawAmount = $request->input('transferAmount') ?? $request->input('amount');
        if (! is_string($content) || (! is_int($rawAmount) && ! is_string($rawAmount))) {
            return response()->json(['success' => false, 'message' => 'Invalid payment data'], 422);
        }

        $amount = filter_var($rawAmount, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($amount === false) {
            return response()->json(['success' => false, 'message' => 'Invalid payment data'], 422);
        }

        // Hỗ trợ mã mới 10 ký tự và đơn cũ 8 ký tự sau tiền tố MH.
        $paymentCode = $request->input('code');
        $orderCode = is_string($paymentCode) && preg_match('/\AMH(?:[A-Z0-9]{10}|[A-Z0-9]{8})\z/i', $paymentCode)
            ? $paymentCode
            : null;
        if ($orderCode === null) {
            preg_match('/(?<![A-Z0-9])MH(?:[A-Z0-9]{10}|[A-Z0-9]{8})(?![A-Z0-9])/i', $content, $matches);
            $orderCode = $matches[0] ?? null;
        }

        if (! $orderCode) {
            $this->recordWebhookLog('warning', 'Không tìm thấy mã đơn hàng trong nội dung chuyển khoản', null, $amount, $request->ip(), $content);

            return response()->json(['success' => false, 'message' => 'No order code detected in description'], 200);
        }

        $orderCode = strtoupper($orderCode);
        $providerTransId = $request->input('id') ? (string) $request->input('id') : ($request->input('referenceCode') ? (string) $request->input('referenceCode') : null);

        return DB::transaction(function () use ($orderCode, $amount, $request, $content, $providerTransId): JsonResponse {
            $order = Order::where('order_code', $orderCode)->lockForUpdate()->first();

            if (! $order) {
                $this->recordWebhookLog('warning', "Không tìm thấy đơn hàng #{$orderCode} trong cơ sở dữ liệu", $orderCode, $amount, $request->ip(), $content);

                return response()->json(['success' => false, 'message' => 'Order not found'], 200);
            }

            // Chống tấn công replay attack hoặc 1 giao dịch ngân hàng dùng chung cho 2 đơn khác nhau
            if ($providerTransId && Order::where('provider_transaction_id', $providerTransId)->where('id', '!=', $order->id)->exists()) {
                $this->recordWebhookLog('error', "Mã giao dịch SePay #{$providerTransId} đã được sử dụng cho một đơn hàng khác", $orderCode, $amount, $request->ip(), $content);

                return response()->json(['success' => false, 'message' => 'Transaction ID already used for another order'], 409);
            }

            if ($order->isPaid()) {
                $this->recordWebhookLog('info', "Đơn hàng #{$orderCode} đã được thanh toán trước đó (idempotent)", $orderCode, $amount, $request->ip(), $content);

                return response()->json(['success' => true, 'message' => 'Order already processed (idempotent)'], 200);
            }

            if ($order->status !== 'pending') {
                $this->recordWebhookLog('error', "Đơn hàng #{$orderCode} không ở trạng thái chờ thanh toán", $orderCode, $amount, $request->ip(), $content);

                return response()->json(['success' => false, 'message' => 'Order cannot be paid in its current status'], 409);
            }

            if ($amount !== $order->amount) {
                $this->recordWebhookLog('error', "Số tiền chuyển khoản ({$amount}đ) không khớp giá trị đơn ({$order->amount}đ)", $orderCode, $amount, $request->ip(), $content);

                return response()->json(['success' => false, 'message' => 'Payment amount mismatch'], 400);
            }

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'provider_transaction_id' => $providerTransId,
            ]);

            if ($order->invitation) {
                $order->invitation->update([
                    'is_published' => true,
                    'published_at' => now(),
                    'expires_at' => now()->addDays($order->plan->duration_days),
                ]);
            }

            $this->recordWebhookLog('success', "Xác nhận thanh toán và kích hoạt gói cho đơn #{$orderCode} thành công", $orderCode, $amount, $request->ip(), $content);

            return response()->json(['success' => true, 'message' => 'Payment confirmed and plan activated'], 200);
        });
    }

    /**
     * Record a payment webhook audit log entry to storage/logs/payment_webhooks.jsonl.
     */
    protected function recordWebhookLog(string $status, string $message, ?string $orderCode = null, ?int $amount = null, ?string $ip = null, ?string $content = null): void
    {
        try {
            $logPath = storage_path('logs/payment_webhooks.jsonl');
            $dir = dirname($logPath);
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $entry = json_encode([
                'timestamp' => now()->toIso8601String(),
                'ip' => $ip ?? request()->ip(),
                'status' => $status,
                'message' => $message,
                'order_code' => $orderCode,
                'amount' => $amount,
                'content' => $content ?? '',
                'gateway' => 'SePay',
            ], JSON_UNESCAPED_UNICODE);

            File::append($logPath, $entry."\n");
        } catch (\Throwable) {
            // Keep webhook idempotent and robust
        }
    }

    /**
     * Màn hình Onboarding Khởi Đầu Thành Công Sau Thanh Toán (TASK-VAL-26).
     */
    public function firstSuccess(string $orderCode, PostPaymentOnboardingService $service): View
    {
        $order = Order::with(['plan', 'invitation.wedding'])
            ->where('order_code', strtoupper($orderCode))
            ->firstOrFail();

        $guide = $service->getOnboardingGuide($order);

        return view('client.pages.checkout-first-success', [
            'order' => $order,
            'guide' => $guide,
        ]);
    }
}
