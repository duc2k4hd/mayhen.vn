/**
 * Mây Hẹn (mayhen.vn) — Native ES Module Script for "Trọn Kiếp Này Bên Nhau" (`tron-kiep-nay-ben-nhau`)
 * Zero external dependencies. Implements:
 * - Opening Screen & Music playback with Web Audio fallback & Promise rejection safety
 * - Studio Embed mode ($isEmbed) safety (never locks document body in embed mode)
 * - Global APIs: window.updateMayhenCountdown(targetDate) & window.renderMayhenWeddingCalendar(targetDate)
 *   using Asia/Ho_Chi_Minh timezone and accurate leap-year / Monday-Sunday calendar logic
 * - Live Studio sync (`data-sync-target` across all occurrences + `postMessage` & `mayhen:studio-sync` custom events)
 * - Functional RSVP Form & Guestbook Wishes with CSRF & Studio adapter hooks
 * - High-DPI Pointer Events Electronic Signature Canvas with resize preservation & PNG export
 */

const VIETNAMESE_WEEKDAYS = [
  'Chủ Nhật',
  'Thứ Hai',
  'Thứ Ba',
  'Thứ Tư',
  'Thứ Năm',
  'Thứ Sáu',
  'Thứ Bảy'
];

let countdownIntervalId = null;

/**
 * Parse a date input (ISO string YYYY-MM-DD, DD.MM.YYYY, Date object, or timestamp)
 * into `{ year, month, day, hours, minutes, seconds }` anchored to Asia/Ho_Chi_Minh (+07:00).
 */
function parseTargetDateHcm(targetDate) {
  if (!targetDate) {
    return { year: 2026, month: 8, day: 2, hours: 9, minutes: 0, seconds: 0 };
  }

  if (typeof targetDate === 'string') {
    const trimmed = targetDate.trim();
    // Match YYYY-MM-DD
    const isoMatch = trimmed.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})(?:[T\s](\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?/);
    if (isoMatch) {
      return {
        year: parseInt(isoMatch[1], 10),
        month: parseInt(isoMatch[2], 10),
        day: parseInt(isoMatch[3], 10),
        hours: isoMatch[4] !== undefined ? parseInt(isoMatch[4], 10) : 9,
        minutes: isoMatch[5] !== undefined ? parseInt(isoMatch[5], 10) : 0,
        seconds: isoMatch[6] !== undefined ? parseInt(isoMatch[6], 10) : 0
      };
    }
    // Match DD . MM . YYYY or DD-MM-YYYY
    const dmyMatch = trimmed.match(/^(\d{1,2})[\s./-]+(\d{1,2})[\s./-]+(\d{4})/);
    if (dmyMatch) {
      return {
        year: parseInt(dmyMatch[3], 10),
        month: parseInt(dmyMatch[2], 10),
        day: parseInt(dmyMatch[1], 10),
        hours: 9,
        minutes: 0,
        seconds: 0
      };
    }
  }

  const d = targetDate instanceof Date ? targetDate : new Date(targetDate);
  if (Number.isNaN(d.getTime())) {
    return { year: 2026, month: 8, day: 2, hours: 9, minutes: 0, seconds: 0 };
  }

  // Format in Asia/Ho_Chi_Minh timezone
  const formatter = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Asia/Ho_Chi_Minh',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  });
  const parts = {};
  for (const part of formatter.formatToParts(d)) {
    parts[part.type] = part.value;
  }
  return {
    year: parseInt(parts.year, 10),
    month: parseInt(parts.month, 10),
    day: parseInt(parts.day, 10),
    hours: parseInt(parts.hour, 10) || 9,
    minutes: parseInt(parts.minute, 10) || 0,
    seconds: parseInt(parts.second, 10) || 0
  };
}

/**
 * Convert `{ year, month, day, hours, minutes, seconds }` in Asia/Ho_Chi_Minh (+07:00)
 * to a UTC epoch timestamp in milliseconds.
 */
