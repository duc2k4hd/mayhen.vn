<?php

use App\Mail\EmailRegistrationVerificationMail;
use App\Models\EmailRegistrationToken;
use App\Models\User;
use App\Services\ClientDeviceProtectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('email đã tồn tại trên hệ thống thì thông báo lỗi trực tiếp và tuyệt đối không gửi mail xác thực', function () {
    Mail::fake();

    User::factory()->create([
        'email' => 'da.ton.tai@gmail.com',
    ]);

    $response = $this->from(route('register'))->post(route('register.submit'), [
        'email' => 'da.ton.tai@gmail.com',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHasErrors(['email' => 'Địa chỉ email này đã tồn tại trên hệ thống, vui lòng chọn mail khác hoặc chuyển sang đăng nhập!']);
    $response->assertSessionMissing('registration_link_sent');

    Mail::assertNothingQueued();
});

test('mỗi session chỉ được gửi mail xác thực 1 phút một lần', function () {
    Mail::fake();

    $email1 = 'lan1@gmail.com';
    $email2 = 'lan2@gmail.com';

    // Lần gửi thứ 1 trong session: Thành công
    $res1 = $this->from(route('register'))->post(route('register.submit'), [
        'email' => $email1,
    ]);
    $res1->assertRedirect(route('register'));
    $res1->assertSessionHas('registration_link_sent', true);

    Mail::assertQueued(EmailRegistrationVerificationMail::class, 1);

    // Lần gửi thứ 2 ngay lập tức trong cùng session: Bị chặn với lỗi session rate limit 1 phút
    $res2 = $this->from(route('register'))->post(route('register.submit'), [
        'email' => $email2,
    ]);
    $res2->assertRedirect(route('register'));
    $res2->assertSessionHasErrors('email');

    $errors = session('errors')->get('email');
    expect($errors[0])->toContain('Mỗi phiên chỉ được gửi yêu cầu xác thực 1 phút một lần');

    // Số mail được queue vẫn chỉ là 1
    Mail::assertQueued(EmailRegistrationVerificationMail::class, 1);

    // Tua nhanh 61 giây: Cho phép gửi tiếp
    $this->travel(61)->seconds();

    $res3 = $this->from(route('register'))->post(route('register.submit'), [
        'email' => $email2,
    ]);
    $res3->assertRedirect(route('register'));
    $res3->assertSessionHas('registration_link_sent', true);

    Mail::assertQueued(EmailRegistrationVerificationMail::class, 2);
});

test('đăng ký trên 5 tài khoản trên 1 thiết bị máy thì bị cấm đăng ký 7 ngày, không ảnh hưởng máy khác chung ip mạng', function () {
    Mail::fake();
    $deviceService = app(ClientDeviceProtectionService::class);

    $machineIdA = (string) Str::uuid();
    $machineIdB = (string) Str::uuid();
    $sharedNetworkIp = '113.161.88.99'; // Cùng 1 IP Public mạng Wifi quán cafe

    // Xóa sạch cache test
    $deviceService->unbanMachine($machineIdA);
    $deviceService->unbanMachine($machineIdB);

    // Máy A đăng ký thành công 5 tài khoản lần lượt
    for ($i = 1; $i <= 5; $i++) {
        $email = "user.machine_a.{$i}@gmail.com";

        // Gửi link xác thực với Cookie máy A
        $postRes = $this->from(route('register'))
            ->withServerVariables(['REMOTE_ADDR' => $sharedNetworkIp])
            ->withCookie(ClientDeviceProtectionService::COOKIE_NAME, $machineIdA)
            ->post(route('register.submit'), [
                'email' => $email,
            ]);
        $postRes->assertSessionHasNoErrors();
        $postRes->assertRedirect(route('register'));

        // Lấy token vừa sinh
        $tokenRow = EmailRegistrationToken::where('email', $email)->firstOrFail();
        [$rawToken, $tokenHash] = EmailRegistrationToken::generateTokenPair();
        $tokenRow->update(['token_hash' => $tokenHash]);

        // Hoàn tất đăng ký tài khoản
        $completeRes = $this->withServerVariables(['REMOTE_ADDR' => $sharedNetworkIp])
            ->withCookie(ClientDeviceProtectionService::COOKIE_NAME, $machineIdA)
            ->post(route('register.verify.submit', ['token' => $rawToken]), [
                'name' => "User Machine A {$i}",
                'password' => 'MatKhauAnToan123',
                'password_confirmation' => 'MatKhauAnToan123',
            ]);
        $completeRes->assertRedirect(route('onboarding.index'));

        // Đăng xuất và tua 61 giây để chuẩn bị cho lượt tiếp theo
        $this->post(route('logout'));
        $this->travel(61)->seconds();
    }

    // Xác nhận máy A đã đăng ký đúng 5 tài khoản và bị kích hoạt cấm 7 ngày
    expect($deviceService->getRegisteredCount($machineIdA))->toBe(5)
        ->and($deviceService->isMachineBanned($machineIdA))->toBeTrue()
        ->and($deviceService->canRegister($machineIdA))->toBeFalse();

    // Máy A cố gửi mail đăng ký tài khoản thứ 6: Bị chặn ngay lập tức kèm thông báo cấm 7 ngày
    $attemptRes = $this->withServerVariables(['REMOTE_ADDR' => $sharedNetworkIp])
        ->withCookie(ClientDeviceProtectionService::COOKIE_NAME, $machineIdA)
        ->from(route('register'))
        ->post(route('register.submit'), [
            'email' => 'user.machineA.6@gmail.com',
        ]);

    $attemptRes->assertRedirect(route('register'));
    $attemptRes->assertSessionHasErrors('email');
    $errors = session('errors')->get('email');
    expect($errors[0])->toContain('Thiết bị này đã vượt quá giới hạn đăng ký (tối đa 5 tài khoản). Tạm thời không thể đăng ký mới');

    // Mở trang đăng ký trên Máy A: Hiển thị giao diện cảnh báo thiết bị đã bị khóa 7 ngày
    $getRegisterA = $this->withServerVariables(['REMOTE_ADDR' => $sharedNetworkIp])
        ->withCookie(ClientDeviceProtectionService::COOKIE_NAME, $machineIdA)
        ->get(route('register'));
    $getRegisterA->assertOk();
    $getRegisterA->assertSee('Giới hạn đăng ký trên thiết bị');
    $getRegisterA->assertSee('Thiết bị này đã vượt quá số lượng tài khoản cho phép (tối đa 5 tài khoản)');

    // Trong khi đó, Máy B (khách hàng khác dùng CHUNG địa chỉ IP mạng Wifi 113.161.88.99):
    // Hoàn toàn KHÔNG bị cấm, vẫn có thể đăng ký tài khoản bình thường
    expect($deviceService->isMachineBanned($machineIdB))->toBeFalse()
        ->and($deviceService->canRegister($machineIdB))->toBeTrue();

    $getRegisterB = $this->withServerVariables(['REMOTE_ADDR' => $sharedNetworkIp])
        ->withCookie(ClientDeviceProtectionService::COOKIE_NAME, $machineIdB)
        ->get(route('register'));
    $getRegisterB->assertOk();
    $getRegisterB->assertDontSee('Giới hạn đăng ký trên thiết bị');

    $postResB = $this->withServerVariables(['REMOTE_ADDR' => $sharedNetworkIp])
        ->withCookie(ClientDeviceProtectionService::COOKIE_NAME, $machineIdB)
        ->post(route('register.submit'), [
            'email' => 'khachhang.mayB@gmail.com',
        ]);
    $postResB->assertRedirect(route('register'));
    $postResB->assertSessionHas('registration_link_sent', true);
});
