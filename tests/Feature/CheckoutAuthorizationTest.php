<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    config([
        'services.sepay.bank_code' => 'MB',
        'services.sepay.bank_name' => 'MB Bank',
        'services.sepay.bank_account' => '0012345678',
        'services.sepay.bank_account_name' => 'TEST ACCOUNT',
    ]);
});

function makeCheckoutOrder(): Order
{
    $plan = Plan::create(['slug' => 'webhook-test', 'name' => 'Gói kiểm thử', 'price' => 100000]);

    return Order::create([
        'order_code' => 'MHTEST0001',
        'user_id' => User::factory()->create()->id,
        'plan_id' => $plan->id,
        'amount' => $plan->price,
        'status' => 'pending',
        'payment_method' => 'sepay',
    ]);
}

function makeCheckoutInvitation(User $owner): Invitation
{
    $template = InvitationTemplate::create([
        'slug' => 'checkout-template',
        'name' => 'Mẫu kiểm thử',
        'thumbnail_url' => '/test-thumbnail.webp',
    ]);
    $wedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Minh',
        'bride_name' => 'An',
        'wedding_date' => '2027-01-01',
    ]);

    return Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'checkout-test-invitation',
    ]);
}

function checkoutInput(Plan $plan, ?Invitation $invitation = null): array
{
    return [
        'plan_id' => $plan->id,
        'invitation_id' => $invitation?->id,
        'customer_name' => 'Khách kiểm thử',
        'customer_email' => 'customer@example.test',
        'customer_phone' => '0900000000',
        'payment_method' => 'sepay',
    ];
}

function sepayHeaders(array $payload, ?int $timestamp = null, string $secret = 'test-secret'): array
{
    $timestamp = (string) ($timestamp ?? now()->timestamp);
    $body = json_encode($payload);

    return [
        'X-SePay-Timestamp' => $timestamp,
        'X-SePay-Signature' => 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $secret),
    ];
}

test('a webhook is unavailable when its secret is not configured', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => null]);

    $this->postJson(route('webhook.payment'), ['content' => $order->order_code, 'transferAmount' => $order->amount])
        ->assertStatus(503);
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
});

test('an admin cannot mark an order paid without payment verification', function () {
    $invitation = makeCheckoutInvitation(User::factory()->create());
    $order = makeCheckoutOrder();
    $order->update(['invitation_id' => $invitation->id]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post('/admin/orders/'.$order->id.'/mark-paid')->assertNotFound();

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending', 'paid_at' => null]);
    $this->assertDatabaseHas('invitations', ['id' => $invitation->id, 'is_published' => false]);
});

test('a webhook rejects missing invalid or expired HMAC credentials', function (string $variant) {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => 'test-secret']);
    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
        'transferType' => 'in',
    ];
    $headers = sepayHeaders($payload);
    if ($variant === 'missing timestamp') {
        unset($headers['X-SePay-Timestamp']);
    } elseif ($variant === 'missing signature') {
        unset($headers['X-SePay-Signature']);
    } elseif ($variant === 'wrong secret') {
        $headers = sepayHeaders($payload, secret: 'wrong-secret');
    } elseif ($variant === 'expired') {
        $headers = sepayHeaders($payload, now()->timestamp - 301);
    } elseif ($variant === 'future timestamp') {
        $headers = sepayHeaders($payload, now()->timestamp + 301);
    } elseif ($variant === 'malformed signature') {
        $headers['X-SePay-Signature'] = 'sha256=invalid';
    } elseif ($variant === 'tampered body') {
        $payload['transferAmount']++;
    }

    $this->postJson(route('webhook.payment'), $payload, $headers)->assertUnauthorized();
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
})->with([
    'missing timestamp',
    'missing signature',
    'wrong secret',
    'expired',
    'future timestamp',
    'malformed signature',
    'tampered body',
]);

test('a new order uses the configured ten-character payment suffix', function () {
    $plan = Plan::create(['slug' => 'code-test', 'name' => 'Gói kiểm thử', 'price' => 100000]);

    $response = $this->postJson(route('checkout.process'), checkoutInput($plan))->assertOk();

    expect($response->json('order_code'))->toMatch('/\AMH[A-Z0-9]{10}\z/');
    expect($response->json('bank_info.account_number'))->toBe('0012345678');
    expect($response->json('vietqr_url'))->toContain('/MB-0012345678-compact2.png?');
    $this->assertDatabaseHas('orders', ['order_code' => $response->json('order_code'), 'status' => 'pending']);
});

