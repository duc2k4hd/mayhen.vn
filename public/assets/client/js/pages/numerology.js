/**
 * MAYHEN.VN — TRA CỨU THẦN SỐ HỌC ONLINE & THẦN SỐ HỌC TÌNH DUYÊN MIỄN PHÍ
 * 100% Pure Vanilla JS ES Module • Editorial Mathematical Art • Zero External Libraries
 */
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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
    // 1. BỘ 12 TÁC PHẨM HÌNH HỌC TOÁN HỌC NGUYÊN BẢN (1..9, 11, 22, 33)
    // =========================================================================
    function buildNumberGeometrySvg(num, stroke = '#85182A') {
        const s = escapeHtml(stroke);
        const map = {
            '1': `<circle cx="50" cy="50" r="34" fill="none" stroke="${s}" stroke-width="1" stroke-dasharray="2.5 4" opacity="0.58"/><circle cx="50" cy="50" r="18" fill="none" stroke="${s}" stroke-width="1.2" opacity="0.45"/><line x1="50" y1="12" x2="50" y2="88" stroke="${s}" stroke-width="1.8" stroke-linecap="round"/><circle cx="50" cy="50" r="5" fill="${s}"/><circle cx="50" cy="18" r="2.6" fill="${s}"/>`,
            '2': `<path d="M26 22 C56 22, 56 78, 26 78" stroke="${s}" stroke-width="1.6" fill="none" stroke-linecap="round"/><path d="M74 22 C44 22, 44 78, 74 78" stroke="${s}" stroke-width="1.6" fill="none" stroke-linecap="round"/><circle cx="50" cy="50" r="32" fill="none" stroke="${s}" stroke-width="0.8" opacity="0.35"/><circle cx="50" cy="50" r="4" fill="${s}"/>`,
            '3': `<polygon points="50,14 84,78 16,78" stroke="${s}" stroke-width="1.6" fill="none" stroke-linejoin="round"/><polygon points="50,30 70,68 30,68" stroke="${s}" stroke-width="1" fill="none" opacity="0.65"/><circle cx="50" cy="56" r="3.8" fill="${s}"/>`,
            '4': `<rect x="20" y="20" width="60" height="60" stroke="${s}" stroke-width="1.6" fill="none"/><rect x="33" y="33" width="34" height="34" stroke="${s}" stroke-width="1" fill="none" opacity="0.68"/><line x1="20" y1="50" x2="80" y2="50" stroke="${s}" stroke-width="0.9" opacity="0.48"/><line x1="50" y1="20" x2="50" y2="80" stroke="${s}" stroke-width="0.9" opacity="0.48"/><circle cx="50" cy="50" r="3.2" fill="${s}"/>`,
            '5': `<polygon points="50,12 86,38 72,82 28,82 14,38" stroke="${s}" stroke-width="1.6" fill="none" stroke-linejoin="round"/><polygon points="50,24 72,72 20,44 80,44 28,72" stroke="${s}" stroke-width="0.95" fill="none" opacity="0.68"/><circle cx="50" cy="50" r="3" fill="${s}"/>`,
            '6': `<polygon points="50,12 82,31 82,69 50,88 18,69 18,31" stroke="${s}" stroke-width="1.5" fill="none" stroke-linejoin="round"/><circle cx="50" cy="50" r="19" stroke="${s}" stroke-width="1.15" fill="none"/><circle cx="50" cy="50" r="4" fill="${s}"/>`,
            '7': `<circle cx="50" cy="50" r="36" stroke="${s}" stroke-width="1.4" fill="none"/><circle cx="50" cy="50" r="24" stroke="${s}" stroke-width="1.15" fill="none" opacity="0.8"/><circle cx="50" cy="50" r="13" stroke="${s}" stroke-width="1" fill="none" opacity="0.7"/><circle cx="50" cy="50" r="3.8" fill="${s}"/>`,
            '8': `<circle cx="50" cy="33" r="18" stroke="${s}" stroke-width="1.6" fill="none"/><circle cx="50" cy="67" r="18" stroke="${s}" stroke-width="1.6" fill="none"/><line x1="22" y1="50" x2="78" y2="50" stroke="${s}" stroke-width="0.9" stroke-dasharray="2.5 3" opacity="0.58"/><circle cx="50" cy="50" r="3" fill="${s}"/>`,
            '9': `<circle cx="50" cy="50" r="35" stroke="${s}" stroke-width="1.6" fill="none"/><polygon points="50,15 80,67 20,67" stroke="${s}" stroke-width="1" fill="none" opacity="0.62"/><polygon points="50,85 20,33 80,33" stroke="${s}" stroke-width="1" fill="none" opacity="0.62"/><circle cx="50" cy="50" r="3.4" fill="${s}"/>`,
            '11': `<circle cx="50" cy="50" r="35" fill="none" stroke="${s}" stroke-width="0.9" stroke-dasharray="2.5 4" opacity="0.55"/><line x1="36" y1="16" x2="36" y2="84" stroke="${s}" stroke-width="2" stroke-linecap="round"/><line x1="64" y1="16" x2="64" y2="84" stroke="${s}" stroke-width="2" stroke-linecap="round"/><line x1="26" y1="50" x2="74" y2="50" stroke="${s}" stroke-width="1" stroke-dasharray="3 3" opacity="0.75"/><circle cx="36" cy="24" r="3.4" fill="${s}"/><circle cx="64" cy="24" r="3.4" fill="${s}"/>`,
            '22': `<rect x="19" y="19" width="62" height="62" stroke="${s}" stroke-width="1.6" fill="none"/><polygon points="50,17 83,50 50,83 17,50" stroke="${s}" stroke-width="1.3" fill="none"/><rect x="35" y="35" width="30" height="30" stroke="${s}" stroke-width="1" fill="none" opacity="0.8"/><circle cx="50" cy="50" r="3.4" fill="${s}"/>`,
            '33': `<circle cx="50" cy="50" r="35" stroke="${s}" stroke-width="1.3" fill="none"/><polygon points="50,16 80,68 20,68" stroke="${s}" stroke-width="1.4" fill="none"/><polygon points="50,84 20,32 80,32" stroke="${s}" stroke-width="1.4" fill="none"/><circle cx="50" cy="50" r="4.6" fill="${s}"/>`,
        };
        return `<svg viewBox="0 0 100 100" width="66" height="66" aria-hidden="true">${map[String(num)] || map['9']}</svg>`;
    }

    // =========================================================================
    // 2. XÒE QUẠT BỘ THẺ NGHỆ THUẬT CON SỐ (100% TRÊN 1 MÀN — ZERO SCROLLBAR)
    // =========================================================================
    function initArtCardsFan(container, items) {
        if (!container || !Array.isArray(items) || items.length === 0) return;
        const stage = container.querySelector('[data-num-fan-stage]');
        const hand = container.querySelector('[data-num-fan-hand]');
        const pillsWrap = container.querySelector('[data-num-fan-pills]');
        const drawer = container.querySelector('[data-num-card-drawer]');
        if (!hand) return;

        hand.innerHTML = items.map((item, idx) => {
            const isMaster = Boolean(item.is_master) || ['11', '22', '33'].includes(String(item.value));
            const stroke = isMaster ? '#ECCB82' : '#85182A';
            const shortKicker = item.kicker === 'Con Số Cơ Bản' ? 'Cơ Bản' : (item.kicker === 'Master Number' ? 'Master' : (item.kicker || 'Dấu Ấn'));
            return `
                <div class="mayhen_numerology_art_card" tabindex="0" role="button" aria-pressed="false" aria-label="Mở thẻ số ${escapeHtml(item.display)} — ${escapeHtml(item.title)}" data-card-idx="${idx}">
                    <div class="mayhen_numerology_art_card_inner">
                        <div class="mayhen_numerology_art_face mayhen_numerology_art_front ${isMaster ? 'mayhen_numerology_art_front--master' : ''}">
                            <div class="mayhen_numerology_card_head">
                                <span class="mayhen_numerology_card_pip">
                                    <span class="mayhen_numerology_card_pip_num">${escapeHtml(item.display)}</span>
                                    <span class="mayhen_numerology_card_pip_dot" aria-hidden="true">${isMaster ? '✦' : '◆'}</span>
                                </span>
                                <span class="mayhen_numerology_card_kicker">${escapeHtml(shortKicker)}</span>
                            </div>
                            <div class="mayhen_numerology_card_svg">${buildNumberGeometrySvg(item.value, stroke)}</div>
                            <div class="mayhen_numerology_card_foot">
                                <div class="mayhen_numerology_card_numeral">${escapeHtml(item.display)}</div>
                                <div class="mayhen_numerology_card_title">${escapeHtml(item.title)}</div>
                            </div>
                        </div>
                        <div class="mayhen_numerology_art_face mayhen_numerology_art_back ${isMaster ? 'mayhen_numerology_art_back--master' : ''}">
                            <div>
                                <div class="mayhen_numerology_back_kicker">${escapeHtml(item.kicker || ('Số ' + item.display))}</div>
                                <div class="mayhen_numerology_back_num">${escapeHtml(item.display)}</div>
                                <div class="mayhen_numerology_back_title">${escapeHtml(item.title)}</div>
                            </div>
                            <p class="mayhen_numerology_back_summary">${escapeHtml(item.short_meaning || '')}</p>
                            <div><span class="mayhen_numerology_back_hint">Đọc chi tiết &darr;</span></div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        if (pillsWrap) {
            const hasDistinctKickers = items.some((it) => it.kicker && it.kicker !== 'Con Số Cơ Bản' && it.kicker !== 'Master Number');
            pillsWrap.innerHTML = items.map((item, idx) => {
                const isMaster = Boolean(item.is_master) || ['11', '22', '33'].includes(String(item.value));
                const pillLabel = hasDistinctKickers
                    ? `${escapeHtml(item.kicker)} · ${escapeHtml(item.display)}`
                    : escapeHtml(item.display);
                return `<button type="button" class="mayhen_numerology_fan_pill ${isMaster ? 'mayhen_numerology_fan_pill--master' : ''}" role="tab" aria-selected="false" data-pill-idx="${idx}">${pillLabel}</button>`;
            }).join('');
        }

        const wraps = Array.from(hand.querySelectorAll('.mayhen_numerology_art_card'));
        const pills = pillsWrap ? Array.from(pillsWrap.querySelectorAll('.mayhen_numerology_fan_pill')) : [];
        const count = wraps.length;
        let activeIdx = -1;

        function applyNeighborShift(focusIdx, strength) {
            if (window.innerWidth < 920 || reduceMotion || focusIdx < 0) {
                wraps.forEach((o) => o.style.setProperty('--shift-x', '0px'));
                return;
            }
            wraps.forEach((other, i) => {
                if (i < focusIdx) {
                    const dist = focusIdx - i;
                    other.style.setProperty('--shift-x', `${Math.max(-strength, -(strength * 1.15) / Math.sqrt(dist)).toFixed(1)}px`);
                } else if (i > focusIdx) {
                    const dist = i - focusIdx;
                    other.style.setProperty('--shift-x', `${Math.min(strength, (strength * 1.15) / Math.sqrt(dist)).toFixed(1)}px`);
                } else {
                    other.style.setProperty('--shift-x', '0px');
                }
            });
        }

        function layoutFan() {
            const stageW = Math.max(300, (stage || hand).getBoundingClientRect().width || window.innerWidth);
            const isCompact = stageW < 920 && count > 6;

            if (!isCompact) {
                // Quạt vòng cung cực tọa độ thực sự (Polar Arc) như xòe bộ bài trên tay
                const cardW = stageW < 580 ? 88 : (stageW < 920 ? 106 : 146);
                const cardH = stageW < 580 ? 142 : (stageW < 920 ? 168 : 226);
                const maxAngleDeg = count >= 11 ? 31 : (count >= 7 ? 24.5 : 16);
                const maxAngleRad = (maxAngleDeg * Math.PI) / 180;
                const sinMax = Math.sin(maxAngleRad) || 0.4;

                // Tính bán kính tối ưu vừa khít chiều ngang mà không bao giờ cắt góc lá ngoài cùng
                const outerHalfW = (cardW * Math.cos(maxAngleRad) + cardH * Math.sin(maxAngleRad)) * 0.56;
                const availableHalfX = Math.max(120, (stageW / 2) - outerHalfW - 14);
                const idealRadius = count >= 11 ? 650 : 610;
                const radius = Math.min(idealRadius, availableHalfX / sinMax);
                const mid = (count - 1) / 2;

                wraps.forEach((w, i) => {
                    const t = mid > 0 ? (i - mid) / mid : 0;
                    const thetaDeg = t * maxAngleDeg;
                    const thetaRad = (thetaDeg * Math.PI) / 180;
                    const x = radius * Math.sin(thetaRad);
                    const y = radius * (1 - Math.cos(thetaRad));
                    w.style.setProperty('--fan-x', `${x.toFixed(1)}px`);
                    w.style.setProperty('--fan-y', `${y.toFixed(1)}px`);
                    w.style.setProperty('--fan-rot', `${thetaDeg.toFixed(1)}deg`);
                    w.style.setProperty('--fan-z', String(10 + i));
                });
            } else {
                // Mobile & Tablet: 2 vòng cung xếp tầng gọn gàng, không thanh cuộn ngang
                const half = Math.ceil(count / 2);
                const cardW = stageW < 580 ? 88 : 106;
                const cardH = stageW < 580 ? 142 : 168;
                const maxAngleDeg = 18;
                const maxAngleRad = (maxAngleDeg * Math.PI) / 180;
                const sinMax = Math.sin(maxAngleRad);
                const outerHalfW = (cardW * Math.cos(maxAngleRad) + cardH * Math.sin(maxAngleRad)) * 0.54;
                const availableHalfX = Math.max(80, (stageW / 2) - outerHalfW - 10);
                const radius = Math.min(430, availableHalfX / sinMax);
                const row2Offset = stageW < 580 ? 156 : 182;

                wraps.forEach((w, i) => {
                    const row = i < half ? 0 : 1;
                    const col = row === 0 ? i : i - half;
                    const rowCount = row === 0 ? half : count - half;
                    const mid = (rowCount - 1) / 2;
                    const t = mid > 0 ? (col - mid) / mid : 0;
                    const thetaDeg = t * maxAngleDeg;
                    const thetaRad = (thetaDeg * Math.PI) / 180;
                    const x = radius * Math.sin(thetaRad);
                    const y = (row === 0 ? 0 : row2Offset) + radius * (1 - Math.cos(thetaRad));
                    w.style.setProperty('--fan-x', `${x.toFixed(1)}px`);
                    w.style.setProperty('--fan-y', `${y.toFixed(1)}px`);
                    w.style.setProperty('--fan-rot', `${thetaDeg.toFixed(1)}deg`);
                    w.style.setProperty('--fan-z', String((row === 0 ? 10 : 24) + col));
                });
            }
            if (activeIdx >= 0) {
                applyNeighborShift(activeIdx, 36);
            }
        }

        layoutFan();
        window.addEventListener('resize', layoutFan);

        // Hiệu ứng xòe bộ bài mượt mà khi cuộn tới sân khấu
        if (!reduceMotion && 'IntersectionObserver' in window) {
            hand.classList.add('is-stacked');
            const io = new IntersectionObserver((entries) => {
                if (entries.some((e) => e.isIntersecting)) {
                    requestAnimationFrame(() => hand.classList.remove('is-stacked'));
                    io.disconnect();
                }
            }, { threshold: 0.2 });
            io.observe(stage || hand);
        }

        function resetFan() {
            activeIdx = -1;
            wraps.forEach((w) => {
                w.classList.remove('is-flipped', 'is-selected', 'is-dimmed');
                w.setAttribute('aria-pressed', 'false');
                w.style.setProperty('--shift-x', '0px');
            });
            pills.forEach((p) => {
                p.classList.remove('is-active');
                p.setAttribute('aria-selected', 'false');
            });
            drawer?.classList.remove('is-open');
        }

        function openDrawer(idx) {
            const item = items[idx];
            if (!item || !drawer) return;
            const isMaster = Boolean(item.is_master) || ['11', '22', '33'].includes(String(item.value));
            const stroke = isMaster ? '#ECCB82' : '#85182A';
            drawer.innerHTML = `
                <div class="mayhen_numerology_drawer_grid">
                    <div class="mayhen_numerology_drawer_emblem ${isMaster ? 'mayhen_numerology_drawer_emblem--master' : ''}">
                        ${buildNumberGeometrySvg(item.value, stroke)}
                        <div class="mayhen_numerology_drawer_emblem_num">${escapeHtml(item.display)}</div>
                        <div class="mayhen_numerology_drawer_emblem_tag">${escapeHtml(item.kicker || (isMaster ? 'Master Number' : 'Con Số Cơ Bản'))}</div>
                    </div>
                    <div>
                        <div class="mayhen_numerology_drawer_top">
                            <div>
                                <span class="mayhen_numerology_chapter_tag">${escapeHtml(item.kicker || 'Nghệ Thuật Con Số')}</span>
                                <h3 class="mayhen_numerology_drawer_title">Số ${escapeHtml(item.display)} — ${escapeHtml(item.title)}</h3>
                            </div>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            ${item.code ? `<button type="button" class="mayhen_numerology_mini_btn" data-num-concept-lookup="${escapeHtml(item.code)}">? Giải mã</button>` : ''}
                            ${item.code ? `<button type="button" class="mayhen_numerology_mini_btn mayhen_numerology_mini_btn--ai" data-num-ask-trigger data-scope-type="indicator" data-topic-code="ind:${escapeHtml(item.code)}" data-scope-label="${escapeHtml(item.kicker)} (${escapeHtml(item.display)})">✦ Hỏi AI</button>` : ''}
                            <button type="button" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm" data-num-prev>&larr; Thẻ trước</button>
                            <button type="button" class="mayhen_numerology_btn_primary mayhen_numerology_btn_sm" data-num-next>Thẻ tiếp &rarr;</button>
                        </div>
                        </div>
                        <p style="font-size:15px; line-height:1.74; color:#292524; margin:0;">${escapeHtml(item.full_meaning || item.short_meaning || '')}</p>
                        ${item.love_meaning ? `<div class="mayhen_numerology_drawer_love"><strong>Trong tình yêu &amp; hôn nhân:</strong> ${escapeHtml(item.love_meaning)}</div>` : ''}
                        ${item.reflection ? `<p class="mayhen_numerology_drawer_reflection"><strong>Câu hỏi chiêm nghiệm:</strong> “${escapeHtml(item.reflection)}”</p>` : ''}
                    </div>
                </div>
            `;
            drawer.classList.add('is-open');
            drawer.querySelector('[data-num-prev]')?.addEventListener('click', () => activate((idx - 1 + count) % count, true));
            drawer.querySelector('[data-num-next]')?.addEventListener('click', () => activate((idx + 1) % count, true));
        }

        function activate(idx, forceOpen = false) {
            if (!forceOpen && idx === activeIdx) {
                resetFan();
                return;
            }
            activeIdx = idx;
            wraps.forEach((w, i) => {
                const isTarget = i === idx;
                w.classList.toggle('is-selected', isTarget);
                w.classList.toggle('is-flipped', isTarget);
                w.classList.toggle('is-dimmed', !isTarget);
                w.setAttribute('aria-pressed', isTarget ? 'true' : 'false');
            });
            pills.forEach((p, i) => {
                const isTarget = i === idx;
                p.classList.toggle('is-active', isTarget);
                p.setAttribute('aria-selected', isTarget ? 'true' : 'false');
            });
            applyNeighborShift(idx, 34);
            openDrawer(idx);
        }

        wraps.forEach((w, idx) => {
            w.addEventListener('mouseenter', () => {
                if (activeIdx >= 0) return;
                applyNeighborShift(idx, 20);
            });
            w.addEventListener('mouseleave', () => {
                if (activeIdx >= 0) applyNeighborShift(activeIdx, 34);
                else wraps.forEach((o) => o.style.setProperty('--shift-x', '0px'));
            });
            w.addEventListener('click', () => activate(idx));
            w.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activate(idx); }
            });
        });

        pills.forEach((pill, idx) => {
            pill.addEventListener('click', () => activate(idx, true));
        });

        container.querySelector('[data-num-fan-reset]')?.addEventListener('click', resetFan);
        container.querySelector('[data-num-fan-random]')?.addEventListener('click', () => {
            let pick = Math.floor(Math.random() * count);
            if (count > 1 && pick === activeIdx) pick = (pick + 1) % count;
            activate(pick, true);
        });
    }

    // =========================================================================
    // 3. BIỂU ĐỒ NGÀY SINH 3×3 + ĐƯỜNG MŨI TÊN SVG (DAVID A. PHILLIPS)
    // =========================================================================
    function renderBirthChart3x3(container, birthChart, enriched) {
        if (!container || !birthChart?.cells) return;
        const gridOrder = [3, 6, 9, 2, 5, 8, 1, 4, 7];
        const coords = {
            3: [50, 50], 6: [150, 50], 9: [250, 50],
            2: [50, 150], 5: [150, 150], 8: [250, 150],
            1: [50, 250], 4: [150, 250], 7: [250, 250],
        };

        const arrowLines = (enriched?.arrows_enriched || []).map((ar) => {
            if (ar.status === 'partial') return '';
            const [a, , c] = ar.digits;
            const [x1, y1] = coords[a] || [50, 50];
            const [x2, y2] = coords[c] || [250, 250];
            const stroke = ar.status === 'full' ? '#1E4D3B' : '#B58445';
            const dash = ar.status === 'empty' ? 'stroke-dasharray="6 5"' : '';
            return `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="${stroke}" stroke-width="${ar.status === 'full' ? '3' : '1.8'}" ${dash} opacity="0.65"/>`;
        }).join('');

        const cellsHtml = gridOrder.map((d) => {
            const cell = birthChart.cells[d] || { count: 0, display: '' };
            return `
                <button type="button" class="mayhen_numerology_grid3x3_cell" data-grid-digit="${d}">
                    ${cell.count > 0
                        ? `<span class="mayhen_numerology_grid3x3_digits">${escapeHtml(cell.display)}</span>`
                        : `<span class="mayhen_numerology_grid3x3_empty">${d}</span>`}
                </button>
            `;
        }).join('');

        const arrowsSummaryHtml = (enriched?.arrows_enriched || [])
            .filter((ar) => ar.status !== 'partial' && ar.name)
            .map((ar) => `
                <div class="mayhen_numerology_note_box" style="margin-bottom:8px; border-left-color:${ar.status === 'full' ? '#1E4D3B' : '#CFA860'};">
                    <strong>${ar.status === 'full' ? 'Mũi tên đủ' : 'Mũi tên trống'} ${escapeHtml(ar.code)} — ${escapeHtml(ar.name)}:</strong> ${escapeHtml(ar.meaning)}
                </div>
            `).join('') || '<p style="font-size:13.5px; color:#5C5248;">Ngày sinh phân bố xen kẽ nên không hình thành đường mũi tên đủ 3 số hay trống cả 3 số — biểu thị sự cân bằng đa hướng.</p>';

        container.innerHTML = `
            <div class="mayhen_numerology_birthgrid_wrap">
                <div>
                    <div class="mayhen_numerology_grid3x3_box">
                        <svg class="mayhen_numerology_grid3x3_svg" viewBox="0 0 300 300" aria-hidden="true">${arrowLines}</svg>
                        ${cellsHtml}
                    </div>
                    <p style="font-size:11.5px; color:#6B5F52; margin-top:8px;">
                        Chạm từng ô (1–9) để xem ý nghĩa tần suất. ${escapeHtml(birthChart.source_note || '')}
                    </p>
                </div>
                <div>
                    <div data-grid-digit-inspector class="mayhen_numerology_note_box" style="margin-bottom:14px;">
                        Chạm vào bất kỳ ô số nào trong Biểu đồ ngày sinh 3×3 để đọc phân tích chi tiết tần suất xuất hiện.
                    </div>
                    <h4 style="font-family:'MayhenV3EditorialSerif',Georgia,serif; margin:0 0 8px;">Các Đường Mũi Tên Hiện Diện</h4>
                    ${arrowsSummaryHtml}
                </div>
            </div>
        `;

        const inspector = container.querySelector('[data-grid-digit-inspector]');
        const btns = container.querySelectorAll('[data-grid-digit]');
        btns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const d = Number(btn.getAttribute('data-grid-digit'));
                btns.forEach((b) => b.classList.toggle('is-active', b === btn));
                const cell = birthChart.cells[d] || { count: 0 };
                const note = enriched?.digit_notes?.[d] || {};
                if (inspector) {
                    const hasAiDrawer = Boolean(document.querySelector('[data-num-scoped-backdrop]'));
                    inspector.innerHTML = `
                        <div class="mayhen_numerology_note_inline_head">
                            <strong>Ô số ${d} (${escapeHtml(note.plane || '')}) — Xuất hiện ${cell.count} lần:</strong>
                            ${hasAiDrawer ? `<button type="button" class="mayhen_numerology_mini_btn mayhen_numerology_mini_btn--ai" data-num-ask-trigger data-scope-type="section" data-section-key="birth_chart" data-scope-label="Biểu Đồ Ngày Sinh 3×3 & Ô số ${d}" data-prefill-question="Ô số ${d} xuất hiện ${cell.count} lần trên Biểu đồ ngày sinh 3×3 của mình mang ý nghĩa và cách cân bằng gì?">✦ Hỏi AI ô ${d}</button>` : ''}
                        </div>
                        ${escapeHtml(note.note || '')}
                    `;
                }
            });
        });
    }

    // =========================================================================
    // 4. XUẤT THẺ NGHỆ THUẬT CON SỐ PNG HIDPI (1:1, 9:16, 4:5 — MẶC ĐỊNH ẨN NGÀY SINH)
    // =========================================================================
    async function exportArtCardPng(calc, interpretation, ratioKey, hideBirthDate) {
        if (document.fonts?.ready) {
            try { await document.fonts.ready; } catch {}
        }

        const dims = {
            '1:1': [1080, 1080],
            '9:16': [1080, 1920],
            '4:5': [1080, 1350],
        }[ratioKey] || [1080, 1080];

        const [W, H] = dims;
        const isStory = ratioKey === '9:16';
        const isPost = ratioKey === '4:5';
        const canvas = document.createElement('canvas');
        canvas.width = W;
        canvas.height = H;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const SERIF = '"MayhenV3EditorialSerif", "Times New Roman", serif';
        const SANS = '"Segoe UI", Arial, sans-serif';

        const drawRoundedRect = (x, y, w, h, r) => {
            ctx.beginPath();
            ctx.moveTo(x + r, y);
            ctx.arcTo(x + w, y, x + w, y + h, r);
            ctx.arcTo(x + w, y + h, x, y + h, r);
            ctx.arcTo(x, y + h, x, y, r);
            ctx.arcTo(x, y, x + w, y, r);
            ctx.closePath();
        };

        const wrapLines = (text, maxWidth, maxLines = 4) => {
            const words = String(text || '').trim().split(/\s+/).filter(Boolean);
            const lines = [];
            let current = '';
            for (const w of words) {
                const probe = current ? `${current} ${w}` : w;
                if (ctx.measureText(probe).width > maxWidth && current) {
                    lines.push(current);
                    current = w;
                    if (lines.length >= maxLines - 1) break;
                } else {
                    current = probe;
                }
            }
            if (current) {
                if (lines.length >= maxLines - 1 && words.length > lines.join(' ').split(' ').length + current.split(' ').length) {
                    current = `${current}…`;
                }
                lines.push(current);
            }
            return lines.slice(0, maxLines);
        };

        // 1. Nền giấy mỹ thuật chuyển sắc ấm & khung viền kép + 4 góc hoa văn
        const bgGrad = ctx.createLinearGradient(0, 0, W, H);
        bgGrad.addColorStop(0, '#FFFDF9');
        bgGrad.addColorStop(0.55, '#FAF4E8');
        bgGrad.addColorStop(1, '#F3E9D6');
        ctx.fillStyle = bgGrad;
        ctx.fillRect(0, 0, W, H);

        // Quầng sáng dịu ở trung tâm trên
        const glow = ctx.createRadialGradient(W / 2, H * 0.30, 20, W / 2, H * 0.30, W * 0.48);
        glow.addColorStop(0, 'rgba(207, 168, 96, 0.16)');
        glow.addColorStop(1, 'rgba(207, 168, 96, 0)');
        ctx.fillStyle = glow;
        ctx.fillRect(0, 0, W, H);

        // Khung viền kép letterpress
        ctx.strokeStyle = '#CFA860';
        ctx.lineWidth = 3.5;
        ctx.strokeRect(38, 38, W - 76, H - 76);
        ctx.strokeStyle = 'rgba(207, 168, 96, 0.45)';
        ctx.lineWidth = 1.4;
        ctx.strokeRect(52, 52, W - 104, H - 104);

        // 4 góc trang trí hình học tinh tế
        const cornerSize = 26;
        const corners = [
            [52, 52, 1, 1],
            [W - 52, 52, -1, 1],
            [52, H - 52, 1, -1],
            [W - 52, H - 52, -1, -1],
        ];
        ctx.strokeStyle = '#85182A';
        ctx.lineWidth = 2.2;
        corners.forEach(([cx, cy, sx, sy]) => {
            ctx.beginPath();
            ctx.moveTo(cx + sx * 8, cy + sy * (8 + cornerSize));
            ctx.lineTo(cx + sx * 8, cy + sy * 8);
            ctx.lineTo(cx + sx * (8 + cornerSize), cy + sy * 8);
            ctx.stroke();
        });

        const core = calc?.core_numbers || {};
        const cycles = calc?.personal_cycles || {};
        const lp = core.life_path || { display: '—', value: 1 };
        ctx.textAlign = 'center';

        // 2. Phần đầu thẻ: thương hiệu & tên hồ sơ
        const topY = isStory ? 132 : (isPost ? 114 : 104);
        ctx.fillStyle = '#1E4D3B';
        ctx.font = `700 20px ${SANS}`;
        ctx.fillText('MÂY HẸN  ·  DẤU ẤN CON SỐ', W / 2, topY);

        ctx.strokeStyle = 'rgba(207, 168, 96, 0.7)';
        ctx.lineWidth = 1.2;
        ctx.beginPath();
        ctx.moveTo(W / 2 - 110, topY + 18);
        ctx.lineTo(W / 2 + 110, topY + 18);
        ctx.stroke();

        ctx.fillStyle = '#5C5248';
        ctx.font = `600 21px ${SANS}`;
        const ownerLine = hideBirthDate
            ? `${calc?.display_name || 'Hồ Sơ Dấu Ấn Con Số'}  •  Ngày sinh ẩn riêng tư`
            : `${calc?.display_name || 'Hồ Sơ Dấu Ấn Con Số'}  •  ${calc?.birth?.label || ''}`;
        ctx.fillText(ownerLine, W / 2, topY + 50);

        // 3. Huy hiệu hình học trung tâm (Sacred Geometry Emblem)
        const embY = isStory ? 465 : (isPost ? 372 : 318);
        const embR = isStory ? 168 : (isPost ? 148 : 126);

        // Vòng ngoài nét đứt
        ctx.save();
        ctx.setLineDash([5, 7]);
        ctx.strokeStyle = 'rgba(207, 168, 96, 0.75)';
        ctx.lineWidth = 1.6;
        ctx.beginPath();
        ctx.arc(W / 2, embY, embR + 18, 0, Math.PI * 2);
        ctx.stroke();
        ctx.restore();

        // Nền vòng tròn trắng ngà
        ctx.fillStyle = '#FFFDF9';
        ctx.beginPath();
        ctx.arc(W / 2, embY, embR, 0, Math.PI * 2);
        ctx.fill();
        ctx.strokeStyle = '#CFA860';
        ctx.lineWidth = 2.6;
        ctx.stroke();

        // Vòng tròn trong + hình bát giác mảnh
        ctx.strokeStyle = 'rgba(133, 24, 42, 0.25)';
        ctx.lineWidth = 1.2;
        ctx.beginPath();
        ctx.arc(W / 2, embY, embR - 14, 0, Math.PI * 2);
        ctx.stroke();

        ctx.fillStyle = '#8A7B68';
        ctx.font = `700 15px ${SANS}`;
        ctx.fillText('CHỈ SỐ ĐƯỜNG ĐỜI', W / 2, embY - embR * 0.44);

        ctx.fillStyle = '#85182A';
        ctx.font = `700 ${isStory ? 148 : (isPost ? 132 : 114)}px ${SERIF}`;
        ctx.textBaseline = 'middle';
        ctx.fillText(String(lp.display), W / 2, embY + 12);
        ctx.textBaseline = 'alphabetic';

        // 4. Tiêu đề biên tập (tự xuống dòng, không bao giờ tràn mép!)
        const headlineY = embY + embR + (isStory ? 84 : (isPost ? 66 : 56));
        ctx.fillStyle = '#1F1915';
        ctx.font = `700 ${isStory ? 40 : (isPost ? 35 : 31)}px ${SERIF}`;
        const headLines = wrapLines(interpretation?.headline || `Chỉ Số Đường Đời ${lp.display}`, W - 180, 2);
        headLines.forEach((ln, idx) => {
            ctx.fillText(ln, W / 2, headlineY + idx * (isStory ? 52 : 42));
        });

        // 5. Lưới 6 chỉ số cốt lõi thanh lịch
        const stats = [
            { label: 'ĐƯỜNG ĐỜI', val: core.life_path?.display || '—' },
            { label: 'SỨ MỆNH', val: core.expression?.display || '—' },
            { label: 'LINH HỒN', val: core.soul_urge?.display || '—' },
            { label: 'NHÂN CÁCH', val: core.personality?.display || '—' },
            { label: 'NGÀY SINH', val: core.birthday?.display || '—' },
            { label: `NĂM ${cycles.personal_year?.year || ''}`, val: String(cycles.personal_year?.value ?? '—') },
        ];

        const gridTop = headlineY + headLines.length * (isStory ? 52 : 42) + (isStory ? 46 : 24);
        const cols = isStory ? 3 : 6;
        const rows = isStory ? 2 : 1;
        const gridPadX = 86;
        const gapX = 14;
        const gapY = 16;
        const cellW = (W - gridPadX * 2 - gapX * (cols - 1)) / cols;
        const cellH = isStory ? 136 : (isPost ? 112 : 96);

        stats.forEach((st, idx) => {
            const c = idx % cols;
            const r = Math.floor(idx / cols);
            const bx = gridPadX + c * (cellW + gapX);
            const by = gridTop + r * (cellH + gapY);

            drawRoundedRect(bx, by, cellW, cellH, 12);
            ctx.fillStyle = idx === 0 ? '#FFF7F5' : '#FFFFFF';
            ctx.fill();
            ctx.strokeStyle = idx === 0 ? '#85182A' : '#DFCFAF';
            ctx.lineWidth = idx === 0 ? 1.8 : 1.2;
            ctx.stroke();

            ctx.fillStyle = '#7A6D60';
            ctx.font = `700 ${isStory ? 14 : 12.5}px ${SANS}`;
            ctx.fillText(st.label, bx + cellW / 2, by + (isStory ? 36 : 28));

            ctx.fillStyle = idx === 0 ? '#85182A' : '#1E4D3B';
            ctx.font = `700 ${isStory ? 42 : (isPost ? 34 : 30)}px ${SERIF}`;
            ctx.fillText(st.val, bx + cellW / 2, by + (isStory ? 96 : (isPost ? 80 : 72)));
        });

        // 6. Khung trích dẫn / Câu hỏi chiêm nghiệm (viền trái đỏ son, xuống dòng gọn)
        const quoteBoxTop = gridTop + rows * cellH + (rows - 1) * gapY + (isStory ? 54 : 28);
        const quoteText = String(
            interpretation?.reflection_question
            || interpretation?.summary
            || 'Hiểu mình, hiểu người, vun đắp nhân duyên.'
        );
        ctx.font = `italic ${isStory ? 25 : (isPost ? 22 : 20)}px ${SERIF}`;
        const qLines = wrapLines(`“${quoteText}”`, W - 240, isStory ? 5 : 3);
        const qLineH = isStory ? 38 : 30;
        const quoteBoxH = Math.max(92, qLines.length * qLineH + 46);

        drawRoundedRect(86, quoteBoxTop, W - 172, quoteBoxH, 12);
        ctx.fillStyle = 'rgba(255, 255, 255, 0.88)';
        ctx.fill();
        ctx.strokeStyle = '#E4D5BE';
        ctx.lineWidth = 1.2;
        ctx.stroke();

        // Viền trái đỏ son nhấn Editorial
        ctx.fillStyle = '#85182A';
        ctx.fillRect(86, quoteBoxTop + 14, 5, quoteBoxH - 28);

        ctx.fillStyle = '#1E4D3B';
        ctx.font = `italic ${isStory ? 25 : (isPost ? 22 : 20)}px ${SERIF}`;
        const qStartY = quoteBoxTop + (quoteBoxH - (qLines.length - 1) * qLineH) / 2 + 6;
        qLines.forEach((ln, i) => {
            ctx.fillText(ln, W / 2, qStartY + i * qLineH);
        });

        // Nếu là Story (9:16), thêm khung tóm tắt ngắn để cân đối chiều cao
        if (isStory && interpretation?.summary) {
            const sumTop = quoteBoxTop + quoteBoxH + 38;
            ctx.fillStyle = '#4A4038';
            ctx.font = `500 22px ${SANS}`;
            const sLines = wrapLines(interpretation.summary, W - 210, 4);
            sLines.forEach((ln, i) => {
                ctx.fillText(ln, W / 2, sumTop + i * 34);
            });
        }

        // 7. Chân thẻ thương hiệu
        ctx.fillStyle = '#7A6E60';
        ctx.font = `600 18px ${SANS}`;
        ctx.fillText(`mayhen.vn/than-so-hoc  ·  ${calc?.method_name || 'Pythagorean Hans Decoz'}`, W / 2, H - 78);

        const a = document.createElement('a');
        a.download = `mayhen-than-so-hoc-${ratioKey.replace(':', 'x')}-${Date.now()}.png`;
        a.href = canvas.toDataURL('image/png');
        a.click();
    }

    // =========================================================================
    // 4B. BIỂU ĐỒ SÓNG VẬN SỐ CHU KỲ 9 NĂM (CUBIC BEZIER + ANIMATION 60FPS)
    // =========================================================================
    const PERSONAL_YEAR_WAVE_META = {
        1: { level: 48, short: 'Khởi đầu', theme: 'Khởi Đầu Chu Kỳ Mới', summary: 'Năm mở màn cho chặng 9 năm mới: phù hợp xác lập định hướng rõ ràng, gieo hạt thói quen mới và chủ động bước đi đầu tiên.', focus: 'Tự chủ, chọn 1–2 ưu tiên cốt lõi thay vì dàn trải.' },
        2: { level: 34, short: 'Kết nối', theme: 'Kết Nối & Kiên Nhẫn', summary: 'Năm của nhịp chậm có chủ đích: nuôi dưỡng quan hệ, hoàn thiện chi tiết và lắng nghe nhiều hơn trước khi tăng tốc.', focus: 'Hợp tác, đối thoại chân thành trong tình yêu và công việc.' },
        3: { level: 68, short: 'Mở rộng', theme: 'Mở Rộng & Biểu Đạt', summary: 'Năm thuận lợi cho học hỏi, sáng tạo, mở rộng giao tiếp xã hội và thổi thêm niềm vui vào nhịp sống hằng ngày.', focus: 'Sáng tạo đi kèm quản lý thời gian để không bị loãng.' },
        4: { level: 42, short: 'Xây nền', theme: 'Xây Nền & Kỷ Luật', summary: 'Năm củng cố cấu trúc: rà soát tài chính, sức khỏe, kỹ năng nền tảng và thiết lập quy trình làm việc bền vững.', focus: 'Kiên trì từng bước, không đốt cháy giai đoạn.' },
        5: { level: 78, short: 'Chuyển động', theme: 'Chuyển Động & Đổi Mới', summary: 'Năm giàu biến chuyển và cơ hội làm mới góc nhìn: linh hoạt điều chỉnh kế hoạch nhưng vẫn giữ nguyên tắc an toàn.', focus: 'Thích nghi tỉnh táo, chọn lọc trải nghiệm đáng giá.' },
        6: { level: 62, short: 'Tổ ấm', theme: 'Tổ Ấm & Trách Nhiệm', summary: 'Năm hướng về gia đình, tình yêu, cam kết bền vững và chăm chút không gian sống lẫn sức khỏe tinh thần.', focus: 'Hòa khí gia đạo, cân bằng giữa chăm lo người thân và chăm sóc bản thân.' },
        7: { level: 26, short: 'Tĩnh tâm', theme: 'Tĩnh Tâm & Nâng Tầm Chiều Sâu', summary: 'Năm lý tưởng để học chuyên sâu, đánh giá lại định hướng và củng cố nội lực thay vì chạy theo bề nổi.', focus: 'Đọc sâu, nghỉ ngơi chất lượng và đối thoại trung thực với chính mình.' },
        8: { level: 90, short: 'Gặt hái', theme: 'Gặt Hái & Điều Hành Thực Tế', summary: 'Năm tập trung vào kết quả thực chất, quản trị tài chính minh bạch và nâng chuẩn hiệu quả công việc.', focus: 'Kỷ luật thực thi, cân bằng công việc với gia đình.' },
        9: { level: 96, short: 'Chuyển giao', theme: 'Khép Lại & Chuyển Giao', summary: 'Năm cuối chu kỳ 9 năm: tổng kết bài học, hoàn tất việc dang dở và buông nhẹ những thói quen không còn phù hợp.', focus: 'Dọn gọn tâm trí và chuẩn bị nền tảng cho chu kỳ mới.' },
    };

    function buildSmoothWavePath(points) {
        if (!points.length) return '';
        if (points.length === 1) return `M ${points[0].x.toFixed(1)} ${points[0].y.toFixed(1)}`;
        let d = `M ${points[0].x.toFixed(1)} ${points[0].y.toFixed(1)}`;
        for (let i = 0; i < points.length - 1; i++) {
            const p0 = points[Math.max(0, i - 1)];
            const p1 = points[i];
            const p2 = points[i + 1];
            const p3 = points[Math.min(points.length - 1, i + 2)];
            const cp1x = p1.x + (p2.x - p0.x) / 6;
            const cp1y = p1.y + (p2.y - p0.y) / 6;
            const cp2x = p2.x - (p3.x - p1.x) / 6;
            const cp2y = p2.y - (p3.y - p1.y) / 6;
            d += ` C ${cp1x.toFixed(1)} ${cp1y.toFixed(1)}, ${cp2x.toFixed(1)} ${cp2y.toFixed(1)}, ${p2.x.toFixed(1)} ${p2.y.toFixed(1)}`;
        }
        return d;
    }

    function renderNineYearWaveChart(container, timeline) {
        if (!container || !Array.isArray(timeline) || timeline.length === 0) return;

        const W = 960;
        const H = 300;
        const padLeft = 86;
        const padRight = 46;
        const padTop = 58;
        const baseY = 236;
        const usableH = baseY - padTop;
        const usableW = W - padLeft - padRight;
        const stepX = timeline.length > 1 ? usableW / (timeline.length - 1) : 0;

        const points = timeline.map((item, idx) => {
            const val = Number(item.value) || 1;
            const meta = PERSONAL_YEAR_WAVE_META[val] || PERSONAL_YEAR_WAVE_META[1];
            const x = padLeft + idx * stepX;
            const y = baseY - (meta.level / 100) * usableH;
            return {
                idx,
                year: item.year,
                value: val,
                isCurrent: Boolean(item.is_current),
                meta,
                x,
                y,
            };
        });

        const wavePath = buildSmoothWavePath(points);
        const firstPt = points[0];
        const lastPt = points[points.length - 1];
        const areaPath = `${wavePath} L ${lastPt.x.toFixed(1)} ${baseY} L ${firstPt.x.toFixed(1)} ${baseY} Z`;
        const uid = 'mhWave_' + Math.random().toString(36).slice(2, 7);

        const gridLines = [
            { y: padTop + usableH * 0.08, label: 'Đỉnh cao' },
            { y: padTop + usableH * 0.48, label: 'Chuyển nhịp' },
            { y: padTop + usableH * 0.84, label: 'Củng cố' },
        ];

        const gridHtml = gridLines.map((g) => `
            <line class="mayhen_numerology_wave_grid_line" x1="${padLeft - 14}" y1="${g.y.toFixed(1)}" x2="${W - padRight + 16}" y2="${g.y.toFixed(1)}" />
            <text class="mayhen_numerology_wave_grid_label" x="${padLeft - 18}" y="${(g.y + 3.5).toFixed(1)}" text-anchor="end">${g.label}</text>
        `).join('');

        const defaultActiveIdx = Math.max(0, points.findIndex((p) => p.isCurrent));

        const nodesHtml = points.map((pt, i) => {
            const delayMs = 220 + i * 75;
            const r = pt.isCurrent ? 21 : 17;
            const pillY = pt.y - 40;
            return `
                <g class="mayhen_numerology_wave_node ${pt.isCurrent ? 'is-current' : ''} ${i === defaultActiveIdx ? 'is-active' : ''}"
                   tabindex="0"
                   role="button"
                   aria-label="Năm ${pt.year}: Năm cá nhân số ${pt.value} — ${escapeHtml(pt.meta.theme)}"
                   data-wave-idx="${i}">
                    <line class="mayhen_numerology_wave_stem" x1="${pt.x.toFixed(1)}" y1="${(pt.y + r + 2).toFixed(1)}" x2="${pt.x.toFixed(1)}" y2="${baseY}" />
                    <g class="mayhen_numerology_wave_dot_wrap" style="--node-delay:${delayMs}ms;">
                        ${pt.isCurrent ? `<circle class="mayhen_numerology_wave_pulse" cx="${pt.x.toFixed(1)}" cy="${pt.y.toFixed(1)}" r="22" />` : ''}
                        ${pt.isCurrent ? `
                            <rect class="mayhen_numerology_wave_now_pill" x="${(pt.x - 31).toFixed(1)}" y="${pillY.toFixed(1)}" width="62" height="18" rx="9" />
                            <text class="mayhen_numerology_wave_now_txt" x="${pt.x.toFixed(1)}" y="${(pillY + 9).toFixed(1)}">HIỆN TẠI</text>
                        ` : ''}
                        <circle class="mayhen_numerology_wave_dot" cx="${pt.x.toFixed(1)}" cy="${pt.y.toFixed(1)}" r="${r}" />
                        <text class="mayhen_numerology_wave_num" x="${pt.x.toFixed(1)}" y="${pt.y.toFixed(1)}">${pt.value}</text>
                    </g>
                    <text class="mayhen_numerology_wave_year" x="${pt.x.toFixed(1)}" y="${baseY + 22}">${pt.year}</text>
                    <text class="mayhen_numerology_wave_tag_short" x="${pt.x.toFixed(1)}" y="${baseY + 38}">${escapeHtml(pt.meta.short)}</text>
                </g>
            `;
        }).join('');

        container.innerHTML = `
            <div class="mayhen_numerology_wave_canvas_wrap">
                <svg class="mayhen_numerology_wave_svg" viewBox="0 0 ${W} ${H}" role="img" aria-label="Biểu đồ sóng vận số chu kỳ 9 năm">
                    <defs>
                        <linearGradient id="${uid}_stroke" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#CFA860" />
                            <stop offset="48%" stop-color="#85182A" />
                            <stop offset="100%" stop-color="#1E4D3B" />
                        </linearGradient>
                        <linearGradient id="${uid}_area" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#CFA860" stop-opacity="0.30" />
                            <stop offset="65%" stop-color="#85182A" stop-opacity="0.08" />
                            <stop offset="100%" stop-color="#FAF4E8" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>
                    ${gridHtml}
                    <line x1="${padLeft - 14}" y1="${baseY}" x2="${W - padRight + 16}" y2="${baseY}" stroke="#DFCFAF" stroke-width="1.2" />
                    <path class="mayhen_numerology_wave_area" d="${areaPath}" fill="url(#${uid}_area)" />
                    <path class="mayhen_numerology_wave_path_glow" d="${wavePath}" />
                    <path class="mayhen_numerology_wave_path" data-wave-main-path d="${wavePath}" stroke="url(#${uid}_stroke)" />
                    ${nodesHtml}
                </svg>
            </div>
            <div class="mayhen_numerology_wave_detail" data-num-wave-detail aria-live="polite"></div>
        `;

        const mainPath = container.querySelector('[data-wave-main-path]');
        if (mainPath && typeof mainPath.getTotalLength === 'function') {
            const len = Math.ceil(mainPath.getTotalLength()) + 8;
            container.style.setProperty('--wave-len', `${len}`);
        }

        const detailEl = container.querySelector('[data-num-wave-detail]');
        const nodeEls = Array.from(container.querySelectorAll('.mayhen_numerology_wave_node'));

        function selectPoint(idx) {
            const pt = points[idx];
            if (!pt || !detailEl) return;
            nodeEls.forEach((n, j) => n.classList.toggle('is-active', j === idx));
            detailEl.innerHTML = `
                <div class="mayhen_numerology_wave_detail_main">
                    <div class="mayhen_numerology_wave_detail_kicker">
                        <span>Năm ${pt.year} • Năm Cá Nhân Số ${pt.value}</span>
                        ${pt.isCurrent ? '<span class="mayhen_numerology_wave_detail_badge">Đang diễn ra</span>' : ''}
                    </div>
                    <h5 class="mayhen_numerology_wave_detail_title">${escapeHtml(pt.meta.theme)}</h5>
                    <p class="mayhen_numerology_wave_detail_desc">${escapeHtml(pt.meta.summary)}</p>
                </div>
                <div class="mayhen_numerology_wave_detail_focus">
                    <strong>Trọng tâm hành động</strong>
                    ${escapeHtml(pt.meta.focus)}
                </div>
            `;
        }

        nodeEls.forEach((nodeEl) => {
            const idx = Number(nodeEl.getAttribute('data-wave-idx'));
            nodeEl.addEventListener('click', () => selectPoint(idx));
            nodeEl.addEventListener('mouseenter', () => selectPoint(idx));
            nodeEl.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectPoint(idx);
                }
            });
        });

        selectPoint(defaultActiveIdx);

        // Kích hoạt animation vẽ sóng êm khi cuộn tới khung nhìn
        const triggerAnim = () => {
            requestAnimationFrame(() => container.classList.add('is-animated'));
        };

        if (reduceMotion || !('IntersectionObserver' in window)) {
            triggerAnim();
        } else {
            const io = new IntersectionObserver((entries) => {
                if (entries.some((e) => e.isIntersecting)) {
                    triggerAnim();
                    io.disconnect();
                }
            }, { threshold: 0.18 });
            io.observe(container);
        }
    }

    // =========================================================================
    // 4C. ĐỒ HỌA KIM TỰ THÁP 4 ĐỈNH CAO & 4 THỬ THÁCH SVG (ANIMATION VẼ NÉT)
    // =========================================================================
    function renderPinnaclePyramidSvg(container, calc, enriched) {
        if (!container || !Array.isArray(enriched?.pinnacles_enriched) || enriched.pinnacles_enriched.length < 4) return;

        const pins = enriched.pinnacles_enriched;
        const chals = enriched.challenges_enriched || [];
        const currentAge = Number(enriched.current_age ?? -1);
        const birth = calc?.birth || {};
        const bMonth = String(birth.month || '').padStart(2, '0');
        const bDay = String(birth.day || '').padStart(2, '0');
        const bYear = String(birth.year || '');

        const isPinActive = (p) => {
            if (currentAge < 0) return false;
            const s = Number(p.age_start ?? 0);
            const e = p.age_end === null || p.age_end === undefined ? 120 : Number(p.age_end);
            return currentAge >= s && currentAge <= e;
        };

        const activePinIdx = Math.max(0, pins.findIndex(isPinActive));

        // Tọa độ cân đối 3 chân đế + 4 đỉnh cao trên viewBox 780x325
        const pts = {
            m: { x: 165, y: 268, label: `Tháng ${bMonth}` },
            d: { x: 390, y: 268, label: `Ngày ${bDay}` },
            y: { x: 615, y: 268, label: `Năm ${bYear}` },
            p1: { x: 277, y: 186 },
            p2: { x: 503, y: 186 },
            p3: { x: 390, y: 112 },
            p4: { x: 390, y: 42 },
        };

        const edges = [
            `M ${pts.m.x} ${pts.m.y} L ${pts.p1.x} ${pts.p1.y} L ${pts.d.x} ${pts.d.y}`,
            `M ${pts.d.x} ${pts.d.y} L ${pts.p2.x} ${pts.p2.y} L ${pts.y.x} ${pts.y.y}`,
            `M ${pts.p1.x} ${pts.p1.y} L ${pts.p3.x} ${pts.p3.y} L ${pts.p2.x} ${pts.p2.y}`,
            `M ${pts.m.x} ${pts.m.y} L ${pts.p4.x} ${pts.p4.y} L ${pts.y.x} ${pts.y.y}`,
        ];

        const pinPositions = [pts.p1, pts.p2, pts.p3, pts.p4];
        const pinNodesHtml = pins.slice(0, 4).map((p, idx) => {
            const pos = pinPositions[idx];
            const active = isPinActive(p);
            const chal = chals[idx];
            const delay = 220 + idx * 110;
            const ageOffsetX = idx === 0 ? -74 : (idx === 1 ? 74 : (idx === 2 ? 0 : 96));
            const ageOffsetY = idx === 2 ? -30 : (idx === 3 ? 4 : -4);
            return `
                <g class="mayhen_numerology_pyr_node ${active ? 'is-current' : ''} ${idx === activePinIdx ? 'is-selected' : ''}"
                   tabindex="0"
                   role="button"
                   data-pyr-idx="${idx}"
                   style="--pyr-delay:${delay}ms;">
                    ${active ? `<circle class="mayhen_numerology_wave_pulse" cx="${pos.x}" cy="${pos.y}" r="24" />` : ''}
                    <circle class="mayhen_numerology_pyr_circle" cx="${pos.x}" cy="${pos.y}" r="${active ? 21 : 18}" />
                    <text class="mayhen_numerology_pyr_num" x="${pos.x}" y="${pos.y}">${escapeHtml(p.display)}</text>
                    <text class="mayhen_numerology_pyr_age" x="${pos.x + ageOffsetX}" y="${pos.y + ageOffsetY}" text-anchor="middle">
                        ${escapeHtml(p.label)} (${escapeHtml(p.age_range)})
                    </text>
                    ${chal ? `<text class="mayhen_numerology_pyr_chal" x="${pos.x + ageOffsetX}" y="${pos.y + ageOffsetY + 14}" text-anchor="middle">Thử thách: ${escapeHtml(chal.value)}</text>` : ''}
                </g>
            `;
        }).join('');

        container.innerHTML = `
            <div class="mayhen_numerology_pyr_header">
                <div>
                    <span class="mayhen_numerology_chapter_tag" style="margin-bottom: 4px;">Bản Đồ 4 Đỉnh Cao Kim Tự Tháp</span>
                    <h3 class="mayhen_numerology_sub_h3" style="margin: 0;">Đồ Họa Kim Tự Tháp Thành Tựu &amp; Thử Thách</h3>
                </div>
                <span class="mayhen_numerology_career_note">Chạm từng đỉnh cao để đọc chi tiết giai đoạn tương ứng</span>
            </div>
            <div class="mayhen_numerology_wave_canvas_wrap">
                <svg class="mayhen_numerology_pyr_svg" viewBox="0 0 780 315" role="img" aria-label="Kim tự tháp 4 đỉnh cao Thần Số Học">
                    <line x1="125" y1="268" x2="655" y2="268" stroke="#DFCFAF" stroke-width="1.4" />
                    ${edges.map((d, i) => `<path class="mayhen_numerology_pyr_edge" style="--edge-delay:${i * 120}ms;" d="${d}" />`).join('')}
                    ${[pts.m, pts.d, pts.y].map((b) => `
                        <g>
                            <circle cx="${b.x}" cy="${b.y}" r="6" fill="#CFA860" stroke="#FFFDF9" stroke-width="2" />
                            <text class="mayhen_numerology_pyr_base_lbl" x="${b.x}" y="${b.y + 24}" text-anchor="middle">${escapeHtml(b.label)}</text>
                        </g>
                    `).join('')}
                    ${pinNodesHtml}
                </svg>
            </div>
            <div class="mayhen_numerology_pyr_detail" data-num-pyr-detail aria-live="polite"></div>
        `;

        const detailEl = container.querySelector('[data-num-pyr-detail]');
        const nodes = Array.from(container.querySelectorAll('.mayhen_numerology_pyr_node'));

        function selectPinnacle(idx) {
            const p = pins[idx];
            const c = chals[idx];
            if (!p || !detailEl) return;
            const hasAiDrawer = Boolean(document.querySelector('[data-num-scoped-backdrop]'));
            nodes.forEach((n, j) => n.classList.toggle('is-selected', j === idx));
            detailEl.innerHTML = `
                <div class="mayhen_numerology_wave_detail_main">
                    <div class="mayhen_numerology_wave_detail_kicker">
                        <span>${escapeHtml(p.label)} • ${escapeHtml(p.age_range)} • Đỉnh Cao Số ${escapeHtml(p.display)}</span>
                        ${isPinActive(p) ? '<span class="mayhen_numerology_wave_detail_badge">Giai đoạn hiện tại</span>' : ''}
                    </div>
                    <p class="mayhen_numerology_wave_detail_desc">${escapeHtml(p.meaning || '')}</p>
                    ${hasAiDrawer ? `<div style="margin-top: 8px;"><button type="button" class="mayhen_numerology_mini_btn mayhen_numerology_mini_btn--ai" data-num-ask-trigger data-scope-type="section" data-section-key="life_journey" data-scope-label="Kim Tự Tháp & ${escapeHtml(p.label)}" data-prefill-question="Trong ${escapeHtml(p.label)} số ${escapeHtml(p.display)} (${escapeHtml(p.age_range)}) song hành Thử thách ${escapeHtml(c ? c.value : '0')}, mình cần chuẩn bị và hành động thế nào?">✦ Hỏi AI đỉnh cao này</button></div>` : ''}
                </div>
                ${c ? `
                    <div class="mayhen_numerology_wave_detail_focus">
                        <strong>Thử Thách Song Hành • Số ${escapeHtml(c.value)}</strong>
                        ${escapeHtml(c.meaning || '')}
                    </div>
                ` : ''}
            `;
        }

        nodes.forEach((n) => {
            const idx = Number(n.getAttribute('data-pyr-idx'));
            n.addEventListener('click', () => selectPinnacle(idx));
            n.addEventListener('mouseenter', () => selectPinnacle(idx));
            n.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectPinnacle(idx);
                }
            });
        });

        selectPinnacle(activePinIdx);

        const triggerPyr = () => requestAnimationFrame(() => container.classList.add('is-animated'));
        if (reduceMotion || !('IntersectionObserver' in window)) {
            triggerPyr();
        } else {
            const io = new IntersectionObserver((entries) => {
                if (entries.some((e) => e.isIntersecting)) {
                    triggerPyr();
                    io.disconnect();
                }
            }, { threshold: 0.18 });
            io.observe(container);
        }
    }

    // =========================================================================
    // 4D. SCROLL-REVEAL TINH TẾ & ANIMATION THANH NĂNG LỰC NGHỀ NGHIỆP
    // =========================================================================
    function initEditorialRevealAndBars(root) {
        if (!root) return;
        const reveals = Array.from(root.querySelectorAll('[data-num-reveal]'));
        const careerPanel = root.querySelector('[data-num-career-bars]');

        if (reduceMotion || !('IntersectionObserver' in window)) {
            reveals.forEach((el) => el.classList.add('is-revealed'));
            careerPanel?.classList.add('is-animated');
            return;
        }

        const revIo = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    revIo.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -24px 0px' });
        reveals.forEach((el) => revIo.observe(el));

        if (careerPanel) {
            const barIo = new IntersectionObserver((entries) => {
                if (entries.some((e) => e.isIntersecting)) {
                    careerPanel.classList.add('is-animated');
                    barIo.disconnect();
                }
            }, { threshold: 0.16 });
            barIo.observe(careerPanel);
        }
    }

    function initIndicatorFilters(root) {
        if (!root) return;
        const btns = Array.from(root.querySelectorAll('[data-ind-filter]'));
        const cards = Array.from(root.querySelectorAll('.mayhen_numerology_ind_card'));
        if (!btns.length || !cards.length) return;

        btns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const mode = btn.getAttribute('data-ind-filter') || 'all';
                btns.forEach((b) => b.classList.toggle('is-active', b === btn));
                cards.forEach((c) => {
                    const grp = c.getAttribute('data-ind-group') || 'core';
                    const visible = mode === 'all' || grp === mode;
                    c.style.display = visible ? '' : 'none';
                    if (visible) c.classList.add('is-revealed');
                });
            });
        });
    }

    // =========================================================================
    // 5. TRANG CHI TIẾT HỒ SƠ (/than-so-hoc/ket-qua/{slug})
    // =========================================================================
    const showApp = document.getElementById('mayhenNumerologyShowApp');
    if (showApp) {
        const calcJson = JSON.parse(document.getElementById('mayhenNumCalcJson')?.textContent || '{}');
        const enrichedJson = JSON.parse(document.getElementById('mayhenNumEnrichedJson')?.textContent || '{}');
        const interpJson = JSON.parse(document.getElementById('mayhenNumInterpJson')?.textContent || '{}');
        const endpoints = JSON.parse(document.getElementById('mayhenNumEndpointsJson')?.textContent || '{}');
        const sectionDefs = JSON.parse(document.getElementById('mayhenNumSectionDefsJson')?.textContent || '{}');
        const followupRecords = JSON.parse(document.getElementById('mayhenNumFollowupsJson')?.textContent || '[]');
        const conceptGlossary = enrichedJson.concept_glossary || {};

        // Bộ thẻ cá nhân xòe quạt
        const personalCards = [];
        const core = calcJson.core_numbers || {};
        const ext = calcJson.extended_numbers || {};
        const cardSpecs = [
            ['life_path', 'Đường Đời', core.life_path],
            ['expression', 'Sứ Mệnh', core.expression],
            ['soul_urge', 'Linh Hồn', core.soul_urge],
            ['personality', 'Nhân Cách', core.personality],
            ['birthday', 'Ngày Sinh', core.birthday ? { value: core.birthday.raw_day, display: String(core.birthday.raw_day), is_master: core.birthday.is_master } : null],
            ['maturity', 'Trưởng Thành', ext.maturity],
            ['attitude', 'Thái Độ', ext.attitude],
            ['balance', 'Cân Bằng', ext.balance],
        ];
        cardSpecs.forEach(([code, label, obj]) => {
            if (!obj || obj.is_empty_category) return;
            const m = code === 'birthday'
                ? { title: enrichedJson.birthday_meaning?.title || label, contextual_meaning: enrichedJson.birthday_meaning?.summary || '' }
                : (enrichedJson.indicator_meanings?.[code] || {});
            personalCards.push({
                code,
                kicker: label,
                value: obj.value,
                display: obj.display || String(obj.value),
                is_master: Boolean(obj.is_master),
                title: m.title || label,
                short_meaning: m.contextual_meaning || '',
                full_meaning: m.contextual_meaning || '',
                love_meaning: m.love_meaning || '',
                reflection: m.reflection_question || '',
            });
        });
        initArtCardsFan(showApp, personalCards);

        renderBirthChart3x3(showApp.querySelector('[data-num-birthchart]'), calcJson.birth_chart, enrichedJson);
        renderNineYearWaveChart(showApp.querySelector('[data-num-cycle-wave]'), calcJson.personal_cycles?.nine_year_timeline || []);
        renderPinnaclePyramidSvg(showApp.querySelector('[data-num-pyramid-svg]'), calcJson, enrichedJson);
        initIndicatorFilters(showApp);
        initEditorialRevealAndBars(showApp);

        // Xuất ảnh thẻ nghệ thuật 3 tỉ lệ
        showApp.querySelectorAll('[data-num-export-ratio]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const ratio = btn.getAttribute('data-num-export-ratio') || '1:1';
                const hideCb = showApp.querySelector('[data-num-export-hide-birth]');
                exportArtCardPng(calcJson, interpJson, ratio, Boolean(hideCb?.checked));
            });
        });

        // -----------------------------------------------------------------
        // Popover Giải Mã Khái Niệm Tức Thì cho người mới
        // -----------------------------------------------------------------
        const conceptPop = document.querySelector('[data-num-concept-popover]');
        const popGroup = conceptPop?.querySelector('[data-num-pop-group]');
        const popTitle = conceptPop?.querySelector('[data-num-pop-title]');
        const popFormula = conceptPop?.querySelector('[data-num-pop-formula]');
        const popSummary = conceptPop?.querySelector('[data-num-pop-summary]');
        const popApply = conceptPop?.querySelector('[data-num-pop-apply]');
        const popAskBtn = conceptPop?.querySelector('[data-num-pop-ask]');
        const popCloseBtn = conceptPop?.querySelector('[data-num-pop-close]');
        let currentConceptScope = { type: 'section', sectionKey: 'core_numbers', topicCode: null, label: 'Bộ Chỉ Số Cốt Lõi', prefill: '' };

        function closeConceptPopover() {
            if (!conceptPop) return;
            conceptPop.classList.remove('is-open');
            conceptPop.setAttribute('aria-hidden', 'true');
        }

        function openConceptPopover(conceptKey, triggerEl) {
            if (!conceptPop) return;
            const info = conceptGlossary[conceptKey] || null;
            if (!info) return;

            if (popGroup) popGroup.textContent = info.group || 'Khái niệm Thần Số Học';
            if (popTitle) popTitle.textContent = info.title || conceptKey;
            if (popFormula) popFormula.textContent = `Cách tính: ${info.formula || ''}`;
            if (popSummary) popSummary.textContent = info.summary || '';
            if (popApply) popApply.textContent = info.how_to_apply ? `Ứng dụng: ${info.how_to_apply}` : '';

            const isIndicator = !['master_numbers', 'karmic_debts', 'birth_chart', 'arrows', 'pinnacles', 'challenges', 'period_cycles', 'personal_year', 'personal_month_day'].includes(conceptKey);
            currentConceptScope = isIndicator
                ? {
                    type: 'indicator',
                    sectionKey: null,
                    topicCode: `ind:${conceptKey}`,
                    label: info.title || conceptKey,
                    prefill: `${info.title} trong hồ sơ của mình có ý nghĩa thực tế gì nổi bật nhất?`,
                }
                : {
                    type: 'section',
                    sectionKey: ['birth_chart', 'arrows'].includes(conceptKey) ? 'birth_chart' : (['pinnacles', 'challenges', 'period_cycles'].includes(conceptKey) ? 'life_journey' : (['personal_year', 'personal_month_day'].includes(conceptKey) ? 'personal_cycles' : 'overview')),
                    topicCode: null,
                    label: info.title || conceptKey,
                    prefill: `${info.title} trong hồ sơ của mình có ý nghĩa và cách ứng dụng ra sao?`,
                };

            conceptPop.classList.add('is-open');
            conceptPop.setAttribute('aria-hidden', 'false');

            if (window.innerWidth > 640 && triggerEl) {
                const rect = triggerEl.getBoundingClientRect();
                const popW = Math.min(390, window.innerWidth - 24);
                const popH = conceptPop.offsetHeight || 280;
                const left = Math.max(12, Math.min(rect.left, window.innerWidth - popW - 12));
                let top = rect.bottom + 8;
                if (top + popH > window.innerHeight - 12) {
                    top = Math.max(12, rect.top - popH - 8);
                }
                conceptPop.style.left = `${Math.round(left)}px`;
                conceptPop.style.top = `${Math.round(top)}px`;
                conceptPop.style.bottom = 'auto';
            }
        }

        popCloseBtn?.addEventListener('click', closeConceptPopover);
        popAskBtn?.addEventListener('click', () => {
            closeConceptPopover();
            openScopedModal({
                type: currentConceptScope.type,
                sectionKey: currentConceptScope.sectionKey,
                topicCode: currentConceptScope.topicCode,
                label: currentConceptScope.label,
                prefillQuestion: currentConceptScope.prefill,
            });
        });

        // -----------------------------------------------------------------
        // Ngăn Kéo Chat Hỏi Sâu AI Chuẩn Tử Vi (Zero-Trust + Lock Scroll + 600 chars)
        // -----------------------------------------------------------------
        const fuForm = showApp.querySelector('[data-num-followup-form]');
        const fuList = showApp.querySelector('[data-num-followup-list]');
        const fuErrBox = showApp.querySelector('[data-num-followup-error]');

        const scopedBackdrop = document.querySelector('[data-num-scoped-backdrop]');
        const scopedModal = scopedBackdrop?.querySelector('[data-num-scoped-modal]');
        const scopedBadge = scopedBackdrop?.querySelector('[data-num-scoped-badge]');
        const scopedTitle = scopedBackdrop?.querySelector('[data-num-scoped-title]');
        const scopedDesc = scopedBackdrop?.querySelector('[data-num-scoped-desc]');
        const scopedSuggestWrap = scopedBackdrop?.querySelector('[data-num-scoped-suggest-wrap]');
        const scopedSuggestToggle = scopedBackdrop?.querySelector('[data-num-scoped-suggest-toggle]');
        const scopedSuggestions = scopedBackdrop?.querySelector('[data-num-scoped-suggestions]');
        const scopedHistory = scopedBackdrop?.querySelector('[data-num-scoped-history]');
        const scopedScrollBottomBtn = scopedBackdrop?.querySelector('[data-num-scroll-bottom]');
        const scopedForm = scopedBackdrop?.querySelector('[data-num-scoped-form]');
        const scopedInput = scopedBackdrop?.querySelector('#numScopedInput');
        const scopedCharCount = scopedBackdrop?.querySelector('[data-num-char-count]');
        const scopedErrBox = scopedBackdrop?.querySelector('[data-num-scoped-error]');
        const scopedSubmit = scopedBackdrop?.querySelector('[data-num-scoped-submit]');

        const MAX_SCOPED_QUESTION_CHARS = 600;
        let lockedPageScrollY = 0;
        let isPageScrollLocked = false;
        let scopedTouchStartY = 0;

        let activeScope = {
            type: 'general',
            topicCode: null,
            sectionKey: null,
            scopeCode: null,
            label: 'Tổng thể hồ sơ',
        };

        function formatSafeProse(text) {
            return escapeHtml(String(text || '')).replace(/\n/g, '<br>');
        }

        function setScopedBackgroundLock(lock) {
            const shouldLock = Boolean(lock);
            if (shouldLock && !isPageScrollLocked) {
                lockedPageScrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0;
                if (window.innerWidth <= 767) {
                    document.body.style.top = `-${lockedPageScrollY}px`;
                }
                document.documentElement.classList.add('mayhen_numerology_chat_locked');
                document.body.classList.add('mayhen_numerology_chat_locked');
                isPageScrollLocked = true;
            } else if (!shouldLock && isPageScrollLocked) {
                const restoreY = lockedPageScrollY;
                const hadFixedBody = window.innerWidth <= 767 || document.body.style.top !== '';
                document.documentElement.classList.remove('mayhen_numerology_chat_locked');
                document.body.classList.remove('mayhen_numerology_chat_locked');
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
            const div = document.createElement('div');
            div.className = 'mayhen_numerology_note_box';
            div.style.marginBottom = '10px';
            div.innerHTML = `
                <div class="mayhen_numerology_fu_meta">
                    <span class="mayhen_numerology_scope_pill">${escapeHtml(fu.scope_label || 'Tổng thể hồ sơ')}</span>
                    <small>${escapeHtml(fu.created_at || '')}</small>
                </div>
                <div style="font-weight:700; color:#85182A; margin-bottom:4px;">Hỏi: ${escapeHtml(fu.question)}</div>
                <div class="mayhen_numerology_long_prose">${formatSafeProse(fu.answer)}</div>
            `;
            fuList.appendChild(div);
        }

        function buildScopedChatBubblePair(question, answer, createdAt = '') {
            return `
                <div class="mayhen_numerology_chat_row mayhen_numerology_chat_row--user">
                    <div class="mayhen_numerology_chat_bubble mayhen_numerology_chat_bubble--user">${escapeHtml(question)}</div>
                </div>
                <div class="mayhen_numerology_chat_row mayhen_numerology_chat_row--ai">
                    <div class="mayhen_numerology_chat_bubble mayhen_numerology_chat_bubble--ai">
                        <div class="mayhen_numerology_chat_sender">
                            <span>✦ Mây Hẹn AI</span>
                            ${createdAt ? `<time>${escapeHtml(createdAt)}</time>` : ''}
                        </div>
                        <div class="mayhen_numerology_chat_text">${formatSafeProse(answer)}</div>
                    </div>
                </div>
            `;
        }

        function appendPendingChatBubbles(question) {
            if (!scopedHistory) return;
            const emptyBox = scopedHistory.querySelector('.mayhen_numerology_scoped_empty');
            if (emptyBox) {
                emptyBox.remove();
            }
            const wrapper = document.createElement('div');
            wrapper.setAttribute('data-num-pending-group', '1');
            wrapper.style.display = 'contents';
            wrapper.innerHTML = `
                <div class="mayhen_numerology_chat_row mayhen_numerology_chat_row--user">
                    <div class="mayhen_numerology_chat_bubble mayhen_numerology_chat_bubble--user">${escapeHtml(question)}</div>
                </div>
                <div class="mayhen_numerology_chat_row mayhen_numerology_chat_row--ai">
                    <div class="mayhen_numerology_chat_bubble mayhen_numerology_chat_bubble--ai">
                        <div class="mayhen_numerology_chat_sender"><span>✦ Mây Hẹn AI</span></div>
                        <div class="mayhen_numerology_chat_typing">
                            <span>Đang đối chiếu hồ sơ con số</span>
                            <span class="mayhen_numerology_chat_dots" aria-hidden="true"><span></span><span></span><span></span></span>
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
                if (!activeScope.scopeCode) return !f.topic_code;
                return f.topic_code === activeScope.scopeCode;
            });

            if (matched.length === 0) {
                scopedHistory.innerHTML = `
                    <div class="mayhen_numerology_scoped_empty">
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

        function openScopedModal({ type = 'section', topicCode = null, sectionKey = null, label = 'Phần đang xem', prefillQuestion = '' }) {
            if (!scopedBackdrop) return;
            closeConceptPopover();

            const secDef = sectionKey ? sectionDefs[sectionKey] : null;
            const scopeCode = secDef ? secDef.scope_code : (topicCode || null);
            activeScope = { type, topicCode, sectionKey, scopeCode, label };

            if (scopedBadge) scopedBadge.textContent = 'Khóa dữ kiện';
            if (scopedTitle) scopedTitle.textContent = `Hỏi Sâu AI • ${label}`;

            let description = secDef?.description || 'Khóa chặt theo bộ số, biểu đồ 3×3 và dữ kiện có thật trong hồ sơ.';
            let suggestions = Array.isArray(secDef?.suggested_questions) ? secDef.suggested_questions : [];

            if (type === 'indicator' && scopeCode) {
                const rawIndCode = scopeCode.startsWith('ind:') ? scopeCode.slice(4) : scopeCode;
                const indMeta = (enrichedJson.detailed_indicators || []).find((i) => i.code === rawIndCode);
                if (indMeta) {
                    description = `${indMeta.label} = ${indMeta.display}${indMeta.title ? ` (${indMeta.title})` : ''}`;
                    suggestions = [
                        `Vì sao ${indMeta.label} mang số ${indMeta.display} lại ảnh hưởng lớn tới con đường của mình?`,
                        `Cách ứng dụng thế mạnh và khắc phục điểm yếu của ${indMeta.label} (${indMeta.display}) trong năm nay?`,
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
                    <button type="button" class="mayhen_numerology_scoped_chip" data-num-chip-question="${escapeHtml(sq)}">
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
            const lookupBtn = e.target.closest('[data-num-concept-lookup]');
            if (lookupBtn) {
                e.stopPropagation();
                openConceptPopover(lookupBtn.getAttribute('data-num-concept-lookup') || '', lookupBtn);
                return;
            }

            const trigger = e.target.closest('[data-num-ask-trigger]');
            if (trigger) {
                openScopedModal({
                    type: trigger.getAttribute('data-scope-type') || 'section',
                    topicCode: trigger.getAttribute('data-topic-code') || null,
                    sectionKey: trigger.getAttribute('data-section-key') || null,
                    label: trigger.getAttribute('data-scope-label') || 'Phần đang xem',
                    prefillQuestion: trigger.getAttribute('data-prefill-question') || '',
                });
                return;
            }

            if (conceptPop?.classList.contains('is-open') && !e.target.closest('[data-num-concept-popover]')) {
                closeConceptPopover();
            }
        });

        scopedBackdrop?.addEventListener('click', (e) => {
            if (e.target === scopedBackdrop || e.target.closest('[data-num-scoped-close]')) {
                closeScopedModal();
                return;
            }
            if (e.target.closest('[data-num-scroll-bottom]')) {
                scrollScopedToBottom(true);
                return;
            }
            if (e.target.closest('[data-num-scoped-suggest-toggle]')) {
                const isCollapsed = scopedSuggestWrap?.classList.contains('is-collapsed');
                setSuggestionsCollapsed(!isCollapsed);
                return;
            }
            const chip = e.target.closest('[data-num-chip-question]');
            if (chip && scopedInput) {
                scopedInput.value = chip.getAttribute('data-num-chip-question') || '';
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
                ? e.target.closest('[data-num-scoped-history], #numScopedInput, [data-num-scoped-suggestions]')
                : null;
            if (!scrollable || scrollable.scrollHeight <= scrollable.clientHeight + 1) {
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
                ? e.target.closest('[data-num-scoped-history], #numScopedInput')
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
            if (e.key === 'Escape') {
                if (scopedBackdrop?.classList.contains('is-open')) {
                    closeScopedModal();
                } else if (conceptPop?.classList.contains('is-open')) {
                    closeConceptPopover();
                }
            }
        });

        async function submitNumerologyFollowup(payload, errEl, submitBtn, defaultLabel, busyHtml = null) {
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
                    submitBtn.textContent = 'Đang đối chiếu hồ sơ...';
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

            const created = await submitNumerologyFollowup(
                {
                    question: q,
                    topic_code: activeScope.topicCode,
                    section_key: activeScope.sectionKey,
                },
                scopedErrBox,
                scopedSubmit,
                'Gửi câu hỏi',
                '<span class="mayhen_numerology_chat_dots" aria-hidden="true"><span></span><span></span><span></span></span>'
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
            const rawScopeVal = String(fd.get('topic_code') || '').trim() || null;
            let sectionKey = null;
            let topicCode = rawScopeVal;
            if (rawScopeVal && rawScopeVal.startsWith('sec:')) {
                const foundEntry = Object.entries(sectionDefs).find(([, def]) => def.scope_code === rawScopeVal);
                if (foundEntry) {
                    sectionKey = foundEntry[0];
                    topicCode = null;
                }
            }
            const btn = fuForm.querySelector('button[type="submit"]');
            const created = await submitNumerologyFollowup(
                { question: q, topic_code: topicCode, section_key: sectionKey },
                fuErrBox,
                btn,
                'Gửi Câu Hỏi Trên Hồ Sơ Này'
            );
            if (created) {
                followupRecords.push(created);
                appendFollowupCardToMainList(created);
                fuForm.reset();
            }
        });

        // Nhật ký phát triển
        const jForm = showApp.querySelector('[data-num-journal-form]');
        const jList = showApp.querySelector('[data-num-journal-list]');
        jForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(jForm);
            const res = await fetch(endpoints.journal, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    milestone_days: Number(fd.get('milestone_days') || 30),
                    current_goal: String(fd.get('current_goal') || ''),
                    actions_taken: String(fd.get('actions_taken') || ''),
                    lessons_learned: String(fd.get('lessons_learned') || ''),
                }),
            });
            const data = await res.json();
            if (res.ok && data.success && data.journal && jList) {
                const div = document.createElement('div');
                div.className = 'mayhen_numerology_note_box';
                div.style.marginBottom = '10px';
                div.innerHTML = `<strong>Mốc ${data.journal.milestone_days} ngày • ${escapeHtml(data.journal.created_at)}</strong><br>${escapeHtml(data.journal.current_goal || data.journal.actions_taken || '')}`;
                jList.prepend(div);
                jForm.reset();
            }
        });

        // Chia sẻ / Thu hồi Thẻ Ẩn Ngày Sinh
        const shareOut = showApp.querySelector('[data-num-share-output]');
        const revokeBtn = showApp.querySelector('[data-num-revoke-btn]');
        showApp.querySelector('[data-num-share-btn]')?.addEventListener('click', async () => {
            const res = await fetch(endpoints.toggle_share, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ action: 'enable' }),
            });
            const data = await res.json();
            if (res.ok && data.share_url) {
                navigator.clipboard?.writeText(data.share_url);
                if (shareOut) {
                    shareOut.innerHTML = `Link Thẻ Chia Sẻ (đã ẩn ngày sinh): <a href="${escapeHtml(data.share_url)}" target="_blank">${escapeHtml(data.share_url)}</a> (đã sao chép)`;
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
                if (shareOut) { shareOut.textContent = 'Đã thu hồi liên kết chia sẻ. Hồ sơ trở lại trạng thái riêng tư.'; shareOut.style.display = 'block'; }
                revokeBtn.style.display = 'none';
            }
        });

        // Phản hồi đánh giá hồ sơ
        showApp.querySelectorAll('[data-num-feedback]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const res = await fetch(endpoints.feedback, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ feedback_rating: btn.getAttribute('data-num-feedback') }),
                });
                if (res.ok) {
                    showApp.querySelectorAll('[data-num-feedback]').forEach((b) => b.classList.toggle('is-active', b === btn));
                }
            });
        });
    }

    // =========================================================================
    // 6. TRANG CHÍNH (/than-so-hoc): 3 TAB TUẦN TỰ + TRA CỨU MIỄN PHÍ + BỘ 12 THẺ
    // =========================================================================
    const landingApp = document.getElementById('mayhenNumerologyApp');
    if (landingApp) {
        const previewUrl = landingApp.getAttribute('data-preview-url') || '';
        const storeUrl = landingApp.getAttribute('data-store-url') || '';
        const canUseAi = landingApp.getAttribute('data-can-ai') === '1';

        const archetypesJson = JSON.parse(document.getElementById('mayhenNumArchetypesJson')?.textContent || '{}');
        const galleryItems = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '11', '22', '33'].map((k) => {
            const a = archetypesJson[k] || {};
            return {
                kicker: ['11', '22', '33'].includes(k) ? 'Master Number' : 'Con Số Cơ Bản',
                value: k,
                display: k === '11' ? '11/2' : (k === '22' ? '22/4' : (k === '33' ? '33/6' : k)),
                is_master: ['11', '22', '33'].includes(k),
                title: a.title || ('Số ' + k),
                short_meaning: a.core_meaning || '',
                full_meaning: `${a.core_meaning || ''} ${a.traditional_view || ''}`,
                love_meaning: a.love_meaning || '',
                reflection: a.reflection_question || '',
            };
        });
        initArtCardsFan(landingApp, galleryItems);

        // Trạng thái 3 tab tuần tự có khóa
        let currentTab = 1;
        let maxUnlockedTab = 1;
        let focusTopic = 'toan_bo';
        let yOverrides = {};
        let skipOverlay = false;

        const tabBtns = Array.from(landingApp.querySelectorAll('[data-num-tab]'));
        const tabPanels = Array.from(landingApp.querySelectorAll('[data-num-panel]'));
        const step1Err = document.getElementById('numStep1Err');
        const step2Err = document.getElementById('numStep2Err');
        const submitErr = document.getElementById('numSubmitErr');

        function validateStep1(show = true) {
            if (step1Err) step1Err.style.display = 'none';
            const dEl = document.getElementById('numBirthDay');
            const mEl = document.getElementById('numBirthMonth');
            const yEl = document.getElementById('numBirthYear');
            [dEl, mEl, yEl].forEach((el) => el?.classList.remove('is-invalid'));

            const d = Number(dEl?.value);
            const m = Number(mEl?.value);
            const y = Number(yEl?.value);
            if (!dEl?.value || !mEl?.value || !yEl?.value) {
                if (show) {
                    if (!dEl?.value) dEl?.classList.add('is-invalid');
                    if (!mEl?.value) mEl?.classList.add('is-invalid');
                    if (!yEl?.value) yEl?.classList.add('is-invalid');
                    if (step1Err) { step1Err.textContent = 'Vui lòng nhập đầy đủ Ngày, Tháng và Năm sinh dương lịch trước khi tiếp tục.'; step1Err.style.display = 'block'; }
                }
                return false;
            }
            const probe = new Date(y, m - 1, d);
            if (y < 1900 || y > 2100 || probe.getFullYear() !== y || probe.getMonth() !== m - 1 || probe.getDate() !== d) {
                if (show && step1Err) {
                    dEl?.classList.add('is-invalid');
                    step1Err.textContent = `Ngày dương lịch ${d}/${m}/${y} không tồn tại. Vui lòng kiểm tra lại.`;
                    step1Err.style.display = 'block';
                }
                return false;
            }
            if (probe > new Date()) {
                if (show && step1Err) {
                    yEl?.classList.add('is-invalid');
                    step1Err.textContent = 'Ngày sinh không được nằm trong tương lai.';
                    step1Err.style.display = 'block';
                }
                return false;
            }
            return true;
        }

        function renderTabs() {
            tabBtns.forEach((b) => {
                const s = Number(b.getAttribute('data-num-tab'));
                const locked = s > maxUnlockedTab;
                const done = s < currentTab || (s < maxUnlockedTab && s !== currentTab);
                b.classList.toggle('is-active', s === currentTab);
                b.classList.toggle('is-locked', locked);
                b.classList.toggle('is-completed', done && s !== currentTab);
                const numEl = b.querySelector('[data-num-tab-badge]');
                if (numEl) numEl.textContent = done && s !== currentTab ? '✓' : String(s);
            });
            tabPanels.forEach((p) => p.classList.toggle('is-active', Number(p.getAttribute('data-num-panel')) === currentTab));
        }

        function goToTab(target) {
            if (target > currentTab) {
                if (currentTab === 1 && !validateStep1(true)) return;
                maxUnlockedTab = Math.max(maxUnlockedTab, target);
            }
            currentTab = target;
            renderTabs();
        }

        tabBtns.forEach((b) => {
            b.addEventListener('click', () => {
                const t = Number(b.getAttribute('data-num-tab'));
                if (t > maxUnlockedTab) {
                    if (t === currentTab + 1) goToTab(t);
                    else validateStep1(true);
                    return;
                }
                goToTab(t);
            });
        });
        landingApp.querySelectorAll('[data-num-next]').forEach((b) => b.addEventListener('click', () => goToTab(Number(b.getAttribute('data-num-next')))));
        landingApp.querySelectorAll('[data-num-prev]').forEach((b) => b.addEventListener('click', () => goToTab(Number(b.getAttribute('data-num-prev')))));
        landingApp.querySelectorAll('[data-num-topic]').forEach((b) => {
            b.addEventListener('click', () => {
                focusTopic = b.getAttribute('data-num-topic') || 'toan_bo';
                landingApp.querySelectorAll('[data-num-topic]').forEach((o) => o.classList.toggle('is-active', o === b));
            });
        });

        function collectPayload() {
            const customLast = (document.getElementById('numGroupLast')?.value || '').trim();
            const customMid = (document.getElementById('numGroupMiddle')?.value || '').trim();
            const customFirst = (document.getElementById('numGroupFirst')?.value || '').trim();
            const hasCustomGroups = customLast !== '' || customMid !== '' || customFirst !== '';
            return {
                display_name: (document.getElementById('numFullBirthName')?.value || '').trim() || 'Hồ Sơ Cá Nhân',
                full_birth_name: (document.getElementById('numFullBirthName')?.value || '').trim(),
                everyday_name: (document.getElementById('numEverydayName')?.value || '').trim(),
                birth_day: Number(document.getElementById('numBirthDay')?.value || 0),
                birth_month: Number(document.getElementById('numBirthMonth')?.value || 0),
                birth_year: Number(document.getElementById('numBirthYear')?.value || 0),
                method_id: document.getElementById('numMethodId')?.value || 'pythagorean_decoz_v1',
                y_mode: document.getElementById('numYMode')?.value || 'vn_phonetic_y_v1',
                name_groups: hasCustomGroups ? { last: customLast, middle: customMid, first: customFirst } : null,
                y_overrides: yOverrides,
                viewed_year: Number(document.getElementById('numViewedYear')?.value || new Date().getFullYear()),
                focus_topic: focusTopic,
                user_context_note: (document.getElementById('numContextNote')?.value || '').trim(),
            };
        }

        // Màn chuyển cảnh rút gọn số êm ái (tuyến tính 4 chặng, không lặp giật, không nhảy chiều cao)
        const calcOverlay = document.getElementById('mayhenNumCalcOverlay');
        const calcDigitsEl = document.getElementById('mayhenNumCalcDigits');
        const calcLabelEl = document.getElementById('mayhenNumCalcLabel');
        const calcBarEl = document.getElementById('mayhenNumCalcBar');
        const calcDotsEl = document.getElementById('mayhenNumCalcDots');
        const etaBadgeEl = document.getElementById('mayhenNumEtaBadge');
        const ritualTimerEl = document.getElementById('mayhenNumRitualTimer');
        document.getElementById('mayhenNumSkipAnim')?.addEventListener('click', () => {
            skipOverlay = true;
            if (calcOverlay) {
                calcOverlay.classList.remove('is-open');
                calcOverlay.setAttribute('aria-hidden', 'true');
            }
        });

        function reduceQuickDigit(n) {
            let v = Math.abs(Number(n) || 0);
            while (v > 9 && v !== 11 && v !== 22 && v !== 33) {
                v = String(v).split('').reduce((acc, ch) => acc + Number(ch), 0);
            }
            return v;
        }

        async function playReductionOverlay(payload, resultPromise, isAiRun = false) {
            if (!calcOverlay || (reduceMotion && !isAiRun) || (skipOverlay && !isAiRun)) {
                return await resultPromise;
            }

            if (etaBadgeEl) {
                etaBadgeEl.hidden = !isAiRun;
            }

            const startedMs = Date.now();
            let timerInterval = null;
            if (isAiRun && ritualTimerEl) {
                ritualTimerEl.textContent = '00:00';
                timerInterval = window.setInterval(() => {
                    const sec = Math.floor((Date.now() - startedMs) / 1000);
                    const m = String(Math.floor(sec / 60)).padStart(2, '0');
                    const s = String(sec % 60).padStart(2, '0');
                    ritualTimerEl.textContent = `${m}:${s}`;
                }, 1000);
            }

            const dd = String(payload.birth_day).padStart(2, '0');
            const mm = String(payload.birth_month).padStart(2, '0');
            const yyyy = String(payload.birth_year);
            const rDay = reduceQuickDigit(payload.birth_day);
            const rMonth = reduceQuickDigit(payload.birth_month);
            const rYear = reduceQuickDigit(payload.birth_year);
            const rTotal = rDay + rMonth + rYear;
            const rFinal = reduceQuickDigit(rTotal);

            const stages = [
                {
                    digits: `${dd} · ${mm} · ${yyyy}`,
                    label: 'Chuẩn hóa ngày sinh dương lịch & quy ước hệ tính đã chọn',
                    pct: 22,
                    delay: 0,
                },
                {
                    digits: `${rDay} + ${rMonth} + ${rYear} = ${rTotal}`,
                    label: 'Tách nhóm Ngày · Tháng · Năm & đối chiếu số Master',
                    pct: 52,
                    delay: 1150,
                },
                {
                    digits: `Đường Đời ${rFinal === 11 ? '11/2' : (rFinal === 22 ? '22/4' : (rFinal === 33 ? '33/6' : rFinal))}`,
                    label: 'Lập Biểu đồ 3×3, 4 Đỉnh cao, 4 Thử thách & hệ chỉ số cốt lõi',
                    pct: 78,
                    delay: 2350,
                },
                {
                    digits: isAiRun ? 'Luận Giải AI 2 Pha' : 'Hoàn thiện hồ sơ',
                    label: isAiRun
                        ? 'AI đang phân tích siêu chi tiết từng chỉ số & chu kỳ (2–5 phút để đạt độ chính xác)…'
                        : 'Đang tổng hợp luận giải cá nhân hóa dựa trên đúng dữ liệu của bạn…',
                    pct: 93,
                    delay: 3650,
                },
            ];

            const dots = calcDotsEl ? Array.from(calcDotsEl.querySelectorAll('.mayhen_numerology_calc_dot')) : [];
            const scheduledTimers = [];
            let closed = false;

            function setStage(idx, animateSwap) {
                if (closed) return;
                const st = stages[idx];
                if (!st) return;

                const applyContent = () => {
                    if (closed) return;
                    if (calcDigitsEl) {
                        calcDigitsEl.textContent = st.digits;
                        calcDigitsEl.classList.remove('is-fading');
                    }
                    if (calcLabelEl) {
                        calcLabelEl.textContent = st.label;
                        calcLabelEl.classList.remove('is-fading');
                    }
                };

                if (animateSwap) {
                    calcDigitsEl?.classList.add('is-fading');
                    calcLabelEl?.classList.add('is-fading');
                    scheduledTimers.push(setTimeout(applyContent, 240));
                } else {
                    applyContent();
                }

                if (calcBarEl) calcBarEl.style.width = `${st.pct}%`;
                dots.forEach((d, di) => {
                    d.classList.toggle('is-active', di === idx);
                    d.classList.toggle('is-done', di < idx);
                });
            }

            if (calcBarEl) calcBarEl.style.width = '12%';
            setStage(0, false);
            calcOverlay.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => calcOverlay.classList.add('is-open'));

            for (let idx = 1; idx < stages.length; idx++) {
                scheduledTimers.push(setTimeout(() => setStage(idx, true), stages[idx].delay));
            }

            try {
                const [res] = await Promise.all([resultPromise, new Promise((r) => setTimeout(r, 950))]);
                if (calcBarEl) {
                    calcBarEl.style.width = '100%';
                    await new Promise((r) => setTimeout(r, 220));
                }
                return res;
            } finally {
                closed = true;
                if (timerInterval) clearInterval(timerInterval);
                scheduledTimers.forEach((t) => clearTimeout(t));
                calcOverlay.classList.remove('is-open');
                calcOverlay.setAttribute('aria-hidden', 'true');
                if (etaBadgeEl) etaBadgeEl.hidden = true;
            }
        }

        // Render kết quả tra cứu nhanh miễn phí ngay trên trang cho khách (có bảng "Cách tính của tôi" + bật/tắt chữ Y!)
        const instantHost = document.getElementById('mayhenNumInstantResult');
        function renderInstantResult(data) {
            if (!instantHost || !data?.calculation) return;
            const c = data.calculation;
            const e = data.enriched;
            const interp = data.interpretation;
            const core = c.core_numbers;
            const ext = c.extended_numbers;
            const nb = c.name_breakdown;

            const lettersHtml = nb
                ? nb.letters.map((l) => `
                    <div class="mayhen_numerology_letter_tile ${l.type === 'vowel' ? 'mayhen_numerology_letter_tile--vowel' : 'mayhen_numerology_letter_tile--consonant'}">
                        <div style="font-weight:800; font-size:14px;">${escapeHtml(l.char)}</div>
                        <div style="color:#5C5248;">${l.value} · ${l.type === 'vowel' ? 'NA' : 'PA'}</div>
                        ${l.is_y ? `<button type="button" data-toggle-y="${l.index}" data-next-type="${l.type === 'vowel' ? 'consonant' : 'vowel'}">Đổi Y</button>` : ''}
                    </div>
                `).join('')
                : '<p style="font-size:13px; color:#5C5248;">Bạn chưa nhập họ tên khai sinh — hãy bổ sung họ tên ở Bước 2 để tính thêm Sứ Mệnh, Linh Hồn, Nhân Cách.</p>';

            instantHost.innerHTML = `
                <div class="mayhen_numerology_signature_hero">
                    <div class="mayhen_numerology_giant_badge">
                        <span class="mayhen_numerology_chapter_tag">Chỉ Số Đường Đời</span>
                        <div class="mayhen_numerology_giant_number">${escapeHtml(core.life_path.display)}</div>
                        <div style="font-size:12px; color:#1E4D3B; font-weight:700;">${escapeHtml(e.indicator_meanings?.life_path?.title || '')}</div>
                    </div>
                    <div>
                        <span class="mayhen_numerology_chapter_tag">${escapeHtml(c.method_name)} • ${escapeHtml(c.completeness_label)}</span>
                        <h2 style="font-family:'MayhenV3EditorialSerif',Georgia,serif; margin:0 0 8px;">${escapeHtml(interp.headline)}</h2>
                        <p style="font-size:15px; line-height:1.72; margin:0 0 12px;">${escapeHtml(interp.summary)}</p>
                        <div class="mayhen_numerology_note_box">${escapeHtml(core.life_path_comparison?.explanation || '')}</div>
                    </div>
                </div>

                <div class="mayhen_numerology_stat_strip" style="margin-bottom:22px;">
                    <div class="mayhen_numerology_stat_cell">
                        <div class="mayhen_numerology_stat_lbl">Sứ Mệnh</div>
                        <div class="mayhen_numerology_stat_val">${escapeHtml(core.expression?.display || '—')}</div>
                    </div>
                    <div class="mayhen_numerology_stat_cell">
                        <div class="mayhen_numerology_stat_lbl">Linh Hồn</div>
                        <div class="mayhen_numerology_stat_val">${escapeHtml(core.soul_urge?.display || '—')}</div>
                    </div>
                    <div class="mayhen_numerology_stat_cell">
                        <div class="mayhen_numerology_stat_lbl">Nhân Cách</div>
                        <div class="mayhen_numerology_stat_val">${escapeHtml(core.personality?.display || '—')}</div>
                    </div>
                    <div class="mayhen_numerology_stat_cell">
                        <div class="mayhen_numerology_stat_lbl">Ngày Sinh / Năm CN ${c.personal_cycles.personal_year.year}</div>
                        <div class="mayhen_numerology_stat_val">${escapeHtml(core.birthday.display)} · ${escapeHtml(c.personal_cycles.personal_year.value)}</div>
                    </div>
                </div>

                <details class="mayhen_numerology_note_box" open style="margin-bottom:22px; background:#FCFBF9;">
                    <summary style="font-weight:700; cursor:pointer; color:#1E4D3B;">Cách tính của tôi — Kiểm tra từng bước quy đổi &amp; phân loại nguyên âm / phụ âm</summary>
                    <div style="margin-top:12px;">
                        <p style="margin:0 0 8px;"><strong>Bước tính Đường Đời:</strong> ${(core.life_path.formula_steps || []).map(escapeHtml).join(' → ')}</p>
                        ${nb ? `<p style="margin:0 0 6px;"><strong>Họ tên chuẩn hóa:</strong> ${escapeHtml(nb.original_name)} → <code>${escapeHtml(nb.normalized_name)}</code> • Kiểm tra toàn vẹn: Nguyên âm (${nb.raw_vowel_sum}) + Phụ âm (${nb.raw_consonant_sum}) = Tổng (${nb.raw_all_sum})</p>` : ''}
                        <div class="mayhen_numerology_letter_row">${lettersHtml}</div>
                    </div>
                </details>

                <div style="margin-bottom:24px;">
                    <h3 style="font-family:'MayhenV3EditorialSerif',Georgia,serif; margin:0 0 12px;">Biểu Đồ Ngày Sinh 3×3 (Lớp Độc Lập Theo David A. Phillips)</h3>
                    <div data-instant-birthchart></div>
                </div>

                <div style="margin-bottom:24px;">
                    <div class="mayhen_numerology_wave_head" style="margin-top:0;">
                        <div>
                            <h3 class="mayhen_numerology_wave_title">Biểu Đồ Sóng Vận Số Chu Kỳ 9 Năm</h3>
                            <p class="mayhen_numerology_wave_sub">Nhịp lên xuống tự nhiên của 9 năm liên tiếp — chạm vào từng đỉnh sóng để xem chủ đề từng năm.</p>
                        </div>
                    </div>
                    <div class="mayhen_numerology_wave_card" data-instant-cycle-wave></div>
                </div>

                <div class="mayhen_numerology_note_box" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div>
                        <strong>Muốn lưu hồ sơ nghệ thuật 10 chương, mở Luận Giải AI 6 Tầng, Hỏi Sâu AI &amp; Xuất Thẻ PNG?</strong><br>
                        Hoàn toàn miễn phí cho thành viên Mây Hẹn (tự động cấp gói Thành Viên Premium 0đ).
                    </div>
                    <button type="button" class="mayhen_numerology_btn_crimson" id="numUpgradeToAiBtn">Lưu Hồ Sơ &amp; Luận Giải AI 6 Tầng</button>
                </div>
            `;
            instantHost.style.display = 'block';
            renderBirthChart3x3(instantHost.querySelector('[data-instant-birthchart]'), c.birth_chart, e);
            renderNineYearWaveChart(instantHost.querySelector('[data-instant-cycle-wave]'), c.personal_cycles?.nine_year_timeline || []);

            // Cho phép người dùng đảo vai trò từng chữ Y và tính lại ngay lập tức
            instantHost.querySelectorAll('[data-toggle-y]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const idx = Number(btn.getAttribute('data-toggle-y'));
                    yOverrides[idx] = btn.getAttribute('data-next-type');
                    runFreePreview();
                });
            });
            document.getElementById('numUpgradeToAiBtn')?.addEventListener('click', runSaveWithAi);
            instantHost.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
        }

        async function runFreePreview() {
            if (!validateStep1(true)) { goToTab(1); return; }
            if (submitErr) submitErr.style.display = 'none';
            const payload = collectPayload();
            const req = fetch(previewUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload),
            });
            const res = await playReductionOverlay(payload, req);
            const data = await res.json();
            if (!res.ok || !data.success) {
                if (submitErr) { submitErr.textContent = data.message || 'Thông tin nhập chưa hợp lệ.'; submitErr.style.display = 'block'; }
                return;
            }
            renderInstantResult(data);
        }

        let idemKey = 'num_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 8);
        async function runSaveWithAi() {
            if (!validateStep1(true)) { goToTab(1); return; }
            if (!canUseAi) {
                document.getElementById('mayhenNumLoginModal')?.classList.add('is-open');
                return;
            }
            if (submitErr) submitErr.style.display = 'none';
            const btn = document.getElementById('numSaveAiBtn');
            if (btn) { btn.disabled = true; btn.textContent = 'Đang lập hồ sơ & luận giải AI 6 tầng...'; }
            try {
                const payload = Object.assign(collectPayload(), { idempotency_key: idemKey });
                const req = fetch(storeUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(payload),
                });
                const res = await playReductionOverlay(payload, req, true);
                const data = await res.json();
                if (res.status === 401 || res.status === 403) {
                    document.getElementById('mayhenNumLoginModal')?.classList.add('is-open');
                    return;
                }
                if (!res.ok || !data.success) {
                    if (submitErr) { submitErr.textContent = data.message || 'Không thể lưu hồ sơ lúc này.'; submitErr.style.display = 'block'; }
                    return;
                }
                if (data.result_url) window.location.assign(data.result_url);
            } finally {
                if (btn) { btn.disabled = false; btn.textContent = 'Lưu Hồ Sơ & Luận Giải AI 6 Tầng (0đ)'; }
            }
        }

        document.getElementById('numFreePreviewBtn')?.addEventListener('click', runFreePreview);
        document.getElementById('numSaveAiBtn')?.addEventListener('click', runSaveWithAi);
        document.getElementById('mayhenNumLoginClose')?.addEventListener('click', () => {
            document.getElementById('mayhenNumLoginModal')?.classList.remove('is-open');
        });
        renderTabs();
    }

    // =========================================================================
    // 7. THẦN SỐ HỌC TÌNH DUYÊN & KHÔNG GIAN "CÂU CHUYỆN HAI CHÚNG TA"
    // =========================================================================
    const loveForm = document.getElementById('mayhenNumLoveForm');
    loveForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(loveForm);
        const err = document.getElementById('mayhenNumLoveErr');
        const btn = loveForm.querySelector('button[type="submit"]');
        if (err) err.style.display = 'none';
        if (btn) { btn.disabled = true; btn.textContent = 'Đang đối chiếu 2 hồ sơ độc lập...'; }
        try {
            const res = await fetch(loveForm.getAttribute('action') || '', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    person_a_name: String(fd.get('person_a_name') || ''),
                    person_a_day: Number(fd.get('person_a_day')),
                    person_a_month: Number(fd.get('person_a_month')),
                    person_a_year: Number(fd.get('person_a_year')),
                    person_b_name: String(fd.get('person_b_name') || ''),
                    person_b_day: Number(fd.get('person_b_day')),
                    person_b_month: Number(fd.get('person_b_month')),
                    person_b_year: Number(fd.get('person_b_year')),
                    relationship_stage: String(fd.get('relationship_stage') || 'dang_yeu'),
                    partner_consent_confirmed: Boolean(fd.get('partner_consent_confirmed')),
                    couple_note: String(fd.get('couple_note') || ''),
                }),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                if (err) { err.textContent = data.message || 'Vui lòng kiểm tra lại ngày sinh và xác nhận đồng thuận.'; err.style.display = 'block'; }
                return;
            }
            if (data.result_url) window.location.assign(data.result_url);
        } finally {
            if (btn) { btn.disabled = false; btn.textContent = 'Phân Tích Thần Số Học Tình Duyên'; }
        }
    });

    const storyForm = document.getElementById('mayhenNumCoupleStoryForm');
    storyForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(storyForm);
        const answers = {};
        for (const [k, v] of fd.entries()) {
            if (k.startsWith('q_')) answers[k] = String(v);
        }
        const statusEl = document.getElementById('mayhenNumStoryStatus');
        const res = await fetch(storyForm.getAttribute('action') || '', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                answers,
                promise_a: String(fd.get('promise_a') || ''),
                promise_b: String(fd.get('promise_b') || ''),
                message_note: String(fd.get('message_note') || ''),
            }),
        });
        if (res.ok && statusEl) {
            statusEl.textContent = 'Đã lưu Câu chuyện hai chúng ta & Lời hẹn 30 ngày vào hồ sơ riêng tư.';
            statusEl.style.display = 'block';
        }
    });

    // Tìm kiếm không dấu Bách Khoa Thần Số Học
    const libSearch = document.getElementById('mayhenNumLibSearch');
    if (libSearch) {
        const items = Array.from(document.querySelectorAll('[data-num-lib-item]'));
        libSearch.addEventListener('input', () => {
            const q = stripTones(libSearch.value.trim());
            items.forEach((item) => {
                const hay = stripTones(item.getAttribute('data-num-lib-item') || '');
                item.style.display = q === '' || hay.includes(q) ? '' : 'none';
            });
        });
    }
});
