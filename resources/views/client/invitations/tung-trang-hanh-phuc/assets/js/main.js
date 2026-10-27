(() => {
  'use strict';
  const C = window.MAYHEN_INVITATION_KSNVO949DUDF_CONFIG;
  if (!C) { console.error('Thiếu file config.js'); return; }
  const P = 'mayhen_invitation_ksnvo949dudf_';
  const $ = (id) => document.getElementById(P + id);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const storageBase = 'mayhen_ksnvo949dudf_v1_';
  const storage = {
    get(key, fallback) { try { return JSON.parse(localStorage.getItem(storageBase + key)) ?? fallback; } catch { return fallback; } },
    set(key, value) { try { localStorage.setItem(storageBase + key, JSON.stringify(value)); return true; } catch { return false; } }
  };
  const formatLong = (date) => new Intl.DateTimeFormat('vi-VN', {weekday:'long', day:'2-digit', month:'long', year:'numeric', timeZone:'Asia/Ho_Chi_Minh'}).format(date);
  const formatDigits = (date) => new Intl.DateTimeFormat('en-GB', {day:'2-digit',month:'2-digit',year:'numeric',timeZone:'Asia/Ho_Chi_Minh'}).format(date).replaceAll('/',' . ');
  let toastTimeout;
  function toast(message) {
    const node = $('toast');
    node.textContent = message;
    node.classList.add(P + 'toast-visible');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => node.classList.remove(P + 'toast-visible'), 3500);
  }
  function setText(selector, value) { $$(selector).forEach((el) => { el.textContent = String(value); }); }
  function updateContent() {
    const wedding = new Date(C.weddingDate);
    const dateValid = !Number.isNaN(wedding.getTime());
    const guestParams = new URLSearchParams(location.search);
    const nameOverride = (guestParams.get('guest') || '').trim().slice(0,80);
    const guest = nameOverride || C.guestName;
    const short = `${C.bride} & ${C.groom}`;
    setText('[data-guest="name"]',guest);
    setText('[data-couple="short"]',short);
    setText('[data-couple="groom"]',C.groom);
    setText('[data-couple="bride"]',C.bride);
    setText('[data-family="groom-father"]', C.family.groomFather);
    setText('[data-family="groom-mother"]', C.family.groomMother);
    setText('[data-family="bride-father"]', C.family.brideFather);
    setText('[data-family="bride-mother"]', C.family.brideMother);
    if (dateValid) {
      setText('[data-wedding="date-long"]',formatDigits(wedding));
      setText('[data-wedding="full-date"]',formatLong(wedding));
      setText('[data-wedding="year"]',new Intl.DateTimeFormat('en-US',{year:'numeric',timeZone:'Asia/Ho_Chi_Minh'}).format(wedding));
    }
    setText('[data-event="ceremony-time"]', C.events.ceremony.time);
    setText('[data-event="ceremony-venue"]', C.events.ceremony.venue);
    setText('[data-event="ceremony-address"]', C.events.ceremony.address);
    setText('[data-event="party-time"]', C.events.party.time);
    setText('[data-event="party-venue"]', C.events.party.venue);
    setText('[data-event="party-address"]', C.events.party.address);
    $$('[data-map]').forEach((link) => {
      const url = C.events[link.dataset.map]?.mapUrl || '';
      if (url && /^https:\/\//.test(url)) {
        link.href = url;link.target = '_blank';link.rel='noopener noreferrer';link.removeAttribute('aria-disabled');
      } else {
        link.setAttribute('aria-disabled','true');
        link.addEventListener('click', (event) => {event.preventDefault(); toast('Bạn cần cập nhật liên kết bản đồ trong config.js.');});
      }
    });
    const schedule = $('schedule-items');schedule.replaceChildren();
    C.schedule.forEach(({time,label}) => {
      const li = document.createElement('li');
      const t = document.createElement('time');t.textContent = String(time);
      const span = document.createElement('span');span.textContent = String(label);
      li.append(t,span);schedule.append(li);
    });
    // Images are configured separately: each <img> has a local SVG default.
    const imageMap = [
      ['.mayhen_invitation_ksnvo949dudf_hero-photo img',C.images.hero],
      ['.mayhen_invitation_ksnvo949dudf_portrait-left img',C.images.groom],
      ['.mayhen_invitation_ksnvo949dudf_portrait-right img',C.images.bride],
      ['.mayhen_invitation_ksnvo949dudf_save-date-photo img',C.images.saveTheDate]
    ];
    imageMap.forEach(([selector,url])=>{const target=document.querySelector(selector);if(target&&url)target.src=url;});
    $$('.mayhen_invitation_ksnvo949dudf_gallery-photo img').forEach((img,i)=>{if(C.images.gallery[i])img.src=C.images.gallery[i];});
    document.title = `Thiệp cưới ${short} | Mây Hẹn`;
    document.querySelector('meta[name="description"]')?.setAttribute('content',`Trân trọng kính mời ${guest} chung vui trong lễ cưới của ${short}.`);
  }
  function initOpening() {
    const modal = $('opening');
    $('site').inert = true;
    document.body.classList.add(P+'scroll-lock');
    $('open-invitation').addEventListener('click', () => {
      if (modal.classList.contains(P + 'opening-animating')) return;
      modal.classList.add(P + 'opening-animating');
      setTimeout(() => {
        modal.classList.add(P + 'opened');
        modal.setAttribute('aria-hidden','true');
        document.body.classList.remove(P + 'scroll-lock');
        $('site').inert = false;
        $('hero-title').setAttribute('tabindex','-1');
        $('hero-title').focus({preventScroll:true});
      }, 1550);
    });
    // Skip link must bypass overlay, not leave keyboard focus behind a modal.
    document.querySelector('.mayhen_invitation_ksnvo949dudf_skip-link')?.addEventListener('click', () => {
      modal.classList.add(P + 'opened');document.body.classList.remove(P + 'scroll-lock');$('site').inert = false;
    });
  }
  function initScroll() {
    const reveal = $$('.mayhen_invitation_ksnvo949dudf_reveal');
    if (!('IntersectionObserver' in window)) reveal.forEach((el)=>el.classList.add(P+'revealed'));
    else {
      const observer = new IntersectionObserver((entries,obs) => {
        entries.forEach(({target,isIntersecting})=>{if(isIntersecting){target.classList.add(P+'revealed');obs.unobserve(target);}});
      },{rootMargin:'0px 0px -7% 0px',threshold:.08});
      reveal.forEach((el)=>observer.observe(el));
    }
    const onScroll = () => {
      const available = Math.max(document.documentElement.scrollHeight-innerHeight,1);
      $('progress').style.width = `${Math.max(0,Math.min(100,100*scrollY/available))}%`;
      $('header').classList.toggle(P+'scrolled',scrollY>35);
      $('to-top').style.opacity = scrollY>350?'1':'.55';
    };
    addEventListener('scroll',onScroll,{passive:true});onScroll();
    $('to-top').addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));
    const menu = $('menu-toggle'),nav=$('mobile-nav');
    menu.addEventListener('click',()=>{const opening=menu.getAttribute('aria-expanded')!=='true';menu.setAttribute('aria-expanded',String(opening));nav.hidden=!opening;});
    $$('a[href^="#"]',nav).forEach((a)=>a.addEventListener('click',()=>{menu.setAttribute('aria-expanded','false');nav.hidden=true;}));
    addEventListener('resize',()=>{if(innerWidth>760){nav.hidden=true;menu.setAttribute('aria-expanded','false');}});
  }
  function initCountdown() {
    const target = new Date(C.weddingDate).getTime();
    const update = () => {
      const remain = Math.max(0,target-Date.now());
      const sec = Math.floor(remain/1000);
      $('days').textContent = String(Math.floor(sec/86400)).padStart(3,'0');
      $('hours').textContent = String(Math.floor(sec%86400/3600)).padStart(2,'0');
      $('minutes').textContent = String(Math.floor(sec%3600/60)).padStart(2,'0');
      $('seconds').textContent = String(sec%60).padStart(2,'0');
      if (remain<=0) $('countdown-message').textContent='Ngày hạnh phúc đã đến. Cảm ơn bạn đã ở đây!';
    };
    update();setInterval(update,1000);
  }
  function safeText(key, def) { const val=String(key ?? def ?? '').trim();return val.length>0?val:String(def || ''); }
  function initRSVP() {
    const form=$('rsvp-form'),status=$('rsvp-status'),guests=$('guest-count-wrap');
    const stored = storage.get('rsvp',null);
    if(stored){form.elements.name.value=stored.name;form.elements.note.value=stored.note||'';form.elements.guests.value=String(stored.guests||1);const radio=form.querySelector(`input[value="${stored.attendance==='no'?'no':'yes'}"]`);if(radio)radio.checked=true;status.textContent='Đã lưu lựa chọn của bạn trên trình duyệt này.';}
    const updateGuests=()=>{const attend=form.querySelector('input[name="attendance"]:checked')?.value;guests.hidden=attend==='no';};
    form.addEventListener('change',updateGuests);updateGuests();
    form.addEventListener('submit',(event)=>{
      event.preventDefault();if(!form.reportValidity())return;
      const data = {name:safeText(form.elements.name.value,''),attendance:form.querySelector('input[name="attendance"]:checked')?.value||'yes',guests:Number(form.elements.guests.value)||1,note:form.elements.note.value.trim().slice(0,500),time:new Date().toISOString()};
      const saved=storage.set('rsvp',data);
      status.textContent=saved?'Đã lưu xác nhận (chỉ trên thiết bị này). Chưa gửi đến cô dâu chú rể.':'Trình duyệt không cho phép lưu dữ liệu. Vui lòng thử lại.';
      toast(saved?'Đã lưu xác nhận trên trình duyệt của bạn.':'Không thể lưu dữ liệu demo.');
    });
  }
  function renderWishes() {
    const container=$('wish-list');
    const current = storage.get('wishes',[]);
    container.replaceChildren();
    const defaults = [{name:'Một người bạn thân',content:'Chúc hai bạn một đời bình yên, luôn nắm tay nhau qua mọi mùa yêu thương.'},{name:'Bạn của đôi mình',content:'Mãi hạnh phúc nhé! Chúc mừng ngày tuyệt đẹp của hai bạn.'}];
    [...current,...defaults].slice(0,50).forEach(({name,content})=>{
      const card=document.createElement('blockquote');const body=document.createElement('p'),footer=document.createElement('footer');
      body.textContent=content;footer.textContent=`— ${name}`;card.append(body,footer);container.append(card);
    });
  }
  function initWishes() {
    renderWishes();const form=$('wish-form');
    form.addEventListener('submit',(e)=>{
      e.preventDefault();if(!form.reportValidity())return;
      const current=storage.get('wishes',[]);
      const data={name:form.elements.name.value.trim().slice(0,80),content:form.elements.content.value.trim().slice(0,800)};
      if(data.name.length<1||data.content.length<1)return;
      const saved=storage.set('wishes',[data,...current].slice(0,48));
      if(saved){form.reset();renderWishes();$('wish-status').textContent='Đã lưu lời chúc trên trình duyệt này (bản demo).';toast('Lời chúc đã được lưu trên thiết bị này ♡');}
      else {$('wish-status').textContent='Không thể lưu lời chúc trên trình duyệt.';}
    });
  }
  function initGift() {
    const mount=$('gift-accounts');
    const list=(C.bankAccounts||[]).filter(a=>a.number&&a.bank&&a.holder);
    if(list.length===0)return;
    $('gift-message').textContent='Nếu muốn gửi lời chúc mừng từ xa, bạn có thể chọn thông tin dưới đây.';
    list.forEach((account)=>{
      const article=document.createElement('div');article.className=P+'gift-account';
      const title=document.createElement('strong');title.textContent=account.label||'Mừng cưới';
      const bank=document.createElement('span');bank.textContent=account.bank;
      const holder=document.createElement('span');holder.textContent=account.holder;
      const number=document.createElement('span');number.textContent=account.number;
      const button=document.createElement('button');button.type='button';button.textContent='Sao chép số tài khoản';
      button.addEventListener('click',()=>copyText(account.number).then(()=>toast('Đã sao chép số tài khoản.')).catch(()=>toast('Không thể sao chép trên thiết bị này.')));
      article.append(title,bank,holder,number,button);mount.append(article);
    });
  }
  async function copyText(text) {
    if(navigator.clipboard&&window.isSecureContext)return navigator.clipboard.writeText(text);
    const el=document.createElement('textarea');el.value=text;el.style.position='fixed';el.style.left='-10000px';document.body.append(el);el.select();const ok=document.execCommand('copy');el.remove();if(!ok)throw new Error('Clipboard unsupported');
  }
  function initGallery() {
    const files=C.images.gallery || [];
    const modal=$('lightbox'),large=$('lightbox-image');let index=0,lastFocus=null;
    const update=()=>{large.src=files[index];large.alt=`Ảnh cưới ${index+1}`;$('lightbox-counter').textContent=`${String(index+1).padStart(2,'0')} / ${String(files.length).padStart(2,'0')}`;};
    const open=(n)=>{if(!files[n])return;lastFocus=document.activeElement;index=n;update();modal.hidden=false;document.body.classList.add(P+'scroll-lock');$('lightbox-close').focus();};
    const close=()=>{modal.hidden=true;document.body.classList.remove(P+'scroll-lock');lastFocus?.focus?.();};
    $$('.mayhen_invitation_ksnvo949dudf_gallery-photo').forEach((button,i)=>button.addEventListener('click',()=>open(i)));
    $('lightbox-close').addEventListener('click',close);
    $('lightbox-next').addEventListener('click',()=>{index=(index+1)%files.length;update();});
    $('lightbox-prev').addEventListener('click',()=>{index=(index+files.length-1)%files.length;update();});
    modal.addEventListener('click',(e)=>{if(e.target===modal)close();});
    addEventListener('keydown',(e)=>{
      if(modal.hidden)return;
      if(e.key==='Escape')close();
      if(e.key==='ArrowRight'){$('lightbox-next').click();e.preventDefault();}
      if(e.key==='ArrowLeft'){$('lightbox-prev').click();e.preventDefault();}
      if(e.key==='Tab'){
        const focusable=[$('lightbox-close'),$('lightbox-prev'),$('lightbox-next')];
        const idx=focusable.indexOf(document.activeElement);
        const next=(idx+(e.shiftKey?-1:1)+focusable.length)%focusable.length;
        focusable[next].focus();e.preventDefault();
      }
    });
  }
  function initCalendar() {
    $('calendar-download').addEventListener('click',()=>{
      const date=new Date(C.weddingDate);if(Number.isNaN(date.getTime()))return toast('Ngày cưới chưa được cấu hình.');
      const formatter=(d)=>d.toISOString().replace(/[-:]/g,'').replace(/\.\d{3}/,'');
      const end=new Date(date.getTime()+3*3600*1000);
      const ics=['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//Mayhen//Wedding Invitation//VI','BEGIN:VEVENT',`UID:mayhen-${date.getTime()}@local`,`DTSTAMP:${formatter(new Date())}`,`DTSTART:${formatter(date)}`,`DTEND:${formatter(end)}`,`SUMMARY:${C.groom} & ${C.bride} - Le Cuoi`,`DESCRIPTION:Wedding invitation - Mayhen`,'END:VEVENT','END:VCALENDAR'].join('\r\n');
      const blob=new Blob([ics],{type:'text/calendar;charset=utf-8'}),url=URL.createObjectURL(blob),a=document.createElement('a');
      a.href=url;a.download='save-the-date.ics';document.body.append(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(url),1000);
    });
  }
  function initShare() {
    $('share').addEventListener('click',async()=>{
      const data={title:document.title,text:`Thiệp cưới ${C.groom} & ${C.bride}`,url:location.href};
      try {if(navigator.share){await navigator.share(data);return;}await copyText(data.url);toast('Đã sao chép liên kết thiệp cưới.');}
      catch(e){if(e.name!=='AbortError')toast('Không thể chia sẻ trên trình duyệt này.');}
    });
  }
  function initMusic() {
    if(!C.musicUrl)return;
    const control=$('music'),audio=$('audio');audio.src=C.musicUrl;control.hidden=false;
    control.addEventListener('click',async()=>{
      if(audio.paused){try{await audio.play();control.textContent='♫';control.setAttribute('aria-label','Tắt nhạc nền');toast('Đã bật nhạc nền.');}catch{toast('Trình duyệt không thể phát tệp nhạc này.');}}
      else{audio.pause();control.textContent='♪';control.setAttribute('aria-label','Bật nhạc nền');}
    });
  }
  updateContent();
  initOpening();initScroll();initCountdown();initRSVP();initWishes();initGift();initGallery();initCalendar();initShare();initMusic();
})();
