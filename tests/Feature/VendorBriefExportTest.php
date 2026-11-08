<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\VendorBriefService;

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
        'groom_name' => 'Hải Đăng',
        'bride_name' => 'Thanh Thảo',
        'wedding_date' => '2026-12-28',
        'venue_name' => 'Trung Tâm Hội Nghị White Palace',
        'venue_address' => '194 Hoàng Văn Thụ, Phú Nhuận, TP.HCM',
        'extra_data' => [
            'party_time' => '18:00',
            'groom_father' => 'Nguyễn Văn Hùng',
            'groom_mother' => 'Trần Thị Mai',
            'bride_father' => 'Lê Văn Nam',
            'bride_mother' => 'Phạm Thị Lan',
            'event_party_lunar' => '19 Tháng 11 Âm Lịch',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'dang-thao-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
    ]);

    // Thêm khách VIP
    $this->vipGuest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Trần Văn Phú',
        'salutation' => 'Bác',
        'group_name' => 'Khách VIP Nhà Trai',
        'table_name' => 'Bàn VIP 01',
        'phone' => '0912345678', // SĐT nhạy cảm
        'expected_attendees' => 2,
    ]);

    // Thêm phản hồi ăn chay
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $this->vipGuest->id,
        'guest_name' => 'Trần Văn Phú',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'dietary_requirements' => '01 Suất Ăn Chay Trường',
    ]);
});

test('it generates vendor brief with accurate data and hides guest pii', function () {
    $service = app(VendorBriefService::class);
    $brief = $service->generate($this->wedding, $this->invitation);

    expect($brief['wedding_title'])->toBe('Hải Đăng & Thanh Thảo');
    expect($brief['mc']['parents']['groom_side']['father'])->toBe('Nguyễn Văn Hùng');
    expect($brief['mc']['parents']['bride_side']['mother'])->toBe('Phạm Thị Lan');

    // Kiểm tra PII Protection: VIP roster không chứa số điện thoại
    $vipRoster = $brief['mc']['vip_roster'];
    expect($vipRoster)->toHaveCount(1);
    expect($vipRoster[0]['name'])->toBe('Bác Trần Văn Phú');
    expect($vipRoster[0]['table'])->toBe('Bàn VIP 01');
    expect(array_key_exists('phone', $vipRoster[0]))->toBeFalse();

    // Kiểm tra Venue catering
    expect($brief['venue']['dietary_requirements'])->toHaveCount(1);
    expect($brief['venue']['dietary_requirements'][0]['requirement'])->toBe('01 Suất Ăn Chay Trường');

    // Kiểm tra Photo shot list
    expect($brief['photographer']['key_shots'])->toHaveCount(8);
});

test('owner can view vendor brief page', function () {
    $response = $this->actingAs($this->user)->get(route('account.invitations.vendor_brief', $this->invitation->id));

    $response->assertOk();
    $response->assertSee('Bản Hướng Dẫn Tác Nghiệp Đối Tác');
    $response->assertSee('MC BRIEF');
    $response->assertSee('VENUE & CATERING', false);
    $response->assertSee('PHOTO / VIDEO SHOT LIST');
    $response->assertSee('Nguyễn Văn Hùng');
    $response->assertSee('01 Suất Ăn Chay Trường');
    $response->assertDontSee('0912345678'); // Đảm bảo SĐT không bị lọt vào view
});

test('stranger cannot view vendor brief of another user', function () {
    $response = $this->actingAs($this->otherUser)->get(route('account.invitations.vendor_brief', $this->invitation->id));

    $response->assertNotFound();
});

test('unauthenticated user is redirected to login for vendor brief', function () {
    $response = $this->get(route('account.invitations.vendor_brief', $this->invitation->id));

    $response->assertRedirect(route('login'));
});
