@php
    $assetBase = url('/mau-thiep/luu-but-ngay-chung-doi/assets');
    $isEmbed = request()->boolean('embed');

    // 1. Tên cô dâu chú rể & Khách mời
    $groomName = $wedding->groom_name ?? 'Ngọc Sơn';
    $brideName = $wedding->bride_name ?? 'Thúy Quỳnh';
    $guestName = request()->query('guest') ?: ($invitation->guest_name ?? 'Bạn và Gia Đình');
    $extraData = $wedding->extra_data ?? [];

    // Bố mẹ hai bên
    $groomFather = !empty($extraData['groom_father']) ? $extraData['groom_father'] : 'Nguyễn Văn Nam';
    $groomMother = !empty($extraData['groom_mother']) ? $extraData['groom_mother'] : 'Trần Thị Mai';
    $brideFather = !empty($extraData['bride_father']) ? $extraData['bride_father'] : 'Lê Văn Hùng';
    $brideMother = !empty($extraData['bride_mother']) ? $extraData['bride_mother'] : 'Phạm Thị Lan';

    // 2. Ngày giờ và Sự kiện cưới
    $weddingDate = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2027, 11, 1, 11, 0, 0);

    $weddingDay = $weddingDate->format('d');
    $weddingMonth = $weddingDate->format('m');
    $weddingYear = $weddingDate->format('Y');
    $weddingTime = $weddingDate->format('H:i');
    $coverDateFormatted = $weddingDate->format('d.m.Y');
    $weddingIso = $weddingDate->toIso8601String();

    $dayNames = [
        'Sunday' => 'Chủ Nhật',
        'Monday' => 'Thứ Hai',
        'Tuesday' => 'Thứ Ba',
        'Wednesday' => 'Thứ Tư',
        'Thursday' => 'Thứ Năm',
        'Friday' => 'Thứ Sáu',
        'Saturday' => 'Thứ Bảy',
    ];
    $weddingDayOfWeek = $dayNames[$weddingDate->format('l')] ?? 'Thứ Bảy';

    // Sự kiện & Địa chỉ
    $brideEventAddress = !empty($extraData['bride_event_address'])
        ? $extraData['bride_event_address']
        : 'Đường Nguyễn Tất Thành, Văn Phú, Lào Cai';

    $groomEventAddress = !empty($extraData['groom_event_address'])
        ? $extraData['groom_event_address']
        : 'Phố Hoàng Liên, Cốc Lếu, Lào Cai';

    $brideMapLink = !empty($extraData['bride_map_link'])
        ? $extraData['bride_map_link']
        : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($brideEventAddress);

    $groomMapLink = !empty($extraData['groom_map_link'])
        ? $extraData['groom_map_link']
        : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($groomEventAddress);

    // Hàm resolve asset chuẩn bảo mật
    $resolveAsset = function (?string $path, string $fallback = '') use ($assetBase): string {
        $val = trim((string) $path);
        if (str_starts_with($val, 'blob:')) {
            $val = '';
        }
        if (empty($val)) {
            $fb = trim($fallback);
            if (empty($fb)) return '';
            return str_starts_with($fb, 'http://') || str_starts_with($fb, 'https://') || str_starts_with($fb, '/')
                ? $fb
                : $assetBase . '/' . ltrim($fb, '/');
        }
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://') || str_starts_with($val, '/')) {
            return $val;
        }
        return $assetBase . '/' . ltrim($val, '/');
    };

    // 3. Mừng cưới QR & Ngân hàng
    $qrGroom = $resolveAsset($extraData['qr_groom_image'] ?? null, '');
    $qrBride = $resolveAsset($extraData['qr_bride_image'] ?? null, '');

    $groomBankInfo = [
        'bank' => $extraData['groom_bank_name'] ?? 'Techcombank',
        'name' => $extraData['groom_account_name'] ?? $groomName,
        'number' => $extraData['groom_account_number'] ?? '190368888888',
        'qr' => $qrGroom,
    ];
    $brideBankInfo = [
        'bank' => $extraData['bride_bank_name'] ?? 'Vietcombank',
        'name' => $extraData['bride_account_name'] ?? $brideName,
        'number' => $extraData['bride_account_number'] ?? '998877665544',
        'qr' => $qrBride,
    ];

    // 4. Nhạc nền
    $musicUrl = $resolveAsset($wedding->music_url ?? null, '');

    // 5. Album ảnh cưới 12 ảnh
    $defaultAlbum = [
        $assetBase . '/images/external/3901bfd3190f56014d895d1a.jpg',
        $assetBase . '/images/external/e9af36aeab2bdadab5cfa38a.jpg',
        $assetBase . '/images/external/92a00aa5496c7a21b66e849c.jpg',
        $assetBase . '/images/external/69415bc1a70fa6ab262a5a64.jpg',
        $assetBase . '/images/external/26844409b0f13b3939a425ed.jpg',
        $assetBase . '/images/external/6da36455f708e4ada496156b.jpg',
        $assetBase . '/images/external/d186c382da25135ecdb84a26.jpg',
        $assetBase . '/images/external/9a1368650f4a8f7d390b25fb.jpg',
        $assetBase . '/images/external/0086c9a04b4db8dd38df1f73.jpg',
        $assetBase . '/images/external/fd1e0117e9fae02cd4a4ce79.jpg',
        $assetBase . '/images/external/ba3d1ab0162723fc4b2a42b8.jpg',
        $assetBase . '/images/external/750ef46df6b267d9fe57e090.jpg',
    ];
    $rawGallery = $wedding->extra_data['gallery'] ?? [];
    $validGallery = is_array($rawGallery)
        ? array_filter(
            $rawGallery,
            fn($item) => !empty(trim((string) $item)) && !str_starts_with((string) $item, 'blob:'),
        )
        : [];
    $selectedAlbum = !empty($validGallery) ? array_values($validGallery) : $defaultAlbum;
    $albumPhotos = array_map(fn($p) => $resolveAsset($p), $selectedAlbum);

    // 6. Lời chúc & RSVP URLs
    $wishes = $wishes ?? collect([]);
    $wishSubmitUrl = $wishSubmitUrl ?? (isset($invitation)
        ? route('invitation.wish.store', $invitation->slug)
        : route('templates.preview.wish', 'luu-but-ngay-chung-doi'));

    $rsvpSubmitUrl = $rsvpSubmitUrl ?? (isset($invitation)
        ? route('rsvp.submit', $invitation->slug)
        : route('templates.preview.rsvp', 'luu-but-ngay-chung-doi'));

    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Lưu Bút Ngày Chung Đôi';
    $pageTitle = $isPreview 
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name') 
        : 'Thiệp mời cưới: "' . $groomName . ' & ' . $brideName . '" | ' . config('app.name');

    $cssPath = resource_path('views/client/invitations/luu-but-ngay-chung-doi/styles.css');
    $jsPath = resource_path('views/client/invitations/luu-but-ngay-chung-doi/script.js');
    $cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
    $jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();
