<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingEvent;
use App\Services\SmartGroupInvitationService;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Smart Groups',
        'slug' => 'mau-test-smart-groups-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Quang Huy',
        'bride_name' => 'Thùy Chi',
        'wedding_date' => now()->addMonths(3)->toDateString(),
        'venue_name' => 'White Palace Hoang Van Thu',
        'venue_address' => '194 Hoang Van Thu, Phu Nhuan, HCMC',
    ]);

    $this->eventVuQuy = WeddingEvent::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lễ Vu Quy (Nhà Gái)',
        'event_date' => now()->addMonths(3)->toDateString(),
        'start_time' => '09:00',
        'venue_name' => 'Tư Gia Nhà Gái',
        'is_active' => true,
        'display_order' => 1,
    ]);

    $this->eventBanquet = WeddingEvent::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Tiệc Cưới Chiêu Đãi',
        'event_date' => now()->addMonths(3)->toDateString(),
        'start_time' => '18:00',
        'venue_name' => 'White Palace',
        'is_active' => true,
        'display_order' => 2,
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'huy-chi-'.uniqid(),
        'title' => 'Thiệp Cưới Quang Huy & Thùy Chi',
        'is_published' => true,
    ]);
});

test('chủ thiệp có thể truy cập Studio Cấu Hình Thiệp Thông Minh Theo Nhóm Khách', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.smart_groups.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Thiệp Thông Minh Theo Nhóm Khách');
});

test('người dùng khác bị 403 khi truy cập cấu hình Smart Groups của chủ thiệp', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.smart_groups.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('chủ thiệp có thể lưu phân tuyến sự kiện và lời chào theo nhóm', function () {
    $response = $this->actingAs($this->user)
        ->post(route('account.smart_groups.update', $this->invitation->id), [
            'smart_groups' => [
                'dong_nghiep' => [
                    'greeting' => 'Trân trọng kính mời quý anh chị đồng nghiệp tới dự tiệc chiêu đãi.',
                    'title' => 'Khách Mời Đồng Nghiệp',
                    'allowed_event_ids' => [$this->eventBanquet->id],
                ],
                'gia_tien' => [
                    'greeting' => 'Kính mời họ hàng tới dự đầy đủ Lễ Vu Quy và Tiệc.',
                    'title' => 'Bà Con Họ Hàng',
                    'allowed_event_ids' => [$this->eventVuQuy->id, $this->eventBanquet->id],
                ],
            ],
        ]);

    $response->assertRedirect(route('account.smart_groups.index', $this->invitation->id));
    $response->assertSessionHas('success');

    $config = $this->invitation->fresh()->custom_config;
    expect($config['smart_groups']['dong_nghiep']['allowed_event_ids'])->toEqual([$this->eventBanquet->id]);
    expect($config['smart_groups']['gia_tien']['allowed_event_ids'])->toContain($this->eventVuQuy->id, $this->eventBanquet->id);
});

test('SmartGroupInvitationService lọc đúng sự kiện cho từng nhóm khách mời', function () {
    $this->invitation->update([
        'custom_config' => [
            'smart_groups' => [
                'dong_nghiep' => [
                    'allowed_event_ids' => [$this->eventBanquet->id],
                ],
            ],
        ],
    ]);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Minh Quân',
        'group_name' => 'Đồng nghiệp công ty',
        'guest_token' => 'TOKEN_COLLEAGUE_'.uniqid(),
    ]);

    $filtered = SmartGroupInvitationService::filterEventsForGuest($this->wedding, $guest, $this->invitation);

    expect($filtered->pluck('id')->all())->toEqual([$this->eventBanquet->id]);
    expect($filtered->pluck('id')->all())->not->toContain($this->eventVuQuy->id);
});
