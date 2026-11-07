<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Partner;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::where('slug', 'giua-muon-ngan-vi-sao')->first() ?? InvitationTemplate::create([
        'name' => 'Giữa Muôn Ngàn Vì Sao',
        'slug' => 'giua-muon-ngan-vi-sao',
        'description' => 'Test Template for Seating Planner',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.giua-muon-ngan-vi-sao.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Thành Nam',
        'bride_name' => 'Bảo Ngọc',
        'wedding_date' => now()->addDays(20),
        'venue_name' => 'Grand Palace Sài Gòn',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thanh-nam-bao-ngoc-'.uniqid(),
        'is_published' => true,
    ]);
});

test('chủ sở hữu truy cập được seating planner studio', function () {
    $table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn VIP 01 - Họ Nhà Trai',
        'shape' => 'round',
        'capacity' => 10,
        'pos_x' => 100,
        'pos_y' => 150,
    ]);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Chú Ba',
        'table_id' => $table->id,
        'table_name' => $table->name,
        'expected_attendees' => 2,
    ]);

    $unassigned = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bạn Học Đại Học',
        'expected_attendees' => 1,
    ]);

    $response = $this->actingAs($this->user)->get(route('seating.index', $this->invitation->id));
    $response->assertStatus(200);
    $response->assertSee('SEATING PLANNER STUDIO');
    $response->assertSee('Thành Nam &amp; Bảo Ngọc', false);
    $response->assertSee('Bàn VIP 01 - Họ Nhà Trai');
    $response->assertSee('Chú Ba');
    $response->assertSee('Bạn Học Đại Học');
});

test('người lạ không có quyền truy cập seating planner của cặp đôi', function () {
    $stranger = User::factory()->create();

    $response = $this->actingAs($stranger)->get(route('seating.index', $this->invitation->id));
    $response->assertStatus(404);
});

test('đối tác studio sở hữu đám cưới có quyền truy cập seating planner', function () {
    $partnerUser = User::factory()->create(['role' => 'partner']);
    $partner = Partner::create([
        'user_id' => $partnerUser->id,
        'business_name' => 'Studio Hoàng Gia',
        'slug' => 'studio-hoang-gia',
        'partner_code' => 'MH-PARTNER-HG',
        'phone' => '0912334455',
        'email' => 'hg@studio.vn',
        'status' => 'active',
    ]);

    $this->wedding->update(['partner_id' => $partner->id]);

    $response = $this->actingAs($partnerUser)->get(route('seating.index', $this->invitation->id));
    $response->assertStatus(200);
    $response->assertSee('Sơ Đồ Xếp Chỗ');
    $response->assertSee('Bàn Tiệc Cưới');
});

test('cặp đôi có thể tạo bàn tiệc mới trong sảnh', function () {
    $response = $this->actingAs($this->user)->post(route('seating.storeTable', $this->invitation->id), [
        'name' => 'Bàn Bạn Cấp 3',
        'shape' => 'round',
        'capacity' => 10,
        'zone' => 'Cánh Phải',
        'notes' => 'Bàn dành riêng cho hội bạn thân',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('wedding_tables', [
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn Bạn Cấp 3',
        'shape' => 'round',
        'capacity' => 10,
        'zone' => 'Cánh Phải',
    ]);
});

test('cặp đôi có thể cập nhật bàn tiệc và đồng bộ tên bàn cho khách', function () {
    $table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn 05',
        'shape' => 'round',
        'capacity' => 10,
    ]);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Anh Tuấn',
        'table_id' => $table->id,
        'table_name' => 'Bàn 05',
    ]);

    $response = $this->actingAs($this->user)->put(route('seating.updateTable', [
        'id' => $this->invitation->id,
        'tableId' => $table->id,
    ]), [
        'name' => 'Bàn VIP Doanh Nhân',
        'shape' => 'long',
        'capacity' => 12,
        'zone' => 'VIP',
    ]);

    $response->assertRedirect();

    expect($table->fresh()->name)->toBe('Bàn VIP Doanh Nhân')
        ->and($table->fresh()->shape)->toBe('long')
        ->and($table->fresh()->capacity)->toBe(12)
        ->and($guest->fresh()->table_name)->toBe('Bàn VIP Doanh Nhân');
});