@endphp

@extends('client.layouts.invitation')

@section('html_class', 'mayhen_invitation_ladjihe38udhd_root')
@section('title', $pageTitle)
@section('meta_description', $isPreview ? 'Trải nghiệm xem thử mẫu thiệp cưới ' . $templateName . ' trên ' . config('app.name') : 'Thiệp cưới ' . $groomName . ' & ' . $brideName . ' – ngày ' . $coverDateFormatted . '.')
@section('body_class', 'mayhen_invitation_wrapper')

@push('fonts')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Philosopher:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ route('templates.asset', ['slug' => 'luu-but-ngay-chung-doi', 'path' => 'styles.css']) }}?v={{ $cssVersion }}">
@endpush

@section('head_extra')
    <script>
        window.MAYHEN_W18 = {
            groom: @json($groomName),
            bride: @json($brideName),
            guest: @json($guestName),
            weddingDate: @json($weddingIso),
            brideEventAddress: @json($brideEventAddress),
            groomEventAddress: @json($groomEventAddress),
            gift: {
                groom: @json($groomBankInfo),
                bride: @json($brideBankInfo)
            },
            album: @json($albumPhotos),
            isPreview: {{ $isPreview ? 'true' : 'false' }},
            music: @json($musicUrl),
            wishSubmitUrl: @json($wishSubmitUrl),
            rsvpSubmitUrl: @json($rsvpSubmitUrl),
            storageKey: 'mayhen_lbcd_' + @json($invitation->slug ?? 'demo')
        };
    </script>
@endsection

