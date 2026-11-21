/**
 * Hẹn Cưới Vào Mùa Xuân — Glass Garden Green Vanilla JS Engine
 * Zero external libraries. Handles frosted glass opening, audio toggle,
 * dynamic botanical heart calendar, countdown, scroll reveal, 10-photo Lightbox,
 * 3D Glass Garden Gift Box VietQR, Signature Canvas, RSVP, Wishes & Live Sync.
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

    /* ---- 2. GLASS OPENING CURTAIN ---- */
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
            revealVisibleNow();
        }, 560);
    }

    if (isEmbed || (opening && opening.classList.contains('is-opened'))) {
        unlockScroll();
    } else if (opening) {
        document.body.classList.add('mayhen_invitation_scroll_locked');
        if (openBtn) {
            openBtn.addEventListener('click', openInvitation);
        }
        opening.addEventListener('click', function (e) {
            if (e.target === opening) {
                openInvitation();
            }
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
    }, 900);

    /* ---- 4. DYNAMIC BOTANICAL CALENDAR & COUNTDOWN ---- */
    var calGrid = document.getElementById('mayhen_cal_grid');
    var calTitleEl = document.getElementById('mayhen_cal_title');
    var cdDays = document.getElementById('mayhen_cd_days');
    var cdHours = document.getElementById('mayhen_cd_hours');
    var cdMins = document.getElementById('mayhen_cd_mins');
    var cdSecs = document.getElementById('mayhen_cd_secs');
    var countdownTimer = null;

    function pad2(n) { return n < 10 ? '0' + n : String(n); }

    function renderCalendar(dateStr) {
        if (!calGrid) { return; }
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(dateStr || '').trim());
        if (!m) { return; }
        var year = parseInt(m[1], 10);
        var month = parseInt(m[2], 10);
        var selectedDay = parseInt(m[3], 10);

        if (calTitleEl) {
            calTitleEl.textContent = 'Tháng ' + month + ' / ' + year;
        }

        var firstDay = new Date(year, month - 1, 1);
        var startCol = (firstDay.getDay() + 6) % 7;
        var daysInMonth = new Date(year, month, 0).getDate();

        var html = '';
        for (var i = 0; i < startCol; i++) {
            html += '<span class="mayhen_invitation_cal_cell is-empty" aria-hidden="true"></span>';
        }
        for (var d = 1; d <= daysInMonth; d++) {
            var isSel = d === selectedDay;
            html += '<span class="mayhen_invitation_cal_cell' + (isSel ? ' is-selected' : '') + '">' + d + '</span>';
        }
        calGrid.innerHTML = html;
    }

    function startCountdown(isoStr) {
        if (countdownTimer) { window.clearInterval(countdownTimer); }
        var targetMs = Date.parse(isoStr);
        if (isNaN(targetMs)) { return; }
        function tick() {
            var diff = Math.max(0, targetMs - Date.now());
            var totalSec = Math.floor(diff / 1000);
            var d = Math.floor(totalSec / 86400);
            var h = Math.floor((totalSec % 86400) / 3600);
            var mi = Math.floor((totalSec % 3600) / 60);
            var s = totalSec % 60;
            if (cdDays) { cdDays.textContent = pad2(d); }
            if (cdHours) { cdHours.textContent = pad2(h); }
            if (cdMins) { cdMins.textContent = pad2(mi); }
            if (cdSecs) { cdSecs.textContent = pad2(s); }
        }
        tick();
        countdownTimer = window.setInterval(tick, 1000);
    }

    renderCalendar(root.dataset.weddingDate || '2026-08-02');
    startCountdown(root.dataset.targetIso || '2026-08-02T11:30:00+07:00');

    /* ---- 5. SMOOTH SCROLL TO RSVP ---- */
    var jumpRsvpBtn = document.getElementById('mayhen_jump_rsvp');
    var rsvpSection = document.getElementById('mayhen_rsvp_box');
    if (jumpRsvpBtn && rsvpSection) {
        jumpRsvpBtn.addEventListener('click', function () {
            rsvpSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }

    /* ---- 6. 10-PHOTO LIGHTBOX ---- */
    var lightbox = document.getElementById('mayhen_lightbox');
    var lbImg = document.getElementById('mayhen_lb_img');
    var lbCounter = document.getElementById('mayhen_lb_counter');
    var lbClose = document.getElementById('mayhen_lb_close');
    var lbPrev = document.getElementById('mayhen_lb_prev');
    var lbNext = document.getElementById('mayhen_lb_next');
    var galleryButtons = Array.prototype.slice.call(root.querySelectorAll('.mayhen_invitation_album_item'));
    var lbIndex = 0;

    function getGalleryUrls() {
        return galleryButtons.map(function (btn) {
            var img = btn.querySelector('img');
            return img ? img.src : '';
        }).filter(Boolean);
    }

    function openLightbox(idx) {
        var urls = getGalleryUrls();
        if (!lightbox || !lbImg || urls.length === 0) { return; }
        lbIndex = (idx + urls.length) % urls.length;
        lbImg.src = urls[lbIndex];
        if (lbCounter) { lbCounter.textContent = (lbIndex + 1) + ' / ' + urls.length; }
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
    }

    function closeLightbox() {
        if (!lightbox) { return; }
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
    }

    galleryButtons.forEach(function (btn, idx) {
        btn.addEventListener('click', function () { openLightbox(idx); });
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
    var sigInk = '#35452f';
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
                sigInk = dot.getAttribute('data-sig-color') || '#35452f';
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
                    card.className = 'mayhen_invitation_sig_card';
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

    /* ---- 9. RSVP FORM ---- */
    var rsvpForm = document.getElementById('mayhen_rsvp_form');
    var rsvpStatus = document.getElementById('mayhen_rsvp_status');
    if (rsvpForm) {
        rsvpForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(rsvpForm);
            var payload = {
                guest_name: String(fd.get('guest_name') || '').trim(),
                attending_status: String(fd.get('attending_status') || 'attending'),
                adults_count: parseInt(String(fd.get('adults_count') || '1'), 10) || 1,
                children_count: parseInt(String(fd.get('children_count') || '0'), 10) || 0,
                wishes: String(fd.get('wishes') || '').trim()
            };
            if (!payload.guest_name) {
                if (rsvpStatus) { rsvpStatus.textContent = 'Vui lòng nhập họ và tên của bạn.'; }
                return;
            }
            if (!rsvpEndpoint) {
                if (rsvpStatus) { rsvpStatus.textContent = 'Đã ghi nhận phản hồi của ' + payload.guest_name + '!'; }
                return;
            }
            var submitBtn = rsvpForm.querySelector('button[type="submit"]');
            if (submitBtn) { submitBtn.disabled = true; }
            fetch(rsvpEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            }).then(function (r) {
                return r.json().then(function (data) { return { ok: r.ok, data: data }; });
            }).then(function (res) {
                if (res.ok) {
                    if (rsvpStatus) { rsvpStatus.textContent = res.data.message || 'Cảm ơn quý khách đã xác nhận tham dự!'; }
                    rsvpForm.reset();
                } else {
                    if (rsvpStatus) { rsvpStatus.textContent = res.data.message || 'Vui lòng kiểm tra lại thông tin.'; }
                }
            }).catch(function () {
                if (rsvpStatus) { rsvpStatus.textContent = 'Có lỗi kết nối, vui lòng thử lại sau.'; }
            }).finally(function () {
                if (submitBtn) { submitBtn.disabled = false; }
            });
        });
    }

    /* ---- 10. WISHES FORM ---- */
    var wishForm = document.getElementById('mayhen_wish_form');
    var wishList = document.getElementById('mayhen_wish_list');
    var wishStatus = document.getElementById('mayhen_wish_status');
    if (wishForm) {
        wishForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(wishForm);
            var name = String(fd.get('name') || '').trim();
            var message = String(fd.get('message') || '').trim();
            if (!name || !message) {
                if (wishStatus) { wishStatus.textContent = 'Vui lòng nhập tên và lời chúc phúc.'; }
                return;
            }
            function prependWish(author, msg) {
                if (!wishList) { return; }
                var empty = wishList.querySelector('.mayhen_invitation_wish_empty');
                if (empty) { empty.remove(); }
                var item = document.createElement('article');
                item.className = 'mayhen_invitation_wish_item';
                var h = document.createElement('strong');
                h.textContent = author;
                var p = document.createElement('p');
                p.textContent = msg;
                item.appendChild(h);
                item.appendChild(p);
                wishList.prepend(item);
            }
            if (!wishEndpoint) {
                prependWish(name, message);
                if (wishStatus) { wishStatus.textContent = 'Cảm ơn lời chúc tốt đẹp của ' + name + '!'; }
                wishForm.reset();
                return;
            }
            var btn = wishForm.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; }
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
                if (wishStatus) { wishStatus.textContent = 'Đã gửi lời chúc tới đôi uyên ương!'; }
                wishForm.reset();
            }).catch(function () {
                if (wishStatus) { wishStatus.textContent = 'Không thể gửi lời chúc lúc này, vui lòng thử lại.'; }
            }).finally(function () {
                if (btn) { btn.disabled = false; }
            });
        });
    }

    /* ---- 11. STUDIO LIVE SYNC BRIDGE ---- */
    window.addEventListener('message', function (event) {
        var msg = event.data;
        if (!msg || typeof msg !== 'object') { return; }
        var payload = msg.payload || msg.data || msg;
        if (payload.groom_name) {
            root.querySelectorAll('[data-sync-target="groom_name"]').forEach(function (el) {
                el.textContent = String(payload.groom_name);
            });
        }
        if (payload.bride_name) {
            root.querySelectorAll('[data-sync-target="bride_name"]').forEach(function (el) {
                el.textContent = String(payload.bride_name);
            });
        }
        if (payload.wedding_date && /^\d{4}-\d{2}-\d{2}$/.test(String(payload.wedding_date))) {
            renderCalendar(payload.wedding_date);
            var t = payload.event_target_time || payload.party_time || '11:30';
            startCountdown(payload.wedding_date + 'T' + t + ':00+07:00');
        }
        if (Array.isArray(payload.dresscode_colors)) {
            var swatchWrap = root.querySelector('[data-sync-target="dresscode_colors"]');
            if (swatchWrap) {
                swatchWrap.innerHTML = '';
                payload.dresscode_colors.forEach(function (hex) {
                    var sw = document.createElement('span');
                    sw.className = 'mayhen_invitation_dresscode_swatch';
                    sw.style.backgroundColor = String(hex);
                    swatchWrap.appendChild(sw);
                });
            }
        }
    });
})();
