<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm',
        'slug' => 'mau-invariants-'.uniqid(),
        'description' => 'Test Template',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.hac-bach-trao-duyen.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Bảo Nam',
        'bride_name' => 'Tường Vy',
        'wedding_date' => now()->addDays(30),
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'bao-nam-tuong-vy-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'checkin_pin' => '654321',
        ],
    ]);

    $this->table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn VIP 01',
        'shape' => 'round',
        'capacity' => 10,
        'zone' => 'Khu VIP',
        'display_order' => 1,
    ]);
});

test('table headcount dynamically adapts to rsvp attending count', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn A',
        'expected_attendees' => 1,
        'table_id' => $this->table->id,
        'table_name' => $this->table->name,
    ]);

    // Ban đầu chưa RSVP, tính theo expected_attendees = 1
    expect($this->table->fresh()->assigned_attendees_count)->toBe(1);

    // Khách RSVP báo đi 3 người (2 người lớn, 1 trẻ em)
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest->id,
        'guest_name' => $guest->name,
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 1,
    ]);

    // Bàn tiệc trong Seating Planner phải tự động cập nhật lên 3 ghế
    expect($this->table->fresh()->assigned_attendees_count)->toBe(3);
    expect($this->table->fresh()->remaining_seats)->toBe(7);
});

test('table headcount releases seat when guest declines rsvp', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Trần Thị B',
        'expected_attendees' => 2,
        'table_id' => $this->table->id,
        'table_name' => $this->table->name,
    ]);

    expect($this->table->fresh()->assigned_attendees_count)->toBe(2);

    // Khách báo bận không tham dự (declined)
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest->id,
        'guest_name' => $guest->name,
        'attending_status' => 'declined',
        'adults_count' => 0,
        'children_count' => 0,
    ]);

    // Ghế được giải phóng về 0
    expect($this->table->fresh()->assigned_attendees_count)->toBe(0);
    expect($this->table->fresh()->remaining_seats)->toBe(10);
});

test('checkin scan automatically uses rsvp headcount', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lê Hoàng C',
        'expected_attendees' => 1,
        'guest_token' => 'token-test-checkin-rsvp-12345',
    ]);

    // Khách RSVP 4 người
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest->id,
        'guest_name' => $guest->name,
        'attending_status' => 'attending',
        'adults_count' => 3,
        'children_count' => 1,
    ]);

    // Tiếp tân quét mã QR mà không nhập số người đè lên
    $this->actingAs($this->user);
    $response = $this->postJson(route('checkin.scan', $this->invitation->slug), [
        'token' => 'token-test-checkin-rsvp-12345',
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('guest.checked_in_attendees', 4);

    $guest->refresh();
    expect($guest->checked_in_at)->not->toBeNull();
    expect($guest->checked_in_attendees)->toBe(4);
});

test('guest management update syncs table_id with seating planner', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Phạm Minh D',
        'expected_attendees' => 2,
        'table_id' => null,
        'table_name' => null,
    ]);

    $this->actingAs($this->user);

    // Cập nhật khách và gán tên bàn "Bàn VIP 01"
    $response = $this->put(route('guests.update', $guest->id), [
        'name' => 'Phạm Minh D',
        'salutation' => 'Thân mời',
        'group_name' => 'Đồng Nghiệp',
        'table_name' => 'Bàn VIP 01',
        'expected_attendees' => 2,
    ]);

    $response->assertRedirect();
    $guest->refresh();

    // Kiểm tra table_id được tự động liên kết với bàn có sẵn
    expect($guest->table_id)->toBe($this->table->id);
    expect($guest->table_name)->toBe('Bàn VIP 01');

    // Khách này không còn nằm trong danh sách chưa xếp bàn của Seating Planner
    $unassigned = $this->wedding->guests()->whereNull('table_id')->pluck('id')->all();
    expect($unassigned)->not->toContain($guest->id);

    // Khi xóa bàn khỏi khách mời, table_id phải về null
    $this->put(route('guests.update', $guest->id), [
        'name' => 'Phạm Minh D',
        'salutation' => 'Thân mời',
        'group_name' => 'Đồng Nghiệp',
        'table_name' => '',
        'expected_attendees' => 2,
    ]);

    $guest->refresh();
    expect($guest->table_id)->toBeNull();
    expect($guest->table_name)->toBeNull();
});

test('guest store and batch store sync table_id', function () {
    $this->actingAs($this->user);

    // 1. Store đơn lẻ
    $this->post(route('guests.store', $this->invitation->id), [
        'name' => 'Võ Thị E',
        'salutation' => 'Bạn',
        'group_name' => 'Cấp 3',
        'table_name' => 'Bàn VIP 01',
        'expected_attendees' => 2,
    ]);

    $guestE = $this->wedding->guests()->where('name', 'Võ Thị E')->first();
    expect($guestE)->not->toBeNull();
    expect($guestE->table_id)->toBe($this->table->id);

    // 2. Batch store
    $batchData = 'Đỗ Văn F | Anh | Bạn Đại Học | 2 | 0987654321 | Bàn VIP 01';
    $this->post(route('guests.batch_store', $this->invitation->id), [
        'batch_input' => $batchData,
    ]);

    $guestF = $this->wedding->guests()->where('name', 'Đỗ Văn F')->first();
    expect($guestF)->not->toBeNull();
    expect($guestF->table_id)->toBe($this->table->id);
});

