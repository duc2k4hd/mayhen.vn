@extends('client.layouts.invitation')

@php
    use App\Services\VietQrService;

    $tplSlug = $template->slug ?? 'loi-hua-bang-nu-cuoi';
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
    $groomName = trim((string) ($wedding?->groom_name ?? ($extraData['groom_name'] ?? 'Trọng Nghĩa')));
    if ($groomName === '') {
        $groomName = 'Trọng Nghĩa';
    }
    $brideName = trim((string) ($wedding?->bride_name ?? ($extraData['bride_name'] ?? 'Yến Nhi')));
    if ($brideName === '') {
        $brideName = 'Yến Nhi';
    }
    $groomRoleLabel = trim((string) ($extraData['groom_role_label'] ?? 'Thứ Nam'));
    $brideRoleLabel = trim((string) ($extraData['bride_role_label'] ?? 'Thứ Nữ'));

    $guestNameRaw = trim((string) ($guest?->name ?? ''));
    $displayGuestName = $guestNameRaw !== '' ? $guestNameRaw : 'Quý khách';

    // 2. Date & Lunar Date (Asia/Ho_Chi_Minh)
    $rawWeddingDate = trim((string) ($wedding?->wedding_date?->format('Y-m-d') ?? ($wedding?->wedding_date ?? ($extraData['wedding_date'] ?? '2026-12-20'))));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawWeddingDate)) {
        $rawWeddingDate = '2026-12-20';
    }
    $ceremonyTime = trim((string) ($extraData['ceremony_time'] ?? '09:30'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $ceremonyTime)) {
        $ceremonyTime = '09:30';
    }
    $partyTime = trim((string) ($extraData['party_time'] ?? '11:00'));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $partyTime)) {
        $partyTime = '11:00';
    }
    $targetTime = trim((string) ($extraData['event_target_time'] ?? $partyTime));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $targetTime)) {
        $targetTime = '11:00';
    }

    $dateTs = strtotime($rawWeddingDate . ' ' . $targetTime . ':00 +0700') ?: strtotime('2026-12-20 11:00:00 +0700');
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

    $lunarDate = trim((string) ($extraData['wedding_lunar_date'] ?? '(TỨC NGÀY 12 THÁNG 11 NĂM BÍNH NGỌ)'));
    $ceremonyType = trim((string) ($extraData['ceremony_type'] ?? 'LỄ THÀNH HÔN CỦA CON CHÚNG TÔI'));
    $slogan = trim((string) ($extraData['slogan'] ?? 'Dự tiệc chung vui cùng gia đình'));

    // 3. Photos & Media (100% Local Assets)
    $heroPhoto1 = $resolveAsset(
        $wedding?->cover_photo_url ?? ($extraData['hero_photo_url'] ?? ($extraData['cover_photo_url'] ?? '')),
        $assetBase . '/images/hero-couple.webp'
    );
    $heroPhoto2 = $resolveAsset(
        $extraData['hero_photo_2_url'] ?? ($extraData['groom_photo_url'] ?? ''),
        $assetBase . '/images/hero-couple-2.webp'
    );
    $saveDatePhoto = $resolveAsset($extraData['save_date_photo_url'] ?? '', $assetBase . '/images/save-date.webp');
    $footerPhoto = $resolveAsset($extraData['footer_photo_url'] ?? '', $assetBase . '/images/footer-couple.webp');

    // 4. Parents & Families
    $groomFather = trim((string) ($extraData['groom_father'] ?? 'Trần Văn Trung'));
    $groomMother = trim((string) ($extraData['groom_mother'] ?? 'Lê Thị Hằng'));
    $groomParentsAddress = trim((string) ($extraData['groom_parents_address'] ?? 'Quận 1, TP.HCM'));

    $brideFather = trim((string) ($extraData['bride_father'] ?? 'Cao Đình Khoát'));
    $brideMother = trim((string) ($extraData['bride_mother'] ?? 'Lâm Thị Duyên'));
    $brideParentsAddress = trim((string) ($extraData['bride_parents_address'] ?? 'Quận 3, TP.HCM'));

    // 5. Love Story
    $storyTitle = trim((string) ($extraData['story_title'] ?? 'LỜI HỨA BẰNG NỤ CƯỜI'));
    $storySubtitle = trim((string) ($extraData['story_subtitle'] ?? 'Từ nụ cười đầu tiên đến ngày chung một mái nhà'));
    $coupleQuote = trim((string) ($extraData['couple_quote'] ?? '“Hãy sống trọn đời vì tình yêu”'));
    $storyContent = trim((string) ($extraData['story_content'] ?? 'Gặp nhau giữa những ngày bình thường nhất, chúng mình chọn giữ lại nụ cười của đối phương làm điều quý giá nhất suốt chặng đường về sau. Hôm nay, thật hạnh phúc khi được chia sẻ khoảnh khắc này cùng những người thương yêu.'));

    // 6. Venue & Maps & Google Calendar
    $venueName = trim((string) ($wedding?->venue_name ?? ($extraData['venue_name'] ?? 'Trung tâm Tiệc cưới Hoa Hồng')));
    if ($venueName === '') {
        $venueName = 'Trung tâm Tiệc cưới Hoa Hồng';
    }
    $venueAddress = trim((string) ($wedding?->venue_address ?? ($extraData['venue_address'] ?? 'Trung tâm Tiệc cưới Hoa Hồng, Quận 1, TP.HCM')));
    if ($venueAddress === '') {
        $venueAddress = 'Trung tâm Tiệc cưới Hoa Hồng, Quận 1, TP.HCM';
    }
    $venueMapUrl = trim((string) ($wedding?->venue_map_url ?? ($extraData['venue_map_url'] ?? '')));
    if ($venueMapUrl === '') {
        $venueMapUrl = 'https://maps.google.com/?q=' . rawurlencode($venueAddress);
    }
    $mapEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($venueAddress) . '&output=embed';

    $gcalStart = gmdate('Ymd\THis\Z', $dateTs);
    $gcalEnd = gmdate('Ymd\THis\Z', $dateTs + 10800);
    $gcalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . rawurlencode('Lễ Cưới ' . $groomName . ' & ' . $brideName)
        . '&dates=' . $gcalStart . '/' . $gcalEnd
        . '&location=' . rawurlencode($venueAddress)
        . '&details=' . rawurlencode('Trân trọng kính mời ' . $displayGuestName . ' đến chung vui cùng gia đình.');

    // 7. Timeline & Dresscode
    $defaultTimeline = [
        ['time' => '08:30', 'title' => 'Đón dâu & Nghi lễ gia tiên'],
        ['time' => '09:30', 'title' => 'Cử hành Hôn lễ chính thức'],
        ['time' => '10:30', 'title' => 'Đón khách & Chụp ảnh lưu niệm'],
        ['time' => '11:00', 'title' => 'Khai tiệc & Nâng ly chúc phúc'],
        ['time' => '12:30', 'title' => 'Giao lưu âm nhạc & Cảm ơn'],
    ];
    $timelineItems = is_array($extraData['timeline'] ?? null) && count($extraData['timeline']) > 0
        ? $extraData['timeline']
        : $defaultTimeline;

    $dresscodeNote = trim((string) ($extraData['dresscode_note'] ?? 'Áo dài truyền thống hoặc trang phục lịch sự theo tông đỏ son, kem sáng và hồng phấn'));
    $defaultColors = ['#c90000', '#f38c9c', '#fbf5e6', '#6f5d52'];
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
    $giftNote = trim((string) ($extraData['gift_note'] ?? 'Chạm vào hộp quà bên dưới để xem thông tin mừng cưới Nhà Trai & Nhà Gái'));
    $groomBankName = trim((string) ($extraData['groom_bank_name'] ?? 'Vietcombank'));
    $groomBankAccount = trim((string) ($extraData['groom_bank_account'] ?? '0123456789'));
    $groomAccountName = trim((string) ($extraData['groom_account_name'] ?? ($extraData['groom_bank_owner'] ?? 'TRAN TRONG NGHIA')));
    $brideBankName = trim((string) ($extraData['bride_bank_name'] ?? 'Techcombank'));
    $brideBankAccount = trim((string) ($extraData['bride_bank_account'] ?? '9876543210'));
    $brideAccountName = trim((string) ($extraData['bride_account_name'] ?? ($extraData['bride_bank_owner'] ?? 'CAO YEN NHI')));

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
    $endingTitle = trim((string) ($extraData['ending_title'] ?? 'Thank You!'));
    $endingText = trim((string) ($extraData['ending_text'] ?? 'Sự hiện diện và nụ cười của bạn là món quà trân quý nhất trong ngày hạnh phúc của chúng mình!'));

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

