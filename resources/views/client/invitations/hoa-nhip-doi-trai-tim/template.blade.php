@extends('client.layouts.invitation')

@php
    // =========================================================================
    // TEMPLATE: Hòa Nhịp Đôi Trái Tim (hoa-nhip-doi-trai-tim)
    // Source Lock: Golden Lake Ripples, Auspicious Clouds & Cranes
    // 10 Canonical Sections | BEM mayhen_invitation_* | 100% Local Assets
    // =========================================================================

    $slug = 'hoa-nhip-doi-trai-tim';
    $templateName = $template->name ?? 'Hòa Nhịp Đôi Trái Tim';

    $isEmbed = ($isEmbed ?? false)
        || request()->boolean('embed')
        || request()->boolean('in_modal')
        || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe'], true);

    $isPreview = request()->routeIs('templates.preview')
        || (!isset($wedding) && isset($template))
        || !isset($invitation)
        || empty($invitation->id);

    $invConfig = is_array($invitation->custom_config ?? null)
        ? $invitation->custom_config
        : (json_decode((string) ($invitation->custom_config ?? '{}'), true) ?? []);

    $isCuratedSeven = !empty($isCuratedSeven)
        || !empty($invConfig['is_curated_seven'])
        || !empty($invConfig['is_curated']);

    $autoOpened = $isEmbed || ($isCuratedSeven && request()->boolean('crafting'));

    $signatureEndpoint = (!$isPreview && isset($invitation) && $invitation->id)
        ? route('invitation.signature.save', $invitation->id)
        : '';

    // Helper $resolveAsset an toàn tuyệt đối (Local fallback, Storage/Uploads, VietQR data URI, Zero External CDN)
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

    // Chuẩn hóa $extraData (giữ nguyên mảng & khóa ảnh/URL)
    $extraData = is_array($wedding->extra_data ?? null)
        ? $wedding->extra_data
        : (json_decode((string) ($wedding->extra_data ?? '{}'), true) ?? []);

    foreach ($extraData as $k => $v) {
        if ($v === null
            && !in_array($k, ['gallery', 'timeline', 'dresscode_colors'], true)
            && !str_ends_with((string) $k, '_url')
            && !str_ends_with((string) $k, '_image')) {
            $extraData[$k] = '';
        }
    }

    // 1. Cô dâu & Chú rể + Khách mời
    $groomName    = trim((string) ($wedding->groom_name ?? ($extraData['groom_name'] ?? 'Phước Tú'))) ?: 'Phước Tú';
    $brideName    = trim((string) ($wedding->bride_name ?? ($extraData['bride_name'] ?? 'Tuyết Nhi'))) ?: 'Tuyết Nhi';
    $ceremonyType = trim((string) ($extraData['ceremony_type'] ?? 'Thiệp Mời Cưới')) ?: 'Thiệp Mời Cưới';
    $slogan       = trim((string) ($extraData['slogan'] ?? 'Như giọt nước rơi xuống mặt hồ, một ngày vui lan toả niềm hạnh phúc — hẹn gặp bạn trong ngày hai đứa về chung một nhà.'));

    $groomInitial = mb_strtoupper(mb_substr($groomName, 0, 1));
    $brideInitial = mb_strtoupper(mb_substr($brideName, 0, 1));

    $guestName = 'Bạn và Gia Đình';
    if (isset($guest) && !empty($guest->name)) {
        $salutation = trim((string) ($guest->salutation ?? ''));
        $guestName = trim($salutation !== '' ? ($salutation . ' ' . $guest->name) : $guest->name);
    } elseif (!empty($extraData['guest_name'])) {
        $guestName = (string) $extraData['guest_name'];
    }

    // 2. Ngày giờ cưới & Đếm ngược (Asia/Ho_Chi_Minh)
    $weddingDate = !empty($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date, 'Asia/Ho_Chi_Minh')
        : \Carbon\Carbon::create(2027, 11, 21, 11, 0, 0, 'Asia/Ho_Chi_Minh');

    $weddingDay        = $weddingDate->format('d');
    $weddingMonth      = $weddingDate->format('m');
    $weddingMonthInt   = (int) $weddingDate->format('n');
    $weddingYear       = $weddingDate->format('Y');
    $weddingDateDigits = $weddingDate->format('d . m . Y');
    $weddingDateDots   = $weddingDate->format('d · m · Y');

    $weekdayMap = [
        0 => 'Chủ Nhật',
        1 => 'Thứ Hai',
        2 => 'Thứ Ba',
        3 => 'Thứ Tư',
        4 => 'Thứ Năm',
        5 => 'Thứ Sáu',
        6 => 'Thứ Bảy',
    ];
    $weddingWeekday   = $weekdayMap[$weddingDate->dayOfWeek] ?? 'Chủ Nhật';
    $weddingLunarDate = trim((string) ($extraData['wedding_lunar_date'] ?? '(Tức ngày 24 tháng 10 năm Đinh Mùi)'));

    $partyTime     = trim((string) ($extraData['party_time'] ?? '11:00')) ?: '11:00';
    $countdownTime = trim((string) ($extraData['event_target_time'] ?? $partyTime)) ?: '11:00';
    $timeParts     = explode(':', $countdownTime . ':00');
    $cdHour        = str_pad((string) ((int) ($timeParts[0] ?? 11)), 2, '0', STR_PAD_LEFT);
    $cdMin         = str_pad((string) ((int) ($timeParts[1] ?? 0)), 2, '0', STR_PAD_LEFT);
    $targetIso     = sprintf('%s-%s-%sT%s:%s:00+07:00', $weddingYear, $weddingMonth, $weddingDay, $cdHour, $cdMin);

    $partyTimeParts = explode(':', $partyTime . ':00');
    $partyTimeText  = sprintf('%s giờ %s', str_pad((string) ((int) ($partyTimeParts[0] ?? 11)), 2, '0', STR_PAD_LEFT), str_pad((string) ((int) ($partyTimeParts[1] ?? 0)), 2, '0', STR_PAD_LEFT));

    // Lịch tháng server-side (Monday = 0 .. Sunday = 6)
    $firstOfMonth   = $weddingDate->copy()->startOfMonth();
    $daysInMonth    = $weddingDate->daysInMonth;
    $leadingBlanks  = ($firstOfMonth->dayOfWeek + 6) % 7;
    $selectedDayInt = (int) $weddingDate->format('j');

    // Link Google Calendar động từ ngày cưới & giờ tiệc
    $partyVenue        = trim((string) ($extraData['party_venue'] ?? ($wedding->venue_name ?? 'Nhà Hàng Phì Lũ (Sảnh Pearl)')));
    $eventPartyAddress = trim((string) ($extraData['event_party_address'] ?? ($wedding->venue_address ?? 'Số 1-2-3 Khu công viên Bắc Tượng Đài, Đường 2/9, Đà Nẵng')));
    $gcalStart         = \Carbon\Carbon::create((int) $weddingYear, $weddingMonthInt, $selectedDayInt, (int) $cdHour, (int) $cdMin, 0, 'Asia/Ho_Chi_Minh')->utc();
    $gcalEnd           = $gcalStart->copy()->addHours(4);
    $googleCalendarUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . rawurlencode("Đám cưới {$groomName} & {$brideName}")
        . '&dates=' . $gcalStart->format('Ymd\THis\Z') . '/' . $gcalEnd->format('Ymd\THis\Z')
        . '&location=' . rawurlencode(trim($partyVenue . ', ' . $eventPartyAddress, ', '));

    // 3. Sự kiện & Bản đồ
    $ceremonyTime         = trim((string) ($extraData['ceremony_time'] ?? '08:00'));
    $ceremonyAddress      = trim((string) ($extraData['ceremony_address'] ?? 'Tư gia Nhà Gái — Sơn Trà, Thành phố Đà Nẵng'));
    $ceremonyGroomTime    = trim((string) ($extraData['ceremony_groom_time'] ?? '09:30'));
    $ceremonyGroomAddress = trim((string) ($extraData['ceremony_groom_address'] ?? 'Tư gia Nhà Trai — Hải Châu, Thành phố Đà Nẵng'));

    $rawMapLink = trim((string) ($extraData['event_party_map_link'] ?? ''));
    $mapQuery   = rawurlencode(trim($partyVenue . ' ' . $eventPartyAddress));
    $mapLinkUrl = preg_match('#^https?://#i', $rawMapLink)
        ? $rawMapLink
        : "https://maps.google.com/?q={$mapQuery}";
    $mapEmbedUrl = "https://maps.google.com/maps?q={$mapQuery}&t=&z=15&ie=UTF8&iwloc=&output=embed";

    // 4. Song thân & Đôi uyên ương
    $groomFather      = trim((string) ($extraData['groom_father'] ?? 'Ông Nguyễn Phước Tấn'));
    $groomMother      = trim((string) ($extraData['groom_mother'] ?? 'Bà Bùi Thị Phương'));
    $groomParentsAddr = trim((string) ($extraData['groom_parents_address'] ?? 'Hải Châu, Thành phố Đà Nẵng'));
    $brideFather      = trim((string) ($extraData['bride_father'] ?? 'Ông Nguyễn Ngọc Toàn'));
    $brideMother      = trim((string) ($extraData['bride_mother'] ?? 'Bà Huỳnh Thị Vân'));
    $brideParentsAddr = trim((string) ($extraData['bride_parents_address'] ?? 'Sơn Trà, Thành phố Đà Nẵng'));
    $groomBio         = trim((string) ($extraData['groom_bio'] ?? 'Chàng trai ấm áp, chân thành và luôn trân trọng từng khoảnh khắc bình yên bên gia đình nhỏ.'));
    $brideBio         = trim((string) ($extraData['bride_bio'] ?? 'Cô gái dịu dàng, rạng rỡ nụ cười và là bến đỗ ngọt ngào của hành trình chung đôi.'));

    // 5. Chuyện tình yêu
    $storyTitle    = trim((string) ($extraData['story_title'] ?? 'Câu Chuyện Tình Yêu'));
    $storySubtitle = trim((string) ($extraData['story_subtitle'] ?? 'Ấn Tượng Đầu Tiên'));
    $coupleQuote   = trim((string) ($extraData['couple_quote'] ?? 'Gặp nhau tình cờ tại buổi tiệc bạn chung, tôi ấn tượng với em, một cô gái xinh đẹp, mạnh mẽ và tự nhiên. Khoảnh khắc đó, tôi biết… tim mình đã đập khác đi rồi.'));
    $storyContent  = trim((string) ($extraData['story_content'] ?? 'Hơn 15 năm trôi qua, chúng tôi đã cùng nhau trưởng thành, hiểu và thương nhau hơn bất cứ điều gì. Cảm ơn vì đã cùng nhau đi qua những năm tháng thanh xuân rực rỡ nhất.'));

    if (!empty($personaMessage) && is_array($personaMessage)) {
        $storyTitle    = $personaMessage['title'] ?? $storyTitle;
        $storySubtitle = $personaMessage['subtitle'] ?? $storySubtitle;
        $storyContent  = $personaMessage['content'] ?? $storyContent;
    }

    // 6. Lịch trình (Timeline)
    $timelineDefault = [
        ['time' => '10:30', 'title' => 'Đón khách · Chụp ảnh lưu niệm'],
        ['time' => '11:00', 'title' => 'Khai tiệc'],
        ['time' => '11:30', 'title' => 'Làm lễ · Trao nhẫn'],
        ['time' => '12:00', 'title' => 'Cắt bánh · Rót rượu'],
    ];
    $timelineItems = is_array($extraData['timeline'] ?? null) && count($extraData['timeline']) > 0
        ? array_slice(array_values($extraData['timeline']), 0, 6)
        : $timelineDefault;

    // 7. Dresscode
    $dresscodeColorsRaw = trim((string) ($extraData['dresscode_colors'] ?? '#F7DAF8,#DDC5BC,#A5745E,#740808,#0D1210'));
    $dresscodeNote      = trim((string) ($extraData['dresscode_note'] ?? 'Chúng tôi rất mong quý khách lựa chọn trang phục theo các tông màu dưới đây để cùng hòa vào không khí buổi tiệc.'));
    $dresscodePalette   = array_values(array_filter(array_map('trim', explode(',', $dresscodeColorsRaw)), fn ($c) => preg_match('/^#[0-9A-Fa-f]{3,6}$/', $c)));
    if (empty($dresscodePalette)) {
        $dresscodePalette = ['#F7DAF8', '#DDC5BC', '#A5745E', '#740808', '#0D1210'];
    }
    $colorNameMap = [
        '#f7daf8' => 'Hồng',
        '#ddc5bc' => 'Be',
        '#a5745e' => 'Nâu',
        '#740808' => 'Đỏ',
        '#0d1210' => 'Đen',
        '#fce39e' => 'Vàng Kem',
        '#ffffff' => 'Trắng',
        '#c89a47' => 'Vàng Kim',
        '#aa7e2c' => 'Đồng Ánh',
        '#f3ebdc' => 'Kem Sữa',
    ];

    // 8. Album 10 ảnh & Ảnh đại diện
    $defaultGalleryFiles = [
        'images/gallery-01.webp',
        'images/gallery-02.webp',
        'images/gallery-03.webp',
        'images/gallery-04.webp',
        'images/gallery-05.webp',
        'images/gallery-06.webp',
        'images/gallery-07.webp',
        'images/gallery-08.webp',
        'images/gallery-09.webp',
        'images/gallery-10.webp',
    ];
    $rawGallery = is_array($extraData['gallery'] ?? null) ? $extraData['gallery'] : [];
    $galleryPhotos = [];
    for ($i = 0; $i < 10; $i++) {
        $galleryPhotos[$i] = $resolveAsset($defaultGalleryFiles[$i], $rawGallery[$i] ?? null);
    }

    $heroPhotoUrl     = $resolveAsset('images/hero-couple.webp', $wedding->cover_photo_url ?? ($extraData['cover_photo_url'] ?? ($extraData['hero_photo_url'] ?? null)));
    $openingPhotoUrl  = $resolveAsset('images/opening-couple.webp', $wedding->cover_photo_url ?? ($extraData['cover_photo_url'] ?? null));
    $saveDatePhotoUrl = $resolveAsset('images/save-date.webp', $extraData['save_date_photo_url'] ?? null);
    $groomPhotoUrl    = $resolveAsset('images/groom-portrait.webp', $extraData['groom_photo_url'] ?? null);
    $bridePhotoUrl    = $resolveAsset('images/bride-portrait.webp', $extraData['bride_photo_url'] ?? null);

    // 9. Hộp mừng cưới & VietQR động
    $groomBankName    = trim((string) ($extraData['groom_bank_name'] ?? 'Techcombank'));
    $groomBankAccount = trim((string) ($extraData['groom_bank_account'] ?? '19038889990011'));
    $groomAccountName = trim((string) ($extraData['groom_account_name'] ?? ($extraData['groom_bank_owner'] ?? 'NGUYEN PHUOC TU')));
    $groomQrRaw       = $extraData['qr_groom_image'] ?? ($extraData['groom_qr_url'] ?? null);
    $qrGroomUrl       = \App\Services\VietQrService::resolveQrImage(
        $groomQrRaw,
        $groomBankName,
        $groomBankAccount,
        'Mung cuoi ' . $groomName,
        $resolveAsset('images/qr-groom.webp', $groomQrRaw),
        '19038889990011'
    );

    $brideBankName    = trim((string) ($extraData['bride_bank_name'] ?? 'Vietcombank'));
    $brideBankAccount = trim((string) ($extraData['bride_bank_account'] ?? '1029384756'));
    $brideAccountName = trim((string) ($extraData['bride_account_name'] ?? ($extraData['bride_bank_owner'] ?? 'NGUYEN TUYET NHI')));
    $brideQrRaw       = $extraData['qr_bride_image'] ?? ($extraData['bride_qr_url'] ?? null);
    $qrBrideUrl       = \App\Services\VietQrService::resolveQrImage(
        $brideQrRaw,
        $brideBankName,
        $brideBankAccount,
        'Mung cuoi ' . $brideName,
        $resolveAsset('images/qr-bride.webp', $brideQrRaw),
        '1029384756'
    );

    // 10. Kết thiệp & Nhạc nền
    $thankYouTitle = trim((string) ($extraData['thank_you_title'] ?? 'Thank you!'));
    $thankYouQuote = trim((string) ($extraData['thank_you_quote'] ?? 'Sự hiện diện của bạn là niềm vinh hạnh cho gia đình chúng tôi. Chân thành cảm ơn!'));
    $musicUrl      = $resolveAsset('wedding-song.mp3', $wedding->music_url ?? ($extraData['music_url'] ?? null));

    $rootStateClasses = trim(($autoOpened ? 'mayhen_invitation_root--opened ' : '') . ($isEmbed ? 'mayhen_invitation_root--embed' : ''));
