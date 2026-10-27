<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Rate limiter thông minh cho khách mời (lời chúc, RSVP):
        // Phân biệt theo Session ID hoặc IP, giúp 1000 khách chung WiFi nhà hàng tiệc cưới không bị chặn chéo lẫn nhau
        RateLimiter::for('guest_interactions', function (Request $request) {
            $key = $request->session()->getId() ?: $request->ip();

            return Limit::perMinute(60)->by($key);
        });

        // Rate limiter cho đăng nhập / đăng ký (chống Brute-force theo Email + IP)
        RateLimiter::for('auth_attempts', function (Request $request) {
            $key = (string) ($request->input('email', '').'|'.$request->ip());

            return Limit::perMinute(15)->by($key);
        });

        // Rate limiter cho thao tác phòng thu / biên tập thiệp cưới
        RateLimiter::for('editor_actions', function (Request $request) {
            $key = (string) ($request->user()?->id ?: $request->session()->getId() ?: $request->ip());

            return Limit::perMinute(120)->by($key);
        });
    }
}
