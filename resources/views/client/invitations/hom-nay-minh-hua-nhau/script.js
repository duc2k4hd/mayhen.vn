/**
 * Hôm Nay Mình Hứa Nhau — Native ES Module Controller
 * Pure Vanilla JS, Zero External Libraries, XSS-Safe DOM Operations.
 */

const VN_OFFSET = '+07:00';

function setText(el, val) {
    if (el) {
        el.textContent = val == null ? '' : String(val);
    }
}

function createToast(root) {
    const el = root.querySelector('#mayhenToast');
    let timer = null;
    return {
        show(msg) {
            if (!el) return;
            el.textContent = msg;
            el.classList.add('is-shown');
            clearTimeout(timer);
            timer = setTimeout(() => el.classList.remove('is-shown'), 2600);
        },
    };
}

function initAudio(root) {
    const audio = root.querySelector('[data-mayhen-audio]');
    const btn = root.querySelector('#mayhenMusicToggleBtn');

    const syncUI = (playing) => {
        if (!btn) return;
        btn.classList.toggle('is-playing', Boolean(playing));
        btn.setAttribute('aria-pressed', playing ? 'true' : 'false');
    };

    if (!audio) {
        return { play: () => {}, pause: () => {}, isPlaying: () => false };
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
            if (audio.paused) play();
            else pause();
        });
    }

    return { play, pause, isPlaying: () => !audio.paused };
}

