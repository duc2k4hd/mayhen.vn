/**
 * MAYHEN INVITATION - HỒNG BẢO HOÀNG GIA (ROYAL CRIMSON & ANTIQUE GOLD)
 * Prefix: mayhen_invitation_ladjihe38udhd_
 */

document.addEventListener('DOMContentLoaded', () => {

  const urlParams = new URLSearchParams(window.location.search);
  const isEmbed = urlParams.get('embed') === '1' || document.body.classList.contains('mayhen_invitation_ladjihe38udhd_embed_mode');

  /* ==========================================================================
     0. INTERSECTION OBSERVER CHO TOÀN BỘ BOX ANIMATION (SCROLL REVEAL & STAGGER)
     ========================================================================== */
  const animBoxes = document.querySelectorAll('.mayhen_invitation_ladjihe38udhd_box_anim');

  if (isEmbed) {
    // Trong chế độ nhúng Studio Editor, hiển thị tức thời toàn bộ section
    animBoxes.forEach(box => box.classList.add('is-revealed'));
    document.documentElement.classList.remove('mayhen_invitation_ladjihe38udhd_scroll_locked');
    document.body.classList.remove('mayhen_invitation_ladjihe38udhd_scroll_locked');
  } else if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
          // Stagger delay nhẹ tạo cảm giác chuyển động mượt mà
          setTimeout(() => {
            entry.target.classList.add('is-revealed');
          }, (index % 4) * 80);
          observer.unobserve(entry.target);
        }
      });
    }, {
      root: null,
      rootMargin: '0px 0px -40px 0px',
      threshold: 0.12
    });

    animBoxes.forEach(box => observer.observe(box));
  } else {
    // Fallback cho trình duyệt cũ
    animBoxes.forEach(box => box.classList.add('is-revealed'));
  }

  /* ==========================================================================
     1. PARSE URL PARAMETERS (GUEST NAME & STUDIO LIVE HOOKS)
     ========================================================================== */
  const explicitGuestParam = urlParams.get('to') || urlParams.get('name') || urlParams.get('khach');
  const wrapperEl = document.querySelector('.mayhen_invitation_ladjihe38udhd_wrapper');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const guestOpenNameEl = document.getElementById('mayhen_invitation_ladjihe38udhd_guest_open_name');
  const guestMainNameEl = document.getElementById('mayhen_invitation_ladjihe38udhd_guest_main_name');
  const rsvpNameInput = document.getElementById('rsvp_name');

  if (explicitGuestParam) {
    if (guestOpenNameEl) guestOpenNameEl.textContent = explicitGuestParam;
    if (guestMainNameEl) guestMainNameEl.textContent = explicitGuestParam;
    if (rsvpNameInput) rsvpNameInput.value = explicitGuestParam;
  }

  function setStatus(el, msg, isError = false) {
    if (!el) return;
    el.textContent = msg;
    el.classList.toggle('is-error', isError);
    el.classList.toggle('is-success', !isError && msg !== '');
  }

  /* ==========================================================================
     2. AUDIO & VINYL CONTROLLER
     ========================================================================== */
  const audio = document.getElementById('mayhen_invitation_ladjihe38udhd_audio');
  const vinylBtn = document.getElementById('mayhen_invitation_ladjihe38udhd_vinyl_btn');
  let isPlaying = false;

  function toggleMusic() {
    if (!audio) return;
    if (audio.paused) {
      audio.play().then(() => {
        isPlaying = true;
        vinylBtn.classList.add('playing');
      }).catch(err => {
        console.warn('Tự động phát âm thanh bị chặn bởi chính sách trình duyệt:', err);
      });
    } else {
      audio.pause();
      isPlaying = false;
      vinylBtn.classList.remove('playing');
    }
  }

  if (vinylBtn) {
    vinylBtn.addEventListener('click', toggleMusic);
  }

  /* ==========================================================================
     3. OPENING ENVELOPE MODAL
     ========================================================================== */
  const openingOverlay = document.getElementById('mayhen_invitation_ladjihe38udhd_opening');
  const openBtn = document.getElementById('mayhen_invitation_ladjihe38udhd_btn_open');
  const sealBtn = document.getElementById('mayhen_invitation_ladjihe38udhd_seal_btn');

  function openEnvelope() {
    if (openingOverlay) {
      openingOverlay.classList.add('closed');
      
      // Mở khóa cuộn trang
      document.documentElement.classList.remove('mayhen_invitation_ladjihe38udhd_scroll_locked');
      document.body.classList.remove('mayhen_invitation_ladjihe38udhd_scroll_locked');

      // Phát nhạc ngay khi khách bấm mở thiệp
      if (audio && audio.paused) {
        audio.play().then(() => {
          isPlaying = true;
          if (vinylBtn) vinylBtn.classList.add('playing');
        }).catch(() => {});
      }
      // Cuộn êm đến phần Hero
      const heroEl = document.getElementById('hero');
      if (heroEl) {
        heroEl.scrollIntoView({ behavior: 'smooth' });
      }
    }
  }

  if (openBtn) openBtn.addEventListener('click', openEnvelope);
  if (sealBtn) sealBtn.addEventListener('click', openEnvelope);

  /* ==========================================================================
     4. COUNTDOWN TIMER & DYNAMIC CALENDAR (DỮ LIỆU ĐỘNG THEO NGÀY CƯỚI)
     ========================================================================== */
  const countdownEl = document.getElementById('mayhen_invitation_ladjihe38udhd_countdown');
  const customDateStr = countdownEl ? countdownEl.getAttribute('data-wedding-date') : null;
  let weddingTimestamp = customDateStr
    ? new Date(customDateStr).getTime()
    : new Date('2026-12-20T10:00:00+07:00').getTime();

  function updateCountdown() {
    const now = Date.now();
    const distance = weddingTimestamp - now;

    const dEl = document.getElementById('cd_days');
    const hEl = document.getElementById('cd_hours');
    const mEl = document.getElementById('cd_minutes');
    const sEl = document.getElementById('cd_seconds');

    if (isNaN(distance) || distance <= 0) {
      if (dEl) dEl.textContent = '00';
      if (hEl) hEl.textContent = '00';
      if (mEl) mEl.textContent = '00';
      if (sEl) sEl.textContent = '00';
      return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    const pad = (n) => String(n).padStart(2, '0');

    if (dEl) dEl.textContent = pad(days);
    if (hEl) hEl.textContent = pad(hours);
    if (mEl) mEl.textContent = pad(minutes);
    if (sEl) sEl.textContent = pad(seconds);
  }

  window.updateMayhenCountdown = function (dateStr) {
    if (!dateStr) return;
    const iso = String(dateStr).includes('T') ? String(dateStr) : `${String(dateStr).trim()}T10:00:00+07:00`;
    const parsed = new Date(iso).getTime();
    if (!isNaN(parsed)) {
      weddingTimestamp = parsed;
      if (countdownEl) countdownEl.setAttribute('data-wedding-date', iso);
      updateCountdown();
    }
  };

  window.renderMayhenWeddingCalendar = function (dateStr) {
    if (!dateStr) return;
    const match = String(dateStr).match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!match) return;
    const year = Number(match[1]);
    const month = Number(match[2]);
    const day = Number(match[3]);
    const monthTitleEl = document.querySelector('[data-mayhen-calendar-month-title]');
    const labelEl = document.querySelector('[data-mayhen-calendar-label]');
    const gridEl = document.querySelector('[data-mayhen-calendar-grid]');
    if (monthTitleEl) monthTitleEl.textContent = `Tháng ${String(month).padStart(2, '0')} - ${year}`;
    const enMonths = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
    if (labelEl) labelEl.textContent = `${enMonths[month - 1] || ''} ${year}`;
    if (!gridEl) return;

    const leadingBlanks = new Date(year, month - 1, 1).getDay();
    const daysInMonth = new Date(year, month, 0).getDate();
    gridEl.innerHTML = '';
    ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'].forEach((d) => {
      const h = document.createElement('div');
      h.className = 'mayhen_invitation_ladjihe38udhd_cal_day_name';
      h.textContent = d;
      gridEl.appendChild(h);
    });
    for (let i = 0; i < leadingBlanks; i++) {
      const blank = document.createElement('div');
      blank.className = 'mayhen_invitation_ladjihe38udhd_cal_cell empty';
      gridEl.appendChild(blank);
    }
    for (let d = 1; d <= daysInMonth; d++) {
      const cell = document.createElement('div');
      if (d === day) {
        cell.className = 'mayhen_invitation_ladjihe38udhd_cal_cell special_day';
        cell.title = 'Ngày Hạnh Phúc';
        const numSpan = document.createElement('span');
        numSpan.textContent = String(d);
        const pinSpan = document.createElement('span');
        pinSpan.className = 'mayhen_invitation_ladjihe38udhd_heart_pin';
        pinSpan.textContent = '♥';
        cell.appendChild(numSpan);
        cell.appendChild(pinSpan);
      } else {
        cell.className = 'mayhen_invitation_ladjihe38udhd_cal_cell';
        cell.textContent = String(d);
      }
      gridEl.appendChild(cell);
    }
  };

  updateCountdown();
  setInterval(updateCountdown, 1000);

  /* ==========================================================================
     5. ADD TO GOOGLE CALENDAR (DYNAMIC)
     ========================================================================== */
  const addCalBtn = document.getElementById('mayhen_invitation_ladjihe38udhd_add_cal_btn');
  if (addCalBtn) {
    addCalBtn.addEventListener('click', () => {
      const gName = (document.querySelector('[data-sync-target="groom_name"]')?.textContent || 'Chú Rể').trim();
      const bName = (document.querySelector('[data-sync-target="bride_name"]')?.textContent || 'Cô Dâu').trim();
      const addr = (document.querySelector('[data-sync-target="event_party_address"]')?.textContent || 'Tư gia').trim();
      const venue = (document.querySelector('[data-sync-target="party_venue"]')?.textContent || '').trim();
      const targetDate = new Date(weddingTimestamp);
      const startDate = isNaN(targetDate.getTime()) ? new Date() : targetDate;
      const endDate = new Date(startDate.getTime() + 4 * 60 * 60 * 1000);
      const fmtUtc = (d) => d.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';

      const title = encodeURIComponent(`Lễ Thành Hôn - ${gName} & ${bName}`);
      const details = encodeURIComponent(`Trân trọng kính mời quý khách tới dự bữa tiệc mừng cưới của ${gName} & ${bName}!`);
      const location = encodeURIComponent(venue ? `${venue}, ${addr}` : addr);
      const dates = `${fmtUtc(startDate)}/${fmtUtc(endDate)}`;
      const calUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${dates}&details=${details}&location=${location}`;
      window.open(calUrl, '_blank', 'noopener');
    });
  }

  /* ==========================================================================
     6. SỔ LƯU BÚT KÝ TÊN VÀ VẼ CANVAS (SIGNATURE PAD)
     ========================================================================== */
  const openSignBtn = document.getElementById('mayhen_invitation_ladjihe38udhd_btn_open_sign');
  const canvasDrawer = document.getElementById('mayhen_invitation_ladjihe38udhd_canvas_drawer');
  const closeCanvasBtn = document.getElementById('mayhen_invitation_ladjihe38udhd_canvas_close');
  const canvas = document.getElementById('mayhen_invitation_ladjihe38udhd_sign_canvas');
  const clearBtn = document.getElementById('mayhen_invitation_ladjihe38udhd_btn_clear');
  const saveSignBtn = document.getElementById('mayhen_invitation_ladjihe38udhd_btn_save_sign');
  const signWall = document.getElementById('mayhen_invitation_ladjihe38udhd_sign_wall');
  const sigStatusEl = document.querySelector('[data-signature-status]');

  let ctx = null;
  let isDrawing = false;
  let hasDrawn = false;

  if (canvas) {
    ctx = canvas.getContext('2d');
    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#85182a'; // Đỏ rượu vang hoàng gia khi ký

    function getPos(e) {
      const rect = canvas.getBoundingClientRect();
      const clientX = e.touches ? e.touches[0].clientX : e.clientX;
      const clientY = e.touches ? e.touches[0].clientY : e.clientY;
      return {
        x: (clientX - rect.left) * (canvas.width / rect.width),
        y: (clientY - rect.top) * (canvas.height / rect.height)
      };
    }

    function startDraw(e) {
      isDrawing = true;
      hasDrawn = true;
      const pos = getPos(e);
      ctx.beginPath();
      ctx.moveTo(pos.x, pos.y);
      e.preventDefault();
    }

    function draw(e) {
      if (!isDrawing) return;
      const pos = getPos(e);
      ctx.lineTo(pos.x, pos.y);
      ctx.stroke();
      e.preventDefault();
    }

    function stopDraw() {
      isDrawing = false;
    }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDraw);

    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    window.addEventListener('touchend', stopDraw);
  }

  if (openSignBtn && canvasDrawer) {
    openSignBtn.addEventListener('click', () => {
      canvasDrawer.classList.toggle('active');
    });
  }

  if (closeCanvasBtn && canvasDrawer) {
    closeCanvasBtn.addEventListener('click', () => {
      canvasDrawer.classList.remove('active');
    });
  }

  if (clearBtn && ctx && canvas) {
    clearBtn.addEventListener('click', () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      hasDrawn = false;
    });
  }

  function appendSignaturePreview(dataUrl) {
    if (!signWall) return;
    const emptyHint = signWall.querySelector('[data-sig-empty]');
    if (emptyHint) emptyHint.remove();
    const item = document.createElement('div');
    item.className = 'mayhen_invitation_ladjihe38udhd_sign_item';
    const img = document.createElement('img');
    img.src = dataUrl;
    img.alt = 'Chữ ký khách mời';
    item.appendChild(img);
    signWall.prepend(item);
  }

  if (signWall) {
    const emptySpan = document.createElement('span');
    emptySpan.setAttribute('data-sig-empty', 'true');
    emptySpan.style.cssText = 'font-size:12px; color:#999; margin:auto;';
    emptySpan.textContent = 'Hãy là người tiếp theo ký tên lưu niệm trên thiệp nhé!';
    signWall.appendChild(emptySpan);
  }

  if (saveSignBtn && canvas) {
    saveSignBtn.addEventListener('click', async () => {
      if (!hasDrawn) {
        setStatus(sigStatusEl, 'Vui lòng ký tên vào khung trước khi lưu!', true);
        return;
      }
      const dataUrl = canvas.toDataURL('image/png');
      appendSignaturePreview(dataUrl);
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      hasDrawn = false;

      const sigEndpoint = wrapperEl ? (wrapperEl.getAttribute('data-signature-endpoint') || '').trim() : '';
      if (!sigEndpoint) {
        if (canvasDrawer) canvasDrawer.classList.remove('active');
        setStatus(sigStatusEl, 'Chữ ký đã hiển thị xem thử (sẽ tự động lưu khi khách mở link thiệp chính thức).');
        return;
      }

      const rawGuest = (rsvpNameInput && rsvpNameInput.value.trim()) ? rsvpNameInput.value.trim() : (wrapperEl.getAttribute('data-guest-name') || '');
      const signerName = (!rawGuest || rawGuest === 'Bạn và Gia Đình' || rawGuest === 'Quý Khách') ? 'Khách mời' : rawGuest;

      saveSignBtn.disabled = true;
      setStatus(sigStatusEl, 'Đang lưu chữ ký...');
      try {
        const res = await fetch(sigEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
          body: JSON.stringify({ guest_name: signerName, signature_data: dataUrl })
        });
        const result = await res.json();
        if (!res.ok || result.success !== true) {
          throw new Error(result.message || 'Không thể lưu chữ ký.');
        }
        setStatus(sigStatusEl, result.message || 'Đã lưu chữ ký kỷ niệm của bạn thành công!');
      } catch (err) {
        setStatus(sigStatusEl, err.message || 'Không thể kết nối máy chủ lưu chữ ký.', true);
      } finally {
        saveSignBtn.disabled = false;
      }
    });
  }

  /* ==========================================================================
     7. PHOTO GALLERY LIGHTBOX
     ========================================================================== */
  const galleryItems = document.querySelectorAll('.mayhen_invitation_ladjihe38udhd_gallery_item');
  const lightbox = document.getElementById('mayhen_invitation_ladjihe38udhd_lightbox');
  const lbImg = document.getElementById('mayhen_invitation_ladjihe38udhd_lb_img');
  const lbClose = document.getElementById('mayhen_invitation_ladjihe38udhd_lb_close');
  const lbPrev = document.getElementById('mayhen_invitation_ladjihe38udhd_lb_prev');
  const lbNext = document.getElementById('mayhen_invitation_ladjihe38udhd_lb_next');

  let currentImgIndex = 0;
  const imageSources = Array.from(galleryItems).map(item => item.getAttribute('data-full'));

  function showLightbox(index) {
    if (!lightbox || !lbImg) return;
    currentImgIndex = (index + imageSources.length) % imageSources.length;
    lbImg.src = imageSources[currentImgIndex];
    lightbox.classList.add('active');
  }

  galleryItems.forEach((item, index) => {
    item.addEventListener('click', () => showLightbox(index));
  });

  if (lbClose) {
    lbClose.addEventListener('click', () => lightbox.classList.remove('active'));
  }
  if (lbPrev) {
    lbPrev.addEventListener('click', () => showLightbox(currentImgIndex - 1));
  }
  if (lbNext) {
    lbNext.addEventListener('click', () => showLightbox(currentImgIndex + 1));
  }
  if (lightbox) {
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) lightbox.classList.remove('active');
    });
  }

  /* ==========================================================================
     8. RSVP FORM SUBMISSION (BACKEND API WITH CSRF)
     ========================================================================== */
  const rsvpForm = document.getElementById('mayhen_invitation_ladjihe38udhd_rsvp_form');
  const rsvpStatusEl = document.querySelector('[data-rsvp-status]');
  if (rsvpForm) {
    let isRsvpSubmitting = false;
    rsvpForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (isRsvpSubmitting) return;
      const formData = new FormData(rsvpForm);
      const guestNameVal = String(formData.get('name') || '').trim();
      const phoneVal = String(formData.get('phone') || '').trim();
      const attendanceRaw = String(formData.get('attendance') || 'có');
      const guestsNum = Number(formData.get('guests') || 1);
      const isAttending = attendanceRaw === 'có';

      if (!guestNameVal) {
        setStatus(rsvpStatusEl, 'Vui lòng nhập họ và tên của bạn.', true);
        return;
      }

      const rsvpEndpoint = wrapperEl ? (wrapperEl.getAttribute('data-rsvp-endpoint') || '').trim() : '';
      if (!rsvpEndpoint) {
        setStatus(rsvpStatusEl, 'Chưa thể gửi xác nhận. Vui lòng thử lại sau.', true);
        return;
      }

      isRsvpSubmitting = true;
      setStatus(rsvpStatusEl, 'Đang gửi xác nhận tham dự...');
      try {
        const res = await fetch(rsvpEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
          body: JSON.stringify({
            guest_name: guestNameVal,
            phone: phoneVal || null,
            attending_status: isAttending ? 'attending' : 'declined',
            adults_count: isAttending ? (guestsNum || 1) : 0,
            children_count: 0
          })
        });
        const result = await res.json();
        if (!res.ok || result.success !== true) {
          throw new Error(result.message || 'Không thể gửi xác nhận.');
        }
        setStatus(rsvpStatusEl, result.message || 'Cảm ơn bạn đã xác nhận tham dự!');
        rsvpForm.reset();
      } catch (err) {
        setStatus(rsvpStatusEl, err.message || 'Không thể kết nối máy chủ. Vui lòng thử lại.', true);
      } finally {
        isRsvpSubmitting = false;
      }
    });
  }

  /* ==========================================================================
     9. GUESTBOOK WISH SUBMISSION (BACKEND API WITH XSS DEFENSE)
     ========================================================================== */
  const wishForm = document.getElementById('mayhen_invitation_ladjihe38udhd_wish_form');
  const wishList = document.getElementById('mayhen_invitation_ladjihe38udhd_wish_list');
  const wishStatusEl = document.querySelector('[data-wish-status]');

  if (wishForm && wishList) {
    let isWishSubmitting = false;
    wishForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (isWishSubmitting) return;
      const nameInput = document.getElementById('wish_name');
      const msgInput = document.getElementById('wish_content');
      const name = (nameInput?.value || '').trim();
      const msg = (msgInput?.value || '').trim();

      if (!name || !msg) {
        setStatus(wishStatusEl, 'Vui lòng nhập đầy đủ tên và lời chúc của bạn.', true);
        return;
      }

      const wishEndpoint = wrapperEl ? (wrapperEl.getAttribute('data-wish-endpoint') || '').trim() : '';
      if (!wishEndpoint) {
        setStatus(wishStatusEl, 'Chưa thể gửi lời chúc. Vui lòng thử lại sau.', true);
        return;
      }

      isWishSubmitting = true;
      setStatus(wishStatusEl, 'Đang gửi lời chúc...');
      try {
        const res = await fetch(wishEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
          body: JSON.stringify({ name: name, message: msg })
        });
        const result = await res.json();
        if (!res.ok || result.success !== true) {
          throw new Error(result.message || 'Không thể gửi lời chúc.');
        }

        const card = document.createElement('div');
        card.className = 'mayhen_invitation_ladjihe38udhd_wish_card mayhen_invitation_wish_item mayhen_invitation_ladjihe38udhd_box_anim is-revealed';
        const authorWrap = document.createElement('div');
        authorWrap.className = 'mayhen_invitation_ladjihe38udhd_wish_author';
        const avatarSpan = document.createElement('span');
        avatarSpan.className = 'mayhen_invitation_ladjihe38udhd_avatar';
        avatarSpan.textContent = name.charAt(0).toUpperCase() || 'K';
        const metaWrap = document.createElement('div');
        const strongName = document.createElement('strong');
        strongName.textContent = name;
        const timeEl = document.createElement('time');
        timeEl.textContent = 'Vừa xong';
        metaWrap.appendChild(strongName);
        metaWrap.appendChild(timeEl);
        authorWrap.appendChild(avatarSpan);
        authorWrap.appendChild(metaWrap);

        const msgP = document.createElement('p');
        msgP.className = 'mayhen_invitation_ladjihe38udhd_wish_msg';
        msgP.textContent = msg;

        card.appendChild(authorWrap);
        card.appendChild(msgP);
        wishList.prepend(card);

        if (nameInput) nameInput.value = '';
        if (msgInput) msgInput.value = '';
        setStatus(wishStatusEl, result.message || 'Đã gửi lời chúc phúc ngọt ngào của bạn thành công!');
      } catch (err) {
        setStatus(wishStatusEl, err.message || 'Không thể kết nối máy chủ lưu lời chúc.', true);
      } finally {
        isWishSubmitting = false;
      }
    });
  }

  /* ==========================================================================
     10. COPY ACCOUNT NUMBER (NO ALERT)
     ========================================================================== */
  const copyBtns = document.querySelectorAll('.mayhen_invitation_ladjihe38udhd_btn_copy');
  copyBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const textToCopy = btn.getAttribute('data-copy') || '';
      const original = btn.textContent;
      const done = () => {
        btn.textContent = 'Đã sao chép';
        setTimeout(() => { btn.textContent = original; }, 2000);
      };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(textToCopy).then(done).catch(done);
      } else {
        done();
      }
    });
  });

  /* ==========================================================================
     11. DOCK QUICK NAVIGATION BUTTONS
     ========================================================================== */
  const dockBtns = document.querySelectorAll('.mayhen_invitation_ladjihe38udhd_dock_btn');
  dockBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-target');
      const targetEl = document.querySelector(targetId);
      if (targetEl) {
        targetEl.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

});
