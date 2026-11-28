<?php

use App\Mail\EmailRegistrationVerificationMail;
use App\Models\EmailRegistrationToken;
use App\Models\InvitationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'client.test@mayhen.test',
        'password' => Hash::make('Secret123456'),
        'role' => 'customer',
    ]);
});

test('đăng nhập với query param redirect sẽ chuyển hướng chính xác đến trang đích', function () {
    $response = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => '/mau-thiep/hac-bach-trao-duyen',
    ]);

    $response->assertRedirect('/mau-thiep/hac-bach-trao-duyen');
    $this->assertAuthenticatedAs($this->user);
});

test('đăng nhập bảo toàn đầy đủ các query params của link trước đó', function () {
    $targetUrl = '/tao-thiep?template=celestial-romance&step=couple';

    $response = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => $targetUrl,
    ]);

    $response->assertRedirect($targetUrl);
    $this->assertAuthenticatedAs($this->user);
});

test('đăng nhập tự động chuyển hướng về url.intended trong session khi bị middleware auth chặn', function () {
    // Giả lập middleware auth đã lưu trang khách đang muốn truy cập vào session
    $response = $this->withSession(['url.intended' => url('/tai-khoan')])
        ->post(route('login.submit'), [
            'email' => 'client.test@mayhen.test',
            'password' => 'Secret123456',
        ]);

    $response->assertRedirect(url('/tai-khoan'));
    $this->assertAuthenticatedAs($this->user);
});

test('khách truy cập form đăng nhập không có ?redirect= sẽ mặc định chuyển về /tai-khoan sau khi đăng nhập', function () {
    $serviceUrl = route('services');

    // Khách hàng đang ở trang /dich-vu và bấm mở trang đăng nhập (không có ?redirect=)
    $formResponse = $this->from($serviceUrl)->get(route('login'));
    $formResponse->assertOk();

    // Đăng nhập thành công mặc định chuyển về /tai-khoan
    $loginResponse = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
    ]);

    $loginResponse->assertRedirect(route('account.dashboard'));
    $this->assertAuthenticatedAs($this->user);
});

test('chọn mẫu premium rồi đăng nhập hoặc đăng ký thành công sẽ chuyển đúng về mẫu premium đó', function () {
    $premiumTemplate = InvitationTemplate::create([
        'slug' => 'mau-premium-redirect-test',
        'name' => 'Mẫu Premium Redirect',
        'category' => 'modern',
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'demo_url' => '/xem-thu/mau-premium-redirect-test',
        'default_config' => ['tier' => 'premium'],
        'is_active' => true,
    ]);

    $targetEditorUrl = route('invitation.create', ['template' => $premiumTemplate->slug]);

    // 1. Khách vãng lai chọn mẫu Premium -> chuyển sang /dang-nhap?redirect=...
    $guardRes = $this->get($targetEditorUrl);
    $guardRes->assertRedirect(route('login', ['redirect' => $targetEditorUrl]));

    // 2. Đăng nhập thành công -> quay đúng mẫu Premium
    $this->get(route('login', ['redirect' => $targetEditorUrl]))->assertOk();
    $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => $targetEditorUrl,
    ])->assertRedirect($targetEditorUrl);

    // 3. Đăng xuất & đăng ký qua link email từ chính ?redirect= đó -> quay đúng mẫu Premium
    $this->post(route('logout'));
    Mail::fake();
    $this->post(route('register.submit'), [
        'email' => 'new.premium.user@gmail.com',
        'redirect' => $targetEditorUrl,
    ])->assertRedirect();

    $rawToken = null;
    Mail::assertQueued(EmailRegistrationVerificationMail::class, function ($mail) use (&$rawToken) {
        if (preg_match('/\/dang-ky\/xac-thuc\/([a-f0-9]{64})$/', $mail->verificationUrl, $m)) {
            $rawToken = $m[1];
        }

        return true;
    });

    $this->post(route('register.verify.submit', ['token' => $rawToken]), [
        'name' => 'Khách Premium Mới',
        'password' => 'SecurePass123',
        'password_confirmation' => 'SecurePass123',
    ])->assertRedirect($targetEditorUrl);
});

test('đăng nhập fallback an toàn về /tai-khoan khi không có redirect hay intended URL', function () {
    $response = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
    ]);

    $response->assertRedirect(route('account.dashboard'));
    $this->assertAuthenticatedAs($this->user);
});

