<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Quản Trị Viên Cấp Cao',
        'email' => 'superadmin@mayhen.vn',
        'role' => 'admin',
    ]);

    $this->manager = User::factory()->create([
        'name' => 'Nguyễn Quản Lý',
        'email' => 'manager@mayhen.vn',
        'role' => 'manager',
    ]);

    $this->staff = User::factory()->create([
        'name' => 'Trần Nhân Viên',
        'email' => 'staff@mayhen.vn',
        'role' => 'staff',
    ]);

    $this->partner = User::factory()->create([
        'name' => 'Studio Hoàng Gia',
        'email' => 'partner@mayhen.vn',
        'role' => 'partner',
    ]);

    $this->customer = User::factory()->create([
        'name' => 'Nguyễn Khách Hàng',
        'email' => 'customer@mayhen.vn',
        'role' => 'customer',
    ]);

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu RBAC Test',
        'slug' => 'mau-rbac-test',
        'category' => 'luxury',
        'thumbnail_url' => '/mau-thiep/mau-rbac-test/assets/images/hero-couple.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Đức Anh',
        'bride_name' => 'Ngọc Mai',
        'wedding_date' => now()->addMonths(2),
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'duc-anh-ngoc-mai-rbac',
        'is_published' => true,
    ]);

    $this->plan = Plan::create([
        'slug' => 'test-plan',
        'name' => 'Gói Kiểm Thử',
        'price' => 199000,
        'duration_days' => 365,
        'max_invitations' => 1,
        'is_active' => true,
    ]);

    $this->order = Order::create([
        'order_code' => 'MHRBAC001',
        'user_id' => $this->customer->id,
        'plan_id' => $this->plan->id,
        'amount' => 199000,
        'status' => 'pending',
        'payment_method' => 'vietqr',
    ]);

    $this->wish = WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Khách Mời A',
        'message' => 'Chúc hai bạn hạnh phúc',
        'is_approved' => true,
    ]);
});

test('admin has full access to all sensitive management actions', function () {
    // 1. Lock invitation
    $this->actingAs($this->admin)
        ->post(route('admin.invitations.lock', $this->invitation->id), ['reason' => 'Vi phạm quy định'])
        ->assertRedirect();
    $config = $this->invitation->fresh()->custom_config;
    expect(! empty($config['is_locked']))->toBeTrue();

    // 2. Approve order (kèm chứng từ đối soát NXT-01)
    $this->actingAs($this->admin)
        ->post(route('admin.orders.approve', $this->order->id), [
            'reference_code' => 'FT_ADMIN_RBAC',
            'reconciliation_note' => 'Admin đối soát hợp lệ',
        ])
        ->assertRedirect();
    expect($this->order->fresh()->status)->toBe('paid');

    // 3. Delete wish
    $this->actingAs($this->admin)
        ->delete(route('admin.wishes.destroy', $this->wish->id))
        ->assertRedirect();
    expect(WeddingWish::find($this->wish->id))->toBeNull();
});

test('manager can perform operational actions but cannot change system settings or roles', function () {
    // 1. Manager can lock invitation
    $this->actingAs($this->manager)
        ->post(route('admin.invitations.lock', $this->invitation->id), ['reason' => 'Manager khóa'])
        ->assertRedirect();
    $config = $this->invitation->fresh()->custom_config;
    expect(! empty($config['is_locked']))->toBeTrue();

    // 2. Manager can approve order (kèm chứng từ đối soát NXT-01)
    $this->actingAs($this->manager)
        ->post(route('admin.orders.approve', $this->order->id), [
            'reference_code' => 'FT_MGR_RBAC',
            'reconciliation_note' => 'Manager đối soát hợp lệ',
        ])
        ->assertRedirect();
    expect($this->order->fresh()->status)->toBe('paid');

    // 3. Manager CANNOT change user role (Requires admin.super)
    $this->actingAs($this->manager)
        ->post(route('admin.users.change_role', $this->customer->id), ['role' => 'partner'])
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('error');
    expect($this->customer->fresh()->role)->toBe('customer');

    // 4. Manager CANNOT access settings (Requires admin.super)
    $this->actingAs($this->manager)
        ->get(route('admin.settings.plans'))
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('error');
});

test('staff can view dashboards and lists but CANNOT perform sensitive actions', function () {
    // 1. Staff can view dashboard, invitations, orders
    $this->actingAs($this->staff)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($this->staff)->get(route('admin.invitations.index'))->assertOk();
    $this->actingAs($this->staff)->get(route('admin.orders.index'))->assertOk();
    $this->actingAs($this->staff)->get(route('admin.users.index'))->assertOk();

    // 2. Staff CANNOT approve order (Requires admin.manager)
    $responseApprove = $this->actingAs($this->staff)
        ->post(route('admin.orders.approve', $this->order->id));
    $responseApprove->assertSessionHas('error');
    expect($this->order->fresh()->status)->toBe('pending');

    // 3. Staff CANNOT cancel order
    $responseCancel = $this->actingAs($this->staff)
        ->post(route('admin.orders.cancel', $this->order->id), ['reason' => 'Hủy']);
    $responseCancel->assertSessionHas('error');
    expect($this->order->fresh()->status)->toBe('pending');

    // 4. Staff CANNOT lock invitation
    $responseLock = $this->actingAs($this->staff)
        ->post(route('admin.invitations.lock', $this->invitation->id), ['reason' => 'Khóa']);
    $responseLock->assertSessionHas('error');
    $config = $this->invitation->fresh()->custom_config;
    expect(! empty($config['is_locked']))->toBeFalse();

    // 5. Staff CANNOT delete wish
    $responseDelete = $this->actingAs($this->staff)
        ->delete(route('admin.wishes.destroy', $this->wish->id));
    $responseDelete->assertSessionHas('error');
    expect(WeddingWish::find($this->wish->id))->not->toBeNull();
});

test('partner and regular customer are completely blocked from admin portal', function () {
    // Partner blocked
    $this->actingAs($this->partner)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));

    // Customer blocked
    $this->actingAs($this->customer)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));
});
