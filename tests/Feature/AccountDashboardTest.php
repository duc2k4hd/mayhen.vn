<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'client',
            'name' => 'Hoàng Phúc',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('account.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_invitations_sees_empty_state(): void
    {
        $response = $this->actingAs($this->user)->get(route('account.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Hoàng Phúc');
        $response->assertSee('Bạn Chưa Tạo Mẫu Thiệp Cưới Nào');
        $response->assertSee('Khám Phá Kho Mẫu Thiệp');
    }

    public function test_authenticated_user_with_active_invitation_sees_kpis_and_6_quick_actions(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->user->id,
            'groom_name' => 'Hoàng Phúc',
            'bride_name' => 'Bảo Trân',
            'wedding_date' => now()->addDays(20)->format('Y-m-d'),
            'venue_name' => 'Reverie Saigon',
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Giữa Muôn Ngàn Vì Sao',
            'slug' => 'giua-muon-ngan-vi-sao',
            'thumbnail_url' => '/assets/thumb.jpg',
            'version' => '1.0.0',
            'is_active' => true,
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'dam-cuoi-hoang-phuc-bao-tran',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('account.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Hoàng Phúc');
        $response->assertSee('Bảo Trân');
        $response->assertSee('Còn 20 ngày nữa');

        // 6 Quick Action Cards
        $response->assertSee(route('guests.index', $invitation->id));
        $response->assertSee(route('pricing'));
        $response->assertSee(route('account.orders'));
        $response->assertSee(route('seating.index', $invitation->id));
        $response->assertSee(route('live_wall.settings', $invitation->id));
        $response->assertSee(route('memory.settings', $invitation->id));
    }
}
