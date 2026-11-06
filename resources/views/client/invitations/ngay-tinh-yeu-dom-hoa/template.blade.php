@extends('client.layouts.invitation')

@php
    // =========================================================================
    // TEMPLATE: Ngày Tình Yêu Đơm Hoa (ngay-tinh-yeu-dom-hoa)
    // Slug chuẩn: ngay-tinh-yeu-dom-hoa
    // Phong cách: Quiet Luxury — Đỏ Son — Thư Pháp — Cánh cửa đôi Song Hỷ
    // =========================================================================

    $slug        = 'ngay-tinh-yeu-dom-hoa';
    $assetBase   = url("/mau-thiep/{$slug}/assets");
    $isEmbed     = $isEmbed ?? request()->boolean('embed')
                            || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);
    $isPreview   = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Ngày Tình Yêu Đơm Hoa';

    // --------------------------
    // 1. HÀM RESOLVE ASSET
    // --------------------------
    $resolveAsset = function (string $relativePath, ?string $overrideUrl = null) use ($slug): string {
        $override = trim((string) ($overrideUrl ?? ''));
        if (str_starts_with($override, 'blob:')) {
            $override = '';
        }
        if (!empty($override)) {
            if (str_starts_with($override, 'http://') || str_starts_with($override, 'https://') || str_starts_with($override, '/')) {
                return $override;
            }
        }
        return route('templates.asset', ['slug' => $slug, 'path' => ltrim($relativePath, '/')]);
    };

    // --------------------------
    // 2. DỮ LIỆU CÔ DÂU & CHÚ RỂ
    // --------------------------
    $extraData   = $wedding->extra_data ?? [];
    $groomName   = $wedding->groom_name ?? ($extraData['groom_name'] ?? 'Đình Sang');
    $brideName   = $wedding->bride_name ?? ($extraData['bride_name'] ?? 'Hoài Thương');
    $coupleNames = $extraData['couple_names'] ?? ($groomName . ' - ' . $brideName);

    $groomFather = $extraData['groom_father'] ?? ($wedding->groom_parents ?? 'Nguyễn Đắc Cường');
    $groomMother = $extraData['groom_mother'] ?? 'Đặng Thị Hương';
    $brideFather = $extraData['bride_father'] ?? ($wedding->bride_parents ?? 'Nguyễn Đức Trí');
    $brideMother = $extraData['bride_mother'] ?? 'Bùi Thị Tầm';

    $groomBio    = $extraData['groom_bio'] ?? 'Biết ơn Em về tất cả. Nguyện bên Em qua muôn nẻo đường phía trước, Mình đồng hành cùng nhau đến mãi mãi về sau.';
    $brideBio    = $extraData['bride_bio'] ?? 'Hành trình trưởng thành của em thật đẹp khi có sự xuất hiện của Anh. Cùng nhau chúng ta khám phá thế giới kì diệu này anh nhé';
    $loveQuote   = $extraData['love_quote'] ?? '"Hôn nhân là chuyện cả đời, Yêu người vừa ý, cưới người mình thương"';

    // --------------------------
    // 3. NGÀY GIỜ CƯỚI
    // --------------------------
    $weddingDate        = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2026, 11, 22, 13, 0, 0);

    $weddingDay         = $weddingDate->format('d');
    $weddingMonth       = $weddingDate->format('m');
    $weddingYear        = $weddingDate->format('Y');
    $weddingDateDigits  = $weddingDate->format('d.m.Y');
    $weddingWeekday     = mb_strtoupper($weddingDate->locale('vi')->isoFormat('dddd'));
    $weddingLunarDate   = $extraData['wedding_lunar_date'] ?? '(Tức ngày 14 tháng 10 năm Bính Ngọ)';
    $calendarMonthYear  = $extraData['calendar_month_year'] ?? "Tháng {$weddingMonth}/{$weddingYear}";

    // Lễ Ăn Hỏi — ngày trước ngày cưới chính 1 ngày làm mặc định
    $engagementDateDefault = $weddingDate->copy()->subDay();
    $engagementDay         = $extraData['engagement_day'] ?? $engagementDateDefault->format('d');
    $engagementWeekday     = $extraData['engagement_weekday'] ?? mb_strtoupper($engagementDateDefault->locale('vi')->isoFormat('dddd'));
    $engagementMonthYear   = $extraData['engagement_month_year'] ?? $weddingMonth . ' - ' . $weddingYear;
    $engagementTime        = $extraData['engagement_time'] ?? 'Vào Lúc 08:00';
    $engagementVenue       = $extraData['engagement_venue'] ?? 'TƯ GIA NHÀ GÁI';
    $engagementLunarDate   = $extraData['engagement_lunar_date'] ?? '(Tức ngày 13 tháng 10 năm Bính Ngọ)';

    // Lễ Thành Hôn
    $ceremonyTime  = $extraData['ceremony_time'] ?? 'Vào Lúc 13:00';
    $ceremonyVenue = $extraData['ceremony_venue'] ?? 'TƯ GIA NHÀ TRAI';

    // --------------------------
    // 4. TIỆC CƯỚI
    // --------------------------
    $bridePartyTime    = $extraData['bride_party_time'] ?? 'Vào Lúc 16:30';
    $bridePartyVenue   = $extraData['bride_party_venue'] ?? 'NHÀ VĂN HÓA TỔ 5 QUANG LÃM';
    $bridePartyAddress = $extraData['bride_party_address'] ?? 'Phường Phú Lương, TP. Hà Nội';
    $bridePartyMapUrl  = $extraData['bride_party_map_url'] ?? 'https://maps.google.com';

    $partyTime         = $extraData['party_time'] ?? 'Vào Lúc 17:30';
    $partyVenue        = $extraData['party_venue'] ?? 'NHÀ VĂN HÓA TỔ 6 NHÂN ĐẠO';
    $eventPartyAddress = $extraData['event_party_address'] ?? ($extraData['party_address'] ?? 'TDP Nhân Sơn, P. Yên Nghĩa, TP. Hà Nội');
    $partyMapUrl       = $extraData['party_map_url'] ?? 'https://maps.google.com';

    // --------------------------
    // 5. KHÁCH MỜI
    // --------------------------
    $guestName = $invitation->guest_name ?? 'Quý Khách';

    // --------------------------
    // 6. ẢNH
    // --------------------------
    $heroPhotoUrl      = $resolveAsset('assets/images/hero-couple.webp',          $wedding->cover_photo_url ?? ($extraData['hero_photo_url'] ?? null));
    $coupleBannerUrl   = $resolveAsset('assets/images/section2-couple-banner.webp', $extraData['couple_banner_url'] ?? null);
    $triptychLeftUrl   = $resolveAsset('assets/images/triptych-left.webp',         $extraData['triptych_left_url'] ?? null);
    $triptychCenterUrl = $resolveAsset('assets/images/triptych-center.webp',       $extraData['triptych_center_url'] ?? null);
    $triptychRightUrl  = $resolveAsset('assets/images/triptych-right.webp',        $extraData['triptych_right_url'] ?? null);
    $groomPhotoUrl     = $resolveAsset('assets/images/groom-portrait.webp',        $wedding->groom_photo_url ?? ($extraData['groom_photo_url'] ?? null));
    $bridePhotoUrl     = $resolveAsset('assets/images/bride-portrait.webp',        $wedding->bride_photo_url ?? ($extraData['bride_photo_url'] ?? null));
    $footerPhotoUrl    = $resolveAsset('assets/images/footer-couple-circle.webp',  $extraData['footer_photo_url'] ?? null);

    // Album 8 ảnh cưới (key: gallery_photo_1 .. gallery_photo_8)
    $defaultGallery = [
        1 => 'assets/images/gallery-01.webp',
        2 => 'assets/images/gallery-02.webp',
        3 => 'assets/images/gallery-03.webp',
        4 => 'assets/images/gallery-04.webp',
        5 => 'assets/images/gallery-05.webp',
        6 => 'assets/images/gallery-06.webp',
        7 => 'assets/images/gallery-07.webp',
        8 => 'assets/images/gallery-08.webp',
    ];
    $galleryPhotos = [];
    for ($i = 1; $i <= 8; $i++) {
        $galleryPhotos[$i] = $resolveAsset($defaultGallery[$i], $extraData["gallery_photo_{$i}"] ?? null);
    }

    // --------------------------
    // 7. MỪNG CƯỚI & QR
    // --------------------------
    $groomBankName    = $extraData['groom_bank_name'] ?? 'Vietcombank';
    $groomBankAccount = $extraData['groom_bank_account'] ?? '0123456789';
    $qrGroomUrl       = $resolveAsset('assets/images/qr-groom.webp', $extraData['qr_groom_image'] ?? null);

    $brideBankName    = $extraData['bride_bank_name'] ?? 'Techcombank';
    $brideBankAccount = $extraData['bride_bank_account'] ?? '9876543210';
    $qrBrideUrl       = $resolveAsset('assets/images/qr-bride.webp', $extraData['qr_bride_image'] ?? null);

    // --------------------------
    // 8. NHẠC NỀN & ENDPOINTS
    // --------------------------
    $musicUrl      = $wedding->music_url ?? ($extraData['music_url'] ?? '');
    $rsvpEndpoint  = $rsvpSubmitUrl  ?? ($isPreview ? route('templates.preview.rsvp', $slug)  : route('invitation.show', $invitation->slug ?? ''));
    $wishEndpoint  = $wishSubmitUrl  ?? ($isPreview ? route('templates.preview.wish', $slug)   : route('invitation.wish.store', $invitation->slug ?? ''));

    // --------------------------
    // 9. META
    // --------------------------
    $pageTitle = $isPreview
        ? "Xem thử: \"{$templateName}\" | " . config('app.name')
        : "Thiệp cưới: {$groomName} & {$brideName} | " . config('app.name');

    $cssVersion = file_exists(resource_path("views/client/invitations/{$slug}/styles.css"))
        ? filemtime(resource_path("views/client/invitations/{$slug}/styles.css"))
        : time();
    $jsVersion = file_exists(resource_path("views/client/invitations/{$slug}/script.js"))
        ? filemtime(resource_path("views/client/invitations/{$slug}/script.js"))
        : time();
