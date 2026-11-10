<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GuestController as AdminGuestController;
use App\Http\Controllers\Admin\IntelligenceController as AdminIntelligenceController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\JobMonitorController as AdminJobMonitorController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;
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
        Route::get('/{id}/simulate-impact', [AdminInvitationController::class, 'simulateImpact'])->name('simulate_impact');
        Route::get('/{id}/rehearsal', [AdminInvitationController::class, 'syntheticRehearsal'])->name('rehearsal');
        Route::post('/{id}/run-rehearsal', [AdminInvitationController::class, 'runRehearsal'])->name('run_rehearsal');
        Route::post('/{id}/toggle-publish', [AdminInvitationController::class, 'togglePublish'])->name('toggle_publish');
        Route::post('/{id}/extend', [AdminInvitationController::class, 'extend'])->name('extend');
        Route::post('/{id}/migrate-template', [AdminInvitationController::class, 'migrateTemplate'])->name('migrate_template');
        Route::get('/{id}/export-guests', [AdminInvitationController::class, 'exportGuests'])->name('export_guests');
        Route::get('/{id}/export-gifts', [AdminInvitationController::class, 'exportGifts'])->name('export_gifts');
        Route::get('/{id}/export-rsvps', [AdminInvitationController::class, 'exportRsvps'])->name('export_rsvps');
        Route::get('/{id}/export-wishes', [AdminInvitationController::class, 'exportWishes'])->name('export_wishes');
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

        // Template Release Lab & Visual Regression (NXT-04)
        Route::get('/{id}/release-lab', [AdminTemplateController::class, 'releaseLab'])->name('release_lab');
        Route::post('/{id}/releases', [AdminTemplateController::class, 'createRelease'])->middleware('admin.manager')->name('releases.create');
        Route::post('/{id}/releases/{releaseId}/publish', [AdminTemplateController::class, 'publishRelease'])->middleware('admin.manager')->name('releases.publish');
        Route::post('/{id}/releases/{releaseId}/rollback', [AdminTemplateController::class, 'rollbackRelease'])->middleware('admin.manager')->name('releases.rollback');
        Route::post('/{id}/releases/{releaseId}/rollout', [AdminTemplateController::class, 'updateRollout'])->middleware('admin.manager')->name('releases.rollout');
    });

    // Quản lý Đơn hàng & Thanh toán SePay / VietQR
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/export-csv', [AdminOrderController::class, 'exportCsv'])->name('export');
        Route::get('/webhooks/audit', [AdminOrderController::class, 'webhooks'])->name('webhooks');
        Route::get('/{id}', [AdminOrderController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [AdminOrderController::class, 'approve'])->middleware('admin.manager')->name('approve');
        Route::post('/{id}/refund', [AdminOrderController::class, 'refund'])->middleware('admin.manager')->name('refund');
        Route::post('/{id}/refund-request', [AdminOrderController::class, 'requestRefund'])->middleware('admin.manager')->name('request_refund');
        Route::post('/{id}/refund-confirm', [AdminOrderController::class, 'confirmRefundPayout'])->middleware('admin.manager')->name('confirm_refund');
        Route::post('/reconciliations/{reconciliationId}/approve', [AdminOrderController::class, 'confirmApproval'])->middleware('admin.super')->name('reconciliations.approve');
        Route::post('/reconciliations/{reconciliationId}/reject', [AdminOrderController::class, 'rejectApproval'])->middleware('admin.super')->name('reconciliations.reject');
        Route::post('/{id}/cancel', [AdminOrderController::class, 'cancel'])->middleware('admin.manager')->name('cancel');
    });

    // Quản lý Người dùng & Phân quyền
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/export-csv', [AdminUserController::class, 'exportCsv'])->name('export');
        Route::get('/{id}', [AdminUserController::class, 'show'])->name('show');
        Route::get('/{id}/export-personal-data', [AdminUserController::class, 'exportPersonalData'])->middleware('admin.super')->name('export_personal_data');
        Route::post('/{id}/anonymize', [AdminUserController::class, 'anonymize'])->middleware('admin.super')->name('anonymize');
        Route::post('/{id}/change-role', [AdminUserController::class, 'changeRole'])->middleware('admin.super')->name('change_role');
        Route::post('/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->middleware('admin.manager')->name('reset_password');
        Route::post('/{id}/lock', [AdminUserController::class, 'lock'])->middleware('admin.manager')->name('lock');
        Route::post('/{id}/unlock', [AdminUserController::class, 'unlock'])->middleware('admin.manager')->name('unlock');
    });

    // Quản lý Khách mời & Điểm danh ngày cưới (Wave 05 & Innovation #8, #15)
    Route::prefix('guests')->name('guests.')->group(function () {
        Route::get('/', [AdminGuestController::class, 'index'])->name('index');
        Route::get('/rescue', [AdminGuestController::class, 'rescueDesk'])->name('rescue');
        Route::get('/campaign-preview', [AdminGuestController::class, 'campaignPreview'])->name('campaign_preview');
        Route::get('/export-csv', [AdminGuestController::class, 'exportCsv'])->name('export');
        Route::post('/{id}/toggle-checkin', [AdminGuestController::class, 'toggleCheckIn'])->name('toggle_checkin');
        Route::post('/{id}/assign-table', [AdminGuestController::class, 'quickAssignTable'])->name('assign_table');
        Route::post('/weddings/{weddingId}/emergency-table', [AdminGuestController::class, 'createEmergencyTable'])->name('emergency_table');
    });

    // Quản lý Lời chúc & Giám sát RSVP
    Route::prefix('wishes')->name('wishes.')->group(function () {
        Route::get('/', [AdminWishController::class, 'index'])->name('index');
        Route::get('/export-csv', [AdminWishController::class, 'exportWishesCsv'])->name('export');
        Route::get('/rsvps', [AdminWishController::class, 'rsvps'])->name('rsvps');
        Route::get('/rsvps/export-csv', [AdminWishController::class, 'exportRsvpsCsv'])->name('rsvps.export');
        Route::post('/batch-approve', [AdminWishController::class, 'batchApprove'])->name('batch_approve');
        Route::post('/batch-delete', [AdminWishController::class, 'batchDelete'])->middleware('admin.manager')->name('batch_delete');
        Route::post('/{id}/toggle-approval', [AdminWishController::class, 'toggleApproval'])->name('toggle_approval');
        Route::delete('/{id}', [AdminWishController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
    });

    // Quản lý Gói Cước & Đăng Ký (Wave 06 - ADM-A10)
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [AdminPlanController::class, 'index'])->name('index');
        Route::get('/{plan}/edit', [AdminPlanController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [AdminPlanController::class, 'update'])->middleware('admin.super')->name('update');
        Route::post('/{plan}/toggle-active', [AdminPlanController::class, 'toggleActive'])->middleware('admin.super')->name('toggle_active');
    });

    // Quản lý Mã Giảm Giá & Ưu Đãi (Wave 06 - ADM-A11)
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [AdminCouponController::class, 'index'])->name('index');
        Route::post('/', [AdminCouponController::class, 'store'])->middleware('admin.manager')->name('store');
        Route::put('/{coupon}', [AdminCouponController::class, 'update'])->middleware('admin.manager')->name('update');
        Route::post('/{coupon}/toggle-active', [AdminCouponController::class, 'toggleActive'])->middleware('admin.manager')->name('toggle_active');
        Route::delete('/{coupon}', [AdminCouponController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
        Route::get('/export-csv', [AdminCouponController::class, 'exportCsv'])->name('export');
    });

    // Cấu hình Hệ thống, Bảng giá, Ngân hàng VietQR & Thanh tra Bộ nhớ (Yêu cầu Quản trị viên cấp cao)
    Route::prefix('settings')->middleware('admin.super')->name('settings.')->group(function () {
        Route::get('/plans', [AdminSettingController::class, 'plans'])->name('plans');
        Route::get('/plans/{id}/edit', [AdminSettingController::class, 'editPlan'])->name('plans.edit');
        Route::put('/plans/{id}', [AdminSettingController::class, 'updatePlan'])->name('plans.update');
        Route::post('/plans/{id}/toggle-active', [AdminSettingController::class, 'togglePlanActive'])->name('plans.toggle_active');
        Route::get('/seo', [AdminSettingController::class, 'seoInspector'])->name('seo');
        Route::post('/seo/clear-sitemap', [AdminSettingController::class, 'clearSitemapCache'])->name('seo.clear_sitemap');
        Route::get('/banking', [AdminSettingController::class, 'banking'])->name('banking');
        Route::post('/banking', [AdminSettingController::class, 'updateBanking'])->name('banking.update');
        Route::get('/storage', [AdminSettingController::class, 'storageInspector'])->name('storage');
        Route::get('/audit-logs', [AdminSettingController::class, 'auditLogs'])->name('audit_logs');
        Route::get('/audit-logs/export-csv', [AdminSettingController::class, 'exportAuditLogs'])->name('audit_logs.export');
    });

    // Trung tâm Hỗ trợ Khách hàng (Wave 07 - ADM-A18)
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [AdminSupportController::class, 'index'])->name('index');
        Route::get('/{ticket}', [AdminSupportController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [AdminSupportController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/status', [AdminSupportController::class, 'updateStatus'])->middleware('admin.manager')->name('update_status');
        Route::post('/{ticket}/assign', [AdminSupportController::class, 'assignTo'])->middleware('admin.manager')->name('assign');
    });

    // Giám sát Queue & Failed Jobs (Wave 07 - ADM-A19)
    Route::prefix('system/jobs')->middleware('admin.super')->name('jobs.')->group(function () {
        Route::get('/', [AdminJobMonitorController::class, 'index'])->name('index');
        Route::post('/retry-all', [AdminJobMonitorController::class, 'retryAll'])->name('retry_all');
        Route::post('/purge', [AdminJobMonitorController::class, 'purge'])->name('purge');
        Route::post('/{uuid}/retry', [AdminJobMonitorController::class, 'retry'])->name('retry');
        Route::delete('/{uuid}', [AdminJobMonitorController::class, 'destroy'])->name('destroy');
    });

    // Trung Tâm Trí Tuệ Vận Hành & Radar (Wave 08 - ADM-A20, A21, A22 & Innovation #13)
    Route::prefix('intelligence')->name('intelligence.')->group(function () {
        Route::get('/radar', [AdminIntelligenceController::class, 'readinessRadar'])->name('radar');
        Route::get('/inbox', [AdminIntelligenceController::class, 'actionInbox'])->name('inbox');
        Route::get('/reconciliation', [AdminIntelligenceController::class, 'reconciliationDetective'])->name('reconciliation');
        Route::get('/delight-recovery', [AdminIntelligenceController::class, 'delightRecovery'])->name('delight_recovery');
    });
});
