/**
 * MAYHEN.VN — FORGOT PASSWORD LOGIC
 * JavaScript thuần (Vanilla JS Native ES Modules)
 * Zero external libraries
 */
(() => {
  'use strict';

  const userConfig = window.MAYHEN_FORGOT_PASSWORD_CONFIG || {};

  const MAYHEN_CONFIG = Object.freeze({
    resetEndpoint: userConfig.resetEndpoint || '/quen-mat-khau',
    homeUrl: userConfig.homeUrl || '/',
    loginUrl: userConfig.loginUrl || '/dang-nhap',
    registerUrl: userConfig.registerUrl || '/dang-ky',
    supportUrl: userConfig.supportUrl || '/lien-he',
    resendCooldownSeconds: userConfig.resendCooldownSeconds || 60,
    requestTimeoutMs: userConfig.requestTimeoutMs || 12000
  });

  const $ = (id) => document.getElementById('mayhen_forgot_password_' + id);
  const page = $('page');
  if (!page) return;
  const brandName = document.querySelector('meta[name="application-name"]')?.content?.trim() || '';

  const form = $('form');
  const email = $('email');
  const error = $('error');
  const submit = $('submit');
  const submitText = $('submit_text');
  const clear = $('clear');
  const requestStage = $('request_stage');
  const successStage = $('success_stage');
  const preview = $('preview');
  const demoBadge = $('demo_badge');
  const resend = $('resend');
  const resendTimer = $('resend_timer');
  const backdrop = $('modal_backdrop');
  const dialog = $('modal');
  const content = $('modal_content');
  const toast = $('toast');

  let toastTimeout = null;
  let lastFocused = null;
  let lastEmail = (window.MAYHEN_SENT_EMAIL || '').trim();
  let timerId = null;
  let cooldownUntil = 0;
  let isDemo = false;
  let sending = false;

  const memory = { theme: 'light', scale: 1, motion: 'on' };
  const linkMap = {
    home: MAYHEN_CONFIG.homeUrl,
    login: MAYHEN_CONFIG.loginUrl,
    register: MAYHEN_CONFIG.registerUrl
  };

  document.querySelectorAll('[data-config-link]').forEach((a) => {
    const key = a.dataset.configLink;
    if (typeof linkMap[key] === 'string') a.setAttribute('href', linkMap[key]);
  });

  function setError(message) {
    if (error) error.textContent = message || '';
    if (email) email.setAttribute('aria-invalid', message ? 'true' : 'false');
  }

  function normalizeEmail(value) {
    return String(value || '').trim();
  }

  function isValidEmail(value) {
    if (!value || value.length > 254 || /\s/.test(value)) return false;
    return /^[^@\s]+@[^@\s]+\.[^@\s]{2,}$/u.test(value) && value.split('@')[0].length <= 64;
  }

  function showToast(message) {
    if (!toast) return;
    window.clearTimeout(toastTimeout);
    toast.textContent = message;
    toast.dataset.open = 'true';
    toastTimeout = window.setTimeout(() => {
      toast.dataset.open = 'false';
    }, 4400);
  }

  function setLoading(loading) {
    sending = loading;
    if (submit) {
      submit.disabled = loading;
      submit.dataset.loading = String(loading);
      submit.setAttribute('aria-busy', String(loading));
    }
    if (submitText) {
      submitText.textContent = loading ? 'Đang gửi mã xác thực...' : 'Gửi liên kết khôi phục';
    }
  }

  function showRequest() {
    clearInterval(timerId);
    if (requestStage) requestStage.hidden = false;
    if (successStage) successStage.hidden = true;
    if (demoBadge) demoBadge.hidden = true;
    if (resend) resend.hidden = true;
    isDemo = false;
    setError('');
    requestAnimationFrame(() => email && email.focus());
  }

  function showConfirmation(address, demo = false) {
    if (requestStage) requestStage.hidden = true;
    if (successStage) successStage.hidden = false;
    const successEmailEl = $('success_email');
    if (successEmailEl) successEmailEl.textContent = address || 'email của bạn';

    const descEl = $('success_description');
    if (descEl) {
      descEl.textContent = demo
        ? 'Đây chỉ là bản xem trước trạng thái xác nhận. Chưa có email nào được gửi.'
        : (brandName
          ? `Nếu địa chỉ này có tài khoản ${brandName}, bạn sẽ nhận được email hướng dẫn đặt lại mật khẩu.`
          : 'Nếu địa chỉ này có tài khoản, bạn sẽ nhận được email hướng dẫn đặt lại mật khẩu.');
    }

    if (demoBadge) demoBadge.hidden = !demo;
    isDemo = demo;

    if (!demo) {
      if (resend) resend.hidden = false;
      startCooldown();
    } else {
      if (resend) resend.hidden = true;
    }

    const editBtn = $('edit_email');
    if (editBtn) requestAnimationFrame(() => editBtn.focus());
  }

  function startCooldown() {
    clearInterval(timerId);
    cooldownUntil = Date.now() + MAYHEN_CONFIG.resendCooldownSeconds * 1000;
    const tick = () => {
      const seconds = Math.max(0, Math.ceil((cooldownUntil - Date.now()) / 1000));
      if (resend) resend.disabled = seconds > 0 || sending;
      if (resendTimer) resendTimer.textContent = seconds ? '(' + seconds + 's)' : '';
      if (seconds === 0) clearInterval(timerId);
    };
    tick();
    timerId = window.setInterval(tick, 1000);
  }

  function readCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      || form?.querySelector('input[name="_token"]')?.value
      || '';
  }

  async function sendEmail(address) {
    if (!MAYHEN_CONFIG.resetEndpoint) {
      showToast('Chưa cấu hình API khôi phục mật khẩu.');
      return;
    }
    if (sending) return;
    setLoading(true);

    const controller = new AbortController();
    const deadline = window.setTimeout(() => controller.abort(), MAYHEN_CONFIG.requestTimeoutMs);

    try {
      const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      };
      const csrf = readCsrf();
      if (csrf) headers['X-CSRF-TOKEN'] = csrf;

      const result = await fetch(MAYHEN_CONFIG.resetEndpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers,
        body: JSON.stringify({ email: address }),
        signal: controller.signal,
        cache: 'no-store'
      });

      if (result.status === 419) {
        showToast('Phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
        return;
      }
      if (result.status === 429) {
        const data = await result.json().catch(() => ({}));
        const msg = data.message || 'Bạn thao tác quá nhanh. Vui lòng thử lại sau ít phút.';
        showToast(msg);
        setError(msg);
        return;
      }
      if (result.status === 422) {
        const data = await result.json().catch(() => ({}));
        const msg = data.message || (data.errors && data.errors.email ? data.errors.email[0] : null) || 'Yêu cầu không hợp lệ.';
        showToast(msg);
        setError(msg);
        return;
      }

      if (result.ok) {
        lastEmail = address;
        showConfirmation(address, false);
      } else {
        const data = await result.json().catch(() => ({}));
        const msg = data.message || (data.errors && data.errors.email ? data.errors.email[0] : null) || 'Không thể xử lý yêu cầu lúc này. Vui lòng thử lại sau.';
        showToast(msg);
        setError(msg);
      }
    } catch (e) {
      showToast(e?.name === 'AbortError' ? 'Kết nối quá thời gian. Vui lòng thử lại.' : 'Không thể kết nối máy chủ. Kiểm tra kết nối Internet và thử lại.');
    } finally {
      window.clearTimeout(deadline);
      setLoading(false);
    }
  }

  if (form) {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const value = normalizeEmail(email?.value);
      if (email) email.value = value;
      if (!value) {
        setError('Vui lòng nhập địa chỉ email để tiếp tục.');
        if (email) email.focus();
        return;
      }
      if (!isValidEmail(value)) {
        setError('Email chưa đúng định dạng. Ví dụ: tenban@example.com');
        if (email) email.focus();
        return;
      }
      setError('');
      void sendEmail(value);
    });
  }

  if (email) {
    email.addEventListener('input', () => {
      setError('');
      if (clear) clear.hidden = !email.value;
    });
  }

  if (clear) {
    clear.addEventListener('click', () => {
      if (email) {
        email.value = '';
        email.focus();
      }
      clear.hidden = true;
      setError('');
    });
  }

  const editEmailBtn = $('edit_email');
  if (editEmailBtn) {
    editEmailBtn.addEventListener('click', () => {
      if (!sending) {
        if (email) {
          email.value = lastEmail || email.value;
          if (clear) clear.hidden = !email.value;
        }
        showRequest();
      }
    });
  }

  if (resend) {
    resend.addEventListener('click', () => {
      if (!sending && !isDemo && Date.now() >= cooldownUntil && lastEmail) {
        void sendEmail(lastEmail);
      }
    });
  }

  if (preview) {
    preview.addEventListener('click', () => {
      lastEmail = normalizeEmail(email?.value);
      showConfirmation(lastEmail || 'tenban@example.com', true);
    });
  }

  const modalMeta = {
    guide: ['Hướng dẫn', '3 bước khôi phục mật khẩu', 'mayhen_forgot_password_icon_help'],
    noemail: ['Giải đáp thắc mắc', 'Bạn chưa nhận được email?', 'mayhen_forgot_password_icon_mail'],
    security: ['An toàn tài khoản', 'Một vài lời nhắc bảo mật', 'mayhen_forgot_password_icon_shield'],
    support: [brandName ? `Luôn có ${brandName}` : 'Luôn đồng hành cùng bạn', 'Chúng mình có thể giúp gì?', 'mayhen_forgot_password_icon_help'],
    privacy: ['Quyền riêng tư', 'Thông tin của bạn luôn quan trọng', 'mayhen_forgot_password_icon_lock'],
    settings: ['Trải nghiệm của bạn', 'Tùy chỉnh cách hiển thị', 'mayhen_forgot_password_icon_sliders']
  };

  function openModal(key) {
    if (!modalMeta[key]) return;
    const template = $('template_' + key);
    if (!template) return;
    lastFocused = document.activeElement;
    const eyebrow = $('modal_eyebrow');
    const title = $('modal_title');
    const ornament = $('modal_ornament');

    if (eyebrow) eyebrow.textContent = modalMeta[key][0];
    if (title) title.textContent = modalMeta[key][1];
    if (ornament) {
      const useEl = ornament.querySelector('use');
      if (useEl) useEl.setAttribute('href', '#' + modalMeta[key][2]);
    }
    if (content) content.replaceChildren(template.content.cloneNode(true));
    if (backdrop) backdrop.hidden = false;

    if (key === 'settings') syncSettings();
    if (key === 'support') {
      const link = $('support_link');
      if (link) link.href = MAYHEN_CONFIG.supportUrl;
    }
    const closeBtn = $('modal_close');
    if (closeBtn) closeBtn.focus();
  }

  function closeModal() {
    if (!backdrop || backdrop.hidden) return;
    backdrop.hidden = true;
    if (content) content.replaceChildren();
    if (lastFocused?.isConnected) lastFocused.focus();
  }

  document.querySelectorAll('[data-modal]').forEach((button) => {
    button.addEventListener('click', () => openModal(button.dataset.modal));
  });

  const modalCloseBtn = $('modal_close');
  if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);

  if (backdrop) {
    backdrop.addEventListener('mousedown', (event) => {
      if (event.target === backdrop) closeModal();
    });
  }

  function getFocusable() {
    if (!dialog) return [];
    return [...dialog.querySelectorAll('button:not(:disabled),a[href],textarea,input:not(:disabled),select:not(:disabled),[tabindex]:not([tabindex="-1"])')]
      .filter((el) => !el.hidden && el.getClientRects().length > 0);
  }

  document.addEventListener('keydown', (event) => {
    if (!backdrop || backdrop.hidden) return;
    if (event.key === 'Escape') {
      event.preventDefault();
      closeModal();
    }
    if (event.key === 'Tab') {
      const focusables = getFocusable();
      if (!focusables.length) return;
      const first = focusables[0];
      const last = focusables[focusables.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  });

  if (backdrop) {
    backdrop.addEventListener('click', (event) => {
      if (event.target.closest('[data-close-modal]')) closeModal();
      const themeButton = event.target.closest('[data-set-theme]');
      if (themeButton) setTheme(themeButton.dataset.setTheme);
      const scaleButton = event.target.closest('[data-set-scale]');
      if (scaleButton) setScale(Number(scaleButton.dataset.setScale));
      if (event.target.closest('#mayhen_forgot_password_motion_switch')) {
        setMotion(memory.motion === 'on' ? 'off' : 'on');
      }
      if (event.target.closest('#mayhen_forgot_password_support_copy')) {
        const msgEl = $('support_message');
        const message = (msgEl ? msgEl.value : '').trim();
        if (!message) {
          showToast('Hãy nhập nội dung cần hỗ trợ trước khi sao chép.');
          return;
        }
        const safeMessage = (brandName ? `Yêu cầu hỗ trợ ${brandName}: ` : 'Yêu cầu hỗ trợ: ') + message;
        if (navigator.clipboard?.writeText) {
          void navigator.clipboard.writeText(safeMessage)
            .then(() => showToast('Đã sao chép nội dung. Hãy dán vào trang hỗ trợ.'))
            .catch(() => fallbackCopy(safeMessage));
        } else {
          fallbackCopy(safeMessage);
        }
      }
    });
  }

  function fallbackCopy(value) {
    const textarea = document.createElement('textarea');
    textarea.value = value;
    textarea.setAttribute('readonly', '');
    textarea.style.cssText = 'position:fixed;left:-9999px;top:0';
    document.body.appendChild(textarea);
    textarea.select();
    let ok = false;
    try {
      ok = document.execCommand('copy');
    } catch {}
    textarea.remove();
    showToast(ok ? 'Đã sao chép nội dung hỗ trợ.' : 'Trình duyệt không cho phép sao chép tự động.');
  }

  function syncSettings() {
    if (!content) return;
    content.querySelectorAll('[data-set-theme]').forEach((b) => {
      b.setAttribute('aria-pressed', String(b.dataset.setTheme === memory.theme));
    });
    content.querySelectorAll('[data-set-scale]').forEach((b) => {
      b.setAttribute('aria-pressed', String(Number(b.dataset.setScale) === memory.scale));
    });
    const motion = $('motion_switch');
    if (motion) motion.setAttribute('aria-checked', String(memory.motion === 'on'));
  }

  function setTheme(value) {
    memory.theme = value === 'dark' ? 'dark' : 'light';
    if (page) page.dataset.theme = memory.theme;
    document.body.dataset.mayhenTheme = memory.theme;
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', memory.theme === 'dark' ? '#201c20' : '#faf7f3');
    syncSettings();
  }

  function setScale(value) {
    memory.scale = [.94, 1, 1.1].includes(value) ? value : 1;
    document.documentElement.style.setProperty('--mayhen-forgot-password-scale', String(memory.scale));
    syncSettings();
  }

  function setMotion(value) {
    memory.motion = value === 'off' ? 'off' : 'on';
    if (page) page.dataset.motion = memory.motion;
    syncSettings();
  }

  const themeToggle = $('theme_toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', () => setTheme(memory.theme === 'light' ? 'dark' : 'light'));
  }

  if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) setMotion('off');

  // Nếu người dùng vừa gửi request thành công qua form POST truyền thống
  if (window.MAYHEN_SENT_EMAIL) {
    showConfirmation(window.MAYHEN_SENT_EMAIL, false);
  }
})();
