@php
    $assetBase = url('/mau-thiep/minimalism-do-dam/assets');
    $isEmbed = request()->boolean('embed');
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Minimalism Đỏ Đậm';

    // 1. Dữ liệu Cô dâu & Chú rể
    $groomName = $wedding->groom_name ?? 'Minh Tuấn';
    $brideName = $wedding->bride_name ?? 'Thu Hà';
    $extraData = $wedding->extra_data ?? [];

    $groomFather = $extraData['groom_father'] ?? 'Nguyễn Quốc Bình';
    $groomMother = $extraData['groom_mother'] ?? 'Nguyễn Hoài Mai';
    $groomParentsAddress = $extraData['groom_parents_address'] ?? '123 Đường Hoa, Phường 1, Quận 1, TP.HCM';

    $brideFather = $extraData['bride_father'] ?? 'Vũ Minh Hải';
    $brideMother = $extraData['bride_mother'] ?? 'Vũ Thanh Hiền';
    $brideParentsAddress = $extraData['bride_parents_address'] ?? '456 Đường Sen, Phường 2, Quận 3, TP.HCM';

    // 2. Ngày giờ và sự kiện cưới
    $weddingDate = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2026, 1, 3, 18, 0, 0);

    $weddingDateDigits = $weddingDate->format('d . m . Y');
    $weddingDay = $weddingDate->format('d');
    $weddingMonth = $weddingDate->format('m');
    $weddingYear = $weddingDate->format('Y');
    $weddingDayOfWeek = $weddingDate->locale('vi')->isoFormat('dddd');
    $weddingLunar = $extraData['wedding_lunar_date'] ?? 'Nhằm ngày 15 tháng 11 năm Ất Tỵ';

    // Tiệc cưới chính
    $partyTime = $extraData['party_time'] ?? '18:00';
    $partyVenue = $wedding->venue_name ?? ($extraData['party_venue'] ?? 'Trung Tâm Hội Nghị White Palace');
    $partyAddress = $wedding->venue_address ?? ($extraData['event_party_address'] ?? '194 Hoàng Văn Thụ, Phường 9, Quận Phú Nhuận, TP. Hồ Chí Minh');
    $partyMapUrl = 'https://www.google.com/maps?q=' . urlencode($partyVenue . ', ' . $partyAddress) . '&output=embed';
    $partyDirUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($partyVenue . ', ' . $partyAddress);

    // Câu chuyện tình yêu & trích dẫn
    $storyTitle = $extraData['story_title'] ?? 'Chuyện Tình Yêu';
    $coupleQuote = $extraData['couple_quote'] ?? 'Gặp gỡ là duyên, đồng hành là nợ, cùng nhau già đi là lời hứa trọn đời.';
    $storyContent = $extraData['story_content'] ?? 'Tình yêu không phải là tìm một người hoàn hảo, mà là học cách nhìn thấy những điều tuyệt vời nhất từ một người không hoàn hảo.';

    // Hàm resolve asset
    $resolveAsset = function (?string $path, string $fallback = '') use ($assetBase): string {
        $val = trim((string) $path);
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

    // 10 ảnh album cưới
    $defaultGallery = [
        'img_4.webp',
        'img_7.webp',
        'img_8.webp',
        'img_9.webp',
        'img_10.webp',
        'img_11.webp',
        'img_12.webp',
        'img_13.webp',
        'img_14.webp',
        'img_15.webp',
    ];
    $rawGallery = $extraData['gallery'] ?? [];
    $gallery = [];
    for ($i = 0; $i < 10; $i++) {
        $img = !empty($rawGallery[$i]) ? $rawGallery[$i] : ($defaultGallery[$i] ?? 'img_4.webp');
        $gallery[] = $resolveAsset($img, $defaultGallery[$i] ?? 'img_4.webp');
    }

    $heroPhotoUrl = $resolveAsset($wedding->cover_photo_url ?? ($extraData['hero_photo_url'] ?? null), 'img_4.webp');

    // Mừng cưới
    $groomBankName = $extraData['groom_bank_name'] ?? 'Vietcombank';
    $groomBankAccount = $extraData['groom_bank_account'] ?? '0123 456 789';
    $groomAccountName = $extraData['groom_account_name'] ?? 'NGUYEN MINH TUAN';
    $cleanGroomAcc = preg_replace('/\s+/', '', $groomBankAccount);
    $qrGroomUrl = $resolveAsset($extraData['qr_groom_image'] ?? null, 'https://img.vietqr.io/image/' . strtolower($groomBankName) . '-' . $cleanGroomAcc . '-compact2.png?amount=0&addInfo=' . urlencode('Mung cuoi ' . $groomName));

    $brideBankName = $extraData['bride_bank_name'] ?? 'Techcombank';
    $brideBankAccount = $extraData['bride_bank_account'] ?? '1903 5555 8888';
    $brideAccountName = $extraData['bride_account_name'] ?? 'TRAN THU HA';
    $cleanBrideAcc = preg_replace('/\s+/', '', $brideBankAccount);
    $qrBrideUrl = $resolveAsset($extraData['qr_bride_image'] ?? null, 'https://img.vietqr.io/image/' . strtolower($brideBankName) . '-' . $cleanBrideAcc . '-compact2.png?amount=0&addInfo=' . urlencode('Mung cuoi ' . $brideName));

    // Khách mời
    $guestName = isset($guest) && $guest ? $guest->name : request()->query('guest', request()->query('name', $invitation->guest_name ?? 'Quý Khách'));

    $pageTitle = $isPreview
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name')
        : 'Thiệp cưới: ' . $groomName . ' & ' . $brideName . ' | ' . config('app.name');

    $wishSubmitUrl = $wishSubmitUrl ?? ($isPreview ? route('templates.preview.wish', 'minimalism-do-dam') : route('invitation.wish.store', $invitation->slug ?? ''));
    $rsvpSubmitUrl = $rsvpSubmitUrl ?? '';
@endphp

@extends('client.layouts.invitation')

@section('title', $pageTitle)
@section('meta_description', 'Trân trọng kính mời ' . $guestName . ' tới dự lễ cưới của ' . $groomName . ' & ' . $brideName . '.')

@push('fonts')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=EB+Garamond:ital,wght@0,400;0,600;1,400&family=Lora:ital,wght@0,400;0,500;0,600;1,400&family=Ms+Madi&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=The+Nautigal:wght@400;700&family=Uchen&family=Viaoda+Libre&display=swap" rel="stylesheet">
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ route('templates.asset', ['slug' => 'minimalism-do-dam', 'path' => 'styles.css']) }}?v={{ time() }}">
@endpush

