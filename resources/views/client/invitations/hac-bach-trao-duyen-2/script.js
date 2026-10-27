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

  /* ==========================================================================
     2. GÁN TÊN KHÁCH MỜI TỪ URL PARAM (?to= hoặc ?guest=)
     ========================================================================== */
  const urlParams = new URLSearchParams(window.location.search);
  const guestName = urlParams.get('to') || urlParams.get('guest');
  if (guestName) {
    const guestEl = document.querySelector('#GROUP140 p') || document.querySelector('#POPUP8 p');
    if (guestEl && guestEl.textContent.includes('Thân gửi')) {
      guestEl.innerHTML = `Thân gửi: <br><strong style="font-size:1.15em; color:#fff;">${decodeURIComponent(guestName)}</strong>`;
    }
  }

  /* ==========================================================================
     3. SCROLL REVEAL BẰNG INTERSECTION OBSERVER
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
     4. COUNTDOWN TIMER (SECTION 22)
     ========================================================================== */
  const targetDateStr = document.body.getAttribute('data-target-date') || '2025-10-09T16:00:00';
  const targetTime = new Date(targetDateStr).getTime() || new Date(2025, 9, 9, 16, 0, 0).getTime();

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
     5. CÁC NÚT ĐIỀU HƯỚNG VÀ POPUP (BẢN ĐỒ, LỜI CHÚC, MỪNG CƯỚI)
     ========================================================================== */
  document.querySelectorAll('[data-action="true"]').forEach(btn => {
    btn.style.cursor = 'pointer';
  });

});
