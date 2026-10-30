@php
    $assetBase = url('/mau-thiep/dau-an-thoi-gian/assets');
    $isEmbed = request()->boolean('embed');
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Dấu Ấn Thời Gian';

    // 1. Phần 1: Đôi Uyên Ương (couple)
    $groomName = $wedding->groom_name ?? 'Minh Tuấn';
    $brideName = $wedding->bride_name ?? 'Thu Hà';
    $extraData = $wedding->extra_data ?? [];
    $coupleQuote = $extraData['couple_quote'] ?? 'Thời gian có thể trôi, nhưng tình yêu chúng mình trao nhau sẽ mãi là dấu ấn vĩnh cửu.';

    // 2. Phần 2: Nhà Trai (groom_family)
    $groomFather = $extraData['groom_father'] ?? ($wedding->groom_parents ?? 'Ông Nguyễn Văn Hùng');
    $groomMother = $extraData['groom_mother'] ?? 'Bà Lê Thị Thu';
    $groomPartyTime = $extraData['groom_party_time'] ?? ($extraData['ceremony_groom_time'] ?? '10:00');
    $groomPartyDate = $extraData['groom_party_date'] ?? null;
    $groomPartyLunar = $extraData['groom_party_lunar'] ?? ($extraData['wedding_lunar_date'] ?? 'Ngày 12 tháng 11 năm Bính Ngọ');
    $groomPartyAddress = $extraData['groom_party_address'] ?? ($extraData['ceremony_groom_address'] ?? ($extraData['groom_parents_address'] ?? 'Tư gia Nhà Trai - Đống Đa, Hà Nội'));

    // 3. Phần 3: Nhà Gái (bride_family)
    $brideFather = $extraData['bride_father'] ?? ($wedding->bride_parents ?? 'Ông Trần Quốc Bảo');
    $brideMother = $extraData['bride_mother'] ?? 'Bà Phạm Thị Mai';
    $bridePartyTime = $extraData['bride_party_time'] ?? ($extraData['ceremony_time'] ?? '08:00');
    $bridePartyDate = $extraData['bride_party_date'] ?? null;
    $bridePartyLunar = $extraData['bride_party_lunar'] ?? ($extraData['wedding_lunar_date'] ?? 'Ngày 12 tháng 11 năm Bính Ngọ');
    $bridePartyAddress = $extraData['bride_party_address'] ?? ($extraData['ceremony_address'] ?? ($extraData['bride_parents_address'] ?? 'Tư gia Nhà Gái - Ba Đình, Hà Nội'));

    // Ngày giờ hôn lễ chính
    $weddingDate = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2026, 12, 20, 18, 0, 0);

    $weddingDateDigits = $weddingDate->format('d . m . Y');
    $weddingDay = $weddingDate->format('d');
    $weddingMonth = $weddingDate->format('m');
    $weddingYear = $weddingDate->format('Y');
    $targetTimestamp = $weddingDate->timestamp * 1000;
    $weddingLunar = $extraData['wedding_lunar_date'] ?? 'Nhằm ngày 12 tháng 11 năm Bính Ngọ';

    // Tiệc cưới chính
    $partyTime = $extraData['party_time'] ?? '18:00';
    $partyVenue = $extraData['party_venue'] ?? 'Trung tâm Tiệc cưới Ngân Hà';
    $partyAddress = $extraData['event_party_address'] ?? ($extraData['party_address'] ?? 'Số 88 Trần Duy Hưng, Cầu Giấy, Hà Nội');
    $partyMapLink = 'https://maps.google.com/maps?q=' . urlencode($partyVenue . ', ' . $partyAddress) . '&z=16&output=embed';

    // Khách mời
    $guestName = $invitation->guest_name ?? 'Quý Khách';

    // Hàm chuẩn hóa asset ảnh
    $resolveAsset = function (?string $path, string $fallback = '') use ($assetBase): string {
        $val = trim((string) $path);
        if (str_starts_with($val, 'blob:')) {
            $val = '';
        }
        if (empty($val)) {
            $fb = trim($fallback);
            if (empty($fb)) return '';
            return (str_starts_with($fb, 'http://') || str_starts_with($fb, 'https://') || str_starts_with($fb, '/'))
                ? $fb
                : $assetBase . '/' . ltrim($fb, '/');
        }
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://') || str_starts_with($val, '/') || str_starts_with($val, 'data:')) {
            return $val;
        }
        return $assetBase . '/' . ltrim($val, '/');
    };

    // Ảnh đại diện & chân dung
    $heroPhotoUrl = $resolveAsset($wedding->cover_photo_url ?? ($extraData['hero_photo_url'] ?? null), 'sun_earth.jpg');
    $groomPhotoUrl = $resolveAsset($extraData['groom_photo_url'] ?? null, 'groom.jpg');
    $bridePhotoUrl = $resolveAsset($extraData['bride_photo_url'] ?? null, 'bride.jpg');
    $saveDatePhotoUrl = $resolveAsset($extraData['save_date_photo_url'] ?? null, 'savedate.jpg');

    // 4. Phần 4: Ảnh cưới (Album) chuẩn 10 ảnh (album)
    $defaultGallery = [
        'gallery_1.jpg',
        'savedate.jpg',
        'gallery_2.jpg',
        'gallery_3.jpg',
        'gallery_4.jpg',
        'gallery_5.jpg',
        'gallery_6.jpg',
        'hero.jpg',
        'groom.jpg',
        'bride.jpg',
    ];
    $rawGallery = $extraData['gallery'] ?? [];
    $gallery = [];
    for ($i = 0; $i < 10; $i++) {
        $gallery[] = !empty($rawGallery[$i]) ? $resolveAsset($rawGallery[$i], $defaultGallery[$i]) : $resolveAsset($defaultGallery[$i]);
    }

    // 5. Phần 5: Lịch trình (timeline)
    $timelineItems = $extraData['timeline'] ?? [
        ['time' => '08:00', 'title' => 'Lễ Vu Quy (Nhà Gái)'],
        ['time' => '10:00', 'title' => 'Lễ Thành Hôn (Nhà Trai)'],
        ['time' => '18:00', 'title' => 'Khai Tiệc Mừng Cưới'],
        ['time' => '19:30', 'title' => 'Chụp Ảnh Kỷ Niệm'],
    ];

    // 6. Phần 6: Mừng cưới (gift)
    $groomBankName = $extraData['groom_bank_name'] ?? 'Vietcombank';
    $groomBankAccount = $extraData['groom_bank_account'] ?? '0123 456 789';
    $groomBankHolder = $extraData['groom_bank_holder'] ?? ($extraData['groom_account_name'] ?? 'NGUYEN MINH TUAN');
    $cleanGroomAcc = preg_replace('/\s+/', '', $groomBankAccount);
    $qrGroomUrl = $resolveAsset(
        $extraData['qr_groom_image'] ?? null,
        'https://img.vietqr.io/image/' . strtolower($groomBankName) . '-' . $cleanGroomAcc . '-compact2.svg'
    );

    $brideBankName = $extraData['bride_bank_name'] ?? 'Techcombank';
    $brideBankAccount = $extraData['bride_bank_account'] ?? '1903 5555 8888';
    $brideBankHolder = $extraData['bride_bank_holder'] ?? ($extraData['bride_account_name'] ?? 'TRAN THU HA');
    $cleanBrideAcc = preg_replace('/\s+/', '', $brideBankAccount);
    $qrBrideUrl = $resolveAsset(
        $extraData['qr_bride_image'] ?? null,
        'https://img.vietqr.io/image/' . strtolower($brideBankName) . '-' . $cleanBrideAcc . '-compact2.svg'
    );

    // Tiêu đề & URLs
    $pageTitle = $isPreview
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name')
        : 'Thiệp cưới: ' . $groomName . ' & ' . $brideName . ' | ' . config('app.name');

    $wishSubmitUrl = $wishSubmitUrl ?? ($isPreview ? route('templates.preview.wish', 'dau-an-thoi-gian') : route('invitation.wish.store', $invitation->slug ?? ''));
    $rsvpSubmitUrl = $rsvpSubmitUrl ?? ($isPreview ? route('templates.preview.rsvp', 'dau-an-thoi-gian') : route('invitation.show', $invitation->slug ?? ''));

    $cssVersion = file_exists(resource_path('views/client/invitations/dau-an-thoi-gian/styles.css'))
        ? filemtime(resource_path('views/client/invitations/dau-an-thoi-gian/styles.css'))
        : time();
    $jsVersion = file_exists(resource_path('views/client/invitations/dau-an-thoi-gian/script.js'))
        ? filemtime(resource_path('views/client/invitations/dau-an-thoi-gian/script.js'))
        : time();