test('checkout does not create an order if the payment bank account is missing', function () {
    $plan = Plan::create(['slug' => 'bank-missing', 'name' => 'Gói kiểm thử', 'price' => 100000]);
    config(['services.sepay.bank_account' => null]);

    $this->postJson(route('checkout.process'), checkoutInput($plan))->assertStatus(503);
    $this->assertDatabaseCount('orders', 0);
});

test('the webhook recognizes new and legacy payment codes', function (string $code) {
    $order = makeCheckoutOrder();
    $order->update(['order_code' => $code]);
    config(['services.sepay.webhook_secret' => 'test-secret']);
    $payload = [
        'content' => 'Thanh toan '.$code,
        'transferAmount' => $order->amount,
        'transferType' => 'in',
        'accountNumber' => '0012345678',
    ];

    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))
        ->assertOk()->assertJson(['success' => true]);
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
})->with(['MHTEST0001', 'MHTEST000001']);

test('the webhook does not match a payment code inside a longer code', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => 'test-secret']);
    $payload = [
        'content' => $order->order_code.'XYZ',
        'transferAmount' => $order->amount,
        'transferType' => 'in',
        'accountNumber' => '0012345678',
    ];

    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))
        ->assertOk()->assertJson(['success' => false]);
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
});

test('the webhook accepts the payment code extracted by SePay', function () {
    $order = makeCheckoutOrder();
    $order->update(['order_code' => 'MHTEST000001']);
    config(['services.sepay.webhook_secret' => 'test-secret']);
    $payload = [
        'code' => $order->order_code,
        'content' => 'Thong tin chuyen khoan',
        'transferAmount' => $order->amount,
        'transferType' => 'in',
        'accountNumber' => '0012345678',
    ];

    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))
        ->assertOk()->assertJson(['success' => true]);
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
});

test('the legacy webhook route also requires a valid HMAC signature', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => 'test-secret']);
    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
        'transferType' => 'in',
        'accountNumber' => '0012345678',
    ];

    $this->postJson(route('webhook.payment.legacy'), $payload)->assertUnauthorized();
    $this->postJson(route('webhook.payment.legacy'), $payload, sepayHeaders($payload))
        ->assertOk()->assertJson(['success' => true]);
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
});

test('a correctly authenticated webhook can mark a matching order paid', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => 'test-secret']);

    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
        'transferType' => 'in',
        'accountNumber' => '0012345678',
    ];
    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertOk()->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
});

test('a webhook cannot pay an order without the configured receiving account', function (mixed $account) {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => 'test-secret']);
    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
        'transferType' => 'in',
        'accountNumber' => $account,
    ];

    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertStatus(422);
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
})->with([null, '12345678', '0099999999', 12345678]);

test('a webhook is unavailable when the receiving account is not configured', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => 'test-secret', 'services.sepay.bank_account' => null]);
    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
        'transferType' => 'in',
        'accountNumber' => '0012345678',
    ];

    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertStatus(503);
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
});

test('a repeated webhook does not extend the invitation again', function () {
    $invitation = makeCheckoutInvitation(User::factory()->create());
    $order = makeCheckoutOrder();
    $order->update(['invitation_id' => $invitation->id]);
    config(['services.sepay.webhook_secret' => 'test-secret']);
    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
        'transferType' => 'in',
        'accountNumber' => '0012345678',
    ];
    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertOk()->assertJson(['success' => true]);
    $expiry = $invitation->fresh()->expires_at->toIso8601String();
    $paidAt = $order->fresh()->paid_at->toIso8601String();

    $this->travel(2)->days();
    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertOk()->assertJson(['success' => true]);

    expect($invitation->fresh()->expires_at->toIso8601String())->toBe($expiry);
    expect($order->fresh()->paid_at->toIso8601String())->toBe($paidAt);
});

test('a webhook cannot mark a non-pending order paid', function (string $status) {
    $order = makeCheckoutOrder();
    $order->update(['status' => $status]);
    config(['services.sepay.webhook_secret' => 'test-secret']);

    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
        'transferType' => 'in',
        'accountNumber' => '0012345678',
    ];
    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertStatus(409);

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => $status, 'paid_at' => null]);
})->with(['failed', 'refunded']);

