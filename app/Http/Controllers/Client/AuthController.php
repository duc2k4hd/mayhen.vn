<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Hiển thị trang đăng nhập phong cách Quiet Luxury.
     */
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect($request->query('redirect', route('home')));
        }

        return view('client.pages.login', [
            'redirect' => $request->query('redirect'),
        ]);
    }

    /**
     * Xử lý đăng nhập người dùng.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $redirectUrl = $request->input('redirect');
            if ($redirectUrl && filter_var($redirectUrl, FILTER_VALIDATE_URL) && str_starts_with($redirectUrl, url('/'))) {
                return redirect($redirectUrl)->with('success', 'Đăng nhập thành công! Chào mừng bạn quay trở lại.');
            }

            return redirect()->intended(route('home'))->with('success', 'Đăng nhập thành công! Chào mừng bạn quay trở lại.');
        }

        return back()->withInput($request->only('email', 'redirect'))->withErrors([
            'email' => 'Email hoặc mật khẩu không chính xác. Vui lòng kiểm tra lại.',
        ]);
    }

    /**
     * Hiển thị trang đăng ký tài khoản mới.
     */
    public function showRegisterForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect($request->query('redirect', route('home')));
        }

        return view('client.pages.register', [
            'redirect' => $request->query('redirect'),
        ]);
    }

    /**
     * Xử lý đăng ký tài khoản mới.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng. Vui lòng đăng nhập hoặc dùng email khác.',
            'password.required' => 'Vui lòng thiết lập mật khẩu.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu phải chứa ít nhất 6 ký tự.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        $redirectUrl = $request->input('redirect');
        if ($redirectUrl && filter_var($redirectUrl, FILTER_VALIDATE_URL) && str_starts_with($redirectUrl, url('/'))) {
            return redirect($redirectUrl)->with('success', 'Đăng ký tài khoản thành công! Bạn có thể bắt đầu tạo thiệp ngay bây giờ.');
        }

        return redirect()->route('home')->with('success', 'Đăng ký tài khoản thành công! Chào mừng bạn đến với Mây Hẹn.');
    }

    /**
     * Đăng xuất tài khoản.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Đã đăng xuất tài khoản an toàn.');
    }
}
