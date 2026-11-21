@extends('client.layouts.invitation')

@php
    use App\Services\VietQrService;

    $tplSlug = $template->slug ?? 'xuan-sang-tinh-vua-chom';
    $assetBase = '/mau-thiep/' . $tplSlug . '/assets';

    $resolveAsset = static function (?string $path, string $fallback = '') use ($assetBase): string {
        $raw = trim((string) $path);
        if ($raw === '') {
            $raw = trim($fallback);
        }
        if ($raw === '') {
            return $assetBase . '/thumbnail.webp';
        }
        if (preg_match('#^https?://[^/]+(/mau-thiep/.*)$#i', $raw, $m)) {
            return $m[1];
        }
        if (preg_match('#^(https?://|/|blob:|data:)#i', $raw)) {
            return $raw;
        }
        $clean = preg_replace('#^assets/#', '', ltrim($raw, '/'));
        return $assetBase . '/' . $clean;
    };

    $wedding = $wedding ?? null;
    $rawExtraData = is_array($wedding?->extra_data ?? null) ? $wedding->extra_data : [];
    $extraData = [];
    foreach ($rawExtraData as $k => $v) {
        if (is_string($v)) {
            $trimmed = trim($v);
            if ($trimmed !== '') {
                $extraData[$k] = $trimmed;
            }
        } elseif ($v !== null) {
            $extraData[$k] = $v;
        }
    }

    $isPreview = !empty($isPreview) || request()->routeIs('templates.preview');
    $isEmbed = !empty($isEmbed)
        || request()->boolean('embed')
        || request()->boolean('in_modal')
        || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe'], true);
    $invCfgArr = isset($invitation) && is_array($invitation->custom_config) ? $invitation->custom_config : [];
    $isCuratedSeven = !empty($isCuratedSeven) || !empty($invCfgArr['is_curated_seven']) || !empty($invCfgArr['is_curated']);
    $autoOpened = $isEmbed || request()->boolean('opened') || ($isCuratedSeven && request()->boolean('crafting'));

    // 1. Couple & Guest Identity
    $groomName = trim((string) ($wedding?->groom_name ?? ($extraData['groom_name'] ?? 'Thế Bảo')));
    if ($groomName === '') {
        $groomName = 'Thế Bảo';
    }
    $brideName = trim((string) ($wedding?->bride_name ?? ($extraData['bride_name'] ?? 'Ngọc Ánh')));
    if ($brideName === '') {
        $brideName = 'Ngọc Ánh';
    }
    $coupleNames = $groomName . ' & ' . $brideName;

    $groomShortParts = preg_split('/\s+/', $groomName) ?: [$groomName];
    $brideShortParts = preg_split('/\s+/', $brideName) ?: [$brideName];
    $groomInitial = mb_strtoupper(mb_substr((string) end($groomShortParts), 0, 1));
    $brideInitial = mb_strtoupper(mb_substr((string) end($brideShortParts), 0, 1));

    $guestNameRaw = trim((string) ($guest?->name ?? ''));
    $displayGuestName = $guestNameRaw !== '' ? $guestNameRaw : 'Quý khách';

    // 2. Date & Lunar Date (Asia/Ho_Chi_Minh)
    $rawWeddingDate = trim((string) ($wedding?->wedding_date?->format('Y-m-d') ?? ($wedding?->wedding_date ?? ($extraData['wedding_date'] ?? '2026-05-03'))));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawWeddingDate)) {
        $rawWeddingDate = '2026-05-03';
    }
    $partyTime = trim((string) ($extraData['party_time'] ?? '18:00'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $partyTime)) {
        $partyTime = '18:00';
    }
    $targetTime = trim((string) ($extraData['event_target_time'] ?? $partyTime));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $targetTime)) {
        $targetTime = '18:00';
    }

    $dateTs = strtotime($rawWeddingDate . ' ' . $targetTime . ':00 +0700') ?: strtotime('2026-05-03 18:00:00 +0700');
    $wDay = date('d', $dateTs);
    $wDayNoZero = (string) (int) $wDay;
    $wMonth = date('m', $dateTs);
    $wMonthNoZero = (string) (int) $wMonth;
    $wYear = date('Y', $dateTs);

    $weekdaysVi = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    $wWeekday = $weekdaysVi[(int) date('w', $dateTs)] ?? 'Chủ Nhật';
    $wWeekdayUpper = mb_strtoupper($wWeekday, 'UTF-8');
    $weddingDateDigits = $wDay . ' . ' . $wMonth . ' . ' . $wYear;
    $openingDateLabel = $wDayNoZero . ' tháng ' . $wMonthNoZero . ', ' . $wYear;
    $targetIso = $rawWeddingDate . 'T' . $targetTime . ':00+07:00';

    $lunarDate = trim((string) ($extraData['wedding_lunar_date'] ?? '(Tức ngày 17 tháng 3 năm Bính Ngọ)'));
    $ceremonyType = trim((string) ($extraData['ceremony_type'] ?? 'TRÂN TRỌNG KÍNH MỜI DỰ LỄ THÀNH HÔN'));
    $slogan = trim((string) ($extraData['slogan'] ?? 'Dự tiệc chung vui cùng anh chị'));

    // 3. Portraits & Photos (100% Local Assets)
    $heroPhoto = $resolveAsset(
        $wedding?->cover_photo_url ?? ($extraData['hero_photo_url'] ?? ($extraData['cover_photo_url'] ?? '')),
        $assetBase . '/images/hero-couple.webp'
    );
    $saveDatePhoto = $resolveAsset($extraData['save_date_photo_url'] ?? '', $assetBase . '/images/save-date.webp');
    $groomPhoto = $resolveAsset($extraData['groom_photo_url'] ?? '', $assetBase . '/images/groom-portrait.webp');
    $bridePhoto = $resolveAsset($extraData['bride_photo_url'] ?? '', $assetBase . '/images/bride-portrait.webp');
    $footerPhoto = $resolveAsset($extraData['footer_photo_url'] ?? '', $assetBase . '/images/footer-couple.webp');

    // 4. Parents & Couple Details
    $groomFather = trim((string) ($extraData['groom_father'] ?? 'Nguyễn Gia Minh'));
    $groomMother = trim((string) ($extraData['groom_mother'] ?? 'Lê Thị Hương'));
    $groomParentsAddress = trim((string) ($extraData['groom_parents_address'] ?? 'Quận 1, TP. Hồ Chí Minh'));
    $groomRole = trim((string) ($extraData['groom_role'] ?? 'TRƯỞNG NAM'));
    $groomBio = trim((string) ($extraData['groom_bio'] ?? 'Chàng trai điềm đạm, chân thành và luôn trân trọng từng khoảnh khắc sum vầy bên gia đình.'));

    $brideFather = trim((string) ($extraData['bride_father'] ?? 'Phạm Văn Tài'));
    $brideMother = trim((string) ($extraData['bride_mother'] ?? 'Hà Kim Oanh'));
    $brideParentsAddress = trim((string) ($extraData['bride_parents_address'] ?? 'Quận 3, TP. Hồ Chí Minh'));
    $brideRole = trim((string) ($extraData['bride_role'] ?? 'ÚT NỮ'));
    $brideBio = trim((string) ($extraData['bride_bio'] ?? 'Cô gái dịu dàng, yêu nét đẹp truyền thống và luôn mang nụ cười ấm áp đến mọi người xung quanh.'));

    // 5. Love Story
    $storyTitle = trim((string) ($extraData['story_title'] ?? 'Xuân Sang Tình Vừa Chớm'));
    $storySubtitle = trim((string) ($extraData['story_subtitle'] ?? 'TỪ ÁNH NHÌN ĐẦU TIÊN ĐẾN TRĂM NĂM DUYÊN NỢ'));
    $coupleQuote = trim((string) ($extraData['couple_quote'] ?? 'Giữa muôn ngàn sắc hoa mùa xuân, chúng ta tìm thấy nhau như một chữ duyên lành đã hẹn từ rất lâu.'));
    $storyContent = trim((string) ($extraData['story_content'] ?? 'Từ những buổi chiều trò chuyện giản đơn đến ngày khoác lên mình tà áo cưới truyền thống, mỗi bước đi cùng nhau đều hóa thành kỷ niệm đáng trân quý nhất đời người.'));

    // 6. Ceremonies & Banquet
    $ceremonyScope = $ceremonyScope ?? ($extraData['ceremony_scope'] ?? 'both');
    $showBrideCeremony = ($ceremonyScope !== 'groom');
    $showGroomCeremony = ($ceremonyScope !== 'bride');

    $ceremonyGroomTime = trim((string) ($extraData['ceremony_groom_time'] ?? '09:00'));
    $ceremonyGroomAddr = trim((string) ($extraData['ceremony_groom_address'] ?? 'Tư gia Nhà Trai · ' . $groomParentsAddress));

    $ceremonyBrideTime = trim((string) ($extraData['ceremony_time'] ?? '08:00'));
    $ceremonyBrideAddr = trim((string) ($extraData['ceremony_address'] ?? 'Tư gia Nhà Gái · ' . $brideParentsAddress));

    $partyWelcomeTime = trim((string) ($extraData['party_welcome_time'] ?? '17:30'));
    $partyVenue = trim((string) ($extraData['party_venue'] ?? 'White Palace Convention Center'));
    $eventPartyAddress = trim((string) ($extraData['event_party_address'] ?? ($extraData['party_address'] ?? '194 Hoàng Văn Thụ, Phú Nhuận, TP. Hồ Chí Minh')));
    $partyMapUrl = trim((string) ($extraData['event_party_map_link'] ?? ($extraData['party_map_url'] ?? '')));
    if ($partyMapUrl === '' || str_starts_with($partyMapUrl, '#')) {
        $partyMapUrl = 'https://maps.google.com/?q=' . urlencode($partyVenue . ', ' . $eventPartyAddress);
    }
    $mapEmbedUrl = 'https://maps.google.com/maps?q=' . urlencode($partyVenue . ', ' . $eventPartyAddress) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';

    // 7. Timeline (6 Milestones)
    $defaultTimeline = [
        ['time' => '08:00', 'title' => 'Lễ Vu Quy tại tư gia nhà gái'],
        ['time' => '09:00', 'title' => 'Lễ Thành Hôn tại tư gia nhà trai'],
        ['time' => '17:30', 'title' => 'Đón khách & Chụp ảnh lưu niệm'],
        ['time' => '18:00', 'title' => 'Khai tiệc & Cử hành nghi lễ'],
        ['time' => '19:00', 'title' => 'Nâng ly chúc phúc & Dùng tiệc'],
        ['time' => '20:30', 'title' => 'Giao lưu âm nhạc & Tiễn khách'],
    ];
    $timelineItems = is_array($extraData['timeline'] ?? null) && count($extraData['timeline']) > 0
        ? array_values($extraData['timeline'])
        : $defaultTimeline;

    // 8. Dresscode
    $dresscodeColorsRaw = trim((string) ($extraData['dresscode_colors'] ?? '#9B2E33,#D8A85C,#FAF4E6,#286227'));
    $dresscodeColorNames = ['Đỏ Son', 'Vàng Hoàng Kim', 'Kem Lụa', 'Xanh Cổ Điển', 'Hồng Đào', 'Nâu Trầm'];
    $dresscodeHexList = array_values(array_filter(array_map('trim', explode(',', $dresscodeColorsRaw))));
    if (empty($dresscodeHexList)) {
        $dresscodeHexList = ['#9B2E33', '#D8A85C', '#FAF4E6', '#286227'];
    }
    $dresscodeNote = trim((string) ($extraData['dresscode_note'] ?? 'Kính mời quý khách diện trang phục thanh lịch theo các gam màu đỏ son, vàng ánh kim, kem lụa hoặc xanh cổ điển để lưu lại những khung hình hài hòa nhất.'));

    // 9. Album (10 Local Photos Strict)
    $defaultGallery = [];
    for ($gi = 1; $gi <= 10; $gi++) {
        $defaultGallery[] = $assetBase . '/images/gallery-' . str_pad((string) $gi, 2, '0', STR_PAD_LEFT) . '.webp';
    }
    $rawGallery = is_array($extraData['gallery'] ?? null) ? array_values($extraData['gallery']) : [];
    $galleryPhotos = [];
    for ($gi = 0; $gi < 10; $gi++) {
        $galleryPhotos[] = $resolveAsset($rawGallery[$gi] ?? '', $defaultGallery[$gi]);
    }

    // 10. Gift / Dynamic VietQR
    $groomBankName = trim((string) ($extraData['groom_bank_name'] ?? 'Vietcombank'));
    $groomBankAccount = trim((string) ($extraData['groom_bank_account'] ?? '0123456789'));
    $groomAccountName = trim((string) ($extraData['groom_account_name'] ?? 'NGUYEN THE BAO'));
    $qrGroomUrl = VietQrService::resolveQrImage(
        $extraData['qr_groom_image'] ?? null,
        $groomBankName,
        $groomBankAccount,
        'Mung cuoi ' . $groomName,
        $assetBase . '/images/qr-groom.webp',
        '0123456789'
    );

    $brideBankName = trim((string) ($extraData['bride_bank_name'] ?? 'Techcombank'));
    $brideBankAccount = trim((string) ($extraData['bride_bank_account'] ?? '9876543210'));
    $brideAccountName = trim((string) ($extraData['bride_account_name'] ?? 'PHAM NGOC ANH'));
    $qrBrideUrl = VietQrService::resolveQrImage(
        $extraData['qr_bride_image'] ?? null,
        $brideBankName,
        $brideBankAccount,
        'Mung cuoi ' . $brideName,
        $assetBase . '/images/qr-bride.webp',
        '9876543210'
    );

    // 11. Thank You Finale
    $thankYouTitle = trim((string) ($extraData['thank_you_title'] ?? 'Trân Trọng Cảm Ơn!'));
    $thankYouQuote = trim((string) ($extraData['thank_you_quote'] ?? 'Sự hiện diện và lời chúc phúc của Quý vị là món quà vô giá giúp ngày vui của hai gia đình thêm trọn vẹn.'));

    // 12. Audio & Backend Endpoints
    $resolvedMusicUrl = $resolveAsset($extraData['music_url'] ?? '', $assetBase . '/wedding-song.mp3');
    $rsvpEndpoint = $rsvpSubmitUrl ?? '';
    $wishEndpoint = $wishSubmitUrl ?? '';
    $sigEndpoint = (!$isPreview && isset($invitation) && $invitation->id)
        ? route('invitation.signature.save', ['id' => $invitation->id])
        : '';

    $wishesList = $wishes ?? collect();
    $savedSignatures = collect($wishesList)->filter(static fn ($w) => !empty($w->signature_url))->take(8);

    // SSR Countdown Seed
    $nowTs = time();
    $diffSec = max(0, $dateTs - $nowTs);
    $initDays = str_pad((string) floor($diffSec / 86400), 2, '0', STR_PAD_LEFT);
    $initHours = str_pad((string) floor(($diffSec % 86400) / 3600), 2, '0', STR_PAD_LEFT);
    $initMins = str_pad((string) floor(($diffSec % 3600) / 60), 2, '0', STR_PAD_LEFT);
    $initSecs = str_pad((string) ($diffSec % 60), 2, '0', STR_PAD_LEFT);

    // Dynamic Google Calendar Link
    $gcalStart = gmdate('Ymd\THis\Z', $dateTs);
    $gcalEnd = gmdate('Ymd\THis\Z', $dateTs + 10800);
    $gcalHref = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . urlencode('Lễ Cưới ' . $coupleNames)
        . '&dates=' . $gcalStart . '/' . $gcalEnd
        . '&details=' . urlencode('Trân trọng kính mời tới dự lễ thành hôn của ' . $coupleNames)
        . '&location=' . urlencode($partyVenue . ' - ' . $eventPartyAddress);

    $invBrandName = \App\Models\PlatformSetting::getBrandName();
    $tplDisplayName = $template->name ?? 'Xuân Sang Tình Vừa Chớm';
    $pageTitle = $isPreview
        ? 'Xem thử: "' . $tplDisplayName . '" | ' . $invBrandName
        : 'Thiệp cưới: "' . $coupleNames . '" | ' . $invBrandName;
