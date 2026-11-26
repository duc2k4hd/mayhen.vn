/**
 * Mây Hẹn – The Wedding Journey (Pure Vanilla JavaScript)
 * - Date Finder (AJAX + Compare 3+ Dates + Day Detail Drawer)
 * - Planner Landing (Quick Budget VND Calculator + Free/AI Content Scripts)
 * - Personal Wedding Workspace (Tabs, Checklist CRUD, Budget VND CRUD, 2D Floor Plan & Seating Studio, Timeline Conflict Checker, Vendors, AI Concierge)
 */
(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatVnd(n) {
        const val = Math.round(Number(n) || 0);
        return val.toLocaleString('vi-VN') + ' ₫';
    }

    function showOverlay(title, desc) {
        const overlay = document.getElementById('mayhenWeddingOverlay');
        if (!overlay) return;
        const t = overlay.querySelector('[data-overlay-title]');
        const d = overlay.querySelector('[data-overlay-desc]');
        if (t && title) t.textContent = title;
        if (d && desc) d.textContent = desc;
        overlay.classList.add('is-open');
    }

    function hideOverlay() {
        const overlay = document.getElementById('mayhenWeddingOverlay');
        if (overlay) overlay.classList.remove('is-open');
    }

    async function apiJson(url, method, body) {
        const res = await fetch(url, {
            method: method || 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: body ? JSON.stringify(body) : undefined,
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const err = new Error(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
            err.status = res.status;
            err.data = data;
            throw err;
        }
        return data;
    }

    /* =========================================================================
     * 1. DATE FINDER PAGE (/xem-ngay-cuoi)
     * ========================================================================= */
    function initDateFinder() {
        const root = document.querySelector('[data-wedding-page="date-finder"]');
        if (!root) return;

        const form = document.getElementById('weddingDateFilterForm');
        const calendarEl = document.getElementById('weddingDateCalendar');
        const recommendedEl = document.getElementById('weddingDateRecommended');
        const detailEl = document.getElementById('weddingDateDetail');
        const compareBox = document.getElementById('weddingDateCompareBox');
        const coupleMetaEl = document.getElementById('weddingCoupleMeta');
        const findUrl = root.getAttribute('data-find-url');
        const compareUrl = root.getAttribute('data-compare-url');

        let currentData = null;
        try {
            currentData = JSON.parse(root.getAttribute('data-initial') || 'null');
        } catch (e) {
            currentData = null;
        }
        const selectedCompare = new Set();

        function renderCalendar(data) {
            if (!data || !calendarEl) return;
            currentData = data;

            // Render couple Kim Lau & Dai Loi banner if present
            if (coupleMetaEl) {
                const parts = [];
                if (data.couple?.kim_lau) {
                    const kl = data.couple.kim_lau;
                    parts.push(
                        '<div class="mayhen_wedding_budget_alert ' + (kl.is_kim_lau ? 'mayhen_wedding_budget_alert--warn' : 'mayhen_wedding_budget_alert--info') + '">' +
                        '<strong>Kim Lâu năm ' + escapeHtml(kl.wedding_year) + ':</strong> ' + escapeHtml(kl.label) +
                        '<div style="font-size:12.5px;margin-top:4px;opacity:0.85;">' + escapeHtml(kl.note) + '</div></div>'
                    );
                }
                if (data.couple?.dai_loi && data.couple.dai_loi.enabled) {
                    const dl = data.couple.dai_loi;
                    parts.push(
                        '<div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--info">' +
                        '<strong>Hành Giá Đại Lợi Nguyệt (Tuổi ' + escapeHtml(dl.bride_branch) + '):</strong> Đại Lợi tháng ' +
                        escapeHtml((dl.dai_loi || []).join(', ')) + ' ÂL · Tiểu Lợi tháng ' + escapeHtml((dl.tieu_loi || []).join(', ')) + ' ÂL.' +
                        '<div style="font-size:12px;margin-top:4px;opacity:0.85;">' + escapeHtml(dl.source) + '</div></div>'
                    );
                }
                coupleMetaEl.innerHTML = parts.join('');
            }

            // Build month cells aligned Monday..Sunday
            const days = data.calendar_days || [];
            if (days.length === 0) {
                calendarEl.innerHTML = '<p>Không có dữ liệu ngày trong tháng này.</p>';
                return;
            }
            const firstParts = days[0].iso_date.split('-').map(Number);
            const firstDow = new Date(firstParts[0], firstParts[1] - 1, firstParts[2]).getDay(); // 0 CN..6 T7
            const pad = (firstDow + 6) % 7; // Monday = 0

            let html = '';
            for (let i = 0; i < pad; i++) {
                html += '<div></div>';
            }

            days.forEach((d) => {
                const blocked = !d.passes_hard_constraints;
                const isSel = selectedCompare.has(d.iso_date);
                const cls = [
                    'mayhen_wedding_date_cell',
                    blocked ? 'is-blocked' : ('is-' + d.tier),
                    isSel ? 'is-selected' : '',
                ].join(' ');
                const badgeCls = 'mayhen_wedding_date_badge mayhen_wedding_date_badge--' + d.tier;
                const solarDay = d.solar?.day ?? d.iso_date.slice(-2);
                const lunarShort = (d.lunar?.day ?? '') + '/' + (d.lunar?.month ?? '') + (d.lunar?.is_leap ? 'N' : '');

                html +=
                    '<button type="button" class="' + cls + '" data-iso="' + escapeHtml(d.iso_date) + '">' +
                    '<div>' +
                    '<div class="mayhen_wedding_date_cell_solar">' + escapeHtml(solarDay) + (d.is_weekend ? ' <span style="font-size:10px;color:#85182A;">CT</span>' : '') + '</div>' +
                    '<div class="mayhen_wedding_date_cell_lunar">' + escapeHtml(lunarShort) + ' ÂL · ' + escapeHtml(d.truc?.name || '') + '</div>' +
                    '</div>' +
                    '<span class="' + badgeCls + '">' + (blocked ? 'Lọc ẩn' : escapeHtml(d.tier === 'favorable' ? 'Thuận lợi' : (d.tier === 'balanced' ? 'Cân nhắc' : 'Lưu ý'))) + '</span>' +
                    '</button>';
            });

            calendarEl.innerHTML = html;

            // Recommended list
            if (recommendedEl) {
                const rec = data.recommended || [];
                if (rec.length === 0) {
                    recommendedEl.innerHTML = '<div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--warn">Không có ngày nào thỏa mãn đồng thời các ràng buộc thực tế bạn chọn trong tháng này. Hãy thử nới lỏng bộ lọc hoặc chuyển sang tháng kế tiếp.</div>';
                } else {
                    recommendedEl.innerHTML = rec.map((d) => {
                        const checked = selectedCompare.has(d.iso_date);
                        return (
                            '<div class="mayhen_wedding_timeline_item">' +
                            '<div class="mayhen_wedding_timeline_time">' + escapeHtml(d.solar?.label || d.iso_date) +
                            '<div style="font-size:12px;color:#5F5750;font-weight:500;">' + escapeHtml(d.weekday || '') + ' · ' + escapeHtml(d.lunar?.day + '/' + d.lunar?.month + ' ÂL') + '</div></div>' +
                            '<div><strong>' + escapeHtml(d.day_quality?.label + ' (' + d.day_quality?.god_name + ')') + ' · Trực ' + escapeHtml(d.truc?.name) + '</strong>' +
                            '<div style="font-size:12.5px;color:#5F5750;">Can Chi: ' + escapeHtml(d.can_chi_day) + ' · ' + escapeHtml(d.tier_label) + '</div></div>' +
                            '<div style="display:flex;gap:8px;flex-wrap:wrap;">' +
                            '<button type="button" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--outline mayhen_wedding_planner_btn--sm" data-inspect-iso="' + escapeHtml(d.iso_date) + '">Chi tiết</button>' +
                            '<button type="button" class="mayhen_wedding_planner_btn ' + (checked ? 'mayhen_wedding_planner_btn--crimson' : 'mayhen_wedding_planner_btn--primary') + ' mayhen_wedding_planner_btn--sm" data-compare-toggle="' + escapeHtml(d.iso_date) + '">' + (checked ? '✓ Đã chọn so sánh' : '+ So sánh') + '</button>' +
                            '</div></div>'
                        );
                    }).join('');
                }
            }

            if (days.length > 0) {
                renderDayDetail((data.recommended && data.recommended[0]) || days[0]);
            }
        }

        function renderDayDetail(d) {
            if (!d || !detailEl) return;
            const favHtml = (d.favorable_rules || []).map((r) => '<li>' + escapeHtml(r) + '</li>').join('') || '<li>Chưa có yếu tố nổi bật riêng theo hồ sơ nghi lễ.</li>';
            const cauHtml = (d.caution_rules || []).map((r) => '<li>' + escapeHtml(r) + '</li>').join('') || '<li>Không ghi nhận kiêng kỵ trọng yếu theo bộ quy tắc đang bật.</li>';
            const winHtml = (d.ceremony_windows || []).map((w) =>
                '<li><strong>' + escapeHtml(w.slot) + ' (' + escapeHtml(w.range) + ')</strong> — Khởi hành dự kiến ' + escapeHtml(w.suggested_departure) + '. ' + escapeHtml(w.note) + '</li>'
            ).join('') || '<li>Vui lòng tham khảo danh sách 6 khung giờ Hoàng Đạo trong ngày.</li>';

            detailEl.innerHTML =
                '<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px;">' +
                '<div><h3 style="margin:0 0 4px;font-size:20px;">' + escapeHtml(d.weekday + ', ' + (d.solar?.label || d.iso_date)) + '</h3>' +
                '<div style="font-size:13.5px;color:#5F5750;">Âm lịch: ' + escapeHtml(d.lunar?.label_full || '') + ' · Ngày ' + escapeHtml(d.can_chi_day) + ' · Hỷ Thần: ' + escapeHtml(d.directions?.hy_than || '') + '</div></div>' +
                '<button type="button" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--crimson mayhen_wedding_planner_btn--sm" data-compare-toggle="' + escapeHtml(d.iso_date) + '">' + (selectedCompare.has(d.iso_date) ? '✓ Đang so sánh' : '+ Đưa vào bảng so sánh') + '</button>' +
                '</div>' +
                '<div class="mayhen_wedding_planner_grid_equal">' +
                '<div style="background:#F2F9F5;padding:14px;border-radius:12px;"><strong style="color:#1E4D3B;">Yếu tố thuận lợi</strong><ul style="margin:8px 0 0;padding-left:18px;font-size:13.5px;">' + favHtml + '</ul></div>' +
                '<div style="background:#FDF4F4;padding:14px;border-radius:12px;"><strong style="color:#85182A;">Điểm cần lưu tâm</strong><ul style="margin:8px 0 0;padding-left:18px;font-size:13.5px;">' + cauHtml + '</ul></div>' +
                '</div>' +
                '<div style="margin-top:14px;background:#F6EFE2;padding:14px;border-radius:12px;"><strong>Khung giờ Hoàng Đạo ban ngày &amp; Đệm di chuyển thực tế</strong><ul style="margin:8px 0 0;padding-left:18px;font-size:13.5px;">' + winHtml + '</ul></div>';
        }

        async function runCompare() {
            if (!compareBox) return;
            const list = Array.from(selectedCompare);
            if (list.length === 0) {
                compareBox.innerHTML = '<p style="font-size:13.5px;color:#5F5750;">Chọn từ 2 đến 5 ngày trên lịch để so sánh song song (thứ trong tuần, Hoàng Đạo, Trực, xung tuổi, khung giờ ban ngày).</p>';
                return;
            }
            try {
                const payload = collectFilterPayload();
                const res = await apiJson(compareUrl, 'POST', {
                    dates: list,
                    activity: payload.activity,
                    groom_year: payload.groom_year,
                    bride_year: payload.bride_year,
                    check_dai_loi: payload.check_dai_loi,
                });
                const cands = res.data?.candidates || [];
                compareBox.innerHTML =
                    '<div class="mayhen_wedding_date_compare_table_wrap"><table class="mayhen_wedding_date_compare_table"><thead><tr>' +
                    '<th>Ngày Dương / Âm</th><th>Thứ &amp; Cuối tuần</th><th>Hoàng Đạo &amp; Trực</th><th>Xung tuổi / Lưu ý</th><th>Giờ đẹp ban ngày</th><th>Phân bậc</th>' +
                    '</tr></thead><tbody>' +
                    cands.map((c) =>
                        '<tr>' +
                        '<td><strong>' + escapeHtml(c.solar?.label || c.iso_date) + '</strong><br><span style="font-size:12px;color:#5F5750;">' + escapeHtml(c.lunar?.day + '/' + c.lunar?.month + ' ÂL · ' + c.can_chi_day) + '</span></td>' +
                        '<td>' + escapeHtml(c.weekday) + (c.is_weekend ? ' <span class="mayhen_wedding_date_badge mayhen_wedding_date_badge--favorable">Cuối tuần</span>' : '') + '</td>' +
                        '<td>' + escapeHtml(c.day_quality?.label + ' (' + c.day_quality?.god_name + ')') + '<br>Trực ' + escapeHtml(c.truc?.name) + '</td>' +
                        '<td>' + escapeHtml((c.caution_rules || []).slice(0, 2).join('; ') || 'Không có lưu ý lớn') + '</td>' +
                        '<td>' + escapeHtml((c.daylight_hoang_dao_hours || []).map((h) => h.branch_name).join(', ') || 'Ít giờ ban ngày') + '</td>' +
                        '<td><span class="mayhen_wedding_date_badge mayhen_wedding_date_badge--' + escapeHtml(c.tier) + '">' + escapeHtml(c.tier_label) + '</span></td>' +
                        '</tr>'
                    ).join('') +
                    '</tbody></table></div>';
            } catch (e) {
                compareBox.innerHTML = '<div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--warn">' + escapeHtml(e.message) + '</div>';
            }
        }

        function collectFilterPayload() {
            const fd = new FormData(form);
            const excludedRaw = String(fd.get('excluded_dates_text') || '').split(/[\s,;]+/).filter((s) => /^\d{4}-\d{2}-\d{2}$/.test(s));
            return {
                year: Number(fd.get('year')) || new Date().getFullYear(),
                month: Number(fd.get('month')) || (new Date().getMonth() + 1),
                activity: String(fd.get('activity') || 'le_thanh_hon'),
                weekends_only: fd.get('weekends_only') === '1',
                min_prep_days: Number(fd.get('min_prep_days')) || 0,
                excluded_dates: excludedRaw,
                groom_year: fd.get('groom_year') ? Number(fd.get('groom_year')) : null,
                bride_year: fd.get('bride_year') ? Number(fd.get('bride_year')) : null,
                check_kim_lau: fd.get('check_kim_lau') === '1',
                check_dai_loi: fd.get('check_dai_loi') === '1',
                avoid_clash_couple: fd.get('avoid_clash_couple') === '1',
                prefer_daylight_hours: true,
            };
        }

        if (form) {
            form.addEventListener('submit', async (ev) => {
                ev.preventDefault();
                showOverlay('Đang tra cứu Lịch Vạn Niên & lọc ngày cưới...', 'Kết hợp ràng buộc thực tế và quy tắc truyền thống UTC+7.');
                try {
                    const res = await apiJson(findUrl, 'POST', collectFilterPayload());
                    renderCalendar(res.data);
                } catch (e) {
                    alert(e.message);
                } finally {
                    hideOverlay();
                }
            });
        }

        root.addEventListener('click', (ev) => {
            const cell = ev.target.closest('.mayhen_wedding_date_cell');
            if (cell && currentData) {
                const iso = cell.getAttribute('data-iso');
                const found = (currentData.calendar_days || []).find((x) => x.iso_date === iso);
                if (found) renderDayDetail(found);
                return;
            }
            const inspectBtn = ev.target.closest('[data-inspect-iso]');
            if (inspectBtn && currentData) {
                const iso = inspectBtn.getAttribute('data-inspect-iso');
                const found = (currentData.calendar_days || []).find((x) => x.iso_date === iso);
                if (found) {
                    renderDayDetail(found);
                    detailEl?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                return;
            }
            const cmpBtn = ev.target.closest('[data-compare-toggle]');
            if (cmpBtn) {
                const iso = cmpBtn.getAttribute('data-compare-toggle');
                if (selectedCompare.has(iso)) {
                    selectedCompare.delete(iso);
                } else if (selectedCompare.size < 5) {
                    selectedCompare.add(iso);
                } else {
                    alert('Chỉ so sánh tối đa 5 ngày ứng viên cùng lúc.');
                    return;
                }
                if (currentData) renderCalendar(currentData);
                runCompare();
            }
        });

        if (currentData) {
            renderCalendar(currentData);
            runCompare();
        }
    }

    /* =========================================================================
     * 2. PLANNER LANDING (/to-chuc-dam-cuoi) — Quick Budget & Content Scripts
     * ========================================================================= */
    function initPlannerLanding() {
        const root = document.querySelector('[data-wedding-page="planner-landing"]');
        if (!root) return;

        const budgetForm = document.getElementById('quickBudgetForm');
        const budgetOut = document.getElementById('quickBudgetResult');
        const budgetUrl = root.getAttribute('data-quick-budget-url');

        if (budgetForm && budgetOut) {
            budgetForm.addEventListener('submit', async (ev) => {
                ev.preventDefault();
                const fd = new FormData(budgetForm);
                try {
                    const res = await apiJson(budgetUrl, 'POST', {
                        total_budget_vnd: Number(fd.get('total_budget_vnd')) || 150000000,
                        guest_count: Number(fd.get('guest_count')) || 150,
                        seats_per_table: Number(fd.get('seats_per_table')) || 10,
                    });
                    const d = res.data;
                    budgetOut.innerHTML =
                        '<div class="mayhen_wedding_planner_grid_3" style="margin-bottom:14px;">' +
                        '<div class="mayhen_wedding_budget_kpi"><div class="mayhen_wedding_budget_kpi_label">Số bàn chính + dự phòng</div><div class="mayhen_wedding_budget_kpi_value">' + escapeHtml(d.estimated_tables) + ' + ' + escapeHtml(d.reserve_tables) + ' bàn</div></div>' +
                        '<div class="mayhen_wedding_budget_kpi"><div class="mayhen_wedding_budget_kpi_label">Chi phí bình quân / khách</div><div class="mayhen_wedding_budget_kpi_value mayhen_wedding_budget_kpi_value--jade">' + escapeHtml(formatVnd(d.cost_per_guest_vnd)) + '</div></div>' +
                        '<div class="mayhen_wedding_budget_kpi"><div class="mayhen_wedding_budget_kpi_label">Tổng ngân sách (VND nguyên)</div><div class="mayhen_wedding_budget_kpi_value mayhen_wedding_budget_kpi_value--crimson">' + escapeHtml(formatVnd(d.total_budget_vnd)) + '</div></div>' +
                        '</div>' +
                        '<div class="mayhen_wedding_date_compare_table_wrap"><table class="mayhen_wedding_date_compare_table"><thead><tr><th>Hạng mục</th><th>Tỷ trọng</th><th>Phân bổ đề xuất (VND)</th></tr></thead><tbody>' +
                        (d.allocations || []).map((a) =>
                            '<tr><td>' + escapeHtml(a.label) + '</td><td>' + escapeHtml(a.ratio) + '%</td><td><strong>' + escapeHtml(formatVnd(a.budgeted_vnd)) + '</strong></td></tr>'
                        ).join('') +
                        '</tbody></table></div>';
                } catch (e) {
                    budgetOut.innerHTML = '<div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--warn">' + escapeHtml(e.message) + '</div>';
                }
            });
        }

        initScriptGenerator(root);
        initCreateProjectForm();
    }

    function initScriptGenerator(root) {
        const scriptForm = document.getElementById('weddingScriptForm');
        const scriptOut = document.getElementById('weddingScriptOutput');
        const scriptUrl = root.getAttribute('data-script-url');
        if (!scriptForm || !scriptOut || !scriptUrl) return;

        scriptForm.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            const fd = new FormData(scriptForm);
            const useAi = fd.get('use_ai') === '1';
            showOverlay(
                useAi ? 'AI Wedding Concierge đang biên soạn nội dung...' : 'Đang tạo bản mẫu chuẩn biên tập...',
                'Hệ thống đang cá nhân hóa theo đúng tên cặp đôi, vùng miền và ngày cưới.'
            );
            try {
                const res = await apiJson(scriptUrl, 'POST', {
                    script_type: String(fd.get('script_type') || 'mc_ceremony'),
                    groom_name: String(fd.get('groom_name') || ''),
                    bride_name: String(fd.get('bride_name') || ''),
                    wedding_date: String(fd.get('wedding_date') || ''),
                    region: String(fd.get('region') || 'bac'),
                    tone: String(fd.get('tone') || 'Trang trọng, ấm áp'),
                    custom_notes: String(fd.get('custom_notes') || ''),
                    use_ai: useAi,
                    project_slug: root.getAttribute('data-project-slug') || null,
                });
                const d = res.data;
                scriptOut.innerHTML =
                    '<div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--info"><strong>' + escapeHtml(d.title) + '</strong> (' + (d.source === 'ai' ? 'AI Wedding Concierge' : 'Mẫu biên tập chuẩn') + ')</div>' +
                    '<pre style="white-space:pre-wrap;font-family:inherit;background:#FFFDF9;border:1px solid #E5DAC8;border-radius:12px;padding:16px;font-size:14px;line-height:1.7;">' + escapeHtml(d.content) + '</pre>' +
                    '<p style="font-size:12px;color:#5F5750;margin-top:8px;">' + escapeHtml(d.disclaimer) + '</p>';
            } catch (e) {
                if (e.status === 401 && e.data?.login_url) {
                    scriptOut.innerHTML = '<div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--warn">' + escapeHtml(e.message) + ' <a href="' + escapeHtml(e.data.login_url) + '" style="font-weight:700;color:#85182A;">Đăng nhập ngay →</a></div>';
                } else {
                    scriptOut.innerHTML = '<div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--warn">' + escapeHtml(e.message) + '</div>';
                }
            } finally {
                hideOverlay();
            }
        });
    }

    function initCreateProjectForm() {
        const createForm = document.getElementById('createWeddingProjectForm');
        if (!createForm) return;
        createForm.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            showOverlay('Đang khởi tạo Hồ Sơ Tổ Chức Đám Cưới...', 'Tự động tạo Checklist co giãn theo ngày cưới, bảng Ngân sách VND và Sơ đồ bàn 2D.');
            const fd = new FormData(createForm);
            try {
                const res = await apiJson(createForm.getAttribute('action'), 'POST', {
                    groom_name: String(fd.get('groom_name') || ''),
                    bride_name: String(fd.get('bride_name') || ''),
                    wedding_date: String(fd.get('wedding_date') || '') || null,
                    region_style: String(fd.get('region_style') || 'bac'),
                    wedding_style: String(fd.get('wedding_style') || 'nha_hang'),
                    budget_total_vnd: Number(fd.get('budget_total_vnd')) || 150000000,
                    guest_target: Number(fd.get('guest_target')) || 150,
                });
                if (res.redirect_url) {
                    window.location.href = res.redirect_url;
                }
            } catch (e) {
                hideOverlay();
                alert(e.message);
            }
        });
    }

    /* =========================================================================
     * 3. PERSONAL WEDDING WORKSPACE (/to-chuc-dam-cuoi/du-an/{slug})
     * ========================================================================= */
    function initWorkspace() {
        const root = document.querySelector('[data-wedding-page="workspace"]');
        if (!root) return;

        // Tabs
        const tabBtns = root.querySelectorAll('[data-tab-target]');
        const tabPanels = root.querySelectorAll('[data-tab-panel]');
        tabBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-tab-target');
                tabBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
                tabPanels.forEach((p) => p.classList.toggle('is-active', p.getAttribute('data-tab-panel') === target));
            });
        });

        initScriptGenerator(root);

        // Checklist: toggle status & add task & delete task
        const taskForm = document.getElementById('addTaskForm');
        if (taskForm) {
            taskForm.addEventListener('submit', async (ev) => {
                ev.preventDefault();
                const fd = new FormData(taskForm);
                try {
                    await apiJson(taskForm.getAttribute('action'), 'POST', {
                        phase: String(fd.get('phase') || 'Bổ sung'),
                        title: String(fd.get('title') || ''),
                        due_date: String(fd.get('due_date') || '') || null,
                        priority: String(fd.get('priority') || 'medium'),
                        assignee: String(fd.get('assignee') || 'Cặp đôi'),
                    });
                    window.location.reload();
                } catch (e) {
                    alert(e.message);
                }
            });
        }

        root.addEventListener('change', async (ev) => {
            const statusSelect = ev.target.closest('[data-task-status-url]');
            if (statusSelect) {
                try {
                    await apiJson(statusSelect.getAttribute('data-task-status-url'), 'PATCH', {
                        status: statusSelect.value,
                    });
                } catch (e) {
                    alert(e.message);
                }
            }
        });

        root.addEventListener('click', async (ev) => {
            const delBtn = ev.target.closest('[data-delete-url]');
            if (delBtn) {
                if (!confirm('Bạn có chắc muốn xóa mục này?')) return;
                try {
                    await apiJson(delBtn.getAttribute('data-delete-url'), 'DELETE');
                    window.location.reload();
                } catch (e) {
                    alert(e.message);
                }
            }
        });

        // Add Expense
        const expenseForm = document.getElementById('addExpenseForm');
        if (expenseForm) {
            expenseForm.addEventListener('submit', async (ev) => {
                ev.preventDefault();
                const fd = new FormData(expenseForm);
                try {
                    await apiJson(expenseForm.getAttribute('action'), 'POST', {
                        category: String(fd.get('category') || 'venue_catering'),
                        title: String(fd.get('title') || ''),
                        payer_side: String(fd.get('payer_side') || 'couple'),
                        status: String(fd.get('status') || 'committed'),
                        budgeted_vnd: Number(fd.get('budgeted_vnd')) || 0,
                        committed_vnd: Number(fd.get('committed_vnd')) || 0,
                        deposit_vnd: Number(fd.get('deposit_vnd')) || 0,
                        paid_vnd: Number(fd.get('paid_vnd')) || 0,
                    });
                    window.location.reload();
                } catch (e) {
                    alert(e.message);
                }
            });
        }

        // Add Timeline Item
        const timelineForm = document.getElementById('addTimelineForm');
        if (timelineForm) {
            timelineForm.addEventListener('submit', async (ev) => {
                ev.preventDefault();
                const fd = new FormData(timelineForm);
                try {
                    await apiJson(timelineForm.getAttribute('action'), 'POST', {
                        title: String(fd.get('title') || ''),
                        start_time: String(fd.get('start_time') || '09:00'),
                        end_time: String(fd.get('end_time') || '10:00'),
                        location: String(fd.get('location') || ''),
                        assignee: String(fd.get('assignee') || ''),
                    });
                    window.location.reload();
                } catch (e) {
                    alert(e.message);
                }
            });
        }

        // Add Vendor
        const vendorForm = document.getElementById('addVendorForm');
        if (vendorForm) {
            vendorForm.addEventListener('submit', async (ev) => {
                ev.preventDefault();
                const fd = new FormData(vendorForm);
                try {
                    await apiJson(vendorForm.getAttribute('action'), 'POST', {
                        category: String(fd.get('category') || 'venue_catering'),
                        name: String(fd.get('name') || ''),
                        phone: String(fd.get('phone') || ''),
                        quote_vnd: Number(fd.get('quote_vnd')) || 0,
                        deposit_vnd: Number(fd.get('deposit_vnd')) || 0,
                        status: String(fd.get('status') || 'quoted'),
                        pros: String(fd.get('pros') || ''),
                        cons: String(fd.get('cons') || ''),
                    });
                    window.location.reload();
                } catch (e) {
                    alert(e.message);
                }
            });
        }

        // 2D Floor Plan & Seating Studio
        initFloorPlanStudio(root);
    }

    function initFloorPlanStudio(root) {
        const svg = document.getElementById('weddingFloorPlanSvg');
        const issuesBox = document.getElementById('floorPlanIssues');
        const saveBtn = document.getElementById('saveFloorPlanBtn');
        const addTableBtn = document.getElementById('addFloorTableBtn');
        const exportPngBtn = document.getElementById('exportFloorPngBtn');
        const selectedTablePanel = document.getElementById('selectedTableEditor');
        const saveUrl = root.getAttribute('data-floorplan-url');

        if (!svg || !saveUrl) return;

        let plan = null;
        try {
            plan = JSON.parse(root.getAttribute('data-floorplan') || 'null');
        } catch (e) {
            plan = null;
        }
        if (!plan || !Array.isArray(plan.tables)) {
            plan = { room_width: 840, room_height: 480, stage: { x: 270, y: 20, width: 300, height: 64, label: 'Sân khấu chính' }, tables: [] };
        }

        let activeTableIdx = plan.tables.length > 0 ? 0 : -1;
        let draggingIdx = -1;

        function renderSvg() {
            const w = plan.room_width || 840;
            const h = plan.room_height || 480;
            svg.setAttribute('viewBox', '0 0 ' + w + ' ' + h);

            const st = plan.stage || { x: 270, y: 20, width: 300, height: 64, label: 'Sân khấu chính' };
            let inner =
                '<rect x="' + st.x + '" y="' + st.y + '" width="' + st.width + '" height="' + st.height + '" rx="12" fill="#85182A" />' +
                '<text x="' + (st.x + st.width / 2) + '" y="' + (st.y + st.height / 2 + 5) + '" text-anchor="middle" fill="#FAF5EB" font-size="15" font-weight="700">' + escapeHtml(st.label || 'Sân khấu') + '</text>';

            plan.tables.forEach((t, idx) => {
                const isActive = idx === activeTableIdx;
                const assigned = (t.guests || []).length;
                const cap = Number(t.capacity) || 10;
                const isOver = assigned > cap;
                const fill = isOver ? '#FDE2E4' : (isActive ? '#E9F2EE' : '#FFFFFF');
                const stroke = isOver ? '#85182A' : (isActive ? '#1E4D3B' : '#CFA860');

                inner +=
                    '<g class="mayhen_wedding_seating_table_group" data-table-idx="' + idx + '">' +
                    '<circle cx="' + t.x + '" cy="' + t.y + '" r="' + (t.radius || 38) + '" fill="' + fill + '" stroke="' + stroke + '" stroke-width="' + (isActive ? 3 : 2) + '" />' +
                    '<text x="' + t.x + '" y="' + (t.y - 4) + '" text-anchor="middle" fill="#1F1B18" font-size="12" font-weight="700">' + escapeHtml(t.label) + '</text>' +
                    '<text x="' + t.x + '" y="' + (t.y + 14) + '" text-anchor="middle" fill="#5F5750" font-size="11">' + assigned + '/' + cap + ' ghế</text>' +
                    '</g>';
            });

            svg.innerHTML = inner;
            renderSelectedTableEditor();
        }

        function renderSelectedTableEditor() {
            if (!selectedTablePanel) return;
            const t = plan.tables[activeTableIdx];
            if (!t) {
                selectedTablePanel.innerHTML = '<p style="font-size:13.5px;color:#5F5750;">Chạm vào một bàn trên sơ đồ để đổi tên, sức chứa hoặc gán danh sách khách mời.</p>';
                return;
            }
            selectedTablePanel.innerHTML =
                '<div class="mayhen_wedding_planner_row_2">' +
                '<div class="mayhen_wedding_planner_field"><label class="mayhen_wedding_planner_label">Tên bàn</label><input type="text" class="mayhen_wedding_planner_input" id="editTableLabel" value="' + escapeHtml(t.label) + '"></div>' +
                '<div class="mayhen_wedding_planner_field"><label class="mayhen_wedding_planner_label">Sức chứa (ghế)</label><input type="number" min="2" max="20" class="mayhen_wedding_planner_input" id="editTableCap" value="' + escapeHtml(t.capacity || 10) + '"></div>' +
                '</div>' +
                '<div class="mayhen_wedding_planner_field"><label class="mayhen_wedding_planner_label">Danh sách khách ngồi bàn này (mỗi khách 1 dòng)</label>' +
                '<textarea rows="4" class="mayhen_wedding_planner_textarea" id="editTableGuests">' + escapeHtml((t.guests || []).join('\n')) + '</textarea></div>' +
                '<div style="display:flex;gap:8px;flex-wrap:wrap;">' +
                '<button type="button" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--primary mayhen_wedding_planner_btn--sm" id="applyTableEditBtn">Cập nhật bàn</button>' +
                '<button type="button" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--outline mayhen_wedding_planner_btn--sm" id="removeTableBtn">Xóa bàn này</button>' +
                '</div>';

            document.getElementById('applyTableEditBtn')?.addEventListener('click', () => {
                t.label = document.getElementById('editTableLabel').value.trim() || t.label;
                t.capacity = Math.max(2, Number(document.getElementById('editTableCap').value) || 10);
                t.guests = document.getElementById('editTableGuests').value
                    .split('\n')
                    .map((s) => s.trim())
                    .filter(Boolean);
                renderSvg();
            });

            document.getElementById('removeTableBtn')?.addEventListener('click', () => {
                plan.tables.splice(activeTableIdx, 1);
                activeTableIdx = plan.tables.length > 0 ? 0 : -1;
                renderSvg();
            });
        }

        function getSvgCoords(ev) {
            const rect = svg.getBoundingClientRect();
            const scaleX = (plan.room_width || 840) / rect.width;
            const scaleY = (plan.room_height || 480) / rect.height;
            return {
                x: Math.round((ev.clientX - rect.left) * scaleX),
                y: Math.round((ev.clientY - rect.top) * scaleY),
            };
        }

        svg.addEventListener('pointerdown', (ev) => {
            const grp = ev.target.closest('[data-table-idx]');
            if (!grp) return;
            draggingIdx = Number(grp.getAttribute('data-table-idx'));
            activeTableIdx = draggingIdx;
            svg.setPointerCapture(ev.pointerId);
            renderSvg();
        });

        svg.addEventListener('pointermove', (ev) => {
            if (draggingIdx < 0 || !plan.tables[draggingIdx]) return;
            const pt = getSvgCoords(ev);
            const r = plan.tables[draggingIdx].radius || 38;
            plan.tables[draggingIdx].x = Math.max(r, Math.min((plan.room_width || 840) - r, Math.round(pt.x / 10) * 10));
            plan.tables[draggingIdx].y = Math.max(r + 90, Math.min((plan.room_height || 480) - r, Math.round(pt.y / 10) * 10));
            renderSvg();
        });

        svg.addEventListener('pointerup', () => {
            draggingIdx = -1;
        });

        addTableBtn?.addEventListener('click', () => {
            const idx = plan.tables.length + 1;
            plan.tables.push({
                id: 'T' + Date.now(),
                label: 'Bàn ' + idx,
                x: 160 + ((idx * 90) % 520),
                y: 260,
                radius: 38,
                capacity: 10,
                zone: 'Khách mời',
                guests: [],
            });
            activeTableIdx = plan.tables.length - 1;
            renderSvg();
        });

        saveBtn?.addEventListener('click', async () => {
            try {
                const res = await apiJson(saveUrl, 'POST', { floorplan: plan });
                const v = res.validation;
                if (issuesBox) {
                    if (v.issues && v.issues.length > 0) {
                        issuesBox.innerHTML = v.issues.map((it) =>
                            '<div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--warn">' + escapeHtml(it.message) + '</div>'
                        ).join('');
                    } else {
                        issuesBox.innerHTML = '<div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--info">Đã lưu sơ đồ thành công! ' + escapeHtml(v.total_tables) + ' bàn · ' + escapeHtml(v.total_assigned) + '/' + escapeHtml(v.total_capacity) + ' ghế — Không phát hiện chồng lấn hay trùng khách.</div>';
                    }
                }
            } catch (e) {
                alert(e.message);
            }
        });

        exportPngBtn?.addEventListener('click', () => {
            const serializer = new XMLSerializer();
            const svgStr = serializer.serializeToString(svg);
            const blob = new Blob([svgStr], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = plan.room_width || 840;
                canvas.height = plan.room_height || 480;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#FAF5EB';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0);
                URL.revokeObjectURL(url);
                const a = document.createElement('a');
                a.download = 'so-do-ban-tiec-mayhen.png';
                a.href = canvas.toDataURL('image/png');
                a.click();
            };
            img.src = url;
        });

        renderSvg();
    }

    document.addEventListener('DOMContentLoaded', () => {
        initDateFinder();
        initPlannerLanding();
        initWorkspace();
    });
})();
