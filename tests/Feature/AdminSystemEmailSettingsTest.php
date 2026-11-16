<?php

use App\Mail\CustomerResetPasswordMail;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\PlatformConfigurationService;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'email' => 'admin_mail_test@mayhen.vn',
        'role' => 'admin',
        'password' => bcrypt('AdminPassword123!'),
    ]);

    app(PlatformConfigurationService::class)->seedDefaultsIfEmpty();
});

test('platform settings provides 3 standardized default emails', function () {
    expect(PlatformSetting::getAdminEmail())->toBe('admin@mayhen.vn')
        ->and(PlatformSetting::getSupportEmail())->toBe('support@mayhen.vn')
        ->and(PlatformSetting::getNoreplyEmail())->toBe('no-reply@mayhen.vn');
});

test('admin can view mail configuration group with 3 email settings and overview cards', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.settings.platform', ['group' => 'mail']));

    $response->assertOk();
    $response->assertSee('Máy Chủ Thư Điện Tử (SMTP Server)');
    $response->assertSee('3 Tài Khoản Email Chuẩn Hóa Phân Quyền Toàn Nền Tảng Mây Hẹn');
    $response->assertSee('admin@mayhen.vn');
    $response->assertSee('support@mayhen.vn');
    $response->assertSee('no-reply@mayhen.vn');
    $response->assertSee('mail_from_admin');
    $response->assertSee('mail_from_support');
    $response->assertSee('mail_from_noreply');
});

test('admin can update the 3 system emails in platform settings', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.settings.platform.update', ['group' => 'mail']), [
        'mail_mailer' => 'smtp',
        'mail_host' => 'smtp.sendgrid.net',
        'mail_port' => '587',
        'mail_encryption' => 'tls',
        'mail_username' => 'custom_mailer@mayhen.vn',
        'mail_from_address' => 'system@mayhen.vn',
        'mail_from_name' => 'Mây Hẹn Custom Brand',
        'mail_from_admin' => 'internal-alerts@mayhen.vn',
        'mail_from_support' => 'cskh-vip@mayhen.vn',
        'mail_from_noreply' => 'outgoing-noreply@mayhen.vn',
    ]);

    $response->assertSessionHas('success');

    expect(PlatformSetting::getAdminEmail())->toBe('internal-alerts@mayhen.vn')
        ->and(PlatformSetting::getSupportEmail())->toBe('cskh-vip@mayhen.vn')
        ->and(PlatformSetting::getNoreplyEmail())->toBe('outgoing-noreply@mayhen.vn');
});

test('customer reset password mail dynamically reads noreply email from platform settings', function () {
    // 1. Kiểm tra với giá trị mặc định no-reply@mayhen.vn
    $user = User::factory()->create([
        'email' => 'couple_test@mayhen.vn',
        'name' => 'Nguyễn Văn A',
    ]);

    $mailable = new CustomerResetPasswordMail($user, 'https://mayhen.vn/password/reset/token123', 48);
    $envelope = $mailable->envelope();

    expect($envelope->from->address)->toBe('no-reply@mayhen.vn')
        ->and($envelope->subject)->toContain('Hướng Dẫn Đặt Lại Mật Khẩu');

    // Kiểm tra view content được truyền $noreplyEmail
    $content = $mailable->content();
    expect($content->with['noreplyEmail'])->toBe('no-reply@mayhen.vn');

    // 2. Cập nhật no-reply email trong settings và kiểm tra mailable đổi theo
    PlatformSetting::setValue(PlatformSetting::GROUP_MAIL, 'mail_from_noreply', 'auth-notice@mayhen.vn');
    PlatformSetting::setValue(PlatformSetting::GROUP_MAIL, 'mail_from_name', 'Mây Hẹn Security Team');

    $updatedMailable = new CustomerResetPasswordMail($user, 'https://mayhen.vn/password/reset/token123', 48);
    $updatedEnvelope = $updatedMailable->envelope();

    expect($updatedEnvelope->from->address)->toBe('auth-notice@mayhen.vn')
        ->and($updatedEnvelope->from->name)->toBe('Mây Hẹn Security Team');

    $updatedContent = $updatedMailable->content();
    expect($updatedContent->with['noreplyEmail'])->toBe('auth-notice@mayhen.vn');
});

test('client forgot password page renders dynamic noreply email from platform settings', function () {
    PlatformSetting::setValue(PlatformSetting::GROUP_MAIL, 'mail_from_noreply', 'custom-noreply@mayhen.vn');

    $response = $this->get(route('password.request'));

    $response->assertOk();
    $response->assertSee('custom-noreply@mayhen.vn');
});
