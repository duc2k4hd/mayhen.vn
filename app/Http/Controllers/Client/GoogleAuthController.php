<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\AdminNewUserRegisteredMail;
use App\Mail\CustomerWelcomeMail;
use App\Models\Invitation;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\ClientDeviceProtectionService;
use App\Services\GoogleOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class GoogleAuthController extends Controller
{
    public const SESSION_STATE_KEY = 'mayhen_google_oauth_state';

    public const SESSION_VERIFIER_KEY = 'mayhen_google_oauth_verifier';

    public const SESSION_EXPIRES_KEY = 'mayhen_google_oauth_expires_at';

    public const SESSION_REDIRECT_KEY = 'mayhen_google_oauth_redirect';

    public const SESSION_PENDING_KEY = 'mayhen_google_pending_registration';

    public function __construct(
        protected GoogleOAuthService $googleOAuth,
        protected ClientDeviceProtectionService $deviceProtection
    ) {}

    /**
     * Bước 1: Chuyển hướng sang Google OAuth 2.0 với State 256-bit + PKCE S256.
     */
    public function redirect(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('account.dashboard');
        }

        if (! $this->googleOAuth->isConfigured()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Tính năng đăng nhập bằng Google hiện chưa sẵn sàng. Vui lòng đăng nhập bằng email hoặc thử lại sau.',
            ]);
        }

        $state = bin2hex(random_bytes(32));
        $pkce = $this->googleOAuth->generatePkcePair();
        $redirect = $this->resolveSafeRedirect(
            (string) ($request->query('redirect') ?: $request->session()->get('url.intended', '')),
            ''
        );

        $request->session()->put([
            self::SESSION_STATE_KEY => $state,
            self::SESSION_VERIFIER_KEY => $pkce['verifier'],
            self::SESSION_EXPIRES_KEY => now()->addMinutes(10)->timestamp,
            self::SESSION_REDIRECT_KEY => $redirect,
        ]);

        return redirect()->away($this->googleOAuth->buildAuthorizationUrl($state, $pkce['challenge']));
    }

    /**
     * Bước 2: Nhận callback từ Google, kiểm tra State + PKCE, đăng nhập hoặc chuyển sang bước nhập thông tin & mật khẩu.
     */
    public function callback(Request $request): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull(self::SESSION_STATE_KEY, '');
        $codeVerifier = (string) $request->session()->pull(self::SESSION_VERIFIER_KEY, '');
        $expiresAt = (int) $request->session()->pull(self::SESSION_EXPIRES_KEY, 0);
        $intendedRedirect = (string) $request->session()->pull(self::SESSION_REDIRECT_KEY, '');

        if ($request->filled('error')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Bạn đã hủy đăng nhập bằng Google.',
            ]);
        }

        $returnedState = (string) $request->query('state', '');
        $code = (string) $request->query('code', '');

        if (
            $expectedState === ''
            || $codeVerifier === ''
            || $code === ''
            || $expiresAt < now()->timestamp
            || ! hash_equals($expectedState, $returnedState)
        ) {
            return redirect()->route('login')->withErrors([
                'email' => 'Phiên xác thực Google không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.',
            ]);
        }

        try {
            $profile = $this->googleOAuth->fetchVerifiedUserProfile($code, $codeVerifier);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->withErrors([
                'email' => 'Không thể xác thực tài khoản Google lúc này. Vui lòng thử lại sau.',
            ]);
        }

        // Tìm tài khoản đã tồn tại theo google_id hoặc email đã xác thực
        $user = User::query()
            ->where('google_id', $profile['google_id'])
            ->first();

        if (! $user) {
            $user = User::query()->where('email', $profile['email'])->first();
        }

        if ($user) {
            if ($user->isLocked()) {
                $reason = $user->lock_reason ?: 'Tài khoản của bạn đã bị khóa tạm thời. Vui lòng liên hệ ban quản trị.';

                return redirect()->route('login')->withErrors([
                    'email' => "Tài khoản của bạn đã bị khóa. Lý do: {$reason}",
                ]);
            }

            // Bảo vệ tài khoản quản trị: không tự động liên kết Google theo email nếu chưa gắn google_id
            if ($user->google_id === null && $user->canAccessAdminPanel()) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Tài khoản quản trị vui lòng đăng nhập bằng mật khẩu tại cổng quản trị.',
                ]);
            }

            // Chống xung đột định danh Google ID
            if ($user->google_id !== null && ! hash_equals($user->google_id, $profile['google_id'])) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Email này đã được liên kết với một tài khoản Google khác.',
                ]);
            }

            $updates = [];
            if ($user->google_id === null) {
                $updates['google_id'] = $profile['google_id'];
            }
            if ($user->email_verified_at === null) {
                $updates['email_verified_at'] = now();
            }
            if (empty($user->avatar) && $profile['avatar']) {
                $updates['avatar'] = $profile['avatar'];
            }
            if (! empty($updates)) {
                $user->forceFill($updates)->save();
            }

            Auth::login($user, true);
            $this->syncOwnedInvitations($request, $user);

            $safeRedirect = $this->resolveSafeRedirect(
                $intendedRedirect ?: (string) $request->session()->pull('url.intended', ''),
                route('account.dashboard')
            );
            $request->session()->regenerate();

            return redirect($safeRedirect)->with('success', 'Đăng nhập bằng Google thành công! Chào mừng bạn quay trở lại.');
        }

        // Người dùng Google lần đầu: lưu phiên tạm trong server-side Session (15 phút) và chuyển sang nhập thông tin cơ bản + mật khẩu
        $request->session()->put(self::SESSION_PENDING_KEY, [
            'google_id' => $profile['google_id'],
            'email' => $profile['email'],
            'name' => $profile['name'],
            'avatar' => $profile['avatar'],
            'redirect' => $intendedRedirect,
            'expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        return redirect()->route('auth.google.complete');
    }

    /**
     * Bước 3: Hiển thị form nhập thông tin cơ bản & mật khẩu cho người dùng Google mới.
     */
    public function showCompleteForm(Request $request): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('account.dashboard');
        }

        $pending = $this->getValidPendingSession($request);
        if (! $pending) {
            return redirect()->route('login')->withErrors([
                'email' => 'Phiên hoàn tất đăng ký Google đã hết hạn. Vui lòng đăng nhập lại bằng Google.',
            ]);
        }

        return response()
            ->view('client.pages.auth-complete-profile', [
                'mode' => 'google',
                'verifiedEmail' => $pending['email'],
                'defaultName' => $pending['name'],
                'formAction' => route('auth.google.complete.submit'),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Referrer-Policy', 'no-referrer');
    }

    /**
     * Bước 4: Hoàn tất tạo tài khoản từ Google kèm thông tin cơ bản & mật khẩu (để lần sau đăng nhập được cả 2 cách).
     */
    public function completeRegistration(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('account.dashboard');
        }

        $machineId = $this->deviceProtection->resolveMachineId($request);

        // Kiểm tra giới hạn máy / thiết bị: Nếu đã tạo >= 5 tài khoản thì cấm trong vòng 7 ngày
        if ($this->deviceProtection->isMachineBanned($machineId) || ! $this->deviceProtection->canRegister($machineId)) {
            $banExpiresAt = $this->deviceProtection->getBanExpiresAt($machineId);
            $timeMsg = $banExpiresAt ? ' đến '.$banExpiresAt->format('H:i d/m/Y') : ' trong vòng 7 ngày';

            return redirect()->route('register')->withErrors([
                'email' => 'Thiết bị này đã vượt quá giới hạn đăng ký (tối đa '.ClientDeviceProtectionService::MAX_ACCOUNTS_PER_DEVICE." tài khoản). Tạm thời không thể đăng ký mới{$timeMsg}. Vui lòng đăng nhập hoặc liên hệ hỗ trợ!",
            ])->withCookie($this->deviceProtection->makeCookie($machineId));
        }

        $pending = $this->getValidPendingSession($request);
        if (! $pending) {
            return redirect()->route('login')->withErrors([
                'email' => 'Phiên hoàn tất đăng ký Google đã hết hạn. Vui lòng đăng nhập lại bằng Google.',
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s().]{8,20}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'phone.regex' => 'Số điện thoại không đúng định dạng hợp lệ.',
            'password.required' => 'Vui lòng thiết lập mật khẩu đăng nhập.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu phải chứa ít nhất 8 ký tự gồm cả chữ và số.',
        ]);

        $user = DB::transaction(function () use ($pending, $validated): ?User {
            $exists = User::query()
                ->where('email', $pending['email'])
                ->orWhere('google_id', $pending['google_id'])
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                return null;
            }

            return User::create([
                'name' => trim(strip_tags($validated['name'])),
                'email' => $pending['email'],
                'google_id' => $pending['google_id'],
                'phone' => isset($validated['phone']) && trim((string) $validated['phone']) !== '' ? trim((string) $validated['phone']) : null,
                'avatar' => $pending['avatar'] ?? null,
                'email_verified_at' => now(),
                'password' => Hash::make($validated['password']),
                'role' => 'customer',
            ]);
        });

        $request->session()->forget(self::SESSION_PENDING_KEY);

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Tài khoản với email này đã tồn tại. Vui lòng đăng nhập.',
            ]);
        }

        Auth::login($user, true);
        $this->syncOwnedInvitations($request, $user);

        // Ghi nhận 1 tài khoản đăng ký thành công cho thiết bị (tự động ban 7 ngày nếu đạt >= 5 tài khoản)
        $this->deviceProtection->recordSuccessfulRegistration($machineId, $user->id);

        // Gửi email chào mừng & thông báo Admin có người dùng mới đăng ký qua Google (100% Queue bất đồng bộ từ no-reply)
        PlatformSetting::applyMailRuntimeConfiguration();
        try {
            Mail::to($user->email)->queue(new CustomerWelcomeMail($user));
        } catch (\Throwable $e) {
            Log::warning('Không thể đưa email chào mừng đăng ký Google vào hàng đợi: '.$e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        try {
            $adminEmail = trim(PlatformSetting::getAdminEmail());
            if ($adminEmail !== '') {
                Mail::to($adminEmail)->queue(new AdminNewUserRegisteredMail($user, 'google', $request->ip()));
            }
        } catch (\Throwable $e) {
            Log::warning('Không thể đưa email thông báo Admin người dùng mới đăng ký Google vào hàng đợi: '.$e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        $safeRedirect = $this->resolveSafeRedirect(
            (string) ($pending['redirect'] ?? ''),
            route('onboarding.index')
        );
        $request->session()->regenerate();

        return redirect($safeRedirect)
            ->with('success', 'Hoàn tất đăng ký thành công! Lần sau bạn có thể đăng nhập bằng Google hoặc Email & mật khẩu.')
            ->withCookie($this->deviceProtection->makeCookie($machineId));
    }

    /**
     * @return array{google_id: string, email: string, name: string, avatar: ?string, redirect: string, expires_at: int}|null
     */
    protected function getValidPendingSession(Request $request): ?array
    {
        $pending = $request->session()->get(self::SESSION_PENDING_KEY);
        if (
            ! is_array($pending)
            || empty($pending['google_id'])
            || empty($pending['email'])
            || (int) ($pending['expires_at'] ?? 0) < now()->timestamp
        ) {
            $request->session()->forget(self::SESSION_PENDING_KEY);

            return null;
        }

        if (User::query()->where('email', $pending['email'])->orWhere('google_id', $pending['google_id'])->exists()) {
            $request->session()->forget(self::SESSION_PENDING_KEY);

            return null;
        }

        return $pending;
    }

    protected function syncOwnedInvitations(Request $request, User $user): void
    {
        $ownedSlugs = (array) $request->session()->get('owned_invitation_slugs', []);
        if (empty($ownedSlugs)) {
            return;
        }

        $invitations = Invitation::withTrashed()
            ->with('wedding')
            ->whereIn('slug', $ownedSlugs)
            ->get();

        $guestUser = User::where('email', 'guest@mayhen.vn')->first();
        $guestUserId = $guestUser ? (int) $guestUser->id : null;

        foreach ($invitations as $invitation) {
            if ($invitation->wedding) {
                $currentOwnerId = (int) $invitation->wedding->user_id;
                if ($currentOwnerId === 0 || ($guestUserId !== null && $currentOwnerId === $guestUserId)) {
                    $invitation->wedding->update(['user_id' => $user->id]);
                    $invitation->forceFill(['owner_user_id' => $user->id])->saveQuietly();
                }
            }
        }
    }

    protected function resolveSafeRedirect(?string $url, string $default): string
    {
        if (empty($url)) {
            return $default;
        }

        $parsed = parse_url($url);
        if (! $parsed) {
            return $default;
        }

        if (isset($parsed['host'])) {
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            $requestHost = request()->getHost();
            if ($parsed['host'] !== $appHost && $parsed['host'] !== $requestHost) {
                return $default;
            }
        }

        $path = $parsed['path'] ?? '/';
        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return $default;
        }

        $blockedPaths = ['/dang-nhap', '/dang-ky', '/dang-xuat', '/auth/google'];
        foreach ($blockedPaths as $blocked) {
            if (str_starts_with($path, $blocked)) {
                return $default;
            }
        }

        return $url;
    }
}
