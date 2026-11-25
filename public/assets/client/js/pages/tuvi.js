/**
 * MAYHEN.VN — MÂY HẸN – THIÊN MỆNH (TỬ VI ĐẨU SỐ 3D & THIÊN BÀN TƯƠNG TÁC)
 * 100% Pure Vanilla JS ES Module • Zero External Libraries
 */
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
                <p style="font-size:12.5px; color:#6B5F52; margin:0;">
                    <strong>Tam Phương Tứ Chính:</strong> Tam hợp với ${(rel.triad_palace_codes || []).map((c) => escapeHtml(chartData.palaces_by_code[c]?.name || c)).join(' & ')} • Xung chiếu: ${escapeHtml(chartData.palaces_by_code[rel.opposite_palace_code]?.name || '')}.
                    ${digest.vcd_note ? `<br><strong>Ghi chú Vô Chính Diệu:</strong> ${escapeHtml(digest.vcd_note)}` : ''}
                </p>
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
    // 4. THIÊN BÀN 4x4 TƯƠNG TÁC (HIGHLIGHT TAM PHƯƠNG TỨ CHÍNH + XUNG CHIẾU)
    // =========================================================================
    function initInteractiveBoard(container, chartData, ruleAnalysis) {
        if (!container || !chartData?.palaces_by_branch) return;
        const cells = Array.from(container.querySelectorAll('.mayhen_tuvi_palace_cell'));
        const inspector = container.querySelector('[data-tuvi-board-inspector]');
        if (cells.length === 0) return;

        function selectBranch(branchIdx) {
            const b = Number(branchIdx);
            const triadA = (b + 4) % 12;
            const triadB = (b + 8) % 12;
            const opp = (b + 6) % 12;

            cells.forEach((cell) => {
                const cb = Number(cell.getAttribute('data-branch-idx'));
                cell.classList.toggle('is-selected', cb === b);
                cell.classList.toggle('is-triad', cb === triadA || cb === triadB);
                cell.classList.toggle('is-opposite', cb === opp);
            });

            const p = chartData.palaces_by_branch[b];
            const digest = ruleAnalysis?.palace_digests?.[p?.code] || {};
            if (p && inspector) {
                inspector.innerHTML = `
                    <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:8px;">
                        <strong style="color:#85182A; font-size:16px;">${escapeHtml(p.name)} (${escapeHtml(p.can_chi)}) — Đại Hạn ${p.dai_han_start}–${p.dai_han_end} tuổi</strong>
                        <span style="font-size:12px; color:#1E4D3B; font-weight:700;">Tam hợp: ${escapeHtml(chartData.palaces_by_branch[triadA]?.name)} &amp; ${escapeHtml(chartData.palaces_by_branch[triadB]?.name)} • Xung chiếu: ${escapeHtml(chartData.palaces_by_branch[opp]?.name)}</span>
                    </div>
                    <p style="margin:0 0 6px; font-size:13.5px;"><strong>Chính tinh:</strong> ${escapeHtml(digest.stars_headline || '')}</p>
                    <p style="margin:0; font-size:13.5px; color:#4A4038;">${escapeHtml(digest.lead_star_insight || '')} ${escapeHtml(digest.modern_meaning || '')}</p>
                `;
            }
        }

        cells.forEach((cell) => {
            cell.addEventListener('click', () => selectBranch(cell.getAttribute('data-branch-idx')));
        });
        selectBranch(chartData.summary?.menh_branch_index ?? 2);
    }

    // =========================================================================
    // 5. XUẤT ẢNH LÁ SỐ TỬ VI HIDPI PNG (TÙY CHỌN ẨN TÊN & NGÀY GIỜ SINH)
    // =========================================================================
    function exportTuViChartPng(chartData, hideBirthInfo) {
        if (!chartData?.palaces_by_branch) return;
        const canvas = document.createElement('canvas');
        const W = 1440;
        const H = 1080;
        canvas.width = W;
        canvas.height = H;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        ctx.fillStyle = '#FCFBF9';
        ctx.fillRect(0, 0, W, H);
        ctx.strokeStyle = '#CFA860';
        ctx.lineWidth = 4;
        ctx.strokeRect(20, 20, W - 40, H - 40);

        const gridPos = {
            5: [0, 0], 6: [1, 0], 7: [2, 0], 8: [3, 0],
            4: [0, 1], 9: [3, 1],
            3: [0, 2], 10: [3, 2],
            2: [0, 3], 1: [1, 3], 0: [2, 3], 11: [3, 3],
        };
        const pad = 34;
        const gap = 10;
        const cw = (W - pad * 2 - gap * 3) / 4;
        const ch = (H - pad * 2 - gap * 3) / 4;

        chartData.palaces_by_branch.forEach((p) => {
            const [col, row] = gridPos[p.branch_index] || [0, 0];
            const x = pad + col * (cw + gap);
            const y = pad + row * (ch + gap);

            ctx.fillStyle = p.is_menh ? '#FAF4E8' : '#FFFFFF';
            ctx.fillRect(x, y, cw, ch);
            ctx.strokeStyle = p.is_menh ? '#85182A' : '#DFD2BE';
            ctx.lineWidth = p.is_menh ? 2.5 : 1.5;
            ctx.strokeRect(x, y, cw, ch);

            ctx.fillStyle = '#85182A';
            ctx.font = 'bold 16px Georgia, serif';
            ctx.fillText(`${p.short_name.toUpperCase()}${p.is_than ? ' (THÂN)' : ''}`, x + 12, y + 26);

            ctx.fillStyle = '#6E6256';
            ctx.font = 'bold 13px sans-serif';
            ctx.fillText(p.can_chi, x + cw - 78, y + 26);

            let cy = y + 54;
            ctx.fillStyle = '#1F1915';
            ctx.font = 'bold 16px Georgia, serif';
            if ((p.major_stars || []).length > 0) {
                p.major_stars.forEach((s) => {
                    ctx.fillText(`${s.name} (${s.brightness})`, x + 12, cy);
                    cy += 22;
                });
            } else {
                ctx.fillStyle = '#7A6E62';
                ctx.fillText('Vô Chính Diệu', x + 12, cy);
                cy += 22;
            }

            ctx.fillStyle = '#4A4038';
            ctx.font = '12.5px sans-serif';
            const auxText = (p.aux_stars || []).slice(0, 6).map((a) => a.name).join(', ');
            ctx.fillText(auxText.slice(0, 38), x + 12, cy + 8);
            if (auxText.length > 38) {
                ctx.fillText(auxText.slice(38, 76), x + 12, cy + 26);
            }

            ctx.fillStyle = '#6B5F52';
            ctx.font = 'bold 12px sans-serif';
            ctx.fillText(`ĐH: ${p.dai_han_start}–${p.dai_han_end}`, x + 12, y + ch - 14);
            ctx.fillText(p.ring_trang_sinh || '', x + cw - 92, y + ch - 14);
        });

        // Khung giữa Thiên Bàn
        const cx = pad + cw + gap;
        const cy = pad + ch + gap;
        const cW = cw * 2 + gap;
        const cH = ch * 2 + gap;
        ctx.fillStyle = '#FAF5EB';
        ctx.fillRect(cx, cy, cW, cH);
        ctx.strokeStyle = '#CFA860';
        ctx.lineWidth = 2.5;
        ctx.strokeRect(cx, cy, cW, cH);

        ctx.textAlign = 'center';
        ctx.fillStyle = '#85182A';
        ctx.font = 'bold 28px Georgia, serif';
        ctx.fillText('MÂY HẸN · THIÊN MỆNH', cx + cW / 2, cy + 72);

        ctx.fillStyle = '#1F1915';
        ctx.font = 'bold 20px Georgia, serif';
        ctx.fillText(hideBirthInfo ? 'Lá Số Ẩn Danh' : (chartData.nickname || 'Lá Số Thiên Mệnh'), cx + cW / 2, cy + 114);

        ctx.fillStyle = '#4A4038';
        ctx.font = '16px sans-serif';
        const infoLines = [
            hideBirthInfo ? 'Thông tin ngày giờ sinh đã được ẩn riêng tư' : `Dương lịch: ${chartData.birth?.solar?.label || ''} • Âm lịch: ${chartData.birth?.lunar?.label || ''}`,
            `Bản Mệnh: ${chartData.summary?.nap_am?.name || ''} (${chartData.summary?.nap_am?.element || ''})`,
            `Ngũ Hành Cục: ${chartData.summary?.cuc?.name || ''} • ${chartData.yin_yang_gender_label || ''}`,
            `Mệnh tại ${chartData.summary?.menh_branch_name || ''} • Thân cư ${chartData.summary?.than_palace_name || ''}`,
            `Trường phái: ${chartData.school_name || 'Nam Phái Việt Nam'}`,
            'mayhen.vn/tu-vi — Hiểu mình, hiểu người, vun đắp nhân duyên',
        ];
        infoLines.forEach((line, i) => {
            ctx.fillText(line, cx + cW / 2, cy + 162 + i * 34);
        });

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
        initInteractiveBoard(showApp, chartJson, ruleJson);

        // 5 góc nhìn tab
        const tabs = showApp.querySelectorAll('.mayhen_tuvi_view_tab');
        const panels = showApp.querySelectorAll('.mayhen_tuvi_view_panel');
        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const target = tab.getAttribute('data-view');
                tabs.forEach((t) => t.classList.toggle('is-active', t === tab));
                panels.forEach((p) => p.classList.toggle('is-active', p.getAttribute('data-view-panel') === target));
            });
        });

        // Xuất PNG HiDPI
        showApp.querySelector('[data-tuvi-export-png]')?.addEventListener('click', () => {
            const hideCb = showApp.querySelector('[data-tuvi-export-hide-pii]');
            exportTuViChartPng(chartJson, Boolean(hideCb?.checked));
        });

        // Hỏi sâu AI
        const fuForm = showApp.querySelector('[data-tuvi-followup-form]');
        const fuList = showApp.querySelector('[data-tuvi-followup-list]');
        fuForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(fuForm);
            const q = String(fd.get('question') || '').trim();
            if (q.length < 4) return;
            const btn = fuForm.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.textContent = 'Đang đối chiếu lá số...'; }
            try {
                const res = await fetch(endpoints.followup, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ question: q, palace_code: String(fd.get('palace_code') || '') || null }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    alert(data.message || 'Không thể gửi câu hỏi lúc này.');
                    return;
                }
                if (fuList && data.followup) {
                    const item = document.createElement('div');
                    item.className = 'mayhen_tuvi_card';
                    item.style.padding = '12px 14px';
                    item.innerHTML = `<div style="font-weight:700; color:#85182A; margin-bottom:4px;">Hỏi: ${escapeHtml(data.followup.question)}</div><p style="font-size:13.5px;">${escapeHtml(data.followup.answer)}</p>`;
                    fuList.appendChild(item);
                }
                fuForm.reset();
            } finally {
                if (btn) { btn.disabled = false; btn.textContent = 'Hỏi Sâu Trên Lá Số Này'; }
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

        // Luận giải lại AI
        const reBtn = showApp.querySelector('[data-tuvi-reinterpret-btn]');
        reBtn?.addEventListener('click', async () => {
            reBtn.disabled = true;
            reBtn.textContent = 'Đang chạy lại AI 6 tầng...';
            const res = await fetch(endpoints.reinterpret, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });
            if (res.ok) window.location.reload();
            else { alert('AI tạm gián đoạn, vui lòng thử lại sau.'); reBtn.disabled = false; }
        });

        return;
    }

    // =========================================================================
    // 7. TRANG CHÍNH (/tu-vi): 1 LUỒNG DUY NHẤT + LIVE PREVIEW + NGHI THỨC KHAI BÀI
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

        // Chế độ giờ sinh dạng 4 nút trực quan 1 chạm
        let birthTimeMode = 'exact_time';
        const timeModeHidden = document.getElementById('tuviBirthTimeMode');
        const timeModeBtns = landingApp.querySelectorAll('[data-time-mode-btn]');

        function syncTimeModeFields(mode) {
            birthTimeMode = mode || 'exact_time';
            if (timeModeHidden) timeModeHidden.value = birthTimeMode;
            timeModeBtns.forEach((b) => b.classList.toggle('is-active', b.getAttribute('data-time-mode-btn') === birthTimeMode));
            landingApp.querySelectorAll('[data-time-field]').forEach((el) => {
                el.style.display = el.getAttribute('data-time-field') === birthTimeMode ? '' : 'none';
            });
            scheduleCalendarPreview();
        }
        timeModeBtns.forEach((btn) => {
            btn.addEventListener('click', () => syncTimeModeFields(btn.getAttribute('data-time-mode-btn')));
        });
        syncTimeModeFields('exact_time');

        // Toggle tháng nhuận khi chọn Âm lịch
        const calTypeSel = document.getElementById('tuviCalendarType');
        const leapWrap = document.getElementById('tuviLeapMonthWrap');
        calTypeSel?.addEventListener('change', () => {
            if (leapWrap) leapWrap.style.display = calTypeSel.value === 'lunar' ? '' : 'none';
            scheduleCalendarPreview();
        });

        // Chủ đề trọng tâm
        let focusTopic = 'toan_bo';
        landingApp.querySelectorAll('[data-tuvi-topic]').forEach((btn) => {
            btn.addEventListener('click', () => {
                focusTopic = btn.getAttribute('data-tuvi-topic') || 'toan_bo';
                landingApp.querySelectorAll('[data-tuvi-topic]').forEach((b) => b.classList.toggle('is-active', b === btn));
            });
        });

        function collectWizardPayload() {
            return {
                nickname: (document.getElementById('tuviNickname')?.value || '').trim(),
                gender_yin_yang: document.getElementById('tuviGender')?.value || 'female',
                calendar_type: calTypeSel?.value || 'solar',
                birth_year: Number(document.getElementById('tuviBirthYear')?.value || 1995),
                birth_month: Number(document.getElementById('tuviBirthMonth')?.value || 8),
                birth_day: Number(document.getElementById('tuviBirthDay')?.value || 15),
                is_leap_month: Boolean(document.getElementById('tuviIsLeapMonth')?.checked),
                birth_time_mode: birthTimeMode,
                birth_hour: Number(document.getElementById('tuviBirthHour')?.value ?? 6),
                birth_minute: Number(document.getElementById('tuviBirthMinute')?.value ?? 30),
                birth_branch_index: Number(document.getElementById('tuviBirthBranch')?.value ?? 3),
                birth_time_window: document.getElementById('tuviBirthWindow')?.value || 'morning',
                birth_timezone: document.getElementById('tuviTimezone')?.value || 'Asia/Ho_Chi_Minh',
                leap_month_rule: document.getElementById('tuviLeapRule')?.value || 'split_15',
                late_rat_rule: document.getElementById('tuviLateRatRule')?.value || 'next_lunar_day',
                viewed_year: Number(document.getElementById('tuviViewedYear')?.value || new Date().getFullYear()),
                focus_topic: focusTopic,
            };
        }

        let previewTimer = 0;
        function scheduleCalendarPreview() {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(refreshCalendarPreview, 320);
        }

        ['tuviBirthDay', 'tuviBirthMonth', 'tuviBirthYear', 'tuviIsLeapMonth', 'tuviBirthHour', 'tuviBirthMinute', 'tuviBirthBranch', 'tuviBirthWindow', 'tuviTimezone', 'tuviLeapRule', 'tuviLateRatRule'].forEach((id) => {
            const el = document.getElementById(id);
            el?.addEventListener('change', scheduleCalendarPreview);
            el?.addEventListener('input', scheduleCalendarPreview);
        });

        async function refreshCalendarPreview() {
            const box = document.getElementById('tuviCalendarPreviewBox');
            if (!box || !previewUrl) return;
            try {
                const res = await fetch(previewUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(collectWizardPayload()),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    box.innerHTML = `<strong style="color:#B91C1C;">${escapeHtml(data.message || 'Ngày giờ sinh chưa hợp lệ.')}</strong>`;
                    return;
                }
                const b = data.birth;
                box.innerHTML = `
                    <div><strong>Đối chiếu tức thời:</strong> Dương lịch <strong>${escapeHtml(b.solar.label)}</strong> ↔ Âm lịch <strong>${escapeHtml(b.lunar.label)}</strong></div>
                    <div><strong>Bản Mệnh:</strong> ${escapeHtml(b.nap_am.name)} (hành ${escapeHtml(b.nap_am.element)}) • <strong>Giờ Địa Chi:</strong> ${escapeHtml(b.pillars.hour ? b.pillars.hour.label + ' (' + b.pillars.hour.range + ')' : 'Chưa xác định giờ cụ thể')}</div>
                    ${(b.notes || []).length ? `<div style="margin-top:4px; color:#85182A;">${b.notes.map(escapeHtml).join(' ')}</div>` : ''}
                `;
            } catch {
                // Giữ nguyên nội dung xem trước gần nhất
            }
        }
        refreshCalendarPreview();

        // Nghi thức chuyển cảnh 1 luồng mượt khi lập lá số & luận giải AI
        const ritualOverlay = document.getElementById('mayhenTuViRitualOverlay');
        const ritualSteps = ritualOverlay ? Array.from(ritualOverlay.querySelectorAll('[data-ritual-step]')) : [];
        let ritualInterval = 0;

        function startRitualOverlay() {
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
            }, 950);
        }

        function stopRitualOverlay() {
            clearInterval(ritualInterval);
            ritualOverlay?.classList.remove('is-open');
        }

        const submitBtn = document.getElementById('tuviSubmitGenerateBtn');
        const errBox = document.getElementById('tuviWizardErrorBox');
        let idemKey = 'tv_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 8);

        submitBtn?.addEventListener('click', async () => {
            if (!canUseFree) {
                document.getElementById('mayhenTuViLoginModal')?.classList.add('is-open');
                return;
            }
            if (errBox) errBox.style.display = 'none';
            submitBtn.disabled = true;
            const oldTxt = submitBtn.textContent;
            submitBtn.textContent = '✦ Đang Lập Thiên Bàn & Luận Giải...';
            startRitualOverlay();
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
                ritualSteps.forEach((s) => { s.classList.remove('is-active'); s.classList.add('is-done'); });
                if (data.result_url) {
                    window.location.assign(data.result_url);
                }
            } catch {
                stopRitualOverlay();
                if (errBox) {
                    errBox.textContent = 'Lỗi kết nối máy chủ. Vui lòng thử lại sau giây lát.';
                    errBox.style.display = 'block';
                }
            } finally {
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
            if (data.result_url) window.location.assign(data.result_url);
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
