/* MÂY HẸN DOCS | Pure JavaScript, no external dependencies.
   Demo content is intentionally local. Connect live docs, help requests and auth in Laravel. */
(() => {
  'use strict';
  const root = document.getElementById('mayhen_docs_app');
  if (!root) return;
  const $ = (selector) => root.querySelector(selector);
  const $$ = (selector) => Array.from(root.querySelectorAll(selector));

  const GUIDES = {
    'intro': {category:'Bắt đầu',title:'Mây Hẹn là gì?',summary:'Một không gian để thiết kế lời mời trực tuyến, tổ chức thông tin ngày cưới và kết nối với những người thân thương.',tags:'giới thiệu tài liệu thiệp cưới bắt đầu',steps:['Khám phá các nhóm hướng dẫn về thiết kế, khách mời và chia sẻ.','Xác định phong cách thiệp mong muốn: tối giản, cổ điển, lãng mạn hoặc hiện đại.','Chuẩn bị những thông tin cần thiết: tên hai bạn, thời gian, địa điểm và một vài hình ảnh.','Bắt đầu từ hướng dẫn tạo thiệp đầu tiên, sau đó kiểm tra giao diện trên điện thoại.'],tip:'Đừng cố hoàn thiện mọi chi tiết ngay lần đầu. Bạn có thể bắt đầu bằng một mẫu đơn giản.'},
    'first-card': {category:'Bắt đầu',title:'Tạo thiệp cưới online đầu tiên',summary:'Lộ trình ngắn gọn để biến ý tưởng thành một tấm thiệp cưới có thể xem trước và chuẩn bị chia sẻ.',tags:'tạo thiệp đầu tiên quickstart mới bắt đầu 5 phút',steps:['Chuẩn bị tên cô dâu, chú rể, ngày cưới và thông tin địa điểm.','Chọn một mẫu thiệp phù hợp với tông màu và phong cách buổi lễ.','Thay nội dung minh họa bằng thông tin của hai bạn; kiểm tra chính tả và múi giờ.','Thêm ảnh, lời nhắn và những chi tiết cần thiết, tránh khiến thiệp quá dài.','Xem trước trên cả máy tính lẫn điện thoại; xuất bản khi đã kiểm tra xong.'],tip:'Nên gửi liên kết xem thử cho người thân tin cậy để nhờ rà soát trước khi phát hành rộng rãi.'},
    'pick-template': {category:'Thiết kế',title:'Chọn mẫu thiệp phù hợp',summary:'Cách chọn mẫu thiệp cưới vừa đẹp vừa dễ đọc trên mọi kích thước màn hình.',tags:'template mẫu thiệp phong cách hiện đại hoa cưới',steps:['Chọn tông màu phù hợp với không khí lễ cưới và bộ nhận diện của hai bạn.','Ưu tiên mẫu có chữ rõ ràng, khoảng trắng thoáng và vị trí hiển thị thời gian, địa điểm nổi bật.','Xem thử bố cục trên điện thoại vì phần lớn khách mời có thể mở từ điện thoại.','Kiểm tra ảnh, hiệu ứng và nhạc nền có hỗ trợ tắt hoặc bỏ qua.'],tip:'Màu kem, hồng đất và champagne hợp với phong cách thanh lịch, nhưng độ tương phản vẫn quan trọng nhất.'},
    'publish': {category:'Chia sẻ',title:'Xuất bản thiệp cưới',summary:'Những việc cần rà soát trước khi đưa thiệp đến với khách mời.',tags:'xuất bản đăng thiệp publish phát hành',steps:['Rà soát họ tên, ngày giờ, địa chỉ, số điện thoại liên hệ và liên kết bản đồ.','Kiểm tra thông tin RSVP, nút xác nhận và khả năng hiển thị trên màn hình nhỏ.','Xác định dữ liệu nào được công khai và dữ liệu nào chỉ nên giữ riêng.','Khi tích hợp backend, lưu bản nháp và trạng thái xuất bản riêng biệt.','Sau khi xuất bản, thử mở liên kết trong chế độ ẩn danh trước khi chia sẻ.'],tip:'Bản demo hướng dẫn này không có chức năng xuất bản thật; phần đó cần kết nối với Laravel.'},
    'edit-content': {category:'Thiết kế',title:'Chỉnh sửa nội dung lời mời',summary:'Sắp xếp các phần thông tin quan trọng sao cho khách mời đọc nhanh và không bỏ sót.',tags:'nội dung văn bản lời mời tên ngày giờ địa điểm',steps:['Bắt đầu bằng lời chào ngắn gọn và tên hai bạn.','Ghi rõ ngày, giờ bắt đầu, địa điểm tổ chức và hướng dẫn di chuyển nếu cần.','Bổ sung nội dung về dress code, lưu ý khách mời hoặc lời nhắn riêng khi phù hợp.','Đọc thử trên điện thoại: tiêu đề ngắn, đoạn văn không quá dày và nút CTA đủ lớn.'],tip:'Thông tin địa điểm và thời gian nên nổi bật hơn các đoạn trang trí.'},
    'fonts-colors': {category:'Thiết kế',title:'Màu sắc và phông chữ',summary:'Phối màu thiệp cưới dịu mắt nhưng vẫn giữ được độ tương phản và tính nhất quán.',tags:'màu sắc font chữ typography hồng đất champagne',steps:['Chọn một màu chính, một màu nền trung tính và một màu nhấn vừa đủ.','Đảm bảo chữ trên nền sáng hoặc tối có độ tương phản tốt.','Dùng ít kiểu chữ: một kiểu cho tiêu đề, một kiểu dễ đọc cho nội dung.','Kiểm tra cỡ chữ, chiều cao dòng và nút bấm trên màn hình 320px.'],tip:'Trang demo đang dùng Open Sans từ Google Fonts, kèm font dự phòng khi không có mạng.'},
    'photos': {category:'Thiết kế',title:'Thêm ảnh và album cưới',summary:'Chuẩn bị ảnh sắc nét và tối ưu dung lượng để thiệp tải nhanh.',tags:'ảnh cưới photo album hình ảnh kỷ niệm upload',steps:['Chọn ảnh có sự đồng ý của người xuất hiện trong ảnh.','Cắt ảnh đúng tỷ lệ cho vị trí hiển thị, giữ khuôn mặt ở vùng quan trọng.','Xuất WebP hoặc AVIF khi phù hợp, giới hạn kích thước theo nhu cầu sử dụng.','Bổ sung mô tả ảnh khi cần để hỗ trợ khả năng tiếp cận.','Kiểm tra tải ảnh qua kết nối di động và cách hiển thị khi ảnh lỗi.'],tip:'Không đưa ảnh giấy tờ, địa chỉ nhà riêng hoặc dữ liệu nhạy cảm lên thiệp công khai.'},
    'music': {category:'Thiết kế',title:'Nhạc nền và hiệu ứng',summary:'Kết hợp chuyển động và âm thanh nhẹ nhàng mà không làm phiền khách mời.',tags:'nhạc nền âm nhạc chuyển động animation hiệu ứng',steps:['Ưu tiên nhạc có quyền sử dụng hợp lệ.','Cung cấp nút bật/tắt rõ ràng; không phụ thuộc vào khả năng tự phát của trình duyệt.','Giữ chuyển động tinh tế, ngắn gọn và tránh làm mất tập trung khi đọc.','Tôn trọng tùy chọn giảm chuyển động trong thiết bị của người xem.'],tip:'Trình duyệt thường giới hạn autoplay có âm thanh; hãy để người xem chủ động phát.'},
    'rsvp': {category:'Khách mời',title:'Thiết lập xác nhận tham dự (RSVP)',summary:'Một biểu mẫu ngắn, dễ dùng giúp bạn thu thập xác nhận tham dự rõ ràng.',tags:'rsvp tham dự xác nhận khách mời đồng ý từ chối',steps:['Xác định những trường cần thiết: tên khách, lựa chọn tham dự và số người đi cùng nếu được phép.','Thêm ngày chốt phản hồi và lời cảm ơn thân thiện sau khi gửi.','Đặt câu hỏi ngắn và phù hợp trên điện thoại.','Trong backend Laravel, xử lý trùng phản hồi, giới hạn tốc độ và kiểm tra CSRF khi cần.','Chỉ thu thập dữ liệu thực sự cần thiết, công bố rõ cách sử dụng thông tin.'],tip:'Không nên yêu cầu khách nhập quá nhiều thông tin cho một lời xác nhận đơn giản.'},
    'guest-list': {category:'Khách mời',title:'Quản lý danh sách khách mời',summary:'Sắp xếp khách mời và trạng thái xác nhận để theo dõi công việc dễ dàng.',tags:'khách mời danh sách group thống kê sự kiện',steps:['Lập danh sách theo nhóm như gia đình, bạn bè, đồng nghiệp.','Chuẩn hóa tên và thông tin liên hệ trước khi nhập dữ liệu.','Theo dõi trạng thái chưa trả lời, tham dự và không tham dự.','Đặt quyền truy cập danh sách cho những người có trách nhiệm tổ chức.'],tip:'Danh sách khách mời là dữ liệu riêng tư; không công khai trong mã nguồn frontend.'},
    'checkin': {category:'Khách mời',title:'Check-in khách mời bằng QR',summary:'Gợi ý quy trình đón tiếp với mã QR duy nhất và trạng thái check-in theo từng khách.',tags:'qr check in kiểm tra mã khách mời quét',steps:['Tạo mã định danh không dễ đoán cho từng vé mời trong backend.','Xác thực QR với máy chủ trước khi đánh dấu đã đến.','Lưu thời gian và người thao tác để dễ đối soát.','Cho phép phương án tìm thủ công nếu khách không mở được mã.'],tip:'Không nên lưu trực tiếp dữ liệu riêng tư dưới dạng QR không bảo vệ.'},
    'wishes': {category:'Khách mời',title:'Sổ lưu bút lời chúc',summary:'Tạo không gian để khách mời gửi những lời chúc chân thành.',tags:'lời chúc comment khách lưu bút gallery kỷ niệm',steps:['Đặt khu vực lời chúc ở vị trí khách dễ tìm.','Giữ biểu mẫu ngắn: tên hiển thị và lời nhắn.','Có bước kiểm duyệt, lọc spam và giới hạn tần suất ở backend.','Sao lưu lời chúc định kỳ để bảo vệ những kỷ niệm đẹp.'],tip:'Không hiển thị nội dung người dùng gửi dưới dạng HTML chưa được làm sạch.'},
    'share': {category:'Chia sẻ',title:'Chia sẻ thiệp cưới',summary:'Chọn kênh gửi phù hợp và đảm bảo liên kết mở nhanh, dễ hiểu.',tags:'chia sẻ zalo facebook messenger copy link lời mời',steps:['Kiểm tra bản xem trước và thông tin trước khi gửi cho khách.','Tạo liên kết ngắn gọn, ổn định và có giao diện chia sẻ thân thiện.','Thử mở liên kết trên điện thoại bằng trình duyệt khác.','Hạn chế chia sẻ công khai nếu trang chứa thông tin riêng tư.'],tip:'Bạn có thể dùng nút “Chia sẻ trang” trong footer để thử tính năng chia sẻ tài liệu này.'},
    'privacy': {category:'Chia sẻ',title:'Bảo mật và quyền riêng tư',summary:'Những nguyên tắc giúp bảo vệ dữ liệu người dùng, khách mời và những khoảnh khắc riêng tư.',tags:'bảo mật riêng tư quyền truy cập personal data thông tin cá nhân',steps:['Phân loại dữ liệu công khai và dữ liệu chỉ dành cho người tổ chức.','Xác thực và phân quyền phía server thay vì chỉ ẩn phần tử bằng CSS.','Dùng HTTPS, kiểm tra đầu vào và giới hạn truy cập API.','Đưa ra chính sách lưu trữ, xóa dữ liệu và thông báo quyền riêng tư minh bạch.'],tip:'Không đặt khóa API bí mật hoặc dữ liệu khách mời trong JavaScript của trang.'},
    'custom-link': {category:'Chia sẻ',title:'Tùy chỉnh liên kết thiệp',summary:'Tạo liên kết dễ nhớ, nhất quán và tránh xung đột với các địa chỉ đã tồn tại.',tags:'slug đường dẫn domain tên miền seo liên kết',steps:['Chọn slug ngắn, dễ gõ và phù hợp với cặp đôi.','Giới hạn ký tự hợp lệ và kiểm tra trùng ở backend.','Thiết lập canonical, metadata và hình xem trước cho trang công khai.','Khi thay đổi đường dẫn, sử dụng chuyển hướng 301 nếu URL cũ đã được chia sẻ.'],tip:'Tránh đưa số điện thoại hoặc dữ liệu nhận dạng nhạy cảm vào slug.'},
    'account': {category:'Hỗ trợ',title:'Tài khoản và trợ giúp',summary:'Các lưu ý khi quản lý tài khoản và tìm hỗ trợ trong quá trình làm thiệp.',tags:'tài khoản đăng nhập đăng ký hỗ trợ mật khẩu',steps:['Sử dụng mật khẩu mạnh và không chia sẻ thông tin đăng nhập.','Nếu quên mật khẩu, dùng luồng khôi phục có giới hạn thời gian và mã một lần.','Không gửi mật khẩu hoặc mã OTP trong nội dung góp ý.','Sử dụng ô tìm kiếm tài liệu và chức năng góp ý để ghi nhận vấn đề cần bổ sung.'],tip:'Các chức năng tài khoản thật cần tích hợp với hệ thống xác thực Laravel.'}
  };
  const guideIds = Object.keys(GUIDES);
  const CHECKLIST = ['Chốt ngày, giờ và địa điểm lễ cưới','Chọn mẫu thiệp phù hợp','Điền đầy đủ thông tin lời mời','Thêm album và kiểm tra ảnh','Tạo danh sách khách mời','Kiểm tra biểu mẫu RSVP','Xem trước và chia sẻ thiệp'];
  const SETTINGS_KEY='mayhen_docs_settings_v1';
  const BOOKMARKS_KEY='mayhen_docs_bookmarks_v1';
  const CHECKLIST_KEY='mayhen_docs_checklist_v1';
  const DATE_KEY='mayhen_docs_wedding_date_v1';
  const FEEDBACK_KEY='mayhen_docs_feedback_draft_v1';
  const RATING_KEY='mayhen_docs_rating_v1';
  const store = {
    get(key, fallback) { try {const val=localStorage.getItem(key);return val===null?fallback:JSON.parse(val);} catch {return fallback;} },
    set(key, value) { try {localStorage.setItem(key,JSON.stringify(value));return true;} catch {return false;} }
  };
  let settings = {...{theme:'light',font:16,width:'comfortable',motion:true},...store.get(SETTINGS_KEY,{})};
  if (!['light','dark','system'].includes(settings.theme)) settings.theme='light';
  if (!['comfortable','wide'].includes(settings.width)) settings.width='comfortable';
  settings.font=Math.max(14,Math.min(20,Number(settings.font)||16));
  settings.motion=settings.motion!==false;
  let bookmarks = store.get(BOOKMARKS_KEY,[]);
  if (!Array.isArray(bookmarks)) bookmarks=[];
  bookmarks=bookmarks.filter(id=>GUIDES[id]);
  let tasks=store.get(CHECKLIST_KEY,[]);
  if (!Array.isArray(tasks)) tasks=[];
  tasks=CHECKLIST.map((_,i)=>tasks[i]===true);
  let weddingDate=store.get(DATE_KEY,'');
  if (typeof weddingDate!=='string') weddingDate='';
  let currentGuide='first-card';
  let searchActiveIndex=0;
  let toastTimer;
  let returnFocus=null;
  const systemDark=window.matchMedia?.('(prefers-color-scheme: dark)');

  function toast(message) {
    const el=$('#mayhen_docs_toast');
    if (!el) return;
    window.clearTimeout(toastTimer);
    el.textContent=message;
    el.classList.add('mayhen_docs_toast_show');
    toastTimer=window.setTimeout(()=>el.classList.remove('mayhen_docs_toast_show'),3100);
  }
  function normalize(value){return String(value||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/đ/g,'d').replace(/Đ/g,'D').toLowerCase().trim();}
  function searchGuides(input){
    const stopwords=new Set(['lam','sao','de','toi','co','the','muon','nhu','nao','cach','gi','la','mot','va','cho','cua','ban','duoc','khong','voi','tren','trong','hay','ve','xin','hoi','nhung','nhieu','can','nen','tu']);
    const terms=normalize(input).replace(/[^a-z0-9\s]/g,' ').split(/\s+/).filter(term=>term.length>=2&&!stopwords.has(term));
    if (!terms.length) return ['first-card','rsvp','pick-template','photos','guest-list','privacy','share','wishes'];
    const requiredMatches=terms.length<=2?1:Math.ceil(terms.length*.38);
    return guideIds.map(id=>{
      const g=GUIDES[id];const title=normalize(g.title), tags=normalize(g.tags), desc=normalize(g.summary);
      let score=0,matched=0;
      for(const term of terms){
        if(title.includes(term)){score+=5;matched++;}
        else if(tags.includes(term)){score+=3;matched++;}
        else if(desc.includes(term)){score+=1;matched++;}
      }
      if(matched<requiredMatches)return null;
      if(title.startsWith(normalize(input))) score+=4;
      return {id,score,matched};
    }).filter(Boolean).sort((a,b)=>b.score-a.score||b.matched-a.matched||a.id.localeCompare(b.id)).map(x=>x.id);
  }
  function closeDialog(dialog){if(dialog?.open) dialog.close();}
  function openDialog(id){
    const dlg=document.getElementById(id);if(!dlg || typeof dlg.showModal!=='function') return;
    const opened=root.querySelector('dialog[open]'); if(opened && opened!==dlg) opened.close();
    if(dlg.open) return;
    returnFocus=document.activeElement;
    dlg.showModal();
    if(id==='mayhen_docs_dialog_search') {renderSearch();$('#mayhen_docs_search_input').focus();}
    if(id==='mayhen_docs_dialog_bookmarks') renderBookmarks();
    if(id==='mayhen_docs_dialog_checklist') renderChecklist();
    if(id==='mayhen_docs_dialog_countdown') updateCountdown();
    if(id==='mayhen_docs_dialog_share') $('#mayhen_docs_share_link').value=window.location.href;
    if(id==='mayhen_docs_dialog_settings') updateSettingsUI();
  }
  $$('dialog').forEach(dialog=>{
    dialog.addEventListener('click',event=>{
      if(event.target===dialog){const rect=dialog.getBoundingClientRect();if(event.clientX<rect.left || event.clientX>rect.right || event.clientY<rect.top || event.clientY>rect.bottom)dialog.close();}
    });
    dialog.addEventListener('close',()=>{try {if(returnFocus && returnFocus.isConnected && typeof returnFocus.focus==='function')returnFocus.focus({preventScroll:true});}catch{}});
  });
  $$('[data-close]').forEach(button=>button.addEventListener('click',()=>closeDialog(button.closest('dialog'))));
  $$('[data-modal]').forEach(button=>button.addEventListener('click',()=>{closeMobileMenu();closeLanguageMenu();openDialog(button.dataset.modal);}));

  function appendIcon(parent,name){
    const svg=document.createElementNS('http://www.w3.org/2000/svg','svg'); svg.setAttribute('class','mayhen_docs_icon');svg.setAttribute('aria-hidden','true');
    const use=document.createElementNS('http://www.w3.org/2000/svg','use');use.setAttribute('href','#mayhen_docs_i_'+name);svg.appendChild(use);parent.appendChild(svg);
  }
  function makeGuideResult(id,onClick){
    const g=GUIDES[id];const button=document.createElement('button');button.type='button';
    appendIcon(button,'book');const body=document.createElement('span');
    const title=document.createElement('strong'); title.textContent=g.title;
    const small=document.createElement('small');small.textContent=g.category+' · '+g.summary;
    body.append(title,small);button.append(body);appendIcon(button,'arrow');button.addEventListener('click',onClick||(()=>openGuide(id)));
    return button;
  }
  function renderSearch(){
    const container=$('#mayhen_docs_search_results');const query=$('#mayhen_docs_search_input').value;
    container.replaceChildren();const results=searchGuides(query);searchActiveIndex=0;
    if(!results.length){const empty=document.createElement('div');empty.className='mayhen_docs_no_results';empty.textContent='Chưa tìm thấy nội dung phù hợp. Thử từ khóa ngắn hơn hoặc gửi góp ý để bổ sung tài liệu.';container.append(empty);return;}
    results.slice(0,12).forEach((id,i)=>{
      const btn=makeGuideResult(id,()=>{closeDialog($('#mayhen_docs_dialog_search'));openGuide(id);});
      btn.setAttribute('role','option');btn.setAttribute('aria-selected',i===0?'true':'false');container.append(btn);
      btn.addEventListener('mouseenter',()=>{searchActiveIndex=i;highlightSearch(false);});
    });
  }
  function highlightSearch(scroll){
    const options=$$('#mayhen_docs_search_results>[role="option"]');
    options.forEach((button,index)=>button.setAttribute('aria-selected',index===searchActiveIndex?'true':'false'));
    if(scroll)options[searchActiveIndex]?.scrollIntoView({block:'nearest'});
  }
  $('#mayhen_docs_search_input').addEventListener('input',renderSearch);
  $('#mayhen_docs_search_input').addEventListener('keydown',event=>{
    const options=$$('#mayhen_docs_search_results>[role="option"]');
    if(event.key==='ArrowDown'){event.preventDefault();searchActiveIndex=(searchActiveIndex+1)%Math.max(1,options.length);highlightSearch(true);}
    else if(event.key==='ArrowUp'){event.preventDefault();searchActiveIndex=(searchActiveIndex-1+Math.max(1,options.length))%Math.max(1,options.length);highlightSearch(true);}
    else if(event.key==='Enter'&&options.length){event.preventDefault();options[searchActiveIndex]?.click();}
    else if(event.key==='Escape'){event.preventDefault();closeDialog($('#mayhen_docs_dialog_search'));}
  });
  $$('[data-search-term]').forEach(button=>button.addEventListener('click',()=>{$('#mayhen_docs_search_input').value=button.dataset.searchTerm;renderSearch();$('#mayhen_docs_search_input').focus();}));

  function openGuide(id){
    const g=GUIDES[id];if(!g)return;
    currentGuide=id;
    $('#mayhen_docs_guide_category').textContent=g.category.toUpperCase()+' / HƯỚNG DẪN MẪU';
    $('#mayhen_docs_guide_title').textContent=g.title;
    $('#mayhen_docs_guide_summary').textContent=g.summary;
    $('#mayhen_docs_guide_tip').textContent=g.tip;
    const list=$('#mayhen_docs_guide_steps');list.replaceChildren();
    g.steps.forEach(step=>{const li=document.createElement('li');li.textContent=step;list.append(li);});
    updateGuideBookmarkButton();closeMobileMenu();openDialog('mayhen_docs_dialog_guide');
  }
  $$('[data-guide]').forEach(button=>button.addEventListener('click',()=>openGuide(button.dataset.guide)));
  function updateGuideBookmarkButton(){
    const saved=bookmarks.includes(currentGuide);const button=$('#mayhen_docs_guide_bookmark');
    button.querySelector('span').textContent=saved?'Đã lưu ✓':'Lưu hướng dẫn';button.setAttribute('aria-pressed',String(saved));
  }
  $('#mayhen_docs_guide_bookmark').addEventListener('click',()=>{
    if(bookmarks.includes(currentGuide)){bookmarks=bookmarks.filter(id=>id!==currentGuide);toast('Đã bỏ hướng dẫn khỏi mục đã lưu.');}
    else{bookmarks.unshift(currentGuide);toast('Đã lưu hướng dẫn trên thiết bị.');}
    store.set(BOOKMARKS_KEY,bookmarks);updateGuideBookmarkButton();
  });
  async function copyText(value){
    try {if(navigator.clipboard && window.isSecureContext){await navigator.clipboard.writeText(value);return true;}} catch {}
    let textarea;
    try{
      textarea=document.createElement('textarea');textarea.value=value;textarea.setAttribute('readonly','');textarea.style.cssText='position:fixed;left:-10000px;top:0;opacity:0';(root.querySelector('dialog[open]') || document.body).appendChild(textarea);
      textarea.select();textarea.setSelectionRange(0,textarea.value.length);return document.execCommand('copy');
    }catch{return false;}finally{textarea?.remove();}
  }
  $('#mayhen_docs_guide_copy').addEventListener('click',async()=>{
    const g=GUIDES[currentGuide];const text=[g.title,'',g.summary,'',...g.steps.map((s,i)=>`${i+1}. ${s}`),'','Mẹo: '+g.tip].join('\n');
    toast(await copyText(text)?'Đã sao chép nội dung hướng dẫn.':'Chưa sao chép được, hãy thử lại trong trình duyệt HTTPS.');
  });
  function renderBookmarks(){
    const list=$('#mayhen_docs_saved_list');list.replaceChildren();
    if(!bookmarks.length){const none=document.createElement('p');none.className='mayhen_docs_no_results';none.textContent='Chưa có hướng dẫn nào được lưu. Hãy mở một hướng dẫn và bấm “Lưu hướng dẫn”.';list.append(none);return;}
    bookmarks.forEach(id=>list.append(makeGuideResult(id,()=>openGuide(id))));
  }

  function makeAskResults(query){
    const results=searchGuides(query).slice(0,5);const list=$('#mayhen_docs_ask_results');list.replaceChildren();
    const heading=document.createElement('p');heading.textContent=results.length?'Các hướng dẫn gần với câu hỏi của bạn:':'Chưa có tài liệu trùng khớp. Bạn có thể thử câu hỏi ngắn gọn hơn hoặc gửi góp ý.';list.append(heading);
    results.forEach(id=>list.append(makeGuideResult(id,()=>openGuide(id))));
  }
  $('#mayhen_docs_ask_form').addEventListener('submit',event=>{
    event.preventDefault();const text=$('#mayhen_docs_ask_input').value.trim();if(!text){toast('Bạn hãy nhập câu hỏi để tìm hướng dẫn.');$('#mayhen_docs_ask_input').focus();return;}makeAskResults(text);
  });

  function applySettings(persist=true){
    const theme=settings.theme==='system'?(systemDark?.matches?'dark':'light'):settings.theme;
    root.dataset.theme=theme;root.dataset.width=settings.width;root.dataset.motion=settings.motion?'on':'off';
    root.style.setProperty('--mayhen-docs-font-size',settings.font+'px');
    document.documentElement.style.colorScheme=theme;document.querySelector('meta[name="theme-color"]')?.setAttribute('content',theme==='dark'?'#1e1b1e':'#fffdfb');
    if(persist)store.set(SETTINGS_KEY,settings);
    updateSettingsUI();
  }
  function updateSettingsUI(){
    $$('[data-theme-option]').forEach(button=>button.setAttribute('aria-pressed',String(button.dataset.themeOption===settings.theme)));
    $$('[data-width-option]').forEach(button=>button.setAttribute('aria-pressed',String(button.dataset.widthOption===settings.width)));
    $('#mayhen_docs_font_range').value=settings.font;$('#mayhen_docs_font_output').textContent=settings.font+'px';
    $('#mayhen_docs_motion_toggle').checked=settings.motion;
  }
  $$('[data-theme-option]').forEach(button=>button.addEventListener('click',()=>{settings.theme=button.dataset.themeOption;applySettings();}));
  $$('[data-width-option]').forEach(button=>button.addEventListener('click',()=>{settings.width=button.dataset.widthOption;applySettings();}));
  $('#mayhen_docs_font_range').addEventListener('input',event=>{settings.font=Number(event.target.value);applySettings();});
  $('#mayhen_docs_font_minus').addEventListener('click',()=>{settings.font=Math.max(14,settings.font-1);applySettings();});
  $('#mayhen_docs_font_plus').addEventListener('click',()=>{settings.font=Math.min(20,settings.font+1);applySettings();});
  $('#mayhen_docs_motion_toggle').addEventListener('change',event=>{settings.motion=event.target.checked;applySettings();});
  $('#mayhen_docs_reset_settings').addEventListener('click',()=>{settings={theme:'light',font:16,width:'comfortable',motion:true};applySettings();toast('Đã khôi phục cài đặt mặc định.');});
  $('#mayhen_docs_float_theme').addEventListener('click',()=>{settings.theme=root.dataset.theme==='dark'?'light':'dark';applySettings();toast(root.dataset.theme==='dark'?'Đã bật chế độ tối.':'Đã bật chế độ sáng.');});
  if(systemDark){const cb=()=>{if(settings.theme==='system')applySettings(false);};systemDark.addEventListener?.('change',cb);}
  applySettings(false);

  function openMobileMenu(){const sidebar=$('#mayhen_docs_sidebar');sidebar.classList.add('mayhen_docs_sidebar_open');$('#mayhen_docs_scrim').hidden=false;$('#mayhen_docs_menu_button').setAttribute('aria-expanded','true');$('#mayhen_docs_sidebar_close').focus();}
  function closeMobileMenu(){const sidebar=$('#mayhen_docs_sidebar');sidebar.classList.remove('mayhen_docs_sidebar_open');$('#mayhen_docs_scrim').hidden=true;$('#mayhen_docs_menu_button').setAttribute('aria-expanded','false');}
  $('#mayhen_docs_menu_button').addEventListener('click',()=>$('#mayhen_docs_sidebar').classList.contains('mayhen_docs_sidebar_open')?closeMobileMenu():openMobileMenu());
  $('#mayhen_docs_sidebar_close').addEventListener('click',closeMobileMenu);$('#mayhen_docs_scrim').addEventListener('click',closeMobileMenu);
  $$('.mayhen_docs_side_group').forEach(button=>button.addEventListener('click',()=>{
    const next=button.getAttribute('aria-expanded')!=='true';button.setAttribute('aria-expanded',String(next));
    $('#mayhen_docs_group_'+button.dataset.group)?.classList.toggle('mayhen_docs_group_closed',!next);
  }));
  $$('.mayhen_docs_sidebar a[href^="#"]').forEach(anchor=>anchor.addEventListener('click',closeMobileMenu));
  function closeLanguageMenu(){$('#mayhen_docs_language_menu').hidden=true;$('#mayhen_docs_language_button').setAttribute('aria-expanded','false');}
  $('#mayhen_docs_language_button').addEventListener('click',()=>{
    const menu=$('#mayhen_docs_language_menu');const next=menu.hidden;menu.hidden=!next;$('#mayhen_docs_language_button').setAttribute('aria-expanded',String(next));
  });
  $$('[data-language]').forEach(button=>button.addEventListener('click',()=>{closeLanguageMenu();toast(button.dataset.language==='en'?'Bản tiếng Anh đang được chuẩn bị; nội dung hiện vẫn là tiếng Việt.':'Đang xem tài liệu tiếng Việt.');}));
  document.addEventListener('click',event=>{if(!event.target.closest('#mayhen_docs_language_button, #mayhen_docs_language_menu'))closeLanguageMenu();});

  function renderChecklist(){
    const el=$('#mayhen_docs_checklist_items');el.replaceChildren();
    CHECKLIST.forEach((task,index)=>{
      const label=document.createElement('label');const checkbox=document.createElement('input');checkbox.type='checkbox';checkbox.checked=tasks[index];
      const text=document.createElement('span');text.textContent=task;label.append(checkbox,text);
      checkbox.addEventListener('change',()=>{tasks[index]=checkbox.checked;store.set(CHECKLIST_KEY,tasks);updateChecklistProgress();});el.append(label);
    });updateChecklistProgress();
  }
  function updateChecklistProgress(){const count=tasks.filter(Boolean).length;$('#mayhen_docs_checklist_text').textContent=`${count} / ${CHECKLIST.length} công việc đã hoàn thành`;$('#mayhen_docs_checklist_bar').style.width=(100*count/CHECKLIST.length)+'%';}
  $('#mayhen_docs_checklist_reset').addEventListener('click',()=>{tasks=CHECKLIST.map(()=>false);store.set(CHECKLIST_KEY,tasks);renderChecklist();toast('Đã đặt lại checklist.');});

  function updateCountdown(){
    const date=weddingDate?new Date(weddingDate):null;
    if(!date || !Number.isFinite(date.getTime())){
      ['days','hours','minutes','seconds'].forEach(part=>$('#mayhen_docs_'+part).textContent='—');$('#mayhen_docs_countdown_note').textContent='Chọn ngày và giờ để bắt đầu đếm ngược.';return;
    }
    const diff=Math.max(0,date.getTime()-Date.now());const days=Math.floor(diff/86400000),hours=Math.floor(diff/3600000)%24,minutes=Math.floor(diff/60000)%60,seconds=Math.floor(diff/1000)%60;
    $('#mayhen_docs_days').textContent=days;$('#mayhen_docs_hours').textContent=String(hours).padStart(2,'0');$('#mayhen_docs_minutes').textContent=String(minutes).padStart(2,'0');$('#mayhen_docs_seconds').textContent=String(seconds).padStart(2,'0');
    $('#mayhen_docs_countdown_note').textContent=diff===0?'Ngày hẹn đã đến hoặc đã qua. Chúc hai bạn thật nhiều hạnh phúc!':'Mỗi khoảnh khắc đang đưa hai bạn đến gần ngày đặc biệt hơn.';
  }
  $('#mayhen_docs_date_input').value=weddingDate;
  $('#mayhen_docs_date_save').addEventListener('click',()=>{const value=$('#mayhen_docs_date_input').value;
    if(!value || !Number.isFinite(new Date(value).getTime())){toast('Vui lòng chọn ngày và giờ hợp lệ.');return;}
    weddingDate=value;store.set(DATE_KEY,weddingDate);updateCountdown();toast('Đã lưu ngày hẹn trên thiết bị.');
  });
  window.setInterval(()=>{if($('#mayhen_docs_dialog_countdown').open)updateCountdown();},1000);

  $('#mayhen_docs_share_copy').addEventListener('click',async()=>toast(await copyText($('#mayhen_docs_share_link').value)?'Đã sao chép liên kết.':'Không thể sao chép, vui lòng chọn liên kết và sao chép thủ công.'));
  $('#mayhen_docs_native_share').addEventListener('click',async()=>{
    if(navigator.share){try{await navigator.share({title:document.title,text:'Trung tâm hướng dẫn Mây Hẹn',url:window.location.href});}catch(error){if(error?.name!=='AbortError')toast('Thiết bị không thể chia sẻ liên kết này.');}}
    else toast('Trình duyệt chưa hỗ trợ chia sẻ trực tiếp. Bạn có thể dùng nút Sao chép.');
  });
  $('#mayhen_docs_print').addEventListener('click',()=>{closeDialog($('#mayhen_docs_dialog_share'));window.setTimeout(()=>window.print(),80);});
  const draft=store.get(FEEDBACK_KEY,'');$('#mayhen_docs_feedback_text').value=typeof draft==='string'?draft:'';
  function updateFeedbackCount(){$('#mayhen_docs_feedback_count').textContent=$('#mayhen_docs_feedback_text').value.length+' / 1500 ký tự';}
  $('#mayhen_docs_feedback_text').addEventListener('input',updateFeedbackCount);updateFeedbackCount();
  $('#mayhen_docs_feedback_save').addEventListener('click',()=>{const val=$('#mayhen_docs_feedback_text').value;store.set(FEEDBACK_KEY,val);toast('Đã lưu nháp góp ý trên thiết bị. Chưa gửi đến Mây Hẹn.');});
  $('#mayhen_docs_feedback_copy').addEventListener('click',async()=>{const val=$('#mayhen_docs_feedback_text').value.trim();if(!val){toast('Vui lòng nhập nội dung góp ý.');return;}const ok=await copyText('Góp ý cho Mây Hẹn Docs\n\n'+val);toast(ok?'Đã sao chép góp ý. Bạn có thể gửi tới đội ngũ Mây Hẹn.':'Không thể sao chép. Bạn có thể chọn văn bản và sao chép thủ công.');});
  const rating=store.get(RATING_KEY,'');$$('[data-feedback]').forEach(button=>{
    button.setAttribute('aria-pressed',String(button.dataset.feedback===rating));
    button.addEventListener('click',()=>{store.set(RATING_KEY,button.dataset.feedback);$$('[data-feedback]').forEach(other=>other.setAttribute('aria-pressed',String(other===button)));
      if(button.dataset.feedback==='yes')toast('Cảm ơn bạn đã đánh giá. ♡');else openDialog('mayhen_docs_dialog_feedback');
    });
  });

  // Carousel navigation, with disabled state for discoverability.
  const track=$('#mayhen_docs_workflow_track'),previous=$('#mayhen_docs_carousel_prev'),next=$('#mayhen_docs_carousel_next');
  function updateCarouselButtons(){previous.disabled=track.scrollLeft<=2;next.disabled=track.scrollLeft+track.clientWidth>=track.scrollWidth-3;}
  const carouselAmount=()=>Math.min(track.clientWidth*.84,335);
  previous.addEventListener('click',()=>track.scrollBy({left:-carouselAmount(),behavior:'smooth'}));
  next.addEventListener('click',()=>track.scrollBy({left:carouselAmount(),behavior:'smooth'}));
  track.addEventListener('scroll',updateCarouselButtons,{passive:true});window.addEventListener('resize',updateCarouselButtons);window.setTimeout(updateCarouselButtons,100);
  $$('.mayhen_docs_faq_list details').forEach(details=>details.addEventListener('toggle',()=>{if(details.open)$$('.mayhen_docs_faq_list details').filter(item=>item!==details && item.open).forEach(other=>other.open=false);}));

  const topButton=$('#mayhen_docs_to_top');
  function handleScroll(){const max=document.documentElement.scrollHeight-innerHeight;$('#mayhen_docs_progress').style.width=(max>0?Math.min(100,100*scrollY/max):0)+'%';topButton.classList.toggle('mayhen_docs_to_top_visible',scrollY>470);}
  window.addEventListener('scroll',handleScroll,{passive:true});window.addEventListener('resize',handleScroll);handleScroll();
  topButton.addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));$('#mayhen_docs_footer_top').addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));
  document.addEventListener('keydown',event=>{
    const editing=event.target.matches?.('input, textarea, [contenteditable="true"]');
    if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='k'){event.preventDefault();openDialog('mayhen_docs_dialog_search');return;}
    if(event.key==='Escape'){closeMobileMenu();closeLanguageMenu();return;}
    if(!editing && !root.querySelector('dialog[open]') && event.key==='/'){event.preventDefault();openDialog('mayhen_docs_dialog_search');}
    if(!editing && !root.querySelector('dialog[open]') && event.key==='?'){event.preventDefault();openDialog('mayhen_docs_dialog_shortcuts');}
  });
  // Incoming deep-links to bundled guide summaries: file.html#guide-rsvp
  let linkedGuide='';
  try {linkedGuide=decodeURIComponent(window.location.hash.slice(1));} catch {linkedGuide='';}
  if(linkedGuide.startsWith('guide-')&&GUIDES[linkedGuide.slice(6)]){
    window.setTimeout(()=>openGuide(linkedGuide.slice(6)),140);
  }
})();
