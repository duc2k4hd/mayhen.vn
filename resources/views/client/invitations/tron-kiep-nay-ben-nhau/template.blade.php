@extends('client.layouts.invitation')

@php
    // =========================================================================
    // TEMPLATE: Trọn Kiếp Này Bên Nhau (tron-kiep-nay-ben-nhau)
    // Phong cách: Quiet Luxury — Đỏ Son Hoàng Gia & Vàng Ánh Kim (Ruby Crimson & Antique Gold)
    // Chuẩn: 10 Section BEM mayhen_invitation_* | data-sync-target đầy đủ 100%
    // =========================================================================

    $slug      = 'tron-kiep-nay-ben-nhau';
    $isEmbed   = $isEmbed ?? request()->boolean('embed')
                          || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Trọn Kiếp Này Bên Nhau';

    // --- Hàm resolve asset an toàn tuyệt đối với fallback nội bộ (100% relative path chống CORS & 404) ---
    $resolveAsset = function (string $relativePath, ?string $overrideUrl = null) use ($slug): string {
        $cleanRelative = ltrim(preg_replace('#^/?mau-thiep/[^/]+/assets/#', '', $relativePath), '/');
        $cleanRelative = ltrim(preg_replace('#^assets/#', '', $cleanRelative), '/');
        $fallbackUrl   = "/mau-thiep/{$slug}/assets/{$cleanRelative}";

        $override = trim((string) ($overrideUrl ?? ''));
        if (str_starts_with($override, 'blob:')) {
            return $fallbackUrl;
        }
        if (!empty($override)) {
            if (str_starts_with($override, 'data:')) {
                return $override;
            }
            if (preg_match('#^/?(storage|uploads)/#', $override)) {
                return '/' . ltrim($override, '/');
            }
            if (preg_match('#^https?://#', $override) && !str_contains($override, 'localhost') && !str_contains($override, '127.0.0.1')) {
                return $override;
            }
            $cleanOverride = ltrim(preg_replace('#^/?mau-thiep/[^/]+/assets/#', '', $override), '/');
            $cleanOverride = ltrim(preg_replace('#^assets/#', '', $cleanOverride), '/');
            $localFile     = resource_path("views/client/invitations/{$slug}/assets/{$cleanOverride}");
            if (file_exists($localFile)) {
                return "/mau-thiep/{$slug}/assets/{$cleanOverride}";
            }
            // File override không tồn tại trong template này (ảnh từ template khác nạp nhầm) -> fallback về ảnh mặc định của template
            return $fallbackUrl;
        }
        return $fallbackUrl;
    };

    // --- Dữ liệu extra ---
    $extraData = $wedding->extra_data ?? [];
    foreach ($extraData as $key => $value) {
        if ($value === null && !in_array($key, ['gallery', 'timeline', 'dresscode_colors'], true) && !str_ends_with($key, '_url') && !str_ends_with($key, '_image')) {
            $extraData[$key] = '';
        }
    }

    // --- 1. TÊN CÔ DÂU CHÚ RỂ ---
    $groomName   = $wedding->groom_name ?? ($extraData['groom_name'] ?? 'Trần Quốc Hưng');
    $brideName   = $wedding->bride_name ?? ($extraData['bride_name'] ?? 'Âu Mỹ Anh');
    $coupleNames = $extraData['couple_names'] ?? ($groomName . ' & ' . $brideName);

    // --- Tiêu đề trang chuẩn Mây Hẹn ---
    $pageTitle = $isPreview
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name')
        : 'Thiệp mời cưới: "' . $groomName . ' & ' . $brideName . '" | ' . config('app.name');

    // --- 2. SECTION 1: HERO ---
    $ceremonyType = $extraData['ceremony_type'] ?? ($extraData['ceremony_title'] ?? 'Lễ Vu Quy');
    $heroSlogan   = $extraData['slogan'] ?? 'Hôn nhân là chuyện cả đời. Yêu người vừa ý, cưới người mình thương.';
    $heroPhotoUrl = $resolveAsset('assets/images/hero-couple.webp', $wedding->cover_photo_url ?? ($extraData['hero_photo_url'] ?? null));

    // --- 3. NGÀY GIỜ CƯỚI ---
    $weddingDate       = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2026, 8, 2, 9, 0, 0);
    $weddingDay        = $weddingDate->format('d');
    $weddingMonth      = $weddingDate->format('m');
    $weddingYear       = $weddingDate->format('Y');
    $weddingDateDigits = $weddingDate->format('d . m . Y');
    $weddingWeekday    = mb_strtoupper($weddingDate->locale('vi')->isoFormat('dddd'));
    $weddingLunarDate  = $extraData['wedding_lunar_date'] ?? '(Tức ngày 20 tháng 06 năm Bính Ngọ)';
    $calendarMonthYear = 'Tháng ' . $weddingMonth . ' / ' . $weddingYear;

    // Giờ đếm ngược
    $countdownTime = $extraData['event_target_time'] ?? ($extraData['countdown_time'] ?? '09:00');
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
    $storyTitle       = $extraData['story_title']    ?? 'Lời Ngỏ Tình Yêu';
    $storySubtitle    = $extraData['story_subtitle'] ?? 'Trọn Kiếp Này Bên Nhau';
    $coupleQuote      = $extraData['couple_quote']   ?? 'Hôn nhân là chuyện cả đời, Yêu người vừa ý, cưới người mình thương';
    $storyContent     = $extraData['story_content']  ?? 'Cảm ơn bạn vì đã luôn dành sự yêu thương và quan tâm đến vợ chồng mình! Chúng mình biết rằng cuộc sống bận rộn, nào là công việc, trách nhiệm gia đình và những lo toan thường ngày. Vì thế, sự trân trọng dành cho bạn càng lớn hơn nữa. Thật lòng, ngày Hạnh Phúc của chúng mình sẽ trở nên trọn vẹn hơn nếu có sự hiện diện của bạn cùng người thương.';
    $saveDatePhotoUrl = $resolveAsset('assets/images/countdown-veil.webp', $extraData['save_date_photo_url'] ?? null);

    if (! empty($personaMessage)) {
        $storyTitle = $personaMessage['title'] ?? $storyTitle;
        $storySubtitle = $personaMessage['subtitle'] ?? $storySubtitle;
        $storyContent = $personaMessage['content'] ?? $storyContent;
    }

    // --- 5. SECTION 3: GIA ĐÌNH & ĐÔI UYÊN ƯƠNG ---
    $groomFather      = $extraData['groom_father']          ?? ($wedding->groom_parents ?? 'Trần Hùng Anh');
    $groomMother      = $extraData['groom_mother']          ?? 'Võ Thị Thúy Vân';
    $groomParentsAddr = $extraData['groom_parents_address'] ?? ($extraData['groom_address'] ?? 'Ấp Đông Giang, X. Đông Bình II, H. Thới Lai, TP. Cần Thơ');
    $brideFather      = $extraData['bride_father']          ?? ($wedding->bride_parents ?? 'Âu Sới Tỷ');
    $brideMother      = $extraData['bride_mother']          ?? 'Nguyễn Thị Diệu Hiền';
    $brideParentsAddr = $extraData['bride_parents_address'] ?? ($extraData['bride_address'] ?? 'Ấp Bến Bào, Xã Vĩnh Lộc A, Huyện Hồng Dân, Tỉnh Bạc Liêu');

    $groomPhotoUrl    = $resolveAsset('assets/images/groom-portrait.webp', $extraData['groom_photo_url'] ?? null);
    $groomBio         = $extraData['groom_bio'] ?? 'Một chàng trai điềm đạm, chân thành và luôn sống trọn vẹn vì tình yêu thương gia đình.';
    $bridePhotoUrl    = $resolveAsset('assets/images/bride-portrait.webp', $extraData['bride_photo_url'] ?? null);
    $brideBio         = $extraData['bride_bio'] ?? 'Một người con gái dịu dàng, tinh tế, luôn mang đến nụ cười ấm áp và bến đỗ bình yên.';

    // --- 6. SECTION 5: SỰ KIỆN & BẢN ĐỒ ---
    $guestName         = isset($guest) && $guest ? $guest->name : request()->query('guest', request()->query('name', $invitation->guest_name ?? 'Quý Khách'));
    $guestSalutation   = isset($guest) && $guest ? $guest->salutation : 'Kính mời';
    $partyTime         = $extraData['party_time']          ?? '09:00';
    $partyVenue        = $extraData['party_venue']         ?? 'TƯ GIA NHÀ GÁI';
    $eventPartyAddress = $extraData['event_party_address'] ?? 'Ấp Bến Bào, Xã Vĩnh Lộc A, Huyện Hồng Dân, Tỉnh Bạc Liêu';

    $ceremonyTime      = $extraData['ceremony_time']       ?? '09:00';
    $ceremonyAddress   = $extraData['ceremony_address']    ?? 'Ấp Bến Bào, Xã Vĩnh Lộc A, Huyện Hồng Dân, Tỉnh Bạc Liêu';
    $ceremonyGroomTime = $extraData['ceremony_groom_time'] ?? '11:00';
    $ceremonyGroomAddr = $extraData['ceremony_groom_address'] ?? 'Ấp Đông Giang, X. Đông Bình II, H. Thới Lai, TP. Cần Thơ';

    $mapGoogleUrl      = $extraData['event_party_map_link'] ?? ($extraData['map_google_url'] ?? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($eventPartyAddress));

    $ceremonyPhoto1    = $resolveAsset('assets/images/ceremony-aodai-01.webp', $extraData['ceremony_photo_1'] ?? null);
    $ceremonyPhoto2    = $resolveAsset('assets/images/ceremony-aodai-02.webp', $extraData['ceremony_photo_2'] ?? null);
    $ceremonyPhoto3    = $resolveAsset('assets/images/ceremony-aodai-03.webp', $extraData['ceremony_photo_3'] ?? null);

    // --- 7. SECTION 6: TIMELINE ---
    $timelineDefault = [
        ['time' => '08:30', 'title' => 'Đón khách & Trà thân mật'],
        ['time' => '09:00', 'title' => 'Cử hành Lễ Vu Quy tại tư gia'],
        ['time' => '10:30', 'title' => 'Khai tiệc mừng tân hôn'],
        ['time' => '12:00', 'title' => 'Chụp ảnh kỷ niệm & Tiễn khách'],
    ];
    $timelineList = $extraData['timeline'] ?? $timelineDefault;
    if (!is_array($timelineList) || empty($timelineList)) {
        $timelineList = $timelineDefault;
    }

    // --- 8. SECTION 7: DRESSCODE ---
    $dresscodeColorsRaw = $extraData['dresscode_colors'] ?? '#C82333,#F5D38E,#FFFFFF,#2B2B2B';
    $dresscodeItems     = array_filter(array_map('trim', explode(',', (string) $dresscodeColorsRaw)));
    if (empty($dresscodeItems)) {
        $dresscodeItems = ['#C82333', '#F5D38E', '#FFFFFF', '#2B2B2B'];
    }
    $dresscodeNote = $extraData['dresscode_note'] ?? 'Khuyến khích quý khách diện trang phục trang nhã mang tông màu đỏ son, vàng champagne hoặc trắng để những khung hình kỷ niệm thêm phần rạng rỡ.';

    $dresscodeColorNames = [
        '#c82333' => 'Đỏ Son',
        '#8b0000' => 'Đỏ Rượu Vang',
        '#f5d38e' => 'Vàng Champagne',
        '#ffffff' => 'Trắng Tinh Khôi',
        '#2b2b2b' => 'Đen Quý Phái',
        '#eae6df' => 'Be Ấm',
        '#d9b4a7' => 'Hồng Tro',
        '#748e8a' => 'Xanh Xô Thơm',
        '#1b2a4a' => 'Xanh Đêm',
        '#fce39e' => 'Vàng Ánh Kim',
        '#0c0c0c' => 'Đen Tuyền',
    ];

    // --- 9. SECTION 8: ALBUM 10 ẢNH CƯỚI TIÊU CHUẨN ---
    $defaultGalleryList = [
        0 => 'assets/images/hero-couple.webp',
        1 => 'assets/images/ceremony-aodai-01.webp',
        2 => 'assets/images/countdown-veil.webp',
        3 => 'assets/images/ceremony-aodai-02.webp',
        4 => 'assets/images/gallery-05.webp',
        5 => 'assets/images/gallery-06.webp',
        6 => 'assets/images/ceremony-aodai-03.webp',
        7 => 'assets/images/groom-portrait.webp',
        8 => 'assets/images/bride-portrait.webp',
        9 => 'assets/images/hero-couple.webp',
    ];
    $rawGallery = (isset($wedding) && isset($wedding->extra_data['gallery'])) ? $wedding->extra_data['gallery'] : ($extraData['gallery'] ?? []);
    $galleryPhotos = [];
    for ($g = 0; $g < 10; $g++) {
        $defaultPath = $defaultGalleryList[$g] ?? 'assets/images/hero-couple.webp';
        $img = !empty($rawGallery[$g]) ? $rawGallery[$g] : ($extraData['gallery_photo_' . ($g + 1)] ?? null);
        $galleryPhotos[$g] = $resolveAsset($defaultPath, $img);
    }

    // --- 10. SECTION 9: HỘP MỪNG CƯỚI ---
    $groomBankName    = $extraData['groom_bank_name']    ?? 'Vietcombank - CN Cần Thơ';
    $groomBankAccount = $extraData['groom_bank_account'] ?? '1029384756';
    $groomAccountName = $extraData['groom_account_name'] ?? 'TRAN QUOC HUNG';
    $qrGroomUrl       = $resolveAsset('assets/images/qr-groom.webp', $extraData['qr_groom_image'] ?? null);

    $brideBankName    = $extraData['bride_bank_name']    ?? 'Techcombank - CN Bạc Liêu';
    $brideBankAccount = $extraData['bride_bank_account'] ?? '190345678901';
    $brideAccountName = $extraData['bride_account_name'] ?? 'AU MY ANH';
    $qrBrideUrl       = $resolveAsset('assets/images/qr-bride.webp', $extraData['qr_bride_image'] ?? null);

    // --- 11. SECTION 10: TƯƠNG TÁC & LỜI CẢM ƠN ---
    $endingTitle    = $extraData['thank_you_title'] ?? 'Thank You!';
    $endingText     = $extraData['thank_you_quote'] ?? 'Sự hiện diện của quý vị là niềm vinh hạnh lớn nhất cho gia đình chúng tôi!';
    $footerPhotoUrl = $resolveAsset('assets/images/ceremony-aodai-02.webp', $extraData['footer_photo_url'] ?? null);

    $musicUrl       = $resolveAsset('assets/audio/wedding-melody.mp3', $extraData['background_music_url'] ?? null);
    $rsvpEndpoint   = $rsvpSubmitUrl ?? '';
    $wishEndpoint   = $wishSubmitUrl ?? '';
    $sigEndpoint    = '';
    $cssVersion     = '2.0.0';
    $jsVersion      = '2.0.0';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ route('templates.asset', ['slug' => $slug, 'path' => 'styles.css']) }}?v={{ $cssVersion }}">