@section('content')
<div id="mayhen_w18_stage"><div class="mayhen_invitation_ladjihe38udhd_ladi-wraper">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION28">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION1">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE1">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX43">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH43">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph">You are invited to <br/>the wedding celebration of</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE80">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE76">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE85">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE82">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE87">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP94">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE3">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">sơn</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE282">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">&amp;<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE283">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">quỳnh</h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_btn_open_menu" data-action="true" id="SHAPE15">
<div class="mayhen_invitation_ladjihe38udhd_ladi-shape"><svg class="" fill="#3B5E4F" height="100%" preserveaspectratio="none" viewbox="0 0 1536 1896.0833" width="100%" xmlns="http://www.w3.org/2000/svg">
<path d="M1536 1344v128q0 26-19 45t-45 19H64q-26 0-45-19t-19-45v-128q0-26 19-45t45-19h1408q26 0 45 19t19 45zm0-512v128q0 26-19 45t-45 19H64q-26 0-45-19T0 960V832q0-26 19-45t45-19h1408q26 0 45 19t19 45zm0-512v128q0 26-19 45t-45 19H64q-26 0-45-19T0 448V320q0-26 19-45t45-19h1408q26 0 45 19t19 45z">
</path>
</svg></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP165">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX85">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-animation" id="BOX86">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP166">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_off_speaker" id="SHAPE22">
<div class="mayhen_invitation_ladjihe38udhd_ladi-shape mayhen_invitation_ladjihe38udhd_ladi-transition"><svg class="" enable-background="new 206 206 100 100" fill="rgba(255, 255, 255, 1)" height="100%" preserveaspectratio="none" version="1.1" viewbox="206 206 100 100" width="100%" x="0px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:xlink="http://www.w3.org/1999/xlink" y="0px">
<metadata>
<sfw xmlns="http://ns.adobe.com/SaveForWeb/1.0/">
<slices></slices>
<slicesourcebounds bottomleftorigin="true" height="90" width="88.801" x="355.599" y="255"></slicesourcebounds>
</sfw>
</metadata>
<g>
<path d="M226.473,243.243h-3.415c-1.665,0-3.014,1.349-3.014,3.014v19.486c0,1.664,1.349,3.014,3.014,3.014h3.415h0.201h6.228   v-25.514h-6.228H226.473z">
</path>
<g>
<polygon points="268.26,301 268.26,252.234 243.876,276.617   ">
</polygon>
<polygon points="268.26,211 236.116,243.143 236.116,266.194 268.26,234.051   ">
</polygon>
<rect height="6.428" transform="matrix(-0.7071 0.7071 -0.7071 -0.7071 617.6146 254.9795)" width="119.156" x="196.422" y="252.187"></rect>
</g>
</g>
</svg></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_on_speaker" id="SHAPE23" style="display: none;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-shape mayhen_invitation_ladjihe38udhd_ladi-transition"><svg class="" enable-background="new 206 206 100 100" fill="rgba(255, 255, 255, 1)" height="100%" preserveaspectratio="none" version="1.1" viewbox="206 206 100 100" width="100%" x="0px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:xlink="http://www.w3.org/1999/xlink" y="0px">
<metadata>
<sfw xmlns="http://ns.adobe.com/SaveForWeb/1.0/">
<slices></slices>
<slicesourcebounds bottomleftorigin="true" height="89.602" width="90" x="355" y="255.199"></slicesourcebounds>
</sfw>
</metadata>
<g>
<path d="M217.4,243.3H214c-1.657,0-3,1.343-3,3v19.4c0,1.657,1.343,3,3,3h3.4h0.2h6.2v-25.4h-6.2H217.4z">
</path>
<polygon points="227,268.8 259,300.801 259,211.199 227,243.2  ">
</polygon>
<path d="M270.031,244.424l-4.631,4.63c3.83,3.83,3.83,10.062,0,13.891l4.631,4.63C276.414,261.191,276.414,250.808,270.031,244.424   z">
</path>
<path d="M279.291,235.163l-4.629,4.631c8.936,8.936,8.936,23.476,0,32.412l4.631,4.631   C290.781,265.348,290.781,246.653,279.291,235.163z">
</path>
<path d="M288.553,225.903l-4.631,4.63c14.043,14.042,14.043,36.892,0,50.935l4.631,4.63   C305.148,269.503,305.148,242.499,288.553,225.903z">
</path>
</g>
</svg></div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION22">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE95">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE94">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE88">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP71">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE184">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">Save <br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE185">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">Date</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE186">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">the</h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH45">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph">Together, we found home</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP89">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX51">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE276"><h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">{{ $weddingDay }}</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE277"><h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">{{ $weddingDayOfWeek }}</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE278">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline"><span class="mayhen_invitation_ladjihe38udhd_ladipage-animated-headline mayhen_invitation_ladjihe38udhd_scale mayhen_invitation_ladjihe38udhd_letters"><span class="mayhen_invitation_ladjihe38udhd_ladipage-animated-words-wrapper" data-word='["17:00"]' style="width: 74px;"><b class="mayhen_invitation_ladjihe38udhd_is-visible" style="opacity: 1;"><i class="mayhen_invitation_ladjihe38udhd_out">1</i><i class="mayhen_invitation_ladjihe38udhd_out">1</i><i class="mayhen_invitation_ladjihe38udhd_out">:</i><i class="mayhen_invitation_ladjihe38udhd_out">0</i><i class="mayhen_invitation_ladjihe38udhd_out">0</i></b><b style="opacity: 1;"><i class="mayhen_invitation_ladjihe38udhd_in">1</i><i class="mayhen_invitation_ladjihe38udhd_in">7</i><i class="mayhen_invitation_ladjihe38udhd_in">:</i><i class="mayhen_invitation_ladjihe38udhd_in">0</i><i class="mayhen_invitation_ladjihe38udhd_in">0</i></b></span></span><span class="mayhen_invitation_ladjihe38udhd_ladipage-animated-headline mayhen_invitation_ladjihe38udhd_scale mayhen_invitation_ladjihe38udhd_ladipage-animated-headline-duplicate"><span class="mayhen_invitation_ladjihe38udhd_ladipage-animated-words-wrapper" data-word='["17:00"]'>11:00</span></span></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE279"><h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">Tháng {{ $weddingMonth }}</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE280"><h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">{{ $weddingYear }}</h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP95">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE284"><h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">{{ mb_strtolower(mb_substr($groomName, 0, 1)) }}</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE285">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">&amp;<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE286"><h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">{{ mb_strtolower(mb_substr($brideName, 0, 1)) }}</h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_button_keoXuong mayhen_invitation_ladjihe38udhd_ladi-animation" id="GROUP138">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-animation" id="BOX76">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-animation" data-action="true" id="HEADLINE334">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Kéo Xuống</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HTML_CODE9">
<div class="mayhen_invitation_ladjihe38udhd_ladi-html-code">

