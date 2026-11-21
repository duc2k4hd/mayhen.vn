@extends('client.layouts.invitation')

@php
    $slug = 'tieng-hat-trong-le-duong';
    $assetBase = "/mau-thiep/{$slug}/assets";
    $extra = is_array($wedding->extra_data ?? null) ? $wedding->extra_data : [];
    $isEmbed = request()->boolean('embed') || request()->boolean('preview') || !empty($isStudioPreview);
    $isCuratedSeven = !empty($curatedSevenMode);
    $autoOpened = $isEmbed;

    $resolveAsset = static function (?string $url, string $fallback) use ($assetBase): string {
        $candidate = trim((string) ($url ?? ''));
        if ($candidate === '') {
            return $fallback;
        }
        if (
            str_starts_with($candidate, 'http://')
            || str_starts_with($candidate, 'https://')
            || str_starts_with($candidate, '/storage/')
            || str_starts_with($candidate, '/mau-thiep/')
            || str_starts_with($candidate, 'data:')
        ) {
            return $candidate;
        }

        return $assetBase . '/' . ltrim($candidate, '/');
    };

    $groomName = trim((string) ($wedding->groom_name ?? '')) ?: 'Quang Huy';
    $brideName = trim((string) ($wedding->bride_name ?? '')) ?: 'Bảo Ngọc';
    $ceremonyType = trim((string) ($extra['ceremony_type'] ?? '')) ?: 'Lễ Thành Hôn';
    $slogan = trim((string) ($extra['slogan'] ?? '')) ?: 'Save the date';
    $weddingLunar = trim((string) ($extra['wedding_lunar_date'] ?? '')) ?: 'Nhằm ngày 11 tháng 11 năm Bính Ngọ';

    $rawDate = trim((string) ($wedding->wedding_date ?? '')) ?: '2026-12-20';
    try {
        $carbonDate = \Illuminate\Support\Carbon::parse($rawDate, 'Asia/Ho_Chi_Minh');
    } catch (\Throwable $e) {
        $carbonDate = \Illuminate\Support\Carbon::parse('2026-12-20', 'Asia/Ho_Chi_Minh');
    }
    $weddingDateIso = $carbonDate->format('Y-m-d');
    $dayNum = $carbonDate->format('d');
    $monthNum = $carbonDate->format('m');
    $yearNum = $carbonDate->format('Y');
    $formattedDateDots = "{$dayNum} · {$monthNum} · {$yearNum}";
    $formattedDateShort = "{$dayNum}.{$monthNum}.{$yearNum}";
    $weekdayMap = [
        0 => 'Chủ Nhật',
        1 => 'Thứ Hai',
        2 => 'Thứ Ba',
        3 => 'Thứ Tư',
        4 => 'Thứ Năm',
        5 => 'Thứ Sáu',
        6 => 'Thứ Bảy',
    ];
    $dayOfWeek = $weekdayMap[(int) $carbonDate->dayOfWeek] ?? 'Chủ Nhật';

    $partyTime = trim((string) ($extra['party_time'] ?? '')) ?: '11:00';
    $targetTime = trim((string) ($extra['event_target_time'] ?? '')) ?: $partyTime;
    $targetTimeClean = preg_match('/^\d{2}:\d{2}/', $targetTime, $tm) ? $tm[0] : '11:00';
    $targetIso = "{$weddingDateIso}T{$targetTimeClean}:00+07:00";

    $coverPhoto = $resolveAsset($wedding->cover_photo_url ?? null, "{$assetBase}/images/hero-couple.webp");
    $openingSealPhoto = $resolveAsset($extra['opening_seal_photo_url'] ?? ($wedding->cover_photo_url ?? null), "{$assetBase}/images/opening-seal.webp");
    $saveDatePhoto = $resolveAsset($extra['save_date_photo_url'] ?? null, "{$assetBase}/images/save-date.webp");
    $inviteBannerPhoto = $resolveAsset($extra['invite_banner_url'] ?? null, "{$assetBase}/images/invite-banner.webp");
    $musicUrl = $resolveAsset($extra['music_url'] ?? null, "{$assetBase}/wedding-song.mp3");

    $storyTitle = trim((string) ($extra['story_title'] ?? '')) ?: 'Trúc Đào';
    $storySubtitle = trim((string) ($extra['story_subtitle'] ?? '')) ?: 'Mùa thu 2019 · Lần đầu gặp gỡ';
    $coupleQuote = trim((string) ($extra['couple_quote'] ?? '')) ?: 'Trăm năm tình viên mãn, bạc đầu nghĩa phu thê';
    $storyContent = trim((string) ($extra['story_content'] ?? '')) ?: 'Một chiều thu bên dòng sông Hương, hai người xa lạ tình cờ trú chung một mái hiên. Cơn mưa ngang qua, để lại một lời chào và một ánh nhìn.';

    $groomFather = trim((string) ($extra['groom_father'] ?? '')) ?: 'Ông Nguyễn Văn Hùng';
    $groomMother = trim((string) ($extra['groom_mother'] ?? '')) ?: 'Bà Lê Thị Thu';
    $groomParentsAddr = trim((string) ($extra['groom_parents_address'] ?? '')) ?: 'Phú Xuân, Huế';
    $brideFather = trim((string) ($extra['bride_father'] ?? '')) ?: 'Ông Trần Quốc Bảo';
    $brideMother = trim((string) ($extra['bride_mother'] ?? '')) ?: 'Bà Phạm Thị Mai';
    $brideParentsAddr = trim((string) ($extra['bride_parents_address'] ?? '')) ?: 'Thuận Hoá, Huế';

    $groomPhoto = $resolveAsset($extra['groom_photo_url'] ?? null, "{$assetBase}/images/groom-portrait.webp");
    $groomBio = trim((string) ($extra['groom_bio'] ?? '')) ?: 'Nguyễn Quang Huy — Chàng trai ấm áp luôn chọn bình yên bên người mình thương.';
    $bridePhoto = $resolveAsset($extra['bride_photo_url'] ?? null, "{$assetBase}/images/bride-portrait.webp");
    $brideBio = trim((string) ($extra['bride_bio'] ?? '')) ?: 'Trần Bảo Ngọc — Cô gái dịu dàng mang nụ cười thanh xuân giữa cố đô.';

    $partyVenue = trim((string) ($extra['party_venue'] ?? '')) ?: 'Nhà hàng Đồng Khánh';
    $partyAddress = trim((string) ($extra['event_party_address'] ?? '')) ?: '86 Nguyễn Sinh Cung, thành phố Huế';
    $ceremonyTime = trim((string) ($extra['ceremony_time'] ?? '')) ?: '08:00';
    $ceremonyAddress = trim((string) ($extra['ceremony_address'] ?? '')) ?: 'Tại tư gia nhà gái · Thuận Hoá, Huế';
    $ceremonyGroomTime = trim((string) ($extra['ceremony_groom_time'] ?? '')) ?: '09:30';
    $ceremonyGroomAddress = trim((string) ($extra['ceremony_groom_address'] ?? '')) ?: 'Tại tư gia nhà trai · Phú Xuân, Huế';

    $defaultMapQuery = rawurlencode("{$partyVenue}, {$partyAddress}");
    $mapLink = trim((string) ($extra['event_party_map_link'] ?? '')) ?: "https://maps.google.com/?q={$defaultMapQuery}";
    $mapEmbedUrl = "https://maps.google.com/maps?q={$defaultMapQuery}&t=&z=16&ie=UTF8&iwloc=&output=embed";

    $gcalStartUtc = $carbonDate->copy()->setTimeFromTimeString($targetTimeClean . ':00')->setTimezone('UTC');
    $gcalEndUtc = $gcalStartUtc->copy()->addHours(4);
    $gcalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . rawurlencode("Lễ cưới {$groomName} & {$brideName}")
        . '&dates=' . $gcalStartUtc->format('Ymd\THis\Z') . '/' . $gcalEndUtc->format('Ymd\THis\Z')
        . '&details=' . rawurlencode("Trân trọng kính mời Quý khách tới dự {$ceremonyType} của {$groomName} & {$brideName}.")
        . '&location=' . rawurlencode("{$partyVenue}, {$partyAddress}");

    $defaultTimeline = [
        ['time' => '10:30', 'title' => 'Đón khách'],
        ['time' => '11:00', 'title' => 'Cử hành hôn lễ'],
        ['time' => '11:30', 'title' => 'Khai tiệc'],
        ['time' => '13:00', 'title' => 'Chụp ảnh lưu niệm'],
    ];
    $timelineItems = is_array($extra['timeline'] ?? null) && count($extra['timeline']) > 0
        ? array_values(array_slice($extra['timeline'], 0, 6))
        : $defaultTimeline;

    $rawDresscodeColors = trim((string) ($extra['dresscode_colors'] ?? '')) ?: '#F4ECDC,#7C8C6E,#F2B5C5,#EAC98E';
    $dresscodeColors = array_values(array_filter(array_map('trim', explode(',', $rawDresscodeColors)), static fn ($c) => $c !== ''));
    if (count($dresscodeColors) === 0) {
        $dresscodeColors = ['#F4ECDC', '#7C8C6E', '#F2B5C5', '#EAC98E'];
    }
    $dresscodeNote = trim((string) ($extra['dresscode_note'] ?? '')) ?: 'Rất mong Quý khách diện trang phục theo những gam màu dưới đây, để ngày vui thêm hài hoà như sắc trúc đào giữa trời xanh.';
    $colorNameMap = [
        '#F4ECDC' => 'Trắng Kem',
        '#7C8C6E' => 'Xanh Rêu',
        '#F2B5C5' => 'Hồng Đào',
        '#EAC98E' => 'Vàng Nhạt',
        '#6B7B5F' => 'Rêu Cổ Điển',
        '#E6AACB' => 'Hồng Phấn',
        '#C9A15E' => 'Vàng Đồng',
        '#FFFFFF' => 'Trắng Tinh',
        '#F5F5DC' => 'Be Nhạt',
    ];

    $defaultGallery = [];
    for ($i = 1; $i <= 10; $i++) {
        $defaultGallery[] = sprintf('%s/images/gallery-%02d.webp', $assetBase, $i);
    }
    $rawGallery = is_array($extra['gallery'] ?? null) ? array_values($extra['gallery']) : [];
    $galleryPhotos = [];
    for ($i = 0; $i < 10; $i++) {
        $galleryPhotos[] = $resolveAsset($rawGallery[$i] ?? null, $defaultGallery[$i]);
    }

    $groomBankName = trim((string) ($extra['groom_bank_name'] ?? '')) ?: 'Vietcombank';
    $groomBankAccount = trim((string) ($extra['groom_bank_account'] ?? '')) ?: '0123456789';
    $groomAccountName = trim((string) ($extra['groom_account_name'] ?? '')) ?: 'NGUYEN QUANG HUY';
    $groomQrImage = \App\Services\VietQrService::resolveQrImage(
        $extra['qr_groom_image'] ?? null,
        $groomBankName,
        $groomBankAccount,
        $groomAccountName,
        "{$assetBase}/images/qr-groom.webp"
    );

    $brideBankName = trim((string) ($extra['bride_bank_name'] ?? '')) ?: 'Techcombank';
    $brideBankAccount = trim((string) ($extra['bride_bank_account'] ?? '')) ?: '190355558888';
    $brideAccountName = trim((string) ($extra['bride_account_name'] ?? '')) ?: 'TRAN BAO NGOC';
    $brideQrImage = \App\Services\VietQrService::resolveQrImage(
        $extra['qr_bride_image'] ?? null,
        $brideBankName,
        $brideBankAccount,
        $brideAccountName,
        "{$assetBase}/images/qr-bride.webp"
    );

    $thankYouTitle = trim((string) ($extra['thank_you_title'] ?? '')) ?: 'Thank You';
    $thankYouQuote = trim((string) ($extra['thank_you_quote'] ?? '')) ?: 'Sự hiện diện của bạn là niềm vinh hạnh cho gia đình chúng mình. Hẹn gặp bạn trong ngày vui!';
    $footerPhoto = $resolveAsset($extra['footer_photo_url'] ?? null, "{$assetBase}/images/ending-photo.webp");

    $guestName = trim((string) request()->query('guest', request()->query('khach', ''))) ?: 'Quý Khách';
    $wishesList = $wishes ?? ($wedding->wishes ?? collect());

    $weddingSlug = (string) ($wedding->slug ?? '');
    $weddingId = $wedding->id ?? null;
    $rsvpUrl = ($weddingSlug !== '' && \Illuminate\Support\Facades\Route::has('rsvp.submit'))
        ? route('rsvp.submit', ['slug' => $weddingSlug])
        : '';
    $wishUrl = ($weddingSlug !== '' && \Illuminate\Support\Facades\Route::has('invitation.wish.store'))
        ? route('invitation.wish.store', ['slug' => $weddingSlug])
        : '';
    $signatureUrl = ($weddingId && \Illuminate\Support\Facades\Route::has('invitation.signature.save'))
        ? route('invitation.signature.save', ['id' => $weddingId])
        : '';

    $extractInitial = static function (string $name, string $fallback): string {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $last = end($parts);
        $char = is_string($last) && $last !== '' ? mb_substr($last, 0, 1, 'UTF-8') : $fallback;

        return mb_strtoupper($char, 'UTF-8');
    };
    $groomInitial = $extractInitial($groomName, 'H');
    $brideInitial = $extractInitial($brideName, 'N');

    $cssFile = resource_path("views/client/invitations/{$slug}/styles.css");
    $jsFile = resource_path("views/client/invitations/{$slug}/script.js");
    $cssVer = file_exists($cssFile) ? filemtime($cssFile) : 1;
    $jsVer = file_exists($jsFile) ? filemtime($jsFile) : 1;