@endphp

@section('title', $pageTitle)

@push('styles')
    <link rel="stylesheet" href="{{ $assetBase }}/styles.css">
@endpush

@section('content')
<div class="mayhen_invitation_root{{ $isEmbed ? ' mayhen_invitation_root--embed' : '' }}"
     id="mayhen_invitation_root"
     data-template-slug="{{ $tplSlug }}"
     data-target-iso="{{ $targetIso }}"
     data-wedding-date="{{ $rawWeddingDate }}"
     data-couple-names="{{ $coupleNames }}"
     data-party-venue="{{ $partyVenue }}"
     data-party-address="{{ $eventPartyAddress }}"
     data-rsvp-endpoint="{{ $rsvpEndpoint }}"
     data-wish-endpoint="{{ $wishEndpoint }}"
     data-signature-endpoint="{{ $sigEndpoint }}"
     data-csrf-token="{{ csrf_token() }}"
     data-is-embed="{{ $isEmbed ? '1' : '0' }}"
     data-is-preview="{{ $isPreview ? '1' : '0' }}"
     data-hy-particle="{{ $assetBase }}/images/opening-hy-float.webp">

    {{-- Template Owned Audio (Global Music Dock Automatically Yields) --}}
    <audio class="mayhen_invitation_audio"
           data-mayhen-audio
           data-sync-target="music_url"
           preload="none"
           loop>
        <source src="{{ $resolvedMusicUrl }}" type="audio/mpeg">
    </audio>

    {{-- Floating Blush-Rose Audio Toggle Button (Reference Top-Right Match) --}}
    <button type="button"
            class="mayhen_invitation_music_btn"
            id="mayhenMusicToggleBtn"
            aria-label="Phát hoặc tạm dừng nhạc nền"
            aria-pressed="false"
            title="Nhạc nền lễ cưới">
        <svg class="mayhen_invitation_music_icon_note" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M9 18V5l12-2v13" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="6" cy="18" r="3" />
            <circle cx="18" cy="16" r="3" />
        </svg>
        <svg class="mayhen_invitation_music_icon_muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M9 18V5l12-2v13" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="6" cy="18" r="3" />
            <circle cx="18" cy="16" r="3" />
            <line x1="3" y1="3" x2="21" y2="21" stroke-linecap="round" />
        </svg>
    </button>

    {{-- Subtle Floating Petals / Double-Happiness Sparkle Layer --}}
    <div class="mayhen_invitation_petals" id="mayhenPetalsLayer" aria-hidden="true"></div>

    {{-- ==================== 0. OPENING IMPERIAL BLOSSOM CARD ==================== --}}
    <div class="mayhen_invitation_opening{{ $autoOpened ? ' is-opened' : '' }}"
         id="mayhenOpeningCurtain"
         data-mayhen-section="opening"
         @if($autoOpened) aria-hidden="true" @endif>
        <div class="mayhen_invitation_opening_particles" id="mayhenOpeningParticles" aria-hidden="true">
            <img src="{{ $assetBase }}/images/opening-hy-float.webp" alt="" class="mayhen_invitation_hy_float mayhen_invitation_hy_float--1">
            <img src="{{ $assetBase }}/images/opening-hy-float.webp" alt="" class="mayhen_invitation_hy_float mayhen_invitation_hy_float--2">
            <img src="{{ $assetBase }}/images/opening-hy-float.webp" alt="" class="mayhen_invitation_hy_float mayhen_invitation_hy_float--3">
            <img src="{{ $assetBase }}/images/opening-hy-float.webp" alt="" class="mayhen_invitation_hy_float mayhen_invitation_hy_float--4">
            <img src="{{ $assetBase }}/images/opening-hy-float.webp" alt="" class="mayhen_invitation_hy_float mayhen_invitation_hy_float--5">
            <img src="{{ $assetBase }}/images/opening-hy-float.webp" alt="" class="mayhen_invitation_hy_float mayhen_invitation_hy_float--6">
        </div>

        <div class="mayhen_invitation_open_card" id="mayhenOpeningCard">
            <img src="{{ $assetBase }}/images/opening-blossom.webp"
                 alt=""
                 class="mayhen_invitation_open_blossom"
                 aria-hidden="true">
            <img src="{{ $assetBase }}/images/opening-lantern.webp"
                 alt=""
                 class="mayhen_invitation_open_lantern"
                 aria-hidden="true">

            <div class="mayhen_invitation_open_content">
                <img src="{{ $assetBase }}/images/opening-emblem.webp"
                     alt="Biểu tượng Song Hỷ"
                     class="mayhen_invitation_open_emblem"
                     width="62"
                     height="62">

                <h2 class="mayhen_invitation_open_names">
                    <span data-sync-target="groom_name">{{ $groomName }}</span>
                    <span class="mayhen_invitation_open_amp">&amp;</span>
                    <span data-sync-target="bride_name">{{ $brideName }}</span>
                </h2>

                <div class="mayhen_invitation_open_divider" aria-hidden="true">
                    <span></span><i>◆</i><span></span>
                </div>

                <p class="mayhen_invitation_open_date" data-sync-target="wedding_date">{{ $openingDateLabel }}</p>
                <p class="mayhen_invitation_open_invite_label">Thân Mời</p>

                <div class="mayhen_invitation_open_guest_pill">{{ $displayGuestName }}</div>

                <p class="mayhen_invitation_open_slogan" data-sync-target="slogan">{{ $slogan }}</p>

                <button type="button"
                        class="mayhen_invitation_open_btn"
                        id="mayhenOpenInvitationBtn">
                    Mở thiệp
                </button>
            </div>

            <img src="{{ $assetBase }}/images/opening-fan.webp"
                 alt=""
                 class="mayhen_invitation_open_fan"
                 aria-hidden="true">
            <img src="{{ $assetBase }}/images/opening-cloud.webp"
                 alt=""
                 class="mayhen_invitation_open_cloud"
                 aria-hidden="true">
        </div>
    </div>

    {{-- ==================== MAIN PAPER CANVAS ==================== --}}
    <div class="mayhen_invitation_wrap">
        <main class="mayhen_invitation_main" id="mayhen_invitation_main_content">

            {{-- ==================== SECTION 1: HERO COVER (SONG HỶ & HOA ĐÀO XUÂN) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_hero mayhen_invitation_reveal"
                     id="mayhen_section_hero"
                     data-mayhen-section="hero">
                <div class="mayhen_invitation_hero_top_decor" aria-hidden="true">
                    <img src="{{ $assetBase }}/images/hero-branch.webp"
                         alt=""
                         class="mayhen_invitation_hero_branch">
                    <img src="{{ $assetBase }}/images/hero-lantern.webp"
                         alt=""
                         class="mayhen_invitation_hero_lantern">
                </div>

                <div class="mayhen_invitation_hero_heading_wrap">
                    <p class="mayhen_invitation_hero_eyebrow" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>
                    <h1 class="mayhen_invitation_hero_names">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <span class="mayhen_invitation_hero_amp">&amp;</span>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </h1>
                </div>

                <div class="mayhen_invitation_hero_stage">
                    <img src="{{ $assetBase }}/images/hero-double-happiness.webp"
                         alt="Chữ Song Hỷ đỏ truyền thống"
                         class="mayhen_invitation_hero_hy"
                         aria-hidden="true">

                    <img src="{{ $assetBase }}/images/hero-cloud-top.webp"
                         alt=""
                         class="mayhen_invitation_hero_cloud_top"
                         aria-hidden="true">

                    {{-- Customizable Couple Portrait cleanly resolving reference's HTTP 403 hero cutout slot --}}
                    <div class="mayhen_invitation_hero_portrait_frame">
                        <img src="{{ $heroPhoto }}"
                             alt="Ảnh bìa cặp đôi {{ $coupleNames }}"
                             class="mayhen_invitation_hero_portrait"
                             data-sync-target="cover_photo_url"
                             width="240"
                             height="340">
                    </div>

                    <img src="{{ $assetBase }}/images/hero-fan.webp"
                         alt=""
                         class="mayhen_invitation_hero_fan"
                         aria-hidden="true">

                    <img src="{{ $assetBase }}/images/hero-clouds-bottom.webp"
                         alt=""
                         class="mayhen_invitation_hero_clouds_bottom"
                         aria-hidden="true">
                </div>

                <div class="mayhen_invitation_hero_date_ribbon">
                    <span class="mayhen_invitation_hero_date_digits" data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</span>
                    <span class="mayhen_invitation_hero_date_lunar" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</span>
                </div>
            </section>

            {{-- ==================== SECTION 2: PARENTS & COUPLE (THÔNG TIN LỄ CƯỚI) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_parents mayhen_invitation_reveal"
                     id="mayhen_section_parents"
                     data-mayhen-section="parents">
                <div class="mayhen_invitation_corner_frame">
                    <img src="{{ $assetBase }}/images/corner-frame.webp" alt="" class="mayhen_invitation_corner mayhen_invitation_corner--tl" aria-hidden="true">
                    <img src="{{ $assetBase }}/images/corner-frame.webp" alt="" class="mayhen_invitation_corner mayhen_invitation_corner--tr" aria-hidden="true">

                    <h2 class="mayhen_invitation_section_heading">THÔNG TIN LỄ CƯỚI</h2>

                    <div class="mayhen_invitation_families_grid">
                        <div class="mayhen_invitation_family_col">
                            <p class="mayhen_invitation_family_title">Ông Bà</p>
                            <p class="mayhen_invitation_family_names">
                                <span data-sync-target="groom_father">{{ $groomFather }}</span><br>
                                <span data-sync-target="groom_mother">{{ $groomMother }}</span>
                            </p>
                            <p class="mayhen_invitation_family_addr" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                        </div>
                        <div class="mayhen_invitation_family_col">
                            <p class="mayhen_invitation_family_title">Ông Bà</p>
                            <p class="mayhen_invitation_family_names">
                                <span data-sync-target="bride_father">{{ $brideFather }}</span><br>
                                <span data-sync-target="bride_mother">{{ $brideMother }}</span>
                            </p>
                            <p class="mayhen_invitation_family_addr" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                        </div>
                    </div>

                    <div class="mayhen_invitation_couple_stack">
                        <div class="mayhen_invitation_couple_person">
                            <h3 class="mayhen_invitation_couple_fullname" data-sync-target="groom_name">{{ $groomName }}</h3>
                            <p class="mayhen_invitation_couple_role" data-sync-target="groom_role">{{ $groomRole }}</p>
                        </div>
                        <span class="mayhen_invitation_couple_plum_amp" aria-hidden="true">&amp;</span>
                        <div class="mayhen_invitation_couple_person">
                            <h3 class="mayhen_invitation_couple_fullname" data-sync-target="bride_name">{{ $brideName }}</h3>
                            <p class="mayhen_invitation_couple_role" data-sync-target="bride_role">{{ $brideRole }}</p>
                        </div>
                    </div>

                    <div class="mayhen_invitation_portraits_grid">
                        <article class="mayhen_invitation_portrait_card">
                            <div class="mayhen_invitation_portrait_media_wrap">
                                <img src="{{ $groomPhoto }}"
                                     alt="Chân dung Chú rể {{ $groomName }}"
                                     class="mayhen_invitation_portrait_media"
                                     data-sync-target="groom_photo_url"
                                     loading="lazy"
                                     width="300"
                                     height="420">
                            </div>
                            <p class="mayhen_invitation_portrait_tag">Chú Rể</p>
                            <p class="mayhen_invitation_portrait_bio" data-sync-target="groom_bio">{{ $groomBio }}</p>
                        </article>

                        <article class="mayhen_invitation_portrait_card">
                            <div class="mayhen_invitation_portrait_media_wrap">
                                <img src="{{ $bridePhoto }}"
                                     alt="Chân dung Cô dâu {{ $brideName }}"
                                     class="mayhen_invitation_portrait_media"
                                     data-sync-target="bride_photo_url"
                                     loading="lazy"
                                     width="300"
                                     height="420">
                            </div>
                            <p class="mayhen_invitation_portrait_tag">Cô Dâu</p>
                            <p class="mayhen_invitation_portrait_bio" data-sync-target="bride_bio">{{ $brideBio }}</p>
                        </article>
                    </div>
                </div>
            </section>

            {{-- ==================== SECTION 3: LOVE STORY (CHUYỆN TÌNH MÙA XUÂN) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_story mayhen_invitation_reveal"
                     id="mayhen_section_story"
                     data-mayhen-section="story">
                <div class="mayhen_invitation_story_card">
                    <p class="mayhen_invitation_story_sub" data-sync-target="story_subtitle">{{ $storySubtitle }}</p>
                    <h2 class="mayhen_invitation_story_title" data-sync-target="story_title">{{ $storyTitle }}</h2>

                    <div class="mayhen_invitation_story_photo_frame">
                        <img src="{{ $saveDatePhoto }}"
                             alt="Khoảnh khắc kỷ niệm {{ $coupleNames }}"
                             class="mayhen_invitation_story_photo"
                             data-sync-target="save_date_photo_url"
                             loading="lazy"
                             width="360"
                             height="480">
                    </div>

                    <blockquote class="mayhen_invitation_story_quote" data-sync-target="couple_quote">
                        “{{ $coupleQuote }}”
                    </blockquote>
                    <p class="mayhen_invitation_story_text" data-sync-target="story_content">{{ $storyContent }}</p>
                </div>
            </section>

            {{-- ==================== SECTION 4: EVENTS & MAPS (NGHI LỄ & TIỆC CƯỚI) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_events mayhen_invitation_reveal"
                     id="mayhen_section_events"
                     data-mayhen-section="events">

                {{-- 4A. Ceremonies Block (Reference Olive-Green Ceremonial Styling) --}}
                <div class="mayhen_invitation_ceremony_block">
                    @if($showGroomCeremony)
                        <h2 class="mayhen_invitation_ceremony_heading">LỄ THÀNH HÔN ĐƯỢC TỔ CHỨC</h2>
                        <p class="mayhen_invitation_ceremony_at">LÚC</p>
                        <p class="mayhen_invitation_ceremony_time" data-sync-target="ceremony_groom_time">{{ $ceremonyGroomTime }}</p>
                        <div class="mayhen_invitation_ceremony_date_row">
                            <span data-sync-target="wedding_weekday">{{ $wWeekdayUpper }}</span>
                            <span class="mayhen_invitation_date_sep" aria-hidden="true">|</span>
                            <strong class="mayhen_invitation_ceremony_day" data-sync-target="wedding_day">{{ $wDay }}</strong>
                            <span class="mayhen_invitation_date_sep" aria-hidden="true">|</span>
                            <span>THÁNG <span data-sync-target="wedding_month">{{ $wMonth }}</span></span>
                        </div>
                        <p class="mayhen_invitation_ceremony_year" data-sync-target="wedding_year">{{ $wYear }}</p>
                        <p class="mayhen_invitation_ceremony_lunar" data-sync-target="wedding_lunar_date">{{ mb_strtoupper($lunarDate, 'UTF-8') }}</p>
                        <p class="mayhen_invitation_ceremony_addr" data-sync-target="ceremony_groom_address">{{ $ceremonyGroomAddr }}</p>
                    @endif

                    @if($showBrideCeremony)
                        <div class="mayhen_invitation_ceremony_subcard">
                            <span class="mayhen_invitation_ceremony_badge">LỄ VU QUY (NHÀ GÁI)</span>
                            <p class="mayhen_invitation_ceremony_subtime">
                                Vào lúc <strong data-sync-target="ceremony_time">{{ $ceremonyBrideTime }}</strong> · <span data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</span>
                            </p>
                            <p class="mayhen_invitation_ceremony_subaddr" data-sync-target="ceremony_address">{{ $ceremonyBrideAddr }}</p>
                        </div>
                    @endif

                    <img src="{{ $assetBase }}/images/corner-frame.webp" alt="" class="mayhen_invitation_corner mayhen_invitation_corner--bl" aria-hidden="true">
                    <img src="{{ $assetBase }}/images/corner-frame.webp" alt="" class="mayhen_invitation_corner mayhen_invitation_corner--br" aria-hidden="true">
                </div>

                {{-- 4B. Wedding Banquet Block (Reference Deep Crimson Styling) --}}
                <div class="mayhen_invitation_party_block">
                    <h2 class="mayhen_invitation_party_heading">THÔNG TIN TIỆC CƯỚI</h2>
                    <p class="mayhen_invitation_party_sub">TIỆC CƯỚI DIỄN RA LÚC</p>
                    <p class="mayhen_invitation_party_time" data-sync-target="party_time">{{ $partyTime }}</p>
                    <div class="mayhen_invitation_party_date_row">
                        <span data-sync-target="wedding_weekday">{{ $wWeekdayUpper }}</span>
                        <span class="mayhen_invitation_date_sep" aria-hidden="true">|</span>
                        <strong class="mayhen_invitation_party_day" data-sync-target="wedding_day">{{ $wDay }}</strong>
                        <span class="mayhen_invitation_date_sep" aria-hidden="true">|</span>
                        <span>THÁNG <span data-sync-target="wedding_month">{{ $wMonth }}</span></span>
                    </div>
                    <p class="mayhen_invitation_party_year" data-sync-target="wedding_year">{{ $wYear }}</p>
                    <p class="mayhen_invitation_party_lunar" data-sync-target="wedding_lunar_date">{{ mb_strtoupper($lunarDate, 'UTF-8') }}</p>

                    <div class="mayhen_invitation_party_milestones">
                        <div class="mayhen_invitation_party_milestone">
                            <span class="mayhen_invitation_milestone_label">ĐÓN KHÁCH</span>
                            <strong class="mayhen_invitation_milestone_time" data-sync-target="party_welcome_time">{{ $partyWelcomeTime }}</strong>
                        </div>
                        <div class="mayhen_invitation_party_milestone_divider" aria-hidden="true"></div>
                        <div class="mayhen_invitation_party_milestone">
                            <span class="mayhen_invitation_milestone_label">KHAI TIỆC</span>
                            <strong class="mayhen_invitation_milestone_time" data-sync-target="party_time">{{ $partyTime }}</strong>
                        </div>
                    </div>
                </div>

                {{-- 4C. Venue & Interactive Google Map --}}
                <div class="mayhen_invitation_map_block" id="mayhen_section_map">
                    <h2 class="mayhen_invitation_map_heading">TIỆC CƯỚI SẼ TỔ CHỨC TẠI</h2>
                    <h3 class="mayhen_invitation_map_venue" data-sync-target="party_venue">{{ $partyVenue }}</h3>
                    <p class="mayhen_invitation_map_address" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>

                    <div class="mayhen_invitation_map_frame">
                        <iframe class="mayhen_invitation_map_iframe"
                                id="mayhenMapIframe"
                                src="{{ $mapEmbedUrl }}"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Bản đồ chỉ đường {{ $partyVenue }}"></iframe>
                    </div>

                    <a href="{{ $partyMapUrl }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="mayhen_invitation_map_btn"
                       id="mayhenMapDirectLink"
                       data-sync-target="event_party_map_link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11z" />
                            <circle cx="12" cy="10" r="2.6" />
                        </svg>
                        <span>Mở trong Maps</span>
                    </a>
                </div>
            </section>

            {{-- ==================== SECTION 5: INTERACTIVE ALBUM BOOK (10 PHOTOS) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_album mayhen_invitation_reveal"
                     id="mayhen_section_album"
                     data-mayhen-section="album">
                <div class="mayhen_invitation_book_stage" id="mayhenAlbumBookStage">
                    {{-- Closed Leather-Bound Album Cover (Exact Reference Aesthetic) --}}
                    <div class="mayhen_invitation_book_cover{{ $isEmbed ? ' is-hidden' : '' }}" id="mayhenAlbumBookCover">
                        <div class="mayhen_invitation_book_spine" aria-hidden="true"></div>
                        <div class="mayhen_invitation_book_inner_border">
                            <h2 class="mayhen_invitation_book_title">ALBUM<br>ẢNH</h2>
                            <p class="mayhen_invitation_book_subtitle">Những khoảnh khắc đáng nhớ</p>
                            <button type="button"
                                    class="mayhen_invitation_book_play"
                                    id="mayhenAlbumOpenBtn"
                                    aria-label="Mở sách ảnh cưới">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <polygon points="8,5 19,12 8,19" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <span class="mayhen_invitation_book_hint">Chạm để mở album (10 ảnh)</span>
                        </div>
                    </div>

                    {{-- Opened Two-Page Interactive Book Spread --}}
                    <div class="mayhen_invitation_book_spread{{ $isEmbed ? ' is-open' : '' }}" id="mayhenAlbumBookSpread">
                        <button type="button"
                                class="mayhen_invitation_book_nav mayhen_invitation_book_nav--prev"
                                id="mayhenBookPrevBtn"
                                aria-label="Trang trước">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <div class="mayhen_invitation_book_pages">
                            <div class="mayhen_invitation_book_page mayhen_invitation_book_page--left">
                                <p class="mayhen_invitation_book_page_quote" id="mayhenBookCaption">
                                    Mỗi bức ảnh là một câu chuyện nhỏ. Hãy lật từng trang để cùng ôn lại nhé!
                                </p>
                                <span class="mayhen_invitation_book_page_counter" id="mayhenBookCounter">Trang 1 / 10</span>
                                <button type="button" class="mayhen_invitation_book_close_btn" id="mayhenAlbumCloseBtn">Đóng bìa</button>
                            </div>
                            <button type="button"
                                    class="mayhen_invitation_book_page mayhen_invitation_book_page--right"
                                    id="mayhenBookPhotoBtn"
                                    aria-label="Phóng to ảnh hiện tại">
                                <img src="{{ $galleryPhotos[0] }}"
                                     alt="Ảnh cưới nổi bật {{ $coupleNames }}"
                                     class="mayhen_invitation_book_active_img"
                                     id="mayhenBookActiveImg"
                                     width="320"
                                     height="450">
                            </button>
                        </div>

                        <button type="button"
                                class="mayhen_invitation_book_nav mayhen_invitation_book_nav--next"
                                id="mayhenBookNextBtn"
                                aria-label="Trang tiếp theo">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- 10-Photo Thumbnail Strip (100% Live Sync & Lightbox Accessible) --}}
                <div class="mayhen_invitation_album_thumbs" id="mayhenAlbumThumbs" role="list" aria-label="Danh sách 10 ảnh cưới">
                    @foreach($galleryPhotos as $gIdx => $gUrl)
                        <button type="button"
                                class="mayhen_invitation_album_thumb{{ $gIdx === 0 ? ' is-active' : '' }}"
                                data-gallery-index="{{ $gIdx }}"
                                data-lightbox-src="{{ $gUrl }}"
                                aria-label="Xem ảnh cưới số {{ $gIdx + 1 }}">
                            <img src="{{ $gUrl }}"
                                 alt="Ảnh cưới {{ $gIdx + 1 }} - {{ $coupleNames }}"
                                 data-sync-target="gallery.{{ $gIdx }}"
                                 loading="eager"
                                 width="140"
                                 height="196">
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- ==================== SECTION 6: COUNTDOWN & OLIVE HEART CALENDAR ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_countdown_calendar mayhen_invitation_reveal"
                     id="mayhen_section_countdown"
                     data-mayhen-section="countdown_calendar">
                <div class="mayhen_invitation_calendar_card">
                    <h2 class="mayhen_invitation_calendar_title" data-sync-target="calendar_month_year">
                        Tháng {{ $wMonthNoZero }} / {{ $wYear }}
                    </h2>

                    <div class="mayhen_invitation_calendar_weekdays">
                        <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                    </div>
                    <div class="mayhen_invitation_calendar_rule" aria-hidden="true"></div>
                    <div class="mayhen_invitation_calendar_grid" id="mayhenCalendarBody" data-sync-target="wedding_calendar">
                        {{-- Populated cleanly by script.js & window.renderMayhenWeddingCalendar --}}
                    </div>
                </div>

                <div class="mayhen_invitation_countdown_wrap" id="mayhenCountdownBox" data-countdown="{{ $targetIso }}">
                    <p class="mayhen_invitation_countdown_label">CÙNG ĐẾM NGƯỢC ĐẾN NGÀY CHUNG ĐÔI</p>
                    <div class="mayhen_invitation_countdown_grid">
                        <div class="mayhen_invitation_cd_box">
                            <strong class="mayhen_invitation_cd_num" data-cd-days data-sync-target="countdown_day">{{ $initDays }}</strong>
                            <span class="mayhen_invitation_cd_unit">Ngày</span>
                        </div>
                        <div class="mayhen_invitation_cd_box">
                            <strong class="mayhen_invitation_cd_num" data-cd-hours data-sync-target="countdown_hour">{{ $initHours }}</strong>
                            <span class="mayhen_invitation_cd_unit">Giờ</span>
                        </div>
                        <div class="mayhen_invitation_cd_box">
                            <strong class="mayhen_invitation_cd_num" data-cd-mins data-sync-target="countdown_min">{{ $initMins }}</strong>
                            <span class="mayhen_invitation_cd_unit">Phút</span>
                        </div>
                        <div class="mayhen_invitation_cd_box">
                            <strong class="mayhen_invitation_cd_num" data-cd-secs data-sync-target="countdown_sec">{{ $initSecs }}</strong>
                            <span class="mayhen_invitation_cd_unit">Giây</span>
                        </div>
                    </div>

                    <a href="{{ $gcalHref }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="mayhen_invitation_gcal_btn"
                       id="mayhenAddCalLink">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        <span>Thêm vào Google Calendar</span>
                    </a>
                </div>
            </section>

            {{-- ==================== SECTION 7: WEDDING TIMELINE ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_timeline mayhen_invitation_reveal"
                     id="mayhen_section_timeline"
                     data-mayhen-section="timeline">
                <h2 class="mayhen_invitation_section_heading">LỊCH TRÌNH NGÀY CƯỚI</h2>
                <p class="mayhen_invitation_section_sub">Từng khoảnh khắc ý nghĩa trong ngày trọng đại</p>

                <ol class="mayhen_invitation_timeline_list" id="mayhenTimelineList">
                    @foreach($timelineItems as $idx => $item)
                        @php
                            $tlTime = trim((string) ($item['time'] ?? '09:00'));
                            $tlTitle = trim((string) ($item['title'] ?? ''));
                        @endphp
                        <li class="mayhen_invitation_timeline_item">
                            <span class="mayhen_invitation_timeline_time" data-sync-target="timeline.{{ $idx }}.time">{{ $tlTime }}</span>
                            <span class="mayhen_invitation_timeline_marker" aria-hidden="true"><i></i></span>
                            <span class="mayhen_invitation_timeline_title" data-sync-target="timeline.{{ $idx }}.title">{{ $tlTitle }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>

            {{-- ==================== SECTION 8: DRESSCODE (HIDDEN IN CURATED 7) ==================== --}}
            @if(!$isCuratedSeven)
                <section class="mayhen_invitation_section mayhen_invitation_dresscode mayhen_invitation_reveal"
                         id="mayhen_section_dresscode"
                         data-mayhen-section="dresscode">
                    <h2 class="mayhen_invitation_section_heading">GỢI Ý TRANG PHỤC</h2>
                    <p class="mayhen_invitation_dresscode_note" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>

                    <div class="mayhen_invitation_dresscode_swatches" id="mayhenDresscodeSwatches" data-sync-target="dresscode_colors">
                        @foreach($dresscodeHexList as $dIdx => $hexColor)
                            @php
                                $safeHex = preg_match('/^#[0-9A-Fa-f]{3,8}$/', $hexColor) ? $hexColor : '#9B2E33';
                                $label = $dresscodeColorNames[$dIdx] ?? $safeHex;
                            @endphp
                            <div class="mayhen_invitation_swatch_item">
                                <span class="mayhen_invitation_swatch_circle" style="--swatch-hex: {{ $safeHex }};"></span>
                                <span class="mayhen_invitation_swatch_label">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ==================== SECTION 9: GIFT & VIETQR (HỘP QUÀ MỪNG) ==================== --}}
            @if(!$isCuratedSeven)
                <section class="mayhen_invitation_section mayhen_invitation_gift mayhen_invitation_reveal{{ $isEmbed ? ' is-gift-open' : '' }}"
                         id="mayhen_section_gift"
                         data-mayhen-section="gift">
                    <h2 class="mayhen_invitation_gift_heading">HỘP QUÀ MỪNG</h2>
                    <p class="mayhen_invitation_gift_desc">
                        Tấm lòng và lời chúc phúc của Quý khách là món quà quý giá nhất với gia đình chúng tôi.
                    </p>

                    <button type="button"
                            class="mayhen_invitation_gift_trigger"
                            id="mayhenGiftToggleBtn"
                            aria-expanded="{{ $isEmbed ? 'true' : 'false' }}"
                            aria-controls="mayhenGiftPanel">
                        <span class="mayhen_invitation_gift_seal" aria-hidden="true">囍</span>
                        <span class="mayhen_invitation_gift_trigger_text">Mở Hộp Quà Mừng &amp; Mã VietQR</span>
                    </button>

                    <div class="mayhen_invitation_gift_panel" id="mayhenGiftPanel">
                        <div class="mayhen_invitation_qr_grid">
                            <article class="mayhen_invitation_qr_card">
                                <span class="mayhen_invitation_qr_badge">MỪNG CƯỚI NHÀ TRAI</span>
                                <button type="button"
                                        class="mayhen_invitation_qr_img_btn"
                                        data-lightbox-src="{{ $qrGroomUrl }}"
                                        aria-label="Phóng to mã QR Nhà Trai">
                                    <img src="{{ $qrGroomUrl }}"
                                         alt="Mã QR mừng cưới Chú rể {{ $groomName }}"
                                         class="mayhen_invitation_qr_img"
                                         data-sync-target="qr_groom_image"
                                         loading="lazy"
                                         width="200"
                                         height="200">
                                </button>
                                <p class="mayhen_invitation_qr_name" data-sync-target="groom_name">{{ $groomName }}</p>
                                <p class="mayhen_invitation_qr_bank" data-sync-target="groom_bank_name">{{ $groomBankName }}</p>
                                <p class="mayhen_invitation_qr_stk" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</p>
                                <p class="mayhen_invitation_qr_holder" data-sync-target="groom_account_name">{{ $groomAccountName }}</p>
                                <button type="button" class="mayhen_invitation_copy_btn" data-copy-stk="{{ $groomBankAccount }}">
                                    Sao chép STK
                                </button>
                            </article>

                            <article class="mayhen_invitation_qr_card">
                                <span class="mayhen_invitation_qr_badge">MỪNG CƯỚI NHÀ GÁI</span>
                                <button type="button"
                                        class="mayhen_invitation_qr_img_btn"
                                        data-lightbox-src="{{ $qrBrideUrl }}"
                                        aria-label="Phóng to mã QR Nhà Gái">
                                    <img src="{{ $qrBrideUrl }}"
                                         alt="Mã QR mừng cưới Cô dâu {{ $brideName }}"
                                         class="mayhen_invitation_qr_img"
                                         data-sync-target="qr_bride_image"
                                         loading="lazy"
                                         width="200"
                                         height="200">
                                </button>
                                <p class="mayhen_invitation_qr_name" data-sync-target="bride_name">{{ $brideName }}</p>
                                <p class="mayhen_invitation_qr_bank" data-sync-target="bride_bank_name">{{ $brideBankName }}</p>
                                <p class="mayhen_invitation_qr_stk" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</p>
                                <p class="mayhen_invitation_qr_holder" data-sync-target="bride_account_name">{{ $brideAccountName }}</p>
                                <button type="button" class="mayhen_invitation_copy_btn" data-copy-stk="{{ $brideBankAccount }}">
                                    Sao chép STK
                                </button>
                            </article>
                        </div>
                    </div>
                </section>
            @endif

            {{-- ==================== SECTION 10: SIGNATURE, RSVP, WISHES & THANK YOU ENDING ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_ending mayhen_invitation_reveal"
                     id="mayhen_section_ending"
                     data-mayhen-section="ending">

                @if(!$isCuratedSeven)
                    {{-- 10A. SỔ KÝ TÊN LƯU BÚT (LAW #8 STRICT: ZERO HUMAN/COUPLE PHOTOS) --}}
                    <div class="mayhen_invitation_signature_block" id="mayhen_section_signature" data-mayhen-section="signature">
                        <div class="mayhen_invitation_sig_emblem" aria-hidden="true">
                            <svg class="mayhen_invitation_sig_seal_svg" viewBox="0 0 64 64" fill="none">
                                <circle cx="32" cy="32" r="29" stroke="currentColor" stroke-width="1.6" stroke-dasharray="3 3" />
                                <circle cx="32" cy="32" r="23" stroke="currentColor" stroke-width="1.2" fill="rgba(155, 46, 51, 0.08)" />
                                <text x="32" y="39" text-anchor="middle" fill="currentColor" font-size="22" font-family="serif" font-weight="700">囍</text>
                            </svg>
                        </div>
                        <h2 class="mayhen_invitation_section_heading">SỔ KÝ TÊN LƯU BÚT</h2>
                        <p class="mayhen_invitation_sig_desc">
                            Kính mời Quý khách lưu lại nét bút kỷ niệm dành tặng <span data-sync-target="couple_names">{{ $coupleNames }}</span>
                        </p>

                        <div class="mayhen_invitation_sig_wall" id="mayhenSignatureWall">
                            @forelse($savedSignatures as $sig)
                                <div class="mayhen_invitation_sig_card">
                                    <img src="{{ $sig->signature_url }}" alt="Chữ ký của {{ $sig->guest_name }}" loading="lazy">
                                    <span class="mayhen_invitation_sig_card_name">{{ $sig->guest_name }}</span>
                                </div>
                            @empty
                                <div class="mayhen_invitation_sig_empty">Chưa có chữ ký nào — hãy là người đầu tiên ký tên lưu niệm!</div>
                            @endforelse
                        </div>

                        <div class="mayhen_invitation_sig_pad">
                            <label for="mayhenSignerName" class="mayhen_invitation_sr_only">Tên khách ký lưu bút</label>
                            <input type="text"
                                   id="mayhenSignerName"
                                   class="mayhen_invitation_input"
                                   placeholder="Nhập họ tên của bạn trước khi ký..."
                                   value="{{ $guestNameRaw }}"
                                   maxlength="100">

                            <div class="mayhen_invitation_sig_canvas_wrap">
                                <canvas id="mayhenSignatureCanvas"
                                        class="mayhen_invitation_sig_canvas"
                                        width="360"
                                        height="160"
                                        aria-label="Khung vẽ chữ ký điện tử"></canvas>
                                <span class="mayhen_invitation_sig_placeholder" id="mayhenSigPlaceholder">Chạm hoặc kéo tay để ký tên tại đây</span>
                            </div>

                            <div class="mayhen_invitation_sig_toolbar">
                                <div class="mayhen_invitation_sig_inks" role="group" aria-label="Chọn màu mực ký">
                                    <button type="button" class="mayhen_invitation_ink_dot is-active" data-pen-color="#9B2E33" aria-label="Mực đỏ son"></button>
                                    <button type="button" class="mayhen_invitation_ink_dot" data-pen-color="#2B1D12" aria-label="Mực tàu trầm"></button>
                                    <button type="button" class="mayhen_invitation_ink_dot" data-pen-color="#9E6B24" aria-label="Mực vàng đồng"></button>
                                </div>
                                <div class="mayhen_invitation_sig_actions">
                                    <button type="button" class="mayhen_invitation_btn_ghost" id="mayhenSigClearBtn">Xóa</button>
                                    <button type="button" class="mayhen_invitation_btn_ghost" id="mayhenSigDownloadBtn">Tải PNG</button>
                                    <button type="button" class="mayhen_invitation_btn_primary" id="mayhenSigSaveBtn">Lưu chữ ký</button>
                                </div>
                            </div>
                            <p class="mayhen_invitation_status_msg" id="mayhenSigStatus" role="status" aria-live="polite"></p>
                        </div>
                    </div>

                    {{-- 10B. XÁC NHẬN THAM DỰ RSVP (LAW #8 STRICT: ZERO HUMAN/COUPLE PHOTOS, REFERENCE WHITE CARD) --}}
                    <div class="mayhen_invitation_rsvp_wrap" id="mayhen_section_rsvp" data-mayhen-section="rsvp">
                        <form class="mayhen_invitation_rsvp_card" id="mayhenRsvpForm" novalidate>
                            @csrf
                            <div class="mayhen_invitation_rsvp_ornament" aria-hidden="true">
                                <span></span><i>✦</i><span></span>
                            </div>
                            <h2 class="mayhen_invitation_rsvp_title">Xác nhận tham dự</h2>

                            <div class="mayhen_invitation_field">
                                <label for="mayhenRsvpName" class="mayhen_invitation_label">Họ và tên *</label>
                                <input type="text"
                                       id="mayhenRsvpName"
                                       name="guest_name"
                                       class="mayhen_invitation_input"
                                       placeholder="Nhập tên của bạn"
                                       value="{{ $guestNameRaw }}"
                                       required>
                            </div>

                            <fieldset class="mayhen_invitation_rsvp_fieldset">
                                <legend class="mayhen_invitation_label">Bạn sẽ tham dự chứ?</legend>
                                <div class="mayhen_invitation_rsvp_radios" id="mayhenRsvpRadios">
                                    <label class="mayhen_invitation_rsvp_option is-selected">
                                        <input type="radio" name="attending_status" value="attending" checked>
                                        <span>Có, tôi sẽ tham dự</span>
                                    </label>
                                    <label class="mayhen_invitation_rsvp_option">
                                        <input type="radio" name="attending_status" value="declined">
                                        <span>Tôi bận, rất tiếc không thể tham dự</span>
                                    </label>
                                    <label class="mayhen_invitation_rsvp_option">
                                        <input type="radio" name="attending_status" value="uncertain">
                                        <span>Có thể tham dự</span>
                                    </label>
                                </div>
                            </fieldset>

                            <div class="mayhen_invitation_field_row">
                                <div class="mayhen_invitation_field">
                                    <label for="mayhenRsvpAdults" class="mayhen_invitation_label">Người lớn</label>
                                    <select id="mayhenRsvpAdults" name="adults_count" class="mayhen_invitation_select">
                                        <option value="1">1 người</option>
                                        <option value="2">2 người</option>
                                        <option value="3">3 người</option>
                                        <option value="4">4 người</option>
                                        <option value="5">5 người</option>
                                    </select>
                                </div>
                                <div class="mayhen_invitation_field">
                                    <label for="mayhenRsvpChildren" class="mayhen_invitation_label">Trẻ em</label>
                                    <select id="mayhenRsvpChildren" name="children_count" class="mayhen_invitation_select">
                                        <option value="0">0 trẻ em</option>
                                        <option value="1">1 trẻ em</option>
                                        <option value="2">2 trẻ em</option>
                                        <option value="3">3 trẻ em</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mayhen_invitation_field">
                                <label for="mayhenRsvpNote" class="mayhen_invitation_label">Lời nhắn gửi Dâu Rể (tuỳ chọn)</label>
                                <textarea id="mayhenRsvpNote"
                                          name="wishes"
                                          class="mayhen_invitation_textarea"
                                          placeholder="Ghi chú chế độ ăn hoặc lời chúc ngắn..."
                                          maxlength="1000"></textarea>
                            </div>

                            <button type="submit" class="mayhen_invitation_rsvp_submit" id="mayhenRsvpSubmitBtn">
                                Gửi xác nhận
                            </button>
                            <p class="mayhen_invitation_status_msg" id="mayhenRsvpStatus" role="status" aria-live="polite"></p>
                        </form>
                    </div>

                    {{-- 10C. SỔ LỜI CHÚC PHÚC (WISHES) --}}
                    <div class="mayhen_invitation_wishes_wrap" id="mayhen_section_wishes" data-mayhen-section="wishes">
                        <h2 class="mayhen_invitation_section_heading">SỔ LỜI CHÚC PHÚC</h2>

                        <div class="mayhen_invitation_wishes_list" id="mayhenWishesList">
                            @forelse($wishesList as $wish)
                                <article class="mayhen_invitation_wish_item">
                                    <h3 class="mayhen_invitation_wish_author">{{ $wish->guest_name ?? 'Khách mời' }}</h3>
                                    <p class="mayhen_invitation_wish_body">{{ $wish->message }}</p>
                                    @if(!empty($wish->reply))
                                        <div class="mayhen_invitation_wish_reply">
                                            <strong>{{ $coupleNames }} phản hồi:</strong>
                                            <span>{{ $wish->reply }}</span>
                                        </div>
                                    @endif
                                </article>
                            @empty
                                <p class="mayhen_invitation_wish_empty" id="mayhenWishEmpty">
                                    Hãy gửi lời chúc phúc đầu tiên tới {{ $coupleNames }} nhé!
                                </p>
                            @endforelse
                        </div>

                        <form class="mayhen_invitation_wish_form" id="mayhenWishForm" novalidate>
                            @csrf
                            <div class="mayhen_invitation_field">
                                <label for="mayhenWishName" class="mayhen_invitation_label">Tên của bạn *</label>
                                <input type="text"
                                       id="mayhenWishName"
                                       name="name"
                                       class="mayhen_invitation_input"
                                       placeholder="Nhập họ tên..."
                                       value="{{ $guestNameRaw }}"
                                       required>
                            </div>

                            <div class="mayhen_invitation_field">
                                <label for="mayhenWishPreset" class="mayhen_invitation_label">Lời chúc gợi ý nhanh</label>
                                <select id="mayhenWishPreset" class="mayhen_invitation_select">
                                    <option value="">-- Tự soạn lời chúc riêng --</option>
                                    <option value="Chúc hai bạn trăm năm hạnh phúc, sắc son bền chặt và sớm đón thiên thần nhỏ!">Trăm năm hạnh phúc, sắc son bền chặt!</option>
                                    <option value="Chúc mừng ngày trọng đại! Cầu chúc tổ ấm mới luôn ngập tràn tiếng cười và bình an.">Chúc mừng tổ ấm mới ngập tràn bình an!</option>
                                    <option value="Mừng đôi uyên ương nên duyên vợ chồng, mãi luôn yêu thương và đồng hành trọn đời!">Mừng đôi uyên ương nên duyên trăm năm!</option>
                                </select>
                            </div>

                            <div class="mayhen_invitation_field">
                                <label for="mayhenWishMessage" class="mayhen_invitation_label">Lời chúc gửi Cô Dâu &amp; Chú Rể *</label>
                                <textarea id="mayhenWishMessage"
                                          name="message"
                                          class="mayhen_invitation_textarea"
                                          placeholder="Viết những lời chúc tốt đẹp nhất..."
                                          maxlength="1000"
                                          required></textarea>
                                <span class="mayhen_invitation_char_count"><span id="mayhenWishCount">0</span> / 1000</span>
                            </div>

                            <button type="submit" class="mayhen_invitation_rsvp_submit" id="mayhenWishSubmitBtn">
                                Gửi lời chúc
                            </button>
                            <p class="mayhen_invitation_status_msg" id="mayhenWishStatus" role="status" aria-live="polite"></p>
                        </form>
                    </div>

                    {{-- 10D. LỜI TRI ÂN KẾT THIỆP --}}
                    <div class="mayhen_invitation_finale">
                        <div class="mayhen_invitation_finale_photo_frame">
                            <img src="{{ $footerPhoto }}"
                                 alt="Ảnh kết thiệp {{ $coupleNames }}"
                                 class="mayhen_invitation_finale_photo"
                                 data-sync-target="footer_photo_url"
                                 loading="lazy"
                                 width="340"
                                 height="460">
                        </div>
                        <h2 class="mayhen_invitation_finale_title" data-sync-target="thank_you_title">{{ $thankYouTitle }}</h2>
                        <p class="mayhen_invitation_finale_quote" data-sync-target="thank_you_quote">{{ $thankYouQuote }}</p>
                        <div class="mayhen_invitation_monogram_row" aria-hidden="true">
                            <span class="mayhen_invitation_monogram_badge" data-monogram-role="groom">{{ $groomInitial }}</span>
                            <span class="mayhen_invitation_monogram_hy">囍</span>
                            <span class="mayhen_invitation_monogram_badge" data-monogram-role="bride">{{ $brideInitial }}</span>
                        </div>
                        <p class="mayhen_invitation_finale_names" data-sync-target="couple_names">{{ $coupleNames }}</p>
                    </div>
                @else
                    {{-- Curated 7-Section Mode: Concise Closing Ribbon --}}
                    <div class="mayhen_invitation_curated_closing">
                        <h2 class="mayhen_invitation_finale_title" data-sync-target="thank_you_title">{{ $thankYouTitle }}</h2>
                        <p class="mayhen_invitation_finale_quote" data-sync-target="thank_you_quote">{{ $thankYouQuote }}</p>
                        <p class="mayhen_invitation_finale_names" data-sync-target="couple_names">{{ $coupleNames }}</p>
                    </div>
                @endif
            </section>
        </main>
    </div>

    {{-- Fullscreen Album & QR Lightbox --}}
    <div class="mayhen_invitation_lightbox" id="mayhenLightbox" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="mayhen_invitation_lb_top">
            <span id="mayhenLbCounter">1 / 10</span>
            <button type="button" class="mayhen_invitation_lb_close" id="mayhenLbClose" aria-label="Đóng xem ảnh">&times;</button>
        </div>
        <div class="mayhen_invitation_lb_stage">
            <button type="button" class="mayhen_invitation_lb_arrow mayhen_invitation_lb_arrow--prev" id="mayhenLbPrev" aria-label="Ảnh trước">&#8249;</button>
            <img src="{{ $galleryPhotos[0] }}" alt="Xem phóng to ảnh cưới" class="mayhen_invitation_lb_img" id="mayhenLbImg">
            <button type="button" class="mayhen_invitation_lb_arrow mayhen_invitation_lb_arrow--next" id="mayhenLbNext" aria-label="Ảnh tiếp theo">&#8250;</button>
        </div>
        <p class="mayhen_invitation_lb_caption" id="mayhenLbCaption">{{ $coupleNames }}</p>
    </div>

    <div class="mayhen_invitation_toast" id="mayhenToast" role="status" aria-live="polite"></div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ $assetBase }}/script.js"></script>
@endpush
