<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\WeddingWish;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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

        // Rate limiter bảo vệ tải tệp ảnh lên hệ thống (Chống DoS lưu trữ)
        RateLimiter::for('upload_actions', function (Request $request) {
            $key = (string) ($request->user()?->id ?: $request->ip());

            return Limit::perMinute(30)->by($key);
        });

        // View Composer cho Admin Shell Master Layout (Badge dữ liệu thời gian thực)
        View::composer('admin.layouts.master', function ($view) {
            if (Auth::check() && method_exists(Auth::user(), 'canAccessAdminPanel') && Auth::user()->canAccessAdminPanel()) {
                $pendingOrders = Order::where('status', 'pending')->count();
                $pendingWishes = WeddingWish::where('is_approved', false)->count();

                $view->with('pendingOrdersBadge', $pendingOrders);
                $view->with('pendingWishesBadge', $pendingWishes);
            }
        });
    }
}
