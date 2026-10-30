@php
    $assetBase = url('/mau-thiep/dau-ky-tram-nam/assets');
    $isEmbed = request()->boolean('embed');

    // 1. Tên cô dâu chú rể & Khách mời
    $groomName = $wedding->groom_name ?? 'Quốc Thái';
    $brideName = $wedding->bride_name ?? 'Thuý Ngân';
    $guestName = request()->query('guest') ?: ($invitation->guest_name ?? 'Bạn và Gia Đình');
    $extraData = $wedding->extra_data ?? [];

    // Viết tắt chữ cái đầu cho tem sáp Monogram
    $groomWords = array_values(array_filter(explode(' ', trim((string) $groomName))));
    $brideWords = array_values(array_filter(explode(' ', trim((string) $brideName))));
    $groomInitial = mb_substr(end($groomWords) ?: 'T', 0, 1, 'UTF-8');
    $brideInitial = mb_substr(end($brideWords) ?: 'N', 0, 1, 'UTF-8');

    // Bố mẹ hai bên
    $groomFather = !empty($extraData['groom_father']) ? $extraData['groom_father'] : 'Trần Văn Quân';
    $groomMother = !empty($extraData['groom_mother']) ? $extraData['groom_mother'] : 'Lâm Thị Quyên';
    $brideFather = !empty($extraData['bride_father']) ? $extraData['bride_father'] : 'Lư Xùng Mừng';
    $brideMother = !empty($extraData['bride_mother']) ? $extraData['bride_mother'] : 'Nguyễn Thị Lệ Hằng';

    // 2. Thời gian cưới & Sự kiện
    $weddingDate = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2026, 3, 21, 10, 0, 0);

    $weddingDay = $weddingDate->format('d');
    $weddingMonth = $weddingDate->format('m');
    $weddingYear = $weddingDate->format('Y');
    $weddingTime = $weddingDate->format('H:i');
    $weddingTimeFormatted = $weddingDate->format('H') . ' GIỜ ' . $weddingDate->format('i');
    $weddingDayFormatted = $weddingDate->format('d \T\H\Á\N\G m \N\Ă\M Y');
    $coverDateFormatted = $weddingDate->format('d . m . Y');
    $weddingIso = $weddingDate->toIso8601String();

    $dayNames = [
        'Sunday' => 'CHỦ NHẬT',
        'Monday' => 'THỨ HAI',
        'Tuesday' => 'THỨ BA',
        'Wednesday' => 'THỨ TƯ',
        'Thursday' => 'THỨ NĂM',
        'Friday' => 'THỨ SÁU',
        'Saturday' => 'THỨ BẢY',
    ];
    $weddingDayOfWeek = $dayNames[$weddingDate->format('l')] ?? 'THỨ BẢY';

    // Địa chỉ tiệc cưới
    $eventAddress = !empty($extraData['event_party_address'])
        ? $extraData['event_party_address']
        : 'Khóm An Tài, Phường An Bình, Tỉnh Đồng Tháp';
    $lunarDate = !empty($extraData['event_party_lunar'])
        ? $extraData['event_party_lunar']
        : '03 tháng 02 năm Bính Ngọ';
    $mapUrl = !empty($extraData['event_party_map_url'])
        ? $extraData['event_party_map_url']
        : ('https://www.google.com/maps/search/?api=1&query=' . urlencode($eventAddress));

    // Hàm resolve asset chuẩn an toàn
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

    // Ảnh chính & Lời tựa
    $coverPhoto = $resolveAsset($wedding->cover_photo_url ?? ($extraData['hero_photo_url'] ?? null), 'muslims-hero-bg-20231207085803-t1lex.jpg');
    $saveDatePhoto = $resolveAsset($extraData['save_date_photo_url'] ?? null, 'hip_1854-20250417154226-mql7h.jpg');
    $groomPhoto = $resolveAsset($extraData['groom_photo_url'] ?? null, 'hip_1854-20250417154226-mql7h.jpg');
    $bridePhoto = $resolveAsset($extraData['bride_photo_url'] ?? null, 'hip_2374-20250417154254-fetzp.jpg');
    $partyTime = $extraData['party_time'] ?? $weddingTimeFormatted;
    $partyVenue = $extraData['party_venue'] ?? 'Tư gia';
    $coupleQuote = !empty($extraData['couple_quote'])
        ? $extraData['couple_quote']
        : 'Tình yêu của anh và em là một hành trình kỳ diệu, vượt qua bao thử thách để cùng nhau bước đến ngày trọng đại đám cưới của chúng mình.';

    // 3. Mừng cưới & Ngân hàng
    $cleanGroomAcc = preg_replace('/[^0-9A-Za-z]/', '', $extraData['groom_bank_account'] ?? '1012345678');
    $cleanBrideAcc = preg_replace('/[^0-9A-Za-z]/', '', $extraData['bride_bank_account'] ?? '190398765432');
    $groomBankName = $extraData['groom_bank_name'] ?? 'Vietcombank';
    $brideBankName = $extraData['bride_bank_name'] ?? 'Techcombank';

    $qrGroom = !empty($extraData['qr_groom_image']) 
        ? $resolveAsset($extraData['qr_groom_image'], '')
        : (!empty($groomBankName) && !empty($cleanGroomAcc) 
            ? 'https://img.vietqr.io/image/' . urlencode(strtolower(str_replace(' ', '', $groomBankName))) . '-' . $cleanGroomAcc . '-compact2.png?amount=0&addInfo=' . urlencode('Mung cuoi ' . $groomName) . '&accountName=' . urlencode($extraData['groom_account_name'] ?? 'TRAN QUOC THAI')
            : '');

    $qrBride = !empty($extraData['qr_bride_image']) 
        ? $resolveAsset($extraData['qr_bride_image'], '')
        : (!empty($brideBankName) && !empty($cleanBrideAcc) 
            ? 'https://img.vietqr.io/image/' . urlencode(strtolower(str_replace(' ', '', $brideBankName))) . '-' . $cleanBrideAcc . '-compact2.png?amount=0&addInfo=' . urlencode('Mung cuoi ' . $brideName) . '&accountName=' . urlencode($extraData['bride_account_name'] ?? 'LU THUY NGAN')
            : '');

    $groomBankInfo = [
        'bank' => $groomBankName,
        'name' => $groomBankName,
        'holder' => $extraData['groom_account_name'] ?? 'TRẦN QUỐC THÁI',
        'account' => $extraData['groom_bank_account'] ?? '1012345678',
        'number' => $extraData['groom_bank_account'] ?? '1012345678',
        'qr' => $qrGroom,
    ];
    $brideBankInfo = [
        'bank' => $brideBankName,
        'name' => $brideBankName,
        'holder' => $extraData['bride_account_name'] ?? 'LƯ THUÝ NGÂN',
        'account' => $extraData['bride_bank_account'] ?? '190398765432',
        'number' => $extraData['bride_bank_account'] ?? '190398765432',
        'qr' => $qrBride,
    ];

    // 4. Nhạc nền
    $musicUrl = $resolveAsset($wedding->music_url ?? null, '');

    // 5. Album ảnh cưới (10 ảnh fallback chuẩn file cục bộ có thật)
    $rawGallery = $wedding->extra_data['gallery'] ?? [];
    $validGallery = is_array($rawGallery)
        ? array_filter(
            $rawGallery,
            fn($item) => !empty(trim((string) $item)) && !str_starts_with((string) $item, 'blob:'),
        )
        : [];
    $validGallery = array_values($validGallery);

    $defaultGallery = [
        'muslims-hero-bg-20231207085803-t1lex.jpg',
        'hip_1055-20250417154150-fnhf7.jpg',
        'hip_0956-20250417154149-1cpow.jpg',
        'hip_0991-20250418032446-bi00o.jpg',
        'hip_1314-20250417154153-ezy7i.jpg',
        'hip_1854-20250417154226-mql7h.jpg',
        'hip_2374-20250417154254-fetzp.jpg',
        'hip_1350-20250417154155-wsx2n.jpg',
        'hip_1524-20250418032309-dzaln.jpg',
        'hip_1629-20250417154214-bay2t.jpg',
    ];
    $albumPhotos = [];
    for ($ai = 0; $ai < 10; $ai++) {
        $p = $validGallery[$ai] ?? ($defaultGallery[$ai] ?? 'muslims-hero-bg-20231207085803-t1lex.jpg');
        $albumPhotos[$ai] = $resolveAsset($p, $defaultGallery[$ai] ?? 'muslims-hero-bg-20231207085803-t1lex.jpg');
    }

    // Lịch trình timeline
    $defaultTimeline = [
        ['time' => '08:30', 'title' => 'Đón Khách Nhà Gái'],
        ['time' => '10:00', 'title' => 'Lễ Thành Hôn Nhà Trai'],
        ['time' => '11:30', 'title' => 'Khai Tiệc Mừng Cưới'],
        ['time' => '13:00', 'title' => 'Chụp Ảnh Kỷ Niệm'],
    ];
    $rawTimeline = $extraData['timeline'] ?? [];
    $timelineList = is_array($rawTimeline) && count($rawTimeline) > 0 ? array_values($rawTimeline) : $defaultTimeline;

    // 6. Lời chúc & RSVP URLs
    $wishes = $wishes ?? collect([]);
    $wishSubmitUrl = $wishSubmitUrl ?? (isset($invitation)
        ? route('invitation.wish.store', $invitation->slug)
        : route('templates.preview.wish', 'dau-ky-tram-nam'));

    $rsvpSubmitUrl = $rsvpSubmitUrl ?? (isset($invitation)
        ? route('rsvp.submit', $invitation->slug)
        : route('templates.preview.rsvp', 'dau-ky-tram-nam'));

    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Dấu Ký Trăm Năm';
    $pageTitle = $isPreview 
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name') 
        : 'Thiệp mời cưới: "' . $groomName . ' & ' . $brideName . '" | ' . config('app.name');

    $cssPath = resource_path('views/client/invitations/dau-ky-tram-nam/styles.css');
    $jsPath = resource_path('views/client/invitations/dau-ky-tram-nam/script.js');
    $cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
    $jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();
