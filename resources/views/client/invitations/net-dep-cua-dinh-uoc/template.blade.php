@extends('client.layouts.invitation')

@php
    // =========================================================================
    // TEMPLATE: Nét Đẹp Của Đính Ước (net-dep-cua-dinh-uoc)
    // Source Lock: Cocoa Brown Silk Loom (#6B503D), Embroidery Hoop, Dashed Stitch,
    //              Parchment Ivory (#FBF5EE), Brocade Gift Pouch (Túi Gấm)
    // 10 Canonical Sections | BEM mayhen_invitation_* | 100% Local Assets
    // =========================================================================

    $slug = 'net-dep-cua-dinh-uoc';
    $templateName = $template->name ?? 'Nét Đẹp Của Đính Ước';

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

    $rsvpEndpoint = (!$isPreview && isset($invitation) && !empty($invitation->slug))
        ? url("/thiep/{$invitation->slug}/rsvp")
        : '';

    $wishEndpoint = (!$isPreview && isset($invitation) && !empty($invitation->slug))
        ? url("/thiep/{$invitation->slug}/wishes")
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

    // Chuẩn hóa $extraData
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
    $groomName    = trim((string) ($wedding->groom_name ?? ($extraData['groom_name'] ?? 'Hoàng Bảo'))) ?: 'Hoàng Bảo';
    $brideName    = trim((string) ($wedding->bride_name ?? ($extraData['bride_name'] ?? 'Ánh Hồng'))) ?: 'Ánh Hồng';
    $ceremonyType = trim((string) ($extraData['ceremony_type'] ?? 'Thư Mời Tiệc Cưới')) ?: 'Thư Mời Tiệc Cưới';
    $slogan       = trim((string) ($extraData['slogan'] ?? 'Hôn nhân là chuyện cả đời, yêu người vừa ý, cưới người mình thương...'));

    $groomInitial = mb_strtoupper(mb_substr($groomName, 0, 1));
    $brideInitial = mb_strtoupper(mb_substr($brideName, 0, 1));

    $guestInvitePhrase = 'Kính mời';
    $guestName = 'Bạn và Gia Đình';
    if (isset($guest) && !empty($guest->name)) {
        $guestInvitePhrase = method_exists($guest, 'displayInvitePhrase') ? $guest->displayInvitePhrase('Kính mời') : 'Kính mời';
        $guestName = method_exists($guest, 'displayRecipientName') ? $guest->displayRecipientName() : trim((string) $guest->name);
    } elseif (!empty($extraData['guest_name'])) {
        $guestName = (string) $extraData['guest_name'];
    }

    // 2. Ngày giờ cưới & Đếm ngược (Asia/Ho_Chi_Minh)
    $weddingDate = !empty($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date, 'Asia/Ho_Chi_Minh')
        : \Carbon\Carbon::create(2027, 10, 24, 10, 0, 0, 'Asia/Ho_Chi_Minh');

    $weddingDay        = $weddingDate->format('d');
    $weddingMonth      = $weddingDate->format('m');
    $weddingMonthInt   = (int) $weddingDate->format('n');
    $weddingYear       = $weddingDate->format('Y');
    $weddingDateDigits = $weddingDate->format('d . m . Y');
    $weddingDateCompact = $weddingDate->format('d.m.Y');

    $weekdayMap = [
        0 => 'Chủ Nhật',
        1 => 'Thứ Hai',
        2 => 'Thứ Ba',
        3 => 'Thứ Tư',
        4 => 'Thứ Năm',
        5 => 'Thứ Sáu',
        6 => 'Thứ Bảy',
    ];
    $monthNameMap = [
        1 => 'Tháng Một', 2 => 'Tháng Hai', 3 => 'Tháng Ba', 4 => 'Tháng Tư',
        5 => 'Tháng Năm', 6 => 'Tháng Sáu', 7 => 'Tháng Bảy', 8 => 'Tháng Tám',
        9 => 'Tháng Chín', 10 => 'Tháng Mười', 11 => 'Tháng Mười Một', 12 => 'Tháng Mười Hai',
    ];
    $weddingWeekday    = $weekdayMap[$weddingDate->dayOfWeek] ?? 'Chủ Nhật';
    $weddingMonthLabel = $monthNameMap[$weddingMonthInt] ?? "Tháng {$weddingMonth}";
    $weddingLunarDate  = trim((string) ($extraData['wedding_lunar_date'] ?? '(Tức Ngày 25 Tháng 09 Năm Đinh Mùi)'));

    $partyTime     = trim((string) ($extraData['party_time'] ?? '10:00')) ?: '10:00';
    $countdownTime = trim((string) ($extraData['event_target_time'] ?? $partyTime)) ?: '10:00';
    $timeParts     = explode(':', $countdownTime . ':00');
    $cdHour        = str_pad((string) ((int) ($timeParts[0] ?? 10)), 2, '0', STR_PAD_LEFT);
    $cdMin         = str_pad((string) ((int) ($timeParts[1] ?? 0)), 2, '0', STR_PAD_LEFT);
    $targetIso     = sprintf('%s-%s-%sT%s:%s:00+07:00', $weddingYear, $weddingMonth, $weddingDay, $cdHour, $cdMin);

    $partyTimeParts = explode(':', $partyTime . ':00');
    $partyTimeHour  = str_pad((string) ((int) ($partyTimeParts[0] ?? 10)), 2, '0', STR_PAD_LEFT);
    $partyTimeMin   = str_pad((string) ((int) ($partyTimeParts[1] ?? 0)), 2, '0', STR_PAD_LEFT);
    $partyTimeShort = "{$partyTimeHour}h{$partyTimeMin}";

    // Lịch tháng server-side (Monday = 0 .. Sunday = 6)
    $firstOfMonth   = $weddingDate->copy()->startOfMonth();
    $daysInMonth    = $weddingDate->daysInMonth;
    $leadingBlanks  = ($firstOfMonth->dayOfWeek + 6) % 7;
    $selectedDayInt = (int) $weddingDate->format('j');

    // Google Calendar Link động
    $partyVenue        = trim((string) ($extraData['party_venue'] ?? ($wedding->venue_name ?? 'Adora Center — Sảnh Agate (Tầng 2)')));
    $eventPartyAddress = trim((string) ($extraData['event_party_address'] ?? ($wedding->venue_address ?? '431 Hoàng Văn Thụ, P. Tân Sơn Nhất, TP. Hồ Chí Minh')));
    $gcalStart         = \Carbon\Carbon::create((int) $weddingYear, $weddingMonthInt, $selectedDayInt, (int) $cdHour, (int) $cdMin, 0, 'Asia/Ho_Chi_Minh')->utc();
    $gcalEnd           = $gcalStart->copy()->addHours(4);
    $googleCalendarUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . rawurlencode("Tiệc cưới {$groomName} & {$brideName}")
        . '&dates=' . $gcalStart->format('Ymd\THis\Z') . '/' . $gcalEnd->format('Ymd\THis\Z')
        . '&location=' . rawurlencode(trim($partyVenue . ', ' . $eventPartyAddress, ', '));

    // 3. Sự kiện & Bản đồ
    $ceremonyTime         = trim((string) ($extraData['ceremony_time'] ?? '08:00'));
    $ceremonyAddress      = trim((string) ($extraData['ceremony_address'] ?? 'Tư gia Nhà Gái — Số 12 Lê Văn Sỹ, P. Phú Nhuận, TP. Hồ Chí Minh'));
    $ceremonyGroomTime    = trim((string) ($extraData['ceremony_groom_time'] ?? '09:30'));
    $ceremonyGroomAddress = trim((string) ($extraData['ceremony_groom_address'] ?? 'Tư gia Nhà Trai — Quận 3, TP. Hồ Chí Minh'));

    $rawMapLink = trim((string) ($extraData['event_party_map_link'] ?? ''));
    $mapQuery   = rawurlencode(trim($partyVenue . ' ' . $eventPartyAddress));
    $mapLinkUrl = preg_match('#^https?://#i', $rawMapLink)
        ? $rawMapLink
        : "https://maps.google.com/?q={$mapQuery}";
    $mapEmbedUrl = "https://maps.google.com/maps?q={$mapQuery}&t=&z=15&ie=UTF8&iwloc=&output=embed";

    // 4. Song thân & Đôi uyên ương
    $groomFather      = trim((string) ($extraData['groom_father'] ?? 'Ông Phan Phong Phú'));
    $groomMother      = trim((string) ($extraData['groom_mother'] ?? 'Bà Nguyễn Thị Lê Na'));
    $groomParentsAddr = trim((string) ($extraData['groom_parents_address'] ?? 'Quận 3, TP. Hồ Chí Minh'));
    $brideFather      = trim((string) ($extraData['bride_father'] ?? 'Ông Phạm Trọng Toàn'));
    $brideMother      = trim((string) ($extraData['bride_mother'] ?? 'Bà Nguyễn Thị Phương'));
    $brideParentsAddr = trim((string) ($extraData['bride_parents_address'] ?? 'Số 12 Lê Văn Sỹ, P. Phú Nhuận, TP. Hồ Chí Minh'));
    $groomBio         = trim((string) ($extraData['groom_bio'] ?? 'Chàng trai điềm đạm, ấm áp và luôn là điểm tựa dịu dàng trên hành trình chung đôi.'));
    $brideBio         = trim((string) ($extraData['bride_bio'] ?? 'Cô gái dịu dàng, nụ cười trong trẻo và là mảnh ghép ngọt ngào nhất của tổ ấm nhỏ.'));

    // 5. Chuyện tình yêu
    $storyTitle    = trim((string) ($extraData['story_title'] ?? 'Chuyện Tình Yêu'));
    $storySubtitle = trim((string) ($extraData['story_subtitle'] ?? 'Lần đầu gặp gỡ'));
    $coupleQuote   = trim((string) ($extraData['couple_quote'] ?? 'Một buổi chiều mưa ở Sài Gòn, hai đứa tình cờ trú chung mái hiên nhỏ. Một câu chào vu vơ, một nụ cười ngại ngùng — vậy mà thành duyên.'));
    $storyContent  = trim((string) ($extraData['story_content'] ?? 'Như trái xanh đủ ngày sẽ chín, tình yêu của chúng mình cũng vậy — sau bao lần hẹn hò và sẻ chia, yêu nhau đủ rồi thì mình về chung một nhà thôi.'));

    if (!empty($personaMessage) && is_array($personaMessage)) {
        $storyTitle    = $personaMessage['title'] ?? $storyTitle;
        $storySubtitle = $personaMessage['subtitle'] ?? $storySubtitle;
        $storyContent  = $personaMessage['content'] ?? $storyContent;
    }

    // 6. Lịch trình (Timeline)
    $timelineDefault = [
        ['time' => '10:00', 'title' => 'Ăn hỏi & Đón khách'],
        ['time' => '11:00', 'title' => 'Nghi lễ thành hôn'],
        ['time' => '12:00', 'title' => 'Nhập tiệc & Chung vui'],
    ];
    $timelineItems = is_array($extraData['timeline'] ?? null) && count($extraData['timeline']) > 0
        ? array_slice(array_values($extraData['timeline']), 0, 6)
        : $timelineDefault;

    // 7. Dresscode
    $dresscodeColorsRaw = trim((string) ($extraData['dresscode_colors'] ?? '#6B503D,#ECDBD4,#F1F3F4,#A6A6A6'));
    $dresscodeNote      = trim((string) ($extraData['dresscode_note'] ?? 'Chúng mình rất mong quý khách chọn trang phục theo các tông màu dưới đây để cùng hoà vào không khí buổi tiệc.'));
    $dresscodePalette   = array_values(array_filter(array_map('trim', explode(',', $dresscodeColorsRaw)), fn ($c) => preg_match('/^#[0-9A-Fa-f]{3,6}$/', $c)));
    if (empty($dresscodePalette)) {
        $dresscodePalette = ['#6B503D', '#ECDBD4', '#F1F3F4', '#A6A6A6'];
    }
    $colorNameMap = [
        '#6b503d' => 'Nâu Trầm',
        '#ecdbd4' => 'Hồng Phấn',
        '#f1f3f4' => 'Trắng Khói',
        '#a6a6a6' => 'Xám Bạc',
        '#fbf5ee' => 'Kem Ngà',
        '#de8d13' => 'Hổ Phách',
        '#ffffff' => 'Trắng',
        '#000000' => 'Đen',
    ];

    // 8. Ảnh địa phương 100%
    $openingPhoto  = $resolveAsset('images/opening-couple.webp', $wedding->cover_photo_url ?? null);
    $heroPhoto     = $resolveAsset('images/hero-couple.webp', $wedding->cover_photo_url ?? null);
    $groomPhoto    = $resolveAsset('images/groom-portrait.webp', $extraData['groom_photo_url'] ?? null);
    $bridePhoto    = $resolveAsset('images/bride-portrait.webp', $extraData['bride_photo_url'] ?? null);
    $triptych1     = $resolveAsset('images/triptych-1.webp');
    $triptych2     = $resolveAsset('images/triptych-2.webp');
    $triptych3     = $resolveAsset('images/triptych-3.webp');
    $saveDatePhoto = $resolveAsset('images/save-date.webp', $extraData['save_date_photo_url'] ?? null);
    $storyPhoto2   = $resolveAsset('images/story-2.webp');
    $storyPhoto3   = $resolveAsset('images/story-3.webp');
    $endingPhoto   = $resolveAsset('images/ending-couple.webp', $extraData['save_date_photo_url'] ?? null);

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
    $rawGallery = is_array($extraData['gallery'] ?? null) ? array_values($extraData['gallery']) : [];
    $galleryUrls = [];
    for ($i = 0; $i < 10; $i++) {
        $galleryUrls[] = $resolveAsset($defaultGalleryFiles[$i], $rawGallery[$i] ?? null);
    }

    // 9. Mừng cưới & VietQR
    $groomBankName    = trim((string) ($extraData['groom_bank_name'] ?? 'Techcombank'));
    $groomBankAccount = trim((string) ($extraData['groom_bank_account'] ?? '19036868990011'));
    $groomAccountName = trim((string) ($extraData['groom_account_name'] ?? 'PHAN HOANG BAO'));
    $brideBankName    = trim((string) ($extraData['bride_bank_name'] ?? 'MB Bank'));
    $brideBankAccount = trim((string) ($extraData['bride_bank_account'] ?? '098877665544'));
    $brideAccountName = trim((string) ($extraData['bride_account_name'] ?? 'PHAM ANH HONG'));

    $qrGroomResolved = \App\Services\VietQrService::resolveQrImage(
        $extraData['qr_groom_image'] ?? null,
        $groomBankName,
        $groomBankAccount,
        $groomAccountName,
        "Mung cuoi {$groomName} va {$brideName}"
    );
    $qrGroomImage = $resolveAsset('images/qr-groom.webp', $qrGroomResolved);

    $qrBrideResolved = \App\Services\VietQrService::resolveQrImage(
        $extraData['qr_bride_image'] ?? null,
        $brideBankName,
        $brideBankAccount,
        $brideAccountName,
        "Mung cuoi {$groomName} va {$brideName}"
    );
    $qrBrideImage = $resolveAsset('images/qr-bride.webp', $qrBrideResolved);

    // 10. Lời cảm ơn, Nhạc nền, Lời chúc & Chữ ký
    $thankYouTitle = trim((string) ($extraData['thank_you_title'] ?? 'Thank you!'));
    $thankYouQuote = trim((string) ($extraData['thank_you_quote'] ?? 'Sự hiện diện của bạn là niềm vinh hạnh lớn nhất trong ngày trọng đại của gia đình chúng mình. Chân thành cảm ơn!'));

    $musicUrl = $resolveAsset('wedding-song.mp3', $wedding->music_url ?? ($extraData['music_url'] ?? null));

    $wishesList = isset($wishes) && $wishes instanceof \Illuminate\Support\Collection
        ? $wishes
        : collect([]);

    $signaturesList = isset($signatures) && $signatures instanceof \Illuminate\Support\Collection
        ? $signatures
        : collect([]);
