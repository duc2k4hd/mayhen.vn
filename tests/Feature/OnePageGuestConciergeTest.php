<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Celestial Romance',
        'slug' => 'celestial-concierge-'.uniqid(),
        'blade_view' => 'client.invitations.celestial.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb.jpg',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Bích Phương',
        'wedding_date' => '2026-12-25',
        'venue_name' => 'GEM Center TP.HCM',
        'venue_address' => 'Số 8 Nguyễn Bỉnh Khiêm, Đa Kao, Quận 1',
        'extra_data' => [
            'party_time' => '18:30',
            'event_party_map_url' => 'https://maps.app.goo.gl/example123',
            'groom_bank_account' => '0987654321',
            'groom_bank_name' => 'Vietcombank',
            'groom_bank_holder' => 'HOANG NAM',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'nam-phuong-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
        'custom_config' => [
            'parking_guide' => [
                'car_parking' => 'Tầng hầm B2 - B3 toà nhà GEM Center',
                'moto_parking' => 'Bãi giữ xe máy cổng số 8',
                'elevator_guide' => 'Thang máy sảnh A lên tầng 3',
                'parking_fee' => 'Miễn phí cho khách dự tiệc',
            ],
            'persona_messages' => [
                'Bạn Thân' => [
                    'title' => 'Gửi Người Bạn Đồng Hành',
                    'message' => 'Rất vui vì có bạn cùng chứng kiến khoảnh khắc trọng đại của chúng mình!',
                ],
            ],
        ],
    ]);

    $this->guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lê Minh Tuấn',
        'salutation' => 'Bạn',
        'group_name' => 'Bạn Thân',
        'table_name' => 'Bàn VIP 02',
        'expected_attendees' => 2,
    ]);
});

test('it renders one-page guest concierge successfully with personalized data', function () {
    $response = $this->get(route('guest.concierge', ['guestToken' => $this->guest->guest_token]));

    $response->assertOk();
    $response->assertSee('Lê Minh Tuấn');
    $response->assertSee('Bàn VIP 02');
    $response->assertSee('Gửi Người Bạn Đồng Hành');
    $response->assertSee('Tầng hầm B2 - B3');
    $response->assertSee('GEM Center TP.HCM');
    $response->assertSee('Thêm Vào Google Calendar');
    $response->assertSee('Tải Lịch Apple Calendar');
});

test('it allows directScan route to render guest concierge seamlessly', function () {
    $response = $this->get(route('checkin.direct', ['token' => $this->guest->guest_token]));

    $response->assertOk();
    $response->assertSee('Lê Minh Tuấn');
    $response->assertSee('Bàn VIP 02');
});

test('it generates valid iCalendar file download', function () {
    $response = $this->get(route('guest.concierge.ics', ['guestToken' => $this->guest->guest_token]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
    expect($response->getContent())->toContain('BEGIN:VCALENDAR');
    expect($response->getContent())->toContain('BEGIN:VEVENT');
    expect($response->getContent())->toContain('Hoàng Nam & Bích Phương');
    expect($response->getContent())->toContain('GEM Center TP.HCM');
});

test('it returns 404 for invalid guest token in concierge', function () {
    $response = $this->get(route('guest.concierge', ['guestToken' => 'non-existent-token-xyz']));

    $response->assertNotFound();
});

test('guest can submit fast rsvp directly from concierge', function () {
    $response = $this->post(route('rsvp.submit', [
        'slug' => $this->invitation->slug,
        'token' => $this->guest->guest_token,
    ]), [
        'guest_name' => 'Lê Minh Tuấn',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'wishes' => 'Chúc hai bạn mãi mãi hạnh phúc!',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('guest_responses', [
        'wedding_id' => $this->wedding->id,
        'guest_id' => $this->guest->id,
        'guest_name' => 'Lê Minh Tuấn',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'wishes' => 'Chúc hai bạn mãi mãi hạnh phúc!',
    ]);
});
