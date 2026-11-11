<?php

use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\BlogController as ClientBlogController;
use App\Http\Controllers\Client\CheckInController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\ClientSupportController;
use App\Http\Controllers\Client\CuratedInvitationController;
use App\Http\Controllers\Client\CustomDomainController;
use App\Http\Controllers\Client\CustomerReviewController;
use App\Http\Controllers\Client\FamilyApprovalController;
use App\Http\Controllers\Client\GiftManagementController;
use App\Http\Controllers\Client\GuestController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\InvitationAnalyticsController;
use App\Http\Controllers\Client\InvitationDeliveryController;
use App\Http\Controllers\Client\InvitationEditorController;
use App\Http\Controllers\Client\LiveCoordinationController;
use App\Http\Controllers\Client\LiveWallController;
use App\Http\Controllers\Client\MemoryVaultController;
use App\Http\Controllers\Client\OnboardingController;
use App\Http\Controllers\Client\PageController;
use App\Http\Controllers\Client\PaperInvitationScanController;
use App\Http\Controllers\Client\PhotoDropController;
use App\Http\Controllers\Client\PlanAdvisorController;
use App\Http\Controllers\Client\PremiumPreviewController;
use App\Http\Controllers\Client\PrivacyConsentController;
use App\Http\Controllers\Client\SeatingPlannerController;
use App\Http\Controllers\Client\SitemapController;
use App\Http\Controllers\Client\StationeryKitController;
use App\Http\Controllers\Client\TemplateController;
use App\Http\Controllers\Client\TimeCapsuleController;
use App\Http\Controllers\Client\WebhookController;
use App\Http\Controllers\Client\WeddingEventController;
use App\Http\Controllers\Client\WeddingKeepsakeController;
use App\Http\Controllers\Client\WeddingMediaController;
use App\Http\Controllers\Client\WeddingPlannerController;
use App\Http\Controllers\Client\WeddingRescheduleController;
use App\Http\Controllers\Client\WishManagementController;
use App\Http\Controllers\Partner\PartnerCommissionController;
use App\Http\Controllers\Partner\PartnerDashboardController;
use App\Http\Controllers\Partner\PartnerProfileController;
use App\Http\Controllers\Partner\PartnerRegistrationController;
use App\Http\Controllers\Partner\PartnerWeddingController;
use App\Services\CustomerDelightService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Mây Hẹn (mayhen.vn)
|--------------------------------------------------------------------------
*/

// SEO Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Client Marketing Website
Route::get('/', [HomeController::class, 'index'])->name('home');

// Cẩm Nang Cưới & Chuyên Đề Tạp Chí (Editorial Blog & SEO Content)
Route::prefix('cam-nang-cuoi')->name('blog.')->group(function () {
    Route::get('/', [ClientBlogController::class, 'index'])->name('index');
    Route::get('/{slug}', [ClientBlogController::class, 'show'])->name('show');
});

Route::prefix('mau-thiep')->name('templates.')->group(function () {
    Route::get('/', [TemplateController::class, 'index'])->name('index');
    Route::get('/{slug}', [TemplateController::class, 'show'])->name('show');
});

Route::get('/xem-thu/{slug}', [TemplateController::class, 'preview'])->name('templates.preview');
Route::get('/xem-thu-tinh-nang/{feature}', [PremiumPreviewController::class, 'preview'])->name('premium.preview');
Route::get('/mau-thiep/{slug}/assets/{path}', [TemplateController::class, 'asset'])->where('path', '.*')->name('templates.asset');
Route::post('/xem-thu/{slug}/wishes', [GuestController::class, 'submitDemoWish'])->middleware('throttle:guest_interactions')->name('templates.preview.wish');
Route::post('/xem-thu/{slug}/rsvp', [GuestController::class, 'submitDemoRsvp'])->middleware('throttle:guest_interactions')->name('templates.preview.rsvp');

// AI Curated Invitation System (7-Section Auto Design)
Route::get('/tao-thiep-tu-dong', [CuratedInvitationController::class, 'create'])->name('curated.create');
Route::get('/tao-thiep-tu-dong/khoi-tao', [CuratedInvitationController::class, 'create'])->name('curated.create.alias');
Route::post('/tao-thiep-tu-dong/khoi-tao', [CuratedInvitationController::class, 'generate'])->middleware('throttle:editor_actions')->name('curated.generate');
Route::get('/tao-thiep-tu-dong/xem-thu/{slug}', [CuratedInvitationController::class, 'preview'])->name('curated.preview');
Route::post('/api/curated/remix/{id}', [CuratedInvitationController::class, 'remix'])->middleware('throttle:30,1')->name('curated.remix');
Route::post('/api/curated/switch-variant', [CuratedInvitationController::class, 'switchVariant'])->middleware('throttle:30,1')->name('curated.switch_variant');

