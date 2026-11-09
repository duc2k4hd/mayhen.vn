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

        // 5. Cấp quyền camera cho chính trang web (Phục vụ máy quét QR điểm danh), khóa micro & vị trí
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');

        // 6. Kích hoạt Strict-Transport-Security (HSTS) trên môi trường HTTPS
        if ($request->isSecure() || config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
