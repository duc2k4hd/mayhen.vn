<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Services\PostPaymentOnboardingService;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->plan = Plan::updateOrCreate(['slug' => 'pro'], [
        'name' => 'Trọn Vẹn (Wedding Pro)',
        'price' => 299000,
        'duration_days' => 730,
        'max_invitations' => 3,
        'is_active' => true,
    ]);

    $this->template = InvitationTemplate::create([
        'name' => 'Rose Elegance',
        'slug' => 'rose-elegance-'.uniqid(),
        'blade_view' => 'client.invitations.rose.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb.jpg',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Thanh Tùng',
        'bride_name' => 'Mai Lan',
        'wedding_date' => now()->addDays(30),
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'tung-lan-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
    ]);
});

it('renders first success onboarding portal for paid order with 3 steps guide', function () {
    $order = Order::create([
        'order_code' => 'MHTEST1234',
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'invitation_id' => $this->invitation->id,
        'amount' => 299000,
        'payment_method' => 'vietqr',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $response = $this->get(route('checkout.first_success', $order->order_code));

    $response->assertOk()
        ->assertSee('Kích Hoạt Gói Dịch Vụ Thành Công!')
        ->assertSee('Trọn Vẹn (Wedding Pro)')
        ->assertSee('3 Bước Khởi Đầu Hoàn Hảo Cho Ngày Trọng Đại')
        ->assertSee('730 ngày');
});

it('renders pending status with auto polling helper when order is not yet paid', function () {
    $order = Order::create([
        'order_code' => 'MHPENDING99',
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'invitation_id' => $this->invitation->id,
        'amount' => 299000,
        'payment_method' => 'vietqr',
        'status' => 'pending',
    ]);

    $response = $this->get(route('checkout.first_success', $order->order_code));

    $response->assertOk()
        ->assertSee('Đang Xác Nhận Giao Dịch Ngân Hàng')
        ->assertSee('data-is-paid="0"', false);
});

it('generates customized onboarding steps tailored to the purchased plan and attached invitation', function () {
    $order = Order::create([
        'order_code' => 'MHONBOARD01',
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'invitation_id' => $this->invitation->id,
        'amount' => 299000,
        'payment_method' => 'vietqr',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $service = app(PostPaymentOnboardingService::class);
    $guide = $service->getOnboardingGuide($order);

    expect($guide['onboarding_steps'])->toHaveCount(3)
        ->and($guide['onboarding_steps'][0]['action_url'])->toBe(route('invitation.show', $this->invitation->slug))
        ->and($guide['onboarding_steps'][1]['action_url'])->toBe(route('seating.index', $this->invitation->id))
        ->and($guide['onboarding_steps'][2]['action_url'])->toBe(route('privacy.show', $this->invitation->id));
});

it('returns 404 for non-existent order code', function () {
    $response = $this->get(route('checkout.first_success', 'INVALID_ORDER_XYZ'));

    $response->assertNotFound();
});

