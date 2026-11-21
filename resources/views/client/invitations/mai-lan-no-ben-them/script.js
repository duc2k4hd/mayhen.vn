/**
 * Mai Lan Nở Bên Thềm — Jasmine Green Vanilla JS Engine
 * Zero external libraries. Handles watercolor opening, audio toggle, scroll reveal,
 * dynamic gold-heart calendar, countdown, 10-photo Lightbox, 3D Jasmine Gift Box
 * VietQR, Signature Canvas, RSVP, Wishes & Studio Live Sync.
 */
(function () {
    'use strict';

    var root = document.getElementById('mayhen_invitation_root');
    if (!root) {
        return;
    }

    var isEmbed = root.dataset.embed === '1' || window.__MAYHEN_IS_EMBED === true;
    var isPreview = root.dataset.preview === '1';
    var csrfToken = root.dataset.csrf || '';
    var rsvpEndpoint = root.dataset.rsvpUrl || '';
    var wishEndpoint = root.dataset.wishUrl || '';
    var sigEndpoint = root.dataset.signatureUrl || '';

    /* ---- 1. AUDIO CONTROLLER ---- */
    var audio = document.querySelector('[data-mayhen-audio]');
    var musicBtn = document.getElementById('mayhen_music_toggle');
    var isPlaying = false;

    function syncMusicUi() {
        if (!musicBtn) { return; }
        musicBtn.classList.toggle('is-playing', isPlaying);
        musicBtn.setAttribute('aria-pressed', isPlaying ? 'true' : 'false');
    }

    function tryPlayMusic() {
        if (!audio || isEmbed) { return; }
        var p = audio.play();
        if (p && typeof p.then === 'function') {
            p.then(function () {
                isPlaying = true;
                syncMusicUi();
            }).catch(function () {
                isPlaying = false;
                syncMusicUi();
            });
        }
    }

    if (musicBtn && audio) {
        musicBtn.addEventListener('click', function () {
            if (isPlaying) {
                audio.pause();
                isPlaying = false;
                syncMusicUi();
            } else {
                tryPlayMusic();
            }
        });
    }

    /* ---- 2. JASMINE OPENING CURTAIN ---- */
    var opening = document.getElementById('mayhen_opening');
    var openBtn = document.getElementById('mayhen_open_btn');

    function unlockScroll() {
        document.body.classList.remove('mayhen_invitation_scroll_locked');
        document.documentElement.classList.remove('mayhen_invitation_scroll_locked');
    }

    function openInvitation() {
        if (!opening || opening.classList.contains('is-opened')) {
            unlockScroll();
            return;
        }
        opening.classList.add('is-fading');
        unlockScroll();
        tryPlayMusic();
        window.setTimeout(function () {
            opening.classList.add('is-opened');
            root.classList.add('mayhen_invitation_root--opened');
            revealVisibleNow();
        }, 560);
    }

    if (isEmbed || (opening && opening.classList.contains('is-opened'))) {
        unlockScroll();
    } else if (opening) {
        document.body.classList.add('mayhen_invitation_scroll_locked');
        if (openBtn) { openBtn.addEventListener('click', openInvitation); }
        opening.addEventListener('click', function (e) {
            if (e.target === opening) { openInvitation(); }
        });
    }

    /* ---- 3. SCROLL REVEAL ---- */
    var revealEls = Array.prototype.slice.call(root.querySelectorAll('.mayhen_invitation_reveal'));

    function revealVisibleNow() {
        if (isEmbed || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            revealEls.forEach(function (el) { el.classList.add('mayhen_invitation_visible'); });
            return;
        }
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('mayhen_invitation_visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.06, rootMargin: '60px 0px 60px 0px' });
            revealEls.forEach(function (el) { io.observe(el); });
        } else {
            revealEls.forEach(function (el) { el.classList.add('mayhen_invitation_visible'); });
        }
    }

    revealVisibleNow();
    window.setTimeout(function () {
        revealEls.forEach(function (el) {
            if (el.getBoundingClientRect().top < window.innerHeight + 120) {
                el.classList.add('mayhen_invitation_visible');
            }
        });
    }, 300);

    /* ---- 4. DYNAMIC JASMINE HEART CALENDAR ---- */
    var calTitle = document.getElementById('mayhen_cal_title');
    var calGrid = document.getElementById('mayhen_cal_grid');

    function renderCalendar(dateStr) {
        if (!calGrid) { return; }
        var heartUrl = calGrid.dataset.heartUrl || '';
        var parts = String(dateStr || '').split('-');
        var y = parseInt(parts[0], 10) || 2027;
        var m = parseInt(parts[1], 10) || 8;
        var d = parseInt(parts[2], 10) || 2;

        if (calTitle) { calTitle.textContent = 'Tháng ' + m + ' / ' + y; }

        var firstDay = new Date(y, m - 1, 1).getDay();
        var startOffset = (firstDay + 6) % 7;
        var daysInMonth = new Date(y, m, 0).getDate();

        calGrid.innerHTML = '';
        for (var i = 0; i < startOffset; i++) {
            var empty = document.createElement('div');
            empty.className = 'mayhen_invitation_cal_cell';
            calGrid.appendChild(empty);
        }
        for (var day = 1; day <= daysInMonth; day++) {
            var cell = document.createElement('div');
            if (day === d) {
                cell.className = 'mayhen_invitation_cal_cell mayhen_invitation_cal_cell--wedding';
                cell.innerHTML = '<span class="mayhen_invitation_cal_heart"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg><span>' + day + '</span></span>';
            } else {
                cell.className = 'mayhen_invitation_cal_cell';
                cell.textContent = String(day);
            }
            calGrid.appendChild(cell);
        }
    }

    renderCalendar(root.dataset.weddingDate);

    /* ---- 5. COUNTDOWN TIMER ---- */
    var cdRoot = document.getElementById('mayhen_countdown');
    var targetMs = Date.parse(root.dataset.targetIso || '2027-08-02T19:00:00+07:00') || (Date.now() + 86400000 * 30);

    function tickCountdown() {
        if (!cdRoot) { return; }
        var diff = Math.max(0, targetMs - Date.now());
        var days = Math.floor(diff / 86400000);
        var hours = Math.floor((diff % 86400000) / 3600000);
        var mins = Math.floor((diff % 3600000) / 60000);
        var secs = Math.floor((diff % 60000) / 1000);

        var dEl = cdRoot.querySelector('[data-cd="days"]');
        var hEl = cdRoot.querySelector('[data-cd="hours"]');
        var mEl = cdRoot.querySelector('[data-cd="minutes"]');
        var sEl = cdRoot.querySelector('[data-cd="seconds"]');
        if (dEl) { dEl.textContent = String(days).padStart(2, '0'); }
        if (hEl) { hEl.textContent = String(hours).padStart(2, '0'); }
        if (mEl) { mEl.textContent = String(mins).padStart(2, '0'); }
        if (sEl) { sEl.textContent = String(secs).padStart(2, '0'); }
    }

    tickCountdown();
    window.setInterval(tickCountdown, 1000);

    /* ---- 6. 10-PHOTO LIGHTBOX ---- */
    var albumItems = Array.prototype.slice.call(root.querySelectorAll('.mayhen_invitation_album_item'));
    var lightbox = document.getElementById('mayhen_lightbox');
    var lbImg = document.getElementById('mayhen_lb_img');
    var lbCounter = document.getElementById('mayhen_lb_counter');
    var lbClose = document.getElementById('mayhen_lb_close');
    var lbPrev = document.getElementById('mayhen_lb_prev');
    var lbNext = document.getElementById('mayhen_lb_next');
    var lbIndex = 0;

    function openLightbox(idx) {
        var total = albumItems.length;
        if (!lightbox || !lbImg || total === 0) { return; }
        lbIndex = ((idx % total) + total) % total;
        var img = albumItems[lbIndex].querySelector('img');
        if (img && img.src) { lbImg.src = img.src; }
        if (lbCounter) { lbCounter.textContent = (lbIndex + 1) + ' / ' + total; }
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
    }

    function closeLightbox() {
        if (!lightbox) { return; }
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
    }

    albumItems.forEach(function (item, idx) {
        item.addEventListener('click', function () { openLightbox(idx); });
    });
    if (lbClose) { lbClose.addEventListener('click', closeLightbox); }
    if (lbPrev) { lbPrev.addEventListener('click', function () { openLightbox(lbIndex - 1); }); }
    if (lbNext) { lbNext.addEventListener('click', function () { openLightbox(lbIndex + 1); }); }
    if (lightbox) {
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) { closeLightbox(); }
        });
    }
    document.addEventListener('keydown', function (e) {
        if (!lightbox || !lightbox.classList.contains('is-open')) { return; }
        if (e.key === 'Escape') { closeLightbox(); }
        if (e.key === 'ArrowLeft') { openLightbox(lbIndex - 1); }
        if (e.key === 'ArrowRight') { openLightbox(lbIndex + 1); }
    });

    /* ---- 7. 3D GIFT BOX TOGGLE & COPY STK ---- */
    var giftBtn = document.getElementById('mayhen_gift_toggle');
    var giftPanel = document.getElementById('mayhen_gift_panel');
    if (giftBtn && giftPanel) {
        giftBtn.addEventListener('click', function () {
            var expanded = giftPanel.classList.toggle('is-open');
            giftBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            giftPanel.setAttribute('aria-hidden', expanded ? 'false' : 'true');
        });
    }

    root.querySelectorAll('[data-copy-account]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetKey = btn.getAttribute('data-copy-account');
            var accEl = root.querySelector('[data-sync-target="' + targetKey + '"]');
            var text = accEl ? accEl.textContent.trim() : '';
            if (!text) { return; }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).catch(function () {});
            }
            var orig = btn.textContent;
            btn.textContent = 'Đã sao chép ✓';
            window.setTimeout(function () { btn.textContent = orig; }, 1800);
        });
    });

    /* ---- 8. SIGNATURE CANVAS ---- */
    var sigCanvas = document.getElementById('mayhen_sig_canvas');
    var sigNameInput = document.getElementById('mayhen_sig_name');
    var sigClearBtn = document.getElementById('mayhen_sig_clear');
    var sigDownloadBtn = document.getElementById('mayhen_sig_download');
    var sigSaveBtn = document.getElementById('mayhen_sig_save');
    var sigWall = document.getElementById('mayhen_sig_wall');
    var sigStatus = document.getElementById('mayhen_sig_status');
    var sigPlaceholder = document.getElementById('mayhen_sig_placeholder');
    var sigInk = '#2e4733';
    var sigHasInk = false;

    if (sigCanvas) {
        var ctx = sigCanvas.getContext('2d');
        var drawing = false;

        function resizeCanvas() {
            var ratio = Math.min(window.devicePixelRatio || 1, 2);
            var rect = sigCanvas.getBoundingClientRect();
            if (rect.width < 10) { return; }
            sigCanvas.width = Math.round(rect.width * ratio);
            sigCanvas.height = Math.round(rect.height * ratio);
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.lineWidth = 2.6;
            ctx.strokeStyle = sigInk;
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        function getPoint(e) {
            var rect = sigCanvas.getBoundingClientRect();
            var pt = e.touches ? e.touches[0] : e;
            return { x: pt.clientX - rect.left, y: pt.clientY - rect.top };
        }

        function startDraw(e) {
            e.preventDefault();
            drawing = true;
            sigHasInk = true;
            if (sigPlaceholder) { sigPlaceholder.style.display = 'none'; }
            var pt = getPoint(e);
            ctx.strokeStyle = sigInk;
            ctx.beginPath();
            ctx.moveTo(pt.x, pt.y);
        }

        function moveDraw(e) {
            if (!drawing) { return; }
            e.preventDefault();
            var pt = getPoint(e);
            ctx.lineTo(pt.x, pt.y);
            ctx.stroke();
        }

        function endDraw() { drawing = false; }

        sigCanvas.addEventListener('mousedown', startDraw);
        sigCanvas.addEventListener('mousemove', moveDraw);
        window.addEventListener('mouseup', endDraw);
        sigCanvas.addEventListener('touchstart', startDraw, { passive: false });
        sigCanvas.addEventListener('touchmove', moveDraw, { passive: false });
        sigCanvas.addEventListener('touchend', endDraw);

        root.querySelectorAll('[data-sig-color]').forEach(function (dot) {
            dot.addEventListener('click', function () {
                sigInk = dot.getAttribute('data-sig-color') || '#2e4733';
                root.querySelectorAll('[data-sig-color]').forEach(function (d) {
                    d.classList.toggle('is-active', d === dot);
                });
            });
        });

        if (sigClearBtn) {
            sigClearBtn.addEventListener('click', function () {
                ctx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
                sigHasInk = false;
                if (sigPlaceholder) { sigPlaceholder.style.display = ''; }
            });
        }

        if (sigDownloadBtn) {
            sigDownloadBtn.addEventListener('click', function () {
                if (!sigHasInk) { return; }
                var a = document.createElement('a');
                a.href = sigCanvas.toDataURL('image/png');
                a.download = 'chu-ky-luu-but.png';
                a.click();
            });
        }

        if (sigSaveBtn) {
            sigSaveBtn.addEventListener('click', function () {
                var guestName = sigNameInput ? sigNameInput.value.trim() : '';
                if (!guestName) {
                    if (sigStatus) { sigStatus.textContent = 'Vui lòng nhập họ tên của bạn trước khi lưu chữ ký.'; }
                    return;
                }
                if (!sigHasInk) {
                    if (sigStatus) { sigStatus.textContent = 'Vui lòng ký tên lên khung giấy trước khi lưu.'; }
                    return;
                }
                var dataUrl = sigCanvas.toDataURL('image/png');
                function appendCard(name, imgSrc) {
                    if (!sigWall) { return; }
                    var empty = sigWall.querySelector('.mayhen_invitation_sig_empty');
                    if (empty) { empty.remove(); }
                    var card = document.createElement('div');
                    card.className = 'mayhen_invitation_sig_item';
                    var img = document.createElement('img');
                    img.src = imgSrc;
                    img.alt = name;
                    var cap = document.createElement('span');
                    cap.textContent = name;
                    card.appendChild(img);
                    card.appendChild(cap);
                    sigWall.prepend(card);
                }

                if (isPreview || !sigEndpoint) {
                    appendCard(guestName, dataUrl);
                    if (sigStatus) { sigStatus.textContent = 'Đã lưu chữ ký kỷ niệm của ' + guestName + ' (chế độ xem thử).'; }
                    return;
                }

                sigSaveBtn.disabled = true;
                fetch(sigEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ guest_name: guestName, signature_data: dataUrl })
                }).then(function (r) {
                    if (!r.ok) { throw new Error('HTTP ' + r.status); }
                    return r.json();
                }).then(function () {
                    appendCard(guestName, dataUrl);
                    if (sigStatus) { sigStatus.textContent = 'Cảm ơn ' + guestName + ' đã lưu lại chữ ký kỷ niệm!'; }
                }).catch(function () {
                    if (sigStatus) { sigStatus.textContent = 'Không thể lưu chữ ký lúc này, vui lòng thử lại.'; }
                }).finally(function () {
                    sigSaveBtn.disabled = false;
                });
            });
        }
    }

    /* ---- 9. RSVP & WISHES FORMS ---- */
    var rsvpForm = document.getElementById('mayhen_rsvp_form');
    var rsvpStatus = document.getElementById('mayhen_rsvp_status');
    var rsvpSubmit = document.getElementById('mayhen_rsvp_submit');

    if (rsvpForm) {
        rsvpForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(rsvpForm);
            var guestName = String(fd.get('guest_name') || '').trim();
            if (!guestName) {
                if (rsvpStatus) { rsvpStatus.textContent = 'Vui lòng nhập họ tên của bạn.'; }
                return;
            }
            if (isPreview || !rsvpEndpoint) {
                if (rsvpStatus) { rsvpStatus.textContent = 'Cảm ơn ' + guestName + ' đã xác nhận tham dự (chế độ xem thử).'; }
                return;
            }
            if (rsvpSubmit) { rsvpSubmit.disabled = true; }
            fetch(rsvpEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    guest_name: guestName,
                    attending_status: fd.get('attending_status') || 'attending',
                    adults_count: parseInt(fd.get('adults_count') || '1', 10),
                    children_count: parseInt(fd.get('children_count') || '0', 10),
                    wishes: String(fd.get('wishes') || '').trim()
                })
            }).then(function (r) {
                if (!r.ok) { throw new Error('HTTP ' + r.status); }
                return r.json();
            }).then(function () {
                if (rsvpStatus) { rsvpStatus.textContent = 'Cảm ơn ' + guestName + '! Xác nhận của bạn đã được ghi nhận.'; }
                rsvpForm.reset();
            }).catch(function () {
                if (rsvpStatus) { rsvpStatus.textContent = 'Có lỗi xảy ra, vui lòng thử lại sau ít phút.'; }
            }).finally(function () {
                if (rsvpSubmit) { rsvpSubmit.disabled = false; }
            });
        });
    }

    var wishForm = document.getElementById('mayhen_wish_form');
    var wishName = document.getElementById('mayhen_wish_name');
    var wishMsg = document.getElementById('mayhen_wish_msg');
    var wishList = document.getElementById('mayhen_wish_list');
    var wishStatus = document.getElementById('mayhen_wish_status');
    var wishSubmit = document.getElementById('mayhen_wish_submit');

    if (wishForm) {
        wishForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var name = wishName ? wishName.value.trim() : '';
            var message = wishMsg ? wishMsg.value.trim() : '';
            if (!name || !message) {
                if (wishStatus) { wishStatus.textContent = 'Vui lòng nhập đầy đủ tên và lời chúc.'; }
                return;
            }

            function prependWish(n, m) {
                if (!wishList) { return; }
                var art = document.createElement('article');
                art.className = 'mayhen_invitation_wish_item';
                var st = document.createElement('strong');
                st.textContent = n;
                var p = document.createElement('p');
                p.textContent = m;
                art.appendChild(st);
                art.appendChild(p);
                wishList.prepend(art);
            }

            if (isPreview || !wishEndpoint) {
                prependWish(name, message);
                if (wishStatus) { wishStatus.textContent = 'Cảm ơn ' + name + ' đã gửi lời chúc (chế độ xem thử).'; }
                wishForm.reset();
                return;
            }

            if (wishSubmit) { wishSubmit.disabled = true; }
            fetch(wishEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ name: name, message: message })
            }).then(function (r) {
                if (!r.ok) { throw new Error('HTTP ' + r.status); }
                return r.json();
            }).then(function () {
                prependWish(name, message);
                if (wishStatus) { wishStatus.textContent = 'Lời chúc của bạn đã được lưu lại!'; }
                wishForm.reset();
            }).catch(function () {
                if (wishStatus) { wishStatus.textContent = 'Không thể gửi lời chúc lúc này, vui lòng thử lại.'; }
            }).finally(function () {
                if (wishSubmit) { wishSubmit.disabled = false; }
            });
        });
    }

    /* ---- 10. STUDIO LIVE SYNC BRIDGE LISTENER ---- */
    window.addEventListener('message', function (ev) {
        var data = ev && ev.data;
        if (!data || typeof data !== 'object') { return; }
        if (data.type !== 'MAYHEN_EDITOR_SYNC' && data.type !== 'MAYHEN_SYNC_DATA') { return; }
        var payload = data.payload || data.data || {};
        Object.keys(payload).forEach(function (key) {
            var val = payload[key];
            if (val === null || val === undefined) { return; }
            if (key === 'wedding_date' && /^\d{4}-\d{2}-\d{2}$/.test(String(val))) {
                renderCalendar(String(val));
                targetMs = Date.parse(String(val) + 'T19:00:00+07:00') || targetMs;
                tickCountdown();
            }
            root.querySelectorAll('[data-sync-target="' + key + '"]').forEach(function (el) {
                if (el.tagName === 'IMG') {
                    if (typeof val === 'string' && val.trim() !== '') { el.src = val; }
                } else if (el.tagName === 'A') {
                    if (typeof val === 'string' && val.trim() !== '') { el.href = val; }
                } else if (typeof val === 'string' || typeof val === 'number') {
                    el.textContent = String(val);
                }
            });
        });
    });
})();