@endphp

@extends('client.layouts.invitation')

@section('title', $pageTitle)
@section('meta_description', 'Trân trọng kính mời ' . $guestName . ' tới dự lễ thành hôn của ' . $groomName . ' & ' . $brideName . '.')
@section('body_class', 'mayhen_invitation_ladoifji894yfv_body ' . ($isEmbed ? 'mayhen_invitation_ladoifji894yfv_ready' : 'mayhen_invitation_ladoifji894yfv_lock'))

@push('fonts')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Great+Vibes&display=swap" rel="stylesheet">
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ route('templates.asset', ['slug' => 'dau-an-thoi-gian', 'path' => 'styles.css']) }}?v={{ $cssVersion }}">
@endpush

@section('head_extra')
    @if ($isEmbed)
        <style>
            .mayhen_invitation_ladoifji894yfv_cover {
                display: none !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }
            .mayhen_invitation_ladoifji894yfv_page {
                opacity: 1 !important;
                transform: none !important;
            }
            html, body {
                overflow-x: hidden !important;
                overflow-y: auto !important;
                height: auto !important;
            }
        </style>
    @endif
@endsection

@section('content')
<svg width="0" height="0" style="position:absolute;visibility:hidden"><symbol id="mayhen_invitation_ladoifji894yfv_star" viewBox="0 0 20 20"><path d="M10 0l2.2 7.8L20 10l-7.8 2.2L10 20l-2.2-7.8L0 10l7.8-2.2z" fill="currentColor"/></symbol></svg>
<canvas class="mayhen_invitation_ladoifji894yfv_sky"></canvas>