// Quick Creation & Live Editor
Route::get('/tao-thiep', [InvitationEditorController::class, 'create'])->name('invitation.create');
Route::post('/tao-thiep', [InvitationEditorController::class, 'store'])->middleware('throttle:editor_actions')->name('invitation.store');
Route::post('/tao-thiep/upload-asset', [InvitationEditorController::class, 'uploadAsset'])->middleware('throttle:upload_actions')->name('invitation.uploadAsset');
Route::post('/api/editor/autosave', [InvitationEditorController::class, 'autosave'])->middleware('throttle:editor_actions')->name('invitation.autosave');
Route::get('/api/wedding-musics', [InvitationEditorController::class, 'musics'])->name('invitation.musics');
Route::post('/api/editor/ai-story', [InvitationEditorController::class, 'generateAiStory'])->middleware('throttle:editor_actions')->name('invitation.aiStory');
Route::get('/api/editor/consistency-check/{id}', [InvitationEditorController::class, 'consistencyCheck'])->middleware(['auth', 'throttle:30,1'])->name('invitation.consistency_check');
Route::get('/api/editor/collision-check/{id}', [InvitationEditorController::class, 'collisionCheck'])->middleware(['auth', 'throttle:30,1'])->name('invitation.collision_check');
Route::get('/api/editor/readiness/{id}', [InvitationEditorController::class, 'readinessCheck'])->middleware(['auth', 'throttle:30,1'])->name('invitation.readiness_check');
Route::post('/api/editor/publish-impact/{id}', [InvitationEditorController::class, 'publishImpact'])->middleware(['auth', 'throttle:30,1'])->name('invitation.publish_impact');
Route::get('/api/editor/family-approval/{id}', [FamilyApprovalController::class, 'status'])->middleware(['auth', 'throttle:30,1'])->name('family_approval.status');
Route::get('/api/thiep/{id}/events', [WeddingEventController::class, 'apiEvents'])->name('invitation.events.api');
Route::get('/thiep/{slug}', [InvitationEditorController::class, 'show'])->name('invitation.show');
Route::post('/thiep/{slug}/mo-khoa', [InvitationEditorController::class, 'unlockPassword'])->middleware('throttle:20,1')->name('invitation.unlock');
Route::post('/thiep/{slug}/rsvp', [GuestController::class, 'submitRsvp'])->middleware('throttle:guest_interactions')->name('rsvp.submit');
Route::get('/thiep/{slug}/rsvp/responses', [GuestController::class, 'getRsvpResponses'])->name('invitation.rsvp.responses');
Route::get('/thiep/{slug}/wishes', [GuestController::class, 'getWishes'])->name('invitation.wish.index');
Route::post('/thiep/{slug}/wishes', [GuestController::class, 'submitWish'])->middleware('throttle:guest_interactions')->name('invitation.wish.store');
Route::post('/thiep/{slug}/wishes/{wishId}/reply', [GuestController::class, 'replyWish'])->middleware('throttle:guest_interactions')->name('invitation.wish.reply');