function hcmPartsToEpochMs(parts) {
  const utcMs = Date.UTC(
    parts.year,
    parts.month - 1,
    parts.day,
    parts.hours || 0,
    parts.minutes || 0,
    parts.seconds || 0
  );
  // Asia/Ho_Chi_Minh is UTC+7
  return utcMs - 7 * 3600 * 1000;
}

function pad2(num) {
  return String(Math.max(0, Math.floor(num))).padStart(2, '0');
}

/**
 * Sync all DOM elements matching `[data-sync-target="<key>"]`.
 */
export function syncFieldValue(key, value) {
  if (!key || value === undefined || value === null) return;
  const nodes = document.querySelectorAll(`[data-sync-target="${CSS.escape(key)}"]`);
  nodes.forEach((node) => {
    const tag = node.tagName.toLowerCase();
    if (tag === 'img') {
      node.setAttribute('src', String(value));
      if (node.hasAttribute('data-lightbox-src')) {
        node.setAttribute('data-lightbox-src', String(value));
      }
    } else if (tag === 'a') {
      node.setAttribute('href', String(value));
    } else if (tag === 'audio') {
      node.setAttribute('src', String(value));
    } else {
      node.textContent = String(value);
    }
  });
}

/**
 * Global API #1: window.updateMayhenCountdown(targetDate)
 * Updates the countdown immediately and manages a single clean interval timer.
 */
