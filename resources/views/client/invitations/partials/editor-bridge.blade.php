{{-- 
    MAYHEN.VN — INVITATION EDITOR REALTIME BRIDGE
    Bộ kết nối chuẩn hóa 2 chiều giữa Studio Editor V2 và tất cả các Template thiệp cưới (Native Vanilla JS).
--}}

{{-- QUY TẮC TOÀN CỤC: TỰ ĐỘNG MỞ SẴN NỘI DUNG THIỆP MỜI TRONG IFRAME (ZERO-FRICTION AUTO-OPENED PROTOCOL) --}}
<style id="mayhen_global_auto_opened_styles">
    /* 1. Mở khóa cuộn mượt mà toàn trang cho html và ẨN HOÀN TOÀN thanh scrollbar trong iframe */
    html.mayhen_embed_mode,
    html.mayhen_template_auto_opened {
        scroll-behavior: smooth !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        height: auto !important;
        position: static !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    body.mayhen_embed_mode,
    body.mayhen_template_auto_opened {
        overflow: visible !important;
        overflow-y: visible !important;
        overflow-x: hidden !important;
        height: auto !important;
        position: static !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    html.mayhen_embed_mode::-webkit-scrollbar,
    body.mayhen_embed_mode::-webkit-scrollbar,
    html.mayhen_template_auto_opened::-webkit-scrollbar,
    body.mayhen_template_auto_opened::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    /* 2. Cưỡng chế ẩn triệt để 100% tất cả các màn che, cánh cửa 3D, popup nút bấm mở đầu của MỌI template */
    .mayhen_embed_mode .mayhen_invitation_opening,
    .mayhen_embed_mode .mayhen_invitation_envelope,
    .mayhen_embed_mode .mayhen_invitation_open_btn,
    .mayhen_embed_mode #mayhen-door-stage,
    .mayhen_embed_mode [id*="door-stage"],
    .mayhen_embed_mode [class*="door_stage"],
    .mayhen_embed_mode #POPUP8,
    .mayhen_embed_mode .ladi-popup:has(#BUTTON18),
    .mayhen_embed_mode .mayhen_invitation_sr_cua,
    .mayhen_embed_mode [class*="_sr_cua"],
    .mayhen_embed_mode [class*="open_hint"],
    .mayhen_embed_mode [id*="open-hint"] {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* 3. Cưỡng chế hiển thị nội dung chính của thiệp cưới, giải phóng triệt để overflow trap */
    .mayhen_embed_mode .mayhen_invitation_ladi_wraper,
    .mayhen_embed_mode .ladi-wraper,
    .mayhen_embed_mode [class*="ladi_wraper"],
    .mayhen_embed_mode [class*="card_wrapper"],
    .mayhen_embed_mode #mayhen_invitation_main_content {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        overflow: visible !important;
        overflow-y: visible !important;
        height: auto !important;
        box-shadow: none !important;
    }

    /* 4. Cưỡng chế các animation khởi đầu hiển thị ngay lập tức (không bị ẩn trắng khi chưa cuộn) */
    .mayhen_embed_mode .ladi-animation-hidden,
    .mayhen_embed_mode [class*="ladi-animation-hidden"],
    .mayhen_embed_mode [class*="_rv"],
    .mayhen_embed_mode [class*="sr_hien"] {
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
    }

    /* 5. Ẩn tuyệt đối 100% thanh điều hướng bottom nav dock khi nhúng trong iframe xem mẫu hoặc editor */
    .mayhen_embed_mode .mayhen_invitation_bottom_nav,
    .mayhen_embed_mode [class*="bottom_nav"],
    body.mayhen_embed_mode [class*="bottom_nav"],
    [data-in-iframe="true"] [class*="bottom_nav"] {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* 7. Hiệu ứng Highlight vàng kim Quiet Luxury khi người dùng click/focus vào trường hoặc ảnh trong Studio Editor */
    @keyframes mayhenTargetPulse {
        0% {
            outline: 3px solid rgba(197, 168, 128, 0.95);
            outline-offset: 4px;
            box-shadow: 0 0 0 6px rgba(197, 168, 128, 0.35), 0 10px 30px rgba(197, 168, 128, 0.3);
            transform: scale(1.02);
        }
        40% {
            outline: 3px solid rgba(197, 168, 128, 0.8);
            outline-offset: 3px;
            box-shadow: 0 0 0 10px rgba(197, 168, 128, 0.15), 0 8px 24px rgba(197, 168, 128, 0.2);
        }
        100% {
            outline: 2px solid transparent;
            outline-offset: 0;
            box-shadow: none;
            transform: scale(1);
        }
    }
    .mayhen_editor_target_highlight {
        animation: mayhenTargetPulse 1.8s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
        position: relative;
        z-index: 10;
        transition: transform 0.3s ease, outline 0.3s ease, box-shadow 0.3s ease;
    }
</style>

<script>
(function() {
    'use strict';

    // Nhận diện trạng thái nhúng (Showcase, Editor, Preview iframe)
    var inIframe = false;
    try {
        inIframe = window.self !== window.top;
    } catch (e) {
        inIframe = true;
    }

    var isEmbed = Boolean(
        window.__MAYHEN_IS_EMBED || 
        inIframe || 
        new URLSearchParams(window.location.search).get('embed') === '1' ||
        ['editor', 'showcase', 'home', 'preview_card', 'iframe'].indexOf(new URLSearchParams(window.location.search).get('mode')) !== -1
    );

    if (isEmbed) {
        window.__MAYHEN_IS_EMBED = true;
        document.documentElement.classList.add('mayhen_embed_mode', 'mayhen_template_auto_opened');
        if (document.body) {
            document.body.classList.add('mayhen_embed_mode', 'mayhen_template_auto_opened');
            document.body.classList.remove('mayhen_invitation_scroll_locked', 'scroll_locked', 'no-scroll');
            document.body.style.overflow = 'auto';
        }

        var autoOpenInvitation = function() {
            if (document.body) {
                document.body.classList.add('mayhen_embed_mode', 'mayhen_template_auto_opened');
                document.body.classList.remove('mayhen_invitation_scroll_locked', 'scroll_locked', 'no-scroll');
                document.body.style.overflow = 'auto';
            }

            // Đánh dấu đã mở và ẩn các container mở đầu
            var doorStages = document.querySelectorAll(
                '.mayhen_invitation_opening, .mayhen_invitation_envelope, #mayhen-door-stage, [id*="door-stage"], [class*="door_stage"], #POPUP8, [class*="_sr_cua"]'
            );
            doorStages.forEach(function(el) {
                el.classList.add('opened', 'mayhen_invitation_opened', 'mayhen_door_hidden');
                el.style.setProperty('display', 'none', 'important');
            });

            // Ẩn triệt để và gỡ bỏ thanh bottom nav dock chân trang bên trong iframe
            var internalBottomNav = document.querySelectorAll('.mayhen_invitation_bottom_nav, [class*="bottom_nav"]');
            internalBottomNav.forEach(function(el) {
                el.remove();
            });

            // Tự click nút mở phong bì hoặc nút mở thiệp nếu có
            var openTriggers = document.querySelectorAll(
                '.mayhen_invitation_open_btn, #mayhen_open_invitation_btn, #BUTTON18, [id*="open_btn"]'
            );
            openTriggers.forEach(function(btn) {
                try { btn.click(); } catch(e) {}
            });

            // Gọi các hàm mở thiệp của từng template nếu có
            if (typeof window.openWeddingCeremony === 'function') {
                try { window.openWeddingCeremony(); } catch(e) {}
            }
            if (typeof window.openWeddingDoors === 'function') {
                try { window.openWeddingDoors(); } catch(e) {}
            }
            if (typeof window.openInvitation === 'function') {
                try { window.openInvitation(); } catch(e) {}
            }
            if (typeof window.openIntro === 'function') {
                try { window.openIntro(); } catch(e) {}
            }
            if (typeof window.triggerInitialAnimations === 'function') {
                try { window.triggerInitialAnimations(); } catch(e) {}
            }
            if (typeof window.syncStage === 'function') {
                try { window.syncStage(); } catch(e) {}
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', autoOpenInvitation);
        } else {
            autoOpenInvitation();
        }
        window.addEventListener('load', autoOpenInvitation);
    }

    window.__MAYHEN_LOCAL_STATE = window.__MAYHEN_LOCAL_STATE || {
        groom_name: "{{ addslashes($groomName ?? '') }}",
        bride_name: "{{ addslashes($brideName ?? '') }}",
        groom_father: "{{ addslashes($groomFather ?? '') }}",
        groom_mother: "{{ addslashes($groomMother ?? '') }}",
        bride_father: "{{ addslashes($brideFather ?? '') }}",
        bride_mother: "{{ addslashes($brideMother ?? '') }}"
    };

    var assetBase = "{{ $assetBase ?? (isset($template) ? url('/mau-thiep/' . $template->slug . '/assets') : '') }}";
    var resolveUrl = function(raw) {
        var s = (raw || '').toString().trim();
        if (!s) return '';
        if (s.startsWith('http://') || s.startsWith('https://') || s.startsWith('/') || s.startsWith('blob:') || s.startsWith('data:')) {
            return s;
        }
        var cleanPath = s.replace(/^assets\//, '').replace(/^\/+/, '');
        return assetBase ? (assetBase.replace(/\/+$/, '') + '/' + cleanPath) : s;
    };

    // Thuật toán chuyển đổi ngày Dương Lịch sang Âm Lịch Việt Nam chuẩn thiên văn (Hồ Ngọc Đức - GMT+7)
    var calcVietnameseLunar = function(solarDateStr) {
        if (!solarDateStr) return '';
        var parts = solarDateStr.toString().trim().split('-');
        if (parts.length !== 3) return '';
        var yy = parseInt(parts[0], 10);
        var mm = parseInt(parts[1], 10);
        var dd = parseInt(parts[2], 10);
        if (isNaN(yy) || isNaN(mm) || isNaN(dd)) return '';

        function INT(d) { return Math.floor(d); }
        function jdFromDate(d, m, y) {
            var a = INT((14 - m) / 12);
            var y1 = y + 4800 - a;
            var m1 = m + 12 * a - 3;
            return d + INT((153 * m1 + 2) / 5) + 365 * y1 + INT(y1 / 4) - INT(y1 / 100) + INT(y1 / 400) - 32045;
        }
        function getNewMoonDay(k, tz) {
            var T = k / 1236.85;
            var T2 = T * T;
            var T3 = T2 * T;
            var dr = Math.PI / 180;
            var Jd1 = 2415020.75933 + 29.53058868 * k + 0.0001178 * T2 - 0.000000155 * T3;
            var M = 359.2242 + 29.10535608 * k - 0.0000333 * T2 - 0.00000347 * T3;
            var Mprime = 306.0253 + 385.81691806 * k + 0.0107306 * T2 + 0.00001236 * T3;
            var F = 21.2964 + 390.67050646 * k - 0.0016528 * T2 - 0.00000239 * T3;
            var C1 = (0.1734 - 0.000393 * T) * Math.sin(M * dr) + 0.0021 * Math.sin(2 * dr * M)
                   - 0.4068 * Math.sin(Mprime * dr) + 0.0161 * Math.sin(2 * dr * Mprime)
                   - 0.0004 * Math.sin(3 * dr * Mprime) + 0.0104 * Math.sin(2 * dr * F)
                   - 0.0051 * Math.sin(dr * (M + Mprime)) - 0.0074 * Math.sin(dr * (M - Mprime))
                   + 0.0004 * Math.sin(dr * (2 * F + M)) - 0.0004 * Math.sin(dr * (2 * F - M))
                   - 0.0006 * Math.sin(dr * (2 * F + Mprime)) + 0.001 * Math.sin(dr * (2 * F - Mprime))
                   + 0.0005 * Math.sin(dr * (M + 2 * Mprime));
            return INT(Jd1 + C1 + 0.5 + tz / 24);
        }
        function getSunLongitude(jdn, tz) {
            var T = (jdn - 2451545.0 + 0.5 - tz / 24) / 36525;
            var dr = Math.PI / 180;
            var M = 357.5291 + 35999.0503 * T - 0.0001559 * T * T - 0.00000048 * T * T * T;
            var L0 = 280.46645 + 36000.76983 * T + 0.0003032 * T * T;
            var DL = (1.9146 - 0.004817 * T - 0.000014 * T * T) * Math.sin(dr * M)
                   + (0.019993 - 0.000101 * T) * Math.sin(2 * dr * M) + 0.00029 * Math.sin(3 * dr * M);
            var L = (L0 + DL) * dr;
            L = L - Math.PI * 2 * INT(L / (Math.PI * 2));
            return INT(L / (Math.PI / 6));
        }
        function getLunarMonth11(y, tz) {
            var off = jdFromDate(31, 12, y) - 2415021;
            var k = INT(off / 29.530588853);
            var nm = getNewMoonDay(k, tz);
            if (getSunLongitude(nm, tz) >= 9) {
                nm = getNewMoonDay(k - 1, tz);
            }
            return nm;
        }
        function getLeapOffset(a11, tz) {
            var k = INT((a11 - 2415021.076998695) / 29.530588853 + 0.5);
            var last = 0, i = 1, arc = getSunLongitude(getNewMoonDay(k + i, tz), tz);
            do {
                last = arc;
                i++;
                arc = getSunLongitude(getNewMoonDay(k + i, tz), tz);
            } while (arc !== last && i < 14);
            return i - 1;
        }

        var tz = 7;
        var dayNumber = jdFromDate(dd, mm, yy);
        var k = INT((dayNumber - 2415021.076998695) / 29.530588853);
        var monthStart = getNewMoonDay(k + 1, tz);
        if (monthStart > dayNumber) {
            monthStart = getNewMoonDay(k, tz);
        }
        var a11 = getLunarMonth11(yy, tz);
        var b11 = a11;
        var lunarYear = yy;
        if (a11 >= monthStart) {
            lunarYear = yy - 1;
            a11 = getLunarMonth11(yy - 1, tz);
        } else {
            b11 = getLunarMonth11(yy + 1, tz);
        }
        var lunarDay = dayNumber - monthStart + 1;
        var diff = INT((monthStart - a11) / 29);
        var lunarLeap = 0;
        var lunarMonth = diff + 11;
        if (b11 - a11 > 365) {
            var leapDiff = getLeapOffset(a11, tz);
            if (diff >= leapDiff) {
                lunarMonth = diff + 10;
                if (diff === leapDiff) lunarLeap = 1;
            }
        }
        if (lunarMonth > 12) lunarMonth -= 12;
        if (lunarMonth >= 11 && diff < 4) lunarYear -= 1;

        var canList = ['Giáp', 'Ất', 'Bính', 'Đinh', 'Mậu', 'Kỷ', 'Canh', 'Tân', 'Nhâm', 'Quý'];
        var chiList = ['Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi'];

        var canChiYearNum = (lunarMonth >= 11) ? (lunarYear + 1) : lunarYear;
        var canYear = canList[(canChiYearNum + 6) % 10];
        var chiYear = chiList[(canChiYearNum + 8) % 12];
        var leapText = lunarLeap === 1 ? ' nhuận' : '';

        var dStr = lunarDay < 10 ? '0' + lunarDay : '' + lunarDay;
        var mStr = lunarMonth < 10 ? '0' + lunarMonth : '' + lunarMonth;

        return 'Nhằm ngày ' + dStr + ' tháng ' + mStr + leapText + ' năm ' + canYear + ' ' + chiYear;
    };

    // Helper 1: Tìm phần tử hiển thị thực tế (bỏ qua triệt để màn mở đầu / cánh cửa 3D bị ẩn)
    var findVisibleTarget = function(selector) {
        if (!selector) return null;
        try {
            var list = document.querySelectorAll(selector);
            for (var i = 0; i < list.length; i++) {
                var el = list[i];
                if (el.closest('.mayhen_invitation_opening, .mayhen_invitation_envelope, #mayhen-door-stage, [id*="door-stage"], [class*="door_stage"], #POPUP8, [class*="_sr_cua"]')) {
                    continue;
                }
                var rect = el.getBoundingClientRect();
                var style = window.getComputedStyle(el);
                if (style.display !== 'none' && style.visibility !== 'hidden' && (rect.width > 0 || rect.height > 0 || el.getClientRects().length > 0)) {
                    return el;
                }
            }
            for (var j = 0; j < list.length; j++) {
                if (!list[j].closest('.mayhen_invitation_opening, .mayhen_invitation_sr_cua, [class*="opening"], [class*="door_stage"]')) {
                    return list[j];
                }
            }
            return list[0] || null;
        } catch (e) {
            return document.querySelector(selector);
        }
    };

    // Helper 2: Cuộn mượt mà độc lập, tính toán pixel chính xác 100% trong Iframe
    var smoothScrollToTarget = function(el, block) {
        if (!el) return;

        // Phá bỏ mọi lớp khóa cuộn trên html và body
        document.documentElement.classList.remove('mayhen_invitation_scroll_locked', 'scroll_locked', 'no-scroll', 'mayhen_invitation_sr_khoa');
        if (document.body) {
            document.body.classList.remove('mayhen_invitation_scroll_locked', 'scroll_locked', 'no-scroll', 'mayhen_invitation_sr_khoa');
            document.body.style.setProperty('overflow', 'visible', 'important');
            document.body.style.setProperty('overflow-y', 'visible', 'important');
        }

        // Giải phóng triệt để overflow: hidden của bất kỳ container cha nào trên cây DOM
        var p = el.parentElement;
        while (p && p !== document.body && p !== document.documentElement) {
            var cs = window.getComputedStyle(p);
            if (cs.overflow === 'hidden' || cs.overflowY === 'hidden') {
                p.style.setProperty('overflow', 'visible', 'important');
                p.style.setProperty('overflow-y', 'visible', 'important');
            }
            p = p.parentElement;
        }

        var rect = el.getBoundingClientRect();
        var currentScroll = window.pageYOffset || (document.documentElement ? document.documentElement.scrollTop : 0) || (document.body ? document.body.scrollTop : 0) || 0;
        var targetY = currentScroll + rect.top;

        if (block === 'center') {
            var winH = window.innerHeight || (document.documentElement ? document.documentElement.clientHeight : 667);
            targetY = targetY - (winH / 2) + (rect.height / 2);
        } else {
            targetY = Math.max(0, targetY - 24);
        }

        if (targetY < 0) targetY = 0;

        // Ưu tiên scrollIntoView tự nhiên của trình duyệt
        try {
            el.scrollIntoView({ behavior: 'smooth', block: block || 'center', inline: 'nearest' });
        } catch (e) {
            try {
                window.scrollTo({ top: targetY, behavior: 'smooth' });
            } catch (err) {
                window.scrollTo(0, targetY);
            }
        }

        // Dự phòng đa tầng cho mọi phiên bản trình duyệt (đặc biệt khi scrollIntoView bị can thiệp trong iframe)
        setTimeout(function() {
            var currentRect = el.getBoundingClientRect();
            var inView = currentRect.top >= -50 && currentRect.top <= (window.innerHeight || 667) * 0.85;
            if (!inView) {
                try {
                    window.scrollTo({ top: targetY, behavior: 'smooth' });
                } catch (e) {
                    window.scrollTo(0, targetY);
                }
                if (document.documentElement) {
                    try { document.documentElement.scrollTo({ top: targetY, behavior: 'smooth' }); } catch(e) { document.documentElement.scrollTop = targetY; }
                }
                if (document.body) {
                    try { document.body.scrollTo({ top: targetY, behavior: 'smooth' }); } catch(e) { document.body.scrollTop = targetY; }
                }
            }
        }, 80);
    };

    // Helper 3: Tìm kiếm section mục tiêu theo ID hoặc Step (1..10) chuẩn 10 Section của SKILL.md
    var findSectionTarget = function(secId, step) {
        secId = (secId || '').toString().toLowerCase().trim();
        step = parseInt(step, 10) || 0;

        // SECTION 1: TRANG BÌA CHÍNH (HERO COVER)
        if (secId === 'hero' || (secId === 'couple' && step <= 1) || step === 1) {
            return findVisibleTarget('.mayhen_invitation_hero, [data-mayhen-section*="hero"], [data-mayhen-section="couple"]');
        }

        var sectionMap = {
            // Section 1: Hero Cover
            'hero': ['.mayhen_invitation_hero', '[data-mayhen-section*="hero"]', '[data-mayhen-section="couple"]'],
            // Section 2: Love Story & Quote
            'story': ['.mayhen_invitation_story', '.mayhen_invitation_quote', '[data-mayhen-section*="story"]'],
            // Section 3: Family & Couple
            'parents': ['.mayhen_invitation_parents', '.mayhen_invitation_couple', '[data-mayhen-section*="parents"]', '[data-mayhen-section*="family"]'],
            'groom_family': ['.mayhen_invitation_family_groom_title', '[data-mayhen-section="groom_family"]', '.mayhen_invitation_parents'],
            'bride_family': ['.mayhen_invitation_family_bride_title', '[data-mayhen-section="bride_family"]', '.mayhen_invitation_parents'],
            'couple': ['.mayhen_invitation_couple', '.mayhen_invitation_parents', '.mayhen_invitation_hero'],
            // Section 4: Countdown & Calendar
            'countdown_calendar': ['.mayhen_invitation_countdown_calendar', '.mayhen_invitation_countdown', '.mayhen_invitation_calendar', '[data-mayhen-section*="countdown_calendar"]'],
            // Section 5: Events & Maps
            'events': ['.mayhen_invitation_events', '.mayhen_invitation_maps', '[data-mayhen-section*="events"]', '[data-mayhen-section*="wedding_event"]'],
            // Section 6: Wedding Timeline
            'timeline': ['.mayhen_invitation_timeline', '[data-mayhen-section*="timeline"]'],
            // Section 7: Dresscode
            'dresscode': ['.mayhen_invitation_dresscode', '[data-mayhen-section*="dresscode"]'],
            // Section 8: Album 10 Photos
            'album': ['.mayhen_invitation_gallery', '[data-mayhen-section*="album"]', '[data-mayhen-section*="gallery"]'],
            'gallery': ['.mayhen_invitation_gallery', '[data-mayhen-section*="gallery"]', '[data-mayhen-section*="album"]'],
            // Section 9: Wedding Gift & VietQR
            'gift': ['.mayhen_invitation_gift', '[data-mayhen-section*="gift"]'],
            // Section 10: Interactive, RSVP, Wishes & Ending
            'ending': ['.mayhen_invitation_interactive_ending', '.mayhen_invitation_ending', '.mayhen_invitation_rsvp', '.mayhen_invitation_wishes', '[data-mayhen-section*="ending"]', '[data-mayhen-section*="rsvp"]'],
            'rsvp': ['.mayhen_invitation_rsvp', '.mayhen_invitation_interactive_ending'],
            'wishes': ['.mayhen_invitation_wishes', '.mayhen_invitation_interactive_ending']
        };

        // 1. Tìm theo ánh xạ map
        if (sectionMap[secId]) {
            for (var i = 0; i < sectionMap[secId].length; i++) {
                var el = findVisibleTarget(sectionMap[secId][i]);
                if (el) return el;
            }
        }

        // 2. Tìm theo data-mayhen-section trực tiếp
        if (secId) {
            var directEl = findVisibleTarget('[data-mayhen-section="' + secId + '"], [data-mayhen-section*="' + secId + '"]');
            if (directEl) return directEl;
        }

        // 3. Fallback theo số thứ tự step (1..10)
        var standardStepSelectors = [
            '.mayhen_invitation_hero, [data-mayhen-section*="hero"], [data-mayhen-section="couple"]',
            '.mayhen_invitation_story, [data-mayhen-section*="story"]',
            '.mayhen_invitation_parents, [data-mayhen-section*="parents"], [data-mayhen-section*="family"]',
            '.mayhen_invitation_countdown_calendar, .mayhen_invitation_countdown, .mayhen_invitation_calendar, [data-mayhen-section*="countdown_calendar"]',
            '.mayhen_invitation_events, [data-mayhen-section*="events"], [data-mayhen-section*="wedding_event"]',
            '.mayhen_invitation_timeline, [data-mayhen-section*="timeline"]',
            '.mayhen_invitation_dresscode, [data-mayhen-section*="dresscode"]',
            '.mayhen_invitation_gallery, [data-mayhen-section*="album"], [data-mayhen-section*="gallery"]',
            '.mayhen_invitation_gift, [data-mayhen-section*="gift"]',
            '.mayhen_invitation_interactive_ending, .mayhen_invitation_ending, .mayhen_invitation_rsvp, .mayhen_invitation_wishes, [data-mayhen-section*="ending"]'
        ];

        if (step >= 1 && step <= standardStepSelectors.length) {
            var stepEl = findVisibleTarget(standardStepSelectors[step - 1]);
            if (stepEl) return stepEl;
        }

        return null;
    };

    // Helper 4: Tìm kiếm phần tử theo Key dữ liệu (Chuẩn 100% Class BEM & data-sync-target của SKILL.md)
    var findFieldTarget = function(rawKey, step) {
        if (!rawKey) return null;
        var cleanKey = (rawKey || '').toString().trim().replace(/^wedding\./, '').replace(/^extra_data\./, '');
        step = parseInt(step, 10) || 0;
        var targetEl = null;

        // SECTION 1: TRANG BÌA CHÍNH (HERO COVER)
        if (cleanKey === 'ceremony_type') {
            targetEl = findVisibleTarget('.mayhen_invitation_ladi_headline_ceremony_type, [data-sync-target="ceremony_type"]');
        } else if (cleanKey === 'groom_name') {
            targetEl = findVisibleTarget('.mayhen_invitation_hero .mayhen_invitation_ladi_headline_name_groom, .mayhen_invitation_groom_name, [data-sync-target="groom_name"]');
        } else if (cleanKey === 'bride_name') {
            targetEl = findVisibleTarget('.mayhen_invitation_hero .mayhen_invitation_ladi_headline_name_bride, .mayhen_invitation_bride_name, [data-sync-target="bride_name"]');
        } else if (cleanKey === 'hero_photo_url' || cleanKey === 'cover_photo_url') {
            targetEl = findVisibleTarget('.mayhen_invitation_hero_media, .mayhen_invitation_hero .mayhen_invitation_ladi_image, [data-sync-target="hero_photo_url"], [data-sync-target="cover_photo_url"]');
        } else if (cleanKey === 'wedding_date' || cleanKey === 'wedding_date_digits') {
            if (step === 4) {
                targetEl = findVisibleTarget('.mayhen_invitation_calendar_selected_day, .mayhen_invitation_calendar, .mayhen_invitation_countdown_calendar');
            } else if (step === 5) {
                targetEl = findVisibleTarget('.mayhen_invitation_party_date, .mayhen_invitation_event_card_party');
            }
            if (!targetEl) {
                targetEl = findVisibleTarget('.mayhen_invitation_ladi_headline_wedding_day, [data-sync-target="wedding_date_digits"], [data-sync-target="wedding_date"], .mayhen_invitation_calendar_selected_day');
            }
        } else if (cleanKey === 'wedding_lunar_date') {
            targetEl = findVisibleTarget('.mayhen_invitation_ladi_paragraph_lunar_calendar, [data-sync-target="wedding_lunar_date"]');
        } else if (cleanKey === 'slogan') {
            targetEl = findVisibleTarget('.mayhen_invitation_ladi_paragraph_slogan');

        // SECTION 2: LỜI NGỎ & CHUYỆN TÌNH YÊU (LOVE STORY & QUOTE)
        } else if (cleanKey === 'story_title') {
            targetEl = findVisibleTarget('.mayhen_invitation_ladi_headline_story, [data-sync-target="story_title"]');
        } else if (cleanKey === 'save_date_photo_url') {
            targetEl = findVisibleTarget('.mayhen_invitation_story_img, [data-sync-target="save_date_photo_url"]');
        } else if (cleanKey === 'story_subtitle') {
            targetEl = findVisibleTarget('.mayhen_invitation_story_subtitle, [data-sync-target="story_subtitle"]');
        } else if (cleanKey === 'couple_quote' || cleanKey === 'quote') {
            targetEl = findVisibleTarget('.mayhen_invitation_quote_text, [data-sync-target="couple_quote"]');
        } else if (cleanKey === 'story_content') {
            targetEl = findVisibleTarget('.mayhen_invitation_story_content, [data-sync-target="story_content"]');

        // SECTION 3: SONG THÂN & ĐÔI UYÊN ƯƠNG (FAMILY & COUPLE)
        } else if (cleanKey === 'groom_father') {
            targetEl = findVisibleTarget('.mayhen_invitation_family_groom_father, [data-sync-target="groom_father"]');
        } else if (cleanKey === 'groom_mother') {
            targetEl = findVisibleTarget('.mayhen_invitation_family_groom_mother, [data-sync-target="groom_mother"]');
        } else if (cleanKey === 'groom_parents_address') {
            targetEl = findVisibleTarget('.mayhen_invitation_family_groom_address, [data-sync-target="groom_parents_address"]');
        } else if (cleanKey === 'bride_father') {
            targetEl = findVisibleTarget('.mayhen_invitation_family_bride_father, [data-sync-target="bride_father"]');
        } else if (cleanKey === 'bride_mother') {
            targetEl = findVisibleTarget('.mayhen_invitation_family_bride_mother, [data-sync-target="bride_mother"]');
        } else if (cleanKey === 'bride_parents_address') {
            targetEl = findVisibleTarget('.mayhen_invitation_family_bride_address, [data-sync-target="bride_parents_address"]');
        } else if (cleanKey === 'groom_photo_url') {
            targetEl = findVisibleTarget('.mayhen_invitation_groom_photo, [data-sync-target="groom_photo_url"]');
        } else if (cleanKey === 'groom_bio') {
            targetEl = findVisibleTarget('.mayhen_invitation_groom_bio, [data-sync-target="groom_bio"]');
        } else if (cleanKey === 'bride_photo_url') {
            targetEl = findVisibleTarget('.mayhen_invitation_bride_photo, [data-sync-target="bride_photo_url"]');
        } else if (cleanKey === 'bride_bio') {
            targetEl = findVisibleTarget('.mayhen_invitation_bride_bio, [data-sync-target="bride_bio"]');

        // SECTION 4: ĐẾM NGƯỢC & LỊCH THÁNG (COUNTDOWN & CALENDAR)
        } else if (cleanKey === 'event_target_time' || cleanKey.indexOf('countdown') !== -1) {
            targetEl = findVisibleTarget('.mayhen_invitation_countdown, .mayhen_invitation_countdown_calendar');
        } else if (cleanKey === 'calendar_month_year' || cleanKey.indexOf('calendar') !== -1) {
            targetEl = findVisibleTarget('.mayhen_invitation_calendar_month_year, .mayhen_invitation_calendar, [data-sync-target="calendar_month_year"]');

        // SECTION 5: THÔNG TIN SỰ KIỆN & BẢN ĐỒ (EVENTS & MAPS)
        } else if (cleanKey === 'party_time') {
            targetEl = findVisibleTarget('.mayhen_invitation_party_time, [data-sync-target="party_time"]');
        } else if (cleanKey === 'party_venue') {
            targetEl = findVisibleTarget('.mayhen_invitation_party_venue, [data-sync-target="party_venue"]');
        } else if (cleanKey === 'event_party_address' || cleanKey === 'party_address') {
            targetEl = findVisibleTarget('.mayhen_invitation_party_address, .mayhen_invitation_map_address_text, [data-sync-target="event_party_address"]');
        } else if (cleanKey === 'ceremony_time') {
            targetEl = findVisibleTarget('.mayhen_invitation_ceremony_bride_time, [data-sync-target="ceremony_time"]');
        } else if (cleanKey === 'ceremony_address') {
            targetEl = findVisibleTarget('.mayhen_invitation_ceremony_bride_address, [data-sync-target="ceremony_address"]');
        } else if (cleanKey === 'ceremony_groom_time') {
            targetEl = findVisibleTarget('.mayhen_invitation_ceremony_groom_time, [data-sync-target="ceremony_groom_time"]');
        } else if (cleanKey === 'ceremony_groom_address') {
            targetEl = findVisibleTarget('.mayhen_invitation_ceremony_groom_address, [data-sync-target="ceremony_groom_address"]');
        } else if (cleanKey === 'event_party_map_link' || cleanKey.indexOf('map') !== -1) {
            targetEl = findVisibleTarget('.mayhen_invitation_map_box, .mayhen_invitation_map_iframe, .mayhen_invitation_map_toggle_btn');

        // SECTION 6: LỊCH TRÌNH HÔN LỄ (WEDDING TIMELINE)
        } else if (cleanKey.indexOf('timeline') !== -1) {
            var tMatch = cleanKey.match(/timeline\.(\d+)/);
            var tIdx = tMatch ? parseInt(tMatch[1], 10) : null;
            if (tIdx !== null) {
                targetEl = findVisibleTarget('[data-sync-target="timeline.' + tIdx + '.time"], [data-sync-target="timeline.' + tIdx + '.title"]');
                if (!targetEl) {
                    var tlItems = document.querySelectorAll('.mayhen_invitation_timeline_item');
                    if (tlItems && tlItems[tIdx]) targetEl = tlItems[tIdx];
                }
            }
            if (!targetEl) {
                targetEl = findVisibleTarget('.mayhen_invitation_timeline_list, .mayhen_invitation_timeline, [data-mayhen-section*="timeline"]');
            }

        // SECTION 7: QUY ĐỊNH TRANG PHỤC (DRESSCODE)
        } else if (cleanKey.indexOf('dresscode_colors') !== -1) {
            targetEl = findVisibleTarget('.mayhen_invitation_dresscode_swatches, .mayhen_invitation_dresscode');
        } else if (cleanKey === 'dresscode_note' || cleanKey.indexOf('dresscode') !== -1) {
            targetEl = findVisibleTarget('.mayhen_invitation_dresscode_note, [data-sync-target="dresscode_note"], .mayhen_invitation_dresscode');

        // SECTION 8: KHOẢNH KHẮC HẠNH PHÚC (ALBUM 10 PHOTOS)
        } else if (cleanKey.indexOf('gallery') !== -1 || cleanKey.indexOf('album') !== -1) {
            var gMatch = cleanKey.match(/(?:gallery|album|photo)[_.]?(\d+)/i);
            var gIdx = gMatch ? parseInt(gMatch[1], 10) : null;
            if (gIdx !== null) {
                targetEl = findVisibleTarget('[data-sync-target="gallery.' + gIdx + '"], .mayhen_invitation_gallery_item[data-index="' + gIdx + '"]');
                if (!targetEl) {
                    var gItems = document.querySelectorAll('.mayhen_invitation_gallery_item');
                    if (gItems && gItems[gIdx]) targetEl = gItems[gIdx];
                }
            }
            if (!targetEl) {
                targetEl = findVisibleTarget('.mayhen_invitation_gallery_wrap, .mayhen_invitation_gallery, [data-mayhen-section*="album"]');
            }

        // SECTION 9: HỘP MỪNG CƯỚI & VIETQR (WEDDING GIFT & VIETQR)
        } else if (cleanKey === 'qr_groom_image') {
            targetEl = findVisibleTarget('.mayhen_invitation_gift_qr_groom, [data-sync-target="qr_groom_image"]');
        } else if (cleanKey === 'groom_bank_name') {
            targetEl = findVisibleTarget('.mayhen_invitation_gift_bank_name_groom, [data-sync-target="groom_bank_name"]');
        } else if (cleanKey === 'groom_bank_account') {
            targetEl = findVisibleTarget('.mayhen_invitation_gift_account_number_groom, [data-sync-target="groom_bank_account"]');
        } else if (cleanKey === 'groom_account_name' || cleanKey === 'groom_bank_holder') {
            targetEl = findVisibleTarget('.mayhen_invitation_gift_account_holder_groom, [data-sync-target="groom_account_name"]');
        } else if (cleanKey === 'qr_bride_image') {
            targetEl = findVisibleTarget('.mayhen_invitation_gift_qr_bride, [data-sync-target="qr_bride_image"]');
        } else if (cleanKey === 'bride_bank_name') {
            targetEl = findVisibleTarget('.mayhen_invitation_gift_bank_name_bride, [data-sync-target="bride_bank_name"]');
        } else if (cleanKey === 'bride_bank_account') {
            targetEl = findVisibleTarget('.mayhen_invitation_gift_account_number_bride, [data-sync-target="bride_bank_account"]');
        } else if (cleanKey === 'bride_account_name' || cleanKey === 'bride_bank_holder') {
            targetEl = findVisibleTarget('.mayhen_invitation_gift_account_holder_bride, [data-sync-target="bride_account_name"]');
        } else if (cleanKey.indexOf('gift') !== -1 || cleanKey.indexOf('banking') !== -1) {
            targetEl = findVisibleTarget('.mayhen_invitation_gift_box, .mayhen_invitation_gift, [data-mayhen-section*="gift"]');

        // SECTION 10: TƯƠNG TÁC, RSVP, LƯU BÚT & CẢM ƠN
        } else if (cleanKey === 'thank_you_title') {
            targetEl = findVisibleTarget('.mayhen_invitation_ending_title, [data-sync-target="thank_you_title"]');
        } else if (cleanKey === 'thank_you_quote' || cleanKey.indexOf('thank_you') !== -1) {
            targetEl = findVisibleTarget('.mayhen_invitation_ending_text, [data-sync-target="thank_you_quote"]');
        } else if (cleanKey.indexOf('signature') !== -1) {
            targetEl = findVisibleTarget('.mayhen_invitation_signature, .mayhen_invitation_signature_open_btn');
        } else if (cleanKey.indexOf('rsvp') !== -1) {
            targetEl = findVisibleTarget('.mayhen_invitation_rsvp_form, .mayhen_invitation_rsvp');
        } else if (cleanKey.indexOf('wish') !== -1) {
            targetEl = findVisibleTarget('.mayhen_invitation_wish_form, .mayhen_invitation_wishes');
        }

        // Tự động mở hộp quà nếu đang tìm kiếm thông tin mừng cưới
        if (cleanKey.indexOf('qr') !== -1 || cleanKey.indexOf('bank') !== -1 || cleanKey.indexOf('gift') !== -1) {
            try {
                var giftBox = document.querySelector('.mayhen_invitation_gift_box, [class*="gift_box"], [class*="hop_qua"]');
                if (giftBox) giftBox.click();
                var qrWrap = document.querySelector('.mayhen_invitation_gift_card_groom, .mayhen_invitation_gift_card_bride, [class*="gift_card"], [class*="sr_qr"]');
                if (qrWrap && qrWrap.parentElement) {
                    qrWrap.parentElement.style.setProperty('display', 'flex', 'important');
                    qrWrap.parentElement.style.setProperty('opacity', '1', 'important');
                    qrWrap.parentElement.style.setProperty('visibility', 'visible', 'important');
                }
            } catch (e) {}
        }

        // Fallback: Tìm bằng data-sync-target trực tiếp
        if (!targetEl) {
            targetEl = findVisibleTarget('[data-sync-target="' + rawKey + '"], [data-sync-target="' + cleanKey + '"], [data-sync-target*="' + cleanKey + '"]');
        }

        return targetEl;
    };

    // Hàm thực thi Cuộn Section
    var executeScrollToSection = function(secId, step) {
        secId = (secId || '').toString().toLowerCase().trim();
        step = parseInt(step, 10) || 0;

        if (secId === 'hero' || (secId === 'couple' && step <= 1) || step === 1) {
            try {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (e) {
                window.scrollTo(0, 0);
            }
            if (document.documentElement) {
                try { document.documentElement.scrollTo({ top: 0, behavior: 'smooth' }); } catch(e) { document.documentElement.scrollTop = 0; }
            }
            if (document.body) {
                try { document.body.scrollTo({ top: 0, behavior: 'smooth' }); } catch(e) { document.body.scrollTop = 0; }
            }
            return;
        }

        var target = findSectionTarget(secId, step);
        if (target) {
            if (secId === 'gift' || step === 9) {
                try {
                    var giftBoxEl = document.querySelector('.mayhen_invitation_gift_box, [class*="gift_box"], [class*="hop_qua"]');
                    if (giftBoxEl) giftBoxEl.click();
                    var qrWrapEl = document.querySelector('.mayhen_invitation_gift_card_groom, .mayhen_invitation_gift_card_bride, [class*="gift_card"], [class*="sr_qr"]');
                    if (qrWrapEl && qrWrapEl.parentElement) {
                        qrWrapEl.parentElement.style.setProperty('display', 'flex', 'important');
                        qrWrapEl.parentElement.style.setProperty('opacity', '1', 'important');
                        qrWrapEl.parentElement.style.setProperty('visibility', 'visible', 'important');
                    }
                } catch (e) {}
            }
            var scrollEl = target.closest('section, article, [class*="_sec"], [class*="_card"], [class*="_flow"]') || target;
            smoothScrollToTarget(scrollEl, 'start');
        }
    };

    // Hàm thực thi Cuộn Target và Highlight
    var executeScrollToTarget = function(rawKey, action, step) {
        if (!rawKey) return;
        var targetEl = findFieldTarget(rawKey, step);

        if (targetEl) {
            smoothScrollToTarget(targetEl, 'center');

            // Gắn class highlight hiệu ứng vàng kim Quiet Luxury
            document.querySelectorAll('.mayhen_editor_target_highlight').forEach(function(el) {
                el.classList.remove('mayhen_editor_target_highlight');
            });
            void targetEl.offsetWidth; // Force reflow
            targetEl.classList.add('mayhen_editor_target_highlight');

            setTimeout(function() {
                targetEl.classList.remove('mayhen_editor_target_highlight');
            }, 2000);
        }
    };

    // Gắn NGAY LẬP TỨC lên window để trang Studio Editor cha gọi tức thời (Dual Channel Direct Call)
    window.__mayhenScrollToSection = executeScrollToSection;
    window.__mayhenScrollToTarget = executeScrollToTarget;
    window.__mayhenSmoothScroll = smoothScrollToTarget;
    window.__mayhenFindVisibleTarget = findVisibleTarget;
    window.__mayhenFindFieldTarget = findFieldTarget;
    window.__mayhenFindSectionTarget = findSectionTarget;

    window.addEventListener('message', function(event) {
        if (!event.data || typeof event.data !== 'object') return;
        var data = event.data;

        // 1. Đồng bộ Input Text & Ảnh động (Live Typing Sync)
        if (data.type === 'MAYHEN_SYNC_INPUT') {
            var key = data.key || '';
            var val = data.value !== undefined && data.value !== null ? data.value : '';

            // Chuẩn hóa key: loại bỏ wedding. hoặc extra_data.
            var cleanKey = key.replace(/^wedding\./, '').replace(/^extra_data\./, '');
            window.__MAYHEN_LOCAL_STATE[cleanKey] = val;

            // Xây dựng danh sách selector bao gồm cả alias tương thích giữa các mẫu
            var aliasList = [key, cleanKey, 'wedding.' + cleanKey, 'extra_data.' + cleanKey];
            var aliasesMap = {
                'groom_bank_account': ['groom_account_number'],
                'groom_account_number': ['groom_bank_account'],
                'bride_bank_account': ['bride_account_number'],
                'bride_account_number': ['bride_bank_account'],
                'groom_bank_holder': ['groom_account_name'],
                'groom_account_name': ['groom_bank_holder'],
                'bride_bank_holder': ['bride_account_name'],
                'bride_account_name': ['bride_bank_holder'],
                'event_party_address': ['party_address'],
                'party_address': ['event_party_address'],
                'party_venue': ['event_party_venue'],
                'event_party_venue': ['party_venue'],
                'event_party_time': ['party_time'],
                'party_time': ['event_party_time'],
                'groom_party_time': ['groom_ceremony_time'],
                'bride_party_time': ['bride_ceremony_time'],
                'groom_party_address': ['groom_ceremony_address'],
                'bride_party_address': ['bride_ceremony_address'],
                'event_party_date': ['wedding_date', 'groom_party_date', 'bride_party_date'],
                'wedding_date': ['event_party_date', 'groom_party_date', 'bride_party_date', 'wedding_date_digits'],
                'wedding_date_digits': ['wedding_date', 'event_party_date'],
                'event_party_lunar': ['groom_party_lunar', 'bride_party_lunar', 'bride_ceremony_lunar', 'wedding_lunar_date'],
                'wedding_lunar_date': ['event_party_lunar', 'groom_party_lunar', 'bride_party_lunar'],
                'hero_photo_url': ['cover_photo_url', 'save_date_photo_url'],
                'cover_photo_url': ['hero_photo_url', 'save_date_photo_url'],
                'save_date_photo_url': ['cover_photo_url', 'hero_photo_url'],
                'couple_quote': ['quote', 'story', 'love_quote'],
                'quote': ['couple_quote'],
                'story': ['couple_quote']
            };

            if (aliasesMap[cleanKey]) {
                aliasesMap[cleanKey].forEach(function(ak) {
                    aliasList.push(ak);
                    aliasList.push('wedding.' + ak);
                    aliasList.push('extra_data.' + ak);
                });
            }

            var uniqueAliases = [];
            aliasList.forEach(function(item) {
                if (uniqueAliases.indexOf(item) === -1) uniqueAliases.push(item);
            });

            var selector = uniqueAliases.map(function(k) { return '[data-sync-target="' + k + '"]'; }).join(', ');
            var targets = document.querySelectorAll(selector);

            targets.forEach(function(el) {
                var isUppercase = el.getAttribute('data-sync-transform') === 'uppercase' || 
                                  el.classList.contains('uppercase') || 
                                  el.classList.contains('ladi-headline-uppercase');
                var displayVal = isUppercase ? (val || '').toUpperCase() : val;

                if (el.tagName === 'IMG') {
                    if (val && val.indexOf('motdoi.com') === -1 && val.indexOf('ladicdn.com') === -1) {
                        var resolved = resolveUrl(val);
                        if (resolved) el.src = resolved;
                    }
                } else if (el.tagName === 'A' && cleanKey.indexOf('map_url') !== -1) {
                    try {
                        var mapUrl = new URL(val, window.location.origin);
                        if (mapUrl.protocol === 'https:' || mapUrl.protocol === 'http:') el.href = mapUrl.href;
                    } catch (e) {}
                } else if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                    el.value = val;
                } else {
                    if (cleanKey.indexOf('photo') !== -1 || cleanKey.indexOf('image') !== -1 || cleanKey.indexOf('cover') !== -1 || cleanKey.indexOf('gallery') !== -1 || cleanKey.indexOf('album') !== -1 || cleanKey.indexOf('qr') !== -1) {
                        if (val && val.indexOf('motdoi.com') === -1 && val.indexOf('ladicdn.com') === -1) {
                            var bgUrl = resolveUrl(val);
                            if (bgUrl) {
                                el.style.setProperty('background-image', 'url("' + bgUrl + '")', 'important');
                                el.style.removeProperty('display');
                                var parentEl = el.closest('.ladi-element');
                                if (parentEl) parentEl.style.removeProperty('display');
                                var parentGrp = el.closest('[id*="GROUP"]');
                                if (parentGrp) parentGrp.style.removeProperty('display');
                                var giftSection = document.querySelector('[data-mayhen-section="gift"], #SECTION27');
                                if (giftSection) giftSection.style.removeProperty('display');
                            }
                        } else if (!val && cleanKey.indexOf('qr') !== -1) {
                            el.style.setProperty('background-image', 'none', 'important');
                            var parentEl = el.closest('.ladi-element');
                            if (parentEl) parentEl.style.setProperty('display', 'none', 'important');
                            var parentGrp = el.closest('[id*="GROUP"]');
                            if (parentGrp) parentGrp.style.setProperty('display', 'none', 'important');
                        }
                    } else {
                        el.textContent = displayVal;
                    }
                }
            });

            // Đồng bộ định dạng ngày cưới nếu là wedding_date hoặc event_party_date
            if (cleanKey === 'wedding_date' || cleanKey === 'event_party_date') {
                try {
                    var d, m, y, parsedDate;
                    if (val && typeof val === 'string' && val.indexOf('-') !== -1) {
                        var parts = val.trim().split('-');
                        y = parseInt(parts[0], 10);
                        m = String(parseInt(parts[1], 10)).padStart(2, '0');
                        d = String(parseInt(parts[2], 10)).padStart(2, '0');
                        parsedDate = new Date(y, parseInt(m, 10) - 1, parseInt(d, 10));
                    } else if (val && typeof val === 'string' && val.indexOf('/') !== -1) {
                        var parts = val.trim().split('/');
                        d = String(parseInt(parts[0], 10)).padStart(2, '0');
                        m = String(parseInt(parts[1], 10)).padStart(2, '0');
                        y = parseInt(parts[2], 10);
                        parsedDate = new Date(y, parseInt(m, 10) - 1, parseInt(d, 10));
                    } else {
                        parsedDate = new Date(val);
                        if (!isNaN(parsedDate.getTime())) {
                            d = String(parsedDate.getDate()).padStart(2, '0');
                            m = String(parsedDate.getMonth() + 1).padStart(2, '0');
                            y = parsedDate.getFullYear();
                        }
                    }

                    if (parsedDate && !isNaN(parsedDate.getTime())) {
                        var weekdays = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
                        var wName = weekdays[parsedDate.getDay()] || 'Chủ Nhật';

                        document.querySelectorAll('[data-sync-target="wedding_date"], [data-sync-target="wedding_date_digits"]').forEach(function(el) {
                            var cur = el.textContent || '';
                            if (cur.indexOf('/') !== -1) {
                                el.textContent = d + '/' + m + '/' + y;
                            } else {
                                el.textContent = d + ' . ' + m + ' . ' + y;
                            }
                        });

                        document.querySelectorAll('[data-sync-target="wedding_day"]').forEach(function(el) { el.textContent = d; });
                        document.querySelectorAll('[data-sync-target="wedding_month"]').forEach(function(el) { el.textContent = m; });
                        document.querySelectorAll('[data-sync-target="wedding_year"]').forEach(function(el) { el.textContent = y; });
                        document.querySelectorAll('[data-sync-target="wedding_weekday"]').forEach(function(el) { el.textContent = wName; });
                        document.querySelectorAll('[data-sync-target="wedding_date_full"]').forEach(function(el) {
                            el.textContent = wName + ' ' + d + '.' + m + '.' + y;
                        });

                        // Cập nhật toàn bộ các tiêu đề tháng/năm của lịch cưới
                        document.querySelectorAll('[data-sync-target="calendar_month_year"], .mayhen_invitation_calendar_month_year').forEach(function(el) {
                            el.innerHTML = 'Tháng <span data-sync-target="wedding_month">' + m + '</span> · <span data-sync-target="wedding_year">' + y + '</span>';
                        });

                        // Tự động vẽ lại Lịch Cưới tương ứng tháng/năm/ngày đã chọn (Realtime Calendar Sync)
                        if (typeof window.renderMayhenWeddingCalendar === 'function') {
                            window.renderMayhenWeddingCalendar(parsedDate);
                        } else {
                            var calGrid = document.querySelector('[data-sync-target="wedding_calendar"]') ||
                                          document.querySelector('.mayhen_invitation_calendar_body') ||
                                          document.querySelector('.mayhen_invitation_calendar');
                            if (calGrid) {
                                var yr = parsedDate.getFullYear();
                                var mo = parsedDate.getMonth();
                                var tgt = parsedDate.getDate();
                                var tot = new Date(yr, mo + 1, 0).getDate();
                                var fst = (new Date(yr, mo, 1).getDay() + 6) % 7; // Chuẩn thứ Hai đầu tuần (Monday-first)
                                calGrid.innerHTML = '';
                                for (var ei = 0; ei < fst; ei++) {
                                    var spEmp = document.createElement('span');
                                    spEmp.className = 'mayhen_cal_blank mayhen_invitation_empty';
                                    calGrid.appendChild(spEmp);
                                }
                                for (var di = 1; di <= tot; di++) {
                                    var spDay = document.createElement('span');
                                    spDay.textContent = di;
                                    spDay.className = 'mayhen_cal_day' + (di === tgt ? ' mayhen_cal_active mayhen_invitation_calendar_selected_day' : '');
                                    calGrid.appendChild(spDay);
                                }
                                calGrid.dataset.date = yr + '-' + String(mo + 1).padStart(2, '0') + '-' + String(tgt).padStart(2, '0');
                            }
                        }

                        // Cập nhật đồng hồ đếm ngược (Countdown)
                        if (typeof window.updateMayhenCountdown === 'function') {
                            window.updateMayhenCountdown(parsedDate);
                        }
                    }

                        // Tự động tính và đồng bộ ngày Âm lịch nếu có thay đổi ngày cưới
                        var calculatedLunar = calcVietnameseLunar(val);
                        if (calculatedLunar) {
                            window.__MAYHEN_LOCAL_STATE['wedding_lunar_date'] = calculatedLunar;
                            document.querySelectorAll('[data-sync-target="wedding_lunar_date"], [data-sync-target="event_party_lunar"]').forEach(function(el) {
                                var curText = el.textContent || '';
                                if (curText.trim().startsWith('(') && curText.trim().endsWith(')')) {
                                    el.textContent = '(' + calculatedLunar + ')';
                                } else {
                                    el.textContent = calculatedLunar;
                                }
                            });
                        }
                } catch (e) {}
            }

            // Đồng bộ nhạc nền hôn lễ (music_url)
            if (cleanKey === 'music_url' || cleanKey === 'wedding_music_url' || cleanKey === 'music') {
                if (window.MAYHEN_INVITATION_CONFIG) {
                    window.MAYHEN_INVITATION_CONFIG.music = val;
                }
                if (typeof window.updateMayhenMusic === 'function') {
                    window.updateMayhenMusic(val);
                }
                document.querySelectorAll('audio, [data-sync-target="music_url"], [data-sync-target="wedding.music_url"]').forEach(function(a) {
                    if (a.tagName === 'AUDIO') {
                        a.src = val;
                    }
                });
            }

            // QR is supplied by a locally stored wedding asset; never fetch it from a remote image service.

            // Đồng bộ tên đôi uyên ương gộp (couple_names / opening_names)
            if (cleanKey === 'groom_name' || cleanKey === 'bride_name') {
                var g = window.__MAYHEN_LOCAL_STATE['groom_name'] || '';
                var b = window.__MAYHEN_LOCAL_STATE['bride_name'] || '';

                if (cleanKey === 'groom_name' && !g) g = val;
                if (cleanKey === 'bride_name' && !b) b = val;

                // Tự động tìm cả phần tử có class hoặc contract couple_names
                document.querySelectorAll('[data-sync-target="couple_names"]').forEach(function(el) {
                    if (el.querySelector('[data-sync-target="groom_name"]') || el.querySelector('[data-sync-target="bride_name"]')) {
                        return; // Đã có các thẻ con quản lý riêng biệt
                    }
                    var isUpper = el.getAttribute('data-sync-transform') === 'uppercase';
                    var gTxt = isUpper ? (g || 'CHÚ RỂ').toUpperCase() : (g || 'Chú Rể');
                    var bTxt = isUpper ? (b || 'CÔ DÂU').toUpperCase() : (b || 'Cô Dâu');
                    el.innerHTML = gTxt + ' <span class="mayhen_amp">&</span> ' + bTxt;
                });

                // Cập nhật Monogram
                if (cleanKey === 'groom_name') {
                    document.querySelectorAll('[data-monogram-role="groom"]').forEach(function(el) {
                        el.textContent = (val && val.trim().length > 0) ? val.trim().charAt(0).toUpperCase() : 'G';
                    });
                } else if (cleanKey === 'bride_name') {
                    document.querySelectorAll('[data-monogram-role="bride"]').forEach(function(el) {
                        el.textContent = (val && val.trim().length > 0) ? val.trim().charAt(0).toUpperCase() : 'B';
                    });
                }
            }

            // Đồng bộ cha mẹ hai bên gia đình
            if (cleanKey === 'groom_father' || cleanKey === 'groom_mother') {
                var gf = window.__MAYHEN_LOCAL_STATE['groom_father'] || '';
                var gm = window.__MAYHEN_LOCAL_STATE['groom_mother'] || '';
                var pGroom = [];
                if (gf) pGroom.push('ÔNG: ' + gf);
                if (gm) pGroom.push('BÀ: ' + gm);
                if (pGroom.length > 0) {
                    document.querySelectorAll('[data-sync-target="groom_parents"]').forEach(function(el) {
                        el.innerHTML = pGroom.join('<br>');
                    });
                }
            } else if (cleanKey === 'bride_father' || cleanKey === 'bride_mother') {
                var bf = window.__MAYHEN_LOCAL_STATE['bride_father'] || '';
                var bm = window.__MAYHEN_LOCAL_STATE['bride_mother'] || '';
                var pBride = [];
                if (bf) pBride.push('ÔNG: ' + bf);
                if (bm) pBride.push('BÀ: ' + bm);
                if (pBride.length > 0) {
                    document.querySelectorAll('[data-sync-target="bride_parents"]').forEach(function(el) {
                        el.innerHTML = pBride.join('<br>');
                    });
                }
            }
        }

        // 2. Đồng bộ Bảng Màu (Theme Palette Live Switcher)
        if (data.type === 'MAYHEN_CHANGE_THEME') {
            var palette = data.palette;
            if (palette) {
                var styleEl = document.getElementById('mayhen_live_custom_style');
                if (!styleEl) {
                    styleEl = document.createElement('style');
                    styleEl.id = 'mayhen_live_custom_style';
                    document.head.appendChild(styleEl);
                }

                var css = ':root {' +
                    (palette.bg ? '--paper: ' + palette.bg + ' !important; --paper-dim: ' + palette.bg + ' !important; --mayhen_bg_warm: ' + palette.bg + ' !important;' : '') +
                    (palette.text ? '--ink: ' + palette.text + ' !important; --mayhen_ink: ' + palette.text + ' !important;' : '') +
                    (palette.accent ? '--border: ' + palette.accent + ' !important; --mayhen_gold: ' + palette.accent + ' !important; --mayhen_gold_soft: ' + palette.accent + ' !important;' : '') +
                    (palette.primary ? '--primary: ' + palette.primary + ' !important; --primary-dark: ' + palette.primary + ' !important;' : '') +
                '}';
                styleEl.textContent = css;
            }
        }

        // 3. Đồng bộ Con Dấu Sáp Mở Thiệp
        if (data.type === 'MAYHEN_UPDATE_WAX_SEAL') {
            var waxColor = data.color || (data.wax_seal && data.wax_seal.color);
            var waxMonogram = data.monogram || (data.wax_seal && data.wax_seal.monogram);

            if (waxColor) {
                document.querySelectorAll('.mayhen_invitation_wax_seal, [class*="wax_seal"]').forEach(function(el) {
                    el.style.background = 'radial-gradient(circle at 35% 35%, ' + waxColor + ', ' + waxColor + 'cc 70%, #1c1917 100%)';
                });
            }

            if (waxMonogram !== undefined) {
                document.querySelectorAll('.mayhen_invitation_wax_initials, [class*="wax_initials"]').forEach(function(el) {
                    el.textContent = waxMonogram;
                });
            }
        }

        // 4. Sắp xếp thứ tự Section & Ẩn/Hiện
        if (data.type === 'MAYHEN_REORDER_SECTIONS') {
            var order = data.order || [];
            var hidden = data.sections_hidden || data.hidden || [];

            var container = document.querySelector('[data-mayhen-section-container]') ||
                            document.querySelector('.mayhen_invitation_ladi_wraper') ||
                            document.getElementById('mayhen_invitation_main_content');

            if (container && order.length > 0) {
                order.forEach(function(secId) {
                    var el = document.querySelector('[data-mayhen-section="' + secId + '"]');
                    if (el && el.parentNode === container) {
                        container.appendChild(el);
                    }
                });
            }

            document.querySelectorAll('[data-mayhen-section]').forEach(function(secEl) {
                var secId = secEl.getAttribute('data-mayhen-section');
                secEl.style.display = (hidden.indexOf(secId) !== -1) ? 'none' : '';
            });
        }

        // 5. Cuộn tới Section theo Tab Stepper đang chọn (10 Section Tiêu Chuẩn)
        if (data.type === 'MAYHEN_SCROLL_TO_SECTION') {
            executeScrollToSection(data.section_id, data.step);
        }

        // 5.1. Smart Scroll-on-Action: Tự động cuộn đến đúng phần tử và Highlight vàng kim Quiet Luxury
        if (data.type === 'MAYHEN_SCROLL_TO_TARGET') {
            executeScrollToTarget(data.target, data.action, data.step);
        }

        // 6. Tự động cuộn mượt mà khi hiển thị trên Showcase Card trang chủ
        if (data.type === 'MAYHEN_AUTOSCROLL_START') {
            startSmoothScroll();
        } else if (data.type === 'MAYHEN_AUTOSCROLL_STOP') {
            stopSmoothScroll();
        } else if (data.type === 'MAYHEN_SCROLL_TOP') {
            resetScrollToTop();
        }
    });

    // Hàm hỗ trợ tự cuộn chậm rãi mượt mà bên trong iframe
    var autoScrollRaf = null;
    var isScrolling = false;
    var currentScrollPos = 0;

    function resetScrollToTop() {
        isScrolling = false;
        cancelAnimationFrame(autoScrollRaf);
        currentScrollPos = 0;
        try {
            document.documentElement.style.setProperty('scroll-behavior', 'auto', 'important');
            if (document.body) document.body.style.setProperty('scroll-behavior', 'auto', 'important');
        } catch (e) {}
        window.scrollTo(0, 0);
        if (document.documentElement) document.documentElement.scrollTop = 0;
        if (document.body) document.body.scrollTop = 0;
    }

    function startSmoothScroll() {
        if (isScrolling) return;
        isScrolling = true;
        cancelAnimationFrame(autoScrollRaf);

        try {
            document.documentElement.style.setProperty('scroll-behavior', 'auto', 'important');
            if (document.body) document.body.style.setProperty('scroll-behavior', 'auto', 'important');
        } catch (e) {}

        currentScrollPos = window.scrollY || (document.documentElement ? document.documentElement.scrollTop : 0) || (document.body ? document.body.scrollTop : 0) || 0;
        var speed = 2.2; // Tốc độ trôi êm ái, rõ ràng (~130px/s)

        function step() {
            if (!isScrolling) return;

            var docH = Math.max(
                document.body ? document.body.scrollHeight : 0,
                document.documentElement ? document.documentElement.scrollHeight : 0,
                document.body ? document.body.offsetHeight : 0
            );
            var winH = window.innerHeight || 667;
            var maxScroll = Math.max(0, docH - winH);

            // Nếu trang có nội dung dài hơn màn hình (maxScroll > 60) và vị trí hiện tại đã tới sát đáy trang
            if (maxScroll > 60 && currentScrollPos >= maxScroll - 6) {
                isScrolling = false;
                return;
            }

            currentScrollPos += speed;
            window.scrollTo(0, currentScrollPos);
            if (document.documentElement) document.documentElement.scrollTop = currentScrollPos;
            if (document.body) document.body.scrollTop = currentScrollPos;

            autoScrollRaf = requestAnimationFrame(step);
        }
        autoScrollRaf = requestAnimationFrame(step);
    }

    function stopSmoothScroll() {
        isScrolling = false;
        cancelAnimationFrame(autoScrollRaf);
        try {
            document.documentElement.style.removeProperty('scroll-behavior');
            if (document.body) document.body.style.removeProperty('scroll-behavior');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (e) {
            window.scrollTo(0, 0);
            if (document.documentElement) document.documentElement.scrollTop = 0;
            if (document.body) document.body.scrollTop = 0;
        }
        currentScrollPos = 0;
    }

    // Đưa ra window để trang cha có thể gọi trực tiếp
    window.__mayhenStartSmoothScroll = startSmoothScroll;
    window.__mayhenStopSmoothScroll = stopSmoothScroll;

    // Bắt tay 2 chiều: Thông báo cho Studio Editor biết Iframe đã nạp xong và sẵn sàng nhận dữ liệu
    var notifyParentReady = function() {
        try {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'MAYHEN_BRIDGE_READY' }, '*');
            }
        } catch (e) {}
    };

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        notifyParentReady();
    } else {
        document.addEventListener('DOMContentLoaded', notifyParentReady);
    }
    window.addEventListener('load', notifyParentReady);
})();
</script>
