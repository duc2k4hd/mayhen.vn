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
                    inspector.innerHTML = `
                        <strong>Ô số ${d} (${escapeHtml(note.plane || '')}) — Xuất hiện ${cell.count} lần:</strong><br>
                        ${escapeHtml(note.note || '')}
                    `;
                }
            });
        });
    }

    // =========================================================================
    // 4. XUẤT THẺ NGHỆ THUẬT CON SỐ PNG HIDPI (1:1, 9:16, 4:5 — MẶC ĐỊNH ẨN NGÀY SINH)
    // =========================================================================
    function exportArtCardPng(calc, interpretation, ratioKey, hideBirthDate) {
        const dims = {
            '1:1': [1080, 1080],
            '9:16': [1080, 1920],
            '4:5': [1080, 1350],
        }[ratioKey] || [1080, 1080];

        const [W, H] = dims;
        const canvas = document.createElement('canvas');
        canvas.width = W;
        canvas.height = H;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        // Nền giấy mỹ thuật trắng ngà & viền dập nổi letterpress
        ctx.fillStyle = '#FAF5EB';
        ctx.fillRect(0, 0, W, H);
        ctx.strokeStyle = '#CFA860';
        ctx.lineWidth = 4;
        ctx.strokeRect(44, 44, W - 88, H - 88);
        ctx.strokeStyle = '#E3D5BC';
        ctx.lineWidth = 1.5;
        ctx.strokeRect(58, 58, W - 116, H - 116);

        const lp = calc?.core_numbers?.life_path || { display: '—', value: 1 };
        ctx.textAlign = 'center';

        ctx.fillStyle = '#1E4D3B';
        ctx.font = 'bold 24px sans-serif';
        ctx.fillText('MÂY HẸN · DẤU ẤN CON SỐ', W / 2, Math.round(H * 0.14));

        // Hình học vòng tròn tối giản làm nền con số
        const cy = Math.round(H * 0.42);
        ctx.strokeStyle = 'rgba(207, 168, 96, 0.55)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(W / 2, cy, 170, 0, Math.PI * 2);
        ctx.stroke();
        ctx.beginPath();
        ctx.arc(W / 2, cy, 146, 0, Math.PI * 2);
        ctx.stroke();

        ctx.fillStyle = '#85182A';
        ctx.font = 'bold 170px Georgia, serif';
        ctx.fillText(String(lp.display), W / 2, cy + 56);

        ctx.fillStyle = '#292524';
        ctx.font = 'bold 34px Georgia, serif';
        ctx.fillText(String(interpretation?.headline || 'Chỉ Số Đường Đời ' + lp.display).slice(0, 52), W / 2, Math.round(H * 0.66));

        ctx.fillStyle = '#5C5248';
        ctx.font = '24px sans-serif';
        const subLine = hideBirthDate
            ? (calc?.display_name ? `${calc.display_name} • Ngày sinh ẩn riêng tư` : 'Hồ Sơ Dấu Ấn Con Số')
            : `${calc?.display_name || ''} • ${calc?.birth?.label || ''}`;
        ctx.fillText(subLine, W / 2, Math.round(H * 0.72));

        // Câu hỏi chiêm nghiệm tinh tế
        const quote = String(interpretation?.reflection_question || 'Hiểu mình, hiểu người, vun đắp nhân duyên.');
        ctx.fillStyle = '#1E4D3B';
        ctx.font = 'italic 24px Georgia, serif';
        const words = quote.split(' ');
        let line = '';
        let y = Math.round(H * 0.80);
        for (const w of words) {
            const test = line ? `${line} ${w}` : w;
            if (ctx.measureText(test).width > W - 180) {
                ctx.fillText(line, W / 2, y);
                line = w;
                y += 36;
            } else {
                line = test;
            }
        }
        if (line) ctx.fillText(line, W / 2, y);

        ctx.fillStyle = '#7A6E60';
        ctx.font = '20px sans-serif';
        ctx.fillText('mayhen.vn/than-so-hoc — ' + (calc?.method_name || 'Pythagorean Hans Decoz'), W / 2, H - 84);

        const a = document.createElement('a');
        a.download = `mayhen-than-so-hoc-${ratioKey.replace(':', 'x')}-${Date.now()}.png`;
        a.href = canvas.toDataURL('image/png');
        a.click();
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

        // Xuất ảnh thẻ nghệ thuật 3 tỉ lệ
        showApp.querySelectorAll('[data-num-export-ratio]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const ratio = btn.getAttribute('data-num-export-ratio') || '1:1';
                const hideCb = showApp.querySelector('[data-num-export-hide-birth]');
                exportArtCardPng(calcJson, interpJson, ratio, Boolean(hideCb?.checked));
            });
        });

        // Hỏi Sâu AI
        const fuForm = showApp.querySelector('[data-num-followup-form]');
        const fuList = showApp.querySelector('[data-num-followup-list]');
        fuForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(fuForm);
            const q = String(fd.get('question') || '').trim();
            if (q.length < 4) return;
            const btn = fuForm.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.textContent = 'Đang đối chiếu hồ sơ...'; }
            try {
                const res = await fetch(endpoints.followup, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ question: q }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) { alert(data.message || 'Không thể gửi câu hỏi lúc này.'); return; }
                if (fuList && data.followup) {
                    const div = document.createElement('div');
                    div.className = 'mayhen_numerology_note_box';
                    div.style.marginBottom = '10px';
                    div.innerHTML = `<div style="font-weight:700; color:#85182A; margin-bottom:4px;">Hỏi: ${escapeHtml(data.followup.question)}</div><div>${escapeHtml(data.followup.answer)}</div>`;
                    fuList.appendChild(div);
                }
                fuForm.reset();
            } finally {
                if (btn) { btn.disabled = false; btn.textContent = 'Gửi Câu Hỏi Trên Hồ Sơ Này'; }
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

        // Phản hồi & Luận lại AI
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

        const reBtn = showApp.querySelector('[data-num-reinterpret-btn]');
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

        async function playReductionOverlay(payload, resultPromise) {
            if (!calcOverlay || reduceMotion || skipOverlay) return await resultPromise;

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
                    digits: 'Hoàn thiện hồ sơ',
                    label: 'Đang tổng hợp luận giải cá nhân hóa dựa trên đúng dữ liệu của bạn…',
                    pct: 93,
                    delay: 3650,
                },
            ];

            const dots = calcDotsEl ? Array.from(calcDotsEl.querySelectorAll('.mayhen_numerology_calc_dot')) : [];
            const scheduledTimers = [];
            let closed = false;

            function setStage(idx, animateSwap) {
                if (closed || skipOverlay) return;
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
                if (!skipOverlay && calcBarEl) {
                    calcBarEl.style.width = '100%';
                    await new Promise((r) => setTimeout(r, 220));
                }
                return res;
            } finally {
                closed = true;
                scheduledTimers.forEach((t) => clearTimeout(t));
                calcOverlay.classList.remove('is-open');
                calcOverlay.setAttribute('aria-hidden', 'true');
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
                const res = await playReductionOverlay(payload, req);
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
