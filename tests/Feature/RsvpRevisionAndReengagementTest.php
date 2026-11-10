<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\GuestResponseLog;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Tuấn Khải',
        'bride_name' => 'Minh Thư',
        'wedding_date' => '2026-12-30',
        'venue_name' => 'GEM Center',
    ]);

    $this->template = InvitationTemplate::firstOrCreate(
        ['slug' => 'hac-bach-trao-duyen'],
        [
            'name' => 'Hắc Bạch Trao Duyên',
            'category' => 'luxury',
            'thumbnail_url' => 'https://mayhen.vn/assets/thumb.webp',
            'is_active' => true,
        ]
    );

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'khai-thu-wedding',
        'is_published' => true,
        'revision_id' => 0,
        'custom_config' => [
            'allow_rsvp' => true,
            'allow_rsvp_update' => true,
        ],
    ]);

    $this->guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lê Quốc Bảo',
        'salutation' => 'Anh',
        'phone' => '0933112233',
        'guest_token' => 'token_bao_777',
        'expected_attendees' => 2,
    ]);
});

test('first time guest rsvp submission creates response', function () {
    $response = $this->post(route('rsvp.submit', ['slug' => $this->invitation->slug, 'token' => $this->guest->guest_token]), [
        'guest_name' => 'Lê Quốc Bảo',
        'phone' => '0933112233',
        'attending_status' => 'attending',
        'adults_count' => 1,
        'children_count' => 0,
        'dietary_requirements' => 'Không cay',
        'wishes' => 'Chúc mừng hai bạn trăm năm hạnh phúc!',
    ], ['Accept' => 'application/json']);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'is_updated' => false,
            'attending_status' => 'attending',
        ]);

    $guestResponse = GuestResponse::where('guest_id', $this->guest->id)->first();
    expect($guestResponse)->not->toBeNull();
    expect($guestResponse->adults_count)->toBe(1);
    expect($guestResponse->dietary_requirements)->toBe('Không cay');
    expect(GuestResponseLog::count())->toBe(0);
});

test('guest can update existing rsvp when allow_rsvp_update is enabled, creating a revision log', function () {
    // 1. Gửi lần 1: Tham gia 1 người
    $this->post(route('rsvp.submit', ['slug' => $this->invitation->slug, 'token' => $this->guest->guest_token]), [
        'guest_name' => 'Lê Quốc Bảo',
        'phone' => '0933112233',
        'attending_status' => 'attending',
        'adults_count' => 1,
        'children_count' => 0,
        'wishes' => 'Chúc mừng hạnh phúc!',
    ], ['Accept' => 'application/json'])->assertOk();

    $initialResponse = GuestResponse::where('guest_id', $this->guest->id)->first();
    expect($initialResponse->adults_count)->toBe(1);

    // 2. Gửi lần 2 (cập nhật): Đổi sang tham gia 2 người lớn + 1 trẻ em + ăn chay
    $updateResponse = $this->post(route('rsvp.submit', ['slug' => $this->invitation->slug, 'token' => $this->guest->guest_token]), [
        'guest_name' => 'Lê Quốc Bảo',
        'phone' => '0933112233',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 1,
        'dietary_requirements' => '1 suất ăn chay',
        'wishes' => 'Mình dẫn theo bà xã và bé con nhé!',
    ], ['Accept' => 'application/json']);

    $updateResponse->assertOk()
        ->assertJson([
            'success' => true,
            'is_updated' => true,
            'attending_status' => 'attending',
        ]);

    // Kiểm tra bản ghi đã được cập nhật
    $freshResponse = $initialResponse->fresh();
    expect($freshResponse->adults_count)->toBe(2);
    expect($freshResponse->children_count)->toBe(1);
    expect($freshResponse->dietary_requirements)->toBe('1 suất ăn chay');

    // Kiểm tra bản ghi nhật ký biến động GuestResponseLog
    $log = GuestResponseLog::where('guest_response_id', $freshResponse->id)->first();
    expect($log)->not->toBeNull();
    expect($log->previous_status)->toBe('attending');
    expect($log->new_status)->toBe('attending');
    expect($log->previous_adults)->toBe(1);
    expect($log->new_adults)->toBe(2);
    expect($log->previous_children)->toBe(0);
    expect($log->new_children)->toBe(1);
    expect($log->new_dietary)->toBe('1 suất ăn chay');
    expect($log->source)->toBe('guest_online');
});

test('guest cannot update rsvp when allow_rsvp_update is disabled', function () {
    // Tắt tính năng cho phép cập nhật RSVP
    $this->invitation->update([
        'custom_config' => [
            'allow_rsvp' => true,
            'allow_rsvp_update' => false,
        ],
    ]);

    // Lần 1: Gửi thành công
    $this->post(route('rsvp.submit', ['slug' => $this->invitation->slug, 'token' => $this->guest->guest_token]), [
        'guest_name' => 'Lê Quốc Bảo',
        'phone' => '0933112233',
        'attending_status' => 'attending',
        'adults_count' => 1,
    ], ['Accept' => 'application/json'])->assertOk();

    // Lần 2: Bị từ chối với 422
    $response = $this->post(route('rsvp.submit', ['slug' => $this->invitation->slug, 'token' => $this->guest->guest_token]), [
        'guest_name' => 'Lê Quốc Bảo',
        'phone' => '0933112233',
        'attending_status' => 'declined',
    ], ['Accept' => 'application/json']);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi và không thể thay đổi.',
        ]);

    expect(GuestResponseLog::count())->toBe(0);
});

test('host manual rsvp update generates log with source manual_host', function () {
    $this->actingAs($this->user);

    // Khách đã có phản hồi trước đó
    $existing = GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $this->guest->id,
        'guest_name' => $this->guest->name,
        'attending_status' => 'attending',
        'adults_count' => 1,
    ]);

    // Chủ tiệc điều chỉnh thủ công thành declined
    $response = $this->post(route('guests.manual_rsvp', $this->guest->id), [
        'attending_status' => 'declined',
        'notes' => 'Khách gọi điện báo bận công tác đột xuất',
    ], ['Accept' => 'application/json']);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $log = GuestResponseLog::where('guest_response_id', $existing->id)->first();
    expect($log)->not->toBeNull();
    expect($log->previous_status)->toBe('attending');
    expect($log->new_status)->toBe('declined');
    expect($log->source)->toBe('manual_host');
});

test('owner can configure allow_rsvp_update in account settings', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('account.invitations.settings.update', $this->invitation->id), [
        'slug' => $this->invitation->slug,
        'allow_rsvp' => 1,
        'allow_rsvp_update' => 1, // Bật quyền cập nhật
        'rsvp_deadline' => '2026-12-25',
    ]);

    $response->assertRedirect();
    $config = $this->invitation->fresh()->custom_config;
    expect($config['allow_rsvp_update'])->toBeTrue();
});
