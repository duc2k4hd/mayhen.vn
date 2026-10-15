@php
    $assetBase = asset('assets/client/invitations/net-muc-tinh-yeu');
    $isEmbed = request()->boolean('embed');

    // 1. Tên cô dâu chú rể & Gia đình
    $groomName = $wedding->groom_name ?? 'Tràng Đức';
    $brideName = $wedding->bride_name ?? 'Ngọc Phượng';
    $groomParents = $wedding->groom_parents ?? "ÔNG: Nguyễn Tràng Giang\nBÀ: Nguyễn Thị Tám";
    $brideParents = $wedding->bride_parents ?? "ÔNG: Đỗ Như Trung\nBÀ: Lê Thị Hảo";

    // 2. Ngày giờ và sảnh tiệc
    $weddingDate = isset($wedding->wedding_date) ? \Carbon\Carbon::parse($wedding->wedding_date) : \Carbon\Carbon::create(2027, 3, 7, 7, 0, 0);
    $coverDateFormatted = $weddingDate->format('d.m.Y');
    $targetTimestamp = $weddingDate->timestamp * 1000;
    $calendarMonth = $weddingDate->format('m');
    $calendarYear = $weddingDate->format('Y');
    $calendarDay = $weddingDate->day;

    // Sự kiện Nhà Gái
    $brideCeremonyTitle = $wedding->extra_data['bride_ceremony_title'] ?? 'Lễ Vu Quy';
    $brideCeremonyTime = $wedding->extra_data['bride_ceremony_time'] ?? '07:00';
    $brideCeremonyDayName = $wedding->extra_data['bride_ceremony_day_name'] ?? 'Chủ Nhật';
    $brideCeremonyDate = $wedding->extra_data['bride_ceremony_date'] ?? '07/03/2027';
    $brideCeremonyDayNum = explode('/', $brideCeremonyDate)[0] ?? '07';
    $brideCeremonyMonthNum = explode('/', $brideCeremonyDate)[1] ?? '03';
    $brideCeremonyYearNum = explode('/', $brideCeremonyDate)[2] ?? '2027';
    $brideCeremonyLunar = $wedding->extra_data['bride_ceremony_lunar'] ?? '(Nhằm Ngày 30 Tháng 01 Năm Đinh Mùi)';
    $brideCeremonyAddress = $wedding->extra_data['bride_ceremony_address'] ?? 'Xóm 22, Khoái Châu, Hưng Yên';

    $bridePartyTitle = $wedding->extra_data['bride_party_title'] ?? 'Tiệc Cưới';
    $bridePartyTime = $wedding->extra_data['bride_party_time'] ?? '17:00';
    $bridePartyDayName = $wedding->extra_data['bride_party_day_name'] ?? 'Thứ Sáu';
    $bridePartyDate = $wedding->extra_data['bride_party_date'] ?? '06/03/2026';
    $bridePartyDayNum = explode('/', $bridePartyDate)[0] ?? '06';
    $bridePartyMonthNum = explode('/', $bridePartyDate)[1] ?? '03';
    $bridePartyYearNum = explode('/', $bridePartyDate)[2] ?? '2026';
    $bridePartyLunar = $wedding->extra_data['bride_party_lunar'] ?? '(Nhằm Ngày 18 Tháng 01 Năm Bính Ngọ)';
    $bridePartyAddress = $wedding->extra_data['bride_party_address'] ?? 'Xóm 22, Khoái Châu, Hưng Yên';
    $brideMapLink = $wedding->extra_data['bride_map_link'] ?? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($bridePartyAddress);

    // Sự kiện Nhà Trai
    $groomCeremonyTitle = $wedding->extra_data['groom_ceremony_title'] ?? 'Lễ Thành Hôn';
    $groomCeremonyTime = $wedding->extra_data['groom_ceremony_time'] ?? '10:00';
    $groomCeremonyDayName = $wedding->extra_data['groom_ceremony_day_name'] ?? 'Chủ Nhật';
    $groomCeremonyDate = $wedding->extra_data['groom_ceremony_date'] ?? '07/03/2027';
    $groomCeremonyDayNum = explode('/', $groomCeremonyDate)[0] ?? '07';
    $groomCeremonyMonthNum = explode('/', $groomCeremonyDate)[1] ?? '03';
    $groomCeremonyYearNum = explode('/', $groomCeremonyDate)[2] ?? '2027';
    $groomCeremonyLunar = $wedding->extra_data['groom_ceremony_lunar'] ?? '(Nhằm Ngày 30 Tháng 01 Năm Đinh Mùi)';
    $groomCeremonyAddress = $wedding->extra_data['groom_ceremony_address'] ?? 'Số nhà 5, Ngõ 56, Siêu Quần, Đại Thanh, Hà Nội';

    $groomPartyTitle = $wedding->extra_data['groom_party_title'] ?? 'Tiệc Cưới';
    $groomPartyTime = $wedding->extra_data['groom_party_time'] ?? '18:00';
    $groomPartyDayName = $wedding->extra_data['groom_party_day_name'] ?? 'Thứ Sáu';
    $groomPartyDate = $wedding->extra_data['groom_party_date'] ?? '06/03/2026';
    $groomPartyDayNum = explode('/', $groomPartyDate)[0] ?? '06';
    $groomPartyMonthNum = explode('/', $groomPartyDate)[1] ?? '03';
    $groomPartyYearNum = explode('/', $groomPartyDate)[2] ?? '2026';
    $groomPartyLunar = $wedding->extra_data['groom_party_lunar'] ?? '(Nhằm Ngày 18 Tháng 01 Năm Bính Ngọ)';
    $groomPartyAddress = $wedding->extra_data['groom_party_address'] ?? 'Số nhà 5, Ngõ 56, Siêu Quần, Đại Thanh, Hà Nội';
    $groomMapLink = $wedding->extra_data['groom_map_link'] ?? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($groomPartyAddress);

    // 3. Ảnh cưới
    $coverPhotoUrl = $wedding->cover_photo_url ?? ($assetBase . '/assets/3ed9dcf0-26a2-47ad-af9e-8895bff01519.jpg');
    $heroPhotoUrl = $wedding->extra_data['hero_photo_url'] ?? ($assetBase . '/assets/3ed9dcf0-26a2-47ad-af9e-8895bff01519.jpg');
    $triptych1 = $wedding->extra_data['triptych_photo_1'] ?? ($assetBase . '/assets/c3ee7507-53b8-461b-b5cb-c416ffc03791.jpg');
    $triptych2 = $wedding->extra_data['triptych_photo_2'] ?? ($assetBase . '/assets/feecd763-f272-424b-9985-b4f3d68d7fb1.jpg');
    $triptych3 = $wedding->extra_data['triptych_photo_3'] ?? ($assetBase . '/assets/320f95e0-b0c7-4b48-9d11-16c791076898.jpg');
    $footerPhotoUrl = $wedding->extra_data['footer_photo_url'] ?? ($assetBase . '/assets/8abb1d4e-e543-4ccf-994d-7114bbdfa561.jpg');

    // 10 ảnh trong Album cưới
    $defaultAlbum = [
        $assetBase . '/assets/17561028-8353-443f-9499-3cacf8e00f39.jpg',
        $assetBase . '/assets/3d8c97cc-9a05-42c1-916e-c76f3e635f19.jpg',
        $assetBase . '/assets/5bf6d6e7-c1f7-4b0b-bf30-011dc81a7191.jpg',
        $assetBase . '/assets/58d06c3d-d76a-437b-8661-7058afa6798a.jpg',
        $assetBase . '/assets/42c5230a-1ea7-4043-906f-545987e51dee.jpg',
        $assetBase . '/assets/b0406e5d-ad0e-4074-87cc-3cfb05978f06.jpg',
        $assetBase . '/assets/96c5fed3-ce54-4fd7-83e7-2e7e5019190a.jpg',
        $assetBase . '/assets/7fbc223f-7c5e-4438-a3c7-6c2aff07be19.jpg',
        $assetBase . '/assets/6b382386-e964-4bfa-80f0-25285128f65d.jpg',
        $assetBase . '/assets/e5ed00b3-76fb-4467-bf7f-d9c436472f4e.jpg',
    ];
    $albumPhotos = !empty($wedding->extra_data['gallery']) ? $wedding->extra_data['gallery'] : $defaultAlbum;

    // 4. Lời ngỏ / Quote
    $loveQuote = $wedding->story ?? 'Một tình yêu được khắc trên tim, cùng nhau đi qua năm tháng';

    // 5. Timeline (Lịch trình)
    $timelineItems = $wedding->extra_data['timeline'] ?? [
        ['time' => '05:00', 'title' => 'Xuất phát từ nhà trai'],
        ['time' => '07:00', 'title' => 'Đến nhà cô dâu'],
        ['time' => '07:50', 'title' => 'Bắt đầu về nhà trai'],
        ['time' => '10:00', 'title' => 'Lễ thành hôn'],
        ['time' => '11:00', 'title' => 'Chụp ảnh kỷ niệm'],
        ['time' => '11:30', 'title' => 'Dự tiệc'],
    ];

    // 6. Mừng cưới QR & Nhạc nền
    $qrGroom = $wedding->extra_data['qr_groom_image'] ?? ($assetBase . '/assets/4904bdb1-f9fa-4fdc-a3b2-f6a0be4df18b.jpg');
    $qrBride = $wedding->extra_data['qr_bride_image'] ?? ($assetBase . '/assets/b9782cd0-2a24-412c-bd15-2626bbe77477.jpg');
    $musicUrl = $wedding->music_url ?? ($assetBase . '/assets/c236c024-0b4a-472e-a8fb-f64e2fead1a0.mp3');

    // 7. Sổ lưu bút (Wishes & Guestbook)
    $wishes = $wishes ?? collect([]);
    $wishSubmitUrl = $wishSubmitUrl ?? (isset($invitation) ? route('invitation.wish.store', $invitation->slug) : route('templates.preview.wish', 'net-muc-tinh-yeu'));
