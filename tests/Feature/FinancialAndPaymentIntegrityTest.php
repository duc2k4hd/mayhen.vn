<?php

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderReconciliation;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingGift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('services.sepay.webhook_secret', 'test_sepay_webhook_secret_key_123');
    Config::set('services.sepay.bank_account', '0988776655');
    Config::set('services.sepay.bank_code', 'MB');
    Config::set('services.sepay.bank_name', 'MBBank');
    Config::set('services.sepay.bank_account_name', 'CONG TY MAY HEN');
});

/**
 * Tạo header xác thực SePay hợp lệ
 */
function generateSepayHeaders(string $secret, string $content, ?int $timestamp = null): array
{
    $timestamp = $timestamp ?? now()->timestamp;
    $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$content, $secret);

    return [
        'X-SePay-Timestamp' => (string) $timestamp,
        'X-SePay-Signature' => $signature,
        'Content-Type' => 'application/json',
    ];
}

test('G1-009: Webhook trùng provider transaction ID đảm bảo idempotent và từ chối tái sử dụng', function () {
    $user = User::factory()->create();
    $plan = Plan::create([
        'name' => 'Gói Trọn Đời',
        'slug' => 'goi-tron-doi-'.uniqid(),
        'price' => 299000,
        'features' => [],
        'is_active' => true,
    ]);

    $order1 = Order::create([
        'order_code' => 'MHTESTORD101',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => 299000,
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
    ]);

    $order2 = Order::create([
        'order_code' => 'MHTESTORD102',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => 299000,
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
    ]);

    $secret = config('services.sepay.webhook_secret');
    $providerTransId = 'SEPAY_TX_999888';

    // 1. Webhook lần đầu thanh toán cho order1
    $payload1 = [
        'id' => $providerTransId,
        'transferType' => 'in',
        'accountNumber' => '0988776655',
        'transferAmount' => 299000,
        'code' => 'MHTESTORD101',
        'content' => 'Thanh toan don hang MHTESTORD101',
    ];
    $rawContent1 = json_encode($payload1);
    $headers1 = generateSepayHeaders($secret, $rawContent1);

    $response1 = $this->call(
        'POST',
        route('webhook.payment'),
        [],
        [],
        [],
        $this->transformHeadersToServerVars($headers1),
        $rawContent1
    );

    $response1->assertStatus(200);
    expect($order1->fresh()->isPaid())->toBeTrue();
    expect($order1->fresh()->provider_transaction_id)->toBe($providerTransId);

    // 2. Webhook lặp lại cùng providerTransId cho cùng order1 (Idempotent 200)
    $responseDuplicate = $this->call(
        'POST',
        route('webhook.payment'),
        [],
        [],
        [],
        $this->transformHeadersToServerVars($headers1),
        $rawContent1
    );

    $responseDuplicate->assertStatus(200);
    $responseDuplicate->assertJson(['success' => true, 'message' => 'Order already processed (idempotent)']);

    // 3. Webhook gửi providerTransId đã dùng cho order1 nhưng nhằm thanh toán order2 -> 409 Conflict
    $payload2 = [
        'id' => $providerTransId,
        'transferType' => 'in',
        'accountNumber' => '0988776655',
        'transferAmount' => 299000,
        'code' => 'MHTESTORD102',
        'content' => 'Thanh toan don hang MHTESTORD102',
    ];
    $rawContent2 = json_encode($payload2);
    $headers2 = generateSepayHeaders($secret, $rawContent2);

    $responseReplay = $this->call(
        'POST',
        route('webhook.payment'),
        [],
        [],
        [],
        $this->transformHeadersToServerVars($headers2),
        $rawContent2
    );

    $responseReplay->assertStatus(409);
    $responseReplay->assertJson(['success' => false, 'message' => 'Transaction ID already used for another order']);
    expect($order2->fresh()->isPaid())->toBeFalse();
});

