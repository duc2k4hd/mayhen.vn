@extends('client.layouts.invitation')

@php
    use App\Services\VietQrService;

    $tplSlug = $template->slug ?? 'tiec-hong-mung-duyen-moi';
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
    $groomName = trim((string) ($wedding?->groom_name ?? ($extraData['groom_name'] ?? 'Hải Nam')));
    if ($groomName === '') {
        $groomName = 'Hải Nam';
    }
    $brideName = trim((string) ($wedding?->bride_name ?? ($extraData['bride_name'] ?? 'Thanh Hà')));
    if ($brideName === '') {
        $brideName = 'Thanh Hà';
    }
    $slogan = trim((string) ($extraData['slogan'] ?? 'WELCOME TO OUR WEDDING'));

    $guestNameRaw = trim((string) ($guest?->name ?? ''));
    $displayGuestName = $guestNameRaw !== '' ? $guestNameRaw : 'Quý khách';

    // 2. Date & Time (Asia/Ho_Chi_Minh)
    $rawWeddingDate = trim((string) ($wedding?->wedding_date?->format('Y-m-d') ?? ($wedding?->wedding_date ?? ($extraData['wedding_date'] ?? '2027-09-06'))));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawWeddingDate)) {
        $rawWeddingDate = '2027-09-06';
    }
    $ceremonyTime = trim((string) ($extraData['ceremony_time'] ?? '17:30'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $ceremonyTime)) {
        $ceremonyTime = '17:30';
    }
    $welcomeTime = trim((string) ($extraData['welcome_time'] ?? '17:30'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $welcomeTime)) {
        $welcomeTime = '17:30';
    }
    $partyTime = trim((string) ($extraData['party_time'] ?? '18:30'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $partyTime)) {
        $partyTime = '18:30';
    }
    $targetTime = trim((string) ($extraData['event_target_time'] ?? $ceremonyTime));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $targetTime)) {
        $targetTime = '17:30';
    }

    $dateTs = strtotime($rawWeddingDate . ' ' . $targetTime . ':00 +0700') ?: strtotime('2027-09-06 17:30:00 +0700');
    $wDay = date('d', $dateTs);
    $wDayNoZero = (string) (int) $wDay;
    $wMonth = date('m', $dateTs);
    $wMonthNoZero = (string) (int) $wMonth;
    $wYear = date('Y', $dateTs);

    $weekdaysVi = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    $wWeekday = $weekdaysVi[(int) date('w', $dateTs)] ?? 'Thứ Hai';
    $wWeekdayUpper = mb_strtoupper($wWeekday, 'UTF-8');
    $openingDateText = $wDayNoZero . ' tháng ' . $wMonthNoZero . ', ' . $wYear;
    $targetIso = $rawWeddingDate . 'T' . $targetTime . ':00+07:00';

    $lunarDate = trim((string) ($extraData['wedding_lunar_date'] ?? '(TỨC NGÀY 06 THÁNG 08 NĂM ĐINH MÙI)'));
    $ceremonyType = trim((string) ($extraData['ceremony_type'] ?? 'Trân trọng kính mời quý vị đến chung vui cùng gia đình chúng tôi'));

    // 3. Photos & Media (100% Local Assets)
    $heroPhoto = $resolveAsset(
        $wedding?->cover_photo_url ?? ($extraData['hero_photo_url'] ?? ($extraData['cover_photo_url'] ?? '')),
        $assetBase . '/images/hero-couple.webp'
    );
    $saveDatePhoto = $resolveAsset($extraData['save_date_photo_url'] ?? '', $assetBase . '/images/save-date.webp');
    $footerPhoto = $resolveAsset($extraData['footer_photo_url'] ?? '', $assetBase . '/images/footer-couple.webp');

    // 4. Parents & Families
    $groomFather = trim((string) ($extraData['groom_father'] ?? 'Lê Minh Quân'));
    $groomMother = trim((string) ($extraData['groom_mother'] ?? 'Lê Mỹ Duyên'));
    $groomParentsAddress = trim((string) ($extraData['groom_parents_address'] ?? 'Quận 1, TP. Hồ Chí Minh'));

    $brideFather = trim((string) ($extraData['bride_father'] ?? 'Ngô Gia Bảo'));
    $brideMother = trim((string) ($extraData['bride_mother'] ?? 'Ngô Ngọc Diệp'));
    $brideParentsAddress = trim((string) ($extraData['bride_parents_address'] ?? 'Quận 3, TP. Hồ Chí Minh'));

    // 5. Love Story
    $storyTitle = trim((string) ($extraData['story_title'] ?? 'TIỆC HỒNG MỪNG DUYÊN MỚI'));
    $storySubtitle = trim((string) ($extraData['story_subtitle'] ?? 'Hành trình yêu thương mở ra chương mới rạng ngời'));
    $coupleQuote = trim((string) ($extraData['couple_quote'] ?? '“Dưới mái vòm bình yên của yêu thương, chúng mình hẹn ước cùng nhau đi hết chặng đường đời.”'));
    $storyContent = trim((string) ($extraData['story_content'] ?? 'Mỗi cuộc gặp gỡ đều là một chữ duyên lành. Cảm ơn bạn đã đến chung vui và chúc phúc cho khởi đầu mới trọn vẹn của hai chúng mình.'));

    // 6. Venue, Maps & Google Calendar
    $venueName = trim((string) ($wedding?->venue_name ?? ($extraData['venue_name'] ?? 'Trung Tâm Tiệc Cưới Gia Viên')));
    if ($venueName === '') {
        $venueName = 'Trung Tâm Tiệc Cưới Gia Viên';
    }
    $venueAddress = trim((string) ($wedding?->venue_address ?? ($extraData['venue_address'] ?? '188 Lê Duẩn, Quận 1, TP. Hồ Chí Minh')));
    if ($venueAddress === '') {
        $venueAddress = '188 Lê Duẩn, Quận 1, TP. Hồ Chí Minh';
    }
    $venueMapUrl = trim((string) ($wedding?->venue_map_url ?? ($extraData['venue_map_url'] ?? '')));
    if ($venueMapUrl === '') {
        $venueMapUrl = 'https://maps.google.com/?q=' . rawurlencode($venueAddress);
    }
    $mapEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($venueAddress) . '&output=embed';

    $gcalStart = gmdate('Ymd\THis\Z', $dateTs);
    $gcalEnd = gmdate('Ymd\THis\Z', $dateTs + 10800);
    $gcalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . rawurlencode('Lễ Thành Hôn ' . $groomName . ' & ' . $brideName)
        . '&dates=' . $gcalStart . '/' . $gcalEnd
        . '&location=' . rawurlencode($venueAddress)
        . '&details=' . rawurlencode('Trân trọng kính mời ' . $displayGuestName . ' tới dự tiệc cưới cùng gia đình chúng tôi.');

    // 7. Timeline & Dresscode
    $defaultTimeline = [
        ['time' => '17:30', 'title' => 'Đón Khách & Chụp Ảnh Lưu Niệm'],
        ['time' => '18:30', 'title' => 'Nghi Thức Trao Nhẫn & Lễ Thành Hôn'],
        ['time' => '19:00', 'title' => 'Khai Tiệc & Cắt Bánh Hạnh Phúc'],
        ['time' => '21:00', 'title' => 'Giao Lưu Âm Nhạc & Kết Thúc Tiệc'],
    ];
    $timelineItems = is_array($extraData['timeline'] ?? null) && count($extraData['timeline']) > 0
        ? $extraData['timeline']
        : $defaultTimeline;
    $timelineIcons = [
        $assetBase . '/images/lamp-decoration.webp',
        $assetBase . '/images/ring.webp',
        $assetBase . '/images/cake.webp',
        $assetBase . '/images/heart.webp',
    ];

    $dresscodeNote = trim((string) ($extraData['dresscode_note'] ?? 'Trang phục dự tiệc sang trọng theo tông xanh lam thanh cung, xanh pastel, vàng cát và trắng kem'));
    $defaultColors = ['#5e7e9b', '#9bb4c8', '#c9b38c', '#f7f4ec'];
    $dresscodeColors = is_array($extraData['dresscode_colors'] ?? null) && count($extraData['dresscode_colors']) > 0
        ? $extraData['dresscode_colors']
        : $defaultColors;

    // 8. 10-Photo Album
    $rawGallery = is_array($extraData['gallery'] ?? null) ? array_values($extraData['gallery']) : [];
    $galleryPhotos = [];
    for ($i = 0; $i < 10; $i++) {
        $num = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
        $fallback = $assetBase . '/images/gallery-' . $num . '.webp';
        $galleryPhotos[] = $resolveAsset(is_string($rawGallery[$i] ?? null) ? $rawGallery[$i] : '', $fallback);
    }

    // 9. Gift Envelope & Dynamic VietQR
    $giftNote = trim((string) ($extraData['gift_note'] ?? 'Nhấn vào phong bao Thanh Cung Lam bên dưới để mở thông tin mừng cưới Nhà Trai & Nhà Gái'));
    $groomBankName = trim((string) ($extraData['groom_bank_name'] ?? 'Vietcombank'));
    $groomBankAccount = trim((string) ($extraData['groom_bank_account'] ?? '0071000998877'));
    $groomAccountName = trim((string) ($extraData['groom_account_name'] ?? ($extraData['groom_bank_owner'] ?? 'LE HAI NAM')));
    $brideBankName = trim((string) ($extraData['bride_bank_name'] ?? 'Techcombank'));
    $brideBankAccount = trim((string) ($extraData['bride_bank_account'] ?? '19036677889900'));
    $brideAccountName = trim((string) ($extraData['bride_account_name'] ?? ($extraData['bride_bank_owner'] ?? 'NGO THANH HA')));

    $groomQrRaw = trim((string) ($extraData['groom_qr_url'] ?? ''));
    $brideQrRaw = trim((string) ($extraData['bride_qr_url'] ?? ''));
    $groomQrImg = VietQrService::resolveQrImage(
        $groomQrRaw !== '' ? $resolveAsset($groomQrRaw) : '',
        $groomBankName,
        $groomBankAccount,
        $groomAccountName,
        'Mung cuoi ' . $groomName
    ) ?: ($assetBase . '/images/qr-groom.webp');
    $brideQrImg = VietQrService::resolveQrImage(
        $brideQrRaw !== '' ? $resolveAsset($brideQrRaw) : '',
        $brideBankName,
        $brideBankAccount,
        $brideAccountName,
        'Mung cuoi ' . $brideName
    ) ?: ($assetBase . '/images/qr-bride.webp');

    // 10. Ending, Music, Wishes, Signatures, Endpoints
    $endingMessage = trim((string) ($extraData['ending_message'] ?? 'Sự hiện diện của Quý khách là niềm vinh hạnh lớn lao và là món quà tuyệt vời nhất trong ngày hạnh phúc của chúng tôi.'));
    $musicUrl = $resolveAsset($extraData['music_url'] ?? ($wedding?->music_url ?? ''), $assetBase . '/wedding-song.mp3');

    $invSlug = $invitation->slug ?? '';
    $invId = $invitation->id ?? '';
    $rsvpUrl = (!$isPreview && $invSlug !== '') ? route('rsvp.submit', ['slug' => $invSlug]) : '';
    $wishUrl = (!$isPreview && $invSlug !== '') ? route('invitation.wish.store', ['slug' => $invSlug]) : '';
    $signatureUrl = (!$isPreview && $invId !== '') ? route('invitation.signature.save', ['id' => $invId]) : '';

    $wishesList = collect($wishes ?? [])->take(20);
    $signaturesList = collect($signatures ?? [])->take(20);
    $csrfToken = csrf_token();
