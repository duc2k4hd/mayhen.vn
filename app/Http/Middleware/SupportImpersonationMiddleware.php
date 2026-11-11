<?php

namespace App\Http\Middleware;

use App\Services\SupportImpersonationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SupportImpersonationMiddleware
{
    /**
     * Danh sách các tên route bị chặn tuyệt đối trong phiên hỗ trợ khách hàng
     * để bảo vệ mật khẩu, thông tin thanh toán và an toàn tài khoản (Mục 17.2 PRD V3).
     */
    protected array $guardedRouteNames = [
        'account.password.update',
        'account.profile.update',
        'account.privacy.erase',
    ];

    public function __construct(
        protected SupportImpersonationService $impersonationService
    ) {}

    /**
     * Xử lý request qua middleware.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Kiểm tra xem có phiên hỗ trợ nhưng đã hết hạn chưa
        if (session()->has(SupportImpersonationService::SESSION_ORIGINAL_ADMIN_ID)) {
            $expiresAt = (int) session()->get(SupportImpersonationService::SESSION_EXPIRES_AT);

            if (now()->timestamp > $expiresAt) {
                $this->impersonationService->stopImpersonation();

                return redirect()->route('admin.dashboard')->with('warning', 'Phiên hỗ trợ khách hàng đã tự động kết thúc do hết thời hạn (30 phút).');
            }
        }

        // 2. Nếu đang trong phiên hỗ trợ còn hiệu lực
        if ($this->impersonationService->isImpersonating()) {
            $currentRouteName = $request->route()?->getName();

            // Chặn đứng các hành vi nhạy cảm (Đổi mật khẩu, đổi email, xóa tài khoản)
            if ($currentRouteName && in_array($currentRouteName, $this->guardedRouteNames, true)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Hành động này bị vô hiệu hóa trong chế độ hỗ trợ khách hàng để bảo vệ quyền riêng tư (Mục 17.2 PRD V3).',
                    ], 403);
                }

                abort(403, 'Hành động này bị vô hiệu hóa trong chế độ hỗ trợ khách hàng để bảo vệ quyền riêng tư (Mục 17.2 PRD V3).');
            }

            // Chia sẻ dữ liệu phiên hỗ trợ cho tất cả Blade Views
            $impersonationSession = $this->impersonationService->getCurrentSession();
            View::share('impersonationSession', $impersonationSession);
        }

        return $next($request);
    }
}

