@php
    $assetBase = url('/mau-thiep/giua-muon-ngan-vi-sao/assets');
    $isEmbed = request()->boolean('embed');
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Giữa Muôn Ngàn Vì Sao';

    // 1. Dữ liệu Cô dâu & Chú rể
    $groomName = $wedding->groom_name ?? 'Minh Tuấn';
    $brideName = $wedding->bride_name ?? 'Thu Hà';
    $extraData = $wedding->extra_data ?? [];

    $groomFather = $extraData['groom_father'] ?? ($wedding->groom_parents ?? 'Ông Nguyễn Văn Hùng');
    $groomMother = $extraData['groom_mother'] ?? 'Bà Lê Thị Thu';
    $groomParentsAddress = $extraData['groom_parents_address'] ?? 'Đống Đa, Hà Nội';

    $brideFather = $extraData['bride_father'] ?? ($wedding->bride_parents ?? 'Ông Trần Quốc Bảo');
    $brideMother = $extraData['bride_mother'] ?? 'Bà Phạm Thị Mai';
    $brideParentsAddress = $extraData['bride_parents_address'] ?? 'Ba Đình, Hà Nội';

    // 2. Ngày giờ và sự kiện cưới
    $weddingDate = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2026, 12, 20, 18, 0, 0);

    $weddingDateDigits = $weddingDate->format('d . m . Y');
    $weddingDay = $weddingDate->format('d');
    $weddingMonth = $weddingDate->format('m');
    $weddingYear = $weddingDate->format('Y');
    $weddingDayOfWeek = $weddingDate->locale('vi')->isoFormat('dddd');
    $weddingLunar = $extraData['wedding_lunar_date'] ?? 'Nhằm ngày 12 tháng 11 năm Bính Ngọ';

    // Tiệc cưới chính
    $partyTime = $extraData['party_time'] ?? '18:00';
    $partyVenue = $extraData['party_venue'] ?? 'Trung tâm Tiệc cưới Ngân Hà';
    $partyAddress = $extraData['event_party_address'] ?? ($extraData['party_address'] ?? 'Số 88 Trần Duy Hưng, Cầu Giấy, Hà Nội');
    $partyMapLink = 'https://maps.google.com/maps?q=' . urlencode($partyVenue . ', ' . $partyAddress) . '&t=&z=16&ie=UTF8&iwloc=&output=embed';

    // Lễ Vu Quy (Nhà Gái)
    $ceremonyTime = $extraData['ceremony_time'] ?? '08:00';
    $ceremonyAddress = $extraData['ceremony_address'] ?? 'Tại tư gia nhà gái - Ba Đình, Hà Nội';

    // Lễ Thành Hôn (Nhà Trai)
    $ceremonyGroomTime = $extraData['ceremony_groom_time'] ?? '10:00';
    $ceremonyGroomAddress = $extraData['ceremony_groom_address'] ?? 'Tại tư gia nhà trai - Đống Đa, Hà Nội';

    // Khách mời
    $guestName = $invitation->guest_name ?? 'Quý Khách';

    // 3. Hàm chuẩn hóa asset ảnh
    $resolveAsset = function (?string $path, string $fallback = '') use ($assetBase): string {
        $val = trim((string) $path);
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
    $heroPhotoUrl = $resolveAsset($extraData['hero_photo_url'] ?? null, '7da1e3a6d59c1a98.webp');
    $groomPhotoUrl = $resolveAsset($extraData['groom_photo_url'] ?? null, 'b058b38c28460637.webp');
    $bridePhotoUrl = $resolveAsset($extraData['bride_photo_url'] ?? null, '307b8d7ea14424b9.webp');
    $saveDatePhotoUrl = $resolveAsset($extraData['save_date_photo_url'] ?? null, 'c3655bd60267c1b2.webp');
    $kyPhotoUrl = $resolveAsset($extraData['ky_photo_url'] ?? null, '40f8fb60afcb59af.webp');
    $xnPhotoUrl = $resolveAsset($extraData['xn_photo_url'] ?? null, '686ca1bfd6d65f13.webp');
    $ketPhotoUrl = $resolveAsset($extraData['ket_photo_url'] ?? null, 'f5cbab06395d882b.webp');

    // 10 ảnh album cưới mặc định
    $defaultGallery = [
        'c3655bd60267c1b2.webp',
        'a5a53978e8384d26.webp',
        '7da1e3a6d59c1a98.webp',
        '40f8fb60afcb59af.webp',
        '4d93c5df689bbae4.webp',
        '872462fa026e4df9.webp',
        '14325e164a136da2.webp',
        'f5cbab06395d882b.webp',
        '686ca1bfd6d65f13.webp',
        'fbf493cec2de5fdc.webp',
    ];
    $rawGallery = $extraData['gallery'] ?? [];
    $gallery = [];
    for ($i = 0; $i < 10; $i++) {
        $gallery[] = !empty($rawGallery[$i]) ? $resolveAsset($rawGallery[$i], $defaultGallery[$i]) : $defaultGallery[$i];
    }

    // 4. Mừng cưới QR & Ngân hàng
    $groomBankName = $extraData['groom_bank_name'] ?? 'Vietcombank';
    $groomBankAccount = $extraData['groom_bank_account'] ?? '0123 456 789';
    $groomAccountName = $extraData['groom_account_name'] ?? 'NGUYEN MINH TUAN';
    $cleanGroomAcc = preg_replace('/\s+/', '', $groomBankAccount);
    $qrGroomUrl = $resolveAsset(
        $extraData['qr_groom_image'] ?? null,
        'https://img.vietqr.io/image/' . strtolower($groomBankName) . '-' . $cleanGroomAcc . '-compact2.png?amount=0&addInfo=' . urlencode('Mung cuoi ' . $groomName)
    );

    $brideBankName = $extraData['bride_bank_name'] ?? 'Techcombank';
    $brideBankAccount = $extraData['bride_bank_account'] ?? '1903 5555 8888';
    $brideAccountName = $extraData['bride_account_name'] ?? 'TRAN THU HA';
    $cleanBrideAcc = preg_replace('/\s+/', '', $brideBankAccount);
    $qrBrideUrl = $resolveAsset(
        $extraData['qr_bride_image'] ?? null,
        'https://img.vietqr.io/image/' . strtolower($brideBankName) . '-' . $cleanBrideAcc . '-compact2.png?amount=0&addInfo=' . urlencode('Mung cuoi ' . $brideName)
    );

    // Tiêu đề & URLs
    $pageTitle = $isPreview
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name')
        : 'Thiệp cưới: ' . $groomName . ' & ' . $brideName . ' | ' . config('app.name');

    $wishSubmitUrl = $wishSubmitUrl ?? ($isPreview ? route('templates.preview.wish', 'giua-muon-ngan-vi-sao') : route('invitation.wish.store', $invitation->slug ?? ''));
    $rsvpSubmitUrl = $rsvpSubmitUrl ?? ($isPreview ? route('templates.preview.rsvp', 'giua-muon-ngan-vi-sao') : route('invitation.show', $invitation->slug ?? ''));

    $cssVersion = file_exists(resource_path('views/client/invitations/giua-muon-ngan-vi-sao/styles.css'))
        ? filemtime(resource_path('views/client/invitations/giua-muon-ngan-vi-sao/styles.css'))
        : time();
    $jsVersion = file_exists(resource_path('views/client/invitations/giua-muon-ngan-vi-sao/script.js'))
        ? filemtime(resource_path('views/client/invitations/giua-muon-ngan-vi-sao/script.js'))
        : time();
@endphp

@extends('client.layouts.invitation')

@section('title', $pageTitle)
@section('meta_description', 'Trân trọng kính mời ' . $guestName . ' tới dự lễ cưới của ' . $groomName . ' & ' . $brideName . '.')
@section('body_class', ($isEmbed ? 'mayhen_invitation_ladoifji894yfv_embed_mode' : 'mayhen_invitation_ladoifji894yfv_sr_khoa'))

@push('fonts')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Great+Vibes&display=swap" rel="stylesheet">
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ route('templates.asset', ['slug' => 'giua-muon-ngan-vi-sao', 'path' => 'styles.css']) }}?v={{ $cssVersion }}">
@endpush