@endphp

@section('title', 'Thiệp Cưới ' . $groomName . ' & ' . $brideName . ' — Tiệc Hồng Mừng Duyên Mới')
@section('body_class', 'mayhen_invitation_body_thanhcung' . ($isEmbed ? ' mayhen_embed_mode' : '') . ($autoOpened ? ' mayhen_template_auto_opened' : ''))

@push('styles')
    <link rel="stylesheet" href="{{ $assetBase }}/styles.css?v={{ @filemtime(resource_path('views/client/invitations/' . $tplSlug . '/styles.css')) ?: '1' }}">
@endpush

@section('content')
<div
    id="mayhen_invitation_root"
    class="mayhen_invitation_root{{ $isEmbed ? ' mayhen_invitation_root--embed' : '' }}{{ $autoOpened ? ' mayhen_invitation_root--opened' : '' }}"
    data-template-slug="{{ $tplSlug }}"
    data-embed="{{ $isEmbed ? '1' : '0' }}"
    data-preview="{{ $isPreview ? '1' : '0' }}"
    data-wedding-date="{{ $rawWeddingDate }}"
    data-target-iso="{{ $targetIso }}"
    data-csrf="{{ $csrfToken }}"
    data-rsvp-url="{{ $rsvpUrl }}"
    data-wish-url="{{ $wishUrl }}"
    data-signature-url="{{ $signatureUrl }}"