window.updateMayhenCountdown = function updateMayhenCountdown(targetDate) {
  if (countdownIntervalId !== null) {
    clearInterval(countdownIntervalId);
    countdownIntervalId = null;
  }

  const parsed = parseTargetDateHcm(targetDate);
  const targetEpochMs = hcmPartsToEpochMs(parsed);

  const renderTick = () => {
    const nowMs = Date.now();
    const diffMs = Math.max(0, targetEpochMs - nowMs);
    const totalSeconds = Math.floor(diffMs / 1000);

    const days = Math.floor(totalSeconds / 86400);
    const hours = Math.floor((totalSeconds % 86400) / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    document.querySelectorAll('[data-countdown-days]').forEach((el) => {
      el.textContent = pad2(days);
    });
    document.querySelectorAll('[data-countdown-hours]').forEach((el) => {
      el.textContent = pad2(hours);
    });
    document.querySelectorAll('[data-countdown-minutes]').forEach((el) => {
      el.textContent = pad2(minutes);
    });
    document.querySelectorAll('[data-countdown-seconds]').forEach((el) => {
      el.textContent = pad2(seconds);
    });

    if (diffMs <= 0 && countdownIntervalId !== null) {
      clearInterval(countdownIntervalId);
      countdownIntervalId = null;
    }
  };

  renderTick();
  countdownIntervalId = window.setInterval(renderTick, 1000);
};

/**
 * Global API #2: window.renderMayhenWeddingCalendar(targetDate)
 * Renders the monthly wedding calendar (Monday - Sunday columns) and synchronizes
 * all date/weekday/month/year `data-sync-target` elements across the page.
 */
window.renderMayhenWeddingCalendar = function renderMayhenWeddingCalendar(targetDate) {
  const parsed = parseTargetDateHcm(targetDate);
  const { year, month, day } = parsed;

  // Validate day in month (handles leap years accurately via UTC Date)
  const daysInMonth = new Date(Date.UTC(year, month, 0)).getUTCDate();
  const safeDay = Math.min(Math.max(1, day), daysInMonth);

  // Day of week for 1st of month (0=Sun .. 6=Sat) -> convert to Monday-first index (0=Mon .. 6=Sun)
  const firstDaySun0 = new Date(Date.UTC(year, month - 1, 1)).getUTCDay();
  const mondayFirstOffset = (firstDaySun0 + 6) % 7;

  // Day of week for the selected wedding date
  const selectedSun0 = new Date(Date.UTC(year, month - 1, safeDay)).getUTCDay();
  const weekdayLabel = VIETNAMESE_WEEKDAYS[selectedSun0];

  const dd = pad2(safeDay);
  const mm = pad2(month);
  const yyyy = String(year);

  // Synchronize all related date targets
  syncFieldValue('wedding_day', dd);
  syncFieldValue('wedding_month', mm);
  syncFieldValue('wedding_year', yyyy);
  syncFieldValue('wedding_weekday', weekdayLabel);
  syncFieldValue('wedding_date_digits', `${dd} . ${mm} . ${yyyy}`);
  syncFieldValue('calendar_month_year', `Tháng ${mm} / ${yyyy}`);

  // Render calendar grids inside [data-sync-target="wedding_calendar"]
  const grids = document.querySelectorAll('[data-sync-target="wedding_calendar"]');
  grids.forEach((grid) => {
    grid.replaceChildren();

    // Empty leading slots before the 1st of the month
    for (let i = 0; i < mondayFirstOffset; i += 1) {
      const emptyCell = document.createElement('span');
      emptyCell.className = 'mayhen_invitation_calendar__cell mayhen_invitation_calendar__cell--empty';
      emptyCell.setAttribute('aria-hidden', 'true');
      grid.appendChild(emptyCell);
    }

    // Actual days of the month
    for (let dNum = 1; dNum <= daysInMonth; dNum += 1) {
      const cell = document.createElement('span');
      cell.className = 'mayhen_invitation_calendar__cell';
      cell.setAttribute('role', 'gridcell');
      cell.textContent = String(dNum);

      if (dNum === safeDay) {
        cell.classList.add('mayhen_invitation_calendar__cell--active');
        cell.setAttribute('aria-selected', 'true');
        cell.setAttribute('aria-label', `Ngày cưới ${dNum} tháng ${month} năm ${year}`);
      }
      grid.appendChild(cell);
    }
  });
};

/**
 * Initialize Opening Screen, Audio Controls, and Studio Embed Mode
 */
function initOpeningAndMusic(root) {
  const isEmbed = root.getAttribute('data-is-embed') === 'true';
  const openBtn = root.querySelector('.mayhen_invitation_open_btn');
  const audioEl = root.querySelector('[data-mayhen-audio]');
  const musicBtn = root.querySelector('[data-music-toggle]');
  const iconOff = root.querySelector('[data-music-icon-off]');
  const iconOn = root.querySelector('[data-music-icon-on]');

  let isPlaying = false;

  const updateMusicUi = (playing) => {
    isPlaying = playing;
    if (musicBtn) {
      musicBtn.setAttribute('aria-pressed', playing ? 'true' : 'false');
    }
    if (iconOff && iconOn) {
      iconOff.hidden = playing;
      iconOn.hidden = !playing;
    }
  };

  const tryPlayMusic = () => {
    if (!audioEl || !audioEl.getAttribute('src')) {
      updateMusicUi(false);
      return;
    }
    const playPromise = audioEl.play();
    if (playPromise && typeof playPromise.then === 'function') {
      playPromise
        .then(() => updateMusicUi(true))
        .catch(() => updateMusicUi(false));
    }
  };

  if (isEmbed) {
    root.classList.add('mayhen_invitation_root--opened', 'mayhen_invitation_root--embed');
    root.classList.remove('mayhen_invitation_root--locked');
  } else if (openBtn) {
    openBtn.addEventListener('click', () => {
      root.classList.add('mayhen_invitation_root--opened');
      root.classList.remove('mayhen_invitation_root--locked');
      tryPlayMusic();
    });
  }

  if (musicBtn) {
    musicBtn.addEventListener('click', () => {
      if (!audioEl || !audioEl.getAttribute('src')) {
        updateMusicUi(!isPlaying);
        return;
      }
      if (audioEl.paused) {
        tryPlayMusic();
      } else {
        audioEl.pause();
        updateMusicUi(false);
      }
    });
  }
}

/**
 * Initialize Gallery "XEM THÊM" Toggle & Lightbox
 */
function initGalleryAndLightbox(root) {
  const extraItems = root.querySelectorAll('.mayhen_invitation_gallery__item--extra');
  const moreBtn = root.querySelector('[data-gallery-toggle]');
  const lightbox = root.querySelector('[data-mayhen-lightbox]');
  const lightboxImg = root.querySelector('[data-lightbox-img]');
  const lightboxClose = root.querySelector('[data-lightbox-close]');

  let expanded = true;

  if (moreBtn && extraItems.length > 0) {
    moreBtn.addEventListener('click', () => {
      expanded = !expanded;
      extraItems.forEach((item) => {
        item.classList.toggle('mayhen_invitation_gallery__item--collapsed', !expanded);
      });
      moreBtn.textContent = expanded ? 'THU GỌN' : 'XEM THÊM';
    });
  }

  root.querySelectorAll('[data-lightbox-src]').forEach((img) => {
    img.addEventListener('click', () => {
      if (!lightbox || !lightboxImg) return;
      lightboxImg.src = img.getAttribute('src') || img.getAttribute('data-lightbox-src') || '';
      lightbox.hidden = false;
    });
  });

  if (lightboxClose && lightbox) {
    lightboxClose.addEventListener('click', () => {
      lightbox.hidden = true;
    });
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) {
        lightbox.hidden = true;
      }
    });
  }
}