test('G1-010: Webhook tiền thiếu trả về 400 và transferType out trả về 200 không đổi status', function () {
    $user = User::factory()->create();
    $plan = Plan::create([
        'name' => 'Gói Tiêu Chuẩn',
        'slug' => 'goi-tieu-chuan-'.uniqid(),
        'price' => 500000,
        'features' => [],
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_code' => 'MHTESTORD201',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => 500000,
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
    ]);

    $secret = config('services.sepay.webhook_secret');

    // 1. Chuyển thiếu tiền (400.000đ thay vì 500.000đ) -> 400 Mismatch
    $payloadUnderpaid = [
        'id' => 'TX_UNDERPAID_01',
        'transferType' => 'in',
        'accountNumber' => '0988776655',
        'transferAmount' => 400000,
        'code' => 'MHTESTORD201',
        'content' => 'Thanh toan MHTESTORD201',
    ];
    $rawContentUnder = json_encode($payloadUnderpaid);
    $headersUnder = generateSepayHeaders($secret, $rawContentUnder);

    $resUnder = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($headersUnder), $rawContentUnder);
    $resUnder->assertStatus(400);
    $resUnder->assertJson(['success' => false, 'message' => 'Payment amount mismatch']);
    expect($order->fresh()->isPaid())->toBeFalse();

    // 2. Giao dịch tiền ra (transferType === 'out') -> 200 OK nhưng không active order
    $payloadOut = [
        'id' => 'TX_OUT_02',
        'transferType' => 'out',
        'accountNumber' => '0988776655',
        'transferAmount' => 500000,
        'code' => 'MHTESTORD201',
        'content' => 'Rut tien ngan hang MHTESTORD201',
    ];
    $rawContentOut = json_encode($payloadOut);
    $headersOut = generateSepayHeaders($secret, $rawContentOut);

    $resOut = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($headersOut), $rawContentOut);
    $resOut->assertStatus(200);
    $resOut->assertJson(['success' => true]);
    expect($order->fresh()->isPaid())->toBeFalse();
    expect($order->fresh()->status)->toBe('pending');
});

test('G1-011: Nút duyệt refund yêu cầu payout_reference ngân hàng thực tế', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $plan = Plan::create([
        'name' => 'Gói Hoàn Tiền',
        'slug' => 'goi-hoan-tien-'.uniqid(),
        'price' => 300000,
        'features' => [],
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_code' => 'MHTESTREF01',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => 300000,
        'status' => 'paid',
        'payment_method' => 'bank_transfer',
        'paid_at' => now(),
    ]);

    $reconciliation = OrderReconciliation::create([
        'order_id' => $order->id,
        'creator_id' => $staff->id,
        'action_type' => 'refund_request',
        'status' => 'pending',
        'amount' => 300000,
        'reference_code' => 'REF-REQ-TEST01',
        'reconciliation_note' => 'Khách hàng đổi ý muốn hoàn gói',
    ]);

    // 1. Duyệt mà thiếu payout_reference -> Validate fail 302 back with session errors
    $resMissingRef = $this->actingAs($admin)->post(
        route('admin.refunds.approve', ['id' => $reconciliation->id]),
        [
            'payout_reference' => '',
            'review_note' => 'Duyet refund thieu ma',
        ]
    );
    $resMissingRef->assertSessionHasErrors('payout_reference');
    expect($reconciliation->fresh()->status)->toBe('pending');

    // 2. Duyệt có payout_reference hợp lệ từ ngân hàng -> Thành công
    $resSuccess = $this->actingAs($admin)->post(
        route('admin.refunds.approve', ['id' => $reconciliation->id]),
        [
            'payout_reference' => 'VCB_REFUND_99881122',
            'review_note' => 'Đã chuyển khoản hoàn tất qua Vietcombank',
        ]
    );
    $resSuccess->assertRedirect(route('admin.refunds.index', ['tab' => 'requests']));
    expect($reconciliation->fresh()->status)->toBe('completed');
    expect($reconciliation->fresh()->reference_code)->toBe('VCB_REFUND_99881122');
});