@section('content')
    <!-- ===================== ĐỊNH NGHĨA BIỂU TƯỢNG SVG ===================== -->
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <defs>
            <symbol id="mayhen_invitation_ladoifji894yfv_sr_goc" viewBox="0 0 40 40">
                <path d="M2 30V8a6 6 0 0 1 6-6h22" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity=".7"></path>
                <path d="M8 1.5l1.1 4.4 4.4 1.1-4.4 1.1L8 12.5 6.9 8.1 2.5 7 6.9 5.9z" fill="currentColor"></path>
            </symbol>
            <symbol id="mayhen_invitation_ladoifji894yfv_sr_chom_sao" viewBox="0 0 90 60">
                <path d="M4 50L22 34L40 40L58 16L84 8M40 40L48 56" fill="none" stroke="currentColor" stroke-width=".7" stroke-dasharray="2 3" opacity=".55"></path>
                <g fill="currentColor">
                    <circle class="mayhen_invitation_ladoifji894yfv_d1" cx="4" cy="50" r="1.6"></circle>
                    <circle class="mayhen_invitation_ladoifji894yfv_d2" cx="22" cy="34" r="2.2"></circle>
                    <circle class="mayhen_invitation_ladoifji894yfv_d3" cx="40" cy="40" r="1.5"></circle>
                    <circle class="mayhen_invitation_ladoifji894yfv_d1" cx="58" cy="16" r="2.4"></circle>
                    <circle class="mayhen_invitation_ladoifji894yfv_d2" cx="84" cy="8" r="1.7"></circle>
                    <circle class="mayhen_invitation_ladoifji894yfv_d3" cx="48" cy="56" r="1.3"></circle>
                </g>
            </symbol>
            <symbol id="mayhen_invitation_ladoifji894yfv_sr_sao" viewBox="0 0 20 20">
                <path d="M10 0l2.2 7.8L20 10l-7.8 2.2L10 20l-2.2-7.8L0 10l7.8-2.2z" fill="currentColor"></path>
            </symbol>
        </defs>
    </svg>

    <!-- ===================== LỚP CANVAS NỀN SAO RƠI & HẠT SÁNG ===================== -->
    <canvas class="mayhen_invitation_ladoifji894yfv_sr_nen" aria-hidden="true"></canvas>
    <canvas class="mayhen_invitation_ladoifji894yfv_sr_hat" aria-hidden="true"></canvas>

    <!-- ===================== MÀN MỞ: MÀN SAO RƠI ===================== -->
    <div class="mayhen_invitation_ladoifji894yfv_sr_cua {{ $isEmbed ? 'mayhen_invitation_ladoifji894yfv_da_mo' : '' }}" style="{{ $isEmbed ? 'display: none !important;' : '' }}" data-thoi-gian="2200" role="dialog" aria-label="Thiệp mời cưới">
        <div class="mayhen_invitation_ladoifji894yfv_sr_man">
            <div class="mayhen_invitation_ladoifji894yfv_sr_kicker">
                <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Thiệp Mời Cưới</p></div>
                <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_ten_cua mayhen_invitation_ladoifji894yfv_sr_tho mayhen_invitation_ladoifji894yfv_t1 mayhen_invitation_ladoifji894yfv_sr_ten" data-text="{{ $groomName }}">
                <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline" data-text="{{ $groomName }}" data-sync-target="groom_name">{{ $groomName }}</h2>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_va_cua">&amp;</div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_ten_cua mayhen_invitation_ladoifji894yfv_sr_tho mayhen_invitation_ladoifji894yfv_t2 mayhen_invitation_ladoifji894yfv_sr_ten" data-text="{{ $brideName }}">
                <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline" data-text="{{ $brideName }}" data-sync-target="bride_name">{{ $brideName }}</h2>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_ngay_cua mayhen_invitation_ladoifji894yfv_sr_phat_sang">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_headline" data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</p>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_moi_cua">
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_kinh_moi"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Kính mời</p></div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_class_tenKhachMoi mayhen_invitation_ladoifji894yfv_khach_cua mayhen_invitation_ladoifji894yfv_sr_phat_sang">
                    <h3 class="mayhen_invitation_ladoifji894yfv_ladi_headline">{{ $guestName }}</h3>
                </div>
            </div>
            <div>
                <button type="button" class="mayhen_invitation_ladoifji894yfv_sr_nut_mo">Mở thiệp</button>
            </div>
        </div>
    </div>

    <!-- ===================== NỘI DUNG CHÍNH CỦA THIỆP ===================== -->
    <div class="mayhen_invitation_ladoifji894yfv_ladi_wraper mayhen_invitation_ladoifji894yfv_sr">

        <!-- 1. PHẦN ĐẦU THIỆP -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan mayhen_invitation_ladoifji894yfv_sr_dau">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Lễ Thành Hôn</p></div>
                <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_quy_dao mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="phong">
                <span class="mayhen_invitation_ladoifji894yfv_hao_quang" aria-hidden="true"></span>
                <span class="mayhen_invitation_ladoifji894yfv_sao_quay" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_sao_quay mayhen_invitation_ladoifji894yfv_s2" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_lap_lanh mayhen_invitation_ladoifji894yfv_l1" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_lap_lanh mayhen_invitation_ladoifji894yfv_l2" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_lap_lanh mayhen_invitation_ladoifji894yfv_l3" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_lap_lanh mayhen_invitation_ladoifji894yfv_l4" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_lap_lanh mayhen_invitation_ladoifji894yfv_l5" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditImage mayhen_invitation_ladoifji894yfv_img_dau mayhen_invitation_ladoifji894yfv_anh_tron">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_image">
                        <div class="mayhen_invitation_ladoifji894yfv_ladi_image_background" style="background-image: url('{{ $heroPhotoUrl }}');" data-sync-target="hero_photo_url"></div>
                    </div>
                </div>
                <span class="mayhen_invitation_ladoifji894yfv_vet_sang" aria-hidden="true"></span>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_ten_cap">
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_ten mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="trai">
                    <h1 class="mayhen_invitation_ladoifji894yfv_ladi_headline" data-sync-target="groom_name">{{ $groomName }}</h1>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_sr_va mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="phong">&amp;</div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_ten mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="phai">
                    <h1 class="mayhen_invitation_ladoifji894yfv_ladi_headline" data-sync-target="bride_name">{{ $brideName }}</h1>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_ngay_dau mayhen_invitation_ladoifji894yfv_sr_phat_sang mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_headline" data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</p>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_am_dau mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph" data-sync-target="wedding_lunar_date">{{ $weddingLunar }}</p>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_cau_dau mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">Giữa muôn vàn vì sao,<br>chúng ta đã tìm thấy nhau</p>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 2. HAI BÊN GIA ĐÌNH -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Hai Họ</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Hai Bên Gia Đình</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_gia_dinh mayhen_invitation_ladoifji894yfv_sr_the mayhen_invitation_ladoifji894yfv_kieu_doi mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tr" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_bl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_br" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <span class="mayhen_invitation_ladoifji894yfv_sr_the_tt" aria-hidden="true">
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c1"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c2"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <i class="mayhen_invitation_ladoifji894yfv_huy"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                </span>
                <div class="mayhen_invitation_ladoifji894yfv_ben" data-ben="trai">
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_ho"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Nhà Trai</p></div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_bo_me">
                        <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">
                            <span data-sync-target="groom_father">{{ $groomFather }}</span><br>
                            <span data-sync-target="groom_mother">{{ $groomMother }}</span>
                        </p>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_dc">
                        <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                    </div>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_giua" aria-hidden="true"></div>
                <div class="mayhen_invitation_ladoifji894yfv_ben" data-ben="phai">
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_ho"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Nhà Gái</p></div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_bo_me">
                        <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">
                            <span data-sync-target="bride_father">{{ $brideFather }}</span><br>
                            <span data-sync-target="bride_mother">{{ $brideMother }}</span>
                        </p>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_dc">
                        <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                    </div>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_bao_tin mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">Trân trọng báo tin lễ thành hôn<br>của con chúng tôi</p>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_doi">
                <div class="mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="trai">
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditImage mayhen_invitation_ladoifji894yfv_img_chure mayhen_invitation_ladoifji894yfv_tron_nho">
                        <div class="mayhen_invitation_ladoifji894yfv_ladi_image">
                            <div class="mayhen_invitation_ladoifji894yfv_ladi_image_background" style="background-image: url('{{ $groomPhotoUrl }}');" data-sync-target="groom_photo_url"></div>
                        </div>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_vai"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Chú Rể</p></div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_ten_ben mayhen_invitation_ladoifji894yfv_sr_ten">
                        <h3 class="mayhen_invitation_ladoifji894yfv_ladi_headline" data-sync-target="groom_name">{{ $groomName }}</h3>
                    </div>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="phai">
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditImage mayhen_invitation_ladoifji894yfv_img_codau mayhen_invitation_ladoifji894yfv_tron_nho">
                        <div class="mayhen_invitation_ladoifji894yfv_ladi_image">
                            <div class="mayhen_invitation_ladoifji894yfv_ladi_image_background" style="background-image: url('{{ $bridePhotoUrl }}');" data-sync-target="bride_photo_url"></div>
                        </div>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_vai"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Cô Dâu</p></div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_ten_ben mayhen_invitation_ladoifji894yfv_sr_ten">
                        <h3 class="mayhen_invitation_ladoifji894yfv_ladi_headline" data-sync-target="bride_name">{{ $brideName }}</h3>
                    </div>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 3. THƯ MỜI TIỆC CƯỚI -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Thư Mời</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_mot_dong mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Trân Trọng Kính Mời</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_thu mayhen_invitation_ladoifji894yfv_sr_the mayhen_invitation_ladoifji894yfv_kieu_ve mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <i class="mayhen_invitation_ladoifji894yfv_ve_lo mayhen_invitation_ladoifji894yfv_trai" aria-hidden="true"></i>
                <i class="mayhen_invitation_ladoifji894yfv_ve_lo mayhen_invitation_ladoifji894yfv_phai" aria-hidden="true"></i>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tr" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_bl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_br" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <span class="mayhen_invitation_ladoifji894yfv_sr_the_tt" aria-hidden="true">
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c1"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c2"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <i class="mayhen_invitation_ladoifji894yfv_huy"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                </span>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_trang_trong"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Kính mời</p></div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_class_tenKhachMoi mayhen_invitation_ladoifji894yfv_khach mayhen_invitation_ladoifji894yfv_sr_ten"><h3 class="mayhen_invitation_ladoifji894yfv_ladi_headline">{{ $guestName }}</h3></div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_toi_du"><p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">tới dự bữa tiệc chung vui<br>cùng gia đình chúng tôi</p></div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditImage mayhen_invitation_ladoifji894yfv_img_moi mayhen_invitation_ladoifji894yfv_anh_moi">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_image">
                        <div class="mayhen_invitation_ladoifji894yfv_ladi_image_background" style="background-image: url('{{ $saveDatePhotoUrl }}');" data-sync-target="save_date_photo_url"></div>
                    </div>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_vao_luc"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Vào lúc</p></div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_gio mayhen_invitation_ladoifji894yfv_sr_phat_sang">
                    <p class="mayhen_invitation_ladoifji894yfv_ladi_headline" data-sync-target="party_time">{{ $partyTime }}</p>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_sr_ngay_moi">
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_ben"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">{{ $weddingDayOfWeek }}</p></div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_so"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">{{ $weddingDay }}</p></div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_ben"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Tháng {{ $weddingMonth }}</p></div>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_nam"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">{{ $weddingYear }}</p></div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_am_lich"><p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph" data-sync-target="wedding_lunar_date">({{ $weddingLunar }})</p></div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_tai"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Tại</p></div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_noi mayhen_invitation_ladoifji894yfv_sr_phat_sang"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Trung tâm Tiệc cưới</p></div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_noi_ten mayhen_invitation_ladoifji894yfv_sr_ten">
                    <p class="mayhen_invitation_ladoifji894yfv_ladi_headline" data-sync-target="party_venue">{{ $partyVenue }}</p>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_dia_chi">
                    <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph" data-sync-target="event_party_address">{{ $partyAddress }}</p>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 4. NGHI LỄ GIA TIÊN -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Nghi Lễ</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Lễ Gia Tiên</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_ds_le">
                <div class="mayhen_invitation_ladoifji894yfv_sr_le mayhen_invitation_ladoifji894yfv_sr_the mayhen_invitation_ladoifji894yfv_kieu_vom mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tr" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_bl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_br" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                    <span class="mayhen_invitation_ladoifji894yfv_sr_the_tt" aria-hidden="true">
                        <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c1"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                        <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c2"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                        <i class="mayhen_invitation_ladoifji894yfv_huy"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                    </span>
                    <div class="mayhen_invitation_ladoifji894yfv_bieu_tuong" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="14" r="5.5"></circle>
                            <circle cx="15" cy="14" r="5.5"></circle>
                            <path d="M9 4.5l1.5-2h3L15 4.5l-3 3z"></path>
                        </svg>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_ten_le"><h3 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Lễ Vu Quy</h3></div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_gio_le">
                        <p class="mayhen_invitation_ladoifji894yfv_ladi_headline"><span data-sync-target="ceremony_time">{{ $ceremonyTime }}</span> · {{ $weddingDayOfWeek }} {{ $weddingDate->format('d.m.Y') }}</p>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_noi_le">
                        <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph" data-sync-target="ceremony_address">{{ $ceremonyAddress }}</p>
                    </div>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_sr_le mayhen_invitation_ladoifji894yfv_sr_the mayhen_invitation_ladoifji894yfv_kieu_vom mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tr" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_bl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_br" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                    <span class="mayhen_invitation_ladoifji894yfv_sr_the_tt" aria-hidden="true">
                        <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c1"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                        <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c2"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                        <i class="mayhen_invitation_ladoifji894yfv_huy"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                    </span>
                    <div class="mayhen_invitation_ladoifji894yfv_bieu_tuong" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V10l7-6 7 6v11"></path>
                            <path d="M9 21v-6h6v6"></path>
                        </svg>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_ten_le"><h3 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Lễ Thành Hôn</h3></div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_gio_le">
                        <p class="mayhen_invitation_ladoifji894yfv_ladi_headline"><span data-sync-target="ceremony_groom_time">{{ $ceremonyGroomTime }}</span> · {{ $weddingDayOfWeek }} {{ $weddingDate->format('d.m.Y') }}</p>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_noi_le">
                        <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph" data-sync-target="ceremony_groom_address">{{ $ceremonyGroomAddress }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 5. LỊCH CƯỚI -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Save The Date</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Lịch Cưới</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_to_lich mayhen_invitation_ladoifji894yfv_sr_the mayhen_invitation_ladoifji894yfv_kieu_cua_so mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tr" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_bl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_br" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <span class="mayhen_invitation_ladoifji894yfv_sr_the_tt" aria-hidden="true">
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c1"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c2"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <i class="mayhen_invitation_ladoifji894yfv_huy"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                </span>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_thang_lich">
                    <p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Tháng {{ $weddingMonth }} · {{ $weddingYear }}</p>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_edit_Calendar">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_html_code">
                        <div class="mayhen_invitation_ladoifji894yfv_calendar_wrapper">
                            <div class="mayhen_invitation_ladoifji894yfv_calendar_header">
                                <div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div>T7</div><div>CN</div>
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_calendar_body">
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day mayhen_invitation_ladoifji894yfv_empty">30</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">1</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">2</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">3</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">4</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">5</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">6</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">7</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">8</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">9</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">10</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">11</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">12</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">13</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">14</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">15</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">16</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">17</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">18</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">19</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day mayhen_invitation_ladoifji894yfv_selected mayhen_invitation_ladoifji894yfv_cal_selected_bob">{{ $weddingDay }}</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">21</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">22</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">23</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">24</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">25</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">26</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">27</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">28</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">29</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">30</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day">31</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day mayhen_invitation_ladoifji894yfv_empty">1</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day mayhen_invitation_ladoifji894yfv_empty">2</div>
                                <div class="mayhen_invitation_ladoifji894yfv_calendar_day mayhen_invitation_ladoifji894yfv_empty">3</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a class="mayhen_invitation_ladoifji894yfv_sr_nut mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len" target="_blank" rel="noopener" href="https://calendar.google.com/calendar/u/0/r/eventedit?text={{ urlencode('Lễ cưới ' . $groomName . ' & ' . $brideName) }}&dates={{ $weddingDate->format('Ymd\THis\Z') }}/{{ $weddingDate->copy()->addHours(4)->format('Ymd\THis\Z') }}&details={{ urlencode('Trân trọng kính mời tham dự lễ cưới của chúng tôi.') }}&location={{ urlencode($partyVenue . ', ' . $partyAddress) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                    <path d="M16 3v4M8 3v4M3 10h18M12 14v4M10 16h4"></path>
                </svg>
                Thêm vào lịch
            </a>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 6. ĐẾM NGƯỢC -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan mayhen_invitation_ladoifji894yfv_sr_dem">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Đếm Ngược</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Tới Ngày Chung Đôi</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_cho_dem mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="phong">
                <div class="mayhen_invitation_ladoifji894yfv_edit_countDown" data-countdown="{{ $weddingDate->toIso8601String() }}">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_html_code">
                        <div class="mayhen_invitation_ladoifji894yfv_countdown_item">
                            <div class="mayhen_invitation_ladoifji894yfv_countdown_shape"><div class="mayhen_invitation_ladoifji894yfv_countdown_number mayhen_invitation_ladoifji894yfv_cd_day">0</div></div>
                            <div class="mayhen_invitation_ladoifji894yfv_countdown_label">Ngày</div>
                        </div>
                        <span class="mayhen_invitation_ladoifji894yfv_countdown_sep">:</span>
                        <div class="mayhen_invitation_ladoifji894yfv_countdown_item">
                            <div class="mayhen_invitation_ladoifji894yfv_countdown_shape"><div class="mayhen_invitation_ladoifji894yfv_countdown_number mayhen_invitation_ladoifji894yfv_cd_hour">0</div></div>
                            <div class="mayhen_invitation_ladoifji894yfv_countdown_label">Giờ</div>
                        </div>
                        <span class="mayhen_invitation_ladoifji894yfv_countdown_sep">:</span>
                        <div class="mayhen_invitation_ladoifji894yfv_countdown_item">
                            <div class="mayhen_invitation_ladoifji894yfv_countdown_shape"><div class="mayhen_invitation_ladoifji894yfv_countdown_number mayhen_invitation_ladoifji894yfv_cd_min">0</div></div>
                            <div class="mayhen_invitation_ladoifji894yfv_countdown_label">Phút</div>
                        </div>
                        <span class="mayhen_invitation_ladoifji894yfv_countdown_sep">:</span>
                        <div class="mayhen_invitation_ladoifji894yfv_countdown_item">
                            <div class="mayhen_invitation_ladoifji894yfv_countdown_shape"><div class="mayhen_invitation_ladoifji894yfv_countdown_number mayhen_invitation_ladoifji894yfv_cd_sec">0</div></div>
                            <div class="mayhen_invitation_ladoifji894yfv_countdown_label">Giây</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_loi_dem mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">Mỗi ngôi sao rơi là một ngày gần nhau hơn</p>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 7. LỊCH TRÌNH BUỔI TIỆC -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Timeline</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Lịch Trình Buổi Tiệc</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_tl mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <div class="mayhen_invitation_ladoifji894yfv_edit_Timeline">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_html_code">
                        <div class="mayhen_invitation_ladoifji894yfv_wb_tl">
                            <div class="mayhen_invitation_ladoifji894yfv_wb_tl_row">
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_time">17:30</div>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_col">
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_tl_dot">
                                        <svg width="22" height="22" fill="currentColor" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M322,185.1h-88.3c-2.8,0-5,2.2-5,5s2.2,5,5,5H322c2.8,0,5-2.2,5-5S324.7,185.1,322,185.1z"></path>
                                            <path d="M162.5,195.1h41.2c2.8,0,5-2.2,5-5s-2.2-5-5-5h-41.2c-2.8,0-5,2.2-5,5S159.8,195.1,162.5,195.1z"></path>
                                            <path d="M354.5,225.6c0-2.8-2.2-5-5-5h-187c-2.8,0-5,2.2-5,5s2.2,5,5,5h187C352.2,230.6,354.5,228.3,354.5,225.6z"></path>
                                            <path d="M162.5,159.6h145.5c2.8,0,5-2.2,5-5s-2.2-5-5-5H162.5c-2.8,0-5,2.2-5,5S159.8,159.6,162.5,159.6z"></path>
                                            <path d="M354.5,109.1V93.6c0-8.3-6.7-15-15-15h-167c-8.3,0-15,6.7-15,15v15.5c0,8.3,6.7,15,15,15h167C347.8,124.1,354.5,117.3,354.5,109.1z"></path>
                                        </svg>
                                    </div>
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_tl_line"></div>
                                </div>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_lbl">Đón khách</div>
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_tl_row">
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_time">{{ $partyTime }}</div>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_col">
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_tl_dot">
                                        <svg width="22" height="22" fill="currentColor" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M354.079,186.547c-37.031,0-71.12,12.818-98.079,34.241c-26.959-21.424-61.048-34.241-98.079-34.241C70.842,186.547,0,257.389,0,344.467s70.842,157.921,157.921,157.921c37.031,0,71.12-12.818,98.079-34.241c26.959,21.424,61.048,34.241,98.079,34.241c87.078,0,157.921-70.843,157.921-157.921S441.158,186.547,354.079,186.547z"></path>
                                        </svg>
                                    </div>
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_tl_line"></div>
                                </div>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_lbl">Cử hành hôn lễ</div>
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_tl_row">
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_time">18:30</div>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_col">
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_tl_dot">
                                        <svg width="22" height="22" fill="currentColor" viewBox="0 0 354.662 354.662" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M180.502,58.058c-65.766,0-119.271,53.506-119.271,119.275c0,65.764,53.506,119.27,119.271,119.27c65.768,0,119.272-53.506,119.272-119.27C299.774,111.564,246.27,58.058,180.502,58.058z"></path>
                                        </svg>
                                    </div>
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_tl_line"></div>
                                </div>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_lbl">Khai tiệc</div>
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_tl_row">
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_time">20:00</div>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_col">
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_tl_dot">
                                        <svg width="22" height="22" fill="currentColor" viewBox="0 0 233.978 233.978" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M185.489,90.978v-28h-9.134c7.751-6.556,12.686-16.343,12.686-27.267C189.041,16.02,173.021,0,153.33,0c-14.637,0-27.24,8.855-32.748,21.487C115.073,8.855,102.471,0,87.833,0C68.142,0,52.123,16.02,52.123,35.711c0,10.923,4.935,20.711,12.686,27.267h-11.32v23.494l-27-4.623v61.912l27-4.623v22.84h42.467l-32.547,72h22.286l22.794-51.161v51.161h21v-55.585l25.461,55.585h22.286l-32.548-72h40.801v-26h22v-45H185.489z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_tl_lbl">Giao lưu &amp; chụp ảnh</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 8. BẢN ĐỒ -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan mayhen_invitation_ladoifji894yfv_sr_bd">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Bản Đồ</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Đường Tới Hôn Lễ</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_khung_bd mayhen_invitation_ladoifji894yfv_sr_the mayhen_invitation_ladoifji894yfv_kieu_cong mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tr" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_bl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_br" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <span class="mayhen_invitation_ladoifji894yfv_sr_the_tt" aria-hidden="true">
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c1"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c2"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <i class="mayhen_invitation_ladoifji894yfv_huy"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                </span>
                <div class="mayhen_invitation_ladoifji894yfv_cua_bd">
                    <div class="mayhen_invitation_ladoifji894yfv_khe" aria-hidden="true"></div>
                    <div class="mayhen_invitation_ladoifji894yfv_canh mayhen_invitation_ladoifji894yfv_trai" aria-hidden="true">
                        <i class="mayhen_invitation_ladoifji894yfv_hoa_van"></i><i class="mayhen_invitation_ladoifji894yfv_tay"></i><i class="mayhen_invitation_ladoifji894yfv_mep"></i>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_canh mayhen_invitation_ladoifji894yfv_phai" aria-hidden="true">
                        <i class="mayhen_invitation_ladoifji894yfv_hoa_van"></i><i class="mayhen_invitation_ladoifji894yfv_tay"></i><i class="mayhen_invitation_ladoifji894yfv_mep"></i>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_khoa" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></div>
                    <div class="mayhen_invitation_ladoifji894yfv_bung" aria-hidden="true">
                        <i style="--g:0deg;--r:120px;--t:0.0s"></i><i style="--g:20deg;--r:90px;--t:0.05s"></i>
                        <i style="--g:45deg;--r:140px;--t:0.1s"></i><i style="--g:70deg;--r:100px;--t:0.02s"></i>
                        <i style="--g:95deg;--r:130px;--t:0.08s"></i><i style="--g:120deg;--r:85px;--t:0.12s"></i>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_loe" aria-hidden="true"></div>
                    <button type="button" class="mayhen_invitation_ladoifji894yfv_nut_mo_bd">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"></path>
                            <circle cx="12" cy="10" r="2.5"></circle>
                        </svg>
                        <span>Mở bản đồ</span>
                    </button>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_edit_googleMap">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_html_code">
                        <iframe src="{{ $partyMapLink }}" width="600" height="450" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Bản đồ nơi tổ chức"></iframe>
                    </div>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_dia_chi_bd mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph" data-sync-target="event_party_address">
                    <span data-sync-target="party_venue">{{ $partyVenue }}</span><br>
                    {{ $partyAddress }}
                </p>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 9. ALBUM ẢNH (3D COVERFLOW) -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Album</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Khoảnh Khắc</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_tmx_album_cho mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <div class="mayhen_invitation_ladoifji894yfv_edit_galleryV2">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_html_code">
                        <div class="mayhen_invitation_ladoifji894yfv_wbgv2 mayhen_invitation_ladoifji894yfv_wb_cf">
                            @foreach ($gallery as $k => $pic)
                                <div class="mayhen_invitation_ladoifji894yfv_ClientEditImage mayhen_invitation_ladoifji894yfv_album_img_{{ $k }} mayhen_invitation_ladoifji894yfv_wb_cf_it">
                                    <div class="mayhen_invitation_ladoifji894yfv_ladi_image">
                                        <div class="mayhen_invitation_ladoifji894yfv_ladi_image_background" style="background-image: url('{{ $pic }}');" data-sync-target="gallery.{{ $k }}"></div>
                                    </div>
                                </div>
                            @endforeach
                            <button type="button" class="mayhen_invitation_ladoifji894yfv_wb_cf_nut mayhen_invitation_ladoifji894yfv_truoc" aria-label="Ảnh trước">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"></path></svg>
                            </button>
                            <button type="button" class="mayhen_invitation_ladoifji894yfv_wb_cf_nut mayhen_invitation_ladoifji894yfv_sau" aria-label="Ảnh sau">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="transform: scaleX(-1);"><path d="M15 18l-6-6 6-6"></path></svg>
                            </button>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_cf_dots">
                                @foreach ($gallery as $idx => $p)
                                    <button type="button" aria-label="Ảnh {{ $idx + 1 }}" class="{{ $idx === 0 ? 'mayhen_invitation_ladoifji894yfv_on' : '' }}"></button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_album_cau mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">Mỗi khoảnh khắc bên nhau đều lấp lánh như sao trời</p>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 10. KÝ TÊN CHÚC PHÚC -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Signature</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Ký Tên Chúc Phúc</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_mo_ta mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">Để lại chữ ký của bạn và cùng chúng mình<br>lưu giữ kỷ niệm hạnh phúc này nhé!</p>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_khung_ky mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <div class="mayhen_invitation_ladoifji894yfv_nen_ky">
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditImage mayhen_invitation_ladoifji894yfv_img_ky">
                        <div class="mayhen_invitation_ladoifji894yfv_ladi_image">
                            <div class="mayhen_invitation_ladoifji894yfv_ladi_image_background" style="background-image: url('{{ $kyPhotoUrl }}');"></div>
                        </div>
                    </div>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_edit_signature">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_html_code">
                        <div class="mayhen_invitation_ladoifji894yfv_wb_sig_root">
                            <div class="mayhen_invitation_ladoifji894yfv_wb_sig_display">
                                <div class="mayhen_invitation_ladoifji894yfv_wb_sig_display_inner">
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_sig_item" style="left: 3%; top: 29%; filter: drop-shadow(rgba(0, 0, 0, 0.25) 0px 2px 8px);"><img src="{{ $assetBase }}/sig_1.png" alt="Chữ ký"></div>
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_sig_item" style="left: 72%; top: 13%; filter: drop-shadow(rgba(0, 0, 0, 0.25) 0px 2px 8px);"><img src="{{ $assetBase }}/sig_2.png" alt="Chữ ký"></div>
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_sig_item mayhen_invitation_ladoifji894yfv_wb_sig_idle_pop" style="left: 5%; top: 5%; filter: drop-shadow(rgba(0, 0, 0, 0.25) 0px 2px 8px);"><img src="{{ $assetBase }}/sig_3.png" alt="Chữ ký"></div>
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_sig_item mayhen_invitation_ladoifji894yfv_wb_sig_idle_pop" style="left: 72%; top: 32%; filter: drop-shadow(rgba(0, 0, 0, 0.25) 0px 2px 8px);"><img src="{{ $assetBase }}/sig_4.png" alt="Chữ ký"></div>
                                </div>
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_sig_spacer"></div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_sig_btn_row">
                                <button type="button" class="mayhen_invitation_ladoifji894yfv_wb_sig_open_btn mayhen_invitation_ladoifji894yfv_wb_sig_btn">
                                    <svg class="mayhen_invitation_ladoifji894yfv_wb_sig_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="m21 17-2.156-1.868A.5.5 0 0 0 18 15.5v.5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1c0-2.545-3.991-3.97-8.5-4a1 1 0 0 0 0 5c4.153 0 4.745-11.295 5.708-13.5a2.5 2.5 0 1 1 3.31 3.284"></path>
                                        <path d="M3 21h18"></path>
                                    </svg>
                                    <span>Ký tên của bạn</span>
                                </button>
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_sig_pos_btns">
                                <button type="button" class="mayhen_invitation_ladoifji894yfv_wb_sig_btn_back mayhen_invitation_ladoifji894yfv_wb_sig_btn">
                                    <svg class="mayhen_invitation_ladoifji894yfv_wb_sig_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                        <path d="M3 3v5h5"></path>
                                    </svg>
                                    <span>Ký lại</span>
                                </button>
                                <button type="button" class="mayhen_invitation_ladoifji894yfv_wb_sig_btn_save mayhen_invitation_ladoifji894yfv_wb_sig_btn">
                                    <svg class="mayhen_invitation_ladoifji894yfv_wb_sig_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"></path>
                                        <path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"></path>
                                        <path d="M7 3v4a1 1 0 0 0 1 1h7"></path>
                                    </svg>
                                    <span>Lưu chữ ký</span>
                                </button>
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_sig_pad">
                                <div class="mayhen_invitation_ladoifji894yfv_wb_sig_pad_inner">
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_sig_pad_title">
                                        <svg class="mayhen_invitation_ladoifji894yfv_wb_sig_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m21 17-2.156-1.868A.5.5 0 0 0 18 15.5v.5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1c0-2.545-3.991-3.97-8.5-4a1 1 0 0 0 0 5c4.153 0 4.745-11.295 5.708-13.5a2.5 2.5 0 1 1 3.31 3.284"></path>
                                            <path d="M3 21h18"></path>
                                        </svg>
                                        <span>Ký tên của bạn</span>
                                    </div>
                                    <canvas class="mayhen_invitation_ladoifji894yfv_wb_sig_canvas" height="180"></canvas>
                                    <div class="mayhen_invitation_ladoifji894yfv_wb_sig_controls">
                                        <button type="button" class="mayhen_invitation_ladoifji894yfv_wb_sig_btn_clear mayhen_invitation_ladoifji894yfv_wb_sig_btn">
                                            <svg class="mayhen_invitation_ladoifji894yfv_wb_sig_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M10 11v6"></path><path d="M14 11v6"></path>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                            <span>Xóa</span>
                                        </button>
                                        <button type="button" class="mayhen_invitation_ladoifji894yfv_wb_sig_btn_pos mayhen_invitation_ladoifji894yfv_wb_sig_btn">
                                            <svg class="mayhen_invitation_ladoifji894yfv_wb_sig_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 2v20"></path><path d="m15 19-3 3-3-3"></path>
                                                <path d="m19 9 3 3-3 3"></path><path d="M2 12h20"></path>
                                                <path d="m5 9-3 3 3 3"></path><path d="m9 5 3-3 3 3"></path>
                                            </svg>
                                            <span>Chỉnh vị trí</span>
                                        </button>
                                        <button type="button" class="mayhen_invitation_ladoifji894yfv_wb_sig_btn_cancel mayhen_invitation_ladoifji894yfv_wb_sig_btn">
                                            <svg class="mayhen_invitation_ladoifji894yfv_wb_sig_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M18 6 6 18"></path><path d="m6 6 12 12"></path>
                                            </svg>
                                            <span>Hủy</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 11. XÁC NHẬN THAM DỰ (RSVP) -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">RSVP</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Xác Nhận Tham Dự</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="phong">
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditImage mayhen_invitation_ladoifji894yfv_img_xn mayhen_invitation_ladoifji894yfv_sr_anh_xn">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_image">
                        <div class="mayhen_invitation_ladoifji894yfv_ladi_image_background" style="background-image: url('{{ $xnPhotoUrl }}');"></div>
                    </div>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_mo_ta mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">Hãy cho chúng mình biết bạn có thể tới chung vui<br>để gia đình chuẩn bị chu đáo hơn nhé!</p>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_khung_form mayhen_invitation_ladoifji894yfv_sr_the mayhen_invitation_ladoifji894yfv_kieu_sao_vien mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tr" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_bl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_br" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <span class="mayhen_invitation_ladoifji894yfv_sr_the_tt" aria-hidden="true">
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c1"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c2"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <i class="mayhen_invitation_ladoifji894yfv_huy"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                </span>
                <div class="mayhen_invitation_ladoifji894yfv_edit_RPSV">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_html_code">
                        <div class="mayhen_invitation_ladoifji894yfv_wb_rsvp_form">
                            <div class="mayhen_invitation_ladoifji894yfv_wb_form_field">
                                <label class="mayhen_invitation_ladoifji894yfv_wb_form_label">Tên khách mời</label>
                                <input type="text" class="mayhen_invitation_ladoifji894yfv_wb_form_input" name="name" placeholder="Nhập tên của bạn" value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}">
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_form_field">
                                <label class="mayhen_invitation_ladoifji894yfv_wb_form_label">Bạn là khách của ai?</label>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_opt_group" data-name="guestOf">
                                    <label class="mayhen_invitation_ladoifji894yfv_wb_opt_label mayhen_invitation_ladoifji894yfv_active" data-val="bride">Cô Dâu</label>
                                    <label class="mayhen_invitation_ladoifji894yfv_wb_opt_label" data-val="groom">Chú Rể</label>
                                </div>
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_form_field">
                                <label class="mayhen_invitation_ladoifji894yfv_wb_form_label">Xác nhận tham dự</label>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_opt_group" data-name="attendance">
                                    <label class="mayhen_invitation_ladoifji894yfv_wb_opt_label mayhen_invitation_ladoifji894yfv_active" data-val="yes">Sẽ tham dự</label>
                                    <label class="mayhen_invitation_ladoifji894yfv_wb_opt_label" data-val="no">Tiếc quá</label>
                                </div>
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_form_field mayhen_invitation_ladoifji894yfv_wb_guest_count_field">
                                <label class="mayhen_invitation_ladoifji894yfv_wb_form_label">Số lượng khách tham dự</label>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_counter">
                                    <button type="button" class="mayhen_invitation_ladoifji894yfv_wb_counter_btn mayhen_invitation_ladoifji894yfv_wb_dec">−</button>
                                    <input type="text" class="mayhen_invitation_ladoifji894yfv_wb_counter_val" value="1" readonly>
                                    <button type="button" class="mayhen_invitation_ladoifji894yfv_wb_counter_btn mayhen_invitation_ladoifji894yfv_wb_inc">+</button>
                                </div>
                            </div>
                            <button class="mayhen_invitation_ladoifji894yfv_wb_submit_btn" type="button">Xác Nhận</button>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 12. GỬI LỜI CHÚC (GUEST BOOK) -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Guest Book</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Gửi Lời Chúc</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_khung_form mayhen_invitation_ladoifji894yfv_sr_the mayhen_invitation_ladoifji894yfv_kieu_trang mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tr" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_bl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_br" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                <span class="mayhen_invitation_ladoifji894yfv_sr_the_tt" aria-hidden="true">
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c1"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c2"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                    <i class="mayhen_invitation_ladoifji894yfv_huy"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                </span>
                <i class="mayhen_invitation_ladoifji894yfv_trang_khuyet" aria-hidden="true"></i>
                <div class="mayhen_invitation_ladoifji894yfv_edit_sendMessage">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_html_code">
                        <div class="mayhen_invitation_ladoifji894yfv_wb_msg_form">
                            <div class="mayhen_invitation_ladoifji894yfv_wb_form_field">
                                <label class="mayhen_invitation_ladoifji894yfv_wb_form_label">Tên của bạn</label>
                                <input type="text" class="mayhen_invitation_ladoifji894yfv_wb_form_input" name="name" placeholder="Nhập tên của bạn" value="{{ $guestName !== 'Quý Khách' ? $guestName : '' }}">
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_form_field">
                                <label class="mayhen_invitation_ladoifji894yfv_wb_form_label">Ai sẽ thấy lời nhắn này?</label>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_opt_group" data-name="visibility">
                                    <label class="mayhen_invitation_ladoifji894yfv_wb_opt_label mayhen_invitation_ladoifji894yfv_active" data-val="all">Tất cả mọi người</label>
                                    <label class="mayhen_invitation_ladoifji894yfv_wb_opt_label" data-val="private">Chỉ cô dâu, chú rể</label>
                                </div>
                            </div>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_form_field">
                                <label class="mayhen_invitation_ladoifji894yfv_wb_form_label">Lời nhắn của bạn</label>
                                <textarea class="mayhen_invitation_ladoifji894yfv_wb_form_textarea" name="message" maxlength="1500" placeholder="Viết lời chúc của bạn..."></textarea>
                                <div class="mayhen_invitation_ladoifji894yfv_wb_char_count"><span class="mayhen_invitation_ladoifji894yfv_wb_char_num">0</span>/1500</div>
                            </div>
                            <button class="mayhen_invitation_ladoifji894yfv_wb_submit_btn" type="button">Gửi lời chúc</button>
                            <div class="mayhen_invitation_ladoifji894yfv_wb_feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_ds_loi_chuc mayhen_invitation_ladoifji894yfv_sr_ten">
                    <p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Lời chúc từ mọi người</p>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_MessageBox">
                    @if (isset($wishes) && $wishes->count() > 0)
                        @foreach ($wishes as $wish)
                            <div class="mayhen_invitation_ladoifji894yfv_MessageBox_item mayhen_invitation_ladoifji894yfv_wb_msg_first">
                                <h2 class="mayhen_invitation_ladoifji894yfv_MessageBox_item_name">{{ $wish->guest_name }}</h2>
                                <p class="mayhen_invitation_ladoifji894yfv_MessageBox_item_message">{{ $wish->message }}</p>
                            </div>
                        @endforeach
                    @else
                        <div class="mayhen_invitation_ladoifji894yfv_MessageBox_item mayhen_invitation_ladoifji894yfv_wb_msg_first">
                            <h2 class="mayhen_invitation_ladoifji894yfv_MessageBox_item_name">Mây Hẹn</h2>
                            <p class="mayhen_invitation_ladoifji894yfv_MessageBox_item_message">Chúc hai bạn trăm năm hạnh phúc, vẹn tròn yêu thương!</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 13. MỪNG CƯỚI (HỘP QUÀ & QR) -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_dau_muc">
                <div class="mayhen_invitation_ladoifji894yfv_sr_kicker mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                    <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_nhan"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Mừng Cưới</p></div>
                    <i class="mayhen_invitation_ladoifji894yfv_sr_orn"></i>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_tieu_de mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                    <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Hộp Mừng Cưới</h2>
                </div>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_mo_ta mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">Cảm ơn tấm lòng của bạn dành cho chúng mình</p>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_qua_vung">
                <div class="mayhen_invitation_ladoifji894yfv_sr_hop_lop">
                    <div class="mayhen_invitation_ladoifji894yfv_sr_hop_qua mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="phong" role="button" tabindex="0" aria-label="Mở hộp mừng cưới">
                        <span class="mayhen_invitation_ladoifji894yfv_hq_hao" aria-hidden="true"></span>
                        <span class="mayhen_invitation_ladoifji894yfv_hq_cot" aria-hidden="true"></span>
                        <span class="mayhen_invitation_ladoifji894yfv_hq_quy" aria-hidden="true"><i></i><i></i><i></i></span>
                        <span class="mayhen_invitation_ladoifji894yfv_hq_noi" aria-hidden="true">
                            <span class="mayhen_invitation_ladoifji894yfv_hq_than"><i class="mayhen_invitation_ladoifji894yfv_bong"></i></span>
                            <span class="mayhen_invitation_ladoifji894yfv_hq_nap"><i class="mayhen_invitation_ladoifji894yfv_no mayhen_invitation_ladoifji894yfv_trai"></i><i class="mayhen_invitation_ladoifji894yfv_no mayhen_invitation_ladoifji894yfv_phai"></i><i class="mayhen_invitation_ladoifji894yfv_nut"></i></span>
                        </span>
                        <span class="mayhen_invitation_ladoifji894yfv_hq_tia" aria-hidden="true">
                            <i><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i><i><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                            <i><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i><i><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                            <i><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i><i><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                        </span>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_sr_goi_y_hop mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">Chạm để mở hộp mừng cưới</div>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_sr_qr">
                    <div class="mayhen_invitation_ladoifji894yfv_sr_the_qr mayhen_invitation_ladoifji894yfv_sr_the mayhen_invitation_ladoifji894yfv_kieu_bao_thu mayhen_invitation_ladoifji894yfv_tmx_qr">
                        <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                        <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tr" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                        <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_bl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                        <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_br" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                        <span class="mayhen_invitation_ladoifji894yfv_sr_the_tt" aria-hidden="true">
                            <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c1"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                            <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c2"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                            <i class="mayhen_invitation_ladoifji894yfv_huy"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                        </span>
                        <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_vai_qr mayhen_invitation_ladoifji894yfv_tmx_vai_qr"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Chú Rể</p></div>
                        <div class="mayhen_invitation_ladoifji894yfv_ClientEditImage mayhen_invitation_ladoifji894yfv_tmx_anh_trong mayhen_invitation_ladoifji894yfv_anh_qr">
                            <div class="mayhen_invitation_ladoifji894yfv_ladi_image">
                                <div class="mayhen_invitation_ladoifji894yfv_ladi_image_background" style="background-image: url('{{ $qrGroomUrl }}');" data-sync-target="qr_groom_image"></div>
                            </div>
                        </div>
                        <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_tk mayhen_invitation_ladoifji894yfv_tmx_chu_tk">
                            <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">
                                <span data-sync-target="groom_bank_name">{{ $groomBankName }}</span><br>
                                <b data-sync-target="groom_bank_account">{{ $groomBankAccount }}</b><br>
                                <span data-sync-target="groom_account_name">{{ $groomAccountName }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="mayhen_invitation_ladoifji894yfv_sr_the_qr mayhen_invitation_ladoifji894yfv_sr_the mayhen_invitation_ladoifji894yfv_kieu_bao_thu mayhen_invitation_ladoifji894yfv_tmx_qr">
                        <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                        <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_tr" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                        <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_bl" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                        <svg class="mayhen_invitation_ladoifji894yfv_sr_goc mayhen_invitation_ladoifji894yfv_br" aria-hidden="true"><use href="#mayhen_invitation_ladoifji894yfv_sr_goc"></use></svg>
                        <span class="mayhen_invitation_ladoifji894yfv_sr_the_tt" aria-hidden="true">
                            <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c1"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                            <svg class="mayhen_invitation_ladoifji894yfv_chom mayhen_invitation_ladoifji894yfv_c2"><use href="#mayhen_invitation_ladoifji894yfv_sr_chom_sao"></use></svg>
                            <i class="mayhen_invitation_ladoifji894yfv_huy"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></i>
                        </span>
                        <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_vai_qr mayhen_invitation_ladoifji894yfv_tmx_vai_qr"><p class="mayhen_invitation_ladoifji894yfv_ladi_headline">Cô Dâu</p></div>
                        <div class="mayhen_invitation_ladoifji894yfv_ClientEditImage mayhen_invitation_ladoifji894yfv_tmx_anh_trong mayhen_invitation_ladoifji894yfv_anh_qr">
                            <div class="mayhen_invitation_ladoifji894yfv_ladi_image">
                                <div class="mayhen_invitation_ladoifji894yfv_ladi_image_background" style="background-image: url('{{ $qrBrideUrl }}');" data-sync-target="qr_bride_image"></div>
                            </div>
                        </div>
                        <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_tk mayhen_invitation_ladoifji894yfv_tmx_chu_tk">
                            <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">
                                <span data-sync-target="bride_bank_name">{{ $brideBankName }}</span><br>
                                <b data-sync-target="bride_bank_account">{{ $brideBankAccount }}</b><br>
                                <span data-sync-target="bride_account_name">{{ $brideAccountName }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mayhen_invitation_ladoifji894yfv_sr_qr_goi_y">Chạm vào mã QR để phóng to</div>
            </div>
        </section>

        <div class="mayhen_invitation_ladoifji894yfv_sr_vach" aria-hidden="true"></div>

        <!-- 14. LỜI CẢM ƠN (KẾT) -->
        <section class="mayhen_invitation_ladoifji894yfv_sr_phan mayhen_invitation_ladoifji894yfv_sr_ket">
            <div class="mayhen_invitation_ladoifji894yfv_ladi_section_background"></div>
            <div class="mayhen_invitation_ladoifji894yfv_sr_quy_dao mayhen_invitation_ladoifji894yfv_bau_duc mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="phong">
                <span class="mayhen_invitation_ladoifji894yfv_hao_quang" aria-hidden="true"></span>
                <span class="mayhen_invitation_ladoifji894yfv_sao_quay" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_sao_quay mayhen_invitation_ladoifji894yfv_s2" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_lap_lanh mayhen_invitation_ladoifji894yfv_l1" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_lap_lanh mayhen_invitation_ladoifji894yfv_l2" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_lap_lanh mayhen_invitation_ladoifji894yfv_l3" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_lap_lanh mayhen_invitation_ladoifji894yfv_l4" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_lap_lanh mayhen_invitation_ladoifji894yfv_l5" aria-hidden="true"><svg><use href="#mayhen_invitation_ladoifji894yfv_sr_sao"></use></svg></span>
                <div class="mayhen_invitation_ladoifji894yfv_ClientEditImage mayhen_invitation_ladoifji894yfv_img_ket mayhen_invitation_ladoifji894yfv_anh_tron">
                    <div class="mayhen_invitation_ladoifji894yfv_ladi_image">
                        <div class="mayhen_invitation_ladoifji894yfv_ladi_image_background" style="background-image: url('{{ $ketPhotoUrl }}');" data-sync-target="hero_photo_url"></div>
                    </div>
                </div>
                <span class="mayhen_invitation_ladoifji894yfv_vet_sang" aria-hidden="true"></span>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_cam_on mayhen_invitation_ladoifji894yfv_sr_ten mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <h2 class="mayhen_invitation_ladoifji894yfv_ladi_headline">Thank You</h2>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_loi_ket mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_paragraph">Sự hiện diện của Quý khách là niềm vinh hạnh cho gia đình chúng tôi.</p>
            </div>
            <div class="mayhen_invitation_ladoifji894yfv_ClientEditText mayhen_invitation_ladoifji894yfv_sr_ky mayhen_invitation_ladoifji894yfv_sr_hien" data-hieu-ung="len">
                <p class="mayhen_invitation_ladoifji894yfv_ladi_headline">
                    <span data-sync-target="groom_name">{{ $groomName }}</span> &amp; <span data-sync-target="bride_name">{{ $brideName }}</span>
                </p>
            </div>
        </section>

    </div>

    <!-- ===================== TRÌNH XEM ẢNH / PHÓNG TO QR (LIGHTBOX) ===================== -->
    <div class="mayhen_invitation_ladoifji894yfv_tmx_xem" role="dialog" aria-label="Xem ảnh phóng to">
        <div class="mayhen_invitation_ladoifji894yfv_tmx_dau">
            <span class="mayhen_invitation_ladoifji894yfv_tmx_so"></span>
            <button type="button" class="mayhen_invitation_ladoifji894yfv_tmx_dong" aria-label="Đóng">×</button>
        </div>
        <div class="mayhen_invitation_ladoifji894yfv_tmx_khung">
            <button type="button" class="mayhen_invitation_ladoifji894yfv_tmx_mui mayhen_invitation_ladoifji894yfv_trai" aria-label="Ảnh trước">‹</button>
            <img alt="Ảnh phóng to">
            <button type="button" class="mayhen_invitation_ladoifji894yfv_tmx_mui mayhen_invitation_ladoifji894yfv_phai" aria-label="Ảnh sau">›</button>
        </div>
        <div class="mayhen_invitation_ladoifji894yfv_tmx_chu_qr"></div>
        <div class="mayhen_invitation_ladoifji894yfv_tmx_ds"></div>
    </div>

    <!-- ===================== HỘP THÔNG BÁO SAO BĂNG (POPUP) ===================== -->
    <div class="mayhen_invitation_ladoifji894yfv_wb_popup_overlay" role="dialog" aria-modal="true">
        <div class="mayhen_invitation_ladoifji894yfv_wb_popup_box">
            <div class="mayhen_invitation_ladoifji894yfv_wb_popup_icon">
                <span class="mayhen_invitation_ladoifji894yfv_vong"></span>
                <span class="mayhen_invitation_ladoifji894yfv_sao"><svg viewBox="0 0 24 24"><path d="M12 0C12.6 8 16 11.4 24 12C16 12.6 12.6 16 12 24C11.4 16 8 12.6 0 12C8 11.4 11.4 8 12 0Z" fill="currentColor"/></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_nho mayhen_invitation_ladoifji894yfv_n1"><svg viewBox="0 0 24 24"><path d="M12 0C12.6 8 16 11.4 24 12C16 12.6 12.6 16 12 24C11.4 16 8 12.6 0 12C8 11.4 11.4 8 12 0Z" fill="currentColor"/></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_nho mayhen_invitation_ladoifji894yfv_n2"><svg viewBox="0 0 24 24"><path d="M12 0C12.6 8 16 11.4 24 12C16 12.6 12.6 16 12 24C11.4 16 8 12.6 0 12C8 11.4 11.4 8 12 0Z" fill="currentColor"/></svg></span>
                <span class="mayhen_invitation_ladoifji894yfv_nho mayhen_invitation_ladoifji894yfv_n3"><svg viewBox="0 0 24 24"><path d="M12 0C12.6 8 16 11.4 24 12C16 12.6 12.6 16 12 24C11.4 16 8 12.6 0 12C8 11.4 11.4 8 12 0Z" fill="currentColor"/></svg></span>
            </div>
            <h3 class="mayhen_invitation_ladoifji894yfv_wb_popup_title">Đã nhận lời hẹn</h3>
            <p class="mayhen_invitation_ladoifji894yfv_wb_popup_desc">Cảm ơn Quý khách đã xác nhận.<br>Hẹn gặp nhau dưới màn sao ngày vui!</p>
            <button type="button" class="mayhen_invitation_ladoifji894yfv_wb_popup_btn">Hẹn gặp bạn ✦</button>
        </div>
    </div>

    <!-- ===================== NHẠC NỀN & NÚT NHẠC NỔI ===================== -->
    <audio class="mayhen_invitation_ladoifji894yfv_audio_music" style="display: none;" loop src="{{ $assetBase }}/wedding-song.mp3"></audio>
    <div class="mayhen_invitation_ladoifji894yfv_wb_music_btn" role="button" tabindex="0" aria-label="Bật / Tắt nhạc nền">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55C7.79 13 6 14.79 6 17s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"></path>
        </svg>
    </div>
@endsection

@push('scripts')
    <script>
        window.__MAYHEN_IS_EMBED = {{ $isEmbed ? 'true' : 'false' }};
        window.__MAYHEN_WISH_SUBMIT_URL = "{{ $wishSubmitUrl }}";
        window.__MAYHEN_RSVP_SUBMIT_URL = "{{ $rsvpSubmitUrl }}";
    </script>
    <script src="{{ route('templates.asset', ['slug' => 'giua-muon-ngan-vi-sao', 'path' => 'script.js']) }}?v={{ $jsVersion }}"></script>
@endpush

