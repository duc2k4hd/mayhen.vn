<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatingAndReceptionLifecycleDeepTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $stranger;

    private Wedding $wedding;

    private Invitation $invitation;

    private InvitationTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = InvitationTemplate::create([
            'name' => 'Giữa Muôn Ngàn Vì Sao',
            'slug' => 'giua-muon-ngan-vi-sao',
            'thumbnail_url' => '/assets/templates/vi-sao.webp',
            'category' => 'luxury',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Quốc Bảo',
            'email' => 'quocbao@example.com',
        ]);

        $this->stranger = User::factory()->create([
            'name' => 'Kẻ Lạ Mặt',
            'email' => 'kelamat@example.com',
        ]);

        $this->wedding = Wedding::create([
            'user_id' => $this->owner->id,
            'groom_name' => 'Quốc Bảo',
            'bride_name' => 'Thanh Trúc',
            'wedding_date' => Carbon::now()->addWeeks(3),
        ]);

        $this->invitation = Invitation::create([
            'user_id' => $this->owner->id,
            'wedding_id' => $this->wedding->id,
            'template_id' => $this->template->id,
            'slug' => 'quoc-bao-thanh-truc',
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(5),
            'custom_config' => [
                'checkin_pin' => '123456',
            ],
        ]);
    }

    /**
     * TEST 1: Khởi tạo bàn tiệc, tự động phân bố tọa độ và gắn kết khách mời có cùng tên bàn.
     */
    public function test_store_table_allocates_canvas_coordinates_and_links_unmapped_guests(): void
    {
        // Có sẵn 1 khách mời mang tên bàn nhưng chưa có table_id
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bác Ba Cần Thơ',
            'table_name' => 'Bàn VIP Hoàng Gia',
            'expected_attendees' => 2,
        ]);
        $this->assertNull($guest->table_id);

        $response = $this->actingAs($this->owner)
            ->postJson(route('seating.storeTable', $this->invitation->id), [
                'name' => 'Bàn VIP Hoàng Gia',
                'shape' => 'round',
                'capacity' => 10,
                'zone' => 'Khu Vực VIP',
                'notes' => 'Gần sân khấu chính',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $table = WeddingTable::where('name', 'Bàn VIP Hoàng Gia')->first();
        $this->assertNotNull($table);
        $this->assertEquals(10, $table->capacity);
        $this->assertEquals('round', $table->shape);
        $this->assertEquals('Khu Vực VIP', $table->zone);
        $this->assertGreaterThan(0, $table->pos_x);
        $this->assertGreaterThan(0, $table->pos_y);

        // Khách mời tự động được liên kết table_id
        $guest->refresh();
        $this->assertEquals($table->id, $guest->table_id);
    }

    /**
     * TEST 2: Cảnh báo quá tải sức chứa bàn tiệc (isOverCapacity) khi xếp khách vượt số ghế tối đa.
     */
    public function test_assigning_guest_beyond_capacity_triggers_overcapacity_warning(): void
    {
        $table = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn Nhỏ',
            'capacity' => 4,
        ]);

        $guest1 = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Gia đình Chú Bảy',
            'expected_attendees' => 3,
            'table_id' => $table->id,
            'table_name' => $table->name,
        ]);

        $guest2 = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Cô Tám',
            'expected_attendees' => 2,
        ]);

        // Xếp thêm Cô Tám (2 người) vào Bàn Nhỏ (sức chứa 4, tổng thành 5)
        $response = $this->actingAs($this->owner)
            ->postJson(route('seating.assignGuest', $this->invitation->id), [
                'guest_id' => $guest2->id,
                'table_id' => $table->id,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_over_capacity' => true,
        ]);
        $this->assertStringContainsString('quá tải', $response->json('warning'));
        $this->assertTrue($table->refresh()->isOverCapacity());
    }

    /**
     * TEST 3: Đổi tên bàn tiệc tự động đồng bộ tên bàn của toàn bộ khách mời thuộc bàn đó.
     */
    public function test_updating_table_name_cascades_to_all_attached_guests(): void
    {
        $table = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn 01',
            'capacity' => 8,
        ]);

        $g1 = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Khách A',
            'table_id' => $table->id,
            'table_name' => $table->name,
        ]);

        $g2 = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Khách B',
            'table_id' => $table->id,
            'table_name' => $table->name,
        ]);

        // Cập nhật tên bàn thành "Bàn Bạn Cấp 3"
        $response = $this->actingAs($this->owner)
            ->putJson(route('seating.updateTable', ['id' => $this->invitation->id, 'tableId' => $table->id]), [
                'name' => 'Bàn Bạn Cấp 3',
                'shape' => 'round',
                'capacity' => 8,
            ]);

        $response->assertOk();
        $this->assertEquals('Bàn Bạn Cấp 3', $table->refresh()->name);
        $this->assertEquals('Bàn Bạn Cấp 3', $g1->refresh()->table_name);
        $this->assertEquals('Bàn Bạn Cấp 3', $g2->refresh()->table_name);
    }

    /**
     * TEST 4: Xóa bàn tiệc không xóa khách mời (set table_id = null và table_name = null).
     */
    public function test_deleting_table_nullifies_guest_table_without_deleting_guests(): void
    {
        $table = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn Cần Xóa',
            'capacity' => 6,
        ]);

        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Khách Giữ Lại',
            'table_id' => $table->id,
            'table_name' => $table->name,
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('seating.deleteTable', ['id' => $this->invitation->id, 'tableId' => $table->id]));

        $response->assertOk();
        $this->assertDatabaseMissing('wedding_tables', ['id' => $table->id]);

        // Khách mời vẫn còn nguyên trong cơ sở dữ liệu
        $guest->refresh();
        $this->assertNotNull($guest);
        $this->assertNull($guest->table_id);
        $this->assertNull($guest->table_name);
    }

    /**
     * TEST 5: Quét QR điểm danh bóc tách token linh hoạt từ URL đầy đủ (?token=xyz hoặc /diem-danh/xyz).
     */
    public function test_process_scan_extracts_token_from_full_urls_flexibly(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Hoàng Khải',
            'guest_token' => 'custom_token_1234567890abcdef',
            'expected_attendees' => 2,
        ]);

        // Tiếp tân xác thực PIN session
        $this->withSession(["receptionist_verified_{$this->invitation->id}" => true]);

        // URL định dạng 1: Query string ?token=...
        $scanUrl1 = "https://mayhen.vn/thiep/{$this->invitation->slug}?token=custom_token_1234567890abcdef";
        $response1 = $this->postJson(route('checkin.scan', $this->invitation->slug), [
            'token' => $scanUrl1,
        ]);

        $response1->assertOk();
        $response1->assertJson([
            'success' => true,
            'already_checked_in' => false,
        ]);
        $guest->refresh();
        $this->assertNotNull($guest->checked_in_at);
        $this->assertEquals(2, $guest->checked_in_attendees);

        // URL định dạng 2: Route /diem-danh/... (Quét lại -> Báo đã điểm danh, không double count)
        $scanUrl2 = 'https://mayhen.vn/diem-danh/custom_token_1234567890abcdef';
        $response2 = $this->postJson(route('checkin.scan', $this->invitation->slug), [
            'token' => $scanUrl2,
        ]);

        $response2->assertOk();
        $response2->assertJson([
            'success' => true,
            'already_checked_in' => true,
        ]);
    }

    /**
     * TEST 6: Bàn Xử Lý Ngoại Lệ: Chuyển khách đã Declined sang Attending tự động nhận diện sĩ số >= 1 nếu không nhập số lượng.
     */
    public function test_convert_declined_guest_defaults_to_at_least_one_attendee(): void
    {
        $table = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn Dự Phòng',
            'capacity' => 10,
        ]);

        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Dì Mười',
            'expected_attendees' => 2,
        ]);

        // Khách từng bấm từ chối tham dự (Declined)
        $rsvp = GuestResponse::create([
            'wedding_id' => $this->wedding->id,
            'guest_id' => $guest->id,
            'guest_name' => $guest->name,
            'attending_status' => 'declined',
            'adults_count' => 0,
        ]);

        $this->withSession(["receptionist_verified_{$this->invitation->id}" => true]);

        // Tiếp tân bấm đổi sang tham dự mà không gõ số lượng khách
        $response = $this->postJson(route('checkin.exception.convert_declined', ['slug' => $this->invitation->slug, 'guestId' => $guest->id]), [
            'table_name' => 'Bàn Dự Phòng',
        ]);

        $response->assertOk();
        $guest->refresh();
        $rsvp->refresh();

        // Không được phép bằng 0! Phải nhận đúng 2 người (từ expected_attendees)
        $this->assertEquals(2, $guest->checked_in_attendees);
        $this->assertEquals(2, $rsvp->adults_count);
        $this->assertEquals('attending', $rsvp->attending_status);
        $this->assertEquals($table->id, $guest->table_id);
    }

    /**
     * TEST 7: Thêm khách phát sinh (Walk-in) tại cửa tiệc tạo đầy đủ token, rsvp, check-in và bàn tiệc.
     */
    public function test_walk_in_guest_creates_complete_records_and_checks_in_instantly(): void
    {
        $table = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn Khách Mời Thêm',
            'capacity' => 10,
        ]);

        $this->withSession(["receptionist_verified_{$this->invitation->id}" => true]);

        $response = $this->postJson(route('checkin.exception.walk_in', $this->invitation->slug), [
            'name' => 'Anh Tuấn Đồng Nghiệp Cũ',
            'salutation' => 'Anh',
            'group_name' => 'Khách Phát Sinh',
            'table_name' => 'Bàn Khách Mời Thêm',
            'attendees' => 3,
            'note' => 'Đến cùng vợ và con nhỏ',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $walkIn = Guest::where('name', 'Anh Tuấn Đồng Nghiệp Cũ')->first();
        $this->assertNotNull($walkIn);
        $this->assertEquals(3, $walkIn->expected_attendees);
        $this->assertEquals(3, $walkIn->checked_in_attendees);
        $this->assertNotNull($walkIn->checked_in_at);
        $this->assertEquals($table->id, $walkIn->table_id);

        // Có luôn phản hồi RSVP attending
        $this->assertDatabaseHas('guest_responses', [
            'guest_id' => $walkIn->id,
            'attending_status' => 'attending',
            'adults_count' => 3,
        ]);
    }

    /**
     * TEST 8: Hủy điểm danh (Undo Check-in) khi tiếp tân quét nhầm người.
     */
    public function test_undo_check_in_clears_timestamps_cleanly(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bác Bảy Điểm Nhầm',
            'expected_attendees' => 1,
            'checked_in_at' => now(),
            'checked_in_attendees' => 1,
        ]);

        $this->withSession(["receptionist_verified_{$this->invitation->id}" => true]);

        $response = $this->postJson(route('checkin.undo', ['slug' => $this->invitation->slug, 'guestId' => $guest->id]));

        $response->assertOk();
        $guest->refresh();
        $this->assertNull($guest->checked_in_at);
        $this->assertNull($guest->checked_in_attendees);
    }

    /**
     * TEST 9: Tenant Isolation: Người dùng lạ không thể xem, thêm, sửa hoặc xóa bàn tiệc của đám cưới khác (404).
     */
    public function test_seating_planner_tenant_isolation_protects_table_data(): void
    {
        $table = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn Đám Cưới A',
            'capacity' => 10,
        ]);

        // Người lạ xem studio xếp bàn -> 404
        $res1 = $this->actingAs($this->stranger)
            ->get(route('seating.index', $this->invitation->id));
        $res1->assertNotFound();

        // Người lạ xóa bàn -> 404
        $res2 = $this->actingAs($this->stranger)
            ->deleteJson(route('seating.deleteTable', ['id' => $this->invitation->id, 'tableId' => $table->id]));
        $res2->assertNotFound();

        // Bàn tiệc vẫn còn nguyên
        $this->assertDatabaseHas('wedding_tables', ['id' => $table->id]);
    }
}
