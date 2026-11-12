/* MÂY HẸN DOCS - Vanilla JavaScript, no dependencies.
 * Interactive UI demo only. No server/API, no tracking, no fake submissions.
 */
(() => {
  'use strict';
  const PREFIX = 'mayhen_docs_detail_';
  const app = document.getElementById(PREFIX + 'app');
  if (!app) return;
  const byId = suffix => document.getElementById(PREFIX + suffix);
  const all = selector => Array.from(app.querySelectorAll(selector));
  const documentSections = all('.mayhen_docs_detail_doc_section[id]');
  const themeMedia = window.matchMedia('(prefers-color-scheme: dark)');
  const safeStorage = {
    get(key) { try { return localStorage.getItem(PREFIX + key); } catch { return null; } },
    set(key, value) { try { localStorage.setItem(PREFIX + key, String(value)); } catch { /* private mode */ } }
  };
  const state = {
    theme: safeStorage.get('theme') || 'light',
    width: safeStorage.get('width') || 'comfortable',
    font: Math.max(14, Math.min(21, Number(safeStorage.get('font')) || 16)),
    focus: safeStorage.get('focus') === 'true',
    bookmark: safeStorage.get('bookmark') === 'true',
    selectedSearchIndex: 0,
    results: [],
    pendingFrame: false,
    toastTimeout: null
  };

  function toast(message) {
    const el = byId('toast');
    el.textContent = message;
    el.classList.add(PREFIX + 'visible');
    window.clearTimeout(state.toastTimeout);
    state.toastTimeout = window.setTimeout(() => el.classList.remove(PREFIX + 'visible'), 3000);
  }

  async function copyText(text) {
    if (!text) return false;
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return true;
      }
    } catch { /* fall back for file:// or restricted browsers */ }
    const input = document.createElement('textarea');
    input.value = text;
    input.setAttribute('readonly', '');
    input.style.position = 'fixed';
    input.style.top = '-9999px';
    document.body.append(input);
    input.select();
    let copied = false;
    try { copied = document.execCommand('copy'); } catch { /* no clipboard permission */ }
    input.remove();
    return copied;
  }
  async function copyAndToast(text, success = 'Đã sao chép vào bộ nhớ tạm.') {
    const copied = await copyText(text);
    toast(copied ? success : 'Không thể sao chép tự động. Hãy dùng Ctrl + C để sao chép.');
  }

  function smoothTop() {
    window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
  }
  byId('footer_top').addEventListener('click', smoothTop);
  byId('back_top').addEventListener('click', smoothTop);

  function applyTheme() {
    const theme = state.theme === 'system' ? (themeMedia.matches ? 'dark' : 'light') : state.theme;
    app.dataset.theme = theme;
    all('[data-theme-option]').forEach(btn => btn.setAttribute('aria-pressed', String(btn.dataset.themeOption === state.theme)));
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'dark' ? '#252123' : '#fffcfa');
  }
  function applyWidth() {
    app.dataset.width = state.width;
    all('[data-width-option]').forEach(btn => btn.setAttribute('aria-pressed', String(btn.dataset.widthOption === state.width)));
  }
  function setFont(next) {
    state.font = Math.max(14, Math.min(21, next));
    app.style.setProperty('--mayhen-docs-detail-font-size', state.font + 'px');
    byId('settings_font_value').textContent = state.font + 'px';
    safeStorage.set('font', state.font);
  }
  function setFocus(on) {
    state.focus = Boolean(on);
    app.classList.toggle(PREFIX + 'is_focus', state.focus);
    byId('focus_toggle').setAttribute('aria-pressed', String(state.focus));
    byId('focus_checkbox').checked = state.focus;
    safeStorage.set('focus', state.focus);
  }
  function applyBookmark() {
    const btn = byId('bookmark');
    btn.setAttribute('aria-pressed', String(state.bookmark));
    btn.querySelector('span').textContent = state.bookmark ? 'Đã lưu' : 'Lưu bài';
    btn.style.color = state.bookmark ? 'var(--mayhen-docs-detail-rose)' : '';
  }
  if (!['light', 'dark', 'system'].includes(state.theme)) state.theme = 'light';
  if (!['comfortable', 'wide'].includes(state.width)) state.width = 'comfortable';
  applyTheme(); applyWidth(); setFont(state.font); setFocus(state.focus); applyBookmark();
  if (themeMedia.addEventListener) themeMedia.addEventListener('change', () => { if (state.theme === 'system') applyTheme(); });
  all('[data-theme-option]').forEach(btn => btn.addEventListener('click', () => {
    state.theme = btn.dataset.themeOption; safeStorage.set('theme', state.theme); applyTheme();
  }));
  all('[data-width-option]').forEach(btn => btn.addEventListener('click', () => {
    state.width = btn.dataset.widthOption; safeStorage.set('width', state.width); applyWidth();
  }));
  byId('theme_quick').addEventListener('click', () => {
    state.theme = app.dataset.theme === 'light' ? 'dark' : 'light';
    safeStorage.set('theme', state.theme); applyTheme();
    toast(state.theme === 'dark' ? 'Đã chuyển sang chế độ tối.' : 'Đã chuyển sang chế độ sáng.');
  });
  byId('font_decrease').addEventListener('click', () => setFont(state.font - 1));
  byId('font_increase').addEventListener('click', () => setFont(state.font + 1));
  byId('settings_smaller').addEventListener('click', () => setFont(state.font - 1));
  byId('settings_bigger').addEventListener('click', () => setFont(state.font + 1));
  byId('focus_toggle').addEventListener('click', () => setFocus(!state.focus));
  byId('focus_checkbox').addEventListener('change', e => setFocus(e.target.checked));
  byId('bookmark').addEventListener('click', () => {
    state.bookmark = !state.bookmark;
    safeStorage.set('bookmark', state.bookmark);
    applyBookmark(); toast(state.bookmark ? 'Đã lưu bài viết trên thiết bị này.' : 'Đã bỏ lưu bài viết.');
  });

  // Sidebar collapsible groups and mobile drawer.
  function toggleDrawer(open) {
    app.classList.toggle(PREFIX + 'mobile_open', open);
    byId('menu_open').setAttribute('aria-expanded', String(open));
    document.documentElement.style.overflow = open ? 'hidden' : '';
    if (open) byId('menu_close').focus();
  }
  byId('menu_open').addEventListener('click', () => toggleDrawer(true));
  byId('menu_close').addEventListener('click', () => toggleDrawer(false));
  byId('mobile_scrim').addEventListener('click', () => toggleDrawer(false));
  byId('sidebar_toggle').addEventListener('click', () => {
    app.classList.toggle(PREFIX + 'is_sidebar_collapsed');
    const collapsed = app.classList.contains(PREFIX + 'is_sidebar_collapsed');
    byId('sidebar_toggle').setAttribute('aria-pressed', String(collapsed));
    byId('sidebar_toggle').setAttribute('title', collapsed ? 'Mở thanh điều hướng' : 'Thu gọn thanh điều hướng');
  });
  all('.mayhen_docs_detail_nav_group_toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const expanded = btn.getAttribute('aria-expanded') !== 'true';
      btn.setAttribute('aria-expanded', String(expanded));
      document.getElementById(btn.getAttribute('aria-controls')).hidden = !expanded;
    });
  });
  all('.mayhen_docs_detail_sidebar a[href^="#"]').forEach(link => link.addEventListener('click', () => toggleDrawer(false)));
  window.addEventListener('resize', () => { if (window.innerWidth > 1020) toggleDrawer(false); }, { passive: true });

  // Native accessible dialog management.
  const dialogs = all('dialog');
  function openDialog(id) {
    const dialog = document.getElementById(id);
    if (!dialog || !(dialog instanceof HTMLDialogElement)) return;
    dialogs.forEach(d => { if (d.open) d.close(); });
    dialog.showModal();
    document.body.style.overflow = 'hidden';
    if (id === PREFIX + 'search_dialog') {
      byId('search_input').value = '';
      renderSearch('');
      requestAnimationFrame(() => byId('search_input').focus());
    }
    if (id === PREFIX + 'share_dialog') byId('share_input').value = window.location.href;
  }
  function closeDialog(dialog) {
    if (dialog && dialog.open) dialog.close();
  }
  all('[data-modal]').forEach(btn => btn.addEventListener('click', () => openDialog(btn.dataset.modal)));
  all('[data-close-dialog]').forEach(btn => btn.addEventListener('click', () => closeDialog(btn.closest('dialog'))));
  dialogs.forEach(dialog => {
    dialog.addEventListener('close', () => { if (!dialogs.some(d => d.open)) document.body.style.overflow = ''; });
    dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
  });

  // Build accessible TOC modal from the same links as the right rail.
  const tocLinks = all('#mayhen_docs_detail_toc_links a[href^="#"]');
  byId('toc_modal_links').innerHTML = tocLinks.map(link => `<a href="${link.getAttribute('href')}">${link.textContent}</a>`).join('');
  all('#mayhen_docs_detail_toc_modal_links a').forEach(link => link.addEventListener('click', () => closeDialog(byId('toc_dialog'))));

  // Scroll spy and reading progress.
  function updateScrollUI() {
    state.pendingFrame = false;
    const article = byId('article');
    const absoluteTop = article.getBoundingClientRect().top + scrollY;
    const absoluteBottom = article.getBoundingClientRect().bottom + scrollY;
    const denominator = Math.max(1, absoluteBottom - absoluteTop - window.innerHeight + 120);
    const percent = Math.max(0, Math.min(100, Math.round(((window.scrollY - absoluteTop + 40) / denominator) * 100)));
    byId('reading_progress').style.width = percent + '%';
    byId('toc_progress_bar').style.width = percent + '%';
    byId('read_percent').textContent = percent + '%';
    byId('back_top').classList.toggle(PREFIX + 'visible', window.scrollY > 440);
    let active = documentSections[0]?.id;
    for (const section of documentSections) {
      if (section.getBoundingClientRect().top <= 205) active = section.id;
      else break;
    }
    tocLinks.forEach(a => {
      if (a.getAttribute('href') === '#' + active) a.setAttribute('aria-current', 'location');
      else a.removeAttribute('aria-current');
    });
    all('.mayhen_docs_detail_nav_link[href^="#"]').forEach(a => {
      const isActive = window.scrollY < 260
        ? a.getAttribute('href') === '#' + PREFIX + 'article'
        : a.getAttribute('href') === '#' + active;
      a.classList.toggle(PREFIX + 'nav_active', isActive);
    });
  }
  function scheduleScrollUpdate() {
    if (state.pendingFrame) return;
    state.pendingFrame = true;
    requestAnimationFrame(updateScrollUI);
  }
  window.addEventListener('scroll', scheduleScrollUpdate, { passive: true });
  window.addEventListener('resize', scheduleScrollUpdate, { passive: true });
  updateScrollUI();

  // Search is local to this demo page; results link to real sections here.
  const searchIndex = documentSections.map((section, order) => ({
    id: section.id,
    title: section.querySelector('h2')?.textContent.trim() || 'Hướng dẫn',
    summary: (section.querySelector('p')?.textContent.trim() || '').slice(0, 125),
    content: section.textContent.trim().slice(0, 2500),
    order
  }));
  function normalize(text) {
    return (text || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/\s+/g, ' ').trim();
  }
  function renderSearch(query) {
    const words = normalize(query).split(/\s+/).filter(Boolean);
    const results = searchIndex.map(item => {
      const title = normalize(item.title);
      const summary = normalize(item.summary);
      const content = normalize(item.content);
      const matches = words.every(word => title.includes(word) || summary.includes(word) || content.includes(word));
      const score = words.reduce((sum, word) => sum + (title.includes(word) ? 5 : 0) + (summary.includes(word) ? 2 : 0) + (content.includes(word) ? 1 : 0), 0);
      return { ...item, matches, score };
    }).filter(item => words.length ? item.matches : true)
      .sort((a, b) => words.length ? b.score - a.score || a.order - b.order : a.order - b.order);
    state.results = results;
    state.selectedSearchIndex = 0;
    byId('search_count').textContent = words.length ? `${results.length} kết quả phù hợp` : 'Các mục hướng dẫn gợi ý';
    const list = byId('search_results');
    list.replaceChildren();
    if (!results.length) {
      const empty = document.createElement('div');
      empty.className = PREFIX + 'search_empty';
      empty.textContent = 'Chưa tìm thấy mục phù hợp. Hãy thử một từ khóa ngắn hơn.';
      list.append(empty);
      return;
    }
    results.forEach((result, index) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = PREFIX + 'search_result';
      btn.dataset.resultIndex = String(index);
      btn.setAttribute('aria-selected', String(index === 0));
      const symbol = document.createElement('span'); symbol.className = PREFIX + 'search_result_icon'; symbol.textContent = '✧';
      const text = document.createElement('span'); text.className = PREFIX + 'search_result_text';
      const title = document.createElement('strong'); title.textContent = result.title;
      const description = document.createElement('small'); description.textContent = result.summary || 'Đi tới phần hướng dẫn';
      text.append(title, description);
      const arrow = document.createElement('span'); arrow.className = PREFIX + 'search_result_arrow'; arrow.textContent = '↗';
      btn.append(symbol, text, arrow);
      btn.addEventListener('mouseenter', () => selectSearchResult(index));
      btn.addEventListener('click', () => goToSearchResult(index));
      list.append(btn);
    });
  }
  function selectSearchResult(index) {
    if (!state.results.length) return;
    state.selectedSearchIndex = (index + state.results.length) % state.results.length;
    all('.mayhen_docs_detail_search_result').forEach(btn => btn.setAttribute('aria-selected', String(Number(btn.dataset.resultIndex) === state.selectedSearchIndex)));
    all('.mayhen_docs_detail_search_result')[state.selectedSearchIndex]?.scrollIntoView({ block: 'nearest' });
  }
  function goToSearchResult(index) {
    const item = state.results[index];
    if (!item) return;
    closeDialog(byId('search_dialog'));
    const element = document.getElementById(item.id);
    if (!element) return;
    history.replaceState(null, '', '#' + item.id);
    element.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
  }
  byId('search_input').addEventListener('input', e => renderSearch(e.target.value));
  byId('search_input').addEventListener('keydown', e => {
    if (e.key === 'ArrowDown') { e.preventDefault(); selectSearchResult(state.selectedSearchIndex + 1); }
    if (e.key === 'ArrowUp') { e.preventDefault(); selectSearchResult(state.selectedSearchIndex - 1); }
    if (e.key === 'Enter') { e.preventDefault(); goToSearchResult(state.selectedSearchIndex); }
  });
  renderSearch('');
  document.addEventListener('keydown', e => {
    // Escape first closes a dialog, even when a search input normally consumes Escape.
    if (e.key === 'Escape') {
      const activeDialog = dialogs.find(dialog => dialog.open);
      if (activeDialog) { e.preventDefault(); closeDialog(activeDialog); return; }
    }
    const isTyping = e.target instanceof HTMLElement && (e.target.matches('input,textarea,select') || e.target.isContentEditable);
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openDialog(PREFIX + 'search_dialog'); return; }
    if (e.key === 'Escape' && app.classList.contains(PREFIX + 'mobile_open')) { toggleDrawer(false); return; }
    if (isTyping || dialogs.some(dialog => dialog.open) || e.ctrlKey || e.altKey || e.metaKey) return;
    if (e.key === '/') { e.preventDefault(); openDialog(PREFIX + 'search_dialog'); }
    if (e.key === '?') { e.preventDefault(); openDialog(PREFIX + 'shortcuts_dialog'); }
  });

  // Copy Markdown from readable content, not the UI's control labels.
  function articleToMarkdown() {
    const title = byId('article').querySelector('h1').textContent.replace(/\s+/g, ' ').trim();
    const lead = byId('article').querySelector('.mayhen_docs_detail_lead').textContent.trim();
    const lines = [`# ${title}`, '', lead, ''];
    documentSections.forEach(section => {
      lines.push('## ' + section.querySelector('h2').textContent.trim(), '');
      Array.from(section.children).forEach(child => {
        if (child.matches('p')) lines.push(child.textContent.trim(), '');
        if (child.matches('ul')) {
          child.querySelectorAll(':scope > li').forEach(li => lines.push('- ' + li.textContent.trim()));
          lines.push('');
        }
        if (child.matches('ol')) {
          child.querySelectorAll(':scope > li').forEach((li, index) => lines.push(`${index + 1}. ${li.textContent.trim()}`));
          lines.push('');
        }
        if (child.matches('.mayhen_docs_detail_callout')) lines.push('> ' + child.textContent.trim().replace(/\s+/g, ' '), '');
        if (child.matches('.mayhen_docs_detail_quote')) lines.push('> ' + child.querySelector('p').textContent.trim(), '');
        if (child.matches('.mayhen_docs_detail_table_wrap')) {
          const rows = Array.from(child.querySelectorAll('tr')).map(row => Array.from(row.children).map(cell => cell.textContent.trim().replace(/\|/g, '\\|')));
          rows.forEach((row, i) => { lines.push('| ' + row.join(' | ') + ' |'); if (!i) lines.push('| ' + row.map(() => '---').join(' | ') + ' |'); });
          lines.push('');
        }
      });
    });
    return lines.join('\n').replace(/\n{3,}/g, '\n\n').trim();
  }
  byId('copy_markdown').addEventListener('click', () => copyAndToast(articleToMarkdown(), 'Đã sao chép nội dung Markdown.'));
  byId('copy_invitation').addEventListener('click', () => copyAndToast(byId('invitation_text').textContent.trim(), 'Đã sao chép lời mời mẫu.'));
  byId('copy_example_url').addEventListener('click', () => copyAndToast(byId('example_url').textContent.trim(), 'Đã sao chép đường dẫn ví dụ.'));
  all('[data-copy-section]').forEach(btn => btn.addEventListener('click', () => {
    const url = new URL(location.href); url.hash = btn.dataset.copySection;
    copyAndToast(url.href, 'Đã sao chép liên kết tới mục này.');
  }));
  byId('copy_share').addEventListener('click', () => copyAndToast(byId('share_input').value, 'Đã sao chép đường dẫn bài viết.'));
  byId('native_share').addEventListener('click', async () => {
    if (!navigator.share) {
      await copyAndToast(location.href, 'Thiết bị không hỗ trợ chia sẻ trực tiếp. Đã sao chép đường dẫn.');
      return;
    }
    try { await navigator.share({ title: document.title, text: 'Cùng khám phá hướng dẫn Mây Hẹn', url: location.href }); }
    catch (error) { if (error?.name !== 'AbortError') toast('Chưa thể chia sẻ trên thiết bị này.'); }
  });
  byId('print').addEventListener('click', () => { closeDialog(byId('share_dialog')); window.setTimeout(() => window.print(), 80); });

  // Helpful votes are stored only on this device. Feedback is explicitly copied, never faked as sent.
  const savedVote = safeStorage.get('helpful');
  all('[data-feedback-vote]').forEach(btn => {
    btn.setAttribute('aria-pressed', String(btn.dataset.feedbackVote === savedVote));
    btn.addEventListener('click', () => {
      safeStorage.set('helpful', btn.dataset.feedbackVote);
      all('[data-feedback-vote]').forEach(v => v.setAttribute('aria-pressed', String(v === btn)));
      if (btn.dataset.feedbackVote === 'no') {
        byId('feedback_type').value = 'Nội dung khó hiểu';
        openDialog(PREFIX + 'feedback_dialog');
      } else toast('Cảm ơn bạn! Đánh giá đã được lưu trên thiết bị này.');
    });
  });
  byId('feedback_form').addEventListener('submit', async e => {
    e.preventDefault();
    const message = byId('feedback_message').value.trim();
    if (message.length < 5) { toast('Bạn hãy viết ít nhất 5 ký tự nhé.'); byId('feedback_message').focus(); return; }
    const feedback = `Góp ý tài liệu Mây Hẹn\nChủ đề: ${byId('feedback_type').value}\nTrang: ${location.href}\n\n${message}`;
    const copied = await copyText(feedback);
    if (copied) {
      closeDialog(byId('feedback_dialog'));
      toast('Đã sao chép góp ý. Hãy gửi qua kênh hỗ trợ chính thức.');
    } else toast('Không thể sao chép tự động. Vui lòng chọn nội dung và sao chép thủ công.');
  });
})();
