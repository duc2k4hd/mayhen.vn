import { initPersonality3DStages } from './personality-3d.js';

/**
 * Mây Hẹn – The Art of Personality (Vanilla ES Module)
 * - Assessment Session Runner: pagination (4 câu/nhóm hoặc 1 câu), keyboard 1-7, auto-save, kiểm tra câu bỏ sót.
 * - Result Reveal (2.5s + nút Bỏ qua), Evidence Inspector (Xem căn cứ), Interactive 3D Tilt/2D Toggle.
 * - AI Personality Coach AJAX & Canvas Share Card PNG (1:1, 4:5, 9:16).
 */

const initAssessmentRunner = () => {
    const root = document.getElementById('mayhen_personality_test_app');
    if (!root) return;

    const saveUrl = root.dataset.saveUrl || '';
    const submitUrl = root.dataset.submitUrl || '';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const totalQuestions = Number(root.dataset.total || 0);
    const scaleMax = Number(root.dataset.scaleMax || 7);
    const pageSize = Number(root.dataset.pageSize || 1);
    const autoAdvance = root.dataset.autoAdvance === '1';

    let responses = {};
    try {
        const parsed = JSON.parse(root.dataset.savedResponses || '{}');
        responses = parsed && !Array.isArray(parsed) && typeof parsed === 'object' ? parsed : {};
    } catch {
        responses = {};
    }

    const cards = Array.from(root.querySelectorAll('.mayhen_personality_test_qcard'));
    const totalPages = Math.max(1, Math.ceil(cards.length / pageSize));
    // Mở thẳng vào câu đầu tiên chưa trả lời khi người dùng refresh hoặc quay lại tiếp tục
    const firstUnansweredIdx = cards.findIndex((c) => responses[c.dataset.qid] === undefined || responses[c.dataset.qid] === null);
    let currentPage = firstUnansweredIdx >= 0 ? Math.floor(firstUnansweredIdx / pageSize) : totalPages - 1;
    let elapsedSeconds = Number(root.dataset.elapsed || 0);
    let saveTimer = null;
    let advanceTimer = null;
    const companion3D = root.querySelector('[data-sculpture-3d]');

    const progressFill = document.getElementById('mayhen_personality_progress_fill');
    const progressText = document.getElementById('mayhen_personality_progress_text');
    const saveStatus = document.getElementById('mayhen_personality_save_status');
    const prevBtn = document.getElementById('mayhen_personality_prev_btn');
    const nextBtn = document.getElementById('mayhen_personality_next_btn');
    const submitBtn = document.getElementById('mayhen_personality_submit_btn');
    const warningEl = document.getElementById('mayhen_personality_missing_warning');

    setInterval(() => {
        elapsedSeconds += 1;
    }, 1000);

    const countAnswered = () => Object.keys(responses).filter((k) => responses[k] !== null && responses[k] !== '').length;

    const updateProgress = () => {
        const answered = countAnswered();
        const pct = totalQuestions > 0 ? Math.min(100, Math.round((answered / totalQuestions) * 100)) : 0;
        if (progressFill) progressFill.style.width = `${pct}%`;
        if (progressText) {
            progressText.textContent = pageSize === 1
                ? `Câu ${currentPage + 1} / ${totalQuestions} • Đã trả lời ${answered} (${pct}%)`
                : `${answered} / ${totalQuestions} câu (${pct}%) — Trang ${currentPage + 1}/${totalPages}`;
        }
    };

    const renderPage = (shouldScroll = false) => {
        const start = currentPage * pageSize;
        const end = start + pageSize;
        cards.forEach((card, idx) => {
            const isVisible = idx >= start && idx < end;
            card.hidden = !isVisible;
            if (isVisible) {
                card.classList.remove('is-entering');
                void card.offsetWidth;
                card.classList.add('is-entering');
            }
        });
        if (prevBtn) prevBtn.disabled = currentPage === 0;
        if (nextBtn) nextBtn.hidden = currentPage >= totalPages - 1;
        if (submitBtn) submitBtn.hidden = currentPage < totalPages - 1;
        updateProgress();
        if (shouldScroll) {
            window.scrollTo({ top: Math.max(0, root.offsetTop - 90), behavior: 'smooth' });
        }
    };

    const scheduleAutoSave = () => {
        if (!saveUrl) return;
        if (saveStatus) saveStatus.textContent = 'Đang lưu...';
        clearTimeout(saveTimer);
        saveTimer = setTimeout(async () => {
            try {
                const res = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ responses, duration_seconds: elapsedSeconds }),
                });
                if (res.ok && saveStatus) {
                    saveStatus.textContent = 'Đã tự động lưu';
                }
            } catch {
                if (saveStatus) saveStatus.textContent = 'Lưu tạm trên máy (đợi kết nối)';
            }
        }, 1800);
    };

    const selectAnswer = (card, value, fromUserClick = false) => {
        const qid = card.dataset.qid;
        if (!qid) return;
        responses[qid] = value;
        card.classList.remove('is-unanswered');

        card.querySelectorAll('.mayhen_personality_test_opt').forEach((opt) => {
            const isMatch = String(opt.dataset.val) === String(value);
            opt.classList.toggle('is-selected', isMatch);
            const inp = opt.querySelector('input');
            if (inp) inp.checked = isMatch;
        });

        const unsureBtn = card.querySelector('.mayhen_personality_test_unsure_btn');
        if (unsureBtn) {
            unsureBtn.classList.toggle('is-selected', value === 'unsure');
        }

        updateProgress();
        if (fromUserClick) {
            scheduleAutoSave();
            // Xoay nhẹ tác phẩm 3D đồng hành để phản hồi tinh tế
            companion3D?.dispatchEvent(new CustomEvent('personality:switch-sculpture', {
                detail: { sculptureKey: 'prism_crystal', primary: '#1E4D3B', accent: '#CFA860' },
            }));
            if (autoAdvance && pageSize === 1 && currentPage < totalPages - 1) {
                clearTimeout(advanceTimer);
                advanceTimer = setTimeout(() => {
                    currentPage += 1;
                    renderPage(false);
                }, 260);
            }
        }
    };

    cards.forEach((card) => {
        const qid = card.dataset.qid;
        if (qid && responses[qid] !== undefined && responses[qid] !== null) {
            selectAnswer(card, responses[qid], false);
        }

        card.querySelectorAll('.mayhen_personality_test_opt').forEach((opt) => {
            opt.addEventListener('click', (e) => {
                e.preventDefault();
                selectAnswer(card, Number(opt.dataset.val), true);
            });
        });

        const unsureBtn = card.querySelector('.mayhen_personality_test_unsure_btn');
        if (unsureBtn) {
            unsureBtn.addEventListener('click', () => selectAnswer(card, 'unsure', true));
        }
    });

    // Phím 1..7 chọn đáp án, mũi tên Trái/Phải chuyển câu
    document.addEventListener('keydown', (e) => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName || '')) return;
        const num = Number(e.key);
        if (num >= 1 && num <= scaleMax) {
            const visibleCards = cards.filter((c) => !c.hidden);
            const target = visibleCards.find((c) => !responses[c.dataset.qid]) || visibleCards[0];
            if (target) selectAnswer(target, num, true);
        } else if (e.key === 'ArrowLeft' && currentPage > 0) {
            currentPage -= 1;
            renderPage(false);
        } else if (e.key === 'ArrowRight' && currentPage < totalPages - 1) {
            currentPage += 1;
            renderPage(false);
        }
    });

    prevBtn?.addEventListener('click', () => {
        if (currentPage > 0) {
            currentPage -= 1;
            renderPage();
        }
    });

    nextBtn?.addEventListener('click', () => {
        if (currentPage < totalPages - 1) {
            currentPage += 1;
            renderPage();
        }
    });

    submitBtn?.addEventListener('click', async () => {
        const missingCards = cards.filter((c) => responses[c.dataset.qid] === undefined || responses[c.dataset.qid] === null);
        if (missingCards.length > 0 && !submitBtn.dataset.confirmedMissing) {
            missingCards.forEach((c) => c.classList.add('is-unanswered'));
            const firstIdx = cards.indexOf(missingCards[0]);
            currentPage = Math.floor(firstIdx / pageSize);
            renderPage();
            if (warningEl) {
                warningEl.hidden = false;
                warningEl.textContent = `Còn ${missingCards.length} câu chưa trả lời (đã đánh dấu viền đỏ). Bạn có thể trả lời bổ sung hoặc bấm "Hoàn tất & Xem kết quả" thêm một lần nữa để nộp ngay.`;
            }
            submitBtn.dataset.confirmedMissing = '1';
            return;
        }

        clearTimeout(saveTimer);
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang tính toán kết quả xác định...';

        try {
            const res = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ responses, duration_seconds: elapsedSeconds }),
            });
            const data = await res.json().catch(() => null);
            if (res.ok && data?.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }
            submitBtn.disabled = false;
            submitBtn.textContent = 'Thử nộp bài lại';
            if (warningEl) {
                warningEl.hidden = false;
                warningEl.textContent = data?.message || `Không thể nộp bài (HTTP ${res.status}). Vui lòng bấm "Thử nộp bài lại" hoặc tải lại trang (F5) — câu trả lời đã được lưu tự động.`;
            }
        } catch {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Thử nộp bài lại';
            if (warningEl) {
                warningEl.hidden = false;
                warningEl.textContent = 'Mất kết nối tạm thời khi nộp bài. Câu trả lời đã lưu — vui lòng bấm "Thử nộp bài lại".';
            }
        }
    });

    renderPage();
};

