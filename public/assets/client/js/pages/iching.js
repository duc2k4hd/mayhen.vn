/**
 * MAYHEN.VN — GIEO QUẺ KINH DỊCH & GIEO QUẺ ĐẦU NĂM INTERACTIVE ENGINE
 * 100% Pure Vanilla JS • Zero External Libraries
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('mayhenIchingApp');
    if (!root) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const castUrl = root.getAttribute('data-cast-url') || '';
    const canCastFree = root.getAttribute('data-can-cast') === '1';

    let activeMethod = 'coins_3';
    let activeTopic = 'tong_quan';
    let rolledLines = []; // 6 hào từ Hào 1 (Sơ) đến Hào 6 (Thượng), giá trị 6,7,8,9
    let isRolling = false;

    const questionInput = document.getElementById('mayhenIchingQuestion');
    const linesStackEl = document.getElementById('mayhenIchingLinesStack');
    const tossBtn = document.getElementById('mayhenIchingTossOneBtn');
    const tossAllBtn = document.getElementById('mayhenIchingTossAllBtn');
    const resetCoinsBtn = document.getElementById('mayhenIchingResetCoinsBtn');
    const submitCastBtn = document.getElementById('mayhenIchingSubmitCastBtn');
    const resultSection = document.getElementById('mayhenIchingResultSection');
    const resultShell = document.getElementById('mayhenIchingResultShell');
    const coinEls = [
        document.getElementById('mayhenIchingCoin1'),
        document.getElementById('mayhenIchingCoin2'),
        document.getElementById('mayhenIchingCoin3'),
    ];

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // Tạo SVG 6 hào sắc nét cho Quẻ Chủ / Hỗ / Biến (mã 6 bit từ hào 1 -> hào 6)
    function buildHexagramSvg(code6Bits, movingPositions = []) {
        const rows = [];
        for (let i = 5; i >= 0; i--) {
            const pos = i + 1;
            const y = (5 - i) * 16 + 4;
            const isYang = code6Bits[i] === '1';
            const isMoving = movingPositions.includes(pos);
            const fill = isMoving ? '#85182A' : '#2A211B';

            if (isYang) {
                rows.push(`<rect x="4" y="${y}" width="92" height="9" rx="3" fill="${fill}" />`);
            } else {
                rows.push(`<rect x="4" y="${y}" width="40" height="9" rx="3" fill="${fill}" />`);
                rows.push(`<rect x="56" y="${y}" width="40" height="9" rx="3" fill="${fill}" />`);
            }
            if (isMoving) {
                rows.push(`<circle cx="104" cy="${y + 4.5}" r="3.5" fill="#85182A" />`);
            }
        }
        return `<svg viewBox="0 0 112 102" width="108" height="98" aria-hidden="true">${rows.join('')}</svg>`;
    }

    function renderLinesStack() {
        if (!linesStackEl) return;
        const labels = ['Hào Sơ', 'Hào Nhị', 'Hào Tam', 'Hào Tứ', 'Hào Ngũ', 'Hào Thượng'];
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
                const tag = val === 9 ? '9 • Động' : (val === 6 ? '6 • Động' : (val === 7 ? '7 • Dương' : '8 • Âm'));
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
                tossBtn.textContent = `🪙 Tung Đồng Xu — ${labels[rolledLines.length]} (${rolledLines.length + 1}/6)`;
            }
        }
    }

    function tossOnceAnimated() {
        return new Promise((resolve) => {
            if (rolledLines.length >= 6 || isRolling) {
                resolve();
                return;
            }
            isRolling = true;
            const faces = [
                Math.random() < 0.5 ? 3 : 2,
                Math.random() < 0.5 ? 3 : 2,
                Math.random() < 0.5 ? 3 : 2,
            ];

            coinEls.forEach((el) => {
                if (!el) return;
                el.classList.remove('is-tossing');
                void el.offsetWidth;
                el.classList.add('is-tossing');
            });

            setTimeout(() => {
                coinEls.forEach((el, idx) => {
                    if (!el) return;
                    const isYangFace = faces[idx] === 3;
                    const hole = el.querySelector('.mayhen_iching_coin_hole');
                    const lbl = el.querySelector('.mayhen_iching_coin_face_label');
                    if (hole) hole.textContent = isYangFace ? 'D' : 'Â';
                    if (lbl) lbl.textContent = isYangFace ? 'Ngửa (3)' : 'Sấp (2)';
                });
                const sum = faces[0] + faces[1] + faces[2];
                rolledLines.push(sum);
                renderLinesStack();
                isRolling = false;
                resolve();
            }, 640);
        });
    }

    // Chuyển tab 3 phương thức gieo quẻ
    document.querySelectorAll('.mayhen_iching_method_tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            activeMethod = tab.getAttribute('data-method') || 'coins_3';
            document.querySelectorAll('.mayhen_iching_method_tab').forEach((t) => t.classList.toggle('is-active', t === tab));

            const panelKey = activeMethod === 'coins_3' ? 'coins_3' : (activeMethod === 'new_year' ? 'new_year' : 'mai_hoa');
            document.querySelectorAll('.mayhen_iching_stage_panel').forEach((p) => {
                p.classList.toggle('is-active', p.getAttribute('data-stage-panel') === panelKey);
            });
        });
    });

    // Chuyển chủ đề hỏi việc
    document.querySelectorAll('.mayhen_iching_topic_chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            activeTopic = chip.getAttribute('data-topic') || 'tong_quan';
            document.querySelectorAll('.mayhen_iching_topic_chip').forEach((c) => c.classList.toggle('is-active', c === chip));
        });
    });

    // Gợi ý câu hỏi nhanh
    document.querySelectorAll('.mayhen_iching_quick_chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            if (questionInput) {
                questionInput.value = chip.getAttribute('data-sample-q') || '';
                questionInput.focus();
            }
        });
    });

    // Nút tung đồng xu
    tossBtn?.addEventListener('click', () => tossOnceAnimated());
    tossAllBtn?.addEventListener('click', async () => {
        if (isRolling) return;
        if (rolledLines.length >= 6) rolledLines = [];
        renderLinesStack();
        while (rolledLines.length < 6) {
            await tossOnceAnimated();
        }
    });
    resetCoinsBtn?.addEventListener('click', () => {
        if (isRolling) return;
        rolledLines = [];
        renderLinesStack();
    });

    // Chuyển phụ tab Mai Hoa (Giờ động tâm / 2 số)
    const maiHoaModeSelect = document.getElementById('mayhenIchingMaiHoaMode');
    const maiHoaNumsWrap = document.getElementById('mayhenIchingMaiHoaNums');
    maiHoaModeSelect?.addEventListener('change', () => {
        if (maiHoaNumsWrap) {
            maiHoaNumsWrap.style.display = maiHoaModeSelect.value === 'mai_hoa_numbers' ? 'grid' : 'none';
        }
    });

    // Hiển thị bản luận giải 4 tầng + bản đồ 3 quẻ
    function renderReadingResult(payload) {
        if (!resultSection || !resultShell) return;
        const { analysis, interpretation, question, topic_label, result_url } = payload;
        const primary = analysis.primary;
        const mutual = analysis.mutual;
        const changed = analysis.changed;
        const tiYong = analysis.ti_yong;
        const moving = analysis.moving_lines || [];

        const aiTag = interpretation.ai_powered
            ? `<span style="padding:4px 10px; border-radius:999px; background:#1E4D3B; color:#FAF5EB; font-size:11px; font-weight:700;">✦ Luận Giải AI 4 Tầng Cá Nhân Hóa</span>`
            : `<span style="padding:4px 10px; border-radius:999px; background:#5E4315; color:#FAF5EB; font-size:11px; font-weight:700;">✦ Chuẩn Dịch Lý 64 Quẻ</span>`;

        const changedCardHtml = changed
            ? `
                <div class="mayhen_iching_hex_card">
                    <span class="mayhen_iching_hex_role">3. Quẻ Biến (Xu Hướng Tương Lai)</span>
                    <div class="mayhen_iching_hex_svg_wrap">${buildHexagramSvg(changed.code, [])}</div>
                    <h3>Quẻ ${changed.no}: ${escapeHtml(changed.name)} (${escapeHtml(changed.hanzi)})</h3>
                    <span class="mayhen_iching_level_pill">${escapeHtml(changed.level)}</span>
                    <p style="font-size:12.5px; color:#5C5349; margin:0;">${escapeHtml(changed.meaning_short)}</p>
                </div>
            `
            : `
                <div class="mayhen_iching_hex_card">
                    <span class="mayhen_iching_hex_role">3. Quẻ Biến</span>
                    <div style="padding:26px 10px; color:#7A6E62; font-size:13px;">
                        <strong>Quẻ Tĩnh (0 Hào Động)</strong><br>
                        Tình thế giữ nguyên theo bản chất Quẻ Chủ, lấy Quái Từ Quẻ Chủ làm trục luận chính.
                    </div>
                </div>
            `;

        const stepsHtml = (interpretation.action_steps || []).map((s) => `<li>${escapeHtml(s)}</li>`).join('');

        resultShell.innerHTML = `
            <div class="mayhen_iching_result_banner">
                <div style="flex:1; min-width:260px;">
                    <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-bottom:6px;">
                        ${aiTag}
                        <span style="font-size:12px; color:#6A635B;">Chủ đề: <strong>${escapeHtml(topic_label || 'Tổng Quan')}</strong> • ${escapeHtml(analysis.cast_at || '')}</span>
                    </div>
                    <h2 class="mayhen_iching_result_headline">${escapeHtml(interpretation.headline)}</h2>
                    <p style="margin:0; font-size:14px; color:#4A423A;"><strong>Câu hỏi khởi tâm:</strong> "${escapeHtml(question)}"</p>
                </div>
                <div class="mayhen_iching_score_ring">
                    <small>Cát Khí Thể–Dụng</small>
                    <strong>${Number(tiYong.auspicious_score || 75)}/100</strong>
                    <small>${escapeHtml(tiYong.relation)} (${escapeHtml(tiYong.verdict)})</small>
                </div>
            </div>

            <div class="mayhen_iching_triad_grid">
                <div class="mayhen_iching_hex_card is-primary">
                    <span class="mayhen_iching_hex_role">1. Quẻ Chủ (Hiện Tại)</span>
                    <div class="mayhen_iching_hex_svg_wrap">${buildHexagramSvg(primary.code, moving)}</div>
                    <h3>Quẻ ${primary.no}: ${escapeHtml(primary.name)} (${escapeHtml(primary.hanzi)})</h3>
                    <span class="mayhen_iching_level_pill">${escapeHtml(primary.level)}</span>
                    <p style="font-size:12px; color:#6A635B; margin:0 0 6px;">${escapeHtml(primary.trigram_pair_label)}</p>
                    <p style="font-size:12.5px; color:#3F3730; margin:0;"><em>"${escapeHtml(primary.judgment)}"</em></p>
                </div>

                <div class="mayhen_iching_hex_card">
                    <span class="mayhen_iching_hex_role">2. Quẻ Hỗ (Nội Tình Bên Trong)</span>
                    <div class="mayhen_iching_hex_svg_wrap">${buildHexagramSvg(mutual.code, [])}</div>
                    <h3>Quẻ ${mutual.no}: ${escapeHtml(mutual.name)} (${escapeHtml(mutual.hanzi)})</h3>
                    <span class="mayhen_iching_level_pill">${escapeHtml(mutual.level)}</span>
                    <p style="font-size:12.5px; color:#5C5349; margin:0;">${escapeHtml(mutual.meaning_short)}</p>
                </div>

                ${changedCardHtml}
            </div>

            <div class="mayhen_iching_layers_grid">
                <div class="mayhen_iching_layer_card">
                    <span class="mayhen_iching_layer_badge">Tầng 1 • Tượng Quẻ &amp; Thể – Dụng</span>
                    <h4>Bản Chất &amp; Ngũ Hành Sinh Khắc</h4>
                    <p>${escapeHtml(interpretation.layer_1_image)}</p>
                </div>
                <div class="mayhen_iching_layer_card">
                    <span class="mayhen_iching_layer_badge">Tầng 2 • Hào Động (${moving.length > 0 ? 'Hào ' + moving.join(', ') : 'Quẻ Tĩnh'})</span>
                    <h4>Điểm Chuyển Biến Then Chốt</h4>
                    <p>${escapeHtml(interpretation.layer_2_moving_lines)}</p>
                </div>
                <div class="mayhen_iching_layer_card">
                    <span class="mayhen_iching_layer_badge">Tầng 3 • Quẻ Hỗ &amp; Quẻ Biến</span>
                    <h4>Nội Tình &amp; Xu Hướng Tương Lai</h4>
                    <p>${escapeHtml(interpretation.layer_3_evolution)}</p>
                </div>
                <div class="mayhen_iching_layer_card">
                    <span class="mayhen_iching_layer_badge">Tầng 4 • Ứng Kỳ &amp; Thời Điểm</span>
                    <h4>Nhịp Chuyển Biến Thuận Thời</h4>
                    <p>${escapeHtml(interpretation.layer_4_timing_actions)}</p>
                </div>
            </div>

            <div class="mayhen_iching_actions_box">
                <h4>✦ 3 Hành Động Gợi Ý Thực Tế Cho Bạn Lúc Này</h4>
                <ul>${stepsHtml}</ul>
            </div>

            <div class="mayhen_iching_caution_box">
                <strong>Lưu ý thận trọng:</strong> ${escapeHtml(interpretation.caution_note)}
            </div>

            ${result_url ? `
                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:20px; justify-content:flex-end;">
                    <a href="${escapeHtml(result_url)}" class="mayhen_iching_btn_gold">Mở Trang Lá Quẻ Riêng &rarr;</a>
                    <button type="button" class="mayhen_iching_btn_outline" id="mayhenIchingCopyResultUrl">Sao chép link kết quả</button>
                </div>
            ` : ''}
        `;

        resultShell.querySelector('#mayhenIchingCopyResultUrl')?.addEventListener('click', () => {
            navigator.clipboard?.writeText(result_url);
            const btn = resultShell.querySelector('#mayhenIchingCopyResultUrl');
            if (btn) btn.textContent = '✓ Đã sao chép link';
        });

        resultSection.style.display = 'block';
        resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Gửi gieo quẻ & luận giải 4 tầng
    submitCastBtn?.addEventListener('click', async () => {
        if (!canCastFree) {
            document.getElementById('mayhenIchingLoginModal')?.classList.add('is-open');
            return;
        }

        const question = (questionInput?.value || '').trim();
        if (question.length < 5) {
            alert('Vui lòng nhập điều bạn đang trăn trở (tối thiểu 5 ký tự) để quẻ đáp đúng trọng tâm.');
            questionInput?.focus();
            return;
        }

        let payloadMethod = activeMethod;
        if (activeMethod === 'mai_hoa') {
            payloadMethod = maiHoaModeSelect?.value || 'mai_hoa_time';
        }

        // Nếu đang ở tab Đầu Năm thì lắc ống quẻ sinh động trước
        if (activeMethod === 'new_year') {
            const tube = document.getElementById('mayhenIchingTubeBox');
            if (tube) {
                tube.classList.remove('is-shaking');
                void tube.offsetWidth;
                tube.classList.add('is-shaking');
                await new Promise((r) => setTimeout(r, 700));
            }
        }

        // Nếu ở tab 3 đồng xu mà người dùng chưa tung hết 6 hào -> tự tung nốt
        if (payloadMethod === 'coins_3' && rolledLines.length < 6) {
            while (rolledLines.length < 6) {
                await tossOnceAnimated();
            }
        }

        submitCastBtn.disabled = true;
        const oldLabel = submitCastBtn.textContent;
        submitCastBtn.textContent = '✦ Đang Lập Quẻ & Luận Giải 4 Tầng...';

        try {
            const body = {
                method: payloadMethod,
                topic: activeTopic,
                question,
            };
            if (payloadMethod === 'coins_3' && rolledLines.length === 6) {
                body.lines = rolledLines;
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

            if (res.status === 401 || res.status === 403) {
                document.getElementById('mayhenIchingLoginModal')?.classList.add('is-open');
                return;
            }
            if (!res.ok || !data.success) {
                alert(data.message || 'Không thể luận giải quẻ lúc này. Vui lòng thử lại.');
                return;
            }

            renderReadingResult(data);
        } catch {
            alert('Lỗi kết nối tới máy chủ. Vui lòng thử lại sau giây lát.');
        } finally {
            submitCastBtn.disabled = false;
            submitCastBtn.textContent = oldLabel;
        }
    });

    // Xem minh họa 1 quẻ mẫu cho khách chưa đăng nhập
    document.getElementById('mayhenIchingShowDemoBtn')?.addEventListener('click', () => {
        document.getElementById('mayhenIchingLoginModal')?.classList.remove('is-open');
        const demoEl = document.getElementById('mayhenIchingDemoJson');
        if (!demoEl) return;
        try {
            const demoPayload = JSON.parse(demoEl.textContent || '{}');
            renderReadingResult(demoPayload);
        } catch {
            // Bỏ qua
        }
    });

    document.getElementById('mayhenIchingLoginModalClose')?.addEventListener('click', () => {
        document.getElementById('mayhenIchingLoginModal')?.classList.remove('is-open');
    });

    // Tra cứu 64 Quẻ Kinh Dịch trên trang SEO
    const hexSearchInput = document.getElementById('mayhenIching64Search');
    const hexCards = Array.from(document.querySelectorAll('.mayhen_iching_64_card'));
    let activeLevelFilter = 'all';

    function filter64Grid() {
        const q = (hexSearchInput?.value || '').trim().toLowerCase();
        hexCards.forEach((card) => {
            const text = (card.getAttribute('data-search') || '').toLowerCase();
            const lvl = card.getAttribute('data-level') || '';
            const matchQ = q === '' || text.includes(q);
            const matchLvl = activeLevelFilter === 'all' || lvl === activeLevelFilter;
            card.style.display = matchQ && matchLvl ? '' : 'none';
        });
    }

    hexSearchInput?.addEventListener('input', filter64Grid);
    document.querySelectorAll('.mayhen_iching_filter_btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            activeLevelFilter = btn.getAttribute('data-level-filter') || 'all';
            document.querySelectorAll('.mayhen_iching_filter_btn').forEach((b) => b.classList.toggle('is-active', b === btn));
            filter64Grid();
        });
    });

    // Click xem chi tiết từng quẻ trong kho 64 quẻ
    const hexModal = document.getElementById('mayhenIchingHexModal');
    const hexModalBody = document.getElementById('mayhenIchingHexModalBody');
    const catalogJsonEl = document.getElementById('mayhenIchingCatalogJson');
    let catalogMap = {};
    if (catalogJsonEl) {
        try {
            const list = JSON.parse(catalogJsonEl.textContent || '[]');
            Object.values(list).forEach((h) => { catalogMap[String(h.no)] = h; });
        } catch {
            // Bỏ qua
        }
    }

    hexCards.forEach((card) => {
        card.addEventListener('click', () => {
            const no = card.getAttribute('data-hex-no');
            const h = catalogMap[String(no)];
            if (!h || !hexModal || !hexModalBody) return;

            hexModalBody.innerHTML = `
                <div style="text-align:center; margin-bottom:14px;">
                    <span class="mayhen_iching_level_pill">Quẻ số ${h.no} • ${escapeHtml(h.level)}</span>
                    <div class="mayhen_iching_hex_svg_wrap">${buildHexagramSvg(h.code, [])}</div>
                    <h3 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; font-size:24px; margin:0 0 4px; color:#85182A;">
                        ${escapeHtml(h.name)} (${escapeHtml(h.hanzi)})
                    </h3>
                    <p style="font-size:13px; color:#6A635B; margin:0;">${escapeHtml(h.trigram_pair_label)}</p>
                </div>
                <div style="font-size:13.5px; line-height:1.7; color:#3D352E;">
                    <p><strong>Quái từ:</strong> <em>${escapeHtml(h.judgment)}</em></p>
                    <p><strong>Đại tượng:</strong> ${escapeHtml(h.image)}</p>
                    <p><strong>Tổng quan:</strong> ${escapeHtml(h.summary)}</p>
                    <p><strong>Tình duyên &amp; Hôn nhân:</strong> ${escapeHtml(h.love)}</p>
                    <p><strong>Công danh &amp; Sự nghiệp:</strong> ${escapeHtml(h.career)}</p>
                    <p><strong>Tài lộc:</strong> ${escapeHtml(h.wealth)}</p>
                    <p><strong>Gia đạo:</strong> ${escapeHtml(h.family)}</p>
                    <div style="padding:12px 14px; background:#FAF5EB; border-radius:12px; border:1px solid #DFCFAF; margin-top:10px;">
                        <strong>Lời khuyên Kinh Dịch:</strong> ${escapeHtml(h.advice)}
                    </div>
                </div>
            `;
            hexModal.classList.add('is-open');
        });
    });

    document.getElementById('mayhenIchingHexModalClose')?.addEventListener('click', () => {
        hexModal?.classList.remove('is-open');
    });

    renderLinesStack();
});
