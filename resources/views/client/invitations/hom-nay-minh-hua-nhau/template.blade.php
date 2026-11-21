@extends('client.layouts.invitation')

@php
    use App\Services\VietQrService;

    $tplSlug = $template->slug ?? 'hom-nay-minh-hua-nhau';
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
    $groomName = trim((string) ($wedding?->groom_name ?? ($extraData['groom_name'] ?? 'Minh Trí')));
    if ($groomName === '') {
        $groomName = 'Minh Trí';
    }
    $brideName = trim((string) ($wedding?->bride_name ?? ($extraData['bride_name'] ?? 'Thanh Hằng')));
    if ($brideName === '') {
        $brideName = 'Thanh Hằng';
    }
    $coupleNames = $brideName . ' & ' . $groomName;

    $guestNameRaw = trim((string) ($guest?->name ?? ''));
    $displayGuestName = $guestNameRaw !== '' ? $guestNameRaw : 'Quý khách';

    // 2. Date & Lunar Date (Asia/Ho_Chi_Minh)
    $rawWeddingDate = trim((string) ($wedding?->wedding_date?->format('Y-m-d') ?? ($wedding?->wedding_date ?? ($extraData['wedding_date'] ?? '2026-12-15'))));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawWeddingDate)) {
        $rawWeddingDate = '2026-12-15';
    }
    $partyTime = trim((string) ($extraData['party_time'] ?? '10:30'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $partyTime)) {
        $partyTime = '10:30';
    }
    $targetTime = trim((string) ($extraData['event_target_time'] ?? $partyTime));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $targetTime)) {
        $targetTime = '10:30';
    }

    $dateTs = strtotime($rawWeddingDate . ' ' . $targetTime . ':00 +0700') ?: strtotime('2026-12-15 10:30:00 +0700');
    $wDay = date('d', $dateTs);
    $wDayNoZero = (string) (int) $wDay;
    $wMonth = date('m', $dateTs);
    $wMonthNoZero = (string) (int) $wMonth;
    $wYear = date('Y', $dateTs);

    $weekdaysVi = ['Chủ Nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
    $wWeekday = $weekdaysVi[(int) date('w', $dateTs)] ?? 'Thứ 3';
    $wWeekdayUpper = mb_strtoupper($wWeekday, 'UTF-8');
    $weddingDateDigits = $wDay . '.' . $wMonth . '.' . $wYear;
    $targetIso = $rawWeddingDate . 'T' . $targetTime . ':00+07:00';

    $lunarDate = trim((string) ($extraData['wedding_lunar_date'] ?? '(Tức ngày 17 tháng 11 năm Bính Ngọ)'));
    $ceremonyType = trim((string) ($extraData['ceremony_type'] ?? 'TIỆC MỪNG LỄ THÀNH HÔN'));
    $slogan = trim((string) ($extraData['slogan'] ?? 'Trân trọng kính mời bạn tới chung vui ngày hạnh phúc'));

    // 3. Portraits & Media (100% Local Assets)
    $heroPhoto = $resolveAsset(
        $wedding?->cover_photo_url ?? ($extraData['hero_photo_url'] ?? ($extraData['cover_photo_url'] ?? '')),
        $assetBase . '/images/hero-couple.webp'
    );
    $bridePhoto = $resolveAsset($extraData['bride_photo_url'] ?? '', $assetBase . '/images/bride-portrait.webp');
    $groomPhoto = $resolveAsset($extraData['groom_photo_url'] ?? '', $assetBase . '/images/groom-portrait.webp');
    $saveDatePhoto = $resolveAsset($extraData['save_date_photo_url'] ?? '', $assetBase . '/images/save-date.webp');
    $footerPhoto = $resolveAsset($extraData['footer_photo_url'] ?? '', $assetBase . '/images/footer-couple.webp');

    // 4. Couple & Families
    $brideDob = trim((string) ($extraData['bride_dob'] ?? '20.08.2001'));
    $groomDob = trim((string) ($extraData['groom_dob'] ?? '06.05.1998'));
    $brideBio = trim((string) ($extraData['bride_bio'] ?? 'Cô gái dịu dàng, rạng rỡ và luôn mang nguồn năng lượng ngọt ngào tới mọi ngày bên nhau.'));
    $groomBio = trim((string) ($extraData['groom_bio'] ?? 'Chàng trai ấm áp, chân thành và luôn là chỗ dựa vững chãi cho người mình thương.'));

    $groomFather = trim((string) ($extraData['groom_father'] ?? 'Phạm Minh Hải'));
    $groomMother = trim((string) ($extraData['groom_mother'] ?? 'Nguyễn Mai Thu'));
    $groomParentsAddress = trim((string) ($extraData['groom_parents_address'] ?? 'TP. Hà Nội'));

    $brideFather = trim((string) ($extraData['bride_father'] ?? 'Trần Anh Tài'));
    $brideMother = trim((string) ($extraData['bride_mother'] ?? 'Nguyễn Thanh Mai'));
    $brideParentsAddress = trim((string) ($extraData['bride_parents_address'] ?? 'TP. Hạ Long, Quảng Ninh'));

    // 5. Love Story
    $storyTitle = trim((string) ($extraData['story_title'] ?? 'OUR LOVE STORY'));
    $storySubtitle = trim((string) ($extraData['story_subtitle'] ?? 'Và rồi chúng mình chọn ở lại bên nhau'));
    $coupleQuote = trim((string) ($extraData['couple_quote'] ?? 'Hôm nay mình hứa nhau — cùng đi qua những ngày bình yên giản dị nhất.'));
    $storyContent = trim((string) ($extraData['story_content'] ?? 'Bắt đầu từ một cuộc trò chuyện tình cờ, tụi mình nhận ra hạnh phúc chẳng cần điều gì xa xôi mà chỉ đơn giản là luôn có một người lắng nghe, sẻ chia và nắm tay nhau bước tiếp chặng đường dài phía trước.'));

    // 6. Events & Maps
    $ceremonyScope = $ceremonyScope ?? ($extraData['ceremony_scope'] ?? 'both');
    $showBrideCeremony = ($ceremonyScope !== 'groom');
    $showGroomCeremony = ($ceremonyScope !== 'bride');

    $ceremonyBrideTime = trim((string) ($extraData['ceremony_time'] ?? '08:00'));
    $ceremonyBrideAddr = trim((string) ($extraData['ceremony_address'] ?? 'Tư gia Nhà Gái · ' . $brideParentsAddress));
    $ceremonyGroomTime = trim((string) ($extraData['ceremony_groom_time'] ?? '09:30'));
    $ceremonyGroomAddr = trim((string) ($extraData['ceremony_groom_address'] ?? 'Tư gia Nhà Trai · ' . $groomParentsAddress));

    $partyVenue = trim((string) ($extraData['party_venue'] ?? 'KHÁCH SẠN MƯỜNG THANH LUXURY QUẢNG NINH'));
    $eventPartyAddress = trim((string) ($extraData['event_party_address'] ?? ($extraData['party_address'] ?? 'Tổ 1, Khu 2, Đường Hạ Long, Bãi Cháy, TP. Hạ Long, Quảng Ninh')));
    $partyMapUrl = trim((string) ($extraData['event_party_map_link'] ?? ($extraData['party_map_url'] ?? '')));
    if ($partyMapUrl === '' || str_starts_with($partyMapUrl, '#')) {
        $partyMapUrl = 'https://maps.google.com/?q=' . urlencode($partyVenue . ', ' . $eventPartyAddress);
    }
    $mapEmbedUrl = 'https://maps.google.com/maps?q=' . urlencode($partyVenue . ', ' . $eventPartyAddress) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';

    // 7. Timeline (6 Items)
    $defaultTimeline = [
        ['time' => '08:00', 'title' => 'Lễ Vu Quy tại tư gia nhà gái'],
        ['time' => '09:30', 'title' => 'Lễ Thành Hôn tại tư gia nhà trai'],
        ['time' => '10:00', 'title' => 'Đón khách & Chụp hình lưu niệm'],
        ['time' => '10:30', 'title' => 'Cử hành nghi thức & Khai tiệc'],
        ['time' => '11:30', 'title' => 'Nâng ly chúc phúc cùng gia đình'],
        ['time' => '13:00', 'title' => 'Giao lưu âm nhạc & Tiễn khách'],
    ];
    $timelineItems = is_array($extraData['timeline'] ?? null) && count($extraData['timeline']) > 0
        ? array_values($extraData['timeline'])
        : $defaultTimeline;

    // 8. Dresscode
    $dresscodeColorsRaw = trim((string) ($extraData['dresscode_colors'] ?? '#FFFFFF,#FDE2E4,#9B2E33,#1F1D1A'));
    $dresscodeColorNames = ['Trắng Tinh Khôi', 'Hồng Phấn', 'Đỏ Nhung', 'Đen Cổ Điển', 'Kem Nhạt', 'Xanh Ô Liu'];
    $dresscodeHexList = array_values(array_filter(array_map('trim', explode(',', $dresscodeColorsRaw))));
    if (empty($dresscodeHexList)) {
        $dresscodeHexList = ['#FFFFFF', '#FDE2E4', '#9B2E33', '#1F1D1A'];
    }
    $dresscodeNote = trim((string) ($extraData['dresscode_note'] ?? 'Rất mong quý khách lựa chọn trang phục theo các tông màu tinh giản dưới đây để cùng tạo nên những bức ảnh kỷ niệm thật rạng rỡ.'));

    // 9. Album 10 Local Photos
    $defaultGallery = [];
    for ($gi = 1; $gi <= 10; $gi++) {
        $defaultGallery[] = $assetBase . '/images/gallery-' . str_pad((string) $gi, 2, '0', STR_PAD_LEFT) . '.webp';
    }
    $rawGallery = is_array($extraData['gallery'] ?? null) ? array_values($extraData['gallery']) : [];
    $galleryPhotos = [];
    for ($gi = 0; $gi < 10; $gi++) {
        $galleryPhotos[] = $resolveAsset($rawGallery[$gi] ?? '', $defaultGallery[$gi]);
    }

    // 10. Dynamic VietQR
    $groomBankName = trim((string) ($extraData['groom_bank_name'] ?? 'Vietcombank'));
    $groomBankAccount = trim((string) ($extraData['groom_bank_account'] ?? '0123456789'));
    $groomAccountName = trim((string) ($extraData['groom_account_name'] ?? 'PHAM MINH TRI'));
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
    $brideAccountName = trim((string) ($extraData['bride_account_name'] ?? 'TRAN THANH HANG'));
    $qrBrideUrl = VietQrService::resolveQrImage(
        $extraData['qr_bride_image'] ?? null,
        $brideBankName,
        $brideBankAccount,
        'Mung cuoi ' . $brideName,
        $assetBase . '/images/qr-bride.webp',
        '9876543210'
    );

    // 11. Thank You Finale
    $thankYouTitle = trim((string) ($extraData['thank_you_title'] ?? 'Thank You!'));
    $thankYouQuote = trim((string) ($extraData['thank_you_quote'] ?? 'Cảm ơn bạn đã dành thời gian chung vui cùng chúng mình trong ngày trọng đại này!'));

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

    // Google Calendar Link
    $gcalStart = gmdate('Ymd\THis\Z', $dateTs);
    $gcalEnd = gmdate('Ymd\THis\Z', $dateTs + 10800);
    $gcalHref = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . urlencode('Lễ Cưới ' . $coupleNames)
        . '&dates=' . $gcalStart . '/' . $gcalEnd
        . '&details=' . urlencode('Trân trọng kính mời tới dự lễ thành hôn của ' . $coupleNames)
        . '&location=' . urlencode($partyVenue . ' - ' . $eventPartyAddress);

    $invBrandName = \App\Models\PlatformSetting::getBrandName();
    $tplDisplayName = $template->name ?? 'Hôm Nay Mình Hứa Nhau';
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
     data-is-preview="{{ $isPreview ? '1' : '0' }}">

    {{-- Template Owned Audio Element --}}
    <audio class="mayhen_invitation_audio"
           data-mayhen-audio
           data-sync-target="music_url"
           preload="none"
           loop>
        <source src="{{ $resolvedMusicUrl }}" type="audio/mpeg">
    </audio>

    {{-- Floating Soft-Pink Music Button (Reference Top-Right Match) --}}
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

    {{-- ==================== 0. MINIMAL MODERN OPENING SHEET ==================== --}}
    <div class="mayhen_invitation_opening{{ $autoOpened ? ' is-opened' : '' }}"
         id="mayhenOpeningCurtain"
         data-mayhen-section="opening"
         @if($autoOpened) aria-hidden="true" @endif>
        <div class="mayhen_invitation_open_sheet">
            <p class="mayhen_invitation_open_eyebrow">SAVE THE DATE</p>
            <img src="{{ $assetBase }}/images/pink-heart-3d.webp"
                 alt=""
                 class="mayhen_invitation_open_heart"
                 width="58"
                 height="52"
                 aria-hidden="true">

            <h2 class="mayhen_invitation_open_names">
                <span data-sync-target="bride_name">{{ $brideName }}</span>
                <span class="mayhen_invitation_open_amp">&amp;</span>
                <span data-sync-target="groom_name">{{ $groomName }}</span>
            </h2>

            <p class="mayhen_invitation_open_date" data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</p>

            <div class="mayhen_invitation_open_guest_box">
                <span class="mayhen_invitation_open_to">Thân mời</span>
                <strong class="mayhen_invitation_open_guest">{{ $displayGuestName }}</strong>
            </div>

            <p class="mayhen_invitation_open_slogan" data-sync-target="slogan">{{ $slogan }}</p>

            <button type="button"
                    class="mayhen_invitation_open_btn"
                    id="mayhenOpenInvitationBtn">
                Mở thiệp
            </button>
        </div>
    </div>

    {{-- ==================== MAIN PURE-WHITE EDITORIAL COLUMN ==================== --}}
    <div class="mayhen_invitation_wrap">
        <main class="mayhen_invitation_main" id="mayhen_invitation_main_content">

            {{-- ==================== SECTION 1: HERO COVER (SAVE THE DATE + LANTERN GARLAND) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_hero mayhen_invitation_reveal"
                     id="mayhen_section_hero"
                     data-mayhen-section="hero">
                <div class="mayhen_invitation_hero_card">
                    <img src="{{ $heroPhoto }}"
                         alt="Ảnh bìa cặp đôi {{ $coupleNames }}"
                         class="mayhen_invitation_hero_photo"
                         data-sync-target="cover_photo_url"
                         width="390"
                         height="585">

                    <h1 class="mayhen_invitation_hero_save_date">
                        Save The<br>Date
                    </h1>

                    <div class="mayhen_invitation_hero_script_block">
                        <span class="mayhen_invitation_hero_script_name" data-sync-target="bride_name">{{ $brideName }}</span>
                        <span class="mayhen_invitation_hero_script_amp">&amp;</span>
                        <span class="mayhen_invitation_hero_script_name" data-sync-target="groom_name">{{ $groomName }}</span>
                    </div>
                </div>

                <div class="mayhen_invitation_hero_garland_wrap" aria-hidden="true">
                    <img src="{{ $assetBase }}/images/lantern-garland.webp"
                         alt=""
                         class="mayhen_invitation_hero_garland"
                         width="360"
                         height="112">
                </div>
            </section>

            {{-- ==================== SECTION 2: CALENDAR & COUNTDOWN (OUR WEDDING DAY) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_countdown_calendar mayhen_invitation_reveal"
                     id="mayhen_section_countdown"
                     data-mayhen-section="countdown_calendar">
                <div class="mayhen_invitation_cal_top_row">
                    <span class="mayhen_invitation_cal_script_left">Our wedding day</span>
                    <span class="mayhen_invitation_cal_script_right" id="mayhenCalMonthScript">Tháng {{ $wMonthNoZero }}</span>
                </div>

                <div class="mayhen_invitation_cal_card">
                    <h2 class="mayhen_invitation_cal_heading" data-sync-target="calendar_month_year">
                        Tháng {{ $wMonthNoZero }} · Năm {{ $wYear }}
                    </h2>

                    <div class="mayhen_invitation_cal_weekdays">
                        <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                    </div>
                    <div class="mayhen_invitation_cal_rule" aria-hidden="true"></div>
                    <div class="mayhen_invitation_cal_grid" id="mayhenCalendarBody" data-sync-target="wedding_calendar">
                        {{-- Populated cleanly by script.js & window.renderMayhenWeddingCalendar --}}
                    </div>
                </div>

                <div class="mayhen_invitation_countdown_wrap" id="mayhenCountdownBox" data-countdown="{{ $targetIso }}">
                    <div class="mayhen_invitation_cd_grid">
                        <div class="mayhen_invitation_cd_item">
                            <strong class="mayhen_invitation_cd_val" data-cd-days data-sync-target="countdown_day">{{ $initDays }}</strong>
                            <span class="mayhen_invitation_cd_label">Ngày</span>
                        </div>
                        <div class="mayhen_invitation_cd_item">
                            <strong class="mayhen_invitation_cd_val" data-cd-hours data-sync-target="countdown_hour">{{ $initHours }}</strong>
                            <span class="mayhen_invitation_cd_label">Giờ</span>
                        </div>
                        <div class="mayhen_invitation_cd_item">
                            <strong class="mayhen_invitation_cd_val" data-cd-mins data-sync-target="countdown_min">{{ $initMins }}</strong>
                            <span class="mayhen_invitation_cd_label">Phút</span>
                        </div>
                        <div class="mayhen_invitation_cd_item">
                            <strong class="mayhen_invitation_cd_val" data-cd-secs data-sync-target="countdown_sec">{{ $initSecs }}</strong>
                            <span class="mayhen_invitation_cd_label">Giây</span>
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

            {{-- ==================== SECTION 3: STAGGERED PORTRAITS & PARENTS ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_parents mayhen_invitation_reveal"
                     id="mayhen_section_parents"
                     data-mayhen-section="parents">
                {{-- Asymmetric Left Bride Card --}}
                <div class="mayhen_invitation_stagger_row mayhen_invitation_stagger_row--left">
                    <article class="mayhen_invitation_profile_card mayhen_invitation_profile_card--light">
                        <img src="{{ $bridePhoto }}"
                             alt="Chân dung Cô dâu {{ $brideName }}"
                             class="mayhen_invitation_profile_img"
                             data-sync-target="bride_photo_url"
                             loading="lazy"
                             width="260"
                             height="390">
                        <div class="mayhen_invitation_profile_overlay mayhen_invitation_profile_overlay--darktext">
                            <span class="mayhen_invitation_profile_role">Cô dâu</span>
                            <h3 class="mayhen_invitation_profile_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                            <p class="mayhen_invitation_profile_dob" data-sync-target="bride_dob">{{ $brideDob }}</p>
                        </div>
                    </article>
                    <p class="mayhen_invitation_profile_bio" data-sync-target="bride_bio">{{ $brideBio }}</p>
                </div>

                {{-- Asymmetric Right Groom Card --}}
                <div class="mayhen_invitation_stagger_row mayhen_invitation_stagger_row--right">
                    <article class="mayhen_invitation_profile_card mayhen_invitation_profile_card--dark">
                        <img src="{{ $groomPhoto }}"
                             alt="Chân dung Chú rể {{ $groomName }}"
                             class="mayhen_invitation_profile_img"
                             data-sync-target="groom_photo_url"
                             loading="lazy"
                             width="260"
                             height="390">
                        <div class="mayhen_invitation_profile_overlay mayhen_invitation_profile_overlay--lighttext">
                            <span class="mayhen_invitation_profile_role">Chú rể</span>
                            <h3 class="mayhen_invitation_profile_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                            <p class="mayhen_invitation_profile_dob" data-sync-target="groom_dob">{{ $groomDob }}</p>
                        </div>
                    </article>
                    <p class="mayhen_invitation_profile_bio mayhen_invitation_profile_bio--right" data-sync-target="groom_bio">{{ $groomBio }}</p>
                </div>

                {{-- Two Families + Calligraphic Names & Glossy 3D Pink Heart --}}
                <div class="mayhen_invitation_families_block">
                    <div class="mayhen_invitation_families_grid">
                        <div class="mayhen_invitation_family_col">
                            <h3 class="mayhen_invitation_family_side">Nhà Trai</h3>
                            <p class="mayhen_invitation_family_parents">
                                Ông. <span data-sync-target="groom_father">{{ $groomFather }}</span><br>
                                Bà. <span data-sync-target="groom_mother">{{ $groomMother }}</span>
                            </p>
                            <p class="mayhen_invitation_family_city" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                        </div>
                        <div class="mayhen_invitation_family_col">
                            <h3 class="mayhen_invitation_family_side">Nhà Gái</h3>
                            <p class="mayhen_invitation_family_parents">
                                Ông. <span data-sync-target="bride_father">{{ $brideFather }}</span><br>
                                Bà. <span data-sync-target="bride_mother">{{ $brideMother }}</span>
                            </p>
                            <p class="mayhen_invitation_family_city" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                        </div>
                    </div>

                    <div class="mayhen_invitation_heart_couple">
                        <p class="mayhen_invitation_heart_script" data-sync-target="bride_name">{{ $brideName }}</p>
                        <img src="{{ $assetBase }}/images/pink-heart-3d.webp"
                             alt=""
                             class="mayhen_invitation_heart_3d"
                             width="80"
                             height="72"
                             aria-hidden="true">
                        <p class="mayhen_invitation_heart_script" data-sync-target="groom_name">{{ $groomName }}</p>
                    </div>
                </div>
            </section>

            {{-- ==================== SECTION 4: TORN NOTEBOOK LOVE STORY ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_story mayhen_invitation_reveal"
                     id="mayhen_section_story"
                     data-mayhen-section="story">
                <h2 class="mayhen_invitation_story_heading" data-sync-target="story_title">{{ $storyTitle }}</h2>

                <div class="mayhen_invitation_notebook_stack">
                    <div class="mayhen_invitation_notebook_back" aria-hidden="true"></div>
                    <article class="mayhen_invitation_notebook_sheet">
                        <div class="mayhen_invitation_notebook_holes" aria-hidden="true">
                            <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                        </div>
                        <div class="mayhen_invitation_notebook_body">
                            <p class="mayhen_invitation_notebook_text" data-sync-target="story_content">{{ $storyContent }}</p>
                            <p class="mayhen_invitation_notebook_sub" data-sync-target="story_subtitle">{{ $storySubtitle }}</p>
                            <blockquote class="mayhen_invitation_notebook_quote" data-sync-target="couple_quote">{{ $coupleQuote }}</blockquote>
                        </div>
                    </article>

                    <figure class="mayhen_invitation_story_polaroid">
                        <img src="{{ $saveDatePhoto }}"
                             alt="Khoảnh khắc chuyện tình {{ $coupleNames }}"
                             data-sync-target="save_date_photo_url"
                             loading="lazy"
                             width="180"
                             height="220">
                    </figure>
                </div>
            </section>

            {{-- ==================== SECTION 5: EVENTS & MAPS (EDITORIAL DOUBLE-RULE DATE) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_events mayhen_invitation_reveal"
                     id="mayhen_section_events"
                     data-mayhen-section="events">
                <h2 class="mayhen_invitation_event_heading" data-sync-target="ceremony_type">{{ $ceremonyType }}</h2>
                <p class="mayhen_invitation_event_time_line">
                    VÀO LÚC <span data-sync-target="party_time">{{ $partyTime }}</span> <span data-sync-target="wedding_weekday">{{ $wWeekdayUpper }}</span>
                </p>

                <div class="mayhen_invitation_event_date_strip">
                    <div class="mayhen_invitation_event_date_side">
                        <span class="mayhen_invitation_double_rule" aria-hidden="true"></span>
                        <span class="mayhen_invitation_event_side_txt">THÁNG <span data-sync-target="wedding_month">{{ $wMonthNoZero }}</span></span>
                        <span class="mayhen_invitation_double_rule" aria-hidden="true"></span>
                    </div>

                    <div class="mayhen_invitation_event_big_day" data-sync-target="wedding_day">{{ $wDay }}</div>

                    <div class="mayhen_invitation_event_date_side">
                        <span class="mayhen_invitation_double_rule" aria-hidden="true"></span>
                        <span class="mayhen_invitation_event_side_txt">NĂM <span data-sync-target="wedding_year">{{ $wYear }}</span></span>
                        <span class="mayhen_invitation_double_rule" aria-hidden="true"></span>
                    </div>
                </div>

                <p class="mayhen_invitation_event_lunar" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</p>

                <p class="mayhen_invitation_event_venue" data-sync-target="party_venue">TẠI {{ $partyVenue }}</p>
                <p class="mayhen_invitation_event_address" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>

                @if($showBrideCeremony || $showGroomCeremony)
                    <div class="mayhen_invitation_ceremony_pills">
                        @if($showBrideCeremony)
                            <div class="mayhen_invitation_ceremony_pill">
                                <span class="mayhen_invitation_ceremony_pill_tag">LỄ VU QUY</span>
                                <strong data-sync-target="ceremony_time">{{ $ceremonyBrideTime }}</strong>
                                <p data-sync-target="ceremony_address">{{ $ceremonyBrideAddr }}</p>
                            </div>
                        @endif
                        @if($showGroomCeremony)
                            <div class="mayhen_invitation_ceremony_pill">
                                <span class="mayhen_invitation_ceremony_pill_tag">LỄ THÀNH HÔN</span>
                                <strong data-sync-target="ceremony_groom_time">{{ $ceremonyGroomTime }}</strong>
                                <p data-sync-target="ceremony_groom_address">{{ $ceremonyGroomAddr }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mayhen_invitation_map_box" id="mayhen_section_map">
                    <iframe class="mayhen_invitation_map_iframe"
                            id="mayhenMapIframe"
                            src="{{ $mapEmbedUrl }}"
                            loading="lazy"
                            allowfullscreen
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Bản đồ chỉ đường {{ $partyVenue }}"></iframe>
                    <a href="{{ $partyMapUrl }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="mayhen_invitation_map_pill"
                       id="mayhenMapDirectLink"
                       data-sync-target="event_party_map_link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11z" />
                            <circle cx="12" cy="10" r="2.5" />
                        </svg>
                        <span>Mở trong Maps</span>
                    </a>
                </div>
            </section>

            {{-- ==================== SECTION 6: MODERN TIMELINE ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_timeline mayhen_invitation_reveal"
                     id="mayhen_section_timeline"
                     data-mayhen-section="timeline">
                <h2 class="mayhen_invitation_modern_heading">LỊCH TRÌNH NGÀY CƯỚI</h2>
                <p class="mayhen_invitation_modern_sub">Các mốc thời gian đáng nhớ trong ngày chung đôi</p>

                <ol class="mayhen_invitation_timeline_list" id="mayhenTimelineList">
                    @foreach($timelineItems as $idx => $item)
                        @php
                            $tlTime = trim((string) ($item['time'] ?? '10:00'));
                            $tlTitle = trim((string) ($item['title'] ?? ''));
                        @endphp
                        <li class="mayhen_invitation_timeline_row">
                            <span class="mayhen_invitation_timeline_pill" data-sync-target="timeline.{{ $idx }}.time">{{ $tlTime }}</span>
                            <span class="mayhen_invitation_timeline_dot" aria-hidden="true"></span>
                            <span class="mayhen_invitation_timeline_text" data-sync-target="timeline.{{ $idx }}.title">{{ $tlTitle }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>

            {{-- ==================== SECTION 7: DRESSCODE (HIDDEN IN CURATED 7) ==================== --}}
            @if(!$isCuratedSeven)
                <section class="mayhen_invitation_section mayhen_invitation_dresscode mayhen_invitation_reveal"
                         id="mayhen_section_dresscode"
                         data-mayhen-section="dresscode">
                    <h2 class="mayhen_invitation_modern_heading">DRESS CODE</h2>
                    <p class="mayhen_invitation_modern_sub" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>

                    <div class="mayhen_invitation_swatches" id="mayhenDresscodeSwatches" data-sync-target="dresscode_colors">
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

            {{-- ==================== SECTION 8: EDITORIAL 10-PHOTO ALBUM ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_album mayhen_invitation_reveal"
                     id="mayhen_section_album"
                     data-mayhen-section="album">
                <h2 class="mayhen_invitation_modern_heading">ALBUM HÌNH CƯỚI</h2>
                <p class="mayhen_invitation_modern_sub">Chạm vào từng bức ảnh để xem phóng to trọn vẹn</p>

                <div class="mayhen_invitation_album_grid" id="mayhenAlbumGrid">
                    @foreach($galleryPhotos as $gIdx => $gUrl)
                        <button type="button"
                                class="mayhen_invitation_album_card{{ $gIdx === 0 || $gIdx === 5 ? ' mayhen_invitation_album_card--wide' : '' }}"
                                data-gallery-index="{{ $gIdx }}"
                                data-lightbox-src="{{ $gUrl }}"
                                aria-label="Xem ảnh cưới {{ $gIdx + 1 }}">
                            <img src="{{ $gUrl }}"
                                 alt="Khoảnh khắc cưới {{ $gIdx + 1 }} - {{ $coupleNames }}"
                                 data-sync-target="gallery.{{ $gIdx }}"
                                 loading="eager"
                                 width="320"
                                 height="420">
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- ==================== SECTION 9: GIFT & DYNAMIC VIETQR ==================== --}}
            @if(!$isCuratedSeven)
                <section class="mayhen_invitation_section mayhen_invitation_gift mayhen_invitation_reveal{{ $isEmbed ? ' is-gift-open' : '' }}"
                         id="mayhen_section_gift"
                         data-mayhen-section="gift">
                    <h2 class="mayhen_invitation_modern_heading">HỘP QUÀ MỪNG CƯỚI</h2>
                    <p class="mayhen_invitation_modern_sub">
                        Sự hiện diện của bạn là món quà lớn nhất. Nếu ở xa, bạn có thể gửi lời chúc qua mã VietQR dưới đây.
                    </p>

                    <button type="button"
                            class="mayhen_invitation_gift_btn"
                            id="mayhenGiftToggleBtn"
                            aria-expanded="{{ $isEmbed ? 'true' : 'false' }}"
                            aria-controls="mayhenGiftPanel">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="8" width="18" height="4" rx="1" />
                            <path d="M12 8v13M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7" />
                            <path d="M7.5 8a2.5 2.5 0 0 1 0-5C10 3 12 8 12 8s2-5 4.5-5a2.5 2.5 0 0 1 0 5" />
                        </svg>
                        <span>Mở Mã VietQR Mừng Cưới</span>
                    </button>

                    <div class="mayhen_invitation_gift_panel" id="mayhenGiftPanel">
                        <div class="mayhen_invitation_qr_grid">
                            <article class="mayhen_invitation_qr_card">
                                <span class="mayhen_invitation_qr_tag">NHÀ TRAI</span>
                                <button type="button"
                                        class="mayhen_invitation_qr_img_btn"
                                        data-lightbox-src="{{ $qrGroomUrl }}"
                                        aria-label="Phóng to mã QR Nhà Trai">
                                    <img src="{{ $qrGroomUrl }}"
                                         alt="Mã QR Chú rể {{ $groomName }}"
                                         class="mayhen_invitation_qr_img"
                                         data-sync-target="qr_groom_image"
                                         loading="lazy"
                                         width="180"
                                         height="180">
                                </button>
                                <p class="mayhen_invitation_qr_name" data-sync-target="groom_name">{{ $groomName }}</p>
                                <p class="mayhen_invitation_qr_bank" data-sync-target="groom_bank_name">{{ $groomBankName }}</p>
                                <p class="mayhen_invitation_qr_stk" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</p>
                                <p class="mayhen_invitation_qr_holder" data-sync-target="groom_account_name">{{ $groomAccountName }}</p>
                                <button type="button" class="mayhen_invitation_copy_btn" data-copy-stk="{{ $groomBankAccount }}">Sao chép STK</button>
                            </article>

                            <article class="mayhen_invitation_qr_card">
                                <span class="mayhen_invitation_qr_tag">NHÀ GÁI</span>
                                <button type="button"
                                        class="mayhen_invitation_qr_img_btn"
                                        data-lightbox-src="{{ $qrBrideUrl }}"
                                        aria-label="Phóng to mã QR Nhà Gái">
                                    <img src="{{ $qrBrideUrl }}"
                                         alt="Mã QR Cô dâu {{ $brideName }}"
                                         class="mayhen_invitation_qr_img"
                                         data-sync-target="qr_bride_image"
                                         loading="lazy"
                                         width="180"
                                         height="180">
                                </button>
                                <p class="mayhen_invitation_qr_name" data-sync-target="bride_name">{{ $brideName }}</p>
                                <p class="mayhen_invitation_qr_bank" data-sync-target="bride_bank_name">{{ $brideBankName }}</p>
                                <p class="mayhen_invitation_qr_stk" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</p>
                                <p class="mayhen_invitation_qr_holder" data-sync-target="bride_account_name">{{ $brideAccountName }}</p>
                                <button type="button" class="mayhen_invitation_copy_btn" data-copy-stk="{{ $brideBankAccount }}">Sao chép STK</button>
                            </article>
                        </div>
                    </div>
                </section>
            @endif

            {{-- ==================== SECTION 10: SIGNATURE, RSVP, WISHES & FINALE ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_ending mayhen_invitation_reveal"
                     id="mayhen_section_ending"
                     data-mayhen-section="ending">

                @if(!$isCuratedSeven)
                    {{-- 10A. SỔ KÝ TÊN LƯU BÚT (LAW #8: ZERO HUMAN PHOTOS) --}}
                    <div class="mayhen_invitation_sig_box" id="mayhen_section_signature" data-mayhen-section="signature">
                        <div class="mayhen_invitation_sig_icon" aria-hidden="true">
                            <svg viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="1.4" stroke-dasharray="3 3" />
                                <path d="M24 34s-10-6.2-10-13a5.8 5.8 0 0 1 10-3.9A5.8 5.8 0 0 1 34 21c0 6.8-10 13-10 13Z" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="1.6" />
                            </svg>
                        </div>
                        <h2 class="mayhen_invitation_modern_heading">SỔ KÝ TÊN LƯU BÚT</h2>
                        <p class="mayhen_invitation_modern_sub">
                            Lưu lại chữ ký kỷ niệm gửi tặng <span data-sync-target="couple_names">{{ $coupleNames }}</span>
                        </p>

                        <div class="mayhen_invitation_sig_wall" id="mayhenSignatureWall">
                            @forelse($savedSignatures as $sig)
                                <div class="mayhen_invitation_sig_card">
                                    <img src="{{ $sig->signature_url }}" alt="Chữ ký của {{ $sig->guest_name }}" loading="lazy">
                                    <span class="mayhen_invitation_sig_card_name">{{ $sig->guest_name }}</span>
                                </div>
                            @empty
                                <p class="mayhen_invitation_sig_empty">Chưa có chữ ký nào — hãy ký tên đầu tiên để chúc phúc đôi uyên ương!</p>
                            @endforelse
                        </div>

                        <div class="mayhen_invitation_sig_pad">
                            <label for="mayhenSignerName" class="mayhen_invitation_sr_only">Tên khách ký tên</label>
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
                                        height="156"
                                        aria-label="Khung vẽ chữ ký điện tử"></canvas>
                                <span class="mayhen_invitation_sig_placeholder" id="mayhenSigPlaceholder">Chạm hoặc kéo tay để ký tên tại đây</span>
                            </div>

                            <div class="mayhen_invitation_sig_toolbar">
                                <div class="mayhen_invitation_sig_inks" role="group" aria-label="Chọn màu mực">
                                    <button type="button" class="mayhen_invitation_ink_dot is-active" data-pen-color="#1F1D1A" aria-label="Mực đen cổ điển"></button>
                                    <button type="button" class="mayhen_invitation_ink_dot" data-pen-color="#9B2E33" aria-label="Mực đỏ nhung"></button>
                                    <button type="button" class="mayhen_invitation_ink_dot" data-pen-color="#6C8807" aria-label="Mực xanh non"></button>
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

                    {{-- 10B. XÁC NHẬN THAM DỰ RSVP (LAW #8 STRICT: PURE CRIMSON-VELVET GRADIENT STAGE — ZERO HUMAN PHOTOS) --}}
                    <div class="mayhen_invitation_rsvp_stage" id="mayhen_section_rsvp" data-mayhen-section="rsvp">
                        <div class="mayhen_invitation_rsvp_sparkles" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35 10.55 20.03C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54Z" /></svg>
                        </div>

                        <form class="mayhen_invitation_rsvp_card" id="mayhenRsvpForm" novalidate>
                            @csrf
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
                                <div class="mayhen_invitation_rsvp_radios">
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
                    <div class="mayhen_invitation_wishes_box" id="mayhen_section_wishes" data-mayhen-section="wishes">
                        <h2 class="mayhen_invitation_modern_heading">GỬI LỜI CHÚC PHÚC</h2>

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
                                    Hãy viết những lời chúc ngọt ngào đầu tiên dành cho {{ $coupleNames }}!
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
                                <label for="mayhenWishPreset" class="mayhen_invitation_label">Gợi ý lời chúc nhanh</label>
                                <select id="mayhenWishPreset" class="mayhen_invitation_select">
                                    <option value="">-- Tự soạn lời chúc riêng --</option>
                                    <option value="Chúc hai bạn trăm năm hạnh phúc, luôn yêu thương và đồng hành trọn đời!">Trăm năm hạnh phúc, đồng hành trọn đời!</option>
                                    <option value="Chúc mừng ngày trọng đại! Mong tổ ấm mới của hai bạn luôn ngập tràn tiếng cười.">Tổ ấm mới luôn ngập tràn tiếng cười!</option>
                                    <option value="Hôm nay hứa nhau rồi thì mãi mãi hạnh phúc bên nhau nhé đôi bạn trẻ!">Mãi mãi hạnh phúc bên nhau nhé!</option>
                                </select>
                            </div>

                            <div class="mayhen_invitation_field">
                                <label for="mayhenWishMessage" class="mayhen_invitation_label">Lời chúc *</label>
                                <textarea id="mayhenWishMessage"
                                          name="message"
                                          class="mayhen_invitation_textarea"
                                          placeholder="Viết lời chúc của bạn..."
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

                    {{-- 10D. THANK YOU PHOTO FINALE --}}
                    <div class="mayhen_invitation_finale">
                        <div class="mayhen_invitation_finale_photo_card">
                            <img src="{{ $footerPhoto }}"
                                 alt="Ảnh kết thiệp {{ $coupleNames }}"
                                 class="mayhen_invitation_finale_photo"
                                 data-sync-target="footer_photo_url"
                                 loading="lazy"
                                 width="366"
                                 height="480">
                        </div>
                        <h2 class="mayhen_invitation_finale_title" data-sync-target="thank_you_title">{{ $thankYouTitle }}</h2>
                        <p class="mayhen_invitation_finale_quote" data-sync-target="thank_you_quote">{{ $thankYouQuote }}</p>
                        <p class="mayhen_invitation_finale_names" data-sync-target="couple_names">{{ $coupleNames }}</p>
                    </div>
                @else
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
            <img src="{{ $galleryPhotos[0] }}" alt="Phóng to ảnh cưới" class="mayhen_invitation_lb_img" id="mayhenLbImg">
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
