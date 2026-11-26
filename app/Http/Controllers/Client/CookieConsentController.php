<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\UserCookieConsent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class CookieConsentController extends Controller
{
    public const POLICY_VERSION = '2026.1';

    public const COOKIE_NAME = 'mayhen_cookie_consent';

    public function policy(): View
    {
        return view('client.pages.cookie-policy', [
            'policyVersion' => self::POLICY_VERSION,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'analytics' => ['required', 'boolean'],
            'personalization' => ['required', 'boolean'],
            'marketing' => ['nullable', 'boolean'],
            'source_url' => ['nullable', 'string', 'max:300'],
        ]);

        $ip = (string) $request->ip();
        $sessionId = $request->hasSession() ? substr((string) $request->session()->getId(), 0, 100) : 'stateless';

        $consent = UserCookieConsent::create([
            'user_id' => $request->user()?->id,
            'session_id' => $sessionId,
            'ip_address' => $ip,
            'ip_hash' => hash('sha256', $ip.'|'.config('app.key')),
            'user_agent' => substr((string) $request->userAgent(), 0, 300),
            'policy_version' => self::POLICY_VERSION,
            'consent_essential' => true,
            'consent_analytics' => (bool) $validated['analytics'],
            'consent_personalization' => (bool) $validated['personalization'],
            'consent_marketing' => (bool) ($validated['marketing'] ?? false),
            'source_url' => $validated['source_url'] ?? $request->headers->get('referer'),
            'accepted_at' => now(),
        ]);

        $cookiePayload = json_encode([
            'v' => self::POLICY_VERSION,
            'e' => true,
            'a' => $consent->consent_analytics,
            'p' => $consent->consent_personalization,
            'm' => $consent->consent_marketing,
            't' => now()->timestamp,
        ]);

        // Lưu cookie 180 ngày (6 tháng) đồng bộ chính sách lưu trữ dữ liệu
        $cookie = Cookie::make(self::COOKIE_NAME, (string) $cookiePayload, 60 * 24 * 180, '/', null, $request->isSecure(), false, false, 'Lax');

        return response()->json([
            'ok' => true,
            'consent_id' => $consent->id,
            'policy_version' => self::POLICY_VERSION,
        ])->withCookie($cookie);
    }
}