>
    {{-- Hidden Native Audio --}}
    <audio class="mayhen_invitation_audio" data-mayhen-audio loop preload="none" src="{{ $musicUrl }}"></audio>

    {{-- Floating Palace Blue Music Controller --}}
    <button type="button" id="mayhen_music_toggle" class="mayhen_invitation_music_btn" aria-label="Bật hoặc tắt nhạc nền" aria-pressed="false">
        <svg class="mayhen_invitation_music_icon_muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 18V5l12-2v13"></path>
            <circle cx="6" cy="18" r="3"></circle>
            <circle cx="18" cy="16" r="3"></circle>
            <line x1="2" y1="2" x2="22" y2="22"></line>
        </svg>
        <svg class="mayhen_invitation_music_icon_note" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" aria-hidden="true">
            <line x1="6" y1="10" x2="6" y2="18"></line>
            <line x1="10" y1="6" x2="10" y2="18"></line>
            <line x1="14" y1="11" x2="14" y2="18"></line>
            <line x1="18" y1="8" x2="18" y2="18"></line>
        </svg>
    </button>

    {{-- ================================================================
         OPENING CURTAIN: QASR BLUE ROYAL PALACE ARCH
         ================================================================ --}}
    <div id="mayhen_opening" class="mayhen_invitation_opening{{ $autoOpened ? ' is-opened' : '' }}" aria-hidden="{{ $autoOpened ? 'true' : 'false' }}">
        <div class="mayhen_invitation_open_card">
            <div class="mayhen_invitation_open_inner">
                <div class="mayhen_invitation_open_seal" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>

                <h2 class="mayhen_invitation_open_names">
                    <span data-sync-target="groom_name">{{ $groomName }}</span>
                    <span class="mayhen_invitation_open_amp">&amp;</span>
                    <span data-sync-target="bride_name">{{ $brideName }}</span>
                </h2>

                <div class="mayhen_invitation_open_rule" aria-hidden="true">
                    <span></span><em>❦</em><span></span>
                </div>

                <p class="mayhen_invitation_open_date" data-sync-target="opening_date_text">{{ $openingDateText }}</p>
                <p class="mayhen_invitation_open_invite">Thân Mời <strong data-sync-target="guest_name">{{ $displayGuestName }}</strong></p>

                <button type="button" id="mayhen_open_btn" class="mayhen_invitation_open_btn">Mở thiệp</button>
            </div>
        </div>
    </div>

    {{-- ================================================================
         MAIN INVITATION SHEET — QASR BLUE (TIỆC HỒNG MỪNG DUYÊN MỚI)
         ================================================================ --}}
    <div class="mayhen_invitation_shell card-wrapper">
        <main class="mayhen_invitation_main">

            {{-- SECTION 1: HERO — PALACE ARCH + LANTERNS + CURTAINS + DOME PALACE --}}
            <section id="mayhen_section_hero" class="mayhen_invitation_section mayhen_invitation_hero" data-mayhen-section="hero">
                <img class="mayhen_invitation_hero_arch" src="{{ $assetBase }}/images/wallframe-decoration.webp" alt="" aria-hidden="true">
                <img class="mayhen_invitation_hero_curtain mayhen_invitation_hero_curtain--l" src="{{ $assetBase }}/images/curtain-decoration.webp" alt="" aria-hidden="true">
                <img class="mayhen_invitation_hero_curtain mayhen_invitation_hero_curtain--r" src="{{ $assetBase }}/images/curtain-decoration.webp" alt="" aria-hidden="true">
                <img class="mayhen_invitation_hero_lamp mayhen_invitation_hero_lamp--l" src="{{ $assetBase }}/images/lamp-decoration.webp" alt="" aria-hidden="true">
                <img class="mayhen_invitation_hero_lamp mayhen_invitation_hero_lamp--r" src="{{ $assetBase }}/images/lamp-decoration.webp" alt="" aria-hidden="true">

                <div class="mayhen_invitation_hero_top mayhen_invitation_reveal">
                    <p class="mayhen_invitation_hero_kicker" data-sync-target="slogan">{{ $slogan }}</p>
                    <h1 class="mayhen_invitation_hero_names">
                        <span data-sync-target="groom_name">{{ mb_strtoupper($groomName, 'UTF-8') }}</span>
                        <span class="mayhen_invitation_hero_amp">&amp;</span>
                        <span data-sync-target="bride_name">{{ mb_strtoupper($brideName, 'UTF-8') }}</span>
                    </h1>
                </div>

                <div class="mayhen_invitation_hero_palace_wrap">
                    <img class="mayhen_invitation_hero_palace" src="{{ $assetBase }}/images/house2-decoration.webp" alt="Cung điện Thanh Cung Lam" loading="eager">
                    <img class="mayhen_invitation_hero_flower mayhen_invitation_hero_flower--l" src="{{ $assetBase }}/images/flower3-decoration.webp" alt="" aria-hidden="true">
                    <img class="mayhen_invitation_hero_flower mayhen_invitation_hero_flower--r" src="{{ $assetBase }}/images/flower3-decoration.webp" alt="" aria-hidden="true">
                </div>
            </section>

            {{-- SECTION 2: PARENTS & FORMAL ANNOUNCEMENT (PALACE PILLAR FRAME) --}}
            <section id="mayhen_section_parents" class="mayhen_invitation_section mayhen_invitation_parents mayhen_invitation_reveal" data-mayhen-section="parents">
                <img class="mayhen_invitation_palace_bg_fade" src="{{ $assetBase }}/images/house-background.webp" alt="" aria-hidden="true">
                <img class="mayhen_invitation_parents_crest" src="{{ $assetBase }}/images/frame-decoration.webp" alt="" aria-hidden="true">

                <div class="mayhen_invitation_palace_card">
                    <h2 class="mayhen_invitation_sand_title">THÔNG TIN LỄ CƯỚI</h2>

                    <div class="mayhen_invitation_parents_grid">
                        <div class="mayhen_invitation_parent_col">
                            <span class="mayhen_invitation_parent_kicker">Ông Bà</span>
                            <p class="mayhen_invitation_parent_name" data-sync-target="groom_father">{{ $groomFather }}</p>
                            <p class="mayhen_invitation_parent_name" data-sync-target="groom_mother">{{ $groomMother }}</p>
                            <p class="mayhen_invitation_parent_addr" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                        </div>

                        <div class="mayhen_invitation_parent_divider" aria-hidden="true">
                            <img src="{{ $assetBase }}/images/goldenline-decoration.webp" alt="">
                        </div>

                        <div class="mayhen_invitation_parent_col">
                            <span class="mayhen_invitation_parent_kicker">Ông Bà</span>
                            <p class="mayhen_invitation_parent_name" data-sync-target="bride_father">{{ $brideFather }}</p>
                            <p class="mayhen_invitation_parent_name" data-sync-target="bride_mother">{{ $brideMother }}</p>
                            <p class="mayhen_invitation_parent_addr" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                        </div>
                    </div>

                    <p class="mayhen_invitation_invite_copy" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>

                    <div class="mayhen_invitation_formal_couple">
                        <h3 class="mayhen_invitation_formal_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                        <span class="mayhen_invitation_formal_amp" aria-hidden="true">&amp;</span>
                        <h3 class="mayhen_invitation_formal_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                    </div>

                    <div class="mayhen_invitation_ceremony_block">
                        <p class="mayhen_invitation_ceremony_heading">LỄ THÀNH HÔN<br><span data-sync-target="venue_name">{{ mb_strtoupper($venueName, 'UTF-8') }}</span></p>
                        <p class="mayhen_invitation_ceremony_time">VÀO LÚC <strong data-sync-target="ceremony_time">{{ $ceremonyTime }}</strong></p>

                        <div class="mayhen_invitation_date_bar">
                            <span class="mayhen_invitation_date_weekday" data-sync-target="wedding_weekday">{{ $wWeekdayUpper }}</span>
                            <span class="mayhen_invitation_date_pipe" aria-hidden="true"></span>
                            <span class="mayhen_invitation_date_day" data-sync-target="wedding_day">{{ $wDay }}</span>
                            <span class="mayhen_invitation_date_pipe" aria-hidden="true"></span>
                            <span class="mayhen_invitation_date_month" data-sync-target="wedding_month">THÁNG {{ $wMonth }}</span>
                        </div>

                        <p class="mayhen_invitation_date_year" data-sync-target="wedding_year">{{ $wYear }}</p>
                        <p class="mayhen_invitation_date_lunar" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</p>
                    </div>
                </div>
            </section>

            {{-- SECTION 3: EVENTS & GOOGLE MAPS --}}
            <section id="mayhen_section_events" class="mayhen_invitation_section mayhen_invitation_events mayhen_invitation_reveal" data-mayhen-section="events">
                <div class="mayhen_invitation_palace_card">
                    <h2 class="mayhen_invitation_sand_title">THÔNG TIN TIỆC CƯỚI</h2>

                    <div class="mayhen_invitation_party_times">
                        <div class="mayhen_invitation_party_item">
                            <span>ĐÓN KHÁCH</span>
                            <strong data-sync-target="welcome_time">{{ $welcomeTime }}</strong>
                        </div>
                        <div class="mayhen_invitation_party_item">
                            <span>KHAI TIỆC</span>
                            <strong data-sync-target="party_time">{{ $partyTime }}</strong>
                        </div>
                    </div>

                    <p class="mayhen_invitation_venue_kicker">Tiệc cưới được tổ chức tại</p>
                    <h3 class="mayhen_invitation_venue_name" data-sync-target="venue_name">{{ $venueName }}</h3>
                    <p class="mayhen_invitation_venue_addr" data-sync-target="venue_address">{{ $venueAddress }}</p>

                    <div class="mayhen_invitation_map_frame">
                        <iframe
                            src="{{ $mapEmbedUrl }}"
                            title="Bản đồ địa điểm tổ chức"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>

                    <div class="mayhen_invitation_venue_actions">
                        <a href="{{ $venueMapUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_outline_btn" data-sync-target="venue_map_url">Chỉ đường Google Maps</a>
                        <a href="#mayhen_rsvp_form" class="mayhen_invitation_primary_btn">XÁC NHẬN THAM DỰ</a>
                    </div>
                </div>
            </section>

            {{-- SECTION 4: PALACE HEART CALENDAR & COUNTDOWN --}}
            <section id="mayhen_section_countdown" class="mayhen_invitation_section mayhen_invitation_calendar_sec mayhen_invitation_reveal" data-mayhen-section="countdown_calendar">
                <h2 class="mayhen_invitation_sand_title">CÙNG ĐẾM NGƯỢC</h2>

                <div class="mayhen_invitation_countdown_grid" id="mayhen_countdown">
                    <div class="mayhen_invitation_cd_box"><strong data-cd="days">00</strong><span>Ngày</span></div>
                    <div class="mayhen_invitation_cd_box"><strong data-cd="hours">00</strong><span>Giờ</span></div>
                    <div class="mayhen_invitation_cd_box"><strong data-cd="minutes">00</strong><span>Phút</span></div>
                    <div class="mayhen_invitation_cd_box"><strong data-cd="seconds">00</strong><span>Giây</span></div>
                </div>

                <div class="mayhen_invitation_palace_card mayhen_invitation_cal_card">
                    <div class="mayhen_invitation_cal_head" id="mayhen_cal_title">Tháng {{ $wMonthNoZero }} / {{ $wYear }}</div>
                    <div class="mayhen_invitation_cal_weekdays">
                        <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                    </div>
                    <div class="mayhen_invitation_cal_grid" id="mayhen_cal_grid"></div>
                    <a href="{{ $gcalUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_gcal_link">Lưu vào Google Calendar</a>
                </div>
            </section>

            {{-- SECTION 5: LOVE STORY --}}
            <section id="mayhen_section_story" class="mayhen_invitation_section mayhen_invitation_story mayhen_invitation_reveal" data-mayhen-section="story">
                <h2 class="mayhen_invitation_sand_title" data-sync-target="story_title">{{ $storyTitle }}</h2>
                <p class="mayhen_invitation_section_sub" data-sync-target="story_subtitle">{{ $storySubtitle }}</p>

                <div class="mayhen_invitation_palace_card">
                    <div class="mayhen_invitation_story_photo">
                        <img src="{{ $saveDatePhoto }}" alt="Chuyện tình yêu" data-sync-target="save_date_photo_url" loading="lazy">
                    </div>
                    <blockquote class="mayhen_invitation_story_quote" data-sync-target="couple_quote">{{ $coupleQuote }}</blockquote>
                    <p class="mayhen_invitation_story_body" data-sync-target="story_content">{{ $storyContent }}</p>
                </div>
            </section>

            @if (!$isCuratedSeven)
                {{-- SECTION 6: TIMELINE WITH PALACE ICONS --}}
                <section id="mayhen_section_timeline" class="mayhen_invitation_section mayhen_invitation_timeline mayhen_invitation_reveal" data-mayhen-section="timeline">
                    <h2 class="mayhen_invitation_sand_title">LỊCH TRÌNH NGÀY CƯỚI</h2>
                    <div class="mayhen_invitation_palace_card mayhen_invitation_timeline_list" data-sync-target="timeline">
                        @foreach ($timelineItems as $idx => $item)
                            <div class="mayhen_invitation_timeline_row">
                                <div class="mayhen_invitation_timeline_icon">
                                    <img src="{{ $timelineIcons[$idx % count($timelineIcons)] }}" alt="" aria-hidden="true" loading="lazy">
                                </div>
                                <div class="mayhen_invitation_timeline_info">
                                    <span class="mayhen_invitation_timeline_time">{{ $item['time'] ?? '' }}</span>
                                    <p class="mayhen_invitation_timeline_text">{{ $item['title'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- SECTION 7: DRESS CODE --}}
                <section id="mayhen_section_dresscode" class="mayhen_invitation_section mayhen_invitation_dresscode mayhen_invitation_reveal" data-mayhen-section="dresscode">
                    <div class="mayhen_invitation_palace_card">
                        <h2 class="mayhen_invitation_sand_title">DRESS CODE</h2>
                        <p class="mayhen_invitation_section_sub" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>
                        <div class="mayhen_invitation_swatches">
                            @foreach ($dresscodeColors as $hex)
                                <span class="mayhen_invitation_swatch" style="background-color: {{ $hex }};" title="{{ $hex }}"></span>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            {{-- SECTION 8: 10-PHOTO ALBUM & LIGHTBOX --}}
            <section id="mayhen_section_album" class="mayhen_invitation_section mayhen_invitation_album mayhen_invitation_reveal" data-mayhen-section="album">
                <h2 class="mayhen_invitation_sand_title">ALBUM ẢNH</h2>

                <div class="mayhen_invitation_album_grid">
                    @foreach ($galleryPhotos as $idx => $photo)
                        <button type="button" class="mayhen_invitation_album_item{{ ($idx === 2 || $idx === 7) ? ' mayhen_invitation_album_item--wide' : '' }}" data-album-index="{{ $idx }}" aria-label="Xem ảnh cưới {{ $idx + 1 }}">
                            <img src="{{ $photo }}" alt="Ảnh cưới {{ $idx + 1 }}" data-sync-target="gallery.{{ $idx }}" loading="{{ $idx < 2 ? 'eager' : 'lazy' }}">
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- SECTION 9: QASR BLUE GIFT ENVELOPE & DYNAMIC VIETQR --}}
            <section id="mayhen_section_gift" class="mayhen_invitation_section mayhen_invitation_gift mayhen_invitation_reveal" data-mayhen-section="gift">
                <h2 class="mayhen_invitation_sand_title">HỘP QUÀ MỪNG</h2>
                <p class="mayhen_invitation_section_sub" data-sync-target="gift_note">{{ $giftNote }}</p>

                <div class="mayhen_invitation_gift_stage">
                    <button type="button" id="mayhen_gift_toggle" class="mayhen_invitation_gift_btn" aria-expanded="false" aria-controls="mayhen_gift_panel">
                        <img class="mayhen_invitation_gift_main" src="{{ $assetBase }}/images/envelope-blue.webp" alt="Phong bao Thanh Cung Lam" loading="lazy">
                        <span class="mayhen_invitation_primary_btn mayhen_invitation_btn_sm">✦ Nhấn để mở mừng cưới ✦</span>
                    </button>
                </div>

                <div id="mayhen_gift_panel" class="mayhen_invitation_gift_panel" aria-hidden="true">
                    <div class="mayhen_invitation_qr_grid">
                        <div class="mayhen_invitation_qr_card">
                            <span class="mayhen_invitation_qr_side">NHÀ TRAI</span>
                            <div class="mayhen_invitation_qr_box">
                                <img src="{{ $groomQrImg }}" alt="QR Nhà Trai" data-sync-target="groom_qr_url" loading="lazy">
                            </div>
                            <strong class="mayhen_invitation_qr_name" data-sync-target="groom_name">{{ $groomName }}</strong>
                            <span class="mayhen_invitation_qr_bank" data-sync-target="groom_bank_name">{{ $groomBankName }}</span>
                            <span class="mayhen_invitation_qr_acc" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</span>
                            <span class="mayhen_invitation_qr_owner" data-sync-target="groom_account_name">{{ $groomAccountName }}</span>
                            <button type="button" class="mayhen_invitation_copy_btn" data-copy-account="groom_bank_account">Sao chép STK</button>
                        </div>

                        <div class="mayhen_invitation_qr_card">
                            <span class="mayhen_invitation_qr_side">NHÀ GÁI</span>
                            <div class="mayhen_invitation_qr_box">
                                <img src="{{ $brideQrImg }}" alt="QR Nhà Gái" data-sync-target="bride_qr_url" loading="lazy">
                            </div>
                            <strong class="mayhen_invitation_qr_name" data-sync-target="bride_name">{{ $brideName }}</strong>
                            <span class="mayhen_invitation_qr_bank" data-sync-target="bride_bank_name">{{ $brideBankName }}</span>
                            <span class="mayhen_invitation_qr_acc" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</span>
                            <span class="mayhen_invitation_qr_owner" data-sync-target="bride_account_name">{{ $brideAccountName }}</span>
                            <button type="button" class="mayhen_invitation_copy_btn" data-copy-account="bride_bank_account">Sao chép STK</button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECTION 10: SIGNATURE, RSVP, WISHES & FINALE (LAW #8: ZERO HUMAN PHOTOS IN SIGNATURE/RSVP) --}}
            <section id="mayhen_section_ending" class="mayhen_invitation_section mayhen_invitation_ending mayhen_invitation_reveal" data-mayhen-section="ending">

                {{-- 10A. Signature Book (Pure CSS Palace Blue & Sand — Zero Photos) --}}
                <div class="mayhen_invitation_palace_card">
                    <div class="mayhen_invitation_wax_badge" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
                            <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
                            <path d="M2 2l7.586 7.586"></path>
                        </svg>
                    </div>
                    <h2 class="mayhen_invitation_sand_title">SỔ KÝ TÊN LƯU BÚT</h2>
                    <p class="mayhen_invitation_section_sub">Lưu lại chữ ký kỷ niệm gửi tặng <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span></p>

                    <div id="mayhen_sig_wall" class="mayhen_invitation_sig_wall">
                        @forelse ($signaturesList as $sig)
                            <div class="mayhen_invitation_sig_item">
                                <img src="{{ $sig->signature_data }}" alt="{{ $sig->guest_name }}">
                                <span>{{ $sig->guest_name }}</span>
                            </div>
                        @empty
                            <p class="mayhen_invitation_sig_empty">Chưa có chữ ký nào — hãy là người đầu tiên lưu bút chúc phúc đôi uyên ương!</p>
                        @endforelse
                    </div>

                    <input type="text" id="mayhen_sig_name" class="mayhen_invitation_input" placeholder="Nhập họ tên của bạn trước khi ký..." autocomplete="name">

                    <div class="mayhen_invitation_sig_pad">
                        <canvas id="mayhen_sig_canvas" class="mayhen_invitation_sig_canvas"></canvas>
                        <span id="mayhen_sig_placeholder" class="mayhen_invitation_sig_placeholder">Chạm hoặc kéo tay để ký tên tại đây</span>
                    </div>

                    <div class="mayhen_invitation_sig_tools">
                        <div class="mayhen_invitation_sig_colors">
                            <button type="button" class="mayhen_invitation_sig_dot is-active" data-sig-color="#5e7e9b" style="background:#5e7e9b;" aria-label="Mực xanh lam"></button>
                            <button type="button" class="mayhen_invitation_sig_dot" data-sig-color="#72614b" style="background:#72614b;" aria-label="Mực nâu vàng"></button>
                            <button type="button" class="mayhen_invitation_sig_dot" data-sig-color="#1f1a17" style="background:#1f1a17;" aria-label="Mực đen"></button>
                        </div>
                        <div class="mayhen_invitation_sig_btns">
                            <button type="button" id="mayhen_sig_clear" class="mayhen_invitation_outline_btn mayhen_invitation_btn_sm">Xóa</button>
                            <button type="button" id="mayhen_sig_download" class="mayhen_invitation_outline_btn mayhen_invitation_btn_sm">Tải PNG</button>
                            <button type="button" id="mayhen_sig_save" class="mayhen_invitation_primary_btn mayhen_invitation_btn_sm">Lưu chữ ký</button>
                        </div>
                    </div>
                    <p id="mayhen_sig_status" class="mayhen_invitation_status_msg" role="status" aria-live="polite"></p>
                </div>

                {{-- 10B. RSVP Form (Pure CSS Palace Blue Frame — Zero Photos) --}}
                <div class="mayhen_invitation_rsvp_outer">
                    <form id="mayhen_rsvp_form" class="mayhen_invitation_rsvp_card" novalidate>
                        <div class="mayhen_invitation_rsvp_ornament" aria-hidden="true">❦</div>
                        <h2 class="mayhen_invitation_sand_title">XÁC NHẬN THAM DỰ</h2>

                        <div class="mayhen_invitation_field">
                            <label for="mayhen_rsvp_name">Họ và tên *</label>
                            <input type="text" id="mayhen_rsvp_name" name="guest_name" class="mayhen_invitation_input" placeholder="Nhập tên của bạn" value="{{ $guestNameRaw }}" required>
                        </div>

                        <div class="mayhen_invitation_field">
                            <span class="mayhen_invitation_field_lbl">Quý khách sẽ tham dự chứ?</span>
                            <label class="mayhen_invitation_radio_row">
                                <input type="radio" name="attending_status" value="attending" checked>
                                <span>Có, tôi sẽ tham dự</span>
                            </label>
                            <label class="mayhen_invitation_radio_row">
                                <input type="radio" name="attending_status" value="declined">
                                <span>Tôi bận, rất tiếc không thể tham dự</span>
                            </label>
                            <label class="mayhen_invitation_radio_row">
                                <input type="radio" name="attending_status" value="maybe">
                                <span>Có thể tham dự</span>
                            </label>
                        </div>

                        <div class="mayhen_invitation_field_row">
                            <div class="mayhen_invitation_field">
                                <label for="mayhen_rsvp_adults">Người lớn</label>
                                <select id="mayhen_rsvp_adults" name="adults_count" class="mayhen_invitation_input">
                                    <option value="1">1 người</option>
                                    <option value="2">2 người</option>
                                    <option value="3">3 người</option>
                                    <option value="4">4 người</option>
                                </select>
                            </div>
                            <div class="mayhen_invitation_field">
                                <label for="mayhen_rsvp_children">Trẻ em</label>
                                <select id="mayhen_rsvp_children" name="children_count" class="mayhen_invitation_input">
                                    <option value="0">0 trẻ em</option>
                                    <option value="1">1 trẻ em</option>
                                    <option value="2">2 trẻ em</option>
                                    <option value="3">3 trẻ em</option>
                                </select>
                            </div>
                        </div>

                        <div class="mayhen_invitation_field">
                            <label for="mayhen_rsvp_wishes">Lời nhắn gửi Dâu Rể (tuỳ chọn)</label>
                            <textarea id="mayhen_rsvp_wishes" name="wishes" rows="3" class="mayhen_invitation_input" placeholder="Ghi chú chế độ ăn hoặc lời chúc ngắn..."></textarea>
                        </div>

                        <button type="submit" id="mayhen_rsvp_submit" class="mayhen_invitation_primary_btn mayhen_invitation_btn_full">GỬI XÁC NHẬN</button>
                        <p id="mayhen_rsvp_status" class="mayhen_invitation_status_msg" role="status" aria-live="polite"></p>
                    </form>
                </div>

                {{-- 10C. Guestbook Wishes --}}
                <div class="mayhen_invitation_palace_card">
                    <h2 class="mayhen_invitation_sand_title">SỔ LỜI CHÚC</h2>
                    <form id="mayhen_wish_form" class="mayhen_invitation_wish_form" novalidate>
                        <input type="text" id="mayhen_wish_name" class="mayhen_invitation_input" placeholder="Tên của bạn *" required>
                        <textarea id="mayhen_wish_msg" rows="3" class="mayhen_invitation_input" placeholder="Viết lời chúc tốt đẹp nhất gửi tới cô dâu chú rể *" required></textarea>
                        <button type="submit" id="mayhen_wish_submit" class="mayhen_invitation_primary_btn mayhen_invitation_btn_full">GỬI LỜI CHÚC</button>
                        <p id="mayhen_wish_status" class="mayhen_invitation_status_msg" role="status" aria-live="polite"></p>
                    </form>

                    <div id="mayhen_wish_list" class="mayhen_invitation_wish_list">
                        @forelse ($wishesList as $wish)
                            <article class="mayhen_invitation_wish_item">
                                <strong>{{ $wish->name }}</strong>
                                <p>{{ $wish->message }}</p>
                            </article>
                        @empty
                            <article class="mayhen_invitation_wish_item">
                                <strong>Mây Hẹn Atelier</strong>
                                <p>Chúc hai bạn trăm năm hạnh phúc, duyên mới trọn vẹn bình yên!</p>
                            </article>
                        @endforelse
                    </div>
                </div>

                {{-- 10D. Finale Thank You --}}
                <div class="mayhen_invitation_finale">
                    <div class="mayhen_invitation_finale_photo">
                        <img src="{{ $footerPhoto }}" alt="Cảm ơn" data-sync-target="footer_photo_url" loading="lazy">
                    </div>
                    <p class="mayhen_invitation_finale_thank">Thank You!</p>
                    <p class="mayhen_invitation_finale_msg" data-sync-target="ending_message">{{ $endingMessage }}</p>
                    <p class="mayhen_invitation_finale_names">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <span>&amp;</span>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </p>
                    <img class="mayhen_invitation_finale_scene" src="{{ $assetBase }}/images/scene-decoration.webp" alt="" aria-hidden="true" loading="lazy">
                </div>
            </section>

        </main>
    </div>

    {{-- Fullscreen Album Lightbox --}}
    <div id="mayhen_lightbox" class="mayhen_invitation_lightbox" aria-hidden="true" role="dialog" aria-label="Xem ảnh phóng to">
        <button type="button" id="mayhen_lb_close" class="mayhen_invitation_lb_close" aria-label="Đóng">✕</button>
        <button type="button" id="mayhen_lb_prev" class="mayhen_invitation_lb_nav mayhen_invitation_lb_nav--prev" aria-label="Ảnh trước">‹</button>
        <img id="mayhen_lb_img" class="mayhen_invitation_lb_img" src="{{ $galleryPhotos[0] }}" alt="Ảnh cưới phóng to">
        <button type="button" id="mayhen_lb_next" class="mayhen_invitation_lb_nav mayhen_invitation_lb_nav--next" aria-label="Ảnh kế tiếp">›</button>
        <div id="mayhen_lb_counter" class="mayhen_invitation_lb_counter">1 / {{ count($galleryPhotos) }}</div>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ $assetBase }}/script.js?v={{ @filemtime(resource_path('views/client/invitations/' . $tplSlug . '/script.js')) ?: '1' }}"></script>
@endpush
