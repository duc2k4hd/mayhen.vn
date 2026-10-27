@php
    $assetBase = url('/mau-thiep/thuoc-phim-tinh-yeu/assets');
    $isEmbed = request()->boolean('embed');

    // 1. Tên cô dâu chú rể & Gia đình
    $groomName = $wedding->groom_name ?? 'Tràng Đức';
    $brideName = $wedding->bride_name ?? 'Ngọc Phượng';
    $extraData = $wedding->extra_data ?? [];

    // Phân tách bố mẹ thông minh: Ưu tiên extra_data riêng biệt, fallback parse từ text cũ hoặc default
    $parseParents = function ($text, $defaultFather, $defaultMother) {
        $lines = preg_split('/[\r\n&]+/u', trim((string) $text));
        $father = $defaultFather;
        $mother = $defaultMother;
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(?:ÔNG|Ông|Bố|Cha)[:\s-]*(.*)$/ui', $line, $m)) {
                $father = trim($m[1]) ?: $father;
            } elseif (preg_match('/^(?:BÀ|Bà|Mẹ)[:\s-]*(.*)$/ui', $line, $m)) {
                $mother = trim($m[1]) ?: $mother;
            }
        }
        return [$father, $mother];
    };
    [$legacyGroomFather, $legacyGroomMother] = $parseParents(
        $wedding->groom_parents ?? '',
        'Nguyễn Tràng Giang',
        'Nguyễn Thị Tám',
    );
    [$legacyBrideFather, $legacyBrideMother] = $parseParents(
        $wedding->bride_parents ?? '',
        'Đỗ Như Trung',
        'Lê Thị Hảo',
    );

    $groomFather = !empty($extraData['groom_father']) ? $extraData['groom_father'] : $legacyGroomFather;
    $groomMother = !empty($extraData['groom_mother']) ? $extraData['groom_mother'] : $legacyGroomMother;
    $brideFather = !empty($extraData['bride_father']) ? $extraData['bride_father'] : $legacyBrideFather;
    $brideMother = !empty($extraData['bride_mother']) ? $extraData['bride_mother'] : $legacyBrideMother;

    // 2. Ngày giờ và sảnh tiệc
    $weddingDate = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2027, 3, 7, 7, 0, 0);
    $coverDateFormatted = $weddingDate->format('d.m.Y');
    $targetTimestamp = $weddingDate->timestamp * 1000;
    $calendarMonth = $weddingDate->format('m');
    $calendarYear = $weddingDate->format('Y');
    $calendarDay = $weddingDate->day;

    // Sự kiện Nhà Gái
    $brideCeremonyTitle = $wedding->extra_data['bride_ceremony_title'] ?? 'Lễ Vu Quy';
    $brideCeremonyTime = $wedding->extra_data['bride_ceremony_time'] ?? '07:00';
    $brideCeremonyDayName = $wedding->extra_data['bride_ceremony_day_name'] ?? 'Chủ Nhật';
    $brideCeremonyDate = $wedding->extra_data['bride_ceremony_date'] ?? '07/03/2027';
    $brideCeremonyDayNum = explode('/', $brideCeremonyDate)[0] ?? '07';
    $brideCeremonyMonthNum = explode('/', $brideCeremonyDate)[1] ?? '03';
    $brideCeremonyYearNum = explode('/', $brideCeremonyDate)[2] ?? '2027';
    $brideCeremonyLunar = $wedding->extra_data['bride_ceremony_lunar'] ?? '(Nhằm ngày 30 tháng 1 năm Đinh Mùi)';
    $brideCeremonyAddress = $wedding->extra_data['bride_ceremony_address'] ?? 'Xóm 22, Khoái Châu, Hưng Yên';
    $brideMapLink =
        $wedding->extra_data['bride_map_link'] ??
        'https://www.google.com/maps/search/?api=1&query=' . urlencode($brideCeremonyAddress);

    // Sự kiện Nhà Trai / Tiệc Cưới
    $groomPartyTitle = $wedding->extra_data['groom_party_title'] ?? 'Tiệc Cưới';
    $groomPartyTime = $wedding->extra_data['groom_party_time'] ?? '17:00';
    $groomPartyDayName = $wedding->extra_data['groom_party_day_name'] ?? 'Thứ Sáu';
    $groomPartyDate = $wedding->extra_data['groom_party_date'] ?? '06/03/2026';
    $groomPartyDayNum = explode('/', $groomPartyDate)[0] ?? '06';
    $groomPartyMonthNum = explode('/', $groomPartyDate)[1] ?? '03';
    $groomPartyYearNum = explode('/', $groomPartyDate)[2] ?? '2026';
    $groomPartyLunar = $wedding->extra_data['groom_party_lunar'] ?? '(Nhằm ngày 18 tháng 1 năm Bính Ngọ)';
    $groomPartyAddress = $wedding->extra_data['groom_party_address'] ?? 'Xóm 22, Khoái Châu, Hưng Yên';
    $groomMapLink =
        $wedding->extra_data['groom_map_link'] ??
        'https://www.google.com/maps/search/?api=1&query=' . urlencode($groomPartyAddress);

    // Hàm resolve asset chuẩn
    $resolveAsset = function (?string $path, string $fallback = '') use ($assetBase): string {
        $val = trim((string) $path);
        if (str_starts_with($val, 'blob:')) {
            $val = '';
        }
        if (empty($val)) {
            $fb = trim($fallback);
            if (empty($fb)) {
                return '';
            }
            return str_starts_with($fb, 'http://') || str_starts_with($fb, 'https://') || str_starts_with($fb, '/')
                ? $fb
                : $assetBase . '/' . ltrim($fb, '/');
        }
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://') || str_starts_with($val, '/')) {
            return $val;
        }
        return $assetBase . '/' . ltrim($val, '/');
    };

    // 3. Ảnh cưới
    $coverPhotoUrl = $resolveAsset($wedding->cover_photo_url ?? null, '3ed9dcf0-26a2-47ad-af9e-8895bff01519.jpg');
    $heroPhotoUrl = $resolveAsset(
        $wedding->extra_data['hero_photo_url'] ?? null,
        '3ed9dcf0-26a2-47ad-af9e-8895bff01519.jpg',
    );
    $triptych1 = $resolveAsset(
        $wedding->extra_data['triptych_photo_1'] ?? null,
        'c3ee7507-53b8-461b-b5cb-c416ffc03791.jpg',
    );
    $triptych2 = $resolveAsset(
        $wedding->extra_data['triptych_photo_2'] ?? null,
        'feecd763-f272-424b-9985-b4f3d68d7fb1.jpg',
    );
    $triptych3 = $resolveAsset(
        $wedding->extra_data['triptych_photo_3'] ?? null,
        '320f95e0-b0c7-4b48-9d11-16c791076898.jpg',
    );
    $footerPhotoUrl = $resolveAsset(
        $wedding->extra_data['footer_photo_url'] ?? null,
        '8abb1d4e-e543-4ccf-994d-7114bbdfa561.jpg',
    );

    // 10 ảnh trong Album cưới
    $defaultAlbum = [
        '17561028-8353-443f-9499-3cacf8e00f39.jpg',
        '3d8c97cc-9a05-42c1-916e-c76f3e635f19.jpg',
        '5bf6d6e7-c1f7-4b0b-bf30-011dc81a7191.jpg',
        '58d06c3d-d76a-437b-8661-7058afa6798a.jpg',
        '42c5230a-1ea7-4043-906f-545987e51dee.jpg',
        'b0406e5d-ad0e-4074-87cc-3cfb05978f06.jpg',
        '96c5fed3-ce54-4fd7-83e7-2e7e5019190a.jpg',
        '7fbc223f-7c5e-4438-a3c7-6c2aff07be19.jpg',
        '6b382386-e964-4bfa-80f0-25285128f65d.jpg',
        'e5ed00b3-76fb-4467-bf7f-d9c436472f4e.jpg',
    ];
    $rawGallery = $wedding->extra_data['gallery'] ?? [];
    $validGallery = is_array($rawGallery)
        ? array_filter(
            $rawGallery,
            fn($item) => !empty(trim((string) $item)) && !str_starts_with((string) $item, 'blob:'),
        )
        : [];
    $selectedAlbum = !empty($validGallery) ? array_values($validGallery) : $defaultAlbum;
    $albumPhotos = array_map(fn($p) => $resolveAsset($p), $selectedAlbum);

    // 4. Lời ngỏ
    $loveQuote = $wedding->story ?? 'Tình yêu là câu chuyện đẹp đẽ nhất mà chúng ta cùng nhau viết nên.';

    // 5. Mừng cưới QR & Ngân hàng
    $qrGroom = $resolveAsset(
        $wedding->extra_data['qr_groom_image'] ?? null,
        '4904bdb1-f9fa-4fdc-a3b2-f6a0be4df18b.jpg',
    );
    $qrBride = $resolveAsset(
        $wedding->extra_data['qr_bride_image'] ?? null,
        'b9782cd0-2a24-412c-bd15-2626bbe77477.jpg',
    );
    $groomBankInfo = [
        'bank' => $wedding->extra_data['groom_bank_name'] ?? 'Vietcombank',
        'name' => $wedding->extra_data['groom_account_name'] ?? $groomName,
        'number' => $wedding->extra_data['groom_account_number'] ?? '',
        'qr' => $qrGroom,
    ];
    $brideBankInfo = [
        'bank' => $wedding->extra_data['bride_bank_name'] ?? 'Techcombank',
        'name' => $wedding->extra_data['bride_account_name'] ?? $brideName,
        'number' => $wedding->extra_data['bride_account_number'] ?? '',
        'qr' => $qrBride,
    ];

    // 6. Nhạc nền
    $musicUrl = $resolveAsset($wedding->music_url ?? null, 'c236c024-0b4a-472e-a8fb-f64e2fead1a0.mp3');

    // 7. Sổ lưu bút & RSVP
    $wishes = $wishes ?? collect([]);
    $wishSubmitUrl =
        $wishSubmitUrl ??
        (isset($invitation)
            ? route('invitation.wish.store', $invitation->slug)
            : route('templates.preview.wish', 'thuoc-phim-tinh-yeu'));
    $rsvpSubmitUrl =
        $rsvpSubmitUrl ??
        (isset($invitation)
            ? route('rsvp.submit', $invitation->slug)
            : route('templates.preview.rsvp', 'thuoc-phim-tinh-yeu'));
    $currentRsvp = $currentRsvp ?? null;
    $myWish = $myWish ?? null;
    $isOwner = $isOwner ?? false;
    $rsvpStats = $rsvpStats ?? null;
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Thước Phim Tình Yêu';
    $pageTitle = $isPreview 
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name') 
        : 'Thiệp mời cưới: "' . $groomName . ' & ' . $brideName . '" | ' . config('app.name');

    $cssPath = resource_path('views/client/invitations/thuoc-phim-tinh-yeu/styles.css');
    $jsPath = resource_path('views/client/invitations/thuoc-phim-tinh-yeu/script.js');
    $cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
    $jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();
