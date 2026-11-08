<?php

use App\Models\Guest;
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
        'name' => 'Mẫu Thử Nghiệm PreSend',
        'slug' => 'mau-presend-'.uniqid(),
        'description' => 'Test Template PreSend',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.tron-kiep-nay-ben-nhau.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->owner->id,
        'groom_name' => 'Hoàng Phúc',
        'bride_name' => 'Khánh Vy',
        'wedding_date' => now()->addDays(30),
        'venue_name' => 'The Adora Dynasty',
        'cover_photo_url' => '/assets/client/images/cover.jpg',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'hoang-phuc-khanh-vy-'.uniqid(),
        'is_published' => true,
    ]);
});

test('pre-send validation api analyzes guest readiness accurately', function () {
    // 1 khách chuẩn 100%
    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn Hoàn Hảo',
        'salutation' => 'Anh',
        'group_name' => 'Đồng Nghiệp',
        'phone' => '0912345678',
        'expected_attendees' => 2,
    ]);

    // 1 khách thiếu salutation
    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Trần Thị B',
        'salutation' => null,
        'group_name' => 'Bạn Bè',
        'phone' => '0987654321',
        'expected_attendees' => 1,
    ]);

    // 1 khách thiếu group_name
    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lê Văn C',
        'salutation' => 'Bác',
        'group_name' => null,
        'expected_attendees' => 1,
    ]);

    // 1 khách trùng tên với khách B (case insensitive)
    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'trần thị b',
        'salutation' => 'Chị',
        'group_name' => 'Bạn Bè',
        'expected_attendees' => 1,
    ]);

    $response = $this->actingAs($this->owner)->getJson(route('guests.pre_send_validation', $this->invitation->id));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'data' => [
            'total_guests' => 4,
            'is_invitation_published' => true,
        ],
    ]);

    $data = $response->json('data');
    expect($data['readiness_score'])->toBeLessThan(100);
    expect($data['ready_guests_count'])->toBe(1);
    expect($data['flagged_guests_count'])->toBe(3);

    // Kiểm tra danh sách các lỗi được phân loại đúng
    expect($data['issues'])->toHaveKey('missing_salutation');
    expect($data['issues']['missing_salutation']['count'])->toBe(1);

    expect($data['issues'])->toHaveKey('missing_group');
    expect($data['issues']['missing_group']['count'])->toBe(1);

    expect($data['issues'])->toHaveKey('duplicate_candidates');
    expect($data['issues']['duplicate_candidates']['count'])->toBe(1);

    // Kiểm tra trợ lý xưng hô lễ nghi
    expect($data['etiquette_suggestions'])->toHaveKey('Gia Đình & Họ Hàng');
    expect($data['etiquette_suggestions'])->toHaveKey('Bạn Bè');
});

test('pre-send validation flags unpublished invitation warning', function () {
    $this->invitation->update(['is_published' => false]);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn A',
        'salutation' => 'Anh',
        'group_name' => 'Bạn Bè',
        'expected_attendees' => 1,
    ]);

    $response = $this->actingAs($this->owner)->getJson(route('guests.pre_send_validation', $this->invitation->id));

    $response->assertOk();
    $data = $response->json('data');
    expect($data['is_invitation_published'])->toBeFalse();
    expect($data['issues'])->toHaveKey('unpublished');
});

test('unauthorized user cannot access pre-send validation desk', function () {
    $response = $this->actingAs($this->otherUser)->getJson(route('guests.pre_send_validation', $this->invitation->id));

    $response->assertNotFound();
});
