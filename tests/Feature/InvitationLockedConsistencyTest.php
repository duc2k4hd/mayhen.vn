<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvitationLockedConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Wedding $wedding;

    private Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->user = User::factory()->create();

        $this->wedding = Wedding::create([
            'user_id' => $this->user->id,
            'groom_name' => 'Quốc Huy',
            'bride_name' => 'Thanh Thảo',
            'wedding_date' => '2026-11-20',
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Celestial Gold',
            'slug' => 'celestial-gold',
            'blade_view' => 'client.invitations.celestial-gold.template',
            'thumbnail_url' => '/assets/client/images/templates/celestial-gold.jpg',
            'is_active' => true,
        ]);

        // Tạo thiệp đang bị Quản trị viên khóa
        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'slug' => 'quoc-huy-thanh-thao-locked',
            'is_published' => true,
            'custom_config' => [
                'is_locked' => true,
                'lock_reason' => 'Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.',
                'checkin_pin' => '123456',
            ],
        ]);
    }

    public function test_locked_invitation_blocks_memory_vault_public_page(): void
    {
        $response = $this->get(route('memory.vault', $this->invitation->slug));

        $response->assertOk()
            ->assertViewIs('client.pages.invitation-locked')
            ->assertSee('Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.');
    }

    public function test_locked_invitation_blocks_live_projection_wall_and_feed(): void
    {
        // 1. Màn hình HTML
        $response = $this->get(route('live_wall.show', $this->invitation->slug));

        $response->assertOk()
            ->assertViewIs('client.pages.invitation-locked')
            ->assertSee('Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.');

        // 2. API Live Feed
        $apiResponse = $this->getJson(route('live_wall.feed', $this->invitation->slug));

        $apiResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ]);
    }

    public function test_locked_invitation_blocks_photo_drop_portal_and_upload(): void
    {
        // 1. Màn hình HTML Photo Drop
        $response = $this->get(route('photo_drop.show', $this->invitation->slug));

        $response->assertOk()
            ->assertViewIs('client.pages.invitation-locked')
            ->assertSee('Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.');

        // 2. API Upload ảnh
        $file = UploadedFile::fake()->image('test_photo.jpg');

        $uploadResponse = $this->postJson(route('photo_drop.upload', $this->invitation->slug), [
            'uploader_name' => 'Bạn Cùng Bàn',
            'photos' => [$file],
        ]);

        $uploadResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ]);
    }

    public function test_locked_invitation_blocks_checkin_portal_and_scan(): void
    {
        // 1. Màn hình HTML Cổng Điểm Danh
        $response = $this->get(route('checkin.portal', $this->invitation->slug));

        $response->assertOk()
            ->assertViewIs('client.pages.invitation-locked')
            ->assertSee('Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.');

        // 2. API Quét QR Điểm Danh
        $scanResponse = $this->postJson(route('checkin.scan', $this->invitation->slug), [
            'token' => 'sample_token_123',
        ]);

        $scanResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ]);
    }

    public function test_locked_invitation_blocks_guest_concierge_page_and_ics_download(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Nguyễn Văn Nam',
            'expected_attendees' => 2,
        ]);

        // 1. Màn hình Sổ tay khách mời Concierge
        $response = $this->get(route('guest.concierge', ['guestToken' => $guest->guest_token]));

        $response->assertOk()
            ->assertViewIs('client.pages.invitation-locked')
            ->assertSee('Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.');

        // 2. Tải file lịch ICS
        $icsResponse = $this->get(route('guest.concierge.ics', ['guestToken' => $guest->guest_token]));

        $icsResponse->assertStatus(403);
    }

    public function test_locked_invitation_blocks_public_show_and_wishes_and_rsvp(): void
    {
        // 1. Mở trang thiệp công khai
        $response = $this->get(route('invitation.show', $this->invitation->slug));
        $response->assertOk()
            ->assertViewIs('client.pages.invitation-locked')
            ->assertSee('Thiệp cưới tạm thời bị khóa');

        // 2. Gửi lời chúc
        $wishResponse = $this->postJson(route('invitation.wish.store', $this->invitation->slug), [
            'name' => 'Nguyễn Hoàng Nam',
            'message' => 'Chúc mừng hạnh phúc hai bạn!',
        ]);
        $wishResponse->assertStatus(403);

        // 3. Gửi RSVP
        $rsvpResponse = $this->postJson(route('rsvp.submit', $this->invitation->slug), [
            'guest_name' => 'Nguyễn Hoàng Nam',
            'attending_status' => 'attending',
            'adults_count' => 2,
        ]);
        $rsvpResponse->assertStatus(403);
    }

    public function test_owner_cannot_bypass_lock_via_studio_publish_or_autosave(): void
    {
        // Chủ tiệc đăng nhập
        $this->actingAs($this->user);

        // 1. Cố tình gửi cập nhật xuất bản qua /tao-thiep
        $publishResponse = $this->post(route('invitation.store'), [
            'template_id' => $this->invitation->template_id,
            'invitation_id' => $this->invitation->id,
            'revision_id' => 0,
            'wedding' => [
                'groom_name' => 'Quốc Huy Đổi Tên',
                'bride_name' => 'Thanh Thảo',
            ],
        ]);
        $publishResponse->assertStatus(403);

        // 2. Cố tình gửi autosave
        $autosaveResponse = $this->postJson(route('invitation.autosave'), [
            'invitation_id' => $this->invitation->id,
            'revision_id' => 0,
            'draft_config' => ['color' => '#ffffff'],
        ]);
        $autosaveResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'locked' => true,
            ]);

        // 3. Cố tình gọi publish-impact
        $impactResponse = $this->postJson(route('invitation.publish_impact', $this->invitation->id), [
            'wedding' => ['venue_name' => 'Khách sạn mới'],
        ]);
        $impactResponse->assertStatus(403);
    }

    public function test_locked_invitation_blocks_all_reception_actions(): void
    {
        // Tiếp tân có session nhưng thiệp bị admin khóa
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Trần Văn Minh',
            'expected_attendees' => 1,
        ]);

        session(["receptionist_verified_{$this->invitation->id}" => true]);

        // 1. Tìm nhanh
        $searchResponse = $this->getJson(route('checkin.quickSearch', [
            'slug' => $this->invitation->slug,
            'q' => 'Trần',
        ]));
        $searchResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ]);

        // 2. Tiếp nhận khách phát sinh (walk-in)
        $walkInResponse = $this->postJson(route('checkin.exception.walk_in', $this->invitation->slug), [
            'name' => 'Khách Đột Xuất',
            'attendees' => 2,
        ]);
        $walkInResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ]);

        // 3. Đổi bàn
        $tableResponse = $this->postJson(route('checkin.updateTable', [
            'slug' => $this->invitation->slug,
            'guestId' => $guest->id,
        ]), [
            'table_name' => 'Bàn VIP 1',
        ]);
        $tableResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ]);
    }

    public function test_locked_invitation_blocks_public_time_capsule_and_guest_submission(): void
    {
        // 1. Màn hình công khai Hộp Thời Gian
        $response = $this->get(route('time_capsule.public_slug', $this->invitation->slug));
        $response->assertOk()
            ->assertViewIs('client.pages.invitation-locked')
            ->assertSee('Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.');

        // 2. Khách gửi tâm thư gửi tương lai
        $storeResponse = $this->post(route('time_capsule.guest.store', $this->invitation->slug), [
            'sender_name' => 'Người Bạn Thân',
            'recipient_name' => 'Quốc Huy & Thanh Thảo',
            'title' => 'Tâm thư 5 năm sau',
            'content' => 'Chúc hai bạn mãi hạnh phúc như ngày đầu nhé!',
            'milestone_years' => 5,
            'seal_color' => 'gold',
        ]);
        $storeResponse->assertStatus(403);
    }

    public function test_time_capsule_timezone_and_leap_year_handling(): void
    {
        // Ngày cưới vào năm nhuận: 2028-02-29
        $weddingLeap = Wedding::create([
            'user_id' => $this->user->id,
            'groom_name' => 'Đức Thịnh',
            'bride_name' => 'Mỹ Linh',
            'wedding_date' => '2028-02-29',
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $weddingLeap->id,
            'template_id' => $this->invitation->template_id,
            'slug' => 'duc-thinh-my-linh-capsule',
            'is_published' => true,
        ]);

        // 1. Khách gửi tâm thư cho dịp kỷ niệm 1 năm (2029 - không phải năm nhuận)
        $this->post(route('time_capsule.guest.store', $invitation->slug), [
            'sender_name' => 'Phù Dâu',
            'recipient_name' => 'Mỹ Linh',
            'title' => 'Gửi cô bạn thân 1 năm sau',
            'content' => 'Bí mật của chúng mình!',
            'milestone_years' => 1,
            'seal_color' => 'ruby',
        ]);

        $capsule = $weddingLeap->timeCapsules()->first();
        $this->assertNotNull($capsule);

        // Bảo vệ năm nhuận: 2028-02-29 + 1 năm không tràn sang tháng 3 mà ở ngày cuối tháng 2 (2029-02-28)
        $this->assertEquals('2029-02-28', $capsule->unlock_date->format('Y-m-d'));
        $this->assertTrue($capsule->is_sealed);
        $this->assertFalse($capsule->canBeUnlocked());

        // Kiểm tra an toàn: Người ngoài không xem được nội dung thư đã niêm phong sáp
        $publicViewResponse = $this->get(route('time_capsule.public_slug', $invitation->slug));
        $publicViewResponse->assertOk()
            ->assertDontSee('Bí mật của chúng mình!');

        // Chủ tiệc mở sớm trước ngày mở khóa sẽ bị chặn 422
        $this->actingAs($this->user);
        $unsealEarly = $this->post(route('account.time_capsule.unseal', [$invitation->id, $capsule->id]));
        $unsealEarly->assertSessionHas('error');
        $capsule->refresh();
        $this->assertTrue($capsule->is_sealed);
    }
}
