<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientOnboardingWizardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private InvitationTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Nguyễn Minh Trí',
            'email' => 'minhtri@example.com',
        ]);

        $this->template = InvitationTemplate::create([
            'name' => 'Celestial Gold Luxury',
            'slug' => 'celestial-gold-luxury',
            'blade_view' => 'client.invitations.celestial.template',
            'thumbnail_url' => '/assets/templates/celestial.webp',
            'category' => 'Royal Classic',
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_onboarding(): void
    {
        $response = $this->get(route('onboarding.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_onboarding_wizard(): void
    {
        $response = $this->actingAs($this->user)->get(route('onboarding.index'));

        $response->assertOk();
        $response->assertSee('Khởi Tạo Thiệp Cưới Của Bạn');
        $response->assertSee('Nguyễn Minh Trí');
        $response->assertSee('Cho Đám Cưới Của Chính Mình');
        $response->assertSee('Celestial Gold Luxury');
    }

    public function test_user_can_save_step_1_intent(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('onboarding.save_step'), [
            'step' => 1,
            'role_intent' => 'couple',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'next_step' => 2,
        ]);

        $this->assertEquals('couple', session('onboarding_draft.role_intent'));
        $this->assertEquals(2, session('onboarding_draft.step'));
    }

    public function test_user_can_save_step_2_template(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('onboarding.save_step'), [
            'step' => 2,
            'template_id' => $this->template->id,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'next_step' => 3,
        ]);

        $this->assertEquals($this->template->id, session('onboarding_draft.template_id'));
    }

    public function test_user_can_save_step_3_couple_info(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('onboarding.save_step'), [
            'step' => 3,
            'groom_name' => 'Minh Trí',
            'bride_name' => 'Thảo My',
            'wedding_date' => '2026-12-15',
            'venue_name' => 'White Palace Convention',
            'venue_address' => '194 Hoàng Văn Thụ, Q. Phú Nhuận',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'next_step' => 4,
        ]);

        $this->assertEquals('Minh Trí', session('onboarding_draft.groom_name'));
        $this->assertEquals('Thảo My', session('onboarding_draft.bride_name'));
        $this->assertEquals('2026-12-15', session('onboarding_draft.wedding_date'));
    }

    public function test_save_step_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('onboarding.save_step'), [
            'step' => 3,
            'groom_name' => '', // Thiếu tên chú rể
            'bride_name' => '',
            'wedding_date' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['groom_name', 'bride_name', 'wedding_date']);
    }

    public function test_user_can_complete_onboarding_and_create_invitation(): void
    {
        $response = $this->actingAs($this->user)->post(route('onboarding.complete'), [
            'role_intent' => 'couple',
            'template_id' => $this->template->id,
            'groom_name' => 'Thành Nam',
            'bride_name' => 'Khánh Huyền',
            'wedding_date' => '2026-11-28',
            'venue_name' => 'The Adora Center',
            'venue_address' => '431 Hoàng Văn Thụ, TP.HCM',
        ]);

        // Kiểm tra Wedding được tạo
        $wedding = Wedding::where('user_id', $this->user->id)->first();
        $this->assertNotNull($wedding);
        $this->assertEquals('Thành Nam', $wedding->groom_name);
        $this->assertEquals('Khánh Huyền', $wedding->bride_name);
        $this->assertEquals('The Adora Center', $wedding->venue_name);

        // Kiểm tra Invitation được tạo
        $invitation = Invitation::where('wedding_id', $wedding->id)->first();
        $this->assertNotNull($invitation);
        $this->assertEquals($this->template->id, $invitation->template_id);

        // Redirect đến Studio Editor
        $response->assertRedirect(route('invitation.create', ['edit' => $invitation->slug]));
        $response->assertSessionHas('success');

        // Session draft phải được xóa sạch
        $this->assertNull(session('onboarding_draft'));

        // Kiểm tra Audit Log
        $logs = AuditLogger::getRecentLogs(10, 'ONBOARDING_COMPLETED');
        $this->assertNotEmpty($logs);
        $this->assertEquals('invitation', $logs[0]['target_type']);
        $this->assertEquals((string) $invitation->id, $logs[0]['target_id']);
    }

    public function test_new_registered_user_is_redirected_to_onboarding(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Lê Thanh Tùng',
            'email' => 'tung.le@example.com',
            'phone' => '0912345678',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $response->assertRedirect(route('onboarding.index'));
        $this->assertAuthenticated();
    }

    public function test_user_can_reset_onboarding_draft(): void
    {
        session(['onboarding_draft' => ['step' => 3, 'groom_name' => 'Tạm thời']]);

        $response = $this->actingAs($this->user)->post(route('onboarding.reset'));

        $response->assertRedirect(route('onboarding.index'));
        $this->assertNull(session('onboarding_draft'));
    }
}
