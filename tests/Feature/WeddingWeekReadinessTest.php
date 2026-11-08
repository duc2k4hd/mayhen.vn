<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;
use App\Services\WeddingWeekReadinessService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingWeekReadinessTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Wedding $wedding;

    private Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->wedding = Wedding::create([
            'user_id' => $this->user->id,
            'groom_name' => 'Hoàng Nam',
            'bride_name' => 'Khánh Linh',
            'wedding_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Celestial Gold',
            'slug' => 'celestial-gold',
            'blade_view' => 'client.invitations.celestial-gold.template',
            'thumbnail_url' => '/assets/client/images/templates/celestial-gold.jpg',
            'is_active' => true,
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'slug' => 'hoang-nam-khanh-linh-week',
            'is_published' => true,
            'custom_config' => [
                'checkin_pin' => '2468',
            ],
        ]);
    }

    public function test_owner_can_view_wedding_week_readiness_radar(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('invitations.wedding_week', $this->invitation->id));

        $response->assertOk()
            ->assertViewIs('client.pages.account.wedding-week-readiness')
            ->assertSee('Bảng Kiểm Tra Sẵn Sàng Ngày Cưới')
            ->assertSee('Hoàng Nam &amp; Khánh Linh', false)
            ->assertSee('TUẦN LỄ CƯỚI TRỌNG ĐẠI')
            ->assertSee('Trung Tâm Phản Ứng Nhanh Mây Hẹn');
    }

    public function test_unauthorized_user_cannot_access_other_users_readiness_radar(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('invitations.wedding_week', $this->invitation->id));

        $response->assertNotFound();
    }

    public function test_service_accurately_calculates_wedding_week_status_and_pillars(): void
    {
        // Thêm bàn tiệc và khách mời
        WeddingTable::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bàn VIP 01',
            'capacity' => 10,
        ]);

        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Trần Văn An',
            'expected_attendees' => 2,
        ]);

        $service = app(WeddingWeekReadinessService::class);
        $radar = $service->assessReadiness($this->invitation);

        $this->assertTrue($radar['is_wedding_week']);
        $this->assertNotNull($radar['days_until_wedding']);
        $this->assertGreaterThanOrEqual(70, $radar['readiness_score']);
        $this->assertCount(5, $radar['pillars']);
        $this->assertStringContainsString('15 phút', $radar['emergency_support']['commitment']);
    }

    public function test_guest_without_login_is_redirected_to_login(): void
    {
        $response = $this->get(route('invitations.wedding_week', $this->invitation->id));

        $response->assertRedirect(route('login'));
    }
}
