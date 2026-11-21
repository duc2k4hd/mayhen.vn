@extends('client.layouts.invitation')

@php
    $slug = 'ban-nhac-khong-doan-ket';

    $isEmbed = ($isEmbed ?? false)
        || request()->boolean('embed')
        || request()->boolean('in_modal')
        || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);

    $extraData = is_array($wedding->extra_data ?? null)
        ? $wedding->extra_data
        : (json_decode((string) ($wedding->extra_data ?? '{}'), true) ?? []);
    foreach ($extraData as $k => $v) {
        if ($v === null && !in_array($k, ['gallery', 'timeline', 'dresscode_colors'], true) && !str_ends_with($k, '_url') && !str_ends_with($k, '_image')) {
            $extraData[$k] = '';
        }
    }

    $invConfig = is_array($invitation->custom_config ?? null)
        ? $invitation->custom_config
        : (json_decode((string) ($invitation->custom_config ?? '{}'), true) ?? []);

    $isCuratedSeven = !empty($isCuratedSeven)
        || !empty($isCurated)
        || (($template->category ?? '') === 'curated')
        || !empty($invConfig['is_curated_seven'])
        || !empty($invConfig['is_curated']);

    $autoOpened = $isEmbed || ($isCuratedSeven && request()->boolean('crafting'));

    $isPreview = request()->routeIs('templates.preview')
        || (!isset($wedding) && isset($template))
        || !isset($invitation)
        || !$invitation->id;

    $signatureEndpoint = (!$isPreview && isset($invitation) && $invitation->id && \Illuminate\Support\Facades\Route::has('invitation.signature.save'))
        ? route('invitation.signature.save', $invitation->id)
        : '';

    $resolveAsset = function (string $relativePath, ?string $overrideUrl = null) use ($slug): string {
        $cleanRelative = ltrim(preg_replace('#^/?mau-thiep/[^/]+/assets/#', '', $relativePath), '/');
        $cleanRelative = ltrim(preg_replace('#^assets/#', '', $cleanRelative), '/');
        $fallbackUrl   = "/mau-thiep/{$slug}/assets/{$cleanRelative}";

        $override = trim((string) ($overrideUrl ?? ''));
        if ($override === '' || str_starts_with($override, 'blob:')) {
            return $fallbackUrl;
        }

        if (str_starts_with($override, 'data:')) {
            if (preg_match('#^data:image/(png|jpeg|jpg|webp|gif);base64,[A-Za-z0-9+/=]+$#i', $override)
                || preg_match('#^data:image/svg\+xml;base64,[A-Za-z0-9+/=]+$#i', $override)) {
                return $override;
            }
            return $fallbackUrl;
        }

        if (preg_match('#/(storage|uploads)/(.+)$#', $override, $storageMatch)) {
            $storageRelative = $storageMatch[1] . '/' . $storageMatch[2];
            if (file_exists(public_path($storageRelative))) {
                return '/' . $storageRelative;
            }
        }
        if (preg_match('#^/?(storage|uploads)/#', $override)) {
            $cleanStorage = ltrim($override, '/');
            if (file_exists(public_path($cleanStorage))) {
                return '/' . $cleanStorage;
            }
        }

        if (preg_match('#^https?://#i', $override)) {
            return $fallbackUrl;
        }

        $cleanOverride = ltrim(preg_replace('#^/?mau-thiep/[^/]+/assets/#', '', $override), '/');
        $cleanOverride = ltrim(preg_replace('#^assets/#', '', $cleanOverride), '/');
        $localFile     = resource_path("views/client/invitations/{$slug}/assets/{$cleanOverride}");
        if (file_exists($localFile)) {
            return "/mau-thiep/{$slug}/assets/{$cleanOverride}";
        }

        return $fallbackUrl;
    };

    // 1. Names & Dates
    $groomName = trim((string) ($wedding->groom_name ?? $extraData['groom_name'] ?? 'Hoàng Nam')) ?: 'Hoàng Nam';
    $brideName = trim((string) ($wedding->bride_name ?? $extraData['bride_name'] ?? 'Minh Anh')) ?: 'Minh Anh';
    $groomWords = preg_split('/\s+/u', $groomName) ?: [$groomName];
    $brideWords = preg_split('/\s+/u', $brideName) ?: [$brideName];
    $groomInitial = mb_strtoupper(mb_substr(end($groomWords) ?: $groomName, 0, 1));
    $brideInitial = mb_strtoupper(mb_substr(end($brideWords) ?: $brideName, 0, 1));

    $guestName = (isset($guest) && !empty($guest->name))
        ? trim(($guest->salutation ? $guest->salutation . ' ' : '') . $guest->name)
        : (trim((string) request()->query('guest', request()->query('to', 'Quý Khách'))) ?: 'Quý Khách');

    $ceremonyType = trim((string) ($extraData['ceremony_type'] ?? 'Lễ Thành Hôn')) ?: 'Lễ Thành Hôn';
    $slogan = trim((string) ($extraData['slogan'] ?? 'Together for life')) ?: 'Together for life';

    $rawDate = $wedding->wedding_date ?? $extraData['wedding_date'] ?? '2027-01-16';
    try {
        $weddingDate = \Carbon\Carbon::parse($rawDate);
    } catch (\Throwable $e) {
        $weddingDate = \Carbon\Carbon::parse('2027-01-16');
    }
    $dayNum = $weddingDate->format('d');
    $monthNum = $weddingDate->format('m');
    $yearNum = $weddingDate->format('Y');
    $isoDate = $weddingDate->format('Y-m-d');
    $dateDigits = "{$dayNum}.{$monthNum}.{$yearNum}";
    $dateShort = "{$dayNum}.{$monthNum}";

    $weekdayMap = [
        0 => 'Chủ Nhật', 1 => 'Thứ Hai', 2 => 'Thứ Ba',
        3 => 'Thứ Tư', 4 => 'Thứ Năm', 5 => 'Thứ Sáu', 6 => 'Thứ Bảy',
    ];
    $weekdayLabel = $weekdayMap[(int) $weddingDate->dayOfWeek] ?? 'Thứ Bảy';
    $lunarDate = trim((string) ($extraData['wedding_lunar_date'] ?? '(Tức ngày 09 tháng 12 năm Bính Ngọ)'));

    // 2. Parents & Couple
    $groomFather = trim((string) ($extraData['groom_father'] ?? 'Ông: Lê Văn Bình'));
    $groomMother = trim((string) ($extraData['groom_mother'] ?? 'Bà: Phạm Thị Mai'));
    $groomParentsAddress = trim((string) ($extraData['groom_parents_address'] ?? 'Số 12, đường Hoa Sữa, Phường Trung Tâm, Hà Nội'));

    $brideFather = trim((string) ($extraData['bride_father'] ?? 'Ông: Nguyễn Văn An'));
    $brideMother = trim((string) ($extraData['bride_mother'] ?? 'Bà: Trần Thị Hoa'));
    $brideParentsAddress = trim((string) ($extraData['bride_parents_address'] ?? 'Số 25, đường Hoa Lan, Phường Trung Tâm, Hà Nội'));

    $groomBio = trim((string) ($extraData['groom_bio'] ?? 'Chàng trai ấm áp luôn chọn bình yên bên em qua từng thanh âm của cuộc sống.'));
    $brideBio = trim((string) ($extraData['bride_bio'] ?? 'Cô gái dịu dàng mang nụ cười nắng sớm, viết tiếp bản tình ca trăm năm.'));

    // 3. Events & Venue
    $partyTime = trim((string) ($extraData['party_time'] ?? $extraData['event_target_time'] ?? '17:30')) ?: '17:30';
    $rawPartyVenue = trim((string) ($extraData['party_venue'] ?? ''));
    $rawWeddingVenue = (isset($wedding) && !empty($wedding->venue_name) && $wedding->venue_name !== 'Trung Tâm Tiệc Cưới')
        ? trim((string) $wedding->venue_name)
        : '';
    $partyVenue = $rawPartyVenue !== '' ? $rawPartyVenue : ($rawWeddingVenue !== '' ? $rawWeddingVenue : 'Hoa Viên Palace');

    $rawPartyAddr = trim((string) ($extraData['event_party_address'] ?? ''));
    $rawWeddingAddr = (isset($wedding) && !empty($wedding->venue_address) && $wedding->venue_address !== 'TP. Hồ Chí Minh')
        ? trim((string) $wedding->venue_address)
        : '';
    $partyAddress = $rawPartyAddr !== '' ? $rawPartyAddr : ($rawWeddingAddr !== '' ? $rawWeddingAddr : 'Số 88, đường Hồ Tây, Phường Tây Hồ, Hà Nội');

    $ceremonyGroomTime = trim((string) ($extraData['ceremony_groom_time'] ?? '10:00')) ?: '10:00';
    $ceremonyGroomAddress = trim((string) ($extraData['ceremony_groom_address'] ?? $groomParentsAddress));
    $ceremonyTime = trim((string) ($extraData['ceremony_time'] ?? '08:00')) ?: '08:00';
    $ceremonyAddress = trim((string) ($extraData['ceremony_address'] ?? $brideParentsAddress));

    $mapLink = trim((string) ($extraData['event_party_map_link'] ?? ''));
    if ($mapLink === '' || !preg_match('#^https?://#i', $mapLink)) {
        $mapLink = 'https://maps.google.com/?q=' . urlencode("{$partyVenue}, {$partyAddress}");
    }
    $mapEmbedUrl = 'https://maps.google.com/maps?q=' . urlencode("{$partyVenue}, {$partyAddress}") . '&t=&z=15&ie=UTF8&iwloc=&output=embed';

    // Countdown & Google Calendar
    $countdownTime = preg_match('/^\d{1,2}:\d{2}/', $partyTime, $tm) ? $tm[0] : '17:30';
    $targetIso = "{$isoDate}T{$countdownTime}:00+07:00";
    try {
        $targetCarbon = \Carbon\Carbon::parse($targetIso);
    } catch (\Throwable $e) {
        $targetCarbon = $weddingDate->copy()->setTime(17, 30);
    }
    $gcalStart = $targetCarbon->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
    $gcalEnd = $targetCarbon->copy()->addHours(3)->setTimezone('UTC')->format('Ymd\THis\Z');
    $gcalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . urlencode("Lễ Cưới {$groomName} & {$brideName}")
        . '&dates=' . $gcalStart . '/' . $gcalEnd
        . '&location=' . urlencode("{$partyVenue}, {$partyAddress}");

    // 4. Story
    $storyTitle = trim((string) ($extraData['story_title'] ?? 'Love Story')) ?: 'Love Story';
    $storySubtitle = trim((string) ($extraData['story_subtitle'] ?? '2019 · Lần đầu gặp gỡ'));
    $coupleQuote = trim((string) ($extraData['couple_quote'] ?? 'Hôm đó trời không đẹp, mình cũng chẳng có tâm trạng gì đặc biệt. Nhưng anh xuất hiện, chỉ là một người nói chuyện đủ ấm để mình muốn trả lời thêm lần nữa.'));
    $storyContent = trim((string) ($extraData['story_content'] ?? 'Tình yêu của tụi mình không ồn ào, cũng chẳng hoàn hảo. Nhưng sau tất cả, cả hai vẫn chọn ở bên nhau như một bản nhạc không đoạn kết.'));

    // 5. Images (100% Local Assets via $resolveAsset)
    $coverPhotoUrl = $resolveAsset('images/hero-couple.webp', $wedding->cover_photo_url ?? ($extraData['cover_photo_url'] ?? ($extraData['hero_photo_url'] ?? null)));
    $openingPhotoUrl = $resolveAsset('images/opening-heart.webp', $wedding->cover_photo_url ?? null);
    $saveDatePhotoUrl = $resolveAsset('images/story-01.webp', $extraData['save_date_photo_url'] ?? null);
    $groomPhotoUrl = $resolveAsset('images/groom-portrait.webp', $extraData['groom_photo_url'] ?? null);
    $bridePhotoUrl = $resolveAsset('images/bride-portrait.webp', $extraData['bride_photo_url'] ?? null);
    $vinylLabelUrl = $resolveAsset('images/vinyl-label.webp', null);
    $mapCoverUrl = $resolveAsset('images/map-cover.webp', null);
    $footerPhotoUrl = $resolveAsset('images/ending-arch.webp', $extraData['footer_photo_url'] ?? null);

    $stdPhoto1 = $resolveAsset('images/std-01.webp', null);
    $stdPhoto2 = $resolveAsset('images/std-02.webp', null);
    $stdPhoto3 = $resolveAsset('images/std-03.webp', null);
    $storyPhoto2 = $resolveAsset('images/story-02.webp', null);
    $storyPhoto3 = $resolveAsset('images/story-03.webp', null);
    $slidePhoto1 = $resolveAsset('images/slide-01.webp', null);
    $slidePhoto2 = $resolveAsset('images/slide-02.webp', null);

    // 6. Timeline (up to 6 items)
    $defaultTimeline = [
        ['time' => '15:45', 'title' => 'Đón khách'],
        ['time' => '16:15', 'title' => 'Nghi thức thành hôn'],
        ['time' => '17:00', 'title' => 'Chụp hình lưu niệm'],
        ['time' => '17:45', 'title' => 'Khai tiệc'],
        ['time' => '19:00', 'title' => 'Cắt bánh · Rót rượu'],
        ['time' => '20:00', 'title' => 'Tiệc chung vui'],
    ];
    $rawTimeline = $extraData['timeline'] ?? [];
    if (is_string($rawTimeline)) {
        $rawTimeline = json_decode($rawTimeline, true) ?: [];
    }
    $timelineItems = (is_array($rawTimeline) && count($rawTimeline) > 0) ? array_slice($rawTimeline, 0, 6) : $defaultTimeline;

    // 7. Dresscode Swatches
    $dresscodeNote = trim((string) ($extraData['dresscode_note'] ?? 'Chúng tôi rất mong quý khách lựa chọn trang phục theo các tông màu dưới đây để cùng hòa vào không khí buổi tiệc.'));
    $rawColors = $extraData['dresscode_colors'] ?? '#ECDBD4,#D3B2A3,#8C695A,#FAF1ED';
    if (is_array($rawColors)) {
        $colorHexList = array_values(array_filter(array_map('trim', $rawColors)));
    } else {
        $colorHexList = array_values(array_filter(array_map('trim', explode(',', (string) $rawColors))));
    }
    if (count($colorHexList) === 0) {
        $colorHexList = ['#ECDBD4', '#D3B2A3', '#8C695A', '#FAF1ED'];
    }
    $defaultColorNames = ['Hồng Phấn', 'Hồng Đất', 'Nâu Đất', 'Kem Sữa', 'Vàng Đồng', 'Be Nhạt'];
    $swatchRotations = ['-2.5deg', '2deg', '-1.4deg', '2.6deg', '-1.8deg', '1.6deg'];

    // 8. Album 10 Photos
    $rawGallery = $extraData['gallery'] ?? [];
    if (is_string($rawGallery)) {
        $rawGallery = json_decode($rawGallery, true) ?: [];
    }
    $galleryPhotos = [];
    for ($i = 0; $i < 10; $i++) {
        $overrideImg = (is_array($rawGallery) && !empty($rawGallery[$i])) ? (string) $rawGallery[$i] : null;
        $galleryPhotos[$i] = $resolveAsset(sprintf('images/gallery-%02d.webp', $i + 1), $overrideImg);
    }

    // 9. VietQR Dynamic Gift Cards
    $groomBankName = trim((string) ($extraData['groom_bank_name'] ?? 'Techcombank')) ?: 'Techcombank';
    $groomBankAccount = trim((string) ($extraData['groom_bank_account'] ?? '19034567890123')) ?: '19034567890123';
    $groomAccountName = trim((string) ($extraData['groom_account_name'] ?? $extraData['groom_bank_owner'] ?? 'LE HOANG NAM')) ?: 'LE HOANG NAM';
    $groomQrRaw = $extraData['qr_groom_image'] ?? ($extraData['groom_qr_url'] ?? null);
    $qrGroomUrl = \App\Services\VietQrService::resolveQrImage(
        $groomQrRaw,
        $groomBankName,
        $groomBankAccount,
        'Mung cuoi ' . $groomName,
        $resolveAsset('images/qr-groom.webp', $groomQrRaw),
        '19034567890123'
    );

    $brideBankName = trim((string) ($extraData['bride_bank_name'] ?? 'Vietcombank')) ?: 'Vietcombank';
    $brideBankAccount = trim((string) ($extraData['bride_bank_account'] ?? '1029384756')) ?: '1029384756';
    $brideAccountName = trim((string) ($extraData['bride_account_name'] ?? $extraData['bride_bank_owner'] ?? 'NGUYEN MINH ANH')) ?: 'NGUYEN MINH ANH';
    $brideQrRaw = $extraData['qr_bride_image'] ?? ($extraData['bride_qr_url'] ?? null);
    $qrBrideUrl = \App\Services\VietQrService::resolveQrImage(
        $brideQrRaw,
        $brideBankName,
        $brideBankAccount,
        'Mung cuoi ' . $brideName,
        $resolveAsset('images/qr-bride.webp', $brideQrRaw),
        '1029384756'
    );

    // 10. Ending & Audio
    $thankYouTitle = trim((string) ($extraData['thank_you_title'] ?? 'Thank You!')) ?: 'Thank You!';
    $thankYouQuote = trim((string) ($extraData['thank_you_quote'] ?? 'Sự hiện diện của Quý Khách là niềm vinh hạnh cho gia đình chúng tôi.'));
    $musicUrl = $resolveAsset('wedding-song.mp3', $extraData['music_url'] ?? null);
    $approvedWishes = (isset($wishes) && $wishes instanceof \Illuminate\Support\Collection) ? $wishes : collect();