@endphp

@section('title', "Thiệp Cưới {$groomName} & {$brideName} — Tiếng Hát Trong Lễ Đường")
@section('body_class', trim('mayhen_invitation_body_td' . ($autoOpened ? '' : ' td-khoa') . ($isEmbed ? ' mayhen_embed_mode' : '')))

@push('styles')
    <link rel="stylesheet" href="{{ $assetBase }}/styles.css?v={{ $cssVer }}">
@endpush

@section('content')
<div
    id="mayhen_invitation_root"
    @class(['mayhen_invitation_root', 'is-embed' => $isEmbed, 'mayhen_embed_mode' => $isEmbed])
    data-template-slug="{{ $slug }}"
    data-target-iso="{{ $targetIso }}"
    data-wedding-date="{{ $weddingDateIso }}"
    data-party-time="{{ $partyTime }}"
    data-groom-name="{{ $groomName }}"
    data-bride-name="{{ $brideName }}"
    data-party-venue="{{ $partyVenue }}"
    data-party-address="{{ $partyAddress }}"
    data-is-embed="{{ $isEmbed ? 'true' : 'false' }}"
    data-rsvp-url="{{ $rsvpUrl }}"
    data-wish-url="{{ $wishUrl }}"
    data-signature-url="{{ $signatureUrl }}"
    data-csrf-token="{{ csrf_token() }}"
