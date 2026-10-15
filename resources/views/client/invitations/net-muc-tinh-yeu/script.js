(() => {
  'use strict';
  const prefix = 'mayhen_invitation_dhsuudushi9428r_';
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const byId = name => document.getElementById(prefix + name);
  const className = name => '.' + prefix + name;

  // 1. KHỞI TẠO LỊCH CƯỚI (CALENDAR)
  const calendar = byId('calendar');
  if (calendar) {
    const weddingDateData = window.__MAYHEN_WEDDING_DATE || { year: 2027, month: 3, day: 7 };
    const targetDay = weddingDateData.day || 7;
    
    ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'].forEach(day => {
      const cell = document.createElement('span');
      cell.className = prefix + 'calendar_week';
      cell.textContent = day;
      calendar.append(cell);
    });

    for (let day = 1; day <= 31; day += 1) {
      const cell = document.createElement('span');
      cell.textContent = String(day).padStart(2, '0');
      if (day === targetDay) {
        cell.textContent = String(day);
        cell.className = prefix + 'calendar_day';
        cell.setAttribute('aria-label', 'Ngày cưới: ' + targetDay + ' tháng 3');
      }
      calendar.append(cell);
    }
  }

  // 2. ĐẾM NGƯỢC (COUNTDOWN TIMER)
  const targetTime = window.__MAYHEN_WEDDING_TARGET_TIME || new Date(2027, 2, 7, 7, 0, 0).getTime();
  function updateCountdown() {
    let remaining = Math.max(0, Math.floor((targetTime - Date.now()) / 1000));
    const values = {
      days: Math.floor(remaining / 86400),
      hours: Math.floor((remaining % 86400) / 3600),
      minutes: Math.floor((remaining % 3600) / 60),
      seconds: remaining % 60
    };
    Object.entries(values).forEach(([unit, value]) => {
      const el = $('[data-unit="' + unit + '"]');
      if (el) {
        el.textContent = String(value).padStart(unit === 'days' ? 3 : 2, '0');
      }
    });
  }
  updateCountdown();
  setInterval(updateCountdown, 1000);

  // 3. HIỆU ỨNG HIỂN THỊ KHI CUỘN (SCROLL REVEAL)
  const revealItems = $$(className('reveal'));
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add(prefix + 'visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px 30px 0px' });
    revealItems.forEach(item => observer.observe(item));
  } else {
    revealItems.forEach(item => item.classList.add(prefix + 'visible'));
  }

  // 4. NHẠC NỀN (BACKGROUND MUSIC)
  const audio = byId('audio');
  if (audio) {
    audio.volume = 0.5;
  }
  const musicButton = $(className('music'));
  async function playMusic() {
    if (!audio) return;
    try {
      await audio.play();
      if (musicButton) {
        musicButton.setAttribute('aria-pressed', 'true');
        musicButton.setAttribute('aria-label', 'Tắt nhạc nền');
      }
    } catch {
      if (musicButton) {
        musicButton.setAttribute('aria-pressed', 'false');
      }
    }
  }

  if (musicButton && audio) {
    musicButton.addEventListener('click', () => {
      if (audio.paused) {
        playMusic();
      } else {
        audio.pause();
        musicButton.setAttribute('aria-pressed', 'false');
        musicButton.setAttribute('aria-label', 'Bật nhạc nền');
      }
    });
  }

  // 5. TRẢI NGHIỆM MỞ THIỆP ĐỈNH CAO (ENVELOPE OPENING & SMOOTH SCROLL)
  const envelope = document.getElementById('mayhen_envelope_element');
  const openBtn = document.getElementById('mayhen_open_invitation_btn');
  const scrollDownBtn = document.getElementById('mayhen_scroll_down_btn');
  const coverSection = document.getElementById('mayhen_invitation_cover');
  const mainContent = document.getElementById('mayhen_invitation_main_content');

  let isOpened = false;

  function openEnvelope() {
    if (isOpened) return;
    isOpened = true;

    // 1. Kích hoạt hiệu ứng mở nắp phong bì và tem sáp
    if (envelope) {
      envelope.classList.add('mayhen_opened');
      envelope.setAttribute('aria-expanded', 'true');
    }
    if (coverSection) {
      coverSection.classList.add('mayhen_opened_state');
    }

    // 2. Bật nhạc nền
    if (audio && audio.paused) {
      playMusic();
    }

    // 3. Mở khóa cuộn trang
    document.body.classList.remove('mayhen_invitation_scroll_locked');

    // 4. Sau khi thiệp trồi lên (khoảng 800ms), tự động cuộn nhẹ nhàng xuống nội dung
    setTimeout(() => {
      if (mainContent) {
        mainContent.scrollIntoView({ behavior: 'smooth' });
      }
    }, 850);
  }

  if (envelope) {
    envelope.addEventListener('click', openEnvelope);
    envelope.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openEnvelope();
      }
    });
  }

  if (openBtn) {
    openBtn.addEventListener('click', openEnvelope);
  }

  if (scrollDownBtn && mainContent) {
    scrollDownBtn.addEventListener('click', () => {
      mainContent.scrollIntoView({ behavior: 'smooth' });
    });
  }

  // 6. CHUYỂN TAB TIỆC NHÀ GÁI / TIỆC NHÀ TRAI
  const tabButtons = $$(className('tab'));
  function activateTab(button) {
    tabButtons.forEach(tab => {
      const active = tab === button;
      tab.classList.toggle(prefix + 'tab_active', active);
      tab.setAttribute('aria-selected', String(active));
      const targetPanel = document.getElementById(tab.getAttribute('aria-controls'));
      if (targetPanel) {
        targetPanel.hidden = !active;
      }
    });
  }

  tabButtons.forEach((button, index) => {
    button.addEventListener('click', () => activateTab(button));
    button.addEventListener('keydown', event => {
      if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
      event.preventDefault();
      const next = tabButtons[(index + (event.key === 'ArrowRight' ? 1 : -1) + tabButtons.length) % tabButtons.length];
      activateTab(next);
      next.focus();
    });
  });

  // 7. NÚT LÊN ĐẦU TRANG
  const toTop = $(className('to_top'));
  if (toTop) {
    const updateTopButton = () => toTop.classList.toggle(prefix + 'visible', window.scrollY > 350);
    window.addEventListener('scroll', updateTopButton, { passive: true });
    updateTopButton();
    toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // 8. MODAL VÀ THÔNG BÁO TƯƠNG TÁC
  const modal = byId('modal');
  const modalTitle = byId('modal_title');
  const modalBody = $(className('modal_body'));
  let previousFocus = null;

  function openModal(title, content) {
    if (!modal) return;
    previousFocus = document.activeElement;
    modalTitle.textContent = title;
    modalBody.replaceChildren(content);
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    const closeBtn = $(className('modal_close'), modal);
    if (closeBtn) closeBtn.focus();
  }

  function closeModal() {
    if (!modal) return;
    modal.hidden = true;
    document.body.style.overflow = '';
    if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
  }

  if (modal) {
    $$('[data-close="true"]', modal).forEach(el => el.addEventListener('click', closeModal));
  }

  function field(label, tag, name, required = true) {
    const wrapper = document.createElement('label');
    wrapper.textContent = label;
    const input = document.createElement(tag);
    input.name = name;
    input.required = required;
    input.maxLength = name === 'message' ? 400 : 80;
    if (tag === 'textarea') input.rows = 3;
    if (name === 'guests') { input.type = 'number'; input.min = '1'; input.max = '10'; input.value = '1'; }
    wrapper.append(input);
    return wrapper;
  }

  function note(text) {
    const p = document.createElement('p');
    p.className = prefix + 'modal_note';
    p.textContent = text;
    return p;
  }

  // Sổ lưu bút (Guestbook)
  const guestbookButton = $('[data-modal="guestbook"]');
  if (guestbookButton) {
    guestbookButton.addEventListener('click', () => {
      const form = document.createElement('form');
      form.append(
        field('Tên của bạn *', 'input', 'name'),
        field('Lời chúc hạnh phúc gửi đôi bạn *', 'textarea', 'message'),
        note('Lời chúc của bạn sẽ được lưu giữ trang trọng trong sổ lưu niệm của cô dâu chú rể.')
      );
      const submit = document.createElement('button');
      submit.type = 'submit';
      submit.className = prefix + 'primary_button';
      submit.textContent = 'Gửi Lời Chúc';
      form.append(submit);

      form.addEventListener('submit', async event => {
        event.preventDefault();
        const data = new FormData(form);
        const name = (data.get('name') || '').trim();
        const message = (data.get('message') || '').trim();
        if (!name || !message) {
          alert('Vui lòng điền đầy đủ tên và lời chúc của bạn.');
          return;
        }

        submit.disabled = true;
        submit.textContent = 'Đang gửi...';

        const submitUrl = window.__MAYHEN_WISH_SUBMIT_URL || (window.location.pathname.replace(/\/$/, '') + '/wishes');
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = window.__MAYHEN_CSRF_TOKEN || (csrfMeta ? csrfMeta.content : '');

        try {
          const response = await fetch(submitUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ name, message })
          });

          const result = await response.json();
          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Có lỗi xảy ra khi gửi lời chúc.');
          }

          // Thành công: Cập nhật DOM
          const container = document.getElementById('mayhen_wishes_container') || $(className('messages'));
          if (container) {
            const emptyNote = container.querySelector(className('no_wishes')) || container.querySelector('p');
            if (emptyNote) emptyNote.remove();

            const card = document.createElement('article');
            card.className = prefix + 'wish_item';

            const header = document.createElement('div');
            header.className = prefix + 'wish_header';

            const author = document.createElement('strong');
            author.textContent = result.wish ? result.wish.name : name;

            const time = document.createElement('time');
            time.textContent = result.wish ? (result.wish.created_at_human || 'Vừa xong') : 'Vừa xong';

            header.append(author, time);

            const body = document.createElement('p');
            body.textContent = result.wish ? result.wish.message : message;

            card.append(header, body);
            container.prepend(card);
          }

          // Cập nhật bộ đếm lời chúc
          const counter = document.getElementById('mayhen_guestboard_counter');
          if (counter) {
            const countStrong = counter.querySelector('strong');
            if (countStrong) {
              const current = parseInt(countStrong.textContent, 10) || 0;
              countStrong.textContent = current + 1;
            } else {
              counter.innerHTML = '<span>Đã có <strong>1</strong> lời chúc phúc gửi tới đôi bạn</span>';
            }
          }

          closeModal();
          alert(result.message || 'Cảm ơn bạn đã gửi lời chúc tốt đẹp!');
        } catch (error) {
          alert(error.message || 'Không thể kết nối đến máy chủ. Vui lòng thử lại!');
        } finally {
          submit.disabled = false;
          submit.textContent = 'Gửi Lời Chúc';
        }
      });

      openModal('Ký Tên Chúc Phúc', form);
    });
  }

  // Hộp mừng cưới (Gift VietQR)
  const giftButton = $('[data-modal="gift"]');
  if (giftButton) {
    giftButton.addEventListener('click', () => {
      const container = document.createElement('div');
      const tabs = document.createElement('div');
      tabs.className = prefix + 'bank_tabs';
      
      const groomTab = document.createElement('button');
      groomTab.type = 'button';
      groomTab.textContent = 'Mừng Chú Rể';
      groomTab.setAttribute('aria-selected', 'true');

      const brideTab = document.createElement('button');
      brideTab.type = 'button';
      brideTab.textContent = 'Mừng Cô Dâu';
      brideTab.setAttribute('aria-selected', 'false');

      tabs.append(groomTab, brideTab);

      const qrImg = document.createElement('img');
      qrImg.className = prefix + 'qr';
      const qrGroom = window.__MAYHEN_GIFT_QR_GROOM || '';
      const qrBride = window.__MAYHEN_GIFT_QR_BRIDE || '';
      qrImg.src = qrGroom;
      qrImg.alt = 'Mã VietQR mừng cưới';

      const caption = document.createElement('p');
      caption.className = prefix + 'qr_caption';
      caption.textContent = 'Quét mã VietQR chuyển khoản nhanh bằng ứng dụng ngân hàng';

      groomTab.addEventListener('click', () => {
        groomTab.setAttribute('aria-selected', 'true');
        brideTab.setAttribute('aria-selected', 'false');
        qrImg.src = qrGroom;
      });

      brideTab.addEventListener('click', () => {
        brideTab.setAttribute('aria-selected', 'true');
        groomTab.setAttribute('aria-selected', 'false');
        qrImg.src = qrBride;
      });

      container.append(tabs, qrImg, caption);
      openModal('Hộp Mừng Cưới', container);
    });
  }

  // Xác nhận tham dự (RSVP Choices)
  $$('[data-rsvp]').forEach(button => {
    button.addEventListener('click', () => {
      const choice = button.getAttribute('data-rsvp');
      const isYes = choice === 'yes';
      const form = document.createElement('form');
      form.append(
        field('Họ và tên *', 'input', 'name'),
        field('Số điện thoại *', 'input', 'phone')
      );

      if (isYes) {
        form.append(
          field('Số lượng người tham dự', 'input', 'guests'),
          note('Cảm ơn bạn đã nhận lời! Sự hiện diện của bạn là niềm vinh dự lớn đối với chúng tôi.')
        );
      } else {
        form.append(
          field('Lời nhắn gửi tới cặp đôi', 'textarea', 'message', false),
          note('Rất tiếc vì bạn chưa thể tham gia. Cảm ơn bạn vì đã luôn quan tâm và chúc phúc!')
        );
      }

      const submit = document.createElement('button');
      submit.type = 'submit';
      submit.className = prefix + 'primary_button';
      submit.textContent = 'Xác Nhận';
      form.append(submit);

      form.addEventListener('submit', async event => {
        event.preventDefault();
        const data = new FormData(form);
        const guestName = (data.get('name') || '').trim();
        const phone = (data.get('phone') || '').trim();
        const guestsCount = isYes ? (parseInt(data.get('guests'), 10) || 1) : 0;
        const wishMessage = !isYes ? (data.get('message') || '').trim() : '';

        if (!guestName) {
          alert('Vui lòng nhập họ và tên của bạn.');
          return;
        }

        submit.disabled = true;
        submit.textContent = 'Đang ghi nhận...';

        const submitUrl = window.__MAYHEN_RSVP_SUBMIT_URL || (window.location.pathname.replace(/\/$/, '') + '/rsvp');
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = window.__MAYHEN_CSRF_TOKEN || (csrfMeta ? csrfMeta.content : '');

        try {
          const response = await fetch(submitUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
              guest_name: guestName,
              phone: phone,
              attending_status: isYes ? 'attending' : 'declined',
              adults_count: guestsCount,
              children_count: 0,
              wishes: wishMessage
            })
          });

          const result = await response.json();
          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Có lỗi xảy ra khi gửi phản hồi.');
          }

          closeModal();
          alert(result.message || (isYes ? 'Cảm ơn bạn đã xác nhận tham dự!' : 'Cảm ơn bạn đã phản hồi!'));

          // Cập nhật giao diện nút lựa chọn
          button.style.background = 'var(--mayhen_ink)';
          button.style.color = '#ffffff';
          button.style.borderColor = 'var(--mayhen_ink)';
        } catch (error) {
          alert(error.message || 'Không thể kết nối đến máy chủ. Vui lòng thử lại!');
        } finally {
          submit.disabled = false;
          submit.textContent = 'Xác Nhận';
        }
      });

      openModal(isYes ? 'Xác Nhận Tham Dự Lễ Cưới' : 'Gửi Lời Nhắn Tới Đôi Bạn', form);
    });
  });

  // 9. LIGHTBOX PHÓNG TO ALBUM ẢNH
  const lightbox = byId('lightbox');
  if (lightbox) {
    const lightboxImg = $('img', lightbox);
    const lightboxCount = $(className('lightbox_count'), lightbox);
    const photos = $$('[data-photo]');
    let currentIndex = 0;

    function showPhoto(index) {
      currentIndex = (index + photos.length) % photos.length;
      const targetPhoto = photos[currentIndex];
      const img = $('img', targetPhoto);
      if (img && lightboxImg) {
        lightboxImg.src = img.src;
        lightboxImg.alt = img.alt;
      }
      if (lightboxCount) {
        lightboxCount.textContent = (currentIndex + 1) + ' / ' + photos.length;
      }
    }

    photos.forEach(btn => {
      btn.addEventListener('click', () => {
        const index = parseInt(btn.getAttribute('data-photo'), 10) || 0;
        showPhoto(index);
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';
      });
    });

    const closeLightbox = () => {
      lightbox.hidden = true;
      document.body.style.overflow = '';
    };

    const closeBtn = $(className('lightbox_close'), lightbox);
    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);

    const prevBtn = $(className('lightbox_prev'), lightbox);
    if (prevBtn) prevBtn.addEventListener('click', () => showPhoto(currentIndex - 1));

    const nextBtn = $(className('lightbox_next'), lightbox);
    if (nextBtn) nextBtn.addEventListener('click', () => showPhoto(currentIndex + 1));

    window.addEventListener('keydown', (e) => {
      if (lightbox.hidden) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') showPhoto(currentIndex - 1);
      if (e.key === 'ArrowRight') showPhoto(currentIndex + 1);
    });
  }
})();
