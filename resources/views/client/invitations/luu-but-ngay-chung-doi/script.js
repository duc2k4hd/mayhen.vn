/**
 * MAYHEN — Mẫu thiệp cưới "Lưu Bút Ngày Chung Đôi"
 * Native Vanilla ES JavaScript (Không phụ thuộc thư viện bên ngoài)
 */
(() => {
    'use strict';

    const P = 'mayhen_invitation_ladjihe38udhd_';
    const DATA = window.MAYHEN_W18 || {};
    const $ = (q, base = document) => base.querySelector(q);
    const $$ = (q, base = document) => Array.from(base.querySelectorAll(q));

    const storeKey = DATA.storageKey || 'mayhen_lbcd_demo';
    const csrfToken = window.__MAYHEN_CSRF_TOKEN || $('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const loadStorage = (type) => {
        try {
            return JSON.parse(localStorage.getItem(storeKey + '_' + type) || '[]');
        } catch {
            return [];
        }
    };

    const saveStorage = (type, list) => {
        try {
            localStorage.setItem(storeKey + '_' + type, JSON.stringify(list));
        } catch {}
    };

    const stage = $('#mayhen_w18_stage');
    const root = $('.' + P + 'ladi-wraper', stage);
    const panel = $('#mayhen-w18-modal-body');
    const overlay = $('#mayhen-w18-overlay');

    let photos = [];
    let photoIndex = 0;
    let lastFocus = null;
    let toastTimeout;
    let signatureDrawing = false;
    let signed = false;

    // Sanitize chống XSS 100%
    const escapeHTML = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[c]));

    // Toast notification
    const showToast = (message) => {
        const el = $('#mayhen-w18-toast');
        if (!el) return;
        el.textContent = message;
        el.classList.add(P + 'show');
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => el.classList.remove(P + 'show'), 3200);
    };

    // Modal Manager
    function openModal(markup) {
        if (!panel || !overlay) return;
        lastFocus = document.activeElement;
        panel.innerHTML = `<button type="button" class="${P}dialog-close" aria-label="Đóng">×</button>${markup}`;
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        $('.' + P + 'dialog-close', panel)?.focus();
    }

    function closeModal() {
        if (!overlay || !panel) return;
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        panel.innerHTML = '';
        lastFocus?.focus?.();
    }

    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay || e.target.closest('.' + P + 'dialog-close')) {
                closeModal();
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
        if (overlay && overlay.getAttribute('aria-hidden') === 'false' && photos.length) {
            if (e.key === 'ArrowRight') showPhoto(photoIndex + 1);
            if (e.key === 'ArrowLeft') showPhoto(photoIndex - 1);
        }
    });

    function onActivate(selector, handler) {
        $$(selector).forEach(node => {
            node.setAttribute('role', 'button');
            node.setAttribute('tabindex', '0');
            node.addEventListener('click', (e) => {
                e.preventDefault();
                handler(e, node);
            });
            node.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    handler(e, node);
                }
            });
        });
    }

    function navigate(id) {
        closeModal();
        const el = document.getElementById(id);
        if (!el) return;
        const top = el.getBoundingClientRect().top + window.scrollY;
        window.scrollTo({
            top: Math.max(0, top - 12),
            behavior: matchMedia('(prefers-reduced-motion:reduce)').matches ? 'auto' : 'smooth'
        });
    }

    // Co giãn Mobile Responsive
    function responsive() {
        if (!root || !stage) return;
        const mobile = innerWidth < 768;
        if (mobile) {
            const ratio = Math.min(1, innerWidth / 420);
            root.style.transform = `scale(${ratio})`;
            stage.style.height = (root.scrollHeight * ratio) + 'px';
            stage.style.width = '100%';
        } else {
            root.style.transform = '';
            stage.style.height = '';
            stage.style.width = '100%';
        }
    }

    let frame;
    function delayedResize() {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(responsive);
    }
    addEventListener('resize', delayedResize);
    addEventListener('orientationchange', delayedResize);

    // Hiệu ứng cuộn IntersectionObserver
    const hidden = $$('.' + P + 'ladi-element').filter(n => n.id && /^(IMAGE|BOX|GROUP|HEADLINE|PARAGRAPH|FORM|BUTTON|HTML_CODE|LINE)/.test(n.id));
    const animationIds = /^(IMAGE1|IMAGE88|IMAGE80|IMAGE76|IMAGE85|IMAGE82|IMAGE87|HEADLINE|PARAGRAPH|IMAGE[0-9]+|BOX[0-9]+|FORM[0-9]+|BUTTON[0-9]+|LINE[0-9]+)$/;
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add(P + 'ladi-animation');
                observer.unobserve(entry.target);
            }
        }), { rootMargin: '0px 0px -30px 0px', threshold: .025 });
        hidden.forEach(el => {
            if (animationIds.test(el.id)) observer.observe(el);
        });
    } else {
        hidden.forEach(el => el.classList.add(P + 'ladi-animation'));
    }

    // Trình phát nhạc nền
    const music = $('#mayhen-w18-audio');
    const speakerOff = $('.' + P + 'off_speaker');
    const speakerOn = $('.' + P + 'on_speaker');

    if (music && DATA.music) {
        music.src = DATA.music;
    }

    function toggleAudio() {
        if (!music || !music.src) {
            showToast('Chưa có nhạc nền');
            return;
        }
        if (music.paused) {
            music.play().then(() => {
                showToast('Đang phát nhạc nền');
                if (speakerOff) speakerOff.style.display = 'none';
                if (speakerOn) speakerOn.style.display = 'block';
            }).catch(() => {
                showToast('Chạm vào màn hình để bắt đầu phát nhạc');
            });
        } else {
            music.pause();
            showToast('Đã tạm dừng nhạc');
            if (speakerOff) speakerOff.style.display = 'block';
            if (speakerOn) speakerOn.style.display = 'none';
        }
    }

    $('#mayhen-w18-music')?.addEventListener('click', toggleAudio);
    onActivate('#GROUP166', toggleAudio);

    // Mục lục menu
    $('#mayhen-w18-menu')?.addEventListener('click', () => {
        const sections = [
            ['Trang đầu', 'SECTION1'],
            ['Save the Date', 'SECTION22'],
            ['Lịch cưới', 'SECTION24'],
            ['Đếm ngược', 'SECTION6'],
            ['Cặp đôi', 'SECTION4'],
            ['Lễ cưới', 'SECTION16'],
            ['Thiệp mời', 'SECTION13'],
            ['Ký tên', 'SECTION7'],
            ['Album kỷ niệm', 'SECTION20'],
            ['RSVP', 'SECTION8'],
            ['Lời chúc', 'SECTION9'],
            ['Mừng cưới', 'SECTION27'],
            ['Bản đồ chỉ đường', 'SECTION17'],
            ['Cảm ơn', 'SECTION26']
        ];
        openModal(`<h2>Mục Lục Thiệp Cưới</h2><div class="${P}dialog-navigation">${sections.map(([name, id]) => `<button type="button" data-section="${id}">${escapeHTML(name)}</button>`).join('')}</div>`);
    });

    panel?.addEventListener('click', e => {
        const x = e.target.closest('[data-section]');
        if (x) navigate(x.dataset.section);
    });

    // Chia sẻ liên kết
    $('#mayhen-w18-share')?.addEventListener('click', async () => {
        try {
            if (navigator.share) {
                await navigator.share({ title: document.title, url: location.href });
            } else if (navigator.clipboard) {
                await navigator.clipboard.writeText(location.href);
                showToast('Đã sao chép liên kết thiệp');
            } else {
                showToast('Bạn có thể sao chép đường dẫn trên trình duyệt');
            }
        } catch {}
    });

    onActivate('#BUTTON18', () => closeModal());
    onActivate('#BUTTON21', () => showGift());
    onActivate('#BUTTON19', () => showGift('bride'));
    onActivate('#BUTTON20', () => showGift('groom'));
    onActivate('.' + P + 'btn_open_menu', () => $('#mayhen-w18-menu')?.click());
    onActivate('.' + P + 'button_keoXuong', () => navigate('SECTION22'));

    // Bản đồ
    onActivate('#BUTTON9', () => {
        const address = DATA.brideEventAddress || 'Lào Cai, Việt Nam';
        window.open('https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(address), '_blank', 'noopener');
    });

    // Thêm vào lịch ICS
    function saveCalendar() {
        const dt = new Date(DATA.weddingDate || '2027-11-01T11:00:00+07:00');
        const end = new Date(dt.getTime() + 3 * 3600000);
        const fmt = d => d.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}Z$/, 'Z');
        const escapeIcs = s => String(s).replace(/[,;\\]/g, '\\$&').replace(/\n/g, '\\n');
        const ics = `BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//MAYHEN//Wedding//VI\r\nBEGIN:VEVENT\r\nUID:${dt.getTime()}-wedding@mayhen.vn\r\nDTSTAMP:${fmt(new Date())}\r\nDTSTART:${fmt(dt)}\r\nDTEND:${fmt(end)}\r\nSUMMARY:${escapeIcs('Lễ cưới ' + (DATA.groom || '') + ' & ' + (DATA.bride || ''))}\r\nLOCATION:${escapeIcs(DATA.brideEventAddress || '')}\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n`;
        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([ics], { type: 'text/calendar;charset=utf-8' }));
        a.download = 'lich-cuoi-' + (DATA.groom || 'chu-re') + '-' + (DATA.bride || 'co-dau') + '.ics';
        a.click();
        setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    }
    onActivate('#BUTTON10', saveCalendar);

    // Đếm ngược ngày cưới
    const countdownTarget = new Date(DATA.weddingDate || '2027-11-01T11:00:00+07:00').getTime();
    function updateCountdown() {
        const sec = Math.max(0, Math.floor((countdownTarget - Date.now()) / 1000));
        const vals = [Math.floor(sec / 86400), Math.floor(sec % 86400 / 3600), Math.floor(sec % 3600 / 60), sec % 60];
        const nodes = $$('#COUNTDOWN1 .' + P + 'ladi-countdown-text span');
        if (nodes.length >= 4) {
            nodes.slice(0, 4).forEach((node, i) => node.textContent = String(vals[i]).padStart(2, '0'));
        } else {
            $$('#COUNTDOWN1 .' + P + 'ladi-countdown-text').slice(0, 4).forEach((node, i) => node.textContent = String(vals[i]).padStart(2, '0'));
        }
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);

    // Hộp mừng cưới Modal
    function showGift(which) {
        const g = DATA.gift || {};
        const cards = (which ? [which] : ['groom', 'bride']).map(k => {
            const item = g[k] || {};
            const title = k === 'groom' ? 'Chú rể (' + (DATA.groom || 'Chú rể') + ')' : 'Cô dâu (' + (DATA.bride || 'Cô dâu') + ')';
            return `<div style="padding:16px 8px;border-top:1px solid #e5e5e5;text-align:center">
                <h3 style="font-size:20px;margin-bottom:8px;font-weight:600">${escapeHTML(title)}</h3>
                <p style="margin:4px 0"><strong>${escapeHTML(item.bank || 'Ngân hàng')}</strong></p>
                <p style="margin:4px 0">Chủ tài khoản: <strong>${escapeHTML(item.name || item.owner || '...')}</strong></p>
                <p style="margin:4px 0">Số tài khoản: <strong style="font-size:17px;color:#111">${escapeHTML(item.number || item.account || '...')}</strong></p>
                ${item.qr ? `<div style="margin:12px auto;max-width:200px"><img src="${escapeHTML(item.qr)}" alt="Mã QR" style="width:100%;border-radius:8px"></div>` : ''}
                <button type="button" class="${P}dialog-action" data-copy="${escapeHTML(item.number || item.account || '')}" style="margin-top:8px">Sao chép số tài khoản</button>
            </div>`;
        }).join('');

        openModal(`<h2>Hộp Mừng Cưới</h2><p style="text-align:center;color:#666">Cảm ơn tình cảm và sự chúc phúc của quý khách!</p>${cards}`);
    }

    panel?.addEventListener('click', e => {
        const button = e.target.closest('[data-copy]');
        if (!button) return;
        const text = button.dataset.copy;
        if (!text) return;
        navigator.clipboard?.writeText(text).then(() => {
            showToast('Đã sao chép số tài khoản!');
        }).catch(() => {
            showToast('Không thể tự động sao chép');
        });
    });

    // Lightbox Album cưới
    photos = $$('.' + P + 'photoBookImage').map(el => {
        const bg = el.querySelector('.' + P + 'ladi-image-background') || el;
        const match = getComputedStyle(bg).backgroundImage.match(/url\(["']?([^"')]+)["']?\)/);
        return match?.[1] || '';
    }).filter(Boolean);

    function showPhoto(index) {
        if (!photos.length) return;
        photoIndex = (index + photos.length) % photos.length;
        const src = photos[photoIndex];
        openModal(`<h2>Album Kỷ Niệm</h2><img class="${P}dialog-photo" src="${escapeHTML(src)}" alt="Ảnh kỷ niệm ${photoIndex + 1}"><p style="text-align:center;margin-top:8px">${photoIndex + 1} / ${photos.length}</p><div class="${P}dialog-gallery-nav"><button type="button" data-slide="prev" aria-label="Ảnh trước">‹</button><button type="button" data-slide="next" aria-label="Ảnh tiếp">›</button></div>`);
    }

    onActivate('.' + P + 'photoBookImage', (_, node) => {
        const list = $$('.' + P + 'photoBookImage');
        showPhoto(list.indexOf(node));
    });

    panel?.addEventListener('click', e => {
        const btn = e.target.closest('[data-slide]');
        if (btn) showPhoto(photoIndex + (btn.dataset.slide === 'next' ? 1 : -1));
    });

    // Canvas Ký tên Sổ lưu bút
    const canvas = $('#signatureCanvas');
    const prev = $('#previewContainer');
    const group = $('#GROUP13');

    function setupCanvas() {
        if (!canvas) return;
        const dpr = window.devicePixelRatio || 1;
        const box = canvas.getBoundingClientRect();
        const w = box.width || 400;
        const h = box.height || 250;
        canvas.width = Math.round(w * dpr);
        canvas.height = Math.round(h * dpr);
        const ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);
        ctx.strokeStyle = '#262626';
        ctx.lineWidth = 2.4;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }

    function point(e) {
        const r = canvas.getBoundingClientRect();
        return { x: e.clientX - r.left, y: e.clientY - r.top };
    }

    if (canvas) {
        canvas.addEventListener('pointerdown', e => {
            signed = true;
            signatureDrawing = true;
            canvas.setPointerCapture(e.pointerId);
            const p = point(e);
            const c = canvas.getContext('2d');
            c.beginPath();
            c.moveTo(p.x, p.y);
        });

        canvas.addEventListener('pointermove', e => {
            if (!signatureDrawing) return;
            const p = point(e);
            const c = canvas.getContext('2d');
            c.lineTo(p.x, p.y);
            c.stroke();
        });

        for (const ev of ['pointerup', 'pointercancel', 'pointerleave']) {
            canvas.addEventListener(ev, () => signatureDrawing = false);
        }
    }

    onActivate('#BUTTON2', () => {
        if (!group) return;
        group.style.setProperty('display', 'block', 'important');
        setupCanvas();
        navigate('SECTION7');
        showToast('Hãy ký tên bằng ngón tay hoặc chuột');
    });

    onActivate('#BUTTON3', () => {
        canvas?.getContext('2d')?.clearRect(0, 0, canvas.width, canvas.height);
        signed = false;
        showToast('Đã xóa nét vẽ');
    });

    onActivate('#BUTTON4', () => {
        if (!signed) return showToast('Vui lòng ký tên trước');
        openModal(`<h2>Xem Trước Chữ Ký</h2><div style="text-align:center"><img style="width:100%;max-width:380px;border:1px dashed #ccc;background:#fafafa" src="${canvas.toDataURL('image/png')}" alt="Chữ ký"><div style="margin-top:12px"><button class="${P}dialog-action" data-section="SECTION7">Quay lại</button></div></div>`);
    });

    onActivate('#BUTTON5', () => {
        if (!signed) return showToast('Vui lòng ký tên trước');
        const signatures = loadStorage('signatures');
        const sigData = canvas.toDataURL('image/png');
        signatures.push({ image: sigData, date: new Date().toISOString() });
        saveStorage('signatures', signatures);
        renderSignatures();
        group?.style.setProperty('display', 'none', 'important');
        showToast('Đã lưu chữ ký thành công!');
    });

    function renderSignatures() {
        if (!prev) return;
        const list = loadStorage('signatures').slice(-12);
        prev.innerHTML = '';
        list.forEach(s => {
            const im = document.createElement('img');
            im.src = s.image;
            im.alt = 'Chữ ký khách mời';
            im.style.maxHeight = '70px';
            im.style.margin = '4px';
            prev.append(im);
        });
    }
    renderSignatures();

    // Form RSVP và Lời Chúc kết nối Backend Laravel Mây Hẹn
    const formRsvp = $('#FORM2 form');
    const formWish = $('#FORM3 form');

    function submitRsvp(e) {
        if (e) e.preventDefault();
        if (!formRsvp) return;
        if (!formRsvp.reportValidity()) return;

        const formData = new FormData(formRsvp);
        const guestName = (formData.get('name') || '').trim();
        const attending = formData.get('form_item6') || '';
        const guestCount = parseInt(formData.get('form_item14'), 10) || 1;
        const side = formData.get('form_item8') || 'Nhà Gái';
        const note = (formData.get('form_item26') || '').trim();

        if (!guestName) {
            showToast('Vui lòng nhập tên của bạn');
            return;
        }

        // Lưu local dự phòng
        const item = { guestName, attending, guestCount, side, note, createdAt: new Date().toISOString() };
        const localList = loadStorage('rsvp');
        localList.push(item);
        saveStorage('rsvp', localList);

        // Gửi AJAX lên hệ thống Mây Hẹn
        const submitUrl = DATA.rsvpSubmitUrl || '';
        if (submitUrl) {
            const payload = new FormData();
            payload.append('guest_name', guestName);
            payload.append('attending', attending.includes('không thể') ? 0 : 1);
            payload.append('guest_count', guestCount);
            payload.append('side', side.includes('Trai') ? 'groom' : 'bride');
            payload.append('note', note);

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: payload
            }).then(r => r.json()).then(res => {
                if (res.success || res.status === 'success') {
                    showToast('Đã gửi xác nhận tham dự thành công!');
                } else {
                    showToast(res.message || 'Đã ghi nhận xác nhận tham dự');
                }
            }).catch(() => {
                showToast('Đã lưu xác nhận tham dự');
            });
        } else {
            showToast('Đã lưu xác nhận tham dự (chế độ xem thử)');
        }

        formRsvp.reset();
    }

    function submitWish(e) {
        if (e) e.preventDefault();
        if (!formWish) return;
        if (!formWish.reportValidity()) return;

        const formData = new FormData(formWish);
        const name = (formData.get('name') || '').trim();
        const message = (formData.get('message') || '').trim();
        const visibility = formData.get('form_item12') || 'Mọi người';

        if (!name || !message) {
            showToast('Vui lòng nhập tên và lời chúc');
            return;
        }

        // Hiển thị ngay lời chúc mới vào hộp
        appendWishToBox(name, message);

        // Lưu local
        const item = { name, message, visibility, createdAt: new Date().toISOString() };
        const localList = loadStorage('wishes');
        localList.push(item);
        saveStorage('wishes', localList);

        // Gửi AJAX lên hệ thống Mây Hẹn
        const submitUrl = DATA.wishSubmitUrl || '';
        if (submitUrl) {
            const payload = new FormData();
            payload.append('guest_name', name);
            payload.append('wishes', message);

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: payload
            }).then(r => r.json()).then(res => {
                if (res.success || res.status === 'success') {
                    showToast('Cảm ơn bạn! Lời chúc đã được gửi thành công.');
                } else {
                    showToast(res.message || 'Đã gửi lời chúc');
                }
            }).catch(() => {
                showToast('Đã lưu lời chúc');
            });
        } else {
            showToast('Đã lưu lời chúc (chế độ xem thử)');
        }

        formWish.reset();
    }

    function appendWishToBox(name, message) {
        const box = $('#MessageBox');
        if (!box) return;
        const wrap = document.createElement('div');
        wrap.className = P + 'MessageBox-item ' + P + 'user-wish';
        const nameEl = document.createElement('h2');
        nameEl.className = P + 'MessageBox-item-name';
        nameEl.textContent = name;
        const msgEl = document.createElement('p');
        msgEl.className = P + 'MessageBox-item-message';
        msgEl.textContent = message;
        wrap.append(nameEl, msgEl);
        box.prepend(wrap);
    }

    formRsvp?.addEventListener('submit', submitRsvp);
    formWish?.addEventListener('submit', submitWish);
    onActivate('#BUTTON6', submitRsvp);
    onActivate('#BUTTON11', submitWish);

    function renderWishes() {
        const box = $('#MessageBox');
        if (!box) return;
        $$('.' + P + 'user-wish', box).forEach(el => el.remove());
        loadStorage('wishes')
            .filter(x => x.visibility !== 'Chỉ cô dâu, chú rể')
            .slice(-15)
            .forEach(item => {
                appendWishToBox(item.name || 'Khách mời', item.message || '');
            });
    }
    renderWishes();

    // Phong bì chào mừng mở thiệp (Greeting Gate)
    const openedOnce = (() => {
        try {
            return sessionStorage.getItem(storeKey + '_opened') === '1';
        } catch {
            return false;
        }
    })();

    if (!openedOnce) {
        const groom = DATA.groom || 'Ngọc Sơn';
        const bride = DATA.bride || 'Thúy Quỳnh';
        const guest = DATA.guest || 'Bạn và Gia Đình';

        openModal(`
            <p style="text-transform:uppercase;letter-spacing:3px;font-size:12px;color:#888;margin-bottom:8px">The Wedding of</p>
            <h2 style="font-size:32px;font-family:'Philosopher',serif;margin:10px 0">${escapeHTML(groom)} <span style="font-style:italic;color:#999">&amp;</span> ${escapeHTML(bride)}</h2>
            <div style="margin:20px auto;border-top:1px solid #ddd;border-bottom:1px solid #ddd;padding:20px 0;line-height:1.7">
                <p style="color:#666;font-size:14px;margin-bottom:4px">Thân gửi</p>
                <p style="font-size:19px;font-weight:700;color:#111;margin-bottom:8px">${escapeHTML(guest)}</p>
                <p style="font-size:14px;color:#555">Trân trọng kính mời bạn đến chung vui cùng chúng mình trong ngày trọng đại.</p>
            </div>
            <button id="mayhen-w18-open" class="${P}dialog-action" type="button" style="font-size:16px;padding:12px 28px">Mở Thiệp Cưới</button>
        `);

        $('#mayhen-w18-open')?.addEventListener('click', () => {
            try {
                sessionStorage.setItem(storeKey + '_opened', '1');
            } catch {}
            closeModal();
            window.scrollTo({ top: 0, behavior: 'instant' });
            // Tự động thử phát nhạc nếu có
            if (music && music.src && music.paused) {
                music.play().then(() => {
                    if (speakerOff) speakerOff.style.display = 'none';
                    if (speakerOn) speakerOn.style.display = 'block';
                }).catch(() => {});
            }
        });
    }

    setTimeout(responsive, 50);
    setTimeout(responsive, 600);
})();

