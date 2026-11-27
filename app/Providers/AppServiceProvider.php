<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\PlatformSetting;
use App\Models\SupportTicket;
use App\Models\WeddingWish;
use App\Services\FeatureFlagService;
use App\Services\Intelligence\Contracts\LlmProviderInterface;
use App\Services\Intelligence\NineRouterProviderAdapter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
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
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }

        $this->app->bind(LlmProviderInterface::class, NineRouterProviderAdapter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::defaultView('client.components.pagination');
        Paginator::defaultSimpleView('client.components.pagination');

        // Đồng bộ tên thương hiệu từ Cài đặt Admin vào config('app.name') (0 DB query khi cache ấm)
        PlatformSetting::captureInitialAppName((string) config('app.name', 'Mây Hẹn'));
        if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
            config(['app.name' => PlatformSetting::getBrandName()]);
        }

        // Chỉ nạp cấu hình SMTP từ Admin khi thực sự cần gửi thư (tránh tốn truy vấn cache thừa trên mọi GET công khai)
        if (
            $this->app->runningInConsole()
            || ! request()->isMethod('GET')
            || request()->is('admin*', 'quen-mat-khau*', 'dang-ky*', 'xac-thuc*')
        ) {
            PlatformSetting::applyMailRuntimeConfiguration();
        }

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

        // Rate limiter cho tìm kiếm tài liệu hướng dẫn (Chống spam API search)
        RateLimiter::for('docs_search', function (Request $request) {
            $key = (string) ($request->user()?->id ?: $request->session()->getId() ?: $request->ip());

            return Limit::perMinute(60)->by($key);
        });

        // Rate limiter cho Trợ Lý Chatbot AI Mây Hẹn (Chống spam & cạn kiệt quota)
        RateLimiter::for('chatbot_messages', function (Request $request) {
            $key = (string) ($request->user()?->id ?: $request->session()->getId() ?: $request->ip());

            return Limit::perMinute(25)->by($key);
        });

        // Rate limiter cho đề xuất huấn luyện AI từ khách hàng (Chống spam hàng đợi duyệt)
        RateLimiter::for('chatbot_training', function (Request $request) {
            $key = (string) ($request->user()?->id ?: $request->session()->getId() ?: $request->ip());

            return Limit::perMinute(6)->by($key);
        });

        // Blade Directives cho Cờ Tính Năng (Feature Flags)
        Blade::if('feature', function (string $key) {
            return app(FeatureFlagService::class)->isEnabled($key);
        });

        // Blade Directives cho Asset Cache-Busting với filemtime (?v=timestamp)
        Blade::directive('asset_v', function ($expression) {
            return "<?php echo asset_v($expression); ?>";
        });
        Blade::directive('assetV', function ($expression) {
            return "<?php echo asset_v($expression); ?>";
        });

        // View Composer cho Admin Shell Master Layout (Badge dữ liệu thời gian thực, bọc an toàn & cache schema)
        View::composer('admin.layouts.master', function ($view) {
            if (Auth::check() && method_exists(Auth::user(), 'canAccessAdminPanel') && Auth::user()->canAccessAdminPanel()) {
                try {
                    $badges = Cache::remember('mayhen_admin_sidebar_badges_v1', 15, static function (): array {
                        $hasTickets = Schema::hasTable('support_tickets');
                        $hasFailedJobs = Schema::hasTable('failed_jobs');
                        $hasAiEntries = Schema::hasTable('ai_knowledge_entries');

                        return [
                            'pendingOrders' => Order::where('status', 'pending')->count(),
                            'pendingWishes' => WeddingWish::where('is_approved', false)->count(),
                            'openTickets' => $hasTickets ? SupportTicket::whereIn('status', ['open', 'in_progress'])->count() : 0,
                            'failedJobs' => $hasFailedJobs ? DB::table('failed_jobs')->count() : 0,
                            'pendingAiTraining' => $hasAiEntries ? DB::table('ai_knowledge_entries')->where('status', 'pending')->count() : 0,
                        ];
                    });
                } catch (\Throwable) {
                    $badges = [
                        'pendingOrders' => 0,
                        'pendingWishes' => 0,
                        'openTickets' => 0,
                        'failedJobs' => 0,
                        'pendingAiTraining' => 0,
                    ];
                }

                $view->with('pendingOrdersBadge', $badges['pendingOrders']);
                $view->with('pendingWishesBadge', $badges['pendingWishes']);
                $view->with('openTicketsBadge', $badges['openTickets']);
                $view->with('failedJobsBadge', $badges['failedJobs']);
                $view->with('pendingAiTrainingBadge', $badges['pendingAiTraining']);
            }
        });
    }
}
