{{-- 
    MAYHEN.VN — INVITATION EDITOR REALTIME BRIDGE
    Bộ kết nối chuẩn hóa 2 chiều giữa Studio Editor V2 và tất cả các Template thiệp cưới (Native Vanilla JS).
--}}

{{-- QUY TẮC TOÀN CỤC: TỰ ĐỘNG MỞ SẴN NỘI DUNG THIỆP MỜI TRONG IFRAME (ZERO-FRICTION AUTO-OPENED PROTOCOL) --}}
<style id="mayhen_global_auto_opened_styles">
    /* 1. Mở khóa cuộn toàn trang khi nhúng trong iframe (Showcase trang chủ, Studio Editor, Preview) */
    html.mayhen_embed_mode,
    body.mayhen_embed_mode,
    html.mayhen_template_auto_opened,
    body.mayhen_template_auto_opened {
        overflow-y: auto !important;
        overflow-x: hidden !important;
        height: auto !important;
        position: static !important;
    }

    /* 2. Cưỡng chế ẩn triệt để 100% tất cả các màn che, cánh cửa 3D, phong bì, con dấu sáp mở đầu của MỌI template */
    .mayhen_embed_mode [id*="door-stage"],
    .mayhen_embed_mode [class*="door_stage"],
    .mayhen_embed_mode [id*="door-center"],
    .mayhen_embed_mode [class*="door_center"],
    .mayhen_embed_mode [class*="door_left"],
    .mayhen_embed_mode [class*="door_right"],
    .mayhen_embed_mode [id*="wax-seal"],
    .mayhen_embed_mode [class*="wax_seal_wrap"],
    .mayhen_embed_mode [class*="wax_seal"],
    .mayhen_embed_mode [class*="_cover"],
    .mayhen_embed_mode [class*="intro_cover"],
    .mayhen_embed_mode [class*="cover_overlay"],
    .mayhen_embed_mode [class*="_envelope_stage"],
    .mayhen_embed_mode [class*="envelope_wrap"],
    .mayhen_embed_mode [class*="envelope_wrapper"],
    .mayhen_embed_mode [class*="_opening_sides"],
    .mayhen_embed_mode [class*="opening_overlay"],
    .mayhen_embed_mode [class*="_intro_wrapper"],
    .mayhen_embed_mode [class*="_modal_intro"],
    .mayhen_embed_mode [id*="opening_sides"],
    .mayhen_embed_mode [id*="opening"],
    .mayhen_embed_mode [id*="intro_cover"],
    .mayhen_embed_mode [id*="mayhen_envelope"],
    .mayhen_embed_mode [class*="open_hint"],
    .mayhen_embed_mode [id*="open-hint"],
    .mayhen_embed_mode [class*="door_particles"],
    .mayhen_embed_mode [class*="modal-backdrop"],
    .mayhen_embed_mode [class*="section_popup"] {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* 3. Cưỡng chế hiển thị nội dung chính của thiệp cưới và bỏ bóng đổ thừa */
    .mayhen_embed_mode .mayhen_invitation_wrapper,
    .mayhen_embed_mode [class*="_shell"],
    .mayhen_embed_mode [class*="_stage"],
    .mayhen_embed_mode [class*="_stage-spacer"],
    .mayhen_embed_mode [class*="_ladi-wraper"],
    .mayhen_embed_mode [class*="_card-wrapper"],
    .mayhen_embed_mode #mayhen_invitation_main_content,
    .mayhen_embed_mode [id*="stage"] {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        box-shadow: none !important;
    }

    /* 4. Cưỡng chế các animation khởi đầu hiển thị ngay lập tức để không bị mảng trắng khi iframe nạp */
    .mayhen_embed_mode [class*="ladi-animation-hidden"],
    .mayhen_embed_mode [class*="_rv"],
    .mayhen_embed_mode [class*="ladi-animation"] {
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

            // Đánh dấu đã mở lên các container mở đầu
            var doorStages = document.querySelectorAll('[id*="door-stage"], [class*="door_stage"], [class*="opening_sides"], [id*="opening_sides"]');
            doorStages.forEach(function(el) {
                el.classList.add('opened', 'mayhen_door_hidden');
                el.style.display = 'none';
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
                'event_party_address': ['party_address', 'ceremony_address', 'groom_party_address', 'bride_party_address'],
                'party_address': ['event_party_address', 'groom_party_address'],
                'ceremony_address': ['event_party_address', 'groom_ceremony_address'],
                'groom_party_address': ['event_party_address', 'party_address'],
                'bride_party_address': ['event_party_address', 'party_address'],
                'event_party_time': ['party_time', 'ceremony_time', 'groom_party_time', 'bride_party_time'],
                'party_time': ['event_party_time', 'groom_party_time'],
                'event_party_date': ['wedding_date', 'groom_party_date', 'bride_party_date'],
                'wedding_date': ['event_party_date', 'groom_party_date', 'bride_party_date'],
                'event_party_lunar': ['groom_party_lunar', 'bride_party_lunar', 'bride_ceremony_lunar'],
                'hero_photo_url': ['cover_photo_url'],
                'cover_photo_url': ['hero_photo_url']
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
                    if (cleanKey.indexOf('photo') !== -1 || cleanKey.indexOf('image') !== -1 || cleanKey.indexOf('cover') !== -1) {
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
                        document.querySelectorAll('[data-sync-target="wedding_date"]').forEach(function(el) {
                            var cur = el.textContent || '';
                            if (cur.indexOf('.') !== -1) {
                                el.textContent = d + ' . ' + m + ' . ' + y;
                            } else if (cur.indexOf('/') !== -1) {
                                el.textContent = d + '/' + m + '/' + y;
                            }
                        });
                    }
                } catch (e) {}
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

        // 5. Cuộn tới Section
        if (data.type === 'MAYHEN_SCROLL_TO_SECTION') {
            var openingSides = document.getElementById('mayhen_invitation_djcvhwiuo9w9_opening_sides');
            if (openingSides && !openingSides.classList.contains('mayhen_invitation_djcvhwiuo9w9_opened')) {
                openingSides.classList.add('mayhen_invitation_djcvhwiuo9w9_opened');
            }
            document.body.classList.remove('mayhen_invitation_scroll_locked');

            var target = document.querySelector('[data-mayhen-section="' + data.section_id + '"]');
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });

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
