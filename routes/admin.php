<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Management Routes — Mây Hẹn (mayhen.vn)
| Prefix: /admin
| Name Prefix: admin.
|--------------------------------------------------------------------------
*/

// 1. Điều hướng thông minh & Xác thực Quản trị viên
Route::get('/', [AdminAuthController::class, 'index'])->name('index');
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:auth_attempts')->name('login.submit');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

// 2. Không gian Quản trị được bảo vệ bởi middleware admin.auth
Route::middleware('admin.auth')->group(function () {
    // Bảng điều khiển KPI & Hoạt động
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Quản lý Thiệp cưới Khách hàng (Invitations & Weddings)
    Route::prefix('invitations')->name('invitations.')->group(function () {
        Route::get('/', [AdminInvitationController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminInvitationController::class, 'show'])->name('show');
        Route::post('/{id}/toggle-publish', [AdminInvitationController::class, 'togglePublish'])->name('toggle_publish');
        Route::post('/{id}/extend', [AdminInvitationController::class, 'extend'])->name('extend');
    });

    // Quản lý Mẫu thiệp cưới (Templates Engine)
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [AdminTemplateController::class, 'index'])->name('index');
        Route::post('/scan-discovery', [AdminTemplateController::class, 'scanDiscovery'])->name('scan_discovery');
        Route::get('/{id}/edit', [AdminTemplateController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminTemplateController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-active', [AdminTemplateController::class, 'toggleActive'])->name('toggle_active');
        Route::post('/{id}/toggle-featured', [AdminTemplateController::class, 'toggleFeatured'])->name('toggle_featured');
    });

    // Quản lý Đơn hàng & Thanh toán SePay / VietQR
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
    });
});