@endpush

@section('content')
<div
    class="mayhen_invitation_root {{ $isEmbed ? 'mayhen_invitation_root--embed mayhen_invitation_root--opened' : 'mayhen_invitation_root--locked' }}"
    data-is-embed="{{ $isEmbed ? 'true' : 'false' }}"
    data-wedding-date="{{ $weddingDate->format('Y-m-d') }}"
    data-rsvp-endpoint="{{ $rsvpEndpoint }}"
    data-wishes-endpoint="{{ $wishEndpoint }}"
    data-signature-endpoint="{{ $sigEndpoint }}"
    data-csrf-token="{{ function_exists('csrf_token') ? csrf_token() : '' }}"
>
    {{-- =========================================================================
         GLOBAL: MUSIC DOCK & AUDIO ELEMENT
         ========================================================================= --}}
    <div class="mayhen_invitation_floating" aria-label="Điều khiển âm thanh">
        @if ($musicUrl)
        <audio preload="none" loop data-mayhen-audio src="{{ $musicUrl }}" data-sync-target="background_music_url"></audio>
        @else
        <audio preload="none" loop data-mayhen-audio></audio>
        @endif

        <button
            type="button"
            class="mayhen_invitation_floating__music_btn mayhen_invitation_music_btn"
            data-music-toggle
            aria-label="Bật hoặc tắt nhạc nền"
            aria-pressed="false"
        >
            <img
                src="{{ $resolveAsset('assets/icons/icon-music-off.svg') }}"
                alt="Tắt nhạc"
                class="mayhen_invitation_floating__music_icon"
                data-music-icon-off
                width="20"
                height="20"
            >
            <img
                src="{{ $resolveAsset('assets/icons/icon-music-on.svg') }}"
                alt="Bật nhạc"
                class="mayhen_invitation_floating__music_icon"
                data-music-icon-on
                hidden
                width="20"
                height="20"
            >
        </button>

        <div class="mayhen_invitation_floating__right_stack">
            <nav class="mayhen_invitation_floating__QuickMenu" data-quick-menu hidden aria-label="Menu điều hướng nhanh">
                <a href="#mayhen_section_events" class="mayhen_invitation_floating__menu_link">Thiệp mời</a>
                <a href="#mayhen_section_timeline" class="mayhen_invitation_floating__menu_link">Lịch trình</a>
                <a href="#mayhen_section_dresscode" class="mayhen_invitation_floating__menu_link">Trang phục</a>
                <a href="#mayhen_section_album" class="mayhen_invitation_floating__menu_link">Album ảnh</a>
                <a href="#mayhen_section_gift" class="mayhen_invitation_floating__menu_link">Mừng cưới</a>
                <a href="#mayhen_section_interactive" class="mayhen_invitation_floating__menu_link">Lưu bút &amp; RSVP</a>
            </nav>

            <button
                type="button"
                class="mayhen_invitation_floating__ctrl_btn"
                data-menu-toggle
                aria-label="Mở menu điều hướng"
                aria-expanded="false"
            >
                ☰
            </button>
            <button
                type="button"
                class="mayhen_invitation_floating__ctrl_btn"
                data-scroll-top
                aria-label="Cuộn lên đầu trang"
            >
                ↑
            </button>
        </div>

        @if (!$isEmbed)
        <div class="mayhen_invitation_falling_layer" aria-hidden="true">
            <span class="mayhen_invitation_falling_item mayhen_invitation_falling_item--1">❤</span>
            <span class="mayhen_invitation_falling_item mayhen_invitation_falling_item--2">❅</span>
            <span class="mayhen_invitation_falling_item mayhen_invitation_falling_item--3">❤</span>
            <span class="mayhen_invitation_falling_item mayhen_invitation_falling_item--4">❅</span>
            <span class="mayhen_invitation_falling_item mayhen_invitation_falling_item--5">❤</span>
        </div>
        @endif
    </div>

    {{-- =========================================================================
         MÀN MỞ THIỆP: CÁNH CỬA ĐỎ SONG HỶ
         Tự động ẩn hoàn toàn khi $isEmbed === true
         ========================================================================= --}}
    @if (!$isEmbed)
    <section class="mayhen_invitation_opening" aria-label="Màn hình mở thiệp cưới" data-mayhen-section="opening">
        <div class="mayhen_invitation_opening__door mayhen_invitation_opening__door--left" aria-hidden="true"></div>
        <div class="mayhen_invitation_opening__door mayhen_invitation_opening__door--right" aria-hidden="true"></div>

        <div class="mayhen_invitation_opening__center">
            <p class="mayhen_invitation_opening__subtitle">THIỆP MỜI CƯỚI</p>
            <h1 class="mayhen_invitation_opening__couple" data-sync-target="couple_names">
                <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
            </h1>

            <div class="mayhen_invitation_opening__emblem_wrap">
                <img
                    src="{{ $resolveAsset('assets/icons/double-happiness-emblem.svg') }}"
                    alt="Biểu tượng Song Hỷ"
                    class="mayhen_invitation_opening__emblem"
                    width="180"
                    height="216"
                >
            </div>

            <button
                type="button"
                class="mayhen_invitation_open_btn"
                aria-label="Chạm để mở thiệp"
            >
                <span class="mayhen_invitation_open_btn__icon" aria-hidden="true">✉</span>
                <span class="mayhen_invitation_open_btn__text">Chạm để mở thiệp</span>
            </button>

            <p class="mayhen_invitation_opening__music_hint">Nhấn vào đây để phát nhạc 🎵</p>
            <p class="mayhen_invitation_opening__date" data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</p>
        </div>
    </section>
    @endif

    {{-- =========================================================================
         MAIN INVITATION CANVAS (MAX-WIDTH: 480PX — FLOW LAYOUT 100%)
         ========================================================================= --}}
    <main class="mayhen_invitation_canvas mayhen_invitation_ladi_wraper" id="mayhen_invitation_top">

        {{-- ==================== SECTION 1: TRANG BÌA CHÍNH (HERO COVER) ==================== --}}
        <section class="mayhen_invitation_hero mayhen_invitation_reveal" data-mayhen-section="hero,couple">
            <div class="mayhen_invitation_hero__frame">
                <div class="mayhen_invitation_hero__photo_box mayhen_invitation_hero_media">
                    <img
                        src="{{ $heroPhotoUrl }}"
                        alt="{{ $coupleNames }}"
                        class="mayhen_invitation_hero__photo mayhen_invitation_ladi_image"
                        data-sync-target="cover_photo_url"
                        width="800"
                        height="1200"
                    >
                    <div class="mayhen_invitation_hero__overlay">
                        <p class="mayhen_invitation_hero__badge">Happy Wedding</p>
                        <h2 class="mayhen_invitation_hero__names mayhen_invitation_ladi_headline_name_groom" data-sync-target="couple_names">
                            <span data-sync-target="groom_name">{{ $groomName }}</span>
                            <span class="mayhen_invitation_hero_amp">&amp;</span>
                            <span data-sync-target="bride_name">{{ $brideName }}</span>
                        </h2>
                        <div class="mayhen_invitation_hero__meta">
                            <span class="mayhen_invitation_hero__ceremony mayhen_invitation_ladi_headline_ceremony_type" data-sync-target="ceremony_type">{{ $ceremonyType }}</span>
                            <span class="mayhen_invitation_hero__divider" aria-hidden="true">|</span>
                            <span class="mayhen_invitation_hero__weekday" data-sync-target="wedding_weekday">{{ $weddingWeekday }}</span>
                        </div>
                        <p class="mayhen_invitation_hero__date mayhen_invitation_ladi_headline_wedding_day" data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</p>
                        <p class="mayhen_invitation_hero__lunar mayhen_invitation_ladi_paragraph_lunar_calendar" data-sync-target="wedding_lunar_date">{{ $weddingLunarDate }}</p>
                    </div>
                </div>
            </div>

            <div class="mayhen_invitation_hero__quote_band">
                <p class="mayhen_invitation_hero__quote_line mayhen_invitation_ladi_paragraph_slogan" data-sync-target="slogan">{{ $heroSlogan }}</p>
                <img
                    src="{{ $resolveAsset('assets/icons/floral-line-art.svg') }}"
                    alt=""
                    aria-hidden="true"
                    class="mayhen_invitation_hero__ornament"
                    width="180"
                    height="45"
                >
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== SECTION 2: LỜI NGỎ & CHUYỆN TÌNH YÊU (LOVE STORY & QUOTE) ==================== --}}
        <section class="mayhen_invitation_story mayhen_invitation_quote mayhen_invitation_loingo mayhen_invitation_reveal"
                 data-mayhen-section="story,couple">
            <div class="mayhen_invitation_loingo__card">
                <img
                    src="{{ $resolveAsset('assets/icons/floral-line-art.svg') }}"
                    alt=""
                    aria-hidden="true"
                    class="mayhen_invitation_loingo__ornament"
                    width="180"
                    height="45"
                >
                <div class="mayhen_invitation_story_kicker">Love Story</div>
                <h2 class="mayhen_invitation_loingo__title mayhen_invitation_ladi_headline_story" data-sync-target="story_title">{{ $storyTitle }}</h2>
                <h3 class="mayhen_invitation_story_subtitle" data-sync-target="story_subtitle">{{ $storySubtitle }}</h3>

                {{-- Khung vòm nghệ thuật ảnh kỷ niệm --}}
                <div class="mayhen_invitation_story__arch_wrap">
                    <img
                        src="{{ $saveDatePhotoUrl }}"
                        alt="Ảnh kỷ niệm tình yêu"
                        class="mayhen_invitation_story_img"
                        data-sync-target="save_date_photo_url"
                        loading="lazy"
                        width="600"
                        height="800"
                    >
                </div>

                <blockquote class="mayhen_invitation_quote_text" data-sync-target="couple_quote">
                    "{{ $coupleQuote }}"
                </blockquote>

                <div class="mayhen_invitation_loingo__body">
                    <p class="mayhen_invitation_story_content" data-sync-target="story_content">
                        {{ $storyContent }}
                    </p>
                </div>

                <div class="mayhen_invitation_monogram" data-mayhen-wax-monogram aria-hidden="true">
                    ✦ <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span> ✦
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== SECTION 3: SONG THÂN & ĐÔI UYÊN ƯƠNG (FAMILY & COUPLE) ==================== --}}
        <section class="mayhen_invitation_family mayhen_invitation_parents mayhen_invitation_couple mayhen_invitation_reveal"
                 aria-label="Thông tin Hai Bên Gia Đình" data-mayhen-section="parents,groom_family,bride_family">
            <div class="mayhen_invitation_family_kicker">Hai Bên Gia Đình</div>
            <h2 class="mayhen_invitation_family_title">Song Thân &amp; Đôi Uyên Ương</h2>

            {{-- Dòng Nhà Trai --}}
            <div class="mayhen_invitation_family__row mayhen_invitation_family__row--groom">
                <div class="mayhen_invitation_family__photo_wrap mayhen_invitation_family__photo_wrap--groom">
                    <img
                        src="{{ $groomPhotoUrl }}"
                        alt="Chú Rể {{ $groomName }}"
                        class="mayhen_invitation_family__photo mayhen_invitation_groom_photo"
                        data-sync-target="groom_photo_url"
                        loading="lazy"
                        width="600"
                        height="800"
                    >
                </div>
                <div class="mayhen_invitation_family__info mayhen_invitation_family__info--groom">
                    <h3 class="mayhen_invitation_family__house_title mayhen_invitation_family_groom_title">Nhà Trai</h3>
                    <p class="mayhen_invitation_family__parent">
                        <span class="mayhen_invitation_family__parent_label">Bố :</span>
                        <strong class="mayhen_invitation_family_groom_father" data-sync-target="groom_father">{{ $groomFather }}</strong>
                    </p>
                    <p class="mayhen_invitation_family__parent">
                        <span class="mayhen_invitation_family__parent_label">Mẹ :</span>
                        <strong class="mayhen_invitation_family_groom_mother" data-sync-target="groom_mother">{{ $groomMother }}</strong>
                    </p>
                    <p class="mayhen_invitation_family__address">
                        <span>Tư gia :</span>
                        <span class="mayhen_invitation_family_groom_address" data-sync-target="groom_parents_address">{{ $groomParentsAddr }}</span>
                    </p>
                    <div class="mayhen_invitation_family__person_box">
                        <span class="mayhen_invitation_family__role mayhen_invitation_groom_role">Chú Rể</span>
                        <p class="mayhen_invitation_family__person_name mayhen_invitation_groom_name" data-sync-target="groom_name">{{ $groomName }}</p>
                    </div>
                    <p class="mayhen_invitation_groom_bio" data-sync-target="groom_bio">{{ $groomBio }}</p>
                </div>
            </div>

            {{-- Dòng Nhà Gái --}}
            <div class="mayhen_invitation_family__row mayhen_invitation_family__row--bride">
                <div class="mayhen_invitation_family__info mayhen_invitation_family__info--bride">
                    <h3 class="mayhen_invitation_family__house_title mayhen_invitation_family_bride_title">Nhà Gái</h3>
                    <p class="mayhen_invitation_family__parent">
                        <span class="mayhen_invitation_family__parent_label">Bố :</span>
                        <strong class="mayhen_invitation_family_bride_father" data-sync-target="bride_father">{{ $brideFather }}</strong>
                    </p>
                    <p class="mayhen_invitation_family__parent">
                        <span class="mayhen_invitation_family__parent_label">Mẹ :</span>
                        <strong class="mayhen_invitation_family_bride_mother" data-sync-target="bride_mother">{{ $brideMother }}</strong>
                    </p>
                    <p class="mayhen_invitation_family__address">
                        <span>Tư gia :</span>
                        <span class="mayhen_invitation_family_bride_address" data-sync-target="bride_parents_address">{{ $brideParentsAddr }}</span>
                    </p>
                    <div class="mayhen_invitation_family__person_box">
                        <span class="mayhen_invitation_family__role mayhen_invitation_bride_role">Cô Dâu</span>
                        <p class="mayhen_invitation_family__person_name mayhen_invitation_bride_name" data-sync-target="bride_name">{{ $brideName }}</p>
                    </div>
                    <p class="mayhen_invitation_bride_bio" data-sync-target="bride_bio">{{ $brideBio }}</p>
                </div>
                <div class="mayhen_invitation_family__photo_wrap mayhen_invitation_family__photo_wrap--bride">
                    <img
                        src="{{ $bridePhotoUrl }}"
                        alt="Cô Dâu {{ $brideName }}"
                        class="mayhen_invitation_family__photo mayhen_invitation_bride_photo"
                        data-sync-target="bride_photo_url"
                        loading="lazy"
                        width="600"
                        height="800"
                    >
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== SECTION 4: ĐẾM NGƯỢC & LỊCH THÁNG (COUNTDOWN & CALENDAR) ==================== --}}
        <section class="mayhen_invitation_countdown_calendar mayhen_invitation_countdown mayhen_invitation_calendar mayhen_invitation_reveal"
                 aria-label="Đếm ngược & Lịch cưới" data-mayhen-section="countdown_calendar,couple">
            <div class="mayhen_invitation_countdown_kicker">Save The Date</div>
            <h2 class="mayhen_invitation_countdown_title">Đếm Ngược &amp; Lịch Cưới</h2>

            {{-- Khung Tờ Lịch Tháng --}}
            <div class="mayhen_invitation_calendar__card">
                <header class="mayhen_invitation_calendar__header">
                    <h3 class="mayhen_invitation_calendar__title mayhen_invitation_calendar_month_year" data-sync-target="calendar_month_year">
                        {{ $calendarMonthYear }}
                    </h3>
                </header>

                <div class="mayhen_invitation_calendar__weekdays" aria-hidden="true">
                    <span>Thứ 2</span>
                    <span>Thứ 3</span>
                    <span>Thứ 4</span>
                    <span>Thứ 5</span>
                    <span>Thứ 6</span>
                    <span>Thứ 7</span>
                    <span>CN</span>
                </div>

                <div
                    class="mayhen_invitation_calendar__grid"
                    data-sync-target="wedding_calendar"
                    role="grid"
                    aria-label="Lịch tháng đám cưới"
                >
                    {{-- Render động bởi window.renderMayhenWeddingCalendar() --}}
                </div>
            </div>

            {{-- Đồng Hồ Đếm Ngược Hình Trái Tim Tinh Tế --}}
            <div class="mayhen_invitation_countdown__card" data-countdown="{{ $countdownIso }}">
                <div class="mayhen_invitation_countdown__bg_wrap">
                    <img
                        src="{{ $saveDatePhotoUrl }}"
                        alt=""
                        aria-hidden="true"
                        class="mayhen_invitation_countdown__bg_img"
                        loading="lazy"
                    >
                </div>
                <div class="mayhen_invitation_countdown__glass_card">
                    <p class="mayhen_invitation_countdown__heading">CÙNG ĐẾM NGƯỢC THỜI GIAN</p>
                    <h3 class="mayhen_invitation_countdown__couple" data-sync-target="couple_names">
                        <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </h3>

                    <div class="mayhen_invitation_countdown__hearts" role="timer" aria-live="polite">
                        <div class="mayhen_invitation_countdown__item">
                            <div class="mayhen_invitation_countdown__heart_box">
                                <span class="mayhen_invitation_countdown__num mayhen_invitation_cd_day" data-countdown-days>00</span>
                            </div>
                            <span class="mayhen_invitation_countdown__label">Ngày</span>
                        </div>
                        <div class="mayhen_invitation_countdown__item">
                            <div class="mayhen_invitation_countdown__heart_box">
                                <span class="mayhen_invitation_countdown__num mayhen_invitation_cd_hour" data-countdown-hours>00</span>
                            </div>
                            <span class="mayhen_invitation_countdown__label">Giờ</span>
                        </div>
                        <div class="mayhen_invitation_countdown__item">
                            <div class="mayhen_invitation_countdown__heart_box">
                                <span class="mayhen_invitation_countdown__num mayhen_invitation_cd_min" data-countdown-minutes>00</span>
                            </div>
                            <span class="mayhen_invitation_countdown__label">Phút</span>
                        </div>
                        <div class="mayhen_invitation_countdown__item">
                            <div class="mayhen_invitation_countdown__heart_box">
                                <span class="mayhen_invitation_countdown__num mayhen_invitation_cd_sec" data-countdown-seconds>00</span>
                            </div>
                            <span class="mayhen_invitation_countdown__label">Giây</span>
                        </div>
                    </div>

                    <p class="mayhen_invitation_countdown__footer_script">Happy Wedding</p>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== SECTION 5: THÔNG TIN SỰ KIỆN & BẢN ĐỒ (EVENTS & MAPS) ==================== --}}
        <section class="mayhen_invitation_events mayhen_invitation_maps mayhen_invitation_ceremony mayhen_invitation_reveal"
                 id="mayhen_section_events" data-mayhen-section="events,wedding_event">
            <div class="mayhen_invitation_events_kicker">Events &amp; Venue</div>
            <h2 class="mayhen_invitation_ceremony__script_title mayhen_invitation_events_title">Thư Mời Tiệc Cưới</h2>
            <p class="mayhen_invitation_ceremony__subtitle">
                Sự hiện diện của Quý Khách là niềm vinh hạnh cho gia đình của chúng tôi!
            </p>

            {{-- Bộ 3 ảnh vòm nghệ thuật áo dài đỏ Song Hỷ --}}
            <div class="mayhen_invitation_ceremony__triptych">
                <div class="mayhen_invitation_ceremony__arch_item" data-lightbox-item="true">
                    <img
                        src="{{ $ceremonyPhoto1 }}"
                        alt="Ảnh lễ cưới 1"
                        class="mayhen_invitation_ceremony__arch_img"
                        data-sync-target="ceremony_photo_1"
                        loading="lazy"
                        width="600"
                        height="900"
                    >
                </div>
                <div class="mayhen_invitation_ceremony__arch_item mayhen_invitation_ceremony__arch_item--center" data-lightbox-item="true">
                    <img
                        src="{{ $ceremonyPhoto2 }}"
                        alt="Ảnh lễ cưới 2"
                        class="mayhen_invitation_ceremony__arch_img"
                        data-sync-target="ceremony_photo_2"
                        loading="lazy"
                        width="600"
                        height="900"
                    >
                </div>
                <div class="mayhen_invitation_ceremony__arch_item" data-lightbox-item="true">
                    <img
                        src="{{ $ceremonyPhoto3 }}"
                        alt="Ảnh lễ cưới 3"
                        class="mayhen_invitation_ceremony__arch_img"
                        data-sync-target="ceremony_photo_3"
                        loading="lazy"
                        width="600"
                        height="900"
                    >
                </div>
            </div>

            {{-- Lời mời quan khách --}}
            <article class="mayhen_invitation_ceremony__card mayhen_invitation_event_card_party">
                <p class="mayhen_invitation_ceremony__invite_heading">TRÂN TRỌNG KÍNH MỜI</p>
                <p class="mayhen_invitation_ceremony__guest_label mayhen_invitation_guest_name" data-sync-target="guest_name">{{ $guestName }}</p>
                <p class="mayhen_invitation_ceremony__invite_desc">
                    Tới dự bữa tiệc thân mật cùng gia đình chúng tôi
                </p>

                <div class="mayhen_invitation_ceremony__venue_block">
                    <p class="mayhen_invitation_ceremony__venue_title">
                        TẠI: <span class="mayhen_invitation_party_venue" data-sync-target="party_venue">{{ $partyVenue }}</span>
                    </p>
                    <p class="mayhen_invitation_ceremony__venue_address mayhen_invitation_party_address" data-sync-target="event_party_address">
                        {{ $eventPartyAddress }}
                    </p>
                </div>

                <p class="mayhen_invitation_ceremony__time_line">
                    VÀO LÚC <span class="mayhen_invitation_party_time" data-sync-target="party_time">{{ $partyTime }}</span>
                </p>

                <div class="mayhen_invitation_ceremony__date_split">
                    <div class="mayhen_invitation_ceremony__date_col">
                        <span data-sync-target="wedding_weekday">{{ $weddingWeekday }}</span>
                    </div>
                    <div class="mayhen_invitation_ceremony__date_col mayhen_invitation_ceremony__date_col--day">
                        <strong data-sync-target="wedding_day">{{ $weddingDay }}</strong>
                    </div>
                    <div class="mayhen_invitation_ceremony__date_col">
                        <span data-sync-target="wedding_month">{{ $weddingMonth }}</span> - <span data-sync-target="wedding_year">{{ $weddingYear }}</span>
                    </div>
                </div>

                <p class="mayhen_invitation_ceremony__lunar" data-sync-target="wedding_lunar_date">
                    {{ $weddingLunarDate }}
                </p>
            </article>

            {{-- Hôn lễ cử hành tại tư gia --}}
            <article class="mayhen_invitation_ceremony__card mayhen_invitation_ceremony__card--formal">
                <p class="mayhen_invitation_ceremony__formal_label" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>
                <div class="mayhen_invitation_ceremony__couple_pair">
                    <span class="mayhen_invitation_ceremony__script_name" data-sync-target="bride_name">{{ $brideName }}</span>
                    <span class="mayhen_invitation_ceremony__amp">&amp;</span>
                    <span class="mayhen_invitation_ceremony__script_name" data-sync-target="groom_name">{{ $groomName }}</span>
                </div>
                <p class="mayhen_invitation_ceremony__time_line">
                    HÔN LỄ ĐƯỢC CỬ HÀNH VÀO LÚC <span data-sync-target="ceremony_time">{{ $ceremonyTime }}</span>
                </p>
                <p class="mayhen_invitation_ceremony__full_solar">
                    <span data-sync-target="wedding_weekday">{{ $weddingWeekday }}</span>, ngày
                    <strong data-sync-target="wedding_day">{{ $weddingDay }}</strong> tháng
                    <strong data-sync-target="wedding_month">{{ $weddingMonth }}</strong> năm
                    <strong data-sync-target="wedding_year">{{ $weddingYear }}</strong>
                </p>
                <p class="mayhen_invitation_ceremony__lunar" data-sync-target="wedding_lunar_date">
                    {{ $weddingLunarDate }}
                </p>
                <p class="mayhen_invitation_ceremony__ceremony_place" data-sync-target="ceremony_address">
                    HÔN LỄ ĐƯỢC CỬ HÀNH TẠI TƯ GIA: {{ $ceremonyAddress }}
                </p>
            </article>

            {{-- Bản đồ chỉ đường --}}
            <div class="mayhen_invitation_map__card mayhen_invitation_map_box">
                <h3 class="mayhen_invitation_map__venue_name" data-sync-target="party_venue">{{ $partyVenue }}</h3>
                <p class="mayhen_invitation_map__address mayhen_invitation_map_address_text" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>

                {{-- Vector Map Card an toàn không phụ thuộc external iframe --}}
                <div class="mayhen_invitation_map__preview" role="region" aria-label="Bản đồ vị trí tổ chức">
                    <svg class="mayhen_invitation_map__canvas_svg" viewBox="0 0 420 220" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect width="420" height="220" fill="#EBF3EC"/>
                        <path d="M0 45 Q130 60 240 40 T420 55" stroke="#D7E6D9" stroke-width="18" fill="none"/>
                        <path d="M55 0 Q95 110 75 220" stroke="#FFFFFF" stroke-width="10" fill="none"/>
                        <path d="M0 135 L420 110" stroke="#FFFFFF" stroke-width="12" fill="none"/>
                        <path d="M260 0 L210 220" stroke="#FFFFFF" stroke-width="9" fill="none"/>
                        <path d="M0 135 L420 110" stroke="#F6C86E" stroke-width="3" stroke-dasharray="10 6" fill="none"/>
                        <circle cx="222" cy="118" r="26" fill="#C82333" fill-opacity="0.16"/>
                        <circle cx="222" cy="118" r="7" fill="#C82333"/>
                    </svg>
                    <div class="mayhen_invitation_map__pin_badge">
                        <img src="{{ $resolveAsset('assets/icons/icon-map-pin.svg') }}" alt="" width="24" height="24">
                        <span data-sync-target="party_venue">{{ $partyVenue }}</span>
                    </div>
                </div>

                <a
                    href="{{ $mapGoogleUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mayhen_invitation_map__btn mayhen_invitation_map_toggle_btn"
                    data-sync-target="event_party_map_link"
                >
                    <span aria-hidden="true">💍</span> Xem trên Google Maps
                </a>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== SECTION 6: LỊCH TRÌNH TIỆC CƯỚI (TIMELINE) ==================== --}}
        <section class="mayhen_invitation_timeline mayhen_invitation_reveal" id="mayhen_section_timeline" data-mayhen-section="timeline">
            <div class="mayhen_invitation_timeline_kicker">Chương Trình Hôn Lễ</div>
            <h2 class="mayhen_invitation_timeline_title">Lịch Trình Buổi Tiệc</h2>

            <div class="mayhen_invitation_timeline_card">
                <div class="mayhen_invitation_timeline_list">
                    @php
                        $timelineIcons = [
                            0 => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/></svg>',
                            1 => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
                            2 => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>',
                            3 => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M21 6h-3.17L16 4H8L6.17 6H3a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2zm-9 13a5 5 0 1 1 5-5 5 5 0 0 1-5 5z"/></svg>',
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
        <section class="mayhen_invitation_dresscode mayhen_invitation_reveal" id="mayhen_section_dresscode" data-mayhen-section="dresscode">
            <div class="mayhen_invitation_dresscode_kicker">Dresscode</div>
            <h2 class="mayhen_invitation_dresscode_title">Gợi Ý Trang Phục</h2>

            <div class="mayhen_invitation_dresscode_card">
                <div class="mayhen_invitation_dresscode_icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" fill="none" stroke-width="1.8">
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

        {{-- ==================== SECTION 8: ALBUM 8-10 ẢNH CƯỚI (PHOTO ALBUM) ==================== --}}
        <section class="mayhen_invitation_gallery mayhen_invitation_reveal" id="mayhen_section_album" data-mayhen-section="album,gallery">
            <div class="mayhen_invitation_gallery__header">
                <div class="mayhen_invitation_gallery_kicker">Album</div>
                <h2 class="mayhen_invitation_gallery__title mayhen_invitation_gallery_title">Khoảnh Khắc Hạnh Phúc</h2>
                <div class="mayhen_invitation_gallery__savethedate" aria-hidden="true">
                    <span>S</span><span>a</span><span>v</span><span>e</span>
                    <em class="mayhen_invitation_gallery__the">the</em>
                    <span>d</span><span>a</span><span>t</span><span>e</span>
                </div>
            </div>

            <div class="mayhen_invitation_gallery__grid mayhen_invitation_gallery_wrap">
                <figure class="mayhen_invitation_gallery__item mayhen_invitation_gallery__item--tall mayhen_invitation_gallery_item" data-index="0">
                    <img
                        src="{{ $galleryPhotos[0] }}"
                        alt="Album hình cưới 01"
                        class="mayhen_invitation_gallery__img mayhen_invitation_gallery_img"
                        data-sync-target="gallery.0"
                        data-lightbox-src="{{ $galleryPhotos[0] }}"
                        loading="lazy"
                        width="800"
                        height="1200"
                    >
                </figure>
                <figure class="mayhen_invitation_gallery__item mayhen_invitation_gallery_item" data-index="1">
                    <img
                        src="{{ $galleryPhotos[1] }}"
                        alt="Album hình cưới 02"
                        class="mayhen_invitation_gallery__img mayhen_invitation_gallery_img"
                        data-sync-target="gallery.1"
                        data-lightbox-src="{{ $galleryPhotos[1] }}"
                        loading="lazy"
                        width="600"
                        height="900"
                    >
                </figure>
                <figure class="mayhen_invitation_gallery__item mayhen_invitation_gallery_item" data-index="2">
                    <img
                        src="{{ $galleryPhotos[2] }}"
                        alt="Album hình cưới 03"
                        class="mayhen_invitation_gallery__img mayhen_invitation_gallery_img"
                        data-sync-target="gallery.2"
                        data-lightbox-src="{{ $galleryPhotos[2] }}"
                        loading="lazy"
                        width="800"
                        height="1066"
                    >
                    <figcaption class="mayhen_invitation_gallery__caption">JUST THE WAY YOU ARE</figcaption>
                </figure>
                <figure class="mayhen_invitation_gallery__item mayhen_invitation_gallery__item--tall mayhen_invitation_gallery_item" data-index="3">
                    <img
                        src="{{ $galleryPhotos[3] }}"
                        alt="Album hình cưới 04"
                        class="mayhen_invitation_gallery__img mayhen_invitation_gallery_img"
                        data-sync-target="gallery.3"
                        data-lightbox-src="{{ $galleryPhotos[3] }}"
                        loading="lazy"
                        width="600"
                        height="900"
                    >
                </figure>
                <figure class="mayhen_invitation_gallery__item mayhen_invitation_gallery__item--extra mayhen_invitation_gallery_item" data-index="4">
                    <img
                        src="{{ $galleryPhotos[4] }}"
                        alt="Album hình cưới 05"
                        class="mayhen_invitation_gallery__img mayhen_invitation_gallery_img"
                        data-sync-target="gallery.4"
                        data-lightbox-src="{{ $galleryPhotos[4] }}"
                        loading="lazy"
                        width="600"
                        height="800"
                    >
                </figure>
                <figure class="mayhen_invitation_gallery__item mayhen_invitation_gallery__item--extra mayhen_invitation_gallery_item" data-index="5">
                    <img
                        src="{{ $galleryPhotos[5] }}"
                        alt="Album hình cưới 06"
                        class="mayhen_invitation_gallery__img mayhen_invitation_gallery_img"
                        data-sync-target="gallery.5"
                        data-lightbox-src="{{ $galleryPhotos[5] }}"
                        loading="lazy"
                        width="600"
                        height="800"
                    >
                </figure>
                <figure class="mayhen_invitation_gallery__item mayhen_invitation_gallery__item--extra mayhen_invitation_gallery_item" data-index="6">
                    <img
                        src="{{ $galleryPhotos[6] }}"
                        alt="Album hình cưới 07"
                        class="mayhen_invitation_gallery__img mayhen_invitation_gallery_img"
                        data-sync-target="gallery.6"
                        data-lightbox-src="{{ $galleryPhotos[6] }}"
                        loading="lazy"
                        width="600"
                        height="900"
                    >
                </figure>
                <figure class="mayhen_invitation_gallery__item mayhen_invitation_gallery__item--extra mayhen_invitation_gallery_item" data-index="7">
                    <img
                        src="{{ $galleryPhotos[7] }}"
                        alt="Album hình cưới 08"
                        class="mayhen_invitation_gallery__img mayhen_invitation_gallery_img"
                        data-sync-target="gallery.7"
                        data-lightbox-src="{{ $galleryPhotos[7] }}"
                        loading="lazy"
                        width="600"
                        height="800"
                    >
                </figure>
                <figure class="mayhen_invitation_gallery__item mayhen_invitation_gallery__item--extra mayhen_invitation_gallery_item" data-index="8">
                    <img
                        src="{{ $galleryPhotos[8] }}"
                        alt="Album hình cưới 09"
                        class="mayhen_invitation_gallery__img mayhen_invitation_gallery_img"
                        data-sync-target="gallery.8"
                        data-lightbox-src="{{ $galleryPhotos[8] }}"
                        loading="lazy"
                        width="600"
                        height="900"
                    >
                </figure>
                <figure class="mayhen_invitation_gallery__item mayhen_invitation_gallery__item--extra mayhen_invitation_gallery_item" data-index="9">
                    <img
                        src="{{ $galleryPhotos[9] }}"
                        alt="Album hình cưới 10"
                        class="mayhen_invitation_gallery__img mayhen_invitation_gallery_img"
                        data-sync-target="gallery.9"
                        data-lightbox-src="{{ $galleryPhotos[9] }}"
                        loading="lazy"
                        width="600"
                        height="800"
                    >
                </figure>
            </div>

            <div class="mayhen_invitation_gallery__actions">
                <button type="button" class="mayhen_invitation_gallery__more_btn" data-gallery-toggle>
                    XEM THÊM
                </button>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== SECTION 9: HỘP MỪNG CƯỚI & VIETQR ==================== --}}
        <section class="mayhen_invitation_gift mayhen_invitation_reveal" id="mayhen_section_gift" data-mayhen-section="gift">
            <div class="mayhen_invitation_gift__card">
                <div class="mayhen_invitation_gift_kicker">Mừng Cưới</div>
                <h2 class="mayhen_invitation_gift__title mayhen_invitation_gift_title">HỘP QUÀ MỪNG CƯỚI</h2>
                <p class="mayhen_invitation_gift__desc mayhen_invitation_gift_desc">
                    Cảm ơn tất cả các tình cảm mà các em, các bạn, các anh các chị đã dành cho hai vợ chồng ạ!
                </p>

                <button
                    type="button"
                    class="mayhen_invitation_gift__envelope_btn"
                    data-gift-toggle
                    aria-expanded="true"
                    aria-label="Đóng hoặc mở hộp quà mừng cưới"
                >
                    <img
                        src="{{ $resolveAsset('assets/icons/gift-envelope.svg') }}"
                        alt="Mở hộp quà mừng cưới"
                        class="mayhen_invitation_gift__envelope_img"
                        width="110"
                        height="142"
                    >
                </button>

                <div class="mayhen_invitation_gift__qr_grid" data-gift-panel>
                    {{-- QR Chú Rể --}}
                    <div class="mayhen_invitation_gift__qr_box mayhen_invitation_gift_card_groom">
                        <p class="mayhen_invitation_gift__qr_heading">MỪNG CƯỚI ĐẾN</p>
                        <h3 class="mayhen_invitation_gift__qr_role mayhen_invitation_gift_role_groom">CHÚ RỂ</h3>
                        <div class="mayhen_invitation_gift__qr_img_wrap mayhen_invitation_gift_qr_groom" data-lightbox-item="true">
                            <img
                                src="{{ $qrGroomUrl }}"
                                alt="QR Mừng cưới Chú Rể"
                                class="mayhen_invitation_gift__qr_img"
                                data-sync-target="qr_groom_image"
                                loading="lazy"
                                width="240"
                                height="240"
                            >
                        </div>
                        <p class="mayhen_invitation_gift__bank_name mayhen_invitation_gift_bank_name_groom" data-sync-target="groom_bank_name">{{ $groomBankName }}</p>
                        <p class="mayhen_invitation_gift__bank_person mayhen_invitation_gift_account_holder_groom" data-sync-target="groom_account_name">{{ $groomAccountName }}</p>
                        <p class="mayhen_invitation_gift__bank_account">
                            STK: <strong class="mayhen_invitation_gift_account_number_groom" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</strong>
                        </p>
                        <button type="button" class="mayhen_invitation_gift__copy_btn mayhen_invitation_copy_btn" data-copy="{{ $groomBankAccount }}">
                            Sao chép STK
                        </button>
                    </div>

                    {{-- QR Cô Dâu --}}
                    <div class="mayhen_invitation_gift__qr_box mayhen_invitation_gift_card_bride">
                        <p class="mayhen_invitation_gift__qr_heading">MỪNG CƯỚI ĐẾN</p>
                        <h3 class="mayhen_invitation_gift__qr_role mayhen_invitation_gift_role_bride">CÔ DÂU</h3>
                        <div class="mayhen_invitation_gift__qr_img_wrap mayhen_invitation_gift_qr_bride" data-lightbox-item="true">
                            <img
                                src="{{ $qrBrideUrl }}"
                                alt="QR Mừng cưới Cô Dâu"
                                class="mayhen_invitation_gift__qr_img"
                                data-sync-target="qr_bride_image"
                                loading="lazy"
                                width="240"
                                height="240"
                            >
                        </div>
                        <p class="mayhen_invitation_gift__bank_name mayhen_invitation_gift_bank_name_bride" data-sync-target="bride_bank_name">{{ $brideBankName }}</p>
                        <p class="mayhen_invitation_gift__bank_person mayhen_invitation_gift_account_holder_bride" data-sync-target="bride_account_name">{{ $brideAccountName }}</p>
                        <p class="mayhen_invitation_gift__bank_account">
                            STK: <strong class="mayhen_invitation_gift_account_number_bride" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</strong>
                        </p>
                        <button type="button" class="mayhen_invitation_gift__copy_btn mayhen_invitation_copy_btn" data-copy="{{ $brideBankAccount }}">
                            Sao chép STK
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== SECTION 10: TƯƠNG TÁC, RSVP, LƯU BÚT & CẢM ƠN ==================== --}}
        <section class="mayhen_invitation_interactive_ending mayhen_invitation_rsvp mayhen_invitation_wishes mayhen_invitation_reveal"
                 id="mayhen_section_interactive" data-mayhen-section="interactive,rsvp,wishes">

            {{-- 10.1 XÁC NHẬN THAM DỰ (RSVP FORM — TUYỆT ĐỐI KHÔNG ẢNH CẶP ĐÔI) --}}
            <div class="mayhen_invitation_rsvp__card" id="mayhen_section_rsvp">
                <img
                    src="{{ $resolveAsset('assets/icons/floral-line-art.svg') }}"
                    alt=""
                    aria-hidden="true"
                    class="mayhen_invitation_rsvp__ornament"
                    width="160"
                    height="40"
                >
                <h2 class="mayhen_invitation_rsvp__title">Xác Nhận Tham Dự</h2>
                <p class="mayhen_invitation_rsvp__desc">
                    Đám cưới sẽ trọn vẹn và ý nghĩa hơn khi có sự hiện diện và chúc phúc của bạn. Hãy xác nhận sự có mặt của bạn để vợ chồng chuẩn bị đón tiếp bạn chu đáo nhất nha ♥️
                </p>

                <form class="mayhen_invitation_rsvp__form mayhen_invitation_rsvp_form" data-mayhen-rsvp-form novalidate>
                    @csrf
                    <div class="mayhen_invitation_rsvp__field">
                        <label for="mayhen_rsvp_name" class="mayhen_invitation_rsvp__label">Tên khách mời *</label>
                        <input
                            type="text"
                            id="mayhen_rsvp_name"
                            name="guest_name"
                            class="mayhen_invitation_rsvp__input mayhen_invitation_rsvp_name_input"
                            placeholder="Nhập họ và tên của bạn..."
                            value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}"
                            required
                        >
                    </div>

                    <fieldset class="mayhen_invitation_rsvp__fieldset">
                        <legend class="mayhen_invitation_rsvp__legend">Trạng thái tham dự *</legend>
                        <label class="mayhen_invitation_rsvp__radio_label">
                            <input type="radio" name="attendance_status" value="attending" checked>
                            <span>Có, tôi sẽ đến</span>
                        </label>
                        <label class="mayhen_invitation_rsvp__radio_label">
                            <input type="radio" name="attendance_status" value="declined">
                            <span>Xin lỗi, tôi bận mất rồi</span>
                        </label>
                    </fieldset>

                    <div class="mayhen_invitation_rsvp__row">
                        <div class="mayhen_invitation_rsvp__field">
                            <label for="mayhen_rsvp_guests" class="mayhen_invitation_rsvp__label">Số người lớn</label>
                            <select id="mayhen_rsvp_guests" name="guest_count" class="mayhen_invitation_rsvp__select">
                                <option value="1">1 Người</option>
                                <option value="2">2 Người</option>
                                <option value="3">3 Người</option>
                                <option value="4">4 Người</option>
                                <option value="5">5 Người</option>
                            </select>
                        </div>
                        <div class="mayhen_invitation_rsvp__field">
                            <label for="mayhen_rsvp_children" class="mayhen_invitation_rsvp__label">Số trẻ em</label>
                            <select id="mayhen_rsvp_children" name="children_count" class="mayhen_invitation_rsvp__select">
                                <option value="0">0 Trẻ em</option>
                                <option value="1">1 Trẻ em</option>
                                <option value="2">2 Trẻ em</option>
                                <option value="3">3 Trẻ em</option>
                                <option value="4">4 Trẻ em</option>
                                <option value="5">5 Trẻ em</option>
                            </select>
                        </div>
                    </div>

                    <div class="mayhen_invitation_rsvp__field">
                        <label for="mayhen_rsvp_note" class="mayhen_invitation_rsvp__label">Lời nhắn gửi Dâu Rể (nếu có)</label>
                        <textarea
                            id="mayhen_rsvp_note"
                            name="message"
                            rows="2"
                            class="mayhen_invitation_rsvp__textarea"
                            placeholder="Ghi chú thêm về phương tiện hoặc lời nhắn..."
                        ></textarea>
                    </div>

                    <button type="submit" class="mayhen_invitation_rsvp__submit_btn mayhen_invitation_rsvp_submit_btn">
                        Xác Nhận Tham Dự
                    </button>

                    <div class="mayhen_invitation_rsvp__status" data-rsvp-status role="status" aria-live="polite"></div>
                </form>
            </div>

            {{-- 10.2 SỔ KÝ TÊN ĐIỆN TỬ (SIGNATURE PAD — TUYỆT ĐỐI KHÔNG ẢNH CẶP ĐÔI) --}}
            <div class="mayhen_invitation_signature__card mayhen_invitation_signature" id="mayhen_section_signature">
                <img
                    src="{{ $resolveAsset('assets/icons/floral-line-art.svg') }}"
                    alt=""
                    aria-hidden="true"
                    class="mayhen_invitation_signature__ornament"
                    width="160"
                    height="40"
                >
                <h2 class="mayhen_invitation_signature__title">Sổ Ký Tên Lưu Niệm</h2>
                <p class="mayhen_invitation_signature__desc">
                    Hãy để lại chữ ký lưu niệm của bạn trên sổ hồng điện tử của <span data-sync-target="couple_names">{{ $coupleNames }}</span> nhé!
                </p>

                <div class="mayhen_invitation_signature_bg">
                    {{-- Chỉ dùng CSS gradient + hoa văn cổ điển, tuyệt đối không ảnh người --}}
                </div>

                <div class="mayhen_invitation_signature__field">
                    <label for="mayhen_signer_name" class="mayhen_invitation_signature__label">Tên người ký *</label>
                    <input
                        type="text"
                        id="mayhen_signer_name"
                        class="mayhen_invitation_signature__input"
                        placeholder="Nhập tên của bạn trước khi ký..."
                        data-signature-name
                    >
                </div>

                <div class="mayhen_invitation_signature__pad_wrap">
                    <canvas
                        id="mayhen_signature_canvas"
                        class="mayhen_invitation_signature__canvas mayhen_invitation_signature_canvas"
                        width="400"
                        height="200"
                        aria-label="Khung vẽ chữ ký điện tử"
                    ></canvas>
                    <span class="mayhen_invitation_signature__placeholder" data-signature-placeholder>
                        Ký tên tại đây bằng ngón tay, chuột hoặc bút cảm ứng
                    </span>
                </div>

                <div class="mayhen_invitation_signature__controls">
                    <button type="button" class="mayhen_invitation_signature__btn_secondary" data-signature-clear>
                        Xóa chữ ký
                    </button>
                    <button type="button" class="mayhen_invitation_signature__btn_secondary mayhen_invitation_signature_open_btn" data-signature-preview-btn>
                        Xem trước
                    </button>
                    <button type="button" class="mayhen_invitation_signature__btn_primary mayhen_invitation_signature_save_btn" data-signature-save>
                        Lưu chữ ký
                    </button>
                </div>

                <div class="mayhen_invitation_signature__status" data-signature-status role="status" aria-live="polite"></div>

                <div class="mayhen_invitation_signature__preview_box" data-signature-preview-wrap hidden>
                    <p class="mayhen_invitation_signature__preview_label">Bản xem trước chữ ký của bạn:</p>
                    <img src="" alt="Chữ ký lưu niệm" class="mayhen_invitation_signature__preview_img" data-signature-preview-img>
                    <a
                        href="#"
                        download="chu-ky-luu-niem.png"
                        class="mayhen_invitation_signature__download_link"
                        data-signature-download
                    >
                        Tải chữ ký PNG
                    </a>
                </div>
            </div>

            {{-- 10.3 SỔ LƯU BÚT CHÚC PHÚC (WISHES) --}}
            <div class="mayhen_invitation_wishes__container mayhen_invitation_wishes" id="mayhen_section_wishes">
                <h2 class="mayhen_invitation_wishes__title">Gửi Lời Chúc Cho Dâu Rể</h2>
                <p class="mayhen_invitation_wishes__subtitle">
                    Cảm ơn tất cả tình cảm của cô dì chú bác, bạn bè và anh chị em đã dành cho hai vợ chồng ♥️
                </p>

                <form class="mayhen_invitation_wishes__form mayhen_invitation_wish_form" data-mayhen-wishes-form novalidate>
                    @csrf
                    <div class="mayhen_invitation_wishes__field">
                        <label for="mayhen_wish_name" class="mayhen_invitation_wishes__label">Họ và Tên của bạn *</label>
                        <input
                            type="text"
                            id="mayhen_wish_name"
                            name="guest_name"
                            class="mayhen_invitation_wishes__input mayhen_invitation_wish_name_input"
                            placeholder="Nhập họ và tên..."
                            value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}"
                            required
                        >
                    </div>

                    <div class="mayhen_invitation_wishes__field">
                        <label for="mayhen_wish_preset" class="mayhen_invitation_wishes__label">Lời chúc gợi ý</label>
                        <select id="mayhen_wish_preset" class="mayhen_invitation_wishes__select" data-wish-preset-select>
                            <option value="">-- Chọn lời chúc gợi ý --</option>
                            <option value="Chúc hai bạn mãi mãi hạnh phúc bên nhau ❤️💍">Chúc hai bạn mãi mãi hạnh phúc bên nhau ❤️💍</option>
                            <option value="Happy Wedding 🎉🎊 Chúc hai bạn trăm năm viên mãn, sớm đón thiên thần nhỏ nha! 👶✨">Happy Wedding 🎉🎊 Chúc hai bạn trăm năm viên mãn, sớm đón thiên thần nhỏ nha! 👶✨</option>
                            <option value="Trăm năm tình viên mãn 💕, bạc đầu nghĩa phu thê! Chúc hai bạn hạnh phúc mãi mãi!">Trăm năm tình viên mãn 💕, bạc đầu nghĩa phu thê! Chúc hai bạn hạnh phúc mãi mãi!</option>
                            <option value="Mong hai bạn sẽ luôn yêu thương, tôn trọng nhau 🥰. Hãy cùng nhau xây dựng một tổ ấm đầy ắp tiếng cười nhé! 🏡💕">Mong hai bạn sẽ luôn yêu thương, tôn trọng nhau 🥰. Hãy cùng nhau xây dựng một tổ ấm đầy ắp tiếng cười nhé! 🏡💕</option>
                        </select>
                    </div>

                    <div class="mayhen_invitation_wishes__field">
                        <label for="mayhen_wish_message" class="mayhen_invitation_wishes__label">Nội dung lời chúc *</label>
                        <textarea
                            id="mayhen_wish_message"
                            name="wish_message"
                            rows="3"
                            class="mayhen_invitation_wishes__textarea mayhen_invitation_wish_textarea"
                            placeholder="Nhập lời chúc tốt đẹp nhất tới Dâu Rể..."
                            required
                        ></textarea>
                    </div>

                    <button type="submit" class="mayhen_invitation_wishes__submit_btn mayhen_invitation_wish_submit_btn">
                        Gửi Lời Chúc Mừng
                    </button>

                    <div class="mayhen_invitation_wishes__status" data-wishes-status role="status" aria-live="polite"></div>
                </form>

                <div class="mayhen_invitation_wishes__feed mayhen_invitation_wish_list" data-wishes-feed aria-label="Danh sách lời chúc">
                    @foreach ($wishes ?? [] as $wish)
                    <article class="mayhen_invitation_wishes__bubble mayhen_invitation_wish_item" data-wish-id="{{ $wish->id }}">
                        <header class="mayhen_invitation_wishes__bubble_head">
                            <strong>{{ $wish->name }}</strong>
                        </header>
                        <p class="mayhen_invitation_wishes__bubble_text">{{ $wish->message }}</p>
                        @if (!empty($wish->reply))
                            <div class="mayhen_invitation_wish_reply" style="margin-top: 8px; padding: 8px 12px; background: rgba(197, 168, 128, 0.12); border-left: 2px solid #C5A880; border-radius: 4px; font-size: 0.85em; text-align: left;">
                                <div style="font-weight: 600; color: #9C7A4A; margin-bottom: 2px;">
                                    Phản hồi từ Cô Dâu &amp; Chú Rể:
                                </div>
                                <div style="font-style: italic; opacity: 0.9;">
                                    {{ $wish->reply }}
                                </div>
                            </div>
                        @endif
                    </article>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== CHÂN TRANG: LỜI CẢM ƠN KẾT THIỆP (THANK YOU) ==================== --}}
        <footer class="mayhen_invitation_footer mayhen_invitation_ending mayhen_invitation_reveal" data-mayhen-section="ending">
            <div class="mayhen_invitation_footer__photo_frame">
                <img
                    src="{{ $footerPhotoUrl }}"
                    alt="{{ $coupleNames }} - Thank You"
                    class="mayhen_invitation_footer__photo mayhen_invitation_ending_photo"
                    data-sync-target="footer_photo_url"
                    loading="lazy"
                    width="600"
                    height="900"
                >
                <div class="mayhen_invitation_footer__overlay">
                    <h2 class="mayhen_invitation_footer__title mayhen_invitation_ending_title" data-sync-target="thank_you_title">{{ $endingTitle }}</h2>
                    <p class="mayhen_invitation_footer__couple mayhen_invitation_ending_couple_name" data-sync-target="couple_names">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        &amp;
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </p>
                    <p class="mayhen_invitation_footer__text mayhen_invitation_ending_text" data-sync-target="thank_you_quote">
                        {{ $endingText }}
                    </p>
                </div>
            </div>
        </footer>
    </main>

    {{-- ==================== LIGHTBOX MODAL CHO ALBUM & QR ==================== --}}
    <div class="mayhen_invitation_lightbox" data-mayhen-lightbox hidden role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to">
        <button type="button" class="mayhen_invitation_lightbox__close" data-lightbox-close aria-label="Đóng ảnh">×</button>
        <img src="" alt="Ảnh phóng to" class="mayhen_invitation_lightbox__img" data-lightbox-img>
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

