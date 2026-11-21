/**
 * Xuân Sang Tình Vừa Chớm — Native ES Module Interactive Controller
 * Pure Vanilla JavaScript, Zero External Libraries, XSS-Safe DOM Rendering.
 */

const VIETNAM_TZ_OFFSET = '+07:00';

const ALBUM_QUOTES = [
    'Mỗi bức ảnh là một câu chuyện nhỏ. Hãy lật từng trang để cùng ôn lại nhé!',
    'Khoảnh khắc ánh mắt trao nhau giữa sắc đỏ son truyền thống.',
    'Xuân sang tình vừa chớm — duyên lành gắn kết hai trái tim.',
    'Một cái nắm tay giản dị cho chặng đường trăm năm phía trước.',
    'Nụ cười rạng rỡ trong tà áo Nhật Bình và Áo Tấc Việt Nam.',
    'Từng khung hình lưu giữ trọn vẹn niềm hạnh phúc ngày chung đôi.',
    'Dưới chữ Song Hỷ đỏ thắm, chúng ta hẹn ước mãi bên nhau.',
    'Hương hoa đào đầu xuân chúc phúc cho tổ ấm mới bình an.',
    'Cảm ơn vì đã cùng nhau viết tiếp chương đẹp nhất của thanh xuân.',
    'Trân trọng từng giây phút bên gia đình và những người thân thương.',
];

function setSafeText(el, text) {
    if (el) {
        el.textContent = text == null ? '' : String(text);
    }
}

function createToast(root) {
    const el = root.querySelector('#mayhenToast');
    let timer = null;
    return {
        show(message) {
            if (!el) return;
            el.textContent = message;
            el.classList.add('is-shown');
            clearTimeout(timer);
            timer = setTimeout(() => el.classList.remove('is-shown'), 2600);
        },
    };
}

function initAudioController(root) {
    const audio = root.querySelector('[data-mayhen-audio]');
    const toggleBtn = root.querySelector('#mayhenMusicToggleBtn');

    const syncState = (playing) => {
        if (!toggleBtn) return;
        toggleBtn.classList.toggle('is-playing', Boolean(playing));
        toggleBtn.setAttribute('aria-pressed', playing ? 'true' : 'false');
    };

    if (!audio) {
        return { play: () => {}, pause: () => {}, isPlaying: () => false };
    }

    audio.addEventListener('play', () => syncState(true));
    audio.addEventListener('pause', () => syncState(false));
    audio.addEventListener('ended', () => syncState(false));

    const play = () => {
        try {
            const p = audio.play();
            if (p && typeof p.catch === 'function') {
                p.catch(() => syncState(false));
            }
        } catch {
            syncState(false);
        }
    };

    const pause = () => {
        try {
            audio.pause();
        } catch {
            /* ignore */
        }
    };

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (audio.paused) {
                play();
            } else {
                pause();
            }
        });
    }

    return { play, pause, isPlaying: () => !audio.paused };
}

function initScrollReveal(root, isEmbed) {
    const els = Array.from(root.querySelectorAll('.mayhen_invitation_reveal'));
    const show = (el) => el.classList.add('mayhen_invitation_visible');

    if (isEmbed || window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
        els.forEach(show);
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    show(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.08, rootMargin: '0px 0px -24px 0px' }
    );

    els.forEach((el) => observer.observe(el));

    // Reveal initial above-the-fold sections immediately
    setTimeout(() => {
        const vh = window.innerHeight || 820;
        els.forEach((el) => {
            const rect = el.getBoundingClientRect();
            if (rect.top < vh && rect.bottom > 0) {
                show(el);
            }
        });
    }, 250);
}

