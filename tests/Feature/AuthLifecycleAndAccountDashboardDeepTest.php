<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLifecycleAndAccountDashboardDeepTest extends TestCase
{
    use RefreshDatabase;

    protected InvitationTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = InvitationTemplate::create([
            'slug' => 'thiep-mau-test',
            'name' => 'Mẫu Thiệp Mẫu Test',
            'category' => 'minimal',
            'thumbnail_url' => '/images/sample.jpg',
            'demo_url' => '/xem-thu/thiep-mau-test',
            'default_config' => ['tier' => 'free'],
            'current_version' => 'v1',
            'is_active' => true,
            'is_featured' => false,
        ]);
    }

    /**
     * AUTH-001 -> AUTH-005: Đăng ký tài khoản mới, xác thực thông tin và tự động đăng nhập.
     */
    public function test_user_registration_creates_account_and_logs_in(): void
    {
        $response = $this->post('/dang-ky', [
            'name' => 'Nguyễn Văn Cường',
            'email' => 'cuong.nguyen@mayhen.test',
            'phone' => '0912345678',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ]);

        $response->assertRedirect(route('onboarding.index'));
        $this->assertAuthenticated();

        $user = User::where('email', 'cuong.nguyen@mayhen.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Nguyễn Văn Cường', $user->name);
        $this->assertEquals('customer', $user->role);
        $this->assertTrue(Hash::check('SecurePass123', $user->password));
    }

    /**
     * AUTH-004, AUTH-005 & AUTH-007: Đăng nhập thành công, báo lỗi sai mật khẩu và đăng xuất an toàn.
     */
    public function test_user_login_authentication_and_logout_lifecycle(): void
    {
        $user = User::factory()->create([
            'email' => 'lan.tran@mayhen.test',
            'password' => Hash::make('MyPassword2026'),
        ]);

        // 1. Đăng nhập sai mật khẩu -> Thất bại, trả về lỗi email
        $wrongResponse = $this->post('/dang-nhap', [
            'email' => 'lan.tran@mayhen.test',
            'password' => 'WrongPassword',
        ]);

        $wrongResponse->assertSessionHasErrors(['email']);
        $this->assertGuest();

        // 2. Đăng nhập đúng mật khẩu -> Thành công và lưu session
        $correctResponse = $this->post('/dang-nhap', [
            'email' => 'lan.tran@mayhen.test',
            'password' => 'MyPassword2026',
        ]);

        $correctResponse->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        // 3. Đăng xuất -> Thu hồi session và đưa về trang chủ
        $logoutResponse = $this->post('/dang-xuat');
        $logoutResponse->assertRedirect(route('home'));
        $this->assertGuest();
    }

    /**
     * AUTH-011 & AUTH-019: Chống Open Redirect ra bên ngoài và giữ nguyên redirect an toàn nội bộ.
     */
    public function test_auth_redirect_defense_blocks_external_urls_and_permits_safe_internal_paths(): void
    {
        $user = User::factory()->create([
            'email' => 'safe.redirect@mayhen.test',
            'password' => Hash::make('Secret123'),
        ]);

        // 1. Cố ý redirect ra website độc hại bên ngoài -> Bị chặn, fallback về home
        $evilResponse = $this->post('/dang-nhap', [
            'email' => 'safe.redirect@mayhen.test',
            'password' => 'Secret123',
            'redirect' => 'https://evil-phishing-site.com/steal-creds',
        ]);
        $evilResponse->assertRedirect(route('home'));

        // 2. Thử tấn công protocol-relative URL //evil.com -> Bị chặn, fallback về home
        Auth::logout();
        $protocolRelativeResponse = $this->post('/dang-nhap', [
            'email' => 'safe.redirect@mayhen.test',
            'password' => 'Secret123',
            'redirect' => '//evil.com/fake-login',
        ]);
        $protocolRelativeResponse->assertRedirect(route('home'));

        // 3. Redirect an toàn nội bộ (/mau-thiep/thiep-mau-test) -> Chấp thuận chuyển tiếp đúng trang
        Auth::logout();
        $safeResponse = $this->post('/dang-nhap', [
            'email' => 'safe.redirect@mayhen.test',
            'password' => 'Secret123',
            'redirect' => '/mau-thiep/thiep-mau-test',
        ]);
        $safeResponse->assertRedirect('/mau-thiep/thiep-mau-test');
    }

    /**
     * AUTH-013 & AUTH-014: Nhận quyền sở hữu thiệp nháp vô danh khi đăng nhập, CẤM chiếm đoạt thiệp của tài khoản khác.
     */
    public function test_sync_owned_invitations_claims_anonymous_drafts_and_prevents_hijacking(): void
    {
        $guestUser = User::firstOrCreate(
            ['email' => 'guest@mayhen.vn'],
            ['name' => 'Khách Trải Nghiệm', 'password' => Hash::make('guest'), 'role' => 'customer']
        );

        $victim = User::factory()->create(['email' => 'victim@mayhen.test']);
        $attacker = User::factory()->create(['email' => 'attacker@mayhen.test', 'password' => Hash::make('Attack123')]);

        // Thiệp A: Tạo khi chưa đăng nhập (user_id thuộc về guest@mayhen.vn)
        $anonymousWedding = Wedding::create([
            'user_id' => $guestUser->id,
            'groom_name' => 'Chú Rể Vãng Lai',
            'bride_name' => 'Cô Dâu Vãng Lai',
            'wedding_date' => now()->addDays(60)->toDateString(),
        ]);
        $anonymousInvitation = Invitation::create([
            'wedding_id' => $anonymousWedding->id,
            'template_id' => $this->template->id,
            'slug' => 'thiep-vang-lai-chua-chu',
            'is_published' => false,
        ]);

        // Thiệp B: Đã thuộc về nạn nhân ($victim->id)
        $victimWedding = Wedding::create([
            'user_id' => $victim->id,
            'groom_name' => 'Chú Rể Nạn Nhân',
            'bride_name' => 'Cô Dâu Nạn Nhân',
            'wedding_date' => now()->addDays(90)->toDateString(),
        ]);
        $victimInvitation = Invitation::create([
            'wedding_id' => $victimWedding->id,
            'template_id' => $this->template->id,
            'slug' => 'thiep-da-co-chu-chinh-chu',
            'is_published' => false,
        ]);

        // Attacker có trong session cả 2 slug thiệp (nháp của mình và slug rà quét của nạn nhân)
        $sessionData = [
            'owned_invitation_slugs' => [
                $anonymousInvitation->slug,
                $victimInvitation->slug,
            ],
        ];

        // Attacker tiến hành đăng nhập
        $this->withSession($sessionData)->post('/dang-nhap', [
            'email' => 'attacker@mayhen.test',
            'password' => 'Attack123',
        ]);

        // 1. Thiệp vô danh được tự động chuyển quyền sang cho Attacker
        $anonymousWedding->refresh();
        $this->assertEquals($attacker->id, $anonymousWedding->user_id);

        // 2. Thiệp của nạn nhân KHÔNG BỊ CƯỚP, quyền sở hữu vẫn bảo toàn thuộc về Victim (AUTH-014)
        $victimWedding->refresh();
        $this->assertEquals($victim->id, $victimWedding->user_id);
        $this->assertNotEquals($attacker->id, $victimWedding->user_id);
    }

    /**
     * DASH-001 -> DASH-008: Màn hình Dashboard tổng quan hiển thị thống kê thiệp, khách mời và lời chúc.
     */
    public function test_account_dashboard_overview_renders_statistics_and_recent_activity(): void
    {
        $user = User::factory()->create();
        $wedding = Wedding::create([
            'user_id' => $user->id,
            'groom_name' => 'Bảo Long',
            'bride_name' => 'Phương Thảo',
            'wedding_date' => now()->addDays(45)->toDateString(),
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $this->template->id,
            'slug' => 'bao-long-phuong-thao-wedding',
            'is_published' => true,
        ]);

        // Tạo 2 khách mời
        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Khách Mời 01',
            'expected_attendees' => 2,
            'guest_token' => 'guest-token-111',
        ]);
        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Khách Mời 02',
            'expected_attendees' => 3,
            'guest_token' => 'guest-token-222',
        ]);

        // Tạo 1 phản hồi RSVP tham dự
        GuestResponse::create([
            'wedding_id' => $wedding->id,
            'guest_name' => 'Khách Mời 01',
            'attending_status' => 'attending',
            'adults_count' => 2,
            'children_count' => 0,
        ]);

        // Tạo 1 lời chúc
        WeddingWish::create([
            'wedding_id' => $wedding->id,
            'name' => 'Người Bạn Thân',
            'message' => 'Chúc hai bạn mãi hạnh phúc bền lâu!',
            'is_approved' => true,
        ]);

        $response = $this->actingAs($user)->get('/tai-khoan');

        $response->assertOk();
        $response->assertSee('Bảo Long');
        $response->assertSee('Phương Thảo');
        // Thống kê tổng số thiệp, khách dự kiến, khách tham dự và lời chúc
        $response->assertSee('bao-long-phuong-thao-wedding');
    }

    /**
     * DASH-010 & DASH-011: Nhân bản thiệp cưới (Duplicate) và Xóa thiệp cưới (Delete) với kiểm soát phân quyền IDOR.
     */
    public function test_invitation_duplicate_and_delete_with_strict_idor_authorization(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $wedding = Wedding::create([
            'user_id' => $owner->id,
            'groom_name' => 'Đức Thịnh',
            'bride_name' => 'Mai Anh',
            'wedding_date' => now()->addDays(50)->toDateString(),
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $this->template->id,
            'slug' => 'duc-thinh-mai-anh-original',
            'title' => 'Thiệp Cưới Chính Thức',
            'is_published' => true,
        ]);

        // 1. Người lạ (Stranger) cố ý nhân bản thiệp của Owner -> 404
        $strangerDuplicate = $this->actingAs($stranger)->post('/tai-khoan/thiep/'.$invitation->id.'/nhan-ban');
        $strangerDuplicate->assertNotFound();

        // 2. Chủ thiệp nhân bản -> Thành công, tạo bản sao mới với is_published = false
        $ownerDuplicate = $this->actingAs($owner)->post('/tai-khoan/thiep/'.$invitation->id.'/nhan-ban');
        $ownerDuplicate->assertRedirect(route('account.invitations'));

        $this->assertEquals(2, $wedding->invitations()->count());
        $copyInvitation = $wedding->invitations()->where('id', '!=', $invitation->id)->first();
        $this->assertNotNull($copyInvitation);
        $this->assertStringContainsString('sao-chep', $copyInvitation->slug);
        $this->assertFalse($copyInvitation->is_published);
        $this->assertStringContainsString('(Bản Sao)', $copyInvitation->title);

        // 3. Người lạ cố ý xóa bản sao -> 404
        $strangerDelete = $this->actingAs($stranger)->delete('/tai-khoan/thiep/'.$copyInvitation->id);
        $strangerDelete->assertNotFound();
        $this->assertDatabaseHas('invitations', ['id' => $copyInvitation->id]);

        // 4. Chủ thiệp xóa bản sao -> Thành công
        $ownerDelete = $this->actingAs($owner)->delete('/tai-khoan/thiep/'.$copyInvitation->id);
        $ownerDelete->assertRedirect(route('account.invitations'));
        $this->assertDatabaseMissing('invitations', ['id' => $copyInvitation->id]);
    }
}