@endphp

@extends('client.layouts.invitation')

@section('html_class', 'mayhen_invitation_jeowinids483fr_root' . ($isEmbed ? ' mayhen_invitation_jeowinids483fr_embed_mode' : ''))
@section('title', $pageTitle)
@section('meta_description', $isPreview ? 'Trải nghiệm xem thử mẫu thiệp cưới ' . $templateName . ' trên ' . config('app.name') : 'Thiệp cưới ' . $groomName . ' & ' . $brideName . ' – ngày ' . $coverDateFormatted . '.')
@section('body_class', 'mayhen_invitation_wrapper' . ($isEmbed ? ' mayhen_invitation_jeowinids483fr_embed_mode' : ''))

@push('fonts')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;600;700&family=Philosopher:wght@400;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ url('/mau-thiep/dau-ky-tram-nam/assets/styles.css') }}?v={{ $cssVersion }}">
@endpush

@section('content')
{{-- 2 CÁNH CỬA TÌNH YÊU HOÀNG GIA MỞ SANG 2 BÊN (ROYAL 3D DOUBLE GATE OPENING) --}}
<div id="mayhen-door-stage" class="mayhen_invitation_jeowinids483fr_door_stage {{ $isEmbed ? 'opened mayhen_door_hidden' : '' }}" style="{{ $isEmbed ? 'display: none !important;' : '' }}" aria-label="Cánh cửa tình yêu">
    <!-- Cánh cửa bên trái -->
    <div class="mayhen_invitation_jeowinids483fr_door mayhen_invitation_jeowinids483fr_door_left">
        <div class="mayhen_invitation_jeowinids483fr_door_surface">
            <div class="mayhen_invitation_jeowinids483fr_door_frame">
                <div class="mayhen_invitation_jeowinids483fr_door_inner_frame">
                    <div class="mayhen_invitation_jeowinids483fr_door_corner mayhen_invitation_jeowinids483fr_tl"></div>
                    <div class="mayhen_invitation_jeowinids483fr_door_corner mayhen_invitation_jeowinids483fr_bl"></div>
                    <div class="mayhen_invitation_jeowinids483fr_door_filigree"></div>
                </div>
            </div>
            <div class="mayhen_invitation_jeowinids483fr_door_handle mayhen_invitation_jeowinids483fr_door_handle_left"></div>
        </div>
    </div>

    <!-- Cánh cửa bên phải -->
    <div class="mayhen_invitation_jeowinids483fr_door mayhen_invitation_jeowinids483fr_door_right">
        <div class="mayhen_invitation_jeowinids483fr_door_surface">
            <div class="mayhen_invitation_jeowinids483fr_door_frame">
                <div class="mayhen_invitation_jeowinids483fr_door_inner_frame">
                    <div class="mayhen_invitation_jeowinids483fr_door_corner mayhen_invitation_jeowinids483fr_tr"></div>
                    <div class="mayhen_invitation_jeowinids483fr_door_corner mayhen_invitation_jeowinids483fr_br"></div>
                    <div class="mayhen_invitation_jeowinids483fr_door_filigree"></div>
                </div>
            </div>
            <div class="mayhen_invitation_jeowinids483fr_door_handle mayhen_invitation_jeowinids483fr_door_handle_right"></div>
        </div>
    </div>

    <!-- Trung tâm: Thiệp mời & Con dấu sáp hoàng gia -->
    <div class="mayhen_invitation_jeowinids483fr_door_center" id="mayhen-door-center">
        <div class="mayhen_invitation_jeowinids483fr_door_card">
            <!-- 4 góc hoa văn cổ điển mạ vàng -->
            <div class="mayhen_invitation_jeowinids483fr_card_corner mayhen_invitation_jeowinids483fr_cc_tl"></div>
            <div class="mayhen_invitation_jeowinids483fr_card_corner mayhen_invitation_jeowinids483fr_cc_tr"></div>
            <div class="mayhen_invitation_jeowinids483fr_card_corner mayhen_invitation_jeowinids483fr_cc_bl"></div>
            <div class="mayhen_invitation_jeowinids483fr_card_corner mayhen_invitation_jeowinids483fr_cc_br"></div>

            <div class="mayhen_invitation_jeowinids483fr_card_crest">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l2.4 5 5.6.8-4 4 1 5.5-5-2.7-5 2.7 1-5.5-4-4 5.6-.8z"/>
                </svg>
            </div>
            <div class="mayhen_invitation_jeowinids483fr_card_badge">WEDDING INVITATION</div>
            <div class="mayhen_invitation_jeowinids483fr_card_sub">THE WEDDING CELEBRATION</div>
            <h1 class="mayhen_invitation_jeowinids483fr_card_names"><span data-sync-target="groom_name">{{ $groomName }}</span> <span class="mayhen_invitation_jeowinids483fr_amp">&amp;</span> <span data-sync-target="bride_name">{{ $brideName }}</span></h1>
            <div class="mayhen_invitation_jeowinids483fr_card_divider">
                <span class="mayhen_invitation_jeowinids483fr_divider_diamond">✦</span>
            </div>
            <p class="mayhen_invitation_jeowinids483fr_card_guest_label">Trân trọng kính mời</p>
            <div class="mayhen_invitation_jeowinids483fr_card_guest" data-sync-target="guest_name">{{ $guestName }}</div>
            <p class="mayhen_invitation_jeowinids483fr_card_date" data-sync-target="wedding_date">{{ $coverDateFormatted }}</p>
            <p class="mayhen_invitation_jeowinids483fr_card_lunar" data-sync-target="wedding_lunar_date" style="display:none;">{{ $lunarDate }}</p>
        </div>

        <!-- Con dấu sáp đỏ Bordeaux hoàng gia (Royal Wax Seal) -->
        <div class="mayhen_invitation_jeowinids483fr_wax_seal_wrap" id="mayhen-wax-seal" role="button" tabindex="0" title="Chạm để mở cánh cửa tình yêu">
            <div class="mayhen_invitation_jeowinids483fr_wax_seal_glow"></div>
            <!-- Dải ruy băng lụa buông nhẹ sau con dấu -->
            <div class="mayhen_invitation_jeowinids483fr_wax_ribbon mayhen_invitation_jeowinids483fr_ribbon_left"></div>
            <div class="mayhen_invitation_jeowinids483fr_wax_ribbon mayhen_invitation_jeowinids483fr_ribbon_right"></div>
            <div class="mayhen_invitation_jeowinids483fr_wax_seal">
                <div class="mayhen_invitation_jeowinids483fr_wax_rim">
                    <div class="mayhen_invitation_jeowinids483fr_wax_monogram">{{ $groomInitial }}<span class="mayhen_invitation_jeowinids483fr_monogram_amp">&amp;</span>{{ $brideInitial }}</div>
                </div>
            </div>
            <div class="mayhen_invitation_jeowinids483fr_open_hint">
                <span class="mayhen_invitation_jeowinids483fr_hint_icon">✨</span>
                <span class="mayhen_invitation_jeowinids483fr_hint_text">Chạm để mở cánh cửa tình yêu</span>
            </div>
        </div>
    </div>

    <!-- Lớp cánh hoa rơi & bụi sao vàng kim lộng lẫy -->
    <div id="mayhen-door-particles" class="mayhen_invitation_jeowinids483fr_door_particles" aria-hidden="true"></div>
