/**
 * Bản Sonata Màu Hạnh Phúc (Wedding18-v2) — Native ES Module Interactive Controller
 * Strictly Vanilla JS, zero external libraries, XSS-safe DOM rendering.
 */

const VIETNAM_TZ_OFFSET = '+07:00';

function escapeSafeText(el, text) {
    if (el) {
        el.textContent = text == null ? '' : String(text);
    }
}

function createToastController(root) {
    const el = root.querySelector('#mayhenToast');
    let timer = null;

    return {
        show(msg) {
            if (!el) return;
            el.textContent = msg;
            el.classList.add('is-shown');
            clearTimeout(timer);
            timer = setTimeout(() => {
                el.classList.remove('is-shown');
            }, 2600);
        },
    };
}

function initResponsiveScalingUnits(root) {
    const wrap = root.querySelector('#mayhenInvitationWrap');
    if (!wrap) return;

    const updateUnits = () => {
        const w = wrap.clientWidth;
        if (!w) return;
        const isDesktop = window.matchMedia('(min-width: 900px)').matches;
        root.style.setProperty('--u', `${(Math.min(w, 480) / 420).toFixed(4)}px`);
        if (isDesktop) {
            root.style.setProperty('--d', `${(Math.min(w - 40, 960) / 960).toFixed(4)}px`);
        } else {
            root.style.removeProperty('--d');
        }
    };

    updateUnits();
    if ('ResizeObserver' in window) {
        new ResizeObserver(updateUnits).observe(wrap);
    }
    window.addEventListener('resize', updateUnits);
}

function initSingleLineAutoFit(root) {
    let measureCtx = null;
    const fitAll = () => {
        if (!measureCtx) {
            const canvas = document.createElement('canvas');
            measureCtx = canvas.getContext('2d');
        }
        if (!measureCtx) return;

        root.querySelectorAll('.mayhen_invitation_mot_dong').forEach((box) => {
            const h = box.querySelector('.mayhen_invitation_headline, .mayhen_invitation_paragraph');
            if (!h) return;
            h.style.fontSize = '';
            box.classList.remove('mayhen_invitation_hai_dong');
            box.style.removeProperty('--lech');

            const cs = getComputedStyle(h);
            const baseSize = parseFloat(cs.fontSize) || 36;
            const availWidth = box.clientWidth - 4;
            if (availWidth <= 0) return;

            let text = (h.textContent || '').trim();
            if (!text) return;
            if (cs.textTransform === 'uppercase') {
                text = text.toUpperCase();
            }

            measureCtx.font = `${cs.fontStyle} ${cs.fontWeight} ${cs.fontSize} ${cs.fontFamily}`;
            const m = measureCtx.measureText(text);
            const ls = parseFloat(cs.letterSpacing) || 0;
            const inkWidth = (m.actualBoundingBoxLeft || 0) + (m.actualBoundingBoxRight || m.width) + ls * text.length;

            if (inkWidth > availWidth && inkWidth > 0) {
                const ratio = availWidth / inkWidth;
                if (ratio < 0.55) {
                    box.classList.add('mayhen_invitation_hai_dong');
                    h.style.fontSize = `${(baseSize * 0.66).toFixed(1)}px`;
                    return;
                }
                h.style.fontSize = `${(baseSize * ratio).toFixed(1)}px`;
            }
        });
    };

    fitAll();
    window.addEventListener('resize', () => {
        clearTimeout(fitAll._t);
        fitAll._t = setTimeout(fitAll, 150);
    });
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(fitAll).catch(() => {});
    }
    return fitAll;
}

function initAudioController(root) {
    const audio = root.querySelector('[data-mayhen-audio]');
    const btn = root.querySelector('#mayhenMusicFab');

    const syncUI = (playing) => {
        if (!btn) return;
        btn.classList.toggle('is-playing', Boolean(playing));
        btn.setAttribute('aria-pressed', playing ? 'true' : 'false');
    };

    if (!audio) {
        return {
            play: () => {},
            pause: () => {},
            isPlaying: () => false,
        };
    }

    audio.addEventListener('play', () => syncUI(true));
    audio.addEventListener('pause', () => syncUI(false));
    audio.addEventListener('ended', () => syncUI(false));

    const play = () => {
        try {
            const p = audio.play();
            if (p && typeof p.catch === 'function') {
                p.catch(() => syncUI(false));
            }
        } catch {
            syncUI(false);
        }
    };

    const pause = () => {
        try {
            audio.pause();
        } catch {
            /* ignore */
        }
    };

    if (btn) {
        btn.addEventListener('click', () => {
            if (audio.paused) {
                play();
            } else {
                pause();
            }
        });
    }

    return {
        play,
        pause,
        isPlaying: () => !audio.paused,
    };
}

