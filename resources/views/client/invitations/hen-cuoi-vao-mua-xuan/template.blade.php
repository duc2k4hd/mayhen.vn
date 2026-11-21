@extends('client.layouts.invitation')

@php
    use App\Services\VietQrService;

    $tplSlug = $template->slug ?? 'hen-cuoi-vao-mua-xuan';
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
    $groomName = trim((string) ($wedding?->groom_name ?? ($extraData['groom_name'] ?? 'Quang Huy')));
    if ($groomName === '') {
        $groomName = 'Quang Huy';
    }
    $brideName = trim((string) ($wedding?->bride_name ?? ($extraData['bride_name'] ?? 'Thảo Vy')));
    if ($brideName === '') {
        $brideName = 'Thảo Vy';
    }
    $groomRoleLabel = trim((string) ($extraData['groom_role_label'] ?? 'Trưởng Nam'));
    $brideRoleLabel = trim((string) ($extraData['bride_role_label'] ?? 'Út Nữ'));

    $guestNameRaw = trim((string) ($guest?->name ?? ''));
    $displayGuestName = $guestNameRaw !== '' ? $guestNameRaw : 'Quý khách';

    // 2. Date & Time (Asia/Ho_Chi_Minh)
    $rawWeddingDate = trim((string) ($wedding?->wedding_date?->format('Y-m-d') ?? ($wedding?->wedding_date ?? ($extraData['wedding_date'] ?? '2026-08-02'))));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawWeddingDate)) {
        $rawWeddingDate = '2026-08-02';
    }
    $ceremonyTime = trim((string) ($extraData['ceremony_time'] ?? '09:00'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $ceremonyTime)) {
        $ceremonyTime = '09:00';
    }
    $welcomeTime = trim((string) ($extraData['welcome_time'] ?? '11:00'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $welcomeTime)) {
        $welcomeTime = '11:00';
    }
    $partyTime = trim((string) ($extraData['party_time'] ?? '11:30'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $partyTime)) {
        $partyTime = '11:30';
    }
    $targetTime = trim((string) ($extraData['event_target_time'] ?? $partyTime));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $targetTime)) {
        $targetTime = '11:30';
    }

    $dateTs = strtotime($rawWeddingDate . ' ' . $targetTime . ':00 +0700') ?: strtotime('2026-08-02 11:30:00 +0700');
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

    $lunarDate = trim((string) ($extraData['wedding_lunar_date'] ?? '(Tức ngày 20 tháng 6 năm Bính Ngọ)'));
    $ceremonyType = trim((string) ($extraData['ceremony_type'] ?? 'LỄ THÀNH HÔN CỦA CON CHÚNG TÔI'));
    $slogan = trim((string) ($extraData['slogan'] ?? 'Trân trọng kính mời đến chung vui cùng gia đình chúng tôi'));

    // 3. Photos & Media (100% Local Assets)
    $heroPhoto = $resolveAsset(
        $wedding?->cover_photo_url ?? ($extraData['hero_photo_url'] ?? ($extraData['cover_photo_url'] ?? '')),
        $assetBase . '/images/hero-couple.webp'
    );
    $saveDatePhoto = $resolveAsset($extraData['save_date_photo_url'] ?? '', $assetBase . '/images/save-date.webp');
    $footerPhoto = $resolveAsset($extraData['footer_photo_url'] ?? '', $assetBase . '/images/footer-couple.webp');

    // 4. Parents & Families
    $groomFather = trim((string) ($extraData['groom_father'] ?? 'Nguyễn Văn Minh'));
    $groomMother = trim((string) ($extraData['groom_mother'] ?? 'Lê Thị Thanh'));
    $groomParentsAddress = trim((string) ($extraData['groom_parents_address'] ?? 'Đà Lạt, Lâm Đồng'));

    $brideFather = trim((string) ($extraData['bride_father'] ?? 'Trần Quốc Bảo'));
    $brideMother = trim((string) ($extraData['bride_mother'] ?? 'Phạm Thị Hương'));
    $brideParentsAddress = trim((string) ($extraData['bride_parents_address'] ?? 'Thành phố Huế, Thừa Thiên Huế'));

    // 5. Love Story
    $storyTitle = trim((string) ($extraData['story_title'] ?? 'HẸN CƯỚI VÀO MÙA XUÂN'));
    $storySubtitle = trim((string) ($extraData['story_subtitle'] ?? 'Giữa khu vườn nở hoa, chúng mình chọn nắm tay nhau đi hết chặng đường dài'));
    $coupleQuote = trim((string) ($extraData['couple_quote'] ?? '“Năm mươi năm về sau, người bên cạnh vẫn là em.”'));
    $storyContent = trim((string) ($extraData['story_content'] ?? 'Gặp gỡ nhau trong những ngày nắng trong veo, chúng mình học cách lắng nghe, trân trọng và cùng nhau vun đắp tổ ấm nhỏ. Cảm ơn bạn đã là một phần thanh xuân và đến chia vui trong ngày trọng đại này.'));

    // 6. Venue & Maps & Google Calendar
    $venueName = trim((string) ($wedding?->venue_name ?? ($extraData['venue_name'] ?? 'Trung Tâm Tiệc Cưới Thung Lũng Hoa')));
    if ($venueName === '') {
        $venueName = 'Trung Tâm Tiệc Cưới Thung Lũng Hoa';
    }
    $venueAddress = trim((string) ($wedding?->venue_address ?? ($extraData['venue_address'] ?? 'Trung Tâm Tiệc Cưới Thung Lũng Hoa, 25 Phan Chu Trinh, Đà Lạt')));
    if ($venueAddress === '') {
        $venueAddress = 'Trung Tâm Tiệc Cưới Thung Lũng Hoa, 25 Phan Chu Trinh, Đà Lạt';
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
        ['time' => '17:30', 'title' => 'Đón khách & Chụp ảnh lưu niệm'],
        ['time' => '18:30', 'title' => 'Khai tiệc & Cử hành nghi lễ'],
        ['time' => '19:00', 'title' => 'Rót rượu, cắt bánh hạnh phúc'],
        ['time' => '19:30', 'title' => 'Phục vụ món chính & Nâng ly'],
        ['time' => '21:00', 'title' => 'Giao lưu âm nhạc & Kết thúc tiệc'],
    ];
    $timelineItems = is_array($extraData['timeline'] ?? null) && count($extraData['timeline']) > 0
        ? $extraData['timeline']
        : $defaultTimeline;

    $timelineIcons = [
        $assetBase . '/images/icon-welcome.webp',
        $assetBase . '/images/icon-cake.webp',
        $assetBase . '/images/icon-cake.webp',
        $assetBase . '/images/icon-dish.webp',
        $assetBase . '/images/icon-welcome.webp',
    ];

    $dresscodeNote = trim((string) ($extraData['dresscode_note'] ?? 'Trang phục dự tiệc thanh lịch theo tông xanh lá xô thơm, trắng kem, hồng phấn và xanh rêu'));
    $defaultColors = ['#47613e', '#8fa986', '#f5f7f2', '#e8c8c8'];
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
    $giftNote = trim((string) ($extraData['gift_note'] ?? 'Nhấn vào hộp quà bên dưới để xem thông tin mừng cưới Nhà Trai & Nhà Gái'));
    $groomBankName = trim((string) ($extraData['groom_bank_name'] ?? 'Vietcombank'));
    $groomBankAccount = trim((string) ($extraData['groom_bank_account'] ?? '0123456789'));
    $groomAccountName = trim((string) ($extraData['groom_account_name'] ?? ($extraData['groom_bank_owner'] ?? 'TRAN QUANG HUY')));
    $brideBankName = trim((string) ($extraData['bride_bank_name'] ?? 'Techcombank'));
    $brideBankAccount = trim((string) ($extraData['bride_bank_account'] ?? '9876543210'));
    $brideAccountName = trim((string) ($extraData['bride_account_name'] ?? ($extraData['bride_bank_owner'] ?? 'LE THAO VY')));

    $groomQrSrc = VietQrService::resolveQrImage(
        $groomBankName,
        $groomBankAccount,
        $groomAccountName,
        'Mung cuoi ' . $groomName . ' ' . $brideName,
        $resolveAsset($extraData['groom_qr_url'] ?? '', $assetBase . '/images/qr-groom.webp')
    );
    $brideQrSrc = VietQrService::resolveQrImage(
        $brideBankName,
        $brideBankAccount,
        $brideAccountName,
        'Mung cuoi ' . $brideName . ' ' . $groomName,
        $resolveAsset($extraData['bride_qr_url'] ?? '', $assetBase . '/images/qr-bride.webp')
    );

    // 10. Ending & Endpoints
    $endingTitle = trim((string) ($extraData['ending_title'] ?? 'Cảm Ơn Quý Khách'));
    $endingText = trim((string) ($extraData['ending_text'] ?? 'Sự hiện diện và lời chúc phúc của quý khách là niềm hạnh phúc lớn nhất của gia đình chúng tôi!'));

    $audioSrc = $resolveAsset($extraData['music_url'] ?? '', $assetBase . '/wedding-song.mp3');

    $invSlug = $invitation->slug ?? '';
    $invId = $invitation->id ?? null;
    $rsvpUrl = (!$isPreview && $invSlug !== '')
        ? route('rsvp.submit', ['slug' => $invSlug])
        : route('templates.preview.rsvp', ['slug' => $tplSlug]);
    $wishUrl = (!$isPreview && $invSlug !== '')
        ? route('invitation.wish.store', ['slug' => $invSlug])
        : route('templates.preview.wish', ['slug' => $tplSlug]);
    $signatureUrl = (!$isPreview && $invId)
        ? route('invitation.signature.save', ['id' => $invId])
        : '';

    $wishesList = isset($wishes) && is_iterable($wishes) ? $wishes : [];
    $signaturesList = isset($signatures) && is_iterable($signatures) ? $signatures : [];