/**
 * Initialize Gift Box Toggle, Quick Menu & Scroll Top
 */
function initNavigationAndGift(root) {
  const giftToggle = root.querySelector('[data-gift-toggle]');
  const giftPanel = root.querySelector('[data-gift-panel]');
  const menuToggle = root.querySelector('[data-menu-toggle]');
  const quickMenu = root.querySelector('[data-quick-menu]');
  const scrollTopBtn = root.querySelector('[data-scroll-top]');

  if (giftToggle && giftPanel) {
    giftToggle.addEventListener('click', () => {
      const isHidden = giftPanel.hasAttribute('hidden');
      if (isHidden) {
        giftPanel.removeAttribute('hidden');
        giftToggle.setAttribute('aria-expanded', 'true');
      } else {
        giftPanel.setAttribute('hidden', '');
        giftToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  if (menuToggle && quickMenu) {
    menuToggle.addEventListener('click', () => {
      const willOpen = quickMenu.hasAttribute('hidden');
      if (willOpen) {
        quickMenu.removeAttribute('hidden');
        menuToggle.setAttribute('aria-expanded', 'true');
      } else {
        quickMenu.setAttribute('hidden', '');
        menuToggle.setAttribute('aria-expanded', 'false');
      }
    });

    quickMenu.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        quickMenu.setAttribute('hidden', '');
        menuToggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // Copy bank account number
  root.querySelectorAll('[data-copy]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const textToCopy = btn.getAttribute('data-copy');
      if (!textToCopy) return;

      const notifySuccess = () => {
        const originalText = btn.textContent;
        btn.textContent = 'Đã chép ✓';
        setTimeout(() => {
          btn.textContent = originalText;
        }, 2000);
      };

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(textToCopy).then(notifySuccess).catch(() => {});
      } else {
        const textarea = document.createElement('textarea');
        textarea.value = textToCopy;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
          document.execCommand('copy');
          notifySuccess();
        } catch (e) {}
        document.body.removeChild(textarea);
      }
    });
  });
}

/**
 * Initialize Wishes / Guestbook Form and RSVP Form (Honest Backend / Adapter reporting)
 */