</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-animation" id="SHAPE21">
<div class="mayhen_invitation_ladjihe38udhd_ladi-shape mayhen_invitation_ladjihe38udhd_ladi-transition"><svg class="" enable-background="new 0 0 256 256" fill="#3B5E4F" height="100%" preserveaspectratio="none" version="1.1" viewbox="0 0 256 256" width="100%" x="0px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" y="0px">
<g>
<path d="M201.893,84.946c3.369,0,6.107,2.732,6.107,6.107c0,3.373-2.738,6.107-6.107,6.107   c-3.379,0-6.107-2.734-6.107-6.107C195.785,87.679,198.514,84.946,201.893,84.946L201.893,84.946z">
</path>
<path d="M183.424,84.946c3.365,0,6.106,2.732,6.106,6.107c0,3.373-2.741,6.107-6.106,6.107   c-3.378,0-6.113-2.734-6.113-6.107C177.311,87.679,180.046,84.946,183.424,84.946L183.424,84.946z">
</path>
<path d="M183.424,103.415c3.365,0,6.106,2.734,6.106,6.115c0,3.371-2.741,6.105-6.106,6.105   c-3.378,0-6.113-2.734-6.113-6.105C177.311,106.149,180.046,103.415,183.424,103.415L183.424,103.415z">
</path>
<path d="M164.941,103.415c3.374,0,6.117,2.734,6.117,6.115c0,3.371-2.743,6.105-6.117,6.105   c-3.367,0-6.107-2.734-6.107-6.105C158.834,106.149,161.574,103.415,164.941,103.415L164.941,103.415z">
</path>
<path d="M164.941,121.892c3.374,0,6.117,2.734,6.117,6.109c0,3.371-2.743,6.105-6.117,6.105   c-3.367,0-6.107-2.734-6.107-6.105C158.834,124.626,161.574,121.892,164.941,121.892L164.941,121.892z">
</path>
<path d="M146.47,121.892c3.378,0,6.11,2.734,6.11,6.109c0,3.371-2.732,6.105-6.11,6.105   c-3.366,0-6.105-2.734-6.105-6.105C140.364,124.626,143.104,121.892,146.47,121.892L146.47,121.892z">
</path>
<path d="M146.47,140.364c3.378,0,6.11,2.734,6.11,6.105c0,3.379-2.732,6.115-6.11,6.115   c-3.366,0-6.105-2.736-6.105-6.115C140.364,143.099,143.104,140.364,146.47,140.364L146.47,140.364z">
</path>
<path d="M128,140.364c3.379,0,6.109,2.734,6.109,6.105c0,3.379-2.73,6.115-6.109,6.115s-6.109-2.736-6.109-6.115   C121.891,143.099,124.621,140.364,128,140.364L128,140.364z">
</path>
<path d="M128,158.837c3.379,0,6.109,2.736,6.109,6.111c0,3.373-2.73,6.105-6.109,6.105s-6.109-2.732-6.109-6.105   C121.891,161.573,124.621,158.837,128,158.837L128,158.837z">
</path>
<path d="M109.53,121.892c3.366,0,6.105,2.734,6.105,6.109c0,3.371-2.739,6.105-6.105,6.105   c-3.378,0-6.11-2.734-6.11-6.105C103.42,124.626,106.152,121.892,109.53,121.892L109.53,121.892z">
</path>
<path d="M109.53,140.364c3.366,0,6.105,2.734,6.105,6.105c0,3.379-2.739,6.115-6.105,6.115   c-3.378,0-6.11-2.736-6.11-6.115C103.42,143.099,106.152,140.364,109.53,140.364L109.53,140.364z">
</path>
<path d="M91.059,103.415c3.367,0,6.109,2.734,6.109,6.115c0,3.371-2.742,6.105-6.109,6.105   c-3.374,0-6.117-2.734-6.117-6.105C84.941,106.149,87.685,103.415,91.059,103.415L91.059,103.415z">
</path>
<path d="M91.059,121.892c3.367,0,6.109,2.734,6.109,6.109c0,3.371-2.742,6.105-6.109,6.105   c-3.374,0-6.117-2.734-6.117-6.105C84.941,124.626,87.685,121.892,91.059,121.892L91.059,121.892z">
</path>
<path d="M72.576,84.946c3.379,0,6.113,2.732,6.113,6.107c0,3.373-2.734,6.107-6.113,6.107   c-3.365,0-6.106-2.734-6.106-6.107C66.47,87.679,69.211,84.946,72.576,84.946L72.576,84.946z">
</path>
<path d="M72.576,103.415c3.379,0,6.113,2.734,6.113,6.115c0,3.371-2.734,6.105-6.113,6.105   c-3.365,0-6.106-2.734-6.106-6.105C66.47,106.149,69.211,103.415,72.576,103.415L72.576,103.415z">
</path>
<path d="M54.108,84.946c3.378,0,6.106,2.732,6.106,6.107c0,3.373-2.729,6.107-6.106,6.107   c-3.38,0-6.108-2.734-6.108-6.107C48,87.679,50.729,84.946,54.108,84.946L54.108,84.946z">
</path>
</g>
</svg></div>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION24">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP80">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX50">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP81">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE231">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">sun</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE232">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">mon</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE233">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">tue</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE234">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">wed</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE235">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">thur</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE236">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">fri</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE237">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">sat</h3>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP82">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP83">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE238">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition"></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE239">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition"></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE240">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition"></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE241">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition"></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE242">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition"></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE243">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">1</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE244">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">2</h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP84">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE245">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">3</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE246">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">4</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE247">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">5</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE248">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">6</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE249">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">7</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE250">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">8</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE251">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">9</h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP85">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE252">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">10</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE253">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">11</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE254">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">12</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE255">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">13</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE256">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">14</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE257">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">15</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE258">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">16</h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP86">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE259">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">17</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE260">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">18</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE261">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">19</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE262">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">20</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE263">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">21</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE264">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">22</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE265">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">23</h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP87">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE266">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">24</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE267">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">25</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE268">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">26</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE269">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">27</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE270">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">28</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE271">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">29</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE272">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">30</h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE67">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE68">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE69">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE70">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE71">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE72">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE73">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE74">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE75">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE76">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE77">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE78">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE79">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE80">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE273">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Tháng 11</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE274">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">2025</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="SHAPE14">
<div class="mayhen_invitation_ladjihe38udhd_ladi-shape mayhen_invitation_ladjihe38udhd_ladi-transition"><svg class="" fill='url("#SHAPE14_desktop_gradient")' height="100%" preserveaspectratio="none" viewbox="0 -960 960 960" width="100%" xmlns="http://www.w3.org/2000/svg">
<defs id="SHAPE14_defs">
<lineargradient gradienttransform="rotate(0)" id="SHAPE14_desktop_gradient">
<stop offset="0%" stop-color="rgba(255, 255, 255, 1)"></stop>
<stop offset="100%" stop-color="rgba(255, 255, 255, 1)"></stop>
</lineargradient>
</defs>
<path d="m480-121-41-37q-105.77-97.12-174.88-167.56Q195-396 154-451.5T96.5-552Q80-597 80-643q0-90.15 60.5-150.58Q201-854 290-854q57 0 105.5 27t84.5 78q42-54 89-79.5T670-854q89 0 149.5 60.42Q880-733.15 880-643q0 46-16.5 91T806-451.5Q765-396 695.88-325.56 626.77-255.12 521-158l-41 37Zm0-79q101.24-93 166.62-159.5Q712-426 750.5-476t54-89.14q15.5-39.13 15.5-77.72 0-66.14-42-108.64T670.22-794q-51.52 0-95.37 31.5T504-674h-49q-26-56-69.85-88-43.85-32-95.37-32Q224-794 182-751.5t-42 108.82q0 38.68 15.5 78.18 15.5 39.5 54 90T314-358q66 66 166 158Zm0-297Z">
</path>
</svg></div>
</div><a class="mayhen_invitation_ladjihe38udhd_ladi-element" data-replace-href="https://calendar.app.google/q6HqBodZvB5pk1AL9" href="https://calendar.app.google/q6HqBodZvB5pk1AL9" id="BUTTON10" target="_blank">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-shape" id="BUTTON_SHAPE10">
<div class="mayhen_invitation_ladjihe38udhd_ladi-shape"><svg class="" fill="rgba(255, 251, 247, 1)" height="100%" preserveaspectratio="none" viewbox="0 0 1672.2646 1896.0833" width="100%" xmlns="http://www.w3.org/2000/svg">
<path d="M128 1664h288v-288H128v288zm352 0h320v-288H480v288zm-352-352h288V992H128v320zm352 0h320V992H480v320zM128 928h288V640H128v288zm736 736h320v-288H864v288zM480 928h320V640H480v288zm768 736h288v-288h-288v288zm-384-352h320V992H864v320zM512 448V160q0-13-9.5-22.5T480 128h-64q-13 0-22.5 9.5T384 160v288q0 13 9.5 22.5T416 480h64q13 0 22.5-9.5T512 448zm736 864h288V992h-288v320zM864 928h320V640H864v288zm384 0h288V640h-288v288zm32-480V160q0-13-9.5-22.5T1248 128h-64q-13 0-22.5 9.5T1152 160v288q0 13 9.5 22.5t22.5 9.5h64q13 0 22.5-9.5t9.5-22.5zm384-64v1280q0 52-38 90t-90 38H128q-52 0-90-38t-38-90V384q0-52 38-90t90-38h128v-96q0-66 47-113T416 0h64q66 0 113 47t47 113v96h384v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h128q52 0 90 38t38 90z">
</path>
</svg></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-headline" id="BUTTON_TEXT10">
<p class="mayhen_invitation_ladjihe38udhd_ladi-headline">     Thêm vào lịch</p>
</div>
</div>
</a>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION6">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP9">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX5">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX6">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX7">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX8">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" data-daily-end="23:59:59" data-daily-start="00:00:00" data-date-end="1793120399000" data-date-start="1793034000000" data-type="daily" id="COUNTDOWN1">
<div class="mayhen_invitation_ladjihe38udhd_ladi-countdown">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" data-item-type="day" id="COUNTDOWN_ITEM1">
<div class="mayhen_invitation_ladjihe38udhd_ladi-countdown-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-countdown-text"><span>00</span></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" data-item-type="hour" id="COUNTDOWN_ITEM2">
<div class="mayhen_invitation_ladjihe38udhd_ladi-countdown-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-countdown-text"><span>07</span></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" data-item-type="minute" id="COUNTDOWN_ITEM3">
<div class="mayhen_invitation_ladjihe38udhd_ladi-countdown-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-countdown-text"><span>46</span></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" data-item-type="seconds" id="COUNTDOWN_ITEM4">
<div class="mayhen_invitation_ladjihe38udhd_ladi-countdown-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-countdown-text"><span>03</span></div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE4">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE5">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE6">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE287">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">:<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE288">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">:<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE289">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">:<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP96">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH9">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">THỜI GIAN ĐẾM NGƯỢC<br/></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH53">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Countdown time<br/></div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP97">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE291">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Ngày<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE292">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Giờ<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE293">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Phút<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE294">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Giây<br/></h3>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION4">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE117">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE118">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP125">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP116">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE115">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX60">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH66">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Chú rể</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE300">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">NGỌC SƠN<br/></h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH70">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Chú rể là một chàng trai điềm đạm, chân thành và
                                luôn sống hết lòng vì gia đình và những người thân yêu. Với trái tim ấm áp và sự chín
                                chắn trong suy nghĩ, anh đã và đang là chỗ dựa vững chắc cho cô dâu cũng như là người
                                bạn đồng hành lý tưởng trong chặng đường hôn nhân sắp tới.<br/></div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP126">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP127">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE122">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX67">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH75">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Cô dâu</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE304">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">THÚY QUỲNH</h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH76">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Cô dâu là một người con gái dịu dàng, tinh tế và
                                luôn mang đến năng lượng tích cực cho những người xung quanh. Với nụ cười ấm áp và trái
                                tim chân thành, cô không chỉ là niềm tự hào của gia đình mà còn là người bạn đời lý
                                tưởng, đồng hành cùng chú rể trong hành trình xây dựng hạnh phúc.<br/></div>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION16">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE99">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX34">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE55">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE98">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP143">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE81">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH81">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Ngày {{ $weddingDay }} - {{ $weddingMonth }} - {{ $weddingYear }}<br/><span style="text-transform: uppercase;">Tại tư gia nhà trai</span><br/></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE342">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">10:00<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE155">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE343">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">lễ Thành hôn<br/></h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP160">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE87">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH93">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Ngày {{ $weddingDay }} - {{ $weddingMonth }} - {{ $weddingYear }}<br/><span style="text-transform: uppercase;">Tại tư gia nhà gái</span><br/></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE357">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">08:30<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE164">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE358">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">lễ vu quy<br/></h3>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION13">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE46">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE44">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX21">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE47">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE68">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Rất hân hạnh được đón tiếp!<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP39">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX24">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_circle_image" id="BOX25">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE51">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE69">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Ngọc Sơn</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE70">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Thúy Quỳnh</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE71">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Và</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" data-action="true" id="BUTTON9">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-headline" id="BUTTON_TEXT9">
