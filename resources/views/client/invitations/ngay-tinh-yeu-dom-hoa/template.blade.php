@extends('client.layouts.invitation')

@php
    // =========================================================================
    // TEMPLATE: Ngày Tình Yêu Đơm Hoa (ngay-tinh-yeu-dom-hoa)
    // Phong cách: Quiet Luxury — Đỏ Son — Thư Pháp — Song Hỷ
    // Chuẩn: 10 Section BEM mayhen_invitation_* | data-sync-target đầy đủ
    // =========================================================================

    $slug      = 'ngay-tinh-yeu-dom-hoa';
    $isEmbed   = $isEmbed ?? request()->boolean('embed')
                          || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Ngày Tình Yêu Đơm Hoa';

    // --- Hàm resolve asset ---
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

    // --- Dữ liệu extra ---
    $extraData = $wedding->extra_data ?? [];

    // --- 1. TÊN CÔ DÂU CHÚ RỂ ---
    $groomName   = $wedding->groom_name ?? ($extraData['groom_name'] ?? 'Đình Sang');
    $brideName   = $wedding->bride_name ?? ($extraData['bride_name'] ?? 'Hoài Thương');
    $coupleNames = $extraData['couple_names'] ?? ($groomName . ' & ' . $brideName);

    // --- 2. SECTION 1: HERO ---
    $ceremonyType = $extraData['ceremony_type'] ?? 'Lễ Thành Hôn';
    $heroSlogan   = $extraData['slogan'] ?? ($extraData['hero_slogan'] ?? 'Hôn nhân không phải là nhìn nhau, mà là cùng nhau nhìn về một hướng.');

    // --- 3. NGÀY GIỜ CƯỚI ---
    $weddingDate       = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2026, 11, 22, 13, 0, 0);
    $weddingDay        = $weddingDate->format('d');
    $weddingMonth      = $weddingDate->format('m');
    $weddingYear       = $weddingDate->format('Y');
    $weddingDateDigits = $weddingDate->format('d.m.Y');
    $weddingWeekday    = mb_strtoupper($weddingDate->locale('vi')->isoFormat('dddd'));
    $weddingLunarDate  = $extraData['wedding_lunar_date'] ?? '(Tức ngày 14 tháng 10 năm Bính Ngọ)';

    // Giờ đếm ngược
    $countdownTime = $extraData['countdown_time'] ?? '13:00';
    [$cdH, $cdM]   = explode(':', $countdownTime . ':00');
    $countdownIso  = sprintf(
        '%s-%s-%sT%s:%s:00+07:00',
        $weddingYear,
        str_pad((string) $weddingMonth, 2, '0', STR_PAD_LEFT),
        str_pad((string) $weddingDay,   2, '0', STR_PAD_LEFT),
        str_pad((string) $cdH, 2, '0', STR_PAD_LEFT),
        str_pad((string) $cdM, 2, '0', STR_PAD_LEFT)
    );

    // --- 4. SECTION 2: STORY ---
    $storyTitle    = $extraData['story_title']   ?? 'Chuyện Tình Yêu';
    $storySubtitle = $extraData['story_subtitle'] ?? 'Ngày Tình Yêu Đơm Hoa';
    $coupleQuote   = $extraData['couple_quote']  ?? '"Hôn nhân là chuyện cả đời, Yêu người vừa ý, cưới người mình thương"';
    $storyContent  = $extraData['story_content'] ?? 'Biết ơn vì đã tìm thấy nhau giữa thế giới bao la. Cùng nhau chúng ta đi qua những năm tháng tuổi trẻ, và hôm nay bước vào cánh cửa hôn nhân rạng rỡ và hạnh phúc trọn vẹn nhất.';

    // --- 5. SECTION 3: GIA ĐÌNH & ĐÔI UYÊN ƯƠNG ---
    $groomFather        = $extraData['groom_father']         ?? 'Nguyễn Đắc Cường';
    $groomMother        = $extraData['groom_mother']         ?? 'Đặng Thị Hương';
    $groomParentsAddr   = $extraData['groom_parents_address'] ?? 'TDP Nhân Sơn, Yên Nghĩa, Hà Đông, Hà Nội';
    $brideFather        = $extraData['bride_father']         ?? 'Nguyễn Đức Trí';
    $brideMother        = $extraData['bride_mother']         ?? 'Bùi Thị Tầm';
    $brideParentsAddr   = $extraData['bride_parents_address'] ?? 'Tổ 5 Quang Lãm, Phú Lương, Hà Đông, Hà Nội';
    $groomBio           = $extraData['groom_bio'] ?? 'Biết ơn Em về tất cả. Nguyện bên Em qua muôn nẻo đường phía trước, mình đồng hành cùng nhau đến mãi mãi về sau.';
    $brideBio           = $extraData['bride_bio'] ?? 'Hành trình trưởng thành của em thật đẹp khi có sự xuất hiện của Anh. Cùng nhau chúng ta khám phá thế giới kỳ diệu này anh nhé.';

    // --- 6. SECTION 5: SỰ KIỆN ---
    $engagementTime    = $extraData['engagement_time']     ?? '08:00';
    $engagementVenue   = $extraData['engagement_venue']    ?? 'TƯ GIA NHÀ GÁI';

    $weddingDayDefault = $weddingDate->copy()->subDay();
    $engagementDay     = $extraData['engagement_day']        ?? $weddingDayDefault->format('d');
    $engagementWeekday = $extraData['engagement_weekday']    ?? mb_strtoupper($weddingDayDefault->locale('vi')->isoFormat('dddd'));
    $engagementMonthYear = $extraData['engagement_month_year'] ?? ($weddingMonth . ' - ' . $weddingYear);
    $engagementLunarDate = $extraData['engagement_lunar_date'] ?? '(Tức ngày 13 tháng 10 năm Bính Ngọ)';

    $ceremonyTime    = $extraData['ceremony_time']  ?? '13:00';
    $ceremonyVenue   = $extraData['ceremony_venue'] ?? 'TƯ GIA NHÀ TRAI';

    $bridePartyTime    = $extraData['bride_party_time']    ?? '16:30';
    $bridePartyVenue   = $extraData['bride_party_venue']   ?? 'NHÀ VĂN HÓA TỔ 5 QUANG LÃM';
    $bridePartyAddress = $extraData['bride_party_address'] ?? 'Phường Phú Lương, TP. Hà Nội';
    $bridePartyMapUrl  = $extraData['bride_party_map_url'] ?? '#map-nha-gai';

    $partyTime         = $extraData['party_time']         ?? '17:30';
    $partyVenue        = $extraData['party_venue']        ?? 'NHÀ VĂN HÓA TỔ 6 NHÂN ĐẠO';
    $eventPartyAddress = $extraData['event_party_address'] ?? 'TDP Nhân Sơn, P. Yên Nghĩa, TP. Hà Nội';
    $partyMapUrl       = $extraData['party_map_url']       ?? '#map-nha-trai';

    // --- 7. SECTION 6: TIMELINE ---
    $timelineDefault = [
        ['time' => '17:30', 'title' => 'Đón khách'],
        ['time' => '18:00', 'title' => 'Cử hành hôn lễ'],
        ['time' => '18:30', 'title' => 'Khai tiệc'],
        ['time' => '20:00', 'title' => 'Giao lưu & chụp ảnh'],
    ];
    $timelineList = $extraData['timeline'] ?? $timelineDefault;
    if (!is_array($timelineList) || empty($timelineList)) {
        $timelineList = $timelineDefault;
    }

    // --- 8. SECTION 7: DRESSCODE ---
    $dresscodeColors = $extraData['dresscode_colors'] ?? '#FCE39E,#FFFFFF,#1B2A4A,#0C0C0C';
    $dresscodeNote   = $extraData['dresscode_note']   ?? 'Khuyến khích trang phục thanh lịch mang tông màu ấm áp để những bức ảnh kỷ niệm thêm phần lung linh.';
    $dresscodeItems  = array_filter(array_map('trim', explode(',', $dresscodeColors)));
    $dresscodeColorNames = [
        '#fce39e' => 'Vàng Champagne',
        '#ffffff' => 'Trắng Tinh Khôi',
        '#1b2a4a' => 'Xanh Đêm Sâu',
        '#0c0c0c' => 'Đen Huyền',
        '#8e0101' => 'Đỏ Son',
        '#a8b8a3' => 'Xanh Sage',
        '#233628' => 'Xanh Rừng',
        '#c5a059' => 'Vàng Cổ Điển',
        '#f8f6f0' => 'Kem Ấm Áp',
        '#1f1d1a' => 'Charcoal',
        '#f2d6c9' => 'Hồng Pastel',
        '#e8b4b8' => 'Hồng Đất',
    ];

    // --- 9. ẢNH ---
    $heroPhotoUrl      = $resolveAsset('assets/images/hero-couple.webp',           $wedding->cover_photo_url ?? ($extraData['hero_photo_url'] ?? null));
    $saveDatePhotoUrl  = $resolveAsset('assets/images/section2-couple-banner.webp', $extraData['save_date_photo_url'] ?? ($extraData['couple_banner_url'] ?? null));
    $triptychLeftUrl   = $resolveAsset('assets/images/triptych-left.webp',          $extraData['triptych_left_url'] ?? null);
    $triptychCenterUrl = $resolveAsset('assets/images/triptych-center.webp',        $extraData['triptych_center_url'] ?? null);
    $triptychRightUrl  = $resolveAsset('assets/images/triptych-right.webp',         $extraData['triptych_right_url'] ?? null);
    $groomPhotoUrl     = $resolveAsset('assets/images/groom-portrait.webp',         $extraData['groom_photo_url'] ?? ($wedding->groom_photo_url ?? null));
    $bridePhotoUrl     = $resolveAsset('assets/images/bride-portrait.webp',         $extraData['bride_photo_url'] ?? ($wedding->bride_photo_url ?? null));
    $footerPhotoUrl    = $resolveAsset('assets/images/footer-couple-circle.webp',   $extraData['footer_photo_url'] ?? null);

    // Album 8 ảnh — key: gallery_photo_1 .. gallery_photo_8
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

    // --- 10. SECTION 9: MỪNG CƯỚI ---
    $groomBankName    = $extraData['groom_bank_name']    ?? 'Vietcombank';
    $groomBankAccount = $extraData['groom_bank_account'] ?? '0123456789';
    $groomAccountName = $extraData['groom_account_name'] ?? 'NGUYEN DINH SANG';
    $qrGroomUrl       = $resolveAsset('assets/images/qr-groom.webp', $extraData['qr_groom_image'] ?? null);

    $brideBankName    = $extraData['bride_bank_name']    ?? 'Techcombank';
    $brideBankAccount = $extraData['bride_bank_account'] ?? '9876543210';
    $brideAccountName = $extraData['bride_account_name'] ?? 'NGUYEN HOAI THUONG';
    $qrBrideUrl       = $resolveAsset('assets/images/qr-bride.webp', $extraData['qr_bride_image'] ?? null);

    // --- 11. SECTION 10: ENDING ---
    $endingTitle = $extraData['ending_title'] ?? 'Thank You';
    $endingText  = $extraData['ending_text']  ?? 'Sự hiện diện của quý khách là niềm vinh hạnh lớn nhất của chúng tôi!';

    // --- Khách mời & Endpoints ---
    $guestName    = $invitation->guest_name ?? 'Quý Khách';
    $musicUrl     = $wedding->music_url ?? ($extraData['music_url'] ?? '');
    $rsvpEndpoint = $rsvpSubmitUrl ?? ($isPreview ? route('templates.preview.rsvp', $slug) : route('invitation.show', $invitation->slug ?? ''));
    $wishEndpoint = $wishSubmitUrl ?? ($isPreview ? route('templates.preview.wish', $slug)  : route('invitation.wish.store', $invitation->slug ?? ''));

    // --- Meta ---
    $pageTitle  = $isPreview
        ? "Xem thử: \"{$templateName}\" | " . config('app.name')
        : "Thiệp cưới: {$groomName} & {$brideName} | " . config('app.name');
    $cssVersion = file_exists(resource_path("views/client/invitations/{$slug}/styles.css"))
        ? filemtime(resource_path("views/client/invitations/{$slug}/styles.css"))
        : time();
    $jsVersion  = file_exists(resource_path("views/client/invitations/{$slug}/script.js"))
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

    {{-- ==================== MÀN MỞ ĐẦU — CÁNH CỬA ĐÔI SONG HỶ ==================== --}}
    <div class="mayhen_invitation_opening{{ $isEmbed ? ' mayhen_invitation_opening--hidden' : '' }}"
         data-mayhen-section="opening"
         aria-label="Cánh cửa mở thiệp"
         @if($isEmbed) style="display:none!important;" @endif>
        <div class="mayhen_invitation_cloud_door mayhen_invitation_cloud_door--left" data-ignore-autoscroll="true"></div>
        <div class="mayhen_invitation_cloud_door mayhen_invitation_cloud_door--right" data-ignore-autoscroll="true"></div>
        <div class="mayhen_invitation_envelope" data-ignore-autoscroll="true">
            <img class="mayhen_invitation_envelope__song_hy"
                 src="{{ $resolveAsset('assets/images/ornament-song-hy-circle.webp') }}" alt="Song Hỷ">
        </div>
        <button type="button" class="mayhen_invitation_open_btn" data-ignore-autoscroll="true" aria-label="Mở thiệp cưới">
            <span class="mayhen_invitation_open_btn__text">Chạm để mở thiệp</span>
        </button>
    </div>

    {{-- Nhạc nền --}}
    <audio class="mayhen_invitation_audio" loop playsinline data-sync-target="music_url"
        @if(!empty($musicUrl)) src="{{ $musicUrl }}" @endif></audio>

    {{-- Nút đĩa than nhạc --}}
    <button type="button" class="mayhen_invitation_music_toggle mayhen_invitation_music_btn mayhen_invitation_music_dock"
            data-ignore-autoscroll="true" aria-label="Bật hoặc tắt nhạc nền">
        <img src="{{ $resolveAsset('assets/icons/icon-music.svg') }}" alt="Music">
    </button>

    {{-- Nút auto-scroll --}}
    <button type="button" class="mayhen_invitation_scroll_toggle" data-ignore-autoscroll="true" aria-label="Tự động cuộn trang">
        <img src="{{ $resolveAsset('assets/icons/icon-scroll.svg') }}" alt="Auto Scroll">
    </button>

    {{-- Hiệu ứng hoa tuyết đỏ --}}
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

    {{-- ==================== SECTION 1: TRANG BÌA CHÍNH (HERO COVER) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_hero" data-mayhen-section="hero">

        {{-- Loại hình lễ cưới --}}
        <div class="mayhen_invitation_ladi_headline_ceremony_type" data-sync-target="ceremony_type">
            {{ $ceremonyType }}
        </div>

        {{-- Tên đôi uyên ương --}}
        <p class="mayhen_invitation_hero__couple mayhen_invitation_anim mayhen_invitation_anim--bounceIn">
            <span class="mayhen_invitation_ladi_headline_name_groom" data-sync-target="groom_name">{{ $groomName }}</span>
            <span class="mayhen_invitation_hero_amp">&amp;</span>
            <span class="mayhen_invitation_ladi_headline_name_bride" data-sync-target="bride_name">{{ $brideName }}</span>
        </p>

        {{-- Khung ảnh chính --}}
        <div class="mayhen_invitation_hero__stage mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <img class="mayhen_invitation_hero__song_hy"
                 src="{{ $resolveAsset('assets/images/ornament-song-hy-circle.webp') }}" alt="Song Hỷ">
            <img class="mayhen_invitation_hero__cloud_left"
                 src="{{ $resolveAsset('assets/images/ornament-cloud-flower.webp') }}" alt="">
            <img class="mayhen_invitation_hero__cloud_right"
                 src="{{ $resolveAsset('assets/images/ornament-cloud-flower.webp') }}" alt="">

            <div class="mayhen_invitation_hero__photo_frame mayhen_invitation_hero_media" data-lightbox-item="true">
                <img src="{{ $heroPhotoUrl }}"
                     alt="{{ $groomName }} & {{ $brideName }}"
                     class="mayhen_invitation_ladi_image"
                     data-sync-target="hero_photo_url">
            </div>

            <img class="mayhen_invitation_hero__bow_left"
                 src="{{ $resolveAsset('assets/images/ornament-corner-bow.webp') }}" alt="">
            <img class="mayhen_invitation_hero__bow_right"
                 src="{{ $resolveAsset('assets/images/ornament-corner-bow.webp') }}" alt="">
        </div>

        {{-- Ngày cưới --}}
        <p class="mayhen_invitation_ladi_headline_wedding_day" data-sync-target="wedding_date_digits">
            {{ $weddingDateDigits }}
        </p>
        <p class="mayhen_invitation_ladi_paragraph_lunar_calendar" data-sync-target="wedding_lunar_date">
            {{ $weddingLunarDate }}
        </p>

        {{-- Slogan lãng mạn --}}
        <p class="mayhen_invitation_ladi_paragraph_slogan" data-sync-target="slogan">{{ $heroSlogan }}</p>

        {{-- Scroll cue (hardcode, không phải slogan) --}}
        <div class="mayhen_invitation_hero__scroll_cue" data-scroll-next="true">
            <p class="mayhen_invitation_hero__scroll_text">Kéo Xuống Để Xem Chi Tiết<br>Thiệp Cưới</p>
            <img class="mayhen_invitation_hero__scroll_icon"
                 src="{{ $resolveAsset('assets/icons/chevron-down.svg') }}" alt="">
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 2: LỜI NGỎ & CHUYỆN TÌNH YÊU ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_story mayhen_invitation_quote"
             data-mayhen-section="story">

        {{-- Kicker + Tiêu đề --}}
        <div class="mayhen_invitation_story_kicker">Lời Ngỏ</div>
        <h2 class="mayhen_invitation_ladi_headline_story" data-sync-target="story_title">{{ $storyTitle }}</h2>

        {{-- Thông báo hai họ --}}
        <div class="mayhen_invitation_family_ceremony__crest_wrap">
            <img class="mayhen_invitation_family_ceremony__top_crest"
                 src="{{ $resolveAsset('assets/images/ornament-header-crest.webp') }}" alt="">
            <img class="mayhen_invitation_family_ceremony__sub_crest"
                 src="{{ $resolveAsset('assets/images/ornament-cloud-flower.webp') }}" alt="">
        </div>

        <div class="mayhen_invitation_family_grid">
            <div class="mayhen_invitation_family_col mayhen_invitation_anim mayhen_invitation_anim--bounceInLeft">
                <h4 class="mayhen_invitation_family_groom_title">NHÀ TRAI</h4>
                <p class="mayhen_invitation_family_col__parents">
                    Bố: <span class="mayhen_invitation_family_groom_father" data-sync-target="groom_father">{{ $groomFather }}</span><br>
                    Mẹ: <span class="mayhen_invitation_family_groom_mother" data-sync-target="groom_mother">{{ $groomMother }}</span>
                </p>
                <p class="mayhen_invitation_family_groom_address" data-sync-target="groom_parents_address">{{ $groomParentsAddr }}</p>
            </div>
            <div class="mayhen_invitation_family_col mayhen_invitation_anim mayhen_invitation_anim--bounceInRight">
                <h4 class="mayhen_invitation_family_bride_title">NHÀ GÁI</h4>
                <p class="mayhen_invitation_family_col__parents">
                    Bố: <span class="mayhen_invitation_family_bride_father" data-sync-target="bride_father">{{ $brideFather }}</span><br>
                    Mẹ: <span class="mayhen_invitation_family_bride_mother" data-sync-target="bride_mother">{{ $brideMother }}</span>
                </p>
                <p class="mayhen_invitation_family_bride_address" data-sync-target="bride_parents_address">{{ $brideParentsAddr }}</p>
            </div>
        </div>

        <div class="mayhen_invitation_family_notice">
            TRÂN TRỌNG THÔNG BÁO HÔN LỄ CỦA
        </div>

        <div class="mayhen_invitation_announce_couple mayhen_invitation_anim mayhen_invitation_anim--bounceIn">
            <h1 class="mayhen_invitation_announce_couple__name mayhen_invitation_ladi_headline_name_groom"
                data-sync-target="groom_name">{{ $groomName }}</h1>
            <span class="mayhen_invitation_announce_couple__amp">&amp;</span>
            <h1 class="mayhen_invitation_announce_couple__name mayhen_invitation_ladi_headline_name_bride"
                data-sync-target="bride_name">{{ $brideName }}</h1>
        </div>

        {{-- Tiêu đề phụ chuyện tình yêu --}}
        <h3 class="mayhen_invitation_story_subtitle" data-sync-target="story_subtitle">{{ $storySubtitle }}</h3>

        {{-- Ảnh kỷ niệm lời ngỏ --}}
        <div class="mayhen_invitation_story_img mayhen_invitation_anim mayhen_invitation_anim--fadeInDown"
             data-lightbox-item="true">
            <img src="{{ $saveDatePhotoUrl }}"
                 alt="{{ $groomName }} & {{ $brideName }}"
                 data-sync-target="save_date_photo_url">
        </div>

        {{-- Câu trích dẫn --}}
        <blockquote class="mayhen_invitation_quote_text mayhen_invitation_anim mayhen_invitation_anim--fadeInUp"
                    data-sync-target="couple_quote">{!! nl2br(e($coupleQuote)) !!}</blockquote>

        {{-- Đoạn tâm sự --}}
        <p class="mayhen_invitation_story_content mayhen_invitation_anim mayhen_invitation_anim--fadeInUp"
           data-sync-target="story_content">{{ $storyContent }}</p>

        <div class="mayhen_invitation_monogram">✦ {{ mb_strtoupper(mb_substr($groomName, -mb_strpos(strrev($groomName), ' ') ?: mb_strlen($groomName), 1)) }} &amp; {{ mb_strtoupper(mb_substr($brideName, -mb_strpos(strrev($brideName), ' ') ?: mb_strlen($brideName), 1)) }} ✦</div>

        {{-- Lễ Ăn Hỏi --}}
        <div class="mayhen_invitation_ritual_block mayhen_invitation_ceremony_bride_card mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <img class="mayhen_invitation_ritual_icon--rings"
                 src="{{ $resolveAsset('assets/icons/engagement-rings.svg') }}" alt="Lễ Ăn Hỏi">
            <p class="mayhen_invitation_ritual_title">LỄ ĂN HỎI</p>
            <p class="mayhen_invitation_ritual_time mayhen_invitation_ceremony_bride_time" data-sync-target="engagement_time">{{ $engagementTime }}</p>
            <div class="mayhen_invitation_date_row">
                <div class="mayhen_invitation_date_wing" data-sync-target="engagement_weekday">{{ $engagementWeekday }}</div>
                <div class="mayhen_invitation_date_daynum" data-sync-target="engagement_day">{{ $engagementDay }}</div>
                <div class="mayhen_invitation_date_wing" data-sync-target="engagement_month_year">{{ $engagementMonthYear }}</div>
            </div>
            <p class="mayhen_invitation_ritual_lunar" data-sync-target="engagement_lunar_date">{{ $engagementLunarDate }}</p>
            <div class="mayhen_invitation_ritual_venue_box">
                <span class="mayhen_invitation_ritual_venue_label">HÔN LỄ ĐƯỢC CỬ HÀNH TẠI</span>
                <span class="mayhen_invitation_ritual_venue_name mayhen_invitation_ceremony_bride_address" data-sync-target="engagement_venue">{{ $engagementVenue }}</span>
            </div>
        </div>

        <div class="mayhen_invitation_ritual_connector" aria-hidden="true"></div>

        {{-- Lễ Thành Hôn --}}
        <div class="mayhen_invitation_ritual_block mayhen_invitation_ceremony_groom_card mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <img class="mayhen_invitation_ritual_icon--couple"
                 src="{{ $resolveAsset('assets/icons/ceremony-couple.svg') }}" alt="Lễ Thành Hôn">
            <p class="mayhen_invitation_ritual_title">LỄ THÀNH HÔN</p>
            <p class="mayhen_invitation_ritual_time mayhen_invitation_ceremony_groom_time" data-sync-target="ceremony_time">{{ $ceremonyTime }}</p>
            <div class="mayhen_invitation_date_row">
                <div class="mayhen_invitation_date_wing mayhen_invitation_ladi_headline_wedding_day" data-sync-target="wedding_weekday">{{ $weddingWeekday }}</div>
                <div class="mayhen_invitation_date_daynum" data-sync-target="wedding_day">{{ $weddingDay }}</div>
                <div class="mayhen_invitation_date_wing">
                    <span data-sync-target="wedding_month">{{ $weddingMonth }}</span> - <span data-sync-target="wedding_year">{{ $weddingYear }}</span>
                </div>
            </div>
            <p class="mayhen_invitation_ritual_lunar mayhen_invitation_ladi_paragraph_lunar_calendar" data-sync-target="wedding_lunar_date">{{ $weddingLunarDate }}</p>
            <div class="mayhen_invitation_ritual_venue_box">
                <span class="mayhen_invitation_ritual_venue_label">HÔN LỄ ĐƯỢC CỬ HÀNH TẠI</span>
                <span class="mayhen_invitation_ritual_venue_name mayhen_invitation_ceremony_groom_address" data-sync-target="ceremony_venue">{{ $ceremonyVenue }}</span>
            </div>
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 3: SONG THÂN & ĐÔI UYÊN ƯƠNG ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_parents mayhen_invitation_couple"
             data-mayhen-section="groom_family,bride_family">

        {{-- Trích dẫn tình yêu --}}
        <blockquote class="mayhen_invitation_couple_quote mayhen_invitation_quote_text mayhen_invitation_anim mayhen_invitation_anim--fadeInUp"
                    data-sync-target="couple_quote">{!! nl2br(e($coupleQuote)) !!}</blockquote>

        {{-- Chú Rể --}}
        <div class="mayhen_invitation_profile_row">
            <div class="mayhen_invitation_groom_photo mayhen_invitation_anim mayhen_invitation_anim--fadeInLeft"
                 data-lightbox-item="true">
                <img src="{{ $groomPhotoUrl }}"
                     alt="{{ $groomName }}"
                     data-sync-target="groom_photo_url"
                     class="mayhen_invitation_ladi_image">
            </div>
            <div class="mayhen_invitation_profile_text mayhen_invitation_anim mayhen_invitation_anim--fadeInRight">
                <div class="mayhen_invitation_groom_role">Chú Rể</div>
                <h3 class="mayhen_invitation_groom_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                <p class="mayhen_invitation_groom_bio" data-sync-target="groom_bio">{{ $groomBio }}</p>
            </div>
        </div>

        {{-- Cô Dâu --}}
        <div class="mayhen_invitation_profile_row mayhen_invitation_profile_row--bride">
            <div class="mayhen_invitation_profile_text mayhen_invitation_anim mayhen_invitation_anim--fadeInLeft">
                <div class="mayhen_invitation_bride_role">Cô Dâu</div>
                <h3 class="mayhen_invitation_bride_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                <p class="mayhen_invitation_bride_bio" data-sync-target="bride_bio">{{ $brideBio }}</p>
            </div>
            <div class="mayhen_invitation_bride_photo mayhen_invitation_anim mayhen_invitation_anim--fadeInRight"
                 data-lightbox-item="true">
                <img src="{{ $bridePhotoUrl }}"
                     alt="{{ $brideName }}"
                     data-sync-target="bride_photo_url"
                     class="mayhen_invitation_ladi_image">
            </div>
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 4: ĐẾM NGƯỢC & LỊCH THÁNG ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_countdown_calendar mayhen_invitation_countdown mayhen_invitation_calendar"
             data-mayhen-section="countdown_calendar">

        <div class="mayhen_invitation_countdown_kicker">Save The Date</div>
        <h2 class="mayhen_invitation_countdown_title">Đếm Ngược Hôn Lễ</h2>

        {{-- Tờ lịch tháng --}}
        <div class="mayhen_invitation_calendar">
            <h3 class="mayhen_invitation_calendar_month_year" data-sync-target="calendar_month_year">
                Tháng {{ $weddingMonth }}/{{ $weddingYear }}
            </h3>
            <span style="display:none;" data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</span>
            <div class="mayhen_invitation_calendar_weekdays">
                <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
            </div>
            <div class="mayhen_invitation_calendar_body mayhen_invitation_calendar_grid"
                 data-sync-target="wedding_calendar"
                 data-wedding-year="{{ $weddingYear }}"
                 data-wedding-month="{{ $weddingMonth }}"
                 data-wedding-day="{{ $weddingDay }}"
                 data-heart-icon="{{ $resolveAsset('assets/icons/calendar-heart.svg') }}">
            </div>
        </div>

        {{-- Đồng hồ đếm ngược --}}
        <div class="mayhen_invitation_countdown_stage">
            <div class="mayhen_invitation_countdown mayhen_invitation_countdown_grid mayhen_invitation_anim mayhen_invitation_anim--fadeInRight"
                 data-countdown-target="{{ $countdownIso }}">
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

    {{-- ==================== SECTION 5: SỰ KIỆN & BẢN ĐỒ ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_events mayhen_invitation_maps"
             data-mayhen-section="wedding_event">

        <div class="mayhen_invitation_events_kicker">Events & Venue</div>
        <h2 class="mayhen_invitation_events_title">Thư Mời Tiệc Cưới</h2>

        {{-- Bộ 3 ảnh nghệ thuật --}}
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

        {{-- Kính mời --}}
        <div class="mayhen_invitation_guest_invite mayhen_invitation_guest_name">
            <h3 class="mayhen_invitation_events_kicker">TRÂN TRỌNG KÍNH MỜI</h3>
            <h3 class="mayhen_invitation_guest_invite__name" data-sync-target="guest_name">{{ $guestName }}</h3>
            <p>Tới dự buổi tiệc chung vui cùng gia đình chúng tôi</p>
        </div>

        {{-- Tiệc cưới Nhà Gái --}}
        <div class="mayhen_invitation_event_card_party mayhen_invitation_ceremony_bride_card mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <p class="mayhen_invitation_party_block__heading">TIỆC CƯỚI NHÀ GÁI</p>
            <p class="mayhen_invitation_party_time mayhen_invitation_ceremony_bride_time" data-sync-target="bride_party_time">{{ $bridePartyTime }}</p>
            <div class="mayhen_invitation_date_row">
                <div class="mayhen_invitation_date_wing mayhen_invitation_date_wing--black" data-sync-target="engagement_weekday">{{ $engagementWeekday }}</div>
                <div class="mayhen_invitation_date_daynum mayhen_invitation_date_daynum--black" data-sync-target="engagement_day">{{ $engagementDay }}</div>
                <div class="mayhen_invitation_date_wing mayhen_invitation_date_wing--black" data-sync-target="engagement_month_year">{{ $engagementMonthYear }}</div>
            </div>
            <p class="mayhen_invitation_party_block__lunar" data-sync-target="engagement_lunar_date">{{ $engagementLunarDate }}</p>
            <div class="mayhen_invitation_party_card">
                <p class="mayhen_invitation_party_card__label">Địa điểm tổ chức</p>
                <p class="mayhen_invitation_party_venue" data-sync-target="bride_party_venue">{{ $bridePartyVenue }}</p>
                <p class="mayhen_invitation_party_address mayhen_invitation_ceremony_bride_address" data-sync-target="bride_party_address">{{ $bridePartyAddress }}</p>
                <a href="{{ $bridePartyMapUrl }}" target="_blank" rel="noopener noreferrer"
                   class="mayhen_invitation_map_btn mayhen_invitation_map_toggle_btn"
                   data-sync-target="bride_party_map_url">Xem bản đồ</a>
            </div>
        </div>

        <img class="mayhen_invitation_party_divider mayhen_invitation_divider"
             src="{{ $resolveAsset('assets/images/ornament-divider-floral.webp') }}" alt="">

        {{-- Tiệc cưới Nhà Trai --}}
        <div class="mayhen_invitation_event_card_party mayhen_invitation_ceremony_groom_card mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <p class="mayhen_invitation_party_block__heading">TIỆC CƯỚI NHÀ TRAI</p>
            <p class="mayhen_invitation_party_time mayhen_invitation_ceremony_groom_time" data-sync-target="party_time">{{ $partyTime }}</p>
            <div class="mayhen_invitation_date_row">
                <div class="mayhen_invitation_date_wing mayhen_invitation_date_wing--black" data-sync-target="wedding_weekday">{{ $weddingWeekday }}</div>
                <div class="mayhen_invitation_date_daynum mayhen_invitation_date_daynum--black" data-sync-target="wedding_day">{{ $weddingDay }}</div>
                <div class="mayhen_invitation_date_wing mayhen_invitation_date_wing--black">
                    <span data-sync-target="wedding_month">{{ $weddingMonth }}</span> - <span data-sync-target="wedding_year">{{ $weddingYear }}</span>
                </div>
            </div>
            <p class="mayhen_invitation_party_block__lunar" data-sync-target="wedding_lunar_date">{{ $weddingLunarDate }}</p>
            <div class="mayhen_invitation_party_card">
                <p class="mayhen_invitation_party_card__label">Địa điểm tổ chức</p>
                <p class="mayhen_invitation_party_venue" data-sync-target="party_venue">{{ $partyVenue }}</p>
                <p class="mayhen_invitation_party_address mayhen_invitation_ceremony_groom_address" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>
                <a href="{{ $partyMapUrl }}" target="_blank" rel="noopener noreferrer"
                   class="mayhen_invitation_map_btn mayhen_invitation_map_toggle_btn"
                   data-sync-target="party_map_url">Xem bản đồ</a>
            </div>
        </div>

        {{-- Iframe bản đồ --}}
        <div class="mayhen_invitation_map_box">
            <iframe class="mayhen_invitation_map_iframe"
                    src="https://maps.google.com/maps?q={{ urlencode($eventPartyAddress) }}&output=embed"
                    width="100%" height="260" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade" title="Bản đồ chỉ đường"></iframe>
            <p class="mayhen_invitation_map_address_text" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 6: LỊCH TRÌNH HÔN LỄ (TIMELINE) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_timeline" data-mayhen-section="timeline">

        <div class="mayhen_invitation_timeline_kicker">Chương Trình Hôn Lễ</div>
        <h2 class="mayhen_invitation_timeline_title">Lịch Trình Buổi Tiệc</h2>

        <div class="mayhen_invitation_timeline_card">
            <div class="mayhen_invitation_timeline_list">
                @php
                    $timelineIcons = [
                        0 => '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/></svg>',
                        1 => '<svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
                        2 => '<svg viewBox="0 0 24 24"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>',
                        3 => '<svg viewBox="0 0 24 24"><path d="M21 6h-3.17L16 4H8L6.17 6H3a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2zm-9 13a5 5 0 1 1 5-5 5 5 0 0 1-5 5z"/></svg>',
                    ];
                @endphp
                @foreach($timelineList as $index => $item)
                    <div class="mayhen_invitation_timeline_item">
                        <div class="mayhen_invitation_timeline_time" data-sync-target="timeline.{{ $index }}.time">
                            {{ $item['time'] ?? '' }}
                        </div>
                        <div class="mayhen_invitation_timeline_col">
                            <div class="mayhen_invitation_timeline_dot" aria-hidden="true">
                                {!! $timelineIcons[$index % 4] !!}
                            </div>
                            @if(!$loop->last)
                                <div class="mayhen_invitation_timeline_line" aria-hidden="true"></div>
                            @endif
                        </div>
                        <div class="mayhen_invitation_timeline_text" data-sync-target="timeline.{{ $index }}.title">
                            {{ $item['title'] ?? '' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 7: QUY ĐỊNH TRANG PHỤC (DRESSCODE) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_dresscode" data-mayhen-section="dresscode">

        <div class="mayhen_invitation_dresscode_kicker">Dresscode</div>
        <h2 class="mayhen_invitation_dresscode_title">Gợi Ý Trang Phục</h2>

        <div class="mayhen_invitation_dresscode_card">
            <div class="mayhen_invitation_dresscode_icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" stroke-width="1.8">
                    <path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/>
                </svg>
            </div>

            <div class="mayhen_invitation_dresscode_swatches">
                @foreach($dresscodeItems as $hex)
                    @php
                        $cleanHex = trim($hex);
                        $cName = $dresscodeColorNames[strtolower($cleanHex)] ?? ($dresscodeColorNames[$cleanHex] ?? 'Trang Nhã');
                    @endphp
                    <div class="mayhen_invitation_dresscode_item">
                        <div class="mayhen_invitation_dresscode_circle"
                             style="background-color: {{ $cleanHex }};"
                             title="{{ $cName }}"></div>
                        <span class="mayhen_invitation_dresscode_label">{{ $cName }}</span>
                    </div>
                @endforeach
            </div>

            <p class="mayhen_invitation_dresscode_note" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 8: ALBUM HÌNH CƯỚI (8 PHOTOS) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_album_section mayhen_invitation_gallery" data-mayhen-section="album,gallery">

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

    {{-- ==================== SECTION 9: HỘP MỪNG CƯỚI & VIETQR ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_gift_section mayhen_invitation_gift" data-mayhen-section="gift">

        <div class="mayhen_invitation_gift_header">
            <img class="mayhen_invitation_gift_icon"
                 src="{{ $resolveAsset('assets/icons/gift-pouch.svg') }}" alt="Hộp Mừng Cưới">
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
                <p class="mayhen_invitation_qr_card__bank mayhen_invitation_gift_bank_info">
                    <span class="mayhen_invitation_gift_bank_name_groom" data-sync-target="groom_bank_name">{{ $groomBankName }}</span>:
                    <strong class="mayhen_invitation_gift_account_number_groom" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</strong>
                </p>
                <div style="font-size:11px; font-weight:700; color:var(--mayhen-invitation-text-charcoal); margin-top:2px;"
                     class="mayhen_invitation_gift_account_holder_groom"
                     data-sync-target="groom_account_name">{{ $groomAccountName }}</div>
                <button type="button" class="mayhen_invitation_qr_card__btn mayhen_invitation_copy_btn" data-copy="{{ $groomBankAccount }}" style="border:none; cursor:pointer;">
                    Sao chép STK
                </button>
            </div>

            {{-- QR Cô Dâu --}}
            <div class="mayhen_invitation_qr_card mayhen_invitation_gift_card_bride mayhen_invitation_anim mayhen_invitation_anim--fadeInRight">
                <h3 class="mayhen_invitation_qr_card__title mayhen_invitation_gift_role_bride">MỪNG CƯỚI ĐẾN<br>CÔ DÂU</h3>
                <div class="mayhen_invitation_qr_card__img_wrap mayhen_invitation_gift_qr_bride" data-lightbox-item="true">
                    <img src="{{ $qrBrideUrl }}" alt="QR Cô Dâu" data-sync-target="qr_bride_image">
                </div>
                <p class="mayhen_invitation_qr_card__bank mayhen_invitation_gift_bank_info">
                    <span class="mayhen_invitation_gift_bank_name_bride" data-sync-target="bride_bank_name">{{ $brideBankName }}</span>:
                    <strong class="mayhen_invitation_gift_account_number_bride" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</strong>
                </p>
                <div style="font-size:11px; font-weight:700; color:var(--mayhen-invitation-text-charcoal); margin-top:2px;"
                     class="mayhen_invitation_gift_account_holder_bride"
                     data-sync-target="bride_account_name">{{ $brideAccountName }}</div>
                <button type="button" class="mayhen_invitation_qr_card__btn mayhen_invitation_copy_btn" data-copy="{{ $brideBankAccount }}" style="border:none; cursor:pointer;">
                    Sao chép STK
                </button>
            </div>
        </div>
    </section>

    <div class="mayhen_invitation_divider" aria-hidden="true"></div>

    {{-- ==================== SECTION 10: TƯƠNG TÁC, RSVP, LƯU BÚT & CẢM ƠN ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_interactive_ending mayhen_invitation_rsvp mayhen_invitation_wishes mayhen_invitation_ending"
             data-mayhen-section="rsvp,wishes">

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

            <div class="mayhen_invitation_form_radio_box mayhen_invitation_rsvp_guest_of" role="radiogroup" aria-label="Khách của ai">
                <label class="mayhen_invitation_form_radio_label">
                    <input type="radio" name="guest_of" value="Khách của Chú Rể" checked>
                    <span>Khách của Chú Rể</span>
                </label>
                <label class="mayhen_invitation_form_radio_label">
                    <input type="radio" name="guest_of" value="Khách của Cô Dâu">
                    <span>Khách của Cô Dâu</span>
                </label>
            </div>

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
                <select name="guest_count" class="mayhen_invitation_form_select mayhen_invitation_rsvp_count" aria-label="Số lượng">
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
        <div class="mayhen_invitation_wishes">
            <div class="mayhen_invitation_wish_list">
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
        </div>

        {{-- Sổ ký tên — TUYỆT ĐỐI KHÔNG ĐẶT ẢNH NGƯỜI VÀO ĐÂY --}}
        <div class="mayhen_invitation_signature mayhen_invitation_anim mayhen_invitation_anim--fadeInUp">
            <h3 class="mayhen_invitation_signature_title">Ký Tên Lưu Bút</h3>
            <p class="mayhen_invitation_signature_sub">Hãy để lại chữ ký kỷ niệm chúc phúc cho hai vợ chồng</p>
            <div class="mayhen_invitation_signature_bg">
                {{-- Chỉ dùng CSS gradient + wax seal, tuyệt đối không ảnh người --}}
            </div>
            <div class="mayhen_invitation_signature_canvas_wrap">
                <canvas class="mayhen_invitation_signature_canvas"
                        width="330" height="160" aria-label="Khung vẽ chữ ký"></canvas>
            </div>
            <div class="mayhen_invitation_signature_controls">
                <button type="button" class="mayhen_invitation_sig_btn" data-sig-action="clear">Xóa</button>
                <button type="button" class="mayhen_invitation_sig_btn mayhen_invitation_signature_open_btn" data-sig-action="preview">Xem trước</button>
                <button type="button" class="mayhen_invitation_sig_btn mayhen_invitation_sig_btn--primary mayhen_invitation_signature_save_btn" data-sig-action="save">Lưu chữ ký</button>
            </div>
            <p class="mayhen_invitation_sig_status mayhen_invitation_status_msg" role="status" aria-live="polite"></p>
            <div class="mayhen_invitation_signature_preview_list" aria-label="Danh sách chữ ký đã lưu"></div>
        </div>

        {{-- Lời cảm ơn kết thiệp --}}
        <div class="mayhen_invitation_ending">
            <img class="mayhen_invitation_footer_ornament"
                 src="{{ $resolveAsset('assets/images/ornament-footer-floral.webp') }}" alt="">
            <p class="mayhen_invitation_ending_couple_name">
                <span data-sync-target="groom_name">{{ $groomName }}</span>
                &amp;
                <span data-sync-target="bride_name">{{ $brideName }}</span>
            </p>
            <div class="mayhen_invitation_ending_photo" data-lightbox-item="true">
                <div class="mayhen_invitation_footer_circle">
                    <img src="{{ $footerPhotoUrl }}"
                         alt="{{ $groomName }} & {{ $brideName }}"
                         data-sync-target="footer_photo_url">
                </div>
                <img class="mayhen_invitation_footer_thankyou_img"
                     src="{{ $resolveAsset('assets/images/ornament-thankyou-text.webp') }}"
                     alt="Thank You">
            </div>
            <h2 class="mayhen_invitation_ending_title" data-sync-target="ending_title">{{ $endingTitle }}</h2>
            <p class="mayhen_invitation_ending_text" data-sync-target="ending_text">{{ $endingText }}</p>
        </div>
    </section>

    {{-- ==================== LIGHTBOX MODAL ==================== --}}
    <div class="mayhen_invitation_modal_backdrop mayhen_invitation_lightbox" role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to">
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
