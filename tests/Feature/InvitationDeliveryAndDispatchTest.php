<?php

use App\Models\Guest;
use App\Models\GuestDelivery;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Thảo Vy',
        'wedding_date' => '2026-12-25',
        'venue_name' => 'Trung Tâm Hội Nghị White Palace',
        'venue_address' => '194 Hoàng Văn Thụ, Phú Nhuận, TP.HCM',
    ]);

    $this->template = InvitationTemplate::firstOrCreate(
        ['slug' => 'hac-bach-trao-duyen'],
        [
            'name' => 'Hắc Bạch Trao Duyên',
            'category' => 'luxury',
            'thumbnail_url' => 'https://mayhen.vn/assets/images/templates/hac-bach.webp',
            'is_active' => true,
        ]
    );

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'nam-vy-wedding',
        'is_published' => true,
        'revision_id' => 0,
    ]);

    $this->guest1 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn An',
        'salutation' => 'Anh',
        'phone' => '0901234567',
        'group_name' => 'Bạn Đại Học',
        'guest_token' => 'token_an_123',
        'expected_attendees' => 2,
        'is_sent' => false,
    ]);

    $this->guest2 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Trần Thị Bình',
        'salutation' => 'Chị',
        'phone' => '0912345678',
        'group_name' => 'Đồng Nghiệp',
        'guest_token' => 'token_binh_456',
        'expected_attendees' => 1,
        'is_sent' => false,
    ]);
});

test('unauthorized user cannot access delivery endpoints', function () {
    $this->actingAs($this->otherUser);

    // Preview
    $this->get(route('guests.delivery.preview', [$this->invitation->id, $this->guest1->id]))
        ->assertForbidden();

    // Single dispatch
    $this->post(route('guests.delivery.single', $this->guest1->id), [
        'channel' => 'copy',
    ])->assertForbidden();

    // Batch dispatch
    $this->post(route('guests.delivery.batch', $this->invitation->id), [
        'guest_ids' => [$this->guest1->id],
        'channel' => 'copy',
    ])->assertForbidden();

    // Stats
    $this->get(route('guests.delivery.stats', $this->invitation->id))
        ->assertForbidden();
});

test('owner can preview personalized invitation message with merge fields', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('guests.delivery.preview', [$this->invitation->id, $this->guest1->id]));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'guest' => [
                'id' => $this->guest1->id,
                'name' => 'Nguyễn Văn An',
                'salutation' => 'Anh',
            ],
            'channel' => 'copy',
        ]);

    $message = $response->json('message');
    expect($message)->toContain('Kính gửi Anh Nguyễn Văn An')
        ->toContain('Hoàng Nam & Thảo Vy')
        ->toContain('25/12/2026')
        ->toContain('White Palace')
        ->toContain('nam-vy-wedding')
        ->toContain('token_an_123');
});

test('owner can dispatch single invitation via copy channel', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('guests.delivery.single', $this->guest1->id), [
        'channel' => 'copy',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'guest' => [
                'id' => $this->guest1->id,
                'is_sent' => true,
            ],
        ]);

    // Kiểm tra bản ghi cơ sở dữ liệu
    expect($this->guest1->fresh()->is_sent)->toBeTrue();
    expect($this->guest1->fresh()->sent_at)->not->toBeNull();

    $delivery = GuestDelivery::where('guest_id', $this->guest1->id)->first();
    expect($delivery)->not->toBeNull();
    expect($delivery->channel)->toBe('copy');
    expect($delivery->status)->toBe('sent');
    expect($delivery->isSuccessful())->toBeTrue();
});

test('owner can dispatch single invitation via zalo channel with custom message', function () {
    $this->actingAs($this->user);

    $customText = 'Thân mời {salutation} {guest_name} ghé dự lễ cưới của {couple_names} tại {invitation_url} nha!';

    $response = $this->post(route('guests.delivery.single', $this->guest2->id), [
        'channel' => 'zalo',
        'custom_message' => $customText,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    $delivery = GuestDelivery::where('guest_id', $this->guest2->id)->first();
    expect($delivery)->not->toBeNull();
    expect($delivery->channel)->toBe('zalo');
    expect($delivery->recipient)->toBe('0912345678');
    expect($delivery->message_body)->toContain('Thân mời Chị Trần Thị Bình')
        ->toContain('Hoàng Nam & Thảo Vy');
});

test('owner can dispatch batch invitations to multiple guests', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('guests.delivery.batch', $this->invitation->id), [
        'guest_ids' => [$this->guest1->id, $this->guest2->id],
        'channel' => 'copy',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'result' => [
                'total' => 2,
                'successful' => 2,
                'failed' => 0,
            ],
        ]);

    expect($this->guest1->fresh()->is_sent)->toBeTrue();
    expect($this->guest2->fresh()->is_sent)->toBeTrue();
    expect(GuestDelivery::where('wedding_id', $this->wedding->id)->count())->toBe(2);
});

test('delivery stats returns accurate metrics for sent and unsent guests', function () {
    $this->actingAs($this->user);

    // Gửi thiệp cho guest 1
    $this->post(route('guests.delivery.single', $this->guest1->id), [
        'channel' => 'zalo',
    ]);

    // Giả lập guest 1 đã mở thiệp
    $this->guest1->update([
        'open_count' => 3,
        'opened_at' => now(),
    ]);

    $response = $this->get(route('guests.delivery.stats', $this->invitation->id));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'total_guests' => 2,
                'sent_guests' => 1,
                'unsent_guests' => 1,
                'opened_after_sent' => 1,
                'open_rate' => 100.0,
                'channel_breakdown' => [
                    'zalo' => 1,
                    'copy' => 0,
                    'sms' => 0,
                    'email' => 0,
                ],
            ],
        ]);
});

test('delivery history returns paginated list of deliveries with guest details', function () {
    $this->actingAs($this->user);

    // Gửi thiệp cho cả 2 khách
    $this->post(route('guests.delivery.single', $this->guest1->id), ['channel' => 'copy']);
    $this->post(route('guests.delivery.single', $this->guest2->id), ['channel' => 'sms']);

    $response = $this->get(route('guests.delivery.history', $this->invitation->id));

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    $data = $response->json('data.data');
    expect(count($data))->toBe(2);
    expect($data[0]['guest']['name'])->not->toBeEmpty();
});

test('guests page renders delivery cockpit button and stats', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('guests.index', $this->invitation->id));

    $response->assertOk();
    $response->assertSee('Phân Phối Thiệp &amp; Kênh Gửi', false);
    $response->assertSee('Đã Gửi Thiệp Cưới');
    $response->assertSee('Chiến Dịch Phân Phối Thiệp Cưới');
    $response->assertSee('CAMPAIGN DISPATCH COCKPIT');
});
