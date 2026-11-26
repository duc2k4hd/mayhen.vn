/* ==========================================================================
   MÂY HẸN – VẠN NIÊN LỊCH (/lich-am)
   100% Pure Vanilla JavaScript — Zero External Dependencies
   - 3D Bloc Flip Navigation & Interactive Month Grid
   - Procedural 3D-Shaded Astronomical Moon Phase Sphere (Canvas 2D)
   - Interactive 12-Hour Traditional Astronomical Clock (SVG)
   - Strict 2-Way Solar<->Lunar Converter, Date Finder, Lunar Anniversary & AI Q&A
   ========================================================================== */

(function () {
    'use strict';

    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ----------------------------------------------------------------------
       1. Astronomical 3D-Shaded Moon Phase Sphere Renderer (Canvas 2D)
       ---------------------------------------------------------------------- */
    function renderMoonCanvas(canvas, cycleFraction) {
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const size = canvas.width;
        const cx = size / 2;
        const cy = size / 2;
        const r = size * 0.44;
        ctx.clearRect(0, 0, size, size);

        // Soft ambient celestial halo
        const halo = ctx.createRadialGradient(cx, cy, r * 0.85, cx, cy, r * 1.15);
        halo.addColorStop(0, 'rgba(233, 210, 160, 0.22)');
        halo.addColorStop(1, 'rgba(233, 210, 160, 0)');
        ctx.fillStyle = halo;
        ctx.beginPath();
        ctx.arc(cx, cy, r * 1.15, 0, Math.PI * 2);
        ctx.fill();

        // Clip to Moon disc
        ctx.save();
        ctx.beginPath();
        ctx.arc(cx, cy, r, 0, Math.PI * 2);
        ctx.clip();

        // Lit lunar parchment-silver sphere base
        const baseGrad = ctx.createRadialGradient(cx - r * 0.2, cy - r * 0.2, r * 0.1, cx, cy, r);
        baseGrad.addColorStop(0, '#FBF4DF');
        baseGrad.addColorStop(0.65, '#E3D5B4');
        baseGrad.addColorStop(1, '#AE9C78');
        ctx.fillStyle = baseGrad;
        ctx.fillRect(cx - r, cy - r, r * 2, r * 2);

        // Realistic deterministic lunar maria (dark volcanic plains)
        const maria = [
            [-0.25, -0.22, 0.28], [0.18, -0.18, 0.24], [0.32, 0.08, 0.2],
            [-0.12, 0.22, 0.22], [0.05, -0.42, 0.16], [-0.42, 0.05, 0.18],
        ];
        ctx.fillStyle = 'rgba(84, 78, 68, 0.24)';
        maria.forEach(([mx, my, mr]) => {
            ctx.beginPath();
            ctx.arc(cx + mx * r, cy + my * r, mr * r, 0, Math.PI * 2);
            ctx.fill();
        });

        // Accurate phase terminator (0 = New Moon, 0.25 = First Quarter right lit, 0.5 = Full Moon, 0.75 = Last Quarter left lit)
        const f = ((Number(cycleFraction) % 1) + 1) % 1;
        const phaseRad = f * Math.PI * 2;
        const cosP = Math.cos(phaseRad);
        const waxing = f < 0.5;

        ctx.fillStyle = 'rgba(13, 22, 36, 0.91)';
        ctx.beginPath();
        if (waxing) {
            // Left hemisphere dark
            ctx.arc(cx, cy, r + 1, Math.PI / 2, Math.PI * 1.5, false);
            ctx.ellipse(cx, cy, Math.abs(cosP) * r, r + 1, 0, -Math.PI / 2, Math.PI / 2, cosP < 0);
        } else {
            // Right hemisphere dark
            ctx.arc(cx, cy, r + 1, -Math.PI / 2, Math.PI / 2, false);
            ctx.ellipse(cx, cy, Math.abs(cosP) * r, r + 1, 0, Math.PI / 2, Math.PI * 1.5, cosP < 0);
        }
        ctx.closePath();
        ctx.fill();

        // Subtle limb shadow for 3D spherical depth
        const limb = ctx.createRadialGradient(cx, cy, r * 0.72, cx, cy, r);
        limb.addColorStop(0, 'rgba(0,0,0,0)');
        limb.addColorStop(1, 'rgba(0,0,0,0.34)');
        ctx.fillStyle = limb;
        ctx.fillRect(cx - r, cy - r, r * 2, r * 2);

        ctx.restore();
    }

    /* ----------------------------------------------------------------------
       2. Interactive 12-Hour Traditional Astronomical Clock (SVG)
       ---------------------------------------------------------------------- */
    function renderAstronomicalClock(container, hours) {
        if (!container || !Array.isArray(hours) || hours.length !== 12) return;

        // Current Vietnam hour (UTC+7)
        const nowUtc = new Date();
        const vnHour = (nowUtc.getUTCHours() + 7) % 24;
        const vnMin = nowUtc.getUTCMinutes();
        const currentBranchIdx = Math.floor(((vnHour + 1) % 24) / 2);
        const handAngleDeg = ((vnHour + vnMin / 60) / 24) * 360;

        let segments = '';
        hours.forEach((h, idx) => {
            const centerDeg = idx * 30 - 90; // Tý (0) ở đỉnh 12 giờ (00:00)
            const rad = (centerDeg * Math.PI) / 180;
            const lx = 140 + Math.cos(rad) * 102;
            const ly = 140 + Math.sin(rad) * 102;
            const dotX = 140 + Math.cos(rad) * 72;
            const dotY = 140 + Math.sin(rad) * 72;

            const isHd = Boolean(h.is_hoang_dao);
            const isNow = idx === currentBranchIdx;
            const fillCol = isNow ? '#85182A' : (isHd ? '#CFA860' : '#CBD5E1');
            const textCol = isNow ? '#85182A' : (isHd ? '#1E4D3B' : '#57534E');

            segments += `
                <g class="mayhen_cal_clock_branch" data-branch-idx="${idx}" style="cursor:pointer;">
                    <circle cx="${dotX.toFixed(1)}" cy="${dotY.toFixed(1)}" r="${isNow ? 7 : 5}" fill="${fillCol}" stroke="#FFFDF8" stroke-width="1.2"/>
                    <text x="${lx.toFixed(1)}" y="${(ly + 4).toFixed(1)}" text-anchor="middle" font-size="12" font-weight="${isHd || isNow ? '700' : '500'}" fill="${textCol}" font-family="Segoe UI, Arial, sans-serif">${esc(h.branch)}</text>
                </g>
            `;
        });

        container.innerHTML = `
            <svg viewBox="0 0 280 280" class="mayhen_calendar_clock_svg" role="img" aria-label="Đồng hồ 12 thời thần">
                <circle cx="140" cy="140" r="128" fill="#FAF5EB" stroke="#CFA860" stroke-width="2"/>
                <circle cx="140" cy="140" r="88" fill="#FFFDF8" stroke="rgba(41,37,36,0.14)" stroke-width="1"/>
                <circle cx="140" cy="140" r="54" fill="none" stroke="rgba(207,168,96,0.4)" stroke-width="1" stroke-dasharray="3 3"/>
                ${segments}
                <g transform="rotate(${handAngleDeg.toFixed(1)} 140 140)">
                    <line x1="140" y1="140" x2="140" y2="66" stroke="#85182A" stroke-width="2.4" stroke-linecap="round"/>
                    <circle cx="140" cy="140" r="5" fill="#85182A"/>
                </g>
                <text x="140" y="162" text-anchor="middle" font-size="10.5" fill="#57534E" font-family="Segoe UI, Arial, sans-serif">Giờ VN · ${String(vnHour).padStart(2, '0')}:${String(vnMin).padStart(2, '0')}</text>
            </svg>
        `;
    }

    /* ----------------------------------------------------------------------
       3. Main Calendar App Controller (/lich-am)
       ---------------------------------------------------------------------- */
    function initCalendarPage() {
        const cfgEl = document.getElementById('mayhenCalendarConfig');
        if (!cfgEl) return;
        const cfg = JSON.parse(cfgEl.textContent || '{}');

        let currentIso = cfg.initialBundle?.day?.solar?.iso || cfg.todayIso;
        let currentCity = cfg.initialBundle?.sun_times?.city_key || 'ha-noi';
        let currentMode = cfg.initialBundle?.day?.calendar_mode || 'vn_modern_utc7';
        let activeMonthYear = Number(cfg.monthGrid?.year || 2026);
        let activeMonthNum = Number(cfg.monthGrid?.month || 1);

        const moonCanvas = document.getElementById('calMoonCanvas');
        const clockWrap = document.getElementById('calClockWrap');
        const blocCard = document.getElementById('calBlocCard');

        // Initial render of Moon 3D & 12-Hour Clock
        renderMoonCanvas(moonCanvas, cfg.initialBundle?.moon_phase?.cycle_fraction ?? 0.5);
        renderAstronomicalClock(clockWrap, cfg.initialBundle?.almanac?.hours || []);

        function shiftIsoDate(iso, deltaDays) {
            const [y, m, d] = iso.split('-').map(Number);
            const dt = new Date(Date.UTC(y, m - 1, d + deltaDays));
            return `${dt.getUTCFullYear()}-${String(dt.getUTCMonth() + 1).padStart(2, '0')}-${String(dt.getUTCDate()).padStart(2, '0')}`;
        }

        function updateDayUi(bundle, explain) {
            const d = bundle.day;
            const alm = bundle.almanac;
            const moon = bundle.moon_phase;
            const sun = bundle.sun_times;
            const term = bundle.solar_term;

            currentIso = d.solar.iso;
            const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

            setText('calBlocMonthLabel', `THÁNG ${String(d.solar.month).padStart(2, '0')} · NĂM ${d.solar.year}`);
            setText('calWeekday', d.solar.weekday_name);
            setText('calBigDay', String(d.solar.day));
            setText('calSolarFull', `Dương lịch: ${d.solar.label}`);
            setText('calLunarBig', `${String(d.lunar.day).padStart(2, '0')}/${String(d.lunar.month).padStart(2, '0')}${d.lunar.is_leap_month ? ' (Nhuận)' : ''} Âm Lịch`);
            setText('calLunarSub', `Năm ${d.can_chi.year.label} (${d.can_chi.year.zodiac}) · ${d.lunar.month_size_label}`);
            setText('calCanChiDay', d.can_chi.day.label);
            setText('calNapAmDay', `${d.can_chi.day.nap_am.name} (${d.can_chi.day.nap_am.element})`);
            setText('calCanChiMonth', d.can_chi.month.label);
            setText('calSolarTermName', `${term.current.name} (${term.sun_longitude_deg}°)`);
            setText('calBlocFooterTerm', `Tiết ${term.current.name} · Còn ${term.days_until_next} ngày tới ${term.next.name}`);

            const qualTag = document.getElementById('calQualityTag');
            if (qualTag) {
                qualTag.className = `mayhen_calendar_tag mayhen_calendar_tag--${alm.day_quality.type}`;
                qualTag.textContent = `✦ ${alm.day_quality.label} (${alm.day_quality.god_name})`;
            }

            // Moon & Sun
            renderMoonCanvas(moonCanvas, moon.cycle_fraction);
            setText('calMoonName', moon.phase_name);
            setText('calMoonMeta', `Chiếu sáng ${moon.illumination_pct}% · Tuổi Trăng ${moon.age_days} ngày · ${moon.trend_label}`);
            setText('calMoonNext', `Trăng tròn kế tiếp: ${moon.next_full_moon_vn} · Trăng non: ${moon.next_new_moon_vn}`);
            setText('calSunRise', sun.sunrise);
            setText('calSunNoon', sun.solar_noon);
            setText('calSunSet', sun.sunset);

            // Clock & Hours
            renderAstronomicalClock(clockWrap, alm.hours);
            const pillsEl = document.getElementById('calHoursPills');
            if (pillsEl) {
                pillsEl.innerHTML = alm.hours.map((h) =>
                    `<div class="mayhen_calendar_hour_pill ${h.is_hoang_dao ? 'is-hoang-dao' : ''}"><strong>${esc(h.branch)}</strong> (${esc(h.range)})<br><span style="font-size:10px;color:#57534E;">${esc(h.god_name)}</span></div>`
                ).join('');
            }

            // Almanac 4 boxes
            setText('calAlmTruc', `Trực ${alm.truc.name}`);
            setText('calAlmTrucDesc', alm.truc.desc);
            setText('calAlmMansion', `Tú ${alm.mansion.name} (${alm.mansion.element})`);
            setText('calAlmMansionDesc', alm.mansion.nature);
            setText('calAlmLucDieu', alm.luc_dieu.name);
            setText('calAlmLucDieuDesc', alm.luc_dieu.desc);
            setText('calAlmDirections', `Hỷ Thần: ${alm.directions.hy_than} · Tài Thần: ${alm.directions.tai_than}`);
            setText('calAlmClash', `Tuổi xung theo Chi ngày: ${alm.clash_branch}`);

            // AI summary
            if (explain) {
                setText('calExplainSummary', explain.summary || '');
                setText('calExplainTraditional', explain.traditional_note || '');
            }

            // Highlight selected cell in Month Grid
            document.querySelectorAll('.mayhen_calendar_cell[data-iso]').forEach((c) => {
                c.classList.toggle('is-selected', c.getAttribute('data-iso') === currentIso);
            });
        }

        async function loadDay(iso, flipDir = null) {
            if (blocCard && flipDir) {
                blocCard.classList.add(flipDir === 'next' ? 'is-flipping-next' : 'is-flipping-prev');
            }
            try {
                const url = `${cfg.endpoints.apiDay}?date=${encodeURIComponent(iso)}&city=${encodeURIComponent(currentCity)}&mode=${encodeURIComponent(currentMode)}`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (res.ok && data.success) {
                    updateDayUi(data.bundle, data.explain);
                    const [y, m] = iso.split('-').map(Number);
                    if (y !== activeMonthYear || m !== activeMonthNum) {
                        loadMonth(y, m);
                    }
                }
            } catch (e) {
                // Silent network fallback
            } finally {
                if (blocCard) {
                    setTimeout(() => blocCard.classList.remove('is-flipping-next', 'is-flipping-prev'), 220);
                }
            }
        }

        async function loadMonth(year, month) {
            try {
                const url = `${cfg.endpoints.apiMonth}?year=${year}&month=${month}&mode=${encodeURIComponent(currentMode)}`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (!res.ok || !data.success) return;

                const mg = data.month_grid;
                activeMonthYear = mg.year;
                activeMonthNum = mg.month;
                const labelEl = document.getElementById('calMonthLabel');
                if (labelEl) labelEl.textContent = mg.label;

                const gridEl = document.getElementById('calMonthGrid');
                if (gridEl) {
                    gridEl.innerHTML = mg.cells.map((cell) => {
                        if (!cell) return '<div class="mayhen_calendar_cell is-empty"></div>';
                        const cls = [
                            'mayhen_calendar_cell',
                            cell.is_weekend ? 'is-weekend' : '',
                            cell.iso === cfg.todayIso ? 'is-today' : '',
                            cell.iso === currentIso ? 'is-selected' : '',
                        ].filter(Boolean).join(' ');
                        return `
                            <button type="button" class="${cls}" data-iso="${cell.iso}">
                                ${cell.is_hoang_dao ? '<span class="mayhen_calendar_cell_dot" title="Ngày Hoàng Đạo"></span>' : ''}
                                <span class="mayhen_calendar_cell_solar">${cell.solar_day}</span>
                                <span class="mayhen_calendar_cell_lunar">${esc(cell.lunar_short)}</span>
                                ${cell.holiday_name ? `<span class="mayhen_calendar_cell_hol">${esc(cell.holiday_name)}</span>` : ''}
                            </button>
                        `;
                    }).join('');
                    bindMonthCells();
                }
            } catch (e) {}
        }

        function bindMonthCells() {
            document.querySelectorAll('.mayhen_calendar_cell[data-iso]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const iso = btn.getAttribute('data-iso');
                    if (iso) loadDay(iso, iso > currentIso ? 'next' : 'prev');
                });
            });
        }
        bindMonthCells();

        // Bloc Day Navigation Buttons
        document.getElementById('calPrevDayBtn')?.addEventListener('click', () => loadDay(shiftIsoDate(currentIso, -1), 'prev'));
        document.getElementById('calNextDayBtn')?.addEventListener('click', () => loadDay(shiftIsoDate(currentIso, 1), 'next'));
        document.getElementById('calTodayBtn')?.addEventListener('click', () => loadDay(cfg.todayIso, 'next'));

        // Month Navigation Buttons
        document.getElementById('calPrevMonthBtn')?.addEventListener('click', () => {
            let m = activeMonthNum - 1, y = activeMonthYear;
            if (m < 1) { m = 12; y--; }
            loadMonth(y, m);
        });
        document.getElementById('calNextMonthBtn')?.addEventListener('click', () => {
            let m = activeMonthNum + 1, y = activeMonthYear;
            if (m > 12) { m = 1; y++; }
            loadMonth(y, m);
        });

        // City selector for Sunrise/Sunset
        document.getElementById('calCitySelect')?.addEventListener('change', (e) => {
            currentCity = e.target.value;
            loadDay(currentIso, null);
        });

        // Utility Tabs
        const tabBtns = document.querySelectorAll('[data-cal-tab]');
        const tabPanels = document.querySelectorAll('[data-cal-panel]');
        tabBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const key = btn.getAttribute('data-cal-tab');
                tabBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
                tabPanels.forEach((p) => p.classList.toggle('is-active', p.getAttribute('data-cal-panel') === key));
            });
        });

        // Tool 1: 2-Way Solar <-> Lunar Converter
        const convForm = document.getElementById('calConvertForm');
        const convOut = document.getElementById('calConvertResult');
        if (convForm && convOut) {
            convForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const fd = new FormData(convForm);
                const payload = {
                    direction: fd.get('direction'),
                    day: Number(fd.get('day')),
                    month: Number(fd.get('month')),
                    year: Number(fd.get('year')),
                    is_leap_month: fd.get('is_leap_month') === '1',
                    mode: fd.get('mode') || 'vn_modern_utc7',
                };
                convOut.style.display = 'block';
                convOut.textContent = 'Đang quy đổi chuẩn lịch Việt Nam...';
                try {
                    const res = await fetch(cfg.endpoints.apiConvert, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        convOut.innerHTML = `<strong style="color:#85182A;">${esc(data.message || 'Ngày nhập không hợp lệ.')}</strong>`;
                        return;
                    }
                    const r = data.result;
                    convOut.innerHTML = `
                        <div><strong>Dương lịch:</strong> ${esc(r.solar.weekday_name)}, ${esc(r.solar.label)}</div>
                        <div><strong>Âm lịch Việt Nam:</strong> <span style="color:#85182A;font-weight:700;">${esc(r.lunar.label_full)}</span> (${esc(r.lunar.month_size_label)})</div>
                        <div><strong>Can Chi:</strong> Ngày ${esc(r.can_chi.day.label)} (${esc(r.can_chi.day.nap_am.name)}) · Tháng ${esc(r.can_chi.month.label)} · Năm ${esc(r.can_chi.year.label)}</div>
                        <div><strong>Tiết khí:</strong> ${esc(data.solar_term?.name || '')} · <strong>${esc(data.almanac?.day_quality?.label || '')}</strong> (${esc(data.almanac?.day_quality?.god_name || '')})</div>
                        ${r.warning ? `<div style="margin-top:6px;color:#85182A;font-size:12.5px;">⚠ ${esc(r.warning)}</div>` : ''}
                    `;
                } catch (err) {
                    convOut.textContent = 'Kết nối tạm gián đoạn.';
                }
            });
        }

        // Tool 2: Activity Date Finder (Wedding / Khai trương / Nhập trạch)
        const findForm = document.getElementById('calFindDatesForm');
        const findOut = document.getElementById('calFindDatesResult');
        if (findForm && findOut) {
            findForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const fd = new FormData(findForm);
                findOut.style.display = 'block';
                findOut.textContent = 'Đang quét các ngày phù hợp theo điều kiện thực tế...';
                try {
                    const res = await fetch(cfg.endpoints.apiFindDates, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        body: JSON.stringify({
                            activity: fd.get('activity'),
                            from_date: fd.get('from_date'),
                            to_date: fd.get('to_date'),
                            weekends_only: fd.get('weekends_only') === '1',
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        findOut.innerHTML = `<strong style="color:#85182A;">${esc(data.message || 'Không thể tìm ngày.')}</strong>`;
                        return;
                    }
                    if (!data.dates || !data.dates.length) {
                        findOut.textContent = 'Không có ngày nào khớp bộ lọc trong khoảng thời gian đã chọn. Bạn hãy mở rộng khoảng ngày hoặc bỏ lọc chỉ cuối tuần.';
                        return;
                    }
                    findOut.innerHTML = data.dates.map((item) => `
                        <div style="padding:10px 0;border-bottom:1px solid rgba(41,37,36,0.1);">
                            <strong>${esc(item.weekday)}, ${esc(item.solar_label)}</strong> (Âm lịch: ${esc(item.lunar_label)} · Ngày ${esc(item.day_can_chi)})
                            <span style="margin-left:8px;font-size:12px;color:#1E4D3B;font-weight:700;">${esc(item.evaluation.status_label)}</span>
                            <div style="font-size:12.5px;color:#57534E;margin-top:3px;">${esc(item.day_quality)} · ${esc(item.truc)} · Giờ đẹp: ${esc(item.hoang_dao_hours.join(', '))}</div>
                        </div>
                    `).join('');
                } catch (err) {
                    findOut.textContent = 'Kết nối tạm gián đoạn.';
                }
            });
        }

        // Tool 3: Lunar Anniversary / Ngày Giỗ Calculator
        const annForm = document.getElementById('calAnniversaryForm');
        const annOut = document.getElementById('calAnniversaryResult');
        if (annForm && annOut) {
            annForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const fd = new FormData(annForm);
                annOut.style.display = 'block';
                annOut.textContent = 'Đang tính bảng ngày giỗ / kỷ niệm nhiều năm...';
                try {
                    const res = await fetch(cfg.endpoints.apiAnniversary, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        body: JSON.stringify({
                            lunar_day: Number(fd.get('lunar_day')),
                            lunar_month: Number(fd.get('lunar_month')),
                            years_count: Number(fd.get('years_count') || 6),
                            short_month_rule: fd.get('short_month_rule'),
                            leap_month_rule: fd.get('leap_month_rule'),
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        annOut.textContent = data.message || 'Không thể tính ngày.';
                        return;
                    }
                    annOut.innerHTML = (data.rows || []).map((r) => `
                        <div style="padding:8px 0;border-bottom:1px solid rgba(41,37,36,0.08);">
                            <strong>Năm ${r.lunar_year} (${esc(r.year_can_chi)}):</strong> ${esc(r.weekday)}, <strong>${esc(r.solar_date)}</strong> (${esc(r.lunar_label)})
                            ${r.note ? `<div style="font-size:12px;color:#85182A;">${esc(r.note)}</div>` : ''}
                        </div>
                    `).join('');
                } catch (err) {
                    annOut.textContent = 'Kết nối tạm gián đoạn.';
                }
            });
        }

        // Tool 4: Grounded AI Q&A
        const aiForm = document.getElementById('calAiForm');
        const aiOut = document.getElementById('calAiResult');
        if (aiForm && aiOut) {
            aiForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const q = aiForm.querySelector('[name="question"]')?.value.trim() || '';
                if (q.length < 3) return;
                aiOut.style.display = 'block';
                aiOut.textContent = 'Đang đối chiếu dữ liệu lịch pháp và trả lời...';
                try {
                    const res = await fetch(cfg.endpoints.apiAskAi, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        body: JSON.stringify({ date: currentIso, question: q }),
                    });
                    const data = await res.json();
                    aiOut.textContent = data.answer || data.message || 'Không thể trả lời lúc này.';
                } catch (err) {
                    aiOut.textContent = 'Kết nối tạm gián đoạn.';
                }
            });
        }
    }

    function initMyEventsPage() {
        const form = document.getElementById('calNewEventForm');
        if (!form) return;
        const statusEl = document.getElementById('calNewEventStatus');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(form);
            if (statusEl) statusEl.textContent = 'Đang lưu sự kiện gia đình...';
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        title: fd.get('title'),
                        event_type: fd.get('event_type'),
                        calendar_type: fd.get('calendar_type'),
                        event_day: Number(fd.get('event_day')),
                        event_month: Number(fd.get('event_month')),
                        short_month_rule: fd.get('short_month_rule'),
                        leap_month_rule: fd.get('leap_month_rule'),
                        notes: fd.get('notes'),
                    }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.reload();
                } else if (statusEl) {
                    statusEl.textContent = data.message || 'Không thể lưu sự kiện.';
                }
            } catch (err) {
                if (statusEl) statusEl.textContent = 'Kết nối tạm gián đoạn.';
            }
        });

        document.querySelectorAll('[data-delete-event-url]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                if (!confirm('Xóa sự kiện gia đình này?')) return;
                const res = await fetch(btn.getAttribute('data-delete-event-url'), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                });
                if (res.ok) window.location.reload();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCalendarPage();
        initMyEventsPage();
    });
})();