test('đăng ký tài khoản mới qua link xác thực email cũng tự động chuyển hướng lại link ban đầu nếu có redirect', function () {
    [$rawToken, $tokenHash] = EmailRegistrationToken::generateTokenPair();
    EmailRegistrationToken::create([
        'email' => 'thutrang.new@mayhen.test',
        'token_hash' => $tokenHash,
        'redirect_to' => '/bang-gia',
        'expires_at' => now()->addHours(24),
    ]);

    $response = $this->post(route('register.verify.submit', ['token' => $rawToken]), [
        'name' => 'Nguyễn Thu Trang',
        'password' => 'SecurePass123',
        'password_confirmation' => 'SecurePass123',
    ]);

    $response->assertRedirect('/bang-gia');
    $this->assertAuthenticated();
});

test('đăng ký tài khoản mới qua link xác thực email không có redirect sẽ fallback về onboarding', function () {
    [$rawToken, $tokenHash] = EmailRegistrationToken::generateTokenPair();
    EmailRegistrationToken::create([
        'email' => 'nam.tran@mayhen.test',
        'token_hash' => $tokenHash,
        'expires_at' => now()->addHours(24),
    ]);

    $response = $this->post(route('register.verify.submit', ['token' => $rawToken]), [
        'name' => 'Trần Văn Nam',
        'password' => 'SecurePass123',
        'password_confirmation' => 'SecurePass123',
    ]);

    $response->assertRedirect(route('onboarding.index'));
    $this->assertAuthenticated();
});

test('chặn đứng các cuộc tấn công Open Redirect ra bên ngoài hoặc protocol relative URL', function () {
    // 1. URL miền độc hại bên ngoài
    $resEvil = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => 'https://evil-phishing.com/steal-account',
    ]);
    $resEvil->assertRedirect(route('account.dashboard'));

    // 2. Protocol relative //evil.com
    auth()->logout();
    $resProtocol = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => '//evil.com/login',
    ]);
    $resProtocol->assertRedirect(route('account.dashboard'));
});

test('tự động phát hiện và chặn vòng lặp redirect về chính các trang auth', function () {
    $resLoopLogin = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => '/dang-nhap',
    ]);
    $resLoopLogin->assertRedirect(route('account.dashboard'));

    auth()->logout();
    $resLoopRegister = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => '/dang-ky',
    ]);
    $resLoopRegister->assertRedirect(route('account.dashboard'));
});

test('người dùng đã đăng nhập truy cập /dang-nhap kèm param redirect sẽ được chuyển ngay đến link đó', function () {
    $response = $this->actingAs($this->user)->get(route('login', ['redirect' => '/cam-nang-cuoi']));

    $response->assertRedirect('/cam-nang-cuoi');
});

test('dựng gia phả rồi bấm tạo link thật yêu cầu đăng nhập/đăng ký sẽ luôn quay lại đúng trang Studio Gia Phả', function () {
    $studioUrl = '/gia-pha/tao-thu?mau=thanh-ngoc-gia-phong';

    // 1. Studio render đầy đủ link Đăng nhập và Đăng ký kèm ?redirect=...
    $studioRes = $this->get($studioUrl);
    $studioRes->assertOk()
        ->assertSee('id="mayhenFtModalLoginLink"', false)
        ->assertSee('id="mayhenFtModalRegisterLink"', false)
        ->assertSee(route('login', ['redirect' => $studioUrl]), false)
        ->assertSee(route('register', ['redirect' => $studioUrl]), false);

    // 2. Khách vãng lai gọi publish -> trả 403 PREMIUM_PLAN_REQUIRED kèm cả login_url và register_url có redirect về Studio
    $publishRes = $this->postJson(route('family_tree.publish'), [
        'title' => 'Gia Phả Họ Trần',
        'template_style' => 'thanh-ngoc-gia-phong',
        'members' => [['id' => 'm1', 'name' => 'Cụ Tổ Trần']],
    ]);

    $publishRes->assertStatus(403)
        ->assertJsonPath('code', 'PREMIUM_PLAN_REQUIRED');
    expect($publishRes->json('login_url'))->toContain('redirect=%2Fgia-pha%2Ftao-thu');
    expect($publishRes->json('register_url'))->toContain('redirect=%2Fgia-pha%2Ftao-thu');

    // 3. Trang đăng nhập hiển thị thông báo giữ bản nháp gia phả và đăng nhập xong quay thẳng về Studio kèm query ?mau=...
    $this->get(route('login', ['redirect' => $studioUrl]))
        ->assertOk()
        ->assertSee('tự động lưu tạm');

    $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => $studioUrl,
    ])->assertRedirect($studioUrl);
});
