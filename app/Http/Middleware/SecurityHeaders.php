<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach modern security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 1. Chống MIME-Type Sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 2. Chống Clickjacking nhưng cho phép iframe nội bộ của chính mayhen.vn (Preview / Studio Canvas)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 3. Bảo vệ XSS trình duyệt kế thừa
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 4. Referrer-Policy chuẩn khuyến nghị của Google (Bảo mật + Tối ưu 100% cho SEO & Analytics)
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 5. Khóa quyền truy cập phần cứng nhạy cảm
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