function initOpeningAndPetals(root, audioCtrl, isEmbed) {
    const curtain = root.querySelector('#mayhenOpeningCurtain');
    const openBtn = root.querySelector('#mayhenOpenInvitationBtn');
    const petalsLayer = root.querySelector('#mayhenPetalsLayer');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let petalTimer = null;

    const spawnPetal = (isGold = false) => {
        if (!petalsLayer || reducedMotion || isEmbed) return;
        if (petalsLayer.childElementCount > 14) return;
        const p = document.createElement('i');
        p.className = 'mayhen_invitation_petal' + (isGold ? ' mayhen_invitation_petal--gold' : '');
        p.style.left = `${(Math.random() * 94 + 3).toFixed(1)}%`;
        p.style.setProperty('--p-size', `${(9 + Math.random() * 6).toFixed(1)}px`);
        p.style.setProperty('--p-dur', `${(7.5 + Math.random() * 4.5).toFixed(1)}s`);
        p.style.setProperty('--p-drift', `${(-45 + Math.random() * 90).toFixed(0)}px`);
        petalsLayer.appendChild(p);
        p.addEventListener('animationend', () => p.remove(), { once: true });
    };

    const startPetals = () => {
        if (reducedMotion || isEmbed || petalTimer) return;
        petalTimer = setInterval(() => {
            if (!document.hidden) {
                spawnPetal(Math.random() < 0.32);
            }
        }, 1400);
    };

    if (!curtain || curtain.classList.contains('is-opened') || isEmbed) {
        document.body.classList.remove('mayhen_invitation_scroll_locked');
        if (!isEmbed) startPetals();
        return;
    }

    document.body.classList.add('mayhen_invitation_scroll_locked');

    let opened = false;
    const handleOpen = () => {
        if (opened) return;
        opened = true;

        audioCtrl.play();
        for (let i = 0; i < 10; i += 1) {
            setTimeout(() => spawnPetal(i % 2 === 0), i * 80);
        }

        curtain.classList.add('is-fading');
        document.body.classList.remove('mayhen_invitation_scroll_locked');

        const hero = root.querySelector('#mayhen_section_hero');
        if (hero) hero.classList.add('mayhen_invitation_visible');

        setTimeout(() => {
            curtain.classList.add('is-opened');
            curtain.setAttribute('aria-hidden', 'true');
            startPetals();
        }, 720);
    };

    if (openBtn) {
        openBtn.addEventListener('click', handleOpen);
    }

    window.openInvitation = handleOpen;
    window.openWeddingCeremony = handleOpen;
}

function initCountdownAndCalendar(root) {
    const cdBox = root.querySelector('#mayhenCountdownBox');
    const calBody = root.querySelector('#mayhenCalendarBody');
    const calTitle = root.querySelector('[data-sync-target="calendar_month_year"]');
    const daysEl = root.querySelector('[data-cd-days]');
    const hoursEl = root.querySelector('[data-cd-hours]');
    const minsEl = root.querySelector('[data-cd-mins]');
    const secsEl = root.querySelector('[data-cd-secs]');

    let targetMs = Date.parse(root.getAttribute('data-target-iso') || '2026-05-03T18:00:00+07:00');
    if (Number.isNaN(targetMs)) {
        targetMs = Date.parse('2026-05-03T18:00:00+07:00');
    }

    const pad = (n) => String(Math.max(0, Math.floor(n))).padStart(2, '0');

    const tick = () => {
        const diff = Math.max(0, Math.floor((targetMs - Date.now()) / 1000));
        if (daysEl) daysEl.textContent = pad(diff / 86400);
        if (hoursEl) hoursEl.textContent = pad((diff % 86400) / 3600);
        if (minsEl) minsEl.textContent = pad((diff % 3600) / 60);
        if (secsEl) secsEl.textContent = pad(diff % 60);
    };

    tick();
    setInterval(tick, 1000);

    const createHeartSvg = () => {
        const ns = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(ns, 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('aria-hidden', 'true');
        svg.setAttribute('class', 'mayhen_invitation_cal_heart');
        const path = document.createElementNS(ns, 'path');
        path.setAttribute(
            'd',
            'M12 21.35 10.55 20.03C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54Z'
        );
        path.setAttribute('fill', 'currentColor');
        svg.appendChild(path);
        return svg;
    };

    const renderCalendar = (dateInput) => {
        if (!calBody) return;
        let yr = 2026;
        let mo = 4; // 0-indexed May
        let day = 3;

        if (dateInput instanceof Date && !Number.isNaN(dateInput.getTime())) {
            yr = dateInput.getFullYear();
            mo = dateInput.getMonth();
            day = dateInput.getDate();
        } else if (typeof dateInput === 'string' && /^\d{4}-\d{2}-\d{2}/.test(dateInput)) {
            const parts = dateInput.split('-');
            yr = parseInt(parts[0], 10);
            mo = parseInt(parts[1], 10) - 1;
            day = parseInt(parts[2], 10);
        }

        if (calTitle) {
            calTitle.textContent = `Tháng ${mo + 1} / ${yr}`;
        }

        const totalDays = new Date(yr, mo + 1, 0).getDate();
        const firstOffset = (new Date(yr, mo, 1).getDay() + 6) % 7; // Monday-first

        calBody.textContent = '';

        for (let i = 0; i < firstOffset; i += 1) {
            const empty = document.createElement('span');
            empty.className = 'mayhen_invitation_cal_cell mayhen_invitation_cal_cell--empty';
            empty.setAttribute('aria-hidden', 'true');
            calBody.appendChild(empty);
        }

        for (let d = 1; d <= totalDays; d += 1) {
            const cell = document.createElement('span');
            const isEvent = d === day;
            cell.className = isEvent
                ? 'mayhen_invitation_cal_cell mayhen_invitation_cal_cell--event mayhen_invitation_calendar_selected_day'
                : 'mayhen_invitation_cal_cell';
            cell.setAttribute('aria-label', isEvent ? `Ngày cưới ${d} tháng ${mo + 1}` : `Ngày ${d}`);

            if (isEvent) {
                cell.appendChild(createHeartSvg());
            }

            const num = document.createElement('span');
            num.className = 'mayhen_invitation_cal_num';
            num.textContent = String(d);
            cell.appendChild(num);
            calBody.appendChild(cell);
        }
    };

    renderCalendar(root.getAttribute('data-wedding-date') || '2026-05-03');

    window.renderMayhenWeddingCalendar = (dateInput) => {
        renderCalendar(dateInput);
    };

    window.updateMayhenCountdown = (newDateInput) => {
        if (!newDateInput) return;
        let isoStr = String(newDateInput).trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(isoStr)) {
            const currentIso = root.getAttribute('data-target-iso') || `T18:00:00${VIETNAM_TZ_OFFSET}`;
            const timePart = currentIso.includes('T') ? currentIso.split('T')[1] : `18:00:00${VIETNAM_TZ_OFFSET}`;
            isoStr = `${isoStr}T${timePart}`;
        }
        const parsed = Date.parse(isoStr);
        if (!Number.isNaN(parsed)) {
            targetMs = parsed;
            root.setAttribute('data-target-iso', isoStr);
            if (cdBox) cdBox.setAttribute('data-countdown', isoStr);
            tick();
        }
    };
}