function initRevealAndBotanicalBloom(root, isEmbed) {
    const revealEls = Array.from(root.querySelectorAll('.mayhen_invitation_hien'));
    const flowers = Array.from(root.querySelectorAll('#mayhenInvitationWrap .mayhen_invitation_nh'));
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (isEmbed || reduced || !('IntersectionObserver' in window)) {
        revealEls.forEach((el) => el.classList.add('da-hien'));
        flowers.forEach((f) => f.classList.add('da-no'));
        return () => {};
    }

    let started = false;
    const startObservers = () => {
        if (started) return;
        started = true;

        // 1. Assign directional bloom type to botanical layers outside first hero card
        flowers.forEach((h) => {
            if (h.closest('.mayhen_invitation_dau')) return;
            const phan = h.closest('.mayhen_invitation_phan');
            if (!phan) return;
            const r = h.getBoundingClientRect();
            const q = phan.getBoundingClientRect();
            if (!r.width || !q.width) return;
            const cx = (r.left + r.width / 2 - q.left) / q.width;
            const cy = (r.top + r.height / 2 - q.top) / q.height;
            const mode = cx < 0.25 ? 'xoe-t' : cx > 0.75 ? 'xoe-p' : cy < 0.3 ? 'roi' : cy > 0.7 ? 'moc' : 'no';
            h.setAttribute('data-no', mode);
            h.classList.add('mayhen_invitation_no');
        });

        const ioReveal = new IntersectionObserver(
            (entries) => {
                let k = 0;
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.style.setProperty('--tre', `${Math.min(k * 0.16, 1.0).toFixed(2)}s`);
                    k += 1;
                    entry.target.classList.add('da-hien');
                    ioReveal.unobserve(entry.target);
                });
            },
            { threshold: 0, rootMargin: '0px 0px -10% 0px' }
        );

        revealEls.forEach((el) => {
            if (!el.classList.contains('da-hien')) {
                ioReveal.observe(el);
            }
        });

        const ioBloom = new IntersectionObserver(
            (entries) => {
                let k = 0;
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.style.setProperty('--no-tre', `${Math.min(0.1 + k * 0.16, 1.1).toFixed(2)}s`);
                    k += 1;
                    entry.target.classList.add('da-no');
                    ioBloom.unobserve(entry.target);
                });
            },
            { threshold: 0, rootMargin: '0px 0px -6% 0px' }
        );

        flowers.forEach((h) => {
            if (h.classList.contains('mayhen_invitation_no')) {
                ioBloom.observe(h);
            }
        });
    };

    return startObservers;
}

