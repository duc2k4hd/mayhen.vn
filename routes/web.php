<?php

use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\CheckInController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\CustomDomainController;
use App\Http\Controllers\Client\GiftManagementController;
use App\Http\Controllers\Client\GuestController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\InvitationEditorController;
use App\Http\Controllers\Client\LiveCoordinationController;
use App\Http\Controllers\Client\LiveWallController;
use App\Http\Controllers\Client\MemoryVaultController;
use App\Http\Controllers\Client\PageController;
use App\Http\Controllers\Client\PhotoDropController;
use App\Http\Controllers\Client\SeatingPlannerController;
use App\Http\Controllers\Client\SitemapController;
use App\Http\Controllers\Client\TemplateController;
use App\Http\Controllers\Client\TimeCapsuleController;
use App\Http\Controllers\Client\WishManagementController;
use App\Http\Controllers\Partner\PartnerCommissionController;
use App\Http\Controllers\Partner\PartnerDashboardController;
use App\Http\Controllers\Partner\PartnerProfileController;
use App\Http\Controllers\Partner\PartnerRegistrationController;
use App\Http\Controllers\Partner\PartnerWeddingController;
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

Route::prefix('mau-thiep')->name('templates.')->group(function () {
    Route::get('/', [TemplateController::class, 'index'])->name('index');
    Route::get('/{slug}', [TemplateController::class, 'show'])->name('show');
});

Route::get('/xem-thu/{slug}', [TemplateController::class, 'preview'])->name('templates.preview');
Route::get('/mau-thiep/{slug}/assets/{path}', [TemplateController::class, 'asset'])->where('path', '.*')->name('templates.asset');
Route::post('/xem-thu/{slug}/wishes', [GuestController::class, 'submitDemoWish'])->middleware('throttle:guest_interactions')->name('templates.preview.wish');
Route::post('/xem-thu/{slug}/rsvp', [GuestController::class, 'submitDemoRsvp'])->middleware('throttle:guest_interactions')->name('templates.preview.rsvp');

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
Route::post('/api/checkout/process', [CheckoutController::class, 'process'])->middleware('throttle:20,1')->name('checkout.process');
Route::get('/api/checkout/status/{orderCode}', [CheckoutController::class, 'checkStatus'])->name('checkout.status');
Route::post('/api/payments/sepay/webhook', [CheckoutController::class, 'handleWebhook'])->middleware('throttle:60,1')->name('webhook.payment');
Route::post('/api/webhook/payment', [CheckoutController::class, 'handleWebhook'])->middleware('throttle:60,1')->name('webhook.payment.legacy');

// User Account Portal (Cặp đôi / Khách hàng)
Route::prefix('tai-khoan')->middleware('auth')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('dashboard');
    Route::get('/thiep', [AccountController::class, 'invitations'])->name('invitations');
    Route::post('/thiep/{id}/xuat-ban', [AccountController::class, 'togglePublish'])->name('invitations.toggle_publish');
    Route::get('/don-hang', [AccountController::class, 'orders'])->name('orders');
    Route::get('/ho-so', [AccountController::class, 'profile'])->name('profile');
    Route::post('/ho-so', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::post('/doi-mat-khau', [AccountController::class, 'updatePassword'])->name('password.update');

    // Cài Đặt Chi Tiết & Nhân Bản Thiệp Cưới
    Route::get('/thiep/{id}/cai-dat', [AccountController::class, 'settings'])->name('invitations.settings');
    Route::post('/thiep/{id}/cai-dat', [AccountController::class, 'updateSettings'])->name('invitations.settings.update');
    Route::post('/thiep/{id}/nhan-ban', [AccountController::class, 'duplicate'])->name('invitations.duplicate');
    Route::post('/thiep/{id}/khoi-phuc-khan-cap', [AccountController::class, 'rollback'])->name('invitations.rollback');
    Route::delete('/thiep/{id}', [AccountController::class, 'destroy'])->name('invitations.destroy');
    Route::get('/thiep/{id}/bo-du-phong-ngay-cuoi', [AccountController::class, 'resilienceKit'])->name('invitations.resilience_kit');

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
    Route::delete('/thiep/{id}/so-luu-but/{wishId}', [WishManagementController::class, 'destroy'])->name('wishes.destroy');

    // Sổ Mừng Cưới & Quản Lý Tiền Mừng
    Route::get('/thiep/{id}/so-mung-cuoi', [GiftManagementController::class, 'index'])->name('gifts.index');
    Route::post('/thiep/{id}/so-mung-cuoi', [GiftManagementController::class, 'store'])->name('gifts.store');
    Route::put('/thiep/{id}/so-mung-cuoi/{giftId}', [GiftManagementController::class, 'update'])->name('gifts.update');
    Route::post('/thiep/{id}/so-mung-cuoi/{giftId}/cam-on', [GiftManagementController::class, 'toggleAcknowledge'])->name('gifts.toggle_ack');
    Route::delete('/thiep/{id}/so-mung-cuoi/{giftId}', [GiftManagementController::class, 'destroy'])->name('gifts.destroy');
    Route::get('/thiep/{id}/so-mung-cuoi/xuat-csv', [GiftManagementController::class, 'exportCsv'])->name('gifts.export');
    Route::get('/thiep/{id}/so-mung-cuoi/in', [GiftManagementController::class, 'print'])->name('gifts.print');
});

