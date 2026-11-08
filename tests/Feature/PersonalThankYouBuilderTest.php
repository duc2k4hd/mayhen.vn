<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\PersonalThankYouService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Rose Elegance',
        'slug' => 'rose-elegance-'.uniqid(),
        'blade_view' => 'client.invitations.rose.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb.jpg',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Văn Minh',
        'bride_name' => 'Thảo Linh',
        'wedding_date' => '2026-11-20',
        'venue_name' => 'White Palace Hoàng Văn Thụ',
        'venue_address' => '194 Hoàng Văn Thụ, Phường 9, Phú Nhuận, TP.HCM',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'minh-linh-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
    ]);

    // 1. Khách VIP Họ Hàng đã tham dự (check-in)
    $this->attendedVipGuest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Trần Văn Phú',
        'salutation' => 'Bác',
        'group_name' => 'Họ Hàng Nhà Trai',
        'is_sent' => true,
        'phone' => '0901234567',
        'checked_in_at' => now(),
        'expected_attendees' => 2,
    ]);

    // 2. Khách Bạn Bè đã tham dự (RSVP attending)
    $this->attendedFriendGuest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lê Quốc Bảo',
        'salutation' => 'Bạn',
        'group_name' => 'Bạn Đại Học',
        'is_sent' => true,
        'phone' => '0912345678',
        'expected_attendees' => 1,
    ]);
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $this->attendedFriendGuest->id,
        'guest_name' => 'Lê Quốc Bảo',
        'attending_status' => 'attending',
        'adults_count' => 1,
        'children_count' => 0,
    ]);

    // 3. Khách báo không thể đến nhưng gửi quà/chúc mừng (RSVP declined)
    $this->giftOnlyGuest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Chú Hoàng Nam',
        'salutation' => 'Chú',
        'group_name' => 'Gia Đình Đồng Nghiệp',
        'is_sent' => true,
        'phone' => '0988776655',
        'expected_attendees' => 1,
    ]);
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $this->giftOnlyGuest->id,
        'guest_name' => 'Chú Hoàng Nam',
        'attending_status' => 'declined',
        'adults_count' => 0,
        'children_count' => 0,
        'wishes' => 'Chúc hai cháu trăm năm hạnh phúc! Chú gửi quà mừng nhé!',
    ]);

    // 4. Khách chung chưa phản hồi
    $this->generalGuest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Phạm Thu Hương',
        'salutation' => 'Em',
        'group_name' => 'Hàng Xóm',
        'is_sent' => true,
        'phone' => '0977112233',
        'expected_attendees' => 1,
    ]);
});

it('blocks unauthorized access to personal thank-you builder API (IDOR defense)', function () {
    // Khách vãng lai chưa đăng nhập -> 404
    $this->getJson(route('guests.thankyou_builder', $this->invitation->id))
        ->assertNotFound();

    // Người dùng khác không sở hữu thiệp -> 404
    $this->actingAs($this->otherUser)
        ->getJson(route('guests.thankyou_builder', $this->invitation->id))
        ->assertNotFound();
});

it('builds thank-you plan successfully for wedding owner', function () {
    $response = $this->actingAs($this->user)
        ->getJson(route('guests.thankyou_builder', $this->invitation->id));

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'success',
            'data' => [
                'stats' => [
                    'total_guests',
                    'attended_count',
                    'gift_only_count',
                    'general_count',
                ],
                'album_link',
                'messages',
            ],
        ]);

    $data = $response->json('data');
    expect($data['stats']['total_guests'])->toBe(4);
    expect($data['stats']['attended_count'])->toBe(2);
    expect($data['stats']['gift_only_count'])->toBe(1);
    expect($data['stats']['general_count'])->toBe(1);
    expect($data['album_link'])->toContain($this->invitation->slug);
});

it('classifies guests accurately into attended, gift_only, and general categories', function () {
    $service = app(PersonalThankYouService::class);
    $plan = $service->buildThankYouPlan($this->wedding, $this->invitation);

    $messages = collect($plan['messages'])->keyBy('guest_id');

    // 1. Khách check-in -> attended
    expect($messages->get($this->attendedVipGuest->id)['category'])->toBe('attended');
    expect($messages->get($this->attendedVipGuest->id)['category_label'])->toBe('Đã đến chung vui');

    // 2. Khách RSVP attending -> attended
    expect($messages->get($this->attendedFriendGuest->id)['category'])->toBe('attended');

    // 3. Khách RSVP declined -> gift_only
    expect($messages->get($this->giftOnlyGuest->id)['category'])->toBe('gift_only');
    expect($messages->get($this->giftOnlyGuest->id)['category_label'])->toBe('Gửi quà / chúc phúc từ xa');

    // 4. Khách chưa phản hồi -> general
    expect($messages->get($this->generalGuest->id)['category'])->toBe('general');
});

it('customizes messages respectfully for senior family vs friends', function () {
    $service = app(PersonalThankYouService::class);
    $plan = $service->buildThankYouPlan($this->wedding, $this->invitation);

    $messages = collect($plan['messages'])->keyBy('guest_id');

    // Lời chúc cho bậc tiền bối (Họ Hàng)
    $vipMsg = $messages->get($this->attendedVipGuest->id)['suggested_message'];
    expect($vipMsg)->toContain('Kính gửi Bác Trần Văn Phú');
    expect($vipMsg)->toContain('vợ chồng con');
    expect($vipMsg)->toContain('Văn Minh & Thảo Linh');
    expect($vipMsg)->toContain('trân trọng gửi lời cảm ơn sâu sắc nhất');
    expect($vipMsg)->toContain('niềm vinh hạnh to lớn của gia đình hai bên');

    // Lời chúc cho Bạn bè
    $friendMsg = $messages->get($this->attendedFriendGuest->id)['suggested_message'];
    expect($friendMsg)->toContain('Gửi Bạn Lê Quốc Bảo thân mến!');
    expect($friendMsg)->toContain('quẩy hết mình');
    expect($friendMsg)->toContain('khoảnh khắc siêu đáng nhớ');

    // Lời cảm ơn cho người gửi quà từ xa
    $giftMsg = $messages->get($this->giftOnlyGuest->id)['suggested_message'];
    expect($giftMsg)->toContain('Kính gửi Chú Hoàng Nam');
    expect($giftMsg)->toContain('món quà ý nghĩa');
    expect($giftMsg)->toContain('không thể trực tiếp tham dự');
});
