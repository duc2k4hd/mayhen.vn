/**
 * TEMPLATE: Nét Đẹp Của Đính Ước (net-dep-cua-dinh-uoc)
 * Pure Vanilla ES Module | Zero External Libraries
 */

const MONTH_NAMES_VI = [
    '', 'Tháng Một', 'Tháng Hai', 'Tháng Ba', 'Tháng Tư',
    'Tháng Năm', 'Tháng Sáu', 'Tháng Bảy', 'Tháng Tám',
    'Tháng Chín', 'Tháng Mười', 'Tháng Mười Một', 'Tháng Mười Hai',
];

const COLOR_LABELS = {
    '#6b503d': 'Nâu Trầm',
    '#ecdbd4': 'Hồng Phấn',
    '#f1f3f4': 'Trắng Khói',
    '#a6a6a6': 'Xám Bạc',
    '#fbf5ee': 'Kem Ngà',
    '#de8d13': 'Hổ Phách',
    '#ffffff': 'Trắng',
    '#000000': 'Đen',
};

const root = document.querySelector('[data-mayhen-root]');
if (root) {
    const isEmbed = root.dataset.isEmbed === '1';
    const isPreview = root.dataset.isPreview === '1';
    const csrfToken = root.dataset.csrfToken || '';
    const rsvpEndpoint = root.dataset.rsvpEndpoint || '';
    const wishEndpoint = root.dataset.wishEndpoint || '';
    const signatureEndpoint = root.dataset.signatureEndpoint || '';

    const toastEl = root.querySelector('[data-toast]');
    let toastTimer = null;

    function showToast(message) {
        if (!toastEl || !message) return;
        toastEl.textContent = message;
        toastEl.classList.add('mayhen_invitation_toast--show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toastEl.classList.remove('mayhen_invitation_toast--show');
        }, 2600);
    }

    // 1. Desktop Blurred Backdrop
    const backdropEl = root.querySelector('.mayhen_invitation_desktop_backdrop');
    if (backdropEl && backdropEl.dataset.backdropSrc) {
        backdropEl.style.backgroundImage = `url("${backdropEl.dataset.backdropSrc}")`;
    }

    // 2. Audio Controller
    const audioEl = root.querySelector('[data-mayhen-audio]');
    const musicBtn = root.querySelector('[data-music-toggle]');

    function syncMusicUi(playing) {
        if (!musicBtn) return;
        musicBtn.classList.toggle('mayhen_invitation_audio_btn--playing', playing);
        musicBtn.setAttribute('aria-pressed', playing ? 'true' : 'false');
    }

    function playAudioSafe() {
        if (!audioEl || isEmbed) return;
        const p = audioEl.play();
        if (p && typeof p.then === 'function') {
            p.then(() => syncMusicUi(true)).catch(() => syncMusicUi(false));
        }
    }

    if (musicBtn && audioEl) {
        musicBtn.addEventListener('click', () => {
            if (audioEl.paused) {
                playAudioSafe();
            } else {
                audioEl.pause();
                syncMusicUi(false);
            }
        });
    }

    // 3. Opening Curtain Unlock
    const openingCurtain = root.querySelector('[data-opening-curtain]');
    const openBtn = root.querySelector('[data-open-card-btn]');

    if (openingCurtain && !isEmbed) {
        document.body.classList.add('mayhen_invitation_locked');
    }

    function unlockInvitation() {
        if (!openingCurtain) return;
        openingCurtain.classList.add('mayhen_invitation_opening--unlocking');
        document.body.classList.remove('mayhen_invitation_locked');
        root.classList.add('mayhen_invitation_root--opened');
        playAudioSafe();

        setTimeout(() => {
            openingCurtain.classList.add('mayhen_invitation_opening--done');
        }, 1050);
    }

    if (openBtn) {
        openBtn.addEventListener('click', unlockInvitation);
    }

    // 4. Scroll Reveal (IntersectionObserver)
    const revealEls = Array.from(root.querySelectorAll('.mayhen_invitation_reveal'));
    if (isEmbed || !('IntersectionObserver' in window)) {
        revealEls.forEach((el) => el.classList.add('mayhen_invitation_reveal--visible'));
    } else {
        const revealObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('mayhen_invitation_reveal--visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.08, rootMargin: '0px 0px -24px 0px' }
        );
        revealEls.forEach((el) => revealObserver.observe(el));
    }

    // 5. Countdown Timer + Global Live Sync Hook
    const cdRoot = root.querySelector('[data-countdown-root]');
    const cdDays = root.querySelector('[data-cd-days]');
    const cdHours = root.querySelector('[data-cd-hours]');
    const cdMinutes = root.querySelector('[data-cd-minutes]');
    const cdSeconds = root.querySelector('[data-cd-seconds]');
    let countdownTimer = null;
    let currentTargetMs = cdRoot ? Date.parse(cdRoot.dataset.targetIso || '') : NaN;

    function pad2(n) {
        return String(Math.max(0, Math.floor(n))).padStart(2, '0');
    }

    function tickCountdown() {
        if (!cdDays || !cdHours || !cdMinutes || !cdSeconds || Number.isNaN(currentTargetMs)) return;
        const diff = Math.max(0, currentTargetMs - Date.now());
        const days = Math.floor(diff / 86400000);
        const hours = Math.floor((diff % 86400000) / 3600000);
        const mins = Math.floor((diff % 3600000) / 60000);
        const secs = Math.floor((diff % 60000) / 1000);
        cdDays.textContent = String(days).padStart(2, '0');
        cdHours.textContent = pad2(hours);
        cdMinutes.textContent = pad2(mins);
        cdSeconds.textContent = pad2(secs);
    }

    function startCountdown(isoString) {
        const parsed = Date.parse(isoString);
        if (!Number.isNaN(parsed)) {
            currentTargetMs = parsed;
            if (cdRoot) cdRoot.dataset.targetIso = isoString;
        }
        tickCountdown();
        clearInterval(countdownTimer);
        countdownTimer = setInterval(tickCountdown, 1000);
    }

    if (cdRoot) {
        startCountdown(cdRoot.dataset.targetIso || '');
    }

    window.updateMayhenCountdown = function updateMayhenCountdown(dateStr, timeStr) {
        if (!dateStr) return;
        const cleanDate = String(dateStr).slice(0, 10);
        const cleanTime = timeStr && /^\d{1,2}:\d{2}/.test(String(timeStr))
            ? String(timeStr).slice(0, 5)
            : '10:00';
        startCountdown(`${cleanDate}T${cleanTime}:00+07:00`);
    };

    // 6. Calendar Renderer + Global Live Sync Hook
    const calContainer = root.querySelector('[data-wedding-calendar]');
    const calGrid = root.querySelector('[data-calendar-grid]');
    const calMonthLabel = root.querySelector('[data-cal-month-label]');
    const calYearLabel = root.querySelector('[data-cal-year-label]');

    window.renderMayhenWeddingCalendar = function renderMayhenWeddingCalendar(dateInput) {
        if (!calGrid || !dateInput) return;
        const match = String(dateInput).match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
        if (!match) return;
        const year = Number(match[1]);
        const month = Number(match[2]);
        const selectedDay = Number(match[3]);

        if (calMonthLabel) calMonthLabel.textContent = MONTH_NAMES_VI[month] || `Tháng ${month}`;
        if (calYearLabel) calYearLabel.textContent = String(year);
        if (calContainer) {
            calContainer.dataset.year = String(year);
            calContainer.dataset.month = String(month);
            calContainer.dataset.day = String(selectedDay);
        }

        const firstDow = new Date(year, month - 1, 1).getDay();
        const leadingBlanks = (firstDow + 6) % 7;
        const daysInMonth = new Date(year, month, 0).getDate();

        calGrid.textContent = '';
        for (let b = 0; b < leadingBlanks; b++) {
            const blank = document.createElement('span');
            blank.className = 'mayhen_invitation_cal_cell mayhen_invitation_cal_cell--empty';
            calGrid.appendChild(blank);
        }
        for (let d = 1; d <= daysInMonth; d++) {
            const cell = document.createElement('span');
            cell.className = 'mayhen_invitation_cal_cell' + (d === selectedDay ? ' mayhen_invitation_cal_cell--active' : '');
            cell.textContent = String(d);
            calGrid.appendChild(cell);
        }
    };

    // 7. Studio Editor Live Sync Listener
    const dresscodeContainer = root.querySelector('[data-dresscode-container]');

    function renderDresscodeSwatches(rawColors) {
        if (!dresscodeContainer || typeof rawColors !== 'string') return;
        const hexes = rawColors
            .split(',')
            .map((s) => s.trim())
            .filter((s) => /^#[0-9A-Fa-f]{3,6}$/.test(s));
        if (!hexes.length) return;

        dresscodeContainer.textContent = '';
        hexes.forEach((hex) => {
            const item = document.createElement('div');
            item.className = 'mayhen_invitation_swatch_item';

            const frame = document.createElement('div');
            frame.className = 'mayhen_invitation_swatch_frame';

            const swatch = document.createElement('span');
            swatch.className = 'mayhen_invitation_swatch_color';
            swatch.style.backgroundColor = hex;

            const heart = document.createElement('span');
            heart.className = 'mayhen_invitation_swatch_heart';
            heart.setAttribute('aria-hidden', 'true');
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('viewBox', '0 0 24 21');
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', 'M12 20.4S1.6 14 1.6 6.9A5.4 5.4 0 0 1 12 4.6a5.4 5.4 0 0 1 10.4 2.3C22.4 14 12 20.4 12 20.4z');
            svg.appendChild(path);
            heart.appendChild(svg);

            frame.appendChild(swatch);
            frame.appendChild(heart);

            const label = document.createElement('span');
            label.className = 'mayhen_invitation_swatch_label';
            label.textContent = COLOR_LABELS[hex.toLowerCase()] || hex.toUpperCase();

            item.appendChild(frame);
            item.appendChild(label);
            dresscodeContainer.appendChild(item);
        });
    }

    window.addEventListener('message', (event) => {
        const data = event.data;
        if (!data || data.type !== 'MAYHEN_SYNC_INPUT') return;
        const field = data.field || data.key || '';
        const val = data.value;

        if (field === 'dresscode_colors' || field.endsWith('.dresscode_colors')) {
            renderDresscodeSwatches(String(val || ''));
        } else if (field === 'wedding_date' || field.endsWith('.wedding_date')) {
            window.renderMayhenWeddingCalendar(String(val || ''));
            window.updateMayhenCountdown(String(val || ''));
        } else if (field === 'event_target_time' || field.endsWith('.event_target_time')) {
            const curIso = cdRoot ? (cdRoot.dataset.targetIso || '').slice(0, 10) : '';
            if (curIso) window.updateMayhenCountdown(curIso, String(val || '10:00'));
        } else if (field === 'event_party_map_link' || field.endsWith('.event_party_map_link')) {
            const mapLinkEl = root.querySelector('[data-sync-target="event_party_map_link"]');
            if (mapLinkEl && typeof val === 'string' && /^https?:\/\//i.test(val.trim())) {
                mapLinkEl.setAttribute('href', val.trim());
            }
        }
    });

    // 8. 3D Coverflow Album + Lightbox
    const coverflowEl = root.querySelector('[data-coverflow]');
    const slides = coverflowEl ? Array.from(coverflowEl.querySelectorAll('[data-coverflow-slide]')) : [];
    const dots = Array.from(root.querySelectorAll('[data-coverflow-dot]'));
    const prevBtn = root.querySelector('[data-coverflow-prev]');
    const nextBtn = root.querySelector('[data-coverflow-next]');
    let activeSlide = 0;

    function layoutCoverflow() {
        const total = slides.length;
        if (!total) return;
        slides.forEach((slide, idx) => {
            let offset = idx - activeSlide;
            if (offset > total / 2) offset -= total;
            if (offset < -total / 2) offset += total;

            const abs = Math.abs(offset);
            const tx = offset * 126;
            const tz = -abs * 120;
            const ry = offset === 0 ? 0 : (offset > 0 ? -34 : 34);
            const scale = offset === 0 ? 1 : Math.max(0.76, 0.9 - abs * 0.06);
            const opacity = abs > 2 ? 0 : (abs === 2 ? 0.45 : (abs === 1 ? 0.85 : 1));

            slide.style.transform = `translateX(${tx}px) translateZ(${tz}px) rotateY(${ry}deg) scale(${scale})`;
            slide.style.zIndex = String(20 - abs);
            slide.style.opacity = String(opacity);
            slide.style.pointerEvents = abs <= 2 ? 'auto' : 'none';
        });

        dots.forEach((dot, idx) => {
            dot.classList.toggle('mayhen_invitation_coverflow_dot--active', idx === activeSlide);
        });
    }

    function goSlide(index) {
        if (!slides.length) return;
        activeSlide = (index + slides.length) % slides.length;
        layoutCoverflow();
    }

    if (slides.length) {
        layoutCoverflow();
        if (prevBtn) prevBtn.addEventListener('click', () => goSlide(activeSlide - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => goSlide(activeSlide + 1));
        dots.forEach((dot) => {
            dot.addEventListener('click', () => goSlide(Number(dot.dataset.coverflowDot || 0)));
        });

        let touchStartX = 0;
        coverflowEl.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].clientX;
        }, { passive: true });
        coverflowEl.addEventListener('touchend', (e) => {
            const dx = e.changedTouches[0].clientX - touchStartX;
            if (Math.abs(dx) > 36) {
                goSlide(dx < 0 ? activeSlide + 1 : activeSlide - 1);
            }
        }, { passive: true });
    }

    // Lightbox
    const lightboxEl = root.querySelector('[data-lightbox]');
    const lightboxImg = root.querySelector('[data-lightbox-img]');
    const lbClose = root.querySelector('[data-lightbox-close]');
    const lbPrev = root.querySelector('[data-lightbox-prev]');
    const lbNext = root.querySelector('[data-lightbox-next]');
    let lightboxIdx = 0;

    function openLightbox(idx) {
        if (!lightboxEl || !lightboxImg || !slides.length) return;
        lightboxIdx = (idx + slides.length) % slides.length;
        const img = slides[lightboxIdx].querySelector('img');
        if (img) {
            lightboxImg.src = img.currentSrc || img.src;
            lightboxImg.alt = img.alt;
        }
        lightboxEl.classList.add('mayhen_invitation_lightbox--open');
    }

    function closeLightbox() {
        if (lightboxEl) lightboxEl.classList.remove('mayhen_invitation_lightbox--open');
    }

    slides.forEach((slide, idx) => {
        slide.addEventListener('click', () => {
            if (idx === activeSlide) {
                openLightbox(idx);
            } else {
                goSlide(idx);
            }
        });
    });

    if (lbClose) lbClose.addEventListener('click', closeLightbox);
    if (lbPrev) lbPrev.addEventListener('click', () => openLightbox(lightboxIdx - 1));
    if (lbNext) lbNext.addEventListener('click', () => openLightbox(lightboxIdx + 1));
    if (lightboxEl) {
        lightboxEl.addEventListener('click', (e) => {
            if (e.target === lightboxEl) closeLightbox();
        });
    }
    document.addEventListener('keydown', (e) => {
        if (!lightboxEl || !lightboxEl.classList.contains('mayhen_invitation_lightbox--open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') openLightbox(lightboxIdx - 1);
        if (e.key === 'ArrowRight') openLightbox(lightboxIdx + 1);
    });

    // 9. Brocade Gift Pouch ("Túi Gấm") + 1-Tap Copy STK
    const giftStage = root.querySelector('[data-gift-stage]');
    const pouchBtn = root.querySelector('[data-pouch-toggle]');
    if (giftStage && pouchBtn) {
        pouchBtn.addEventListener('click', () => {
            giftStage.classList.add('mayhen_invitation_gift_stage--open');
            pouchBtn.setAttribute('aria-expanded', 'true');
            showToast('Đã mở túi gấm mừng cưới');
        });
    }

    root.querySelectorAll('[data-copy-account]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const acc = btn.dataset.copyAccount || '';
            if (!acc) return;
            const onDone = () => {
                btn.classList.add('mayhen_invitation_copy_btn--copied');
                btn.textContent = 'Đã sao chép';
                showToast(`Đã sao chép STK: ${acc}`);
                setTimeout(() => {
                    btn.classList.remove('mayhen_invitation_copy_btn--copied');
                    btn.textContent = 'Sao chép STK';
                }, 2000);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(acc).then(onDone).catch(onDone);
            } else {
                onDone();
            }
        });
    });

    // 10. Signature Canvas (Pointer Events + DPR + Resize Buffer)
    const sigBoard = root.querySelector('[data-sig-board]');
    const sigCanvas = root.querySelector('[data-sig-canvas]');
    const sigNameInput = root.querySelector('[data-sig-name]');
    const sigClearBtn = root.querySelector('[data-sig-clear]');
    const sigSaveBtn = root.querySelector('[data-sig-save]');
    const sigFeedback = root.querySelector('[data-sig-feedback]');
    const sigWall = root.querySelector('[data-sig-wall]');

    if (sigCanvas && sigBoard) {
        const ctx = sigCanvas.getContext('2d');
        let drawing = false;
        let strokes = [];
        let currentStroke = null;

        function redrawStrokes() {
            if (!ctx) return;
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            const w = sigBoard.clientWidth || 320;
            const h = sigBoard.clientHeight || 196;
            sigCanvas.width = Math.round(w * dpr);
            sigCanvas.height = Math.round(h * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, w, h);
            ctx.strokeStyle = '#4A3528';
            ctx.lineWidth = 2.4;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            strokes.forEach((pts) => {
                if (!pts.length) return;
                ctx.beginPath();
                ctx.moveTo(pts[0].x * w, pts[0].y * h);
                for (let i = 1; i < pts.length; i++) {
                    ctx.lineTo(pts[i].x * w, pts[i].y * h);
                }
                ctx.stroke();
            });
        }

        redrawStrokes();
        window.addEventListener('resize', redrawStrokes);

        function getNormPoint(e) {
            const rect = sigCanvas.getBoundingClientRect();
            const x = Math.max(0, Math.min(1, (e.clientX - rect.left) / (rect.width || 1)));
            const y = Math.max(0, Math.min(1, (e.clientY - rect.top) / (rect.height || 1)));
            return { x, y };
        }

        sigCanvas.addEventListener('pointerdown', (e) => {
            drawing = true;
            sigCanvas.setPointerCapture(e.pointerId);
            currentStroke = [getNormPoint(e)];
            strokes.push(currentStroke);
            sigBoard.classList.add('mayhen_invitation_sig_board--drawn');
            redrawStrokes();
        });

        sigCanvas.addEventListener('pointermove', (e) => {
            if (!drawing || !currentStroke) return;
            currentStroke.push(getNormPoint(e));
            redrawStrokes();
        });

        function endStroke() {
            drawing = false;
            currentStroke = null;
        }
        sigCanvas.addEventListener('pointerup', endStroke);
        sigCanvas.addEventListener('pointercancel', endStroke);

        if (sigClearBtn) {
            sigClearBtn.addEventListener('click', () => {
                strokes = [];
                sigBoard.classList.remove('mayhen_invitation_sig_board--drawn');
                redrawStrokes();
                if (sigFeedback) sigFeedback.textContent = '';
            });
        }

        if (sigSaveBtn) {
            sigSaveBtn.addEventListener('click', async () => {
                const signerName = (sigNameInput ? sigNameInput.value : '').trim();
                if (!signerName) {
                    if (sigFeedback) {
                        sigFeedback.textContent = 'Vui lòng nhập tên của bạn trước khi lưu chữ ký.';
                        sigFeedback.classList.add('mayhen_invitation_feedback--error');
                    }
                    return;
                }
                if (!strokes.length) {
                    if (sigFeedback) {
                        sigFeedback.textContent = 'Vui lòng vẽ chữ ký của bạn lên khung giấy.';
                        sigFeedback.classList.add('mayhen_invitation_feedback--error');
                    }
                    return;
                }

                const dataUrl = sigCanvas.toDataURL('image/png');
                if (sigFeedback) sigFeedback.classList.remove('mayhen_invitation_feedback--error');

                if (isPreview || !signatureEndpoint) {
                    appendSignatureChip(signerName, dataUrl);
                    if (sigFeedback) sigFeedback.textContent = 'Đã lưu chữ ký xem thử!';
                    showToast('Đã lưu chữ ký xem thử');
                    return;
                }

                sigSaveBtn.disabled = true;
                try {
                    const res = await fetch(signatureEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            guest_name: signerName,
                            signature_data: dataUrl,
                        }),
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    appendSignatureChip(signerName, dataUrl);
                    strokes = [];
                    sigBoard.classList.remove('mayhen_invitation_sig_board--drawn');
                    redrawStrokes();
                    if (sigFeedback) sigFeedback.textContent = 'Cảm ơn bạn đã lưu giữ chữ ký kỷ niệm!';
                    showToast('Lưu chữ ký thành công');
                } catch {
                    if (sigFeedback) {
                        sigFeedback.textContent = 'Chưa thể lưu chữ ký lúc này, vui lòng thử lại.';
                        sigFeedback.classList.add('mayhen_invitation_feedback--error');
                    }
                } finally {
                    sigSaveBtn.disabled = false;
                }
            });
        }

        function appendSignatureChip(name, imgSrc) {
            if (!sigWall) return;
            const chip = document.createElement('div');
            chip.className = 'mayhen_invitation_sig_chip';
            const img = document.createElement('img');
            img.src = imgSrc;
            img.alt = `Chữ ký của ${name}`;
            const p = document.createElement('p');
            p.className = 'mayhen_invitation_sig_chip_name';
            p.textContent = name;
            chip.appendChild(img);
            chip.appendChild(p);
            sigWall.prepend(chip);
        }
    }

    // 11. RSVP Form Submission
    const rsvpForm = root.querySelector('[data-rsvp-form]');
    const rsvpStatusInput = root.querySelector('[data-rsvp-status-input]');
    const rsvpStatusGroup = root.querySelector('[data-rsvp-status-group]');
    const rsvpSubmitBtn = root.querySelector('[data-rsvp-submit]');
    const rsvpFeedback = root.querySelector('[data-rsvp-feedback]');

    if (rsvpStatusGroup && rsvpStatusInput) {
        rsvpStatusGroup.querySelectorAll('[data-status-val]').forEach((btn) => {
            btn.addEventListener('click', () => {
                rsvpStatusInput.value = btn.dataset.statusVal || 'confirmed';
                rsvpStatusGroup.querySelectorAll('[data-status-val]').forEach((b) => {
                    b.classList.toggle('mayhen_invitation_choice_btn--active', b === btn);
                });
            });
        });
    }

    if (rsvpForm) {
        rsvpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(rsvpForm);
            const guestName = String(fd.get('guest_name') || '').trim();
            if (!guestName) {
                if (rsvpFeedback) {
                    rsvpFeedback.textContent = 'Vui lòng nhập tên của bạn.';
                    rsvpFeedback.classList.add('mayhen_invitation_feedback--error');
                }
                return;
            }
            if (rsvpFeedback) rsvpFeedback.classList.remove('mayhen_invitation_feedback--error');

            if (isPreview || !rsvpEndpoint) {
                if (rsvpFeedback) rsvpFeedback.textContent = 'Cảm ơn bạn đã xác nhận tham dự (chế độ xem thử)!';
                showToast('Đã ghi nhận xác nhận xem thử');
                return;
            }

            if (rsvpSubmitBtn) rsvpSubmitBtn.disabled = true;
            try {
                const res = await fetch(rsvpEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        guest_name: guestName,
                        guest_phone: String(fd.get('guest_phone') || '').trim(),
                        attending_status: String(fd.get('status') || 'confirmed') === 'declined' ? 'declined' : 'attending',
                        adults_count: Number(fd.get('attending_count') || 1),
                        children_count: 0,
                        wishes: String(fd.get('notes') || '').trim(),
                    }),
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                if (rsvpFeedback) rsvpFeedback.textContent = 'Cảm ơn bạn! Chúng mình đã nhận được xác nhận tham dự.';
                showToast('Xác nhận tham dự thành công');
            } catch {
                if (rsvpFeedback) {
                    rsvpFeedback.textContent = 'Không thể gửi xác nhận lúc này, vui lòng thử lại.';
                    rsvpFeedback.classList.add('mayhen_invitation_feedback--error');
                }
            } finally {
                if (rsvpSubmitBtn) rsvpSubmitBtn.disabled = false;
            }
        });
    }

    // 12. Wishes Form Submission (XSS-Safe DOM)
    const wishForm = root.querySelector('[data-wish-form]');
    const wishSubmitBtn = root.querySelector('[data-wish-submit]');
    const wishFeedback = root.querySelector('[data-wish-feedback]');
    const wishesFeed = root.querySelector('[data-wishes-feed]');

    function prependWishItem(author, message) {
        if (!wishesFeed) return;
        const emptyEl = wishesFeed.querySelector('[data-wishes-empty]');
        if (emptyEl) emptyEl.remove();

        const item = document.createElement('div');
        item.className = 'mayhen_invitation_wish_item';
        const pAuthor = document.createElement('p');
        pAuthor.className = 'mayhen_invitation_wish_author';
        pAuthor.textContent = author;
        const pText = document.createElement('p');
        pText.className = 'mayhen_invitation_wish_text';
        pText.textContent = message;
        item.appendChild(pAuthor);
        item.appendChild(pText);
        wishesFeed.prepend(item);
    }

    if (wishForm) {
        wishForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(wishForm);
            const author = String(fd.get('guest_name') || '').trim();
            const message = String(fd.get('message') || '').trim();
            if (!author || !message) {
                if (wishFeedback) {
                    wishFeedback.textContent = 'Vui lòng nhập đầy đủ tên và lời chúc của bạn.';
                    wishFeedback.classList.add('mayhen_invitation_feedback--error');
                }
                return;
            }
            if (wishFeedback) wishFeedback.classList.remove('mayhen_invitation_feedback--error');

            if (isPreview || !wishEndpoint) {
                prependWishItem(author, message);
                wishForm.reset();
                if (wishFeedback) wishFeedback.textContent = 'Cảm ơn lời chúc ngọt ngào của bạn (chế độ xem thử)!';
                showToast('Đã thêm lời chúc xem thử');
                return;
            }

            if (wishSubmitBtn) wishSubmitBtn.disabled = true;
            try {
                const res = await fetch(wishEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ name: author, message }),
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                prependWishItem(author, message);
                wishForm.reset();
                if (wishFeedback) wishFeedback.textContent = 'Cảm ơn bạn đã gửi lời chúc phúc tới chúng mình!';
                showToast('Gửi lời chúc thành công');
            } catch {
                if (wishFeedback) {
                    wishFeedback.textContent = 'Chưa thể gửi lời chúc lúc này, vui lòng thử lại.';
                    wishFeedback.classList.add('mayhen_invitation_feedback--error');
                }
            } finally {
                if (wishSubmitBtn) wishSubmitBtn.disabled = false;
            }
        });
    }
}
