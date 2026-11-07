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