<p class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">CHỈ ĐƯỜNG</p>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE100">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE101">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH59">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Tới dự BUỔI TIỆC CHUNG VUI CÙNG GIA ĐÌNH chúng tôi<br/>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_class_tenKhachMoi" id="HEADLINE66"><h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition"><div style="display: inline-block;  padding:2px 20px;border-radius:15px; border:0px solid #ffffff;"><span style="display: block;  word-wrap: break-word; max-width: 100%;">Bạn và Gia Đình</span></div><br/></h3></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE67">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">TRÂN TRỌNG KÍNH MỜI</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP150">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH23">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition"><span style="font-size: 18px;">VÀO LÚC 11 GIỜ 00
                                    <br/></span>{{ mb_strtoupper($weddingDayOfWeek) }}, NGÀY {{ $weddingDay }} THÁNG {{ $weddingMonth }} NĂM {{ $weddingYear }}<br/></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH24">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">{!! nl2br(e($brideEventAddress)) !!}
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH25">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">(Tức ngày 12 tháng 09 năm Ất Tỵ)</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BUTTON19">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-headline" id="BUTTON_TEXT19">
<p class="mayhen_invitation_ladjihe38udhd_ladi-headline">TIỆC CƯỚI NHÀ GÁI</p>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH83">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Trung tâm tiệc cưới<br/><span style="font-size: 28px;">HOÀNG GIA</span><br/></div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP151">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH84">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition"><span style="font-size: 18px;">VÀO LÚC 17 GIỜ 00
                                    <br/></span>{{ mb_strtoupper($weddingDayOfWeek) }}, NGÀY {{ $weddingDay }} THÁNG {{ $weddingMonth }} NĂM {{ $weddingYear }}<br/></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH85">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">{!! nl2br(e($groomEventAddress)) !!}</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH86">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">(Tức ngày 12 tháng 09 năm Ất Tỵ)</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BUTTON20">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-headline" id="BUTTON_TEXT20">
