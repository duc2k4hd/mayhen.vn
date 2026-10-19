{{-- 
    MAYHEN.VN — INVITATION EDITOR REALTIME BRIDGE
    Bộ kết nối chuẩn hóa giữa Studio Editor V2 và tất cả các Template thiệp cưới (Native Vanilla JS).
    Contract: Tất cả các section trong mọi mẫu thiệp chỉ cần mang attribute [data-mayhen-section="{section_id}"].
--}}

<script>
(function() {
    'use strict';

    window.addEventListener('message', function(event) {
        if (!event.data || typeof event.data !== 'object') return;
        var data = event.data;

        // 1. Đồng bộ Input Text & Ảnh động (Live Typing Sync)
        if (data.type === 'MAYHEN_SYNC_INPUT') {
            var key = data.key;
            var val = data.value !== undefined && data.value !== null ? data.value : '';

            // Tự động tìm mọi phần tử có contract data-sync-target="{field_key}"
            // Hỗ trợ cả key dạng phẳng 'wedding.groom_name' hoặc rút gọn 'groom_name'
            var cleanKey = key.replace(/^wedding\./, '').replace(/^extra_data\./, '');
            var targets = document.querySelectorAll('[data-sync-target="' + key + '"], [data-sync-target="' + cleanKey + '"]');
            
            var assetBase = "{{ $assetBase ?? (isset($template) ? url('/mau-thiep/' . $template->slug . '/assets') : '') }}";
            var resolveUrl = function(raw) {
                var s = (raw || '').toString().trim();
                if (!s) return '';
                if (s.startsWith('http://') || s.startsWith('https://') || s.startsWith('/') || s.startsWith('blob:')) {
                    return s;
                }
                return assetBase ? (assetBase.replace(/\/+$/, '') + '/' + s.replace(/^\/+/, '')) : s;
            };

            targets.forEach(function(el) {
                if (el.tagName === 'IMG') {
                    if (val) el.src = resolveUrl(val);
                } else if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                    el.value = val;
                } else {
                    if (cleanKey.indexOf('photo') !== -1 || cleanKey.indexOf('image') !== -1 || cleanKey.indexOf('cover') !== -1) {
                        el.style.backgroundImage = 'url("' + resolveUrl(val) + '")';
                    } else {
                        el.textContent = val;
                    }
                }
            });

            // Tương thích ngược với các selector Monogram của template
            if (cleanKey === 'groom_name') {
                document.querySelectorAll('[data-monogram-role="groom"]').forEach(function(el) {
                    el.textContent = (val && val.trim().length > 0) ? val.trim().charAt(0).toUpperCase() : '';
                });
            } else if (cleanKey === 'bride_name') {
                document.querySelectorAll('[data-monogram-role="bride"]').forEach(function(el) {
                    el.textContent = (val && val.trim().length > 0) ? val.trim().charAt(0).toUpperCase() : '';
                });
            }

            // Tự động ghép lại khối hiển thị Phụ mẫu cho các template dùng khối văn bản chung (như net-muc-tinh-yeu)
            if (cleanKey === 'groom_father' || cleanKey === 'groom_mother') {
                var gfEl = document.querySelector('[data-sync-target="groom_father"]');
                var gmEl = document.querySelector('[data-sync-target="groom_mother"]');
                var gfVal = (cleanKey === 'groom_father') ? val : (gfEl ? gfEl.textContent : '');
                var gmVal = (cleanKey === 'groom_mother') ? val : (gmEl ? gmEl.textContent : '');
                var combinedGroom = 'ÔNG: ' + (gfVal || 'Nguyễn Tràng Giang') + '<br>BÀ: ' + (gmVal || 'Nguyễn Thị Tám');
                document.querySelectorAll('[data-sync-target="groom_parents"]').forEach(function(el) {
                    el.innerHTML = combinedGroom;
                });
            } else if (cleanKey === 'bride_father' || cleanKey === 'bride_mother') {
                var bfEl = document.querySelector('[data-sync-target="bride_father"]');
                var bmEl = document.querySelector('[data-sync-target="bride_mother"]');
                var bfVal = (cleanKey === 'bride_father') ? val : (bfEl ? bfEl.textContent : '');
                var bmVal = (cleanKey === 'bride_mother') ? val : (bmEl ? bmEl.textContent : '');
                var combinedBride = 'ÔNG: ' + (bfVal || 'Đỗ Như Trung') + '<br>BÀ: ' + (bmVal || 'Lê Thị Hảo');
                document.querySelectorAll('[data-sync-target="bride_parents"]').forEach(function(el) {
                    el.innerHTML = combinedBride;
                });
            }
        }

        // 2. Đồng bộ 6 Bảng Màu (Theme Palette Live Switcher)
        if (data.type === 'MAYHEN_CHANGE_THEME') {
            var palette = data.palette;
            if (palette) {
                var styleEl = document.getElementById('mayhen_live_custom_style');
                if (!styleEl) {
                    styleEl = document.createElement('style');
                    styleEl.id = 'mayhen_live_custom_style';
                    document.head.appendChild(styleEl);
                }

                // Tiêm biến CSS chuẩn cho cả 2 hệ thống token của các mẫu thiệp
                var css = ':root {' +
                    (palette.bg ? '--paper: ' + palette.bg + ' !important; --paper-dim: ' + palette.bg + ' !important; --mayhen_bg_warm: ' + palette.bg + ' !important;' : '') +
                    (palette.text ? '--ink: ' + palette.text + ' !important; --mayhen_ink: ' + palette.text + ' !important;' : '') +
                    (palette.accent ? '--border: ' + palette.accent + ' !important; --mayhen_gold: ' + palette.accent + ' !important; --mayhen_gold_soft: ' + palette.accent + ' !important;' : '') +
                    (palette.primary ? '--primary: ' + palette.primary + ' !important; --primary-dark: ' + palette.primary + ' !important;' : '') +
                '}';
                styleEl.textContent = css;
            }
        }

        // 3. Đồng bộ Con Dấu Sáp Mở Thiệp (Wax Seal Color & Monogram)
        if (data.type === 'MAYHEN_UPDATE_WAX_SEAL') {
            var waxColor = data.color;
            var waxMonogram = data.monogram;

            if (waxColor) {
                var sealPulse = document.querySelectorAll('.mayhen_invitation_djcvhwiuo9w9_seal-pulse-glow');
                sealPulse.forEach(function(el) {
                    el.style.boxShadow = '0 0 0 10px ' + waxColor + '33, 0 0 0 20px ' + waxColor + '1a';
                });

                var waxSeals = document.querySelectorAll('.mayhen_invitation_dhsuudushi9428r_wax_seal');
                waxSeals.forEach(function(el) {
                    el.style.background = 'radial-gradient(circle at 35% 35%, ' + waxColor + ', ' + waxColor + 'cc 70%, #1c1917 100%)';
                });
            }

            if (waxMonogram !== undefined) {
                var initials = document.querySelectorAll('.mayhen_invitation_dhsuudushi9428r_wax_initials');
                initials.forEach(function(el) {
                    el.textContent = waxMonogram;
                });
            }
        }

        // 4. Sắp xếp thứ tự & Ẩn/Hiện Section hàng loạt (Section Reorder & Hidden State)
        if (data.type === 'MAYHEN_REORDER_SECTIONS') {
            var order = data.order || [];
            var hidden = data.hidden || [];

            // Tìm container chứa các section (tự động nhận diện cho mọi template)
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

            // Cập nhật trạng thái ẩn/hiện dựa trên Contract chuẩn
            document.querySelectorAll('[data-mayhen-section]').forEach(function(secEl) {
                var secId = secEl.getAttribute('data-mayhen-section');
                if (hidden.indexOf(secId) !== -1) {
                    secEl.style.display = 'none';
                } else {
                    secEl.style.display = '';
                }
            });
        }

        // 5. Ẩn/Hiện từng Section riêng lẻ (Section Toggle)
        if (data.type === 'MAYHEN_TOGGLE_SECTION') {
            var targetSec = document.querySelector('[data-mayhen-section="' + data.section_id + '"]');
            if (targetSec) {
                targetSec.style.display = data.visible ? '' : 'none';
            }
        }

        // 6. Tự động cuộn đến Section khi bấm chuyển Tab nhập liệu trong Studio (Auto-Scroll)
        if (data.type === 'MAYHEN_SCROLL_TO_SECTION') {
            // Tự động mở cánh phong bì nếu đang ở trạng thái đóng
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
})();
</script>
