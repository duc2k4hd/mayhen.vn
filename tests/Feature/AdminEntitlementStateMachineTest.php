<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;
use App\Services\EntitlementStateMachineService;

beforeEach(function () {
    // Đảm bảo có các gói cước chuẩn
    Plan::firstOrCreate(['slug' => 'free'], [
        'name' => 'Trải Nghiệm (Free)',
        'price' => 0,
        'duration_days' => 7,
        'max_invitations' => 1,
        'features' => ['Tạo 1 thiệp cưới cơ bản'],
        'is_active' => true,
    ]);

    Plan::firstOrCreate(['slug' => 'plus'], [
        'name' => 'Hạnh Phúc (Plus)',
        'price' => 299000,
        'duration_days' => 180,
        'max_invitations' => 1,
        'features' => ['Ảnh bàn tiệc', 'Nhạc nền', 'RSVP không giới hạn'],
        'is_active' => true,
    ]);

    Plan::firstOrCreate(['slug' => 'pro'], [
        'name' => 'Vương Giả (Pro)',
        'price' => 599000,
        'duration_days' => 365,
        'max_invitations' => 1,
        'features' => ['Tên miền riêng', 'Bỏ nhãn Mây Hẹn', 'Đầy đủ mọi tính năng'],
        'is_active' => true,
    ]);

    $this->template = InvitationTemplate::firstOrCreate(
        ['slug' => 'hanh-phuc-mang-ten-chung-minh'],
        [
            'name' => 'Hạnh Phúc Mang Tên Chúng Mình',
            'category' => 'luxury',
            'thumbnail_url' => '/mau-thiep/hanh-phuc-mang-ten-chung-minh/assets/thumbnail.webp',
            'is_active' => true,
            'is_featured' => true,
        ]
    );
});

test('entitlement state machine correctly resolves all 5 lifecycle states', function () {
    $user = User::factory()->create();
    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Minh',
        'bride_name' => 'Hà',
        'wedding_date' => now()->addDays(30)->format('Y-m-d'),
    ]);

    // 1. STATE_TRIAL: Mới tạo, chưa thanh toán
    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thiep-trial-test',
        'title' => 'Thiệp Trial',
        'expires_at' => now()->addDays(7),
        'is_published' => true,
    ]);

    expect(EntitlementStateMachineService::getSubscriptionState($invitation))
        ->toBe(EntitlementStateMachineService::STATE_TRIAL);

    // 2. STATE_ACTIVE: Đã thanh toán đơn hàng, hạn dùng tương lai
    $plusPlan = Plan::where('slug', 'plus')->firstOrFail();
    $order = Order::create([
        'user_id' => $user->id,
        'invitation_id' => $invitation->id,
        'plan_id' => $plusPlan->id,
        'order_code' => 'ORD-ACTIVE-001',
        'amount' => 299000,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $invitation->update(['expires_at' => now()->addDays(60)]);
    expect(EntitlementStateMachineService::getSubscriptionState($invitation))
        ->toBe(EntitlementStateMachineService::STATE_ACTIVE);

    // 3. STATE_GRACE_PERIOD: Quá hạn nhưng trong vòng 7 ngày sau cưới
    $invitation->update(['expires_at' => now()->subDays(3)]);
    expect(EntitlementStateMachineService::getSubscriptionState($invitation))
        ->toBe(EntitlementStateMachineService::STATE_GRACE_PERIOD);

    // 4. STATE_EXPIRED: Quá hạn trên 7 ngày
    $invitation->update(['expires_at' => now()->subDays(15)]);
    expect(EntitlementStateMachineService::getSubscriptionState($invitation))
        ->toBe(EntitlementStateMachineService::STATE_EXPIRED);

    // 5. STATE_REFUNDED: Đơn hàng bị hoàn tiền
    $order->update(['status' => 'refunded', 'refunded_at' => now()]);
    expect(EntitlementStateMachineService::getSubscriptionState($invitation))
        ->toBe(EntitlementStateMachineService::STATE_REFUNDED);
});

