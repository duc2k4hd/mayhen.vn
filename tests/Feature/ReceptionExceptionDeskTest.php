<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Rose Elegance',
        'slug' => 'rose-elegance-'.uniqid(),
        'blade_view' => 'client.invitations.rose.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb.jpg',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hoàng Phúc',
        'bride_name' => 'Thanh Thảo',
        'wedding_date' => now()->addDays(20),
        'venue_name' => 'Gala Center Hoàng Văn Thụ',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'phuc-thao-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
        'custom_config' => [
            'checkin_pin' => '999999',
        ],
    ]);
});

it('handles walk-in guest at reception desk and checks in immediately', function () {
    $this->actingAs($this->user);

    $response = $this->postJson(route('checkin.exception.walk_in', $this->invitation->slug), [
        'name' => 'Chú Lê Văn Hùng',
        'salutation' => 'Chú',
        'group_name' => 'Bạn Ba Mẹ',
        'table_name' => 'Bàn VIP 03',
        'attendees' => 2,
        'note' => 'Khách đến đột xuất không báo trước',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'guest' => [
                'name' => 'Chú Lê Văn Hùng',
                'salutation' => 'Chú',
                'group_name' => 'Bạn Ba Mẹ',
                'table_name' => 'Bàn VIP 03',
                'checked_in_attendees' => 2,
            ],
        ]);

    $guest = Guest::where('name', 'Chú Lê Văn Hùng')->first();
    expect($guest)->not->toBeNull()
        ->and($guest->checked_in_at)->not->toBeNull()
        ->and($guest->checked_in_attendees)->toBe(2)
        ->and($guest->check_in_note)->toContain('Khách đến đột xuất');

    $responseRecord = GuestResponse::where('guest_id', $guest->id)->first();
    expect($responseRecord)->not->toBeNull()
        ->and($responseRecord->attending_status)->toBe('attending')
        ->and($responseRecord->adults_count)->toBe(2);
});

it('adjusts attendees headcount for plus-ones at reception desk', function () {
    $this->actingAs($this->user);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Trần Quang Khải',
        'salutation' => 'Anh',
        'group_name' => 'Đồng Nghiệp',
        'table_name' => 'Bàn 06',
        'expected_attendees' => 1,
        'is_sent' => true,
    ]);

    $response = $this->postJson(route('checkin.exception.adjust_attendees', [
        'slug' => $this->invitation->slug,
        'guestId' => $guest->id,
    ]), [
        'attendees' => 3,
        'note' => 'Đi cùng vợ và con nhỏ',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'checked_in_attendees' => 3,
        ]);

    $freshGuest = $guest->fresh();
    expect($freshGuest->checked_in_attendees)->toBe(3)
        ->and($freshGuest->checked_in_at)->not->toBeNull()
        ->and($freshGuest->check_in_note)->toContain('Đi cùng vợ và con nhỏ');
});

it('converts declined RSVP guest to attending upon arrival at reception desk', function () {
    $this->actingAs($this->user);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Phạm Hồng Nhung',
        'salutation' => 'Bạn',
        'group_name' => 'Bạn Cấp 3',
        'table_name' => null,
        'expected_attendees' => 1,
        'is_sent' => true,
    ]);

    $responseRecord = GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest->id,
        'guest_name' => 'Phạm Hồng Nhung',
        'attending_status' => 'declined',
        'adults_count' => 0,
        'children_count' => 0,
    ]);

    $response = $this->postJson(route('checkin.exception.convert_declined', [
        'slug' => $this->invitation->slug,
        'guestId' => $guest->id,
    ]), [
        'table_name' => 'Bàn 09',
        'attendees' => 2,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'guest' => [
                'name' => 'Phạm Hồng Nhung',
                'table_name' => 'Bàn 09',
                'checked_in_attendees' => 2,
            ],
        ]);

    $freshGuest = $guest->fresh();
    expect($freshGuest->table_name)->toBe('Bàn 09')
        ->and($freshGuest->checked_in_attendees)->toBe(2)
        ->and($freshGuest->checked_in_at)->not->toBeNull();

    expect($responseRecord->fresh()->attending_status)->toBe('attending')
        ->and($responseRecord->fresh()->adults_count)->toBe(2);
});

it('protects exception desk endpoints from unauthorized access', function () {
    // Unauthenticated guest without PIN -> 403
    $this->postJson(route('checkin.exception.walk_in', $this->invitation->slug), [
        'name' => 'Hacker Anonymous',
        'attendees' => 1,
    ])->assertForbidden();
});
