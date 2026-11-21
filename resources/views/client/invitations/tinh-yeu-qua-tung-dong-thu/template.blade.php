@extends('client.layouts.invitation')

@php
    $templateSlug = 'tinh-yeu-qua-tung-dong-thu';
    $assetBase = "/mau-thiep/{$templateSlug}/assets";

    $isEmbed = ($isEmbed ?? false)
        || request()->boolean('embed')
        || request()->boolean('in_modal')
        || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);

    $extra = (isset($wedding) && is_array($wedding->extra_data)) ? $wedding->extra_data : [];
    $invConfig = (isset($invitation) && is_array($invitation->custom_config))
        ? $invitation->custom_config
        : (isset($invitation) ? (json_decode((string) ($invitation->custom_config ?? '{}'), true) ?: []) : []);

    $isCuratedSeven = !empty($isCurated)
        || (($template->category ?? '') === 'curated')
        || !empty($invConfig['curated_layout']);

    $val = function (string $key, mixed $default = '') use ($wedding, $extra, $invConfig) {
        if (isset($wedding) && isset($wedding->{$key}) && $wedding->{$key} !== '' && $wedding->{$key} !== null) {
            return $wedding->{$key};
        }
        if (array_key_exists($key, $extra) && $extra[$key] !== '' && $extra[$key] !== null) {
            return $extra[$key];
        }
        if (array_key_exists($key, $invConfig) && $invConfig[$key] !== '' && $invConfig[$key] !== null) {
            return $invConfig[$key];
        }
        return $default;
    };

    $resolveAsset = function (?string $path, string $fallbackRelative = 'images/hero-main.webp') use ($assetBase, $templateSlug) {
        $fallbackUrl = "{$assetBase}/" . ltrim($fallbackRelative, '/');
        if (empty($path) || !is_string($path)) {
            return $fallbackUrl;
        }
        $trimmed = trim($path);
        if (preg_match('#^https?://#i', $trimmed)) {
            return $fallbackUrl;
        }
        if (str_starts_with($trimmed, '/')) {
            return $trimmed;
        }
        $cleanRel = ltrim(preg_replace('#^assets/#i', '', $trimmed), '/');
        $diskCandidate = resource_path("views/client/invitations/{$templateSlug}/assets/{$cleanRel}");
        if (!file_exists($diskCandidate)) {
            return $fallbackUrl;
        }
        return "{$assetBase}/{$cleanRel}";
    };

    // 1. Names & Masthead
    $groomName = (string) $val('groom_name', 'Thành Đạt');
    $brideName = (string) $val('bride_name', 'Ngọc Hân');
    $groomFullName = (string) $val('groom_full_name', 'Lê Thành Đạt');
    $brideFullName = (string) $val('bride_full_name', 'Phạm Ngọc Hân');
    $groomRole = (string) $val('groom_role', 'Trưởng Nam');
    $brideRole = (string) $val('bride_role', 'Ái Nữ');
    $coupleNames = "{$groomName} & {$brideName}";

    $groomWords = preg_split('/\s+/u', trim($groomName)) ?: [$groomName];
    $brideWords = preg_split('/\s+/u', trim($brideName)) ?: [$brideName];
    $groomShort = end($groomWords) ?: $groomName;
    $brideShort = end($brideWords) ?: $brideName;

    $guestName = isset($guest) && !empty($guest->name)
        ? (string) $guest->name
        : (string) request()->query('guest', request()->query('to', 'Quý Khách'));

    $newspaperName = (string) $val('newspaper_name', 'Nhật Báo Tình Yêu');
    $newspaperSub = (string) $val('newspaper_sub', 'SÀI GÒN · SỐ ĐẶC BIỆT TRĂM NĂM');
    $newspaperIssue = (string) $val('newspaper_issue', 'Số 2712');
    $newspaperPrice = (string) $val('newspaper_price', 'Giá: Trăm Năm');
    $heroKicker = (string) $val('hero_kicker', 'Tin vui');
    $heroSubheadline = (string) $val('hero_subheadline', (string) $val('slogan', 'Chính thức nên duyên vợ chồng tại Sài Gòn'));
    $heroCaption = (string) $val('hero_caption', 'Ảnh: Đôi tân lang tân nương trong buổi chiều dạo phố Sài Gòn.');
    $heroLeadCol1 = (string) $val('hero_lead_col1', 'Sài Gòn những ngày cuối năm, giữa tiếng chuông nhà thờ và hương hoa sữa thoảng qua hàng me cổ thụ, đôi bạn trẻ Thành Đạt và Ngọc Hân đã cùng nhau viết nên trang báo đẹp nhất của đời mình.');
    $heroLeadCol2 = (string) $val('hero_lead_col2', 'Toà soạn Nhật Báo Tình Yêu trân trọng phát hành ấn phẩm đặc biệt này, kính báo cùng hai họ và bằng hữu gần xa về ngày vui trăm năm của đôi uyên ương.');
    $invitationGreeting = (string) $val('invitation_greeting', 'Trân trọng kính mời Quý khách tới dự bữa tiệc rượu chung vui cùng gia đình chúng tôi, mừng ngày thành hôn của hai con.');

    // 2. Date & Lunar Date
    $rawDate = $val('wedding_date', '2026-12-27');
    try {
        $carbonDate = \Carbon\Carbon::parse($rawDate);
    } catch (\Throwable $e) {
        $carbonDate = \Carbon\Carbon::parse('2026-12-27');
    }
    $dayNum = $carbonDate->format('d');
    $monthNum = $carbonDate->format('m');
    $yearNum = $carbonDate->format('Y');
    $isoDate = $carbonDate->format('Y-m-d');

    $weekdayMap = [
        0 => 'Chủ Nhật',
        1 => 'Thứ Hai',
        2 => 'Thứ Ba',
        3 => 'Thứ Tư',
        4 => 'Thứ Năm',
        5 => 'Thứ Sáu',
        6 => 'Thứ Bảy',
    ];
    $weekdayLabel = $weekdayMap[(int) $carbonDate->dayOfWeek] ?? 'Chủ Nhật';
    $monthTitleLabel = 'Tháng ' . (int) $monthNum;
    $yearTitleLabel = 'Năm ' . $yearNum;
    $dateDotLabel = "{$weekdayLabel} · {$dayNum}.{$monthNum}.{$yearNum}";
    $weddingDateFormatted = (string) $val('wedding_date_formatted', $dateDotLabel);
    $lunarDate = (string) $val('wedding_date_lunar', (string) $val('wedding_lunar_date', 'Nhằm ngày 19 tháng 11 năm Bính Ngọ'));
    $weddingTimeFormatted = (string) $val('wedding_time_formatted', '18 giờ 00');

    // 3. Parents & Couple
    $groomFather = (string) $val('groom_father', 'Ông Lê Văn Phước');
    $groomMother = (string) $val('groom_mother', 'Bà Trần Thị Thu Cúc');
    $groomAddress = (string) $val('groom_address', (string) $val('groom_parents_address', 'Quận 3, TP. Hồ Chí Minh'));

    $brideFather = (string) $val('bride_father', 'Ông Phạm Minh Trí');
    $brideMother = (string) $val('bride_mother', 'Bà Võ Thị Mỹ Lệ');
    $brideAddress = (string) $val('bride_address', (string) $val('bride_parents_address', 'Quận 5, TP. Hồ Chí Minh'));

    // 4. Images (100% Local Assets)
    $heroImageUrl = $resolveAsset((string) $val('hero_image', (string) $val('cover_photo_url', '')), 'images/hero-main.webp');
    $inviteImageUrl = $resolveAsset((string) $val('invite_image', (string) $val('invitation_photo_url', '')), 'images/invite-main.webp');
    $groomPhotoUrl = $resolveAsset((string) $val('groom_photo', (string) $val('groom_photo_url', '')), 'images/hero-main.webp');
    $bridePhotoUrl = $resolveAsset((string) $val('bride_photo', (string) $val('bride_photo_url', '')), 'images/hero-main.webp');
    $endingImageUrl = $resolveAsset((string) $val('ending_image', (string) $val('footer_photo_url', '')), 'images/ending-main.webp');

    // 5. Love Story (3 Newspaper Reportage Columns)
    $storyTitle = (string) $val('story_title', 'Chuyện Tình Phố Cũ');
    $storyQuote = (string) $val('story_quote', (string) $val('couple_quote', 'Ba kỳ phóng sự ghi lại hành trình từ buổi chiều mưa dưới hàng me đường Duy Tân tới ngày đôi uyên ương chính thức về chung một mái nhà.'));

    $defaultStories = [
        [
            'year' => 'Thu 2019 · Hàng me đường Duy Tân',
            'title' => 'Chiều mưa bên hiên tiệm sách cũ',
            'description' => 'Cơn mưa bất chợt chiều Sài Gòn đã giữ chân hai người lạ dưới cùng một mái hiên. Ly cà phê vợt nóng hổi và cuốn tiểu thuyết cũ mở ra câu chuyện không hồi kết.',
            'photo' => "{$assetBase}/images/story-01.webp",
        ],
        [
            'year' => 'Xuân 2022 · Đường hoa Nguyễn Huệ',
            'title' => 'Những vòng xe qua khắp phố phường',
            'description' => 'Trên chiếc Vespa màu kem cổ điển, anh chở em đi qua từng con hẻm nhỏ, từng góc phố ngập nắng vàng và những chiều hoàng hôn bên bến Bạch Đằng.',
            'photo' => "{$assetBase}/images/story-02.webp",
        ],
        [
            'year' => 'Đông 2026 · Toà soạn trái tim',
            'title' => 'Lời hẹn ước trăm năm được ấn hành',
            'description' => 'Sau bảy năm đồng hành, chúng mình quyết định ký tên vào bản hợp đồng hạnh phúc trọn đời, mở ra chương mới mang tên Gia Đình.',
            'photo' => "{$assetBase}/images/story-03.webp",
        ],
    ];
    $rawStories = $val('love_story', []);
    $stories = [];
    for ($i = 0; $i < 3; $i++) {
        $item = (is_array($rawStories) && isset($rawStories[$i]) && is_array($rawStories[$i])) ? $rawStories[$i] : [];
        $fallbackStoryImg = sprintf('images/story-%02d.webp', $i + 1);
        $stories[$i] = [
            'year' => !empty($item['year']) ? (string) $item['year'] : (string) $val("love_story.{$i}.year", $defaultStories[$i]['year']),
            'title' => !empty($item['title']) ? (string) $item['title'] : (string) $val("love_story.{$i}.title", $defaultStories[$i]['title']),
            'description' => !empty($item['description']) ? (string) $item['description'] : (string) $val("love_story.{$i}.description", $defaultStories[$i]['description']),
            'photo' => $resolveAsset((string) ($item['photo'] ?? $val("love_story.{$i}.photo", '')), $fallbackStoryImg),
        ];
    }

    // 6. Events (Rạp Hạnh Phúc Marquee) & Saigon Post Office Map
    $marqueeTitle = (string) $val('marquee_title', 'Rạp Hạnh Phúc');
    $marqueeSubtitle = (string) $val('marquee_subtitle', 'Suất chiếu đặc biệt · Trăm năm một lần');

    $vuQuyTitle = (string) $val('vu_quy_title', 'Lễ Vu Quy');
    $vuQuyTime = (string) $val('vu_quy_time', (string) $val('ceremony_time', '08:00'));
    $vuQuyAddress = (string) $val('vu_quy_address', (string) $val('ceremony_address', 'Tại Tư Gia Nhà Gái · Quận 5, TP.HCM'));

    $thanhHonTitle = (string) $val('thanh_hon_title', 'Lễ Thành Hôn');
    $thanhHonTime = (string) $val('thanh_hon_time', (string) $val('ceremony_groom_time', '10:30'));
    $thanhHonAddress = (string) $val('thanh_hon_address', (string) $val('ceremony_groom_address', 'Tại Tư Gia Nhà Trai · Quận 3, TP.HCM'));

    $tiecCuoiTitle = (string) $val('tiec_cuoi_title', 'Tiệc Cưới');
    $partyTime = (string) $val('wedding_time', (string) $val('party_time', '18:00'));
    $rawPartyVenue = (string) ($extra['party_venue'] ?? $extra['venue_name'] ?? $invConfig['party_venue'] ?? $invConfig['venue_name'] ?? '');
    $rawWeddingVenue = (isset($wedding) && !empty($wedding->venue_name) && $wedding->venue_name !== 'Trung Tâm Tiệc Cưới')
        ? (string) $wedding->venue_name
        : '';
    $venueName = $rawPartyVenue !== '' ? $rawPartyVenue : ($rawWeddingVenue !== '' ? $rawWeddingVenue : 'Nhà hàng Hương Xưa – Sảnh Bến Thành');

    $rawPartyAddr = (string) ($extra['event_party_address'] ?? $extra['venue_address'] ?? $invConfig['event_party_address'] ?? $invConfig['venue_address'] ?? '');
    $rawWeddingAddr = (isset($wedding) && !empty($wedding->venue_address) && $wedding->venue_address !== 'TP. Hồ Chí Minh')
        ? (string) $wedding->venue_address
        : '';
    $venueAddress = $rawPartyAddr !== '' ? $rawPartyAddr : ($rawWeddingAddr !== '' ? $rawWeddingAddr : 'Số 68 Đồng Khởi, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh');

    // Split venueName for hand-painted signboard if it contains "–" or "-"
    $venueParts = preg_split('/\s*[–-]\s*/u', $venueName, 2) ?: [$venueName];
    $signboardMain = trim($venueParts[0] ?? $venueName);
    $signboardSub = isset($venueParts[1]) ? ('★ ' . trim($venueParts[1]) . ' ★') : '★ Sảnh Tiệc Hạnh Phúc ★';

    $mapLat = (string) $val('map_lat', '10.7769');
    $mapLng = (string) $val('map_lng', '106.7009');
    $googleMapsUrl = (string) $val('google_maps_url', (string) $val('event_party_map_link', "https://maps.google.com/?q={$mapLat},{$mapLng}"));
    $mapEmbedUrl = 'https://maps.google.com/maps?q=' . urlencode("{$venueName}, {$venueAddress}") . '&t=&z=15&ie=UTF8&iwloc=&output=embed';

    // 7. Calendar & Radio Countdown
    $calendarHeading = (string) $val('calendar_heading', 'Ngày Lành Tháng Tốt');
    $blocNote = (string) $val('bloc_note', 'Ngày hoàng đạo · Trăm năm hạnh phúc');
    $countdownTitle = (string) $val('countdown_title', 'Đếm Ngược Ngày Vui');
    $radioBrand = (string) $val('radio_brand', 'Đài Tình Yêu');

    $targetTimeRaw = preg_match('/^\d{1,2}:\d{2}/', $partyTime, $tm) ? $tm[0] : '18:00';
    try {
        $targetCarbon = \Carbon\Carbon::parse("{$isoDate} {$targetTimeRaw}");
    } catch (\Throwable $e) {
        $targetCarbon = $carbonDate->copy()->setTime(18, 0);
    }
    $targetIso = $targetCarbon->format('Y-m-d\TH:i:sP');

    $gcalStart = $targetCarbon->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
    $gcalEnd = $targetCarbon->copy()->addHours(3)->setTimezone('UTC')->format('Ymd\THis\Z');
    $gcalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . urlencode("Đám cưới {$groomName} & {$brideName}")
        . '&dates=' . $gcalStart . '/' . $gcalEnd
        . '&location=' . urlencode("{$venueName}, {$venueAddress}");

    // 8. Timeline
    $defaultTimeline = [
        ['time' => '17:30', 'title' => 'Đón khách', 'description' => 'Đón tiếp quan khách và chụp hình lưu niệm tại sảnh Bến Thành'],
        ['time' => '18:00', 'title' => 'Làm lễ ra mắt hai họ', 'description' => 'Nghi thức rước cô dâu chú rể, trao nhẫn cưới và rót rượu sâm-panh'],
        ['time' => '18:30', 'title' => 'Khai tiệc', 'description' => 'Nâng ly chúc mừng cùng hai bên gia đình và thưởng thức tiệc tối'],
        ['time' => '20:00', 'title' => 'Chụp ảnh lưu niệm', 'description' => 'Giao lưu âm nhạc Sài Gòn xưa và gửi lời cảm ơn tới từng bàn tiệc'],
    ];
    $rawTimeline = $val('timeline_items', $val('timeline', []));
    $timelineItems = [];
    for ($i = 0; $i < 4; $i++) {
        $row = (is_array($rawTimeline) && isset($rawTimeline[$i]) && is_array($rawTimeline[$i])) ? $rawTimeline[$i] : [];
        $timelineItems[$i] = [
            'time' => !empty($row['time']) ? (string) $row['time'] : (string) $val("timeline_items.{$i}.time", $defaultTimeline[$i]['time']),
            'title' => !empty($row['title']) ? (string) $row['title'] : (string) $val("timeline_items.{$i}.title", $defaultTimeline[$i]['title']),
            'description' => !empty($row['description'] ?? $row['desc'] ?? '')
                ? (string) ($row['description'] ?? $row['desc'])
                : (string) $val("timeline_items.{$i}.description", $defaultTimeline[$i]['description']),
        ];
    }

    // 9. Dresscode (Perforated Saigon Postage Stamps)
    $dresscodeNote = (string) $val('dresscode_note', 'Kính mời Quý khách diện trang phục theo các gam màu Sài Gòn xưa dưới đây để những khung hình kỷ niệm thêm đồng điệu.');
    $defaultPalette = [
        ['name' => 'Kem Giấy', 'hex' => '#F3EBDA', 'denom' => '20đ', 'rot' => '-3.2deg'],
        ['name' => 'Xanh Lam', 'hex' => '#2F5A87', 'denom' => '50đ', 'rot' => '2.4deg'],
        ['name' => 'Đỏ Gạch', 'hex' => '#B4513A', 'denom' => '100đ', 'rot' => '-2.1deg'],
        ['name' => 'Vàng Nghệ', 'hex' => '#D9A33A', 'denom' => '200đ', 'rot' => '3.1deg'],
    ];
    $rawPalette = $val('dresscode_palette', []);
    $dresscodeStamps = [];
    for ($i = 0; $i < 4; $i++) {
        $pItem = (is_array($rawPalette) && isset($rawPalette[$i]) && is_array($rawPalette[$i])) ? $rawPalette[$i] : [];
        $dresscodeStamps[$i] = [
            'name' => !empty($pItem['name']) ? (string) $pItem['name'] : (string) $val("dresscode_palette.{$i}.name", $defaultPalette[$i]['name']),
            'hex' => !empty($pItem['hex']) ? (string) $pItem['hex'] : (string) $val("dresscode_palette.{$i}.hex", $defaultPalette[$i]['hex']),
            'denom' => $defaultPalette[$i]['denom'],
            'rot' => $defaultPalette[$i]['rot'],
        ];
    }

    // 10. Album 10 Photos
    $rawGallery = $val('gallery', []);
    $galleryPhotos = [];
    for ($i = 0; $i < 10; $i++) {
        $candidate = is_array($rawGallery) && !empty($rawGallery[$i])
            ? (string) $rawGallery[$i]
            : (string) $val("gallery.{$i}", '');
        $fallbackImg = sprintf('images/album-%02d.webp', $i + 1);
        $galleryPhotos[$i] = $resolveAsset($candidate, $fallbackImg);
    }

    // 11. Gift Postbox & Postal Money-Order VietQR
    $giftDescription = (string) $val('gift_description', 'Sự hiện diện của Quý khách là món quà quý giá nhất. Nếu muốn gửi quà mừng từ xa, Quý khách có thể chạm vào thùng thư bưu điện dưới đây.');

    $groomBankName = (string) $val('groom_bank_name', (string) $val('bank_name', 'Vietcombank'));
    $groomBankAccount = (string) $val('groom_bank_account', (string) $val('bank_account_number', '1029384756'));
    $groomBankHolder = (string) $val('groom_bank_holder', (string) $val('groom_account_name', 'LE THANH DAT'));
    $rawGroomQr = (string) $val('groom_qr_image', (string) $val('qr_groom_image', ''));
    $groomQrUrl = \App\Services\VietQrService::resolveQrImage(
        !empty($rawGroomQr) ? $resolveAsset($rawGroomQr, 'images/qr-default.webp') : null,
        $groomBankName,
        $groomBankAccount,
        $groomBankHolder,
        "Mung cuoi {$groomShort} {$brideShort}"
    ) ?: $resolveAsset($rawGroomQr, 'images/qr-default.webp');

    $brideBankName = (string) $val('bride_bank_name', 'Techcombank');
    $brideBankAccount = (string) $val('bride_bank_account', '19034567890123');
    $brideBankHolder = (string) $val('bride_bank_holder', (string) $val('bride_account_name', 'PHAM NGOC HAN'));
    $rawBrideQr = (string) $val('bride_qr_image', (string) $val('qr_bride_image', ''));
    $brideQrUrl = \App\Services\VietQrService::resolveQrImage(
        !empty($rawBrideQr) ? $resolveAsset($rawBrideQr, 'images/qr-default.webp') : null,
        $brideBankName,
        $brideBankAccount,
        $brideBankHolder,
        "Mung cuoi {$groomShort} {$brideShort}"
    ) ?: $resolveAsset($rawBrideQr, 'images/qr-default.webp');

    // 12. Ending, Signature, RSVP & Wishes
    $signatureSubtitle = (string) $val('signature_subtitle', 'Kính mời Quý khách để lại chữ ký tay trên tấm bưu thiếp kỷ niệm gửi về cho đôi uyên ương.');
    $rsvpNote = (string) $val('rsvp_note', 'Xin Quý khách vui lòng gửi bức điện tín xác nhận để gia đình chúng tôi chu đáo đón tiếp.');
    $thankYouTitle = (string) $val('thank_you_title', 'Cảm Ơn');
    $thankYouText = (string) $val('thank_you_text', (string) $val('thank_you_quote', 'Sự hiện diện và những lời chúc tốt đẹp của Quý khách là niềm vinh hạnh lớn lao của gia đình chúng tôi. Xin chân thành cảm tạ!'));
    $musicUrl = $resolveAsset((string) $val('music_url', ''), 'wedding-song.mp3');

    // Interactive Endpoints
    $hasRealInvitation = isset($invitation) && !empty($invitation->slug) && empty($isDemo);
    $rsvpEndpoint = !empty($rsvpSubmitUrl)
        ? $rsvpSubmitUrl
        : ($hasRealInvitation && \Illuminate\Support\Facades\Route::has('rsvp.submit')
            ? route('rsvp.submit', ['slug' => $invitation->slug])
            : (\Illuminate\Support\Facades\Route::has('templates.preview.rsvp') ? route('templates.preview.rsvp', ['slug' => $templateSlug]) : ''));
    $wishEndpoint = !empty($wishSubmitUrl)
        ? $wishSubmitUrl
        : ($hasRealInvitation && \Illuminate\Support\Facades\Route::has('invitation.wish.store')
            ? route('invitation.wish.store', ['slug' => $invitation->slug])
            : (\Illuminate\Support\Facades\Route::has('templates.preview.wish') ? route('templates.preview.wish', ['slug' => $templateSlug]) : ''));
    $signatureEndpoint = (isset($invitation) && !empty($invitation->id) && \Illuminate\Support\Facades\Route::has('invitation.signature.save'))
        ? route('invitation.signature.save', ['id' => $invitation->id])
        : '';

    $approvedWishes = (isset($wishes) && $wishes instanceof \Illuminate\Support\Collection) ? $wishes : collect();
    $savedSignatures = is_array($invConfig['signatures'] ?? null) ? $invConfig['signatures'] : [];