@section('content')
<div class="mayhen_invitation_ladi_wraper mayhen_invitation_mdd {{ $isEmbed ? 'is-embed' : '' }}" id="mayhenInvitationRoot"
     data-is-embed="{{ $isEmbed ? 'true' : 'false' }}"
     data-rsvp-endpoint="{{ $rsvpSubmitUrl }}"
     data-wish-endpoint="{{ $wishSubmitUrl }}">

    {{-- =========================================================================
         1. SECTION 1: MÀN MỞ ĐẦU (OPENING & ENVELOPE)
         ========================================================================= --}}
    <div class="mayhen_invitation_opening mayhen_invitation_mdd_opening {{ $isEmbed ? 'is-opened' : '' }}" 
         data-mayhen-section="opening" 
         id="invite-envelope"
         style="{{ $isEmbed ? 'display: none !important;' : '' }}"
         role="dialog" 
         aria-label="Màn mở phong bì thiệp cưới">
        
        {{-- Bụi sao / Trái tim bay lơ lửng --}}
        <div class="mayhen_invitation_mdd_particles" aria-hidden="true">
            <div class="mayhen_invitation_mdd_particle" style="left:12%;top:-30px;color:#a8323b;font-size:16px;--sway:25px;animation:mayhenAmbientFall 10s ease-in-out infinite;">❤</div>
            <div class="mayhen_invitation_mdd_particle" style="left:28%;top:-30px;color:#ece4d8;font-size:20px;--sway:-18px;animation:mayhenAmbientFall 9s ease-in-out 0.5s infinite;">❤</div>
            <div class="mayhen_invitation_mdd_particle" style="left:48%;top:-30px;color:#7a1f26;font-size:22px;--sway:20px;animation:mayhenAmbientFall 11s ease-in-out 1s infinite;">❤</div>
            <div class="mayhen_invitation_mdd_particle" style="left:65%;top:-30px;color:#c9a24a;font-size:18px;--sway:-22px;animation:mayhenAmbientFall 12s ease-in-out 1.5s infinite;">❤</div>
            <div class="mayhen_invitation_mdd_particle" style="left:82%;top:-30px;color:#a8323b;font-size:15px;--sway:28px;animation:mayhenAmbientFall 9s ease-in-out 2s infinite;">❤</div>
            <div class="mayhen_invitation_mdd_particle" style="left:92%;top:-30px;color:#c9a24a;font-size:19px;--sway:-25px;animation:mayhenAmbientFall 10s ease-in-out 0.8s infinite;">❤</div>
        </div>

        {{-- Khung phong bì chính --}}
        <div class="mayhen_invitation_envelope mayhen_invitation_mdd_envelope">
            {{-- Tem niêm phong sáp (Wax Seal) --}}
            <div class="mayhen_invitation_envelope_seal mayhen_invitation_mdd_seal">
                <svg viewBox="0 0 24 24" class="mayhen_invitation_mdd_seal_icon" aria-hidden="true">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"></path>
                </svg>
            </div>

            {{-- Thẻ thiệp bên trong phong bì --}}
            <div class="mayhen_invitation_mdd_card">
                <div class="mayhen_invitation_mdd_card_bg" aria-hidden="true">
                    <img src="{{ $assetBase }}/cover.webp" alt="" class="mayhen_invitation_mdd_card_flower mayhen_invitation_mdd_card_flower_top">
                    <img src="{{ $assetBase }}/cover.webp" alt="" class="mayhen_invitation_mdd_card_flower mayhen_invitation_mdd_card_flower_bot">
                </div>

                <div class="mayhen_invitation_mdd_card_content">
                    <h1 class="mayhen_invitation_mdd_couple_names">
                        <span class="mayhen_invitation_mdd_name" data-sync-target="groom_name">{{ $groomName }}</span>
                        <span class="mayhen_invitation_mdd_amp">&amp;</span>
                        <span class="mayhen_invitation_mdd_name" data-sync-target="bride_name">{{ $brideName }}</span>
                    </h1>

                    <div class="mayhen_invitation_mdd_divider" aria-hidden="true">
                        <span class="mayhen_invitation_mdd_line"></span>
                        <span class="mayhen_invitation_mdd_fleur">❦</span>
                        <span class="mayhen_invitation_mdd_line"></span>
                    </div>

                    <div class="mayhen_invitation_mdd_date">
                        <span data-sync-target="wedding_date_digits">{{ $weddingDay }} tháng {{ $weddingMonth }}, {{ $weddingYear }}</span>
                    </div>

                    <div class="mayhen_invitation_mdd_invite_text">
                        <span>Thân Mời: <strong>{{ $guestName }}</strong></span>
                    </div>

                    <button type="button" class="mayhen_invitation_open_btn mayhen_invitation_mdd_open_btn" id="invite-open-btn">
                        <span>Mở thiệp</span>
                        <div class="mayhen_invitation_mdd_btn_shine" aria-hidden="true"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         2. SECTION 2: TRANG BÌA CHÍNH (HERO COVER / SAVE THE DATE)
         ========================================================================= --}}
    <section class="mayhen_invitation_hero mayhen_invitation_mdd_hero" data-mayhen-section="hero,couple">
        <div class="mayhen_invitation_mdd_hero_pattern" aria-hidden="true">
            <img src="{{ $assetBase }}/img_2.webp" alt="" class="mayhen_invitation_mdd_bg_watermark">
        </div>

        <p class="mayhen_invitation_ladi_headline_ceremony_type mayhen_invitation_mdd_kicker" data-sync-target="ceremony_type">Save The Date</p>

        {{-- Khung ảnh Polaroid bồng bềnh --}}
        <div class="mayhen_invitation_hero_media mayhen_invitation_mdd_polaroid_wrapper">
            <div class="mayhen_invitation_mdd_polaroid_box">
                <img src="{{ $assetBase }}/img_3.webp" alt="" class="mayhen_invitation_mdd_frame_base" aria-hidden="true">
                
                {{-- Ảnh cặp đôi chính --}}
                <div class="mayhen_invitation_mdd_polaroid_photo">
                    <img src="{{ $heroPhotoUrl }}" alt="Ảnh cưới {{ $groomName }} & {{ $brideName }}" class="mayhen_invitation_ladi_image" data-sync-target="hero_photo_url">
                </div>

                {{-- Họa tiết hoa lá đỏ trôi bồng bềnh --}}
                <div class="mayhen_invitation_mdd_leaf_accent" aria-hidden="true">
                    <img src="{{ $assetBase }}/cover.webp" alt="" class="mayhen_invitation_mdd_leaf_img">
                </div>

                <img src="{{ $assetBase }}/img_5.webp" alt="" class="mayhen_invitation_mdd_frame_bottom" aria-hidden="true">
            </div>
        </div>

        {{-- Tên cô dâu chú rể thư pháp uốn lượn --}}
        <div class="mayhen_invitation_mdd_hero_names">
            <h1 class="mayhen_invitation_ladi_headline_name_groom mayhen_invitation_mdd_groom" data-sync-target="groom_name">{{ $groomName }}</h1>
            <span class="mayhen_invitation_hero_amp mayhen_invitation_mdd_amp_big">&amp;</span>
            <h1 class="mayhen_invitation_ladi_headline_name_bride mayhen_invitation_mdd_bride" data-sync-target="bride_name">{{ $brideName }}</h1>
        </div>
    </section>

    {{-- =========================================================================
         3. SECTION 3: LỜI NGỎ & CHUYỆN TÌNH YÊU (LOVE STORY & QUOTE)
         ========================================================================= --}}
    <section class="mayhen_invitation_story mayhen_invitation_quote mayhen_invitation_mdd_story" data-mayhen-section="couple">
        <div class="mayhen_invitation_mdd_card_panel">
            <div class="mayhen_invitation_mdd_panel_bg" aria-hidden="true">
                <img src="{{ $assetBase }}/img_6.webp" alt="" class="mayhen_invitation_mdd_texture">
            </div>
            
            <div class="mayhen_invitation_mdd_story_body">
                <h2 class="mayhen_invitation_ladi_headline_story mayhen_invitation_mdd_sec_title" data-sync-target="story_title">{{ $storyTitle }}</h2>
                <div class="mayhen_invitation_mdd_quote_symbol">“</div>
                <blockquote class="mayhen_invitation_quote_text mayhen_invitation_mdd_quote_text" data-sync-target="couple_quote">{{ $coupleQuote }}</blockquote>
                <p class="mayhen_invitation_story_content mayhen_invitation_mdd_story_desc" data-sync-target="story_content">{{ $storyContent }}</p>
                <div class="mayhen_invitation_monogram mayhen_invitation_mdd_monogram">✦ {{ mb_substr($groomName, 0, 1) }} & {{ mb_substr($brideName, 0, 1) }} ✦</div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         4. SECTION 4: SONG THÂN & ĐÔI UYÊN ƯƠNG (FAMILY & COUPLE)
         ========================================================================= --}}
    <section class="mayhen_invitation_parents mayhen_invitation_mdd_family" data-mayhen-section="groom_family,bride_family">
        {{-- Nhành hoa trang trí nổi bật góc thẻ --}}
        <div class="mayhen_invitation_mdd_accent_flower" aria-hidden="true">
            <img src="{{ $assetBase }}/cover.webp" alt="" class="mayhen_invitation_mdd_floating_flower">
        </div>

        <div class="mayhen_invitation_mdd_card_panel">
            <div class="mayhen_invitation_mdd_panel_bg" aria-hidden="true">
                <img src="{{ $assetBase }}/img_6.webp" alt="" class="mayhen_invitation_mdd_texture">
            </div>

            <div class="mayhen_invitation_mdd_family_content">
                <h2 class="mayhen_invitation_family_title mayhen_invitation_mdd_panel_heading">THÔNG TIN LỄ CƯỚI</h2>

                {{-- Khối 2 cột Hai bên gia đình --}}
                <div class="mayhen_invitation_mdd_family_grid">
                    {{-- Nhà Trai --}}
                    <div class="mayhen_invitation_family_groom_card mayhen_invitation_mdd_fam_col">
                        <span class="mayhen_invitation_family_groom_title mayhen_invitation_mdd_fam_label">NHÀ TRAI</span>
                        <div class="mayhen_invitation_mdd_parents_names">
                            <span class="mayhen_invitation_family_groom_father" data-sync-target="groom_father">{{ $groomFather }}</span>
                            <span class="mayhen_invitation_family_groom_mother" data-sync-target="groom_mother">{{ $groomMother }}</span>
                        </div>
                        <p class="mayhen_invitation_family_groom_address mayhen_invitation_mdd_fam_addr" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                    </div>

                    <div class="mayhen_invitation_mdd_fam_separator" aria-hidden="true"></div>

                    {{-- Nhà Gái --}}
                    <div class="mayhen_invitation_family_bride_card mayhen_invitation_mdd_fam_col">
                        <span class="mayhen_invitation_family_bride_title mayhen_invitation_mdd_fam_label">NHÀ GÁI</span>
                        <div class="mayhen_invitation_mdd_parents_names">
                            <span class="mayhen_invitation_family_bride_father" data-sync-target="bride_father">{{ $brideFather }}</span>
                            <span class="mayhen_invitation_family_bride_mother" data-sync-target="bride_mother">{{ $brideMother }}</span>
                        </div>
                        <p class="mayhen_invitation_family_bride_address mayhen_invitation_mdd_fam_addr" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                    </div>
                </div>

                {{-- Thông báo hôn lễ trang trọng --}}
                <div class="mayhen_invitation_mdd_notice_box">
                    <p class="mayhen_invitation_family_notice">TRÂN TRỌNG BÁO TIN<br>LỄ THÀNH HÔN CỦA CON CHÚNG TÔI</p>
                    
                    <div class="mayhen_invitation_mdd_couple_banner">
                        <h3 class="mayhen_invitation_groom_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                        <span class="mayhen_invitation_mdd_role_label">Trưởng Nam</span>
                        <div class="mayhen_invitation_mdd_center_amp">&amp;</div>
                        <h3 class="mayhen_invitation_bride_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                        <span class="mayhen_invitation_mdd_role_label">Út Nữ</span>
                    </div>

                    <div class="mayhen_invitation_mdd_ceremony_details">
                        <p class="mayhen_invitation_mdd_ceremony_title">LỄ THÀNH HÔN ĐƯỢC CỬ HÀNH TẠI TƯ GIA</p>
                        <div class="mayhen_invitation_mdd_ceremony_meta">
                            <span>VÀO LÚC 09:00</span>
                            <span>{{ mb_strtoupper($weddingDayOfWeek) }}</span>
                        </div>
                        <div class="mayhen_invitation_mdd_ceremony_date_lockup">
                            <span class="mayhen_invitation_mdd_date_large">{{ $weddingDay }}</span>
                            <div class="mayhen_invitation_mdd_date_vdivider"></div>
                            <div class="mayhen_invitation_mdd_date_month_year">
                                <span>THÁNG {{ $weddingMonth }}</span>
                                <span>{{ $weddingYear }}</span>
                            </div>
                        </div>
                        <div class="mayhen_invitation_mdd_lunar_text" data-sync-target="wedding_lunar_date">({{ $weddingLunar }})</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         5. SECTION 5: ĐẾM NGƯỢC & LỊCH CƯỚI (COUNTDOWN & CALENDAR)
         ========================================================================= --}}
    <section class="mayhen_invitation_countdown_calendar mayhen_invitation_mdd_calendar_section" data-mayhen-section="couple">
        <div class="mayhen_invitation_mdd_card_panel">
            <div class="mayhen_invitation_mdd_panel_bg" aria-hidden="true">
                <img src="{{ $assetBase }}/img_6.webp" alt="" class="mayhen_invitation_mdd_texture">
            </div>

            <div class="mayhen_invitation_mdd_panel_body">
                <h2 class="mayhen_invitation_countdown_title mayhen_invitation_mdd_panel_heading">THỜI GIAN & ĐẾM NGƯỢC</h2>

                {{-- Đồng hồ đếm ngược --}}
                <div class="mayhen_invitation_countdown mayhen_invitation_mdd_countdown" data-countdown="{{ $weddingDate->toIso8601String() }}">
                    <div class="mayhen_invitation_cd_item">
                        <span class="mayhen_invitation_cd_num cd-days">00</span>
                        <span class="mayhen_invitation_cd_label">Ngày</span>
                    </div>
                    <div class="mayhen_invitation_cd_colon">:</div>
                    <div class="mayhen_invitation_cd_item">
                        <span class="mayhen_invitation_cd_num cd-hours">00</span>
                        <span class="mayhen_invitation_cd_label">Giờ</span>
                    </div>
                    <div class="mayhen_invitation_cd_colon">:</div>
                    <div class="mayhen_invitation_cd_item">
                        <span class="mayhen_invitation_cd_num cd-mins">00</span>
                        <span class="mayhen_invitation_cd_label">Phút</span>
                    </div>
                    <div class="mayhen_invitation_cd_colon">:</div>
                    <div class="mayhen_invitation_cd_item">
                        <span class="mayhen_invitation_cd_num cd-secs">00</span>
                        <span class="mayhen_invitation_cd_label">Giây</span>
                    </div>
                </div>

                {{-- Tờ lịch tháng màu kem viền chỉ đỏ --}}
                <div class="mayhen_invitation_calendar mayhen_invitation_mdd_calendar_card">
                    <div class="mayhen_invitation_calendar_month_year mayhen_invitation_mdd_cal_header">
                        Tháng {{ $weddingMonth }} / {{ $weddingYear }}
                    </div>
                    <div class="mayhen_invitation_mdd_cal_days_header">
                        <div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div>T7</div><div>CN</div>
                    </div>
                    <div class="mayhen_invitation_mdd_cal_grid" id="mayhenMddCalendarGrid">
                        {{-- JS tự động sinh hoặc render tĩnh tháng 1/2026 --}}
                        <div></div><div></div><div></div><div>1</div><div>2</div>
                        <div class="mayhen_invitation_calendar_selected_day mayhen_invitation_mdd_selected_day">
                            <svg viewBox="0 0 24 22" class="mayhen_invitation_mdd_heart_marker" fill="#511419">
                                <path d="M12 21C12 21 1.5 13.5 1.5 7.5C1.5 4.46 3.96 2 7 2C8.76 2 10.35 2.81 11.4 4.09L12 4.8L12.6 4.09C13.65 2.81 15.24 2 17 2C20.04 2 22.5 4.46 22.5 7.5C22.5 13.5 12 21 12 21Z"></path>
                            </svg>
                            <span>{{ (int) $weddingDay }}</span>
                        </div>
                        <div>4</div><div>5</div><div>6</div><div>7</div><div>8</div><div>9</div><div>10</div>
                        <div>11</div><div>12</div><div>13</div><div>14</div><div>15</div><div>16</div><div>17</div>
                        <div>18</div><div>19</div><div>20</div><div>21</div><div>22</div><div>23</div><div>24</div>
                        <div>25</div><div>26</div><div>27</div><div>28</div><div>29</div><div>30</div><div>31</div>
                    </div>
                </div>

                <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text={{ urlencode('Lễ Cưới ' . $groomName . ' & ' . $brideName) }}&dates={{ $weddingDate->format('Ymd\THis\Z') }}/{{ $weddingDate->copy()->addHours(4)->format('Ymd\THis\Z') }}&details={{ urlencode('Trân trọng kính mời tới dự tiệc cưới của ' . $groomName . ' & ' . $brideName) }}&location={{ urlencode($partyVenue . ', ' . $partyAddress) }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="mayhen_invitation_add_calendar_btn mayhen_invitation_mdd_cal_btn">
                    <span>Thêm vào lịch</span>
                </a>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         6. SECTION 6: SỰ KIỆN HÔN LỄ & BẢN ĐỒ (EVENTS & MAPS)
         ========================================================================= --}}
    <section class="mayhen_invitation_events mayhen_invitation_maps mayhen_invitation_mdd_events" data-mayhen-section="wedding_event">
        <div class="mayhen_invitation_mdd_card_panel">
            <div class="mayhen_invitation_mdd_panel_bg" aria-hidden="true">
                <img src="{{ $assetBase }}/img_6.webp" alt="" class="mayhen_invitation_mdd_texture">
            </div>

            <div class="mayhen_invitation_mdd_panel_body">
                <h2 class="mayhen_invitation_events_title mayhen_invitation_mdd_panel_heading">THÔNG TIN TIỆC CƯỚI</h2>

                <div class="mayhen_invitation_event_card_party mayhen_invitation_mdd_party_details">
                    <p class="mayhen_invitation_mdd_party_intro">Tiệc cưới sẽ diễn ra vào lúc:</p>
                    <div class="mayhen_invitation_party_date mayhen_invitation_mdd_party_date_row">
                        <span>{{ mb_strtoupper($weddingDayOfWeek) }}</span>
                        <span data-sync-target="party_time">{{ $partyTime }}</span>
                    </div>

                    <div class="mayhen_invitation_mdd_ceremony_date_lockup">
                        <span class="mayhen_invitation_mdd_date_large">{{ $weddingDay }}</span>
                        <div class="mayhen_invitation_mdd_date_vdivider"></div>
                        <div class="mayhen_invitation_mdd_date_month_year">
                            <span>THÁNG {{ $weddingMonth }}</span>
                            <span>{{ $weddingYear }}</span>
                        </div>
                    </div>

                    <div class="mayhen_invitation_mdd_lunar_text">({{ $weddingLunar }})</div>

                    {{-- 2 mốc đón khách & khai tiệc --}}
                    <div class="mayhen_invitation_mdd_party_milestones">
                        <div class="mayhen_invitation_mdd_ms_item">
                            <span class="mayhen_invitation_mdd_ms_label">ĐÓN KHÁCH</span>
                            <span class="mayhen_invitation_mdd_ms_time">17:30</span>
                        </div>
                        <div class="mayhen_invitation_mdd_ms_item">
                            <span class="mayhen_invitation_mdd_ms_label">KHAI TIỆC</span>
                            <span class="mayhen_invitation_mdd_ms_time" data-sync-target="party_time">{{ $partyTime }}</span>
                        </div>
                    </div>

                    {{-- Địa điểm & Bản đồ --}}
                    <div class="mayhen_invitation_map_box mayhen_invitation_mdd_venue_block">
                        <h3 class="mayhen_invitation_party_venue mayhen_invitation_mdd_venue_title" data-sync-target="party_venue">{{ $partyVenue }}</h3>
                        <p class="mayhen_invitation_party_address mayhen_invitation_mdd_venue_address" data-sync-target="event_party_address">{{ $partyAddress }}</p>

                        <div class="mayhen_invitation_mdd_map_container">
                            <iframe class="mayhen_invitation_map_iframe mayhen_invitation_mdd_map_iframe" 
                                    src="{{ $partyMapUrl }}" 
                                    loading="lazy" 
                                    allowfullscreen="" 
                                    referrerpolicy="no-referrer-when-downgrade" 
                                    title="Bản đồ địa điểm tiệc cưới"></iframe>
                        </div>

                        <a href="{{ $partyDirUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_mdd_directions_btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mayhen_invitation_mdd_icon_gps"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>
                            <span>Chỉ đường</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         7. SECTION 7: LỊCH TRÌNH HÔN LỄ (TIMELINE)
         ========================================================================= --}}
    <section class="mayhen_invitation_timeline mayhen_invitation_mdd_timeline" data-mayhen-section="timeline">
        <div class="mayhen_invitation_mdd_card_panel">
            <div class="mayhen_invitation_mdd_panel_bg" aria-hidden="true">
                <img src="{{ $assetBase }}/img_6.webp" alt="" class="mayhen_invitation_mdd_texture">
            </div>

            <div class="mayhen_invitation_mdd_panel_body">
                <h2 class="mayhen_invitation_timeline_title mayhen_invitation_mdd_panel_heading">LỊCH TRÌNH BUỔI TIỆC</h2>

                <div class="mayhen_invitation_timeline_list mayhen_invitation_mdd_timeline_list">
                    {{-- 17:30 --}}
                    <div class="mayhen_invitation_timeline_item mayhen_invitation_mdd_tl_item">
                        <span class="mayhen_invitation_timeline_time mayhen_invitation_mdd_tl_time" data-sync-target="timeline.0.time">17:30</span>
                        <div class="mayhen_invitation_timeline_dot mayhen_invitation_mdd_tl_node">
                            <span class="mayhen_invitation_mdd_tl_dot"></span>
                        </div>
                        <div class="mayhen_invitation_timeline_text mayhen_invitation_mdd_tl_content">
                            <img src="{{ $assetBase }}/img_18.webp" alt="" class="mayhen_invitation_mdd_tl_icon" aria-hidden="true">
                            <span data-sync-target="timeline.0.title">Đón khách</span>
                        </div>
                    </div>

                    {{-- 18:00 --}}
                    <div class="mayhen_invitation_timeline_item mayhen_invitation_mdd_tl_item">
                        <span class="mayhen_invitation_timeline_time mayhen_invitation_mdd_tl_time" data-sync-target="timeline.1.time">18:00</span>
                        <div class="mayhen_invitation_timeline_dot mayhen_invitation_mdd_tl_node">
                            <span class="mayhen_invitation_mdd_tl_dot"></span>
                        </div>
                        <div class="mayhen_invitation_timeline_text mayhen_invitation_mdd_tl_content">
                            <img src="{{ $assetBase }}/img_18.webp" alt="" class="mayhen_invitation_mdd_tl_icon" aria-hidden="true">
                            <span data-sync-target="timeline.1.title">Khai tiệc</span>
                        </div>
                    </div>

                    {{-- 18:30 --}}
                    <div class="mayhen_invitation_timeline_item mayhen_invitation_mdd_tl_item">
                        <span class="mayhen_invitation_timeline_time mayhen_invitation_mdd_tl_time" data-sync-target="timeline.2.time">18:30</span>
                        <div class="mayhen_invitation_timeline_dot mayhen_invitation_mdd_tl_node">
                            <span class="mayhen_invitation_mdd_tl_dot"></span>
                        </div>
                        <div class="mayhen_invitation_timeline_text mayhen_invitation_mdd_tl_content">
                            <img src="{{ $assetBase }}/img_18.webp" alt="" class="mayhen_invitation_mdd_tl_icon" aria-hidden="true">
                            <span data-sync-target="timeline.2.title">Nghi thức cưới</span>
                        </div>
                    </div>

                    {{-- 19:00 --}}
                    <div class="mayhen_invitation_timeline_item mayhen_invitation_mdd_tl_item">
                        <span class="mayhen_invitation_timeline_time mayhen_invitation_mdd_tl_time" data-sync-target="timeline.3.time">19:00</span>
                        <div class="mayhen_invitation_timeline_dot mayhen_invitation_mdd_tl_node">
                            <span class="mayhen_invitation_mdd_tl_dot"></span>
                        </div>
                        <div class="mayhen_invitation_timeline_text mayhen_invitation_mdd_tl_content">
                            <img src="{{ $assetBase }}/img_18.webp" alt="" class="mayhen_invitation_mdd_tl_icon" aria-hidden="true">
                            <span data-sync-target="timeline.3.title">Cắt bánh & nâng ly</span>
                        </div>
                    </div>

                    {{-- 20:30 --}}
                    <div class="mayhen_invitation_timeline_item mayhen_invitation_mdd_tl_item">
                        <span class="mayhen_invitation_timeline_time mayhen_invitation_mdd_tl_time" data-sync-target="timeline.4.time">20:30</span>
                        <div class="mayhen_invitation_timeline_dot mayhen_invitation_mdd_tl_node">
                            <span class="mayhen_invitation_mdd_tl_dot"></span>
                        </div>
                        <div class="mayhen_invitation_timeline_text mayhen_invitation_mdd_tl_content">
                            <span data-sync-target="timeline.4.title">Kết thúc tiệc & Tiễn khách</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         8. SECTION 8: QUY ĐỊNH TRANG PHỤC (DRESSCODE)
         ========================================================================= --}}
    <section class="mayhen_invitation_dresscode mayhen_invitation_mdd_dresscode" data-mayhen-section="dresscode">
        <h2 class="mayhen_invitation_dresscode_title mayhen_invitation_mdd_dresscode_title">DRESS CODE</h2>
        <p class="mayhen_invitation_dresscode_note mayhen_invitation_mdd_dresscode_subtitle" data-sync-target="dresscode_note">Trang phục dự tiệc thanh lịch</p>

        <div class="mayhen_invitation_dresscode_swatches mayhen_invitation_mdd_swatches">
            <div class="mayhen_invitation_dresscode_item">
                <div class="mayhen_invitation_dresscode_circle" style="background-color:#511419;" title="Đỏ Rượu Vang"></div>
            </div>
            <div class="mayhen_invitation_dresscode_item">
                <div class="mayhen_invitation_dresscode_circle" style="background-color:#8C3A3F;" title="Hồng Rượu"></div>
            </div>
            <div class="mayhen_invitation_dresscode_item">
                <div class="mayhen_invitation_dresscode_circle" style="background-color:#C9A24A;" title="Vàng Hoàng Gia"></div>
            </div>
            <div class="mayhen_invitation_dresscode_item">
                <div class="mayhen_invitation_dresscode_circle" style="background-color:#ECE4D8; border: 1px solid rgba(81,20,25,0.2);" title="Trắng Kem"></div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         9. SECTION 9: KHOẢNH KHẮC HẠNH PHÚC (ALBUM 10 PHOTOS)
         ========================================================================= --}}
    <section class="mayhen_invitation_gallery mayhen_invitation_mdd_gallery" data-mayhen-section="album">
        <h2 class="mayhen_invitation_gallery_title mayhen_invitation_mdd_sec_title">ALBUM ẢNH CƯỚI</h2>

        <div class="mayhen_invitation_gallery_wrap mayhen_invitation_mdd_coverflow_wrap">
            {{-- Mũi tên điều hướng --}}
            <button type="button" class="mayhen_invitation_gallery_prev_btn mayhen_invitation_mdd_nav_arrow mayhen_invitation_mdd_prev" aria-label="Xem ảnh trước">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"></path></svg>
            </button>
            <button type="button" class="mayhen_invitation_gallery_next_btn mayhen_invitation_mdd_nav_arrow mayhen_invitation_mdd_next" aria-label="Xem ảnh kế tiếp">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path></svg>
            </button>

            {{-- Sân khấu 3D Coverflow --}}
            <div class="mayhen_invitation_mdd_3d_stage" style="perspective:1000px;" id="mayhenMddGalleryStage">
                @foreach($gallery as $idx => $photoUrl)
                    <div class="mayhen_invitation_gallery_item mayhen_invitation_mdd_card_slide" data-index="{{ $idx }}" style="transition-duration:900ms;">
                        <img src="{{ $photoUrl }}" alt="Ảnh cưới {{ $idx + 1 }}" class="mayhen_invitation_gallery_img" data-sync-target="gallery.{{ $idx }}">
                    </div>
                @endforeach
            </div>

            {{-- Dải chấm tròn phân trang (10 dots) --}}
            <div class="mayhen_invitation_gallery_dots mayhen_invitation_mdd_dots">
                @for($d = 0; $d < 10; $d++)
                    <button type="button" class="mayhen_invitation_mdd_dot {{ $d === 0 ? 'is-active' : '' }}" aria-label="Đến ảnh {{ $d + 1 }}" data-dot-index="{{ $d }}"></button>
                @endfor
            </div>
        </div>

        {{-- Dialog Lightbox phóng to ảnh --}}
        <dialog class="mayhen_invitation_lightbox mayhen_invitation_mdd_lightbox" id="mayhenMddLightbox">
            <div class="mayhen_invitation_mdd_lightbox_box">
                <button type="button" class="mayhen_invitation_mdd_lightbox_close" id="mayhenMddLightboxClose" aria-label="Đóng phóng to">✕</button>
                <div class="mayhen_invitation_mdd_lightbox_counter" id="mayhenMddLightboxCounter">1 / 10</div>
                <div class="mayhen_invitation_mdd_lightbox_main">
                    <button type="button" class="mayhen_invitation_mdd_lightbox_btn mayhen_invitation_mdd_lightbox_prev" id="mayhenMddLightboxPrev" aria-label="Ảnh trước">‹</button>
                    <img src="{{ $gallery[0] }}" alt="Ảnh cưới phóng to" class="mayhen_invitation_mdd_lightbox_img" id="mayhenMddLightboxImg">
                    <button type="button" class="mayhen_invitation_mdd_lightbox_btn mayhen_invitation_mdd_lightbox_next" id="mayhenMddLightboxNext" aria-label="Ảnh kế tiếp">›</button>
                </div>
            </div>
        </dialog>
    </section>

    {{-- =========================================================================
         10. SECTION 10: MỪNG CƯỚI, KÝ TÊN, RSVP, LƯU BÚT & CẢM ƠN
         ========================================================================= --}}
    <section class="mayhen_invitation_interactive_ending mayhen_invitation_mdd_interactive" data-mayhen-section="gift,rsvp,wishes">
        
        {{-- 10.1 HỘP MỪNG CƯỚI (VIETQR) --}}
        <div class="mayhen_invitation_gift mayhen_invitation_mdd_gift_block">
            <div class="mayhen_invitation_mdd_card_panel">
                <div class="mayhen_invitation_mdd_panel_bg" aria-hidden="true">
                    <img src="{{ $assetBase }}/img_6.webp" alt="" class="mayhen_invitation_mdd_texture">
                </div>

                <div class="mayhen_invitation_mdd_panel_body">
                    <h2 class="mayhen_invitation_gift_title mayhen_invitation_mdd_panel_heading">HỘP MỪNG CƯỚI</h2>
                    <p class="mayhen_invitation_gift_desc mayhen_invitation_mdd_gift_intro">Món quà ý nghĩa nhất là sự hiện diện của Quý khách. Nếu muốn gửi tặng món quà chúc phúc, Quý khách có thể gửi tới tài khoản bên dưới:</p>

                    <div class="mayhen_invitation_mdd_qr_grid">
                        {{-- Chú Rể --}}
                        <div class="mayhen_invitation_gift_card_groom mayhen_invitation_mdd_qr_card">
                            <span class="mayhen_invitation_gift_role_groom mayhen_invitation_mdd_qr_tag">CHÚ RỂ</span>
                            <div class="mayhen_invitation_mdd_qr_frame">
                                <img src="{{ $qrGroomUrl }}" alt="Mã QR Chú Rể" class="mayhen_invitation_gift_qr_groom" data-sync-target="qr_groom_image">
                            </div>
                            <span class="mayhen_invitation_gift_bank_name_groom mayhen_invitation_mdd_bank_name" data-sync-target="groom_bank_name">{{ $groomBankName }}</span>
                            <b class="mayhen_invitation_gift_account_number_groom mayhen_invitation_mdd_acc_num" data-sync-target="groom_bank_account">{{ $groomBankAccount }}</b>
                            <span class="mayhen_invitation_gift_account_holder_groom mayhen_invitation_mdd_acc_holder" data-sync-target="groom_account_name">{{ $groomAccountName }}</span>
                            <button type="button" class="mayhen_invitation_copy_btn mayhen_invitation_mdd_copy_btn" data-copy="{{ $cleanGroomAcc }}">
                                <span>Sao chép số tài khoản</span>
                            </button>
                        </div>

                        {{-- Cô Dâu --}}
                        <div class="mayhen_invitation_gift_card_bride mayhen_invitation_mdd_qr_card">
                            <span class="mayhen_invitation_gift_role_bride mayhen_invitation_mdd_qr_tag">CÔ DÂU</span>
                            <div class="mayhen_invitation_mdd_qr_frame">
                                <img src="{{ $qrBrideUrl }}" alt="Mã QR Cô Dâu" class="mayhen_invitation_gift_qr_bride" data-sync-target="qr_bride_image">
                            </div>
                            <span class="mayhen_invitation_gift_bank_name_bride mayhen_invitation_mdd_bank_name" data-sync-target="bride_bank_name">{{ $brideBankName }}</span>
                            <b class="mayhen_invitation_gift_account_number_bride mayhen_invitation_mdd_acc_num" data-sync-target="bride_bank_account">{{ $brideBankAccount }}</b>
                            <span class="mayhen_invitation_gift_account_holder_bride mayhen_invitation_mdd_acc_holder" data-sync-target="bride_account_name">{{ $brideAccountName }}</span>
                            <button type="button" class="mayhen_invitation_copy_btn mayhen_invitation_mdd_copy_btn" data-copy="{{ $cleanBrideAcc }}">
                                <span>Sao chép số tài khoản</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 10.2 SỔ KÝ TÊN ĐIỆN TỬ (CẤM ẢNH NGƯỜI) --}}
        <div class="mayhen_invitation_signature mayhen_invitation_mdd_signature_block">
            <div class="mayhen_invitation_mdd_card_panel">
                <div class="mayhen_invitation_mdd_panel_bg" aria-hidden="true">
                    <img src="{{ $assetBase }}/img_6.webp" alt="" class="mayhen_invitation_mdd_texture">
                </div>

                <div class="mayhen_invitation_mdd_panel_body">
                    <h2 class="mayhen_invitation_mdd_panel_heading">SỔ KÝ TÊN ĐIỆN TỬ</h2>
                    <p class="mayhen_invitation_mdd_sig_desc">Kính mời Quý khách lưu lại nét bút kỷ niệm chúc mừng ngày vui của chúng tôi:</p>

                    <div class="mayhen_invitation_signature_bg mayhen_invitation_mdd_canvas_wrap">
                        <canvas class="mayhen_invitation_signature_canvas" id="mayhenSignatureCanvas" width="340" height="180"></canvas>
                    </div>

                    <div class="mayhen_invitation_mdd_canvas_actions">
                        <button type="button" class="mayhen_invitation_mdd_clear_sig_btn" id="mayhenClearSignatureBtn">Xóa chữ ký</button>
                        <button type="button" class="mayhen_invitation_signature_save_btn mayhen_invitation_mdd_save_sig_btn" id="mayhenSaveSignatureBtn">Lưu chữ ký</button>
                    </div>
                    <div class="mayhen_invitation_mdd_sig_saved_msg" id="mayhenSignatureSuccessMsg" style="display:none;">✦ Chữ ký của Quý khách đã được lưu giữ trân trọng! ✦</div>
                </div>
            </div>
        </div>

        {{-- 10.3 XÁC NHẬN THAM DỰ (RSVP) (CẤM ẢNH NGƯỜI) --}}
        <div class="mayhen_invitation_rsvp mayhen_invitation_mdd_rsvp_block">
            <div class="mayhen_invitation_mdd_card_panel">
                <div class="mayhen_invitation_mdd_panel_bg" aria-hidden="true">
                    <img src="{{ $assetBase }}/img_6.webp" alt="" class="mayhen_invitation_mdd_texture">
                </div>

                <div class="mayhen_invitation_mdd_panel_body">
                    <h2 class="mayhen_invitation_mdd_panel_heading">XÁC NHẬN THAM DỰ</h2>
                    <p class="mayhen_invitation_mdd_rsvp_desc">Để gia đình chuẩn bị chu đáo nhất, xin Quý khách vui lòng xác nhận trước ngày 28/12/2025:</p>

                    <form class="mayhen_invitation_rsvp_form mayhen_invitation_mdd_form" id="mayhenRsvpForm">
                        @csrf
                        <div class="mayhen_invitation_mdd_form_group">
                            <label for="mdd_rsvp_name">Họ và tên của Quý khách <span class="req">*</span></label>
                            <input type="text" id="mdd_rsvp_name" name="name" value="{{ $guestName }}" class="mayhen_invitation_rsvp_name_input" required placeholder="Họ và tên...">
                        </div>

                        <div class="mayhen_invitation_mdd_form_group">
                            <label>Quý khách là khách của:</label>
                            <div class="mayhen_invitation_rsvp_guest_of mayhen_invitation_mdd_radio_group">
                                <label><input type="radio" name="guest_of" value="groom" checked> Nhà Trai</label>
                                <label><input type="radio" name="guest_of" value="bride"> Nhà Gái</label>
                            </div>
                        </div>

                        <div class="mayhen_invitation_mdd_form_group">
                            <label>Khả năng tham dự:</label>
                            <div class="mayhen_invitation_rsvp_attendance mayhen_invitation_mdd_radio_group">
                                <label><input type="radio" name="attendance" value="yes" checked> Chắc chắn tham dự</label>
                                <label><input type="radio" name="attendance" value="no"> Rất tiếc không thể đến</label>
                            </div>
                        </div>

                        <div class="mayhen_invitation_mdd_form_group">
                            <label for="mdd_rsvp_count">Số lượng người tham dự cùng:</label>
                            <div class="mayhen_invitation_rsvp_count mayhen_invitation_mdd_count_box">
                                <select id="mdd_rsvp_count" name="guest_count">
                                    <option value="1">1 người (Chỉ mình tôi)</option>
                                    <option value="2">2 người (Đi cùng người thương)</option>
                                    <option value="3">3 người (Gia đình)</option>
                                    <option value="4">4+ người</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="mayhen_invitation_rsvp_submit_btn mayhen_invitation_mdd_submit_btn">
                            <span>GỬI XÁC NHẬN</span>
                        </button>
                        <div class="mayhen_invitation_mdd_alert_success" id="mayhenRsvpSuccess" style="display:none;">Cảm ơn Quý khách đã gửi phản hồi!</div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 10.4 SỔ LƯU BÚT CHÚC PHÚC (WISHES) --}}
        <div class="mayhen_invitation_wishes mayhen_invitation_mdd_wishes_block">
            <div class="mayhen_invitation_mdd_card_panel">
                <div class="mayhen_invitation_mdd_panel_bg" aria-hidden="true">
                    <img src="{{ $assetBase }}/img_6.webp" alt="" class="mayhen_invitation_mdd_texture">
                </div>

                <div class="mayhen_invitation_mdd_panel_body">
                    <h2 class="mayhen_invitation_mdd_panel_heading">SỔ LƯU BÚT CHÚC PHÚC</h2>
                    <p class="mayhen_invitation_mdd_wishes_desc">Gửi trao những lời chúc tốt đẹp nhất tới cặp đôi:</p>

                    <form class="mayhen_invitation_wish_form mayhen_invitation_mdd_form" id="mayhenWishForm">
                        @csrf
                        <div class="mayhen_invitation_mdd_form_group">
                            <input type="text" name="name" class="mayhen_invitation_wish_name_input" value="{{ $guestName }}" required placeholder="Tên của bạn...">
                        </div>
                        <div class="mayhen_invitation_mdd_form_group">
                            <textarea name="message" class="mayhen_invitation_wish_textarea" required rows="3" placeholder="Nhập lời chúc hạnh phúc..."></textarea>
                        </div>
                        <button type="submit" class="mayhen_invitation_wish_submit_btn mayhen_invitation_mdd_submit_btn">
                            <span>GỬI LỜI CHÚC</span>
                        </button>
                    </form>

                    <div class="mayhen_invitation_wish_list mayhen_invitation_mdd_wish_list" id="mayhenWishList">
                        <div class="mayhen_invitation_wish_item mayhen_invitation_mdd_wish_item">
                            <strong>Trần Quốc Huy:</strong>
                            <p>Chúc hai bạn trăm năm hạnh phúc, đầu bạc răng long, mãi mãi mặn nồng như thuở ban đầu!</p>
                        </div>
                        <div class="mayhen_invitation_wish_item mayhen_invitation_mdd_wish_item">
                            <strong>Lê Minh Thảo:</strong>
                            <p>Mừng ngày vui của hai bạn! Chúc cuộc sống hôn nhân luôn tràn ngập tiếng cười và may mắn!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 10.5 LỜI CẢM ƠN KẾT THIỆP (ENDING) --}}
        <div class="mayhen_invitation_ending mayhen_invitation_mdd_ending">
            <h2 class="mayhen_invitation_ending_title mayhen_invitation_mdd_ending_title">THANK YOU</h2>
            <p class="mayhen_invitation_ending_text mayhen_invitation_mdd_ending_desc" data-sync-target="ending_thank_you">
                Sự hiện diện của Quý khách là niềm vinh hạnh to lớn cho gia đình chúng tôi!
            </p>
            <div class="mayhen_invitation_ending_couple_name mayhen_invitation_mdd_ending_names">
                <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ route('templates.asset', ['slug' => 'minimalism-do-dam', 'path' => 'script.js']) }}?v={{ time() }}"></script>
@endpush
