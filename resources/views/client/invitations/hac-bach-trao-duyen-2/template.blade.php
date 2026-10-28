@php
    $assetBase = url('/mau-thiep/hac-bach-trao-duyen-2/assets');
    $isEmbed = request()->boolean('embed');

    // 1. Tên cô dâu chú rể & Gia đình
    $groomName = $wedding->groom_name ?? 'Randy';
    $brideName = $wedding->bride_name ?? 'Minh Quý';
    $extraData = $wedding->extra_data ?? [];

    $parseParents = function ($text, $defaultFather, $defaultMother) {
        $lines = preg_split('/[\r\n&]+/u', trim((string) $text));
        $father = $defaultFather;
        $mother = $defaultMother;
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(?:ÔNG|Ông|Bố|Cha)[:\s-]*(.*)$/ui', $line, $m)) {
                $father = trim($m[1]) ?: $father;
            } elseif (preg_match('/^(?:BÀ|Bà|Mẹ)[:\s-]*(.*)$/ui', $line, $m)) {
                $mother = trim($m[1]) ?: $mother;
            }
        }
        return [$father, $mother];
    };
    [$legacyGroomFather, $legacyGroomMother] = $parseParents(
        $wedding->groom_parents ?? '',
        'Trần Văn Hùng',
        'Lê Thị Mai',
    );
    [$legacyBrideFather, $legacyBrideMother] = $parseParents(
        $wedding->bride_parents ?? '',
        'Nguyễn Văn Nam',
        'Vũ Thị Lan',
    );

    $groomFather = !empty($extraData['groom_father']) ? $extraData['groom_father'] : $legacyGroomFather;
    $groomMother = !empty($extraData['groom_mother']) ? $extraData['groom_mother'] : $legacyGroomMother;
    $brideFather = !empty($extraData['bride_father']) ? $extraData['bride_father'] : $legacyBrideFather;
    $brideMother = !empty($extraData['bride_mother']) ? $extraData['bride_mother'] : $legacyBrideMother;

    // 2. Ngày cưới
    $weddingDate = isset($wedding->wedding_date)
        ? \Carbon\Carbon::parse($wedding->wedding_date)
        : \Carbon\Carbon::create(2025, 10, 9, 16, 0, 0);
    $targetTimestamp = $weddingDate->timestamp * 1000;
    $calendarMonth = $weddingDate->format('m');
    $calendarYear = $weddingDate->format('Y');
    $calendarDay = $weddingDate->day;

    // Sự kiện Lễ Cưới & Tiệc Cưới
    $eventPartyTime = $extraData['event_party_time'] ?? '16:00';
    $eventPartyDate = $extraData['event_party_date'] ?? '09/10/2025';
    $eventPartyLunar = $extraData['event_party_lunar'] ?? '(Tức ngày 18 tháng 08 năm Ất Tỵ)';
    $eventPartyAddress =
        $extraData['event_party_address'] ?? 'Trung Tâm Hội Nghị Tiệc Cưới Luxury Palace, TP. Hồ Chí Minh';
    $eventPartyMap = $extraData['event_party_map'] ?? 'https://maps.google.com/?q=' . urlencode($eventPartyAddress);

    // 3. Hàm resolve asset chuẩn của Mây Hẹn
    $resolveAsset = function (?string $path, string $fallback = '') use ($assetBase): string {
        $val = trim((string) $path);
        if (str_starts_with($val, 'blob:')) {
            $val = '';
        }
        if (empty($val)) {
            $fb = trim($fallback);
            if (empty($fb)) {
                return '';
            }
            return str_starts_with($fb, 'http://') || str_starts_with($fb, 'https://') || str_starts_with($fb, '/')
                ? $fb
                : $assetBase . '/' . ltrim($fb, '/');
        }
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://') || str_starts_with($val, '/')) {
            return $val;
        }
        return $assetBase . '/' . ltrim($val, '/');
    };

    // 4. Lời chúc & RSVP URLs
    $wishes = $wishes ?? collect([]);
    $wishSubmitUrl =
        $wishSubmitUrl ??
        (isset($invitation)
            ? route('invitation.wish.store', $invitation->slug)
            : route('templates.preview.wish', 'hac-bach-trao-duyen-2'));
    $rsvpSubmitUrl =
        $rsvpSubmitUrl ??
        (isset($invitation)
            ? route('invitation.rsvp.store', $invitation->slug)
            : route('templates.preview.rsvp', 'hac-bach-trao-duyen-2'));

    // Cache busting
    $cssPath = resource_path('views/client/invitations/hac-bach-trao-duyen-2/styles.css');
    $jsPath = resource_path('views/client/invitations/hac-bach-trao-duyen-2/script.js');
    $cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
    $jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();

    // Chuẩn hóa tiêu đề trang cho tất cả các mẫu thiệp
    $isPreview = request()->routeIs('templates.preview') || (!isset($wedding) && isset($template));
    $templateName = $template->name ?? 'Hắc Bạch Trao Duyên 2';
    $pageTitle = $isPreview
        ? 'Xem thử: "' . $templateName . '" | ' . config('app.name')
        : 'Thiệp mời cưới: "' . $groomName . ' & ' . $brideName . '" | ' . config('app.name');
@endphp
@extends('client.layouts.invitation')

@section('html_class', 'mayhen_invitation_ladjihe38udhd_scroll_locked')
@section('title', $pageTitle)
@section('body_class', 'mayhen_invitation_ladjihe38udhd_body mayhen_invitation_ladjihe38udhd_scroll_locked')
@section('body_attributes', 'data-target-date=' . $targetTimestamp)

@push('fonts')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Philosopher:wght@400;700&display=swap" rel="stylesheet">
@endpush

