/**
 * MAYHEN INVITATION - HỒNG BẢO HOÀNG GIA (ROYAL CRIMSON & ANTIQUE GOLD)
 * Prefix: mayhen_invitation_ladjihe38udhd_
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ==========================================================================
     0. INTERSECTION OBSERVER CHO TOÀN BỘ BOX ANIMATION (SCROLL REVEAL & STAGGER)
     ========================================================================== */
  const animBoxes = document.querySelectorAll('.mayhen_invitation_ladjihe38udhd_box_anim');

  if ('IntersectionObserver' in window) {
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
     1. PARSE URL PARAMETERS (GUEST NAME & AUTO-SCROLL)
     ========================================================================== */
  const urlParams = new URLSearchParams(window.location.search);
  const guestName = urlParams.get('to') || urlParams.get('name') || urlParams.get('khach') || 'Bạn và Gia Đình';

  const guestOpenNameEl = document.getElementById('mayhen_invitation_ladjihe38udhd_guest_open_name');
  const guestMainNameEl = document.getElementById('mayhen_invitation_ladjihe38udhd_guest_main_name');
  const rsvpNameInput = document.getElementById('rsvp_name');

  if (guestOpenNameEl) guestOpenNameEl.textContent = guestName;
  if (guestMainNameEl) guestMainNameEl.textContent = guestName;
  if (rsvpNameInput && guestName !== 'Bạn và Gia Đình') rsvpNameInput.value = guestName;

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
     4. COUNTDOWN TIMER (20 THÁNG 12 NĂM 2025 10:00:00)
     ========================================================================== */
  const weddingDate = new Date('2025-12-20T10:00:00+07:00').getTime();

  function updateCountdown() {
    const now = new Date().getTime();
    const distance = weddingDate - now;

    if (distance <= 0) {
      document.getElementById('cd_days').textContent = '00';
      document.getElementById('cd_hours').textContent = '00';
      document.getElementById('cd_minutes').textContent = '00';
      document.getElementById('cd_seconds').textContent = '00';
      return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    const pad = (n) => String(n).padStart(2, '0');

    const dEl = document.getElementById('cd_days');
    const hEl = document.getElementById('cd_hours');
    const mEl = document.getElementById('cd_minutes');
    const sEl = document.getElementById('cd_seconds');

    if (dEl) dEl.textContent = pad(days);
    if (hEl) hEl.textContent = pad(hours);
    if (mEl) mEl.textContent = pad(minutes);
    if (sEl) sEl.textContent = pad(seconds);
  }

  updateCountdown();
  setInterval(updateCountdown, 1000);

  /* ==========================================================================
     5. ADD TO GOOGLE CALENDAR
     ========================================================================== */
  const addCalBtn = document.getElementById('mayhen_invitation_ladjihe38udhd_add_cal_btn');
  if (addCalBtn) {
    addCalBtn.addEventListener('click', () => {
      const title = encodeURIComponent('Lễ Thành Hôn - Hà Kiên & Phạm Xôn');
      const details = encodeURIComponent('Trân trọng kính mời quý khách tới dự bữa tiệc mừng cưới của chúng mình!');
      const location = encodeURIComponent('423 An Dương Vương, Phú Thượng, Tây Hồ, Hà Nội');
      const dates = '20251220T030000Z/20251220T070000Z'; // UTC time
      const calUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${dates}&details=${details}&location=${location}`;
      window.open(calUrl, '_blank');
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

  // Load saved signatures from localStorage
  function loadSignatures() {
    if (!signWall) return;
    const saved = JSON.parse(localStorage.getItem('mayhen_signatures') || '[]');
    signWall.innerHTML = '';
    if (saved.length === 0) {
      signWall.innerHTML = '<span style="font-size:12px; color:#999; margin:auto;">Chưa có chữ ký nào. Hãy là người đầu tiên ký tên nhé!</span>';
      return;
    }
    saved.forEach(dataUrl => {
      const item = document.createElement('div');
      item.className = 'mayhen_invitation_ladjihe38udhd_sign_item';
      const img = document.createElement('img');
      img.src = dataUrl;
      img.alt = 'Chữ ký khách';
      item.appendChild(img);
      signWall.appendChild(item);
    });
  }

  loadSignatures();

  if (saveSignBtn && canvas) {
    saveSignBtn.addEventListener('click', () => {
      if (!hasDrawn) {
        alert('Vui lòng ký tên vào khung trước khi lưu!');
        return;
      }
      const dataUrl = canvas.toDataURL('image/png');
      const saved = JSON.parse(localStorage.getItem('mayhen_signatures') || '[]');
      saved.unshift(dataUrl);
      localStorage.setItem('mayhen_signatures', JSON.stringify(saved.slice(0, 30)));
      loadSignatures();
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      hasDrawn = false;
      if (canvasDrawer) canvasDrawer.classList.remove('active');
      alert('Đã lưu chữ ký kỷ niệm của bạn thành công!');
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

  function getGalleryItemSrc(item) {
    if (!item) return '';
    const full = item.getAttribute('data-full');
    if (full && (full.startsWith('http://') || full.startsWith('https://') || full.startsWith('/'))) {
      return full;
    }
    const innerImg = item.querySelector('img');
    return innerImg ? (innerImg.currentSrc || innerImg.src || innerImg.getAttribute('src') || '') : (full || '');
  }

  function showLightbox(index) {
    if (!lightbox || !lbImg || galleryItems.length === 0) return;
    currentImgIndex = (index + galleryItems.length) % galleryItems.length;
    const targetItem = galleryItems[currentImgIndex];
    const src = getGalleryItemSrc(targetItem);
    if (src) {
      lbImg.src = src;
      lightbox.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeLightbox() {
    if (!lightbox) return;
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
  }

  galleryItems.forEach((item, index) => {
    item.addEventListener('click', () => showLightbox(index));
  });

  if (lbClose) {
    lbClose.addEventListener('click', closeLightbox);
  }
  if (lbPrev) {
    lbPrev.addEventListener('click', () => showLightbox(currentImgIndex - 1));
  }
  if (lbNext) {
    lbNext.addEventListener('click', () => showLightbox(currentImgIndex + 1));
  }
  if (lightbox) {
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) closeLightbox();
    });
  }

  window.addEventListener('keydown', (e) => {
    if (!lightbox || !lightbox.classList.contains('active')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') showLightbox(currentImgIndex - 1);
    if (e.key === 'ArrowRight') showLightbox(currentImgIndex + 1);
  });

  /* ==========================================================================
     8. RSVP FORM SUBMISSION (LOCAL STORAGE CACHE)
     ========================================================================== */
  const rsvpForm = document.getElementById('mayhen_invitation_ladjihe38udhd_rsvp_form');
  if (rsvpForm) {
    rsvpForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const formData = new FormData(rsvpForm);
      const data = {
        name: formData.get('name'),
        phone: formData.get('phone'),
        side: formData.get('side'),
        attendance: formData.get('attendance'),
        guests: formData.get('guests'),
        time: new Date().toISOString()
      };
      
      const rsvpList = JSON.parse(localStorage.getItem('mayhen_rsvps') || '[]');
      rsvpList.unshift(data);
      localStorage.setItem('mayhen_rsvps', JSON.stringify(rsvpList));

      alert(`Cảm ơn bạn ${data.name} đã xác nhận tham dự! Đôi uyên ương rất vinh hạnh được đón tiếp bạn!`);
      rsvpForm.reset();
    });
  }

  /* ==========================================================================
     9. GUESTBOOK WISH SUBMISSION
     ========================================================================== */
  const wishForm = document.getElementById('mayhen_invitation_ladjihe38udhd_wish_form');
  const wishList = document.getElementById('mayhen_invitation_ladjihe38udhd_wish_list');

  if (wishForm && wishList) {
    wishForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const nameInput = document.getElementById('wish_name');
      const msgInput = document.getElementById('wish_content');
      const name = nameInput.value.trim();
      const msg = msgInput.value.trim();

      if (!name || !msg) return;

      const card = document.createElement('div');
      card.className = 'mayhen_invitation_ladjihe38udhd_wish_card mayhen_invitation_ladjihe38udhd_box_anim is-revealed';
      const initial = name.charAt(0).toUpperCase();

      card.innerHTML = `
        <div class="mayhen_invitation_ladjihe38udhd_wish_author">
          <span class="mayhen_invitation_ladjihe38udhd_avatar">${initial}</span>
          <div>
            <strong>${name}</strong>
            <time>Vừa xong</time>
          </div>
        </div>
        <p class="mayhen_invitation_ladjihe38udhd_wish_msg">${msg}</p>
      `;

      wishList.prepend(card);

      const savedWishes = JSON.parse(localStorage.getItem('mayhen_wishes') || '[]');
      savedWishes.unshift({ name, msg, time: 'Vừa xong' });
      localStorage.setItem('mayhen_wishes', JSON.stringify(savedWishes.slice(0, 50)));

      nameInput.value = '';
      msgInput.value = '';
      alert('Đã gửi lời chúc phúc ngọt ngào của bạn thành công!');
    });
  }

  /* ==========================================================================
     10. COPY ACCOUNT NUMBER
     ========================================================================== */
  const copyBtns = document.querySelectorAll('.mayhen_invitation_ladjihe38udhd_btn_copy');
  copyBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const textToCopy = btn.getAttribute('data-copy');
      if (navigator.clipboard) {
        navigator.clipboard.writeText(textToCopy).then(() => {
          const original = btn.textContent;
          btn.textContent = 'Đã sao chép';
          setTimeout(() => btn.textContent = original, 2000);
        });
      } else {
        alert('Đã sao chép: ' + textToCopy);
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