test('entitlement catalog strictly controls feature access by active plan', function () {
    $user = User::factory()->create();
    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Tuấn',
        'bride_name' => 'Lan',
        'wedding_date' => now()->addDays(30)->format('Y-m-d'),
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thiep-plan-matrix',
        'title' => 'Thiệp Plan Matrix',
        'expires_at' => now()->addDays(60),
        'is_published' => true,
    ]);

    // Gói Free / Trial: Không có quyền cao cấp
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'remove_branding'))->toBeFalse();
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'custom_domain'))->toBeFalse();
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'live_guest_photo_drop'))->toBeFalse();

    // Nâng lên gói Plus
    $plusPlan = Plan::where('slug', 'plus')->firstOrFail();
    $order = Order::create([
        'user_id' => $user->id,
        'invitation_id' => $invitation->id,
        'plan_id' => $plusPlan->id,
        'order_code' => 'ORD-PLUS-001',
        'amount' => 299000,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'live_guest_photo_drop'))->toBeTrue();
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'background_music'))->toBeTrue();
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'remove_branding'))->toBeFalse();
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'custom_domain'))->toBeFalse();

    // Nâng lên gói Pro
    $proPlan = Plan::where('slug', 'pro')->firstOrFail();
    $order->update(['plan_id' => $proPlan->id]);

    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'remove_branding'))->toBeTrue();
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'custom_domain'))->toBeTrue();
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'live_guest_photo_drop'))->toBeTrue();
});

test('admin can view entitlements tab in invitation show page', function () {
    $superAdmin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Đức',
        'bride_name' => 'Nhi',
        'wedding_date' => now()->addDays(20)->format('Y-m-d'),
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thiep-show-entitlements',
        'title' => 'Thiệp Show Entitlements',
        'expires_at' => now()->addDays(30),
        'is_published' => true,
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('admin.invitations.show', $invitation->id));

    $response->assertStatus(200);
    $response->assertSee('tab-entitlements');
    $response->assertSee('Trạng Thái Gói Cước &amp; Vòng Đời Đăng Ký', false);
    $response->assertSee('Ma Trận Cấp Quyền &amp; Tính Năng Độc Bản', false);
    $response->assertSee('Bỏ Nhãn Hiệu Mây Hẹn (Zero Branding)');
    $response->assertSee('Tên Miền Riêng Độc Bản (Custom Domain)');
});

test('manager can override entitlement with audit logging while staff is forbidden', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $staff = User::factory()->create(['role' => 'staff']);
    $user = User::factory()->create();
    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Khoa',
        'bride_name' => 'Yến',
        'wedding_date' => now()->addDays(15)->format('Y-m-d'),
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thiep-override-entitlements',
        'title' => 'Thiệp Override',
        'expires_at' => now()->addDays(30),
        'is_published' => true,
    ]);

    // Mặc định: Gói Free không có remove_branding
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'remove_branding'))->toBeFalse();

    // 1. Staff cố gắng can thiệp -> Bị chặn (Redirect kèm thông báo lỗi hoặc 403 khi dùng JSON)
    $staffResponse = $this->actingAs($staff)
        ->post(route('admin.invitations.override_entitlement', $invitation->id), [
            'feature_key' => 'remove_branding',
            'action' => 'grant',
            'reason' => 'Staff cố ý can thiệp không có thẩm quyền',
        ]);
    $staffResponse->assertRedirect();
    $staffResponse->assertSessionHas('error');
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'remove_branding'))->toBeFalse();

    $staffJsonResponse = $this->actingAs($staff)
        ->postJson(route('admin.invitations.override_entitlement', $invitation->id), [
            'feature_key' => 'remove_branding',
            'action' => 'grant',
            'reason' => 'Staff cố ý can thiệp không có thẩm quyền',
        ]);
    $staffJsonResponse->assertStatus(403);

    // 2. Manager can thiệp cấp quyền ngoại lệ hợp lệ
    $managerResponse = $this->actingAs($manager)
        ->post(route('admin.invitations.override_entitlement', $invitation->id), [
            'feature_key' => 'remove_branding',
            'action' => 'grant',
            'reason' => 'Khách hàng VIP trải nghiệm đặc biệt theo phê duyệt Ban Giám đốc',
        ]);

    $managerResponse->assertRedirect();
    $managerResponse->assertSessionHas('success');

    $invitation->refresh();
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'remove_branding'))->toBeTrue();

    // Kiểm tra Audit Log được ghi nhận đầy đủ
    $recentLogs = AuditLogger::getRecentLogs(20, 'override_entitlement');
    $overrideLog = $recentLogs[0] ?? null;

    expect($overrideLog)->not->toBeNull();
    expect($overrideLog['description'])->toContain('Cấp ngoại lệ tính năng [Bỏ Nhãn Hiệu Mây Hẹn (Zero Branding)]');

    // 3. Manager đặt lại về mặc định (Reset)
    $resetResponse = $this->actingAs($manager)
        ->post(route('admin.invitations.override_entitlement', $invitation->id), [
            'feature_key' => 'remove_branding',
            'action' => 'reset',
            'reason' => 'Kết thúc thời gian trải nghiệm VIP',
        ]);

    $resetResponse->assertRedirect();
    $invitation->refresh();
    expect(EntitlementStateMachineService::canAccessFeature($invitation, 'remove_branding'))->toBeFalse();
});
