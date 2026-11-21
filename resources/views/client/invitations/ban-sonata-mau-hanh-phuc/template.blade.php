@extends('client.layouts.invitation')

@php
    $templateSlug = 'ban-sonata-mau-hanh-phuc';
    $assetBase = "/mau-thiep/{$templateSlug}/assets";

    $isEmbed = ($isEmbed ?? false)
        || request()->boolean('embed')
        || request()->boolean('in_modal')
        || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);

    $extra = (isset($wedding) && is_array($wedding->extra_data)) ? $wedding->extra_data : [];
    $invConfig = (isset($invitation) && is_array($invitation->custom_config))
        ? $invitation->custom_config
        : (isset($invitation) ? (json_decode((string) ($invitation->custom_config ?? '{}'), true) ?: []) : []);

    $isCuratedSeven = !empty($isCurated)
        || (($template->category ?? '') === 'curated')
        || !empty($invConfig['curated_layout']);

    $val = function (string $key, mixed $default = '') use ($wedding, $extra, $invConfig) {
        if (isset($wedding) && isset($wedding->{$key}) && $wedding->{$key} !== '' && $wedding->{$key} !== null) {
            return $wedding->{$key};
        }
        if (array_key_exists($key, $extra) && $extra[$key] !== '' && $extra[$key] !== null) {
            return $extra[$key];
        }
        if (array_key_exists($key, $invConfig) && $invConfig[$key] !== '' && $invConfig[$key] !== null) {
            return $invConfig[$key];
        }
        return $default;
    };

    $resolveAsset = function (?string $path, string $fallbackRelative = 'images/hero-main.webp') use ($assetBase, $templateSlug) {
        $fallbackUrl = "{$assetBase}/" . ltrim($fallbackRelative, '/');
        if (empty($path) || !is_string($path)) {
            return $fallbackUrl;
        }
        $trimmed = trim($path);
        if (preg_match('#^https?://#i', $trimmed)) {
            return $fallbackUrl;
        }
        if (str_starts_with($trimmed, '/')) {
            return $trimmed;
        }
        $cleanRel = ltrim(preg_replace('#^assets/#i', '', $trimmed), '/');
        $diskCandidate = resource_path("views/client/invitations/{$templateSlug}/assets/{$cleanRel}");
        if (!file_exists($diskCandidate)) {
            return $fallbackUrl;
        }
        return "{$assetBase}/{$cleanRel}";
    };

    $groomName = (string) $val('groom_name', 'Hoàng Nam');
    $brideName = (string) $val('bride_name', 'Minh Anh');
    $coupleNames = "{$groomName} & {$brideName}";

    // Short names & initials
    $groomWords = preg_split('/\s+/u', trim($groomName)) ?: [$groomName];
    $brideWords = preg_split('/\s+/u', trim($brideName)) ?: [$brideName];
    $groomShort = end($groomWords) ?: $groomName;
    $brideShort = end($brideWords) ?: $brideName;
    $groomInitial = mb_strtoupper(mb_substr($groomShort, 0, 1));
    $brideInitial = mb_strtoupper(mb_substr($brideShort, 0, 1));

    $guestName = isset($guest) && !empty($guest->name)
        ? (string) $guest->name
        : (string) request()->query('guest', request()->query('to', 'Bạn và Gia Đình'));

    $ceremonyType = (string) $val('ceremony_type', 'You are invited to the wedding celebration of');
    $slogan = (string) $val('slogan', 'Together, we found home');

    $rawDate = $val('wedding_date', '2027-01-16');
    try {
        $carbonDate = \Carbon\Carbon::parse($rawDate);
    } catch (\Throwable $e) {
        $carbonDate = \Carbon\Carbon::parse('2027-01-16');
    }

    $dayNum = $carbonDate->format('d');
    $monthNum = $carbonDate->format('m');
    $yearNum = $carbonDate->format('Y');
    $isoDate = $carbonDate->format('Y-m-d');

    $weekdayMap = [
        0 => 'Chủ Nhật',
        1 => 'Thứ Hai',
        2 => 'Thứ Ba',
        3 => 'Thứ Tư',
        4 => 'Thứ Năm',
        5 => 'Thứ Sáu',
        6 => 'Thứ Bảy',
    ];
    $monthTextMap = [
        1 => 'Tháng Một', 2 => 'Tháng Hai', 3 => 'Tháng Ba', 4 => 'Tháng Tư',
        5 => 'Tháng Năm', 6 => 'Tháng Sáu', 7 => 'Tháng Bảy', 8 => 'Tháng Tám',
        9 => 'Tháng Chín', 10 => 'Tháng Mười', 11 => 'Tháng Mười Một', 12 => 'Tháng Mười Hai',
    ];
    $weekdayLabel = $weekdayMap[(int) $carbonDate->dayOfWeek] ?? 'Thứ Bảy';
    $monthTextLabel = $monthTextMap[(int) $carbonDate->month] ?? ('Tháng ' . $monthNum);
    $fullVietnameseDate = "{$weekdayLabel}, ngày {$dayNum} tháng {$monthNum} năm {$yearNum}";
    $lunarDate = (string) $val('wedding_lunar_date', '(Tức ngày 09 tháng 12 năm Bính Ngọ)');

    $groomFather = (string) $val('groom_father', 'Ông: Lê Văn Bình');
    $groomMother = (string) $val('groom_mother', 'Bà: Phạm Thị Mai');
    $groomParentsAddress = (string) $val('groom_parents_address', 'Phường Tây Hồ, Quận Tây Hồ, Hà Nội');

    $brideFather = (string) $val('bride_father', 'Ông: Nguyễn Văn An');
    $brideMother = (string) $val('bride_mother', 'Bà: Trần Thị Hoa');
    $brideParentsAddress = (string) $val('bride_parents_address', 'Phường Ba Đình, Quận Ba Đình, Hà Nội');

    $groomBio = (string) $val('groom_bio', 'Chàng trai ấm áp, điềm đạm, luôn tin rằng hạnh phúc lớn nhất là tìm thấy người cùng mình sẻ chia mọi khoảnh khắc bình dị.');
    $brideBio = (string) $val('bride_bio', 'Cô gái dịu dàng, yêu thiên nhiên và những điều lãng mạn, luôn rạng rỡ nụ cười khi bước đi bên người mình thương.');

    // Images
    $coverPhotoUrl = $resolveAsset((string) $val('cover_photo_url', ''), 'images/hero-main.webp');
    $openingPhotoUrl = $resolveAsset((string) $val('opening_photo_url', ''), 'images/opening.webp');
    $saveDatePhotoUrl = $resolveAsset((string) $val('save_date_photo_url', ''), 'images/std-main.webp');
    $groomPhotoUrl = $resolveAsset((string) $val('groom_photo_url', ''), 'images/groom.webp');
    $bridePhotoUrl = $resolveAsset((string) $val('bride_photo_url', ''), 'images/bride.webp');
    $invitationPhotoUrl = $resolveAsset((string) $val('invitation_photo_url', ''), 'images/invite-oval.webp');
    $ceremonyPhotoUrl = $resolveAsset((string) $val('ceremony_photo_url', ''), 'images/event-main.webp');
    $footerPhotoUrl = $resolveAsset((string) $val('footer_photo_url', ''), 'images/ending-main.webp');

    // Love Story
    $storyTitle = (string) $val('story_title', 'Love Story');
    $storySubtitle = (string) $val('story_subtitle', 'Chuyện tình');
    $coupleQuote = (string) $val('couple_quote', 'Giữa muôn vạn nốt nhạc của cuộc đời, chúng mình đã tìm thấy giai điệu bình yên nhất khi ở cạnh nhau.');
    $storyContent = (string) $val('story_content', 'Bắt đầu từ một ánh nhìn tình cờ năm 2019, đi qua bao mùa nắng gió và lời cầu hôn ngọt ngào dưới ánh nến năm 2023, hôm nay chúng mình chính thức nắm tay nhau bước vào ngôi nhà hạnh phúc.');
    $storyPhoto1 = $resolveAsset((string) $val('story_photo_1_url', ''), 'images/story-01.webp');
    $storyPhoto2 = $resolveAsset((string) $val('story_photo_2_url', ''), 'images/story-02.webp');
    $storyPhoto3 = $resolveAsset((string) $val('story_photo_3_url', ''), 'images/story-03.webp');

    // Events & Map
    $partyTime = (string) $val('party_time', '18:30');
    $partyVenue = (string) $val('party_venue', (string) $val('venue_name', 'Trung Tâm Tiệc Cưới Hồ Tây'));
    $partyAddress = (string) $val('event_party_address', (string) $val('venue_address', 'Số 88, đường Hồ Tây, Phường Tây Hồ, Hà Nội'));
    $ceremonyTime = (string) $val('ceremony_time', '09:00');
    $ceremonyAddress = (string) $val('ceremony_address', 'Số 88, đường Hồ Tây, Phường Tây Hồ, Hà Nội');
    $ceremonyGroomTime = (string) $val('ceremony_groom_time', '16:30');
    $ceremonyGroomAddress = (string) $val('ceremony_groom_address', 'Số 88, đường Hồ Tây, Phường Tây Hồ, Hà Nội');
    $mapLink = (string) $val('event_party_map_link', 'https://maps.google.com/?q=' . urlencode($partyVenue . ', ' . $partyAddress));
    $mapEmbedUrl = 'https://maps.google.com/maps?q=' . urlencode($partyVenue . ', ' . $partyAddress) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';

    // Countdown
    $countdownTitle = (string) $val('countdown_title', 'Countdown time');
    $targetDateRaw = (string) $val('event_target_date', $isoDate);
    $targetTimeRaw = (string) $val('event_target_time', $ceremonyGroomTime ?: '16:30');
    try {
        $targetCarbon = \Carbon\Carbon::parse("{$targetDateRaw} {$targetTimeRaw}");
    } catch (\Throwable $e) {
        $targetCarbon = $carbonDate->copy()->setTime(16, 30);
    }
    $targetIso = $targetCarbon->format('Y-m-d\TH:i:sP');

    $gcalStart = $targetCarbon->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
    $gcalEnd = $targetCarbon->copy()->addHours(3)->setTimezone('UTC')->format('Ymd\THis\Z');
    $gcalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . urlencode("Đám cưới {$groomName} & {$brideName}")
        . '&dates=' . $gcalStart . '/' . $gcalEnd
        . '&location=' . urlencode("{$partyVenue}, {$partyAddress}");

    // Timeline
    $defaultTimeline = [
        ['time' => '16:00', 'title' => 'Đón khách', 'desc' => 'Check-in và chụp hình cùng cô dâu chú rể'],
        ['time' => '16:30', 'title' => 'Nghi lễ', 'desc' => 'Khoảnh khắc thiêng liêng trao nhẫn cưới'],
        ['time' => '18:30', 'title' => 'Nhập tiệc', 'desc' => 'Khai tiệc và nâng ly chúc mừng hạnh phúc'],
    ];
    $rawTimeline = $val('timeline', $defaultTimeline);
    $timelineItems = (is_array($rawTimeline) && count($rawTimeline) > 0) ? array_values($rawTimeline) : $defaultTimeline;

    // Dresscode
    $dresscodeNote = (string) $val('dresscode_note', 'Chúng tôi rất mong quý khách lựa chọn trang phục theo các tông màu dưới đây để cùng hòa vào không khí buổi tiệc.');
    $dresscodeColorsRaw = (string) $val('dresscode_colors', '#858569,#F5F0E8,#D4C5A9,#C8A882');
    $dresscodeHexList = array_values(array_filter(array_map('trim', explode(',', $dresscodeColorsRaw))));
    if (empty($dresscodeHexList)) {
        $dresscodeHexList = ['#858569', '#F5F0E8', '#D4C5A9', '#C8A882'];
    }
    $dresscodeLabels = ['Xanh Ô Liu', 'Trắng Kem', 'Be Cát', 'Nâu Nude', 'Xanh Rêu', 'Kem Ánh Vàng'];

    // Gallery 10 photos
    $defaultGallery = [
        "{$assetBase}/images/album-01.webp",
        "{$assetBase}/images/album-02.webp",
        "{$assetBase}/images/album-03.webp",
        "{$assetBase}/images/album-04.webp",
        "{$assetBase}/images/album-05.webp",
        "{$assetBase}/images/album-06.webp",
        "{$assetBase}/images/album-07.webp",
        "{$assetBase}/images/album-08.webp",
        "{$assetBase}/images/album-09.webp",
        "{$assetBase}/images/album-10.webp",
    ];
    $rawGallery = $val('gallery', []);
    $galleryPhotos = [];
    for ($i = 0; $i < 10; $i++) {
        $candidate = is_array($rawGallery) && !empty($rawGallery[$i]) ? (string) $rawGallery[$i] : '';
        $fallbackImg = sprintf('images/album-%02d.webp', $i + 1);
        $galleryPhotos[$i] = $resolveAsset($candidate, $fallbackImg);
    }

    // Gift & VietQR
    $groomBankName = (string) $val('groom_bank_name', (string) $val('bank_name', 'Techcombank'));
    $groomBankAccount = (string) $val('groom_bank_account', (string) $val('bank_account_number', '190388889999'));
    $groomAccountName = (string) $val('groom_account_name', (string) $val('bank_account_name', 'LE HOANG NAM'));
    $rawGroomQr = (string) $val('qr_groom_image', '');
    $groomQrUrl = \App\Services\VietQrService::resolveQrImage(
        !empty($rawGroomQr) ? $resolveAsset($rawGroomQr, 'images/qr-default.webp') : null,
        $groomBankName,
        $groomBankAccount,
        $groomAccountName,
        "Mung cuoi {$groomShort} {$brideShort}"
    ) ?: $resolveAsset($rawGroomQr, 'images/qr-default.webp');

    $brideBankName = (string) $val('bride_bank_name', 'Vietcombank');
    $brideBankAccount = (string) $val('bride_bank_account', '001100998877');
    $brideAccountName = (string) $val('bride_account_name', 'NGUYEN MINH ANH');
    $rawBrideQr = (string) $val('qr_bride_image', '');
    $brideQrUrl = \App\Services\VietQrService::resolveQrImage(
        !empty($rawBrideQr) ? $resolveAsset($rawBrideQr, 'images/qr-default.webp') : null,
        $brideBankName,
        $brideBankAccount,
        $brideAccountName,
        "Mung cuoi {$groomShort} {$brideShort}"
    ) ?: $resolveAsset($rawBrideQr, 'images/qr-default.webp');

    // Ending
    $thankYouTitle = (string) $val('thank_you_title', 'Thank you!');
    $thankYouQuote = (string) $val('thank_you_quote', 'Sự hiện diện của bạn là niềm vinh hạnh cho gia đình chúng tôi. Chân thành cảm ơn!');
    $musicUrl = $resolveAsset((string) $val('music_url', ''), 'wedding-song.mp3');

    // Endpoints
    $hasRealInvitation = isset($invitation) && !empty($invitation->slug) && empty($isDemo);
    $rsvpEndpoint = !empty($rsvpSubmitUrl)
        ? $rsvpSubmitUrl
        : ($hasRealInvitation && \Illuminate\Support\Facades\Route::has('rsvp.submit')
            ? route('rsvp.submit', ['slug' => $invitation->slug])
            : '');
    $wishEndpoint = !empty($wishSubmitUrl)
        ? $wishSubmitUrl
        : ($hasRealInvitation && \Illuminate\Support\Facades\Route::has('invitation.wish.store')
            ? route('invitation.wish.store', ['slug' => $invitation->slug])
            : '');
    $signatureEndpoint = (isset($invitation) && !empty($invitation->id) && \Illuminate\Support\Facades\Route::has('invitation.signature.save'))
        ? route('invitation.signature.save', ['id' => $invitation->id])
        : '';

    $approvedWishes = (isset($wishes) && $wishes instanceof \Illuminate\Support\Collection) ? $wishes : collect();
    $savedSignatures = is_array($invConfig['signatures'] ?? null) ? $invConfig['signatures'] : [];
