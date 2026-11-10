<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('admin.login'));
        }

        $user = Auth::user();
        if (! $user || ! method_exists($user, 'canAccessAdminPanel') || ! $user->canAccessAdminPanel()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Admin privileges required.'], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Tài khoản của bạn không có quyền truy cập không gian quản trị.']);
        }

        if (method_exists($user, 'isLocked') && $user->isLocked()) {
            $reason = $user->lock_reason ?: 'Tài khoản đã bị tạm khóa bởi Quản trị viên.';
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => $reason], 403);
            }

            return redirect()->route('admin.login')
                ->withErrors(['email' => "Tài khoản của bạn đã bị khóa. Lý do: {$reason}"]);
        }

        return $next($request);
    }
}
