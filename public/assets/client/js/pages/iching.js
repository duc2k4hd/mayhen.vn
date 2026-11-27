/**
 * MAYHEN.VN — MÂY HẸN DỊCH TÂM V2 INTERACTIVE ENGINE
 * 100% Pure Vanilla JS ES Module • Zero External Libraries
 */
import { formatSafeAiMarkdown, formatSafeAiInline } from '../common/markdown-safe.js';

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function stripVietnameseTones(str) {
        return String(str ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'D')
            .toLowerCase();
    }

    function playSoftChime(enabled) {
        if (!enabled) return;
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            const ctx = new Ctx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(528, ctx.currentTime);
            gain.gain.setValueAtTime(0.04, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.45);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.46);
        } catch {
            // Bỏ qua nếu trình duyệt chặn audio
        }
    }

    function buildHexagramSvg(code6Bits, movingPositions = [], lineValues = null) {
        const rows = [];
        for (let i = 5; i >= 0; i--) {
            const pos = i + 1;
            const y = (5 - i) * 16 + 4;
            const isYang = code6Bits[i] === '1';
            const isMoving = movingPositions.includes(pos);
            const fill = isMoving ? '#85182A' : '#2A211B';
            const val = Array.isArray(lineValues) ? Number(lineValues[i]) : (isMoving ? (isYang ? 9 : 6) : (isYang ? 7 : 8));

            if (isYang) {
                rows.push(`<rect x="4" y="${y}" width="88" height="9" rx="3" fill="${fill}" />`);
            } else {
                rows.push(`<rect x="4" y="${y}" width="38" height="9" rx="3" fill="${fill}" />`);
                rows.push(`<rect x="54" y="${y}" width="38" height="9" rx="3" fill="${fill}" />`);
            }
            if (isMoving) {
                const sym = val === 9 ? '○' : '×';
                rows.push(`<text x="103" y="${y + 8.5}" font-size="11" font-weight="700" fill="#85182A" text-anchor="middle">${sym}</text>`);
            }
        }
        return `<svg viewBox="0 0 116 102" width="114" height="100" aria-hidden="true">${rows.join('')}</svg>`;
    }

    function bindDepthTabs(container) {
        if (!container) return;
        const tabs = container.querySelectorAll('.mayhen_iching_depth_tab');
        const panels = container.querySelectorAll('.mayhen_iching_depth_panel');
        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const depth = tab.getAttribute('data-depth');
                tabs.forEach((t) => t.classList.toggle('is-active', t === tab));
                panels.forEach((p) => p.classList.toggle('is-active', p.getAttribute('data-depth-panel') === depth));
            });
        });
    }

    function bindInteractiveLineInspector(container, lineDetails) {
        if (!container || !Array.isArray(lineDetails)) return;
        const btns = container.querySelectorAll('.mayhen_iching_line_btn');
        const inspector = container.querySelector('.mayhen_iching_line_inspector');
        if (!inspector || btns.length === 0) return;

        function showLine(pos) {
            const line = lineDetails.find((l) => Number(l.position) === Number(pos)) || lineDetails[0];
            if (!line) return;
            btns.forEach((b) => b.classList.toggle('is-active', Number(b.getAttribute('data-line-pos')) === Number(line.position)));

            const ct = line.classical_text;
            const classicalHtml = ct
                ? `
                    <p style="margin:6px 0 4px;"><strong>Nguyên văn:</strong> ${escapeHtml(ct.original)}</p>
                    <p style="margin:0 0 4px;"><strong>Hán Việt:</strong> <em>${escapeHtml(ct.han_viet)}</em></p>
                    <p style="margin:0 0 4px;"><strong>Nghĩa:</strong> ${escapeHtml(ct.translation)}</p>
                    ${ct.xiaoxiang ? `<p style="margin:0; color:#5C5349;"><strong>Tiểu Tượng:</strong> ${escapeHtml(ct.xiaoxiang)}</p>` : ''}
                  `
                : `<p style="margin:6px 0 0; color:#6A6056;">Nguyên văn Hào Từ của hào này đang trong lộ trình thẩm định cổ thư. Hệ thống không tự sáng tác lời cổ nhân; vui lòng tham khảo Quái Từ, Đại Tượng và phần Luận Giải tổng hợp.</p>`;

            inspector.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px; margin-bottom:6px;">
                    <strong style="color:#85182A;">${escapeHtml(line.label)} (Hào ${line.position}) — ${escapeHtml(line.type_label)}</strong>
                    <span class="mayhen_iching_provenance_badge">${line.classical_verified ? 'Cổ thư đã thẩm định' : 'Đang thẩm định'}</span>
                </div>
                ${classicalHtml}
            `;
        }

        btns.forEach((b) => b.addEventListener('click', () => showLine(b.getAttribute('data-line-pos'))));
        const firstMoving = lineDetails.find((l) => l.is_moving);
        showLine(firstMoving ? firstMoving.position : 1);
    }

    function bindReadingCompanionHandlers(container, endpoints) {
        if (!container || !endpoints) return;

        // 1. Checklist toggle
        const checklistInputs = container.querySelectorAll('.mayhen_iching_check_input');
        checklistInputs.forEach((cb) => {
            cb.addEventListener('change', async () => {
                const li = cb.closest('li');
                if (li) li.classList.toggle('is-done', cb.checked);
                const payload = Array.from(checklistInputs).map((item) => ({
                    text: item.getAttribute('data-step-text') || '',
                    done: Boolean(item.checked),
                }));
                try {
                    await fetch(endpoints.journal, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ checklist: payload }),
                    });
                } catch {
                    // Bỏ qua lỗi mạng tạm
                }
            });
        });

        // 2. Follow-up Q&A
        const followupForm = container.querySelector('[data-iching-followup-form]');
        const followupList = container.querySelector('[data-iching-followup-list]');
        followupForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = followupForm.querySelector('input[name="question"], textarea[name="question"]');
            const btn = followupForm.querySelector('button[type="submit"]');
            const q = (input?.value || '').trim();
            if (q.length < 4) return;

            if (btn) { btn.disabled = true; btn.textContent = 'Đang đối chiếu lá quẻ...'; }
            try {
                const res = await fetch(endpoints.followup, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ question: q }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    alert(data.message || 'Không thể gửi câu hỏi đào sâu lúc này.');
                    return;
                }
                if (followupList && data.followup) {
                    const el = document.createElement('div');
                    el.className = 'mayhen_iching_followup_item';
                    el.innerHTML = `
                        <div class="mayhen_iching_followup_q">Hỏi sâu: ${escapeHtml(data.followup.question)}</div>
                        <div class="mayhen_md_prose">${formatSafeAiMarkdown(data.followup.answer)}</div>
                    `;
                    followupList.appendChild(el);
                }
                if (input) input.value = '';
            } finally {
                if (btn) { btn.disabled = false; btn.textContent = 'Hỏi Sâu Về Lá Quẻ Này'; }
            }
        });

        // 3. Journal save
        const journalForm = container.querySelector('[data-iching-journal-form]');
        const journalList = container.querySelector('[data-iching-journal-list]');
        journalForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(journalForm);
            const btn = journalForm.querySelector('button[type="submit"]');
            const body = {
                milestone_days: Number(fd.get('milestone_days') || 7),
                actions_taken: String(fd.get('actions_taken') || ''),
                progress_note: String(fd.get('progress_note') || ''),
                lessons_learned: String(fd.get('lessons_learned') || ''),
            };
            if (!body.actions_taken.trim() && !body.progress_note.trim() && !body.lessons_learned.trim()) {
                alert('Vui lòng ghi ít nhất một dòng trải nghiệm để lưu Nhật ký.');
                return;
            }
            if (btn) { btn.disabled = true; btn.textContent = 'Đang lưu...'; }
            try {
                const res = await fetch(endpoints.journal, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                if (res.ok && data.success && data.journal && journalList) {
                    const card = document.createElement('div');
                    card.className = 'mayhen_iching_followup_item';
                    card.innerHTML = `
                        <div class="mayhen_iching_followup_q">Mốc ${data.journal.milestone_days} ngày • ${escapeHtml(data.journal.created_at)}</div>
                        ${data.journal.actions_taken ? `<div><strong>Đã làm:</strong> ${escapeHtml(data.journal.actions_taken)}</div>` : ''}
                        ${data.journal.progress_note ? `<div><strong>Chuyển biến:</strong> ${escapeHtml(data.journal.progress_note)}</div>` : ''}
                        ${data.journal.lessons_learned ? `<div><strong>Bài học:</strong> ${escapeHtml(data.journal.lessons_learned)}</div>` : ''}
                    `;
                    journalList.prepend(card);
                    journalForm.reset();
                }
            } finally {
                if (btn) { btn.disabled = false; btn.textContent = 'Lưu Nhật Ký Chiêm Nghiệm'; }
            }
        });

        // 4. Feedback 4 levels & Favorite
        container.querySelectorAll('[data-iching-feedback]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const rating = btn.getAttribute('data-iching-feedback');
                const res = await fetch(endpoints.feedback, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ feedback_rating: rating }),
                });
                if (res.ok) {
                    container.querySelectorAll('[data-iching-feedback]').forEach((b) => b.classList.toggle('is-active', b === btn));
                }
            });
        });

        const favBtn = container.querySelector('[data-iching-favorite-btn]');
        favBtn?.addEventListener('click', async () => {
            const nextState = favBtn.getAttribute('data-favorite') !== '1';
            const res = await fetch(endpoints.feedback, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ is_favorite: nextState }),
            });
            if (res.ok) {
                favBtn.setAttribute('data-favorite', nextState ? '1' : '0');
                favBtn.textContent = nextState ? '★ Đã lưu Yêu thích' : '☆ Đánh dấu Yêu thích';
            }
        });

        // 5. Share / Revoke PII-redacted Card
        const shareBtn = container.querySelector('[data-iching-share-btn]');
        const revokeBtn = container.querySelector('[data-iching-revoke-btn]');
        const shareUrlOut = container.querySelector('[data-iching-share-output]');

        shareBtn?.addEventListener('click', async () => {
            const res = await fetch(endpoints.toggle_share, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ action: 'enable' }),
            });
            const data = await res.json();
            if (res.ok && data.success && data.share_url) {
                navigator.clipboard?.writeText(data.share_url);
                if (shareUrlOut) {
                    shareUrlOut.innerHTML = `Link Thẻ Chia Sẻ (đã ẩn câu hỏi riêng tư): <a href="${escapeHtml(data.share_url)}" target="_blank" rel="noopener">${escapeHtml(data.share_url)}</a> (đã sao chép)`;
                    shareUrlOut.style.display = 'block';
                }
                if (revokeBtn) revokeBtn.style.display = 'inline-flex';
            }
        });

        revokeBtn?.addEventListener('click', async () => {
            const res = await fetch(endpoints.toggle_share, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ action: 'revoke' }),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                if (shareUrlOut) {
                    shareUrlOut.textContent = data.message || 'Đã thu hồi liên kết chia sẻ.';
                    shareUrlOut.style.display = 'block';
                }
                revokeBtn.style.display = 'none';
            }
        });

        // 6. Re-interpret AI on exact same hexagram
        const reinterpretBtn = container.querySelector('[data-iching-reinterpret-btn]');
        reinterpretBtn?.addEventListener('click', async () => {
            reinterpretBtn.disabled = true;
            reinterpretBtn.textContent = 'Đang chạy lại AI trên lá quẻ này...';
            try {
                const res = await fetch(endpoints.reinterpret, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                });
                if (res.ok) {
                    window.location.reload();
                } else {
                    alert('AI tạm chưa phản hồi, vui lòng thử lại sau ít phút.');
                }
            } finally {
                reinterpretBtn.disabled = false;
            }
        });
    }

    // =========================================================================
    // PHẦN A: TRANG CHI TIẾT LÁ QUẺ (/gieo-que/ket-qua/{slug})
    // =========================================================================
    const showApp = document.getElementById('mayhenIchingShowApp');
    if (showApp) {
        bindDepthTabs(showApp);
        const lineDetailsEl = document.getElementById('mayhenIchingShowLinesJson');
        const endpointsEl = document.getElementById('mayhenIchingShowEndpointsJson');
        try {
            if (lineDetailsEl) {
                bindInteractiveLineInspector(showApp, JSON.parse(lineDetailsEl.textContent || '[]'));
            }
            if (endpointsEl) {
                bindReadingCompanionHandlers(showApp, JSON.parse(endpointsEl.textContent || '{}'));
            }
        } catch {
            // Bỏ qua lỗi parse
        }
        return;
    }

    // =========================================================================
    // PHẦN B: TRANG CHÍNH & BÀN GIEO QUẺ (/gieo-que)
    // =========================================================================
    const root = document.getElementById('mayhenIchingApp');
    if (!root) return;

    const castUrl = root.getAttribute('data-cast-url') || '';
    const refineUrl = root.getAttribute('data-refine-url') || '';
    const historyUrl = root.getAttribute('data-history-url') || '';
    const canCastFree = root.getAttribute('data-can-cast') === '1';
    const dailyLimit = Number(root.getAttribute('data-daily-limit') || 5);
    const cooldownLabel = root.getAttribute('data-cooldown-label') || '5 phút/lần';
    let dailyRemaining = Number(root.getAttribute('data-daily-remaining') || 5);
    let cooldownRemaining = Number(root.getAttribute('data-cooldown-seconds') || 0);
    let latestReadingUrl = root.getAttribute('data-latest-reading-url') || '';
    const DRAFT_KEY = 'mayhen_iching_draft_v2';

    const taxonomyEl = document.getElementById('mayhenIchingTaxonomyJson');
    let topicsTaxonomy = {};
    try {
        topicsTaxonomy = JSON.parse(taxonomyEl?.textContent || '{}');
    } catch {
        topicsTaxonomy = {};
    }

    let activeMethod = 'coins_3';
    let activeTopic = 'tong_quan';
    let activeSubTopic = '';
    let rolledLines = [];
    let rolledCoinFaces = [];
    let isRolling = false;
    let soundEnabled = false;
    let skipAnimation = false;
    let currentIdempotencyKey = '';

    const questionInput = document.getElementById('mayhenIchingQuestion');
    const contextInput = document.getElementById('mayhenIchingContext');
    const hideQuestionCb = document.getElementById('mayhenIchingHideQuestion');
    const subtopicWrap = document.getElementById('mayhenIchingSubtopics');
    const refineBox = document.getElementById('mayhenIchingRefineBox');
    const crisisBox = document.getElementById('mayhenIchingCrisisBox');
    const linesStackEl = document.getElementById('mayhenIchingLinesStack');
    const tossBtn = document.getElementById('mayhenIchingTossOneBtn');
    const tossAllBtn = document.getElementById('mayhenIchingTossAllBtn');
    const resetCoinsBtn = document.getElementById('mayhenIchingResetCoinsBtn');
    const submitCastBtn = document.getElementById('mayhenIchingSubmitCastBtn');
    const quotaNoticeEl = document.getElementById('mayhenIchingQuotaNotice');
    const quotaRemainText = document.getElementById('mayhenIchingQuotaRemainText');
    const resultSection = document.getElementById('mayhenIchingResultSection');
    const resultShell = document.getElementById('mayhenIchingResultShell');

    function formatMmSs(totalSec) {
        const s = Math.max(0, Math.floor(totalSec));
        const m = Math.floor(s / 60);
        const rem = s % 60;
        return `${String(m).padStart(2, '0')}:${String(rem).padStart(2, '0')}`;
    }

    let cooldownTimerId = null;
    function syncCastQuotaUi(customMessage = '') {
        if (quotaRemainText) {
            quotaRemainText.textContent = `${Math.max(0, dailyRemaining)}/${dailyLimit}`;
        }
        if (!canCastFree || !submitCastBtn) return;

        if (dailyRemaining <= 0) {
            submitCastBtn.disabled = true;
            submitCastBtn.textContent = `Đã dùng đủ ${dailyLimit}/${dailyLimit} lượt hôm nay`;
            if (quotaNoticeEl) {
                quotaNoticeEl.innerHTML = `
                    🌿 ${escapeHtml(customMessage || `Bạn đã dùng hết ${dailyLimit} lượt gieo quẻ trong ngày hôm nay. Hãy quay lại vào ngày mai hoặc mở Sổ Quẻ để xem lại & Hỏi Sâu về các lá quẻ đã lập.`)}
                    ${historyUrl ? ` <a href="${escapeHtml(historyUrl)}" class="mayhen_iching_status_link">Mở Sổ Quẻ &rarr;</a>` : ''}
                `;
                quotaNoticeEl.style.display = 'block';
            }
            return;
        }

        if (cooldownRemaining > 0) {
            submitCastBtn.disabled = true;
            submitCastBtn.textContent = `⏳ Chờ ${formatMmSs(cooldownRemaining)} để gieo lượt tiếp theo`;
            if (quotaNoticeEl) {
                quotaNoticeEl.innerHTML = `
                    🌿 ${escapeHtml(customMessage || `Theo gói hiện tại, nhịp giãn cách giữa hai lượt gieo là ${cooldownLabel}.`)}
                    (Còn <strong>${formatMmSs(cooldownRemaining)}</strong>)
                    ${latestReadingUrl ? ` • <a href="${escapeHtml(latestReadingUrl)}" class="mayhen_iching_status_link">Xem lá quẻ vừa gieo &rarr;</a>` : ''}
                `;
                quotaNoticeEl.style.display = 'block';
            }
            if (!cooldownTimerId) {
                cooldownTimerId = setInterval(() => {
                    cooldownRemaining = Math.max(0, cooldownRemaining - 1);
                    if (cooldownRemaining <= 0) {
                        clearInterval(cooldownTimerId);
                        cooldownTimerId = null;
                        if (quotaNoticeEl) quotaNoticeEl.style.display = 'none';
                        submitCastBtn.disabled = false;
                        submitCastBtn.textContent = '✦ Lập Quẻ & Luận Giải (Miễn Phí)';
                    } else {
                        syncCastQuotaUi();
                    }
                }, 1000);
            }
            return;
        }

        if (quotaNoticeEl) quotaNoticeEl.style.display = 'none';
        submitCastBtn.disabled = false;
        submitCastBtn.textContent = '✦ Lập Quẻ & Luận Giải (Miễn Phí)';
    }

    syncCastQuotaUi();
    const coinEls = [
        document.getElementById('mayhenIchingCoin1'),
        document.getElementById('mayhenIchingCoin2'),
        document.getElementById('mayhenIchingCoin3'),
    ];
    const coinShadowEls = [
        document.getElementById('mayhenIchingCoinShadow1'),
        document.getElementById('mayhenIchingCoinShadow2'),
        document.getElementById('mayhenIchingCoinShadow3'),
    ];
    const coinLabelEls = [
        document.getElementById('mayhenIchingCoinLabel1'),
        document.getElementById('mayhenIchingCoinLabel2'),
        document.getElementById('mayhenIchingCoinLabel3'),
    ];

    // =========================================================================
    // ĐỒNG XU CỔ 3D EXTRUDED (NGỬA VÀNG HOÀNG KIM / SẤP ĐỒNG CỔ) + VẬT LÝ 60FPS
    // =========================================================================
    function playCoinClink(enabled, delayMs = 0) {
        if (!enabled) return;
        setTimeout(() => {
            try {
                const Ctx = window.AudioContext || window.webkitAudioContext;
                if (!Ctx) return;
                const ctx = new Ctx();
                const now = ctx.currentTime;
                [1480, 2740].forEach((freq, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = i === 0 ? 'triangle' : 'sine';
                    osc.frequency.setValueAtTime(freq + (Math.random() * 60 - 30), now);
                    gain.gain.setValueAtTime(i === 0 ? 0.032 : 0.016, now);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.28);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(now);
                    osc.stop(now + 0.3);
                });
            } catch {
                // Bỏ qua nếu trình duyệt chặn audio
            }
        }, delayMs);
    }

    function mountAntiqueCoin3D(el, uid) {
        if (!el) return;
        const isLg = el.getAttribute('data-coin-size') === 'lg';
        const edgeOffsets = isLg ? [3.4, 2.0, 0.7, -0.7, -2.0, -3.4] : [2.2, 1.1, 0, -1.1, -2.2];

        const frontSvg = `
            <svg viewBox="0 0 120 120" aria-hidden="true">
                <defs>
                    <mask id="m_f_${uid}">
                        <circle cx="60" cy="60" r="58" fill="#ffffff" />
                        <rect x="44" y="44" width="32" height="32" rx="1.5" fill="#000000" />
                    </mask>
                    <radialGradient id="g_f_${uid}" cx="34%" cy="28%" r="72%">
                        <stop offset="0%" stop-color="#FFF2BF" />
                        <stop offset="38%" stop-color="#E7C26E" />
                        <stop offset="76%" stop-color="#BD8C39" />
                        <stop offset="100%" stop-color="#8B5E1A" />
                    </radialGradient>
                    <linearGradient id="rim_f_${uid}" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#FFF7D4" />
                        <stop offset="48%" stop-color="#D4A44C" />
                        <stop offset="100%" stop-color="#7D5214" />
                    </linearGradient>
                </defs>
                <g mask="url(#m_f_${uid})">
                    <circle cx="60" cy="60" r="58" fill="url(#g_f_${uid})" />
                    <circle cx="60" cy="60" r="54.5" fill="none" stroke="url(#rim_f_${uid})" stroke-width="5.5" />
                    <circle cx="60" cy="60" r="49" fill="none" stroke="#6E4711" stroke-width="1.4" opacity="0.7" />
                    <rect x="41.5" y="41.5" width="37" height="37" rx="2" fill="none" stroke="#FFE9A8" stroke-width="2.6" />
                    <rect x="43.5" y="43.5" width="33" height="33" rx="1.5" fill="none" stroke="#6E4711" stroke-width="1.2" />
                    <g fill="#5C380C" font-family="'MayhenV3EditorialSerif', 'Noto Serif SC', Georgia, serif" font-weight="700" font-size="21" text-anchor="middle">
                        <text x="60" y="33.5">易</text>
                        <text x="60" y="102.5">經</text>
                        <text x="94" y="67.5">通</text>
                        <text x="26" y="67.5">寶</text>
                    </g>
                    <g fill="#FFF2C2" opacity="0.35" font-family="'MayhenV3EditorialSerif', 'Noto Serif SC', Georgia, serif" font-weight="700" font-size="21" text-anchor="middle">
                        <text x="59.4" y="32.9">易</text>
                        <text x="59.4" y="101.9">經</text>
                        <text x="93.4" y="66.9">通</text>
                        <text x="25.4" y="66.9">寶</text>
                    </g>
                </g>
            </svg>
        `;

        const backSvg = `
            <svg viewBox="0 0 120 120" aria-hidden="true">
                <defs>
                    <mask id="m_b_${uid}">
                        <circle cx="60" cy="60" r="58" fill="#ffffff" />
                        <rect x="44" y="44" width="32" height="32" rx="1.5" fill="#000000" />
                    </mask>
                    <radialGradient id="g_b_${uid}" cx="36%" cy="30%" r="74%">
                        <stop offset="0%" stop-color="#6F4B25" />
                        <stop offset="55%" stop-color="#482D14" />
                        <stop offset="100%" stop-color="#291709" />
                    </radialGradient>
                    <linearGradient id="rim_b_${uid}" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#DFB86B" />
                        <stop offset="52%" stop-color="#9A6D2A" />
                        <stop offset="100%" stop-color="#4A2E0E" />
                    </linearGradient>
                </defs>
                <g mask="url(#m_b_${uid})">
                    <circle cx="60" cy="60" r="58" fill="url(#g_b_${uid})" />
                    <circle cx="60" cy="60" r="54.5" fill="none" stroke="url(#rim_b_${uid})" stroke-width="5.5" />
                    <circle cx="60" cy="60" r="48.5" fill="none" stroke="#C5984B" stroke-width="1.1" opacity="0.65" />
                    <rect x="41.5" y="41.5" width="37" height="37" rx="2" fill="none" stroke="#C89B4D" stroke-width="2.4" />
                    <rect x="43.5" y="43.5" width="33" height="33" rx="1.5" fill="none" stroke="#1E1006" stroke-width="1.2" />
                    <path d="M24 52 C20 56, 20 64, 24 68 M29 48 C25 55, 25 65, 29 72" fill="none" stroke="#B78940" stroke-width="2" stroke-linecap="round" opacity="0.62" />
                    <path d="M96 52 C100 56, 100 64, 96 68 M91 48 C95 55, 95 65, 91 72" fill="none" stroke="#B78940" stroke-width="2" stroke-linecap="round" opacity="0.62" />
                    <circle cx="60" cy="24" r="2.2" fill="#B78940" opacity="0.55" />
                    <circle cx="60" cy="96" r="2.2" fill="#B78940" opacity="0.55" />
                </g>
            </svg>
        `;

        const edgeSvg = `
            <svg viewBox="0 0 120 120" aria-hidden="true">
                <defs>
                    <mask id="m_e_${uid}">
                        <circle cx="60" cy="60" r="58" fill="#ffffff" />
                        <rect x="44" y="44" width="32" height="32" rx="1.5" fill="#000000" />
                    </mask>
                    <linearGradient id="g_e_${uid}" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#FCE8A6" />
                        <stop offset="45%" stop-color="#C8963E" />
                        <stop offset="100%" stop-color="#6B4410" />
                    </linearGradient>
                </defs>
                <circle cx="60" cy="60" r="58" fill="url(#g_e_${uid})" mask="url(#m_e_${uid})" />
            </svg>
        `;

        const edgesHtml = edgeOffsets
            .map((z) => `<div class="mayhen_iching_coin3d_edge" style="transform:translateZ(${z}px)">${edgeSvg}</div>`)
            .join('');

        el.innerHTML = `
            <div class="mayhen_iching_coin3d_face mayhen_iching_coin3d_face--front">${frontSvg}</div>
            ${edgesHtml}
            <div class="mayhen_iching_coin3d_face mayhen_iching_coin3d_face--back">${backSvg}</div>
        `;
    }

    function setCoinStaticFace(coinEl, shadowEl, isYang, tiltZ = 0) {
        if (!coinEl) return;
        const rotY = isYang ? 0 : 180;
        coinEl.style.transform = `translate3d(0px, 0px, 0px) rotateX(10deg) rotateY(${rotY}deg) rotateZ(${tiltZ}deg)`;
        if (shadowEl) {
            shadowEl.style.transform = 'scale(1)';
            shadowEl.style.opacity = '0.85';
        }
    }

    function animateCoinToss3D(coinEl, shadowEl, targetIsYang, slotIdx, opts = {}) {
        return new Promise((resolve) => {
            if (!coinEl) {
                resolve();
                return;
            }
            const duration = opts.duration ?? 840;
            const peakHeight = (opts.peakHeight ?? 98) + (slotIdx === 1 ? 18 : 0);
            const delay = (opts.stagger ?? 65) * slotIdx;
            const finalTiltZ = slotIdx === 0 ? -5 : (slotIdx === 2 ? 5 : 0);
            const finalRotY = targetIsYang ? 0 : 180;
            // Tổng góc quay mượt: 2 hoặc 3 vòng nguyên + mặt đích
            const spinsX = (opts.spinsX ?? 2) * 360;
            const spinsY = (opts.spinsY ?? 2) * 360 + finalRotY;
            const driftX = slotIdx === 0 ? -8 : (slotIdx === 2 ? 8 : 0);

            if (opts.instant) {
                setCoinStaticFace(coinEl, shadowEl, targetIsYang, finalTiltZ);
                resolve();
                return;
            }

            const startAt = performance.now() + delay;

            function frame(now) {
                if (now < startAt) {
                    requestAnimationFrame(frame);
                    return;
                }
                const rawT = Math.min(1, (now - startAt) / duration);
                // Đường cong mượt: bay lên parabol 0..0.82, nhún nhẹ tiếp đất 0.82..1.0
                let y = 0;
                let x = 0;
                let rotX = 10;
                let rotY = finalRotY;
                let rotZ = finalTiltZ;
                let scale = 1;

                if (rawT < 0.82) {
                    const p = rawT / 0.82;
                    // Ease out cubic cho góc xoay, parabol cho độ cao
                    const easeRot = 1 - Math.pow(1 - p, 2.4);
                    y = -4 * peakHeight * p * (1 - p);
                    x = Math.sin(p * Math.PI) * driftX;
                    scale = 1 + 0.14 * Math.sin(p * Math.PI);
                    rotX = 10 + spinsX * easeRot;
                    rotY = spinsY * easeRot;
                    rotZ = finalTiltZ * p + Math.sin(p * Math.PI * 2) * 14;
                } else {
                    const b = (rawT - 0.82) / 0.18;
                    const bounce = Math.sin(b * Math.PI) * (1 - b) * 10;
                    y = -bounce;
                    rotX = 10 + Math.sin(b * Math.PI) * 9;
                    rotY = finalRotY;
                    rotZ = finalTiltZ;
                    scale = 1 + bounce * 0.002;
                }

                coinEl.style.transform = `translate3d(${x.toFixed(1)}px, ${y.toFixed(1)}px, 0px) scale(${scale.toFixed(3)}) rotateX(${rotX.toFixed(1)}deg) rotateY(${rotY.toFixed(1)}deg) rotateZ(${rotZ.toFixed(1)}deg)`;

                if (shadowEl) {
                    const liftRatio = Math.min(1, Math.abs(y) / Math.max(1, peakHeight));
                    const sScale = 1 - liftRatio * 0.42;
                    const sOp = 0.88 - liftRatio * 0.55;
                    shadowEl.style.transform = `scale(${sScale.toFixed(3)})`;
                    shadowEl.style.opacity = sOp.toFixed(3);
                }

                if (rawT < 1) {
                    requestAnimationFrame(frame);
                } else {
                    setCoinStaticFace(coinEl, shadowEl, targetIsYang, finalTiltZ);
                    resolve();
                }
            }

            requestAnimationFrame(frame);
        });
    }

    // Dựng sẵn 3 đồng xu 3D trên bàn gieo chính
    coinEls.forEach((el, idx) => {
        mountAntiqueCoin3D(el, `inline_${idx}`);
        setCoinStaticFace(el, coinShadowEls[idx], idx !== 1, idx === 0 ? -4 : (idx === 2 ? 4 : 0));
    });

    function generateIdempotencyKey() {
        return 'cast_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 10);
    }

    function saveDraft() {
        try {
            sessionStorage.setItem(DRAFT_KEY, JSON.stringify({
                activeMethod,
                activeTopic,
                activeSubTopic,
                question: questionInput?.value || '',
                rolledLines,
                rolledCoinFaces,
            }));
        } catch {
            // Bỏ qua nếu sessionStorage không khả dụng
        }
    }

    function restoreDraft() {
        try {
            const raw = sessionStorage.getItem(DRAFT_KEY);
            if (!raw) return;
            const d = JSON.parse(raw);
            if (d.activeTopic) activeTopic = d.activeTopic;
            if (d.activeSubTopic) activeSubTopic = d.activeSubTopic;
            if (d.question && questionInput && !questionInput.value) questionInput.value = d.question;
            if (Array.isArray(d.rolledLines) && d.rolledLines.length <= 6) {
                rolledLines = d.rolledLines.filter((n) => [6, 7, 8, 9].includes(Number(n))).map(Number);
            }
            if (Array.isArray(d.rolledCoinFaces)) {
                rolledCoinFaces = d.rolledCoinFaces.slice(0, rolledLines.length);
            }
        } catch {
            // Bỏ qua
        }
    }

    function renderSubTopics() {
        if (!subtopicWrap) return;
        const topicData = topicsTaxonomy?.topics?.[activeTopic];
        const list = Array.isArray(topicData?.sub_topics) ? topicData.sub_topics : [];
        if (list.length === 0) {
            subtopicWrap.innerHTML = '';
            return;
        }
        subtopicWrap.innerHTML = list.map((sub) => `
            <button type="button" class="mayhen_iching_subtopic_chip ${activeSubTopic === sub ? 'is-active' : ''}" data-subtopic="${escapeHtml(sub)}">
                ${escapeHtml(sub)}
            </button>
        `).join('');

        subtopicWrap.querySelectorAll('.mayhen_iching_subtopic_chip').forEach((btn) => {
            btn.addEventListener('click', () => {
                const val = btn.getAttribute('data-subtopic') || '';
                activeSubTopic = activeSubTopic === val ? '' : val;
                renderSubTopics();
                saveDraft();
            });
        });
    }

    function renderLinesStack() {
        if (!linesStackEl) return;
        const labels = ['Hào Sơ (1)', 'Hào Nhị (2)', 'Hào Tam (3)', 'Hào Tứ (4)', 'Hào Ngũ (5)', 'Hào Thượng'];
        let html = '';
        for (let i = 0; i < 6; i++) {
            const val = rolledLines[i];
            if (!val) {
                html += `
                    <div class="mayhen_iching_line_row">
                        <span class="mayhen_iching_line_idx">${labels[i]}</span>
                        <div class="mayhen_iching_line_bar_wrap"><span class="mayhen_iching_bar_placeholder"></span></div>
                        <span class="mayhen_iching_line_tag" style="color:#9A8E80;">Chờ gieo</span>
                    </div>
                `;
            } else {
                const isYang = val === 7 || val === 9;
                const isMoving = val === 6 || val === 9;
                const tag = val === 9 ? '9 • Động ○' : (val === 6 ? '6 • Động ×' : (val === 7 ? '7 • Dương' : '8 • Âm'));
                const barHtml = isYang
                    ? '<span class="mayhen_iching_bar_yang"></span>'
                    : '<span class="mayhen_iching_bar_yin"></span><span class="mayhen_iching_bar_yin"></span>';
                html += `
                    <div class="mayhen_iching_line_row ${isMoving ? 'is-moving' : ''}">
                        <span class="mayhen_iching_line_idx">${labels[i]}</span>
                        <div class="mayhen_iching_line_bar_wrap">${barHtml}</div>
                        <span class="mayhen_iching_line_tag">${tag}</span>
                    </div>
                `;
            }
        }
        linesStackEl.innerHTML = html;

        if (tossBtn) {
            if (rolledLines.length >= 6) {
                tossBtn.disabled = true;
                tossBtn.textContent = '✓ Đã Gieo Đủ 6 Hào';
            } else {
                tossBtn.disabled = false;
                tossBtn.textContent = `🪙 Tung Hào ${rolledLines.length + 1}/6`;
            }
        }
    }

    const coinStagePanel = root.querySelector('.mayhen_iching_stage_panel[data-stage-panel="coins_3"]');
    const altarRightEl = root.querySelector('.mayhen_iching_altar_right');
    let coinStageBackdrop = null;
    let isStageZoomed = false;

    function ensureCoinStageBackdrop() {
        if (!coinStageBackdrop) {
            coinStageBackdrop = document.createElement('div');
            coinStageBackdrop.className = 'mayhen_iching_coin_stage_backdrop';
            coinStageBackdrop.setAttribute('aria-hidden', 'true');
            document.body.appendChild(coinStageBackdrop);
        }
        return coinStageBackdrop;
    }

    function openCoinStageZoom() {
        if (!coinStagePanel || isStageZoomed) return;
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (skipAnimation || prefersReduced) return;

        // Giữ nguyên chiều cao cột phải để trang nền không bị nhảy layout khi box xu nổi lên giữa màn hình
        if (altarRightEl) {
            altarRightEl.style.minHeight = `${altarRightEl.offsetHeight}px`;
        }

        const backdrop = ensureCoinStageBackdrop();
        coinStagePanel.classList.remove('is-tossing-closing');
        coinStagePanel.classList.add('is-tossing-zoom');
        requestAnimationFrame(() => {
            backdrop.classList.add('is-visible');
        });
        isStageZoomed = true;
    }

    function closeCoinStageZoom(delayMs = 450) {
        if (!coinStagePanel || !isStageZoomed) return Promise.resolve();
        return new Promise((resolve) => {
            setTimeout(() => {
                if (!coinStagePanel) {
                    isStageZoomed = false;
                    resolve();
                    return;
                }
                coinStagePanel.classList.add('is-tossing-closing');
                coinStageBackdrop?.classList.remove('is-visible');

                setTimeout(() => {
                    coinStagePanel.classList.remove('is-tossing-zoom', 'is-tossing-closing');
                    if (altarRightEl) {
                        altarRightEl.style.minHeight = '';
                    }
                    isStageZoomed = false;
                    resolve();
                }, 300);
            }, delayMs);
        });
    }

    async function tossOnceAnimated(opts = {}) {
        if (rolledLines.length >= 6 || isRolling) return;
        isRolling = true;

        const manageZoom = opts.manageZoom !== false;
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const instant = skipAnimation || prefersReduced;

        if (manageZoom && !instant) {
            openCoinStageZoom();
            await new Promise((r) => setTimeout(r, 180));
        }

        const faces = [
            Math.random() < 0.5 ? 3 : 2,
            Math.random() < 0.5 ? 3 : 2,
            Math.random() < 0.5 ? 3 : 2,
        ];
        playCoinClink(soundEnabled, 180);

        await Promise.all(
            coinEls.map((el, idx) => animateCoinToss3D(el, coinShadowEls[idx], faces[idx] === 3, idx, {
                duration: 720,
                peakHeight: isStageZoomed ? 86 : 54,
                stagger: 55,
                instant,
            }))
        );

        coinLabelEls.forEach((lbl, idx) => {
            if (lbl) lbl.textContent = faces[idx] === 3 ? 'Ngửa (3)' : 'Sấp (2)';
        });

        const sum = faces[0] + faces[1] + faces[2];
        rolledLines.push(sum);
        rolledCoinFaces.push(faces.map((f) => (f === 3 ? 'Ngửa (3)' : 'Sấp (2)')));
        currentIdempotencyKey = generateIdempotencyKey();
        renderLinesStack();
        saveDraft();

        if (manageZoom && isStageZoomed) {
            await closeCoinStageZoom(460);
        }

        isRolling = false;
    }

    // Chuyển tab 4 nhóm phương thức
    document.querySelectorAll('.mayhen_iching_method_tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            activeMethod = tab.getAttribute('data-method') || 'coins_3';
            document.querySelectorAll('.mayhen_iching_method_tab').forEach((t) => t.classList.toggle('is-active', t === tab));
            document.querySelectorAll('.mayhen_iching_stage_panel').forEach((p) => {
                p.classList.toggle('is-active', p.getAttribute('data-stage-panel') === activeMethod);
            });
            currentIdempotencyKey = generateIdempotencyKey();
            saveDraft();
        });
    });

    // Chuyển chủ đề & tình huống con
    document.querySelectorAll('.mayhen_iching_topic_chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            activeTopic = chip.getAttribute('data-topic') || 'tong_quan';
            activeSubTopic = '';
            document.querySelectorAll('.mayhen_iching_topic_chip').forEach((c) => c.classList.toggle('is-active', c === chip));
            renderSubTopics();
            saveDraft();
        });
    });

    // Gợi ý câu hỏi nhanh
    document.querySelectorAll('.mayhen_iching_quick_chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            if (questionInput) {
                questionInput.value = chip.getAttribute('data-sample-q') || '';
                questionInput.focus();
                currentIdempotencyKey = generateIdempotencyKey();
                saveDraft();
            }
        });
    });

    // Gợi ý làm rõ câu hỏi mở (chủ động, không ép)
    let refineTimer = null;
    async function checkQuestionRefinement() {
        const q = (questionInput?.value || '').trim();
        if (!refineUrl || q.length < 6) {
            refineBox?.classList.remove('is-visible');
            crisisBox?.classList.remove('is-visible');
            return;
        }
        try {
            const res = await fetch(refineUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ question: q }),
            });
            if (!res.ok) return;
            const data = await res.json();

            if (data.safety && !data.safety.allow_cast && crisisBox) {
                refineBox?.classList.remove('is-visible');
                const resList = (data.safety.resources || []).map((r) => `<li><strong>${escapeHtml(r.label)}:</strong> ${escapeHtml(r.detail)}</li>`).join('');
                crisisBox.innerHTML = `
                    <strong>${escapeHtml(data.safety.title || 'Bạn không cần đối diện chuyện này một mình')}</strong>
                    <p style="margin:6px 0 0;">${escapeHtml(data.safety.notice)}</p>
                    ${resList ? `<ul>${resList}</ul>` : ''}
                `;
                crisisBox.classList.add('is-visible');
                return;
            }
            crisisBox?.classList.remove('is-visible');

            if (data.refinement?.has_suggestion && data.refinement.suggested && refineBox) {
                const suggested = data.refinement.suggested;
                refineBox.innerHTML = `
                    <div><strong>Gợi ý giúp câu hỏi sâu sắc hơn:</strong> ${escapeHtml(data.refinement.reason || '')}</div>
                    <div style="margin-top:4px; color:#85182A;"><em>“${escapeHtml(suggested)}”</em></div>
                    <div class="mayhen_iching_refine_actions">
                        <button type="button" class="mayhen_iching_pill_btn is-active" id="mayhenIchingApplyRefineBtn">Dùng câu hỏi gợi ý</button>
                        <button type="button" class="mayhen_iching_pill_btn" id="mayhenIchingDismissRefineBtn">Giữ nguyên câu của tôi</button>
                    </div>
                `;
                refineBox.classList.add('is-visible');
                refineBox.querySelector('#mayhenIchingApplyRefineBtn')?.addEventListener('click', () => {
                    if (questionInput) {
                        questionInput.value = suggested;
                        currentIdempotencyKey = generateIdempotencyKey();
                        saveDraft();
                    }
                    refineBox.classList.remove('is-visible');
                });
                refineBox.querySelector('#mayhenIchingDismissRefineBtn')?.addEventListener('click', () => {
                    refineBox.classList.remove('is-visible');
                });
            } else {
                refineBox?.classList.remove('is-visible');
            }
        } catch {
            // Bỏ qua
        }
    }

    questionInput?.addEventListener('input', () => {
        currentIdempotencyKey = generateIdempotencyKey();
        saveDraft();
        clearTimeout(refineTimer);
        refineTimer = setTimeout(checkQuestionRefinement, 650);
    });

    // Tĩnh tâm 10 giây (tùy chọn), âm thanh (tắt mặc định) & bỏ qua hoạt ảnh
    const mindfulBtn = document.getElementById('mayhenIchingMindfulBtn');
    let mindfulInterval = null;
    mindfulBtn?.addEventListener('click', () => {
        if (mindfulInterval) {
            clearInterval(mindfulInterval);
            mindfulInterval = null;
            mindfulBtn.classList.remove('is-active');
            mindfulBtn.textContent = '🌿 Tĩnh tâm 10 giây';
            return;
        }
        let sec = 10;
        mindfulBtn.classList.add('is-active');
        mindfulBtn.textContent = `Hít thở sâu... ${sec}s`;
        mindfulInterval = setInterval(() => {
            sec--;
            if (sec <= 0) {
                clearInterval(mindfulInterval);
                mindfulInterval = null;
                mindfulBtn.classList.remove('is-active');
                mindfulBtn.textContent = '✓ Tâm đã tĩnh — Sẵn sàng';
                playSoftChime(soundEnabled);
            } else {
                mindfulBtn.textContent = `Hít thở sâu... ${sec}s`;
            }
        }, 1000);
    });

    const soundBtn = document.getElementById('mayhenIchingSoundBtn');
    soundBtn?.addEventListener('click', () => {
        soundEnabled = !soundEnabled;
        soundBtn.classList.toggle('is-active', soundEnabled);
        soundBtn.textContent = soundEnabled ? '🔔 Âm thanh: Bật' : '🔕 Âm thanh: Tắt';
        if (soundEnabled) playSoftChime(true);
    });

    const skipAnimBtn = document.getElementById('mayhenIchingSkipAnimBtn');
    skipAnimBtn?.addEventListener('click', () => {
        skipAnimation = !skipAnimation;
        skipAnimBtn.classList.toggle('is-active', skipAnimation);
        skipAnimBtn.textContent = skipAnimation ? '⚡ Gieo nhanh: Bật' : '⚡ Bỏ qua hiệu ứng';
    });

    // Nút tung đồng xu
    tossBtn?.addEventListener('click', () => tossOnceAnimated({ manageZoom: true }));
    tossAllBtn?.addEventListener('click', async () => {
        if (isRolling) return;
        if (rolledLines.length >= 6) {
            rolledLines = [];
            rolledCoinFaces = [];
        }
        renderLinesStack();

        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const instant = skipAnimation || prefersReduced;
        if (!instant) {
            openCoinStageZoom();
            await new Promise((r) => setTimeout(r, 220));
        }

        while (rolledLines.length < 6) {
            await tossOnceAnimated({ manageZoom: false });
        }

        if (isStageZoomed) {
            await closeCoinStageZoom(650);
        }
    });
    resetCoinsBtn?.addEventListener('click', () => {
        if (isRolling) return;
        rolledLines = [];
        rolledCoinFaces = [];
        currentIdempotencyKey = generateIdempotencyKey();
        renderLinesStack();
        saveDraft();
    });

    // Mai Hoa sub-mode
    const maiHoaModeSelect = document.getElementById('mayhenIchingMaiHoaMode');
    const maiHoaNumsWrap = document.getElementById('mayhenIchingMaiHoaNums');
    maiHoaModeSelect?.addEventListener('change', () => {
        if (maiHoaNumsWrap) {
            maiHoaNumsWrap.style.display = maiHoaModeSelect.value === 'mai_hoa_numbers' ? 'grid' : 'none';
        }
        currentIdempotencyKey = generateIdempotencyKey();
    });

    // RENDER KẾT QUẢ 10 THÀNH PHẦN + 3 MỨC ĐỘ ĐỌC
    function renderReadingResult(payload) {
        if (!resultSection || !resultShell) return;
        const { analysis, interpretation, question, topic_label, sub_topic, result_url, duplicate_hint, checklist, endpoints } = payload;
        const primary = analysis.primary;
        const mutual = analysis.mutual;
        const changed = analysis.changed;
        const tiYong = analysis.ti_yong || {};
        const chuHy = analysis.chu_hy || {};
        const guidance = analysis.guidance_state || { label: 'Cân bằng', tone: 'balanced', description: '' };
        const moving = analysis.moving_lines || [];
        const lineDetails = analysis.line_details || [];

        const aiTag = interpretation.ai_powered
            ? `<span style="padding:4px 10px; border-radius:999px; background:#1E4D3B; color:#FAF5EB; font-size:11px; font-weight:700;">✦ Luận Giải AI Cá Nhân Hóa</span>`
            : `<span style="padding:4px 10px; border-radius:999px; background:#5E4315; color:#FAF5EB; font-size:11px; font-weight:700;">✦ Luận Giải Chuẩn Kho Tri Thức</span>`;

        const changedCardHtml = changed
            ? `
                <div class="mayhen_iching_hex_card">
                    <span class="mayhen_iching_hex_role">3. Quẻ Biến (Hướng Chuyển Hóa)</span>
                    <div class="mayhen_iching_hex_svg_wrap">${buildHexagramSvg(changed.code, [])}</div>
                    <h3>Quẻ #${changed.no}: ${escapeHtml(changed.name)} (${escapeHtml(changed.hanzi)})</h3>
                    <span class="mayhen_iching_level_pill">${escapeHtml(changed.guidance_state_ref || changed.level)}</span>
                    <p style="font-size:12.5px; color:#5C5349; margin:0;">${escapeHtml(changed.meaning_short)}</p>
                </div>
            `
            : `
                <div class="mayhen_iching_hex_card">
                    <span class="mayhen_iching_hex_role">3. Quẻ Biến</span>
                    <div style="padding:26px 10px; color:#7A6E62; font-size:13px;">
                        <strong>Quẻ Tĩnh (0 Hào Động)</strong><br>
                        Lấy Quái Từ và Đại Tượng Quẻ Chủ làm trục luận chính.
                    </div>
                </div>
            `;

        const stepsSource = Array.isArray(checklist) && checklist.length > 0
            ? checklist
            : (interpretation.action_steps || []).map((s) => ({ text: s, done: false }));

        const checklistHtml = stepsSource.map((item) => `
            <li class="${item.done ? 'is-done' : ''}">
                <input type="checkbox" class="mayhen_iching_check_input" data-step-text="${escapeHtml(item.text)}" ${item.done ? 'checked' : ''} ${endpoints ? '' : 'disabled'}>
                <span>${escapeHtml(item.text)}</span>
            </li>
        `).join('');

        const cautionNotes = Array.isArray(interpretation.caution_notes) && interpretation.caution_notes.length > 0
            ? interpretation.caution_notes
            : (interpretation.caution_note ? [interpretation.caution_note] : []);

        const plan = interpretation.time_horizon_plan || {};
        const trace = analysis.calculation_trace || {};
        const traceStepsHtml = Array.isArray(trace.steps)
            ? `<ul style="margin:6px 0 0; padding-left:18px;">${trace.steps.map((s) => `<li>${escapeHtml(s)}</li>`).join('')}</ul>`
            : (Array.isArray(trace.coin_throws)
                ? `<div style="margin-top:6px; display:flex; flex-wrap:wrap; gap:6px;">${trace.coin_throws.map((t) => `<span style="padding:3px 9px; border-radius:8px; background:#FFFFFF; border:1px solid #E2D5C0; font-size:12px;">Hào ${t.position}: ${escapeHtml((t.faces || []).join(' + '))} = <strong>${t.sum}</strong></span>`).join('')}</div>`
                : '');

        const selectedTextsHtml = (chuHy.selected_texts || []).map((st) => `
            <div style="padding:10px 12px; border-radius:10px; background:#FFFFFF; border:1px solid #E5D9C6; margin-top:8px;">
                <strong>${escapeHtml(st.target)}</strong> — <em>${escapeHtml(st.role)}</em>
                <span class="mayhen_iching_provenance_badge">${st.verified ? 'Cổ thư đã thẩm định' : 'Lược dịch Mây Hẹn'}</span>
                ${st.original ? `<div style="margin-top:4px;">${escapeHtml(st.original)} (${escapeHtml(st.han_viet || '')})</div>` : ''}
                <div style="margin-top:4px; color:#3D352E;">${escapeHtml(st.translation || st.note || '')}</div>
            </div>
        `).join('');

        const lineButtonsHtml = lineDetails.map((l) => `
            <button type="button" class="mayhen_iching_line_btn ${l.is_moving ? 'is-moving' : ''}" data-line-pos="${l.position}">
                <span><strong>${escapeHtml(l.label)}</strong> (Hào ${l.position})</span>
                <span>${escapeHtml(l.type_label)}</span>
            </button>
        `).join('');

        const ecoHtml = (interpretation.ecosystem_suggestions || []).map((eco) => `
            <div class="mayhen_iching_ecosystem_card">
                <h4>${escapeHtml(eco.title)}</h4>
                <p>${escapeHtml(eco.description)}</p>
                <a href="${escapeHtml(eco.url)}" class="mayhen_iching_status_link">${escapeHtml(eco.cta)} &rarr;</a>
            </div>
        `).join('');

        const companionSectionHtml = endpoints ? `
            <div class="mayhen_iching_companion_grid">
                <div class="mayhen_iching_companion_card">
                    <h3>Hỏi Sâu Về Lá Quẻ Này</h3>
                    <p style="font-size:12.5px; color:#5C5349; margin:0 0 12px;">Đặt thêm câu hỏi đào sâu bám sát đúng lá quẻ #${primary.no} ${escapeHtml(primary.name)} thay vì gieo lặp lại.</p>
                    <div class="mayhen_iching_followup_list" data-iching-followup-list></div>
                    <form data-iching-followup-form>
                        <textarea name="question" class="mayhen_iching_textarea" rows="2" maxlength="320" placeholder="VD: Trong 3 hành động trên, tôi nên bắt đầu từ bước nào trước?"></textarea>
                        <button type="submit" class="mayhen_iching_btn_gold mayhen_iching_btn_sm" style="margin-top:8px;">Hỏi Sâu Về Lá Quẻ Này</button>
                    </form>
                </div>
                <div class="mayhen_iching_companion_card">
                    <h3>Nhật Ký Nhìn Lại (7 / 30 / 90 ngày)</h3>
                    <p style="font-size:12.5px; color:#5C5349; margin:0 0 12px;">Ghi lại hành động bạn đã làm và chuyển biến thực tế để biến lá quẻ thành bài học sống.</p>
                    <div class="mayhen_iching_followup_list" data-iching-journal-list></div>
                    <form data-iching-journal-form>
                        <select name="milestone_days" class="mayhen_iching_select" style="margin-bottom:8px; padding:8px 10px; font-size:13px;">
                            <option value="7">Mốc nhìn lại sau 7 ngày</option>
                            <option value="30">Mốc nhìn lại sau 30 ngày</option>
                            <option value="90">Mốc nhìn lại sau 90 ngày</option>
                            <option value="0">Ghi chú ngay hôm nay</option>
                        </select>
                        <input type="text" name="actions_taken" class="mayhen_iching_input" style="margin-bottom:8px; font-size:13px;" placeholder="Bạn đã làm điều gì cụ thể?">
                        <input type="text" name="progress_note" class="mayhen_iching_input" style="margin-bottom:8px; font-size:13px;" placeholder="Điều gì đã chuyển biến rõ hơn?">
                        <button type="submit" class="mayhen_iching_btn_outline mayhen_iching_btn_sm">Lưu Nhật Ký Chiêm Nghiệm</button>
                    </form>
                </div>
            </div>

            <div class="mayhen_iching_feedback_bar">
                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:7px;">
                    <span style="font-size:12.5px; font-weight:700; color:#4A4038;">Bài luận này có hữu ích với bạn?</span>
                    <button type="button" class="mayhen_iching_pill_btn" data-iching-feedback="very_helpful">Rất hữu ích</button>
                    <button type="button" class="mayhen_iching_pill_btn" data-iching-feedback="helpful">Hữu ích</button>
                    <button type="button" class="mayhen_iching_pill_btn" data-iching-feedback="neutral">Bình thường</button>
                    <button type="button" class="mayhen_iching_pill_btn" data-iching-feedback="not_relevant">Chưa sát</button>
                </div>
                <div class="mayhen_iching_btn_row">
                    <button type="button" class="mayhen_iching_pill_btn" data-iching-favorite-btn data-favorite="0">☆ Đánh dấu Yêu thích</button>
                    <button type="button" class="mayhen_iching_pill_btn" data-iching-share-btn>🔗 Tạo Thẻ Chia Sẻ (Ẩn Câu Hỏi)</button>
                    <button type="button" class="mayhen_iching_pill_btn" data-iching-revoke-btn style="display:none; color:#B91C1C;">Thu hồi link chia sẻ</button>
                    ${!interpretation.ai_powered ? `<button type="button" class="mayhen_iching_pill_btn is-active" data-iching-reinterpret-btn>✦ Thử Luận Giải Lại Bằng AI</button>` : ''}
                </div>
            </div>
            <div data-iching-share-output style="display:none; margin-top:10px; font-size:12.5px; color:#1E4D3B; padding:10px 14px; background:#F1F7F4; border-radius:10px;"></div>
        ` : '';

        resultShell.innerHTML = `
            ${duplicate_hint ? `<div class="mayhen_iching_soft_notice">🌿 <strong>Nhắc nhẹ:</strong> ${escapeHtml(duplicate_hint)}</div>` : ''}

            <div class="mayhen_iching_result_banner">
                <div style="flex:1; min-width:260px;">
                    <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-bottom:6px;">
                        ${aiTag}
                        <span style="font-size:12px; color:#6A635B;">
                            ${escapeHtml(analysis.method_label)} • Chủ đề: <strong>${escapeHtml(topic_label || 'Tổng Quan')}</strong>${sub_topic ? ` (${escapeHtml(sub_topic)})` : ''} • ${escapeHtml(analysis.cast_at || '')}
                        </span>
                    </div>
                    <h2 class="mayhen_iching_result_headline">${escapeHtml(interpretation.headline)}</h2>
                    <p style="margin:0; font-size:14px; color:#4A423A;"><strong>Câu hỏi khởi tâm:</strong> “${escapeHtml(question)}”</p>
                </div>
                <div class="mayhen_iching_guidance_card tone-${escapeHtml(guidance.tone || 'balanced')}">
                    <span class="mayhen_iching_guidance_kicker">Trạng thái hướng dẫn</span>
                    <strong class="mayhen_iching_guidance_label">${escapeHtml(guidance.label)}</strong>
                    <span class="mayhen_iching_guidance_desc">${escapeHtml(guidance.description)}</span>
                </div>
            </div>

            <div class="mayhen_iching_depth_tabs" role="tablist">
                <button type="button" class="mayhen_iching_depth_tab is-active" data-depth="full">Luận giải đầy đủ</button>
                <button type="button" class="mayhen_iching_depth_tab" data-depth="quick">Đọc nhanh (30 giây)</button>
                <button type="button" class="mayhen_iching_depth_tab" data-depth="deep">Nghiên cứu &amp; Minh bạch phép tính</button>
            </div>

            <!-- MỨC 1: ĐỌC NHANH -->
            <div class="mayhen_iching_depth_panel" data-depth-panel="quick">
                <div class="mayhen_iching_direct_box">
                    <h3>Tóm tắt cốt lõi</h3>
                    <div class="mayhen_md_prose">${formatSafeAiMarkdown(interpretation.plain_summary || interpretation.direct_answer)}</div>
                </div>
                <div class="mayhen_iching_actions_box">
                    <h4>✦ 3 Hành Động Gợi Ý Thực Tế</h4>
                    <ul class="mayhen_iching_checklist">${checklistHtml}</ul>
                </div>
                <div class="mayhen_iching_reflection_box">
                    <strong>Câu hỏi tự quán chiếu dành cho bạn</strong>
                    <p>“${formatSafeAiInline(interpretation.reflection_question || '')}”</p>
                </div>
            </div>

            <!-- MỨC 2: LUẬN GIẢI ĐẦY ĐỦ (MẶC ĐỊNH) -->
            <div class="mayhen_iching_depth_panel is-active" data-depth-panel="full">
                <div class="mayhen_iching_direct_box">
                    <h3>Trả lời trực tiếp câu hỏi của bạn</h3>
                    <div class="mayhen_md_prose">${formatSafeAiMarkdown(interpretation.direct_answer || interpretation.plain_summary)}</div>
                </div>

                <div class="mayhen_iching_triad_grid">
                    <div class="mayhen_iching_hex_card is-primary">
                        <span class="mayhen_iching_hex_role">1. Quẻ Chủ (Hiện Tại)</span>
                        <div class="mayhen_iching_hex_svg_wrap">${buildHexagramSvg(primary.code, moving, analysis.lines)}</div>
                        <h3>Quẻ #${primary.no}: ${escapeHtml(primary.name)} (${escapeHtml(primary.hanzi)})</h3>
                        <span class="mayhen_iching_level_pill">${escapeHtml(primary.guidance_state_ref || primary.level)}</span>
                        <p style="font-size:12px; color:#6A635B; margin:0 0 6px;">${escapeHtml(primary.trigram_pair_label)}</p>
                        <p style="font-size:12.5px; color:#3F3730; margin:0;"><em>“${escapeHtml(primary.judgment)}”</em></p>
                    </div>

                    <div class="mayhen_iching_hex_card">
                        <span class="mayhen_iching_hex_role">2. Quẻ Hỗ (${analysis.mutual_meta?.is_applicable_traditionally ? 'Nội Tình Mai Hoa' : 'Tham Khảo Nội Tình'})</span>
                        <div class="mayhen_iching_hex_svg_wrap">${buildHexagramSvg(mutual.code, [])}</div>
                        <h3>Quẻ #${mutual.no}: ${escapeHtml(mutual.name)} (${escapeHtml(mutual.hanzi)})</h3>
                        <span class="mayhen_iching_level_pill">${escapeHtml(mutual.guidance_state_ref || mutual.level)}</span>
                        <p style="font-size:12.5px; color:#5C5349; margin:0;">${escapeHtml(mutual.meaning_short)}</p>
                    </div>

                    ${changedCardHtml}
                </div>

                <div class="mayhen_iching_layers_grid">
                    <div class="mayhen_iching_layer_card">
                        <span class="mayhen_iching_layer_badge">Tầng 1 • Tượng Quẻ ${tiYong.applicable ? '& Thể – Dụng' : '& Quái Từ'}</span>
                        <h4>Bản Chất Tình Thế Hiện Tại</h4>
                        <div class="mayhen_md_prose">${formatSafeAiMarkdown(interpretation.layer_1_image)}</div>
                    </div>
                    <div class="mayhen_iching_layer_card">
                        <span class="mayhen_iching_layer_badge">Tầng 2 • Hào Động (${moving.length > 0 ? 'Hào ' + moving.join(', ') : 'Quẻ Tĩnh'})</span>
                        <h4>Điểm Chuyển Biến Theo Chu Hy</h4>
                        <div class="mayhen_md_prose">${formatSafeAiMarkdown(interpretation.layer_2_moving_lines)}</div>
                    </div>
                    <div class="mayhen_iching_layer_card">
                        <span class="mayhen_iching_layer_badge">Tầng 3 • Quẻ Hỗ &amp; Quẻ Biến</span>
                        <h4>Tiến Trình Nội Tại &amp; Chuyển Hóa</h4>
                        <div class="mayhen_md_prose">${formatSafeAiMarkdown(interpretation.layer_3_evolution)}</div>
                    </div>
                    <div class="mayhen_iching_layer_card">
                        <span class="mayhen_iching_layer_badge">Tầng 4 • Nhịp Điệu &amp; Hành Động</span>
                        <h4>Cách Ứng Xử Thuận Thời</h4>
                        <div class="mayhen_md_prose">${formatSafeAiMarkdown(interpretation.layer_4_timing_actions)}</div>
                    </div>
                </div>

                ${plan.today ? `
                    <div class="mayhen_iching_horizon_grid">
                        <div class="mayhen_iching_horizon_card"><strong>Hôm nay</strong><p>${escapeHtml(plan.today)}</p></div>
                        <div class="mayhen_iching_horizon_card"><strong>7 ngày tới</strong><p>${escapeHtml(plan.next_7_days || '')}</p></div>
                        <div class="mayhen_iching_horizon_card"><strong>Nhìn lại sau 30 ngày</strong><p>${escapeHtml(plan.review_30_days || '')}</p></div>
                    </div>
                ` : ''}

                <div class="mayhen_iching_actions_box">
                    <h4>✦ 3 Hành Động Gợi Ý Thực Tế (Tick để lưu tiến độ)</h4>
                    <ul class="mayhen_iching_checklist">${checklistHtml}</ul>
                </div>

                ${cautionNotes.length > 0 ? `
                    <div class="mayhen_iching_caution_box">
                        <strong>Điều cần lưu ý:</strong>
                        <ul>${cautionNotes.map((n) => `<li>${escapeHtml(n)}</li>`).join('')}</ul>
                    </div>
                ` : ''}

                ${interpretation.reflection_question ? `
                    <div class="mayhen_iching_reflection_box">
                        <strong>Câu hỏi tự quán chiếu</strong>
                        <p>“${escapeHtml(interpretation.reflection_question)}”</p>
                    </div>
                ` : ''}
            </div>

            <!-- MỨC 3: NGHIÊN CỨU CHUYÊN SÂU & MINH BẠCH PHÉP TÍNH -->
            <div class="mayhen_iching_depth_panel" data-depth-panel="deep">
                <div class="mayhen_iching_interactive_lines">
                    <h4 style="margin:0 0 10px; font-size:15px;">Bấm vào từng hào của Quẻ Chủ #${primary.no} ${escapeHtml(primary.name)} để tra cứu Hào Từ</h4>
                    <div class="mayhen_iching_interactive_lines_grid">
                        <div class="mayhen_iching_line_buttons">${lineButtonsHtml}</div>
                        <div class="mayhen_iching_line_inspector"></div>
                    </div>
                </div>

                <div class="mayhen_iching_trace_box">
                    <h4>Minh bạch thuật toán &amp; Quy tắc lập quẻ (${escapeHtml(analysis.rule_version || '')} • KB ${escapeHtml(analysis.kb_version || '')})</h4>
                    <div><strong>Phương pháp:</strong> ${escapeHtml(trace.school || analysis.method_label)}</div>
                    <div><strong>Quy tắc áp dụng:</strong> ${escapeHtml(trace.rule_note || '')}</div>
                    ${trace.lunar ? `<div><strong>Âm lịch Việt Nam (UTC+7):</strong> ${escapeHtml(trace.lunar.lunar_date_label)} • Giờ ${escapeHtml(trace.lunar.hour_branch_name)}</div>` : ''}
                    ${traceStepsHtml}
                    <div style="margin-top:8px;"><strong>Quẻ Hỗ:</strong> ${escapeHtml(analysis.mutual_meta?.note || '')}</div>
                    <div style="margin-top:6px;"><strong>Quy tắc Chu Hy:</strong> ${escapeHtml(chuHy.summary_text || analysis.moving_rule)}</div>
                    ${selectedTextsHtml}
                    <div style="margin-top:10px;">
                        <strong>Trường phái Thể – Dụng:</strong>
                        ${tiYong.applicable
                            ? `${escapeHtml(tiYong.ti_position)} (${escapeHtml(tiYong.ti_trigram?.name)} – ${escapeHtml(tiYong.ti_trigram?.element)}) vs ${escapeHtml(tiYong.yong_position)} (${escapeHtml(tiYong.yong_trigram?.name)} – ${escapeHtml(tiYong.yong_trigram?.element)}) → <strong>${escapeHtml(tiYong.relation)}</strong>. ${escapeHtml(tiYong.mutual_note || '')}`
                            : escapeHtml(tiYong.school_note || 'Không áp dụng Thể – Dụng cho phép gieo 3 đồng xu.')}
                    </div>
                </div>
            </div>

            ${companionSectionHtml}

            ${ecoHtml ? `<div class="mayhen_iching_ecosystem_grid">${ecoHtml}</div>` : ''}

            <div class="mayhen_iching_closing_note">
                “${escapeHtml(interpretation.closing_note || 'Một quẻ Dịch không quyết định tương lai của bạn, nhưng có thể giúp bạn nhìn lại hiện tại và cân nhắc những lựa chọn phía trước.')}”
            </div>

            ${result_url ? `
                <div class="mayhen_iching_btn_row" style="margin-top:16px; justify-content:flex-end;">
                    <a href="${escapeHtml(result_url)}" class="mayhen_iching_btn_gold mayhen_iching_btn_sm">Mở Trang Lá Quẻ Riêng Tư &rarr;</a>
                </div>
            ` : ''}
        `;

        bindDepthTabs(resultShell);
        bindInteractiveLineInspector(resultShell, lineDetails);
        bindReadingCompanionHandlers(resultShell, endpoints);

        resultShell.classList.remove('is-entering');
        void resultShell.offsetWidth;
        resultShell.classList.add('is-entering');
        resultSection.style.display = 'block';
        resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // =========================================================================
    // SÂN KHẤU NGHI THỨC GIEO XU 3D & CHUYỂN CẢNH ỨNG QUẺ (00:00 -> 00:21)
    // =========================================================================
    const ritualOverlay = document.getElementById('mayhenIchingRitualOverlay');
    const ritualCanvas = document.getElementById('mayhenIchingRitualCanvas');
    const ritualHeader = document.getElementById('mayhenIchingRitualHeader');
    const ritualQuestionEl = document.getElementById('mayhenIchingRitualQuestion');
    const ritualPhaseEl = document.getElementById('mayhenIchingRitualPhase');
    const ritualSubEl = document.getElementById('mayhenIchingRitualSub');
    const ritualStage = document.getElementById('mayhenIchingRitualStage');
    const ritualLadderEl = document.getElementById('mayhenIchingRitualLadder');
    const ritualRevealEl = document.getElementById('mayhenIchingRitualReveal');
    const ritualProceedBtn = document.getElementById('mayhenIchingRitualProceedBtn');
    const ritualProceedText = document.getElementById('mayhenIchingRitualProceedText');
    const ritualStatusNote = document.getElementById('mayhenIchingRitualStatusNote');
    const ritualSkipBtn = document.getElementById('mayhenIchingRitualSkipBtn');
    const ritualSoundBtn = document.getElementById('mayhenIchingRitualSoundBtn');

    const ritualCoinEls = [
        document.getElementById('mayhenIchingRitualCoin1'),
        document.getElementById('mayhenIchingRitualCoin2'),
        document.getElementById('mayhenIchingRitualCoin3'),
    ];
    const ritualShadowEls = [
        document.getElementById('mayhenIchingRitualShadow1'),
        document.getElementById('mayhenIchingRitualShadow2'),
        document.getElementById('mayhenIchingRitualShadow3'),
    ];
    const ritualTagEls = [
        document.getElementById('mayhenIchingRitualTag1'),
        document.getElementById('mayhenIchingRitualTag2'),
        document.getElementById('mayhenIchingRitualTag3'),
    ];

    ritualCoinEls.forEach((el, idx) => mountAntiqueCoin3D(el, `ritual_${idx}`));

    let goldDustRaf = 0;
    function startGoldDustCanvas() {
        if (!ritualCanvas) return;
        const ctx = ritualCanvas.getContext('2d');
        if (!ctx) return;
        let w = (ritualCanvas.width = window.innerWidth);
        let h = (ritualCanvas.height = window.innerHeight);

        const particles = Array.from({ length: 42 }, () => ({
            x: Math.random() * w,
            y: Math.random() * h,
            r: 0.7 + Math.random() * 1.9,
            vy: -(0.12 + Math.random() * 0.28),
            vx: (Math.random() - 0.5) * 0.16,
            alpha: 0.18 + Math.random() * 0.45,
            phase: Math.random() * Math.PI * 2,
        }));

        function tick() {
            ctx.clearRect(0, 0, w, h);
            particles.forEach((p) => {
                p.x += p.vx;
                p.y += p.vy;
                p.phase += 0.015;
                if (p.y < -10) { p.y = h + 10; p.x = Math.random() * w; }
                if (p.x < -10) p.x = w + 10;
                if (p.x > w + 10) p.x = -10;
                const a = p.alpha * (0.65 + 0.35 * Math.sin(p.phase));
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(235, 198, 126, ${a.toFixed(3)})`;
                ctx.fill();
            });
            goldDustRaf = requestAnimationFrame(tick);
        }
        cancelAnimationFrame(goldDustRaf);
        goldDustRaf = requestAnimationFrame(tick);
    }

    function stopGoldDustCanvas() {
        cancelAnimationFrame(goldDustRaf);
    }

    function renderRitualLadder(linesArr) {
        if (!ritualLadderEl) return;
        let html = '';
        for (let i = 0; i < 6; i++) {
            const val = linesArr[i];
            if (!val) {
                html += `
                    <div class="mayhen_iching_rladder_row">
                        <span class="mayhen_iching_rladder_num">${i + 1}</span>
                        <div class="mayhen_iching_rladder_bars"><span class="mayhen_iching_rladder_ghost"></span></div>
                        <span class="mayhen_iching_rladder_mark"></span>
                    </div>
                `;
            } else {
                const isYang = val === 7 || val === 9;
                const isMoving = val === 6 || val === 9;
                const mark = val === 9 ? '○' : (val === 6 ? '×' : '');
                const bars = isYang
                    ? '<span class="mayhen_iching_rladder_yang"></span>'
                    : '<span class="mayhen_iching_rladder_yin"></span><span class="mayhen_iching_rladder_yin"></span>';
                html += `
                    <div class="mayhen_iching_rladder_row is-lit ${isMoving ? 'is-moving' : ''}">
                        <span class="mayhen_iching_rladder_num">${i + 1}</span>
                        <div class="mayhen_iching_rladder_bars">${bars}</div>
                        <span class="mayhen_iching_rladder_mark">${mark}</span>
                    </div>
                `;
            }
        }
        ritualLadderEl.innerHTML = html;
    }

    function buildRitualHexSvg(linesArr) {
        const rows = [];
        for (let i = 5; i >= 0; i--) {
            const y = (5 - i) * 20 + 6;
            const val = Number(linesArr[i] || 7);
            const isYang = val === 7 || val === 9;
            const isMoving = val === 6 || val === 9;
            const fill = isMoving ? '#DF4B5D' : '#E7C178';
            const glow = isMoving ? 'rgba(223,75,93,0.55)' : 'rgba(231,193,120,0.45)';
            if (isYang) {
                rows.push(`<rect x="18" y="${y}" width="92" height="10" rx="4" fill="${fill}" style="filter:drop-shadow(0 0 6px ${glow})" />`);
            } else {
                rows.push(`<rect x="18" y="${y}" width="40" height="10" rx="4" fill="${fill}" style="filter:drop-shadow(0 0 6px ${glow})" />`);
                rows.push(`<rect x="70" y="${y}" width="40" height="10" rx="4" fill="${fill}" style="filter:drop-shadow(0 0 6px ${glow})" />`);
            }
            rows.push(`<text x="6" y="${y + 8.5}" font-size="10" fill="rgba(235,221,198,0.5)" text-anchor="middle">${i + 1}</text>`);
            if (isMoving) {
                rows.push(`<text x="122" y="${y + 9}" font-size="11" font-weight="700" fill="#E85C6C" text-anchor="middle">${val === 9 ? '○' : '×'}</text>`);
            }
        }
        return `<svg viewBox="0 0 136 130" width="140" height="134" aria-hidden="true">${rows.join('')}</svg>`;
    }

    function waitMs(ms) {
        return new Promise((r) => setTimeout(r, ms));
    }

    ritualSoundBtn?.addEventListener('click', () => {
        soundEnabled = !soundEnabled;
        ritualSoundBtn.textContent = soundEnabled ? '🔔 Âm thanh: Bật' : '🔕 Âm thanh: Tắt';
        if (soundBtn) {
            soundBtn.classList.toggle('is-active', soundEnabled);
            soundBtn.textContent = soundEnabled ? '🔔 Âm thanh: Bật' : '🔕 Âm thanh: Tắt';
        }
        if (soundEnabled) playCoinClink(true);
    });

    async function runCinematicRitual({ question, shouldTossCoins, presetLines, fixedFacesSequence = null, fetchPromiseFactory }) {
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!ritualOverlay || skipAnimation || prefersReduced) {
            const generatedLines = shouldTossCoins
                ? Array.from({ length: 6 }, () => [0, 1, 2].reduce((acc) => acc + (Math.random() < 0.5 ? 3 : 2), 0))
                : presetLines;
            return await fetchPromiseFactory(generatedLines, null);
        }

        let skipped = false;
        let resolvedApiPayload = null;
        let apiFailed = null;
        let apiStarted = false;
        let apiPromise = null;
        let proceedResolver = null;

        function startApiOnce(linesArr, coinFacesArr) {
            if (apiStarted) return;
            apiStarted = true;
            apiPromise = fetchPromiseFactory(linesArr, coinFacesArr)
                .then((res) => {
                    resolvedApiPayload = res;
                    if (res?.ok && res.data?.success && ritualProceedBtn && ritualProceedText && ritualStatusNote) {
                        ritualProceedBtn.disabled = false;
                        ritualProceedText.textContent = 'Xem luận giải ngay →';
                        ritualStatusNote.textContent = res.data.result_url
                            ? 'Luận giải đã hoàn tất — Đang tự động chuyển sang trang kết quả...'
                            : 'Bài luận đã hoàn tất — Chạm để mở bản chi tiết';
                    }
                    return res;
                })
                .catch((err) => {
                    apiFailed = err;
                    return { ok: false, error: err };
                });
        }

        function closeOverlaySmooth() {
            return new Promise((resolve) => {
                ritualOverlay.classList.add('is-leaving');
                setTimeout(() => {
                    ritualOverlay.classList.remove('is-open', 'is-leaving');
                    ritualOverlay.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    stopGoldDustCanvas();
                    resolve();
                }, 480);
            });
        }

        // Reset DOM sân khấu nghi thức
        ritualHeader?.classList.remove('is-hidden');
        ritualStage?.classList.remove('is-fading-out', 'is-gone');
        ritualRevealEl?.classList.remove('is-mounted', 'is-visible');
        ritualRevealEl?.querySelectorAll('.mayhen_iching_ritual_reveal_block').forEach((b) => b.classList.remove('is-shown'));
        ritualTagEls.forEach((t) => { if (t) { t.textContent = ''; t.classList.remove('is-visible', 'is-yin'); } });

        if (ritualQuestionEl) ritualQuestionEl.textContent = `“${question}”`;
        if (ritualPhaseEl) ritualPhaseEl.textContent = 'Tĩnh tâm';
        if (ritualSubEl) ritualSubEl.textContent = 'Giữ câu hỏi trong lòng...';
        if (ritualProceedBtn) ritualProceedBtn.disabled = true;
        if (ritualProceedText) ritualProceedText.textContent = 'Đang chuẩn bị luận giải...';
        if (ritualStatusNote) ritualStatusNote.textContent = 'Luận giải đang được viết cho riêng câu hỏi của bạn...';
        if (ritualSoundBtn) ritualSoundBtn.textContent = soundEnabled ? '🔔 Âm thanh: Bật' : '🔕 Âm thanh: Tắt';

        const ritualLines = shouldTossCoins ? [] : [...(presetLines || [])];
        const ritualCoinFaces = [];
        renderRitualLadder(ritualLines);

        // Đặt 3 đồng xu bay vào từ trên cao
        ritualCoinEls.forEach((el, idx) => {
            if (!el) return;
            el.style.opacity = '0';
            el.style.transform = `translate3d(${(idx - 1) * 40}px, -160px, 0px) rotateX(65deg) rotateY(30deg)`;
        });

        document.body.style.overflow = 'hidden';
        ritualOverlay.setAttribute('aria-hidden', 'false');
        ritualOverlay.classList.add('is-open');
        startGoldDustCanvas();

        const onSkip = () => {
            skipped = true;
            if (proceedResolver) proceedResolver();
        };
        const onProceed = () => {
            if (proceedResolver) proceedResolver();
        };
        ritualSkipBtn?.addEventListener('click', onSkip, { once: true });
        ritualProceedBtn?.addEventListener('click', onProceed);

        try {
            if (shouldTossCoins) {
                // Nhịp 1: Đưa 3 đồng xu lướt mượt vào sân khấu (00:01 -> 00:03)
                await waitMs(220);
                ritualCoinEls.forEach((el, idx) => {
                    if (!el) return;
                    el.style.transition = `transform 0.72s cubic-bezier(0.22, 1, 0.36, 1) ${idx * 110}ms, opacity 0.55s ease ${idx * 110}ms`;
                    el.style.opacity = '1';
                    const tiltZ = idx === 0 ? -5 : (idx === 2 ? 5 : 0);
                    el.style.transform = `translate3d(0px, 0px, 0px) rotateX(10deg) rotateY(0deg) rotateZ(${tiltZ}deg)`;
                });
                await waitMs(860);
                ritualCoinEls.forEach((el) => { if (el) el.style.transition = ''; });

                // Nhịp 2: 6 lượt tung 3 đồng xu 3D vật lý + sáng dần 6 hào (00:03 -> 00:11)
                for (let step = 1; step <= 6; step++) {
                    if (skipped) {
                        while (ritualLines.length < 6) {
                            const f = [Math.random() < 0.5 ? 3 : 2, Math.random() < 0.5 ? 3 : 2, Math.random() < 0.5 ? 3 : 2];
                            ritualLines.push(f[0] + f[1] + f[2]);
                            ritualCoinFaces.push(f.map((v) => (v === 3 ? 'Ngửa (3)' : 'Sấp (2)')));
                        }
                        break;
                    }

                    if (ritualPhaseEl) ritualPhaseEl.textContent = `Hào thứ ${step} / 6`;
                    if (ritualSubEl) ritualSubEl.textContent = step === 1 ? 'Khởi niệm lập hào Sơ...' : (step === 6 ? 'Hoàn thiện hào Thượng...' : 'Đang gieo 3 đồng xu cổ...');
                    ritualTagEls.forEach((t) => { if (t) t.classList.remove('is-visible'); });

                    const faces = Array.isArray(fixedFacesSequence?.[step - 1])
                        ? fixedFacesSequence[step - 1]
                        : [
                            Math.random() < 0.5 ? 3 : 2,
                            Math.random() < 0.5 ? 3 : 2,
                            Math.random() < 0.5 ? 3 : 2,
                        ];
                    playCoinClink(soundEnabled, 180);

                    await Promise.all(
                        ritualCoinEls.map((el, idx) => animateCoinToss3D(el, ritualShadowEls[idx], faces[idx] === 3, idx, {
                            duration: 860,
                            peakHeight: 112,
                            stagger: 65,
                            spinsX: 2,
                            spinsY: 2,
                        }))
                    );

                    ritualTagEls.forEach((tag, idx) => {
                        if (!tag) return;
                        const isYang = faces[idx] === 3;
                        tag.textContent = isYang ? 'Ngửa' : 'Sấp';
                        tag.classList.toggle('is-yin', !isYang);
                        tag.classList.add('is-visible');
                    });

                    const sum = faces[0] + faces[1] + faces[2];
                    ritualLines.push(sum);
                    ritualCoinFaces.push(faces.map((v) => (v === 3 ? 'Ngửa (3)' : 'Sấp (2)')));
                    renderRitualLadder(ritualLines);
                    await waitMs(step === 6 ? 420 : 280);
                }

                // Đồng bộ lại tháp hào trên trang chính
                if (activeMethod === 'coins_3') {
                    rolledLines = [...ritualLines];
                    rolledCoinFaces = [...ritualCoinFaces];
                    renderLinesStack();
                }

                // Gọi API luận giải NGAY LẬP TỨC song song với màn Ứng Quẻ
                startApiOnce(ritualLines, ritualCoinFaces);
            } else {
                startApiOnce(presetLines, null);
            }

            if (skipped) {
                const finalRes = await apiPromise;
                await closeOverlaySmooth();
                return finalRes;
            }

            // Nhịp 3: Chuyển cảnh mượt sang Màn Ứng Quẻ (00:11 -> 00:17)
            ritualStage?.classList.add('is-fading-out');
            ritualHeader?.classList.add('is-hidden');
            await waitMs(480);
            ritualStage?.classList.add('is-gone');

            // Xác định ngay quẻ Chủ để hiển thị tức thì (nếu là Mai Hoa chưa có lines sẵn -> đợi API)
            let revealLines = ritualLines.length === 6 ? ritualLines : null;
            let primaryHex = null;

            if (revealLines) {
                const bits = revealLines.map((v) => (v === 7 || v === 9 ? '1' : '0')).join('');
                primaryHex = catalogByCode[bits] || null;
            }

            if (!primaryHex) {
                const apiRes = await apiPromise;
                if (!apiRes?.ok || !apiRes.data?.success) {
                    await closeOverlaySmooth();
                    return apiRes;
                }
                primaryHex = apiRes.data.analysis?.primary || null;
                revealLines = apiRes.data.analysis?.lines || [7, 7, 7, 7, 7, 7];
            }

            if (primaryHex && ritualRevealEl) {
                const hexBox = document.getElementById('mayhenIchingRitualRevealHex');
                const hanziEl = document.getElementById('mayhenIchingRitualRevealHanzi');
                const nameEl = document.getElementById('mayhenIchingRitualRevealName');
                const noEl = document.getElementById('mayhenIchingRitualRevealNo');
                const judgEl = document.getElementById('mayhenIchingRitualRevealJudgment');
                const imgEl = document.getElementById('mayhenIchingRitualRevealImage');

                if (hexBox) hexBox.innerHTML = buildRitualHexSvg(revealLines);
                if (hanziEl) hanziEl.textContent = primaryHex.hanzi || '';
                if (nameEl) nameEl.textContent = primaryHex.name || '';
                if (noEl) noEl.textContent = `Quẻ số ${primaryHex.no}`;
                if (judgEl) judgEl.textContent = primaryHex.judgment || '';
                if (imgEl) imgEl.textContent = primaryHex.image || '';

                ritualRevealEl.classList.add('is-mounted');
                void ritualRevealEl.offsetWidth;
                ritualRevealEl.classList.add('is-visible');
                playSoftChime(soundEnabled);

                const blocks = ritualRevealEl.querySelectorAll('.mayhen_iching_ritual_reveal_block');
                await waitMs(420);
                blocks[0]?.classList.add('is-shown');
                await waitMs(450);
                blocks[1]?.classList.add('is-shown');
            }

            // Chờ API trả về (nếu chưa xong) — trường hợp báo lỗi/crisis/429 sẽ đóng overlay ngay
            const finalApiRes = await apiPromise;
            if (!finalApiRes?.ok || !finalApiRes.data?.success || skipped || apiFailed) {
                await closeOverlaySmooth();
                return finalApiRes;
            }

            // Nếu đã có trang kết quả riêng (/gieo-que/ket-qua/{slug}), tự động chuyển trang êm ái sau 900ms (hoặc ngay khi bấm nút)
            const autoRedirectDelay = finalApiRes.data?.result_url ? 900 : 6500;
            await new Promise((resolve) => {
                proceedResolver = resolve;
                setTimeout(resolve, autoRedirectDelay);
            });

            if (finalApiRes.data?.result_url) {
                ritualOverlay.classList.add('is-leaving');
                return finalApiRes;
            }

            await closeOverlaySmooth();
            return finalApiRes;
        } finally {
            ritualProceedBtn?.removeEventListener('click', onProceed);
            ritualSkipBtn?.removeEventListener('click', onSkip);
        }
    }

    // Gửi gieo quẻ & luận giải (chạy qua sân khấu nghi thức 3D mượt mà)
    submitCastBtn?.addEventListener('click', async () => {
        if (!canCastFree) {
            document.getElementById('mayhenIchingLoginModal')?.classList.add('is-open');
            return;
        }

        if (dailyRemaining <= 0 || cooldownRemaining > 0) {
            syncCastQuotaUi();
            quotaNoticeEl?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const question = (questionInput?.value || '').trim();
        if (question.length < 5) {
            alert('Vui lòng nhập điều bạn đang trăn trở (tối thiểu 5 ký tự) để bài luận đi đúng trọng tâm.');
            questionInput?.focus();
            return;
        }

        let payloadMethod = activeMethod;
        if (activeMethod === 'mai_hoa') {
            payloadMethod = maiHoaModeSelect?.value || 'mai_hoa_time';
        }

        let manualLines = null;
        if (payloadMethod === 'coins_manual') {
            manualLines = [];
            for (let i = 1; i <= 6; i++) {
                const sel = document.getElementById(`mayhenIchingManualLine${i}`);
                manualLines.push(Number(sel?.value || 7));
            }
        }

        if (!currentIdempotencyKey) {
            currentIdempotencyKey = generateIdempotencyKey();
        }

        const shouldTossCoinsInRitual = (payloadMethod === 'coins_3' && rolledLines.length < 6)
            || payloadMethod === 'spring_blessing';
        const presetLines = payloadMethod === 'coins_3' && rolledLines.length === 6
            ? rolledLines
            : (payloadMethod === 'coins_manual' ? manualLines : null);

        submitCastBtn.disabled = true;
        const oldLabel = submitCastBtn.textContent;
        submitCastBtn.textContent = '✦ Đang Lập Quẻ & Luận Giải...';

        try {
            const apiOutcome = await runCinematicRitual({
                question,
                shouldTossCoins: shouldTossCoinsInRitual,
                presetLines,
                fetchPromiseFactory: async (finalLines, finalCoinFaces) => {
                    const body = {
                        method: payloadMethod,
                        topic: activeTopic,
                        sub_topic: activeSubTopic || null,
                        question,
                        context_note: (contextInput?.value || '').trim() || null,
                        hide_question_in_storage: Boolean(hideQuestionCb?.checked),
                        idempotency_key: currentIdempotencyKey,
                    };

                    if (Array.isArray(finalLines) && finalLines.length === 6 && (payloadMethod === 'coins_3' || payloadMethod === 'coins_manual' || payloadMethod === 'spring_blessing')) {
                        body.lines = finalLines;
                        if (Array.isArray(finalCoinFaces) && finalCoinFaces.length === 6) {
                            body.coin_faces = finalCoinFaces;
                        } else if (payloadMethod === 'coins_3' && rolledCoinFaces.length === 6) {
                            body.coin_faces = rolledCoinFaces;
                        }
                    }
                    if (payloadMethod === 'mai_hoa_numbers') {
                        body.number_upper = Number(document.getElementById('mayhenIchingNumUpper')?.value || 18);
                        body.number_lower = Number(document.getElementById('mayhenIchingNumLower')?.value || 26);
                    }

                    const res = await fetch(castUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(body),
                    });
                    const data = await res.json();
                    return { ok: res.ok, status: res.status, data };
                },
            });

            if (!apiOutcome) return;
            const { ok, status, data } = apiOutcome;

            if (status === 401 || status === 403) {
                document.getElementById('mayhenIchingLoginModal')?.classList.add('is-open');
                return;
            }

            if (data?.code === 'SAFETY_CRISIS_BLOCK' && data.safety && crisisBox) {
                const resList = (data.safety.resources || []).map((r) => `<li><strong>${escapeHtml(r.label)}:</strong> ${escapeHtml(r.detail)}</li>`).join('');
                crisisBox.innerHTML = `
                    <strong>${escapeHtml(data.safety.title || 'Bạn không cần đối diện chuyện này một mình')}</strong>
                    <p style="margin:6px 0 0;">${escapeHtml(data.safety.notice)}</p>
                    ${resList ? `<ul>${resList}</ul>` : ''}
                `;
                crisisBox.classList.add('is-visible');
                crisisBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            if (status === 429 && data) {
                if (data.quota) {
                    dailyRemaining = Number(data.quota.daily_remaining ?? dailyRemaining);
                    cooldownRemaining = Number(data.quota.cooldown_seconds ?? 0);
                    latestReadingUrl = data.quota.latest_reading_url || latestReadingUrl;
                }
                syncCastQuotaUi(data.message || '');
                quotaNoticeEl?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            if (!ok || !data?.success) {
                alert(data?.message || 'Không thể luận giải quẻ lúc này. Vui lòng thử lại.');
                return;
            }

            sessionStorage.removeItem(DRAFT_KEY);
            currentIdempotencyKey = generateIdempotencyKey();

            // Tự động chuyển thẳng sang link kết quả riêng tư (/gieo-que/ket-qua/{slug}) khi AI luận giải xong
            if (data.result_url) {
                window.location.assign(data.result_url);
                return;
            }

            renderReadingResult(data);
        } catch {
            alert('Lỗi kết nối tới máy chủ. Bạn có thể bấm gửi lại — hệ thống sẽ giữ nguyên kết quả gieo.');
        } finally {
            submitCastBtn.disabled = false;
            submitCastBtn.textContent = oldLabel;
        }
    });

    // Xem quẻ mẫu cho khách chưa đăng nhập — cho phép trải nghiệm trọn vẹn hiệu ứng 3D!
    document.getElementById('mayhenIchingShowDemoBtn')?.addEventListener('click', async () => {
        document.getElementById('mayhenIchingLoginModal')?.classList.remove('is-open');
        const demoEl = document.getElementById('mayhenIchingDemoJson');
        if (!demoEl) return;
        try {
            const demoPayload = JSON.parse(demoEl.textContent || '{}');
            // 6 lượt tung khớp đúng Quẻ Mẫu #11 Địa Thiên Thái ([7,7,7,8,8,8]) để màn Ứng Quẻ đồng bộ 100%
            const demoFaces = [
                [3, 2, 2], [2, 3, 2], [2, 2, 3],
                [3, 3, 2], [3, 2, 3], [2, 3, 3],
            ];
            await runCinematicRitual({
                question: demoPayload.question || 'Tôi nên chuẩn bị điều gì để năm mới và gia đạo thêm hài hòa, vững vàng?',
                shouldTossCoins: true,
                presetLines: null,
                fixedFacesSequence: demoFaces,
                fetchPromiseFactory: async () => ({ ok: true, status: 200, data: { ...demoPayload, success: true } }),
            });
            renderReadingResult(demoPayload);
        } catch {
            // Bỏ qua
        }
    });

    document.getElementById('mayhenIchingLoginModalClose')?.addEventListener('click', () => {
        document.getElementById('mayhenIchingLoginModal')?.classList.remove('is-open');
    });

    // TRA CỨU 64 QUẺ KINH DỊCH — TÌM KHÔNG DẤU + LỌC 10 CHỦ ĐỀ ĐỜI SỐNG
    const hexSearchInput = document.getElementById('mayhenIching64Search');
    const hexCards = Array.from(document.querySelectorAll('.mayhen_iching_64_card'));
    let activeThemeFilter = 'all';

    function filter64Grid() {
        const q = stripVietnameseTones((hexSearchInput?.value || '').trim());
        hexCards.forEach((card) => {
            const text = stripVietnameseTones(card.getAttribute('data-search') || '');
            const themes = (card.getAttribute('data-themes') || '').split(',');
            const matchQ = q === '' || text.includes(q);
            const matchTheme = activeThemeFilter === 'all' || themes.includes(activeThemeFilter);
            card.style.display = matchQ && matchTheme ? '' : 'none';
        });
    }

    hexSearchInput?.addEventListener('input', filter64Grid);
    document.querySelectorAll('.mayhen_iching_filter_btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            activeThemeFilter = btn.getAttribute('data-theme-filter') || 'all';
            document.querySelectorAll('.mayhen_iching_filter_btn').forEach((b) => b.classList.toggle('is-active', b === btn));
            filter64Grid();
        });
    });

    // Modal chi tiết từng quẻ trong kho 64 quẻ (có Quẻ Hỗ / Quẻ Thác / Quẻ Tông)
    const hexModal = document.getElementById('mayhenIchingHexModal');
    const hexModalBody = document.getElementById('mayhenIchingHexModalBody');
    const catalogJsonEl = document.getElementById('mayhenIchingCatalogJson');
    let catalogMap = {};
    let catalogByCode = {};
    if (catalogJsonEl) {
        try {
            const list = JSON.parse(catalogJsonEl.textContent || '[]');
            Object.values(list).forEach((h) => {
                catalogMap[String(h.no)] = h;
                if (h.code) catalogByCode[h.code] = h;
            });
        } catch {
            // Bỏ qua
        }
    }

    function openHexagramModal(no) {
        const h = catalogMap[String(no)];
        if (!h || !hexModal || !hexModalBody) return;

        const bits = String(h.code || '111111');
        const mutualHex = catalogByCode[bits[1] + bits[2] + bits[3] + bits[2] + bits[3] + bits[4]];
        const oppBits = bits.split('').map((b) => (b === '1' ? '0' : '1')).join('');
        const revBits = bits.split('').reverse().join('');
        const oppositeHex = catalogByCode[oppBits];
        const reverseHex = catalogByCode[revBits];

        const classicalBox = h.classical_data
            ? `
                <div style="padding:10px 13px; background:#F8F4EC; border-radius:10px; border:1px solid #E3D6C0; margin-bottom:10px;">
                    <span class="mayhen_iching_provenance_badge" style="margin:0 0 4px;">Cổ thư đã thẩm định</span>
                    <div><strong>Quái Từ:</strong> ${escapeHtml(h.classical_data.guaci?.original)} — <em>${escapeHtml(h.classical_data.guaci?.han_viet)}</em></div>
                    <div><strong>Đại Tượng:</strong> ${escapeHtml(h.classical_data.daxiang?.original)} — <em>${escapeHtml(h.classical_data.daxiang?.han_viet)}</em></div>
                </div>
              `
            : '';

        hexModalBody.innerHTML = `
            <div style="text-align:center; margin-bottom:14px;">
                <span class="mayhen_iching_level_pill">Quẻ #${h.no} ${escapeHtml(h.unicode || '')} • ${escapeHtml(h.guidance_state_ref || h.level)}</span>
                <div class="mayhen_iching_hex_svg_wrap">${buildHexagramSvg(h.code, [])}</div>
                <h3 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; font-size:24px; margin:0 0 4px; color:#85182A;">
                    ${escapeHtml(h.name)} (${escapeHtml(h.hanzi)})
                </h3>
                <p style="font-size:13px; color:#6A635B; margin:0;">${escapeHtml(h.trigram_pair_label)}</p>
            </div>
            ${classicalBox}
            <div style="font-size:13.5px; line-height:1.7; color:#3D352E;">
                <p><strong>Quái từ lược dịch:</strong> <em>${escapeHtml(h.judgment)}</em></p>
                <p><strong>Đại tượng:</strong> ${escapeHtml(h.image)}</p>
                <p><strong>Tổng quan:</strong> ${escapeHtml(h.summary)}</p>
                <p><strong>Tình duyên &amp; Hôn nhân:</strong> ${escapeHtml(h.love)}</p>
                <p><strong>Công danh &amp; Sự nghiệp:</strong> ${escapeHtml(h.career)}</p>
                <p><strong>Tài lộc:</strong> ${escapeHtml(h.wealth)}</p>
                <p><strong>Gia đạo:</strong> ${escapeHtml(h.family)}</p>
                <div style="padding:12px 14px; background:#FAF5EB; border-radius:12px; border:1px solid #DFCFAF; margin:10px 0;">
                    <strong>Lời khuyên ứng dụng:</strong> ${escapeHtml(h.advice)}
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:12px;">
                    ${mutualHex ? `<button type="button" class="mayhen_iching_pill_btn" data-jump-hex="${mutualHex.no}">Quẻ Hỗ: #${mutualHex.no} ${escapeHtml(mutualHex.name)}</button>` : ''}
                    ${oppositeHex ? `<button type="button" class="mayhen_iching_pill_btn" data-jump-hex="${oppositeHex.no}">Quẻ Thác (Đảo): #${oppositeHex.no} ${escapeHtml(oppositeHex.name)}</button>` : ''}
                    ${reverseHex ? `<button type="button" class="mayhen_iching_pill_btn" data-jump-hex="${reverseHex.no}">Quẻ Tông (Lật): #${reverseHex.no} ${escapeHtml(reverseHex.name)}</button>` : ''}
                </div>
            </div>
        `;
        hexModalBody.querySelectorAll('[data-jump-hex]').forEach((b) => {
            b.addEventListener('click', () => openHexagramModal(b.getAttribute('data-jump-hex')));
        });
        hexModal.classList.add('is-open');
    }

    hexCards.forEach((card) => {
        card.addEventListener('click', () => openHexagramModal(card.getAttribute('data-hex-no')));
    });

    document.getElementById('mayhenIchingHexModalClose')?.addEventListener('click', () => {
        hexModal?.classList.remove('is-open');
    });

    restoreDraft();
    document.querySelectorAll('.mayhen_iching_topic_chip').forEach((c) => {
        c.classList.toggle('is-active', c.getAttribute('data-topic') === activeTopic);
    });
    renderSubTopics();
    renderLinesStack();
});