@endphp

@section('title', "Thiệp Cưới {$groomName} & {$brideName} — Bản Sonata Màu Hạnh Phúc")

@push('styles')
    <link rel="stylesheet" href="{{ asset_v("mau-thiep/{$templateSlug}/assets/styles.css") }}">
@endpush

@section('content')
<div
    id="mayhen_invitation_root"
    class="mayhen_invitation_root card-wrapper {{ $isEmbed ? 'mayhen_invitation_root--embed' : '' }}"
    data-template-slug="{{ $templateSlug }}"
    data-is-embed="{{ $isEmbed ? '1' : '0' }}"
    data-wedding-date="{{ $isoDate }}"
    data-target-iso="{{ $targetIso }}"
    data-couple-names="{{ $coupleNames }}"
    data-rsvp-endpoint="{{ $rsvpEndpoint }}"
    data-wish-endpoint="{{ $wishEndpoint }}"
    data-signature-endpoint="{{ $signatureEndpoint }}"
    data-csrf-token="{{ csrf_token() }}"
>
    {{-- Audio Player (Standard Mây Hẹn Contract) --}}
    <audio
        class="mayhen_invitation_audio"
        data-mayhen-audio
        data-sync-target="music_url"
        src="{{ $musicUrl }}"
        loop
        preload="none"
    ></audio>

    {{-- Floating Music Toggle Button --}}
    <button
        type="button"
        id="mayhenMusicFab"
        class="mayhen_invitation_music_fab"
        aria-label="Bật / Tắt nhạc nền"
        aria-pressed="false"
    >
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6Z"/>
        </svg>
    </button>

    {{-- Falling Olive Leaves Layer --}}
    <div id="mayhenPetalsLayer" class="mayhen_invitation_petals_layer" aria-hidden="true"></div>

    {{-- ==================== 0. MÀN MỞ ĐẦU (#w18Mo) ==================== --}}
    <div
        id="mayhenOpeningCurtain"
        class="mayhen_invitation_opening {{ $isEmbed ? 'mo tan da-mo is-opened' : '' }}"
        aria-label="Màn mở thiệp cưới"
        @if($isEmbed) style="display: none !important;" aria-hidden="true" @endif
    >
        <div class="nhom">
            <i class="hoa-mo p mayhen_invitation_tt" aria-hidden="true"></i>
            <i class="hoa-mo t mayhen_invitation_tt" aria-hidden="true"></i>
            <div class="oval">
                <i class="vien-t mayhen_invitation_tt" aria-hidden="true"></i>
                <i class="vien-p mayhen_invitation_tt" aria-hidden="true"></i>
                <i class="gon g1 mayhen_invitation_tt" aria-hidden="true"></i>
                <i class="gon g2 mayhen_invitation_tt" aria-hidden="true"></i>
                <i class="gon g3 mayhen_invitation_tt" aria-hidden="true"></i>
                <i class="vet t v1 mayhen_invitation_tt" aria-hidden="true"></i>
                <i class="vet t v2 mayhen_invitation_tt" aria-hidden="true"></i>
                <i class="vet t v3 mayhen_invitation_tt" aria-hidden="true"></i>
                <i class="vet p v1 mayhen_invitation_tt" aria-hidden="true"></i>
                <i class="vet p v2 mayhen_invitation_tt" aria-hidden="true"></i>
                <i class="vet p v3 mayhen_invitation_tt" aria-hidden="true"></i>
                <div class="khung-mo">
                    <div class="mayhen_invitation_edit_image">
                        <div class="mayhen_invitation_image_frame">
                            <img
                                src="{{ $openingPhotoUrl }}"
                                alt="{{ $coupleNames }}"
                                class="mayhen_invitation_image_tag"
                                data-sync-target="opening_photo_url"
                            >
                        </div>
                    </div>
                </div>
                <i class="mo-anh mayhen_invitation_tt" aria-hidden="true"></i>
                <div class="mayhen_invitation_edit_text than-gui">
                    <p class="mayhen_invitation_paragraph">Thân gửi</p>
                </div>
                <div class="mayhen_invitation_edit_text khach mayhen_invitation_viet mayhen_invitation_ten_khach">
                    <h2 class="mayhen_invitation_headline" data-sync-target="guest_name">{{ $guestName }}</h2>
                </div>
            </div>
            <button type="button" class="mayhen_invitation_nut_mo" id="mayhenOpenInvitationBtn">Ấn vào để Xem</button>
            <div class="mayhen_invitation_edit_text ten-mo mayhen_invitation_mot_dong">
                <h1 class="mayhen_invitation_headline" data-sync-target="couple_names">{{ $groomShort }} &amp; {{ $brideShort }}</h1>
            </div>
        </div>
    </div>

    {{-- ==================== KHUNG THIỆP CHÍNH (#w18Thiep) ==================== --}}
    <div id="mayhenInvitationWrap" class="mayhen_invitation_wrap">

        {{-- ==================== SECTION 1: HERO COVER & SAVE THE DATE (data-mayhen-section="hero") ==================== --}}
        <section
            id="mayhen_section_hero"
            data-mayhen-section="hero"
            class="mayhen_invitation_section_group"
        >
            {{-- 1A. ĐẦU THIỆP (#SECTION_DAU) --}}
            <div class="mayhen_invitation_phan mayhen_invitation_dau">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_the_dau">
                    <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                        <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-la-net);--f:saturate(33%) hue-rotate(24deg);--mx:293;--my:37.3%;--mw:200;--mh:163;--mbx:0;--mby:0;--mbw:200;--mbh:163;--dx:560;--dy:37.3%;--dw:265;--dh:216;--dbx:0;--dby:0;--dbw:265;--dbh:216" aria-hidden="true"></i>
                        <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-oai-huong);--f:saturate(33%) hue-rotate(24deg);--mx:252;--my:56%;--mw:186;--mh:256;--mbx:0;--mby:0;--mbw:186;--mbh:256;--dx:506;--dy:56%;--dw:247;--dh:339;--dbx:0;--dby:0;--dbw:247;--dbh:339" aria-hidden="true"></i>
                        <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-cum-hoa);--f:saturate(33%) hue-rotate(24deg);--mx:-9;--my:55.1%;--mw:137;--mh:173;--mbx:0;--mby:0;--mbw:137;--mbh:173;--dx:159;--dy:55.1%;--dw:182;--dh:229;--dbx:0;--dby:0;--dbw:182;--dbh:229" aria-hidden="true"></i>
                        <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-hong-net);--f:saturate(33%) hue-rotate(24deg);--mx:260;--my:84.8%;--mw:223;--mh:233;--mbx:0;--mby:0;--mbw:223;--mbh:233;--dx:516;--dy:84.8%;--dw:296;--dh:309;--dbx:0;--dby:0;--dbw:296;--dbh:309" aria-hidden="true"></i>
                    </div>
                    <div class="anh-dau mayhen_invitation_anh mayhen_invitation_av mayhen_invitation_hien" data-hieu-ung="nen" data-av="man">
                        <div class="mayhen_invitation_edit_image">
                            <div class="mayhen_invitation_image_frame">
                                <img
                                    src="{{ $coverPhotoUrl }}"
                                    alt="{{ $coupleNames }}"
                                    class="mayhen_invitation_image_tag"
                                    data-sync-target="cover_photo_url"
                                >
                            </div>
                        </div>
                    </div>
                    <i class="mo-duoi mayhen_invitation_tt" aria-hidden="true"></i>
                    <div class="mayhen_invitation_edit_text moi-dong mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                        <h3 class="mayhen_invitation_headline" data-sync-target="ceremony_type">{{ $ceremonyType }}</h3>
                    </div>
                    <div class="mayhen_invitation_edit_text ten-dau mayhen_invitation_mot_dong mayhen_invitation_hien" data-hieu-ung="len">
                        <h1 class="mayhen_invitation_headline">
                            <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                        </h1>
                    </div>
                    <i class="hoa-van mayhen_invitation_tt mayhen_invitation_hien" data-hieu-ung="len" aria-hidden="true"></i>
                </div>
            </div>

            {{-- 1B. SAVE THE DATE (#SECTION_STD) --}}
            <div class="mayhen_invitation_phan mayhen_invitation_std">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-la-net);--f:saturate(33%) hue-rotate(24deg);--mx:-94;--my:31%;--mw:200;--mh:163;--mbx:0;--mby:0;--mbw:200;--mbh:163;--dx:40;--dy:27.9%;--dw:200;--dh:163;--dbx:0;--dby:0;--dbw:200;--dbh:163" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-cum-hoa);--f:saturate(33%) hue-rotate(24deg);--mx:-91;--my:47.5%;--mw:197;--mh:248;--mbx:0;--mby:0;--mbw:197;--mbh:248;--dx:43;--dy:42.8%;--dw:197;--dh:248;--dbx:0;--dby:0;--dbw:197;--dbh:248" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-hong-net);--f:saturate(33%) hue-rotate(24deg);--mx:286;--my:14.2%;--mw:223;--mh:233;--mbx:0;--mby:0;--mbw:223;--mbh:233;--dx:628;--dy:11.6%;--dw:296;--dh:309;--dbx:0;--dby:0;--dbw:296;--dbh:309" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-nu-hoa);--f:saturate(33%) hue-rotate(24deg);--mx:341;--my:24.8%;--mw:99;--mh:88;--mbx:0;--mby:0;--mbw:99;--mbh:88;--dx:679;--dy:27%;--dw:99;--dh:88;--dbx:0;--dby:0;--dbw:99;--dbh:88" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-canh-nam);--f:saturate(33%) hue-rotate(24deg);--mx:240;--my:73.9%;--mw:200;--mh:164;--mbx:0;--mby:0;--mbw:200;--mbh:164;--dx:644;--dy:66.1%;--dw:200;--dh:164;--dbx:0;--dby:0;--dbw:200;--dbh:164" aria-hidden="true"></i>
                </div>
                <div class="mayhen_invitation_khung">
                    <div class="mayhen_invitation_edit_text cau mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                        <h3 class="mayhen_invitation_headline" data-sync-target="slogan">{{ $slogan }}</h3>
                    </div>
                    <div class="mayhen_invitation_dong_std">
                        <div class="mayhen_invitation_edit_text s mayhen_invitation_mot_dong mayhen_invitation_hien" data-hieu-ung="trai">
                            <h1 class="mayhen_invitation_headline">Save</h1>
                        </div>
                        <div class="mayhen_invitation_edit_text the mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="nay">
                            <h1 class="mayhen_invitation_headline">the</h1>
                        </div>
                        <div class="mayhen_invitation_edit_text s p mayhen_invitation_mot_dong mayhen_invitation_hien" data-hieu-ung="phai">
                            <h1 class="mayhen_invitation_headline">Date</h1>
                        </div>
                    </div>
                    <div class="mayhen_invitation_anh_std mayhen_invitation_anh mayhen_invitation_av mayhen_invitation_hien" data-hieu-ung="khung" data-av="zoom">
                        <div class="mayhen_invitation_edit_image">
                            <div class="mayhen_invitation_image_frame">
                                <img
                                    src="{{ $saveDatePhotoUrl }}"
                                    alt="Save The Date {{ $coupleNames }}"
                                    class="mayhen_invitation_image_tag"
                                    data-sync-target="save_date_photo_url"
                                >
                            </div>
                        </div>
                    </div>
                    <div class="mayhen_invitation_chu_dau">
                        <div class="mayhen_invitation_edit_text c mayhen_invitation_hien" data-hieu-ung="trai">
                            <h1 class="mayhen_invitation_headline" data-groom-initial>{{ $groomInitial }}</h1>
                        </div>
                        <div class="va mayhen_invitation_tt mayhen_invitation_hien" data-hieu-ung="nay" aria-hidden="true">&amp;</div>
                        <div class="mayhen_invitation_edit_text c p mayhen_invitation_hien" data-hieu-ung="phai">
                            <h1 class="mayhen_invitation_headline" data-bride-initial>{{ $brideInitial }}</h1>
                        </div>
                    </div>
                    <div class="mayhen_invitation_luoi_ngay">
                        <div class="ben mayhen_invitation_hien" data-hieu-ung="trai">
                            <div class="mayhen_invitation_edit_text m1 mayhen_invitation_mot_dong">
                                <h3 class="mayhen_invitation_headline" data-std-weekday>{{ $weekdayLabel }}</h3>
                            </div>
                            <div class="mayhen_invitation_edit_text m2 mayhen_invitation_mot_dong">
                                <h3 class="mayhen_invitation_headline" data-sync-target="ceremony_groom_time">{{ $ceremonyGroomTime }}</h3>
                            </div>
                        </div>
                        <div class="mayhen_invitation_edit_text so mayhen_invitation_mot_dong mayhen_invitation_hien" data-hieu-ung="nay">
                            <h1 class="mayhen_invitation_headline" data-std-day>{{ $dayNum }}</h1>
                        </div>
                        <div class="ben p mayhen_invitation_hien" data-hieu-ung="phai">
                            <div class="mayhen_invitation_edit_text m1 mayhen_invitation_mot_dong">
                                <h3 class="mayhen_invitation_headline" data-std-month>{{ $monthTextLabel }}</h3>
                            </div>
                            <div class="mayhen_invitation_edit_text m2 mayhen_invitation_mot_dong">
                                <h3 class="mayhen_invitation_headline" data-std-year>{{ $yearNum }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== SECTION 2: CALENDAR & COUNTDOWN (data-mayhen-section="countdown_calendar") ==================== --}}
        <section
            id="mayhen_section_countdown_calendar"
            data-mayhen-section="countdown_calendar"
            class="mayhen_invitation_section_group"
        >
            {{-- 2A. LỊCH THÁNG (#SECTION_LICH) --}}
            <div class="mayhen_invitation_phan mayhen_invitation_lich">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-hong-trang);--f:opacity(.38);--mx:260;--my:65.1%;--mw:223;--mh:233;--mbx:0;--mby:0;--mbw:223;--mbh:233;--dx:772;--dy:54%;--dw:296;--dh:309;--dbx:0;--dby:0;--dbw:296;--dbh:309" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-nu-trang);--f:opacity(.39);--mx:315;--my:79%;--mw:99;--mh:88;--mbx:0;--mby:0;--mbw:99;--mbh:88;--dx:860;--dy:73.9%;--dw:99;--dh:88;--dbx:0;--dby:0;--dbw:99;--dbh:88" aria-hidden="true"></i>
                </div>
                <div class="mayhen_invitation_khung">
                    <div class="dau-lich">
                        <div class="mayhen_invitation_edit_text thang mayhen_invitation_viet mayhen_invitation_mot_dong mayhen_invitation_hien" data-hieu-ung="viet">
                            <h2 class="mayhen_invitation_headline" data-cal-month-heading>Tháng {{ $monthNum }}</h2>
                        </div>
                        <div class="mayhen_invitation_edit_text nam mayhen_invitation_viet mayhen_invitation_mot_dong mayhen_invitation_hien" data-hieu-ung="viet">
                            <h1 class="mayhen_invitation_headline" data-cal-year-heading>{{ $yearNum }}</h1>
                        </div>
                    </div>
                    <div class="mayhen_invitation_cho_lich mayhen_invitation_hien" data-hieu-ung="len">
                        <div class="mayhen_invitation_calendar_wrapper">
                            <div class="mayhen_invitation_calendar_header">
                                <div>T2</div>
                                <div>T3</div>
                                <div>T4</div>
                                <div>T5</div>
                                <div>T6</div>
                                <div>T7</div>
                                <div>CN</div>
                            </div>
                            <div class="mayhen_invitation_calendar_body" id="mayhenCalendarBody"></div>
                        </div>
                    </div>
                    <div class="mayhen_invitation_add_cal_wrap mayhen_invitation_hien" data-hieu-ung="len">
                        <a
                            href="{{ $gcalUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            id="mayhenAddCalLink"
                            class="mayhen_invitation_add_cal_link"
                        >
                            <span>Thêm vào lịch</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 2B. ĐẾM NGƯỢC (#SECTION_DEM — HUY CHƯƠNG TRÒN V2) --}}
            <div class="mayhen_invitation_phan mayhen_invitation_dem">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-hoa-day);--mx:-36;--my:-12%;--mw:120;--mh:268;--mbx:0;--mby:0;--mbw:120;--mbh:268;--dx:-80;--dy:-18%;--dw:180;--dh:402;--dbx:0;--dby:0;--dbw:180;--dbh:402" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-hoa-day);--mx:336;--my:10%;--mw:120;--mh:268;--mbx:0;--mby:0;--mbw:120;--mbh:268;--dx:860;--dy:6%;--dw:180;--dh:402;--dbx:0;--dby:0;--dbw:180;--dbh:402" aria-hidden="true"></i>
                </div>
                <div class="mayhen_invitation_khung">
                    <div class="mayhen_invitation_edit_text td1 mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                        <p class="mayhen_invitation_paragraph" data-sync-target="countdown_title">{{ $countdownTitle }}</p>
                    </div>
                    <div class="mayhen_invitation_edit_text td2 mayhen_invitation_hien" data-hieu-ung="len">
                        <p class="mayhen_invitation_paragraph">THỜI GIAN ĐẾM NGƯỢC</p>
                    </div>
                    <div
                        id="mayhenCountdownBox"
                        class="mayhen_invitation_cho_dem mayhen_invitation_hien"
                        data-hieu-ung="len"
                        data-countdown="{{ $targetIso }}"
                    >
                        <div class="mayhen_invitation_cd_item" style="--dl:.1s">
                            <span class="mayhen_invitation_cd_number" data-cd-days>00</span>
                            <span class="mayhen_invitation_cd_label">Ngày</span>
                        </div>
                        <span class="mayhen_invitation_cd_sep">:</span>
                        <div class="mayhen_invitation_cd_item" style="--dl:.3s">
                            <span class="mayhen_invitation_cd_number" data-cd-hours>00</span>
                            <span class="mayhen_invitation_cd_label">Giờ</span>
                        </div>
                        <span class="mayhen_invitation_cd_sep">:</span>
                        <div class="mayhen_invitation_cd_item" style="--dl:.5s">
                            <span class="mayhen_invitation_cd_number" data-cd-mins>00</span>
                            <span class="mayhen_invitation_cd_label">Phút</span>
                        </div>
                        <span class="mayhen_invitation_cd_sep">:</span>
                        <div class="mayhen_invitation_cd_item" style="--dl:.7s">
                            <span class="mayhen_invitation_cd_number" data-cd-secs>00</span>
                            <span class="mayhen_invitation_cd_label">Giây</span>
                        </div>
                    </div>
                    <i class="mayhen_invitation_dong_ho mayhen_invitation_tt mayhen_invitation_hien" data-hieu-ung="nay" aria-hidden="true"></i>
                </div>
            </div>
        </section>

        {{-- ==================== SECTION 3: COUPLE PORTRAITS & FORMAL INVITATION (data-mayhen-section="parents") ==================== --}}
        <section
            id="mayhen_section_parents"
            data-mayhen-section="parents"
            class="mayhen_invitation_section_group"
        >
            {{-- 3A. CÔ DÂU CHÚ RỂ (#SECTION_CD) --}}
            <div class="mayhen_invitation_phan mayhen_invitation_cd">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-xi-trai);--f:saturate(33%) hue-rotate(24deg);--mx:-27;--my:-0.4%;--mw:122;--mh:144;--mbx:0;--mby:0;--mbw:122;--mbh:144;--dx:-124;--dy:46.8%;--dw:182;--dh:216;--dbx:0;--dby:0;--dbw:182;--dbh:216" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-xi-phai);--f:saturate(33%) hue-rotate(24deg);--mx:332;--my:-0.6%;--mw:122;--mh:144;--mbx:0;--mby:0;--mbw:122;--mbh:144;--dx:902;--dy:45.6%;--dw:182;--dh:216;--dbx:0;--dby:0;--dbw:182;--dbh:216" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh chi-dt" style="--a:var(--a-mang-phai);--dx:-360;--dy:0%;--dw:360;--dh:250;--dbx:0;--dby:0;--dbw:360;--dbh:250" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh chi-dt" style="--a:var(--a-mang-trai);--dx:0;--dy:0%;--dw:360;--dh:250;--dbx:0;--dby:0;--dbw:360;--dbh:250" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh chi-dt" style="--a:var(--a-mang-phai);--dx:600;--dy:0%;--dw:360;--dh:250;--dbx:0;--dby:0;--dbw:360;--dbh:250" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh chi-dt" style="--a:var(--a-mang-trai);--dx:960;--dy:0%;--dw:360;--dh:250;--dbx:0;--dby:0;--dbw:360;--dbh:250" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-hoa-dai);--f:saturate(33%) hue-rotate(24deg);--mx:0;--my:42.2%;--mw:232;--mh:302;--mbx:0;--mby:0;--mbw:232;--mbh:302;--dx:-222;--dy:58%;--dw:289;--dh:376;--dbx:0;--dby:0;--dbw:289;--dbh:376" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-canh-mam);--f:saturate(33%) hue-rotate(24deg);--mx:331;--my:47.1%;--mw:105;--mh:158;--mbx:0;--mby:0;--mbw:148;--mbh:192;--dx:854;--dy:69.7%;--dw:223;--dh:289;--dbx:0;--dby:0;--dbw:223;--dbh:289" aria-hidden="true"></i>
                </div>
                <div class="mayhen_invitation_khung">
                    {{-- Chú Rể --}}
                    <div class="mayhen_invitation_nguoi">
                        <div class="hinh mayhen_invitation_av mayhen_invitation_hien" data-hieu-ung="trai" data-av="quet">
                            <div class="mayhen_invitation_edit_image">
                                <div class="mayhen_invitation_image_frame">
                                    <img
                                        src="{{ $groomPhotoUrl }}"
                                        alt="Chú rể {{ $groomName }}"
                                        class="mayhen_invitation_image_tag"
                                        data-sync-target="groom_photo_url"
                                    >
                                </div>
                            </div>
                            <i class="mo-anh mayhen_invitation_tt" aria-hidden="true"></i>
                            <div class="tren-anh">
                                <div class="mayhen_invitation_edit_text vai mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                                    <p class="mayhen_invitation_paragraph">The groom</p>
                                </div>
                                <div class="mayhen_invitation_edit_text ten mayhen_invitation_mot_dong">
                                    <h2 class="mayhen_invitation_headline" data-sync-target="groom_name">{{ $groomName }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="mayhen_invitation_edit_text gt mayhen_invitation_hien" data-hieu-ung="len">
                            <p class="mayhen_invitation_paragraph" data-sync-target="groom_bio">{{ $groomBio }}</p>
                        </div>
                    </div>

                    {{-- Cô Dâu --}}
                    <div class="mayhen_invitation_nguoi">
                        <div class="hinh mayhen_invitation_av mayhen_invitation_hien" data-hieu-ung="phai" data-av="quet">
                            <div class="mayhen_invitation_edit_image">
                                <div class="mayhen_invitation_image_frame">
                                    <img
                                        src="{{ $bridePhotoUrl }}"
                                        alt="Cô dâu {{ $brideName }}"
                                        class="mayhen_invitation_image_tag"
                                        data-sync-target="bride_photo_url"
                                    >
                                </div>
                            </div>
                            <i class="mo-anh mayhen_invitation_tt" aria-hidden="true"></i>
                            <div class="tren-anh">
                                <div class="mayhen_invitation_edit_text vai mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                                    <p class="mayhen_invitation_paragraph">The bride</p>
                                </div>
                                <div class="mayhen_invitation_edit_text ten mayhen_invitation_mot_dong">
                                    <h2 class="mayhen_invitation_headline" data-sync-target="bride_name">{{ $brideName }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="mayhen_invitation_edit_text gt mayhen_invitation_hien" data-hieu-ung="len">
                            <p class="mayhen_invitation_paragraph" data-sync-target="bride_bio">{{ $brideBio }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3B. THƯ MỜI GIA ĐÌNH HAI BÊN (#SECTION_MOI) --}}
            <div class="mayhen_invitation_phan mayhen_invitation_moi">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt tren" aria-hidden="true">
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-hoa-v-trai);--f:saturate(33%) hue-rotate(24deg);--mx:-40;--my:67.9%;--mw:281;--mh:282;--mbx:0;--mby:0;--mbw:281;--mbh:282;--dx:-125;--dy:60.6%;--dw:281;--dh:282;--dbx:0;--dby:0;--dbw:281;--dbh:282" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-hoa-v-phai);--f:saturate(33%) hue-rotate(24deg);--mx:278;--my:48.9%;--mw:272;--mh:215;--mbx:0;--mby:0;--mbw:272;--mbh:215;--dx:852;--dy:11%;--dw:272;--dh:215;--dbx:0;--dby:0;--dbw:272;--dbh:215" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-bo-len);--f:saturate(33%) hue-rotate(24deg);--mx:293;--my:88.7%;--mw:157;--mh:228;--mbx:0;--mby:0;--mbw:195;--mbh:284;--dx:798;--dy:73%;--dw:204;--dh:297;--dbx:0;--dby:0;--dbw:253;--dbh:368" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-bo-ru);--f:saturate(33%) hue-rotate(24deg);--mx:275;--my:0%;--mw:145;--mh:140;--mbx:0;--mby:-105;--mbw:145;--mbh:246;--dx:-115;--dy:0%;--dw:189;--dh:182;--dbx:0;--dby:-137;--dbw:189;--dbh:319" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-vang-trai);--f:saturate(33%) hue-rotate(24deg);--mx:-4;--my:0%;--mw:118;--mh:190;--mbx:0;--mby:-129;--mbw:118;--mbh:319;--dx:821;--dy:0%;--dw:153;--dh:246;--dbx:0;--dby:-168;--dbw:153;--dbh:415" aria-hidden="true"></i>
                </div>
                <div class="mayhen_invitation_khung">
                    <div class="mayhen_invitation_the_moi mayhen_invitation_hien" data-hieu-ung="len">
                        <i class="bo-tren mayhen_invitation_tt" aria-hidden="true"></i>
                        <div class="mayhen_invitation_edit_text kinh mayhen_invitation_hien" data-hieu-ung="len">
                            <h3 class="mayhen_invitation_headline">TRÂN TRỌNG KÍNH MỜI</h3>
                        </div>
                        <div class="mayhen_invitation_edit_text khach mayhen_invitation_viet mayhen_invitation_ten_khach mayhen_invitation_hien" data-hieu-ung="viet">
                            <h2 class="mayhen_invitation_headline" data-sync-target="guest_name">{{ $guestName }}</h2>
                        </div>
                        <div class="mayhen_invitation_edit_text toi-du mayhen_invitation_hien" data-hieu-ung="len">
                            <p class="mayhen_invitation_paragraph">TỚI DỰ BỮA TIỆC THÂN MẬT<br>CHUNG VUI CÙNG GIA ĐÌNH CHÚNG TÔI MỪNG LỄ THÀNH HÔN CỦA HAI CON</p>
                        </div>
                        <div class="mayhen_invitation_ten_moi">
                            <div class="mayhen_invitation_edit_text ten mayhen_invitation_viet mayhen_invitation_mot_dong mayhen_invitation_hien" data-hieu-ung="trai">
                                <h2 class="mayhen_invitation_headline" data-sync-target="groom_name">{{ $groomName }}</h2>
                            </div>
                            <div class="mayhen_invitation_edit_text va mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="nay">
                                <p class="mayhen_invitation_paragraph">và</p>
                            </div>
                            <div class="mayhen_invitation_edit_text ten mayhen_invitation_viet mayhen_invitation_mot_dong mayhen_invitation_hien" data-hieu-ung="phai">
                                <h2 class="mayhen_invitation_headline" data-sync-target="bride_name">{{ $brideName }}</h2>
                            </div>
                        </div>

                        <div class="mayhen_invitation_vien_oval mayhen_invitation_anh mayhen_invitation_av mayhen_invitation_hien" data-hieu-ung="khung" data-av="zoom">
                            <div class="mayhen_invitation_oval">
                                <div class="mayhen_invitation_edit_image">
                                    <div class="mayhen_invitation_image_frame">
                                        <img
                                            src="{{ $invitationPhotoUrl }}"
                                            alt="Thư mời {{ $coupleNames }}"
                                            class="mayhen_invitation_image_tag"
                                            data-sync-target="invitation_photo_url"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mayhen_invitation_ho">
                            <div class="mayhen_invitation_hien" data-hieu-ung="trai">
                                <div class="mayhen_invitation_edit_text nha">
                                    <p class="mayhen_invitation_paragraph">Nhà trai</p>
                                </div>
                                <div class="mayhen_invitation_edit_text bo-me">
                                    <p class="mayhen_invitation_paragraph" data-sync-target="groom_father">{{ $groomFather }}</p>
                                    <p class="mayhen_invitation_paragraph" data-sync-target="groom_mother">{{ $groomMother }}</p>
                                    <p class="mayhen_invitation_paragraph" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                                </div>
                            </div>
                            <i class="ke mayhen_invitation_tt" aria-hidden="true"></i>
                            <div class="mayhen_invitation_hien" data-hieu-ung="phai">
                                <div class="mayhen_invitation_edit_text nha">
                                    <p class="mayhen_invitation_paragraph">Nhà gái</p>
                                </div>
                                <div class="mayhen_invitation_edit_text bo-me">
                                    <p class="mayhen_invitation_paragraph" data-sync-target="bride_father">{{ $brideFather }}</p>
                                    <p class="mayhen_invitation_paragraph" data-sync-target="bride_mother">{{ $brideMother }}</p>
                                    <p class="mayhen_invitation_paragraph" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mayhen_invitation_edit_text gio mayhen_invitation_hien" data-hieu-ung="len">
                            <p class="mayhen_invitation_paragraph">
                                TỔ CHỨC VÀO LÚC <span data-sync-target="party_time">{{ $partyTime }}</span><br>
                                <span data-sync-target="wedding_date">{{ $fullVietnameseDate }}</span>
                            </p>
                        </div>
                        <div class="mayhen_invitation_edit_text am mayhen_invitation_hien" data-hieu-ung="len">
                            <p class="mayhen_invitation_paragraph" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</p>
                        </div>
                        <div class="mayhen_invitation_edit_text tai mayhen_invitation_hien" data-hieu-ung="len">
                            <p class="mayhen_invitation_paragraph">TẠI</p>
                        </div>
                        <div class="mayhen_invitation_edit_text nha-to mayhen_invitation_hien" data-hieu-ung="nay">
                            <h2 class="mayhen_invitation_headline" data-sync-target="party_venue">{{ $partyVenue }}</h2>
                        </div>
                        <div class="mayhen_invitation_edit_text dc mayhen_invitation_hien" data-hieu-ung="len">
                            <p class="mayhen_invitation_paragraph" data-sync-target="event_party_address">{{ $partyAddress }}</p>
                        </div>
                        <button type="button" class="mayhen_invitation_nut nut-cd mayhen_invitation_hien" data-hieu-ung="nay" data-scroll-to="#mayhen_map_postcard">
                            <span class="mayhen_invitation_headline">Chỉ đường</span>
                        </button>
                        <div class="mayhen_invitation_edit_text han mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                            <p class="mayhen_invitation_paragraph">Rất hân hạnh được đón tiếp!</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== SECTION 4: LOVE STORY (data-mayhen-section="story") ==================== --}}
        <section
            id="mayhen_section_story"
            data-mayhen-section="story"
            class="mayhen_invitation_phan mayhen_invitation_tr"
        >
            <div class="mayhen_invitation_section_bg"></div>
            <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-oai-huong);--f:saturate(33%) hue-rotate(24deg) opacity(.45);--mx:-46;--my:16%;--mw:150;--mh:206;--mbx:0;--mby:0;--mbw:150;--mbh:206;--dx:-120;--dy:14%;--dw:220;--dh:302;--dbx:0;--dby:0;--dbw:220;--dbh:302" aria-hidden="true"></i>
                <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-cum-hoa);--f:saturate(33%) hue-rotate(24deg) opacity(.45);--mx:320;--my:62%;--mw:140;--mh:176;--mbx:0;--mby:0;--mbw:140;--mbh:176;--dx:850;--dy:58%;--dw:200;--dh:252;--dbx:0;--dby:0;--dbw:200;--dbh:252" aria-hidden="true"></i>
            </div>
            <div class="mayhen_invitation_khung">
                <div class="mayhen_invitation_td_que">
                    <i class="mayhen_invitation_tt t mayhen_invitation_hien" data-hieu-ung="trai" aria-hidden="true"></i>
                    <div>
                        <div class="mayhen_invitation_edit_text td1 mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                            <h2 class="mayhen_invitation_headline" data-sync-target="story_title">{{ $storyTitle }}</h2>
                        </div>
                        <div class="mayhen_invitation_edit_text td2 mayhen_invitation_hien" data-hieu-ung="len">
                            <p class="mayhen_invitation_paragraph" data-sync-target="story_subtitle">{{ $storySubtitle }}</p>
                        </div>
                    </div>
                    <i class="mayhen_invitation_tt p mayhen_invitation_hien" data-hieu-ung="phai" aria-hidden="true"></i>
                </div>

                <div class="mayhen_invitation_truyen_ds">
                    {{-- Chương 1: 2019 --}}
                    <div class="mayhen_invitation_chuong mayhen_invitation_hien" data-hieu-ung="trai">
                        <div class="hinh-tr mayhen_invitation_anh mayhen_invitation_av" data-av="man">
                            <div class="mayhen_invitation_edit_image">
                                <div class="mayhen_invitation_image_frame">
                                    <img
                                        src="{{ $storyPhoto1 }}"
                                        alt="Lần đầu gặp gỡ"
                                        class="mayhen_invitation_image_tag"
                                        data-sync-target="story_photo_1_url"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="chu-tr">
                            <div class="mayhen_invitation_edit_text nam-tr">
                                <h3 class="mayhen_invitation_headline">2019</h3>
                            </div>
                            <div class="mayhen_invitation_edit_text ten-tr">
                                <h3 class="mayhen_invitation_headline">Lần đầu gặp gỡ</h3>
                            </div>
                            <div class="mayhen_invitation_edit_text nd-tr">
                                <p class="mayhen_invitation_paragraph" data-sync-target="couple_quote">{{ $coupleQuote }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Chương 2: 2023 --}}
                    <div class="mayhen_invitation_chuong mayhen_invitation_chan mayhen_invitation_hien" data-hieu-ung="phai">
                        <div class="hinh-tr mayhen_invitation_anh mayhen_invitation_av" data-av="man">
                            <div class="mayhen_invitation_edit_image">
                                <div class="mayhen_invitation_image_frame">
                                    <img
                                        src="{{ $storyPhoto2 }}"
                                        alt="Lời cầu hôn"
                                        class="mayhen_invitation_image_tag"
                                        data-sync-target="story_photo_2_url"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="chu-tr">
                            <div class="mayhen_invitation_edit_text nam-tr">
                                <h3 class="mayhen_invitation_headline">2023</h3>
                            </div>
                            <div class="mayhen_invitation_edit_text ten-tr">
                                <h3 class="mayhen_invitation_headline">Lời cầu hôn</h3>
                            </div>
                            <div class="mayhen_invitation_edit_text nd-tr">
                                <p class="mayhen_invitation_paragraph" data-sync-target="story_content">{{ $storyContent }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Chương 3: 2027 --}}
                    <div class="mayhen_invitation_chuong mayhen_invitation_hien" data-hieu-ung="trai">
                        <div class="hinh-tr mayhen_invitation_anh mayhen_invitation_av" data-av="man">
                            <div class="mayhen_invitation_edit_image">
                                <div class="mayhen_invitation_image_frame">
                                    <img
                                        src="{{ $storyPhoto3 }}"
                                        alt="Về chung một nhà"
                                        class="mayhen_invitation_image_tag"
                                        data-sync-target="story_photo_3_url"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="chu-tr">
                            <div class="mayhen_invitation_edit_text nam-tr">
                                <h3 class="mayhen_invitation_headline">{{ $yearNum }}</h3>
                            </div>
                            <div class="mayhen_invitation_edit_text ten-tr">
                                <h3 class="mayhen_invitation_headline">Về chung một nhà</h3>
                            </div>
                            <div class="mayhen_invitation_edit_text nd-tr">
                                <p class="mayhen_invitation_paragraph">Sau bao ngày tháng đồng hành, chúng mình quyết định nắm tay nhau bước sang trang mới, viết tiếp câu chuyện gia đình nhỏ đầy ắp tiếng cười.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== SECTION 5: WEDDING EVENTS & AIRMAIL POSTCARD MAP (data-mayhen-section="events") ==================== --}}
        <section
            id="mayhen_section_events"
            data-mayhen-section="events"
            class="mayhen_invitation_section_group"
        >
            {{-- 5A. SỰ KIỆN CƯỚI (#SECTION_SK) --}}
            <div class="mayhen_invitation_phan mayhen_invitation_sk_phan mayhen_invitation_sk">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-hong-net);--f:saturate(33%) hue-rotate(24deg) opacity(.45);--mx:-95;--my:10%;--mw:210;--mh:220;--mbx:0;--mby:0;--mbw:210;--mbh:220;--dx:-170;--dy:14%;--dw:280;--dh:292;--dbx:0;--dby:0;--dbw:280;--dbh:292" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-oai-huong);--f:saturate(33%) hue-rotate(24deg) opacity(.45);--mx:315;--my:62%;--mw:155;--mh:212;--mbx:0;--mby:0;--mbw:155;--mbh:212;--dx:855;--dy:58%;--dw:220;--dh:302;--dbx:0;--dby:0;--dbw:220;--dbh:302" aria-hidden="true"></i>
                </div>
                <div class="mayhen_invitation_khung">
                    <div class="mayhen_invitation_sk_dau">
                        <i class="mayhen_invitation_tt t mayhen_invitation_hien" data-hieu-ung="trai" aria-hidden="true"></i>
                        <div>
                            <div class="mayhen_invitation_edit_text td mayhen_invitation_hien" data-hieu-ung="len">
                                <h2 class="mayhen_invitation_headline">Sự Kiện Cưới</h2>
                            </div>
                            <div class="mayhen_invitation_edit_text phu mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                                <p class="mayhen_invitation_paragraph">Wedding Events</p>
                            </div>
                        </div>
                        <i class="mayhen_invitation_tt p mayhen_invitation_hien" data-hieu-ung="phai" aria-hidden="true"></i>
                    </div>

                    <div class="mayhen_invitation_sk_luoi">
                        <div class="mayhen_invitation_anh_sk mayhen_invitation_anh mayhen_invitation_av mayhen_invitation_hien" data-hieu-ung="vom" data-av="man">
                            <div class="mayhen_invitation_edit_image">
                                <div class="mayhen_invitation_image_frame">
                                    <img
                                        src="{{ $ceremonyPhotoUrl }}"
                                        alt="Sự kiện cưới {{ $coupleNames }}"
                                        class="mayhen_invitation_image_tag"
                                        data-sync-target="ceremony_photo_url"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="mayhen_invitation_ds_sk">
                            {{-- Lễ 1: Lễ Ăn Hỏi / Vu Quy --}}
                            <div class="mayhen_invitation_su_kien mayhen_invitation_hien" data-hieu-ung="len">
                                <div class="bieu-tuong" aria-hidden="true">
                                    <i class="ic" style="--ic:var(--a-moc-1)"></i>
                                </div>
                                <div class="mayhen_invitation_edit_text gio mayhen_invitation_mot_dong">
                                    <h3 class="mayhen_invitation_headline" data-sync-target="ceremony_time">{{ $ceremonyTime }}</h3>
                                </div>
                                <i class="hv mayhen_invitation_tt" aria-hidden="true"></i>
                                <div class="mayhen_invitation_edit_text ten-sk">
                                    <h3 class="mayhen_invitation_headline">LỄ ĂN HỎI &amp; VU QUY</h3>
                                </div>
                                <div class="mayhen_invitation_edit_text ngay-sk">
                                    <p class="mayhen_invitation_paragraph">{{ $fullVietnameseDate }}</p>
                                </div>
                                <div class="mayhen_invitation_edit_text noi-sk">
                                    <p class="mayhen_invitation_paragraph" data-sync-target="ceremony_address">{{ $ceremonyAddress }}</p>
                                </div>
                            </div>

                            {{-- Lễ 2: Lễ Thành Hôn --}}
                            <div class="mayhen_invitation_su_kien mayhen_invitation_hien" data-hieu-ung="len">
                                <div class="bieu-tuong" aria-hidden="true">
                                    <i class="ic" style="--ic:var(--a-moc-2)"></i>
                                </div>
                                <div class="mayhen_invitation_edit_text gio mayhen_invitation_mot_dong">
                                    <h3 class="mayhen_invitation_headline" data-sync-target="ceremony_groom_time">{{ $ceremonyGroomTime }}</h3>
                                </div>
                                <i class="hv mayhen_invitation_tt" aria-hidden="true"></i>
                                <div class="mayhen_invitation_edit_text ten-sk">
                                    <h3 class="mayhen_invitation_headline">LỄ THÀNH HÔN</h3>
                                </div>
                                <div class="mayhen_invitation_edit_text ngay-sk">
                                    <p class="mayhen_invitation_paragraph">{{ $fullVietnameseDate }}</p>
                                </div>
                                <div class="mayhen_invitation_edit_text noi-sk">
                                    <p class="mayhen_invitation_paragraph" data-sync-target="ceremony_groom_address">{{ $ceremonyGroomAddress }}</p>
                                </div>
                            </div>

                            {{-- Lễ 3: Tiệc Cưới --}}
                            <div class="mayhen_invitation_su_kien mayhen_invitation_hien" data-hieu-ung="len">
                                <div class="bieu-tuong" aria-hidden="true">
                                    <i class="ic" style="--ic:var(--a-moc-3)"></i>
                                </div>
                                <div class="mayhen_invitation_edit_text gio mayhen_invitation_mot_dong">
                                    <h3 class="mayhen_invitation_headline" data-sync-target="party_time">{{ $partyTime }}</h3>
                                </div>
                                <i class="hv mayhen_invitation_tt" aria-hidden="true"></i>
                                <div class="mayhen_invitation_edit_text ten-sk">
                                    <h3 class="mayhen_invitation_headline">TIỆC CƯỚI</h3>
                                </div>
                                <div class="mayhen_invitation_edit_text ngay-sk">
                                    <p class="mayhen_invitation_paragraph">{{ $fullVietnameseDate }}</p>
                                </div>
                                <div class="mayhen_invitation_edit_text noi-sk">
                                    <p class="mayhen_invitation_paragraph" data-sync-target="event_party_address">{{ $partyAddress }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5B. BẢN ĐỒ BƯU THIẾP HÀNG KHÔNG (#SECTION_BANDO) --}}
            <div id="mayhen_map_postcard" class="mayhen_invitation_phan mayhen_invitation_bd">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_khung">
                    <div class="mayhen_invitation_edit_text tai-bd mayhen_invitation_hien" data-hieu-ung="len">
                        <h2 class="mayhen_invitation_headline">Địa chỉ tiệc cưới</h2>
                    </div>
                    <div class="mayhen_invitation_edit_text phu-bd mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                        <p class="mayhen_invitation_paragraph">See you there</p>
                    </div>
                    <div class="than-bd mayhen_invitation_hien" data-hieu-ung="len">
                        <div class="mayhen_invitation_khoi_bd mayhen_invitation_hien" data-hieu-ung="buu">
                            <div class="o-bd">
                                <iframe
                                    id="mayhenMapIframe"
                                    src="{{ $mapEmbedUrl }}"
                                    width="600"
                                    height="450"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="Bản đồ nơi tổ chức"
                                ></iframe>
                            </div>
                            <i class="tem mayhen_invitation_tt" aria-hidden="true"></i>
                            <i class="dau mayhen_invitation_tt" aria-hidden="true"></i>
                        </div>
                        <div class="mayhen_invitation_edit_text dc">
                            <p class="mayhen_invitation_paragraph" data-sync-target="event_party_address">{{ $partyAddress }}</p>
                        </div>
                        <a
                            href="{{ $mapLink }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mayhen_invitation_map_link_btn"
                            data-sync-target="event_party_map_link"
                        >
                            Mở Google Maps chỉ đường
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== SECTION 6: TIMELINE (data-mayhen-section="timeline") ==================== --}}
        <section
            id="mayhen_section_timeline"
            data-mayhen-section="timeline"
            class="mayhen_invitation_phan mayhen_invitation_lt_phan mayhen_invitation_lt"
        >
            <div class="mayhen_invitation_section_bg"></div>
            <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-hoa-day);--f:opacity(.38);--mx:-42;--my:8%;--mw:136;--mh:304;--mbx:0;--mby:0;--mbw:136;--mbh:304;--dx:-120;--dy:10%;--dw:210;--dh:470;--dbx:0;--dby:0;--dbw:210;--dbh:470" aria-hidden="true"></i>
                <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-hoa-day);--f:opacity(.38);--mx:328;--my:48%;--mw:136;--mh:304;--mbx:0;--mby:0;--mbw:136;--mbh:304;--dx:870;--dy:46%;--dw:210;--dh:470;--dbx:0;--dby:0;--dbw:210;--dbh:470" aria-hidden="true"></i>
            </div>
            <div class="mayhen_invitation_khung">
                <div class="mayhen_invitation_edit_text td mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                    <h2 class="mayhen_invitation_headline">Timeline</h2>
                </div>
                <div class="mayhen_invitation_edit_text phu mayhen_invitation_hien" data-hieu-ung="len">
                    <p class="mayhen_invitation_paragraph">Lịch trình ngày cưới</p>
                </div>

                <div class="mayhen_invitation_lt_anh mayhen_invitation_anh mayhen_invitation_av mayhen_invitation_hien" data-hieu-ung="nay" data-av="zoom">
                    <div class="mayhen_invitation_edit_image">
                        <div class="mayhen_invitation_image_frame">
                            <img
                                src="{{ $ceremonyPhotoUrl }}"
                                alt="Timeline {{ $coupleNames }}"
                                class="mayhen_invitation_image_tag"
                                data-sync-target="ceremony_photo_url"
                            >
                        </div>
                    </div>
                </div>

                <div class="mayhen_invitation_ds_moc">
                    @foreach($timelineItems as $idx => $item)
                        @php
                            $tlTime = is_array($item) ? ($item['time'] ?? '16:00') : '16:00';
                            $tlTitle = is_array($item) ? ($item['title'] ?? 'Đón khách') : 'Đón khách';
                            $tlDesc = is_array($item) ? ($item['desc'] ?? '') : '';
                            $isEven = ($idx % 2 === 1);
                        @endphp
                        <div
                            class="mayhen_invitation_moc {{ $isEven ? 'mayhen_invitation_chan' : '' }} mayhen_invitation_hien"
                            data-hieu-ung="{{ $isEven ? 'phai' : 'trai' }}"
                        >
                            <div class="cham" aria-hidden="true">
                                @if($idx % 3 === 0)
                                    <svg viewBox="0 0 48 48"><path d="M9 17h30l-3 19a4 4 0 0 1-4 3H16a4 4 0 0 1-4-3L9 17Z"/><path d="M16 17V13a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v4"/><circle cx="24" cy="27" r="5"/></svg>
                                @elseif($idx % 3 === 1)
                                    <svg viewBox="0 0 48 48"><circle cx="18" cy="27" r="9"/><circle cx="30" cy="27" r="9"/><path d="m21 13 3-5 3 5"/><path d="M24 8v5"/></svg>
                                @else
                                    <svg viewBox="0 0 48 48"><path d="M14 8h8l-2 14a6 6 0 0 1-6 5v13"/><path d="M10 40h8"/><path d="M34 8h-8l2 14a6 6 0 0 0 6 5v13"/><path d="M30 40h8"/></svg>
                                @endif
                            </div>
                            <div class="mayhen_invitation_edit_text gio mayhen_invitation_mot_dong">
                                <h3 class="mayhen_invitation_headline" data-sync-target="timeline.{{ $idx }}.time">{{ $tlTime }}</h3>
                            </div>
                            <div class="mayhen_invitation_edit_text ten">
                                <h3 class="mayhen_invitation_headline" data-sync-target="timeline.{{ $idx }}.title">{{ $tlTitle }}</h3>
                            </div>
                            @if(!empty($tlDesc))
                                <div class="mayhen_invitation_edit_text mo-ta-moc">
                                    <p class="mayhen_invitation_paragraph">{{ $tlDesc }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ==================== SECTION 7: DRESSCODE (data-mayhen-section="dresscode") ==================== --}}
        @if(!$isCuratedSeven)
        <section
            id="mayhen_section_dresscode"
            data-mayhen-section="dresscode"
            class="mayhen_invitation_phan mayhen_invitation_dc"
        >
            <div class="mayhen_invitation_section_bg"></div>
            <div class="mayhen_invitation_khung">
                <div class="mayhen_invitation_edit_text td mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                    <h2 class="mayhen_invitation_headline">Dresscode</h2>
                </div>
                <div class="mayhen_invitation_edit_text mo-ta mayhen_invitation_hien" data-hieu-ung="len">
                    <p class="mayhen_invitation_paragraph" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>
                </div>
                <div
                    id="mayhenDresscodeSwatches"
                    class="mayhen_invitation_dcm_ds mayhen_invitation_hien"
                    data-hieu-ung="len"
                    data-sync-target="dresscode_colors"
                >
                    @foreach($dresscodeHexList as $idx => $hex)
                        <div class="mayhen_invitation_dcm_o" style="animation-delay: {{ number_format($idx * 0.15, 2) }}s">
                            <div class="mayhen_invitation_dcm_vom" style="--tre: -{{ number_format($idx * 1.4, 1) }}s">
                                <svg class="mayhen_invitation_dcm_hoa" viewBox="0 0 34 22" aria-hidden="true">
                                    <path d="M17 16c-3-5-9-6-13-3M17 16c3-5 9-6 13-3M17 16V8"/>
                                    <path d="M8 12c-1-2 0-4 2-4M26 12c1-2 0-4-2-4"/>
                                    <circle cx="17" cy="6" r="3"/>
                                    <circle cx="17" cy="17" r="1.6"/>
                                </svg>
                                <i class="mayhen_invitation_dcm_kinh" style="--c: {{ $hex }}"></i>
                            </div>
                            <span class="mayhen_invitation_dcm_ten">{{ $dresscodeLabels[$idx] ?? $hex }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- ==================== SECTION 8: ALBUM 10 PHOTOS (data-mayhen-section="album") ==================== --}}
        <section
            id="mayhen_section_album"
            data-mayhen-section="album"
            class="mayhen_invitation_phan mayhen_invitation_ab"
        >
            <div class="mayhen_invitation_section_bg"></div>
            <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                <i class="mayhen_invitation_tt mayhen_invitation_nh chi-dt lac" style="--a:var(--a-hoa-day);--f:opacity(.3);--dx:-274;--dy:85.9%;--dw:259;--dh:578;--dbx:0;--dby:0;--dbw:259;--dbh:578" aria-hidden="true"></i>
                <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-hoa-day);--f:opacity(.3);--mx:-48;--my:-7.5%;--mw:149;--mh:334;--mbx:0;--mby:0;--mbw:149;--mbh:334;--dx:1024;--dy:17.7%;--dw:259;--dh:578;--dbx:0;--dby:0;--dbw:259;--dbh:578" aria-hidden="true"></i>
            </div>
            <div class="mayhen_invitation_khung">
                <div class="mayhen_invitation_ab_dau">
                    <i class="goc mayhen_invitation_tt mayhen_invitation_hien" data-hieu-ung="khung" aria-hidden="true"></i>
                    <div class="mayhen_invitation_edit_text nho mayhen_invitation_viet mayhen_invitation_mot_dong mayhen_invitation_hien" data-hieu-ung="viet">
                        <p class="mayhen_invitation_headline">Our memories</p>
                    </div>
                    <div class="mayhen_invitation_edit_text lon mayhen_invitation_mot_dong mayhen_invitation_hien" data-hieu-ung="len">
                        <h2 class="mayhen_invitation_headline">Wedding album</h2>
                    </div>
                </div>

                <div class="mayhen_invitation_cf_wrap mayhen_invitation_hien" data-hieu-ung="len">
                    <div id="mayhenCoverflowStage" class="mayhen_invitation_cf_stage">
                        @foreach($galleryPhotos as $idx => $photoUrl)
                            <div
                                class="mayhen_invitation_cf_item"
                                data-cf-index="{{ $idx }}"
                                data-lightbox-src="{{ $photoUrl }}"
                            >
                                <img
                                    src="{{ $photoUrl }}"
                                    alt="Album cưới {{ $coupleNames }} {{ $idx + 1 }}"
                                    loading="{{ $idx < 3 ? 'eager' : 'lazy' }}"
                                    data-sync-target="gallery.{{ $idx }}"
                                >
                            </div>
                        @endforeach
                    </div>
                    <div class="mayhen_invitation_cf_controls">
                        <button type="button" id="mayhenCfPrev" class="mayhen_invitation_cf_nav" aria-label="Ảnh trước">‹</button>
                        <div class="mayhen_invitation_cf_dots">
                            @foreach($galleryPhotos as $idx => $photoUrl)
                                <button
                                    type="button"
                                    class="mayhen_invitation_cf_dot {{ $idx === 0 ? 'is-active' : '' }}"
                                    data-cf-dot="{{ $idx }}"
                                    aria-label="Ảnh {{ $idx + 1 }}"
                                ></button>
                            @endforeach
                        </div>
                        <button type="button" id="mayhenCfNext" class="mayhen_invitation_cf_nav" aria-label="Ảnh tiếp theo">›</button>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== SECTION 9: GIFT & VIETQR (data-mayhen-section="gift") ==================== --}}
        @if(!$isCuratedSeven)
        <section
            id="mayhen_section_gift"
            data-mayhen-section="gift"
            class="mayhen_invitation_phan mayhen_invitation_qr_phan"
        >
            <div class="mayhen_invitation_section_bg"></div>
            <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-hoa-v-trai);--f:saturate(33%) hue-rotate(24deg);--mx:-38;--my:17.7%;--mw:178;--mh:179;--mbx:0;--mby:0;--mbw:178;--mbh:179;--dx:51;--dy:16.4%;--dw:281;--dh:282;--dbx:0;--dby:0;--dbw:281;--dbh:282" aria-hidden="true"></i>
                <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-hoa-v-phai);--f:saturate(33%) hue-rotate(24deg);--mx:243;--my:70.1%;--mw:200;--mh:158;--mbx:0;--mby:0;--mbw:200;--mbh:158;--dx:590;--dy:69.8%;--dw:272;--dh:215;--dbx:0;--dby:0;--dbw:272;--dbh:215" aria-hidden="true"></i>
            </div>
            <div class="mayhen_invitation_khung">
                <div class="mayhen_invitation_share">
                    <div class="mayhen_invitation_edit_text a mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="trai">
                        <p class="mayhen_invitation_headline">Share</p>
                    </div>
                    <div class="mayhen_invitation_edit_text b mayhen_invitation_hien" data-hieu-ung="nay">
                        <p class="mayhen_invitation_headline">THE</p>
                    </div>
                    <div class="mayhen_invitation_edit_text a p mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="phai">
                        <p class="mayhen_invitation_headline">Love</p>
                    </div>
                </div>

                <div class="mayhen_invitation_the_qr_ngoai mayhen_invitation_hien" data-hieu-ung="len">
                    <div class="mayhen_invitation_trong_qr">
                        <div class="mayhen_invitation_qr">
                            {{-- QR Chú Rể --}}
                            <div class="mayhen_invitation_the_qr mayhen_invitation_qr_card">
                                <div class="khung-qr mayhen_invitation_qr_img_box" data-lightbox-src="{{ $groomQrUrl }}">
                                    <div class="anh-qr">
                                        <img
                                            src="{{ $groomQrUrl }}"
                                            alt="Mã QR mừng cưới Chú rể"
                                            data-sync-target="qr_groom_image"
                                        >
                                    </div>
                                    <i class="ngam mayhen_invitation_tt" aria-hidden="true"></i>
                                </div>
                                <div class="mayhen_invitation_edit_text vai-qr mayhen_invitation_qr_role mayhen_invitation_mot_dong">
                                    <p class="mayhen_invitation_headline">Chú rể</p>
                                </div>
                                <div class="mayhen_invitation_edit_text tk">
                                    <p class="mayhen_invitation_paragraph">
                                        <span data-sync-target="groom_account_name">{{ $groomAccountName }}</span><br>
                                        STK: <span class="mayhen_invitation_qr_stk" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</span><br>
                                        NH: <span data-sync-target="groom_bank_name">{{ $groomBankName }}</span>
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="mayhen_invitation_qr_copy_btn"
                                    data-copy-stk="{{ $groomBankAccount }}"
                                >
                                    Sao chép STK
                                </button>
                            </div>

                            {{-- QR Cô Dâu --}}
                            <div class="mayhen_invitation_the_qr mayhen_invitation_qr_card">
                                <div class="khung-qr mayhen_invitation_qr_img_box" data-lightbox-src="{{ $brideQrUrl }}">
                                    <div class="anh-qr">
                                        <img
                                            src="{{ $brideQrUrl }}"
                                            alt="Mã QR mừng cưới Cô dâu"
                                            data-sync-target="qr_bride_image"
                                        >
                                    </div>
                                    <i class="ngam mayhen_invitation_tt" aria-hidden="true"></i>
                                </div>
                                <div class="mayhen_invitation_edit_text vai-qr mayhen_invitation_qr_role mayhen_invitation_mot_dong">
                                    <p class="mayhen_invitation_headline">Cô dâu</p>
                                </div>
                                <div class="mayhen_invitation_edit_text tk">
                                    <p class="mayhen_invitation_paragraph">
                                        <span data-sync-target="bride_account_name">{{ $brideAccountName }}</span><br>
                                        STK: <span class="mayhen_invitation_qr_stk" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</span><br>
                                        NH: <span data-sync-target="bride_bank_name">{{ $brideBankName }}</span>
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="mayhen_invitation_qr_copy_btn"
                                    data-copy-stk="{{ $brideBankAccount }}"
                                >
                                    Sao chép STK
                                </button>
                            </div>
                        </div>
                        <div class="goi-y">Chạm vào mã QR để phóng to</div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        {{-- ==================== SECTION 10: ENDING — SIGNATURE, RSVP, WISHES & THANK YOU (data-mayhen-section="ending") ==================== --}}
        @if(!$isCuratedSeven)
        <section
            id="mayhen_section_ending"
            data-mayhen-section="ending"
            class="mayhen_invitation_section_group"
        >
            {{-- 10A. LƯU BÚT KÝ TÊN (#SECTION_KY — STRICT LAW #8: PURE BOTANICAL HEART CREST SVG, NO HUMAN PHOTOS) --}}
            <div id="mayhen_section_signature" class="mayhen_invitation_phan mayhen_invitation_ky mayhen_invitation_signature">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-hoa-v-trai);--f:saturate(33%) hue-rotate(24deg) opacity(.45);--mx:-40;--my:10%;--mw:160;--mh:160;--mbx:0;--mby:0;--mbw:160;--mbh:160;--dx:-120;--dy:12%;--dw:240;--dh:240;--dbx:0;--dby:0;--dbw:240;--dbh:240" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-hoa-v-phai);--f:saturate(33%) hue-rotate(24deg) opacity(.45);--mx:300;--my:70%;--mw:160;--mh:126;--mbx:0;--mby:0;--mbw:160;--mbh:126;--dx:840;--dy:72%;--dw:240;--dh:190;--dbx:0;--dby:0;--dbw:240;--dbh:190" aria-hidden="true"></i>
                </div>
                <div class="mayhen_invitation_khung">
                    <div class="mayhen_invitation_ky_dau">
                        <i class="mayhen_invitation_tt t mayhen_invitation_hien" data-hieu-ung="trai" aria-hidden="true"></i>
                        <div>
                            <div class="mayhen_invitation_edit_text sig mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                                <p class="mayhen_invitation_paragraph">Signature</p>
                            </div>
                            <div class="mayhen_invitation_edit_text luu mayhen_invitation_hien" data-hieu-ung="len">
                                <p class="mayhen_invitation_paragraph">Lưu Bút</p>
                            </div>
                        </div>
                        <i class="mayhen_invitation_tt p mayhen_invitation_hien" data-hieu-ung="phai" aria-hidden="true"></i>
                    </div>
                    <div class="mayhen_invitation_edit_text mo-ta mayhen_invitation_hien" data-hieu-ung="len">
                        <p class="mayhen_invitation_paragraph">Để lại chữ ký của Bạn và cùng chúng mình<br>lưu giữ kỷ niệm hạnh phúc này nhé!!</p>
                    </div>

                    <div class="mayhen_invitation_bang_ky mayhen_invitation_hien" data-hieu-ung="mo">
                        <div class="mayhen_invitation_sig_crest">
                            {{-- Pure Botanical Olive Heart Emblem (No Human Photo - Law #8) --}}
                            <svg class="mayhen_invitation_sig_heart_svg" viewBox="0 0 120 96" fill="none" aria-hidden="true">
                                <path d="M60 82C60 82 18 56 18 31C18 18 28 10 40 10C49 10 56 15 60 22C64 15 71 10 80 10C92 10 102 18 102 31C102 56 60 82 60 82Z" stroke="currentColor" stroke-width="1.6" stroke-dasharray="3 3" fill="rgba(133, 133, 105, 0.1)"/>
                                <path d="M60 76C60 76 24 52 24 31C24 21 32 15 41 15C49 15 55 19 60 26C65 19 71 15 79 15C88 15 96 21 96 31C96 52 60 76 60 76Z" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M44 42C49 36 55 36 60 42C65 36 71 36 76 42" stroke="#858569" stroke-width="1.4" stroke-linecap="round"/>
                                <circle cx="60" cy="30" r="3" fill="#858569"/>
                            </svg>
                            <div class="ten-ky">
                                <div class="mayhen_invitation_edit_text t mayhen_invitation_mot_dong">
                                    <h3 class="mayhen_invitation_headline" data-sync-target="groom_name">{{ $groomName }}</h3>
                                </div>
                                <div class="va mayhen_invitation_tt" aria-hidden="true">&amp;</div>
                                <div class="mayhen_invitation_edit_text t mayhen_invitation_mot_dong">
                                    <h3 class="mayhen_invitation_headline" data-sync-target="bride_name">{{ $brideName }}</h3>
                                </div>
                            </div>
                        </div>

                        <div class="mayhen_invitation_sig_form_box">
                            <input
                                type="text"
                                id="mayhenSignerName"
                                class="mayhen_invitation_sig_input"
                                placeholder="Nhập tên của bạn để lưu chữ ký..."
                                maxlength="80"
                            >
                            <div class="mayhen_invitation_sig_canvas_wrap">
                                <canvas id="mayhenSignatureCanvas" width="360" height="170"></canvas>
                                <div id="mayhenSigPlaceholder" class="mayhen_invitation_sig_placeholder">
                                    ✎ Ký tên trực tiếp tại đây
                                </div>
                            </div>
                            <div class="mayhen_invitation_sig_toolbar">
                                <div class="mayhen_invitation_sig_colors" aria-label="Chọn màu mực ký">
                                    <button type="button" class="mayhen_invitation_sig_color_btn is-active" data-pen-color="#5E5E45" style="background:#5E5E45" aria-label="Mực xanh ô liu"></button>
                                    <button type="button" class="mayhen_invitation_sig_color_btn" data-pen-color="#2C2C22" style="background:#2C2C22" aria-label="Mực đen than"></button>
                                    <button type="button" class="mayhen_invitation_sig_color_btn" data-pen-color="#8B5E3C" style="background:#8B5E3C" aria-label="Mực nâu đồng"></button>
                                </div>
                                <div class="mayhen_invitation_sig_actions">
                                    <button type="button" id="mayhenSigClearBtn" class="mayhen_invitation_sig_btn">🗑 Xóa</button>
                                    <button type="button" id="mayhenSigSaveBtn" class="mayhen_invitation_sig_btn mayhen_invitation_sig_btn--primary">💾 Lưu chữ ký</button>
                                </div>
                            </div>
                            <div id="mayhenSigStatus" class="mayhen_invitation_sig_status" role="status"></div>
                        </div>

                        <div id="mayhenSignatureWall" class="mayhen_invitation_sig_wall">
                            @forelse($savedSignatures as $sigItem)
                                @if(!empty($sigItem['signature_url']) || !empty($sigItem['signature_data']))
                                    <div class="mayhen_invitation_sig_card">
                                        <img
                                            src="{{ $sigItem['signature_url'] ?? $sigItem['signature_data'] }}"
                                            alt="Chữ ký của {{ $sigItem['guest_name'] ?? 'Khách mời' }}"
                                        >
                                        <span class="mayhen_invitation_sig_card_name">{{ $sigItem['guest_name'] ?? 'Khách mời' }}</span>
                                    </div>
                                @endif
                            @empty
                                <div class="mayhen_invitation_sig_empty">Hãy là người đầu tiên lưu chữ ký chúc phúc trên sổ lưu bút!</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- 10B. XÁC NHẬN THAM DỰ (#SECTION_XN — STRICT LAW #8: PURE BOTANICAL CREST ON #858569 CARD, NO HUMAN PHOTOS) --}}
            <div id="mayhen_section_rsvp" class="mayhen_invitation_phan mayhen_invitation_xn mayhen_invitation_rsvp">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt tren" aria-hidden="true">
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-la-net);--f:saturate(33%) hue-rotate(24deg);--mx:291;--my:33.2%;--mw:157;--mh:128;--mbx:0;--mby:0;--mbw:157;--mbh:128;--dx:40;--dy:28.6%;--dw:200;--dh:163;--dbx:0;--dby:0;--dbw:200;--dbh:163" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-hong-net);--f:saturate(33%) hue-rotate(24deg);--mx:-191;--my:31.4%;--mw:200;--mh:209;--mbx:0;--mby:0;--mbw:200;--mbh:209;--dx:-262;--dy:88.1%;--dw:296;--dh:309;--dbx:0;--dby:0;--dbw:296;--dbh:309" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-bo-len);--f:saturate(33%) hue-rotate(24deg) opacity(.65);--mx:244;--my:73.1%;--mw:176;--mh:256;--mbx:0;--mby:0;--mbw:195;--mbh:284;--dx:768;--dy:65.4%;--dw:229;--dh:332;--dbx:0;--dby:0;--dbw:253;--dbh:368" aria-hidden="true"></i>
                </div>
                <div class="mayhen_invitation_khung">
                    <div class="mayhen_invitation_the_xanh mayhen_invitation_hien" data-hieu-ung="len">
                        {{-- Pure Botanical Laurel Crest SVG instead of Couple Photo (Law #8) --}}
                        <div class="mayhen_invitation_rsvp_botanical_crest" aria-hidden="true">
                            <svg viewBox="0 0 160 80" fill="none">
                                <path d="M24 62C36 30 58 16 80 16C102 16 124 30 136 62" stroke="currentColor" stroke-width="1.5" stroke-dasharray="3 3"/>
                                <path d="M36 58C48 34 62 24 80 24C98 24 112 34 124 58" stroke="currentColor" stroke-width="1.2"/>
                                <circle cx="80" cy="24" r="4" fill="#FFFBF7"/>
                                <path d="M64 46L80 34L96 46" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="chu-xn">
                            <div class="mayhen_invitation_edit_text td1 mayhen_invitation_hien" data-hieu-ung="len">
                                <h2 class="mayhen_invitation_headline">Xác nhận tham dự</h2>
                            </div>
                            <div class="mayhen_invitation_edit_text td2 mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                                <p class="mayhen_invitation_paragraph">Confirm and send wishes</p>
                            </div>
                            <div class="mayhen_invitation_edit_text mo-ta mayhen_invitation_hien" data-hieu-ung="len">
                                <p class="mayhen_invitation_paragraph">Vui lòng xác nhận sự tham dự của bạn để chúng tôi có thể chuẩn bị và đón tiếp bạn một cách chu đáo nhất.<br>Sự hiện diện của bạn là niềm vinh hạnh đối với chúng tôi. Trân trọng!</p>
                            </div>
                        </div>

                        <form id="mayhenRsvpForm" class="mayhen_invitation_rsvp_form mayhen_invitation_hien" data-hieu-ung="len">
                            <div class="mayhen_invitation_rsvp_field">
                                <label class="mayhen_invitation_rsvp_label" for="mayhenRsvpName">Họ và tên khách mời *</label>
                                <input
                                    type="text"
                                    id="mayhenRsvpName"
                                    name="guest_name"
                                    class="mayhen_invitation_rsvp_input"
                                    placeholder="Nhập họ tên của bạn..."
                                    required
                                    maxlength="100"
                                >
                            </div>

                            <div class="mayhen_invitation_rsvp_pills">
                                <label class="mayhen_invitation_rsvp_pill is-active">
                                    <input type="radio" name="attending_status" value="attending" checked>
                                    <span>Sẽ tham dự</span>
                                </label>
                                <label class="mayhen_invitation_rsvp_pill">
                                    <input type="radio" name="attending_status" value="declined">
                                    <span>Rất tiếc vắng mặt</span>
                                </label>
                            </div>

                            <div class="mayhen_invitation_rsvp_grid2 mayhen_invitation_rsvp_field">
                                <div>
                                    <label class="mayhen_invitation_rsvp_label" for="mayhenRsvpAdults">Số người lớn</label>
                                    <select id="mayhenRsvpAdults" name="adults_count" class="mayhen_invitation_rsvp_select">
                                        <option value="1">1 người</option>
                                        <option value="2">2 người</option>
                                        <option value="3">3 người</option>
                                        <option value="4">4 người</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mayhen_invitation_rsvp_label" for="mayhenRsvpChildren">Số trẻ em</label>
                                    <select id="mayhenRsvpChildren" name="children_count" class="mayhen_invitation_rsvp_select">
                                        <option value="0">0 trẻ em</option>
                                        <option value="1">1 trẻ em</option>
                                        <option value="2">2 trẻ em</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mayhen_invitation_rsvp_field">
                                <label class="mayhen_invitation_rsvp_label" for="mayhenRsvpNote">Ghi chú / Lời nhắn</label>
                                <textarea
                                    id="mayhenRsvpNote"
                                    name="wishes"
                                    rows="2"
                                    class="mayhen_invitation_rsvp_textarea"
                                    placeholder="Yêu cầu ăn chay, dị ứng hoặc lời nhắn gửi..."
                                    maxlength="400"
                                ></textarea>
                            </div>

                            <button type="submit" id="mayhenRsvpSubmitBtn" class="mayhen_invitation_rsvp_submit">
                                Gửi xác nhận tham dự
                            </button>
                            <div id="mayhenRsvpStatus" class="mayhen_invitation_rsvp_status" role="status"></div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 10C. SỔ LỜI CHÚC (#SECTION_LC) --}}
            <div class="mayhen_invitation_phan mayhen_invitation_lc">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt" aria-hidden="true">
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-hoa-dai);--f:saturate(33%) hue-rotate(24deg);--mx:-1;--my:82.7%;--mw:141;--mh:183;--mbx:0;--mby:0;--mbw:141;--mbh:183;--dx:788;--dy:27.3%;--dw:289;--dh:376;--dbx:0;--dby:0;--dbw:289;--dbh:376" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-xi-trai);--f:saturate(33%) hue-rotate(24deg);--mx:-22;--my:5.2%;--mw:122;--mh:144;--mbx:0;--mby:0;--mbw:122;--mbh:144;--dx:724;--dy:5.3%;--dw:182;--dh:216;--dbx:0;--dby:0;--dbw:182;--dbh:216" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-xi-phai);--f:saturate(33%) hue-rotate(24deg);--mx:325;--my:5.2%;--mw:122;--mh:144;--mbx:0;--mby:0;--mbw:122;--mbh:144;--dx:41;--dy:5.3%;--dw:182;--dh:216;--dbx:0;--dby:0;--dbw:182;--dbh:216" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-bo-ngang-trang);--f:contrast(31%) saturate(33%) hue-rotate(24deg);--mx:110;--my:0%;--mw:200;--mh:78;--mbx:1;--mby:-44;--mbw:200;--mbh:122;--dx:334;--dy:0%;--dw:293;--dh:114;--dbx:1;--dby:-64;--dbw:293;--dbh:178" aria-hidden="true"></i>
                </div>
                <div class="mayhen_invitation_khung">
                    <div class="mayhen_invitation_edit_text td1 mayhen_invitation_hien" data-hieu-ung="len">
                        <h2 class="mayhen_invitation_headline">Lời chúc từ khách mời</h2>
                    </div>
                    <div class="mayhen_invitation_edit_text td2 mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                        <p class="mayhen_invitation_paragraph">Wishes from guests</p>
                    </div>

                    <div class="mayhen_invitation_hop_lc mayhen_invitation_hien" data-hieu-ung="len">
                        <i class="goc-1 mayhen_invitation_tt" aria-hidden="true"></i>
                        <i class="goc-2 mayhen_invitation_tt mayhen_invitation_hien" data-hieu-ung="nay" aria-hidden="true"></i>
                        <div id="mayhenWishesList" class="mayhen_invitation_wishes_list">
                            @forelse($approvedWishes as $wish)
                                <div class="mayhen_invitation_wish_item">
                                    <div class="mayhen_invitation_wish_name">{{ $wish->name ?? 'Khách mời' }}</div>
                                    <div class="mayhen_invitation_wish_msg">{{ $wish->message ?? '' }}</div>
                                </div>
                            @empty
                                <div class="mayhen_invitation_wish_item">
                                    <div class="mayhen_invitation_wish_name">Gia đình &amp; Bạn bè</div>
                                    <div class="mayhen_invitation_wish_msg">Chúc hai bạn {{ $groomShort }} &amp; {{ $brideShort }} trăm năm tình viên mãn, bạc đầu nghĩa phu thê!</div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <form id="mayhenWishForm" class="mayhen_invitation_wish_form mayhen_invitation_hien" data-hieu-ung="len">
                        <div class="mayhen_invitation_rsvp_field">
                            <input
                                type="text"
                                id="mayhenWishName"
                                class="mayhen_invitation_sig_input"
                                placeholder="Tên của bạn *"
                                required
                                maxlength="80"
                            >
                        </div>
                        <div class="mayhen_invitation_rsvp_field">
                            <select id="mayhenWishPreset" class="mayhen_invitation_sig_input">
                                <option value="">-- Chọn nhanh lời chúc gợi ý --</option>
                                <option value="Chúc hai bạn trăm năm hạnh phúc, sớm có thiên thần nhỏ!">Chúc hai bạn trăm năm hạnh phúc, sớm có thiên thần nhỏ!</option>
                                <option value="Chúc mừng hạnh phúc! Chúc hai bạn luôn yêu thương và đồng hành cùng nhau trọn đời.">Chúc mừng hạnh phúc! Chúc hai bạn luôn yêu thương và đồng hành cùng nhau trọn đời.</option>
                                <option value="Happy Wedding! Chúc cuộc sống mới của hai bạn luôn ngập tràn tiếng cười.">Happy Wedding! Chúc cuộc sống mới của hai bạn luôn ngập tràn tiếng cười.</option>
                            </select>
                        </div>
                        <div class="mayhen_invitation_rsvp_field">
                            <textarea
                                id="mayhenWishMessage"
                                rows="3"
                                class="mayhen_invitation_sig_input"
                                placeholder="Nhập lời chúc tốt đẹp gửi tới cô dâu, chú rể..."
                                required
                                maxlength="500"
                            ></textarea>
                        </div>
                        <button type="submit" id="mayhenWishSubmitBtn" class="mayhen_invitation_rsvp_submit">
                            Gửi lời chúc phúc
                        </button>
                        <div id="mayhenWishStatus" class="mayhen_invitation_sig_status" role="status"></div>
                    </form>
                </div>
            </div>

            {{-- 10D. LỜI CẢM ƠN (#SECTION_KET) --}}
            <div class="mayhen_invitation_phan mayhen_invitation_ket">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_lop_hoa mayhen_invitation_tt tren" aria-hidden="true">
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-mang-phai);--mx:-213;--my:-0.4%;--mw:200;--mh:139;--mbx:0;--mby:0;--mbw:200;--mbh:139;--dx:-360;--dy:0%;--dw:360;--dh:250;--dbx:0;--dby:0;--dbw:360;--dbh:250" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-mang-trai);--mx:-13;--my:-0.4%;--mw:200;--mh:139;--mbx:0;--mby:0;--mbw:200;--mbh:139;--dx:0;--dy:0%;--dw:360;--dh:250;--dbx:0;--dby:0;--dbw:360;--dbh:250" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-mang-phai);--mx:237;--my:-0.4%;--mw:200;--mh:139;--mbx:0;--mby:0;--mbw:200;--mbh:139;--dx:600;--dy:0%;--dw:360;--dh:250;--dbx:0;--dby:0;--dbw:360;--dbh:250" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh" style="--a:var(--a-mang-trai);--mx:437;--my:-0.4%;--mw:200;--mh:139;--mbx:0;--mby:0;--mbw:200;--mbh:139;--dx:960;--dy:0%;--dw:360;--dh:250;--dbx:0;--dby:0;--dbw:360;--dbh:250" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-vang-trai);--f:saturate(33%) hue-rotate(24deg);--mx:0;--my:-27.9%;--mw:138;--mh:390;--mbx:1;--mby:172;--mbw:137;--mbh:218;--dx:149;--dy:-31.3%;--dw:200;--dh:567;--dbx:1;--dby:250;--dbw:199;--dbh:316" aria-hidden="true"></i>
                    <i class="mayhen_invitation_tt mayhen_invitation_nh lac" style="--a:var(--a-vang-phai);--f:saturate(33%) hue-rotate(24deg);--mx:287;--my:-8.1%;--mw:147;--mh:226;--mbx:0;--mby:0;--mbw:147;--mbh:226;--dx:635;--dy:0%;--dw:218;--dh:334;--dbx:0;--dby:0;--dbw:218;--dbh:334" aria-hidden="true"></i>
                </div>
                <div class="mayhen_invitation_khung">
                    <div class="mayhen_invitation_vom mayhen_invitation_hien" data-hieu-ung="vom">
                        <div class="hinh-vom mayhen_invitation_av" data-av="man">
                            <div class="mayhen_invitation_edit_image">
                                <div class="mayhen_invitation_image_frame">
                                    <img
                                        src="{{ $footerPhotoUrl }}"
                                        alt="Thank you {{ $coupleNames }}"
                                        class="mayhen_invitation_image_tag"
                                        data-sync-target="footer_photo_url"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="mayhen_invitation_edit_text cam-on mayhen_invitation_viet mayhen_invitation_hien" data-hieu-ung="viet">
                            <h2 class="mayhen_invitation_headline" data-sync-target="thank_you_title">{{ $thankYouTitle }}</h2>
                        </div>
                    </div>
                    <div class="mayhen_invitation_ket_duoi">
                        <i class="mayhen_invitation_tt t mayhen_invitation_hien" data-hieu-ung="trai" aria-hidden="true"></i>
                        <div class="mayhen_invitation_edit_text loi mayhen_invitation_hien" data-hieu-ung="len">
                            <p class="mayhen_invitation_paragraph" data-sync-target="thank_you_quote">{{ $thankYouQuote }}</p>
                        </div>
                        <i class="mayhen_invitation_tt p mayhen_invitation_hien" data-hieu-ung="phai" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </section>
        @endif

    </div>

    {{-- Fullscreen Lightbox for Album & QR --}}
    <div id="mayhenLightbox" class="mayhen_invitation_lightbox" aria-hidden="true" role="dialog" aria-label="Xem ảnh phóng to">
        <div class="mayhen_invitation_lb_top">
            <span id="mayhenLbCounter">1 / 10</span>
            <button type="button" id="mayhenLbClose" class="mayhen_invitation_lb_close" aria-label="Đóng">×</button>
        </div>
        <div class="mayhen_invitation_lb_stage">
            <button type="button" id="mayhenLbPrev" class="mayhen_invitation_lb_arrow mayhen_invitation_lb_arrow--prev" aria-label="Ảnh trước">‹</button>
            <img id="mayhenLbImg" src="{{ $coverPhotoUrl }}" alt="Ảnh phóng to">
            <button type="button" id="mayhenLbNext" class="mayhen_invitation_lb_arrow mayhen_invitation_lb_arrow--next" aria-label="Ảnh tiếp theo">›</button>
        </div>
        <div id="mayhenLbCaption" class="mayhen_invitation_lb_caption"></div>
    </div>

    {{-- Toast Notification --}}
    <div id="mayhenToast" class="mayhen_invitation_toast" role="status" aria-live="polite"></div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v("mau-thiep/{$templateSlug}/assets/script.js") }}"></script>
@endpush
