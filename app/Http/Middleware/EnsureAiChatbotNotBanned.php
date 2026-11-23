<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Chatbot\AiChatbotAbuseGuardService;
use App\Services\ClientDeviceProtectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAiChatbotNotBanned
{
    public function __construct(
        protected AiChatbotAbuseGuardService $abuseGuard,
        protected ClientDeviceProtectionService $deviceProtection
    ) {}

    /**
     * Chặn đứng lập tức ở Backend nếu thiết bị đã bị tạm dừng 24h (chống DevTools bypass, tiêu tốn 0 token).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $identifier = $this->abuseGuard->resolveIdentifier($request);
        $banInfo = $this->abuseGuard->getBanInfo($identifier['device_key']);

        if ($banInfo !== null) {
            $response = $this->abuseGuard->makeBanResponse($banInfo);

            return $this->deviceProtection->attachCookieToResponse($response, $identifier['machine_id']);
        }

        $response = $next($request);

        // Đảm bảo máy client luôn có Cookie Machine ID hợp lệ để định danh thiết bị độc lập
        return $this->deviceProtection->attachCookieToResponse($response, $identifier['machine_id']);
    }
}
