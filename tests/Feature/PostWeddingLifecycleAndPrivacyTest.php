<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => Hash::make('MySecurePass123!'),
    ]);

    $this->template = InvitationTemplate::firstOrCreate(
        ['slug' => 'test-lifecycle-template'],
        [
            'name' => 'Test Lifecycle Template',
            'description' => 'Test template for lifecycle',
            'category' => 'modern',
            'blade_view' => 'client.invitations.template-1',
            'thumbnail_url' => '/assets/img/demo.jpg',
            'is_active' => true,
        ]
    );

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Phạm Hoàng Long',
        'bride_name' => 'Đỗ Hải Yến',
        'wedding_date' => now()->subDays(5)->toDateString(),
        'venue_name' => 'Gem Center',
        'venue_address' => '8 Nguyễn Bỉnh Khiêm, Q.1, TP.HCM',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'dam-cuoi-long-yen-'.uniqid(),
        'title' => 'Thiệp cưới Long & Yến',
        'version_type' => 'free',
        'is_published' => true,
        'published_at' => now()->subDays(20),
        'custom_config' => [],
    ]);
});

test('owner can freeze RSVP and configure redirect to memory vault', function () {
    $response = $this->actingAs($this->user)
        ->post(route('memory.post_wedding_lifecycle', $this->invitation->id), [
            'freeze_rsvp' => 1,
            'redirect_to_vault' => 1,
            'thank_you_letter' => 'Cảm ơn mọi người đã tham dự lễ cưới của Long & Yến!',
        ]);

    $response->assertRedirect()->assertSessionHas('success');

    $freshInvitation = $this->invitation->fresh();
    expect($freshInvitation->isRsvpFrozen())->toBeTrue();
    expect($freshInvitation->shouldRedirectToMemoryVault())->toBeTrue();

    // Kiểm tra AuditLog
    $logs = AuditLogger::getRecentLogs(10, 'POST_WEDDING_LIFECYCLE_TRIGGERED');
    expect(count($logs))->toBeGreaterThanOrEqual(1);
});

test('frozen RSVP blocks public guests from submitting RSVP responses', function () {
    $this->invitation->setPostWeddingConfig([
        'freeze_rsvp' => true,
    ]);

    $response = $this->postJson(route('rsvp.submit', $this->invitation->slug), [
        'guest_name' => 'Nguyễn Khách Trễ',
        'attending_status' => 'attending',
        'adults_count' => 1,
        'children_count' => 0,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'is_frozen' => true,
        ]);
});

test('public invitation redirects to memory vault when configured', function () {
    $this->invitation->setPostWeddingConfig([
        'redirect_to_vault' => true,
    ]);

    // Khách vãng lai truy cập link thiệp cưới
    $response = $this->get(route('invitation.show', $this->invitation->slug));

    $response->assertRedirect(route('memory.vault', $this->invitation->slug));
});

test('owner can purge guest PII after wedding with valid current password', function () {
    $guest1 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Anh Trần Hùng',
        'phone' => '0901234567',
        'guest_token' => 'guest-token-abc',
        'check_in_note' => 'Ăn chay bàn VIP',
        'last_device' => 'iPhone 15 Pro',
    ]);

    $guest2 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Chị Lê Nga',
        'phone' => '0987654321',
        'guest_token' => 'guest-token-xyz',
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('memory.purge_guest_pii', $this->invitation->id), [
            'current_password' => 'MySecurePass123!',
        ]);

    $response->assertRedirect()->assertSessionHas('success');

    $g1 = $guest1->fresh();
    $g2 = $guest2->fresh();

    // Dữ liệu PII đã bị xóa sạch và token bị vô hiệu hóa
    expect($g1->phone)->toBeNull();
    expect(str_starts_with((string) $g1->guest_token, 'purged_'))->toBeTrue();
    expect($g1->check_in_note)->toBeNull();
    expect($g2->phone)->toBeNull();
    expect(str_starts_with((string) $g2->guest_token, 'purged_'))->toBeTrue();

    // Tên và quan hệ vẫn được giữ lại để lưu niệm
    expect($g1->name)->toBe('Anh Trần Hùng');
    expect($g2->name)->toBe('Chị Lê Nga');

    // Kiểm tra AuditLog
    $logs = AuditLogger::getRecentLogs(10, 'GUEST_PII_PURGED_PRIVACY');
    expect(count($logs))->toBeGreaterThanOrEqual(1);
});

test('purge guest PII fails when password is incorrect', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Anh Nguyễn Văn A',
        'phone' => '0911223344',
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('memory.purge_guest_pii', $this->invitation->id), [
            'current_password' => 'WrongPassword!',
        ]);

    $response->assertSessionHasErrors('current_password');

    // Phone không bị xóa
    expect($guest->fresh()->phone)->toBe('0911223344');
});

test('offline archive download returns zip file with complete wedding datasets', function () {
    $response = $this->actingAs($this->user)
        ->get(route('memory.offline_archive', $this->invitation->id));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/zip');
});

test('stranger cannot trigger post wedding lifecycle or purge guest data', function () {
    $stranger = User::factory()->create([
        'password' => Hash::make('StrangerPass!'),
    ]);

    $this->actingAs($stranger)
        ->post(route('memory.post_wedding_lifecycle', $this->invitation->id), [
            'freeze_rsvp' => 1,
        ])
        ->assertStatus(404);

    $this->actingAs($stranger)
        ->post(route('memory.purge_guest_pii', $this->invitation->id), [
            'current_password' => 'StrangerPass!',
        ])
        ->assertStatus(404);
});
