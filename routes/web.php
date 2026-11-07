<?php

use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\GuestController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\InvitationEditorController;
use App\Http\Controllers\Client\PageController;
use App\Http\Controllers\Client\SitemapController;
use App\Http\Controllers\Client\TemplateController;
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
Route::post('/tao-thiep/upload-asset', [InvitationEditorController::class, 'uploadAsset'])->middleware('throttle:editor_actions')->name('invitation.uploadAsset');
Route::post('/api/editor/autosave', [InvitationEditorController::class, 'autosave'])->middleware('throttle:editor_actions')->name('invitation.autosave');
Route::get('/api/wedding-musics', [InvitationEditorController::class, 'musics'])->name('invitation.musics');
Route::get('/thiep/{slug}', [InvitationEditorController::class, 'show'])->name('invitation.show');
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
});

// Guest Management & RSVP
Route::prefix('tai-khoan')->group(function () {
    Route::get('/thiep/{id}/khach-moi', [GuestController::class, 'index'])->name('guests.index');
    Route::post('/thiep/{id}/khach-moi', [GuestController::class, 'store'])->middleware('throttle:60,1')->name('guests.store');
    Route::delete('/khach-moi/{id}', [GuestController::class, 'destroy'])->name('guests.destroy');
});

// Marketing Informational Pages
Route::get('/dich-vu', [PageController::class, 'services'])->name('services');
Route::get('/bang-gia', [PageController::class, 'pricing'])->name('pricing');
Route::get('/huong-dan', [PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/ve-may-hen', [PageController::class, 'about'])->name('about');
Route::get('/lien-he', [PageController::class, 'contact'])->name('contact');
Route::get('/cau-hoi-thuong-gap', [PageController::class, 'faq'])->name('faq');
Route::get('/dieu-khoan-dich-vu', [PageController::class, 'terms'])->name('terms');
Route::get('/chinh-sach-bao-mat', [PageController::class, 'privacy'])->name('privacy');

// Admin Management Panel
Route::prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
