<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
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
        $redirect = $this->determineRedirectUrl($request);

        if (Auth::check()) {
            return redirect($redirect ?: route('home'));
        }

        return view('client.pages.login', [
            'redirect' => $redirect,
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
            $user = Auth::user();
            if ($user && $user->isLocked()) {
                $reason = $user->lock_reason ?: 'Tài khoản của bạn đã bị khóa tạm thời. Vui lòng liên hệ ban quản trị.';
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withInput($request->only('email', 'redirect'))->withErrors([
                    'email' => "Tài khoản của bạn đã bị khóa. Lý do: {$reason}",
                ]);
            }

            $this->syncOwnedInvitations($request, $user);

            // Xác định URL đích: ưu tiên input redirect từ form, nếu không có lấy từ session url.intended
            $intended = $request->input('redirect') ?: (string) $request->session()->pull('url.intended');
            $safeRedirect = $this->resolveSafeRedirect($intended, route('home'));

            $request->session()->regenerate();

            return redirect($safeRedirect)->with('success', 'Đăng nhập thành công! Chào mừng bạn quay trở lại.');
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
        $redirect = $this->determineRedirectUrl($request);

        if (Auth::check()) {
            return redirect($redirect ?: route('home'));
        }

        return view('client.pages.register', [
            'redirect' => $redirect,
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
        $this->syncOwnedInvitations($request, $user);

        // Xác định URL đích khi đăng ký: ưu tiên input redirect, nếu không có fallback về onboarding
        $intended = $request->input('redirect') ?: (string) $request->session()->pull('url.intended');
        $safeRedirect = $this->resolveSafeRedirect($intended, route('onboarding.index'));

        $request->session()->regenerate();

        return redirect($safeRedirect)->with('success', 'Đăng ký tài khoản thành công! Bắt đầu tạo thiệp cưới của bạn ngay nhé.');
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

    /**
     * Xác định URL chuyển hướng ban đầu một cách an toàn và thông minh:
     * 1. Query parameter ?redirect=...
     * 2. Session 'url.intended' (do Laravel Middleware auth hoặc redirect()->guest() ghi nhận)
     * 3. URL trang trước đó (url()->previous()) nếu là trang nội bộ hợp lệ
     */
    protected function determineRedirectUrl(Request $request): ?string
    {
        // 1. Kiểm tra query param 'redirect'
        $queryRedirect = $request->query('redirect');
        if (! empty($queryRedirect)) {
            $safe = $this->resolveSafeRedirect($queryRedirect, '');
            if (! empty($safe)) {
                $request->session()->put('url.intended', $safe);

                return $safe;
            }
        }

        // 2. Kiểm tra session 'url.intended'
        if ($request->session()->has('url.intended')) {
            $intended = (string) $request->session()->get('url.intended');
            $safe = $this->resolveSafeRedirect($intended, '');
            if (! empty($safe)) {
                return $safe;
            }
        }

        // 3. Kiểm tra trang trước đó (url()->previous())
        $previousUrl = url()->previous();
        if (! empty($previousUrl) && $previousUrl !== $request->fullUrl()) {
            $safe = $this->resolveSafeRedirect($previousUrl, '');
            if (! empty($safe)) {
                $request->session()->put('url.intended', $safe);

                return $safe;
            }
        }

        return null;
    }

    /**
     * Giải quyết đường dẫn redirect an toàn, chống Open Redirect (AUTH-019)
     * và ngăn chặn chuyển hướng vòng lặp về chính các trang auth.
     */
    protected function resolveSafeRedirect(?string $redirectUrl, string $fallback): string
    {
        if (empty($redirectUrl)) {
            return $fallback;
        }

        $redirectUrl = trim($redirectUrl);

        // Safe relative path: bắt đầu bằng / nhưng không phải // hoặc /\
        if (str_starts_with($redirectUrl, '/') && ! str_starts_with($redirectUrl, '//') && ! str_contains($redirectUrl, '\\')) {
            if ($this->isAuthUrl($redirectUrl)) {
                return $fallback;
            }

            return $redirectUrl;
        }

        // Safe absolute URL trong cùng ứng dụng
        if (filter_var($redirectUrl, FILTER_VALIDATE_URL) && str_starts_with($redirectUrl, url('/'))) {
            if ($this->isAuthUrl($redirectUrl)) {
                return $fallback;
            }

            return $redirectUrl;
        }

        return $fallback;
    }

    /**
     * Kiểm tra xem đường dẫn có phải là trang auth nội bộ (tránh redirect vòng lặp vô tận).
     */
    protected function isAuthUrl(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;
        $path = rtrim($path, '/');

        $authPaths = [
            '/dang-nhap',
            '/dang-ky',
            '/dang-xuat',
            '/quen-mat-khau',
            '/dat-lai-mat-khau',
            '/login',
            '/register',
            '/logout',
        ];

        foreach ($authPaths as $authPath) {
            if ($path === $authPath || str_starts_with($path, $authPath.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Đồng bộ các thiệp cưới được tạo lúc chưa đăng nhập sang tài khoản người dùng vừa đăng nhập.
     */
    protected function syncOwnedInvitations(Request $request, User $user): void
    {
        $ownedSlugs = (array) $request->session()->get('owned_invitation_slugs', []);
        if (! empty($ownedSlugs)) {
            $invitations = Invitation::with('wedding')
                ->whereIn('slug', $ownedSlugs)
                ->get();

            $guestUser = User::where('email', 'guest@mayhen.vn')->first();
            $guestUserId = $guestUser ? (int) $guestUser->id : null;

            foreach ($invitations as $invitation) {
                // AUTH-013 & AUTH-014: Chỉ chuyển quyền sở hữu nếu thiệp chưa có chủ (user_id null hoặc thuộc về tài khoản tạm guest@mayhen.vn)
                // Tuyệt đối không chiếm đoạt thiệp đã thuộc về tài khoản cá nhân khác
                if ($invitation->wedding) {
                    $currentOwnerId = (int) $invitation->wedding->user_id;
                    if ($currentOwnerId === 0 || ($guestUserId !== null && $currentOwnerId === $guestUserId)) {
                        $invitation->wedding->update(['user_id' => $user->id]);
                    }
                }
            }
        }
    }
}
