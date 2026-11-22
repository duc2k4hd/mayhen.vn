/**
 * MAYHEN.VN — WEDDING AI COPYWRITING SUITE MODAL (5 TABS)
 * Pure Vanilla ES Module — Zero raw innerHTML interpolation (XSS-safe DOM builders)
 * Injectable fetch/document/window seam for deterministic Node unit testing.
 */
import { $, $$, on } from '../common/dom.js';

export const DEFAULT_AI_ENDPOINTS = Object.freeze({
    invitations: '/api/editor/ai/invitations',
    story: '/api/editor/ai-story',
    thankYou: '/api/editor/ai/thank-you',
    polish: '/api/editor/ai/polish',
    translate: '/api/editor/ai/translate',
});

/**
 * Pure HTTP adapter seam cho cả 5 API biên tập nội dung AI.
 */
export const createAiCopyClient = ({
    fetchImpl = (...args) => globalThis.fetch(...args),
    csrfToken = '',
    endpoints = {},
} = {}) => {
    const resolved = { ...DEFAULT_AI_ENDPOINTS, ...endpoints };

    const postJson = async (url, payload) => {
        const res = await fetchImpl(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                Accept: 'application/json',
            },
            body: JSON.stringify(payload),
        });

        return res.json();
    };

    return {
        generateInvitations: (payload) => postJson(resolved.invitations, payload),
        generateStory: (payload) => postJson(resolved.story, payload),
        generateThankYou: (payload) => postJson(resolved.thankYou, payload),
        polishText: (payload) => postJson(resolved.polish, payload),
        translateText: (payload) => postJson(resolved.translate, payload),
    };
};

const createButton = (doc, label, { className = '', style = '' } = {}) => {
    const btn = doc.createElement('button');
    btn.type = 'button';
    if (className) btn.className = className;
    if (style) btn.style.cssText = style;
    btn.textContent = label;
    return btn;
};

/**
 * Tạo hộp trạng thái loading / lỗi hoàn toàn bằng textContent (miễn nhiễm XSS).
 */
export const renderStatusBanner = (doc, message, { variant = 'loading' } = {}) => {
    const el = doc.createElement('div');
    if (variant === 'error') {
        el.style.cssText = 'color: #C62828; font-size: 13px; padding: 12px;';
    } else if (variant === 'validation') {
        el.style.cssText = 'color: #C62828; font-size: 12px; padding: 8px;';
    } else {
        el.style.cssText = 'text-align: center; padding: 20px; color: #7A726A; font-style: italic;';
    }
    el.textContent = String(message ?? '');
    return el;
};

/**
 * Render thẻ phương án dùng chung cho Tab 1 (Lời mời), Tab 2 (Lời ngỏ) và Tab 3 (Lời cảm ơn).
 * 100% createElement + textContent: không nội suy dữ liệu API vào innerHTML.
 */
