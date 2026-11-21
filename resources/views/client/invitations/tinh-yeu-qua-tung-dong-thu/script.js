/**
 * Mây Hẹn (mayhen.vn) — Native ES Module Controller
 * Template: "Tình yêu qua từng dòng thư" (tinh-yeu-qua-tung-dong-thu)
 * Source Lock: https://s.27biggroup.com/weddingA11 ("Sài Gòn Xưa — Nhật Báo Tình Yêu")
 */

const initTinhYeuQuaTungDongThu = () => {
    const root = document.getElementById('mayhen_invitation_root');
    if (!root) {
        return;
    }

    const isEmbed = root.dataset.isEmbed === '1'
        || window.location.search.includes('embed=1')
        || window.location.search.includes('mode=editor');

    const csrfToken = root.dataset.csrfToken
        || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || '';

    // =========================================================================
    // 1. TOAST NOTIFICATION HELPER
    // =========================================================================
    const toastEl = document.getElementById('mayhenToast');
    let toastTimer = null;
    const showToast = (message) => {
        if (!toastEl || !message) {
            return;
        }
        toastEl.textContent = message;
        toastEl.classList.add('is-visible');
        if (toastTimer) {
            window.clearTimeout(toastTimer);
        }
        toastTimer = window.setTimeout(() => {
            toastEl.classList.remove('is-visible');
        }, 3200);
    };

    // =========================================================================
    // 2. AUDIO & FLOATING VINYL BUTTON
    // =========================================================================
    const audioEl = document.getElementById('mayhenBgAudio');
    const musicBtn = document.getElementById('mayhenMusicToggle');

    const syncMusicState = (playing) => {
        if (!musicBtn) {
            return;
        }
        musicBtn.classList.toggle('is-playing', Boolean(playing));
        musicBtn.setAttribute('aria-pressed', playing ? 'true' : 'false');
    };

    const tryPlayMusic = () => {
        if (!audioEl || isEmbed) {
            return;
        }
        audioEl.play()
            .then(() => syncMusicState(true))
            .catch(() => syncMusicState(false));
    };

    if (musicBtn && audioEl) {
        musicBtn.addEventListener('click', () => {
            if (audioEl.paused) {
                audioEl.play()
                    .then(() => syncMusicState(true))
                    .catch(() => showToast('Trình duyệt tạm chặn phát nhạc tự động.'));
            } else {
                audioEl.pause();
                syncMusicState(false);
            }
        });
    }

    // =========================================================================
    // 3. SINGLE-LINE AUTO-FIT (.sg-1dong)
    // =========================================================================
    const fitSingleLineHeadlines = () => {
        document.querySelectorAll('.sg-1dong').forEach((box) => {
            const inner = box.querySelector('.ladi-headline') || box.firstElementChild;
            if (!inner) {
                return;
            }
            inner.style.fontSize = '';
            const maxW = box.clientWidth;
            const naturalW = inner.scrollWidth;
            if (maxW > 16 && naturalW > maxW + 1) {
                const currentFs = parseFloat(window.getComputedStyle(inner).fontSize) || 32;
                const nextFs = Math.max(11, Math.floor(currentFs * (maxW / naturalW) * 0.96));
                inner.style.fontSize = `${nextFs}px`;
            }
        });
    };

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(fitSingleLineHeadlines);
    }
    window.addEventListener('resize', fitSingleLineHeadlines, { passive: true });
    window.setTimeout(fitSingleLineHeadlines, 180);

    // =========================================================================
    // 4. CONTINUOUS FALLING TAMARIND LEAVES (#sgNenDong)
    // =========================================================================
    const initFallingTamarindLeaves = () => {
        const layer = document.getElementById('sgNenDong');
        if (!layer || isEmbed) {
            return;
        }
        layer.textContent = '';
        const count = 18;
        for (let i = 0; i < count; i++) {
            const leaf = document.createElement('i');
            if (i % 4 === 0) {
                leaf.className = 'vang';
            }
            const scale = 0.75 + (i % 4) * 0.15;
            leaf.style.width = `${(6 * scale).toFixed(1)}px`;
            leaf.style.height = `${(12 * scale).toFixed(1)}px`;
            leaf.style.left = `${(i * 5.7 + (i % 3) * 3.5) % 97}%`;
            leaf.style.setProperty('--lech', `${(i % 2 === 0 ? 1 : -1) * (35 + (i % 5) * 18)}px`);
            leaf.style.setProperty('--xoay', `${(i % 2 === 0 ? 1 : -1) * (360 + (i % 4) * 90)}deg`);
            leaf.style.animationDuration = `${11 + (i % 6) * 1.8}s`;
            leaf.style.animationDelay = `${-(i * 1.3)}s`;
            layer.appendChild(leaf);
        }
    };
    initFallingTamarindLeaves();

    // =========================================================================
    // 5. FOLDED NEWSPAPER OPENING CURTAIN + VESPA RIDE + 26 TAMARIND LEAVES (#sgCua)
    // =========================================================================
    const openingCurtain = document.getElementById('sgCua');
    const openBtn = document.getElementById('sgNutMo');
    const windLeavesBox = document.getElementById('sgLaGio');

    if (isEmbed) {
        document.documentElement.classList.add('is-embed-editor');
        document.body.classList.add('is-embed-editor');
        document.documentElement.classList.remove('sg-khoa', 'no-scroll');
        document.body.classList.remove('sg-khoa', 'no-scroll');
        root.classList.add('opened', 'is-embed');
        if (openingCurtain) {
            openingCurtain.classList.add('an', 'da-mo', 'is-opened');
            openingCurtain.style.display = 'none';
        }
    } else if (openingCurtain && !openingCurtain.classList.contains('da-mo')) {
        document.documentElement.classList.add('sg-khoa');
        document.body.classList.add('sg-khoa');

        if (windLeavesBox && windLeavesBox.children.length === 0) {
            for (let i = 0; i < 26; i++) {
                const leaf = document.createElement('i');
                const angle = (i / 26) * Math.PI * 2;
                const radius = 140 + (i % 6) * 55;
                leaf.style.setProperty('--x', `${Math.round(Math.cos(angle) * radius)}px`);
                leaf.style.setProperty('--y', `${Math.round(Math.sin(angle) * radius * 0.8 - 60)}px`);
                leaf.style.setProperty('--r', `${(i % 2 === 0 ? 1 : -1) * (360 + (i % 5) * 110)}deg`);
                leaf.style.animationDelay = `${(0.45 + (i % 8) * 0.07).toFixed(2)}s`;
                if (i % 3 === 0) {
                    leaf.style.background = 'linear-gradient(160deg, #F4D27A, #D29A33)';
                }
                windLeavesBox.appendChild(leaf);
            }
        }

        let isOpening = false;
        const triggerOpenNewspaper = () => {
            if (isOpening) {
                return;
            }
            isOpening = true;
            root.classList.add('opened');
            openingCurtain.classList.add('dang-mo');
            document.documentElement.classList.remove('sg-khoa', 'no-scroll');
            document.body.classList.remove('sg-khoa', 'no-scroll');
            window.scrollTo(0, 0);
            tryPlayMusic();

            window.setTimeout(() => {
                openingCurtain.classList.add('tat');
            }, 1900);

            window.setTimeout(() => {
                openingCurtain.classList.add('an', 'da-mo', 'is-opened');
                fitSingleLineHeadlines();
            }, 2750);
        };

        openBtn?.addEventListener('click', triggerOpenNewspaper);
        openingCurtain.querySelector('.sg-to-bao')?.addEventListener('click', triggerOpenNewspaper);
    }

    // =========================================================================
    // 6. SCROLL REVEAL (.sg-hien) & SAIGON SKYLINE VESPA (.sg-ket.co-xe)
    // =========================================================================
    const revealItems = document.querySelectorAll('.sg-hien');
    const footerSection = document.getElementById('SECTION_KET');

    if (isEmbed || !('IntersectionObserver' in window)) {
        revealItems.forEach((el) => el.classList.add('da-hien'));
        footerSection?.classList.add('co-xe');
    } else {
        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('da-hien');
                        io.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -30px 0px' }
        );
        revealItems.forEach((el) => io.observe(el));

        if (footerSection) {
            const footerObserver = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        footerSection.classList.toggle('co-xe', entry.isIntersecting);
                    });
                },
                { threshold: 0.15 }
            );
            footerObserver.observe(footerSection);
        }
    }

    // =========================================================================
    // 7. CALENDAR BLOC & WALL CALENDAR RENDERER
    // =========================================================================
    const weekdayNames = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];

    const renderMayhenWeddingCalendar = (dateInput) => {
        const raw = String(dateInput || root.dataset.weddingDate || '2026-12-27').trim();
        const match = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
        const year = match ? parseInt(match[1], 10) : 2026;
        const month = match ? parseInt(match[2], 10) : 12;
        const day = match ? parseInt(match[3], 10) : 27;

        const targetDate = new Date(year, month - 1, day);
        const weekdayLabel = weekdayNames[targetDate.getDay()] || 'Chủ Nhật';
        const dayPadded = String(day).padStart(2, '0');
        const monthPadded = String(month).padStart(2, '0');

        // Update Bloc & Invite date badges
        const setTextById = (id, text) => {
            const el = document.getElementById(id);
            if (el) {
                const inner = el.querySelector('.ladi-headline, .ladi-paragraph') || el;
                inner.textContent = text;
            }
        };

        setTextById('TXT_BLOC_THANG', `Tháng ${month}`);
        setTextById('TXT_BLOC_NAM', `Năm ${year}`);
        setTextById('mayhenBlocWeekday', weekdayLabel);
        setTextById('mayhenBlocDay', dayPadded);
        setTextById('mayhenInviteWeekday', weekdayLabel);
        setTextById('mayhenInviteDay', dayPadded);
        setTextById('mayhenInviteMonth', `Tháng ${month}`);
        setTextById('mayhenInviteYear', `Năm ${year}`);
        setTextById('mayhenPostmarkDayMonth', `${dayPadded}·${monthPadded}`);
        setTextById('mayhenCalMonthTitle', `THÁNG ${monthPadded} / ${year}`);

        const grid = document.getElementById('mayhenCalendarDaysGrid');
        if (!grid) {
            return;
        }
        grid.textContent = '';

        // Monday-first offset (T2=0 ... CN=6)
        const firstDayJs = new Date(year, month - 1, 1).getDay();
        const mondayOffset = (firstDayJs + 6) % 7;
        const daysInMonth = new Date(year, month, 0).getDate();

        for (let e = 0; e < mondayOffset; e++) {
            const emptyCell = document.createElement('span');
            emptyCell.className = 'mayhen_invitation_cal_day is-empty';
            grid.appendChild(emptyCell);
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const cell = document.createElement('span');
            const isSunday = (mondayOffset + d - 1) % 7 === 6;
            const isWeddingDay = d === day;
            cell.className = [
                'mayhen_invitation_cal_day',
                isSunday ? 'is-sunday' : '',
                isWeddingDay ? 'is-wedding-day' : '',
            ]
                .filter(Boolean)
                .join(' ');
            cell.textContent = String(d);
            grid.appendChild(cell);
        }
    };

    window.renderMayhenWeddingCalendar = renderMayhenWeddingCalendar;
    renderMayhenWeddingCalendar(root.dataset.weddingDate);

    // =========================================================================
    // 8. WOODEN VINTAGE RADIO COUNTDOWN ("ĐÀI TÌNH YÊU")
    // =========================================================================
    let currentTargetIso = root.dataset.targetIso || `${root.dataset.weddingDate || '2026-12-27'}T18:00:00`;

    const updateMayhenCountdown = (overrideDateStr) => {
        if (overrideDateStr) {
            const clean = String(overrideDateStr).trim();
            currentTargetIso = clean.includes('T') ? clean : `${clean}T18:00:00`;
        }
        const targetMs = new Date(currentTargetIso).getTime();
        const nowMs = Date.now();
        const diffSec = Number.isFinite(targetMs) ? Math.max(0, Math.floor((targetMs - nowMs) / 1000)) : 0;

        const days = Math.floor(diffSec / 86400);
        const hours = Math.floor((diffSec % 86400) / 3600);
        const minutes = Math.floor((diffSec % 3600) / 60);
        const seconds = diffSec % 60;

        const setDigit = (id, val) => {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = String(val).padStart(2, '0');
            }
        };

        setDigit('mayhenCdDays', days);
        setDigit('mayhenCdHours', hours);
        setDigit('mayhenCdMinutes', minutes);
        setDigit('mayhenCdSeconds', seconds);
    };

    window.updateMayhenCountdown = updateMayhenCountdown;
    updateMayhenCountdown();
    window.setInterval(() => updateMayhenCountdown(), 1000);

    // =========================================================================
    // 9. SAIGON POSTBOX GIFT CHEST & VIETQR COPY (#sgHop / #sgQuaVung)
    // =========================================================================
    const postboxWrap = document.getElementById('sgQuaVung');
    const postboxBtn = document.getElementById('sgHop');

    if (postboxBtn && postboxWrap) {
        const togglePostbox = () => {
            const isOpen = postboxWrap.classList.toggle('da-mo');
            postboxBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        };
        postboxBtn.addEventListener('click', togglePostbox);
        postboxBtn.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                togglePostbox();
            }
        });
    }

    document.querySelectorAll('[data-copy-account]').forEach((btn) => {
        btn.addEventListener('click', async (ev) => {
            ev.stopPropagation();
            const stk = (btn.getAttribute('data-copy-account') || '').trim();
            if (!stk) {
                return;
            }
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(stk);
                } else {
                    const tmp = document.createElement('textarea');
                    tmp.value = stk;
                    document.body.appendChild(tmp);
                    tmp.select();
                    document.execCommand('copy');
                    document.body.removeChild(tmp);
                }
                showToast(`Đã sao chép số tài khoản: ${stk}`);
            } catch {
                showToast(`Số tài khoản: ${stk}`);
            }
        });
    });

    // =========================================================================
    // 10. LIGHTBOX FOR FILMSTRIP ALBUM & POSTAL VIETQR (.tmx-xem)
    // =========================================================================
    const lightbox = document.getElementById('mayhenLightbox');
    const lbImg = document.getElementById('mayhenLbImage');
    const lbCounter = document.getElementById('mayhenLbCounter');
    const lbCaption = document.getElementById('mayhenLbCaption');
    const lbClose = document.getElementById('mayhenLbClose');
    const lbPrev = document.getElementById('mayhenLbPrev');
    const lbNext = document.getElementById('mayhenLbNext');

    let lbList = [];
    let lbIndex = 0;

    const getAlbumUrls = () =>
        Array.from(document.querySelectorAll('.mayhen_invitation_album_item img')).map(
            (img) => img.currentSrc || img.getAttribute('src') || ''
        );

    const openLightbox = (urls, startIndex = 0, isQr = false, captionText = '') => {
        if (!lightbox || !lbImg || !Array.isArray(urls) || urls.length === 0) {
            return;
        }
        lbList = urls.filter(Boolean);
        lbIndex = Math.max(0, Math.min(startIndex, lbList.length - 1));
        lightbox.classList.toggle('chi-mot', lbList.length <= 1);
        lightbox.classList.toggle('la-qr', Boolean(isQr));
        lightbox.classList.add('mo');
        lightbox.setAttribute('aria-hidden', 'false');
        renderLightboxSlide(captionText);
    };

    const renderLightboxSlide = (captionText = '') => {
        if (!lbImg || lbList.length === 0) {
            return;
        }
        lbImg.src = lbList[lbIndex];
        if (lbCounter) {
            lbCounter.textContent = lbList.length > 1 ? `${lbIndex + 1} / ${lbList.length}` : 'BƯU PHẨM SÀI GÒN';
        }
        if (lbCaption) {
            lbCaption.textContent = captionText;
        }
    };

    const closeLightbox = () => {
        if (!lightbox) {
            return;
        }
        lightbox.classList.remove('mo', 'la-qr', 'chi-mot');
        lightbox.setAttribute('aria-hidden', 'true');
    };

    document.querySelectorAll('.mayhen_invitation_album_item').forEach((item, idx) => {
        item.addEventListener('click', () => {
            openLightbox(getAlbumUrls(), idx, false, `Cuộn phim kỷ niệm — Tấm số ${idx + 1}`);
        });
    });

    document.querySelectorAll('[data-lightbox-src]').forEach((trigger) => {
        if (trigger.classList.contains('mayhen_invitation_album_item')) {
            return;
        }
        trigger.addEventListener('click', (ev) => {
            ev.stopPropagation();
            const imgEl = trigger.querySelector('img');
            const src = imgEl?.getAttribute('src') || trigger.getAttribute('data-lightbox-src') || '';
            const isQr = trigger.closest('.sg-the-qr') !== null;
            if (src) {
                openLightbox([src], 0, isQr, isQr ? 'Quét mã VietQR để gửi quà mừng cưới' : '');
            }
        });
    });

    lbClose?.addEventListener('click', closeLightbox);
    lbPrev?.addEventListener('click', () => {
        if (lbList.length > 1) {
            lbIndex = (lbIndex - 1 + lbList.length) % lbList.length;
            renderLightboxSlide();
        }
    });
    lbNext?.addEventListener('click', () => {
        if (lbList.length > 1) {
            lbIndex = (lbIndex + 1) % lbList.length;
            renderLightboxSlide();
        }
    });
    lightbox?.addEventListener('click', (ev) => {
        if (ev.target === lightbox || ev.target.classList.contains('tmx-khung')) {
            closeLightbox();
        }
    });
    document.addEventListener('keydown', (ev) => {
        if (!lightbox || !lightbox.classList.contains('mo')) {
            return;
        }
        if (ev.key === 'Escape') {
            closeLightbox();
        } else if (ev.key === 'ArrowLeft' && lbList.length > 1) {
            lbIndex = (lbIndex - 1 + lbList.length) % lbList.length;
            renderLightboxSlide();
        } else if (ev.key === 'ArrowRight' && lbList.length > 1) {
            lbIndex = (lbIndex + 1) % lbList.length;
            renderLightboxSlide();
        }
    });

    // =========================================================================
    // 11. CARTE POSTALE SIGNATURE PAD (#mayhenSignatureCanvas — Law #8 Compliant)
    // =========================================================================
    const sigCanvas = document.getElementById('mayhenSignatureCanvas');
    const sigPlaceholder = document.getElementById('mayhenSignaturePlaceholder');
    const sigClearBtn = document.getElementById('mayhenSigClearBtn');
    const sigSaveBtn = document.getElementById('mayhenSigSaveBtn');
    const sigNameInput = document.getElementById('mayhenSignatureName');
    const sigWall = document.getElementById('mayhenSignatureWall');

    if (sigCanvas) {
        const ctx = sigCanvas.getContext('2d');
        let isDrawing = false;
        let hasStrokes = false;

        const configurePen = () => {
            if (!ctx) {
                return;
            }
            ctx.strokeStyle = '#1F3D5E';
            ctx.lineWidth = 2.3;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        };
        configurePen();

        const getPoint = (ev) => {
            const rect = sigCanvas.getBoundingClientRect();
            const scaleX = sigCanvas.width / (rect.width || 1);
            const scaleY = sigCanvas.height / (rect.height || 1);
            const clientX = ev.touches ? ev.touches[0].clientX : ev.clientX;
            const clientY = ev.touches ? ev.touches[0].clientY : ev.clientY;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY,
            };
        };

        const startDraw = (ev) => {
            if (!ctx) {
                return;
            }
            isDrawing = true;
            hasStrokes = true;
            if (sigPlaceholder) {
                sigPlaceholder.style.display = 'none';
            }
            configurePen();
            const pt = getPoint(ev);
            ctx.beginPath();
            ctx.moveTo(pt.x, pt.y);
        };

        const moveDraw = (ev) => {
            if (!isDrawing || !ctx) {
                return;
            }
            if (ev.cancelable) {
                ev.preventDefault();
            }
            const pt = getPoint(ev);
            ctx.lineTo(pt.x, pt.y);
            ctx.stroke();
        };

        const stopDraw = () => {
            isDrawing = false;
        };

        sigCanvas.addEventListener('mousedown', startDraw);
        sigCanvas.addEventListener('mousemove', moveDraw);
        window.addEventListener('mouseup', stopDraw);
        sigCanvas.addEventListener('touchstart', startDraw, { passive: true });
        sigCanvas.addEventListener('touchmove', moveDraw, { passive: false });
        sigCanvas.addEventListener('touchend', stopDraw);

        const clearSignature = () => {
            if (!ctx) {
                return;
            }
            ctx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
            hasStrokes = false;
            if (sigPlaceholder) {
                sigPlaceholder.style.display = 'flex';
            }
        };

        sigClearBtn?.addEventListener('click', clearSignature);

        sigSaveBtn?.addEventListener('click', async () => {
            const guestName = (sigNameInput?.value || '').trim() || 'Quan khách Sài Gòn';
            if (!hasStrokes) {
                showToast('Xin Quý khách vui lòng ký tên lên bưu thiếp trước khi đóng dấu!');
                return;
            }

            const dataUrl = sigCanvas.toDataURL('image/png');
            const endpoint = root.dataset.signatureEndpoint || '';

            const prependSignatureCard = (name, imgSrc) => {
                if (!sigWall) {
                    return;
                }
                const card = document.createElement('div');
                card.className = 'mayhen_invitation_sig_card';
                const img = document.createElement('img');
                img.src = imgSrc;
                img.alt = `Chữ ký của ${name}`;
                const caption = document.createElement('div');
                caption.className = 'mayhen_invitation_sig_card_name';
                caption.textContent = name;
                card.appendChild(img);
                card.appendChild(caption);
                sigWall.prepend(card);
            };

            if (endpoint) {
                try {
                    sigSaveBtn.disabled = true;
                    const res = await window.fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            guest_name: guestName,
                            signature_data: dataUrl,
                        }),
                    });
                    const json = await res.json().catch(() => ({}));
                    prependSignatureCard(guestName, json.signature_url || dataUrl);
                    showToast('Đã đóng dấu và lưu bưu thiếp chữ ký thành công!');
                    clearSignature();
                } catch {
                    prependSignatureCard(guestName, dataUrl);
                    showToast('Đã lưu chữ ký trên bưu thiếp kỷ niệm!');
                    clearSignature();
                } finally {
                    sigSaveBtn.disabled = false;
                }
            } else {
                prependSignatureCard(guestName, dataUrl);
                showToast('Đã đóng dấu chữ ký lên Bưu thiếp Sài Gòn!');
                clearSignature();
            }
        });
    }

    // =========================================================================
    // 12. PAR AVION TELEGRAM RSVP & READER MAILBAG WISHES
    // =========================================================================
    const rsvpForm = document.getElementById('mayhenRsvpForm');
    if (rsvpForm) {
        rsvpForm.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            const submitBtn = document.getElementById('mayhenRsvpSubmitBtn');
            const formData = new FormData(rsvpForm);
            const payload = {
                name: String(formData.get('name') || '').trim(),
                attendance: String(formData.get('attendance') || 'attending'),
                guests_count: parseInt(String(formData.get('guests_count') || '1'), 10) || 1,
                side: String(formData.get('side') || 'groom'),
                dietary_notes: String(formData.get('dietary_notes') || '').trim(),
            };

            if (!payload.name) {
                showToast('Xin Quý khách vui lòng nhập họ và tên.');
                return;
            }

            const endpoint = root.dataset.rsvpEndpoint || '';
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            try {
                if (endpoint) {
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
                    showToast(json.message || 'Bưu điện Sài Gòn đã chuyển bức điện tín xác nhận của Quý khách!');
                } else {
                    showToast('Đã gửi bức điện tín xác nhận tham dự thành công!');
                }
                rsvpForm.reset();
            } catch {
                showToast('Đã ghi nhận bức điện tín xác nhận của Quý khách!');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            }
        });
    }

    const wishForm = document.getElementById('mayhenWishForm');
    const wishesList = document.getElementById('mayhenWishesList');
    if (wishForm) {
        wishForm.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            const submitBtn = document.getElementById('mayhenWishSubmitBtn');
            const nameEl = document.getElementById('mayhenWishName');
            const msgEl = document.getElementById('mayhenWishMessage');
            const authorName = (nameEl?.value || '').trim();
            const content = (msgEl?.value || '').trim();

            if (!authorName || !content) {
                showToast('Xin Quý khách vui lòng nhập tên và lời chúc.');
                return;
            }

            const appendWishCard = (author, message) => {
                if (!wishesList) {
                    return;
                }
                const item = document.createElement('div');
                item.className = 'mayhen_invitation_wish_item';

                const header = document.createElement('div');
                header.className = 'mayhen_invitation_wish_header';

                const authorSpan = document.createElement('span');
                authorSpan.className = 'mayhen_invitation_wish_author';
                authorSpan.textContent = author;

                const timeSpan = document.createElement('span');
                timeSpan.className = 'mayhen_invitation_wish_time';
                timeSpan.textContent = 'Vừa gửi tòa soạn';

                header.appendChild(authorSpan);
                header.appendChild(timeSpan);

                const p = document.createElement('p');
                p.className = 'mayhen_invitation_wish_text';
                p.textContent = message;

                item.appendChild(header);
                item.appendChild(p);
                wishesList.prepend(item);
            };

            const endpoint = root.dataset.wishEndpoint || '';
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            try {
                if (endpoint) {
                    const res = await window.fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            name: authorName,
                            author_name: authorName,
                            message: content,
                            content,
                        }),
                    });
                    const json = await res.json().catch(() => ({}));
                    appendWishCard(authorName, content);
                    showToast(json.message || 'Toà soạn đã đăng thư chúc phúc của Quý khách lên số báo kỷ niệm!');
                } else {
                    appendWishCard(authorName, content);
                    showToast('Đã đăng thư chúc phúc lên Nhật Báo Tình Yêu!');
                }
                wishForm.reset();
            } catch {
                appendWishCard(authorName, content);
                showToast('Đã ghi nhận thư chúc phúc của Quý khách!');
                wishForm.reset();
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            }
        });
    }

    // =========================================================================
    // 13. STUDIO V2 LIVE SYNC BRIDGE (MAYHEN_SYNC_INPUT)
    // =========================================================================
    const applyFieldSync = (rawField, value) => {
        if (!rawField) {
            return;
        }
        const cleanKey = String(rawField)
            .replace(/^wedding\.extra_data\./, '')
            .replace(/^wedding\./, '')
            .replace(/^extra_data\./, '');

        const targets = document.querySelectorAll(
            `[data-sync-target="${rawField}"], [data-sync-target="${cleanKey}"]`
        );

        targets.forEach((el) => {
            const tag = el.tagName.toUpperCase();
            if (tag === 'IMG') {
                if (typeof value === 'string' && value.trim() !== '') {
                    el.setAttribute('src', value);
                    const bgSibling = el.parentElement?.querySelector('.ladi-image-background');
                    if (bgSibling) {
                        bgSibling.style.backgroundImage = `url('${value}')`;
                    }
                    const lbHolder = el.closest('[data-lightbox-src]');
                    if (lbHolder) {
                        lbHolder.setAttribute('data-lightbox-src', value);
                    }
                }
            } else if (tag === 'AUDIO') {
                if (typeof value === 'string' && value.trim() !== '') {
                    el.setAttribute('src', value);
                }
            } else if (tag === 'A' && cleanKey === 'google_maps_url') {
                if (typeof value === 'string' && value.trim() !== '') {
                    el.setAttribute('href', value);
                }
            } else if (cleanKey.endsWith('.hex') && el.classList.contains('sg-dct-mau')) {
                if (typeof value === 'string' && value.trim() !== '') {
                    el.style.setProperty('--c', value);
                    el.style.backgroundColor = value;
                }
            } else if (typeof value === 'string' || typeof value === 'number') {
                el.textContent = String(value);
            }
        });

        if (cleanKey === 'wedding_date' && typeof value === 'string' && value.trim() !== '') {
            root.dataset.weddingDate = value.trim();
            renderMayhenWeddingCalendar(value.trim());
            updateMayhenCountdown(value.trim());
        }

        fitSingleLineHeadlines();
    };

    window.addEventListener('message', (event) => {
        const data = event.data;
        if (!data || typeof data !== 'object') {
            return;
        }
        if (data.type === 'MAYHEN_SYNC_INPUT') {
            const field = data.field || data.key || data.payload?.field || data.payload?.key;
            const val = data.value !== undefined ? data.value : data.payload?.value;
            applyFieldSync(field, val);
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTinhYeuQuaTungDongThu, { once: true });
} else {
    initTinhYeuQuaTungDongThu();
}
