/* ==========================================================================
   MÂY HẸN – DUYÊN HỢP (/cao-ly-dau-hinh & /xem-tuoi-vo-chong) — Pure Vanilla JS
   1. Vòng Thiên Văn Đồng Ngọc 10 Can × 12 Chi SVG
   2. Hai Vòng Kết Duyên SVG
   3. Sáu Huy Chương Lục Cảnh
   4. Ma Trận 120 Tổ Hợp Tương Tác
   5. Vòng Bát Quái Bát Trạch Du Niên 8 Quẻ SVG
   6. Form Phân Tích, Loading Overlay, Chuyển Trang Sổ Nhân Duyên & Xuất PNG
   ========================================================================== */
(function () {
    'use strict';

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
            throw new Error(data.message || 'Không thể xử lý yêu cầu.');
        }
        return data;
    }

    function polarToCartesian(cx, cy, r, deg) {
        const rad = (deg - 90) * Math.PI / 180;
        return { x: cx + r * Math.cos(rad), y: cy + r * Math.sin(rad) };
    }

    function ringSectorPath(cx, cy, rInner, rOuter, startDeg, endDeg) {
        const p1 = polarToCartesian(cx, cy, rOuter, startDeg);
        const p2 = polarToCartesian(cx, cy, rOuter, endDeg);
        const p3 = polarToCartesian(cx, cy, rInner, endDeg);
        const p4 = polarToCartesian(cx, cy, rInner, startDeg);
        const large = endDeg - startDeg > 180 ? 1 : 0;
        return `M ${p1.x} ${p1.y} A ${rOuter} ${rOuter} 0 ${large} 1 ${p2.x} ${p2.y} L ${p3.x} ${p3.y} A ${rInner} ${rInner} 0 ${large} 0 ${p4.x} ${p4.y} Z`;
    }

    // =========================================================================
    // 1. VÒNG THIÊN VĂN ĐỒNG & NGỌC 10 THIÊN CAN × 12 ĐỊA CHI (ẢNH 1)
    // =========================================================================
    function initCaoLyWheel() {
        const svgWrap = document.getElementById('mayhenCaoLyWheelSvgWrap');
        const dataEl = document.getElementById('mayhenCaoLyDataset');
        if (!svgWrap || !dataEl) return;

        const dataset = JSON.parse(dataEl.textContent || '{}');
        const stems = dataset.stems || [];
        const branches = dataset.branches || [];
        const records = dataset.records || [];
        const recordByKey = {};
        records.forEach((r) => {
            recordByKey[`${r.husband_stem_index}_${r.wife_branch_index}`] = r;
        });

        let activeStem = 6;   // Canh
        let activeBranch = 7; // Mùi

        const stemSelect = document.getElementById('mayhenCaoLyStemSelect');
        const branchSelect = document.getElementById('mayhenCaoLyBranchSelect');
        const resultBox = document.getElementById('mayhenCaoLyResultBox');
        const matrixBox = document.getElementById('mayhenCaoLyMatrixDetail');

        function paintAll() {
            const cx = 220, cy = 220;
            // Vòng ngoài: 12 Địa Chi (30° mỗi cung)
            let branchPaths = '';
            branches.forEach((b, i) => {
                const start = i * 30 - 15;
                const end = start + 30;
                const isSel = i === activeBranch;
                const d = ringSectorPath(cx, cy, 134, 196, start, end);
                const mid = polarToCartesian(cx, cy, 165, i * 30);
                branchPaths += `
                    <g class="mayhen_cao_ly_wheel_sector" data-branch="${i}">
                        <path d="${d}" fill="${isSel ? '#85182A' : '#FAF5EB'}" stroke="#C5A975" stroke-width="1.2" />
                        <text x="${mid.x}" y="${mid.y - 4}" text-anchor="middle" fill="${isSel ? '#FAF5EB' : '#85182A'}" font-size="20" font-weight="700" font-family="serif">${esc(b.hanzi)}</text>
                        <text x="${mid.x}" y="${mid.y + 14}" text-anchor="middle" fill="${isSel ? '#F4DCAA' : '#6E6259'}" font-size="11" font-weight="600">${esc(b.name)}</text>
                    </g>
                `;
            });

            // Vòng trong: 10 Thiên Can (36° mỗi cung)
            let stemPaths = '';
            stems.forEach((s, i) => {
                const start = i * 36 - 18;
                const end = start + 36;
                const isSel = i === activeStem;
                const d = ringSectorPath(cx, cy, 70, 126, start, end);
                const mid = polarToCartesian(cx, cy, 98, i * 36);
                stemPaths += `
                    <g class="mayhen_cao_ly_wheel_sector" data-stem="${i}">
                        <path d="${d}" fill="${isSel ? '#1E4D3B' : '#FFFDF8'}" stroke="#C5A975" stroke-width="1.2" />
                        <text x="${mid.x}" y="${mid.y + 7}" text-anchor="middle" fill="${isSel ? '#FAF5EB' : '#1E4D3B'}" font-size="20" font-weight="700" font-family="serif">${esc(s.hanzi)}</text>
                    </g>
                `;
            });

            svgWrap.innerHTML = `
                <svg class="mayhen_cao_ly_wheel_svg" viewBox="0 0 440 440" role="img" aria-label="Vòng Thiên Văn Đồng Ngọc 10 Thiên Can và 12 Địa Chi">
                    <circle cx="${cx}" cy="${cy}" r="212" fill="#E8DABF" stroke="#B79A63" stroke-width="2" />
                    <circle cx="${cx}" cy="${cy}" r="202" fill="#F4ECDD" stroke="#C5A975" stroke-width="1.2" />
                    ${branchPaths}
                    <circle cx="${cx}" cy="${cy}" r="131" fill="none" stroke="#B79A63" stroke-width="3" />
                    ${stemPaths}
                    <circle cx="${cx}" cy="${cy}" r="64" fill="#FFFDF8" stroke="#CFA860" stroke-width="3" />
                    <text x="${cx}" y="${cy + 4}" text-anchor="middle" fill="#85182A" font-size="40" font-weight="700" font-family="serif">囍</text>
                    <text x="${cx}" y="${cy + 28}" text-anchor="middle" fill="#6E6259" font-size="10.5" letter-spacing="1.6" font-weight="600">DUYÊN HỢP</text>
                </svg>
            `;

            svgWrap.querySelectorAll('[data-stem]').forEach((el) => {
                el.addEventListener('click', () => {
                    activeStem = Number(el.getAttribute('data-stem'));
                    paintAll();
                });
            });
            svgWrap.querySelectorAll('[data-branch]').forEach((el) => {
                el.addEventListener('click', () => {
                    activeBranch = Number(el.getAttribute('data-branch'));
                    paintAll();
                });
            });

            if (stemSelect) stemSelect.value = String(activeStem);
            if (branchSelect) branchSelect.value = String(activeBranch);

            const rec = recordByKey[`${activeStem}_${activeBranch}`];
            if (rec && resultBox) {
                resultBox.innerHTML = `
                    <div style="font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:#6E6259;">TỔ HỢP ĐANG TRA CỨU · ${esc(rec.id)}</div>
                    <div style="font-family:var(--mayhen-marriage-serif);font-size:30px;font-weight:700;color:#85182A;margin:4px 0;">${esc(rec.combination_name)} (${esc(rec.hanzi)})</div>
                    <div style="margin-bottom:10px;">
                        <span class="mayhen_marriage_badge" style="background:${rec.scene.color}20;color:${rec.scene.color};border-color:${rec.scene.color}50;">${esc(rec.scene.name)} · ${esc(rec.scene.keyword)}</span>
                        <span class="mayhen_marriage_badge mayhen_marriage_badge--crimson" style="margin-left:6px;">${esc(rec.element_relation)}</span>
                    </div>
                    <p style="font-size:13.8px;line-height:1.65;margin:0 0 8px;">${esc(rec.tradition_summary)}</p>
                    <p style="font-size:13.5px;line-height:1.6;color:#1E4D3B;margin:0 0 10px;"><strong>Góc nhìn hiện đại Mây Hẹn:</strong> ${esc(rec.modern_guidance)}</p>
                    <a href="/cao-ly-dau-hinh/${esc(rec.slug)}" class="mayhen_marriage_btn mayhen_marriage_btn--outline mayhen_marriage_btn--sm">Xem trang chi tiết tổ hợp ${esc(rec.combination_name)} &rarr;</a>
                `;
            }

            // Sync 120 Matrix active cell
            document.querySelectorAll('.mayhen_cao_ly_matrix_cell').forEach((btn) => {
                const isCur = Number(btn.getAttribute('data-s')) === activeStem && Number(btn.getAttribute('data-b')) === activeBranch;
                btn.classList.toggle('is-selected', isCur);
                btn.textContent = isCur ? '✓' : '·';
            });

            if (rec && matrixBox) {
                matrixBox.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;">
                        <div>
                            <div style="font-size:12px;color:#6E6259;">Tổ hợp đã chọn · Bản ghi số ${rec.order}/120 (${esc(rec.id)})</div>
                            <div style="font-family:var(--mayhen-marriage-serif);font-size:26px;font-weight:700;color:#292524;">${esc(rec.combination_name)} — <span style="color:${rec.scene.color}">${esc(rec.scene.name)}</span></div>
                            <p style="font-size:13px;color:#4E342E;margin:4px 0 0;">${esc(rec.modern_guidance)} · <em>${esc(rec.source_label)}</em></p>
                        </div>
                        <div style="padding:10px 16px;background:#FAF5EB;border:1px solid rgba(178,146,94,0.35);border-radius:12px;font-family:serif;font-size:28px;font-weight:700;color:#85182A;">${esc(rec.hanzi)}</div>
                    </div>
                `;
            }
        }

        if (stemSelect) {
            stemSelect.addEventListener('change', () => {
                activeStem = Number(stemSelect.value);
                paintAll();
            });
        }
        if (branchSelect) {
            branchSelect.addEventListener('change', () => {
                activeBranch = Number(branchSelect.value);
                paintAll();
            });
        }

        document.querySelectorAll('.mayhen_cao_ly_matrix_cell').forEach((btn) => {
            btn.addEventListener('click', () => {
                activeStem = Number(btn.getAttribute('data-s'));
                activeBranch = Number(btn.getAttribute('data-b'));
                paintAll();
            });
        });

        paintAll();
    }

    // =========================================================================
    // 2. HAI VÒNG KẾT DUYÊN SVG (ẢNH 2)
    // =========================================================================
    function renderTwoRings(wrap, analysis) {
        if (!wrap || !analysis) return;
        const h = analysis.husband;
        const w = analysis.wife;
        wrap.innerHTML = `
            <svg class="mayhen_marriage_rings_svg" viewBox="0 0 460 220" role="img" aria-label="Hai vòng kết duyên vợ chồng">
                <circle cx="152" cy="110" r="94" fill="#1E4D3B" stroke="#CFA860" stroke-width="5" />
                <circle cx="152" cy="110" r="85" fill="none" stroke="rgba(250,245,235,0.28)" stroke-width="1.2" />
                <text x="142" y="64" text-anchor="middle" fill="#F4DCAA" font-size="11.5" letter-spacing="1.4" font-weight="600">NGƯỜI CHỒNG</text>
                <text x="142" y="118" text-anchor="middle" fill="#FAF5EB" font-size="44" font-weight="700" font-family="serif">${esc(h.hanzi)}</text>
                <text x="142" y="152" text-anchor="middle" fill="#F4DCAA" font-size="16" font-weight="600">${esc(h.can_chi)}</text>

                <circle cx="308" cy="110" r="94" fill="#85182A" stroke="#CFA860" stroke-width="5" />
                <circle cx="308" cy="110" r="85" fill="none" stroke="rgba(250,245,235,0.28)" stroke-width="1.2" />
                <text x="318" y="64" text-anchor="middle" fill="#F4DCAA" font-size="11.5" letter-spacing="1.4" font-weight="600">NGƯỜI VỢ</text>
                <text x="318" y="118" text-anchor="middle" fill="#FAF5EB" font-size="44" font-weight="700" font-family="serif">${esc(w.hanzi)}</text>
                <text x="318" y="152" text-anchor="middle" fill="#F4DCAA" font-size="16" font-weight="600">${esc(w.can_chi)}</text>

                <circle cx="230" cy="110" r="28" fill="#FAF5EB" stroke="#CFA860" stroke-width="3" />
                <text x="230" y="121" text-anchor="middle" fill="#85182A" font-size="28" font-weight="700" font-family="serif">囍</text>
            </svg>
            <div style="font-size:12px;letter-spacing:0.18em;color:#9A7228;margin:4px 0 10px;font-weight:600;">TỪ NHÂN DUYÊN ĐẾN TRĂM NĂM</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;width:100%;font-size:13px;margin-bottom:8px;">
                <div><span style="color:#6E6259;">${h.lunar_year} · ${esc(h.zodiac)}</span><br><strong>${esc(h.nap_am.name)}</strong> · Cung ${esc(h.kua.name)}</div>
                <div><span style="color:#6E6259;">${w.lunar_year} · ${esc(w.zodiac)}</span><br><strong>${esc(w.nap_am.name)}</strong> · Cung ${esc(w.kua.name)}</div>
            </div>
            <div class="mayhen_marriage_quick_rows">
                <div class="mayhen_marriage_quick_row"><span>Tổ hợp Cao Ly</span><strong style="color:#85182A;">${esc(analysis.cao_ly.combination_name)} (${esc(analysis.cao_ly.scene.name)})</strong></div>
                <div class="mayhen_marriage_quick_row"><span>Thiên Can</span><strong style="color:#1E4D3B;">${esc(analysis.stem_relation.label)}</strong></div>
                <div class="mayhen_marriage_quick_row"><span>Quan hệ Địa Chi</span><strong style="color:#1E4D3B;">${esc(analysis.branch_relation.primary_label)}</strong></div>
                <div class="mayhen_marriage_quick_row"><span>Ngũ Hành Nạp Âm</span><strong style="color:#1E4D3B;">${esc(analysis.nap_am_relation.label)}</strong></div>
                <div class="mayhen_marriage_quick_row"><span>Cung Phi Bát Trạch</span><strong style="color:#85182A;">${esc(analysis.du_nien.name)}</strong></div>
            </div>
        `;
    }

    // =========================================================================
    // 5. VÒNG BÁT QUÁI BÁT TRẠCH DU NIÊN 8 QUẺ 3 HÀO (ẢNH 5)
    // =========================================================================
    function initBaguaWheel(initialA, initialB) {
        const wrap = document.getElementById('mayhenBaguaSvgWrap');
        const metaEl = document.getElementById('mayhenBaguaDataset');
        if (!wrap || !metaEl) return null;

        const data = JSON.parse(metaEl.textContent || '{}');
        const trigrams = data.trigrams || {};
        const matrix = data.matrix || {};
        const duNienMeta = data.du_nien_meta || {};

        // Thứ tự 8 quẻ theo vòng tròn đồng hồ giống ảnh mẫu: Khảm (0°), Cấn (45°), Chấn (90°), Tốn (135°), Ly (180°), Khôn (225°), Đoài (270°), Càn (315°)
        const order = [1, 8, 3, 4, 9, 2, 7, 6];
        let kuaA = initialA || 1;
        let kuaB = initialB || 4;

        const selA = document.getElementById('mayhenBaguaSelectA');
        const selB = document.getElementById('mayhenBaguaSelectB');
        const infoBox = document.getElementById('mayhenBaguaInfo');

        function drawTrigramLines(cx, cy, lines, strokeColor) {
            // lines: [hào dưới, hào giữa, hào trên] -> vẽ từ trên xuống: index 2 (trên), 1 (giữa), 0 (dưới)
            let out = '';
            const ys = [cy - 10, cy, cy + 10];
            [2, 1, 0].forEach((idx, row) => {
                const y = ys[row];
                if (lines[idx] === 1) {
                    out += `<line x1="${cx - 14}" y1="${y}" x2="${cx + 14}" y2="${y}" stroke="${strokeColor}" stroke-width="3.2" stroke-linecap="round" />`;
                } else {
                    out += `<line x1="${cx - 14}" y1="${y}" x2="${cx - 3}" y2="${y}" stroke="${strokeColor}" stroke-width="3.2" stroke-linecap="round" />`;
                    out += `<line x1="${cx + 3}" y1="${y}" x2="${cx + 14}" y2="${y}" stroke="${strokeColor}" stroke-width="3.2" stroke-linecap="round" />`;
                }
            });
            return out;
        }

        function paintBagua() {
            const cx = 210, cy = 210;
            let sectors = '';
            order.forEach((num, idx) => {
                const t = trigrams[num];
                const start = idx * 45 - 22.5;
                const end = start + 45;
                const isA = num === kuaA;
                const isB = num === kuaB;
                const fill = isA ? '#1E4D3B' : (isB ? '#85182A' : '#FAF5EB');
                const strokeCol = (isA || isB) ? '#FAF5EB' : '#6E5B45';
                const textCol = (isA || isB) ? '#F4DCAA' : '#4E342E';
                const d = ringSectorPath(cx, cy, 98, 186, start, end);
                const mid = polarToCartesian(cx, cy, 144, idx * 45);
                sectors += `
                    <g class="mayhen_cao_ly_wheel_sector" data-kua="${num}">
                        <path d="${d}" fill="${fill}" stroke="#C5A975" stroke-width="1.4" />
                        ${drawTrigramLines(mid.x, mid.y - 8, t.lines, strokeCol)}
                        <text x="${mid.x}" y="${mid.y + 20}" text-anchor="middle" fill="${textCol}" font-size="13" font-weight="600">${esc(t.name)}</text>
                    </g>
                `;
            });

            const dnCode = matrix[kuaA]?.[kuaB] || 'phuc_vi';
            const dn = duNienMeta[dnCode] || { name: 'Phục Vị', group: 'Cát', modern_note: '' };
            const tA = trigrams[kuaA];
            const tB = trigrams[kuaB];

            wrap.innerHTML = `
                <svg class="mayhen_cao_ly_wheel_svg" viewBox="0 0 420 420" role="img" aria-label="Vòng Bát Quái Bát Trạch Du Niên">
                    <circle cx="${cx}" cy="${cy}" r="202" fill="#E8DABF" stroke="#B79A63" stroke-width="2" />
                    <circle cx="${cx}" cy="${cy}" r="192" fill="#F4ECDD" stroke="#C5A975" stroke-width="1.2" />
                    ${sectors}
                    <circle cx="${cx}" cy="${cy}" r="90" fill="#FFFDF8" stroke="#CFA860" stroke-width="3" />
                    <text x="${cx}" y="${cy - 22}" text-anchor="middle" fill="#6E6259" font-size="11.5" letter-spacing="1.4">DU NIÊN</text>
                    <text x="${cx}" y="${cy + 10}" text-anchor="middle" fill="#85182A" font-size="25" font-weight="700" font-family="serif">${esc(dn.name.split(' ')[0] + ' ' + (dn.name.split(' ')[1] || ''))}</text>
                    <text x="${cx}" y="${cy + 36}" text-anchor="middle" fill="#6E6259" font-size="13">${esc(tA.name)} · ${esc(tB.name)}</text>
                </svg>
            `;

            if (selA) selA.value = String(kuaA);
            if (selB) selB.value = String(kuaB);
            if (infoBox) {
                infoBox.innerHTML = `
                    <strong style="color:#1E4D3B;font-size:16px;">${esc(dn.name)}</strong>
                    <span class="mayhen_marriage_badge" style="margin-left:6px;">${esc(dn.group)}</span>
                    <p style="margin:6px 0 0;font-size:13.2px;color:#4E342E;line-height:1.55;">${esc(dn.modern_note)}</p>
                `;
            }

            wrap.querySelectorAll('[data-kua]').forEach((g) => {
                g.addEventListener('click', () => {
                    kuaB = Number(g.getAttribute('data-kua'));
                    paintBagua();
                });
            });
        }

        if (selA) selA.addEventListener('change', () => { kuaA = Number(selA.value); paintBagua(); });
        if (selB) selB.addEventListener('change', () => { kuaB = Number(selB.value); paintBagua(); });

        paintBagua();
        return { setPair: (a, b) => { kuaA = a; kuaB = b; paintBagua(); } };
    }

    // =========================================================================
    // 3. RENDER COMPATIBILITY REPORT & INIT
    // =========================================================================
    function renderCompatibilityWorkspace(analysis, aiReport, baguaCtrl) {
        window.__MAYHEN_ACTIVE_MARRIAGE__ = { analysis, aiReport };
        renderTwoRings(document.getElementById('mayhenMarriageRingsWrap'), analysis);
        if (baguaCtrl && analysis?.husband?.kua && analysis?.wife?.kua) {
            baguaCtrl.setPair(analysis.husband.kua.number, analysis.wife.kua.number);
        }

        const warnEl = document.getElementById('mayhenMarriageWarnings');
        if (warnEl) {
            if (analysis.warnings?.length) {
                warnEl.style.display = 'block';
                warnEl.innerHTML = '<strong>Lưu ý minh bạch về năm sinh &amp; Tết Nguyên Đán:</strong><ul style="margin:6px 0 0 18px;padding:0;">' +
                    analysis.warnings.map((w) => `<li>${esc(w)}</li>`).join('') + '</ul>';
            } else {
                warnEl.style.display = 'none';
            }
        }

        const wyBox = document.getElementById('mayhenMarriageWeddingYearBox');
        if (wyBox && analysis.wedding_year) {
            const wy = analysis.wedding_year;
            wyBox.innerHTML = `
                <div style="margin-bottom:8px;">
                    <span class="mayhen_marriage_badge">Năm dự kiến ${wy.year} (${esc(wy.year_can_chi)})</span>
                    <span class="mayhen_marriage_badge ${wy.is_kim_lau ? 'mayhen_marriage_badge--crimson' : ''}" style="margin-left:6px;">${esc(wy.kim_lau_label)}</span>
                </div>
                <p style="font-size:13.5px;color:#4E342E;line-height:1.6;margin:0 0 12px;">${esc(wy.note)}</p>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    <a href="/lich-am" class="mayhen_marriage_btn mayhen_marriage_btn--jade mayhen_marriage_btn--sm">Xem Ngày Cưới Tốt Trong Lịch Vạn Niên &rarr;</a>
                    <a href="/mau-thiep" class="mayhen_marriage_btn mayhen_marriage_btn--outline mayhen_marriage_btn--sm">Khám Phá Mẫu Thiệp Cưới Online &rarr;</a>
                </div>
            `;
        }

        const evBox = document.getElementById('mayhenMarriageEvidenceBox');
        if (evBox && analysis.evidence) {
            evBox.innerHTML = analysis.evidence.map((e) => `
                <div style="padding:12px 14px;background:#FAF5EB;border:1px solid rgba(178,146,94,0.3);border-left:3px solid #1E4D3B;border-radius:10px;font-size:13px;">
                    <div style="display:flex;justify-content:space-between;font-size:11.5px;color:#6E6259;margin-bottom:3px;">
                        <strong>${esc(e.layer)}</strong>
                        <span>${esc(e.rule_id)} · ${esc(e.source)}</span>
                    </div>
                    <div>${esc(e.result)}</div>
                </div>
            `).join('');
        }

        const aiBox = document.getElementById('mayhenMarriageAiBox');
        if (aiBox && aiReport) {
            const blocks = [
                ['1. Tổng Quan Nhân Duyên Đa Phương Pháp', aiReport.summary],
                ['2. Lớp Cao Ly Đầu Hình (Can Chồng – Chi Vợ)', aiReport.caoly_layer],
                ['3. Lớp Thiên Can & Địa Chi', aiReport.canchi_layer],
                ['4. Lớp Ngũ Hành Nạp Âm & Cung Phi Bát Trạch', aiReport.napam_and_kua],
                ['5. Tổng Hợp Điểm Khác Biệt Giữa Các Phương Pháp', aiReport.conflict_synthesis],
                ['6. Gợi Ý Trao Đổi Tôn Trọng Với Gia Đình Hai Bên', aiReport.family_dialogue_guide],
            ];
            aiBox.innerHTML = blocks.map(([t, b]) => `
                <div style="padding:14px 16px;background:#FAF5EB;border:1px solid rgba(178,146,94,0.28);border-radius:12px;">
                    <h4 style="font-family:var(--mayhen-marriage-serif);font-size:19px;margin:0 0 6px;color:#85182A;">${esc(t)}</h4>
                    <p style="margin:0;font-size:13.8px;line-height:1.65;">${esc(b)}</p>
                </div>
            `).join('');
        }

        const themesBox = document.getElementById('mayhenMarriageThemesBox');
        if (themesBox && analysis.themes) {
            themesBox.innerHTML = analysis.themes.map((t) => `
                <div style="padding:12px 14px;background:#FAF5EB;border:1px solid rgba(178,146,94,0.25);border-radius:10px;font-size:13px;">
                    <strong style="color:#1E4D3B;">${t.id}. ${esc(t.title)}</strong>
                    <p style="margin:5px 0 0;line-height:1.55;">${esc(t.insight)}</p>
                </div>
            `).join('');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCaoLyWheel();

        // Lục Cảnh medals click highlight
        document.querySelectorAll('.mayhen_cao_ly_medal_card').forEach((card) => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.mayhen_cao_ly_medal_card').forEach((c) => c.classList.remove('is-active'));
                card.classList.add('is-active');
                const detail = document.getElementById('mayhenLucCanhActiveDetail');
                if (detail) {
                    detail.innerHTML = `
                        <strong style="color:#85182A;font-size:17px;">${esc(card.getAttribute('data-name'))} (${esc(card.getAttribute('data-hanzi'))})</strong>
                        <p style="margin:6px 0;"><strong>Ngữ cảnh cổ truyền:</strong> ${esc(card.getAttribute('data-hist'))}</p>
                        <p style="margin:6px 0;color:#1E4D3B;"><strong>Diễn giải hiện đại Mây Hẹn:</strong> ${esc(card.getAttribute('data-modern'))}</p>
                        <p style="margin:6px 0 0;"><strong>Gợi ý cặp đôi:</strong> ${esc(card.getAttribute('data-tip'))}</p>
                    `;
                }
            });
        });

        // Init compatibility page or show page
        const initCompat = document.getElementById('mayhenMarriageInitialData');
        let baguaCtrl = null;
        if (initCompat) {
            try {
                const parsed = JSON.parse(initCompat.textContent || '{}');
                baguaCtrl = initBaguaWheel(parsed.analysis?.husband?.kua?.number || 1, parsed.analysis?.wife?.kua?.number || 4);
                if (parsed.analysis) {
                    renderCompatibilityWorkspace(parsed.analysis, parsed.aiReport || null, baguaCtrl);
                }
            } catch (_) {}
        } else {
            baguaCtrl = initBaguaWheel(1, 4);
        }

        // Loading overlay
        const overlay = document.getElementById('mayhenMarriageLoadingOverlay');
        const showLoading = () => {
            if (!overlay) return;
            overlay.classList.add('is-visible');
            document.body.style.overflow = 'hidden';
        };
        const hideLoading = () => {
            if (!overlay) return;
            overlay.classList.remove('is-visible');
            document.body.style.overflow = '';
        };

        // Main compatibility form
        const form = document.getElementById('mayhenMarriageForm');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const errEl = document.getElementById('mayhenMarriageFormError');
                if (errEl) errEl.innerHTML = '';
                const fd = new FormData(form);
                const payload = {
                    husband_name: fd.get('husband_name') || '',
                    husband_calendar_type: fd.get('husband_calendar_type') || 'solar',
                    husband_year: Number(fd.get('husband_year')),
                    husband_month: fd.get('husband_month') ? Number(fd.get('husband_month')) : null,
                    husband_day: fd.get('husband_day') ? Number(fd.get('husband_day')) : null,
                    wife_name: fd.get('wife_name') || '',
                    wife_calendar_type: fd.get('wife_calendar_type') || 'solar',
                    wife_year: Number(fd.get('wife_year')),
                    wife_month: fd.get('wife_month') ? Number(fd.get('wife_month')) : null,
                    wife_day: fd.get('wife_day') ? Number(fd.get('wife_day')) : null,
                    wedding_year: fd.get('wedding_year') ? Number(fd.get('wedding_year')) : null,
                    save_report: fd.get('save_report') === '1',
                    use_ai: fd.get('use_ai') === '1',
                };

                showLoading();
                try {
                    const [res] = await Promise.all([
                        postJson(form.getAttribute('data-endpoint'), payload),
                        new Promise((r) => setTimeout(r, 700)),
                    ]);
                    if (res.saved_url) {
                        window.location.href = res.saved_url;
                        return;
                    }
                    renderCompatibilityWorkspace(res.analysis, res.ai_report, baguaCtrl);
                    hideLoading();
                    document.getElementById('mayhenMarriageRingsCard')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (err) {
                    hideLoading();
                    if (errEl) errEl.innerHTML = `${esc(err.message)} <a href="/bang-gia" style="color:#1E4D3B;font-weight:600;margin-left:4px;">Xem Bảng Giá &rarr;</a>`;
                }
            });
        }

        // Toggle Share on Sổ Nhân Duyên
        const shareBtn = document.getElementById('mayhenMarriageToggleShareBtn');
        if (shareBtn) {
            shareBtn.addEventListener('click', async () => {
                shareBtn.disabled = true;
                try {
                    const res = await postJson(shareBtn.getAttribute('data-endpoint'), {});
                    const box = document.getElementById('mayhenMarriageShareBox');
                    if (res.is_shared && res.share_url) {
                        shareBtn.textContent = 'Tắt Link Chia Sẻ Ẩn Danh';
                        if (box) {
                            box.style.display = 'block';
                            box.innerHTML = `Link chia sẻ ẩn danh: <a href="${esc(res.share_url)}" target="_blank" style="font-weight:600;color:#1E4D3B;">${esc(res.share_url)}</a>`;
                        }
                    } else {
                        shareBtn.textContent = 'Tạo Link Chia Sẻ Ẩn Danh';
                        if (box) { box.style.display = 'none'; box.innerHTML = ''; }
                    }
                } catch (_) {} finally { shareBtn.disabled = false; }
            });
        }

        // Followup AI Form on Sổ Nhân Duyên
        const fuForm = document.getElementById('mayhenMarriageFollowupForm');
        if (fuForm) {
            fuForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const inp = fuForm.querySelector('input[name="question"]');
                const list = document.getElementById('mayhenMarriageFollowupList');
                const errEl = document.getElementById('mayhenMarriageFollowupError');
                if (errEl) errEl.textContent = '';
                try {
                    const res = await postJson(fuForm.getAttribute('data-endpoint'), { question: inp.value });
                    if (list) {
                        const div = document.createElement('div');
                        div.style.cssText = 'padding:12px 14px;background:#FAF5EB;border:1px solid rgba(178,146,94,0.3);border-radius:10px;margin-bottom:10px;font-size:13.5px;';
                        div.innerHTML = `<div style="font-weight:600;color:#85182A;margin-bottom:4px;">Hỏi: ${esc(res.followup.question)}</div><div>${esc(res.followup.answer)}</div>`;
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
