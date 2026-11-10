<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\InvitationView;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationAnalyticsAndFunnelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Wedding $wedding;

    protected Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'couple_analytics@mayhen.vn',
        ]);

        $this->wedding = Wedding::create([
            'user_id' => $this->user->id,
            'groom_name' => 'Nguyễn Minh Quân',
            'bride_name' => 'Lê Diệu Linh',
            'wedding_date' => now()->addDays(30),
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Celestial Elegance',
            'slug' => 'celestial-elegance',
            'thumbnail_url' => '/assets/templates/celestial.webp',
            'is_active' => true,
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'slug' => 'quan-linh-wedding-analytics',
            'title' => 'Thiệp Cưới Minh Quân & Diệu Linh',
            'is_published' => true,
            'view_count' => 0,
            'custom_config' => [],
        ]);
    }

    public function test_guests_cannot_view_analytics_dashboard_without_login(): void
    {
        $response = $this->get(route('account.invitations.analytics', $this->invitation->id));

        $response->assertRedirect(route('login'));
    }

    public function test_strangers_cannot_view_another_couples_invitation_analytics_anti_idor(): void
    {
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)->get(route('account.invitations.analytics', $this->invitation->id));

        $response->assertStatus(404);
    }

    public function test_owner_can_view_analytics_dashboard_with_funnel_and_kpis(): void
    {
        $response = $this->actingAs($this->user)->get(route('account.invitations.analytics', $this->invitation->id));

        $response->assertOk();
        $response->assertSee('Báo Cáo Phân Tích');
        $response->assertSee('Biểu Đồ Lượng Truy Cập 7 Ngày Gần Nhất');
        $response->assertSee('Phễu Chuyển Đổi Quan Khách');
        $response->assertSee('Thiết Bị Truy Cập');
        $response->assertSee('Ứng Dụng Mở Thiệp');
        $response->assertSee('Luật 91/2025/QH15');
    }

    public function test_public_visit_automatically_increments_view_count_and_creates_anonymous_view_record(): void
    {
        $this->assertEquals(0, $this->invitation->fresh()->view_count);
        $this->assertEquals(0, InvitationView::count());

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '113.190.234.56',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        ])->get(route('invitation.show', $this->invitation->slug));

        $response->assertOk();

        // Kiểm tra biến đếm atomic increment
        $this->assertEquals(1, $this->invitation->fresh()->view_count);

        // Kiểm tra bản ghi view ẩn danh
        $view = InvitationView::first();
        $this->assertNotNull($view);
        $this->assertEquals($this->invitation->id, $view->invitation_id);
        $this->assertEquals('mobile', $view->device_type);
        $this->assertEquals('ios', $view->platform);
        $this->assertEquals('safari', $view->browser);
        $this->assertEquals(64, strlen($view->visitor_hash)); // SHA-256 hash an toàn
    }

    public function test_owner_visit_does_not_increment_view_count_or_record_analytics(): void
    {
        $this->assertEquals(0, $this->invitation->fresh()->view_count);

        $response = $this->actingAs($this->user)->get(route('invitation.show', $this->invitation->slug));

        $response->assertOk();
        $this->assertEquals(0, $this->invitation->fresh()->view_count);
        $this->assertEquals(0, InvitationView::count());
    }

    public function test_detects_zalo_and_facebook_in_app_browsers_correctly(): void
    {
        // 1. Khách mở qua Zalo trên Android
        $this->withServerVariables([
            'REMOTE_ADDR' => '14.161.45.67',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; U; Android 14; vi-vn; SM-S918B Build/UP1A.231005.007) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/120.0.0.0 Mobile Safari/537.36 Zalo/24.01.01',
        ])->get(route('invitation.show', $this->invitation->slug));

        $zaloView = InvitationView::latest('id')->first();
        $this->assertNotNull($zaloView);
        $this->assertEquals('mobile', $zaloView->device_type);
        $this->assertEquals('android', $zaloView->platform);
        $this->assertEquals('zalo', $zaloView->browser);

        // 2. Khách mở qua Facebook Messenger trên iPhone
        $this->withServerVariables([
            'REMOTE_ADDR' => '27.72.88.99',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/445.0.0.35.117;FBBV/545642571;FBDV/iPhone15,2;FBMD/iPhone;FBSN/iOS;FBSV/17.2;FBSS/3;FBID/phone;FBLC/vi_VN;FBOP/5]',
        ])->get(route('invitation.show', $this->invitation->slug));

        $fbView = InvitationView::latest('id')->first();
        $this->assertNotNull($fbView);
        $this->assertEquals('mobile', $fbView->device_type);
        $this->assertEquals('ios', $fbView->platform);
        $this->assertEquals('facebook', $fbView->browser);
    }

    public function test_personalized_guest_visit_links_to_guest_id_in_analytics(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bác Trần Văn Nam',
            'salutation' => 'Bác',
            'guest_token' => 'token-nam-bac-123456',
            'expected_attendees' => 2,
            'is_sent' => true,
        ]);

        $this->withServerVariables([
            'REMOTE_ADDR' => '42.115.89.10',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ])->get(route('invitation.show', ['slug' => $this->invitation->slug, 'token' => $guest->guest_token]));

        $view = InvitationView::latest('id')->first();
        $this->assertNotNull($view);
        $this->assertEquals($guest->id, $view->guest_id);
        $this->assertEquals('desktop', $view->device_type);
        $this->assertEquals('windows', $view->platform);
        $this->assertEquals('chrome', $view->browser);
    }

    public function test_owner_can_export_analytics_csv_with_utf8_bom_and_formula_defense(): void
    {
        // Tạo sẵn một số lượt xem
        InvitationView::create([
            'invitation_id' => $this->invitation->id,
            'wedding_id' => $this->wedding->id,
            'visitor_hash' => hash('sha256', 'visitor_sample_1'),
            'device_type' => 'mobile',
            'platform' => 'ios',
            'browser' => 'zalo',
            'referer_host' => 'zalo.me',
            'viewed_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('account.invitations.analytics.export', [
            'id' => $this->invitation->id,
            'days' => 30,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('thong_ke_thiep_cuoi_', (string) $response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        // Kiểm tra UTF-8 BOM
        $this->assertTrue(str_starts_with($content, "\xEF\xBB\xBF"));
        $this->assertStringContainsString('Thời Điểm', $content);
        $this->assertStringContainsString('Loại Thiết Bị', $content);
        $this->assertStringContainsString('mobile', $content);
        $this->assertStringContainsString('zalo', $content);
    }
}