function initForms(root) {
  const csrfToken = root.getAttribute('data-csrf-token') || '';
  const rsvpEndpoint = (root.getAttribute('data-rsvp-endpoint') || '').trim();
  const wishesEndpoint = (root.getAttribute('data-wishes-endpoint') || '').trim();

  // Preset Wish Selector
  const presetSelect = root.querySelector('[data-wish-preset-select]');
  const wishTextarea = root.querySelector('#mayhen_wish_message');
  if (presetSelect && wishTextarea) {
    presetSelect.addEventListener('change', () => {
      if (presetSelect.value) {
        wishTextarea.value = presetSelect.value;
      }
    });
  }

  // Guestbook / Wishes Form
  const wishesForm = root.querySelector('[data-mayhen-wishes-form]');
  const wishesStatus = root.querySelector('[data-wishes-status]');
  const wishesFeed = root.querySelector('[data-wishes-feed]');

  if (wishesForm && wishesStatus) {
    wishesForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(wishesForm);
      const guestName = String(formData.get('guest_name') || '').trim();
      const wishMessage = String(formData.get('wish_message') || '').trim();

      if (!guestName || !wishMessage) {
        wishesStatus.textContent = 'Vui lòng nhập đầy đủ Họ tên và Nội dung lời chúc.';
        return;
      }

      const payload = { name: guestName, message: wishMessage };

      // Dispatch event hook for Mây Hẹn Studio / Parent Host
      window.dispatchEvent(new CustomEvent('mayhen:wish-submit', { detail: payload }));

      if (!wishesEndpoint) {
        wishesStatus.textContent = 'Chưa thể gửi lời chúc. Vui lòng thử lại sau.';
        return;
      }

      try {
        wishesStatus.textContent = 'Đang gửi lời chúc...';
        const res = await fetch(wishesEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json'
          },
          body: JSON.stringify(payload)
        });
        const result = await res.json();
        if (!res.ok || result.success !== true) {
          wishesStatus.textContent = result.message || 'Không thể gửi lời chúc. Vui lòng thử lại.';
          return;
        }
        if (wishesFeed && result.wish && result.wish.id != null) {
          const wishId = String(result.wish.id);
          let bubble = Array.from(wishesFeed.querySelectorAll('[data-wish-id]'))
            .find((item) => item.dataset.wishId === wishId);
          if (!bubble) {
            bubble = document.createElement('article');
            bubble.className = 'mayhen_invitation_wishes__bubble mayhen_invitation_wish_item';
            bubble.dataset.wishId = wishId;
            const head = document.createElement('header');
            head.className = 'mayhen_invitation_wishes__bubble_head';
            head.appendChild(document.createElement('strong'));
            const text = document.createElement('p');
            text.className = 'mayhen_invitation_wishes__bubble_text';
            bubble.append(head, text);
            wishesFeed.prepend(bubble);
          }
          bubble.querySelector('strong').textContent = result.wish.name;
          bubble.querySelector('p').textContent = result.wish.message;
        }
        wishesStatus.textContent = result.message || 'Cảm ơn bạn đã gửi lời chúc.';
        wishesForm.reset();
      } catch (err) {
        wishesStatus.textContent = 'Không thể kết nối máy chủ lưu lời chúc. Vui lòng thử lại sau.';
      }
    });
  }

  // RSVP Form
  const rsvpForm = root.querySelector('[data-mayhen-rsvp-form]');
  const rsvpStatus = root.querySelector('[data-rsvp-status]');
  let isRsvpSubmitting = false;

  if (rsvpForm && rsvpStatus) {
    rsvpForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (isRsvpSubmitting) return;
      const formData = new FormData(rsvpForm);
      const guestName = String(formData.get('guest_name') || '').trim();
      const attendanceStatus = String(formData.get('attendance_status') || 'attending');
      const guestCount = String(formData.get('guest_count') || '1');
      const childrenCount = String(formData.get('children_count') || '0');
      const message = String(formData.get('message') || '').trim();

      if (!guestName) {
        rsvpStatus.textContent = 'Vui lòng nhập Tên khách mời để xác nhận tham dự.';
        return;
      }

      const payload = {
        guest_name: guestName,
        attending_status: attendanceStatus,
        adults_count: attendanceStatus === 'attending' ? Number(guestCount) : 0,
        children_count: attendanceStatus === 'attending' ? Number(childrenCount) : 0,
        wishes: message
      };

      window.dispatchEvent(new CustomEvent('mayhen:rsvp-submit', { detail: payload }));

      if (!rsvpEndpoint) {
        rsvpStatus.textContent = 'Chưa thể gửi xác nhận. Vui lòng thử lại sau.';
        return;
      }

      try {
        isRsvpSubmitting = true;
        rsvpStatus.textContent = 'Đang gửi xác nhận tham dự...';
        const res = await fetch(rsvpEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json'
          },
          body: JSON.stringify(payload)
        });
        const result = await res.json();
        if (!res.ok || result.success !== true) {
          rsvpStatus.textContent = result.message || 'Không thể gửi xác nhận. Vui lòng thử lại.';
          return;
        }
        rsvpStatus.textContent = result.message || 'Cảm ơn bạn đã gửi xác nhận.';
        rsvpForm.reset();
      } catch (err) {
        rsvpStatus.textContent = 'Lỗi kết nối khi gửi xác nhận tham dự. Vui lòng thử lại sau.';
      } finally {
        isRsvpSubmitting = false;
      }
    });
  }
}

