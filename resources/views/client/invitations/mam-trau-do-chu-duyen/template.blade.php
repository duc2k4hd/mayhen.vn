@extends('client.layouts.invitation')

@php
    use App\Services\VietQrService;

    $tplSlug = $template->slug ?? 'mam-trau-do-chu-duyen';
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
    $groomName = trim((string) ($wedding?->groom_name ?? ($extraData['groom_name'] ?? 'Quốc Bảo')));
    if ($groomName === '') {
        $groomName = 'Quốc Bảo';
    }
    $brideName = trim((string) ($wedding?->bride_name ?? ($extraData['bride_name'] ?? 'Khánh Vy')));
    if ($brideName === '') {
        $brideName = 'Khánh Vy';
    }
    $groomRole = trim((string) ($extraData['groom_role'] ?? 'Út Nam'));
    $brideRole = trim((string) ($extraData['bride_role'] ?? 'Thứ Nữ'));

    $guestNameRaw = trim((string) ($guest?->name ?? ''));
    $displayGuestName = $guestNameRaw !== '' ? $guestNameRaw : 'Quý khách';

    // 2. Date & Time (Asia/Ho_Chi_Minh)
    $rawWeddingDate = trim((string) ($wedding?->wedding_date?->format('Y-m-d') ?? ($wedding?->wedding_date ?? ($extraData['wedding_date'] ?? '2027-03-13'))));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawWeddingDate)) {
        $rawWeddingDate = '2027-03-13';
    }
    $ceremonyTime = trim((string) ($extraData['ceremony_time'] ?? '11:00'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $ceremonyTime)) {
        $ceremonyTime = '11:00';
    }
    $welcomeTime = trim((string) ($extraData['welcome_time'] ?? '11:30'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $welcomeTime)) {
        $welcomeTime = '11:30';
    }
    $partyTime = trim((string) ($extraData['party_time'] ?? '12:00'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $partyTime)) {
        $partyTime = '12:00';
    }
    $targetTime = trim((string) ($extraData['event_target_time'] ?? $ceremonyTime));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $targetTime)) {
        $targetTime = '11:00';
    }

    $dateTs = strtotime($rawWeddingDate . ' ' . $targetTime . ':00 +0700') ?: strtotime('2027-03-13 11:00:00 +0700');
    $wDay = date('d', $dateTs);
    $wDayNoZero = (string) (int) $wDay;
    $wMonth = date('m', $dateTs);
    $wMonthNoZero = (string) (int) $wMonth;
    $wYear = date('Y', $dateTs);

    $weekdaysVi = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    $wWeekday = $weekdaysVi[(int) date('w', $dateTs)] ?? 'Thứ Bảy';
    $wWeekdayUpper = mb_strtoupper($wWeekday, 'UTF-8');
    $openingDateText = $wDayNoZero . ' tháng ' . $wMonthNoZero . ', ' . $wYear;
    $targetIso = $rawWeddingDate . 'T' . $targetTime . ':00+07:00';

    $lunarDate = trim((string) ($extraData['wedding_lunar_date'] ?? '(TỨC NGÀY 06 THÁNG 02 NĂM ĐINH MÙI)'));
    $ceremonyType = trim((string) ($extraData['ceremony_type'] ?? 'LỄ THÀNH HÔN CỦA CON CHÚNG TÔI'));

    // 3. Photos & Media (100% Local Assets)
    $heroPhoto = $resolveAsset(
        $wedding?->cover_photo_url ?? ($extraData['hero_photo_url'] ?? ($extraData['cover_photo_url'] ?? '')),
        $assetBase . '/images/hero-couple.webp'
    );
    $saveDatePhoto = $resolveAsset($extraData['save_date_photo_url'] ?? '', $assetBase . '/images/save-date.webp');
    $footerPhoto = $resolveAsset($extraData['footer_photo_url'] ?? '', $assetBase . '/images/footer-couple.webp');

    // 4. Parents & Families
    $groomFather = trim((string) ($extraData['groom_father'] ?? 'Đặng Hữu Thịnh'));
    $groomMother = trim((string) ($extraData['groom_mother'] ?? 'Bùi Thị Ngọc Diệp'));
    $groomParentsAddress = trim((string) ($extraData['groom_parents_address'] ?? 'Số 42 đường Lê Lợi, phường Vĩnh Ninh, thành phố Huế'));

    $brideFather = trim((string) ($extraData['bride_father'] ?? 'Lưu Văn Cường'));
    $brideMother = trim((string) ($extraData['bride_mother'] ?? 'Trần Thị Mỹ Hạnh'));
    $brideParentsAddress = trim((string) ($extraData['bride_parents_address'] ?? 'Thôn An Truyền, xã Phú An, huyện Phú Vang, thành phố Huế'));

    // 5. Love Story
    $storyTitle = trim((string) ($extraData['story_title'] ?? 'MÂM TRẦU ĐỎ CHỮ DUYÊN'));
    $storySubtitle = trim((string) ($extraData['story_subtitle'] ?? 'Trăm năm tình viên mãn, bạc đầu nghĩa phu thê'));
    $coupleQuote = trim((string) ($extraData['couple_quote'] ?? '“Trầu xanh cau trắng nên duyên, tơ hồng xe thắm bình yên một đời.”'));
    $storyContent = trim((string) ($extraData['story_content'] ?? 'Từ cái nắm tay đầu tiên đến ngày sánh bước dưới mái nhà chung, mỗi chặng đường đều trọn vẹn khi có nhau và có sự chúc phúc của gia đình, người thân cùng bạn bè quý mến.'));

    // 6. Venue, Maps & Google Calendar
    $venueName = trim((string) ($wedding?->venue_name ?? ($extraData['venue_name'] ?? 'Trung Tâm Tiệc Cưới Cung Đình Huế')));
    if ($venueName === '') {
        $venueName = 'Trung Tâm Tiệc Cưới Cung Đình Huế';
    }
    $venueAddress = trim((string) ($wedding?->venue_address ?? ($extraData['venue_address'] ?? '42 Lê Lợi, phường Vĩnh Ninh, thành phố Huế')));
    if ($venueAddress === '') {
        $venueAddress = '42 Lê Lợi, phường Vĩnh Ninh, thành phố Huế';
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
        ['time' => '09:30', 'title' => 'Lễ Gia Tiên & Đón Dâu'],
        ['time' => '11:00', 'title' => 'Đón Khách & Chụp Ảnh Lưu Niệm'],
        ['time' => '12:00', 'title' => 'Khai Tiệc & Cắt Bánh Hạnh Phúc'],
        ['time' => '13:00', 'title' => 'Giao Lưu Âm Nhạc & Nâng Ly Chúc Mừng'],
    ];
    $timelineItems = is_array($extraData['timeline'] ?? null) && count($extraData['timeline']) > 0
        ? $extraData['timeline']
        : $defaultTimeline;
    $timelineIcons = [
        $assetBase . '/images/home.webp',
        $assetBase . '/images/double-happiness.webp',
        $assetBase . '/images/cake.webp',
        $assetBase . '/images/music.webp',
    ];

    $dresscodeNote = trim((string) ($extraData['dresscode_note'] ?? 'Áo dài hoặc trang phục dự tiệc thanh lịch theo tông đỏ son, vàng champagne, kem ngà và nâu trầm'));
    $defaultColors = ['#8c0408', '#ffd390', '#ffe8c4', '#540103'];
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
    $giftNote = trim((string) ($extraData['gift_note'] ?? 'Nhấn vào phong bao Long Phụng bên dưới để mở thông tin mừng cưới Nhà Trai & Nhà Gái'));
    $groomBankName = trim((string) ($extraData['groom_bank_name'] ?? 'Vietcombank'));
    $groomBankAccount = trim((string) ($extraData['groom_bank_account'] ?? '0071000812345'));
    $groomAccountName = trim((string) ($extraData['groom_account_name'] ?? ($extraData['groom_bank_owner'] ?? 'DANG QUOC BAO')));
    $brideBankName = trim((string) ($extraData['bride_bank_name'] ?? 'Techcombank'));
    $brideBankAccount = trim((string) ($extraData['bride_bank_account'] ?? '19035678912345'));
    $brideAccountName = trim((string) ($extraData['bride_account_name'] ?? ($extraData['bride_bank_owner'] ?? 'LUU KHANH VY')));

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
    $endingMessage = trim((string) ($extraData['ending_message'] ?? 'Sự hiện diện của Quý khách là niềm vinh hạnh lớn lao và là lời chúc phúc ý nghĩa nhất cho ngày chung đôi của hai gia đình.'));
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