@endphp

@section('title', "{$groomName} & {$brideName} — {$templateName}")

@push('styles')
    <link rel="stylesheet" href="{{ "/mau-thiep/{$slug}/assets/styles.css" }}?v={{ filemtime(resource_path("views/client/invitations/{$slug}/styles.css")) }}">
@endpush

@section('content')
<div class="mayhen_invitation_root {{ $isEmbed ? 'mayhen_invitation_root--embed' : '' }} {{ $autoOpened ? 'mayhen_invitation_root--opened' : '' }}"
     data-mayhen-root
     data-template-slug="{{ $slug }}"
     data-is-embed="{{ $isEmbed ? '1' : '0' }}"
     data-is-preview="{{ $isPreview ? '1' : '0' }}"
     data-is-curated="{{ $isCuratedSeven ? '1' : '0' }}"
     data-target-iso="{{ $targetIso }}"
     data-rsvp-endpoint="{{ $rsvpEndpoint }}"
     data-wish-endpoint="{{ $wishEndpoint }}"
     data-signature-endpoint="{{ $signatureEndpoint }}"
     data-csrf-token="{{ csrf_token() }}">

    {{-- Desktop Blurred Backdrop --}}
    <div class="mayhen_invitation_desktop_backdrop" data-backdrop-src="{{ $heroPhoto }}" aria-hidden="true"></div>

    {{-- Audio Element (Mandatory Live Sync Target) --}}
    <audio class="mayhen_invitation_audio" data-mayhen-audio data-sync-target="music_url" preload="none" loop>
        <source src="{{ $musicUrl }}" type="audio/mpeg">
    </audio>

    {{-- Falling Petals Layer (Pure CSS) --}}
    @if(!$isEmbed)
    <div class="mayhen_invitation_petals" aria-hidden="true">
        @foreach([
            ['x' => '8%',  'size' => '10px', 'dur' => '13s', 'delay' => '0s'],
            ['x' => '22%', 'size' => '12px', 'dur' => '15s', 'delay' => '-4s'],
            ['x' => '38%', 'size' => '9px',  'dur' => '12s', 'delay' => '-8s'],
            ['x' => '55%', 'size' => '11px', 'dur' => '14s', 'delay' => '-2s'],
            ['x' => '72%', 'size' => '10px', 'dur' => '16s', 'delay' => '-6s'],
            ['x' => '88%', 'size' => '12px', 'dur' => '13.5s', 'delay' => '-10s'],
        ] as $p)
            <span class="mayhen_invitation_petal" style="--petal-x: {{ $p['x'] }}; --petal-size: {{ $p['size'] }}; --petal-dur: {{ $p['dur'] }}; --petal-delay: {{ $p['delay'] }};"></span>
        @endforeach
    </div>
    @endif

    {{-- =====================================================================
         OPENING CURTAIN: SILK LOOM + WOODEN EMBROIDERY HOOP
         ===================================================================== --}}
    @if(!$autoOpened)
    <div class="mayhen_invitation_opening" data-opening-curtain role="dialog" aria-label="Mở thiệp cưới {{ $groomName }} và {{ $brideName }}">
        <div class="mayhen_invitation_opening_loom" aria-hidden="true">
            @for($s = 0; $s < 12; $s++)
                @php $distFromCenter = $s < 6 ? (5 - $s) : ($s - 6); @endphp
                <i class="mayhen_invitation_opening_strip" style="--strip-i: {{ $s }}; --strip-k: {{ $distFromCenter }};"></i>
            @endfor
        </div>
        <div class="mayhen_invitation_opening_frame" aria-hidden="true"></div>
        <div class="mayhen_invitation_opening_twig mayhen_invitation_opening_twig--left" aria-hidden="true"></div>
        <div class="mayhen_invitation_opening_twig mayhen_invitation_opening_twig--right" aria-hidden="true"></div>

        <div class="mayhen_invitation_opening_card">
            <p class="mayhen_invitation_opening_label" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>

            <div class="mayhen_invitation_hoop">
                <div class="mayhen_invitation_hoop_photo_wrap">
                    <img class="mayhen_invitation_hoop_photo"
                         src="{{ $openingPhoto }}"
                         alt="{{ $groomName }} &amp; {{ $brideName }}"
                         data-sync-target="cover_photo_url">
                </div>
                <svg class="mayhen_invitation_hoop_stitch" viewBox="0 0 100 100" aria-hidden="true">
                    <circle cx="50" cy="50" r="48"></circle>
                </svg>
                <span class="mayhen_invitation_hoop_wood" aria-hidden="true"></span>
                <span class="mayhen_invitation_hoop_clasp" aria-hidden="true"></span>
            </div>

            <div class="mayhen_invitation_opening_names">
                <span data-sync-target="groom_name">{{ $groomName }}</span>
                <span class="mayhen_invitation_opening_amp" aria-hidden="true">&amp;</span>
                <span data-sync-target="bride_name">{{ $brideName }}</span>
            </div>

            <p class="mayhen_invitation_opening_date" data-sync-target="wedding_date">{{ $weddingDateDigits }}</p>

            <button type="button" class="mayhen_invitation_open_btn" data-open-card-btn>Mở Thiệp</button>

            <div class="mayhen_invitation_opening_guest">
                <p class="mayhen_invitation_opening_guest_label">{{ $guestInvitePhrase }}</p>
                <p class="mayhen_invitation_opening_guest_name">{{ $guestName }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- =====================================================================
         MAIN INVITATION CARD SHELL (10 CANONICAL SECTIONS)
         ===================================================================== --}}
    <main class="mayhen_invitation_shell card-wrapper">

        {{-- SECTION 1: HERO COVER --}}
        <section class="mayhen_invitation_section mayhen_invitation_hero" id="section-hero" data-mayhen-section="hero">
            <div class="mayhen_invitation_hero_frame">
                <img class="mayhen_invitation_hero_photo"
                     src="{{ $heroPhoto }}"
                     alt="Ảnh cưới {{ $groomName }} và {{ $brideName }}"
                     data-sync-target="cover_photo_url"
                     loading="eager">
                <div class="mayhen_invitation_hero_scrim_top" aria-hidden="true"></div>
                <div class="mayhen_invitation_hero_scrim_bottom" aria-hidden="true"></div>
                <div class="mayhen_invitation_hero_twig mayhen_invitation_hero_twig--left" aria-hidden="true"></div>
                <div class="mayhen_invitation_hero_twig mayhen_invitation_hero_twig--right" aria-hidden="true"></div>

                <div class="mayhen_invitation_hero_names">
                    <h1 class="mayhen_invitation_hero_couple_line">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <span class="mayhen_invitation_hero_amp">&amp;</span>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </h1>
                </div>

                <div class="mayhen_invitation_hero_bottom">
                    <p class="mayhen_invitation_hero_type" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>
                    <div class="mayhen_invitation_hero_date_box">
                        <p class="mayhen_invitation_hero_time_row">
                            <span data-sync-target="wedding_weekday">{{ $weddingWeekday }}</span>
                            <span> - </span>
                            <span data-sync-target="party_time">{{ $partyTimeShort }}</span>
                        </p>
                        <p class="mayhen_invitation_hero_date_digits" data-sync-target="wedding_date">{{ $weddingDateCompact }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- SECTION 2: PARENTS & COUPLE --}}
        <section class="mayhen_invitation_section" id="section-parents" data-mayhen-section="parents">
            <p class="mayhen_invitation_quote mayhen_invitation_reveal" data-sync-target="slogan">“{{ $slogan }}”</p>

            <div class="mayhen_invitation_families mayhen_invitation_reveal">
                <div class="mayhen_invitation_family_col">
                    <h3 class="mayhen_invitation_family_title">Nhà Trai</h3>
                    <p class="mayhen_invitation_family_names">
                        <span data-sync-target="groom_father">{{ $groomFather }}</span><br>
                        <span data-sync-target="groom_mother">{{ $groomMother }}</span>
                    </p>
                    <p class="mayhen_invitation_family_addr" data-sync-target="groom_parents_address">{{ $groomParentsAddr }}</p>
                </div>

                <div class="mayhen_invitation_family_divider" aria-hidden="true"></div>

                <div class="mayhen_invitation_family_col">
                    <h3 class="mayhen_invitation_family_title">Nhà Gái</h3>
                    <p class="mayhen_invitation_family_names">
                        <span data-sync-target="bride_father">{{ $brideFather }}</span><br>
                        <span data-sync-target="bride_mother">{{ $brideMother }}</span>
                    </p>
                    <p class="mayhen_invitation_family_addr" data-sync-target="bride_parents_address">{{ $brideParentsAddr }}</p>
                </div>
            </div>

            <span class="mayhen_invitation_heart_icon mayhen_invitation_reveal" aria-hidden="true">
                <svg viewBox="0 0 24 21"><path d="M12 20.4S1.6 14 1.6 6.9A5.4 5.4 0 0 1 12 4.6a5.4 5.4 0 0 1 10.4 2.3C22.4 14 12 20.4 12 20.4z"/></svg>
            </span>

            <div class="mayhen_invitation_couple_grid">
                <article class="mayhen_invitation_person_card mayhen_invitation_reveal">
                    <p class="mayhen_invitation_person_role">Chú Rể</p>
                    <h3 class="mayhen_invitation_person_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                    <div class="mayhen_invitation_arch_frame">
                        <img class="mayhen_invitation_arch_photo"
                             src="{{ $groomPhoto }}"
                             alt="Chú rể {{ $groomName }}"
                             data-sync-target="groom_photo_url"
                             loading="eager">
                    </div>
                    <p class="mayhen_invitation_person_bio" data-sync-target="groom_bio">{{ $groomBio }}</p>
                </article>

                <article class="mayhen_invitation_person_card mayhen_invitation_reveal">
                    <p class="mayhen_invitation_person_role">Cô Dâu</p>
                    <h3 class="mayhen_invitation_person_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                    <div class="mayhen_invitation_arch_frame">
                        <img class="mayhen_invitation_arch_photo"
                             src="{{ $bridePhoto }}"
                             alt="Cô dâu {{ $brideName }}"
                             data-sync-target="bride_photo_url"
                             loading="eager">
                    </div>
                    <p class="mayhen_invitation_person_bio" data-sync-target="bride_bio">{{ $brideBio }}</p>
                </article>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- SECTION 3: STORY & INVITATION LETTER --}}
        <section class="mayhen_invitation_section mayhen_invitation_section--ivory" id="section-story" data-mayhen-section="story">
            <h2 class="mayhen_invitation_heading mayhen_invitation_reveal">Thư Mời</h2>

            <div class="mayhen_invitation_ribbon_wrap mayhen_invitation_reveal">
                <p class="mayhen_invitation_ribbon_kicker">Trân trọng kính mời</p>
                <div class="mayhen_invitation_ribbon">
                    <svg class="mayhen_invitation_ribbon_heart" viewBox="0 0 24 21" aria-hidden="true"><path d="M12 20.4S1.6 14 1.6 6.9A5.4 5.4 0 0 1 12 4.6a5.4 5.4 0 0 1 10.4 2.3C22.4 14 12 20.4 12 20.4z"/></svg>
                    <span class="mayhen_invitation_ribbon_guest">{{ $guestName }}</span>
                    <svg class="mayhen_invitation_ribbon_heart" viewBox="0 0 24 21" aria-hidden="true"><path d="M12 20.4S1.6 14 1.6 6.9A5.4 5.4 0 0 1 12 4.6a5.4 5.4 0 0 1 10.4 2.3C22.4 14 12 20.4 12 20.4z"/></svg>
                </div>
            </div>

            <p class="mayhen_invitation_invite_lead mayhen_invitation_reveal">Tham dự lễ cưới của</p>
            <p class="mayhen_invitation_invite_couple mayhen_invitation_reveal">
                <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
            </p>

            {{-- 3-Photo Arched Triptych --}}
            <div class="mayhen_invitation_triptych mayhen_invitation_reveal">
                <div class="mayhen_invitation_triptych_item">
                    <img class="mayhen_invitation_triptych_photo" src="{{ $triptych1 }}" alt="Khoảnh khắc cưới 1" loading="eager">
                </div>
                <div class="mayhen_invitation_triptych_item mayhen_invitation_triptych_item--center">
                    <img class="mayhen_invitation_triptych_photo" src="{{ $triptych2 }}" alt="Khoảnh khắc cưới 2" loading="eager">
                </div>
                <div class="mayhen_invitation_triptych_item">
                    <img class="mayhen_invitation_triptych_photo" src="{{ $triptych3 }}" alt="Khoảnh khắc cưới 3" loading="eager">
                </div>
            </div>

            <div class="mayhen_invitation_party_announce mayhen_invitation_reveal">
                <p class="mayhen_invitation_party_title" data-sync-target="ceremony_type">Tiệc Mừng Lễ Vu Quy</p>
                <p class="mayhen_invitation_party_time">Vào lúc <strong data-sync-target="party_time">{{ $partyTimeShort }}</strong></p>
                <p class="mayhen_invitation_party_weekday" data-sync-target="wedding_weekday">{{ $weddingWeekday }}</p>

                <div class="mayhen_invitation_date_trio">
                    <span class="mayhen_invitation_date_trio_side">Tháng <span data-sync-target="wedding_month">{{ $weddingMonth }}</span></span>
                    <span class="mayhen_invitation_date_trio_day" data-sync-target="wedding_day">{{ $weddingDay }}</span>
                    <span class="mayhen_invitation_date_trio_side" data-sync-target="wedding_year">{{ $weddingYear }}</span>
                </div>

                <p class="mayhen_invitation_lunar" data-sync-target="wedding_lunar_date">{{ $weddingLunarDate }}</p>

                <a class="mayhen_invitation_pill_btn" href="#section-events">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>
                    <span>Xem vị trí</span>
                </a>
            </div>

            {{-- Love Story Chapters --}}
            <div class="mayhen_invitation_story_block">
                <p class="mayhen_invitation_script_sub mayhen_invitation_reveal">Love Story</p>
                <h2 class="mayhen_invitation_eyebrow mayhen_invitation_reveal" data-sync-target="story_title">{{ $storyTitle }}</h2>
                <div class="mayhen_invitation_stitch_line" aria-hidden="true">
                    <svg viewBox="0 0 24 21"><path d="M12 20.4S1.6 14 1.6 6.9A5.4 5.4 0 0 1 12 4.6a5.4 5.4 0 0 1 10.4 2.3C22.4 14 12 20.4 12 20.4z"/></svg>
                </div>

                <div class="mayhen_invitation_story_list">
                    <article class="mayhen_invitation_story_card mayhen_invitation_reveal">
                        <div class="mayhen_invitation_story_media">
                            <img class="mayhen_invitation_story_photo"
                                 src="{{ $saveDatePhoto }}"
                                 alt="{{ $storySubtitle }}"
                                 data-sync-target="save_date_photo_url"
                                 loading="eager">
                        </div>
                        <p class="mayhen_invitation_story_date">14 . 02 . 2020</p>
                        <h3 class="mayhen_invitation_story_title" data-sync-target="story_subtitle">{{ $storySubtitle }}</h3>
                        <p class="mayhen_invitation_story_text" data-sync-target="couple_quote">{{ $coupleQuote }}</p>
                    </article>

                    <article class="mayhen_invitation_story_card mayhen_invitation_reveal">
                        <div class="mayhen_invitation_story_media">
                            <img class="mayhen_invitation_story_photo" src="{{ $storyPhoto2 }}" alt="Nhận lời yêu" loading="eager">
                        </div>
                        <p class="mayhen_invitation_story_date">20 . 10 . 2021</p>
                        <h3 class="mayhen_invitation_story_title">Nhận lời yêu</h3>
                        <p class="mayhen_invitation_story_text">Sau bao lần hẹn hò, anh lấy hết can đảm ngỏ lời. Em gật đầu, và từ hôm ấy mọi con đường đều có hai người cùng đi.</p>
                    </article>

                    <article class="mayhen_invitation_story_card mayhen_invitation_reveal">
                        <div class="mayhen_invitation_story_media">
                            <img class="mayhen_invitation_story_photo" src="{{ $storyPhoto3 }}" alt="Về chung một nhà" loading="eager">
                        </div>
                        <p class="mayhen_invitation_story_date" data-sync-target="wedding_date">{{ $weddingDateDigits }}</p>
                        <h3 class="mayhen_invitation_story_title">Về chung một nhà</h3>
                        <p class="mayhen_invitation_story_text" data-sync-target="story_content">{{ $storyContent }}</p>
                    </article>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- SECTION 4: COUNTDOWN & CALENDAR --}}
        <section class="mayhen_invitation_section" id="section-countdown" data-mayhen-section="countdown_calendar">
            <div class="mayhen_invitation_cal_head mayhen_invitation_reveal">
                <h2 class="mayhen_invitation_cal_month" data-cal-month-label>{{ $weddingMonthLabel }}</h2>
                <span class="mayhen_invitation_cal_year" data-cal-year-label>{{ $weddingYear }}</span>
            </div>

            <div class="mayhen_invitation_cal_card mayhen_invitation_reveal"
                 data-wedding-calendar
                 data-year="{{ $weddingYear }}"
                 data-month="{{ $weddingMonthInt }}"
                 data-day="{{ $selectedDayInt }}">
                <div class="mayhen_invitation_cal_weekdays">
                    <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                </div>
                <div class="mayhen_invitation_cal_grid" data-calendar-grid>
                    @for($b = 0; $b < $leadingBlanks; $b++)
                        <span class="mayhen_invitation_cal_cell mayhen_invitation_cal_cell--empty"></span>
                    @endfor
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        <span class="mayhen_invitation_cal_cell {{ $d === $selectedDayInt ? 'mayhen_invitation_cal_cell--active' : '' }}">{{ $d }}</span>
                    @endfor
                </div>
            </div>

            <a class="mayhen_invitation_pill_btn mayhen_invitation_reveal"
               href="{{ $googleCalendarUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               data-gcal-link>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 7H5v11h14V9z"/></svg>
                <span>Thêm vào lịch</span>
            </a>

            <div class="mayhen_invitation_countdown_wrap mayhen_invitation_reveal">
                <h2 class="mayhen_invitation_heading">Cùng đếm ngược</h2>
                <p class="mayhen_invitation_desc">Từng ngày trôi qua, chúng mình càng mong tới ngày chung đôi</p>

                <div class="mayhen_invitation_countdown_grid" data-countdown-root data-target-iso="{{ $targetIso }}">
                    <div class="mayhen_invitation_cd_item">
                        <div class="mayhen_invitation_cd_box" data-cd-days>00</div>
                        <span class="mayhen_invitation_cd_label">Ngày</span>
                    </div>
                    <div class="mayhen_invitation_cd_item">
                        <div class="mayhen_invitation_cd_box" data-cd-hours>00</div>
                        <span class="mayhen_invitation_cd_label">Giờ</span>
                    </div>
                    <div class="mayhen_invitation_cd_item">
                        <div class="mayhen_invitation_cd_box" data-cd-minutes>00</div>
                        <span class="mayhen_invitation_cd_label">Phút</span>
                    </div>
                    <div class="mayhen_invitation_cd_item">
                        <div class="mayhen_invitation_cd_box" data-cd-seconds>00</div>
                        <span class="mayhen_invitation_cd_label">Giây</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- SECTION 5: EVENTS & MAPS --}}
        <section class="mayhen_invitation_section mayhen_invitation_section--ivory" id="section-events" data-mayhen-section="events">
            <p class="mayhen_invitation_eyebrow mayhen_invitation_reveal">Chương trình</p>
            <h2 class="mayhen_invitation_heading mayhen_invitation_reveal">Thông Tin Lễ Cưới</h2>

            <div class="mayhen_invitation_events_list">
                <article class="mayhen_invitation_event_card mayhen_invitation_reveal">
                    <span class="mayhen_invitation_event_badge" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="9" cy="14" r="5"/><circle cx="15" cy="14" r="5"/><path d="M12 5l2-2 2 2-2 2z"/></svg>
                    </span>
                    <h3 class="mayhen_invitation_event_name">Lễ Vu Quy</h3>
                    <p class="mayhen_invitation_event_time">
                        <span data-sync-target="ceremony_time">{{ $ceremonyTime }}</span> · <span data-sync-target="wedding_date">{{ $weddingDateCompact }}</span>
                    </p>
                    <p class="mayhen_invitation_event_venue">Tư gia Nhà Gái</p>
                    <p class="mayhen_invitation_event_addr" data-sync-target="ceremony_address">{{ $ceremonyAddress }}</p>
                </article>

                <article class="mayhen_invitation_event_card mayhen_invitation_reveal">
                    <span class="mayhen_invitation_event_badge" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 11.5L12 4l9 7.5M5.5 10v10h13V10M10 20v-5.5h4V20"/></svg>
                    </span>
                    <h3 class="mayhen_invitation_event_name">Lễ Thành Hôn</h3>
                    <p class="mayhen_invitation_event_time">
                        <span data-sync-target="ceremony_groom_time">{{ $ceremonyGroomTime }}</span> · <span data-sync-target="wedding_date">{{ $weddingDateCompact }}</span>
                    </p>
                    <p class="mayhen_invitation_event_venue">Tư gia Nhà Trai</p>
                    <p class="mayhen_invitation_event_addr" data-sync-target="ceremony_groom_address">{{ $ceremonyGroomAddress }}</p>
                </article>

                <article class="mayhen_invitation_event_card mayhen_invitation_reveal">
                    <span class="mayhen_invitation_event_badge" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M5 3v7a2 2 0 0 0 2 2v9M9 3v7a2 2 0 0 1-2 2M7 3v6M18 21V3c-2.4 1.3-3.8 3.8-3.8 7.6 0 3 1.4 4.4 3.8 4.4"/></svg>
                    </span>
                    <h3 class="mayhen_invitation_event_name">Tiệc Mừng Chung Vui</h3>
                    <p class="mayhen_invitation_event_time">
                        <span data-sync-target="wedding_weekday">{{ $weddingWeekday }}</span> · <span data-sync-target="party_time">{{ $partyTime }}</span> · <span data-sync-target="wedding_date">{{ $weddingDateCompact }}</span>
                    </p>
                    <p class="mayhen_invitation_event_venue" data-sync-target="party_venue">{{ $partyVenue }}</p>
                    <p class="mayhen_invitation_event_addr" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>
                </article>
            </div>

            <div class="mayhen_invitation_map_block mayhen_invitation_reveal">
                <p class="mayhen_invitation_eyebrow">Địa điểm tổ chức</p>
                <h3 class="mayhen_invitation_map_venue" data-sync-target="party_venue">{{ $partyVenue }}</h3>
                <p class="mayhen_invitation_map_addr" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>

                <div class="mayhen_invitation_stitch_line" aria-hidden="true">
                    <svg viewBox="0 0 24 21"><path d="M12 20.4S1.6 14 1.6 6.9A5.4 5.4 0 0 1 12 4.6a5.4 5.4 0 0 1 10.4 2.3C22.4 14 12 20.4 12 20.4z"/></svg>
                </div>

                <div class="mayhen_invitation_map_shell">
                    <span class="mayhen_invitation_map_tag">Vị trí</span>
                    <iframe class="mayhen_invitation_map_frame"
                            src="{{ $mapEmbedUrl }}"
                            title="Bản đồ địa điểm tổ chức tiệc cưới {{ $partyVenue }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen></iframe>
                </div>

                <a class="mayhen_invitation_pill_btn"
                   href="{{ $mapLinkUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   data-sync-target="event_party_map_link">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>
                    <span>Chỉ đường Google Maps</span>
                </a>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- SECTION 6: TIMELINE --}}
        <section class="mayhen_invitation_section" id="section-timeline" data-mayhen-section="timeline">
            <h2 class="mayhen_invitation_heading mayhen_invitation_reveal">Timeline</h2>
            <p class="mayhen_invitation_desc mayhen_invitation_reveal">Lịch trình ngày vui của chúng mình</p>

            <div class="mayhen_invitation_timeline_list" data-sync-target="timeline">
                @foreach($timelineItems as $idx => $item)
                    @php
                        $sideClass = $idx % 2 === 0 ? 'mayhen_invitation_timeline_item--left' : 'mayhen_invitation_timeline_item--right';
                        $tTime = trim((string) ($item['time'] ?? ''));
                        $tTitle = trim((string) ($item['title'] ?? ''));
                    @endphp
                    <div class="mayhen_invitation_timeline_item {{ $sideClass }} mayhen_invitation_reveal">
                        <div class="mayhen_invitation_timeline_content">
                            <p class="mayhen_invitation_timeline_time">{{ $tTime }}</p>
                            <p class="mayhen_invitation_timeline_title">{{ $tTitle }}</p>
                        </div>
                        <span class="mayhen_invitation_timeline_node" aria-hidden="true">
                            @if($idx % 3 === 0)
                                <svg viewBox="0 0 24 24"><circle cx="9" cy="14" r="5"/><circle cx="15" cy="14" r="5"/></svg>
                            @elseif($idx % 3 === 1)
                                <svg viewBox="0 0 24 24"><path d="M12 20s-8-5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 9c0 6-8 11-8 11z"/></svg>
                            @else
                                <svg viewBox="0 0 24 24"><path d="M5 3v7a2 2 0 0 0 2 2v9M9 3v7a2 2 0 0 1-2 2M18 21V3c-2.4 1.3-3.8 3.8-3.8 7.6 0 3 1.4 4.4 3.8 4.4"/></svg>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </section>

        @if(!$isCuratedSeven)
        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- SECTION 7: DRESSCODE --}}
        <section class="mayhen_invitation_section mayhen_invitation_section--ivory" id="section-dresscode" data-mayhen-section="dresscode">
            <p class="mayhen_invitation_eyebrow mayhen_invitation_reveal">Trang phục</p>
            <h2 class="mayhen_invitation_heading mayhen_invitation_reveal">Dress Code</h2>
            <p class="mayhen_invitation_desc mayhen_invitation_reveal" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>

            <div class="mayhen_invitation_dresscode_list mayhen_invitation_reveal" data-sync-target="dresscode_colors" data-dresscode-container>
                @foreach($dresscodePalette as $hex)
                    @php
                        $lowerHex = strtolower($hex);
                        $colorLabel = $colorNameMap[$lowerHex] ?? strtoupper($hex);
                    @endphp
                    <div class="mayhen_invitation_swatch_item">
                        <div class="mayhen_invitation_swatch_frame">
                            <span class="mayhen_invitation_swatch_color" style="background-color: {{ $hex }};"></span>
                            <span class="mayhen_invitation_swatch_heart" aria-hidden="true">
                                <svg viewBox="0 0 24 21"><path d="M12 20.4S1.6 14 1.6 6.9A5.4 5.4 0 0 1 12 4.6a5.4 5.4 0 0 1 10.4 2.3C22.4 14 12 20.4 12 20.4z"/></svg>
                            </span>
                        </div>
                        <span class="mayhen_invitation_swatch_label">{{ $colorLabel }}</span>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- SECTION 8: ALBUM 10 PHOTOS (3D COVERFLOW + LIGHTBOX) --}}
        <section class="mayhen_invitation_section" id="section-album" data-mayhen-section="album">
            <div class="mayhen_invitation_album_header mayhen_invitation_reveal">
                <p class="mayhen_invitation_script_sub">Anniversary</p>
                <h2 class="mayhen_invitation_eyebrow">Album Ảnh Cưới</h2>
                <p class="mayhen_invitation_desc">Những khoảnh khắc chúng mình muốn lưu giữ mãi</p>
            </div>

            <div class="mayhen_invitation_coverflow mayhen_invitation_reveal" data-coverflow>
                @foreach($galleryUrls as $gIdx => $gUrl)
                    <div class="mayhen_invitation_coverflow_slide" data-coverflow-slide="{{ $gIdx }}" role="button" tabindex="0" aria-label="Xem ảnh cưới {{ $gIdx + 1 }}">
                        <img class="mayhen_invitation_coverflow_img"
                             src="{{ $gUrl }}"
                             alt="Album ảnh cưới {{ $groomName }} và {{ $brideName }} - Ảnh {{ $gIdx + 1 }}"
                             data-sync-target="gallery.{{ $gIdx }}"
                             loading="eager"
                             decoding="async">
                    </div>
                @endforeach

                <button type="button" class="mayhen_invitation_coverflow_nav mayhen_invitation_coverflow_nav--prev" data-coverflow-prev aria-label="Ảnh trước">
                    <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <button type="button" class="mayhen_invitation_coverflow_nav mayhen_invitation_coverflow_nav--next" data-coverflow-next aria-label="Ảnh tiếp theo">
                    <svg viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </div>

            <div class="mayhen_invitation_coverflow_dots" data-coverflow-dots>
                @for($dIdx = 0; $dIdx < count($galleryUrls); $dIdx++)
                    <button type="button"
                            class="mayhen_invitation_coverflow_dot {{ $dIdx === 0 ? 'mayhen_invitation_coverflow_dot--active' : '' }}"
                            data-coverflow-dot="{{ $dIdx }}"
                            aria-label="Chuyển tới ảnh {{ $dIdx + 1 }}"></button>
                @endfor
            </div>
        </section>

        @if(!$isCuratedSeven)
        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- SECTION 9: GIFT (BROCADE DRAWSTRING POUCH "TÚI GẤM" + VIETQR) --}}
        <section class="mayhen_invitation_section mayhen_invitation_section--ivory" id="section-gift" data-mayhen-section="gift">
            <h2 class="mayhen_invitation_heading mayhen_invitation_reveal">Mừng Cưới</h2>
            <p class="mayhen_invitation_desc mayhen_invitation_reveal">Thành ý chúc phúc của Quý khách xin được trân trọng ghi nhận qua túi gấm mừng cưới dưới đây.</p>

            <div class="mayhen_invitation_gift_stage mayhen_invitation_reveal" data-gift-stage>
                <button type="button" class="mayhen_invitation_pouch_btn" data-pouch-toggle aria-expanded="false" aria-label="Chạm để mở túi gấm mừng cưới">
                    <svg class="mayhen_invitation_pouch_svg" viewBox="0 0 176 202" aria-hidden="true">
                        <defs>
                            <linearGradient id="mayhenPouchBody" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#7C5A45"/>
                                <stop offset=".55" stop-color="#5E4232"/>
                                <stop offset="1" stop-color="#432E20"/>
                            </linearGradient>
                            <linearGradient id="mayhenPouchNeck" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#957058"/>
                                <stop offset="1" stop-color="#6B4E3B"/>
                            </linearGradient>
                            <pattern id="mayhenBrocade" width="16" height="16" patternUnits="userSpaceOnUse">
                                <g fill="#E6C595" fill-opacity=".42">
                                    <circle cx="8" cy="8" r="1.5"/>
                                    <ellipse cx="8" cy="4.6" rx="1.2" ry="2.2"/>
                                    <ellipse cx="8" cy="11.4" rx="1.2" ry="2.2"/>
                                    <ellipse cx="4.6" cy="8" rx="2.2" ry="1.2"/>
                                    <ellipse cx="11.4" cy="8" rx="2.2" ry="1.2"/>
                                </g>
                            </pattern>
                        </defs>
                        <path d="M60 90C30 108 14 140 18 170C22 194 56 200 88 200C120 200 154 194 158 170C162 140 146 108 116 90Z" fill="url(#mayhenPouchBody)"/>
                        <path d="M60 90C30 108 14 140 18 170C22 194 56 200 88 200C120 200 154 194 158 170C162 140 146 108 116 90Z" fill="url(#mayhenBrocade)"/>
                        <path d="M60 93C52 80 40 68 36 54C48 58 56 52 62 44C70 54 78 48 84 38C88 46 92 46 96 38C100 48 108 54 114 44C120 52 128 58 140 54C136 68 124 80 116 93Z" fill="url(#mayhenPouchNeck)"/>
                        <path d="M60 93C52 80 40 68 36 54C48 58 56 52 62 44C70 54 78 48 84 38C88 46 92 46 96 38C100 48 108 54 114 44C120 52 128 58 140 54C136 68 124 80 116 93Z" fill="url(#mayhenBrocade)"/>
                        <ellipse cx="88" cy="92" rx="31" ry="6.5" fill="none" stroke="#B88A58" stroke-width="5.5"/>
                        <ellipse cx="88" cy="92" rx="31" ry="6.5" fill="none" stroke="#E6C595" stroke-width="2.4" stroke-dasharray="5 3"/>
                        <path d="M83 100C72 110 64 120 58 130M93 100C106 112 114 122 118 136" fill="none" stroke="#D9B27C" stroke-width="2.4" stroke-linecap="round"/>
                        <rect x="53" y="130" width="10" height="6" rx="2" fill="#E2BE8A" stroke="#A67C4E" stroke-width="1"/>
                        <path d="M52 136H64L66 156H50Z" fill="#C99A62"/>
                        <rect x="113" y="136" width="10" height="6" rx="2" fill="#E2BE8A" stroke="#A67C4E" stroke-width="1"/>
                        <path d="M112 142H124L126 162H110Z" fill="#C99A62"/>
                        <circle cx="88" cy="150" r="23" fill="#F6E8D4" stroke="#C9A06E" stroke-width="2.4"/>
                        <circle cx="88" cy="150" r="18" fill="none" stroke="#8A6B54" stroke-width="1.2" stroke-dasharray="2.4 2.2"/>
                        <path d="M88 161.5S77 155 77 147.6A5.6 5.6 0 0 1 88 145a5.6 5.6 0 0 1 11 2.6C99 155 88 161.5 88 161.5z" fill="#8A5A3C"/>
                    </svg>
                    <span class="mayhen_invitation_pouch_hint">
                        <svg viewBox="0 0 24 21" aria-hidden="true"><path d="M12 20.4S1.6 14 1.6 6.9A5.4 5.4 0 0 1 12 4.6a5.4 5.4 0 0 1 10.4 2.3C22.4 14 12 20.4 12 20.4z"/></svg>
                        <span>Chạm để mở túi gấm</span>
                    </span>
                </button>

                <div class="mayhen_invitation_qr_grid">
                    <article class="mayhen_invitation_qr_card">
                        <p class="mayhen_invitation_qr_role">Mừng cưới Chú rể</p>
                        <div class="mayhen_invitation_qr_box">
                            <img class="mayhen_invitation_qr_img"
                                 src="{{ $qrGroomImage }}"
                                 alt="Mã QR mừng cưới chú rể {{ $groomName }}"
                                 data-sync-target="qr_groom_image"
                                 loading="eager">
                        </div>
                        <p class="mayhen_invitation_qr_name" data-sync-target="groom_name">{{ $groomName }}</p>
                        <p class="mayhen_invitation_qr_bank" data-sync-target="groom_bank_name">{{ $groomBankName }}</p>
                        <p class="mayhen_invitation_qr_acc" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</p>
                        <p class="mayhen_invitation_qr_holder" data-sync-target="groom_account_name">{{ $groomAccountName }}</p>
                        <button type="button" class="mayhen_invitation_copy_btn" data-copy-account="{{ $groomBankAccount }}">Sao chép STK</button>
                    </article>

                    <article class="mayhen_invitation_qr_card">
                        <p class="mayhen_invitation_qr_role">Mừng cưới Cô dâu</p>
                        <div class="mayhen_invitation_qr_box">
                            <img class="mayhen_invitation_qr_img"
                                 src="{{ $qrBrideImage }}"
                                 alt="Mã QR mừng cưới cô dâu {{ $brideName }}"
                                 data-sync-target="qr_bride_image"
                                 loading="eager">
                        </div>
                        <p class="mayhen_invitation_qr_name" data-sync-target="bride_name">{{ $brideName }}</p>
                        <p class="mayhen_invitation_qr_bank" data-sync-target="bride_bank_name">{{ $brideBankName }}</p>
                        <p class="mayhen_invitation_qr_acc" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</p>
                        <p class="mayhen_invitation_qr_holder" data-sync-target="bride_account_name">{{ $brideAccountName }}</p>
                        <button type="button" class="mayhen_invitation_copy_btn" data-copy-account="{{ $brideBankAccount }}">Sao chép STK</button>
                    </article>
                </div>
            </div>
        </section>
        @endif

        <div class="mayhen_invitation_divider" aria-hidden="true"></div>

        {{-- SECTION 10: ENDING (SIGNATURE + RSVP + WISHES + THANK YOU) — RULE #8 COMPLIANT --}}
        <section class="mayhen_invitation_section mayhen_invitation_ending_section" id="section-ending" data-mayhen-section="ending">

            {{-- 10A. SIGNATURE GUESTBOOK (ZERO COUPLE PHOTOS — EMBROIDERY HOOP MONOGRAM ONLY) --}}
            <div class="mayhen_invitation_interactive_block" id="section-signature">
                <p class="mayhen_invitation_script_sub mayhen_invitation_reveal">Signature</p>
                <h2 class="mayhen_invitation_heading mayhen_invitation_reveal">Lưu Bút</h2>
                <div class="mayhen_invitation_stitch_line" aria-hidden="true">
                    <svg viewBox="0 0 24 21"><path d="M12 20.4S1.6 14 1.6 6.9A5.4 5.4 0 0 1 12 4.6a5.4 5.4 0 0 1 10.4 2.3C22.4 14 12 20.4 12 20.4z"/></svg>
                </div>
                <p class="mayhen_invitation_desc mayhen_invitation_reveal">Để lại chữ ký của bạn và cùng chúng mình lưu giữ kỷ niệm hạnh phúc này nhé!</p>

                <div class="mayhen_invitation_stitched_card mayhen_invitation_reveal">
                    <div class="mayhen_invitation_crest_wrap">
                        <div class="mayhen_invitation_crest_hoop" aria-hidden="true">
                            <span class="mayhen_invitation_crest_initials">{{ $groomInitial }}&amp;{{ $brideInitial }}</span>
                        </div>
                        <p class="mayhen_invitation_crest_caption">
                            <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                        </p>
                    </div>

                    <div class="mayhen_invitation_sig_stage">
                        <div class="mayhen_invitation_field">
                            <label class="mayhen_invitation_label" for="mayhenSigName">Tên của bạn</label>
                            <input id="mayhenSigName" type="text" class="mayhen_invitation_input" placeholder="Nhập tên người ký..." maxlength="80" data-sig-name>
                        </div>

                        <div class="mayhen_invitation_sig_board" data-sig-board style="margin-top: 10px;">
                            <canvas class="mayhen_invitation_sig_canvas" data-sig-canvas></canvas>
                            <span class="mayhen_invitation_sig_placeholder">Ký tên của bạn tại đây bằng ngón tay hoặc chuột...</span>
                        </div>

                        <div class="mayhen_invitation_sig_actions">
                            <button type="button" class="mayhen_invitation_ghost_btn" data-sig-clear>Xóa ký lại</button>
                            <button type="button" class="mayhen_invitation_submit_btn" data-sig-save style="width: auto; flex: 1;">Lưu chữ ký</button>
                        </div>

                        <p class="mayhen_invitation_feedback" data-sig-feedback aria-live="polite"></p>

                        <div class="mayhen_invitation_sig_wall" data-sig-wall>
                            @foreach($signaturesList->take(6) as $sig)
                                <div class="mayhen_invitation_sig_chip">
                                    @if(!empty($sig->signature_data))
                                        <img src="{{ $sig->signature_data }}" alt="Chữ ký của {{ $sig->signer_name }}">
                                    @endif
                                    <p class="mayhen_invitation_sig_chip_name">{{ $sig->signer_name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mayhen_invitation_divider" aria-hidden="true"></div>

            {{-- 10B. RSVP CONFIRMATION (ZERO COUPLE PHOTOS — EMBROIDERY HOOP MONOGRAM ONLY) --}}
            <div class="mayhen_invitation_interactive_block mayhen_invitation_interactive_block--ivory" id="section-rsvp">
                <p class="mayhen_invitation_script_sub mayhen_invitation_reveal">Join with us</p>
                <h2 class="mayhen_invitation_eyebrow mayhen_invitation_reveal">Xác Nhận Tham Dự</h2>
                <div class="mayhen_invitation_stitch_line" aria-hidden="true">
                    <svg viewBox="0 0 24 21"><path d="M12 20.4S1.6 14 1.6 6.9A5.4 5.4 0 0 1 12 4.6a5.4 5.4 0 0 1 10.4 2.3C22.4 14 12 20.4 12 20.4z"/></svg>
                </div>
                <p class="mayhen_invitation_desc mayhen_invitation_reveal">Sự hiện diện của bạn là niềm vui của chúng mình. Xin hãy xác nhận để chúng mình đón tiếp chu đáo nhất nhé!</p>

                <div class="mayhen_invitation_stitched_card mayhen_invitation_reveal">
                    <div class="mayhen_invitation_crest_wrap">
                        <div class="mayhen_invitation_crest_hoop" aria-hidden="true">
                            <span class="mayhen_invitation_crest_initials">{{ $groomInitial }}&amp;{{ $brideInitial }}</span>
                        </div>
                    </div>

                    <form class="mayhen_invitation_form" data-rsvp-form novalidate>
                        <div class="mayhen_invitation_field">
                            <label class="mayhen_invitation_label" for="mayhenRsvpName">Tên khách mời</label>
                            <input id="mayhenRsvpName" name="guest_name" type="text" class="mayhen_invitation_input" placeholder="Nhập họ tên của bạn" value="{{ $guest->name ?? '' }}" required>
                        </div>

                        <div class="mayhen_invitation_field">
                            <label class="mayhen_invitation_label" for="mayhenRsvpPhone">Số điện thoại (không bắt buộc)</label>
                            <input id="mayhenRsvpPhone" name="guest_phone" type="tel" class="mayhen_invitation_input" placeholder="09xx xxx xxx">
                        </div>

                        <div class="mayhen_invitation_field">
                            <span class="mayhen_invitation_label">Xác nhận tham dự</span>
                            <input type="hidden" name="status" value="confirmed" data-rsvp-status-input>
                            <div class="mayhen_invitation_choice_group" data-rsvp-status-group>
                                <button type="button" class="mayhen_invitation_choice_btn mayhen_invitation_choice_btn--active" data-status-val="confirmed">Sẽ tham dự</button>
                                <button type="button" class="mayhen_invitation_choice_btn" data-status-val="declined">Tiếc quá</button>
                            </div>
                        </div>

                        <div class="mayhen_invitation_field">
                            <label class="mayhen_invitation_label" for="mayhenRsvpCount">Số lượng khách tham dự</label>
                            <input id="mayhenRsvpCount" name="attending_count" type="number" min="1" max="10" value="1" class="mayhen_invitation_input">
                        </div>

                        <div class="mayhen_invitation_field">
                            <label class="mayhen_invitation_label" for="mayhenRsvpNote">Ghi chú (ăn chay, đi cùng gia đình...)</label>
                            <input id="mayhenRsvpNote" name="notes" type="text" class="mayhen_invitation_input" placeholder="Ghi chú thêm cho gia đình...">
                        </div>

                        <button type="submit" class="mayhen_invitation_submit_btn" data-rsvp-submit>Xác Nhận Tham Dự</button>
                        <p class="mayhen_invitation_feedback" data-rsvp-feedback aria-live="polite"></p>
                    </form>
                </div>
            </div>

            <div class="mayhen_invitation_divider" aria-hidden="true"></div>

            {{-- 10C. WISHES WALL & FORM --}}
            <div class="mayhen_invitation_interactive_block" id="section-wishes">
                <p class="mayhen_invitation_script_sub mayhen_invitation_reveal">Best Wishes</p>
                <h2 class="mayhen_invitation_eyebrow mayhen_invitation_reveal">Lời Chúc Từ Khách Mời</h2>
                <div class="mayhen_invitation_stitch_line" aria-hidden="true">
                    <svg viewBox="0 0 24 21"><path d="M12 20.4S1.6 14 1.6 6.9A5.4 5.4 0 0 1 12 4.6a5.4 5.4 0 0 1 10.4 2.3C22.4 14 12 20.4 12 20.4z"/></svg>
                </div>

                <div class="mayhen_invitation_stitched_card mayhen_invitation_reveal">
                    <div class="mayhen_invitation_wishes_feed" data-wishes-feed>
                        @forelse($wishesList as $wish)
                            <div class="mayhen_invitation_wish_item">
                                <p class="mayhen_invitation_wish_author">{{ $wish->guest_name }}</p>
                                <p class="mayhen_invitation_wish_text">{{ $wish->message }}</p>
                            </div>
                        @empty
                            <p class="mayhen_invitation_wishes_empty" data-wishes-empty>Hãy là người đầu tiên gửi lời chúc phúc ngọt ngào tới {{ $groomName }} &amp; {{ $brideName }}!</p>
                        @endforelse
                    </div>

                    <form class="mayhen_invitation_form" data-wish-form novalidate>
                        <div class="mayhen_invitation_field">
                            <label class="mayhen_invitation_label" for="mayhenWishAuthor">Tên của bạn</label>
                            <input id="mayhenWishAuthor" name="guest_name" type="text" class="mayhen_invitation_input" placeholder="Nhập tên của bạn" value="{{ $guest->name ?? '' }}" required>
                        </div>

                        <div class="mayhen_invitation_field">
                            <label class="mayhen_invitation_label" for="mayhenWishMessage">Lời chúc của bạn</label>
                            <textarea id="mayhenWishMessage" name="message" class="mayhen_invitation_textarea" maxlength="600" placeholder="Viết lời chúc phúc gửi tới cô dâu, chú rể..." required></textarea>
                        </div>

                        <button type="submit" class="mayhen_invitation_submit_btn" data-wish-submit>Gửi Lời Chúc</button>
                        <p class="mayhen_invitation_feedback" data-wish-feedback aria-live="polite"></p>
                    </form>
                </div>
            </div>

            {{-- 10D. FULL-BLEED THANK YOU CARD --}}
            <div class="mayhen_invitation_thankyou">
                <img class="mayhen_invitation_thankyou_photo"
                     src="{{ $endingPhoto }}"
                     alt="Lời cảm ơn {{ $groomName }} và {{ $brideName }}"
                     loading="eager">
                <div class="mayhen_invitation_thankyou_scrim" aria-hidden="true"></div>
                <div class="mayhen_invitation_thankyou_corner mayhen_invitation_thankyou_corner--left" aria-hidden="true"></div>
                <div class="mayhen_invitation_thankyou_corner mayhen_invitation_thankyou_corner--right" aria-hidden="true"></div>

                <div class="mayhen_invitation_thankyou_content">
                    <h2 class="mayhen_invitation_thankyou_title" data-sync-target="thank_you_title">{{ $thankYouTitle }}</h2>
                    <p class="mayhen_invitation_thankyou_quote" data-sync-target="thank_you_quote">{{ $thankYouQuote }}</p>
                    <p class="mayhen_invitation_thankyou_names">
                        <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </p>
                </div>
            </div>
        </section>
    </main>

    {{-- Floating Music Dock --}}
    @if(!$isEmbed)
    <div class="mayhen_invitation_audio_dock">
        <button type="button" class="mayhen_invitation_audio_btn" data-music-toggle aria-label="Bật hoặc tắt nhạc nền" aria-pressed="false">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6z"/></svg>
        </button>
    </div>
    @endif

    {{-- Lightbox Modal --}}
    <div class="mayhen_invitation_lightbox" data-lightbox role="dialog" aria-modal="true" aria-label="Xem ảnh cưới phóng to">
        <button type="button" class="mayhen_invitation_lightbox_close" data-lightbox-close aria-label="Đóng">&times;</button>
        <button type="button" class="mayhen_invitation_lightbox_nav mayhen_invitation_lightbox_nav--prev" data-lightbox-prev aria-label="Ảnh trước">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <img class="mayhen_invitation_lightbox_img" data-lightbox-img src="{{ $galleryUrls[0] }}" alt="Ảnh cưới phóng to">
        <button type="button" class="mayhen_invitation_lightbox_nav mayhen_invitation_lightbox_nav--next" data-lightbox-next aria-label="Ảnh sau">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 18l6-6-6-6"/></svg>
        </button>
    </div>

    {{-- Subtle Toast --}}
    <div class="mayhen_invitation_toast" data-toast role="status" aria-live="polite"></div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ "/mau-thiep/{$slug}/assets/script.js" }}?v={{ filemtime(resource_path("views/client/invitations/{$slug}/script.js")) }}"></script>
@endpush