>
    {{-- 13 Inline SVG Botanical & Bamboo Symbols (Zero External SVG CDN) --}}
    <svg width="0" height="0" style="position:absolute;width:0;height:0;overflow:hidden;" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="dk-g-truc" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#5E8B3F" /><stop offset=".35" stop-color="#A9CF6E" /><stop offset=".6" stop-color="#8DB858" /><stop offset="1" stop-color="#4E7634" />
            </linearGradient>
            <linearGradient id="dk-g-la" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#6E9A4A" /><stop offset="1" stop-color="#A6CC74" />
            </linearGradient>
            <linearGradient id="dk-g-la2" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#B9C9A4" stop-opacity=".9" /><stop offset="1" stop-color="#DCE6CB" stop-opacity=".55" />
            </linearGradient>
            <symbol id="dk-la" viewBox="0 -12 100 24">
                <path d="M0 0C18-9 58-10 100 0C58 10 18 9 0 0Z" fill="url(#dk-g-la)" />
                <path d="M2 0H92" stroke="#4E7634" stroke-opacity=".45" stroke-width=".8" />
            </symbol>
            <symbol id="dk-la-nhat" viewBox="0 -12 100 24">
                <path d="M0 0C18-9 58-10 100 0C58 10 18 9 0 0Z" fill="url(#dk-g-la2)" />
            </symbol>
            <symbol id="dk-hoa" viewBox="-20 -20 40 40">
                <g fill="#F2B5C5" stroke="#D98AA5" stroke-width=".8">
                    <path d="M0 0C-8-5-9-16 0-19C9-16 8-5 0 0Z" />
                    <path d="M0 0C-8-5-9-16 0-19C9-16 8-5 0 0Z" transform="rotate(72)" />
                    <path d="M0 0C-8-5-9-16 0-19C9-16 8-5 0 0Z" transform="rotate(144)" />
                    <path d="M0 0C-8-5-9-16 0-19C9-16 8-5 0 0Z" transform="rotate(216)" />
                    <path d="M0 0C-8-5-9-16 0-19C9-16 8-5 0 0Z" transform="rotate(288)" />
                </g>
                <circle r="4.2" fill="#C2627F" />
                <g fill="#F6D58A">
                    <circle cx="0" cy="-7" r="1.3" /><circle cx="6.6" cy="-2.2" r="1.3" />
                    <circle cx="4.1" cy="5.7" r="1.3" /><circle cx="-4.1" cy="5.7" r="1.3" /><circle cx="-6.6" cy="-2.2" r="1.3" />
                </g>
            </symbol>
            <symbol id="dk-nu" viewBox="-10 -14 20 24">
                <path d="M0 8C-6 4-6-6 0-12C6-6 6 4 0 8Z" fill="#E8A1B6" stroke="#C97A93" stroke-width=".8" />
                <path d="M-4 6L0 10L4 6" fill="#7E9B55" />
            </symbol>
            <symbol id="dk-chum-hoa" viewBox="0 0 120 90">
                <path d="M4 82C30 70 52 52 70 30C80 18 92 10 112 6" fill="none" stroke="#6B4E3A" stroke-width="2.4" stroke-linecap="round" />
                <use href="#dk-la" x="40" y="44" width="40" height="10" transform="rotate(-40 60 50)" />
                <use href="#dk-la" x="64" y="30" width="34" height="9" transform="rotate(20 80 34)" />
                <use href="#dk-hoa" x="18" y="52" width="30" height="30" />
                <use href="#dk-hoa" x="58" y="12" width="26" height="26" />
                <use href="#dk-hoa" x="88" y="-2" width="20" height="20" />
                <use href="#dk-nu" x="36" y="40" width="12" height="14" />
                <use href="#dk-nu" x="100" y="10" width="10" height="12" />
            </symbol>
            <symbol id="dk-dau-chan" viewBox="0 0 24 36">
                <ellipse cx="12" cy="22" rx="7" ry="11" fill="currentColor" />
                <g fill="currentColor">
                    <circle cx="5" cy="6" r="2.4" /><circle cx="10" cy="3.4" r="2.6" />
                    <circle cx="15.5" cy="3.6" r="2.4" /><circle cx="20" cy="6.6" r="2" />
                </g>
            </symbol>
            <linearGradient id="dk-g-vang" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#F6DFAF" /><stop offset=".45" stop-color="#D7AF6B" /><stop offset=".7" stop-color="#F3D9A4" /><stop offset="1" stop-color="#A8803F" />
            </linearGradient>
            <radialGradient id="dk-g-do" cx=".42" cy=".38" r=".7">
                <stop offset="0" stop-color="#B24653" /><stop offset=".65" stop-color="#8E2F3A" /><stop offset="1" stop-color="#6A1F28" />
            </radialGradient>
            <symbol id="dk-an" viewBox="-120 -120 240 240">
                <circle r="117" fill="url(#dk-g-vang)" />
                <circle r="110" fill="url(#dk-g-do)" />
                <circle r="103" fill="none" stroke="#E9C98E" stroke-opacity=".55" stroke-width="1" />
                <circle r="73" fill="none" stroke="#E9C98E" stroke-opacity=".8" stroke-width="1.4" />
                <circle r="68" fill="#7E2832" stroke="#E9C98E" stroke-opacity=".3" stroke-width="1" />
                <g fill="#E9C98E">
                    <circle cx="0" cy="-58" r="1.8" /><circle cx="-10" cy="-56" r="1.2" /><circle cx="10" cy="-56" r="1.2" />
                    <circle cx="0" cy="56" r="1.8" /><circle cx="-10" cy="54" r="1.2" /><circle cx="10" cy="54" r="1.2" />
                </g>
            </symbol>
            <symbol id="dk-mam" viewBox="0 0 80 90">
                <path d="M40 88V44" stroke="#8DB858" stroke-width="3.4" stroke-linecap="round" fill="none" />
                <path d="M40 54C26 54 10 44 8 24C26 22 40 34 40 54Z" fill="#A9CF6E" stroke="#5E8B3F" stroke-width="1.4" />
                <path d="M40 46C42 26 56 10 76 10C76 32 60 46 40 46Z" fill="#8DB858" stroke="#5E8B3F" stroke-width="1.4" />
                <path d="M40 54C30 50 18 40 14 28M40 46C48 34 60 22 70 16" stroke="#4E7634" stroke-width="1" fill="none" opacity=".6" />
            </symbol>
            <linearGradient id="td-g-vang" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#FFF1C9" /><stop offset=".35" stop-color="#E9C98E" /><stop offset=".65" stop-color="#C9A15E" /><stop offset="1" stop-color="#8C6420" />
            </linearGradient>
            <linearGradient id="td-g-than" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#9C7638" /><stop offset=".45" stop-color="#F6DFAF" /><stop offset="1" stop-color="#B88A44" />
            </linearGradient>
            <linearGradient id="td-g-do" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#C9404D" /><stop offset="1" stop-color="#94283A" />
            </linearGradient>
            <symbol id="td-truc-vang" viewBox="0 0 90 220">
                <rect x="30" y="0" width="9" height="220" rx="3" fill="url(#td-g-than)" />
                <rect x="54" y="46" width="7" height="174" rx="3" fill="url(#td-g-than)" />
                <g fill="#8C6420">
                    <rect x="28" y="52" width="13" height="3" rx="1.5" /><rect x="28" y="112" width="13" height="3" rx="1.5" /><rect x="28" y="170" width="13" height="3" rx="1.5" />
                    <rect x="52.5" y="90" width="10" height="2.6" rx="1.3" /><rect x="52.5" y="150" width="10" height="2.6" rx="1.3" /><rect x="52.5" y="200" width="10" height="2.6" rx="1.3" />
                </g>
                <g fill="url(#td-g-vang)">
                    <path d="M0 0C8-6 30-7 46 0C30 7 8 6 0 0Z" transform="translate(36 52) rotate(-150) scale(1)" />
                    <path d="M0 0C8-6 30-7 46 0C30 7 8 6 0 0Z" transform="translate(36 54) rotate(-118) scale(0.9)" />
                    <path d="M0 0C8-6 30-7 46 0C30 7 8 6 0 0Z" transform="translate(38 112) rotate(-30) scale(1)" />
                    <path d="M0 0C8-6 30-7 46 0C30 7 8 6 0 0Z" transform="translate(38 114) rotate(-62) scale(0.85)" />
                    <path d="M0 0C8-6 30-7 46 0C30 7 8 6 0 0Z" transform="translate(32 170) rotate(-160) scale(0.9)" />
                    <path d="M0 0C8-6 30-7 46 0C30 7 8 6 0 0Z" transform="translate(60 90) rotate(-40) scale(0.8)" />
                    <path d="M0 0C8-6 30-7 46 0C30 7 8 6 0 0Z" transform="translate(60 92) rotate(-12) scale(0.75)" />
                    <path d="M0 0C8-6 30-7 46 0C30 7 8 6 0 0Z" transform="translate(58 150) rotate(-150) scale(0.8)" />
                    <path d="M0 0C8-6 30-7 46 0C30 7 8 6 0 0Z" transform="translate(60 150) rotate(-30) scale(0.7)" />
                    <path d="M0 0C8-6 30-7 46 0C30 7 8 6 0 0Z" transform="translate(34 8) rotate(-100) scale(0.7)" />
                    <path d="M0 0C8-6 30-7 46 0C30 7 8 6 0 0Z" transform="translate(36 10) rotate(-70) scale(0.65)" />
                </g>
            </symbol>
            <linearGradient id="td-g-tre-ngang" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#C6E08F" /><stop offset=".4" stop-color="#8DB858" /><stop offset="1" stop-color="#4E7634" />
            </linearGradient>
            <linearGradient id="td-g-tre-doc" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#4E7634" /><stop offset=".4" stop-color="#A9CF6E" /><stop offset=".6" stop-color="#8DB858" /><stop offset="1" stop-color="#4E7634" />
            </linearGradient>
            <symbol id="td-ong-ngang" viewBox="0 0 400 22" preserveAspectRatio="none">
                <rect x="0" y="3" width="400" height="16" rx="8" fill="url(#td-g-tre-ngang)" />
                <g fill="#3F5F2A" opacity=".8">
                    <rect x="64" y="2" width="4" height="18" rx="2" /><rect x="166" y="2" width="4" height="18" rx="2" />
                    <rect x="268" y="2" width="4" height="18" rx="2" /><rect x="350" y="2" width="4" height="18" rx="2" />
                </g>
                <rect x="6" y="6" width="388" height="3" rx="1.5" fill="#E7F3C8" opacity=".55" />
            </symbol>
            <symbol id="td-than-doc" viewBox="0 0 24 400" preserveAspectRatio="none">
                <rect x="2" y="0" width="20" height="400" rx="6" fill="url(#td-g-tre-doc)" />
                <g fill="#3F5F2A" opacity=".75">
                    <rect x="0" y="70" width="24" height="5" rx="2.5" /><rect x="0" y="170" width="24" height="5" rx="2.5" />
                    <rect x="0" y="262" width="24" height="5" rx="2.5" /><rect x="0" y="346" width="24" height="5" rx="2.5" />
                </g>
                <rect x="7" y="0" width="3" height="400" fill="#E7F3C8" opacity=".35" />
            </symbol>
            <symbol id="td-nap-lx" viewBox="0 0 210 132">
                <path d="M0 0H210V44C196 58 178 60 166 72C154 84 140 84 128 96C120 104 114 120 105 120C96 120 90 104 82 96C70 84 56 84 44 72C32 60 14 58 0 44Z" fill="url(#td-g-do)" stroke="#E9C98E" stroke-width="2.4" />
                <path d="M6 6H204V40C191 52 175 55 163 66C151 78 137 78 125 90C118 97 112 110 105 110C98 110 92 97 85 90C73 78 59 78 47 66C35 55 19 52 6 40Z" fill="none" stroke="#E9C98E" stroke-opacity=".55" stroke-width="1" stroke-dasharray="3 3" />
            </symbol>
            <symbol id="td-no" viewBox="0 0 120 60">
                <path d="M60 30C48 8 18 2 12 14C6 26 30 40 60 30Z" fill="#A63D49" />
                <path d="M60 30C72 8 102 2 108 14C114 26 90 40 60 30Z" fill="#A63D49" />
                <path d="M60 30C50 15 26 10 20 17M60 30C70 15 94 10 100 17" fill="none" stroke="#F3D9A4" stroke-opacity=".5" stroke-width="2" stroke-linecap="round" />
                <path d="M56 32L44 58L51 55L55 61L60 34ZM64 32L76 58L69 55L65 61L60 34Z" fill="#8E2F3A" />
                <circle cx="60" cy="30" r="7.5" fill="#8E2F3A" stroke="#E9C98E" stroke-width="1.2" />
            </symbol>
        </defs>
    </svg>

    {{-- Màn Mở: Cổng Hai Cánh Cửa Xanh Rêu + Dải Lụa Đỏ + Ấn Triện Vàng --}}
    <div id="tdOpening" @class(['mayhen_invitation_opening', 'is-opened' => $autoOpened]) role="dialog" aria-label="Thiệp mời cưới" aria-hidden="{{ $autoOpened ? 'true' : 'false' }}">
        <div class="mayhen_invitation_door mayhen_invitation_door--left" aria-hidden="true">
            <div class="mayhen_invitation_door_bg"></div>
            <div class="mayhen_invitation_door_frame"></div>
            <div class="mayhen_invitation_tt mayhen_invitation_tt--la"></div>
            <div class="mayhen_invitation_door_ribbon"></div>
            <div class="mayhen_invitation_door_seal"><svg aria-hidden="true"><use href="#dk-an" /></svg></div>
        </div>
        <div class="mayhen_invitation_door mayhen_invitation_door--right" aria-hidden="true">
            <div class="mayhen_invitation_door_bg"></div>
            <div class="mayhen_invitation_door_frame"></div>
            <div class="mayhen_invitation_tt mayhen_invitation_tt--la2"></div>
            <div class="mayhen_invitation_door_ribbon"></div>
            <div class="mayhen_invitation_door_seal"><svg aria-hidden="true"><use href="#dk-an" /></svg></div>
        </div>
        <div class="mayhen_invitation_door_flash" aria-hidden="true"></div>
        <div class="mayhen_invitation_seal_photo">
            <img src="{{ $openingSealPhoto }}" alt="Ảnh ấn triện {{ $groomName }} &amp; {{ $brideName }}" data-sync-target="cover_photo_url">
        </div>
        <button type="button" class="mayhen_invitation_seal_hit" id="tdSealHit" aria-label="Mở thiệp"></button>
        <div class="mayhen_invitation_opening_content">
            <div class="mayhen_invitation_opening_top">
                <p class="mayhen_invitation_opening_kicker">Thiệp mời cưới</p>
                <p class="mayhen_invitation_opening_couple">
                    <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                </p>
                <p class="mayhen_invitation_opening_hint">Chạm vào ấn đỏ để mở cánh cửa</p>
            </div>
            <div class="mayhen_invitation_opening_bottom">
                <p class="mayhen_invitation_opening_salutation">Trân trọng kính mời</p>
                <h3 class="mayhen_invitation_opening_guest">{{ $guestName }}</h3>
                <div>
                    <button type="button" id="tdOpenBtn" class="mayhen_invitation_open_btn">Mở thiệp</button>
                </div>
            </div>
        </div>
        <div class="mayhen_invitation_burst_petals" id="mayhenBurstPetals" aria-hidden="true"></div>
    </div>

    {{-- Lớp cánh hoa trúc đào rơi nhẹ --}}
    <div class="mayhen_invitation_petals_layer" id="mayhenFallingPetals" aria-hidden="true"></div>

    {{-- Nội dung chính của thiệp --}}
    <main class="mayhen_invitation_card_wrapper card-wrapper">

        {{-- ================================================================= --}}
        {{-- SECTION 1: HERO (TRANG BÌA CHÍNH & LỜI NGỎ)                       --}}
        {{-- ================================================================= --}}
        <div data-mayhen-section="hero">
            <section class="mayhen_invitation_section_block mayhen_invitation_hero_banner">
                <div class="mayhen_invitation_mist" aria-hidden="true"></div>
                <div class="mayhen_invitation_tt mayhen_invitation_tt--la" aria-hidden="true"></div>
                <div class="mayhen_invitation_tt mayhen_invitation_tt--tre-trai" aria-hidden="true"></div>
                <div class="mayhen_invitation_tt mayhen_invitation_tt--la2" aria-hidden="true"></div>
                <div class="mayhen_invitation_tt mayhen_invitation_tt--tre" aria-hidden="true"></div>

                <p class="mayhen_invitation_hero_kicker mayhen_invitation_reveal" data-reveal="up" data-sync-target="slogan">{{ $slogan }}</p>
                <p class="mayhen_invitation_hero_ceremony mayhen_invitation_reveal" data-reveal="up" data-sync-target="ceremony_type">{{ $ceremonyType }}</p>

                <div class="mayhen_invitation_hero_couple">
                    <h1 class="mayhen_invitation_hero_name mayhen_invitation_reveal" data-reveal="left" data-sync-target="groom_name">{{ $groomName }}</h1>
                    <div class="mayhen_invitation_hero_amp mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">&amp;</div>
                    <h1 class="mayhen_invitation_hero_name mayhen_invitation_reveal" data-reveal="right" data-sync-target="bride_name">{{ $brideName }}</h1>
                </div>

                {{-- Ảnh chính bìa Hero (Tỉ lệ 3:4 khung vòm viền kem chỉ vàng & chùm hoa trúc đào) --}}
                <div class="mayhen_invitation_frame mayhen_invitation_frame--arch mayhen_invitation_hero_portrait mayhen_invitation_reveal" data-reveal="pop">
                    <div class="mayhen_invitation_frame_media" data-lightbox-src="{{ $coverPhoto }}" data-lightbox-caption="{{ $groomName }} &amp; {{ $brideName }} — {{ $formattedDateDots }}">
                        <img src="{{ $coverPhoto }}" alt="Ảnh chính thiệp cưới {{ $groomName }} &amp; {{ $brideName }}" data-sync-target="cover_photo_url" loading="eager">
                    </div>
                    <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tl" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                    <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tr" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                </div>

                <p class="mayhen_invitation_hero_date mayhen_invitation_reveal" data-reveal="up" data-sync-target="wedding_date">{{ $formattedDateDots }}</p>
                <p class="mayhen_invitation_hero_lunar mayhen_invitation_reveal" data-reveal="up" data-sync-target="wedding_lunar_date">{{ $weddingLunar }}</p>
                <div class="mayhen_invitation_hero_scroll" aria-hidden="true">Cuộn xuống</div>
            </section>

            <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

            {{-- Lời Ngỏ kèm Khung Vòm Trúc Đào --}}
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_intro_grid">
                    <div class="mayhen_invitation_frame mayhen_invitation_frame--arch mayhen_invitation_reveal" data-reveal="up">
                        <div class="mayhen_invitation_frame_media" data-lightbox-src="{{ $saveDatePhoto }}" data-lightbox-caption="Lời ngỏ — {{ $groomName }} &amp; {{ $brideName }}">
                            <img src="{{ $saveDatePhoto }}" alt="Ảnh vòm lời ngỏ {{ $groomName }} &amp; {{ $brideName }}" data-sync-target="save_date_photo_url" loading="eager">
                        </div>
                        <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tl" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                        <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tr" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                    </div>

                    <div>
                        <h2 class="mayhen_invitation_intro_quote mayhen_invitation_reveal" data-reveal="up" data-sync-target="couple_quote">{{ $coupleQuote }}</h2>
                        <p class="mayhen_invitation_prose mayhen_invitation_reveal" data-reveal="up">
                            Giữa những ngày đông se lạnh của xứ Huế, chúng mình quyết định nắm tay nhau, cùng bước tiếp một chặng đường dài.
                        </p>
                        <p class="mayhen_invitation_prose mayhen_invitation_reveal" data-reveal="up">
                            Thật vui nếu ngày ấy có bạn ở bên, chung vui và chúc phúc cho chúng mình.
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

        {{-- ================================================================= --}}
        {{-- SECTION 2: PARENTS (HAI BÊN GIA ĐÌNH & ĐÔI UYÊN ƯƠNG)             --}}
        {{-- ================================================================= --}}
        <section class="mayhen_invitation_section_block" data-mayhen-section="parents">
            <div class="mayhen_invitation_tt mayhen_invitation_tt--la2 mayhen_invitation_tt--g-tr" aria-hidden="true"></div>

            <div class="mayhen_invitation_sec_head">
                <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                    <i></i><span class="mayhen_invitation_kicker_label">Hai họ</span><i></i>
                </div>
                <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Hai Bên Gia Đình</h2>
                <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                    <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                </div>
            </div>

            <div class="mayhen_invitation_family_grid">
                <div class="mayhen_invitation_family_card mayhen_invitation_glass_card mayhen_invitation_reveal" data-reveal="left">
                    <p class="mayhen_invitation_family_side">Nhà Trai</p>
                    <p class="mayhen_invitation_family_parents">
                        <span data-sync-target="groom_father">{{ $groomFather }}</span><br>
                        <span data-sync-target="groom_mother">{{ $groomMother }}</span>
                    </p>
                    <p class="mayhen_invitation_family_addr" data-sync-target="groom_parents_address">{{ $groomParentsAddr }}</p>
                </div>

                <div class="mayhen_invitation_family_card mayhen_invitation_glass_card mayhen_invitation_reveal" data-reveal="right">
                    <p class="mayhen_invitation_family_side">Nhà Gái</p>
                    <p class="mayhen_invitation_family_parents">
                        <span data-sync-target="bride_father">{{ $brideFather }}</span><br>
                        <span data-sync-target="bride_mother">{{ $brideMother }}</span>
                    </p>
                    <p class="mayhen_invitation_family_addr" data-sync-target="bride_parents_address">{{ $brideParentsAddr }}</p>
                </div>
            </div>

            <p class="mayhen_invitation_announce_text mayhen_invitation_reveal" data-reveal="up">
                Trân trọng báo tin lễ thành hôn<br>của con chúng tôi
            </p>

            <div class="mayhen_invitation_couple_grid">
                <div class="mayhen_invitation_reveal" data-reveal="left">
                    <div class="mayhen_invitation_frame mayhen_invitation_frame--portrait">
                        <div class="mayhen_invitation_frame_media" data-lightbox-src="{{ $groomPhoto }}" data-lightbox-caption="Chú rể {{ $groomName }}">
                            <img src="{{ $groomPhoto }}" alt="Chân dung chú rể {{ $groomName }}" data-sync-target="groom_photo_url" loading="lazy">
                        </div>
                        <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tl" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                    </div>
                    <p class="mayhen_invitation_person_role">Chú Rể</p>
                    <h3 class="mayhen_invitation_person_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                    <p class="mayhen_invitation_person_bio" data-sync-target="groom_bio">{{ $groomBio }}</p>
                </div>

                <div class="mayhen_invitation_reveal" data-reveal="right">
                    <div class="mayhen_invitation_frame mayhen_invitation_frame--portrait">
                        <div class="mayhen_invitation_frame_media" data-lightbox-src="{{ $bridePhoto }}" data-lightbox-caption="Cô dâu {{ $brideName }}">
                            <img src="{{ $bridePhoto }}" alt="Chân dung cô dâu {{ $brideName }}" data-sync-target="bride_photo_url" loading="lazy">
                        </div>
                        <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tr" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                    </div>
                    <p class="mayhen_invitation_person_role">Cô Dâu</p>
                    <h3 class="mayhen_invitation_person_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                    <p class="mayhen_invitation_person_bio" data-sync-target="bride_bio">{{ $brideBio }}</p>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

        {{-- ================================================================= --}}
        {{-- SECTION 3: EVENTS (THƯ MỜI, LỄ GIA TIÊN & BẢN ĐỒ CỬA TRE 2 CÁNH) --}}
        {{-- ================================================================= --}}
        <div data-mayhen-section="events">
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_sec_head">
                    <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                        <i></i><span class="mayhen_invitation_kicker_label">Thư mời</span><i></i>
                    </div>
                    <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Trân Trọng Kính Mời</h2>
                    <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                        <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                    </div>
                </div>

                <div class="mayhen_invitation_invite_grid">
                    <div class="mayhen_invitation_reveal" data-reveal="up">
                        <div class="mayhen_invitation_frame mayhen_invitation_frame--wide">
                            <div class="mayhen_invitation_frame_media" data-lightbox-src="{{ $inviteBannerPhoto }}" data-lightbox-caption="Thư mời {{ $groomName }} &amp; {{ $brideName }}">
                                <img src="{{ $inviteBannerPhoto }}" alt="Ảnh thư mời cưới {{ $groomName }} &amp; {{ $brideName }}" data-sync-target="cover_photo_url" loading="lazy">
                            </div>
                            <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tl" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                        </div>
                        <p class="mayhen_invitation_invite_salutation">Kính mời</p>
                        <h3 class="mayhen_invitation_invite_guest">{{ $guestName }}</h3>
                        <p class="mayhen_invitation_invite_sub">tới dự bữa tiệc chung vui<br>cùng gia đình chúng tôi</p>
                    </div>

                    <div class="mayhen_invitation_time_card mayhen_invitation_glass_card mayhen_invitation_reveal" data-reveal="up">
                        <p class="mayhen_invitation_party_clock" data-sync-target="party_time">{{ $partyTime }}</p>
                        <p class="mayhen_invitation_party_clock_lbl">Khai tiệc</p>

                        <div class="mayhen_invitation_date_row">
                            <span class="mayhen_invitation_date_side" id="mayhenEventWeekday">{{ $dayOfWeek }}</span>
                            <span class="mayhen_invitation_date_big" id="mayhenEventDayNum">{{ $dayNum }}</span>
                            <span class="mayhen_invitation_date_side" id="mayhenEventMonthYear">Tháng {{ $monthNum }} · {{ $yearNum }}</span>
                        </div>
                        <p class="mayhen_invitation_lunar_sub" data-sync-target="wedding_lunar_date">({{ $weddingLunar }})</p>

                        <div class="mayhen_invitation_soft_hr" aria-hidden="true"></div>

                        <p class="mayhen_invitation_venue_kicker">Địa điểm</p>
                        <h3 class="mayhen_invitation_venue_name" data-sync-target="party_venue">{{ $partyVenue }}</h3>
                        <p class="mayhen_invitation_venue_addr" data-sync-target="event_party_address">{{ $partyAddress }}</p>
                    </div>
                </div>
            </section>

            <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

            {{-- Lễ Gia Tiên (Lễ Vu Quy & Lễ Thành Hôn) --}}
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_sec_head">
                    <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                        <i></i><span class="mayhen_invitation_kicker_label">Nghi lễ</span><i></i>
                    </div>
                    <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Lễ Gia Tiên</h2>
                    <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                        <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                    </div>
                </div>

                <div class="mayhen_invitation_ceremony_grid">
                    <div class="mayhen_invitation_ceremony_item mayhen_invitation_glass_card mayhen_invitation_reveal" data-reveal="left">
                        <div class="mayhen_invitation_ceremony_icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="14" r="5.5" /><circle cx="15" cy="14" r="5.5" /><path d="M9 4.5l1.5-2h3L15 4.5l-3 3z" />
                            </svg>
                        </div>
                        <h3 class="mayhen_invitation_ceremony_name">Lễ Vu Quy</h3>
                        <p class="mayhen_invitation_ceremony_time">
                            <span data-sync-target="ceremony_time">{{ $ceremonyTime }}</span> · {{ $dayOfWeek }} {{ $formattedDateShort }}
                        </p>
                        <p class="mayhen_invitation_ceremony_place" data-sync-target="ceremony_address">{{ $ceremonyAddress }}</p>
                    </div>

                    <div class="mayhen_invitation_ceremony_item mayhen_invitation_glass_card mayhen_invitation_reveal" data-reveal="right">
                        <div class="mayhen_invitation_ceremony_icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 21h18M5 21V10l7-6 7 6v11" /><path d="M9 21v-6h6v6" />
                            </svg>
                        </div>
                        <h3 class="mayhen_invitation_ceremony_name">Lễ Thành Hôn</h3>
                        <p class="mayhen_invitation_ceremony_time">
                            <span data-sync-target="ceremony_groom_time">{{ $ceremonyGroomTime }}</span> · {{ $dayOfWeek }} {{ $formattedDateShort }}
                        </p>
                        <p class="mayhen_invitation_ceremony_place" data-sync-target="ceremony_groom_address">{{ $ceremonyGroomAddress }}</p>
                    </div>
                </div>
            </section>

            <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

            {{-- Bản Đồ Cửa Tre 2 Cánh Tương Tác --}}
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_sec_head">
                    <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                        <i></i><span class="mayhen_invitation_kicker_label">Địa điểm</span><i></i>
                    </div>
                    <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Đường Tới Hôn Lễ</h2>
                    <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                        <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                    </div>
                </div>

                <div class="mayhen_invitation_map_frame mayhen_invitation_reveal" data-reveal="up">
                    <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tl" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>

                    <div id="tdMapDoors" @class(['mayhen_invitation_map_doors', 'is-open is-done' => $isEmbed])>
                        <div class="mayhen_invitation_map_wing mayhen_invitation_map_wing--left" aria-hidden="true">
                            <span class="mayhen_invitation_map_knob"></span>
                        </div>
                        <div class="mayhen_invitation_map_wing mayhen_invitation_map_wing--right" aria-hidden="true">
                            <span class="mayhen_invitation_map_knob"></span>
                        </div>
                        <button type="button" id="tdMapOpenBtn" class="mayhen_invitation_map_open_btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z" /><circle cx="12" cy="10" r="2.5" />
                            </svg>
                            <span>Mở cửa xem bản đồ</span>
                        </button>
                    </div>

                    <div class="mayhen_invitation_map_viewport">
                        <iframe
                            id="mayhenMapIframe"
                            src="{{ $mapEmbedUrl }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                            title="Bản đồ {{ $partyVenue }}"
                        ></iframe>
                    </div>
                </div>

                <p class="mayhen_invitation_prose mayhen_invitation_reveal" data-reveal="up">
                    <strong data-sync-target="party_venue">{{ $partyVenue }}</strong><br>
                    <span data-sync-target="event_party_address">{{ $partyAddress }}</span>
                </p>

                <a
                    id="mayhenMapExternalLink"
                    href="{{ $mapLink }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mayhen_invitation_gold_btn mayhen_invitation_reveal"
                    data-reveal="up"
                    data-sync-target="event_party_map_link"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z" /><circle cx="12" cy="10" r="2.5" />
                    </svg>
                    <span>Chỉ đường Google Maps</span>
                </a>
            </section>
        </div>

        <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

        {{-- ================================================================= --}}
        {{-- SECTION 4: STORY (CHUYỆN TÌNH YÊU "TRÚC ĐÀO")                     --}}
        {{-- ================================================================= --}}
        <section class="mayhen_invitation_section_block" data-mayhen-section="story">
            <div class="mayhen_invitation_sec_head">
                <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                    <i></i><span class="mayhen_invitation_kicker_label">Chuyện của chúng mình</span><i></i>
                </div>
                <h2 class="mayhen_invitation_sec_title mayhen_invitation_sec_title--script mayhen_invitation_reveal" data-reveal="up" data-sync-target="story_title">{{ $storyTitle }}</h2>
                <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                    <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                </div>
            </div>

            <div class="mayhen_invitation_story_list">
                <article class="mayhen_invitation_story_item mayhen_invitation_reveal" data-reveal="up">
                    <div class="mayhen_invitation_story_media_col">
                        <div class="mayhen_invitation_frame">
                            <div class="mayhen_invitation_frame_media" data-lightbox-src="{{ $assetBase }}/images/story-01.webp" data-lightbox-caption="Lần đầu gặp gỡ">
                                <img src="{{ $assetBase }}/images/story-01.webp" alt="Chặng 1: Lần đầu gặp gỡ" loading="lazy">
                            </div>
                            <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tr" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                        </div>
                    </div>
                    <div>
                        <span class="mayhen_invitation_footprint_badge" aria-hidden="true"><svg><use href="#dk-dau-chan" /></svg></span>
                        <p class="mayhen_invitation_story_time" data-sync-target="story_subtitle">{{ $storySubtitle }}</p>
                        <h3 class="mayhen_invitation_story_heading">Lần đầu gặp gỡ</h3>
                        <p class="mayhen_invitation_prose mayhen_invitation_story_desc" data-sync-target="story_content">{{ $storyContent }}</p>
                    </div>
                </article>

                <article class="mayhen_invitation_story_item mayhen_invitation_reveal" data-reveal="up">
                    <div class="mayhen_invitation_story_media_col">
                        <div class="mayhen_invitation_frame">
                            <div class="mayhen_invitation_frame_media" data-lightbox-src="{{ $assetBase }}/images/story-02.webp" data-lightbox-caption="Cùng nhau lớn lên">
                                <img src="{{ $assetBase }}/images/story-02.webp" alt="Chặng 2: Cùng nhau lớn lên" loading="lazy">
                            </div>
                            <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tl" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                        </div>
                    </div>
                    <div>
                        <span class="mayhen_invitation_footprint_badge" aria-hidden="true"><svg><use href="#dk-dau-chan" /></svg></span>
                        <p class="mayhen_invitation_story_time">Những năm tháng ấy</p>
                        <h3 class="mayhen_invitation_story_heading">Cùng nhau lớn lên</h3>
                        <p class="mayhen_invitation_prose mayhen_invitation_story_desc">
                            Những buổi đi học, những lần đi làm muộn, những chuyến xe xa nhà… Chúng mình học cách thương nhau qua từng điều nhỏ bé nhất.
                        </p>
                    </div>
                </article>

                <article class="mayhen_invitation_story_item mayhen_invitation_reveal" data-reveal="up">
                    <div class="mayhen_invitation_story_media_col">
                        <div class="mayhen_invitation_frame">
                            <div class="mayhen_invitation_frame_media" data-lightbox-src="{{ $assetBase }}/images/story-03.webp" data-lightbox-caption="Lời cầu hôn">
                                <img src="{{ $assetBase }}/images/story-03.webp" alt="Chặng 3: Lời cầu hôn" loading="lazy">
                            </div>
                            <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tr" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                        </div>
                    </div>
                    <div>
                        <span class="mayhen_invitation_footprint_badge" aria-hidden="true"><svg><use href="#dk-dau-chan" /></svg></span>
                        <p class="mayhen_invitation_story_time">Mùa đông 2025</p>
                        <h3 class="mayhen_invitation_story_heading">Lời cầu hôn</h3>
                        <p class="mayhen_invitation_prose mayhen_invitation_story_desc">
                            Dưới tán trúc xanh và những cánh đào đầu mùa, một chiếc nhẫn, một câu hỏi, và một cái gật đầu thật khẽ.
                        </p>
                    </div>
                </article>
            </div>

            <blockquote class="mayhen_invitation_story_pullquote mayhen_invitation_reveal" data-reveal="up">
                Từ những dấu chân bên nhau, một mái nhà nhỏ bắt đầu nảy mầm.
            </blockquote>

            <div class="mayhen_invitation_sprout_box mayhen_invitation_reveal" data-reveal="up">
                <svg class="mayhen_invitation_sprout_svg" aria-hidden="true"><use href="#dk-mam" /></svg>
                <div class="mayhen_invitation_sprout_soil" aria-hidden="true"></div>
                <h3 class="mayhen_invitation_sprout_title">Về chung một nhà</h3>
                <p class="mayhen_invitation_prose">
                    Và giờ đây, chúng mình muốn được cùng nhau viết tiếp câu chuyện ấy — với sự chứng kiến của những người thân yêu nhất.
                </p>
            </div>
        </section>

        <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

        {{-- ================================================================= --}}
        {{-- SECTION 5: COUNTDOWN & CALENDAR (LỊCH CƯỚI & ĐẾM NGƯỢC)           --}}
        {{-- ================================================================= --}}
        <div data-mayhen-section="countdown_calendar">
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_tt mayhen_invitation_tt--tre-trai mayhen_invitation_tt--g-bl" aria-hidden="true"></div>

                <div class="mayhen_invitation_sec_head">
                    <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                        <i></i><span class="mayhen_invitation_kicker_label">Save the date</span><i></i>
                    </div>
                    <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Lịch Cưới</h2>
                    <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                        <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                    </div>
                </div>

                <div class="mayhen_invitation_calendar_grid">
                    <div class="mayhen_invitation_calendar_card mayhen_invitation_glass_card mayhen_invitation_reveal" data-reveal="up">
                        <p class="mayhen_invitation_calendar_month" id="mayhenCalendarMonth">Tháng {{ $monthNum }} · {{ $yearNum }}</p>
                        <div class="mayhen_invitation_calendar_head" aria-hidden="true">
                            <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                        </div>
                        <div class="mayhen_invitation_calendar_body" id="mayhenCalendarBody"></div>
                    </div>

                    <div class="mayhen_invitation_calendar_text_col mayhen_invitation_reveal" data-reveal="up">
                        <p class="mayhen_invitation_prose">
                            Ngày chúng mình chính thức về chung một nhà. Hãy lưu lại ngày này và dành cho chúng mình một chút thời gian nhé!
                        </p>
                        <a
                            id="mayhenAddCalLink"
                            href="{{ $gcalUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mayhen_invitation_gold_btn"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18M12 14v4M10 16h4" />
                            </svg>
                            <span>Thêm vào lịch</span>
                        </a>
                    </div>
                </div>
            </section>

            <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_tt mayhen_invitation_tt--la mayhen_invitation_tt--g-tl" aria-hidden="true"></div>
                <div class="mayhen_invitation_tt mayhen_invitation_tt--tre mayhen_invitation_tt--g-br" aria-hidden="true"></div>

                <div class="mayhen_invitation_sec_head">
                    <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                        <i></i><span class="mayhen_invitation_kicker_label">Đếm ngược</span><i></i>
                    </div>
                    <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Tới Ngày Chung Đôi</h2>
                    <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                        <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                    </div>
                </div>

                <div class="mayhen_invitation_countdown_grid mayhen_invitation_reveal" data-reveal="pop" id="mayhenCountdownGrid" data-target-iso="{{ $targetIso }}">
                    <div class="mayhen_invitation_cd_box">
                        <span class="mayhen_invitation_cd_num mayhen_invitation_cd_day">00</span>
                        <span class="mayhen_invitation_cd_lbl">Ngày</span>
                    </div>
                    <div class="mayhen_invitation_cd_box">
                        <span class="mayhen_invitation_cd_num mayhen_invitation_cd_hour">00</span>
                        <span class="mayhen_invitation_cd_lbl">Giờ</span>
                    </div>
                    <div class="mayhen_invitation_cd_box">
                        <span class="mayhen_invitation_cd_num mayhen_invitation_cd_min">00</span>
                        <span class="mayhen_invitation_cd_lbl">Phút</span>
                    </div>
                    <div class="mayhen_invitation_cd_box">
                        <span class="mayhen_invitation_cd_num mayhen_invitation_cd_sec">00</span>
                        <span class="mayhen_invitation_cd_lbl">Giây</span>
                    </div>
                </div>

                <p class="mayhen_invitation_cd_note mayhen_invitation_reveal" data-reveal="up">
                    Từng ngày trôi qua, chúng mình càng mong được gặp bạn
                </p>
            </section>
        </div>

        <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

        {{-- ================================================================= --}}
        {{-- SECTION 6: TIMELINE (CHƯƠNG TRÌNH HÔN LỄ)                         --}}
        {{-- ================================================================= --}}
        <section class="mayhen_invitation_section_block" data-mayhen-section="timeline">
            <div class="mayhen_invitation_sec_head">
                <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                    <i></i><span class="mayhen_invitation_kicker_label">Lịch trình</span><i></i>
                </div>
                <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Chương Trình Hôn Lễ</h2>
                <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                    <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                </div>
            </div>

            <div class="mayhen_invitation_tl_card mayhen_invitation_reveal" data-reveal="up">
                @foreach($timelineItems as $idx => $item)
                    @php
                        $tlTime = is_array($item) ? (string) ($item['time'] ?? '') : '';
                        $tlTitle = is_array($item) ? (string) ($item['title'] ?? '') : '';
                    @endphp
                    <div class="mayhen_invitation_tl_row">
                        <div class="mayhen_invitation_tl_time">{{ $tlTime }}</div>
                        <div class="mayhen_invitation_tl_axis">
                            <div class="mayhen_invitation_tl_dot" aria-hidden="true">
                                @if($idx % 4 === 0)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                                @elseif($idx % 4 === 1)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="14" r="5" /><circle cx="15" cy="14" r="5" /><path d="M12 5l2-2 2 2-4 4-4-4 2-2 2 2z" /></svg>
                                @elseif($idx % 4 === 2)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 22h8M12 15v7M7 3h10l-1.5 7.5A3.6 3.6 0 0 1 12 13.5a3.6 3.6 0 0 1-3.5-3L7 3z" /></svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" /><circle cx="12" cy="13" r="4" /></svg>
                                @endif
                            </div>
                            <div class="mayhen_invitation_tl_line" aria-hidden="true"></div>
                        </div>
                        <div class="mayhen_invitation_tl_title">{{ $tlTitle }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ================================================================= --}}
        {{-- SECTION 7: DRESSCODE (ỐNG TRÚC CẮM NHÀNH TRÚC ĐÀO)               --}}
        {{-- ================================================================= --}}
        @if(!$isCuratedSeven)
            <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

            <section class="mayhen_invitation_section_block" data-mayhen-section="dresscode">
                <div class="mayhen_invitation_sec_head">
                    <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                        <i></i><span class="mayhen_invitation_kicker_label">Trang phục</span><i></i>
                    </div>
                    <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Sắc Màu Ngày Vui</h2>
                    <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                        <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                    </div>
                </div>

                <p class="mayhen_invitation_prose mayhen_invitation_reveal" data-reveal="up" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>

                <div class="mayhen_invitation_dresscode_swatches mayhen_invitation_reveal" data-reveal="up" id="mayhenDresscodeSwatches" data-sync-target="dresscode_colors">
                    @foreach($dresscodeColors as $cIdx => $hexColor)
                        @php
                            $hexUpper = strtoupper(trim($hexColor));
                            $colorLabel = $colorNameMap[$hexUpper] ?? $hexUpper;
                        @endphp
                        <div class="mayhen_invitation_dco" style="--c:{{ $hexColor }}">
                            <div class="mayhen_invitation_dco_vessel">
                                <svg class="mayhen_invitation_dco_flower" viewBox="0 0 64 58" aria-hidden="true" style="animation-delay:{{ number_format($cIdx * 0.5, 1) }}s">
                                    <path class="cuong" d="M32 58 Q30 42 26 28 Q23 18 18 12" />
                                    <path class="cuong" d="M32 58 Q35 44 41 31 Q46 22 50 16" />
                                    <path class="cuong" d="M32 58 Q32 40 34 20" />
                                    <path class="la" d="M26 42 Q12 36 8 28 Q20 32 28 38 Z" />
                                    <path class="la" d="M36 44 Q50 38 56 30 Q44 34 36 40 Z" />
                                    <path class="la" d="M31 32 Q20 22 14 18 Q24 22 32 28 Z" />
                                    <g transform="translate(34,17)">
                                        <path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" />
                                        <path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(72)" />
                                        <path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(144)" />
                                        <path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(216)" />
                                        <path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(288)" />
                                        <circle class="nhuy" r="2.6" />
                                    </g>
                                    <g transform="translate(18,14) scale(0.72)">
                                        <path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" />
                                        <path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(72)" />
                                        <path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(144)" />
                                        <path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(216)" />
                                        <path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(288)" />
                                        <circle class="nhuy" r="2.6" />
                                    </g>
                                    <path class="nu" d="M50 18 C47 14 48 9 52 7 C55 11 53 16 50 18Z" />
                                </svg>
                                <div class="mayhen_invitation_dco_tube" style="--c:{{ $hexColor }}">
                                    <i class="mayhen_invitation_dco_mouth"></i>
                                    <i class="mayhen_invitation_dco_node mayhen_invitation_dco_node--1"></i>
                                    <i class="mayhen_invitation_dco_node mayhen_invitation_dco_node--2"></i>
                                    <i class="mayhen_invitation_dco_silk"></i>
                                    <svg class="mayhen_invitation_dco_bow" viewBox="0 0 40 20" aria-hidden="true">
                                        <path d="M20 10 C14 3 4 2 2 8 C1 13 11 16 20 10 Z" />
                                        <path d="M20 10 C26 3 36 2 38 8 C39 13 29 16 20 10 Z" />
                                        <path d="M18 11 L12 19 L16 18 L20 12 Z" />
                                        <path d="M22 11 L28 19 L24 18 L20 12 Z" />
                                        <circle cx="20" cy="10" r="2.6" />
                                    </svg>
                                </div>
                                <span class="mayhen_invitation_dco_shadow" aria-hidden="true"></span>
                            </div>
                            <span class="mayhen_invitation_dco_label">{{ $colorLabel }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

        {{-- ================================================================= --}}
        {{-- SECTION 8: ALBUM (10 ẢNH KỶ NIỆM & LIGHTBOX)                      --}}
        {{-- ================================================================= --}}
        <section class="mayhen_invitation_section_block" data-mayhen-section="album">
            <div class="mayhen_invitation_tt mayhen_invitation_tt--la2 mayhen_invitation_tt--g-tr" aria-hidden="true"></div>

            <div class="mayhen_invitation_sec_head">
                <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                    <i></i><span class="mayhen_invitation_kicker_label">Album</span><i></i>
                </div>
                <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Khoảnh Khắc Bên Nhau</h2>
                <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                    <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                </div>
            </div>

            <div class="mayhen_invitation_album_grid mayhen_invitation_reveal" data-reveal="up" id="mayhenAlbumGrid">
                @foreach($galleryPhotos as $gIdx => $gPhoto)
                    <figure
                        @class(['mayhen_invitation_album_item', 'mayhen_invitation_album_item--featured' => ($gIdx === 0 || $gIdx === 9)])
                        data-gallery-index="{{ $gIdx }}"
                        data-lightbox-src="{{ $gPhoto }}"
                        data-lightbox-caption="Ảnh cưới {{ $gIdx + 1 }} / {{ count($galleryPhotos) }} — {{ $groomName }} &amp; {{ $brideName }}"
                    >
                        <div class="mayhen_invitation_album_media">
                            <img src="{{ $gPhoto }}" alt="Ảnh cưới {{ $gIdx + 1 }} của {{ $groomName }} &amp; {{ $brideName }}" data-sync-target="gallery.{{ $gIdx }}" loading="lazy">
                        </div>
                    </figure>
                @endforeach
            </div>

            <p class="mayhen_invitation_album_quote mayhen_invitation_reveal" data-reveal="up">
                Mỗi khoảnh khắc bên nhau đều là kỷ vật quý giá
            </p>
        </section>

        {{-- ================================================================= --}}
        {{-- SECTION 9: GIFT (HỘP MỪNG CƯỚI BAO LÌ XÌ SƠN MÀI ĐỎ & VIETQR)     --}}
        {{-- ================================================================= --}}
        @if(!$isCuratedSeven)
            <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

            <section class="mayhen_invitation_section_block" data-mayhen-section="gift">
                <div class="mayhen_invitation_sec_head">
                    <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                        <i></i><span class="mayhen_invitation_kicker_label">Mừng cưới</span><i></i>
                    </div>
                    <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Hộp Mừng Cưới</h2>
                    <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                        <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                    </div>
                </div>

                <p class="mayhen_invitation_prose mayhen_invitation_reveal" data-reveal="up">
                    Cảm ơn tấm lòng của bạn dành cho chúng mình
                </p>

                <div id="tdGiftStage" @class(['mayhen_invitation_gift_stage', 'is-opened' => $isEmbed])>
                    <div class="mayhen_invitation_lixi_wrap">
                        <div id="tdLixiBox" class="mayhen_invitation_lixi mayhen_invitation_reveal" data-reveal="pop" role="button" tabindex="0" aria-label="Mở bao lì xì mừng cưới">
                            <span class="mayhen_invitation_lixi_halo" aria-hidden="true"></span>
                            <span class="mayhen_invitation_lixi_shadow" aria-hidden="true"></span>
                            <span class="mayhen_invitation_lixi_back" aria-hidden="true"></span>
                            <span class="mayhen_invitation_lixi_card" aria-hidden="true">
                                <svg viewBox="0 0 90 220"><use href="#td-truc-vang" /></svg>
                                <em class="mayhen_invitation_lixi_hy">囍</em>
                                <b>Hồng Phúc</b>
                                <i>Trăm năm hạnh phúc</i>
                            </span>
                            <span class="mayhen_invitation_lixi_front" aria-hidden="true">
                                <svg class="mayhen_invitation_lixi_bamboo mayhen_invitation_lixi_bamboo--left" viewBox="0 0 90 220"><use href="#td-truc-vang" /></svg>
                                <svg class="mayhen_invitation_lixi_bamboo mayhen_invitation_lixi_bamboo--right" viewBox="0 0 90 220"><use href="#td-truc-vang" /></svg>
                                <em class="mayhen_invitation_lixi_title">Mừng Cưới</em>
                                <small class="mayhen_invitation_lixi_sub">Trăm năm hảo hợp</small>
                            </span>
                            <span class="mayhen_invitation_lixi_flap" aria-hidden="true">
                                <svg viewBox="0 0 210 132" preserveAspectRatio="none"><use href="#td-nap-lx" /></svg>
                            </span>
                            <span class="mayhen_invitation_lixi_coin" aria-hidden="true">囍</span>
                        </div>
                        <p class="mayhen_invitation_lixi_hint">Chạm vào bao lì xì để mở</p>
                    </div>

                    <div class="mayhen_invitation_qr_grid">
                        <div class="mayhen_invitation_qr_card">
                            <p class="mayhen_invitation_qr_role">Chú Rể</p>
                            <div class="mayhen_invitation_qr_media" data-lightbox-src="{{ $groomQrImage }}" data-lightbox-qr="1" data-lightbox-caption="Mừng cưới Chú rể · {{ $groomBankName }} · {{ $groomBankAccount }} · {{ $groomAccountName }}">
                                <img src="{{ $groomQrImage }}" alt="Mã QR Chú rể {{ $groomAccountName }}" data-sync-target="qr_groom_image" loading="lazy">
                            </div>
                            <p class="mayhen_invitation_qr_info">
                                <strong data-sync-target="groom_bank_name">{{ $groomBankName }}</strong><br>
                                <span data-sync-target="groom_bank_account">{{ $groomBankAccount }}</span><br>
                                <span data-sync-target="groom_account_name">{{ $groomAccountName }}</span>
                            </p>
                            <button type="button" class="mayhen_invitation_qr_copy_btn" data-copy-account="{{ $groomBankAccount }}">Sao chép STK</button>
                        </div>

                        <div class="mayhen_invitation_qr_card">
                            <p class="mayhen_invitation_qr_role">Cô Dâu</p>
                            <div class="mayhen_invitation_qr_media" data-lightbox-src="{{ $brideQrImage }}" data-lightbox-qr="1" data-lightbox-caption="Mừng cưới Cô dâu · {{ $brideBankName }} · {{ $brideBankAccount }} · {{ $brideAccountName }}">
                                <img src="{{ $brideQrImage }}" alt="Mã QR Cô dâu {{ $brideAccountName }}" data-sync-target="qr_bride_image" loading="lazy">
                            </div>
                            <p class="mayhen_invitation_qr_info">
                                <strong data-sync-target="bride_bank_name">{{ $brideBankName }}</strong><br>
                                <span data-sync-target="bride_bank_account">{{ $brideBankAccount }}</span><br>
                                <span data-sync-target="bride_account_name">{{ $brideAccountName }}</span>
                            </p>
                            <button type="button" class="mayhen_invitation_qr_copy_btn" data-copy-account="{{ $brideBankAccount }}">Sao chép STK</button>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

        {{-- ================================================================= --}}
        {{-- SECTION 10: ENDING (KÝ TÊN, RSVP, SỔ LỜI CHÚC TRE & THANK YOU)   --}}
        {{-- ================================================================= --}}
        <div data-mayhen-section="ending">
            {{-- 10A. Bảng Ký Tên Tranh Cuộn Tre (0 Ảnh Người — Tuân Thủ Luật #8) --}}
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_tt mayhen_invitation_tt--la mayhen_invitation_tt--g-tl" aria-hidden="true"></div>
                <div class="mayhen_invitation_tt mayhen_invitation_tt--tre-trai mayhen_invitation_tt--g-bl" aria-hidden="true"></div>
                <div class="mayhen_invitation_tt mayhen_invitation_tt--tre mayhen_invitation_tt--g-br" aria-hidden="true"></div>

                <div class="mayhen_invitation_sec_head">
                    <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                        <i></i><span class="mayhen_invitation_kicker_label">Lưu bút</span><i></i>
                    </div>
                    <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Ký Tên Chúc Phúc</h2>
                    <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                        <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                    </div>
                </div>

                <p class="mayhen_invitation_prose mayhen_invitation_reveal" data-reveal="up">
                    Để lại chữ ký của bạn, cùng chúng mình lưu giữ ngày vui này mãi mãi.
                </p>

                <div class="mayhen_invitation_sig_scroll mayhen_invitation_reveal" data-reveal="up">
                    <span class="mayhen_invitation_sig_bamboo_frame" aria-hidden="true">
                        <svg class="mayhen_invitation_sig_roller mayhen_invitation_sig_roller--top" viewBox="0 0 400 22" preserveAspectRatio="none"><use href="#td-ong-ngang" /></svg>
                        <svg class="mayhen_invitation_sig_roller mayhen_invitation_sig_roller--bottom" viewBox="0 0 400 22" preserveAspectRatio="none"><use href="#td-ong-ngang" /></svg>
                        <i class="mayhen_invitation_sig_cord mayhen_invitation_sig_cord--left"></i>
                        <i class="mayhen_invitation_sig_cord mayhen_invitation_sig_cord--right"></i>
                        <i class="mayhen_invitation_sig_hook"></i>
                        <svg class="mayhen_invitation_sig_rod mayhen_invitation_sig_rod--left" viewBox="0 0 24 400" preserveAspectRatio="none"><use href="#td-than-doc" /></svg>
                        <svg class="mayhen_invitation_sig_rod mayhen_invitation_sig_rod--right" viewBox="0 0 24 400" preserveAspectRatio="none"><use href="#td-than-doc" /></svg>
                    </span>
                    <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tl" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>

                    <div class="mayhen_invitation_sig_crest">
                        <svg class="mayhen_invitation_sig_crest_svg" viewBox="0 0 120 90" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                        <p class="mayhen_invitation_sig_couple">
                            <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                        </p>
                    </div>

                    <div class="mayhen_invitation_sig_field">
                        <label for="mayhenSigGuestName">Tên của bạn</label>
                        <input id="mayhenSigGuestName" type="text" class="mayhen_invitation_sig_input" placeholder="Nhập tên người ký..." maxlength="80" value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}">
                    </div>

                    <div class="mayhen_invitation_sig_canvas_wrap">
                        <canvas id="mayhenSigCanvas" class="mayhen_invitation_sig_canvas" aria-label="Khung vẽ chữ ký chúc phúc"></canvas>
                        <div id="mayhenSigHint" class="mayhen_invitation_sig_hint">Ký tay của bạn tại đây…</div>
                    </div>

                    <div class="mayhen_invitation_sig_actions">
                        <button type="button" id="mayhenSigClear" class="mayhen_invitation_sig_btn mayhen_invitation_sig_btn--outline">Xóa nét</button>
                        <button type="button" id="mayhenSigPreviewBtn" class="mayhen_invitation_sig_btn mayhen_invitation_sig_btn--outline">Xem trước</button>
                        <button type="button" id="mayhenSigSave" class="mayhen_invitation_sig_btn">Lưu chữ ký</button>
                    </div>

                    <div id="mayhenSigPreviewBox" class="mayhen_invitation_sig_preview">
                        <img id="mayhenSigPreviewImg" src="" alt="Bản xem trước chữ ký">
                        <a id="mayhenSigDownloadLink" href="#" download="chu-ky-chuc-phuc.png" class="mayhen_invitation_sig_btn mayhen_invitation_sig_btn--outline">Tải ảnh chữ ký</a>
                    </div>
                    <p id="mayhenSigStatus" class="mayhen_invitation_sig_status" role="status" aria-live="polite"></p>
                </div>
            </section>

            <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

            {{-- 10B. Xác Nhận Tham Dự RSVP (0 Ảnh Người — Tuân Thủ Luật #8) --}}
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_tt mayhen_invitation_tt--la" aria-hidden="true"></div>
                <div class="mayhen_invitation_tt mayhen_invitation_tt--tre" aria-hidden="true"></div>

                <div class="mayhen_invitation_sec_head">
                    <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                        <i></i><span class="mayhen_invitation_kicker_label">Hồi âm</span><i></i>
                    </div>
                    <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Xác Nhận Tham Dự</h2>
                    <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                        <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                    </div>
                </div>

                {{-- Khung Ấn Triện Trúc Đào SVG + Monogram (Thay thế IMG_XN1/IMG_XN2 theo Luật #8) --}}
                <div class="mayhen_invitation_rsvp_emblem_card mayhen_invitation_reveal" data-reveal="up">
                    <svg class="mayhen_invitation_rsvp_emblem_svg" viewBox="-120 -120 240 240" aria-hidden="true"><use href="#dk-an" /></svg>
                    <p class="mayhen_invitation_rsvp_monogram">
                        <span data-monogram-role="groom">{{ $groomInitial }}</span> &amp; <span data-monogram-role="bride">{{ $brideInitial }}</span>
                    </p>
                    <p class="mayhen_invitation_rsvp_emblem_note">R.S.V.P · {{ $formattedDateDots }}</p>
                </div>

                <p class="mayhen_invitation_prose mayhen_invitation_reveal" data-reveal="up">
                    Để gia đình chuẩn bị chu đáo, bạn vui lòng xác nhận tham dự sớm nhé!
                </p>

                <div class="mayhen_invitation_form_card mayhen_invitation_reveal" data-reveal="up">
                    <form id="mayhenRsvpForm" novalidate>
                        <div class="mayhen_invitation_form_group">
                            <label class="mayhen_invitation_form_label" for="mayhenRsvpName">Họ và tên khách mời *</label>
                            <input id="mayhenRsvpName" name="guest_name" type="text" class="mayhen_invitation_input" required placeholder="Nhập họ tên của bạn..." value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}">
                        </div>
                        <div class="mayhen_invitation_form_group">
                            <label class="mayhen_invitation_form_label" for="mayhenRsvpStatus">Bạn sẽ tham dự chứ? *</label>
                            <select id="mayhenRsvpStatus" name="attending_status" class="mayhen_invitation_select" required>
                                <option value="attending">Chắc chắn sẽ tham dự</option>
                                <option value="maybe">Chưa chắc chắn</option>
                                <option value="not_attending">Rất tiếc mình bận mất rồi</option>
                            </select>
                        </div>
                        <div class="mayhen_invitation_form_row">
                            <div class="mayhen_invitation_form_group">
                                <label class="mayhen_invitation_form_label" for="mayhenRsvpAdults">Số người lớn</label>
                                <input id="mayhenRsvpAdults" name="adults_count" type="number" min="1" max="10" value="1" class="mayhen_invitation_input">
                            </div>
                            <div class="mayhen_invitation_form_group">
                                <label class="mayhen_invitation_form_label" for="mayhenRsvpChildren">Số trẻ em</label>
                                <input id="mayhenRsvpChildren" name="children_count" type="number" min="0" max="10" value="0" class="mayhen_invitation_input">
                            </div>
                        </div>
                        <div class="mayhen_invitation_form_group">
                            <label class="mayhen_invitation_form_label" for="mayhenRsvpNote">Lời nhắn gửi gia đình</label>
                            <textarea id="mayhenRsvpNote" name="wishes" rows="2" class="mayhen_invitation_textarea" placeholder="Ghi chú chế độ ăn hoặc lời chúc ngắn..."></textarea>
                        </div>
                        <button type="submit" class="mayhen_invitation_submit_btn">Gửi xác nhận tham dự</button>
                        <p id="mayhenRsvpFeedback" class="mayhen_invitation_form_feedback" role="status" aria-live="polite"></p>
                    </form>
                </div>
            </section>

            <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

            {{-- 10C. Sổ Lưu Bút Lời Chúc — Cây Tre Đứng & Thẻ Tre --}}
            <section class="mayhen_invitation_section_block">
                <div class="mayhen_invitation_sec_head">
                    <div class="mayhen_invitation_kicker mayhen_invitation_reveal" data-reveal="up">
                        <i></i><span class="mayhen_invitation_kicker_label">Lời chúc</span><i></i>
                    </div>
                    <h2 class="mayhen_invitation_sec_title mayhen_invitation_reveal" data-reveal="up">Gửi Lời Chúc Phúc</h2>
                    <div class="mayhen_invitation_sec_rule mayhen_invitation_reveal" data-reveal="pop" aria-hidden="true">
                        <span></span><svg><use href="#dk-hoa" /></svg><span></span>
                    </div>
                </div>

                <div class="mayhen_invitation_form_card mayhen_invitation_reveal" data-reveal="up">
                    <form id="mayhenWishForm" novalidate>
                        <div class="mayhen_invitation_form_group">
                            <label class="mayhen_invitation_form_label" for="mayhenWishAuthor">Tên của bạn *</label>
                            <input id="mayhenWishAuthor" name="name" type="text" class="mayhen_invitation_input" required placeholder="Nhập tên của bạn..." value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}">
                        </div>
                        <div class="mayhen_invitation_form_group">
                            <label class="mayhen_invitation_form_label" for="mayhenWishMessage">Lời chúc phúc *</label>
                            <textarea id="mayhenWishMessage" name="message" rows="3" class="mayhen_invitation_textarea" required placeholder="Viết lời chúc tốt đẹp gửi tới {{ $groomName }} &amp; {{ $brideName }}..."></textarea>
                        </div>
                        <button type="submit" class="mayhen_invitation_submit_btn">Gửi lời chúc</button>
                        <p id="mayhenWishFeedback" class="mayhen_invitation_form_feedback" role="status" aria-live="polite"></p>
                    </form>
                </div>

                <h3 class="mayhen_invitation_wishes_heading mayhen_invitation_reveal" data-reveal="up">Lời chúc từ mọi người</h3>

                <div id="mayhenWishesList" class="mayhen_invitation_bamboo_wishes mayhen_invitation_reveal" data-reveal="up">
                    @forelse($wishesList as $wish)
                        <article class="mayhen_invitation_wish_slip">
                            <p class="mayhen_invitation_wish_author">{{ $wish->guest_name ?? $wish->name ?? 'Bạn bè' }}</p>
                            <p class="mayhen_invitation_wish_text">{{ $wish->message ?? '' }}</p>
                            @if(!empty($wish->reply))
                                <p class="mayhen_invitation_wish_reply">{{ $wish->reply }}</p>
                            @endif
                        </article>
                    @empty
                        <article class="mayhen_invitation_wish_slip" data-placeholder-wish="1">
                            <p class="mayhen_invitation_wish_author">Mây Hẹn</p>
                            <p class="mayhen_invitation_wish_text">Chúc {{ $groomName }} &amp; {{ $brideName }} trăm năm hạnh phúc, sắc son bền chặt như nhành trúc đào giữa nắng xuân!</p>
                        </article>
                    @endforelse
                </div>
            </section>

            {{-- 10D. Lời Cảm Ơn Kết Thiệp --}}
            @if(!$isCuratedSeven)
                <div class="mayhen_invitation_divider" aria-hidden="true"><svg><use href="#dk-hoa" /></svg></div>

                <section class="mayhen_invitation_section_block">
                    <div class="mayhen_invitation_tt mayhen_invitation_tt--la" aria-hidden="true"></div>
                    <div class="mayhen_invitation_tt mayhen_invitation_tt--tre-trai" aria-hidden="true"></div>
                    <div class="mayhen_invitation_tt mayhen_invitation_tt--la2" aria-hidden="true"></div>
                    <div class="mayhen_invitation_tt mayhen_invitation_tt--tre" aria-hidden="true"></div>

                    <div class="mayhen_invitation_frame mayhen_invitation_frame--wide mayhen_invitation_reveal" data-reveal="up">
                        <div class="mayhen_invitation_frame_media" data-lightbox-src="{{ $footerPhoto }}" data-lightbox-caption="{{ $thankYouTitle }} — {{ $groomName }} &amp; {{ $brideName }}">
                            <img src="{{ $footerPhoto }}" alt="Ảnh kết thiệp {{ $groomName }} &amp; {{ $brideName }}" data-sync-target="footer_photo_url" loading="lazy">
                        </div>
                        <svg class="mayhen_invitation_corner_flower mayhen_invitation_corner_flower--tr" aria-hidden="true"><use href="#dk-chum-hoa" /></svg>
                    </div>

                    <h2 class="mayhen_invitation_closing_title mayhen_invitation_reveal" data-reveal="up" data-sync-target="thank_you_title">{{ $thankYouTitle }}</h2>
                    <p class="mayhen_invitation_prose mayhen_invitation_reveal" data-reveal="up" data-sync-target="thank_you_quote">{{ $thankYouQuote }}</p>
                    <p class="mayhen_invitation_closing_sign mayhen_invitation_reveal" data-reveal="up">
                        <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                    </p>
                </section>
            @else
                <footer class="mayhen_invitation_curated_closing">
                    <p class="mayhen_invitation_closing_sign">
                        <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span> · {{ $formattedDateDots }}
                    </p>
                </footer>
            @endif
        </div>
    </main>

    {{-- Nhạc nền & Nút bật/tắt nhạc nổi --}}
    <audio id="mayhenBgAudio" src="{{ $musicUrl }}" loop preload="none" data-sync-target="music_url"></audio>
    <button type="button" id="mayhenMusicBtn" class="mayhen_invitation_music_btn" aria-label="Bật hoặc tắt nhạc nền" aria-pressed="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" aria-hidden="true">
            <path d="M9 18V5l12-2v13" /><circle cx="6" cy="18" r="3" /><circle cx="18" cy="16" r="3" />
        </svg>
    </button>

    {{-- Full-Screen Lightbox cho Album & Mã QR --}}
    <div id="mayhenLightbox" class="mayhen_invitation_lightbox" role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to" aria-hidden="true">
        <div class="mayhen_invitation_lb_top">
            <span id="mayhenLbCount">1 / 1</span>
            <button type="button" id="mayhenLbClose" class="mayhen_invitation_lb_close" aria-label="Đóng">&times;</button>
        </div>
        <div class="mayhen_invitation_lb_stage">
            <button type="button" id="mayhenLbPrev" class="mayhen_invitation_lb_nav mayhen_invitation_lb_nav--prev" aria-label="Ảnh trước">&lsaquo;</button>
            <img id="mayhenLbImg" src="" alt="Ảnh phóng to">
            <button type="button" id="mayhenLbNext" class="mayhen_invitation_lb_nav mayhen_invitation_lb_nav--next" aria-label="Ảnh sau">&rsaquo;</button>
        </div>
        <p id="mayhenLbCaption" class="mayhen_invitation_lb_caption"></p>
    </div>

    {{-- Toast thông báo không dùng alert() --}}
    <div id="mayhenToast" class="mayhen_invitation_toast" role="status" aria-live="polite"></div>
</div>
@endsection

@push('scripts')
    <script src="{{ $assetBase }}/script.js?v={{ $jsVer }}" defer></script>
@endpush