// Guest Management & RSVP
Route::prefix('tai-khoan')->group(function () {
    Route::get('/thiep/{id}/khach-moi', [GuestController::class, 'index'])->name('guests.index');
    Route::post('/thiep/{id}/khach-moi', [GuestController::class, 'store'])->middleware('throttle:60,1')->name('guests.store');
    Route::post('/thiep/{id}/khach-moi/nhap-nhanh', [GuestController::class, 'batchStore'])->middleware('throttle:30,1')->name('guests.batch_store');
    Route::get('/thiep/{id}/khach-moi/xuat-csv', [GuestController::class, 'exportCsv'])->name('guests.export');
    Route::get('/thiep/{id}/khach-moi/in', [GuestController::class, 'print'])->name('guests.print');
    Route::get('/thiep/{id}/khach-moi/kiem-tra-truoc-gui', [GuestController::class, 'preSendValidation'])->middleware('throttle:30,1')->name('guests.pre_send_validation');
    Route::put('/khach-moi/{id}', [GuestController::class, 'update'])->middleware('throttle:60,1')->name('guests.update');
    Route::post('/khach-moi/{id}/rsvp-thu-cong', [GuestController::class, 'manualRsvp'])->middleware('throttle:60,1')->name('guests.manual_rsvp');
    Route::delete('/khach-moi/{id}', [GuestController::class, 'destroy'])->name('guests.destroy');

    // Bảng Điều Khiển Ngày Cưới Realtime (Live Command Center - TASK-V2-02)
    Route::get('/thiep/{id}/dieu-phoi', [LiveCoordinationController::class, 'index'])->name('coordination.index');
    Route::get('/api/thiep/{id}/dieu-phoi/stats', [LiveCoordinationController::class, 'liveStats'])->middleware('throttle:120,1')->name('coordination.stats');

    // Két Lưu Trữ Kỷ Niệm Số & Sách Kỷ Yếu (Digital Memory Vault - TASK-V2-03 & TASK-V2-04)
    Route::get('/thiep/{id}/ky-niem', [MemoryVaultController::class, 'settings'])->name('memory.settings');
    Route::post('/thiep/{id}/ky-niem', [MemoryVaultController::class, 'updateSettings'])->middleware('throttle:30,1')->name('memory.updateSettings');
    Route::get('/thiep/{id}/sach-ky-yeu', [MemoryVaultController::class, 'keepsakeBook'])->name('memory.keepsake_book');

    // Quản Lý Album Ảnh Khách Chụp Ngày Cưới (Live Guest Photo Drop - TASK-V2-05)
    Route::get('/thiep/{id}/album-khach', [PhotoDropController::class, 'manage'])->name('photo_drop.manage');
    Route::post('/thiep/{id}/album-khach/{photoId}/duyet', [PhotoDropController::class, 'toggleApproval'])->name('photo_drop.toggleApproval');
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
Route::get('/diem-danh-tiec/{slug}', [CheckInController::class, 'showPortal'])->name('checkin.portal');
Route::post('/diem-danh-tiec/{slug}/xac-thuc-pin', [CheckInController::class, 'verifyPin'])->middleware('throttle:15,1')->name('checkin.verifyPin');
Route::post('/api/diem-danh-tiec/{slug}/quet', [CheckInController::class, 'processScan'])->middleware('throttle:120,1')->name('checkin.scan');
Route::get('/api/diem-danh-tiec/{slug}/tim-nhanh', [CheckInController::class, 'quickSearch'])->middleware('throttle:60,1')->name('checkin.quickSearch');
Route::post('/api/diem-danh-tiec/{slug}/huy/{guestId}', [CheckInController::class, 'undo'])->middleware('throttle:60,1')->name('checkin.undo');
Route::post('/api/diem-danh-tiec/{slug}/ban-tiec/{guestId}', [CheckInController::class, 'updateTable'])->middleware('throttle:60,1')->name('checkin.updateTable');

// Thẻ VIP & Tra Cứu Bàn Tiệc Khi Quét QR Cá Nhân
Route::get('/diem-danh/{token}', [CheckInController::class, 'directScan'])->name('checkin.direct');
Route::get('/khach-moi/{guestToken}', [CheckInController::class, 'guestConcierge'])->name('guest.concierge');
Route::get('/khach-moi/{guestToken}/lich-ics', [CheckInController::class, 'downloadIcs'])->name('guest.concierge.ics');

// Marketing Informational Pages
Route::get('/dich-vu', [PageController::class, 'services'])->name('services');
Route::get('/bang-gia', [PageController::class, 'pricing'])->name('pricing');
Route::get('/huong-dan', [PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/ve-may-hen', [PageController::class, 'about'])->name('about');
Route::get('/lien-he', [PageController::class, 'contact'])->name('contact');
Route::get('/cau-hoi-thuong-gap', [PageController::class, 'faq'])->name('faq');
Route::get('/dieu-khoan-dich-vu', [PageController::class, 'terms'])->name('terms');
Route::get('/chinh-sach-bao-mat', [PageController::class, 'privacy'])->name('privacy');

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

    // Quản lý hoa hồng & chiết khấu B2B
    Route::get('/hoa-hong', [PartnerCommissionController::class, 'index'])->name('commissions.index');

    // Cài đặt hồ sơ Studio & Co-branding
    Route::get('/ho-so', [PartnerProfileController::class, 'edit'])->name('profile');
    Route::post('/ho-so', [PartnerProfileController::class, 'update'])->middleware('throttle:30,1')->name('profile.update');
});

// Admin Management Panel
Route::prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