@push('styles')
    <link rel="stylesheet"
        href="{{ route('templates.asset', ['slug' => 'hac-bach-trao-duyen-2', 'path' => 'styles.css']) }}?v={{ $cssVersion }}">
    @if ($isEmbed)
        <style>
            html,
            body {
                overflow-x: hidden !important;
                -webkit-overflow-scrolling: touch !important;
            }

            .mayhen_invitation_ladjihe38udhd_box_anim {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        </style>
    @endif
@endpush

@section('content')

    <!-- Audio Player (Hidden) -->
    <audio id="mayhen_invitation_ladjihe38udhd_audio" loop preload="auto">
        <source src="{{ route('templates.asset', ['slug' => 'hac-bach-trao-duyen-2', 'path' => 'wedding-song.mp3']) }}"
            type="audio/mpeg">
    </audio>

    <!-- Floating Audio Controller (Đĩa than Vinyl & Equalizer) -->
    <aside class="mayhen_invitation_ladjihe38udhd_music_dock" aria-label="Điều khiển âm nhạc">
        <div class="mayhen_invitation_ladjihe38udhd_vinyl_wrap" id="mayhen_invitation_ladjihe38udhd_vinyl_btn"
            title="Bật/Tắt nhạc">
            <div class="mayhen_invitation_ladjihe38udhd_vinyl_center">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z" />
                </svg>
            </div>
        </div>
        <div class="mayhen_invitation_ladjihe38udhd_soundwave">
            <span></span><span></span><span></span><span></span>
        </div>
    </aside>

    <!-- ROYAL WEDDING INVITATION ENVELOPE (POPUP8) -->
    <div id="POPUP8" class="ladi-element ">
        <div class='ladi-popup'>
            <div class="ladi-popup-background"></div>
            <div id="GROUP149" class="ladi-element ">
                <div class='ladi-group'>
                    <div id="IMAGE159" class="ladi-element ">
                        <div class='ladi-image'>
                            <div class="ladi-image-background"></div>
                        </div>
                    </div>
                    <div id="IMAGE158" class="ladi-element ">
                        <div class='ladi-image'>
                            <div class="ladi-image-background"></div>
                        </div>
                    </div>
                    <div data-action="true" id="BUTTON18" class="ladi-element off_speaker ">
                        <div class='ladi-button'>
                            <div class="ladi-button-background"></div>
                            <div id="BUTTON_TEXT18" class="ladi-element ladi-button-headline ">
                                <p class='ladi-headline'>Ấn vào để Xem</p>
                            </div>
                        </div>
                    </div>
                    <div id="GROUP145" class="ladi-element ">
                        <div class='ladi-group'>
                            <div id="BOX79" class="ladi-element circle_image ">
                                <div class='ladi-box ladi-transition'></div>
                            </div>
                            <div id="BOX78" class="ladi-element ">
                                <div class='ladi-box ladi-transition'></div>
                            </div>
                            <div id="IMAGE157" class="ladi-element ">
                                <div class='ladi-image ladi-transition'>
                                    <div class="ladi-image-background" data-sync-target="cover_photo_url"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="GROUP140" class="ladi-element ">
                        <div class='ladi-group'>
                            <div id="BOX77" class="ladi-element ">
                                <div class='ladi-box ladi-transition'></div>
                            </div>
                            <div id="HEADLINE335" class="ladi-element ">
                                <h3 class='ladi-headline'>Thân gửi</h3>
                            </div>
                            <div id="HEADLINE336" class="ladi-element class_tenKhachMoi ">
                                <h3 class='ladi-headline ladi-transition'>Bạn và Gia Đình<br></h3>
                            </div>
                        </div>
                    </div>
                    <div id="HEADLINE338" class="ladi-element ">
                        <h3 class='ladi-headline ladi-transition' data-sync-target="couple_names"
                            data-sync-transform="uppercase">{{ mb_strtoupper($groomName) }} &amp;
                            {{ mb_strtoupper($brideName) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="ladi-wraper">
        <div id="SECTION28" class="ladi-section" style="display:none !important; height:0 !important;">
            <div class='ladi-section-background'></div>
            <div class="ladi-container"></div>
        </div>
        <div id="SECTION1" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="IMAGE1" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background" data-sync-target="hero_photo_url"></div>
                    </div>
                </div>
                <div id="BOX43" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-box ladi-transition'></div>
                </div>
                <div id="PARAGRAPH43" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph'>You are invited to <br>the wedding celebration of</div>
                </div>
                <div id="IMAGE80" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE76" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE85" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE82" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE87" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="GROUP94" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="HEADLINE3" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition' data-sync-target="groom_name"
                                data-sync-transform="uppercase">{{ mb_strtoupper($groomName) }}</h3>
                        </div>
                        <div id="HEADLINE282" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>&amp;<br></h3>
                        </div>
                        <div id="HEADLINE283" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition' data-sync-target="bride_name"
                                data-sync-transform="uppercase">{{ mb_strtoupper($brideName) }}<br></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="SECTION22" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="IMAGE95" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE94" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE88" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="GROUP71" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="HEADLINE184" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>Save&nbsp;<br></h3>
                        </div>
                        <div id="HEADLINE185" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>Date</h3>
                        </div>
                        <div id="HEADLINE186" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>the</h3>
                        </div>
                    </div>
                </div>
                <div id="PARAGRAPH45" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph'>Together, we found home</div>
                </div>
                <div id="GROUP89" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="HEADLINE276" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>09</h3>
                        </div>
                        <div id="BOX51" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="HEADLINE277" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>Chủ Nhật</h3>
                        </div>
                        <div id="HEADLINE278" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>16:00</h3>
                        </div>
                        <div id="HEADLINE279" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>Tháng mười</h3>
                        </div>
                        <div id="HEADLINE280" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>2025</h3>
                        </div>
                    </div>
                </div>
                <div id="GROUP95" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="HEADLINE284" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>r</h3>
                        </div>
                        <div id="HEADLINE285" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>&amp;<br></h3>
                        </div>
                        <div id="HEADLINE286" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>q</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="SECTION24" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="GROUP80" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="BOX50" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="GROUP81" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="HEADLINE231" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>sun</h3>
                                </div>
                                <div id="HEADLINE232" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>mon</h3>
                                </div>
                                <div id="HEADLINE233" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>tue</h3>
                                </div>
                                <div id="HEADLINE234" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>wed</h3>
                                </div>
                                <div id="HEADLINE235" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>thur</h3>
                                </div>
                                <div id="HEADLINE236" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>fri</h3>
                                </div>
                                <div id="HEADLINE237" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>sat</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="GROUP82" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="GROUP83" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="HEADLINE238" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'></h3>
                                </div>
                                <div id="HEADLINE239" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'></h3>
                                </div>
                                <div id="HEADLINE240" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'></h3>
                                </div>
                                <div id="HEADLINE241" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'></h3>
                                </div>
                                <div id="HEADLINE242" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>1</h3>
                                </div>
                                <div id="HEADLINE243" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>2</h3>
                                </div>
                                <div id="HEADLINE244" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>3</h3>
                                </div>
                            </div>
                        </div>
                        <div id="GROUP84" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="HEADLINE245" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>4</h3>
                                </div>
                                <div id="HEADLINE246" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>5</h3>
                                </div>
                                <div id="HEADLINE247" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>6</h3>
                                </div>
                                <div id="HEADLINE248" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>7</h3>
                                </div>
                                <div id="HEADLINE249" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>8</h3>
                                </div>
                                <div id="HEADLINE250" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>9</h3>
                                </div>
                                <div id="HEADLINE251" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>10</h3>
                                </div>
                            </div>
                        </div>
                        <div id="GROUP85" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="HEADLINE252" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>11</h3>
                                </div>
                                <div id="HEADLINE253" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>12</h3>
                                </div>
                                <div id="HEADLINE254" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>13</h3>
                                </div>
                                <div id="HEADLINE255" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>14</h3>
                                </div>
                                <div id="HEADLINE256" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>15</h3>
                                </div>
                                <div id="HEADLINE257" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>16</h3>
                                </div>
                                <div id="HEADLINE258" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>17</h3>
                                </div>
                            </div>
                        </div>
                        <div id="GROUP86" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="HEADLINE259" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>18</h3>
                                </div>
                                <div id="HEADLINE260" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>19</h3>
                                </div>
                                <div id="HEADLINE261" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>20</h3>
                                </div>
                                <div id="HEADLINE262" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>21</h3>
                                </div>
                                <div id="HEADLINE263" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>22</h3>
                                </div>
                                <div id="HEADLINE264" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>23</h3>
                                </div>
                                <div id="HEADLINE265" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>24</h3>
                                </div>
                            </div>
                        </div>
                        <div id="GROUP87" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="HEADLINE266" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>25</h3>
                                </div>
                                <div id="HEADLINE267" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>26</h3>
                                </div>
                                <div id="HEADLINE268" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>27</h3>
                                </div>
                                <div id="HEADLINE269" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>28</h3>
                                </div>
                                <div id="HEADLINE270" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>29</h3>
                                </div>
                                <div id="HEADLINE271" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>30</h3>
                                </div>
                                <div id="HEADLINE272" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>31</h3>
                                </div>
                            </div>
                        </div>
                        <div id="LINE67" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE68" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE69" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE70" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE71" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE72" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE73" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE74" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE75" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE76" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE77" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE78" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE79" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="LINE80" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="HEADLINE273" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline ladi-transition'>Tháng Mười</h3>
                </div>
                <div id="HEADLINE274" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline ladi-transition'>2025</h3>
                </div>
                <div id="SHAPE14" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-shape ladi-transition'><svg xmlns="http://www.w3.org/2000/svg" height="100%"
                            viewBox="0 -960 960 960" width="100%" preserveAspectRatio="none" class=""
                            fill="url(&quot;#SHAPE14_desktop_gradient&quot;)">
                            <defs id="SHAPE14_defs">
                                <linearGradient id="SHAPE14_desktop_gradient" gradientTransform="rotate(0)">
                                    <stop offset="0%" stop-color="rgba(255, 255, 255, 1)"></stop>
                                    <stop offset="100%" stop-color="rgba(255, 255, 255, 1)"></stop>
                                </linearGradient>
                            </defs>
                            <path
                                d="m480-121-41-37q-105.77-97.12-174.88-167.56Q195-396 154-451.5T96.5-552Q80-597 80-643q0-90.15 60.5-150.58Q201-854 290-854q57 0 105.5 27t84.5 78q42-54 89-79.5T670-854q89 0 149.5 60.42Q880-733.15 880-643q0 46-16.5 91T806-451.5Q765-396 695.88-325.56 626.77-255.12 521-158l-41 37Zm0-79q101.24-93 166.62-159.5Q712-426 750.5-476t54-89.14q15.5-39.13 15.5-77.72 0-66.14-42-108.64T670.22-794q-51.52 0-95.37 31.5T504-674h-49q-26-56-69.85-88-43.85-32-95.37-32Q224-794 182-751.5t-42 108.82q0 38.68 15.5 78.18 15.5 39.5 54 90T314-358q66 66 166 158Zm0-297Z">
                            </path>
                        </svg></div>
                </div><a
                    href="https://calendar.google.com/calendar/event?action=TEMPLATE&tmeid=MXBubmE5Y2QyNmFxZm9oOGxrNWI0c29qNXAgMjdiaWdncm91cEBt&tmsrc=27biggroup%40gmail.com"
                    target="_blank" id="BUTTON10" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-button'>
                        <div class="ladi-button-background"></div>
                        <div id="BUTTON_SHAPE10"
                            class="ladi-element ladi-button-shape mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-shape'><svg xmlns="http://www.w3.org/2000/svg" width="100%"
                                    height="100%" preserveAspectRatio="none" viewBox="0 0 1672.2646 1896.0833"
                                    class="" fill="rgba(255, 251, 247, 1)">
                                    <path
                                        d="M128 1664h288v-288H128v288zm352 0h320v-288H480v288zm-352-352h288V992H128v320zm352 0h320V992H480v320zM128 928h288V640H128v288zm736 736h320v-288H864v288zM480 928h320V640H480v288zm768 736h288v-288h-288v288zm-384-352h320V992H864v320zM512 448V160q0-13-9.5-22.5T480 128h-64q-13 0-22.5 9.5T384 160v288q0 13 9.5 22.5T416 480h64q13 0 22.5-9.5T512 448zm736 864h288V992h-288v320zM864 928h320V640H864v288zm384 0h288V640h-288v288zm32-480V160q0-13-9.5-22.5T1248 128h-64q-13 0-22.5 9.5T1152 160v288q0 13 9.5 22.5t22.5 9.5h64q13 0 22.5-9.5t9.5-22.5zm384-64v1280q0 52-38 90t-90 38H128q-52 0-90-38t-38-90V384q0-52 38-90t90-38h128v-96q0-66 47-113T416 0h64q66 0 113 47t47 113v96h384v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h128q52 0 90 38t38 90z">
                                    </path>
                                </svg></div>
                        </div>
                        <div id="BUTTON_TEXT10"
                            class="ladi-element ladi-button-headline mayhen_invitation_ladjihe38udhd_box_anim">
                            <p class='ladi-headline'>&nbsp; &nbsp; &nbsp;Thêm vào lịch</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div id="SECTION6" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="GROUP9" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="BOX5" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="BOX6" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="BOX7" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="BOX8" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                    </div>
                </div>
                <div id="COUNTDOWN1" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-countdown'>
                        <div id="COUNTDOWN_ITEM1" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class="ladi-countdown-background"></div>
                            <div class='ladi-countdown-text'><span>00</span></div>
                        </div>
                        <div id="COUNTDOWN_ITEM2" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class="ladi-countdown-background"></div>
                            <div class='ladi-countdown-text'><span>00</span></div>
                        </div>
                        <div id="COUNTDOWN_ITEM3" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class="ladi-countdown-background"></div>
                            <div class='ladi-countdown-text'><span>00</span></div>
                        </div>
                        <div id="COUNTDOWN_ITEM4" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class="ladi-countdown-background"></div>
                            <div class='ladi-countdown-text'><span>00</span></div>
                        </div>
                    </div>
                </div>
                <div id="IMAGE4" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE5" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE6" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="HEADLINE287" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline'>:<br></h3>
                </div>
                <div id="HEADLINE288" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline'>:<br></h3>
                </div>
                <div id="HEADLINE289" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline'>:<br></h3>
                </div>
                <div id="GROUP96" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="PARAGRAPH9" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-paragraph ladi-transition'>THỜI GIAN ĐẾM NGƯỢC<br></div>
                        </div>
                        <div id="PARAGRAPH53" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-paragraph ladi-transition'>Countdown time<br></div>
                        </div>
                    </div>
                </div>
                <div id="GROUP97" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="HEADLINE291" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>Ngày<br></h3>
                        </div>
                        <div id="HEADLINE292" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>Giờ<br></h3>
                        </div>
                        <div id="HEADLINE293" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>Phút<br></h3>
                        </div>
                        <div id="HEADLINE294" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>Giây<br></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="SECTION4" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="IMAGE117" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE118" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="GROUP125" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="GROUP116" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="IMAGE115" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-image ladi-transition'>
                                        <div class="ladi-image-background" data-sync-target="groom_photo_url"></div>
                                    </div>
                                </div>
                                <div id="BOX60" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-box ladi-transition'></div>
                                </div>
                                <div id="PARAGRAPH66" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-paragraph ladi-transition'>The groom</div>
                                </div>
                                <div id="HEADLINE300" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition' data-sync-target="groom_name">
                                        {{ $groomName }}<br></h3>
                                </div>
                            </div>
                        </div>
                        <div id="PARAGRAPH70" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-paragraph ladi-transition'>Chú rể là một chàng trai điềm đạm, chân thành và
                                luôn sống hết lòng vì gia đình và những người thân yêu. Với trái tim ấm áp và sự chín
                                chắn trong suy nghĩ, anh đã và đang là chỗ dựa vững chắc cho cô dâu cũng như là người
                                bạn đồng hành lý tưởng trong chặng đường hôn nhân sắp tới.<br></div>
                            <div class="mayhen_invitation_ladjihe38udhd_parents_box"
                                style="margin-top: 10px; font-size: 13px; color: #78716c;"
                                data-sync-target="groom_parents"><strong>Nhà Trai:</strong> Ông <span
                                    data-sync-target="groom_father">{{ $groomFather }}</span> &amp; Bà <span
                                    data-sync-target="groom_mother">{{ $groomMother }}</span></div>
                        </div>
                    </div>
                </div>
                <div id="GROUP126" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="GROUP127" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="IMAGE122" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-image ladi-transition'>
                                        <div class="ladi-image-background" data-sync-target="bride_photo_url"></div>
                                    </div>
                                </div>
                                <div id="BOX67" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-box ladi-transition'></div>
                                </div>
                                <div id="PARAGRAPH75" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-paragraph ladi-transition'>The bride</div>
                                </div>
                                <div id="HEADLINE304" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition' data-sync-target="bride_name">
                                        {{ $brideName }}<br></h3>
                                </div>
                            </div>
                        </div>
                        <div id="PARAGRAPH76" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-paragraph ladi-transition'>Cô dâu là một người con gái dịu dàng, tinh tế và
                                luôn mang đến năng lượng tích cực cho những người xung quanh. Với nụ cười ấm áp và trái
                                tim chân thành, cô không chỉ là niềm tự hào của gia đình mà còn là người bạn đời lý
                                tưởng, đồng hành cùng chú rể trong hành trình xây dựng hạnh phúc.<br></div>
                            <div class="mayhen_invitation_ladjihe38udhd_parents_box"
                                style="margin-top: 10px; font-size: 13px; color: #78716c;"
                                data-sync-target="bride_parents"><strong>Nhà Gái:</strong> Ông <span
                                    data-sync-target="bride_father">{{ $brideFather }}</span> &amp; Bà <span
                                    data-sync-target="bride_mother">{{ $brideMother }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="SECTION13" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="IMAGE46" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE44" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="BOX21" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-box ladi-transition'></div>
                </div>
                <div id="IMAGE47" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="HEADLINE68" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline ladi-transition'>Rất hân hạnh được đón tiếp!<br></h3>
                </div>
                <div id="GROUP39" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="BOX24" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="BOX25" class="ladi-element circle_image mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="IMAGE51" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image ladi-transition'>
                                <div class="ladi-image-background"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="HEADLINE69" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline ladi-transition' data-sync-target="groom_name">{{ $groomName }}</h3>
                </div>
                <div id="HEADLINE70" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline ladi-transition' data-sync-target="bride_name">{{ $brideName }}</h3>
                </div>
                <div id="HEADLINE71" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline ladi-transition'>and</h3>
                </div>
                <div id="PARAGRAPH23" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'><span style="font-size: 18px;"
                            data-sync-target="event_party_time">VÀO LÚC {{ $eventPartyTime }}
                            <br></span><span
                            data-sync-target="wedding_date">{{ mb_strtoupper($weddingDate->translatedFormat('l, \n\g\à\y d \t\h\á\n\g m \n\ă\m Y')) }}</span><br>
                    </div>
                </div>
                <div id="PARAGRAPH24" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'><span style="font-weight: 400;"
                            data-sync-target="event_party_address">{{ $eventPartyAddress }}</span><br></div>
                </div>
                <div id="PARAGRAPH25" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition' data-sync-target="event_party_lunar">
                        {{ $eventPartyLunar }}</div>
                </div>
                <div data-action="true" id="BUTTON9" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-button ladi-transition'>
                        <div class="ladi-button-background"></div>
                        <div id="BUTTON_TEXT9"
                            class="ladi-element ladi-button-headline mayhen_invitation_ladjihe38udhd_box_anim">
                            <p class='ladi-headline ladi-transition'>CHỈ ĐƯỜNG/ DIRECTIONS</p>
                        </div>
                    </div>
                </div>
                <div id="IMAGE100" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE101" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="PARAGRAPH59" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'>Tới dự lễ cưới thân mật của chúng tôi<br></div>
                </div>
                <div id="HEADLINE66" class="ladi-element class_tenKhachMoi mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline ladi-transition'>Bạn và Gia Đình<br></h3>
                </div>
                <div id="HEADLINE67" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline ladi-transition'>TRÂN TRỌNG KÍNH MỜI</h3>
                </div>
            </div>
        </div>
        <div id="SECTION16" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="IMAGE99" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="BOX34" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-box ladi-transition'></div>
                </div>
                <div id="HEADLINE72" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline ladi-transition'>Timeline</h3>
                </div>
                <div id="IMAGE55" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE98" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="GROUP142" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="LINE16" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="PARAGRAPH30" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-paragraph ladi-transition'>Checkin với photobooth và gửi lời chúc đến CDCR
                            </div>
                        </div>
                        <div id="HEADLINE73" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>16:00<br></h3>
                        </div>
                        <div id="IMAGE53" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image'>
                                <div class="ladi-image-background"></div>
                            </div>
                        </div>
                        <div id="HEADLINE341" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>Đón Khách<br></h3>
                        </div>
                    </div>
                </div>
                <div id="GROUP143" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="LINE81" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="PARAGRAPH81" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-paragraph ladi-transition'>Chứng kiến khoảnh khắc thiêng liêng cùng CDCR
                            </div>
                        </div>
                        <div id="HEADLINE342" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>16:30<br></h3>
                        </div>
                        <div id="IMAGE155" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image'>
                                <div class="ladi-image-background"></div>
                            </div>
                        </div>
                        <div id="HEADLINE343" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>Nghi lễ<br></h3>
                        </div>
                    </div>
                </div>
                <div id="GROUP144" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="LINE82" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-line'>
                                <div class="ladi-line-container"></div>
                            </div>
                        </div>
                        <div id="PARAGRAPH82" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-paragraph ladi-transition'>Cùng nhau khai tiệc và nâng ly chúc mừng CDCR
                            </div>
                        </div>
                        <div id="HEADLINE344" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>18:30<br></h3>
                        </div>
                        <div id="IMAGE156" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image'>
                                <div class="ladi-image-background"></div>
                            </div>
                        </div>
                        <div id="HEADLINE345" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>Nhập tiệc<br></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="SECTION19" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="HEADLINE173" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline ladi-transition'>Dresscode<br></h3>
                </div>
                <div id="GROUP91" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="BOX37" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="BOX38" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="BOX39" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="BOX40" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="BOX41" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                    </div>
                </div>
                <div id="PARAGRAPH54" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph'>Chúng tôi rất mong quý khách lựa chọn trang phục theo các tông màu dưới
                        đây để cùng hòa vào không khí buổi tiệc.</div>
                </div>
            </div>
        </div>
        <div id="SECTION7" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="IMAGE106" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE105" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="GROUP11" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="BOX9" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="IMAGE7" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image ladi-transition'>
                                <div class="ladi-image-background"></div>
                            </div>
                        </div>
                        <div id="GROUP12" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="HEADLINE12" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition' data-sync-target="bride_name">
                                        {{ $brideName }}<br></h3>
                                </div>
                                <div id="HEADLINE13" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition' data-sync-target="groom_name">
                                        {{ $groomName }}<br></h3>
                                </div>
                                <div id="HEADLINE14" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline ladi-transition'>&amp;<br></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="HTML_CODE1" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-html-code'>
                        <div id="previewContainer" class="mayhen_hacbach2_signature_wall">
                            <div class="mayhen_hacbach2_wall_header">
                                <span class="wall_icon">✒️</span>
                                <h4>Sổ Lưu Bút Kỷ Niệm</h4>
                                <p>Những nét chữ chúc phúc quý giá của bạn bè &amp; người thân</p>
                            </div>
                            <div id="mayhen_hacbach2_signature_list" class="mayhen_hacbach2_signature_list"></div>
                        </div>

                        <div id="deleteDialog" class="mayhen_hacbach2_dialog" hidden>
                            <div class="dialog_backdrop"></div>
                            <div class="dialog_card">
                                <div class="dialog_icon">⚠️</div>
                                <p>Bạn có chắc chắn muốn xóa nét vẽ chữ ký này để ký lại?</p>
                                <div id="deleteDialog_group_button" class="dialog_actions">
                                    <button id="cancelDelete" type="button" class="btn_cancel">Hủy</button>
                                    <button id="confirmDelete" type="button" class="btn_confirm">Xóa nét vẽ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-action="true" id="BUTTON2" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-button ladi-transition'>
                        <div class="ladi-button-background"></div>
                        <div id="BUTTON_TEXT2"
                            class="ladi-element ladi-button-headline mayhen_invitation_ladjihe38udhd_box_anim">
                            <p class='ladi-headline ladi-transition'>NHẤN ĐỂ KÝ TÊN<br></p>
                        </div>
                    </div>
                </div>
                <div id="GROUP13" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="BOX10" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="HEADLINE15" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>Khu vực ký tên<br></h3>
                        </div>
                        <div id="HTML_CODE2" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-html-code'>
                                <div id="wrapCanvas">
                                    <canvas id="signatureCanvas" width="400" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div id="GROUP14" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="BUTTON3"
                                    class="ladi-element Signature_BTN_xoa mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-button ladi-transition'>
                                        <div class="ladi-button-background"></div>
                                        <div id="BUTTON_TEXT3"
                                            class="ladi-element ladi-button-headline mayhen_invitation_ladjihe38udhd_box_anim">
                                            <p class='ladi-headline ladi-transition'>Xóa</p>
                                        </div>
                                    </div>
                                </div>
                                <div id="BUTTON4"
                                    class="ladi-element Signature_BTN_xem mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-button ladi-transition'>
                                        <div class="ladi-button-background"></div>
                                        <div id="BUTTON_TEXT4"
                                            class="ladi-element ladi-button-headline mayhen_invitation_ladjihe38udhd_box_anim">
                                            <p class='ladi-headline ladi-transition'>Xem thử</p>
                                        </div>
                                    </div>
                                </div>
                                <div data-action="true" id="BUTTON5"
                                    class="ladi-element Signature_BTN_luu mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-button ladi-transition'>
                                        <div class="ladi-button-background"></div>
                                        <div id="BUTTON_TEXT5"
                                            class="ladi-element ladi-button-headline mayhen_invitation_ladjihe38udhd_box_anim">
                                            <p class='ladi-headline ladi-transition'>Lưu Chữ Ký</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="PARAGRAPH10" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'>Để lại chữ ký của Bạn và cùng chúng mình <br>lưu giữ kỷ
                        niệm hạnh phúc này nhé!!<br></div>
                </div>
                <div id="PARAGRAPH11" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'>Lưu Bút</div>
                </div>
                <div id="PARAGRAPH12" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'>Signature</div>
                </div>
                <div id="IMAGE8" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE9" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="SECTION20" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="IMAGE60" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE61" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE62" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE63" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE64" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE65" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE66" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="BOX42" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-box ladi-transition'></div>
                </div>
                <div id="PARAGRAPH41" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'>Wedding album</div>
                </div>
                <div id="PARAGRAPH42" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'>Our memories</div>
                </div>
                <div id="IMAGE107" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE108" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE109" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE110" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE114" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE119" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE120" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="SECTION8" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="BOX52" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-box ladi-transition'></div>
                </div>
                <div id="IMAGE13" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE17" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE10" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE11" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE12" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE14" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE15" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE16" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="PARAGRAPH13" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'>Xác nhận và gửi lời chúc<br></div>
                </div>
                <div id="PARAGRAPH14" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'>Vui lòng xác nhận sự tham dự của bạn để chúng tôi có thể
                        chuẩn bị và đón tiếp bạn một cách chu đáo nhất.<br>Sự hiện diện của bạn là niềm vinh hạnh đối
                        với chúng tôi. Trân trọng!<br></div>
                </div>
                <div id="GROUP90" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="FORM2" data-config-id="663edb65c9c5120012ef6f25"
                            class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <form autocomplete="off" method="post" class='ladi-form'>
                                <div id="BUTTON6" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-button ladi-transition'>
                                        <div class="ladi-button-background"></div>
                                        <div id="BUTTON_TEXT6"
                                            class="ladi-element ladi-button-headline mayhen_invitation_ladjihe38udhd_box_anim">
                                            <p class='ladi-headline ladi-transition'>XÁC NHẬN THAM DỰ</p>
                                        </div>
                                    </div>
                                </div>
                                <div id="FORM_ITEM3" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class="ladi-form-item-container">
                                        <div class="ladi-form-item-background"></div>
                                        <div class='ladi-form-item'><input autocomplete="off" tabindex="1"
                                                name="name" required class="ladi-form-control" type="text"
                                                placeholder="Tên của bạn là gì? " value="" /></div>
                                    </div>
                                </div>
                                <div id="FORM_ITEM5" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class="ladi-form-item-container">
                                        <div class="ladi-form-item-background"></div>
                                        <div class='ladi-form-item'><select tabindex="2" name="form_item6" required
                                                class="ladi-form-control ladi-form-control-select" data-selected="">
                                                <option value="" class="ladi-hidden">Bạn sẽ tham dự chứ?</option>
                                                <option value="Rất vui khi được tham dự">Rất vui khi được tham dự
                                                </option>
                                                <option value="Rất tiếc không thể đến">Rất tiếc không thể đến</option>
                                            </select></div>
                                    </div>
                                </div>
                                <div id="FORM_ITEM7" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class="ladi-form-item-container">
                                        <div class="ladi-form-item-background"></div>
                                        <div class='ladi-form-item'><input autocomplete="off" tabindex="3"
                                                name="form_item14" class="ladi-form-control" type="number"
                                                placeholder="Số lượng người tham gia?" value="" /></div>
                                    </div>
                                </div>
                                <div id="FORM_ITEM8" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class="ladi-form-item-container">
                                        <div class="ladi-form-item-background"></div>
                                        <div class='ladi-form-item ladi-form-checkbox ladi-form-checkbox-horizontal'>
                                            <div class="ladi-form-checkbox-box-item">
                                                <div class="ladi-form-checkbox-item"><input tabindex="4"
                                                        name="form_item8" type="radio" value="Cô Dâu" /><span
                                                        data-checked="false">Cô Dâu</span></div>
                                                <div class="ladi-form-checkbox-item"><input tabindex="4"
                                                        name="form_item8" type="radio" value="Chú Rể" /><span
                                                        data-checked="false">Chú Rể</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="FORM_ITEM26" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class="ladi-form-item-container">
                                        <div class="ladi-form-item-background"></div>
                                        <div class='ladi-form-item'>
                                            <textarea autocomplete="off" tabindex="5" name="form_item26" class="ladi-form-control"
                                                placeholder="Để lại lời nhắn"></textarea>
                                        </div>
                                    </div>
                                </div><button type="submit" class="ladi-hidden"></button>
                            </form>
                        </div>
                        <div id="PARAGRAPH46" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-paragraph'>&nbsp; Bạn là khách của ?<br></div>
                        </div>
                    </div>
                </div>
                <div id="PARAGRAPH55" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'>Confirm and send wishes<br></div>
                </div>
            </div>
        </div>
        <div id="SECTION9" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="IMAGE20" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE18" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE19" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="BOX53" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-box ladi-transition'></div>
                </div>
                <div id="HTML_CODE8" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-html-code'><!--messaeboxx-->
                        <style>
                            #MessageBox>* {
                                color: #AA7E2C;
                            }

                            ::-webkit-scrollbar {
                                width: 3px;
                                /* Chiều rộng của thanh cuộn */
                            }

                            ::-webkit-scrollbar-thumb {
                                background: #888;
                            }

                            #MessageBox {
                                padding: 30px;
                            }

                            .MessageBox-item {
                                border-bottom: 1px dashed #E6B066;
                                margin: 15px 0px;
                            }

                            .MessageBox-item-name {
                                font-size: 20px;
                                font-weight: bold;
                                margin: 10px 0px;

                            }

                            .MessageBox-item-message {
                                margin-bottom: 10px;
                            }
                        </style>
                        <div id="MessageBox">
                        </div>


                    </div>
                </div>
                <div id="IMAGE21" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="HEADLINE17" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <h3 class='ladi-headline ladi-transition'>LỜI CHÚC TỪ KHÁCH MỜI<br></h3>
                </div>
                <div id="IMAGE22" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE23" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image ladi-transition'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="PARAGRAPH56" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-paragraph ladi-transition'>Wishes from guests</div>
                </div>
                <div id="GROUP101" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="FORM3" data-config-id="688e346c804b6c0038582f27"
                            class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <form autocomplete="off" method="post" class='ladi-form'>
                                <div id="BUTTON11" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-button ladi-transition'>
                                        <div class="ladi-button-background"></div>
                                        <div id="BUTTON_TEXT11"
                                            class="ladi-element ladi-button-headline mayhen_invitation_ladjihe38udhd_box_anim">
                                            <p class='ladi-headline ladi-transition'>GỬI LỜI CHÚC</p>
                                        </div>
                                    </div>
                                </div>
                                <div id="FORM_ITEM11" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class="ladi-form-item-container">
                                        <div class="ladi-form-item-background"></div>
                                        <div class='ladi-form-item'><input autocomplete="off" tabindex="1"
                                                name="name" required class="ladi-form-control" type="text"
                                                placeholder="Tên Bạn là..." value="" /></div>
                                    </div>
                                </div>
                                <div id="FORM_ITEM12" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class="ladi-form-item-container">
                                        <div class="ladi-form-item-background"></div>
                                        <div class='ladi-form-item'>
                                            <textarea autocomplete="off" tabindex="8" name="message" required class="ladi-form-control" placeholder=""></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div id="FORM_ITEM14" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class="ladi-form-item-container">
                                        <div class="ladi-form-item-background"></div>
                                        <div class='ladi-form-item'><select tabindex="7" name="form_item12" required
                                                class="ladi-form-control ladi-form-control-select" data-selected="">
                                                <option value="" class="ladi-hidden">Ai sẽ thấy lời chúc của bạn?
                                                </option>
                                                <option value="Mọi người">Mọi người</option>
                                                <option value="Chỉ cô dâu, chú rể">Chỉ cô dâu, chú rể</option>
                                            </select></div>
                                    </div>
                                </div><button type="submit" class="ladi-hidden"></button>
                            </form>
                        </div>
                        <div id="PARAGRAPH63" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-paragraph'>Gửi lời chúc đến CDCR<br></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="SECTION27" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="IMAGE132" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE133" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="BOX70" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-box ladi-transition'></div>
                </div>
                <div id="BOX71" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-box ladi-transition'></div>
                </div>
                <div id="GROUP129" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="HEADLINE315" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>Share</h3>
                        </div>
                        <div id="HEADLINE316" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>Love</h3>
                        </div>
                        <div id="HEADLINE317" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>THE</h3>
                        </div>
                    </div>
                </div>
                <div id="GROUP130" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div data-action="true" id="IMAGE134"
                            class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image'>
                                <div class="ladi-image-background" data-sync-target="bride_bank_account"></div>
                            </div>
                        </div>
                        <div id="HEADLINE318" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline' data-sync-target="bride_name">{{ $brideName }}</h3>
                        </div>
                    </div>
                </div>
                <div id="GROUP131" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div data-action="true" id="IMAGE135"
                            class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image'>
                                <div class="ladi-image-background" data-sync-target="groom_bank_account"></div>
                            </div>
                        </div>
                        <div id="HEADLINE319" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline' data-sync-target="groom_name">{{ $groomName }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="SECTION17" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="GROUP56" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="BOX30" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="GROUP57" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="BOX31" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-box ladi-transition'></div>
                                </div>
                                <div id="HTML_CODE5" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-html-code'><iframe
                                            src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1756.9885563739792!2d108.1463068!3d16.0950639!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x314219f3604f0ac7%3A0x6d7c119700749d02!2sNami%20Restaurant!5e1!3m2!1svi!2s!4v1749724413112!5m2!1svi!2s"
                                            width="400" height="250" style="border:0;" allowfullscreen=""
                                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                                </div>
                            </div>
                        </div>
                        <div id="HEADLINE164" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline ladi-transition'>Địa chỉ TIỆc cưới<br></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="SECTION26" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="GROUP128" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-group'>
                        <div id="BOX68" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="BOX69" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="IMAGE123" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image'>
                                <div class="ladi-image-background"></div>
                            </div>
                        </div>
                        <div id="HEADLINE313" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>Thank you!<br></h3>
                        </div>
                        <div id="HEADLINE314" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>Sự hiện diện của bạn là niềm vinh hạnh cho gia đình chúng tôi.
                                <br>Chân thành cảm ơn!<br>
                            </h3>
                        </div>
                        <div id="IMAGE124" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image'>
                                <div class="ladi-image-background"></div>
                            </div>
                        </div>
                        <div id="IMAGE125" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image'>
                                <div class="ladi-image-background"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="IMAGE126" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE127" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE128" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE129" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE130" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
                <div id="IMAGE131" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-image'>
                        <div class="ladi-image-background"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="SECTION_POPUP" class='ladi-section'>
            <div class='ladi-section-background'></div>
            <div class="ladi-container">
                <div id="DROPBOX1" data-dropbox="true" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-popup'>
                        <div class="ladi-popup-background"></div>
                        <div data-action="true" id="GROUP105"
                            class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="BOX54" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-box ladi-transition'></div>
                                </div>
                                <div id="HEADLINE297" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline'>&nbsp;Gửi xác nhận tham dự (RSVP)&nbsp;</h3>
                                </div>
                            </div>
                        </div>
                        <div id="GROUP106" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="BOX55" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-box ladi-transition'></div>
                                </div>
                                <div id="HEADLINE298" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline'>Gửi lời chúc (Leave your wishes)<br></h3>
                                </div>
                            </div>
                        </div>
                        <div data-action="true" id="GROUP107"
                            class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="BOX56" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-box ladi-transition'></div>
                                </div>
                                <div id="SHAPE16" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-shape'><svg xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px"
                                            viewBox="0 0 100 100" enable-background="new 0 0 100 100"
                                            xml:space="preserve" preserveAspectRatio="none" width="100%"
                                            height="100%" class="" fill="rgba(255, 255, 255, 1)">
                                            <path
                                                d="M87.808,44.589c0-6.96-5.642-12.603-12.603-12.603H60.497c4.249-1.59,8.789-3.693,11.225-6.129  c3.378-3.372,5.397-7.735,5.391-11.667c0-2.683-0.898-5.003-2.609-6.707c-4.191-4.209-12.695-2.911-18.375,2.781  C53.693,12.7,51.59,17.24,50,21.489c-1.59-4.249-3.693-8.789-6.129-11.225c-5.686-5.698-14.184-6.997-18.375-2.781  c-1.711,1.705-2.609,4.024-2.609,6.707c-0.006,3.932,2.012,8.295,5.391,11.667c2.436,2.436,6.976,4.539,11.225,6.129H24.795  c-6.96,0-12.603,5.642-12.603,12.603v12.603h6.301v25.205c0,6.96,5.642,12.603,12.603,12.603h37.808  c6.96,0,12.603-5.642,12.603-12.603V57.192h6.301V44.589z M60.584,14.72c2.185-2.191,4.948-3.544,7.206-3.544  c0.683,0,1.625,0.129,2.258,0.763c0.634,0.634,0.763,1.575,0.763,2.258c0.006,2.258-1.354,5.021-3.544,7.206  c-2.086,2.086-7.661,4.344-12.646,5.963C56.234,22.381,58.498,16.806,60.584,14.72z M29.188,14.197c0-0.683,0.129-1.625,0.763-2.258  c0.634-0.634,1.575-0.763,2.258-0.763c2.258,0,5.021,1.354,7.206,3.544c2.086,2.086,4.344,7.661,5.963,12.646  c-4.984-1.612-10.56-3.877-12.646-5.963C30.542,19.218,29.182,16.455,29.188,14.197z M18.493,44.589  c0-3.474,2.827-6.301,6.301-6.301h22.055v12.603H18.493V44.589z M24.795,82.397V57.192h22.055v31.507H31.096  C27.622,88.699,24.795,85.872,24.795,82.397z M75.205,82.397c0,3.474-2.827,6.301-6.301,6.301H53.151V57.192h22.055V82.397z   M81.507,50.891H53.151V38.288h22.055c3.474,0,6.301,2.827,6.301,6.301V50.891z">
                                            </path>
                                        </svg></div>
                                </div>
                            </div>
                        </div>
                        <div id="GROUP108" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="BOX57" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-box ladi-transition'></div>
                                </div>
                                <div id="HEADLINE299" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline'>Mừng cưới<br></h3>
                                </div>
                            </div>
                        </div>
                        <div data-action="true" id="GROUP109"
                            class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="BOX58" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-box ladi-transition'></div>
                                </div>
                                <div id="SHAPE17" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-shape'><svg xmlns="http://www.w3.org/2000/svg" width="100%"
                                            height="100%" preserveAspectRatio="none" viewBox="0 0 24 24"
                                            class="" fill="rgba(255, 255, 255, 1)">
                                            <path
                                                d="M9,22A1,1 0 0,1 8,21V18H4A2,2 0 0,1 2,16V4C2,2.89 2.9,2 4,2H20A2,2 0 0,1 22,4V16A2,2 0 0,1 20,18H13.9L10.2,21.71C10,21.9 9.75,22 9.5,22V22H9M10,15L18,7L16.59,5.58L10,12.17L7.41,9.59L6,11L10,15Z">
                                            </path>
                                        </svg></div>
                                </div>
                            </div>
                        </div>
                        <div data-action="true" id="GROUP110"
                            class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="BOX59" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-box ladi-transition'></div>
                                </div>
                                <div id="SHAPE18" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-shape'><svg xmlns="http://www.w3.org/2000/svg" width="100%"
                                            height="100%" preserveAspectRatio="none" viewBox="0 0 1536 1896.0833"
                                            class="" fill="rgba(255, 255, 255, 1)">
                                            <path
                                                d="M404 1108l152 152-52 52h-56v-96h-96v-56zm414-390q14 13-3 30l-291 291q-17 17-30 3-14-13 3-30l291-291q17-17 30-3zm-274 690l544-544-288-288-544 544v288h288zm608-608l92-92q28-28 28-68t-28-68l-152-152q-28-28-68-28t-68 28l-92 92zm384-384v960q0 119-84.5 203.5T1248 1664H288q-119 0-203.5-84.5T0 1376V416q0-119 84.5-203.5T288 128h960q119 0 203.5 84.5T1536 416z">
                                            </path>
                                        </svg></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="POPUP2" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-popup'>
                        <div class="ladi-popup-background"></div>
                        <div class="ladi-overlay"></div>
                        <div id="BOX72" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="IMAGE136" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image'>
                                <div class="ladi-image-background"></div>
                            </div>
                        </div>
                        <div id="HEADLINE320" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>CTK: .............................<span
                                    style="font-weight: normal;"><br></span>STK: <span
                                    style="font-weight: normal;">999999999999999</span><br>NH: <span
                                    style="font-weight: normal;">Vietcombank</span><br></h3>
                        </div>
                        <div id="HEADLINE321" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>Mừng cưới</h3>
                        </div>
                        <div id="HEADLINE322" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>CHÚ RỂ</h3>
                        </div>
                    </div>
                </div>
                <div id="POPUP3" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-popup'>
                        <div class="ladi-popup-background"></div>
                        <div class="ladi-overlay"></div>
                        <div id="BOX73" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-box ladi-transition'></div>
                        </div>
                        <div id="IMAGE137" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-image'>
                                <div class="ladi-image-background"></div>
                            </div>
                        </div>
                        <div id="HEADLINE323" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>CTK: .............................<span
                                    style="font-weight: normal;"><br></span>STK: <span
                                    style="font-weight: normal;">999999999999999</span><br>NH: <span
                                    style="font-weight: normal;">Vietcombank</span><br></h3>
                        </div>
                        <div id="HEADLINE324" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>Mừng cưới</h3>
                        </div>
                        <div id="HEADLINE325" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <h3 class='ladi-headline'>CÔ DÂU</h3>
                        </div>
                    </div>
                </div>
                <div id="POPUP4" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-popup'>
                        <div class="ladi-popup-background"></div>
                        <div class="ladi-overlay"></div>
                        <div id="GROUP132" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="IMAGE138" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-image'>
                                        <div class="ladi-image-background"></div>
                                    </div>
                                </div>
                                <div id="IMAGE139" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-image'>
                                        <div class="ladi-image-background"></div>
                                    </div>
                                </div>
                                <div id="HEADLINE326" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline'>Cảm ơn bạn đã xác nhận<br></h3>
                                </div>
                                <div id="PARAGRAPH78" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-paragraph'>Mong rằng bạn sẽ có mặt trong ngày trọng đại của chúng
                                        mình, để khoảnh khắc ấy thêm phần ý nghĩa và trọn vẹn hơn.</div>
                                </div>
                                <div id="IMAGE140" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-image'>
                                        <div class="ladi-image-background"></div>
                                    </div>
                                </div>
                                <div id="IMAGE141" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-image'>
                                        <div class="ladi-image-background"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="POPUP5" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                    <div class='ladi-popup'>
                        <div class="ladi-popup-background"></div>
                        <div class="ladi-overlay"></div>
                        <div id="GROUP133" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                            <div class='ladi-group'>
                                <div id="IMAGE142" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-image'>
                                        <div class="ladi-image-background"></div>
                                    </div>
                                </div>
                                <div id="IMAGE143" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-image'>
                                        <div class="ladi-image-background"></div>
                                    </div>
                                </div>
                                <div id="HEADLINE327" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <h3 class='ladi-headline'>Cảm ơn bạn đã&nbsp;<br>gừi lời chúc tới chúng mình<br>
                                    </h3>
                                </div>
                                <div id="PARAGRAPH80" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-paragraph'>Mong rằng bạn sẽ có mặt trong ngày trọng đại của chúng
                                        mình, để khoảnh khắc ấy thêm phần ý nghĩa và trọn vẹn hơn.</div>
                                </div>
                                <div id="IMAGE144" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-image'>
                                        <div class="ladi-image-background"></div>
                                    </div>
                                </div>
                                <div id="IMAGE145" class="ladi-element mayhen_invitation_ladjihe38udhd_box_anim">
                                    <div class='ladi-image'>
                                        <div class="ladi-image-background"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div id="backdrop-popup" class="backdrop-popup"></div>
    <div id="backdrop-dropbox" class="backdrop-dropbox"></div>
    <div id="lightbox-screen" class="lightbox-screen"></div>

    <!--[if lt IE 9]><![endif]-->
    <link href="https://fonts.googleapis.com/css2?family=Philosopher:wght@400;700&display=swap" rel="stylesheet"
        type="text/css">
    <style type="text/css">
        @-webkit-keyframes fadeInUp {
            0% {
                opacity: 0;
                -webkit-transform: translateY(20px);
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                -webkit-transform: translateY(0);
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                -webkit-transform: translateY(20px);
                -ms-transform: translateY(20px);
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                -webkit-transform: translateY(0);
                -ms-transform: translateY(0);
                transform: translateY(0);
            }
        }

        @-webkit-keyframes fadeInDown {
            0% {
                opacity: 0;
                -webkit-transform: translateY(-20px);
                transform: translateY(-20px);
            }

            100% {
                opacity: 1;
                -webkit-transform: translateY(0);
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            0% {
                opacity: 0;
                -webkit-transform: translateY(-20px);
                -ms-transform: translateY(-20px);
                transform: translateY(-20px);
            }

            100% {
                opacity: 1;
                -webkit-transform: translateY(0);
                -ms-transform: translateY(0);
                transform: translateY(0);
            }
        }

        @-webkit-keyframes bounceIn {
            0% {
                opacity: 0;
                -webkit-transform: scale(0.3);
                transform: scale(0.3);
            }

            50% {
                opacity: 1;
                -webkit-transform: scale(1.05);
                transform: scale(1.05);
            }

            70% {
                -webkit-transform: scale(0.9);
                transform: scale(0.9);
            }

            100% {
                opacity: 1;
                -webkit-transform: scale(1);
                transform: scale(1);
            }
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                -webkit-transform: scale(0.3);
                -ms-transform: scale(0.3);
                transform: scale(0.3);
            }

            50% {
                opacity: 1;
                -webkit-transform: scale(1.05);
                -ms-transform: scale(1.05);
                transform: scale(1.05);
            }

            70% {
                -webkit-transform: scale(0.9);
                -ms-transform: scale(0.9);
                transform: scale(0.9);
            }

            100% {
                opacity: 1;
                -webkit-transform: scale(1);
                -ms-transform: scale(1);
                transform: scale(1);
            }
        }

        @-webkit-keyframes rotateInUpLeft {
            0% {
                -webkit-transform-origin: left bottom;
                transform-origin: left bottom;
                -webkit-transform: rotate(90deg);
                transform: rotate(90deg);
                opacity: 0;
            }

            100% {
                -webkit-transform-origin: left bottom;
                transform-origin: left bottom;
                -webkit-transform: rotate(0);
                transform: rotate(0);
                opacity: 1;
            }
        }

        @keyframes rotateInUpLeft {
            0% {
                -webkit-transform-origin: left bottom;
                -ms-transform-origin: left bottom;
                transform-origin: left bottom;
                -webkit-transform: rotate(90deg);
                -ms-transform: rotate(90deg);
                transform: rotate(90deg);
                opacity: 0;
            }

            100% {
                -webkit-transform-origin: left bottom;
                -ms-transform-origin: left bottom;
                transform-origin: left bottom;
                -webkit-transform: rotate(0);
                -ms-transform: rotate(0);
                transform: rotate(0);
                opacity: 1;
            }
        }

        @-webkit-keyframes rotateInUpRight {
            0% {
                -webkit-transform-origin: right bottom;
                transform-origin: right bottom;
                -webkit-transform: rotate(-90deg);
                transform: rotate(-90deg);
                opacity: 0;
            }

            100% {
                -webkit-transform-origin: right bottom;
                transform-origin: right bottom;
                -webkit-transform: rotate(0);
                transform: rotate(0);
                opacity: 1;
            }
        }

        @keyframes rotateInUpRight {
            0% {
                -webkit-transform-origin: right bottom;
                -ms-transform-origin: right bottom;
                transform-origin: right bottom;
                -webkit-transform: rotate(-90deg);
                -ms-transform: rotate(-90deg);
                transform: rotate(-90deg);
                opacity: 0;
            }

            100% {
                -webkit-transform-origin: right bottom;
                -ms-transform-origin: right bottom;
                transform-origin: right bottom;
                -webkit-transform: rotate(0);
                -ms-transform: rotate(0);
                transform: rotate(0);
                opacity: 1;
            }
        }

        @-webkit-keyframes pulse {
            0% {
                -webkit-transform: scale(1);
                transform: scale(1);
            }

            50% {
                -webkit-transform: scale(1.1);
                transform: scale(1.1);
            }

            100% {
                -webkit-transform: scale(1);
                transform: scale(1);
            }
        }

        @keyframes pulse {
            0% {
                -webkit-transform: scale(1);
                -ms-transform: scale(1);
                transform: scale(1);
            }

            50% {
                -webkit-transform: scale(1.1);
                -ms-transform: scale(1.1);
                transform: scale(1.1);
            }

            100% {
                -webkit-transform: scale(1);
                -ms-transform: scale(1);
                transform: scale(1);
            }
        }

        @-webkit-keyframes fadeInRight {
            0% {
                opacity: 0;
                -webkit-transform: translateX(20px);
                transform: translateX(20px);
            }

            100% {
                opacity: 1;
                -webkit-transform: translateX(0);
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            0% {
                opacity: 0;
                -webkit-transform: translateX(40px);
                -ms-transform: translateX(40px);
                transform: translateX(40px);
            }

            100% {
                opacity: 1;
                -webkit-transform: translateX(0);
                -ms-transform: translateX(0);
                transform: translateX(0);
            }
        }

        @-webkit-keyframes bounceInUp {
            0% {
                opacity: 0;
                -webkit-transform: translateY(2000px);
                transform: translateY(2000px);
            }

            60% {
                opacity: 1;
                -webkit-transform: translateY(-30px);
                transform: translateY(-30px);
            }

            80% {
                -webkit-transform: translateY(10px);
                transform: translateY(10px);
            }

            100% {
                -webkit-transform: translateY(0);
                transform: translateY(0);
            }
        }

        @keyframes bounceInUp {
            0% {
                opacity: 0;
                -webkit-transform: translateY(2000px);
                -ms-transform: translateY(2000px);
                transform: translateY(2000px);
            }

            60% {
                opacity: 1;
                -webkit-transform: translateY(-30px);
                -ms-transform: translateY(-30px);
                transform: translateY(-30px);
            }

            80% {
                -webkit-transform: translateY(10px);
                -ms-transform: translateY(10px);
                transform: translateY(10px);
            }

            100% {
                -webkit-transform: translateY(0);
                -ms-transform: translateY(0);
                transform: translateY(0);
            }
        }

        @-webkit-keyframes bounceInRight {
            0% {
                opacity: 0;
                -webkit-transform: translateX(2000px);
                transform: translateX(2000px);
            }

            60% {
                opacity: 1;
                -webkit-transform: translateX(-30px);
                transform: translateX(-30px);
            }

            80% {
                -webkit-transform: translateX(10px);
                transform: translateX(10px);
            }

            100% {
                -webkit-transform: translateX(0);
                transform: translateX(0);
            }
        }

        @keyframes bounceInRight {
            0% {
                opacity: 0;
                -webkit-transform: translateX(2000px);
                -ms-transform: translateX(2000px);
                transform: translateX(2000px);
            }

            60% {
                opacity: 1;
                -webkit-transform: translateX(-30px);
                -ms-transform: translateX(-30px);
                transform: translateX(-30px);
            }

            80% {
                -webkit-transform: translateX(10px);
                -ms-transform: translateX(10px);
                transform: translateX(10px);
            }

            100% {
                -webkit-transform: translateX(0);
                -ms-transform: translateX(0);
                transform: translateX(0);
            }
        }

        @-webkit-keyframes flash {

            0%,
            100%,
            50% {
                opacity: 1;
            }

            25%,
            75% {
                opacity: 0;
            }
        }

        @keyframes flash {

            0%,
            100%,
            50% {
                opacity: 1;
            }

            25%,
            75% {
                opacity: 0;
            }
        }

        @-webkit-keyframes rotateInDownLeft {
            0% {
                -webkit-transform-origin: left bottom;
                transform-origin: left bottom;
                -webkit-transform: rotate(-90deg);
                transform: rotate(-90deg);
                opacity: 0;
            }

            100% {
                -webkit-transform-origin: left bottom;
                transform-origin: left bottom;
                -webkit-transform: rotate(0);
                transform: rotate(0);
                opacity: 1;
            }
        }

        @keyframes rotateInDownLeft {
            0% {
                -webkit-transform-origin: left bottom;
                -ms-transform-origin: left bottom;
                transform-origin: left bottom;
                -webkit-transform: rotate(-90deg);
                -ms-transform: rotate(-90deg);
                transform: rotate(-90deg);
                opacity: 0;
            }

            100% {
                -webkit-transform-origin: left bottom;
                -ms-transform-origin: left bottom;
                transform-origin: left bottom;
                -webkit-transform: rotate(0);
                -ms-transform: rotate(0);
                transform: rotate(0);
                opacity: 1;
            }
        }

        @-webkit-keyframes rotateIn {
            0% {
                -webkit-transform-origin: center center;
                transform-origin: center center;
                -webkit-transform: rotate(-200deg);
                transform: rotate(-200deg);
                opacity: 0;
            }

            100% {
                -webkit-transform-origin: center center;
                transform-origin: center center;
                -webkit-transform: rotate(0);
                transform: rotate(0);
                opacity: 1;
            }
        }

        @keyframes rotateIn {
            0% {
                -webkit-transform-origin: center center;
                -ms-transform-origin: center center;
                transform-origin: center center;
                -webkit-transform: rotate(-200deg);
                -ms-transform: rotate(-200deg);
                transform: rotate(-200deg);
                opacity: 0;
            }

            100% {
                -webkit-transform-origin: center center;
                -ms-transform-origin: center center;
                transform-origin: center center;
                -webkit-transform: rotate(0);
                -ms-transform: rotate(0);
                transform: rotate(0);
                opacity: 1;
            }
        }

        @-webkit-keyframes swing {
            20% {
                -webkit-transform: rotate(15deg);
                transform: rotate(15deg);
            }

            40% {
                -webkit-transform: rotate(-10deg);
                transform: rotate(-10deg);
            }

            60% {
                -webkit-transform: rotate(5deg);
                transform: rotate(5deg);
            }

            80% {
                -webkit-transform: rotate(-5deg);
                transform: rotate(-5deg);
            }

            100% {
                -webkit-transform: rotate(0);
                transform: rotate(0);
            }
        }

        @keyframes swing {
            20% {
                -webkit-transform: rotate(15deg);
                -ms-transform: rotate(15deg);
                transform: rotate(15deg);
            }

            40% {
                -webkit-transform: rotate(-10deg);
                -ms-transform: rotate(-10deg);
                transform: rotate(-10deg);
            }

            60% {
                -webkit-transform: rotate(5deg);
                -ms-transform: rotate(5deg);
                transform: rotate(5deg);
            }

            80% {
                -webkit-transform: rotate(-5deg);
                -ms-transform: rotate(-5deg);
                transform: rotate(-5deg);
            }

            100% {
                -webkit-transform: rotate(0);
                -ms-transform: rotate(0);
                transform: rotate(0);
            }
        }

        @-webkit-keyframes bounceInLeft {
            0% {
                opacity: 0;
                -webkit-transform: translateX(-2000px);
                transform: translateX(-2000px);
            }

            60% {
                opacity: 1;
                -webkit-transform: translateX(30px);
                transform: translateX(30px);
            }

            80% {
                -webkit-transform: translateX(-10px);
                transform: translateX(-10px);
            }

            100% {
                -webkit-transform: translateX(0);
                transform: translateX(0);
            }
        }

        @keyframes bounceInLeft {
            0% {
                opacity: 0;
                -webkit-transform: translateX(-2000px);
                -ms-transform: translateX(-2000px);
                transform: translateX(-2000px);
            }

            60% {
                opacity: 1;
                -webkit-transform: translateX(30px);
                -ms-transform: translateX(30px);
                transform: translateX(30px);
            }

            80% {
                -webkit-transform: translateX(-10px);
                -ms-transform: translateX(-10px);
                transform: translateX(-10px);
            }

            100% {
                -webkit-transform: translateX(0);
                -ms-transform: translateX(0);
                transform: translateX(0);
            }
        }

        @-webkit-keyframes tada {
            0% {
                -webkit-transform: scale(1);
                transform: scale(1);
            }

            10%,
            20% {
                -webkit-transform: scale(0.9) rotate(-3deg);
                transform: scale(0.9) rotate(-3deg);
            }

            30%,
            50%,
            70%,
            90% {
                -webkit-transform: scale(1.1) rotate(3deg);
                transform: scale(1.1) rotate(3deg);
            }

            40%,
            60%,
            80% {
                -webkit-transform: scale(1.1) rotate(-3deg);
                transform: scale(1.1) rotate(-3deg);
            }

            100% {
                -webkit-transform: scale(1) rotate(0);
                transform: scale(1) rotate(0);
            }
        }

        @keyframes tada {
            0% {
                -webkit-transform: scale(1);
                -ms-transform: scale(1);
                transform: scale(1);
            }

            10%,
            20% {
                -webkit-transform: scale(0.9) rotate(-3deg);
                -ms-transform: scale(0.9) rotate(-3deg);
                transform: scale(0.9) rotate(-3deg);
            }

            30%,
            50%,
            70%,
            90% {
                -webkit-transform: scale(1.1) rotate(3deg);
                -ms-transform: scale(1.1) rotate(3deg);
                transform: scale(1.1) rotate(3deg);
            }

            40%,
            60%,
            80% {
                -webkit-transform: scale(1.1) rotate(-3deg);
                -ms-transform: scale(1.1) rotate(-3deg);
                transform: scale(1.1) rotate(-3deg);
            }

            100% {
                -webkit-transform: scale(1) rotate(0);
                -ms-transform: scale(1) rotate(0);
                transform: scale(1) rotate(0);
            }
        }

        @-webkit-keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        @-webkit-keyframes fadeInLeft {
            0% {
                opacity: 0;
                -webkit-transform: translateX(-20px);
                transform: translateX(-20px);
            }

            100% {
                opacity: 1;
                -webkit-transform: translateX(0);
                transform: translateX(0);
            }
        }

        @keyframes fadeInLeft {
            0% {
                opacity: 0;
                -webkit-transform: translateX(-20px);
                -ms-transform: translateX(-20px);
                transform: translateX(-20px);
            }

            100% {
                opacity: 1;
                -webkit-transform: translateX(0);
                -ms-transform: translateX(0);
                transform: translateX(0);
            }
        }

        @-webkit-keyframes bounce {

            0%,
            100%,
            20%,
            50%,
            80% {
                -webkit-transform: translateY(0);
                transform: translateY(0);
            }

            40% {
                -webkit-transform: translateY(-30px);
                transform: translateY(-30px);
            }

            60% {
                -webkit-transform: translateY(-15px);
                transform: translateY(-15px);
            }
        }

        @keyframes bounce {

            0%,
            100%,
            20%,
            50%,
            80% {
                -webkit-transform: translateY(0);
                -ms-transform: translateY(0);
                transform: translateY(0);
            }

            40% {
                -webkit-transform: translateY(-30px);
                -ms-transform: translateY(-30px);
                transform: translateY(-30px);
            }

            60% {
                -webkit-transform: translateY(-15px);
                -ms-transform: translateY(-15px);
                transform: translateY(-15px);
            }
        }

        @-webkit-keyframes flipInY {
            0% {
                -webkit-transform: perspective(400px) rotateY(90deg);
                transform: perspective(400px) rotateY(90deg);
                opacity: 0;
            }

            40% {
                -webkit-transform: perspective(400px) rotateY(-10deg);
                transform: perspective(400px) rotateY(-10deg);
            }

            70% {
                -webkit-transform: perspective(400px) rotateY(10deg);
                transform: perspective(400px) rotateY(10deg);
            }

            100% {
                -webkit-transform: perspective(400px) rotateY(0);
                transform: perspective(400px) rotateY(0);
                opacity: 1;
            }
        }

        @keyframes flipInY {
            0% {
                -webkit-transform: perspective(400px) rotateY(90deg);
                -ms-transform: perspective(400px) rotateY(90deg);
                transform: perspective(400px) rotateY(90deg);
                opacity: 0;
            }

            40% {
                -webkit-transform: perspective(400px) rotateY(-10deg);
                -ms-transform: perspective(400px) rotateY(-10deg);
                transform: perspective(400px) rotateY(-10deg);
            }

            70% {
                -webkit-transform: perspective(400px) rotateY(10deg);
                -ms-transform: perspective(400px) rotateY(10deg);
                transform: perspective(400px) rotateY(10deg);
            }

            100% {
                -webkit-transform: perspective(400px) rotateY(0);
                -ms-transform: perspective(400px) rotateY(0);
                transform: perspective(400px) rotateY(0);
                opacity: 1;
            }
        }

        @-webkit-keyframes shake {

            0%,
            100% {
                -webkit-transform: translateX(0);
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                -webkit-transform: translateX(-10px);
                transform: translateX(-10px);
            }

            20%,
            40%,
            60%,
            80% {
                -webkit-transform: translateX(10px);
                transform: translateX(10px);
            }
        }

        @keyframes shake {

            0%,
            100% {
                -webkit-transform: translateX(0);
                -ms-transform: translateX(0);
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                -webkit-transform: translateX(-10px);
                -ms-transform: translateX(-10px);
                transform: translateX(-10px);
            }

            20%,
            40%,
            60%,
            80% {
                -webkit-transform: translateX(10px);
                -ms-transform: translateX(10px);
                transform: translateX(10px);
            }
        }

        [data-hint]:after {
            content: attr(data-hint);
            text-align: center;
            white-space: nowrap;
            z-index: 9999;
            background: #292929;
            padding: 3px 7px;
            border-radius: 2px;
            color: white;
            font-weight: 400;
            font-size: 12px;
            line-height: 16px;
        }

        [data-hint]:after,
        [data-hint]:before {
            display: inline-block;
            pointer-events: none;
            position: absolute;
            visibility: hidden;
        }

        [data-hint]:hover:after,
        [data-hint]:hover:before {
            visibility: visible;
        }

        [data-hint]:before {
            content: "";
            border: 5px solid transparent;
            z-index: 9998;
        }

        .hint-persist:before,
        .hint-persist:after {
            visibility: visible;
        }

        @media only screen and (max-width: 768px) {

            [class*="hint-"][class*="-mobile"]:after,
            [class*="hint-"][class*="-mobile"]:before {
                display: none;
            }
        }

        .hint-d-short:hover:after,
        .hint-d-short:hover:before {
            -webkit-transition: visibility 0s 0.3s ease;
            transition: visibility 0s 0.3s ease;
        }

        [class*="hint-fade"][class*="-d-short"]:hover:before,
        [class*="hint-fade"][class*="-d-short"]:hover:after,
        [class*="hint-anim"][class*="-d-short"]:hover:before,
        [class*="hint-anim"][class*="-d-short"]:hover:after {
            -webkit-transition-delay: 0.3s;
            transition-delay: 0.3s;
        }

        [class*="hint-fade"]:before,
        [class*="hint-fade"]:after {
            -webkit-transition: opacity 0.2s ease-out, visibility 0.2s ease-out;
            transition: opacity 0.2s ease-out, visibility 0.2s ease-out;
            opacity: 0;
        }

        [class*="hint-fade"]:hover:before,
        [class*="hint-fade"]:hover:after {
            opacity: 1;
        }

        [class*="hint-"][class*="-s-small"]:after {
            max-width: 200px;
            width: -webkit-max-content;
            width: -moz-max-content;
            width: max-content;
            white-space: normal;
        }

        [class*="hint-top"]:before {
            border-top-color: #292929;
            bottom: 100%;
            margin-bottom: 0px;
        }

        [class*="hint-top"]:after {
            bottom: 100%;
            margin-bottom: 10px;
        }

        [class*="hint-anim"][class*="hint-top"]:after,
        [class*="hint-anim"][class*="hint-top"]:before {
            bottom: 125%;
            opacity: 0;
            -webkit-transition: opacity 0.3s ease-out, visibility 0.3s ease-out, bottom 0.3s ease-out;
            transition: opacity 0.3s ease-out, visibility 0.3s ease-out, bottom 0.3s ease-out;
        }

        [class*="hint-anim"][class*="hint-top"]:hover:after,
        [class*="hint-anim"][class*="hint-top"]:hover:before {
            opacity: 1;
            bottom: 100%;
        }

        [class*="hint-top-middle"]:before {
            right: 50%;
            margin-right: -5px;
        }

        [class*="hint-top-middle"]:after {
            left: 50%;
            -webkit-transform: translateX(-50%);
            -ms-transform: translateX(-50%);
            transform: translateX(-50%);
        }

        [class*="hint-bottom"]:before {
            border-bottom-color: #292929;
            top: 100%;
            margin-top: 0px;
        }

        [class*="hint-bottom"]:after {
            margin-top: 10px;
            top: 100%;
        }

        [class*="hint-anim"][class*="hint-bottom"]:after,
        [class*="hint-anim"][class*="hint-bottom"]:before {
            top: 125%;
            opacity: 0;
            -webkit-transition: opacity 0.3s ease, visibility 0.3s ease, top 0.3s ease;
            transition: opacity 0.3s ease, visibility 0.3s ease, top 0.3s ease;
        }

        [class*="hint-anim"][class*="hint-bottom"]:hover:after,
        [class*="hint-anim"][class*="hint-bottom"]:hover:before {
            top: 100%;
            opacity: 1;
        }

        [class*="hint-bottom-middle"]:before {
            right: 50%;
            margin-right: -5px;
        }

        [class*="hint-bottom-middle"]:after {
            left: 50%;
            -webkit-transform: translateX(-50%);
            -ms-transform: translateX(-50%);
            transform: translateX(-50%);
        }
    </style>






    <!---->

    <!--custom chức năng âm nhạc-->
    {{-- LIGHTBOX GALLERY XEM ANH CUOI SAC NET --}}
    <div id="mayhen_hacbach2_lightbox" class="mayhen_invitation_ladjihe38udhd_lightbox" role="dialog"
        aria-modal="true" aria-label="Xem ảnh cưới phóng to" hidden>
        <div class="mayhen_invitation_ladjihe38udhd_lightbox_backdrop"></div>
        <button type="button" class="mayhen_invitation_ladjihe38udhd_lightbox_close" aria-label="Đóng (ESC)">
            <svg viewBox="0 0 24 24">
                <path
                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
            </svg>
        </button>
        <button type="button" class="mayhen_invitation_ladjihe38udhd_lightbox_nav prev"
            aria-label="Ảnh trước (Mũi tên trái)">
            <svg viewBox="0 0 24 24">
                <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
            </svg>
        </button>
        <button type="button" class="mayhen_invitation_ladjihe38udhd_lightbox_nav next"
            aria-label="Ảnh sau (Mũi tên phải)">
            <svg viewBox="0 0 24 24">
                <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" />
            </svg>
        </button>
        <div class="mayhen_invitation_ladjihe38udhd_lightbox_stage">
            <img id="mayhen_hacbach2_lightbox_img" src="" alt="Ảnh cưới phóng to">
            <div class="mayhen_invitation_ladjihe38udhd_lightbox_counter">
                <span id="mayhen_hacbach2_lightbox_idx">1</span> / <span id="mayhen_hacbach2_lightbox_total">1</span>
            </div>
        </div>
    </div>



    <div id="mayhen_hacbach2_toast" class="mayhen_hacbach2_toast"></div>
@endsection

@push('scripts')
    <script
        src="{{ route('templates.asset', ['slug' => 'hac-bach-trao-duyen-2', 'path' => 'script.js']) }}?v={{ $jsVersion }}">
    </script>
@endpush
