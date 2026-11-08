<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\StaffShiftHandoverService;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Autumn Breeze',
        'slug' => 'autumn-breeze-'.uniqid(),
        'blade_view' => 'client.invitations.autumn.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb.jpg',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Thành Đạt',
        'bride_name' => 'Mai Anh',
        'wedding_date' => now()->addDays(10),
        'venue_name' => 'Asiana Plaza Bình Thạnh',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'dat-anh-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
        'custom_config' => [
            'checkin_pin' => '112233',
        ],
    ]);
});

it('allows wedding owner to manage reception shifts via api and defends against idor', function () {
    $this->actingAs($this->user);

    // 1. Tạo ca trực mới
    $createResponse = $this->postJson(route('checkin.shifts.store', $this->invitation->id), [
        'name' => 'Ca 1 - Đón Khách Họ Hàng',
        'staff_name' => 'Lê Hoàng Nam',
        'staff_phone' => '0901234567',
        'pin' => '4321',
        'start_time' => '10:30',
        'end_time' => '12:00',
    ]);

    $createResponse->assertOk()
        ->assertJson([
            'success' => true,
            'shift' => [
                'name' => 'Ca 1 - Đón Khách Họ Hàng',
                'staff_name' => 'Lê Hoàng Nam',
                'pin' => '4321',
            ],
        ]);

    $shiftId = $createResponse->json('shift.id');

    // 2. Lấy danh sách ca trực
    $listResponse = $this->getJson(route('checkin.shifts.index', $this->invitation->id));
    $listResponse->assertOk()
        ->assertJsonCount(1, 'shifts');

    // 3. Xóa ca trực
    $deleteResponse = $this->deleteJson(route('checkin.shifts.delete', ['id' => $this->invitation->id, 'shiftId' => $shiftId]));
    $deleteResponse->assertOk()
        ->assertJson(['success' => true]);

    // 4. Phòng vệ IDOR: Tài khoản khác cố tình quản lý ca trực -> 404
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser);

    $forbidden = $this->getJson(route('checkin.shifts.index', $this->invitation->id));
    $forbidden->assertNotFound();
});

it('authenticates receptionist using specific shift pin and sets session', function () {
    $service = app(StaffShiftHandoverService::class);
    $shift = $service->saveShift($this->invitation, [
        'name' => 'Ca 2 - Đón Khách Bạn Bè',
        'staff_name' => 'Trần Thu Thảo',
        'pin' => '8899',
    ]);

    // Thử nhập sai PIN -> báo lỗi
    $wrongResponse = $this->post(route('checkin.verifyPin', $this->invitation->slug), [
        'pin' => '0000',
    ]);
    $wrongResponse->assertSessionHas('error');

    // Nhập đúng PIN ca trực -> đăng nhập thành công
    $successResponse = $this->post(route('checkin.verifyPin', $this->invitation->slug), [
        'pin' => '8899',
    ]);

    $successResponse->assertRedirect(route('checkin.portal', $this->invitation->slug))
        ->assertSessionHas("receptionist_verified_{$this->invitation->id}", true)
        ->assertSessionHas("reception_shift_{$this->invitation->id}", function ($val) use ($shift) {
            return $val['id'] === $shift['id'] && $val['staff_name'] === 'Trần Thu Thảo';
        });
});

it('prefixes guest checkin note with current staff name during shift checkin', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'name' => 'Anh Nguyễn Văn An',
        'expected_attendees' => 2,
        'guest_token' => 'token_test_123',
    ]);

    $shift = [
        'id' => 'shift_test_01',
        'name' => 'Ca Sáng',
        'staff_name' => 'Lê Hoàng Nam',
    ];

    $response = $this->withSession([
        "receptionist_verified_{$this->invitation->id}" => true,
        "reception_shift_{$this->invitation->id}" => $shift,
    ])->postJson(route('checkin.scan', $this->invitation->slug), [
        'token' => 'token_test_123',
        'attendees' => 2,
        'note' => 'Khách mang hoa chúc mừng',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    $guest->refresh();
    expect($guest->isCheckedIn())->toBeTrue()
        ->and($guest->check_in_note)->toContain('[Lê Hoàng Nam]')
        ->and($guest->check_in_note)->toContain('Khách mang hoa chúc mừng');
});

it('generates accurate shift handover summary and records handover log', function () {
    $guest1 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'name' => 'Bác Trần Văn Phú',
        'expected_attendees' => 2,
        'checked_in_at' => now(),
        'checked_in_attendees' => 2,
    ]);

    $guest2 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'name' => 'Chị Lê Thị Mai',
        'expected_attendees' => 1,
        'checked_in_at' => now(),
        'checked_in_attendees' => 1,
        'check_in_note' => 'Khách phát sinh tại bàn đón tiếp',
    ]);

    $guest3 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'name' => 'Bạn Nguyễn Văn Chưa Đến',
        'expected_attendees' => 1,
    ]);

    $service = app(StaffShiftHandoverService::class);
    $shift = $service->saveShift($this->invitation, [
        'name' => 'Ca 1',
        'staff_name' => 'Phạm Văn Tiến',
        'pin' => '5566',
    ]);

    // 1. Lấy biên bản tóm tắt ca trực qua API
    $summaryResponse = $this->withSession([
        "receptionist_verified_{$this->invitation->id}" => true,
        "reception_shift_{$this->invitation->id}" => $shift,
    ])->getJson(route('checkin.shift.summary', $this->invitation->slug));

    $summaryResponse->assertOk()
        ->assertJson([
            'success' => true,
            'summary' => [
                'summary' => [
                    'total_checked_in' => 2,
                    'total_attendees' => 3,
                    'walk_in_count' => 1,
                    'remaining_uncheckin_guests' => 1,
                ],
            ],
        ]);

    // 2. Gửi xác nhận bàn giao ca trực
    $handoverResponse = $this->withSession([
        "receptionist_verified_{$this->invitation->id}" => true,
        "reception_shift_{$this->invitation->id}" => $shift,
    ])->postJson(route('checkin.shift.handover', $this->invitation->slug), [
        'shift_id' => $shift['id'],
        'handed_to_staff' => 'Đỗ Minh Khang',
        'notes' => 'Bàn giao ca suôn sẻ, các bác họ hàng đã vào đủ',
    ]);

    $handoverResponse->assertOk()
        ->assertJson([
            'success' => true,
            'handover' => [
                'handed_from_staff' => 'Phạm Văn Tiến',
                'handed_to_staff' => 'Đỗ Minh Khang',
                'guests_checked_in' => 2,
                'attendees_checked_in' => 3,
            ],
        ]);

    // Kiểm tra cấu hình thiệp đã lưu lịch sử handover
    $this->invitation->refresh();
    $savedShifts = $this->invitation->custom_config['reception_shifts'] ?? [];
    $savedShift = collect($savedShifts)->firstWhere('id', $shift['id']);

    expect($savedShift['handovers'])->toHaveCount(1)
        ->and($savedShift['handovers'][0]['handed_to_staff'])->toBe('Đỗ Minh Khang');
});
