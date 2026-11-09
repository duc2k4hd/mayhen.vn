<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

test('unauthenticated users and customers are barred from admin portal', function () {
    // 1. Unauthenticated guest
    $resGuest = $this->get(route('admin.dashboard'));
    $resGuest->assertRedirect(route('admin.login'));

    // 2. Customer user
    $customer = User::factory()->create(['role' => 'customer']);
    $resCustomer = $this->actingAs($customer)->get(route('admin.dashboard'));
    $resCustomer->assertRedirect(route('admin.login'))
        ->assertSessionHasErrors(['email']);

    // 3. Partner user
    $partner = User::factory()->create(['role' => 'partner']);
    $resPartner = $this->actingAs($partner)->get(route('admin.dashboard'));
    $resPartner->assertRedirect(route('admin.login'))
        ->assertSessionHasErrors(['email']);
});

test('staff role can access dashboard and read-only views but is denied manager and super actions', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $staff->id,
        'groom_name' => 'Thành Nam',
        'bride_name' => 'Bảo Trân',
        'wedding_date' => '2026-11-20',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $staff->id,
        'slug' => 'thiep-cuoi-staff-test-'.uniqid(),
        'revision_id' => 0,
        'is_published' => true,
    ]);

    // 1. Allowed to view dashboard, invitations, templates, users, wishes
    $this->actingAs($staff)->get(route('admin.dashboard'))->assertStatus(200);
    $this->actingAs($staff)->get(route('admin.invitations.index'))->assertStatus(200);
    $this->actingAs($staff)->get(route('admin.templates.index'))->assertStatus(200);
    $this->actingAs($staff)->get(route('admin.users.index'))->assertStatus(200);
    $this->actingAs($staff)->get(route('admin.wishes.index'))->assertStatus(200);

    // 2. Denied manager action: locking invitation (JSON 403 or redirect with error)
    $resLock = $this->actingAs($staff)->postJson(route('admin.invitations.lock', $invitation->id), [
        'reason' => 'Staff trying to lock',
    ]);
    $resLock->assertStatus(403);

    // 3. Denied super action: settings banking
    $resBanking = $this->actingAs($staff)->get(route('admin.settings.banking'));
    $resBanking->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('error');
});

test('manager role can lock unlock invitations and approve orders but is denied super admin settings', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $manager->id,
        'groom_name' => 'Quốc Cường',
        'bride_name' => 'Ngọc Diệp',
        'wedding_date' => '2026-12-10',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $manager->id,
        'slug' => 'thiep-cuoi-manager-lock-'.uniqid(),
        'revision_id' => 0,
    ]);

    // 1. Manager locks invitation
    $resLock = $this->actingAs($manager)->post(route('admin.invitations.lock', $invitation->id), [
        'reason' => 'Nghi vấn vi phạm điều khoản dịch vụ',
    ]);
    $resLock->assertSessionHas('success');

    $invitation->refresh();
    expect($invitation->custom_config['is_locked'])->toBeTrue()
        ->and($invitation->custom_config['lock_reason'])->toBe('Nghi vấn vi phạm điều khoản dịch vụ');

    // 2. Manager unlocks invitation
    $resUnlock = $this->actingAs($manager)->post(route('admin.invitations.unlock', $invitation->id));
    $resUnlock->assertSessionHas('success');

    $invitation->refresh();
    expect($invitation->custom_config)->not->toHaveKey('is_locked');

    // 3. Manager blocked from super admin action: change-role
    $targetUser = User::factory()->create(['role' => 'customer']);
    $resRole = $this->actingAs($manager)->post(route('admin.users.change_role', $targetUser->id), [
        'role' => 'manager',
    ]);
    $resRole->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('error');
});

test('super admin cannot demote their own account', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.users.change_role', $admin->id), [
        'role' => 'customer',
    ]);

    $response->assertSessionHas('error', 'Bạn không thể tự hạ quyền tài khoản Quản trị viên của chính mình.');

    $admin->refresh();
    expect($admin->role)->toBe('admin');
});

test('manager cannot reset password of super admin', function () {
    $superAdmin = User::factory()->create(['role' => 'admin', 'password' => bcrypt('supersecret123')]);
    $manager = User::factory()->create(['role' => 'manager']);

    $response = $this->actingAs($manager)->post(route('admin.users.reset_password', $superAdmin->id), [
        'password' => 'NewPassword999!',
        'password_confirmation' => 'NewPassword999!',
    ]);

    $response->assertSessionHas('error', 'Chỉ Quản trị viên cấp cao mới có thẩm quyền đặt lại mật khẩu cho tài khoản Quản trị viên.');
});

test('invitation extension and publish toggling lifecycle', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $manager->id,
        'groom_name' => 'Minh Trí',
        'bride_name' => 'Yến Nhi',
        'wedding_date' => '2026-11-28',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $manager->id,
        'slug' => 'thiep-cuoi-minh-tri-yen-nhi-'.uniqid(),
        'revision_id' => 0,
        'is_published' => false,
        'expires_at' => now()->addDays(10),
    ]);

    // 1. Toggle publish -> becomes published
    $this->actingAs($manager)->post(route('admin.invitations.toggle_publish', $invitation->id));
    $invitation->refresh();
    expect($invitation->is_published)->toBeTrue()
        ->and($invitation->published_at)->not->toBeNull();

    // 2. Extend expiration by 90 days
    $oldExpires = $invitation->expires_at->copy();
    $this->actingAs($manager)->post(route('admin.invitations.extend', $invitation->id), [
        'days' => 90,
    ]);
    $invitation->refresh();
    expect((int) $oldExpires->diffInDays($invitation->expires_at))->toBe(90);
});

test('audit logger correctly records administrative events and supports filtered retrieval', function () {
    $admin = User::factory()->create([
        'name' => 'Super Auditor',
        'email' => 'auditor@mayhen.vn',
        'role' => 'admin',
    ]);

    $uniqueTag = 'AUDIT_TEST_'.uniqid();

    // Log an action
    $this->actingAs($admin);
    AuditLogger::log(
        'update_plan',
        'Plan',
        999,
        "Cập nhật gói cước kiểm toán: {$uniqueTag}",
        ['test_tag' => $uniqueTag]
    );

    // Retrieve logs
    $logs = AuditLogger::getRecentLogs(20, 'update_plan', $uniqueTag);

    expect($logs)->not->toBeEmpty()
        ->and($logs[0]['action'])->toBe('update_plan')
        ->and($logs[0]['admin_name'])->toBe('Super Auditor')
        ->and($logs[0]['description'])->toContain($uniqueTag);
});

test('admin csv exports include utf8 bom and formula injection defense', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $manager->id,
        'groom_name' => 'Gia Huy',
        'bride_name' => 'Thanh Thảo',
        'wedding_date' => '2026-12-18',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $manager->id,
        'slug' => 'thiep-cuoi-gia-huy-thanh-thao-'.uniqid(),
    ]);

    // Create guest with potential formula injection
    Guest::create([
        'wedding_id' => $wedding->id,
        'name' => '=CMD|"/C calc"!A0',
        'phone' => '0901234567',
        'guest_token' => 'token-csv-'.uniqid(),
    ]);

    $response = $this->actingAs($manager)->get(route('admin.invitations.export_guests', $invitation->id));
    $response->assertStatus(200);

    $content = $response->streamedContent();

    // 1. Starts with UTF-8 BOM
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue();

    // 2. Formula character '=' sanitized with leading single quote
    expect($content)->toContain("'=CMD");
});
