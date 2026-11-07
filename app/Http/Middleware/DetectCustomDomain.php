<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Client\CheckInController;
use App\Http\Controllers\Client\InvitationEditorController;
use App\Http\Controllers\Client\LiveWallController;
use App\Http\Controllers\Client\MemoryVaultController;
use App\Http\Controllers\Client\PhotoDropController;
use App\Http\Controllers\Client\TimeCapsuleController;
use App\Models\WeddingDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectCustomDomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        // 1. Kiểm tra host chính của hệ thống Mây Hẹn (không can thiệp)
        $mainDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'mayhen.vn';
        $systemHosts = array_unique([
            $mainDomain,
            'www.'.$mainDomain,
            'mayhen.vn',
            'www.mayhen.vn',
            'localhost',
            '127.0.0.1',
        ]);

        if (in_array($host, $systemHosts, true)) {
            return $next($request);
        }

        // 2. Tra cứu theo Subdomain (ví dụ: tuanlan.mayhen.vn)
        $subdomain = null;
        $systemBaseDomains = array_unique(array_filter([
            'mayhen.vn',
            ! in_array($mainDomain, ['localhost', '127.0.0.1'], true) ? $mainDomain : null,
        ]));

        foreach ($systemBaseDomains as $sysBase) {
            $suffix = '.'.$sysBase;
            if (str_ends_with($host, $suffix)) {
                $subdomain = substr($host, 0, -strlen($suffix));
                break;
            }
        }

        $matchedDomain = null;

        if ($subdomain !== null) {
            // Bỏ qua nếu là subdomain nội bộ hệ thống
            if (in_array($subdomain, WeddingDomain::BLACKLISTED_SUBDOMAINS, true)) {
                return $next($request);
            }

            $matchedDomain = WeddingDomain::with(['invitation', 'wedding'])
                ->where('subdomain', $subdomain)
                ->where('status', 'active')
                ->first();
        } else {
            // 3. Tra cứu theo Tên Miền Riêng (ví dụ: tuanlan.wedding)
            $matchedDomain = WeddingDomain::with(['invitation', 'wedding'])
                ->where('custom_domain', $host)
                ->where('status', 'active')
                ->first();
        }

        // Nếu là tên miền ngoài không thuộc hệ thống nhưng chưa kích hoạt hoặc không tồn tại
        if (! $matchedDomain || ! $matchedDomain->invitation) {
            if ($subdomain === null) {
                return response()->view('client.pages.domain-pending', [
                    'host' => $host,
                ], 404);
            }

            return $next($request);
        }

        // Gắn thông tin tên miền đã xác thực vào request
        $request->attributes->set('custom_wedding_domain', $matchedDomain);

        // 4. Phục vụ trực tiếp các trang chức năng trên Tên Miền Riêng
        $path = trim($request->path(), '/');

        // Trang chủ '/' -> Phục vụ thiệp cưới công khai
        if ($path === '' || $path === '/') {
            return response(app(InvitationEditorController::class)->show($matchedDomain->invitation->slug));
        }

        // '/ky-niem' -> Két lưu trữ kỷ niệm số
        if ($path === 'ky-niem') {
            return response(app(MemoryVaultController::class)->show($matchedDomain->invitation->slug));
        }

        // '/photo-drop' -> Thu thập ảnh khách chụp
        if ($path === 'photo-drop') {
            return response(app(PhotoDropController::class)->show($matchedDomain->invitation->slug));
        }

        // '/diem-danh-tiec' -> Cổng tiếp tân quét mã
        if ($path === 'diem-danh-tiec') {
            return response(app(CheckInController::class)->showPortal($matchedDomain->invitation->slug));
        }

        // '/trinh-chieu' -> Bức Tường Trình Chiếu Lễ Cưới Trực Tiếp
        if ($path === 'trinh-chieu') {
            return response(app(LiveWallController::class)->show($request, $matchedDomain->invitation->slug));
        }

        // '/hop-thoi-gian' -> Hộp Thời Gian Kỷ Niệm Tương Lai (Wedding Time Capsule)
        if ($path === 'hop-thoi-gian') {
            return response(app(TimeCapsuleController::class)->showPublic($request, $matchedDomain->invitation->slug));
        }

        return $next($request);
    }
}