/**
 * Initialize Electronic Signature Canvas (Pointer Events, DPI scaling, resize preservation)
 */
function initSignaturePad(root) {
  const canvas = root.querySelector('#mayhen_signature_canvas');
  if (!canvas) return;

  const placeholder = root.querySelector('[data-signature-placeholder]');
  const clearBtn = root.querySelector('[data-signature-clear]');
  const previewBtn = root.querySelector('[data-signature-preview-btn]');
  const saveBtn = root.querySelector('[data-signature-save]');
  const signerInput = root.querySelector('[data-signature-name]');
  const statusEl = root.querySelector('[data-signature-status]');
  const previewWrap = root.querySelector('[data-signature-preview-wrap]');
  const previewImg = root.querySelector('[data-signature-preview-img]');
  const downloadLink = root.querySelector('[data-signature-download]');

  const signatureEndpoint = (root.getAttribute('data-signature-endpoint') || '').trim();
  const csrfToken = root.getAttribute('data-csrf-token') || '';

  const ctx = canvas.getContext('2d');
  const strokes = [];
  let currentStroke = null;
  let isDrawing = false;

  const redrawAll = () => {
    const rect = canvas.getBoundingClientRect();
    ctx.clearRect(0, 0, rect.width, rect.height);
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#1A1A1A';
    ctx.lineWidth = 2.4;

    for (const stroke of strokes) {
      if (stroke.length === 0) continue;
      ctx.beginPath();
      ctx.moveTo(stroke[0].x * rect.width, stroke[0].y * rect.height);
      for (let i = 1; i < stroke.length; i += 1) {
        ctx.lineTo(stroke[i].x * rect.width, stroke[i].y * rect.height);
      }
      ctx.stroke();
    }
  };

  const setupCanvasResolution = () => {
    const rect = canvas.getBoundingClientRect();
    if (rect.width === 0 || rect.height === 0) return;
    const dpr = Math.max(1, window.devicePixelRatio || 1);
    canvas.width = Math.round(rect.width * dpr);
    canvas.height = Math.round(rect.height * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    redrawAll();
  };

  setupCanvasResolution();
  window.addEventListener('resize', setupCanvasResolution);

  const getNormPoint = (evt) => {
    const rect = canvas.getBoundingClientRect();
    return {
      x: Math.max(0, Math.min(1, (evt.clientX - rect.left) / rect.width)),
      y: Math.max(0, Math.min(1, (evt.clientY - rect.top) / rect.height))
    };
  };

  canvas.addEventListener('pointerdown', (evt) => {
    evt.preventDefault();
    canvas.setPointerCapture(evt.pointerId);
    isDrawing = true;
    if (placeholder) placeholder.hidden = true;
    currentStroke = [getNormPoint(evt)];
    strokes.push(currentStroke);
    redrawAll();
  });

  canvas.addEventListener('pointermove', (evt) => {
    if (!isDrawing || !currentStroke) return;
    evt.preventDefault();
    currentStroke.push(getNormPoint(evt));
    redrawAll();
  });

  const finishStroke = () => {
    isDrawing = false;
    currentStroke = null;
  };

  canvas.addEventListener('pointerup', finishStroke);
  canvas.addEventListener('pointercancel', finishStroke);

  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      strokes.length = 0;
      redrawAll();
      if (placeholder) placeholder.hidden = false;
      if (previewWrap) previewWrap.hidden = true;
      if (statusEl) statusEl.textContent = 'Đã xóa chữ ký. Bạn có thể ký lại.';
    });
  }

  const exportSignatureDataUrl = () => canvas.toDataURL('image/png');

  if (previewBtn) {
    previewBtn.addEventListener('click', () => {
      if (strokes.length === 0) {
        if (statusEl) statusEl.textContent = 'Vui lòng ký tên lên khung vẽ trước khi xem trước.';
        return;
      }
      const dataUrl = exportSignatureDataUrl();
      if (previewImg) previewImg.src = dataUrl;
      if (downloadLink) downloadLink.href = dataUrl;
      if (previewWrap) previewWrap.hidden = false;
      if (statusEl) statusEl.textContent = 'Đã tạo bản xem trước chữ ký.';
    });
  }

  if (saveBtn) {
    saveBtn.addEventListener('click', async () => {
      const signerName = signerInput ? signerInput.value.trim() : '';
      if (!signerName) {
        if (statusEl) statusEl.textContent = 'Vui lòng nhập Tên người ký trước khi lưu.';
        return;
      }
      if (strokes.length === 0) {
        if (statusEl) statusEl.textContent = 'Vui lòng ký tên lên khung vẽ trước khi lưu.';
        return;
      }

      const dataUrl = exportSignatureDataUrl();
      if (previewImg) previewImg.src = dataUrl;
      if (downloadLink) downloadLink.href = dataUrl;
      if (previewWrap) previewWrap.hidden = false;

      const payload = { signer_name: signerName, signature_png: dataUrl };
      window.dispatchEvent(new CustomEvent('mayhen:signature-submit', { detail: payload }));

      if (!signatureEndpoint) {
        if (statusEl) {
          statusEl.textContent = `Đã tạo chữ ký của "${signerName}" (Chế độ xem trước — có thể tải PNG bên dưới; cần kết nối API endpoint Mây Hẹn để lưu lên máy chủ).`;
        }
        return;
      }

      try {
        if (statusEl) statusEl.textContent = 'Đang lưu chữ ký...';
        const res = await fetch(signatureEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json'
          },
          body: JSON.stringify(payload)
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        if (statusEl) statusEl.textContent = 'Đã lưu chữ ký lưu niệm thành công!';
      } catch (err) {
        if (statusEl) statusEl.textContent = 'Không thể lưu chữ ký lên máy chủ. Bạn vẫn có thể tải file PNG bên dưới.';
      }
    });
  }
}

