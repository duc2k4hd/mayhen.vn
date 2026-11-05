@extends('client.layouts.invitation')

@php
    $slug = $slug ?? 'hanh-phuc-mang-ten-chung-minh';
    $assetBase = url('/mau-thiep/hanh-phuc-mang-ten-chung-minh/assets');
    $isEmbed = !empty($isEmbed) || request()->boolean('embed') || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Hạnh Phúc Mang Tên Chúng Mình';

    $data = $data ?? [];
    $extraData = $wedding->extra_data ?? [];

    $resolveAsset = function (string $filename, ?string $customUrl = null) use ($slug, $assetBase) {
        if (!empty($customUrl)) {
            return $customUrl;
        }
        return $assetBase . '/' . ltrim($filename, '/');
    };

    // 1. Thông tin Đôi Uyên Ương & Loại Lễ
    $ceremonyType = $data['ceremony_type'] ?? ($extraData['ceremony_type'] ?? 'Lễ Thành Hôn');
    $groomName = $data['groom_name'] ?? ($wedding->groom_name ?? ($extraData['groom_name'] ?? 'Đình Sang'));
    $brideName = $data['bride_name'] ?? ($wedding->bride_name ?? ($extraData['bride_name'] ?? 'Hoài Thương'));
    $coupleNames = $data['couple_names'] ?? ($wedding->couple_names ?? ($extraData['couple_names'] ?? ($groomName . ' & ' . $brideName)));

    $pageTitle = $isPreview 
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name') 
        : 'Thiệp mời cưới: "' . $groomName . ' & ' . $brideName . '" | ' . config('app.name');

    // 2. Song Thân Hai Họ & Địa Chỉ Tư Gia
    $groomFather = $data['groom_father'] ?? ($extraData['groom_father'] ?? ($wedding->groom_parents ?? 'Nguyễn Đắc Cường'));
    $groomMother = $data['groom_mother'] ?? ($extraData['groom_mother'] ?? 'Đặng Thị Hương');
    $groomParentsAddress = $data['groom_parents_address'] ?? ($extraData['groom_parents_address'] ?? 'TDP Nhân Sơn, Yên Nghĩa, Hà Nội');

    $brideFather = $data['bride_father'] ?? ($extraData['bride_father'] ?? ($wedding->bride_parents ?? 'Nguyễn Đức Trí'));
    $brideMother = $data['bride_mother'] ?? ($extraData['bride_mother'] ?? 'Bùi Thị Tầm');
    $brideParentsAddress = $data['bride_parents_address'] ?? ($extraData['bride_parents_address'] ?? 'Tổ 5 Quang Lãm, Phú Lương, Hà Đông, Hà Nội');

    // 3. Thời Gian & Lịch Cưới
    $weddingDateDigits = $data['wedding_date_digits'] ?? ($extraData['wedding_date_digits'] ?? '22.11.2026');
    $weddingDay = $data['wedding_day'] ?? ($extraData['wedding_day'] ?? '22');
    $weddingMonth = $data['wedding_month'] ?? ($extraData['wedding_month'] ?? '11');
    $weddingYear = $data['wedding_year'] ?? ($extraData['wedding_year'] ?? '2026');
    $weddingLunarDate = $data['wedding_lunar_date'] ?? ($extraData['wedding_lunar_date'] ?? '(Tức ngày 14 tháng 10 năm Bính Ngọ)');
    $calendarMonthYear = $data['calendar_month_year'] ?? ($extraData['calendar_month_year'] ?? "Tháng {$weddingMonth}/{$weddingYear}");

    // 4. Lễ Ăn Hỏi & Lễ Thành Hôn
    $engagementTime = $data['engagement_time'] ?? ($extraData['engagement_time'] ?? '08:00');
    $engagementDay = $data['engagement_day'] ?? ($extraData['engagement_day'] ?? '21');
    $engagementLunar = $data['engagement_lunar_date'] ?? ($extraData['engagement_lunar_date'] ?? '(Tức ngày 13 tháng 10 năm Bính Ngọ)');
    $engagementVenue = $data['engagement_venue'] ?? ($extraData['engagement_venue'] ?? 'Tư Gia Nhà Gái');

    $ceremonyTime = $data['ceremony_time'] ?? ($extraData['ceremony_time'] ?? '13:00');
    $ceremonyLunar = $data['ceremony_lunar_date'] ?? ($extraData['ceremony_lunar_date'] ?? '(Tức ngày 14 tháng 10 năm Bính Ngọ)');
    $ceremonyVenue = $data['ceremony_venue'] ?? ($extraData['ceremony_venue'] ?? 'Tư Gia Nhà Trai');

    // 5. Tiệc Cưới Hai Bên & Địa Điểm
    $bridePartyTime = $data['bride_party_time'] ?? ($extraData['bride_party_time'] ?? '16:30');
    $bridePartyVenue = $data['bride_party_venue'] ?? ($extraData['bride_party_venue'] ?? 'Nhà Văn Hóa Tổ 5 Quang Lãm');
    $bridePartyAddress = $data['bride_party_address'] ?? ($extraData['bride_party_address'] ?? 'Phường Phú Lương, Quận Hà Đông, TP. Hà Nội');
    $bridePartyMapUrl = $data['bride_party_map_url'] ?? ($extraData['bride_party_map_url'] ?? '#map-nha-gai');

    $partyTime = $data['party_time'] ?? ($extraData['party_time'] ?? '17:30');
    $partyVenue = $data['party_venue'] ?? ($extraData['party_venue'] ?? 'Nhà Văn Hóa Tổ 6 Nhân Đạo');
    $eventPartyAddress = $data['event_party_address'] ?? ($extraData['event_party_address'] ?? 'TDP Nhân Sơn, Phường Yên Nghĩa, TP. Hà Nội');
    $partyMapUrl = $data['party_map_url'] ?? ($extraData['party_map_url'] ?? 'https://maps.google.com/?q=' . urlencode($partyVenue . ' ' . $eventPartyAddress));
    $partyMapEmbedUrl = 'https://maps.google.com/maps?q=' . urlencode($partyVenue . ', ' . $eventPartyAddress) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';

    // 6. Lời Ngỏ, Câu Chuyện & Bio
    $loveQuote = $data['love_quote'] ?? ($extraData['love_quote'] ?? '“Hôn nhân là chuyện cả đời, Yêu người vừa ý, cưới người mình thương”');
    $storyTitle = $data['story_title'] ?? ($extraData['story_title'] ?? 'Chuyện Tình Yêu');
    $storySubtitle = $data['story_subtitle'] ?? ($extraData['story_subtitle'] ?? 'Hạnh Phúc Mang Tên Chúng Mình');
    $storyContent = $data['story_content'] ?? ($extraData['story_content'] ?? 'Biết ơn vì đã tìm thấy nhau giữa thế giới bao la. Cùng nhau chúng ta đi qua những năm tháng tuổi trẻ, và hôm nay bước vào cánh cửa hôn nhân rạng rỡ và hạnh phúc trọn vẹn nhất.');

    $groomBio = $data['groom_bio'] ?? ($extraData['groom_bio'] ?? 'Biết ơn Em về tất cả. Nguyện bên Em qua muôn nẻo đường phía trước, mình đồng hành cùng nhau đến mãi mãi về sau.');
    $brideBio = $data['bride_bio'] ?? ($extraData['bride_bio'] ?? 'Hành trình trưởng thành của em thật đẹp khi có sự xuất hiện của Anh. Cùng nhau chúng ta khám phá thế giới kỳ diệu này anh nhé.');

    // 7. Lịch Trình Buổi Tiệc (Timeline mốc chuẩn hóa)
    $defaultTimeline = [
        ['time' => '17:30', 'title' => 'Đón khách'],
        ['time' => '18:00', 'title' => 'Cử hành hôn lễ'],
        ['time' => '18:30', 'title' => 'Khai tiệc'],
        ['time' => '20:00', 'title' => 'Giao lưu & chụp ảnh'],
    ];
    $rawTimeline = $extraData['timeline'] ?? ($wedding->extra_data['timeline'] ?? ($data['timeline'] ?? []));
    $timelineList = is_array($rawTimeline) && count($rawTimeline) > 0 ? array_values($rawTimeline) : $defaultTimeline;
    foreach ($timelineList as $tIdx => $tVal) {
        $timelineList[$tIdx]['time'] = $tVal['time'] ?? ($data["timeline.{$tIdx}.time"] ?? ($defaultTimeline[$tIdx]['time'] ?? ''));
        $timelineList[$tIdx]['title'] = $tVal['title'] ?? ($tVal['desc'] ?? ($data["timeline.{$tIdx}.title"] ?? ($defaultTimeline[$tIdx]['title'] ?? '')));
    }

    // 8. Quy Định Trang Phục (Dresscode)
    $rawDresscodeColors = $extraData['dresscode_colors'] ?? ($data['dresscode_colors'] ?? '#a8b8a3,#233628,#c5a059,#f8f6f0');
    $dresscodeColors = is_array($rawDresscodeColors) ? $rawDresscodeColors : array_map('trim', explode(',', $rawDresscodeColors));
    $dresscodeColorNames = [
        '#a8b8a3' => 'Xanh Sage',
        '#233628' => 'Xanh Rừng Đậm',
        '#c5a059' => 'Vàng Cổ Điển',
        '#f8f6f0' => 'Kem Ấm Áp',
        '#ffffff' => 'Trắng Tinh Khôi',
        '#1f1d1a' => 'Charcoal',
    ];
    $dresscodeNote = $extraData['dresscode_note'] ?? ($data['dresscode_note'] ?? 'Khuyến khích trang phục màu xanh lá nhã nhặn hoặc tông màu ấm áp để buổi tiệc thêm phần đồng điệu và trang trọng.');

    // 9. Album 10 Ảnh Cưới Chuẩn Hóa
    $defaultGallery = [
        'gallery-1.webp', 'gallery-2.webp', 'gallery-3.webp', 'gallery-4.webp', 'gallery-5.webp',
        'gallery-6.webp', 'gallery-7.webp', 'gallery-8.webp', 'gallery-9.webp', 'gallery-10.webp',
    ];
    $rawGallery = $extraData['gallery'] ?? ($data['gallery'] ?? []);
    $gallery = [];
    for ($i = 0; $i < 10; $i++) {
        $img = !empty($rawGallery[$i]) ? $rawGallery[$i] : ($data["gallery.{$i}"] ?? ($defaultGallery[$i] ?? 'gallery-1.webp'));
        $gallery[] = $resolveAsset($img, $defaultGallery[$i] ?? 'gallery-1.webp');
    }

    // 10. Mừng Cưới & Ngân Hàng
    $groomBankName = $data['groom_bank_name'] ?? ($extraData['groom_bank_name'] ?? 'Vietcombank');
    $groomBankAccount = $data['groom_bank_account'] ?? ($extraData['groom_bank_account'] ?? '0123456789');
    $groomAccountName = $data['groom_account_name'] ?? ($extraData['groom_account_name'] ?? ($data['groom_bank_holder'] ?? ($extraData['groom_bank_holder'] ?? 'NGUYEN DINH SANG')));

    $brideBankName = $data['bride_bank_name'] ?? ($extraData['bride_bank_name'] ?? 'Techcombank');
    $brideBankAccount = $data['bride_bank_account'] ?? ($extraData['bride_bank_account'] ?? '9876543210');
    $brideAccountName = $data['bride_account_name'] ?? ($extraData['bride_account_name'] ?? ($data['bride_bank_holder'] ?? ($extraData['bride_bank_holder'] ?? 'NGUYEN HOAI THUONG')));

    // 11. Ending Lời Tri Ân Kết Thiệp
    $endingTitle = $extraData['ending_title'] ?? ($data['ending_title'] ?? 'Thank You!');
    $endingText = $extraData['ending_text'] ?? ($data['ending_text'] ?? 'Sự hiện diện và lời chúc phúc của bạn là niềm vinh hạnh lớn lao đối với gia đình chúng mình.');

    $guestName = request()->query('guest', request()->query('name', $invitation->guest_name ?? 'Quý Khách'));

    $cssVersion = file_exists(resource_path('views/client/invitations/hanh-phuc-mang-ten-chung-minh/styles.css'))
        ? filemtime(resource_path('views/client/invitations/hanh-phuc-mang-ten-chung-minh/styles.css'))
        : time();
    $jsVersion = file_exists(resource_path('views/client/invitations/hanh-phuc-mang-ten-chung-minh/script.js'))
        ? filemtime(resource_path('views/client/invitations/hanh-phuc-mang-ten-chung-minh/script.js'))
        : time();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ route('templates.asset', ['slug' => 'hanh-phuc-mang-ten-chung-minh', 'path' => 'styles.css']) }}?v={{ $cssVersion }}">
