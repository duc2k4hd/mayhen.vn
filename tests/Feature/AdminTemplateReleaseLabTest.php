<?php

use App\Models\InvitationTemplate;
use App\Models\InvitationTemplateRelease;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'email' => 'admin_lab_test@mayhen.vn',
        'role' => 'admin',
    ]);

    $this->manager = User::factory()->create([
        'email' => 'manager_lab_test@mayhen.vn',
        'role' => 'manager',
    ]);

    $this->staff = User::factory()->create([
        'email' => 'staff_lab_test@mayhen.vn',
        'role' => 'staff',
    ]);

    $this->customer = User::factory()->create([
        'email' => 'customer_lab_test@mayhen.vn',
        'role' => 'customer',
    ]);

    // Mẫu thiệp đã có thật trong thư mục resources/views/client/invitations/
    $this->template = InvitationTemplate::firstOrCreate(
        ['slug' => 'ngay-tinh-yeu-dom-hoa'],
        [
            'name' => 'Ngày Tình Yêu Đơm Hoa',
            'category' => 'modern',
            'thumbnail_url' => '/mau-thiep/ngay-tinh-yeu-dom-hoa/assets/images/hero-couple.webp',
            'is_active' => true,
            'is_featured' => false,
            'current_version' => 'v1.0.0',
            'release_status' => 'published',
            'rollout_percentage' => 100,
        ]
    );
});

test('guest and customer cannot access template release lab', function () {
    $this->get(route('admin.templates.release_lab', $this->template->id))
        ->assertRedirect(route('admin.login'));

    $this->actingAs($this->customer)
        ->get(route('admin.templates.release_lab', $this->template->id))
        ->assertRedirect(route('admin.login'));
});

test('admin and manager can access template release lab workbench', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.templates.release_lab', $this->template->id));

    $response->assertStatus(200);
    $response->assertSee('Release Lab & Visual Regression: Ngày Tình Yêu Đơm Hoa');
    $response->assertSee('Preflight Quality Gate');
    $response->assertSee('375px');
    $response->assertSee('430px');
    $response->assertSee('768px');
    $response->assertSee('1440px');
    $response->assertSee('Sổ Cái Lịch Sử Phiên Bản');
});

test('manager can access template release lab workbench', function () {
    $response = $this->actingAs($this->manager)->get(route('admin.templates.release_lab', $this->template->id));
    $response->assertStatus(200);
    $response->assertSee('Khởi Tạo Bản Phát Hành Thử Nghiệm');
});

test('staff is forbidden from creating releases or managing releases', function () {
    // 1. Web request -> Redirect back with error
    $response = $this->actingAs($this->staff)->post(route('admin.templates.releases.create', $this->template->id), [
        'version' => 'v1.1.0',
        'rollout_percentage' => 50,
        'release_notes' => 'Staff trying to create release',
    ]);
    $response->assertSessionHas('error');

    // 2. JSON request -> 403 Forbidden
    $jsonResponse = $this->actingAs($this->staff)->postJson(route('admin.templates.releases.create', $this->template->id), [
        'version' => 'v1.1.0',
        'rollout_percentage' => 50,
    ]);
    $jsonResponse->assertStatus(403);
});

test('admin can create staged release with valid semantic version and canary rollout', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.templates.releases.create', $this->template->id), [
        'version' => 'v1.1.0',
        'rollout_percentage' => 30,
        'release_notes' => 'Cập nhật padding và typography section hero theo chuẩn Quiet Luxury.',
    ]);

    $response->assertRedirect(route('admin.templates.release_lab', $this->template->id));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('invitation_template_releases', [
        'template_id' => $this->template->id,
        'version' => 'v1.1.0',
        'release_status' => 'staged',
        'rollout_percentage' => 30,
        'creator_id' => $this->admin->id,
    ]);

    $updatedTemplate = $this->template->fresh();
    expect($updatedTemplate->staged_version)->toBe('v1.1.0')
        ->and($updatedTemplate->release_status)->toBe('staged');
});

test('create release rejects invalid version format and duplicate version', function () {
    // 1. Invalid version format
    $badResponse = $this->actingAs($this->admin)->post(route('admin.templates.releases.create', $this->template->id), [
        'version' => 'invalid_version_tag',
        'rollout_percentage' => 50,
    ]);
    $badResponse->assertSessionHasErrors('version');

    // 2. Create v1.1.0 first
    $this->actingAs($this->admin)->post(route('admin.templates.releases.create', $this->template->id), [
        'version' => 'v1.1.0',
        'rollout_percentage' => 20,
    ]);

    // 3. Duplicate version attempt
    $duplicateResponse = $this->actingAs($this->admin)->post(route('admin.templates.releases.create', $this->template->id), [
        'version' => 'v1.1.0',
        'rollout_percentage' => 50,
    ]);
    $duplicateResponse->assertSessionHasErrors('version');
});

