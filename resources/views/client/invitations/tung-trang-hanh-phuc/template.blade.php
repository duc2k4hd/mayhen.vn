@php
    $assetBase = url('/mau-thiep/tung-trang-hanh-phuc/assets');
    $isEmbed = request()->boolean('embed');

    // 1. Tên cô dâu chú rể & Gia đình
    $groomName = $wedding->groom_name ?? 'Hoàng Nam';
    $brideName = $wedding->bride_name ?? 'Minh Anh';
    $extraData = $wedding->extra_data ?? [];

    $groomFather = $extraData['groom_father'] ?? ($wedding->groom_parents ?? 'Nguyễn Hoàng Hải');
    $groomMother = $extraData['groom_mother'] ?? 'Trần Thị Mai';
    $brideFather = $extraData['bride_father'] ?? ($wedding->bride_parents ?? 'Phạm Văn Minh');
    $brideMother = $extraData['bride_mother'] ?? 'Lê Thị Thu';

    // 2. Ngày giờ và sự kiện
    $weddingDate = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2027, 1, 16, 10, 0, 0);

    $weddingDateLong = $weddingDate->format('d · m · Y');
    $weddingDateDigits = $weddingDate->format('d . m . Y');
    $weddingFullDate = 'Thứ Bảy, ngày ' . $weddingDate->format('d') . ' tháng ' . $weddingDate->format('m') . ' năm ' . $weddingDate->format('Y');
    $weddingYear = $weddingDate->format('Y');
    $targetTimestamp = $weddingDate->timestamp * 1000;

    // Sự kiện Hôn Lễ & Tiệc Cưới
    $ceremonyTime = $extraData['ceremony_time'] ?? '10:00';
    $ceremonyVenue = $extraData['ceremony_venue'] ?? 'Tư gia Nhà Trai';
    $ceremonyAddress = $extraData['ceremony_address'] ?? 'Số 168 Hoàng Hoa Thám, Ba Đình, Hà Nội';
    $ceremonyMapLink = $extraData['ceremony_map_link'] ?? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($ceremonyAddress);

    $partyTime = $extraData['party_time'] ?? '11:30';
    $partyVenue = $extraData['party_venue'] ?? 'Trung tâm Hội nghị Trống Đồng Palace';
    $partyAddress = $extraData['party_address'] ?? 'Số 72 Trần Đăng Ninh, Cầu Giấy, Hà Nội';
    $partyMapLink = $extraData['party_map_link'] ?? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($partyAddress);

    // Hàm resolve asset
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
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://') || str_starts_with($val, '/')) {
            return $val;
        }
        return $assetBase . '/' . ltrim($val, '/');
    };

    // 3. Ảnh cưới chất lượng cao Quiet Luxury
    $heroPhotoUrl = $resolveAsset($extraData['hero_photo_url'] ?? null, 'images/hero-wedding.jpg');
    $groomPhotoUrl = $resolveAsset($extraData['groom_photo_url'] ?? null, 'images/groom.jpg');
    $bridePhotoUrl = $resolveAsset($extraData['bride_photo_url'] ?? null, 'images/bride.jpg');
    $saveDatePhotoUrl = $resolveAsset($extraData['save_date_photo_url'] ?? null, 'images/scene.jpg');

    $defaultGallery = [
        'images/gallery-01.jpg',
        'images/gallery-02.jpg',
        'images/gallery-03.jpg',
        'images/gallery-04.jpg',
        'images/gallery-05.jpg',
        'images/gallery-06.jpg',
    ];
    $rawGallery = $extraData['gallery'] ?? [];
    $validGallery = is_array($rawGallery) ? array_filter($rawGallery, fn($item) => !empty(trim((string)$item)) && !str_starts_with((string)$item, 'blob:')) : [];
    $galleryList = !empty($validGallery) ? array_values($validGallery) : $defaultGallery;
    $galleryPhotos = array_map(fn($p) => $resolveAsset($p), $galleryList);

    // 4. Lời ngỏ
    $storyText = $wedding->story ?? 'Tình yêu không chỉ là nhìn về nhau, mà là cùng nhau nhìn về một hướng. Sau những năm tháng cùng sẻ chia, chúng mình hạnh phúc thông báo ngày chung đôi và trân trọng kính mời bạn đến chung vui trong khoảnh khắc thiêng liêng này.';

    // 5. Mừng cưới QR & Ngân hàng
    $qrGroom = $resolveAsset($extraData['qr_groom_image'] ?? null, 'images/qr-groom.jpg');
    $qrBride = $resolveAsset($extraData['qr_bride_image'] ?? null, 'images/qr-bride.jpg');
    $groomBankInfo = [
        'bank' => $extraData['groom_bank_name'] ?? 'Vietcombank',
        'name' => $extraData['groom_account_name'] ?? 'HOANG NAM',
        'number' => $extraData['groom_account_number'] ?? '1012345678',
        'qr' => $qrGroom,
    ];
    $brideBankInfo = [
        'bank' => $extraData['bride_bank_name'] ?? 'Techcombank',
        'name' => $extraData['bride_account_name'] ?? 'MINH ANH',
        'number' => $extraData['bride_account_number'] ?? '190123456789',
        'qr' => $qrBride,
    ];

    // 6. Nhạc nền
    $musicUrl = $resolveAsset($wedding->music_url ?? null, 'wedding.mp3');

    // 7. Route RSVP & Wishes
    $wishes = $wishes ?? collect([]);
    $wishSubmitUrl = $wishSubmitUrl ?? (isset($invitation) ? route('invitation.wish.store', $invitation->slug) : route('templates.preview.wish', 'tung-trang-hanh-phuc'));
    $rsvpSubmitUrl = $rsvpSubmitUrl ?? (isset($invitation) ? route('rsvp.submit', $invitation->slug) : route('templates.preview.rsvp', 'tung-trang-hanh-phuc'));
    $currentRsvp = $currentRsvp ?? null;
    $myWish = $myWish ?? null;
    $isOwner = $isOwner ?? false;
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Từng Trang Hạnh Phúc';
    $pageTitle = $isPreview 
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name') 
        : 'Thiệp mời cưới: "' . $groomName . ' & ' . $brideName . '" | ' . config('app.name');

    $guestName = request()->query('to') ?: (request()->query('guest') ?: 'Quý Khách');

    $cssPath = resource_path('views/client/invitations/tung-trang-hanh-phuc/styles.css');
    $jsPath = resource_path('views/client/invitations/tung-trang-hanh-phuc/script.js');
    $cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
    $jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();
