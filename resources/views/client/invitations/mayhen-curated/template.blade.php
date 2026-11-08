@php
    $assetBase = url('/mau-thiep/mayhen-curated/assets');
    $isEmbed = request()->boolean('embed');
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Mây Hẹn Curated (Thiết Kế Tự Phối)';

    // 1. Dữ liệu Cô dâu & Chú rể
    $groomName = $wedding->groom_name ?? 'Minh Tuấn';
    $brideName = $wedding->bride_name ?? 'Thu Hà';
    $extraData = $wedding->extra_data ?? [];
    if (! is_array($extraData)) {
        $extraData = json_decode((string) $extraData, true) ?? [];
    }

    $groomFather = $extraData['groom_father'] ?? ($wedding->groom_parents ?? 'Ông Nguyễn Văn Hùng');
    $groomMother = $extraData['groom_mother'] ?? 'Bà Lê Thị Thu';
    $groomParentsAddress = $extraData['groom_parents_address'] ?? 'Tư gia Nhà Trai - Đống Đa, Hà Nội';
    $groomBio = $extraData['groom_bio'] ?? 'Một chàng trai điềm đạm, chân thành và luôn sống trọn vẹn vì tình yêu thương gia đình.';

    $brideFather = $extraData['bride_father'] ?? ($wedding->bride_parents ?? 'Ông Trần Quốc Bảo');
    $brideMother = $extraData['bride_mother'] ?? 'Bà Phạm Thị Mai';
    $brideParentsAddress = $extraData['bride_parents_address'] ?? 'Tư gia Nhà Gái - Ba Đình, Hà Nội';
    $brideBio = $extraData['bride_bio'] ?? 'Một người con gái dịu dàng, tinh tế, luôn mang đến nụ cười ấm áp và bến đỗ bình yên.';

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
    $partyVenue = $wedding->venue_name ?? ($extraData['party_venue'] ?? 'Trung tâm Tiệc cưới Ngân Hà');
    $partyAddress = $wedding->venue_address ?? ($extraData['event_party_address'] ?? ($extraData['party_address'] ?? 'Số 88 Trần Duy Hưng, Cầu Giấy, Hà Nội'));
    $partyMapLink = 'https://maps.google.com/maps?q=' . urlencode($partyVenue . ', ' . $partyAddress) . '&t=&z=16&ie=UTF8&iwloc=&output=embed';

    // Lễ Gia Tiên
    $ceremonyTime = $extraData['ceremony_time'] ?? '08:00';
    $ceremonyAddress = $extraData['ceremony_address'] ?? 'Tại tư gia nhà gái';
    $ceremonyGroomTime = $extraData['ceremony_groom_time'] ?? '10:00';
    $ceremonyGroomAddress = $extraData['ceremony_groom_address'] ?? 'Tại tư gia nhà trai';
    $ceremonyType = $extraData['ceremony_type'] ?? 'Lễ Thành Hôn';

    // Khách mời
    $guestName = isset($guest) && $guest ? $guest->name : request()->query('guest', request()->query('name', $invitation->guest_name ?? 'Quý Khách'));
    $guestSalutation = isset($guest) && $guest ? $guest->salutation : 'Kính mời';

    // 3. Hàm chuẩn hóa asset ảnh an toàn
    $resolveAsset = function (?string $path, string $fallback = '') use ($assetBase): string {
        $val = trim((string) $path);
        if (str_starts_with($val, 'data:image/')) {
            return $val;
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

    $heroPhotoUrl = $resolveAsset($wedding->cover_photo_url ?? ($extraData['hero_photo_url'] ?? null), 'hero-couple.webp');
    $groomPhotoUrl = $resolveAsset($wedding->groom_photo_url ?? ($extraData['groom_photo_url'] ?? null), 'groom-portrait.webp');
    $bridePhotoUrl = $resolveAsset($wedding->bride_photo_url ?? ($extraData['bride_photo_url'] ?? null), 'bride-portrait.webp');
    $saveDatePhotoUrl = $resolveAsset($extraData['save_date_photo_url'] ?? null, 'story-couple.webp');

    // 4. Lời ngỏ & Chuyện tình yêu
    $storyTitle = $extraData['story_title'] ?? 'Câu Chuyện Tình Yêu';
    $storySubtitle = $extraData['story_subtitle'] ?? 'Hành Trình Gặp Gỡ & Kết Duyên';
    $coupleQuote = $extraData['couple_quote'] ?? 'Giữa muôn ngàn vì sao sáng trên bầu trời, em là ngôi sao duy nhất chiếu rọi trái tim anh.';
    $storyContent = $extraData['story_content'] ?? 'Có những cuộc gặp gỡ như đã được định sẵn từ muôn kiếp trước. Chúng mình đã cùng nhau đi qua những năm tháng thanh xuân, và hôm nay, chúng ta cùng viết tiếp chương mới rạng rỡ nhất.';

    if (! empty($personaMessage)) {
        $storyTitle = $personaMessage['title'] ?? $storyTitle;
        $storySubtitle = $personaMessage['subtitle'] ?? $storySubtitle;
        $storyContent = $personaMessage['content'] ?? $storyContent;
    }

    // 5. Cấu hình Bố Cục Tự Phối (Curated Layout) & Bảng Màu (Palette) (Mục 3, 4, 10)
    $customConfig = isset($invitation) && is_array($invitation->custom_config) 
        ? $invitation->custom_config 
        : (isset($invitation) ? json_decode((string) $invitation->custom_config, true) ?? [] : []);

    $curatedLayout = $customConfig['curated_layout'] ?? [];
    $palette = $curatedLayout['palette'] ?? ($customConfig['color_theme'] ?? 'ruby');
    $style = $curatedLayout['style'] ?? 'romantic';

    // Biến thể cho từng section trong 7 section (Mục 5, 10)
    $heroVariant = $curatedLayout['sections']['hero'] ?? 'variant_a';
    $storyVariant = $curatedLayout['sections']['story'] ?? 'variant_a';
    $parentsVariant = $curatedLayout['sections']['parents'] ?? 'variant_a';
    $countdownVariant = $curatedLayout['sections']['countdown'] ?? 'variant_a';
    $eventsVariant = $curatedLayout['sections']['events'] ?? 'variant_a';
    $timelineVariant = $curatedLayout['sections']['timeline'] ?? 'variant_a';
    $dresscodeVariant = $curatedLayout['sections']['dresscode'] ?? 'variant_a';

    $showVariantControls = request()->boolean('curated_controls') || request()->routeIs('curated.preview');
    $isCraftingMode = request()->boolean('crafting');

    $pageTitle = $isPreview 
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name') 
        : 'Thiệp cưới: "' . $groomName . ' & ' . $brideName . '" | ' . config('app.name');
@endphp

@extends('client.layouts.invitation')

@section('title', $pageTitle)

@push('styles')
    <link rel="stylesheet" href="{{ $assetBase }}/../styles.css?v={{ time() }}">
@endpush

@section('content')
<div class="mayhen_invitation_ladi_wraper mayhen_curated_theme--{{ $palette }} {{ $isEmbed ? 'is-embed' : '' }} {{ $isCraftingMode ? 'is-crafting-live' : '' }}" 
     id="mayhenCuratedRoot"
     data-curated-palette="{{ $palette }}" 
     data-curated-style="{{ $style }}"
     data-curated-invitation="1">

    {{-- G.2 Nút Đĩa Than Bật/Tắt Nhạc --}}
    <div class="mayhen_invitation_music_dock" id="musicDock" title="Bật/Tắt Nhạc Nền">
        <button type="button" class="mayhen_invitation_music_btn" id="musicBtn" aria-label="Bật/Tắt nhạc nền">
            <img src="{{ $assetBase }}/music-disc.svg" alt="Music" class="mayhen_invitation_disc_icon">
        </button>
        <audio id="bgAudio" class="mayhen_invitation_audio" loop preload="none" data-sync-target="music_url">
            <source src="{{ $wedding->music_url ?? ($assetBase . '/audio.mp3') }}" type="audio/mpeg">
        </audio>
    </div>

    {{-- G.4 Màn Mở Đầu (Opening Envelope Gate) --}}
    @if(! $isEmbed && ! $isCraftingMode)
        <div class="mayhen_invitation_opening" id="invitationOpening" data-mayhen-section="opening">
            <div class="mayhen_invitation_envelope_wrapper">
                <div class="mayhen_invitation_envelope_inner">
                    <div class="mayhen_invitation_envelope_wax">
                        <img src="{{ $assetBase }}/wax-seal.svg" alt="Seal" class="mayhen_invitation_wax_seal_pulse">
                    </div>
                    <div class="mayhen_invitation_opening_kicker">MÂY HẸN &bull; THIỆP MỜI TRÂN QUÝ</div>
                    <h2 class="mayhen_invitation_opening_couple">{{ $groomName }} &amp; {{ $brideName }}</h2>
                    <p class="mayhen_invitation_opening_date">{{ $weddingDateDigits }}</p>
                    <button type="button" class="mayhen_invitation_open_btn" id="openInvitationBtn">
                        CHẠM ĐỂ MỞ THIỆP
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- CẤU TRÚC ĐÚNG 7 SECTION THEO CHUẨN MÂY HẸN (MỤC 5) --}}
    <div class="mayhen_curated_sections_flow" id="curatedSectionsFlow">
        
        {{-- SECTION 1: Trang bìa chính (Opening & Hero Cover) --}}
        @include("client.invitations.mayhen-curated.sections.hero.{$heroVariant}")

        <div class="mayhen_invitation_divider" aria-hidden="true">
            <span>✦</span>
        </div>

        {{-- SECTION 2: Lời ngỏ & Chuyện tình yêu (Love Story & Quote) --}}
        @include("client.invitations.mayhen-curated.sections.story.{$storyVariant}")

        <div class="mayhen_invitation_divider" aria-hidden="true">
            <span>✦</span>
        </div>

        {{-- SECTION 3: Song thân & Đôi uyên ương (Family & Couple) --}}
        @include("client.invitations.mayhen-curated.sections.parents.{$parentsVariant}")

        <div class="mayhen_invitation_divider" aria-hidden="true">
            <span>✦</span>
        </div>

        {{-- SECTION 4: Đếm ngược & Lịch tháng (Countdown & Calendar) --}}
        @include("client.invitations.mayhen-curated.sections.countdown.{$countdownVariant}")

        <div class="mayhen_invitation_divider" aria-hidden="true">
            <span>✦</span>
        </div>

        {{-- SECTION 5: Thông tin sự kiện & Bản đồ (Events & Maps) --}}
        @include("client.invitations.mayhen-curated.sections.events.{$eventsVariant}")

        <div class="mayhen_invitation_divider" aria-hidden="true">
            <span>✦</span>
        </div>

        {{-- SECTION 6: Lịch trình hôn lễ (Wedding Timeline) --}}
        @include("client.invitations.mayhen-curated.sections.timeline.{$timelineVariant}")

        <div class="mayhen_invitation_divider" aria-hidden="true">
            <span>✦</span>
        </div>

        {{-- SECTION 7: Quy định trang phục (Dresscode) --}}
        @include("client.invitations.mayhen-curated.sections.dresscode.{$dresscodeVariant}")

        {{-- Kết thiệp nhẹ nhàng --}}
        <footer class="mayhen_curated_footer">
            <p>Trân trọng cảm ơn tình cảm và sự hiện diện của Quý khách!</p>
            <div class="mayhen_curated_footer_couple">{{ $groomName }} &amp; {{ $brideName }}</div>
        </footer>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ $assetBase }}/../script.js?v={{ time() }}"></script>
@endpush
