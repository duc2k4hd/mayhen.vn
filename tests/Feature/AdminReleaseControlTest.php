<?php

use App\Models\SystemFeatureFlag;
use App\Models\User;
use App\Services\ReleaseAndRollbackService;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'email' => 'admin_release_test@mayhen.vn',
        'role' => 'admin',
    ]);

    $this->manager = User::factory()->create([
        'email' => 'manager_release_test@mayhen.vn',
        'role' => 'manager',
    ]);

    $this->staff = User::factory()->create([
        'email' => 'staff_release_test@mayhen.vn',
        'role' => 'staff',
    ]);

    $this->customer = User::factory()->create([
        'email' => 'customer_release_test@mayhen.vn',
        'role' => 'customer',
    ]);

    ReleaseAndRollbackService::seedDefaultFeatureFlags();
});

test('super admin and manager can access release control cockpit', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('admin.release.index'));

    $response->assertStatus(200);
    $response->assertViewIs('admin.pages.release.index');
    $response->assertSee('Kiểm Soát Phát Hành & Hoàn Tác');
    $response->assertSee('Bảng Kiểm Tra Sẵn Sàng Nền Tảng');
    $response->assertSee('Ma Trận Cờ Tính Năng');
    $response->assertSee('live_photo_drop');
});

test('staff is forbidden from modifying release flags or safe mode', function () {
    $flag = SystemFeatureFlag::where('key', 'live_photo_drop')->first();

    $response = $this->actingAs($this->staff)
        ->post(route('admin.release.toggle_flag', $flag->id), [
            'is_enabled' => 1,
            'rollout_percentage' => 100,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $jsonResponse = $this->actingAs($this->staff)
        ->postJson(route('admin.release.toggle_flag', $flag->id), [
            'is_enabled' => 1,
            'rollout_percentage' => 100,
        ]);

    $jsonResponse->assertStatus(403);
});

test('sticky evaluation hashes user id consistently for gradual rollouts', function () {
    $flag = SystemFeatureFlag::where('key', 'ai_story_generator')->first();
    $flag->update([
        'is_enabled' => true,
        'rollout_percentage' => 50,
        'kill_switch_active' => false,
    ]);
    ReleaseAndRollbackService::flushFlagCache();

    $userA = User::factory()->create(['id' => 101]);

    $evalA1 = ReleaseAndRollbackService::isFeatureEnabled('ai_story_generator', $userA);
    $evalA2 = ReleaseAndRollbackService::isFeatureEnabled('ai_story_generator', $userA);
    $evalA3 = ReleaseAndRollbackService::isFeatureEnabled('ai_story_generator', $userA);

    // Tính nhất quán (Sticky): Đánh giá 3 lần cùng 1 user luôn cho cùng kết quả
    expect($evalA1)->toBe($evalA2);
    expect($evalA2)->toBe($evalA3);

    // 0% rollout thì luôn false
    $flag->update(['rollout_percentage' => 0]);
    ReleaseAndRollbackService::flushFlagCache();
    expect(ReleaseAndRollbackService::isFeatureEnabled('ai_story_generator', $userA))->toBeFalse();

    // 100% rollout thì luôn true
    $flag->update(['rollout_percentage' => 100]);
    ReleaseAndRollbackService::flushFlagCache();
    expect(ReleaseAndRollbackService::isFeatureEnabled('ai_story_generator', $userA))->toBeTrue();
});

test('kill switch instantly freezes feature regardless of rollout percentage', function () {
    $flag = SystemFeatureFlag::where('key', 'vietqr_dynamic')->first();
    $flag->update([
        'is_enabled' => true,
        'rollout_percentage' => 100,
        'kill_switch_active' => false,
    ]);
    ReleaseAndRollbackService::flushFlagCache();

    expect(ReleaseAndRollbackService::isFeatureEnabled('vietqr_dynamic'))->toBeTrue();

    // Manager kích hoạt Kill Switch
    $response = $this->actingAs($this->manager)
        ->post(route('admin.release.kill_switch', $flag->id), [
            'kill_switch_reason' => 'Nghi vấn đối tác SePay phản hồi chậm trên diện rộng',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $flag->refresh();
    expect($flag->kill_switch_active)->toBeTrue();
    expect($flag->kill_switch_reason)->toContain('Nghi vấn đối tác SePay');

    // Sau khi kích hoạt Kill Switch, dù là ai cũng bị chặn (kể cả rollout 100%)
    expect(ReleaseAndRollbackService::isFeatureEnabled('vietqr_dynamic'))->toBeFalse();
    expect(ReleaseAndRollbackService::isFeatureEnabled('vietqr_dynamic', $this->customer))->toBeFalse();

    // Manager khôi phục cờ (Reset Kill Switch)
    $resetResponse = $this->actingAs($this->manager)
        ->post(route('admin.release.reset_kill_switch', $flag->id));

    $resetResponse->assertRedirect();
    $flag->refresh();
    expect($flag->kill_switch_active)->toBeFalse();
    expect(ReleaseAndRollbackService::isFeatureEnabled('vietqr_dynamic'))->toBeTrue();
});

test('preflight checklist verifies 6 core infrastructure prerequisites', function () {
    $checklist = ReleaseAndRollbackService::runPreflightChecklist();

    expect($checklist)->toHaveKey('checks');
    expect($checklist)->toHaveKey('is_ready_for_release');
    expect(count($checklist['checks']))->toBe(6);

    $checkKeys = array_keys($checklist['checks']);
    expect($checkKeys)->toContain('database');
    expect($checkKeys)->toContain('cache');
    expect($checkKeys)->toContain('storage');
    expect($checkKeys)->toContain('migrations');
    expect($checkKeys)->toContain('queue');
    expect($checkKeys)->toContain('environment');
});

test('emergency safe mode freezes experimental ai story generator', function () {
    // 1. Chỉ Super Admin mới được bật Safe Mode (Manager bị chặn bởi admin.super middleware)
    $managerResponse = $this->actingAs($this->manager)
        ->post(route('admin.release.toggle_safe_mode'), ['action' => 'activate']);

    $managerResponse->assertRedirect();
    $managerResponse->assertSessionHas('error');

    // 2. Super Admin bật Safe Mode
    $adminResponse = $this->actingAs($this->superAdmin)
        ->post(route('admin.release.toggle_safe_mode'), [
            'action' => 'activate',
            'reason' => 'Kiểm thử phòng vệ khẩn cấp ngày cưới',
        ]);

    $adminResponse->assertRedirect();
    $adminResponse->assertSessionHas('success');

    // 3. Khi Safe Mode kích hoạt: cờ ai_story_generator bị ngắt Kill Switch
    $aiFlag = SystemFeatureFlag::where('key', 'ai_story_generator')->first();
    expect($aiFlag->kill_switch_active)->toBeTrue();
    expect(ReleaseAndRollbackService::isFeatureEnabled('ai_story_generator'))->toBeFalse();

    // 4. Super Admin tắt Safe Mode
    $this->actingAs($this->superAdmin)
        ->post(route('admin.release.toggle_safe_mode'), ['action' => 'deactivate']);

    $aiFlag->refresh();
    expect($aiFlag->kill_switch_active)->toBeFalse();
    expect(ReleaseAndRollbackService::isFeatureEnabled('ai_story_generator'))->toBeTrue();
});