// Authentication
Route::get('/dang-nhap', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:auth_attempts')->name('login.submit');
Route::get('/dang-ky', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:auth_attempts')->name('register.submit');
Route::post('/dang-xuat', [AuthController::class, 'logout'])->middleware('throttle:30,1')->name('logout');

// Checkout & VietQR Payment
Route::get('/thanh-toan/{planSlug}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::get('/thanh-toan/thanh-cong/{orderCode}', [CheckoutController::class, 'firstSuccess'])->name('checkout.first_success');
Route::post('/api/checkout/apply-coupon', [CheckoutController::class, 'applyCoupon'])->middleware('throttle:30,1')->name('checkout.apply_coupon');
Route::post('/api/checkout/process', [CheckoutController::class, 'process'])->middleware('throttle:20,1')->name('checkout.process');
Route::get('/api/checkout/status/{orderCode}', [CheckoutController::class, 'checkStatus'])->middleware('throttle:60,1')->name('checkout.status');
Route::post('/api/payments/sepay/webhook', [CheckoutController::class, 'handleWebhook'])->middleware('throttle:60,1')->name('webhook.payment');
Route::post('/api/webhook/payment', [CheckoutController::class, 'handleWebhook'])->middleware('throttle:60,1')->name('webhook.payment.legacy');

// Onboarding Wizard Cho Cặp Đôi Mới (Section 21.2 Master PRD V3)
Route::middleware('auth')->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', [OnboardingController::class, 'index'])->name('index');
    Route::post('/luu-buoc', [OnboardingController::class, 'saveStep'])->middleware('throttle:30,1')->name('save_step');
    Route::post('/hoan-tat', [OnboardingController::class, 'complete'])->middleware('throttle:20,1')->name('complete');
    Route::post('/lam-lai', [OnboardingController::class, 'reset'])->name('reset');
});

// User Account Portal (Cặp đôi / Khách hàng)
Route::prefix('tai-khoan')->middleware('auth')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('dashboard');
    Route::get('/thiep', [AccountController::class, 'invitations'])->name('invitations');
    Route::post('/thiep/{id}/xuat-ban', [AccountController::class, 'togglePublish'])->name('invitations.toggle_publish');
    Route::get('/don-hang', [AccountController::class, 'orders'])->name('orders');
    Route::get('/ho-so', [AccountController::class, 'profile'])->name('profile');
    Route::post('/ho-so', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::post('/doi-mat-khau', [AccountController::class, 'updatePassword'])->name('password.update');

    // Trung Tâm Hỗ Trợ & Yêu Cầu Cặp Đôi (Client Support Desk)
    Route::get('/ho-tro', [ClientSupportController::class, 'index'])->name('support.index');
    Route::post('/ho-tro', [ClientSupportController::class, 'store'])->name('support.store');
    Route::get('/ho-tro/{ticketCode}', [ClientSupportController::class, 'show'])->name('support.show');
    Route::post('/ho-tro/{ticketCode}/phan-hoi', [ClientSupportController::class, 'reply'])->name('support.reply');

    // Cài Đặt Chi Tiết & Nhân Bản Thiệp Cưới
    Route::get('/thiep/{id}/cai-dat', [AccountController::class, 'settings'])->name('invitations.settings');
    Route::post('/thiep/{id}/cai-dat', [AccountController::class, 'updateSettings'])->name('invitations.settings.update');
    Route::post('/thiep/{id}/nhan-ban', [AccountController::class, 'duplicate'])->name('invitations.duplicate');
    Route::post('/thiep/{id}/khoi-phuc-khan-cap', [AccountController::class, 'rollback'])->name('invitations.rollback');
    Route::delete('/thiep/{id}', [AccountController::class, 'destroy'])->name('invitations.destroy');
    Route::get('/thiep/{id}/bo-du-phong-ngay-cuoi', [AccountController::class, 'resilienceKit'])->name('invitations.resilience_kit');
    Route::get('/thiep/{id}/vendor-brief', [AccountController::class, 'vendorBrief'])->name('invitations.vendor_brief');

    // Tên Miền Riêng & Vanity Subdomain (TASK-V3-03)
    Route::get('/thiep/{id}/ten-mien', [CustomDomainController::class, 'show'])->name('invitations.domain');
    Route::match(['post', 'delete'], '/thiep/{id}/ten-mien', [CustomDomainController::class, 'storeOrUpdate'])->name('invitations.domain.destroy');
    Route::post('/thiep/{id}/ten-mien/subdomain', [CustomDomainController::class, 'updateSubdomain'])->middleware('throttle:20,1')->name('invitations.domain.subdomain');
    Route::post('/thiep/{id}/ten-mien/custom', [CustomDomainController::class, 'updateCustomDomain'])->middleware('throttle:20,1')->name('invitations.domain.custom');
    Route::post('/thiep/{id}/ten-mien/xac-minh-dns', [CustomDomainController::class, 'verifyDns'])->middleware('throttle:30,1')->name('invitations.domain.verify');

    // Sổ Lưu Bút & Lời Chúc Cặp Đôi
    Route::get('/thiep/{id}/so-luu-but', [WishManagementController::class, 'index'])->name('wishes.index');
    Route::get('/thiep/{id}/so-luu-but/xuat-csv', [WishManagementController::class, 'exportCsv'])->name('wishes.export');
    Route::get('/thiep/{id}/so-luu-but/in', [WishManagementController::class, 'print'])->name('wishes.print');
    Route::post('/thiep/{id}/so-luu-but/{wishId}/phan-hoi', [WishManagementController::class, 'reply'])->name('wishes.reply');
    Route::post('/thiep/{id}/so-luu-but/{wishId}/an-hien', [WishManagementController::class, 'toggleVisibility'])->name('wishes.toggle_visibility');
    Route::post('/thiep/{id}/so-luu-but/{wishId}/ghim', [WishManagementController::class, 'togglePin'])->name('wishes.toggle_pin');
    Route::post('/thiep/{id}/so-luu-but/che-do-kiem-duyet', [WishManagementController::class, 'updateModerationMode'])->name('wishes.update_moderation_mode');
    Route::post('/thiep/{id}/so-luu-but/duyet-tat-ca', [WishManagementController::class, 'approveAll'])->name('wishes.approve_all');
    Route::delete('/thiep/{id}/so-luu-but/{wishId}', [WishManagementController::class, 'destroy'])->name('wishes.destroy');

    // Sổ Mừng Cưới & Quản Lý Tiền Mừng
    Route::get('/thiep/{id}/so-mung-cuoi', [GiftManagementController::class, 'index'])->name('gifts.index');
    Route::post('/thiep/{id}/so-mung-cuoi', [GiftManagementController::class, 'store'])->name('gifts.store');
    Route::post('/thiep/{id}/so-mung-cuoi/bat-tat-qr', [GiftManagementController::class, 'toggleQr'])->name('gifts.toggle_qr');
    Route::post('/thiep/{id}/so-mung-cuoi/tai-khoan-ngan-hang', [GiftManagementController::class, 'updateBanking'])->name('gifts.update_banking');
    Route::put('/thiep/{id}/so-mung-cuoi/{giftId}', [GiftManagementController::class, 'update'])->name('gifts.update');
    Route::post('/thiep/{id}/so-mung-cuoi/{giftId}/cam-on', [GiftManagementController::class, 'toggleAcknowledge'])->name('gifts.toggle_ack');
    Route::delete('/thiep/{id}/so-mung-cuoi/{giftId}', [GiftManagementController::class, 'destroy'])->name('gifts.destroy');
    Route::get('/thiep/{id}/so-mung-cuoi/xuat-csv', [GiftManagementController::class, 'exportCsv'])->name('gifts.export');
    Route::get('/thiep/{id}/so-mung-cuoi/in', [GiftManagementController::class, 'print'])->name('gifts.print');

    // Quản Lý Đa Sự Kiện Cưới (Multi-Event Architecture & Timelines - Master PRD V3)
    Route::get('/thiep/{id}/su-kien', [WeddingEventController::class, 'index'])->name('events.index');
    Route::post('/thiep/{id}/su-kien', [WeddingEventController::class, 'store'])->name('events.store');
    Route::put('/thiep/{id}/su-kien/{eventId}', [WeddingEventController::class, 'update'])->name('events.update');
    Route::delete('/thiep/{id}/su-kien/{eventId}', [WeddingEventController::class, 'destroy'])->name('events.destroy');

    // Tự Động Hóa & Outbound Webhook (Master PRD V3)
    Route::get('/thiep/{id}/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/thiep/{id}/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::put('/thiep/{id}/webhooks/{webhookId}', [WebhookController::class, 'update'])->name('webhooks.update');
    Route::delete('/thiep/{id}/webhooks/{webhookId}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/thiep/{id}/webhooks/{webhookId}/ping', [WebhookController::class, 'ping'])->name('webhooks.ping');

    // Kế Hoạch & Sổ Dự Toán Ngân Sách Cưới (Wedding Planner & Budget Engine - Master PRD V3 Section 36)
    Route::get('/thiep/{id}/ke-hoach-cuoi', [WeddingPlannerController::class, 'index'])->name('planner.index');
    Route::post('/thiep/{id}/ke-hoach-cuoi/khoi-tao-mau', [WeddingPlannerController::class, 'seedDefaults'])->name('planner.seed_defaults');
    Route::post('/thiep/{id}/ke-hoach-cuoi/cong-viec', [WeddingPlannerController::class, 'storeTask'])->name('planner.tasks.store');
    Route::put('/thiep/{id}/ke-hoach-cuoi/cong-viec/{taskId}', [WeddingPlannerController::class, 'updateTask'])->name('planner.tasks.update');
    Route::post('/thiep/{id}/ke-hoach-cuoi/cong-viec/{taskId}/hoan-thanh', [WeddingPlannerController::class, 'toggleTask'])->name('planner.tasks.toggle');
    Route::delete('/thiep/{id}/ke-hoach-cuoi/cong-viec/{taskId}', [WeddingPlannerController::class, 'destroyTask'])->name('planner.tasks.destroy');
    Route::post('/thiep/{id}/ke-hoach-cuoi/ngan-sach', [WeddingPlannerController::class, 'storeBudgetItem'])->name('planner.budget.store');
    Route::put('/thiep/{id}/ke-hoach-cuoi/ngan-sach/{itemId}', [WeddingPlannerController::class, 'updateBudgetItem'])->name('planner.budget.update');
    Route::delete('/thiep/{id}/ke-hoach-cuoi/ngan-sach/{itemId}', [WeddingPlannerController::class, 'destroyBudgetItem'])->name('planner.budget.destroy');
    Route::post('/thiep/{id}/ke-hoach-cuoi/muc-tieu-ngan-sach', [WeddingPlannerController::class, 'updateTargetBudget'])->name('planner.budget.target');
    Route::get('/thiep/{id}/ke-hoach-cuoi/xuat-ngan-sach-csv', [WeddingPlannerController::class, 'exportBudgetCsv'])->name('planner.budget.export');
    Route::post('/thiep/{id}/ke-hoach-cuoi/nha-cung-cap', [WeddingPlannerController::class, 'storeVendor'])->name('planner.vendors.store');
    Route::put('/thiep/{id}/ke-hoach-cuoi/nha-cung-cap/{vendorId}', [WeddingPlannerController::class, 'updateVendor'])->name('planner.vendors.update');
    Route::delete('/thiep/{id}/ke-hoach-cuoi/nha-cung-cap/{vendorId}', [WeddingPlannerController::class, 'destroyVendor'])->name('planner.vendors.destroy');
    Route::post('/thiep/{id}/ke-hoach-cuoi/nha-cung-cap/{vendorId}/thanh-toan-coc', [WeddingPlannerController::class, 'depositVendor'])->name('planner.vendors.deposit');
    Route::post('/thiep/{id}/ke-hoach-cuoi/khoi-tao-nha-cung-cap-mau', [WeddingPlannerController::class, 'seedDefaultVendors'])->name('planner.vendors.seed_defaults');
    Route::get('/thiep/{id}/ke-hoach-cuoi/xuat-nha-cung-cap-csv', [WeddingPlannerController::class, 'exportVendorsCsv'])->name('planner.vendors.export');

    // Thông Báo Dời Lịch Cưới & Tái Xác Nhận RSVP (Luồng G - Master PRD V3 Section 61)
    Route::get('/thiep/{id}/thong-bao-doi-lich', [WeddingRescheduleController::class, 'index'])->name('reschedule.index');
    Route::post('/thiep/{id}/thong-bao-doi-lich/thuc-hien', [WeddingRescheduleController::class, 'submit'])->name('reschedule.submit');
    Route::get('/api/thiep/{id}/thong-bao-doi-lich/mau-tin/{guestId}', [WeddingRescheduleController::class, 'message'])->name('reschedule.message');

    // Kho Tệp Media & Quản Lý Dung Lượng Lưu Trữ Cưới (Master PRD V3 Section 26)
    Route::get('/thiep/{id}/kho-tep', [WeddingMediaController::class, 'index'])->name('wedding_media.index');
    Route::delete('/thiep/{id}/kho-tep', [WeddingMediaController::class, 'destroy'])->name('wedding_media.destroy');
    Route::get('/api/thiep/{id}/dung-luong', [WeddingMediaController::class, 'quota'])->name('wedding_media.quota');

    // Báo Cáo Phân Tích Hiệu Suất Tương Tác & Lượng Truy Cập Thiệp Cưới (Master PRD V3 Section 19 & 42)
    Route::get('/thiep/{id}/thong-ke', [InvitationAnalyticsController::class, 'index'])->name('invitations.analytics');
    Route::get('/thiep/{id}/thong-ke/xuat-csv', [InvitationAnalyticsController::class, 'exportCsv'])->name('invitations.analytics.export');

    // Lưu Giữ Kỷ Niệm Hậu Sự Kiện & Chế Độ Kỷ Niệm (Mục 2.8 Master Prompt & Section 36 PRD V3)
    Route::get('/thiep/{id}/luu-giu-ky-niem', [WeddingKeepsakeController::class, 'index'])->name('keepsake.index');
    Route::post('/thiep/{id}/luu-giu-ky-niem', [WeddingKeepsakeController::class, 'update'])->name('keepsake.update');
    Route::post('/thiep/{id}/luu-giu-ky-niem/toggle', [WeddingKeepsakeController::class, 'toggle'])->name('keepsake.toggle');
    Route::get('/thiep/{id}/luu-giu-ky-niem/xuat-so-luu-niem', [WeddingKeepsakeController::class, 'exportBook'])->name('keepsake.export');
    Route::get('/api/thiep/{id}/luu-giu-ky-niem/tin-nhan-cam-on/{guestId}', [WeddingKeepsakeController::class, 'getThankYouMessage'])->name('keepsake.thank_you_message');

    // Bóc Tách Thông Tin Thiệp Cưới Giấy Bằng AI & Rà Soát Thủ Công (Mục 2.9 Master Prompt & Section 21 PRD V3)
    Route::get('/thiep/{id}/quet-thiep-giay', [PaperInvitationScanController::class, 'create'])->name('paper_scan.create');
    Route::post('/thiep/{id}/quet-thiep-giay', [PaperInvitationScanController::class, 'store'])->middleware('throttle:20,1')->name('paper_scan.store');
    Route::get('/thiep/{id}/quet-thiep-giay/{scanId}/ra-soat', [PaperInvitationScanController::class, 'review'])->name('paper_scan.review');
    Route::post('/thiep/{id}/quet-thiep-giay/{scanId}/ap-dung', [PaperInvitationScanController::class, 'apply'])->name('paper_scan.apply');

    // Bộ Ấn Phẩm Đồng Bộ Đa Định Dạng (Mục 2.7 Master Prompt & Section 38 PRD V3)
    Route::get('/thiep/{id}/bo-thiep-dong-bo', [StationeryKitController::class, 'index'])->name('stationery.index');
    Route::post('/thiep/{id}/bo-thiep-dong-bo/log-download', [StationeryKitController::class, 'logDownload'])->middleware('throttle:30,1')->name('stationery.log_download');

    // Gửi Đánh Giá & Cảm Nhận Khách Hàng (Mục 7.14 DOCX)
    Route::post('/thiep/{id}/danh-gia', [CustomerReviewController::class, 'store'])->middleware('throttle:10,1')->name('reviews.store');
});

// Guest Management & RSVP
Route::prefix('tai-khoan')->group(function () {
    Route::get('/thiep/{id}/khach-moi', [GuestController::class, 'index'])->name('guests.index');
    Route::post('/thiep/{id}/khach-moi', [GuestController::class, 'store'])->middleware('throttle:60,1')->name('guests.store');
    Route::post('/thiep/{id}/khach-moi/nhap-nhanh', [GuestController::class, 'batchStore'])->middleware('throttle:30,1')->name('guests.batch_store');
    Route::get('/thiep/{id}/khach-moi/mau-csv', [GuestController::class, 'downloadCsvTemplate'])->name('guests.csv_template');
    Route::post('/thiep/{id}/khach-moi/nhap-csv', [GuestController::class, 'importCsv'])->middleware('throttle:30,1')->name('guests.import_csv');
    Route::get('/thiep/{id}/khach-moi/xuat-csv', [GuestController::class, 'exportCsv'])->name('guests.export');
    Route::get('/thiep/{id}/khach-moi/in', [GuestController::class, 'print'])->name('guests.print');
    Route::get('/thiep/{id}/khach-moi/kiem-tra-truoc-gui', [GuestController::class, 'preSendValidation'])->middleware('throttle:30,1')->name('guests.pre_send_validation');
    Route::get('/thiep/{id}/khach-moi/ke-hoach-nhac-nho', [GuestController::class, 'reminderPlanner'])->middleware('throttle:30,1')->name('guests.reminder_planner');
    Route::get('/thiep/{id}/khach-moi/loi-cam-on-sau-cuoi', [GuestController::class, 'thankYouBuilder'])->middleware('throttle:30,1')->name('guests.thankyou_builder');
    Route::put('/khach-moi/{id}', [GuestController::class, 'update'])->middleware('throttle:60,1')->name('guests.update');
    Route::post('/khach-moi/{id}/toggle-sent', [GuestController::class, 'toggleSent'])->middleware('throttle:60,1')->name('guests.toggle_sent');
    Route::post('/khach-moi/{id}/rsvp-thu-cong', [GuestController::class, 'manualRsvp'])->middleware('throttle:60,1')->name('guests.manual_rsvp');
    Route::post('/khach-moi/{id}/tai-tao-token', [GuestController::class, 'regenerateToken'])->middleware('throttle:30,1')->name('guests.regenerate_token');
    Route::delete('/khach-moi/{id}', [GuestController::class, 'destroy'])->name('guests.destroy');

    // Phân Phối Thiệp Cưới & Quản Lý Kênh Gửi (Wedding Invitation Delivery & Campaign Dispatch - Section 30 Master PRD V3)
    Route::get('/thiep/{id}/phan-phoi/xem-truoc/{guestId}', [InvitationDeliveryController::class, 'preview'])->middleware('throttle:60,1')->name('guests.delivery.preview');
    Route::post('/khach-moi/{guestId}/phan-phoi', [InvitationDeliveryController::class, 'dispatchSingle'])->middleware('throttle:60,1')->name('guests.delivery.single');
    Route::post('/thiep/{id}/phan-phoi-hang-loat', [InvitationDeliveryController::class, 'dispatchBatch'])->middleware('throttle:30,1')->name('guests.delivery.batch');
    Route::get('/thiep/{id}/phan-phoi/thong-ke', [InvitationDeliveryController::class, 'stats'])->middleware('throttle:60,1')->name('guests.delivery.stats');
    Route::get('/thiep/{id}/phan-phoi/lich-su', [InvitationDeliveryController::class, 'history'])->middleware('throttle:60,1')->name('guests.delivery.history');

    // Bảng Điều Khiển Ngày Cưới Realtime (Live Command Center - TASK-V2-02)
    Route::get('/thiep/{id}/dieu-phoi', [LiveCoordinationController::class, 'index'])->name('coordination.index');
    Route::get('/api/thiep/{id}/dieu-phoi/stats', [LiveCoordinationController::class, 'liveStats'])->middleware('throttle:120,1')->name('coordination.stats');

    // Két Lưu Trữ Kỷ Niệm Số & Sách Kỷ Yếu (Digital Memory Vault - TASK-V2-03 & TASK-V2-04)
    Route::get('/thiep/{id}/ky-niem', [MemoryVaultController::class, 'settings'])->name('memory.settings');
    Route::post('/thiep/{id}/ky-niem', [MemoryVaultController::class, 'updateSettings'])->middleware('throttle:30,1')->name('memory.updateSettings');
    Route::post('/thiep/{id}/hau-su-kien', [MemoryVaultController::class, 'postWeddingLifecycle'])->middleware('throttle:30,1')->name('memory.post_wedding_lifecycle');
    Route::post('/thiep/{id}/hau-su-kien/xoa-du-lieu-nhay-cam', [MemoryVaultController::class, 'purgeGuestPii'])->middleware('throttle:10,1')->name('memory.purge_guest_pii');
    Route::get('/thiep/{id}/sach-ky-yeu', [MemoryVaultController::class, 'keepsakeBook'])->name('memory.keepsake_book');
    Route::get('/thiep/{id}/xuat-ky-niem-ngoai-tuyen', [MemoryVaultController::class, 'offlineArchive'])->middleware('throttle:10,1')->name('memory.offline_archive');

    // Quản Lý Album Ảnh Khách Chụp Ngày Cưới (Live Guest Photo Drop - TASK-V2-05)
    Route::get('/thiep/{id}/album-khach', [PhotoDropController::class, 'manage'])->name('photo_drop.manage');
    Route::post('/thiep/{id}/album-khach/{photoId}/duyet', [PhotoDropController::class, 'toggleApproval'])->name('photo_drop.toggleApproval');
    Route::post('/thiep/{id}/album-khach/duyet-tat-ca', [PhotoDropController::class, 'approveAll'])->name('photo_drop.approveAll');
    Route::delete('/thiep/{id}/album-khach/{photoId}', [PhotoDropController::class, 'destroy'])->name('photo_drop.destroy');
    Route::get('/thiep/{id}/album-khach/tai-zip', [PhotoDropController::class, 'downloadZip'])->name('photo_drop.downloadZip');

    // Sơ Đồ Xếp Bàn Tiệc Trực Quan (Seating Planner Studio - TASK-V3-02)
    Route::get('/thiep/{id}/so-do-ban-tiec', [SeatingPlannerController::class, 'index'])->name('seating.index');
    Route::post('/thiep/{id}/so-do-ban-tiec/ban', [SeatingPlannerController::class, 'storeTable'])->middleware('throttle:30,1')->name('seating.storeTable');
    Route::put('/thiep/{id}/so-do-ban-tiec/ban/{tableId}', [SeatingPlannerController::class, 'updateTable'])->middleware('throttle:30,1')->name('seating.updateTable');
    Route::delete('/thiep/{id}/so-do-ban-tiec/ban/{tableId}', [SeatingPlannerController::class, 'deleteTable'])->name('seating.deleteTable');
    Route::post('/api/thiep/{id}/so-do-ban-tiec/vi-tri', [SeatingPlannerController::class, 'updatePositions'])->middleware('throttle:60,1')->name('seating.updatePositions');
    Route::post('/api/thiep/{id}/so-do-ban-tiec/xep-khach', [SeatingPlannerController::class, 'assignGuest'])->middleware('throttle:60,1')->name('seating.assignGuest');
    Route::get('/thiep/{id}/so-do-ban-tiec/in', [SeatingPlannerController::class, 'printChart'])->name('seating.print');
    Route::get('/api/thiep/{id}/so-do-ban-tiec/suc-khoe', [SeatingPlannerController::class, 'healthCheck'])->middleware('throttle:30,1')->name('seating.health_check');

    // Bàn Giao Ca Trực Lễ Tân (Staff Shift Handover - TASK-VAL-23)
    Route::get('/api/thiep/{id}/ca-truc-le-tan', [CheckInController::class, 'getShifts'])->middleware('throttle:60,1')->name('checkin.shifts.index');
    Route::post('/api/thiep/{id}/ca-truc-le-tan', [CheckInController::class, 'saveShift'])->middleware('throttle:30,1')->name('checkin.shifts.store');
    Route::delete('/api/thiep/{id}/ca-truc-le-tan/{shiftId}', [CheckInController::class, 'deleteShift'])->name('checkin.shifts.delete');

    // Bảng Radar Sẵn Sàng Tuần Lễ Cưới & Cam Kết Hỗ Trợ Kỹ Thuật (TASK-VAL-28)
    Route::get('/thiep/{id}/tuan-le-cuoi', [AccountController::class, 'weddingWeekReadiness'])->middleware('auth')->name('invitations.wedding_week');

    // Bức Tường Trình Chiếu Lễ Cưới Trực Tiếp (Live Wedding Projection Wall - TASK-V3-04)
    Route::get('/thiep/{id}/man-hinh-chieu', [LiveWallController::class, 'settings'])->middleware('auth')->name('live_wall.settings');
    Route::post('/thiep/{id}/man-hinh-chieu', [LiveWallController::class, 'updateSettings'])->middleware(['auth', 'throttle:30,1'])->name('live_wall.settings.update');

    // Hộp Thời Gian Kỷ Niệm Tương Lai (Wedding Time Capsule - TASK-V3-05)
    Route::get('/thiep/{id}/hop-thoi-gian', [TimeCapsuleController::class, 'index'])->middleware('auth')->name('account.time_capsule.index');
    Route::post('/thiep/{id}/hop-thoi-gian', [TimeCapsuleController::class, 'store'])->middleware(['auth', 'throttle:30,1'])->name('account.time_capsule.store');
    Route::post('/thiep/{id}/hop-thoi-gian/{capsuleId}/mo-khoa', [TimeCapsuleController::class, 'unseal'])->middleware('auth')->name('account.time_capsule.unseal');
    Route::delete('/thiep/{id}/hop-thoi-gian/{capsuleId}', [TimeCapsuleController::class, 'destroy'])->middleware('auth')->name('account.time_capsule.destroy');

    // Nhận Thiệp Cưới Từ Studio Bàn Giao
    Route::post('/nhan-thiep-doi-tac', [PartnerWeddingController::class, 'claim'])->middleware('throttle:15,1')->name('account.claim_partner_invitation');

    // Trung Tâm Minh Bạch Quyền Riêng Tư & Bảo Mật Dữ Liệu (Privacy & Consent Center - TASK-VAL-24)
    Route::get('/thiep/{id}/trung-tam-quyen-rieng-tu', [PrivacyConsentController::class, 'show'])->name('privacy.show');
    Route::post('/thiep/{id}/trung-tam-quyen-rieng-tu', [PrivacyConsentController::class, 'update'])->middleware('throttle:30,1')->name('privacy.update');
    Route::get('/api/thiep/{id}/quyen-rieng-tu/kiem-toan', [PrivacyConsentController::class, 'auditReport'])->middleware('throttle:60,1')->name('privacy.audit');
});

// Bức Tường Trình Chiếu Lễ Cưới Trực Tiếp (Live Wedding Projection Wall Public)
Route::get('/trinh-chieu/{slug?}', [LiveWallController::class, 'show'])->name('live_wall.show');
Route::post('/trinh-chieu/{slug}/xac-thuc-pin', [LiveWallController::class, 'verifyPin'])->middleware('throttle:15,1')->name('live_wall.verify_pin');
Route::get('/api/trinh-chieu/{slug}/feed', [LiveWallController::class, 'liveFeed'])->middleware('throttle:120,1')->name('live_wall.feed');

// Trang Kỷ Niệm Ngày Cưới Công Khai (Digital Memory Vault Public)
Route::get('/ky-niem/{slug?}', [MemoryVaultController::class, 'show'])->name('memory.vault');

// Live Guest Photo Drop (Khách quét mã QR tại bàn tiệc tải ảnh tự nhiên)
Route::get('/photo-drop/{slug?}', [PhotoDropController::class, 'show'])->name('photo_drop.show');
Route::post('/api/photo-drop/{slug}/upload', [PhotoDropController::class, 'upload'])->middleware('throttle:20,1')->name('photo_drop.upload');

// Hộp Thời Gian Kỷ Niệm Tương Lai Công Khai (Wedding Time Capsule Public - TASK-V3-05)
Route::get('/hop-thoi-gian/{slug?}', [TimeCapsuleController::class, 'showPublic'])->name('time_capsule.public');
Route::get('/thiep/{slug}/hop-thoi-gian', [TimeCapsuleController::class, 'showPublic'])->name('time_capsule.public_slug');
Route::post('/thiep/{slug}/hop-thoi-gian', [TimeCapsuleController::class, 'storeGuest'])->middleware('throttle:20,1')->name('time_capsule.guest.store');

// Cổng Tiếp Tân Điểm Danh & Quét Mã QR Ngày Cưới (QR Check-in & Table Finder - TASK-V2-01)
Route::get('/diem-danh-tiec/{slug?}', [CheckInController::class, 'showPortal'])->name('checkin.portal');
Route::post('/diem-danh-tiec/{slug}/xac-thuc-pin', [CheckInController::class, 'verifyPin'])->middleware('throttle:15,1')->name('checkin.verifyPin');
Route::post('/api/diem-danh-tiec/{slug}/quet', [CheckInController::class, 'processScan'])->middleware('throttle:120,1')->name('checkin.scan');
Route::get('/api/diem-danh-tiec/{slug}/tim-nhanh', [CheckInController::class, 'quickSearch'])->middleware('throttle:60,1')->name('checkin.quickSearch');
Route::post('/api/diem-danh-tiec/{slug}/huy/{guestId}', [CheckInController::class, 'undo'])->middleware('throttle:60,1')->name('checkin.undo');
Route::post('/api/diem-danh-tiec/{slug}/ban-tiec/{guestId}', [CheckInController::class, 'updateTable'])->middleware('throttle:60,1')->name('checkin.updateTable');
Route::post('/diem-danh-tiec/{slug}/dien-tap/bat', [CheckInController::class, 'enableDryRun'])->name('checkin.dry_run.enable');
Route::post('/diem-danh-tiec/{slug}/dien-tap/tat', [CheckInController::class, 'disableDryRun'])->name('checkin.dry_run.disable');
Route::post('/diem-danh-tiec/{slug}/dien-tap/lam-moi', [CheckInController::class, 'resetDryRun'])->name('checkin.dry_run.reset');

// Bàn Xử Lý Ngoại Lệ Ngày Cưới (RSVP Exception Desk - TASK-VAL-21)
Route::post('/api/diem-danh-tiec/{slug}/ngoai-le/khach-phat-sinh', [CheckInController::class, 'walkInGuest'])->middleware('throttle:60,1')->name('checkin.exception.walk_in');
Route::post('/api/diem-danh-tiec/{slug}/ngoai-le/dieu-chinh-suat/{guestId}', [CheckInController::class, 'adjustAttendees'])->middleware('throttle:60,1')->name('checkin.exception.adjust_attendees');
Route::post('/api/diem-danh-tiec/{slug}/ngoai-le/chuyen-tham-du/{guestId}', [CheckInController::class, 'convertDeclined'])->middleware('throttle:60,1')->name('checkin.exception.convert_declined');

// Bàn Giao Ca Trực Lễ Tân (Staff Shift Handover - TASK-VAL-23)
Route::get('/api/diem-danh-tiec/{slug}/bien-ban-ban-giao', [CheckInController::class, 'getHandoverSummary'])->middleware('throttle:60,1')->name('checkin.shift.summary');
Route::post('/api/diem-danh-tiec/{slug}/ban-giao-ca', [CheckInController::class, 'submitShiftHandover'])->middleware('throttle:30,1')->name('checkin.shift.handover');

// Thẻ VIP & Tra Cứu Bàn Tiệc Khi Quét QR Cá Nhân
Route::get('/diem-danh/{token}', [CheckInController::class, 'directScan'])->name('checkin.direct');
Route::get('/khach-moi/{guestToken}', [CheckInController::class, 'guestConcierge'])->name('guest.concierge');
Route::get('/khach-moi/{guestToken}/lich-ics', [CheckInController::class, 'downloadIcs'])->name('guest.concierge.ics');

// Rà Soát Lễ Nghi & Duyệt Thiệp Gia Đình Hai Nhà (Two-Family Review - TASK-VAL-19)
Route::get('/gia-dinh-duyet/{token}', [FamilyApprovalController::class, 'review'])->name('family_approval.review');
Route::post('/gia-dinh-duyet/{token}/quyet-dinh', [FamilyApprovalController::class, 'submitDecision'])->middleware('throttle:20,1')->name('family_approval.submit');

// Marketing Informational Pages
Route::get('/dich-vu', [PageController::class, 'services'])->name('services');
Route::get('/bang-gia', [PageController::class, 'pricing'])->name('pricing');
Route::get('/tu-van-goi-cuoi', [PlanAdvisorController::class, 'index'])->name('pricing.advisor');
Route::post('/api/tu-van-goi-cuoi/phan-tich', [PlanAdvisorController::class, 'analyze'])->middleware('throttle:30,1')->name('pricing.advisor.analyze');

// Vòng Lặp Phản Hồi Trải Nghiệm Khách Hàng (Customer Delight Feedback API - NXT-13)
Route::post('/api/feedback/submit', function (Request $request) {
    $validated = $request->validate([
        'milestone' => 'required|string|in:after_publish,after_payment,post_wedding,general',
        'rating' => 'required|integer|min:1|max:5',
        'category' => 'required|string|max:50',
        'comment' => 'nullable|string|max:1000',
        'wedding_id' => 'nullable|integer|exists:weddings,id',
    ]);

    $feedback = CustomerDelightService::recordFeedback($validated, $request->user());

    return response()->json([
        'success' => true,
        'message' => 'Cảm ơn bạn đã gửi ý kiến phản hồi cho Mây Hẹn!',
        'feedback_id' => $feedback->id,
    ]);
})->middleware('throttle:20,1')->name('feedback.submit');
Route::get('/huong-dan', [PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/ve-may-hen', [PageController::class, 'about'])->name('about');
Route::get('/lien-he', [PageController::class, 'contact'])->name('contact');
Route::post('/lien-he', [PageController::class, 'submitContact'])->middleware('throttle:10,1')->name('contact.submit');
Route::get('/cau-hoi-thuong-gap', [PageController::class, 'faq'])->name('faq');
Route::get('/dieu-khoan-dich-vu', [PageController::class, 'terms'])->name('terms');
Route::get('/chinh-sach-bao-mat', [PageController::class, 'privacy'])->name('privacy');
Route::get('/trang/{slug}', [PageController::class, 'showPage'])->name('page.show');

// Đăng Ký Đối Tác Studio & Wedding Planner (Public)
Route::get('/doi-tac/dang-ky', [PartnerRegistrationController::class, 'create'])->name('partner.register');
Route::post('/doi-tac/dang-ky', [PartnerRegistrationController::class, 'store'])->middleware('throttle:10,1')->name('partner.register.store');

// Cổng Quản Trị Đối Tác Studio B2B (Protected by partner middleware)
Route::prefix('doi-tac')->name('partner.')->middleware('partner')->group(function () {
    Route::get('/', [PartnerDashboardController::class, 'index'])->name('index');
    Route::get('/tong-quan', [PartnerDashboardController::class, 'index'])->name('dashboard');

    // Quản lý danh sách thiệp cưới của khách hàng
    Route::get('/danh-sach-thiep', [PartnerWeddingController::class, 'index'])->name('weddings.index');
    Route::get('/tao-thiep', [PartnerWeddingController::class, 'create'])->name('weddings.create');
    Route::post('/tao-thiep', [PartnerWeddingController::class, 'store'])->middleware('throttle:30,1')->name('weddings.store');
    Route::get('/thiep/{id}/ban-giao', [PartnerWeddingController::class, 'handover'])->name('weddings.handover');
    Route::post('/thiep/{id}/ban-giao/lam-moi', [PartnerWeddingController::class, 'regenerateHandoverCode'])->name('weddings.regenerate');
    Route::post('/thiep/{id}/ban-giao/thu-hoi', [PartnerWeddingController::class, 'revokeHandoverCode'])->name('weddings.revoke');

    // Quản lý hoa hồng & chiết khấu B2B
    Route::get('/hoa-hong', [PartnerCommissionController::class, 'index'])->name('commissions.index');

    // Cài đặt hồ sơ Studio & Co-branding
    Route::get('/ho-so', [PartnerProfileController::class, 'edit'])->name('profile');
    Route::post('/ho-so', [PartnerProfileController::class, 'update'])->middleware('throttle:30,1')->name('profile.update');
});

// Admin Management Panel
Route::prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