{{-- MÀN MỞ THIỆP ĐIỆN ẢNH --}}
<div class="mayhen_invitation_ladoifji894yfv_cover">
 <div class="mayhen_invitation_ladoifji894yfv_cover_box">
  <p class="mayhen_invitation_ladoifji894yfv_tag mayhen_invitation_ladoifji894yfv_a" style="--d:.3s"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Thiệp Mời Cưới<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p>
  <h2 class="mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_names mayhen_invitation_ladoifji894yfv_a" style="--d:.6s" data-sync-target="groom_name">{{ $groomName }}</h2>
  <span class="mayhen_invitation_ladoifji894yfv_amp mayhen_invitation_ladoifji894yfv_a" style="--d:.85s">&amp;</span>
  <h2 class="mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_names mayhen_invitation_ladoifji894yfv_a" style="--d:1s" data-sync-target="bride_name">{{ $brideName }}</h2>
  <p class="mayhen_invitation_ladoifji894yfv_cover_date mayhen_invitation_ladoifji894yfv_a" style="--d:1.4s" data-sync-target="wedding_date">{{ $weddingDateDigits }}</p>
  <p class="mayhen_invitation_ladoifji894yfv_small mayhen_invitation_ladoifji894yfv_a" style="--d:1.6s">Kính mời</p>
  <p class="mayhen_invitation_ladoifji894yfv_guest mayhen_invitation_ladoifji894yfv_a" style="--d:1.6s">{{ $guestName }}</p>
  <button class="mayhen_invitation_ladoifji894yfv_btn mayhen_invitation_ladoifji894yfv_open mayhen_invitation_ladoifji894yfv_a" style="--d:1.8s" type="button">Mở thiệp</button>
 </div>
</div>

<main class="mayhen_invitation_ladoifji894yfv_page">

{{-- 1. HERO SECTION --}}
<section class="mayhen_invitation_ladoifji894yfv_sec mayhen_invitation_ladoifji894yfv_hero" data-mayhen-section="couple">
 <p class="mayhen_invitation_ladoifji894yfv_tag mayhen_invitation_ladoifji894yfv_rv"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Lễ Thành Hôn<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p>
 <div class="mayhen_invitation_ladoifji894yfv_orbit mayhen_invitation_ladoifji894yfv_rv">
  <span class="mayhen_invitation_ladoifji894yfv_ring"></span>
  <svg class="mayhen_invitation_ladoifji894yfv_spin" width="16" height="16" viewBox="0 0 20 20" style="position:absolute;width:16px;height:16px;top:-8px;left:50%;margin-left:-8px;z-index:3;pointer-events:none;"><use href="#mayhen_invitation_ladoifji894yfv_star"/></svg>
  <div class="mayhen_invitation_ladoifji894yfv_photo mayhen_invitation_ladoifji894yfv_round" style="background-image: url('{{ $heroPhotoUrl }}')" data-sync-target="cover_photo_url"></div>
 </div>
 <h1 class="mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_big mayhen_invitation_ladoifji894yfv_rv" data-sync-target="groom_name">{{ $groomName }}</h1>
 <span class="mayhen_invitation_ladoifji894yfv_amp mayhen_invitation_ladoifji894yfv_rv">&amp;</span>
 <h1 class="mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_big mayhen_invitation_ladoifji894yfv_rv" data-sync-target="bride_name">{{ $brideName }}</h1>
 <p class="mayhen_invitation_ladoifji894yfv_pill mayhen_invitation_ladoifji894yfv_rv" data-sync-target="wedding_date">{{ $weddingDateDigits }}</p>
 <p class="mayhen_invitation_ladoifji894yfv_note mayhen_invitation_ladoifji894yfv_rv" data-sync-target="wedding_lunar_date">{{ $weddingLunar }}</p>
 <p class="mayhen_invitation_ladoifji894yfv_quote mayhen_invitation_ladoifji894yfv_rv" data-sync-target="couple_quote">{!! nl2br(e($coupleQuote)) !!}</p>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 2. HAI BÊN GIA ĐÌNH & CÔ DÂU CHÚ RỂ --}}