@section('title', 'Thiệp Cưới ' . $groomName . ' & ' . $brideName . ' — Lời Hứa Bằng Nụ Cười')
@section('body_class', 'mayhen_invitation_body_smile' . ($isEmbed ? ' mayhen_embed_mode' : '') . ($autoOpened ? ' mayhen_template_auto_opened' : ''))

@push('styles')
    <link rel="stylesheet" href="{{ $assetBase }}/styles.css">
@endpush

@section('content')
<div
    id="mayhen_invitation_root"
    class="mayhen_invitation_root mayhen_invitation_root--smile{{ $isEmbed ? ' mayhen_invitation_root--embed' : '' }}"
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

    {{-- Nút bật/tắt nhạc nổi góc phải --}}
    <button
        type="button"
        id="mayhen_music_toggle"
        class="mayhen_invitation_music_btn"
        aria-label="Bật hoặc tắt nhạc nền thiệp cưới"
        aria-pressed="false"
    >
        <svg class="mayhen_invitation_music_icon_muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 18V5l12-2v13"></path>
            <circle cx="6" cy="18" r="3"></circle>
            <circle cx="18" cy="16" r="3"></circle>
            <line x1="2" y1="2" x2="22" y2="22"></line>
        </svg>
        <svg class="mayhen_invitation_music_icon_note" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 18V5l12-2v13"></path>
            <circle cx="6" cy="18" r="3"></circle>
            <circle cx="18" cy="16" r="3"></circle>
        </svg>
    </button>

    {{-- ================================================================
         MÀN MỞ THIỆP — BLOSSOM ENVELOPE (CHUẨN BẢN GỐC TEMPLATE 26)
         ================================================================ --}}
    <div
        id="mayhen_opening"
        class="mayhen_invitation_opening{{ $autoOpened ? ' is-opened' : '' }}"
        role="dialog"
        aria-modal="true"
        aria-label="Mở thiệp cưới {{ $groomName }} và {{ $brideName }}"
    >
        <div class="mayhen_invitation_open_hy_layer" aria-hidden="true">
            <img src="{{ $assetBase }}/images/open-hy.webp" alt="" class="mayhen_invitation_open_hy mayhen_invitation_open_hy--1">
            <img src="{{ $assetBase }}/images/open-hy.webp" alt="" class="mayhen_invitation_open_hy mayhen_invitation_open_hy--2">
            <img src="{{ $assetBase }}/images/open-hy.webp" alt="" class="mayhen_invitation_open_hy mayhen_invitation_open_hy--3">
            <img src="{{ $assetBase }}/images/open-hy.webp" alt="" class="mayhen_invitation_open_hy mayhen_invitation_open_hy--4">
        </div>

        <div class="mayhen_invitation_open_card">
            <img src="{{ $assetBase }}/images/open-flower.webp" alt="" class="mayhen_invitation_open_decor mayhen_invitation_open_decor--flower" aria-hidden="true">
            <img src="{{ $assetBase }}/images/open-lantern.webp" alt="" class="mayhen_invitation_open_decor mayhen_invitation_open_decor--lantern" aria-hidden="true">
            <img src="{{ $assetBase }}/images/open-fan.webp" alt="" class="mayhen_invitation_open_decor mayhen_invitation_open_decor--fan" aria-hidden="true">
            <img src="{{ $assetBase }}/images/open-cloud.webp" alt="" class="mayhen_invitation_open_decor mayhen_invitation_open_decor--cloud" aria-hidden="true">

            <div class="mayhen_invitation_open_inner">
                <img src="{{ $assetBase }}/images/open-seal.webp" alt="Song Hỷ" class="mayhen_invitation_open_seal">

                <p class="mayhen_invitation_open_names">
                    <span data-sync-target="groom_name">{{ $groomName }}</span>
                    <span class="mayhen_invitation_open_amp">&amp;</span>
                    <span data-sync-target="bride_name">{{ $brideName }}</span>
                </p>

                <div class="mayhen_invitation_open_rule" aria-hidden="true">
                    <span></span><i>◆</i><span></span>
                </div>

                <p class="mayhen_invitation_open_date" data-sync-target="wedding_date_text">{{ $openingDateText }}</p>

                <p class="mayhen_invitation_open_invite_label">Thân Mời</p>
                <div class="mayhen_invitation_open_guest_pill">{{ $displayGuestName }}</div>
                <p class="mayhen_invitation_open_slogan" data-sync-target="slogan">{{ $slogan }}</p>

                <button type="button" id="mayhen_open_btn" class="mayhen_invitation_open_btn">
                    Mở thiệp
                </button>
            </div>
        </div>
    </div>

    {{-- ================================================================
         TRANG THIỆP CHÍNH (WHITE ARTBOARD — MAX 430PX)
         ================================================================ --}}
    <div class="mayhen_invitation_shell card-wrapper">
        <main class="mayhen_invitation_main">

            {{-- SECTION 1: HERO COLLAGE — KHUNG VẼ TAY ĐỎ & NHÂN VẬT CHIBI --}}
            <section
                id="mayhen_section_hero"
                class="mayhen_invitation_section mayhen_invitation_hero"
                data-mayhen-section="hero"
            >
                <div class="mayhen_invitation_hero_header">
                    <img
                        src="{{ $assetBase }}/images/our-wedding-script.webp"
                        alt="Our wedding"
                        class="mayhen_invitation_hero_title_script"
                    >
                </div>

                <div class="mayhen_invitation_hero_collage">
                    {{-- Hàng trên: Ảnh 1 bên trái + Chữ Hỷ Hoa bên phải --}}
                    <div class="mayhen_invitation_collage_top">
                        <figure class="mayhen_invitation_sketch_frame mayhen_invitation_sketch_frame--left">
                            <img
                                src="{{ $heroPhoto1 }}"
                                alt="Ảnh cưới {{ $groomName }} và {{ $brideName }}"
                                class="mayhen_invitation_sketch_photo"
                                data-sync-target="cover_photo_url"
                            >
                            <img
                                src="{{ $assetBase }}/images/frame-red-1.webp"
                                alt=""
                                class="mayhen_invitation_sketch_overlay"
                                aria-hidden="true"
                            >
                        </figure>

                        <div class="mayhen_invitation_congrats_wrap">
                            <img
                                src="{{ $assetBase }}/images/congrats-hy-floral.webp"
                                alt="Chúc mừng hỷ sự"
                                class="mayhen_invitation_congrats_img"
                            >
                        </div>
                    </div>

                    {{-- Hàng giữa: Cô dâu Chibi bên trái + Ảnh 2 bên phải --}}
                    <div class="mayhen_invitation_collage_mid">
                        <div class="mayhen_invitation_chibi_col mayhen_invitation_chibi_col--bride">
                            <span class="mayhen_invitation_role_tag" data-sync-target="bride_role_label">{{ $brideRoleLabel }}</span>
                            <h2 class="mayhen_invitation_chibi_name" data-sync-target="bride_name">{{ $brideName }}</h2>
                            <img
                                src="{{ $assetBase }}/images/chibi-bride.webp"
                                alt="Cô dâu {{ $brideName }}"
                                class="mayhen_invitation_chibi_img"
                            >
                        </div>

                        <figure class="mayhen_invitation_sketch_frame mayhen_invitation_sketch_frame--right">
                            <img
                                src="{{ $heroPhoto2 }}"
                                alt="Khoảnh khắc cưới {{ $groomName }} và {{ $brideName }}"
                                class="mayhen_invitation_sketch_photo"
                                data-sync-target="hero_photo_2_url"
                            >
                            <img
                                src="{{ $assetBase }}/images/frame-red-2.webp"
                                alt=""
                                class="mayhen_invitation_sketch_overlay"
                                aria-hidden="true"
                            >
                        </figure>
                    </div>

                    {{-- Hàng dưới: Trích dẫn viết tay nghiêng bên trái + Chú rể Chibi bên phải --}}
                    <div class="mayhen_invitation_collage_bottom">
                        <div class="mayhen_invitation_quote_tilt">
                            <p class="mayhen_invitation_quote_brush" data-sync-target="couple_quote">{{ $coupleQuote }}</p>
                        </div>

                        <div class="mayhen_invitation_chibi_col mayhen_invitation_chibi_col--groom">
                            <span class="mayhen_invitation_role_tag" data-sync-target="groom_role_label">{{ $groomRoleLabel }}</span>
                            <h2 class="mayhen_invitation_chibi_name" data-sync-target="groom_name">{{ $groomName }}</h2>
                            <img
                                src="{{ $assetBase }}/images/chibi-groom.webp"
                                alt="Chú rể {{ $groomName }}"
                                class="mayhen_invitation_chibi_img"
                            >
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECTION 2: THÔNG TIN GIA ĐÌNH & BÁO TIN LỄ THÀNH HÔN --}}
            <section
                id="mayhen_section_parents"
                class="mayhen_invitation_section mayhen_invitation_parents mayhen_invitation_reveal"
                data-mayhen-section="parents"
            >
                <h2 class="mayhen_invitation_red_title">THÔNG TIN LỄ CƯỚI</h2>

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
            </section>

            {{-- SECTION 3: THỜI GIAN HÔN LỄ, TIỆC CƯỚI & BẢN ĐỒ --}}
            <section
                id="mayhen_section_events"
                class="mayhen_invitation_section mayhen_invitation_events mayhen_invitation_reveal"
                data-mayhen-section="events"
            >
                {{-- Hôn lễ --}}
                <div class="mayhen_invitation_event_block">
                    <p class="mayhen_invitation_event_kicker">HÔN LỄ ĐƯỢC TỔ CHỨC</p>
                    <p class="mayhen_invitation_event_at">VÀO LÚC</p>
                    <p class="mayhen_invitation_event_time" data-sync-target="ceremony_time">{{ $ceremonyTime }}</p>

                    <div class="mayhen_invitation_date_bar">
                        <span class="mayhen_invitation_date_side" data-sync-target="wedding_weekday">{{ $wWeekdayUpper }}</span>
                        <span class="mayhen_invitation_date_pipe" aria-hidden="true">|</span>
                        <span class="mayhen_invitation_date_day" data-sync-target="wedding_day">{{ $wDay }}</span>
                        <span class="mayhen_invitation_date_pipe" aria-hidden="true">|</span>
                        <span class="mayhen_invitation_date_side">THÁNG <span data-sync-target="wedding_month">{{ $wMonthNoZero }}</span></span>
                    </div>

                    <p class="mayhen_invitation_date_year" data-sync-target="wedding_year">{{ $wYear }}</p>
                    <p class="mayhen_invitation_date_lunar" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</p>
                </div>

                {{-- Tiệc cưới --}}
                <div class="mayhen_invitation_event_block">
                    <p class="mayhen_invitation_event_kicker">TIỆC CƯỚI DIỄN RA VÀO LÚC</p>
                    <p class="mayhen_invitation_event_time" data-sync-target="party_time">{{ $partyTime }}</p>

                    <div class="mayhen_invitation_date_bar">
                        <span class="mayhen_invitation_date_side">{{ $wWeekdayUpper }}</span>
                        <span class="mayhen_invitation_date_pipe" aria-hidden="true">|</span>
                        <span class="mayhen_invitation_date_day">{{ $wDay }}</span>
                        <span class="mayhen_invitation_date_pipe" aria-hidden="true">|</span>
                        <span class="mayhen_invitation_date_side">THÁNG {{ $wMonthNoZero }}</span>
                    </div>

                    <p class="mayhen_invitation_date_year">{{ $wYear }}</p>
                    <p class="mayhen_invitation_date_lunar">{{ $lunarDate }}</p>
                </div>

                {{-- Địa điểm & Bản đồ --}}
                <div class="mayhen_invitation_venue_block">
                    <p class="mayhen_invitation_event_kicker">ĐỊA ĐIỂM TỔ CHỨC HÔN LỄ &amp; TIỆC</p>
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
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            Mở trong Maps
                        </a>
                    </div>

                    <button type="button" id="mayhen_jump_rsvp" class="mayhen_invitation_cta_rsvp">
                        Xác nhận tham dự
                    </button>
                </div>
            </section>

            {{-- SECTION 4: LỊCH THÁNG HỒNG PHẤN & ĐẾM NGƯỢC --}}
            <section
                id="mayhen_section_calendar"
                class="mayhen_invitation_section mayhen_invitation_countdown_calendar mayhen_invitation_reveal"
                data-mayhen-section="countdown_calendar"
            >
                <div class="mayhen_invitation_rose_cal">
                    <div class="mayhen_invitation_rose_cal_head">
                        <span class="mayhen_invitation_rose_cal_label">Tháng</span>
                        <span id="mayhen_cal_month" class="mayhen_invitation_rose_cal_month">{{ $wMonth }}</span>
                        <span id="mayhen_cal_year" class="mayhen_invitation_rose_cal_year">{{ $wYear }}</span>
                    </div>

                    <div class="mayhen_invitation_cal_weekdays" aria-hidden="true">
                        <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                    </div>

                    <div id="mayhen_cal_grid" class="mayhen_invitation_cal_grid" aria-label="Lịch tháng ngày cưới"></div>
                </div>

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

                <a
                    href="{{ $gcalUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mayhen_invitation_gcal_btn"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Thêm vào Google Calendar
                </a>
            </section>

            {{-- SECTION 5: CHUYỆN TÌNH YÊU (KHUNG VẼ TAY ĐỎ) --}}
            <section
                id="mayhen_section_story"
                class="mayhen_invitation_section mayhen_invitation_story mayhen_invitation_reveal"
                data-mayhen-section="story"
            >
                <h2 class="mayhen_invitation_red_title" data-sync-target="story_title">{{ $storyTitle }}</h2>
                <p class="mayhen_invitation_story_sub" data-sync-target="story_subtitle">{{ $storySubtitle }}</p>

                <div class="mayhen_invitation_story_card">
                    <figure class="mayhen_invitation_story_frame">
                        <img
                            src="{{ $saveDatePhoto }}"
                            alt="Kỷ niệm {{ $groomName }} và {{ $brideName }}"
                            class="mayhen_invitation_story_photo"
                            data-sync-target="save_date_photo_url"
                        >
                        <img
                            src="{{ $assetBase }}/images/frame-red-3.webp"
                            alt=""
                            class="mayhen_invitation_story_frame_overlay"
                            aria-hidden="true"
                        >
                    </figure>

                    <p class="mayhen_invitation_story_text" data-sync-target="story_content">{{ $storyContent }}</p>
                </div>
            </section>

            {{-- SECTION 6: LỊCH TRÌNH NGÀY CƯỚI --}}
            @if(!$isCuratedSeven)
            <section
                id="mayhen_section_timeline"
                class="mayhen_invitation_section mayhen_invitation_timeline mayhen_invitation_reveal"
                data-mayhen-section="timeline"
            >
                <h2 class="mayhen_invitation_red_title">LỊCH TRÌNH NGÀY CƯỚI</h2>
                <ol class="mayhen_invitation_timeline_list" data-sync-target="timeline">
                    @foreach($timelineItems as $idx => $item)
                        <li class="mayhen_invitation_timeline_item">
                            <span class="mayhen_invitation_timeline_time" data-sync-target="timeline.{{ $idx }}.time">{{ $item['time'] ?? '' }}</span>
                            <span class="mayhen_invitation_timeline_dot" aria-hidden="true"></span>
                            <span class="mayhen_invitation_timeline_title" data-sync-target="timeline.{{ $idx }}.title">{{ $item['title'] ?? '' }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>
            @endif

            {{-- SECTION 7: DRESSCODE --}}
            @if(!$isCuratedSeven)
            <section
                id="mayhen_section_dresscode"
                class="mayhen_invitation_section mayhen_invitation_dresscode mayhen_invitation_reveal"
                data-mayhen-section="dresscode"
            >
                <div class="mayhen_invitation_dresscode_card">
                    <h2 class="mayhen_invitation_red_title">DRESSCODE</h2>
                    <p class="mayhen_invitation_dresscode_note" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>
                    <div class="mayhen_invitation_dresscode_swatches" data-sync-target="dresscode_colors">
                        @foreach($dresscodeColors as $hex)
                            <span class="mayhen_invitation_dresscode_swatch" style="background-color: {{ e($hex) }};" title="{{ e($hex) }}"></span>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif

            {{-- SECTION 8: ALBUM 10 ẢNH CƯỚI + LIGHTBOX --}}
            <section
                id="mayhen_section_album"
                class="mayhen_invitation_section mayhen_invitation_album mayhen_invitation_reveal"
                data-mayhen-section="album"
            >
                <h2 class="mayhen_invitation_red_title">ALBUM KỶ NIỆM</h2>
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
            </section>

            {{-- SECTION 9: HỘP QUÀ MỪNG 3D & VIETQR ĐỘNG --}}
            <section
                id="mayhen_section_gift"
                class="mayhen_invitation_section mayhen_invitation_gift mayhen_invitation_reveal"
                data-mayhen-section="gift"
            >
                <h2 class="mayhen_invitation_gift_heading">HỘP QUÀ MỪNG</h2>
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
                            alt="Mở hộp quà mừng cưới VietQR"
                            class="mayhen_invitation_gift_box_img"
                        >
                        <span class="mayhen_invitation_gift_cta_tag">Chạm để mở VietQR</span>
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
            </section>

            {{-- SECTION 10: KÝ TÊN LƯU BÚT, RSVP (LUẬT #8: 0 ẢNH NGƯỜI), LỜI CHÚC & CẢM ƠN --}}
            <section
                id="mayhen_section_ending"
                class="mayhen_invitation_section mayhen_invitation_ending mayhen_invitation_reveal"
                data-mayhen-section="ending"
            >
                {{-- 10A. Sổ Ký Tên Lưu Bút (0 ảnh người — chỉ dùng icon SVG & khung giấy thuần) --}}
                <div class="mayhen_invitation_sig_card_wrap">
                    <div class="mayhen_invitation_badge_icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </div>
                    <h2 class="mayhen_invitation_red_title">SỔ KÝ TÊN LƯU BÚT</h2>
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
                            <button type="button" class="mayhen_invitation_sig_color is-active" data-sig-color="#111111" style="background-color:#111111;" aria-label="Mực đen"></button>
                            <button type="button" class="mayhen_invitation_sig_color" data-sig-color="#c90000" style="background-color:#c90000;" aria-label="Mực đỏ son"></button>
                            <button type="button" class="mayhen_invitation_sig_color" data-sig-color="#d96b7e" style="background-color:#d96b7e;" aria-label="Mực hồng cánh sen"></button>
                        </div>
                        <div class="mayhen_invitation_sig_actions">
                            <button type="button" id="mayhen_sig_clear" class="mayhen_invitation_btn_ghost">Xóa</button>
                            <button type="button" id="mayhen_sig_download" class="mayhen_invitation_btn_ghost">Tải PNG</button>
                            <button type="button" id="mayhen_sig_save" class="mayhen_invitation_btn_primary">Lưu chữ ký</button>
                        </div>
                    </div>
                    <p id="mayhen_sig_status" class="mayhen_invitation_form_status" aria-live="polite"></p>
                </div>

                {{-- 10B. Xác nhận tham dự RSVP (0 ảnh người — nền gradient hồng đỏ thuần CSS) --}}
                <div id="mayhen_rsvp_box" class="mayhen_invitation_rsvp_stage">
                    <div class="mayhen_invitation_rsvp_card">
                        <div class="mayhen_invitation_rsvp_heart" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                        <h2 class="mayhen_invitation_rsvp_title">Xác nhận tham dự</h2>

                        <form id="mayhen_rsvp_form" class="mayhen_invitation_form">
                            <label class="mayhen_invitation_label" for="mayhen_rsvp_name">Họ và tên *</label>
                            <input type="text" id="mayhen_rsvp_name" name="guest_name" class="mayhen_invitation_input" placeholder="Nhập tên của bạn" required maxlength="100">

                            <span class="mayhen_invitation_label">Bạn sẽ tham dự chứ?</span>
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

                            <button type="submit" class="mayhen_invitation_btn_primary mayhen_invitation_btn_full">Gửi xác nhận</button>
                            <p id="mayhen_rsvp_status" class="mayhen_invitation_form_status" aria-live="polite"></p>
                        </form>
                    </div>
                </div>

                {{-- 10C. Sổ Lời Chúc Phúc --}}
                <div class="mayhen_invitation_wish_box">
                    <h2 class="mayhen_invitation_red_title">GỬI LỜI CHÚC PHÚC</h2>
                    <form id="mayhen_wish_form" class="mayhen_invitation_form">
                        <input type="text" name="name" class="mayhen_invitation_input" placeholder="Tên của bạn *" required maxlength="80">
                        <textarea name="message" rows="3" class="mayhen_invitation_input" placeholder="Viết lời chúc tốt đẹp gửi tới đôi uyên ương..." required maxlength="500"></textarea>
                        <button type="submit" class="mayhen_invitation_btn_primary mayhen_invitation_btn_full">Gửi lời chúc</button>
                        <p id="mayhen_wish_status" class="mayhen_invitation_form_status" aria-live="polite"></p>
                    </form>

                    <div id="mayhen_wish_list" class="mayhen_invitation_wish_list">
                        @forelse($wishesList as $wish)
                            <article class="mayhen_invitation_wish_item">
                                <strong>{{ $wish->guest_name ?? $wish->name ?? $wish['name'] ?? 'Khách quý' }}</strong>
                                <p>{{ $wish->message ?? $wish->content ?? $wish['message'] ?? '' }}</p>
                            </article>
                        @empty
                            <p class="mayhen_invitation_wish_empty">Hãy là người đầu tiên gửi lời chúc ngọt ngào đến {{ $groomName }} &amp; {{ $brideName }}!</p>
                        @endforelse
                    </div>
                </div>

                {{-- 10D. Khung Kết Thiệp & Thank You Calligraphy --}}
                <div class="mayhen_invitation_finale">
                    <figure class="mayhen_invitation_finale_photo_wrap">
                        <img
                            src="{{ $footerPhoto }}"
                            alt="Cảm ơn từ {{ $groomName }} và {{ $brideName }}"
                            class="mayhen_invitation_finale_photo"
                            data-sync-target="footer_photo_url"
                        >
                    </figure>

                    <img
                        src="{{ $assetBase }}/images/thankyou-calligraphy.webp"
                        alt="Thank You"
                        class="mayhen_invitation_finale_script"
                    >
                    <h3 class="mayhen_invitation_sr_only" data-sync-target="ending_title">{{ $endingTitle }}</h3>
                    <p class="mayhen_invitation_finale_text" data-sync-target="ending_text">{{ $endingText }}</p>
                    <p class="mayhen_invitation_finale_couple">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <span>&amp;</span>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </p>
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
