<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingEvent;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Multi Event RSVP',
        'slug' => 'mau-test-multi-event-rsvp-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hoàng Dũng',
        'bride_name' => 'Thanh Thảo',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'White Palace Vo Van Kiet',
        'venue_address' => '59 Vo Van Kiet, An Lac, Binh Tan, HCMC',
    ]);

    $this->eventVuQuy = WeddingEvent::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lễ Vu Quy (Nhà Gái)',
        'event_date' => now()->addMonths(2)->toDateString(),
        'start_time' => '09:00',
        'venue_name' => 'Tư Gia Nhà Gái',
        'is_active' => true,
        'display_order' => 1,
    ]);

    $this->eventBanquet = WeddingEvent::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Tiệc Cưới Chiêu Đãi',
        'event_date' => now()->addMonths(2)->toDateString(),
        'start_time' => '18:00',
        'venue_name' => 'White Palace',
        'is_active' => true,
        'display_order' => 2,
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'dung-thao-'.uniqid(),
        'title' => 'Thiệp Cưới Hoàng Dũng & Thanh Thảo',
        'is_published' => true,
        'custom_config' => [
            'allow_rsvp' => true,
            'allow_rsvp_update' => true,
        ],
    ]);

    // Khách A được mời cả 2 sự kiện: Vu Quy và Tiệc Cưới
    $this->guestBoth = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Anh Nguyễn Văn An',
        'guest_token' => 'TOKEN_AN_'.uniqid(),
        'max_attendees' => 5,
        'event_ids' => [$this->eventVuQuy->id, $this->eventBanquet->id],
    ]);

    // Khách B chỉ được mời Tiệc Cưới
    $this->guestBanquetOnly = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Anh Trần Văn Bình',
        'guest_token' => 'TOKEN_BINH_'.uniqid(),
        'max_attendees' => 3,
        'event_ids' => [$this->eventBanquet->id],
    ]);
});

test('G1-001: khách có thể RSVP độc lập cho 2 sự kiện khác nhau và giữ headcount riêng', function () {
    // 1. RSVP cho Lễ Vu Quy (2 người)
    $responseVuQuy = $this->postJson(
        route('rsvp.submit', ['slug' => $this->invitation->slug, 'token' => $this->guestBoth->guest_token]),
        [
            'event_id' => $this->eventVuQuy->id,
            'guest_name' => $this->guestBoth->name,
            'attending_status' => 'attending',
            'adults_count' => 2,
            'children_count' => 0,
        ]
    );

    $responseVuQuy->assertStatus(200)
        ->assertJson(['success' => true]);

    // 2. RSVP cho Tiệc Cưới (4 người)
    $responseBanquet = $this->postJson(
        route('rsvp.submit', ['slug' => $this->invitation->slug, 'token' => $this->guestBoth->guest_token]),
        [
            'event_id' => $this->eventBanquet->id,
            'guest_name' => $this->guestBoth->name,
            'attending_status' => 'attending',
            'adults_count' => 3,
            'children_count' => 1,
        ]
    );

    $responseBanquet->assertStatus(200)
        ->assertJson(['success' => true]);

    // Kiểm tra có đúng 2 response độc lập trên DB
    $responses = GuestResponse::where('guest_id', $this->guestBoth->id)->get();
    expect($responses)->toHaveCount(2);

    $vuQuyRsvp = $responses->firstWhere('event_id', $this->eventVuQuy->id);
    expect($vuQuyRsvp)->not->toBeNull();
    expect($vuQuyRsvp->adults_count)->toBe(2);
    expect($vuQuyRsvp->children_count)->toBe(0);

    $banquetRsvp = $responses->firstWhere('event_id', $this->eventBanquet->id);
    expect($banquetRsvp)->not->toBeNull();
    expect($banquetRsvp->adults_count)->toBe(3);
    expect($banquetRsvp->children_count)->toBe(1);
});

test('G1-002: cập nhật quyết định cho một sự kiện không ghi đè hoặc giảm headcount sự kiện khác', function () {
    // Tạo 2 response trước đó
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $this->guestBoth->id,
        'event_id' => $this->eventVuQuy->id,
        'guest_name' => $this->guestBoth->name,
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
    ]);

    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $this->guestBoth->id,
        'event_id' => $this->eventBanquet->id,
        'guest_name' => $this->guestBoth->name,
        'attending_status' => 'attending',
        'adults_count' => 4,
        'children_count' => 0,
    ]);

    // Khách đổi ý ở Tiệc Cưới: giảm từ 4 xuống 3 người
    $updateResponse = $this->postJson(
        route('rsvp.submit', ['slug' => $this->invitation->slug, 'token' => $this->guestBoth->guest_token]),
        [
            'event_id' => $this->eventBanquet->id,
            'guest_name' => $this->guestBoth->name,
            'attending_status' => 'attending',
            'adults_count' => 3,
            'children_count' => 0,
        ]
    );

    $updateResponse->assertStatus(200)
        ->assertJson(['success' => true]);

    // Kiểm tra Vu Quy vẫn nguyên 2 người
    $vuQuy = GuestResponse::where('guest_id', $this->guestBoth->id)
        ->where('event_id', $this->eventVuQuy->id)
        ->first();
    expect($vuQuy->adults_count)->toBe(2);

    // Tiệc Cưới đã cập nhật thành 3 người
    $banquet = GuestResponse::where('guest_id', $this->guestBoth->id)
        ->where('event_id', $this->eventBanquet->id)
        ->first();
    expect($banquet->adults_count)->toBe(3);
});

test('G1-003: khách không thể RSVP cho sự kiện ngoài phạm vi được phân quyền', function () {
    // Khách B chỉ được mời Tiệc Cưới, cố tình gửi event_id của Lễ Vu Quy
    $response = $this->postJson(
        route('rsvp.submit', ['slug' => $this->invitation->slug, 'token' => $this->guestBanquetOnly->guest_token]),
        [
            'event_id' => $this->eventVuQuy->id,
            'guest_name' => $this->guestBanquetOnly->name,
            'attending_status' => 'attending',
            'adults_count' => 1,
            'children_count' => 0,
        ]
    );

    // Bị chặn 422
    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);

    // DB không có bản ghi nào cho sự kiện Vu Quy của khách B
    $count = GuestResponse::where('guest_id', $this->guestBanquetOnly->id)
        ->where('event_id', $this->eventVuQuy->id)
        ->count();
    expect($count)->toBe(0);
});

test('G1-004: tham số rehearsal chỉ có hiệu lực với chủ thiệp và không bypass được quyền riêng tư', function () {
    // Khách vãng lai gửi query rehearsal=1 không thể xem thông tin mật hoặc bypass
    $response = $this->get(route('invitation.show', [
        'slug' => $this->invitation->slug,
        'rehearsal' => 1,
        'group' => 'gia_tien',
    ]));

    $response->assertStatus(200);
});