export const renderOptionCard = (
    doc,
    {
        tag = '',
        title = '',
        subtitle = '',
        quoteSubtitle = false,
        content = '',
        copyButtonClass = 'btn-copy-ai',
        applyButtonClass = 'btn-apply-ai',
        onCopy,
        onApply,
    }
) => {
    const card = doc.createElement('div');
    card.style.cssText =
        'border: 1px solid #EAE3D9; border-radius: 12px; padding: 14px; background: #FCFAF7; display: flex; flex-direction: column; gap: 8px;';

    const headerRow = doc.createElement('div');
    headerRow.style.cssText =
        'display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;';

    const metaWrap = doc.createElement('div');
    metaWrap.style.cssText = 'display: flex; align-items: center; gap: 8px;';

    const badge = doc.createElement('span');
    badge.style.cssText =
        'background: #EFE8DD; color: #6B5E4B; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px;';
    badge.textContent = String(tag ?? '');

    const titleEl = doc.createElement('strong');
    titleEl.style.cssText = 'font-size: 13px; color: #1F1D1A;';
    titleEl.textContent = String(title ?? '');

    metaWrap.appendChild(badge);
    metaWrap.appendChild(titleEl);

    const actionsWrap = doc.createElement('div');
    actionsWrap.style.cssText = 'display: flex; gap: 6px;';

    const copyBtn = createButton(doc, 'Sao chép', {
        className: `${copyButtonClass} mayhen_client_button_base mayhen_client_button_sm`,
        style: 'background: #FFF; border: 1px solid #D5C9B8; color: #5A534B; font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer;',
    });
    if (onCopy) on(copyBtn, 'click', () => onCopy(copyBtn));

    const applyBtn = createButton(doc, 'Áp Dụng', {
        className: `${applyButtonClass} mayhen_client_button_base mayhen_client_button_sm`,
        style: 'background: #1F1D1A; color: #FFF; border: none; font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer;',
    });
    if (onApply) on(applyBtn, 'click', () => onApply(applyBtn));

    actionsWrap.appendChild(copyBtn);
    actionsWrap.appendChild(applyBtn);

    headerRow.appendChild(metaWrap);
    headerRow.appendChild(actionsWrap);
    card.appendChild(headerRow);

    if (subtitle) {
        const subEl = doc.createElement('div');
        subEl.style.cssText = 'font-size: 12px; color: #9C7A4A; font-style: italic;';
        subEl.textContent = quoteSubtitle ? `"${subtitle}"` : String(subtitle);
        card.appendChild(subEl);
    }

    const contentEl = doc.createElement('p');
    contentEl.style.cssText = 'font-size: 13px; color: #3A3530; line-height: 1.5; margin: 0;';
    contentEl.textContent = String(content ?? '');
    card.appendChild(contentEl);

    return card;
};

/**
 * Render kết quả soát lỗi & tinh chỉnh Quiet Luxury (Tab 4) hoàn toàn an toàn XSS.
 */
