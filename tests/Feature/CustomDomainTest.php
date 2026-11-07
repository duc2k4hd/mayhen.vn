<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Partner;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomDomainTest extends TestCase
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
            'groom_name' => 'Tuấn Anh',
            'bride_name' => 'Mai Lan',
            'wedding_date' => now()->addMonths(2)->format('Y-m-d'),
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
            'slug' => 'dam-cuoi-tuan-anh-mai-lan',
            'is_published' => true,
        ]);
    }

    public function test_owner_can_view_custom_domain_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('account.invitations.domain', $this->invitation->id));

        $response->assertStatus(200);
        $response->assertSee('Tuấn Anh');
        $response->assertSee('Mai Lan');
        $response->assertSee('Tên Miền Phụ Mây Hẹn (Vanity Subdomain)');
        $response->assertSee('Tên Miền Riêng Độc Bản (Custom Domain)');
    }

    public function test_unauthenticated_user_cannot_view_custom_domain_settings(): void
    {
        $response = $this->get(route('account.invitations.domain', $this->invitation->id));

        $response->assertRedirect(route('login'));
    }

    public function test_stranger_cannot_view_other_users_domain_settings(): void
    {
        $stranger = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($stranger)
            ->get(route('account.invitations.domain', $this->invitation->id));

        $response->assertStatus(404);
    }

    public function test_partner_can_manage_domain_for_their_wedding(): void
    {
        $partnerUser = User::factory()->create(['role' => 'partner']);
        $partner = Partner::create([
            'user_id' => $partnerUser->id,
            'business_name' => 'Élysee Studio',
            'slug' => 'elysee-studio',
            'partner_code' => 'MH-PARTNER-ELYSEE',
            'phone' => '0901234567',
            'email' => 'elysee@example.com',
            'status' => 'active',
        ]);

        $this->wedding->update([
            'partner_id' => $partner->id,
        ]);

        $response = $this->actingAs($partnerUser)
            ->get(route('account.invitations.domain', $this->invitation->id));

        $response->assertStatus(200);
    }

    public function test_owner_can_register_and_update_vanity_subdomain(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('account.invitations.domain.subdomain', $this->invitation->id), [
                'subdomain' => 'tuan-lan',
            ]);

        $response->assertRedirect(route('account.invitations.domain', $this->invitation->id));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('wedding_domains', [
            'invitation_id' => $this->invitation->id,
            'wedding_id' => $this->wedding->id,
            'subdomain' => 'tuan-lan',
            'status' => 'active',
            'ssl_status' => 'active',
        ]);
    }

    public function test_subdomain_must_not_be_blacklisted(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('account.invitations.domain.subdomain', $this->invitation->id), [
                'subdomain' => 'admin',
            ]);

        $response->assertSessionHasErrors('subdomain');
        $this->assertDatabaseMissing('wedding_domains', [
            'subdomain' => 'admin',
        ]);
    }

    public function test_subdomain_must_be_unique(): void
    {
        // Tạo một subdomain đã tồn tại của người khác
        $otherWedding = Wedding::create([
            'user_id' => User::factory()->create()->id,
            'groom_name' => 'Chú rể',
            'bride_name' => 'Cô dâu',
            'wedding_date' => now()->addMonth()->format('Y-m-d'),
        ]);

        WeddingDomain::create([
            'wedding_id' => $otherWedding->id,
            'invitation_id' => Invitation::create([
                'wedding_id' => $otherWedding->id,
                'template_id' => $this->invitation->template_id,
                'slug' => 'thiep-khac',
            ])->id,
            'subdomain' => 'tuanlan',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('account.invitations.domain.subdomain', $this->invitation->id), [
                'subdomain' => 'tuanlan',
            ]);

        $response->assertSessionHasErrors('subdomain');
    }

    public function test_subdomain_format_validation(): void
    {
        // Quá ngắn
        $response = $this->actingAs($this->user)
            ->post(route('account.invitations.domain.subdomain', $this->invitation->id), [
                'subdomain' => 'tl',
            ]);
        $response->assertSessionHasErrors('subdomain');

        // Bắt đầu bằng dấu gạch ngang
        $response2 = $this->actingAs($this->user)
            ->post(route('account.invitations.domain.subdomain', $this->invitation->id), [
                'subdomain' => '-tuanlan',
            ]);
        $response2->assertSessionHasErrors('subdomain');
    }

    public function test_owner_can_register_custom_domain(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('account.invitations.domain.custom', $this->invitation->id), [
                'custom_domain' => 'damcuoituanlan.vn',
            ]);

        $response->assertRedirect(route('account.invitations.domain', $this->invitation->id));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('wedding_domains', [
            'invitation_id' => $this->invitation->id,
            'wedding_id' => $this->wedding->id,
            'custom_domain' => 'damcuoituanlan.vn',
            'status' => 'pending',
        ]);
    }

    public function test_custom_domain_cannot_be_system_domain(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('account.invitations.domain.custom', $this->invitation->id), [
                'custom_domain' => 'mayhen.vn',
            ]);

        $response->assertSessionHasErrors('custom_domain');
    }

    public function test_dns_verification_success_activates_custom_domain(): void
    {
        $domain = WeddingDomain::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'custom_domain' => 'damcuoituanlan.vn',
            'status' => 'pending',
            'cname_target' => 'cname.mayhen.vn',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('account.invitations.domain.verify', $this->invitation->id));

        $response->assertRedirect(route('account.invitations.domain', $this->invitation->id));
        $response->assertSessionHas('status');

        $domain->refresh();
        $this->assertTrue($domain->isActive());
        $this->assertNotNull($domain->dns_verified_at);
    }

    public function test_dns_verification_failure_marks_domain_failed(): void
    {
        $domain = WeddingDomain::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'custom_domain' => 'fail-dns-test.wedding',
            'status' => 'pending',
            'cname_target' => 'cname.mayhen.vn',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('account.invitations.domain.verify', $this->invitation->id));

        $response->assertRedirect(route('account.invitations.domain', $this->invitation->id));
        $response->assertSessionHas('error');

        $domain->refresh();
        $this->assertTrue($domain->isFailed());
    }

    public function test_owner_can_remove_subdomain_or_custom_domain(): void
    {
        $domain = WeddingDomain::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'subdomain' => 'tuanlan',
            'custom_domain' => 'tuanlan.wedding',
            'status' => 'active',
        ]);

        // Gỡ riêng subdomain
        $response = $this->actingAs($this->user)
            ->delete(route('account.invitations.domain.destroy', $this->invitation->id), [
                'type' => 'subdomain',
            ]);

        $response->assertRedirect(route('account.invitations.domain', $this->invitation->id));
        $domain->refresh();
        $this->assertNull($domain->subdomain);
        $this->assertEquals('tuanlan.wedding', $domain->custom_domain);

        // Gỡ riêng custom domain
        $response = $this->actingAs($this->user)
            ->delete(route('account.invitations.domain.destroy', $this->invitation->id), [
                'type' => 'custom_domain',
            ]);

        $domain->refresh();
        $this->assertNull($domain->custom_domain);
    }

    public function test_post_method_on_base_domain_route_is_supported_and_does_not_throw_405(): void
    {
        WeddingDomain::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'subdomain' => 'tuanlan',
            'status' => 'active',
        ]);

        // Gửi POST trực tiếp tới /tai-khoan/thiep/{id}/ten-mien với type=subdomain
        $response = $this->actingAs($this->user)
            ->post(route('account.invitations.domain.destroy', $this->invitation->id), [
                'type' => 'subdomain',
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('account.invitations.domain', $this->invitation->id));
        $this->assertNull($this->wedding->fresh()->domain->subdomain);
    }

    public function test_detect_custom_domain_middleware_serves_invitation_on_subdomain(): void
    {
        WeddingDomain::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'subdomain' => 'tuanlan',
            'status' => 'active',
        ]);

        // Mô phỏng request tới subdomain: tuanlan.mayhen.vn
        $response = $this->get('http://tuanlan.mayhen.vn/');

        $response->assertStatus(200);
        $response->assertSee('Tuấn Anh');
        $response->assertSee('Mai Lan');
    }

    public function test_detect_custom_domain_middleware_serves_invitation_on_custom_domain(): void
    {
        WeddingDomain::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'custom_domain' => 'damcuoituanlan.wedding',
            'status' => 'active',
        ]);

        // Mô phỏng request tới custom domain: damcuoituanlan.wedding
        $response = $this->get('http://damcuoituanlan.wedding/');

        $response->assertStatus(200);
        $response->assertSee('Tuấn Anh');
        $response->assertSee('Mai Lan');
    }

    public function test_unregistered_custom_domain_shows_pending_page(): void
    {
        // Request tới custom domain chưa từng được đăng ký trong hệ thống
        $response = $this->get('http://unregistered-domain.wedding/');

        $response->assertStatus(404);
        $response->assertSee('Tên Miền Đang Chờ Kích Hoạt');
    }
}
