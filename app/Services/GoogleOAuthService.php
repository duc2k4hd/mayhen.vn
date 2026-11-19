<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleOAuthService
{
    public const AUTH_ENDPOINT = 'https://accounts.google.com/o/oauth2/v2/auth';

    public const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';

    public const USERINFO_ENDPOINT = 'https://openidconnect.googleapis.com/v1/userinfo';

    public function getClientId(): string
    {
        $fromAdmin = trim((string) PlatformSetting::getValue(PlatformSetting::GROUP_SECURITY, 'google_client_id', ''));
        if ($fromAdmin !== '' && $fromAdmin !== PlatformSetting::MASKED_SECRET) {
            return $fromAdmin;
        }

        return trim((string) config('services.google.client_id', ''));
    }

    public function getClientSecret(): string
    {
        $fromAdmin = trim((string) PlatformSetting::getValue(PlatformSetting::GROUP_SECURITY, 'google_client_secret', ''));
        if ($fromAdmin !== '' && $fromAdmin !== PlatformSetting::MASKED_SECRET) {
            return $fromAdmin;
        }

        return trim((string) config('services.google.client_secret', ''));
    }

    public function getRedirectUri(): string
    {
        $configured = trim((string) config('services.google.redirect', ''));

        return $configured !== '' ? $configured : route('auth.google.callback');
    }

    public function isConfigured(): bool
    {
        return $this->getClientId() !== '' && $this->getClientSecret() !== '';
    }

    /**
     * Sinh cặp PKCE (code_verifier & code_challenge S256) chống đánh cắp authorization code.
     *
     * @return array{verifier: string, challenge: string}
     */
    public function generatePkcePair(): array
    {
        $verifier = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        return [
            'verifier' => $verifier,
            'challenge' => $challenge,
        ];
    }

    public function buildAuthorizationUrl(string $state, string $codeChallenge): string
    {
        $params = [
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
            'prompt' => 'select_account',
            'access_type' => 'online',
        ];

        return self::AUTH_ENDPOINT.'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Đổi authorization code lấy thông tin tài khoản Google đã xác thực email.
     *
     * @return array{google_id: string, email: string, name: string, avatar: ?string}
     */
    public function fetchVerifiedUserProfile(string $code, string $codeVerifier): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Google OAuth chưa được cấu hình.');
        }

        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->timeout(10)
            ->post(self::TOKEN_ENDPOINT, [
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'code' => $code,
                'code_verifier' => $codeVerifier,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->getRedirectUri(),
            ]);

        if (! $tokenResponse->successful()) {
            throw new RuntimeException('Không thể xác thực mã ủy quyền với Google.');
        }

        $accessToken = trim((string) $tokenResponse->json('access_token', ''));
        if ($accessToken === '') {
            throw new RuntimeException('Phản hồi từ Google thiếu access token hợp lệ.');
        }

        $profileResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(10)
            ->get(self::USERINFO_ENDPOINT);

        if (! $profileResponse->successful()) {
            throw new RuntimeException('Không thể lấy thông tin hồ sơ từ Google.');
        }

        $googleId = trim((string) $profileResponse->json('sub', ''));
        $email = strtolower(trim((string) $profileResponse->json('email', '')));
        $emailVerified = $profileResponse->json('email_verified');
        $isVerified = $emailVerified === true || $emailVerified === 'true' || $emailVerified === 1;

        if ($googleId === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $isVerified) {
            throw new RuntimeException('Tài khoản Google chưa xác thực email hợp lệ.');
        }

        $name = mb_substr(trim(strip_tags((string) $profileResponse->json('name', ''))), 0, 100);
        if ($name === '') {
            $name = mb_substr(strstr($email, '@', true) ?: 'Thành viên', 0, 100);
        }

        $rawAvatar = trim((string) $profileResponse->json('picture', ''));
        $avatar = (str_starts_with($rawAvatar, 'https://') && mb_strlen($rawAvatar) <= 500) ? $rawAvatar : null;

        return [
            'google_id' => $googleId,
            'email' => $email,
            'name' => $name,
            'avatar' => $avatar,
        ];
    }
}
