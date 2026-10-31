/**
 * MAYHEN INVITATION WEDDING 18 - HẮC BẠCH TRAO DUYÊN (BLACK & WHITE EDITORIAL)
 * Pure Vanilla JavaScript (Native ES6+)
 * Không dùng jQuery, không phụ thuộc thư viện ngoài
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ==========================================================================
     1. KHÓA CUỘN TRANG & MỞ PHONG BÌ HOÀNG GIA (POPUP8)
     ========================================================================== */
  const popup8 = document.getElementById('POPUP8');
  const btnOpen18 = document.getElementById('BUTTON18');
  const audio = document.getElementById('mayhen_invitation_ladjihe38udhd_audio');
  const vinylBtn = document.getElementById('mayhen_invitation_ladjihe38udhd_vinyl_btn');
  const vinylWrap = document.querySelector('.mayhen_invitation_ladjihe38udhd_vinyl_wrap');
  const soundwave = document.querySelector('.mayhen_invitation_ladjihe38udhd_soundwave');
  const scrollDownBtn = document.getElementById('GROUP138');
  let isPlaying = false;

  function toggleAudio(play) {
    if (!audio) return;
    if (play === true || (play === undefined && audio.paused)) {
      audio.play().then(() => {
        isPlaying = true;
        if (vinylWrap) vinylWrap.classList.add('spinning');
        if (soundwave) soundwave.classList.add('active');
      }).catch(() => {
        console.log('Audio autoplay prevented by browser');
      });
    } else {
      audio.pause();
      isPlaying = false;
      if (vinylWrap) vinylWrap.classList.remove('spinning');
      if (soundwave) soundwave.classList.remove('active');
    }
  }

  function unlockAndOpen() {
    if (popup8) {
      popup8.classList.add('closed');
    }
    // Mở khóa cuộn trang
    document.documentElement.classList.remove('mayhen_invitation_ladjihe38udhd_scroll_locked');
    document.body.classList.remove('mayhen_invitation_ladjihe38udhd_scroll_locked');

    // Tự động phát nhạc
    toggleAudio(true);

    // Kích hoạt reveal các box ở màn hình đầu tiên
    setTimeout(() => {
      triggerInitialBoxes();
    }, 400);
  }

  if (btnOpen18) {
    btnOpen18.addEventListener('click', (e) => {
      e.stopPropagation();
      unlockAndOpen();
    });
  }

  // Cho phép bấm trực tiếp vào thiệp để mở
  if (popup8) {
    popup8.addEventListener('click', (e) => {
      if (!e.target.closest('a') && !e.target.closest('button')) {
        unlockAndOpen();
      }
    });
  }

  if (vinylBtn) {
    vinylBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      toggleAudio();
    });
  }

  // Cung cấp hàm toàn cục cho Editor Bridge cập nhật nhạc
  window.updateMayhenMusic = function(newUrl) {
    if (!audio) return;
    audio.src = newUrl;
    audio.load();
    if (isPlaying) {
      audio.play().catch(() => {});
    }
  };

  /* ==========================================================================
     2. ĐIỀU KHIỂN NÚT "KÉO XUỐNG" (#GROUP138)
     ========================================================================== */
  if (scrollDownBtn) {
    // Ẩn nút khi cuộn xuống quá màn hình đầu để không che các nội dung khác
    window.addEventListener('scroll', () => {
      if (window.scrollY > 150) {
        scrollDownBtn.classList.add('scrolled-out');
      } else {
        scrollDownBtn.classList.remove('scrolled-out');
      }
    }, { passive: true });

    scrollDownBtn.addEventListener('click', () => {
      const nextSection = document.getElementById('SECTION22') || document.getElementById('SECTION24');
      if (nextSection) {
        nextSection.scrollIntoView({ behavior: 'smooth' });
      } else {
        window.scrollBy({ top: window.innerHeight * 0.8, behavior: 'smooth' });
      }
    });
  }

  /* ==========================================================================
     3. TỰ ĐỘNG SCALE CHUẨN XÁC TRÊN MÀN HÌNH DI ĐỘNG (< 420px)
     ========================================================================== */
  function autoScaleMobile() {
    const screenWidth = window.innerWidth || document.documentElement.clientWidth;
    const wraper = document.querySelector('.ladi-wraper');
    if (!wraper) return;

    if (screenWidth < 420) {
      const scale = screenWidth / 420;
      wraper.style.transform = `scale(${scale})`;
      wraper.style.transformOrigin = 'top center';
      wraper.style.width = '420px';
      wraper.style.marginLeft = 'auto';
      wraper.style.marginRight = 'auto';

      // Bù chiều cao để footer không bị hở đáy
      const naturalHeight = wraper.scrollHeight || wraper.offsetHeight;
      if (naturalHeight > 0) {
        document.body.style.minHeight = `${Math.ceil(naturalHeight * scale)}px`;
      }
    } else {
      wraper.style.transform = '';
      wraper.style.transformOrigin = '';
      wraper.style.width = '';
      wraper.style.marginLeft = '';
      wraper.style.marginRight = '';
      document.body.style.minHeight = '';
    }
  }

  window.addEventListener('resize', autoScaleMobile);
  autoScaleMobile();

  /* ==========================================================================
     4. GÁN TÊN KHÁCH MỜI TỪ URL PARAM (?to= hoặc ?guest=)
     ========================================================================== */
  const urlParams = new URLSearchParams(window.location.search);
  const guestName = urlParams.get('to') || urlParams.get('guest');
  if (guestName) {
    const guestEl = document.querySelector('#GROUP140 p') || document.querySelector('#HEADLINE336 h3');
    if (guestEl) {
      guestEl.innerHTML = `<strong style="font-size:1.1em; color:#fff;">${decodeURIComponent(guestName)}</strong>`;
    }
  }

  /* ==========================================================================
     5. SCROLL REVEAL BẰNG INTERSECTION OBSERVER
     ========================================================================== */
  const animElements = document.querySelectorAll('.mayhen_invitation_ladjihe38udhd_box_anim');

  function triggerInitialBoxes() {
    animElements.forEach(el => {
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight + 50) {
        el.classList.add('mayhen_invitation_ladjihe38udhd_revealed');
      }
    });
  }

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('mayhen_invitation_ladjihe38udhd_revealed');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.08,
      rootMargin: '0px 0px 80px 0px'
    });

    animElements.forEach(el => {
      observer.observe(el);
    });
  } else {
    animElements.forEach(el => el.classList.add('mayhen_invitation_ladjihe38udhd_revealed'));
  }

  /* ==========================================================================
     6. COUNTDOWN TIMER (SECTION 22)
     ========================================================================== */
  const targetDateStr = document.body.getAttribute('data-target-date') || '2025-10-09T16:00:00';
  const targetTime = Number(targetDateStr) || new Date(targetDateStr).getTime() || new Date(2025, 9, 9, 16, 0, 0).getTime();

  function updateCountdown() {
    const now = Date.now();
    const diff = targetTime - now;

    const daysEl = document.querySelector('#COUNTDOWN_ITEM1 .ladi-countdown-text span') || document.getElementById('countdown_days');
    const hoursEl = document.querySelector('#COUNTDOWN_ITEM2 .ladi-countdown-text span') || document.getElementById('countdown_hours');
    const minsEl = document.querySelector('#COUNTDOWN_ITEM3 .ladi-countdown-text span') || document.getElementById('countdown_minutes');
    const secsEl = document.querySelector('#COUNTDOWN_ITEM4 .ladi-countdown-text span') || document.getElementById('countdown_seconds');

    if (diff <= 0) {
      if (daysEl) daysEl.textContent = '00';
      if (hoursEl) hoursEl.textContent = '00';
      if (minsEl) minsEl.textContent = '00';
      if (secsEl) secsEl.textContent = '00';
      return;
    }

    const d = Math.floor(diff / (1000 * 60 * 60 * 24));
    const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
    const m = Math.floor((diff / 1000 / 60) % 60);
    const s = Math.floor((diff / 1000) % 60);

    if (daysEl) daysEl.textContent = String(d).padStart(2, '0');
    if (hoursEl) hoursEl.textContent = String(h).padStart(2, '0');
    if (minsEl) minsEl.textContent = String(m).padStart(2, '0');
    if (secsEl) secsEl.textContent = String(s).padStart(2, '0');
  }

  setInterval(updateCountdown, 1000);
  updateCountdown();

  /* ==========================================================================
     7. ALBUM ẢNH CƯỚI & POPUP LIGHTBOX PHÓNG TO SẮC NÉT
     ========================================================================== */
  const lightboxModal = document.getElementById('mayhen_hacbach2_lightbox');
  const lightboxImg = document.getElementById('mayhen_hacbach2_lightbox_img');
  const lightboxIdx = document.getElementById('mayhen_hacbach2_lightbox_idx');
  const lightboxTotal = document.getElementById('mayhen_hacbach2_lightbox_total');
  const btnLbClose = document.querySelector('.mayhen_invitation_ladjihe38udhd_lightbox_close');
  const btnLbPrev = document.querySelector('.mayhen_invitation_ladjihe38udhd_lightbox_nav.prev');
  const btnLbNext = document.querySelector('.mayhen_invitation_ladjihe38udhd_lightbox_nav.next');
  const lbBackdrop = document.querySelector('.mayhen_invitation_ladjihe38udhd_lightbox_backdrop');

  // Thu thập danh sách ảnh cưới trong SECTION20 và SECTION8
  const albumElements = Array.from(document.querySelectorAll('#SECTION20 .ladi-image, #SECTION8 .ladi-image'));
  const galleryUrls = [];

  function extractImageUrl(el) {
    const bgEl = el.querySelector('.ladi-image-background') || el;
    const computed = window.getComputedStyle(bgEl);
    let bg = computed.backgroundImage;
    if (bg && bg !== 'none') {
      const match = bg.match(/url\(['"]?(.*?)['"]?\)/);
      if (match && match[1]) {
        return match[1];
      }
    }
    const imgTag = el.querySelector('img');
    if (imgTag && imgTag.src) {
      return imgTag.src;
    }
    return '';
  }

  // Lọc và lập bản đồ các ảnh hợp lệ
  const validAlbumItems = [];
  albumElements.forEach(el => {
    const url = extractImageUrl(el);
    if (url && !url.includes('.svg') && !galleryUrls.includes(url)) {
      galleryUrls.push(url);
      validAlbumItems.push({ element: el, url: url, index: galleryUrls.length - 1 });
    }
  });

  let currentPhotoIndex = 0;

  function showPhoto(idx) {
    if (!galleryUrls.length) return;
    if (idx < 0) idx = galleryUrls.length - 1;
    if (idx >= galleryUrls.length) idx = 0;
    currentPhotoIndex = idx;

    if (lightboxImg) {
      lightboxImg.style.opacity = '0';
      lightboxImg.style.transform = 'scale(0.96)';
      setTimeout(() => {
        lightboxImg.src = galleryUrls[currentPhotoIndex];
        lightboxImg.style.opacity = '1';
        lightboxImg.style.transform = 'scale(1)';
      }, 150);
    }

    if (lightboxIdx) lightboxIdx.textContent = String(currentPhotoIndex + 1);
    if (lightboxTotal) lightboxTotal.textContent = String(galleryUrls.length);
  }

  function openLightbox(idx) {
    if (!lightboxModal) return;
    showPhoto(idx);
    lightboxModal.hidden = false;
    // Trigger transition
    requestAnimationFrame(() => {
      lightboxModal.classList.add('active');
    });
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lightboxModal) return;
    lightboxModal.classList.remove('active');
    setTimeout(() => {
      lightboxModal.hidden = true;
      document.body.style.overflow = '';
    }, 350);
  }

  // Gắn sự kiện click mở lightbox cho từng item ảnh
  validAlbumItems.forEach(item => {
    item.element.style.cursor = 'zoom-in';
    item.element.addEventListener('click', (e) => {
      e.stopPropagation();
      openLightbox(item.index);
    });
  });

  if (btnLbClose) {
    btnLbClose.addEventListener('click', (e) => {
      e.stopPropagation();
      closeLightbox();
    });
  }

  if (lbBackdrop) {
    lbBackdrop.addEventListener('click', closeLightbox);
  }

  if (btnLbPrev) {
    btnLbPrev.addEventListener('click', (e) => {
      e.stopPropagation();
      showPhoto(currentPhotoIndex - 1);
    });
  }

  if (btnLbNext) {
    btnLbNext.addEventListener('click', (e) => {
      e.stopPropagation();
      showPhoto(currentPhotoIndex + 1);
    });
  }

  // Điều hướng bằng bàn phím
  window.addEventListener('keydown', (e) => {
    if (!lightboxModal || lightboxModal.hidden || !lightboxModal.classList.contains('active')) return;
    if (e.key === 'Escape') {
      closeLightbox();
    } else if (e.key === 'ArrowLeft') {
      showPhoto(currentPhotoIndex - 1);
    } else if (e.key === 'ArrowRight') {
      showPhoto(currentPhotoIndex + 1);
    }
  });

  // Hỗ trợ vuốt chạm cảm ứng (Touch swipe) trên thiết bị di động
  let touchStartX = 0;
  let touchEndX = 0;

  if (lightboxModal) {
    lightboxModal.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    lightboxModal.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      const diffX = touchEndX - touchStartX;
      if (Math.abs(diffX) > 45) {
        if (diffX > 0) {
          showPhoto(currentPhotoIndex - 1); // Vuốt sang phải -> ảnh trước
        } else {
          showPhoto(currentPhotoIndex + 1); // Vuốt sang trái -> ảnh sau
        }
      }
    }, { passive: true });
  }

  /* ==========================================================================
     8. CÁC NÚT ĐIỀU HƯỚNG VÀ POPUP (BẢN ĐỒ, LỜI CHÚC, MỪNG CƯỚI)
     ========================================================================== */
  document.querySelectorAll('[data-action="true"]').forEach(btn => {
    btn.style.cursor = 'pointer';
  });


  /* ==========================================================================
     9. CHỨC NĂNG SỔ LƯU BÚT & KÝ TÊN CHÚC PHÚC (SIGNATURE GUESTBOOK)
     ========================================================================== */
  const canvas = document.getElementById('signatureCanvas');
  const ctx = canvas ? canvas.getContext('2d') : null;
  const headline15 = document.getElementById('HEADLINE15');
  const btnSignOpen = document.getElementById('BUTTON2');
  const btnSignClear = document.getElementById('BUTTON3');
  const btnSignPreview = document.getElementById('BUTTON4');
  const btnSignSave = document.getElementById('BUTTON5');
  const signListEl = document.getElementById('mayhen_hacbach2_signature_list');
  const deleteDialog = document.getElementById('deleteDialog');
  const btnConfirmDelete = document.getElementById('confirmDelete');
  const btnCancelDelete = document.getElementById('cancelDelete');
  const toastEl = document.getElementById('mayhen_hacbach2_toast');
  const isPreviewMode = window.__MAYHEN_IS_PREVIEW || window.location.pathname.includes('/xem-thu/');

  let isDrawing = false;
  let hasDrawn = false;
  let toastTimer = null;

  function showToast(msg) {
    if (!toastEl) {
      alert(msg);
      return;
    }
    toastEl.innerHTML = msg;
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toastEl.classList.remove('show');
    }, 4000);
  }

  // Khởi tạo Canvas vẽ chữ ký nét mực vàng hoàng gia Antique Gold
  if (canvas && ctx) {
    ctx.strokeStyle = '#e6c88b';
    ctx.lineWidth = 3.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    function getPointerPos(e) {
      const rect = canvas.getBoundingClientRect();
      const scaleX = canvas.width / rect.width;
      const scaleY = canvas.height / rect.height;
      const clientX = e.touches ? e.touches[0].clientX : e.clientX;
      const clientY = e.touches ? e.touches[0].clientY : e.clientY;
      return {
        x: (clientX - rect.left) * scaleX,
        y: (clientY - rect.top) * scaleY
      };
    }

    function startDraw(e) {
      isDrawing = true;
      hasDrawn = true;
      if (headline15) headline15.classList.add('drawing-active');
      const pos = getPointerPos(e);
      ctx.beginPath();
      ctx.moveTo(pos.x, pos.y);
      if (e.cancelable) e.preventDefault();
    }

    function draw(e) {
      if (!isDrawing) return;
      const pos = getPointerPos(e);
      ctx.lineTo(pos.x, pos.y);
      ctx.stroke();
      if (e.cancelable) e.preventDefault();
    }

    function stopDraw() {
      isDrawing = false;
    }

    // Chuột trên Desktop
    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDraw);
    canvas.addEventListener('mouseleave', stopDraw);

    // Chạm cảm ứng trên Mobile/Tablet
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    window.addEventListener('touchend', stopDraw);
  }

  // Nút "NHẤN ĐỂ KÝ TÊN" (#BUTTON2)
  if (btnSignOpen) {
    btnSignOpen.addEventListener('click', (e) => {
      e.stopPropagation();
      const group13 = document.getElementById('GROUP13') || canvas;
      if (group13) {
        group13.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }

  function clearCanvas() {
    if (!ctx || !canvas) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    hasDrawn = false;
    if (headline15) headline15.classList.remove('drawing-active');
  }

  // Nút "Xóa" (#BUTTON3) -> Mở dialog xác nhận trang trọng
  if (btnSignClear) {
    btnSignClear.addEventListener('click', (e) => {
      e.stopPropagation();
      if (!hasDrawn) {
        showToast('Khung ký tên hiện đang trống.');
        return;
      }
      if (deleteDialog) {
        deleteDialog.hidden = false;
      } else {
        clearCanvas();
      }
    });
  }

  if (btnConfirmDelete) {
    btnConfirmDelete.addEventListener('click', () => {
      clearCanvas();
      if (deleteDialog) deleteDialog.hidden = true;
      showToast('Đã xóa nét vẽ chữ ký.');
    });
  }

  if (btnCancelDelete) {
    btnCancelDelete.addEventListener('click', () => {
      if (deleteDialog) deleteDialog.hidden = true;
    });
  }

  // Nút "Xem thử" (#BUTTON4)
  if (btnSignPreview) {
    btnSignPreview.addEventListener('click', (e) => {
      e.stopPropagation();
      if (!hasDrawn) {
        showToast('Vui lòng ký tên vào khung trước khi xem thử!');
        return;
      }
      showToast('✨ Nét chữ ký của bạn trông rất thanh lịch và trang trọng!');
      const previewWall = document.getElementById('previewContainer');
      if (previewWall) {
        previewWall.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }

  // Tạo thẻ hiển thị chữ ký trên Bức Tường Lưu Bút
  function renderSignatureCard(dataUrl, signerName, dateStr) {
    const card = document.createElement('div');
    card.className = 'mayhen_hacbach2_sign_card';

    const img = document.createElement('img');
    img.src = dataUrl;
    img.alt = signerName || 'Chữ ký khách mời';
    img.loading = 'lazy';

    const meta = document.createElement('div');
    meta.className = 'sign_meta';
    meta.innerHTML = `<span>${signerName || 'Khách quý'}</span><span>${dateStr || 'Vừa xong'}</span>`;

    card.appendChild(img);
    card.appendChild(meta);
    return card;
  }

  // Khởi tạo các chữ ký mẫu demo nghệ thuật cho trang xem thử
  function createDemoSignatures() {
    const demos = [
      { name: 'Gia đình Bác Thành', wish: 'Chúc hai cháu trăm năm hạnh phúc!', time: '14:20 - 09/10' },
      { name: 'Bạn thân Gia Bảo', wish: 'Mãi mãi bên nhau hạnh phúc nhé!', time: '15:45 - 09/10' },
      { name: 'Chị Thuỳ Trang', wish: 'Hôn nhân viên mãn, bạc đầu nghĩa phu thê!', time: '16:10 - 09/10' }
    ];

    // Vẽ chữ ký mẫu lên canvas tạm
    const tempCanvas = document.createElement('canvas');
    tempCanvas.width = 300;
    tempCanvas.height = 100;
    const tCtx = tempCanvas.getContext('2d');

    return demos.map((d, i) => {
      tCtx.clearRect(0, 0, tempCanvas.width, tempCanvas.height);
      tCtx.font = 'italic 28px Cormorant Garamond, "Brush Script MT", cursive';
      tCtx.fillStyle = '#e6c88b';
      tCtx.textAlign = 'center';
      tCtx.textBaseline = 'middle';
      tCtx.fillText(d.name, tempCanvas.width / 2, tempCanvas.height / 2 - 5);

      // Thêm nét gạch chân nghệ thuật
      tCtx.strokeStyle = 'rgba(212, 175, 55, 0.6)';
      tCtx.lineWidth = 2;
      tCtx.beginPath();
      tCtx.moveTo(40, tempCanvas.height / 2 + 18);
      tCtx.bezierCurveTo(100, tempCanvas.height / 2 + 28, 200, tempCanvas.height / 2 + 8, 260, tempCanvas.height / 2 + 20);
      tCtx.stroke();

      return {
        url: tempCanvas.toDataURL('image/png'),
        name: d.name,
        time: d.time
      };
    });
  }

  // Nạp danh sách chữ ký
  function loadSignatures() {
    if (!signListEl) return;
    signListEl.innerHTML = '';

    const storageKey = isPreviewMode ? 'mayhen_demo_signatures_hacbach2' : 'mayhen_real_signatures_hacbach2';
    let saved = [];
    try {
      saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
    } catch (e) {
      saved = [];
    }

    // Nếu ở chế độ xem thử và chưa có chữ ký nào, nạp 3 chữ ký mẫu demo
    if (isPreviewMode && (!saved || saved.length === 0)) {
      saved = createDemoSignatures();
      try {
        localStorage.setItem(storageKey, JSON.stringify(saved));
      } catch (e) {}
    }

    if (saved.length === 0) {
      signListEl.innerHTML = '<div style="grid-column: 1/-1; text-align:center; color:#888; font-style:italic; padding:20px;">Chưa có chữ ký nào. Hãy là người đầu tiên ký tên kỷ niệm nhé!</div>';
      return;
    }

    saved.forEach(item => {
      const card = renderSignatureCard(item.url, item.name, item.time);
      signListEl.appendChild(card);
    });
  }

  loadSignatures();

  // Nút "Lưu Chữ Ký" (#BUTTON5)
  if (btnSignSave && canvas) {
    btnSignSave.addEventListener('click', (e) => {
      e.stopPropagation();
      if (!hasDrawn) {
        showToast('Vui lòng ký tên vào khung trước khi lưu!');
        return;
      }

      const dataUrl = canvas.toDataURL('image/png');
      const now = new Date();
      const timeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} - Hôm nay`;
      const signerName = (new URLSearchParams(window.location.search).get('to')) || 'Khách quý';

      const newSign = {
        url: dataUrl,
        name: decodeURIComponent(signerName),
        time: timeStr
      };

      const storageKey = isPreviewMode ? 'mayhen_demo_signatures_hacbach2' : 'mayhen_real_signatures_hacbach2';
      let saved = [];
      try {
        saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
      } catch (err) {
        saved = [];
      }
      saved.unshift(newSign);

      try {
        localStorage.setItem(storageKey, JSON.stringify(saved.slice(0, 40)));
      } catch (err) {}

      // Thêm thẻ chữ ký mới vào đầu danh sách
      if (signListEl) {
        const card = renderSignatureCard(newSign.url, newSign.name, newSign.time);
        if (signListEl.firstElementChild) {
          signListEl.insertBefore(card, signListEl.firstElementChild);
        } else {
          signListEl.appendChild(card);
        }
      }

      clearCanvas();

      if (isPreviewMode) {
        showToast('✨ Đã lưu chữ ký kỷ niệm (Bản xem thử)! Dữ liệu thật sẽ được lưu trữ vĩnh viễn khi bạn tạo thiệp chính thức.');
      } else {
        showToast('✨ Cảm ơn bạn đã để lại chữ ký kỷ niệm chúc phúc cho cô dâu chú rể!');
      }

      // Cuộn nhẹ xem chữ ký vừa ký
      const previewWall = document.getElementById('previewContainer');
      if (previewWall) {
        previewWall.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }
});