@endphp

@section('title', $pageTitle)
@section('meta_description', 'Trân trọng kính mời ' . $guestName . ' tới dự lễ cưới của ' . $groomName . ' & ' . $brideName . '.')
@section('body_class', $isEmbed ? 'mayhen_invitation_embed_mode' : '')

@push('styles')
    <link rel="stylesheet" href="{{ route('templates.asset', ['slug' => $slug, 'path' => 'styles.css']) }}?v={{ $cssVersion }}">
@endpush

@section('content')
<div class="mayhen_invitation_root {{ $isEmbed ? 'mayhen_invitation_root--embed mayhen_invitation_root--opened' : '' }}"
     data-is-embed="{{ $isEmbed ? 'true' : 'false' }}"
     data-rsvp-endpoint="{{ $rsvpEndpoint }}"
     data-signature-endpoint="{{ $wishEndpoint }}">

    {{-- ==================== 0. MÀN MỞ ĐẦU — CÁNH CỬA ĐÔI SONG HỶ ==================== --}}
    @if (!$isEmbed)
        <div class="mayhen_invitation_opening" aria-label="Cánh cửa mở thiệp" style="{{ $isEmbed ? 'display: none !important;' : '' }}">
            <div class="mayhen_invitation_cloud_door mayhen_invitation_cloud_door--left" data-ignore-autoscroll="true"></div>
            <div class="mayhen_invitation_cloud_door mayhen_invitation_cloud_door--right" data-ignore-autoscroll="true"></div>
            <button type="button" class="mayhen_invitation_open_btn" data-ignore-autoscroll="true">
                <span class="mayhen_invitation_open_btn__text">Chạm để mở thiệp</span>
            </button>
        </div>
    @endif

    <audio class="mayhen_invitation_audio" loop playsinline data-sync-target="music_url"
        @if(!empty($musicUrl)) src="{{ $musicUrl }}" @endif></audio>

    <div class="mayhen_invitation_music_hint" data-ignore-autoscroll="true">
        Nhấn vào đây để phát nhạc 🎵
    </div>

    <button type="button" class="mayhen_invitation_music_toggle mayhen_invitation_music_btn mayhen_invitation_music_dock"
            data-ignore-autoscroll="true" aria-label="Bật hoặc tắt nhạc nền">
        <img src="{{ $resolveAsset('assets/icons/icon-music.svg') }}" alt="Music">
    </button>

    <button type="button" class="mayhen_invitation_scroll_toggle" data-ignore-autoscroll="true" aria-label="Tự động cuộn trang">
        <img src="{{ $resolveAsset('assets/icons/icon-scroll.svg') }}" alt="Auto Scroll">
    </button>

    <div class="mayhen_invitation_snowflakes" aria-hidden="true">
        <div class="mayhen_invitation_snowflake">❅</div>
        <div class="mayhen_invitation_snowflake">❅</div>
        <div class="mayhen_invitation_snowflake">❆</div>
        <div class="mayhen_invitation_snowflake">❅</div>
        <div class="mayhen_invitation_snowflake">❆</div>
        <div class="mayhen_invitation_snowflake">❅</div>
        <div class="mayhen_invitation_snowflake">❆</div>
        <div class="mayhen_invitation_snowflake">❅</div>
        <div class="mayhen_invitation_snowflake">❆</div>
        <div class="mayhen_invitation_snowflake">❆</div>
    </div>

    {{-- ==================== SECTION 1 (Hero Cover) — BÌA THIỆP CHÍNH ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_hero" data-mayhen-section="hero">
        <h3 class="mayhen_invitation_hero__subtitle mayhen_invitation_anim mayhen_invitation_anim--fadeInDown">THIỆP MỜI</h3>
        <p class="mayhen_invitation_hero__couple mayhen_invitation_anim mayhen_invitation_anim--bounceIn">
            <span data-sync-target="couple_names">{{ $coupleNames }}</span>
        </p>

        <div class="mayhen_invitation_hero__stage mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <img class="mayhen_invitation_hero__song_hy"
                 src="{{ $resolveAsset('assets/images/ornament-song-hy-circle.webp') }}" alt="Song Hỷ">
            <img class="mayhen_invitation_hero__cloud_left"
                 src="{{ $resolveAsset('assets/images/ornament-cloud-flower.webp') }}" alt="">
            <img class="mayhen_invitation_hero__cloud_right"
                 src="{{ $resolveAsset('assets/images/ornament-cloud-flower.webp') }}" alt="">

            <div class="mayhen_invitation_hero__photo_frame mayhen_invitation_hero_media" data-lightbox-item="true">
                <img src="{{ $heroPhotoUrl }}"
                     alt="{{ $coupleNames }}"
                     data-sync-target="hero_photo_url"
                     class="mayhen_invitation_ladi_image_background">
            </div>

            <img class="mayhen_invitation_hero__bow_left"
                 src="{{ $resolveAsset('assets/images/ornament-corner-bow.webp') }}" alt="">
            <img class="mayhen_invitation_hero__bow_right"
                 src="{{ $resolveAsset('assets/images/ornament-corner-bow.webp') }}" alt="">
        </div>

        <div class="mayhen_invitation_hero__scroll_cue" data-scroll-next="true">
            <h3 class="mayhen_invitation_hero__scroll_text">Kéo Xuống Để Xem Chi Tiết<br>Thiệp Cưới</h3>
            <img class="mayhen_invitation_hero__scroll_icon"
                 src="{{ $resolveAsset('assets/icons/chevron-down.svg') }}" alt="">
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 2 (Love Story) — THÔNG BÁO HÔN LỄ & GIA ĐÌNH ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_family_ceremony mayhen_invitation_story mayhen_invitation_parents mayhen_invitation_couple"
             data-mayhen-section="story">
        <div class="mayhen_invitation_family_ceremony__crest_wrap">
            <img class="mayhen_invitation_family_ceremony__top_crest"
                 src="{{ $resolveAsset('assets/images/ornament-header-crest.webp') }}" alt="">
            <img class="mayhen_invitation_family_ceremony__sub_crest"
                 src="{{ $resolveAsset('assets/images/ornament-cloud-flower.webp') }}" alt="">
        </div>

        <div class="mayhen_invitation_family_grid">
            <div class="mayhen_invitation_family_col mayhen_invitation_anim mayhen_invitation_anim--bounceInLeft">
                <h3 class="mayhen_invitation_family_col__title">NHÀ TRAI</h3>
                <p class="mayhen_invitation_family_col__parents">
                    Bố: <span class="mayhen_invitation_family_groom_father" data-sync-target="groom_father">{{ $groomFather }}</span><br>
                    Mẹ: <span class="mayhen_invitation_family_groom_mother" data-sync-target="groom_mother">{{ $groomMother }}</span>
                </p>
            </div>
            <div class="mayhen_invitation_family_col mayhen_invitation_anim mayhen_invitation_anim--bounceInRight">
                <h3 class="mayhen_invitation_family_col__title">NHÀ GÁI</h3>
                <p class="mayhen_invitation_family_col__parents">
                    Bố: <span class="mayhen_invitation_family_bride_father" data-sync-target="bride_father">{{ $brideFather }}</span><br>
                    Mẹ: <span class="mayhen_invitation_family_bride_mother" data-sync-target="bride_mother">{{ $brideMother }}</span>
                </p>
            </div>
        </div>

        <h3 class="mayhen_invitation_announce_heading mayhen_invitation_anim mayhen_invitation_anim--fadeInDown">
            TRÂN TRỌNG THÔNG BÁO HÔN LỄ CỦA
        </h3>

        <div class="mayhen_invitation_announce_couple mayhen_invitation_anim mayhen_invitation_anim--bounceIn">
            <h1 class="mayhen_invitation_announce_couple__name mayhen_invitation_ladi_headline_name_groom"
                data-sync-target="groom_name">{{ $groomName }}</h1>
            <span class="mayhen_invitation_announce_couple__amp">&amp;</span>
            <h1 class="mayhen_invitation_announce_couple__name mayhen_invitation_ladi_headline_name_bride"
                data-sync-target="bride_name">{{ $brideName }}</h1>
        </div>

        <div class="mayhen_invitation_announce_photo mayhen_invitation_story_img mayhen_invitation_anim mayhen_invitation_anim--fadeInDown"
             data-lightbox-item="true">
            <img src="{{ $coupleBannerUrl }}"
                 alt="{{ $coupleNames }}"
                 data-sync-target="couple_banner_url">
        </div>

        {{-- Lễ Ăn Hỏi --}}
        <div class="mayhen_invitation_ritual_block mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <img class="mayhen_invitation_ritual_icon--rings"
                 src="{{ $resolveAsset('assets/icons/engagement-rings.svg') }}" alt="Lễ Ăn Hỏi">
            <p class="mayhen_invitation_ritual_title">LỄ ĂN HỎI</p>
            <p class="mayhen_invitation_ritual_time" data-sync-target="engagement_time">{{ $engagementTime }}</p>
            <div class="mayhen_invitation_date_row">
                <div class="mayhen_invitation_date_wing" data-sync-target="engagement_weekday">{{ $engagementWeekday }}</div>
                <div class="mayhen_invitation_date_daynum" data-sync-target="engagement_day">{{ $engagementDay }}</div>
                <div class="mayhen_invitation_date_wing" data-sync-target="engagement_month_year">{{ $engagementMonthYear }}</div>
            </div>
            <p class="mayhen_invitation_ritual_lunar" data-sync-target="engagement_lunar_date">{{ $engagementLunarDate }}</p>
            <div class="mayhen_invitation_ritual_venue_box">
                <span class="mayhen_invitation_ritual_venue_label">HÔN LỄ ĐƯỢC CỬ HÀNH TẠI</span>
                <span class="mayhen_invitation_ritual_venue_name" data-sync-target="engagement_venue">{{ $engagementVenue }}</span>
            </div>
        </div>

        <div class="mayhen_invitation_ritual_connector" aria-hidden="true"></div>

        {{-- Lễ Thành Hôn --}}
        <div class="mayhen_invitation_ritual_block mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <img class="mayhen_invitation_ritual_icon--couple"
                 src="{{ $resolveAsset('assets/icons/ceremony-couple.svg') }}" alt="Lễ Thành Hôn">
            <p class="mayhen_invitation_ritual_title">LỄ THÀNH HÔN</p>
            <p class="mayhen_invitation_ritual_time" data-sync-target="ceremony_time">{{ $ceremonyTime }}</p>
            <div class="mayhen_invitation_date_row">
                <div class="mayhen_invitation_date_wing mayhen_invitation_ladi_headline_wedding_day" data-sync-target="wedding_weekday">{{ $weddingWeekday }}</div>
                <div class="mayhen_invitation_date_daynum" data-sync-target="wedding_day">{{ $weddingDay }}</div>
                <div class="mayhen_invitation_date_wing">
                    <span data-sync-target="wedding_month">{{ $weddingMonth }}</span> - <span data-sync-target="wedding_year">{{ $weddingYear }}</span>
                </div>
            </div>
            <p class="mayhen_invitation_ritual_lunar mayhen_invitation_ladi_paragraph_lunar_calendar"
               data-sync-target="wedding_lunar_date">{{ $weddingLunarDate }}</p>
            <div class="mayhen_invitation_ritual_venue_box">
                <span class="mayhen_invitation_ritual_venue_label">HÔN LỄ ĐƯỢC CỬ HÀNH TẠI</span>
                <span class="mayhen_invitation_ritual_venue_name mayhen_invitation_ceremony_bride_address"
                      data-sync-target="ceremony_venue">{{ $ceremonyVenue }}</span>
            </div>
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 3 (Events & Maps) — THƯ MỜI TIỆC CƯỚI & BẢN ĐỒ ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_party_section mayhen_invitation_events mayhen_invitation_maps"
             data-mayhen-section="events">
        <div class="mayhen_invitation_party_header mayhen_invitation_anim mayhen_invitation_anim--bounceIn">
            <h3 class="mayhen_invitation_party_header__title">Thư Mời Tiệc Cưới</h3>
            <img class="mayhen_invitation_party_header__swoosh"
                 src="{{ $resolveAsset('assets/icons/swoosh-line.svg') }}" alt="">
        </div>

        <div class="mayhen_invitation_triptych">
            <div class="mayhen_invitation_triptych__item mayhen_invitation_anim mayhen_invitation_anim--bounceIn" data-lightbox-item="true">
                <img src="{{ $triptychLeftUrl }}" alt="Ảnh cưới bộ ba trái" data-sync-target="triptych_left_url">
            </div>
            <div class="mayhen_invitation_triptych__item mayhen_invitation_triptych__item--center mayhen_invitation_anim mayhen_invitation_anim--bounceIn" data-lightbox-item="true">
                <img src="{{ $triptychCenterUrl }}" alt="Ảnh cưới bộ ba giữa" data-sync-target="triptych_center_url">
            </div>
            <div class="mayhen_invitation_triptych__item mayhen_invitation_anim mayhen_invitation_anim--bounceIn" data-lightbox-item="true">
                <img src="{{ $triptychRightUrl }}" alt="Ảnh cưới bộ ba phải" data-sync-target="triptych_right_url">
            </div>
        </div>

        <div class="mayhen_invitation_guest_invite mayhen_invitation_guest_name">
            <h3 class="mayhen_invitation_guest_invite__label">TRÂN TRỌNG KÍNH MỜI</h3>
            <h3 class="mayhen_invitation_guest_invite__name" data-sync-target="guest_name">{{ $guestName }}</h3>
            <p class="mayhen_invitation_guest_invite__desc">Tới dự buổi tiệc chung vui cùng gia đình chúng tôi</p>
        </div>

        {{-- Tiệc Cưới Nhà Gái --}}
        <div class="mayhen_invitation_party_block mayhen_invitation_ceremony_bride_card mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <p class="mayhen_invitation_party_block__heading">TIỆC CƯỚI NHÀ GÁI</p>
            <p class="mayhen_invitation_party_block__time mayhen_invitation_ceremony_bride_time"
               data-sync-target="bride_party_time">{{ $bridePartyTime }}</p>
            <div class="mayhen_invitation_date_row">
                <div class="mayhen_invitation_date_wing mayhen_invitation_date_wing--black"
                     data-sync-target="engagement_weekday">{{ $engagementWeekday }}</div>
                <div class="mayhen_invitation_date_daynum mayhen_invitation_date_daynum--black"
                     data-sync-target="engagement_day">{{ $engagementDay }}</div>
                <div class="mayhen_invitation_date_wing mayhen_invitation_date_wing--black"
                     data-sync-target="engagement_month_year">{{ $engagementMonthYear }}</div>
            </div>
            <p class="mayhen_invitation_party_block__lunar" data-sync-target="engagement_lunar_date">{{ $engagementLunarDate }}</p>
            <div class="mayhen_invitation_party_card">
                <p class="mayhen_invitation_party_card__label">Địa điểm tổ chức</p>
                <p class="mayhen_invitation_party_card__venue mayhen_invitation_party_venue"
                   data-sync-target="bride_party_venue">{{ $bridePartyVenue }}</p>
                <p class="mayhen_invitation_party_card__address mayhen_invitation_party_address"
                   data-sync-target="bride_party_address">{{ $bridePartyAddress }}</p>
                <a href="{{ $bridePartyMapUrl }}" target="_blank" rel="noopener noreferrer"
                   class="mayhen_invitation_map_btn mayhen_invitation_map_link_btn"
                   data-sync-target="bride_party_map_url">Xem bản đồ</a>
            </div>
        </div>

        <img class="mayhen_invitation_party_divider mayhen_invitation_divider"
             src="{{ $resolveAsset('assets/images/ornament-divider-floral.webp') }}" alt="">

        {{-- Tiệc Cưới Nhà Trai --}}
        <div class="mayhen_invitation_party_block mayhen_invitation_ceremony_groom_card mayhen_invitation_event_card_party mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <p class="mayhen_invitation_party_block__heading">TIỆC CƯỚI NHÀ TRAI</p>
            <p class="mayhen_invitation_party_block__time mayhen_invitation_party_time"
               data-sync-target="party_time">{{ $partyTime }}</p>
            <div class="mayhen_invitation_date_row">
                <div class="mayhen_invitation_date_wing mayhen_invitation_date_wing--black"
                     data-sync-target="wedding_weekday">{{ $weddingWeekday }}</div>
                <div class="mayhen_invitation_date_daynum mayhen_invitation_date_daynum--black"
                     data-sync-target="wedding_day">{{ $weddingDay }}</div>
                <div class="mayhen_invitation_date_wing mayhen_invitation_date_wing--black">
                    <span data-sync-target="wedding_month">{{ $weddingMonth }}</span> - <span data-sync-target="wedding_year">{{ $weddingYear }}</span>
                </div>
            </div>
            <p class="mayhen_invitation_party_block__lunar" data-sync-target="wedding_lunar_date">{{ $weddingLunarDate }}</p>
            <div class="mayhen_invitation_party_card">
                <p class="mayhen_invitation_party_card__label">Địa điểm tổ chức</p>
                <p class="mayhen_invitation_party_card__venue mayhen_invitation_party_venue"
                   data-sync-target="party_venue">{{ $partyVenue }}</p>
                <p class="mayhen_invitation_party_card__address mayhen_invitation_party_address"
                   data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>
                <a href="{{ $partyMapUrl }}" target="_blank" rel="noopener noreferrer"
                   class="mayhen_invitation_map_btn mayhen_invitation_map_link_btn"
                   data-sync-target="party_map_url">Xem bản đồ</a>
            </div>
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 4 (Countdown & Calendar) — SAVE THE DATE & ĐẾM NGƯỢC ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_calendar_rsvp mayhen_invitation_countdown_calendar mayhen_invitation_countdown mayhen_invitation_calendar"
             data-mayhen-section="countdown_calendar">
        <h3 class="mayhen_invitation_std_title">Save The Date</h3>

        <div class="mayhen_invitation_calendar_wrap">
            <h3 class="mayhen_invitation_calendar_month mayhen_invitation_calendar_month_year"
                data-sync-target="calendar_month_year">{{ $calendarMonthYear }}</h3>
            <span style="display:none;" data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</span>
            <div class="mayhen_invitation_calendar_weekdays">
                <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
            </div>
            <div class="mayhen_invitation_calendar_grid mayhen_invitation_calendar_body"
                 data-sync-target="wedding_calendar"
                 data-wedding-year="{{ $weddingYear }}"
                 data-wedding-month="{{ $weddingMonth }}"
                 data-wedding-day="{{ $weddingDay }}"
                 data-heart-icon="{{ $resolveAsset('assets/icons/calendar-heart.svg') }}">
            </div>
        </div>

        <div class="mayhen_invitation_countdown_stage">
            <div class="mayhen_invitation_countdown_grid mayhen_invitation_anim mayhen_invitation_anim--fadeInRight"
                 data-countdown-target="{{ $weddingYear }}-{{ str_pad((string)$weddingMonth, 2, '0', STR_PAD_LEFT) }}-{{ str_pad((string)$weddingDay, 2, '0', STR_PAD_LEFT) }}T13:00:00+07:00">
                <div class="mayhen_invitation_countdown_item">
                    <span class="mayhen_invitation_countdown_num mayhen_invitation_cd_day" data-countdown-unit="days">00</span>
                    <span class="mayhen_invitation_countdown_label">Ngày</span>
                </div>
                <div class="mayhen_invitation_countdown_item">
                    <span class="mayhen_invitation_countdown_num mayhen_invitation_cd_hour" data-countdown-unit="hours">00</span>
                    <span class="mayhen_invitation_countdown_label">Giờ</span>
                </div>
                <div class="mayhen_invitation_countdown_item">
                    <span class="mayhen_invitation_countdown_num mayhen_invitation_cd_min" data-countdown-unit="minutes">00</span>
                    <span class="mayhen_invitation_countdown_label">Phút</span>
                </div>
                <div class="mayhen_invitation_countdown_item">
                    <span class="mayhen_invitation_countdown_num mayhen_invitation_cd_sec" data-countdown-unit="seconds">00</span>
                    <span class="mayhen_invitation_countdown_label">Giây</span>
                </div>
            </div>
            <img class="mayhen_invitation_countdown_bow mayhen_invitation_countdown_bow--left"
                 src="{{ $resolveAsset('assets/images/ornament-corner-bow.webp') }}" alt="">
            <img class="mayhen_invitation_countdown_bow mayhen_invitation_countdown_bow--right"
                 src="{{ $resolveAsset('assets/images/ornament-corner-bow.webp') }}" alt="">
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 5 (Parents & Couple) — CHÂN DUNG CÔ DÂU CHÚ RỂ ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_couple_section mayhen_invitation_parents mayhen_invitation_couple"
             data-mayhen-section="parents">
        <p class="mayhen_invitation_couple_quote mayhen_invitation_quote_text mayhen_invitation_anim mayhen_invitation_anim--fadeInUp"
           data-sync-target="love_quote">{!! nl2br(e($loveQuote)) !!}</p>

        <div class="mayhen_invitation_profile_row">
            <div class="mayhen_invitation_profile_photo mayhen_invitation_groom_photo mayhen_invitation_anim mayhen_invitation_anim--fadeInLeft"
                 data-lightbox-item="true">
                <img src="{{ $groomPhotoUrl }}" alt="{{ $groomName }}" data-sync-target="groom_photo_url">
            </div>
            <div class="mayhen_invitation_profile_text mayhen_invitation_anim mayhen_invitation_anim--fadeInRight">
                <h3 class="mayhen_invitation_profile_role mayhen_invitation_groom_role">Chú Rể</h3>
                <h3 class="mayhen_invitation_profile_name mayhen_invitation_groom_name"
                    data-sync-target="groom_name">{{ $groomName }}</h3>
                <p class="mayhen_invitation_profile_bio mayhen_invitation_groom_bio"
                   data-sync-target="groom_bio">{{ $groomBio }}</p>
            </div>
        </div>

        <div class="mayhen_invitation_profile_row mayhen_invitation_profile_row--bride">
            <div class="mayhen_invitation_profile_text mayhen_invitation_anim mayhen_invitation_anim--fadeInLeft">
                <h3 class="mayhen_invitation_profile_role mayhen_invitation_bride_role">Cô Dâu</h3>
                <h3 class="mayhen_invitation_profile_name mayhen_invitation_bride_name"
                    data-sync-target="bride_name">{{ $brideName }}</h3>
                <p class="mayhen_invitation_profile_bio mayhen_invitation_bride_bio"
                   data-sync-target="bride_bio">{{ $brideBio }}</p>
            </div>
            <div class="mayhen_invitation_profile_photo mayhen_invitation_bride_photo mayhen_invitation_anim mayhen_invitation_anim--fadeInRight"
                 data-lightbox-item="true">
                <img src="{{ $bridePhotoUrl }}" alt="{{ $brideName }}" data-sync-target="bride_photo_url">
            </div>
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 6 (Album 10 Photos) — ALBUM HÌNH CƯỚI ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_album_section mayhen_invitation_gallery"
             data-mayhen-section="album">
        <div class="mayhen_invitation_album_header">
            <h3 class="mayhen_invitation_album_title mayhen_invitation_gallery_title mayhen_invitation_anim mayhen_invitation_anim--fadeInDown">Album hình cưới</h3>
            <img class="mayhen_invitation_album_ornament"
                 src="{{ $resolveAsset('assets/images/ornament-divider-floral.webp') }}" alt="">
        </div>

        <div class="mayhen_invitation_album_grid mayhen_invitation_gallery_wrap">
            @for ($i = 1; $i <= 8; $i++)
                <div class="mayhen_invitation_album_item mayhen_invitation_gallery_item mayhen_invitation_anim mayhen_invitation_anim--fadeInDown"
                     data-lightbox-item="true" data-index="{{ $i }}">
                    <img src="{{ $galleryPhotos[$i] }}"
                         alt="Ảnh cưới {{ $i }}"
                         class="mayhen_invitation_gallery_img"
                         data-sync-target="gallery_photo_{{ $i }}">
                </div>
            @endfor
        </div>

        <button type="button" class="mayhen_invitation_album_more" data-open-album-popup="true">
            <span class="mayhen_invitation_album_more__label">XEM THÊM</span>
            <img class="mayhen_invitation_album_more__icon"
                 src="{{ $resolveAsset('assets/icons/chevron-down.svg') }}" alt="">
        </button>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 7 (Gift & VietQR) — HỘP MỪNG CƯỚI ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_gift_section mayhen_invitation_gift"
             data-mayhen-section="gift">
        <div class="mayhen_invitation_gift_header">
            <img class="mayhen_invitation_gift_icon"
                 src="{{ $resolveAsset('assets/icons/gift-pouch.svg') }}" alt="">
            <h3 class="mayhen_invitation_gift_title">Hộp Mừng Cưới</h3>
            <p class="mayhen_invitation_gift_desc">
                Thật vui vì được gặp và đón tiếp các bạn trong một dịp đặc biệt như đám cưới của chúng tôi.
            </p>
        </div>

        <div class="mayhen_invitation_gift_grid">
            {{-- QR Chú Rể --}}
            <div class="mayhen_invitation_qr_card mayhen_invitation_gift_card_groom mayhen_invitation_anim mayhen_invitation_anim--fadeInLeft">
                <h3 class="mayhen_invitation_qr_card__title mayhen_invitation_gift_role_groom">MỪNG CƯỚI ĐẾN<br>CHÚ RỂ</h3>
                <div class="mayhen_invitation_qr_card__img_wrap mayhen_invitation_gift_qr_groom" data-lightbox-item="true">
                    <img src="{{ $qrGroomUrl }}" alt="QR Chú Rể" data-sync-target="qr_groom_image">
                </div>
                <p class="mayhen_invitation_qr_card__bank">
                    <span class="mayhen_invitation_gift_bank_name_groom" data-sync-target="groom_bank_name">{{ $groomBankName }}</span>:
                    <strong class="mayhen_invitation_gift_account_number_groom" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</strong>
                </p>
                <a href="{{ $qrGroomUrl }}" download="QR-Chu-Re.webp"
                   class="mayhen_invitation_qr_card__btn mayhen_invitation_copy_btn">Tải ảnh QR</a>
            </div>

            {{-- QR Cô Dâu --}}
            <div class="mayhen_invitation_qr_card mayhen_invitation_gift_card_bride mayhen_invitation_anim mayhen_invitation_anim--fadeInRight">
                <h3 class="mayhen_invitation_qr_card__title mayhen_invitation_gift_role_bride">MỪNG CƯỚI ĐẾN<br>CÔ DÂU</h3>
                <div class="mayhen_invitation_qr_card__img_wrap mayhen_invitation_gift_qr_bride" data-lightbox-item="true">
                    <img src="{{ $qrBrideUrl }}" alt="QR Cô Dâu" data-sync-target="qr_bride_image">
                </div>
                <p class="mayhen_invitation_qr_card__bank">
                    <span class="mayhen_invitation_gift_bank_name_bride" data-sync-target="bride_bank_name">{{ $brideBankName }}</span>:
                    <strong class="mayhen_invitation_gift_account_number_bride" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</strong>
                </p>
                <a href="{{ $qrBrideUrl }}" download="QR-Co-Dau.webp"
                   class="mayhen_invitation_qr_card__btn mayhen_invitation_copy_btn">Tải ảnh QR</a>
            </div>
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 8 (Interactive, RSVP, Wishes & Ending) — TƯƠNG TÁC & CẢM ƠN ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_calendar_rsvp mayhen_invitation_interactive_ending mayhen_invitation_rsvp mayhen_invitation_wishes mayhen_invitation_ending"
             data-mayhen-section="ending,interactive,rsvp,wishes">

        {{-- RSVP Form --}}
        <div class="mayhen_invitation_rsvp_header mayhen_invitation_anim mayhen_invitation_anim--fadeInDown">
            <h3 class="mayhen_invitation_rsvp_header__line">Xác Nhận Tham Dự</h3>
            <h1 class="mayhen_invitation_rsvp_header__amp">&amp;</h1>
            <h3 class="mayhen_invitation_rsvp_header__line">Gửi Lời Chúc</h3>
        </div>

        <form class="mayhen_invitation_rsvp_form mayhen_invitation_anim mayhen_invitation_anim--fadeInUp"
              autocomplete="off" novalidate data-rsvp-action="{{ $rsvpEndpoint }}">
            @csrf
            <input type="text" name="name" required
                   class="mayhen_invitation_form_control mayhen_invitation_rsvp_name_input"
                   placeholder="Họ và Tên" aria-label="Họ và Tên"
                   value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}">

            <div class="mayhen_invitation_form_radio_box mayhen_invitation_rsvp_attendance" role="radiogroup" aria-label="Trạng thái tham dự">
                <label class="mayhen_invitation_form_radio_label">
                    <input type="radio" name="is_attending" value="Có, tôi sẽ đến" required checked>
                    <span>Có, tôi sẽ đến</span>
                </label>
                <label class="mayhen_invitation_form_radio_label">
                    <input type="radio" name="is_attending" value="Xin lỗi, tôi bận mất rồi" required>
                    <span>Xin lỗi, tôi bận mất rồi</span>
                </label>
            </div>

            <div class="mayhen_invitation_form_row_2col">
                <select name="party_side" class="mayhen_invitation_form_select" aria-label="Nơi tham dự">
                    <option value="">Nơi tham dự</option>
                    <option value="Tiệc Nhà Trai">Tiệc Nhà Trai</option>
                    <option value="Tiệc Nhà Gái">Tiệc Nhà Gái</option>
                    <option value="Cả hai tiệc">Cả hai tiệc</option>
                </select>
                <select name="guest_count" class="mayhen_invitation_form_select mayhen_invitation_rsvp_count" aria-label="Số lượng tham dự">
                    <option value="">SL tham dự</option>
                    <option value="1">1 Người</option>
                    <option value="2">2 Người</option>
                    <option value="3">3 Người</option>
                    <option value="4">4 Người</option>
                    <option value="5">5 Người</option>
                </select>
            </div>

            <textarea name="message" class="mayhen_invitation_form_textarea mayhen_invitation_wish_textarea"
                      placeholder="Gửi lời chúc cho cô dâu chú rể" aria-label="Lời chúc"></textarea>

            <button type="submit" class="mayhen_invitation_rsvp_submit mayhen_invitation_rsvp_submit_btn">Xác nhận</button>
            <p class="mayhen_invitation_status_msg" role="status" aria-live="polite"></p>
        </form>

        {{-- Danh sách lời chúc --}}
        <div class="mayhen_invitation_wishes_container mayhen_invitation_wish_list">
            @if(isset($wishes) && $wishes->count() > 0)
                @foreach($wishes as $wish)
                    <div class="mayhen_invitation_wish_item">
                        <div class="mayhen_invitation_wish_message">"{{ $wish->message }}"</div>
                        <div class="mayhen_invitation_wish_name">— {{ $wish->guest_name }}</div>
                    </div>
                @endforeach
            @else
                <div class="mayhen_invitation_wish_item">
                    <div class="mayhen_invitation_wish_message">"Chúc hai bạn trăm năm hạnh phúc, bình an và viên mãn!"</div>
                    <div class="mayhen_invitation_wish_name">— Mây Hẹn</div>
                </div>
            @endif
        </div>

        {{-- Ký Tên Lưu Bút — KHÔNG ĐƯỢC ĐẶT ẢNH CỘI ĐÔI VÀO ĐÂY --}}
        <div class="mayhen_invitation_signature_box mayhen_invitation_signature mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <h3 class="mayhen_invitation_signature_title">Ký Tên Lưu Bút</h3>
            <p class="mayhen_invitation_signature_sub">Hãy để lại chữ ký kỷ niệm chúc phúc cho hai vợ chồng</p>
            <div class="mayhen_invitation_signature_canvas_wrap">
                <canvas class="mayhen_invitation_signature_canvas mayhen_invitation_wb_sig_canvas"
                        width="330" height="160" aria-label="Khung vẽ chữ ký"></canvas>
            </div>
            <div class="mayhen_invitation_signature_controls">
                <button type="button" class="mayhen_invitation_sig_btn mayhen_invitation_wb_sig_btn_clear"
                        data-sig-action="clear">Xóa</button>
                <button type="button" class="mayhen_invitation_sig_btn mayhen_invitation_signature_open_btn"
                        data-sig-action="preview">Xem trước</button>
                <button type="button" class="mayhen_invitation_sig_btn mayhen_invitation_sig_btn--primary mayhen_invitation_signature_save_btn mayhen_invitation_wb_sig_btn_save"
                        data-sig-action="save">Lưu chữ ký</button>
            </div>
            <p class="mayhen_invitation_sig_status mayhen_invitation_status_msg" role="status" aria-live="polite"></p>
            <div class="mayhen_invitation_signature_preview_list" aria-label="Danh sách chữ ký đã lưu"></div>
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== FOOTER — LỜI CẢM ƠN CUỐI THIỆP ==================== --}}
    <footer class="mayhen_invitation_section mayhen_invitation_footer_section mayhen_invitation_ending_photo">
        <img class="mayhen_invitation_footer_ornament"
             src="{{ $resolveAsset('assets/images/ornament-footer-floral.webp') }}" alt="">
        <h3 class="mayhen_invitation_footer_names mayhen_invitation_ending_couple_name"
            data-sync-target="couple_names">{{ $coupleNames }}</h3>
        <div class="mayhen_invitation_footer_circle_stage">
            <div class="mayhen_invitation_footer_circle mayhen_invitation_ending_photo" data-lightbox-item="true">
                <img src="{{ $footerPhotoUrl }}"
                     alt="{{ $coupleNames }}"
                     data-sync-target="footer_photo_url">
            </div>
            <img class="mayhen_invitation_footer_thankyou_img"
                 src="{{ $resolveAsset('assets/images/ornament-thankyou-text.webp') }}" alt="Thank You">
        </div>
    </footer>

    {{-- ==================== LIGHTBOX MODAL ==================== --}}
    <div class="mayhen_invitation_modal_backdrop mayhen_invitation_lightbox"
         role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to">
        <div class="mayhen_invitation_modal_box">
            <button type="button" class="mayhen_invitation_modal_close" aria-label="Đóng">✕</button>
            <button type="button" class="mayhen_invitation_modal_nav mayhen_invitation_modal_nav--prev" aria-label="Ảnh trước">❮</button>
            <img class="mayhen_invitation_modal_img" src="" alt="Ảnh phóng to">
            <button type="button" class="mayhen_invitation_modal_nav mayhen_invitation_modal_nav--next" aria-label="Ảnh tiếp">❯</button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script>
        window.__MAYHEN_IS_EMBED      = {{ $isEmbed ? 'true' : 'false' }};
        window.__MAYHEN_RSVP_ENDPOINT = "{{ $rsvpEndpoint }}";
        window.__MAYHEN_WISH_ENDPOINT = "{{ $wishEndpoint }}";
    </script>
    <script type="module" src="{{ route('templates.asset', ['slug' => $slug, 'path' => 'script.js']) }}?v={{ $jsVersion }}"></script>
@endpush

