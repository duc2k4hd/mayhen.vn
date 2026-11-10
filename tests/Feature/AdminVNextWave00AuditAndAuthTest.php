<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use App\Services\AuditLogger;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

test('BUG-ADM-001 fix: staff role can successfully log in via admin login form', function () {
    $staff = User::factory()->create([
        'email' => 'staff.atelier@mayhen.vn',
        'password' => bcrypt('StaffPassword123!'),
        'role' => 'staff',
    ]);

    $res = $this->post(route('admin.login.submit'), [
        'email' => 'staff.atelier@mayhen.vn',
        'password' => 'StaffPassword123!',
    ]);

    $res->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($staff);

    // Verify root /admin redirects authenticated staff to dashboard
    $resRoot = $this->get(route('admin.index'));
    $resRoot->assertRedirect(route('admin.dashboard'));
});

test('BUG-ADM-001 defense: customer and partner roles are barred at login form with session destroyed', function () {
    $customer = User::factory()->create([
        'email' => 'customer.test@mayhen.vn',
        'password' => bcrypt('CustomerPass123!'),
        'role' => 'customer',
    ]);

    $resCustomer = $this->post(route('admin.login.submit'), [
        'email' => 'customer.test@mayhen.vn',
        'password' => 'CustomerPass123!',
    ]);

    $resCustomer->assertSessionHasErrors(['email']);
    $this->assertGuest();

    $partner = User::factory()->create([
        'email' => 'partner.studio@mayhen.vn',
        'password' => bcrypt('PartnerPass123!'),
        'role' => 'partner',
    ]);

    $resPartner = $this->post(route('admin.login.submit'), [
        'email' => 'partner.studio@mayhen.vn',
        'password' => 'PartnerPass123!',
    ]);

    $resPartner->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

test('BUG-ADM-002 fix: system prevents demoting the last super admin', function () {
    // Delete any other admin accounts so only 1 exists
    User::where('role', 'admin')->delete();

    $soleSuperAdmin = User::factory()->create([
        'email' => 'sole.admin@mayhen.vn',
        'role' => 'admin',
    ]);

    // Another admin impersonated or action
    $otherSuperAdmin = User::factory()->create([
        'email' => 'second.admin@mayhen.vn',
        'role' => 'admin',
    ]);

    // When 2 admins exist, demoting one is allowed (if not self)
    $resDemoteOne = $this->actingAs($otherSuperAdmin)->post(route('admin.users.change_role', $soleSuperAdmin->id), [
        'role' => 'manager',
    ]);
    $resDemoteOne->assertSessionHas('success');
    expect($soleSuperAdmin->fresh()->role)->toBe('manager');

    // Now otherSuperAdmin is the ONLY remaining super admin. Attempting to demote them should fail!
    $resDemoteLast = $this->actingAs($otherSuperAdmin)->post(route('admin.users.change_role', $otherSuperAdmin->id), [
        'role' => 'staff',
    ]);
    $resDemoteLast->assertSessionHas('error');
    expect($otherSuperAdmin->fresh()->role)->toBe('admin');
});

test('BUG-ADM-003 fix: manual order approval records reconciliation evidence and audit trail', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $customer = User::factory()->create(['role' => 'customer']);
    $plan = Plan::firstOrCreate(
        ['slug' => 'plus'],
        [
            'name' => 'Gói Plus Hoàng Kim',
            'price' => 299000,
            'duration_days' => 365,
            'max_invitations' => 1,
            'is_active' => true,
        ]
    );

    $template = InvitationTemplate::where('is_active', true)->first();
    $wedding = Wedding::create([
        'user_id' => $customer->id,
        'groom_name' => 'Minh Đức',
        'bride_name' => 'Thu Huyền',
        'wedding_date' => '2026-12-25',
    ]);
    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $customer->id,
        'slug' => 'thiep-reconcile-test-'.uniqid(),
        'revision_id' => 0,
        'is_published' => false,
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'plan_id' => $plan->id,
        'invitation_id' => $invitation->id,
        'order_code' => 'MH'.now()->format('ymd').strtoupper(uniqid()),
        'amount' => 299000,
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
    ]);

    $resApprove = $this->actingAs($manager)->post(route('admin.orders.approve', $order->id), [
        'reference_code' => 'FT26310294829',
        'reconciliation_note' => 'Khớp sao kê VietinBank 299.000đ từ NGUYEN VAN A',
    ]);

    $resApprove->assertSessionHas('success');
    $order->refresh();
    $invitation->refresh();

    expect($order->status)->toBe('paid')
        ->and($order->paid_at)->not->toBeNull()
        ->and($order->provider_transaction_id)->toBe('FT26310294829')
        ->and($invitation->is_published)->toBeTrue();
});