@endphp
<!doctype html>
<html lang="vi" class="mayhen_invitation_djcvhwiuo9w9_no-scroll">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="{{ $isPreview ? 'Trải nghiệm xem thử mẫu thiệp cưới ' . $templateName . ' trên ' . config('app.name') : 'Thiệp cưới ' . $groomName . ' & ' . $brideName . ' – ngày ' . $coverDateFormatted . '.' }}">
    <title>{{ $pageTitle }}</title>
    @include('client.components.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Great+Vibes&family=Manrope:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ $assetBase }}/styles.css?v={{ $cssVersion }}">
    @if (($isPreview ?? false) && !($isEmbed ?? false))
        <link rel="stylesheet" href="{{ asset('assets/client/css/components/bottom-nav.css') }}">
    @endif

    @php
        $customConfig = $invitation->custom_config ?? [];
        $themeConfig = $customConfig['theme'] ?? [];
        $waxSealConfig = $customConfig['wax_seal'] ?? [];
        $sectionsHidden = $customConfig['sections_hidden'] ?? [];

        $themeBg = $themeConfig['bg'] ?? null;
        $themePrimary = $themeConfig['primary'] ?? null;
        $themeAccent = $themeConfig['accent'] ?? null;
        $themeText = $themeConfig['text'] ?? null;

        $sealColor = $waxSealConfig['color'] ?? null;
        $sealMonogram = $waxSealConfig['monogram'] ?? null;
    @endphp

    <style id="mayhen_custom_theme_style">
        :root {
            @if ($themeBg)
                --paper: {{ $themeBg }} !important;
                --paper-dim: {{ $themeBg }} !important;
            @endif
            @if ($themeText)
                --ink: {{ $themeText }} !important;
            @endif
            @if ($themeAccent)
                --border: {{ $themeAccent }} !important;
            @endif
            @if ($themePrimary)
                --primary: {{ $themePrimary }} !important;
            @endif
        }

        @if ($themePrimary)
            .mayhen_invitation_djcvhwiuo9w9_wedding-text,
            .mayhen_invitation_djcvhwiuo9w9_location-button,
            .mayhen_invitation_djcvhwiuo9w9_form-button,
            .mayhen_invitation_djcvhwiuo9w9_music-toggle {
                color: {{ $themePrimary }} !important;
            }

            .mayhen_invitation_djcvhwiuo9w9_side-switch button.mayhen_invitation_djcvhwiuo9w9_active,
            .mayhen_invitation_djcvhwiuo9w9_rsvp-options button.mayhen_invitation_djcvhwiuo9w9_active,
            .mayhen_invitation_djcvhwiuo9w9_form-button {
                background-color: {{ $themePrimary }} !important;
                color: #ffffff !important;
            }
        @endif
        @if ($sealColor)
            .mayhen_invitation_djcvhwiuo9w9_seal-pulse-glow {
                box-shadow: 0 0 0 10px {{ $sealColor }}33, 0 0 0 20px {{ $sealColor }}1a !important;
            }
        @endif
        @if (!empty($sectionsHidden))
            @foreach ($sectionsHidden as $hiddenId)
                [data-mayhen-section="{{ $hiddenId }}"] {
                    display: none !important;
                }
            @endforeach
        @endif
    </style>

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

            /* Gỡ bỏ hoàn toàn mọi lớp khóa cuộn do script.js thêm vào */
            html.mayhen_invitation_djcvhwiuo9w9_no-scroll,
            body.mayhen_invitation_djcvhwiuo9w9_no-scroll,
            .mayhen_invitation_djcvhwiuo9w9_no-scroll {
                overflow-x: hidden !important;
                overflow-y: auto !important;
                height: 100% !important;
                overscroll-behavior: auto !important;
            }

            .mayhen_invitation_djcvhwiuo9w9_root {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: 100% !important;
                overflow: visible !important;
            }

            /* Mở sẵn 2 cánh thiệp khi ở trong Studio Editor Preview để người dùng cuộn xem trực tiếp */
            .mayhen_invitation_djcvhwiuo9w9_opening-sides {
                display: none !important;
                pointer-events: none !important;
                visibility: hidden !important;
            }

            .mayhen_invitation_djcvhwiuo9w9_card-wrapper {
                display: block !important;
            }

            /* Thanh cuộn siêu mảnh, mềm mại, màu vàng đồng Quiet Luxury rõ nét */
            html,
            body {
                scrollbar-width: thin !important;
                scrollbar-color: #B45309 rgba(0, 0, 0, 0.08) !important;
            }

            ::-webkit-scrollbar {
                width: 6px !important;
                height: 6px !important;
            }

            ::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.06) !important;
                border-radius: 999px !important;
            }

            ::-webkit-scrollbar-thumb {
                background: #B45309 !important;
                border-radius: 999px !important;
                border: 1px solid rgba(255, 255, 255, 0.8) !important;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #78350F !important;
            }
        </style>
    @endif
</head>