@endphp

@section('title', "Thiệp Cưới {$groomName} & {$brideName} — {$templateName}")
@section('body_class', 'mayhen_invitation_golden_lake_body' . (!$autoOpened ? ' mayhen_invitation_scroll_locked' : ''))

@push('styles')
    <link rel="stylesheet" href="{{ route('templates.asset', ['slug' => $slug, 'path' => 'styles.css']) }}?v=1.0.0">
@endpush

@section('content')
<div
    class="mayhen_invitation_root {{ $rootStateClasses }}"
    data-slug="{{ $slug }}"
    data-is-embed="{{ $isEmbed ? 'true' : 'false' }}"
    data-is-preview="{{ $isPreview ? 'true' : 'false' }}"
    data-auto-opened="{{ $autoOpened ? 'true' : 'false' }}"
    data-wedding-date="{{ $weddingDate->format('Y-m-d') }}"
    data-target-time="{{ $countdownTime }}"
    data-groom-name="{{ $groomName }}"
    data-bride-name="{{ $brideName }}"
    data-party-venue="{{ $partyVenue }}"
    data-party-address="{{ $eventPartyAddress }}"
    data-rsvp-endpoint="{{ $rsvpSubmitUrl ?? '' }}"
    data-wishes-endpoint="{{ $wishSubmitUrl ?? '' }}"
    data-signature-endpoint="{{ $signatureEndpoint }}"
    data-csrf-token="{{ csrf_token() }}"
    data-guest-name="{{ $guestName !== 'Bạn và Gia Đình' ? $guestName : '' }}"