function initOpeningAndOliveLeaves(root, audioCtrl, startRevealObservers, fitSingleLine, isEmbed) {
    const curtain = root.querySelector('#mayhenOpeningCurtain');
    const openBtn = root.querySelector('#mayhenOpenInvitationBtn');
    const petalsLayer = root.querySelector('#mayhenPetalsLayer');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let idleTimer = null;

    const revealHeroImmediately = () => {
        root.querySelectorAll('.mayhen_invitation_dau .mayhen_invitation_hien').forEach((el, idx) => {
            el.style.setProperty('--tre', `${(0.15 + idx * 0.22).toFixed(2)}s`);
            el.classList.add('da-hien');
        });
    };

    const spawnLeaf = () => {
        if (!petalsLayer || reduced || isEmbed) return;
        if (petalsLayer.childElementCount >= 14) return;
        const leaf = document.createElement('i');
        if (Math.random() < 0.25) leaf.className = 'nv';
        const x = Math.random() * window.innerWidth;
        const s = 10 + Math.random() * 8;
        const d = 9 + Math.random() * 5;
        const x1 = -30 + Math.random() * 90;
        const x2 = -60 + Math.random() * 90;
        leaf.style.left = `${x.toFixed(0)}px`;
        leaf.style.setProperty('--s', `${s.toFixed(0)}px`);
        leaf.style.setProperty('--d', `${d.toFixed(2)}s`);
        leaf.style.setProperty('--x1', `${x1.toFixed(0)}px`);
        leaf.style.setProperty('--x2', `${x2.toFixed(0)}px`);
        petalsLayer.appendChild(leaf);
        setTimeout(() => leaf.remove(), d * 1000 + 150);
    };

    const burstLeaves = () => {
        if (!petalsLayer || reduced || isEmbed) return;
        const w = window.innerWidth;
        const h = window.innerHeight;
        for (let k = 0; k < 26; k += 1) {
            const angle = Math.random() * Math.PI * 2;
            const r = 120 + Math.random() * Math.max(w, h) * 0.52;
            const leaf = document.createElement('i');
            leaf.className = `bung${k % 4 === 0 ? ' nv' : ''}`;
            leaf.style.left = `${(w / 2 - 7).toFixed(0)}px`;
            leaf.style.top = `${(h * 0.48 - 9).toFixed(0)}px`;
            leaf.style.setProperty('--s', `${(9 + Math.random() * 11).toFixed(0)}px`);
            leaf.style.setProperty('--d', `${(1.8 + Math.random() * 1.4).toFixed(2)}s`);
            leaf.style.setProperty('--x1', `${(Math.cos(angle) * r).toFixed(0)}px`);
            leaf.style.setProperty('--x2', `${(Math.sin(angle) * r).toFixed(0)}px`);
            petalsLayer.appendChild(leaf);
            setTimeout(() => leaf.remove(), 3400);
        }
    };

    const startFallingLeaves = () => {
        if (reduced || isEmbed || idleTimer) return;
        const loop = () => {
            idleTimer = setTimeout(() => {
                if (!document.hidden) spawnLeaf();
                loop();
            }, 1100 + Math.random() * 800);
        };
        loop();
    };

    if (!curtain || curtain.classList.contains('da-mo') || isEmbed) {
        document.body.classList.remove('mayhen_invitation_scroll_locked');
        revealHeroImmediately();
        startRevealObservers();
        if (!isEmbed) startFallingLeaves();
        return;
    }

    document.body.classList.add('mayhen_invitation_scroll_locked');

    let isOpening = false;
    const handleOpen = () => {
        if (isOpening || curtain.classList.contains('mo')) return;
        isOpening = true;
        curtain.classList.add('mo');
        audioCtrl.play();

        if (reduced) {
            document.body.classList.remove('mayhen_invitation_scroll_locked');
            revealHeroImmediately();
            curtain.classList.add('tan', 'da-mo');
            startRevealObservers();
            return;
        }

        setTimeout(() => {
            curtain.classList.add('tan');
            document.body.classList.remove('mayhen_invitation_scroll_locked');
            window.scrollTo(0, 0);
            revealHeroImmediately();
            burstLeaves();
            startFallingLeaves();
            setTimeout(fitSingleLine, 900);
        }, 900);

        setTimeout(() => {
            startRevealObservers();
        }, 2000);

        setTimeout(() => {
            curtain.classList.add('da-mo');
            curtain.setAttribute('aria-hidden', 'true');
        }, 2350);
    };

    if (openBtn) {
        openBtn.addEventListener('click', handleOpen);
    }

    window.openInvitation = handleOpen;
    window.openWeddingCeremony = handleOpen;
}

