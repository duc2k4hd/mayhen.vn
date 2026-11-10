@php
    $assetBase = url('/mau-thiep/hac-bach-trao-duyen/assets');
    $isEmbed = request()->boolean('embed');
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Hắc Bạch Trao Duyên';

    // 1. Dữ liệu Cô dâu & Chú rể
    $groomName = $wedding->groom_name ?? 'Hà Kiên';
    $brideName = $wedding->bride_name ?? 'Phạm Xôn';
    $extraData = $wedding->extra_data ?? [];
    foreach ($extraData as $key => $value) {
        if ($value === null && !in_array($key, ['gallery', 'timeline', 'dresscode_colors'], true) && !str_ends_with($key, '_url') && !str_ends_with($key, '_image')) {
            $extraData[$key] = '';
        }
    }

    $groomFather = $extraData['groom_father'] ?? 'Lê Quang Minh';
    $groomMother = $extraData['groom_mother'] ?? 'Trần Thị Thu Trang';
    $brideFather = $extraData['bride_father'] ?? 'Lê Quang Minh';
    $brideMother = $extraData['bride_mother'] ?? 'Trần Thị Thu Trang';

    // 2. Ngày giờ và sự kiện cưới
    $weddingDate = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2025, 12, 20, 10, 0, 0);

    $weddingDateDigits = $weddingDate->format('d . m . Y');
    $weddingDay = $weddingDate->format('d');
    $weddingMonth = $weddingDate->format('m');
    $weddingYear = $weddingDate->format('Y');
    $weddingDayOfWeek = $weddingDate->locale('vi')->isoFormat('dddd');
    $weddingLunar = $extraData['wedding_lunar_date'] ?? 'Nhằm ngày 16 tháng 08 năm Ất Tỵ';
    $weddingIso = $weddingDate->toIso8601String();

    // Tiệc cưới chính
    $partyTime = $extraData['party_time'] ?? '10:00';
    $partyVenue = $extraData['party_venue'] ?? 'TẠI TƯ GIA NHÀ TRAI';
    $partyAddress = $extraData['event_party_address'] ?? ($extraData['party_address'] ?? '423 An Dương Vương - Thượng Thuỵ, Phú Thượng, Tây Hồ, Hà Nội');
    $partyMapUrl = $extraData['party_map_url'] ?? ('https://maps.google.com/?q=' . urlencode($partyAddress));

    // Khách mời
    $guestName = isset($guest) && $guest ? $guest->name : request()->query('to', request()->query('name', request()->query('khach', $invitation->guest_name ?? 'Bạn và Gia Đình')));
    $guestSalutation = isset($guest) && $guest ? $guest->salutation : 'Kính mời';

    // 3. Hàm chuẩn hóa asset ảnh an toàn
    $resolveAsset = function (?string $path, string $fallback = '') use ($assetBase): string {
        $val = trim((string) $path);
        if (str_starts_with($val, 'data:')) {
            if (preg_match('#^data:image/(png|jpeg|webp|gif);base64,([A-Za-z0-9+/=]+)$#D', $val, $matches)) {
                $decoded = base64_decode($matches[2], true);
                $image = $decoded === false ? false : @getimagesizefromstring($decoded);
                if ($image !== false && ($image['mime'] ?? '') === 'image/' . $matches[1]) {
                    return $val;
                }
            }
            if (preg_match('#^data:image/svg\+xml;(base64|utf8),(.+)$#Ds', $val, $matches)) {
                $svg = $matches[1] === 'base64' ? base64_decode($matches[2], true) : rawurldecode($matches[2]);
                $document = new \DOMDocument();
                if (is_string($svg) && !str_contains(strtoupper($svg), '<!DOCTYPE') && @$document->loadXML($svg, LIBXML_NONET)) {
                    $xpath = new \DOMXPath($document);
                    $unsafe = $xpath->query('//*[not(local-name()="svg" or local-name()="g" or local-name()="rect" or local-name()="path" or local-name()="circle" or local-name()="text" or local-name()="tspan" or local-name()="defs" or local-name()="clipPath" or local-name()="polygon" or local-name()="polyline" or local-name()="line" or local-name()="ellipse")] | //@*[starts-with(translate(local-name(), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "on") or local-name()="href" or local-name()="style"]');
                    if ($document->documentElement?->localName === 'svg' && $unsafe->length === 0) {
                        return $val;
                    }
                }
            }
            $val = '';
        }
        if (str_starts_with($val, 'blob:')) {
            $val = '';
        }
        if (empty($val)) {
            $fb = trim($fallback);
            if (empty($fb)) return '';
            return (str_starts_with($fb, 'http://') || str_starts_with($fb, 'https://') || str_starts_with($fb, '/'))
                ? $fb
                : $assetBase . '/' . ltrim($fb, '/');
        }
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://') || str_starts_with($val, '/')) {
            return $val;
        }
        return $assetBase . '/' . ltrim($val, '/');
    };

    // Ảnh chính của mẫu
    $heroPhotoUrl = $resolveAsset($wedding->cover_photo_url ?? ($extraData['hero_photo_url'] ?? null), 'hip_1524-20250418032309-dzaln.jpg');
    $saveDatePhotoUrl = $resolveAsset($extraData['save_date_photo_url'] ?? null, 'hip_2251jpg-ok-20250417154242-reh61.jpg');
    $groomPhotoUrl = $resolveAsset($wedding->groom_photo_url ?? ($extraData['groom_photo_url'] ?? null), 'hip_1854-20250417154226-mql7h.jpg');
    $bridePhotoUrl = $resolveAsset($wedding->bride_photo_url ?? ($extraData['bride_photo_url'] ?? null), 'hip_2374-20250417154254-fetzp.jpg');
    $footerPhotoUrl = $resolveAsset($extraData['footer_photo_url'] ?? null, 'hip_2436-20250417154257-30xy0.jpg');

    // Chuyện tình yêu (Love Story)
    $storyStage1Title = $extraData['story_stage_1_title'] ?? 'Lần Đầu Gặp Gỡ';
    $storyStage1Desc = $extraData['story_stage_1_desc'] ?? 'Có ai tin vào tình yêu từ cái nhìn đầu tiên không? Một cô bé khá xinh xắn mà tôi tình cờ gặp trong lần đi chơi Noel cùng đám bạn. Chúng mình bắt đầu trò chuyện, chia sẻ những niềm vui giản dị mỗi ngày và nhận ra hai đứa sinh ra là để dành cho nhau.';
    $storyStage2Title = $extraData['story_stage_2_title'] ?? 'Ngày Em Nhận Lời Yêu';
    $storyStage2Desc = $extraData['story_stage_2_desc'] ?? 'Không cần đắn đo suy nghĩ gì thêm, tôi quyết định ngỏ lời trong một buổi chiều hoàng hôn bên bờ hồ. Thật may mắn khi lời tỏ tình chân thành đã được em mỉm cười đồng ý. Khoảnh khắc ấy, tôi biết mình là người đàn ông hạnh phúc nhất trên đời!';
    $storyStage3Title = $extraData['story_stage_3_title'] ?? 'Mãi Bên Nhau Nhé!';
    $storyStage3Desc = $extraData['story_stage_3_desc'] ?? 'Suy cho cùng, mấy ai yêu mà chẳng mong được cùng nhau tiến tới ngưỡng cửa hôn nhân. Như trái xanh đủ ngày sẽ khắc chín, con chim đủ cánh sẽ khắc bay, tình yêu chúng mình cảm thấy yêu nhau đủ nhiều thì mình cưới nhau thôi.';

    // Quy định trang phục (Dresscode)
    $rawDresscodeColors = $extraData['dresscode_colors'] ?? '#1F1D1A,#85182A,#CFA860,#FAF7F2';
    $dresscodeColors = is_array($rawDresscodeColors) ? $rawDresscodeColors : array_map('trim', explode(',', $rawDresscodeColors));
    $dresscodeColorNames = [
        '#1F1D1A' => 'Đen Huyền Bí',
        '#85182A' => 'Đỏ Bordeaux',
        '#CFA860' => 'Vàng Ánh Kim',
        '#FAF7F2' => 'Trắng Kem Ấm',
    ];
    $dresscodeNote = $extraData['dresscode_note'] ?? 'Khuyến khích trang phục mang sắc màu Đen, Đỏ Bordeaux, Vàng Ánh Kim hoặc Trắng Kem để những khung hình kỷ niệm thêm phần sang trọng và đồng điệu.';

    // 10 ảnh album cưới mặc định
    $defaultGallery = [
        'hip_1314-20250417154153-ezy7i.jpg',
        'hip_1055-20250417154150-fnhf7.jpg',
        'hip_1663-20250417154215-1gooa.jpg',
        'hip_2035-20250417154231-lro9v.jpg',
        'hip_1653-20250417154214-6m0kb.jpg',
        'hip_2216-20250417154241-dok5k.jpg',
        'hip_2450-20250417154258-ynmd_.jpg',
        'hip_1350-20250417154155-wsx2n.jpg',
        'hip_2226-20250417154242-utfbm.jpg',
        'hip_1881-20250417154228-7obv2.jpg',
    ];
    $rawGallery = (!empty($extraData['gallery']) && is_array($extraData['gallery']))
        ? $extraData['gallery']
        : $defaultGallery;

    $gallery = [];
    foreach ($rawGallery as $idx => $photo) {
        $fb = $defaultGallery[$idx % count($defaultGallery)];
        $gallery[] = $resolveAsset($photo, $fb);
    }

    // Ngân hàng mừng cưới
    $groomBankName = $extraData['groom_bank_name'] ?? 'Vietcombank';
    $groomBankAccount = $extraData['groom_bank_account'] ?? '999999999999999';
    $groomBankOwner = $extraData['groom_bank_owner'] ?? 'HÀ VĂN KIÊN';
    $groomQrUrl = $resolveAsset($extraData['groom_qr_url'] ?? null, 'qr-code-27biggroup-20240106151444-o5e-v.png');

    $brideBankName = $extraData['bride_bank_name'] ?? 'Techcombank';
    $brideBankAccount = $extraData['bride_bank_account'] ?? '1234567890';
    $brideBankOwner = $extraData['bride_bank_owner'] ?? 'PHẠM THỊ XÔN';
    $brideQrUrl = $resolveAsset($extraData['bride_qr_url'] ?? null, 'qr-code-27biggroup-20240106151444-o5e-v.png');

    $footerThankYou = $extraData['footer_thank_you_text'] ?? 'Sự hiện diện và lời chúc phúc của quý khách là niềm vinh hạnh to lớn và là món quà ý nghĩa nhất đối với gia đình chúng mình!';

    $pageTitle = $isPreview
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name')
        : 'Thiệp cưới: ' . $groomName . ' & ' . $brideName . ' | ' . config('app.name');

    $cssVersion = file_exists(resource_path('views/client/invitations/hac-bach-trao-duyen/styles.css'))
        ? filemtime(resource_path('views/client/invitations/hac-bach-trao-duyen/styles.css'))
        : time();
    $jsVersion = file_exists(resource_path('views/client/invitations/hac-bach-trao-duyen/script.js'))
        ? filemtime(resource_path('views/client/invitations/hac-bach-trao-duyen/script.js'))
        : time();