@endphp

@section('title', "Thiệp Cưới {$groomName} & {$brideName} — Tình yêu qua từng dòng thư")

@push('styles')
    <link rel="stylesheet" href="{{ asset_v("mau-thiep/{$templateSlug}/assets/styles.css") }}">
@endpush

@section('content')
<div
    id="mayhen_invitation_root"
    class="mayhen_invitation_wrapper mayhen_invitation_body_wrap card-wrapper {{ $isEmbed ? 'is-embed opened' : '' }}"
    data-template-slug="{{ $templateSlug }}"
    data-is-embed="{{ $isEmbed ? '1' : '0' }}"
    data-wedding-date="{{ $isoDate }}"
    data-target-iso="{{ $targetIso }}"
    data-couple-names="{{ $coupleNames }}"
    data-rsvp-endpoint="{{ $rsvpEndpoint }}"
    data-wish-endpoint="{{ $wishEndpoint }}"
    data-signature-endpoint="{{ $signatureEndpoint }}"
    data-csrf-token="{{ csrf_token() }}"
>
    {{-- Audio Player (Standard Mây Hẹn Contract) --}}
    <audio
        id="mayhenBgAudio"
        class="mayhen_invitation_audio"
        data-mayhen-audio
        data-sync-target="music_url"
        src="{{ $musicUrl }}"
        loop
        preload="none"
    ></audio>

    {{-- Floating Vinyl Music Toggle --}}
    <button
        type="button"
        id="mayhenMusicToggle"
        class="mayhen_invitation_music_toggle"
        aria-label="Bật hoặc tắt nhạc nền Sài Gòn xưa"
        aria-pressed="false"
    >
        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6Z"/>
        </svg>
    </button>

    {{-- ==================== KHO HÌNH VẼ SÀI GÒN XƯA (Inline SVG Symbols) ==================== --}}
    <svg class="sg-kho-hinh" width="0" height="0" style="position:absolute;width:0;height:0;overflow:hidden;" aria-hidden="true" focusable="false">
        <defs>
            {{-- Tháp đồng hồ Chợ Bến Thành --}}
            <symbol id="sg-ben-thanh" viewBox="0 0 160 110">
                <g fill="#F3EBDA" stroke="#2A2420" stroke-width="1.6" stroke-linejoin="round">
                    <path d="M8 98 H152 V104 H8 Z" fill="#D9A33A"/>
                    <rect x="14" y="64" width="132" height="34" fill="#F6EBD0"/>
                    <polygon points="10,64 80,46 150,64" fill="#B4513A"/>
                    <polygon points="34,64 80,52 126,64" fill="#C9644C"/>
                    <rect x="60" y="28" width="40" height="36" fill="#F6EBD0"/>
                    <polygon points="56,28 80,12 104,28" fill="#B4513A"/>
                    <line x1="80" y1="4" x2="80" y2="12" stroke-width="2"/>
                    <circle cx="80" cy="4" r="2.2" fill="#B4513A"/>
                    <circle cx="80" cy="42" r="11" fill="#FBF6EA" stroke-width="1.8"/>
                    <circle cx="80" cy="42" r="8.5" fill="none" stroke-width=".7" stroke-dasharray="1.5 2.4"/>
                    <path d="M80 42 L80 35 M80 42 L85 44" stroke-width="1.6" stroke-linecap="round"/>
                    <path d="M24 98 V78 A10 10 0 0 1 44 78 V98 Z" fill="#2A2420"/>
                    <path d="M50 98 V78 A10 10 0 0 1 70 78 V98 Z" fill="#2A2420"/>
                    <path d="M90 98 V78 A10 10 0 0 1 110 78 V98 Z" fill="#2A2420"/>
                    <path d="M116 98 V78 A10 10 0 0 1 136 78 V98 Z" fill="#2A2420"/>
                    <path d="M72 98 V74 A8 8 0 0 1 88 74 V98 Z" fill="#8E3A27"/>
                    <rect x="65" y="54" width="30" height="8" fill="#2F5A87"/>
                    <line x1="69" y1="58" x2="91" y2="58" stroke="#F6EBD0" stroke-width="1.4"/>
                </g>
            </symbol>

            {{-- Xe Vespa cổ treo hoa cưới & bóng bay tim --}}
            <symbol id="sg-vespa" viewBox="0 0 180 116">
                <g stroke="#2A2420" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round">
                    <path d="M38 66 C 24 44, 18 32, 22 22" fill="none" stroke-width="1.1" stroke-dasharray="2 2"/>
                    <path d="M42 66 C 36 46, 36 34, 42 24" fill="none" stroke-width="1.1" stroke-dasharray="2 2"/>
                    <path d="M22 18 C15 10,6 15,12 24 L22 33 L32 24 C38 15,29 10,22 18 Z" fill="#B4513A"/>
                    <path d="M42 20 C36 13,28 17,33 25 L42 33 L51 25 C56 17,48 13,42 20 Z" fill="#D9A33A"/>
                    <ellipse cx="90" cy="105" rx="64" ry="4.5" fill="rgba(42,36,32,.18)" stroke="none"/>
                    <circle cx="50" cy="88" r="15" fill="#2A2420"/>
                    <circle cx="50" cy="88" r="10.5" fill="#FBF6EA"/>
                    <circle cx="50" cy="88" r="4.5" fill="#D9A33A"/>
                    <circle cx="134" cy="88" r="15" fill="#2A2420"/>
                    <circle cx="134" cy="88" r="10.5" fill="#FBF6EA"/>
                    <circle cx="134" cy="88" r="4.5" fill="#D9A33A"/>
                    <path d="M32 86 C30 66,46 56,68 58 L92 60 L98 84 L32 86 Z" fill="#8DBFA6"/>
                    <path d="M42 68 C46 60,58 58,72 60" fill="none" stroke="#FBF6EA" stroke-width="1.6"/>
                    <path d="M88 84 L112 84 C118 84,122 76,120 66 L112 36 C110 30,114 26,122 26" fill="none" stroke-width="3.2"/>
                    <path d="M92 84 L120 84 L128 60 L114 56 L96 76 Z" fill="#8DBFA6"/>
                    <path d="M114 78 C118 66,134 64,146 76 L148 86 L114 86 Z" fill="#8DBFA6"/>
                    <rect x="52" y="50" width="38" height="10" rx="5" fill="#8E3A27"/>
                    <line x1="112" y1="26" x2="130" y2="26" stroke-width="3"/>
                    <circle cx="127" cy="36" r="6" fill="#F8D57A"/>
                    <path d="M133 33 L144 29 M133 39 L144 43" stroke="#D9A33A" stroke-width="1.3"/>
                    <rect x="24" y="60" width="16" height="11" rx="2" fill="#D9A33A"/>
                    <line x1="24" y1="65.5" x2="40" y2="65.5" stroke-width="1.3"/>
                    <circle cx="122" cy="54" r="4" fill="#B4513A"/>
                    <circle cx="128" cy="58" r="3.5" fill="#F8D57A"/>
                    <circle cx="117" cy="59" r="3.2" fill="#FBF6EA"/>
                </g>
            </symbol>

            {{-- Cây me cổ thụ đường Duy Tân --}}
            <symbol id="sg-cay-me" viewBox="0 0 70 90">
                <g stroke="#2A2420" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round">
                    <path d="M35 86 V48 C35 42,26 36,20 32 M35 56 C40 48,48 44,54 40" fill="none" stroke="#5A3D28" stroke-width="3.4"/>
                    <ellipse cx="35" cy="26" rx="28" ry="15" fill="#2F5644"/>
                    <ellipse cx="20" cy="34" rx="16" ry="10" fill="#3E6B55"/>
                    <ellipse cx="50" cy="34" rx="16" ry="10" fill="#3E6B55"/>
                    <ellipse cx="35" cy="18" rx="18" ry="9" fill="#4E7D65"/>
                </g>
            </symbol>

            {{-- Con dấu Bưu điện Sài Gòn --}}
            <symbol id="sg-dau-bd" viewBox="0 0 110 110">
                <g fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="55" cy="55" r="50"/>
                    <circle cx="55" cy="55" r="43" stroke-width="1" stroke-dasharray="3 3"/>
                    <circle cx="55" cy="55" r="31"/>
                    <line x1="7" y1="45" x2="103" y2="45" stroke-width="1.4"/>
                    <line x1="7" y1="65" x2="103" y2="65" stroke-width="1.4"/>
                </g>
                <g fill="currentColor" font-family="Anton, sans-serif" text-anchor="middle">
                    <text x="55" y="25" font-size="11" letter-spacing="2.5">BƯU ĐIỆN</text>
                    <text x="55" y="59" font-size="13" letter-spacing="2">SÀI GÒN</text>
                    <text x="55" y="92" font-size="10" letter-spacing="3">★ HỶ SỰ ★</text>
                </g>
            </symbol>

            {{-- Hoa gạch bông 8 cánh --}}
            <symbol id="sg-bong" viewBox="0 0 36 36">
                <g fill="none" stroke="#2A2420" stroke-width="1.1">
                    <rect x="1.5" y="1.5" width="33" height="33" fill="#F3EBDA"/>
                    <rect x="4.5" y="4.5" width="27" height="27" stroke="#2F5A87"/>
                    <path d="M18 5 C21 12,21 15,18 18 C15 15,15 12,18 5 Z" fill="#B4513A"/>
                    <path d="M18 31 C21 24,21 21,18 18 C15 21,15 24,18 31 Z" fill="#B4513A"/>
                    <path d="M5 18 C12 21,15 21,18 18 C15 15,12 15,5 18 Z" fill="#B4513A"/>
                    <path d="M31 18 C24 21,21 21,18 18 C21 15,24 15,31 18 Z" fill="#B4513A"/>
                    <circle cx="18" cy="18" r="3.4" fill="#D9A33A"/>
                </g>
            </symbol>
        </defs>
    </svg>

    {{-- Lớp lá me rơi nhẹ xuyên suốt trang --}}
    <div class="sg-nen-dong" id="sgNenDong" aria-hidden="true"></div>

    {{-- ==================== MÀN MỞ ĐẦU: TỜ NHẬT BÁO GẤP ĐÔI & XE VESPA (#sgCua) ==================== --}}
    <div
        class="sg-cua UIopenCard mayhen_invitation_opening {{ $isEmbed ? 'an da-mo is-opened' : '' }}"
        id="sgCua"
        data-thoi-gian="2800"
        role="dialog"
        aria-label="Tờ báo tin vui Sài Gòn xưa"
        @if($isEmbed) style="display: none !important;" aria-hidden="true" @endif
    >
        <div class="la-gio" id="sgLaGio" aria-hidden="true"></div>
        <div class="xe-chay" aria-hidden="true"><svg viewBox="0 0 180 116"><use href="#sg-vespa"/></svg></div>

        <div class="sg-to-bao" id="sgToBao">
            {{-- Nửa trên tờ báo gấp --}}
            <div class="nua tren nua-tren">
                <div class="dau-bao manchette-nho"><span>ẤN PHẨM ĐẶC BIỆT</span><span>SỐ TRĂM NĂM</span></div>
                <div class="ten-bao" data-sync-target="newspaper_name">{{ $newspaperName }}</div>
                <div class="ngay-bao phu-bao">SÀI GÒN · <span id="sgNgayCua" data-sync-target="wedding_date_formatted">{{ $weddingDateFormatted }}</span></div>
                <div class="vien-tin tin-dac-biet"><span class="tin-vui">★ TIN VUI TRỌNG ĐẠI ★</span></div>
                <div class="ten-cua" id="sgReCua" data-sync-target="groom_name">{{ $groomName }}</div>
                <div class="va-cua">&amp;</div>
                <div class="ten-cua" id="sgDauCua" data-sync-target="bride_name">{{ $brideName }}</div>

                {{-- Phiếu người nhận dán trên tờ báo --}}
                <div class="sg-nhan-gui nhan-nguoi-nhan">
                    <span class="kinh-gui kinh">Kính gửi</span>
                    <span class="khach-cua ten-khach" id="sgKhachCua" data-sync-target="guest_name">{{ $guestName }}</span>
                </div>
            </div>

            {{-- Nửa dưới tờ báo gấp (Chợ Bến Thành + Hàng me + Vespa) --}}
            <div class="nua duoi nua-duoi">
                <div class="canh tranh-cua" aria-hidden="true">
                    <svg class="me1 cay t" viewBox="0 0 70 90"><use href="#sg-cay-me"/></svg>
                    <svg class="bt cho" viewBox="0 0 160 110"><use href="#sg-ben-thanh"/></svg>
                    <svg class="me2 cay p" viewBox="0 0 70 90"><use href="#sg-cay-me"/></svg>
                    <svg class="xe" viewBox="0 0 180 116"><use href="#sg-vespa"/></svg>
                </div>
                <div class="loi-cua">Ấn phẩm phát hành riêng mừng ngày thành hôn — Kính mời mở báo xem chi tiết.</div>
            </div>
        </div>

        <button type="button" class="sg-nut-mo Btn_opencard Btn_openMusic" id="sgNutMo" data-mayhen-open-trigger>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h13v13a2 2 0 0 0 2 2H6a2 2 0 0 1-2-2z"/><path d="M17 9h3v9a2 2 0 0 1-2 2"/><path d="M7 9h7M7 13h7M7 17h4"/></svg>
            <span>Đọc tin vui</span>
        </button>
        <div class="sg-goi-y">Chạm để mở tờ báo</div>
    </div>

    {{-- ==================== TRANG NHẬT BÁO CHÍNH (.ladi-wraper.sg.sg-trang-bao) ==================== --}}
    <div class="ladi-wraper sg sg-trang-bao" id="sgThiep">

        {{-- ==================== SECTION 1: HERO COVER — TRANG NHẤT & THIỆP BÁO HỶ (data-mayhen-section="hero") ==================== --}}
        <section
            id="mayhen_section_hero"
            data-mayhen-section="hero"
            class="mayhen_invitation_hero"
        >
            {{-- 1A. TRANG NHẤT NHẬT BÁO (#SECTION_DAU) --}}
            <div id="SECTION_DAU" class="ladi-section sg-phan sg-dau">
                <div class="ladi-section-background"></div>
                <div class="ladi-container">
                    {{-- Măng-sét đầu báo --}}
                    <div class="sg-bao-dau sg-manchette sg-hien" data-hieu-ung="in">
                        <div class="so-bao sg-so-bao">
                            <div id="TXT_SO_BAO" class="ClientEditText"><p class="ladi-paragraph" data-sync-target="newspaper_issue">{{ $newspaperIssue }}</p></div>
                            <span class="cham">◆</span>
                            <div id="TXT_XUAT_BAN" class="ClientEditText"><p class="ladi-paragraph">Xuất bản tại Sài Gòn</p></div>
                            <span class="cham">◆</span>
                            <div id="TXT_GIA_BAO" class="ClientEditText"><p class="ladi-paragraph" data-sync-target="newspaper_price">{{ $newspaperPrice }}</p></div>
                        </div>

                        <div class="sg-bao-ten">
                            <div id="TXT_TEN_BAO" class="ClientEditText bao sg-1dong">
                                <h1 class="ladi-headline" data-sync-target="newspaper_name">{{ $newspaperName }}</h1>
                            </div>
                            <div id="TXT_KHAU_HIEU_BAO" class="ClientEditText khau-hieu">
                                <p class="ladi-paragraph" data-sync-target="newspaper_sub">{{ $newspaperSub }}</p>
                            </div>
                        </div>

                        {{-- Hai ô tai báo --}}
                        <div class="sg-tai-ds">
                            <div class="sg-tai trai">
                                <svg aria-hidden="true"><use href="#sg-ben-thanh"/></svg>
                                <div id="TXT_TAI_TRAI" class="ClientEditText"><p class="ladi-paragraph">Ấn phẩm kỷ niệm trăm năm hạnh phúc</p></div>
                            </div>
                            <div class="sg-tai phai">
                                <svg aria-hidden="true"><use href="#sg-cay-me"/></svg>
                                <div id="TXT_TAI_PHAI" class="ClientEditText"><p class="ladi-paragraph">Phát hành ngày lành tháng tốt</p></div>
                            </div>
                        </div>

                        <div class="sg-dong-ngay">
                            <div id="TXT_NGAY_BAO" class="ClientEditText ngay">
                                <p class="ladi-paragraph" data-sync-target="wedding_date_formatted">{{ $weddingDateFormatted }}</p>
                            </div>
                            <div id="TXT_AM_BAO" class="ClientEditText am">
                                <p class="ladi-paragraph" data-sync-target="wedding_date_lunar">{{ $lunarDate }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Tin chính Trang Nhất --}}
                    <div class="sg-tin-chinh">
                        <div class="sg-vien-dau sg-hien" data-hieu-ung="dong-dau">
                            <div id="TXT_TIN_VUI" class="ClientEditText sg-dong-dau">
                                <p class="ladi-headline" data-sync-target="hero_kicker">{{ $heroKicker }}</p>
                            </div>
                        </div>

                        <div class="sg-ten-cap sg-hien" data-hieu-ung="in">
                            <div id="TXT_CHURE_DAU" class="ClientEditText sg-ten sg-1dong">
                                <h2 class="ladi-headline" data-sync-target="groom_name">{{ $groomName }}</h2>
                            </div>
                            <div class="sg-va" aria-hidden="true">&amp;</div>
                            <div id="TXT_CODAU_DAU" class="ClientEditText sg-ten sg-1dong">
                                <h2 class="ladi-headline" data-sync-target="bride_name">{{ $brideName }}</h2>
                            </div>
                        </div>

                        <div id="TXT_TIT_PHU" class="ClientEditText sg-tit sg-hien" data-hieu-ung="len">
                            <p class="ladi-headline" data-sync-target="hero_subheadline">{{ $heroSubheadline }}</p>
                        </div>
                    </div>

                    {{-- Ảnh lớn Trang Nhất + Con dấu ngày cưới --}}
                    <div class="sg-anh-bao sg-hien" data-hieu-ung="rua">
                        <div class="sg-anh">
                            <div id="IMG_DAU" class="ClientEditImage ladi-element" data-lightbox-src="{{ $heroImageUrl }}">
                                <div class="ladi-image">
                                    <div class="ladi-image-background" style="background-image: url('{{ $heroImageUrl }}');"></div>
                                    <img
                                        src="{{ $heroImageUrl }}"
                                        alt="Ảnh trang nhất {{ $coupleNames }}"
                                        class="mayhen_invitation_photo_el"
                                        data-sync-target="hero_image"
                                    >
                                </div>
                            </div>
                            <div id="TXT_CHU_ANH_DAU" class="ClientEditText sg-chu-anh">
                                <p class="ladi-paragraph" data-sync-target="hero_caption">{{ $heroCaption }}</p>
                            </div>
                        </div>

                        {{-- Con dấu đóng góc ảnh --}}
                        <div class="tem-ngay sg-hien" data-hieu-ung="dong-dau">
                            <div>
                                <div id="TXT_TEM_SO" class="ClientEditText so-tem">
                                    <p class="ladi-headline" id="mayhenPostmarkDayMonth">{{ $dayNum }}·{{ $monthNum }}</p>
                                </div>
                                <div id="TXT_TEM_NHAN" class="ClientEditText nhan-tem"><p class="ladi-paragraph">HỶ SỰ</p></div>
                            </div>
                        </div>
                    </div>

                    {{-- Bài viết mở đầu chia 2 cột báo --}}
                    <div class="sg-cot-tin sg-hien" data-hieu-ung="len">
                        <div id="TXT_BAI_MO_1" class="ClientEditText">
                            <p class="ladi-paragraph" data-sync-target="hero_lead_col1">{{ $heroLeadCol1 }}</p>
                        </div>
                        <div id="TXT_BAI_MO_2" class="ClientEditText">
                            <p class="ladi-paragraph" data-sync-target="hero_lead_col2">{{ $heroLeadCol2 }}</p>
                        </div>
                        <div id="TXT_KY_TEN_BAO" class="ClientEditText ky"><p class="ladi-paragraph">— Phóng viên Hạnh Phúc</p></div>
                    </div>

                    {{-- Minh họa xe Vespa chạy dưới trang nhất --}}
                    <svg class="sg-xe-dau" aria-hidden="true"><use href="#sg-vespa"/></svg>
                </div>
            </div>

            <div class="sg-vach sg-tt" aria-hidden="true"></div>

            {{-- 1B. THIỆP BÁO HỶ & BẢNG HIỆU VẼ TAY (#SECTION_MOI) --}}
            <div id="SECTION_MOI" class="ladi-section sg-phan sg-moi">
                <div class="ladi-section-background"></div>
                <div class="ladi-container">
                    <div class="sg-bao-hy sg-hien" data-hieu-ung="phong">
                        <span class="goc tl" aria-hidden="true"></span>
                        <span class="goc tr" aria-hidden="true"></span>
                        <span class="goc bl" aria-hidden="true"></span>
                        <span class="goc br" aria-hidden="true"></span>

                        <div class="sg-bh-cot trai">
                            <div class="chu-hy">★ THIỆP BÁO HỶ ★</div>
                            <div id="TXT_LOI_MOI_DAU" class="ClientEditText trang-trong"><p class="ladi-headline">Trân Trọng Kính Mời</p></div>
                            <div id="TXT_TEN_KHACH" class="ClientEditText khach">
                                <h3 class="ladi-headline" data-sync-target="guest_name">{{ $guestName }}</h3>
                            </div>
                            <div id="TXT_LOI_MOI_THAN" class="ClientEditText toi-du">
                                <p class="ladi-paragraph" data-sync-target="invitation_greeting">{{ $invitationGreeting }}</p>
                            </div>

                            <div class="sg-anh-moi sg-hien" data-hieu-ung="rua">
                                <div id="IMG_MOI" class="ClientEditImage ladi-element" data-lightbox-src="{{ $inviteImageUrl }}">
                                    <div class="ladi-image">
                                        <div class="ladi-image-background" style="background-image: url('{{ $inviteImageUrl }}');"></div>
                                        <img
                                            src="{{ $inviteImageUrl }}"
                                            alt="Ảnh Thiệp Báo Hỷ {{ $coupleNames }}"
                                            class="mayhen_invitation_photo_el"
                                            data-sync-target="invite_image"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sg-bh-cot phai">
                            <div id="TXT_VAO_LUC" class="ClientEditText vao-luc"><p class="ladi-paragraph">Hôn lễ được cử hành vào lúc</p></div>
                            <div id="TXT_GIO_CUOI" class="ClientEditText gio">
                                <p class="ladi-headline" data-sync-target="wedding_time_formatted">{{ $weddingTimeFormatted }}</p>
                            </div>

                            <div class="sg-ngay-moi">
                                <div id="TXT_THU_CUOI" class="ClientEditText ben">
                                    <p class="ladi-headline" id="mayhenInviteWeekday">{{ $weekdayLabel }}</p>
                                </div>
                                <div id="TXT_NGAY_SO" class="ClientEditText so">
                                    <p class="ladi-headline" id="mayhenInviteDay">{{ $dayNum }}</p>
                                </div>
                                <div id="TXT_THANG_CUOI" class="ClientEditText ben">
                                    <p class="ladi-headline" id="mayhenInviteMonth">{{ $monthTitleLabel }}</p>
                                </div>
                            </div>

                            <div id="TXT_NAM_CUOI" class="ClientEditText nam">
                                <p class="ladi-headline" id="mayhenInviteYear">{{ $yearTitleLabel }}</p>
                            </div>
                            <div id="TXT_AM_LICH" class="ClientEditText am-lich">
                                <p class="ladi-paragraph" data-sync-target="wedding_date_lunar">{{ $lunarDate }}</p>
                            </div>

                            <div id="TXT_TAI_NH" class="ClientEditText tai"><p class="ladi-paragraph">Tại địa điểm</p></div>

                            {{-- Bảng hiệu vẽ tay Sài Gòn xưa --}}
                            <div class="sg-bang-hieu sg-hien" data-hieu-ung="len">
                                <div id="TXT_BANG_LOAI" class="ClientEditText loai-bang"><p class="ladi-headline">NHÀ HÀNG TIỆC CƯỚI</p></div>
                                <div id="TXT_TEN_NHA_HANG" class="ClientEditText ten-bang sg-1dong">
                                    <h3 class="ladi-headline" data-sync-target="venue_name">{{ $signboardMain }}</h3>
                                </div>
                                <div id="TXT_SANH_NHA_HANG" class="ClientEditText phu-bang">
                                    <p class="ladi-paragraph">{{ $signboardSub }}</p>
                                </div>
                            </div>

                            <div id="TXT_DIA_CHI_NH" class="ClientEditText dia-chi">
                                <p class="ladi-paragraph" data-sync-target="venue_address">{{ $venueAddress }}</p>
                            </div>

                            <a
                                id="BTN_THEM_LICH"
                                class="sg-nut"
                                href="{{ $gcalUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <span>Ghi vào sổ tay (Google Calendar)</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="sg-vach sg-tt" aria-hidden="true"></div>

        {{-- ==================== SECTION 2: LOVE STORY — CHUYỆN TÌNH PHỐ CŨ (data-mayhen-section="story") ==================== --}}
        @if(!$isCuratedSeven)
        <section
            id="SECTION_CHUYEN"
            data-mayhen-section="story"
            class="ladi-section sg-phan sg-chuyen mayhen_invitation_story"
        >
            <div class="ladi-section-background"></div>
            <div class="ladi-container">
                <span class="sg-nhan">Phóng sự đặc biệt</span>
                <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                    <div id="TXT_CHUYEN_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Ký sự tình yêu</p></div>
                    <div id="TXT_CHUYEN_TD" class="ClientEditText sg-tieu-de">
                        <h2 class="ladi-headline" data-sync-target="story_title">{{ $storyTitle }}</h2>
                    </div>
                    <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                    <div class="ClientEditText sg-mo-ta">
                        <p class="ladi-paragraph" data-sync-target="story_quote">{{ $storyQuote }}</p>
                    </div>
                </div>

                <div class="sg-ds-tin">
                    @foreach($stories as $idx => $st)
                        <article class="sg-tin sg-hien" data-hieu-ung="len">
                            <div class="sg-tin-anh">
                                <span class="so-ky">{{ $idx + 1 }}</span>
                                <div id="IMG_CT{{ $idx + 1 }}" class="ClientEditImage ladi-element" data-lightbox-src="{{ $st['photo'] }}">
                                    <div class="ladi-image">
                                        <div class="ladi-image-background" style="background-image: url('{{ $st['photo'] }}');"></div>
                                        <img
                                            src="{{ $st['photo'] }}"
                                            alt="{{ $st['title'] }}"
                                            class="mayhen_invitation_photo_el"
                                            data-sync-target="love_story.{{ $idx }}.photo"
                                        >
                                    </div>
                                </div>
                            </div>
                            <div id="TXT_CT{{ $idx + 1 }}_NAM" class="ClientEditText moc">
                                <p class="ladi-paragraph" data-sync-target="love_story.{{ $idx }}.year">{{ $st['year'] }}</p>
                            </div>
                            <div id="TXT_CT{{ $idx + 1 }}_TD" class="ClientEditText tit-tin">
                                <h3 class="ladi-headline" data-sync-target="love_story.{{ $idx }}.title">{{ $st['title'] }}</h3>
                            </div>
                            <div id="TXT_CT{{ $idx + 1 }}_ND" class="ClientEditText bai-tin">
                                <p class="ladi-paragraph" data-sync-target="love_story.{{ $idx }}.description">{{ $st['description'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        <div class="sg-vach sg-tt" aria-hidden="true"></div>
        @endif

        {{-- ==================== SECTION 3: PARENTS & COUPLE — HAI HỌ KẾT THÔNG GIA (data-mayhen-section="parents") ==================== --}}
        <section
            id="SECTION_HAIHO"
            data-mayhen-section="parents"
            class="ladi-section sg-phan sg-haiho mayhen_invitation_parents"
        >
            <div class="ladi-section-background"></div>
            <div class="ladi-container">
                <span class="sg-nhan">Chuyên mục Gia Đình</span>
                <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                    <div id="TXT_HH_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Tin gia thất</p></div>
                    <div id="TXT_HH_TIEUDE" class="ClientEditText sg-tieu-de"><h2 class="ladi-headline">Hai Họ Kết Thông Gia</h2></div>
                    <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                </div>

                {{-- Hộp 2 bên gia đình Nhà Trai & Nhà Gái --}}
                <div class="sg-gia-dinh sg-hien" data-hieu-ung="len">
                    <div class="ben">
                        <div id="TXT_NHATRAI_NHAN" class="ClientEditText ho"><p class="ladi-headline">NHÀ TRAI</p></div>
                        <div class="bo-me">
                            <div id="TXT_BO_CHURE" class="ClientEditText">
                                <p class="ladi-paragraph" data-sync-target="groom_father">{{ $groomFather }}</p>
                            </div>
                            <div id="TXT_ME_CHURE" class="ClientEditText">
                                <p class="ladi-paragraph" data-sync-target="groom_mother">{{ $groomMother }}</p>
                            </div>
                        </div>
                        <div id="TXT_DC_NHATRAI" class="ClientEditText dc">
                            <p class="ladi-paragraph" data-sync-target="groom_address">{{ $groomAddress }}</p>
                        </div>
                    </div>

                    <div class="giua" aria-hidden="true"><svg><use href="#sg-bong"/></svg></div>

                    <div class="ben">
                        <div id="TXT_NHAGAI_NHAN" class="ClientEditText ho"><p class="ladi-headline">NHÀ GÁI</p></div>
                        <div class="bo-me">
                            <div id="TXT_BO_CODAU" class="ClientEditText">
                                <p class="ladi-paragraph" data-sync-target="bride_father">{{ $brideFather }}</p>
                            </div>
                            <div id="TXT_ME_CODAU" class="ClientEditText">
                                <p class="ladi-paragraph" data-sync-target="bride_mother">{{ $brideMother }}</p>
                            </div>
                        </div>
                        <div id="TXT_DC_NHAGAI" class="ClientEditText dc">
                            <p class="ladi-paragraph" data-sync-target="bride_address">{{ $brideAddress }}</p>
                        </div>
                    </div>
                </div>

                <div id="TXT_BAO_TIN" class="ClientEditText sg-bao-tin sg-hien" data-hieu-ung="len">
                    <p class="ladi-paragraph">Trân trọng báo tin lễ thành hôn của hai con chúng tôi</p>
                </div>

                {{-- Chân dung Chú Rể & Cô Dâu trong khung cửa sổ vòm Bưu Điện Sài Gòn --}}
                <div class="sg-doi">
                    <div class="ben-doi ben-chu-re sg-hien" data-hieu-ung="trai">
                        <div class="sg-vom">
                            <div class="sg-vom-anh">
                                <div id="IMG_CHURE" class="ClientEditImage ladi-element" data-lightbox-src="{{ $groomPhotoUrl }}">
                                    <div class="ladi-image">
                                        <div class="ladi-image-background" style="background-image: url('{{ $groomPhotoUrl }}');"></div>
                                        <img
                                            src="{{ $groomPhotoUrl }}"
                                            alt="Chú Rể {{ $groomFullName }}"
                                            class="mayhen_invitation_photo_el"
                                            data-sync-target="groom_photo"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="TXT_VAI_CHURE" class="ClientEditText vai">
                            <p class="ladi-headline">Chú Rể · <span data-sync-target="groom_role">{{ $groomRole }}</span></p>
                        </div>
                        <div id="TXT_TEN_CHURE" class="ClientEditText ten-ben sg-1dong">
                            <h3 class="ladi-headline" data-sync-target="groom_full_name">{{ $groomFullName }}</h3>
                        </div>
                    </div>

                    <svg class="sg-xe-giua" aria-hidden="true"><use href="#sg-vespa"/></svg>

                    <div class="ben-doi ben-co-dau sg-hien" data-hieu-ung="phai">
                        <div class="sg-vom">
                            <div class="sg-vom-anh">
                                <div id="IMG_CODAU" class="ClientEditImage ladi-element" data-lightbox-src="{{ $bridePhotoUrl }}">
                                    <div class="ladi-image">
                                        <div class="ladi-image-background" style="background-image: url('{{ $bridePhotoUrl }}');"></div>
                                        <img
                                            src="{{ $bridePhotoUrl }}"
                                            alt="Cô Dâu {{ $brideFullName }}"
                                            class="mayhen_invitation_photo_el"
                                            data-sync-target="bride_photo"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="TXT_VAI_CODAU" class="ClientEditText vai">
                            <p class="ladi-headline">Cô Dâu · <span data-sync-target="bride_role">{{ $brideRole }}</span></p>
                        </div>
                        <div id="TXT_TEN_CODAU" class="ClientEditText ten-ben sg-1dong">
                            <h3 class="ladi-headline" data-sync-target="bride_full_name">{{ $brideFullName }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="sg-vach sg-tt" aria-hidden="true"></div>

        {{-- ==================== SECTION 4: COUNTDOWN & CALENDAR — LỊCH BLOC & ĐÀI RADIO (data-mayhen-section="countdown_calendar") ==================== --}}
        <section
            id="mayhen_section_countdown_calendar"
            data-mayhen-section="countdown_calendar"
            class="mayhen_invitation_countdown"
        >
            {{-- 4A. TỜ LỊCH BLOC XÉ TAY & LỊCH TREO TƯỜNG (#SECTION_LICH) --}}
            <div id="SECTION_LICH" class="ladi-section sg-phan sg-lich">
                <div class="ladi-section-background"></div>
                <div class="ladi-container">
                    <span class="sg-nhan">Lịch Bloc Sài Gòn</span>
                    <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                        <div id="TXT_LICH_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Ghi nhớ ngày vui</p></div>
                        <div id="TXT_LICH_TD" class="ClientEditText sg-tieu-de">
                            <h2 class="ladi-headline" data-sync-target="calendar_heading">{{ $calendarHeading }}</h2>
                        </div>
                        <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                    </div>

                    <div class="sg-lich-khoi">
                        {{-- Tờ lịch Bloc xé tay --}}
                        <div class="sg-bloc sg-hien" data-hieu-ung="phong">
                            <div class="dau-bloc">
                                <span id="TXT_BLOC_THANG" class="ClientEditText">{{ $monthTitleLabel }}</span>
                                <span>★</span>
                                <span id="TXT_BLOC_NAM" class="ClientEditText">{{ $yearTitleLabel }}</span>
                            </div>
                            <div class="to-bloc">
                                <div id="TXT_BLOC_THU" class="ClientEditText thu-bloc"><p class="ladi-headline" id="mayhenBlocWeekday">{{ $weekdayLabel }}</p></div>
                                <div id="TXT_BLOC_NGAY" class="ClientEditText so-bloc"><p class="ladi-headline" id="mayhenBlocDay">{{ $dayNum }}</p></div>
                                <div id="TXT_BLOC_AM" class="ClientEditText am-bloc">
                                    <p class="ladi-paragraph" data-sync-target="wedding_date_lunar">{{ $lunarDate }}</p>
                                </div>
                                <div id="TXT_BLOC_CHUC" class="ClientEditText chuc-bloc">
                                    <p class="ladi-paragraph" data-sync-target="bloc_note">{{ $blocNote }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Tờ lịch tháng treo tường đóng dấu son ngày cưới --}}
                        <div class="sg-to-lich sg-hien" data-hieu-ung="len">
                            <div
                                id="CAL_CUOI"
                                class="edit_calendar ladi-element"
                                data-date="{{ $isoDate }}"
                                data-color="#B4513A"
                            >
                                <div class="mayhen_invitation_calendar_grid">
                                    <div class="mayhen_invitation_cal_head" id="mayhenCalMonthTitle">
                                        THÁNG {{ $monthNum }} / {{ $yearNum }}
                                    </div>
                                    <div class="mayhen_invitation_cal_weekdays">
                                        <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                                    </div>
                                    <div class="mayhen_invitation_cal_days" id="mayhenCalendarDaysGrid"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sg-vach sg-tt" aria-hidden="true"></div>

            {{-- 4B. ĐÀI RADIO GỖ CỔ ĐIỂN ĐẾM NGƯỢC (#SECTION_DEMNGUOC) --}}
            <div id="SECTION_DEMNGUOC" class="ladi-section sg-phan sg-dem">
                <div class="ladi-section-background"></div>
                <div class="ladi-container">
                    <span class="sg-nhan">Đài Phát Thanh Hạnh Phúc</span>
                    <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                        <div id="TXT_DEM_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Tần số trăm năm</p></div>
                        <div id="TXT_DEM_TD" class="ClientEditText sg-tieu-de">
                            <h2 class="ladi-headline" data-sync-target="countdown_title">{{ $countdownTitle }}</h2>
                        </div>
                        <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                    </div>

                    <div class="sg-radio sg-hien" data-hieu-ung="phong">
                        <div class="loa">
                            <span class="hieu-radio" data-sync-target="radio_brand">{{ $radioBrand }}</span>
                        </div>
                        <div class="mat-so">
                            <div class="tan-so">
                                <span>MHz</span>
                                <div class="vach"><span class="kim"></span></div>
                                <span>SÀI GÒN</span>
                            </div>
                            <div class="sg-cho-dem">
                                <div
                                    id="COUNTDOWN_CUOI"
                                    class="edit_countDown ladi-element"
                                    data-date="{{ $targetIso }}"
                                >
                                    <div class="mayhen_invitation_radio_digits">
                                        <div class="mayhen_invitation_countdown_box">
                                            <span id="mayhenCdDays" class="mayhen_invitation_countdown_num" data-countdown-days>00</span>
                                            <span class="mayhen_invitation_countdown_label">Ngày</span>
                                        </div>
                                        <div class="mayhen_invitation_countdown_box">
                                            <span id="mayhenCdHours" class="mayhen_invitation_countdown_num" data-countdown-hours>00</span>
                                            <span class="mayhen_invitation_countdown_label">Giờ</span>
                                        </div>
                                        <div class="mayhen_invitation_countdown_box">
                                            <span id="mayhenCdMinutes" class="mayhen_invitation_countdown_num" data-countdown-minutes>00</span>
                                            <span class="mayhen_invitation_countdown_label">Phút</span>
                                        </div>
                                        <div class="mayhen_invitation_countdown_box">
                                            <span id="mayhenCdSeconds" class="mayhen_invitation_countdown_num" data-countdown-seconds>00</span>
                                            <span class="mayhen_invitation_countdown_label">Giây</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="nut-radio" aria-hidden="true">
                            <span class="num"></span>
                            <span class="day-dong">FM · TRĂM NĂM</span>
                            <span class="num"></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="sg-vach sg-tt" aria-hidden="true"></div>

        {{-- ==================== SECTION 5: EVENTS & MAPS — RẠP HẠNH PHÚC & BƯU ĐIỆN SÀI GÒN (data-mayhen-section="events") ==================== --}}
        <section
            id="mayhen_section_events"
            data-mayhen-section="events"
            class="mayhen_invitation_events"
        >
            {{-- 5A. RẠP HẠNH PHÚC — CHƯƠNG TRÌNH HÔN LỄ (#SECTION_NGHILE) --}}
            <div id="SECTION_NGHILE" class="ladi-section sg-phan sg-nghile">
                <div class="ladi-section-background"></div>
                <div class="ladi-container">
                    <span class="sg-nhan">Lịch trình trọng đại</span>
                    <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                        <div id="TXT_NL_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Kính báo chương trình</p></div>
                        <div id="TXT_NL_TD" class="ClientEditText sg-tieu-de"><h2 class="ladi-headline">Chương Trình Hôn Lễ</h2></div>
                        <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                    </div>

                    <div class="sg-rap tmx-nghi-le sg-hien" data-hieu-ung="phong">
                        <div class="den tren" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                        <div class="sg-rap-mai">
                            <div id="TXT_TEN_RAP" class="ClientEditText ten-rap">
                                <h3 class="ladi-headline" data-sync-target="marquee_title">{{ $marqueeTitle }}</h3>
                            </div>
                            <div id="TXT_PHU_RAP" class="ClientEditText phu-rap">
                                <p class="ladi-paragraph" data-sync-target="marquee_subtitle">{{ $marqueeSubtitle }}</p>
                            </div>
                        </div>

                        <div class="sg-ds-le">
                            {{-- Lễ 1: Lễ Vu Quy --}}
                            <div class="sg-le" data-tmx-nl="1">
                                <div class="bieu-tuong" data-tmx-icon="1">
                                    <svg viewBox="0 0 36 36" fill="none" stroke="currentColor" stroke-width="1.6">
                                        <path d="M18 6 C20 12,25 14,25 20 A7 7 0 0 1 11 20 C11 14,16 12,18 6 Z" fill="rgba(217,163,58,.25)"/>
                                        <path d="M10 28 H26 M13 31 H23"/>
                                    </svg>
                                </div>
                                <div class="noi-dung">
                                    <div id="TXT_LE1_TEN" class="ClientEditText ten-le">
                                        <h4 class="ladi-headline" data-sync-target="vu_quy_title">{{ $vuQuyTitle }}</h4>
                                    </div>
                                    <div id="TXT_LE1_DC" class="ClientEditText dia-diem">
                                        <p class="ladi-paragraph" data-sync-target="vu_quy_address">{{ $vuQuyAddress }}</p>
                                    </div>
                                </div>
                                <div class="gio-ngay">
                                    <div id="TXT_LE1_GIO" class="ClientEditText gio-le">
                                        <p class="ladi-headline" data-sync-target="vu_quy_time">{{ $vuQuyTime }}</p>
                                    </div>
                                    <div id="TXT_LE1_NGAY" class="ClientEditText ngay-le">
                                        <p class="ladi-paragraph">{{ $dayNum }}.{{ $monthNum }}.{{ $yearNum }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Lễ 2: Lễ Thành Hôn --}}
                            <div class="sg-le" data-tmx-nl="2">
                                <div class="bieu-tuong" data-tmx-icon="2">
                                    <svg viewBox="0 0 36 36" fill="none" stroke="currentColor" stroke-width="1.6">
                                        <circle cx="14" cy="19" r="7"/>
                                        <circle cx="22" cy="19" r="7"/>
                                        <path d="M22 10 L24 12 L22 14 L20 12 Z" fill="currentColor"/>
                                    </svg>
                                </div>
                                <div class="noi-dung">
                                    <div id="TXT_LE2_TEN" class="ClientEditText ten-le">
                                        <h4 class="ladi-headline" data-sync-target="thanh_hon_title">{{ $thanhHonTitle }}</h4>
                                    </div>
                                    <div id="TXT_LE2_DC" class="ClientEditText dia-diem">
                                        <p class="ladi-paragraph" data-sync-target="thanh_hon_address">{{ $thanhHonAddress }}</p>
                                    </div>
                                </div>
                                <div class="gio-ngay">
                                    <div id="TXT_LE2_GIO" class="ClientEditText gio-le">
                                        <p class="ladi-headline" data-sync-target="thanh_hon_time">{{ $thanhHonTime }}</p>
                                    </div>
                                    <div id="TXT_LE2_NGAY" class="ClientEditText ngay-le">
                                        <p class="ladi-paragraph">{{ $dayNum }}.{{ $monthNum }}.{{ $yearNum }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Lễ 3: Tiệc Cưới --}}
                            <div class="sg-le" data-tmx-nl="3">
                                <div class="bieu-tuong" data-tmx-icon="3">
                                    <svg viewBox="0 0 36 36" fill="none" stroke="currentColor" stroke-width="1.6">
                                        <path d="M10 8 H17 L15 19 A3 3 0 0 1 11 19 Z" fill="rgba(217,163,58,.25)"/>
                                        <path d="M13.5 21 V29 M10 29 H17"/>
                                        <path d="M19 8 H26 L25 19 A3 3 0 0 1 21 19 Z" fill="rgba(180,81,58,.3)"/>
                                        <path d="M22.5 21 V29 M19 29 H26"/>
                                    </svg>
                                </div>
                                <div class="noi-dung">
                                    <div id="TXT_LE3_TEN" class="ClientEditText ten-le">
                                        <h4 class="ladi-headline" data-sync-target="tiec_cuoi_title">{{ $tiecCuoiTitle }}</h4>
                                    </div>
                                    <div id="TXT_LE3_DC" class="ClientEditText dia-diem">
                                        <p class="ladi-paragraph" data-sync-target="venue_name">{{ $venueName }}</p>
                                    </div>
                                </div>
                                <div class="gio-ngay">
                                    <div id="TXT_LE3_GIO" class="ClientEditText gio-le">
                                        <p class="ladi-headline" data-sync-target="wedding_time">{{ $partyTime }}</p>
                                    </div>
                                    <div id="TXT_LE3_NGAY" class="ClientEditText ngay-le">
                                        <p class="ladi-paragraph">{{ $dayNum }}.{{ $monthNum }}.{{ $yearNum }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="den duoi" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                    </div>
                </div>
            </div>

            <div class="sg-vach sg-tt" aria-hidden="true"></div>

            {{-- 5B. BẢN ĐỒ VÒM BƯU ĐIỆN SÀI GÒN (#SECTION_BANDO) --}}
            <div id="SECTION_BANDO" class="ladi-section sg-phan sg-bd">
                <div class="ladi-section-background"></div>
                <div class="ladi-container">
                    <span class="sg-nhan">Bưu Điện Trung Tâm</span>
                    <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                        <div id="TXT_BD_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Sơ đồ chỉ dẫn</p></div>
                        <div id="TXT_BD_TD" class="ClientEditText sg-tieu-de"><h2 class="ladi-headline">Đường Tới Tiệc Cưới</h2></div>
                        <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                    </div>

                    <div class="sg-buu-dien sg-hien" data-hieu-ung="phong">
                        <span class="dong-ho" aria-hidden="true"></span>
                        <span class="cot trai" aria-hidden="true"></span>
                        <span class="cot phai" aria-hidden="true"></span>
                        <div class="bien-bd">BƯU ĐIỆN SÀI GÒN · CHỈ ĐƯỜNG</div>
                        <div class="cua-vom">
                            <div
                                id="MAP_CUOI"
                                class="edit_googleMap ladi-element"
                                data-lat="{{ $mapLat }}"
                                data-lng="{{ $mapLng }}"
                                data-zoom="15"
                                data-title="{{ $venueName }}"
                                data-address="{{ $venueAddress }}"
                            >
                                <iframe
                                    title="Bản đồ chỉ đường tới {{ $venueName }}"
                                    src="{{ $mapEmbedUrl }}"
                                    width="100%"
                                    height="100%"
                                    style="border:0; width:100%; height:100%; display:block;"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                ></iframe>
                            </div>
                        </div>
                    </div>

                    <div class="dia-chi-bd sg-hien" data-hieu-ung="len">
                        <div id="TXT_BD_TEN" class="ClientEditText ten-dc">
                            <h3 class="ladi-headline" data-sync-target="venue_name">{{ $venueName }}</h3>
                        </div>
                        <div id="TXT_BD_DC" class="ClientEditText">
                            <p class="ladi-paragraph" data-sync-target="venue_address">{{ $venueAddress }}</p>
                        </div>
                        <a
                            id="BTN_CHI_DUONG"
                            class="sg-nut"
                            href="{{ $googleMapsUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-sync-target="google_maps_url"
                        >
                            <span>Mở bản đồ chỉ đường</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="sg-vach sg-tt" aria-hidden="true"></div>

        {{-- ==================== SECTION 6: TIMELINE — TRÌNH TỰ BUỔI TIỆC (data-mayhen-section="timeline") ==================== --}}
        @if(!$isCuratedSeven)
        <section
            id="SECTION_TIMELINE"
            data-mayhen-section="timeline"
            class="ladi-section sg-phan sg-timeline mayhen_invitation_timeline"
        >
            <div class="ladi-section-background"></div>
            <div class="ladi-container">
                <span class="sg-nhan">Chương trình chi tiết</span>
                <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                    <div id="TXT_TL_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Từng khoảnh khắc</p></div>
                    <div id="TXT_TL_TD" class="ClientEditText sg-tieu-de"><h2 class="ladi-headline">Trình Tự Buổi Tiệc</h2></div>
                    <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                </div>

                <div class="sg-tl sg-hien" data-hieu-ung="len">
                    <div id="TL_CUOI" class="edit_timeLine ladi-element">
                        <div class="mayhen_invitation_timeline_list">
                            @foreach($timelineItems as $idx => $tl)
                                <div class="mayhen_invitation_timeline_item">
                                    <div class="mayhen_invitation_timeline_time" data-sync-target="timeline_items.{{ $idx }}.time">
                                        {{ $tl['time'] }}
                                    </div>
                                    <div class="mayhen_invitation_timeline_axis" aria-hidden="true">
                                        <span class="mayhen_invitation_timeline_dot">
                                            @if($idx === 0)
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                            @elseif($idx === 1)
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="12" r="5"/><circle cx="15" cy="12" r="5"/></svg>
                                            @elseif($idx === 2)
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 22h8M12 15v7M17 3H7l2 8a3 3 0 0 0 6 0l2-8Z"/></svg>
                                            @else
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="mayhen_invitation_timeline_content">
                                        <div class="mayhen_invitation_timeline_title" data-sync-target="timeline_items.{{ $idx }}.title">
                                            {{ $tl['title'] }}
                                        </div>
                                        @if(!empty($tl['description']))
                                            <div class="mayhen_invitation_timeline_desc" data-sync-target="timeline_items.{{ $idx }}.description">
                                                {{ $tl['description'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="sg-vach sg-tt" aria-hidden="true"></div>
        @endif

        {{-- ==================== SECTION 7: DRESSCODE — CON TEM THƯ RĂNG CƯA (data-mayhen-section="dresscode") ==================== --}}
        @if(!$isCuratedSeven)
        <section
            id="SECTION_DRESSCODE"
            data-mayhen-section="dresscode"
            class="ladi-section sg-phan sg-dc mayhen_invitation_dresscode"
            data-wb-khoi="dresscode"
        >
            <div class="ladi-section-background"></div>
            <div class="ladi-container">
                <span class="sg-nhan">Sắc màu ngày vui</span>
                <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                    <div id="TXT_DC_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Gợi ý trang phục</p></div>
                    <div id="TXT_DC_TD" class="ClientEditText sg-tieu-de"><h2 class="ladi-headline">Áo Quần Dự Tiệc</h2></div>
                    <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                    <div id="TXT_DC_MOTA" class="ClientEditText sg-mo-ta">
                        <p class="ladi-paragraph" data-sync-target="dresscode_note">{{ $dresscodeNote }}</p>
                    </div>
                </div>

                <div class="sg-the-dc sg-hien" data-hieu-ung="phong">
                    <div id="DC_CUOI" class="edit_dressCode ladi-element" data-size="52" data-gap="16">
                        <div class="sg-dct-ds">
                            @foreach($dresscodeStamps as $idx => $stamp)
                                <div class="sg-dct" style="animation-delay: {{ $idx * 0.14 }}s;">
                                    <div class="sg-dct-o" style="--r: {{ $stamp['rot'] }};">
                                        <div class="sg-dct-tem">
                                            <div class="sg-dct-khung">
                                                <div
                                                    class="sg-dct-mau"
                                                    style="--c: {{ $stamp['hex'] }}; background-color: {{ $stamp['hex'] }};"
                                                    data-sync-target="dresscode_palette.{{ $idx }}.hex"
                                                >
                                                    <svg viewBox="0 0 40 48" aria-hidden="true">
                                                        <path class="nen" d="M13 6 L16 3 Q20 6 24 3 L27 6 L35 12 L32 20 L27 17 L29 45 L11 45 L13 17 L8 20 L5 12 Z"/>
                                                        <path class="net" d="M13 6 L16 3 Q20 6 24 3 L27 6 L35 12 L32 20 L27 17 L29 45 L11 45 L13 17 L8 20 L5 12 Z M16 3 Q20 10 24 3 M20 8 V45"/>
                                                    </svg>
                                                </div>
                                                <div class="sg-dct-chan">
                                                    <span>SÀI GÒN</span>
                                                    <b>{{ $stamp['denom'] }}</b>
                                                </div>
                                            </div>
                                        </div>
                                        <svg class="sg-dct-dau" viewBox="0 0 60 60" aria-hidden="true" style="animation-delay: {{ 0.25 + $idx * 0.14 }}s;">
                                            <circle cx="30" cy="30" r="27" stroke-width="1.8"/>
                                            <circle cx="30" cy="30" r="22" stroke-width="0.9" stroke-dasharray="2.2 2.2"/>
                                            <path d="M5 24 H55 M5 36 H55" stroke-width="1.1"/>
                                            <text x="30" y="21" text-anchor="middle">BƯU ĐIỆN</text>
                                            <text x="30" y="33" text-anchor="middle" font-size="8.2">SÀI GÒN</text>
                                            <text x="30" y="46" text-anchor="middle">★ {{ $yearNum }} ★</text>
                                        </svg>
                                    </div>
                                    <span
                                        class="sg-dct-ten dc-fs-el"
                                        data-sync-target="dresscode_palette.{{ $idx }}.name"
                                    >{{ $stamp['name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="sg-vach sg-tt" aria-hidden="true"></div>
        @endif

        {{-- ==================== SECTION 8: ALBUM 10 PHOTOS — CUỘN PHIM KỶ NIỆM (data-mayhen-section="album") ==================== --}}
        <section
            id="SECTION_ALBUM"
            data-mayhen-section="album"
            class="ladi-section sg-phan sg-album tmx-album-cho mayhen_invitation_album"
        >
            <div class="ladi-section-background"></div>
            <div class="ladi-container">
                <span class="sg-nhan">Phòng Ảnh Sài Gòn</span>
                <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                    <div id="TXT_AB_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Cuộn phim kỷ niệm</p></div>
                    <div id="TXT_AB_TD" class="ClientEditText sg-tieu-de"><h2 class="ladi-headline">Album Kỷ Niệm</h2></div>
                    <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                </div>

                <div class="sg-phim" aria-hidden="true"></div>

                <div id="ALBUM_CUOI" class="edit_galleryV2 ladi-element">
                    <div class="mayhen_invitation_album_grid">
                        @foreach($galleryPhotos as $idx => $photoUrl)
                            <figure
                                class="mayhen_invitation_album_item sg-hien"
                                data-hieu-ung="rua"
                                data-gallery-index="{{ $idx }}"
                                data-lightbox-src="{{ $photoUrl }}"
                            >
                                <div class="mayhen_invitation_album_frame">
                                    <img
                                        src="{{ $photoUrl }}"
                                        alt="Ảnh kỷ niệm {{ $idx + 1 }} — {{ $coupleNames }}"
                                        loading="lazy"
                                        data-sync-target="gallery.{{ $idx }}"
                                    >
                                </div>
                                <span class="mayhen_invitation_album_num">PHIM SỐ {{ sprintf('%02d', $idx + 1) }}</span>
                            </figure>
                        @endforeach
                    </div>
                </div>

                <div class="sg-phim" aria-hidden="true"></div>
            </div>
        </section>

        <div class="sg-vach sg-tt" aria-hidden="true"></div>

        {{-- ==================== SECTION 9: GIFT & VIETQR — THÙNG THƯ BƯU ĐIỆN & BƯU PHIẾU (data-mayhen-section="gift") ==================== --}}
        @if(!$isCuratedSeven)
        <section
            id="SECTION_QR"
            data-mayhen-section="gift"
            class="ladi-section sg-phan sg-qua tmx-qr mayhen_invitation_gift"
        >
            <div class="ladi-section-background"></div>
            <div class="ladi-container">
                <span class="sg-nhan">Bưu Phẩm Tình Thân</span>
                <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                    <div id="TXT_QR_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Gửi chút tấm lòng</p></div>
                    <div id="TXT_QR_TD" class="ClientEditText sg-tieu-de"><h2 class="ladi-headline">Hộp Mừng Cưới</h2></div>
                    <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                    <div id="TXT_QR_MOTA" class="ClientEditText sg-mo-ta">
                        <p class="ladi-paragraph" data-sync-target="gift_description">{{ $giftDescription }}</p>
                    </div>
                </div>

                <div class="sg-qua-vung {{ $isEmbed ? 'da-mo' : '' }}" id="sgQuaVung">
                    {{-- Thùng thư Bưu Điện Sài Gòn --}}
                    <div
                        class="sg-hop-bd"
                        id="sgHop"
                        role="button"
                        tabindex="0"
                        aria-label="Mở thùng thư mừng cưới"
                        aria-expanded="{{ $isEmbed ? 'true' : 'false' }}"
                    >
                        <div class="thu-nho" aria-hidden="true"></div>
                        <div class="mai-hop"></div>
                        <div class="than-hop">
                            <div class="khe"></div>
                            <div class="bang-hop">
                                <b>THƯ · MỪNG CƯỚI</b>
                                <span>BƯU ĐIỆN SÀI GÒN</span>
                            </div>
                        </div>
                        <div class="chan-hop"></div>
                    </div>

                    <div id="TXT_GOI_Y_HOP" class="ClientEditText sg-goi-y">
                        <p class="ladi-paragraph">▲ Chạm vào thùng thư để mở bưu phiếu mừng cưới ▲</p>
                    </div>

                    {{-- 2 Tấm Bưu Phiếu Mừng Cưới VietQR (Nhà Trai & Nhà Gái) --}}
                    <div class="sg-qr" id="sgQr">
                        {{-- Bưu phiếu 1: Chú Rể --}}
                        <div class="sg-the-qr tmx-the-qr">
                            <div class="dau-phieu"><span>BƯU PHIẾU</span><span>Số 2712</span></div>
                            <div id="TXT_QR1_VAI" class="ClientEditText vai-qr"><h3 class="ladi-headline">Mừng Chú Rể</h3></div>
                            <div class="anh-qr">
                                <div id="IMG_QR1" class="ClientEditImage ladi-element" data-lightbox-src="{{ $groomQrUrl }}">
                                    <div class="ladi-image">
                                        <div class="ladi-image-background" style="background-image: url('{{ $groomQrUrl }}');"></div>
                                        <img
                                            src="{{ $groomQrUrl }}"
                                            alt="Mã VietQR Chú Rể {{ $groomBankHolder }}"
                                            class="mayhen_invitation_qr_img"
                                            data-sync-target="groom_qr_image"
                                        >
                                    </div>
                                </div>
                            </div>
                            <div id="TXT_QR1_NH" class="ClientEditText nh">
                                <p class="ladi-paragraph" data-sync-target="groom_bank_name">{{ $groomBankName }}</p>
                            </div>
                            <div id="TXT_QR1_STK" class="ClientEditText stk">
                                <p class="ladi-paragraph" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</p>
                            </div>
                            <div id="TXT_QR1_CHU" class="ClientEditText chu-tk">
                                <p class="ladi-paragraph" data-sync-target="groom_bank_holder">{{ $groomBankHolder }}</p>
                            </div>
                            <div class="mayhen_invitation_qr_actions">
                                <button
                                    type="button"
                                    class="mayhen_invitation_qr_btn"
                                    data-copy-account="{{ $groomBankAccount }}"
                                >
                                    Sao chép STK
                                </button>
                                <button
                                    type="button"
                                    class="mayhen_invitation_qr_btn mayhen_invitation_qr_btn--secondary"
                                    data-lightbox-src="{{ $groomQrUrl }}"
                                >
                                    Phóng to QR
                                </button>
                            </div>
                        </div>

                        {{-- Bưu phiếu 2: Cô Dâu --}}
                        <div class="sg-the-qr tmx-the-qr">
                            <div class="dau-phieu"><span>BƯU PHIẾU</span><span>Số 2612</span></div>
                            <div id="TXT_QR2_VAI" class="ClientEditText vai-qr"><h3 class="ladi-headline">Mừng Cô Dâu</h3></div>
                            <div class="anh-qr">
                                <div id="IMG_QR2" class="ClientEditImage ladi-element" data-lightbox-src="{{ $brideQrUrl }}">
                                    <div class="ladi-image">
                                        <div class="ladi-image-background" style="background-image: url('{{ $brideQrUrl }}');"></div>
                                        <img
                                            src="{{ $brideQrUrl }}"
                                            alt="Mã VietQR Cô Dâu {{ $brideBankHolder }}"
                                            class="mayhen_invitation_qr_img"
                                            data-sync-target="bride_qr_image"
                                        >
                                    </div>
                                </div>
                            </div>
                            <div id="TXT_QR2_NH" class="ClientEditText nh">
                                <p class="ladi-paragraph" data-sync-target="bride_bank_name">{{ $brideBankName }}</p>
                            </div>
                            <div id="TXT_QR2_STK" class="ClientEditText stk">
                                <p class="ladi-paragraph" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</p>
                            </div>
                            <div id="TXT_QR2_CHU" class="ClientEditText chu-tk">
                                <p class="ladi-paragraph" data-sync-target="bride_bank_holder">{{ $brideBankHolder }}</p>
                            </div>
                            <div class="mayhen_invitation_qr_actions">
                                <button
                                    type="button"
                                    class="mayhen_invitation_qr_btn"
                                    data-copy-account="{{ $brideBankAccount }}"
                                >
                                    Sao chép STK
                                </button>
                                <button
                                    type="button"
                                    class="mayhen_invitation_qr_btn mayhen_invitation_qr_btn--secondary"
                                    data-lightbox-src="{{ $brideQrUrl }}"
                                >
                                    Phóng to QR
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="sg-vach sg-tt" aria-hidden="true"></div>
        @endif

        {{-- ==================== SECTION 10: ENDING — BƯU THIẾP KÝ TÊN, ĐIỆN TÍN RSVP, HỘP THƯ BẠN ĐỌC & LỜI CẢM ƠN (data-mayhen-section="ending") ==================== --}}
        @if(!$isCuratedSeven)
        <section
            id="mayhen_section_ending"
            data-mayhen-section="ending"
            class="mayhen_invitation_ending"
        >
            {{-- 10A. KÝ TÊN LƯU BÚT — BƯU THIẾP CARTE POSTALE (#SECTION_KYTEN) --}}
            {{-- STRICT MANDATORY LAW #8: 0 HUMAN PHOTOS IN SIGNATURE! Con tem .sg-tem sử dụng tranh khắc SVG Chợ Bến Thành & Đôi Chim Bồ Câu Hòa Bình --}}
            <div id="SECTION_KYTEN" class="ladi-section sg-phan sg-ky">
                <div class="ladi-section-background"></div>
                <div class="ladi-container sg-hai-cot">
                    <span class="sg-nhan">Bưu Thiếp Kỷ Niệm</span>
                    <div class="sg-cot-chu">
                        <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                            <div id="TXT_KY_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Bút tích gửi lại</p></div>
                            <div id="TXT_KY_TD" class="ClientEditText sg-tieu-de"><h2 class="ladi-headline">Ký Tên Chúc Phúc</h2></div>
                            <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                            <div id="TXT_KY_MOTA" class="ClientEditText sg-mo-ta">
                                <p class="ladi-paragraph" data-sync-target="signature_subtitle">{{ $signatureSubtitle }}</p>
                            </div>
                        </div>
                        <svg class="sg-ve-them" aria-hidden="true"><use href="#sg-dau-bd"/></svg>
                    </div>

                    <div class="sg-buu-thiep sg-hien" data-hieu-ung="phong">
                        <div class="sg-bt-dau">
                            <div class="chu-bt">
                                <b>BƯU THIẾP · CARTE POSTALE</b>
                                <span>BƯU CHÍNH SÀI GÒN · ẤN PHẨM KỶ NIỆM</span>
                                <div class="nguoi-nhan">
                                    Kính gửi:
                                    <span data-sync-target="groom_name">{{ $groomName }}</span>
                                    &amp;
                                    <span data-sync-target="bride_name">{{ $brideName }}</span>
                                </div>
                            </div>

                            {{-- Con tem răng cưa đồ họa SVG Chợ Bến Thành (Law #8: Tuyệt đối 0 ảnh người) --}}
                            <div class="sg-tem" aria-hidden="true">
                                <div class="mayhen_invitation_stamp_graphic">
                                    <span class="mayhen_invitation_stamp_graphic_top">BƯU CHÍNH</span>
                                    <svg viewBox="0 0 160 110"><use href="#sg-ben-thanh"/></svg>
                                    <div class="mayhen_invitation_stamp_graphic_bot">
                                        <span>SÀI GÒN</span>
                                        <b>100đ</b>
                                    </div>
                                </div>
                            </div>

                            {{-- Dấu bưu điện đóng chồng lên góc tem --}}
                            <svg class="sg-dau-tem" viewBox="0 0 90 90" aria-hidden="true">
                                <g fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="45" cy="45" r="41"/>
                                    <circle cx="45" cy="45" r="35" stroke-width=".9" stroke-dasharray="2.5 2.5"/>
                                    <line x1="6" y1="37" x2="84" y2="37" stroke-width="1.2"/>
                                    <line x1="6" y1="53" x2="84" y2="53" stroke-width="1.2"/>
                                </g>
                                <g fill="currentColor" font-family="Anton, sans-serif" text-anchor="middle">
                                    <text x="45" y="22" font-size="9" letter-spacing="2">SÀI GÒN</text>
                                    <text x="45" y="49" font-size="10.5" letter-spacing="1.5">{{ $dayNum }}·{{ $monthNum }}·{{ substr($yearNum, -2) }}</text>
                                    <text x="45" y="75" font-size="8.5" letter-spacing="2">HỶ SỰ</text>
                                </g>
                            </svg>
                        </div>

                        <div id="SIG_CUOI" class="edit_signature ladi-element">
                            <div class="mayhen_invitation_sig_pad_wrap">
                                <div class="mayhen_invitation_sig_field">
                                    <label for="mayhenSignatureName">Người gửi bưu thiếp</label>
                                    <input
                                        type="text"
                                        id="mayhenSignatureName"
                                        class="mayhen_invitation_sig_input"
                                        placeholder="Nhập quý danh của bạn..."
                                        maxlength="80"
                                    >
                                </div>
                                <div class="mayhen_invitation_sig_canvas_box">
                                    <canvas
                                        id="mayhenSignatureCanvas"
                                        class="mayhen_invitation_sig_canvas"
                                        width="340"
                                        height="150"
                                    ></canvas>
                                    <div id="mayhenSignaturePlaceholder" class="mayhen_invitation_sig_placeholder">
                                        ✎ Ký tên lưu bút lên mặt bưu thiếp tại đây
                                    </div>
                                </div>
                                <div class="mayhen_invitation_sig_actions">
                                    <button type="button" id="mayhenSigClearBtn" class="mayhen_invitation_qr_btn mayhen_invitation_qr_btn--secondary">
                                        Xóa ký lại
                                    </button>
                                    <button type="button" id="mayhenSigSaveBtn" class="mayhen_invitation_qr_btn">
                                        Đóng dấu &amp; Lưu chữ ký
                                    </button>
                                </div>
                                <div id="mayhenSignatureWall" class="mayhen_invitation_sig_wall">
                                    @foreach($savedSignatures as $sig)
                                        @if(!empty($sig['signature_url']) || !empty($sig['signature_data']))
                                            <div class="mayhen_invitation_sig_card">
                                                <img
                                                    src="{{ $sig['signature_url'] ?? $sig['signature_data'] }}"
                                                    alt="Chữ ký của {{ $sig['guest_name'] ?? 'Quan khách' }}"
                                                >
                                                <div class="mayhen_invitation_sig_card_name">{{ $sig['guest_name'] ?? 'Quan khách' }}</div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sg-vach sg-tt" aria-hidden="true"></div>

            {{-- 10B. BỨC ĐIỆN TÍN XÁC NHẬN THAM DỰ — PAR AVION (#SECTION_XACNHAN) --}}
            {{-- STRICT MANDATORY LAW #8: 0 HUMAN PHOTOS IN RSVP! --}}
            <div id="SECTION_XACNHAN" class="ladi-section sg-phan sg-xn">
                <div class="ladi-section-background"></div>
                <div class="ladi-container sg-hai-cot">
                    <span class="sg-nhan">Điện Tín Hồi Âm</span>
                    <div class="sg-cot-chu">
                        <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                            <div id="TXT_XN_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Phúc đáp thiệp mời</p></div>
                            <div id="TXT_XN_TD" class="ClientEditText sg-tieu-de"><h2 class="ladi-headline">Xác Nhận Tham Dự</h2></div>
                            <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                            <div id="TXT_XN_MOTA" class="ClientEditText sg-mo-ta">
                                <p class="ladi-paragraph" data-sync-target="rsvp_note">{{ $rsvpNote }}</p>
                            </div>
                        </div>
                        <svg class="sg-ve-them" aria-hidden="true"><use href="#sg-vespa"/></svg>
                    </div>

                    <div class="sg-dien-tin sg-hien" data-hieu-ung="len">
                        <div class="dau-dt">
                            <span>BƯU ĐIỆN SÀI GÒN · PAR AVION</span>
                            <b>ĐIỆN TÍN</b>
                        </div>

                        <div id="FORM_XAC_NHAN" class="Client Form ladi-element">
                            <form id="mayhenRsvpForm" class="mayhen_invitation_rsvp_form">
                                <div class="mayhen_invitation_form_group">
                                    <label class="mayhen_invitation_form_label" for="mayhenRsvpName">Họ và tên Quý khách *</label>
                                    <input
                                        type="text"
                                        id="mayhenRsvpName"
                                        name="name"
                                        class="mayhen_invitation_input"
                                        placeholder="Nhập họ tên Quý khách..."
                                        value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}"
                                        required
                                        maxlength="120"
                                    >
                                </div>

                                <div class="mayhen_invitation_form_row">
                                    <div class="mayhen_invitation_form_group">
                                        <label class="mayhen_invitation_form_label" for="mayhenRsvpAttendance">Trạng thái tham dự</label>
                                        <select id="mayhenRsvpAttendance" name="attendance" class="mayhen_invitation_select">
                                            <option value="attending">Sẽ đến chung vui</option>
                                            <option value="declined">Rất tiếc vắng mặt</option>
                                        </select>
                                    </div>
                                    <div class="mayhen_invitation_form_group">
                                        <label class="mayhen_invitation_form_label" for="mayhenRsvpGuests">Số người tham dự</label>
                                        <select id="mayhenRsvpGuests" name="guests_count" class="mayhen_invitation_select">
                                            <option value="1">1 người</option>
                                            <option value="2">2 người</option>
                                            <option value="3">3 người</option>
                                            <option value="4">4 người</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mayhen_invitation_form_group">
                                    <label class="mayhen_invitation_form_label" for="mayhenRsvpSide">Khách mời của bên</label>
                                    <select id="mayhenRsvpSide" name="side" class="mayhen_invitation_select">
                                        <option value="groom">Khách Nhà Trai ({{ $groomName }})</option>
                                        <option value="bride">Khách Nhà Gái ({{ $brideName }})</option>
                                    </select>
                                </div>

                                <div class="mayhen_invitation_form_group">
                                    <label class="mayhen_invitation_form_label" for="mayhenRsvpNote">Ghi chú thêm (Thực đơn chay / Dị ứng)</label>
                                    <input
                                        type="text"
                                        id="mayhenRsvpNote"
                                        name="dietary_notes"
                                        class="mayhen_invitation_input"
                                        placeholder="Ghi chú cho ban tiếp tân nếu có..."
                                        maxlength="250"
                                    >
                                </div>

                                <button type="submit" id="mayhenRsvpSubmitBtn" class="mayhen_invitation_submit_btn">
                                    <span>Gửi Bức Điện Tín Xác Nhận</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sg-vach sg-tt" aria-hidden="true"></div>

            {{-- 10C. HỘP THƯ BẠN ĐỌC — GỬI LỜI CHÚC PHÚC (#SECTION_LOICHUC) --}}
            <div id="SECTION_LOICHUC" class="ladi-section sg-phan sg-lc">
                <div class="ladi-section-background"></div>
                <div class="ladi-container sg-hai-cot">
                    <span class="sg-nhan">Hộp Thư Bạn Đọc</span>
                    <div class="sg-cot-chu">
                        <div class="sg-dau-muc sg-hien" data-hieu-ung="len">
                            <div id="TXT_LC_PHU" class="ClientEditText sg-kicker"><p class="ladi-paragraph">Mục thư tín</p></div>
                            <div id="TXT_LC_TD" class="ClientEditText sg-tieu-de"><h2 class="ladi-headline">Gửi Lời Chúc Phúc</h2></div>
                            <div class="sg-gach"><svg aria-hidden="true"><use href="#sg-bong"/></svg></div>
                            <div id="TXT_LC_MOTA" class="ClientEditText sg-mo-ta">
                                <p class="ladi-paragraph">Mỗi dòng chữ của Quý khách sẽ được lưu giữ trang trọng trong số báo kỷ niệm trăm năm của hai vợ chồng.</p>
                            </div>
                        </div>
                        <svg class="sg-ve-them" aria-hidden="true"><use href="#sg-ben-thanh"/></svg>
                    </div>

                    <div class="sg-hop-thu sg-hien" data-hieu-ung="len">
                        <div class="dau-ht">Thư gửi toà soạn</div>

                        <div id="FORM_LOI_CHUC" class="ClientWishes ladi-element">
                            <form id="mayhenWishForm" class="mayhen_invitation_wish_form">
                                <div class="mayhen_invitation_form_group">
                                    <label class="mayhen_invitation_form_label" for="mayhenWishName">Tên người gửi thư *</label>
                                    <input
                                        type="text"
                                        id="mayhenWishName"
                                        name="author_name"
                                        class="mayhen_invitation_input"
                                        placeholder="Nhập quý danh của bạn..."
                                        required
                                        maxlength="100"
                                    >
                                </div>
                                <div class="mayhen_invitation_form_group">
                                    <label class="mayhen_invitation_form_label" for="mayhenWishMessage">Nội dung thư chúc phúc *</label>
                                    <textarea
                                        id="mayhenWishMessage"
                                        name="content"
                                        rows="3"
                                        class="mayhen_invitation_textarea"
                                        placeholder="Viết đôi dòng chúc mừng trăm năm hạnh phúc..."
                                        required
                                        maxlength="500"
                                    ></textarea>
                                </div>
                                <button type="submit" id="mayhenWishSubmitBtn" class="mayhen_invitation_submit_btn mayhen_invitation_submit_btn--blue">
                                    <span>Đăng Lên Số Báo Hạnh Phúc</span>
                                </button>
                            </form>

                            <div id="mayhenWishesList" class="mayhen_invitation_wishes_list">
                                @forelse($approvedWishes as $wish)
                                    <div class="mayhen_invitation_wish_item">
                                        <div class="mayhen_invitation_wish_header">
                                            <span class="mayhen_invitation_wish_author">{{ $wish->name ?? $wish->author_name ?? 'Độc giả thân thiết' }}</span>
                                            <span class="mayhen_invitation_wish_time">{{ optional($wish->created_at)->format('d.m.Y') ?: 'Ấn phẩm đặc biệt' }}</span>
                                        </div>
                                        <p class="mayhen_invitation_wish_text">{{ $wish->message ?? $wish->content ?? '' }}</p>
                                    </div>
                                @empty
                                    <div class="mayhen_invitation_wish_item">
                                        <div class="mayhen_invitation_wish_header">
                                            <span class="mayhen_invitation_wish_author">Toà Soạn &amp; Bằng Hữu</span>
                                            <span class="mayhen_invitation_wish_time">Số Đặc Biệt {{ $dayNum }}.{{ $monthNum }}</span>
                                        </div>
                                        <p class="mayhen_invitation_wish_text">Chúc hai bạn {{ $groomName }} &amp; {{ $brideName }} trăm năm tình viên mãn, bạc đầu nghĩa phu thê, mãi giữ vẹn nguyên nét lãng mạn như những dòng thư Sài Gòn xưa!</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sg-vach sg-tt" aria-hidden="true"></div>

            {{-- 10D. LỜI CUỐI & TOÀN CẢNH ĐƯỜNG PHỐ SÀI GÒN XƯA (#SECTION_KET) --}}
            <div id="SECTION_KET" class="ladi-section sg-phan sg-ket">
                <div class="ladi-section-background"></div>
                <div class="ladi-container">
                    <span class="sg-nhan">Lời cuối</span>

                    <div class="sg-anh-ket sg-hien" data-hieu-ung="rua">
                        <div class="sg-anh">
                            <div id="IMG_KET" class="ClientEditImage ladi-element" data-lightbox-src="{{ $endingImageUrl }}">
                                <div class="ladi-image">
                                    <div class="ladi-image-background" style="background-image: url('{{ $endingImageUrl }}');"></div>
                                    <img
                                        src="{{ $endingImageUrl }}"
                                        alt="Ảnh cảm ơn {{ $coupleNames }}"
                                        class="mayhen_invitation_photo_el"
                                        data-sync-target="ending_image"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="TXT_CAM_ON" class="ClientEditText cam-on sg-hien" data-hieu-ung="in">
                        <h2 class="ladi-headline" data-sync-target="thank_you_title">{{ $thankYouTitle }}</h2>
                    </div>

                    <div id="TXT_LOI_KET" class="ClientEditText loi-ket sg-hien" data-hieu-ung="len">
                        <p class="ladi-paragraph" data-sync-target="thank_you_text">{{ $thankYouText }}</p>
                    </div>

                    <div class="sg-ten-ket sg-hien" data-hieu-ung="len">
                        <div id="TXT_KET_CAP" class="ClientEditText sg-1dong">
                            <p class="ladi-headline">
                                <span data-sync-target="groom_name">{{ $groomName }}</span>
                                &amp;
                                <span data-sync-target="bride_name">{{ $brideName }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="sg-het">— <span>HẾT</span> —</div>

                    {{-- Toàn cảnh đường phố Sài Gòn xưa cuối báo (Nhà thờ Đức Bà · Bưu điện · Chợ Bến Thành · Hàng me · Vespa chạy) --}}
                    <div class="sg-pho" aria-hidden="true">
                        <svg class="bau-troi" viewBox="0 0 800 175" preserveAspectRatio="xMidYMax slice">
                            <circle cx="670" cy="44" r="24" fill="#E8B85C" opacity=".65"/>
                            <path d="M510 42 Q515 36 520 42 Q525 36 530 42" fill="none" stroke="#2A2420" stroke-width="1.3"/>
                            <path d="M540 32 Q544 27 548 32 Q552 27 556 32" fill="none" stroke="#2A2420" stroke-width="1.2"/>

                            {{-- Nhà thờ Đức Bà bên trái --}}
                            <g stroke="#2A2420" stroke-width="1.4" stroke-linejoin="round">
                                <polygon points="78,76 92,30 106,76" fill="#8E3A27"/>
                                <polygon points="122,76 136,30 150,76" fill="#8E3A27"/>
                                <line x1="92" y1="22" x2="92" y2="30" stroke-width="1.6"/>
                                <line x1="88" y1="26" x2="96" y2="26" stroke-width="1.6"/>
                                <line x1="136" y1="22" x2="136" y2="30" stroke-width="1.6"/>
                                <line x1="132" y1="26" x2="140" y2="26" stroke-width="1.6"/>
                                <rect x="78" y="76" width="28" height="72" fill="#C9644C"/>
                                <rect x="122" y="76" width="28" height="72" fill="#C9644C"/>
                                <rect x="102" y="90" width="24" height="58" fill="#B4513A"/>
                                <circle cx="114" cy="106" r="7" fill="#FBF6EA"/>
                                <path d="M107 148 V128 A7 7 0 0 1 121 128 V148 Z" fill="#2A2420"/>
                            </g>

                            <use href="#sg-cay-me" x="175" y="70" width="66" height="85"/>

                            {{-- Chợ Bến Thành ở giữa --}}
                            <use href="#sg-ben-thanh" x="280" y="48" width="240" height="110"/>

                            <use href="#sg-cay-me" x="545" y="70" width="66" height="85"/>

                            {{-- Bưu điện Trung tâm bên phải --}}
                            <g stroke="#2A2420" stroke-width="1.4" stroke-linejoin="round">
                                <rect x="628" y="90" width="124" height="58" fill="#EFD9A0"/>
                                <path d="M654 90 A36 24 0 0 1 726 90 Z" fill="#D9A33A"/>
                                <circle cx="690" cy="82" r="7" fill="#FBF6EA"/>
                                <path d="M690 82 V77 M690 82 L694 83" stroke-width="1.3"/>
                                <path d="M674 148 V116 A16 14 0 0 1 706 116 V148 Z" fill="#2F5644"/>
                                <rect x="638" y="106" width="14" height="24" fill="#2F5644"/>
                                <rect x="728" y="106" width="14" height="24" fill="#2F5644"/>
                            </g>

                            {{-- Mặt đường Sài Gòn --}}
                            <rect x="0" y="148" width="800" height="27" fill="#3B322C"/>
                            <line x1="0" y1="148" x2="800" y2="148" stroke="#2A2420" stroke-width="2"/>
                            <line x1="20" y1="162" x2="780" y2="162" stroke="#EAD8B1" stroke-width="1.6" stroke-dasharray="16 14" opacity=".65"/>
                        </svg>
                        <svg class="xe-pho"><use href="#sg-vespa"/></svg>
                    </div>
                </div>
            </div>
        </section>
        @endif

    </div>

    {{-- ==================== LIGHTBOX XEM ẢNH & MÃ VIETQR (.tmx-xem) ==================== --}}
    <div id="mayhenLightbox" class="tmx-xem" role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to" aria-hidden="true">
        <div class="tmx-dau">
            <span id="mayhenLbCounter">1 / 10</span>
            <button type="button" id="mayhenLbClose" class="tmx-dong" aria-label="Đóng">×</button>
        </div>
        <div class="tmx-khung">
            <button type="button" id="mayhenLbPrev" class="tmx-mui trai" aria-label="Ảnh trước">‹</button>
            <img id="mayhenLbImage" src="{{ $heroImageUrl }}" alt="Ảnh phóng to">
            <button type="button" id="mayhenLbNext" class="tmx-mui phai" aria-label="Ảnh tiếp theo">›</button>
        </div>
        <div id="mayhenLbCaption" class="tmx-chu-qr"></div>
    </div>

    {{-- Toast Notification --}}
    <div id="mayhenToast" class="mayhen_invitation_toast" role="status" aria-live="polite"></div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v("mau-thiep/{$templateSlug}/assets/script.js") }}"></script>
@endpush