@section('title', 'Thiệp Cưới ' . $groomName . ' & ' . $brideName . ' — Mâm Trầu Đỏ Chữ Duyên')
@section('body_class', 'mayhen_invitation_body_longphung' . ($isEmbed ? ' mayhen_embed_mode' : '') . ($autoOpened ? ' mayhen_template_auto_opened' : ''))

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

    {{-- Floating Gold Music Controller --}}
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
         OPENING CURTAIN: LONG PHỤNG ĐỎ SON HOÀNG GIA
         ================================================================ --}}
    <div id="mayhen_opening" class="mayhen_invitation_opening{{ $autoOpened ? ' is-opened' : '' }}" aria-hidden="{{ $autoOpened ? 'true' : 'false' }}">
        <div class="mayhen_invitation_open_hy_rain" aria-hidden="true">
            <span class="mayhen_invitation_open_hy mayhen_invitation_open_hy--1">囍</span>
            <span class="mayhen_invitation_open_hy mayhen_invitation_open_hy--2">囍</span>
            <span class="mayhen_invitation_open_hy mayhen_invitation_open_hy--3">囍</span>
            <span class="mayhen_invitation_open_hy mayhen_invitation_open_hy--4">囍</span>
        </div>

        <div class="mayhen_invitation_open_card">
            <img class="mayhen_invitation_open_phoenix" src="{{ $assetBase }}/images/phoenix.webp" alt="" aria-hidden="true">
            <img class="mayhen_invitation_open_dragon" src="{{ $assetBase }}/images/dragon.webp" alt="" aria-hidden="true">

            <div class="mayhen_invitation_open_inner">
                <div class="mayhen_invitation_open_seal">
                    <img src="{{ $assetBase }}/images/double-happiness.webp" alt="Song Hỷ">
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
         MAIN INVITATION SHEET — LONG PHỤNG V4 ĐỎ (MÂM TRẦU ĐỎ CHỮ DUYÊN)
         ================================================================ --}}
    <div class="mayhen_invitation_shell card-wrapper">
        <main class="mayhen_invitation_main">

            {{-- SECTION 1: HERO — PHOENIX & DRAGON ROYAL GOLD MEDALLION --}}
            <section id="mayhen_section_hero" class="mayhen_invitation_section mayhen_invitation_hero" data-mayhen-section="hero">
                <img class="mayhen_invitation_hero_songhy" src="{{ $assetBase }}/images/double-happiness.webp" alt="Song Hỷ">
                <img class="mayhen_invitation_hero_cloud" src="{{ $assetBase }}/images/cloud1.webp" alt="" aria-hidden="true">
                <img class="mayhen_invitation_hero_phoenix" src="{{ $assetBase }}/images/phoenix.webp" alt="" aria-hidden="true">
                <img class="mayhen_invitation_hero_dragon" src="{{ $assetBase }}/images/dragon.webp" alt="" aria-hidden="true">

                <div class="mayhen_invitation_hero_medallion mayhen_invitation_reveal">
                    <div class="mayhen_invitation_hero_photo_circle">
                        <img src="{{ $heroPhoto }}" alt="{{ $groomName }} & {{ $brideName }}" data-sync-target="hero_photo_url" loading="eager">
                    </div>
                    <img class="mayhen_invitation_hero_frame_ring" src="{{ $assetBase }}/images/frame-avatar.webp" alt="" aria-hidden="true">
                </div>

                <div class="mayhen_invitation_hero_names_wrap mayhen_invitation_reveal">
                    <span class="mayhen_invitation_hero_ghost_amp" aria-hidden="true">&amp;</span>
                    <h1 class="mayhen_invitation_hero_names">
                        <span data-sync-target="groom_name">{{ mb_strtoupper($groomName, 'UTF-8') }}</span>
                        <span data-sync-target="bride_name">{{ mb_strtoupper($brideName, 'UTF-8') }}</span>
                    </h1>
                </div>
            </section>

            {{-- SECTION 2: PARENTS & FORMAL ANNOUNCEMENT --}}
            <section id="mayhen_section_parents" class="mayhen_invitation_section mayhen_invitation_parents mayhen_invitation_reveal" data-mayhen-section="parents">
                <img class="mayhen_invitation_bg_emblem mayhen_invitation_bg_emblem--l" src="{{ $assetBase }}/images/phoenix2.webp" alt="" aria-hidden="true">
                <img class="mayhen_invitation_bg_emblem mayhen_invitation_bg_emblem--r" src="{{ $assetBase }}/images/dragon2.webp" alt="" aria-hidden="true">

                <div class="mayhen_invitation_royal_box_title">
                    <h2>THÔNG TIN LỄ CƯỚI</h2>
                </div>

                <div class="mayhen_invitation_parents_grid">
                    <div class="mayhen_invitation_parent_col">
                        <span class="mayhen_invitation_parent_kicker">Ông Bà</span>
                        <p class="mayhen_invitation_parent_name" data-sync-target="groom_father">{{ $groomFather }}</p>
                        <p class="mayhen_invitation_parent_name" data-sync-target="groom_mother">{{ $groomMother }}</p>
                        <p class="mayhen_invitation_parent_addr" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                    </div>
                    <div class="mayhen_invitation_parent_col">
                        <span class="mayhen_invitation_parent_kicker">Ông Bà</span>
                        <p class="mayhen_invitation_parent_name" data-sync-target="bride_father">{{ $brideFather }}</p>
                        <p class="mayhen_invitation_parent_name" data-sync-target="bride_mother">{{ $brideMother }}</p>
                        <p class="mayhen_invitation_parent_addr" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                    </div>
                </div>

                <div class="mayhen_invitation_announce_block">
                    <p class="mayhen_invitation_announce_kicker">TRÂN TRỌNG BÁO TIN</p>
                    <p class="mayhen_invitation_announce_sub" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>

                    <h3 class="mayhen_invitation_script_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                    <span class="mayhen_invitation_role_tag" data-sync-target="groom_role">{{ mb_strtoupper($groomRole, 'UTF-8') }}</span>

                    <div class="mayhen_invitation_script_amp" aria-hidden="true">&amp;</div>

                    <h3 class="mayhen_invitation_script_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                    <span class="mayhen_invitation_role_tag" data-sync-target="bride_role">{{ mb_strtoupper($brideRole, 'UTF-8') }}</span>
                </div>

                <div class="mayhen_invitation_ceremony_block">
                    <p class="mayhen_invitation_ceremony_heading">LỄ THÀNH HÔN ĐƯỢC CỬ HÀNH TẠI<br>TƯ GIA</p>
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
            </section>

            {{-- SECTION 3: EVENTS, PARTY & GOOGLE MAPS --}}
            <section id="mayhen_section_events" class="mayhen_invitation_section mayhen_invitation_events mayhen_invitation_reveal" data-mayhen-section="events">
                <img class="mayhen_invitation_cloud_accent mayhen_invitation_cloud_accent--l" src="{{ $assetBase }}/images/cloud2.webp" alt="" aria-hidden="true">
                <img class="mayhen_invitation_cloud_accent mayhen_invitation_cloud_accent--r" src="{{ $assetBase }}/images/cloud2b.webp" alt="" aria-hidden="true">

                <div class="mayhen_invitation_royal_box_title">
                    <h2>THÔNG TIN TIỆC CƯỚI</h2>
                </div>

                <div class="mayhen_invitation_party_times">
                    <div class="mayhen_invitation_party_card">
                        <span>ĐÓN KHÁCH</span>
                        <strong data-sync-target="welcome_time">{{ $welcomeTime }}</strong>
                    </div>
                    <div class="mayhen_invitation_party_card">
                        <span>KHAI TIỆC</span>
                        <strong data-sync-target="party_time">{{ $partyTime }}</strong>
                    </div>
                </div>

                <div class="mayhen_invitation_venue_box">
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
                        <a href="{{ $venueMapUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_gold_outline_btn" data-sync-target="venue_map_url">Chỉ đường Google Maps</a>
                        <a href="#mayhen_rsvp_form" class="mayhen_invitation_gold_btn">XÁC NHẬN THAM DỰ</a>
                    </div>
                </div>
            </section>

            {{-- SECTION 4: ROYAL HEART CALENDAR & COUNTDOWN --}}
            <section id="mayhen_section_countdown" class="mayhen_invitation_section mayhen_invitation_calendar_sec mayhen_invitation_reveal" data-mayhen-section="countdown_calendar">
                <div class="mayhen_invitation_royal_box_title">
                    <h2>CÙNG ĐẾM NGƯỢC</h2>
                </div>

                <div class="mayhen_invitation_countdown_grid" id="mayhen_countdown">
                    <div class="mayhen_invitation_cd_box"><strong data-cd="days">00</strong><span>Ngày</span></div>
                    <div class="mayhen_invitation_cd_box"><strong data-cd="hours">00</strong><span>Giờ</span></div>
                    <div class="mayhen_invitation_cd_box"><strong data-cd="minutes">00</strong><span>Phút</span></div>
                    <div class="mayhen_invitation_cd_box"><strong data-cd="seconds">00</strong><span>Giây</span></div>
                </div>

                <div class="mayhen_invitation_cal_card">
                    <div class="mayhen_invitation_cal_head" id="mayhen_cal_title">Tháng {{ $wMonthNoZero }} / {{ $wYear }}</div>
                    <div class="mayhen_invitation_cal_weekdays">
                        <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                    </div>
                    <div class="mayhen_invitation_cal_grid" id="mayhen_cal_grid" data-heart-url="{{ $assetBase }}/images/heart.svg"></div>
                    <a href="{{ $gcalUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_gcal_link">Lưu vào Google Calendar</a>
                </div>
            </section>

            {{-- SECTION 5: LOVE STORY --}}
            <section id="mayhen_section_story" class="mayhen_invitation_section mayhen_invitation_story mayhen_invitation_reveal" data-mayhen-section="story">
                <div class="mayhen_invitation_royal_box_title">
                    <h2 data-sync-target="story_title">{{ $storyTitle }}</h2>
                </div>
                <p class="mayhen_invitation_section_sub" data-sync-target="story_subtitle">{{ $storySubtitle }}</p>

                <div class="mayhen_invitation_royal_card">
                    <div class="mayhen_invitation_story_photo">
                        <img src="{{ $saveDatePhoto }}" alt="Chuyện tình yêu" data-sync-target="save_date_photo_url" loading="lazy">
                    </div>
                    <blockquote class="mayhen_invitation_story_quote" data-sync-target="couple_quote">{{ $coupleQuote }}</blockquote>
                    <p class="mayhen_invitation_story_body" data-sync-target="story_content">{{ $storyContent }}</p>
                </div>
            </section>

            @if (!$isCuratedSeven)
                {{-- SECTION 6: TIMELINE WITH ROYAL ICONS --}}
                <section id="mayhen_section_timeline" class="mayhen_invitation_section mayhen_invitation_timeline mayhen_invitation_reveal" data-mayhen-section="timeline">
                    <div class="mayhen_invitation_royal_box_title">
                        <h2>LỊCH TRÌNH NGÀY CƯỚI</h2>
                    </div>
                    <div class="mayhen_invitation_timeline_list" data-sync-target="timeline">
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
                    <div class="mayhen_invitation_royal_card">
                        <h2 class="mayhen_invitation_gold_heading">DRESS CODE</h2>
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
                <div class="mayhen_invitation_royal_box_title">
                    <h2>ALBUM ẢNH CƯỚI</h2>
                </div>

                <div class="mayhen_invitation_album_grid">
                    @foreach ($galleryPhotos as $idx => $photo)
                        <button type="button" class="mayhen_invitation_album_item{{ ($idx === 2 || $idx === 7) ? ' mayhen_invitation_album_item--wide' : '' }}" data-album-index="{{ $idx }}" aria-label="Xem ảnh cưới {{ $idx + 1 }}">
                            <img src="{{ $photo }}" alt="Ảnh cưới {{ $idx + 1 }}" data-sync-target="gallery.{{ $idx }}" loading="{{ $idx < 2 ? 'eager' : 'lazy' }}">
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- SECTION 9: LONG PHỤNG RED ENVELOPE GIFT & DYNAMIC VIETQR --}}
            <section id="mayhen_section_gift" class="mayhen_invitation_section mayhen_invitation_gift mayhen_invitation_reveal" data-mayhen-section="gift">
                <div class="mayhen_invitation_royal_box_title">
                    <h2>MỪNG CƯỚI ĐÔI UYÊN ƯƠNG</h2>
                </div>
                <p class="mayhen_invitation_section_sub" data-sync-target="gift_note">{{ $giftNote }}</p>

                <div class="mayhen_invitation_gift_stage">
                    <button type="button" id="mayhen_gift_toggle" class="mayhen_invitation_gift_btn" aria-expanded="false" aria-controls="mayhen_gift_panel">
                        <img class="mayhen_invitation_gift_main" src="{{ $assetBase }}/images/envelope-red.webp" alt="Phong bao mừng cưới Long Phụng" loading="lazy">
                        <span class="mayhen_invitation_gold_btn">✦ Nhấn để mở mừng cưới ✦</span>
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

            {{-- SECTION 10: SIGNATURE, RSVP, WISHES & ROYAL FINALE (LAW #8: ZERO HUMAN PHOTOS IN SIGNATURE/RSVP) --}}
            <section id="mayhen_section_ending" class="mayhen_invitation_section mayhen_invitation_ending mayhen_invitation_reveal" data-mayhen-section="ending">

                {{-- 10A. Signature Book (Pure CSS Crimson & Gold — Zero Photos) --}}
                <div class="mayhen_invitation_royal_card">
                    <div class="mayhen_invitation_wax_badge" aria-hidden="true">囍</div>
                    <h2 class="mayhen_invitation_gold_heading">SỔ KÝ TÊN LƯU BÚT</h2>
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
                            <button type="button" class="mayhen_invitation_sig_dot is-active" data-sig-color="#8c0408" style="background:#8c0408;" aria-label="Mực đỏ son"></button>
                            <button type="button" class="mayhen_invitation_sig_dot" data-sig-color="#b87b1e" style="background:#b87b1e;" aria-label="Mực vàng đồng"></button>
                            <button type="button" class="mayhen_invitation_sig_dot" data-sig-color="#1f1a17" style="background:#1f1a17;" aria-label="Mực đen"></button>
                        </div>
                        <div class="mayhen_invitation_sig_btns">
                            <button type="button" id="mayhen_sig_clear" class="mayhen_invitation_gold_outline_btn mayhen_invitation_btn_sm">Xóa</button>
                            <button type="button" id="mayhen_sig_download" class="mayhen_invitation_gold_outline_btn mayhen_invitation_btn_sm">Tải PNG</button>
                            <button type="button" id="mayhen_sig_save" class="mayhen_invitation_gold_btn mayhen_invitation_btn_sm">Lưu chữ ký</button>
                        </div>
                    </div>
                    <p id="mayhen_sig_status" class="mayhen_invitation_status_msg" role="status" aria-live="polite"></p>
                </div>

                {{-- 10B. RSVP Form (Pure CSS Crimson Lacquer Frame — Zero Photos) --}}
                <form id="mayhen_rsvp_form" class="mayhen_invitation_royal_card mayhen_invitation_rsvp_card" novalidate>
                    <div class="mayhen_invitation_wax_badge" aria-hidden="true">❦</div>
                    <h2 class="mayhen_invitation_gold_heading">XÁC NHẬN THAM DỰ</h2>

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

                    <button type="submit" id="mayhen_rsvp_submit" class="mayhen_invitation_gold_btn mayhen_invitation_btn_full">GỬI XÁC NHẬN</button>
                    <p id="mayhen_rsvp_status" class="mayhen_invitation_status_msg" role="status" aria-live="polite"></p>
                </form>

                {{-- 10C. Guestbook Wishes --}}
                <div class="mayhen_invitation_royal_card">
                    <h2 class="mayhen_invitation_gold_heading">SỔ LỜI CHÚC</h2>
                    <form id="mayhen_wish_form" class="mayhen_invitation_wish_form" novalidate>
                        <input type="text" id="mayhen_wish_name" class="mayhen_invitation_input" placeholder="Tên của bạn *" required>
                        <textarea id="mayhen_wish_msg" rows="3" class="mayhen_invitation_input" placeholder="Viết lời chúc tốt đẹp nhất gửi tới cô dâu chú rể *" required></textarea>
                        <button type="submit" id="mayhen_wish_submit" class="mayhen_invitation_gold_btn mayhen_invitation_btn_full">GỬI LỜI CHÚC</button>
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
                                <p>Chúc đôi uyên ương trăm năm hạnh phúc, duyên thắm mặn nồng!</p>
                            </article>
                        @endforelse
                    </div>
                </div>

                {{-- 10D. Royal Finale & Cloud Footer --}}
                <div class="mayhen_invitation_finale">
                    <div class="mayhen_invitation_finale_photo">
                        <img src="{{ $footerPhoto }}" alt="Cảm ơn" data-sync-target="footer_photo_url" loading="lazy">
                    </div>
                    <p class="mayhen_invitation_finale_thank">Xin Trân Trọng Cảm Ơn!</p>
                    <p class="mayhen_invitation_finale_msg" data-sync-target="ending_message">{{ $endingMessage }}</p>
                    <p class="mayhen_invitation_finale_names">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <span>&amp;</span>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </p>
                    <img class="mayhen_invitation_finale_cloud" src="{{ $assetBase }}/images/cloud4-bottom.webp" alt="" aria-hidden="true" loading="lazy">
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
