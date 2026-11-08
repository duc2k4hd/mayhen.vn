<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\GentleReminderPlannerService;

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
        'groom_name' => 'Thành Nam',
        'bride_name' => 'Thục Quyên',
        'wedding_date' => '2026-12-30',
        'venue_name' => 'Gala Center Hoàng Văn Thụ',
        'venue_address' => '415 Hoàng Văn Thụ, Tân Bình, TP.HCM',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'nam-quyen-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
    ]);

    // 1. Khách chưa gửi thiệp
    $this->unsentGuest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Trần Văn An',
        'salutation' => 'Bác',
        'group_name' => 'Họ Hàng Nhà Trai',
        'is_sent' => false,
        'phone' => '0901234567',
        'expected_attendees' => 2,
    ]);

    // 2. Khách đã gửi thiệp nhưng chưa phản hồi RSVP
    $this->unresponsiveGuest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Minh Tuấn',
        'salutation' => 'Bạn',
        'group_name' => 'Bạn Đại Học',
        'is_sent' => true,
        'phone' => '0987654321',
        'expected_attendees' => 1,
    ]);

    // 3. Khách đã phản hồi tham dự nhưng chưa gán bàn
    $this->unseatedGuest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Chị Lê Thị Mai',
        'salutation' => 'Chị',
        'group_name' => 'Đồng Nghiệp',
        'is_sent' => true,
        'table_name' => null, // Chưa xếp bàn
        'expected_attendees' => 2,
    ]);

    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $this->unseatedGuest->id,
        'guest_name' => 'Chị Lê Thị Mai',
        'attending_status' => 'attending',
        'adults_count' => 2,
    ]);
});

test('it plans gentle reminders accurately categorized by guest state', function () {
    $service = app(GentleReminderPlannerService::class);
    $plan = $service->plan($this->wedding, $this->invitation);

    expect($plan['stats']['total_guests'])->toBe(3);
    expect($plan['stats']['unsent_count'])->toBe(1);
    expect($plan['stats']['unresponsive_count'])->toBe(1);
    expect($plan['stats']['attending_unseated_count'])->toBe(1);

    // Kiểm tra tin nhắn người lớn tuổi
    $seniorReminder = collect($plan['reminders'])->firstWhere('guest_id', $this->unsentGuest->id);
    expect($seniorReminder['suggested_message'])->toContain('Kính gửi Bác');
    expect($seniorReminder['zalo_link'])->toBe('https://zalo.me/0901234567');

    // Kiểm tra tin nhắn bạn bè
    $friendReminder = collect($plan['reminders'])->firstWhere('guest_id', $this->unresponsiveGuest->id);
    expect($friendReminder['suggested_message'])->toContain('Alo Bạn');

    // Kiểm tra khung giờ đề xuất
    expect($plan['recommended_time']['time_windows'])->toHaveCount(2);
});

test('owner can fetch gentle reminder plan via api', function () {
    $response = $this->actingAs($this->user)->getJson(route('guests.reminder_planner', $this->invitation->id));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'data' => [
            'stats' => [
                'total_guests' => 3,
                'unresponsive_count' => 1,
                'unsent_count' => 1,
                'attending_unseated_count' => 1,
            ],
        ],
    ]);
});

test('stranger cannot fetch reminder plan of another user via api', function () {
    $response = $this->actingAs($this->otherUser)->getJson(route('guests.reminder_planner', $this->invitation->id));

    $response->assertNotFound();
});

test('guests page displays reminder modal and action button', function () {
    $response = $this->actingAs($this->user)->get(route('guests.index', $this->invitation->id));

    $response->assertOk();
    $response->assertSee('btn-open-reminder-modal');
    $response->assertSee('Nhắc Nhở Tế Nhị');
    $response->assertSee('mayhen_modal_reminder_planner');
});