function initLightbox(root) {
    const lb = root.querySelector('#mayhenLightbox');
    const imgEl = root.querySelector('#mayhenLbImg');
    const counterEl = root.querySelector('#mayhenLbCounter');
    const captionEl = root.querySelector('#mayhenLbCaption');
    const closeBtn = root.querySelector('#mayhenLbClose');
    const prevBtn = root.querySelector('#mayhenLbPrev');
    const nextBtn = root.querySelector('#mayhenLbNext');

    if (!lb || !imgEl) {
        return { openList: () => {}, isOpen: () => false };
    }

    let items = [];
    let current = 0;

    const render = () => {
        const item = items[current];
        if (!item) return;
        imgEl.src = item.src;
        setSafeText(captionEl, item.caption || '');
        setSafeText(counterEl, `${current + 1} / ${items.length}`);
        const single = items.length <= 1;
        if (prevBtn) prevBtn.style.display = single ? 'none' : '';
        if (nextBtn) nextBtn.style.display = single ? 'none' : '';
    };

    const close = () => {
        lb.classList.remove('is-open');
        lb.setAttribute('aria-hidden', 'true');
    };

    const step = (delta) => {
        if (items.length <= 1) return;
        current = (current + delta + items.length) % items.length;
        render();
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (prevBtn) prevBtn.addEventListener('click', () => step(-1));
    if (nextBtn) nextBtn.addEventListener('click', () => step(1));

    lb.addEventListener('click', (evt) => {
        if (evt.target === lb || evt.target.classList.contains('mayhen_invitation_lb_stage')) {
            close();
        }
    });

    document.addEventListener('keydown', (evt) => {
        if (!lb.classList.contains('is-open')) return;
        if (evt.key === 'Escape') close();
        if (evt.key === 'ArrowLeft') step(-1);
        if (evt.key === 'ArrowRight') step(1);
    });

    return {
        openList(list, index = 0) {
            items = list;
            current = Math.max(0, Math.min(index, list.length - 1));
            render();
            lb.classList.add('is-open');
            lb.setAttribute('aria-hidden', 'false');
        },
        isOpen: () => lb.classList.contains('is-open'),
    };
}

function initAlbumBook(root, lightbox) {
    const cover = root.querySelector('#mayhenAlbumBookCover');
    const spread = root.querySelector('#mayhenAlbumBookSpread');
    const openBtn = root.querySelector('#mayhenAlbumOpenBtn');
    const closeBtn = root.querySelector('#mayhenAlbumCloseBtn');
    const prevBtn = root.querySelector('#mayhenBookPrevBtn');
    const nextBtn = root.querySelector('#mayhenBookNextBtn');
    const photoBtn = root.querySelector('#mayhenBookPhotoBtn');
    const activeImg = root.querySelector('#mayhenBookActiveImg');
    const captionEl = root.querySelector('#mayhenBookCaption');
    const counterEl = root.querySelector('#mayhenBookCounter');
    const thumbBtns = Array.from(root.querySelectorAll('.mayhen_invitation_album_thumb'));

    if (!thumbBtns.length) return;

    let activeIdx = 0;

    const getGalleryItems = () => {
        const couple = root.getAttribute('data-couple-names') || 'Album Cưới';
        return thumbBtns.map((btn, i) => ({
            src: btn.querySelector('img')?.src || btn.getAttribute('data-lightbox-src') || '',
            caption: `${couple} • Trang ${i + 1} / ${thumbBtns.length}`,
        }));
    };

    const showBookSpread = () => {
        if (cover) cover.classList.add('is-hidden');
        if (spread) spread.classList.add('is-open');
    };

    const showBookCover = () => {
        if (spread) spread.classList.remove('is-open');
        if (cover) cover.classList.remove('is-hidden');
    };

    const renderPage = (idx) => {
        activeIdx = ((idx % thumbBtns.length) + thumbBtns.length) % thumbBtns.length;
        const src = thumbBtns[activeIdx].querySelector('img')?.src || '';
        if (activeImg && src) {
            activeImg.src = src;
        }
        setSafeText(captionEl, ALBUM_QUOTES[activeIdx % ALBUM_QUOTES.length]);
        setSafeText(counterEl, `Trang ${activeIdx + 1} / ${thumbBtns.length}`);
        thumbBtns.forEach((btn, i) => btn.classList.toggle('is-active', i === activeIdx));
    };

    if (openBtn) {
        openBtn.addEventListener('click', () => {
            showBookSpread();
            renderPage(activeIdx);
        });
    }

    if (cover) {
        cover.addEventListener('click', (evt) => {
            if (evt.target === openBtn || openBtn?.contains(evt.target)) return;
            showBookSpread();
            renderPage(activeIdx);
        });
    }

    if (closeBtn) closeBtn.addEventListener('click', showBookCover);
    if (prevBtn) prevBtn.addEventListener('click', () => renderPage(activeIdx - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => renderPage(activeIdx + 1));

    if (photoBtn) {
        photoBtn.addEventListener('click', () => {
            lightbox.openList(getGalleryItems(), activeIdx);
        });
    }

    thumbBtns.forEach((btn, idx) => {
        btn.addEventListener('click', () => {
            const wasActive = idx === activeIdx && spread?.classList.contains('is-open');
            showBookSpread();
            renderPage(idx);
            if (wasActive) {
                lightbox.openList(getGalleryItems(), idx);
            }
        });
    });

    renderPage(0);
}

function initGiftSection(root, lightbox, toast) {
    const giftSection = root.querySelector('#mayhen_section_gift');
    const toggleBtn = root.querySelector('#mayhenGiftToggleBtn');

    if (giftSection && toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isOpen = giftSection.classList.toggle('is-gift-open');
            toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    root.querySelectorAll('.mayhen_invitation_copy_btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const stk = (
                btn.closest('.mayhen_invitation_qr_card')?.querySelector('.mayhen_invitation_qr_stk')?.textContent
                || btn.getAttribute('data-copy-stk')
                || ''
            ).trim();
            if (!stk) return;

            const originalLabel = btn.textContent;
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(stk);
                } else {
                    const tmp = document.createElement('textarea');
                    tmp.value = stk;
                    document.body.appendChild(tmp);
                    tmp.select();
                    document.execCommand('copy');
                    tmp.remove();
                }
                btn.classList.add('is-copied');
                btn.textContent = 'Đã sao chép ✓';
                toast.show(`Đã sao chép STK: ${stk}`);
                setTimeout(() => {
                    btn.classList.remove('is-copied');
                    btn.textContent = originalLabel;
                }, 2000);
            } catch {
                toast.show(`Số tài khoản: ${stk}`);
            }
        });
    });

    root.querySelectorAll('.mayhen_invitation_qr_img_btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const img = btn.querySelector('img');
            const src = img?.src || btn.getAttribute('data-lightbox-src');
            if (!src) return;
            const roleName = btn.closest('.mayhen_invitation_qr_card')?.querySelector('.mayhen_invitation_qr_name')?.textContent || 'Mã QR Mừng Cưới';
            lightbox.openList([{ src, caption: `Mừng cưới • ${roleName}` }], 0);
        });
    });
}

