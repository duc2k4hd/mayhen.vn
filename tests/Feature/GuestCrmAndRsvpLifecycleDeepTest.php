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

class GuestCrmAndRsvpLifecycleDeepTest extends TestCase
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
            'name' => 'Ngày Tình Yêu Đơm Hoa',
            'slug' => 'ngay-tinh-yeu-dom-hoa',
            'thumbnail_url' => '/assets/templates/dom-hoa.webp',
            'category' => 'romantic',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Tuấn Kiệt',
            'email' => 'tuankiet@example.com',
        ]);

        $this->stranger = User::factory()->create([
            'name' => 'Người Dùng Khác',
            'email' => 'stranger@example.com',
        ]);

        $this->wedding = Wedding::create([
            'user_id' => $this->owner->id,
            'groom_name' => 'Tuấn Kiệt',
            'bride_name' => 'Hải Yến',
            'wedding_date' => Carbon::now()->addMonths(1),
        ]);

        $this->invitation = Invitation::create([
            'user_id' => $this->owner->id,
            'wedding_id' => $this->wedding->id,
            'template_id' => $this->template->id,
            'slug' => 'tuan-kiet-hai-yen',
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(2),
        ]);
    }

    /**
     * TEST 1: Batch import khách mời với xử lý khoảng trắng, lọc từ ngữ thô tục, và gán bàn tiệc.
     */
    public function test_batch_import_guests_handles_whitespace_profanity_filtering_and_table_linking(): void
    {
        $tableVip = WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn VIP 01',
            'capacity' => 10,
        ]);

        $batchInput = "   \n".
            "Anh Hùng & Chị Mai | Thân mời | Bạn Cấp 3 | 2 | 0901112233 | Bàn VIP 01   \n".
            "Đồ khốn nạn chó chết | Thân mời | Khách Cấm | 1 | 0988000000 | \n".
            "Bác Năm Sài Gòn | Kính mời | Họ Hàng | 3 | 0912345678 | \n".
            'Bạn Lan Hương   ';

        $response = $this->actingAs($this->owner)
            ->post(route('guests.batch_store', $this->invitation->id), [
                'batch_input' => $batchInput,
                'default_salutation' => 'Thân mời',
                'default_group' => 'Đồng Nghiệp',
            ]);

        $response->assertRedirect(route('guests.index', $this->invitation->id));
        $response->assertSessionHas('success');

        // Khách hợp lệ 1: Có bàn VIP 01 và tự link table_id
        $guest1 = Guest::where('name', 'Anh Hùng & Chị Mai')->first();
        $this->assertNotNull($guest1);
        $this->assertEquals('Bàn VIP 01', $guest1->table_name);
        $this->assertEquals($tableVip->id, $guest1->table_id);
        $this->assertEquals(2, $guest1->expected_attendees);

        // Khách 2: Chứa từ thô tục bị bỏ qua, không được tạo
        $this->assertNull(Guest::where('name', 'like', '%khốn nạn%')->first());

        // Khách 3: Phân tách đầy đủ họ hàng, 3 người
        $guest3 = Guest::where('name', 'Bác Năm Sài Gòn')->first();
        $this->assertNotNull($guest3);
        $this->assertEquals('Họ Hàng', $guest3->group_name);
        $this->assertEquals('Kính mời', $guest3->salutation);
        $this->assertEquals(3, $guest3->expected_attendees);

        // Khách 4: Chỉ có tên -> tự fallback salutation và group mặc định
        $guest4 = Guest::where('name', 'Bạn Lan Hương')->first();
        $this->assertNotNull($guest4);
        $this->assertEquals('Đồng Nghiệp', $guest4->group_name);
        $this->assertEquals('Thân mời', $guest4->salutation);
        $this->assertNotNull($guest4->guest_token);
    }

    /**
     * TEST 2: Xóa khách mời tự động xóa bỏ GuestResponse liên kết, bảo vệ tính toàn vẹn số liệu RSVP.
     */
    public function test_deleting_guest_cascades_and_removes_orphaned_rsvp_response(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Ngô Bảo Châu',
            'phone' => '0933445566',
            'expected_attendees' => 2,
        ]);

        $response = GuestResponse::create([
            'wedding_id' => $this->wedding->id,
            'guest_id' => $guest->id,
            'guest_name' => 'Ngô Bảo Châu',
            'attending_status' => 'attending',
            'adults_count' => 2,
            'children_count' => 1,
        ]);

        $this->assertEquals(1, $this->wedding->guestResponses()->count());

        // Chủ thiệp xóa khách mời khỏi danh sách
        $delResponse = $this->actingAs($this->owner)
            ->delete(route('guests.destroy', $guest->id));

        $delResponse->assertRedirect();
        $this->assertDatabaseMissing('guests', ['id' => $guest->id]);

        // Invariant: Phản hồi RSVP của khách đã xóa KHÔNG được trở thành bản ghi mồ côi làm sai lệch thống kê
        $this->assertDatabaseMissing('guest_responses', ['id' => $response->id]);
        $this->assertEquals(0, $this->wedding->guestResponses()->count());
    }

    /**
     * TEST 3: Hai khách khác nhau cùng mạng NAT (chung 1 IP Public) đều có thể gửi RSVP thành công mà không bị khóa nhầm (QA-PRIV-009).
     */
    public function test_multiple_guests_on_same_nat_ip_can_both_submit_rsvp_successfully(): void
    {
        $sharedNatIp = '113.161.72.15';

        // 1. Khách A (Trần Long, số điện thoại 0901111111) gửi RSVP qua IP NAT
        $resA = $this->withServerVariables(['REMOTE_ADDR' => $sharedNatIp])
            ->postJson("/thiep/{$this->invitation->slug}/rsvp", [
                'guest_name' => 'Trần Long',
                'phone' => '0901111111',
                'attending_status' => 'attending',
                'adults_count' => 2,
                'wishes' => 'Chúc hai bạn hạnh phúc!',
            ]);
        $resA->assertOk();
        $resA->assertJson(['success' => true]);

        // 2. Khách B (Lê Thúy, số điện thoại 0902222222) cùng chung IP NAT gửi RSVP
        // Không được phép bị khóa 422 nhầm với khách A!
        $resB = $this->withServerVariables(['REMOTE_ADDR' => $sharedNatIp])
            ->postJson("/thiep/{$this->invitation->slug}/rsvp", [
                'guest_name' => 'Lê Thúy',
                'phone' => '0902222222',
                'attending_status' => 'attending',
                'adults_count' => 1,
                'wishes' => 'Chúc mừng ngày vui!',
            ]);
        $resB->assertOk();
        $resB->assertJson(['success' => true]);

        // Kiểm chứng cả 2 phản hồi đều được lưu độc lập
        $this->assertEquals(2, $this->wedding->guestResponses()->count());
    }

    /**
     * TEST 4: Khách cố tình gửi trùng RSVP (cùng số điện thoại hoặc token cá nhân) bị chặn với 422.
     */
    public function test_duplicate_rsvp_with_same_phone_is_blocked_with_422(): void
    {
        $ip = '118.69.182.20';

        // Lần 1: Thành công
        $res1 = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson("/thiep/{$this->invitation->slug}/rsvp", [
                'guest_name' => 'Phạm Minh',
                'phone' => '0903334444',
                'attending_status' => 'attending',
                'adults_count' => 1,
            ]);
        $res1->assertOk();

        // Lần 2: Cố tình gửi lại với cùng số điện thoại -> Bị từ chối 422
        $res2 = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson("/thiep/{$this->invitation->slug}/rsvp", [
                'guest_name' => 'Phạm Minh (Spam)',
                'phone' => '0903334444',
                'attending_status' => 'declined',
                'adults_count' => 0,
            ]);
        $res2->assertStatus(422);
        $res2->assertJson([
            'success' => false,
            'message' => 'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi và không thể thay đổi.',
        ]);

        $this->assertEquals(1, $this->wedding->guestResponses()->count());
    }

    /**
     * TEST 5: Cặp đôi cập nhật thủ công trạng thái RSVP (manualRsvp) và xóa phản hồi khi chuyển về pending.
     */
    public function test_host_can_manually_update_guest_rsvp_and_reset_to_pending(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Võ Trọng Nghĩa',
            'expected_attendees' => 2,
        ]);

        // Cập nhật thủ công sang attending với 2 người lớn, 1 trẻ em
        $response = $this->actingAs($this->owner)
            ->postJson(route('guests.manual_rsvp', $guest->id), [
                'attending_status' => 'attending',
                'adults_count' => 2,
                'children_count' => 1,
                'notes' => 'Gia đình đã gọi điện báo tham gia đầy đủ',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('guest_responses', [
            'guest_id' => $guest->id,
            'attending_status' => 'attending',
            'adults_count' => 2,
            'children_count' => 1,
        ]);

        // Cặp đôi chuyển trạng thái về pending (chưa rõ) -> phản hồi cũ tự động bị xóa
        $resetResponse = $this->actingAs($this->owner)
            ->postJson(route('guests.manual_rsvp', $guest->id), [
                'attending_status' => 'pending',
            ]);

        $resetResponse->assertOk();
        $this->assertDatabaseMissing('guest_responses', ['guest_id' => $guest->id]);
    }

    /**
     * TEST 6: Validation biên khi cập nhật thông tin khách mời (số người âm, vượt 50, tên rỗng).
     */
    public function test_guest_update_validations_and_boundary_checks(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Nguyễn Thu Trang',
            'expected_attendees' => 1,
        ]);

        // Tên rỗng -> 422
        $res1 = $this->actingAs($this->owner)
            ->putJson(route('guests.update', $guest->id), [
                'name' => '',
                'expected_attendees' => 1,
            ]);
        $res1->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // Số người âm (0) -> 422
        $res2 = $this->actingAs($this->owner)
            ->putJson(route('guests.update', $guest->id), [
                'name' => 'Nguyễn Thu Trang',
                'expected_attendees' => 0,
            ]);
        $res2->assertStatus(422)
            ->assertJsonValidationErrors(['expected_attendees']);

        // Số người quá lớn (> 50) -> 422
        $res3 = $this->actingAs($this->owner)
            ->putJson(route('guests.update', $guest->id), [
                'name' => 'Nguyễn Thu Trang',
                'expected_attendees' => 51,
            ]);
        $res3->assertStatus(422)
            ->assertJsonValidationErrors(['expected_attendees']);

        // Hợp lệ -> 200
        $res4 = $this->actingAs($this->owner)
            ->putJson(route('guests.update', $guest->id), [
                'name' => 'Nguyễn Thu Trang (Đã sửa)',
                'expected_attendees' => 4,
                'group_name' => 'VIP Doanh Nghiệp',
            ]);
        $res4->assertOk();
        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'name' => 'Nguyễn Thu Trang (Đã sửa)',
            'expected_attendees' => 4,
            'group_name' => 'VIP Doanh Nghiệp',
        ]);
    }

    /**
     * TEST 7: Người dùng lạ không thể xem, thêm, sửa hoặc xóa khách mời của thiệp cưới người khác (Tenant Isolation).
     */
    public function test_guest_crm_tenant_isolation_prevents_unauthorized_access(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Khách Thiệp A',
            'expected_attendees' => 1,
        ]);

        // Người lạ xem danh sách khách -> 404
        $res1 = $this->actingAs($this->stranger)
            ->get(route('guests.index', $this->invitation->id));
        $res1->assertNotFound();

        // Người lạ sửa khách -> 404
        $res2 = $this->actingAs($this->stranger)
            ->putJson(route('guests.update', $guest->id), [
                'name' => 'Hacked Name',
                'expected_attendees' => 1,
            ]);
        $res2->assertNotFound();

        // Người lạ xóa khách -> 404
        $res3 = $this->actingAs($this->stranger)
            ->delete(route('guests.destroy', $guest->id));
        $res3->assertNotFound();

        // Dữ liệu khách ban đầu vẫn còn nguyên
        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'name' => 'Khách Thiệp A',
        ]);
    }
}
