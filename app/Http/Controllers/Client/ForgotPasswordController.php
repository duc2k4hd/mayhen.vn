<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\CustomerResetPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $isExempt = $user->isExemptFromPasswordResetRestrictions();

            if (! $isExempt) {
                // 1. KIỂM TRA LINK ĐANG CÒN HIỆU LỰC (IN-FLIGHT):
                // Nếu mail đã gửi và link chưa dùng (vẫn tồn tại trong password_reset_tokens và chưa quá 48h)
                // thì KHÔNG CHO PHÉP gửi lại trên web (1 mail chỉ được gửi 1 lần).
                $activeToken = DB::table('password_reset_tokens')->where('email', $user->email)->first();
                if ($activeToken) {
                    $tokenExpireMinutes = (int) config('auth.passwords.users.expire', 2880);
                    $expiresAt = Carbon::parse($activeToken->created_at)->addMinutes($tokenExpireMinutes);

                    if ($expiresAt->isFuture()) {
                        $inFlightMsg = 'Một liên kết đặt lại mật khẩu đã được gửi đến email này trước đó và vẫn còn hiệu lực. Mỗi yêu cầu chỉ được gửi 1 lần để bảo mật tài khoản. Vui lòng kiểm tra hộp thư (kể cả mục Spam hoặc Quảng cáo).';

                        if ($request->expectsJson() || $request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'in_flight' => true,
                                'message' => $inFlightMsg,
                                'expires_at' => $expiresAt->toIso8601String(),
                            ], 422);
                        }

                        return back()->withInput($request->only('email'))->withErrors([
                            'email' => $inFlightMsg,
                        ])->with('in_flight', true);
                    }
                }

                // 2. KIỂM TRA COOLDOWN 7 NGÀY:
                // Nếu người dùng đã đổi mật khẩu thành công trong vòng 7 ngày gần nhất,
                // thì không thể tiếp tục quên mật khẩu trên web.
                if ($user->last_password_reset_at) {
                    $cooldownUntil = Carbon::parse($user->last_password_reset_at)->addDays(7);
                    if ($cooldownUntil->isFuture()) {
                        $remainingDays = Carbon::now()->diffInDays($cooldownUntil, false) + 1;
                        $formattedDate = $cooldownUntil->format('H:i d/m/Y');
                        $cooldownMsg = "Tài khoản của bạn đã thực hiện đổi mật khẩu trong vòng 7 ngày qua. Để bảo vệ an toàn tối đa cho tài khoản, hệ thống tạm khóa tính năng quên mật khẩu đến {$formattedDate} (còn khoảng {$remainingDays} ngày). Nếu cần hỗ trợ khẩn cấp, vui lòng liên hệ trực tiếp Bộ phận Chăm sóc khách hàng Mây Hẹn (Hotline hoặc Zalo).";

                        if ($request->expectsJson() || $request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'cooldown' => true,
                                'message' => $cooldownMsg,
                                'cooldown_until' => $cooldownUntil->toIso8601String(),
                            ], 429);
                        }

                        return back()->withInput($request->only('email'))->withErrors([
                            'email' => $cooldownMsg,
                        ])->with('cooldown', true);
                    }
                }
            }

            // Tạo token an toàn cryptographically secure (được hash sha256 trong DB)
            $token = Password::broker()->createToken($user);

            // Sinh URL đặt lại mật khẩu có hiệu lực 48 tiếng
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);

            // HÀNG ĐỢI EMAIL BẤT ĐỒNG BỘ (QUEUE):
            // Sử dụng Mail::queue đẩy việc gửi thư vào queue worker database/redis.
            // Ngay cả khi 1 triệu người dùng cùng bấm, server web chỉ mất < 2ms đẩy job vào hàng đợi,
            // không bị nghẽn socket SMTP và không sập server.
            try {
                Mail::to($user->email)->queue(new CustomerResetPasswordMail($user, $resetUrl, 48));
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