</div>

<div class="mayhen_invitation_jeowinids483fr_shell">
      <div
        aria-label="Nội dung thiệp cưới"
        class="mayhen_invitation_jeowinids483fr_stage-spacer"
      >
        <div class="mayhen_invitation_jeowinids483fr_stage">
          <div
            class="mayhen_invitation_jeowinids483fr_ladi-section mayhen_invitation_jeowinids483fr_section3"
            data-mayhen-section="couple"
          >
            <div
              class="mayhen_invitation_jeowinids483fr_ladi-section-background"
            ></div>
            <div class="mayhen_invitation_jeowinids483fr_ladi-container">
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_image51"
                data-sync-target="cover_photo_url"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="cover_photo_url"
                    style="background-image: url('{{ $coverPhoto }}');"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_image210"
                data-sync-target="save_date_photo_url"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="save_date_photo_url"
                    style="background-image: url('{{ $saveDatePhoto }}');"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_paragraph59"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-paragraph" data-sync-target="couple_quote">
                  {{ $coupleQuote }}
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_paragraph61"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-paragraph">
                  Đám cưới này là lời cam kết chân thành, là sự bắt đầu của một
                  chương mới – nơi chúng ta cùng vun đắp tổ ấm, cùng sẻ chia mọi
                  vui buồn và cùng nắm tay nhau đi đến cuối con đường mang tên
                  hạnh phúc.
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group134"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_box86"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-box mayhen_invitation_jeowinids483fr_ladi-transition"
                    ></div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_image209"
                  >
                    <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_image52"
              >
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-image mayhen_invitation_jeowinids483fr_ladi-transition"
                >
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_image211"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_line14"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-line">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-line-container"
                  ></div>
                </div>
              </div>
              <div class="mayhen_invitation_jeowinids483fr_calendar_card">
                <div class="mayhen_invitation_jeowinids483fr_calendar_header">
                  <h3 class="mayhen_invitation_jeowinids483fr_calendar_month" data-sync-target="calendar_month_year">
                    Tháng <span data-sync-target="wedding_month">{{ $weddingMonth }}</span> · <span data-sync-target="wedding_year">{{ $weddingYear }}</span>
                  </h3>
                </div>
                <div class="mayhen_invitation_jeowinids483fr_calendar_weekdays">
                  <span>MON</span><span>TUE</span><span>WED</span><span>THU</span><span>FRI</span><span>SAT</span><span>SUN</span>
                </div>
                <div class="mayhen_invitation_jeowinids483fr_calendar_days" data-sync-target="wedding_calendar" data-date="{{ $weddingDate->format('Y-m-d') }}">
                  @php
                    $daysInMonth = (int) $weddingDate->daysInMonth;
                    $startDayOfWeek = ($weddingDate->copy()->startOfMonth()->dayOfWeekIso - 1);
                    $curDay = (int) $weddingDay;
                  @endphp
                  @for ($b = 0; $b < $startDayOfWeek; $b++)
                    <span class="mayhen_cal_blank mayhen_invitation_ladoifji894yfv_cal_empty"></span>
                  @endfor
                  @for ($d = 1; $d <= $daysInMonth; $d++)
                    <span class="mayhen_cal_day {{ $d === $curDay ? 'mayhen_cal_active mayhen_invitation_ladoifji894yfv_cal_target' : '' }}">{{ $d }}</span>
                  @endfor
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_headline240"
              >
                <h1 class="mayhen_invitation_jeowinids483fr_ladi-headline">
                  Our Story
                </h1>
              </div>
            </div>
          </div>
          <div
            class="mayhen_invitation_jeowinids483fr_ladi-section mayhen_invitation_jeowinids483fr_section25"
          >
            <div
              class="mayhen_invitation_jeowinids483fr_ladi-section-background"
            ></div>
            <div class="mayhen_invitation_jeowinids483fr_ladi-container">
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_image213"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_box89"
              >
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-box mayhen_invitation_jeowinids483fr_ladi-transition"
                ></div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_headline293"
              >
                <h1 class="mayhen_invitation_jeowinids483fr_ladi-headline">
                  ALL OF ME
                </h1>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_headline294"
              >
                <h1 class="mayhen_invitation_jeowinids483fr_ladi-headline">
                  ALL OF YOU
                </h1>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_headline295"
              >
                <h1 class="mayhen_invitation_jeowinids483fr_ladi-headline">
                  The
                </h1>
              </div>
            </div>
          </div>
          <div
            class="mayhen_invitation_jeowinids483fr_ladi-section mayhen_invitation_jeowinids483fr_section26"
            data-mayhen-section="groom_family,bride_family"
          >
            <div
              class="mayhen_invitation_jeowinids483fr_ladi-section-background"
            ></div>
            <div class="mayhen_invitation_jeowinids483fr_ladi-container">
              <span data-sync-target="groom_photo_url" data-photo="{{ $groomPhoto }}" style="display:none;"></span>
              <span data-sync-target="bride_photo_url" data-photo="{{ $bridePhoto }}" style="display:none;"></span>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_image226"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image225"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_image222"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group106"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group107"
                    data-mayhen-section="bride_family"
                  >
                    <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_paragraph34"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-paragraph mayhen_invitation_jeowinids483fr_ladi-transition"
                        >
                          Nhà gái
                        </div>
                      </div>
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_paragraph35"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-paragraph mayhen_invitation_jeowinids483fr_ladi-transition"
                        >
                          Ông: <span data-sync-target="bride_father">{{ $brideFather }}</span><br />Bà: <span data-sync-target="bride_mother">{{ $brideMother }}</span><br />
                        </div>
                      </div>
                    </div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group108"
                    data-mayhen-section="groom_family"
                  >
                    <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_paragraph36"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-paragraph mayhen_invitation_jeowinids483fr_ladi-transition"
                        >
                          Nhà trai
                        </div>
                      </div>
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_paragraph37"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-paragraph mayhen_invitation_jeowinids483fr_ladi-transition"
                        >
                          Ông: <span data-sync-target="groom_father">{{ $groomFather }}</span><br />Bà: <span data-sync-target="groom_mother">{{ $groomMother }}</span><br />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group104"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_class_tenkhachmoi mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_headline107"
                  >
                    <h3
                      class="mayhen_invitation_jeowinids483fr_ladi-headline mayhen_invitation_jeowinids483fr_ladi-transition"
                    >
                      <div
                        style="
                          display: inline-block;
                          padding: 0px 10px 2px 10px;
                          border-radius: 15px;
                          border: 1px solid #333;
                        "
                      >
                        <span
                          style="
                            display: block;
                            word-wrap: break-word;
                            max-width: 100%;
                          "
                          ><span data-sync-target="guest_name">{{ $guestName }}</span></span
                        >
                      </div>
                      <br />
                    </h3>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_paragraph33"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-paragraph mayhen_invitation_jeowinids483fr_ladi-transition"
                    >
                      TỚI DỰ BỮA TIỆC CHUNG VUI<br />CÙNG GIA ĐÌNH CHÚNG TÔI<br />
                    </div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_headline108"
                  >
                    <h3
                      class="mayhen_invitation_jeowinids483fr_ladi-headline mayhen_invitation_jeowinids483fr_ladi-transition"
                    >
                      TRÂN TRỌNG KÍNH MỜI
                    </h3>
                  </div>
                </div>
              </div>
              <div class="mayhen_invitation_jeowinids483fr_couple_names_wrapper">
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_headline110"
                >
                  <h3
                    class="mayhen_invitation_jeowinids483fr_ladi-headline mayhen_invitation_jeowinids483fr_ladi-transition"
                  >
                    <span data-sync-target="bride_name">{{ $brideName }}</span>
                  </h3>
                </div>
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_headline112"
                >
                  <h3
                    class="mayhen_invitation_jeowinids483fr_ladi-headline mayhen_invitation_jeowinids483fr_ladi-transition"
                  >
                    &amp;
                  </h3>
                </div>
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_headline111"
                >
                  <h3
                    class="mayhen_invitation_jeowinids483fr_ladi-headline mayhen_invitation_jeowinids483fr_ladi-transition"
                  >
                    <span data-sync-target="groom_name">{{ $groomName }}</span>
                  </h3>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_paragraph66"
              >
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-paragraph mayhen_invitation_jeowinids483fr_ladi-transition"
                >
                  <span style="font-size: 18px">VÀO LÚC <span data-sync-target="party_time">{{ $partyTime }}</span><br /></span
                  >{{ $weddingDayOfWeek }}, NGÀY <span data-sync-target="wedding_date">{{ $weddingDayFormatted }}</span><br />
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_paragraph67"
              >
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-paragraph mayhen_invitation_jeowinids483fr_ladi-transition"
                >
                  <span data-sync-target="event_party_address">{{ $eventAddress }}</span>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_paragraph68"
              >
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-paragraph mayhen_invitation_jeowinids483fr_ladi-transition"
                >
                  @php
                    $cleanLunar = trim((string) $lunarDate);
                    $lunarDisplay = (!str_starts_with($cleanLunar, 'Nhằm') && !str_starts_with($cleanLunar, 'Tức'))
                        ? ('Nhằm ngày ' . $cleanLunar)
                        : $cleanLunar;
                  @endphp
                  (<span data-sync-target="event_party_lunar">{{ $lunarDisplay }}</span>)
                  <span data-sync-target="wedding_lunar_date" style="display:none;">{{ $lunarDate }}</span>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_paragraph69"
              >
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-paragraph mayhen_invitation_jeowinids483fr_ladi-transition"
                >
                  TẠI <span data-sync-target="party_venue">{{ $partyVenue }}</span>
                </div>
              </div>
              <a
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_button23"
                href="{{ $mapUrl }}"
                data-sync-target="event_party_map_url"
                role="button"
                tabindex="0"
                target="_blank"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-button">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-button-background"
                  ></div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-button-shape mayhen_invitation_jeowinids483fr_button_shape23"
                  >
                    <div class="mayhen_invitation_jeowinids483fr_ladi-shape">
                      <svg
                        class=""
                        fill="#FFFFFF"
                        height="100%"
                        preserveaspectratio="none"
                        viewbox="0 0 24 24"
                        width="100%"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M12,2C15.31,2 18,4.66 18,7.95C18,12.41 12,19 12,19C12,19 6,12.41 6,7.95C6,4.66 8.69,2 12,2M12,6A2,2 0 0,0 10,8A2,2 0 0,0 12,10A2,2 0 0,0 14,8A2,2 0 0,0 12,6M20,19C20,21.21 16.42,23 12,23C7.58,23 4,21.21 4,19C4,17.71 5.22,16.56 7.11,15.83L7.75,16.74C6.67,17.19 6,17.81 6,18.5C6,19.88 8.69,21 12,21C15.31,21 18,19.88 18,18.5C18,17.81 17.33,17.19 16.25,16.74L16.89,15.83C18.78,16.56 20,17.71 20,19Z"
                        ></path>
                      </svg>
                    </div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-button-headline mayhen_invitation_jeowinids483fr_button_text23"
                  >
                    <p class="mayhen_invitation_jeowinids483fr_ladi-headline">
                         Chỉ Đường
                    </p>
                  </div>
                </div>
              </a>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image224"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image223"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
            </div>
          </div>
          <div
            class="mayhen_invitation_jeowinids483fr_ladi-section mayhen_invitation_jeowinids483fr_section27"
            data-mayhen-section="timeline"
          >
            <div
              class="mayhen_invitation_jeowinids483fr_ladi-section-background"
            ></div>
            <div class="mayhen_invitation_jeowinids483fr_ladi-container">
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image245"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_box94"
              >
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-box mayhen_invitation_jeowinids483fr_ladi-transition"
                ></div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image227"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_headline352"
              >
                <h1 class="mayhen_invitation_jeowinids483fr_ladi-headline">
                  Timeline
                </h1>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group173"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_line47"
                  >
                    <div class="mayhen_invitation_jeowinids483fr_ladi-line">
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-line-container"
                      ></div>
                    </div>
                  </div>
                  @php
                    $timelineIcons = [
                      0 => 'w31_icon5-20240324143948-l5hxp.png',
                      1 => 'nhan-20231213014544-ritpg.png',
                      2 => 'timeline3-20240323075615-el-qz.png',
                      3 => 'icontiec2-20231213014753-naxdk.png',
                    ];
                  @endphp
                  @foreach($timelineList as $tIdx => $tItem)
                    @php
                      $tIconFile = $timelineIcons[$tIdx % 4];
                      $tIconUrl = $resolveAsset($tIconFile, $tIconFile);
                      $tTime = $tItem['time'] ?? '';
                      $tTitle = $tItem['title'] ?? ($tItem['desc'] ?? '');
                      $isOdd = ($tIdx % 2 === 1);
                      $topOffset = $tIdx * 76;
                    @endphp
                    @if(!$isOdd)
                      {{-- Even row: Icon Left, Line Center, Text Right --}}
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group174"
                        style="position: absolute; top: {{ $topOffset }}px; left: 0px;"
                      >
                        <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_line48"
                          >
                            <div class="mayhen_invitation_jeowinids483fr_ladi-line">
                              <div
                                class="mayhen_invitation_jeowinids483fr_ladi-line-container"
                              ></div>
                            </div>
                          </div>
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_image228"
                          >
                            <div
                              class="mayhen_invitation_jeowinids483fr_ladi-image"
                            >
                              <div
                                class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                                style="background-image: url('{{ $tIconUrl }}');"
                              ></div>
                            </div>
                          </div>
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_paragraph70"
                          >
                            <div
                              class="mayhen_invitation_jeowinids483fr_ladi-paragraph"
                            >
                              <span style="font-weight: 700"
                                ><span
                                  style="
                                    color: rgb(77, 88, 23);
                                  "
                                  data-sync-target="timeline.{{ $tIdx }}.time"
                                  >{{ $tTime }}</span
                                ><br /></span
                              ><span data-sync-target="timeline.{{ $tIdx }}.title">{{ $tTitle }}</span><br />
                            </div>
                          </div>
                        </div>
                      </div>
                    @else
                      {{-- Odd row: Text Left, Line Center, Icon Right --}}
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group175"
                        style="position: absolute; top: {{ $topOffset }}px; left: 0px;"
                      >
                        <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_image229"
                          >
                            <div
                              class="mayhen_invitation_jeowinids483fr_ladi-image"
                            >
                              <div
                                class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                                style="background-image: url('{{ $tIconUrl }}');"
                              ></div>
                            </div>
                          </div>
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_line49"
                          >
                            <div class="mayhen_invitation_jeowinids483fr_ladi-line">
                              <div
                                class="mayhen_invitation_jeowinids483fr_ladi-line-container"
                              ></div>
                            </div>
                          </div>
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_paragraph71"
                          >
                            <div
                              class="mayhen_invitation_jeowinids483fr_ladi-paragraph"
                            >
                              <span style="font-weight: 700"
                                ><span style="color: rgb(77, 88, 23)" data-sync-target="timeline.{{ $tIdx }}.time">{{ $tTime }}</span
                                ><br /></span
                              ><span data-sync-target="timeline.{{ $tIdx }}.title">{{ $tTitle }}</span><br />
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif
                  @endforeach
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image244"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
            </div>
          </div>
          <div
            class="mayhen_invitation_jeowinids483fr_ladi-section mayhen_invitation_jeowinids483fr_section28"
            data-mayhen-section="album"
          >
            <div
              class="mayhen_invitation_jeowinids483fr_ladi-section-background"
            ></div>
            <div class="mayhen_invitation_jeowinids483fr_ladi-container">
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_headline353"
              >
                <h3 class="mayhen_invitation_jeowinids483fr_ladi-headline">
                  Our Album
                </h3>
              </div>
              <div
                aria-label="Xem ảnh cưới lớn"
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image232"
                data-mayhen-gallery="true"
                role="button"
                tabindex="0"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="gallery.0"
                    style="background-image: url('{{ $albumPhotos[0] }}');"
                  ></div>
                </div>
              </div>
              <div
                aria-label="Xem ảnh cưới lớn"
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image246"
                data-mayhen-gallery="true"
                role="button"
                tabindex="0"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="gallery.1"
                    style="background-image: url('{{ $albumPhotos[1] }}');"
                  ></div>
                </div>
              </div>
              <div
                aria-label="Xem ảnh cưới lớn"
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image247"
                data-mayhen-gallery="true"
                role="button"
                tabindex="0"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="gallery.2"
                    style="background-image: url('{{ $albumPhotos[2] }}');"
                  ></div>
                </div>
              </div>
              <div
                aria-label="Xem ảnh cưới lớn"
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image248"
                data-mayhen-gallery="true"
                role="button"
                tabindex="0"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="gallery.3"
                    style="background-image: url('{{ $albumPhotos[3] }}');"
                  ></div>
                </div>
              </div>
              <div
                aria-label="Xem ảnh cưới lớn"
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image249"
                data-mayhen-gallery="true"
                role="button"
                tabindex="0"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="gallery.4"
                    style="background-image: url('{{ $albumPhotos[4] }}');"
                  ></div>
                </div>
              </div>
              <div
                aria-label="Xem ảnh cưới lớn"
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image237"
                data-mayhen-gallery="true"
                role="button"
                tabindex="0"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="gallery.5"
                    style="background-image: url('{{ $albumPhotos[5] }}');"
                  ></div>
                </div>
              </div>
              <div
                aria-label="Xem ảnh cưới lớn"
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image238"
                data-mayhen-gallery="true"
                role="button"
                tabindex="0"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="gallery.6"
                    style="background-image: url('{{ $albumPhotos[6] }}');"
                  ></div>
                </div>
              </div>
              <div
                aria-label="Xem ảnh cưới lớn"
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_gallery_extra mayhen_invitation_jeowinids483fr_gallery_extra_0"
                data-mayhen-gallery="true"
                role="button"
                tabindex="0"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="gallery.7"
                    style="background-image: url('{{ $albumPhotos[7] }}');"
                  ></div>
                </div>
              </div>
              <div
                aria-label="Xem ảnh cưới lớn"
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_gallery_extra mayhen_invitation_jeowinids483fr_gallery_extra_1"
                data-mayhen-gallery="true"
                role="button"
                tabindex="0"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="gallery.8"
                    style="background-image: url('{{ $albumPhotos[8] }}');"
                  ></div>
                </div>
              </div>
              <div
                aria-label="Xem ảnh cưới lớn"
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_gallery_extra mayhen_invitation_jeowinids483fr_gallery_extra_2"
                data-mayhen-gallery="true"
                role="button"
                tabindex="0"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                    data-sync-target="gallery.9"
                    style="background-image: url('{{ $albumPhotos[9] }}');"
                  ></div>
                </div>
              </div>
            </div>
          </div>
          <div
            class="mayhen_invitation_jeowinids483fr_ladi-section mayhen_invitation_jeowinids483fr_section29"
          >
            <div
              class="mayhen_invitation_jeowinids483fr_ladi-section-background"
            ></div>
            <div class="mayhen_invitation_jeowinids483fr_ladi-container">
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image251"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image252"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_headline354"
              >
                <h1 class="mayhen_invitation_jeowinids483fr_ladi-headline">
                  Xác Nhận
                </h1>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_group55"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_form3"
                  >
                    <form
                      autocomplete="off"
                      class="mayhen_invitation_jeowinids483fr_ladi-form"
                      method="post"
                    >
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_form_item13"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-form-item-container"
                        >
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-form-item-background"
                          ></div>
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-form-item mayhen_invitation_jeowinids483fr_ladi-form-checkbox mayhen_invitation_jeowinids483fr_ladi-form-checkbox-horizontal"
                          >
                            <div
                              class="mayhen_invitation_jeowinids483fr_ladi-form-checkbox-box-item"
                            >
                              <div
                                class="mayhen_invitation_jeowinids483fr_ladi-form-checkbox-item"
                              >
                                <input
                                  name="form_item13"
                                  tabindex="4"
                                  type="checkbox"
                                  value="Cô dâu"
                                /><span>Cô dâu</span>
                              </div>
                              <div
                                class="mayhen_invitation_jeowinids483fr_ladi-form-checkbox-item"
                              >
                                <input
                                  name="form_item13"
                                  tabindex="4"
                                  type="checkbox"
                                  value="Chú rể"
                                /><span>Chú rể</span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_btn-reload mayhen_invitation_jeowinids483fr_button7"
                        role="button"
                        tabindex="0"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-button mayhen_invitation_jeowinids483fr_ladi-transition"
                        >
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-button-background"
                          ></div>
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-button-headline mayhen_invitation_jeowinids483fr_button_text7"
                          >
                            <p
                              class="mayhen_invitation_jeowinids483fr_ladi-headline mayhen_invitation_jeowinids483fr_ladi-transition"
                            >
                              XÁC NHẬN THAM DỰ
                            </p>
                          </div>
                        </div>
                      </div>
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_form_item8"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-form-item-container"
                        >
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-form-item-background"
                          ></div>
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-form-item"
                          >
                            <input
                              autocomplete="off"
                              class="mayhen_invitation_jeowinids483fr_ladi-form-control"
                              name="name"
                              placeholder="Tên của bạn là gì ?"
                              required=""
                              tabindex="1"
                              type="text"
                              value=""
                            />
                          </div>
                        </div>
                      </div>
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_form_item9"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-form-item-container"
                        >
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-form-item-background"
                          ></div>
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-form-item"
                          >
                            <textarea
                              autocomplete="off"
                              class="mayhen_invitation_jeowinids483fr_ladi-form-control"
                              name="message"
                              placeholder="Gửi lời chúc đến cô dâu chú rể..."
                              tabindex="6"
                            ></textarea>
                          </div>
                        </div>
                      </div>
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_form_item10"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-form-item-container"
                        >
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-form-item-background"
                          ></div>
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-form-item"
                          >
                            <select
                              class="mayhen_invitation_jeowinids483fr_ladi-form-control mayhen_invitation_jeowinids483fr_ladi-form-control-select"
                              name="form_item6"
                              required=""
                              tabindex="2"
                            >
                              <option
                                class="mayhen_invitation_jeowinids483fr_ladi-hidden"
                                value=""
                              >
                                Bạn sẽ đến chứ ?
                              </option>
                              <option value="Mình chắc chắn sẽ đến">
                                Mình chắc chắn sẽ đến
                              </option>
                              <option value="Mình có thể sẽ đến">
                                Mình có thể sẽ đến
                              </option>
                              <option value="Tiếc quá mình không đến được">
                                Tiếc quá mình không đến được
                              </option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_form_item14"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-form-item-container"
                        >
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-form-item-background"
                          ></div>
                          <div
                            class="mayhen_invitation_jeowinids483fr_ladi-form-item"
                          >
                            <input
                              autocomplete="off"
                              class="mayhen_invitation_jeowinids483fr_ladi-form-control"
                              name="form_item14"
                              placeholder="Bạn đi mấy người?"
                              tabindex="3"
                              type="number"
                              value=""
                            />
                          </div>
                        </div>
                      </div>
                      <button
                        class="mayhen_invitation_jeowinids483fr_ladi-hidden"
                        type="submit"
                      ></button>
                    </form>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_headline62"
                  >
                    <h3
                      class="mayhen_invitation_jeowinids483fr_ladi-headline mayhen_invitation_jeowinids483fr_ladi-transition"
                    >
                        Bạn là khách mời của ai?<br />
                    </h3>
                  </div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_paragraph79"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-paragraph">
                  Hãy xác nhận sự hiện diện của Quý Khách<br />để chúng tôi tiếp
                  đón một cách chu đáo nhất.<br />Trân Trọng!<br />
                </div>
              </div>
            </div>
          </div>
          <div
            class="mayhen_invitation_jeowinids483fr_ladi-section mayhen_invitation_jeowinids483fr_section30"
          >
            <div
              class="mayhen_invitation_jeowinids483fr_ladi-section-background"
            ></div>
            <div class="mayhen_invitation_jeowinids483fr_ladi-container">
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group184"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_box96"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-box mayhen_invitation_jeowinids483fr_ladi-transition"
                    ></div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_box97"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-box mayhen_invitation_jeowinids483fr_ladi-transition"
                    ></div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_box98"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-box mayhen_invitation_jeowinids483fr_ladi-transition"
                    ></div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_box99"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-box mayhen_invitation_jeowinids483fr_ladi-transition"
                    ></div>
                  </div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_paragraph81"
              >
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-paragraph mayhen_invitation_jeowinids483fr_ladi-transition"
                >
                  Count Down
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group185"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_headline357"
                  >
                    <h3
                      class="mayhen_invitation_jeowinids483fr_ladi-headline mayhen_invitation_jeowinids483fr_ladi-transition"
                    >
                      Ngày
                    </h3>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_headline358"
                  >
                    <h3
                      class="mayhen_invitation_jeowinids483fr_ladi-headline mayhen_invitation_jeowinids483fr_ladi-transition"
                    >
                      Giờ
                    </h3>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_headline359"
                  >
                    <h3
                      class="mayhen_invitation_jeowinids483fr_ladi-headline mayhen_invitation_jeowinids483fr_ladi-transition"
                    >
                      Phút
                    </h3>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_headline360"
                  >
                    <h3
                      class="mayhen_invitation_jeowinids483fr_ladi-headline mayhen_invitation_jeowinids483fr_ladi-transition"
                    >
                      Giây
                    </h3>
                  </div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_countdown4"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-countdown">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_countdown_item13"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-countdown-background"
                    ></div>
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-countdown-text"
                    >
                      <span>00</span>
                    </div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_countdown_item14"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-countdown-background"
                    ></div>
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-countdown-text"
                    >
                      <span>00</span>
                    </div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_countdown_item15"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-countdown-background"
                    ></div>
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-countdown-text"
                    >
                      <span>00</span>
                    </div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_countdown_item16"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-countdown-background"
                    ></div>
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-countdown-text"
                    >
                      <span>00</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div
            class="mayhen_invitation_jeowinids483fr_ladi-section mayhen_invitation_jeowinids483fr_section12"
          >
            <div
              class="mayhen_invitation_jeowinids483fr_ladi-section-background"
            ></div>
            <div class="mayhen_invitation_jeowinids483fr_ladi-container">
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_image148"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_image149"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_box45"
              >
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-box mayhen_invitation_jeowinids483fr_ladi-transition"
                ></div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_box44"
              >
                <div
                  class="mayhen_invitation_jeowinids483fr_ladi-box mayhen_invitation_jeowinids483fr_ladi-transition"
                ></div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image144"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_headline57"
              >
                <h3 class="mayhen_invitation_jeowinids483fr_ladi-headline">
                  Thank you!<br />
                </h3>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_headline58"
              >
                <h3 class="mayhen_invitation_jeowinids483fr_ladi-headline">
                  Sự hiện diện của bạn là niềm vinh hạnh cho gia đình chúng
                  tôi.<br />Chân thành cảm ơn!<br />
                </h3>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image145"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation-hidden mayhen_invitation_jeowinids483fr_image146"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-image">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-image-background"
                  ></div>
                </div>
              </div>
              <div
                class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group72"
              >
                <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_box58"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-box mayhen_invitation_jeowinids483fr_ladi-transition"
                    ></div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_ladi-animation mayhen_invitation_jeowinids483fr_box59"
                  >
                    <div
                      class="mayhen_invitation_jeowinids483fr_ladi-box mayhen_invitation_jeowinids483fr_ladi-transition"
                    ></div>
                  </div>
                  <div
                    class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_group73"
                  >
                    <div class="mayhen_invitation_jeowinids483fr_ladi-group">
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_off_speaker mayhen_invitation_jeowinids483fr_shape10"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-shape"
                        >
                          <svg
                            class=""
                            fill="rgba(255, 255, 255, 1)"
                            height="100%"
                            preserveaspectratio="none"
                            viewbox="206 206 100 100"
                            width="100%"
                            x="0px"
                            xmlns="http://www.w3.org/2000/svg"
                            y="0px"
                          >
                            <g>
                              <path
                                d="M226.473,243.243h-3.415c-1.665,0-3.014,1.349-3.014,3.014v19.486c0,1.664,1.349,3.014,3.014,3.014h3.415h0.201h6.228   v-25.514h-6.228H226.473z"
                              ></path>
                              <g>
                                <polygon
                                  points="268.26,301 268.26,252.234 243.876,276.617   "
                                ></polygon>
                                <polygon
                                  points="268.26,211 236.116,243.143 236.116,266.194 268.26,234.051   "
                                ></polygon>
                                <rect
                                  height="6.428"
                                  transform="matrix(-0.7071 0.7071 -0.7071 -0.7071 617.6146 254.9795)"
                                  width="119.156"
                                  x="196.422"
                                  y="252.187"
                                ></rect>
                              </g>
                            </g>
                          </svg>
                        </div>
                      </div>
                      <div
                        class="mayhen_invitation_jeowinids483fr_ladi-element mayhen_invitation_jeowinids483fr_on_speaker mayhen_invitation_jeowinids483fr_shape11"
                      >
                        <div
                          class="mayhen_invitation_jeowinids483fr_ladi-shape"
                        >
                          <svg
                            class=""
                            fill="rgba(255, 255, 255, 1)"
                            height="100%"
                            preserveaspectratio="none"
                            viewbox="206 206 100 100"
                            width="100%"
                            x="0px"
                            xmlns="http://www.w3.org/2000/svg"
                            y="0px"
                          >
                            <g>
                              <path
                                d="M217.4,243.3H214c-1.657,0-3,1.343-3,3v19.4c0,1.657,1.343,3,3,3h3.4h0.2h6.2v-25.4h-6.2H217.4z"
                              ></path>
                              <polygon
                                points="227,268.8 259,300.801 259,211.199 227,243.2  "
                              ></polygon>
                              <path
                                d="M270.031,244.424l-4.631,4.63c3.83,3.83,3.83,10.062,0,13.891l4.631,4.63C276.414,261.191,276.414,250.808,270.031,244.424   z"
                              ></path>
                              <path
                                d="M279.291,235.163l-4.629,4.631c8.936,8.936,8.936,23.476,0,32.412l4.631,4.631   C290.781,265.348,290.781,246.653,279.291,235.163z"
                              ></path>
                              <path
                                d="M288.553,225.903l-4.631,4.63c14.043,14.042,14.043,36.892,0,50.935l4.631,4.63   C305.148,269.503,305.148,242.499,288.553,225.903z"
                              ></path>
                            </g>
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- SECTION 6: HỘP MỪNG CƯỚI (GIFT / BANK INFO) -->
          <div
            class="mayhen_invitation_jeowinids483fr_ladi-section mayhen_invitation_jeowinids483fr_gift_section"
            data-mayhen-section="gift"
          >
            <div class="mayhen_invitation_jeowinids483fr_ladi-section-background"></div>
            <div class="mayhen_invitation_jeowinids483fr_gift_container">
              <div class="mayhen_invitation_jeowinids483fr_gift_header">
                <span class="mayhen_invitation_jeowinids483fr_gift_badge">WEDDING GIFT</span>
                <h2 class="mayhen_invitation_jeowinids483fr_gift_title">Hộp Mừng Cưới</h2>
                <p class="mayhen_invitation_jeowinids483fr_gift_subtitle">
                  Sự hiện diện và lời chúc phúc của Quý khách là món quà ý nghĩa nhất dành cho chúng tôi.
                </p>
              </div>

              <div class="mayhen_invitation_jeowinids483fr_gift_cards">
                <!-- Thẻ mừng cưới Chú Rể -->
                <div class="mayhen_invitation_jeowinids483fr_gift_card">
                  <div class="mayhen_invitation_jeowinids483fr_gift_card_role">MỪNG CƯỚI CHÚ RỂ</div>
                  <h4 class="mayhen_invitation_jeowinids483fr_gift_card_name" data-sync-target="groom_name">{{ $groomName }}</h4>
                  <div class="mayhen_invitation_jeowinids483fr_gift_qr_box">
                    <img
                      src="{{ $qrGroom }}"
                      alt="Mã QR Chú rể"
                      class="mayhen_invitation_jeowinids483fr_gift_qr_image"
                      data-sync-target="qr_groom_image"
                    />
                  </div>
                  <div class="mayhen_invitation_jeowinids483fr_gift_bank_row">
                    <span class="mayhen_invitation_jeowinids483fr_gift_bank_label">Ngân hàng:</span>
                    <strong class="mayhen_invitation_jeowinids483fr_gift_bank_value" data-sync-target="groom_bank_name">{{ $groomBankName }}</strong>
                  </div>
                  <div class="mayhen_invitation_jeowinids483fr_gift_bank_row">
                    <span class="mayhen_invitation_jeowinids483fr_gift_bank_label">Số TK:</span>
                    <strong class="mayhen_invitation_jeowinids483fr_gift_bank_value" data-sync-target="groom_bank_account">{{ $groomBankInfo['account'] }}</strong>
                  </div>
                  <div class="mayhen_invitation_jeowinids483fr_gift_bank_row">
                    <span class="mayhen_invitation_jeowinids483fr_gift_bank_label">Chủ TK:</span>
                    <span class="mayhen_invitation_jeowinids483fr_gift_bank_value" data-sync-target="groom_bank_holder">{{ $groomBankInfo['holder'] }}</span>
                  </div>
                </div>

                <!-- Thẻ mừng cưới Cô Dâu -->
                <div class="mayhen_invitation_jeowinids483fr_gift_card">
                  <div class="mayhen_invitation_jeowinids483fr_gift_card_role">MỪNG CƯỚI CÔ DÂU</div>
                  <h4 class="mayhen_invitation_jeowinids483fr_gift_card_name" data-sync-target="bride_name">{{ $brideName }}</h4>
                  <div class="mayhen_invitation_jeowinids483fr_gift_qr_box">
                    <img
                      src="{{ $qrBride }}"
                      alt="Mã QR Cô dâu"
                      class="mayhen_invitation_jeowinids483fr_gift_qr_image"
                      data-sync-target="qr_bride_image"
                    />
                  </div>
                  <div class="mayhen_invitation_jeowinids483fr_gift_bank_row">
                    <span class="mayhen_invitation_jeowinids483fr_gift_bank_label">Ngân hàng:</span>
                    <strong class="mayhen_invitation_jeowinids483fr_gift_bank_value" data-sync-target="bride_bank_name">{{ $brideBankName }}</strong>
                  </div>
                  <div class="mayhen_invitation_jeowinids483fr_gift_bank_row">
                    <span class="mayhen_invitation_jeowinids483fr_gift_bank_label">Số TK:</span>
                    <strong class="mayhen_invitation_jeowinids483fr_gift_bank_value" data-sync-target="bride_bank_account">{{ $brideBankInfo['account'] }}</strong>
                  </div>
                  <div class="mayhen_invitation_jeowinids483fr_gift_bank_row">
                    <span class="mayhen_invitation_jeowinids483fr_gift_bank_label">Chủ TK:</span>
                    <span class="mayhen_invitation_jeowinids483fr_gift_bank_value" data-sync-target="bride_bank_holder">{{ $brideBankInfo['holder'] }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div
      class="mayhen_invitation_jeowinids483fr_ladi-section mayhen_invitation_jeowinids483fr_section_popup"
    >
      <div
        class="mayhen_invitation_jeowinids483fr_ladi-section-background"
      ></div>
      <div class="mayhen_invitation_jeowinids483fr_ladi-container"></div>
    </div>
    <div
      aria-hidden="true"
      class="mayhen_invitation_jeowinids483fr_modal-backdrop"
    ></div>
    <div
      aria-label="Thông tin mừng cưới"
      aria-modal="true"
      class="mayhen_invitation_jeowinids483fr_gift-dialog"
      hidden=""
      role="dialog"
    >
      <button
        aria-label="Đóng"
        class="mayhen_invitation_jeowinids483fr_gift-close"
        type="button"
      >
        ×
      </button>
      <span class="mayhen_invitation_jeowinids483fr_gift-eyebrow"
        >WEDDING GIFT</span
      >
      <h2>Mừng cưới</h2>
      <div class="mayhen_invitation_jeowinids483fr_gift-tabs">
        <button
          class="mayhen_invitation_jeowinids483fr_gift-tab mayhen_invitation_jeowinids483fr_selected"
          data-person="bride"
          type="button"
        >
          Cô dâu
        </button>
        <button
          class="mayhen_invitation_jeowinids483fr_gift-tab"
          data-person="groom"
          type="button"
        >
          Chú rể
        </button>
      </div>
      <p class="mayhen_invitation_jeowinids483fr_gift-note">
        Thông tin chuyển khoản chưa được cấu hình.
      </p>
      <div
        class="mayhen_invitation_jeowinids483fr_gift-details"
        hidden=""
      >
        <div class="mayhen_invitation_jeowinids483fr_gift_qr_wrap">
          <img class="mayhen_invitation_jeowinids483fr_gift_qr_img" src="" alt="Mã QR mừng cưới" />
        </div>
        <div class="mayhen_invitation_jeowinids483fr_gift_bank_name"></div>
        <div class="mayhen_invitation_jeowinids483fr_gift_holder"></div>
        <div class="mayhen_invitation_jeowinids483fr_gift_acc_row">
          <span class="mayhen_invitation_jeowinids483fr_gift_account"></span>
          <button type="button" class="mayhen_invitation_jeowinids483fr_gift_copy_btn">Sao chép</button>
        </div>
      </div>
    </div>
    <button
      aria-label="Đóng hộp thoại"
      class="mayhen_invitation_jeowinids483fr_modal-close"
      type="button"
    >
      ×
    </button>
    <nav
      aria-label="Điều hướng thiệp cưới"
      class="mayhen_invitation_jeowinids483fr_cta-menu"
      style="{{ $isEmbed ? 'display: none !important;' : '' }}"
    >
      <button class="mayhen_active" data-go="section26" type="button">Thiệp mời</button>
      <button data-go="section27" type="button">Lịch trình</button>
      <button data-go="section28" type="button">Album</button>
      <button data-go="section29" type="button">Xác nhận</button>
      <button data-menu="gift" type="button">Mừng cưới</button>
    </nav>
    <div
      aria-label="Album ảnh cưới"
      aria-modal="true"
      class="mayhen_invitation_jeowinids483fr_lightbox"
      role="dialog"
    >
      <button
        aria-label="Đóng"
        class="mayhen_invitation_jeowinids483fr_lightbox-close"
        type="button"
      >
        ×
      </button>
      <button
        aria-label="Ảnh trước"
        class="mayhen_invitation_jeowinids483fr_lightbox-prev"
        type="button"
      >
        ‹
      </button>
      <img
        alt="Ảnh cưới phóng to"
        class="mayhen_invitation_jeowinids483fr_lightbox-photo"
      />
      <button
        aria-label="Ảnh tiếp"
        class="mayhen_invitation_jeowinids483fr_lightbox-next"
        type="button"
      >
        ›
      </button>
    </div>
    <div
      aria-live="polite"
      class="mayhen_invitation_jeowinids483fr_toast"
      role="status"
    ></div>

@endsection

@push('scripts')
    <script>
        window.MAYHEN_INVITATION_CONFIG = {
            templateName: 'Dấu Ký Trăm Năm',
            bride: @json($brideName),
            groom: @json($groomName),
            guestDefault: @json($guestName),
            weddingDate: @json($weddingIso),
            address: @json($eventAddress),
            mapUrl: @json($mapUrl),
            bank: {
                bride: @json($brideBankInfo),
                groom: @json($groomBankInfo)
            },
            music: @json($musicUrl),
            wishSubmitUrl: @json($wishSubmitUrl),
            rsvpSubmitUrl: @json($rsvpSubmitUrl),
            isEmbed: {{ $isEmbed ? 'true' : 'false' }}
        };
    </script>
    <script defer src="{{ url('/mau-thiep/dau-ky-tram-nam/assets/script.js') }}?v={{ $jsVersion }}"></script>
@endpush