const initRevealOverlay = () => {
    const overlay = document.getElementById('mayhen_personality_reveal_overlay');
    if (!overlay) return;

    const skipBtn = document.getElementById('mayhen_personality_reveal_skip');
    const letters = Array.from(overlay.querySelectorAll('.mayhen_personality_reveal_letter'));
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const close = () => {
        overlay.classList.add('is-hidden');
        setTimeout(() => overlay.remove(), 450);
    };

    skipBtn?.addEventListener('click', close);

    if (reduced) {
        letters.forEach((l) => l.classList.add('is-shown'));
        setTimeout(close, 500);
        return;
    }

    letters.forEach((el, idx) => {
        setTimeout(() => el.classList.add('is-shown'), 260 + idx * 360);
    });
    setTimeout(close, 2600);
};

const initInteractiveSculptures = () => {
    initPersonality3DStages();

    const modeToggle = document.getElementById('mayhen_personality_3d_toggle');
    const wraps = Array.from(document.querySelectorAll('.mayhen_personality_3d_wrap'));
    const cards = Array.from(document.querySelectorAll('.mayhen_personality_sculpture_card'));
    if (cards.length === 0 && wraps.length === 0) return;

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let is3D = !reduced;

    const applyMode = () => {
        cards.forEach((c) => {
            c.dataset.mode = is3D ? '3d' : '2d';
            if (!is3D) c.style.transform = '';
        });
        wraps.forEach((w) => {
            const l3d = w.querySelector('[data-layer-3d]');
            const l2d = w.querySelector('[data-layer-2d]');
            if (l3d && l2d) {
                l3d.hidden = !is3D;
                l2d.hidden = is3D;
            }
        });
        if (modeToggle) {
            modeToggle.textContent = is3D ? 'Đang bật 3D Tương tác (Chuyển sang 2D)' : 'Đang bật 2D Editorial (Chuyển sang 3D)';
        }
    };

    modeToggle?.addEventListener('click', () => {
        is3D = !is3D;
        applyMode();
    });

    applyMode();
};

