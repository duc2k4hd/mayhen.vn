<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! method_exists($user, 'isSuperAdmin') || ! $user->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Chỉ Quản trị viên cấp cao mới có quyền thực hiện thao tác này.',
                ], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('error', 'Chỉ Quản trị viên cấp cao mới có quyền thực hiện thao tác này.');
        }

        return $next($request);
    }
}