export const renderPolishResult = (doc, data, { onCopy, onReplace } = {}) => {
    const nodes = [];
    const count = Number(data?.improvements_count ?? 0);

    const summaryBox = doc.createElement('div');
    summaryBox.style.cssText =
        'background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; padding: 12px; display: flex; align-items: center; justify-content: space-between;';

    const summaryText = doc.createElement('div');
    summaryText.style.cssText = 'font-size: 12px; color: #1F1D1A;';

    const summaryLabel = doc.createElement('strong');
    summaryLabel.textContent = 'Kết quả kiểm tra:';
    const countEl = doc.createElement('strong');
    countEl.textContent = String(count);

    summaryText.appendChild(summaryLabel);
    summaryText.appendChild(doc.createTextNode(' Phát hiện '));
    summaryText.appendChild(countEl);
    summaryText.appendChild(doc.createTextNode(' điểm cải tiến'));

    const statusBadge = doc.createElement('span');
    const hasImprovements = count > 0;
    statusBadge.style.cssText = `background: ${hasImprovements ? '#E8F5E9' : '#EDE7F6'}; color: ${hasImprovements ? '#2E7D32' : '#512DA8'}; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px;`;
    statusBadge.textContent = hasImprovements ? '✓ Đã Tối Ưu' : '✓ Chuẩn Mực';

    summaryBox.appendChild(summaryText);
    summaryBox.appendChild(statusBadge);
    nodes.push(summaryBox);

    if (Array.isArray(data?.improvements) && data.improvements.length > 0) {
        const listWrap = doc.createElement('div');
        listWrap.style.cssText = 'display: flex; flex-direction: column; gap: 6px;';

        data.improvements.forEach((item) => {
            const itemEl = doc.createElement('div');
            itemEl.style.cssText =
                'font-size: 12px; color: #5A534B; background: #FFF; border: 1px dashed #D5C9B8; border-radius: 8px; padding: 6px 10px; display: flex; align-items: baseline; gap: 6px;';

            const bullet = doc.createElement('span');
            bullet.style.cssText = 'color: #9C7A4A; font-weight: 600;';
            bullet.textContent = '•';

            const reason = doc.createElement('span');
            reason.textContent = String(item?.reason ?? '');

            itemEl.appendChild(bullet);
            itemEl.appendChild(reason);
            listWrap.appendChild(itemEl);
        });
        nodes.push(listWrap);
    }

    const polishedText = String(data?.polished ?? '');
    const polishedCard = doc.createElement('div');
    polishedCard.style.cssText =
        'border: 1px solid #C5A880; border-radius: 12px; padding: 14px; background: #FFFDF9; display: flex; flex-direction: column; gap: 8px;';

    const cardHeader = doc.createElement('div');
    cardHeader.style.cssText = 'display: flex; align-items: center; justify-content: space-between;';

    const cardTitle = doc.createElement('strong');
    cardTitle.style.cssText =
        'font-size: 12px; color: #9C7A4A; text-transform: uppercase; letter-spacing: 0.5px;';
    cardTitle.textContent = 'Văn bản sau tinh chỉnh Quiet Luxury';

    const btnGroup = doc.createElement('div');
    btnGroup.style.cssText = 'display: flex; gap: 6px;';

    const copyBtn = createButton(doc, 'Sao chép', {
        className: 'btn-copy-polish mayhen_client_button_base mayhen_client_button_sm',
        style: 'background: #FFF; border: 1px solid #D5C9B8; color: #5A534B; font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer;',
    });
    if (onCopy) on(copyBtn, 'click', () => onCopy(polishedText, copyBtn));

    const replaceBtn = createButton(doc, 'Thay thế vào ô nhập', {
        className: 'btn-replace-polish mayhen_client_button_base mayhen_client_button_sm',
        style: 'background: #1F1D1A; color: #FFF; border: none; font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer;',
    });
    if (onReplace) on(replaceBtn, 'click', () => onReplace(polishedText, replaceBtn));

    btnGroup.appendChild(copyBtn);
    btnGroup.appendChild(replaceBtn);
    cardHeader.appendChild(cardTitle);
    cardHeader.appendChild(btnGroup);

    const bodyEl = doc.createElement('p');
    bodyEl.style.cssText =
        "font-size: 13px; color: #1F1D1A; line-height: 1.5; margin: 0; font-family: 'Playfair Display', serif;";
    bodyEl.textContent = polishedText;

    polishedCard.appendChild(cardHeader);
    polishedCard.appendChild(bodyEl);
    nodes.push(polishedCard);

    return nodes;
};

/**
 * Render kết quả dịch đa ngôn ngữ bảo toàn tên riêng (Tab 5) hoàn toàn an toàn XSS.
 */
export const renderTranslateResult = (doc, data, { onCopy } = {}) => {
    const translatedText = String(data?.translated_text ?? '');
    const card = doc.createElement('div');
    card.style.cssText =
        'border: 1px solid #EAE3D9; border-radius: 12px; padding: 14px; background: #FCFAF7; display: flex; flex-direction: column; gap: 8px;';

    const headerRow = doc.createElement('div');
    headerRow.style.cssText =
        'display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;';

    const metaWrap = doc.createElement('div');
    metaWrap.style.cssText = 'display: flex; align-items: center; gap: 8px;';

    const langBadge = doc.createElement('span');
    langBadge.style.cssText =
        'background: #EFE8DD; color: #6B5E4B; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px;';
    langBadge.textContent = String(data?.target_lang_name ?? '');

    const titleEl = doc.createElement('strong');
    titleEl.style.cssText = 'font-size: 13px; color: #1F1D1A;';
    titleEl.textContent = 'Bản Dịch Chuẩn Nghi Thức';

    metaWrap.appendChild(langBadge);
    metaWrap.appendChild(titleEl);

    const copyBtn = createButton(doc, 'Sao chép', {
        className: 'btn-copy-trans mayhen_client_button_base mayhen_client_button_sm',
        style: 'background: #1F1D1A; color: #FFF; border: none; font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer;',
    });
    if (onCopy) on(copyBtn, 'click', () => onCopy(translatedText, copyBtn));

    headerRow.appendChild(metaWrap);
    headerRow.appendChild(copyBtn);
    card.appendChild(headerRow);

    const bodyEl = doc.createElement('p');
    bodyEl.style.cssText = 'font-size: 13px; color: #1F1D1A; line-height: 1.5; margin: 0;';
    bodyEl.textContent = translatedText;
    card.appendChild(bodyEl);

    if (Array.isArray(data?.preserved_items) && data.preserved_items.length > 0) {
        const preservedBox = doc.createElement('div');
        preservedBox.style.cssText =
            'font-size: 11px; color: #2E7D32; background: #E8F5E9; padding: 4px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;';

        const labelSpan = doc.createElement('span');
        labelSpan.textContent = '🔒 Đã bảo toàn nguyên vẹn tên riêng:';

        const namesEl = doc.createElement('strong');
        namesEl.textContent = data.preserved_items.join(', ');

        preservedBox.appendChild(labelSpan);
        preservedBox.appendChild(namesEl);
        card.appendChild(preservedBox);
    }

    return card;
};

