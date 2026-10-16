/* MAYHEN / Graceful 04 — vanilla JS, no Next.js / jQuery / third-party runtime. */
(() => {
  'use strict';

  const P = 'mayhen_invitation_djcvhwiuo9w9_';
  const C = name => `.${P}${name}`;
  const $ = (selector, parent = document) => parent.querySelector(selector);
  const $$ = (selector, parent = document) => Array.from(parent.querySelectorAll(selector));
  const root = $(C('root'));
  if (!root) return;

  const KEYS = { guestbook: `${P}guestbook_v1`, rsvp: `${P}rsvp_v1` };
  const STORAGE_LIMIT = 80;
  const MAX_GUESTS = 20;
  const WEDDING_DATE = (window.__MAYHEN_WEDDING_TARGET_TIME) ? new Date(window.__MAYHEN_WEDDING_TARGET_TIME) : new Date('2027-03-07T07:00:00+07:00');
    const accounts = {
    bride: window.__MAYHEN_BRIDE_ACCOUNT || null,
    groom: window.__MAYHEN_GROOM_ACCOUNT || null
  };
  const state = { attending: true, people: 1, activeSide: 'bride', galleryIndex: 0, modalMode: null, activeGift: 'bride' };
  let storedComments = [];
  let lastFocused = null;
  let toastTimer;

  function readStorage(key, fallback) {
    try { const stored = localStorage.getItem(key); return stored ? JSON.parse(stored) : fallback; }
    catch { return fallback; }
  }
  function saveStorage(key, value) {
    try { localStorage.setItem(key, JSON.stringify(value)); return true; }
    catch { return false; }
  }
  function escapeHTML(text) {
    return String(text).replace(/[&<>"']/g, char => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[char]));
  }
  function toast(message) {
    const el = $(`#${P}toast`);
    if (!el) return;
    el.textContent = message;
    el.classList.add(`${P}show`);
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove(`${P}show`), 3600);
  }
  function setBodyScrollLock(locked) { document.body.classList.toggle(`${P}no-scroll`, locked); }

  // Progressive reveal: remove hiding entirely if motion or IntersectionObserver is unsupported.
  if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    root.classList.add(`${P}motion-ready`);
    const observer = new IntersectionObserver(entries => {
      for (const entry of entries) {
        if (entry.isIntersecting) {
          entry.target.classList.add(`${P}is-visible`);
          observer.unobserve(entry.target);
        }
      }
    }, { threshold: 0.065, rootMargin: '0px 0px -25px 0px' });
    $$(C('reveal'), root).forEach(el => observer.observe(el));
  }

  // Countdown (days : hours : minutes : seconds).
  const values = $$(C('countdown-piece') + ' ' + C('value'));
  const updateCountdown = () => {
    const remaining = Math.max(0, Math.floor((WEDDING_DATE.getTime() - Date.now()) / 1000));
    const day = Math.floor(remaining / 86400);
    const hours = Math.floor(remaining % 86400 / 3600);
    const minutes = Math.floor(remaining % 3600 / 60);
    const seconds = remaining % 60;
    [day, hours, minutes, seconds].forEach((value, index) => {
      if (values[index]) values[index].textContent = String(value).padStart(index === 0 ? 3 : 2, '0');
    });
  };
  updateCountdown();
  setInterval(updateCountdown, 1000);

  // Invitation side selector. Original invitation data displays both events; preserve both.
  const sideSwitch = $(C('side-switch'));
  if (sideSwitch) {
    const buttons = $$('button', sideSwitch);
    function selectSide(i) {
      buttons.forEach((btn, j) => {
        btn.classList.toggle(`${P}active`, i === j);
        btn.setAttribute('aria-pressed', String(i === j));
      });
      state.activeSide = i === 0 ? 'bride' : 'groom';
      root.dataset.side = state.activeSide;
      const events = $$(C('invitation-event-block'));
      events.forEach((event, j) => event.classList.toggle(`${P}selected-event`, i === j));
    }
    buttons.forEach((btn, i) => btn.addEventListener('click', () => selectSide(i)));
    selectSide(0);
  }

  // Guest personalization is optional: ?to=... or ?guest=...
  try {
    const params = new URLSearchParams(location.search);
    const name = (params.get('to') || params.get('guest') || '').trim().slice(0, 70);
    if (name) {
      const target = $(C('invitation-guest-name'));
      if (target) target.textContent = name;
    }
  } catch (_) { /* local file URLs may omit query parameters */ }

  // Headline fitting: shrink genuinely long strings only, never over-expand fonts.
  function fitNames() {
    $$('[data-fit-text="true"]').forEach(el => {
      el.style.removeProperty('font-size');
      const parentWidth = el.parentElement.clientWidth;
      if (el.scrollWidth > parentWidth + 2 && parentWidth > 0) {
        const computed = parseFloat(getComputedStyle(el).fontSize);
        el.style.fontSize = Math.max(15, computed * Math.max(.67, parentWidth / el.scrollWidth - .015)) + 'px';
      }
    });
  }
  document.fonts?.ready?.then(fitNames);
  addEventListener('resize', fitNames, {passive:true});

  // Modal framework — backdrop, X and Escape close, focus restored.
  const modal = $(`#${P}modal`);
  const modalPanel = $(C('modal-panel'), modal);
  const modalBody = $(`#${P}modal-body`);
  function openModal(markup, mode) {
    const lightbox = $(`#${P}lightbox`);
    if (lightbox?.getAttribute('aria-hidden') === 'false') closeLightbox();
    lastFocused = document.activeElement;
    state.modalMode = mode;
    modalBody.innerHTML = markup;
    modal.setAttribute('aria-hidden', 'false');
    setBodyScrollLock(true);
    modalPanel.focus();
  }
  function closeModal() {
    modal.setAttribute('aria-hidden', 'true');
    state.modalMode = null;
    modalBody.replaceChildren();
    setBodyScrollLock(false);
    if (lastFocused?.isConnected) lastFocused.focus();
  }
  $$('[data-modal-close]', modal).forEach(el => el.addEventListener('click', closeModal));

  // Album lightbox: next, previous, keyboard and touch swipe.
  const gallery = $$(C('photo-grid') + ' ' + C('photo') + ' img');
  const lightbox = $(`#${P}lightbox`);
  const lightboxImage = $(C('lightbox-image'), lightbox);
  const lbCounter = $(C('lightbox-counter'), lightbox);
  function renderLightbox() {
    const photo = gallery[state.galleryIndex];
    if (!photo) return;
    lightboxImage.src = photo.currentSrc || photo.src;
    lightboxImage.alt = photo.alt || `Ảnh cưới ${state.galleryIndex + 1}`;
    lbCounter.textContent = `${state.galleryIndex + 1} / ${gallery.length}`;
  }
  function openLightbox(index) {
    lastFocused = document.activeElement;
    state.galleryIndex = index;
    renderLightbox();
    lightbox.setAttribute('aria-hidden','false');
    setBodyScrollLock(true);
    $(C('lightbox-close'), lightbox)?.focus();
  }
  function closeLightbox() {
    lightbox.setAttribute('aria-hidden','true');
    setBodyScrollLock(false);
    if (lastFocused?.isConnected) lastFocused.focus();
  }
  function advanceGallery(direction) {
    if (!gallery.length) return;
    state.galleryIndex = (state.galleryIndex + direction + gallery.length) % gallery.length;
    renderLightbox();
  }
  gallery.forEach((img, i) => img.closest('button')?.addEventListener('click', () => openLightbox(i)));
  $(C('lightbox-close'),lightbox)?.addEventListener('click', closeLightbox);
  $(C('lightbox-prev'),lightbox)?.addEventListener('click', () => advanceGallery(-1));
  $(C('lightbox-next'),lightbox)?.addEventListener('click', () => advanceGallery(1));
  lightbox.addEventListener('click', event => { if(event.target === lightbox) closeLightbox(); });
  let swipeStartX = null;
  lightbox.addEventListener('touchstart',e => { swipeStartX=e.changedTouches[0]?.clientX??null; }, {passive:true});
  lightbox.addEventListener('touchend',e => {
    const x = e.changedTouches[0]?.clientX;
    if(x != null && swipeStartX != null && Math.abs(x-swipeStartX)>45) advanceGallery(x < swipeStartX ? 1:-1);
    swipeStartX=null;
  }, {passive:true});
  document.addEventListener('keydown', event => {
    if(event.key==='Escape') {
      if(modal.getAttribute('aria-hidden')==='false')closeModal();
      else if(lightbox.getAttribute('aria-hidden')==='false')closeLightbox();
    }
    if(lightbox.getAttribute('aria-hidden')==='false') {
      if(event.key==='ArrowLeft')advanceGallery(-1);
      if(event.key==='ArrowRight')advanceGallery(1);
    }
  });

  // Draw signature using pointer events; clients cannot submit to a real server in static mode.
  function initSignaturePad(canvas) {
    if(!canvas)return { clear:()=>{}, toDataURL:()=>null };
    const ctx = canvas.getContext('2d');
    let hasDrawing=false, drawing=false;
    function resetCanvas() {
      const ratio = Math.min(devicePixelRatio||1,2);
      const rect = canvas.getBoundingClientRect();
      const width=Math.max(1,Math.round(rect.width*ratio));
      const height=Math.max(1,Math.round(rect.height*ratio));
      canvas.width=width;canvas.height=height;
      ctx.setTransform(ratio,0,0,ratio,0,0);
      ctx.lineCap='round';ctx.lineJoin='round';ctx.strokeStyle='#1c1c1c';ctx.lineWidth=2.25;
      ctx.fillStyle='#fafafa';ctx.fillRect(0,0,rect.width,rect.height);
      hasDrawing=false;
    }
    function position(event) {
      const rect=canvas.getBoundingClientRect();
      return {x:event.clientX-rect.left,y:event.clientY-rect.top};
    }
    canvas.addEventListener('pointerdown', event=>{
      drawing=true;hasDrawing=true;canvas.setPointerCapture(event.pointerId);
      const p=position(event);ctx.beginPath();ctx.moveTo(p.x,p.y);ctx.lineTo(p.x+.1,p.y+.1);ctx.stroke();
    });
    canvas.addEventListener('pointermove', event=>{
      if(!drawing)return;
      const p=position(event);ctx.lineTo(p.x,p.y);ctx.stroke();
    });
    ['pointerup','pointercancel','lostpointercapture'].forEach(event=>canvas.addEventListener(event,()=>drawing=false));
    resetCanvas();
    return { clear:resetCanvas, toDataURL:()=>hasDrawing?canvas.toDataURL('image/png'):null };
  }
  const commentsContainer=$(C('comment-list'));
  const signatureBoard=$(C('signature-board'));
  const signatureEmpty=$(C('signature-board-empty'));
  const guestCount=$(C('guestbook-board-heading')+' small');
  function paintComments() {
    if(guestCount)guestCount.textContent=`${storedComments.length} chữ ký`;
    if(signatureEmpty)signatureEmpty.hidden=storedComments.length>0;
    let stickerLayer=$(C('signature-stickers'),signatureBoard);
    if(!stickerLayer) { stickerLayer=document.createElement('div');stickerLayer.className=`${P}signature-stickers`;signatureBoard.append(stickerLayer); }
    stickerLayer.replaceChildren();
    storedComments.slice(0,8).forEach((item,index)=>{
      const sticker=document.createElement('div');
      sticker.className=`${P}signature-sticker`;
      sticker.style.left=`${8+(index%3)*29}%`;
      sticker.style.top=`${9+Math.floor(index/3)*26}%`;
      sticker.style.setProperty('--rotation', `${[-5,7,-3,6,-7,3,4,-2][index]}deg`);
      if(item.signature?.startsWith('data:image/png;base64,')){
        const image=document.createElement('img');image.src=item.signature;image.alt='Chữ ký';sticker.append(image);
      }
      const name=document.createElement('span');name.textContent=item.name;sticker.append(name);stickerLayer.append(sticker);
    });
    $$('.'+P+'comment-new',commentsContainer).forEach(el=>el.remove());
    storedComments.forEach(item=>{
      const article=document.createElement('article');article.className=`${P}comment-item ${P}comment-new`;
      const header=document.createElement('div');header.className=`${P}comment-item-header`;
      const author=document.createElement('div');author.className=`${P}comment-author`;
      const strong=document.createElement('strong');strong.textContent=item.name;author.append(strong);
      const time=document.createElement('time');time.textContent=item.date;
      header.append(author,time);
      const message=document.createElement('p');message.textContent=item.message;
      article.append(header,message);commentsContainer.prepend(article);
    });
  }
  storedComments=readStorage(KEYS.guestbook,[]);
  if(!Array.isArray(storedComments))storedComments=[];
  storedComments=storedComments.slice(-STORAGE_LIMIT).filter(x => x && typeof x.name==='string' && typeof x.message==='string');
  paintComments();
  $(C('guestbook-start-button'))?.addEventListener('click',()=>{
    openModal(`<h2 id="${P}modal-title">Ký tên & gửi lời chúc</h2>
      <p>Gửi lời chúc đến Tràng Đức và Ngọc Phượng. Nội dung sẽ được lưu trên trình duyệt hiện tại.</p>
      <form id="${P}signature-form">
        <label class="${P}field">Tên của bạn<input name="name" required maxlength="50" placeholder="Tên của bạn"></label>
        <label class="${P}field">Lời chúc<textarea name="message" required maxlength="500" placeholder="Chúc hai bạn trăm năm hạnh phúc..."></textarea></label>
        <label class="${P}field">Chữ ký viết tay<canvas class="${P}signature-pad" aria-label="Vùng ký tên bằng tay hoặc chuột"></canvas></label>
        <div class="${P}modal-actions"><button class="${P}outline-button" type="button" id="${P}clear-signature">Xóa chữ ký</button><button class="${P}form-button" type="submit">Lưu lời chúc</button></div>
      </form>`, 'signature');
    const canvas=$(C('signature-pad'),modalBody);
    const pad=initSignaturePad(canvas);
    $(`#${P}clear-signature`)?.addEventListener('click',pad.clear);
    $(`#${P}signature-form`)?.addEventListener('submit',event=>{
      event.preventDefault();
      const form=event.currentTarget;
      const name=form.elements.name.value.trim();
      const message=form.elements.message.value.trim();
            if (!name || !message) { toast('Vui lòng điền tên và lời chúc.'); return; }
      const entry = { name, message, date: new Date().toLocaleDateString('vi-VN'), signature: pad.toDataURL() };
      storedComments = [...storedComments, entry].slice(-STORAGE_LIMIT);
      const saved = saveStorage(KEYS.guestbook, storedComments);
      paintComments();
      closeModal();
      
      // Gửi đồng bộ lên backend Mây Hẹn nếu có API
      if (window.__MAYHEN_WISH_SUBMIT_URL) {
        const formData = new FormData();
        formData.append('guest_name', name);
        formData.append('wishes', message);
        formData.append('signature', pad.toDataURL());
        fetch(window.__MAYHEN_WISH_SUBMIT_URL, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': window.__MAYHEN_CSRF_TOKEN || '',
            'Accept': 'application/json'
          },
          body: formData
        }).then(r => r.json()).then(res => {
          if (res.success) toast('Cảm ơn bạn! Lời chúc đã được gửi thành công.');
        }).catch(() => {});
      } else {
        toast(saved ? 'Đã lưu lời chúc trên thiết bị của bạn.' : 'Đã thêm lời chúc tạm thời.');
      }
    });
  });

  // RSVP demonstration: state is local and is not sent to the couple.
  const rsvpButtons=$$(C('rsvp-options')+' button');
  const attendanceRow=$(C('attendance-row'));
  const countEl=$(C('attendance-stepper')+' strong');
  const updateRSVP=()=>{
    rsvpButtons.forEach((button,i)=>{
      const active = i === (state.attending?0:1);
      button.classList.toggle(`${P}active`,active);button.setAttribute('aria-pressed',String(active));
    });
    if(attendanceRow)attendanceRow.hidden=!state.attending;
    if(countEl)countEl.textContent=state.people;
  };
  rsvpButtons.forEach((button,i)=>button.addEventListener('click',()=>{state.attending=i===0;updateRSVP();}));
  $$(C('attendance-stepper')+' button').forEach((button,i)=>button.addEventListener('click',()=>{
    state.people=Math.min(MAX_GUESTS,Math.max(1,state.people+(i===0?-1:1)));
    updateRSVP();
  }));
  const savedRsvp=readStorage(KEYS.rsvp,null);
  if(savedRsvp && typeof savedRsvp.attending==='boolean'){
    state.attending=savedRsvp.attending;
    state.people=Math.min(MAX_GUESTS,Math.max(1,Number(savedRsvp.people)||1));
  }
  updateRSVP();
  $(C('rsvp-submit'))?.addEventListener('click',()=>{
    openModal(`<h2 id="${P}modal-title">Xác nhận tham dự</h2>
      <p>Bạn chọn <strong>${state.attending?'Tôi sẽ đến':'Rất tiếc, tôi bận'}</strong>${state.attending?` · ${state.people} người`:''}.</p>
      <p>Bản HTML tĩnh này chỉ lưu xác nhận trên thiết bị; để cô dâu chú rể nhận phản hồi, cần kết nối backend.</p>
      <form id="${P}rsvp-form">
        <label class="${P}field">Tên người xác nhận<input maxlength="80" name="name" required placeholder="Họ và tên" value="${escapeHTML(savedRsvp?.name||'')}"></label>
        <div class="${P}modal-actions"><button class="${P}form-button" type="submit">Lưu trên thiết bị</button></div>
      </form>`, 'rsvp');
    $(`#${P}rsvp-form`)?.addEventListener('submit',event=>{
      event.preventDefault();
      const name=event.currentTarget.elements.name.value.trim();
            if (!name) { toast('Vui lòng nhập họ và tên.'); return; }
      const saved = saveStorage(KEYS.rsvp, { attending: state.attending, people: state.attending ? state.people : 0, name, updated: new Date().toISOString() });
      closeModal();
      
      // Gửi đồng bộ lên backend Mây Hẹn nếu có API
      if (window.__MAYHEN_RSVP_SUBMIT_URL) {
        const rsvpData = new FormData();
        rsvpData.append('guest_name', name);
        rsvpData.append('attending_status', state.attending ? 'attending' : 'declined');
        rsvpData.append('adults_count', state.attending ? state.people : 0);
        fetch(window.__MAYHEN_RSVP_SUBMIT_URL, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': window.__MAYHEN_CSRF_TOKEN || '',
            'Accept': 'application/json'
          },
          body: rsvpData
        }).then(r => r.json()).then(res => {
          if (res.success) toast('Cảm ơn bạn! Phản hồi tham dự đã được ghi nhận.');
        }).catch(() => {});
      } else {
        toast(saved ? 'Đã lưu xác nhận trên trình duyệt của bạn.' : 'Trình duyệt chặn lưu xác nhận cục bộ.');
      }
    });
  });

  // Gift box: no fabricated payment/QR info; configure real verified bank account first.
  function drawGift(account) {
    state.activeGift=account;
    $$(C('gift-tabs')+' button',modalBody).forEach(button=>{
      const selected=button.dataset.side===account;
      button.classList.toggle(`${P}active`,selected);
      button.setAttribute('aria-pressed',String(selected));
    });
    const details=$(`#${P}gift-details`);
    const info=accounts[account];
    if(!info){details.innerHTML='<p>Thông tin tài khoản mừng cưới chưa được cấu hình. Vui lòng liên hệ trực tiếp cô dâu hoặc chú rể.</p>';return;}
    const qr=info.qr?`<img alt="Mã QR ngân hàng" style="max-width:220px;margin:14px auto" src="${escapeHTML(info.qr)}">`:'';
    details.innerHTML=`<strong>${escapeHTML(info.bank||'Ngân hàng')}</strong><div>${escapeHTML(info.name||'')}</div><div>${escapeHTML(info.number||'')}</div>${qr}<button class="${P}outline-button" data-copy-bank type="button">Sao chép số tài khoản</button>`;
    $('[data-copy-bank]',details)?.addEventListener('click',async()=>{
      try{await navigator.clipboard.writeText(info.number||'');toast('Đã sao chép số tài khoản.');}
      catch{toast('Không thể sao chép tự động.');}
    });
  }
  $(C('present-box')+' button')?.addEventListener('click',()=>{
    openModal(`<h2 id="${P}modal-title">Hộp mừng cưới</h2>
      <p>Sự hiện diện của bạn đã là món quà quý giá nhất.</p>
      <div class="${P}gift-tabs"><button type="button" data-side="bride">Cô dâu</button><button type="button" data-side="groom">Chú rể</button></div>
      <div class="${P}gift-details" id="${P}gift-details"></div>`, 'gift');
    $$(C('gift-tabs')+' button',modalBody).forEach(button=>button.addEventListener('click',()=>drawGift(button.dataset.side)));
    drawGift('bride');
  });

  // Remove broken <img> glyphs if remote-hosted images are unavailable.
  $$('img',root).forEach(img=>{
    img.addEventListener('error',()=>{
      img.classList.add(`${P}image-unavailable`);
      img.setAttribute('title','Không thể tải ảnh từ máy chủ nguồn');
    },{once:true});
  });
  // Trình phát nhạc nền Mây Hẹn
  const musicButton = $(C('music-toggle'));
  const audio = $(`#${P}audio`);
  if (musicButton && audio) {
    let isPlaying = false;
    const playMusic = () => {
      audio.play().then(() => {
        isPlaying = true;
        musicButton.classList.add(`${P}music-playing`);
        musicButton.setAttribute('aria-pressed', 'true');
        musicButton.setAttribute('aria-label', 'Tắt nhạc nền');
      }).catch(() => {});
    };
    const pauseMusic = () => {
      audio.pause();
      isPlaying = false;
      musicButton.classList.remove(`${P}music-playing`);
      musicButton.setAttribute('aria-pressed', 'false');
      musicButton.setAttribute('aria-label', 'Bật nhạc nền');
    };

    musicButton.addEventListener('click', (e) => {
      e.stopPropagation();
      if (isPlaying) pauseMusic();
      else playMusic();
    });

    // Thử tự động phát khi người dùng có tương tác đầu tiên trên trang
    const autoPlayOnce = () => {
      if (!isPlaying) playMusic();
      window.removeEventListener('click', autoPlayOnce);
      window.removeEventListener('touchstart', autoPlayOnce);
    };
    window.addEventListener('click', autoPlayOnce, { once: true, passive: true });
    window.addEventListener('touchstart', autoPlayOnce, { once: true, passive: true });
  }
})();