@endphp

@section('title', 'Thiệp Cưới ' . $groomName . ' & ' . $brideName . ' — Hẹn Cưới Vào Mùa Xuân')
@section('body_class', 'mayhen_invitation_body_garden' . ($isEmbed ? ' mayhen_embed_mode' : '') . ($autoOpened ? ' mayhen_template_auto_opened' : ''))

@push('styles')
    <link rel="stylesheet" href="{{ $assetBase }}/styles.css">
@endpush

@section('content')
<div
    id="mayhen_invitation_root"
    class="mayhen_invitation_root mayhen_invitation_root--garden{{ $isEmbed ? ' mayhen_invitation_root--embed' : '' }}"
    data-embed="{{ $isEmbed ? '1' : '0' }}"
    data-preview="{{ $isPreview ? '1' : '0' }}"
    data-wedding-date="{{ $rawWeddingDate }}"
    data-target-iso="{{ $targetIso }}"
    data-csrf="{{ csrf_token() }}"
    data-rsvp-url="{{ $rsvpUrl }}"
    data-wish-url="{{ $wishUrl }}"
    data-signature-url="{{ $signatureUrl }}"
>
    {{-- Audio nền nội bộ --}}
    <audio class="mayhen_invitation_audio" data-mayhen-audio preload="none" loop playsinline>
        <source src="{{ $audioSrc }}" type="audio/mpeg">
    </audio>

    {{-- Nút nhạc nền xanh lá vườn kính --}}
    <button
        type="button"
        id="mayhen_music_toggle"
        class="mayhen_invitation_music_btn"
        aria-label="Bật hoặc tắt nhạc nền thiệp cưới"
        aria-pressed="false"
    >
        <svg class="mayhen_invitation_music_icon_muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 18V5l12-2v13"></path>
            <circle cx="6" cy="18" r="3"></circle>
            <circle cx="18" cy="16" r="3"></circle>
            <line x1="2" y1="2" x2="22" y2="22"></line>
        </svg>
        <svg class="mayhen_invitation_music_icon_note" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="6" y1="16" x2="6" y2="11"></line>
            <line x1="10" y1="16" x2="10" y2="7"></line>
            <line x1="14" y1="16" x2="14" y2="10"></line>
            <line x1="18" y1="16" x2="18" y2="6"></line>
        </svg>
    </button>

    {{-- ================================================================
         MÀN MỞ THIỆP — KÍNH MỜ VƯỜN HOA XANH (EXACT GLASS GARDEN GREEN)
         ================================================================ --}}
    <div
        id="mayhen_opening"
        class="mayhen_invitation_opening{{ $autoOpened ? ' is-opened' : '' }}"
        role="dialog"
        aria-modal="true"
        aria-label="Mở thiệp cưới {{ $groomName }} và {{ $brideName }}"
    >
        <div class="mayhen_invitation_open_card">
            <img src="{{ $assetBase }}/images/flower-1.webp" alt="" class="mayhen_invitation_open_flower mayhen_invitation_open_flower--tl" aria-hidden="true">
            <img src="{{ $assetBase }}/images/flower-2.webp" alt="" class="mayhen_invitation_open_flower mayhen_invitation_open_flower--br" aria-hidden="true">

            <div class="mayhen_invitation_open_inner">
                <div class="mayhen_invitation_open_seal" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>

                <p class="mayhen_invitation_open_names">
                    <span data-sync-target="groom_name">{{ $groomName }}</span>
                    <span class="mayhen_invitation_open_amp">&amp;</span>
                    <span data-sync-target="bride_name">{{ $brideName }}</span>
                </p>

                <div class="mayhen_invitation_open_rule" aria-hidden="true">
                    <span></span><i>❦</i><span></span>
                </div>

                <p class="mayhen_invitation_open_date" data-sync-target="wedding_date_text">{{ $openingDateText }}</p>
                <p class="mayhen_invitation_open_invite">Thân Mời</p>
                @if($guestNameRaw !== '')
                    <p class="mayhen_invitation_open_guest">{{ $displayGuestName }}</p>
                @endif

                <button type="button" id="mayhen_open_btn" class="mayhen_invitation_open_btn">
                    Mở thiệp
                </button>
            </div>
        </div>
    </div>

    {{-- ================================================================
         TRANG THIỆP CHÍNH (BOTANICAL WATERCOLOR GARDEN + FROSTED GLASS)
         ================================================================ --}}
    <div class="mayhen_invitation_shell card-wrapper">
        <main class="mayhen_invitation_main">

            {{-- SECTION 1: HERO FROSTED GLASS ARCH & SPRING BLOSSOM BOUQUET --}}
            <section
                id="mayhen_section_hero"
                class="mayhen_invitation_section mayhen_invitation_hero"
                data-mayhen-section="hero"
            >
                <div class="mayhen_invitation_glass_arch">
                    <h1 class="mayhen_invitation_arch_names">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <span class="mayhen_invitation_arch_amp">&amp;</span>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </h1>
                </div>

                <div class="mayhen_invitation_hero_bouquet_wrap">
                    <img
                        src="{{ $assetBase }}/images/flower-3.webp"
                        alt="Hoa mùa xuân"
                        class="mayhen_invitation_hero_bouquet"
                    >
                </div>
            </section>

            {{-- SECTION 2: THÔNG TIN LỄ CƯỚI & BÁO TIN (FROSTED GLASS PANEL) --}}
            <section
                id="mayhen_section_parents"
                class="mayhen_invitation_section mayhen_invitation_parents mayhen_invitation_reveal"
                data-mayhen-section="parents"
            >
                <div class="mayhen_invitation_glass_panel">
                    <h2 class="mayhen_invitation_green_title">THÔNG TIN LỄ CƯỚI</h2>

                    <div class="mayhen_invitation_parents_grid">
                        <div class="mayhen_invitation_parent_col">
                            <p class="mayhen_invitation_parent_prefix">Ông Bà</p>
                            <p class="mayhen_invitation_parent_names">
                                <span data-sync-target="groom_father">{{ $groomFather }}</span><br>
                                <span data-sync-target="groom_mother">{{ $groomMother }}</span>
                            </p>
                            <p class="mayhen_invitation_parent_addr" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                        </div>

                        <span class="mayhen_invitation_parents_divider" aria-hidden="true"></span>

                        <div class="mayhen_invitation_parent_col">
                            <p class="mayhen_invitation_parent_prefix">Ông Bà</p>
                            <p class="mayhen_invitation_parent_names">
                                <span data-sync-target="bride_father">{{ $brideFather }}</span><br>
                                <span data-sync-target="bride_mother">{{ $brideMother }}</span>
                            </p>
                            <p class="mayhen_invitation_parent_addr" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                        </div>
                    </div>

                    <div class="mayhen_invitation_announce_block">
                        <p class="mayhen_invitation_announce_lead">TRÂN TRỌNG BÁO TIN</p>
                        <p class="mayhen_invitation_announce_sub" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>

                        <div class="mayhen_invitation_formal_couple">
                            <h3 class="mayhen_invitation_formal_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                            <p class="mayhen_invitation_formal_role" data-sync-target="groom_role_label">{{ mb_strtoupper($groomRoleLabel, 'UTF-8') }}</p>

                            <span class="mayhen_invitation_formal_amp" aria-hidden="true">&amp;</span>

                            <h3 class="mayhen_invitation_formal_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                            <p class="mayhen_invitation_formal_role" data-sync-target="bride_role_label">{{ mb_strtoupper($brideRoleLabel, 'UTF-8') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECTION 3: LỄ THÀNH HÔN, TIỆC CƯỚI & BẢN ĐỒ --}}
            <section
                id="mayhen_section_events"
                class="mayhen_invitation_section mayhen_invitation_events mayhen_invitation_reveal"
                data-mayhen-section="events"
            >
                <div class="mayhen_invitation_glass_panel">
                    {{-- Lễ Thành Hôn --}}
                    <div class="mayhen_invitation_event_block">
                        <p class="mayhen_invitation_event_kicker">LỄ THÀNH HÔN ĐƯỢC CỬ HÀNH TẠI TƯ GIA</p>
                        <p class="mayhen_invitation_event_at">VÀO LÚC</p>
                        <p class="mayhen_invitation_event_time" data-sync-target="ceremony_time">{{ $ceremonyTime }}</p>

                        <div class="mayhen_invitation_date_bar">
                            <span class="mayhen_invitation_date_side" data-sync-target="wedding_weekday">{{ $wWeekdayUpper }}</span>
                            <span class="mayhen_invitation_date_pipe" aria-hidden="true">|</span>
                            <span class="mayhen_invitation_date_day" data-sync-target="wedding_day">{{ $wDay }}</span>
                            <span class="mayhen_invitation_date_pipe" aria-hidden="true">|</span>
                            <span class="mayhen_invitation_date_side">THÁNG <span data-sync-target="wedding_month">{{ $wMonth }}</span></span>
                        </div>
                        <p class="mayhen_invitation_date_year" data-sync-target="wedding_year">{{ $wYear }}</p>
                        <p class="mayhen_invitation_date_lunar" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</p>
                    </div>

                    <div class="mayhen_invitation_soft_divider" aria-hidden="true"></div>

                    {{-- Thông Tin Tiệc Cưới --}}
                    <div class="mayhen_invitation_event_block">
                        <h2 class="mayhen_invitation_green_title">THÔNG TIN TIỆC CƯỚI</h2>
                        <p class="mayhen_invitation_party_lead">Tiệc cưới sẽ diễn ra vào lúc:</p>
                        <p class="mayhen_invitation_event_time" data-sync-target="party_time">{{ $partyTime }}</p>

                        <div class="mayhen_invitation_mini_times">
                            <div>
                                <span>Đón khách</span>
                                <strong data-sync-target="welcome_time">{{ $welcomeTime }}</strong>
                            </div>
                            <div>
                                <span>Khai tiệc</span>
                                <strong>{{ $partyTime }}</strong>
                            </div>
                        </div>

                        <p class="mayhen_invitation_venue_kicker">Tiệc cưới sẽ tổ chức tại</p>
                        <h3 class="mayhen_invitation_venue_name" data-sync-target="venue_name">{{ $venueName }}</h3>
                        <p class="mayhen_invitation_venue_address" data-sync-target="venue_address">{{ $venueAddress }}</p>

                        <div class="mayhen_invitation_map_card">
                            <iframe
                                src="{{ $mapEmbedUrl }}"
                                class="mayhen_invitation_map_iframe"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Bản đồ địa điểm tổ chức tiệc cưới {{ $venueName }}"
                            ></iframe>
                            <a
                                href="{{ $venueMapUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mayhen_invitation_map_pill"
                                data-sync-target="venue_map_url"
                            >
                                Chỉ đường
                            </a>
                        </div>

                        <button type="button" id="mayhen_jump_rsvp" class="mayhen_invitation_cta_green">
                            XÁC NHẬN THAM DỰ
                        </button>
                    </div>
                </div>
            </section>

            {{-- SECTION 4: LỊCH THÁNG VƯỜN XANH & ĐẾM NGƯỢC --}}
            <section
                id="mayhen_section_calendar"
                class="mayhen_invitation_section mayhen_invitation_countdown_calendar mayhen_invitation_reveal"
                data-mayhen-section="countdown_calendar"
            >
                <div class="mayhen_invitation_glass_panel">
                    <h2 class="mayhen_invitation_green_title">Cùng Đếm Ngược</h2>

                    <div class="mayhen_invitation_countdown_grid" aria-label="Đếm ngược đến ngày cưới">
                        <div class="mayhen_invitation_cd_box">
                            <span id="mayhen_cd_days" class="mayhen_invitation_cd_val">00</span>
                            <span class="mayhen_invitation_cd_label">Ngày</span>
                        </div>
                        <div class="mayhen_invitation_cd_box">
                            <span id="mayhen_cd_hours" class="mayhen_invitation_cd_val">00</span>
                            <span class="mayhen_invitation_cd_label">Giờ</span>
                        </div>
                        <div class="mayhen_invitation_cd_box">
                            <span id="mayhen_cd_mins" class="mayhen_invitation_cd_val">00</span>
                            <span class="mayhen_invitation_cd_label">Phút</span>
                        </div>
                        <div class="mayhen_invitation_cd_box">
                            <span id="mayhen_cd_secs" class="mayhen_invitation_cd_val">00</span>
                            <span class="mayhen_invitation_cd_label">Giây</span>
                        </div>
                    </div>

                    <div class="mayhen_invitation_cal_box">
                        <p id="mayhen_cal_title" class="mayhen_invitation_cal_title">Tháng {{ $wMonthNoZero }} / {{ $wYear }}</p>

                        <div class="mayhen_invitation_cal_weekdays" aria-hidden="true">
                            <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                        </div>

                        <div id="mayhen_cal_grid" class="mayhen_invitation_cal_grid" aria-label="Lịch tháng ngày cưới"></div>

                        <a
                            href="{{ $gcalUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mayhen_invitation_gcal_btn"
                        >
                            Thêm vào lịch
                        </a>
                    </div>
                </div>
            </section>

            {{-- SECTION 5: CHUYỆN TÌNH YÊU --}}
            <section
                id="mayhen_section_story"
                class="mayhen_invitation_section mayhen_invitation_story mayhen_invitation_reveal"
                data-mayhen-section="story"
            >
                <div class="mayhen_invitation_glass_panel">
                    <img src="{{ $assetBase }}/images/flower-4.webp" alt="" class="mayhen_invitation_story_flower" aria-hidden="true">
                    <h2 class="mayhen_invitation_green_title" data-sync-target="story_title">{{ $storyTitle }}</h2>
                    <p class="mayhen_invitation_story_sub" data-sync-target="story_subtitle">{{ $storySubtitle }}</p>

                    <div class="mayhen_invitation_story_photos">
                        <figure class="mayhen_invitation_story_arch">
                            <img
                                src="{{ $heroPhoto }}"
                                alt="Ảnh cưới {{ $groomName }} và {{ $brideName }}"
                                class="mayhen_invitation_story_img"
                                data-sync-target="cover_photo_url"
                            >
                        </figure>
                        <figure class="mayhen_invitation_story_arch">
                            <img
                                src="{{ $saveDatePhoto }}"
                                alt="Kỷ niệm {{ $groomName }} và {{ $brideName }}"
                                class="mayhen_invitation_story_img"
                                data-sync-target="save_date_photo_url"
                            >
                        </figure>
                    </div>

                    <p class="mayhen_invitation_story_quote" data-sync-target="couple_quote">{{ $coupleQuote }}</p>
                    <p class="mayhen_invitation_story_text" data-sync-target="story_content">{{ $storyContent }}</p>
                </div>
            </section>

            {{-- SECTION 6: DRESS CODE --}}
            @if(!$isCuratedSeven)
            <section
                id="mayhen_section_dresscode"
                class="mayhen_invitation_section mayhen_invitation_dresscode mayhen_invitation_reveal"
                data-mayhen-section="dresscode"
            >
                <div class="mayhen_invitation_glass_panel">
                    <h2 class="mayhen_invitation_green_title">DRESS CODE</h2>
                    <p class="mayhen_invitation_dresscode_note" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>
                    <div class="mayhen_invitation_dresscode_swatches" data-sync-target="dresscode_colors">
                        @foreach($dresscodeColors as $hex)
                            <span class="mayhen_invitation_dresscode_swatch" style="background-color: {{ e($hex) }};" title="{{ e($hex) }}"></span>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif

            {{-- SECTION 7: LỊCH TRÌNH NGÀY CƯỚI VỚI BIỂU TƯỢNG MÀU NƯỚC --}}
            @if(!$isCuratedSeven)
            <section
                id="mayhen_section_timeline"
                class="mayhen_invitation_section mayhen_invitation_timeline mayhen_invitation_reveal"
                data-mayhen-section="timeline"
            >
                <div class="mayhen_invitation_glass_panel">
                    <h2 class="mayhen_invitation_green_title">LỊCH TRÌNH NGÀY CƯỚI</h2>
                    <ol class="mayhen_invitation_timeline_list" data-sync-target="timeline">
                        @foreach($timelineItems as $idx => $item)
                            <li class="mayhen_invitation_timeline_item">
                                <img
                                    src="{{ $timelineIcons[$idx % count($timelineIcons)] }}"
                                    alt=""
                                    class="mayhen_invitation_timeline_icon"
                                    aria-hidden="true"
                                >
                                <div class="mayhen_invitation_timeline_body">
                                    <span class="mayhen_invitation_timeline_time" data-sync-target="timeline.{{ $idx }}.time">{{ $item['time'] ?? '' }}</span>
                                    <span class="mayhen_invitation_timeline_title" data-sync-target="timeline.{{ $idx }}.title">{{ $item['title'] ?? '' }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
            @endif

            {{-- SECTION 8: ALBUM 10 ẢNH CƯỚI + LIGHTBOX --}}
            <section
                id="mayhen_section_album"
                class="mayhen_invitation_section mayhen_invitation_album mayhen_invitation_reveal"
                data-mayhen-section="album"
            >
                <div class="mayhen_invitation_glass_panel">
                    <h2 class="mayhen_invitation_green_title">Album Ảnh</h2>
                    <p class="mayhen_invitation_section_sub">Chạm vào từng khoảnh khắc để xem phóng to toàn màn hình</p>

                    <div class="mayhen_invitation_album_grid">
                        @foreach($galleryPhotos as $idx => $photoUrl)
                            <button
                                type="button"
                                class="mayhen_invitation_album_item{{ ($idx === 4 || $idx === 9) ? ' mayhen_invitation_album_item--wide' : '' }}"
                                aria-label="Xem ảnh cưới số {{ $idx + 1 }}"
                            >
                                <img
                                    src="{{ $photoUrl }}"
                                    alt="Ảnh cưới {{ $groomName }} và {{ $brideName }} #{{ $idx + 1 }}"
                                    loading="lazy"
                                    data-sync-target="gallery.{{ $idx }}"
                                >
                            </button>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- SECTION 9: HỘP QUÀ MỪNG 3D VƯỜN XANH & VIETQR ĐỘNG --}}
            <section
                id="mayhen_section_gift"
                class="mayhen_invitation_section mayhen_invitation_gift mayhen_invitation_reveal"
                data-mayhen-section="gift"
            >
                <div class="mayhen_invitation_glass_panel">
                    <h2 class="mayhen_invitation_green_title">Hộp Quà Mừng</h2>
                    <p class="mayhen_invitation_section_sub" data-sync-target="gift_note">{{ $giftNote }}</p>

                    <div class="mayhen_invitation_gift_stage">
                        <img src="{{ $assetBase }}/images/gift-mini-1.webp" alt="" class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--1" aria-hidden="true">
                        <img src="{{ $assetBase }}/images/gift-mini-2.webp" alt="" class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--2" aria-hidden="true">
                        <img src="{{ $assetBase }}/images/gift-mini-3.webp" alt="" class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--3" aria-hidden="true">
                        <img src="{{ $assetBase }}/images/gift-mini-4.webp" alt="" class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--4" aria-hidden="true">
                        <img src="{{ $assetBase }}/images/gift-mini-5.webp" alt="" class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--5" aria-hidden="true">
                        <img src="{{ $assetBase }}/images/gift-mini-6.webp" alt="" class="mayhen_invitation_gift_mini mayhen_invitation_gift_mini--6" aria-hidden="true">

                        <button
                            type="button"
                            id="mayhen_gift_toggle"
                            class="mayhen_invitation_gift_box_btn"
                            aria-expanded="false"
                            aria-controls="mayhen_gift_panel"
                        >
                            <img
                                src="{{ $assetBase }}/images/gift-box-main.webp"
                                alt="Nhấn để mở Hộp Quà Mừng VietQR"
                                class="mayhen_invitation_gift_box_img"
                            >
                            <span class="mayhen_invitation_gift_cta_tag">Nhấn để mở</span>
                        </button>
                    </div>

                    <div id="mayhen_gift_panel" class="mayhen_invitation_gift_panel" aria-hidden="true">
                        <div class="mayhen_invitation_qr_grid">
                            <article class="mayhen_invitation_qr_card">
                                <span class="mayhen_invitation_qr_side">NHÀ TRAI</span>
                                <div class="mayhen_invitation_qr_frame">
                                    <img src="{{ $groomQrSrc }}" alt="Mã VietQR Nhà Trai" data-sync-target="groom_qr_url">
                                </div>
                                <strong class="mayhen_invitation_qr_owner" data-sync-target="groom_name">{{ $groomName }}</strong>
                                <span class="mayhen_invitation_qr_bank" data-sync-target="groom_bank_name">{{ $groomBankName }}</span>
                                <span class="mayhen_invitation_qr_number" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</span>
                                <span class="mayhen_invitation_qr_holder" data-sync-target="groom_account_name">{{ $groomAccountName }}</span>
                                <button type="button" class="mayhen_invitation_copy_btn" data-copy-account="groom_bank_account">Sao chép STK</button>
                            </article>

                            <article class="mayhen_invitation_qr_card">
                                <span class="mayhen_invitation_qr_side">NHÀ GÁI</span>
                                <div class="mayhen_invitation_qr_frame">
                                    <img src="{{ $brideQrSrc }}" alt="Mã VietQR Nhà Gái" data-sync-target="bride_qr_url">
                                </div>
                                <strong class="mayhen_invitation_qr_owner" data-sync-target="bride_name">{{ $brideName }}</strong>
                                <span class="mayhen_invitation_qr_bank" data-sync-target="bride_bank_name">{{ $brideBankName }}</span>
                                <span class="mayhen_invitation_qr_number" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</span>
                                <span class="mayhen_invitation_qr_holder" data-sync-target="bride_account_name">{{ $brideAccountName }}</span>
                                <button type="button" class="mayhen_invitation_copy_btn" data-copy-account="bride_bank_account">Sao chép STK</button>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECTION 10: KÝ TÊN LƯU BÚT, RSVP (LUẬT #8: 0 ẢNH NGƯỜI), SỔ LƯU BÚT & CẢM ƠN --}}
            <section
                id="mayhen_section_ending"
                class="mayhen_invitation_section mayhen_invitation_ending mayhen_invitation_reveal"
                data-mayhen-section="ending"
            >
                {{-- 10A. Sổ Ký Tên Lưu Bút (0 ảnh người) --}}
                <div class="mayhen_invitation_glass_panel">
                    <div class="mayhen_invitation_badge_icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
                            <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
                        </svg>
                    </div>
                    <h2 class="mayhen_invitation_green_title">SỔ KÝ TÊN LƯU BÚT</h2>
                    <p class="mayhen_invitation_section_sub">Lưu lại chữ ký kỷ niệm gửi tặng {{ $groomName }} &amp; {{ $brideName }}</p>

                    <div id="mayhen_sig_wall" class="mayhen_invitation_sig_wall">
                        @forelse($signaturesList as $sig)
                            <div class="mayhen_invitation_sig_card">
                                <img src="{{ $sig->signature_data ?? $sig['signature_data'] ?? '' }}" alt="{{ e($sig->guest_name ?? $sig['guest_name'] ?? 'Khách mời') }}">
                                <span>{{ $sig->guest_name ?? $sig['guest_name'] ?? 'Khách mời' }}</span>
                            </div>
                        @empty
                            <p class="mayhen_invitation_sig_empty">Chưa có chữ ký nào — hãy ký tên đầu tiên để chúc phúc đôi uyên ương!</p>
                        @endforelse
                    </div>

                    <input
                        type="text"
                        id="mayhen_sig_name"
                        class="mayhen_invitation_input"
                        placeholder="Nhập họ tên của bạn trước khi ký..."
                        maxlength="80"
                    >

                    <div class="mayhen_invitation_sig_pad_wrap">
                        <canvas id="mayhen_sig_canvas" class="mayhen_invitation_sig_canvas"></canvas>
                        <span id="mayhen_sig_placeholder" class="mayhen_invitation_sig_placeholder">Chạm hoặc kéo tay để ký tên tại đây</span>
                    </div>

                    <div class="mayhen_invitation_sig_toolbar">
                        <div class="mayhen_invitation_sig_colors" role="group" aria-label="Chọn màu mực ký">
                            <button type="button" class="mayhen_invitation_sig_color is-active" data-sig-color="#35452f" style="background-color:#35452f;" aria-label="Mực xanh rêu"></button>
                            <button type="button" class="mayhen_invitation_sig_color" data-sig-color="#1e1e1e" style="background-color:#1e1e1e;" aria-label="Mực đen"></button>
                            <button type="button" class="mayhen_invitation_sig_color" data-sig-color="#6b8e5e" style="background-color:#6b8e5e;" aria-label="Mực xanh lá"></button>
                        </div>
                        <div class="mayhen_invitation_sig_actions">
                            <button type="button" id="mayhen_sig_clear" class="mayhen_invitation_btn_ghost">Xóa</button>
                            <button type="button" id="mayhen_sig_download" class="mayhen_invitation_btn_ghost">Tải PNG</button>
                            <button type="button" id="mayhen_sig_save" class="mayhen_invitation_btn_primary">Lưu chữ ký</button>
                        </div>
                    </div>
                    <p id="mayhen_sig_status" class="mayhen_invitation_form_status" aria-live="polite"></p>
                </div>

                {{-- 10B. Xác nhận tham dự RSVP (0 ảnh người — nền xanh rừng thuần CSS) --}}
                <div id="mayhen_rsvp_box" class="mayhen_invitation_rsvp_stage">
                    <div class="mayhen_invitation_rsvp_card">
                        <div class="mayhen_invitation_rsvp_ornament" aria-hidden="true">❦</div>
                        <h2 class="mayhen_invitation_green_title">XÁC NHẬN THAM DỰ</h2>

                        <form id="mayhen_rsvp_form" class="mayhen_invitation_form">
                            <label class="mayhen_invitation_label" for="mayhen_rsvp_name">Họ và tên *</label>
                            <input type="text" id="mayhen_rsvp_name" name="guest_name" class="mayhen_invitation_input" placeholder="Nhập tên của bạn" required maxlength="100">

                            <span class="mayhen_invitation_label">Quý khách sẽ tham dự chứ?</span>
                            <div class="mayhen_invitation_radio_group">
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

                            <div class="mayhen_invitation_two_cols">
                                <div>
                                    <label class="mayhen_invitation_label" for="mayhen_rsvp_adults">Người lớn</label>
                                    <select id="mayhen_rsvp_adults" name="adults_count" class="mayhen_invitation_input">
                                        <option value="1">1 người</option>
                                        <option value="2">2 người</option>
                                        <option value="3">3 người</option>
                                        <option value="4">4 người</option>
                                        <option value="5">5 người</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mayhen_invitation_label" for="mayhen_rsvp_children">Trẻ em</label>
                                    <select id="mayhen_rsvp_children" name="children_count" class="mayhen_invitation_input">
                                        <option value="0">0 trẻ em</option>
                                        <option value="1">1 trẻ em</option>
                                        <option value="2">2 trẻ em</option>
                                        <option value="3">3 trẻ em</option>
                                    </select>
                                </div>
                            </div>

                            <label class="mayhen_invitation_label" for="mayhen_rsvp_note">Lời nhắn gửi Dâu Rể (tuỳ chọn)</label>
                            <textarea id="mayhen_rsvp_note" name="wishes" rows="3" class="mayhen_invitation_input" placeholder="Ghi chú chế độ ăn hoặc lời chúc ngắn..." maxlength="400"></textarea>

                            <button type="submit" class="mayhen_invitation_btn_primary mayhen_invitation_btn_full">GỬI XÁC NHẬN</button>
                            <p id="mayhen_rsvp_status" class="mayhen_invitation_form_status" aria-live="polite"></p>
                        </form>
                    </div>
                </div>

                {{-- 10C. Sổ Lưu Bút Lời Chúc --}}
                <div class="mayhen_invitation_glass_panel">
                    <h2 class="mayhen_invitation_green_title">Sổ lưu bút</h2>
                    <form id="mayhen_wish_form" class="mayhen_invitation_form">
                        <input type="text" name="name" class="mayhen_invitation_input" placeholder="Tên của bạn *" required maxlength="80">
                        <textarea name="message" rows="3" class="mayhen_invitation_input" placeholder="Viết lời chúc tốt đẹp gửi tới đôi uyên ương..." required maxlength="500"></textarea>
                        <button type="submit" class="mayhen_invitation_btn_primary mayhen_invitation_btn_full">GỬI LỜI CHÚC</button>
                        <p id="mayhen_wish_status" class="mayhen_invitation_form_status" aria-live="polite"></p>
                    </form>

                    <div id="mayhen_wish_list" class="mayhen_invitation_wish_list">
                        @forelse($wishesList as $wish)
                            <article class="mayhen_invitation_wish_item">
                                <strong>{{ $wish->guest_name ?? $wish->name ?? $wish['name'] ?? 'Khách quý' }}</strong>
                                <p>{{ $wish->message ?? $wish->content ?? $wish['message'] ?? '' }}</p>
                            </article>
                        @empty
                            <p class="mayhen_invitation_wish_empty">Hãy là người đầu tiên gửi lời chúc tốt đẹp đến {{ $groomName }} &amp; {{ $brideName }}!</p>
                        @endforelse
                    </div>
                </div>

                {{-- 10D. Khung Kết Thiệp Vườn Kính Xanh --}}
                <div class="mayhen_invitation_glass_panel mayhen_invitation_finale">
                    <figure class="mayhen_invitation_finale_photo_wrap">
                        <img
                            src="{{ $footerPhoto }}"
                            alt="Cảm ơn từ {{ $groomName }} và {{ $brideName }}"
                            class="mayhen_invitation_finale_photo"
                            data-sync-target="footer_photo_url"
                        >
                    </figure>

                    <h3 class="mayhen_invitation_finale_title" data-sync-target="ending_title">{{ $endingTitle }}</h3>
                    <p class="mayhen_invitation_finale_text" data-sync-target="ending_text">{{ $endingText }}</p>
                    <p class="mayhen_invitation_finale_couple">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <span class="mayhen_invitation_finale_amp">&amp;</span>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </p>
                    <img src="{{ $assetBase }}/images/flower-5.webp" alt="" class="mayhen_invitation_finale_garland" aria-hidden="true">
                </div>
            </section>

        </main>
    </div>

    {{-- Lightbox toàn màn hình cho Album 10 ảnh --}}
    <div id="mayhen_lightbox" class="mayhen_invitation_lightbox" role="dialog" aria-modal="true" aria-hidden="true">
        <button type="button" id="mayhen_lb_close" class="mayhen_invitation_lb_close" aria-label="Đóng xem ảnh">&times;</button>
        <button type="button" id="mayhen_lb_prev" class="mayhen_invitation_lb_nav mayhen_invitation_lb_nav--prev" aria-label="Ảnh trước">&#8249;</button>
        <img id="mayhen_lb_img" class="mayhen_invitation_lb_img" src="{{ $galleryPhotos[0] }}" alt="Ảnh cưới phóng to">
        <button type="button" id="mayhen_lb_next" class="mayhen_invitation_lb_nav mayhen_invitation_lb_nav--next" aria-label="Ảnh tiếp theo">&#8250;</button>
        <span id="mayhen_lb_counter" class="mayhen_invitation_lb_counter">1 / 10</span>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ $assetBase }}/script.js"></script>
@endpush
