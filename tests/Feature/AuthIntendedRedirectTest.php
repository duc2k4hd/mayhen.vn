<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

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

test('khách truy cập form đăng nhập từ một trang nội bộ sẽ tự động được ghi nhận và chuyển lại sau khi đăng nhập', function () {
    $serviceUrl = route('services');

    // Khách hàng đang ở trang /dich-vu và click mở trang đăng nhập
    $formResponse = $this->from($serviceUrl)->get(route('login'));
    $formResponse->assertOk();
    $formResponse->assertSee('value="'.$serviceUrl.'"', false);

    // Đăng nhập thành công mà không cần truyền redirect thủ công
    $loginResponse = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => $serviceUrl,
    ]);

    $loginResponse->assertRedirect($serviceUrl);
    $this->assertAuthenticatedAs($this->user);
});

test('đăng nhập fallback an toàn về trang chủ khi không có redirect hay intended URL', function () {
    $response = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($this->user);
});

test('đăng ký tài khoản mới cũng tự động chuyển hướng lại link ban đầu nếu có redirect', function () {
    $response = $this->post(route('register.submit'), [
        'name' => 'Nguyễn Thu Trang',
        'email' => 'thutrang.new@mayhen.test',
        'password' => 'SecurePass123',
        'password_confirmation' => 'SecurePass123',
        'redirect' => '/bang-gia',
    ]);

    $response->assertRedirect('/bang-gia');
    $this->assertAuthenticated();
});

test('đăng ký tài khoản mới không có redirect sẽ fallback về onboarding', function () {
    $response = $this->post(route('register.submit'), [
        'name' => 'Trần Văn Nam',
        'email' => 'nam.tran@mayhen.test',
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
    $resEvil->assertRedirect(route('home'));

    // 2. Protocol relative //evil.com
    auth()->logout();
    $resProtocol = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => '//evil.com/login',
    ]);
    $resProtocol->assertRedirect(route('home'));
});

test('tự động phát hiện và chặn vòng lặp redirect về chính các trang auth', function () {
    $resLoopLogin = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => '/dang-nhap',
    ]);
    $resLoopLogin->assertRedirect(route('home'));

    auth()->logout();
    $resLoopRegister = $this->post(route('login.submit'), [
        'email' => 'client.test@mayhen.test',
        'password' => 'Secret123456',
        'redirect' => '/dang-ky',
    ]);
    $resLoopRegister->assertRedirect(route('home'));
});

test('người dùng đã đăng nhập truy cập /dang-nhap kèm param redirect sẽ được chuyển ngay đến link đó', function () {
    $response = $this->actingAs($this->user)->get(route('login', ['redirect' => '/cam-nang-cuoi']));

    $response->assertRedirect('/cam-nang-cuoi');
});