test('flow 07: rsvp multi-transition lifecycle synchronizes table seats and checkin headcount without breaking guest token', function () {
    // 1. Khách mời được khởi tạo với 3 suất ban đầu và gán vào Bàn VIP 01 (sức chứa 10)
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Anh Tuấn',
        'expected_attendees' => 3,
        'table_id' => $this->table->id,
        'table_name' => $this->table->name,
    ]);

    $originalToken = $guest->guest_token;
    expect($originalToken)->not->toBeEmpty();
    expect($this->table->fresh()->assigned_attendees_count)->toBe(3);
    expect($this->table->fresh()->remaining_seats)->toBe(7);

    // 2. Khách phản hồi RSVP: Có tham dự 3 người (2 người lớn, 1 trẻ em)
    $response = GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest->id,
        'guest_name' => $guest->name,
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 1,
    ]);

    expect($this->table->fresh()->assigned_attendees_count)->toBe(3);
    expect($this->table->fresh()->remaining_seats)->toBe(7);
    expect($guest->fresh()->guest_token)->toBe($originalToken);

    // 3. Khách đổi ý báo bận: Không tham dự (declined)
    $response->update([
        'attending_status' => 'declined',
        'adults_count' => 0,
        'children_count' => 0,
    ]);

    // Bàn tiệc lập tức giải phóng ghế về 0, toàn bộ 10 ghế trống trở lại
    expect($this->table->fresh()->assigned_attendees_count)->toBe(0);
    expect($this->table->fresh()->remaining_seats)->toBe(10);
    expect($guest->fresh()->guest_token)->toBe($originalToken); // Token không bị xóa

    // 4. Khách sắp xếp được thời gian và xác nhận lại: Có tham dự 2 người lớn
    $response->update([
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
    ]);

    // Bàn tiệc tự động chiếm đúng 2 ghế, còn 8 ghế trống
    expect($this->table->fresh()->assigned_attendees_count)->toBe(2);
    expect($this->table->fresh()->remaining_seats)->toBe(8);
    expect($guest->fresh()->actualAttendeesCount())->toBe(2);
    expect($guest->fresh()->guest_token)->toBe($originalToken);

    // 5. Đến ngày cưới, lễ tân quét QR token của khách: Check-in tự động nhận diện đúng 2 suất
    $this->actingAs($this->user);
    $scanResponse = $this->postJson(route('checkin.scan', $this->invitation->slug), [
        'token' => $originalToken,
    ]);

    $scanResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('already_checked_in', false)
        ->assertJsonPath('guest.checked_in_attendees', 2);

    $freshGuest = $guest->fresh();
    expect($freshGuest->checked_in_at)->not->toBeNull();
    expect($freshGuest->checked_in_attendees)->toBe(2);
    expect($freshGuest->guest_token)->toBe($originalToken);
});

test('flow 10: repeated checkin scan is idempotent and partial attendee checkin records exact count', function () {
    // Khách mời được mời 3 suất
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Hoàng Minh Châu',
        'expected_attendees' => 3,
        'table_id' => $this->table->id,
        'table_name' => $this->table->name,
    ]);

    $this->actingAs($this->user);

    // 1. Check-in một phần: Khách đi 2 trong tổng số 3 suất
    $firstScan = $this->postJson(route('checkin.scan', $this->invitation->slug), [
        'token' => $guest->guest_token,
        'attendees' => 2,
        'note' => 'Đi 2 người, 1 người bận',
    ]);

    $firstScan->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('already_checked_in', false)
        ->assertJsonPath('guest.checked_in_attendees', 2);

    $guest->refresh();
    $firstCheckedInAt = $guest->checked_in_at;
    expect($firstCheckedInAt)->not->toBeNull();
    expect($guest->checked_in_attendees)->toBe(2);

    // 2. Quét lại lần 2 (lễ tân khác hoặc quét nhầm): Không được tăng số lượng, không đổi mốc thời gian
    $secondScan = $this->postJson(route('checkin.scan', $this->invitation->slug), [
        'token' => $guest->guest_token,
        'attendees' => 5, // Cố tình truyền số khác
    ]);

    $secondScan->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('already_checked_in', true)
        ->assertJsonPath('previous_time', $firstCheckedInAt->format('H:i'));

    $guest->refresh();
    // Vẫn giữ nguyên 2 suất và thời gian check-in đầu tiên, không bị ghi đè
    expect($guest->checked_in_attendees)->toBe(2);
    expect($guest->checked_in_at->timestamp)->toBe($firstCheckedInAt->timestamp);
});