@endpush

@section('content')
<div class="mayhen_invitation_ladi_wraper mayhen_invitation_app {{ $isEmbed ? 'mayhen_invitation_is_embed' : '' }}" data-is-embed="{{ $isEmbed ? 'true' : 'false' }}">

    {{-- ==================== NÚT ĐĨA THAN NHẠC CỐ ĐỊNH GÓC TRÊN BÊN TRÁI ==================== --}}
    <div class="mayhen_invitation_music_dock" data-ignore-autoscroll="true">
        <button type="button" class="mayhen_invitation_music_btn" aria-label="Bật/Tắt nhạc nền" title="Bật/Tắt âm nhạc">
            <svg class="mayhen_invitation_music_icon" viewBox="0 0 24 24"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
        </button>
    </div>

    {{-- ==================== MÀN MỞ THIỆP PHONG BÌ / CÁNH CỬA SONG HỶ ==================== --}}
    <div class="mayhen_invitation_opening {{ $isEmbed ? 'mayhen_invitation_opening--opened' : '' }}"
         style="{{ $isEmbed ? 'display: none !important;' : '' }}"
         aria-label="Màn hình mở thiệp">
        <div class="mayhen_invitation_door mayhen_invitation_door--left"></div>
        <div class="mayhen_invitation_door mayhen_invitation_door--right"></div>
        <div class="mayhen_invitation_opening_center">
            <span class="mayhen_invitation_opening_subtitle">Hạnh Phúc Mang Tên Chúng Mình</span>
            <h2 class="mayhen_invitation_opening_names" data-sync-target="couple_names">{{ $coupleNames }}</h2>
            <button type="button" class="mayhen_invitation_open_btn" aria-label="Chạm để mở thiệp">
                <span class="mayhen_invitation_wax_seal">
                    <img src="{{ $resolveAsset('wax-seal.svg') }}" alt="Tem sáp niêm phong Mây Hẹn">
                </span>
                <span class="mayhen_invitation_open_hint">Chạm Để Mở Thiệp</span>
            </button>
        </div>
    </div>

    {{-- ==================== HIỆU ỨNG LÁ SAGE RƠI NHẸ 60FPS ==================== --}}
    <div class="mayhen_invitation_petals" aria-hidden="true">
        <span class="mayhen_invitation_petal"></span>
        <span class="mayhen_invitation_petal"></span>
        <span class="mayhen_invitation_petal"></span>
        <span class="mayhen_invitation_petal"></span>
        <span class="mayhen_invitation_petal"></span>
        <span class="mayhen_invitation_petal"></span>
    </div>

    {{-- ==================== SECTION 1: TRANG BÌA CHÍNH (HERO COVER) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_hero mayhen_invitation_hero--sage"
             data-mayhen-section="hero,couple"
             style="background-image: url('{{ $resolveAsset('bg-paper-sage.webp') }}');">
        <div class="mayhen_invitation_hero_card mayhen_invitation_reveal mayhen_invitation_visible">
            <span class="mayhen_invitation_corner mayhen_invitation_corner--tl"><img src="{{ $resolveAsset('floral-corner.svg') }}" alt=""></span>
            <span class="mayhen_invitation_corner mayhen_invitation_corner--tr"><img src="{{ $resolveAsset('floral-corner.svg') }}" alt=""></span>
            <span class="mayhen_invitation_corner mayhen_invitation_corner--bl"><img src="{{ $resolveAsset('floral-corner.svg') }}" alt=""></span>
            <span class="mayhen_invitation_corner mayhen_invitation_corner--br"><img src="{{ $resolveAsset('floral-corner.svg') }}" alt=""></span>

            <p class="mayhen_invitation_hero_badge" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>
            <h1 class="mayhen_invitation_hero_couple" data-sync-target="couple_names">{{ $coupleNames }}</h1>

            <div class="mayhen_invitation_crest">
                <img src="{{ $resolveAsset('botanical-crest.svg') }}" alt="Hoa văn Mây Hẹn">
            </div>

            <div class="mayhen_invitation_hero_frame mayhen_invitation_lightbox_trigger">
                <img src="{{ $resolveAsset('hero-couple.webp', $wedding->cover_photo_url ?? ($data['hero_photo_url'] ?? null)) }}"
                     alt="{{ $coupleNames }}"
                     data-sync-target="hero_photo_url">
            </div>

            <div class="mayhen_invitation_hero_date_pill">
                <span data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</span>
            </div>
            <p class="mayhen_invitation_lunar_note" style="margin-top: 0.35rem; font-size: 0.8rem;" data-sync-target="wedding_lunar_date">{{ $weddingLunarDate }}</p>

            <div class="mayhen_invitation_scroll_cue" data-scroll-to=".mayhen_invitation_story">
                <span>Kéo Xuống Để Xem Chi Tiết Thiệp Cưới</span>
                <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
            </div>
        </div>
    </section>

    {{-- ==================== SECTION 2: LỜI NGỎ & CHUYỆN TÌNH YÊU (LOVE STORY & QUOTE) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_story"
             data-mayhen-section="story"
             style="background-image: url('{{ $resolveAsset('bg-paper-sage.webp') }}');">
        <div class="mayhen_invitation_section_header mayhen_invitation_reveal">
            <span class="mayhen_invitation_eyebrow" data-sync-target="story_subtitle">{{ $storySubtitle }}</span>
            <h2 class="mayhen_invitation_script_title" data-sync-target="story_title">{{ $storyTitle }}</h2>
            <div class="mayhen_invitation_divider">
                <img src="{{ $resolveAsset('divider-ornament.svg') }}" alt="">
            </div>
        </div>

        <blockquote class="mayhen_invitation_quote_box mayhen_invitation_reveal" data-sync-target="couple_quote">
            {{ $loveQuote }}
        </blockquote>

        <div class="mayhen_invitation_triptych mayhen_invitation_reveal">
            <div class="mayhen_invitation_triptych_item mayhen_invitation_lightbox_trigger">
                <img src="{{ $resolveAsset('story-1.webp', $data['story_photo_1'] ?? null) }}" alt="Ảnh kỷ niệm 1" data-sync-target="story_photo_1">
            </div>
            <div class="mayhen_invitation_triptych_item mayhen_invitation_triptych_item--center mayhen_invitation_lightbox_trigger">
                <img src="{{ $resolveAsset('story-2.webp', $extraData['save_date_photo_url'] ?? ($data['story_photo_2'] ?? null)) }}" alt="Ảnh kỷ niệm 2" data-sync-target="save_date_photo_url">
            </div>
            <div class="mayhen_invitation_triptych_item mayhen_invitation_lightbox_trigger">
                <img src="{{ $resolveAsset('story-3.webp', $data['story_photo_3'] ?? null) }}" alt="Ảnh kỷ niệm 3" data-sync-target="story_photo_3">
            </div>
        </div>

        <p class="mayhen_invitation_story_content mayhen_invitation_reveal" data-sync-target="story_content" style="max-width: 420px; margin: 1.25rem auto 0; text-align: center; font-size: 0.88rem; line-height: 1.7; color: var(--mayhen-text-muted);">
            {{ $storyContent }}
        </p>
    </section>

    {{-- ==================== SECTION 3: SONG THÂN & ĐÔI UYÊN ƯƠNG (FAMILY & COUPLE) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_section--sage mayhen_invitation_family_section mayhen_invitation_parents"
             data-mayhen-section="parents,groom_family,bride_family">
        <div class="mayhen_invitation_section_header mayhen_invitation_reveal">
            <span class="mayhen_invitation_eyebrow">Song Thân Hai Họ</span>
            <h2 class="mayhen_invitation_title">Trân Trọng Thông Báo Hôn Lễ</h2>
            <div class="mayhen_invitation_divider">
                <img src="{{ $resolveAsset('divider-ornament.svg') }}" alt="">
            </div>
        </div>

        <div class="mayhen_invitation_family_grid">
            <div class="mayhen_invitation_family_card mayhen_invitation_reveal mayhen_invitation_reveal--left">
                <h3 class="mayhen_invitation_family_side">Nhà Trai</h3>
                <p class="mayhen_invitation_family_parent">Bố: <strong data-sync-target="groom_father">{{ $groomFather }}</strong></p>
                <p class="mayhen_invitation_family_parent">Mẹ: <strong data-sync-target="groom_mother">{{ $groomMother }}</strong></p>
                <p class="mayhen_invitation_family_parent" style="font-size: 0.78rem; color: var(--mayhen-text-muted); margin-top: 0.35rem;" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
            </div>
            <div class="mayhen_invitation_family_card mayhen_invitation_reveal mayhen_invitation_reveal--right">
                <h3 class="mayhen_invitation_family_side">Nhà Gái</h3>
                <p class="mayhen_invitation_family_parent">Bố: <strong data-sync-target="bride_father">{{ $brideFather }}</strong></p>
                <p class="mayhen_invitation_family_parent">Mẹ: <strong data-sync-target="bride_mother">{{ $brideMother }}</strong></p>
                <p class="mayhen_invitation_family_parent" style="font-size: 0.78rem; color: var(--mayhen-text-muted); margin-top: 0.35rem;" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
            </div>
        </div>

        <div class="mayhen_invitation_announce_box mayhen_invitation_reveal">
            <div class="mayhen_invitation_announce_names">
                <span class="mayhen_invitation_announce_name" data-sync-target="groom_name">{{ $groomName }}</span>
                <span class="mayhen_invitation_announce_amp">&amp;</span>
                <span class="mayhen_invitation_announce_name" data-sync-target="bride_name">{{ $brideName }}</span>
            </div>
            <div class="mayhen_invitation_banner_photo mayhen_invitation_lightbox_trigger">
                <img src="{{ $resolveAsset('banner-couple.webp', $data['couple_photo_url'] ?? null) }}"
                     alt="{{ $coupleNames }}"
                     data-sync-target="couple_photo_url">
            </div>
        </div>

        <div class="mayhen_invitation_couple_list">
            <div class="mayhen_invitation_couple_row mayhen_invitation_reveal mayhen_invitation_reveal--left">
                <div class="mayhen_invitation_couple_photo mayhen_invitation_lightbox_trigger">
                    <img src="{{ $resolveAsset('groom-portrait.webp', $wedding->groom_photo_url ?? ($data['groom_photo_url'] ?? null)) }}"
                         alt="{{ $groomName }}"
                         data-sync-target="groom_photo_url">
                </div>
                <div class="mayhen_invitation_couple_info">
                    <span class="mayhen_invitation_couple_role">Chú Rể</span>
                    <h3 class="mayhen_invitation_couple_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                    <p class="mayhen_invitation_couple_bio" data-sync-target="groom_bio">{{ $groomBio }}</p>
                </div>
            </div>

            <div class="mayhen_invitation_couple_row mayhen_invitation_couple_row--reverse mayhen_invitation_reveal mayhen_invitation_reveal--right">
                <div class="mayhen_invitation_couple_info">
                    <span class="mayhen_invitation_couple_role">Cô Dâu</span>
                    <h3 class="mayhen_invitation_couple_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                    <p class="mayhen_invitation_couple_bio" data-sync-target="bride_bio">{{ $brideBio }}</p>
                </div>
                <div class="mayhen_invitation_couple_photo mayhen_invitation_lightbox_trigger">
                    <img src="{{ $resolveAsset('bride-portrait.webp', $wedding->bride_photo_url ?? ($data['bride_photo_url'] ?? null)) }}"
                         alt="{{ $brideName }}"
                         data-sync-target="bride_photo_url">
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== SECTION 4: ĐẾM NGƯỢC & LỊCH THÁNG (COUNTDOWN & CALENDAR) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_section--sage mayhen_invitation_calendar mayhen_invitation_countdown_calendar"
             data-mayhen-section="countdown_calendar">
        <div class="mayhen_invitation_section_header mayhen_invitation_reveal">
            <span class="mayhen_invitation_eyebrow">Ngày Chung Đôi</span>
            <h2 class="mayhen_invitation_title">Save The Date</h2>
            <div class="mayhen_invitation_divider">
                <img src="{{ $resolveAsset('divider-ornament.svg') }}" alt="">
            </div>
        </div>

        <div class="mayhen_invitation_calendar_card mayhen_invitation_reveal">
            <div class="mayhen_invitation_calendar_header">
                <h3 class="mayhen_invitation_calendar_month" data-sync-target="calendar_month_year">{{ $calendarMonthYear }}</h3>
            </div>
            <div class="mayhen_invitation_calendar_weekdays">
                <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
            </div>
            <div class="mayhen_invitation_calendar_grid"
                 data-sync-target="wedding_calendar"
                 data-wedding-year="{{ $weddingYear }}"
                 data-wedding-month="{{ $weddingMonth }}"
                 data-wedding-day="{{ $weddingDay }}">
                {{-- Được tự động điền và đồng bộ qua renderMayhenWeddingCalendar() --}}
            </div>
        </div>

        <div class="mayhen_invitation_countdown mayhen_invitation_reveal"
             data-wedding-target="{{ $weddingYear }}-{{ str_pad($weddingMonth, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($weddingDay, 2, '0', STR_PAD_LEFT) }}T13:00:00">
            <div class="mayhen_invitation_countdown_box">
                <span class="mayhen_invitation_countdown_num" data-countdown-unit="days">00</span>
                <span class="mayhen_invitation_countdown_label">Ngày</span>
            </div>
            <div class="mayhen_invitation_countdown_box">
                <span class="mayhen_invitation_countdown_num" data-countdown-unit="hours">00</span>
                <span class="mayhen_invitation_countdown_label">Giờ</span>
            </div>
            <div class="mayhen_invitation_countdown_box">
                <span class="mayhen_invitation_countdown_num" data-countdown-unit="minutes">00</span>
                <span class="mayhen_invitation_countdown_label">Phút</span>
            </div>
            <div class="mayhen_invitation_countdown_box">
                <span class="mayhen_invitation_countdown_num" data-countdown-unit="seconds">00</span>
                <span class="mayhen_invitation_countdown_label">Giây</span>
            </div>
        </div>
    </section>

    {{-- ==================== SECTION 5: THÔNG TIN SỰ KIỆN, TIỆC CƯỚI & BẢN ĐỒ (EVENTS & MAPS) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_events"
             data-mayhen-section="events,wedding_event"
             style="background-image: url('{{ $resolveAsset('bg-paper-sage.webp') }}');">
        <div class="mayhen_invitation_section_header mayhen_invitation_reveal">
            <span class="mayhen_invitation_eyebrow">Save Our Joy</span>
            <h2 class="mayhen_invitation_script_title">Thư Mời Tiệc Cưới</h2>
            <div class="mayhen_invitation_divider">
                <img src="{{ $resolveAsset('divider-ornament.svg') }}" alt="">
            </div>
        </div>

        <div class="mayhen_invitation_guest_banner mayhen_invitation_reveal">
            <p class="mayhen_invitation_guest_title">Trân Trọng Kính Mời</p>
            <h3 class="mayhen_invitation_guest_name" data-sync-target="guest_name">{{ $guestName }}</h3>
            <p class="mayhen_invitation_guest_desc">Tới dự buổi tiệc chung vui cùng gia đình chúng tôi</p>
        </div>

        <div class="mayhen_invitation_party_grid">
            {{-- Tiệc Cưới Nhà Gái --}}
            <article class="mayhen_invitation_party_card mayhen_invitation_reveal">
                <h3 class="mayhen_invitation_ceremony_name">Tiệc Cưới Nhà Gái</h3>
                <p class="mayhen_invitation_ceremony_time" data-sync-target="bride_party_time">{{ $bridePartyTime }}</p>
                <div class="mayhen_invitation_date_split">
                    <div class="mayhen_invitation_date_side">Tháng <span data-sync-target="wedding_month">{{ $weddingMonth }}</span></div>
                    <div class="mayhen_invitation_date_big" data-sync-target="engagement_day">{{ $engagementDay }}</div>
                    <div class="mayhen_invitation_date_side">Năm <span data-sync-target="wedding_year">{{ $weddingYear }}</span></div>
                </div>
                <p class="mayhen_invitation_lunar_note" data-sync-target="engagement_lunar_date">{{ $engagementLunar }}</p>
                <h4 class="mayhen_invitation_venue_title" data-sync-target="bride_party_venue">{{ $bridePartyVenue }}</h4>
                <p class="mayhen_invitation_party_address" data-sync-target="bride_party_address">{{ $bridePartyAddress }}</p>
                <a href="{{ $bridePartyMapUrl }}" target="_blank" rel="noopener" class="mayhen_invitation_map_btn" data-sync-target="bride_party_map_url">
                    <span>Xem Bản Đồ Chỉ Đường</span>
                </a>
            </article>

            {{-- Tiệc Cưới Nhà Trai --}}
            <article class="mayhen_invitation_party_card mayhen_invitation_reveal">
                <h3 class="mayhen_invitation_ceremony_name">Tiệc Cưới Nhà Trai</h3>
                <p class="mayhen_invitation_ceremony_time" data-sync-target="party_time">{{ $partyTime }}</p>
                <div class="mayhen_invitation_date_split">
                    <div class="mayhen_invitation_date_side">Tháng <span data-sync-target="wedding_month">{{ $weddingMonth }}</span></div>
                    <div class="mayhen_invitation_date_big" data-sync-target="wedding_day">{{ $weddingDay }}</div>
                    <div class="mayhen_invitation_date_side">Năm <span data-sync-target="wedding_year">{{ $weddingYear }}</span></div>
                </div>
                <p class="mayhen_invitation_lunar_note" data-sync-target="wedding_lunar_date">{{ $weddingLunarDate }}</p>
                <h4 class="mayhen_invitation_venue_title" data-sync-target="party_venue">{{ $partyVenue }}</h4>
                <p class="mayhen_invitation_party_address" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>
                <a href="{{ $partyMapUrl }}" target="_blank" rel="noopener" class="mayhen_invitation_map_btn" data-sync-target="party_map_url">
                    <span>Xem Bản Đồ Chỉ Đường</span>
                </a>
            </article>
        </div>

        <div class="mayhen_invitation_ceremony_list" style="margin-top: 1.5rem;">
            {{-- Lễ Ăn Hỏi --}}
            <article class="mayhen_invitation_ceremony_card mayhen_invitation_reveal">
                <div class="mayhen_invitation_ceremony_icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </div>
                <h3 class="mayhen_invitation_ceremony_name">Lễ Ăn Hỏi</h3>
                <p class="mayhen_invitation_ceremony_time" data-sync-target="engagement_time">{{ $engagementTime }}</p>
                <p class="mayhen_invitation_lunar_note" data-sync-target="engagement_lunar_date">{{ $engagementLunar }}</p>
                <div class="mayhen_invitation_venue_pill">
                    <span class="mayhen_invitation_venue_label">Hôn lễ cử hành tại</span>
                    <h4 class="mayhen_invitation_venue_title" data-sync-target="engagement_venue">{{ $engagementVenue }}</h4>
                </div>
            </article>

            {{-- Lễ Thành Hôn --}}
            <article class="mayhen_invitation_ceremony_card mayhen_invitation_reveal">
                <div class="mayhen_invitation_ceremony_icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                </div>
                <h3 class="mayhen_invitation_ceremony_name">Lễ Thành Hôn</h3>
                <p class="mayhen_invitation_ceremony_time" data-sync-target="ceremony_time">{{ $ceremonyTime }}</p>
                <p class="mayhen_invitation_lunar_note" data-sync-target="wedding_lunar_date">{{ $weddingLunarDate }}</p>
                <div class="mayhen_invitation_venue_pill">
                    <span class="mayhen_invitation_venue_label">Hôn lễ cử hành tại</span>
                    <h4 class="mayhen_invitation_venue_title" data-sync-target="ceremony_venue">{{ $ceremonyVenue }}</h4>
                </div>
            </article>
        </div>

        {{-- Khung Bản Đồ Google Maps Tương Tác --}}
        <div class="mayhen_invitation_map_box mayhen_invitation_reveal">
            <iframe class="mayhen_invitation_map_iframe"
                    src="{{ $partyMapEmbedUrl }}"
                    loading="lazy"
                    allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Bản đồ chỉ đường tiệc cưới">
            </iframe>
            <div class="mayhen_invitation_map_meta">
                <strong data-sync-target="party_venue">{{ $partyVenue }}</strong>
                <p class="mayhen_invitation_map_address_text" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>
                <a href="{{ $partyMapUrl }}" target="_blank" rel="noopener" class="mayhen_invitation_map_btn" style="align-self: center; margin-top: 0.35rem;" data-sync-target="party_map_url">
                    <span>Mở Chỉ Đường Google Maps</span>
                </a>
            </div>
        </div>
    </section>

    {{-- ==================== SECTION 6: LỊCH TRÌNH TIỆC CƯỚI (TIMELINE) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_section--sage mayhen_invitation_timeline"
             data-mayhen-section="timeline">
        <div class="mayhen_invitation_section_header mayhen_invitation_reveal">
            <span class="mayhen_invitation_eyebrow">Timeline</span>
            <h2 class="mayhen_invitation_title">Lịch Trình Buổi Tiệc</h2>
            <div class="mayhen_invitation_divider">
                <img src="{{ $resolveAsset('divider-ornament.svg') }}" alt="">
            </div>
        </div>

        <div class="mayhen_invitation_timeline_card mayhen_invitation_reveal">
            <div class="mayhen_invitation_timeline_list">
                @php
                    $timelineIcons = [
                        0 => '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/></svg>',
                        1 => '<svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
                        2 => '<svg viewBox="0 0 24 24"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>',
                        3 => '<svg viewBox="0 0 24 24"><path d="M21 6h-3.17L16 4H8L6.17 6H3a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2zm-9 13a5 5 0 1 1 5-5 5 5 0 0 1-5 5z"/></svg>',
                    ];
                @endphp
                @foreach ($timelineList as $tIdx => $tItem)
                    <div class="mayhen_invitation_timeline_item">
                        <div class="mayhen_invitation_timeline_time" data-sync-target="timeline.{{ $tIdx }}.time">{{ $tItem['time'] }}</div>
                        <div class="mayhen_invitation_timeline_col">
                            <div class="mayhen_invitation_timeline_dot">
                                {!! $timelineIcons[$tIdx % 4] !!}
                            </div>
                            @if (!$loop->last)
                                <div class="mayhen_invitation_timeline_line"></div>
                            @endif
                        </div>
                        <div class="mayhen_invitation_timeline_text" data-sync-target="timeline.{{ $tIdx }}.title">{{ $tItem['title'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==================== SECTION 7: QUY ĐỊNH TRANG PHỤC (DRESSCODE) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_dresscode"
             data-mayhen-section="dresscode"
             style="background-image: url('{{ $resolveAsset('bg-paper-sage.webp') }}');">
        <div class="mayhen_invitation_section_header mayhen_invitation_reveal">
            <span class="mayhen_invitation_eyebrow">Dresscode</span>
            <h2 class="mayhen_invitation_title">Gợi Ý Trang Phục</h2>
            <div class="mayhen_invitation_divider">
                <img src="{{ $resolveAsset('divider-ornament.svg') }}" alt="">
            </div>
        </div>

        <div class="mayhen_invitation_dresscode_card mayhen_invitation_reveal">
            <div class="mayhen_invitation_dresscode_icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" stroke-width="1.8">
                    <path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/>
                </svg>
            </div>

            <div class="mayhen_invitation_dresscode_swatches">
                @foreach ($dresscodeColors as $cHex)
                    @php
                        $hex = trim($cHex);
                        $cName = $dresscodeColorNames[strtolower($hex)] ?? ($dresscodeColorNames[$hex] ?? $hex);
                    @endphp
                    <div class="mayhen_invitation_dresscode_item">
                        <div class="mayhen_invitation_dresscode_circle" style="background-color: {{ $hex }};" title="{{ $cName }}"></div>
                        <span class="mayhen_invitation_dresscode_label">{{ $cName }}</span>
                    </div>
                @endforeach
            </div>

            <p class="mayhen_invitation_dresscode_note" data-sync-target="dresscode_note">
                {{ $dresscodeNote }}
            </p>
        </div>
    </section>

    {{-- ==================== SECTION 8: KHOẢNH KHẮC HẠNH PHÚC (ALBUM 10 PHOTOS) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_section--sage mayhen_invitation_gallery"
             data-mayhen-section="album,gallery">
        <div class="mayhen_invitation_section_header mayhen_invitation_reveal">
            <span class="mayhen_invitation_eyebrow">Khoảnh Khắc Ngọt Ngào</span>
            <h2 class="mayhen_invitation_script_title">Album Hình Cưới</h2>
            <div class="mayhen_invitation_divider">
                <img src="{{ $resolveAsset('divider-ornament.svg') }}" alt="">
            </div>
        </div>

        <div class="mayhen_invitation_gallery_grid">
            @for ($i = 0; $i < 10; $i++)
                <div class="mayhen_invitation_gallery_item mayhen_invitation_reveal mayhen_invitation_lightbox_trigger" data-gallery-index="{{ $i }}">
                    <img src="{{ $gallery[$i] }}"
                         alt="Ảnh cưới {{ $i + 1 }}"
                         data-sync-target="gallery.{{ $i }}">
                </div>
            @endfor
        </div>

        <button type="button" class="mayhen_invitation_gallery_more_btn" data-open-gallery="0">
            <span>Xem Trình Chiếu Toàn Màn Hình</span>
        </button>
    </section>

    {{-- ==================== SECTION 9: HỘP MỪNG CƯỚI (WEDDING GIFT & VIETQR) ==================== --}}
    <section class="mayhen_invitation_section mayhen_invitation_gift"
             data-mayhen-section="gift"
             style="background-image: url('{{ $resolveAsset('bg-paper-sage.webp') }}');">
        <div class="mayhen_invitation_section_header mayhen_invitation_reveal">
            <span class="mayhen_invitation_eyebrow">Gửi Trao Yêu Thương</span>
            <h2 class="mayhen_invitation_title">Hộp Mừng Cưới</h2>
            <div class="mayhen_invitation_divider">
                <img src="{{ $resolveAsset('divider-ornament.svg') }}" alt="">
            </div>
            <p style="font-size:0.86rem;color:var(--mayhen-text-muted);margin-top:0.55rem;">
                Thật vui vì được gặp và đón tiếp quý khách trong dịp đặc biệt của gia đình chúng tôi.
            </p>
        </div>

        <div class="mayhen_invitation_gift_grid">
            {{-- Mừng cưới Chú Rể --}}
            <div class="mayhen_invitation_gift_card mayhen_invitation_reveal mayhen_invitation_reveal--left">
                <h3 class="mayhen_invitation_gift_title">Mừng Cưới Đến<br>Chú Rể</h3>
                <div class="mayhen_invitation_gift_qr mayhen_invitation_lightbox_trigger">
                    <img src="{{ $resolveAsset('qr-groom.webp', $extraData['qr_groom_image'] ?? ($data['qr_groom_image'] ?? null)) }}"
                         alt="QR Chú Rể"
                         data-sync-target="qr_groom_image">
                </div>
                <div class="mayhen_invitation_gift_meta">
                    <span data-sync-target="groom_bank_name">{{ $groomBankName }}</span>
                    <span class="mayhen_invitation_gift_bank_no" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</span>
                    <span data-sync-target="groom_account_name">{{ $groomAccountName }}</span>
                </div>
                <div class="mayhen_invitation_gift_actions">
                    <button type="button" class="mayhen_invitation_chip_btn" data-copy-target="groom_bank_account">Sao chép STK</button>
                    <a href="{{ $resolveAsset('qr-groom.webp', $extraData['qr_groom_image'] ?? ($data['qr_groom_image'] ?? null)) }}" download="QR-Chu-Re.webp" class="mayhen_invitation_chip_btn">Tải ảnh QR</a>
                </div>
            </div>

            {{-- Mừng cưới Cô Dâu --}}
            <div class="mayhen_invitation_gift_card mayhen_invitation_reveal mayhen_invitation_reveal--right">
                <h3 class="mayhen_invitation_gift_title">Mừng Cưới Đến<br>Cô Dâu</h3>
                <div class="mayhen_invitation_gift_qr mayhen_invitation_lightbox_trigger">
                    <img src="{{ $resolveAsset('qr-bride.webp', $extraData['qr_bride_image'] ?? ($data['qr_bride_image'] ?? null)) }}"
                         alt="QR Cô Dâu"
                         data-sync-target="qr_bride_image">
                </div>
                <div class="mayhen_invitation_gift_meta">
                    <span data-sync-target="bride_bank_name">{{ $brideBankName }}</span>
                    <span class="mayhen_invitation_gift_bank_no" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</span>
                    <span data-sync-target="bride_account_name">{{ $brideAccountName }}</span>
                </div>
                <div class="mayhen_invitation_gift_actions">
                    <button type="button" class="mayhen_invitation_chip_btn" data-copy-target="bride_bank_account">Sao chép STK</button>
                    <a href="{{ $resolveAsset('qr-bride.webp', $extraData['qr_bride_image'] ?? ($data['qr_bride_image'] ?? null)) }}" download="QR-Co-Dau.webp" class="mayhen_invitation_chip_btn">Tải ảnh QR</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== SECTION 10: TƯƠNG TÁC, SỔ KÝ TÊN, RSVP & LỜI CẢM ƠN ==================== --}}
    {{-- 10.1 Sổ Ký Tên Điện Tử (TUYỆT ĐỐI KHÔNG ẢNH NGƯỜI) --}}
    <section class="mayhen_invitation_section mayhen_invitation_section--sage mayhen_invitation_signature"
             data-mayhen-section="signature">
        <div class="mayhen_invitation_section_header mayhen_invitation_reveal">
            <span class="mayhen_invitation_eyebrow">Dấu Ấn Kỷ Niệm</span>
            <h2 class="mayhen_invitation_title">Sổ Lưu Bút Chữ Ký</h2>
            <div class="mayhen_invitation_divider">
                <img src="{{ $resolveAsset('divider-ornament.svg') }}" alt="">
            </div>
        </div>

        <div class="mayhen_invitation_signature_card mayhen_invitation_reveal">
            <div class="mayhen_invitation_signature_board_wrap">
                <p class="mayhen_invitation_signature_board_label">Bảng Lưu Giữ Chữ Ký Khách Mời (Có thể kéo thả vị trí)</p>
                <div class="mayhen_invitation_signature_board">
                    <div class="mayhen_invitation_signature_watermark">
                        <img src="{{ $resolveAsset('wax-seal.svg') }}" alt="">
                    </div>
                </div>
            </div>

            <div class="mayhen_invitation_signature_pad_box">
                <div class="mayhen_invitation_signature_toolbar">
                    <span>Chọn màu mực ký tên:</span>
                    <div class="mayhen_invitation_ink_group">
                        <button type="button" class="mayhen_invitation_ink_dot mayhen_invitation_ink_dot--active" data-ink="#233628" style="background:#233628;" aria-label="Xanh rừng đậm"></button>
                        <button type="button" class="mayhen_invitation_ink_dot" data-ink="#967332" style="background:#967332;" aria-label="Vàng cổ điển"></button>
                        <button type="button" class="mayhen_invitation_ink_dot" data-ink="#2c2c2c" style="background:#2c2c2c;" aria-label="Đen than"></button>
                    </div>
                </div>
                <div class="mayhen_invitation_canvas_wrap">
                    <canvas class="mayhen_invitation_signature_canvas" width="380" height="165"></canvas>
                </div>
                <div class="mayhen_invitation_signature_actions">
                    <button type="button" class="mayhen_invitation_btn mayhen_invitation_btn--outline" data-sig-action="clear">Xóa Nét</button>
                    <button type="button" class="mayhen_invitation_btn mayhen_invitation_btn--sage" data-sig-action="preview">Xem Thử</button>
                    <button type="button" class="mayhen_invitation_btn mayhen_invitation_btn--forest" data-sig-action="save">Lưu Chữ Ký</button>
                </div>
            </div>
        </div>
    </section>

    {{-- 10.2 Xác Nhận Tham Dự & Sổ Lưu Bút Lời Chúc (TUYỆT ĐỐI KHÔNG ẢNH NGƯỜI) --}}
    <section class="mayhen_invitation_section mayhen_invitation_rsvp mayhen_invitation_wishes"
             data-mayhen-section="rsvp,wishes"
             style="background-image: url('{{ $resolveAsset('bg-paper-sage.webp') }}');">
        <div class="mayhen_invitation_section_header mayhen_invitation_reveal">
            <span class="mayhen_invitation_eyebrow">Sự Hiện Diện Trân Quý</span>
            <h2 class="mayhen_invitation_title">Xác Nhận Tham Dự &amp; Gửi Lời Chúc</h2>
            <div class="mayhen_invitation_divider">
                <img src="{{ $resolveAsset('divider-ornament.svg') }}" alt="">
            </div>
        </div>

        <div class="mayhen_invitation_rsvp_card mayhen_invitation_reveal">
            <div class="mayhen_invitation_rsvp_seal_header">
                <div class="mayhen_invitation_rsvp_seal_icon">
                    <img src="{{ $resolveAsset('wax-seal.svg') }}" alt="Tem sáp">
                </div>
            </div>

            <form class="mayhen_invitation_form" autocomplete="off">
                <div class="mayhen_invitation_field">
                    <input type="text" name="name" required class="mayhen_invitation_input" placeholder="Họ và Tên của bạn *">
                </div>

                <div class="mayhen_invitation_radio_group">
                    <label class="mayhen_invitation_radio_label">
                        <input type="radio" name="attending" value="Có, tôi sẽ đến" checked>
                        <span>Có, mình sẽ đến</span>
                    </label>
                    <label class="mayhen_invitation_radio_label">
                        <input type="radio" name="attending" value="Xin lỗi, tôi bận mất rồi">
                        <span>Tiếc quá, mình bận</span>
                    </label>
                </div>

                <div class="mayhen_invitation_row_2col">
                    <select name="party_side" class="mayhen_invitation_select">
                        <option value="Tiệc Cưới Nhà Trai & Nhà Gái">Nơi tham dự: Cả hai bên</option>
                        <option value="Tiệc Cưới Nhà Trai">Tiệc Cưới Nhà Trai</option>
                        <option value="Tiệc Cưới Nhà Gái">Tiệc Cưới Nhà Gái</option>
                    </select>
                    <select name="guest_count" class="mayhen_invitation_select">
                        <option value="1 Người">Số lượng: 1 Người</option>
                        <option value="2 Người">2 Người</option>
                        <option value="3 Người">3 Người</option>
                        <option value="Gia đình">Đi cùng Gia đình</option>
                    </select>
                </div>

                <div class="mayhen_invitation_field">
                    <textarea name="message" class="mayhen_invitation_textarea" placeholder="Gửi lời chúc tốt đẹp nhất tới cô dâu chú rể..."></textarea>
                </div>

                <button type="submit" class="mayhen_invitation_submit_btn">Xác Nhận &amp; Gửi Lời Chúc</button>
            </form>
        </div>

        <div class="mayhen_invitation_wishes_box mayhen_invitation_reveal">
            <div class="mayhen_invitation_wishes_list">
                <div class="mayhen_invitation_wish_item">
                    <p class="mayhen_invitation_wish_msg">“Chúc hai bạn trăm năm tình viên mãn, bạc đầu nghĩa phu thê, mãi mãi ngập tràn tiếng cười!”</p>
                    <span class="mayhen_invitation_wish_author">— Hùng Nguyễn</span>
                </div>
                <div class="mayhen_invitation_wish_item">
                    <p class="mayhen_invitation_wish_msg">“Thật ngưỡng mộ tình yêu đẹp của hai em. Chúc gia đình nhỏ luôn ấm áp, bình yên và hạnh phúc trọn vẹn!”</p>
                    <span class="mayhen_invitation_wish_author">— Lê Quốc Huy</span>
                </div>
                <div class="mayhen_invitation_wish_item">
                    <p class="mayhen_invitation_wish_msg">“Chúc cô dâu chú rể trăm năm hạnh phúc, luôn thấu hiểu và đồng hành cùng nhau trên mọi chặng đường!”</p>
                    <span class="mayhen_invitation_wish_author">— Hạ Châm &amp; Gia đình</span>
                </div>
            </div>
        </div>
    </section>

    {{-- 10.3 Lời Cảm Ơn Cuối Thiệp (ENDING & THANK YOU) --}}
    <footer class="mayhen_invitation_section mayhen_invitation_section--forest mayhen_invitation_footer mayhen_invitation_ending"
            data-mayhen-section="ending">
        <span class="mayhen_invitation_eyebrow">Forever &amp; Always</span>
        <div class="mayhen_invitation_footer_ring mayhen_invitation_lightbox_trigger">
            <img src="{{ $resolveAsset('footer-couple.webp', $extraData['footer_photo_url'] ?? ($data['footer_photo_url'] ?? null)) }}"
                 alt="{{ $coupleNames }}"
                 data-sync-target="footer_photo_url">
        </div>
        <h2 class="mayhen_invitation_footer_thank" data-sync-target="ending_title">{{ $endingTitle }}</h2>
        <p class="mayhen_invitation_footer_names"><span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span></p>
        <p class="mayhen_invitation_footer_sub" data-sync-target="ending_text">
            {{ $endingText }}
        </p>
    </footer>

    {{-- ==================== LIGHTBOX TRÌNH CHIẾU ẢNH ==================== --}}
    <div class="mayhen_invitation_lightbox" role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to">
        <div class="mayhen_invitation_lightbox_inner">
            <button type="button" class="mayhen_invitation_lightbox_close" aria-label="Đóng">✕</button>
            <button type="button" class="mayhen_invitation_lightbox_nav mayhen_invitation_lightbox_nav--prev" aria-label="Ảnh trước">❮</button>
            <img src="" alt="Wedding Photo" class="mayhen_invitation_lightbox_img">
            <button type="button" class="mayhen_invitation_lightbox_nav mayhen_invitation_lightbox_nav--next" aria-label="Ảnh sau">❯</button>
        </div>
    </div>

    {{-- ==================== TOAST NOTIFICATION ==================== --}}
    <div class="mayhen_invitation_toast" role="status" aria-live="polite"></div>

    {{-- ==================== LOCAL AUDIO PLAYER ==================== --}}
    <audio class="mayhen_invitation_audio" src="{{ $resolveAsset('music.mp3') }}" preload="auto" loop playsinline></audio>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ route('templates.asset', ['slug' => 'hanh-phuc-mang-ten-chung-minh', 'path' => 'script.js']) }}?v={{ $jsVersion }}"></script>
@endpush
