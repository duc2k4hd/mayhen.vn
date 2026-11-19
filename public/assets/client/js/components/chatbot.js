/**
 * Mây Hẹn — Client Zero-Trust Grounded AI Chatbot Widget
 * Pure Vanilla ES Module — Zero external libraries, strictly XSS-safe DOM rendering (textContent only).
 */

const initMayhenChatbot = () => {
    const root = document.getElementById('mayhenClientChatbot');
    if (!root) {
        return;
    }

    const askUrl = root.dataset.askUrl || '/api/ai-chatbot/ask';
    const trainUrl = root.dataset.trainUrl || '/api/ai-chatbot/train';
    const feedbackUrl = root.dataset.feedbackUrl || '/api/ai-chatbot/feedback';

    const getTriggers = () => Array.from(document.querySelectorAll('[data-chatbot-trigger="true"]'));
    const closeBtn = document.getElementById('mayhenChatbotCloseBtn');
    const popup = document.getElementById('mayhenChatbotPopup');
    const messagesEl = document.getElementById('mayhenChatbotMessages');
    const quickChipsEl = document.getElementById('mayhenChatbotQuickChips');
    const form = document.getElementById('mayhenChatbotForm');
    const input = document.getElementById('mayhenChatbotInput');
    const sendBtn = document.getElementById('mayhenChatbotSendBtn');

    const trainToggleBtn = document.getElementById('mayhenChatbotTrainToggleBtn');
    const trainCancelBtn = document.getElementById('mayhenChatbotTrainCancelBtn');
    const trainPanel = document.getElementById('mayhenChatbotTrainPanel');
    const trainForm = document.getElementById('mayhenChatbotTrainForm');
    const trainQuestion = document.getElementById('mayhenChatbotTrainQuestion');
    const trainAnswer = document.getElementById('mayhenChatbotTrainAnswer');
    const trainCategory = document.getElementById('mayhenChatbotTrainCategory');
    const trainStatus = document.getElementById('mayhenChatbotTrainStatus');
    const trainSubmitBtn = document.getElementById('mayhenChatbotTrainSubmitBtn');

    let isBusy = false;
    let lastUserQuestion = '';

    const getCsrfToken = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') || '' : '';
    };

    const isSafeRelativeOrSameOriginUrl = (url) => {
        if (typeof url !== 'string' || url.trim() === '') {
            return false;
        }
        if (url.startsWith('/') && !url.startsWith('//')) {
            return true;
        }
        try {
            const parsed = new URL(url, window.location.origin);
            return parsed.origin === window.location.origin;
        } catch {
            return false;
        }
    };

    const scrollToBottom = () => {
        if (messagesEl) {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }
    };

    const setPopupOpen = (open) => {
        if (!popup) {
            return;
        }
        popup.classList.toggle('mayhen_client_chatbot_open', open);
        popup.setAttribute('aria-hidden', open ? 'false' : 'true');
        getTriggers().forEach((trigger) => {
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            trigger.classList.toggle('mayhen_client_bottom_nav_item_active', open);
        });
        if (open && input) {
            window.setTimeout(() => input.focus(), 120);
        }
    };

    const setTrainPanelOpen = (open, prefillQuestion = '') => {
        if (!trainPanel) {
            return;
        }
        trainPanel.hidden = !open;
        if (open) {
            if (trainStatus) {
                trainStatus.textContent = '';
                trainStatus.classList.remove('mayhen_client_chatbot_error');
            }
            if (prefillQuestion && trainQuestion) {
                trainQuestion.value = prefillQuestion.slice(0, 190);
            }
            if (trainAnswer) {
                trainAnswer.focus();
            }
        }
    };

    const appendUserBubble = (text) => {
        const wrap = document.createElement('div');
        wrap.className = 'mayhen_client_chatbot_msg mayhen_client_chatbot_msg_user';
        const bubble = document.createElement('div');
        bubble.className = 'mayhen_client_chatbot_bubble';
        const p = document.createElement('p');
        p.className = 'mayhen_client_chatbot_text';
        p.textContent = text;
        bubble.appendChild(p);
        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        scrollToBottom();
    };

    const appendTypingBubble = () => {
        const wrap = document.createElement('div');
        wrap.className = 'mayhen_client_chatbot_msg mayhen_client_chatbot_msg_bot';
        const bubble = document.createElement('div');
        bubble.className = 'mayhen_client_chatbot_bubble';
        const p = document.createElement('p');
        p.className = 'mayhen_client_chatbot_text';
        p.textContent = 'Đang tra cứu dữ liệu xác thực từ Mây Hẹn...';
        bubble.appendChild(p);
        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        scrollToBottom();
        return wrap;
    };

    const sendFeedback = async (logId, feedback, clickedBtn, siblingBtn) => {
        try {
            const res = await fetch(feedbackUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ log_id: logId, feedback }),
            });
            if (res.ok) {
                clickedBtn.classList.add('mayhen_client_chatbot_action_active');
                clickedBtn.disabled = true;
                if (siblingBtn) {
                    siblingBtn.disabled = true;
                }
            }
        } catch {
            // Silent ignore network glitch for non-blocking feedback
        }
    };

    const appendBotResponse = (data, questionAsked) => {
        const wrap = document.createElement('div');
        wrap.className = 'mayhen_client_chatbot_msg mayhen_client_chatbot_msg_bot';

        const bubble = document.createElement('div');
        bubble.className = 'mayhen_client_chatbot_bubble';

        const textEl = document.createElement('p');
        textEl.className = 'mayhen_client_chatbot_text';
        textEl.textContent = data.reply || 'Mình chưa tìm thấy thông tin phù hợp trong hệ thống.';
        bubble.appendChild(textEl);

        // 1. Thẻ gợi ý Mẫu Thiệp Cưới (xác thực 100% từ DB)
        if (Array.isArray(data.templates) && data.templates.length > 0) {
            const group = document.createElement('div');
            group.className = 'mayhen_client_chatbot_cards_group';

            const label = document.createElement('div');
            label.className = 'mayhen_client_chatbot_cards_label';
            label.textContent = 'Mẫu thiệp cưới phù hợp';
            group.appendChild(label);

            data.templates.forEach((tpl) => {
                const card = document.createElement('div');
                card.className = 'mayhen_client_chatbot_tpl_card';

                if (tpl.thumbnail_url && isSafeRelativeOrSameOriginUrl(tpl.thumbnail_url)) {
                    const img = document.createElement('img');
                    img.className = 'mayhen_client_chatbot_tpl_thumb';
                    img.src = tpl.thumbnail_url;
                    img.alt = tpl.name || 'Mẫu thiệp cưới';
                    img.loading = 'lazy';
                    card.appendChild(img);
                }

                const body = document.createElement('div');
                body.className = 'mayhen_client_chatbot_tpl_body';

                const name = document.createElement('p');
                name.className = 'mayhen_client_chatbot_tpl_name';
                name.textContent = tpl.name || 'Mẫu thiệp Mây Hẹn';
                body.appendChild(name);

                const meta = document.createElement('p');
                meta.className = 'mayhen_client_chatbot_tpl_meta';
                meta.textContent = `Phong cách: ${tpl.category || 'luxury'} • Gói: ${(tpl.tier || 'free').toUpperCase()}`;
                body.appendChild(meta);

                const links = document.createElement('div');
                links.className = 'mayhen_client_chatbot_tpl_links';

                if (isSafeRelativeOrSameOriginUrl(tpl.preview_url)) {
                    const previewLink = document.createElement('a');
                    previewLink.className = 'mayhen_client_chatbot_link_btn';
                    previewLink.href = tpl.preview_url;
                    previewLink.target = '_blank';
                    previewLink.rel = 'noopener noreferrer';
                    previewLink.textContent = 'Xem thử';
                    links.appendChild(previewLink);
                }

                if (isSafeRelativeOrSameOriginUrl(tpl.editor_url)) {
                    const createLink = document.createElement('a');
                    createLink.className = 'mayhen_client_chatbot_link_btn mayhen_client_chatbot_link_btn_primary';
                    createLink.href = tpl.editor_url;
                    createLink.textContent = 'Tạo thiệp ngay';
                    links.appendChild(createLink);
                }

                body.appendChild(links);
                card.appendChild(body);
                group.appendChild(card);
            });

            bubble.appendChild(group);
        }

        // 2. Thẻ Tài Liệu Hướng Dẫn Khách Hàng (Client Docs)
        if (Array.isArray(data.docs) && data.docs.length > 0) {
            const group = document.createElement('div');
            group.className = 'mayhen_client_chatbot_cards_group';

            const label = document.createElement('div');
            label.className = 'mayhen_client_chatbot_cards_label';
            label.textContent = 'Hướng dẫn chi tiết (Docs)';
            group.appendChild(label);

            data.docs.forEach((doc) => {
                if (!isSafeRelativeOrSameOriginUrl(doc.url)) {
                    return;
                }
                const link = document.createElement('a');
                link.className = 'mayhen_client_chatbot_doc_card';
                link.href = doc.url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';

                const title = document.createElement('p');
                title.className = 'mayhen_client_chatbot_doc_title';
                title.textContent = `${doc.title || 'Xem hướng dẫn'} →`;
                link.appendChild(title);

                if (doc.summary) {
                    const summary = document.createElement('p');
                    summary.className = 'mayhen_client_chatbot_doc_summary';
                    summary.textContent = doc.summary;
                    link.appendChild(summary);
                }

                group.appendChild(link);
            });

            bubble.appendChild(group);
        }

        // 3. Thanh đánh giá & Góp ý dạy AI (luôn đi qua Admin duyệt)
        if (data.log_id && data.can_train) {
            const bar = document.createElement('div');
            bar.className = 'mayhen_client_chatbot_actions_bar';

            const upBtn = document.createElement('button');
            upBtn.type = 'button';
            upBtn.className = 'mayhen_client_chatbot_action_btn';
            upBtn.textContent = 'Hữu ích';

            const downBtn = document.createElement('button');
            downBtn.type = 'button';
            downBtn.className = 'mayhen_client_chatbot_action_btn';
            downBtn.textContent = 'Chưa đúng';

            const trainBtn = document.createElement('button');
            trainBtn.type = 'button';
            trainBtn.className = 'mayhen_client_chatbot_action_btn';
            trainBtn.textContent = 'Góp ý dạy AI';

            upBtn.addEventListener('click', () => sendFeedback(data.log_id, 'helpful', upBtn, downBtn));
            downBtn.addEventListener('click', () => {
                sendFeedback(data.log_id, 'unhelpful', downBtn, upBtn);
                setTrainPanelOpen(true, questionAsked);
            });
            trainBtn.addEventListener('click', () => setTrainPanelOpen(true, questionAsked));

            bar.appendChild(upBtn);
            bar.appendChild(downBtn);
            bar.appendChild(trainBtn);
            bubble.appendChild(bar);
        }

        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        scrollToBottom();
    };

    const askChatbot = async (messageText) => {
        const trimmed = messageText.trim();
        if (trimmed.length < 2 || isBusy) {
            return;
        }

        isBusy = true;
        lastUserQuestion = trimmed;
        if (sendBtn) {
            sendBtn.disabled = true;
        }
        if (quickChipsEl) {
            quickChipsEl.hidden = true;
        }
        setTrainPanelOpen(false);

        appendUserBubble(trimmed);
        const typingEl = appendTypingBubble();

        try {
            const res = await fetch(askUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ message: trimmed }),
            });

            const json = await res.json().catch(() => null);
            typingEl.remove();

            if (!res.ok || !json || !json.data) {
                const errMsg = (res.status === 422 && json && json.message)
                    ? json.message
                    : 'Hệ thống trợ lý đang bận xử lý, bạn vui lòng thử lại sau ít giây nhé.';
                appendBotResponse({ reply: errMsg, templates: [], docs: [], can_train: false }, trimmed);
            } else {
                appendBotResponse(json.data, trimmed);
            }
        } catch {
            typingEl.remove();
            appendBotResponse(
                { reply: 'Không thể kết nối tới Trợ lý lúc này. Bạn có thể xem nhanh tài liệu tại /docs hoặc thử lại nhé.', templates: [], docs: [], can_train: false },
                trimmed
            );
        } finally {
            isBusy = false;
            if (sendBtn) {
                sendBtn.disabled = false;
            }
            if (input) {
                input.focus();
            }
        }
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-chatbot-trigger="true"]');
        if (!trigger) {
            return;
        }
        event.preventDefault();
        const isOpen = popup?.classList.contains('mayhen_client_chatbot_open');
        setPopupOpen(!isOpen);
    });

    closeBtn?.addEventListener('click', () => setPopupOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && popup?.classList.contains('mayhen_client_chatbot_open')) {
            setPopupOpen(false);
        }
    });

    quickChipsEl?.addEventListener('click', (event) => {
        const btn = event.target.closest('.mayhen_client_chatbot_chip');
        if (!btn) {
            return;
        }
        const prompt = btn.getAttribute('data-prompt') || '';
        if (prompt) {
            askChatbot(prompt);
        }
    });

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!input) {
            return;
        }
        const val = input.value;
        input.value = '';
        askChatbot(val);
    });

    trainToggleBtn?.addEventListener('click', () => {
        setTrainPanelOpen(trainPanel?.hidden ?? true, lastUserQuestion);
    });

    trainCancelBtn?.addEventListener('click', () => setTrainPanelOpen(false));

    trainForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!trainQuestion || !trainAnswer || !trainSubmitBtn || !trainStatus) {
            return;
        }

        trainSubmitBtn.disabled = true;
        trainStatus.textContent = 'Đang kiểm tra bảo mật và gửi đề xuất...';
        trainStatus.classList.remove('mayhen_client_chatbot_error');

        try {
            const res = await fetch(trainUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    category: trainCategory ? trainCategory.value : 'general',
                    question: trainQuestion.value,
                    suggested_answer: trainAnswer.value,
                }),
            });

            const json = await res.json().catch(() => null);
            if (res.ok && json && json.ok) {
                trainStatus.textContent = json.message || 'Đã gửi đề xuất chờ Admin duyệt!';
                trainQuestion.value = '';
                trainAnswer.value = '';
            } else {
                trainStatus.classList.add('mayhen_client_chatbot_error');
                trainStatus.textContent = (json && json.message) ? json.message : 'Vui lòng kiểm tra lại nội dung góp ý.';
            }
        } catch {
            trainStatus.classList.add('mayhen_client_chatbot_error');
            trainStatus.textContent = 'Không thể gửi đề xuất lúc này, vui lòng thử lại.';
        } finally {
            trainSubmitBtn.disabled = false;
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMayhenChatbot);
} else {
    initMayhenChatbot();
}