function initSignaturePad(root, toast) {
    const canvas = root.querySelector('#mayhenSignatureCanvas');
    if (!canvas) return;

    const nameInput = root.querySelector('#mayhenSignerName');
    const placeholder = root.querySelector('#mayhenSigPlaceholder');
    const clearBtn = root.querySelector('#mayhenSigClearBtn');
    const downloadBtn = root.querySelector('#mayhenSigDownloadBtn');
    const saveBtn = root.querySelector('#mayhenSigSaveBtn');
    const statusEl = root.querySelector('#mayhenSigStatus');
    const wallEl = root.querySelector('#mayhenSignatureWall');
    const inkBtns = Array.from(root.querySelectorAll('[data-pen-color]'));

    const sigEndpoint = (root.getAttribute('data-signature-endpoint') || '').trim();
    const csrfToken = root.getAttribute('data-csrf-token') || '';
    const ctx = canvas.getContext('2d');
    const strokes = [];
    let currentStroke = null;
    let isDrawing = false;
    let penColor = '#9B2E33';

    const redraw = () => {
        const rect = canvas.getBoundingClientRect();
        if (!rect.width || !rect.height) return;
        ctx.clearRect(0, 0, rect.width, rect.height);
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.lineWidth = 2.6;

        strokes.forEach((stroke) => {
            if (!stroke.points.length) return;
            ctx.strokeStyle = stroke.color;
            ctx.beginPath();
            ctx.moveTo(stroke.points[0].x * rect.width, stroke.points[0].y * rect.height);
            for (let i = 1; i < stroke.points.length; i += 1) {
                ctx.lineTo(stroke.points[i].x * rect.width, stroke.points[i].y * rect.height);
            }
            if (stroke.points.length === 1) {
                ctx.lineTo(stroke.points[0].x * rect.width + 0.5, stroke.points[0].y * rect.height + 0.5);
            }
            ctx.stroke();
        });
    };

    const syncResolution = () => {
        const rect = canvas.getBoundingClientRect();
        if (!rect.width || !rect.height) return;
        const dpr = Math.max(1, window.devicePixelRatio || 1);
        canvas.width = Math.round(rect.width * dpr);
        canvas.height = Math.round(rect.height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        redraw();
    };

    syncResolution();
    window.addEventListener('resize', syncResolution);

    inkBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            penColor = btn.getAttribute('data-pen-color') || '#9B2E33';
            inkBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
        });
    });

    const getNormalizedPoint = (clientX, clientY) => {
        const rect = canvas.getBoundingClientRect();
        return {
            x: Math.max(0, Math.min(1, (clientX - rect.left) / rect.width)),
            y: Math.max(0, Math.min(1, (clientY - rect.top) / rect.height)),
        };
    };

    let activeSource = null;
    const startStroke = (x, y, source) => {
        isDrawing = true;
        activeSource = source;
        if (placeholder) placeholder.hidden = true;
        currentStroke = { color: penColor, points: [getNormalizedPoint(x, y)] };
        strokes.push(currentStroke);
        redraw();
    };
    const moveStroke = (x, y, source) => {
        if (!isDrawing || !currentStroke || activeSource !== source) return;
        currentStroke.points.push(getNormalizedPoint(x, y));
        redraw();
    };
    const finishStroke = () => {
        isDrawing = false;
        currentStroke = null;
        activeSource = null;
    };

    canvas.addEventListener('pointerdown', (evt) => {
        evt.preventDefault();
        try {
            if (canvas.setPointerCapture && evt.pointerId !== undefined) {
                canvas.setPointerCapture(evt.pointerId);
            }
        } catch {
            /* ignore */
        }
        startStroke(evt.clientX, evt.clientY, 'pointer');
    });
    canvas.addEventListener('pointermove', (evt) => {
        if (activeSource !== 'pointer') return;
        evt.preventDefault();
        moveStroke(evt.clientX, evt.clientY, 'pointer');
    });
    canvas.addEventListener('pointerup', finishStroke);
    canvas.addEventListener('pointercancel', finishStroke);

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            strokes.length = 0;
            redraw();
            if (placeholder) placeholder.hidden = false;
            setSafeText(statusEl, '');
        });
    }

    if (downloadBtn) {
        downloadBtn.addEventListener('click', () => {
            if (!strokes.length) {
                setSafeText(statusEl, 'Vui lòng ký tên vào khung trước khi tải ảnh.');
                return;
            }
            const a = document.createElement('a');
            a.href = canvas.toDataURL('image/png');
            a.download = 'chu-ky-luu-but.png';
            a.click();
            toast.show('Đã tải chữ ký PNG về máy!');
        });
    }

    const prependSignatureCard = (guestName, dataUrl) => {
        if (!wallEl) return;
        const empty = wallEl.querySelector('.mayhen_invitation_sig_empty');
        if (empty) empty.remove();

        const card = document.createElement('div');
        card.className = 'mayhen_invitation_sig_card';

        const img = document.createElement('img');
        img.src = dataUrl;
        img.alt = `Chữ ký của ${guestName}`;

        const label = document.createElement('span');
        label.className = 'mayhen_invitation_sig_card_name';
        label.textContent = guestName;

        card.appendChild(img);
        card.appendChild(label);
        wallEl.prepend(card);
    };

    if (saveBtn) {
        saveBtn.addEventListener('click', async () => {
            const guestName = (nameInput?.value || '').trim();
            if (!guestName) {
                setSafeText(statusEl, 'Vui lòng nhập họ tên trước khi lưu chữ ký.');
                nameInput?.focus();
                return;
            }
            if (!strokes.length) {
                setSafeText(statusEl, 'Vui lòng ký tên vào khung trắng trước khi bấm lưu.');
                return;
            }

            const dataUrl = canvas.toDataURL('image/png');
            if (!sigEndpoint) {
                prependSignatureCard(guestName, dataUrl);
                setSafeText(statusEl, 'Bản xem thử: chữ ký đã hiển thị trực tiếp (không lưu cơ sở dữ liệu).');
                toast.show('Đã gắn chữ ký xem thử lên sổ lưu bút!');
                return;
            }

            saveBtn.disabled = true;
            setSafeText(statusEl, 'Đang lưu chữ ký...');

            try {
                const res = await fetch(sigEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ guest_name: guestName, signature_data: dataUrl }),
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok || json.success === false) {
                    throw new Error(json.message || 'Không thể lưu chữ ký lúc này.');
                }
                prependSignatureCard(guestName, json.signature_url || dataUrl);
                strokes.length = 0;
                redraw();
                if (placeholder) placeholder.hidden = false;
                setSafeText(statusEl, json.message || 'Chữ ký của bạn đã được lưu lại!');
                toast.show('Đã lưu chữ ký lưu niệm thành công!');
            } catch (err) {
                setSafeText(statusEl, err.message || 'Lỗi kết nối khi lưu chữ ký.');
            } finally {
                saveBtn.disabled = false;
            }
        });
    }
}