@endphp

@section('title', "Thiệp Cưới {$groomName} & {$brideName} — Bản Nhạc Không Đoạn Kết")
@section('body_class', 'mayhen_invitation_body_w21')

@push('styles')
    <link rel="stylesheet" href="{{ route('templates.asset', ['slug' => $slug, 'path' => 'styles.css']) }}?v=1.0.0">
@endpush

@section('content')
<div
    id="mayhen_invitation_root"
    class="mayhen_invitation_root {{ $autoOpened ? 'is-embed opened' : '' }}"
    data-template-slug="{{ $slug }}"
    data-is-embed="{{ $autoOpened ? 'true' : 'false' }}"
    data-is-preview="{{ $isPreview ? 'true' : 'false' }}"
    data-wedding-date="{{ $isoDate }}"
    data-target-time="{{ $countdownTime }}"
    data-target-iso="{{ $targetIso }}"
    data-rsvp-endpoint="{{ $rsvpSubmitUrl ?? '' }}"
    data-wishes-endpoint="{{ $wishSubmitUrl ?? '' }}"
    data-signature-endpoint="{{ $signatureEndpoint }}"
    data-csrf-token="{{ csrf_token() }}"
    data-guest-name="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}"
>
    {{-- Audio Player (Nhường music-dock.blade.php không phát chồng 2 nhạc) --}}
    <audio
        id="mayhenBgAudio"
        class="mayhen_invitation_audio"
        data-mayhen-audio
        data-sync-target="music_url"
        src="{{ $musicUrl }}"
        loop
        preload="none"
        playsinline
    ></audio>

    {{-- Nút nhạc nổi góc trái dưới --}}
    <button
        type="button"
        id="mayhenMusicBtn"
        class="mayhen_invitation_music_btn"
        aria-label="Bật hoặc tạm dừng nhạc nền"
        aria-pressed="false"
    >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6Z"/>
        </svg>
    </button>

    {{-- Lớp bướm phấn bay nhẹ --}}
    <div id="w21Buom" class="mayhen_invitation_butterflies" aria-hidden="true"></div>

    {{-- ==================== MÀN MỞ THIỆP: HAI CÁNH CỬA KÍNH + 3 VÒM LỒNG NHAU ==================== --}}
    @if(!$autoOpened)
    <div
        id="w21Mo"
        class="mayhen_invitation_opening"
        data-mayhen-section="opening"
        role="dialog"
        aria-label="Màn mở thiệp mời cưới"
    >
        <div class="mayhen_invitation_door mayhen_invitation_door--left">
            <i class="mayhen_invitation_door_glass mayhen_invitation_tt" aria-hidden="true"></i>
            <i class="mayhen_invitation_door_arch mayhen_invitation_door_arch--1 mayhen_invitation_tt" aria-hidden="true"></i>
            <i class="mayhen_invitation_door_arch mayhen_invitation_door_arch--2 mayhen_invitation_tt" aria-hidden="true"></i>
            <i class="mayhen_invitation_door_arch mayhen_invitation_door_arch--3 mayhen_invitation_tt" aria-hidden="true"></i>
        </div>
        <div class="mayhen_invitation_door mayhen_invitation_door--right">
            <i class="mayhen_invitation_door_glass mayhen_invitation_tt" aria-hidden="true"></i>
            <i class="mayhen_invitation_door_arch mayhen_invitation_door_arch--1 mayhen_invitation_tt" aria-hidden="true"></i>
            <i class="mayhen_invitation_door_arch mayhen_invitation_door_arch--2 mayhen_invitation_tt" aria-hidden="true"></i>
            <i class="mayhen_invitation_door_arch mayhen_invitation_door_arch--3 mayhen_invitation_tt" aria-hidden="true"></i>
        </div>
        <div class="mayhen_invitation_opening_content">
            <p class="mayhen_invitation_opening_kicker">KÍNH GỬI</p>
            <p class="mayhen_invitation_opening_guest">{{ $guestName }}</p>
            <div class="mayhen_invitation_opening_heart mayhen_invitation_photo_frame">
                <img src="{{ $openingPhotoUrl }}" alt="Ảnh mở thiệp {{ $groomName }} và {{ $brideName }}">
            </div>
            <p class="mayhen_invitation_opening_name mayhen_invitation_viet mayhen_invitation_gr" data-sync-target="groom_name">{{ $groomName }}</p>
            <div class="mayhen_invitation_opening_amp mayhen_invitation_tt" aria-hidden="true">&amp;</div>
            <p class="mayhen_invitation_opening_name mayhen_invitation_viet mayhen_invitation_gr" data-sync-target="bride_name">{{ $brideName }}</p>
            <button type="button" id="w21NutMo" class="mayhen_invitation_open_btn" data-mayhen-open-trigger>
                ẤN MỞ THIỆP
            </button>
        </div>
    </div>
    @endif

    {{-- ==================== NỘI DUNG CHÍNH THIỆP CƯỚI ==================== --}}
    <main class="mayhen_invitation_card_wrapper card-wrapper" id="w21Thiep">

        {{-- ==================== 1. HERO COVER (ĐẦU THIỆP) ==================== --}}
        <section
            id="mayhen_section_hero"
            data-mayhen-section="hero"
            class="mayhen_invitation_section_block mayhen_invitation_hero"
        >
            <div class="mayhen_invitation_section_bg"></div>
            <i class="mayhen_invitation_hero_leaf_pc mayhen_invitation_tt" aria-hidden="true"></i>

            <div class="mayhen_invitation_hero_card mayhen_invitation_reveal" data-reveal="pop">
                <i class="mayhen_invitation_hero_card_back mayhen_invitation_tt" aria-hidden="true"></i>
                <div class="mayhen_invitation_hero_photo mayhen_invitation_photo_frame" data-lightbox-src="{{ $coverPhotoUrl }}">
                    <img src="{{ $coverPhotoUrl }}" alt="Ảnh bìa {{ $groomName }} và {{ $brideName }}" data-sync-target="cover_photo_url">
                </div>
                <p class="mayhen_invitation_hero_slogan mayhen_invitation_viet mayhen_invitation_gr" data-sync-target="slogan">{{ $slogan }}</p>
                <p class="mayhen_invitation_hero_vertical">HAPPY WEDDING</p>
                <p class="mayhen_invitation_hero_date" data-sync-target="wedding_date_digits">{{ $dateShort }}</p>
            </div>

            <div class="mayhen_invitation_hero_names mayhen_invitation_reveal" data-reveal="right">
                <i class="mayhen_invitation_hero_flower mayhen_invitation_tt" aria-hidden="true"></i>
                <i class="mayhen_invitation_hero_leaf mayhen_invitation_tt" aria-hidden="true"></i>
                <h1 class="mayhen_invitation_hero_name_text mayhen_invitation_viet mayhen_invitation_gr" data-sync-target="groom_name">{{ $groomName }}</h1>
                <div class="mayhen_invitation_hero_amp mayhen_invitation_tt" aria-hidden="true">&amp;</div>
                <h1 class="mayhen_invitation_hero_name_text mayhen_invitation_viet mayhen_invitation_gr" data-sync-target="bride_name">{{ $brideName }}</h1>
            </div>
        </section>

        {{-- ==================== 2. COUNTDOWN & CALENDAR (SAVE THE DATE + LỊCH + ĐẾM NGƯỢC) ==================== --}}
        <div id="mayhen_section_countdown_calendar" data-mayhen-section="countdown_calendar">
            <section class="mayhen_invitation_section_block mayhen_invitation_std">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_std_title mayhen_invitation_reveal" data-reveal="pop">
                    <h2>Save The Date</h2>
                </div>

                <div class="mayhen_invitation_std_cols">
                    <div class="mayhen_invitation_std_col mayhen_invitation_reveal" data-reveal="left">
                        <div class="mayhen_invitation_std_photo mayhen_invitation_photo_frame">
                            <img src="{{ $stdPhoto1 }}" alt="Ảnh ngày cưới">
                        </div>
                        <i class="mayhen_invitation_std_fade mayhen_invitation_tt" aria-hidden="true"></i>
                        <p class="mayhen_invitation_std_num" data-sync-target="wedding_day">{{ $dayNum }}</p>
                    </div>
                    <div class="mayhen_invitation_std_col mayhen_invitation_reveal" data-reveal="down">
                        <div class="mayhen_invitation_std_photo mayhen_invitation_photo_frame">
                            <img src="{{ $stdPhoto2 }}" alt="Ảnh tháng cưới">
                        </div>
                        <i class="mayhen_invitation_std_fade mayhen_invitation_tt" aria-hidden="true"></i>
                        <p class="mayhen_invitation_std_num" data-sync-target="wedding_month">{{ $monthNum }}</p>
                    </div>
                    <div class="mayhen_invitation_std_col mayhen_invitation_reveal" data-reveal="right">
                        <div class="mayhen_invitation_std_photo mayhen_invitation_photo_frame">
                            <img src="{{ $stdPhoto3 }}" alt="Ảnh năm cưới">
                        </div>
                        <i class="mayhen_invitation_std_fade mayhen_invitation_tt" aria-hidden="true"></i>
                        <p class="mayhen_invitation_std_num" data-sync-target="wedding_year">{{ $yearNum }}</p>
                    </div>
                </div>

                {{-- 5 Quạt giấy xếp nan --}}
                <div class="mayhen_invitation_fan_row mayhen_invitation_tt mayhen_invitation_reveal" data-reveal="up" aria-hidden="true">
                    <i class="mayhen_invitation_fan_item" style="--l:18%;--w:22%;--y:0.705"></i>
                    <i class="mayhen_invitation_fan_item" style="--l:82%;--w:22%;--y:0.705"></i>
                    <i class="mayhen_invitation_fan_item" style="--l:31%;--w:30%;--y:0.65"></i>
                    <i class="mayhen_invitation_fan_item" style="--l:69%;--w:30%;--y:0.65"></i>
                    <i class="mayhen_invitation_fan_item" style="--l:50%;--w:40%;--y:0.62"></i>
                </div>

                {{-- Lịch Tháng Cưới --}}
                <div class="mayhen_invitation_calendar_card mayhen_invitation_reveal" data-reveal="up">
                    <div class="mayhen_invitation_calendar_month" data-sync-target="calendar_month_year">
                        THÁNG {{ $monthNum }} · {{ $yearNum }}
                    </div>
                    <div class="mayhen_invitation_calendar_head">
                        <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                    </div>
                    <div class="mayhen_invitation_calendar_body" data-sync-target="wedding_calendar"></div>
                </div>
            </section>

            <div class="mayhen_invitation_divider" aria-hidden="true"></div>

            {{-- Đồng hồ đếm ngược dọc + Dải ảnh chạy dọc --}}
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_cd_grid">
                    <div class="mayhen_invitation_cd_head">
                        <p class="mayhen_invitation_cd_title mayhen_invitation_viet mayhen_invitation_gr mayhen_invitation_reveal" data-reveal="right">Count Down</p>
                        <p class="mayhen_invitation_cd_desc mayhen_invitation_reveal" data-reveal="up">Cùng đếm ngược<br>tới ngày hôm đó nhé</p>
                    </div>
                    <div class="mayhen_invitation_countdown_col mayhen_invitation_reveal" data-reveal="pop" data-countdown="{{ $targetIso }}">
                        <div class="mayhen_invitation_cd_item">
                            <span class="mayhen_invitation_cd_num mayhen_invitation_cd_day" data-sync-target="countdown_day">00</span>
                            <span class="mayhen_invitation_cd_lbl">Ngày</span>
                        </div>
                        <div class="mayhen_invitation_cd_item">
                            <span class="mayhen_invitation_cd_num mayhen_invitation_cd_hour" data-sync-target="countdown_hour">00</span>
                            <span class="mayhen_invitation_cd_lbl">Giờ</span>
                        </div>
                        <div class="mayhen_invitation_cd_item">
                            <span class="mayhen_invitation_cd_num mayhen_invitation_cd_min" data-sync-target="countdown_min">00</span>
                            <span class="mayhen_invitation_cd_lbl">Phút</span>
                        </div>
                        <div class="mayhen_invitation_cd_item">
                            <span class="mayhen_invitation_cd_num mayhen_invitation_cd_sec" data-sync-target="countdown_sec">00</span>
                            <span class="mayhen_invitation_cd_lbl">Giây</span>
                        </div>
                    </div>
                    <div class="mayhen_invitation_cd_slide_wrap mayhen_invitation_reveal" data-reveal="right">
                        <div class="mayhen_invitation_cd_slide">
                            <div class="mayhen_invitation_cd_track">
                                <img src="{{ $slidePhoto1 }}" alt="Khoảnh khắc cưới 1" loading="lazy">
                                <img src="{{ $slidePhoto2 }}" alt="Khoảnh khắc cưới 2" loading="lazy">
                                <img src="{{ $galleryPhotos[0] }}" alt="Khoảnh khắc cưới 3" loading="lazy">
                                <img src="{{ $slidePhoto1 }}" alt="Khoảnh khắc cưới 4" loading="lazy">
                                <img src="{{ $slidePhoto2 }}" alt="Khoảnh khắc cưới 5" loading="lazy">
                                <img src="{{ $galleryPhotos[0] }}" alt="Khoảnh khắc cưới 6" loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== 3. PARENTS & COUPLE (SONG THÂN + ĐĨA THAN QUAY) ==================== --}}
        <section
            id="mayhen_section_parents"
            data-mayhen-section="parents"
            class="mayhen_invitation_section_block mayhen_invitation_parents"
        >
            <div class="mayhen_invitation_section_bg"></div>

            {{-- Cô Dâu, Chú Rể & Đĩa Than Quay ở giữa --}}
            <div class="mayhen_invitation_couple_row">
                <div class="mayhen_invitation_person_col">
                    <div class="mayhen_invitation_person_card mayhen_invitation_reveal" data-reveal="up">
                        <div class="mayhen_invitation_person_bg" style="background-image: url('{{ $groomPhotoUrl }}');"></div>
                        <i class="mayhen_invitation_person_shade mayhen_invitation_tt" aria-hidden="true"></i>
                        <i class="mayhen_invitation_person_glass mayhen_invitation_tt" aria-hidden="true"></i>
                        <div class="mayhen_invitation_person_photo mayhen_invitation_photo_frame" data-lightbox-src="{{ $groomPhotoUrl }}">
                            <img src="{{ $groomPhotoUrl }}" alt="Chú Rể {{ $groomName }}" data-sync-target="groom_photo_url">
                        </div>
                        <i class="mayhen_invitation_person_photo_fade mayhen_invitation_tt" aria-hidden="true"></i>
                        <p class="mayhen_invitation_person_role">Chú Rể</p>
                        <h3 class="mayhen_invitation_person_name mayhen_invitation_viet" data-sync-target="groom_name">{{ $groomName }}</h3>
                    </div>
                    <p class="mayhen_invitation_person_bio mayhen_invitation_reveal" data-reveal="up" data-sync-target="groom_bio">{{ $groomBio }}</p>
                </div>

                {{-- Đĩa Than Quay ("Bản Nhạc Không Đoạn Kết") --}}
                <div id="w21VinylWrap" class="mayhen_invitation_vinyl_wrap is-paused mayhen_invitation_reveal" data-reveal="pop">
                    <i class="mayhen_invitation_vinyl_wreath mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_vinyl_ring mayhen_invitation_vinyl_ring--1 mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_vinyl_ring mayhen_invitation_vinyl_ring--2 mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_vinyl_wave mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_vinyl_wave mayhen_invitation_vinyl_wave--2 mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_vinyl_shadow mayhen_invitation_tt" aria-hidden="true"></i>
                    <div class="mayhen_invitation_vinyl_disc">
                        <div class="mayhen_invitation_vinyl_label mayhen_invitation_photo_frame">
                            <img src="{{ $vinylLabelUrl }}" alt="Nhãn đĩa nhạc hôn lễ">
                        </div>
                    </div>
                    <i class="mayhen_invitation_vinyl_arm mayhen_invitation_tt" aria-hidden="true"></i>
                    <button type="button" id="w21Loa" class="mayhen_invitation_vinyl_btn" aria-label="Phát hoặc dừng đĩa nhạc" title="Phát / dừng nhạc">
                        <svg class="icon-pause" viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                        <svg class="icon-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.2v13.6a.8.8 0 0 0 1.2.7l10.6-6.8a.8.8 0 0 0 0-1.4L9.2 4.5A.8.8 0 0 0 8 5.2z"/></svg>
                    </button>
                </div>

                <div class="mayhen_invitation_person_col">
                    <div class="mayhen_invitation_person_card mayhen_invitation_reveal" data-reveal="up">
                        <div class="mayhen_invitation_person_bg" style="background-image: url('{{ $bridePhotoUrl }}');"></div>
                        <i class="mayhen_invitation_person_shade mayhen_invitation_tt" aria-hidden="true"></i>
                        <i class="mayhen_invitation_person_glass mayhen_invitation_tt" aria-hidden="true"></i>
                        <div class="mayhen_invitation_person_photo mayhen_invitation_photo_frame" data-lightbox-src="{{ $bridePhotoUrl }}">
                            <img src="{{ $bridePhotoUrl }}" alt="Cô Dâu {{ $brideName }}" data-sync-target="bride_photo_url">
                        </div>
                        <i class="mayhen_invitation_person_photo_fade mayhen_invitation_tt" aria-hidden="true"></i>
                        <p class="mayhen_invitation_person_role">Cô Dâu</p>
                        <h3 class="mayhen_invitation_person_name mayhen_invitation_viet" data-sync-target="bride_name">{{ $brideName }}</h3>
                    </div>
                    <p class="mayhen_invitation_person_bio mayhen_invitation_reveal" data-reveal="up" data-sync-target="bride_bio">{{ $brideBio }}</p>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== 4. EVENTS & MAPS (LỄ THÀNH HÔN + THƯ MỜI + BẢN ĐỒ 2 CÁNH) ==================== --}}
        <div id="mayhen_section_events" data-mayhen-section="events">
            {{-- 4A. Thẻ Song Thân & Nghi Lễ --}}
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_ceremony_card mayhen_invitation_reveal" data-reveal="up">
                    <div class="mayhen_invitation_family_grid">
                        <div>
                            <h3 class="mayhen_invitation_family_side">Nhà trai</h3>
                            <p class="mayhen_invitation_family_parents">
                                <span data-sync-target="groom_father">{{ $groomFather }}</span><br>
                                <span data-sync-target="groom_mother">{{ $groomMother }}</span>
                            </p>
                            <p class="mayhen_invitation_family_addr" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                        </div>
                        <i class="mayhen_invitation_family_sep mayhen_invitation_tt" aria-hidden="true"></i>
                        <div>
                            <h3 class="mayhen_invitation_family_side">Nhà gái</h3>
                            <p class="mayhen_invitation_family_parents">
                                <span data-sync-target="bride_father">{{ $brideFather }}</span><br>
                                <span data-sync-target="bride_mother">{{ $brideMother }}</span>
                            </p>
                            <p class="mayhen_invitation_family_addr" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                        </div>
                    </div>

                    <h2 class="mayhen_invitation_ceremony_title" data-sync-target="ceremony_type">{{ $ceremonyType }}</h2>
                    <i class="mayhen_invitation_rings_icon mayhen_invitation_tt" aria-hidden="true"></i>

                    <div class="mayhen_invitation_ceremony_date_grid">
                        <div class="mayhen_invitation_ceremony_side_lbl" data-sync-target="wedding_weekday">{{ $weekdayLabel }}</div>
                        <div class="mayhen_invitation_ceremony_day_box">
                            <div class="mayhen_invitation_ceremony_day_lbl">Ngày</div>
                            <div class="mayhen_invitation_ceremony_day_num" data-sync-target="wedding_day">{{ $dayNum }}</div>
                            <div class="mayhen_invitation_ceremony_month_lbl">Tháng <span data-sync-target="wedding_month">{{ $monthNum }}</span></div>
                        </div>
                        <div class="mayhen_invitation_ceremony_side_lbl">Năm<br><span data-sync-target="wedding_year">{{ $yearNum }}</span></div>
                    </div>

                    <p class="mayhen_invitation_ceremony_time">Vào lúc <span data-sync-target="ceremony_groom_time">{{ $ceremonyGroomTime }}</span></p>
                    <p class="mayhen_invitation_lunar_text" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</p>

                    <div class="mayhen_invitation_ceremony_place">
                        <p class="mayhen_invitation_ceremony_venue_lbl">TẠI TƯ GIA NHÀ TRAI</p>
                        <p class="mayhen_invitation_ceremony_addr" data-sync-target="ceremony_groom_address">{{ $ceremonyGroomAddress }}</p>
                        <a
                            id="mayhenAddCalLink"
                            class="mayhen_invitation_add_cal_btn"
                            href="{{ $gcalUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2zM5 9h14v11H5z"/></svg>
                            <span>Thêm vào lịch</span>
                        </a>
                    </div>

                    {{-- Lễ Vu Quy Nhà Gái --}}
                    <div class="mayhen_invitation_ceremony_extra">
                        <i class="mayhen_invitation_gach mayhen_invitation_tt" aria-hidden="true"></i>
                        <h3 class="mayhen_invitation_ceremony_title">Lễ Vu Quy</h3>
                        <p class="mayhen_invitation_ceremony_time">Vào lúc <span data-sync-target="ceremony_time">{{ $ceremonyTime }}</span></p>
                        <p class="mayhen_invitation_ceremony_venue_lbl">TẠI TƯ GIA NHÀ GÁI</p>
                        <p class="mayhen_invitation_ceremony_addr" data-sync-target="ceremony_address">{{ $ceremonyAddress }}</p>
                    </div>
                </div>
            </section>

            <div class="mayhen_invitation_divider" aria-hidden="true"></div>

            {{-- 4B. Thư Mời Tiệc Cưới & Bản Đồ 2 Cánh Cửa Tương Tác --}}
            <section class="mayhen_invitation_section_block mayhen_invitation_invite_section">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_invite_card mayhen_invitation_reveal" data-reveal="up">
                    <i class="mayhen_invitation_invite_border_top mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_invite_border_bot mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_invite_leaf1 mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_invite_leaf2 mayhen_invitation_tt" aria-hidden="true"></i>

                    <div class="mayhen_invitation_invite_head">
                        <p class="mayhen_invitation_invite_heading mayhen_invitation_gr">Trân Trọng Kính Mời</p>
                        <p class="mayhen_invitation_invite_guest">{{ $guestName }}</p>
                        <p class="mayhen_invitation_invite_sub">TỚI DỰ TIỆC MỪNG CƯỚI<br>CÙNG GIA ĐÌNH</p>
                    </div>

                    <div class="mayhen_invitation_invite_map_col">
                        <div class="mayhen_invitation_map_box">
                            <i class="mayhen_invitation_map_layer mayhen_invitation_tt" style="--t:12%;--x:-14px" aria-hidden="true"></i>
                            <i class="mayhen_invitation_map_layer mayhen_invitation_tt" style="--t:6%;--x:-7px" aria-hidden="true"></i>
                            <div class="mayhen_invitation_map_card" id="w21TheBd">
                                <iframe
                                    class="mayhen_invitation_map_iframe"
                                    src="{{ $mapEmbedUrl }}"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="Bản đồ {{ $partyVenue }}"
                                ></iframe>
                                <div class="mayhen_invitation_map_door mayhen_invitation_map_door--left mayhen_invitation_tt" aria-hidden="true">
                                    <i class="mayhen_invitation_map_door_img" style="background-image: url('{{ $mapCoverUrl }}');"></i>
                                </div>
                                <div class="mayhen_invitation_map_door mayhen_invitation_map_door--right mayhen_invitation_tt" aria-hidden="true">
                                    <i class="mayhen_invitation_map_door_img" style="background-image: url('{{ $mapCoverUrl }}');"></i>
                                </div>
                                <i class="mayhen_invitation_map_pin mayhen_invitation_tt" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M12 1.6a7.6 7.6 0 0 0-7.6 7.6c0 5.6 7.6 13.2 7.6 13.2s7.6-7.6 7.6-13.2A7.6 7.6 0 0 0 12 1.6zm0 10.4a2.8 2.8 0 1 1 0-5.6 2.8 2.8 0 0 1 0 5.6z"/></svg>
                                </i>
                                <button type="button" id="w21MapToggleBtn" class="mayhen_invitation_map_toggle_btn">
                                    Xem bản đồ
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_invitation_invite_couple">
                        <i class="mayhen_invitation_van mayhen_invitation_tt" aria-hidden="true"></i>
                        <p class="mayhen_invitation_invite_couple_name mayhen_invitation_viet mayhen_invitation_gr" data-sync-target="groom_name">{{ $groomName }}</p>
                        <div class="mayhen_invitation_invite_couple_amp mayhen_invitation_tt" aria-hidden="true">&amp;</div>
                        <p class="mayhen_invitation_invite_couple_name mayhen_invitation_viet mayhen_invitation_gr" data-sync-target="bride_name">{{ $brideName }}</p>
                    </div>

                    <div class="mayhen_invitation_invite_datetime">
                        <p class="mayhen_invitation_invite_time">
                            VÀO LÚC <span data-sync-target="party_time">{{ $partyTime }}</span> - <span data-sync-target="wedding_weekday">{{ $weekdayLabel }}</span>
                        </p>
                        <p class="mayhen_invitation_invite_date" data-sync-target="wedding_date_digits">{{ $dateDigits }}</p>
                        <p class="mayhen_invitation_lunar_text" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</p>
                    </div>

                    <div class="mayhen_invitation_invite_venue_wrap">
                        <p class="mayhen_invitation_invite_venue_kicker">TẠI NHÀ HÀNG</p>
                        <p class="mayhen_invitation_invite_venue_name" data-sync-target="party_venue">{{ $partyVenue }}</p>
                        <p class="mayhen_invitation_invite_venue_addr" data-sync-target="event_party_address">{{ $partyAddress }}</p>
                        <a
                            id="mayhenMapExternalLink"
                            class="mayhen_invitation_map_ext_btn"
                            href="{{ $mapLink }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-sync-target="event_party_map_link"
                        >
                            Mở Google Maps chỉ đường
                        </a>
                    </div>

                    <div class="mayhen_invitation_invite_footer">
                        <i class="mayhen_invitation_gach mayhen_invitation_tt" aria-hidden="true"></i>
                        <p class="mayhen_invitation_invite_welcome">Rất hân hạnh được đón tiếp!</p>
                    </div>
                </div>
            </section>
        </div>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== 5. TIMELINE (LỊCH TRÌNH HÔN LỄ) ==================== --}}
        <section
            id="mayhen_section_timeline"
            data-mayhen-section="timeline"
            class="mayhen_invitation_section_block mayhen_invitation_timeline"
        >
            <div class="mayhen_invitation_section_bg"></div>
            <h2 class="mayhen_invitation_td mayhen_invitation_viet mayhen_invitation_gr mayhen_invitation_gr_vang mayhen_invitation_reveal" data-reveal="up">Timeline</h2>

            <div class="mayhen_invitation_timeline_list">
                @foreach($timelineItems as $idx => $item)
                    @php $isLeft = ($idx % 2 === 0); @endphp
                    <div
                        class="mayhen_invitation_timeline_item {{ $isLeft ? 'mayhen_invitation_timeline_item--left' : 'mayhen_invitation_timeline_item--right' }} mayhen_invitation_reveal"
                        data-reveal="{{ $isLeft ? 'left' : 'right' }}"
                        style="--h: {{ $idx + 1 }}; --c: {{ $isLeft ? 1 : 2 }};"
                    >
                        <i class="mayhen_invitation_timeline_dot mayhen_invitation_tt" aria-hidden="true"></i>
                        <div class="mayhen_invitation_timeline_icon" aria-hidden="true">
                            @if($idx === 0)
                                <svg viewBox="0 0 24 24"><circle cx="9" cy="15" r="6"/><circle cx="15" cy="15" r="6"/><path d="m7 6 2-3 2 3-2 3zM13 6l2-3 2 3-2 3"/></svg>
                            @elseif($idx === 1)
                                <svg viewBox="0 0 24 24"><path d="M12 2v5M9.5 4.5h5M12 7l6 5v9H6v-9zM3 21h18M10 21v-5a2 2 0 0 1 4 0v5"/></svg>
                            @elseif($idx === 2)
                                <svg viewBox="0 0 24 24"><path d="M4 8h3l2-3h6l2 3h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/><circle cx="12" cy="13.5" r="3.6"/></svg>
                            @elseif($idx === 3)
                                <svg viewBox="0 0 24 24"><path d="M7 3h10l-1 7a4 4 0 0 1-8 0zM12 14v6M8 21h8"/></svg>
                            @elseif($idx === 4)
                                <svg viewBox="0 0 24 24"><path d="M4 21h16M6 21v-7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v7M6 16c2 1.6 4 1.6 6 0 2 1.6 4 1.6 6 0M12 12V8"/></svg>
                            @else
                                <svg viewBox="0 0 24 24"><path d="M12 20s-8-5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 9c0 6-8 11-8 11z"/></svg>
                            @endif
                        </div>
                        <p class="mayhen_invitation_timeline_time" data-sync-target="timeline.{{ $idx }}.time">{{ $item['time'] ?? '' }}</p>
                        <p class="mayhen_invitation_timeline_text" data-sync-target="timeline.{{ $idx }}.title">{{ $item['title'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== 6. LOVE STORY (CHUYỆN TÌNH YÊU) ==================== --}}
        <section
            id="mayhen_section_story"
            data-mayhen-section="story"
            class="mayhen_invitation_section_block mayhen_invitation_story"
        >
            <div class="mayhen_invitation_section_bg"></div>
            <h2 class="mayhen_invitation_story_title mayhen_invitation_viet mayhen_invitation_gr mayhen_invitation_reveal" data-reveal="pop" data-sync-target="story_title">{{ $storyTitle }}</h2>

            <article class="mayhen_invitation_story_chapter mayhen_invitation_reveal" data-reveal="left">
                <div class="mayhen_invitation_story_frame">
                    <i class="mayhen_invitation_story_border mayhen_invitation_story_border--a mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_story_border mayhen_invitation_story_border--b mayhen_invitation_tt" aria-hidden="true"></i>
                    <div class="mayhen_invitation_story_photo mayhen_invitation_photo_frame" data-lightbox-src="{{ $saveDatePhotoUrl }}">
                        <img src="{{ $saveDatePhotoUrl }}" alt="Lần đầu gặp gỡ" data-sync-target="save_date_photo_url" loading="lazy">
                    </div>
                </div>
                <div class="mayhen_invitation_story_card">
                    <p class="mayhen_invitation_story_milestone" data-sync-target="story_subtitle">{{ $storySubtitle }}</p>
                    <p class="mayhen_invitation_story_desc" data-sync-target="couple_quote">{{ $coupleQuote }}</p>
                </div>
            </article>

            <article class="mayhen_invitation_story_chapter mayhen_invitation_story_chapter--even mayhen_invitation_reveal" data-reveal="right">
                <div class="mayhen_invitation_story_frame">
                    <i class="mayhen_invitation_story_border mayhen_invitation_story_border--a mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_story_border mayhen_invitation_story_border--b mayhen_invitation_tt" aria-hidden="true"></i>
                    <div class="mayhen_invitation_story_photo mayhen_invitation_photo_frame" data-lightbox-src="{{ $storyPhoto2 }}">
                        <img src="{{ $storyPhoto2 }}" alt="Thương nhau" loading="lazy">
                    </div>
                </div>
                <div class="mayhen_invitation_story_card">
                    <p class="mayhen_invitation_story_milestone">2021 · Thương nhau</p>
                    <p class="mayhen_invitation_story_desc">Ban đầu tụi mình chỉ là hai người xa lạ nói chuyện cho đỡ buồn. Càng nói càng thấy hợp, từ chuyện nhỏ nhặt trong ngày đến những lúc mệt mỏi chỉ cần có người nghe.</p>
                </div>
            </article>

            <article class="mayhen_invitation_story_chapter mayhen_invitation_reveal" data-reveal="left">
                <div class="mayhen_invitation_story_frame">
                    <i class="mayhen_invitation_story_border mayhen_invitation_story_border--a mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_story_border mayhen_invitation_story_border--b mayhen_invitation_tt" aria-hidden="true"></i>
                    <div class="mayhen_invitation_story_photo mayhen_invitation_photo_frame" data-lightbox-src="{{ $storyPhoto3 }}">
                        <img src="{{ $storyPhoto3 }}" alt="Lời cầu hôn" loading="lazy">
                    </div>
                </div>
                <div class="mayhen_invitation_story_card">
                    <p class="mayhen_invitation_story_milestone">2026 · Lời cầu hôn</p>
                    <p class="mayhen_invitation_story_desc" data-sync-target="story_content">{{ $storyContent }}</p>
                </div>
            </article>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== 7. ALBUM 10 PHOTOS ==================== --}}
        <section
            id="mayhen_section_album"
            data-mayhen-section="album"
            class="mayhen_invitation_section_block mayhen_invitation_album"
        >
            <div class="mayhen_invitation_section_bg"></div>

            <div class="mayhen_invitation_album_board mayhen_invitation_reveal" data-reveal="up">
                <i class="mayhen_invitation_album_rail mayhen_invitation_tt" aria-hidden="true"></i>
                <p class="mayhen_invitation_album_hw">HAPPY WEDDING</p>

                <div class="mayhen_invitation_album_featured">
                    @for($g = 0; $g < 4; $g++)
                        <div
                            class="mayhen_invitation_photo_frame tile-{{ $g + 1 }}"
                            data-gallery-index="{{ $g }}"
                            data-lightbox-src="{{ $galleryPhotos[$g] }}"
                        >
                            <img src="{{ $galleryPhotos[$g] }}" alt="Ảnh cưới {{ $g + 1 }}" loading="lazy" data-sync-target="gallery.{{ $g }}">
                        </div>
                    @endfor
                </div>

                <div class="mayhen_invitation_album_row5">
                    <div
                        class="mayhen_invitation_photo_frame tile-5"
                        data-gallery-index="4"
                        data-lightbox-src="{{ $galleryPhotos[4] }}"
                    >
                        <img src="{{ $galleryPhotos[4] }}" alt="Ảnh cưới 5" loading="lazy" data-sync-target="gallery.4">
                    </div>
                    <div class="mayhen_invitation_album_vtitle">
                        <h2 class="mayhen_invitation_album_vtitle_main">Album</h2>
                        <p class="mayhen_invitation_album_vtitle_sub">lưu giữ khoảnh khắc</p>
                    </div>
                </div>

                <p class="mayhen_invitation_album_date" data-sync-target="wedding_date_digits">{{ $dateDigits }}</p>
            </div>

            <div class="mayhen_invitation_album_extra mayhen_invitation_reveal" data-reveal="up">
                @for($g = 5; $g < 10; $g++)
                    <div
                        class="mayhen_invitation_album_thumb mayhen_invitation_photo_frame"
                        data-gallery-index="{{ $g }}"
                        data-lightbox-src="{{ $galleryPhotos[$g] }}"
                    >
                        <img src="{{ $galleryPhotos[$g] }}" alt="Ảnh cưới {{ $g + 1 }}" loading="lazy" data-sync-target="gallery.{{ $g }}">
                    </div>
                @endfor
            </div>
        </section>

        {{-- ==================== 8. DRESSCODE (GỢI Ý TRANG PHỤC — @if(!$isCuratedSeven)) ==================== --}}
        @if(!$isCuratedSeven)
        <div class="mayhen_invitation_divider" aria-hidden="true"></div>
        <section
            id="mayhen_section_dresscode"
            data-mayhen-section="dresscode"
            class="mayhen_invitation_section_block mayhen_invitation_dresscode"
        >
            <div class="mayhen_invitation_section_bg"></div>
            <h2 class="mayhen_invitation_td mayhen_invitation_viet mayhen_invitation_gr mayhen_invitation_gr_vang mayhen_invitation_reveal" data-reveal="pop">Dresscode</h2>
            <p class="mayhen_invitation_dresscode_note mayhen_invitation_reveal" data-reveal="up" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>

            <div
                id="mayhenDresscodeSwatches"
                class="mayhen_invitation_dresscode_swatches mayhen_invitation_reveal"
                data-reveal="pop"
                data-sync-target="dresscode_colors"
            >
                @foreach($colorHexList as $idx => $hex)
                    <div class="mayhen_invitation_dc_item" style="--r: {{ $swatchRotations[$idx % count($swatchRotations)] }};">
                        <svg class="mayhen_invitation_dc_bow" viewBox="0 0 36 22" aria-hidden="true">
                            <path d="M18 11C13 4 4 3 3 9c-1 5 7 7 15 2z"/>
                            <path d="M18 11c5-7 14-8 15-2 1 5-7 7-15 2z"/>
                            <path d="M17 12l-6 9 4-1 3-7zM19 12l6 9-4-1-3-7z"/>
                            <circle class="knot" cx="18" cy="11" r="2.8"/>
                        </svg>
                        <div class="mayhen_invitation_dc_card">
                            <div class="mayhen_invitation_dc_fabric" style="--c: {{ $hex }};"></div>
                            <span class="mayhen_invitation_dc_label">{{ $defaultColorNames[$idx % count($defaultColorNames)] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- ==================== 9. GIFT & VIETQR ("Share THE Love" — @if(!$isCuratedSeven)) ==================== --}}
        @if(!$isCuratedSeven)
        <div class="mayhen_invitation_divider" aria-hidden="true"></div>
        <section
            id="mayhen_section_gift"
            data-mayhen-section="gift"
            class="mayhen_invitation_section_block mayhen_invitation_gift"
        >
            <div class="mayhen_invitation_section_bg"></div>
            <div id="w21GiftFrame" class="mayhen_invitation_gift_frame {{ $autoOpened ? 'is-opened' : '' }} mayhen_invitation_reveal" data-reveal="pop">
                <div id="w21Qua" class="mayhen_invitation_gift_box" role="button" tabindex="0" aria-label="Mở hộp quà mừng cưới">
                    <i class="mayhen_invitation_gift_glow mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_gift_wing mayhen_invitation_gift_wing--left mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_gift_wing mayhen_invitation_gift_wing--right mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_gift_ripple mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_gift_ripple mayhen_invitation_gift_ripple--2 mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_gift_ripple mayhen_invitation_gift_ripple--3 mayhen_invitation_tt" aria-hidden="true"></i>
                    <div class="mayhen_invitation_gift_label">
                        <span class="mayhen_invitation_gift_sh">Share</span>
                        <span class="mayhen_invitation_gift_the">THE</span>
                        <span class="mayhen_invitation_gift_sh">Love</span>
                        <span class="mayhen_invitation_gift_tap">Ấn vào đây</span>
                        <span class="mayhen_invitation_gift_sub">Để gửi quà mừng cưới tới<br>Cô Dâu, Chú Rể</span>
                    </div>
                </div>

                <div class="mayhen_invitation_gift_qr_area">
                    <div class="mayhen_invitation_qr_grid">
                        <div class="mayhen_invitation_qr_card">
                            <p class="mayhen_invitation_qr_role">Mừng cưới chú rể</p>
                            <div class="mayhen_invitation_qr_img_wrap" data-lightbox-src="{{ $qrGroomUrl }}" data-lightbox-qr="1">
                                <img src="{{ $qrGroomUrl }}" alt="VietQR Chú rể {{ $groomName }}" data-sync-target="qr_groom_image" loading="lazy">
                            </div>
                            <p class="mayhen_invitation_qr_name" data-sync-target="groom_name">{{ $groomName }}</p>
                            <p class="mayhen_invitation_qr_info">
                                CTK: <span data-sync-target="groom_account_name">{{ $groomAccountName }}</span><br>
                                STK: <span data-sync-target="groom_bank_account">{{ $groomBankAccount }}</span><br>
                                NH: <span data-sync-target="groom_bank_name">{{ $groomBankName }}</span>
                            </p>
                            <button type="button" class="mayhen_invitation_qr_copy_btn" data-copy-account="{{ $groomBankAccount }}">
                                Sao chép STK
                            </button>
                        </div>

                        <div class="mayhen_invitation_qr_card">
                            <p class="mayhen_invitation_qr_role">Mừng cưới cô dâu</p>
                            <div class="mayhen_invitation_qr_img_wrap" data-lightbox-src="{{ $qrBrideUrl }}" data-lightbox-qr="1">
                                <img src="{{ $qrBrideUrl }}" alt="VietQR Cô dâu {{ $brideName }}" data-sync-target="qr_bride_image" loading="lazy">
                            </div>
                            <p class="mayhen_invitation_qr_name" data-sync-target="bride_name">{{ $brideName }}</p>
                            <p class="mayhen_invitation_qr_info">
                                CTK: <span data-sync-target="bride_account_name">{{ $brideAccountName }}</span><br>
                                STK: <span data-sync-target="bride_bank_account">{{ $brideBankAccount }}</span><br>
                                NH: <span data-sync-target="bride_bank_name">{{ $brideBankName }}</span>
                            </p>
                            <button type="button" class="mayhen_invitation_qr_copy_btn" data-copy-account="{{ $brideBankAccount }}">
                                Sao chép STK
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- ==================== 10. ENDING: SIGNATURE (LAW #8) + RSVP (LAW #8) + WISHES + THANK YOU ==================== --}}
        <div id="mayhen_section_ending" data-mayhen-section="ending" class="mayhen_invitation_ending">

            {{-- 10A. SỔ KÝ TÊN ĐIỆN TỬ CANVAS (LAW #8: 0 ẢNH NGƯỜI) --}}
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_sig_head">
                    <i class="mayhen_invitation_sig_sprig mayhen_invitation_sig_sprig--left mayhen_invitation_tt" aria-hidden="true"></i>
                    <div>
                        <p class="mayhen_invitation_sig_en">Signature</p>
                        <h2 class="mayhen_invitation_sig_vi">Ký Tên</h2>
                    </div>
                    <i class="mayhen_invitation_sig_sprig mayhen_invitation_sig_sprig--right mayhen_invitation_tt" aria-hidden="true"></i>
                </div>
                <p class="mayhen_invitation_sig_desc">Để lại chữ ký của bạn và cùng chúng mình lưu giữ kỷ niệm hạnh phúc này nhé!</p>

                <div class="mayhen_invitation_sig_board mayhen_invitation_reveal" data-reveal="pop">
                    <div class="mayhen_invitation_sig_crest" aria-hidden="true">
                        <svg class="mayhen_invitation_sig_crest_svg" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="50" cy="50" r="44"/>
                            <circle cx="50" cy="50" r="35" stroke-dasharray="3 3"/>
                            <circle cx="50" cy="50" r="22"/>
                            <circle cx="50" cy="50" r="5" fill="currentColor"/>
                            <path d="M50 18v8M50 74v8M18 50h8M74 50h8" stroke-linecap="round"/>
                        </svg>
                        <div class="mayhen_invitation_sig_couple">
                            <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                        </div>
                    </div>

                    <div class="mayhen_invitation_sig_field">
                        <label for="mayhenSigGuestName">Tên của bạn</label>
                        <input
                            type="text"
                            id="mayhenSigGuestName"
                            class="mayhen_invitation_sig_input"
                            placeholder="Nhập tên của bạn..."
                            value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}"
                            maxlength="80"
                        >
                    </div>

                    <div class="mayhen_invitation_sig_canvas_wrap">
                        <canvas id="mayhenSigCanvas" class="mayhen_invitation_sig_canvas"></canvas>
                        <div id="mayhenSigHint" class="mayhen_invitation_sig_hint">✎ Ký tên lưu bút tại đây</div>
                    </div>

                    <div class="mayhen_invitation_sig_actions">
                        <button type="button" id="mayhenSigClear" class="mayhen_invitation_btn_pill mayhen_invitation_btn_pill--outline">Xóa chữ ký</button>
                        <button type="button" id="mayhenSigPreviewBtn" class="mayhen_invitation_btn_pill mayhen_invitation_btn_pill--outline">Xem trước</button>
                        <button type="button" id="mayhenSigSave" class="mayhen_invitation_btn_pill">Lưu chữ ký</button>
                    </div>

                    <div id="mayhenSigPreviewBox" class="mayhen_invitation_sig_preview">
                        <img id="mayhenSigPreviewImg" alt="Xem trước chữ ký">
                        <a id="mayhenSigDownloadLink" class="mayhen_invitation_btn_pill mayhen_invitation_btn_pill--outline" download="chu-ky-may-hen.png">Tải ảnh PNG</a>
                    </div>
                    <div id="mayhenSigStatus" class="mayhen_invitation_sig_status" role="status" aria-live="polite"></div>
                </div>
            </section>

            <div class="mayhen_invitation_divider" aria-hidden="true"></div>

            {{-- 10B. XÁC NHẬN THAM DỰ RSVP (LAW #8: 0 ẢNH NGƯỜI — DÙNG HUY HIỆU ĐĨA NHẠC & MONOGRAM) --}}
            <section class="mayhen_invitation_section_block mayhen_invitation_rsvp_section">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_rsvp_box mayhen_invitation_reveal" data-reveal="pop">
                    <h2 class="mayhen_invitation_rsvp_title mayhen_invitation_viet mayhen_invitation_gr">Xác Nhận</h2>
                    <p class="mayhen_invitation_rsvp_desc">Hãy xác nhận sự hiện diện của Quý Khách để gia đình chúng tôi chuẩn bị chu đáo hơn.</p>

                    <div class="mayhen_invitation_rsvp_grid">
                        <div class="mayhen_invitation_rsvp_emblem_card" aria-hidden="true">
                            <svg class="mayhen_invitation_rsvp_emblem_svg" viewBox="0 0 120 120" fill="none" stroke="currentColor" stroke-width="1.6">
                                <circle cx="60" cy="60" r="52"/>
                                <circle cx="60" cy="60" r="42" stroke-dasharray="2.5 3.5"/>
                                <circle cx="60" cy="60" r="28"/>
                                <path d="M60 44c4-6 13-5 13 2 0 7-13 15-13 15S47 53 47 46c0-7 9-8 13-2z" fill="currentColor" fill-opacity="0.25"/>
                            </svg>
                            <div class="mayhen_invitation_rsvp_monogram" data-mayhen-wax-monogram>
                                <span data-monogram-role="groom">{{ $groomInitial }}</span> &amp; <span data-monogram-role="bride">{{ $brideInitial }}</span>
                            </div>
                            <div class="mayhen_invitation_rsvp_emblem_note">R · S · V · P</div>
                        </div>

                        <form id="mayhenRsvpForm" class="mayhen_invitation_rsvp_form">
                            @if(!empty($isRsvpClosed))
                                <p class="mayhen_invitation_form_feedback">{{ $rsvpClosedNotice ?? 'Đã hết thời gian xác nhận tham dự.' }}</p>
                            @endif
                            <div class="mayhen_invitation_form_group">
                                <label class="mayhen_invitation_form_label" for="mayhenRsvpName">Họ và tên Quý Khách *</label>
                                <input
                                    type="text"
                                    id="mayhenRsvpName"
                                    name="guest_name"
                                    class="mayhen_invitation_rsvp_input"
                                    placeholder="Nhập họ tên của bạn..."
                                    value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}"
                                    required
                                    maxlength="100"
                                >
                            </div>
                            <div class="mayhen_invitation_form_group">
                                <label class="mayhen_invitation_form_label" for="mayhenRsvpStatus">Trạng thái tham dự</label>
                                <select id="mayhenRsvpStatus" name="attending_status" class="mayhen_invitation_rsvp_select">
                                    <option value="attending">Sẽ đến chung vui</option>
                                    <option value="declined">Rất tiếc không thể tham dự</option>
                                    <option value="uncertain">Chưa chắc chắn</option>
                                </select>
                            </div>
                            <div class="mayhen_invitation_form_row">
                                <div class="mayhen_invitation_form_group">
                                    <label class="mayhen_invitation_form_label" for="mayhenRsvpAdults">Người lớn</label>
                                    <select id="mayhenRsvpAdults" name="adults_count" class="mayhen_invitation_rsvp_select">
                                        @for($n = 1; $n <= 6; $n++)
                                            <option value="{{ $n }}">{{ $n }} người</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="mayhen_invitation_form_group">
                                    <label class="mayhen_invitation_form_label" for="mayhenRsvpChildren">Trẻ em</label>
                                    <select id="mayhenRsvpChildren" name="children_count" class="mayhen_invitation_rsvp_select">
                                        @for($n = 0; $n <= 4; $n++)
                                            <option value="{{ $n }}">{{ $n }} bé</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="mayhen_invitation_form_group">
                                <label class="mayhen_invitation_form_label" for="mayhenRsvpWishes">Lời nhắn gửi gia đình</label>
                                <textarea
                                    id="mayhenRsvpWishes"
                                    name="wishes"
                                    rows="2"
                                    class="mayhen_invitation_rsvp_textarea"
                                    placeholder="Ghi chú ăn chay hoặc lời chúc..."
                                    maxlength="1000"
                                ></textarea>
                            </div>
                            <button
                                type="submit"
                                id="mayhenRsvpSubmit"
                                class="mayhen_invitation_rsvp_submit"
                                @if(!empty($isRsvpClosed)) disabled @endif
                            >
                                Gửi Xác Nhận Tham Dự
                            </button>
                            <div id="mayhenRsvpFeedback" class="mayhen_invitation_form_feedback" role="status" aria-live="polite"></div>
                        </form>
                    </div>
                </div>
            </section>

            <div class="mayhen_invitation_divider" aria-hidden="true"></div>

            {{-- 10C. SỔ LƯU BÚT LỜI CHÚC (GỬI LỜI NHẮN / YÊU THƯƠNG) --}}
            <section class="mayhen_invitation_section_block mayhen_invitation_wishes_section">
                <div class="mayhen_invitation_section_bg"></div>
                <p class="mayhen_invitation_wishes_kicker">Gửi Lời Nhắn</p>
                <h2 class="mayhen_invitation_wishes_title mayhen_invitation_viet mayhen_invitation_gr">Yêu Thương</h2>

                <div class="mayhen_invitation_wishes_box mayhen_invitation_reveal" data-reveal="up">
                    <i class="mayhen_invitation_wishes_flower mayhen_invitation_tt" aria-hidden="true"></i>
                    <i class="mayhen_invitation_wishes_leaf mayhen_invitation_tt" aria-hidden="true"></i>
                    <div id="mayhenWishesList" class="mayhen_invitation_wishes_list">
                        @forelse($approvedWishes as $wish)
                            <div class="mayhen_invitation_wish_item">
                                <div class="mayhen_invitation_wish_author">{{ $wish->name }}</div>
                                <p class="mayhen_invitation_wish_text">{{ $wish->message }}</p>
                                @if(!empty($wish->reply))
                                    <div class="mayhen_invitation_wish_reply">
                                        <strong>Cô dâu &amp; Chú rể:</strong> {{ $wish->reply }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="mayhen_invitation_wish_item">
                                <div class="mayhen_invitation_wish_author">Mây Hẹn &amp; Bằng Hữu</div>
                                <p class="mayhen_invitation_wish_text">Chúc hai bạn {{ $groomName }} &amp; {{ $brideName }} mãi viết tiếp bản nhạc hạnh phúc không đoạn kết, trăm năm tình viên mãn!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <form id="mayhenWishForm" class="mayhen_invitation_wish_form">
                    <input
                        type="text"
                        id="mayhenWishName"
                        name="name"
                        class="mayhen_invitation_wish_input"
                        placeholder="Tên của bạn *"
                        value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}"
                        required
                        maxlength="100"
                    >
                    <textarea
                        id="mayhenWishMessage"
                        name="message"
                        rows="3"
                        class="mayhen_invitation_wish_textarea"
                        placeholder="Viết lời chúc yêu thương gửi tới cô dâu, chú rể *"
                        required
                        maxlength="1000"
                    ></textarea>
                    <button type="submit" id="mayhenWishSubmit" class="mayhen_invitation_wish_submit">Gửi Lời Chúc Phúc</button>
                    <div id="mayhenWishFeedback" class="mayhen_invitation_form_feedback" role="status" aria-live="polite"></div>
                </form>
            </section>

            {{-- 10D. LỜI CẢM ƠN KẾT THIỆP (@if(!$isCuratedSeven)) --}}
            @if(!$isCuratedSeven)
            <div class="mayhen_invitation_divider" aria-hidden="true"></div>
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_section_bg"></div>
                <div class="mayhen_invitation_ending_arch mayhen_invitation_reveal" data-reveal="up">
                    <div class="mayhen_invitation_ending_arch_inner">
                        <div class="mayhen_invitation_ending_photo mayhen_invitation_photo_frame" data-lightbox-src="{{ $footerPhotoUrl }}">
                            <img src="{{ $footerPhotoUrl }}" alt="Cảm ơn từ {{ $groomName }} và {{ $brideName }}" data-sync-target="footer_photo_url" loading="lazy">
                        </div>
                    </div>
                    <h2 class="mayhen_invitation_ending_title mayhen_invitation_viet mayhen_invitation_gr" data-sync-target="thank_you_title">{{ $thankYouTitle }}</h2>
                    <p class="mayhen_invitation_ending_quote" data-sync-target="thank_you_quote">{{ $thankYouQuote }}</p>
                    <p class="mayhen_invitation_ending_couple" data-sync-target="couple_names">
                        <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </p>
                </div>
            </section>
            @endif

            @if($isCuratedSeven)
            <div class="mayhen_invitation_curated_closing">
                <p data-sync-target="thank_you_title">{{ $thankYouTitle }}</p>
                <p data-sync-target="thank_you_quote">{{ $thankYouQuote }}</p>
            </div>
            @endif
        </div>
    </main>

    {{-- Toast Notification --}}
    <div id="mayhenToast" class="mayhen_invitation_toast" role="status" aria-live="polite"></div>
</div>
@endsection

@section('modals')
<div id="mayhenLightbox" class="mayhen_invitation_lightbox" role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to" aria-hidden="true">
    <div class="mayhen_invitation_lb_top">
        <span id="mayhenLbCounter">1 / 10</span>
        <button type="button" id="mayhenLbClose" class="mayhen_invitation_lb_close" aria-label="Đóng">×</button>
    </div>
    <div class="mayhen_invitation_lb_stage">
        <button type="button" id="mayhenLbPrev" class="mayhen_invitation_lb_nav mayhen_invitation_lb_nav--prev" aria-label="Ảnh trước">‹</button>
        <img id="mayhenLbImg" src="{{ $coverPhotoUrl }}" alt="Ảnh phóng to">
        <button type="button" id="mayhenLbNext" class="mayhen_invitation_lb_nav mayhen_invitation_lb_nav--next" aria-label="Ảnh tiếp theo">›</button>
    </div>
    <div id="mayhenLbCaption" class="mayhen_invitation_lb_caption"></div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ route('templates.asset', ['slug' => $slug, 'path' => 'script.js']) }}?v=1.0.0"></script>
@endpush
