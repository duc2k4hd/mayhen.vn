{{-- 
    MAYHEN.VN — INVITATION EDITOR REALTIME BRIDGE
    Bộ kết nối chuẩn hóa 2 chiều giữa Studio Editor V2 và tất cả các Template thiệp cưới (Native Vanilla JS).
--}}

{{-- QUY TẮC TOÀN CỤC: TỰ ĐỘNG MỞ SẴN NỘI DUNG THIỆP MỜI TRONG IFRAME (ZERO-FRICTION AUTO-OPENED PROTOCOL) --}}
<style id="mayhen_global_auto_opened_styles">
    /* 1. Mở khóa cuộn toàn trang, tắt scroll-behavior smooth để JS cuộn mượt và ẨN HOÀN TOÀN thanh scrollbar trong iframe */
    html.mayhen_embed_mode,
    body.mayhen_embed_mode,
    html.mayhen_template_auto_opened,
    body.mayhen_template_auto_opened,
    .mayhen_embed_mode * {
        scroll-behavior: auto !important;
    }

    html.mayhen_embed_mode,
    body.mayhen_embed_mode,
    html.mayhen_template_auto_opened,
    body.mayhen_template_auto_opened {
        overflow-y: auto !important;
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
    .mayhen_embed_mode #mayhen-door-stage,
    .mayhen_embed_mode [id*="door-stage"],
    .mayhen_embed_mode [class*="door_stage"],
    .mayhen_embed_mode #POPUP8,
    .mayhen_embed_mode .ladi-popup:has(#BUTTON18),
    .mayhen_embed_mode #mayhen_invitation_ladjihe38udhd_opening,
    .mayhen_embed_mode .mayhen_invitation_ksnvo949dudf_opening,
    .mayhen_embed_mode .mayhen_invitation_djcvhwiuo9w9_opening-sides,
    .mayhen_embed_mode .mayhen_invitation_ladoifji894yfv_cover,
    .mayhen_embed_mode .mayhen_invitation_ladoifji894yfv_sr_cua,
    .mayhen_embed_mode [class*="_sr_cua"],
    .mayhen_embed_mode .mayhen_invitation_dhsuudushi9428r_open_cta,
    .mayhen_embed_mode .mayhen_invitation_dhsuudushi9428r_scroll_down,
    .mayhen_embed_mode .mayhen_invitation_dhsuudushi9428r_wax_seal,
    .mayhen_embed_mode [class*="open_hint"],
    .mayhen_embed_mode [id*="open-hint"],
    .mayhen_embed_mode [class*="door_particles"] {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* 3. Tự động mở phong bì cho nét mực tình yêu khi nhúng iframe */
    .mayhen_embed_mode .mayhen_invitation_dhsuudushi9428r_envelope_letter {
        transform: translateY(-95px) scale(1.02) !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.25) !important;
        z-index: 2 !important;
    }
    .mayhen_embed_mode .mayhen_invitation_dhsuudushi9428r_envelope_stage {
        margin: 160px auto 18px auto !important;
    }

    /* 4. Cưỡng chế hiển thị nội dung chính của thiệp cưới và bỏ bóng đổ thừa */
    .mayhen_embed_mode .mayhen_invitation_wrapper,
    .mayhen_embed_mode [class*="_shell"],
    .mayhen_embed_mode .mayhen_invitation_jeowinids483fr_stage,
    .mayhen_embed_mode .mayhen_invitation_jeowinids483fr_stage-spacer,
    .mayhen_embed_mode .mayhen_invitation_ladjihe38udhd_ladi-wraper,
    .mayhen_embed_mode .ladi-wraper,
    .mayhen_embed_mode [class*="_card-wrapper"],
    .mayhen_embed_mode #mayhen_invitation_main_content {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        box-shadow: none !important;
    }

    /* 5. Cưỡng chế các animation khởi đầu hiển thị ngay lập tức (không bị ẩn trắng khi chưa cuộn) */
    .mayhen_embed_mode .ladi-animation-hidden,
    .mayhen_embed_mode [class*="ladi-animation-hidden"],
    .mayhen_embed_mode [class*="_rv"],
    .mayhen_embed_mode .mayhen_invitation_ladjihe38udhd_box_anim,
    .mayhen_embed_mode .mayhen_invitation_ladoifji894yfv_sr_hien {
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
    }

    /* 6. Ẩn tuyệt đối 100% thanh điều hướng bottom nav dock khi nhúng trong iframe xem mẫu hoặc editor */
    .mayhen_embed_mode [class*="bottom_nav_wrapper"],
    body.mayhen_embed_mode [class*="bottom_nav_wrapper"],
    [data-in-iframe="true"] [class*="bottom_nav_wrapper"] {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
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
            var doorStages = document.querySelectorAll('#mayhen-door-stage, [id*="door-stage"], [class*="door_stage"], [class*="opening_sides"], [id*="opening_sides"], #POPUP8, #mayhen_invitation_ladjihe38udhd_opening, .mayhen_invitation_ksnvo949dudf_opening, .mayhen_invitation_ladoifji894yfv_cover, .mayhen_invitation_ladoifji894yfv_sr_cua, [class*="_sr_cua"]');
            doorStages.forEach(function(el) {
                el.classList.add('opened', 'mayhen_door_hidden', 'mayhen_invitation_ladoifji894yfv_da_mo');
                el.style.setProperty('display', 'none', 'important');
            });

            // Ẩn triệt để và gỡ bỏ thanh bottom nav dock chân trang bên trong iframe
            var internalBottomNav = document.querySelectorAll('[class*="bottom_nav_wrapper"]');
            internalBottomNav.forEach(function(el) {
                el.remove();
            });

            // Tự click nút mở phong bì hoặc nút mở thiệp nếu có
            var openTriggers = document.querySelectorAll(
                '#mayhen_open_invitation_btn, #BUTTON18, [data-action="true"]#BUTTON18, [id*="open_btn"], [class*="open_btn"]'
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
                'hero_photo_url': ['cover_photo_url'],
                'cover_photo_url': ['hero_photo_url'],
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
                    if (cleanKey.indexOf('photo') !== -1 || cleanKey.indexOf('image') !== -1 || cleanKey.indexOf('cover') !== -1 || cleanKey.indexOf('gallery') !== -1 || cleanKey.indexOf('album') !== -1) {
                        if (val && val.indexOf('motdoi.com') === -1 && val.indexOf('ladicdn.com') === -1) {
                            var bgUrl = resolveUrl(val);
                            if (bgUrl) el.style.setProperty('background-image', 'url("' + bgUrl + '")', 'important');
                        }
                    } else {
                        el.textContent = displayVal;
                    }
                }
            });

            // Đồng bộ định dạng ngày cưới nếu là wedding_date hoặc event_party_date
            if (cleanKey === 'wedding_date' || cleanKey === 'event_party_date') {
                try {
                    var parsedDate = new Date(val);
                    if (!isNaN(parsedDate.getTime())) {
                        var d = String(parsedDate.getDate()).padStart(2, '0');
                        var m = String(parsedDate.getMonth() + 1).padStart(2, '0');
                        var y = parsedDate.getFullYear();
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

                        // Tự động vẽ lại Lịch Cưới tương ứng tháng/năm/ngày đã chọn (Realtime Calendar Sync)
                        if (typeof window.renderMayhenWeddingCalendar === 'function') {
                            window.renderMayhenWeddingCalendar(parsedDate);
                        } else {
                            var calGrid = document.querySelector('.mayhen_invitation_ladoifji894yfv_cal_days') || document.querySelector('[data-sync-target="wedding_calendar"]');
                            if (calGrid) {
                                var yr = parsedDate.getFullYear();
                                var mo = parsedDate.getMonth();
                                var tgt = parsedDate.getDate();
                                var tot = new Date(yr, mo + 1, 0).getDate();
                                var fst = new Date(yr, mo, 1).getDay();
                                var emp = (fst === 0) ? 6 : (fst - 1);
                                calGrid.innerHTML = '';
                                for (var ei = 0; ei < emp; ei++) {
                                    var spEmp = document.createElement('span');
                                    spEmp.className = 'mayhen_invitation_ladoifji894yfv_cal_empty';
                                    calGrid.appendChild(spEmp);
                                }
                                for (var di = 1; di <= tot; di++) {
                                    var spDay = document.createElement('span');
                                    spDay.textContent = di;
                                    if (di === tgt) {
                                        spDay.className = 'mayhen_invitation_ladoifji894yfv_cal_target';
                                    }
                                    calGrid.appendChild(spDay);
                                }
                                calGrid.dataset.date = yr + '-' + String(mo + 1).padStart(2, '0') + '-' + String(tgt).padStart(2, '0');
                            }
                            var calMonth = document.querySelector('.mayhen_invitation_ladoifji894yfv_cal_month');
                            if (calMonth) {
                                calMonth.innerHTML = 'Tháng <span data-sync-target="wedding_month">' + m + '</span> · <span data-sync-target="wedding_year">' + y + '</span>';
                            }
                        }

                        // Cập nhật đồng hồ đếm ngược (Countdown)
                        if (typeof window.updateMayhenCountdown === 'function') {
                            window.updateMayhenCountdown(parsedDate);
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
                    }
                } catch (e) {}
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
                document.querySelectorAll('.mayhen_invitation_djcvhwiuo9w9_seal-pulse-glow').forEach(function(el) {
                    el.style.boxShadow = '0 0 0 10px ' + waxColor + '33, 0 0 0 20px ' + waxColor + '1a';
                });
                document.querySelectorAll('.mayhen_invitation_dhsuudushi9428r_wax_seal').forEach(function(el) {
                    el.style.background = 'radial-gradient(circle at 35% 35%, ' + waxColor + ', ' + waxColor + 'cc 70%, #1c1917 100%)';
                });
            }

            if (waxMonogram !== undefined) {
                document.querySelectorAll('.mayhen_invitation_dhsuudushi9428r_wax_initials').forEach(function(el) {
                    el.textContent = waxMonogram;
                });
            }
        }

        // 4. Sắp xếp thứ tự Section & Ẩn/Hiện
        if (data.type === 'MAYHEN_REORDER_SECTIONS') {
            var order = data.order || [];
            var hidden = data.sections_hidden || data.hidden || [];

            var container = document.querySelector('[data-mayhen-section-container]') ||
                            document.querySelector('.mayhen_invitation_djcvhwiuo9w9_card-wrapper') ||
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

        // 5. Cuộn tới Section theo Tab Stepper đang chọn
        if (data.type === 'MAYHEN_SCROLL_TO_SECTION') {
            document.documentElement.classList.remove('mayhen_invitation_scroll_locked', 'scroll_locked', 'no-scroll');
            if (document.body) {
                document.body.classList.remove('mayhen_invitation_scroll_locked', 'scroll_locked', 'no-scroll');
                document.body.style.overflow = 'auto';
            }

            var secId = data.section_id || '';
            if (secId === 'couple') {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            var target = null;
            // 1. Tìm bằng thuộc tính section trực tiếp
            target = document.querySelector('[data-mayhen-section="' + secId + '"], [data-mayhen-section*="' + secId + '"]');

            // 2. Smart selector mapping đại diện cho 6 phần chuẩn nếu mẫu chưa gắn data-mayhen-section
            if (!target) {
                if (secId === 'groom_family') {
                    target = document.querySelector('[data-sync-target="groom_father"], [data-sync-target="groom_party_address"], [data-sync-target="groom_party_time"], [class*="groom_family"]');
                } else if (secId === 'bride_family') {
                    target = document.querySelector('[data-sync-target="bride_father"], [data-sync-target="bride_party_address"], [data-sync-target="bride_party_time"], [class*="bride_family"]');
                } else if (secId === 'album') {
                    target = document.querySelector('[data-sync-target="gallery.0"], [data-sync-target*="gallery"], [class*="flow"], [class*="gallery"], [class*="album"]');
                } else if (secId === 'timeline') {
                    target = document.querySelector('[data-sync-target="timeline.0.time"], [data-sync-target*="timeline"], [class*="timeline"], [class*="schedule"]');
                } else if (secId === 'gift') {
                    target = document.querySelector('[data-sync-target="qr_groom_image"], [data-sync-target="groom_bank_holder"], [data-sync-target="groom_bank_account"], [class*="gift"], [class*="qr"]');
                }
            }

            if (target) {
                // Ưu tiên cuộn tới khối section hoặc card cha để hiển thị đẹp mắt
                var scrollEl = target.closest('section, article, [class*="_sec"], [class*="_card"], [class*="_flow"]') || target;
                scrollEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
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
