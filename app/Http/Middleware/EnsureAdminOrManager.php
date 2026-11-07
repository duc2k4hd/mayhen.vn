<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrManager
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Thao tác này yêu cầu quyền Quản lý hoặc Quản trị viên.',
                ], 403);
            }

            return back()->with('error', 'Tài khoản của bạn có quyền Nhân viên, không được phép thực hiện thao tác nhạy cảm này.');
        }

        return $next($request);
    }
}

