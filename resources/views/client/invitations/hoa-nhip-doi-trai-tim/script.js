// ============================================================================
// TEMPLATE: Hòa Nhịp Đôi Trái Tim (hoa-nhip-doi-trai-tim)
// Pure Vanilla ES Module | Zero External Libraries | Mayhen Live Sync Contract
// ============================================================================

const root = document.querySelector('.mayhen_invitation_root');
if (!root) {
  // No root mounted
} else {
  const isEmbed = root.dataset.isEmbed === 'true' || Boolean(window.__MAYHEN_IS_EMBED);
  const csrfToken = root.dataset.csrfToken
    || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    || window.__MAYHEN_CSRF_TOKEN
    || '';

  // --------------------------------------------------------------------------
  // 1. AUDIO CONTROLLER (NO OVERLAP WITH music-dock.blade.php)
  // --------------------------------------------------------------------------
  const audioEl = root.querySelector('.mayhen_invitation_audio');
  const musicBtn = root.querySelector('[data-mayhen-music-toggle]');
  let isPlaying = false;

  const updateMusicUI = (playing) => {
    isPlaying = playing;
    if (!musicBtn) return;
    musicBtn.classList.toggle('mayhen_invitation_music_btn--playing', playing);
    musicBtn.setAttribute('aria-pressed', playing ? 'true' : 'false');
  };

  const startMusic = () => {
    if (!audioEl || !audioEl.getAttribute('src') || isEmbed) return;
    const p = audioEl.play();
    if (p && typeof p.then === 'function') {
      p.then(() => updateMusicUI(true)).catch(() => updateMusicUI(false));
    }
  };

  const toggleMusic = () => {
    if (!audioEl || !audioEl.getAttribute('src')) return;
    if (audioEl.paused) {
      startMusic();
    } else {
      audioEl.pause();
      updateMusicUI(false);
    }
  };

  if (musicBtn) {
    musicBtn.addEventListener('click', toggleMusic);
  }

  // --------------------------------------------------------------------------
  // 2. OPENING SCREEN & SCROLL UNLOCK SAFETY
  // --------------------------------------------------------------------------
  const openingEl = root.querySelector('.mayhen_invitation_opening');
  const openBtn = root.querySelector('[data-mayhen-open-trigger]');

  const unlockScroll = () => {
    document.body.classList.remove('mayhen_invitation_scroll_locked', 'no-scroll');
    document.documentElement.classList.remove('mayhen_invitation_scroll_locked', 'no-scroll');
  };

  if (isEmbed || !openingEl) {
    unlockScroll();
  } else if (openBtn) {
    let opened = false;
    openBtn.addEventListener('click', () => {
      if (opened) return;
      opened = true;
      startMusic();
      openingEl.classList.add('mayhen_invitation_opening--opening');
      unlockScroll();

      window.setTimeout(() => {
        openingEl.classList.add('mayhen_invitation_opening--fadeout');
      }, 420);

      window.setTimeout(() => {
        openingEl.classList.add('mayhen_invitation_opening--hidden');
        openingEl.setAttribute('hidden', 'hidden');
        root.classList.add('mayhen_invitation_root--opened');
      }, 1250);
    });
  }

  // --------------------------------------------------------------------------
  // 3. SCROLL REVEAL (IMMEDIATE IN EMBED MODE OR VIEWPORT)
  // --------------------------------------------------------------------------
  const revealNodes = Array.from(root.querySelectorAll('.mayhen_invitation_reveal'));
  if (isEmbed || !('IntersectionObserver' in window)) {
    revealNodes.forEach((el) => el.classList.add('mayhen_invitation_visible'));
  } else {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('mayhen_invitation_visible');
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1, rootMargin: '0px 0px -24px 0px' }
    );
    revealNodes.forEach((el) => {
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight * 1.1) {
        el.classList.add('mayhen_invitation_visible');
      } else {
        io.observe(el);
      }
    });
  }

  // --------------------------------------------------------------------------
  // 4. COUNTDOWN & CALENDAR GLOBAL APIS (Asia/Ho_Chi_Minh +07:00)
  // --------------------------------------------------------------------------
  let countdownTimerId = null;

  const parseWeddingDateParts = (input) => {
    if (input instanceof Date && !Number.isNaN(input.getTime())) {
      return { y: input.getFullYear(), m: input.getMonth() + 1, d: input.getDate() };
    }
    const str = String(input || '').trim();
    const isoMatch = str.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
    if (isoMatch) {
      return { y: Number(isoMatch[1]), m: Number(isoMatch[2]), d: Number(isoMatch[3]) };
    }
    const vnMatch = str.match(/^(\d{1,2})[./-](\d{1,2})[./-](\d{4})/);
    if (vnMatch) {
      return { y: Number(vnMatch[3]), m: Number(vnMatch[2]), d: Number(vnMatch[1]) };
    }
    return { y: 2027, m: 11, d: 21 };
  };

  const pad2 = (n) => String(Math.max(0, Math.floor(n))).padStart(2, '0');

  const updateGoogleCalendarLink = (dateInput, timeInput) => {
    const gcalBtn = root.querySelector('[data-mayhen-gcal-link]');
    if (!gcalBtn) return;
    const { y, m, d } = parseWeddingDateParts(dateInput || root.dataset.weddingDate);
    const [hh, mm] = String(timeInput || root.dataset.targetTime || '11:00').split(':');
    const startMs = Date.parse(`${y}-${pad2(m)}-${pad2(d)}T${pad2(Number(hh) || 11)}:${pad2(Number(mm) || 0)}:00+07:00`);
    if (Number.isNaN(startMs)) return;

    const toGcalUtc = (ms) => {
      const dt = new Date(ms);
      return `${dt.getUTCFullYear()}${pad2(dt.getUTCMonth() + 1)}${pad2(dt.getUTCDate())}T${pad2(dt.getUTCHours())}${pad2(dt.getUTCMinutes())}00Z`;
    };
    const groom = root.querySelector('[data-sync-target="groom_name"]')?.textContent?.trim() || root.dataset.groomName || 'Chú Rể';
    const bride = root.querySelector('[data-sync-target="bride_name"]')?.textContent?.trim() || root.dataset.brideName || 'Cô Dâu';
    const venue = root.querySelector('[data-sync-target="party_venue"]')?.textContent?.trim() || root.dataset.partyVenue || '';
    const addr = root.querySelector('[data-sync-target="event_party_address"]')?.textContent?.trim() || root.dataset.partyAddress || '';
    const loc = [venue, addr].filter(Boolean).join(', ');
    gcalBtn.href = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(`Đám cưới ${groom} & ${bride}`)}&dates=${toGcalUtc(startMs)}/${toGcalUtc(startMs + 4 * 3600000)}&location=${encodeURIComponent(loc)}`;
  };

  window.updateMayhenCountdown = function updateMayhenCountdown(targetDate) {
    if (countdownTimerId) {
      window.clearInterval(countdownTimerId);
      countdownTimerId = null;
    }

    const { y, m, d } = parseWeddingDateParts(targetDate || root.dataset.weddingDate);
    const timeStr = root.dataset.targetTime || '11:00';
    const [hh, mm] = String(timeStr).split(':');
    const isoTarget = `${y}-${pad2(m)}-${pad2(d)}T${pad2(Number(hh) || 11)}:${pad2(Number(mm) || 0)}:00+07:00`;
    const targetMs = Date.parse(isoTarget);

    const dayEls = root.querySelectorAll('[data-sync-target="countdown_day"], .mayhen_invitation_cd_day');
    const hourEls = root.querySelectorAll('[data-sync-target="countdown_hour"], .mayhen_invitation_cd_hour');
    const minEls = root.querySelectorAll('[data-sync-target="countdown_min"], .mayhen_invitation_cd_min');
    const secEls = root.querySelectorAll('[data-sync-target="countdown_sec"], .mayhen_invitation_cd_sec');

    const tick = () => {
      const diff = Number.isNaN(targetMs) ? 0 : Math.max(0, targetMs - Date.now());
      const days = Math.floor(diff / 86400000);
      const hours = Math.floor((diff % 86400000) / 3600000);
      const mins = Math.floor((diff % 3600000) / 60000);
      const secs = Math.floor((diff % 60000) / 1000);

      dayEls.forEach((el) => { el.textContent = String(days).padStart(2, '0'); });
      hourEls.forEach((el) => { el.textContent = pad2(hours); });
      minEls.forEach((el) => { el.textContent = pad2(mins); });
      secEls.forEach((el) => { el.textContent = pad2(secs); });
    };

    tick();
    countdownTimerId = window.setInterval(tick, 1000);
  };

  window.renderMayhenWeddingCalendar = function renderMayhenWeddingCalendar(targetDate) {
    const { y, m, d } = parseWeddingDateParts(targetDate || root.dataset.weddingDate);
    const body = root.querySelector('[data-sync-target="wedding_calendar"], .mayhen_invitation_calendar_body');
    const header = root.querySelector('[data-sync-target="calendar_month_year"]');
    if (header) {
      header.textContent = `Tháng ${pad2(m)} · ${y}`;
    }
    if (!body) return;

    const isLeap = (y % 4 === 0 && y % 100 !== 0) || (y % 400 === 0);
    const daysInMonths = [31, isLeap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    const totalDays = daysInMonths[Math.max(0, Math.min(11, m - 1))];
    const firstJsDay = new Date(Date.UTC(y, m - 1, 1)).getUTCDay();
    const leadingBlanks = (firstJsDay + 6) % 7;

    body.textContent = '';
    for (let i = 0; i < leadingBlanks; i += 1) {
      const blank = document.createElement('div');
      blank.className = 'mayhen_invitation_calendar_day mayhen_invitation_calendar_day--empty';
      blank.setAttribute('aria-hidden', 'true');
      body.appendChild(blank);
    }

    for (let day = 1; day <= totalDays; day += 1) {
      const cell = document.createElement('div');
      cell.className = 'mayhen_invitation_calendar_day';
      if (day === d) {
        cell.classList.add('mayhen_invitation_selected', 'mayhen_invitation_calendar_selected_day');
      }
      cell.textContent = String(day);
      body.appendChild(cell);
    }
  };

  window.updateMayhenCountdown(root.dataset.weddingDate);
  window.renderMayhenWeddingCalendar(root.dataset.weddingDate);
  updateGoogleCalendarLink(root.dataset.weddingDate, root.dataset.targetTime);

  // --------------------------------------------------------------------------
  // 5. COVERFLOW 3D ALBUM & LIGHTBOX
  // --------------------------------------------------------------------------
  const cfWrap = root.querySelector('[data-mayhen-coverflow]');
  const cfItems = cfWrap ? Array.from(cfWrap.querySelectorAll('.mayhen_invitation_gallery_item')) : [];
  const cfDots = cfWrap ? Array.from(cfWrap.querySelectorAll('[data-cf-dot]')) : [];
  let activeSlide = 0;

  const renderCoverflow = () => {
    const total = cfItems.length;
    if (!total) return;

    cfItems.forEach((item, idx) => {
      let diff = idx - activeSlide;
      if (diff > total / 2) diff -= total;
      if (diff < -total / 2) diff += total;

      const abs = Math.abs(diff);
      if (abs > 2) {
        item.style.opacity = '0';
        item.style.pointerEvents = 'none';
        item.style.transform = `translate3d(${diff * 120}px, 0, -260px) scale(0.7)`;
        item.style.zIndex = '1';
      } else {
        const tx = diff * 135;
        const tz = -abs * 90;
        const ry = diff * -22;
        const sc = abs === 0 ? 1 : abs === 1 ? 0.84 : 0.7;
        item.style.opacity = abs === 0 ? '1' : abs === 1 ? '0.88' : '0.45';
        item.style.pointerEvents = 'auto';
        item.style.transform = `translate3d(${tx}px, 0, ${tz}px) rotateY(${ry}deg) scale(${sc})`;
        item.style.zIndex = String(10 - abs);
        item.style.filter = abs === 0 ? 'none' : 'brightness(0.86)';
      }
    });

    cfDots.forEach((dot, idx) => {
      dot.classList.toggle('mayhen_invitation_gallery_dot--active', idx === activeSlide);
    });
  };

  const stepSlide = (delta) => {
    if (!cfItems.length) return;
    activeSlide = (activeSlide + delta + cfItems.length) % cfItems.length;
    renderCoverflow();
  };

  if (cfWrap) {
    renderCoverflow();
    cfWrap.querySelector('[data-cf-prev]')?.addEventListener('click', () => stepSlide(-1));
    cfWrap.querySelector('[data-cf-next]')?.addEventListener('click', () => stepSlide(1));
    cfDots.forEach((dot) => {
      dot.addEventListener('click', () => {
        activeSlide = Number(dot.dataset.cfDot) || 0;
        renderCoverflow();
      });
    });

    let touchStartX = 0;
    cfWrap.addEventListener('touchstart', (e) => {
      touchStartX = e.touches[0]?.clientX || 0;
    }, { passive: true });
    cfWrap.addEventListener('touchend', (e) => {
      const dx = (e.changedTouches[0]?.clientX || 0) - touchStartX;
      if (Math.abs(dx) > 40) stepSlide(dx < 0 ? 1 : -1);
    }, { passive: true });
  }

  // Lightbox
  const lightboxEl = document.querySelector('[data-mayhen-lightbox]');
  const lbImg = lightboxEl?.querySelector('[data-lightbox-img]');
  const lbCounter = lightboxEl?.querySelector('[data-lightbox-counter]');
  let lbIndex = 0;

  const getGallerySrcs = () => cfItems.map((el) => el.querySelector('img')?.getAttribute('src') || '');

  const showLightboxAt = (idx) => {
    const srcs = getGallerySrcs();
    if (!lightboxEl || !lbImg || !srcs.length) return;
    lbIndex = (idx + srcs.length) % srcs.length;
    lbImg.src = srcs[lbIndex];
    if (lbCounter) lbCounter.textContent = `${lbIndex + 1} / ${srcs.length}`;
    lightboxEl.classList.add('mayhen_invitation_lightbox--open');
  };

  const closeLightbox = () => {
    lightboxEl?.classList.remove('mayhen_invitation_lightbox--open');
  };

  cfItems.forEach((item, idx) => {
    item.addEventListener('click', () => {
      if (idx !== activeSlide) {
        activeSlide = idx;
        renderCoverflow();
      } else {
        showLightboxAt(idx);
      }
    });
  });

  lightboxEl?.querySelector('[data-lightbox-close]')?.addEventListener('click', closeLightbox);
  lightboxEl?.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => showLightboxAt(lbIndex - 1));
  lightboxEl?.querySelector('[data-lightbox-next]')?.addEventListener('click', () => showLightboxAt(lbIndex + 1));
  lightboxEl?.addEventListener('click', (e) => {
    if (e.target === lightboxEl) closeLightbox();
  });
  document.addEventListener('keydown', (e) => {
    if (!lightboxEl?.classList.contains('mayhen_invitation_lightbox--open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') showLightboxAt(lbIndex - 1);
    if (e.key === 'ArrowRight') showLightboxAt(lbIndex + 1);
  });

  // --------------------------------------------------------------------------
  // 6. GIFT RED ENVELOPE & 1-TAP COPY STK (NO alert())
  // --------------------------------------------------------------------------
  const giftTrigger = root.querySelector('[data-mayhen-gift-trigger]');
  const giftBoxWrap = root.querySelector('[data-mayhen-gift-box-wrap]');
  const giftCards = root.querySelector('[data-mayhen-gift-cards]');

  if (giftTrigger && giftCards) {
    giftTrigger.addEventListener('click', () => {
      giftTrigger.setAttribute('aria-expanded', 'true');
      giftBoxWrap?.classList.add('mayhen_invitation_gift_box_wrap--opened');
      giftCards.classList.add('mayhen_invitation_gift_cards--visible');
    });
  }

  root.querySelectorAll('.mayhen_invitation_copy_btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const targetKey = btn.dataset.copyTarget;
      const liveText = targetKey
        ? root.querySelector(`[data-sync-target="${targetKey}"]`)?.textContent?.trim()
        : '';
      const textToCopy = liveText || btn.dataset.copy || '';
      if (!textToCopy) return;

      try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(textToCopy);
        } else {
          const ta = document.createElement('textarea');
          ta.value = textToCopy;
          ta.style.position = 'fixed';
          ta.style.opacity = '0';
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta);
        }
        const original = btn.textContent;
        btn.textContent = 'Đã sao chép!';
        btn.classList.add('mayhen_invitation_copy_btn--copied');
        window.setTimeout(() => {
          btn.textContent = original || 'Sao chép STK';
          btn.classList.remove('mayhen_invitation_copy_btn--copied');
        }, 2000);
      } catch {
        btn.textContent = 'Không thể sao chép';
      }
    });
  });

  // --------------------------------------------------------------------------
  // 7. SIGNATURE CANVAS (POINTER EVENTS + DPR + RESIZE STROKE BUFFER)
  // --------------------------------------------------------------------------
  const sigCanvas = root.querySelector('[data-mayhen-sig-canvas]');
  const sigPlaceholder = root.querySelector('[data-mayhen-sig-placeholder]');
  const sigNameInput = root.querySelector('[data-mayhen-sig-name]');
  const sigClearBtn = root.querySelector('[data-mayhen-sig-clear]');
  const sigPreviewBtn = root.querySelector('[data-mayhen-sig-preview-btn]');
  const sigSaveBtn = root.querySelector('[data-mayhen-sig-save]');
  const sigPreviewBox = root.querySelector('[data-mayhen-sig-preview-box]');
  const sigPreviewImg = root.querySelector('[data-mayhen-sig-preview-img]');
  const sigDownloadLink = root.querySelector('[data-mayhen-sig-download]');
  const sigFeedback = root.querySelector('[data-mayhen-sig-feedback]');

  if (sigCanvas) {
    const ctx = sigCanvas.getContext('2d');
    const strokes = [];
    let currentStroke = null;
    let isDrawing = false;

    const redrawStrokes = () => {
      if (!ctx) return;
      const rect = sigCanvas.getBoundingClientRect();
      ctx.clearRect(0, 0, rect.width, rect.height);
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      ctx.strokeStyle = '#6E4B1A';
      ctx.lineWidth = 2.6;

      strokes.forEach((pts) => {
        if (!pts.length) return;
        ctx.beginPath();
        ctx.moveTo(pts[0].x * rect.width, pts[0].y * rect.height);
        for (let i = 1; i < pts.length; i += 1) {
          ctx.lineTo(pts[i].x * rect.width, pts[i].y * rect.height);
        }
        ctx.stroke();
      });
    };

    const resizeCanvas = () => {
      const rect = sigCanvas.getBoundingClientRect();
      if (rect.width === 0 || rect.height === 0) return;
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      sigCanvas.width = Math.round(rect.width * dpr);
      sigCanvas.height = Math.round(rect.height * dpr);
      ctx?.setTransform(dpr, 0, 0, dpr, 0, 0);
      redrawStrokes();
    };

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    const getNormPoint = (e) => {
      const rect = sigCanvas.getBoundingClientRect();
      return {
        x: Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width)),
        y: Math.max(0, Math.min(1, (e.clientY - rect.top) / rect.height)),
      };
    };

    sigCanvas.addEventListener('pointerdown', (e) => {
      isDrawing = true;
      sigCanvas.setPointerCapture(e.pointerId);
      sigPlaceholder?.classList.add('mayhen_invitation_signature_placeholder--hidden');
      currentStroke = [getNormPoint(e)];
      strokes.push(currentStroke);
      redrawStrokes();
    });

    sigCanvas.addEventListener('pointermove', (e) => {
      if (!isDrawing || !currentStroke) return;
      currentStroke.push(getNormPoint(e));
      redrawStrokes();
    });

    const endDraw = () => {
      isDrawing = false;
      currentStroke = null;
    };
    sigCanvas.addEventListener('pointerup', endDraw);
    sigCanvas.addEventListener('pointercancel', endDraw);

    const setSigFeedback = (msg, type = '') => {
      if (!sigFeedback) return;
      sigFeedback.textContent = msg;
      sigFeedback.className = 'mayhen_invitation_feedback' + (type ? ` mayhen_invitation_feedback--${type}` : '');
    };

    sigClearBtn?.addEventListener('click', () => {
      strokes.length = 0;
      redrawStrokes();
      sigPlaceholder?.classList.remove('mayhen_invitation_signature_placeholder--hidden');
      sigPreviewBox?.classList.remove('mayhen_invitation_signature_preview--visible');
      setSigFeedback('');
    });

    const showSigPreview = () => {
      if (!strokes.length) {
        setSigFeedback('Vui lòng vẽ chữ ký của bạn trước khi xem trước.', 'error');
        return null;
      }
      const dataUrl = sigCanvas.toDataURL('image/png');
      if (sigPreviewImg) sigPreviewImg.src = dataUrl;
      if (sigDownloadLink) sigDownloadLink.href = dataUrl;
      sigPreviewBox?.classList.add('mayhen_invitation_signature_preview--visible');
      return dataUrl;
    };

    sigPreviewBtn?.addEventListener('click', () => {
      const url = showSigPreview();
      if (url) setSigFeedback('Bản xem trước chữ ký PNG đã sẵn sàng.', 'success');
    });

    let sigSaving = false;
    sigSaveBtn?.addEventListener('click', async () => {
      if (sigSaving) return;
      if (!strokes.length) {
        setSigFeedback('Vui lòng vẽ chữ ký của bạn lên khung trước khi lưu.', 'error');
        return;
      }
      const signerName = (sigNameInput?.value || root.querySelector('#mayhen_rsvp_name')?.value || root.dataset.guestName || '').trim();
      if (!signerName) {
        setSigFeedback('Vui lòng nhập tên của bạn trước khi lưu chữ ký.', 'error');
        sigNameInput?.focus();
        return;
      }

      const dataUrl = showSigPreview();
      const endpoint = root.dataset.signatureEndpoint || '';
      if (!endpoint) {
        setSigFeedback('Chế độ xem thử: Chữ ký đã sẵn sàng tải PNG. Khi bạn tạo và xuất bản thiệp, chữ ký khách mời sẽ được lưu trực tiếp vào Sổ Ký Tên!', 'success');
        return;
      }

      sigSaving = true;
      sigSaveBtn.disabled = true;
      setSigFeedback('Đang lưu chữ ký của bạn…');

      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({
            guest_name: signerName,
            signature_data: dataUrl,
          }),
        });
        const data = await res.json().catch(() => ({}));
        if (res.ok && data.success !== false) {
          setSigFeedback(data.message || 'Cảm ơn bạn đã lưu chữ ký chúc phúc!', 'success');
        } else {
          setSigFeedback(data.message || 'Không thể lưu chữ ký lúc này. Vui lòng thử lại.', 'error');
        }
      } catch {
        setSigFeedback('Lỗi kết nối mạng khi lưu chữ ký. Vui lòng thử lại.', 'error');
      } finally {
        sigSaving = false;
        sigSaveBtn.disabled = false;
      }
    });
  }

  // --------------------------------------------------------------------------
  // 8. RSVP FORM (EXACT SubmitRsvpRequest PAYLOAD)
  // --------------------------------------------------------------------------
  const rsvpForm = root.querySelector('[data-mayhen-rsvp-form]');
  if (rsvpForm) {
    let selectedStatus = 'attending';
    const statusBtns = Array.from(rsvpForm.querySelectorAll('[data-status-val]'));
    const countsWrap = rsvpForm.querySelector('[data-mayhen-rsvp-counts]');
    const adultsInput = rsvpForm.querySelector('[data-counter-input="adults"]');
    const childrenInput = rsvpForm.querySelector('[data-counter-input="children"]');
    const rsvpFeedback = rsvpForm.querySelector('[data-mayhen-rsvp-feedback]');
    const rsvpSubmitBtn = rsvpForm.querySelector('.mayhen_invitation_rsvp_submit_btn');

    statusBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        selectedStatus = btn.dataset.statusVal || 'attending';
        statusBtns.forEach((b) => b.classList.toggle('mayhen_invitation_opt_btn--active', b === btn));
        if (countsWrap) {
          countsWrap.style.display = selectedStatus === 'attending' ? '' : 'none';
        }
      });
    });

    const stepCounter = (input, delta) => {
      if (!input) return;
      const cur = Number(input.value) || 0;
      input.value = String(Math.max(0, Math.min(10, cur + delta)));
    };
    rsvpForm.querySelector('[data-counter-dec="adults"]')?.addEventListener('click', () => stepCounter(adultsInput, -1));
    rsvpForm.querySelector('[data-counter-inc="adults"]')?.addEventListener('click', () => stepCounter(adultsInput, 1));
    rsvpForm.querySelector('[data-counter-dec="children"]')?.addEventListener('click', () => stepCounter(childrenInput, -1));
    rsvpForm.querySelector('[data-counter-inc="children"]')?.addEventListener('click', () => stepCounter(childrenInput, 1));

    let rsvpSubmitting = false;
    rsvpForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (rsvpSubmitting) return;

      const guestName = (rsvpForm.querySelector('input[name="guest_name"]')?.value || '').trim();
      const wishes = (rsvpForm.querySelector('textarea[name="wishes"]')?.value || '').trim();
      const setFeedback = (msg, type = '') => {
        if (!rsvpFeedback) return;
        rsvpFeedback.textContent = msg;
        rsvpFeedback.className = 'mayhen_invitation_feedback' + (type ? ` mayhen_invitation_feedback--${type}` : '');
      };

      if (!guestName) {
        setFeedback('Vui lòng nhập tên của bạn trước khi xác nhận.', 'error');
        return;
      }

      const endpoint = root.dataset.rsvpEndpoint || '';
      if (!endpoint) {
        setFeedback('Chế độ xem thử: Xác nhận tham dự sẽ hoạt động trực tiếp khi thiệp được xuất bản.', 'success');
        return;
      }

      const payload = {
        guest_name: guestName,
        attending_status: selectedStatus,
        adults_count: selectedStatus === 'attending' ? Number(adultsInput?.value || 1) : 0,
        children_count: selectedStatus === 'attending' ? Number(childrenInput?.value || 0) : 0,
        wishes,
      };

      rsvpSubmitting = true;
      if (rsvpSubmitBtn) rsvpSubmitBtn.disabled = true;
      setFeedback('Đang gửi xác nhận…');

      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify(payload),
        });
        const data = await res.json().catch(() => ({}));
        if (res.ok && data.success === true) {
          setFeedback(data.message || 'Cảm ơn bạn đã xác nhận tham dự!', 'success');
        } else {
          setFeedback(data.message || 'Không thể gửi xác nhận lúc này.', 'error');
        }
      } catch {
        setFeedback('Lỗi kết nối mạng. Vui lòng thử lại sau.', 'error');
      } finally {
        rsvpSubmitting = false;
        if (rsvpSubmitBtn) rsvpSubmitBtn.disabled = false;
      }
    });
  }

  // --------------------------------------------------------------------------
  // 9. WISHES FORM (EXACT SubmitWishRequest PAYLOAD + XSS-SAFE DOM INSERT)
  // --------------------------------------------------------------------------
  const wishForm = root.querySelector('[data-mayhen-wish-form]');
  const wishList = root.querySelector('[data-mayhen-wish-list]');
  if (wishForm) {
    const wishFeedback = wishForm.querySelector('[data-mayhen-wish-feedback]');
    const wishSubmitBtn = wishForm.querySelector('.mayhen_invitation_wish_submit_btn');
    let wishSubmitting = false;

    const appendWishSafe = (author, message) => {
      if (!wishList) return;
      wishList.querySelector('[data-mayhen-wish-empty]')?.remove();
      const item = document.createElement('div');
      item.className = 'mayhen_invitation_wish_item';

      const authorEl = document.createElement('p');
      authorEl.className = 'mayhen_invitation_wish_author';
      authorEl.textContent = author;

      const msgEl = document.createElement('p');
      msgEl.className = 'mayhen_invitation_wish_text';
      msgEl.textContent = message;

      item.appendChild(authorEl);
      item.appendChild(msgEl);
      wishList.prepend(item);
    };

    wishForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (wishSubmitting) return;

      const name = (wishForm.querySelector('input[name="name"]')?.value || '').trim();
      const message = (wishForm.querySelector('textarea[name="message"]')?.value || '').trim();
      const setFeedback = (msg, type = '') => {
        if (!wishFeedback) return;
        wishFeedback.textContent = msg;
        wishFeedback.className = 'mayhen_invitation_feedback' + (type ? ` mayhen_invitation_feedback--${type}` : '');
      };

      if (!name || !message) {
        setFeedback('Vui lòng nhập đầy đủ tên và lời chúc của bạn.', 'error');
        return;
      }

      const endpoint = root.dataset.wishesEndpoint || '';
      if (!endpoint) {
        appendWishSafe(name, message);
        wishForm.querySelector('textarea[name="message"]').value = '';
        setFeedback('Chế độ xem thử: Lời chúc đã hiển thị tạm thời. Khi xuất bản thiệp, lời chúc sẽ được lưu vào sổ lưu bút!', 'success');
        return;
      }

      wishSubmitting = true;
      if (wishSubmitBtn) wishSubmitBtn.disabled = true;
      setFeedback('Đang gửi lời chúc…');

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
        const data = await res.json().catch(() => ({}));
        if (res.ok && data.success === true) {
          appendWishSafe(name, message);
          wishForm.querySelector('textarea[name="message"]').value = '';
          setFeedback(data.message || 'Cảm ơn lời chúc ngọt ngào của bạn!', 'success');
        } else {
          setFeedback(data.message || 'Không thể gửi lời chúc lúc này.', 'error');
        }
      } catch {
        setFeedback('Lỗi kết nối mạng. Vui lòng thử lại sau.', 'error');
      } finally {
        wishSubmitting = false;
        if (wishSubmitBtn) wishSubmitBtn.disabled = false;
      }
    });
  }

  // --------------------------------------------------------------------------
  // 10. LIVE SYNC EXTENSIONS (LISTEN ONLY — NEVER OVERRIDE editor-bridge)
  // --------------------------------------------------------------------------
  const renderDresscodeSwatches = (rawColors) => {
    const container = root.querySelector('[data-sync-target="dresscode_colors"]');
    if (!container) return;
    const hexes = String(rawColors || '')
      .split(',')
      .map((s) => s.trim())
      .filter((s) => /^#[0-9A-Fa-f]{3,6}$/.test(s));
    if (!hexes.length) return;

    container.textContent = '';
    hexes.forEach((hex) => {
      const item = document.createElement('div');
      item.className = 'mayhen_invitation_dresscode_item';

      const drop = document.createElement('div');
      drop.className = 'mayhen_invitation_dresscode_drop';

      const circle = document.createElement('div');
      circle.className = 'mayhen_invitation_dresscode_circle';
      circle.style.setProperty('--mh-swatch-color', hex);

      const ripple = document.createElement('div');
      ripple.className = 'mayhen_invitation_dresscode_ripple mayhen_invitation_decor';
      ripple.setAttribute('aria-hidden', 'true');

      const label = document.createElement('span');
      label.className = 'mayhen_invitation_dresscode_label';
      label.textContent = hex.toUpperCase();

      drop.appendChild(circle);
      drop.appendChild(ripple);
      item.appendChild(drop);
      item.appendChild(label);
      container.appendChild(item);
    });
  };

  window.addEventListener('message', (event) => {
    if (event.origin !== window.location.origin) return;
    const data = event.data;
    if (!data || data.type !== 'MAYHEN_SYNC_INPUT') return;

    const field = String(data.field || data.key || '');
    const value = data.value;

    if (field.endsWith('dresscode_colors')) {
      renderDresscodeSwatches(value);
    } else if (field.endsWith('wedding_date')) {
      root.dataset.weddingDate = String(value || '');
      window.updateMayhenCountdown(value);
      window.renderMayhenWeddingCalendar(value);
      updateGoogleCalendarLink(value, root.dataset.targetTime);
    } else if (field.endsWith('event_target_time') || field.endsWith('party_time')) {
      root.dataset.targetTime = String(value || '11:00');
      window.updateMayhenCountdown(root.dataset.weddingDate);
      updateGoogleCalendarLink(root.dataset.weddingDate, value);
    } else if (field.endsWith('event_party_map_link') && /^https?:\/\//i.test(String(value || ''))) {
      root.querySelectorAll('[data-sync-target="event_party_map_link"]').forEach((a) => {
        a.setAttribute('href', String(value));
      });
    }
  });
}