function initSmoothScrollButtons(root) {
    root.querySelectorAll('[data-scroll-to]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const sel = btn.getAttribute('data-scroll-to');
            const target = sel ? root.querySelector(sel) : null;
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

function initCountdownAndCalendar(root) {
    const cdBox = root.querySelector('#mayhenCountdownBox');
    const calBody = root.querySelector('#mayhenCalendarBody');
    const daysEl = root.querySelector('[data-cd-days]');
    const hoursEl = root.querySelector('[data-cd-hours]');
    const minsEl = root.querySelector('[data-cd-mins]');
    const secsEl = root.querySelector('[data-cd-secs]');
    const calMonthHeading = root.querySelector('[data-cal-month-heading]');
    const calYearHeading = root.querySelector('[data-cal-year-heading]');
    const stdDay = root.querySelector('[data-std-day]');
    const stdMonth = root.querySelector('[data-std-month]');
    const stdYear = root.querySelector('[data-std-year]');
    const stdWeekday = root.querySelector('[data-std-weekday]');

    let targetMs = Date.parse(root.getAttribute('data-target-iso') || '2027-01-16T16:30:00+07:00');
    if (Number.isNaN(targetMs)) {
        targetMs = Date.parse('2027-01-16T16:30:00+07:00');
    }

    const pad = (n) => String(Math.max(0, Math.floor(n))).padStart(2, '0');

    const tickCountdown = () => {
        const diff = Math.max(0, Math.floor((targetMs - Date.now()) / 1000));
        if (daysEl) daysEl.textContent = pad(diff / 86400);
        if (hoursEl) hoursEl.textContent = pad((diff % 86400) / 3600);
        if (minsEl) minsEl.textContent = pad((diff % 3600) / 60);
        if (secsEl) secsEl.textContent = pad(diff % 60);
    };

    tickCountdown();
    setInterval(tickCountdown, 1000);

    const monthTextNames = [
        'Tháng Một', 'Tháng Hai', 'Tháng Ba', 'Tháng Tư', 'Tháng Năm', 'Tháng Sáu',
        'Tháng Bảy', 'Tháng Tám', 'Tháng Chín', 'Tháng Mười', 'Tháng Mười Một', 'Tháng Mười Hai',
    ];
    const weekdayNames = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];

    const renderCalendar = (dateInput) => {
        if (!calBody) return;
        let yr = 2027;
        let mo = 0; // Jan (0-indexed)
        let day = 16;

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

        const chosenDate = new Date(yr, mo, day);
        if (calMonthHeading) calMonthHeading.textContent = `Tháng ${pad(mo + 1)}`;
        if (calYearHeading) calYearHeading.textContent = String(yr);
        if (stdDay) stdDay.textContent = pad(day);
        if (stdMonth) stdMonth.textContent = monthTextNames[mo] || `Tháng ${pad(mo + 1)}`;
        if (stdYear) stdYear.textContent = String(yr);
        if (stdWeekday) stdWeekday.textContent = weekdayNames[chosenDate.getDay()] || 'Thứ Bảy';

        const totalDays = new Date(yr, mo + 1, 0).getDate();
        const prevMonthTotal = new Date(yr, mo, 0).getDate();
        // Monday-first grid: Mon=0..Sun=6
        const firstOffset = (new Date(yr, mo, 1).getDay() + 6) % 7;

        calBody.textContent = '';

        for (let i = firstOffset - 1; i >= 0; i -= 1) {
            const cell = document.createElement('div');
            cell.className = 'mayhen_invitation_calendar_day mayhen_invitation_calendar_day--empty mayhen_invitation_empty';
            cell.textContent = String(prevMonthTotal - i);
            calBody.appendChild(cell);
        }

        for (let d = 1; d <= totalDays; d += 1) {
            const cell = document.createElement('div');
            const isSelected = d === day;
            cell.className = isSelected
                ? 'mayhen_invitation_calendar_day mayhen_invitation_calendar_selected_day mayhen_invitation_selected'
                : 'mayhen_invitation_calendar_day';
            cell.textContent = String(d);
            calBody.appendChild(cell);
        }

        const rendered = firstOffset + totalDays;
        const remainder = (7 - (rendered % 7)) % 7;
        for (let n = 1; n <= remainder; n += 1) {
            const cell = document.createElement('div');
            cell.className = 'mayhen_invitation_calendar_day mayhen_invitation_calendar_day--empty mayhen_invitation_empty';
            cell.textContent = String(n);
            calBody.appendChild(cell);
        }
    };

    const initialDateStr = root.getAttribute('data-wedding-date') || '2027-01-16';
    renderCalendar(initialDateStr);

    window.renderMayhenWeddingCalendar = (dateInput) => {
        renderCalendar(dateInput);
    };

    window.updateMayhenCountdown = (newDateInput) => {
        if (!newDateInput) return;
        let isoStr = String(newDateInput).trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(isoStr)) {
            const currentIso = root.getAttribute('data-target-iso') || 'T16:30:00+07:00';
            const timePart = currentIso.includes('T') ? currentIso.split('T')[1] : `16:30:00${VIETNAM_TZ_OFFSET}`;
            isoStr = `${isoStr}T${timePart}`;
        }
        const parsed = Date.parse(isoStr);
        if (!Number.isNaN(parsed)) {
            targetMs = parsed;
            if (cdBox) cdBox.setAttribute('data-countdown', isoStr);
            root.setAttribute('data-target-iso', isoStr);
            tickCountdown();
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
    let currentIndex = 0;

    const render = () => {
        const item = items[currentIndex];
        if (!item) return;
        imgEl.src = item.src;
        if (captionEl) captionEl.textContent = item.caption || '';
        if (counterEl) {
            counterEl.textContent = items.length > 1 ? `${currentIndex + 1} / ${items.length}` : '1 / 1';
        }
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
        currentIndex = (currentIndex + delta + items.length) % items.length;
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
            currentIndex = Math.max(0, Math.min(index, list.length - 1));
            render();
            lb.classList.add('is-open');
            lb.setAttribute('aria-hidden', 'false');
        },
        isOpen: () => lb.classList.contains('is-open'),
    };
}

function initCoverflowGallery(root, lightbox, isEmbed) {
    const stage = root.querySelector('#mayhenCoverflowStage');
    const items = Array.from(root.querySelectorAll('.mayhen_invitation_cf_item'));
    const dots = Array.from(root.querySelectorAll('.mayhen_invitation_cf_dot'));
    const prevBtn = root.querySelector('#mayhenCfPrev');
    const nextBtn = root.querySelector('#mayhenCfNext');

    if (!stage || items.length === 0) return;

    let active = 0;
    const total = items.length;
    let autoTimer = null;

    const getCircularDelta = (idx) => {
        let d = idx - active;
        if (d > total / 2) d -= total;
        if (d < -total / 2) d += total;
        return d;
    };

    const layout = () => {
        const isDesktop = stage.clientWidth >= 600;
        const spreadX = isDesktop ? 176 : 122;

        items.forEach((el, idx) => {
            const d = getCircularDelta(idx);
            const absD = Math.abs(d);

            if (absD > 2) {
                el.style.opacity = '0';
                el.style.pointerEvents = 'none';
                el.style.zIndex = '1';
                el.style.transform = `translate3d(${d * spreadX}px, 0, 0) scale(0.45)`;
                return;
            }

            const tx = d * spreadX;
            const scale = absD === 0 ? 1 : absD === 1 ? 0.82 : 0.64;
            const rotY = absD === 0 ? 0 : (d > 0 ? -1 : 1) * (absD === 1 ? 22 : 34);
            const opacity = absD === 0 ? 1 : absD === 1 ? 0.92 : 0.56;
            const zIndex = 25 - absD * 5;

            el.style.opacity = String(opacity);
            el.style.pointerEvents = 'auto';
            el.style.zIndex = String(zIndex);
            el.style.transform = `translate3d(${tx}px, 0, 0) scale(${scale}) rotateY(${rotY}deg)`;
        });

        dots.forEach((dot, idx) => {
            dot.classList.toggle('is-active', idx === active);
        });
    };

    const goTo = (index) => {
        active = ((index % total) + total) % total;
        layout();
    };

    if (prevBtn) prevBtn.addEventListener('click', () => goTo(active - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goTo(active + 1));

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            const idx = parseInt(dot.getAttribute('data-cf-dot') || '0', 10);
            goTo(idx);
        });
    });

    items.forEach((item, idx) => {
        item.addEventListener('click', () => {
            if (idx !== active) {
                goTo(idx);
                return;
            }
            const couple = root.getAttribute('data-couple-names') || 'Album Cưới';
            const galleryList = items.map((el, i) => ({
                src: el.querySelector('img')?.src || el.getAttribute('data-lightbox-src') || '',
                caption: `${couple} • Ảnh ${i + 1} / ${total}`,
            }));
            lightbox.openList(galleryList, idx);
        });
    });

    let startX = null;
    stage.addEventListener('pointerdown', (evt) => {
        if (evt.target.closest('button')) return;
        startX = evt.clientX;
    });
    stage.addEventListener('pointerup', (evt) => {
        if (startX === null) return;
        const dx = evt.clientX - startX;
        startX = null;
        if (Math.abs(dx) > 36) {
            goTo(active + (dx < 0 ? 1 : -1));
        }
    });

    const startAuto = () => {
        if (isEmbed || autoTimer) return;
        autoTimer = setInterval(() => {
            if (!document.hidden && !lightbox.isOpen()) {
                goTo(active + 1);
            }
        }, 4200);
    };
    const stopAuto = () => {
        clearInterval(autoTimer);
        autoTimer = null;
    };

    stage.addEventListener('mouseenter', stopAuto);
    stage.addEventListener('mouseleave', startAuto);

    layout();
    window.addEventListener('resize', layout);
    startAuto();
}