function initScrollReveal(root, isEmbed) {
    const nodes = Array.from(root.querySelectorAll('.mayhen_invitation_reveal'));
    const show = (el) => el.classList.add('mayhen_invitation_visible');

    if (isEmbed || window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
        nodes.forEach(show);
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

    nodes.forEach((el) => observer.observe(el));

    setTimeout(() => {
        const vh = window.innerHeight || 820;
        nodes.forEach((el) => {
            const r = el.getBoundingClientRect();
            if (r.top < vh && r.bottom > 0) show(el);
        });
    }, 240);
}

function initOpening(root, audioCtrl, isEmbed) {
    const curtain = root.querySelector('#mayhenOpeningCurtain');
    const openBtn = root.querySelector('#mayhenOpenInvitationBtn');

    if (!curtain || curtain.classList.contains('is-opened') || isEmbed) {
        document.body.classList.remove('mayhen_invitation_scroll_locked');
        return;
    }

    document.body.classList.add('mayhen_invitation_scroll_locked');

    let opened = false;
    const handleOpen = () => {
        if (opened) return;
        opened = true;

        audioCtrl.play();
        curtain.classList.add('is-fading');
        document.body.classList.remove('mayhen_invitation_scroll_locked');

        const hero = root.querySelector('#mayhen_section_hero');
        if (hero) hero.classList.add('mayhen_invitation_visible');

        setTimeout(() => {
            curtain.classList.add('is-opened');
            curtain.setAttribute('aria-hidden', 'true');
        }, 620);
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
    const monthRightEl = root.querySelector('#mayhenCalMonthScript');
    const daysEl = root.querySelector('[data-cd-days]');
    const hoursEl = root.querySelector('[data-cd-hours]');
    const minsEl = root.querySelector('[data-cd-mins]');
    const secsEl = root.querySelector('[data-cd-secs]');

    let targetMs = Date.parse(root.getAttribute('data-target-iso') || '2026-12-15T10:30:00+07:00');
    if (Number.isNaN(targetMs)) {
        targetMs = Date.parse('2026-12-15T10:30:00+07:00');
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

    const renderCalendar = (dateInput) => {
        if (!calBody) return;
        let yr = 2026;
        let mo = 11; // 0-indexed Dec
        let day = 15;

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
            calTitle.textContent = `Tháng ${mo + 1} · Năm ${yr}`;
        }
        if (monthRightEl) {
            monthRightEl.textContent = `Tháng ${mo + 1}`;
        }

        const totalDays = new Date(yr, mo + 1, 0).getDate();
        const firstOffset = (new Date(yr, mo, 1).getDay() + 6) % 7; // Monday-first

        calBody.textContent = '';

        for (let i = 0; i < firstOffset; i += 1) {
            const blank = document.createElement('span');
            blank.className = 'mayhen_invitation_cal_cell mayhen_invitation_cal_cell--empty';
            blank.setAttribute('aria-hidden', 'true');
            calBody.appendChild(blank);
        }

        for (let d = 1; d <= totalDays; d += 1) {
            const cell = document.createElement('span');
            const isEvent = d === day;
            cell.className = isEvent
                ? 'mayhen_invitation_cal_cell mayhen_invitation_cal_cell--event mayhen_invitation_calendar_selected_day'
                : 'mayhen_invitation_cal_cell';
            cell.setAttribute('aria-label', isEvent ? `Ngày cưới ${d} tháng ${mo + 1}` : `Ngày ${d}`);

            const num = document.createElement('span');
            num.className = 'mayhen_invitation_cal_num';
            num.textContent = String(d);
            cell.appendChild(num);
            calBody.appendChild(cell);
        }
    };

    renderCalendar(root.getAttribute('data-wedding-date') || '2026-12-15');

    window.renderMayhenWeddingCalendar = (dateInput) => renderCalendar(dateInput);

    window.updateMayhenCountdown = (newDateInput) => {
        if (!newDateInput) return;
        let iso = String(newDateInput).trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(iso)) {
            const currentIso = root.getAttribute('data-target-iso') || `T10:30:00${VN_OFFSET}`;
            const timePart = currentIso.includes('T') ? currentIso.split('T')[1] : `10:30:00${VN_OFFSET}`;
            iso = `${iso}T${timePart}`;
        }
        const parsed = Date.parse(iso);
        if (!Number.isNaN(parsed)) {
            targetMs = parsed;
            root.setAttribute('data-target-iso', iso);
            if (cdBox) cdBox.setAttribute('data-countdown', iso);
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
        setText(captionEl, item.caption || '');
        setText(counterEl, `${current + 1} / ${items.length}`);
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

function initAlbumGrid(root, lightbox) {
    const cards = Array.from(root.querySelectorAll('.mayhen_invitation_album_card'));
    if (!cards.length) return;

    const buildList = () => {
        const couple = root.getAttribute('data-couple-names') || 'Album Cưới';
        return cards.map((card, i) => ({
            src: card.querySelector('img')?.src || card.getAttribute('data-lightbox-src') || '',
            caption: `${couple} • Ảnh ${i + 1} / ${cards.length}`,
        }));
    };

    cards.forEach((card, idx) => {
        card.addEventListener('click', () => {
            lightbox.openList(buildList(), idx);
        });
    });
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

            const orig = btn.textContent;
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
                    btn.textContent = orig;
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
    let penColor = '#1F1D1A';

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

    const syncRes = () => {
        const rect = canvas.getBoundingClientRect();
        if (!rect.width || !rect.height) return;
        const dpr = Math.max(1, window.devicePixelRatio || 1);
        canvas.width = Math.round(rect.width * dpr);
        canvas.height = Math.round(rect.height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        redraw();
    };

    syncRes();
    window.addEventListener('resize', syncRes);

    inkBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            penColor = btn.getAttribute('data-pen-color') || '#1F1D1A';
            inkBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
        });
    });

    const getPt = (x, y) => {
        const rect = canvas.getBoundingClientRect();
        return {
            x: Math.max(0, Math.min(1, (x - rect.left) / rect.width)),
            y: Math.max(0, Math.min(1, (y - rect.top) / rect.height)),
        };
    };

    let activeSrc = null;
    const startStroke = (x, y, src) => {
        isDrawing = true;
        activeSrc = src;
        if (placeholder) placeholder.hidden = true;
        currentStroke = { color: penColor, points: [getPt(x, y)] };
        strokes.push(currentStroke);
        redraw();
    };
    const moveStroke = (x, y, src) => {
        if (!isDrawing || !currentStroke || activeSrc !== src) return;
        currentStroke.points.push(getPt(x, y));
        redraw();
    };
    const endStroke = () => {
        isDrawing = false;
        currentStroke = null;
        activeSrc = null;
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
        if (activeSrc !== 'pointer') return;
        evt.preventDefault();
        moveStroke(evt.clientX, evt.clientY, 'pointer');
    });
    canvas.addEventListener('pointerup', endStroke);
    canvas.addEventListener('pointercancel', endStroke);

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            strokes.length = 0;
            redraw();
            if (placeholder) placeholder.hidden = false;
            setText(statusEl, '');
        });
    }

    if (downloadBtn) {
        downloadBtn.addEventListener('click', () => {
            if (!strokes.length) {
                setText(statusEl, 'Vui lòng ký tên vào khung trước khi tải ảnh.');
                return;
            }
            const a = document.createElement('a');
            a.href = canvas.toDataURL('image/png');
            a.download = 'chu-ky-luu-but.png';
            a.click();
            toast.show('Đã tải chữ ký PNG!');
        });
    }

    const prependCard = (guestName, dataUrl) => {
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
                setText(statusEl, 'Vui lòng nhập họ tên trước khi lưu chữ ký.');
                nameInput?.focus();
                return;
            }
            if (!strokes.length) {
                setText(statusEl, 'Vui lòng ký tên vào khung trước khi lưu.');
                return;
            }

            const dataUrl = canvas.toDataURL('image/png');
            if (!sigEndpoint) {
                prependCard(guestName, dataUrl);
                setText(statusEl, 'Bản xem thử: chữ ký đã hiển thị trực tiếp (không lưu cơ sở dữ liệu).');
                toast.show('Đã gắn chữ ký xem thử!');
                return;
            }

            saveBtn.disabled = true;
            setText(statusEl, 'Đang lưu chữ ký...');

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
                prependCard(guestName, json.signature_url || dataUrl);
                strokes.length = 0;
                redraw();
                if (placeholder) placeholder.hidden = false;
                setText(statusEl, json.message || 'Chữ ký của bạn đã được lưu lại!');
                toast.show('Lưu chữ ký thành công!');
            } catch (err) {
                setText(statusEl, err.message || 'Lỗi kết nối khi lưu chữ ký.');
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
            setText(statusEl, 'Vui lòng nhập họ và tên khách mời.');
            form.querySelector('#mayhenRsvpName')?.focus();
            return;
        }

        const attendingStatus = form.querySelector('input[name="attending_status"]:checked')?.value || 'attending';
        const adultsCount = parseInt(form.querySelector('#mayhenRsvpAdults')?.value || '1', 10) || 1;
        const childrenCount = parseInt(form.querySelector('#mayhenRsvpChildren')?.value || '0', 10) || 0;
        const wishes = (form.querySelector('#mayhenRsvpNote')?.value || '').trim();

        if (!endpoint) {
            setText(statusEl, 'Bản xem thử: xác nhận tham dự sẵn sàng hoạt động trên thiệp thật.');
            toast.show('Bản xem thử mẫu thiệp!');
            return;
        }

        if (submitBtn) submitBtn.disabled = true;
        setText(statusEl, 'Đang gửi xác nhận...');

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
            setText(statusEl, json.message || 'Cảm ơn bạn đã xác nhận tham dự!');
            toast.show('Xác nhận tham dự thành công!');
        } catch (err) {
            setText(statusEl, err.message || 'Vui lòng kiểm tra lại thông tin.');
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
        if (countEl && msgInput) countEl.textContent = String(msgInput.value.length);
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
            setText(statusEl, 'Vui lòng nhập đầy đủ tên và lời chúc.');
            return;
        }

        if (!endpoint) {
            prependWish(name, message);
            if (msgInput) msgInput.value = '';
            updateCount();
            setText(statusEl, 'Bản xem thử: lời chúc đã hiển thị lên sổ!');
            toast.show('Đã thêm lời chúc xem thử!');
            return;
        }

        if (submitBtn) submitBtn.disabled = true;
        setText(statusEl, 'Đang gửi lời chúc...');

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
            setText(statusEl, json.message || 'Cảm ơn lời chúc tốt đẹp của bạn!');
            toast.show('Gửi lời chúc thành công!');
        } catch (err) {
            setText(statusEl, err.message || 'Vui lòng kiểm tra lại lời chúc.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

function initStudioSyncHooks(root) {
    const swatchesContainer = root.querySelector('#mayhenDresscodeSwatches');
    const mapIframe = root.querySelector('#mayhenMapIframe');
    const mapDirectLink = root.querySelector('#mayhenMapDirectLink');
    const gcalBtn = root.querySelector('#mayhenAddCalLink');
    const colorNames = ['Trắng Tinh Khôi', 'Hồng Phấn', 'Đỏ Nhung', 'Đen Cổ Điển', 'Kem Nhạt', 'Xanh Ô Liu'];

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
            const dateStr = root.getAttribute('data-wedding-date') || '2026-12-15';
            const compact = dateStr.replace(/-/g, '');
            if (/^\d{8}$/.test(compact)) {
                gcalBtn.href = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent('Lễ Cưới ' + couple)}&dates=${compact}T033000Z/${compact}T063000Z&location=${query}`;
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
            const card = root.querySelector(`.mayhen_invitation_album_card[data-gallery-index="${idx}"]`);
            if (card && val) {
                card.setAttribute('data-lightbox-src', String(val));
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
        }
    });
}

function bootstrap() {
    const root = document.getElementById('mayhen_invitation_root');
    if (!root) return;

    const isEmbed = root.getAttribute('data-is-embed') === '1'
        || document.documentElement.classList.contains('mayhen_embed_mode');

    const toast = createToast(root);
    const audioCtrl = initAudio(root);
    const lightbox = initLightbox(root);

    initOpening(root, audioCtrl, isEmbed);
    initScrollReveal(root, isEmbed);
    initCountdownAndCalendar(root);
    initAlbumGrid(root, lightbox);
    initGiftSection(root, lightbox, toast);
    initSignaturePad(root, toast);
    initRsvpForm(root, toast);
    initWishesForm(root, toast);
    initStudioSyncHooks(root);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrap);
} else {
    bootstrap();
}
