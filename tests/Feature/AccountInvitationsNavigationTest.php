<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountInvitationsNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Wedding $wedding;

    private Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'client',
        ]);

        $this->wedding = Wedding::create([
            'user_id' => $this->user->id,
            'groom_name' => 'Gia Huy',
            'bride_name' => 'Thanh Thảo',
            'wedding_date' => now()->addDays(30)->format('Y-m-d'),
            'venue_name' => 'White Palace',
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Giữa Muôn Ngàn Vì Sao',
            'slug' => 'giua-muon-ngan-vi-sao',
            'thumbnail_url' => '/assets/thumb.jpg',
            'version' => '1.0.0',
            'is_active' => true,
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'slug' => 'dam-cuoi-gia-huy-thanh-thao',
            'is_published' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('account.invitations'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_all_feature_action_links(): void
    {
        $response = $this->actingAs($this->user)->get(route('account.invitations'));

        $response->assertStatus(200);
        $response->assertSee('Gia Huy');
        $response->assertSee('Thanh Thảo');

        // 1. Studio Tạo Thiệp
        $response->assertSee(route('invitation.create', ['edit' => $this->invitation->slug]));

        // 2. Quản Lý Khách Mời & RSVP
        $response->assertSee(route('guests.index', $this->invitation->id));

        // 3. Sổ Lưu Bút
        $response->assertSee(route('account.wishes.index', $this->invitation->id));

        // 4. Sổ Mừng Cưới & VietQR
        $response->assertSee(route('account.gifts.index', $this->invitation->id));

        // 5. Ảnh Khách Chụp (Live Guest Photo Drop - TASK-V2-05)
        $response->assertSee(route('photo_drop.manage', $this->invitation->id));

        // 6. Điều Phối Sự Kiện Realtime (Live Command Center - TASK-V2-02)
        $response->assertSee(route('coordination.index', $this->invitation->id));

        // 7. Két Lưu Trữ Kỷ Niệm Số (Digital Memory Vault - TASK-V2-03)
        $response->assertSee(route('memory.settings', $this->invitation->id));

        // 8. Sơ Đồ Xếp Bàn Tiệc (Seating Planner Studio - TASK-V3-02)
        $response->assertSee(route('seating.index', $this->invitation->id));

        // 9. Màn Chiếu Sân Khấu Realtime (Live Wedding Projection Wall - TASK-V3-04)
        $response->assertSee(route('live_wall.settings', $this->invitation->id));

        // 10. Hộp Thời Gian Kỷ Niệm (Wedding Time Capsule - TASK-V3-05)
        $response->assertSee(route('account.time_capsule.index', $this->invitation->id));

        // 11. Tên Miền Riêng & Subdomain (TASK-V3-03)
        $response->assertSee(route('account.invitations.domain', $this->invitation->id));

        // 12. Cài Đặt Chi Tiết Thiệp Cưới
        $response->assertSee(route('account.invitations.settings', $this->invitation->id));

        // 13. Xem Thiệp Trực Tuyến
        $response->assertSee(route('invitation.show', $this->invitation->slug));
    }

    public function test_locked_invitation_displays_locked_badge_and_disables_toggle(): void
    {
        $this->invitation->update([
            'custom_config' => [
                'is_locked' => true,
                'locked_reason' => 'Vi phạm chính sách nội dung',
            ],
        ]);

        $response = $this->actingAs($this->user)->get(route('account.invitations'));

        $response->assertStatus(200);
        $response->assertSee('Bị Khóa');
        $response->assertSee('Tạm Khóa Bởi Quản Trị');
    }
}