function initRsvpForm(root, toast) {
    const form = root.querySelector('#mayhenRsvpForm');
    if (!form) return;

    const optionLabels = Array.from(form.querySelectorAll('.mayhen_invitation_rsvp_option'));
    const statusEl = form.querySelector('#mayhenRsvpStatus');
    const submitBtn = form.querySelector('#mayhenRsvpSubmitBtn');
    const endpoint = (root.getAttribute('data-rsvp-endpoint') || '').trim();
    const csrfToken = root.getAttribute('data-csrf-token') || '';

    optionLabels.forEach((label) => {
        const radio = label.querySelector('input[type="radio"]');
        if (!radio) return;
        radio.addEventListener('change', () => {
            optionLabels.forEach((l) => l.classList.remove('is-selected'));
            label.classList.add('is-selected');
        });
    });

    form.addEventListener('submit', async (evt) => {
        evt.preventDefault();
        const guestName = (form.querySelector('#mayhenRsvpName')?.value || '').trim();
        if (!guestName) {
            setSafeText(statusEl, 'Vui lòng nhập họ và tên khách mời.');
            form.querySelector('#mayhenRsvpName')?.focus();
            return;
        }

        const attendingStatus = form.querySelector('input[name="attending_status"]:checked')?.value || 'attending';
        const adultsCount = parseInt(form.querySelector('#mayhenRsvpAdults')?.value || '1', 10) || 1;
        const childrenCount = parseInt(form.querySelector('#mayhenRsvpChildren')?.value || '0', 10) || 0;
        const wishes = (form.querySelector('#mayhenRsvpNote')?.value || '').trim();

        if (!endpoint) {
            setSafeText(statusEl, 'Bản xem thử: xác nhận tham dự sẵn sàng lưu thật trên link thiệp mời.');
            toast.show('Bản xem thử mẫu thiệp!');
            return;
        }

        if (submitBtn) submitBtn.disabled = true;
        setSafeText(statusEl, 'Đang gửi xác nhận...');

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    guest_name: guestName,
                    attending_status: attendingStatus,
                    adults_count: attendingStatus === 'declined' ? 0 : adultsCount,
                    children_count: attendingStatus === 'declined' ? 0 : childrenCount,
                    wishes: wishes || null,
                }),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(json.message || 'Không thể gửi xác nhận lúc này.');
            }
            setSafeText(statusEl, json.message || 'Cảm ơn bạn đã xác nhận tham dự!');
            toast.show('Xác nhận tham dự thành công!');
        } catch (err) {
            setSafeText(statusEl, err.message || 'Vui lòng kiểm tra lại thông tin.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

function initWishesForm(root, toast) {
    const form = root.querySelector('#mayhenWishForm');
    if (!form) return;

    const listEl = root.querySelector('#mayhenWishesList');
    const nameInput = form.querySelector('#mayhenWishName');
    const presetSelect = form.querySelector('#mayhenWishPreset');
    const msgInput = form.querySelector('#mayhenWishMessage');
    const countEl = form.querySelector('#mayhenWishCount');
    const statusEl = form.querySelector('#mayhenWishStatus');
    const submitBtn = form.querySelector('#mayhenWishSubmitBtn');
    const endpoint = (root.getAttribute('data-wish-endpoint') || '').trim();
    const csrfToken = root.getAttribute('data-csrf-token') || '';

    const updateCount = () => {
        if (countEl && msgInput) {
            countEl.textContent = String(msgInput.value.length);
        }
    };

    if (msgInput) msgInput.addEventListener('input', updateCount);
    if (presetSelect && msgInput) {
        presetSelect.addEventListener('change', () => {
            if (presetSelect.value) {
                msgInput.value = presetSelect.value;
                updateCount();
            }
        });
    }

    const prependWish = (name, message) => {
        if (!listEl) return;
        const empty = listEl.querySelector('#mayhenWishEmpty');
        if (empty) empty.remove();

        const item = document.createElement('article');
        item.className = 'mayhen_invitation_wish_item';

        const author = document.createElement('h3');
        author.className = 'mayhen_invitation_wish_author';
        author.textContent = name;

        const body = document.createElement('p');
        body.className = 'mayhen_invitation_wish_body';
        body.textContent = message;

        item.appendChild(author);
        item.appendChild(body);
        listEl.prepend(item);
    };

    form.addEventListener('submit', async (evt) => {
        evt.preventDefault();
        const name = (nameInput?.value || '').trim();
        const message = (msgInput?.value || '').trim();

        if (!name || !message) {
            setSafeText(statusEl, 'Vui lòng nhập đầy đủ tên và lời chúc.');
            return;
        }

        if (!endpoint) {
            prependWish(name, message);
            if (msgInput) msgInput.value = '';
            updateCount();
            setSafeText(statusEl, 'Bản xem thử: lời chúc đã hiển thị lên sổ!');
            toast.show('Đã thêm lời chúc xem thử!');
            return;
        }

        if (submitBtn) submitBtn.disabled = true;
        setSafeText(statusEl, 'Đang gửi lời chúc...');

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ name, message }),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(json.message || 'Không thể gửi lời chúc.');
            }
            prependWish(name, message);
            if (msgInput) msgInput.value = '';
            updateCount();
            setSafeText(statusEl, json.message || 'Cảm ơn lời chúc phúc tốt đẹp của bạn!');
            toast.show('Gửi lời chúc thành công!');
        } catch (err) {
            setSafeText(statusEl, err.message || 'Vui lòng kiểm tra lại lời chúc.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

function initStudioLiveSyncHooks(root) {
    const swatchesContainer = root.querySelector('#mayhenDresscodeSwatches');
    const mapIframe = root.querySelector('#mayhenMapIframe');
    const mapDirectLink = root.querySelector('#mayhenMapDirectLink');
    const gcalBtn = root.querySelector('#mayhenAddCalLink');
    const colorNames = ['Đỏ Son', 'Vàng Hoàng Kim', 'Kem Lụa', 'Xanh Cổ Điển', 'Hồng Đào', 'Nâu Trầm'];

    const renderDresscode = (csv) => {
        if (!swatchesContainer) return;
        const list = String(csv || '')
            .split(',')
            .map((c) => c.trim())
            .filter(Boolean);
        if (!list.length) return;

        swatchesContainer.textContent = '';
        list.forEach((rawHex, idx) => {
            const safeHex = /^#[0-9A-Fa-f]{3,8}$/.test(rawHex) ? rawHex : '#9B2E33';
            const item = document.createElement('div');
            item.className = 'mayhen_invitation_swatch_item';

            const circle = document.createElement('span');
            circle.className = 'mayhen_invitation_swatch_circle';
            circle.style.setProperty('--swatch-hex', safeHex);

            const label = document.createElement('span');
            label.className = 'mayhen_invitation_swatch_label';
            label.textContent = colorNames[idx] || safeHex;

            item.appendChild(circle);
            item.appendChild(label);
            swatchesContainer.appendChild(item);
        });
    };

    const refreshMapAndGcal = () => {
        const venue = root.querySelector('[data-sync-target="party_venue"]')?.textContent?.trim() || '';
        const addr = root.querySelector('[data-sync-target="event_party_address"]')?.textContent?.trim() || '';
        const query = encodeURIComponent([venue, addr].filter(Boolean).join(', '));
        if (mapIframe && query) {
            mapIframe.src = `https://maps.google.com/maps?q=${query}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
        }
        if (gcalBtn) {
            const couple = root.querySelector('[data-sync-target="couple_names"]')?.textContent?.trim() || 'Đôi Uyên Ương';
            const dateStr = root.getAttribute('data-wedding-date') || '2026-05-03';
            const compact = dateStr.replace(/-/g, '');
            if (/^\d{8}$/.test(compact)) {
                gcalBtn.href = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent('Lễ Cưới ' + couple)}&dates=${compact}T110000Z/${compact}T140000Z&location=${query}`;
            }
        }
    };

    window.addEventListener('message', (evt) => {
        if (evt.origin && evt.origin !== window.location.origin) return;
        const payload = evt?.data;
        if (!payload || payload.type !== 'MAYHEN_SYNC_INPUT') return;

        const key = String(payload.key || '').replace(/^wedding\./, '').replace(/^extra_data\./, '');
        const val = payload.value;

        if (key === 'dresscode_colors') {
            renderDresscode(val);
        } else if (key.startsWith('gallery.')) {
            const idx = key.split('.')[1];
            const thumbBtn = root.querySelector(`.mayhen_invitation_album_thumb[data-gallery-index="${idx}"]`);
            if (thumbBtn && val) {
                thumbBtn.setAttribute('data-lightbox-src', String(val));
                if (thumbBtn.classList.contains('is-active')) {
                    const activeImg = root.querySelector('#mayhenBookActiveImg');
                    if (activeImg) activeImg.src = String(val);
                }
            }
        } else if (key === 'wedding_date' && typeof val === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
            root.setAttribute('data-wedding-date', val);
            if (typeof window.renderMayhenWeddingCalendar === 'function') {
                window.renderMayhenWeddingCalendar(val);
            }
            if (typeof window.updateMayhenCountdown === 'function') {
                window.updateMayhenCountdown(val);
            }
            refreshMapAndGcal();
        } else if (key === 'event_party_map_link' && typeof val === 'string' && mapDirectLink) {
            if (/^https?:\/\//i.test(val.trim())) {
                mapDirectLink.href = val.trim();
            }
        } else if (key === 'event_party_address' || key === 'party_address' || key === 'party_venue') {
            setTimeout(refreshMapAndGcal, 40);
        } else if (key === 'groom_name' || key === 'bride_name') {
            const parts = String(val || '').trim().split(/\s+/).filter(Boolean);
            const initial = parts.length ? parts[parts.length - 1].charAt(0).toUpperCase() : '';
            const role = key === 'groom_name' ? 'groom' : 'bride';
            const badge = root.querySelector(`[data-monogram-role="${role}"]`);
            if (badge && initial) badge.textContent = initial;
        }
    });
}

function bootstrap() {
    const root = document.getElementById('mayhen_invitation_root');
    if (!root) return;

    const isEmbed = root.getAttribute('data-is-embed') === '1'
        || document.documentElement.classList.contains('mayhen_embed_mode');

    const toast = createToast(root);
    const audioCtrl = initAudioController(root);
    const lightbox = initLightbox(root);

    initOpeningAndPetals(root, audioCtrl, isEmbed);
    initScrollReveal(root, isEmbed);
    initCountdownAndCalendar(root);
    initAlbumBook(root, lightbox);
    initGiftSection(root, lightbox, toast);
    initSignaturePad(root, toast);
    initRsvpForm(root, toast);
    initWishesForm(root, toast);
    initStudioLiveSyncHooks(root);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrap);
} else {
    bootstrap();
}
