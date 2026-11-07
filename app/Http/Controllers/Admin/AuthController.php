<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Smart root entry for /admin.
     * Redirects to /admin/dashboard if authenticated as admin,
     * otherwise redirects to /admin/login.
     */
    public function index(): RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    }

    /**
     * Show the quiet luxury admin login page.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle admin login authentication.
     */
    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập email quản trị.',
            'email.email' => 'Email chưa đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($validated['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $message = "Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau {$seconds} giây.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 429);
            }

            return back()->withErrors(['email' => $message])->withInput($request->only('email'));
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']], $remember)) {
            $user = Auth::user();

            if (! $user || ! $user->isAdmin()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = 'Tài khoản của bạn không có quyền truy cập không gian quản trị.';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => $message], 403);
                }

                return back()->withErrors(['email' => $message])->withInput($request->only('email'));
            }

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Đăng nhập thành công!',
                    'redirect' => route('admin.dashboard'),
                ]);
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 300);
        $errorMessage = 'Email hoặc mật khẩu không chính xác.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $errorMessage], 422);
        }

        return back()
            ->withErrors(['email' => $errorMessage])
            ->withInput($request->only('email'));
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Bạn đã đăng xuất thành công khỏi không gian quản trị.');
    }
}