test('an insufficient transfer leaves the order pending', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => 'test-secret']);

    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $order->amount - 1,
        'transferType' => 'in',
        'accountNumber' => '0012345678',
    ];
    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertStatus(400);

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending', 'paid_at' => null]);
});

test('an overpayment leaves the order pending for reconciliation', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => 'test-secret']);

    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $order->amount + 1,
        'transferType' => 'in',
        'accountNumber' => '0012345678',
    ];
    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertStatus(400);

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending', 'paid_at' => null]);
});

test('an invitation update failure rolls back the payment status', function () {
    $invitation = makeCheckoutInvitation(User::factory()->create());
    $order = makeCheckoutOrder();
    $order->update(['invitation_id' => $invitation->id]);
    config(['services.sepay.webhook_secret' => 'test-secret']);
    $eventName = 'eloquent.updating: '.Invitation::class;
    Event::listen($eventName, fn () => throw new RuntimeException('Simulated invitation update failure'));

    try {
        $payload = [
            'content' => $order->order_code,
            'transferAmount' => $order->amount,
            'transferType' => 'in',
            'accountNumber' => '0012345678',
        ];
        $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertInternalServerError();
    } finally {
        Event::forget($eventName);
    }

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending', 'paid_at' => null]);
    $this->assertDatabaseHas('invitations', ['id' => $invitation->id, 'is_published' => false]);
});

test('outgoing transfers do not mark an order paid', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => 'test-secret']);

    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
        'transferType' => 'out',
    ];
    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertOk()->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
});

test('an authenticated webhook without a transfer type does not mark an order paid', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => 'test-secret']);

    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
    ];
    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertStatus(422);

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
});

test('a webhook with invalid payment data does not mark an order paid', function (mixed $amount) {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_secret' => 'test-secret']);

    $payload = [
        'content' => $order->order_code,
        'transferAmount' => $amount,
        'transferType' => 'in',
        'accountNumber' => '0012345678',
    ];
    $this->postJson(route('webhook.payment'), $payload, sepayHeaders($payload))->assertStatus(422);

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending', 'paid_at' => null]);
})->with([
    'missing amount' => [null],
    'zero amount' => [0],
    'boolean amount' => [true],
    'decimal amount' => ['100000.50'],
    'non-numeric amount' => ['invalid'],
]);

test('a customer cannot attach another owners invitation to an order', function () {
    $invitation = makeCheckoutInvitation(User::factory()->create());
    $plan = Plan::create(['slug' => 'checkout-test', 'name' => 'Gói kiểm thử', 'price' => 100000]);

    $this->actingAs(User::factory()->create())
        ->postJson(route('checkout.process'), checkoutInput($plan, $invitation))
        ->assertNotFound();

    $this->assertDatabaseCount('orders', 0);
});

test('an invitation owner can attach their invitation to an order', function () {
    $owner = User::factory()->create();
    $invitation = makeCheckoutInvitation($owner);
    $plan = Plan::create(['slug' => 'checkout-test', 'name' => 'Gói kiểm thử', 'price' => 100000]);

    $this->actingAs($owner)->postJson(route('checkout.process'), checkoutInput($plan, $invitation))
        ->assertOk()->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', ['invitation_id' => $invitation->id, 'user_id' => $owner->id, 'status' => 'pending']);
});

test('a guest with the invitation owner session can attach that invitation', function () {
    $invitation = makeCheckoutInvitation(User::factory()->create());
    $plan = Plan::create(['slug' => 'checkout-test', 'name' => 'Gói kiểm thử', 'price' => 100000]);

    $this->withSession(['owned_invitation_slugs' => [$invitation->slug]])
        ->postJson(route('checkout.process'), checkoutInput($plan, $invitation))
        ->assertOk()->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', ['invitation_id' => $invitation->id, 'status' => 'pending']);
});

test('a customer cannot create an order for an inactive plan', function () {
    $plan = Plan::create(['slug' => 'inactive-test', 'name' => 'Gói ngừng bán', 'price' => 100000, 'is_active' => false]);

    $this->postJson(route('checkout.process'), checkoutInput($plan))->assertNotFound();
    $this->assertDatabaseCount('orders', 0);
});