/**
 * Khởi tạo Wedding AI Copywriting Suite 5 tab trong Studio Tạo Thiệp.
 */
export const initAiCopyModal = ({
    doc = globalThis.document,
    win = globalThis.window,
    fetchImpl = (...args) => globalThis.fetch(...args),
    clipboard = globalThis.navigator?.clipboard,
} = {}) => {
    if (!doc || !win) {
        return null;
    }

    const q = (selector) => $(selector, doc);
    const qAll = (selector) => $$(selector, doc);

    const client = createAiCopyClient({
        fetchImpl,
        csrfToken: win.__MAYHEN_CSRF_TOKEN || '',
        endpoints: {
            invitations: win.__MAYHEN_AI_INVITATIONS_URL || DEFAULT_AI_ENDPOINTS.invitations,
            story: win.__MAYHEN_AI_STORY_URL || DEFAULT_AI_ENDPOINTS.story,
            thankYou: win.__MAYHEN_AI_THANK_YOU_URL || DEFAULT_AI_ENDPOINTS.thankYou,
            polish: win.__MAYHEN_AI_POLISH_URL || DEFAULT_AI_ENDPOINTS.polish,
            translate: win.__MAYHEN_AI_TRANSLATE_URL || DEFAULT_AI_ENDPOINTS.translate,
        },
    });

    const btnOpenAiStory = q('#btn-open-ai-story');
    const btnRailAiCopywriter = q('#mayhen_rail_btn_ai_copywriter');
    const aiStoryModal = q('#mayhen_ai_story_modal');
    const aiStoryCloseBtn = q('#mayhen_ai_story_close_btn');
    const aiNavTabs = qAll('.mayhen_ai_nav_tab');
    const aiTabPanes = qAll('.mayhen_ai_tab_pane');
    const btnOpenAiSuites = qAll('.btn-open-ai-suite');

    const switchAiTab = (targetTab) => {
        aiNavTabs.forEach((tab) => {
            const isActive = tab.getAttribute('data-ai-target') === targetTab;
            tab.classList.toggle('is-active', isActive);
            tab.style.fontWeight = isActive ? '600' : '500';
            tab.style.color = isActive ? '#1F1D1A' : '#7A726A';
            tab.style.background = isActive ? '#FFF' : 'transparent';
            tab.style.border = isActive ? '1px solid #EAE3D9' : 'none';
            if (isActive) tab.style.borderBottom = 'none';
        });

        aiTabPanes.forEach((pane) => {
            const isTarget = pane.id === `ai-pane-${targetTab}`;
            pane.style.display = isTarget ? 'flex' : 'none';
        });
    };

    aiNavTabs.forEach((tab) => {
        on(tab, 'click', () => {
            const target = tab.getAttribute('data-ai-target');
            if (target) switchAiTab(target);
        });
    });

    const openAiModal = (defaultTab = 'invitations') => {
        if (!aiStoryModal) return;

        const groomInp = q('[data-sync-key="wedding.groom_name"]') || q('input[name="wedding[groom_name]"]');
        const brideInp = q('[data-sync-key="wedding.bride_name"]') || q('input[name="wedding[bride_name]"]');
        const aiGroomInp = q('#ai-story-groom');
        const aiBrideInp = q('#ai-story-bride');
        if (aiGroomInp && groomInp?.value) aiGroomInp.value = groomInp.value;
        if (aiBrideInp && brideInp?.value) aiBrideInp.value = brideInp.value;

        switchAiTab(defaultTab);
        aiStoryModal.classList.add('mayhen_editor_modal_open');
    };

    const closeAiModal = () => {
        if (aiStoryModal) aiStoryModal.classList.remove('mayhen_editor_modal_open');
    };

    if (btnOpenAiStory) on(btnOpenAiStory, 'click', () => openAiModal('story'));
    if (btnRailAiCopywriter) on(btnRailAiCopywriter, 'click', () => openAiModal('invitations'));
    btnOpenAiSuites.forEach((btn) => {
        on(btn, 'click', () => {
            openAiModal(btn.getAttribute('data-default-ai-tab') || 'invitations');
        });
    });

    if (aiStoryCloseBtn) on(aiStoryCloseBtn, 'click', closeAiModal);
    if (aiStoryModal) {
        on(aiStoryModal, 'click', (e) => {
            if (e.target === aiStoryModal) closeAiModal();
        });
    }

    const copyToClipboard = async (text, btnElement) => {
        try {
            if (!clipboard?.writeText) return;
            await clipboard.writeText(text);
            const origText = btnElement.textContent;
            btnElement.textContent = '✓ Đã sao chép';
            btnElement.style.background = '#2E7D32';
            btnElement.style.color = '#FFF';
            setTimeout(() => {
                btnElement.textContent = origText;
                btnElement.style.background = '';
                btnElement.style.color = '';
            }, 1500);
        } catch (err) {
            console.error('Failed to copy', err);
        }
    };

    const dispatchInputValue = (inputEl, value) => {
        if (!inputEl) return false;
        const EventCtor = win.Event || Event;
        inputEl.value = value;
        inputEl.dispatchEvent(new EventCtor('input', { bubbles: true }));
        inputEl.dispatchEvent(new EventCtor('change', { bubbles: true }));
        return true;
    };

    const resolveFirstInput = (selectors) => {
        for (const selector of selectors) {
            const el = q(selector);
            if (el) return el;
        }
        return null;
    };

    const markAppliedAndClose = (btn) => {
        btn.textContent = '✓ Đã Áp Dụng!';
        btn.style.background = '#2E7D32';
        setTimeout(() => closeAiModal(), 700);
    };

    const setResultsNodes = (container, nodes) => {
        if (!container) return;
        container.replaceChildren(...(Array.isArray(nodes) ? nodes : [nodes]));
    };

    const runAiAction = async ({
        button,
        loadingButtonText,
        resultsEl,
        loadingMessage,
        errorMessage,
        request,
        onSuccess,
    }) => {
        const originalChildren = Array.from(button.childNodes).map((node) => node.cloneNode(true));
        const loadingSpan = doc.createElement('span');
        loadingSpan.textContent = loadingButtonText;

        button.disabled = true;
        button.replaceChildren(loadingSpan);
        if (resultsEl) {
            setResultsNodes(resultsEl, renderStatusBanner(doc, loadingMessage, { variant: 'loading' }));
        }

        try {
            const data = await request();
            onSuccess(data);
        } catch {
            if (resultsEl) {
                setResultsNodes(resultsEl, renderStatusBanner(doc, errorMessage, { variant: 'error' }));
            }
        } finally {
            button.disabled = false;
            button.replaceChildren(...originalChildren);
        }
    };

    const getCoupleNames = () => ({
        groom: q('#ai-story-groom')?.value || q('[data-sync-key="wedding.groom_name"]')?.value || 'Chú Rể',
        bride: q('#ai-story-bride')?.value || q('[data-sync-key="wedding.bride_name"]')?.value || 'Cô Dâu',
    });

    // --- TAB 1: LỜI MỜI CƯỚI ---
    const btnGenAiInvitations = q('#btn-generate-ai-invitations');
    const aiInvitationsResults = q('#ai-invitations-results');
    if (btnGenAiInvitations && aiInvitationsResults) {
        on(btnGenAiInvitations, 'click', () => {
            const { groom, bride } = getCoupleNames();
            runAiAction({
                button: btnGenAiInvitations,
                loadingButtonText: '⏳ Đang biên soạn 3 phương án lời mời...',
                resultsEl: aiInvitationsResults,
                loadingMessage: 'Đang chuẩn hóa nghi thức và văn phong Quiet Luxury...',
                errorMessage: 'Đã xảy ra lỗi khi tạo gợi ý. Vui lòng thử lại.',
                request: () =>
                    client.generateInvitations({
                        groom_name: groom,
                        bride_name: bride,
                        host_type: q('#ai-inv-host-type')?.value || 'parents_both',
                        tone: q('#ai-inv-tone')?.value || 'formal',
                        additional_note: q('#ai-inv-note')?.value || '',
                    }),
                onSuccess: (data) => {
                    if (data?.success && Array.isArray(data.options)) {
                        const cards = data.options.map((opt) =>
                            renderOptionCard(doc, {
                                tag: opt.tag,
                                title: opt.title,
                                subtitle: opt.subtitle,
                                content: opt.content,
                                copyButtonClass: 'btn-copy-inv',
                                applyButtonClass: 'btn-apply-inv',
                                onCopy: (btn) => copyToClipboard(opt.content, btn),
                                onApply: (btn) => {
                                    const targetInp = resolveFirstInput([
                                        '[data-sync-key*="invitation_message"]',
                                        '[data-sync-key*="invitation_note"]',
                                        '[data-sync-key*="party_message"]',
                                        'textarea[name*="invitation_message"]',
                                        'textarea[name*="message"]',
                                    ]);
                                    if (dispatchInputValue(targetInp, opt.content)) {
                                        markAppliedAndClose(btn);
                                    } else {
                                        copyToClipboard(opt.content, btn);
                                    }
                                },
                            })
                        );
                        setResultsNodes(aiInvitationsResults, cards);
                    } else {
                        setResultsNodes(
                            aiInvitationsResults,
                            renderStatusBanner(doc, data?.message || 'Không thể tạo gợi ý. Vui lòng thử lại.', {
                                variant: 'error',
                            })
                        );
                    }
                },
            });
        });
    }

    // --- TAB 2: LỜI NGỎ & KỶ NIỆM ---
    const aiGroomInp = q('#ai-story-groom');
    const aiBrideInp = q('#ai-story-bride');
    const aiKeywordsInp = q('#ai-story-keywords');
    const aiToneBtns = qAll('.mayhen_ai_tone_btn');
    const btnGenAiStory = q('#btn-generate-ai-story');
    const aiStoryResults = q('#ai-story-results');
    let selectedStoryTone = 'romantic';

    aiToneBtns.forEach((btn) => {
        on(btn, 'click', () => {
            selectedStoryTone = btn.getAttribute('data-tone') || 'romantic';
            aiToneBtns.forEach((b) => {
                const active = b === btn;
                b.classList.toggle('is-active', active);
                b.style.borderColor = active ? '#C5A880' : '#E5DCCD';
                b.style.background = active ? '#FAF8F5' : '#FFF';
                b.style.fontWeight = active ? '600' : 'normal';
            });
        });
    });

    if (btnGenAiStory) {
        on(btnGenAiStory, 'click', () => {
            runAiAction({
                button: btnGenAiStory,
                loadingButtonText: '⏳ Đang dệt nên câu chuyện...',
                resultsEl: aiStoryResults,
                loadingMessage: 'Đang phân tích kỷ niệm và tạo 3 phương án Quiet Luxury...',
                errorMessage: 'Đã xảy ra lỗi khi tạo gợi ý. Vui lòng thử lại.',
                request: () =>
                    client.generateStory({
                        groom_name: aiGroomInp?.value || 'Chú Rể',
                        bride_name: aiBrideInp?.value || 'Cô Dâu',
                        keywords: aiKeywordsInp?.value || '',
                        tone: selectedStoryTone,
                    }),
                onSuccess: (data) => {
                    if (data && Array.isArray(data.options) && aiStoryResults) {
                        const cards = data.options.map((opt) =>
                            renderOptionCard(doc, {
                                tag: opt.style_name || opt.tag,
                                title: opt.title,
                                subtitle: opt.subtitle,
                                quoteSubtitle: true,
                                content: opt.content,
                                copyButtonClass: 'btn-copy-story',
                                applyButtonClass: 'btn-apply-story',
                                onCopy: (btn) => copyToClipboard(opt.content, btn),
                                onApply: (btn) => {
                                    dispatchInputValue(
                                        resolveFirstInput(['[data-sync-key*="story_title"]', 'input[name*="story_title"]']),
                                        opt.title
                                    );
                                    dispatchInputValue(
                                        resolveFirstInput(['[data-sync-key*="story_subtitle"]', 'input[name*="story_subtitle"]']),
                                        opt.subtitle
                                    );
                                    dispatchInputValue(
                                        resolveFirstInput(['[data-sync-key*="story_content"]', 'textarea[name*="story_content"]']),
                                        opt.content
                                    );
                                    markAppliedAndClose(btn);
                                },
                            })
                        );
                        setResultsNodes(aiStoryResults, cards);
                    }
                },
            });
        });
    }

    // --- TAB 3: LỜI CẢM ƠN (THANK YOU) ---
    const btnGenAiThankYou = q('#btn-generate-ai-thank-you');
    const aiThankYouResults = q('#ai-thank-you-results');
    if (btnGenAiThankYou && aiThankYouResults) {
        on(btnGenAiThankYou, 'click', () => {
            const { groom, bride } = getCoupleNames();
            runAiAction({
                button: btnGenAiThankYou,
                loadingButtonText: '⏳ Đang tạo lời cảm ơn tri ân...',
                resultsEl: aiThankYouResults,
                loadingMessage: 'Đang dệt nên tâm thư ấm áp...',
                errorMessage: 'Đã xảy ra lỗi khi tạo gợi ý. Vui lòng thử lại.',
                request: () =>
                    client.generateThankYou({
                        groom_name: groom,
                        bride_name: bride,
                        audience: q('#ai-thank-audience')?.value || 'all_guests',
                        tone: q('#ai-thank-tone')?.value || 'heartfelt',
                    }),
                onSuccess: (data) => {
                    if (data?.success && Array.isArray(data.options)) {
                        const cards = data.options.map((opt) =>
                            renderOptionCard(doc, {
                                tag: opt.tag,
                                title: opt.title,
                                content: opt.content,
                                copyButtonClass: 'btn-copy-thank',
                                applyButtonClass: 'btn-apply-thank',
                                onCopy: (btn) => copyToClipboard(opt.content, btn),
                                onApply: (btn) => {
                                    const targetInp = resolveFirstInput([
                                        '[data-sync-key*="thank_you"]',
                                        '[data-sync-key*="gift_thank"]',
                                        'textarea[name*="thank_you"]',
                                        'textarea[name*="gift"]',
                                    ]);
                                    if (dispatchInputValue(targetInp, opt.content)) {
                                        markAppliedAndClose(btn);
                                    } else {
                                        copyToClipboard(opt.content, btn);
                                    }
                                },
                            })
                        );
                        setResultsNodes(aiThankYouResults, cards);
                    } else {
                        setResultsNodes(
                            aiThankYouResults,
                            renderStatusBanner(doc, data?.message || 'Không thể tạo gợi ý. Vui lòng thử lại.', {
                                variant: 'error',
                            })
                        );
                    }
                },
            });
        });
    }

    // --- TAB 4: SOÁT LỖI CHÍNH TẢ & TINH CHỈNH ---
    const btnRunAiPolish = q('#btn-run-ai-polish');
    const aiPolishInput = q('#ai-polish-input');
    const aiPolishResults = q('#ai-polish-results');
    if (btnRunAiPolish && aiPolishInput && aiPolishResults) {
        on(btnRunAiPolish, 'click', () => {
            const text = aiPolishInput.value.trim();
            if (!text) {
                setResultsNodes(
                    aiPolishResults,
                    renderStatusBanner(doc, 'Vui lòng nhập hoặc dán văn bản cần kiểm tra.', { variant: 'validation' })
                );
                return;
            }

            runAiAction({
                button: btnRunAiPolish,
                loadingButtonText: '⏳ Đang kiểm tra...',
                resultsEl: aiPolishResults,
                loadingMessage: 'Đang rà soát chính tả và đối chiếu phong cách Quiet Luxury...',
                errorMessage: 'Đã xảy ra lỗi khi kiểm tra. Vui lòng thử lại.',
                request: () =>
                    client.polishText({
                        text,
                        style: q('#ai-polish-style')?.value || 'quiet_luxury',
                    }),
                onSuccess: (data) => {
                    if (data?.success) {
                        const nodes = renderPolishResult(doc, data, {
                            onCopy: (polished, btn) => copyToClipboard(polished, btn),
                            onReplace: (polished, btn) => {
                                aiPolishInput.value = polished;
                                btn.textContent = '✓ Đã thay thế!';
                                btn.style.background = '#2E7D32';
                            },
                        });
                        setResultsNodes(aiPolishResults, nodes);
                    } else {
                        setResultsNodes(
                            aiPolishResults,
                            renderStatusBanner(doc, data?.message || 'Không thể kiểm tra văn bản.', {
                                variant: 'error',
                            })
                        );
                    }
                },
            });
        });
    }

    // --- TAB 5: DỊCH ĐA NGÔN NGỮ (BẢO TOÀN TÊN RIÊNG) ---
    const btnRunAiTranslate = q('#btn-run-ai-translate');
    const aiTranslateInput = q('#ai-translate-input');
    const aiTranslateResults = q('#ai-translate-results');
    if (btnRunAiTranslate && aiTranslateInput && aiTranslateResults) {
        on(btnRunAiTranslate, 'click', () => {
            const text = aiTranslateInput.value.trim();
            if (!text) {
                setResultsNodes(
                    aiTranslateResults,
                    renderStatusBanner(doc, 'Vui lòng nhập văn bản cần dịch.', { variant: 'validation' })
                );
                return;
            }

            const preserveEntities = [
                q('[data-sync-key="wedding.groom_name"]')?.value,
                q('[data-sync-key="wedding.bride_name"]')?.value,
                q('[data-sync-key="wedding.extra_data.event_party_address"]')?.value,
            ].filter(Boolean);

            runAiAction({
                button: btnRunAiTranslate,
                loadingButtonText: '⏳ Đang dịch nghi thức cưới...',
                resultsEl: aiTranslateResults,
                loadingMessage: 'Đang dịch thuật và khóa an toàn 100% tên riêng...',
                errorMessage: 'Đã xảy ra lỗi khi dịch thuật. Vui lòng thử lại.',
                request: () =>
                    client.translateText({
                        text,
                        target_lang: q('#ai-translate-lang')?.value || 'en',
                        preserve_entities: preserveEntities,
                    }),
                onSuccess: (data) => {
                    if (data?.success) {
                        const card = renderTranslateResult(doc, data, {
                            onCopy: (translated, btn) => copyToClipboard(translated, btn),
                        });
                        setResultsNodes(aiTranslateResults, card);
                    } else {
                        setResultsNodes(
                            aiTranslateResults,
                            renderStatusBanner(doc, data?.message || 'Không thể dịch nội dung.', {
                                variant: 'error',
                            })
                        );
                    }
                },
            });
        });
    }

    return {
        open: openAiModal,
        close: closeAiModal,
        switchTab: switchAiTab,
        client,
    };
};
