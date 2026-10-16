(() => {
  'use strict';

  // Tự động đưa trang về đầu khi tải lại (F5 / Reload / History Navigation)
  // Bắt buộc xem phần mở phong thư trước khi mở khóa nội dung
  if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
  }
  window.scrollTo(0, 0);

  const prefix = 'mayhen_invitation_dhsuudushi9428r_';
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const byId = name => document.getElementById(prefix + name);
  const className = name => '.' + prefix + name;

  // Đảm bảo cuộn về đỉnh khi DOM sẵn sàng và sau khi toàn bộ tài nguyên đã nạp
  document.addEventListener('DOMContentLoaded', () => {
    window.scrollTo(0, 0);
  });
  window.addEventListener('load', () => {
    window.scrollTo(0, 0);
  });

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

  function field(label, tag, name, required = true, initialValue = '') {
    const wrapper = document.createElement('label');
    wrapper.textContent = label;
    const input = document.createElement(tag);
    input.name = name;
    input.required = required;
    input.maxLength = name === 'message' ? 400 : 80;
    if (tag === 'textarea') {
      input.rows = 3;
      if (initialValue) input.value = initialValue;
    } else {
      if (initialValue) input.value = initialValue;
    }
    if (name === 'guests') { input.type = 'number'; input.min = '1'; input.max = '10'; input.value = initialValue || '1'; }
    wrapper.append(input);
    return wrapper;
  }

  function note(text) {
    const p = document.createElement('p');
    p.className = prefix + 'modal_note';
    p.textContent = text;
    return p;
  }

  // 4.1 BỘ LỌC TỪ KHÓA CHỬI BẬY, TỤC TĨU, XÚC PHẠM, TRÙ ẺO ĐÁM CƯỚI (FE MODERATION)
  const VIETNAMESE_BAD_WORDS = [
    // Tục tĩu, bộ phận sinh dục, hành vi tình dục
    'buồi', 'buoi', 'bòi', 'cặc', 'cac', 'kặc', 'kac', 'dái', 'hòn dái', 'hon dai',
    'lồn', 'lon', 'lồz', 'loz', 'l0n', 'bướm', 'hột le', 'vú', 'bóp vú',
    'địt', 'dit', 'd1t', 'đ1t', 'đụ', 'đụ má', 'du ma', 'đụ mẹ', 'du me', 'đụ cụ', 'du cu',
    'chịch', 'chich', 'nện', 'xoạc', 'vét máng', 'vet mang', 'bú cu', 'bu cu', 'liếm lồn', 'liem lon',
    'sóc lọ', 'soc lo', 'sục cặc', 'suc cac', 'thủ dâm', 'thu dam', 'thông đít', 'thong dit',
    'giao cấu', 'giao cau', 'hiếp dâm', 'hấp diêm', 'hap diem', 'cưỡng bức', 'quấy rối tình dục',

    // Chửi thề, viết tắt tiếng lóng Gen Z & MXH
    'đm', 'dm', 'đkm', 'dkm', 'đcm', 'dcm', 'đmm', 'dmm',
    'vcl', 'vkl', 'vcc', 'vl', 'vlon', 'vloz',
    'clgt', 'clgv', 'cmn', 'cmnr', 'cc', 'dbrr', 'đbrr', 'dcmm', 'đcmm',
    'đéo', 'deo', 'đếch', 'dech', 'méo',

    // Xúc phạm cha mẹ, tổ tiên, nhân phẩm gia đình
    'mẹ mày', 'me may', 'mẹ m', 'me m', 'bố mày', 'bo may', 'bố m', 'bo m',
    'bà già mày', 'ba gia may', 'ông già mày', 'ong gia may',
    'cha mày', 'cha may', 'tổ cha', 'to cha', 'tổ mẹ', 'to me',
    'mả cha', 'ma cha', 'mả mẹ', 'ma me', 'tiên sư', 'tien su', 'ông nội mày', 'bà nội mày',

    // Miệt thị phụ nữ, nghề nghiệp xúc phạm
    'đĩ', 'con đĩ', 'con di', 'đĩ mẹ', 'di me', 'đĩ chó', 'di cho', 'đĩ thõa', 'thằng đĩ', 'thang di',
    'điếm', 'diem', 'gái bao', 'gai bao', 'gái gọi', 'gai goi', 'phò', 'pho', 'cave',
    'bán dâm', 'ban dam', 'mại dâm', 'mai dam',

    // Chửi thú vật, miệt thị trí tuệ
    'chó đẻ', 'cho de', 'chó chết', 'cho chet', 'đồ chó', 'do cho', 'thằng chó', 'thang cho', 'con chó', 'con cho',
    'chó má', 'cho ma', 'súc vật', 'suc vat', 'súc sinh', 'suc sinh', 'xúc vật', 'xuc vat',
    'đồ lợn', 'do lon', 'con lợn', 'con lon', 'đồ heo', 'con heo',
    'đồ ngu', 'do ngu', 'ngu lồn', 'ngu lon', 'ngu dốt', 'ngu si', 'óc chó', 'oc cho',
    'óc lợn', 'oc lon', 'óc bò', 'oc bo', 'đần độn', 'dan don', 'đồ đần', 'do dan',
    'đồ khùng', 'do khung', 'bị khùng', 'bi khung', 'thằng khùng', 'thang khung', 'con khùng', 'con khung',
    'đồ điên', 'do dien', 'bị điên', 'bi dien', 'thằng điên', 'thang dien', 'con điên', 'con dien',
    'thiểu năng', 'thieu nang', 'bại não', 'bai nao', 'thần kinh', 'than kinh', 'tâm thần', 'tam than',

    // Trù ẻo đám cưới, tang tóc, ly tán, phá hoại hạnh phúc (Đặc thù thiệp cưới)
    'chết mẹ', 'chet me', 'chết cha', 'chet cha', 'đi chết', 'di chet',
    'chết tiệt', 'chet tiet', 'đoản mệnh', 'doan menh', 'tai nạn', 'tai nan',
    'đám ma', 'dam ma', 'tang gia', 'ma chay', 'quan tài', 'quan tai', 'khăn tang', 'khan tang',
    'bát hương', 'bat huong', 'vòng hoa tang', 'chia buồn tang tóc',
    'ly dị', 'ly di', 'ly hôn', 'ly hon', 'đứt gánh', 'dut ganh',
    'cắm sừng', 'cam sung', 'mọc sừng', 'moc sung', 'tan vỡ', 'tan vo', 'bỏ nhau', 'bo nhau',
    'ngoại tình', 'ngoai tinh', 'phản bội', 'phan boi', 'tiểu tam', 'tieu tam', 'trà xanh', 'tra xanh',
    'con giáp thứ 13', 'con giap thu 13', 'giật chồng', 'giat chong', 'giật vợ', 'giat vo',
    'vô sinh', 'vo sinh', 'tuyệt tự', 'tuyet tu', 'đẻ không có lỗ đít', 'quả báo', 'qua bao'
  ];

  function removeVietnameseTones(str) {
    if (!str) return '';
    return str
      .replace(/[àáạảãâầấậẩẫăằắặẳẵ]/gi, 'a')
      .replace(/[èéẹẻẽêềếệểễ]/gi, 'e')
      .replace(/[ìíịỉĩ]/gi, 'i')
      .replace(/[òóọỏõôồốộổỗơờớợởỡ]/gi, 'o')
      .replace(/[ùúụủũưừứựửữ]/gi, 'u')
      .replace(/[ỳýỵỷỹ]/gi, 'y')
      .replace(/[đ]/gi, 'd')
      .replace(/[Đ]/gi, 'D');
  }

  function checkProfanity(text) {
    if (!text || typeof text !== 'string') return false;
    const lower = text.toLowerCase();
    const ascii = removeVietnameseTones(lower);
    const leet = ascii
      .replace(/0/g, 'o')
      .replace(/1/g, 'i')
      .replace(/3/g, 'e')
      .replace(/4/g, 'a')
      .replace(/7/g, 't')
      .replace(/@/g, 'a');

    for (const bad of VIETNAMESE_BAD_WORDS) {
      if (bad.includes(' ')) {
        if (lower.includes(bad) || ascii.includes(bad) || leet.includes(bad)) {
          return true;
        }
      } else {
        const boundaryRegex = new RegExp('(?:^|[^a-z0-9_])' + bad + '(?:[^a-z0-9_]|$)', 'i');
        if (boundaryRegex.test(lower) || boundaryRegex.test(ascii) || boundaryRegex.test(leet)) {
          return true;
        }
      }
    }

    const separatedPatterns = [
      /d[\s._*~-]*m/i,
      /d[\s._*~-]*k[\s._*~-]*m/i,
      /d[\s._*~-]*c[\s._*~-]*m/i,
      /v[\s._*~-]*c[\s._*~-]*l/i,
      /v[\s._*~-]*k[\s._*~-]*l/i,
      /v[\s._*~-]*l/i,
      /c[\s._*~-]*l[\s._*~-]*g[\s._*~-]*t/i,
      /c[\s._*~-]*a[\s._*~-]*c/i,
      /l[\s._*~-]*[o0*][\s._*~-]*n/i,
      /d[\s._*~-]*[i1*][\s._*~-]*t/i,
      /b[\s._*~-]*u[\s._*~-]*[o0*][\s._*~-]*i/i
    ];

    for (const regex of separatedPatterns) {
      if (regex.test(leet)) {
        return true;
      }
    }

    return false;
  }

  // Sổ lưu bút (Guestbook)
  const guestbookButton = $('[data-modal="guestbook"]');
  if (guestbookButton) {
    guestbookButton.addEventListener('click', () => {
      if (window.__MAYHEN_IS_OWNER) {
        alert('Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi lời chúc. Bạn có thể nhấn "Trả lời" dưới mỗi lời chúc của khách mời.');
        return;
      }

      const myWish = window.__MAYHEN_MY_WISH;
      const isEditing = Boolean(myWish && myWish.message);

      const form = document.createElement('form');
      form.append(
        field('Tên của bạn *', 'input', 'name', true, myWish ? myWish.name : ''),
        field('Lời chúc hạnh phúc gửi đôi bạn *', 'textarea', 'message', true, myWish ? myWish.message : ''),
        note(isEditing 
          ? 'Bạn đang chỉnh sửa lời chúc đã gửi trước đó. Lời chúc sẽ được cập nhật trong sổ lưu niệm.' 
          : 'Lời chúc của bạn sẽ được lưu giữ trang trọng trong sổ lưu niệm của cô dâu chú rể.'
        )
      );
      const submit = document.createElement('button');
      submit.type = 'submit';
      submit.className = prefix + 'primary_button';
      submit.textContent = isEditing ? 'Lưu Cập Nhật Lời Chúc' : 'Gửi Lời Chúc';
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

        if (checkProfanity(name) || checkProfanity(message)) {
          alert('Vui lòng sử dụng ngôn từ lịch sự, trang trọng để gửi lời chúc phúc tới cặp đôi.');
          return;
        }

        submit.disabled = true;
        submit.textContent = isEditing ? 'Đang cập nhật...' : 'Đang gửi...';

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

          // Cập nhật trạng thái lời chúc của IP này
          window.__MAYHEN_MY_WISH = {
            id: result.wish ? result.wish.id : (myWish ? myWish.id : null),
            name: result.wish ? result.wish.name : name,
            message: result.wish ? result.wish.message : message
          };

          // Cập nhật text nút bấm
          guestbookButton.textContent = 'Chỉnh sửa lời chúc của bạn';

          // Thành công: Cập nhật DOM
          const container = document.getElementById('mayhen_wishes_container') || $(className('messages'));
          if (container) {
            const emptyNote = container.querySelector(className('no_wishes')) || container.querySelector('p');
            if (emptyNote) emptyNote.remove();

            // Tìm xem đã có thẻ wish của IP này chưa
            let myWishCard = document.getElementById('mayhen_my_wish_item');

            if (myWishCard && (result.is_update || isEditing)) {
              // Cập nhật thẻ cũ
              const strongEl = myWishCard.querySelector('strong');
              if (strongEl) {
                strongEl.innerHTML = (result.wish ? result.wish.name : name) + ' <span class="' + prefix + 'my_wish_tag">Lời chúc của bạn</span>';
              }
              const timeEl = myWishCard.querySelector('time');
              if (timeEl) {
                timeEl.textContent = 'Vừa cập nhật';
              }
              const bodyEl = myWishCard.querySelector(className('wish_message')) || myWishCard.querySelector('p');
              if (bodyEl) {
                bodyEl.textContent = result.wish ? result.wish.message : message;
              }
            } else {
              // Thêm thẻ mới
              const card = document.createElement('article');
              card.className = prefix + 'wish_item';
              card.id = 'mayhen_my_wish_item';
              card.setAttribute('data-my-wish', 'true');

              const header = document.createElement('div');
              header.className = prefix + 'wish_header';

              const author = document.createElement('strong');
              author.textContent = result.wish ? result.wish.name : name;

              const tag = document.createElement('span');
              tag.className = prefix + 'my_wish_tag';
              tag.textContent = 'Lời chúc của bạn';
              author.append(tag);

              const time = document.createElement('time');
              time.textContent = result.wish ? (result.wish.created_at_human || 'Vừa xong') : 'Vừa xong';

              header.append(author, time);

              const body = document.createElement('p');
              body.className = prefix + 'wish_message';
              body.textContent = result.wish ? result.wish.message : message;

              card.append(header, body);
              container.prepend(card);

              // Cập nhật bộ đếm lời chúc nếu là tạo mới
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
            }
          }

          closeModal();
          alert(result.message || 'Cảm ơn bạn đã gửi lời chúc tốt đẹp!');
        } catch (error) {
          alert(error.message || 'Không thể kết nối đến máy chủ. Vui lòng thử lại!');
        } finally {
          submit.disabled = false;
          submit.textContent = isEditing ? 'Lưu Cập Nhật Lời Chúc' : 'Gửi Lời Chúc';
        }
      });

      openModal(isEditing ? 'Chỉnh Sửa Lời Chúc Của Bạn' : 'Ký Tên Chúc Phúc', form);
    });
  }

  // Xử lý sự kiện Chủ nhân trả lời lời chúc của khách mời
  document.addEventListener('click', async event => {
    const replyBtn = event.target.closest(className('reply_trigger'));
    if (!replyBtn) return;

    const wishId = replyBtn.getAttribute('data-wish-id');
    const author = replyBtn.getAttribute('data-author') || 'khách mời';
    const currentReply = replyBtn.getAttribute('data-reply') || '';

    const form = document.createElement('form');
    form.append(
      field(`Lời phản hồi từ Cô Dâu & Chú Rể gửi tới ${author} *`, 'textarea', 'reply', true, currentReply),
      note('Lời cảm ơn của bạn sẽ hiển thị công khai ngay bên dưới lời chúc của khách mời này.')
    );

    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.className = prefix + 'primary_button';
    submit.textContent = currentReply ? 'Lưu Thay Đổi' : 'Gửi Phản Hồi';
    form.append(submit);

    form.addEventListener('submit', async e => {
      e.preventDefault();
      const data = new FormData(form);
      const replyText = (data.get('reply') || '').trim();
      if (!replyText) {
        alert('Vui lòng nhập nội dung phản hồi.');
        return;
      }

      if (checkProfanity(replyText)) {
        alert('Vui lòng sử dụng ngôn từ lịch sự, trang trọng.');
        return;
      }

      submit.disabled = true;
      submit.textContent = 'Đang lưu...';

      const replyUrl = (window.__MAYHEN_WISH_REPLY_BASE_URL || '') + `/${wishId}/reply`;
      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      const csrfToken = window.__MAYHEN_CSRF_TOKEN || (csrfMeta ? csrfMeta.content : '');

      try {
        const response = await fetch(replyUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({ reply: replyText })
        });

        const result = await response.json();
        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Không thể lưu phản hồi.');
        }

        // Cập nhật DOM
        const replyBox = document.getElementById(`mayhen_wish_reply_${wishId}`);
        if (replyBox) {
          replyBox.style.display = 'block';
          const bodyEl = replyBox.querySelector(className('wish_reply_body')) || replyBox.querySelector('p');
          if (bodyEl) bodyEl.textContent = replyText;
        }

        replyBtn.setAttribute('data-reply', replyText);
        replyBtn.textContent = '✎ Sửa phản hồi';

        closeModal();
        alert('Đã gửi phản hồi lời chúc thành công!');
      } catch (error) {
        alert(error.message || 'Có lỗi xảy ra khi gửi phản hồi.');
      } finally {
        submit.disabled = false;
        submit.textContent = 'Gửi Phản Hồi';
      }
    });

    openModal(`Phản Hồi Lời Chúc Của ${author}`, form);
  });

  // Xử lý xem danh sách phản hồi RSVP của Chủ nhân
  const viewRsvpBtn = document.getElementById('mayhen_btn_view_rsvp_list');
  if (viewRsvpBtn) {
    viewRsvpBtn.addEventListener('click', async () => {
      const modalContainer = document.createElement('div');
      modalContainer.className = 'mayhen_rsvp_list_modal';
      modalContainer.innerHTML = '<p style="text-align:center;padding:24px;font-style:italic;color:var(--mayhen_muted);">Đang tải danh sách phản hồi...</p>';

      openModal('Danh Sách Phản Hồi Tham Dự', modalContainer);

      try {
        const response = await fetch(window.__MAYHEN_RSVP_RESPONSES_URL, {
          headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Không thể tải danh sách phản hồi.');
        }

        const responses = result.responses || [];
        if (responses.length === 0) {
          modalContainer.innerHTML = '<p class="mayhen_rsvp_empty_text">Chưa có khách mời nào gửi xác nhận tham dự.</p>';
          return;
        }

        let html = `
          <div style="margin-bottom:12px;font-size:12px;color:var(--mayhen_ink);display:flex;gap:12px;justify-content:flex-end;">
            <span>Tổng: <strong>${responses.length}</strong> khách</span>
          </div>
          <table class="mayhen_rsvp_table">
            <thead>
              <tr>
                <th>Khách Mời</th>
                <th>Trạng Thái</th>
                <th>Số Khách</th>
                <th>Lời Nhắn</th>
                <th>Thời Gian</th>
              </tr>
            </thead>
            <tbody>
        `;

        responses.forEach(r => {
          const isAttending = r.attending_status === 'attending';
          const tagClass = isAttending ? 'mayhen_rsvp_tag_attending' : 'mayhen_rsvp_tag_declined';
          const tagText = isAttending ? 'Tham dự' : 'Không đi';
          const guestsCount = isAttending ? (r.adults_count || 1) : 0;
          const wishes = r.wishes ? r.wishes : '-';
          const phone = r.phone ? `<br><small style="color:var(--mayhen_muted);">${r.phone}</small>` : '';

          html += `
            <tr>
              <td><strong>${r.guest_name}</strong>${phone}</td>
              <td><span class="${tagClass}">${tagText}</span></td>
              <td style="text-align:center;"><strong>${guestsCount}</strong></td>
              <td style="max-width:140px;word-break:break-word;">${wishes}</td>
              <td><small style="color:var(--mayhen_muted);">${r.created_at_human || ''}</small></td>
            </tr>
          `;
        });

        html += `
            </tbody>
          </table>
        `;

        modalContainer.innerHTML = html;
      } catch (error) {
        modalContainer.innerHTML = `<p style="color:#b91c1c;padding:24px;text-align:center;">${error.message || 'Có lỗi xảy ra khi tải danh sách.'}</p>`;
      }
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
  const rsvpButtons = $$('[data-rsvp]');
  const rsvpNotice = document.getElementById('mayhen_rsvp_locked_notice');

  function lockRsvpButtons(selectedChoice) {
    rsvpButtons.forEach(btn => {
      btn.disabled = true;
      const btnChoice = btn.getAttribute('data-rsvp');
      if (btnChoice === selectedChoice) {
        btn.classList.add(prefix + 'rsvp_selected');
        btn.classList.remove(prefix + 'rsvp_disabled');
        btn.innerHTML = btnChoice === 'yes'
          ? '<span>✓</span>Đã xác nhận: Có tham dự'
          : '<span>✓</span>Đã phản hồi: Không thể tham dự';
      } else {
        btn.classList.add(prefix + 'rsvp_disabled');
        btn.classList.remove(prefix + 'rsvp_selected');
        btn.innerHTML = btnChoice === 'yes'
          ? '<span>♡</span>Có tham dự'
          : '<span>✉</span>Không thể tham dự';
      }
    });

    if (rsvpNotice) {
      rsvpNotice.style.display = 'inline-block';
    }
  }

  // Khởi tạo nếu đã có RSVP từ trước
  if (window.__MAYHEN_CURRENT_RSVP) {
    const prevStatus = window.__MAYHEN_CURRENT_RSVP.attending_status;
    lockRsvpButtons(prevStatus === 'attending' ? 'yes' : 'no');
  }

  rsvpButtons.forEach(button => {
    button.addEventListener('click', () => {
      if (window.__MAYHEN_IS_OWNER) {
        alert('Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi xác nhận tham dự cho chính mình.');
        return;
      }

      if (window.__MAYHEN_CURRENT_RSVP || button.disabled) {
        alert('Bạn đã xác nhận tham dự trước đó rồi và không thể thay đổi.');
        return;
      }

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

        if (checkProfanity(guestName) || (wishMessage && checkProfanity(wishMessage))) {
          alert('Vui lòng sử dụng ngôn từ lịch sự, trang trọng khi gửi thông tin tới cặp đôi.');
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

          // Cập nhật trạng thái đã RSVP và khóa các lựa chọn
          window.__MAYHEN_CURRENT_RSVP = {
            attending_status: isYes ? 'attending' : 'declined',
            guest_name: guestName
          };
          lockRsvpButtons(choice);

          // Nếu có gửi kèm lời chúc, cập nhật trạng thái My Wish
          if (wishMessage) {
            window.__MAYHEN_MY_WISH = { name: guestName, message: wishMessage };
            if (guestbookButton) {
              guestbookButton.textContent = 'Chỉnh sửa lời chúc của bạn';
            }
          }
        } catch (error) {
          alert(error.message || 'Không thể kết nối đến máy chủ. Vui lòng thử lại!');
          if (error.message && error.message.indexOf('không thể thay đổi') !== -1) {
            window.__MAYHEN_CURRENT_RSVP = { attending_status: 'attending' };
            lockRsvpButtons(choice);
            closeModal();
          }
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
