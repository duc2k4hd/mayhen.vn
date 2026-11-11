<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingEvent;
use App\Services\StationeryKitService;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Stationery Kit',
        'slug' => 'mau-test-stationery-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Nguyễn Minh Quân',
        'bride_name' => 'Lê Hoàng Yến',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'Trung Tâm Hội Nghị White Palace',
        'venue_address' => '194 Hoàng Văn Thụ, Phú Nhuận, TP.HCM',
        'extra_data' => [
            'groom_father' => 'Nguyễn Văn An',
            'groom_mother' => 'Trần Thị Bình',
            'bride_father' => 'Lê Văn Cường',
            'bride_mother' => 'Phạm Thị Dung',
            'event_party_time' => '11:30',
            'event_party_lunar' => 'Ngày 18 tháng 11 năm Bính Ngọ',
            'quote' => 'Tình yêu trọn vẹn là cùng nhau nhìn về một hướng.',
        ],
    ]);

    // Tạo sự kiện timeline
    WeddingEvent::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Đón Tiếp Quan Khách',
        'event_type' => 'banquet',
        'event_date' => $this->wedding->wedding_date,
        'event_time' => '10:30',
        'venue_name' => 'Trung Tâm Hội Nghị White Palace',
        'display_order' => 1,
        'is_active' => true,
    ]);

    WeddingEvent::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Khai Tiệc Mừng',
        'event_type' => 'banquet',
        'event_date' => $this->wedding->wedding_date,
        'event_time' => '11:30',
        'venue_name' => 'Trung Tâm Hội Nghị White Palace',
        'display_order' => 2,
        'is_active' => true,
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thiep-stationery-test-'.uniqid(),
        'status' => 'published',
        'is_published' => true,
    ]);
});

test('guest cannot access stationery studio without login', function () {
    $response = $this->get(route('account.stationery.index', $this->invitation->id));
    $response->assertRedirect(route('login'));
});

test('user cannot access stationery studio of another user wedding', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)
        ->get(route('account.stationery.index', $this->invitation->id));

    $response->assertStatus(404);
});

test('owner can access stationery studio and see wedding data and presets', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.stationery.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Nguyễn Minh Quân');
    $response->assertSee('Lê Hoàng Yến');
    $response->assertSee('Ảnh Chia Sẻ Story 9:16');
    $response->assertSee('Standee QR Đặt Bàn Tiệc');
    $response->assertSee('Lịch Trình Tiệc Cưới');
    $response->assertSee('Thiệp Báo Hỷ');
    $response->assertSee('Thiệp Cảm Ơn');
    $response->assertSee('Đỏ Nhung Hoàng Gia &amp; Gold', false);
});

test('stationery service generates accurate stationery data structure and qr uri', function () {
    $data = StationeryKitService::getStationeryData($this->wedding, $this->invitation);

    expect($data)->toHaveKey('groom_name', 'Nguyễn Minh Quân');
    expect($data)->toHaveKey('bride_name', 'Lê Hoàng Yến');
    expect($data)->toHaveKey('venue_name', 'Trung Tâm Hội Nghị White Palace');
    expect($data)->toHaveKey('qr_code_data_uri');
    expect($data['qr_code_data_uri'])->toStartWith('data:image/svg+xml;base64,');
    expect($data['timeline'])->toHaveCount(2);
    expect($data['timeline'][0]['title'])->toBe('Đón Tiếp Quan Khách');
});

test('owner can log stationery kit download action via api', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.stationery.log_download', $this->invitation->id), [
            'preset' => 'story',
            'palette' => 'velvet_crimson',
            'format' => 'png',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Đã ghi nhận lượt tải ấn phẩm thành công.',
    ]);
});

test('log download validates preset and format correctly', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.stationery.log_download', $this->invitation->id), [
            'preset' => 'invalid_preset_format',
            'palette' => 'velvet_crimson',
            'format' => 'exe',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['preset', 'format']);
});
