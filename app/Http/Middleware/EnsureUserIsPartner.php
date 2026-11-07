<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPartner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->guest(route('login'))->with('error', 'Vui lòng đăng nhập để truy cập Cổng Đối Tác Studio.');
        }

        $user = auth()->user();

        // Quản trị viên hệ thống có thể truy cập cổng đối tác để hỗ trợ
        if ($user->isAdmin()) {
            return $next($request);
        }

        $partner = $user->partner;

        if (! $partner) {
            return redirect()->route('partner.register')->with('warning', 'Vui lòng hoàn tất đăng ký thông tin Studio / Đối tác trước khi truy cập.');
        }

        if ($partner->isSuspended()) {
            abort(403, 'Tài khoản đối tác Studio của bạn đang tạm thời bị khóa. Vui lòng liên hệ ban quản trị Mây Hẹn (hotline: 0966 688 888) để được hỗ trợ.');
        }

        return $next($request);
    }
}
