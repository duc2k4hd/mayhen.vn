/**
 * MAYHEN.VN — MÂY HẸN – THIÊN MỆNH (TỬ VI ĐẨU SỐ 3D & THIÊN BÀN TƯƠNG TÁC)
 * 100% Pure Vanilla JS ES Module • Zero External Libraries
 */
import { formatSafeAiMarkdown } from '../common/markdown-safe.js';
import { saveToolDraft, loadToolDraft, clearToolDraft, bindFormDraft, showDraftRestoredBanner } from '../common/tool-draft.js';

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

    function stripTones(str) {
        return String(str ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'D')
            .toLowerCase();
    }

    // =========================================================================
    // 1. BỘ 12 TRANH MINH HỌA VECTOR SVG GỐC ĐỒNG BỘ (ORIENTAL CELESTIAL ART)
    // =========================================================================
    function buildPalaceArtworkSvg(artKey, accent = '#CFA860') {
        const stroke = escapeHtml(accent);
        const artworks = {
            menh: `
                <circle cx="70" cy="76" r="46" stroke="${stroke}" stroke-width="1" stroke-dasharray="2 5" opacity="0.7"/>
                <circle cx="70" cy="76" r="30" stroke="${stroke}" stroke-width="1.3"/>
                <polygon points="70,34 76,66 108,76 76,86 70,118 64,86 32,76 64,66" fill="${stroke}" fill-opacity="0.22" stroke="${stroke}" stroke-width="1.4"/>
                <circle cx="70" cy="76" r="6" fill="${stroke}"/>
            `,
            huynh_de: `
                <ellipse cx="70" cy="76" rx="48" ry="26" stroke="${stroke}" stroke-width="1.1" transform="rotate(-22 70 76)" opacity="0.7"/>
                <ellipse cx="70" cy="76" rx="48" ry="26" stroke="${stroke}" stroke-width="1.1" transform="rotate(22 70 76)" opacity="0.7"/>
                <circle cx="48" cy="76" r="10" fill="${stroke}" fill-opacity="0.28" stroke="${stroke}" stroke-width="1.5"/>
                <circle cx="92" cy="76" r="10" fill="${stroke}" fill-opacity="0.28" stroke="${stroke}" stroke-width="1.5"/>
                <path d="M58 76 H82" stroke="${stroke}" stroke-width="1.2" stroke-dasharray="3 3"/>
            `,
            phu_the: `
                <ellipse cx="70" cy="76" rx="50" ry="24" stroke="${stroke}" stroke-width="1" transform="rotate(-16 70 76)" opacity="0.65"/>
                <ellipse cx="70" cy="76" rx="50" ry="24" stroke="${stroke}" stroke-width="1" transform="rotate(16 70 76)" opacity="0.65"/>
                <circle cx="54" cy="76" r="15" fill="${stroke}" fill-opacity="0.24" stroke="${stroke}" stroke-width="1.4"/>
                <path d="M90 61 A15 15 0 1 0 90 91 A11 11 0 1 1 90 61 Z" fill="${stroke}" fill-opacity="0.82"/>
                <circle cx="70" cy="76" r="3" fill="#FFF3D4"/>
            `,
            tu_tuc: `
                <path d="M70 118 V62" stroke="${stroke}" stroke-width="1.6" stroke-linecap="round"/>
                <path d="M70 92 C52 86, 44 72, 48 56 C60 60, 68 72, 70 92 Z" fill="${stroke}" fill-opacity="0.24" stroke="${stroke}" stroke-width="1.3"/>
                <path d="M70 84 C88 78, 96 64, 92 48 C80 52, 72 64, 70 84 Z" fill="${stroke}" fill-opacity="0.24" stroke="${stroke}" stroke-width="1.3"/>
                <circle cx="70" cy="48" r="10" fill="${stroke}" fill-opacity="0.25" stroke="${stroke}" stroke-width="1.3"/>
            `,
            tai_bach: `
                <circle cx="70" cy="76" r="42" stroke="${stroke}" stroke-width="1.3"/>
                <circle cx="70" cy="76" r="32" stroke="${stroke}" stroke-width="1" stroke-dasharray="4 4"/>
                <rect x="57" y="63" width="26" height="26" rx="2" fill="${stroke}" fill-opacity="0.18" stroke="${stroke}" stroke-width="1.5"/>
                <path d="M36 112 Q70 96 104 112" stroke="${stroke}" stroke-width="1.2" opacity="0.7"/>
            `,
            tat_ach: `
                <path d="M26 98 Q48 90 70 98 T114 98" stroke="${stroke}" stroke-width="1.3"/>
                <path d="M36 110 Q70 102 104 110" stroke="${stroke}" stroke-width="1" opacity="0.65"/>
                <path d="M78 44 A22 22 0 1 0 92 78 A17 17 0 1 1 78 44 Z" fill="${stroke}" fill-opacity="0.65"/>
                <path d="M52 88 C58 74, 72 68, 86 72 C78 86, 66 90, 52 88 Z" fill="${stroke}" fill-opacity="0.22" stroke="${stroke}" stroke-width="1.2"/>
            `,
            thien_di: `
                <path d="M22 106 L52 66 L74 90 L94 58 L118 106 Z" fill="${stroke}" fill-opacity="0.16" stroke="${stroke}" stroke-width="1.4"/>
                <path d="M32 54 Q46 44 60 54 T88 54" stroke="${stroke}" stroke-width="1.2" opacity="0.75"/>
                <circle cx="98" cy="42" r="8" fill="${stroke}" fill-opacity="0.35" stroke="${stroke}" stroke-width="1.2"/>
            `,
            no_boc: `
                <circle cx="70" cy="74" r="7" fill="${stroke}"/>
                <circle cx="40" cy="54" r="5" fill="${stroke}" fill-opacity="0.75"/>
                <circle cx="102" cy="52" r="5" fill="${stroke}" fill-opacity="0.75"/>
                <circle cx="44" cy="102" r="5" fill="${stroke}" fill-opacity="0.75"/>
                <circle cx="98" cy="100" r="5" fill="${stroke}" fill-opacity="0.75"/>
                <path d="M40 54 L70 74 L102 52 M44 102 L70 74 L98 100 M40 54 L44 102 M102 52 L98 100" stroke="${stroke}" stroke-width="1.1" opacity="0.7"/>
            `,
            quan_loc: `
                <path d="M24 112 L70 46 L116 112 Z" fill="${stroke}" fill-opacity="0.16" stroke="${stroke}" stroke-width="1.4"/>
                <path d="M70 46 V112" stroke="${stroke}" stroke-width="1" stroke-dasharray="3 4"/>
                <circle cx="70" cy="36" r="10" fill="${stroke}" fill-opacity="0.32" stroke="${stroke}" stroke-width="1.4"/>
            `,
            dien_trach: `
                <path d="M26 76 L70 42 L114 76" stroke="${stroke}" stroke-width="1.8" stroke-linecap="round"/>
                <rect x="38" y="76" width="64" height="36" rx="2" fill="${stroke}" fill-opacity="0.16" stroke="${stroke}" stroke-width="1.4"/>
                <rect x="61" y="88" width="18" height="24" rx="2" fill="${stroke}" fill-opacity="0.35" stroke="${stroke}" stroke-width="1.2"/>
            `,
            phuc_duc: `
                <path d="M70 42 C82 58, 82 78, 70 92 C58 78, 58 58, 70 42 Z" fill="${stroke}" fill-opacity="0.28" stroke="${stroke}" stroke-width="1.4"/>
                <path d="M70 92 C52 88, 38 74, 34 58 C50 62, 62 74, 70 92 Z" fill="${stroke}" fill-opacity="0.18" stroke="${stroke}" stroke-width="1.2"/>
                <path d="M70 92 C88 88, 102 74, 106 58 C90 62, 78 74, 70 92 Z" fill="${stroke}" fill-opacity="0.18" stroke="${stroke}" stroke-width="1.2"/>
                <path d="M30 106 Q70 98 110 106" stroke="${stroke}" stroke-width="1.2" opacity="0.7"/>
            `,
            phu_mau: `
                <circle cx="70" cy="64" r="28" fill="${stroke}" fill-opacity="0.16" stroke="${stroke}" stroke-width="1.3"/>
                <circle cx="70" cy="64" r="18" stroke="${stroke}" stroke-width="1" stroke-dasharray="3 3"/>
                <path d="M70 92 V118 M70 104 L52 118 M70 104 L88 118" stroke="${stroke}" stroke-width="1.6" stroke-linecap="round"/>
            `,
        };

        return `<svg viewBox="0 0 140 150" width="134" height="144" fill="none" aria-hidden="true">${artworks[artKey] || artworks.menh}</svg>`;
    }

    // =========================================================================
    // 2. BẦU TRỜI SAO CANVAS (TỰ TẠM DỪNG KHI RỜI TAB / NGOÀI VIEWPORT)
    // =========================================================================
    let activeQuality = window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ? 'lightweight'
        : (window.innerWidth < 768 ? 'balanced' : 'high');

    function initStarfieldCanvas(canvas) {
        if (!canvas) return () => {};
        const ctx = canvas.getContext('2d');
        if (!ctx) return () => {};

        let rafId = 0;
        let inView = true;
        let w = 0;
        let h = 0;
        let stars = [];

        function rebuild() {
            const rect = canvas.parentElement?.getBoundingClientRect();
            w = canvas.width = Math.max(320, Math.floor(rect?.width || window.innerWidth));
            h = canvas.height = Math.max(240, Math.floor(rect?.height || 520));
            const count = activeQuality === 'high' ? 46 : (activeQuality === 'balanced' ? 22 : 0);
            stars = Array.from({ length: count }, () => ({
                x: Math.random() * w,
                y: Math.random() * h,
                r: 0.7 + Math.random() * 1.5,
                vy: -(0.08 + Math.random() * 0.2),
                alpha: 0.16 + Math.random() * 0.45,
                phase: Math.random() * Math.PI * 2,
            }));
        }

        function tick() {
            if (!inView || document.hidden || activeQuality === 'lightweight') {
                ctx.clearRect(0, 0, w, h);
                return;
            }
            ctx.clearRect(0, 0, w, h);
            for (const s of stars) {
                s.y += s.vy;
                s.phase += 0.014;
                if (s.y < -6) { s.y = h + 6; s.x = Math.random() * w; }
                const a = s.alpha * (0.65 + 0.35 * Math.sin(s.phase));
                ctx.beginPath();
                ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(235, 201, 132, ${a.toFixed(3)})`;
                ctx.fill();
            }
            rafId = requestAnimationFrame(tick);
        }

        function start() {
            cancelAnimationFrame(rafId);
            rebuild();
            if (activeQuality !== 'lightweight') {
                rafId = requestAnimationFrame(tick);
            }
        }

        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                inView = Boolean(entries[0]?.isIntersecting);
                if (inView && !document.hidden && activeQuality !== 'lightweight') {
                    cancelAnimationFrame(rafId);
                    rafId = requestAnimationFrame(tick);
                }
            }, { threshold: 0.05 });
            io.observe(canvas);
        }

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && inView && activeQuality !== 'lightweight') {
                cancelAnimationFrame(rafId);
                rafId = requestAnimationFrame(tick);
            }
        });
        window.addEventListener('resize', start);
        start();

        return start;
    }

    const starfieldRestarters = Array.from(document.querySelectorAll('.mayhen_tuvi_starfield_canvas')).map(initStarfieldCanvas);

    document.querySelectorAll('[data-tuvi-quality]').forEach((btn) => {
        btn.classList.toggle('is-active', btn.getAttribute('data-tuvi-quality') === activeQuality);
        btn.addEventListener('click', () => {
            activeQuality = btn.getAttribute('data-tuvi-quality') || 'balanced';
            document.querySelectorAll('[data-tuvi-quality]').forEach((b) => b.classList.toggle('is-active', b === btn));
            starfieldRestarters.forEach((fn) => fn());
        });
    });

    // =========================================================================
    // 3. THẬP NHỊ THIÊN MỆNH BÀI 3D — XÒE QUẠT TRÊN TAY (100% TRÊN 1 MÀN, KHÔNG CUỘN)
    // =========================================================================
    function initTwelveCardsDeck(container, chartData, ruleAnalysis, palacesMeta) {
        if (!container || !chartData?.palaces_by_code) return;
        const stage = container.querySelector('[data-tuvi-fan-stage]');
        const fanHand = container.querySelector('[data-tuvi-deck-carousel]');
        const drawer = container.querySelector('[data-tuvi-card-drawer]');
        const exploredCounter = container.querySelector('[data-tuvi-explored-count]');
        if (!fanHand) return;

        const exploredSet = new Set();
        const orderKeys = Object.keys(palacesMeta || {});
        const palaceList = orderKeys.length > 0
            ? orderKeys.map((k) => chartData.palaces_by_code[k]).filter(Boolean)
            : Object.values(chartData.palaces_by_code);

        fanHand.innerHTML = palaceList.map((palace, idx) => {
            const meta = palacesMeta?.[palace.code] || {};
            const majorsHtml = (palace.major_stars || []).length > 0
                ? palace.major_stars.map((s) => `<span class="mayhen_tuvi_star_badge_major"><span>${escapeHtml(s.name)}</span><small>${escapeHtml(s.brightness)}</small></span>`).join('')
                : '<span class="mayhen_tuvi_star_badge_major"><span>Vô Chính Diệu</span><small>Chiếu</small></span>';

            const digest = ruleAnalysis?.palace_digests?.[palace.code] || {};
            return `
                <div class="mayhen_tuvi_card3d_wrap" tabindex="0" role="button" aria-label="Mở Thiên Mệnh Bài ${escapeHtml(palace.name)}" data-palace-code="${escapeHtml(palace.code)}" data-fan-idx="${idx}">
                    <div class="mayhen_tuvi_card3d_inner">
                        <div class="mayhen_tuvi_card3d_face mayhen_tuvi_card3d_back" style="background:linear-gradient(160deg, ${escapeHtml(meta.card_bg_from || '#1B3B31')} 0%, ${escapeHtml(meta.card_bg_to || '#0D1426')} 100%);">
                            <span class="mayhen_tuvi_card3d_back_top">${escapeHtml(palace.hanzi || '')} · ${idx + 1}/12</span>
                            <span class="mayhen_tuvi_card3d_explored_dot" data-dot="${escapeHtml(palace.code)}">✦</span>
                            <div class="mayhen_tuvi_card3d_art_box">${buildPalaceArtworkSvg(meta.art_key || palace.code, meta.theme_color || '#CFA860')}</div>
                            <div>
                                <h3 class="mayhen_tuvi_card3d_back_title">${escapeHtml(palace.short_name || palace.name)}</h3>
                                <div class="mayhen_tuvi_card3d_back_sub">${escapeHtml(palace.can_chi)}</div>
                            </div>
                            <div class="mayhen_tuvi_card3d_sheen"></div>
                        </div>
                        <div class="mayhen_tuvi_card3d_face mayhen_tuvi_card3d_front">
                            <div>
                                <div class="mayhen_tuvi_card3d_front_head">
                                    <strong>${escapeHtml(palace.short_name || palace.name)}</strong>
                                    <small>${escapeHtml(palace.branch_name)}${palace.is_than ? '·THÂN' : ''}</small>
                                </div>
                                <div class="mayhen_tuvi_card3d_front_stars">${majorsHtml}</div>
                            </div>
                            <p class="mayhen_tuvi_card3d_front_note">${escapeHtml(digest.lead_star_insight || meta.modern_meaning || '')}</p>
                            <div style="font-size:9.5px; color:#85182A; font-weight:700;">ĐH ${palace.dai_han_start}–${palace.dai_han_end}t</div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        const wraps = Array.from(fanHand.querySelectorAll('.mayhen_tuvi_card3d_wrap'));
        let activeIndex = -1;

        // Tính tọa độ hình cánh quạt xòe trên tay vừa khít 100% khung hình — KHÔNG BAO GIỜ TRÀN / CUỘN
        function layoutFanHand() {
            const stageWidth = Math.max(300, (stage || fanHand).getBoundingClientRect().width || window.innerWidth);
            const isCompact = stageWidth < 920;

            if (!isCompact) {
                // Desktop: 1 cánh quạt 12 lá duy nhất xòe rộng đều trên 1 màn hình
                const cardW = 164;
                const safeHalf = Math.max(180, (stageWidth - cardW - 28) / 2);
                const maxSpread = Math.min(468, safeHalf);
                wraps.forEach((wrap, i) => {
                    const t = (i - 5.5) / 5.5; // -1 .. +1
                    const x = t * maxSpread;
                    const y = (t * t) * 62;
                    const rot = t * 25;
                    wrap.style.setProperty('--fan-x', `${x.toFixed(1)}px`);
                    wrap.style.setProperty('--fan-y', `${y.toFixed(1)}px`);
                    wrap.style.setProperty('--fan-rot', `${rot.toFixed(1)}deg`);
                    wrap.style.setProperty('--fan-z', String(10 + i));
                    wrap.style.transitionDelay = stage?.classList.contains('is-dealing') ? `${i * 38}ms` : '0ms';
                });
            } else {
                // Tablet / Mobile: 2 vòng quạt nhỏ xếp tầng (6 lá trên + 6 lá dưới) — tất cả 12 lá nằm trọn trên 1 màn không cuộn
                const cardW = stageWidth < 580 ? 94 : 112;
                const safeHalf = Math.max(96, (stageWidth - cardW - 14) / 2);
                const rowSpread = Math.min(260, safeHalf);
                const topRowLift = stageWidth < 580 ? -168 : -186;

                wraps.forEach((wrap, i) => {
                    const row = i < 6 ? 0 : 1;
                    const col = i % 6;
                    const t = (col - 2.5) / 2.5; // -1 .. +1
                    const x = t * rowSpread;
                    const y = (row === 0 ? topRowLift : 0) + (t * t) * 24;
                    const rot = t * 15;
                    wrap.style.setProperty('--fan-x', `${x.toFixed(1)}px`);
                    wrap.style.setProperty('--fan-y', `${y.toFixed(1)}px`);
                    wrap.style.setProperty('--fan-rot', `${rot.toFixed(1)}deg`);
                    wrap.style.setProperty('--fan-z', String((row === 0 ? 10 : 20) + col));
                    wrap.style.transitionDelay = stage?.classList.contains('is-dealing') ? `${i * 30}ms` : '0ms';
                });
            }
        }

        // Tạo hiệu ứng chia bài xòe quạt mượt khi vào màn
        if (stage && activeQuality !== 'lightweight') {
            stage.classList.add('is-dealing');
            layoutFanHand();
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    stage.classList.remove('is-dealing');
                    setTimeout(() => wraps.forEach((w) => { w.style.transitionDelay = '0ms'; }), 700);
                });
            });
        } else {
            layoutFanHand();
        }
        window.addEventListener('resize', layoutFanHand);

        function applyNeighborParting(hoverIdx) {
            if (window.innerWidth < 920 || activeQuality === 'lightweight') return;
            wraps.forEach((w, i) => {
                if (hoverIdx < 0) {
                    w.style.setProperty('--shift-x', '0px');
                } else if (i < hoverIdx) {
                    w.style.setProperty('--shift-x', `${Math.max(-22, -26 / (hoverIdx - i))}px`);
                } else if (i > hoverIdx) {
                    w.style.setProperty('--shift-x', `${Math.min(22, 26 / (i - hoverIdx))}px`);
                } else {
                    w.style.setProperty('--shift-x', '0px');
                }
            });
        }

        function openPalaceDrawer(idx) {
            const palace = palaceList[idx];
            if (!palace || !drawer) return;
            activeIndex = idx;
            const code = palace.code;
            const digest = ruleAnalysis?.palace_digests?.[code] || {};
            const rel = ruleAnalysis?.relations_by_palace?.[code] || {};
            const meta = palacesMeta?.[code] || {};

            const auxNames = (palace.aux_stars || []).map((a) => escapeHtml(a.name)).join(' · ') || 'Không có phụ tinh nổi bật';
            const tuHoaHtml = (palace.tu_hoa || []).map((h) => `<span class="mayhen_tuvi_tuhoa_pill">${escapeHtml(h.name)} (${escapeHtml(h.attached_star_name)})</span>`).join(' ');

            drawer.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                    <div>
                        <span class="mayhen_tuvi_badge">Lá ${idx + 1}/12 • ${escapeHtml(palace.can_chi)} • Đại Hạn ${palace.dai_han_start}–${palace.dai_han_end} tuổi${palace.is_than ? ' • CƯ THÂN' : ''}</span>
                        <h3 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; font-size:22px; margin:0; color:#85182A;">
                            ${escapeHtml(palace.name)} (${escapeHtml(palace.hanzi)}) — ${escapeHtml(meta.card_title || '')}
                        </h3>
                    </div>
                    <div class="mayhen_tuvi_btn_row">
                        <button type="button" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm" data-drawer-prev>&larr; Cung trước</button>
                        <button type="button" class="mayhen_tuvi_btn_primary mayhen_tuvi_btn_sm" data-drawer-next>Cung tiếp theo &rarr;</button>
                        <button type="button" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm" data-close-drawer>Đóng</button>
                    </div>
                </div>
                <p style="font-size:14px; margin:0 0 8px;"><strong>Chính tinh:</strong> ${escapeHtml(digest.stars_headline || '')} ${tuHoaHtml}</p>
                <p style="font-size:13px; color:#544A42; margin:0 0 12px;"><strong>Phụ tinh &amp; Vòng sao:</strong> ${auxNames} • Thái Tuế: <em>${escapeHtml(palace.ring_thai_tue)}</em> • Tràng Sinh: <em>${escapeHtml(palace.ring_trang_sinh)}</em>${palace.has_tuan ? ' • <strong>Tuần Không</strong>' : ''}${palace.has_triet ? ' • <strong>Triệt Lộ</strong>' : ''}</p>
                <div class="mayhen_tuvi_layers_grid" style="margin-bottom:10px;">
                    <div class="mayhen_tuvi_card" style="padding:14px 16px;">
                        <h4>Ý nghĩa ứng dụng hiện đại</h4>
                        <p>${escapeHtml(digest.modern_meaning || '')} ${escapeHtml(digest.lead_star_insight || '')}</p>
                    </div>
                    <div class="mayhen_tuvi_card" style="padding:14px 16px;">
                        <h4>Điểm phát huy &amp; Lưu tâm</h4>
                        <p><strong>Phát huy:</strong> ${escapeHtml(digest.strength_hint || '')}<br><strong>Lưu tâm:</strong> ${escapeHtml(digest.watch_out_hint || '')}</p>
                    </div>
                </div>
                <p style="font-size:12.5px; color:#6B5F52; margin:0 0 10px;">
                    <strong>Tam Phương Tứ Chính:</strong> Tam hợp với ${(rel.triad_palace_codes || []).map((c) => escapeHtml(chartData.palaces_by_code[c]?.name || c)).join(' & ')} • Xung chiếu: ${escapeHtml(chartData.palaces_by_code[rel.opposite_palace_code]?.name || '')}.
                    ${digest.vcd_note ? `<br><strong>Ghi chú Vô Chính Diệu:</strong> ${escapeHtml(digest.vcd_note)}` : ''}
                </p>
                ${document.getElementById('mayhenTuViEndpointsJson') ? `
                    <div style="display:flex; justify-content:flex-end;">
                        <button type="button"
                                class="mayhen_tuvi_ask_scope_btn"
                                data-tuvi-ask-trigger
                                data-scope-type="palace"
                                data-palace-code="${escapeHtml(code)}"
                                data-scope-label="${escapeHtml(palace.name)} (${escapeHtml(palace.can_chi)})">
                            ✦ Hỏi sâu AI về ${escapeHtml(palace.name)}
                        </button>
                    </div>
                ` : ''}
            `;
            drawer.classList.add('is-open');
            drawer.querySelector('[data-close-drawer]')?.addEventListener('click', () => {
                drawer.classList.remove('is-open');
                wraps.forEach((w) => w.classList.remove('is-selected', 'is-dimmed'));
            });
            drawer.querySelector('[data-drawer-prev]')?.addEventListener('click', () => activateCardByIndex((idx - 1 + wraps.length) % wraps.length));
            drawer.querySelector('[data-drawer-next]')?.addEventListener('click', () => activateCardByIndex((idx + 1) % wraps.length));
        }

        function activateCardByIndex(idx) {
            const wrap = wraps[idx];
            if (!wrap) return;
            const code = wrap.getAttribute('data-palace-code') || '';
            wrap.classList.add('is-flipped', 'is-selected');
            wraps.forEach((other, j) => {
                if (j !== idx) {
                    other.classList.remove('is-selected');
                    other.classList.add('is-dimmed');
                } else {
                    other.classList.remove('is-dimmed');
                }
            });
            exploredSet.add(code);
            const dot = wrap.querySelector(`[data-dot="${code}"]`);
            if (dot) dot.textContent = '✓';
            if (exploredCounter) exploredCounter.textContent = `${exploredSet.size}/12`;
            openPalaceDrawer(idx);
        }

        wraps.forEach((wrap, idx) => {
            wrap.addEventListener('mouseenter', () => applyNeighborParting(idx));
            wrap.addEventListener('mouseleave', () => applyNeighborParting(-1));
            wrap.addEventListener('mousemove', (e) => {
                if (activeQuality !== 'high') return;
                const r = wrap.getBoundingClientRect();
                wrap.style.setProperty('--mx', `${(((e.clientX - r.left) / r.width) * 100).toFixed(1)}%`);
                wrap.style.setProperty('--my', `${(((e.clientY - r.top) / r.height) * 100).toFixed(1)}%`);
            });
            wrap.addEventListener('click', () => activateCardByIndex(idx));
            wrap.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    activateCardByIndex(idx);
                }
            });
        });

        container.querySelector('[data-tuvi-deck-reveal-all]')?.addEventListener('click', () => {
            wraps.forEach((w, i) => {
                setTimeout(() => {
                    const code = w.getAttribute('data-palace-code') || '';
                    w.classList.add('is-flipped');
                    w.classList.remove('is-dimmed', 'is-selected');
                    exploredSet.add(code);
                    const dot = w.querySelector(`[data-dot="${code}"]`);
                    if (dot) dot.textContent = '✓';
                    if (exploredCounter) exploredCounter.textContent = `${exploredSet.size}/12`;
                }, activeQuality === 'lightweight' ? 0 : i * 55);
            });
            if (activeIndex < 0) openPalaceDrawer(0);
        });

        container.querySelector('[data-tuvi-deck-reset]')?.addEventListener('click', () => {
            wraps.forEach((w) => {
                w.classList.remove('is-flipped', 'is-selected', 'is-dimmed');
            });
            drawer?.classList.remove('is-open');
            activeIndex = -1;
        });

        // Trên trang chi tiết lá số thật: tự mở nhẹ Cung Mệnh sau khi xòe quạt xong để người mới biết ngay cách tương tác
        if (container.getAttribute('data-auto-open-menh') === '1' && wraps.length > 0) {
            setTimeout(() => activateCardByIndex(0), activeQuality === 'lightweight' ? 120 : 880);
        }
    }

    // =========================================================================
    // 4. THIÊN BÀN 4x4 TƯƠNG TÁC (HIGHLIGHT TAM PHƯƠNG TỨ CHÍNH + VẼ TIA SVG)
    // =========================================================================
    function initInteractiveBoard(container, chartData, ruleAnalysis) {
        if (!container || !chartData?.palaces_by_branch) return;
        const board = container.querySelector('.mayhen_tuvi_chart_board');
        const svg = container.querySelector('[data-tuvi-board-svg]');
        const cells = Array.from(container.querySelectorAll('.mayhen_tuvi_palace_cell'));
        const inspector = container.querySelector('[data-tuvi-board-inspector]');
        const popover = container.querySelector('[data-tuvi-star-popover]');
        const glossary = ruleAnalysis?.star_glossary || {};
        const ringGroupGlossary = ruleAnalysis?.ring_group_glossary || {};
        const starNatureGlossary = ruleAnalysis?.star_nature_glossary || {};
        const hasLiveAiEndpoints = Boolean(document.getElementById('mayhenTuViEndpointsJson'));
        if (!board || cells.length === 0) return;

        let activeBranch = Number(chartData.summary?.menh_branch_index ?? 2);

        function lookupStarEntry(rawName) {
            const name = String(rawName || '').trim();
            if (!name) return null;
            if (glossary[name]) return glossary[name];
            if (name === 'TUẦN' && glossary['Tuần Không']) return glossary['Tuần Không'];
            if (name === 'TRIỆT' && glossary['Triệt Lộ']) return glossary['Triệt Lộ'];
            return {
                name,
                hanzi: '',
                group_label: 'Sao Tử Vi Nam Phái',
                element: '',
                nature: 'trung',
                nature_label: starNatureGlossary.trung?.label || 'Trung tính',
                nature_description: starNatureGlossary.trung?.description || '',
                ring_group_description: '',
                meaning: `Sao ${name} góp phần tạo nên sắc thái riêng khi kết hợp cùng Chính tinh và Tam Phương Tứ Chính tại cung đang xem.`,
                advice: 'Bấm "✦ Hỏi AI chi tiết" để xem phân tích sâu cách sao này tác động lên lá số của bạn.',
            };
        }

        function collectPalaceAllStars(p) {
            if (!p) return [];
            const seen = new Set();
            const list = [];
            const push = (name, roleTag) => {
                const clean = String(name || '').trim();
                if (!clean || seen.has(clean)) return;
                seen.add(clean);
                list.push({ name: clean, roleTag });
            };
            (p.major_stars || []).forEach((s) => push(s.name, `Chính tinh (${s.brightness || 'B'})`));
            (p.tu_hoa || []).forEach((h) => push(h.name, `Tứ Hóa • ${h.attached_star_name || ''}`));
            (p.aux_stars || []).forEach((a) => push(a.name, a.nature === 'thach_thuc' ? 'Sát / Thách thức' : 'Cát / Trợ lực'));
            if (p.ring_thai_tue) push(p.ring_thai_tue, 'Vòng Thái Tuế');
            if (p.ring_bac_si) push(p.ring_bac_si, 'Vòng Bác Sĩ');
            if (p.ring_trang_sinh) push(p.ring_trang_sinh, 'Vòng Tràng Sinh');
            (p.luu_nien_stars || []).forEach((ln) => push(ln, 'Lưu Niên'));
            if (p.has_tuan) push('Tuần Không', 'Không Vong');
            if (p.has_triet) push('Triệt Lộ', 'Không Vong');
            return list;
        }

        function dispatchAskPalace(palaceObj, prefillQuestion = '') {
            if (!palaceObj) return;
            container.dispatchEvent(new CustomEvent('mayhen:tuvi-ask-palace', {
                bubbles: true,
                detail: {
                    palaceCode: palaceObj.code,
                    scopeLabel: `${palaceObj.name} (${palaceObj.can_chi})`,
                    prefillQuestion,
                },
            }));
        }

        function closeStarPopover() {
            if (!popover) return;
            popover.hidden = true;
            popover.innerHTML = '';
        }

        function openSingleStarPopover(starName, palaceObj) {
            if (!popover) return;
            const info = lookupStarEntry(starName);
            if (!info) return;
            const pName = palaceObj?.name || 'lá số';
            const prefill = `Sao ${info.name} trong ${pName} có ý nghĩa và tác động cụ thể ra sao đối với lá số của mình?`;
            const natureBadge = info.nature_label
                ? `<span class="mayhen_tuvi_nature_tag mayhen_tuvi_nature_tag--${escapeHtml(info.nature || 'trung')}">${escapeHtml(info.nature_label)}</span>`
                : '';
            const natureExplain = info.nature_description || info.advice || '';
            const ringExplain = info.ring_group_description || ringGroupGlossary[info.group_label] || '';

            popover.innerHTML = `
                <div class="mayhen_tuvi_star_popover_head">
                    <div>
                        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:6px;">
                            <span class="mayhen_tuvi_badge" style="margin:0;">${escapeHtml(info.group_label)}${info.element ? ` • Hành ${escapeHtml(info.element)}` : ''} • Tại ${escapeHtml(pName)}</span>
                            ${natureBadge}
                        </div>
                        <h4 style="margin:6px 0 0; font-size:18px; color:#85182A; font-family:'MayhenV3EditorialSerif', Georgia, serif;">
                            ✦ Sao ${escapeHtml(info.name)} ${info.hanzi ? `<small style="color:#6B5F52;">(${escapeHtml(info.hanzi)})</small>` : ''}
                        </h4>
                    </div>
                    <button type="button" class="mayhen_tuvi_star_popover_close" data-tuvi-popover-close aria-label="Đóng giải mã sao">&times;</button>
                </div>
                <p style="margin:0 0 8px; font-size:14px; line-height:1.65; color:#2B221B;"><strong>Ý nghĩa sao:</strong> ${escapeHtml(info.meaning)}</p>
                ${natureExplain ? `
                    <p style="margin:0 0 8px; font-size:13.2px; line-height:1.6; color:#544A42;">
                        <strong>Tính chất (${escapeHtml(info.nature_label || 'Trung tính')}):</strong> ${escapeHtml(natureExplain)}
                    </p>
                ` : ''}
                ${ringExplain ? `
                    <div class="mayhen_tuvi_ring_group_box">
                        <strong>Về ${escapeHtml(info.group_label)}:</strong> ${escapeHtml(ringExplain)}
                    </div>
                ` : ''}
                <div class="mayhen_tuvi_btn_row" style="justify-content:space-between; margin-top:10px;">
                    <button type="button" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm" data-popover-all-stars>
                        Xem tất cả sao trong ${escapeHtml(pName)}
                    </button>
                    ${hasLiveAiEndpoints ? `
                        <button type="button" class="mayhen_tuvi_btn_primary mayhen_tuvi_btn_sm" data-popover-ask-ai>
                            ✦ Hỏi AI chi tiết về ${escapeHtml(info.name)}
                        </button>
                    ` : `
                        <button type="button" class="mayhen_tuvi_btn_primary mayhen_tuvi_btn_sm" data-popover-demo-login>
                            ✦ Lập lá số của bạn để hỏi AI
                        </button>
                    `}
                </div>
            `;
            popover.hidden = false;
            popover.querySelector('[data-tuvi-popover-close]')?.addEventListener('click', closeStarPopover);
            popover.querySelector('[data-popover-all-stars]')?.addEventListener('click', () => openPalaceGlossaryPopover(palaceObj));
            popover.querySelector('[data-popover-ask-ai]')?.addEventListener('click', () => {
                closeStarPopover();
                dispatchAskPalace(palaceObj, prefill);
            });
            popover.querySelector('[data-popover-demo-login]')?.addEventListener('click', () => {
                closeStarPopover();
                dispatchAskPalace(palaceObj, '');
            });
            popover.scrollIntoView({ behavior: activeQuality === 'lightweight' ? 'auto' : 'smooth', block: 'nearest' });
        }

        function openPalaceGlossaryPopover(palaceObj) {
            if (!popover || !palaceObj) return;
            const stars = collectPalaceAllStars(palaceObj);
            const ringEntries = Object.entries(ringGroupGlossary);
            const natureEntries = Object.values(starNatureGlossary);

            popover.innerHTML = `
                <div class="mayhen_tuvi_star_popover_head">
                    <div>
                        <span class="mayhen_tuvi_badge">Từ Điển Tức Thì • ${escapeHtml(palaceObj.name)} (${escapeHtml(palaceObj.can_chi)})</span>
                        <h4 style="margin:6px 0 0; font-size:18px; color:#85182A; font-family:'MayhenV3EditorialSerif', Georgia, serif;">
                            Giải Mã Toàn Bộ ${stars.length} Sao &amp; Vòng Sao Trong ${escapeHtml(palaceObj.name)}
                        </h4>
                    </div>
                    <button type="button" class="mayhen_tuvi_star_popover_close" data-tuvi-popover-close aria-label="Đóng từ điển sao">&times;</button>
                </div>
                <div class="mayhen_tuvi_star_glossary_grid">
                    ${stars.map((st) => {
                        const info = lookupStarEntry(st.name);
                        const natClass = escapeHtml(info.nature || 'trung');
                        const natLabel = info.nature_label || st.roleTag;
                        return `
                            <div class="mayhen_tuvi_star_glossary_item">
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:4px;">
                                    <strong style="color:#85182A;">${escapeHtml(info.name)}</strong>
                                    <span class="mayhen_tuvi_nature_tag mayhen_tuvi_nature_tag--${natClass}" title="${escapeHtml(info.nature_description || '')}">${escapeHtml(natLabel)}</span>
                                </div>
                                <div style="font-size:11.5px; color:#6B5F52; font-weight:700; margin-bottom:4px;">${escapeHtml(info.group_label)}${info.element ? ` • Hành ${escapeHtml(info.element)}` : ''}</div>
                                <div style="margin-bottom:6px;">${escapeHtml(info.meaning)}</div>
                                ${hasLiveAiEndpoints ? `
                                    <button type="button"
                                            class="mayhen_tuvi_ask_scope_btn"
                                            style="padding:3px 9px; font-size:11px;"
                                            data-glossary-ask-star="${escapeHtml(info.name)}">
                                        ✦ Hỏi AI sao này
                                    </button>
                                ` : ''}
                            </div>
                        `;
                    }).join('')}
                </div>
                ${(ringEntries.length > 0 || natureEntries.length > 0) ? `
                    <div class="mayhen_tuvi_glossary_legend_section">
                        ${natureEntries.length > 0 ? `
                            <div style="margin-bottom:10px;">
                                <strong style="font-size:12.5px; color:#85182A; display:block; margin-bottom:5px;">Ý nghĩa phân loại Cát tinh • Trung tính • Thử thách:</strong>
                                <div class="mayhen_tuvi_glossary_legend_grid">
                                    ${Object.entries(starNatureGlossary).map(([nKey, nVal]) => `
                                        <div class="mayhen_tuvi_glossary_legend_card">
                                            <span class="mayhen_tuvi_nature_tag mayhen_tuvi_nature_tag--${escapeHtml(nKey)}">${escapeHtml(nVal.label)}</span>
                                            <p style="margin:5px 0 0;">${escapeHtml(nVal.description)}</p>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}
                        ${ringEntries.length > 0 ? `
                            <div>
                                <strong style="font-size:12.5px; color:#1E4D3B; display:block; margin-bottom:5px;">Giải thích ý nghĩa 4 nhóm Vòng Sao &amp; Lưu Niên:</strong>
                                <div class="mayhen_tuvi_glossary_legend_grid">
                                    ${ringEntries.map(([rKey, rDesc]) => `
                                        <div class="mayhen_tuvi_glossary_legend_card">
                                            <strong style="color:#1E4D3B; font-size:12px;">${escapeHtml(rKey)}</strong>
                                            <p style="margin:4px 0 0;">${escapeHtml(rDesc)}</p>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                ` : ''}
            `;
            popover.hidden = false;
            popover.querySelector('[data-tuvi-popover-close]')?.addEventListener('click', closeStarPopover);
            popover.querySelectorAll('[data-glossary-ask-star]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const star = btn.getAttribute('data-glossary-ask-star') || '';
                    closeStarPopover();
                    dispatchAskPalace(palaceObj, `Sao ${star} trong ${palaceObj.name} có ý nghĩa và tác động cụ thể ra sao với mình?`);
                });
            });
            popover.scrollIntoView({ behavior: activeQuality === 'lightweight' ? 'auto' : 'smooth', block: 'nearest' });
        }

        function getInnerAnchorPoint(cell, boardRect) {
            const r = cell.getBoundingClientRect();
            const bx = boardRect.left;
            const by = boardRect.top;
            const bw = boardRect.width;
            const bh = boardRect.height;
            const cx = r.left - bx + r.width / 2;
            const cy = r.top - by + r.height / 2;

            // Neo điểm ở cạnh/góc hướng về tâm Thiên Bàn 2x2 để đường nối gọn đẹp
            const x = cx < bw * 0.3 ? (r.right - bx - 4) : (cx > bw * 0.7 ? (r.left - bx + 4) : cx);
            const y = cy < bh * 0.3 ? (r.bottom - by - 4) : (cy > bh * 0.7 ? (r.top - by + 4) : cy);
            return { x, y };
        }

        function drawTamPhuongLines(b) {
            if (!svg || !board) return;
            const boardRect = board.getBoundingClientRect();
            if (boardRect.width < 680 || boardRect.height < 200) {
                svg.innerHTML = '';
                return;
            }
            const triadA = (b + 4) % 12;
            const triadB = (b + 8) % 12;
            const opp = (b + 6) % 12;

            const cellMap = new Map();
            cells.forEach((c) => cellMap.set(Number(c.getAttribute('data-branch-idx')), c));

            const cMain = cellMap.get(b);
            const cTriadA = cellMap.get(triadA);
            const cTriadB = cellMap.get(triadB);
            const cOpp = cellMap.get(opp);
            if (!cMain || !cTriadA || !cTriadB || !cOpp) {
                svg.innerHTML = '';
                return;
            }

            const pMain = getInnerAnchorPoint(cMain, boardRect);
            const pA = getInnerAnchorPoint(cTriadA, boardRect);
            const pB = getInnerAnchorPoint(cTriadB, boardRect);
            const pOpp = getInnerAnchorPoint(cOpp, boardRect);

            svg.setAttribute('viewBox', `0 0 ${boardRect.width.toFixed(1)} ${boardRect.height.toFixed(1)}`);
            svg.innerHTML = `
                <polygon points="${pMain.x.toFixed(1)},${pMain.y.toFixed(1)} ${pA.x.toFixed(1)},${pA.y.toFixed(1)} ${pB.x.toFixed(1)},${pB.y.toFixed(1)}"
                         fill="rgba(30, 77, 59, 0.045)"
                         stroke="rgba(30, 77, 59, 0.52)"
                         stroke-width="1.6" />
                <line x1="${pMain.x.toFixed(1)}" y1="${pMain.y.toFixed(1)}"
                      x2="${pOpp.x.toFixed(1)}" y2="${pOpp.y.toFixed(1)}"
                      stroke="rgba(181, 107, 39, 0.72)"
                      stroke-width="1.7"
                      stroke-dasharray="6 4" />
                <circle cx="${pMain.x.toFixed(1)}" cy="${pMain.y.toFixed(1)}" r="4.5" fill="#85182A" />
                <circle cx="${pA.x.toFixed(1)}" cy="${pA.y.toFixed(1)}" r="3.5" fill="#1E4D3B" />
                <circle cx="${pB.x.toFixed(1)}" cy="${pB.y.toFixed(1)}" r="3.5" fill="#1E4D3B" />
                <circle cx="${pOpp.x.toFixed(1)}" cy="${pOpp.y.toFixed(1)}" r="3.8" fill="#B56B27" />
            `;
        }

        function selectBranch(branchIdx) {
            const b = Number(branchIdx);
            activeBranch = b;
            const triadA = (b + 4) % 12;
            const triadB = (b + 8) % 12;
            const opp = (b + 6) % 12;

            cells.forEach((cell) => {
                const cb = Number(cell.getAttribute('data-branch-idx'));
                cell.classList.toggle('is-selected', cb === b);
                cell.classList.toggle('is-triad', cb === triadA || cb === triadB);
                cell.classList.toggle('is-opposite', cb === opp);
            });

            requestAnimationFrame(() => drawTamPhuongLines(b));

            const p = chartData.palaces_by_branch[b];
            const digest = ruleAnalysis?.palace_digests?.[p?.code] || {};
            if (p && inspector) {
                const tuHoaText = (p.tu_hoa || []).map((h) => `${h.name} (${h.attached_star_name})`).join(', ');
                const catNames = (p.aux_stars || []).filter((a) => a.nature !== 'thach_thuc').map((a) => a.name).join(' · ');
                const satNames = (p.aux_stars || []).filter((a) => a.nature === 'thach_thuc').map((a) => a.name).join(' · ');
                const luuNienText = (p.luu_nien_stars || []).join(', ');
                const ringsText = [
                    p.ring_thai_tue ? `Thái Tuế: ${p.ring_thai_tue}` : '',
                    p.ring_bac_si ? `Bác Sĩ: ${p.ring_bac_si}` : '',
                    p.ring_trang_sinh ? `Tràng Sinh: ${p.ring_trang_sinh}` : '',
                    p.has_tuan ? 'Tuần Không' : '',
                    p.has_triet ? 'Triệt Lộ' : '',
                ].filter(Boolean).join(' • ');
                const allStars = collectPalaceAllStars(p);

                inspector.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; margin-bottom:8px;">
                        <div>
                            <strong style="color:#85182A; font-size:16px;">${escapeHtml(p.name)} (${escapeHtml(p.can_chi)})${p.is_than ? ' • CƯ THÂN' : ''} — Đại Hạn ${p.dai_han_start}–${p.dai_han_end} tuổi</strong>
                            <div style="font-size:12px; color:#1E4D3B; font-weight:700; margin-top:2px;">Tam hợp: ${escapeHtml(chartData.palaces_by_branch[triadA]?.name)} &amp; ${escapeHtml(chartData.palaces_by_branch[triadB]?.name)} • Xung chiếu: ${escapeHtml(chartData.palaces_by_branch[opp]?.name)}</div>
                        </div>
                        <div class="mayhen_tuvi_btn_row" style="gap:6px;">
                            <button type="button" class="mayhen_tuvi_ask_scope_btn" data-inspector-all-glossary>
                                ? Giải mã đủ ${allStars.length} sao
                            </button>
                            ${hasLiveAiEndpoints && p.code ? `
                                <button type="button"
                                        class="mayhen_tuvi_ask_scope_btn"
                                        style="background:#85182A; border-color:#85182A; color:#FFFDF9;"
                                        data-tuvi-ask-trigger
                                        data-scope-type="palace"
                                        data-palace-code="${escapeHtml(p.code)}"
                                        data-scope-label="${escapeHtml(p.name)} (${escapeHtml(p.can_chi)})">
                                    ✦ Hỏi sâu AI về ${escapeHtml(p.name)}
                                </button>
                            ` : ''}
                        </div>
                    </div>
                    <p style="margin:0 0 6px; font-size:13.5px;"><strong>Chính tinh:</strong> ${escapeHtml(digest.stars_headline || ((p.major_stars || []).map((s) => `${s.name} (${s.brightness})`).join(', ') || 'Vô Chính Diệu'))}${tuHoaText ? ` • <strong>Tứ Hóa:</strong> ${escapeHtml(tuHoaText)}` : ''}</p>
                    <p style="margin:0 0 6px; font-size:12.8px; color:#544A42;">
                        ${catNames ? `<strong style="color:#1E4D3B;">Cát/Quý tinh:</strong> ${escapeHtml(catNames)}` : ''}
                        ${satNames ? `${catNames ? ' • ' : ''}<strong style="color:#A12328;">Sát/Thách thức:</strong> ${escapeHtml(satNames)}` : ''}
                        ${luuNienText ? ` • <strong style="color:#7A3E12;">Lưu Niên:</strong> ${escapeHtml(luuNienText)}` : ''}
                    </p>
                    <p style="margin:0 0 8px; font-size:12.5px; color:#6B5F52;"><strong>Vòng sao &amp; Không Vong:</strong> ${escapeHtml(ringsText)}</p>
                    <div class="mayhen_tuvi_inspector_chips" aria-label="Bấm từng sao để xem giải thích">
                        ${allStars.map((st) => `
                            <button type="button" class="mayhen_tuvi_inspector_chip" data-tuvi-inspector-star="${escapeHtml(st.name)}">
                                ${escapeHtml(st.name)} <span aria-hidden="true">?</span>
                            </button>
                        `).join('')}
                    </div>
                    <div class="mayhen_tuvi_inspector_star_detail" data-tuvi-inspector-star-detail hidden></div>
                    <p style="margin:0; font-size:13.5px; color:#3E362F;">${escapeHtml(digest.lead_star_insight || '')} ${escapeHtml(digest.modern_meaning || '')}</p>
                `;

                const detailBox = inspector.querySelector('[data-tuvi-inspector-star-detail]');
                const chips = Array.from(inspector.querySelectorAll('[data-tuvi-inspector-star]'));
                chips.forEach((chip) => {
                    chip.addEventListener('click', () => {
                        const starName = chip.getAttribute('data-tuvi-inspector-star') || '';
                        const info = lookupStarEntry(starName);
                        const wasActive = chip.classList.contains('is-active');
                        chips.forEach((c) => c.classList.remove('is-active'));
                        if (wasActive && detailBox) {
                            detailBox.hidden = true;
                            detailBox.innerHTML = '';
                            return;
                        }
                        chip.classList.add('is-active');
                        if (detailBox && info) {
                            const natExplain = info.nature_description || info.advice || '';
                            const ringExplain = info.ring_group_description || ringGroupGlossary[info.group_label] || '';
                            detailBox.innerHTML = `
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:4px;">
                                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                        <strong style="color:#85182A;">✦ ${escapeHtml(info.name)} (${escapeHtml(info.group_label)}${info.element ? ` • Hành ${escapeHtml(info.element)}` : ''})</strong>
                                        ${info.nature_label ? `<span class="mayhen_tuvi_nature_tag mayhen_tuvi_nature_tag--${escapeHtml(info.nature || 'trung')}">${escapeHtml(info.nature_label)}</span>` : ''}
                                    </div>
                                    ${hasLiveAiEndpoints ? `
                                        <button type="button" class="mayhen_tuvi_ask_scope_btn" style="padding:3px 10px; font-size:11.5px;" data-detail-ask-star="${escapeHtml(info.name)}">
                                            ✦ Hỏi AI về ${escapeHtml(info.name)} tại ${escapeHtml(p.name)}
                                        </button>
                                    ` : ''}
                                </div>
                                <div>${escapeHtml(info.meaning)}</div>
                                ${natExplain ? `<div style="margin-top:4px; font-size:12.3px; color:#544A42;"><strong>Tính chất (${escapeHtml(info.nature_label || 'Trung tính')}):</strong> ${escapeHtml(natExplain)}</div>` : ''}
                                ${ringExplain ? `<div style="margin-top:4px; font-size:12.3px; color:#1E4D3B;"><strong>Về ${escapeHtml(info.group_label)}:</strong> ${escapeHtml(ringExplain)}</div>` : ''}
                            `;
                            detailBox.hidden = false;
                            detailBox.querySelector('[data-detail-ask-star]')?.addEventListener('click', () => {
                                dispatchAskPalace(p, `Sao ${info.name} trong ${p.name} có ý nghĩa và tác động cụ thể ra sao đối với mình?`);
                            });
                        }
                    });
                });
                inspector.querySelector('[data-inspector-all-glossary]')?.addEventListener('click', () => openPalaceGlossaryPopover(p));
            }
        }

        cells.forEach((cell) => {
            cell.addEventListener('click', (e) => {
                const branch = Number(cell.getAttribute('data-branch-idx'));
                const palaceObj = chartData.palaces_by_branch[branch];

                const glossaryBtn = e.target.closest('[data-tuvi-cell-glossary]');
                if (glossaryBtn) {
                    e.stopPropagation();
                    selectBranch(branch);
                    openPalaceGlossaryPopover(palaceObj);
                    return;
                }

                const askBtn = e.target.closest('[data-tuvi-cell-ask]');
                if (askBtn) {
                    e.stopPropagation();
                    selectBranch(branch);
                    dispatchAskPalace(palaceObj, '');
                    return;
                }

                const starEl = e.target.closest('[data-tuvi-star-lookup]');
                if (starEl) {
                    e.stopPropagation();
                    selectBranch(branch);
                    openSingleStarPopover(starEl.getAttribute('data-tuvi-star-lookup'), palaceObj);
                    return;
                }

                selectBranch(branch);
            });

            cell.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    if (e.target === cell) {
                        e.preventDefault();
                        selectBranch(cell.getAttribute('data-branch-idx'));
                    } else if (e.target.hasAttribute('data-tuvi-star-lookup')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const branch = Number(cell.getAttribute('data-branch-idx'));
                        selectBranch(branch);
                        openSingleStarPopover(e.target.getAttribute('data-tuvi-star-lookup'), chartData.palaces_by_branch[branch]);
                    }
                }
            });
        });
        window.addEventListener('resize', () => drawTamPhuongLines(activeBranch));
        selectBranch(activeBranch);

        return {
            redraw: () => drawTamPhuongLines(activeBranch),
            selectBranch,
        };
    }

    // =========================================================================
    // 5. XUẤT ẢNH LÁ SỐ TỬ VI HIDPI PNG (CHUẨN FONT TIẾNG VIỆT, 1800x1350)
    // =========================================================================
    async function exportTuViChartPng(chartData, hideBirthInfo) {
        if (!chartData?.palaces_by_branch) return;
        if (document.fonts?.ready) {
            try { await document.fonts.ready; } catch {}
        }

        const canvas = document.createElement('canvas');
        const W = 1800;
        const H = 1350;
        canvas.width = W;
        canvas.height = H;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const SERIF = '"MayhenV3EditorialSerif", "Times New Roman", serif';
        const SANS = '"Segoe UI", Arial, sans-serif';

        const drawPill = (x, y, text, bg, fg, align = 'left') => {
            ctx.font = `700 12.5px ${SANS}`;
            const tw = ctx.measureText(text).width;
            const pw = tw + 12;
            const ph = 20;
            const px = align === 'right' ? (x - pw) : (align === 'center' ? (x - pw / 2) : x);
            ctx.fillStyle = bg;
            ctx.beginPath();
            ctx.roundRect ? ctx.roundRect(px, y - 14, pw, ph, 5) : ctx.fillRect(px, y - 14, pw, ph);
            ctx.fill();
            ctx.fillStyle = fg;
            ctx.textAlign = 'center';
            ctx.fillText(text, px + pw / 2, y);
            return pw;
        };

        // Nền giấy cổ điển + khung kép vàng đồng
        ctx.fillStyle = '#F6EFE2';
        ctx.fillRect(0, 0, W, H);
        ctx.strokeStyle = '#CFA860';
        ctx.lineWidth = 4;
        ctx.strokeRect(18, 18, W - 36, H - 36);
        ctx.strokeStyle = 'rgba(207, 168, 96, 0.5)';
        ctx.lineWidth = 1.5;
        ctx.strokeRect(26, 26, W - 52, H - 52);

        const gridPos = {
            5: [0, 0], 6: [1, 0], 7: [2, 0], 8: [3, 0],
            4: [0, 1], 9: [3, 1],
            3: [0, 2], 10: [3, 2],
            2: [0, 3], 1: [1, 3], 0: [2, 3], 11: [3, 3],
        };
        const pad = 36;
        const gap = 12;
        const cw = (W - pad * 2 - gap * 3) / 4;
        const ch = (H - pad * 2 - gap * 3) / 4;

        const elementColors = {
            Kim: '#5A646E',
            Mộc: '#196844',
            Thủy: '#1B4D89',
            Hỏa: '#B4232C',
            Thổ: '#9A6316',
        };
        const tuHoaColors = {
            hoa_loc: ['#175C41', '#F5FBF8'],
            hoa_quyen: ['#9B1E2E', '#FFF7F5'],
            hoa_khoa: ['#1B4D89', '#F2F7FD'],
            hoa_ky: ['#2C2229', '#F4E2B8'],
        };

        chartData.palaces_by_branch.forEach((p) => {
            const [col, row] = gridPos[p.branch_index] || [0, 0];
            const x = pad + col * (cw + gap);
            const y = pad + row * (ch + gap);

            // Khung ô cung
            ctx.fillStyle = p.is_menh ? '#FFFBF4' : '#FFFFFF';
            ctx.beginPath();
            ctx.roundRect ? ctx.roundRect(x, y, cw, ch, 12) : ctx.fillRect(x, y, cw, ch);
            ctx.fill();
            ctx.strokeStyle = p.is_menh ? '#85182A' : '#DFD2BE';
            ctx.lineWidth = p.is_menh ? 2.8 : 1.5;
            ctx.stroke();

            // Dải header cung
            ctx.fillStyle = p.is_menh ? '#F9EDE6' : '#FAF5EB';
            ctx.fillRect(x + 2, y + 2, cw - 4, 36);
            ctx.strokeStyle = '#EFE5D5';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(x + 2, y + 38);
            ctx.lineTo(x + cw - 2, y + 38);
            ctx.stroke();

            ctx.textAlign = 'left';
            ctx.fillStyle = '#6B5F52';
            ctx.font = `700 13.5px ${SANS}`;
            ctx.fillText(p.can_chi || '', x + 12, y + 25);

            ctx.textAlign = 'center';
            ctx.fillStyle = '#85182A';
            ctx.font = `800 16px ${SANS}`;
            const palaceTitle = (p.short_name || '').toUpperCase();
            ctx.fillText(palaceTitle + (p.is_than ? ' · THÂN' : ''), x + cw / 2, y + 25);

            ctx.textAlign = 'right';
            ctx.fillStyle = '#1F1915';
            ctx.font = `800 14px ${SANS}`;
            ctx.fillText(`${p.dai_han_start ?? ''}`, x + cw - 12, y + 25);

            // 14 Chính Tinh (màu Ngũ Hành, cỡ chữ nổi bật)
            let cy = y + 66;
            ctx.textAlign = 'center';
            ctx.font = `700 19.5px ${SERIF}`;
            if ((p.major_stars || []).length > 0) {
                p.major_stars.forEach((s) => {
                    ctx.fillStyle = elementColors[s.element] || '#1F1915';
                    ctx.fillText(`${s.name} (${s.brightness})`, x + cw / 2, cy);
                    cy += 24;
                });
            } else {
                ctx.fillStyle = '#7A6E62';
                ctx.font = `italic 600 17px ${SERIF}`;
                ctx.fillText('Vô Chính Diệu', x + cw / 2, cy);
                cy += 24;
            }

            // Đường kẻ chấm mảnh phân tách Chính Tinh & Phụ Tinh
            ctx.strokeStyle = '#EFE5D5';
            ctx.beginPath();
            ctx.moveTo(x + 14, cy - 8);
            ctx.lineTo(x + cw - 14, cy - 8);
            ctx.stroke();

            // 2 cột phụ tinh (Trái: Tứ Hóa Cát, Cát tinh, Bác Sĩ | Phải: Hóa Kỵ, Sát tinh, Thái Tuế, Lưu Niên)
            let ly = cy + 12;
            (p.tu_hoa || []).filter((h) => h.code !== 'hoa_ky').forEach((h) => {
                const [bg, fg] = tuHoaColors[h.code] || ['#175C41', '#FFFFFF'];
                drawPill(x + 12, ly, h.name, bg, fg, 'left');
                ly += 22;
            });
            const leftAux = [
                ...(p.aux_stars || []).filter((a) => a.nature !== 'thach_thuc').map((a) => ({ text: a.name, color: elementColors[a.element] || '#1E4D3B' })),
                ...(p.ring_bac_si ? [{ text: p.ring_bac_si, color: '#5C5044' }] : []),
            ];
            ctx.font = `600 13.5px ${SANS}`;
            ctx.textAlign = 'left';
            leftAux.slice(0, 7).forEach((item) => {
                if (ly < y + ch - 42) {
                    ctx.fillStyle = item.color;
                    ctx.fillText(item.text, x + 12, ly);
                    ly += 18.5;
                }
            });

            let ry = cy + 12;
            (p.tu_hoa || []).filter((h) => h.code === 'hoa_ky').forEach((h) => {
                const [bg, fg] = tuHoaColors.hoa_ky;
                drawPill(x + cw - 12, ry, h.name, bg, fg, 'right');
                ry += 22;
            });
            const rightAux = [
                ...(p.aux_stars || []).filter((a) => a.nature === 'thach_thuc').map((a) => ({ text: a.name, color: '#A12328', bold: true })),
                ...(p.ring_thai_tue ? [{ text: p.ring_thai_tue, color: '#6B5F52', bold: false }] : []),
                ...(p.luu_nien_stars || []).slice(0, 2).map((s) => ({ text: s, color: '#7A3E12', bold: false })),
            ];
            ctx.textAlign = 'right';
            rightAux.slice(0, 7).forEach((item) => {
                if (ry < y + ch - 42) {
                    ctx.font = `${item.bold ? '700' : '600'} 13.5px ${SANS}`;
                    ctx.fillStyle = item.color;
                    ctx.fillText(item.text, x + cw - 12, ry);
                    ry += 18.5;
                }
            });

            // Chân cung: Tiểu Hạn / Tràng Sinh / Tuần-Triệt
            ctx.strokeStyle = '#E5D7C2';
            ctx.beginPath();
            ctx.moveTo(x + 10, y + ch - 34);
            ctx.lineTo(x + cw - 10, y + ch - 34);
            ctx.stroke();

            ctx.textAlign = 'left';
            ctx.fillStyle = '#6B5F52';
            ctx.font = `700 13px ${SANS}`;
            ctx.fillText(`ĐH ${p.dai_han_start}–${p.dai_han_end}`, x + 12, y + ch - 13);

            ctx.textAlign = 'center';
            ctx.fillStyle = '#3D352E';
            ctx.font = `700 13.5px ${SANS}`;
            ctx.fillText(p.ring_trang_sinh || '', x + cw / 2, y + ch - 13);

            let badgeX = x + cw - 12;
            if (p.has_triet) {
                const pw = drawPill(badgeX, y + ch - 13, 'TRIỆT', '#7A1A26', '#FFF4E0', 'right');
                badgeX -= (pw + 5);
            }
            if (p.has_tuan) {
                drawPill(badgeX, y + ch - 13, 'TUẦN', '#2B231E', '#F4E2B8', 'right');
            }
        });

        // Ô trung tâm Thiên Bàn 2x2
        const cx = pad + cw + gap;
        const cy = pad + ch + gap;
        const cW = cw * 2 + gap;
        const cH = ch * 2 + gap;
        const cGrad = ctx.createRadialGradient(cx + cW / 2, cy + cH * 0.25, 20, cx + cW / 2, cy + cH / 2, cW * 0.7);
        cGrad.addColorStop(0, '#FFFFFF');
        cGrad.addColorStop(1, '#FAF4E8');
        ctx.fillStyle = cGrad;
        ctx.beginPath();
        ctx.roundRect ? ctx.roundRect(cx, cy, cW, cH, 16) : ctx.fillRect(cx, cy, cW, cH);
        ctx.fill();
        ctx.strokeStyle = '#CFA860';
        ctx.lineWidth = 2.8;
        ctx.stroke();
        ctx.strokeStyle = 'rgba(207, 168, 96, 0.4)';
        ctx.lineWidth = 1.2;
        ctx.strokeRect(cx + 10, cy + 10, cW - 20, cH - 20);

        ctx.textAlign = 'center';
        drawPill(cx + cW / 2, cy + 52, 'LÁ SỐ TỬ VI ĐẨU SỐ · NAM PHÁI VIỆT NAM', '#142220', '#EBC984', 'center');

        ctx.fillStyle = '#85182A';
        ctx.font = `700 34px ${SERIF}`;
        ctx.fillText(hideBirthInfo ? 'Lá Số Ẩn Danh' : (chartData.nickname || 'Lá Số Thiên Mệnh'), cx + cW / 2, cy + 104);

        ctx.fillStyle = '#6B5F52';
        ctx.font = `600 15px ${SANS}`;
        ctx.fillText(chartData.school_name || 'Nam Phái Việt Nam (Tử Vi Đẩu Số Tân Biên)', cx + cW / 2, cy + 132);

        // Khung bảng thông tin 2 cột sắc nét
        const boxX = cx + 34;
        const boxY = cy + 154;
        const boxW = cW - 68;
        const boxH = cH - 252;
        ctx.fillStyle = 'rgba(255, 255, 255, 0.92)';
        ctx.beginPath();
        ctx.roundRect ? ctx.roundRect(boxX, boxY, boxW, boxH, 12) : ctx.fillRect(boxX, boxY, boxW, boxH);
        ctx.fill();
        ctx.strokeStyle = '#E4D5BE';
        ctx.lineWidth = 1.2;
        ctx.stroke();

        const sum = chartData.summary || {};
        const birth = chartData.birth || {};
        const rows = [
            ['Dương lịch', hideBirthInfo ? 'Đã ẩn riêng tư' : (birth.solar?.label || ''), 'Âm lịch', hideBirthInfo ? 'Đã ẩn riêng tư' : (birth.lunar?.label || '')],
            ['Âm Dương', chartData.yin_yang_gender_label || '', 'Thuận / Nghịch', sum.am_duong_ly || ''],
            ['Bản Mệnh', `${sum.nap_am?.name || ''} (${sum.nap_am?.element || ''})`, 'Ngũ Hành Cục', sum.cuc?.name || ''],
            ['Cục – Mệnh', sum.cuc_menh_relation || '', 'Lai Nhân Cung', sum.lai_nhan_palace_name || ''],
            ['Chủ Mệnh', sum.chu_menh || '', 'Chủ Thân', sum.chu_than || ''],
            ['Mệnh tại', sum.menh_branch_name || '', 'Thân cư', sum.than_palace_name || ''],
            ['Cân Lượng', sum.can_luong?.label || '—', 'Năm xem hạn', `${chartData.viewed_year || ''} (${sum.current_luu_nien?.can_chi || ''})`],
            ['Tuần Không', (sum.tuan_branches || []).join(' – '), 'Triệt Lộ', (sum.triet_branches || []).join(' – ')],
        ];

        const colHalf = boxW / 2;
        const rowH = Math.floor((boxH - 24) / rows.length);
        ctx.textAlign = 'left';
        rows.forEach(([k1, v1, k2, v2], i) => {
            const ry = boxY + 30 + i * rowH;
            if (i > 0) {
                ctx.strokeStyle = '#F2E9DA';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(boxX + 16, ry - rowH + 10);
                ctx.lineTo(boxX + boxW - 16, ry - rowH + 10);
                ctx.stroke();
            }
            ctx.font = `700 14px ${SANS}`;
            ctx.fillStyle = '#85182A';
            ctx.fillText(`${k1}:`, boxX + 20, ry);
            ctx.font = `600 14.5px ${SANS}`;
            ctx.fillStyle = '#1F1915';
            ctx.fillText(String(v1).slice(0, 32), boxX + 112, ry);

            ctx.font = `700 14px ${SANS}`;
            ctx.fillStyle = '#85182A';
            ctx.fillText(`${k2}:`, boxX + colHalf + 12, ry);
            ctx.font = `600 14.5px ${SANS}`;
            ctx.fillStyle = '#1F1915';
            ctx.fillText(String(v2).slice(0, 32), boxX + colHalf + 124, ry);
        });

        // Chú thích 5 màu Ngũ Hành + chân trang thương hiệu
        const legendY = cy + cH - 58;
        const els = [['● Kim', '#5A646E'], ['● Mộc', '#196844'], ['● Thủy', '#1B4D89'], ['● Hỏa', '#B4232C'], ['● Thổ', '#9A6316']];
        ctx.font = `700 14px ${SANS}`;
        ctx.textAlign = 'center';
        els.forEach(([lbl, col], idx) => {
            ctx.fillStyle = col;
            ctx.fillText(lbl, cx + cW / 2 + (idx - 2) * 96, legendY);
        });

        ctx.fillStyle = '#7A6E60';
        ctx.font = `600 14.5px ${SANS}`;
        ctx.fillText('mayhen.vn/tu-vi  ·  Hiểu mình, hiểu người, vun đắp nhân duyên', cx + cW / 2, cy + cH - 26);

        const link = document.createElement('a');
        link.download = `la-so-tu-vi-mayhen-${Date.now()}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    // =========================================================================
    // 6. KẾT NỐI TRANG CHI TIẾT LÁ SỐ (/tu-vi/la-so/{slug})
    // =========================================================================
    const showApp = document.getElementById('mayhenTuViShowApp');
    if (showApp) {
        const chartJson = JSON.parse(document.getElementById('mayhenTuViChartJson')?.textContent || '{}');
        const ruleJson = JSON.parse(document.getElementById('mayhenTuViRuleJson')?.textContent || '{}');
        const palacesJson = JSON.parse(document.getElementById('mayhenTuViPalacesJson')?.textContent || '{}');
        const endpoints = JSON.parse(document.getElementById('mayhenTuViEndpointsJson')?.textContent || '{}');

        initTwelveCardsDeck(showApp, chartJson, ruleJson, palacesJson);
        const boardApi = initInteractiveBoard(showApp, chartJson, ruleJson);

        // 4 góc nhìn tab
        const tabs = showApp.querySelectorAll('.mayhen_tuvi_view_tab');
        const panels = showApp.querySelectorAll('.mayhen_tuvi_view_panel');
        function activateViewTab(target) {
            tabs.forEach((t) => t.classList.toggle('is-active', t.getAttribute('data-view') === target));
            panels.forEach((p) => p.classList.toggle('is-active', p.getAttribute('data-view-panel') === target));
            if (target === 'board') {
                requestAnimationFrame(() => boardApi?.redraw());
            }
            if (target === 'full') {
                showApp.querySelectorAll('[data-view-panel="full"] [data-tuvi-reveal]').forEach((el) => el.classList.add('is-revealed'));
            }
        }
        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activateViewTab(tab.getAttribute('data-view')));
        });

        // Bấm cung trên Biểu Đồ Tổng Quan 12 Cung -> chuyển sang Tab Luận Giải & cuộn mượt tới cung đó
        showApp.querySelectorAll('[data-jump-palace]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const code = btn.getAttribute('data-jump-palace');
                activateViewTab('full');
                // Đảm bảo bộ lọc mở "all" để cung luôn hiển thị
                showApp.querySelectorAll('[data-tuvi-pfilter]').forEach((f) => f.classList.toggle('is-active', f.getAttribute('data-tuvi-pfilter') === 'all'));
                showApp.querySelectorAll('.mayhen_tuvi_deep_card').forEach((c) => { c.style.display = ''; c.classList.add('is-revealed'); });
                const targetEl = document.getElementById(`tuvi-palace-${code}`);
                if (targetEl) {
                    setTimeout(() => {
                        targetEl.scrollIntoView({ behavior: activeQuality === 'lightweight' ? 'auto' : 'smooth', block: 'center' });
                        targetEl.classList.add('is-flash');
                        setTimeout(() => targetEl.classList.remove('is-flash'), 1400);
                    }, 80);
                }
            });
        });

        // Bộ lọc nhóm cung trong Hồ Sơ Luận Giải 12 Cung
        const pFilterBtns = Array.from(showApp.querySelectorAll('[data-tuvi-pfilter]'));
        const deepCards = Array.from(showApp.querySelectorAll('.mayhen_tuvi_deep_card'));
        pFilterBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const grp = btn.getAttribute('data-tuvi-pfilter') || 'all';
                pFilterBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
                deepCards.forEach((card) => {
                    const show = grp === 'all' || card.getAttribute('data-tuvi-pgroup') === grp;
                    card.style.display = show ? '' : 'none';
                    if (show) card.classList.add('is-revealed');
                });
            });
        });

        // Scroll-reveal tinh tế & animation thanh 12 cung khi cuộn tới
        const revealEls = Array.from(showApp.querySelectorAll('[data-tuvi-reveal]'));
        const scoreBarsWrap = showApp.querySelector('[data-tuvi-score-bars]');
        if (activeQuality === 'lightweight' || !('IntersectionObserver' in window)) {
            revealEls.forEach((el) => el.classList.add('is-revealed'));
            scoreBarsWrap?.classList.add('is-animated');
        } else {
            const revObs = new IntersectionObserver((entries) => {
                entries.forEach((e) => {
                    if (e.isIntersecting) {
                        e.target.classList.add('is-revealed');
                        revObs.unobserve(e.target);
                    }
                });
            }, { threshold: 0.08 });
            revealEls.forEach((el) => revObs.observe(el));

            if (scoreBarsWrap) {
                const barObs = new IntersectionObserver((entries) => {
                    if (entries.some((e) => e.isIntersecting)) {
                        scoreBarsWrap.classList.add('is-animated');
                        barObs.disconnect();
                    }
                }, { threshold: 0.14 });
                barObs.observe(scoreBarsWrap);
            }
        }

        // Xuất PNG HiDPI
        showApp.querySelector('[data-tuvi-export-png]')?.addEventListener('click', () => {
            const hideCb = showApp.querySelector('[data-tuvi-export-hide-pii]');
            exportTuViChartPng(chartJson, Boolean(hideCb?.checked));
        });

        // Hỏi sâu AI theo từng cung / từng phần đã luận (Tab 4 + Ngăn kéo hỏi nhanh theo mục)
        const sectionDefs = JSON.parse(document.getElementById('mayhenTuViSectionDefsJson')?.textContent || '{}');
        const followupRecords = JSON.parse(document.getElementById('mayhenTuViFollowupsJson')?.textContent || '[]');
        const fuForm = showApp.querySelector('[data-tuvi-followup-form]');
        const fuList = showApp.querySelector('[data-tuvi-followup-list]');
        const fuErrBox = showApp.querySelector('[data-tuvi-followup-error]');

        const scopedBackdrop = document.querySelector('[data-tuvi-scoped-backdrop]');
        const scopedModal = scopedBackdrop?.querySelector('[data-tuvi-scoped-modal]');
        const scopedBadge = scopedBackdrop?.querySelector('[data-tuvi-scoped-badge]');
        const scopedTitle = scopedBackdrop?.querySelector('[data-tuvi-scoped-title]');
        const scopedDesc = scopedBackdrop?.querySelector('[data-tuvi-scoped-desc]');
        const scopedSuggestWrap = scopedBackdrop?.querySelector('[data-tuvi-scoped-suggest-wrap]');
        const scopedSuggestToggle = scopedBackdrop?.querySelector('[data-tuvi-scoped-suggest-toggle]');
        const scopedSuggestions = scopedBackdrop?.querySelector('[data-tuvi-scoped-suggestions]');
        const scopedHistory = scopedBackdrop?.querySelector('[data-tuvi-scoped-history]');
        const scopedScrollBottomBtn = scopedBackdrop?.querySelector('[data-tuvi-scroll-bottom]');
        const scopedForm = scopedBackdrop?.querySelector('[data-tuvi-scoped-form]');
        const scopedInput = scopedBackdrop?.querySelector('#tuviScopedInput');
        const scopedCharCount = scopedBackdrop?.querySelector('[data-tuvi-char-count]');
        const scopedErrBox = scopedBackdrop?.querySelector('[data-tuvi-scoped-error]');
        const scopedSubmit = scopedBackdrop?.querySelector('[data-tuvi-scoped-submit]');

        const MAX_SCOPED_QUESTION_CHARS = 600;
        let lockedPageScrollY = 0;
        let isPageScrollLocked = false;
        let scopedTouchStartY = 0;

        let activeScope = {
            type: 'general',
            palaceCode: null,
            sectionKey: null,
            scopeCode: null,
            label: 'Tổng thể lá số',
        };

        function formatSafeProse(text) {
            return formatSafeAiMarkdown(text);
        }

        function setScopedBackgroundLock(lock) {
            const shouldLock = Boolean(lock);
            if (shouldLock && !isPageScrollLocked) {
                lockedPageScrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0;
                if (window.innerWidth <= 767) {
                    document.body.style.top = `-${lockedPageScrollY}px`;
                }
                document.documentElement.classList.add('mayhen_tuvi_chat_locked');
                document.body.classList.add('mayhen_tuvi_chat_locked');
                isPageScrollLocked = true;
            } else if (!shouldLock && isPageScrollLocked) {
                const restoreY = lockedPageScrollY;
                const hadFixedBody = window.innerWidth <= 767 || document.body.style.top !== '';
                document.documentElement.classList.remove('mayhen_tuvi_chat_locked');
                document.body.classList.remove('mayhen_tuvi_chat_locked');
                document.body.style.removeProperty('top');
                isPageScrollLocked = false;
                if (hadFixedBody) {
                    window.scrollTo({ top: restoreY, left: 0, behavior: 'instant' });
                }
            }
        }

        function syncScopedMobileViewport() {
            if (!scopedModal || !scopedBackdrop?.classList.contains('is-open')) {
                scopedModal?.style.removeProperty('height');
                scopedModal?.style.removeProperty('max-height');
                return;
            }
            const vv = window.visualViewport;
            if (window.innerWidth <= 767 && vv) {
                const h = Math.max(260, Math.round(vv.height));
                scopedModal.style.height = `${h}px`;
                scopedModal.style.maxHeight = `${h}px`;
            } else {
                scopedModal.style.removeProperty('height');
                scopedModal.style.removeProperty('max-height');
            }
        }

        function updateScopedCharCount() {
            if (!scopedInput || !scopedCharCount) return;
            const len = Array.from(String(scopedInput.value || '')).length;
            scopedCharCount.textContent = `${len}/${MAX_SCOPED_QUESTION_CHARS}`;
            scopedCharCount.classList.toggle('is-near-limit', len >= 540);
        }

        function resizeScopedInput() {
            if (!scopedInput) return;
            updateScopedCharCount();
            if (!scopedBackdrop?.classList.contains('is-open')) {
                scopedInput.style.removeProperty('height');
                return;
            }
            scopedInput.style.height = 'auto';
            const nextHeight = Math.max(44, Math.min(scopedInput.scrollHeight, 136));
            scopedInput.style.height = `${nextHeight}px`;
            if (scopedInput.scrollHeight <= 136) {
                scopedInput.scrollTop = 0;
            }
        }

        function syncScopedScrollButton() {
            if (!scopedHistory || !scopedScrollBottomBtn) return;
            const distanceFromBottom = scopedHistory.scrollHeight - scopedHistory.scrollTop - scopedHistory.clientHeight;
            const shouldShow = distanceFromBottom > 44 && scopedHistory.scrollHeight > scopedHistory.clientHeight + 24;
            scopedScrollBottomBtn.classList.toggle('is-visible', shouldShow);
        }

        function scrollScopedToBottom(smooth = true) {
            if (!scopedHistory) return;
            const targetTop = scopedHistory.scrollHeight;
            if (typeof scopedHistory.scrollTo === 'function') {
                scopedHistory.scrollTo({
                    top: targetTop,
                    behavior: smooth ? 'smooth' : 'auto',
                });
            } else {
                scopedHistory.scrollTop = targetTop;
            }
            window.requestAnimationFrame(syncScopedScrollButton);
        }

        function setSuggestionsCollapsed(collapsed) {
            if (!scopedSuggestWrap || !scopedSuggestToggle) return;
            scopedSuggestWrap.classList.toggle('is-collapsed', Boolean(collapsed));
            scopedSuggestToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }

        function appendFollowupCardToMainList(fu) {
            if (!fuList || !fu) return;
            const item = document.createElement('div');
            item.className = 'mayhen_tuvi_card';
            item.style.padding = '12px 14px';
            item.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
                    <span class="mayhen_tuvi_scope_pill">${escapeHtml(fu.scope_label || 'Tổng thể lá số')}</span>
                    <small style="color:#6B5F52;">${escapeHtml(fu.created_at || '')}</small>
                </div>
                <div style="font-weight:700; color:#85182A; margin-bottom:4px;">Hỏi: ${escapeHtml(fu.question)}</div>
                <div class="mayhen_tuvi_long_prose mayhen_md_prose" style="font-size:13.5px;">${formatSafeProse(fu.answer)}</div>
            `;
            fuList.appendChild(item);
        }

        function buildScopedChatBubblePair(question, answer, createdAt = '') {
            return `
                <div class="mayhen_tuvi_chat_row mayhen_tuvi_chat_row--user">
                    <div class="mayhen_tuvi_chat_bubble mayhen_tuvi_chat_bubble--user">${escapeHtml(question)}</div>
                </div>
                <div class="mayhen_tuvi_chat_row mayhen_tuvi_chat_row--ai">
                    <div class="mayhen_tuvi_chat_bubble mayhen_tuvi_chat_bubble--ai">
                        <div class="mayhen_tuvi_chat_sender">
                            <span>✦ Mây Hẹn AI</span>
                            ${createdAt ? `<time>${escapeHtml(createdAt)}</time>` : ''}
                        </div>
                        <div class="mayhen_tuvi_chat_text mayhen_md_prose">${formatSafeProse(answer)}</div>
                    </div>
                </div>
            `;
        }

        function appendPendingChatBubbles(question) {
            if (!scopedHistory) return;
            const emptyBox = scopedHistory.querySelector('.mayhen_tuvi_scoped_empty');
            if (emptyBox) {
                emptyBox.remove();
            }
            const wrapper = document.createElement('div');
            wrapper.setAttribute('data-tuvi-pending-group', '1');
            wrapper.style.display = 'contents';
            wrapper.innerHTML = `
                <div class="mayhen_tuvi_chat_row mayhen_tuvi_chat_row--user">
                    <div class="mayhen_tuvi_chat_bubble mayhen_tuvi_chat_bubble--user">${escapeHtml(question)}</div>
                </div>
                <div class="mayhen_tuvi_chat_row mayhen_tuvi_chat_row--ai">
                    <div class="mayhen_tuvi_chat_bubble mayhen_tuvi_chat_bubble--ai">
                        <div class="mayhen_tuvi_chat_sender"><span>✦ Mây Hẹn AI</span></div>
                        <div class="mayhen_tuvi_chat_typing">
                            <span>Đang đối chiếu dữ kiện lá số</span>
                            <span class="mayhen_tuvi_chat_dots" aria-hidden="true"><span></span><span></span><span></span></span>
                        </div>
                    </div>
                </div>
            `;
            scopedHistory.appendChild(wrapper);
            scrollScopedToBottom(true);
        }

        function renderScopedHistory({ scrollBottom = true, smooth = false } = {}) {
            if (!scopedHistory) return;
            const matched = followupRecords.filter((f) => {
                if (!activeScope.scopeCode) return !f.palace_code;
                return f.palace_code === activeScope.scopeCode;
            });

            if (matched.length === 0) {
                scopedHistory.innerHTML = `
                    <div class="mayhen_tuvi_scoped_empty">
                        <strong>Trò chuyện chuyên sâu • ${escapeHtml(activeScope.label || 'Phần đang xem')}</strong>
                        Chưa có tin nhắn nào trong mục này. Chọn một gợi ý bên dưới hoặc nhắn câu hỏi của bạn.
                    </div>
                `;
                setSuggestionsCollapsed(false);
            } else {
                scopedHistory.innerHTML = matched.map((f) => buildScopedChatBubblePair(f.question, f.answer, f.created_at || '')).join('');
                setSuggestionsCollapsed(true);
            }

            if (scrollBottom) {
                window.requestAnimationFrame(() => scrollScopedToBottom(smooth));
            } else {
                window.requestAnimationFrame(syncScopedScrollButton);
            }
        }

        function openScopedModal({ type, palaceCode = null, sectionKey = null, label = 'Phần đang xem', prefillQuestion = '' }) {
            if (!scopedBackdrop) return;
            const secDef = sectionKey ? sectionDefs[sectionKey] : null;
            const scopeCode = secDef ? secDef.scope_code : (palaceCode || null);
            activeScope = { type, palaceCode, sectionKey, scopeCode, label };

            if (scopedBadge) scopedBadge.textContent = 'Khóa dữ kiện';
            if (scopedTitle) scopedTitle.textContent = `Hỏi Sâu AI • ${label}`;

            let description = secDef?.description || 'Khóa chặt theo các sao, Tứ Hóa và dữ kiện có thật trong phần này.';
            let suggestions = Array.isArray(secDef?.suggested_questions) ? secDef.suggested_questions : [];

            if (type === 'palace' && palaceCode) {
                const pData = chartJson?.palaces_by_code?.[palaceCode];
                const digest = ruleJson?.palace_digests?.[palaceCode];
                if (pData) {
                    const auxSample = (pData.aux_stars || []).slice(0, 3).map((a) => a.name).filter(Boolean);
                    description = `${pData.name} (${pData.can_chi}) • Chính tinh: ${digest?.stars_headline || 'Vô Chính Diệu'}`;
                    suggestions = [
                        `Các sao trong ${pData.name} tạo nên điểm sáng và điểm cần lưu tâm gì nổi bật nhất?`,
                        auxSample.length > 0
                            ? `Sao ${auxSample[0]} trong ${pData.name} là gì và tác động ra sao với lá số này?`
                            : `Với ${pData.name} này, mình nên hành động thực tế thế nào trong năm nay?`,
                    ];
                }
            }

            if (scopedDesc) {
                scopedDesc.textContent = description;
                scopedDesc.title = description;
            }
            if (scopedSuggestWrap) {
                scopedSuggestWrap.classList.toggle('is-empty', suggestions.length === 0);
            }
            if (scopedSuggestions) {
                scopedSuggestions.innerHTML = suggestions.map((sq) => `
                    <button type="button" class="mayhen_tuvi_scoped_chip" data-tuvi-chip-question="${escapeHtml(sq)}">
                        ${escapeHtml(sq)}
                    </button>
                `).join('');
            }
            if (scopedErrBox) {
                scopedErrBox.style.display = 'none';
                scopedErrBox.textContent = '';
            }
            if (scopedInput) {
                scopedInput.value = prefillQuestion || '';
                updateScopedCharCount();
            }

            scopedBackdrop.classList.add('is-open');
            scopedBackdrop.setAttribute('aria-hidden', 'false');
            setScopedBackgroundLock(true);
            syncScopedMobileViewport();
            renderScopedHistory({ scrollBottom: true, smooth: false });
            window.requestAnimationFrame(() => {
                resizeScopedInput();
                if (scopedInput) {
                    scopedInput.focus({ preventScroll: true });
                    const endPos = scopedInput.value.length;
                    if (typeof scopedInput.setSelectionRange === 'function') {
                        scopedInput.setSelectionRange(endPos, endPos);
                    }
                    if (scopedInput.scrollHeight <= 136) {
                        scopedInput.scrollTop = 0;
                    }
                }
            });
        }

        function closeScopedModal() {
            if (!scopedBackdrop) return;
            if (document.activeElement === scopedInput) {
                scopedInput?.blur();
            }
            scopedBackdrop.classList.remove('is-open');
            scopedBackdrop.setAttribute('aria-hidden', 'true');
            setScopedBackgroundLock(false);
            syncScopedMobileViewport();
        }

        showApp.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-tuvi-ask-trigger]');
            if (!trigger) return;
            openScopedModal({
                type: trigger.getAttribute('data-scope-type') || 'section',
                palaceCode: trigger.getAttribute('data-palace-code') || null,
                sectionKey: trigger.getAttribute('data-section-key') || null,
                label: trigger.getAttribute('data-scope-label') || 'Phần đang xem',
            });
        });

        showApp.addEventListener('mayhen:tuvi-ask-palace', (e) => {
            const d = e.detail || {};
            if (!d.palaceCode) return;
            openScopedModal({
                type: 'palace',
                palaceCode: d.palaceCode,
                label: d.scopeLabel || d.palaceCode,
                prefillQuestion: d.prefillQuestion || '',
            });
        });

        scopedBackdrop?.addEventListener('click', (e) => {
            if (e.target === scopedBackdrop || e.target.closest('[data-tuvi-scoped-close]')) {
                closeScopedModal();
                return;
            }
            if (e.target.closest('[data-tuvi-scroll-bottom]')) {
                scrollScopedToBottom(true);
                return;
            }
            if (e.target.closest('[data-tuvi-scoped-suggest-toggle]')) {
                const isCollapsed = scopedSuggestWrap?.classList.contains('is-collapsed');
                setSuggestionsCollapsed(!isCollapsed);
                return;
            }
            const chip = e.target.closest('[data-tuvi-chip-question]');
            if (chip && scopedInput) {
                scopedInput.value = chip.getAttribute('data-tuvi-chip-question') || '';
                window.requestAnimationFrame(() => {
                    resizeScopedInput();
                    scopedInput.focus({ preventScroll: true });
                    const endPos = scopedInput.value.length;
                    if (typeof scopedInput.setSelectionRange === 'function') {
                        scopedInput.setSelectionRange(endPos, endPos);
                    }
                    if (scopedInput.scrollHeight <= 136) {
                        scopedInput.scrollTop = 0;
                    }
                });
            }
        });

        scopedBackdrop?.addEventListener('wheel', (e) => {
            if (!scopedBackdrop.classList.contains('is-open')) return;
            const scrollable = e.target instanceof Element
                ? e.target.closest('[data-tuvi-scoped-history], #tuviScopedInput, [data-tuvi-scoped-suggestions]')
                : null;
            if (!scrollable) {
                e.preventDefault();
                return;
            }
            if (scrollable.scrollHeight <= scrollable.clientHeight + 1) {
                e.preventDefault();
            }
        }, { passive: false });

        scopedBackdrop?.addEventListener('touchstart', (e) => {
            if (e.touches && e.touches.length === 1) {
                scopedTouchStartY = e.touches[0].clientY;
            }
        }, { passive: true });

        scopedBackdrop?.addEventListener('touchmove', (e) => {
            if (!scopedBackdrop.classList.contains('is-open')) return;
            const scrollable = e.target instanceof Element
                ? e.target.closest('[data-tuvi-scoped-history], #tuviScopedInput')
                : null;
            if (!scrollable || scrollable.scrollHeight <= scrollable.clientHeight + 1) {
                e.preventDefault();
                return;
            }
            const currentY = e.touches?.[0]?.clientY ?? scopedTouchStartY;
            const deltaY = currentY - scopedTouchStartY;
            const atTop = scrollable.scrollTop <= 0 && deltaY > 0;
            const atBottom = scrollable.scrollTop + scrollable.clientHeight >= scrollable.scrollHeight - 1 && deltaY < 0;
            if (atTop || atBottom) {
                e.preventDefault();
            }
        }, { passive: false });

        scopedHistory?.addEventListener('scroll', syncScopedScrollButton, { passive: true });
        window.addEventListener('resize', () => {
            syncScopedScrollButton();
            syncScopedMobileViewport();
        }, { passive: true });
        window.visualViewport?.addEventListener('resize', () => {
            syncScopedMobileViewport();
            syncScopedScrollButton();
        }, { passive: true });

        scopedInput?.addEventListener('input', resizeScopedInput);
        scopedInput?.addEventListener('focus', () => {
            window.setTimeout(syncScopedMobileViewport, 80);
            window.setTimeout(syncScopedMobileViewport, 280);
        });
        scopedInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
                e.preventDefault();
                if (scopedForm?.requestSubmit) {
                    scopedForm.requestSubmit();
                } else {
                    scopedForm?.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && scopedBackdrop?.classList.contains('is-open')) {
                closeScopedModal();
            }
        });

        async function submitFollowupQuestion(payload, errEl, submitBtn, defaultLabel, busyHtml = null) {
            if (errEl) {
                errEl.style.display = 'none';
                errEl.textContent = '';
            }
            const previousHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                if (busyHtml) {
                    submitBtn.innerHTML = busyHtml;
                } else {
                    submitBtn.textContent = 'Đang đối chiếu lá số...';
                }
            }
            try {
                const res = await fetch(endpoints.followup, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    const msg = data.message || 'Câu hỏi chưa hợp lệ hoặc tạm thời gián đoạn.';
                    if (errEl) {
                        errEl.textContent = msg;
                        errEl.style.display = 'block';
                    }
                    return null;
                }
                return data.followup || null;
            } catch {
                if (errEl) {
                    errEl.textContent = 'Kết nối tạm gián đoạn, vui lòng thử lại.';
                    errEl.style.display = 'block';
                }
                return null;
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    if (busyHtml) {
                        submitBtn.innerHTML = previousHtml;
                    } else {
                        submitBtn.textContent = defaultLabel;
                    }
                }
            }
        }

        scopedForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (scopedSubmit?.disabled) return;
            const q = String(scopedInput?.value || '').trim();
            if (q.length < 4) return;
            if (Array.from(q).length > MAX_SCOPED_QUESTION_CHARS) {
                if (scopedErrBox) {
                    scopedErrBox.textContent = `Câu hỏi tối đa ${MAX_SCOPED_QUESTION_CHARS} ký tự để trợ lý AI phân tích chính xác nhất.`;
                    scopedErrBox.style.display = 'block';
                }
                return;
            }

            if (scopedInput) {
                scopedInput.value = '';
                resizeScopedInput();
            }
            appendPendingChatBubbles(q);

            const created = await submitFollowupQuestion(
                {
                    question: q,
                    palace_code: activeScope.palaceCode,
                    section_key: activeScope.sectionKey,
                },
                scopedErrBox,
                scopedSubmit,
                'Gửi câu hỏi',
                '<span class="mayhen_tuvi_chat_dots" aria-hidden="true"><span></span><span></span><span></span></span>'
            );
            if (created) {
                followupRecords.push(created);
                appendFollowupCardToMainList(created);
                renderScopedHistory({ scrollBottom: true, smooth: true });
            } else {
                renderScopedHistory({ scrollBottom: true, smooth: false });
                if (scopedInput) {
                    scopedInput.value = q;
                    resizeScopedInput();
                    scopedInput.focus();
                }
            }
        });

        fuForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(fuForm);
            const q = String(fd.get('question') || '').trim();
            if (q.length < 4) return;
            const rawScopeVal = String(fd.get('palace_code') || '').trim() || null;
            let sectionKey = null;
            let palaceCode = rawScopeVal;
            if (rawScopeVal && rawScopeVal.startsWith('sec:')) {
                const foundEntry = Object.entries(sectionDefs).find(([, def]) => def.scope_code === rawScopeVal);
                if (foundEntry) {
                    sectionKey = foundEntry[0];
                    palaceCode = null;
                }
            }
            const btn = fuForm.querySelector('button[type="submit"]');
            const created = await submitFollowupQuestion(
                { question: q, palace_code: palaceCode, section_key: sectionKey },
                fuErrBox,
                btn,
                'Hỏi Sâu Trên Lá Số Này'
            );
            if (created) {
                followupRecords.push(created);
                appendFollowupCardToMainList(created);
                fuForm.reset();
            }
        });

        // Nhật ký chiêm nghiệm
        const jForm = showApp.querySelector('[data-tuvi-journal-form]');
        const jList = showApp.querySelector('[data-tuvi-journal-list]');
        jForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(jForm);
            const res = await fetch(endpoints.journal, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    milestone_days: Number(fd.get('milestone_days') || 30),
                    focus_note: String(fd.get('focus_note') || ''),
                    actions_taken: String(fd.get('actions_taken') || ''),
                    lessons_learned: String(fd.get('lessons_learned') || ''),
                }),
            });
            const data = await res.json();
            if (res.ok && data.success && data.journal && jList) {
                const card = document.createElement('div');
                card.className = 'mayhen_tuvi_card';
                card.style.padding = '12px 14px';
                card.innerHTML = `<strong>Mốc ${data.journal.milestone_days} ngày • ${escapeHtml(data.journal.created_at)}</strong><p style="font-size:13px;">${escapeHtml(data.journal.actions_taken || data.journal.focus_note || '')}</p>`;
                jList.prepend(card);
                jForm.reset();
            }
        });

        // Chia sẻ / Thu hồi Thẻ Ẩn Danh
        const shareOut = showApp.querySelector('[data-tuvi-share-output]');
        const revokeBtn = showApp.querySelector('[data-tuvi-revoke-btn]');
        showApp.querySelector('[data-tuvi-share-btn]')?.addEventListener('click', async () => {
            const res = await fetch(endpoints.toggle_share, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ action: 'enable' }),
            });
            const data = await res.json();
            if (res.ok && data.share_url) {
                navigator.clipboard?.writeText(data.share_url);
                if (shareOut) {
                    shareOut.innerHTML = `Link Thẻ Chia Sẻ (đã ẩn ngày giờ sinh): <a href="${escapeHtml(data.share_url)}" target="_blank">${escapeHtml(data.share_url)}</a> (đã sao chép)`;
                    shareOut.style.display = 'block';
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
            if (res.ok) {
                if (shareOut) { shareOut.textContent = 'Đã thu hồi liên kết chia sẻ. Lá số riêng tư trở lại.'; shareOut.style.display = 'block'; }
                revokeBtn.style.display = 'none';
            }
        });

        // Đánh giá hữu ích & Yêu thích
        const fbStatus = showApp.querySelector('[data-tuvi-feedback-status]');
        showApp.querySelectorAll('[data-tuvi-feedback-rating]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const rating = btn.getAttribute('data-tuvi-feedback-rating');
                const res = await fetch(endpoints.feedback, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ feedback_rating: rating }),
                });
                if (res.ok) {
                    showApp.querySelectorAll('[data-tuvi-feedback-rating]').forEach((b) => b.classList.toggle('is-active', b === btn));
                    if (fbStatus) fbStatus.textContent = 'Cảm ơn phản hồi tinh tế của bạn!';
                }
            });
        });

        return;
    }

    // =========================================================================
    // 7. TRANG CHÍNH (/tu-vi): 3 TAB TUẦN TỰ CÓ KHÓA + LIVE PREVIEW + NGHI THỨC
    // =========================================================================
    const landingApp = document.getElementById('mayhenTuViApp');
    if (landingApp) {
        const generateUrl = landingApp.getAttribute('data-generate-url') || '';
        const previewUrl = landingApp.getAttribute('data-preview-url') || '';
        const canUseFree = landingApp.getAttribute('data-can-use') === '1';

        const demoChart = JSON.parse(document.getElementById('mayhenTuViDemoChartJson')?.textContent || '{}');
        const demoRule = JSON.parse(document.getElementById('mayhenTuViDemoRuleJson')?.textContent || '{}');
        const palacesMeta = JSON.parse(document.getElementById('mayhenTuViPalacesJson')?.textContent || '{}');

        initTwelveCardsDeck(landingApp, demoChart, demoRule, palacesMeta);
        initInteractiveBoard(landingApp, demoChart, demoRule);
        landingApp.addEventListener('mayhen:tuvi-ask-palace', () => {
            if (!canUseFree) {
                document.getElementById('mayhenTuViLoginModal')?.classList.add('is-open');
                return;
            }
            document.getElementById('tuviWizardForm')?.scrollIntoView({ behavior: activeQuality === 'lightweight' ? 'auto' : 'smooth', block: 'center' });
        });
        landingApp.querySelector('[data-tuvi-export-png]')?.addEventListener('click', () => {
            const hideCb = landingApp.querySelector('[data-tuvi-export-hide-pii]');
            exportTuViChartPng(demoChart, Boolean(hideCb?.checked));
        });

        // Khai báo toàn bộ biến trạng thái trước (tránh lỗi TDZ)
        let currentTab = 1;
        let maxUnlockedTab = 1;
        let birthTimeMode = 'exact_time';
        let focusTopic = 'toan_bo';
        let previewTimer = 0;
        let lastPreviewBirth = null;

        const tabBtns = Array.from(landingApp.querySelectorAll('[data-tuvi-tab]'));
        const tabPanels = Array.from(landingApp.querySelectorAll('[data-tuvi-panel]'));
        const timeModeHidden = document.getElementById('tuviBirthTimeMode');
        const timeModeBtns = Array.from(landingApp.querySelectorAll('[data-time-mode-btn]'));
        const calTypeSel = document.getElementById('tuviCalendarType');
        const leapWrap = document.getElementById('tuviLeapMonthWrap');
        const step1Err = document.getElementById('tuviStep1Err');
        const step2Err = document.getElementById('tuviStep2Err');

        function clearInvalidInputs() {
            landingApp.querySelectorAll('.mayhen_tuvi_input.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        }

        function showStepError(box, message) {
            if (!box) return;
            box.textContent = message;
            box.style.display = 'block';
        }

        function hideStepError(box) {
            if (!box) return;
            box.style.display = 'none';
        }

        // Kiểm tra hợp lệ Bước 1 (Bắt buộc nhập đủ ngày, tháng, năm hợp lệ)
        function validateStep1(showErrors = true) {
            clearInvalidInputs();
            hideStepError(step1Err);

            const dayEl = document.getElementById('tuviBirthDay');
            const monthEl = document.getElementById('tuviBirthMonth');
            const yearEl = document.getElementById('tuviBirthYear');
            const d = Number(dayEl?.value);
            const m = Number(monthEl?.value);
            const y = Number(yearEl?.value);
            const calType = calTypeSel?.value || 'solar';

            if (!dayEl?.value || !monthEl?.value || !yearEl?.value) {
                if (showErrors) {
                    if (!dayEl?.value) dayEl?.classList.add('is-invalid');
                    if (!monthEl?.value) monthEl?.classList.add('is-invalid');
                    if (!yearEl?.value) yearEl?.classList.add('is-invalid');
                    showStepError(step1Err, 'Vui lòng nhập đầy đủ Ngày, Tháng và Năm sinh trước khi sang Bước 2.');
                }
                return false;
            }
            if (!Number.isInteger(y) || y < 1900 || y > 2100) {
                if (showErrors) {
                    yearEl?.classList.add('is-invalid');
                    showStepError(step1Err, 'Năm sinh cần nằm trong khoảng từ 1900 đến 2100.');
                }
                return false;
            }
            if (!Number.isInteger(m) || m < 1 || m > 12) {
                if (showErrors) {
                    monthEl?.classList.add('is-invalid');
                    showStepError(step1Err, 'Tháng sinh phải từ 1 đến 12.');
                }
                return false;
            }
            if (!Number.isInteger(d) || d < 1 || d > (calType === 'lunar' ? 30 : 31)) {
                if (showErrors) {
                    dayEl?.classList.add('is-invalid');
                    showStepError(step1Err, calType === 'lunar' ? 'Ngày Âm lịch phải từ 1 đến 30.' : 'Ngày sinh phải từ 1 đến 31.');
                }
                return false;
            }
            if (calType === 'solar') {
                const probe = new Date(y, m - 1, d);
                if (probe.getFullYear() !== y || probe.getMonth() !== m - 1 || probe.getDate() !== d) {
                    if (showErrors) {
                        dayEl?.classList.add('is-invalid');
                        showStepError(step1Err, `Ngày Dương lịch ${d}/${m}/${y} không tồn tại. Vui lòng kiểm tra lại.`);
                    }
                    return false;
                }
            }
            return true;
        }

        // Kiểm tra hợp lệ Bước 2 (Bắt buộc nhập giờ nếu chọn chế độ Biết rõ giờ & phút)
        function validateStep2(showErrors = true) {
            clearInvalidInputs();
            hideStepError(step2Err);

            if (birthTimeMode === 'exact_time') {
                const hourEl = document.getElementById('tuviBirthHour');
                const minEl = document.getElementById('tuviBirthMinute');
                const hRaw = (hourEl?.value ?? '').trim();
                const h = Number(hRaw);
                const mi = Number(minEl?.value || 0);
                if (hRaw === '' || !Number.isInteger(h) || h < 0 || h > 23) {
                    if (showErrors) {
                        hourEl?.classList.add('is-invalid');
                        showStepError(step2Err, 'Vui lòng nhập Giờ sinh hợp lệ (từ 0 đến 23 giờ), hoặc chọn chế độ giờ sinh khác.');
                    }
                    return false;
                }
                if (!Number.isInteger(mi) || mi < 0 || mi > 59) {
                    if (showErrors) {
                        minEl?.classList.add('is-invalid');
                        showStepError(step2Err, 'Phút sinh phải nằm trong khoảng từ 0 đến 59.');
                    }
                    return false;
                }
            }
            return true;
        }

        function updateStepSummaries() {
            const nick = (document.getElementById('tuviNickname')?.value || '').trim() || 'Hồ sơ cá nhân';
            const genderLabel = (document.getElementById('tuviGender')?.value || 'female') === 'male' ? 'Nam mệnh' : 'Nữ mệnh';
            const d = document.getElementById('tuviBirthDay')?.value || '';
            const m = document.getElementById('tuviBirthMonth')?.value || '';
            const y = document.getElementById('tuviBirthYear')?.value || '';
            const calLabel = (calTypeSel?.value || 'solar') === 'lunar' ? 'Âm lịch' : 'Dương lịch';

            const s2 = document.getElementById('tuviStep2Summary');
            if (s2) {
                s2.innerHTML = `<strong>${escapeHtml(nick)}</strong> • ${genderLabel} • Ngày sinh: <strong>${escapeHtml(d)}/${escapeHtml(m)}/${escapeHtml(y)}</strong> (${calLabel})`;
            }
            const s3 = document.getElementById('tuviStep3Summary');
            if (s3) {
                if (lastPreviewBirth) {
                    const hrText = lastPreviewBirth.pillars?.hour
                        ? `${lastPreviewBirth.pillars.hour.label} (${lastPreviewBirth.pillars.hour.range})`
                        : 'Chưa rõ giờ sinh';
                    s3.innerHTML = `<strong>${escapeHtml(nick)}</strong> • ${genderLabel} • ${escapeHtml(lastPreviewBirth.solar.label)} ↔ ${escapeHtml(lastPreviewBirth.lunar.label)} • Giờ: <strong>${escapeHtml(hrText)}</strong> • ${escapeHtml(lastPreviewBirth.nap_am.name)}`;
                } else {
                    s3.innerHTML = `<strong>${escapeHtml(nick)}</strong> • ${genderLabel} • ${escapeHtml(d)}/${escapeHtml(m)}/${escapeHtml(y)} (${calLabel})`;
                }
            }
        }

        function renderStepperState() {
            tabBtns.forEach((btn) => {
                const step = Number(btn.getAttribute('data-tuvi-tab'));
                const isLocked = step > maxUnlockedTab;
                const isDone = step < currentTab || (step < maxUnlockedTab && step !== currentTab);
                btn.classList.toggle('is-active', step === currentTab);
                btn.classList.toggle('is-locked', isLocked);
                btn.classList.toggle('is-completed', isDone && step !== currentTab);
                btn.setAttribute('aria-selected', step === currentTab ? 'true' : 'false');
                btn.setAttribute('aria-disabled', isLocked ? 'true' : 'false');
                const numEl = btn.querySelector(`[data-tab-num="${step}"]`);
                if (numEl) numEl.textContent = isDone && step !== currentTab ? '✓' : String(step);
            });
            tabPanels.forEach((p) => {
                p.classList.toggle('is-active', Number(p.getAttribute('data-tuvi-panel')) === currentTab);
            });
            updateStepSummaries();
        }

        async function goToTab(targetStep) {
            const step = Number(targetStep);
            if (step === currentTab) return;

            if (step > currentTab) {
                if (currentTab === 1 && !validateStep1(true)) return;
                if (step >= 3) {
                    if (!validateStep1(true)) {
                        currentTab = 1;
                        renderStepperState();
                        return;
                    }
                    if (!validateStep2(true)) return;
                    const okPreview = await refreshCalendarPreview();
                    if (!okPreview) return;
                }
                maxUnlockedTab = Math.max(maxUnlockedTab, step);
            }

            currentTab = step;
            renderStepperState();
            if (currentTab === 2) {
                refreshCalendarPreview();
            }
        }

        tabBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const target = Number(btn.getAttribute('data-tuvi-tab'));
                if (target > maxUnlockedTab) {
                    // Nếu nhấn thẳng tab kế tiếp hợp lệ thì kiểm tra điều kiện luôn; nếu chưa nhập đủ thì rung nhẹ và báo lỗi
                    if (target === currentTab + 1) {
                        goToTab(target);
                    } else {
                        btn.classList.remove('is-shake');
                        void btn.offsetWidth;
                        btn.classList.add('is-shake');
                        if (currentTab === 1) validateStep1(true);
                        else if (currentTab === 2) validateStep2(true);
                    }
                    return;
                }
                goToTab(target);
            });
        });

        landingApp.querySelectorAll('[data-tuvi-go-next]').forEach((btn) => {
            btn.addEventListener('click', () => goToTab(Number(btn.getAttribute('data-tuvi-go-next'))));
        });
        landingApp.querySelectorAll('[data-tuvi-go-prev]').forEach((btn) => {
            btn.addEventListener('click', () => goToTab(Number(btn.getAttribute('data-tuvi-go-prev'))));
        });

        function collectWizardPayload() {
            return {
                nickname: (document.getElementById('tuviNickname')?.value || '').trim(),
                gender_yin_yang: document.getElementById('tuviGender')?.value || 'female',
                calendar_type: calTypeSel?.value || 'solar',
                birth_year: Number(document.getElementById('tuviBirthYear')?.value || 0),
                birth_month: Number(document.getElementById('tuviBirthMonth')?.value || 0),
                birth_day: Number(document.getElementById('tuviBirthDay')?.value || 0),
                is_leap_month: Boolean(document.getElementById('tuviIsLeapMonth')?.checked),
                birth_time_mode: birthTimeMode,
                birth_hour: document.getElementById('tuviBirthHour')?.value === '' ? null : Number(document.getElementById('tuviBirthHour')?.value),
                birth_minute: Number(document.getElementById('tuviBirthMinute')?.value || 0),
                birth_branch_index: Number(document.getElementById('tuviBirthBranch')?.value ?? 3),
                birth_time_window: document.getElementById('tuviBirthWindow')?.value || 'morning',
                birth_timezone: document.getElementById('tuviTimezone')?.value || 'Asia/Ho_Chi_Minh',
                leap_month_rule: document.getElementById('tuviLeapRule')?.value || 'split_15',
                late_rat_rule: document.getElementById('tuviLateRatRule')?.value || 'next_lunar_day',
                viewed_year: Number(document.getElementById('tuviViewedYear')?.value || new Date().getFullYear()),
                focus_topic: focusTopic,
            };
        }

        async function refreshCalendarPreview() {
            const box = document.getElementById('tuviCalendarPreviewBox');
            if (!box || !previewUrl) return true;
            if (!validateStep1(false)) return false;
            if (birthTimeMode === 'exact_time' && (document.getElementById('tuviBirthHour')?.value ?? '').trim() === '') {
                box.innerHTML = 'Nhập <strong>Giờ sinh (0–23h)</strong> ở trên để xem quy đổi lịch Âm – Dương &amp; Bản Mệnh Ngũ Hành.';
                return false;
            }
            box.textContent = 'Đang đối chiếu lịch Âm – Dương Việt Nam...';
            try {
                const res = await fetch(previewUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(collectWizardPayload()),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    lastPreviewBirth = null;
                    box.innerHTML = `<strong style="color:#B91C1C;">${escapeHtml(data.message || 'Ngày giờ sinh chưa hợp lệ.')}</strong>`;
                    showStepError(step2Err, data.message || 'Ngày giờ sinh chưa hợp lệ. Vui lòng kiểm tra lại.');
                    return false;
                }
                hideStepError(step2Err);
                const b = data.birth;
                lastPreviewBirth = b;
                box.innerHTML = `
                    <div><strong>Đối chiếu tức thời:</strong> Dương lịch <strong>${escapeHtml(b.solar.label)}</strong> ↔ Âm lịch <strong>${escapeHtml(b.lunar.label)}</strong></div>
                    <div><strong>Bản Mệnh:</strong> ${escapeHtml(b.nap_am.name)} (hành ${escapeHtml(b.nap_am.element)}) • <strong>Giờ Địa Chi:</strong> ${escapeHtml(b.pillars.hour ? b.pillars.hour.label + ' (' + b.pillars.hour.range + ')' : 'Chưa xác định giờ cụ thể')}</div>
                    ${(b.notes || []).length ? `<div style="margin-top:4px; color:#85182A;">${b.notes.map(escapeHtml).join(' ')}</div>` : ''}
                `;
                updateStepSummaries();
                return true;
            } catch {
                box.textContent = 'Không thể kết nối kiểm tra lịch lúc này, bạn vẫn có thể tiếp tục.';
                return true;
            }
        }

        function scheduleCalendarPreview() {
            clearTimeout(previewTimer);
            if (currentTab >= 2) {
                previewTimer = setTimeout(refreshCalendarPreview, 280);
            }
        }

        function syncTimeModeFields(mode) {
            birthTimeMode = mode || 'exact_time';
            if (timeModeHidden) timeModeHidden.value = birthTimeMode;
            timeModeBtns.forEach((b) => b.classList.toggle('is-active', b.getAttribute('data-time-mode-btn') === birthTimeMode));
            landingApp.querySelectorAll('[data-time-field]').forEach((el) => {
                el.style.display = el.getAttribute('data-time-field') === birthTimeMode ? '' : 'none';
            });
            hideStepError(step2Err);
            scheduleCalendarPreview();
        }
        timeModeBtns.forEach((btn) => {
            btn.addEventListener('click', () => syncTimeModeFields(btn.getAttribute('data-time-mode-btn')));
        });
        syncTimeModeFields('exact_time');

        calTypeSel?.addEventListener('change', () => {
            if (leapWrap) leapWrap.style.display = calTypeSel.value === 'lunar' ? '' : 'none';
            scheduleCalendarPreview();
        });

        landingApp.querySelectorAll('[data-tuvi-topic]').forEach((btn) => {
            btn.addEventListener('click', () => {
                focusTopic = btn.getAttribute('data-tuvi-topic') || 'toan_bo';
                landingApp.querySelectorAll('[data-tuvi-topic]').forEach((b) => b.classList.toggle('is-active', b === btn));
            });
        });

        ['tuviBirthHour', 'tuviBirthMinute', 'tuviBirthBranch', 'tuviBirthWindow', 'tuviTimezone', 'tuviLeapRule', 'tuviLateRatRule'].forEach((id) => {
            const el = document.getElementById(id);
            el?.addEventListener('change', scheduleCalendarPreview);
            el?.addEventListener('input', scheduleCalendarPreview);
        });

        // Nếu người dùng quay lại sửa Ngày/Tháng/Năm sinh thì reset trạng thái hợp lệ ở Bước 2 nếu cần
        ['tuviBirthDay', 'tuviBirthMonth', 'tuviBirthYear', 'tuviIsLeapMonth'].forEach((id) => {
            document.getElementById(id)?.addEventListener('input', () => {
                hideStepError(step1Err);
                lastPreviewBirth = null;
            });
        });

        const TUVI_DRAFT_KEY = 'tuvi_wizard';
        let tuviDraftTimer = 0;

        function saveTuViDraftNow() {
            const p = collectWizardPayload();
            saveToolDraft(TUVI_DRAFT_KEY, {
                ...p,
                birth_hour_raw: document.getElementById('tuviBirthHour')?.value ?? '',
                current_tab: currentTab,
                max_unlocked_tab: maxUnlockedTab,
            });
        }

        function scheduleTuViDraftSave() {
            window.clearTimeout(tuviDraftTimer);
            tuviDraftTimer = window.setTimeout(saveTuViDraftNow, 180);
        }

        function restoreTuViDraft() {
            const d = loadToolDraft(TUVI_DRAFT_KEY);
            if (!d) return false;

            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el && val !== undefined && val !== null && val !== '') {
                    el.value = String(val);
                }
            };

            setVal('tuviNickname', d.nickname);
            setVal('tuviGender', d.gender_yin_yang);
            setVal('tuviCalendarType', d.calendar_type);
            if (d.birth_year) setVal('tuviBirthYear', d.birth_year);
            if (d.birth_month) setVal('tuviBirthMonth', d.birth_month);
            if (d.birth_day) setVal('tuviBirthDay', d.birth_day);
            const leapEl = document.getElementById('tuviIsLeapMonth');
            if (leapEl && typeof d.is_leap_month === 'boolean') leapEl.checked = d.is_leap_month;
            if (leapWrap && calTypeSel) leapWrap.style.display = calTypeSel.value === 'lunar' ? '' : 'none';

            if (d.birth_hour_raw !== undefined) {
                const hEl = document.getElementById('tuviBirthHour');
                if (hEl) hEl.value = String(d.birth_hour_raw);
            } else if (d.birth_hour !== null && d.birth_hour !== undefined) {
                setVal('tuviBirthHour', d.birth_hour);
            }
            if (d.birth_minute !== undefined) setVal('tuviBirthMinute', d.birth_minute);
            if (d.birth_branch_index !== undefined) setVal('tuviBirthBranch', d.birth_branch_index);
            setVal('tuviBirthWindow', d.birth_time_window);
            setVal('tuviTimezone', d.birth_timezone);
            setVal('tuviLeapRule', d.leap_month_rule);
            setVal('tuviLateRatRule', d.late_rat_rule);
            if (d.viewed_year) setVal('tuviViewedYear', d.viewed_year);

            if (d.birth_time_mode) {
                syncTimeModeFields(d.birth_time_mode);
            }
            if (d.focus_topic) {
                focusTopic = String(d.focus_topic);
                landingApp.querySelectorAll('[data-tuvi-topic]').forEach((b) => {
                    b.classList.toggle('is-active', b.getAttribute('data-tuvi-topic') === focusTopic);
                });
            }

            const savedTab = Number(d.current_tab || 1);
            const savedMax = Number(d.max_unlocked_tab || savedTab);
            if (savedTab >= 1 && savedTab <= 3) {
                currentTab = savedTab;
                maxUnlockedTab = Math.max(savedTab, Math.min(3, savedMax));
            }
            return true;
        }

        landingApp.addEventListener('input', scheduleTuViDraftSave);
        landingApp.addEventListener('change', saveTuViDraftNow);
        landingApp.querySelectorAll('[data-tuvi-topic], [data-time-mode-btn], [data-tuvi-tab], [data-tuvi-go-next], [data-tuvi-go-prev]').forEach((btn) => {
            btn.addEventListener('click', () => window.setTimeout(saveTuViDraftNow, 50));
        });

        const hasTuViRestored = restoreTuViDraft();
        renderStepperState();
        if (hasTuViRestored) {
            if (currentTab >= 2) {
                refreshCalendarPreview();
            }
            const bannerHost = landingApp.querySelector('.mayhen_tuvi_wizard_card') || landingApp.firstElementChild;
            showDraftRestoredBanner(bannerHost, {
                message: '✦ Đã tự động khôi phục toàn bộ thông tin ngày giờ sinh và chủ đề bạn đã chọn trước đó.',
                onReset: () => {
                    clearToolDraft(TUVI_DRAFT_KEY);
                    window.location.reload();
                },
            });
        }

        // Nghi thức chuyển cảnh 1 luồng mượt khi lập lá số & luận giải AI (2-5 phút để tăng độ chính xác)
        const ritualOverlay = document.getElementById('mayhenTuViRitualOverlay');
        const ritualTimerEl = document.getElementById('mayhenTuViRitualTimer');
        const ritualSteps = ritualOverlay ? Array.from(ritualOverlay.querySelectorAll('[data-ritual-step]')) : [];
        let ritualInterval = 0;
        let ritualClockInterval = 0;
        let submitTimerInterval = 0;

        function formatElapsedMmSs(totalSeconds) {
            const m = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
            const s = String(totalSeconds % 60).padStart(2, '0');
            return `${m}:${s}`;
        }

        function startRitualOverlay(startedAtMs) {
            if (ritualTimerEl) ritualTimerEl.textContent = '00:00';
            clearInterval(ritualClockInterval);
            ritualClockInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - startedAtMs) / 1000);
                if (ritualTimerEl) {
                    ritualTimerEl.textContent = formatElapsedMmSs(elapsed);
                }
            }, 1000);

            if (!ritualOverlay || activeQuality === 'lightweight') return;
            ritualOverlay.classList.add('is-open');
            let idx = 0;
            ritualSteps.forEach((s, i) => {
                s.classList.toggle('is-active', i === 0);
                s.classList.remove('is-done');
            });
            clearInterval(ritualInterval);
            ritualInterval = setInterval(() => {
                if (idx < ritualSteps.length - 1) {
                    ritualSteps[idx]?.classList.remove('is-active');
                    ritualSteps[idx]?.classList.add('is-done');
                    idx++;
                    ritualSteps[idx]?.classList.add('is-active');
                }
            }, 1400);
        }

        function stopRitualOverlay() {
            clearInterval(ritualInterval);
            clearInterval(ritualClockInterval);
            clearInterval(submitTimerInterval);
            ritualOverlay?.classList.remove('is-open');
        }

        const submitBtn = document.getElementById('tuviSubmitGenerateBtn');
        const errBox = document.getElementById('tuviWizardErrorBox');
        let idemKey = 'tv_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 8);

        submitBtn?.addEventListener('click', async () => {
            saveTuViDraftNow();
            if (!canUseFree) {
                document.getElementById('mayhenTuViLoginModal')?.classList.add('is-open');
                return;
            }
            if (errBox) errBox.style.display = 'none';
            submitBtn.disabled = true;
            const oldTxt = submitBtn.textContent;
            const startedAtMs = Date.now();
            submitBtn.textContent = '✦ Đang luận giải AI 12 cung (dự kiến 2–5 phút)...';
            clearInterval(submitTimerInterval);
            submitTimerInterval = setInterval(() => {
                const sec = Math.floor((Date.now() - startedAtMs) / 1000);
                submitBtn.textContent = `✦ Đang luận 2–5 phút để tăng độ chính xác (${formatElapsedMmSs(sec)})...`;
            }, 1000);
            startRitualOverlay(startedAtMs);
            try {
                const payload = Object.assign(collectWizardPayload(), { idempotency_key: idemKey });
                const [res] = await Promise.all([
                    fetch(generateUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload),
                    }),
                    new Promise((r) => setTimeout(r, activeQuality === 'lightweight' ? 0 : 900)),
                ]);
                const data = await res.json();
                if (res.status === 401 || res.status === 403) {
                    saveTuViDraftNow();
                    stopRitualOverlay();
                    document.getElementById('mayhenTuViLoginModal')?.classList.add('is-open');
                    return;
                }
                if (!res.ok || !data.success) {
                    stopRitualOverlay();
                    if (errBox) {
                        errBox.textContent = data.message || 'Không thể lập lá số lúc này.';
                        errBox.style.display = 'block';
                    }
                    return;
                }
                clearToolDraft(TUVI_DRAFT_KEY);
                ritualSteps.forEach((s) => { s.classList.remove('is-active'); s.classList.add('is-done'); });
                if (data.result_url) {
                    window.location.assign(data.result_url);
                }
            } catch {
                stopRitualOverlay();
                if (errBox) {
                    errBox.textContent = 'Lỗi kết nối máy chủ hoặc quá thời gian xử lý. Vui lòng thử lại sau giây lát.';
                    errBox.style.display = 'block';
                }
            } finally {
                clearInterval(submitTimerInterval);
                submitBtn.disabled = false;
                submitBtn.textContent = oldTxt;
            }
        });

        document.getElementById('mayhenTuViLoginClose')?.addEventListener('click', () => {
            document.getElementById('mayhenTuViLoginModal')?.classList.remove('is-open');
        });
    }

    // =========================================================================
    // 8. NHÂN DUYÊN SONG MỆNH FORM (/tu-vi/nhan-duyen)
    // =========================================================================
    const synForm = document.getElementById('mayhenTuViSynastryForm');
    const synDraft = bindFormDraft(synForm, 'tuvi_synastry');
    if (synDraft.hasRestored && synForm) {
        showDraftRestoredBanner(synForm, {
            onReset: () => { synDraft.clear(); synForm.reset(); },
        });
    }
    synForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(synForm);
        const btn = synForm.querySelector('button[type="submit"]');
        const err = document.getElementById('mayhenTuViSynastryErr');
        if (err) err.style.display = 'none';
        if (btn) { btn.disabled = true; btn.textContent = '✦ Đang Soi Chiếu Song Mệnh...'; }
        try {
            const res = await fetch(synForm.getAttribute('action') || '', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    chart_a_id: Number(fd.get('chart_a_id')),
                    chart_b_id: Number(fd.get('chart_b_id')),
                    partner_consent_confirmed: Boolean(fd.get('partner_consent_confirmed')),
                    couple_note: String(fd.get('couple_note') || ''),
                }),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                if (err) { err.textContent = data.message || 'Vui lòng chọn 2 lá số khác nhau và xác nhận sự đồng thuận.'; err.style.display = 'block'; }
                return;
            }
            if (data.result_url) {
                synDraft.clear();
                window.location.assign(data.result_url);
            }
        } finally {
            if (btn) { btn.disabled = false; btn.textContent = '✦ Khám Phá Nhân Duyên Song Mệnh'; }
        }
    });

    // =========================================================================
    // 9. TÌM KIẾM KHÔNG DẤU TRONG BÁCH KHOA TỬ VI
    // =========================================================================
    const wikiSearch = document.getElementById('mayhenTuViWikiSearch');
    if (wikiSearch) {
        const items = Array.from(document.querySelectorAll('[data-tuvi-wiki-item]'));
        wikiSearch.addEventListener('input', () => {
            const q = stripTones(wikiSearch.value.trim());
            items.forEach((item) => {
                const hay = stripTones(item.getAttribute('data-tuvi-wiki-item') || '');
                item.style.display = q === '' || hay.includes(q) ? '' : 'none';
            });
        });
    }
});
