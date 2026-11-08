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
        'name' => 'Royal Crimson',
        'slug' => 'royal-crimson-'.uniqid(),
        'blade_view' => 'client.invitations.crimson.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb.jpg',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Quốc Bảo',
        'bride_name' => 'Thanh Hà',
        'wedding_date' => '2026-12-18',
        'venue_name' => 'Trung Tâm Hội Nghị White Palace Phạm Văn Đồng',
        'venue_address' => '108 Phạm Văn Đồng, Hiệp Bình Chánh, Thủ Đức',
        'extra_data' => [
            'ceremony_time' => '08:30',
            'ceremony_groom_time' => '11:30',
            'party_time' => '18:00',
            'event_party_map_url' => 'https://maps.app.goo.gl/white-palace-pham-van-dong-branch',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'bao-ha-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
    ]);
});

test('it reports no collisions when schedule is spacious and branch matches', function () {
    $guardian = app(WeddingConsistencyGuardianService::class);
    $result = $guardian->detectCeremonyCollisions($this->wedding, $this->invitation);

    expect($result['has_collisions'])->toBeFalse();
    expect($result['highest_severity'])->toBe('none');
    expect($result['collisions_count'])->toBe(0);
});

test('it detects tight schedule and overlapping between two ceremonies', function () {
    // 1. Trường hợp quá gấp (< 90 phút)
    $this->wedding->update([
        'extra_data' => [
            'ceremony_time' => '09:00',
            'ceremony_groom_time' => '09:45',
            'party_time' => '18:00',
        ],
    ]);

    $guardian = app(WeddingConsistencyGuardianService::class);
    $result = $guardian->detectCeremonyCollisions($this->wedding, $this->invitation);

    expect($result['has_collisions'])->toBeTrue();
    expect($result['highest_severity'])->toBe('warning');
    $tightIssue = collect($result['collisions'])->firstWhere('type', 'ceremony_tight_schedule');
    expect($tightIssue)->not->toBeNull();

    // 2. Trường hợp trùng giờ đúng 0 phút
    $this->wedding->update([
        'extra_data' => [
            'ceremony_time' => '10:00',
            'ceremony_groom_time' => '10:00',
            'party_time' => '18:00',
        ],
    ]);

    $resultOverlap = $guardian->detectCeremonyCollisions($this->wedding, $this->invitation);
    expect($resultOverlap['has_collisions'])->toBeTrue();
    expect($resultOverlap['highest_severity'])->toBe('critical');
    $overlapIssue = collect($resultOverlap['collisions'])->firstWhere('type', 'ceremony_overlap');
    expect($overlapIssue)->not->toBeNull();
});

test('it detects collision when ceremony happens after banquet time', function () {
    $this->wedding->update([
        'extra_data' => [
            'ceremony_time' => '09:00',
            'ceremony_groom_time' => '19:30', // Sau 18:00
            'party_time' => '18:00',
        ],
    ]);

    $guardian = app(WeddingConsistencyGuardianService::class);
    $result = $guardian->detectCeremonyCollisions($this->wedding, $this->invitation);

    expect($result['has_collisions'])->toBeTrue();
    expect($result['highest_severity'])->toBe('critical');
    $afterPartyIssue = collect($result['collisions'])->firstWhere('type', 'ceremony_after_party');
    expect($afterPartyIssue)->not->toBeNull();
});

test('it detects venue branch mismatch collision between venue name and google maps url', function () {
    // Tên sảnh là Phạm Văn Đồng, nhưng link map lại dính Hoàng Văn Thụ
    $this->wedding->update([
        'venue_name' => 'White Palace Phạm Văn Đồng',
        'extra_data' => [
            'ceremony_time' => '08:30',
            'ceremony_groom_time' => '11:30',
            'party_time' => '18:00',
            'event_party_map_url' => 'https://maps.app.goo.gl/white-palace-hoang-van-thu-branch',
        ],
    ]);

    $guardian = app(WeddingConsistencyGuardianService::class);
    $result = $guardian->detectCeremonyCollisions($this->wedding, $this->invitation);

    expect($result['has_collisions'])->toBeTrue();
    expect($result['highest_severity'])->toBe('critical');
    $mismatchIssue = collect($result['collisions'])->firstWhere('type', 'venue_map_branch_mismatch');
    expect($mismatchIssue)->not->toBeNull();
});

test('owner can check ceremony collisions via api', function () {
    $response = $this->actingAs($this->user)->getJson(route('invitation.collision_check', $this->invitation->id));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'data' => [
            'has_collisions' => false,
            'highest_severity' => 'none',
            'collisions_count' => 0,
        ],
    ]);
});

test('stranger cannot check ceremony collisions of another user via api', function () {
    $response = $this->actingAs($this->otherUser)->getJson(route('invitation.collision_check', $this->invitation->id));

    $response->assertForbidden();
});
