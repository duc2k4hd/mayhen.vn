<?php

use App\Http\Controllers\Admin\AdminAcceptanceAuditController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminAnnouncementController;
use App\Http\Controllers\Admin\AdminAssetDiagnosticsController;
use App\Http\Controllers\Admin\AdminBackupController;
use App\Http\Controllers\Admin\AdminCannedResponseController;
use App\Http\Controllers\Admin\AdminCheckInController;
use App\Http\Controllers\Admin\AdminContentModerationController;
use App\Http\Controllers\Admin\AdminDeveloperIntegrationController;
use App\Http\Controllers\Admin\AdminDocsController;
use App\Http\Controllers\Admin\AdminDomainManagerController;
use App\Http\Controllers\Admin\AdminEmailTemplateController;
use App\Http\Controllers\Admin\AdminFeatureFlagController;
use App\Http\Controllers\Admin\AdminFinancialReconciliationController;
use App\Http\Controllers\Admin\AdminGiftController;
use App\Http\Controllers\Admin\AdminGuestbookController;
use App\Http\Controllers\Admin\AdminGuestImportController;
use App\Http\Controllers\Admin\AdminGuestPhotoController;
use App\Http\Controllers\Admin\AdminInvitationRenewalController;
use App\Http\Controllers\Admin\AdminInvitationRevisionController;
use App\Http\Controllers\Admin\AdminInvoiceController;
use App\Http\Controllers\Admin\AdminKeepsakeController;
use App\Http\Controllers\Admin\AdminLifecycleAutomationController;
use App\Http\Controllers\Admin\AdminNotificationGatewayController;
use App\Http\Controllers\Admin\AdminOrderRefundController;
use App\Http\Controllers\Admin\AdminPaperScanController;
use App\Http\Controllers\Admin\AdminPlatformSettingController;
use App\Http\Controllers\Admin\AdminRbacController;
use App\Http\Controllers\Admin\AdminRescheduleController;
use App\Http\Controllers\Admin\AdminSeatingPlanController;
use App\Http\Controllers\Admin\AdminSecurityController;
use App\Http\Controllers\Admin\AdminSystemHealthController;
use App\Http\Controllers\Admin\AiObservatoryController as AdminAiObservatoryController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\CmsPageController as AdminCmsPageController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GuestController as AdminGuestController;
use App\Http\Controllers\Admin\IntelligenceController as AdminIntelligenceController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\JobMonitorController as AdminJobMonitorController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\MessagingDispatchController as AdminMessagingDispatchController;
use App\Http\Controllers\Admin\OperationsReplayController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PartnerController as AdminPartnerController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\PrivacyOperationsController as AdminPrivacyOperationsController;
use App\Http\Controllers\Admin\ReleaseControlController as AdminReleaseControlController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SeoController as AdminSeoController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;
use App\Http\Controllers\Admin\SupportImpersonationController;
use App\Http\Controllers\Admin\SystemResilienceController as AdminSystemResilienceController;
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
        Route::post('/{id}/override-entitlement', [AdminInvitationController::class, 'overrideEntitlement'])->middleware('admin.manager')->name('override_entitlement');

        // Phân hệ Quản trị Lịch sử Phiên bản & Phục hồi Thiệp cưới (Mục 23.2 PRD V3 & STU-03)
        Route::prefix('{id}/revisions')->name('revisions.')->group(function () {
            Route::get('/', [AdminInvitationRevisionController::class, 'index'])->name('index');
            Route::get('/{revisionId}/diff', [AdminInvitationRevisionController::class, 'diff'])->name('diff');
            Route::post('/{revisionId}/restore', [AdminInvitationRevisionController::class, 'restore'])->middleware('admin.manager')->name('restore');
        });
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

    // Quản Lý Thư Viện Phương Tiện & Dọn Dẹp File Mồ Côi (Mục 7.5 DOCX: Admin Media)
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [AdminMediaController::class, 'index'])->name('index');
        Route::post('/upload', [AdminMediaController::class, 'upload'])->middleware('admin.manager')->name('upload');
        Route::delete('/destroy', [AdminMediaController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
        Route::get('/orphans', [AdminMediaController::class, 'orphans'])->name('orphans');
        Route::post('/purge-orphans', [AdminMediaController::class, 'purgeOrphans'])->middleware('admin.manager')->name('purge_orphans');
    });

    // Phân Hệ Chẩn Đoán Lỗi Ảnh Thiệp & Phục Hồi Tài Nguyên (Mục 60 PRD V3, Luồng F & ADM-AST-01)
    Route::prefix('assets/diagnostics')->name('assets.diagnostics.')->group(function () {
        Route::get('/', [AdminAssetDiagnosticsController::class, 'index'])->name('index');
        Route::get('/scan/{id}', [AdminAssetDiagnosticsController::class, 'scanSingle'])->name('scan');
        Route::post('/{id}/apply-fallback', [AdminAssetDiagnosticsController::class, 'applyFallback'])->middleware('admin.manager')->name('apply_fallback');
        Route::post('/{id}/localize', [AdminAssetDiagnosticsController::class, 'localizeAsset'])->middleware('admin.manager')->name('localize');
        Route::post('/{id}/batch-repair', [AdminAssetDiagnosticsController::class, 'batchRepair'])->middleware('admin.manager')->name('batch_repair');
        Route::get('/export-csv', [AdminAssetDiagnosticsController::class, 'export'])->name('export');
    });

    // Đài Giám Sát Quét Thiệp Giấy AI OCR (Mục 18, 23 PRD V3 & ADM-OCR-01)
    Route::prefix('paper-scans')->name('paper_scans.')->group(function () {
        Route::get('/', [AdminPaperScanController::class, 'index'])->name('index');
        Route::get('/export-csv', [AdminPaperScanController::class, 'export'])->name('export');
        Route::get('/{id}', [AdminPaperScanController::class, 'show'])->name('show');
        Route::put('/{id}/verify', [AdminPaperScanController::class, 'updateVerified'])->middleware('admin.manager')->name('update_verified');
        Route::post('/{id}/apply', [AdminPaperScanController::class, 'apply'])->middleware('admin.manager')->name('apply');
        Route::delete('/{id}', [AdminPaperScanController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
    });

    // Phân Hệ Quản Trị Sổ Hoàn Tiền, Khiếu Nại & Tranh Chấp Thanh Toán (Mục 140 PRD V3 & Mục 9.3 DOCX)
    Route::prefix('orders/refunds')->name('refunds.')->group(function () {
        Route::get('/', [AdminOrderRefundController::class, 'index'])->name('index');
        Route::post('/', [AdminOrderRefundController::class, 'storeRequest'])->middleware('admin.manager')->name('store');
        Route::post('/{id}/approve', [AdminOrderRefundController::class, 'approve'])->middleware('admin.manager')->name('approve');
        Route::post('/{id}/reject', [AdminOrderRefundController::class, 'reject'])->middleware('admin.manager')->name('reject');
        Route::get('/export-csv', [AdminOrderRefundController::class, 'export'])->name('export');
    });

    // Quản lý Đơn hàng & Thanh toán SePay / VietQR
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/export-csv', [AdminOrderController::class, 'exportCsv'])->name('export');
        Route::get('/webhooks/audit', [AdminOrderController::class, 'webhooks'])->name('webhooks');
        Route::get('/{id}', [AdminOrderController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('/{id}/approve', [AdminOrderController::class, 'approve'])->middleware('admin.manager')->name('approve');
        Route::post('/{id}/refund', [AdminOrderController::class, 'refund'])->middleware('admin.manager')->name('refund');
        Route::post('/{id}/refund-request', [AdminOrderController::class, 'requestRefund'])->middleware('admin.manager')->name('request_refund');
        Route::post('/{id}/refund-confirm', [AdminOrderController::class, 'confirmRefundPayout'])->middleware('admin.manager')->name('confirm_refund');
        Route::post('/reconciliations/{reconciliationId}/approve', [AdminOrderController::class, 'confirmApproval'])->middleware('admin.super')->name('reconciliations.approve');
        Route::post('/reconciliations/{reconciliationId}/reject', [AdminOrderController::class, 'rejectApproval'])->middleware('admin.super')->name('reconciliations.reject');
        Route::post('/{id}/cancel', [AdminOrderController::class, 'cancel'])->middleware('admin.manager')->name('cancel');
    });

    // Phân Hệ Quản Trị Sổ Đối Soát Giao Dịch, Webhook & Xử Lý Sai Lệch (Mục 137 PRD V3 & PAY-01, PAY-02, PAY-03)
    Route::prefix('reconciliations')->name('reconciliations.')->group(function () {
        Route::get('/', [AdminFinancialReconciliationController::class, 'index'])->name('index');
        Route::post('/', [AdminFinancialReconciliationController::class, 'store'])->middleware('admin.manager')->name('store');
        Route::get('/export-csv', [AdminFinancialReconciliationController::class, 'export'])->name('export');
        Route::post('/{id}/approve', [AdminFinancialReconciliationController::class, 'approve'])->middleware('admin.super')->name('approve');
        Route::post('/{id}/reject', [AdminFinancialReconciliationController::class, 'reject'])->middleware('admin.super')->name('reject');
        Route::post('/{id}/confirm-refund', [AdminFinancialReconciliationController::class, 'confirmRefund'])->middleware('admin.super')->name('confirm_refund');
    });

    // Quản Lý Gia Hạn, Hết Hạn Thiệp Cưới & Thời Gian Ân Hạn (Mục 139 PRD V3 & Mục 13.4 DOCX)
    Route::prefix('renewals')->name('renewals.')->group(function () {
        Route::get('/', [AdminInvitationRenewalController::class, 'index'])->name('index');
        Route::post('/{id}/extend', [AdminInvitationRenewalController::class, 'extend'])->middleware('admin.manager')->name('extend');
        Route::post('/{id}/remind', [AdminInvitationRenewalController::class, 'sendReminder'])->middleware('admin.manager')->name('remind');
        Route::post('/{id}/archive', [AdminInvitationRenewalController::class, 'archive'])->middleware('admin.manager')->name('archive');
        Route::post('/{id}/unarchive', [AdminInvitationRenewalController::class, 'unarchive'])->middleware('admin.manager')->name('unarchive');
        Route::get('/export-csv', [AdminInvitationRenewalController::class, 'export'])->name('export');
    });

    // Phân Hệ Quản Trị Hóa Đơn Điện Tử, Phiếu Thu VAT & Yêu Cầu Chứng Từ (Mục 141 PRD V3 & Mục 9.4 DOCX)
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [AdminInvoiceController::class, 'index'])->name('index');
        Route::get('/create', [AdminInvoiceController::class, 'create'])->name('create');
        Route::post('/', [AdminInvoiceController::class, 'store'])->middleware('admin.manager')->name('store');
        Route::get('/export-csv', [AdminInvoiceController::class, 'export'])->name('export');
        Route::get('/{id}', [AdminInvoiceController::class, 'show'])->name('show');
        Route::get('/{id}/print', [AdminInvoiceController::class, 'print'])->name('print');
        Route::post('/{id}/issue', [AdminInvoiceController::class, 'issue'])->middleware('admin.manager')->name('issue');
        Route::post('/{id}/cancel', [AdminInvoiceController::class, 'cancel'])->middleware('admin.manager')->name('cancel');
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
        Route::post('/{id}/impersonate', [SupportImpersonationController::class, 'impersonate'])->middleware('admin.manager')->name('impersonate');
    });

    // Quyền hỗ trợ an toàn — Thoát chế độ xem dưới góc nhìn khách (Mục 17.2 PRD V3)
    Route::post('/impersonate/leave', [SupportImpersonationController::class, 'leave'])->name('impersonation.leave');
    Route::get('/impersonate/status', [SupportImpersonationController::class, 'status'])->name('impersonation.status');

    // Quản lý Khách mời & Điểm danh ngày cưới (Wave 05 & Innovation #8, #15)
    Route::prefix('guests')->name('guests.')->group(function () {
        Route::get('/', [AdminGuestController::class, 'index'])->name('index');
        Route::get('/rescue', [AdminGuestController::class, 'rescueDesk'])->name('rescue');
        Route::get('/campaign-preview', [AdminGuestController::class, 'campaignPreview'])->name('campaign_preview');
        Route::get('/export-csv', [AdminGuestController::class, 'exportCsv'])->name('export');
        Route::post('/{id}/toggle-checkin', [AdminGuestController::class, 'toggleCheckIn'])->name('toggle_checkin');
        Route::post('/{id}/assign-table', [AdminGuestController::class, 'quickAssignTable'])->name('assign_table');
        Route::post('/weddings/{weddingId}/emergency-table', [AdminGuestController::class, 'createEmergencyTable'])->name('emergency_table');

        // Phân hệ Nhập Khách Hàng Loạt 4 Bước (Mục 28.3 PRD V3 & GUE-01, GUE-02)
        Route::prefix('import')->name('import.')->group(function () {
            Route::get('/', [AdminGuestImportController::class, 'index'])->name('index');
            Route::post('/upload', [AdminGuestImportController::class, 'upload'])->name('upload');
            Route::get('/{batchId}/mapping', [AdminGuestImportController::class, 'mapping'])->name('mapping');
            Route::post('/{batchId}/preview', [AdminGuestImportController::class, 'preview'])->name('preview');
            Route::post('/{batchId}/execute', [AdminGuestImportController::class, 'execute'])->name('execute');
            Route::get('/{batchId}/export-failed', [AdminGuestImportController::class, 'exportFailed'])->name('export_failed');
        });

        // Phân hệ Quản trị Sơ đồ Bàn tiệc & Chỗ ngồi Quan khách (Mục 32, 212 PRD V3)
        Route::prefix('seating')->name('seating.')->group(function () {
            Route::get('/', [AdminSeatingPlanController::class, 'index'])->name('index');
            Route::post('/weddings/{weddingId}/tables', [AdminSeatingPlanController::class, 'storeTable'])->name('tables.store');
            Route::put('/weddings/{weddingId}/tables/{tableId}', [AdminSeatingPlanController::class, 'updateTable'])->name('tables.update');
            Route::delete('/weddings/{weddingId}/tables/{tableId}', [AdminSeatingPlanController::class, 'destroyTable'])->name('tables.destroy');
            Route::post('/weddings/{weddingId}/tables/{tableId}/assign', [AdminSeatingPlanController::class, 'assign'])->name('assign');
            Route::post('/weddings/{weddingId}/guests/{guestId}/unassign', [AdminSeatingPlanController::class, 'unassign'])->name('unassign');
            Route::get('/weddings/{weddingId}/export-csv', [AdminSeatingPlanController::class, 'export'])->name('export');
        });

        // Phân hệ Quản trị Trạm Điểm Danh Đón Khách Ngày Cưới (Mục 33 PRD V3 & ADM-CHK-01)
        Route::prefix('checkin')->name('checkin.')->group(function () {
            Route::get('/', [AdminCheckInController::class, 'index'])->name('index');
            Route::get('/weddings/{weddingId}/live-stats', [AdminCheckInController::class, 'liveStats'])->name('live_stats');
            Route::get('/weddings/{weddingId}/search', [AdminCheckInController::class, 'search'])->name('search');
            Route::post('/guests/{guestId}/checkin', [AdminCheckInController::class, 'checkIn'])->name('checkin_guest');
            Route::post('/guests/{guestId}/cancel', [AdminCheckInController::class, 'cancel'])->name('cancel_guest');
            Route::post('/guests/{guestId}/update-table', [AdminCheckInController::class, 'updateTable'])->name('update_table');
            Route::post('/weddings/{weddingId}/walk-in', [AdminCheckInController::class, 'walkIn'])->name('walk_in');
            Route::get('/weddings/{weddingId}/export-csv', [AdminCheckInController::class, 'export'])->name('export');
        });
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

        // Phân Hệ Quản Trị Sổ Lưu Bút Kỷ Niệm & Xuất Bản Lưu Niệm (Mục 34 PRD V3 & ADM-GBK-01)
        Route::prefix('guestbook')->name('guestbook.')->group(function () {
            Route::get('/', [AdminGuestbookController::class, 'index'])->name('index');
            Route::post('/{id}/toggle-pin', [AdminGuestbookController::class, 'togglePin'])->name('toggle_pin');
            Route::post('/{id}/toggle-approval', [AdminGuestbookController::class, 'toggleApproval'])->name('toggle_approval');
            Route::post('/{id}/toggle-privacy', [AdminGuestbookController::class, 'togglePrivacy'])->name('toggle_privacy');
            Route::post('/{id}/reply', [AdminGuestbookController::class, 'reply'])->name('reply');
            Route::delete('/{id}/reply', [AdminGuestbookController::class, 'deleteReply'])->name('delete_reply');
            Route::delete('/{id}', [AdminGuestbookController::class, 'destroy'])->name('destroy');
            Route::get('/weddings/{weddingId}/keepsake-print', [AdminGuestbookController::class, 'exportHtml'])->name('keepsake_print');
            Route::get('/weddings/{weddingId}/export-csv', [AdminGuestbookController::class, 'export'])->name('export');
        });
    });

    // Phân Hệ Quản Trị Khoảnh Khắc Khách Gửi & Kiểm Duyệt Tường Ảnh (Mục 217, 218 PRD V3 & ADM-GPH-01)
    Route::prefix('guest-photos')->name('guest_photos.')->group(function () {
        Route::get('/', [AdminGuestPhotoController::class, 'index'])->name('index');
        Route::get('/slideshow', [AdminGuestPhotoController::class, 'slideshow'])->name('slideshow');
        Route::post('/{id}/toggle-approval', [AdminGuestPhotoController::class, 'toggleApproval'])->name('toggle_approval');
        Route::post('/batch-approve', [AdminGuestPhotoController::class, 'batchApprove'])->name('batch_approve');
        Route::delete('/{id}', [AdminGuestPhotoController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
        Route::post('/batch-delete', [AdminGuestPhotoController::class, 'batchDelete'])->middleware('admin.manager')->name('batch_delete');
        Route::get('/export-csv', [AdminGuestPhotoController::class, 'export'])->name('export');
    });

    // Phân Hệ Quản Trị Sổ Mừng Cưới & Quản Lý Quà Mừng (Mục 35 PRD V3 & ADM-GFT-01)
    Route::prefix('gifts')->name('gifts.')->group(function () {
        Route::get('/', [AdminGiftController::class, 'index'])->name('index');
        Route::post('/', [AdminGiftController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminGiftController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-acknowledgement', [AdminGiftController::class, 'toggleAcknowledgement'])->name('toggle_acknowledgement');
        Route::post('/batch-acknowledge', [AdminGiftController::class, 'batchAcknowledge'])->name('batch_acknowledge');
        Route::delete('/{id}', [AdminGiftController::class, 'destroy'])->name('destroy');
        Route::post('/batch-delete', [AdminGiftController::class, 'batchDelete'])->name('batch_delete');
        Route::get('/export-csv', [AdminGiftController::class, 'export'])->name('export');
    });

    // Phân Hệ Quản Trị Kế Hoạch Cưới Hậu Sự Kiện & Lưu Trữ Kỷ Niệm (Mục 36 PRD V3 & ADM-ARC-01)
    Route::prefix('keepsakes')->name('keepsakes.')->group(function () {
        Route::get('/', [AdminKeepsakeController::class, 'index'])->name('index');
        Route::get('/{weddingId}', [AdminKeepsakeController::class, 'show'])->name('show');
        Route::post('/{weddingId}/settings', [AdminKeepsakeController::class, 'updateSettings'])->middleware('admin.manager')->name('update_settings');
        Route::post('/{weddingId}/toggle', [AdminKeepsakeController::class, 'toggle'])->name('toggle');
        Route::post('/capsules/{capsuleId}/unlock', [AdminKeepsakeController::class, 'unlockCapsule'])->name('unlock_capsule');
        Route::delete('/capsules/{capsuleId}', [AdminKeepsakeController::class, 'destroyCapsule'])->middleware('admin.manager')->name('destroy_capsule');
        Route::get('/{weddingId}/export-archive', [AdminKeepsakeController::class, 'exportArchive'])->name('export_archive');
    });

    // Phân Hệ Quản Trị Đổi Ngày Cưới & Tái Xác Nhận RSVP (Mục 61 PRD V3 & ADM-RSC-01)
    Route::prefix('reschedule')->name('reschedule.')->group(function () {
        Route::get('/', [AdminRescheduleController::class, 'index'])->name('index');
        Route::post('/', [AdminRescheduleController::class, 'store'])->middleware('admin.manager')->name('store');
        Route::post('/{id}/notify', [AdminRescheduleController::class, 'notifyGuests'])->middleware('admin.manager')->name('notify');
        Route::get('/export-csv', [AdminRescheduleController::class, 'export'])->name('export');
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
        // Trung Tâm Thiết Lập Nền Tảng An Toàn, Kiểm Tra Kết Nối & Phòng Vệ Cấu Hình (Mục 20 & Mục 50 PRD V3 / Screen contract 08)
        Route::get('/platform', [AdminPlatformSettingController::class, 'index'])->name('platform');
        Route::put('/platform/{group}', [AdminPlatformSettingController::class, 'update'])->name('platform.update');
        Route::post('/platform/test/{service}', [AdminPlatformSettingController::class, 'testConnection'])->name('platform.test');
        Route::post('/platform/rollback/{historyId}', [AdminPlatformSettingController::class, 'rollback'])->name('platform.rollback');

        // Trung Tâm Sao Lưu, Phục Hồi Dữ Liệu & Quản Trị Thảm Họa (Mục 180 PRD V3 & OPS-01)
        Route::prefix('backups')->name('backups.')->group(function () {
            Route::get('/', [AdminBackupController::class, 'index'])->name('index');
            Route::post('/create', [AdminBackupController::class, 'create'])->name('create');
            Route::post('/{id}/verify', [AdminBackupController::class, 'verify'])->name('verify');
            Route::get('/{id}/download', [AdminBackupController::class, 'download'])->name('download');
            Route::delete('/{id}', [AdminBackupController::class, 'destroy'])->name('destroy');
            Route::post('/clean-old', [AdminBackupController::class, 'cleanOld'])->name('clean_old');
        });

        // Phân Hệ Quản Trị Khóa API & Webhooks Ngoại Vi (Mục 179 PRD V3)
        Route::prefix('developer-integrations')->name('developer.')->group(function () {
            Route::get('/', [AdminDeveloperIntegrationController::class, 'index'])->name('index');
            Route::post('/api-keys', [AdminDeveloperIntegrationController::class, 'storeApiKey'])->name('api_keys.store');
            Route::post('/api-keys/{id}/revoke', [AdminDeveloperIntegrationController::class, 'revokeApiKey'])->name('api_keys.revoke');
            Route::delete('/api-keys/{id}', [AdminDeveloperIntegrationController::class, 'destroyApiKey'])->name('api_keys.destroy');
            Route::post('/webhooks', [AdminDeveloperIntegrationController::class, 'storeWebhook'])->name('webhooks.store');
            Route::post('/webhooks/{id}/toggle', [AdminDeveloperIntegrationController::class, 'toggleWebhook'])->name('webhooks.toggle');
            Route::post('/webhooks/{id}/ping', [AdminDeveloperIntegrationController::class, 'pingWebhook'])->name('webhooks.ping');
            Route::delete('/webhooks/{id}', [AdminDeveloperIntegrationController::class, 'destroyWebhook'])->name('webhooks.destroy');
            Route::post('/deliveries/{id}/retry', [AdminDeveloperIntegrationController::class, 'retryDelivery'])->name('deliveries.retry');
        });

        // Phân Hệ Quản Trị An Ninh Mạng, Rate Limits & IP Firewall (Mục 177 PRD V3)
        Route::prefix('security')->name('security.')->group(function () {
            Route::get('/', [AdminSecurityController::class, 'index'])->name('index');
            Route::post('/ip-rules', [AdminSecurityController::class, 'storeIpRule'])->name('ip_rules.store');
            Route::delete('/ip-rules/{id}', [AdminSecurityController::class, 'destroyIpRule'])->name('ip_rules.destroy');
            Route::put('/rate-limits', [AdminSecurityController::class, 'updateRateLimits'])->name('rate_limits.update');
            Route::post('/flush-cache', [AdminSecurityController::class, 'flushCache'])->name('flush_cache');
        });

        // Phân Hệ Cấu Hình Cổng Tin Nhắn SMS & Zalo ZNS (Mục 174 PRD V3)
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [AdminNotificationGatewayController::class, 'index'])->name('index');
            Route::put('/{gateway}', [AdminNotificationGatewayController::class, 'update'])->name('update');
            Route::post('/ping/{gateway}', [AdminNotificationGatewayController::class, 'ping'])->name('ping');
            Route::post('/test-dispatch', [AdminNotificationGatewayController::class, 'sendTest'])->name('send_test');
        });

        // Phân Hệ Quản Trị Tên Miền Riêng & Định Tuyến Liên Kết (Mục 172 PRD V3)
        Route::prefix('domains')->name('domains.')->group(function () {
            Route::get('/', [AdminDomainManagerController::class, 'index'])->name('index');
            Route::post('/{domain}/verify', [AdminDomainManagerController::class, 'verify'])->name('verify');
            Route::post('/{domain}/activate', [AdminDomainManagerController::class, 'activate'])->name('activate');
            Route::post('/{domain}/deactivate', [AdminDomainManagerController::class, 'deactivate'])->name('deactivate');
            Route::delete('/{domain}', [AdminDomainManagerController::class, 'destroy'])->name('destroy');
            Route::post('/dns-lookup', [AdminDomainManagerController::class, 'lookup'])->name('lookup');
            Route::put('/routing-settings', [AdminDomainManagerController::class, 'updateSettings'])->name('settings');
        });

        // Phân Hệ Quản Trị Trung Tâm Phát Hành Tính Năng & Cờ Nền Tảng (Mục 178 PRD V3 & ADM-FLAG-01)
        Route::prefix('flags')->name('flags.')->group(function () {
            Route::get('/', [AdminFeatureFlagController::class, 'index'])->name('index');
            Route::post('/', [AdminFeatureFlagController::class, 'store'])->name('store');
            Route::post('/{key}/toggle', [AdminFeatureFlagController::class, 'toggle'])->name('toggle');
            Route::put('/{key}/rollout', [AdminFeatureFlagController::class, 'updateRollout'])->name('rollout');
            Route::post('/{key}/kill-switch', [AdminFeatureFlagController::class, 'toggleKillSwitch'])->name('kill_switch');
            Route::delete('/{key}', [AdminFeatureFlagController::class, 'destroy'])->name('destroy');
        });

        Route::get('/plans', [AdminSettingController::class, 'plans'])->name('plans');
        Route::get('/plans/{id}/edit', [AdminSettingController::class, 'editPlan'])->name('plans.edit');
        Route::put('/plans/{id}', [AdminSettingController::class, 'updatePlan'])->name('plans.update');
        Route::post('/plans/{id}/toggle-active', [AdminSettingController::class, 'togglePlanActive'])->name('plans.toggle_active');
        Route::get('/seo', [AdminSeoController::class, 'index'])->name('seo');
        Route::post('/seo/clear-sitemap', [AdminSeoController::class, 'clearSitemap'])->name('seo.clear_sitemap');
        Route::get('/banking', [AdminSettingController::class, 'banking'])->name('banking');
        Route::post('/banking', [AdminSettingController::class, 'updateBanking'])->name('banking.update');
        Route::get('/storage', [AdminSettingController::class, 'storageInspector'])->name('storage');
        Route::get('/audit-logs', [AdminSettingController::class, 'auditLogs'])->name('audit_logs');
        Route::get('/audit-logs/export-csv', [AdminSettingController::class, 'exportAuditLogs'])->name('audit_logs.export');
    });

    // Trung tâm Hỗ trợ Khách hàng (Wave 07 - ADM-A18)
    Route::prefix('support')->name('support.')->group(function () {
        // Kho Quản Trị Mẫu Phản Hồi Nhanh & Macro CSKH (Mục 17.1 & Screen contract 05 PRD V3)
        Route::prefix('canned-responses')->name('canned.')->group(function () {
            Route::get('/', [AdminCannedResponseController::class, 'index'])->name('index');
            Route::post('/', [AdminCannedResponseController::class, 'store'])->middleware('admin.manager')->name('store');
            Route::put('/{canned}', [AdminCannedResponseController::class, 'update'])->middleware('admin.manager')->name('update');
            Route::delete('/{canned}', [AdminCannedResponseController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
            Route::post('/{canned}/toggle', [AdminCannedResponseController::class, 'toggle'])->middleware('admin.manager')->name('toggle');
        });

        Route::get('/', [AdminSupportController::class, 'index'])->name('index');
        Route::post('/scan-sla-breaches', [AdminSupportController::class, 'scanSlaBreaches'])->middleware('admin.manager')->name('scan_sla');
        Route::get('/{ticket}', [AdminSupportController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [AdminSupportController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/status', [AdminSupportController::class, 'updateStatus'])->middleware('admin.manager')->name('update_status');
        Route::post('/{ticket}/assign', [AdminSupportController::class, 'assignTo'])->middleware('admin.manager')->name('assign');
        Route::post('/{ticket}/escalate', [AdminSupportController::class, 'escalate'])->middleware('admin.manager')->name('escalate');
        Route::post('/{ticket}/declare-incident', [AdminSupportController::class, 'declareIncident'])->middleware('admin.manager')->name('declare_incident');
        Route::post('/{ticket}/resolve-incident', [AdminSupportController::class, 'resolveIncident'])->middleware('admin.manager')->name('resolve_incident');
    });

    // Trung Tâm Kiểm Duyệt Nội Dung & Báo Cáo Vi Phạm (Mục 17.3 PRD V3 & Dòng 154)
    Route::prefix('moderation')->name('moderation.')->group(function () {
        Route::get('/', [AdminContentModerationController::class, 'index'])->name('index');
        Route::post('/{id}/resolve', [AdminContentModerationController::class, 'resolve'])->middleware('admin.manager')->name('resolve');
    });

    // Quản Lý Yêu Cầu Liên Hệ & Báo Giá (Mục 7.15 DOCX: Admin Contacts)
    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', [AdminContactController::class, 'index'])->name('index');
        Route::put('/{contact}', [AdminContactController::class, 'update'])->middleware('admin.manager')->name('update');
        Route::delete('/{contact}', [AdminContactController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
    });

    // Quản Lý Đánh Giá & Cảm Nhận Khách Hàng (Mục 7.14 DOCX: Admin Reviews)
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [AdminReviewController::class, 'index'])->name('index');
        Route::post('/{review}/approve', [AdminReviewController::class, 'approve'])->middleware('admin.manager')->name('approve');
        Route::post('/{review}/reject', [AdminReviewController::class, 'reject'])->middleware('admin.manager')->name('reject');
        Route::post('/{review}/toggle-featured', [AdminReviewController::class, 'toggleFeatured'])->middleware('admin.manager')->name('toggle_featured');
        Route::delete('/{review}', [AdminReviewController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
    });

    // Trung Tâm Chỉ Huy Tác Vụ Nền, Lô Công Việc & Hàng Đợi (Mục 18 & Mục 48 PRD V3: Job Center Cockpit)
    Route::prefix('system/jobs')->middleware('admin.super')->name('jobs.')->group(function () {
        Route::get('/', [AdminJobMonitorController::class, 'index'])->name('index');
        Route::get('/export-errors', [AdminJobMonitorController::class, 'exportErrors'])->name('export_errors');
        Route::post('/retry-all', [AdminJobMonitorController::class, 'retryAll'])->name('retry_all');
        Route::post('/purge', [AdminJobMonitorController::class, 'purge'])->name('purge');
        Route::post('/batches/{id}/cancel', [AdminJobMonitorController::class, 'cancelBatch'])->name('batches.cancel');
        Route::post('/batches/{id}/retry', [AdminJobMonitorController::class, 'retryBatch'])->name('batches.retry');
        Route::get('/{uuid}/details', [AdminJobMonitorController::class, 'details'])->name('details');
        Route::post('/{uuid}/retry', [AdminJobMonitorController::class, 'retry'])->name('retry');
        Route::delete('/{uuid}', [AdminJobMonitorController::class, 'destroy'])->name('destroy');
    });

    // Trạm Chỉ Huy Kiểm Định Chất Lượng Toàn Diện & Giám Sát Ma Trận Nghiệm Thu (Section 62 & 63 PRD V3)
    Route::prefix('system/acceptance')->middleware('admin.super')->name('system.acceptance.')->group(function () {
        Route::get('/', [AdminAcceptanceAuditController::class, 'index'])->name('index');
        Route::post('/run', [AdminAcceptanceAuditController::class, 'triggerAudit'])->name('run');
        Route::get('/export', [AdminAcceptanceAuditController::class, 'exportCsv'])->name('export');
    });

    // Trạng Thái Dịch Vụ, Sức Khỏe Hạ Tầng & Nhật Ký Lỗi (Mục 158 PRD V3)
    Route::prefix('system/health')->middleware('admin.super')->name('system.health.')->group(function () {
        Route::get('/', [AdminSystemHealthController::class, 'index'])->name('index');
        Route::post('/clear-log', [AdminSystemHealthController::class, 'clearLog'])->name('clear_log');
        Route::get('/download-log', [AdminSystemHealthController::class, 'downloadLog'])->name('download_log');
    });

    // Trung Tâm Trí Tuệ Vận Hành & Radar (Wave 08 - ADM-A20, A21, A22 & Innovation #13)
    Route::prefix('intelligence')->name('intelligence.')->group(function () {
        Route::get('/radar', [AdminIntelligenceController::class, 'readinessRadar'])->name('radar');
        Route::get('/funnel', [AdminIntelligenceController::class, 'funnel'])->name('funnel');
        Route::get('/inbox', [AdminIntelligenceController::class, 'actionInbox'])->name('inbox');
        Route::get('/reconciliation', [AdminIntelligenceController::class, 'reconciliationDetective'])->name('reconciliation');
        Route::get('/delight-recovery', [AdminIntelligenceController::class, 'delightRecovery'])->name('delight_recovery');
        Route::get('/feedback-loop', [AdminIntelligenceController::class, 'feedbackLoop'])->name('feedback_loop');
        Route::post('/feedback/{id}/resolve', [AdminIntelligenceController::class, 'resolveFeedbackAction'])->name('resolve_feedback');
        Route::get('/replay', [OperationsReplayController::class, 'index'])->name('replay.index');
        Route::get('/replay/{type}/{id}', [OperationsReplayController::class, 'show'])->name('replay.show');
    });

    // Trung Tâm Phân Tích & Báo Cáo Toàn Diện (Mục 19 & Mục 49 PRD V3: Analytics & Reporting Hub)
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AdminAnalyticsController::class, 'index'])->name('index');
        Route::get('/export', [AdminAnalyticsController::class, 'export'])->name('export');
    });

    // Quản Trị Quyền Riêng Tư & Lưu Trữ Dữ Liệu (NXT-10: Privacy Operations & Retention Jobs)
    Route::prefix('privacy')->name('privacy.')->group(function () {
        Route::get('/', [AdminPrivacyOperationsController::class, 'index'])->name('index');
        Route::post('/batch-retention', [AdminPrivacyOperationsController::class, 'executeRetentionBatch'])->middleware('admin.manager')->name('batch_retention');
        Route::post('/requests/{id}/approve', [AdminPrivacyOperationsController::class, 'approveErasureRequest'])->middleware('admin.manager')->name('approve_erasure');
        Route::post('/requests/{id}/reject', [AdminPrivacyOperationsController::class, 'rejectErasureRequest'])->middleware('admin.manager')->name('reject_erasure');
        Route::post('/requests/{id}/execute', [AdminPrivacyOperationsController::class, 'executeErasure'])->middleware('admin.manager')->name('execute_erasure');
        Route::get('/dsar-download/{token}', [AdminPrivacyOperationsController::class, 'downloadDsarExport'])->name('download_dsar_export');
    });

    // Kiểm Soát Phát Hành & Hoàn Tác (NXT-11: Release & Rollback Control Center)
    Route::prefix('release-control')->name('release.')->group(function () {
        Route::get('/', [AdminReleaseControlController::class, 'index'])->name('index');
        Route::post('/flags/{id}/toggle', [AdminReleaseControlController::class, 'toggleFlag'])->middleware('admin.manager')->name('toggle_flag');
        Route::post('/flags/{id}/kill-switch', [AdminReleaseControlController::class, 'triggerKillSwitch'])->middleware('admin.manager')->name('kill_switch');
        Route::post('/flags/{id}/reset-kill-switch', [AdminReleaseControlController::class, 'resetKillSwitch'])->middleware('admin.manager')->name('reset_kill_switch');
        Route::post('/safe-mode', [AdminReleaseControlController::class, 'toggleSafeMode'])->middleware('admin.super')->name('toggle_safe_mode');
    });

    // Phát Tin Thiệp Mời Tin Cậy & Chống Trùng (NXT-07: Reliable Messaging Dispatch)
    Route::prefix('messaging')->name('messaging.')->group(function () {
        Route::get('/', [AdminMessagingDispatchController::class, 'index'])->name('index');
        Route::get('/create', [AdminMessagingDispatchController::class, 'create'])->name('create');
        Route::post('/', [AdminMessagingDispatchController::class, 'store'])->name('store');
        Route::get('/{dispatch}', [AdminMessagingDispatchController::class, 'show'])->name('show');
        Route::post('/{dispatch}/dry-run', [AdminMessagingDispatchController::class, 'dryRun'])->name('dry_run');
        Route::post('/{dispatch}/dispatch', [AdminMessagingDispatchController::class, 'dispatchReal'])->middleware('admin.manager')->name('dispatch_real');
    });

    // Đài Quan Sát Chất Lượng & Chi Phí AI (NXT-12: AI Quality & Cost Observatory)
    Route::prefix('ai-observatory')->name('ai_observatory.')->group(function () {
        Route::get('/', [AdminAiObservatoryController::class, 'index'])->name('index');
        Route::post('/simulate', [AdminAiObservatoryController::class, 'simulate'])->middleware('admin.manager')->name('simulate');
        Route::post('/{id}/action', [AdminAiObservatoryController::class, 'recordAction'])->name('record_action');
    });

    // Kiểm Soát Sức Chống Chịu & Tải Cao (NXT-15: System Resilience & Chaos Cockpit)
    Route::prefix('resilience')->name('resilience.')->group(function () {
        Route::get('/', [AdminSystemResilienceController::class, 'index'])->name('index');
        Route::post('/simulate-checkin', [AdminSystemResilienceController::class, 'simulateCheckIn'])->middleware('admin.manager')->name('simulate_checkin');
        Route::post('/simulate-webhook', [AdminSystemResilienceController::class, 'simulateWebhook'])->middleware('admin.manager')->name('simulate_webhook');
        Route::post('/simulate-chaos', [AdminSystemResilienceController::class, 'simulateChaos'])->middleware('admin.manager')->name('simulate_chaos');
        Route::post('/create-snapshot', [AdminSystemResilienceController::class, 'createSnapshot'])->middleware('admin.super')->name('create_snapshot');
    });

    // Quản Trị Mạng Lưới Đối Tác & Quyết Toán Hoa Hồng Studio (Partner & Agency Governance)
    Route::prefix('partners')->name('partners.')->group(function () {
        Route::get('/', [AdminPartnerController::class, 'index'])->name('index');
        Route::get('/commissions', [AdminPartnerController::class, 'commissions'])->name('commissions');
        Route::get('/commissions/export', [AdminPartnerController::class, 'exportCommissions'])->name('commissions.export');
        Route::post('/commissions/{id}/settle', [AdminPartnerController::class, 'settle'])->middleware('admin.manager')->name('commissions.settle');
        Route::get('/{id}', [AdminPartnerController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [AdminPartnerController::class, 'approve'])->middleware('admin.manager')->name('approve');
        Route::post('/{id}/suspend', [AdminPartnerController::class, 'suspend'])->middleware('admin.manager')->name('suspend');
        Route::put('/{id}/commission-rate', [AdminPartnerController::class, 'updateRate'])->middleware('admin.manager')->name('update_rate');
    });

    // Quản Trị Blog & Cẩm Nang Cưới (Mục 7.11 DOCX & Dòng 400 PRD V3: Editorial Blog & SEO Content)
    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/', [AdminBlogController::class, 'index'])->name('index');
        Route::get('/create', [AdminBlogController::class, 'create'])->name('create');
        Route::post('/', [AdminBlogController::class, 'store'])->name('store');
        Route::get('/categories', [AdminBlogController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminBlogController::class, 'storeCategory'])->middleware('admin.manager')->name('categories.store');
        Route::get('/{id}/edit', [AdminBlogController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminBlogController::class, 'update'])->middleware('admin.manager')->name('update');
        Route::delete('/{id}', [AdminBlogController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
        Route::post('/{id}/toggle-publish', [AdminBlogController::class, 'togglePublish'])->middleware('admin.manager')->name('toggle_publish');
    });

    // Quản Lý Các Trang Nội Dung Tĩnh & Pháp Lý (Mục 7.12 DOCX & PRD V3: Admin Pages & Legal CMS)
    Route::prefix('pages')->name('pages.')->group(function () {
        Route::get('/', [AdminCmsPageController::class, 'index'])->name('index');
        Route::get('/create', [AdminCmsPageController::class, 'create'])->name('create');
        Route::post('/', [AdminCmsPageController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminCmsPageController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminCmsPageController::class, 'update'])->middleware('admin.manager')->name('update');
        Route::delete('/{id}', [AdminCmsPageController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
        Route::post('/{id}/toggle-publish', [AdminCmsPageController::class, 'togglePublish'])->middleware('admin.manager')->name('toggle_publish');
    });

    // Trung Tâm Chỉ Huy SEO, Redirects 301/302 & Sức Khỏe Tìm Kiếm (Mục 7.13 DOCX & Dòng 402 PRD V3)
    Route::prefix('seo')->name('seo.')->group(function () {
        Route::get('/', [AdminSeoController::class, 'index'])->name('index');
        Route::post('/redirects', [AdminSeoController::class, 'storeRedirect'])->middleware('admin.manager')->name('redirects.store');
        Route::put('/redirects/{id}', [AdminSeoController::class, 'updateRedirect'])->middleware('admin.manager')->name('redirects.update');
        Route::delete('/redirects/{id}', [AdminSeoController::class, 'destroyRedirect'])->middleware('admin.manager')->name('redirects.destroy');
        Route::post('/redirects/{id}/toggle', [AdminSeoController::class, 'toggleRedirect'])->middleware('admin.manager')->name('redirects.toggle');
        Route::post('/clear-sitemap', [AdminSeoController::class, 'clearSitemap'])->name('clear_sitemap');
    });

    // Phân Hệ Quản Trị Thông Báo Công Khai, Banner & Popup (Mục 147 PRD V3)
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AdminAnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [AdminAnnouncementController::class, 'create'])->name('create');
        Route::post('/', [AdminAnnouncementController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminAnnouncementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminAnnouncementController::class, 'update'])->middleware('admin.manager')->name('update');
        Route::delete('/{id}', [AdminAnnouncementController::class, 'destroy'])->middleware('admin.manager')->name('destroy');
        Route::post('/{id}/toggle', [AdminAnnouncementController::class, 'toggle'])->middleware('admin.manager')->name('toggle');
    });

    // Phân Hệ Quản Trị Mẫu Email & Tin Nhắn Thông Báo Tự Động (Mục 148 PRD V3 & Mục 18.2)
    Route::prefix('email-templates')->name('email-templates.')->group(function () {
        Route::get('/', [AdminEmailTemplateController::class, 'index'])->name('index');
        Route::get('/{template}/edit', [AdminEmailTemplateController::class, 'edit'])->name('edit');
        Route::put('/{template}', [AdminEmailTemplateController::class, 'update'])->middleware('admin.manager')->name('update');
        Route::get('/{template}/preview', [AdminEmailTemplateController::class, 'preview'])->name('preview');
        Route::post('/{template}/test', [AdminEmailTemplateController::class, 'sendTest'])->name('test');
        Route::post('/{template}/reset', [AdminEmailTemplateController::class, 'reset'])->middleware('admin.manager')->name('reset');
        Route::post('/{template}/toggle', [AdminEmailTemplateController::class, 'toggleActive'])->middleware('admin.manager')->name('toggle');
    });

    // Phân Hệ Quản Trị Chiến Dịch Tự Động Hóa Vòng Đời Cưới (Mục 149 PRD V3 & ADM-AUTO-01)
    Route::prefix('campaigns/automation')->name('campaigns.automation.')->group(function () {
        Route::get('/', [AdminLifecycleAutomationController::class, 'index'])->name('index');
        Route::put('/rules/{rule}', [AdminLifecycleAutomationController::class, 'update'])->middleware('admin.manager')->name('update');
        Route::post('/rules/{rule}/toggle', [AdminLifecycleAutomationController::class, 'toggle'])->middleware('admin.manager')->name('toggle');
        Route::post('/scan', [AdminLifecycleAutomationController::class, 'scan'])->middleware('admin.manager')->name('scan');
    });

    // Phân Hệ Quản Trị Ma Trận Phân Quyền & Bàn Giao Vai Trò Vận Hành (Mục 67 PRD V3 & ADM-RBC-01)
    Route::prefix('rbac')->name('rbac.')->group(function () {
        Route::get('/', [AdminRbacController::class, 'index'])->name('index');
        Route::get('/export', [AdminRbacController::class, 'export'])->name('export');
        Route::post('/staff', [AdminRbacController::class, 'storeStaff'])->middleware('admin.super')->name('store_staff');
        Route::post('/staff/{id}/role', [AdminRbacController::class, 'updateRole'])->middleware('admin.super')->name('update_role');
        Route::post('/staff/{id}/toggle-lock', [AdminRbacController::class, 'toggleLock'])->middleware('admin.super')->name('toggle_lock');
        Route::post('/matrix', [AdminRbacController::class, 'updateMatrix'])->middleware('admin.super')->name('update_matrix');
    });

    // Trung Tâm Quy Trình Vận Hành Tiêu Chuẩn & Sổ Tay Bàn Giao Hệ Thống (Admin Knowledge Center)
    Route::prefix('docs')->name('docs.')->group(function () {
        Route::get('/', [AdminDocsController::class, 'index'])->name('index');
        Route::get('/search', [AdminDocsController::class, 'search'])->name('search');
        Route::get('/{category}/{slug}', [AdminDocsController::class, 'show'])->name('show');
    });
});