<body class="mayhen_invitation_wrapper mayhen_invitation_djcvhwiuo9w9_no-scroll">
    <main class="mayhen_invitation_djcvhwiuo9w9_root mayhen_invitation_djcvhwiuo9w9_invitation-page" lang="vi"
        style='--mayhen-g4-opening-left:url("{{ $assetBase }}/opening-left.webp");--mayhen-g4-opening-right:url("{{ $assetBase }}/opening-right.webp");--mayhen-g4-paper-base:url("{{ $assetBase }}/paper-base-seamless.jpg");--mayhen-g4-paper-top:url("{{ $assetBase }}/paper-top-textured.jpg");--mayhen-g4-paper-countdown-bottom:url("{{ $assetBase }}/paper-countdown-bottom-textured.jpg");--mayhen-g4-paper-corner:url("{{ $assetBase }}/paper-corner-textured.jpg");--mayhen-g4-paper-bottom:url("{{ $assetBase }}/paper-bottom-textured.jpg");--mayhen-g4-album-paper:url("{{ $assetBase }}/album-paper-textured.jpg");--mayhen-g4-paper-footer:url("{{ $assetBase }}/paper-footer-textured.jpg");--mayhen-g4-paper-top-overlay:url("{{ $assetBase }}/paper-top-overlay-v2.png");--mayhen-g4-paper-countdown-bottom-overlay:url("{{ $assetBase }}/paper-countdown-bottom-overlay-v2.png");--mayhen-g4-paper-corner-overlay:url("{{ $assetBase }}/paper-corner-overlay-v2.png");--mayhen-g4-paper-tear-overlay:url("{{ $assetBase }}/paper-tear-overlay-v2.png");--mayhen-g4-album-floral-overlay:url("{{ $assetBase }}/album-floral-overlay-v2.png");--mayhen-g4-paper-footer-overlay:url("{{ $assetBase }}/paper-footer-overlay-v2.png")'>
        <div class="mayhen_invitation_djcvhwiuo9w9_opening-sides" id="mayhen_invitation_djcvhwiuo9w9_opening_sides"
            aria-label="Bấm vào cánh trái hoặc cánh phải để mở thiệp">
            <div class="mayhen_invitation_djcvhwiuo9w9_card-side mayhen_invitation_djcvhwiuo9w9_opening-left"
                role="button" tabindex="0" aria-label="Chạm để mở thiệp cưới">
                <img src="{{ $assetBase }}/opening-left.webp"
                    class="mayhen_invitation_djcvhwiuo9w9_opening-img mayhen_invitation_djcvhwiuo9w9_opening-img-left"
                    alt="Cánh thiệp trái" draggable="false" />
                <div class="mayhen_invitation_djcvhwiuo9w9_seal-pulse-glow" aria-hidden="true"></div>
            </div>
            <div class="mayhen_invitation_djcvhwiuo9w9_card-side mayhen_invitation_djcvhwiuo9w9_opening-right"
                role="button" tabindex="0" aria-label="Chạm để mở thiệp cưới">
                <img src="{{ $assetBase }}/opening-right.webp"
                    class="mayhen_invitation_djcvhwiuo9w9_opening-img mayhen_invitation_djcvhwiuo9w9_opening-img-right"
                    alt="Cánh thiệp phải" draggable="false" />
            </div>
        </div>
        <div class="mayhen_invitation_djcvhwiuo9w9_card-wrapper" data-mayhen-section-container>
            <section data-mayhen-section="couple"
                class="mayhen_invitation_djcvhwiuo9w9_card-section mayhen_invitation_djcvhwiuo9w9_hero-section"
                id="mayhen_invitation_djcvhwiuo9w9_home"
                style='background-image:url("{{ $heroPhotoUrl }}");background-position:46% 34%'>
                <div class="mayhen_invitation_djcvhwiuo9w9_hero-overlay"></div>
                <div class="mayhen_invitation_djcvhwiuo9w9_card-banner-inner">
                    <div>
                        <h1
                            class="mayhen_invitation_djcvhwiuo9w9_wedding-text mayhen_invitation_djcvhwiuo9w9_font1 mayhen_invitation_djcvhwiuo9w9_animate-in">
                            We're getting married</h1>
                    </div>
                    <div
                        class="mayhen_invitation_djcvhwiuo9w9_wedding-name-wrapper mayhen_invitation_djcvhwiuo9w9_animate-up">
                        <p class="mayhen_invitation_djcvhwiuo9w9_hero-tagline">Một tình yêu được khắc trên tim</p>
                        <div class="mayhen_invitation_djcvhwiuo9w9_wedding-name" data-fit-text-group="hero-couple">
                            <span data-sync-target="groom_name">{{ $groomName }}</span><span>&amp;</span><span
                                data-sync-target="bride_name">{{ $brideName }}</span></div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_wedding-date">
                            {{ $brideCeremonyDayNum }}.{{ $brideCeremonyMonthNum }}.{{ $brideCeremonyYearNum }}</div>
                    </div>
                </div>
            </section>
            <section data-mayhen-section="countdown"
                class="mayhen_invitation_djcvhwiuo9w9_card-section mayhen_invitation_djcvhwiuo9w9_countdown-section mayhen_invitation_djcvhwiuo9w9_card-countdown"
                id="mayhen_invitation_djcvhwiuo9w9_countdown">
                <div class="mayhen_invitation_djcvhwiuo9w9_card-countdown-inner">
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_logo"
                        data-reveal="soft-scale"
                        style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1750ms">
                        <div aria-label="Logo tên viết tắt DP" class="mayhen_invitation_djcvhwiuo9w9_monogram-text">
                            <span aria-hidden="true"
                                class="mayhen_invitation_djcvhwiuo9w9_monogram-initial mayhen_invitation_djcvhwiuo9w9_monogram-groom"
                                data-monogram-role="groom">D</span><span aria-hidden="true"
                                class="mayhen_invitation_djcvhwiuo9w9_monogram-initial mayhen_invitation_djcvhwiuo9w9_monogram-bride"
                                data-monogram-role="bride">P</span></div>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                        data-reveal="fade-up"
                        style="--mayhen-g4-reveal-delay:90ms;--mayhen-g4-reveal-duration:1650ms">
                        <h3 class="mayhen_invitation_djcvhwiuo9w9_whitespace-pre-line">We will become
                            Husband and wife in</h3>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                        data-reveal="fade-up"
                        style="--mayhen-g4-reveal-delay:190ms;--mayhen-g4-reveal-duration:1700ms">
                        <div aria-label="Đếm ngược đến ngày cưới"
                            class="mayhen_invitation_djcvhwiuo9w9_countdown-inner mayhen_invitation_djcvhwiuo9w9_font2">
                            <div class="mayhen_invitation_djcvhwiuo9w9_countdown-piece">
                                <div class="mayhen_invitation_djcvhwiuo9w9_box">
                                    <div class="mayhen_invitation_djcvhwiuo9w9_value">0</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_countdown-label">Ngày</div>
                                </div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_countdown-piece">
                                <div class="mayhen_invitation_djcvhwiuo9w9_colon">:</div>
                                <div class="mayhen_invitation_djcvhwiuo9w9_box">
                                    <div class="mayhen_invitation_djcvhwiuo9w9_value">0</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_countdown-label">Giờ</div>
                                </div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_countdown-piece">
                                <div class="mayhen_invitation_djcvhwiuo9w9_colon">:</div>
                                <div class="mayhen_invitation_djcvhwiuo9w9_box">
                                    <div class="mayhen_invitation_djcvhwiuo9w9_value">0</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_countdown-label">Phút</div>
                                </div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_countdown-piece">
                                <div class="mayhen_invitation_djcvhwiuo9w9_colon">:</div>
                                <div class="mayhen_invitation_djcvhwiuo9w9_box">
                                    <div class="mayhen_invitation_djcvhwiuo9w9_value">0</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_countdown-label">Giây</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-rotate-right mayhen_invitation_djcvhwiuo9w9_envelope-wrap"
                        data-reveal="rotate-right"
                        style="--mayhen-g4-reveal-delay:120ms;--mayhen-g4-reveal-duration:2000ms">
                        <div class="mayhen_invitation_djcvhwiuo9w9_envelope-photo"><img alt=""
                                class="mayhen_invitation_djcvhwiuo9w9_envelope-back"
                                src="{{ $assetBase }}/envelope-back-custom.png" />
                            <div
                                class="mayhen_invitation_djcvhwiuo9w9_envelope-picture mayhen_invitation_djcvhwiuo9w9_picture-one">
                                <img alt="Khoảnh khắc của cặp đôi" loading="lazy" src="{{ $triptych2 }}"
                                    style="object-position:29% 68%" /></div>
                            <div
                                class="mayhen_invitation_djcvhwiuo9w9_envelope-picture mayhen_invitation_djcvhwiuo9w9_picture-two">
                                <img alt="Khoảnh khắc của cặp đôi" loading="lazy" src="{{ $triptych3 }}"
                                    style="object-position:50% 50%" /></div><img alt=""
                                class="mayhen_invitation_djcvhwiuo9w9_envelope-front"
                                src="{{ $assetBase }}/envelope-front-custom.png" />
                        </div>
                    </div>
                </div>
            </section>
            <section
                class="mayhen_invitation_djcvhwiuo9w9_card-section mayhen_invitation_djcvhwiuo9w9_save-date-section"
                id="mayhen_invitation_djcvhwiuo9w9_save-date"
                style='background-image:url("{{ $triptych1 }}");background-position:52% 46%'>
                <div class="mayhen_invitation_djcvhwiuo9w9_save-date-shade"></div>
                <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-from-left mayhen_invitation_djcvhwiuo9w9_save-date-heading"
                    data-reveal="from-left" style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1850ms">
                    <h2 class="mayhen_invitation_djcvhwiuo9w9_font2">Save the Date</h2>
                </div>
            </section>
            <section data-mayhen-section="bride_events"
                class="mayhen_invitation_djcvhwiuo9w9_card-section mayhen_invitation_djcvhwiuo9w9_info-section mayhen_invitation_djcvhwiuo9w9_card-info"
                id="mayhen_invitation_djcvhwiuo9w9_invitation">
                <div class="mayhen_invitation_djcvhwiuo9w9_card-info-inner">
                    <div class="mayhen_invitation_djcvhwiuo9w9_parents" data-fit-text-group="parents">
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-from-left mayhen_invitation_djcvhwiuo9w9_parent"
                            data-reveal="from-left"
                            style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1800ms">
                            <div class="mayhen_invitation_djcvhwiuo9w9_label">Nhà trai</div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_parent-name" data-fit-text="true"><span
                                    class="mayhen_invitation_djcvhwiuo9w9_name-title">Ông<!-- -->: </span><span
                                    data-sync-target="groom_father">{{ $groomFather }}</span></div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_parent-name" data-fit-text="true"><span
                                    class="mayhen_invitation_djcvhwiuo9w9_name-title">Bà<!-- -->: </span><span
                                    data-sync-target="groom_mother">{{ $groomMother }}</span></div>
                        </div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_v-divider"></div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-from-right mayhen_invitation_djcvhwiuo9w9_parent"
                            data-reveal="from-right"
                            style="--mayhen-g4-reveal-delay:130ms;--mayhen-g4-reveal-duration:1800ms">
                            <div class="mayhen_invitation_djcvhwiuo9w9_label">Nhà gái</div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_parent-name" data-fit-text="true"><span
                                    class="mayhen_invitation_djcvhwiuo9w9_name-title">Ông<!-- -->: </span><span
                                    data-sync-target="bride_father">{{ $brideFather }}</span></div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_parent-name" data-fit-text="true"><span
                                    class="mayhen_invitation_djcvhwiuo9w9_name-title">Bà<!-- -->: </span><span
                                    data-sync-target="bride_mother">{{ $brideMother }}</span></div>
                        </div>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_side-switch-wrap"
                        data-reveal="soft-scale"
                        style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1650ms">
                        <div class="mayhen_invitation_djcvhwiuo9w9_side-switch"><button
                                class="mayhen_invitation_djcvhwiuo9w9_active" type="button">Nhà gái</button><button
                                class="" type="button">Nhà trai</button></div>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_invitation-event-block" data-event-style="standard">
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up mayhen_invitation_djcvhwiuo9w9_text mayhen_invitation_djcvhwiuo9w9_event-title"
                            data-reveal="fade-up"
                            style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1650ms">Lễ Vu Quy</div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_date-and-time">
                            <div class="mayhen_invitation_djcvhwiuo9w9_date-and-time-inner">
                                <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_time"
                                    data-reveal="soft-scale"
                                    style="--mayhen-g4-reveal-delay:80ms;--mayhen-g4-reveal-duration:1750ms">
                                    {{ $brideCeremonyTime }} - {{ $brideCeremonyDayName }}</div>
                                <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_date"
                                    data-reveal="soft-scale"
                                    style="--mayhen-g4-reveal-delay:160ms;--mayhen-g4-reveal-duration:1750ms">
                                    {{ $brideCeremonyDayNum }}.{{ $brideCeremonyMonthNum }}.{{ $brideCeremonyYearNum }}
                                </div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up mayhen_invitation_djcvhwiuo9w9_lunar-date"
                                data-reveal="fade-up"
                                style="--mayhen-g4-reveal-delay:220ms;--mayhen-g4-reveal-duration:1650ms">
                                {{ $brideCeremonyLunar }}</div>
                        </div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up mayhen_invitation_djcvhwiuo9w9_text mayhen_invitation_djcvhwiuo9w9_venue-label"
                            data-reveal="fade-up"
                            style="--mayhen-g4-reveal-delay:120ms;--mayhen-g4-reveal-duration:1650ms">Địa điểm<!-- -->:
                        </div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up mayhen_invitation_djcvhwiuo9w9_hotel"
                            data-reveal="fade-up"
                            style="--mayhen-g4-reveal-delay:190ms;--mayhen-g4-reveal-duration:1700ms">
                            {{ $brideCeremonyAddress }}</div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                            data-reveal="fade-up"
                            style="--mayhen-g4-reveal-delay:320ms;--mayhen-g4-reveal-duration:1650ms"><a
                                class="mayhen_invitation_djcvhwiuo9w9_location-button" href="{{ $brideMapLink }}"
                                rel="noreferrer" target="_blank"><svg aria-hidden="true"
                                    class="mayhen_invitation_djcvhwiuo9w9_lucide mayhen_invitation_djcvhwiuo9w9_lucide-map-pin"
                                    fill="currentColor" height="16" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="16"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                    </path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg> <!-- -->Chỉ đường</a></div>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_invitation-event-block" data-event-style="standard">
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up mayhen_invitation_djcvhwiuo9w9_text mayhen_invitation_djcvhwiuo9w9_event-title"
                            data-reveal="fade-up"
                            style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1650ms">Tiệc Cưới</div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_date-and-time">
                            <div class="mayhen_invitation_djcvhwiuo9w9_date-and-time-inner">
                                <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_time"
                                    data-reveal="soft-scale"
                                    style="--mayhen-g4-reveal-delay:80ms;--mayhen-g4-reveal-duration:1750ms">
                                    {{ $groomPartyTime }} - {{ $groomPartyDayName }}</div>
                                <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_date"
                                    data-reveal="soft-scale"
                                    style="--mayhen-g4-reveal-delay:160ms;--mayhen-g4-reveal-duration:1750ms">
                                    {{ $groomPartyDayNum }}.{{ $groomPartyMonthNum }}.{{ $groomPartyYearNum }}</div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up mayhen_invitation_djcvhwiuo9w9_lunar-date"
                                data-reveal="fade-up"
                                style="--mayhen-g4-reveal-delay:220ms;--mayhen-g4-reveal-duration:1650ms">
                                {{ $groomPartyLunar }}</div>
                        </div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up mayhen_invitation_djcvhwiuo9w9_text mayhen_invitation_djcvhwiuo9w9_venue-label"
                            data-reveal="fade-up"
                            style="--mayhen-g4-reveal-delay:120ms;--mayhen-g4-reveal-duration:1650ms">Địa điểm<!-- -->:
                        </div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up mayhen_invitation_djcvhwiuo9w9_hotel"
                            data-reveal="fade-up"
                            style="--mayhen-g4-reveal-delay:190ms;--mayhen-g4-reveal-duration:1700ms">
                            {{ $brideCeremonyAddress }}</div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                            data-reveal="fade-up"
                            style="--mayhen-g4-reveal-delay:320ms;--mayhen-g4-reveal-duration:1650ms"><a
                                class="mayhen_invitation_djcvhwiuo9w9_location-button"
                                href="https://www.google.com/maps/dir/?api=1&amp;destination=20.828461010035973,105.9531193971634"
                                rel="noreferrer" target="_blank"><svg aria-hidden="true"
                                    class="mayhen_invitation_djcvhwiuo9w9_lucide mayhen_invitation_djcvhwiuo9w9_lucide-map-pin"
                                    fill="currentColor" height="16" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="16"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                    </path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg> <!-- -->Chỉ đường</a></div>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up mayhen_invitation_djcvhwiuo9w9_text mayhen_invitation_djcvhwiuo9w9_invitation-copy"
                        data-reveal="fade-up" style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1700ms">
                        <div>Thân mời</div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_invitation-guest-name mayhen_invitation_djcvhwiuo9w9_font1"
                            data-fit-text-group="invitation-guest">Quý vị &amp; Gia đình</div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_whitespace-pre-line">đến dự tiệc mừng lễ hạnh phúc
                            của chúng tôi</div>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale"
                        data-reveal="soft-scale"
                        style="--mayhen-g4-reveal-delay:120ms;--mayhen-g4-reveal-duration:1850ms">
                        <div class="mayhen_invitation_djcvhwiuo9w9_names" data-fit-text-group="invitation-couple">
                            <div class="mayhen_invitation_djcvhwiuo9w9_name mayhen_invitation_djcvhwiuo9w9_font2"
                                data-fit-text="true">{{ $groomName }}</div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_and mayhen_invitation_djcvhwiuo9w9_font1">and
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_name mayhen_invitation_djcvhwiuo9w9_font2"
                                data-fit-text="true">{{ $brideName }}</div>
                        </div>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up mayhen_invitation_djcvhwiuo9w9_dress-code-block"
                        data-reveal="fade-up"
                        style="--mayhen-g4-reveal-delay:180ms;--mayhen-g4-reveal-duration:1700ms">
                        <div class="mayhen_invitation_djcvhwiuo9w9_venue-label">Dress code</div>
                        <div aria-label="Bảng màu trang phục gợi ý"
                            class="mayhen_invitation_djcvhwiuo9w9_flex mayhen_invitation_djcvhwiuo9w9_flex-wrap mayhen_invitation_djcvhwiuo9w9_gap-2 mayhen_invitation_djcvhwiuo9w9_justify-center mayhen_invitation_djcvhwiuo9w9_dress-code-swatches"
                            role="list"><span aria-label="Màu 1: #FFFFFF"
                                class="mayhen_invitation_djcvhwiuo9w9_block mayhen_invitation_djcvhwiuo9w9_h-9 mayhen_invitation_djcvhwiuo9w9_w-9 mayhen_invitation_djcvhwiuo9w9_shrink-0 mayhen_invitation_djcvhwiuo9w9_rounded-full mayhen_invitation_djcvhwiuo9w9_border"
                                role="listitem"
                                style="background-color:#FFFFFF;border-color:rgba(24, 24, 27, 0.28);box-shadow:0 1px 4px rgba(15, 23, 42, 0.12)"
                                title="#FFFFFF"></span><span aria-label="Màu 2: #D9C7B8"
                                class="mayhen_invitation_djcvhwiuo9w9_block mayhen_invitation_djcvhwiuo9w9_h-9 mayhen_invitation_djcvhwiuo9w9_w-9 mayhen_invitation_djcvhwiuo9w9_shrink-0 mayhen_invitation_djcvhwiuo9w9_rounded-full mayhen_invitation_djcvhwiuo9w9_border"
                                role="listitem"
                                style="background-color:#D9C7B8;border-color:rgba(255, 255, 255, 0.42);box-shadow:0 1px 4px rgba(15, 23, 42, 0.12)"
                                title="#D9C7B8"></span><span aria-label="Màu 3: #8F1D2C"
                                class="mayhen_invitation_djcvhwiuo9w9_block mayhen_invitation_djcvhwiuo9w9_h-9 mayhen_invitation_djcvhwiuo9w9_w-9 mayhen_invitation_djcvhwiuo9w9_shrink-0 mayhen_invitation_djcvhwiuo9w9_rounded-full mayhen_invitation_djcvhwiuo9w9_border"
                                role="listitem"
                                style="background-color:#8F1D2C;border-color:rgba(255, 255, 255, 0.42);box-shadow:0 1px 4px rgba(15, 23, 42, 0.12)"
                                title="#8F1D2C"></span></div>
                    </div>
                </div>
            </section>
            <section data-mayhen-section="story_and_timeline"
                class="mayhen_invitation_djcvhwiuo9w9_card-section mayhen_invitation_djcvhwiuo9w9_love-story-section mayhen_invitation_djcvhwiuo9w9_card-love-story"
                id="mayhen_invitation_djcvhwiuo9w9_story">
                <div class="mayhen_invitation_djcvhwiuo9w9_card-love-story-inner">
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                        data-reveal="fade-up" style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1700ms">
                        <h2>The story of love</h2>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_groom-bride" data-fit-text-group="couple-profile">
                        <div class="mayhen_invitation_djcvhwiuo9w9_person-row">
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-rotate-left mayhen_invitation_djcvhwiuo9w9_person-photo-reveal"
                                data-reveal="rotate-left"
                                style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:2000ms">
                                <div class="mayhen_invitation_djcvhwiuo9w9_person-photo"><img
                                        alt="Ảnh Chú rể {{ $groomName }}" loading="lazy"
                                        src="{{ $assetBase }}/7f029b2b-7c7d-4a44-949d-ca0f9ca46ca9.jpg"
                                        style="object-position:50% 50%" /></div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-from-right mayhen_invitation_djcvhwiuo9w9_person-info"
                                data-reveal="from-right"
                                style="--mayhen-g4-reveal-delay:150ms;--mayhen-g4-reveal-duration:1850ms">
                                <div class="mayhen_invitation_djcvhwiuo9w9_person-info-inner">
                                    <div
                                        class="mayhen_invitation_djcvhwiuo9w9_label mayhen_invitation_djcvhwiuo9w9_font1">
                                        Chú rể</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_name" data-fit-text="true">
                                        {{ $groomName }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_person-row mayhen_invitation_djcvhwiuo9w9_reverse">
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-from-left mayhen_invitation_djcvhwiuo9w9_person-info"
                                data-reveal="from-left"
                                style="--mayhen-g4-reveal-delay:130ms;--mayhen-g4-reveal-duration:1850ms">
                                <div class="mayhen_invitation_djcvhwiuo9w9_person-info-inner">
                                    <div
                                        class="mayhen_invitation_djcvhwiuo9w9_label mayhen_invitation_djcvhwiuo9w9_font1">
                                        Cô dâu</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_name" data-fit-text="true">
                                        {{ $brideName }}</div>
                                </div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-rotate-right mayhen_invitation_djcvhwiuo9w9_person-photo-reveal"
                                data-reveal="rotate-right"
                                style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:2000ms">
                                <div class="mayhen_invitation_djcvhwiuo9w9_person-photo"><img
                                        alt="Ảnh Cô dâu {{ $brideName }}" loading="lazy"
                                        src="{{ $assetBase }}/7f82ce42-b36d-4124-b34a-b91777ec3271.jpg"
                                        style="object-position:50% 50%" /></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section data-mayhen-section="gallery_and_photos"
                class="mayhen_invitation_djcvhwiuo9w9_card-section mayhen_invitation_djcvhwiuo9w9_gallery-section mayhen_invitation_djcvhwiuo9w9_card-gallery"
                id="mayhen_invitation_djcvhwiuo9w9_album">
                <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-film-roll mayhen_invitation_djcvhwiuo9w9_film-frame"
                    data-reveal="film-roll" style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:2200ms">
                    <img alt="" class="mayhen_invitation_djcvhwiuo9w9_film-overlay"
                        src="{{ $assetBase }}/film.png" />
                    <div class="mayhen_invitation_djcvhwiuo9w9_film-images" data-film-track="true">
                        <div class="mayhen_invitation_djcvhwiuo9w9_film-sequence" data-film-sequence="original">
                            <div><img alt="Khoảnh khắc 1" loading="lazy"
                                    src="{{ $assetBase }}/17561028-8353-443f-9499-3cacf8e00f39.jpg"
                                    style="object-position:50% 50%" /></div>
                            <div><img alt="Khoảnh khắc 2" loading="lazy"
                                    src="{{ $assetBase }}/3d8c97cc-9a05-42c1-916e-c76f3e635f19.jpg"
                                    style="object-position:50% 50%" /></div>
                            <div><img alt="Khoảnh khắc 3" loading="lazy"
                                    src="{{ $assetBase }}/5bf6d6e7-c1f7-4b0b-bf30-011dc81a7191.jpg"
                                    style="object-position:50% 50%" /></div>
                            <div><img alt="Khoảnh khắc 4" loading="lazy"
                                    src="{{ $assetBase }}/58d06c3d-d76a-437b-8661-7058afa6798a.jpg"
                                    style="object-position:41% 52%" /></div>
                            <div><img alt="Khoảnh khắc 5" loading="lazy"
                                    src="{{ $assetBase }}/42c5230a-1ea7-4043-906f-545987e51dee.jpg"
                                    style="object-position:50% 50%" /></div>
                        </div>
                        <div aria-hidden="true" class="mayhen_invitation_djcvhwiuo9w9_film-sequence"
                            data-film-sequence="duplicate">
                            <div><img alt="" loading="lazy"
                                    src="{{ $assetBase }}/17561028-8353-443f-9499-3cacf8e00f39.jpg"
                                    style="object-position:50% 50%" /></div>
                            <div><img alt="" loading="lazy"
                                    src="{{ $assetBase }}/3d8c97cc-9a05-42c1-916e-c76f3e635f19.jpg"
                                    style="object-position:50% 50%" /></div>
                            <div><img alt="" loading="lazy"
                                    src="{{ $assetBase }}/5bf6d6e7-c1f7-4b0b-bf30-011dc81a7191.jpg"
                                    style="object-position:50% 50%" /></div>
                            <div><img alt="" loading="lazy"
                                    src="{{ $assetBase }}/58d06c3d-d76a-437b-8661-7058afa6798a.jpg"
                                    style="object-position:41% 52%" /></div>
                            <div><img alt="" loading="lazy"
                                    src="{{ $assetBase }}/42c5230a-1ea7-4043-906f-545987e51dee.jpg"
                                    style="object-position:50% 50%" /></div>
                        </div>
                    </div>
                </div>
                <div class="mayhen_invitation_djcvhwiuo9w9_gallery-header">
                    <div
                        class="mayhen_invitation_djcvhwiuo9w9_gallery-person mayhen_invitation_djcvhwiuo9w9_gallery-person-left">
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-from-left mayhen_invitation_djcvhwiuo9w9_gallery-info"
                            data-reveal="from-left"
                            style="--mayhen-g4-reveal-delay:100ms;--mayhen-g4-reveal-duration:1850ms">
                            <div class="mayhen_invitation_djcvhwiuo9w9_label mayhen_invitation_djcvhwiuo9w9_font1">The
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_name">Album</div>
                        </div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-rotate-left mayhen_invitation_djcvhwiuo9w9_gallery-image-reveal"
                            data-reveal="rotate-left"
                            style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:2000ms">
                            <div
                                class="mayhen_invitation_djcvhwiuo9w9_gallery-image mayhen_invitation_djcvhwiuo9w9_tilted-right">
                                <div class="mayhen_invitation_djcvhwiuo9w9_gallery-image-crop"><img
                                        alt="Album của đôi uyên ương" loading="lazy"
                                        src="{{ $assetBase }}/b0406e5d-ad0e-4074-87cc-3cfb05978f06.jpg"
                                        style="object-position:50% 50%" /></div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="mayhen_invitation_djcvhwiuo9w9_gallery-person mayhen_invitation_djcvhwiuo9w9_gallery-person-right">
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-rotate-right mayhen_invitation_djcvhwiuo9w9_gallery-image-reveal"
                            data-reveal="rotate-right"
                            style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:2000ms">
                            <div
                                class="mayhen_invitation_djcvhwiuo9w9_gallery-image mayhen_invitation_djcvhwiuo9w9_tilted-left">
                                <div class="mayhen_invitation_djcvhwiuo9w9_gallery-image-crop"><img
                                        alt="Tình yêu của đôi uyên ương" loading="lazy"
                                        src="{{ $assetBase }}/96c5fed3-ce54-4fd7-83e7-2e7e5019190a.jpg"
                                        style="object-position:38% 44%" /></div>
                            </div>
                        </div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-from-right mayhen_invitation_djcvhwiuo9w9_gallery-info"
                            data-reveal="from-right"
                            style="--mayhen-g4-reveal-delay:120ms;--mayhen-g4-reveal-duration:1850ms">
                            <div class="mayhen_invitation_djcvhwiuo9w9_name">Of<br />Love</div>
                        </div>
                    </div>
                </div>
                <div class="mayhen_invitation_djcvhwiuo9w9_gallery-header-background"></div>
                <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-stagger mayhen_invitation_djcvhwiuo9w9_photo-grid"
                    data-reveal="stagger" style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1750ms">
                    <button aria-label="Xem ảnh 1 trong album"
                        class="mayhen_invitation_djcvhwiuo9w9_photo mayhen_invitation_djcvhwiuo9w9_photo-1"
                        style="--mayhen-g4-reveal-item-delay:0ms" type="button"><img
                            alt="Khoảnh khắc 1 của đôi uyên ương" loading="lazy" src="{{ $albumPhotos[0] }}"
                            style="object-position:50% 50%" /></button><button aria-label="Xem ảnh 2 trong album"
                        class="mayhen_invitation_djcvhwiuo9w9_photo mayhen_invitation_djcvhwiuo9w9_photo-2"
                        style="--mayhen-g4-reveal-item-delay:0ms" type="button"><img
                            alt="Khoảnh khắc 2 của đôi uyên ương" loading="lazy" src="{{ $albumPhotos[1] }}"
                            style="object-position:50% 50%" /></button><button aria-label="Xem ảnh 3 trong album"
                        class="mayhen_invitation_djcvhwiuo9w9_photo mayhen_invitation_djcvhwiuo9w9_photo-3"
                        style="--mayhen-g4-reveal-item-delay:180ms" type="button"><img
                            alt="Khoảnh khắc 3 của đôi uyên ương" loading="lazy" src="{{ $albumPhotos[2] }}"
                            style="object-position:50% 50%" /></button><button aria-label="Xem ảnh 4 trong album"
                        class="mayhen_invitation_djcvhwiuo9w9_photo mayhen_invitation_djcvhwiuo9w9_photo-4"
                        style="--mayhen-g4-reveal-item-delay:180ms" type="button"><img
                            alt="Khoảnh khắc 4 của đôi uyên ương" loading="lazy" src="{{ $albumPhotos[3] }}"
                            style="object-position:41% 52%" /></button><button aria-label="Xem ảnh 5 trong album"
                        class="mayhen_invitation_djcvhwiuo9w9_photo mayhen_invitation_djcvhwiuo9w9_photo-5"
                        style="--mayhen-g4-reveal-item-delay:360ms" type="button"><img
                            alt="Khoảnh khắc 5 của đôi uyên ương" loading="lazy" src="{{ $albumPhotos[4] }}"
                            style="object-position:50% 50%" /></button><button aria-label="Xem ảnh 6 trong album"
                        class="mayhen_invitation_djcvhwiuo9w9_photo mayhen_invitation_djcvhwiuo9w9_photo-6"
                        style="--mayhen-g4-reveal-item-delay:360ms" type="button"><img
                            alt="Khoảnh khắc 6 của đôi uyên ương" loading="lazy" src="{{ $albumPhotos[5] }}"
                            style="object-position:50% 50%" /></button><button aria-label="Xem ảnh 7 trong album"
                        class="mayhen_invitation_djcvhwiuo9w9_photo mayhen_invitation_djcvhwiuo9w9_photo-7"
                        style="--mayhen-g4-reveal-item-delay:540ms" type="button"><img
                            alt="Khoảnh khắc 7 của đôi uyên ương" loading="lazy" src="{{ $albumPhotos[6] }}"
                            style="object-position:38% 44%" /></button><button aria-label="Xem ảnh 8 trong album"
                        class="mayhen_invitation_djcvhwiuo9w9_photo mayhen_invitation_djcvhwiuo9w9_photo-8"
                        style="--mayhen-g4-reveal-item-delay:540ms" type="button"><img
                            alt="Khoảnh khắc 8 của đôi uyên ương" loading="lazy" src="{{ $albumPhotos[7] }}"
                            style="object-position:50% 50%" /></button><button aria-label="Xem ảnh 9 trong album"
                        class="mayhen_invitation_djcvhwiuo9w9_photo mayhen_invitation_djcvhwiuo9w9_photo-9"
                        style="--mayhen-g4-reveal-item-delay:720ms" type="button"><img
                            alt="Khoảnh khắc 9 của đôi uyên ương" loading="lazy" src="{{ $albumPhotos[8] }}"
                            style="object-position:50% 50%" /></button><button aria-label="Xem ảnh 10 trong album"
                        class="mayhen_invitation_djcvhwiuo9w9_photo mayhen_invitation_djcvhwiuo9w9_photo-10"
                        style="--mayhen-g4-reveal-item-delay:720ms" type="button"><img
                            alt="Khoảnh khắc 10 của đôi uyên ương" loading="lazy" src="{{ $albumPhotos[9] }}"
                            style="object-position:42% 35%" /></button></div>
            </section>
            <section
                class="mayhen_invitation_djcvhwiuo9w9_card-section mayhen_invitation_djcvhwiuo9w9_calendar-section mayhen_invitation_djcvhwiuo9w9_card-calendar"
                id="mayhen_invitation_djcvhwiuo9w9_calendar">
                <div class="mayhen_invitation_djcvhwiuo9w9_calendar-inner">
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale"
                        data-reveal="soft-scale"
                        style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1850ms">
                        <div class="mayhen_invitation_djcvhwiuo9w9_year">2027</div>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-from-right"
                        data-reveal="from-right"
                        style="--mayhen-g4-reveal-delay:100ms;--mayhen-g4-reveal-duration:1850ms">
                        <div class="mayhen_invitation_djcvhwiuo9w9_month">March</div>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                        data-reveal="fade-up"
                        style="--mayhen-g4-reveal-delay:200ms;--mayhen-g4-reveal-duration:1800ms">
                        <div class="mayhen_invitation_djcvhwiuo9w9_mini-calendar">
                            <table>
                                <thead>
                                    <tr>
                                        <th>T2</th>
                                        <th>T3</th>
                                        <th>T4</th>
                                        <th>T5</th>
                                        <th>T6</th>
                                        <th>T7</th>
                                        <th>CN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>2</td>
                                        <td>3</td>
                                        <td>4</td>
                                        <td>5</td>
                                        <td>6</td>
                                        <td><span class="mayhen_invitation_djcvhwiuo9w9_today"
                                                data-calendar-highlight="7"><svg aria-hidden="true"
                                                    class="mayhen_invitation_djcvhwiuo9w9_lucide mayhen_invitation_djcvhwiuo9w9_lucide-heart mayhen_invitation_djcvhwiuo9w9_calendar-heart"
                                                    fill="currentColor" height="24" stroke="currentColor"
                                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    viewbox="0 0 24 24" width="24"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5">
                                                    </path>
                                                </svg><span class="mayhen_invitation_djcvhwiuo9w9_day">7</span></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>8</td>
                                        <td>9</td>
                                        <td>10</td>
                                        <td>11</td>
                                        <td>12</td>
                                        <td>13</td>
                                        <td>14</td>
                                    </tr>
                                    <tr>
                                        <td>15</td>
                                        <td>16</td>
                                        <td>17</td>
                                        <td>18</td>
                                        <td>19</td>
                                        <td>20</td>
                                        <td>21</td>
                                    </tr>
                                    <tr>
                                        <td>22</td>
                                        <td>23</td>
                                        <td>24</td>
                                        <td>25</td>
                                        <td>26</td>
                                        <td>27</td>
                                        <td>28</td>
                                    </tr>
                                    <tr>
                                        <td>29</td>
                                        <td>30</td>
                                        <td>31</td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
            <section
                class="mayhen_invitation_djcvhwiuo9w9_card-section mayhen_invitation_djcvhwiuo9w9_timeline-section"
                id="mayhen_invitation_djcvhwiuo9w9_timeline"
                style='background-image:url("{{ $assetBase }}/26d486c7-1a08-462e-982a-f5b338fefe86.jpg");background-position:50% 50%'>
                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-shade"></div>
                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-inner">
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale"
                        data-reveal="soft-scale"
                        style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1800ms">
                        <h2 class="mayhen_invitation_djcvhwiuo9w9_timeline-title">Timeline</h2>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_timeline-group">
                        <div class="mayhen_invitation_djcvhwiuo9w9_timeline-items">
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_timeline-item"
                                data-reveal="soft-scale"
                                style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1800ms">
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-icon"><img alt=""
                                        loading="lazy" src="{{ $assetBase }}/timeline-car.png"
                                        style="object-position:50% 50%" /></div>
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-content">
                                    <div class="mayhen_invitation_djcvhwiuo9w9_time">05:00</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_title">Xuất phát từ nhà trai</div>
                                </div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_timeline-item"
                                data-reveal="soft-scale"
                                style="--mayhen-g4-reveal-delay:120ms;--mayhen-g4-reveal-duration:1800ms">
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-icon"><img alt=""
                                        loading="lazy" src="{{ $assetBase }}/timeline-home.png"
                                        style="object-position:50% 50%" /></div>
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-content">
                                    <div class="mayhen_invitation_djcvhwiuo9w9_time">07:00</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_title">Đến nhà cô dâu</div>
                                </div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_timeline-item"
                                data-reveal="soft-scale"
                                style="--mayhen-g4-reveal-delay:240ms;--mayhen-g4-reveal-duration:1800ms">
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-icon"><img alt=""
                                        loading="lazy" src="{{ $assetBase }}/timeline-car-front.png"
                                        style="object-position:50% 50%" /></div>
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-content">
                                    <div class="mayhen_invitation_djcvhwiuo9w9_time">07:50</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_title">Bắt đầu về nhà trai</div>
                                </div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_timeline-item"
                                data-reveal="soft-scale"
                                style="--mayhen-g4-reveal-delay:360ms;--mayhen-g4-reveal-duration:1800ms">
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-icon"><img alt=""
                                        loading="lazy" src="{{ $assetBase }}/timeline-ceremony.png"
                                        style="object-position:50% 50%" /></div>
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-content">
                                    <div class="mayhen_invitation_djcvhwiuo9w9_time">10:00</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_title">Lễ thành hôn</div>
                                </div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_timeline-item"
                                data-reveal="soft-scale"
                                style="--mayhen-g4-reveal-delay:360ms;--mayhen-g4-reveal-duration:1800ms">
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-icon"><img alt=""
                                        loading="lazy" src="{{ $assetBase }}/timeline-guest.png"
                                        style="object-position:50% 50%" /></div>
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-content">
                                    <div class="mayhen_invitation_djcvhwiuo9w9_time">11:00</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_title">Chụp ảnh kỷ niệm</div>
                                </div>
                            </div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_timeline-item"
                                data-reveal="soft-scale"
                                style="--mayhen-g4-reveal-delay:360ms;--mayhen-g4-reveal-duration:1800ms">
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-icon"><img alt=""
                                        loading="lazy" src="{{ $assetBase }}/timeline-dinner.png"
                                        style="object-position:50% 50%" /></div>
                                <div class="mayhen_invitation_djcvhwiuo9w9_timeline-item-content">
                                    <div class="mayhen_invitation_djcvhwiuo9w9_time">11:30</div>
                                    <div class="mayhen_invitation_djcvhwiuo9w9_title">Dự tiệc</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section
                class="mayhen_invitation_djcvhwiuo9w9_card-section mayhen_invitation_djcvhwiuo9w9_comments-section mayhen_invitation_djcvhwiuo9w9_card-comments"
                id="mayhen_invitation_djcvhwiuo9w9_guestbook">
                <div class="mayhen_invitation_djcvhwiuo9w9_inner">
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                        data-reveal="fade-up" style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1700ms">
                        <h2
                            class="mayhen_invitation_djcvhwiuo9w9_card-section-title mayhen_invitation_djcvhwiuo9w9_font1">
                            Sổ lưu bút</h2>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                        data-reveal="fade-up"
                        style="--mayhen-g4-reveal-delay:110ms;--mayhen-g4-reveal-duration:1700ms">
                        <p class="mayhen_invitation_djcvhwiuo9w9_card-section-subtitle">Ký tên và gửi những lời chúc
                            tốt đẹp nhất đến cô dâu chú rể trong ngày trọng đại.</p>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_guestbook-shell"
                        data-reveal="soft-scale"
                        style="--mayhen-g4-reveal-delay:180ms;--mayhen-g4-reveal-duration:1850ms">
                        <div class="mayhen_invitation_djcvhwiuo9w9_guestbook-board-heading"><span>Những dấu yêu ở
                                lại</span><small>0 chữ ký</small></div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_signature-board"><img alt="Nền sổ lưu bút"
                                class="mayhen_invitation_djcvhwiuo9w9_signature-board-image" draggable="false"
                                src="{{ $assetBase }}/a7449983-218b-4e16-94e8-64030d2dab3d.jpg" />
                            <div class="mayhen_invitation_djcvhwiuo9w9_signature-board-wash"></div>
                            <div class="mayhen_invitation_djcvhwiuo9w9_signature-board-empty"><svg aria-hidden="true"
                                    class="mayhen_invitation_djcvhwiuo9w9_lucide mayhen_invitation_djcvhwiuo9w9_lucide-pen-line"
                                    fill="none" height="18" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="18"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13 21h8"></path>
                                    <path
                                        d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z">
                                    </path>
                                </svg> <!-- -->Chữ ký đầu tiên đang chờ bạn</div>
                        </div><button class="mayhen_invitation_djcvhwiuo9w9_guestbook-start-button"
                            type="button"><svg aria-hidden="true"
                                class="mayhen_invitation_djcvhwiuo9w9_lucide mayhen_invitation_djcvhwiuo9w9_lucide-pen-line"
                                fill="none" height="16" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="16"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M13 21h8"></path>
                                <path
                                    d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z">
                                </path>
                            </svg>Ký tên &amp; gửi lời chúc</button>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_comment-list">
                        <article class="mayhen_invitation_djcvhwiuo9w9_comment-item">
                            <div class="mayhen_invitation_djcvhwiuo9w9_comment-item-header">
                                <div class="mayhen_invitation_djcvhwiuo9w9_comment-author"><strong>Gia đình cô
                                        Ba</strong></div><time>18/08/2026</time>
                            </div>
                            <p>Chúc hai con trăm năm hạnh phúc, mãi yêu thương và đồng hành cùng nhau!</p>
                        </article>
                        <article class="mayhen_invitation_djcvhwiuo9w9_comment-item">
                            <div class="mayhen_invitation_djcvhwiuo9w9_comment-item-header">
                                <div class="mayhen_invitation_djcvhwiuo9w9_comment-author"><strong>Hội bạn
                                        thân</strong></div><time>17/08/2026</time>
                            </div>
                            <p>Chúc mừng ngày vui của hai bạn. Hạnh phúc thật nhiều nhé!</p>
                        </article>
                    </div>
                </div>
            </section>
            <section class="mayhen_invitation_djcvhwiuo9w9_card-section mayhen_invitation_djcvhwiuo9w9_rsvp-section"
                id="mayhen_invitation_djcvhwiuo9w9_rsvp">
                <div class="mayhen_invitation_djcvhwiuo9w9_rsvp-inner">
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                        data-reveal="fade-up" style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1700ms">
                        <div class="mayhen_invitation_djcvhwiuo9w9_rsvp-eyebrow">RSVP · Xác nhận tham dự</div>
                        <h2
                            class="mayhen_invitation_djcvhwiuo9w9_card-section-title mayhen_invitation_djcvhwiuo9w9_font1">
                            Trân trọng đón tiếp!</h2>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                        data-reveal="fade-up"
                        style="--mayhen-g4-reveal-delay:110ms;--mayhen-g4-reveal-duration:1700ms">
                        <p class="mayhen_invitation_djcvhwiuo9w9_card-section-subtitle">Vui lòng hồi âm trước ngày
                            {{ $brideCeremonyDayNum }}.{{ $brideCeremonyMonthNum }}.{{ $brideCeremonyYearNum }} để
                            việc đón tiếp được chuẩn bị chu đáo nhất.</p>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_rsvp-card"
                        data-reveal="soft-scale"
                        style="--mayhen-g4-reveal-delay:180ms;--mayhen-g4-reveal-duration:1850ms">
                        <div aria-label="Xác nhận tham dự" class="mayhen_invitation_djcvhwiuo9w9_rsvp-options"
                            role="group"><button class="mayhen_invitation_djcvhwiuo9w9_active" type="button"><svg
                                    aria-hidden="true"
                                    class="mayhen_invitation_djcvhwiuo9w9_lucide mayhen_invitation_djcvhwiuo9w9_lucide-check"
                                    fill="none" height="16" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="16"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg> <!-- -->Tôi sẽ đến</button><button class="" type="button"><svg
                                    aria-hidden="true"
                                    class="mayhen_invitation_djcvhwiuo9w9_lucide mayhen_invitation_djcvhwiuo9w9_lucide-x"
                                    fill="none" height="16" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="16"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18 6 6 18"></path>
                                    <path d="m6 6 12 12"></path>
                                </svg> <!-- -->Rất tiếc, tôi bận</button></div>
                        <div class="mayhen_invitation_djcvhwiuo9w9_attendance-row"><span>Số người tham dự</span>
                            <div class="mayhen_invitation_djcvhwiuo9w9_attendance-stepper"><button
                                    aria-label="Giảm số người" type="button"><svg aria-hidden="true"
                                        class="mayhen_invitation_djcvhwiuo9w9_lucide mayhen_invitation_djcvhwiuo9w9_lucide-minus"
                                        fill="none" height="14" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="14"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 12h14"></path>
                                    </svg></button><strong>1</strong><button aria-label="Tăng số người"
                                    type="button"><svg aria-hidden="true"
                                        class="mayhen_invitation_djcvhwiuo9w9_lucide mayhen_invitation_djcvhwiuo9w9_lucide-plus"
                                        fill="none" height="14" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="14"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 12h14"></path>
                                        <path d="M12 5v14"></path>
                                    </svg></button></div>
                        </div><button
                            class="mayhen_invitation_djcvhwiuo9w9_form-button mayhen_invitation_djcvhwiuo9w9_rsvp-submit"
                            type="button"> <!-- -->Gửi xác nhận</button>
                    </div>
                </div>
            </section>
            <section data-mayhen-section="gift_and_music"
                class="mayhen_invitation_djcvhwiuo9w9_card-section mayhen_invitation_djcvhwiuo9w9_gift-section mayhen_invitation_djcvhwiuo9w9_card-gift"
                id="mayhen_invitation_djcvhwiuo9w9_gift">
                <div class="mayhen_invitation_djcvhwiuo9w9_inner">
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                        data-reveal="fade-up" style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1700ms">
                        <h2
                            class="mayhen_invitation_djcvhwiuo9w9_card-section-title mayhen_invitation_djcvhwiuo9w9_font1">
                            Hộp mừng cưới</h2>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up"
                        data-reveal="fade-up"
                        style="--mayhen-g4-reveal-delay:110ms;--mayhen-g4-reveal-duration:1700ms">
                        <p class="mayhen_invitation_djcvhwiuo9w9_card-section-subtitle">Sự hiện diện của bạn là món quà
                            quý giá nhất đối với chúng tôi</p>
                    </div>
                    <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-soft-scale mayhen_invitation_djcvhwiuo9w9_present-box"
                        data-reveal="soft-scale"
                        style="--mayhen-g4-reveal-delay:180ms;--mayhen-g4-reveal-duration:1900ms"><button
                            aria-label="Mở hộp mừng cưới" type="button"><img alt="Hộp mừng cưới"
                                src="{{ $assetBase }}/gift-custom.png" /></button></div>
                </div>
            </section>
            <footer class="mayhen_invitation_djcvhwiuo9w9_app-footer"><img alt="Ảnh kết"
                    class="mayhen_invitation_djcvhwiuo9w9_footer-image" loading="lazy" src="{{ $footerPhotoUrl }}"
                    style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;object-position:50% 50%;color:transparent" />
                <div aria-hidden="true" class="mayhen_invitation_djcvhwiuo9w9_footer-overlay"></div>
                <div class="mayhen_invitation_djcvhwiuo9w9_reveal mayhen_invitation_djcvhwiuo9w9_reveal-fade-up mayhen_invitation_djcvhwiuo9w9_footer-content"
                    data-reveal="fade-up" style="--mayhen-g4-reveal-delay:0ms;--mayhen-g4-reveal-duration:1850ms">
                    <h2 class="mayhen_invitation_djcvhwiuo9w9_footer-thank-you">Thank you</h2>
                    <p class="mayhen_invitation_djcvhwiuo9w9_footer-tagline">Cảm ơn bạn đã dành tình cảm cho chúng
                        mình! Sự hiện diện của bạn là món quà quý giá nhất, khiến ngày trọng đại của chúng mình thêm
                        trọn vẹn và đáng nhớ.</p>
                    <p class="mayhen_invitation_djcvhwiuo9w9_footer-names">{{ $groomName }}<!-- --> &amp;
                        <!-- -->{{ $brideName }}<!-- --> ·
                        {{ $brideCeremonyDayNum }}.{{ $brideCeremonyMonthNum }}.{{ $brideCeremonyYearNum }}</p>
                </div>
            </footer>
        </div>
    </main>
    <div class="mayhen_invitation_djcvhwiuo9w9_modal" id="mayhen_invitation_djcvhwiuo9w9_modal" aria-hidden="true">
        <div class="mayhen_invitation_djcvhwiuo9w9_modal-backdrop" data-modal-close></div>
        <div class="mayhen_invitation_djcvhwiuo9w9_modal-panel" role="dialog" aria-modal="true"
            aria-labelledby="mayhen_invitation_djcvhwiuo9w9_modal-title" tabindex="-1">
            <button class="mayhen_invitation_djcvhwiuo9w9_modal-close" type="button" data-modal-close
                aria-label="Đóng cửa sổ">×</button>
            <div class="mayhen_invitation_djcvhwiuo9w9_modal-body" id="mayhen_invitation_djcvhwiuo9w9_modal-body">
            </div>
        </div>
    </div>
    <div class="mayhen_invitation_djcvhwiuo9w9_lightbox" id="mayhen_invitation_djcvhwiuo9w9_lightbox"
        aria-hidden="true" role="dialog" aria-modal="true" aria-label="Xem album ảnh cưới">
        <button class="mayhen_invitation_djcvhwiuo9w9_lightbox-close" type="button"
            aria-label="Đóng album">×</button>
        <button class="mayhen_invitation_djcvhwiuo9w9_lightbox-prev" type="button" aria-label="Ảnh trước">‹</button>
        <img class="mayhen_invitation_djcvhwiuo9w9_lightbox-image" alt="Ảnh cưới phóng lớn" />
        <button class="mayhen_invitation_djcvhwiuo9w9_lightbox-next" type="button"
            aria-label="Ảnh tiếp theo">›</button>
        <div class="mayhen_invitation_djcvhwiuo9w9_lightbox-counter" aria-live="polite"></div>
    </div>
    <div class="mayhen_invitation_djcvhwiuo9w9_toast" id="mayhen_invitation_djcvhwiuo9w9_toast" role="status"
        aria-live="polite"></div>


    <!-- Trình phát nhạc nền Mây Hẹn -->
    <button class="mayhen_invitation_djcvhwiuo9w9_music-toggle" type="button" aria-label="Bật nhạc nền"
        aria-pressed="false" title="Bật hoặc tắt nhạc nền">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
            <path d="M9 18V5L21 3V16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                stroke-linejoin="round" />
            <circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="1.6" />
            <circle cx="18" cy="16" r="3" stroke="currentColor" stroke-width="1.6" />
        </svg>
    </button>
    <audio id="mayhen_invitation_djcvhwiuo9w9_audio" src="{{ $musicUrl }}" loop preload="auto"></audio>
    <script>
        window.__MAYHEN_IS_PREVIEW = {{ $isPreview ? 'true' : 'false' }};
        window.__MAYHEN_WEDDING_TARGET_TIME = {{ $targetTimestamp }};
        window.__MAYHEN_GIFT_QR_GROOM = "{{ $qrGroom }}";
        window.__MAYHEN_GIFT_QR_BRIDE = "{{ $qrBride }}";
        window.__MAYHEN_GROOM_ACCOUNT = {!! json_encode($groomBankInfo) !!};
        window.__MAYHEN_BRIDE_ACCOUNT = {!! json_encode($brideBankInfo) !!};
        window.__MAYHEN_WISH_SUBMIT_URL = "{{ $wishSubmitUrl }}";
        window.__MAYHEN_RSVP_SUBMIT_URL = "{{ $rsvpSubmitUrl }}";
        window.__MAYHEN_CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
    <script src="{{ $assetBase }}/script.js?v={{ $jsVersion }}"></script>

    @if (($isPreview ?? false) && !($isEmbed ?? false))
        @include('client.components.bottom-nav')
    @endif

    {{-- Bộ kết nối chuẩn hóa giữa Studio V2 và Template --}}
    @include('client.invitations.partials.editor-bridge')
</body>

</html>
