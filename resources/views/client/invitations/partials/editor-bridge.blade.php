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
        return assetBase ? (assetBase.replace(/\/+$/, '') + '/' + s.replace(/^\/+/, '')) : s;
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
                'event_party_address': ['party_address', 'ceremony_address', 'groom_party_address', 'bride_party_address'],
                'party_address': ['event_party_address', 'groom_party_address', 'bride_party_address'],
                'ceremony_address': ['event_party_address', 'groom_ceremony_address'],
                'groom_party_address': ['event_party_address', 'party_address'],
                'bride_party_address': ['event_party_address', 'party_address'],
                'event_party_time': ['party_time', 'ceremony_time', 'groom_party_time', 'bride_party_time'],
                'party_time': ['event_party_time', 'groom_party_time', 'bride_party_time'],
                'groom_party_time': ['party_time', 'event_party_time'],
                'bride_party_time': ['party_time', 'event_party_time'],
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
                    if (val) {
                        var resolved = resolveUrl(val);
                        if (resolved) el.src = resolved;
                    }
                } else if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                    el.value = val;
                } else {
                    if (cleanKey.indexOf('photo') !== -1 || cleanKey.indexOf('image') !== -1 || cleanKey.indexOf('cover') !== -1 || cleanKey.indexOf('gallery') !== -1 || cleanKey.indexOf('album') !== -1) {
                        if (val) {
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
                    }
                } catch (e) {}
            }

            // Tự động làm mới VietQR nếu thay đổi số tài khoản hoặc ngân hàng
            if (cleanKey === 'groom_bank_account' || cleanKey === 'groom_bank_name') {
                var bName = (window.__MAYHEN_LOCAL_STATE['groom_bank_name'] || 'vietcombank').toLowerCase().replace(/\s+/g, '');
                var bAcc = (window.__MAYHEN_LOCAL_STATE['groom_bank_account'] || '').replace(/\s+/g, '');
                var qrEl = document.querySelector('[data-sync-target="qr_groom_image"]');
                if (qrEl && bAcc) {
                    qrEl.src = 'https://img.vietqr.io/image/' + encodeURIComponent(bName) + '-' + encodeURIComponent(bAcc) + '-compact2.svg';
                }
            }
            if (cleanKey === 'bride_bank_account' || cleanKey === 'bride_bank_name') {
                var bName = (window.__MAYHEN_LOCAL_STATE['bride_bank_name'] || 'techcombank').toLowerCase().replace(/\s+/g, '');
                var bAcc = (window.__MAYHEN_LOCAL_STATE['bride_bank_account'] || '').replace(/\s+/g, '');
                var qrEl = document.querySelector('[data-sync-target="qr_bride_image"]');
                if (qrEl && bAcc) {
                    qrEl.src = 'https://img.vietqr.io/image/' + encodeURIComponent(bName) + '-' + encodeURIComponent(bAcc) + '-compact2.svg';
                }
            }

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