<section class="mayhen_invitation_ladoifji894yfv_sec" data-mayhen-section="groom_family,bride_family">
 <header class="mayhen_invitation_ladoifji894yfv_head mayhen_invitation_ladoifji894yfv_rv"><p class="mayhen_invitation_ladoifji894yfv_tag"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Hai Họ<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p><h2 class="mayhen_invitation_ladoifji894yfv_title">Hai Bên Gia Đình</h2></header>
 <div class="mayhen_invitation_ladoifji894yfv_card mayhen_invitation_ladoifji894yfv_families mayhen_invitation_ladoifji894yfv_rv">
  <div data-mayhen-section="groom_family">
   <p class="mayhen_invitation_ladoifji894yfv_label">Nhà Trai</p>
   <p><span data-sync-target="groom_father">{{ $groomFather }}</span><br><span data-sync-target="groom_mother">{{ $groomMother }}</span></p>
   <p class="mayhen_invitation_ladoifji894yfv_note" data-sync-target="groom_party_address">{{ $groomPartyAddress }}</p>
  </div>
  <i class="mayhen_invitation_ladoifji894yfv_vline"></i>
  <div data-mayhen-section="bride_family">
   <p class="mayhen_invitation_ladoifji894yfv_label">Nhà Gái</p>
   <p><span data-sync-target="bride_father">{{ $brideFather }}</span><br><span data-sync-target="bride_mother">{{ $brideMother }}</span></p>
   <p class="mayhen_invitation_ladoifji894yfv_note" data-sync-target="bride_party_address">{{ $bridePartyAddress }}</p>
  </div>
 </div>
 <p class="mayhen_invitation_ladoifji894yfv_quote mayhen_invitation_ladoifji894yfv_rv">Trân trọng báo tin lễ thành hôn<br>của con chúng tôi</p>
 <div class="mayhen_invitation_ladoifji894yfv_couple mayhen_invitation_ladoifji894yfv_rv">
  <div>
   <div class="mayhen_invitation_ladoifji894yfv_photo mayhen_invitation_ladoifji894yfv_portrait" style="background-image: url('{{ $groomPhotoUrl }}')" data-sync-target="groom_photo_url"></div>
   <p class="mayhen_invitation_ladoifji894yfv_label">Chú Rể</p>
   <p class="mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_half" data-sync-target="groom_name">{{ $groomName }}</p>
  </div>
  <div>
   <div class="mayhen_invitation_ladoifji894yfv_photo mayhen_invitation_ladoifji894yfv_portrait" style="background-image: url('{{ $bridePhotoUrl }}')" data-sync-target="bride_photo_url"></div>
   <p class="mayhen_invitation_ladoifji894yfv_label">Cô Dâu</p>
   <p class="mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_half" data-sync-target="bride_name">{{ $brideName }}</p>
  </div>
 </div>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 3. THƯ MỜI TIỆC CƯỚI CHÍNH --}}
<section class="mayhen_invitation_ladoifji894yfv_sec">
 <header class="mayhen_invitation_ladoifji894yfv_head mayhen_invitation_ladoifji894yfv_rv"><p class="mayhen_invitation_ladoifji894yfv_tag"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Thư Mời<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p><h2 class="mayhen_invitation_ladoifji894yfv_title">Trân Trọng Kính Mời</h2></header>
 <div class="mayhen_invitation_ladoifji894yfv_card mayhen_invitation_ladoifji894yfv_ticket mayhen_invitation_ladoifji894yfv_rv">
  <p class="mayhen_invitation_ladoifji894yfv_label">Kính mời</p>
  <p class="mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_guest_big">{{ $guestName }}</p>
  <p class="mayhen_invitation_ladoifji894yfv_soft">tới dự bữa tiệc chung vui<br>cùng gia đình chúng tôi</p>
  <div class="mayhen_invitation_ladoifji894yfv_photo mayhen_invitation_ladoifji894yfv_wide" style="background-image: url('{{ $saveDatePhotoUrl }}')" data-sync-target="save_date_photo_url"></div>
  <p class="mayhen_invitation_ladoifji894yfv_note">Vào lúc</p>
  <p class="mayhen_invitation_ladoifji894yfv_time" data-sync-target="party_time">{{ $partyTime }}</p>
  <div class="mayhen_invitation_ladoifji894yfv_dateblock">
   <span data-sync-target="wedding_weekday">{{ $weddingDate->locale('vi')->isoFormat('dddd') }}</span>
   <b data-sync-target="wedding_day">{{ $weddingDay }}</b>
   <span data-sync-target="wedding_month">Tháng {{ $weddingMonth }}</span>
  </div>
  <p class="mayhen_invitation_ladoifji894yfv_label" data-sync-target="wedding_year">{{ $weddingYear }}</p>
  <p class="mayhen_invitation_ladoifji894yfv_note" data-sync-target="wedding_lunar_date">({{ $weddingLunar }})</p>
  <p class="mayhen_invitation_ladoifji894yfv_label mayhen_invitation_ladoifji894yfv_gap">Tại</p>
  <p class="mayhen_invitation_ladoifji894yfv_venue_s">Trung tâm Tiệc cưới</p>
  <p class="mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_venue" data-sync-target="party_venue">{{ $partyVenue }}</p>
  <p class="mayhen_invitation_ladoifji894yfv_soft" data-sync-target="event_party_address">{{ $partyAddress }}</p>
 </div>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 4. NGHI LỄ GIA TIÊN --}}