<p class="mayhen_invitation_ladjihe38udhd_ladi-headline">TIỆC CƯỚI NHÀ TRAI</p>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH87">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Trung tâm tiệc cưới<br/><span style="font-size: 28px;">SUNRISE</span><br/></div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP152">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP153">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE346">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">Nhà trai</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH88">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph">Ông: Tạ Duy Hiển<br/>Bà: Trần Thị Hòa<br/></div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="LINE83">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line">
<div class="mayhen_invitation_ladjihe38udhd_ladi-line-container"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP154">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE347">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">Nhà GÁi</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH89">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph">Ông: Trần Hữu Ninh<br/>Bà: Ngô Thị Dung<br/></div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION7" style="height: 1326.2px;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE106">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE105">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP11">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX9">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE7">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP12">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE12">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Thúy Quỳnh<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE13">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Ngọc sơn<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE14">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">&amp;<br/></h3>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HTML_CODE1">
<div class="mayhen_invitation_ladjihe38udhd_ladi-html-code">



<div id="previewContainer"></div>
<div class="mayhen_invitation_ladjihe38udhd_hidden" id="deleteDialog">
<p>Bạn có chắc chắn muốn xóa chữ ký này?</p>
<div id="deleteDialog_group_button">
<button id="confirmDelete">Xóa</button>
<button class="mayhen_invitation_ladjihe38udhd_btn mayhen_invitation_ladjihe38udhd_btn-warning" id="blockIP">Chặn chữ ký từ người này</button><button id="cancelDelete">Hủy</button>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" data-action="true" id="BUTTON2">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-headline" id="BUTTON_TEXT2">
<p class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">NHẤN ĐỂ KÝ TÊN<br/></p>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP13" style="display:none !important; visibility:hidden !important; height:0 !important; overflow:hidden !important;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX10">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE15">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Khu vực ký tên<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HTML_CODE2">
<div class="mayhen_invitation_ladjihe38udhd_ladi-html-code">

<div id="wrapCanvas">
<canvas height="250" id="signatureCanvas" style="border:1px solid rgba(230, 176, 102, 1.0);" width="400"></canvas>
</div>



