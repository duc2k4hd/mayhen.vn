<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\EmailRegistrationVerificationMail;
use App\Models\EmailRegistrationToken;
use App\Models\Invitation;
use App\Models\User;
use App\Services\GoogleOAuthService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Danh sách tên miền email rác dùng 1 lần bị chặn khi đăng ký tài khoản.
     *
     * @var list<string>
     */
    protected const DISPOSABLE_EMAIL_DOMAINS = [
        '10minutemail.com',
        '10minutemail.net',
        'dispostable.com',
        'fakeinbox.com',
        'getnada.com',
        'guerrillamail.com',
        'guerrillamailblock.com',
        'maildrop.cc',
        'mailinator.com',
        'mohmal.com',
        'sharklasers.com',
        'temp-mail.org',
        'tempmail.com',
        'tempmail.net',
        'throwawaymail.com',
        'trashmail.com',
        'yopmail.com',
        'yopmail.fr',
        'yopmail.net',
    ];

    public function __construct(
        protected GoogleOAuthService $googleOAuth
    ) {}

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
            'googleEnabled' => $this->googleOAuth->isConfigured(),
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
     * Hiển thị trang đăng ký bằng Email xác thực hoặc Google (không cho tạo tài khoản trực tiếp tránh email rác).
     */
    public function showRegisterForm(Request $request): View|RedirectResponse
    {
        $redirect = $this->determineRedirectUrl($request);

        if (Auth::check()) {
            return redirect($redirect ?: route('home'));
        }

        return view('client.pages.register', [
            'redirect' => $redirect,
            'googleEnabled' => $this->googleOAuth->isConfigured(),
        ]);
    }

    /**
     * Bước 1 Đăng ký bằng Email: Gửi liên kết chứa token 256-bit (chỉ lưu hash SHA-256, dùng 1 lần, hạn 24h)
     * qua hòm thư No-Reply đã cấu hình trong Admin.
     * Phòng vệ: Chống email rác dùng 1 lần, chống Account Enumeration và chống Timing Attack.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email:filter|max:255',
            'redirect' => 'nullable|string|max:500',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email của bạn.',
            'email.email' => 'Địa chỉ email không đúng định dạng hợp lệ.',
        ]);

        $email = strtolower(trim($validated['email']));
        $domain = strtolower((string) substr(strrchr($email, '@') ?: '', 1));

        if ($domain === '' || in_array($domain, self::DISPOSABLE_EMAIL_DOMAINS, true)) {
            return back()->withInput($request->only('email', 'redirect'))->withErrors([
                'email' => 'Vui lòng sử dụng địa chỉ email cá nhân thật (không hỗ trợ email tạm thời/email rác).',
            ]);
        }

        $safeRedirect = $this->resolveSafeRedirect(
            (string) ($request->input('redirect') ?: $request->session()->get('url.intended', '')),
            ''
        );

        $alreadyRegistered = User::query()->where('email', $email)->exists();

        if (! $alreadyRegistered) {
            // Chống spam gửi liên tục trong vòng 60 giây cho cùng một địa chỉ email
            $recentToken = EmailRegistrationToken::query()
                ->where('email', $email)
                ->valid()
                ->latest('id')
                ->first();

            $withinCooldown = $recentToken
                && $recentToken->created_at
                && $recentToken->created_at->diffInSeconds(now()) < EmailRegistrationToken::RESEND_COOLDOWN_SECONDS;

            if (! $withinCooldown) {
                [$rawToken, $tokenHash] = EmailRegistrationToken::generateTokenPair();

                DB::transaction(function () use ($email, $tokenHash, $safeRedirect, $request): void {
                    // Vô hiệu hóa toàn bộ token chưa dùng trước đó để luôn chỉ có 1 link hợp lệ duy nhất
                    EmailRegistrationToken::query()
                        ->where('email', $email)
                        ->whereNull('used_at')
                        ->delete();

                    EmailRegistrationToken::create([
                        'email' => $email,
                        'token_hash' => $tokenHash,
                        'redirect_to' => $safeRedirect !== '' ? $safeRedirect : null,
                        'ip_address' => $request->ip(),
                        'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                        'expires_at' => now()->addHours(EmailRegistrationToken::TTL_HOURS),
                    ]);
                });

                $verificationUrl = route('register.verify', ['token' => $rawToken]);

                try {
                    Mail::to($email)->queue(
                        new EmailRegistrationVerificationMail($email, $verificationUrl, EmailRegistrationToken::TTL_HOURS)
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        } else {
            // Chống Timing Attack & Account Enumeration: đồng bộ độ trễ với nhánh gửi mail
            usleep(random_int(40000, 80000));
        }

        return back()->with('registration_link_sent', true)
            ->with('sent_email', $email)
            ->with('status', 'Nếu địa chỉ email hợp lệ và chưa có tài khoản, chúng mình đã gửi liên kết xác thực đăng ký đến hộp thư của bạn. Liên kết có hiệu lực trong 24 giờ và chỉ sử dụng 1 lần.');
    }

    /**
     * Bước 2 Đăng ký bằng Email: Xác thực token 1 lần từ link email và hiển thị form nhập thông tin cơ bản + mật khẩu.
     */
    public function showCompleteEmailRegistration(Request $request, string $token): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $record = $this->findValidRegistrationToken($token);
        if (! $record) {
            return redirect()->route('register')->withErrors([
                'email' => 'Liên kết xác thực không hợp lệ, đã được sử dụng hoặc đã hết hạn 24 giờ. Vui lòng yêu cầu gửi liên kết mới.',
            ]);
        }

        return response()
            ->view('client.pages.auth-complete-profile', [
                'mode' => 'email',
                'verifiedEmail' => $record->email,
                'defaultName' => '',
                'formAction' => route('register.verify.submit', ['token' => $token]),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Referrer-Policy', 'no-referrer');
    }

    /**
     * Bước 3 Đăng ký bằng Email: Tạo tài khoản đã xác thực email trong transaction kèm lockForUpdate (chống dùng lại token 100%).
     */
    public function completeEmailRegistration(Request $request, string $token): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        if (! preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            return redirect()->route('register')->withErrors([
                'email' => 'Liên kết xác thực không hợp lệ hoặc đã hết hạn 24 giờ.',
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

        $tokenHash = EmailRegistrationToken::hashToken($token);
        $redirectTo = '';

        $user = DB::transaction(function () use ($tokenHash, $validated, &$redirectTo): ?User {
            $record = EmailRegistrationToken::query()
                ->where('token_hash', $tokenHash)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->first();

            if (! $record) {
                return null;
            }

            if (User::query()->where('email', $record->email)->lockForUpdate()->exists()) {
                $record->forceFill(['used_at' => now()])->save();

                return null;
            }

            $redirectTo = (string) ($record->redirect_to ?? '');
            $record->forceFill(['used_at' => now()])->save();

            // Dọn sạch các token cũ khác của cùng email (nếu có)
            EmailRegistrationToken::query()
                ->where('email', $record->email)
                ->where('id', '!=', $record->id)
                ->delete();

            return User::create([
                'name' => trim(strip_tags($validated['name'])),
                'email' => $record->email,
                'phone' => isset($validated['phone']) && trim((string) $validated['phone']) !== '' ? trim((string) $validated['phone']) : null,
                'email_verified_at' => now(),
                'password' => Hash::make($validated['password']),
                'role' => 'customer',
            ]);
        });

        if (! $user) {
            return redirect()->route('register')->withErrors([
                'email' => 'Liên kết xác thực không hợp lệ, đã được sử dụng hoặc đã hết hạn 24 giờ. Vui lòng yêu cầu gửi liên kết mới.',
            ]);
        }

        Auth::login($user, true);
        $this->syncOwnedInvitations($request, $user);

        $safeRedirect = $this->resolveSafeRedirect(
            $redirectTo ?: (string) $request->session()->pull('url.intended', ''),
            route('onboarding.index')
        );
        $request->session()->regenerate();

        return redirect($safeRedirect)->with('success', 'Đăng ký tài khoản thành công! Lần sau bạn có thể đăng nhập bằng Email & mật khẩu hoặc Google.');
    }

    protected function findValidRegistrationToken(string $rawToken): ?EmailRegistrationToken
    {
        if (! preg_match('/\A[a-f0-9]{64}\z/', $rawToken)) {
            return null;
        }

        $record = EmailRegistrationToken::query()
            ->valid()
            ->where('token_hash', EmailRegistrationToken::hashToken($rawToken))
            ->first();

        if (! $record) {
            return null;
        }

        if (User::query()->where('email', $record->email)->exists()) {
            return null;
        }

        return $record;
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
            '/auth/google',
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