<section class="mayhen_invitation_ladoifji894yfv_sec">
 <header class="mayhen_invitation_ladoifji894yfv_head mayhen_invitation_ladoifji894yfv_rv"><p class="mayhen_invitation_ladoifji894yfv_tag"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Nghi Lễ<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p><h2 class="mayhen_invitation_ladoifji894yfv_title">Lễ Gia Tiên</h2></header>
 <div class="mayhen_invitation_ladoifji894yfv_rites">
  <article class="mayhen_invitation_ladoifji894yfv_card mayhen_invitation_ladoifji894yfv_arch mayhen_invitation_ladoifji894yfv_rv">
   <span class="mayhen_invitation_ladoifji894yfv_icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="14" r="5.5"/><circle cx="15" cy="14" r="5.5"/><path d="M9 4.5l1.5-2h3L15 4.5l-3 3z"/></svg></span>
   <h3 class="mayhen_invitation_ladoifji894yfv_rite">Lễ Vu Quy (Nhà Gái)</h3>
   <p class="mayhen_invitation_ladoifji894yfv_rite_time"><span data-sync-target="bride_party_time">{{ $bridePartyTime }}</span> · <span data-sync-target="wedding_date_full">{{ $weddingDate->locale('vi')->isoFormat('dddd D.M.Y') }}</span></p>
   <p class="mayhen_invitation_ladoifji894yfv_note" data-sync-target="bride_party_address">{{ $bridePartyAddress }}</p>
  </article>
  <article class="mayhen_invitation_ladoifji894yfv_card mayhen_invitation_ladoifji894yfv_arch mayhen_invitation_ladoifji894yfv_rv">
   <span class="mayhen_invitation_ladoifji894yfv_icon"><svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V10l7-6 7 6v11M9 21v-6h6v6"/></svg></span>
   <h3 class="mayhen_invitation_ladoifji894yfv_rite">Lễ Thành Hôn (Nhà Trai)</h3>
   <p class="mayhen_invitation_ladoifji894yfv_rite_time"><span data-sync-target="groom_party_time">{{ $groomPartyTime }}</span> · <span data-sync-target="wedding_date_full">{{ $weddingDate->locale('vi')->isoFormat('dddd D.M.Y') }}</span></p>
   <p class="mayhen_invitation_ladoifji894yfv_note" data-sync-target="groom_party_address">{{ $groomPartyAddress }}</p>
  </article>
 </div>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 5. LỊCH CƯỚI & GOOGLE CALENDAR --}}
<section class="mayhen_invitation_ladoifji894yfv_sec">
 <header class="mayhen_invitation_ladoifji894yfv_head mayhen_invitation_ladoifji894yfv_rv"><p class="mayhen_invitation_ladoifji894yfv_tag"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Save The Date<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p><h2 class="mayhen_invitation_ladoifji894yfv_title">Lịch Cưới</h2></header>
 <div class="mayhen_invitation_ladoifji894yfv_card mayhen_invitation_ladoifji894yfv_cal mayhen_invitation_ladoifji894yfv_rv">
  <p class="mayhen_invitation_ladoifji894yfv_cal_month">Tháng {{ $weddingMonth }} · {{ $weddingYear }}</p>
  <div class="mayhen_invitation_ladoifji894yfv_cal_grid mayhen_invitation_ladoifji894yfv_cal_week"><span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span></div>
  <div class="mayhen_invitation_ladoifji894yfv_cal_grid mayhen_invitation_ladoifji894yfv_cal_days"></div>
 </div>
 <a class="mayhen_invitation_ladoifji894yfv_btn mayhen_invitation_ladoifji894yfv_rv" target="_blank" rel="noopener" href="https://calendar.google.com/calendar/u/0/r/eventedit?text={{ urlencode('Lễ cưới ' . $groomName . ' & ' . $brideName) }}&dates={{ $weddingDate->format('Ymd\THis\Z') }}/{{ $weddingDate->copy()->addHours(4)->format('Ymd\THis\Z') }}&location={{ urlencode($partyVenue . ', ' . $partyAddress) }}">Thêm vào lịch</a>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 6. ĐỒNG HỒ ĐẾM NGƯỢC --}}
<section class="mayhen_invitation_ladoifji894yfv_sec">
 <header class="mayhen_invitation_ladoifji894yfv_head mayhen_invitation_ladoifji894yfv_rv"><p class="mayhen_invitation_ladoifji894yfv_tag"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Đếm Ngược<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p><h2 class="mayhen_invitation_ladoifji894yfv_title">Tới Ngày Chung Đôi</h2></header>
 <div class="mayhen_invitation_ladoifji894yfv_count mayhen_invitation_ladoifji894yfv_rv" data-end="{{ $weddingDate->toIso8601String() }}">
  <div><b data-u="d">0</b><span>Ngày</span></div><div><b data-u="h">0</b><span>Giờ</span></div><div><b data-u="m">0</b><span>Phút</span></div><div><b data-u="s">0</b><span>Giây</span></div>
 </div>
 <p class="mayhen_invitation_ladoifji894yfv_quote mayhen_invitation_ladoifji894yfv_rv">Mỗi ngôi sao rơi là một ngày gần nhau hơn</p>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 7. LỊCH TRÌNH BUỔI TIỆC --}}
<section class="mayhen_invitation_ladoifji894yfv_sec" data-mayhen-section="timeline">
 <header class="mayhen_invitation_ladoifji894yfv_head mayhen_invitation_ladoifji894yfv_rv"><p class="mayhen_invitation_ladoifji894yfv_tag"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Timeline<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p><h2 class="mayhen_invitation_ladoifji894yfv_title">Lịch Trình Buổi Tiệc</h2></header>
 <ol class="mayhen_invitation_ladoifji894yfv_timeline mayhen_invitation_ladoifji894yfv_card mayhen_invitation_ladoifji894yfv_rv">
  @foreach ($timelineItems as $tIdx => $tItem)
   <li>
     <time data-sync-target="timeline.{{ $tIdx }}.time">{{ $tItem['time'] ?? '' }}</time>
     <i class="mayhen_invitation_ladoifji894yfv_dot"></i>
     <span data-sync-target="timeline.{{ $tIdx }}.title">{{ $tItem['title'] ?? '' }}</span>
   </li>
  @endforeach
 </ol>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 8. BẢN ĐỒ CHỈ ĐƯỜNG --}}
