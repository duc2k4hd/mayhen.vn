<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Services\RealFunnelAnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-11-10 10:00:00');

    $this->admin = User::factory()->create([
        'email' => 'admin_funnel@mayhen.vn',
        'role' => 'admin',
    ]);

    $this->customer = User::factory()->create([
        'email' => 'customer_funnel@mayhen.vn',
        'role' => 'customer',
    ]);

    $this->plan = Plan::create([
        'name' => 'Gói Plus',
        'slug' => 'plus',
        'price' => 399000,
        'duration_days' => 180,
        'is_active' => true,
    ]);

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Thiệp Hoàng Gia',
        'slug' => 'mau-thiep-hoang-gia',
        'thumbnail_url' => '/assets/client/images/demo.jpg',
        'is_active' => true,
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

test('unauthenticated user is redirected to admin login when accessing funnel', function () {
    $response = $this->get(route('admin.intelligence.funnel'));
    $response->assertRedirect(route('admin.login'));
});

test('real funnel analytics measures all 5 stages accurately from live database', function () {
    $wedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Quốc Anh',
        'bride_name' => 'Mai Phương',
        'wedding_date' => now()->addDays(20),
    ]);

    // Chặng 1 & 2: 2 invitations với tổng view_count = 250
    Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thiep-quoc-anh-mai-phuong-1',
        'view_count' => 150,
        'is_published' => true,
    ]);
    Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thiep-quoc-anh-mai-phuong-2',
        'view_count' => 100,
        'is_published' => true,
    ]);

    // Chặng 3: 1 đơn hàng đã thanh toán
    Order::create([
        'order_code' => 'ORD-FUNNEL-001',
        'user_id' => $this->customer->id,
        'plan_id' => $this->plan->id,
        'amount' => 399000,
        'status' => 'paid',
        'payment_method' => 'vietqr',
        'paid_at' => now(),
    ]);

    // Chặng 4: 4 khách mời, 3 khách đã được gửi thiệp
    $g1 = Guest::create(['wedding_id' => $wedding->id, 'name' => 'Khách 1', 'is_sent' => true]);
    $g2 = Guest::create(['wedding_id' => $wedding->id, 'name' => 'Khách 2', 'is_sent' => true]);
    $g3 = Guest::create(['wedding_id' => $wedding->id, 'name' => 'Khách 3', 'is_sent' => true]);
    Guest::create(['wedding_id' => $wedding->id, 'name' => 'Khách 4', 'is_sent' => false]);

    // Chặng 5: 2 phản hồi RSVP (1 tham dự, 1 từ chối)
    GuestResponse::create([
        'wedding_id' => $wedding->id,
        'guest_id' => $g1->id,
        'guest_name' => 'Khách 1',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
    ]);
    GuestResponse::create([
        'wedding_id' => $wedding->id,
        'guest_id' => $g2->id,
        'guest_name' => 'Khách 2',
        'attending_status' => 'declined',
        'adults_count' => 0,
        'children_count' => 0,
    ]);

    $metrics = RealFunnelAnalyticsService::getGlobalFunnelMetrics();

    expect($metrics['stages']['views']['count'])->toBe(250);
    expect($metrics['stages']['invitations']['count'])->toBe(2);
    expect($metrics['stages']['paid_orders']['count'])->toBe(1);
    expect($metrics['stages']['sent_guests']['count'])->toBe(3);
    expect($metrics['stages']['sent_guests']['total_guests'])->toBe(4);
    expect($metrics['stages']['sent_guests']['conversion_rate_from_prev'])->toBe(75.0);
    expect($metrics['stages']['rsvp_responses']['count'])->toBe(2);
    expect($metrics['stages']['rsvp_responses']['attending_count'])->toBe(1);
    expect($metrics['stages']['rsvp_responses']['attending_rate'])->toBe(50.0);

    // Kiểm tra render trang Admin Funnel
    $response = $this->actingAs($this->admin)->get(route('admin.intelligence.funnel'));
    $response->assertOk();
    $response->assertSee('Phễu Chuyển Đổi Vận Hành Thực Tế');
    $response->assertSee('250');
    $response->assertSee('75% tỷ lệ phát hành trên danh bạ');
    $response->assertSee('Cam Kết Minh Bạch Vận Hành (Anti-Fake Metrics Standard)');
});