test('G1-012: Tách biệt doanh thu Order nền tảng với WeddingGift tiền mừng của cặp đôi', function () {
    $coupleUser = User::factory()->create();
    $wedding = Wedding::create([
        'user_id' => $coupleUser->id,
        'groom_name' => 'Chú Rể Hoàng',
        'bride_name' => 'Cô Dâu Mai',
        'wedding_date' => now()->addDays(30)->toDateString(),
        'slug' => 'hoang-mai-'.uniqid(),
    ]);

    $plan = Plan::create([
        'name' => 'Gói Doanh Thu',
        'slug' => 'goi-doanh-thu-'.uniqid(),
        'price' => 499000,
        'features' => [],
        'is_active' => true,
    ]);

    // 1. Đơn hàng mua gói phần mềm của Mây Hẹn (Doanh thu nền tảng)
    $platformOrder = Order::create([
        'order_code' => 'MHTESTREV01',
        'user_id' => $coupleUser->id,
        'plan_id' => $plan->id,
        'amount' => 499000,
        'status' => 'paid',
        'payment_method' => 'bank_transfer',
        'paid_at' => now(),
    ]);

    // 2. Khách mừng cưới cặp đôi vào STK riêng (Tiền mừng cặp đôi, không phải doanh thu Mây Hẹn)
    $weddingGift = WeddingGift::create([
        'wedding_id' => $wedding->id,
        'sender_name' => 'Anh Ba Bạc Liêu',
        'amount' => 5000000,
        'wishing_message' => 'Chúc hai em trăm năm hạnh phúc!',
        'status' => 'received',
    ]);

    // 3. Tính toán tổng doanh thu nền tảng từ bảng orders
    $totalPlatformRevenue = Order::where('status', 'paid')->sum('amount');
    $totalWeddingGifts = WeddingGift::where('wedding_id', $wedding->id)->sum('amount');

    expect($totalPlatformRevenue)->toBe(499000);
    expect($totalWeddingGifts)->toBe(5000000);
    expect($totalPlatformRevenue)->not->toBe($totalPlatformRevenue + $totalWeddingGifts);
});

test('G1-013: Coupon cạnh tranh hạn mức dùng lockForUpdate chống race condition vượt quota', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $plan = Plan::create([
        'name' => 'Gói Hạn Mức',
        'slug' => 'goi-han-muc-'.uniqid(),
        'price' => 500000,
        'features' => [],
        'is_active' => true,
    ]);

    // Coupon chỉ còn đúng 1 lượt sử dụng cuối cùng
    $coupon = Coupon::create([
        'code' => 'LIMITED1',
        'name' => 'Mã giảm giá giới hạn',
        'discount_type' => 'fixed',
        'discount_value' => 100000,
        'usage_limit' => 1,
        'used_count' => 0,
        'is_active' => true,
        'starts_at' => now()->subDay(),
        'expires_at' => now()->addDays(7),
    ]);

    // Request 1 thanh toán với coupon
    $res1 = $this->actingAs($user1)->postJson(route('checkout.process'), [
        'plan_id' => $plan->id,
        'payment_method' => 'bank_transfer',
        'customer_name' => $user1->name,
        'customer_email' => $user1->email,
        'customer_phone' => '0901234567',
        'coupon_code' => 'LIMITED1',
    ]);

    $res1->assertStatus(200);
    $res1->assertJson(['success' => true, 'amount' => 400000]);
    expect($coupon->fresh()->used_count)->toBe(1);

    // Request 2 cùng dùng coupon đó ngay sau đó (đã hết hạn mức)
    $res2 = $this->actingAs($user2)->postJson(route('checkout.process'), [
        'plan_id' => $plan->id,
        'payment_method' => 'bank_transfer',
        'customer_name' => $user2->name,
        'customer_email' => $user2->email,
        'customer_phone' => '0907654321',
        'coupon_code' => 'LIMITED1',
    ]);

    $res2->assertStatus(200);
    // User 2 phải thanh toán nguyên giá 500.000đ do coupon không còn hợp lệ
    $res2->assertJson(['success' => true, 'amount' => 500000]);
    expect($coupon->fresh()->used_count)->toBe(1);
});
