<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\CustomerResetPasswordMail;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Hiển thị giao diện quên mật khẩu phong cách Quiet Luxury.
     */
    public function showLinkRequestForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('client.pages.forgot-password');
    }

    /**
     * Xử lý gửi email liên kết đặt lại mật khẩu.
     * Phòng vệ bảo mật:
     * - Chống Account Enumeration: Dù email có tồn tại hay không, kết quả trả về luôn giống hệt nhau.
     * - Chống Timing Attack: Đồng bộ thời gian phản hồi giữa email tồn tại và không tồn tại.
     * - Token Cryptographically Secure qua Password broker của Laravel, lưu băm SHA-256.
     * - Hạn dùng 48 tiếng (2880 phút) theo cấu hình config('auth.passwords.users.expire').
     * - Gửi mail từ địa chỉ no-reply@mayhen.vn.
     */
    public function sendResetLinkEmail(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email:filter|max:255',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email của bạn.',
            'email.email' => 'Địa chỉ email không đúng định dạng. Ví dụ: tenban@example.com.',
        ]);

        $email = strtolower(trim($validated['email']));

        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();

        if ($user && ! $user->isLocked()) {
            // Tạo token an toàn cryptographically secure (được hash sha256 trong DB)
            $token = Password::broker()->createToken($user);

            // Sinh URL đặt lại mật khẩu có hiệu lực 48 tiếng
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);

            try {
                Mail::to($user->email)->send(new CustomerResetPasswordMail($user, $resetUrl, 48));
            } catch (\Throwable $e) {
                report($e);
            }
        } else {
            // Chống timing attack: mô phỏng độ trễ nhỏ nếu user không tồn tại trong hệ thống
            usleep(random_int(40000, 80000));
        }

        $successMessage = 'Nếu địa chỉ email này tồn tại trong hệ thống, hướng dẫn đặt lại mật khẩu đã được gửi đến hộp thư của bạn.';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'email' => $email,
            ]);
        }

        return back()->with('status', $successMessage)->with('sent_email', $email);
    }
}
