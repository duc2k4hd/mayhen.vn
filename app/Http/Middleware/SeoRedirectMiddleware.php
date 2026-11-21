<?php

namespace App\Http\Middleware;

use App\Services\SeoService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SeoRedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bỏ qua trang chủ gốc, các đường dẫn nội bộ, quản trị, asset mẫu thiệp, api, trung tâm docs tĩnh và sitemap
        if ($request->path() === '/' || $request->is('admin*', 'assets*', 'storage*', 'api*', '_debugbar*', 'docs*', 'sitemap*', 'robots.txt', 'favicon.ico', 'up', 'mau-thiep/*/assets/*')) {
            return $next($request);
        }

        // Bỏ qua nếu là request POST, PUT, DELETE
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        try {
            $redirect = SeoService::matchRedirect($request->getRequestUri());

            if ($redirect) {
                SeoService::recordHit($redirect);

                $target = $redirect->target_url;

                return redirect($target, $redirect->status_code);
            }
        } catch (\Throwable $e) {
            // Phòng vệ nếu DB chưa sẵn sàng hoặc trong quá trình setup ban đầu
        }

        return $next($request);
    }
}
