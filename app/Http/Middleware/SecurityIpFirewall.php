<?php

namespace App\Http\Middleware;

use App\Models\SecurityIpRule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityIpFirewall
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();

        // 1. Kiểm tra nếu IP thuộc danh sách Tin cậy (Whitelist) thì bỏ qua kiểm tra
        $isWhitelisted = SecurityIpRule::whitelist()
            ->active()
            ->where('ip_address', $clientIp)
            ->exists();

        if ($isWhitelisted) {
            return $next($request);
        }

        // 2. Kiểm tra nếu IP nằm trong Sổ đen (Blacklist) đang kích hoạt
        $blockedRule = SecurityIpRule::blacklist()
            ->active()
            ->where('ip_address', $clientIp)
            ->first();

        if ($blockedRule) {
            $blockedRule->recordHit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'IP_BLOCKED',
                    'message' => 'Địa chỉ IP của bạn tạm thời bị từ chối truy cập do vi phạm chính sách an ninh hệ thống.',
                    'reason' => $blockedRule->reason,
                ], 403);
            }

            return response()->view('errors.403', [
                'exception' => new \Exception("Địa chỉ IP ({$clientIp}) của bạn đang bị giới hạn truy cập vì: {$blockedRule->reason}."),
            ], 403);
        }

        return $next($request);
    }
}

