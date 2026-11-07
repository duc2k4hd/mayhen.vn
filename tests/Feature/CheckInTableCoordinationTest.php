<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm',
        'slug' => 'mau-thu-nghiem-coord-'.uniqid(),
        'description' => 'Test Template',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.hac-bach-trao-duyen.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Đức Duy',
        'bride_name' => 'Ngọc Mai',
        'wedding_date' => now()->addDays(30),
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'duc-duy-ngoc-mai-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'checkin_pin' => '654321',
        ],
    ]);
});

test('có thể thêm và cập nhật khách mời kèm thông tin bàn tiệc', function () {
    $this->actingAs($this->user);

    // 1. Thêm khách mới có bàn tiệc
    $response = $this->post(route('guests.store', $this->invitation->id), [
        'name' => 'Nguyễn Văn Minh',
        'salutation' => 'Thân mời',
        'group_name' => 'Bạn Đại Học',
        'table_name' => 'Bàn 08 - CNTT',
        'expected_attendees' => 2,
        'phone' => '0988776655',
    ]);

    $response->assertRedirect(route('guests.index', $this->invitation->id));

    $guest = Guest::where('name', 'Nguyễn Văn Minh')->first();
    expect($guest)->not->toBeNull();
    expect($guest->table_name)->toBe('Bàn 08 - CNTT');
    expect($guest->isCheckedIn())->toBeFalse();

    // 2. Cập nhật đổi bàn tiệc
    $updateRes = $this->put(route('guests.update', $guest->id), [
        'name' => 'Nguyễn Văn Minh (Vợ Chồng)',
        'salutation' => 'Thương mời',
        'group_name' => 'Bạn Thân',
        'table_name' => 'Bàn VIP 01',
        'expected_attendees' => 2,
    ]);

    $updateRes->assertSessionHasNoErrors();
    $guest->refresh();
    expect($guest->table_name)->toBe('Bàn VIP 01');
    expect($guest->group_name)->toBe('Bạn Thân');
});

test('có thể nhập nhanh khách mời kèm cột bàn tiệc', function () {
    $this->actingAs($this->user);

    $batchInput = "Lê Thị Lan | Thân mời | Bạn Cấp 3 | 2 | 0912345678 | Bàn 03\n"
        ."Trần Văn Cường | Kính mời | Đồng Nghiệp | 1 | | Bàn 09\n"
        .'Gia đình Bác Hải | Kính mời | Họ Nhà Gái | 4 | 0933221100 | Bàn VIP 02';

    $response = $this->post(route('guests.batch_store', $this->invitation->id), [
        'batch_input' => $batchInput,
        'default_salutation' => 'Thân mời',
        'default_group' => 'Khách Chung',
    ]);

    $response->assertRedirect(route('guests.index', $this->invitation->id));

    $g1 = Guest::where('name', 'Lê Thị Lan')->first();
    expect($g1)->not->toBeNull();
    expect($g1->table_name)->toBe('Bàn 03');

    $g2 = Guest::where('name', 'Trần Văn Cường')->first();
    expect($g2)->not->toBeNull();
    expect($g2->table_name)->toBe('Bàn 09');

    $g3 = Guest::where('name', 'Gia đình Bác Hải')->first();
    expect($g3)->not->toBeNull();
    expect($g3->table_name)->toBe('Bàn VIP 02');
});

test('tiếp tân phải nhập đúng mã PIN để mở cổng điểm danh', function () {
    // 1. Khách vãng lai chưa nhập PIN -> Thấy form nhập PIN
    $response = $this->get(route('checkin.portal', $this->invitation->slug));
    $response->assertStatus(200);
    $response->assertSee('Vui lòng nhập mã PIN tiếp tân');

    // 2. Nhập sai PIN -> Bị báo lỗi
    $verifyWrong = $this->post(route('checkin.verifyPin', $this->invitation->slug), [
        'pin' => '000000',
    ]);
    $verifyWrong->assertSessionHas('error');

    // 3. Nhập đúng PIN (654321) -> Thành công
    $verifyCorrect = $this->post(route('checkin.verifyPin', $this->invitation->slug), [
        'pin' => '654321',
    ]);
    $verifyCorrect->assertRedirect(route('checkin.portal', $this->invitation->slug));

    // 4. Mở lại cổng tiếp tân -> Vào thẳng giao diện quét camera
    $afterVerify = $this->get(route('checkin.portal', $this->invitation->slug));
    $afterVerify->assertStatus(200);
    $afterVerify->assertSee('TRỰC TIẾP ĐÓN KHÁCH');
});

test('chủ đám cưới vào thẳng cổng tiếp tân và bảng điều phối không cần nhập PIN', function () {
    $this->actingAs($this->user);

    $portal = $this->get(route('checkin.portal', $this->invitation->slug));
    $portal->assertStatus(200);
    $portal->assertSee('TRỰC TIẾP ĐÓN KHÁCH');

    $commandCenter = $this->get(route('coordination.index', $this->invitation->id));
    $commandCenter->assertStatus(200);
    $commandCenter->assertSee('Trung Tâm Điều Phối & Sĩ Số Ngày Cưới Realtime', false);
    $commandCenter->assertSee('654321'); // PIN tiếp tân
});

test('người lạ không có quyền xem bảng điều phối ngày cưới', function () {
    $stranger = User::factory()->create();
    $this->actingAs($stranger);

    $response = $this->get(route('coordination.index', $this->invitation->id));
    $response->assertStatus(404);
});

