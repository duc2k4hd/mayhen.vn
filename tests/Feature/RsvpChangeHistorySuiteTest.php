<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\GuestResponseLog;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test RSVP History',
        'slug' => 'mau-test-rsvp-history-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hải Nam',
        'bride_name' => 'Thu Hà',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'Caravelle Saigon',
        'venue_address' => '19-23 Lam Son Square, Dist 1, HCMC',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'nam-ha-'.uniqid(),
        'title' => 'Thiệp Cưới Hải Nam & Thu Hà',
        'is_published' => true,
    ]);

    $this->guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Ba Sài Gòn',
        'guest_token' => 'TOKEN_BACBA_'.uniqid(),
        'expected_attendees' => 2,
    ]);

    $this->response = GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $this->guest->id,
        'guest_name' => 'Bác Ba Sài Gòn',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
    ]);

    GuestResponseLog::create([
        'guest_response_id' => $this->response->id,
        'wedding_id' => $this->wedding->id,
        'guest_id' => $this->guest->id,
        'previous_status' => 'pending',
        'new_status' => 'attending',
        'previous_adults' => 0,
        'new_adults' => 2,
        'previous_children' => 0,
        'new_children' => 0,
        'source' => 'guest_online',
    ]);
});

test('chủ thiệp có thể truy cập Studio Sổ Kiểm Toán & Biến Động RSVP', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.rsvp_history.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Sổ Kiểm Toán &amp; Biến Động RSVP', false);
    $response->assertSee('Bác Ba Sài Gòn');
});

test('người dùng khác bị 403 khi truy cập Sổ Kiểm Toán RSVP của chủ thiệp', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.rsvp_history.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('API Guest Timeline trả về nhật ký biến động cá nhân hóa của khách mời', function () {
    $response = $this->actingAs($this->user)
        ->getJson(route('account.rsvp_history.guest_timeline', [
            'id' => $this->invitation->id,
            'guestId' => $this->guest->id,
        ]));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'guest' => [
                'id',
                'name',
            ],
            'timeline',
        ]);
});

test('chủ thiệp có thể xuất file CSV nhật ký biến động RSVP đối soát với nhà hàng', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.rsvp_history.export', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