<section class="mayhen_invitation_ladoifji894yfv_sec">
 <header class="mayhen_invitation_ladoifji894yfv_head mayhen_invitation_ladoifji894yfv_rv"><p class="mayhen_invitation_ladoifji894yfv_tag"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Bản Đồ<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p><h2 class="mayhen_invitation_ladoifji894yfv_title">Đường Tới Hôn Lễ</h2></header>
 <div class="mayhen_invitation_ladoifji894yfv_card mayhen_invitation_ladoifji894yfv_map mayhen_invitation_ladoifji894yfv_rv">
  <iframe class="mayhen_invitation_ladoifji894yfv_map_frame" title="Bản đồ nơi tổ chức" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="{{ $partyMapLink }}"></iframe>
  <div class="mayhen_invitation_ladoifji894yfv_door"><i class="mayhen_invitation_ladoifji894yfv_leaf mayhen_invitation_ladoifji894yfv_l"></i><i class="mayhen_invitation_ladoifji894yfv_leaf mayhen_invitation_ladoifji894yfv_r"></i><i class="mayhen_invitation_ladoifji894yfv_glow"></i><button class="mayhen_invitation_ladoifji894yfv_btn mayhen_invitation_ladoifji894yfv_door_btn" type="button">Mở bản đồ</button></div>
 </div>
 <p class="mayhen_invitation_ladoifji894yfv_soft mayhen_invitation_ladoifji894yfv_rv" data-sync-target="event_party_address">{{ $partyVenue }}<br>{{ $partyAddress }}</p>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 9. ALBUM COVERFLOW 3D KHOẢNH KHẮC --}}
<section class="mayhen_invitation_ladoifji894yfv_sec" data-mayhen-section="album">
 <header class="mayhen_invitation_ladoifji894yfv_head mayhen_invitation_ladoifji894yfv_rv"><p class="mayhen_invitation_ladoifji894yfv_tag"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Album<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p><h2 class="mayhen_invitation_ladoifji894yfv_title">Khoảnh Khắc</h2></header>
 <div class="mayhen_invitation_ladoifji894yfv_flow mayhen_invitation_ladoifji894yfv_rv">
  @foreach ($gallery as $idx => $photoItem)
   <div class="mayhen_invitation_ladoifji894yfv_flow_item mayhen_invitation_ladoifji894yfv_photo" style="background-image: url('{{ $photoItem }}')" data-full="{{ $photoItem }}" data-sync-target="gallery.{{ $idx }}"></div>
  @endforeach
  <button class="mayhen_invitation_ladoifji894yfv_flow_nav mayhen_invitation_ladoifji894yfv_prev" type="button" aria-label="Ảnh trước">‹</button>
  <button class="mayhen_invitation_ladoifji894yfv_flow_nav mayhen_invitation_ladoifji894yfv_next" type="button" aria-label="Ảnh sau">›</button>
 </div>
 <p class="mayhen_invitation_ladoifji894yfv_flow_hint mayhen_invitation_ladoifji894yfv_rv">✦ Chạm vào ảnh giữa để phóng to ✦</p>
 <p class="mayhen_invitation_ladoifji894yfv_quote mayhen_invitation_ladoifji894yfv_rv">Mỗi khoảnh khắc bên nhau đều lấp lánh như sao trời</p>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 10. XÁC NHẬN THAM DỰ (RSVP) --}}
<section class="mayhen_invitation_ladoifji894yfv_sec">
 <header class="mayhen_invitation_ladoifji894yfv_head mayhen_invitation_ladoifji894yfv_rv"><p class="mayhen_invitation_ladoifji894yfv_tag"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>RSVP<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p><h2 class="mayhen_invitation_ladoifji894yfv_title">Xác Nhận Tham Dự</h2></header>
 <p class="mayhen_invitation_ladoifji894yfv_quote mayhen_invitation_ladoifji894yfv_rv">Hãy cho chúng mình biết bạn có thể tới chung vui<br>để gia đình chuẩn bị chu đáo hơn nhé!</p>
 <form class="mayhen_invitation_ladoifji894yfv_card mayhen_invitation_ladoifji894yfv_form mayhen_invitation_ladoifji894yfv_rv" data-msg="Cảm ơn Quý khách đã xác nhận. Hẹn gặp nhau dưới màn sao ngày vui!">
  <label class="mayhen_invitation_ladoifji894yfv_field"><span>Tên khách mời</span><input class="mayhen_invitation_ladoifji894yfv_input" required placeholder="Nhập tên của bạn" value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}"></label>
  <div class="mayhen_invitation_ladoifji894yfv_field"><span>Bạn là khách của ai?</span><div class="mayhen_invitation_ladoifji894yfv_opts"><button type="button" class="mayhen_invitation_ladoifji894yfv_opt">Cô Dâu</button><button type="button" class="mayhen_invitation_ladoifji894yfv_opt mayhen_invitation_ladoifji894yfv_on">Chú Rể</button></div></div>
  <div class="mayhen_invitation_ladoifji894yfv_field"><span>Xác nhận tham dự</span><div class="mayhen_invitation_ladoifji894yfv_opts mayhen_invitation_ladoifji894yfv_attend"><button type="button" class="mayhen_invitation_ladoifji894yfv_opt mayhen_invitation_ladoifji894yfv_on" data-yes>Sẽ tham dự</button><button type="button" class="mayhen_invitation_ladoifji894yfv_opt">Tiếc quá</button></div></div>
  <div class="mayhen_invitation_ladoifji894yfv_field mayhen_invitation_ladoifji894yfv_guests"><span>Số lượng khách tham dự</span><div class="mayhen_invitation_ladoifji894yfv_counter"><button type="button" data-n="-1">−</button><output>1</output><button type="button" data-n="1">+</button></div></div>
  <button class="mayhen_invitation_ladoifji894yfv_btn mayhen_invitation_ladoifji894yfv_submit" type="submit">Xác Nhận</button>
 </form>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 11. SỔ LƯU BÚT LỜI CHÚC (WISHES) --}}