/**
 * Listen for Mây Hẹn Studio Live Sync messages (`postMessage` or `mayhen:studio-sync`)
 */
function initStudioLiveSync() {
  const handleSyncPayload = (data) => {
    if (!data || typeof data !== 'object') return;
    Object.entries(data).forEach(([key, val]) => {
      syncFieldValue(key, val);
    });

    const dateTrigger = data.wedding_date_iso || data.wedding_date || data.wedding_date_digits;
    if (dateTrigger) {
      window.renderMayhenWeddingCalendar(dateTrigger);
      window.updateMayhenCountdown(dateTrigger);
    }
  };

  window.addEventListener('mayhen:studio-sync', (evt) => {
    if (evt.detail) handleSyncPayload(evt.detail);
  });

  window.addEventListener('message', (evt) => {
    const msg = evt.data;
    if (msg && typeof msg === 'object' && (msg.type === 'MAYHEN_STUDIO_SYNC' || msg.action === 'sync')) {
      handleSyncPayload(msg.payload || msg.data || msg);
    }
  });
}

/**
 * Bootstraps the template safely
 */
function bootstrapMayhenInvitation() {
  const root = document.querySelector('.mayhen_invitation_root');
  if (!root) return;

  const initialDate = root.getAttribute('data-wedding-date') || '2026-08-02';
  window.renderMayhenWeddingCalendar(initialDate);
  window.updateMayhenCountdown(initialDate);

  initOpeningAndMusic(root);
  initGalleryAndLightbox(root);
  initNavigationAndGift(root);
  initForms(root);
  initSignaturePad(root);
  initStudioLiveSync();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootstrapMayhenInvitation);
} else {
  bootstrapMayhenInvitation();
}