const initEvidenceToggles = () => {
    document.querySelectorAll('[data-evidence-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-evidence-toggle');
            const panel = targetId ? document.getElementById(targetId) : null;
            if (!panel) return;
            const nextHidden = !panel.hidden;
            panel.hidden = nextHidden;
            btn.setAttribute('aria-expanded', String(!nextHidden));
        });
    });
};

const initCoachForm = () => {
    const form = document.getElementById('mayhen_personality_coach_form');
    if (!form) return;
    const input = form.querySelector('textarea[name="question"]');
    const list = document.getElementById('mayhen_personality_coach_list');
    const submitBtn = form.querySelector('button[type="submit"]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    form.querySelectorAll('[data-coach-prompt]').forEach((chip) => {
        chip.addEventListener('click', () => {
            if (input) {
                input.value = chip.getAttribute('data-coach-prompt') || '';
                input.focus();
            }
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const q = (input?.value || '').trim();
        if (q.length < 4 || !submitBtn) return;

        submitBtn.disabled = true;
        const oldLabel = submitBtn.textContent;
        submitBtn.textContent = 'Đang đối chiếu hồ sơ...';

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ question: q }),
            });
            const data = await res.json().catch(() => null);
            if (data?.ok && list) {
                const item = document.createElement('div');
                item.className = 'mayhen_personality_card';
                item.style.marginTop = '12px';
                const qEl = document.createElement('p');
                qEl.style.fontWeight = '700';
                qEl.style.margin = '0';
                qEl.textContent = `Bạn hỏi: ${q}`;
                const aEl = document.createElement('p');
                aEl.style.margin = '8px 0 0';
                aEl.style.lineHeight = '1.7';
                aEl.textContent = data.answer;
                item.appendChild(qEl);
                item.appendChild(aEl);
                list.prepend(item);
                if (input) input.value = '';
            } else if (data?.message && list) {
                const warn = document.createElement('div');
                warn.className = 'mayhen_personality_notice';
                warn.style.marginTop = '12px';
                warn.textContent = data.message;
                list.prepend(warn);
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = oldLabel;
        }
    });

    const autoStatusEl = document.getElementById('mayhen_personality_ai_auto_status');
    const aiGrid = document.getElementById('mayhen_personality_ai_layers_grid');
    if (autoStatusEl && autoStatusEl.dataset.autoAnalyze === '1' && autoStatusEl.dataset.analyzeUrl) {
        const analyzeUrl = autoStatusEl.dataset.analyzeUrl;
        fetch(analyzeUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            body: JSON.stringify({}),
        })
            .then((res) => res.json().catch(() => null))
            .then((data) => {
                if (data?.ok && Array.isArray(data?.report?.layers) && aiGrid) {
                    aiGrid.innerHTML = '';
                    data.report.layers.forEach((layer) => {
                        const card = document.createElement('div');
                        card.className = 'mayhen_personality_card';
                        const h3 = document.createElement('h3');
                        h3.className = 'mayhen_personality_h3';
                        h3.textContent = layer.title || '';
                        const p = document.createElement('p');
                        p.style.cssText = 'font-size:0.93rem; line-height:1.72; margin:6px 0 0;';
                        p.textContent = layer.content || '';
                        card.appendChild(h3);
                        card.appendChild(p);
                        aiGrid.appendChild(card);
                    });
                    autoStatusEl.textContent = data.source === 'llm_grounded'
                        ? 'Đã phân tích chuyên sâu bởi AI Mây Hẹn'
                        : 'Đã tổng hợp dựa trên câu trả lời của bạn';
                } else {
                    autoStatusEl.textContent = 'Đã tổng hợp dựa trên câu trả lời của bạn';
                }
            })
            .catch(() => {
                autoStatusEl.textContent = 'Đã tổng hợp dựa trên câu trả lời của bạn';
            });
    }
};

