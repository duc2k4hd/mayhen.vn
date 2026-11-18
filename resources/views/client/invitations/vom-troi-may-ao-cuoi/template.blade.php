@extends('client.layouts.invitation')

@php
    use App\Services\VietQrService;

    $tplSlug = $template->slug ?? 'vom-troi-may-ao-cuoi';
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
    $extraData = is_array($wedding?->extra_data ?? null) ? $wedding->extra_data : [];
    $isPreview = !empty($isPreview);
    $isEmbed = !empty($isEmbed)
        || request()->boolean('embed')
        || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe'], true);
    $autoOpened = $isEmbed || request()->boolean('opened');
    $isCuratedSeven = !empty($isCuratedSeven);

    // 1. Couple & Opening Identity
    $groomName = trim((string) ($wedding?->groom_name ?? ($extraData['groom_name'] ?? 'Minh Tiến')));
    if ($groomName === '') {
        $groomName = 'Minh Tiến';
    }
    $brideName = trim((string) ($wedding?->bride_name ?? ($extraData['bride_name'] ?? 'Thu Nguyệt')));
    if ($brideName === '') {
        $brideName = 'Thu Nguyệt';
    }
    $coupleNames = $groomName . ' & ' . $brideName;

    $groomShortParts = preg_split('/\s+/', $groomName);
    $brideShortParts = preg_split('/\s+/', $brideName);
    $groomInitial = mb_strtoupper(mb_substr((string) end($groomShortParts), 0, 1));
    $brideInitial = mb_strtoupper(mb_substr((string) end($brideShortParts), 0, 1));

    $guestNameRaw = trim((string) ($guest?->name ?? ''));
    $displayGuestName = $guestNameRaw !== '' ? $guestNameRaw : 'Bạn và Gia Đình';
    $guestLenTier = mb_strlen($displayGuestName) > 28 ? '2' : (mb_strlen($displayGuestName) > 18 ? '1' : '0');

    // 2. Date & Lunar Breakdown (Vietnam Timezone Asia/Ho_Chi_Minh)
    $rawWeddingDate = trim((string) ($wedding?->wedding_date?->format('Y-m-d') ?? ($wedding?->wedding_date ?? ($extraData['wedding_date'] ?? '2026-12-26'))));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawWeddingDate)) {
        $rawWeddingDate = '2026-12-26';
    }
    $dateTs = strtotime($rawWeddingDate . ' 17:00:00') ?: strtotime('2026-12-26 17:00:00');
    $wDay = date('d', $dateTs);
    $wMonth = date('m', $dateTs);
    $wYear = date('Y', $dateTs);

    $weekdaysVi = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    $wWeekday = $weekdaysVi[(int) date('w', $dateTs)] ?? 'Thứ Bảy';
    $weddingDateDot = $wDay . ' . ' . $wMonth . ' . ' . $wYear;
    $weddingDateFull = $wWeekday . ', ngày ' . $wDay . ' tháng ' . $wMonth . ' năm ' . $wYear;

    $targetTime = trim((string) ($extraData['event_target_time'] ?? ($extraData['party_time'] ?? '17:00')));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $targetTime)) {
        $targetTime = '17:00';
    }
    $targetIso = $rawWeddingDate . 'T' . $targetTime . ':00+07:00';
    $targetTimeDisplay = str_replace(':', ' giờ ', $targetTime);

    $lunarDate = trim((string) ($extraData['wedding_lunar_date'] ?? '(Tức ngày 18 tháng 11 năm Bính Ngọ)'));

    // 3. Portraits & Decorative Photos (100% Local Assets Fallbacks)
    $heroPhoto = $resolveAsset(
        $wedding?->cover_photo_url ?? ($extraData['hero_photo_url'] ?? ($extraData['cover_photo_url'] ?? '')),
        $assetBase . '/images/hero-couple.webp'
    );
    $envelopePhoto = $resolveAsset($extraData['envelope_photo_url'] ?? '', $assetBase . '/images/envelope-couple.webp');
    $vinylPhoto = $resolveAsset($extraData['vinyl_photo_url'] ?? '', $assetBase . '/images/vinyl-disc-couple.webp');
    $groomPhoto = $resolveAsset($extraData['groom_photo_url'] ?? '', $assetBase . '/images/groom-portrait.webp');
    $bridePhoto = $resolveAsset($extraData['bride_photo_url'] ?? '', $assetBase . '/images/bride-portrait.webp');
    $ceremonyPhoto = $resolveAsset($extraData['ceremony_photo_url'] ?? '', $assetBase . '/images/ceremony-couple.webp');
    $saveDatePhoto = $resolveAsset($extraData['save_date_photo_url'] ?? '', $assetBase . '/images/save-date.webp');
    $footerPhoto = $resolveAsset($extraData['footer_photo_url'] ?? '', $assetBase . '/images/footer-couple.webp');

    $groomBio = trim((string) ($extraData['groom_bio'] ?? 'Chàng trai ấm áp, điềm đạm và luôn đặt gia đình làm điểm tựa vững chãi.'));
    $brideBio = trim((string) ($extraData['bride_bio'] ?? 'Cô gái dịu dàng, tinh tế, luôn mang đến nụ cười và bình yên mỗi ngày.'));

    // 4. Invitation Letter & Families
    $partyTime = trim((string) ($extraData['party_time'] ?? '17:00'));
    $partyTimeFormatted = str_replace(':', ' giờ ', $partyTime);
    $partyVenue = trim((string) ($extraData['party_venue'] ?? 'Millennium'));
    $eventPartyAddress = trim((string) ($extraData['event_party_address'] ?? ($extraData['party_address'] ?? 'Tầng 2, Khách sạn Grand Plaza Hà Nội, 117 Đường Trần Duy Hưng, Cầu Giấy, Hà Nội')));
    $partyMapUrl = trim((string) ($extraData['event_party_map_link'] ?? ($extraData['party_map_url'] ?? '')));
    if ($partyMapUrl === '' || str_starts_with($partyMapUrl, '#')) {
        $partyMapUrl = 'https://maps.google.com/?q=' . urlencode($partyVenue . ', ' . $eventPartyAddress);
    }
    $mapEmbedUrl = 'https://maps.google.com/maps?q=' . urlencode($partyVenue . ', ' . $eventPartyAddress) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';

    $groomFather = trim((string) ($extraData['groom_father'] ?? 'Hoàng Minh Bảo'));
    $groomMother = trim((string) ($extraData['groom_mother'] ?? 'Trần Thị Quỳnh Anh'));
    $groomParentsAddress = trim((string) ($extraData['groom_parents_address'] ?? 'Cầu Giấy, Hà Nội'));

    $brideFather = trim((string) ($extraData['bride_father'] ?? 'Nguyễn Văn Nam'));
    $brideMother = trim((string) ($extraData['bride_mother'] ?? 'Trịnh Thu Trà'));
    $brideParentsAddress = trim((string) ($extraData['bride_parents_address'] ?? 'Ba Đình, Hà Nội'));

    // 5. Ceremonies Scope
    $ceremonyScope = $ceremonyScope ?? ($extraData['ceremony_scope'] ?? 'both');
    $showBrideCeremony = ($ceremonyScope !== 'groom');
    $showGroomCeremony = ($ceremonyScope !== 'bride');

    $ceremonyBrideTime = trim((string) ($extraData['ceremony_time'] ?? '08:00'));
    $ceremonyBrideDate = trim((string) ($extraData['ceremony_date'] ?? ($wDay . '/' . $wMonth . '/' . $wYear)));
    $ceremonyBrideVenue = trim((string) ($extraData['ceremony_venue'] ?? 'Tại Tư Gia Nhà Gái'));
    $ceremonyBrideAddr = trim((string) ($extraData['ceremony_address'] ?? $brideParentsAddress));

    $ceremonyGroomTime = trim((string) ($extraData['ceremony_groom_time'] ?? '10:00'));
    $ceremonyGroomDate = trim((string) ($extraData['ceremony_groom_date'] ?? ($wDay . '/' . $wMonth . '/' . $wYear)));
    $ceremonyGroomVenue = trim((string) ($extraData['ceremony_groom_venue'] ?? 'Tại Tư Gia Nhà Trai'));
    $ceremonyGroomAddr = trim((string) ($extraData['ceremony_groom_address'] ?? $groomParentsAddress));

    // 6. Timeline Milestones
    $defaultTimeline = [
        ['time' => '08:00', 'title' => 'Lễ vu quy'],
        ['time' => '10:00', 'title' => 'Lễ thành hôn'],
        ['time' => '16:30', 'title' => 'Đón khách'],
        ['time' => '17:00', 'title' => 'Khai tiệc'],
        ['time' => '18:00', 'title' => 'Cắt bánh · Rót rượu'],
        ['time' => '19:00', 'title' => 'Chụp ảnh lưu niệm'],
    ];
    $timelineItems = is_array($extraData['timeline'] ?? null) && count($extraData['timeline']) > 0
        ? array_values($extraData['timeline'])
        : $defaultTimeline;

    // 7. Love Story Polaroids
    $storyTitle = trim((string) ($extraData['story_title'] ?? 'Gặp gỡ'));
    $storySubtitle = trim((string) ($extraData['story_subtitle'] ?? 'Thương nhau'));
    $storyChapterThreeTitle = trim((string) ($extraData['story_chapter_three_title'] ?? 'Về chung một nhà'));
    $coupleQuote = trim((string) ($extraData['couple_quote'] ?? 'Hôm đó trời không đẹp, mình cũng chẳng có tâm trạng gì đặc biệt. Nhưng anh xuất hiện – không ồn ào, không nổi bật, chỉ là một người nói chuyện đủ ấm để mình muốn trả lời thêm lần nữa.'));
    $storyContent = trim((string) ($extraData['story_content'] ?? 'Ban đầu tụi mình chỉ là hai người xa lạ nói chuyện cho đỡ buồn. Không ai nghĩ sẽ gắn bó. Nhưng càng nói, càng thấy hợp. Từ những câu chuyện nhỏ nhặt trong ngày, đến những lúc mệt mỏi chỉ cần có người nghe.'));
    $storyThirdContent = trim((string) ($extraData['story_chapter_three_content'] ?? 'Anh không phải kiểu người ngọt ngào quá mức. Nhưng anh luôn có mặt đúng lúc mình cần. Tình yêu của tụi mình không ồn ào, không quá hoàn hảo, nhưng cuối cùng vẫn chọn ở lại cùng nhau mãi về sau.'));

    // 8. Gallery (10 Photos Strict)
    $defaultGallery = [];
    for ($gi = 1; $gi <= 10; $gi++) {
        $defaultGallery[] = $assetBase . '/images/gallery-' . str_pad((string) $gi, 2, '0', STR_PAD_LEFT) . '.webp';
    }
    $rawGallery = is_array($extraData['gallery'] ?? null) ? array_values($extraData['gallery']) : [];
    $galleryPhotos = [];
    for ($gi = 0; $gi < 10; $gi++) {
        $galleryPhotos[] = $resolveAsset($rawGallery[$gi] ?? '', $defaultGallery[$gi]);
    }

    // 9. Gift / VietQR
    $groomBankName = trim((string) ($extraData['groom_bank_name'] ?? 'Vietcombank'));
    $groomBankAccount = trim((string) ($extraData['groom_bank_account'] ?? '0123456789'));
    $groomAccountName = trim((string) ($extraData['groom_account_name'] ?? 'HOANG MINH TIEN'));
    $qrGroomUrl = VietQrService::resolveQrImage(
        $extraData['qr_groom_image'] ?? null,
        $groomBankName,
        $groomBankAccount,
        'Mung cuoi ' . $groomName,
        $assetBase . '/images/qr-groom.webp',
        '0123456789'
    );

    $brideBankName = trim((string) ($extraData['bride_bank_name'] ?? 'Techcombank'));
    $brideBankAccount = trim((string) ($extraData['bride_bank_account'] ?? '9876543210'));
    $brideAccountName = trim((string) ($extraData['bride_account_name'] ?? 'NGUYEN THU NGUYET'));
    $qrBrideUrl = VietQrService::resolveQrImage(
        $extraData['qr_bride_image'] ?? null,
        $brideBankName,
        $brideBankAccount,
        'Mung cuoi ' . $brideName,
        $assetBase . '/images/qr-bride.webp',
        '9876543210'
    );

    // 10. Dresscode
    $dresscodeColorsRaw = trim((string) ($extraData['dresscode_colors'] ?? '#F6F8F2,#CFDDCB,#9DB89F,#618768'));
    $dresscodeColorNames = ['Trắng Ngà', 'Xanh Bạc Hà', 'Xanh Lá Mạ', 'Xanh Rêu', 'Kem Nhạt', 'Sage Nhạt'];
    $dresscodeHexList = array_values(array_filter(array_map('trim', explode(',', $dresscodeColorsRaw))));
    if (empty($dresscodeHexList)) {
        $dresscodeHexList = ['#F6F8F2', '#CFDDCB', '#9DB89F', '#618768'];
    }
    $dresscodeNote = trim((string) ($extraData['dresscode_note'] ?? 'Chúng tôi rất mong quý khách lựa chọn trang phục theo các tông màu dưới đây để cùng hòa vào không khí buổi tiệc.'));

    // 11. Thank You Footer
    $thankYouTitle = trim((string) ($extraData['thank_you_title'] ?? 'Thank you!'));
    $thankYouQuote = trim((string) ($extraData['thank_you_quote'] ?? 'Sự hiện diện của bạn là niềm vinh hạnh cho gia đình chúng tôi. Chân thành cảm ơn!'));

    // 12. Audio & Endpoints
    $resolvedMusicUrl = $resolveAsset($extraData['music_url'] ?? '', $assetBase . '/wedding-song.mp3');
    $rsvpEndpoint = $rsvpSubmitUrl ?? '';
    $wishEndpoint = $wishSubmitUrl ?? '';
    $sigEndpoint = (!$isPreview && isset($invitation) && $invitation->id)
        ? route('invitation.signature.save', ['id' => $invitation->id])
        : '';

    $wishesList = $wishes ?? collect();
    $savedSignatures = collect($wishesList)->filter(static fn ($w) => !empty($w->signature_url))->take(8);

    // Initial Countdown Values (Server-rendered for instant SSR hydration)
    $nowTs = time();
    $diffSec = max(0, $dateTs - $nowTs);
    $initDays = (int) floor($diffSec / 86400);
    $initHours = (int) floor(($diffSec % 86400) / 3600);
    $initMins = (int) floor(($diffSec % 3600) / 60);
    $initSecs = (int) ($diffSec % 60);

    // Google Calendar Link
    $gcalStart = gmdate('Ymd\THis\Z', $dateTs);
    $gcalEnd = gmdate('Ymd\THis\Z', $dateTs + 10800);
    $gcalHref = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . urlencode('Lễ Cưới ' . $coupleNames)
        . '&dates=' . $gcalStart . '/' . $gcalEnd
        . '&details=' . urlencode('Trân trọng kính mời tới dự lễ thành hôn của ' . $coupleNames)
        . '&location=' . urlencode($partyVenue . ' - ' . $eventPartyAddress);
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ $assetBase }}/styles.css">
@endpush