function initGiftAndQr(root, lightbox, toast) {
    root.querySelectorAll('.mayhen_invitation_qr_copy_btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const stk = (
                btn.getAttribute('data-copy-stk')
                || btn.closest('.mayhen_invitation_qr_card')?.querySelector('.mayhen_invitation_qr_stk')?.textContent
                || ''
            ).trim();
            if (!stk) return;
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
                toast.show(`Đã sao chép STK: ${stk}`);
            } catch {
                toast.show(`Số tài khoản: ${stk}`);
            }
        });
    });

    root.querySelectorAll('.mayhen_invitation_qr_img_box').forEach((box) => {
        box.addEventListener('click', () => {
            const img = box.querySelector('img');
            const src = img?.src || box.getAttribute('data-lightbox-src');
            if (!src) return;
            const roleText = box.closest('.mayhen_invitation_qr_card')?.querySelector('.mayhen_invitation_qr_role')?.textContent || 'Mã QR Mừng Cưới';
            lightbox.openList([{ src, caption: `Mừng cưới • ${roleText.trim()}` }], 0);
        });
    });
}

function initSignaturePad(root, toast) {
    const canvas = root.querySelector('#mayhenSignatureCanvas');
    if (!canvas) return;

    const nameInput = root.querySelector('#mayhenSignerName');
    const placeholder = root.querySelector('#mayhenSigPlaceholder');
    const clearBtn = root.querySelector('#mayhenSigClearBtn');
    const saveBtn = root.querySelector('#mayhenSigSaveBtn');
    const statusEl = root.querySelector('#mayhenSigStatus');
    const wallEl = root.querySelector('#mayhenSignatureWall');
    const colorBtns = Array.from(root.querySelectorAll('[data-pen-color]'));

    const sigEndpoint = (root.getAttribute('data-signature-endpoint') || '').trim();
    const csrfToken = root.getAttribute('data-csrf-token') || '';
    const ctx = canvas.getContext('2d');
    const strokes = [];
    let currentStroke = null;
    let isDrawing = false;
    let penColor = '#5E5E45';

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

    colorBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            penColor = btn.getAttribute('data-pen-color') || '#5E5E45';
            colorBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
        });
    });

    const getPoint = (clientX, clientY) => {
        const rect = canvas.getBoundingClientRect();
        return {
            x: Math.max(0, Math.min(1, (clientX - rect.left) / rect.width)),
            y: Math.max(0, Math.min(1, (clientY - rect.top) / rect.height)),
        };
    };

    let activeInput = null;
    const beginStroke = (clientX, clientY, kind) => {
        isDrawing = true;
        activeInput = kind;
        if (placeholder) placeholder.hidden = true;
        currentStroke = { color: penColor, points: [getPoint(clientX, clientY)] };
        strokes.push(currentStroke);
        redraw();
    };
    const extendStroke = (clientX, clientY, kind) => {
        if (!isDrawing || !currentStroke || (activeInput && activeInput !== kind)) return;
        currentStroke.points.push(getPoint(clientX, clientY));
        redraw();
    };
    const endDraw = () => {
        isDrawing = false;
        currentStroke = null;
        activeInput = null;
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
        beginStroke(evt.clientX, evt.clientY, 'pointer');
    });

    canvas.addEventListener('pointermove', (evt) => {
        if (!isDrawing || activeInput !== 'pointer') return;
        evt.preventDefault();
        extendStroke(evt.clientX, evt.clientY, 'pointer');
    });

    canvas.addEventListener('pointerup', endDraw);
    canvas.addEventListener('pointercancel', endDraw);

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            strokes.length = 0;
            redraw();
            if (placeholder) placeholder.hidden = false;
            escapeSafeText(statusEl, '');
        });
    }

    const prependSignatureCard = (guestName, dataUrl) => {
        if (!wallEl) return;
        const emptyMsg = wallEl.querySelector('.mayhen_invitation_sig_empty');
        if (emptyMsg) emptyMsg.remove();

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
                escapeSafeText(statusEl, 'Vui lòng nhập tên của bạn trước khi lưu chữ ký.');
                nameInput?.focus();
                return;
            }
            if (!strokes.length) {
                escapeSafeText(statusEl, 'Vui lòng ký tên vào khung trắng trước khi bấm lưu.');
                return;
            }

            const dataUrl = canvas.toDataURL('image/png');
            if (!sigEndpoint) {
                prependSignatureCard(guestName, dataUrl);
                escapeSafeText(statusEl, 'Bản xem thử: chữ ký đã được gắn lên bảng lưu bút!');
                toast.show('Đã thêm chữ ký xem thử lên sổ lưu bút!');
                return;
            }

            saveBtn.disabled = true;
            escapeSafeText(statusEl, 'Đang lưu chữ ký...');

            try {
                const res = await fetch(sigEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        guest_name: guestName,
                        signature_data: dataUrl,
                    }),
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok || json.success === false) {
                    throw new Error(json.message || 'Không thể lưu chữ ký lúc này.');
                }
                prependSignatureCard(guestName, json.signature_url || dataUrl);
                strokes.length = 0;
                redraw();
                if (placeholder) placeholder.hidden = false;
                escapeSafeText(statusEl, json.message || 'Chữ ký của bạn đã được lưu lại!');
                toast.show('Đã lưu chữ ký lưu niệm thành công!');
            } catch (err) {
                escapeSafeText(statusEl, err.message || 'Lỗi kết nối khi lưu chữ ký.');
            } finally {
                saveBtn.disabled = false;
            }
        });
    }
}

