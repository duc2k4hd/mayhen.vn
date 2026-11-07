<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingConsistencyGuardianService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Guardian Test',
        'slug' => 'mau-guardian-'.uniqid(),
        'description' => 'Test Template Guardian',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.tron-kiep-nay-ben-nhau.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Tuấn Kiệt',
        'bride_name' => 'Mai Phương',
        'wedding_date' => now()->addDays(30),
        'venue_name' => 'White Palace Hoàng Văn Thụ',
        'venue_address' => '194 Hoàng Văn Thụ, Phường 9, Phú Nhuận, TP.HCM',
        'groom_parents' => 'ÔNG: Tuấn Hùng & BÀ: Thu Nga',
        'bride_parents' => 'ÔNG: Minh Phương & BÀ: Kim Tuyến',
        'extra_data' => [
            'event_party_map_url' => 'https://maps.app.goo.gl/sample123',
            'groom_bank_account' => '0123456789',
            'groom_bank_name' => 'Vietcombank',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'tuan-kiet-mai-phuong-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'checkin_pin' => '889900',
        ],
    ]);
});

test('consistency guardian returns excellent score for complete wedding information', function () {
    $guardian = app(WeddingConsistencyGuardianService::class);
    $result = $guardian->inspect($this->wedding, $this->invitation);

    expect($result['score'])->toBe(100);
    expect($result['status'])->toBe('excellent');
    expect($result['checks_passed'])->toBe(6);
    expect($result['issues'])->toBeEmpty();
});

test('consistency guardian detects missing venue and invalid map url', function () {
    $this->wedding->update([
        'venue_name' => null,
        'venue_address' => null,
        'extra_data' => [
            'event_party_map_url' => 'https://some-random-site.com/fake-map',
        ],
    ]);

    $guardian = app(WeddingConsistencyGuardianService::class);
    $result = $guardian->inspect($this->wedding, $this->invitation);

    expect($result['score'])->toBeLessThan(100);
    $fields = array_column($result['issues'], 'field');
    expect($fields)->toContain('venue_name');
    expect($fields)->toContain('event_party_map_url');
});

test('api consistency check enforces ownership and returns json inspection', function () {
    // 1. Khách ngoài chưa login -> 302 login
    $guestResponse = $this->getJson(route('invitation.consistency_check', $this->invitation->id));
    $guestResponse->assertUnauthorized();

    // 2. User khác -> 403 Forbidden
    $this->actingAs($this->otherUser);
    $forbiddenResponse = $this->getJson(route('invitation.consistency_check', $this->invitation->id));
    $forbiddenResponse->assertForbidden();

    // 3. Chủ thiệp -> 200 OK kèm data
    $this->actingAs($this->user);
    $successResponse = $this->getJson(route('invitation.consistency_check', $this->invitation->id));
    $successResponse->assertOk();
    $successResponse->assertJsonPath('success', true);
    $successResponse->assertJsonPath('data.status', 'excellent');
    $successResponse->assertJsonPath('data.score', 100);
});