<section class="mayhen_invitation_ladoifji894yfv_sec">
 <header class="mayhen_invitation_ladoifji894yfv_head mayhen_invitation_ladoifji894yfv_rv"><p class="mayhen_invitation_ladoifji894yfv_tag"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Guest Book<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p><h2 class="mayhen_invitation_ladoifji894yfv_title">Gửi Lời Chúc</h2></header>
 <form class="mayhen_invitation_ladoifji894yfv_card mayhen_invitation_ladoifji894yfv_form mayhen_invitation_ladoifji894yfv_wish mayhen_invitation_ladoifji894yfv_rv" data-msg="Như một vì sao băng, lời chúc của bạn đã bay tới cô dâu, chú rể.">
  <label class="mayhen_invitation_ladoifji894yfv_field"><span>Tên của bạn</span><input class="mayhen_invitation_ladoifji894yfv_input mayhen_invitation_ladoifji894yfv_wish_name" required placeholder="Nhập tên của bạn" value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}"></label>
  <div class="mayhen_invitation_ladoifji894yfv_field"><span>Ai sẽ thấy lời nhắn này?</span><div class="mayhen_invitation_ladoifji894yfv_opts"><button type="button" class="mayhen_invitation_ladoifji894yfv_opt mayhen_invitation_ladoifji894yfv_on">Tất cả mọi người</button><button type="button" class="mayhen_invitation_ladoifji894yfv_opt">Chỉ cô dâu, chú rể</button></div></div>
  <label class="mayhen_invitation_ladoifji894yfv_field"><span>Lời nhắn của bạn</span><textarea class="mayhen_invitation_ladoifji894yfv_input mayhen_invitation_ladoifji894yfv_wish_text" required maxlength="1500" placeholder="Viết lời chúc của bạn..."></textarea></label>
  <button class="mayhen_invitation_ladoifji894yfv_btn mayhen_invitation_ladoifji894yfv_submit" type="submit">Gửi lời chúc</button>
  <p class="mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_list_title">Lời chúc từ mọi người</p>
  <div class="mayhen_invitation_ladoifji894yfv_list">
   @if (isset($wishes) && $wishes->count() > 0)
    @foreach ($wishes as $wish)
     <div class="mayhen_invitation_ladoifji894yfv_item">
      <b>{{ $wish->guest_name }}</b>
      <p>{{ $wish->message }}</p>
     </div>
    @endforeach
   @else
    <p class="mayhen_invitation_ladoifji894yfv_empty">Hãy là người đầu tiên gửi lời chúc<br>tới cô dâu, chú rể</p>
   @endif
  </div>
 </form>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 12. HỘP MỪNG CƯỚI & VIETQR --}}