@endphp
<!doctype html>
<html lang="vi" class="mayhen_invitation_ksnvo949dudf_scroll-lock">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FAF7F2">
    <meta name="description"
        content="{{ $isPreview ? 'Trải nghiệm xem thử mẫu thiệp cưới ' . $templateName . ' trên ' . config('app.name') : 'Trân trọng kính mời ' . $guestName . ' chung vui trong lễ cưới của ' . $groomName . ' & ' . $brideName . '.' }}">
    <title>{{ $pageTitle }}</title>
    @include('client.components.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ $assetBase }}/styles.css?v={{ $cssVersion }}">
    @if (($isPreview ?? false) && !($isEmbed ?? false))
        <link rel="stylesheet" href="{{ asset('assets/client/css/components/bottom-nav.css') }}">
    @endif

    @if ($isEmbed)
        <style>
            body {
                background: transparent !important;
            }

            html,
            body {
                overflow-x: hidden !important;
                overflow-y: auto !important;
                height: 100% !important;
                -webkit-overflow-scrolling: touch !important;
                scroll-behavior: smooth !important;
            }

            html.mayhen_invitation_ksnvo949dudf_scroll-lock,
            body.mayhen_invitation_ksnvo949dudf_scroll-lock {
                overflow-x: hidden !important;
                overflow-y: auto !important;
                height: 100% !important;
                overscroll-behavior: auto !important;
            }

            .mayhen_invitation_ksnvo949dudf_opening {
                display: none !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }

            .mayhen_invitation_ksnvo949dudf_site {
                display: block !important;
            }
        </style>
    @endif
</head>

<body class="mayhen_invitation_ksnvo949dudf_body mayhen_invitation_ksnvo949dudf_scroll-lock">
    <a class="mayhen_invitation_ksnvo949dudf_skip-link" href="#mayhen_invitation_ksnvo949dudf_main">Bỏ qua mở phong bì</a>

    {{-- NÚT ĐIỀU KHIỂN NHẠC ĐĨA THAN HOÀNG GIA (VINTAGE GOLD VINYL RECORD) --}}
    <div class="mayhen_invitation_ksnvo949dudf_music-dock">
        <button class="mayhen_invitation_ksnvo949dudf_music-btn" id="mayhen_invitation_ksnvo949dudf_music" type="button" aria-label="Bật/Tắt nhạc nền" title="Bật/Tắt nhạc cưới">
            <span class="mayhen_invitation_ksnvo949dudf_vinyl-record">
                <span class="mayhen_invitation_ksnvo949dudf_vinyl-grooves"></span>
                <span class="mayhen_invitation_ksnvo949dudf_vinyl-label">
                    <svg class="mayhen_invitation_ksnvo949dudf_note-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                    </svg>
                </span>
            </span>
            <span class="mayhen_invitation_ksnvo949dudf_music-bars" aria-hidden="true">
                <i></i><i></i><i></i><i></i>
            </span>
            <span class="mayhen_invitation_ksnvo949dudf_floating-notes" aria-hidden="true">
                <span class="fn-1">♪</span>
                <span class="fn-2">♫</span>
            </span>
        </button>
    </div>

    {{-- MÀN HÌNH MỞ ĐẦU — PHONG BÌ HOÀNG GIA RỰC RỠ & QUÝ PHÁI (GRAND ROYAL ENVELOPE) --}}
    <div class="mayhen_invitation_ksnvo949dudf_opening" id="mayhen_invitation_ksnvo949dudf_opening" role="dialog" aria-label="Mở thiệp cưới hoàng gia" aria-modal="true">
        {{-- Vầng sáng hào quang ấm áp & Họa tiết góc hoàng gia --}}
        <div class="mayhen_invitation_ksnvo949dudf_opening-ambient" aria-hidden="true"></div>
        <div class="mayhen_invitation_ksnvo949dudf_corner-ornament top-left" aria-hidden="true">
            <svg viewBox="0 0 100 100" fill="none"><path d="M0 0 L100 0 C60 0 0 60 0 100 Z" fill="rgba(197, 168, 128, 0.08)"/><path d="M4 4 L80 4 C45 4 4 45 4 80 Z" stroke="rgba(197, 168, 128, 0.45)" stroke-width="1.5" fill="none"/><circle cx="24" cy="24" r="3" fill="#C5A880"/></svg>
        </div>
        <div class="mayhen_invitation_ksnvo949dudf_corner-ornament top-right" aria-hidden="true">
            <svg viewBox="0 0 100 100" fill="none"><path d="M100 0 L0 0 C40 0 100 60 100 100 Z" fill="rgba(197, 168, 128, 0.08)"/><path d="M96 4 L20 4 C55 4 96 45 96 80 Z" stroke="rgba(197, 168, 128, 0.45)" stroke-width="1.5" fill="none"/><circle cx="76" cy="24" r="3" fill="#C5A880"/></svg>
        </div>
        <div class="mayhen_invitation_ksnvo949dudf_corner-ornament bottom-left" aria-hidden="true">
            <svg viewBox="0 0 100 100" fill="none"><path d="M0 100 L100 100 C60 100 0 40 0 0 Z" fill="rgba(197, 168, 128, 0.08)"/><path d="M4 96 L80 96 C45 96 4 55 4 20 Z" stroke="rgba(197, 168, 128, 0.45)" stroke-width="1.5" fill="none"/><circle cx="24" cy="76" r="3" fill="#C5A880"/></svg>
        </div>
        <div class="mayhen_invitation_ksnvo949dudf_corner-ornament bottom-right" aria-hidden="true">
            <svg viewBox="0 0 100 100" fill="none"><path d="M100 100 L0 100 C40 100 100 40 100 0 Z" fill="rgba(197, 168, 128, 0.08)"/><path d="M96 96 L20 96 C55 96 96 55 96 20 Z" stroke="rgba(197, 168, 128, 0.45)" stroke-width="1.5" fill="none"/><circle cx="76" cy="76" r="3" fill="#C5A880"/></svg>
        </div>

        <div class="mayhen_invitation_ksnvo949dudf_opening-container">
            {{-- Huy hiệu hoàng gia & Tên đôi uyên ương to rõ --}}
            <div class="mayhen_invitation_ksnvo949dudf_royal-crest">
                <div class="mayhen_invitation_ksnvo949dudf_crest-emblem">
                    <span class="mayhen_invitation_ksnvo949dudf_crest-monogram">{{ mb_substr($groomName, 0, 1) }} & {{ mb_substr($brideName, 0, 1) }}</span>
                </div>
                <span class="mayhen_invitation_ksnvo949dudf_crest-tag">✦ SAVE OUR DATE · THIỆP CƯỚI BÁO HỶ ✦</span>
                <h1 class="mayhen_invitation_ksnvo949dudf_opening-couple-names" data-sync-target="couple_names">
                    <span>{{ $groomName }}</span>
                    <em class="amp">&</em>
                    <span>{{ $brideName }}</span>
                </h1>
                <p class="mayhen_invitation_ksnvo949dudf_opening-date-badge">{{ $weddingFullDate }}</p>
            </div>

            {{-- PHONG BÌ HOÀNG GIA 3D KÍCH THƯỚC LỚN --}}
            <div class="mayhen_invitation_ksnvo949dudf_envelope" id="mayhen_invitation_ksnvo949dudf_envelope_el">
                <div class="mayhen_invitation_ksnvo949dudf_envelope-back"></div>
                
                {{-- Lá thư mời hé lộ ảnh cưới và chi tiết bên trong --}}
                <div class="mayhen_invitation_ksnvo949dudf_envelope-letter">
                    <div class="mayhen_invitation_ksnvo949dudf_letter-card">
                        <div class="mayhen_invitation_ksnvo949dudf_letter-photo">
                            <img src="{{ $heroPhotoUrl }}" alt="Ảnh cưới {{ $groomName }} & {{ $brideName }}">
                        </div>
                        <div class="mayhen_invitation_ksnvo949dudf_letter-details">
                            <span class="mayhen_invitation_ksnvo949dudf_letter-badge">TRÂN TRỌNG BÁO HỶ</span>
                            <h3 class="mayhen_invitation_ksnvo949dudf_letter-names">{{ $groomName }} & {{ $brideName }}</h3>
                            <p class="mayhen_invitation_ksnvo949dudf_letter-day">{{ $weddingDateDigits }}</p>
                        </div>
                    </div>
                </div>

                {{-- Nắp gập phong bì 3D cắt vát hoàng gia viền chỉ vàng --}}
                <div class="mayhen_invitation_ksnvo949dudf_envelope-flap">
                    <div class="mayhen_invitation_ksnvo949dudf_flap-gold-edge"></div>
                </div>

                {{-- Thân trước phong bì --}}
                <div class="mayhen_invitation_ksnvo949dudf_envelope-front">
                    <div class="mayhen_invitation_ksnvo949dudf_front-liner"></div>
                </div>

                {{-- Tem sáp đỏ Ruby hoàng gia dập nổi Monogram & nhịp đập hào quang --}}
                <div class="mayhen_invitation_ksnvo949dudf_envelope-seal" id="mayhen_invitation_ksnvo949dudf_seal_btn" role="button" tabindex="0" title="Chạm mở thiệp mời">
                    <div class="mayhen_invitation_ksnvo949dudf_seal-glow"></div>
                    <div class="mayhen_invitation_ksnvo949dudf_seal-body">
                        <span class="mayhen_invitation_ksnvo949dudf_seal-monogram">{{ mb_substr($groomName, 0, 1) }} & {{ mb_substr($brideName, 0, 1) }}</span>
                    </div>
                </div>
            </div>

            {{-- Thông tin khách quý & Nút Mở thiệp vàng hoàng gia --}}
            <div class="mayhen_invitation_ksnvo949dudf_opening-action-zone">
                <div class="mayhen_invitation_ksnvo949dudf_guest-vip-pill">
                    <span class="kicker">KÍNH MỜI QUÝ KHÁCH</span>
                    <strong class="name">{{ $guestName }}</strong>
                </div>

                <button class="mayhen_invitation_ksnvo949dudf_open-btn" id="mayhen_invitation_ksnvo949dudf_open-invitation" type="button">
                    <span class="mayhen_invitation_ksnvo949dudf_btn-shimmer"></span>
                    <svg class="mayhen_invitation_ksnvo949dudf_btn-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span class="mayhen_invitation_ksnvo949dudf_btn-text">CHẠM ĐỂ MỞ THIỆP</span>
                    <svg class="mayhen_invitation_ksnvo949dudf_btn-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- NỘI DUNG CHÍNH CỦA THIỆP CƯỚI (KHÔNG CÓ HEADER/FOOTER WEBSITE) --}}
    <main class="mayhen_invitation_ksnvo949dudf_site" id="mayhen_invitation_ksnvo949dudf_site">
        
        {{-- SECTION 1: HERO KHỞI ĐẦU --}}
        <section class="mayhen_invitation_ksnvo949dudf_hero-section" id="mayhen_invitation_ksnvo949dudf_main">
            <div class="mayhen_invitation_ksnvo949dudf_hero-card mayhen_invitation_ksnvo949dudf_box-anim">
                <div class="mayhen_invitation_ksnvo949dudf_hero-header">
                    <span class="mayhen_invitation_ksnvo949dudf_kicker">WEDDING INVITATION</span>
                    <h1 class="mayhen_invitation_ksnvo949dudf_hero-title">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <em class="mayhen_invitation_ksnvo949dudf_hero-amp">&</em>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </h1>
                    <div class="mayhen_invitation_ksnvo949dudf_hero-line">
                        <span></span>
                        <small>✦</small>
                        <span></span>
                    </div>
                    <p class="mayhen_invitation_ksnvo949dudf_hero-date" data-sync-target="wedding_date">{{ $weddingFullDate }}</p>
                </div>

                <div class="mayhen_invitation_ksnvo949dudf_hero-media">
                    <div class="mayhen_invitation_ksnvo949dudf_hero-frame">
                        <img id="sync_hero_photo" src="{{ $heroPhotoUrl }}" alt="Ảnh cưới {{ $groomName }} & {{ $brideName }}" loading="eager">
                    </div>
                </div>

                <div class="mayhen_invitation_ksnvo949dudf_hero-footer">
                    <p class="mayhen_invitation_ksnvo949dudf_hero-quote">{{ $storyText }}</p>
                </div>
            </div>
        </section>

        {{-- SECTION 2: LỜI MỜI & SONG THÂN HAI HỌ --}}
        <section class="mayhen_invitation_ksnvo949dudf_section mayhen_invitation_ksnvo949dudf_parents-section">
            <div class="mayhen_invitation_ksnvo949dudf_container">
                <div class="mayhen_invitation_ksnvo949dudf_formal-invitation mayhen_invitation_ksnvo949dudf_box-anim">
                    <div class="mayhen_invitation_ksnvo949dudf_monogram-mark">
                        <span>{{ mb_substr($groomName, 0, 1) }}</span>
                        <small>&</small>
                        <span>{{ mb_substr($brideName, 0, 1) }}</span>
                    </div>
                    <span class="mayhen_invitation_ksnvo949dudf_sub-kicker">TRÂN TRỌNG KÍNH BÁO</span>
                    <h2 class="mayhen_invitation_ksnvo949dudf_invitation-heading">Lễ Thành Hôn & Vu Quy</h2>
                    <p class="mayhen_invitation_ksnvo949dudf_invitation-sub">
                        Cùng gia đình hai bên trân trọng kính mời Quý khách tới dự bữa cơm thân mật, chung vui và chúc phúc cho hai con chúng tôi:
                    </p>

                    <div class="mayhen_invitation_ksnvo949dudf_parents-grid">
                        <div class="mayhen_invitation_ksnvo949dudf_family-side mayhen_invitation_ksnvo949dudf_box-anim">
                            <span class="mayhen_invitation_ksnvo949dudf_family-badge">NHÀ TRAI</span>
                            <p class="mayhen_invitation_ksnvo949dudf_parent-names">
                                <strong>{{ $groomFather }}</strong>
                                <strong>{{ $groomMother }}</strong>
                            </p>
                            <span class="mayhen_invitation_ksnvo949dudf_child-role">Trưởng Nam</span>
                            <h3 class="mayhen_invitation_ksnvo949dudf_couple-fullname" data-sync-target="groom_name">{{ $groomName }}</h3>
                        </div>

                        <div class="mayhen_invitation_ksnvo949dudf_parents-divider">
                            <span>♥</span>
                        </div>

                        <div class="mayhen_invitation_ksnvo949dudf_family-side mayhen_invitation_ksnvo949dudf_box-anim">
                            <span class="mayhen_invitation_ksnvo949dudf_family-badge">NHÀ GÁI</span>
                            <p class="mayhen_invitation_ksnvo949dudf_parent-names">
                                <strong>{{ $brideFather }}</strong>
                                <strong>{{ $brideMother }}</strong>
                            </p>
                            <span class="mayhen_invitation_ksnvo949dudf_child-role">Ái Nữ</span>
                            <h3 class="mayhen_invitation_ksnvo949dudf_couple-fullname" data-sync-target="bride_name">{{ $brideName }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 3: ĐÔI UYÊN ƯƠNG --}}
        <section class="mayhen_invitation_ksnvo949dudf_section mayhen_invitation_ksnvo949dudf_couple-section">
            <div class="mayhen_invitation_ksnvo949dudf_container">
                <div class="mayhen_invitation_ksnvo949dudf_section-title-wrap">
                    <span class="mayhen_invitation_ksnvo949dudf_kicker">THE HAPPY COUPLE</span>
                    <h2 class="mayhen_invitation_ksnvo949dudf_sec-title">Đôi Uyên Ương</h2>
                </div>

                <div class="mayhen_invitation_ksnvo949dudf_couple-grid">
                    {{-- Chú rể --}}
                    <div class="mayhen_invitation_ksnvo949dudf_person-card mayhen_invitation_ksnvo949dudf_box-anim">
                        <div class="mayhen_invitation_ksnvo949dudf_person-photo-frame">
                            <img id="sync_groom_photo" src="{{ $groomPhotoUrl }}" alt="Chú rể {{ $groomName }}" loading="lazy">
                        </div>
                        <div class="mayhen_invitation_ksnvo949dudf_person-info">
                            <span class="mayhen_invitation_ksnvo949dudf_person-role">CHÚ RỂ</span>
                            <h3 data-sync-target="groom_name">{{ $groomName }}</h3>
                            <p>“Cảm ơn em đã bước vào cuộc đời anh, biến những ngày bình thường thành những ngày ngập tràn yêu thương và hạnh phúc.”</p>
                        </div>
                    </div>

                    {{-- Cô dâu --}}
                    <div class="mayhen_invitation_ksnvo949dudf_person-card mayhen_invitation_ksnvo949dudf_box-anim">
                        <div class="mayhen_invitation_ksnvo949dudf_person-photo-frame">
                            <img id="sync_bride_photo" src="{{ $bridePhotoUrl }}" alt="Cô dâu {{ $brideName }}" loading="lazy">
                        </div>
                        <div class="mayhen_invitation_ksnvo949dudf_person-info">
                            <span class="mayhen_invitation_ksnvo949dudf_person-role">CÔ DÂU</span>
                            <h3 data-sync-target="bride_name">{{ $brideName }}</h3>
                            <p>“Ở bên anh, em tìm thấy sự bình yên và tin tưởng. Em muốn cùng anh bước tiếp trên mọi chặng đường tương lai.”</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 4: ĐẾM NGƯỢC NGÀY HẠNH PHÚC --}}
        <section class="mayhen_invitation_ksnvo949dudf_section mayhen_invitation_ksnvo949dudf_countdown-section">
            <div class="mayhen_invitation_ksnvo949dudf_container">
                <div class="mayhen_invitation_ksnvo949dudf_countdown-box mayhen_invitation_ksnvo949dudf_box-anim">
                    <span class="mayhen_invitation_ksnvo949dudf_kicker">COUNTDOWN TO OUR BIG DAY</span>
                    <h2 class="mayhen_invitation_ksnvo949dudf_countdown-title">Cùng Đếm Ngược Ngày Vui</h2>
                    
                    <div class="mayhen_invitation_ksnvo949dudf_countdown-digits">
                        <div class="mayhen_invitation_ksnvo949dudf_timer-unit">
                            <strong id="mayhen_invitation_ksnvo949dudf_days">00</strong>
                            <span>NGÀY</span>
                        </div>
                        <div class="mayhen_invitation_ksnvo949dudf_timer-sep">:</div>
                        <div class="mayhen_invitation_ksnvo949dudf_timer-unit">
                            <strong id="mayhen_invitation_ksnvo949dudf_hours">00</strong>
                            <span>GIỜ</span>
                        </div>
                        <div class="mayhen_invitation_ksnvo949dudf_timer-sep">:</div>
                        <div class="mayhen_invitation_ksnvo949dudf_timer-unit">
                            <strong id="mayhen_invitation_ksnvo949dudf_minutes">00</strong>
                            <span>PHÚT</span>
                        </div>
                        <div class="mayhen_invitation_ksnvo949dudf_timer-sep">:</div>
                        <div class="mayhen_invitation_ksnvo949dudf_timer-unit">
                            <strong id="mayhen_invitation_ksnvo949dudf_seconds">00</strong>
                            <span>GIÂY</span>
                        </div>
                    </div>

                    <p class="mayhen_invitation_ksnvo949dudf_countdown-date">{{ $weddingFullDate }}</p>
                </div>
            </div>
        </section>

        {{-- SECTION 5: THÔNG TIN SỰ KIỆN & BẢN ĐỒ --}}
        <section class="mayhen_invitation_ksnvo949dudf_section mayhen_invitation_ksnvo949dudf_events-section">
            <div class="mayhen_invitation_ksnvo949dudf_container">
                <div class="mayhen_invitation_ksnvo949dudf_section-title-wrap">
                    <span class="mayhen_invitation_ksnvo949dudf_kicker">WEDDING EVENTS</span>
                    <h2 class="mayhen_invitation_ksnvo949dudf_sec-title">Thông Tin Hôn Lễ & Tiệc Cưới</h2>
                </div>

                <div class="mayhen_invitation_ksnvo949dudf_events-grid">
                    {{-- Lễ Thành Hôn --}}
                    <div class="mayhen_invitation_ksnvo949dudf_event-card mayhen_invitation_ksnvo949dudf_box-anim">
                        <div class="mayhen_invitation_ksnvo949dudf_event-badge">LỄ THÀNH HÔN</div>
                        <div class="mayhen_invitation_ksnvo949dudf_event-time">{{ $ceremonyTime }}</div>
                        <h3 class="mayhen_invitation_ksnvo949dudf_event-venue" data-sync-target="ceremony_venue">{{ $ceremonyVenue }}</h3>
                        <p class="mayhen_invitation_ksnvo949dudf_event-address" data-sync-target="ceremony_address">{{ $ceremonyAddress }}</p>
                        
                        <div class="mayhen_invitation_ksnvo949dudf_event-actions">
                            <a href="{{ $ceremonyMapLink }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_ksnvo949dudf_event-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span>Chỉ Đường Google Maps</span>
                            </a>
                        </div>
                    </div>

                    {{-- Tiệc Cưới --}}
                    <div class="mayhen_invitation_ksnvo949dudf_event-card mayhen_invitation_ksnvo949dudf_event-card--highlight mayhen_invitation_ksnvo949dudf_box-anim">
                        <div class="mayhen_invitation_ksnvo949dudf_event-badge">TIỆC MỪNG CƯỚI</div>
                        <div class="mayhen_invitation_ksnvo949dudf_event-time">{{ $partyTime }}</div>
                        <h3 class="mayhen_invitation_ksnvo949dudf_event-venue" data-sync-target="party_venue">{{ $partyVenue }}</h3>
                        <p class="mayhen_invitation_ksnvo949dudf_event-address" data-sync-target="party_address">{{ $partyAddress }}</p>
                        
                        <div class="mayhen_invitation_ksnvo949dudf_event-actions">
                            <a href="{{ $partyMapLink }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_ksnvo949dudf_event-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span>Chỉ Đường Google Maps</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- LỊCH TRÌNH NGÀY CƯỚI --}}
                <div class="mayhen_invitation_ksnvo949dudf_timeline-wrap mayhen_invitation_ksnvo949dudf_box-anim">
                    <h3 class="mayhen_invitation_ksnvo949dudf_timeline-title">Lịch Trình Hôn Lễ</h3>
                    <div class="mayhen_invitation_ksnvo949dudf_timeline-grid">
                        <div class="mayhen_invitation_ksnvo949dudf_timeline-item mayhen_invitation_ksnvo949dudf_box-anim">
                            <span class="mayhen_invitation_ksnvo949dudf_timeline-time">10:00</span>
                            <strong>Đón Tiếp Khách Quý</strong>
                            <p>Chào đón người thân & bạn bè</p>
                        </div>
                        <div class="mayhen_invitation_ksnvo949dudf_timeline-item mayhen_invitation_ksnvo949dudf_box-anim">
                            <span class="mayhen_invitation_ksnvo949dudf_timeline-time">11:00</span>
                            <strong>Làm Lễ Thành Hôn</strong>
                            <p>Trao nhẫn & lời nguyện thề</p>
                        </div>
                        <div class="mayhen_invitation_ksnvo949dudf_timeline-item mayhen_invitation_ksnvo949dudf_box-anim">
                            <span class="mayhen_invitation_ksnvo949dudf_timeline-time">11:30</span>
                            <strong>Khai Tiệc Mừng</strong>
                            <p>Nâng ly chúc mừng hạnh phúc</p>
                        </div>
                        <div class="mayhen_invitation_ksnvo949dudf_timeline-item mayhen_invitation_ksnvo949dudf_box-anim">
                            <span class="mayhen_invitation_ksnvo949dudf_timeline-time">12:30</span>
                            <strong>Chụp Ảnh Kỷ Niệm</strong>
                            <p>Lưu giữ khoảnh khắc quý giá</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 6: ALBUM ẢNH CƯỚI ART GALLERY --}}
        <section class="mayhen_invitation_ksnvo949dudf_section mayhen_invitation_ksnvo949dudf_gallery-section">
            <div class="mayhen_invitation_ksnvo949dudf_container">
                <div class="mayhen_invitation_ksnvo949dudf_section-title-wrap">
                    <span class="mayhen_invitation_ksnvo949dudf_kicker">SWEET MEMORIES</span>
                    <h2 class="mayhen_invitation_ksnvo949dudf_sec-title">Khoảnh Khắc Hạnh Phúc</h2>
                </div>

                <div class="mayhen_invitation_ksnvo949dudf_gallery-grid" id="mayhen_invitation_ksnvo949dudf_gallery">
                    @foreach ($galleryPhotos as $idx => $photoUrl)
                        <div class="mayhen_invitation_ksnvo949dudf_gallery-item mayhen_invitation_ksnvo949dudf_box-anim" data-index="{{ $idx }}" role="button" tabindex="0" aria-label="Xem ảnh cưới {{ $idx + 1 }}">
                            <div class="mayhen_invitation_ksnvo949dudf_photo-frame">
                                <img src="{{ $photoUrl }}" alt="Ảnh cưới {{ $idx + 1 }}" loading="lazy">
                                <div class="mayhen_invitation_ksnvo949dudf_photo-overlay">
                                    <span>Phóng to</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- SECTION 7: HỘP MỪNG CƯỚI VIETQR --}}
        <section class="mayhen_invitation_ksnvo949dudf_section mayhen_invitation_ksnvo949dudf_gift-section">
            <div class="mayhen_invitation_ksnvo949dudf_container">
                <div class="mayhen_invitation_ksnvo949dudf_gift-card mayhen_invitation_ksnvo949dudf_box-anim">
                    <span class="mayhen_invitation_ksnvo949dudf_kicker">WEDDING GIFT</span>
                    <h2 class="mayhen_invitation_ksnvo949dudf_gift-title">Hộp Mừng Cưới Chúc Phúc</h2>
                    <p class="mayhen_invitation_ksnvo949dudf_gift-sub">
                        Sự hiện diện của bạn là món quà ý nghĩa nhất. Nếu muốn gửi thêm lời chúc mừng từ xa, bạn có thể gửi qua số tài khoản dưới đây:
                    </p>

                    <div class="mayhen_invitation_ksnvo949dudf_accounts-grid">
                        {{-- Tài khoản Chú rể --}}
                        <div class="mayhen_invitation_ksnvo949dudf_account-box mayhen_invitation_ksnvo949dudf_box-anim">
                            <span class="mayhen_invitation_ksnvo949dudf_acc-tag">CHÚ RỂ</span>
                            <h4 data-sync-target="groom_name">{{ $groomName }}</h4>
                            <div class="mayhen_invitation_ksnvo949dudf_qr-wrap">
                                <img src="{{ $groomBankInfo['qr'] }}" alt="Mã QR Chú rể" loading="lazy">
                            </div>
                            <p class="mayhen_invitation_ksnvo949dudf_bank-name">{{ $groomBankInfo['bank'] }}</p>
                            <p class="mayhen_invitation_ksnvo949dudf_account-num">
                                <strong id="groom_acc_num">{{ $groomBankInfo['number'] }}</strong>
                            </p>
                            <p class="mayhen_invitation_ksnvo949dudf_account-owner">{{ $groomBankInfo['name'] }}</p>
                            <button class="mayhen_invitation_ksnvo949dudf_copy-btn" data-copy="{{ $groomBankInfo['number'] }}" type="button">
                                Sao Chép Số Tài Khoản
                            </button>
                        </div>

                        {{-- Tài khoản Cô dâu --}}
                        <div class="mayhen_invitation_ksnvo949dudf_account-box mayhen_invitation_ksnvo949dudf_box-anim">
                            <span class="mayhen_invitation_ksnvo949dudf_acc-tag">CÔ DÂU</span>
                            <h4 data-sync-target="bride_name">{{ $brideName }}</h4>
                            <div class="mayhen_invitation_ksnvo949dudf_qr-wrap">
                                <img src="{{ $brideBankInfo['qr'] }}" alt="Mã QR Cô dâu" loading="lazy">
                            </div>
                            <p class="mayhen_invitation_ksnvo949dudf_bank-name">{{ $brideBankInfo['bank'] }}</p>
                            <p class="mayhen_invitation_ksnvo949dudf_account-num">
                                <strong id="bride_acc_num">{{ $brideBankInfo['number'] }}</strong>
                            </p>
                            <p class="mayhen_invitation_ksnvo949dudf_account-owner">{{ $brideBankInfo['name'] }}</p>
                            <button class="mayhen_invitation_ksnvo949dudf_copy-btn" data-copy="{{ $brideBankInfo['number'] }}" type="button">
                                Sao Chép Số Tài Khoản
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 8: SỔ LƯU BÚT & RSVP --}}
        <section class="mayhen_invitation_ksnvo949dudf_section mayhen_invitation_ksnvo949dudf_interactive-section">
            <div class="mayhen_invitation_ksnvo949dudf_container">
                <div class="mayhen_invitation_ksnvo949dudf_interactive-grid">
                    
                    {{-- SỔ LƯU BÚT --}}
                    <div class="mayhen_invitation_ksnvo949dudf_form-panel mayhen_invitation_ksnvo949dudf_box-anim" id="mayhen_invitation_ksnvo949dudf_wishes">
                        <span class="mayhen_invitation_ksnvo949dudf_kicker">GUESTBOOK</span>
                        <h2 class="mayhen_invitation_ksnvo949dudf_panel-title">Sổ Lưu Bút Chúc Mừng</h2>
                        <p class="mayhen_invitation_ksnvo949dudf_panel-desc">Gửi những lời chúc tốt đẹp nhất đến cặp đôi mới cưới:</p>

                        <form class="mayhen_invitation_ksnvo949dudf_form" id="mayhen_invitation_ksnvo949dudf_wish-form">
                            <div class="mayhen_invitation_ksnvo949dudf_form-group">
                                <label for="wish_sender">Tên của bạn <span>*</span></label>
                                <input type="text" id="wish_sender" name="name" placeholder="Ví dụ: Hoàng Tuấn, Thu Trang..." required>
                            </div>
                            <div class="mayhen_invitation_ksnvo949dudf_form-group">
                                <label for="wish_content">Lời chúc phúc <span>*</span></label>
                                <textarea id="wish_content" name="message" rows="4" placeholder="Chúc hai bạn trăm năm hạnh phúc, mãi mãi mặn nồng..." required></textarea>
                            </div>
                            <button type="submit" class="mayhen_invitation_ksnvo949dudf_submit-btn" id="mayhen_invitation_ksnvo949dudf_submit-wish">
                                <span>Gửi Lời Chúc Phúc</span>
                            </button>
                            <p class="mayhen_invitation_ksnvo949dudf_notice" id="mayhen_invitation_ksnvo949dudf_wish-notice" aria-live="polite"></p>
                        </form>

                        <div class="mayhen_invitation_ksnvo949dudf_wishes-stream" id="mayhen_invitation_ksnvo949dudf_wish-list">
                            @if ($wishes->isNotEmpty())
                                @foreach ($wishes as $w)
                                    <div class="mayhen_invitation_ksnvo949dudf_wish-card mayhen_invitation_ksnvo949dudf_box-anim">
                                        <p class="mayhen_invitation_ksnvo949dudf_wish-msg">“{{ $w->message }}”</p>
                                        <span class="mayhen_invitation_ksnvo949dudf_wish-author">— {{ $w->name }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="mayhen_invitation_ksnvo949dudf_wish-card mayhen_invitation_ksnvo949dudf_box-anim">
                                    <p class="mayhen_invitation_ksnvo949dudf_wish-msg">“Chúc hai bạn một hành trình mới ngập tràn niềm vui, tiếng cười và hạnh phúc viên mãn!”</p>
                                    <span class="mayhen_invitation_ksnvo949dudf_wish-author">— Bạn bè & Người thân</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- XÁC NHẬN THAM DỰ RSVP --}}
                    <div class="mayhen_invitation_ksnvo949dudf_form-panel mayhen_invitation_ksnvo949dudf_box-anim" id="mayhen_invitation_ksnvo949dudf_rsvp">
                        <span class="mayhen_invitation_ksnvo949dudf_kicker">RSVP</span>
                        <h2 class="mayhen_invitation_ksnvo949dudf_panel-title">Xác Nhận Tham Dự</h2>
                        <p class="mayhen_invitation_ksnvo949dudf_panel-desc">Vui lòng phản hồi sớm để chúng mình chuẩn bị đón tiếp chu đáo nhất:</p>

                        <form class="mayhen_invitation_ksnvo949dudf_form" id="mayhen_invitation_ksnvo949dudf_rsvp-form">
                            <div class="mayhen_invitation_ksnvo949dudf_form-group">
                                <label for="rsvp_guest_name">Họ và tên khách mời <span>*</span></label>
                                <input type="text" id="rsvp_guest_name" name="guest_name" value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}" placeholder="Nhập họ và tên..." required>
                            </div>
                            <div class="mayhen_invitation_ksnvo949dudf_form-group">
                                <label for="rsvp_phone">Số điện thoại <span>*</span></label>
                                <input type="tel" id="rsvp_phone" name="phone" placeholder="Số điện thoại liên hệ..." required>
                            </div>
                            <div class="mayhen_invitation_ksnvo949dudf_form-group">
                                <label>Bạn sẽ tham dự chứ?</label>
                                <div class="mayhen_invitation_ksnvo949dudf_radio-group">
                                    <label class="mayhen_invitation_ksnvo949dudf_radio-pill">
                                        <input type="radio" name="attending" value="yes" checked>
                                        <span>Chắc chắn sẽ tới tham dự</span>
                                    </label>
                                    <label class="mayhen_invitation_ksnvo949dudf_radio-pill">
                                        <input type="radio" name="attending" value="no">
                                        <span>Rất tiếc không thể tham dự</span>
                                    </label>
                                </div>
                            </div>
                            <div class="mayhen_invitation_ksnvo949dudf_form-group">
                                <label for="rsvp_count">Số lượng người tham dự</label>
                                <select id="rsvp_count" name="guest_count">
                                    <option value="1">Đi 1 người</option>
                                    <option value="2">Đi 2 người (Kèm bạn/người thân)</option>
                                    <option value="3">Gia đình (3 người)</option>
                                    <option value="4">Gia đình (4 người trở lên)</option>
                                </select>
                            </div>
                            <button type="submit" class="mayhen_invitation_ksnvo949dudf_submit-btn" id="mayhen_invitation_ksnvo949dudf_submit-rsvp">
                                <span>Xác Nhận Tham Dự</span>
                            </button>
                            <p class="mayhen_invitation_ksnvo949dudf_notice" id="mayhen_invitation_ksnvo949dudf_rsvp-notice" aria-live="polite"></p>
                        </form>
                    </div>

                </div>
            </div>
        </section>

        {{-- SECTION 9: LỜI CẢM TẠ CUỐI THIỆP (THAY THẾ HOÀN TOÀN FOOTER WEBSITE) --}}
        <section class="mayhen_invitation_ksnvo949dudf_ending-section">
            <div class="mayhen_invitation_ksnvo949dudf_container">
                <div class="mayhen_invitation_ksnvo949dudf_ending-box mayhen_invitation_ksnvo949dudf_box-anim">
                    <span class="mayhen_invitation_ksnvo949dudf_kicker">WITH DEEP GRATITUDE</span>
                    <h2 class="mayhen_invitation_ksnvo949dudf_ending-title">Chân Thành Cảm Ơn</h2>
                    <p class="mayhen_invitation_ksnvo949dudf_ending-text">
                        Sự hiện diện, lời chúc phúc và tình cảm yêu thương của Quý khách chính là món quà quý giá nhất trong ngày trọng đại của chúng mình.
                    </p>
                    <div class="mayhen_invitation_ksnvo949dudf_ending-sign">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <small>&</small>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </div>
                    <p class="mayhen_invitation_ksnvo949dudf_ending-date">{{ $weddingDateLong }}</p>
                </div>
            </div>
        </section>

    </main>

    {{-- LIGHTBOX XEM ẢNH RETINA --}}
    <div class="mayhen_invitation_ksnvo949dudf_lightbox" id="mayhen_invitation_ksnvo949dudf_lightbox" hidden>
        <button class="mayhen_invitation_ksnvo949dudf_lightbox-close" id="mayhen_invitation_ksnvo949dudf_lb_close" type="button" aria-label="Đóng">✕</button>
        <button class="mayhen_invitation_ksnvo949dudf_lightbox-arrow mayhen_invitation_ksnvo949dudf_lightbox-prev" id="mayhen_invitation_ksnvo949dudf_lb_prev" type="button" aria-label="Ảnh trước">‹</button>
        <button class="mayhen_invitation_ksnvo949dudf_lightbox-arrow mayhen_invitation_ksnvo949dudf_lightbox-next" id="mayhen_invitation_ksnvo949dudf_lb_next" type="button" aria-label="Ảnh sau">›</button>
        <div class="mayhen_invitation_ksnvo949dudf_lightbox-content">
            <img id="mayhen_invitation_ksnvo949dudf_lb_img" src="" alt="Ảnh cưới phóng to">
        </div>
    </div>

    {{-- THẺ AUDIO NỀN NHẠC CƯỚI NATIVE --}}
    <audio id="mayhen_invitation_ksnvo949dudf_audio" loop preload="auto">
        <source src="{{ $musicUrl }}" type="audio/mpeg">
    </audio>

    {{-- TOAST THÔNG BÁO TINH TẾ --}}
    <div class="mayhen_invitation_ksnvo949dudf_toast" id="mayhen_invitation_ksnvo949dudf_toast" role="status" aria-live="polite"></div>

    {{-- LIVE PREVIEW STUDIO V2 BRIDGE --}}
    @include('client.invitations.partials.editor-bridge')

    {{-- KHỞI TẠO BIẾN TOÀN CỤC CHO SCRIPT.JS --}}
    <script>
        window.__MAYHEN_WEDDING_TIMESTAMP = {{ (int) $targetTimestamp }};
        window.__MAYHEN_WISH_SUBMIT_URL = "{{ $wishSubmitUrl }}";
        window.__MAYHEN_RSVP_SUBMIT_URL = "{{ $rsvpSubmitUrl }}";
        window.__MAYHEN_CSRF_TOKEN = "{{ csrf_token() }}";
        window.__MAYHEN_GALLERY_PHOTOS = @json($galleryPhotos);
    </script>
    <script src="{{ $assetBase }}/script.js?v={{ $jsVersion }}"></script>
    @if (($isPreview ?? false) && !($isEmbed ?? false))
        @include('client.components.bottom-nav')
    @endif
</body>

</html>