test('BUG-ADM-004 fix: AuditLogger automatically redacts sensitive credentials and logs auth events', function () {
    $sensitiveData = [
        'email' => 'admin@mayhen.vn',
        'password' => 'SuperSecretPass123!',
        'password_confirmation' => 'SuperSecretPass123!',
        'api_token' => 'tok_live_123456789',
        'webhook_secret' => 'whsec_99887766',
        'nested' => [
            'card_number' => '4111222233334444',
            'bank_account' => '103870000000',
            'normal_note' => 'Ghi chú an toàn',
        ],
    ];

    $sanitized = AuditLogger::sanitizeDetails($sensitiveData);

    expect($sanitized['password'])->toBe('***REDACTED***')
        ->and($sanitized['password_confirmation'])->toBe('***REDACTED***')
        ->and($sanitized['api_token'])->toBe('***REDACTED***')
        ->and($sanitized['webhook_secret'])->toBe('***REDACTED***')
        ->and($sanitized['nested']['card_number'])->toBe('***REDACTED***')
        ->and($sanitized['nested']['bank_account'])->toBe('***REDACTED***')
        ->and($sanitized['nested']['normal_note'])->toBe('Ghi chú an toàn')
        ->and($sanitized['email'])->toBe('admin@mayhen.vn');

    // Test sign-in and sign-out audit events
    $admin = User::factory()->create([
        'email' => 'admin.audit@mayhen.vn',
        'password' => bcrypt('AdminPass123!'),
        'role' => 'admin',
    ]);

    $this->post(route('admin.login.submit'), [
        'email' => 'admin.audit@mayhen.vn',
        'password' => 'AdminPass123!',
    ]);

    $this->actingAs($admin)->post(route('admin.logout'));

    $recentLogs = AuditLogger::getRecentLogs(20);
    $actions = array_column($recentLogs, 'action');

    expect($actions)->toContain('admin_login')
        ->and($actions)->toContain('admin_logout');
});

test('Wave 02: Admin master layout renders command palette and real-time operational notification badges', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);
    $plan = Plan::firstOrCreate(
        ['slug' => 'plus'],
        [
            'name' => 'Gói Plus Hoàng Kim',
            'price' => 299000,
            'duration_days' => 365,
            'max_invitations' => 1,
            'is_active' => true,
        ]
    );

    $wedding = Wedding::create([
        'user_id' => $customer->id,
        'groom_name' => 'Thành Nam',
        'bride_name' => 'Bảo Trân',
        'wedding_date' => '2026-11-20',
    ]);

    // Create 2 pending orders
    Order::create([
        'user_id' => $customer->id,
        'plan_id' => $plan->id,
        'order_code' => 'MH'.now()->format('ymd').'PEND1',
        'amount' => 299000,
        'status' => 'pending',
    ]);
    Order::create([
        'user_id' => $customer->id,
        'plan_id' => $plan->id,
        'order_code' => 'MH'.now()->format('ymd').'PEND2',
        'amount' => 299000,
        'status' => 'pending',
    ]);

    // Create 3 unapproved wishes
    WeddingWish::create([
        'wedding_id' => $wedding->id,
        'name' => 'Khách Mời 1',
        'message' => 'Chúc mừng hai bạn trăm năm hạnh phúc!',
        'is_approved' => false,
    ]);
    WeddingWish::create([
        'wedding_id' => $wedding->id,
        'name' => 'Khách Mời 2',
        'message' => 'Chúc hai bạn sớm sinh quý tử!',
        'is_approved' => false,
    ]);
    WeddingWish::create([
        'wedding_id' => $wedding->id,
        'name' => 'Khách Mời 3',
        'message' => 'Happy wedding!',
        'is_approved' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    $response->assertStatus(200);

    // Verify Command Palette triggers and items exist in the HTML
    $response->assertSee('mayhen_admin_command_trigger', false);
    $response->assertSee('mayhen_admin_command_palette', false);
    $response->assertSee('Ctrl K', false);

    // Verify operational notification badges render real counts
    $response->assertSee('title="2 đơn hàng chờ đối soát"', false);
    $response->assertSee('title="3 lời chúc chờ duyệt"', false);
});
