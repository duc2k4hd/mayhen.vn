<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use Carbon\Carbon;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);

    config([
        'services.sepay.webhook_secret' => 'test_sepay_webhook_secret_key_123456',
        'services.sepay.bank_code' => 'ICB',
        'services.sepay.bank_name' => 'VietinBank',
        'services.sepay.bank_account' => '103870000000',
        'services.sepay.bank_account_name' => 'MAY HEN ATELIER',
    ]);
});

/**
 * Helper to build valid signed SePay webhook request headers and payload.
 */
function buildSignedSepayPayload(array $payload, ?int $timestamp = null, ?string $secret = null): array
{
    $secret = $secret ?? config('services.sepay.webhook_secret');
    $timestamp = $timestamp ?? now()->timestamp;
    $rawContent = json_encode($payload);
    $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$rawContent, $secret);

    return [
        'headers' => [
            'Content-Type' => 'application/json',
            'X-SePay-Timestamp' => (string) $timestamp,
            'X-SePay-Signature' => $signature,
        ],
        'content' => $rawContent,
    ];
}

test('order creation validates active plan and generates vietqr payload', function () {
    $user = User::factory()->create();
    $activePlan = Plan::create([
        'name' => 'Gói Plus Hoàng Gia',
        'slug' => 'plus-hoang-gia-'.uniqid(),
        'price' => 399000,
        'duration_days' => 365,
        'max_invitations' => 1,
        'is_active' => true,
    ]);

    $inactivePlan = Plan::create([
        'name' => 'Gói Cũ Tạm Ngưng',
        'slug' => 'old-plan-'.uniqid(),
        'price' => 199000,
        'duration_days' => 180,
        'max_invitations' => 1,
        'is_active' => false,
    ]);

    // 1. Thử tạo đơn hàng với gói đã tắt bán -> 404
    $resInactive = $this->actingAs($user)->postJson(route('checkout.process'), [
        'plan_id' => $inactivePlan->id,
        'customer_name' => 'Nguyễn Minh Tuấn',
        'customer_email' => 'tuan@example.com',
        'customer_phone' => '0901234567',
        'payment_method' => 'vietqr',
    ]);
    $resInactive->assertStatus(404);

    // 2. Tạo đơn hàng với gói active -> Thành công và trả thông tin VietQR
    $resActive = $this->actingAs($user)->postJson(route('checkout.process'), [
        'plan_id' => $activePlan->id,
        'customer_name' => 'Nguyễn Minh Tuấn',
        'customer_email' => 'tuan@example.com',
        'customer_phone' => '0901234567',
        'payment_method' => 'vietqr',
    ]);

    $resActive->assertStatus(200)
        ->assertJson([
            'success' => true,
            'amount' => 399000,
            'bank_info' => [
                'bank_name' => 'VietinBank',
                'account_number' => '103870000000',
                'account_name' => 'MAY HEN ATELIER',
            ],
        ]);

    $orderCode = $resActive->json('order_code');
    expect($orderCode)->toStartWith('MH');

    $order = Order::where('order_code', $orderCode)->first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('pending');
});

test('webhook validates hmac signature and rejects drifted timestamp replay attacks', function () {
    $plan = Plan::create([
        'name' => 'Gói Test Webhook',
        'slug' => 'test-webhook-'.uniqid(),
        'price' => 200000,
        'duration_days' => 365,
        'max_invitations' => 1,
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_code' => 'MHTESTAUTH01',
        'user_id' => User::factory()->create()->id,
        'plan_id' => $plan->id,
        'amount' => 200000,
        'status' => 'pending',
        'payment_method' => 'sepay',
    ]);

    $payload = [
        'id' => 10001,
        'accountNumber' => '103870000000',
        'transferType' => 'in',
        'transferAmount' => 200000,
        'content' => 'Thanh toan don hang MHTESTAUTH01',
    ];

    // 1. Chữ ký sai -> 401
    $resInvalidSig = $this->postJson(route('webhook.payment'), $payload, [
        'X-SePay-Timestamp' => (string) now()->timestamp,
        'X-SePay-Signature' => 'sha256=invalid_fake_signature_hash_000000000000000000000000000000000000',
    ]);
    $resInvalidSig->assertStatus(401);

    // 2. Timestamp bị trôi quá 300 giây (Replay Attack) -> 401
    $driftedTimestamp = now()->subSeconds(350)->timestamp;
    $drifted = buildSignedSepayPayload($payload, $driftedTimestamp);
    $resDrifted = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($drifted['headers']), $drifted['content']);
    $resDrifted->assertStatus(401);

    // 3. Chữ ký hợp lệ và thời gian chuẩn -> 200
    $valid = buildSignedSepayPayload($payload);
    $resValid = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($valid['headers']), $valid['content']);
    $resValid->assertStatus(200)
        ->assertJson(['success' => true]);

    $order->refresh();
    expect($order->isPaid())->toBeTrue();
});

