@extends('client.layouts.invitation')

@php
    use App\Services\VietQrService;

    $tplSlug = $template->slug ?? 'mot-mua-he-ben-nhau';
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
    $groomName = trim((string) ($wedding?->groom_name ?? ($extraData['groom_name'] ?? 'Hoàng Nam')));
    if ($groomName === '') {
        $groomName = 'Hoàng Nam';
    }
    $brideName = trim((string) ($wedding?->bride_name ?? ($extraData['bride_name'] ?? 'Thanh Tú')));
    if ($brideName === '') {
        $brideName = 'Thanh Tú';
    }
    $groomRole = trim((string) ($extraData['groom_role'] ?? 'Út Nam'));
    $brideRole = trim((string) ($extraData['bride_role'] ?? 'Thứ Nữ'));

    $guestNameRaw = trim((string) ($guest?->name ?? ''));
    $displayGuestName = $guestNameRaw !== '' ? $guestNameRaw : 'Quý khách & Gia đình';

    // 2. Date & Time (Asia/Ho_Chi_Minh)
    $rawWeddingDate = trim((string) ($wedding?->wedding_date?->format('Y-m-d') ?? ($wedding?->wedding_date ?? ($extraData['wedding_date'] ?? '2027-05-10'))));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawWeddingDate)) {
        $rawWeddingDate = '2027-05-10';
    }
    $ceremonyTime = trim((string) ($extraData['ceremony_time'] ?? '09:00'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $ceremonyTime)) {
        $ceremonyTime = '09:00';
    }
    $welcomeTime = trim((string) ($extraData['welcome_time'] ?? '10:00'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $welcomeTime)) {
        $welcomeTime = '10:00';
    }
    $partyTime = trim((string) ($extraData['party_time'] ?? '10:30'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $partyTime)) {
        $partyTime = '10:30';
    }
    $targetTime = trim((string) ($extraData['event_target_time'] ?? $partyTime));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $targetTime)) {
        $targetTime = '10:30';
    }

    $dateTs = strtotime($rawWeddingDate . ' ' . $targetTime . ':00 +0700') ?: strtotime('2027-05-10 10:30:00 +0700');
    $wDay = date('d', $dateTs);
    $wDayNoZero = (string) (int) $wDay;
    $wMonth = date('m', $dateTs);
    $wMonthNoZero = (string) (int) $wMonth;
    $wYear = date('Y', $dateTs);

    $weekdaysVi = ['Chủ Nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
    $wWeekday = $weekdaysVi[(int) date('w', $dateTs)] ?? 'Chủ Nhật';
    $wWeekdayUpper = mb_strtoupper($wWeekday, 'UTF-8');
    $openingDateText = $wDayNoZero . ' tháng ' . $wMonthNoZero . ', ' . $wYear;
    $targetIso = $rawWeddingDate . 'T' . $targetTime . ':00+07:00';

    $lunarDate = trim((string) ($extraData['wedding_lunar_date'] ?? '(TỨC NGÀY 24 THÁNG 3 NĂM ĐINH MÙI)'));
    $ceremonyType = trim((string) ($extraData['ceremony_type'] ?? 'LỄ THÀNH HÔN CỦA CON CHÚNG TÔI'));

    // 3. Photos & Media (100% Local Assets)
    $groomPhoto = $resolveAsset($extraData['groom_photo_url'] ?? '', $assetBase . '/images/groom-portrait.webp');
    $bridePhoto = $resolveAsset($extraData['bride_photo_url'] ?? '', $assetBase . '/images/bride-portrait.webp');
    $heroPhoto = $resolveAsset(
        $wedding?->cover_photo_url ?? ($extraData['hero_photo_url'] ?? ($extraData['cover_photo_url'] ?? '')),
        $assetBase . '/images/hero-couple.webp'
    );
    $saveDatePhoto = $resolveAsset($extraData['save_date_photo_url'] ?? '', $assetBase . '/images/save-date.webp');
    $footerPhoto = $resolveAsset($extraData['footer_photo_url'] ?? '', $assetBase . '/images/footer-couple.webp');

    // 4. Parents & Families
    $groomFather = trim((string) ($extraData['groom_father'] ?? 'Phạm Văn Toàn'));
    $groomMother = trim((string) ($extraData['groom_mother'] ?? 'Nguyễn Thị Thu'));
    $groomParentsAddress = trim((string) ($extraData['groom_parents_address'] ?? '45 Đường Số 8, Hiệp Bình Chánh, Thủ Đức, TP.HCM'));

    $brideFather = trim((string) ($extraData['bride_father'] ?? 'Lê Văn Phúc'));
    $brideMother = trim((string) ($extraData['bride_mother'] ?? 'Võ Thị Kim Oanh'));
    $brideParentsAddress = trim((string) ($extraData['bride_parents_address'] ?? '120 Đinh Tiên Hoàng, Phường 3, Bình Thạnh, TP.HCM'));

    // 5. Love Story
    $storyTitle = trim((string) ($extraData['story_title'] ?? 'MỘT MÙA HÈ BÊN NHAU'));
    $storySubtitle = trim((string) ($extraData['story_subtitle'] ?? 'Từ nắng hạ dịu dàng đến lời hẹn ước trăm năm'));
    $coupleQuote = trim((string) ($extraData['couple_quote'] ?? '“Giữa muôn vạn mùa hạ đi qua, bình yên nhất vẫn là có nhau nắm tay đi đến cuối con đường.”'));
    $storyContent = trim((string) ($extraData['story_content'] ?? 'Chúng mình gặp nhau vào một ngày nắng nhẹ, để rồi từng khoảnh khắc bên nhau đều trở thành kỷ niệm ngọt ngào. Cảm ơn bạn đã luôn đồng hành và đến chung vui trong ngày trọng đại này.'));

    // 6. Venue, Maps & Google Calendar
    $venueName = trim((string) ($wedding?->venue_name ?? ($extraData['venue_name'] ?? 'Tư Gia & Sảnh Tiệc Hoa Mộc')));
    if ($venueName === '') {
        $venueName = 'Tư Gia & Sảnh Tiệc Hoa Mộc';
    }
    $venueAddress = trim((string) ($wedding?->venue_address ?? ($extraData['venue_address'] ?? '45 Đường Số 8, Hiệp Bình Chánh, Thủ Đức, TP.HCM')));
    if ($venueAddress === '') {
        $venueAddress = '45 Đường Số 8, Hiệp Bình Chánh, Thủ Đức, TP.HCM';
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
        ['time' => '09:00', 'title' => 'Lễ Thành Hôn tại Tư Gia'],
        ['time' => '10:00', 'title' => 'Đón khách & Chụp ảnh lưu niệm'],
        ['time' => '10:30', 'title' => 'Khai tiệc & Cử hành nghi lễ'],
        ['time' => '11:30', 'title' => 'Rót rượu, cắt bánh & Nâng ly'],
        ['time' => '13:30', 'title' => 'Giao lưu & Kết thúc tiệc'],
    ];
    $timelineItems = is_array($extraData['timeline'] ?? null) && count($extraData['timeline']) > 0
        ? $extraData['timeline']
        : $defaultTimeline;

    $dresscodeNote = trim((string) ($extraData['dresscode_note'] ?? 'Trang phục thanh lịch theo tông nâu gỗ, kem sữa, be mộc và hồng đất'));
    $defaultColors = ['#4a2816', '#8a6c5a', '#d9c2b2', '#fdf8f5'];
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

    // 9. Gift Box & Dynamic VietQR
    $giftNote = trim((string) ($extraData['gift_note'] ?? 'Nhấn vào hộp quà bên dưới để mở thông tin mừng cưới Nhà Trai & Nhà Gái'));
    $groomBankName = trim((string) ($extraData['groom_bank_name'] ?? 'TPBank'));
    $groomBankAccount = trim((string) ($extraData['groom_bank_account'] ?? '4109876543'));
    $groomAccountName = trim((string) ($extraData['groom_account_name'] ?? ($extraData['groom_bank_owner'] ?? 'PHAM HOANG NAM')));
    $brideBankName = trim((string) ($extraData['bride_bank_name'] ?? 'Vietcombank'));
    $brideBankAccount = trim((string) ($extraData['bride_bank_account'] ?? '0987654321'));
    $brideAccountName = trim((string) ($extraData['bride_account_name'] ?? ($extraData['bride_bank_owner'] ?? 'LE THANH TU')));

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
    $endingMessage = trim((string) ($extraData['ending_message'] ?? 'Sự hiện diện của Quý khách là niềm vinh hạnh lớn lao và là món quà tuyệt vời nhất trong ngày chung đôi của chúng tôi.'));
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

@section('title', 'Thiệp Cưới ' . $groomName . ' & ' . $brideName . ' — Một Mùa Hè Bên Nhau')
@section('body_class', 'mayhen_invitation_body_hoamoc' . ($isEmbed ? ' mayhen_embed_mode' : '') . ($autoOpened ? ' mayhen_template_auto_opened' : ''))

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

    {{-- Floating Music Controller --}}
    <button type="button" id="mayhen_music_toggle" class="mayhen_invitation_music_btn" aria-label="Bật hoặc tắt nhạc nền" aria-pressed="false">
        <svg class="mayhen_invitation_music_icon_muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 18V5l12-2v13"></path>
            <circle cx="6" cy="18" r="3"></circle>
            <circle cx="18" cy="16" r="3"></circle>
            <line x1="2" y1="2" x2="22" y2="22"></line>
        </svg>
        <svg class="mayhen_invitation_music_icon_note" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <line x1="6" y1="10" x2="6" y2="18"></line>
            <line x1="10" y1="6" x2="10" y2="18"></line>
            <line x1="14" y1="11" x2="14" y2="18"></line>
            <line x1="18" y1="8" x2="18" y2="18"></line>
        </svg>
    </button>

    {{-- ================================================================
         OPENING CURTAIN: BOHO RUSTIC BROWN ENVELOPE
         ================================================================ --}}
    <div id="mayhen_opening" class="mayhen_invitation_opening{{ $autoOpened ? ' is-opened' : '' }}" aria-hidden="{{ $autoOpened ? 'true' : 'false' }}">
        <div class="mayhen_invitation_open_leaves" aria-hidden="true">
            <span class="mayhen_invitation_open_leaf mayhen_invitation_open_leaf--1"></span>
            <span class="mayhen_invitation_open_leaf mayhen_invitation_open_leaf--2"></span>
            <span class="mayhen_invitation_open_leaf mayhen_invitation_open_leaf--3"></span>
        </div>

        <div class="mayhen_invitation_open_card">
            <img class="mayhen_invitation_open_floral mayhen_invitation_open_floral--top" src="{{ $assetBase }}/images/flower-top.webp" alt="" aria-hidden="true">
            <img class="mayhen_invitation_open_floral mayhen_invitation_open_floral--bottom" src="{{ $assetBase }}/images/flower-bottom.webp" alt="" aria-hidden="true">

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
                <p class="mayhen_invitation_open_invite_lbl">Kính Mời</p>
                <div class="mayhen_invitation_open_guest_pill" data-sync-target="guest_name">{{ $displayGuestName }}</div>
                <p class="mayhen_invitation_open_invite_sub">đến dự buổi tiệc chung vui cùng gia đình</p>

                <button type="button" id="mayhen_open_btn" class="mayhen_invitation_open_btn">Mở thiệp</button>
            </div>
        </div>
    </div>

    {{-- ================================================================
         MAIN INVITATION SHEET — BOHO FLORAL BROWN (HOA MỘC NÂU)
         ================================================================ --}}
    <div class="mayhen_invitation_shell card-wrapper">
        <main class="mayhen_invitation_main">

            {{-- SECTION 1: HERO — BOHO WATERCOLOR CROWN + DUAL TILTED PORTRAITS + RIBBON --}}
            <section id="mayhen_section_hero" class="mayhen_invitation_section mayhen_invitation_hero" data-mayhen-section="hero">
                <img class="mayhen_invitation_hero_crown" src="{{ $assetBase }}/images/flower-top.webp" alt="" aria-hidden="true">

                <div class="mayhen_invitation_hero_stage">
                    <img class="mayhen_invitation_hero_ribbon" src="{{ $assetBase }}/images/decoration-bar.webp" alt="" aria-hidden="true">

                    {{-- Groom Row: Tilted Portrait Left + Role & Name Right --}}
                    <div class="mayhen_invitation_hero_row mayhen_invitation_hero_row--groom mayhen_invitation_reveal">
                        <div class="mayhen_invitation_hero_polaroid mayhen_invitation_hero_polaroid--groom">
                            <img src="{{ $groomPhoto }}" alt="{{ $groomName }}" data-sync-target="groom_photo_url" loading="eager">
                        </div>
                        <div class="mayhen_invitation_hero_meta mayhen_invitation_hero_meta--groom">
                            <span class="mayhen_invitation_hero_role" data-sync-target="groom_role">{{ $groomRole }}</span>
                            <h1 class="mayhen_invitation_hero_name" data-sync-target="groom_name">{{ $groomName }}</h1>
                        </div>
                    </div>

                    {{-- Bride Row: Role & Name Left + Tilted Portrait Right --}}
                    <div class="mayhen_invitation_hero_row mayhen_invitation_hero_row--bride mayhen_invitation_reveal">
                        <div class="mayhen_invitation_hero_meta mayhen_invitation_hero_meta--bride">
                            <span class="mayhen_invitation_hero_role" data-sync-target="bride_role">{{ $brideRole }}</span>
                            <h2 class="mayhen_invitation_hero_name" data-sync-target="bride_name">{{ $brideName }}</h2>
                        </div>
                        <div class="mayhen_invitation_hero_polaroid mayhen_invitation_hero_polaroid--bride">
                            <img src="{{ $bridePhoto }}" alt="{{ $brideName }}" data-sync-target="bride_photo_url" loading="eager">
                        </div>
                    </div>
                </div>

                <img class="mayhen_invitation_hero_foot_flower" src="{{ $assetBase }}/images/flower-bottom.webp" alt="" aria-hidden="true">
            </section>

            {{-- SECTION 2: PARENTS & FORMAL ANNOUNCEMENT (WATERMARK LEAF BACKDROP) --}}
            <section id="mayhen_section_parents" class="mayhen_invitation_section mayhen_invitation_parents mayhen_invitation_reveal" data-mayhen-section="parents">
                <img class="mayhen_invitation_watermark_leaf" src="{{ $assetBase }}/images/fixed-flower.webp" alt="" aria-hidden="true">
                <img class="mayhen_invitation_parents_mid_flower" src="{{ $assetBase }}/images/flower-mid.webp" alt="" aria-hidden="true">

                <div class="mayhen_invitation_parents_inner">
                    <h2 class="mayhen_invitation_brown_title">THÔNG TIN LỄ CƯỚI</h2>

                    <div class="mayhen_invitation_parents_grid">
                        <div class="mayhen_invitation_parent_col">
                            <span class="mayhen_invitation_parent_kicker">Ông bà</span>
                            <p class="mayhen_invitation_parent_name" data-sync-target="groom_father">{{ $groomFather }}</p>
                            <p class="mayhen_invitation_parent_name" data-sync-target="groom_mother">{{ $groomMother }}</p>
                            <p class="mayhen_invitation_parent_addr" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                        </div>
                        <div class="mayhen_invitation_parent_col">
                            <span class="mayhen_invitation_parent_kicker">Ông bà</span>
                            <p class="mayhen_invitation_parent_name" data-sync-target="bride_father">{{ $brideFather }}</p>
                            <p class="mayhen_invitation_parent_name" data-sync-target="bride_mother">{{ $brideMother }}</p>
                            <p class="mayhen_invitation_parent_addr" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                        </div>
                    </div>

                    <div class="mayhen_invitation_announce_block">
                        <p class="mayhen_invitation_announce_kicker">TRÂN TRỌNG BÁO TIN</p>
                        <p class="mayhen_invitation_announce_sub" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>

                        <h3 class="mayhen_invitation_formal_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                        <span class="mayhen_invitation_formal_role" data-sync-target="groom_role">{{ mb_strtoupper($groomRole, 'UTF-8') }}</span>

                        <div class="mayhen_invitation_formal_amp" aria-hidden="true">&amp;</div>

                        <h3 class="mayhen_invitation_formal_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                        <span class="mayhen_invitation_formal_role" data-sync-target="bride_role">{{ mb_strtoupper($brideRole, 'UTF-8') }}</span>
                    </div>

                    {{-- Ceremony Date Block inside Parents / Announcement flow --}}
                    <div class="mayhen_invitation_ceremony_block">
                        <p class="mayhen_invitation_ceremony_heading">LỄ THÀNH HÔN ĐƯỢC CỬ HÀNH TẠI<br>TƯ GIA</p>
                        <p class="mayhen_invitation_ceremony_at">VÀO LÚC</p>
                        <p class="mayhen_invitation_ceremony_time" data-sync-target="ceremony_time">{{ $ceremonyTime }}</p>

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

            {{-- SECTION 3: 10-PHOTO ALBUM — 3D COVERFLOW CAROUSEL + THUMBNAIL GRID + LIGHTBOX --}}
            <section id="mayhen_section_album" class="mayhen_invitation_section mayhen_invitation_album mayhen_invitation_reveal" data-mayhen-section="album">
                <h2 class="mayhen_invitation_brown_title">ALBUM ẢNH</h2>

                <div class="mayhen_invitation_coverflow" id="mayhen_coverflow">
                    <button type="button" class="mayhen_invitation_cf_nav mayhen_invitation_cf_nav--prev" id="mayhen_cf_prev" aria-label="Ảnh trước">‹</button>
                    <div class="mayhen_invitation_cf_stage" id="mayhen_cf_stage">
                        @foreach ($galleryPhotos as $idx => $photo)
                            <figure class="mayhen_invitation_cf_slide{{ $idx === 0 ? ' is-active' : '' }}" data-cf-index="{{ $idx }}">
                                <img src="{{ $photo }}" alt="Ảnh cưới {{ $idx + 1 }}" data-sync-target="gallery.{{ $idx }}" loading="{{ $idx < 3 ? 'eager' : 'lazy' }}">
                            </figure>
                        @endforeach
                    </div>
                    <button type="button" class="mayhen_invitation_cf_nav mayhen_invitation_cf_nav--next" id="mayhen_cf_next" aria-label="Ảnh kế tiếp">›</button>
                </div>

                <p class="mayhen_invitation_cf_counter"><span id="mayhen_cf_current">1</span> / {{ count($galleryPhotos) }}</p>

                <div class="mayhen_invitation_album_thumbs">
                    @foreach ($galleryPhotos as $idx => $photo)
                        <button type="button" class="mayhen_invitation_album_item{{ $idx === 0 ? ' is-active' : '' }}" data-album-index="{{ $idx }}" aria-label="Xem ảnh cưới {{ $idx + 1 }}">
                            <img src="{{ $photo }}" alt="Ảnh cưới nhỏ {{ $idx + 1 }}" loading="lazy">
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- SECTION 4: PARTY EVENTS & GOOGLE MAPS --}}
            <section id="mayhen_section_events" class="mayhen_invitation_section mayhen_invitation_events mayhen_invitation_reveal" data-mayhen-section="events">
                <img class="mayhen_invitation_watermark_leaf mayhen_invitation_watermark_leaf--events" src="{{ $assetBase }}/images/fixed-flower.webp" alt="" aria-hidden="true">

                <div class="mayhen_invitation_events_inner">
                    <h2 class="mayhen_invitation_brown_title">THÔNG TIN TIỆC CƯỚI</h2>
                    <p class="mayhen_invitation_party_lead">TIỆC CƯỚI SẼ DIỄN RA VÀO LÚC:</p>
                    <p class="mayhen_invitation_ceremony_time" data-sync-target="party_time">{{ $partyTime }}</p>

                    <div class="mayhen_invitation_date_bar">
                        <span class="mayhen_invitation_date_weekday" data-sync-target="wedding_weekday">{{ $wWeekdayUpper }}</span>
                        <span class="mayhen_invitation_date_pipe" aria-hidden="true"></span>
                        <span class="mayhen_invitation_date_day" data-sync-target="wedding_day">{{ $wDay }}</span>
                        <span class="mayhen_invitation_date_pipe" aria-hidden="true"></span>
                        <span class="mayhen_invitation_date_month" data-sync-target="wedding_month">THÁNG {{ $wMonth }}</span>
                    </div>

                    <p class="mayhen_invitation_date_year" data-sync-target="wedding_year">{{ $wYear }}</p>
                    <p class="mayhen_invitation_date_lunar" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</p>

                    <div class="mayhen_invitation_party_times">
                        <div class="mayhen_invitation_party_time_col">
                            <span class="mayhen_invitation_party_time_lbl">ĐÓN KHÁCH</span>
                            <strong class="mayhen_invitation_party_time_val" data-sync-target="welcome_time">{{ $welcomeTime }}</strong>
                        </div>
                        <div class="mayhen_invitation_party_time_col">
                            <span class="mayhen_invitation_party_time_lbl">KHAI TIỆC</span>
                            <strong class="mayhen_invitation_party_time_val" data-sync-target="party_time">{{ $partyTime }}</strong>
                        </div>
                    </div>

                    <div class="mayhen_invitation_venue_box">
                        <p class="mayhen_invitation_venue_kicker">Tiệc cưới sẽ được tổ chức tại</p>
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

                        <a href="{{ $venueMapUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_map_btn" data-sync-target="venue_map_url">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>Chỉ đường</span>
                        </a>
                    </div>
                </div>
            </section>

            {{-- SECTION 5: RUSTIC HEART CALENDAR & COUNTDOWN --}}
            <section id="mayhen_section_countdown" class="mayhen_invitation_section mayhen_invitation_calendar_sec mayhen_invitation_reveal" data-mayhen-section="countdown_calendar">
                <img class="mayhen_invitation_cal_flower" src="{{ $assetBase }}/images/flower-mid.webp" alt="" aria-hidden="true">

                <div class="mayhen_invitation_cal_card">
                    <div class="mayhen_invitation_cal_head" id="mayhen_cal_title">Tháng {{ $wMonthNoZero }} / {{ $wYear }}</div>
                    <div class="mayhen_invitation_cal_weekdays">
                        <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                    </div>
                    <div class="mayhen_invitation_cal_grid" id="mayhen_cal_grid"></div>
                </div>

                <div class="mayhen_invitation_cal_actions">
                    <a href="{{ $gcalUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_gcal_link">Thêm vào lịch</a>
                    <a href="#mayhen_rsvp_form" class="mayhen_invitation_primary_btn">XÁC NHẬN THAM DỰ</a>
                </div>

                <div class="mayhen_invitation_countdown_wrap">
                    <p class="mayhen_invitation_countdown_lbl">CÙNG ĐẾM NGƯỢC ĐẾN NGÀY HẠNH PHÚC</p>
                    <div class="mayhen_invitation_countdown_grid" id="mayhen_countdown">
                        <div class="mayhen_invitation_cd_box"><strong data-cd="days">00</strong><span>Ngày</span></div>
                        <div class="mayhen_invitation_cd_box"><strong data-cd="hours">00</strong><span>Giờ</span></div>
                        <div class="mayhen_invitation_cd_box"><strong data-cd="minutes">00</strong><span>Phút</span></div>
                        <div class="mayhen_invitation_cd_box"><strong data-cd="seconds">00</strong><span>Giây</span></div>
                    </div>
                </div>
            </section>

            {{-- SECTION 6: LOVE STORY --}}
            <section id="mayhen_section_story" class="mayhen_invitation_section mayhen_invitation_story mayhen_invitation_reveal" data-mayhen-section="story">
                <h2 class="mayhen_invitation_brown_title" data-sync-target="story_title">{{ $storyTitle }}</h2>
                <p class="mayhen_invitation_section_sub" data-sync-target="story_subtitle">{{ $storySubtitle }}</p>

                <div class="mayhen_invitation_story_card">
                    <div class="mayhen_invitation_story_photo_wrap">
                        <img src="{{ $saveDatePhoto }}" alt="Chuyện tình yêu" data-sync-target="save_date_photo_url" loading="lazy">
                    </div>
                    <blockquote class="mayhen_invitation_story_quote" data-sync-target="couple_quote">{{ $coupleQuote }}</blockquote>
                    <p class="mayhen_invitation_story_body" data-sync-target="story_content">{{ $storyContent }}</p>
                </div>
            </section>

            @if (!$isCuratedSeven)
                {{-- SECTION 7: DRESS CODE --}}
                <section id="mayhen_section_dresscode" class="mayhen_invitation_section mayhen_invitation_dresscode mayhen_invitation_reveal" data-mayhen-section="dresscode">
                    <div class="mayhen_invitation_rustic_card">
                        <h2 class="mayhen_invitation_brown_title">DRESS CODE</h2>
                        <p class="mayhen_invitation_section_sub" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>
                        <div class="mayhen_invitation_swatches">
                            @foreach ($dresscodeColors as $hex)
                                <span class="mayhen_invitation_swatch" style="background-color: {{ $hex }};" title="{{ $hex }}"></span>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- SECTION 8: WEDDING TIMELINE --}}
                <section id="mayhen_section_timeline" class="mayhen_invitation_section mayhen_invitation_timeline mayhen_invitation_reveal" data-mayhen-section="timeline">
                    <h2 class="mayhen_invitation_brown_title">LỊCH TRÌNH NGÀY CƯỚI</h2>
                    <div class="mayhen_invitation_timeline_list" data-sync-target="timeline">
                        @foreach ($timelineItems as $item)
                            <div class="mayhen_invitation_timeline_row">
                                <span class="mayhen_invitation_timeline_time">{{ $item['time'] ?? '' }}</span>
                                <span class="mayhen_invitation_timeline_dot" aria-hidden="true"></span>
                                <span class="mayhen_invitation_timeline_text">{{ $item['title'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- SECTION 9: 3D BOHO FLORAL BROWN GIFT BOX & DYNAMIC VIETQR --}}
            <section id="mayhen_section_gift" class="mayhen_invitation_section mayhen_invitation_gift mayhen_invitation_reveal" data-mayhen-section="gift">
                <h2 class="mayhen_invitation_brown_title">HỘP QUÀ MỪNG</h2>
                <p class="mayhen_invitation_section_sub" data-sync-target="gift_note">{{ $giftNote }}</p>

                <div class="mayhen_invitation_gift_stage">
                    <img class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--1" src="{{ $assetBase }}/images/gift-mini-1.webp" alt="" aria-hidden="true" loading="lazy">
                    <img class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--2" src="{{ $assetBase }}/images/gift-mini-2.webp" alt="" aria-hidden="true" loading="lazy">
                    <img class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--3" src="{{ $assetBase }}/images/gift-mini-3.webp" alt="" aria-hidden="true" loading="lazy">
                    <img class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--4" src="{{ $assetBase }}/images/gift-mini-4.webp" alt="" aria-hidden="true" loading="lazy">
                    <img class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--5" src="{{ $assetBase }}/images/gift-mini-5.webp" alt="" aria-hidden="true" loading="lazy">
                    <img class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--6" src="{{ $assetBase }}/images/gift-mini-6.webp" alt="" aria-hidden="true" loading="lazy">

                    <button type="button" id="mayhen_gift_toggle" class="mayhen_invitation_gift_btn" aria-expanded="false" aria-controls="mayhen_gift_panel">
                        <img class="mayhen_invitation_gift_main" src="{{ $assetBase }}/images/gift-box-main.webp" alt="Hộp quà mừng cưới" loading="lazy">
                        <span class="mayhen_invitation_gift_cta">✦ Nhấn để mở ✦</span>
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

                {{-- 10A. Signature Book (Pure CSS Rustic Paper — Zero Photos) --}}
                <div class="mayhen_invitation_rustic_card mayhen_invitation_sig_card_wrap">
                    <div class="mayhen_invitation_wax_badge" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
                            <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
                            <path d="M2 2l7.586 7.586"></path>
                        </svg>
                    </div>
                    <h2 class="mayhen_invitation_brown_title">SỔ KÝ TÊN LƯU BÚT</h2>
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
                            <button type="button" class="mayhen_invitation_sig_dot is-active" data-sig-color="#4a2816" style="background:#4a2816;" aria-label="Mực nâu gỗ"></button>
                            <button type="button" class="mayhen_invitation_sig_dot" data-sig-color="#8a6c5a" style="background:#8a6c5a;" aria-label="Mực nâu đất"></button>
                            <button type="button" class="mayhen_invitation_sig_dot" data-sig-color="#1f1a17" style="background:#1f1a17;" aria-label="Mực đen"></button>
                        </div>
                        <div class="mayhen_invitation_sig_btns">
                            <button type="button" id="mayhen_sig_clear" class="mayhen_invitation_outline_btn">Xóa</button>
                            <button type="button" id="mayhen_sig_download" class="mayhen_invitation_outline_btn">Tải PNG</button>
                            <button type="button" id="mayhen_sig_save" class="mayhen_invitation_primary_btn mayhen_invitation_primary_btn--sm">Lưu chữ ký</button>
                        </div>
                    </div>
                    <p id="mayhen_sig_status" class="mayhen_invitation_status_msg" role="status" aria-live="polite"></p>
                </div>

                {{-- 10B. RSVP Form (Pure CSS Deep Rustic Brown Frame — Zero Photos) --}}
                <div class="mayhen_invitation_rsvp_outer">
                    <form id="mayhen_rsvp_form" class="mayhen_invitation_rsvp_card" novalidate>
                        <div class="mayhen_invitation_rsvp_ornament" aria-hidden="true">❦</div>
                        <h2 class="mayhen_invitation_brown_title">XÁC NHẬN THAM DỰ</h2>

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

                        <button type="submit" id="mayhen_rsvp_submit" class="mayhen_invitation_primary_btn mayhen_invitation_primary_btn--full">GỬI XÁC NHẬN</button>
                        <p id="mayhen_rsvp_status" class="mayhen_invitation_status_msg" role="status" aria-live="polite"></p>
                    </form>
                </div>

                {{-- 10C. Guestbook Wishes --}}
                <div class="mayhen_invitation_rustic_card mayhen_invitation_wishes_card">
                    <h2 class="mayhen_invitation_brown_title">SỔ LỜI CHÚC</h2>
                    <form id="mayhen_wish_form" class="mayhen_invitation_wish_form" novalidate>
                        <input type="text" id="mayhen_wish_name" class="mayhen_invitation_input" placeholder="Tên của bạn *" required>
                        <textarea id="mayhen_wish_msg" rows="3" class="mayhen_invitation_input" placeholder="Viết lời chúc tốt đẹp nhất gửi tới cô dâu chú rể *" required></textarea>
                        <button type="submit" id="mayhen_wish_submit" class="mayhen_invitation_primary_btn mayhen_invitation_primary_btn--full">GỬI LỜI CHÚC</button>
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
                                <p>Chúc hai bạn trăm năm hạnh phúc, luôn giữ mãi mùa hè ấm áp bên nhau!</p>
                            </article>
                        @endforelse
                    </div>
                </div>

                {{-- 10D. Finale Thank You & Boho Floral Footer --}}
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
                    <img class="mayhen_invitation_finale_flower" src="{{ $assetBase }}/images/flower-bottom.webp" alt="" aria-hidden="true" loading="lazy">
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