>
    {{-- SVG Gradients dùng chung cho Trái Tim Đếm Ngược --}}
    <svg class="mayhen_invitation_decor" width="0" height="0" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="mayhenGradHeart" gradientTransform="rotate(60)">
                <stop offset="0%" stop-color="#AA7E2C"></stop>
                <stop offset="100%" stop-color="#FFAB10"></stop>
            </linearGradient>
        </defs>
    </svg>

    {{-- Thẻ Audio nội bộ (Nhường music-dock.blade.php tự ẩn, tránh phát chồng 2 bài) --}}
    <audio
        preload="none"
        loop
        playsinline
        class="mayhen_invitation_audio"
        data-mayhen-audio
        data-sync-target="music_url"
        @if($musicUrl) src="{{ $musicUrl }}" @endif
    ></audio>

    {{-- Nút nhạc nổi vàng kim --}}
    <div class="mayhen_invitation_music_dock">
        <button
            type="button"
            class="mayhen_invitation_music_btn"
            data-mayhen-music-toggle
            aria-label="Bật hoặc tắt nhạc nền"
            aria-pressed="false"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"></path>
            </svg>
        </button>
    </div>

    {{-- ===================== MÀN MỞ THIỆP: MẶT HỒ GỢN SÓNG, MÂY CÁT TƯỜNG & HẠC ===================== --}}
    @if(!$autoOpened)
    <div
        class="mayhen_invitation_opening"
        data-mayhen-section="opening"
        role="dialog"
        aria-modal="true"
        aria-label="Mở thiệp cưới {{ $groomName }} và {{ $brideName }}"
    >
        <i class="mayhen_invitation_opening_lake mayhen_invitation_decor" aria-hidden="true"></i>
        <div class="mayhen_invitation_opening_scene mayhen_invitation_decor" aria-hidden="true">
            <i class="mayhen_invitation_opening_cloud mayhen_invitation_opening_cloud--top"><b></b></i>
            <i class="mayhen_invitation_opening_cloud mayhen_invitation_opening_cloud--moon"><b></b></i>
            <i class="mayhen_invitation_opening_cloud mayhen_invitation_opening_cloud--small"><b></b></i>
            <i class="mayhen_invitation_opening_cloud mayhen_invitation_opening_cloud--bottom"><b></b></i>
            <i class="mayhen_invitation_opening_crane mayhen_invitation_opening_crane--1"><b></b></i>
            <i class="mayhen_invitation_opening_crane mayhen_invitation_opening_crane--2"><b></b></i>
            <i class="mayhen_invitation_opening_crane mayhen_invitation_opening_crane--3"><b></b></i>
        </div>
        <i class="mayhen_invitation_opening_glow mayhen_invitation_decor" aria-hidden="true"></i>
        <div class="mayhen_invitation_opening_ripples mayhen_invitation_decor" aria-hidden="true">
            <i style="--mh-delay: 0s;"></i>
            <i style="--mh-delay: 1.5s;"></i>
            <i style="--mh-delay: 3s;"></i>
        </div>

        <div class="mayhen_invitation_opening_inner">
            <p class="mayhen_invitation_opening_label" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>
            <div class="mayhen_invitation_opening_portrait">
                <i class="mayhen_invitation_opening_portrait_border mayhen_invitation_decor" aria-hidden="true"></i>
                <div class="mayhen_invitation_opening_portrait_frame">
                    <img src="{{ $openingPhotoUrl }}" alt="Ảnh cưới {{ $groomName }} & {{ $brideName }}" data-sync-target="cover_photo_url">
                </div>
            </div>
            <div class="mayhen_invitation_opening_names" data-sync-target="couple_names">
                <span data-sync-target="groom_name">{{ $groomName }}</span>
                <span class="mayhen_invitation_opening_rings mayhen_invitation_decor" aria-hidden="true"></span>
                <span data-sync-target="bride_name">{{ $brideName }}</span>
            </div>
            <p class="mayhen_invitation_opening_date" data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</p>
            <p class="mayhen_invitation_opening_invite">Trân trọng kính mời</p>
            <span class="mayhen_invitation_opening_guest">{{ $guestName }}</span>
            <button type="button" class="mayhen_invitation_open_btn" data-mayhen-open-trigger>
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 1.8C8.2 6.6 4.8 10.6 4.8 14.6a7.2 7.2 0 0 0 14.4 0c0-4-3.4-8-7.2-12.8z"></path>
                </svg>
                <span>Mở thiệp</span>
                <i class="mayhen_invitation_open_btn_hand mayhen_invitation_decor" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 11.2V4.6a1.6 1.6 0 0 1 3.2 0v5.2l.1-1.2a1.5 1.5 0 0 1 3 .2v1.1a1.5 1.5 0 0 1 3 .2v1.2a1.4 1.4 0 0 1 2.8.3v4.6c0 3.6-2.7 6.3-6.2 6.3h-1.5c-2.2 0-3.6-.9-4.8-2.6l-3.3-4.8a1.5 1.5 0 0 1 2.4-1.8L9 15z"></path>
                    </svg>
                </i>
            </button>
        </div>
    </div>
    @endif

    {{-- ===================== NỘI DUNG THIỆP CHÍNH (10 SECTION CHUẨN MÂY HẸN) ===================== --}}
    <main class="mayhen_invitation_ladi_wraper mayhen_invitation_card_wrapper" id="mayhen_invitation_main_content">

        {{-- SECTION 1: HERO COVER --}}
        <section class="mayhen_invitation_hero" data-mayhen-section="hero,couple">
            <div class="mayhen_invitation_hero_top">
                <div class="mayhen_invitation_hero_names">
                    <p class="mayhen_invitation_ladi_headline_ceremony_type" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>
                    <h1 class="mayhen_invitation_ladi_headline_name_groom" data-sync-target="groom_name">{{ $groomName }}</h1>
                    <i class="mayhen_invitation_hero_rings mayhen_invitation_decor" aria-hidden="true"></i>
                    <h1 class="mayhen_invitation_ladi_headline_name_bride" data-sync-target="bride_name">{{ $brideName }}</h1>
                </div>

                <div class="mayhen_invitation_hero_media">
                    <div class="mayhen_invitation_hero_photo_back mayhen_invitation_decor" aria-hidden="true">
                        <img src="{{ $heroPhotoUrl }}" alt="" data-sync-target="hero_photo_url">
                    </div>
                    <div class="mayhen_invitation_hero_photo_front">
                        <img
                            class="mayhen_invitation_ladi_image"
                            src="{{ $heroPhotoUrl }}"
                            alt="Ảnh bìa {{ $groomName }} & {{ $brideName }}"
                            data-sync-target="cover_photo_url"
                        >
                    </div>
                    <i class="mayhen_invitation_hero_flower mayhen_invitation_decor" aria-hidden="true"></i>
                </div>
            </div>

            <div class="mayhen_invitation_hero_clouds mayhen_invitation_decor" aria-hidden="true"></div>

            <div class="mayhen_invitation_hero_date_block">
                <p class="mayhen_invitation_hero_std">Save the date</p>
                <p class="mayhen_invitation_ladi_headline_wedding_day" data-sync-target="wedding_date">{{ $weddingDateDots }}</p>
                <p class="mayhen_invitation_ladi_paragraph_slogan" data-sync-target="slogan">{{ $slogan }}</p>
            </div>
        </section>

        {{-- SECTION 5 (Vị trí 2 theo bản gốc): THƯ MỜI & THÔNG TIN SỰ KIỆN --}}
        <section class="mayhen_invitation_section mayhen_invitation_events" data-mayhen-section="events">
            <div class="mayhen_invitation_events_grid">
                <div class="mayhen_invitation_party_photo mayhen_invitation_reveal">
                    <img src="{{ $saveDatePhotoUrl }}" alt="Ảnh thư mời {{ $groomName }} & {{ $brideName }}" data-sync-target="save_date_photo_url">
                </div>

                <div class="mayhen_invitation_event_card_party mayhen_invitation_reveal">
                    <h2 class="mayhen_invitation_invite_heading">Trân trọng kính mời</h2>
                    <div class="mayhen_invitation_guest_pill_wrap">
                        <h3 class="mayhen_invitation_guest_name">{{ $guestName }}</h3>
                    </div>
                    <p class="mayhen_invitation_invite_sub">Đến dự buổi tiệc chung vui cùng<br>gia đình chúng tôi</p>

                    <div class="mayhen_invitation_invite_couple" data-sync-target="couple_names">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <i class="mayhen_invitation_gold_heart mayhen_invitation_decor" aria-hidden="true"></i>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </div>

                    <p class="mayhen_invitation_invite_month">Tháng <span data-sync-target="wedding_month">{{ $weddingMonth }}</span></p>
                    <div class="mayhen_invitation_ribbon_row">
                        <div class="mayhen_invitation_ribbon">
                            <svg viewBox="0 0 346.71 72.06" preserveAspectRatio="none" aria-hidden="true">
                                <polygon points="346.71 72.06 0 72.06 15.85 36.45 0 0 346.71 0 330.87 36.45 346.71 72.06"></polygon>
                            </svg>
                            <span data-sync-target="wedding_weekday">{{ $weddingWeekday }}</span>
                        </div>
                        <div class="mayhen_invitation_ribbon_day" data-sync-target="wedding_day">{{ $weddingDay }}</div>
                        <div class="mayhen_invitation_ribbon">
                            <svg viewBox="0 0 346.71 72.06" preserveAspectRatio="none" aria-hidden="true">
                                <polygon points="346.71 72.06 0 72.06 15.85 36.45 0 0 346.71 0 330.87 36.45 346.71 72.06"></polygon>
                            </svg>
                            <span data-sync-target="wedding_year">{{ $weddingYear }}</span>
                        </div>
                    </div>

                    <p class="mayhen_invitation_ladi_paragraph_lunar_calendar" data-sync-target="wedding_lunar_date">{{ $weddingLunarDate }}</p>
                    <p class="mayhen_invitation_party_time_line">Vào lúc <span data-sync-target="party_time">{{ $partyTime }}</span> ({{ $partyTimeText }})</p>
                    <p class="mayhen_invitation_party_time_line">Tại</p>

                    <div class="mayhen_invitation_venue_wrap">
                        <i class="mayhen_invitation_venue_wave mayhen_invitation_decor" aria-hidden="true"></i>
                        <p class="mayhen_invitation_party_venue" data-sync-target="party_venue">{{ $partyVenue }}</p>
                        <i class="mayhen_invitation_venue_wave mayhen_invitation_venue_wave--right mayhen_invitation_decor" aria-hidden="true"></i>
                    </div>

                    <p class="mayhen_invitation_party_address" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>

                    <a
                        href="{{ $mapLinkUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mayhen_invitation_map_link_btn"
                        data-sync-target="event_party_map_link"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 1.6a7.6 7.6 0 0 0-7.6 7.6c0 5.6 7.6 13.2 7.6 13.2s7.6-7.6 7.6-13.2A7.6 7.6 0 0 0 12 1.6zm0 10.4a2.8 2.8 0 1 1 0-5.6 2.8 2.8 0 0 1 0 5.6z"></path>
                        </svg>
                        <span>Xem vị trí</span>
                    </a>

                    <i class="mayhen_invitation_flourish mayhen_invitation_decor" aria-hidden="true"></i>
                </div>
            </div>

            {{-- Nghi lễ gia tiên (Vu Quy & Thành Hôn) --}}
            <div class="mayhen_invitation_ceremonies_list">
                @if(!isset($showBrideCeremony) || $showBrideCeremony)
                <div class="mayhen_invitation_ceremony_card mayhen_invitation_ceremony_bride_card mayhen_invitation_reveal">
                    <div class="mayhen_invitation_ceremony_badge" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 21s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.6-7 10-7 10z"></path>
                        </svg>
                    </div>
                    <div class="mayhen_invitation_ceremony_info">
                        <h3 class="mayhen_invitation_ceremony_name">Lễ Vu Quy (Nhà Gái)</h3>
                        <p class="mayhen_invitation_ceremony_time">Giờ cử hành: <span data-sync-target="ceremony_time">{{ $ceremonyTime }}</span></p>
                        <p class="mayhen_invitation_ceremony_addr" data-sync-target="ceremony_address">{{ $ceremonyAddress }}</p>
                    </div>
                </div>
                @endif

                @if(!isset($showGroomCeremony) || $showGroomCeremony)
                <div class="mayhen_invitation_ceremony_card mayhen_invitation_ceremony_groom_card mayhen_invitation_reveal">
                    <div class="mayhen_invitation_ceremony_badge" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="9" cy="13" r="5"></circle>
                            <circle cx="15" cy="13" r="5"></circle>
                            <path d="M12 4l1.5 2.5h-3L12 4z"></path>
                        </svg>
                    </div>
                    <div class="mayhen_invitation_ceremony_info">
                        <h3 class="mayhen_invitation_ceremony_name">Lễ Thành Hôn (Nhà Trai)</h3>
                        <p class="mayhen_invitation_ceremony_time">Giờ cử hành: <span data-sync-target="ceremony_groom_time">{{ $ceremonyGroomTime }}</span></p>
                        <p class="mayhen_invitation_ceremony_addr" data-sync-target="ceremony_groom_address">{{ $ceremonyGroomAddress }}</p>
                    </div>
                </div>
                @endif
            </div>
        </section>

        {{-- SECTION 4: LỊCH CƯỚI SAVE THE DATE & ĐẾM NGƯỢC TRÁI TIM VÀNG --}}
        <section class="mayhen_invitation_section mayhen_invitation_countdown_calendar" data-mayhen-section="countdown_calendar">
            <div class="mayhen_invitation_std_grid">
                <div class="mayhen_invitation_reveal">
                    <h2 class="mayhen_invitation_script_title">Save the date</h2>
                    <p class="mayhen_invitation_calendar_month_year" data-sync-target="calendar_month_year">Tháng {{ $weddingMonth }} · {{ $weddingYear }}</p>
                    <p class="mayhen_invitation_desc">Như giọt nước rơi xuống mặt hồ, một ngày vui lan toả niềm hạnh phúc — hẹn gặp bạn trong ngày hai đứa về chung một nhà.</p>
                </div>

                <div class="mayhen_invitation_calendar mayhen_invitation_reveal">
                    <i class="mayhen_invitation_calendar_crown mayhen_invitation_decor" aria-hidden="true"></i>
                    <div class="mayhen_invitation_calendar_weekdays" aria-hidden="true">
                        <div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div>T7</div><div>CN</div>
                    </div>
                    <div class="mayhen_invitation_calendar_body" data-sync-target="wedding_calendar">
                        @for($blank = 0; $blank < $leadingBlanks; $blank++)
                            <div class="mayhen_invitation_calendar_day mayhen_invitation_calendar_day--empty" aria-hidden="true"></div>
                        @endfor
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            <div class="mayhen_invitation_calendar_day {{ $day === $selectedDayInt ? 'mayhen_invitation_selected mayhen_invitation_calendar_selected_day' : '' }}">{{ $day }}</div>
                        @endfor
                    </div>
                    <a
                        href="{{ $googleCalendarUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mayhen_invitation_add_calendar_btn"
                        data-mayhen-gcal-link
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2zM5 9h14v11H5zm2 3h3v3H7z"></path>
                        </svg>
                        <span>Thêm vào lịch</span>
                    </a>
                </div>
            </div>

            <div class="mayhen_invitation_countdown_wrap mayhen_invitation_reveal">
                <h2 class="mayhen_invitation_script_title">Đếm ngược ngày vui</h2>
                <p class="mayhen_invitation_desc">Từng giây trôi qua là từng gợn sóng nhỏ đưa chúng mình về bên nhau.</p>

                <div class="mayhen_invitation_countdown" data-countdown="{{ $targetIso }}">
                    <div class="mayhen_invitation_cd_box">
                        <div class="mayhen_invitation_cd_heart" style="--mh-cd-delay: 0s;">
                            <svg viewBox="0 0 1792 1896.0833" aria-hidden="true"><path d="M896 1664q-26 0-44-18l-624-602q-10-8-27.5-26T145 952.5 77 855 23.5 734 0 596q0-220 127-344t351-124q62 0 126.5 21.5t120 58T820 276t76 68q36-36 76-68t95.5-68.5 120-58T1314 128q224 0 351 124t127 344q0 221-229 450l-623 600q-18 18-44 18z"></path></svg>
                            <span class="mayhen_invitation_cd_num mayhen_invitation_cd_day" data-sync-target="countdown_day">00</span>
                        </div>
                        <span class="mayhen_invitation_cd_label">Ngày</span>
                    </div>
                    <div class="mayhen_invitation_cd_box">
                        <div class="mayhen_invitation_cd_heart" style="--mh-cd-delay: 0.25s;">
                            <svg viewBox="0 0 1792 1896.0833" aria-hidden="true"><path d="M896 1664q-26 0-44-18l-624-602q-10-8-27.5-26T145 952.5 77 855 23.5 734 0 596q0-220 127-344t351-124q62 0 126.5 21.5t120 58T820 276t76 68q36-36 76-68t95.5-68.5 120-58T1314 128q224 0 351 124t127 344q0 221-229 450l-623 600q-18 18-44 18z"></path></svg>
                            <span class="mayhen_invitation_cd_num mayhen_invitation_cd_hour" data-sync-target="countdown_hour">00</span>
                        </div>
                        <span class="mayhen_invitation_cd_label">Giờ</span>
                    </div>
                    <div class="mayhen_invitation_cd_box">
                        <div class="mayhen_invitation_cd_heart" style="--mh-cd-delay: 0.5s;">
                            <svg viewBox="0 0 1792 1896.0833" aria-hidden="true"><path d="M896 1664q-26 0-44-18l-624-602q-10-8-27.5-26T145 952.5 77 855 23.5 734 0 596q0-220 127-344t351-124q62 0 126.5 21.5t120 58T820 276t76 68q36-36 76-68t95.5-68.5 120-58T1314 128q224 0 351 124t127 344q0 221-229 450l-623 600q-18 18-44 18z"></path></svg>
                            <span class="mayhen_invitation_cd_num mayhen_invitation_cd_min" data-sync-target="countdown_min">00</span>
                        </div>
                        <span class="mayhen_invitation_cd_label">Phút</span>
                    </div>
                    <div class="mayhen_invitation_cd_box">
                        <div class="mayhen_invitation_cd_heart" style="--mh-cd-delay: 0.75s;">
                            <svg viewBox="0 0 1792 1896.0833" aria-hidden="true"><path d="M896 1664q-26 0-44-18l-624-602q-10-8-27.5-26T145 952.5 77 855 23.5 734 0 596q0-220 127-344t351-124q62 0 126.5 21.5t120 58T820 276t76 68q36-36 76-68t95.5-68.5 120-58T1314 128q224 0 351 124t127 344q0 221-229 450l-623 600q-18 18-44 18z"></path></svg>
                            <span class="mayhen_invitation_cd_num mayhen_invitation_cd_sec" data-sync-target="countdown_sec">00</span>
                        </div>
                        <span class="mayhen_invitation_cd_label">Giây</span>
                    </div>
                </div>
                <i class="mayhen_invitation_countdown_clouds mayhen_invitation_decor" aria-hidden="true"></i>
            </div>
        </section>

        {{-- SECTION 3: SONG THÂN HAI HỌ & CÔ DÂU CHÚ RỂ --}}
        <section class="mayhen_invitation_section mayhen_invitation_parents mayhen_invitation_couple" data-mayhen-section="parents,groom_family,bride_family">
            <div class="mayhen_invitation_couple_title_wrap mayhen_invitation_reveal">
                <i class="mayhen_invitation_brush_bg mayhen_invitation_decor" aria-hidden="true">
                    <svg viewBox="0 0 416.07 97.96" preserveAspectRatio="none">
                        <path d="M415.93,69.33c-8.52,0-18.6.57-28,.57,0-1,2.1-2.42,2.87-3,1.16-.85,3.13-.63,4.56-3.17.44-.77-1.24-1.84,0-3.23.74-.84,2-1,2.56-1.48.74-.61,7.84-4.58,2-4.58.39-2.15,7.7-1,7.44-3-.08-.65-19.25-1-21.17-1,0-2.35,5.1,0,5.72-1.14.83-1.52-7.66-1.12-8.57-1.91.82-2.44.88.25-.62-2.46,5.08-4.78,11.85-2.2,17.75-5.61-1.82-1.61-8.17.68-10.5.82-4.22.26-13.52,1.43-16.36-1.42,1.42-1.48,9.88-.82,10.19-1.63.73-1.91-10.36.43-10.77-2.1.89.27,1.44-1.74.81-2.28.77.66-4.6,0-4.25-1.15.93.41,1.5.22,1.74-.56,0-1.2-6.2-1.73-6.89-1.73,0-3,12.9-4.62,14.82-4.87,3.53-.44,8.16-1.21,11.51-1.42,1.6-.11,4-.51,5.15-.58,2.11-.12,4,.2,5.79-.45,1-.36-.2-1.26-.64-1.26,0-.68,4.94-3.21,5.72-3.44,2.43-.7,6.7.53,8.51-1.39-1-1.1-10.83-.83-12.38-.9-4.3-.19-8.14-.07-12.08-.57-7.41-.94-14.46-1.16-21.82-2.29-12.21-1.87-24.64-4.48-37.49-5.72C317,5,302,3.93,287.31,3.09,271,2.15,253.49.31,236.68.09,213-.23,190.44.39,167.56.66c-14.71.17-31.73-.19-46.35,1.71-28,3.65-57.48,3-85.56,8-4.31.77-27.23,6.23-18.34,12,1.34.87,4,.65,4.9,1.43.68.58,2.71,4.31,2.58,5.43-.21,1.85,1,.89-.67,2.87-.43.52-3.82-.58-4.2,0-1.48,2.29,4.52,1.15,5.72,1.15,0,2.69-5.88.59-6.78,2.49-.11.25,13-1.16,11.72,2.43-.73,2.1-6-1.26-7.13,1-.17.34,5.7,1.1,6.2,1-.07.43-10.8,1.15-12,1.15-.25.82,1.77,1.47,2.3,1.91-.3,3.19-8.21-1.11-5.61,3.81,1.79,3.38,6,1.51,8.46,3.16-1.94,2.67-1.87.74-1.49,4.28.18,1.64,3.44.37.55,2.87-1.38,1.19-7.46,1.19-9.65,1.14-3.48-.08-7.22-1-10,.84a12.6,12.6,0,0,0-2,2c-.34.67-.23,4,.57,4.58,1.1.85,3.37-.19,4.29.57,2.25,1.84-.16,3.44.88,5.15.55.89,3.59,3.73,4.27,4.58.52.64,1.88,1.87,2.6,2.91,1,1.43.62,2.44,1.89,3.56,2.24,2,5.66,2.22,8.12,4.14,1.86,1.45,2.29,2.41,4.5,4,2.76,1.95-.82,2,4.43,2.48,0,0,.82-1.06,1-1,2.33.2,13.82,2.29,13.57,2.56.65.87,2.27.06,2.27-.85,2.81,0,5.37,1.4,8.29,1.72,1.28.14,2.28-.36,4,.58.81-.92,5.69.54,7.11.56,2.77,0,20.54.82,20,.85,2.29-.15,10.53-1,11.12.31,1.52-1.44,11.37-1.44,13.78-1.73,3.7-.45,7.47-.13,10.3-.58,5-.77,9.58,0,13.73.58,2.6.33,143.42-.78,168.09-1.72,10.88-.42,25.81,0,36.28,0,3.54,0,34,1.06,34,0,0-.48-7.66-1.17-8.11-1.15a24.85,24.85,0,0,1-5.85.09,51.74,51.74,0,0,0-7.23-.37,35.42,35.42,0,0,1-4.83.29,26.57,26.57,0,0,1-2.8-1.06c-1-.21-1.16,1.34-2.1,1.06a.9.9,0,0,0-.57-1.15c.43-2.17,24.68-.6,26.33-.57,1.29,0,2.64-.5,4.22-.5a66.55,66.55,0,0,1,6.67.2,164.81,164.81,0,0,0,18.55-.27c1,0,19.52-4.74,16-7.44h-4.28C411.35,82.05,416.34,69.33,415.93,69.33Z"></path>
                    </svg>
                </i>
                <h2 class="mayhen_invitation_script_title">Cô dâu &amp; Chú rể</h2>
            </div>

            <div class="mayhen_invitation_couple_layout">
                <div class="mayhen_invitation_rose_frame mayhen_invitation_reveal">
                    <div class="mayhen_invitation_rose_frame_photo">
                        <img src="{{ $openingPhotoUrl }}" alt="Ảnh đôi {{ $groomName }} & {{ $brideName }}">
                    </div>
                    <i class="mayhen_invitation_rose_frame_overlay mayhen_invitation_decor" aria-hidden="true"></i>
                </div>

                <div class="mayhen_invitation_families_grid">
                    <div class="mayhen_invitation_family_col mayhen_invitation_family_col--groom mayhen_invitation_reveal">
                        <h4 class="mayhen_invitation_family_groom_title">Nhà Trai</h4>
                        <div class="mayhen_invitation_portrait_box">
                            <img src="{{ $groomPhotoUrl }}" alt="Chú rể {{ $groomName }}" data-sync-target="groom_photo_url">
                        </div>
                        <p class="mayhen_invitation_role_name">Chú rể: <span data-sync-target="groom_name">{{ $groomName }}</span></p>
                        <p class="mayhen_invitation_parents_names">
                            <span data-sync-target="groom_father">{{ $groomFather }}</span><br>
                            <span data-sync-target="groom_mother">{{ $groomMother }}</span>
                        </p>
                        <p class="mayhen_invitation_parents_addr" data-sync-target="groom_parents_address">{{ $groomParentsAddr }}</p>
                        <p class="mayhen_invitation_couple_bio mayhen_invitation_groom_bio" data-sync-target="groom_bio">{{ $groomBio }}</p>
                    </div>

                    <div class="mayhen_invitation_family_col mayhen_invitation_family_col--bride mayhen_invitation_reveal">
                        <h4 class="mayhen_invitation_family_bride_title">Nhà Gái</h4>
                        <div class="mayhen_invitation_portrait_box">
                            <img src="{{ $bridePhotoUrl }}" alt="Cô dâu {{ $brideName }}" data-sync-target="bride_photo_url">
                        </div>
                        <p class="mayhen_invitation_role_name">Cô dâu: <span data-sync-target="bride_name">{{ $brideName }}</span></p>
                        <p class="mayhen_invitation_parents_names">
                            <span data-sync-target="bride_father">{{ $brideFather }}</span><br>
                            <span data-sync-target="bride_mother">{{ $brideMother }}</span>
                        </p>
                        <p class="mayhen_invitation_parents_addr" data-sync-target="bride_parents_address">{{ $brideParentsAddr }}</p>
                        <p class="mayhen_invitation_couple_bio mayhen_invitation_bride_bio" data-sync-target="bride_bio">{{ $brideBio }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 2: CÂU CHUYỆN TÌNH YÊU --}}
        <section class="mayhen_invitation_section mayhen_invitation_story" data-mayhen-section="story">
            <div class="mayhen_invitation_section_head mayhen_invitation_reveal">
                <h2 class="mayhen_invitation_section_title" data-sync-target="story_title">{{ $storyTitle }}</h2>
                <i class="mayhen_invitation_ripple_divider mayhen_invitation_decor" aria-hidden="true">
                    <svg viewBox="0 0 160 22" fill="none" stroke="currentColor" stroke-linecap="round">
                        <path d="M66 12a14 5 0 0 0 28 0" stroke-width="1.6"></path>
                        <path d="M48 9a32 9 0 0 0 64 0" stroke-width="1.2" opacity=".75"></path>
                        <path d="M24 6a56 13 0 0 0 112 0" stroke-width="1" opacity=".5"></path>
                        <path d="M2 3a78 17 0 0 0 156 0" stroke-width=".8" opacity=".3"></path>
                        <circle cx="80" cy="7" r="2.2" fill="currentColor" stroke="none"></circle>
                    </svg>
                </i>
            </div>

            <div class="mayhen_invitation_story_list">
                <article class="mayhen_invitation_story_chapter mayhen_invitation_reveal">
                    <div class="mayhen_invitation_story_photo_wrap">
                        <img src="{{ $galleryPhotos[0] }}" alt="Kỷ niệm gặp gỡ">
                    </div>
                    <div class="mayhen_invitation_story_body">
                        <h3 class="mayhen_invitation_story_subtitle" data-sync-target="story_subtitle">{{ $storySubtitle }}</h3>
                        <blockquote class="mayhen_invitation_quote_text" data-sync-target="couple_quote">{{ $coupleQuote }}</blockquote>
                    </div>
                </article>

                <article class="mayhen_invitation_story_chapter mayhen_invitation_story_chapter--even mayhen_invitation_reveal">
                    <div class="mayhen_invitation_story_photo_wrap">
                        <img src="{{ $galleryPhotos[1] }}" alt="Hành trình bên nhau">
                    </div>
                    <div class="mayhen_invitation_story_body">
                        <h3 class="mayhen_invitation_story_subtitle">Hành Trình Bên Nhau</h3>
                        <p class="mayhen_invitation_story_content" data-sync-target="story_content">{{ $storyContent }}</p>
                    </div>
                </article>

                <article class="mayhen_invitation_story_chapter mayhen_invitation_reveal">
                    <div class="mayhen_invitation_story_photo_wrap">
                        <img src="{{ $galleryPhotos[2] }}" alt="Yêu đủ rồi, cưới thôi">
                    </div>
                    <div class="mayhen_invitation_story_body">
                        <h3 class="mayhen_invitation_story_subtitle">Yêu Đủ Rồi, Cưới Thôi</h3>
                        <p class="mayhen_invitation_story_content">Cùng nhau trưởng thành, thấu hiểu và nắm tay bước vào chương mới rạng rỡ nhất — cảm ơn vì đã luôn là thanh xuân và bến đỗ bình yên của nhau.</p>
                    </div>
                </article>
            </div>
        </section>

        {{-- SECTION 8: ALBUM 10 ẢNH CƯỚI (COVERFLOW 3D + LIGHTBOX) --}}
        <section class="mayhen_invitation_section mayhen_invitation_gallery" data-mayhen-section="album,gallery">
            <div class="mayhen_invitation_gallery_banner">
                <div class="mayhen_invitation_section_head">
                    <h2 class="mayhen_invitation_section_title">Album Ảnh Cưới</h2>
                    <i class="mayhen_invitation_ripple_divider mayhen_invitation_decor" aria-hidden="true">
                        <svg viewBox="0 0 160 22" fill="none" stroke="currentColor" stroke-linecap="round">
                            <path d="M66 12a14 5 0 0 0 28 0" stroke-width="1.6"></path>
                            <path d="M48 9a32 9 0 0 0 64 0" stroke-width="1.2" opacity=".75"></path>
                            <path d="M24 6a56 13 0 0 0 112 0" stroke-width="1" opacity=".5"></path>
                            <circle cx="80" cy="7" r="2.2" fill="currentColor" stroke="none"></circle>
                        </svg>
                    </i>
                    <p class="mayhen_invitation_desc">Cùng ngắm nhìn khoảnh khắc ngọt ngào của chúng mình nhé!</p>
                </div>
            </div>

            <div class="mayhen_invitation_gallery_wrap" data-mayhen-coverflow>
                <div class="mayhen_invitation_coverflow_stage">
                    <button type="button" class="mayhen_invitation_gallery_nav mayhen_invitation_gallery_prev_btn" data-cf-prev aria-label="Ảnh trước">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>

                    @foreach($galleryPhotos as $gIdx => $gUrl)
                        <div class="mayhen_invitation_gallery_item" data-index="{{ $gIdx }}" data-lightbox-index="{{ $gIdx }}">
                            <img
                                class="mayhen_invitation_gallery_img"
                                src="{{ $gUrl }}"
                                alt="Ảnh cưới {{ $gIdx + 1 }}"
                                loading="eager"
                                decoding="async"
                                data-sync-target="gallery.{{ $gIdx }}"
                            >
                        </div>
                    @endforeach

                    <button type="button" class="mayhen_invitation_gallery_nav mayhen_invitation_gallery_next_btn" data-cf-next aria-label="Ảnh tiếp theo">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                </div>

                <div class="mayhen_invitation_gallery_dots" data-cf-dots>
                    @foreach($galleryPhotos as $gIdx => $gUrl)
                        <button
                            type="button"
                            class="mayhen_invitation_gallery_dot {{ $gIdx === 0 ? 'mayhen_invitation_gallery_dot--active' : '' }}"
                            data-cf-dot="{{ $gIdx }}"
                            aria-label="Xem ảnh {{ $gIdx + 1 }}"
                        ></button>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- SECTION 6: LỊCH TRÌNH NGÀY CƯỚI ("DÒNG SUỐI THỜI GIAN") --}}
        <section class="mayhen_invitation_section mayhen_invitation_timeline" data-mayhen-section="timeline">
            <div class="mayhen_invitation_section_head mayhen_invitation_reveal">
                <p class="mayhen_invitation_section_kicker">Our wedding day</p>
                <h2 class="mayhen_invitation_section_title">Lịch Trình Ngày Cưới</h2>
                <i class="mayhen_invitation_ripple_divider mayhen_invitation_decor" aria-hidden="true">
                    <svg viewBox="0 0 160 22" fill="none" stroke="currentColor" stroke-linecap="round">
                        <path d="M66 12a14 5 0 0 0 28 0" stroke-width="1.6"></path>
                        <path d="M48 9a32 9 0 0 0 64 0" stroke-width="1.2" opacity=".75"></path>
                        <path d="M24 6a56 13 0 0 0 112 0" stroke-width="1" opacity=".5"></path>
                        <circle cx="80" cy="7" r="2.2" fill="currentColor" stroke="none"></circle>
                    </svg>
                </i>
            </div>

            <div class="mayhen_invitation_timeline_list">
                <div class="mayhen_invitation_timeline_stream mayhen_invitation_decor" aria-hidden="true"></div>
                @foreach($timelineItems as $tIdx => $tItem)
                    @php
                        $sideClass = ($tIdx % 2 === 0) ? 'mayhen_invitation_timeline_item--left' : 'mayhen_invitation_timeline_item--right';
                    @endphp
                    <div class="mayhen_invitation_timeline_item {{ $sideClass }} mayhen_invitation_reveal">
                        <div class="mayhen_invitation_timeline_dot" aria-hidden="true">
                            @if($tIdx % 4 === 0)
                                <svg viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                            @elseif($tIdx % 4 === 1)
                                <svg viewBox="0 0 24 24"><path d="M8 22h8M12 11v11M6 2h12l-1 6a5 5 0 0 1-10 0L6 2z"></path></svg>
                            @elseif($tIdx % 4 === 2)
                                <svg viewBox="0 0 24 24"><circle cx="9" cy="14" r="4.5"></circle><circle cx="15" cy="14" r="4.5"></circle><path d="M12 5l1.5 2.5h-3L12 5z"></path></svg>
                            @else
                                <svg viewBox="0 0 24 24"><path d="M4 20h16M6 20v-6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v6M9 12V8h6v4M12 4v4"></path></svg>
                            @endif
                        </div>
                        <i class="mayhen_invitation_timeline_ripple mayhen_invitation_decor" aria-hidden="true"></i>
                        <div class="mayhen_invitation_timeline_content">
                            <div class="mayhen_invitation_timeline_time" data-sync-target="timeline.{{ $tIdx }}.time">{{ $tItem['time'] ?? '' }}</div>
                            <div class="mayhen_invitation_timeline_text" data-sync-target="timeline.{{ $tIdx }}.title">{{ $tItem['title'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- DẢI NHẮC NHỚ NGÀY HÔM ĐẤY NHÉ --}}
        <section class="mayhen_invitation_section mayhen_invitation_reminder">
            <div class="mayhen_invitation_reminder_grid">
                <div class="mayhen_invitation_reminder_photo mayhen_invitation_reveal">
                    <img src="{{ $galleryPhotos[6] }}" alt="Khoảnh khắc nâng ly hạnh phúc">
                </div>
                <div class="mayhen_invitation_reminder_banner mayhen_invitation_reveal">
                    <i class="mayhen_invitation_reminder_lotus mayhen_invitation_decor" aria-hidden="true"></i>
                    <h2 class="mayhen_invitation_reminder_title">Nhớ ngày<br>hôm đấy nhé</h2>
                    <p class="mayhen_invitation_reminder_text">Sự hiện diện của bạn là niềm vinh hạnh của chúng mình!</p>
                </div>
            </div>
        </section>

        {{-- SECTION 7: DRESSCODE (Ẩn khi chế độ tinh tuyển 7 Section) --}}
        @if(!$isCuratedSeven)
        <section class="mayhen_invitation_section mayhen_invitation_dresscode" data-mayhen-section="dresscode">
            <h2 class="mayhen_invitation_script_title mayhen_invitation_reveal">Dresscode</h2>
            <p class="mayhen_invitation_dresscode_note mayhen_invitation_reveal" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>
            <div class="mayhen_invitation_dresscode_swatches mayhen_invitation_reveal" data-sync-target="dresscode_colors">
                @foreach($dresscodePalette as $hexColor)
                    @php
                        $lowerHex = strtolower($hexColor);
                        $colorLabel = $colorNameMap[$lowerHex] ?? strtoupper($hexColor);
                    @endphp
                    <div class="mayhen_invitation_dresscode_item">
                        <div class="mayhen_invitation_dresscode_drop">
                            <div class="mayhen_invitation_dresscode_circle" style="--mh-swatch-color: {{ $hexColor }};"></div>
                            <div class="mayhen_invitation_dresscode_ripple mayhen_invitation_decor" aria-hidden="true"></div>
                        </div>
                        <span class="mayhen_invitation_dresscode_label">{{ $colorLabel }}</span>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- SECTION 9: HỘP MỪNG CƯỚI & VIETQR ĐỘNG (Ẩn khi chế độ tinh tuyển 7 Section) --}}
        @if(!$isCuratedSeven)
        <section class="mayhen_invitation_section mayhen_invitation_gift" data-mayhen-section="gift">
            <div class="mayhen_invitation_section_head mayhen_invitation_reveal">
                <h2 class="mayhen_invitation_section_title">Gửi Quà Mừng Đến<br>Cô Dâu &amp; Chú Rể</h2>
                <i class="mayhen_invitation_ripple_divider mayhen_invitation_decor" aria-hidden="true">
                    <svg viewBox="0 0 160 22" fill="none" stroke="currentColor" stroke-linecap="round">
                        <path d="M66 12a14 5 0 0 0 28 0" stroke-width="1.6"></path>
                        <path d="M48 9a32 9 0 0 0 64 0" stroke-width="1.2" opacity=".75"></path>
                        <path d="M24 6a56 13 0 0 0 112 0" stroke-width="1" opacity=".5"></path>
                        <circle cx="80" cy="7" r="2.2" fill="currentColor" stroke="none"></circle>
                    </svg>
                </i>
                <p class="mayhen_invitation_desc">Sự hiện diện của bạn là niềm vinh hạnh của chúng tôi!</p>
            </div>

            <div class="mayhen_invitation_gift_arch mayhen_invitation_reveal">
                <i class="mayhen_invitation_gift_arch_ring mayhen_invitation_decor" aria-hidden="true"></i>
                <img src="{{ $galleryPhotos[4] }}" alt="Ảnh đôi mừng cưới">
            </div>

            <div class="mayhen_invitation_gift_stage">
                <div class="mayhen_invitation_gift_box_wrap" data-mayhen-gift-box-wrap>
                    <button
                        type="button"
                        class="mayhen_invitation_gift_box"
                        data-mayhen-gift-trigger
                        aria-expanded="false"
                        aria-label="Chạm bao lì xì để xem mã mừng cưới"
                    >
                        <span class="mayhen_invitation_gift_envelope">
                            <span class="mayhen_invitation_gift_env_body"></span>
                            <span class="mayhen_invitation_gift_env_flap"></span>
                            <span class="mayhen_invitation_gift_env_coin"></span>
                            <span class="mayhen_invitation_gift_env_hy">
                                <svg viewBox="0 0 100 94" aria-hidden="true">
                                    <rect x="8" y="4" width="34" height="5" rx="1"></rect><rect x="22" y="4" width="6" height="18" rx="1"></rect>
                                    <rect x="12" y="16" width="26" height="4" rx="1"></rect><rect x="12" y="24" width="26" height="4" rx="1"></rect>
                                    <rect x="12" y="24" width="4" height="14" rx="1"></rect><rect x="34" y="24" width="4" height="14" rx="1"></rect>
                                    <rect x="12" y="34" width="26" height="4" rx="1"></rect><rect x="5" y="50" width="40" height="5" rx="1"></rect>
                                    <rect x="12" y="60" width="26" height="4" rx="1"></rect><rect x="12" y="60" width="4" height="30" rx="1"></rect>
                                    <rect x="34" y="60" width="4" height="30" rx="1"></rect><rect x="12" y="86" width="26" height="4" rx="1"></rect>
                                    <rect x="58" y="4" width="34" height="5" rx="1"></rect><rect x="72" y="4" width="6" height="18" rx="1"></rect>
                                    <rect x="62" y="16" width="26" height="4" rx="1"></rect><rect x="62" y="24" width="26" height="4" rx="1"></rect>
                                    <rect x="62" y="24" width="4" height="14" rx="1"></rect><rect x="84" y="24" width="4" height="14" rx="1"></rect>
                                    <rect x="62" y="34" width="26" height="4" rx="1"></rect><rect x="55" y="50" width="40" height="5" rx="1"></rect>
                                    <rect x="62" y="60" width="26" height="4" rx="1"></rect><rect x="62" y="60" width="4" height="30" rx="1"></rect>
                                    <rect x="84" y="60" width="4" height="30" rx="1"></rect><rect x="62" y="86" width="26" height="4" rx="1"></rect>
                                </svg>
                            </span>
                        </span>
                        <i class="mayhen_invitation_gift_cloud mayhen_invitation_gift_cloud--left"></i>
                        <i class="mayhen_invitation_gift_cloud mayhen_invitation_gift_cloud--right"></i>
                    </button>
                    <p class="mayhen_invitation_gift_hint">Chạm bao lì xì để xem mã mừng cưới</p>
                </div>

                <div class="mayhen_invitation_gift_cards" data-mayhen-gift-cards>
                    <div class="mayhen_invitation_gift_card_groom">
                        <div class="mayhen_invitation_gift_role_groom">Chú Rể</div>
                        <div class="mayhen_invitation_gift_qr_lake">
                            <div class="mayhen_invitation_gift_qr_frame">
                                <img
                                    class="mayhen_invitation_gift_qr_groom"
                                    src="{{ $qrGroomUrl }}"
                                    alt="Mã VietQR Chú rể {{ $groomName }}"
                                    data-sync-target="qr_groom_image"
                                >
                            </div>
                        </div>
                        <div class="mayhen_invitation_gift_bank_meta">
                            <div data-sync-target="groom_bank_name">{{ $groomBankName }}</div>
                            <div>STK: <b data-sync-target="groom_bank_account">{{ $groomBankAccount }}</b></div>
                            <div data-sync-target="groom_account_name">{{ $groomAccountName }}</div>
                        </div>
                        <button type="button" class="mayhen_invitation_copy_btn" data-copy-target="groom_bank_account" data-copy="{{ $groomBankAccount }}">Sao chép STK</button>
                    </div>

                    <div class="mayhen_invitation_gift_card_bride">
                        <div class="mayhen_invitation_gift_role_bride">Cô Dâu</div>
                        <div class="mayhen_invitation_gift_qr_lake">
                            <div class="mayhen_invitation_gift_qr_frame">
                                <img
                                    class="mayhen_invitation_gift_qr_bride"
                                    src="{{ $qrBrideUrl }}"
                                    alt="Mã VietQR Cô dâu {{ $brideName }}"
                                    data-sync-target="qr_bride_image"
                                >
                            </div>
                        </div>
                        <div class="mayhen_invitation_gift_bank_meta">
                            <div data-sync-target="bride_bank_name">{{ $brideBankName }}</div>
                            <div>STK: <b data-sync-target="bride_bank_account">{{ $brideBankAccount }}</b></div>
                            <div data-sync-target="bride_account_name">{{ $brideAccountName }}</div>
                        </div>
                        <button type="button" class="mayhen_invitation_copy_btn" data-copy-target="bride_bank_account" data-copy="{{ $brideBankAccount }}">Sao chép STK</button>
                    </div>
                </div>
            </div>
        </section>
        @endif

        {{-- SECTION 10: KÝ TÊN, RSVP, SỔ LƯU BÚT, BẢN ĐỒ & LỜI CẢM ƠN --}}
        <section class="mayhen_invitation_section mayhen_invitation_interactive_ending" data-mayhen-section="ending,rsvp,wishes">

            {{-- 10.1 SỔ KÝ TÊN ĐIỆN TỬ (TUYỆT ĐỐI KHÔNG ẢNH NGƯỜI — LUẬT #8) --}}
            <div class="mayhen_invitation_signature">
                <p class="mayhen_invitation_signature_kicker">Signature</p>
                <h2 class="mayhen_invitation_signature_title">Lưu Bút</h2>
                <p class="mayhen_invitation_desc">Để lại chữ ký của Bạn và cùng chúng mình lưu giữ kỷ niệm hạnh phúc này nhé!</p>

                <div class="mayhen_invitation_signature_board">
                    <div class="mayhen_invitation_signature_crest">
                        <svg class="mayhen_invitation_signature_wreath" viewBox="0 0 120 110" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="M60 96 C26 74 14 52 18 32 C22 14 42 10 56 24 L60 28 L64 24 C78 10 98 14 102 32 C106 52 94 74 60 96 Z" stroke-width="1.6" stroke-dasharray="3 2"></path>
                            <path d="M60 90 C32 71 22 52 25 35 C28 21 44 18 56 29 L60 33 L64 29 C76 18 92 21 95 35 C98 52 88 71 60 90 Z" stroke-width="1" opacity="0.65"></path>
                            <circle cx="60" cy="54" r="22" fill="#FFFDF6" stroke="currentColor" stroke-width="1.3"></circle>
                            <text x="60" y="60" text-anchor="middle" fill="#8A6420" stroke="none" font-family="Cormorant Upright, Georgia, serif" font-size="18" font-weight="700" data-mayhen-wax-monogram>{{ $groomInitial }} &amp; {{ $brideInitial }}</text>
                        </svg>
                        <p class="mayhen_invitation_signature_couple">
                            <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                        </p>
                    </div>

                    <div class="mayhen_invitation_signature_bg">
                        <canvas class="mayhen_invitation_signature_canvas" data-mayhen-sig-canvas aria-label="Bảng vẽ chữ ký chúc phúc"></canvas>
                        <div class="mayhen_invitation_signature_placeholder" data-mayhen-sig-placeholder>Chạm và vẽ chữ ký chúc phúc của bạn tại đây…</div>
                    </div>

                    <div class="mayhen_invitation_signature_controls">
                        <input
                            type="text"
                            class="mayhen_invitation_signature_input"
                            data-mayhen-sig-name
                            placeholder="Nhập tên của bạn để lưu chữ ký"
                            value="{{ $guestName !== 'Bạn và Gia Đình' ? $guestName : '' }}"
                            maxlength="100"
                        >
                        <div class="mayhen_invitation_signature_actions">
                            <button type="button" class="mayhen_invitation_sig_btn" data-mayhen-sig-clear>Xóa chữ ký</button>
                            <button type="button" class="mayhen_invitation_sig_btn" data-mayhen-sig-preview-btn>Xem trước</button>
                            <button type="button" class="mayhen_invitation_sig_btn mayhen_invitation_sig_btn--primary mayhen_invitation_signature_save_btn" data-mayhen-sig-save>Lưu chữ ký</button>
                        </div>
                        <div class="mayhen_invitation_signature_preview" data-mayhen-sig-preview-box>
                            <img src="" alt="Xem trước chữ ký" data-mayhen-sig-preview-img>
                            <a href="#" download="chu-ky-chuc-phuc.png" class="mayhen_invitation_sig_btn" data-mayhen-sig-download>Tải ảnh PNG</a>
                        </div>
                        <div class="mayhen_invitation_feedback" data-mayhen-sig-feedback role="status" aria-live="polite"></div>
                    </div>
                </div>
            </div>

            {{-- 10.2 XÁC NHẬN THAM DỰ RSVP (TUYỆT ĐỐI KHÔNG ẢNH NGƯỜI — LUẬT #8) --}}
            <div class="mayhen_invitation_rsvp">
                <div class="mayhen_invitation_rsvp_card">
                    <div class="mayhen_invitation_rsvp_seal" data-mayhen-wax-seal>
                        <span data-monogram-role="groom">{{ $groomInitial }}</span>&amp;<span data-monogram-role="bride">{{ $brideInitial }}</span>
                    </div>
                    <p class="mayhen_invitation_rsvp_kicker">Xác nhận</p>
                    <h2 class="mayhen_invitation_section_title">Bạn Sẽ Đến Chứ</h2>
                    <p class="mayhen_invitation_desc">Chúng mình rất chờ đón sự kiện trọng đại này của cuộc đời. Xin hãy xác nhận sự có mặt để chúng mình đón tiếp chu đáo nhất nhé!</p>

                    @if(!empty($isRsvpClosed))
                        <div class="mayhen_invitation_feedback" style="margin-top: 16px;">
                            {{ $rsvpClosedNotice ?? 'Đã hết thời gian xác nhận tham dự.' }}
                        </div>
                    @else
                        <form class="mayhen_invitation_rsvp_form" data-mayhen-rsvp-form novalidate>
                            <div class="mayhen_invitation_form_field">
                                <label class="mayhen_invitation_form_label" for="mayhen_rsvp_name">Tên khách mời</label>
                                <input
                                    id="mayhen_rsvp_name"
                                    type="text"
                                    class="mayhen_invitation_form_input mayhen_invitation_rsvp_name_input"
                                    name="guest_name"
                                    placeholder="Nhập tên của bạn"
                                    value="{{ $guestName !== 'Bạn và Gia Đình' ? $guestName : '' }}"
                                    maxlength="100"
                                    required
                                >
                            </div>

                            <div class="mayhen_invitation_form_field">
                                <span class="mayhen_invitation_form_label">Xác nhận tham dự</span>
                                <div class="mayhen_invitation_opt_group mayhen_invitation_opt_group--three" data-mayhen-rsvp-status-group>
                                    <button type="button" class="mayhen_invitation_opt_btn mayhen_invitation_opt_btn--active" data-status-val="attending">Sẽ tham dự</button>
                                    <button type="button" class="mayhen_invitation_opt_btn" data-status-val="declined">Tiếc quá</button>
                                    <button type="button" class="mayhen_invitation_opt_btn" data-status-val="uncertain">Chưa chắc</button>
                                </div>
                            </div>

                            <div class="mayhen_invitation_counter_grid" data-mayhen-rsvp-counts>
                                <div class="mayhen_invitation_form_field">
                                    <span class="mayhen_invitation_form_label">Người lớn</span>
                                    <div class="mayhen_invitation_counter">
                                        <button type="button" class="mayhen_invitation_counter_btn" data-counter-dec="adults">−</button>
                                        <input type="text" class="mayhen_invitation_counter_val" data-counter-input="adults" value="1" readonly aria-label="Số người lớn">
                                        <button type="button" class="mayhen_invitation_counter_btn" data-counter-inc="adults">+</button>
                                    </div>
                                </div>
                                <div class="mayhen_invitation_form_field">
                                    <span class="mayhen_invitation_form_label">Trẻ em</span>
                                    <div class="mayhen_invitation_counter">
                                        <button type="button" class="mayhen_invitation_counter_btn" data-counter-dec="children">−</button>
                                        <input type="text" class="mayhen_invitation_counter_val" data-counter-input="children" value="0" readonly aria-label="Số trẻ em">
                                        <button type="button" class="mayhen_invitation_counter_btn" data-counter-inc="children">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="mayhen_invitation_form_field">
                                <label class="mayhen_invitation_form_label" for="mayhen_rsvp_wishes">Lời nhắn gửi (tùy chọn)</label>
                                <textarea
                                    id="mayhen_rsvp_wishes"
                                    class="mayhen_invitation_form_textarea"
                                    name="wishes"
                                    maxlength="1000"
                                    placeholder="Ghi chú ăn chay hoặc lời chúc gửi cô dâu chú rể..."
                                ></textarea>
                            </div>

                            <button type="submit" class="mayhen_invitation_submit_btn mayhen_invitation_rsvp_submit_btn">Xác Nhận Tham Dự</button>
                            <div class="mayhen_invitation_feedback" data-mayhen-rsvp-feedback role="status" aria-live="polite"></div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- 10.3 SỔ LƯU BÚT LỜI CHÚC --}}
            <div class="mayhen_invitation_wishes">
                <div class="mayhen_invitation_section_head">
                    <h2 class="mayhen_invitation_section_title">Lời Chúc Từ Khách Mời</h2>
                    <i class="mayhen_invitation_ripple_divider mayhen_invitation_decor" aria-hidden="true">
                        <svg viewBox="0 0 160 22" fill="none" stroke="currentColor" stroke-linecap="round">
                            <path d="M66 12a14 5 0 0 0 28 0" stroke-width="1.6"></path>
                            <path d="M48 9a32 9 0 0 0 64 0" stroke-width="1.2" opacity=".75"></path>
                            <path d="M24 6a56 13 0 0 0 112 0" stroke-width="1" opacity=".5"></path>
                            <circle cx="80" cy="7" r="2.2" fill="currentColor" stroke="none"></circle>
                        </svg>
                    </i>
                </div>

                <div class="mayhen_invitation_wishes_grid">
                    <div class="mayhen_invitation_wish_box">
                        <i class="mayhen_invitation_wish_rings mayhen_invitation_decor" aria-hidden="true"></i>
                        <i class="mayhen_invitation_wish_floral mayhen_invitation_decor" aria-hidden="true"></i>
                        <div class="mayhen_invitation_wish_list" data-mayhen-wish-list>
                            @forelse($wishes ?? [] as $wish)
                                <div class="mayhen_invitation_wish_item">
                                    <p class="mayhen_invitation_wish_author">{{ $wish->name }}</p>
                                    <p class="mayhen_invitation_wish_text">{{ $wish->message }}</p>
                                    @if(!empty($wish->reply))
                                        <div class="mayhen_invitation_wish_reply">
                                            <strong>Cô dâu &amp; Chú rể:</strong> {{ $wish->reply }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="mayhen_invitation_wish_empty" data-mayhen-wish-empty>
                                    Hãy là người đầu tiên gửi lời chúc tới cô dâu, chú rể
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mayhen_invitation_wish_form_card">
                        <form class="mayhen_invitation_wish_form" data-mayhen-wish-form novalidate>
                            <div class="mayhen_invitation_form_field">
                                <label class="mayhen_invitation_form_label" for="mayhen_wish_name">Tên của bạn</label>
                                <input
                                    id="mayhen_wish_name"
                                    type="text"
                                    class="mayhen_invitation_form_input mayhen_invitation_wish_name_input"
                                    name="name"
                                    maxlength="100"
                                    placeholder="Nhập tên của bạn"
                                    value="{{ $guestName !== 'Bạn và Gia Đình' ? $guestName : '' }}"
                                    required
                                >
                            </div>
                            <div class="mayhen_invitation_form_field">
                                <label class="mayhen_invitation_form_label" for="mayhen_wish_msg">Lời chúc của bạn</label>
                                <textarea
                                    id="mayhen_wish_msg"
                                    class="mayhen_invitation_form_textarea mayhen_invitation_wish_textarea"
                                    name="message"
                                    maxlength="1000"
                                    placeholder="Viết lời chúc ngọt ngào gửi đến đôi uyên ương..."
                                    required
                                ></textarea>
                            </div>
                            <button type="submit" class="mayhen_invitation_submit_btn mayhen_invitation_wish_submit_btn">Gửi Lời Chúc</button>
                            <div class="mayhen_invitation_feedback" data-mayhen-wish-feedback role="status" aria-live="polite"></div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 10.4 VỊ TRÍ BẢN ĐỒ MẶT HỒ --}}
            <div class="mayhen_invitation_maps" id="mayhen_section_map">
                <div class="mayhen_invitation_section_head">
                    <h2 class="mayhen_invitation_section_title">Vị Trí</h2>
                    <i class="mayhen_invitation_ripple_divider mayhen_invitation_decor" aria-hidden="true">
                        <svg viewBox="0 0 160 22" fill="none" stroke="currentColor" stroke-linecap="round">
                            <path d="M66 12a14 5 0 0 0 28 0" stroke-width="1.6"></path>
                            <path d="M48 9a32 9 0 0 0 64 0" stroke-width="1.2" opacity=".75"></path>
                            <circle cx="80" cy="7" r="2.2" fill="currentColor" stroke="none"></circle>
                        </svg>
                    </i>
                    <p class="mayhen_invitation_desc">Rất hân hạnh được đón tiếp bạn bên mặt hồ hạnh phúc của chúng mình</p>
                </div>

                <div class="mayhen_invitation_map_lake_wrap">
                    <i class="mayhen_invitation_map_clouds mayhen_invitation_decor" aria-hidden="true"></i>
                    <div class="mayhen_invitation_map_oval">
                        <div class="mayhen_invitation_map_oval_inner">
                            <iframe
                                class="mayhen_invitation_map_iframe"
                                src="{{ $mapEmbedUrl }}"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Bản đồ {{ $partyVenue }}"
                            ></iframe>
                        </div>
                    </div>
                    <p class="mayhen_invitation_map_address_text" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>
                    <a
                        href="{{ $mapLinkUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mayhen_invitation_map_link_btn"
                        data-sync-target="event_party_map_link"
                    >
                        <span>Mở Google Maps</span>
                    </a>
                </div>
            </div>

            {{-- 10.5 LỜI CẢM ƠN KẾT THIỆP --}}
            @if(!$isCuratedSeven)
            <footer class="mayhen_invitation_ending">
                <h2 class="mayhen_invitation_ending_title" data-sync-target="thank_you_title">{{ $thankYouTitle }}</h2>
                <p class="mayhen_invitation_ending_text" data-sync-target="thank_you_quote">{{ $thankYouQuote }}</p>
                <p class="mayhen_invitation_ending_couple_name" data-sync-target="couple_names">
                    <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                </p>
                <i class="mayhen_invitation_ripple_divider mayhen_invitation_decor" aria-hidden="true">
                    <svg viewBox="0 0 160 22" fill="none" stroke="currentColor" stroke-linecap="round">
                        <path d="M66 12a14 5 0 0 0 28 0" stroke-width="1.6"></path>
                        <path d="M48 9a32 9 0 0 0 64 0" stroke-width="1.2" opacity=".75"></path>
                        <path d="M24 6a56 13 0 0 0 112 0" stroke-width="1" opacity=".5"></path>
                        <circle cx="80" cy="7" r="2.2" fill="currentColor" stroke="none"></circle>
                    </svg>
                </i>
            </footer>
            @endif

            @if($isCuratedSeven)
            <footer class="mayhen_invitation_ending" style="padding: 32px 16px 48px;">
                <p class="mayhen_invitation_ending_text" data-sync-target="thank_you_quote">{{ $thankYouQuote }}</p>
            </footer>
            @endif
        </section>
    </main>
</div>
@endsection

@section('modals')
<div class="mayhen_invitation_lightbox" data-mayhen-lightbox role="dialog" aria-modal="true" aria-label="Xem ảnh cưới phóng to">
    <div class="mayhen_invitation_lightbox_bar">
        <span data-lightbox-counter>1 / 10</span>
        <button type="button" class="mayhen_invitation_lightbox_close" data-lightbox-close aria-label="Đóng">&times;</button>
    </div>
    <div class="mayhen_invitation_lightbox_stage">
        <button type="button" class="mayhen_invitation_gallery_nav mayhen_invitation_gallery_prev_btn" data-lightbox-prev aria-label="Ảnh trước">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <img class="mayhen_invitation_lightbox_img" src="" alt="Ảnh cưới phóng to" data-lightbox-img>
        <button type="button" class="mayhen_invitation_gallery_nav mayhen_invitation_gallery_next_btn" data-lightbox-next aria-label="Ảnh tiếp theo">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ route('templates.asset', ['slug' => $slug, 'path' => 'script.js']) }}?v=1.0.0"></script>
@endpush
