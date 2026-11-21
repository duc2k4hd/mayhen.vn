/**
 * Tiếng Hát Trong Lễ Đường — Pure Vanilla JS (ES6+, Zero Dependencies)
 * Author: Mây Hẹn Studio
 */
(function () {
    'use strict';

    const root = document.getElementById('mayhen_invitation_root');
    if (!root) return;

    const isEmbed = root.dataset.isEmbed === 'true' || root.dataset.isEmbed === '1';
    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const csrfToken = root.dataset.csrfToken || '';
    const rsvpUrl = root.dataset.rsvpUrl || '';
    const wishUrl = root.dataset.wishUrl || '';
    const signatureUrl = root.dataset.signatureUrl || '';

    const FLOWER_SVG = '<svg viewBox="-20 -20 40 40" aria-hidden="true"><use href="#dk-hoa"></use></svg>';
    const BUD_SVG = '<svg viewBox="-10 -14 20 24" aria-hidden="true"><use href="#dk-nu"></use></svg>';

    const COLOR_LABELS = {
        '#F4ECDC': 'Trắng Kem',
        '#7C8C6E': 'Xanh Rêu',
        '#F2B5C5': 'Hồng Đào',
        '#EAC98E': 'Vàng Nhạt',
        '#6B7B5F': 'Rêu Cổ Điển',
        '#E6AACB': 'Hồng Phấn',
        '#C9A15E': 'Vàng Đồng',
        '#FFFFFF': 'Trắng Tinh',
        '#F5F5DC': 'Be Nhạt',
    };

    const WEEKDAYS = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];

    /* =====================================================================
     * 1. TOAST HELPER (No window.alert)
     * ===================================================================== */
    let toastTimer = null;
    function showToast(message) {
        const toast = document.getElementById('mayhenToast');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.remove('is-visible');
        }, 2800);
    }

    /* =====================================================================
     * 2. AUDIO & FLOATING MUSIC BUTTON
     * ===================================================================== */
    const audioEl = document.getElementById('mayhenBgAudio');
    const musicBtn = document.getElementById('mayhenMusicBtn');

    function setMusicState(playing) {
        if (!musicBtn) return;
        musicBtn.classList.toggle('is-playing', playing);
        musicBtn.setAttribute('aria-pressed', playing ? 'true' : 'false');
    }

    function tryPlayMusic() {
        if (!audioEl || !audioEl.src) return;
        const playPromise = audioEl.play();
        if (playPromise && typeof playPromise.then === 'function') {
            playPromise
                .then(() => setMusicState(true))
                .catch(() => setMusicState(false));
        }
    }

    if (musicBtn && audioEl) {
        musicBtn.addEventListener('click', () => {
            if (audioEl.paused) {
                tryPlayMusic();
            } else {
                audioEl.pause();
                setMusicState(false);
            }
        });
    }

    /* =====================================================================
     * 3. FALLING OLEANDER PETALS & BURST PETALS
     * ===================================================================== */
    function startFallingPetals() {
        if (reduceMotion) return;
        const container = document.getElementById('mayhenFallingPetals');
        if (!container || container.childElementCount > 0) return;

        for (let i = 0; i < 16; i += 1) {
            const node = document.createElement('i');
            const size = 9 + Math.random() * 9;
            node.style.width = `${size.toFixed(1)}px`;
            node.style.height = `${size.toFixed(1)}px`;
            node.style.left = `${(Math.random() * 100).toFixed(2)}%`;
            node.style.opacity = i % 4 === 3 ? '0.55' : '0.85';
            node.style.setProperty('--lech', `${((Math.random() * 2 - 1) * 90).toFixed(0)}px`);
            node.style.animationDuration = `${(11 + Math.random() * 9).toFixed(2)}s`;
            node.style.animationDelay = `${(-Math.random() * 18).toFixed(2)}s`;
            node.innerHTML = i % 2 === 1 ? BUD_SVG : FLOWER_SVG;
            container.appendChild(node);
        }
    }

    function spawnBurstPetals() {
        if (reduceMotion) return;
        const burst = document.getElementById('mayhenBurstPetals');
        if (!burst) return;
        burst.innerHTML = '';

        for (let i = 0; i < 34; i += 1) {
            const petal = document.createElement('i');
            const angle = Math.random() * Math.PI * 2;
            const radius = 140 + Math.random() * 260;
            petal.style.left = `${(46 + Math.random() * 8).toFixed(2)}%`;
            petal.style.setProperty('--x', `${(Math.cos(angle) * radius).toFixed(0)}px`);
            petal.style.setProperty('--y', `${(Math.sin(angle) * radius * 0.7 + 160).toFixed(0)}px`);
            petal.style.setProperty('--r', `${((Math.random() * 2 - 1) * 360).toFixed(0)}deg`);
            petal.style.animationDelay = `${(0.15 + Math.random() * 0.4).toFixed(2)}s`;
            petal.innerHTML = i % 3 === 0 ? BUD_SVG : FLOWER_SVG;
            burst.appendChild(petal);
        }
    }

    /* =====================================================================
     * 4. TWO-DOOR OPENING SCREEN WITH IMPERIAL SEAL
     * ===================================================================== */
    const openingEl = document.getElementById('tdOpening');
    const openBtn = document.getElementById('tdOpenBtn');
    const sealHit = document.getElementById('tdSealHit');
    let isOpeningTriggered = false;

    function openInvitationDoors() {
        if (!openingEl || isOpeningTriggered || openingEl.classList.contains('is-opened')) return;
        isOpeningTriggered = true;
        spawnBurstPetals();
        openingEl.classList.add('is-opening');
        tryPlayMusic();

        const duration = reduceMotion ? 60 : 2300;
        setTimeout(() => {
            openingEl.classList.add('is-opened');
            openingEl.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('td-khoa');
            startFallingPetals();
            revealVisibleNow();
        }, duration);
    }

    if (isEmbed) {
        document.body.classList.remove('td-khoa');
        if (openingEl) {
            openingEl.classList.add('is-opened');
            openingEl.setAttribute('aria-hidden', 'true');
        }
        startFallingPetals();
    } else if (openingEl) {
        if (openBtn) openBtn.addEventListener('click', openInvitationDoors);
        if (sealHit) sealHit.addEventListener('click', openInvitationDoors);
    }

    /* =====================================================================
     * 5. SCROLL REVEAL (IntersectionObserver + Immediate Fallback)
     * ===================================================================== */
    const revealEls = Array.from(root.querySelectorAll('.mayhen_invitation_reveal'));

    function revealVisibleNow() {
        revealEls.forEach((el) => {
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight * 0.95) {
                el.classList.add('is-visible');
            }
        });
    }

    if (isEmbed || reduceMotion || !('IntersectionObserver' in window)) {
        revealEls.forEach((el) => el.classList.add('is-visible'));
    } else {
        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -30px 0px' }
        );
        revealEls.forEach((el) => io.observe(el));
        revealVisibleNow();
    }

    /* =====================================================================
     * 6. INTERACTIVE 2-WING BAMBOO MAP DOORS
     * ===================================================================== */
    const mapDoors = document.getElementById('tdMapDoors');
    const mapOpenBtn = document.getElementById('tdMapOpenBtn');

    if (mapDoors && mapOpenBtn) {
        mapOpenBtn.addEventListener('click', () => {
            if (mapDoors.classList.contains('is-open')) return;
            mapDoors.classList.add('is-open');
            setTimeout(() => {
                mapDoors.classList.add('is-done');
            }, reduceMotion ? 50 : 1850);
        });
    }

    /* =====================================================================
     * 7. WEDDING CALENDAR & LIVE COUNTDOWN (Asia/Ho_Chi_Minh +07:00)
     * ===================================================================== */
    function parseWeddingDateParts(dateStr) {
        const m = String(dateStr || '').trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) {
            return { year: 2026, month: 12, day: 20 };
        }
        return {
            year: parseInt(m[1], 10),
            month: parseInt(m[2], 10),
            day: parseInt(m[3], 10),
        };
    }

    function renderMayhenWeddingCalendar(dateStr) {
        const body = document.getElementById('mayhenCalendarBody');
        const monthEl = document.getElementById('mayhenCalendarMonth');
        if (!body) return;

        const { year, month, day } = parseWeddingDateParts(dateStr);
        const pad2 = (n) => String(n).padStart(2, '0');
        if (monthEl) {
            monthEl.textContent = `Tháng ${pad2(month)} · ${year}`;
        }

        const firstDay = new Date(year, month - 1, 1);
        const startOffset = (firstDay.getDay() + 6) % 7; // Monday = 0
        const daysInMonth = new Date(year, month, 0).getDate();

        body.innerHTML = '';
        for (let i = 0; i < startOffset; i += 1) {
            const empty = document.createElement('span');
            empty.className = 'mayhen_invitation_calendar_day';
            body.appendChild(empty);
        }
        for (let d = 1; d <= daysInMonth; d += 1) {
            const cell = document.createElement('span');
            cell.className = 'mayhen_invitation_calendar_day';
            if (d === day) {
                cell.classList.add('mayhen_invitation_selected', 'mayhen_invitation_calendar_selected_day');
            }
            cell.textContent = String(d);
            body.appendChild(cell);
        }

        // Sync event date display & Google Calendar URL
        const targetDate = new Date(year, month - 1, day);
        const weekdayEl = document.getElementById('mayhenEventWeekday');
        const dayNumEl = document.getElementById('mayhenEventDayNum');
        const monthYearEl = document.getElementById('mayhenEventMonthYear');
        if (weekdayEl) weekdayEl.textContent = WEEKDAYS[targetDate.getDay()] || 'Chủ Nhật';
        if (dayNumEl) dayNumEl.textContent = pad2(day);
        if (monthYearEl) monthYearEl.textContent = `Tháng ${pad2(month)} · ${year}`;
    }

    let countdownInterval = null;
    let currentTargetIso = root.dataset.targetIso || '2026-12-20T11:00:00+07:00';

    function updateMayhenCountdown(newIso) {
        if (newIso) {
            currentTargetIso = newIso;
        }
        const grid = document.getElementById('mayhenCountdownGrid');
        if (!grid) return;

        const dayEl = grid.querySelector('.mayhen_invitation_cd_day');
        const hourEl = grid.querySelector('.mayhen_invitation_cd_hour');
        const minEl = grid.querySelector('.mayhen_invitation_cd_min');
        const secEl = grid.querySelector('.mayhen_invitation_cd_sec');

        const targetMs = Date.parse(currentTargetIso);
        const tick = () => {
            if (Number.isNaN(targetMs)) return;
            const diff = Math.max(0, targetMs - Date.now());
            const days = Math.floor(diff / 86400000);
            const hours = Math.floor((diff % 86400000) / 3600000);
            const mins = Math.floor((diff % 3600000) / 60000);
            const secs = Math.floor((diff % 60000) / 1000);
            const pad = (n) => String(n).padStart(2, '0');
            if (dayEl) dayEl.textContent = pad(days);
            if (hourEl) hourEl.textContent = pad(hours);
            if (minEl) minEl.textContent = pad(mins);
            if (secEl) secEl.textContent = pad(secs);
        };

        clearInterval(countdownInterval);
        tick();
        countdownInterval = setInterval(tick, 1000);
    }

    window.renderMayhenWeddingCalendar = renderMayhenWeddingCalendar;
    window.updateMayhenCountdown = updateMayhenCountdown;

    renderMayhenWeddingCalendar(root.dataset.weddingDate || '2026-12-20');
    updateMayhenCountdown(currentTargetIso);

    /* =====================================================================
     * 8. LUCKY ENVELOPE (BAO LÌ XÌ) & 1-TAP STK COPY
     * ===================================================================== */
    const giftStage = document.getElementById('tdGiftStage');
    const lixiBox = document.getElementById('tdLixiBox');

    function openGiftBox() {
        if (!giftStage || giftStage.classList.contains('is-opened')) return;
        giftStage.classList.add('is-opened');
    }

    if (lixiBox) {
        lixiBox.addEventListener('click', openGiftBox);
        lixiBox.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openGiftBox();
            }
        });
    }

    root.addEventListener('click', (e) => {
        const copyBtn = e.target.closest('[data-copy-account]');
        if (!copyBtn) return;
        const account = copyBtn.getAttribute('data-copy-account') || '';
        if (!account) return;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(account).then(
                () => showToast(`Đã sao chép STK: ${account}`),
                () => showToast(`Số tài khoản: ${account}`)
            );
        } else {
            showToast(`Số tài khoản: ${account}`);
        }
    });

    /* =====================================================================
     * 9. FULL-SCREEN LIGHTBOX (ALBUM + FRAMES + QR)
     * ===================================================================== */
    const lightbox = document.getElementById('mayhenLightbox');
    const lbImg = document.getElementById('mayhenLbImg');
    const lbCount = document.getElementById('mayhenLbCount');
    const lbCaption = document.getElementById('mayhenLbCaption');
    const lbClose = document.getElementById('mayhenLbClose');
    const lbPrev = document.getElementById('mayhenLbPrev');
    const lbNext = document.getElementById('mayhenLbNext');

    let lbItems = [];
    let lbIndex = 0;

    function renderLightbox() {
        if (!lightbox || !lbItems.length) return;
        const item = lbItems[lbIndex];
        if (lbImg) lbImg.src = item.src;
        if (lbCaption) lbCaption.textContent = item.caption || '';
        if (lbCount) lbCount.textContent = `${lbIndex + 1} / ${lbItems.length}`;
        lightbox.classList.toggle('is-single', lbItems.length <= 1);
        lightbox.classList.toggle('is-qr', Boolean(item.isQr));
    }

    function openLightbox(items, startIndex) {
        if (!lightbox || !items.length) return;
        lbItems = items;
        lbIndex = Math.max(0, Math.min(startIndex, items.length - 1));
        renderLightbox();
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.classList.remove('is-open', 'is-qr');
        lightbox.setAttribute('aria-hidden', 'true');
    }

    root.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-lightbox-src]');
        if (!trigger) return;

        if (trigger.hasAttribute('data-gallery-index')) {
            const albumNodes = Array.from(root.querySelectorAll('#mayhenAlbumGrid [data-lightbox-src]'));
            const items = albumNodes.map((n) => ({
                src: n.querySelector('img')?.getAttribute('src') || n.getAttribute('data-lightbox-src') || '',
                caption: n.getAttribute('data-lightbox-caption') || '',
                isQr: false,
            }));
            const idx = albumNodes.indexOf(trigger);
            openLightbox(items, idx >= 0 ? idx : 0);
        } else {
            const imgSrc = trigger.querySelector('img')?.getAttribute('src') || trigger.getAttribute('data-lightbox-src') || '';
            openLightbox(
                [{ src: imgSrc, caption: trigger.getAttribute('data-lightbox-caption') || '', isQr: trigger.getAttribute('data-lightbox-qr') === '1' }],
                0
            );
        }
    });

    if (lbClose) lbClose.addEventListener('click', closeLightbox);
    if (lbPrev) {
        lbPrev.addEventListener('click', () => {
            if (!lbItems.length) return;
            lbIndex = (lbIndex - 1 + lbItems.length) % lbItems.length;
            renderLightbox();
        });
    }
    if (lbNext) {
        lbNext.addEventListener('click', () => {
            if (!lbItems.length) return;
            lbIndex = (lbIndex + 1) % lbItems.length;
            renderLightbox();
        });
    }
    if (lightbox) {
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) closeLightbox();
        });
    }
    document.addEventListener('keydown', (e) => {
        if (!lightbox || !lightbox.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft' && lbPrev) lbPrev.click();
        if (e.key === 'ArrowRight' && lbNext) lbNext.click();
    });

    /* =====================================================================
     * 10. HTML5 SIGNATURE CANVAS (Pointer Events + DPR Scaling + Stroke Keep)
     * ===================================================================== */
    const sigCanvas = document.getElementById('mayhenSigCanvas');
    const sigHint = document.getElementById('mayhenSigHint');
    const sigClearBtn = document.getElementById('mayhenSigClear');
    const sigPreviewBtn = document.getElementById('mayhenSigPreviewBtn');
    const sigSaveBtn = document.getElementById('mayhenSigSave');
    const sigPreviewBox = document.getElementById('mayhenSigPreviewBox');
    const sigPreviewImg = document.getElementById('mayhenSigPreviewImg');
    const sigDownloadLink = document.getElementById('mayhenSigDownloadLink');
    const sigStatus = document.getElementById('mayhenSigStatus');
    const sigNameInput = document.getElementById('mayhenSigGuestName');

    if (sigCanvas) {
        const ctx = sigCanvas.getContext('2d');
        let drawing = false;
        let hasStrokes = false;
        let lastPoint = null;

        function resizeSigCanvas(preserve) {
            const rect = sigCanvas.getBoundingClientRect();
            if (!rect.width || !rect.height) return;
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            let snapshot = null;
            if (preserve && hasStrokes && sigCanvas.width > 0 && sigCanvas.height > 0) {
                snapshot = sigCanvas.toDataURL('image/png');
            }
            sigCanvas.width = Math.round(rect.width * dpr);
            sigCanvas.height = Math.round(rect.height * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.lineWidth = 2.4;
            ctx.strokeStyle = '#3E4A36';

            if (snapshot) {
                const img = new Image();
                img.onload = () => ctx.drawImage(img, 0, 0, rect.width, rect.height);
                img.src = snapshot;
            }
        }

        function getLocalPoint(ev) {
            const rect = sigCanvas.getBoundingClientRect();
            return { x: ev.clientX - rect.left, y: ev.clientY - rect.top };
        }

        sigCanvas.addEventListener('pointerdown', (ev) => {
            ev.preventDefault();
            sigCanvas.setPointerCapture(ev.pointerId);
            drawing = true;
            hasStrokes = true;
            if (sigHint) sigHint.style.display = 'none';
            lastPoint = getLocalPoint(ev);
            ctx.beginPath();
            ctx.moveTo(lastPoint.x, lastPoint.y);
        });

        sigCanvas.addEventListener('pointermove', (ev) => {
            if (!drawing) return;
            const pt = getLocalPoint(ev);
            ctx.lineTo(pt.x, pt.y);
            ctx.stroke();
            lastPoint = pt;
        });

        const stopDrawing = () => {
            drawing = false;
            lastPoint = null;
        };
        sigCanvas.addEventListener('pointerup', stopDrawing);
        sigCanvas.addEventListener('pointercancel', stopDrawing);

        if (sigClearBtn) {
            sigClearBtn.addEventListener('click', () => {
                const rect = sigCanvas.getBoundingClientRect();
                ctx.clearRect(0, 0, rect.width, rect.height);
                hasStrokes = false;
                if (sigHint) sigHint.style.display = 'flex';
                if (sigPreviewBox) sigPreviewBox.classList.remove('is-visible');
                if (sigStatus) sigStatus.textContent = '';
            });
        }

        if (sigPreviewBtn) {
            sigPreviewBtn.addEventListener('click', () => {
                if (!hasStrokes) {
                    showToast('Vui lòng ký tên trước khi xem trước.');
                    return;
                }
                const dataUrl = sigCanvas.toDataURL('image/png');
                if (sigPreviewImg) sigPreviewImg.src = dataUrl;
                if (sigDownloadLink) sigDownloadLink.href = dataUrl;
                if (sigPreviewBox) sigPreviewBox.classList.add('is-visible');
            });
        }

        if (sigSaveBtn) {
            sigSaveBtn.addEventListener('click', async () => {
                if (!hasStrokes) {
                    showToast('Vui lòng ký tên vào khung trước khi lưu.');
                    return;
                }
                const guestName = (sigNameInput?.value || '').trim() || 'Khách mời';
                const dataUrl = sigCanvas.toDataURL('image/png');

                if (!signatureUrl) {
                    if (sigStatus) sigStatus.textContent = `Cảm ơn ${guestName} đã lưu bút chúc phúc!`;
                    showToast('Đã lưu chữ ký chúc phúc (bản xem trước).');
                    return;
                }

                sigSaveBtn.disabled = true;
                try {
                    const res = await fetch(signatureUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ guest_name: guestName, signature_data: dataUrl }),
                    });
                    if (!res.ok) throw new Error('Request failed');
                    if (sigStatus) sigStatus.textContent = `Cảm ơn ${guestName} đã gửi chữ ký chúc phúc!`;
                    showToast('Lưu chữ ký thành công!');
                } catch (_err) {
                    if (sigStatus) sigStatus.textContent = 'Không thể kết nối máy chủ, bạn có thể tải ảnh chữ ký về máy.';
                    showToast('Chưa lưu được lên máy chủ, vui lòng thử lại.');
                } finally {
                    sigSaveBtn.disabled = false;
                }
            });
        }

        resizeSigCanvas(false);
        window.addEventListener('resize', () => resizeSigCanvas(true));
    }

    /* =====================================================================
     * 11. RSVP FORM SUBMISSION
     * ===================================================================== */
    const rsvpForm = document.getElementById('mayhenRsvpForm');
    const rsvpFeedback = document.getElementById('mayhenRsvpFeedback');

    if (rsvpForm) {
        rsvpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const guestName = (rsvpForm.querySelector('[name="guest_name"]')?.value || '').trim();
            const attendingStatus = rsvpForm.querySelector('[name="attending_status"]')?.value || 'attending';
            const adultsCount = parseInt(rsvpForm.querySelector('[name="adults_count"]')?.value || '1', 10) || 1;
            const childrenCount = parseInt(rsvpForm.querySelector('[name="children_count"]')?.value || '0', 10) || 0;
            const wishes = (rsvpForm.querySelector('[name="wishes"]')?.value || '').trim();

            if (!guestName) {
                if (rsvpFeedback) rsvpFeedback.textContent = 'Vui lòng nhập họ và tên của bạn.';
                return;
            }

            if (!rsvpUrl) {
                if (rsvpFeedback) rsvpFeedback.textContent = `Cảm ơn ${guestName} đã xác nhận tham dự!`;
                showToast('Đã ghi nhận phản hồi tham dự (chế độ xem thử).');
                return;
            }

            const submitBtn = rsvpForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            try {
                const res = await fetch(rsvpUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        guest_name: guestName,
                        attending_status: attendingStatus,
                        adults_count: adultsCount,
                        children_count: childrenCount,
                        wishes,
                    }),
                });
                if (!res.ok) throw new Error('RSVP failed');
                if (rsvpFeedback) rsvpFeedback.textContent = `Cảm ơn ${guestName} đã hồi âm! Hẹn gặp bạn trong ngày vui.`;
                showToast('Gửi xác nhận tham dự thành công!');
                rsvpForm.reset();
            } catch (_err) {
                if (rsvpFeedback) rsvpFeedback.textContent = 'Có lỗi xảy ra, vui lòng thử lại sau.';
                showToast('Không thể gửi xác nhận lúc này.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    /* =====================================================================
     * 12. BAMBOO WISHBOOK FORM SUBMISSION (XSS-Safe DOM Creation)
     * ===================================================================== */
    const wishForm = document.getElementById('mayhenWishForm');
    const wishFeedback = document.getElementById('mayhenWishFeedback');
    const wishesList = document.getElementById('mayhenWishesList');

    function prependBambooWish(author, message) {
        if (!wishesList) return;
        const placeholder = wishesList.querySelector('[data-placeholder-wish="1"]');
        if (placeholder) placeholder.remove();

        const slip = document.createElement('article');
        slip.className = 'mayhen_invitation_wish_slip';

        const authorEl = document.createElement('p');
        authorEl.className = 'mayhen_invitation_wish_author';
        authorEl.textContent = author;

        const textEl = document.createElement('p');
        textEl.className = 'mayhen_invitation_wish_text';
        textEl.textContent = message;

        slip.appendChild(authorEl);
        slip.appendChild(textEl);
        wishesList.insertBefore(slip, wishesList.firstChild);
    }

    if (wishForm) {
        wishForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = (wishForm.querySelector('[name="name"]')?.value || '').trim();
            const message = (wishForm.querySelector('[name="message"]')?.value || '').trim();

            if (!name || !message) {
                if (wishFeedback) wishFeedback.textContent = 'Vui lòng nhập đầy đủ tên và lời chúc.';
                return;
            }

            if (!wishUrl) {
                prependBambooWish(name, message);
                if (wishFeedback) wishFeedback.textContent = 'Cảm ơn lời chúc ấm áp của bạn!';
                showToast('Đã treo thẻ tre lời chúc của bạn!');
                wishForm.reset();
                return;
            }

            const submitBtn = wishForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            try {
                const res = await fetch(wishUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ name, message }),
                });
                if (!res.ok) throw new Error('Wish failed');
                prependBambooWish(name, message);
                if (wishFeedback) wishFeedback.textContent = 'Cảm ơn lời chúc ấm áp của bạn!';
                showToast('Gửi lời chúc thành công!');
                wishForm.reset();
            } catch (_err) {
                if (wishFeedback) wishFeedback.textContent = 'Không thể gửi lời chúc lúc này, vui lòng thử lại.';
                showToast('Gửi lời chúc thất bại.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    /* =====================================================================
     * 13. STUDIO LIVE SYNC (MAYHEN_SYNC_INPUT)
     * ===================================================================== */
    function rebuildDresscodeSwatches(csvColors) {
        const container = document.getElementById('mayhenDresscodeSwatches');
        if (!container) return;
        const colors = String(csvColors || '')
            .split(',')
            .map((c) => c.trim())
            .filter(Boolean);
        if (!colors.length) return;

        container.innerHTML = '';
        colors.forEach((hex, idx) => {
            const safeHex = /^#[0-9a-fA-F]{3,8}$/.test(hex) ? hex : '#F4ECDC';
            const label = COLOR_LABELS[safeHex.toUpperCase()] || safeHex.toUpperCase();

            const item = document.createElement('div');
            item.className = 'mayhen_invitation_dco';
            item.style.setProperty('--c', safeHex);

            const vessel = document.createElement('div');
            vessel.className = 'mayhen_invitation_dco_vessel';
            vessel.innerHTML =
                `<svg class="mayhen_invitation_dco_flower" viewBox="0 0 64 58" aria-hidden="true" style="animation-delay:${(idx * 0.5).toFixed(1)}s">` +
                '<path class="cuong" d="M32 58 Q30 42 26 28 Q23 18 18 12" />' +
                '<path class="cuong" d="M32 58 Q35 44 41 31 Q46 22 50 16" />' +
                '<path class="cuong" d="M32 58 Q32 40 34 20" />' +
                '<path class="la" d="M26 42 Q12 36 8 28 Q20 32 28 38 Z" />' +
                '<path class="la" d="M36 44 Q50 38 56 30 Q44 34 36 40 Z" />' +
                '<path class="la" d="M31 32 Q20 22 14 18 Q24 22 32 28 Z" />' +
                '<g transform="translate(34,17)">' +
                '<path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" />' +
                '<path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(72)" />' +
                '<path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(144)" />' +
                '<path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(216)" />' +
                '<path class="canh" d="M0 0 C-5-3-6-11 0-13 C6-11 5-3 0 0Z" transform="rotate(288)" />' +
                '<circle class="nhuy" r="2.6" /></g>' +
                '<path class="nu" d="M50 18 C47 14 48 9 52 7 C55 11 53 16 50 18Z" /></svg>' +
                `<div class="mayhen_invitation_dco_tube" style="--c:${safeHex}">` +
                '<i class="mayhen_invitation_dco_mouth"></i>' +
                '<i class="mayhen_invitation_dco_node mayhen_invitation_dco_node--1"></i>' +
                '<i class="mayhen_invitation_dco_node mayhen_invitation_dco_node--2"></i>' +
                '<i class="mayhen_invitation_dco_silk"></i>' +
                '<svg class="mayhen_invitation_dco_bow" viewBox="0 0 40 20" aria-hidden="true">' +
                '<path d="M20 10 C14 3 4 2 2 8 C1 13 11 16 20 10 Z" />' +
                '<path d="M20 10 C26 3 36 2 38 8 C39 13 29 16 20 10 Z" />' +
                '<path d="M18 11 L12 19 L16 18 L20 12 Z" />' +
                '<path d="M22 11 L28 19 L24 18 L20 12 Z" />' +
                '<circle cx="20" cy="10" r="2.6" /></svg></div>' +
                '<span class="mayhen_invitation_dco_shadow" aria-hidden="true"></span>';

            const labelEl = document.createElement('span');
            labelEl.className = 'mayhen_invitation_dco_label';
            labelEl.textContent = label;

            item.appendChild(vessel);
            item.appendChild(labelEl);
            container.appendChild(item);
        });
    }

    window.addEventListener('message', (event) => {
        const data = event.data;
        if (!data || data.type !== 'MAYHEN_SYNC_INPUT') return;
        const field = String(data.field || '');
        const value = data.value;

        if (field === 'dresscode_colors' && typeof value === 'string') {
            rebuildDresscodeSwatches(value);
            return;
        }
        if (field === 'wedding_date' && typeof value === 'string') {
            root.dataset.weddingDate = value;
            renderMayhenWeddingCalendar(value);
            const t = (root.dataset.partyTime || '11:00').slice(0, 5);
            updateMayhenCountdown(`${value}T${t}:00+07:00`);
            const { year, month, day } = parseWeddingDateParts(value);
            const pad2 = (n) => String(n).padStart(2, '0');
            root.querySelectorAll('[data-sync-target="wedding_date"]').forEach((el) => {
                el.textContent = `${pad2(day)} · ${pad2(month)} · ${year}`;
            });
            return;
        }
        if ((field === 'party_time' || field === 'event_target_time') && typeof value === 'string') {
            root.dataset.partyTime = value;
            const d = root.dataset.weddingDate || '2026-12-20';
            updateMayhenCountdown(`${d}T${value.slice(0, 5)}:00+07:00`);
        }
        if (field === 'event_party_map_link' && typeof value === 'string') {
            const link = document.getElementById('mayhenMapExternalLink');
            if (link && value.trim()) link.href = value.trim();
            return;
        }
        if (field === 'groom_name' && typeof value === 'string') {
            const parts = value.trim().split(/\s+/);
            const initial = (parts[parts.length - 1] || 'H').charAt(0).toUpperCase();
            root.querySelectorAll('[data-monogram-role="groom"]').forEach((el) => {
                el.textContent = initial;
            });
        }
        if (field === 'bride_name' && typeof value === 'string') {
            const parts = value.trim().split(/\s+/);
            const initial = (parts[parts.length - 1] || 'N').charAt(0).toUpperCase();
            root.querySelectorAll('[data-monogram-role="bride"]').forEach((el) => {
                el.textContent = initial;
            });
        }
    });
})();
