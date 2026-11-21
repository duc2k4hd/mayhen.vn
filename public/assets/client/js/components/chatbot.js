/**
 * Mây Hẹn Intelligence Engine — Client Zero-Trust Grounded AI Chatbot Widget
 * Pure Vanilla ES Module — Zero external libraries, strictly XSS-safe DOM rendering (createElement/textContent only).
 * Không cho phép khách hàng nhập text dạy AI; chỉ ghi nhận phản hồi chất lượng Hữu ích / Chưa đúng.
 */

const initMayhenChatbot = () => {
    const root = document.getElementById('mayhenClientChatbot');
    if (!root) {
        return;
    }

    const askUrl = root.dataset.askUrl || '/api/ai-chatbot/ask';
    const feedbackUrl = root.dataset.feedbackUrl || '/api/ai-chatbot/feedback';
    const forgetMemoryUrl = root.dataset.forgetMemoryUrl || '/api/ai-chatbot/memory';

    const getTriggers = () => Array.from(document.querySelectorAll('[data-chatbot-trigger="true"]'));
    const closeBtn = document.getElementById('mayhenChatbotCloseBtn');
    const clearBtn = document.getElementById('mayhenChatbotClearBtn');
    const popup = document.getElementById('mayhenChatbotPopup');
    const messagesEl = document.getElementById('mayhenChatbotMessages');
    const quickChipsEl = document.getElementById('mayhenChatbotQuickChips');
    const form = document.getElementById('mayhenChatbotForm');
    const input = document.getElementById('mayhenChatbotInput');
    const sendBtn = document.getElementById('mayhenChatbotSendBtn');

    const HISTORY_STORAGE_KEY = 'mayhen_client_chatbot_history_v1';
    const OPEN_STATE_KEY = 'mayhen_client_chatbot_open_v1';
    const HISTORY_TTL_MS = 24 * 60 * 60 * 1000;
    const MAX_HISTORY_ITEMS = 40;

    let isBusy = false;
    let chatHistory = [];

    const saveChatHistory = () => {
        try {
            const trimmed = chatHistory.slice(-MAX_HISTORY_ITEMS);
            chatHistory = trimmed;
            window.localStorage.setItem(
                HISTORY_STORAGE_KEY,
                JSON.stringify({ updatedAt: Date.now(), items: trimmed })
            );
        } catch {
            // Bỏ qua nếu trình duyệt chặn localStorage
        }
        if (clearBtn) {
            clearBtn.hidden = chatHistory.length === 0;
        }
    };

    const loadChatHistory = () => {
        try {
            const raw = window.localStorage.getItem(HISTORY_STORAGE_KEY);
            if (!raw) {
                return [];
            }
            const parsed = JSON.parse(raw);
            if (!parsed || !Array.isArray(parsed.items)) {
                return [];
            }
            if (typeof parsed.updatedAt === 'number' && (Date.now() - parsed.updatedAt) > HISTORY_TTL_MS) {
                window.localStorage.removeItem(HISTORY_STORAGE_KEY);
                return [];
            }
            return parsed.items.slice(-MAX_HISTORY_ITEMS);
        } catch {
            return [];
        }
    };

    const saveOpenState = (open) => {
        try {
            window.sessionStorage.setItem(OPEN_STATE_KEY, open ? '1' : '0');
        } catch {
            // Silent ignore
        }
    };

    const loadOpenState = () => {
        try {
            return window.sessionStorage.getItem(OPEN_STATE_KEY) === '1';
        } catch {
            return false;
        }
    };

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

    const isNearBottom = () => {
        if (!messagesEl) {
            return true;
        }
        return (messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight) < 110;
    };

    const scrollToBottom = (force = false) => {
        if (messagesEl && (force || isNearBottom())) {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }
    };

    const appendInlineFormattedNodes = (parentEl, text) => {
        const tokenRegex = /\[([^\]]+)\]\(([^)\s]+)\)|\*\*([^*]+)\*\*|__([^_]+)__|`([^`]+)`|(?<!\*)\*([^*\n]+)\*(?!\*)|(?<=^|[\s(])(\/(?:docs|mau-thiep|xem-thu|tao-thiep|bang-gia|lien-he|dich-vu|cau-hoi-thuong-gap)(?:\/[a-zA-Z0-9\-_/?=&%#]+)?)/g;
        let lastIndex = 0;
        let match;

        while ((match = tokenRegex.exec(text)) !== null) {
            if (match.index > lastIndex) {
                parentEl.appendChild(document.createTextNode(text.slice(lastIndex, match.index)));
            }

            if (match[1] !== undefined && match[2] !== undefined) {
                const label = match[1].trim();
                const href = match[2].trim();
                if (isSafeRelativeOrSameOriginUrl(href)) {
                    const a = document.createElement('a');
                    a.className = 'mayhen_client_chatbot_inline_link';
                    a.href = href;
                    a.target = '_blank';
                    a.rel = 'noopener noreferrer';
                    a.textContent = label || href;
                    parentEl.appendChild(a);
                } else {
                    parentEl.appendChild(document.createTextNode(label));
                }
            } else if (match[3] !== undefined || match[4] !== undefined) {
                const strong = document.createElement('strong');
                strong.textContent = match[3] !== undefined ? match[3] : match[4];
                parentEl.appendChild(strong);
            } else if (match[5] !== undefined) {
                const code = document.createElement('code');
                code.className = 'mayhen_client_chatbot_inline_code';
                code.textContent = match[5];
                parentEl.appendChild(code);
            } else if (match[6] !== undefined) {
                const em = document.createElement('em');
                em.textContent = match[6];
                parentEl.appendChild(em);
            } else if (match[7] !== undefined) {
                const rawPath = match[7].replace(/[.,;:!?)]+$/, '');
                const trailingPunct = match[7].slice(rawPath.length);
                if (isSafeRelativeOrSameOriginUrl(rawPath)) {
                    const a = document.createElement('a');
                    a.className = 'mayhen_client_chatbot_inline_link';
                    a.href = rawPath;
                    a.target = '_blank';
                    a.rel = 'noopener noreferrer';
                    a.textContent = rawPath;
                    parentEl.appendChild(a);
                } else {
                    parentEl.appendChild(document.createTextNode(rawPath));
                }
                if (trailingPunct) {
                    parentEl.appendChild(document.createTextNode(trailingPunct));
                }
            }

            lastIndex = tokenRegex.lastIndex;
        }

        if (lastIndex < text.length) {
            parentEl.appendChild(document.createTextNode(text.slice(lastIndex)));
        }
    };

    const renderFormattedBotText = (containerEl, rawText) => {
        const normalized = String(rawText || '').replace(/\r\n?/g, '\n').trim();
        const lines = normalized.split('\n');

        lines.forEach((rawLine, idx) => {
            const line = rawLine.trim();
            if (line === '') {
                return;
            }

            const p = document.createElement('p');
            p.className = 'mayhen_client_chatbot_text mayhen_client_chatbot_text_line';

            const headingMatch = line.match(/^#{1,6}\s+(.+)$/);
            if (headingMatch) {
                const strong = document.createElement('strong');
                appendInlineFormattedNodes(strong, headingMatch[1]);
                p.appendChild(strong);
                containerEl.appendChild(p);
                return;
            }

            const bulletMatch = line.match(/^[-*•]\s+(.+)$/);
            if (bulletMatch) {
                p.appendChild(document.createTextNode('• '));
                appendInlineFormattedNodes(p, bulletMatch[1]);
                containerEl.appendChild(p);
                return;
            }

            appendInlineFormattedNodes(p, line);
            if (idx === lines.length - 1) {
                p.classList.add('mayhen_client_chatbot_text_line_last');
            }
            containerEl.appendChild(p);
        });
    };

    const syncMobileScrollLock = (open) => {
        const shouldLock = Boolean(open) && window.innerWidth <= 767;
        document.documentElement.classList.toggle('mayhen_client_chatbot_mobile_locked', shouldLock);
        document.body.classList.toggle('mayhen_client_chatbot_mobile_locked', shouldLock);
    };

    const setPopupOpen = (open, persist = true) => {
        if (!popup) {
            return;
        }
        popup.classList.toggle('mayhen_client_chatbot_open', open);
        popup.setAttribute('aria-hidden', open ? 'false' : 'true');
        syncMobileScrollLock(open);
        getTriggers().forEach((trigger) => {
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            trigger.classList.toggle('mayhen_client_bottom_nav_item_active', open);
        });
        if (persist) {
            saveOpenState(open);
        }
        if (open) {
            scrollToBottom(true);
            if (input && window.innerWidth > 767) {
                window.setTimeout(() => input.focus(), 120);
            }
        }
    };

    window.addEventListener('resize', () => {
        if (popup) {
            syncMobileScrollLock(popup.classList.contains('mayhen_client_chatbot_open'));
        }
    });

    const appendUserBubble = (text, persist = true) => {
        const wrap = document.createElement('div');
        wrap.className = 'mayhen_client_chatbot_msg mayhen_client_chatbot_msg_user';
        wrap.dataset.dynamicMsg = '1';
        const bubble = document.createElement('div');
        bubble.className = 'mayhen_client_chatbot_bubble';
        const p = document.createElement('p');
        p.className = 'mayhen_client_chatbot_text';
        p.textContent = text;
        bubble.appendChild(p);
        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        if (persist) {
            chatHistory.push({ role: 'user', text });
            saveChatHistory();
        }
        scrollToBottom();
    };

    const appendTypingBubble = () => {
        const wrap = document.createElement('div');
        wrap.className = 'mayhen_client_chatbot_msg mayhen_client_chatbot_msg_bot';

        const bubble = document.createElement('div');
        bubble.className = 'mayhen_client_chatbot_bubble mayhen_client_chatbot_typing_bubble';
        bubble.setAttribute('aria-label', 'Đang soạn tin');

        const orb = document.createElement('span');
        orb.className = 'mayhen_client_chatbot_typing_orb';
        orb.setAttribute('aria-hidden', 'true');
        const svgNS = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(svgNS, 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '2');
        svg.setAttribute('stroke-linecap', 'round');
        svg.setAttribute('stroke-linejoin', 'round');
        const path1 = document.createElementNS(svgNS, 'path');
        path1.setAttribute('d', 'M12 19l7-7 3 3-7 7-3-3z');
        const path2 = document.createElementNS(svgNS, 'path');
        path2.setAttribute('d', 'M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z');
        const path3 = document.createElementNS(svgNS, 'path');
        path3.setAttribute('d', 'M2 2l7.586 7.586');
        svg.appendChild(path1);
        svg.appendChild(path2);
        svg.appendChild(path3);
        orb.appendChild(svg);

        const content = document.createElement('div');
        content.className = 'mayhen_client_chatbot_typing_content';

        const row = document.createElement('div');
        row.className = 'mayhen_client_chatbot_typing_row';

        const label = document.createElement('span');
        label.className = 'mayhen_client_chatbot_typing_label';
        label.textContent = 'Đang soạn tin';

        const dots = document.createElement('span');
        dots.className = 'mayhen_client_chatbot_typing_dots';
        dots.setAttribute('aria-hidden', 'true');
        for (let i = 0; i < 3; i += 1) {
            const dot = document.createElement('span');
            dot.className = 'mayhen_client_chatbot_typing_dot';
            dots.appendChild(dot);
        }

        row.appendChild(label);
        row.appendChild(dots);

        const bar = document.createElement('div');
        bar.className = 'mayhen_client_chatbot_typing_bar';
        bar.setAttribute('aria-hidden', 'true');

        content.appendChild(row);
        content.appendChild(bar);

        bubble.appendChild(orb);
        bubble.appendChild(content);
        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        scrollToBottom(true);
        return wrap;
    };

    const animateBotTextStream = (containerEl, rawText) => new Promise((resolve) => {
        const normalized = String(rawText || '').replace(/\r\n?/g, '\n').trim();
        const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!normalized || prefersReduced) {
            renderFormattedBotText(containerEl, normalized);
            resolve();
            return;
        }

        // Tách theo từ + khoảng trắng/xuống dòng để gõ mượt từng nhịp tự nhiên
        const tokens = normalized.match(/\S+\s*|\n/g) || [normalized];
        const totalTokens = tokens.length;
        // Tốc độ thích ứng: ngắn thì gõ thong thả, dài thì gõ nhanh hơn để tổng thời gian luôn ~0.9s - 2.4s
        const stepSize = totalTokens > 90 ? 3 : totalTokens > 45 ? 2 : 1;
        const intervalMs = totalTokens > 90 ? 22 : 26;

        let cursorIdx = 0;
        let currentText = '';

        const caret = document.createElement('span');
        caret.className = 'mayhen_client_chatbot_caret';
        caret.setAttribute('aria-hidden', 'true');

        const step = () => {
            const nextEnd = Math.min(totalTokens, cursorIdx + stepSize);
            for (let i = cursorIdx; i < nextEnd; i += 1) {
                currentText += tokens[i];
            }
            cursorIdx = nextEnd;

            containerEl.textContent = '';
            renderFormattedBotText(containerEl, currentText);

            if (cursorIdx < totalTokens) {
                const lastLine = containerEl.lastElementChild;
                if (lastLine) {
                    lastLine.appendChild(caret);
                } else {
                    containerEl.appendChild(caret);
                }
                scrollToBottom(false);
                window.setTimeout(step, intervalMs);
            } else {
                scrollToBottom(false);
                resolve();
            }
        };

        step();
    });

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
                const targetItem = chatHistory.find((item) => item.role === 'bot' && item.data && item.data.log_id === logId);
                if (targetItem) {
                    targetItem.feedback = feedback;
                    saveChatHistory();
                }
            }
        } catch {
            // Silent ignore network glitch for non-blocking feedback
        }
    };

    const appendBotResponse = async (data, questionAsked, persist = true, savedFeedback = null, animate = true) => {
        const wrap = document.createElement('div');
        wrap.className = 'mayhen_client_chatbot_msg mayhen_client_chatbot_msg_bot';
        wrap.dataset.dynamicMsg = '1';

        const bubble = document.createElement('div');
        bubble.className = 'mayhen_client_chatbot_bubble';

        const textGroup = document.createElement('div');
        textGroup.className = 'mayhen_client_chatbot_text_group';
        bubble.appendChild(textGroup);

        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        scrollToBottom(true);

        const replyText = data.reply || 'Mình chưa tìm thấy thông tin phù hợp trong hệ thống.';
        if (animate) {
            await animateBotTextStream(textGroup, replyText);
        } else {
            renderFormattedBotText(textGroup, replyText);
        }

        let staggerIdx = 0;
        const applyReveal = (el) => {
            if (!animate) {
                return;
            }
            el.classList.add('mayhen_client_chatbot_reveal_item');
            el.style.setProperty('--mh-stagger', `${staggerIdx * 75}ms`);
            staggerIdx += 1;
        };

        // 1. Thẻ gợi ý Mẫu Thiệp Cưới (xác thực 100% từ DB)
        if (Array.isArray(data.templates) && data.templates.length > 0) {
            const group = document.createElement('div');
            group.className = 'mayhen_client_chatbot_cards_group';

            const label = document.createElement('div');
            label.className = 'mayhen_client_chatbot_cards_label';
            label.textContent = 'Mẫu thiệp cưới phù hợp';
            applyReveal(label);
            group.appendChild(label);

            data.templates.forEach((tpl) => {
                const card = document.createElement('div');
                card.className = 'mayhen_client_chatbot_tpl_card';
                applyReveal(card);

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
            applyReveal(label);
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
                applyReveal(link);

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

        // 3. Thanh phản hồi chất lượng (CHỈ Hữu ích / Chưa đúng — không còn nút dạy AI từ khách hàng)
        const allowFeedback = data.allow_feedback !== false && Boolean(data.log_id);
        if (allowFeedback) {
            const bar = document.createElement('div');
            bar.className = 'mayhen_client_chatbot_actions_bar';
            applyReveal(bar);

            const upBtn = document.createElement('button');
            upBtn.type = 'button';
            upBtn.className = 'mayhen_client_chatbot_action_btn';
            upBtn.textContent = 'Hữu ích';

            const downBtn = document.createElement('button');
            downBtn.type = 'button';
            downBtn.className = 'mayhen_client_chatbot_action_btn';
            downBtn.textContent = 'Chưa đúng';

            if (savedFeedback === 'helpful') {
                upBtn.classList.add('mayhen_client_chatbot_action_active');
                upBtn.disabled = true;
                downBtn.disabled = true;
            } else if (savedFeedback === 'unhelpful') {
                downBtn.classList.add('mayhen_client_chatbot_action_active');
                upBtn.disabled = true;
                downBtn.disabled = true;
            }

            upBtn.addEventListener('click', () => sendFeedback(data.log_id, 'helpful', upBtn, downBtn));
            downBtn.addEventListener('click', () => sendFeedback(data.log_id, 'unhelpful', downBtn, upBtn));

            bar.appendChild(upBtn);
            bar.appendChild(downBtn);
            bubble.appendChild(bar);
        }

        if (persist) {
            chatHistory.push({
                role: 'bot',
                question: questionAsked || '',
                feedback: savedFeedback,
                data: {
                    log_id: data.log_id || null,
                    reply: data.reply || '',
                    templates: Array.isArray(data.templates) ? data.templates : [],
                    docs: Array.isArray(data.docs) ? data.docs : [],
                    allow_feedback: allowFeedback,
                },
            });
            saveChatHistory();
        }
        scrollToBottom(false);
    };

    const askChatbot = async (messageText) => {
        const trimmed = messageText.trim();
        if (trimmed.length < 2 || isBusy) {
            return;
        }

        isBusy = true;
        if (sendBtn) {
            sendBtn.disabled = true;
        }
        if (quickChipsEl) {
            quickChipsEl.hidden = true;
        }

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
                await appendBotResponse({ reply: errMsg, templates: [], docs: [], allow_feedback: false }, trimmed, true, null, true);
            } else {
                await appendBotResponse(json.data, trimmed, true, null, true);
            }
        } catch {
            typingEl.remove();
            await appendBotResponse(
                { reply: 'Không thể kết nối tới Trợ lý lúc này. Bạn có thể xem nhanh tài liệu tại /docs hoặc thử lại nhé.', templates: [], docs: [], allow_feedback: false },
                trimmed,
                true,
                null,
                true
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

    clearBtn?.addEventListener('click', () => {
        if (isBusy) {
            return;
        }
        chatHistory = [];
        try {
            window.localStorage.removeItem(HISTORY_STORAGE_KEY);
        } catch {
            // Silent ignore
        }
        messagesEl?.querySelectorAll('[data-dynamic-msg="1"]').forEach((node) => node.remove());
        if (quickChipsEl) {
            quickChipsEl.hidden = false;
        }
        if (clearBtn) {
            clearBtn.hidden = true;
        }
        // Xóa luôn Scoped Customer Memory phía server theo quyền riêng tư
        fetch(forgetMemoryUrl, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        }).catch(() => {});
        scrollToBottom();
    });

    // Khôi phục lịch sử hội thoại đã lưu khi người dùng tải lại hoặc chuyển trang
    const restoredHistory = loadChatHistory();
    if (restoredHistory.length > 0) {
        chatHistory = restoredHistory;
        if (quickChipsEl) {
            quickChipsEl.hidden = true;
        }
        if (clearBtn) {
            clearBtn.hidden = false;
        }
        restoredHistory.forEach((item) => {
            if (!item || typeof item !== 'object') {
                return;
            }
            if (item.role === 'user' && typeof item.text === 'string' && item.text.trim() !== '') {
                appendUserBubble(item.text, false);
            } else if (item.role === 'bot' && item.data && typeof item.data === 'object') {
                appendBotResponse(item.data, item.question || '', false, item.feedback || null, false);
            }
        });
    }

    if (loadOpenState()) {
        setPopupOpen(true, false);
    }

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
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMayhenChatbot);
} else {
    initMayhenChatbot();
}
