/**
 * Mây Hẹn (mayhen.vn) — Native ES Module Controller
 * Template: "Bản Nhạc Không Đoạn Kết" (ban-nhac-khong-doan-ket)
 * Source Lock: https://s.27biggroup.com/wedding21
 */

const initBanNhacKhongDoanKet = () => {
    const root = document.getElementById('mayhen_invitation_root');
    if (!root) {
        return;
    }

    const isEmbed = root.dataset.isEmbed === 'true'
        || window.__MAYHEN_IS_EMBED === true
        || window.location.search.includes('embed=1')
        || window.location.search.includes('mode=editor');

    const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const csrfToken = root.dataset.csrfToken
        || window.__MAYHEN_CSRF_TOKEN
        || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || '';

    // =========================================================================
    // 1. TOAST HELPER
    // =========================================================================
    const toastEl = document.getElementById('mayhenToast');
    let toastTimer = null;
    const showToast = (msg) => {
        if (!toastEl || !msg) return;
        toastEl.textContent = msg;
        toastEl.classList.add('is-visible');
        if (toastTimer) window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => toastEl.classList.remove('is-visible'), 3000);
    };

    // =========================================================================
    // 2. AUDIO & SPINNING VINYL RECORD SYNC
    // =========================================================================
    const audioEl = document.getElementById('mayhenBgAudio');
    const musicBtn = document.getElementById('mayhenMusicBtn');
    const vinylWrap = document.getElementById('w21VinylWrap');
    const vinylBtn = document.getElementById('w21Loa');

    const syncAudioUI = (playing) => {
        const isPlaying = Boolean(playing);
        musicBtn?.classList.toggle('is-playing', isPlaying);
        musicBtn?.setAttribute('aria-pressed', isPlaying ? 'true' : 'false');
        vinylWrap?.classList.toggle('is-paused', !isPlaying);
    };

    const toggleMusic = () => {
        if (!audioEl) return;
        if (audioEl.paused) {
            audioEl.play().then(() => syncAudioUI(true)).catch(() => {
                syncAudioUI(false);
                showToast('Chạm lại để phát nhạc nền hôn lễ.');
            });
        } else {
            audioEl.pause();
            syncAudioUI(false);
        }
    };

    const tryStartMusic = () => {
        if (!audioEl || isEmbed) return;
        audioEl.play().then(() => syncAudioUI(true)).catch(() => syncAudioUI(false));
    };

    musicBtn?.addEventListener('click', toggleMusic);
    vinylBtn?.addEventListener('click', toggleMusic);

    // =========================================================================
    // 3. TWO-DOOR GLASS-ARCH OPENING CURTAIN (#w21Mo)
    // =========================================================================
    const openingEl = document.getElementById('w21Mo');
    const openBtn = document.getElementById('w21NutMo');

    const unlockScroll = () => {
        document.documentElement.classList.remove('w21-khoa', 'no-scroll');
        document.body.classList.remove('w21-khoa', 'no-scroll');
    };

    if (isEmbed || !openingEl) {
        unlockScroll();
        root.classList.add('opened');
        if (openingEl) {
            openingEl.classList.add('is-opened');
            openingEl.style.display = 'none';
        }
    } else {
        document.body.classList.add('w21-khoa');
        let openingStarted = false;
        const openDoors = () => {
            if (openingStarted) return;
            openingStarted = true;
            root.classList.add('opened');
            openingEl.classList.add('is-opening');
            tryStartMusic();
            unlockScroll();

            window.setTimeout(() => openingEl.classList.add('is-fading'), 1150);
            window.setTimeout(() => {
                openingEl.classList.add('is-opened');
                openingEl.style.display = 'none';
                unlockScroll();
            }, 1680);
        };
        openBtn?.addEventListener('click', openDoors);
    }

    // =========================================================================
    // 4. SCROLL REVEAL (.mayhen_invitation_reveal)
    // =========================================================================
    const reveals = Array.from(document.querySelectorAll('.mayhen_invitation_reveal'));
    if (isEmbed || reducedMotion || !('IntersectionObserver' in window)) {
        reveals.forEach((el) => el.classList.add('is-visible'));
    } else {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -28px 0px' });

        reveals.forEach((el) => {
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight * 0.95) {
                el.classList.add('is-visible');
            } else {
                io.observe(el);
            }
        });
    }

    // =========================================================================
    // 5. INTERACTIVE 2-DOOR GOOGLE MAPS CARD (#w21TheBd)
    // =========================================================================
    const mapCard = document.getElementById('w21TheBd');
    const mapToggleBtn = document.getElementById('w21MapToggleBtn');
    if (mapCard && mapToggleBtn) {
        mapToggleBtn.addEventListener('click', () => {
            const opened = mapCard.classList.toggle('is-map-open');
            mapToggleBtn.textContent = opened ? 'Đóng bản đồ' : 'Xem bản đồ';
        });
    }

    // =========================================================================
    // 6. GLOBAL CALENDAR & COUNTDOWN APIS (Asia/Ho_Chi_Minh +07:00)
    // =========================================================================
    const parseYmd = (input) => {
        if (input instanceof Date && !Number.isNaN(input.getTime())) {
            return { y: input.getFullYear(), m: input.getMonth() + 1, d: input.getDate() };
        }
        const s = String(input || root.dataset.weddingDate || '2027-01-16').trim();
        let m = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
        if (m) return { y: parseInt(m[1], 10), m: parseInt(m[2], 10), d: parseInt(m[3], 10) };
        m = s.match(/^(\d{1,2})[./-](\d{1,2})[./-](\d{4})/);
        if (m) return { y: parseInt(m[3], 10), m: parseInt(m[2], 10), d: parseInt(m[1], 10) };
        return { y: 2027, m: 1, d: 16 };
    };

    const renderMayhenWeddingCalendar = (targetDate) => {
        const { y, m, d } = parseYmd(targetDate);
        const pad2 = (n) => String(n).padStart(2, '0');
        const monthTitles = document.querySelectorAll('[data-sync-target="calendar_month_year"]');
        monthTitles.forEach((el) => {
            el.textContent = `THÁNG ${pad2(m)} · ${y}`;
        });

        const bodies = document.querySelectorAll('[data-sync-target="wedding_calendar"], .mayhen_invitation_calendar_body');
        if (bodies.length === 0) return;

        const isLeap = (y % 4 === 0 && y % 100 !== 0) || (y % 400 === 0);
        const daysInMonth = [31, isLeap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][m - 1] || 30;
        const firstDay = new Date(y, m - 1, 1).getDay();
        const mondayOffset = (firstDay + 6) % 7;

        bodies.forEach((grid) => {
            grid.textContent = '';
            for (let i = 0; i < mondayOffset; i++) {
                const empty = document.createElement('span');
                empty.className = 'mayhen_invitation_calendar_day';
                grid.appendChild(empty);
            }
            for (let day = 1; day <= daysInMonth; day++) {
                const cell = document.createElement('span');
                const isSunday = (mondayOffset + day - 1) % 7 === 6;
                cell.className = 'mayhen_invitation_calendar_day'
                    + (isSunday ? ' is-sunday' : '')
                    + (day === d ? ' mayhen_invitation_selected mayhen_invitation_calendar_selected_day' : '');
                cell.textContent = String(day);
                grid.appendChild(cell);
            }
        });
    };

    let cdInterval = null;
    const updateMayhenCountdown = (targetDate) => {
        if (cdInterval) window.clearInterval(cdInterval);
        const { y, m, d } = parseYmd(targetDate);
        const rawTime = String(root.dataset.targetTime || '17:30').trim();
        const tm = rawTime.match(/(\d{1,2}):(\d{2})/);
        const hh = tm ? String(parseInt(tm[1], 10)).padStart(2, '0') : '17';
        const mm = tm ? tm[2] : '30';
        const iso = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}T${hh}:${mm}:00+07:00`;
        const targetMs = new Date(iso).getTime();

        const tick = () => {
            const diffSec = Number.isFinite(targetMs) ? Math.max(0, Math.floor((targetMs - Date.now()) / 1000)) : 0;
            const days = Math.floor(diffSec / 86400);
            const hours = Math.floor((diffSec % 86400) / 3600);
            const mins = Math.floor((diffSec % 3600) / 60);
            const secs = diffSec % 60;

            const setAll = (sel, val) => {
                document.querySelectorAll(sel).forEach((el) => {
                    el.textContent = String(val).padStart(2, '0');
                });
            };
            setAll('[data-sync-target="countdown_day"], .mayhen_invitation_cd_day', days);
            setAll('[data-sync-target="countdown_hour"], .mayhen_invitation_cd_hour', hours);
            setAll('[data-sync-target="countdown_min"], .mayhen_invitation_cd_min', mins);
            setAll('[data-sync-target="countdown_sec"], .mayhen_invitation_cd_sec', secs);
        };
        tick();
        cdInterval = window.setInterval(tick, 1000);
    };

    window.renderMayhenWeddingCalendar = renderMayhenWeddingCalendar;
    window.updateMayhenCountdown = updateMayhenCountdown;
    renderMayhenWeddingCalendar(root.dataset.weddingDate);
    updateMayhenCountdown(root.dataset.weddingDate);

    // =========================================================================
    // 7. GIFT BOX ("Share THE Love") & 1-TAP COPY STK
    // =========================================================================
    const giftFrame = document.getElementById('w21GiftFrame');
    const giftBox = document.getElementById('w21Qua');
    if (giftFrame && giftBox) {
        const openGift = () => {
            if (giftFrame.classList.contains('is-opened') || giftBox.classList.contains('is-opening')) return;
            giftBox.classList.add('is-opening');
            window.setTimeout(() => giftFrame.classList.add('is-opened'), 760);
        };
        giftBox.addEventListener('click', openGift);
        giftBox.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openGift();
            }
        });
    }

    document.querySelectorAll('[data-copy-account]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const stk = (btn.getAttribute('data-copy-account') || '').trim();
            if (!stk) return;
            const prev = btn.textContent;
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(stk);
                } else {
                    const ta = document.createElement('textarea');
                    ta.value = stk;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }
                btn.textContent = 'Đã sao chép!';
                showToast(`Đã sao chép STK: ${stk}`);
                window.setTimeout(() => { btn.textContent = prev; }, 2000);
            } catch {
                showToast(`STK: ${stk}`);
            }
        });
    });

    // =========================================================================
    // 8. FULL-SCREEN LIGHTBOX (ALBUM & QR)
    // =========================================================================
    const lb = document.getElementById('mayhenLightbox');
    const lbImg = document.getElementById('mayhenLbImg');
    const lbCounter = document.getElementById('mayhenLbCounter');
    const lbCaption = document.getElementById('mayhenLbCaption');
    let lbUrls = [];
    let lbIdx = 0;

    const renderLb = (caption = '') => {
        if (!lbImg || lbUrls.length === 0) return;
        lbImg.src = lbUrls[lbIdx];
        if (lbCounter) lbCounter.textContent = lbUrls.length > 1 ? `${lbIdx + 1} / ${lbUrls.length}` : 'Bản Nhạc Không Đoạn Kết';
        if (lbCaption) lbCaption.textContent = caption;
    };

    const openLb = (urls, idx = 0, isQr = false, caption = '') => {
        if (!lb || !urls.length) return;
        lbUrls = urls.filter(Boolean);
        lbIdx = Math.max(0, Math.min(idx, lbUrls.length - 1));
        lb.classList.toggle('is-single', lbUrls.length <= 1);
        lb.classList.toggle('is-qr', Boolean(isQr));
        lb.classList.add('is-open');
        lb.setAttribute('aria-hidden', 'false');
        renderLb(caption);
    };

    const closeLb = () => {
        if (!lb) return;
        lb.classList.remove('is-open', 'is-qr', 'is-single');
        lb.setAttribute('aria-hidden', 'true');
    };

    document.querySelectorAll('[data-gallery-index]').forEach((item) => {
        item.addEventListener('click', () => {
            const idx = parseInt(item.getAttribute('data-gallery-index') || '0', 10) || 0;
            const urls = Array.from(document.querySelectorAll('[data-gallery-index] img')).map((im) => im.currentSrc || im.getAttribute('src') || '');
            openLb(urls, idx, false, `Album Kỷ Niệm — Ảnh ${idx + 1}`);
        });
    });

    document.querySelectorAll('[data-lightbox-src]:not([data-gallery-index])').forEach((el) => {
        el.addEventListener('click', () => {
            const im = el.querySelector('img');
            const src = im?.currentSrc || im?.getAttribute('src') || el.getAttribute('data-lightbox-src') || '';
            const isQr = el.getAttribute('data-lightbox-qr') === '1';
            if (src) openLb([src], 0, isQr, isQr ? 'Quét mã VietQR để gửi quà mừng cưới' : '');
        });
    });

    document.getElementById('mayhenLbClose')?.addEventListener('click', closeLb);
    document.getElementById('mayhenLbPrev')?.addEventListener('click', () => {
        if (lbUrls.length > 1) { lbIdx = (lbIdx - 1 + lbUrls.length) % lbUrls.length; renderLb(); }
    });
    document.getElementById('mayhenLbNext')?.addEventListener('click', () => {
        if (lbUrls.length > 1) { lbIdx = (lbIdx + 1) % lbUrls.length; renderLb(); }
    });
    lb?.addEventListener('click', (e) => {
        if (e.target === lb || e.target.classList.contains('mayhen_invitation_lb_stage')) closeLb();
    });
    document.addEventListener('keydown', (e) => {
        if (!lb?.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeLb();
        else if (e.key === 'ArrowLeft' && lbUrls.length > 1) { lbIdx = (lbIdx - 1 + lbUrls.length) % lbUrls.length; renderLb(); }
        else if (e.key === 'ArrowRight' && lbUrls.length > 1) { lbIdx = (lbIdx + 1) % lbUrls.length; renderLb(); }
    });

    // =========================================================================
    // 9. SIGNATURE CANVAS (POINTER EVENTS + DPR + RESIZE STROKE PRESERVATION)
    // =========================================================================
    const sigCanvas = document.getElementById('mayhenSigCanvas');
    const sigHint = document.getElementById('mayhenSigHint');
    const sigNameInput = document.getElementById('mayhenSigGuestName');
    const sigPreviewBox = document.getElementById('mayhenSigPreviewBox');
    const sigPreviewImg = document.getElementById('mayhenSigPreviewImg');
    const sigDownloadLink = document.getElementById('mayhenSigDownloadLink');
    const sigStatus = document.getElementById('mayhenSigStatus');

    if (sigCanvas) {
        const ctx = sigCanvas.getContext('2d');
        const strokes = [];
        let activeStroke = null;
        let drawing = false;

        const redrawAll = () => {
            if (!ctx) return;
            const rect = sigCanvas.getBoundingClientRect();
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            const w = Math.max(240, Math.floor(rect.width));
            const h = Math.max(140, Math.floor(rect.height));
            sigCanvas.width = Math.floor(w * dpr);
            sigCanvas.height = Math.floor(h * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.strokeStyle = '#6E4E42';
            ctx.lineWidth = 2.4;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            for (const pts of strokes) {
                if (pts.length < 2) continue;
                ctx.beginPath();
                ctx.moveTo(pts[0].x * w, pts[0].y * h);
                for (let i = 1; i < pts.length; i++) {
                    ctx.lineTo(pts[i].x * w, pts[i].y * h);
                }
                ctx.stroke();
            }
        };

        redrawAll();
        window.addEventListener('resize', redrawAll, { passive: true });

        const normPoint = (ev) => {
            const rect = sigCanvas.getBoundingClientRect();
            return {
                x: (ev.clientX - rect.left) / (rect.width || 1),
                y: (ev.clientY - rect.top) / (rect.height || 1),
                rawX: ev.clientX - rect.left,
                rawY: ev.clientY - rect.top,
            };
        };

        sigCanvas.addEventListener('pointerdown', (ev) => {
            if (!ctx) return;
            sigCanvas.setPointerCapture?.(ev.pointerId);
            drawing = true;
            if (sigHint) sigHint.style.display = 'none';
            const pt = normPoint(ev);
            activeStroke = [{ x: pt.x, y: pt.y }];
            strokes.push(activeStroke);
            ctx.beginPath();
            ctx.moveTo(pt.rawX, pt.rawY);
        });

        sigCanvas.addEventListener('pointermove', (ev) => {
            if (!drawing || !ctx || !activeStroke) return;
            const pt = normPoint(ev);
            activeStroke.push({ x: pt.x, y: pt.y });
            ctx.lineTo(pt.rawX, pt.rawY);
            ctx.stroke();
        });

        const endDraw = () => {
            drawing = false;
            activeStroke = null;
        };
        sigCanvas.addEventListener('pointerup', endDraw);
        sigCanvas.addEventListener('pointercancel', endDraw);

        document.getElementById('mayhenSigClear')?.addEventListener('click', () => {
            strokes.length = 0;
            redrawAll();
            if (sigHint) sigHint.style.display = 'flex';
            sigPreviewBox?.classList.remove('is-visible');
            if (sigStatus) sigStatus.textContent = '';
        });

        const showPreview = () => {
            if (strokes.length === 0) {
                showToast('Vui lòng ký tên lên bảng trước.');
                return null;
            }
            const dataUrl = sigCanvas.toDataURL('image/png');
            if (sigPreviewImg) sigPreviewImg.src = dataUrl;
            if (sigDownloadLink) sigDownloadLink.href = dataUrl;
            sigPreviewBox?.classList.add('is-visible');
            return dataUrl;
        };

        document.getElementById('mayhenSigPreviewBtn')?.addEventListener('click', showPreview);

        document.getElementById('mayhenSigSave')?.addEventListener('click', async () => {
            const dataUrl = showPreview();
            if (!dataUrl) return;
            const signerName = (sigNameInput?.value || document.getElementById('mayhenRsvpName')?.value || root.dataset.guestName || '').trim();
            if (!signerName) {
                showToast('Vui lòng nhập tên của bạn trước khi lưu chữ ký.');
                sigNameInput?.focus();
                return;
            }
            const endpoint = root.dataset.signatureEndpoint || '';
            if (!endpoint) {
                const msg = 'Chế độ xem thử: Chữ ký đã sẵn sàng tải PNG. Khi bạn tạo và xuất bản thiệp, chữ ký khách mời sẽ được lưu trực tiếp vào Sổ Ký Tên!';
                if (sigStatus) sigStatus.textContent = msg;
                showToast('Đã tạo bản xem trước PNG!');
                return;
            }
            try {
                const res = await window.fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ guest_name: signerName, signature_data: dataUrl }),
                });
                const json = await res.json().catch(() => ({}));
                if (res.ok && json.success) {
                    if (sigStatus) sigStatus.textContent = json.message || 'Đã lưu chữ ký của bạn vào Sổ Ký Tên!';
                    showToast('Đã lưu chữ ký thành công!');
                } else {
                    if (sigStatus) sigStatus.textContent = json.message || 'Không thể lưu chữ ký lúc này.';
                }
            } catch {
                if (sigStatus) sigStatus.textContent = 'Lỗi kết nối máy chủ, vui lòng thử lại.';
            }
        });
    }

    // =========================================================================
    // 10. RSVP & WISHES (EXACT LARAVEL PAYLOADS + ZERO innerHTML XSS)
    // =========================================================================
    const rsvpForm = document.getElementById('mayhenRsvpForm');
    const rsvpFeedback = document.getElementById('mayhenRsvpFeedback');
    let rsvpSubmitting = false;

    rsvpForm?.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        if (rsvpSubmitting) return;
        const endpoint = root.dataset.rsvpEndpoint || '';
        const fd = new FormData(rsvpForm);
        const guestName = String(fd.get('guest_name') || '').trim();
        const status = String(fd.get('attending_status') || 'attending');
        const payload = {
            guest_name: guestName,
            attending_status: status,
            adults_count: status === 'attending' ? Number(fd.get('adults_count') || 1) : 0,
            children_count: status === 'attending' ? Number(fd.get('children_count') || 0) : 0,
            wishes: String(fd.get('wishes') || '').trim(),
        };

        if (!guestName) {
            showToast('Vui lòng nhập họ và tên.');
            return;
        }
        if (!endpoint) {
            if (rsvpFeedback) rsvpFeedback.textContent = 'Chế độ xem thử: Form RSVP đã sẵn sàng hoạt động khi xuất bản thiệp!';
            return;
        }

        rsvpSubmitting = true;
        const btn = document.getElementById('mayhenRsvpSubmit');
        if (btn) btn.disabled = true;

        try {
            const res = await window.fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.success === true) {
                if (rsvpFeedback) rsvpFeedback.textContent = json.message || 'Cảm ơn Quý Khách đã xác nhận tham dự!';
                showToast(json.message || 'Đã gửi xác nhận tham dự!');
                rsvpForm.reset();
            } else {
                if (rsvpFeedback) rsvpFeedback.textContent = json.message || 'Vui lòng kiểm tra lại thông tin xác nhận.';
            }
        } catch {
            if (rsvpFeedback) rsvpFeedback.textContent = 'Lỗi kết nối, vui lòng thử lại.';
        } finally {
            rsvpSubmitting = false;
            if (btn) btn.disabled = false;
        }
    });

    const wishForm = document.getElementById('mayhenWishForm');
    const wishesList = document.getElementById('mayhenWishesList');
    const wishFeedback = document.getElementById('mayhenWishFeedback');
    let wishSubmitting = false;

    const appendWishDOM = (author, message) => {
        if (!wishesList) return;
        const item = document.createElement('div');
        item.className = 'mayhen_invitation_wish_item';
        const authorEl = document.createElement('div');
        authorEl.className = 'mayhen_invitation_wish_author';
        authorEl.textContent = author;
        const msgEl = document.createElement('p');
        msgEl.className = 'mayhen_invitation_wish_text';
        msgEl.textContent = message;
        item.appendChild(authorEl);
        item.appendChild(msgEl);
        wishesList.prepend(item);
    };

    wishForm?.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        if (wishSubmitting) return;
        const fd = new FormData(wishForm);
        const name = String(fd.get('name') || '').trim();
        const message = String(fd.get('message') || '').trim();
        if (!name || !message) {
            showToast('Vui lòng nhập tên và lời chúc.');
            return;
        }
        const endpoint = root.dataset.wishesEndpoint || '';
        if (!endpoint) {
            appendWishDOM(name, message);
            if (wishFeedback) wishFeedback.textContent = 'Chế độ xem thử: Lời chúc đã hiển thị minh họa!';
            wishForm.reset();
            return;
        }

        wishSubmitting = true;
        const btn = document.getElementById('mayhenWishSubmit');
        if (btn) btn.disabled = true;

        try {
            const res = await window.fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ name, message }),
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.success === true) {
                appendWishDOM(name, message);
                if (wishFeedback) wishFeedback.textContent = json.message || 'Cảm ơn bạn đã gửi lời chúc phúc!';
                showToast('Đã gửi lời chúc thành công!');
                wishForm.reset();
            } else {
                if (wishFeedback) wishFeedback.textContent = json.message || 'Không thể gửi lời chúc lúc này.';
            }
        } catch {
            if (wishFeedback) wishFeedback.textContent = 'Lỗi kết nối, vui lòng thử lại.';
        } finally {
            wishSubmitting = false;
            if (btn) btn.disabled = false;
        }
    });

    // =========================================================================
    // 11. STUDIO LIVE SYNC LISTENER (NON-DESTRUCTIVE WITH editor-bridge)
    // =========================================================================
    const rebuildDresscode = (csv) => {
        const container = document.getElementById('mayhenDresscodeSwatches');
        if (!container) return;
        const list = String(csv || '').split(',').map((s) => s.trim()).filter((s) => /^#[0-9a-f]{3,8}$/i.test(s));
        if (!list.length) return;
        const rots = ['-2.5deg', '2deg', '-1.4deg', '2.6deg', '-1.8deg', '1.6deg'];
        container.textContent = '';
        list.forEach((hex, idx) => {
            const item = document.createElement('div');
            item.className = 'mayhen_invitation_dc_item';
            item.style.setProperty('--r', rots[idx % rots.length]);
            const card = document.createElement('div');
            card.className = 'mayhen_invitation_dc_card';
            const fabric = document.createElement('div');
            fabric.className = 'mayhen_invitation_dc_fabric';
            fabric.style.setProperty('--c', hex);
            const lbl = document.createElement('span');
            lbl.className = 'mayhen_invitation_dc_label';
            lbl.textContent = hex.toUpperCase();
            card.appendChild(fabric);
            card.appendChild(lbl);
            item.appendChild(card);
            container.appendChild(item);
        });
    };

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || !event.data || typeof event.data !== 'object') return;
        if (event.data.type !== 'MAYHEN_SYNC_INPUT') return;
        const rawKey = String(event.data.field || event.data.key || '').replace(/^wedding\.extra_data\.|^wedding\.|^extra_data\./, '');
        const val = event.data.value;
        if (rawKey === 'dresscode_colors') {
            rebuildDresscode(val);
        } else if (rawKey === 'wedding_date' && typeof val === 'string' && val.trim()) {
            root.dataset.weddingDate = val.trim();
            renderMayhenWeddingCalendar(val.trim());
            updateMayhenCountdown(val.trim());
        } else if ((rawKey === 'party_time' || rawKey === 'event_target_time') && typeof val === 'string' && val.trim()) {
            root.dataset.targetTime = val.trim();
            updateMayhenCountdown(root.dataset.weddingDate);
        } else if (rawKey === 'event_party_map_link' && typeof val === 'string' && /^https?:\/\//i.test(val.trim())) {
            const linkEl = document.getElementById('mayhenMapExternalLink');
            if (linkEl) linkEl.href = val.trim();
        } else if (rawKey.startsWith('gallery.') && typeof val === 'string' && val.trim()) {
            const idx = rawKey.split('.')[1];
            const holder = document.querySelector(`[data-gallery-index="${idx}"]`);
            if (holder) holder.setAttribute('data-lightbox-src', val.trim());
        }
    });

    // =========================================================================
    // 12. GENTLE BLUSH BUTTERFLIES (#w21Buom — VIEW MODE ONLY)
    // =========================================================================
    const bfLayer = document.getElementById('w21Buom');
    if (bfLayer && !isEmbed && !reducedMotion) {
        const colors = ['#E2BDAF', '#D3B2A3', '#E8CFC4', '#C9A293'];
        const spawnButterfly = () => {
            if (document.hidden || bfLayer.children.length >= 4) return;
            const el = document.createElement('span');
            el.className = 'mayhen_invitation_bf';
            const fromLeft = Math.random() > 0.5;
            const y0 = 15 + Math.random() * 65;
            const dy = (Math.random() - 0.5) * 260;
            const dur = 11 + Math.random() * 6;
            el.style.setProperty('--x0', fromLeft ? '-40px' : 'calc(100vw + 40px)');
            el.style.setProperty('--y0', `${y0}vh`);
            el.style.setProperty('--dx', fromLeft ? 'calc(100vw + 90px)' : 'calc(-100vw - 90px)');
            el.style.setProperty('--dy', `${dy}px`);
            el.style.setProperty('--c', colors[Math.floor(Math.random() * colors.length)]);
            el.style.animationDuration = `${dur}s`;

            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('viewBox', '0 0 40 30');
            const p1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            p1.setAttribute('class', 'wing');
            p1.setAttribute('d', 'M20 15C13 4 3 5 5 14c1 5 8 7 15 1zM20 15c-6 4-11 10-6 13 4 2 7-4 6-13z');
            const p2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            p2.setAttribute('class', 'wing');
            p2.setAttribute('d', 'M20 15c7-11 17-10 15-1-1 5-8 7-15 1zM20 15c6 4 11 10 6 13-4 2-7-4-6-13z');
            svg.appendChild(p1);
            svg.appendChild(p2);
            el.appendChild(svg);
            bfLayer.appendChild(el);
            window.setTimeout(() => el.remove(), dur * 1000 + 200);
        };
        window.setTimeout(spawnButterfly, 1800);
        window.setInterval(spawnButterfly, 4800);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBanNhacKhongDoanKet, { once: true });
} else {
    initBanNhacKhongDoanKet();
}