test('admin can publish release to production when preflight passes', function () {
    // 1. Tạo bản release staged
    $release = InvitationTemplateRelease::create([
        'template_id' => $this->template->id,
        'version' => 'v1.2.0',
        'release_status' => 'staged',
        'rollout_percentage' => 100,
        'release_notes' => 'Tối ưu Core Web Vitals và ảnh WebP.',
        'creator_id' => $this->admin->id,
    ]);

    $oldVersion = $this->template->current_version;

    // 2. Phê duyệt & Công bố phát hành
    $response = $this->actingAs($this->admin)->post(route('admin.templates.releases.publish', [
        'id' => $this->template->id,
        'releaseId' => $release->id,
    ]));

    $response->assertRedirect(route('admin.templates.release_lab', $this->template->id));
    $response->assertSessionHas('success');

    $updatedRelease = $release->fresh();
    expect($updatedRelease->release_status)->toBe('published')
        ->and($updatedRelease->publisher_id)->toBe($this->admin->id)
        ->and($updatedRelease->published_at)->not->toBeNull();

    $updatedTemplate = $this->template->fresh();
    expect($updatedTemplate->current_version)->toBe('v1.2.0')
        ->and($updatedTemplate->previous_version)->toBe($oldVersion)
        ->and($updatedTemplate->release_status)->toBe('published')
        ->and($updatedTemplate->staged_version)->toBeNull();
});

test('admin can perform 1-click instant rollback with audit reason', function () {
    // 1. Tạo 2 bản releases: v1.0.0 (cũ) và v1.1.0 (hiện tại)
    $releaseOld = InvitationTemplateRelease::create([
        'template_id' => $this->template->id,
        'version' => 'v1.0.0',
        'release_status' => 'deprecated',
        'rollout_percentage' => 100,
        'creator_id' => $this->admin->id,
    ]);

    $releaseCurrent = InvitationTemplateRelease::create([
        'template_id' => $this->template->id,
        'version' => 'v1.1.0',
        'release_status' => 'published',
        'rollout_percentage' => 100,
        'creator_id' => $this->admin->id,
        'publisher_id' => $this->admin->id,
        'published_at' => now(),
    ]);

    $this->template->update([
        'current_version' => 'v1.1.0',
        'previous_version' => 'v1.0.0',
        'release_status' => 'published',
    ]);

    // 2. Thử rollback thiếu lý do -> Bị lỗi validation
    $badRollback = $this->actingAs($this->admin)->post(route('admin.templates.releases.rollback', [
        'id' => $this->template->id,
        'releaseId' => $releaseOld->id,
    ]), [
        'rollback_reason' => 'ngắn', // < 6 ký tự
    ]);
    $badRollback->assertSessionHasErrors('rollback_reason');

    // 3. Thực hiện rollback thành công có lý do kiểm toán
    $rollbackReason = 'Phát hiện vỡ layout font Cormorant Garamond trên iOS Safari';
    $response = $this->actingAs($this->admin)->post(route('admin.templates.releases.rollback', [
        'id' => $this->template->id,
        'releaseId' => $releaseOld->id,
    ]), [
        'rollback_reason' => $rollbackReason,
    ]);

    $response->assertRedirect(route('admin.templates.release_lab', $this->template->id));
    $response->assertSessionHas('success');

    // 4. Kiểm tra trạng thái Database
    $currentRelFresh = $releaseCurrent->fresh();
    expect($currentRelFresh->release_status)->toBe('rolled_back')
        ->and($currentRelFresh->rollback_reason)->toBe($rollbackReason)
        ->and($currentRelFresh->rolled_back_at)->not->toBeNull();

    $oldRelFresh = $releaseOld->fresh();
    expect($oldRelFresh->release_status)->toBe('published');

    $tplFresh = $this->template->fresh();
    expect($tplFresh->current_version)->toBe('v1.0.0')
        ->and($tplFresh->previous_version)->toBe('v1.1.0')
        ->and($tplFresh->rollout_percentage)->toBe(100);
});

test('admin and manager can update canary rollout percentage via form and json', function () {
    $release = InvitationTemplateRelease::create([
        'template_id' => $this->template->id,
        'version' => 'v1.1.0',
        'release_status' => 'published',
        'rollout_percentage' => 20,
        'creator_id' => $this->admin->id,
    ]);

    // Form submission
    $response = $this->actingAs($this->admin)->post(route('admin.templates.releases.rollout', [
        'id' => $this->template->id,
        'releaseId' => $release->id,
    ]), [
        'rollout_percentage' => 60,
    ]);

    $response->assertSessionHas('success');
    expect($release->fresh()->rollout_percentage)->toBe(60);

    // JSON AJAX submission
    $ajaxResponse = $this->actingAs($this->manager)->postJson(route('admin.templates.releases.rollout', [
        'id' => $this->template->id,
        'releaseId' => $release->id,
    ]), [
        'rollout_percentage' => 85,
    ]);

    $ajaxResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'rollout_percentage' => 85,
        ]);

    expect($release->fresh()->rollout_percentage)->toBe(85);
});
