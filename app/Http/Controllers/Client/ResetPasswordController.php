<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordController extends Controller
{
    /**
     * Hiển thị giao diện đặt lại mật khẩu mới khi người dùng mở liên kết từ email.
     */
    public function showResetForm(Request $request, string $token): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $email = (string) $request->query('email', '');

        if ($email === '') {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Liên kết đặt lại mật khẩu thiếu thông tin email hợp lệ. Vui lòng thử lại.',
            ]);
        }

        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();

        // Kiểm tra tính hợp lệ của token và thời hạn 48 tiếng
        if (! $user || ! Password::broker()->tokenExists($user, $token)) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn (sau 48 giờ). Vui lòng tạo yêu cầu mới.',
            ]);
        }

        return view('client.pages.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Xử lý lưu mật khẩu mới và hủy bỏ token ngay lập tức (dùng 1 lần).
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|max:255',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ], [
            'token.required' => 'Mã xác thực không hợp lệ.',
            'email.required' => 'Vui lòng cung cấp địa chỉ email.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed' => 'Mật khẩu xác nhận không trùng khớp.',
            'password.min' => 'Mật khẩu phải chứa ít nhất 8 ký tự, bao gồm cả chữ và số để đảm bảo an toàn.',
        ]);

        $credentials = $request->only('email', 'password', 'password_confirmation', 'token');

        // Thực hiện đặt lại mật khẩu và tự động hủy token trong password_reset_tokens
        $status = Password::broker()->reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'last_password_reset_at' => now(), // Cập nhật thời điểm đổi mật khẩu thành công để áp dụng cooldown 7 ngày
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Khi đổi mật khẩu thành công: token đã bị xóa, liên kết cũ mất tác dụng hoàn toàn
            return redirect()->route('login')->with(
                'success',
                'Mật khẩu của bạn đã được thay đổi thành công! Vui lòng đăng nhập với mật khẩu mới.'
            );
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'Không thể đặt lại mật khẩu. Liên kết có thể đã hết hạn hoặc đã được sử dụng trước đó.',
        ]);
    }
}
