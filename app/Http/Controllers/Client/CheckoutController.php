<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Show checkout page for selected plan.
     */
    public function show(string $planSlug): View
    {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        return view('client.pages.checkout', [
            'plan' => $plan,
        ]);
    }

    /**
     * Create an order and generate VietQR code.
     */
    public function process(CreateOrderRequest $request): JsonResponse
    {
        $plan = Plan::findOrFail($request->validated('plan_id'));

        $user = auth()->user() ?? User::firstOrCreate(
            ['email' => $request->validated('customer_email')],
            [
                'name' => $request->validated('customer_name'),
                'phone' => $request->validated('customer_phone'),
                'password' => bcrypt(Str::random(16)),
                'role' => 'customer',
            ]
        );

        $orderCode = 'MH'.strtoupper(Str::random(8));

        $order = Order::create([
            'order_code' => $orderCode,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'invitation_id' => $request->validated('invitation_id'),
            'amount' => $plan->price,
            'status' => 'pending',
            'payment_method' => $request->validated('payment_method'),
        ]);

        // VietQR payload
        $vietQrUrl = 'https://img.vietqr.io/image/MB-0988888888-compact2.png?'.http_build_query([
            'amount' => $order->amount,
            'addInfo' => $order->order_code,
            'accountName' => 'MAY HEN VN',
        ]);

        return response()->json([
            'success' => true,
            'order_code' => $order->order_code,
            'amount' => $order->amount,
            'formatted_amount' => number_format($order->amount, 0, ',', '.').'đ',
            'vietqr_url' => $vietQrUrl,
            'bank_info' => [
                'bank_name' => 'Ngân hàng Quân Đội (MB Bank)',
                'account_number' => '0988888888',
                'account_name' => 'MAY HEN VN',
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
        // 1. Verify gateway authorization token if configured
        $secretToken = config('services.sepay.webhook_token', 'mayhen_secret_webhook_token_2026');
        $authHeader = $request->header('Authorization') ?? $request->header('X-API-KEY');

        if ($authHeader && ! str_contains($authHeader, $secretToken)) {
            return response()->json(['error' => 'Unauthorized signature'], 401);
        }

        // 2. Extract content & amount
        $content = $request->input('content') ?? $request->input('description') ?? '';
        $amount = (int) ($request->input('transferAmount') ?? $request->input('amount') ?? 0);

        // Find matching pending order code in transaction description
        preg_match('/MH[A-Z0-9]{8}/', strtoupper($content), $matches);
        $orderCode = $matches[0] ?? null;

        if (! $orderCode) {
            return response()->json(['success' => false, 'message' => 'No order code detected in description'], 200);
        }

        $order = Order::where('order_code', $orderCode)->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 200);
        }

        // 3. Idempotency Check: Don't process twice
        if ($order->isPaid()) {
            return response()->json(['success' => true, 'message' => 'Order already processed (idempotent)'], 200);
        }

        // 4. Verify amount matches
        if ($amount >= $order->amount) {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Activate invitation entitlement if attached
            if ($order->invitation) {
                $order->invitation->update([
                    'is_published' => true,
                    'published_at' => now(),
                    'expires_at' => now()->addDays($order->plan->duration_days),
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Payment confirmed and plan activated'], 200);
        }

        return response()->json(['success' => false, 'message' => 'Insufficient payment amount'], 400);
    }
}