const initShareCardExport = () => {
    const buttons = document.querySelectorAll('[data-export-ratio]');
    const metaEl = document.getElementById('mayhen_personality_share_meta');
    if (buttons.length === 0 || !metaEl) return;

    const code = metaEl.dataset.code || 'BẢN SẮC';
    const artTitle = metaEl.dataset.artTitle || 'The Art of Personality';
    const vnName = metaEl.dataset.vnName || '';
    const summary = metaEl.dataset.summary || '';

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const ratio = btn.getAttribute('data-export-ratio') || '1:1';
            const dims = ratio === '9:16' ? [1080, 1920] : ratio === '4:5' ? [1080, 1350] : [1080, 1080];
            const canvas = document.createElement('canvas');
            canvas.width = dims[0];
            canvas.height = dims[1];
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            ctx.fillStyle = '#FAF5EB';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.strokeStyle = '#CFA860';
            ctx.lineWidth = 4;
            ctx.strokeRect(48, 48, canvas.width - 96, canvas.height - 96);

            ctx.fillStyle = '#1E4D3B';
            ctx.font = '600 26px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('MÂY HẸN • THE ART OF PERSONALITY', canvas.width / 2, 140);

            ctx.fillStyle = '#292524';
            ctx.font = '700 132px Georgia, serif';
            ctx.fillText(code, canvas.width / 2, Math.round(canvas.height * 0.42));

            ctx.fillStyle = '#85182A';
            ctx.font = '600 44px Georgia, serif';
            ctx.fillText(artTitle, canvas.width / 2, Math.round(canvas.height * 0.50));

            ctx.fillStyle = '#4E342E';
            ctx.font = '500 30px sans-serif';
            ctx.fillText(vnName, canvas.width / 2, Math.round(canvas.height * 0.56));

            ctx.fillStyle = '#676059';
            ctx.font = '400 26px sans-serif';
            ctx.fillText(summary.slice(0, 68), canvas.width / 2, Math.round(canvas.height * 0.65));

            ctx.fillStyle = '#1E4D3B';
            ctx.font = '600 24px sans-serif';
            ctx.fillText('mayhen.vn/mbti', canvas.width / 2, canvas.height - 104);

            const link = document.createElement('a');
            link.download = `mayhen-personality-${code.toLowerCase()}-${ratio.replace(':', 'x')}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    });
};

const initShowroomSwitcher = () => {
    const showroom = document.getElementById('mayhen_personality_showroom');
    if (!showroom) return;

    const stage = showroom.querySelector('[data-sculpture-3d]');
    const codeEl = document.getElementById('mayhen_showroom_code');
    const titleEl = document.getElementById('mayhen_showroom_title');
    const vnEl = document.getElementById('mayhen_showroom_vn');
    const stackEl = document.getElementById('mayhen_showroom_stack');
    const storyEl = document.getElementById('mayhen_showroom_story');
    const linkEl = document.getElementById('mayhen_showroom_link');
    const buttons = Array.from(showroom.querySelectorAll('.mayhen_personality_pick_btn'));

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            buttons.forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            if (codeEl) codeEl.textContent = btn.dataset.code || '';
            if (titleEl) titleEl.textContent = btn.dataset.artTitle || '';
            if (vnEl) vnEl.textContent = `${btn.dataset.vnName || ''} • ${btn.dataset.group || ''}`;
            if (stackEl) stackEl.textContent = btn.dataset.stack || '';
            if (storyEl) storyEl.textContent = btn.dataset.story || '';
            if (linkEl && btn.dataset.url) {
                linkEl.href = btn.dataset.url;
                linkEl.textContent = `Khám phá hồ sơ ${btn.dataset.code} →`;
            }

            stage?.dispatchEvent(new CustomEvent('personality:switch-sculpture', {
                detail: {
                    sculptureKey: btn.dataset.sculptureKey,
                    primary: btn.dataset.primary,
                    accent: btn.dataset.accent,
                },
            }));
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initAssessmentRunner();
    initRevealOverlay();
    initInteractiveSculptures();
    initShowroomSwitcher();
    initEvidenceToggles();
    initCoachForm();
    initShareCardExport();
});