@endphp
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Thiệp cưới {{ $groomName }} & {{ $brideName }} – ngày {{ $coverDateFormatted }}.">
    <title>Thiệp Mời Cưới {{ $groomName }} & {{ $brideName }}</title>

    <link rel="stylesheet" href="{{ $assetBase }}/styles.css">
    
    @if ($isEmbed)
        <style>
            body { background: transparent !important; }
            .mayhen_invitation_dhsuudushi9428r_page {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        </style>
    @endif

    <script>
        window.__MAYHEN_WEDDING_TARGET_TIME = {{ $targetTimestamp }};
        window.__MAYHEN_WEDDING_DATE = {
            year: {{ $calendarYear }},
            month: {{ (int)$calendarMonth }},
            day: {{ (int)$calendarDay }}
        };
        window.__MAYHEN_GIFT_QR_GROOM = "{{ $qrGroom }}";
        window.__MAYHEN_GIFT_QR_BRIDE = "{{ $qrBride }}";
        window.__MAYHEN_WISH_SUBMIT_URL = "{{ $wishSubmitUrl }}";
        window.__MAYHEN_CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
    <script src="{{ $assetBase }}/script.js" defer></script>
</head>
<body class="mayhen_invitation_scroll_locked">
    <main class="mayhen_invitation_dhsuudushi9428r_page">
        {{-- Section 1: Màn hình mở đầu & Phong bì mở thiệp sang trọng --}}
        <section class="mayhen_invitation_dhsuudushi9428r_cover" id="mayhen_invitation_cover" aria-label="Mở thiệp mời cưới">
            <div class="mayhen_invitation_dhsuudushi9428r_cover_heading">
                <img class="mayhen_invitation_dhsuudushi9428r_corner mayhen_invitation_dhsuudushi9428r_corner_right" src="{{ $assetBase }}/assets/goc-vuong.png" alt="" aria-hidden="true">
                <img class="mayhen_invitation_dhsuudushi9428r_corner" src="{{ $assetBase }}/assets/goc-vuong.png" alt="" aria-hidden="true">
                <p class="mayhen_invitation_dhsuudushi9428r_kicker">Thiệp</p>
                <h1 class="mayhen_invitation_dhsuudushi9428r_display mayhen_invitation_dhsuudushi9428r_cover_title">Mời Cưới</h1>
                
                <div class="mayhen_invitation_dhsuudushi9428r_cover_names">
                    <img src="{{ $assetBase }}/assets/chu-hy-1.png" alt="" aria-hidden="true">
                    <p class="mayhen_invitation_dhsuudushi9428r_display">
                        <span data-sync-target="groom_name">{{ $groomName }}</span>
                        <span class="mayhen_heart">❤</span>
                        <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </p>
                    <img src="{{ $assetBase }}/assets/chu-hy-1.png" alt="" aria-hidden="true">
                </div>
                <p class="mayhen_invitation_dhsuudushi9428r_cover_date" data-sync-target="cover_date">{{ $coverDateFormatted }}</p>
            </div>

            {{-- 3D Realistic Interactive Envelope --}}
            <div class="mayhen_invitation_dhsuudushi9428r_envelope_stage">
                <div class="mayhen_invitation_dhsuudushi9428r_envelope" id="mayhen_envelope_element" role="button" tabindex="0" aria-label="Chạm để mở thiệp mời" aria-expanded="false">
                    {{-- Thân lưng phong bì --}}
                    <div class="mayhen_invitation_dhsuudushi9428r_envelope_back">
                        <div class="mayhen_invitation_dhsuudushi9428r_envelope_liner"></div>
                    </div>
                    
                    {{-- Tấm thiệp bên trong sẽ trồi lên khi mở --}}
                    <div class="mayhen_invitation_dhsuudushi9428r_envelope_letter">
                        <img id="sync_cover_photo" class="mayhen_invitation_dhsuudushi9428r_envelope_letter_img" src="{{ $coverPhotoUrl }}" alt="Ảnh cưới {{ $groomName }} và {{ $brideName }}">
                        <span class="mayhen_invitation_dhsuudushi9428r_envelope_letter_text">Thiệp Mời Hôn Lễ</span>
                    </div>

                    {{-- Nắp gập phong bì 3D --}}
                    <div class="mayhen_invitation_dhsuudushi9428r_envelope_flap"></div>

                    {{-- Tem sáp niêm phong (Wax Seal) --}}
                    <div class="mayhen_invitation_dhsuudushi9428r_wax_seal" title="Chạm để mở thiệp">
                        囍
                    </div>

                    {{-- Cánh trước phong bì --}}
                    <div class="mayhen_invitation_dhsuudushi9428r_envelope_front">
                        <div class="mayhen_invitation_dhsuudushi9428r_envelope_pocket"></div>
                    </div>
                </div>
            </div>

            {{-- CTA Button & Hint --}}
            <div class="mayhen_invitation_dhsuudushi9428r_open_cta">
                <button type="button" class="mayhen_invitation_dhsuudushi9428r_open_btn" id="mayhen_open_invitation_btn">
                    <span>✉</span> Chạm Mở Thiệp
                </button>
                <p class="mayhen_invitation_dhsuudushi9428r_open_hint">Chạm vào phong bì hoặc tem sáp để mở thiệp</p>
            </div>

            {{-- Nút cuộn xuống sau khi mở thiệp --}}
            <div class="mayhen_invitation_dhsuudushi9428r_scroll_down">
                <button type="button" id="mayhen_scroll_down_btn">
                    Cuộn xuống xem tiếp ↓
                </button>
            </div>
        </section>

        {{-- MAIN INVITATION BODY (Nội dung chi tiết được cuộn xuống sau khi mở) --}}
        <div class="mayhen_invitation_dhsuudushi9428r_main_body" id="mayhen_invitation_main_content">
            {{-- Section 2: Gia đình hai bên (Thiết kế đối xứng cân bằng Quiet Luxury) --}}
            <section class="mayhen_invitation_dhsuudushi9428r_families" id="mayhen_invitation_dhsuudushi9428r_families" aria-label="Gia đình hai bên">
                <div class="mayhen_invitation_dhsuudushi9428r_family_card mayhen_invitation_dhsuudushi9428r_reveal">
                    <div class="mayhen_invitation_dhsuudushi9428r_family_col">
                        <span class="mayhen_invitation_dhsuudushi9428r_family_badge">NHÀ TRAI</span>
                        <div class="mayhen_invitation_dhsuudushi9428r_family_text" data-sync-target="groom_parents">{!! nl2br(e($groomParents)) !!}</div>
                    </div>
                    <div class="mayhen_invitation_dhsuudushi9428r_family_divider" aria-hidden="true">
                        <img src="{{ $assetBase }}/assets/chu-hy-1.png" alt="">
                    </div>
                    <div class="mayhen_invitation_dhsuudushi9428r_family_col">
                        <span class="mayhen_invitation_dhsuudushi9428r_family_badge">NHÀ GÁI</span>
                        <div class="mayhen_invitation_dhsuudushi9428r_family_text" data-sync-target="bride_parents">{!! nl2br(e($brideParents)) !!}</div>
                    </div>
                </div>
                <div class="mayhen_invitation_dhsuudushi9428r_family_subtext mayhen_invitation_dhsuudushi9428r_reveal">
                    Trân trọng thông báo tin hôn lễ
                </div>
            </section>

            {{-- Section 3: Câu chuyện & Bộ ảnh cưới --}}
            <section class="mayhen_invitation_dhsuudushi9428r_story" aria-label="Cô dâu chú rể">
                <div class="mayhen_invitation_dhsuudushi9428r_story_names mayhen_invitation_dhsuudushi9428r_reveal">
                    <span class="mayhen_invitation_dhsuudushi9428r_display" data-sync-target="groom_name">{{ $groomName }}</span>
                    <span class="mayhen_invitation_dhsuudushi9428r_story_amp">&amp;</span>
                    <span class="mayhen_invitation_dhsuudushi9428r_display" data-sync-target="bride_name">{{ $brideName }}</span>
                </div>

                <div class="mayhen_invitation_dhsuudushi9428r_hero_wrap mayhen_invitation_dhsuudushi9428r_reveal">
                    <img id="sync_hero_photo" class="mayhen_invitation_dhsuudushi9428r_hero_photo" src="{{ $heroPhotoUrl }}" alt="Ảnh cưới {{ $groomName }} và {{ $brideName }}" loading="eager">
                </div>
                
                <p id="sync_love_quote" class="mayhen_invitation_dhsuudushi9428r_quote mayhen_invitation_dhsuudushi9428r_reveal" data-sync-target="story">“{{ $loveQuote }}”</p>

                <div class="mayhen_invitation_dhsuudushi9428r_triptych mayhen_invitation_dhsuudushi9428r_reveal">
                    <img id="sync_triptych_1" src="{{ $triptych1 }}" alt="Ảnh cưới 1" loading="lazy">
                    <img id="sync_triptych_2" src="{{ $triptych2 }}" alt="Ảnh cưới 2" loading="lazy">
                    <img id="sync_triptych_3" src="{{ $triptych3 }}" alt="Ảnh cưới 3" loading="lazy">
                </div>
            </section>

            {{-- Section 4: Lời mời và Hai tab Lễ cưới Nhà Gái / Nhà Trai --}}
            <section class="mayhen_invitation_dhsuudushi9428r_invite" id="mayhen_invitation_dhsuudushi9428r_invite">
                <div class="mayhen_invitation_dhsuudushi9428r_reveal">
                    <p class="mayhen_invitation_dhsuudushi9428r_invite_title">Trân Trọng Kính Mời</p>
                    <div class="mayhen_invitation_dhsuudushi9428r_rule"><span>✦</span></div>
                    <p class="mayhen_invitation_dhsuudushi9428r_invite_intro">Chúng tôi rất vinh dự khi được mời quý khách đến dự lễ cưới và cùng chia sẻ niềm hạnh phúc trong ngày trọng đại này.</p>
                    
                    <div class="mayhen_invitation_dhsuudushi9428r_tabs" role="tablist" aria-label="Thông tin lễ cưới">
                        <button class="mayhen_invitation_dhsuudushi9428r_tab mayhen_invitation_dhsuudushi9428r_tab_active" role="tab" aria-selected="true" aria-controls="mayhen_invitation_dhsuudushi9428r_bride" id="mayhen_invitation_dhsuudushi9428r_bride_tab" type="button">Tiệc Nhà Gái</button>
                        <button class="mayhen_invitation_dhsuudushi9428r_tab" role="tab" aria-selected="false" aria-controls="mayhen_invitation_dhsuudushi9428r_groom" id="mayhen_invitation_dhsuudushi9428r_groom_tab" type="button">Tiệc Nhà Trai</button>
                    </div>

                    {{-- Tab Nhà Gái --}}
                    <div class="mayhen_invitation_dhsuudushi9428r_event_panel" id="mayhen_invitation_dhsuudushi9428r_bride" role="tabpanel" aria-labelledby="mayhen_invitation_dhsuudushi9428r_bride_tab">
                        <article class="mayhen_invitation_dhsuudushi9428r_event">
                            <h2 data-sync-target="bride_ceremony_title">{{ $brideCeremonyTitle }}</h2>
                            <p class="mayhen_invitation_dhsuudushi9428r_event_time">Vào lúc: <span data-sync-target="bride_ceremony_time">{{ $brideCeremonyTime }}</span></p>
                            <p data-sync-target="bride_ceremony_day_name">{{ $brideCeremonyDayName }}</p>
                            <p class="mayhen_invitation_dhsuudushi9428r_date_tile">
                                <small>THÁNG {{ $brideCeremonyMonthNum }}</small>
                                <b>{{ $brideCeremonyDayNum }}</b>
                                <small>{{ $brideCeremonyYearNum }}</small>
                            </p>
                            <p class="mayhen_invitation_dhsuudushi9428r_lunar" data-sync-target="bride_ceremony_lunar">{{ $brideCeremonyLunar }}</p>
                            <p class="mayhen_invitation_dhsuudushi9428r_address" data-sync-target="bride_ceremony_address">{{ $brideCeremonyAddress }}</p>
                            <a target="_blank" rel="noopener noreferrer" href="{{ $brideMapLink }}">Xem chỉ đường ↗</a>
                        </article>

                        <article class="mayhen_invitation_dhsuudushi9428r_event">
                            <h2 data-sync-target="bride_party_title">{{ $bridePartyTitle }}</h2>
                            <p class="mayhen_invitation_dhsuudushi9428r_event_time">Vào lúc: <span data-sync-target="bride_party_time">{{ $bridePartyTime }}</span></p>
                            <p data-sync-target="bride_party_day_name">{{ $bridePartyDayName }}</p>
                            <p class="mayhen_invitation_dhsuudushi9428r_date_tile">
                                <small>THÁNG {{ $bridePartyMonthNum }}</small>
                                <b>{{ $bridePartyDayNum }}</b>
                                <small>{{ $bridePartyYearNum }}</small>
                            </p>
                            <p class="mayhen_invitation_dhsuudushi9428r_lunar" data-sync-target="bride_party_lunar">{{ $bridePartyLunar }}</p>
                            <p class="mayhen_invitation_dhsuudushi9428r_address" data-sync-target="bride_party_address">{{ $bridePartyAddress }}</p>
                            <a target="_blank" rel="noopener noreferrer" href="{{ $brideMapLink }}">Xem chỉ đường ↗</a>
                        </article>
                    </div>

                    {{-- Tab Nhà Trai --}}
                    <div class="mayhen_invitation_dhsuudushi9428r_event_panel" id="mayhen_invitation_dhsuudushi9428r_groom" role="tabpanel" aria-labelledby="mayhen_invitation_dhsuudushi9428r_groom_tab" hidden>
                        <article class="mayhen_invitation_dhsuudushi9428r_event">
                            <h2 data-sync-target="groom_ceremony_title">{{ $groomCeremonyTitle }}</h2>
                            <p class="mayhen_invitation_dhsuudushi9428r_event_time">Vào lúc: <span data-sync-target="groom_ceremony_time">{{ $groomCeremonyTime }}</span></p>
                            <p data-sync-target="groom_ceremony_day_name">{{ $groomCeremonyDayName }}</p>
                            <p class="mayhen_invitation_dhsuudushi9428r_date_tile">
                                <small>THÁNG {{ $groomCeremonyMonthNum }}</small>
                                <b>{{ $groomCeremonyDayNum }}</b>
                                <small>{{ $groomCeremonyYearNum }}</small>
                            </p>
                            <p class="mayhen_invitation_dhsuudushi9428r_lunar" data-sync-target="groom_ceremony_lunar">{{ $groomCeremonyLunar }}</p>
                            <p class="mayhen_invitation_dhsuudushi9428r_address" data-sync-target="groom_ceremony_address">{{ $groomCeremonyAddress }}</p>
                            <a target="_blank" rel="noopener noreferrer" href="{{ $groomMapLink }}">Xem chỉ đường ↗</a>
                        </article>

                        <article class="mayhen_invitation_dhsuudushi9428r_event">
                            <h2 data-sync-target="groom_party_title">{{ $groomPartyTitle }}</h2>
                            <p class="mayhen_invitation_dhsuudushi9428r_event_time">Vào lúc: <span data-sync-target="groom_party_time">{{ $groomPartyTime }}</span></p>
                            <p data-sync-target="groom_party_day_name">{{ $groomPartyDayName }}</p>
                            <p class="mayhen_invitation_dhsuudushi9428r_date_tile">
                                <small>THÁNG {{ $groomPartyMonthNum }}</small>
                                <b>{{ $groomPartyDayNum }}</b>
                                <small>{{ $groomPartyYearNum }}</small>
                            </p>
                            <p class="mayhen_invitation_dhsuudushi9428r_lunar" data-sync-target="groom_party_lunar">{{ $groomPartyLunar }}</p>
                            <p class="mayhen_invitation_dhsuudushi9428r_address" data-sync-target="groom_party_address">{{ $groomPartyAddress }}</p>
                            <a target="_blank" rel="noopener noreferrer" href="{{ $groomMapLink }}">Xem chỉ đường ↗</a>
                        </article>
                    </div>

                    <div class="mayhen_invitation_dhsuudushi9428r_dress">
                        <p>DRESS CODE GỢI Ý</p>
                        <span data-sync-target="dresscode_hint">{{ $wedding->extra_data['dresscode_hint'] ?? 'Trắng, Nâu nhạt, Đỏ trầm' }}</span>
                        <div class="mayhen_invitation_dhsuudushi9428r_swatches">
                            <i title="Trắng"></i>
                            <i title="Nâu nhạt"></i>
                            <i title="Đỏ trầm"></i>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Section 5: Lịch, Đếm ngược & Timeline --}}
            <section class="mayhen_invitation_dhsuudushi9428r_schedule" aria-label="Lịch và chương trình cưới">
                <div class="mayhen_invitation_dhsuudushi9428r_calendar mayhen_invitation_dhsuudushi9428r_reveal">
                    <h2>Tháng {{ $calendarMonth }} <span>/ {{ $calendarYear }}</span></h2>
                    <div class="mayhen_invitation_dhsuudushi9428r_calendar_grid" id="mayhen_invitation_dhsuudushi9428r_calendar"></div>
                </div>

                <div class="mayhen_invitation_dhsuudushi9428r_countdown mayhen_invitation_dhsuudushi9428r_reveal">
                    <p>Cùng đếm ngược khoảnh khắc hạnh phúc</p>
                    <div class="mayhen_invitation_dhsuudushi9428r_countdown_numbers">
                        <div><strong data-unit="days">000</strong><span>Ngày</span></div>
                        <div><strong data-unit="hours">00</strong><span>Giờ</span></div>
                        <div><strong data-unit="minutes">00</strong><span>Phút</span></div>
                        <div><strong data-unit="seconds">00</strong><span>Giây</span></div>
                    </div>
                </div>

                <div class="mayhen_invitation_dhsuudushi9428r_heart_divider" aria-hidden="true">♥ &nbsp; ♥ &nbsp; ♥</div>

                <div class="mayhen_invitation_dhsuudushi9428r_timeline mayhen_invitation_dhsuudushi9428r_reveal">
                    <h2 class="mayhen_invitation_dhsuudushi9428r_display">Lịch Trình</h2>
                    <ol>
                        @foreach ($timelineItems as $item)
                            <li>
                                <time>{{ $item['time'] ?? '' }}</time>
                                <span>{{ $item['title'] ?? '' }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            {{-- Section 6: Sổ lưu bút --}}
            <section class="mayhen_invitation_dhsuudushi9428r_guestbook" id="mayhen_invitation_dhsuudushi9428r_guestbook">
                <div class="mayhen_invitation_dhsuudushi9428r_section_head mayhen_invitation_dhsuudushi9428r_reveal">
                    <p>Sổ Lưu Bút</p>
                    <h2 class="mayhen_invitation_dhsuudushi9428r_display">Gửi Lời Chúc</h2>
                    <span>Những lời chúc phúc ngọt ngào gửi tới đôi bạn</span>
                </div>
                <div class="mayhen_invitation_dhsuudushi9428r_guestboard mayhen_invitation_dhsuudushi9428r_reveal">
                    <div class="mayhen_invitation_dhsuudushi9428r_guestboard_empty" id="mayhen_guestboard_counter">
                        @if ($wishes->count() > 0)
                            <span>Đã có <strong>{{ $wishes->count() }}</strong> lời chúc phúc gửi tới đôi bạn</span>
                        @else
                            <span>Hãy là người đầu tiên ký tên!</span>
                        @endif
                    </div>
                </div>
                <p class="mayhen_invitation_dhsuudushi9428r_demo_note">Gửi lời chúc tốt đẹp nhất tới cô dâu & chú rể</p>
                <button class="mayhen_invitation_dhsuudushi9428r_primary_button" type="button" data-modal="guestbook">Ký tên chúc phúc</button>
                <p class="mayhen_invitation_dhsuudushi9428r_steps">1. Ký tên &nbsp;·&nbsp; 2. Viết lời chúc &nbsp;·&nbsp; 3. Gửi trao yêu thương</p>
                <div class="mayhen_invitation_dhsuudushi9428r_messages" id="mayhen_wishes_container" aria-live="polite">
                    @forelse ($wishes as $wish)
                        <article class="mayhen_invitation_dhsuudushi9428r_wish_item">
                            <div class="mayhen_invitation_dhsuudushi9428r_wish_header">
                                <strong>{{ $wish->name }}</strong>
                                <time>{{ $wish->created_at ? $wish->created_at->diffForHumans() : 'Vừa xong' }}</time>
                            </div>
                            <p>{{ $wish->message }}</p>
                        </article>
                    @empty
                        <p class="mayhen_invitation_dhsuudushi9428r_no_wishes">Chưa có lời chúc nào. Hãy là người đầu tiên gửi lời chúc mừng!</p>
                    @endforelse
                </div>
            </section>

            {{-- Section 7: Album ảnh cưới --}}
            <section class="mayhen_invitation_dhsuudushi9428r_album" aria-label="Album ảnh cưới">
                <button class="mayhen_invitation_dhsuudushi9428r_album_photo mayhen_invitation_dhsuudushi9428r_album_wide mayhen_invitation_dhsuudushi9428r_reveal" type="button" data-photo="0">
                    <img src="{{ $albumPhotos[0] ?? $defaultAlbum[0] }}" alt="Ảnh cưới trong album 1" loading="lazy">
                </button>
                <div class="mayhen_invitation_dhsuudushi9428r_album_mosaic">
                    <button class="mayhen_invitation_dhsuudushi9428r_album_photo" type="button" data-photo="1">
                        <img src="{{ $albumPhotos[1] ?? $defaultAlbum[1] }}" alt="Ảnh cưới trong album 2" loading="lazy">
                    </button>
                    <div class="mayhen_invitation_dhsuudushi9428r_album_title">
                        <span>Khoảnh Khắc</span>
                        <strong class="mayhen_invitation_dhsuudushi9428r_display">Ảnh cưới</strong>
                        <i>♥</i>
                    </div>
                    <button class="mayhen_invitation_dhsuudushi9428r_album_photo" type="button" data-photo="2">
                        <img src="{{ $albumPhotos[2] ?? $defaultAlbum[2] }}" alt="Ảnh cưới trong album 3" loading="lazy">
                    </button>
                    <button class="mayhen_invitation_dhsuudushi9428r_album_photo" type="button" data-photo="3">
                        <img src="{{ $albumPhotos[3] ?? $defaultAlbum[3] }}" alt="Ảnh cưới trong album 4" loading="lazy">
                    </button>
                    <button class="mayhen_invitation_dhsuudushi9428r_album_photo" type="button" data-photo="4">
                        <img src="{{ $albumPhotos[4] ?? $defaultAlbum[4] }}" alt="Ảnh cưới trong album 5" loading="lazy">
                    </button>
                </div>
                <div class="mayhen_invitation_dhsuudushi9428r_album_pairs">
                    @for ($i = 5; $i < 10; $i++)
                        <button class="mayhen_invitation_dhsuudushi9428r_album_photo" type="button" data-photo="{{ $i }}">
                            <img src="{{ $albumPhotos[$i] ?? ($defaultAlbum[$i] ?? $defaultAlbum[0]) }}" alt="Ảnh cưới trong album {{ $i + 1 }}" loading="lazy">
                        </button>
                    @endfor
                </div>
            </section>

            {{-- Section 8: Xác nhận tham dự (RSVP) --}}
            <section class="mayhen_invitation_dhsuudushi9428r_rsvp mayhen_invitation_dhsuudushi9428r_frame" id="mayhen_invitation_dhsuudushi9428r_rsvp">
                <div class="mayhen_invitation_dhsuudushi9428r_reveal">
                    <span class="mayhen_invitation_dhsuudushi9428r_ornament">✧</span>
                    <h2>Xác Nhận Tham Dự</h2>
                    <div class="mayhen_invitation_dhsuudushi9428r_rsvp_choices">
                        <button type="button" data-rsvp="yes"><span>♡</span>Có, tôi sẽ tham dự</button>
                        <button type="button" data-rsvp="no"><span>✉</span>Rất tiếc, chưa sắp xếp được</button>
                    </div>
                </div>
            </section>

            {{-- Section 9: Hộp mừng cưới VietQR --}}
            <section class="mayhen_invitation_dhsuudushi9428r_gift mayhen_invitation_dhsuudushi9428r_frame" id="mayhen_invitation_dhsuudushi9428r_gift">
                <div class="mayhen_invitation_dhsuudushi9428r_reveal">
                    <h2>Hộp Mừng Cưới</h2>
                    <div class="mayhen_invitation_dhsuudushi9428r_gift_scene">
                        <button class="mayhen_invitation_dhsuudushi9428r_gift_box" type="button" data-modal="gift" aria-label="Mở hộp mừng cưới">
                            <span>囍</span>
                        </button>
                    </div>
                    <p>Sự hiện diện của bạn là món quà quý giá nhất đối với chúng tôi</p>
                </div>
            </section>

            {{-- Section 10: Footer --}}
            <footer class="mayhen_invitation_dhsuudushi9428r_footer">
                <img src="{{ $footerPhotoUrl }}" alt="Ảnh cuối thiệp" loading="lazy">
                <div>
                    <p class="mayhen_invitation_dhsuudushi9428r_display">Rất hân hạnh được đón tiếp quý khách!</p>
                </div>
            </footer>
        </div>
    </main>

    {{-- Điều khiển nổi: Nhạc nền & Lên đầu trang --}}
    <div class="mayhen_invitation_dhsuudushi9428r_floating">
        <button class="mayhen_invitation_dhsuudushi9428r_music" type="button" aria-label="Bật nhạc nền" aria-pressed="false" title="Bật hoặc tắt nhạc">♫</button>
        <button class="mayhen_invitation_dhsuudushi9428r_to_top" type="button" aria-label="Lên đầu trang" title="Lên đầu trang">↑</button>
    </div>
    <audio id="mayhen_invitation_dhsuudushi9428r_audio" src="{{ $musicUrl }}" loop preload="none"></audio>

    {{-- Modal chung --}}
    <div class="mayhen_invitation_dhsuudushi9428r_modal" id="mayhen_invitation_dhsuudushi9428r_modal" role="dialog" aria-modal="true" aria-labelledby="mayhen_invitation_dhsuudushi9428r_modal_title" hidden>
        <div class="mayhen_invitation_dhsuudushi9428r_modal_backdrop" data-close="true"></div>
        <div class="mayhen_invitation_dhsuudushi9428r_modal_card">
            <button class="mayhen_invitation_dhsuudushi9428r_modal_close" type="button" data-close="true" aria-label="Đóng">×</button>
            <h2 id="mayhen_invitation_dhsuudushi9428r_modal_title"></h2>
            <div class="mayhen_invitation_dhsuudushi9428r_modal_body"></div>
        </div>
    </div>

    {{-- Lightbox phóng to ảnh cưới --}}
    <div class="mayhen_invitation_dhsuudushi9428r_lightbox" id="mayhen_invitation_dhsuudushi9428r_lightbox" role="dialog" aria-modal="true" aria-label="Xem ảnh cưới" hidden>
        <button type="button" class="mayhen_invitation_dhsuudushi9428r_lightbox_close" aria-label="Đóng">×</button>
        <button type="button" class="mayhen_invitation_dhsuudushi9428r_lightbox_prev" aria-label="Ảnh trước">‹</button>
        <img alt="Ảnh cưới phóng lớn">
        <button type="button" class="mayhen_invitation_dhsuudushi9428r_lightbox_next" aria-label="Ảnh tiếp theo">›</button>
        <span class="mayhen_invitation_dhsuudushi9428r_lightbox_count"></span>
    </div>

    {{-- Script đồng bộ thời gian thực từ Editor Studio --}}
    <script>
        window.addEventListener('message', function(event) {
            if (!event.data || event.data.type !== 'MAYHEN_SYNC_INPUT') return;
            var key = event.data.key;
            var value = event.data.value;

            var textTargets = document.querySelectorAll('[data-sync-target="' + key + '"]');
            textTargets.forEach(function(el) {
                if (key.indexOf('parents') !== -1) {
                    el.innerHTML = (value || '').replace(/\n/g, '<br>');
                } else {
                    el.textContent = value || '';
                }
            });

            if (key === 'cover_photo_url') {
                var el = document.getElementById('sync_cover_photo');
                if (el) el.src = value;
            } else if (key === 'hero_photo_url') {
                var el = document.getElementById('sync_hero_photo');
                if (el) el.src = value;
            } else if (key === 'triptych_photo_1') {
                var el = document.getElementById('sync_triptych_1');
                if (el) el.src = value;
            } else if (key === 'triptych_photo_2') {
                var el = document.getElementById('sync_triptych_2');
                if (el) el.src = value;
            } else if (key === 'triptych_photo_3') {
                var el = document.getElementById('sync_triptych_3');
                if (el) el.src = value;
            }
        });
    </script>
</body>
</html>
