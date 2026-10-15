(() => {
  'use strict';
  const prefix = 'mayhen_invitation_dhsuudushi9428r_';
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const byId = name => document.getElementById(prefix + name);
  const className = name => '.' + prefix + name;

  // Lịch và bộ đếm ngược động theo dữ liệu cưới thực tế
  const weddingDateInfo = window.__MAYHEN_WEDDING_DATE || { year: 2027, month: 3, day: 7 };
  const targetYear = weddingDateInfo.year;
  const targetMonth = weddingDateInfo.month; // 1-12
  const targetDay = weddingDateInfo.day;

  const calendar = byId('calendar');
  if (calendar) {
    calendar.replaceChildren();
    ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'].forEach(day => {
      const cell = document.createElement('span');
      cell.className = prefix + 'calendar_week';
      cell.textContent = day;
      calendar.append(cell);
    });

    // Tính thứ của ngày 1 trong tháng (0 = CN, 1 = T2, ... 6 = T7)
    const firstDayIndex = new Date(targetYear, targetMonth - 1, 1).getDay();
    // Chuyển sang chuẩn bắt đầu từ Thứ 2 (MON = 0, SUN = 6)
    const offset = (firstDayIndex + 6) % 7;
    for (let i = 0; i < offset; i++) {
      const emptyCell = document.createElement('span');
      calendar.append(emptyCell);
    }

    const daysInMonth = new Date(targetYear, targetMonth, 0).getDate();
    for (let day = 1; day <= daysInMonth; day += 1) {
      const cell = document.createElement('span');
      cell.textContent = String(day).padStart(2, '0');
      if (day === targetDay) {
        cell.textContent = String(day);
        cell.className = prefix + 'calendar_day';
        cell.setAttribute('aria-label', `Ngày cưới: ${day} tháng ${targetMonth} năm ${targetYear}`);
      }
      calendar.append(cell);
    }
  }

  const targetTime = window.__MAYHEN_WEDDING_TARGET_TIME || new Date(targetYear, targetMonth - 1, targetDay, 7, 0, 0).getTime();
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
      if (el) el.textContent = String(value).padStart(unit === 'days' ? 3 : 2, '0');
    });
  }
  updateCountdown();
  setInterval(updateCountdown, 1000);

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

  const audio = byId('audio');
  audio.volume = 0.5;
  const musicButton = $(className('music'));
  async function playMusic() {
    try {
      await audio.play();
      musicButton.setAttribute('aria-pressed', 'true');
      musicButton.setAttribute('aria-label', 'Tắt nhạc nền');
    } catch {
      musicButton.setAttribute('aria-pressed', 'false');
    }
  }
  musicButton.addEventListener('click', () => {
    if (audio.paused) playMusic();
    else {
      audio.pause();
      musicButton.setAttribute('aria-pressed', 'false');
      musicButton.setAttribute('aria-label', 'Bật nhạc nền');
    }
  });
  const envelope = $(className('envelope_button'));
  envelope.addEventListener('click', () => {
    envelope.classList.add(prefix + 'opened');
    envelope.setAttribute('aria-expanded', 'true');
    if (audio.paused) playMusic();
    setTimeout(() => {
      envelope.hidden = true;
      $(className('open_hint')).hidden = true;
      $(className('opened_stage')).hidden = false;
    }, 450);
  });

  const tabButtons = $$(className('tab'));
  function activateTab(button) {
    tabButtons.forEach(tab => {
      const active = tab === button;
      tab.classList.toggle(prefix + 'tab_active', active);
      tab.setAttribute('aria-selected', String(active));
      document.getElementById(tab.getAttribute('aria-controls')).hidden = !active;
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

  const toTop = $(className('to_top'));
  const updateTopButton = () => toTop.classList.toggle(prefix + 'visible', window.scrollY > 350);
  window.addEventListener('scroll', updateTopButton, { passive: true });
  updateTopButton();
  toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  const modal = byId('modal');
  const modalTitle = byId('modal_title');
  const modalBody = $(className('modal_body'));
  let previousFocus = null;
  function openModal(title, content) {
    previousFocus = document.activeElement;
    modalTitle.textContent = title;
    modalBody.replaceChildren(content);
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    $(className('modal_close'), modal).focus();
  }
  function closeModal() {
    modal.hidden = true;
    document.body.style.overflow = '';
    if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
  }
  $$('[data-close="true"]', modal).forEach(el => el.addEventListener('click', closeModal));

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
  function submitButton(label) {
    const button = document.createElement('button');
    button.className = prefix + 'primary_button';
    button.type = 'submit';
    button.textContent = label;
    return button;
  }
  function guestbookModal() {
    const form = document.createElement('form');
    form.append(field('Tên của bạn', 'input', 'name'), field('Lời chúc', 'textarea', 'message'), note('Chế độ xem thử: lời chúc chỉ hiển thị trong phiên này và sẽ mất khi tải lại trang.'), submitButton('Gửi lời chúc'));
    form.addEventListener('submit', event => {
      event.preventDefault();
      const name = form.elements.name.value.trim();
      const message = form.elements.message.value.trim();
      if (!name || !message) return;
      const list = $(className('messages'));
      if (list.firstElementChild?.tagName === 'P') list.replaceChildren();
      const item = document.createElement('article');
      const author = document.createElement('strong');
      const text = document.createElement('p');
      author.textContent = name;
      text.textContent = message;
      item.append(author, text);
      list.prepend(item);
      $(className('guestboard_empty') + ' span').textContent = 'Cảm ơn lời chúc của bạn!';
      closeModal();
    });
    openModal('Gửi Lời Chúc', form);
  }
  function rsvpModal(attending) {
    const form = document.createElement('form');
    form.append(field('Họ và tên', 'input', 'name'));
    if (attending) form.append(field('Số người tham dự', 'input', 'guests'));
    form.append(field('Lời nhắn (không bắt buộc)', 'textarea', 'message', false), note('Xem thử giao diện: xác nhận không được gửi hay lưu lên máy chủ.'), submitButton('Xác nhận'));
    form.addEventListener('submit', event => {
      event.preventDefault();
      const name = form.elements.name.value.trim();
      if (!name) return;
      const summary = document.createElement('div');
      summary.append(note('Cảm ơn ' + name + '! Lựa chọn của bạn đã được ghi nhận trong phiên xem thử này.'));
      const okay = document.createElement('button');
      okay.className = prefix + 'primary_button';
      okay.textContent = 'Đóng';
      okay.type = 'button';
      okay.addEventListener('click', closeModal);
      summary.append(okay);
      modalTitle.textContent = attending ? 'Hẹn gặp tại lễ cưới!' : 'Cảm ơn lời phản hồi';
      modalBody.replaceChildren(summary);
    });
    openModal(attending ? 'Xác Nhận Tham Dự' : 'Rất tiếc, chưa sắp xếp được', form);
  }
  function giftModal() {
    const container = document.createElement('div');
    const tabs = document.createElement('div');
    tabs.className = prefix + 'bank_tabs';
    const image = document.createElement('img');
    image.className = prefix + 'qr';
    image.alt = 'Mã QR mừng cưới';
    const caption = document.createElement('p');
    caption.className = prefix + 'qr_caption';
    const qrGroom = window.__MAYHEN_GIFT_QR_GROOM || 'assets/4904bdb1-f9fa-4fdc-a3b2-f6a0be4df18b.jpg';
    const qrBride = window.__MAYHEN_GIFT_QR_BRIDE || 'assets/b9782cd0-2a24-412c-bd15-2626bbe77477.jpg';
    const banks = [
      { label: 'Nhà trai', image: qrGroom },
      { label: 'Nhà gái', image: qrBride }
    ];
    banks.forEach((bank, index) => {
      const button = document.createElement('button');
      button.textContent = bank.label;
      button.type = 'button';
      button.addEventListener('click', () => {
        image.src = bank.image;
        image.alt = 'Mã QR mừng cưới ' + bank.label;
        caption.textContent = bank.label;
        [...tabs.children].forEach(el => el.setAttribute('aria-selected', String(el === button)));
      });
      tabs.append(button);
    });
    container.append(tabs, image, caption, note('Sự hiện diện của bạn là món quà quý giá nhất đối với chúng tôi.'));
    openModal('Hộp Mừng Cưới', container);
    tabs.firstElementChild.click();
  }
  $('[data-modal="guestbook"]').addEventListener('click', guestbookModal);
  $('[data-modal="gift"]').addEventListener('click', giftModal);
  $$('[data-rsvp]').forEach(button => button.addEventListener('click', () => rsvpModal(button.dataset.rsvp === 'yes')));

  const lightbox = byId('lightbox');
  const photos = $$('[data-photo] img');
  let photoIndex = 0;
  function showPhoto(index) {
    photoIndex = (index + photos.length) % photos.length;
    $('img', lightbox).src = photos[photoIndex].src;
    $('img', lightbox).alt = photos[photoIndex].alt;
    $(className('lightbox_count'), lightbox).textContent = (photoIndex + 1) + ' / ' + photos.length;
  }
  function closeLightbox() {
    lightbox.hidden = true;
    document.body.style.overflow = '';
    $('[data-photo="' + photoIndex + '"]').focus();
  }
  $$('[data-photo]').forEach((button, index) => button.addEventListener('click', () => {
    showPhoto(index);
    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';
    $(className('lightbox_close'), lightbox).focus();
  }));
  $(className('lightbox_close'), lightbox).addEventListener('click', closeLightbox);
  $(className('lightbox_prev'), lightbox).addEventListener('click', () => showPhoto(photoIndex - 1));
  $(className('lightbox_next'), lightbox).addEventListener('click', () => showPhoto(photoIndex + 1));
  lightbox.addEventListener('click', event => { if (event.target === lightbox) closeLightbox(); });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      if (!lightbox.hidden) closeLightbox();
      else if (!modal.hidden) closeModal();
    }
    if (!lightbox.hidden && event.key === 'ArrowLeft') showPhoto(photoIndex - 1);
    if (!lightbox.hidden && event.key === 'ArrowRight') showPhoto(photoIndex + 1);
  });
})();