@section('content')
<div class="mayhen_invitation_root"
     id="mayhen_invitation_root"
     data-template-slug="{{ $tplSlug }}"
     data-target-iso="{{ $targetIso }}"
     data-wedding-date="{{ $rawWeddingDate }}"
     data-couple-names="{{ $coupleNames }}"
     data-party-venue="{{ $partyVenue }}"
     data-party-address="{{ $eventPartyAddress }}"
     data-rsvp-endpoint="{{ $rsvpEndpoint }}"
     data-wish-endpoint="{{ $wishEndpoint }}"
     data-signature-endpoint="{{ $sigEndpoint }}"
     data-csrf-token="{{ csrf_token() }}"
     data-is-embed="{{ $isEmbed ? '1' : '0' }}"
     data-is-preview="{{ $isPreview ? '1' : '0' }}">

    {{-- Template Owned Audio Element (Music Dock Automatically Yields) --}}
    <audio class="mayhen_invitation_audio"
           data-mayhen-audio
           data-sync-target="music_url"
           preload="none"
           loop>
        <source src="{{ $resolvedMusicUrl }}" type="audio/mpeg">
    </audio>

    {{-- Falling Botanical Sage Petals & Light Motes Stage --}}
    <div class="mayhen_invitation_petals" id="mayhenPetalsLayer" aria-hidden="true"></div>

    {{-- 0. ENVELOPE OPENING CURTAIN (Hidden immediately in $isEmbed / Auto-open) --}}
    <div class="mayhen_invitation_opening{{ $autoOpened ? ' is-opened' : '' }}"
         id="mayhenOpeningCurtain"
         @if($autoOpened) aria-hidden="true" @endif>
        <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_open_c1" aria-hidden="true"></i>
        <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_open_c2" aria-hidden="true"></i>
        <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_open_c3" aria-hidden="true"></i>
        <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_open_c4" aria-hidden="true"></i>

        <div class="mayhen_invitation_open_inner">
            <h2 class="mayhen_invitation_open_title">Wedding Invitation</h2>
            <p class="mayhen_invitation_open_date" data-sync-target="wedding_date">{{ $wDay }}.{{ $wMonth }}.{{ $wYear }}</p>
            <i class="mayhen_invitation_divider mayhen_invitation_divider--white" aria-hidden="true"></i>

            <div class="mayhen_invitation_envelope_box">
                <div class="mayhen_invitation_env_back" aria-hidden="true"></div>

                <div class="mayhen_invitation_env_card">
                    <img src="{{ $envelopePhoto }}"
                         alt="Ảnh phong bì cưới"
                         class="mayhen_invitation_env_photo"
                         width="275"
                         height="322">
                </div>

                <div class="mayhen_invitation_env_badge">
                    <span class="mayhen_invitation_env_to_label">Thân gửi</span>
                    <span class="mayhen_invitation_env_guest" data-guest-len="{{ $guestLenTier }}">{{ $displayGuestName }}</span>
                </div>

                <div class="mayhen_invitation_env_front" aria-hidden="true"></div>

                <div class="mayhen_invitation_env_actions">
                    <button type="button"
                            class="mayhen_invitation_open_btn"
                            id="mayhenOpenInvitationBtn">
                        Ấn vào để Xem
                    </button>
                    <p class="mayhen_invitation_env_from">
                        From: <span data-sync-target="groom_name">{{ $groomName }}</span> - <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="mayhen_invitation_wrap">
        <main class="mayhen_invitation_main" id="mayhen_invitation_main_content">

            {{-- ==================== SECTION 1: HERO COVER & TURNTABLE DISC ==================== --}}
            <div class="mayhen_invitation_hero_group mayhen_invitation_hero"
                 id="mayhen_section_hero"
                 data-mayhen-section="hero">

                <section class="mayhen_invitation_section mayhen_invitation_section--sage mayhen_invitation_hero_band mayhen_invitation_reveal">
                    <div class="mayhen_invitation_section_bg"></div>

                    <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_hero_bg_g1" aria-hidden="true"></i>
                    <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_hero_bg_g2" aria-hidden="true"></i>
                    <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_hero_bg_g3" aria-hidden="true"></i>
                    <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_hero_bg_g4" aria-hidden="true"></i>

                    <div class="mayhen_invitation_card mayhen_invitation_hero_card">
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f1" data-bloom="xoe-t" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f2" data-bloom="roi" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f3" data-bloom="no" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f4" data-bloom="no" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f5" data-bloom="xoe-p" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f6" data-bloom="xoe-p" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f7" data-bloom="no" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f8" data-bloom="xoe-p" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f9" data-bloom="moc" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f10" data-bloom="xoe-t" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f11" data-bloom="moc" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_hero_f12" data-bloom="xoe-t" aria-hidden="true"></i>

                        <div class="mayhen_invitation_hero_oval">
                            <div class="mayhen_invitation_hero_oval_ring" aria-hidden="true"></div>
                            <div class="mayhen_invitation_hero_frame">
                                <img src="{{ $heroPhoto }}"
                                     alt="Ảnh bìa cặp đôi {{ $coupleNames }}"
                                     class="mayhen_invitation_hero_media"
                                     data-sync-target="cover_photo_url"
                                     width="300"
                                     height="510">
                                <div class="mayhen_invitation_sparkle" aria-hidden="true">
                                    <b></b><b></b><b></b><b></b><b></b><b></b>
                                </div>
                            </div>
                            <span class="mayhen_invitation_hero_sheen" aria-hidden="true"></span>
                        </div>

                        <div class="mayhen_invitation_hero_vertical_date mayhen_invitation_hero_vertical_date--left" aria-hidden="true">
                            <span class="mayhen_invitation_hero_vertical_text">
                                <span data-sync-target="wedding_day">{{ $wDay }}</span>•<span data-sync-target="wedding_month">{{ $wMonth }}</span>•<span data-sync-target="wedding_year">{{ $wYear }}</span>
                            </span>
                        </div>
                        <div class="mayhen_invitation_hero_vertical_date mayhen_invitation_hero_vertical_date--right">
                            <span class="mayhen_invitation_hero_vertical_text">
                                <span data-sync-target="wedding_day">{{ $wDay }}</span>•<span data-sync-target="wedding_month">{{ $wMonth }}</span>•<span data-sync-target="wedding_year">{{ $wYear }}</span>
                            </span>
                        </div>
                    </div>
                </section>

                {{-- Overlapping Turntable Vinyl Player --}}
                <section class="mayhen_invitation_section mayhen_invitation_music_band mayhen_invitation_reveal" id="mayhenMusicBand">
                    <div class="mayhen_invitation_section_bg"></div>

                    <div class="mayhen_invitation_disc_wrap">
                        <div class="mayhen_invitation_disc">
                            <div class="mayhen_invitation_disc_spin">
                                <div class="mayhen_invitation_disc_photo_box">
                                    <img src="{{ $vinylPhoto }}"
                                         alt="Đĩa nhạc cưới {{ $coupleNames }}"
                                         class="mayhen_invitation_disc_photo"
                                         width="260"
                                         height="260">
                                </div>
                                <div class="mayhen_invitation_disc_center" aria-hidden="true"></div>
                            </div>
                        </div>
                        <i class="mayhen_invitation_tonearm" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_disc_bouquet" aria-hidden="true"></i>
                    </div>

                    <div class="mayhen_invitation_speaker_row">
                        <button type="button"
                                class="mayhen_invitation_speaker_btn"
                                id="mayhenDiscPlayBtn"
                                aria-label="Phát hoặc tạm dừng nhạc nền">
                            <svg class="mayhen_invitation_icon_play" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6z" />
                            </svg>
                            <svg class="mayhen_invitation_icon_pause" viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="6" y="5" width="4" height="14" rx="1" />
                                <rect x="14" y="5" width="4" height="14" rx="1" />
                            </svg>
                        </button>
                    </div>

                    <div class="mayhen_invitation_twigs_row" aria-hidden="true">
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_twig mayhen_invitation_twig--left"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_twig mayhen_invitation_twig--right"></i>
                    </div>
                </section>
            </div>

            {{-- ==================== SECTION 2: COUPLE & PARENTS INVITATION LETTER ==================== --}}
            <div class="mayhen_invitation_parents"
                 id="mayhen_section_parents"
                 data-mayhen-section="parents">

                {{-- Staggered Couple Portraits Card --}}
                <section class="mayhen_invitation_section mayhen_invitation_couple_band mayhen_invitation_reveal">
                    <div class="mayhen_invitation_section_bg"></div>

                    <div class="mayhen_invitation_couple_card">
                        <div class="mayhen_invitation_couple_photo_box mayhen_invitation_couple_photo_box--groom mayhen_invitation_reveal" data-reveal="left">
                            <img src="{{ $groomPhoto }}"
                                 alt="Chân dung Chú rể {{ $groomName }}"
                                 class="mayhen_invitation_couple_photo"
                                 data-sync-target="groom_photo_url"
                                 width="380"
                                 height="570">
                            <span class="mayhen_invitation_couple_inner_border" aria-hidden="true"></span>
                            <div class="mayhen_invitation_sparkle" aria-hidden="true"><b></b><b></b><b></b></div>
                        </div>

                        <div class="mayhen_invitation_couple_info mayhen_invitation_couple_info--groom mayhen_invitation_reveal" data-reveal="right">
                            <h3 class="mayhen_invitation_couple_script mayhen_invitation_script_text" data-sync-target="groom_name">{{ $groomName }}</h3>
                            <p class="mayhen_invitation_couple_role">CHÚ RỂ</p>
                            <i class="mayhen_invitation_divider" aria-hidden="true"></i>
                            <p class="mayhen_invitation_couple_bio" data-sync-target="groom_bio">{{ $groomBio }}</p>
                        </div>

                        <div class="mayhen_invitation_couple_info mayhen_invitation_couple_info--bride mayhen_invitation_reveal" data-reveal="left">
                            <h3 class="mayhen_invitation_couple_script mayhen_invitation_script_text" data-sync-target="bride_name">{{ $brideName }}</h3>
                            <p class="mayhen_invitation_couple_role">CÔ DÂU</p>
                            <i class="mayhen_invitation_divider" aria-hidden="true"></i>
                            <p class="mayhen_invitation_couple_bio" data-sync-target="bride_bio">{{ $brideBio }}</p>
                        </div>

                        <div class="mayhen_invitation_couple_photo_box mayhen_invitation_couple_photo_box--bride mayhen_invitation_reveal" data-reveal="right">
                            <img src="{{ $bridePhoto }}"
                                 alt="Chân dung Cô dâu {{ $brideName }}"
                                 class="mayhen_invitation_couple_photo"
                                 data-sync-target="bride_photo_url"
                                 width="380"
                                 height="570">
                            <span class="mayhen_invitation_couple_inner_border" aria-hidden="true"></span>
                            <div class="mayhen_invitation_sparkle" aria-hidden="true"><b></b><b></b><b></b></div>
                        </div>
                    </div>
                </section>

                {{-- Invitation Letter & Two Families Card --}}
                <section class="mayhen_invitation_section mayhen_invitation_section--sage mayhen_invitation_letter_band mayhen_invitation_reveal">
                    <div class="mayhen_invitation_section_bg"></div>

                    <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_letter_f1" aria-hidden="true"></i>
                    <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_letter_f2" aria-hidden="true"></i>
                    <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_letter_f3" aria-hidden="true"></i>
                    <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_letter_f4" aria-hidden="true"></i>

                    <div class="mayhen_invitation_card mayhen_invitation_letter_card">
                        <i class="mayhen_invitation_wreath_top" aria-hidden="true"></i>

                        <h2 class="mayhen_invitation_letter_title">TRÂN TRỌNG KÍNH MỜI</h2>

                        <div class="mayhen_invitation_letter_guest" id="mayhenLetterGuestBadge">
                            {{ $displayGuestName }}
                        </div>

                        <p class="mayhen_invitation_letter_subtitle" data-sync-target="ceremony_type">
                            {{ $extraData['ceremony_type'] ?? 'TỚI DỰ BỮA TIỆC MỪNG LỄ THÀNH HÔN CỦA CHÚNG TÔI' }}
                        </p>

                        <div class="mayhen_invitation_letter_names">
                            <i class="mayhen_invitation_divider mayhen_invitation_divider--up" aria-hidden="true"></i>
                            <p class="mayhen_invitation_letter_couple_name mayhen_invitation_script_text" data-sync-target="groom_name">{{ $groomName }}</p>
                            <span class="mayhen_invitation_letter_amp">&amp;</span>
                            <p class="mayhen_invitation_letter_couple_name mayhen_invitation_script_text" data-sync-target="bride_name">{{ $brideName }}</p>
                            <i class="mayhen_invitation_divider mayhen_invitation_divider--down" aria-hidden="true"></i>
                            <div class="mayhen_invitation_monogram_row" aria-hidden="true">
                                <span class="mayhen_invitation_monogram_badge" data-monogram-role="groom">{{ $groomInitial }}</span>
                                <span class="mayhen_invitation_monogram_badge" data-monogram-role="bride">{{ $brideInitial }}</span>
                            </div>
                        </div>

                        <div class="mayhen_invitation_letter_details">
                            <p class="mayhen_invitation_letter_time">
                                Vào lúc <span data-sync-target="party_time">{{ $partyTimeFormatted }}</span><br>
                                <span data-sync-target="wedding_date_full">{{ $weddingDateFull }}</span>
                            </p>
                            <p class="mayhen_invitation_letter_lunar" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</p>

                            <p class="mayhen_invitation_letter_at">Tại hội trường</p>
                            <h3 class="mayhen_invitation_letter_venue" data-sync-target="party_venue">{{ $partyVenue }}</h3>
                            <p class="mayhen_invitation_letter_addr" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>

                            <button type="button" class="mayhen_invitation_view_map_btn" data-scroll-to="#mayhen_section_map">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1.6a7.6 7.6 0 0 0-7.6 7.6c0 5.6 7.6 13.2 7.6 13.2s7.6-7.6 7.6-13.2A7.6 7.6 0 0 0 12 1.6zm0 10.4a2.8 2.8 0 1 1 0-5.6 2.8 2.8 0 0 1 0 5.6z" /></svg>
                                <span>Xem vị trí</span>
                            </button>
                        </div>

                        <div class="mayhen_invitation_families_grid">
                            <div>
                                <h4 class="mayhen_invitation_family_side">Nhà trai</h4>
                                <p class="mayhen_invitation_family_names">
                                    Ông: <span data-sync-target="groom_father">{{ $groomFather }}</span><br>
                                    Bà: <span data-sync-target="groom_mother">{{ $groomMother }}</span>
                                </p>
                                <p class="mayhen_invitation_family_addr" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                            </div>
                            <div>
                                <h4 class="mayhen_invitation_family_side">Nhà gái</h4>
                                <p class="mayhen_invitation_family_names">
                                    Ông: <span data-sync-target="bride_father">{{ $brideFather }}</span><br>
                                    Bà: <span data-sync-target="bride_mother">{{ $brideMother }}</span>
                                </p>
                                <p class="mayhen_invitation_family_addr" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                            </div>
                        </div>

                        <p class="mayhen_invitation_letter_honor" data-sync-target="slogan">{{ $extraData['slogan'] ?? 'Rất hân hạnh được đón tiếp!' }}</p>
                        <i class="mayhen_invitation_wreath_bottom" aria-hidden="true"></i>
                    </div>
                </section>
            </div>

            {{-- ==================== SECTION 3: COUNTDOWN & MONTH CALENDAR ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_countdown_section mayhen_invitation_countdown_calendar mayhen_invitation_reveal"
                     id="mayhen_section_countdown"
                     data-mayhen-section="countdown_calendar">
                <div class="mayhen_invitation_section_bg"></div>

                <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_countdown_f1" data-bloom="xoe-t" aria-hidden="true"></i>
                <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_countdown_f2" data-bloom="xoe-p" aria-hidden="true"></i>

                <h2 class="mayhen_invitation_countdown_title">THỜI GIAN ĐẾM NGƯỢC</h2>

                <div class="mayhen_invitation_countdown_grid" data-countdown="{{ $targetIso }}" id="mayhenCountdownBox">
                    <div class="mayhen_invitation_cd_item">
                        <div class="mayhen_invitation_cd_box"><span class="mayhen_invitation_cd_val" data-cd-days>{{ str_pad((string) $initDays, 2, '0', STR_PAD_LEFT) }}</span></div>
                        <span class="mayhen_invitation_cd_label">Ngày</span>
                    </div>
                    <span class="mayhen_invitation_cd_sep">:</span>
                    <div class="mayhen_invitation_cd_item">
                        <div class="mayhen_invitation_cd_box"><span class="mayhen_invitation_cd_val" data-cd-hours>{{ str_pad((string) $initHours, 2, '0', STR_PAD_LEFT) }}</span></div>
                        <span class="mayhen_invitation_cd_label">Giờ</span>
                    </div>
                    <span class="mayhen_invitation_cd_sep">:</span>
                    <div class="mayhen_invitation_cd_item">
                        <div class="mayhen_invitation_cd_box"><span class="mayhen_invitation_cd_val" data-cd-mins>{{ str_pad((string) $initMins, 2, '0', STR_PAD_LEFT) }}</span></div>
                        <span class="mayhen_invitation_cd_label">Phút</span>
                    </div>
                    <span class="mayhen_invitation_cd_sep">:</span>
                    <div class="mayhen_invitation_cd_item">
                        <div class="mayhen_invitation_cd_box"><span class="mayhen_invitation_cd_val" data-cd-secs>{{ str_pad((string) $initSecs, 2, '0', STR_PAD_LEFT) }}</span></div>
                        <span class="mayhen_invitation_cd_label">Giây</span>
                    </div>
                </div>

                <i class="mayhen_invitation_tulip_divider" aria-hidden="true"></i>

                <div class="mayhen_invitation_calendar_shell mayhen_invitation_calendar">
                    <p class="mayhen_invitation_calendar_month mayhen_invitation_calendar_month_year" data-sync-target="calendar_month_year">
                        THÁNG <span data-sync-target="wedding_month">{{ $wMonth }}</span> · <span data-sync-target="wedding_year">{{ $wYear }}</span>
                    </p>
                    <div class="mayhen_invitation_calendar_wrapper">
                        <div class="mayhen_invitation_calendar_header">
                            <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                        </div>
                        <div class="mayhen_invitation_calendar_body" id="mayhenCalendarBody" data-sync-target="wedding_calendar">
                            {{-- Populated cleanly by script.js & window.renderMayhenWeddingCalendar --}}
                        </div>
                    </div>
                </div>

                <div class="mayhen_invitation_add_cal_wrap">
                    <a href="{{ $gcalHref }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="mayhen_invitation_add_cal_btn"
                       id="mayhenAddCalLink">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z" /></svg>
                        <span>Thêm vào lịch</span>
                    </a>
                </div>
            </section>

            {{-- ==================== SECTION 4: WEDDING EVENTS (NGÀY CHUNG ĐÔI) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_section--sage mayhen_invitation_events mayhen_invitation_reveal"
                     id="mayhen_section_events"
                     data-mayhen-section="events">
                <div class="mayhen_invitation_section_bg"></div>

                <div class="mayhen_invitation_events_frame">
                    <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_events_corner" data-bloom="xoe-p" aria-hidden="true"></i>
                    <h2 class="mayhen_invitation_events_title">NGÀY CHUNG ĐÔI</h2>

                    <div class="mayhen_invitation_events_row">
                        <div class="mayhen_invitation_events_photo_box">
                            <img src="{{ $ceremonyPhoto }}"
                                 alt="Khoảnh khắc ngày chung đôi"
                                 class="mayhen_invitation_events_photo"
                                 width="347"
                                 height="458">
                            <div class="mayhen_invitation_sparkle" aria-hidden="true"><b></b><b></b><b></b></div>
                        </div>

                        <div class="mayhen_invitation_events_list">
                            @if($showBrideCeremony)
                                <article class="mayhen_invitation_card mayhen_invitation_event_card">
                                    <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_event_flower_l" aria-hidden="true"></i>
                                    <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_event_flower_r" aria-hidden="true"></i>
                                    <h3 class="mayhen_invitation_event_name">Lễ Vu Quy</h3>
                                    <div class="mayhen_invitation_event_rings" aria-hidden="true"></div>
                                    <p class="mayhen_invitation_event_time">Vào lúc <span data-sync-target="ceremony_time">{{ $ceremonyBrideTime }}</span></p>
                                    <p class="mayhen_invitation_event_weekday" data-sync-target="wedding_weekday">{{ $wWeekday }}</p>
                                    <p class="mayhen_invitation_event_date" data-sync-target="ceremony_date">{{ $ceremonyBrideDate }}</p>
                                    <p class="mayhen_invitation_event_lunar" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</p>
                                    <p class="mayhen_invitation_event_venue" data-sync-target="ceremony_venue">{{ $ceremonyBrideVenue }}</p>
                                    <p class="mayhen_invitation_event_addr" data-sync-target="ceremony_address">{{ $ceremonyBrideAddr }}</p>
                                </article>
                            @endif

                            @if($showGroomCeremony)
                                <article class="mayhen_invitation_card mayhen_invitation_event_card">
                                    <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_event_flower_l" aria-hidden="true"></i>
                                    <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_event_flower_r" aria-hidden="true"></i>
                                    <h3 class="mayhen_invitation_event_name">Lễ Thành Hôn</h3>
                                    <div class="mayhen_invitation_event_rings" aria-hidden="true"></div>
                                    <p class="mayhen_invitation_event_time">Vào lúc <span data-sync-target="ceremony_groom_time">{{ $ceremonyGroomTime }}</span></p>
                                    <p class="mayhen_invitation_event_weekday" data-sync-target="wedding_weekday">{{ $wWeekday }}</p>
                                    <p class="mayhen_invitation_event_date" data-sync-target="ceremony_groom_date">{{ $ceremonyGroomDate }}</p>
                                    <p class="mayhen_invitation_event_lunar" data-sync-target="wedding_lunar_date">{{ $lunarDate }}</p>
                                    <p class="mayhen_invitation_event_venue" data-sync-target="ceremony_groom_venue">{{ $ceremonyGroomVenue }}</p>
                                    <p class="mayhen_invitation_event_addr" data-sync-target="ceremony_groom_address">{{ $ceremonyGroomAddr }}</p>
                                </article>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            {{-- ==================== SECTION 5: WEDDING TIMELINE ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_timeline mayhen_invitation_reveal"
                     id="mayhen_section_timeline"
                     data-mayhen-section="timeline">
                <div class="mayhen_invitation_section_bg"></div>

                <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_timeline_f1" data-bloom="xoe-t" aria-hidden="true"></i>
                <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_timeline_f2" data-bloom="xoe-p" aria-hidden="true"></i>

                <h2 class="mayhen_invitation_timeline_title">Timeline</h2>
                <p class="mayhen_invitation_timeline_subtitle">Lịch trình ngày chung đôi</p>

                <div class="mayhen_invitation_timeline_list mayhen_invitation_reveal" id="mayhenTimelineList">
                    @foreach($timelineItems as $idx => $item)
                        @php
                            $isRight = ($idx % 2 === 1);
                            $rowNum = $idx + 1;
                            $colNum = $isRight ? 2 : 1;
                            $tlTime = trim((string) ($item['time'] ?? '08:00'));
                            $tlTitle = trim((string) ($item['title'] ?? ''));
                        @endphp
                        <div class="mayhen_invitation_timeline_node {{ $isRight ? 'mayhen_invitation_timeline_node--right' : 'mayhen_invitation_timeline_node--left' }}"
                             style="--tl-row: {{ $rowNum }}; --tl-col: {{ $colNum }};">
                            <i class="mayhen_invitation_timeline_dot" aria-hidden="true"></i>
                            <div class="mayhen_invitation_timeline_icon" aria-hidden="true">
                                @if($idx === 0)
                                    <svg viewBox="0 0 24 24"><circle cx="9" cy="15" r="5" /><circle cx="15" cy="15" r="5" /><path d="m7 6 2-3 2 3-2 3z" /></svg>
                                @elseif($idx === 1)
                                    <svg viewBox="0 0 24 24"><path d="M12 2v5M9.5 4.5h5M12 7l6 5v9H6v-9zM3 21h18" /></svg>
                                @elseif($idx === 2)
                                    <svg viewBox="0 0 24 24"><path d="M4 8h3l2-3h6l2 3h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z" /><circle cx="12" cy="13.5" r="3.5" /></svg>
                                @elseif($idx === 3)
                                    <svg viewBox="0 0 24 24"><path d="M7 3h10l-1 7a4 4 0 0 1-8 0zM12 14v6M8 21h8" /></svg>
                                @elseif($idx === 4)
                                    <svg viewBox="0 0 24 24"><path d="M4 21h16M6 21v-7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v7M12 12V8" /></svg>
                                @else
                                    <svg viewBox="0 0 24 24"><path d="M12 20s-8-5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 9c0 6-8 11-8 11z" /></svg>
                                @endif
                            </div>
                            <time class="mayhen_invitation_timeline_time" data-sync-target="timeline.{{ $idx }}.time">{{ $tlTime }}</time>
                            <p class="mayhen_invitation_timeline_label" data-sync-target="timeline.{{ $idx }}.title">{{ $tlTitle }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ==================== SECTION 6: LOVE STORY (POLAROID CHAPTERS) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_section--sage mayhen_invitation_story mayhen_invitation_reveal"
                     id="mayhen_section_story"
                     data-mayhen-section="story">
                <div class="mayhen_invitation_section_bg"></div>

                <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_story_f1" aria-hidden="true"></i>
                <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_story_f2" aria-hidden="true"></i>

                <h2 class="mayhen_invitation_story_heading">Love Story</h2>
                <p class="mayhen_invitation_story_sub" data-sync-target="story_subtitle">{{ $storySubtitle }}</p>

                <div class="mayhen_invitation_story_list">
                    <article class="mayhen_invitation_story_item mayhen_invitation_reveal">
                        <div class="mayhen_invitation_polaroid_frame">
                            <span class="mayhen_invitation_polaroid_foliage" aria-hidden="true"></span>
                            <span class="mayhen_invitation_polaroid_pin" aria-hidden="true"></span>
                            <div class="mayhen_invitation_polaroid_photo_box">
                                <img src="{{ $saveDatePhoto }}"
                                     alt="Chương 1 - {{ $storyTitle }}"
                                     class="mayhen_invitation_polaroid_photo"
                                     data-sync-target="save_date_photo_url"
                                     width="330"
                                     height="346">
                            </div>
                        </div>
                        <div class="mayhen_invitation_polaroid_caption">
                            <h3 class="mayhen_invitation_polaroid_title" data-sync-target="story_title">{{ $storyTitle }}</h3>
                            <p class="mayhen_invitation_polaroid_quote" data-sync-target="couple_quote">{{ $coupleQuote }}</p>
                        </div>
                    </article>

                    <article class="mayhen_invitation_story_item mayhen_invitation_story_item--even mayhen_invitation_reveal">
                        <div class="mayhen_invitation_polaroid_frame">
                            <span class="mayhen_invitation_polaroid_foliage" aria-hidden="true"></span>
                            <span class="mayhen_invitation_polaroid_pin" aria-hidden="true"></span>
                            <div class="mayhen_invitation_polaroid_photo_box">
                                <img src="{{ $galleryPhotos[1] }}"
                                     alt="Chương 2 - {{ $storySubtitle }}"
                                     class="mayhen_invitation_polaroid_photo"
                                     width="330"
                                     height="346">
                            </div>
                        </div>
                        <div class="mayhen_invitation_polaroid_caption">
                            <h3 class="mayhen_invitation_polaroid_title" data-sync-target="story_subtitle">{{ $storySubtitle }}</h3>
                            <p class="mayhen_invitation_polaroid_quote" data-sync-target="story_content">{{ $storyContent }}</p>
                        </div>
                    </article>

                    <article class="mayhen_invitation_story_item mayhen_invitation_reveal">
                        <div class="mayhen_invitation_polaroid_frame">
                            <span class="mayhen_invitation_polaroid_foliage" aria-hidden="true"></span>
                            <span class="mayhen_invitation_polaroid_pin" aria-hidden="true"></span>
                            <div class="mayhen_invitation_polaroid_photo_box">
                                <img src="{{ $galleryPhotos[2] }}"
                                     alt="Chương 3 - {{ $storyChapterThreeTitle }}"
                                     class="mayhen_invitation_polaroid_photo"
                                     width="330"
                                     height="346">
                            </div>
                        </div>
                        <div class="mayhen_invitation_polaroid_caption">
                            <h3 class="mayhen_invitation_polaroid_title">{{ $storyChapterThreeTitle }}</h3>
                            <p class="mayhen_invitation_polaroid_quote">{{ $storyThirdContent }}</p>
                        </div>
                    </article>
                </div>
            </section>

            {{-- ==================== INTERACTIVE SIGNATURE (STRICTLY NO HUMAN PHOTOS - LAW 8) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_signature mayhen_invitation_reveal"
                     id="mayhen_section_signature">
                <div class="mayhen_invitation_section_bg"></div>

                <div class="mayhen_invitation_sig_header">
                    <i class="mayhen_invitation_sig_twig mayhen_invitation_sig_twig--left" aria-hidden="true"></i>
                    <div>
                        <span class="mayhen_invitation_sig_en">Signature</span>
                        <h2 class="mayhen_invitation_sig_vi">Lưu Bút</h2>
                    </div>
                    <i class="mayhen_invitation_sig_twig mayhen_invitation_sig_twig--right" aria-hidden="true"></i>
                </div>

                <p class="mayhen_invitation_sig_desc">
                    Hãy gửi gắm chữ ký lưu niệm của bạn trên trang sổ vàng của <span data-sync-target="couple_names">{{ $coupleNames }}</span> nhé!
                </p>

                <div class="mayhen_invitation_sig_board">
                    {{-- Law #8: Pure botanical SVG emblem — ZERO couple/human photos --}}
                    <div class="mayhen_invitation_sig_emblem" aria-hidden="true">
                        <svg class="mayhen_invitation_sig_heart_svg" viewBox="0 0 64 56" fill="none">
                            <path d="M32 51C32 51 6 35.5 6 17.5C6 9.5 12.4 4 19.8 4C24.7 4 29.2 6.6 32 10.7C34.8 6.6 39.3 4 44.2 4C51.6 4 58 9.5 58 17.5C58 35.5 32 51 32 51Z"
                                  stroke="currentColor" stroke-width="1.8" fill="rgba(97,135,104,0.12)" />
                            <path d="M22 25C25 22 29 22 32 25C35 22 39 22 42 25" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                        </svg>
                        <div class="mayhen_invitation_sig_couple" data-sync-target="couple_names">{{ $coupleNames }}</div>
                    </div>

                    <div class="mayhen_invitation_sig_wall" id="mayhenSignatureWall">
                        @forelse($savedSignatures as $sig)
                            <div class="mayhen_invitation_sig_card">
                                <img src="{{ $sig->signature_url }}" alt="Chữ ký của {{ $sig->guest_name }}" loading="lazy">
                                <span class="mayhen_invitation_sig_card_name">{{ $sig->guest_name }}</span>
                            </div>
                        @empty
                            <div class="mayhen_invitation_sig_empty">Chưa có chữ ký nào — hãy là người đầu tiên ký tên lưu niệm!</div>
                        @endforelse
                    </div>

                    <div class="mayhen_invitation_sig_pad_panel">
                        <div class="mayhen_invitation_sig_row">
                            <input type="text"
                                   id="mayhenSignerName"
                                   class="mayhen_invitation_sig_input"
                                   placeholder="Nhập tên của bạn trước khi ký..."
                                   value="{{ $guestNameRaw }}"
                                   maxlength="100">
                        </div>

                        <div class="mayhen_invitation_sig_canvas_box">
                            <canvas id="mayhenSignatureCanvas"
                                    class="mayhen_invitation_signature_canvas"
                                    width="540"
                                    height="170"
                                    aria-label="Khung vẽ chữ ký điện tử"></canvas>
                            <span class="mayhen_invitation_sig_placeholder" id="mayhenSigPlaceholder">Chạm hoặc kéo tay để ký tên tại đây</span>
                        </div>

                        <div class="mayhen_invitation_sig_tools">
                            <div class="mayhen_invitation_sig_colors" role="group" aria-label="Chọn màu mực">
                                <button type="button" class="mayhen_invitation_sig_color_dot is-active" data-pen-color="#45664B" style="background:#45664B" aria-label="Mực xanh rêu"></button>
                                <button type="button" class="mayhen_invitation_sig_color_dot" data-pen-color="#1E293B" style="background:#1E293B" aria-label="Mực đen than"></button>
                                <button type="button" class="mayhen_invitation_sig_color_dot" data-pen-color="#8B6914" style="background:#8B6914" aria-label="Mực vàng đồng"></button>
                            </div>
                            <div class="mayhen_invitation_sig_row">
                                <button type="button" class="mayhen_invitation_sig_btn" id="mayhenSigClearBtn">Xóa ký lại</button>
                                <button type="button" class="mayhen_invitation_sig_btn mayhen_invitation_sig_btn--primary" id="mayhenSigSaveBtn">Lưu chữ ký</button>
                            </div>
                        </div>
                        <div class="mayhen_invitation_sig_status" id="mayhenSigStatus" role="status" aria-live="polite"></div>
                    </div>
                </div>
            </section>

            {{-- ==================== SECTION 7: ALBUM (3D COVERFLOW LIGHT GALLERY) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_gallery mayhen_invitation_reveal"
                     id="mayhen_section_album"
                     data-mayhen-section="album">
                <div class="mayhen_invitation_section_bg"></div>

                <div class="mayhen_invitation_gallery_decor" aria-hidden="true">
                    <i class="mayhen_invitation_hoa mayhen_invitation_gallery_w1"></i>
                    <i class="mayhen_invitation_hoa mayhen_invitation_gallery_w2"></i>
                    <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_gallery_r1"></i>
                    <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_gallery_r2"></i>
                </div>

                <div class="mayhen_invitation_gallery_head">
                    <h2 class="mayhen_invitation_gallery_title">KHOẢNH KHẮC NGỌT NGÀO</h2>
                    <p class="mayhen_invitation_gallery_desc">Chạm vào bức ảnh để xem trọn vẹn album ngày chung đôi</p>
                </div>

                <div class="mayhen_invitation_coverflow_wrap">
                    <div class="mayhen_invitation_coverflow" id="mayhenCoverflowStage">
                        @foreach($galleryPhotos as $gIdx => $gUrl)
                            <div class="mayhen_invitation_cf_item" data-cf-index="{{ $gIdx }}" data-lightbox-src="{{ $gUrl }}">
                                <img src="{{ $gUrl }}"
                                     alt="Ảnh cưới {{ $gIdx + 1 }}"
                                     data-sync-target="gallery.{{ $gIdx }}"
                                     loading="eager"
                                     width="300"
                                     height="450">
                            </div>
                        @endforeach

                        <button type="button" class="mayhen_invitation_cf_nav mayhen_invitation_cf_nav--prev" id="mayhenCfPrev" aria-label="Ảnh trước">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6" /></svg>
                        </button>
                        <button type="button" class="mayhen_invitation_cf_nav mayhen_invitation_cf_nav--next" id="mayhenCfNext" aria-label="Ảnh tiếp theo">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 18l6-6-6-6" /></svg>
                        </button>
                    </div>

                    <div class="mayhen_invitation_cf_dots" id="mayhenCfDots" role="tablist" aria-label="Chọn ảnh album">
                        @foreach($galleryPhotos as $gIdx => $gUrl)
                            <button type="button"
                                    class="mayhen_invitation_cf_dot {{ $gIdx === 0 ? 'is-active' : '' }}"
                                    data-cf-dot="{{ $gIdx }}"
                                    aria-label="Ảnh {{ $gIdx + 1 }}"></button>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ==================== INTERACTIVE RSVP (STRICTLY NO HUMAN PHOTOS - LAW 8) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_rsvp mayhen_invitation_reveal"
                     id="mayhen_section_rsvp">
                <div class="mayhen_invitation_section_bg"></div>

                <div class="mayhen_invitation_rsvp_card">
                    <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_rsvp_rose" aria-hidden="true"></i>
                    <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_rsvp_twig" aria-hidden="true"></i>

                    {{-- Law #8: Pure botanical line divider — ZERO couple/human photos --}}
                    <div class="mayhen_invitation_rsvp_botanical_top" aria-hidden="true"></div>
                    <h2 class="mayhen_invitation_rsvp_title">XÁC NHẬN THAM DỰ</h2>
                    <p class="mayhen_invitation_rsvp_desc">
                        Chúng tôi háo hức mong chờ ngày trọng đại này. Xin vui lòng xác nhận sự tham dự của bạn để gia đình chuẩn bị đón tiếp chu đáo nhất!
                    </p>

                    <form class="mayhen_invitation_rsvp_form" id="mayhenRsvpForm" novalidate>
                        @csrf
                        <div class="mayhen_invitation_form_field">
                            <label for="mayhenRsvpName" class="mayhen_invitation_form_label">Tên của bạn *</label>
                            <input type="text"
                                   id="mayhenRsvpName"
                                   name="guest_name"
                                   class="mayhen_invitation_form_input"
                                   placeholder="Nhập tên khách mời..."
                                   value="{{ $guestNameRaw }}"
                                   required>
                        </div>

                        <div class="mayhen_invitation_form_field">
                            <span class="mayhen_invitation_form_label">Bạn sẽ tham dự chứ?</span>
                            <div class="mayhen_invitation_rsvp_pills" id="mayhenRsvpPills">
                                <label class="mayhen_invitation_rsvp_pill is-active">
                                    <input type="radio" name="attending_status" value="attending" checked>
                                    <span>Sẽ tham dự</span>
                                </label>
                                <label class="mayhen_invitation_rsvp_pill">
                                    <input type="radio" name="attending_status" value="declined">
                                    <span>Tiếc quá</span>
                                </label>
                                <label class="mayhen_invitation_rsvp_pill">
                                    <input type="radio" name="attending_status" value="uncertain">
                                    <span>Có thể</span>
                                </label>
                            </div>
                        </div>

                        <div class="mayhen_invitation_form_row">
                            <div class="mayhen_invitation_form_field">
                                <label for="mayhenRsvpAdults" class="mayhen_invitation_form_label">Số người lớn</label>
                                <select id="mayhenRsvpAdults" name="adults_count" class="mayhen_invitation_form_select">
                                    <option value="1">1 Người</option>
                                    <option value="2">2 Người</option>
                                    <option value="3">3 Người</option>
                                    <option value="4">4 Người</option>
                                    <option value="5">5 Người</option>
                                </select>
                            </div>
                            <div class="mayhen_invitation_form_field">
                                <label for="mayhenRsvpChildren" class="mayhen_invitation_form_label">Số trẻ em</label>
                                <select id="mayhenRsvpChildren" name="children_count" class="mayhen_invitation_form_select">
                                    <option value="0">0 Trẻ em</option>
                                    <option value="1">1 Trẻ em</option>
                                    <option value="2">2 Trẻ em</option>
                                    <option value="3">3 Trẻ em</option>
                                    <option value="4">4 Trẻ em</option>
                                </select>
                            </div>
                        </div>

                        <div class="mayhen_invitation_form_field">
                            <label for="mayhenRsvpNote" class="mayhen_invitation_form_label">Lời nhắn gửi Dâu Rể (tuỳ chọn)</label>
                            <textarea id="mayhenRsvpNote"
                                      name="wishes"
                                      class="mayhen_invitation_form_textarea"
                                      placeholder="Ghi chú thêm về chế độ ăn hoặc lời chúc..."
                                      maxlength="1000"></textarea>
                        </div>

                        <button type="submit" class="mayhen_invitation_submit_btn" id="mayhenRsvpSubmitBtn">
                            Xác Nhận
                        </button>
                        <div class="mayhen_invitation_form_status" id="mayhenRsvpStatus" role="status" aria-live="polite"></div>
                    </form>
                </div>
            </section>

            {{-- ==================== INTERACTIVE WISHES (LỜI CHÚC PHÚC) ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_section--sage mayhen_invitation_wishes mayhen_invitation_reveal"
                     id="mayhen_section_wishes">
                <div class="mayhen_invitation_section_bg"></div>

                <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_wishes_fern_l" aria-hidden="true"></i>
                <i class="mayhen_invitation_hoa mayhen_invitation_hoa--white mayhen_invitation_sway mayhen_invitation_wishes_fern_r" aria-hidden="true"></i>
                <i class="mayhen_invitation_hoa mayhen_invitation_wishes_wreath" aria-hidden="true"></i>

                <h2 class="mayhen_invitation_wishes_title">LỜI CHÚC PHÚC</h2>

                <div class="mayhen_invitation_wishes_grid">
                    <div class="mayhen_invitation_wishes_box_wrap">
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_wishes_box_rose" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_wishes_box_twig" aria-hidden="true"></i>
                        <i class="mayhen_invitation_hoa mayhen_invitation_wishes_box_daisy" aria-hidden="true"></i>

                        <div class="mayhen_invitation_wishes_list" id="mayhenWishesList">
                            @forelse($wishesList as $wish)
                                <div class="mayhen_invitation_wish_item">
                                    <div class="mayhen_invitation_wish_name">{{ $wish->guest_name ?? 'Khách mời' }}</div>
                                    <div class="mayhen_invitation_wish_msg">{{ $wish->message }}</div>
                                    @if(!empty($wish->reply))
                                        <div class="mayhen_invitation_wish_reply">
                                            <div class="mayhen_invitation_wish_reply_title">Lời cảm ơn từ {{ $coupleNames }}:</div>
                                            <div class="mayhen_invitation_wish_reply_text">{{ $wish->reply }}</div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="mayhen_invitation_wish_empty" id="mayhenWishEmpty">
                                    Chưa có lời chúc nào — hãy gửi lời chúc phúc đầu tiên tới {{ $coupleNames }}!
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mayhen_invitation_wishes_form_wrap">
                        <form id="mayhenWishForm" novalidate>
                            @csrf
                            <div class="mayhen_invitation_form_field">
                                <label for="mayhenWishName" class="mayhen_invitation_form_label">Tên của bạn *</label>
                                <input type="text"
                                       id="mayhenWishName"
                                       name="name"
                                       class="mayhen_invitation_form_input"
                                       placeholder="Nhập họ tên..."
                                       value="{{ $guestNameRaw }}"
                                       required>
                            </div>

                            <div class="mayhen_invitation_form_field">
                                <label for="mayhenWishPreset" class="mayhen_invitation_form_label">Chọn nhanh lời chúc gợi ý</label>
                                <select id="mayhenWishPreset" class="mayhen_invitation_form_select">
                                    <option value="">-- Tự soạn lời chúc riêng --</option>
                                    <option value="Chúc hai bạn trăm năm hạnh phúc, đầu bạc răng long và luôn giữ mãi nụ cười ngày chung đôi!">Trăm năm hạnh phúc, đầu bạc răng long!</option>
                                    <option value="Chúc mừng ngày trọng đại! Mong hai bạn có thật nhiều niềm vui và hạnh phúc bên tổ ấm mới.">Chúc mừng hạnh phúc tổ ấm mới!</option>
                                    <option value="Chúc đôi uyên ương mãi luôn yêu thương, thấu hiểu và gắn bó trọn đời bên nhau.">Yêu thương &amp; gắn bó trọn đời!</option>
                                </select>
                            </div>

                            <div class="mayhen_invitation_form_field">
                                <label for="mayhenWishMessage" class="mayhen_invitation_form_label">Lời chúc gửi cô dâu chú rể *</label>
                                <textarea id="mayhenWishMessage"
                                          name="message"
                                          class="mayhen_invitation_form_textarea"
                                          placeholder="Viết những lời chúc tốt đẹp nhất..."
                                          maxlength="1000"
                                          required></textarea>
                                <div class="mayhen_invitation_char_count"><span id="mayhenWishCount">0</span> / 1000</div>
                            </div>

                            <button type="submit" class="mayhen_invitation_submit_btn" id="mayhenWishSubmitBtn">
                                Gửi lời chúc
                            </button>
                            <div class="mayhen_invitation_form_status" id="mayhenWishStatus" role="status" aria-live="polite"></div>
                        </form>
                    </div>
                </div>
            </section>

            {{-- ==================== SECTION 8: WEDDING GIFT & VIETQR (3D GIFT BOX) ==================== --}}
            @if(!$isCuratedSeven)
                <section class="mayhen_invitation_section mayhen_invitation_gift mayhen_invitation_reveal @if($isEmbed) is-gift-unlocked @endif"
                         id="mayhen_section_gift"
                         data-mayhen-section="gift">
                    <div class="mayhen_invitation_section_bg"></div>

                    <div class="mayhen_invitation_gift_header">
                        <span class="mayhen_invitation_gift_script">Share</span>
                        <span class="mayhen_invitation_gift_mid">THE</span>
                        <span class="mayhen_invitation_gift_script">Love</span>
                    </div>

                    <p class="mayhen_invitation_gift_desc">
                        Thành ý chúc phúc và quà mừng của Quý khách xin được trân trọng ghi nhận qua số tài khoản / mã QR dưới đây.
                    </p>

                    <button type="button"
                            class="mayhen_invitation_gift_box"
                            id="mayhenGiftBoxBtn"
                            aria-label="Mở hộp quà mừng cưới">
                        <span class="mayhen_invitation_gift_shadow" aria-hidden="true"></span>
                        <span class="mayhen_invitation_gift_body" aria-hidden="true"></span>
                        <span class="mayhen_invitation_gift_lid" aria-hidden="true"></span>
                        <span class="mayhen_invitation_gift_bow" aria-hidden="true">
                            <i></i><i></i><b></b><u></u><u></u>
                        </span>
                    </button>
                    <p class="mayhen_invitation_gift_hint">Chạm vào hộp quà để mở mã QR mừng cưới</p>

                    <div class="mayhen_invitation_gift_panel" id="mayhenGiftPanel">
                        <div class="mayhen_invitation_qr_wrapper">
                            <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_qr_decor_tl" aria-hidden="true"></i>
                            <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_qr_decor_br" aria-hidden="true"></i>

                            <div class="mayhen_invitation_qr_frame">
                                <div class="mayhen_invitation_qr_inner">
                                    <div class="mayhen_invitation_qr_grid">
                                        <div class="mayhen_invitation_qr_card">
                                            <div class="mayhen_invitation_qr_img_box" data-lightbox-src="{{ $qrGroomUrl }}">
                                                <img src="{{ $qrGroomUrl }}"
                                                     alt="Mã QR Chú rể {{ $groomName }}"
                                                     class="mayhen_invitation_qr_img"
                                                     data-sync-target="qr_groom_image"
                                                     width="240"
                                                     height="240">
                                            </div>
                                            <p class="mayhen_invitation_qr_role" data-sync-target="groom_name">{{ $groomName }}</p>
                                            <p class="mayhen_invitation_qr_bank" data-sync-target="groom_bank_name">{{ $groomBankName }}</p>
                                            <p class="mayhen_invitation_qr_stk" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</p>
                                            <p class="mayhen_invitation_qr_bank" data-sync-target="groom_account_name">{{ $groomAccountName }}</p>
                                            <button type="button" class="mayhen_invitation_qr_copy_btn" data-copy-stk="{{ $groomBankAccount }}">Sao chép STK</button>
                                        </div>

                                        <div class="mayhen_invitation_qr_card">
                                            <div class="mayhen_invitation_qr_img_box" data-lightbox-src="{{ $qrBrideUrl }}">
                                                <img src="{{ $qrBrideUrl }}"
                                                     alt="Mã QR Cô dâu {{ $brideName }}"
                                                     class="mayhen_invitation_qr_img"
                                                     data-sync-target="qr_bride_image"
                                                     width="240"
                                                     height="240">
                                            </div>
                                            <p class="mayhen_invitation_qr_role" data-sync-target="bride_name">{{ $brideName }}</p>
                                            <p class="mayhen_invitation_qr_bank" data-sync-target="bride_bank_name">{{ $brideBankName }}</p>
                                            <p class="mayhen_invitation_qr_stk" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</p>
                                            <p class="mayhen_invitation_qr_bank" data-sync-target="bride_account_name">{{ $brideAccountName }}</p>
                                            <button type="button" class="mayhen_invitation_qr_copy_btn" data-copy-stk="{{ $brideBankAccount }}">Sao chép STK</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            {{-- ==================== SECTION 4B: GOOGLE MAP LOCATION ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_section--sage mayhen_invitation_maps mayhen_invitation_reveal"
                     id="mayhen_section_map">
                <div class="mayhen_invitation_section_bg"></div>

                <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_maps_twig" data-bloom="xoe-t" aria-hidden="true"></i>

                <h2 class="mayhen_invitation_maps_title">VỊ TRÍ</h2>
                <i class="mayhen_invitation_divider mayhen_invitation_divider--down mayhen_invitation_divider--white" aria-hidden="true"></i>

                <div class="mayhen_invitation_map_block">
                    <div class="mayhen_invitation_map_ribbon">Địa chỉ tiệc cưới</div>

                    <div class="mayhen_invitation_map_card">
                        <div class="mayhen_invitation_map_box">
                            <iframe class="mayhen_invitation_map_iframe"
                                    id="mayhenMapIframe"
                                    src="{{ $mapEmbedUrl }}"
                                    loading="lazy"
                                    allowfullscreen
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="Bản đồ chỉ đường nơi tổ chức"></iframe>
                            <div class="mayhen_invitation_map_sonar" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z" /></svg>
                            </div>
                        </div>

                        <p class="mayhen_invitation_map_address" data-sync-target="event_party_address">{{ $eventPartyAddress }}</p>
                        <a href="{{ $partyMapUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="mayhen_invitation_map_direct_btn"
                           data-sync-target="event_party_map_link">
                            Mở Google Maps chỉ đường
                        </a>
                    </div>
                </div>
            </section>

            {{-- ==================== SECTION 9: DRESSCODE (WOVEN FABRIC SWATCHES) ==================== --}}
            @if(!$isCuratedSeven)
                <section class="mayhen_invitation_section mayhen_invitation_dresscode mayhen_invitation_reveal"
                         id="mayhen_section_dresscode"
                         data-mayhen-section="dresscode">
                    <div class="mayhen_invitation_section_bg"></div>

                    <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_dc_f1" data-bloom="xoe-t" aria-hidden="true"></i>
                    <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_dc_f2" data-bloom="xoe-p" aria-hidden="true"></i>

                    <h2 class="mayhen_invitation_dc_title mayhen_invitation_script_text">Dresscode</h2>
                    <p class="mayhen_invitation_dc_desc" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>

                    <div class="mayhen_invitation_dc_card">
                        <div class="mayhen_invitation_dc_list mayhen_invitation_dresscode_swatches" id="mayhenDresscodeSwatches" data-sync-target="dresscode_colors">
                            @foreach($dresscodeHexList as $dIdx => $hexColor)
                                @php
                                    $safeHex = preg_match('/^#[0-9A-Fa-f]{3,8}$/', $hexColor) ? $hexColor : '#618768';
                                    $label = $dresscodeColorNames[$dIdx] ?? $safeHex;
                                    $delay = number_format(0.15 + $dIdx * 0.12, 2);
                                @endphp
                                <div class="mayhen_invitation_dc_swatch" style="--swatch-hex: {{ $safeHex }}; --dc-delay: {{ $delay }}s;">
                                    <div class="mayhen_invitation_dc_fabric"></div>
                                    <span class="mayhen_invitation_dc_label">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            {{-- ==================== SECTION 10: THANK YOU ARCH ENDING ==================== --}}
            <section class="mayhen_invitation_section mayhen_invitation_ending mayhen_invitation_interactive_ending mayhen_invitation_reveal"
                     id="mayhen_section_ending"
                     data-mayhen-section="ending">
                <div class="mayhen_invitation_section_bg"></div>

                <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_ending_ru" data-bloom="xoe-t" aria-hidden="true"></i>
                <i class="mayhen_invitation_hoa mayhen_invitation_sway mayhen_invitation_bloom mayhen_invitation_ending_dai" data-bloom="xoe-p" aria-hidden="true"></i>

                <div class="mayhen_invitation_ending_arch_wrap">
                    <div class="mayhen_invitation_ending_arch_outer">
                        <div class="mayhen_invitation_ending_arch_inner">
                            <div class="mayhen_invitation_ending_photo_box">
                                <img src="{{ $footerPhoto }}"
                                     alt="Ảnh kết thiệp {{ $coupleNames }}"
                                     class="mayhen_invitation_ending_photo"
                                     data-sync-target="footer_photo_url"
                                     width="386"
                                     height="538">
                            </div>
                        </div>
                    </div>
                    <h2 class="mayhen_invitation_ending_thank mayhen_invitation_script_text" data-sync-target="thank_you_title">{{ $thankYouTitle }}</h2>
                </div>

                <p class="mayhen_invitation_ending_quote" data-sync-target="thank_you_quote">{{ $thankYouQuote }}</p>

                <div class="mayhen_invitation_ending_twigs" aria-hidden="true">
                    <i class="is-left mayhen_invitation_sway"></i>
                    <i class="is-right mayhen_invitation_sway"></i>
                </div>

                <p class="mayhen_invitation_ending_names mayhen_invitation_script_text mayhen_invitation_ending_couple_name" data-sync-target="couple_names">{{ $coupleNames }}</p>
            </section>

        </main>
    </div>

    {{-- Bottom space preserved cleanly for global MayHen floating locale/accessibility pills --}}

    {{-- Fullscreen Album & QR Lightbox --}}
    <div class="mayhen_invitation_lightbox" id="mayhenLightbox" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="mayhen_invitation_lb_top">
            <span id="mayhenLbCounter">1 / 10</span>
            <button type="button" class="mayhen_invitation_lb_close" id="mayhenLbClose" aria-label="Đóng xem ảnh">&times;</button>
        </div>
        <div class="mayhen_invitation_lb_stage">
            <button type="button" class="mayhen_invitation_lb_arrow mayhen_invitation_lb_arrow--prev" id="mayhenLbPrev" aria-label="Ảnh trước">&#8249;</button>
            <img src="{{ $galleryPhotos[0] }}" alt="Xem chi tiết ảnh" class="mayhen_invitation_lb_img" id="mayhenLbImg">
            <button type="button" class="mayhen_invitation_lb_arrow mayhen_invitation_lb_arrow--next" id="mayhenLbNext" aria-label="Ảnh tiếp">&#8250;</button>
        </div>
        <p class="mayhen_invitation_lb_caption" id="mayhenLbCaption">{{ $coupleNames }}</p>
    </div>

    <div class="mayhen_invitation_toast" id="mayhenToast" role="status" aria-live="polite"></div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ $assetBase }}/script.js"></script>
@endpush