test('quét mã QR điểm danh khách mời và phản hồi vị trí bàn tiệc', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Phạm Hoàng Nam',
        'salutation' => 'Anh',
        'group_name' => 'Bạn Thân',
        'table_name' => 'Bàn 07 - VIP',
        'expected_attendees' => 2,
    ]);

    // Giả lập tiếp tân đã xác thực PIN
    session(["receptionist_verified_{$this->invitation->id}" => true]);

    // 1. Quét lần đầu
    $response = $this->postJson(route('checkin.scan', $this->invitation->slug), [
        'token' => $guest->guest_token,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'already_checked_in' => false,
        'guest' => [
            'name' => 'Phạm Hoàng Nam',
            'table_name' => 'Bàn 07 - VIP',
            'expected_attendees' => 2,
        ],
    ]);

    $guest->refresh();
    expect($guest->isCheckedIn())->toBeTrue();
    expect($guest->checked_in_at)->not->toBeNull();

    // 2. Quét lại lần 2 -> Cảnh báo đã điểm danh
    $res2 = $this->postJson(route('checkin.scan', $this->invitation->slug), [
        'token' => $guest->guest_token,
    ]);

    $res2->assertStatus(200);
    $res2->assertJson([
        'success' => true,
        'already_checked_in' => true,
    ]);
});

test('tiếp tân có thể tìm nhanh, đổi bàn và hủy điểm danh cho khách', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Võ Thị Bích Trâm',
        'salutation' => 'Chị',
        'group_name' => 'Đồng Nghiệp',
        'table_name' => 'Bàn 02',
        'phone' => '0909112233',
        'expected_attendees' => 1,
    ]);

    session(["receptionist_verified_{$this->invitation->id}" => true]);

    // 1. Tìm nhanh
    $searchRes = $this->getJson(route('checkin.quickSearch', ['slug' => $this->invitation->slug, 'q' => 'Bích Trâm']));
    $searchRes->assertStatus(200);
    $searchRes->assertJsonFragment(['name' => 'Võ Thị Bích Trâm']);

    // 2. Đổi bàn tiệc
    $updateTableRes = $this->postJson(url("api/diem-danh-tiec/{$this->invitation->slug}/ban-tiec/{$guest->id}"), [
        'table_name' => 'Bàn 05 - VIP Cty',
    ]);
    $updateTableRes->assertStatus(200);
    $updateTableRes->assertJson(['success' => true, 'table_name' => 'Bàn 05 - VIP Cty']);
    $guest->refresh();
    expect($guest->table_name)->toBe('Bàn 05 - VIP Cty');

    // 3. Điểm danh qua ID
    $checkInRes = $this->postJson(route('checkin.scan', $this->invitation->slug), [
        'guest_id' => $guest->id,
    ]);
    $checkInRes->assertStatus(200);
    $guest->refresh();
    expect($guest->isCheckedIn())->toBeTrue();

    // 4. Hủy điểm danh
    $undoRes = $this->postJson(url("api/diem-danh-tiec/{$this->invitation->slug}/huy/{$guest->id}"));
    $undoRes->assertStatus(200);
    $guest->refresh();
    expect($guest->isCheckedIn())->toBeFalse();
});

test('api liveStats trả về đúng thống kê realtime và sóng lấp đầy bàn tiệc', function () {
    $this->actingAs($this->user);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Khách 1',
        'table_name' => 'Bàn 01',
        'expected_attendees' => 2,
        'checked_in_at' => now(),
        'checked_in_attendees' => 2,
    ]);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Khách 2',
        'table_name' => 'Bàn 01',
        'expected_attendees' => 3,
    ]);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Khách 3',
        'table_name' => 'Bàn 02',
        'expected_attendees' => 4,
    ]);

    $statsRes = $this->getJson(route('coordination.stats', $this->invitation->id));
    $statsRes->assertStatus(200);
    $data = $statsRes->json();

    expect($data['stats']['total_expected_attendees'])->toBe(9);
    expect($data['stats']['checked_in_count'])->toBe(2);
    expect($data['stats']['pending_attendees'])->toBe(7);
    expect($data['tables'])->toBeArray();
    expect(count($data['tables']))->toBeGreaterThanOrEqual(2);
});

test('khách mở thẻ VIP cá nhân trực tiếp bằng guest_token', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Đỗ Thành Đạt',
        'salutation' => 'Anh',
        'group_name' => 'Đối Tác',
        'table_name' => 'Bàn VIP 01',
        'expected_attendees' => 2,
    ]);

    $response = $this->get(route('checkin.direct', $guest->guest_token));
    $response->assertStatus(200);
    $response->assertSee('MÂY HẸN WEDDING VIP PASS');
    $response->assertSee('Đỗ Thành Đạt');
    $response->assertSee('Bàn VIP 01');
});

test('xuất csv danh sách khách mời có đầy đủ cột bàn tiệc và thời gian điểm danh', function () {
    $this->actingAs($this->user);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lâm Tâm Như',
        'salutation' => 'Chị',
        'group_name' => 'Bạn Đại Học',
        'table_name' => 'Bàn 12 - Nghệ Thuật',
        'expected_attendees' => 2,
        'checked_in_at' => now(),
        'checked_in_attendees' => 2,
    ]);

    $response = $this->get(route('guests.export', $this->invitation->id));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    // Lấy nội dung file stream tải về
    ob_start();
    $response->sendContent();
    $csvContent = ob_get_clean();

    expect($csvContent)->toContain('Bàn Tiệc');
    expect($csvContent)->toContain('Điểm Danh Ngày Cưới');
    expect($csvContent)->toContain('Bàn 12 - Nghệ Thuật');
    expect($csvContent)->toContain('Đã có mặt');
});