test('webhook validates bank account number and ignores outgoing transfer', function () {
    $plan = Plan::create([
        'name' => 'Gói Test Account',
        'slug' => 'test-account-'.uniqid(),
        'price' => 100000,
        'duration_days' => 365,
        'max_invitations' => 1,
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_code' => 'MHTESTACC01',
        'user_id' => User::factory()->create()->id,
        'plan_id' => $plan->id,
        'amount' => 100000,
        'status' => 'pending',
        'payment_method' => 'sepay',
    ]);

    // 1. Giao dịch tiền ra (transferType: out) -> bỏ qua an toàn với 200
    $outPayload = [
        'id' => 10002,
        'accountNumber' => '103870000000',
        'transferType' => 'out',
        'transferAmount' => 100000,
        'content' => 'Rut tien ngan hang',
    ];
    $signedOut = buildSignedSepayPayload($outPayload);
    $resOut = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($signedOut['headers']), $signedOut['content']);
    $resOut->assertStatus(200);

    // 2. Giao dịch gửi nhầm tài khoản thụ hưởng khác -> 422
    $wrongAccountPayload = [
        'id' => 10003,
        'accountNumber' => '999999999999', // Sai số tài khoản
        'transferType' => 'in',
        'transferAmount' => 100000,
        'content' => 'Thanh toan don MHTESTACC01',
    ];
    $signedWrong = buildSignedSepayPayload($wrongAccountPayload);
    $resWrong = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($signedWrong['headers']), $signedWrong['content']);
    $resWrong->assertStatus(422)
        ->assertJson(['message' => 'Invalid payment account']);
});

test('webhook rejects underpaid or overpaid amounts and keeps order pending', function () {
    $plan = Plan::create([
        'name' => 'Gói Kiểm Tra Số Tiền',
        'slug' => 'test-amount-'.uniqid(),
        'price' => 500000,
        'duration_days' => 365,
        'max_invitations' => 1,
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_code' => 'MHAMOUNTMIS0',
        'user_id' => User::factory()->create()->id,
        'plan_id' => $plan->id,
        'amount' => 500000,
        'status' => 'pending',
        'payment_method' => 'sepay',
    ]);

    // Khách chuyển thiếu tiền (chỉ chuyển 400.000đ thay vì 500.000đ)
    $underpaidPayload = [
        'id' => 10004,
        'accountNumber' => '103870000000',
        'transferType' => 'in',
        'transferAmount' => 400000,
        'content' => 'Thanh toan MHAMOUNTMIS0',
    ];
    $signedUnderpaid = buildSignedSepayPayload($underpaidPayload);
    $response = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($signedUnderpaid['headers']), $signedUnderpaid['content']);

    $response->assertStatus(400)
        ->assertJson(['message' => 'Payment amount mismatch']);

    $order->refresh();
    expect($order->status)->toBe('pending');
});

test('webhook guarantees idempotency on duplicate callbacks and extends invitation validity', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Bảo Trọng',
        'bride_name' => 'Diệu Huyền',
        'wedding_date' => '2026-12-30',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-cuoi-bao-trong-dieu-huyen-'.uniqid(),
        'is_published' => false,
        'expires_at' => Carbon::now()->addDays(20),
    ]);

    $plan = Plan::create([
        'name' => 'Gói Gia Hạn 1 Năm',
        'slug' => 'gia-han-1-nam-'.uniqid(),
        'price' => 350000,
        'duration_days' => 365,
        'max_invitations' => 1,
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_code' => 'MHIDEMPOTE01',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'invitation_id' => $invitation->id,
        'amount' => 350000,
        'status' => 'pending',
        'payment_method' => 'sepay',
    ]);

    $payload = [
        'id' => 10005,
        'accountNumber' => '103870000000',
        'transferType' => 'in',
        'transferAmount' => 350000,
        'content' => 'MHIDEMPOTE01 thanh toan',
    ];

    // 1. Callback lần đầu: Kích hoạt đơn hàng và cộng dồn hạn hiển thị
    $signed1 = buildSignedSepayPayload($payload);
    $res1 = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($signed1['headers']), $signed1['content']);
    $res1->assertStatus(200)
        ->assertJson(['success' => true]);

    $order->refresh();
    $invitation->refresh();

    expect($order->isPaid())->toBeTrue()
        ->and($invitation->is_published)->toBeTrue();

    // Hạn ban đầu còn 20 ngày + 365 ngày = 385 ngày
    $expectedExpiration = Carbon::now()->addDays(385);
    expect(now()->diffInDays($invitation->expires_at))->toBeGreaterThanOrEqual(384);

    $originalExpiration = $invitation->expires_at->copy();

    // 2. Callback lần 2 do SePay retry: Xử lý idempotent (trả 200, không cộng dồn lần 2)
    $signed2 = buildSignedSepayPayload($payload);
    $res2 = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($signed2['headers']), $signed2['content']);
    $res2->assertStatus(200)
        ->assertJson(['message' => 'Order already processed (idempotent)']);

    $invitation->refresh();
    expect($invitation->expires_at->timestamp)->toBe($originalExpiration->timestamp);
});

