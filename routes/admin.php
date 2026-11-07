<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WishController as AdminWishController;
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
        Route::post('/{id}/migrate-template', [AdminInvitationController::class, 'migrateTemplate'])->name('migrate_template');
        Route::get('/{id}/export-guests', [AdminInvitationController::class, 'exportGuests'])->name('export_guests');
        Route::get('/{id}/export-gifts', [AdminInvitationController::class, 'exportGifts'])->name('export_gifts');
        Route::post('/{id}/lock', [AdminInvitationController::class, 'lock'])->middleware('admin.manager')->name('lock');
        Route::post('/{id}/unlock', [AdminInvitationController::class, 'unlock'])->middleware('admin.manager')->name('unlock');
    });

    // Quản lý Mẫu thiệp cưới (Templates Engine)
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [AdminTemplateController::class, 'index'])->name('index');
        Route::get('/export-csv', [AdminTemplateController::class, 'exportCsv'])->name('export');
        Route::post('/scan-discovery', [AdminTemplateController::class, 'scanDiscovery'])->middleware('admin.super')->name('scan_discovery');
        Route::get('/{id}/edit', [AdminTemplateController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminTemplateController::class, 'update'])->middleware('admin.manager')->name('update');
        Route::post('/{id}/toggle-active', [AdminTemplateController::class, 'toggleActive'])->middleware('admin.manager')->name('toggle_active');
        Route::post('/{id}/toggle-featured', [AdminTemplateController::class, 'toggleFeatured'])->middleware('admin.manager')->name('toggle_featured');
    });

    // Quản lý Đơn hàng & Thanh toán SePay / VietQR
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/export-csv', [AdminOrderController::class, 'exportCsv'])->name('export');
        Route::get('/webhooks/audit', [AdminOrderController::class, 'webhooks'])->name('webhooks');
        Route::get('/{id}', [AdminOrderController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [AdminOrderController::class, 'approve'])->middleware('admin.manager')->name('approve');
        Route::post('/{id}/cancel', [AdminOrderController::class, 'cancel'])->middleware('admin.manager')->name('cancel');
    });

    // Quản lý Người dùng & Phân quyền
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/export-csv', [AdminUserController::class, 'exportCsv'])->name('export');
        Route::get('/{id}', [AdminUserController::class, 'show'])->name('show');
        Route::post('/{id}/change-role', [AdminUserController::class, 'changeRole'])->middleware('admin.super')->name('change_role');
        Route::post('/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->middleware('admin.manager')->name('reset_password');
    });

    // Quản lý Lời chúc & Giám sát RSVP
    Route::prefix('wishes')->name('wishes.')->group(function () {
        Route::get('/', [AdminWishController::class, 'index'])->name('index');
        Route::get('/export-csv', [AdminWishController::class, 'exportWishesCsv'])->name('export');
        Route::get('/rsvps', [AdminWishController::class, 'rsvps'])->name('rsvps');
        Route::get('/rsvps/export-csv', [AdminWishController::class, 'exportRsvpsCsv'])->name('rsvps.export');
        Route::post('/{id}/toggle-approval', [AdminWishController::class, 'toggleApproval'])->name('toggle_approval');
        Route::delete('/{id}', [AdminWishController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
    });

    // Cấu hình Hệ thống, Bảng giá, Ngân hàng VietQR & Thanh tra Bộ nhớ (Yêu cầu Quản trị viên cấp cao)
    Route::prefix('settings')->middleware('admin.super')->name('settings.')->group(function () {
        Route::get('/plans', [AdminSettingController::class, 'plans'])->name('plans');
        Route::get('/plans/{id}/edit', [AdminSettingController::class, 'editPlan'])->name('plans.edit');
        Route::put('/plans/{id}', [AdminSettingController::class, 'updatePlan'])->name('plans.update');
        Route::post('/plans/{id}/toggle-active', [AdminSettingController::class, 'togglePlanActive'])->name('plans.toggle_active');
        Route::get('/banking', [AdminSettingController::class, 'banking'])->name('banking');
        Route::post('/banking', [AdminSettingController::class, 'updateBanking'])->name('banking.update');
        Route::get('/storage', [AdminSettingController::class, 'storageInspector'])->name('storage');
        Route::get('/audit-logs', [AdminSettingController::class, 'auditLogs'])->name('audit_logs');
        Route::get('/audit-logs/export-csv', [AdminSettingController::class, 'exportAuditLogs'])->name('audit_logs.export');
    });
});