</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP14">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_Signature_BTN_xoa" id="BUTTON3">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-headline" id="BUTTON_TEXT3">
<p class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Xóa</p>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_Signature_BTN_xem" id="BUTTON4">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-headline" id="BUTTON_TEXT4">
<p class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Xem thử</p>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_Signature_BTN_luu" data-action="true" id="BUTTON5">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-headline" id="BUTTON_TEXT5">
<p class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">Lưu Chữ Ký</p>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH10">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Để lại chữ ký của Bạn và cùng chúng mình <br/>lưu giữ kỷ
                        niệm hạnh phúc này nhé!!<br/></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH11">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Lưu Bút</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH12">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Signature</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE8">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE9">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION20">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE60" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE61" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE62" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE63" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE64" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE65" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE66" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX42">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH41">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Wedding album</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH42">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Our memories</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE107">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE108">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE109" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE110" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE114" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE119" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_photoBookImage" id="IMAGE120" style="cursor: pointer;">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION8">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX52">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE13">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE17">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE10">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE11">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE12">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE14">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE15">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE16">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH13">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Xác nhận và gửi lời chúc<br/></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH14">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Vui lòng xác nhận sự tham dự của bạn để chúng tôi có thể
                        chuẩn bị và đón tiếp bạn một cách chu đáo nhất.<br/>Sự hiện diện của bạn là niềm vinh hạnh đối
                        với chúng tôi. Trân trọng!<br/></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP90">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="FORM2">
<form action="" autocomplete="off" class="mayhen_invitation_ladjihe38udhd_ladi-form" method="post">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BUTTON6">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-headline" id="BUTTON_TEXT6">
<p class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">XÁC NHẬN THAM DỰ</p>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_name1" id="FORM_ITEM3">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item"><input autocomplete="off" class="mayhen_invitation_ladjihe38udhd_ladi-form-control" name="name" placeholder="Tên của bạn là gì? " required="" tabindex="1" type="text" value=""/></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="FORM_ITEM5">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item"><select class="mayhen_invitation_ladjihe38udhd_ladi-form-control mayhen_invitation_ladjihe38udhd_ladi-form-control-select" data-selected="" name="form_item6" required="" tabindex="2">
<option class="mayhen_invitation_ladjihe38udhd_ladi-hidden" value="">Bạn sẽ tham dự chứ?</option>
<option value="Rất vui khi được tham dự">Rất vui khi được tham dự
                                                </option>
<option value="Rất tiếc không thể đến">Rất tiếc không thể đến</option>
</select></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="FORM_ITEM7">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item"><input autocomplete="off" class="mayhen_invitation_ladjihe38udhd_ladi-form-control" name="form_item14" placeholder="Số lượng người tham gia?" tabindex="3" type="number" value=""/></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="FORM_ITEM8">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item mayhen_invitation_ladjihe38udhd_ladi-form-checkbox mayhen_invitation_ladjihe38udhd_ladi-form-checkbox-horizontal">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-checkbox-box-item">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-checkbox-item"><input name="form_item8" tabindex="4" type="radio" value="Cô Dâu"/><span data-checked="false">Cô Dâu</span></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-checkbox-item"><input name="form_item8" tabindex="4" type="radio" value="Chú Rể"/><span data-checked="false">Chú Rể</span></div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="FORM_ITEM26">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item"><textarea autocomplete="off" class="mayhen_invitation_ladjihe38udhd_ladi-form-control" name="form_item26" placeholder="Để lại lời nhắn" tabindex="5"></textarea></div>
</div>
</div>
</form>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH46">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph">  Bạn là khách của ?<br/></div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH55">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Confirm and send wishes<br/></div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION9">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE20">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE18">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE19">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX53">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HTML_CODE8">
<div class="mayhen_invitation_ladjihe38udhd_ladi-html-code"><!--messaeboxx-->


<div id="MessageBox">
@if($wishes && $wishes->count() > 0)
    @foreach($wishes as $w)
        <div class="mayhen_invitation_ladjihe38udhd_MessageBox-item">
            <h2 class="mayhen_invitation_ladjihe38udhd_MessageBox-item-name">{{ $w->guest_name ?? 'Khách mời' }}</h2>
            <p class="mayhen_invitation_ladjihe38udhd_MessageBox-item-message">{{ $w->wishes ?? '' }}</p>
        </div>
    @endforeach
@else
    <div class="mayhen_invitation_ladjihe38udhd_MessageBox-item"><h2 class="mayhen_invitation_ladjihe38udhd_MessageBox-item-name">Quỳnh Anh</h2><p class="mayhen_invitation_ladjihe38udhd_MessageBox-item-message">Chúc vợ chồng trăm năm hạnh phúc!</p></div>
    <div class="mayhen_invitation_ladjihe38udhd_MessageBox-item"><h2 class="mayhen_invitation_ladjihe38udhd_MessageBox-item-name">Bách Hợp</h2><p class="mayhen_invitation_ladjihe38udhd_MessageBox-item-message">Mừng đám cưới của hai bạn nhé!!!</p></div>
    <div class="mayhen_invitation_ladjihe38udhd_MessageBox-item"><h2 class="mayhen_invitation_ladjihe38udhd_MessageBox-item-name">Minh Nhật</h2><p class="mayhen_invitation_ladjihe38udhd_MessageBox-item-message">Trăm năm hạnh phúc nha 2 bạn :3</p></div>
    <div class="mayhen_invitation_ladjihe38udhd_MessageBox-item"><h2 class="mayhen_invitation_ladjihe38udhd_MessageBox-item-name">Bảo Ngọc</h2><p class="mayhen_invitation_ladjihe38udhd_MessageBox-item-message">Chúc hai bạn luôn vui vẻ và ngập tràn tình yêu ❤️</p></div>
@endif
</div>