@endphp

@extends('client.layouts.invitation')

@section('title', $pageTitle)
@section('meta_description', 'Trân trọng kính mời ' . $guestName . ' tới dự lễ cưới của ' . $groomName . ' & ' . $brideName . '.')
@section('body_class', ($isEmbed ? 'mayhen_invitation_ladjihe38udhd_embed_mode' : 'mayhen_invitation_ladjihe38udhd_scroll_locked'))

@push('fonts')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600;700&family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Cinzel:wght@500;600;700&display=swap" rel="stylesheet">
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ route('templates.asset', ['slug' => 'hac-bach-trao-duyen', 'path' => 'styles.css']) }}?v={{ $cssVersion }}">
@endpush

@section('content')

    <!-- Audio Player (Hidden) -->
    <audio id="mayhen_invitation_ladjihe38udhd_audio" loop preload="auto">
        <source src="{{ $resolveAsset(null, 'wedding-song.mp3') }}" type="audio/mpeg">
    </audio>

    <!-- Floating Audio & Navigation Dock -->
    <aside class="mayhen_invitation_ladjihe38udhd_music_dock" aria-label="Điều khiển âm nhạc và tiện ích">
        <!-- Vinyl Disc Player -->
        <div class="mayhen_invitation_ladjihe38udhd_vinyl_wrap" id="mayhen_invitation_ladjihe38udhd_vinyl_btn" title="Bật/Tắt nhạc">
            <div class="mayhen_invitation_ladjihe38udhd_vinyl_record">
                <div class="mayhen_invitation_ladjihe38udhd_vinyl_grooves"></div>
                <div class="mayhen_invitation_ladjihe38udhd_vinyl_center">
                    <svg viewBox="0 0 24 24" class="mayhen_invitation_ladjihe38udhd_icon_note">
                        <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                    </svg>
                </div>
            </div>
            <!-- Mini Soundwave -->
            <div class="mayhen_invitation_ladjihe38udhd_soundwave">
                <span></span><span></span><span></span><span></span>
            </div>
        </div>

        <!-- Quick Floating Action Icons -->
        <div class="mayhen_invitation_ladjihe38udhd_dock_actions">
            <button type="button" class="mayhen_invitation_ladjihe38udhd_dock_btn" data-target="#rsvp" title="Xác nhận tham dự">
                <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
            </button>
            <button type="button" class="mayhen_invitation_ladjihe38udhd_dock_btn" data-target="#signature" title="Gửi lời chúc & Ký tên">
                <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>
            </button>
            <button type="button" class="mayhen_invitation_ladjihe38udhd_dock_btn" data-target="#gift" title="Mừng cưới">
                <svg viewBox="0 0 24 24"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76V8h2v.76L15.38 12 17 10.83 14.92 8H20v6z"/></svg>
            </button>
            <button type="button" class="mayhen_invitation_ladjihe38udhd_dock_btn" data-target="#events" title="Bản đồ chỉ đường">
                <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            </button>
        </div>
    </aside>

    <!-- ===================== SECTION 1: PHONG BÌ HOÀNG GIA 3D (OPENING OVERLAY) ===================== -->
    <div class="mayhen_invitation_ladjihe38udhd_opening_overlay {{ $isEmbed ? 'closed opened' : '' }}" id="mayhen_invitation_ladjihe38udhd_opening" style="{{ $isEmbed ? 'display: none !important;' : '' }}" role="dialog" aria-label="Thiệp cưới Hà Kiên & Phạm Xôn">
        <div class="mayhen_invitation_ladjihe38udhd_opening_bg"></div>
        <div class="mayhen_invitation_ladjihe38udhd_opening_card mayhen_invitation_ladjihe38udhd_box_anim">
            <div class="mayhen_invitation_ladjihe38udhd_envelope_wrap">
                <div class="mayhen_invitation_ladjihe38udhd_envelope_glow"></div>
                <div class="mayhen_invitation_ladjihe38udhd_envelope_body">
                    <div class="mayhen_invitation_ladjihe38udhd_envelope_flap"></div>
                    <div class="mayhen_invitation_ladjihe38udhd_envelope_peek_card">
                        <img src="{{ $resolveAsset(null, 'hip_2307-20250417154245-1n-2l.jpg') }}" alt="{{ $groomName }} & {{ $brideName }}" class="mayhen_invitation_ladjihe38udhd_peek_img">
                    </div>
                    <div class="mayhen_invitation_ladjihe38udhd_envelope_pocket">
                        <!-- Wax Seal Button -->
                        <button type="button" class="mayhen_invitation_ladjihe38udhd_wax_seal" id="mayhen_invitation_ladjihe38udhd_seal_btn" aria-label="Chạm để mở thiệp">
                            <span class="mayhen_invitation_ladjihe38udhd_seal_stamp">HK & PX</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mayhen_invitation_ladjihe38udhd_opening_content">
                <span class="mayhen_invitation_ladjihe38udhd_opening_subtitle">SAVE THE DATE</span>
                <h1 class="mayhen_invitation_ladjihe38udhd_opening_names">
                    <span data-sync-target="groom_name">{{ $groomName }}</span>
                    <span>&</span>
                    <span data-sync-target="bride_name">{{ $brideName }}</span>
                </h1>
                <div class="mayhen_invitation_ladjihe38udhd_opening_guest">
                    <span class="mayhen_invitation_ladjihe38udhd_guest_label">{{ $guestSalutation }}</span>
                    <span class="mayhen_invitation_ladjihe38udhd_guest_name" id="mayhen_invitation_ladjihe38udhd_guest_open_name">{{ $guestName }}</span>
                </div>
                <button type="button" class="mayhen_invitation_ladjihe38udhd_btn_open" id="mayhen_invitation_ladjihe38udhd_btn_open">
                    <span>MỞ THIỆP CƯỚI</span>
                    <svg viewBox="0 0 24 24" class="mayhen_invitation_ladjihe38udhd_btn_arrow">
                        <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <main class="mayhen_invitation_ladjihe38udhd_wrapper">

        <!-- ===================== SECTION 2: HERO SECTION ===================== -->
        <header class="mayhen_invitation_ladjihe38udhd_hero mayhen_invitation_ladjihe38udhd_box_anim" id="hero">
            <div class="mayhen_invitation_ladjihe38udhd_hero_bg_wrap">
                <img src="{{ $heroPhotoUrl }}" alt="Wedding Couple Hero" class="mayhen_invitation_ladjihe38udhd_hero_img">
                <div class="mayhen_invitation_ladjihe38udhd_hero_overlay"></div>
            </div>

            <div class="mayhen_invitation_ladjihe38udhd_hero_content">
                <div class="mayhen_invitation_ladjihe38udhd_monogram">
                    <span>HK</span>
                    <span class="mayhen_invitation_ladjihe38udhd_mono_amp">&</span>
                    <span>PX</span>
                </div>
                <p class="mayhen_invitation_ladjihe38udhd_hero_tagline">TRÂN TRỌNG KÍNH MỜI</p>
                <div class="mayhen_invitation_ladjihe38udhd_guest_badge">
                    <span id="mayhen_invitation_ladjihe38udhd_guest_main_name">{{ $guestName }}</span>
                </div>
                <h1 class="mayhen_invitation_ladjihe38udhd_hero_title">
                    <span data-sync-target="groom_name">{{ $groomName }}</span>
                    <span class="mayhen_invitation_ladjihe38udhd_amp">&amp;</span>
                    <span data-sync-target="bride_name">{{ $brideName }}</span>
                </h1>
                <p class="mayhen_invitation_ladjihe38udhd_hero_date" data-sync-target="wedding_date">{{ $weddingDateDigits }}</p>

                <!-- Scroll down hint -->
                <a href="#save-date" class="mayhen_invitation_ladjihe38udhd_scroll_down">
                    <span>KÉO XUỐNG</span>
                    <div class="mayhen_invitation_ladjihe38udhd_scroll_mouse">
                        <div class="mayhen_invitation_ladjihe38udhd_scroll_wheel"></div>
                    </div>
                </a>
            </div>
        </header>

        <!-- ===================== SECTION 5: SAVE THE DATE & CALENDAR & COUNTDOWN ===================== -->
        <section class="mayhen_invitation_ladjihe38udhd_section mayhen_invitation_ladjihe38udhd_save_date" id="save-date">
            <div class="mayhen_invitation_ladjihe38udhd_container">

                <div class="mayhen_invitation_ladjihe38udhd_heading_block mayhen_invitation_ladjihe38udhd_box_anim">
                    <span class="mayhen_invitation_ladjihe38udhd_subheading">SAVE THE DATE</span>
                    <h2 class="mayhen_invitation_ladjihe38udhd_heading">Tháng {{ $weddingMonth }} - {{ $weddingYear }}</h2>
                    <div class="mayhen_invitation_ladjihe38udhd_divider">
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_icon">✦</span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                    </div>
                </div>

                <!-- Couple Arch Frame -->
                <div class="mayhen_invitation_ladjihe38udhd_arch_card mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class="mayhen_invitation_ladjihe38udhd_arch_photo_wrap">
                        <img src="{{ $saveDatePhotoUrl }}" alt="Save the date couple" class="mayhen_invitation_ladjihe38udhd_arch_img">
                    </div>
                </div>

                <!-- Wedding Calendar (Tháng cưới) -->
                <div class="mayhen_invitation_ladjihe38udhd_calendar_box mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class="mayhen_invitation_ladjihe38udhd_calendar_header">
                        <span>{{ strtoupper($weddingDate->locale('en')->isoFormat('MMMM YYYY')) }}</span>
                    </div>
                    <div class="mayhen_invitation_ladjihe38udhd_calendar_grid">
                        <div class="mayhen_invitation_ladjihe38udhd_cal_day_name">SUN</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_day_name">MON</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_day_name">TUE</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_day_name">WED</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_day_name">THU</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_day_name">FRI</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_day_name">SAT</div>

                        <!-- Lưới ngày mẫu tháng 12/2025 -->
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell empty"></div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">1</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">2</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">3</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">4</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">5</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">6</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">7</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">8</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">9</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">10</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">11</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">12</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">13</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">14</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">15</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">16</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">17</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">18</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">19</div>
                        <!-- Ngày cưới đặc biệt -->
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell special_day" title="Ngày Hạnh Phúc">
                            <span>{{ (int) $weddingDay }}</span>
                            <span class="mayhen_invitation_ladjihe38udhd_heart_pin">♥</span>
                        </div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">21</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">22</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">23</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">24</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">25</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">26</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">27</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">28</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">29</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">30</div>
                        <div class="mayhen_invitation_ladjihe38udhd_cal_cell">31</div>
                    </div>

                    <div class="mayhen_invitation_ladjihe38udhd_cal_action">
                        <button type="button" class="mayhen_invitation_ladjihe38udhd_btn_calendar" id="mayhen_invitation_ladjihe38udhd_add_cal_btn">
                            <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5z"/></svg>
                            <span>THÊM VÀO LỊCH (GOOGLE CALENDAR)</span>
                        </button>
                    </div>
                </div>

                <!-- COUNTDOWN TIMER -->
                <div class="mayhen_invitation_ladjihe38udhd_countdown_box mayhen_invitation_ladjihe38udhd_box_anim">
                    <p class="mayhen_invitation_ladjihe38udhd_countdown_title">CÙNG ĐẾM NGƯỢC THỜI GIAN</p>
                    <div class="mayhen_invitation_ladjihe38udhd_countdown_grid" id="mayhen_invitation_ladjihe38udhd_countdown" data-wedding-date="{{ $weddingIso }}">
                        <div class="mayhen_invitation_ladjihe38udhd_count_item">
                            <span class="mayhen_invitation_ladjihe38udhd_count_num" id="cd_days">00</span>
                            <span class="mayhen_invitation_ladjihe38udhd_count_label">NGÀY</span>
                        </div>
                        <span class="mayhen_invitation_ladjihe38udhd_count_sep">:</span>
                        <div class="mayhen_invitation_ladjihe38udhd_count_item">
                            <span class="mayhen_invitation_ladjihe38udhd_count_num" id="cd_hours">00</span>
                            <span class="mayhen_invitation_ladjihe38udhd_count_label">GIỜ</span>
                        </div>
                        <span class="mayhen_invitation_ladjihe38udhd_count_sep">:</span>
                        <div class="mayhen_invitation_ladjihe38udhd_count_item">
                            <span class="mayhen_invitation_ladjihe38udhd_count_num" id="cd_minutes">00</span>
                            <span class="mayhen_invitation_ladjihe38udhd_count_label">PHÚT</span>
                        </div>
                        <span class="mayhen_invitation_ladjihe38udhd_count_sep">:</span>
                        <div class="mayhen_invitation_ladjihe38udhd_count_item">
                            <span class="mayhen_invitation_ladjihe38udhd_count_num" id="cd_seconds">00</span>
                            <span class="mayhen_invitation_ladjihe38udhd_count_label">GIÂY</span>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===================== SECTION 4: THÔNG TIN CẶP ĐÔI & GIA ĐÌNH ===================== -->
        <section class="mayhen_invitation_ladjihe38udhd_section mayhen_invitation_ladjihe38udhd_couple_info" id="couple">
            <div class="mayhen_invitation_ladjihe38udhd_container">

                <div class="mayhen_invitation_ladjihe38udhd_heading_block mayhen_invitation_ladjihe38udhd_box_anim">
                    <span class="mayhen_invitation_ladjihe38udhd_subheading">CHÚ RỂ & CÔ DÂU</span>
                    <h2 class="mayhen_invitation_ladjihe38udhd_heading">Hai Mảnh Ghép Hạnh Phúc</h2>
                    <div class="mayhen_invitation_ladjihe38udhd_divider">
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_icon">✦</span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                    </div>
                </div>

                <div class="mayhen_invitation_ladjihe38udhd_couple_grid">
                    <!-- Chú Rể -->
                    <div class="mayhen_invitation_ladjihe38udhd_person_card mayhen_invitation_ladjihe38udhd_box_anim">
                        <div class="mayhen_invitation_ladjihe38udhd_person_photo_wrap">
                            <img src="{{ $groomPhotoUrl }}" alt="Chú rể {{ $groomName }}" class="mayhen_invitation_ladjihe38udhd_person_img">
                            <span class="mayhen_invitation_ladjihe38udhd_person_role">CHÚ RỂ</span>
                        </div>
                        <h3 class="mayhen_invitation_ladjihe38udhd_person_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                        <p class="mayhen_invitation_ladjihe38udhd_family_title">NHÀ TRAI</p>
                        <p class="mayhen_invitation_ladjihe38udhd_family_names">
                            <strong>Ông:</strong> <span data-sync-target="groom_father">{{ $groomFather }}</span><br>
                            <strong>Bà:</strong> <span data-sync-target="groom_mother">{{ $groomMother }}</span>
                        </p>
                    </div>

                    <!-- Ampersand center -->
                    <div class="mayhen_invitation_ladjihe38udhd_couple_center mayhen_invitation_ladjihe38udhd_box_anim">
                        <span class="mayhen_invitation_ladjihe38udhd_couple_ring">
                            <img src="{{ $resolveAsset(null, 'nhan-20231213014544-ritpg.png') }}" alt="Wedding Rings" class="mayhen_invitation_ladjihe38udhd_ring_img">
                        </span>
                    </div>

                    <!-- Cô Dâu -->
                    <div class="mayhen_invitation_ladjihe38udhd_person_card mayhen_invitation_ladjihe38udhd_box_anim">
                        <div class="mayhen_invitation_ladjihe38udhd_person_photo_wrap">
                            <img src="{{ $bridePhotoUrl }}" alt="Cô dâu {{ $brideName }}" class="mayhen_invitation_ladjihe38udhd_person_img">
                            <span class="mayhen_invitation_ladjihe38udhd_person_role">CÔ DÂU</span>
                        </div>
                        <h3 class="mayhen_invitation_ladjihe38udhd_person_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                        <p class="mayhen_invitation_ladjihe38udhd_family_title">NHÀ GÁI</p>
                        <p class="mayhen_invitation_ladjihe38udhd_family_names">
                            <strong>Ông:</strong> <span data-sync-target="bride_father">{{ $brideFather }}</span><br>
                            <strong>Bà:</strong> <span data-sync-target="bride_mother">{{ $brideMother }}</span>
                        </p>
                    </div>
                </div>

                <!-- ===================== SECTION 6: EVENTS & VENUE ===================== -->
                <div class="mayhen_invitation_ladjihe38udhd_event_notice mayhen_invitation_ladjihe38udhd_box_anim" id="events">
                    <p class="mayhen_invitation_ladjihe38udhd_notice_quote">
                        "TỚI DỰ BỮA TIỆC MỪNG CƯỚI CÙNG GIA ĐÌNH CHÚNG MÌNH"
                    </p>
                    <div class="mayhen_invitation_ladjihe38udhd_notice_time">
                        <span class="mayhen_invitation_ladjihe38udhd_notice_hour" data-sync-target="party_time">{{ $partyTime }}</span>
                        <span class="mayhen_invitation_ladjihe38udhd_notice_day">{{ strtoupper($weddingDayOfWeek) }} | <span data-sync-target="wedding_date">{{ $weddingDateDigits }}</span></span>
                        <span class="mayhen_invitation_ladjihe38udhd_lunar" data-sync-target="wedding_lunar_date">({{ $weddingLunar }})</span>
                    </div>
                    <div class="mayhen_invitation_ladjihe38udhd_notice_place">
                        <p class="mayhen_invitation_ladjihe38udhd_place_name" data-sync-target="party_venue">{{ $partyVenue }}</p>
                        <p class="mayhen_invitation_ladjihe38udhd_place_address" data-sync-target="event_party_address">{{ $partyAddress }}</p>
                        <a href="{{ $partyMapUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_ladjihe38udhd_btn_map">
                            <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            <span>XEM VỊ TRÍ TRÊN GOOGLE MAPS</span>
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===================== SECTION 7: TIMELINE LỄ CƯỚI ===================== -->
        <section class="mayhen_invitation_ladjihe38udhd_section mayhen_invitation_ladjihe38udhd_timeline_section" id="timeline">
            <div class="mayhen_invitation_ladjihe38udhd_container">

                <div class="mayhen_invitation_ladjihe38udhd_heading_block mayhen_invitation_ladjihe38udhd_box_anim">
                    <span class="mayhen_invitation_ladjihe38udhd_subheading">TIMELINE</span>
                    <h2 class="mayhen_invitation_ladjihe38udhd_heading">Lịch Trình Hôn Lễ</h2>
                    <div class="mayhen_invitation_ladjihe38udhd_divider">
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_icon">✦</span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                    </div>
                </div>

                <div class="mayhen_invitation_ladjihe38udhd_timeline_list">
                    <!-- Event 1: Ăn Hỏi -->
                    <div class="mayhen_invitation_ladjihe38udhd_timeline_card mayhen_invitation_ladjihe38udhd_box_anim">
                        <div class="mayhen_invitation_ladjihe38udhd_tl_icon_wrap">
                            <img src="{{ $resolveAsset(null, 'ictiec1-300x236-20231213014753-vreis.png') }}" alt="Lễ Ăn Hỏi" class="mayhen_invitation_ladjihe38udhd_tl_icon">
                        </div>
                        <div class="mayhen_invitation_ladjihe38udhd_tl_info">
                            <span class="mayhen_invitation_ladjihe38udhd_tl_time">08:00 - {{ $weddingDateDigits }}</span>
                            <h3 class="mayhen_invitation_ladjihe38udhd_tl_title">LỄ ĂN HỎI</h3>
                            <p class="mayhen_invitation_ladjihe38udhd_tl_place">TẠI TƯ GIA NHÀ GÁI</p>
                            <p class="mayhen_invitation_ladjihe38udhd_tl_desc">Nghi thức trang trọng mở đầu cho ngày vui của hai gia đình.</p>
                        </div>
                    </div>

                    <!-- Event 2: Rước Dâu -->
                    <div class="mayhen_invitation_ladjihe38udhd_timeline_card mayhen_invitation_ladjihe38udhd_box_anim">
                        <div class="mayhen_invitation_ladjihe38udhd_tl_icon_wrap">
                            <img src="{{ $resolveAsset(null, 'icontiec2-20231213014753-naxdk.png') }}" alt="Lễ Rước Dâu" class="mayhen_invitation_ladjihe38udhd_tl_icon">
                        </div>
                        <div class="mayhen_invitation_ladjihe38udhd_tl_info">
                            <span class="mayhen_invitation_ladjihe38udhd_tl_time">09:30 - {{ $weddingDateDigits }}</span>
                            <h3 class="mayhen_invitation_ladjihe38udhd_tl_title">LỄ RƯỚC DÂU</h3>
                            <p class="mayhen_invitation_ladjihe38udhd_tl_place">TẠI TƯ GIA NHÀ GÁI VỀ NHÀ TRAI</p>
                            <p class="mayhen_invitation_ladjihe38udhd_tl_desc">Đón nàng về dinh chuẩn bị cho khoảnh khắc trao nhẫn thiêng liêng.</p>
                        </div>
                    </div>

                    <!-- Event 3: Lễ Thành Hôn & Tiệc Cưới -->
                    <div class="mayhen_invitation_ladjihe38udhd_timeline_card mayhen_invitation_ladjihe38udhd_box_anim">
                        <div class="mayhen_invitation_ladjihe38udhd_tl_icon_wrap">
                            <img src="{{ $resolveAsset(null, 'icon-glass-copy-20231213014753-z7r3d.png') }}" alt="Lễ Thành Hôn" class="mayhen_invitation_ladjihe38udhd_tl_icon">
                        </div>
                        <div class="mayhen_invitation_ladjihe38udhd_tl_info">
                            <span class="mayhen_invitation_ladjihe38udhd_tl_time">{{ $partyTime }} - {{ $weddingDateDigits }}</span>
                            <h3 class="mayhen_invitation_ladjihe38udhd_tl_title">TIỆC MỪNG CƯỚI & LỄ THÀNH HÔN</h3>
                            <p class="mayhen_invitation_ladjihe38udhd_tl_place">{{ $partyVenue }}</p>
                            <p class="mayhen_invitation_ladjihe38udhd_tl_desc">Nâng ly chúc phúc và cùng sẻ chia khoảnh khắc ngọt ngào bên người thân yêu.</p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===================== SECTION 3: CÂU CHUYỆN TÌNH YÊU (LOVE STORY) ===================== -->
        <section class="mayhen_invitation_ladjihe38udhd_section mayhen_invitation_ladjihe38udhd_story_section" id="story">
            <div class="mayhen_invitation_ladjihe38udhd_container">

                <div class="mayhen_invitation_ladjihe38udhd_heading_block mayhen_invitation_ladjihe38udhd_box_anim">
                    <span class="mayhen_invitation_ladjihe38udhd_subheading">OUR STORY</span>
                    <h2 class="mayhen_invitation_ladjihe38udhd_heading">Câu Chuyện Tình Yêu</h2>
                    <div class="mayhen_invitation_ladjihe38udhd_divider">
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_icon">✦</span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                    </div>
                </div>

                <div class="mayhen_invitation_ladjihe38udhd_story_list">
                    <!-- Story Milestone 1 -->
                    <article class="mayhen_invitation_ladjihe38udhd_story_item mayhen_invitation_ladjihe38udhd_box_anim">
                        <div class="mayhen_invitation_ladjihe38udhd_story_photo">
                            <img src="{{ $resolveAsset(null, 'hip_0991-20250418032446-bi00o.jpg') }}" alt="{{ $storyStage1Title }}" class="mayhen_invitation_ladjihe38udhd_story_img">
                        </div>
                        <div class="mayhen_invitation_ladjihe38udhd_story_text">
                            <span class="mayhen_invitation_ladjihe38udhd_story_date">GIAI ĐOẠN 01</span>
                            <h3 class="mayhen_invitation_ladjihe38udhd_story_title">{{ $storyStage1Title }}</h3>
                            <p class="mayhen_invitation_ladjihe38udhd_story_desc">{{ $storyStage1Desc }}</p>
                        </div>
                    </article>

                    <!-- Story Milestone 2 -->
                    <article class="mayhen_invitation_ladjihe38udhd_story_item reverse mayhen_invitation_ladjihe38udhd_box_anim">
                        <div class="mayhen_invitation_ladjihe38udhd_story_photo">
                            <img src="{{ $resolveAsset(null, 'hip_2050-20250418032310-_wmhg.jpg') }}" alt="{{ $storyStage2Title }}" class="mayhen_invitation_ladjihe38udhd_story_img">
                        </div>
                        <div class="mayhen_invitation_ladjihe38udhd_story_text">
                            <span class="mayhen_invitation_ladjihe38udhd_story_date">GIAI ĐOẠN 02</span>
                            <h3 class="mayhen_invitation_ladjihe38udhd_story_title">{{ $storyStage2Title }}</h3>
                            <p class="mayhen_invitation_ladjihe38udhd_story_desc">{{ $storyStage2Desc }}</p>
                        </div>
                    </article>

                    <!-- Story Milestone 3 -->
                    <article class="mayhen_invitation_ladjihe38udhd_story_item mayhen_invitation_ladjihe38udhd_box_anim">
                        <div class="mayhen_invitation_ladjihe38udhd_story_photo">
                            <img src="{{ $resolveAsset(null, 'hip_1858-20250417154227-exs1m.jpg') }}" alt="{{ $storyStage3Title }}" class="mayhen_invitation_ladjihe38udhd_story_img">
                        </div>
                        <div class="mayhen_invitation_ladjihe38udhd_story_text">
                            <span class="mayhen_invitation_ladjihe38udhd_story_date">GIAI ĐOẠN 03</span>
                            <h3 class="mayhen_invitation_ladjihe38udhd_story_title">{{ $storyStage3Title }}</h3>
                            <p class="mayhen_invitation_ladjihe38udhd_story_desc">{{ $storyStage3Desc }}</p>
                        </div>
                    </article>
                </div>

            </div>
        </section>

        <!-- ===================== SECTION 8: QUY ĐỊNH TRANG PHỤC (DRESSCODE) ===================== -->
        <section class="mayhen_invitation_ladjihe38udhd_section mayhen_invitation_ladjihe38udhd_dresscode_section" id="dresscode">
            <div class="mayhen_invitation_ladjihe38udhd_container">

                <div class="mayhen_invitation_ladjihe38udhd_heading_block mayhen_invitation_ladjihe38udhd_box_anim">
                    <span class="mayhen_invitation_ladjihe38udhd_subheading">DRESSCODE</span>
                    <h2 class="mayhen_invitation_ladjihe38udhd_heading">Gợi Ý Trang Phục</h2>
                    <div class="mayhen_invitation_ladjihe38udhd_divider">
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_icon">✦</span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                    </div>
                </div>

                <div class="mayhen_invitation_ladjihe38udhd_dresscode_grid mayhen_invitation_ladjihe38udhd_box_anim">
                    @foreach($dresscodeColors as $color)
                        @php
                            $hex = strtoupper(trim($color));
                            $name = $dresscodeColorNames[$hex] ?? $hex;
                        @endphp
                        <div class="mayhen_invitation_ladjihe38udhd_dresscode_item">
                            <div class="mayhen_invitation_ladjihe38udhd_dresscode_swatch" style="background-color: {{ $hex }};"></div>
                            <span class="mayhen_invitation_ladjihe38udhd_dresscode_label">{{ $name }}</span>
                        </div>
                    @endforeach
                </div>

                <p class="mayhen_invitation_ladjihe38udhd_dresscode_note mayhen_invitation_ladjihe38udhd_box_anim" data-sync-target="dresscode_note">
                    {{ $dresscodeNote }}
                </p>

            </div>
        </section>

        <!-- ===================== SECTION 9: ALBUM 10 ẢNH CƯỚI (PHOTO GALLERY) ===================== -->
        <section class="mayhen_invitation_ladjihe38udhd_section mayhen_invitation_ladjihe38udhd_gallery_section" id="gallery">
            <div class="mayhen_invitation_ladjihe38udhd_container">

                <div class="mayhen_invitation_ladjihe38udhd_heading_block mayhen_invitation_ladjihe38udhd_box_anim">
                    <span class="mayhen_invitation_ladjihe38udhd_subheading">WEDDING GALLERY</span>
                    <h2 class="mayhen_invitation_ladjihe38udhd_heading">Khoảnh Khắc Đẹp Nhất</h2>
                    <div class="mayhen_invitation_ladjihe38udhd_divider">
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_icon">✦</span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                    </div>
                </div>

                <div class="mayhen_invitation_ladjihe38udhd_gallery_grid">
                    @foreach($gallery as $index => $photoUrl)
                        @php
                            $isTall = in_array($index, [0, 5], true);
                            $isWide = in_array($index, [3, 8], true);
                            $extraClass = $isTall ? 'item_tall' : ($isWide ? 'item_wide' : '');
                        @endphp
                        <div class="mayhen_invitation_ladjihe38udhd_gallery_item {{ $extraClass }} mayhen_invitation_ladjihe38udhd_box_anim" data-full="{{ $photoUrl }}">
                            <img src="{{ $photoUrl }}" alt="Wedding Photo {{ $index + 1 }}" class="mayhen_invitation_ladjihe38udhd_gal_img" loading="lazy">
                            <div class="mayhen_invitation_ladjihe38udhd_gal_hover"><span>PHÓNG TO</span></div>
                        </div>
                    @endforeach
                </div>

            </div>
        </section>

        <!-- LIGHTBOX MODAL FOR GALLERY -->
        <div class="mayhen_invitation_ladjihe38udhd_lightbox" id="mayhen_invitation_ladjihe38udhd_lightbox">
            <button type="button" class="mayhen_invitation_ladjihe38udhd_lightbox_close" id="mayhen_invitation_ladjihe38udhd_lb_close">&times;</button>
            <button type="button" class="mayhen_invitation_ladjihe38udhd_lightbox_nav prev" id="mayhen_invitation_ladjihe38udhd_lb_prev">&#10094;</button>
            <div class="mayhen_invitation_ladjihe38udhd_lightbox_body">
                <img src="" alt="Enlarged Wedding Photo" id="mayhen_invitation_ladjihe38udhd_lb_img">
            </div>
            <button type="button" class="mayhen_invitation_ladjihe38udhd_lightbox_nav next" id="mayhen_invitation_ladjihe38udhd_lb_next">&#10095;</button>
        </div>

        <!-- ===================== SECTION 10: SỔ LƯU BÚT KÝ TÊN (SIGNATURE PAD - CẤM ẢNH NGƯỜI LẠ) ===================== -->
        <section class="mayhen_invitation_ladjihe38udhd_section mayhen_invitation_ladjihe38udhd_signature_section" id="signature">
            <div class="mayhen_invitation_ladjihe38udhd_container">

                <div class="mayhen_invitation_ladjihe38udhd_heading_block mayhen_invitation_ladjihe38udhd_box_anim">
                    <span class="mayhen_invitation_ladjihe38udhd_subheading">SIGNATURE BOOK</span>
                    <h2 class="mayhen_invitation_ladjihe38udhd_heading">Sổ Lưu Bút Ký Tên</h2>
                    <div class="mayhen_invitation_ladjihe38udhd_divider">
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_icon">✦</span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                    </div>
                    <p class="mayhen_invitation_ladjihe38udhd_subtext">Để lại chữ ký của Bạn và cùng chúng mình lưu giữ kỷ niệm hạnh phúc này nhé!</p>
                </div>

                <div class="mayhen_invitation_ladjihe38udhd_signature_board mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class="mayhen_invitation_ladjihe38udhd_sign_header">
                        <span>BẢNG KÝ TÊN KỶ NIỆM</span>
                        <button type="button" class="mayhen_invitation_ladjihe38udhd_btn_sign_trigger" id="mayhen_invitation_ladjihe38udhd_btn_open_sign">
                            <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                            <span>NHẤN ĐỂ KÝ TÊN</span>
                        </button>
                    </div>

                    <!-- Signatures wall -->
                    <div class="mayhen_invitation_ladjihe38udhd_signatures_wall" id="mayhen_invitation_ladjihe38udhd_sign_wall">
                        <!-- Dynamic signatures render here -->
                    </div>

                    <!-- Signature Canvas Box -->
                    <div class="mayhen_invitation_ladjihe38udhd_canvas_box" id="mayhen_invitation_ladjihe38udhd_canvas_drawer">
                        <div class="mayhen_invitation_ladjihe38udhd_canvas_header">
                            <span>Khu Vực Ký Tên Trực Tiếp</span>
                            <button type="button" class="mayhen_invitation_ladjihe38udhd_canvas_close" id="mayhen_invitation_ladjihe38udhd_canvas_close">×</button>
                        </div>
                        <div class="mayhen_invitation_ladjihe38udhd_canvas_wrap">
                            <canvas id="mayhen_invitation_ladjihe38udhd_sign_canvas" width="460" height="240"></canvas>
                        </div>
                        <div class="mayhen_invitation_ladjihe38udhd_canvas_actions">
                            <button type="button" class="mayhen_invitation_ladjihe38udhd_btn_clear" id="mayhen_invitation_ladjihe38udhd_btn_clear">
                                <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                                <span>Xóa Làm Lại</span>
                            </button>
                            <button type="button" class="mayhen_invitation_ladjihe38udhd_btn_save_sign" id="mayhen_invitation_ladjihe38udhd_btn_save_sign">
                                <svg viewBox="0 0 24 24"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/></svg>
                                <span>Lưu Chữ Ký</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===================== SECTION 11: XÁC NHẬN THAM DỰ (RSVP - CẤM ẢNH NGƯỜI LẠ) ===================== -->
        <section class="mayhen_invitation_ladjihe38udhd_section mayhen_invitation_ladjihe38udhd_rsvp_section" id="rsvp">
            <div class="mayhen_invitation_ladjihe38udhd_container">

                <div class="mayhen_invitation_ladjihe38udhd_heading_block mayhen_invitation_ladjihe38udhd_box_anim">
                    <span class="mayhen_invitation_ladjihe38udhd_subheading">RSVP</span>
                    <h2 class="mayhen_invitation_ladjihe38udhd_heading">Xác Nhận Tham Dự</h2>
                    <div class="mayhen_invitation_ladjihe38udhd_divider">
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_icon">✦</span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                    </div>
                    <p class="mayhen_invitation_ladjihe38udhd_subtext">
                        Chúng mình rất chờ đón sự kiện trọng đại này của cuộc đời. Xin hãy xác nhận sự có mặt của bạn để chúng mình chuẩn bị đón tiếp một cách chu đáo nhất nhé!
                    </p>
                </div>

                <form class="mayhen_invitation_ladjihe38udhd_rsvp_form mayhen_invitation_ladjihe38udhd_box_anim" id="mayhen_invitation_ladjihe38udhd_rsvp_form">
                    <div class="mayhen_invitation_ladjihe38udhd_form_group">
                        <label for="rsvp_name">Họ và tên của bạn: <span class="required">*</span></label>
                        <input type="text" id="rsvp_name" name="name" required placeholder="Nhập tên khách mời..." class="mayhen_invitation_ladjihe38udhd_input">
                    </div>

                    <div class="mayhen_invitation_ladjihe38udhd_form_group">
                        <label for="rsvp_phone">Số điện thoại liên hệ:</label>
                        <input type="tel" id="rsvp_phone" name="phone" placeholder="Nhập số điện thoại..." class="mayhen_invitation_ladjihe38udhd_input">
                    </div>

                    <div class="mayhen_invitation_ladjihe38udhd_form_group">
                        <label>Bạn là khách mời của:</label>
                        <div class="mayhen_invitation_ladjihe38udhd_radio_group">
                            <label class="mayhen_invitation_ladjihe38udhd_radio_label">
                                <input type="radio" name="side" value="nhà trai" checked>
                                <span>Nhà Trai (Chú rể)</span>
                            </label>
                            <label class="mayhen_invitation_ladjihe38udhd_radio_label">
                                <input type="radio" name="side" value="nhà gái">
                                <span>Nhà Gái (Cô dâu)</span>
                            </label>
                        </div>
                    </div>

                    <div class="mayhen_invitation_ladjihe38udhd_form_group">
                        <label>Xác nhận tham dự tiệc:</label>
                        <div class="mayhen_invitation_ladjihe38udhd_radio_group">
                            <label class="mayhen_invitation_ladjihe38udhd_radio_label">
                                <input type="radio" name="attendance" value="có" checked>
                                <span>Sẽ tham dự chắc chắn</span>
                            </label>
                            <label class="mayhen_invitation_ladjihe38udhd_radio_label">
                                <input type="radio" name="attendance" value="không">
                                <span>Rất tiếc không thể tham dự</span>
                            </label>
                        </div>
                    </div>

                    <div class="mayhen_invitation_ladjihe38udhd_form_group">
                        <label for="rsvp_guests">Số lượng người tham dự cùng bạn:</label>
                        <select id="rsvp_guests" name="guests" class="mayhen_invitation_ladjihe38udhd_select">
                            <option value="1">1 người (Đi một mình)</option>
                            <option value="2" selected>2 người (Đi cùng người thương / bạn)</option>
                            <option value="3">3 người (Gia đình)</option>
                            <option value="4">4+ người</option>
                        </select>
                    </div>

                    <button type="submit" class="mayhen_invitation_ladjihe38udhd_btn_submit">
                        <span>XÁC NHẬN THAM DỰ</span>
                    </button>
                </form>

            </div>
        </section>

        <!-- ===================== SECTION 12: LỜI CHÚC PHÚC (GUESTBOOK) ===================== -->
        <section class="mayhen_invitation_ladjihe38udhd_section mayhen_invitation_ladjihe38udhd_guestbook_section" id="guestbook">
            <div class="mayhen_invitation_ladjihe38udhd_container">

                <div class="mayhen_invitation_ladjihe38udhd_heading_block mayhen_invitation_ladjihe38udhd_box_anim">
                    <span class="mayhen_invitation_ladjihe38udhd_subheading">GUESTBOOK</span>
                    <h2 class="mayhen_invitation_ladjihe38udhd_heading">Lời Chúc Phúc</h2>
                    <div class="mayhen_invitation_ladjihe38udhd_divider">
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_icon">✦</span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                    </div>
                </div>

                <div class="mayhen_invitation_ladjihe38udhd_guestbook_wrap">
                    <form class="mayhen_invitation_ladjihe38udhd_wish_form mayhen_invitation_ladjihe38udhd_box_anim" id="mayhen_invitation_ladjihe38udhd_wish_form">
                        <div class="mayhen_invitation_ladjihe38udhd_form_group">
                            <input type="text" id="wish_name" name="name" required placeholder="Tên của bạn..." class="mayhen_invitation_ladjihe38udhd_input">
                        </div>
                        <div class="mayhen_invitation_ladjihe38udhd_form_group">
                            <textarea id="wish_content" name="message" required rows="4" placeholder="Gửi những lời chúc phúc ngọt ngào nhất tới đôi uyên ương..." class="mayhen_invitation_ladjihe38udhd_textarea"></textarea>
                        </div>
                        <button type="submit" class="mayhen_invitation_ladjihe38udhd_btn_submit">
                            <span>GỬI LỜI CHÚC</span>
                        </button>
                    </form>

                    <div class="mayhen_invitation_ladjihe38udhd_wish_list" id="mayhen_invitation_ladjihe38udhd_wish_list">
                        <div class="mayhen_invitation_ladjihe38udhd_wish_card mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class="mayhen_invitation_ladjihe38udhd_wish_author">
                                <span class="mayhen_invitation_ladjihe38udhd_avatar">T</span>
                                <div>
                                    <strong>Trần Minh Tuấn</strong>
                                    <time>Hôm nay</time>
                                </div>
                            </div>
                            <p class="mayhen_invitation_ladjihe38udhd_wish_msg">
                                Chúc mừng hạnh phúc hai bạn! Chúc {{ $groomName }} & {{ $brideName }} trăm năm tình viên mãn, đầu bạc răng long, luôn yêu thương và thấu hiểu nhau như những ngày đầu!
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===================== SECTION 13: HỘP MỪNG CƯỚI (GIFT / BANK ACCOUNTS / QR) ===================== -->
        <section class="mayhen_invitation_ladjihe38udhd_section mayhen_invitation_ladjihe38udhd_gift_section" id="gift">
            <div class="mayhen_invitation_ladjihe38udhd_container">

                <div class="mayhen_invitation_ladjihe38udhd_heading_block mayhen_invitation_ladjihe38udhd_box_anim">
                    <span class="mayhen_invitation_ladjihe38udhd_subheading">WEDDING GIFT</span>
                    <h2 class="mayhen_invitation_ladjihe38udhd_heading">Hộp Mừng Cưới</h2>
                    <div class="mayhen_invitation_ladjihe38udhd_divider">
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_icon">✦</span>
                        <span class="mayhen_invitation_ladjihe38udhd_divider_line"></span>
                    </div>
                    <p class="mayhen_invitation_ladjihe38udhd_subtext">
                        Thành ý chúc phúc và quà mừng của Quý khách xin được trân trọng ghi nhận qua số tài khoản hoặc quét mã QR dưới đây.
                    </p>
                </div>

                <div class="mayhen_invitation_ladjihe38udhd_bank_grid">
                    <!-- Chú Rể QR -->
                    <div class="mayhen_invitation_ladjihe38udhd_bank_card mayhen_invitation_ladjihe38udhd_box_anim">
                        <div class="mayhen_invitation_ladjihe38udhd_qr_wrap">
                            <img src="{{ $groomQrUrl }}" alt="QR Code Chú Rể" class="mayhen_invitation_ladjihe38udhd_qr_img">
                        </div>
                        <h3 class="mayhen_invitation_ladjihe38udhd_bank_title">MỪNG CƯỚI CHÚ RỂ</h3>
                        <div class="mayhen_invitation_ladjihe38udhd_bank_details">
                            <p><strong>Ngân hàng:</strong> <span data-sync-target="groom_bank_name">{{ $groomBankName }}</span></p>
                            <p><strong>Chủ tài khoản:</strong> <span>{{ $groomBankOwner }}</span></p>
                            <div class="mayhen_invitation_ladjihe38udhd_stk_row">
                                <span><strong>STK:</strong> <span id="stk_groom" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</span></span>
                                <button type="button" class="mayhen_invitation_ladjihe38udhd_btn_copy" data-copy="{{ $groomBankAccount }}">Sao chép</button>
                            </div>
                        </div>
                    </div>

                    <!-- Cô Dâu QR -->
                    <div class="mayhen_invitation_ladjihe38udhd_bank_card mayhen_invitation_ladjihe38udhd_box_anim">
                        <div class="mayhen_invitation_ladjihe38udhd_qr_wrap">
                            <img src="{{ $brideQrUrl }}" alt="QR Code Cô Dâu" class="mayhen_invitation_ladjihe38udhd_qr_img">
                        </div>
                        <h3 class="mayhen_invitation_ladjihe38udhd_bank_title">MỪNG CƯỚI CÔ DÂU</h3>
                        <div class="mayhen_invitation_ladjihe38udhd_bank_details">
                            <p><strong>Ngân hàng:</strong> <span data-sync-target="bride_bank_name">{{ $brideBankName }}</span></p>
                            <p><strong>Chủ tài khoản:</strong> <span>{{ $brideBankOwner }}</span></p>
                            <div class="mayhen_invitation_ladjihe38udhd_stk_row">
                                <span><strong>STK:</strong> <span id="stk_bride" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</span></span>
                                <button type="button" class="mayhen_invitation_ladjihe38udhd_btn_copy" data-copy="{{ $brideBankAccount }}">Sao chép</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===================== SECTION 14: LỜI CẢM ƠN & KẾT THÚC (FOOTER) ===================== -->
        <footer class="mayhen_invitation_ladjihe38udhd_footer mayhen_invitation_ladjihe38udhd_box_anim" id="footer">
            <div class="mayhen_invitation_ladjihe38udhd_container">
                <div class="mayhen_invitation_ladjihe38udhd_footer_content">
                    <div class="mayhen_invitation_ladjihe38udhd_footer_photo_wrap mayhen_invitation_ladjihe38udhd_box_anim">
                        <img src="{{ $footerPhotoUrl }}" alt="Thank you couple" class="mayhen_invitation_ladjihe38udhd_footer_photo">
                    </div>
                    <h2 class="mayhen_invitation_ladjihe38udhd_footer_title">Thank you!</h2>
                    <p class="mayhen_invitation_ladjihe38udhd_footer_desc" data-sync-target="footer_thank_you_text">
                        {{ $footerThankYou }}
                    </p>
                    <div class="mayhen_invitation_ladjihe38udhd_footer_names">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <span>&amp;</span>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </div>
                    <p class="mayhen_invitation_ladjihe38udhd_footer_date" data-sync-target="wedding_date">{{ $weddingDateDigits }}</p>
                </div>
            </div>
        </footer>

    </main>

@endsection

@push('scripts')
    <script src="{{ route('templates.asset', ['slug' => 'hac-bach-trao-duyen', 'path' => 'script.js']) }}?v={{ $jsVersion }}"></script>
@endpush