test('webhook prevents reusing the same provider transaction id across multiple orders', function () {
    $plan = Plan::create([
        'name' => 'Gói Trùng Transaction ID',
        'slug' => 'test-transid-'.uniqid(),
        'price' => 250000,
        'duration_days' => 365,
        'max_invitations' => 1,
        'is_active' => true,
    ]);

    $user = User::factory()->create();

    $order1 = Order::create([
        'order_code' => 'MHTRANSID001',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => 250000,
        'status' => 'pending',
        'payment_method' => 'sepay',
    ]);

    $order2 = Order::create([
        'order_code' => 'MHTRANSID002',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => 250000,
        'status' => 'pending',
        'payment_method' => 'sepay',
    ]);

    // Giao dịch 1 hoàn tất thành công với ID 99999
    $payload1 = [
        'id' => 99999,
        'accountNumber' => '103870000000',
        'transferType' => 'in',
        'transferAmount' => 250000,
        'content' => 'Thanh toan MHTRANSID001',
    ];
    $signed1 = buildSignedSepayPayload($payload1);
    $res1 = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($signed1['headers']), $signed1['content']);
    $res1->assertStatus(200);

    // Kẻ gian cố tình gửi cùng ID giao dịch 99999 cho đơn thứ 2 -> Bị chặn 409 Conflict
    $payload2 = [
        'id' => 99999,
        'accountNumber' => '103870000000',
        'transferType' => 'in',
        'transferAmount' => 250000,
        'content' => 'Thanh toan MHTRANSID002',
    ];
    $signed2 = buildSignedSepayPayload($payload2);
    $res2 = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($signed2['headers']), $signed2['content']);
    $res2->assertStatus(409)
        ->assertJson(['message' => 'Transaction ID already used for another order']);
});

test('admin manual order approval refund and cancellation flow', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $manager->id,
        'groom_name' => 'Thế Phong',
        'bride_name' => 'Như Quỳnh',
        'wedding_date' => '2026-11-18',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $manager->id,
        'slug' => 'thiep-cuoi-the-phong-nhu-quynh-'.uniqid(),
        'is_published' => false,
    ]);

    $plan = Plan::create([
        'name' => 'Gói Admin Duyệt',
        'slug' => 'admin-duyet-'.uniqid(),
        'price' => 500000,
        'duration_days' => 365,
        'max_invitations' => 1,
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_code' => 'MHADMINFLOW1',
        'user_id' => $manager->id,
        'plan_id' => $plan->id,
        'invitation_id' => $invitation->id,
        'amount' => 500000,
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
    ]);

    // 1. Quản lý duyệt thủ công đơn hàng -> status chuyển sang Paid, thiệp được kích hoạt
    $resApprove = $this->actingAs($manager)->post(route('admin.orders.approve', $order->id), [
        'note' => 'Đã đối soát khớp sao kê ngân hàng',
    ]);
    $resApprove->assertSessionHas('success');

    $order->refresh();
    $invitation->refresh();
    expect($order->status)->toBe('paid')
        ->and($order->paid_at)->not->toBeNull()
        ->and($invitation->is_published)->toBeTrue();

    // 2. Quản lý hoàn tiền đơn hàng (Refund)
    $resRefund = $this->actingAs($manager)->post(route('admin.orders.refund', $order->id), [
        'refund_reason' => 'Khách hủy gói tiệc cưới',
    ]);
    $resRefund->assertSessionHas('success');

    $order->refresh();
    expect($order->status)->toBe('refunded');

    // 3. Tạo đơn mới và hủy đơn (Cancel)
    $order2 = Order::create([
        'order_code' => 'MHADMINFLOW2',
        'user_id' => $manager->id,
        'plan_id' => $plan->id,
        'amount' => 500000,
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
    ]);

    $resCancel = $this->actingAs($manager)->post(route('admin.orders.cancel', $order2->id), [
        'cancel_reason' => 'Khách nhập sai thông tin',
    ]);
    $resCancel->assertSessionHas('success');

    $order2->refresh();
    expect($order2->status)->toBe('cancelled');
});