<section class="mayhen_invitation_ladoifji894yfv_sec" data-mayhen-section="gift">
 <header class="mayhen_invitation_ladoifji894yfv_head mayhen_invitation_ladoifji894yfv_rv"><p class="mayhen_invitation_ladoifji894yfv_tag"><i class="mayhen_invitation_ladoifji894yfv_orn"></i>Mừng Cưới<i class="mayhen_invitation_ladoifji894yfv_orn"></i></p><h2 class="mayhen_invitation_ladoifji894yfv_title">Hộp Mừng Cưới</h2></header>
 <p class="mayhen_invitation_ladoifji894yfv_quote mayhen_invitation_ladoifji894yfv_rv">Cảm ơn tấm lòng của bạn dành cho chúng mình</p>
 <div class="mayhen_invitation_ladoifji894yfv_gift mayhen_invitation_ladoifji894yfv_rv">
  <button class="mayhen_invitation_ladoifji894yfv_box" type="button" aria-label="Mở hộp mừng cưới"><i class="mayhen_invitation_ladoifji894yfv_box_lid"></i><i class="mayhen_invitation_ladoifji894yfv_box_body"></i></button>
  <p class="mayhen_invitation_ladoifji894yfv_label mayhen_invitation_ladoifji894yfv_hint">Chạm để mở hộp mừng cưới</p>
  <div class="mayhen_invitation_ladoifji894yfv_qrs">
   <div class="mayhen_invitation_ladoifji894yfv_card mayhen_invitation_ladoifji894yfv_qr">
    <p class="mayhen_invitation_ladoifji894yfv_label">Chú Rể</p>
    <div class="mayhen_invitation_ladoifji894yfv_qr_img">
      <img src="{{ $qrGroomUrl }}" alt="Mã QR Chú Rể {{ $groomBankName }}" loading="lazy" data-sync-target="qr_groom_image">
    </div>
    <p class="mayhen_invitation_ladoifji894yfv_soft"><span data-sync-target="groom_bank_name">{{ $groomBankName }}</span><br><b data-sync-target="groom_bank_account">{{ $groomBankAccount }}</b><br><span data-sync-target="groom_bank_holder">{{ $groomBankHolder }}</span></p>
   </div>
   <div class="mayhen_invitation_ladoifji894yfv_card mayhen_invitation_ladoifji894yfv_qr">
    <p class="mayhen_invitation_ladoifji894yfv_label">Cô Dâu</p>
    <div class="mayhen_invitation_ladoifji894yfv_qr_img">
      <img src="{{ $qrBrideUrl }}" alt="Mã QR Cô Dâu {{ $brideBankName }}" loading="lazy" data-sync-target="qr_bride_image">
    </div>
    <p class="mayhen_invitation_ladoifji894yfv_soft"><span data-sync-target="bride_bank_name">{{ $brideBankName }}</span><br><b data-sync-target="bride_bank_account">{{ $brideBankAccount }}</b><br><span data-sync-target="bride_bank_holder">{{ $brideBankHolder }}</span></p>
   </div>
  </div>
 </div>
</section>

<div class="mayhen_invitation_ladoifji894yfv_divider"></div>

{{-- 13. LỜI CẢM ƠN (THANK YOU) --}}
<section class="mayhen_invitation_ladoifji894yfv_sec mayhen_invitation_ladoifji894yfv_end">
 <div class="mayhen_invitation_ladoifji894yfv_orbit mayhen_invitation_ladoifji894yfv_tall mayhen_invitation_ladoifji894yfv_rv">
  <span class="mayhen_invitation_ladoifji894yfv_ring"></span>
  <div class="mayhen_invitation_ladoifji894yfv_photo mayhen_invitation_ladoifji894yfv_round" style="background-image: url('{{ $heroPhotoUrl }}')" data-sync-target="hero_photo_url"></div>
 </div>
 <h2 class="mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_thanks mayhen_invitation_ladoifji894yfv_rv">Thank You</h2>
 <p class="mayhen_invitation_ladoifji894yfv_quote mayhen_invitation_ladoifji894yfv_rv">Sự hiện diện của Quý khách là niềm vinh hạnh cho gia đình chúng tôi.</p>
 <p class="mayhen_invitation_ladoifji894yfv_script mayhen_invitation_ladoifji894yfv_sign mayhen_invitation_ladoifji894yfv_rv"><span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span></p>
</section>
</main>
@endsection

@section('modals')
{{-- LIGHTBOX MODAL RETINA --}}
<div class="mayhen_invitation_ladoifji894yfv_lightbox" hidden role="dialog" aria-modal="true" aria-label="Xem ảnh cưới phóng to">
 <div class="mayhen_invitation_ladoifji894yfv_lightbox_backdrop"></div>
 <div class="mayhen_invitation_ladoifji894yfv_lightbox_dialog">
  <button class="mayhen_invitation_ladoifji894yfv_lightbox_close" type="button" aria-label="Đóng xem ảnh">✕</button>
  <button class="mayhen_invitation_ladoifji894yfv_lightbox_arrow mayhen_invitation_ladoifji894yfv_lightbox_prev" type="button" aria-label="Ảnh trước">‹</button>
  <button class="mayhen_invitation_ladoifji894yfv_lightbox_arrow mayhen_invitation_ladoifji894yfv_lightbox_next" type="button" aria-label="Ảnh sau">›</button>
  <div class="mayhen_invitation_ladoifji894yfv_lightbox_wrap">
   <img class="mayhen_invitation_ladoifji894yfv_lightbox_img" src="" alt="Ảnh cưới phóng to">
  </div>
  <p class="mayhen_invitation_ladoifji894yfv_lightbox_counter">1 / {{ count($gallery) }}</p>
 </div>
</div>

<div class="mayhen_invitation_ladoifji894yfv_toast" role="status"></div>
@endsection

@push('scripts')
<script>
    window.__MAYHEN_IS_EMBED = {{ $isEmbed ? 'true' : 'false' }};
    window.__MAYHEN_WISH_SUBMIT_URL = "{{ $wishSubmitUrl }}";
    window.__MAYHEN_RSVP_SUBMIT_URL = "{{ $rsvpSubmitUrl }}";
</script>
<script src="{{ route('templates.asset', ['slug' => 'dau-an-thoi-gian', 'path' => 'three.min.js']) }}"></script>
<script src="{{ route('templates.asset', ['slug' => 'dau-an-thoi-gian', 'path' => 'textures.js']) }}"></script>
<script src="{{ route('templates.asset', ['slug' => 'dau-an-thoi-gian', 'path' => 'script.js']) }}?v={{ $jsVersion }}"></script>
@endpush

