/* ==========================================================================
   MÂY HẸN – TỨ TRỤ NHÂN SINH (/bat-tu) — Pure Vanilla JS
   Interactive 3D Four Pillars, Five Elements SVG, Ten Gods Orbit, Time Travel,
   Method Comparison, HiDPI PNG Export & Couple Relationship BaZi
   ========================================================================== */
(function () {
    'use strict';

    const EL_COLORS = {
        'Mộc': '#1E4D3B',
        'Hỏa': '#85182A',
        'Thổ': '#9A7228',
        'Kim': '#52606D',
        'Thủy': '#1B4965',
    };

    const EL_CLASSES = {
        'Mộc': 'mayhen_bazi_el--moc',
        'Hỏa': 'mayhen_bazi_el--hoa',
        'Thổ': 'mayhen_bazi_el--tho',
        'Kim': 'mayhen_bazi_el--kim',
        'Thủy': 'mayhen_bazi_el--thuy',
    };

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    async function postJson(url, payload) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.message || 'Không thể xử lý yêu cầu. Vui lòng kiểm tra lại dữ liệu.');
        }
        return data;
    }

    // =========================================================================
    // 1. RENDER FOUR PILLARS (3D + 2D TABLE)
    // =========================================================================
    function renderPillars3D(container, chart, animate) {
        if (!container || !chart) return;
        const order = [
            { key: 'year', title: 'Trụ Năm' },
            { key: 'month', title: 'Trụ Tháng' },
            { key: 'day', title: 'Trụ Ngày' },
            { key: 'hour', title: 'Trụ Giờ' },
        ];

        container.innerHTML = order.map((item, idx) => {
            const p = chart.pillars?.[item.key];
            if (!p) {
                return `
                    <div class="mayhen_bazi_pillar_col mayhen_bazi_pillar_col--empty ${animate ? 'is-forming' : ''}" style="animation-delay:${idx * 120}ms">
                        <div class="mayhen_bazi_pillar_head">${item.title}</div>
                        <span class="mayhen_bazi_pillar_tg">Chưa có giờ sinh</span>
                        <div class="mayhen_bazi_glyph_box">
                            <span class="mayhen_bazi_hanzi">?</span>
                            <span class="mayhen_bazi_vi_name">Giữ trống</span>
                            <span class="mayhen_bazi_el_meta">Không mặc định 12:00</span>
                        </div>
                        <div class="mayhen_bazi_pillar_foot">Xem 12 phương án Trụ Giờ ở phần nghiên cứu bên dưới.</div>
                    </div>
                `;
            }

            const isDay = item.key === 'day';
            const sCls = EL_CLASSES[p.stem.element] || '';
            const bCls = EL_CLASSES[p.branch.element] || '';
            const hiddenHtml = (p.hidden_stems || []).map((hs) => `
                <div class="mayhen_bazi_hidden_chip">
                    <span>${esc(hs.hanzi)} ${esc(hs.name)} (${esc(hs.element)})</span>
                    <span>${esc(hs.ten_god?.short || '')}</span>
                </div>
            `).join('');

            return `
                <div class="mayhen_bazi_pillar_col ${isDay ? 'mayhen_bazi_pillar_col--day' : ''} ${animate ? 'is-forming' : ''}" data-pillar="${item.key}" style="animation-delay:${idx * 140}ms">
                    <div class="mayhen_bazi_pillar_head">${esc(item.title)}</div>
                    <span class="mayhen_bazi_pillar_tg">${esc(p.ten_god?.name || '')}</span>
                    <div class="mayhen_bazi_glyph_box">
                        <span class="mayhen_bazi_hanzi ${sCls}">${esc(p.stem.hanzi)}</span>
                        <span class="mayhen_bazi_vi_name ${sCls}">${esc(p.stem.name)}</span>
                        <span class="mayhen_bazi_el_meta">${esc(p.stem.yin_yang)} ${esc(p.stem.element)}</span>
                    </div>
                    <div class="mayhen_bazi_glyph_box">
                        <span class="mayhen_bazi_hanzi ${bCls}">${esc(p.branch.hanzi)}</span>
                        <span class="mayhen_bazi_vi_name ${bCls}">${esc(p.branch.name)} (${esc(p.branch.zodiac)})</span>
                        <span class="mayhen_bazi_el_meta">${esc(p.branch.element)} · ${esc(p.life_stage)}</span>
                    </div>
                    <div class="mayhen_bazi_hidden_list">${hiddenHtml}</div>
                    <div class="mayhen_bazi_pillar_foot">
                        <div>Nạp Âm: ${esc(p.nap_am?.name || '')}</div>
                        <div>Không Vong: ${esc(p.kong_wang?.label || '')}</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderTable2D(container, chart) {
        if (!container || !chart) return;
        const cols = ['year', 'month', 'day', 'hour'];
        const cell = (fn) => cols.map((k) => {
            const p = chart.pillars?.[k];
            return `<td>${p ? fn(p, k) : '<span style="color:#6E6259;">Chưa có</span>'}</td>`;
        }).join('');

        container.innerHTML = `
            <table class="mayhen_bazi_table">
                <thead>
                    <tr>
                        <th>Thành phần</th>
                        <th>Trụ Năm</th>
                        <th>Trụ Tháng</th>
                        <th>Trụ Ngày (Nhật Chủ)</th>
                        <th>Trụ Giờ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><th>Thập Thần</th>${cell((p) => `<strong>${esc(p.ten_god?.name)}</strong> (${esc(p.ten_god?.hanzi)})`)}</tr>
                    <tr><th>Thiên Can</th>${cell((p) => `<strong class="${EL_CLASSES[p.stem.element] || ''}">${esc(p.stem.hanzi)} ${esc(p.stem.name)}</strong> (${esc(p.stem.yin_yang)} ${esc(p.stem.element)})`)}</tr>
                    <tr><th>Địa Chi</th>${cell((p) => `<strong class="${EL_CLASSES[p.branch.element] || ''}">${esc(p.branch.hanzi)} ${esc(p.branch.name)}</strong> (${esc(p.branch.zodiac)} · ${esc(p.branch.element)})`)}</tr>
                    <tr><th>Tàng Can & Thập Thần</th>${cell((p) => (p.hidden_stems || []).map((h) => `${esc(h.name)} (${esc(h.role_label)} · ${esc(h.ten_god?.name)})`).join('<br>'))}</tr>
                    <tr><th>Nạp Âm</th>${cell((p) => `${esc(p.nap_am?.name)} (${esc(p.nap_am?.element)})`)}</tr>
                    <tr><th>Vòng Trường Sinh</th>${cell((p) => esc(p.life_stage))}</tr>
                    <tr><th>Tuần Không Vong</th>${cell((p) => esc(p.kong_wang?.label))}</tr>
                </tbody>
            </table>
        `;
    }

    // =========================================================================
    // 2. FIVE ELEMENTS INTERACTIVE PENTAGON SVG (3 INDEPENDENT MODES)
    // =========================================================================
    function renderFiveElements(svgEl, barsEl, chart, modeKey, activeEl) {
        if (!chart?.elements) return;
        const mode = chart.elements[modeKey] || chart.elements.eight_chars;
        const order = ['Mộc', 'Hỏa', 'Thổ', 'Kim', 'Thủy'];

        if (barsEl) {
            barsEl.innerHTML = `<p style="font-size:12.5px;color:#6E6259;margin:0 0 10px;">${esc(mode.label)}</p>` +
                order.map((el) => {
                    const pct = mode.percentages?.[el] ?? 0;
                    const cnt = mode.counts?.[el] ?? 0;
                    return `
                        <div class="mayhen_bazi_bar_row">
                            <strong style="color:${EL_COLORS[el]}">${el}</strong>
                            <div class="mayhen_bazi_bar_track">
                                <div class="mayhen_bazi_bar_fill" style="width:${Math.min(100, pct)}%;background:${EL_COLORS[el]};"></div>
                            </div>
                            <span style="text-align:right;font-size:12px;">${cnt} (${pct}%)</span>
                        </div>
                    `;
                }).join('') +
                `<p style="font-size:11.5px;color:#6E6259;margin:8px 0 0;">${esc(chart.elements.disclaimer)}</p>`;
        }

        if (!svgEl) return;
        const cx = 140, cy = 132, r = 92;
        const coords = order.map((el, i) => {
            const ang = (-90 + i * 72) * Math.PI / 180;
            return { el, x: cx + r * Math.cos(ang), y: cy + r * Math.sin(ang) };
        });

        let genLines = '';
        let ctrlLines = '';
        for (let i = 0; i < 5; i++) {
            const a = coords[i];
            const b = coords[(i + 1) % 5];
            const c = coords[(i + 2) % 5];
            const hiGen = !activeEl || activeEl === a.el || activeEl === b.el;
            const hiCtrl = !activeEl || activeEl === a.el || activeEl === c.el;
            genLines += `<line x1="${a.x}" y1="${a.y}" x2="${b.x}" y2="${b.y}" stroke="#1E4D3B" stroke-width="${hiGen ? 2 : 1}" stroke-opacity="${hiGen ? 0.65 : 0.16}" />`;
            ctrlLines += `<line x1="${a.x}" y1="${a.y}" x2="${c.x}" y2="${c.y}" stroke="#85182A" stroke-dasharray="4 4" stroke-width="${hiCtrl ? 1.5 : 1}" stroke-opacity="${hiCtrl ? 0.55 : 0.12}" />`;
        }

        const nodes = coords.map((pt) => {
            const pct = mode.percentages?.[pt.el] ?? 0;
            const isSel = activeEl === pt.el;
            return `
                <g class="mayhen_bazi_el_node" data-el="${pt.el}" style="cursor:pointer;">
                    <circle cx="${pt.x}" cy="${pt.y}" r="${isSel ? 28 : 24}" fill="${EL_COLORS[pt.el]}" fill-opacity="${isSel ? 1 : 0.9}" stroke="#CFA860" stroke-width="${isSel ? 3 : 1.5}" />
                    <text x="${pt.x}" y="${pt.y - 2}" text-anchor="middle" fill="#FAF5EB" font-size="12" font-weight="700">${pt.el}</text>
                    <text x="${pt.x}" y="${pt.y + 12}" text-anchor="middle" fill="#F4DCAA" font-size="10">${pct}%</text>
                </g>
            `;
        }).join('');

        svgEl.innerHTML = `
            <svg viewBox="0 0 280 264" width="280" height="264" role="img" aria-label="Đồ thị Ngũ Hành Tương Sinh Tương Khắc">
                ${genLines}
                ${ctrlLines}
                ${nodes}
                <text x="140" y="256" text-anchor="middle" fill="#6E6259" font-size="10">Nét liền: Tương Sinh · Nét đứt: Tương Khắc (Bấm chọn hành)</text>
            </svg>
        `;

        svgEl.querySelectorAll('.mayhen_bazi_el_node').forEach((n) => {
            n.addEventListener('click', () => {
                const chosen = n.getAttribute('data-el');
                renderFiveElements(svgEl, barsEl, chart, modeKey, activeEl === chosen ? null : chosen);
            });
        });
    }

    // =========================================================================
    // 3. TEN GODS ORBIT SVG
    // =========================================================================
    function renderTenGodsOrbit(svgEl, detailEl, chart) {
        if (!svgEl || !chart?.ten_gods_summary) return;
        const list = chart.ten_gods_summary;
        const cx = 160, cy = 148, r = 108;

        const nodes = list.map((tg, i) => {
            const ang = (-90 + i * 36) * Math.PI / 180;
            const x = cx + r * Math.cos(ang);
            const y = cy + r * Math.sin(ang);
            const present = Boolean(tg.present);
            return `
                <g class="mayhen_bazi_tg_node" data-idx="${i}" style="cursor:pointer;">
                    <line x1="${cx}" y1="${cy}" x2="${x}" y2="${y}" stroke="${present ? '#CFA860' : '#D6CFC2'}" stroke-width="${present ? 1.5 : 1}" stroke-dasharray="${present ? '' : '3 3'}" />
                    <circle cx="${x}" cy="${y}" r="${present ? 23 : 18}" fill="${present ? '#1E4D3B' : '#F3EAD8'}" stroke="${present ? '#CFA860' : '#BFB5A5'}" stroke-width="1.5" />
                    <text x="${x}" y="${y + 3}" text-anchor="middle" fill="${present ? '#FAF5EB' : '#6E6259'}" font-size="10.5" font-weight="${present ? 700 : 500}">${esc(tg.short)}</text>
                </g>
            `;
        }).join('');

        svgEl.innerHTML = `
            <svg viewBox="0 0 320 296" width="320" height="296" role="img" aria-label="Thập Thần Orbit xoay quanh Nhật Chủ">
                ${nodes}
                <circle cx="${cx}" cy="${cy}" r="34" fill="#85182A" stroke="#CFA860" stroke-width="2.5" />
                <text x="${cx}" y="${cy - 4}" text-anchor="middle" fill="#FAF5EB" font-size="15" font-weight="700">${esc(chart.day_master?.hanzi)} ${esc(chart.day_master?.name)}</text>
                <text x="${cx}" y="${cy + 13}" text-anchor="middle" fill="#F4DCAA" font-size="10">Nhật Chủ</text>
            </svg>
        `;

        const showDetail = (tg) => {
            if (!detailEl || !tg) return;
            detailEl.innerHTML = `
                <div style="padding:12px 14px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.16);border-radius:10px;font-size:13px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <strong>${esc(tg.name)} (${esc(tg.hanzi)}) — ${esc(tg.group)}</strong>
                        <span class="mayhen_bazi_badge ${tg.present ? '' : 'mayhen_bazi_badge--gold'}">${tg.present ? 'Hiện diện trong lá số' : 'Tham khảo thư viện'}</span>
                    </div>
                    <p style="margin:0 0 6px;color:#292524;">${esc(tg.desc)}</p>
                    ${tg.locations?.length ? `<div style="font-size:12px;color:#1E4D3B;">Vị trí: ${esc(tg.locations.join(' · '))}</div>` : ''}
                </div>
            `;
        };

        const firstPresent = list.find((t) => t.present) || list[0];
        showDetail(firstPresent);

        svgEl.querySelectorAll('.mayhen_bazi_tg_node').forEach((n) => {
            n.addEventListener('click', () => {
                const idx = Number(n.getAttribute('data-idx'));
                showDetail(list[idx]);
            });
        });
    }

    // =========================================================================
    // 4. RENDER ANALYSIS SECTIONS, LUCK PILLARS, EVIDENCE & 10-LAYER REPORT
    // =========================================================================
    function renderFullWorkspace(chart, report, animate) {
        window.__MAYHEN_ACTIVE_BAZI__ = { chart, report };

        const pillars3dEl = document.getElementById('mayhenBaziPillars3D');
        const table2dEl = document.getElementById('mayhenBaziTable2D');
        renderPillars3D(pillars3dEl, chart, animate);
        renderTable2D(table2dEl, chart);

        // Meta header on stage
        const stageMeta = document.getElementById('mayhenBaziStageMeta');
        if (stageMeta) {
            stageMeta.textContent = `${chart.birth.solar_label} · ${chart.birth.has_hour ? chart.birth.civil_time.slice(0, 5) : 'Chưa có giờ sinh'} · ${chart.method.id}`;
        }

        // Warnings
        const warnEl = document.getElementById('mayhenBaziWarnings');
        if (warnEl) {
            if (chart.warnings?.length) {
                warnEl.style.display = 'block';
                warnEl.innerHTML = '<strong>Lưu ý minh bạch về dữ liệu thời gian:</strong><ul style="margin:6px 0 0 18px;padding:0;">' +
                    chart.warnings.map((w) => `<li>${esc(w)}</li>`).join('') + '</ul>';
            } else {
                warnEl.style.display = 'none';
            }
        }

        // Five elements & Ten gods
        const activeModeBtn = document.querySelector('.mayhen_bazi_el_mode_btn.is-active');
        const elMode = activeModeBtn?.getAttribute('data-el-mode') || 'eight_chars';
        renderFiveElements(document.getElementById('mayhenBaziElementsSvg'), document.getElementById('mayhenBaziElementsBars'), chart, elMode, null);
        renderTenGodsOrbit(document.getElementById('mayhenBaziTenGodsSvg'), document.getElementById('mayhenBaziTenGodsDetail'), chart);

        // Solar Time & Boundaries box
        const timeBox = document.getElementById('mayhenBaziTimeDetail');
        if (timeBox) {
            const st = chart.birth.solar_time;
            timeBox.innerHTML = `
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;font-size:13px;">
                    <div><span style="color:#6E6259;">Lịch Âm Việt Nam:</span><br><strong>${esc(chart.birth.lunar?.label_full || '')}</strong></div>
                    <div><span style="color:#6E6259;">Mốc Lập Xuân năm sinh:</span><br><strong>${esc(chart.birth.lap_xuan_vn)}</strong></div>
                    <div><span style="color:#6E6259;">Giờ Mặt Trời Thực (TST):</span><br><strong>${esc(st.true_solar_label)}</strong> (${st.total_correction_min >= 0 ? '+' : ''}${st.total_correction_min} phút)</div>
                    <div><span style="color:#6E6259;">Hiệu chỉnh Kinh độ / EoT:</span><br><strong>${st.longitude_correction_min}p / ${st.eot_min}p</strong></div>
                </div>
            `;
        }

        // Structure, Useful Gods & Relationships
        const structBox = document.getElementById('mayhenBaziStructureBox');
        if (structBox) {
            const ug = chart.useful_gods;
            const rels = [...(chart.relationships?.stems || []), ...(chart.relationships?.branches || [])];
            structBox.innerHTML = `
                <div style="margin-bottom:12px;">
                    <span class="mayhen_bazi_badge">${esc(chart.structure.name)}</span>
                    <span class="mayhen_bazi_badge mayhen_bazi_badge--gold" style="margin-left:6px;">${esc(chart.strength.label)}</span>
                </div>
                <p style="font-size:13.5px;line-height:1.6;margin:0 0 12px;">${esc(chart.structure.note)} <em>(${esc(chart.structure.source)})</em></p>
                <div class="mayhen_bazi_three_col" style="margin-bottom:12px;">
                    <div style="padding:12px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.16);border-radius:10px;font-size:12.8px;">
                        <strong style="color:#1E4D3B;">1. Phù Ức</strong>
                        <p style="margin:6px 0 0;">${esc(ug.phu_uc.note)}</p>
                    </div>
                    <div style="padding:12px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.16);border-radius:10px;font-size:12.8px;">
                        <strong style="color:#85182A;">2. Điều Hậu</strong>
                        <p style="margin:6px 0 0;">${esc(ug.dieu_hau.note)}</p>
                    </div>
                    <div style="padding:12px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.16);border-radius:10px;font-size:12.8px;">
                        <strong style="color:#9A7228;">3. Cách Cục</strong>
                        <p style="margin:6px 0 0;">${esc(ug.cach_cuc.note)}</p>
                    </div>
                </div>
                <div style="font-size:12.5px;color:#4E342E;">
                    <strong>Quan hệ Can Chi phát hiện (${rels.length}):</strong>
                    ${rels.length ? rels.map((r) => `<span class="mayhen_bazi_badge mayhen_bazi_badge--crimson" style="margin:4px 4px 0 0;" title="${esc(r.status_note)}">${esc(r.label)}</span>`).join('') : ' Không có xung hợp trực diện giữa 4 trụ.'}
                </div>
            `;
        }

        // Symbolic Stars & Auxiliary Pillars
        const starsBox = document.getElementById('mayhenBaziStarsBox');
        if (starsBox) {
            const aux = chart.auxiliary_pillars;
            const starsHtml = (chart.symbolic_stars || []).length
                ? chart.symbolic_stars.map((s) => `<div style="padding:8px 10px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.14);border-radius:8px;font-size:12.8px;"><strong>${esc(s.name)}</strong> (${esc(s.pillar)} · ${esc(s.branch)}) — ${esc(s.desc)}</div>`).join('')
                : '<p style="font-size:13px;color:#6E6259;">Chưa ghi nhận Thần Sát tiêu biểu trong nhóm quy tắc cơ bản.</p>';
            starsBox.innerHTML = `
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;font-size:12.5px;">
                    <span class="mayhen_bazi_badge mayhen_bazi_badge--gold">Thai Nguyên: ${esc(aux.thai_nguyen?.label || '')}</span>
                    ${aux.menh_cung ? `<span class="mayhen_bazi_badge">Mệnh Cung Bát Tự: ${esc(aux.menh_cung.label)}</span>` : ''}
                    ${aux.than_cung ? `<span class="mayhen_bazi_badge">Thân Cung Bát Tự: ${esc(aux.than_cung.label)}</span>` : ''}
                </div>
                <div style="display:grid;gap:8px;">${starsHtml}</div>
            `;
        }

        // Luck Pillars Timeline
        const luckBox = document.getElementById('mayhenBaziLuckBox');
        if (luckBox && chart.luck_pillars?.primary) {
            const lp = chart.luck_pillars.primary;
            luckBox.innerHTML = `
                <p style="font-size:13px;color:#4E342E;margin:0 0 12px;">
                    <strong>${esc(lp.direction_label)}</strong> · Khởi vận: <strong>${esc(lp.start_age_label)}</strong> (từ mốc Tiết ${esc(lp.reference_jie)}).
                    ${esc(chart.luck_pillars.note || '')}
                </p>
                <div class="mayhen_bazi_luck_strip">
                    ${lp.cycles.map((c, idx) => `
                        <div class="mayhen_bazi_luck_item ${idx === 0 ? 'is-active' : ''}" data-luck-year="${c.start_year}">
                            <div style="font-size:11px;color:#6E6259;">${c.age_start}–${c.age_end}t</div>
                            <div style="font-family:var(--mayhen-bazi-serif);font-size:21px;font-weight:700;color:${EL_COLORS[c.pillar.stem.element] || '#292524'}">${esc(c.pillar.hanzi)}</div>
                            <div style="font-size:12px;font-weight:600;">${esc(c.pillar.label)}</div>
                            <div style="font-size:11px;color:#1E4D3B;">${esc(c.pillar.ten_god.short)}</div>
                            <div style="font-size:10.5px;color:#6E6259;">${c.start_year}–${c.end_year}</div>
                        </div>
                    `).join('')}
                </div>
            `;

            luckBox.querySelectorAll('.mayhen_bazi_luck_item').forEach((item) => {
                item.addEventListener('click', () => {
                    luckBox.querySelectorAll('.mayhen_bazi_luck_item').forEach((x) => x.classList.remove('is-active'));
                    item.classList.add('is-active');
                    const yInput = document.getElementById('mayhenBaziAnnualYear');
                    if (yInput) {
                        yInput.value = item.getAttribute('data-luck-year');
                        yInput.dispatchEvent(new Event('change'));
                    }
                });
            });
        }

        // Annual Flow
        renderAnnualFlow(document.getElementById('mayhenBaziAnnualBox'), chart.annual_flow);

        // Evidence Graph
        const evBox = document.getElementById('mayhenBaziEvidenceBox');
        if (evBox && chart.evidence_graph) {
            evBox.innerHTML = chart.evidence_graph.map((n) => `
                <div class="mayhen_bazi_evidence_node">
                    <div style="display:flex;justify-content:space-between;font-size:11.5px;color:#6E6259;margin-bottom:4px;">
                        <strong>Bước ${n.step}: ${esc(n.node)}</strong>
                        <span>Rule: ${esc(n.rule_id)} · ${esc(n.source)}</span>
                    </div>
                    <div>${esc(n.conclusion)}</div>
                </div>
            `).join('');
        }

        // 10-Layer Report
        const repBox = document.getElementById('mayhenBaziReportBox');
        if (repBox && report) {
            const blocks = [
                ['Tầng 1 · Tổng Quan Tứ Trụ', report.executive_summary],
                ['Tầng 2 · Nhật Chủ (Day Master)', report.day_master_analysis],
                ['Tầng 3 · Nguyệt Lệnh & Khí Thế', report.seasonal_analysis],
                ['Tầng 4 · Thập Thần Hiện Diện', report.ten_gods_analysis],
                ['Tầng 5 & 6 · Cách Cục & Dụng Thần', report.structure_and_useful_god],
                ['Tầng 7 · Công Việc & Phát Triển', report.career_and_growth],
                ['Tầng 8 · Tình Yêu & Gia Đình', report.love_and_family],
                ['Tầng 9 · Đại Vận & Chu Kỳ Thời Gian', report.timing_analysis],
            ];
            repBox.innerHTML = blocks.map(([title, body]) => `
                <div class="mayhen_bazi_report_block">
                    <h4>${esc(title)}</h4>
                    <p>${esc(body)}</p>
                </div>
            `).join('') + `
                <div class="mayhen_bazi_report_block" style="grid-column:1/-1;">
                    <h4>Tầng 10 · Gợi Ý Chiêm Nghiệm & Hành Động Thực Tế</h4>
                    <ul style="margin:6px 0 0 18px;padding:0;font-size:13.8px;line-height:1.65;">
                        ${(report.practical_reflections || []).map((r) => `<li>${esc(r)}</li>`).join('')}
                    </ul>
                    <p style="margin-top:8px;font-size:12px;color:#6E6259;">${esc(report.limitations || '')}</p>
                </div>
            `;
        }
    }

    function renderAnnualFlow(container, flow) {
        if (!container || !flow) return;
        const ap = flow.annual_pillar;
        container.innerHTML = `
            <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:12px;font-size:13.5px;">
                <span class="mayhen_bazi_badge">Lưu Niên ${flow.year}: ${esc(ap.hanzi)} ${esc(ap.label)} (${esc(ap.ten_god.name)})</span>
                <span style="color:#6E6259;font-size:12.5px;">Lập Xuân ${flow.year}: ${esc(flow.lap_xuan_vn)}</span>
            </div>
            ${flow.interactions?.length ? `<div style="margin-bottom:12px;font-size:13px;color:#85182A;"><strong>Tương tác với lá số gốc:</strong> ${esc(flow.interactions.join(' '))}</div>` : ''}
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(125px,1fr));gap:8px;">
                ${(flow.monthly_pillars || []).map((mp) => `
                    <div style="padding:8px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.14);border-radius:10px;text-align:center;font-size:12px;">
                        <div style="color:#6E6259;font-size:11px;">${esc(mp.jie_name)}</div>
                        <strong style="font-size:14px;">${esc(mp.label)}</strong>
                        <div style="color:#1E4D3B;font-size:11px;">${esc(mp.ten_god.short)}</div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // =========================================================================
    // 5. HIDPI PNG EXPORT (DEFAULT HIDES BIRTH DATE/TIME FOR PRIVACY)
    // =========================================================================
    function exportArtPng(ratio) {
        const active = window.__MAYHEN_ACTIVE_BAZI__;
        if (!active?.chart) return;
        const chart = active.chart;
        const dims = ratio === '9:16' ? [1080, 1920] : (ratio === '4:5' ? [1080, 1350] : [1080, 1080]);
        const canvas = document.createElement('canvas');
        canvas.width = dims[0];
        canvas.height = dims[1];
        const ctx = canvas.getContext('2d');

        // Background Ivory Paper
        ctx.fillStyle = '#FAF5EB';
        ctx.fillRect(0, 0, dims[0], dims[1]);

        // Jade Gold Frame
        ctx.strokeStyle = '#CFA860';
        ctx.lineWidth = 4;
        ctx.strokeRect(36, 36, dims[0] - 72, dims[1] - 72);
        ctx.strokeStyle = 'rgba(30,77,59,0.35)';
        ctx.lineWidth = 1.5;
        ctx.strokeRect(48, 48, dims[0] - 96, dims[1] - 96);

        // Header
        ctx.fillStyle = '#1E4D3B';
        ctx.font = '600 22px "Be Vietnam Pro", sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('MÂY HẸN – TỨ TRỤ NHÂN SINH', dims[0] / 2, 118);

        ctx.fillStyle = '#292524';
        ctx.font = '700 44px "Be Vietnam Pro", serif';
        ctx.fillText(`Nhật Chủ ${chart.day_master.name} ${chart.day_master.element} (${chart.day_master.hanzi})`, dims[0] / 2, 182);

        ctx.fillStyle = '#6E6259';
        ctx.font = '500 22px "Be Vietnam Pro", sans-serif';
        ctx.fillText(`${chart.structure.name} · ${chart.strength.label}`, dims[0] / 2, 224);

        // 4 Pillars Cards
        const keys = ['year', 'month', 'day', 'hour'];
        const titles = ['TRỤ NĂM', 'TRỤ THÁNG', 'TRỤ NGÀY', 'TRỤ GIỜ'];
        const cardW = 205;
        const gap = 22;
        const startX = (dims[0] - (cardW * 4 + gap * 3)) / 2;
        const cardY = dims[1] * 0.28;
        const cardH = 390;

        keys.forEach((k, i) => {
            const p = chart.pillars[k];
            const x = startX + i * (cardW + gap);
            ctx.fillStyle = k === 'day' ? '#1E4D3B' : '#FFFDF8';
            ctx.fillRect(x, cardY, cardW, cardH);
            ctx.strokeStyle = k === 'day' ? '#CFA860' : 'rgba(78,52,46,0.22)';
            ctx.lineWidth = 2;
            ctx.strokeRect(x, cardY, cardW, cardH);

            ctx.fillStyle = k === 'day' ? '#F4DCAA' : '#6E6259';
            ctx.font = '600 18px "Be Vietnam Pro", sans-serif';
            ctx.fillText(titles[i], x + cardW / 2, cardY + 38);

            if (p) {
                ctx.fillStyle = k === 'day' ? '#FAF5EB' : (EL_COLORS[p.stem.element] || '#292524');
                ctx.font = '700 66px serif';
                ctx.fillText(p.stem.hanzi, x + cardW / 2, cardY + 135);
                ctx.font = '700 22px "Be Vietnam Pro", sans-serif';
                ctx.fillText(p.stem.name, x + cardW / 2, cardY + 172);

                ctx.fillStyle = k === 'day' ? '#FAF5EB' : (EL_COLORS[p.branch.element] || '#292524');
                ctx.font = '700 66px serif';
                ctx.fillText(p.branch.hanzi, x + cardW / 2, cardY + 265);
                ctx.font = '700 22px "Be Vietnam Pro", sans-serif';
                ctx.fillText(`${p.branch.name} (${p.branch.zodiac})`, x + cardW / 2, cardY + 302);

                ctx.fillStyle = k === 'day' ? '#F4DCAA' : '#6E6259';
                ctx.font = '500 17px "Be Vietnam Pro", sans-serif';
                ctx.fillText(p.ten_god.name, x + cardW / 2, cardY + 350);
            } else {
                ctx.fillStyle = '#6E6259';
                ctx.font = '500 20px "Be Vietnam Pro", sans-serif';
                ctx.fillText('Chưa có giờ', x + cardW / 2, cardY + 205);
            }
        });

        // Footer (No birth date/time shown for privacy)
        ctx.fillStyle = '#6E6259';
        ctx.font = '500 18px "Be Vietnam Pro", sans-serif';
        ctx.fillText('Bản nghệ thuật ẩn ngày giờ sinh · mayhen.vn/bat-tu', dims[0] / 2, dims[1] - 78);

        const link = document.createElement('a');
        link.download = `mayhen-bat-tu-${ratio.replace(':', 'x')}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    // =========================================================================
    // 6. INITIALIZATION ON MAIN PAGE, LOVE PAGE, REVERSE PAGE & SHOW PAGE
    // =========================================================================
    document.addEventListener('DOMContentLoaded', () => {
        // Hydrate initial sample chart if present
        const initScript = document.getElementById('mayhenBaziInitialData');
        if (initScript) {
            try {
                const parsed = JSON.parse(initScript.textContent || '{}');
                if (parsed.chart) {
                    renderFullWorkspace(parsed.chart, parsed.report || null, false);
                }
            } catch (_) {}
        }

        // Toggle hour inputs when time_accuracy === unknown
        const accSelect = document.getElementById('mayhenBaziTimeAccuracy');
        const hourGroup = document.getElementById('mayhenBaziHourInputs');
        if (accSelect && hourGroup) {
            const syncHourVis = () => {
                hourGroup.style.opacity = accSelect.value === 'unknown' ? '0.45' : '1';
                hourGroup.querySelectorAll('input').forEach((inp) => {
                    inp.disabled = accSelect.value === 'unknown';
                });
            };
            accSelect.addEventListener('change', syncHourVis);
            syncHourVis();
        }

        // Toggle leap month checkbox when calendar_type === lunar
        const calType = document.getElementById('mayhenBaziCalendarType');
        const leapWrap = document.getElementById('mayhenBaziLeapWrap');
        if (calType && leapWrap) {
            const syncLeap = () => {
                leapWrap.style.display = calType.value === 'lunar' ? 'inline-flex' : 'none';
            };
            calType.addEventListener('change', syncLeap);
            syncLeap();
        }

        // 3D Tilt slider & View mode toggle (3D vs 2D)
        const tiltSlider = document.getElementById('mayhenBaziTiltRange');
        const pillars3d = document.getElementById('mayhenBaziPillars3D');
        if (tiltSlider && pillars3d) {
            tiltSlider.addEventListener('input', () => {
                const deg = Number(tiltSlider.value);
                pillars3d.style.transform = `rotateY(${deg}deg) rotateX(4deg)`;
            });
        }

        document.querySelectorAll('[data-bazi-view]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const view = btn.getAttribute('data-bazi-view');
                document.querySelectorAll('[data-bazi-view]').forEach((b) => b.classList.toggle('is-active', b === btn));
                const v3d = document.getElementById('mayhenBaziViewport3D');
                const v2d = document.getElementById('mayhenBaziTable2D');
                if (v3d && v2d) {
                    v3d.style.display = view === '3d' ? 'block' : 'none';
                    v2d.style.display = view === '2d' ? 'block' : 'none';
                }
            });
        });

        // Five elements mode switch
        document.querySelectorAll('.mayhen_bazi_el_mode_btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.mayhen_bazi_el_mode_btn').forEach((b) => b.classList.toggle('is-active', b === btn));
                const active = window.__MAYHEN_ACTIVE_BAZI__;
                if (active?.chart) {
                    renderFiveElements(
                        document.getElementById('mayhenBaziElementsSvg'),
                        document.getElementById('mayhenBaziElementsBars'),
                        active.chart,
                        btn.getAttribute('data-el-mode'),
                        null
                    );
                }
            });
        });

        // Export PNG buttons
        document.querySelectorAll('[data-bazi-export]').forEach((btn) => {
            btn.addEventListener('click', () => exportArtPng(btn.getAttribute('data-bazi-export')));
        });

        // Full-Screen Loading Overlay Controller
        const loadingOverlay = document.getElementById('mayhenBaziLoadingOverlay');
        const loadingTitle = document.getElementById('mayhenBaziLoadingTitle');
        const loadingSub = document.getElementById('mayhenBaziLoadingSub');
        const loadingSteps = Array.from(document.querySelectorAll('#mayhenBaziLoadingSteps li'));
        let loadingTimer = null;

        function showBaziLoading(title, subtitle, useAi) {
            if (!loadingOverlay) return;
            if (loadingTitle) loadingTitle.textContent = title || 'Đang Khởi Lập Lá Số Tứ Trụ...';
            if (loadingSub) {
                loadingSub.textContent = subtitle || (useAi
                    ? 'Đang đối chiếu Tiết Khí và kết nối AI biên tập luận giải 10 tầng (khoảng 10–25 giây)...'
                    : 'Đang đối chiếu Tiết Khí thiên văn và quy tắc Tử Bình...');
            }
            let stepIdx = 0;
            const paintSteps = () => {
                loadingSteps.forEach((li, i) => {
                    li.classList.toggle('is-done', i < stepIdx);
                    li.classList.toggle('is-active', i === stepIdx);
                });
            };
            paintSteps();
            loadingOverlay.classList.add('is-visible');
            loadingOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            if (loadingTimer) clearInterval(loadingTimer);
            const intervalMs = useAi ? 2600 : 420;
            loadingTimer = setInterval(() => {
                if (stepIdx < loadingSteps.length - 1) {
                    stepIdx++;
                    paintSteps();
                }
            }, intervalMs);
        }

        function hideBaziLoading() {
            if (loadingTimer) {
                clearInterval(loadingTimer);
                loadingTimer = null;
            }
            if (!loadingOverlay) return;
            loadingSteps.forEach((li) => {
                li.classList.remove('is-active');
                li.classList.add('is-done');
            });
            setTimeout(() => {
                loadingOverlay.classList.remove('is-visible');
                loadingOverlay.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }, 220);
        }

        // Main Calculate Form
        const mainForm = document.getElementById('mayhenBaziForm');
        if (mainForm) {
            mainForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const errEl = document.getElementById('mayhenBaziFormError');
                const submitBtn = mainForm.querySelector('button[type="submit"]');
                if (errEl) errEl.innerHTML = '';
                if (submitBtn) submitBtn.disabled = true;

                const fd = new FormData(mainForm);
                const wantsAi = fd.get('use_ai') === '1';
                const payload = {
                    name: fd.get('name') || '',
                    calendar_type: fd.get('calendar_type') || 'solar',
                    day: Number(fd.get('day')),
                    month: Number(fd.get('month')),
                    year: Number(fd.get('year')),
                    is_leap_month: fd.get('is_leap_month') === '1',
                    time_accuracy: fd.get('time_accuracy') || 'exact',
                    hour: fd.get('time_accuracy') === 'unknown' ? null : Number(fd.get('hour')),
                    minute: fd.get('time_accuracy') === 'unknown' ? null : Number(fd.get('minute')),
                    gender: fd.get('gender') || 'unspecified',
                    location_key: fd.get('location_key') || 'ha-noi',
                    method_id: fd.get('method_id') || 'tu_binh_default_v1',
                    save_profile: fd.get('save_profile') === '1',
                    use_ai: wantsAi,
                };

                showBaziLoading(
                    wantsAi ? 'Đang Lập Lá Số & Luận Giải AI 10 Tầng...' : 'Đang Khởi Lập Lá Số Tứ Trụ...',
                    null,
                    wantsAi
                );

                try {
                    const [res] = await Promise.all([
                        postJson(mainForm.getAttribute('data-endpoint'), payload),
                        new Promise((r) => setTimeout(r, wantsAi ? 900 : 750)),
                    ]);
                    renderFullWorkspace(res.chart, res.report, true);
                    if (res.quota && res.quota.is_logged_in) {
                        const qText = document.getElementById('mayhenBaziQuotaText');
                        if (qText) {
                            qText.innerHTML = `Còn <strong>${res.quota.remaining_today}/${res.quota.daily_limit}</strong> lượt lưu &amp; AI hôm nay · ${esc(res.quota.cooldown_label)} · Hỏi sâu tối đa ${res.quota.max_followups} câu/hồ sơ`;
                        }
                    }
                    if (res.saved_url) {
                        if (loadingTitle) loadingTitle.textContent = 'Hoàn Tất! Đang Mở Trang Bài Luận Chi Tiết...';
                        if (loadingSub) loadingSub.textContent = 'Đang chuyển bạn tới hồ sơ Bát Tự 10 tầng vừa lập...';
                        loadingSteps.forEach((li) => {
                            li.classList.remove('is-active');
                            li.classList.add('is-done');
                        });
                        setTimeout(() => {
                            window.location.href = res.saved_url;
                        }, 320);
                        return;
                    }
                    hideBaziLoading();
                    setTimeout(() => {
                        document.getElementById('mayhenBaziStageCard')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 260);
                } catch (err) {
                    hideBaziLoading();
                    if (errEl) {
                        errEl.innerHTML = `${esc(err.message)} <a href="/bang-gia" style="color:#1E4D3B;font-weight:600;margin-left:4px;">Xem Bảng Giá &rarr;</a>`;
                    }
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }

        // Compare Methods button
        const compareBtn = document.getElementById('mayhenBaziCompareBtn');
        if (compareBtn && mainForm) {
            compareBtn.addEventListener('click', async () => {
                const out = document.getElementById('mayhenBaziCompareResult');
                if (!out) return;
                out.innerHTML = '<p style="font-size:13px;color:#6E6259;">Đang đối chiếu 3 trường phái...</p>';
                const fd = new FormData(mainForm);
                try {
                    const res = await postJson(compareBtn.getAttribute('data-endpoint'), {
                        calendar_type: fd.get('calendar_type') || 'solar',
                        day: Number(fd.get('day')),
                        month: Number(fd.get('month')),
                        year: Number(fd.get('year')),
                        is_leap_month: fd.get('is_leap_month') === '1',
                        time_accuracy: fd.get('time_accuracy') || 'exact',
                        hour: fd.get('time_accuracy') === 'unknown' ? null : Number(fd.get('hour')),
                        minute: fd.get('time_accuracy') === 'unknown' ? null : Number(fd.get('minute')),
                        gender: fd.get('gender') || 'unspecified',
                        location_key: fd.get('location_key') || 'ha-noi',
                    });
                    out.innerHTML = `
                        <div class="mayhen_bazi_table_wrap">
                            <table class="mayhen_bazi_table">
                                <thead><tr><th>Trường phái</th><th>Giờ lập trụ</th><th>Năm · Tháng · Ngày · Giờ</th><th>Cách Cục</th></tr></thead>
                                <tbody>
                                    ${res.comparisons.map((c) => `
                                        <tr>
                                            <td><strong>${esc(c.method_name)}</strong></td>
                                            <td>${esc(c.effective_time)}</td>
                                            <td>${esc(c.year)} · ${esc(c.month)} · <strong>${esc(c.day)}</strong> · ${esc(c.hour)}</td>
                                            <td>${esc(c.structure)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                } catch (err) {
                    out.innerHTML = `<p style="color:#85182A;font-size:13px;">${esc(err.message)}</p>`;
                }
            });
        }

        // Time Travel Annual Year selector
        const annualInput = document.getElementById('mayhenBaziAnnualYear');
        if (annualInput) {
            annualInput.addEventListener('change', async () => {
                const active = window.__MAYHEN_ACTIVE_BAZI__;
                if (!active?.chart) return;
                const p = active.chart.pillars;
                try {
                    const res = await postJson(annualInput.getAttribute('data-endpoint'), {
                        target_year: Number(annualInput.value),
                        day_stem_index: p.day.stem_index,
                        year_branch_index: p.year.branch_index,
                        month_branch_index: p.month.branch_index,
                        day_branch_index: p.day.branch_index,
                        hour_branch_index: p.hour ? p.hour.branch_index : null,
                    });
                    renderAnnualFlow(document.getElementById('mayhenBaziAnnualBox'), res.annual_flow);
                } catch (_) {}
            });
        }

        // Love BaZi Form (/bat-tu/tinh-duyen)
        const loveForm = document.getElementById('mayhenBaziLoveForm');
        if (loveForm) {
            loveForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const out = document.getElementById('mayhenBaziLoveResult');
                const fd = new FormData(loveForm);
                const buildP = (prefix) => ({
                    name: fd.get(`${prefix}_name`) || (prefix === 'a' ? 'Người thứ nhất' : 'Người thứ hai'),
                    calendar_type: fd.get(`${prefix}_calendar_type`) || 'solar',
                    day: Number(fd.get(`${prefix}_day`)),
                    month: Number(fd.get(`${prefix}_month`)),
                    year: Number(fd.get(`${prefix}_year`)),
                    time_accuracy: fd.get(`${prefix}_hour`) === '' ? 'unknown' : 'exact',
                    hour: fd.get(`${prefix}_hour`) === '' ? null : Number(fd.get(`${prefix}_hour`)),
                    minute: 0,
                    gender: fd.get(`${prefix}_gender`) || 'unspecified',
                    location_key: 'ha-noi',
                    method_id: 'tu_binh_default_v1',
                });

                try {
                    const res = await postJson(loveForm.getAttribute('data-endpoint'), {
                        person_a: buildP('a'),
                        person_b: buildP('b'),
                    });
                    const an = res.analysis;
                    out.innerHTML = `
                        <div class="mayhen_bazi_card" style="margin-top:22px;">
                            <h2 class="mayhen_bazi_card_title">Tứ Trụ Kết Duyên: ${esc(an.person_a.day_master.name)} ${esc(an.person_a.day_master.element)} ↔ ${esc(an.person_b.day_master.name)} ${esc(an.person_b.day_master.element)}</h2>
                            <p class="mayhen_bazi_card_sub">${esc(an.disclaimer)}</p>
                            <div class="mayhen_bazi_two_col" style="margin-bottom:16px;">
                                <div style="padding:14px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.16);border-radius:12px;">
                                    <strong>${esc(an.person_a.birth.name || 'Người A')}</strong>: ${esc(an.person_a.pillars.year.label)} · ${esc(an.person_a.pillars.month.label)} · <strong>${esc(an.person_a.pillars.day.label)}</strong> · ${esc(an.person_a.pillars.hour?.label || 'Chưa có giờ')}
                                    <div style="font-size:12.5px;color:#1E4D3B;margin-top:6px;">Nhìn người B qua lăng kính: <strong>${esc(an.directed_ten_gods.a_sees_b.name)}</strong></div>
                                </div>
                                <div style="padding:14px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.16);border-radius:12px;">
                                    <strong>${esc(an.person_b.birth.name || 'Người B')}</strong>: ${esc(an.person_b.pillars.year.label)} · ${esc(an.person_b.pillars.month.label)} · <strong>${esc(an.person_b.pillars.day.label)}</strong> · ${esc(an.person_b.pillars.hour?.label || 'Chưa có giờ')}
                                    <div style="font-size:12.5px;color:#85182A;margin-top:6px;">Nhìn người A qua lăng kính: <strong>${esc(an.directed_ten_gods.b_sees_a.name)}</strong></div>
                                </div>
                            </div>
                            <div style="margin-bottom:16px;">
                                <strong>Các tương tác nổi bật được phát hiện:</strong>
                                <ul style="margin:6px 0 0 18px;font-size:13.5px;line-height:1.6;">
                                    ${an.detected_relations.map((r) => `<li><strong>${esc(r.label)}:</strong> ${esc(r.nature)}</li>`).join('')}
                                    ${an.complementary_elements.map((c) => `<li>${esc(c)}</li>`).join('')}
                                </ul>
                            </div>
                            <h3 class="mayhen_bazi_card_title" style="font-size:20px;">12 Chủ Đề Đồng Hành & Hôn Nhân</h3>
                            <div class="mayhen_bazi_three_col" style="margin:12px 0 18px;">
                                ${an.themes.map((t) => `<div style="padding:12px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.14);border-radius:10px;font-size:13px;"><strong style="color:#1E4D3B;">${t.id}. ${esc(t.title)}</strong><p style="margin:5px 0 0;line-height:1.55;">${esc(t.insight)}</p></div>`).join('')}
                            </div>
                            <div style="padding:14px 16px;background:rgba(30,77,59,0.07);border:1px solid rgba(30,77,59,0.2);border-radius:12px;">
                                <strong style="color:#1E4D3B;">Gợi ý 4 câu hỏi hai bạn nên cùng trò chuyện:</strong>
                                <ul style="margin:6px 0 0 18px;font-size:13.5px;line-height:1.6;">
                                    ${an.dialogue_questions.map((q) => `<li>${esc(q)}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    `;
                } catch (err) {
                    out.innerHTML = `<div class="mayhen_bazi_warning_box">${esc(err.message)}</div>`;
                }
            });
        }

        // Reverse Lookup Form (/bat-tu/tra-cuu-nguoc)
        const revForm = document.getElementById('mayhenBaziReverseForm');
        if (revForm) {
            revForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const out = document.getElementById('mayhenBaziReverseResult');
                const fd = new FormData(revForm);
                try {
                    const res = await postJson(revForm.getAttribute('data-endpoint'), {
                        year_stem: Number(fd.get('year_stem')),
                        year_branch: Number(fd.get('year_branch')),
                        month_stem: Number(fd.get('month_stem')),
                        month_branch: Number(fd.get('month_branch')),
                        day_stem: Number(fd.get('day_stem')),
                        day_branch: Number(fd.get('day_branch')),
                        hour_stem: Number(fd.get('hour_stem')),
                        hour_branch: Number(fd.get('hour_branch')),
                        from_year: Number(fd.get('from_year')),
                        to_year: Number(fd.get('to_year')),
                    });
                    if (!res.matches.length) {
                        out.innerHTML = '<div class="mayhen_bazi_warning_box">Không tìm thấy ngày giờ sinh hợp lệ khớp 4 trụ này trong khoảng năm đã chọn (kiểm tra quy tắc Ngũ Hổ Độn & Ngũ Thử Độn).</div>';
                        return;
                    }
                    out.innerHTML = `
                        <div class="mayhen_bazi_table_wrap" style="margin-top:16px;">
                            <table class="mayhen_bazi_table">
                                <thead><tr><th>Ngày Dương lịch</th><th>Khung giờ sinh</th><th>Tứ Trụ kiểm chứng</th></tr></thead>
                                <tbody>
                                    ${res.matches.map((m) => `<tr><td><strong>${esc(m.solar_label)}</strong></td><td>${esc(m.hour_range)}</td><td>${esc(m.year_pillar)} · ${esc(m.month_pillar)} · ${esc(m.day_pillar)} · ${esc(m.hour_pillar)}</td></tr>`).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                } catch (err) {
                    out.innerHTML = `<div class="mayhen_bazi_warning_box">${esc(err.message)}</div>`;
                }
            });
        }

        // Toggle Anonymous Share Link on /bat-tu/ket-qua/{slug}
        const shareBtn = document.getElementById('mayhenBaziToggleShareBtn');
        if (shareBtn) {
            shareBtn.addEventListener('click', async () => {
                shareBtn.disabled = true;
                try {
                    const res = await postJson(shareBtn.getAttribute('data-endpoint'), {});
                    const box = document.getElementById('mayhenBaziShareBox');
                    if (res.is_shared && res.share_url) {
                        shareBtn.textContent = 'Tắt Link Chia Sẻ Ẩn Danh';
                        if (box) {
                            box.style.display = 'block';
                            box.innerHTML = `Link chia sẻ ẩn danh (đã ẩn ngày giờ sinh): <a href="${esc(res.share_url)}" target="_blank" style="font-weight:600;color:#1E4D3B;">${esc(res.share_url)}</a>`;
                        }
                    } else {
                        shareBtn.textContent = 'Tạo Link Chia Sẻ Ẩn Danh';
                        if (box) {
                            box.style.display = 'none';
                            box.innerHTML = '';
                        }
                    }
                } catch (_) {} finally {
                    shareBtn.disabled = false;
                }
            });
        }

        // Followup AI Form on /bat-tu/ket-qua/{slug}
        const fuForm = document.getElementById('mayhenBaziFollowupForm');
        if (fuForm) {
            fuForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const inp = fuForm.querySelector('input[name="question"]');
                const list = document.getElementById('mayhenBaziFollowupList');
                const errEl = document.getElementById('mayhenBaziFollowupError');
                if (errEl) errEl.textContent = '';
                try {
                    const res = await postJson(fuForm.getAttribute('data-endpoint'), { question: inp.value });
                    if (list) {
                        const div = document.createElement('div');
                        div.style.cssText = 'padding:12px 14px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.16);border-radius:10px;margin-bottom:10px;font-size:13.5px;';
                        div.innerHTML = `<div style="font-weight:600;color:#1E4D3B;margin-bottom:4px;">Hỏi: ${esc(res.followup.question)}</div><div>${esc(res.followup.answer)}</div>`;
                        list.prepend(div);
                    }
                    inp.value = '';
                } catch (err) {
                    if (errEl) errEl.textContent = err.message;
                }
            });
        }
    });
})();