test('xóa bàn tiệc sẽ chuyển toàn bộ khách trong bàn về danh sách chưa có bàn', function () {
    $table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn Xóa',
        'shape' => 'round',
        'capacity' => 10,
    ]);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Chị Hằng',
        'table_id' => $table->id,
        'table_name' => 'Bàn Xóa',
    ]);

    $response = $this->actingAs($this->user)->delete(route('seating.deleteTable', [
        'id' => $this->invitation->id,
        'tableId' => $table->id,
    ]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('wedding_tables', ['id' => $table->id]);

    $freshGuest = $guest->fresh();
    expect($freshGuest->table_id)->toBeNull()
        ->and($freshGuest->table_name)->toBeNull();
});

test('cặp đôi có thể gán khách vào bàn tiệc qua API', function () {
    $table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn Đồng Nghiệp FPT',
        'shape' => 'round',
        'capacity' => 10,
    ]);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Đồng Nghiệp A',
    ]);

    $response = $this->actingAs($this->user)->postJson(route('seating.assignGuest', $this->invitation->id), [
        'guest_id' => $guest->id,
        'table_id' => $table->id,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $freshGuest = $guest->fresh();
    expect($freshGuest->table_id)->toBe($table->id)
        ->and($freshGuest->table_name)->toBe('Bàn Đồng Nghiệp FPT');
});

test('cặp đôi có thể gỡ khách khỏi bàn tiệc về danh sách chưa có bàn', function () {
    $table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn Bạn Thơ Ấu',
        'shape' => 'round',
        'capacity' => 10,
    ]);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bạn Thân',
        'table_id' => $table->id,
        'table_name' => 'Bàn Bạn Thơ Ấu',
    ]);

    $response = $this->actingAs($this->user)->postJson(route('seating.assignGuest', $this->invitation->id), [
        'guest_id' => $guest->id,
        'table_id' => null,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $freshGuest = $guest->fresh();
    expect($freshGuest->table_id)->toBeNull()
        ->and($freshGuest->table_name)->toBeNull();
});

test('cặp đôi có thể cập nhật vị trí tọa độ kéo thả các bàn trên canvas', function () {
    $table1 = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn 1',
        'pos_x' => 50,
        'pos_y' => 50,
    ]);

    $table2 = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn 2',
        'pos_x' => 100,
        'pos_y' => 100,
    ]);

    $response = $this->actingAs($this->user)->postJson(route('seating.updatePositions', $this->invitation->id), [
        'positions' => [
            ['id' => $table1->id, 'pos_x' => 250, 'pos_y' => 300],
            ['id' => $table2->id, 'pos_x' => 450, 'pos_y' => 500],
        ],
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    expect($table1->fresh()->pos_x)->toBe(250)
        ->and($table1->fresh()->pos_y)->toBe(300)
        ->and($table2->fresh()->pos_x)->toBe(450)
        ->and($table2->fresh()->pos_y)->toBe(500);
});

test('cặp đôi có thể mở trang in sơ đồ sảnh tiệc chuẩn A4/A3', function () {
    $table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn VIP Danh Dự',
        'capacity' => 8,
    ]);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Hai',
        'table_id' => $table->id,
        'table_name' => $table->name,
        'expected_attendees' => 2,
    ]);

    $response = $this->actingAs($this->user)->get(route('seating.print', $this->invitation->id));
    $response->assertStatus(200);
    $response->assertSee('SƠ ĐỒ BÀN TIỆC');
    $response->assertSee('BẢNG ĐIỀU PHỐI ĐÓN KHÁCH');
    $response->assertSee('Thành Nam');
    $response->assertSee('Bảo Ngọc');
    $response->assertSee('Bàn VIP Danh Dự');
    $response->assertSee('Bác Hai');
});