</div></div><div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE21">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE17">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">LỜI CHÚC TỪ KHÁCH MỜI<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE22">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE23">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH56">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph mayhen_invitation_ladjihe38udhd_ladi-transition">Wishes from guests</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP101">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="FORM3">
<form action="" autocomplete="off" class="mayhen_invitation_ladjihe38udhd_ladi-form" method="post">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BUTTON11">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button mayhen_invitation_ladjihe38udhd_ladi-transition">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-headline" id="BUTTON_TEXT11">
<p class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">GỬI LỜI CHÚC</p>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_name2" id="FORM_ITEM11">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item"><input autocomplete="off" class="mayhen_invitation_ladjihe38udhd_ladi-form-control" name="name" placeholder="Tên Bạn là..." required="" tabindex="6" type="text" value=""/></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="FORM_ITEM12">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item"><textarea autocomplete="off" class="mayhen_invitation_ladjihe38udhd_ladi-form-control" name="message" placeholder="" required="" tabindex="13"></textarea></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="FORM_ITEM14">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-form-item"><select class="mayhen_invitation_ladjihe38udhd_ladi-form-control mayhen_invitation_ladjihe38udhd_ladi-form-control-select" data-selected="" name="form_item12" required="" tabindex="12">
<option class="mayhen_invitation_ladjihe38udhd_ladi-hidden" value="">Ai sẽ thấy lời chúc của bạn?
                                                </option>
<option value="Mọi người">Mọi người</option>
<option value="Chỉ cô dâu, chú rể">Chỉ cô dâu, chú rể</option>
</select></div>
</div>
</div>
</form>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="PARAGRAPH63">
<div class="mayhen_invitation_ladjihe38udhd_ladi-paragraph">Gửi lời chúc đến CDCR<br/></div>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION27">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE132">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE133">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP129">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE315">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">Share</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE316">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">Love</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE317">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">THE</h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX70">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX71">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" data-action="true" id="BUTTON21">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button">
<div class="mayhen_invitation_ladjihe38udhd_ladi-button-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element mayhen_invitation_ladjihe38udhd_ladi-button-headline" id="BUTTON_TEXT21">
<p class="mayhen_invitation_ladjihe38udhd_ladi-headline">Gửi quà yêu thương<br/>đến CẶP đôi</p>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION17">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP56">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX30">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP57">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX31">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HTML_CODE5">
<div class="mayhen_invitation_ladjihe38udhd_ladi-html-code"><iframe allowfullscreen="" height="250" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3706.3547153838763!2d104.9265176!3d21.7277699!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31335a10a32f5069%3A0x1a125dd059555d3f!2zVGnhu4djIEPGsOG7m2kgSG_DoG5nIEdpYQ!5e0!3m2!1svi!2s!4v1760807167741!5m2!1svi!2s" style="border:0;" width="400"></iframe></div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE164">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">TIỆC CƯỚI NHÀ GÁI<br/></h3>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP157">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX81">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP158">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX82">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HTML_CODE10">
<div class="mayhen_invitation_ladjihe38udhd_ladi-html-code"><iframe allowfullscreen="" height="250" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3706.630608571415!2d104.9023143748835!3d21.71706508011008!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31335b4144d13883%3A0x60923c51e77c8728!2zVHJ1bmcgdMOibSB04buVIGNo4bupYyBz4buxIGtp4buHbiB0aeG7h2MgY8aw4bubaSBTVU5SSVNFIFBBTEFDRQ!5e0!3m2!1svi!2s!4v1760807268278!5m2!1svi!2s" style="border:0;" width="400"></iframe></div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE354">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline mayhen_invitation_ladjihe38udhd_ladi-transition">TIỆC CƯỚI NHÀ TRAI<br/></h3>
</div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-section" id="SECTION26">
<div class="mayhen_invitation_ladjihe38udhd_ladi-section-background"></div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-container">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="GROUP128">
<div class="mayhen_invitation_ladjihe38udhd_ladi-group">
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX68">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="BOX69">
<div class="mayhen_invitation_ladjihe38udhd_ladi-box mayhen_invitation_ladjihe38udhd_ladi-transition"></div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE123">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE313">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">Thank you!<br/></h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="HEADLINE314">
<h3 class="mayhen_invitation_ladjihe38udhd_ladi-headline">Sự hiện diện của bạn là niềm vinh hạnh<br/>cho gia đình chúng tôi.
                                <br/>Chân thành cảm ơn!<br/>
</h3>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE124">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE125">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE126">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE127">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE128">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE129">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE130">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
<div class="mayhen_invitation_ladjihe38udhd_ladi-element" id="IMAGE131">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image">
<div class="mayhen_invitation_ladjihe38udhd_ladi-image-background"></div>
</div>
</div>
</div>
</div>

</div></div>
@endsection

@section('modals')
<nav class="mayhen_invitation_ladjihe38udhd_widget-nav" aria-label="Truy cập nhanh">
  <button id="mayhen-w18-menu" type="button" title="Mở menu" aria-label="Mở menu">☰</button>
  <button id="mayhen-w18-music" type="button" title="Nhạc nền" aria-label="Bật/tắt nhạc">♫</button>
  <button id="mayhen-w18-share" type="button" title="Chia sẻ thiệp" aria-label="Chia sẻ thiệp">↗</button>
</nav>
<div id="mayhen-w18-overlay" class="mayhen_invitation_ladjihe38udhd_dialog-overlay" role="dialog" aria-modal="true" aria-hidden="true" aria-label="Hộp thoại thiệp cưới">
  <div class="mayhen_invitation_ladjihe38udhd_dialog-panel" id="mayhen-w18-modal-body"></div>
</div>
<div id="mayhen-w18-toast" class="mayhen_invitation_ladjihe38udhd_dialog-toast" role="status" aria-live="polite"></div>
<audio id="mayhen-w18-audio" loop preload="none"></audio>
@endsection

@push('scripts')
    <script src="{{ route('templates.asset', ['slug' => 'luu-but-ngay-chung-doi', 'path' => 'script.js']) }}?v={{ $jsVersion }}" defer></script>
@endpush
