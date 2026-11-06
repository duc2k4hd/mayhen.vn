<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;

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

test('a webhook is unavailable when its secret is not configured', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_token' => null]);

    $this->postJson(route('webhook.payment'), ['content' => $order->order_code, 'transferAmount' => $order->amount])
        ->assertStatus(503);
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
});

test('a webhook requires the exact configured SePay authorization header', function (?string $authorization) {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_token' => 'test-secret']);
    $headers = $authorization === null ? [] : ['Authorization' => $authorization];

    $this->postJson(route('webhook.payment'), [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
    ], $headers)->assertUnauthorized();
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
})->with([
    'missing header' => [null],
    'wrong key' => ['Apikey wrong-secret'],
    'substring only' => ['Apikey prefix-test-secret-suffix'],
    'wrong scheme' => ['Bearer test-secret'],
]);

test('a correctly authenticated webhook can mark a matching order paid', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_token' => 'test-secret']);

    $this->postJson(route('webhook.payment'), [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
        'transferType' => 'in',
    ], ['Authorization' => 'Apikey test-secret'])->assertOk()->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
});

test('outgoing transfers do not mark an order paid', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_token' => 'test-secret']);

    $this->postJson(route('webhook.payment'), [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
        'transferType' => 'out',
    ], ['Authorization' => 'Apikey test-secret'])->assertOk()->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
});

test('an authenticated webhook without a transfer type does not mark an order paid', function () {
    $order = makeCheckoutOrder();
    config(['services.sepay.webhook_token' => 'test-secret']);

    $this->postJson(route('webhook.payment'), [
        'content' => $order->order_code,
        'transferAmount' => $order->amount,
    ], ['Authorization' => 'Apikey test-secret'])->assertStatus(422);

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
});

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
