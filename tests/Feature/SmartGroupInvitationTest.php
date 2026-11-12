<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingEvent;
use App\Services\SmartGroupInvitationService;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Smart Groups',
        'slug' => 'mau-test-smart-groups-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Đặng Tuấn Kiệt',
        'bride_name' => 'Ngô Bảo Ngọc',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'White Palace Hoàng Văn Thụ',
        'venue_address' => '194 Hoàng Văn Thụ, Phú Nhuận, TP.HCM',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'tuan-kiet-bao-ngoc-'.uniqid(),
        'title' => 'Thiệp Cưới Tuấn Kiệt & Bảo Ngọc',
        'is_published' => true,
    ]);

    // Sự kiện 1: Lễ Gia Tiên buổi sáng tại tư gia
    $this->eventGiaTien = WeddingEvent::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lễ Vu Quy & Gia Tiên',
        'event_type' => 'ceremony',
        'event_date' => $this->wedding->wedding_date,
        'event_time' => '07:30',
        'venue_name' => 'Tư gia Nhà Gái',
        'display_order' => 1,
        'is_active' => true,
    ]);

    // Sự kiện 2: Tiệc Chiêu Đãi buổi tối tại Trung tâm hội nghị
    $this->eventTiec = WeddingEvent::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Tiệc Cưới Chiêu Đãi',
        'event_type' => 'banquet',
        'event_date' => $this->wedding->wedding_date,
        'event_time' => '18:00',
        'venue_name' => 'White Palace Hoàng Văn Thụ',
        'display_order' => 2,
        'is_active' => true,
    ]);
});

test('khách chưa đăng nhập không thể truy cập cấu hình nhóm thông minh', function () {
    $this->get(route('account.smart_groups.index', $this->invitation->id))
        ->assertRedirect(route('login'));

    $this->post(route('account.smart_groups.update', $this->invitation->id), [])
        ->assertRedirect(route('login'));
});

test('người dùng khác không có quyền thao tác trên thiệp của người khác (403)', function () {
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('account.smart_groups.index', $this->invitation->id))
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->post(route('account.smart_groups.update', $this->invitation->id), [])
        ->assertForbidden();
});

test('chủ sở hữu truy cập thành công màn hình cấu hình thiệp theo nhóm khách', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.smart_groups.index', $this->invitation->id));

    $response->assertOk()
        ->assertSee('Thiệp Thông Minh Theo Nhóm Khách')
        ->assertSee('Họ Hàng — Dự Lễ Gia Tiên &amp; Rước Dâu', false)
        ->assertSee('Đồng Nghiệp &amp; Đối Tác', false)
        ->assertSee('Lễ Vu Quy &amp; Gia Tiên', false)
        ->assertSee('Tiệc Cưới Chiêu Đãi');
});

test('chủ sở hữu lưu thành công cấu hình phân tuyến sự kiện và lời mời cho nhóm', function () {
    $response = $this->actingAs($this->user)
        ->post(route('account.smart_groups.update', $this->invitation->id), [
            'smart_groups' => [
                'dong_nghiep' => [
                    'title' => 'Thân Mời Quý Đồng Nghiệp',
                    'greeting' => 'Trân trọng kính mời {salutation} {guest_name} đến nâng ly chúc mừng cùng {couple_name}!',
                    'allowed_event_ids' => [$this->eventTiec->id], // Chỉ cho phép xem Tiệc tối
                ],
                'gia_tien' => [
                    'title' => 'Kính Mời Bà Con Thân Tộc',
                    'greeting' => 'Kính mời {salutation} {guest_name} tham dự lễ gia tiên bái đường cùng {couple_name}.',
                    'allowed_event_ids' => [$this->eventGiaTien->id, $this->eventTiec->id], // Xem cả 2
                ],
            ],
        ]);

    $response->assertRedirect(route('account.smart_groups.index', $this->invitation->id))
        ->assertSessionHas('success');

    $this->invitation->refresh();
    $smartConfig = $this->invitation->custom_config['smart_groups'] ?? [];

    expect($smartConfig['dong_nghiep']['allowed_event_ids'])->toBe([$this->eventTiec->id])
        ->and($smartConfig['gia_tien']['allowed_event_ids'])->toBe([$this->eventGiaTien->id, $this->eventTiec->id]);
});

test('SmartGroupInvitationService lọc chính xác sự kiện hiển thị cho từng nhóm khách', function () {
    // Cấu hình: Nhóm 'dong_nghiep' chỉ được xem eventTiec
    $this->invitation->update([
        'custom_config' => [
            'smart_groups' => [
                'dong_nghiep' => [
                    'allowed_event_ids' => [$this->eventTiec->id],
                ],
            ],
        ],
    ]);

    // Khách A: Nhóm Đồng Nghiệp
    $guestDongNghiep = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Hải Nam',
        'salutation' => 'Anh',
        'group_name' => 'Đồng Nghiệp',
    ]);

    // Khách B: Nhóm Họ Hàng Gia Tiên
    $guestHohang = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Bảy Đặng',
        'salutation' => 'Bác',
        'group_name' => 'Họ Hàng Gia Tiên',
    ]);

    $eventsForNam = SmartGroupInvitationService::filterEventsForGuest($this->wedding, $guestDongNghiep, $this->invitation);
    $eventsForBacBay = SmartGroupInvitationService::filterEventsForGuest($this->wedding, $guestHohang, $this->invitation);

    // Khách Nam chỉ thấy 1 sự kiện (Tiệc tối)
    expect($eventsForNam)->toHaveCount(1)
        ->and($eventsForNam->first()->id)->toBe($this->eventTiec->id);

    // Bác Bảy thấy cả 2 sự kiện
    expect($eventsForBacBay)->toHaveCount(2);
});

test('SmartGroupInvitationService sinh đúng lời mời tùy biến và thay thế placeholder', function () {
    $this->invitation->update([
        'custom_config' => [
            'smart_groups' => [
                'ban_than' => [
                    'title' => 'Gửi Bạn Yêu',
                    'greeting' => 'Chào {salutation} {guest_name}, quẩy hết mình cùng {couple_name} nhé!',
                ],
            ],
        ],
    ]);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Trần Minh Đức',
        'salutation' => 'Bạn',
        'group_name' => 'Bạn Thân',
    ]);

    $greeting = SmartGroupInvitationService::resolveGroupGreeting($this->invitation, $guest);

    expect($greeting)->not->toBeNull()
        ->and($greeting['title'])->toBe('Gửi Bạn Yêu')
        ->and($greeting['message'])->toBe('Chào Bạn Trần Minh Đức, quẩy hết mình cùng Đặng Tuấn Kiệt & Ngô Bảo Ngọc nhé!');
});