test('campaign preview renders real measured metrics and eliminates all hardcoded marketing rates', function () {
    $wedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Đức Minh',
        'bride_name' => 'Thanh Thảo',
        'wedding_date' => now()->addDays(15),
    ]);

    // Tạo 10 khách: 6 khách đã gửi thiệp (60%)
    for ($i = 1; $i <= 10; $i++) {
        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => "Quan Khách {$i}",
            'is_sent' => $i <= 6,
        ]);
    }

    // 3 phản hồi RSVP (trên 6 thiệp gửi = 50%), trong đó 2 khách tham dự (66.7%)
    GuestResponse::create([
        'wedding_id' => $wedding->id,
        'guest_name' => 'Quan Khách 1',
        'attending_status' => 'attending',
        'adults_count' => 1,
        'children_count' => 0,
    ]);
    GuestResponse::create([
        'wedding_id' => $wedding->id,
        'guest_name' => 'Quan Khách 2',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
    ]);
    GuestResponse::create([
        'wedding_id' => $wedding->id,
        'guest_name' => 'Quan Khách 3',
        'attending_status' => 'declined',
        'adults_count' => 0,
        'children_count' => 0,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.guests.campaign_preview', ['wedding_id' => $wedding->id]));

    $response->assertOk();
    // 1. Phải nhìn thấy dữ liệu thật
    $response->assertSee('Hiệu Suất Chiến Dịch Đo Lường Thực Tế');
    $response->assertSee('✓ Dữ Liệu Đo Thật 100%');
    $response->assertSee('60% (6/10 khách)');
    $response->assertSee('50% (3 phản hồi)');

    // 2. CẤM TUYỆT ĐỐI KHÔNG ĐƯỢC XUẤT HIỆN CÁC CON SỐ HARDCODE CŨ THEO LỖI ADM-NEXT-P2-09
    $response->assertDontSee('85% - 92%');
    $response->assertDontSee('tiết kiệm 95%');
    $response->assertDontSee('Tương Thích Thiết Bị 100%');
});

test('funnel analytics time filtering scopes metrics to selected date window', function () {
    $wedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Trọng',
        'bride_name' => 'Linh',
        'wedding_date' => now()->addDays(10),
    ]);

    // Đơn hàng cũ tạo cách đây 45 ngày
    Carbon::setTestNow(now()->subDays(45));
    Order::create([
        'order_code' => 'ORD-OLD-45D',
        'user_id' => $this->customer->id,
        'plan_id' => $this->plan->id,
        'amount' => 399000,
        'status' => 'paid',
        'payment_method' => 'vietqr',
    ]);

    // Đơn hàng mới tạo hôm nay
    Carbon::setTestNow('2026-11-10 10:00:00');
    Order::create([
        'order_code' => 'ORD-NEW-TODAY',
        'user_id' => $this->customer->id,
        'plan_id' => $this->plan->id,
        'amount' => 399000,
        'status' => 'paid',
        'payment_method' => 'vietqr',
    ]);

    // 1. Toàn thời gian: 2 đơn hàng
    $allTime = RealFunnelAnalyticsService::getGlobalFunnelMetrics();
    expect($allTime['stages']['paid_orders']['count'])->toBe(2);

    // 2. 30 ngày qua: chỉ có 1 đơn hàng
    $thirtyDays = RealFunnelAnalyticsService::getGlobalFunnelMetrics(now()->subDays(30), now());
    expect($thirtyDays['stages']['paid_orders']['count'])->toBe(1);
});
