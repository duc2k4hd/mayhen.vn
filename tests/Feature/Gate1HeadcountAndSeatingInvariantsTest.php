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

class Gate1HeadcountAndSeatingInvariantsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Wedding $wedding;

    private Invitation $invitation;

    private InvitationTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = InvitationTemplate::create([
            'name' => 'Celestial Romance',
            'slug' => 'celestial-romance',
            'thumbnail_url' => '/assets/templates/celestial.webp',
            'category' => 'luxury',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create();
        $this->wedding = Wedding::create([
            'user_id' => $this->owner->id,
            'groom_name' => 'Tuấn Kiệt',
            'bride_name' => 'Hương Giang',
            'wedding_date' => Carbon::now()->addMonths(2),
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $this->template->id,
            'slug' => 'kiet-giang-wedding',
            'is_published' => true,
            'expires_at' => Carbon::now()->addYear(),
            'custom_config' => [
                'checkin_pin' => '654321',
            ],
        ]);
    }

    public function test_inv11_guest_token_is_automatically_generated_and_at_least_32_chars(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Nguyễn Văn An',
            'expected_attendees' => 2,
        ]);

        $this->assertNotEmpty($guest->guest_token);
        $this->assertGreaterThanOrEqual(32, strlen($guest->guest_token));
    }

    public function test_inv12_actual_attendees_count_respects_rsvp_status(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Trần Thị Bích',
            'expected_attendees' => 3,
        ]);

        // 1. Chưa có RSVP: trả về expected_attendees (3)
        $this->assertEquals(3, $guest->actualAttendeesCount());

        // 2. RSVP Attending với 2 người lớn + 1 trẻ em: trả về 3
        $response = GuestResponse::create([
            'wedding_id' => $this->wedding->id,
            'guest_id' => $guest->id,
            'guest_name' => $guest->name,
            'attending_status' => 'attending',
            'adults_count' => 2,
            'children_count' => 1,
        ]);
        $guest->refresh();
        $this->assertEquals(3, $guest->actualAttendeesCount());

        // 3. RSVP Declined: trả về 0
        $response->update(['attending_status' => 'declined']);
        $guest->refresh();
        $this->assertEquals(0, $guest->actualAttendeesCount());
    }

    public function test_inv13_and_inv14_table_headcount_dynamically_reflects_rsvp_changes(): void
    {
        $table = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn VIP 01',
            'capacity' => 10,
        ]);

        $guest1 = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Khách 1',
            'expected_attendees' => 2,
            'table_id' => $table->id,
        ]);

        $guest2 = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Khách 2',
            'expected_attendees' => 3,
            'table_id' => $table->id,
        ]);

        $table->refresh();
        // Ban đầu cả 2 chưa RSVP: 2 + 3 = 5 ghế
        $this->assertEquals(5, $table->assigned_attendees_count);

        // Khách 2 báo bận (declined RSVP)
        GuestResponse::create([
            'wedding_id' => $this->wedding->id,
            'guest_id' => $guest2->id,
            'guest_name' => $guest2->name,
            'attending_status' => 'declined',
            'adults_count' => 0,
            'children_count' => 0,
        ]);

        $table->refresh();
        // Sĩ số bàn tiệc giảm tức thì xuống 2 ghế (INV-14)
        $this->assertEquals(2, $table->assigned_attendees_count);
    }

    public function test_inv15_bidirectional_table_sync_and_table_rename(): void
    {
        $table = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn Bạn Cấp 3',
            'capacity' => 10,
        ]);

        // 1. Gán qua table_name tự động khớp table_id
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Lê Hoàng Nam',
            'table_name' => 'Bàn Bạn Cấp 3',
        ]);
        $this->assertEquals($table->id, $guest->table_id);

        // 2. Đổi tên bàn tiệc tự động đồng bộ sang table_name của khách
        $table->update(['name' => 'Bàn Bạn Cấp 3 VIP']);
        $guest->refresh();
        $this->assertEquals('Bàn Bạn Cấp 3 VIP', $guest->table_name);

        // 3. Xóa table_name tự động gỡ table_id
        $guest->update(['table_name' => null]);
        $this->assertNull($guest->table_id);
    }

    public function test_inv16_deleting_table_nullifies_guest_references_without_deleting_guest(): void
    {
        $table = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn Đồng Nghiệp',
            'capacity' => 8,
        ]);

        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Phạm Minh Quân',
            'table_id' => $table->id,
        ]);

        $this->assertEquals($table->id, $guest->table_id);
        $this->assertEquals('Bàn Đồng Nghiệp', $guest->table_name);

        // Xóa bàn tiệc
        $table->delete();

        // Khách mời không bị xóa, table_id và table_name được gỡ về null an toàn (INV-16)
        $guest->refresh();
        $this->assertNull($guest->table_id);
        $this->assertNull($guest->table_name);
        $this->assertDatabaseHas('guests', ['id' => $guest->id]);
    }

    public function test_inv17_and_inv18_reception_checkin_and_rescan_idempotency(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Đỗ Văn Thành',
            'expected_attendees' => 2,
        ]);

        // Xác lập phiên lễ tân với mã PIN hợp lệ
        $pin = '654321';
        $verifyRes = $this->post(route('checkin.verifyPin', $this->invitation->slug), [
            'pin' => $pin,
        ]);
        $verifyRes->assertSessionHas("receptionist_verified_{$this->invitation->id}", true);

        // Lần 1: Quét mã QR lần đầu không kèm attendees -> tự ghi nhận theo actualAttendeesCount (2)
        $scan1 = $this->postJson(route('checkin.scan', $this->invitation->slug), [
            'token' => $guest->guest_token,
        ]);

        $scan1->assertStatus(200);
        $scan1->assertJson([
            'success' => true,
            'already_checked_in' => false,
            'guest' => [
                'name' => 'Đỗ Văn Thành',
                'checked_in_attendees' => 2,
            ],
        ]);

        $guest->refresh();
        $this->assertTrue($guest->isCheckedIn());
        $this->assertEquals(2, $guest->checked_in_attendees);

        // Lần 2: Quét lại mã QR của khách đã vào tiệc -> trả về already_checked_in = true, không tăng sĩ số
        $scan2 = $this->postJson(route('checkin.scan', $this->invitation->slug), [
            'token' => $guest->guest_token,
        ]);

        $scan2->assertStatus(200);
        $scan2->assertJson([
            'success' => true,
            'already_checked_in' => true,
        ]);
        $this->assertEquals(2, $scan2->json('stats.checked_in_count'));
    }

    public function test_inv19_and_inv20_rsvp_cross_wedding_and_owner_prevention(): void
    {
        // 1. INV-20: Chủ thiệp đang đăng nhập không thể tự gửi RSVP cho thiệp của mình
        $ownerRes = $this->actingAs($this->owner)
            ->postJson(route('rsvp.submit', $this->invitation->slug), [
                'guest_name' => 'Chủ Đám Cưới',
                'attending_status' => 'attending',
                'adults_count' => 1,
            ]);

        $ownerRes->assertStatus(403);
        $ownerRes->assertJson([
            'success' => false,
            'message' => 'Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi xác nhận tham dự cho chính mình.',
        ]);

        // 2. INV-19: Token từ đám cưới B không thể nộp RSVP cho đám cưới A
        $otherWedding = Wedding::create([
            'user_id' => User::factory()->create()->id,
            'groom_name' => 'Người Khác',
            'bride_name' => 'Bạn Khác',
            'wedding_date' => Carbon::now()->addMonths(3),
        ]);
        $alienGuest = Guest::create([
            'wedding_id' => $otherWedding->id,
            'name' => 'Khách Đám Cưới B',
            'expected_attendees' => 1,
        ]);

        auth()->logout();
        $alienRes = $this->postJson(route('rsvp.submit', ['slug' => $this->invitation->slug, 'token' => $alienGuest->guest_token]), [
            'guest_name' => 'Khách Đám Cưới B',
            'attending_status' => 'attending',
            'adults_count' => 1,
        ]);

        $alienRes->assertStatus(404);
    }

    public function test_cross_module_headcount_consistency_flow(): void
    {
        $table1 = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn Thân Thiết',
            'capacity' => 10,
        ]);

        $table2 = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn Danh Dự',
            'capacity' => 10,
        ]);

        // 1. Tạo khách ban đầu với 2 suất dự kiến gán vào Bàn Thân Thiết
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Vũ Hải Đăng',
            'expected_attendees' => 2,
            'table_id' => $table1->id,
        ]);

        $table1->refresh();
        $this->assertEquals(2, $table1->assigned_attendees_count);
        $this->assertEquals(0, $table2->assigned_attendees_count);

        // 2. Khách nộp RSVP xác nhận tham dự 3 người (2 lớn, 1 trẻ em)
        $rsvpRes = $this->postJson(route('rsvp.submit', ['slug' => $this->invitation->slug, 'token' => $guest->guest_token]), [
            'guest_name' => 'Vũ Hải Đăng',
            'attending_status' => 'attending',
            'adults_count' => 2,
            'children_count' => 1,
        ]);
        $rsvpRes->assertStatus(200);

        // 3. Sĩ số bàn tiệc tự động nhảy lên 3 ghế
        $table1->refresh();
        $this->assertEquals(3, $table1->assigned_attendees_count);

        // 4. Lễ tân quét QR check-in
        $this->post(route('checkin.verifyPin', $this->invitation->slug), ['pin' => '654321']);
        $scanRes = $this->postJson(route('checkin.scan', $this->invitation->slug), [
            'token' => $guest->guest_token,
        ]);
        $scanRes->assertStatus(200);
        $guest->refresh();
        $this->assertEquals(3, $guest->checked_in_attendees);

        // 5. Lễ tân chuyển bàn cho khách sang "Bàn Danh Dự"
        $changeTableRes = $this->postJson(route('checkin.updateTable', ['slug' => $this->invitation->slug, 'guestId' => $guest->id]), [
            'table_name' => 'Bàn Danh Dự',
        ]);
        $changeTableRes->assertStatus(200);

        $guest->refresh();
        $this->assertEquals('Bàn Danh Dự', $guest->table_name);
        $this->assertEquals($table2->id, $guest->table_id);

        $table1->refresh();
        $table2->refresh();
        $this->assertEquals(0, $table1->assigned_attendees_count);
        $this->assertEquals(3, $table2->assigned_attendees_count);
    }
}
