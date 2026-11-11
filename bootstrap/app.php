<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\DetectCustomDomain;
use App\Http\Middleware\EnsureAdminOrManager;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureUserIsPartner;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SeoRedirectMiddleware;
use App\Http\Middleware\SupportImpersonationMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SeoRedirectMiddleware::class);

        $middleware->web(append: [
            SecurityHeaders::class,
            DetectCustomDomain::class,
            SupportImpersonationMiddleware::class,
        ]);

        $middleware->alias([
            'admin.auth' => AdminAuthenticate::class,
            'admin.manager' => EnsureAdminOrManager::class,
            'admin.super' => EnsureSuperAdmin::class,
            'partner' => EnsureUserIsPartner::class,
        ]);

        $middleware->preventRequestForgery(except: [
            'api/payments/sepay/webhook',
            'api/webhook/payment',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