function initRsvpForm(root, toast) {
    const form = root.querySelector('#mayhenRsvpForm');
    if (!form) return;

    const pillLabels = Array.from(form.querySelectorAll('.mayhen_invitation_rsvp_pill'));
    const statusEl = form.querySelector('#mayhenRsvpStatus');
    const submitBtn = form.querySelector('#mayhenRsvpSubmitBtn');
    const endpoint = (root.getAttribute('data-rsvp-endpoint') || '').trim();
    const csrfToken = root.getAttribute('data-csrf-token') || '';

    pillLabels.forEach((label) => {
        const radio = label.querySelector('input[type="radio"]');
        if (!radio) return;
        radio.addEventListener('change', () => {
            pillLabels.forEach((l) => l.classList.remove('is-active'));
            label.classList.add('is-active');
        });
    });

    form.addEventListener('submit', async (evt) => {
        evt.preventDefault();
        const guestName = (form.querySelector('#mayhenRsvpName')?.value || '').trim();
        if (!guestName) {
            escapeSafeText(statusEl, 'Vui lòng nhập họ tên khách mời.');
            form.querySelector('#mayhenRsvpName')?.focus();
            return;
        }

        const attendingStatus = form.querySelector('input[name="attending_status"]:checked')?.value || 'attending';
        const adultsCount = parseInt(form.querySelector('#mayhenRsvpAdults')?.value || '1', 10) || 1;
        const childrenCount = parseInt(form.querySelector('#mayhenRsvpChildren')?.value || '0', 10) || 0;
        const wishes = (form.querySelector('#mayhenRsvpNote')?.value || '').trim();

        if (!endpoint) {
            escapeSafeText(statusEl, 'Bản xem thử: Xác nhận tham dự hoạt động bình thường trên thiệp thật!');
            toast.show('Đã ghi nhận xác nhận xem thử!');
            return;
        }

        if (submitBtn) submitBtn.disabled = true;
        escapeSafeText(statusEl, 'Đang gửi xác nhận...');

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
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
            escapeSafeText(statusEl, json.message || 'Cảm ơn bạn đã xác nhận tham dự!');
            toast.show('Xác nhận tham dự thành công!');
        } catch (err) {
            escapeSafeText(statusEl, err.message || 'Vui lòng kiểm tra lại thông tin.');
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
    const statusEl = form.querySelector('#mayhenWishStatus');
    const submitBtn = form.querySelector('#mayhenWishSubmitBtn');
    const endpoint = (root.getAttribute('data-wish-endpoint') || '').trim();
    const csrfToken = root.getAttribute('data-csrf-token') || '';

    if (presetSelect && msgInput) {
        presetSelect.addEventListener('change', () => {
            if (presetSelect.value) {
                msgInput.value = presetSelect.value;
            }
        });
    }

    const prependWishItem = (name, message) => {
        if (!listEl) return;
        const item = document.createElement('div');
        item.className = 'mayhen_invitation_wish_item';

        const nameEl = document.createElement('div');
        nameEl.className = 'mayhen_invitation_wish_name';
        nameEl.textContent = name;

        const msgEl = document.createElement('div');
        msgEl.className = 'mayhen_invitation_wish_msg';
        msgEl.textContent = message;

        item.appendChild(nameEl);
        item.appendChild(msgEl);
        listEl.prepend(item);
    };

    form.addEventListener('submit', async (evt) => {
        evt.preventDefault();
        const name = (nameInput?.value || '').trim();
        const message = (msgInput?.value || '').trim();

        if (!name || !message) {
            escapeSafeText(statusEl, 'Vui lòng nhập đầy đủ tên và lời chúc.');
            return;
        }

        if (!endpoint) {
            prependWishItem(name, message);
            if (msgInput) msgInput.value = '';
            escapeSafeText(statusEl, 'Bản xem thử: lời chúc đã hiển thị lên sổ!');
            toast.show('Đã gửi lời chúc xem thử!');
            return;
        }

        if (submitBtn) submitBtn.disabled = true;
        escapeSafeText(statusEl, 'Đang gửi lời chúc...');

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ name, message }),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(json.message || 'Không thể gửi lời chúc.');
            }
            prependWishItem(name, message);
            if (msgInput) msgInput.value = '';
            escapeSafeText(statusEl, json.message || 'Cảm ơn lời chúc tốt đẹp của bạn!');
            toast.show('Gửi lời chúc thành công!');
        } catch (err) {
            escapeSafeText(statusEl, err.message || 'Vui lòng kiểm tra lại lời chúc.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

function initStudioLiveSyncHooks(root, fitSingleLine) {
    const swatchesContainer = root.querySelector('#mayhenDresscodeSwatches');
    const mapIframe = root.querySelector('#mayhenMapIframe');
    const gcalBtn = root.querySelector('#mayhenAddCalLink');
    const groomInitEl = root.querySelector('[data-groom-initial]');
    const brideInitEl = root.querySelector('[data-bride-initial]');
    const colorNames = ['Xanh Ô Liu', 'Trắng Kem', 'Be Cát', 'Nâu Nude', 'Xanh Rêu', 'Kem Nhạt'];

    const createFlowerSvg = () => {
        const ns = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(ns, 'svg');
        svg.setAttribute('class', 'mayhen_invitation_dcm_hoa');
        svg.setAttribute('viewBox', '0 0 34 22');
        svg.setAttribute('aria-hidden', 'true');

        const p1 = document.createElementNS(ns, 'path');
        p1.setAttribute('d', 'M17 16c-3-5-9-6-13-3M17 16c3-5 9-6 13-3M17 16V8');
        const p2 = document.createElementNS(ns, 'path');
        p2.setAttribute('d', 'M8 12c-1-2 0-4 2-4M26 12c1-2 0-4-2-4');
        const c1 = document.createElementNS(ns, 'circle');
        c1.setAttribute('cx', '17');
        c1.setAttribute('cy', '6');
        c1.setAttribute('r', '3');
        const c2 = document.createElementNS(ns, 'circle');
        c2.setAttribute('cx', '17');
        c2.setAttribute('cy', '17');
        c2.setAttribute('r', '1.6');

        svg.append(p1, p2, c1, c2);
        return svg;
    };

    const renderDresscode = (csv) => {
        if (!swatchesContainer) return;
        const list = String(csv || '')
            .split(',')
            .map((c) => c.trim())
            .filter(Boolean);
        if (!list.length) return;

        swatchesContainer.textContent = '';
        list.forEach((rawHex, idx) => {
            const safeHex = /^#[0-9A-Fa-f]{3,8}$/.test(rawHex) ? rawHex : '#858569';
            const item = document.createElement('div');
            item.className = 'mayhen_invitation_dcm_o';
            item.style.animationDelay = `${(idx * 0.15).toFixed(2)}s`;

            const vom = document.createElement('div');
            vom.className = 'mayhen_invitation_dcm_vom';
            vom.style.setProperty('--tre', `-${(idx * 1.4).toFixed(1)}s`);
            vom.appendChild(createFlowerSvg());

            const kinh = document.createElement('i');
            kinh.className = 'mayhen_invitation_dcm_kinh';
            kinh.style.setProperty('--c', safeHex);
            vom.appendChild(kinh);

            const label = document.createElement('span');
            label.className = 'mayhen_invitation_dcm_ten';
            label.textContent = colorNames[idx] || safeHex;

            item.append(vom, label);
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
            const dateStr = root.getAttribute('data-wedding-date') || '2027-01-16';
            const compactDate = dateStr.replace(/-/g, '');
            if (/^\d{8}$/.test(compactDate)) {
                gcalBtn.href = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent('Đám cưới ' + couple)}&dates=${compactDate}T093000Z/${compactDate}T130000Z&location=${query}`;
            }
        }
    };

    window.addEventListener('message', (evt) => {
        const payload = evt?.data;
        if (!payload || payload.type !== 'MAYHEN_SYNC_INPUT') return;
        const key = String(payload.key || '').replace(/^wedding\./, '').replace(/^extra_data\./, '');
        const val = payload.value;

        if (key === 'dresscode_colors') {
            renderDresscode(val);
        } else if (key === 'groom_name' && typeof val === 'string' && groomInitEl) {
            const words = val.trim().split(/\s+/);
            const last = words[words.length - 1] || val.trim();
            if (last) groomInitEl.textContent = last.charAt(0).toUpperCase();
            setTimeout(fitSingleLine, 30);
        } else if (key === 'bride_name' && typeof val === 'string' && brideInitEl) {
            const words = val.trim().split(/\s+/);
            const last = words[words.length - 1] || val.trim();
            if (last) brideInitEl.textContent = last.charAt(0).toUpperCase();
            setTimeout(fitSingleLine, 30);
        } else if (key.startsWith('gallery.')) {
            const idx = key.split('.')[1];
            const item = root.querySelector(`.mayhen_invitation_cf_item[data-cf-index="${idx}"]`);
            if (item && val) {
                item.setAttribute('data-lightbox-src', String(val));
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
        } else if (key === 'event_party_address' || key === 'party_address' || key === 'party_venue') {
            setTimeout(refreshMapAndGcal, 40);
        }
    });
}

function bootstrap() {
    const root = document.getElementById('mayhen_invitation_root');
    if (!root) return;

    const isEmbed = root.getAttribute('data-is-embed') === '1'
        || document.documentElement.classList.contains('mayhen_embed_mode');

    initResponsiveScalingUnits(root);
    const fitSingleLine = initSingleLineAutoFit(root);
    const toast = createToastController(root);
    const audioCtrl = initAudioController(root);
    const lightbox = initLightbox(root);
    const startRevealObservers = initRevealAndBotanicalBloom(root, isEmbed);

    initOpeningAndOliveLeaves(root, audioCtrl, startRevealObservers, fitSingleLine, isEmbed);
    initSmoothScrollButtons(root);
    initCountdownAndCalendar(root);
    initCoverflowGallery(root, lightbox, isEmbed);
    initGiftAndQr(root, lightbox, toast);
    initSignaturePad(root, toast);
    initRsvpForm(root, toast);
    initWishesForm(root, toast);
    initStudioLiveSyncHooks(root, fitSingleLine);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrap);
} else {
    bootstrap();
}
