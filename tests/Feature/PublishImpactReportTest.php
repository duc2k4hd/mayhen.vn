<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm Impact',
        'slug' => 'mau-impact-'.uniqid(),
        'description' => 'Test Template Impact',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.tron-kiep-nay-ben-nhau.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->owner->id,
        'groom_name' => 'Văn Nam',
        'bride_name' => 'Thu Hà',
        'wedding_date' => '2026-12-15',
        'venue_name' => 'White Palace Hoàng Văn Thụ',
        'venue_address' => '194 Hoàng Văn Thụ, Phường 9, Phú Nhuận',
        'extra_data' => [
            'party_time' => '17:30',
            'groom_bank_account' => '1234567890',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'van-nam-thu-ha-'.uniqid(),
        'is_published' => true,
        'published_at' => now()->subDays(5),
    ]);

    // Tạo 3 khách mời, trong đó 2 khách đã RSVP tham dự
    $guest1 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn A',
        'expected_attendees' => 2,
    ]);
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest1->id,
        'guest_name' => 'Nguyễn Văn A',
        'attending_status' => 'attending',
        'adults_count' => 2,
    ]);

    $guest2 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Trần Thị B',
        'expected_attendees' => 1,
    ]);
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest2->id,
        'guest_name' => 'Trần Thị B',
        'attending_status' => 'attending',
        'adults_count' => 1,
    ]);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lê Văn C',
        'expected_attendees' => 1,
    ]);
});

test('publish impact api detects critical date change and reports affected guest count', function () {
    $response = $this->actingAs($this->owner)->postJson(route('invitation.publish_impact', $this->invitation->id), [
        'wedding' => [
            'wedding_date' => '2026-12-20', // Đổi ngày từ 15 -> 20
        ],
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'data' => [
            'has_critical_changes' => true,
            'impacted_guests_count' => 3,
            'impacted_rsvp_count' => 2,
            'warning_level' => 'critical',
        ],
    ]);

    $data = $response->json('data');
    expect($data['changes'])->toHaveCount(1);
    expect($data['changes'][0]['field'])->toBe('wedding_date');
    expect($data['changes'][0]['old'])->toBe('2026-12-15');
    expect($data['changes'][0]['new'])->toBe('2026-12-20');
});

test('publish impact api detects warning level when only time is adjusted', function () {
    $response = $this->actingAs($this->owner)->postJson(route('invitation.publish_impact', $this->invitation->id), [
        'wedding' => [
            'extra_data' => [
                'party_time' => '18:00', // Đổi giờ từ 17:30 -> 18:00
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'data' => [
            'has_critical_changes' => true,
            'warning_level' => 'warning',
        ],
    ]);
});

test('publish impact api returns none warning when no critical fields changed', function () {
    $response = $this->actingAs($this->owner)->postJson(route('invitation.publish_impact', $this->invitation->id), [
        'wedding' => [
            'story' => 'Một câu chuyện tình yêu mới cập nhật...',
        ],
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'data' => [
            'has_critical_changes' => false,
            'warning_level' => 'none',
            'changes' => [],
        ],
    ]);
});

test('unauthorized user cannot query publish impact', function () {
    $response = $this->actingAs($this->otherUser)->postJson(route('invitation.publish_impact', $this->invitation->id), [
        'wedding' => [
            'wedding_date' => '2026-12-20',
        ],
    ]);

    $response->assertForbidden();
});
