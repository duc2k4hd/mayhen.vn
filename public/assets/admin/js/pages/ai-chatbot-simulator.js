/**
 * Mây Hẹn Admin — Phòng Kiểm Thử & Mô Phỏng Luồng AI Trực Tiếp (Vanilla ES Module)
 * - Gọi API thực thi thật 7 bước (AiChatPipelineTracerService), hiển thị chính xác PASS / FAIL ở từng bước.
 * - Vẽ động ống dẫn năng lượng SVG giữa các thẻ bước với quả cầu ánh sáng + đuôi sao chổi + sóng tiếp nhận.
 * - Hỗ trợ viết kịch bản kiểm thử, tự động lưu (auto-save 800ms), chạy đơn lẻ và chạy toàn bộ bộ test.
 * - 100% XSS-safe (chỉ dùng textContent & DOM API, không chèn innerHTML dữ liệu người dùng).
 */

const SVG_NS = 'http://www.w3.org/2000/svg';

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') || '' : '';
}

function sleep(ms) {
    return new Promise((resolve) => window.setTimeout(resolve, ms));
}

function easeInOutCubic(t) {
    return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

/**
 * Chuyển chuỗi có markdown link `[Nhãn](/duong-dan)` và `**in đậm**` thành DOM node an toàn 100% (chống XSS).
 */
function appendFormattedText(container, text) {
    const tokenRegex = /(\[([^\]]+)\]\(([^)\s]+)\)|\*\*([^*]+)\*\*)/g;
    let lastIndex = 0;
    let match;

    while ((match = tokenRegex.exec(text)) !== null) {
        if (match.index > lastIndex) {
            container.appendChild(document.createTextNode(text.slice(lastIndex, match.index)));
        }

        if (match[2] && match[3]) {
            const href = match[3].trim();
            if (href.startsWith('/') || /^https:\/\/(?:www\.)?mayhen\.vn(\/|$)/i.test(href)) {
                const a = document.createElement('a');
                a.className = 'mayhen_admin_ai_sim_msg_link';
                a.href = href;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
                a.textContent = match[2];
                container.appendChild(a);
            } else {
                container.appendChild(document.createTextNode(match[2]));
            }
        } else if (match[4]) {
            const strong = document.createElement('strong');
            strong.textContent = match[4];
            container.appendChild(strong);
        }

        lastIndex = tokenRegex.lastIndex;
    }

    if (lastIndex < text.length) {
        container.appendChild(document.createTextNode(text.slice(lastIndex)));
    }
}

/**
 * Động cơ vẽ và truyền năng lượng SVG giữa 7 thẻ bước (1→2→3→4→5→6→7).
 */
class EnergyConduitVisualizer {
    constructor(areaEl, svgEl, tracksGroup, streamsGroup, orbsGroup, nodeMap) {
        this.areaEl = areaEl;
        this.svgEl = svgEl;
        this.tracksGroup = tracksGroup;
        this.streamsGroup = streamsGroup;
        this.orbsGroup = orbsGroup;
        this.nodeMap = nodeMap;
        this.segments = {}; // key: "1-2" => { track, stream }
        this.activeOrbRafs = new Set();

        this.rebuildGeometry();
        if (typeof ResizeObserver === 'function') {
            this.observer = new ResizeObserver(() => this.rebuildGeometry());
            this.observer.observe(this.areaEl);
        } else {
            window.addEventListener('resize', () => this.rebuildGeometry(), { passive: true });
        }
    }

    buildPathData(fromRect, toRect, areaRect, isRowWrap) {
        const x1 = fromRect.left - areaRect.left;
        const y1 = fromRect.top - areaRect.top;
        const w1 = fromRect.width;
        const h1 = fromRect.height;

        const x2 = toRect.left - areaRect.left;
        const y2 = toRect.top - areaRect.top;
        const w2 = toRect.width;
        const h2 = toRect.height;

        if (!isRowWrap) {
            // Nối ngang mượt từ cạnh phải thẻ trước sang cạnh trái thẻ kế tiếp
            const sx = x1 + w1;
            const sy = y1 + h1 * 0.5;
            const ex = x2;
            const ey = y2 + h2 * 0.5;
            const mx = (sx + ex) * 0.5;
            return `M ${sx.toFixed(1)} ${sy.toFixed(1)} C ${mx.toFixed(1)} ${sy.toFixed(1)}, ${mx.toFixed(1)} ${ey.toFixed(1)}, ${ex.toFixed(1)} ${ey.toFixed(1)}`;
        }

        // Đường truyền chữ S mềm mại từ đáy Bước 4 (hàng trên bên phải) xuống đỉnh Bước 5 (hàng dưới bên trái)
        const sx = x1 + w1 * 0.5;
        const sy = y1 + h1;
        const ex = x2 + w2 * 0.5;
        const ey = y2;
        const midY = (sy + ey) * 0.5;
        return `M ${sx.toFixed(1)} ${sy.toFixed(1)} C ${sx.toFixed(1)} ${(midY + 6).toFixed(1)}, ${ex.toFixed(1)} ${(midY - 6).toFixed(1)}, ${ex.toFixed(1)} ${ey.toFixed(1)}`;
    }

    rebuildGeometry() {
        const areaRect = this.areaEl.getBoundingClientRect();
        if (areaRect.width < 10 || areaRect.height < 10) return;

        this.svgEl.setAttribute('viewBox', `0 0 ${areaRect.width.toFixed(1)} ${areaRect.height.toFixed(1)}`);

        const pairs = [
            [1, 2, false],
            [2, 3, false],
            [3, 4, false],
            [4, 5, true],
            [5, 6, false],
            [6, 7, false],
        ];

        const prevClasses = {};
        Object.keys(this.segments).forEach((key) => {
            prevClasses[key] = this.segments[key].stream.getAttribute('class') || 'mayhen_admin_ai_sim_conduit_stream';
        });

        this.tracksGroup.textContent = '';
        this.streamsGroup.textContent = '';
        this.segments = {};

        for (const [fromStep, toStep, isWrap] of pairs) {
            const fromEl = this.nodeMap.get(fromStep);
            const toEl = this.nodeMap.get(toStep);
            if (!fromEl || !toEl) continue;

            const d = this.buildPathData(fromEl.getBoundingClientRect(), toEl.getBoundingClientRect(), areaRect, isWrap);
            const key = `${fromStep}-${toStep}`;

            const track = document.createElementNS(SVG_NS, 'path');
            track.setAttribute('d', d);
            track.setAttribute('class', 'mayhen_admin_ai_sim_conduit_track');
            this.tracksGroup.appendChild(track);

            const stream = document.createElementNS(SVG_NS, 'path');
            stream.setAttribute('d', d);
            stream.setAttribute('class', prevClasses[key] || 'mayhen_admin_ai_sim_conduit_stream');
            this.streamsGroup.appendChild(stream);

            this.segments[key] = { track, stream };
        }
    }

    resetAll() {
        this.activeOrbRafs.forEach((id) => cancelAnimationFrame(id));
        this.activeOrbRafs.clear();
        this.orbsGroup.textContent = '';
        Object.values(this.segments).forEach(({ stream }) => {
            stream.setAttribute('class', 'mayhen_admin_ai_sim_conduit_stream');
        });
    }

    setSegmentState(fromStep, toStep, mode) {
        const seg = this.segments[`${fromStep}-${toStep}`];
        if (!seg) return;
        const clsMap = {
            gold: 'mayhen_admin_ai_sim_conduit_stream is_flowing_gold',
            ok: 'mayhen_admin_ai_sim_conduit_stream is_flowing_ok',
            fail: 'mayhen_admin_ai_sim_conduit_stream is_flowing_fail',
            skipped: 'mayhen_admin_ai_sim_conduit_stream is_skipped',
            idle: 'mayhen_admin_ai_sim_conduit_stream',
        };
        seg.stream.setAttribute('class', clsMap[mode] || clsMap.idle);
    }

    /**
     * Bắn luồng năng lượng (quả cầu sáng + đuôi sao chổi 3 tầng) chạy dọc đường cong từ Bước i → Bước i+1.
     */
    pulseEnergy(fromStep, toStep, tone = 'gold', durationMs = 460) {
        return new Promise((resolve) => {
            const seg = this.segments[`${fromStep}-${toStep}`];
            if (!seg) {
                resolve();
                return;
            }

            const totalLen = seg.stream.getTotalLength();
            if (!totalLen || totalLen < 4) {
                resolve();
                return;
            }

            const palette =
                tone === 'fail'
                    ? { core: '#FFF0F2', mid: '#E36B7C', halo: 'rgba(180,59,76,0.45)' }
                    : tone === 'ok'
                        ? { core: '#F0FFF9', mid: '#3DB893', halo: 'rgba(30,123,97,0.4)' }
                        : { core: '#FFFDF5', mid: '#E2B466', halo: 'rgba(194,157,107,0.48)' };

            this.setSegmentState(fromStep, toStep, tone === 'fail' ? 'fail' : 'gold');

            const g = document.createElementNS(SVG_NS, 'g');
            g.setAttribute('filter', 'url(#mayhenSimEnergyGlow)');

            const trail2 = document.createElementNS(SVG_NS, 'circle');
            trail2.setAttribute('r', '3.5');
            trail2.setAttribute('fill', palette.mid);
            trail2.setAttribute('opacity', '0.45');

            const trail1 = document.createElementNS(SVG_NS, 'circle');
            trail1.setAttribute('r', '4.8');
            trail1.setAttribute('fill', palette.mid);
            trail1.setAttribute('opacity', '0.72');

            const halo = document.createElementNS(SVG_NS, 'circle');
            halo.setAttribute('r', '10.5');
            halo.setAttribute('fill', palette.halo);

            const orb = document.createElementNS(SVG_NS, 'circle');
            orb.setAttribute('r', '5.5');
            orb.setAttribute('fill', palette.core);
            orb.setAttribute('stroke', palette.mid);
            orb.setAttribute('stroke-width', '2');

            g.appendChild(trail2);
            g.appendChild(trail1);
            g.appendChild(halo);
            g.appendChild(orb);
            this.orbsGroup.appendChild(g);

            const startTs = performance.now();
            const tick = (now) => {
                const rawP = Math.min(1, (now - startTs) / Math.max(120, durationMs));
                const eased = easeInOutCubic(rawP);

                const pHead = seg.stream.getPointAtLength(totalLen * eased);
                const pT1 = seg.stream.getPointAtLength(Math.max(0, totalLen * eased - 11));
                const pT2 = seg.stream.getPointAtLength(Math.max(0, totalLen * eased - 22));

                halo.setAttribute('cx', pHead.x.toFixed(1));
                halo.setAttribute('cy', pHead.y.toFixed(1));
                orb.setAttribute('cx', pHead.x.toFixed(1));
                orb.setAttribute('cy', pHead.y.toFixed(1));

                trail1.setAttribute('cx', pT1.x.toFixed(1));
                trail1.setAttribute('cy', pT1.y.toFixed(1));
                trail2.setAttribute('cx', pT2.x.toFixed(1));
                trail2.setAttribute('cy', pT2.y.toFixed(1));

                if (rawP < 1) {
                    const id = requestAnimationFrame(tick);
                    this.activeOrbRafs.add(id);
                } else {
                    g.remove();
                    this.setSegmentState(fromStep, toStep, tone);
                    resolve();
                }
            };

            const id = requestAnimationFrame(tick);
            this.activeOrbRafs.add(id);
        });
    }
}

const initAdminAiSimulator = () => {
    const root = document.getElementById('mayhenAdminAiSimulator');
    if (!root) return;

    const urls = {
        run: root.dataset.runUrl || '',
        reset: root.dataset.resetUrl || '',
        storeScenario: root.dataset.storeScenarioUrl || '',
        updateScenarioTpl: root.dataset.updateScenarioTemplate || '',
        deleteScenarioTpl: root.dataset.deleteScenarioTemplate || '',
    };

    const messagesEl = document.getElementById('mayhenSimMessages');
    const chatForm = document.getElementById('mayhenSimChatForm');
    const chatInput = document.getElementById('mayhenSimInput');
    const sendBtn = document.getElementById('mayhenSimSendBtn');
    const replayBtn = document.getElementById('mayhenSimReplayBtn');
    const resetBtn = document.getElementById('mayhenSimResetBtn');
    const speedSelect = document.getElementById('mayhenSimSpeedSelect');
    const autoCaptureToggle = document.getElementById('mayhenSimAutoCaptureToggle');
    const saveFromChatBtn = document.getElementById('mayhenSimSaveFromChatBtn');
    const activeScenarioHint = document.getElementById('mayhenSimActiveScenarioHint');

    const verdictPill = document.getElementById('mayhenSimVerdictPill');
    const verdictText = document.getElementById('mayhenSimVerdictText');

    const flowArea = document.getElementById('mayhenSimFlowArea');
    const conduitSvg = document.getElementById('mayhenSimConduitSvg');
    const tracksGroup = document.getElementById('mayhenSimConduitTracks');
    const streamsGroup = document.getElementById('mayhenSimConduitStreams');
    const orbsGroup = document.getElementById('mayhenSimConduitOrbs');

    const inspBadge = document.getElementById('mayhenSimInspBadge');
    const inspTitle = document.getElementById('mayhenSimInspTitle');
    const inspComp = document.getElementById('mayhenSimInspComp');
    const inspBody = document.getElementById('mayhenSimInspBody');
    const assertionsBox = document.getElementById('mayhenSimAssertionsBox');
    const telemetryLeft = document.getElementById('mayhenSimTelemetryLeft');
    const telemetryRight = document.getElementById('mayhenSimTelemetryRight');

    const scenarioListEl = document.getElementById('mayhenSimScenarioList');
    const suiteStatEl = document.getElementById('mayhenSimSuiteStat');
    const newScenarioBtn = document.getElementById('mayhenSimNewScenarioBtn');
    const runAllBtn = document.getElementById('mayhenSimRunAllBtn');

    const scForm = document.getElementById('mayhenSimScenarioForm');
    const editorHeading = document.getElementById('mayhenSimEditorHeading');
    const saveStateEl = document.getElementById('mayhenSimSaveState');
    const fieldId = document.getElementById('mayhenSimFieldId');
    const fieldName = document.getElementById('mayhenSimFieldName');
    const fieldCategory = document.getElementById('mayhenSimFieldCategory');
    const fieldPrompt = document.getElementById('mayhenSimFieldPrompt');
    const fieldMode = document.getElementById('mayhenSimFieldMode');
    const fieldCards = document.getElementById('mayhenSimFieldCards');
    const fieldContain = document.getElementById('mayhenSimFieldContain');
    const fieldNotContain = document.getElementById('mayhenSimFieldNotContain');
    const deleteScenarioBtn = document.getElementById('mayhenSimDeleteScenarioBtn');
    const runEditorBtn = document.getElementById('mayhenSimRunEditorBtn');

    const nodeMap = new Map();
    for (let i = 1; i <= 7; i++) {
        const el = document.getElementById(`mayhenSimNode${i}`);
        if (el) nodeMap.set(i, el);
    }

    const conduits = new EnergyConduitVisualizer(flowArea, conduitSvg, tracksGroup, streamsGroup, orbsGroup, nodeMap);

    let sandboxSession = 'lab_' + Date.now().toString(36);
    let scenarios = [];
    try {
        scenarios = JSON.parse(root.dataset.initialScenarios || '[]');
    } catch {
        scenarios = [];
    }

    let selectedScenarioId = null;
    let lastTrace = null;
    let lastUserPrompt = '';
    let isRunning = false;
    let autoSaveTimer = 0;

    function getSpeedFactor() {
        const val = Number.parseFloat(speedSelect ? speedSelect.value : '1');
        return Number.isFinite(val) && val > 0 ? val : 1;
    }

    function setVerdict(state, label) {
        verdictPill.className = 'mayhen_admin_ai_sim_verdict_pill';
        const map = {
            idle: 'mayhen_admin_ai_sim_verdict_idle',
            running: 'mayhen_admin_ai_sim_verdict_running',
            passed: 'mayhen_admin_ai_sim_verdict_pass',
            failed: 'mayhen_admin_ai_sim_verdict_fail',
        };
        verdictPill.classList.add(map[state] || map.idle);
        verdictText.textContent = label;
    }

    function resetBoardVisuals() {
        conduits.resetAll();
        for (let i = 1; i <= 7; i++) {
            const el = nodeMap.get(i);
            if (!el) continue;
            el.className = 'mayhen_admin_ai_sim_node';
            const msEl = el.querySelector(`[data-step-ms="${i}"]`);
            const stEl = el.querySelector(`[data-step-state="${i}"]`);
            if (msEl) msEl.textContent = '—';
            if (stEl) stEl.textContent = 'Chờ chạy';
        }
    }

    function applyNodeState(stepData, triggerImpact = false) {
        const num = Number(stepData.step);
        const el = nodeMap.get(num);
        if (!el) return;

        el.className = 'mayhen_admin_ai_sim_node';
        const status = stepData.status || 'passed';
        const classMap = {
            active: 'is_active',
            passed: 'is_passed',
            warn: 'is_warn',
            failed: 'is_failed',
            skipped: 'is_skipped',
            halted: 'is_halted',
        };
        if (classMap[status]) {
            el.classList.add(classMap[status]);
        }

        if (triggerImpact) {
            el.classList.add(status === 'failed' ? 'is_receiving_fail' : 'is_receiving');
            window.setTimeout(() => {
                el.classList.remove('is_receiving', 'is_receiving_fail');
            }, 680);
        }

        const msEl = el.querySelector(`[data-step-ms="${num}"]`);
        const stEl = el.querySelector(`[data-step-state="${num}"]`);
        if (msEl) {
            msEl.textContent = stepData.duration_ms > 0 ? `${stepData.duration_ms}ms` : '—';
        }
        if (stEl) {
            const labelMap = {
                active: 'Đang xử lý...',
                passed: 'PASS ✓',
                warn: 'Cảnh báo',
                failed: 'FAIL / Chặn ✗',
                skipped: 'Bỏ qua',
                halted: 'Đã dừng',
            };
            stEl.textContent = labelMap[status] || status;
        }
    }

    function selectNodeForInspector(stepNumber) {
        if (!lastTrace || !Array.isArray(lastTrace.steps)) return;
        const stepData = lastTrace.steps.find((s) => Number(s.step) === Number(stepNumber));
        if (!stepData) return;

        nodeMap.forEach((el, num) => {
            el.classList.toggle('is_selected', num === Number(stepNumber));
        });

        renderInspector(stepData, lastTrace);
    }

    function renderInspector(stepData, trace) {
        inspBadge.className = 'mayhen_admin_ai_sim_insp_badge';
        if (stepData.status === 'passed') inspBadge.classList.add('is_ok');
        else if (stepData.status === 'failed') inspBadge.classList.add('is_fail');
        else if (stepData.status === 'warn') inspBadge.classList.add('is_warn');

        const stateShort = {
            passed: 'PASS ✓',
            failed: 'FAIL ✗',
            warn: 'CẢNH BÁO',
            skipped: 'BỎ QUA',
            halted: 'ĐÃ DỪNG',
            active: 'ĐANG CHẠY',
        }[stepData.status] || '';

        inspBadge.textContent = `BƯỚC ${stepData.step}/7${stateShort ? ' • ' + stateShort : ''}`;
        inspTitle.textContent = stepData.headline || stepData.title;
        inspComp.textContent = stepData.component || '';

        inspBody.textContent = '';

        if (stepData.error_reason) {
            const errBox = document.createElement('div');
            errBox.className = 'mayhen_admin_ai_sim_error_box';
            errBox.textContent = `Lý do dừng / lỗi tại Bước ${stepData.step}: ${stepData.error_reason}`;
            inspBody.appendChild(errBox);
        }

        const p = document.createElement('p');
        p.className = 'mayhen_admin_ai_sim_insp_summary';
        p.textContent = stepData.summary || '';
        inspBody.appendChild(p);

        if (Array.isArray(stepData.metrics) && stepData.metrics.length > 0) {
            const grid = document.createElement('div');
            grid.className = 'mayhen_admin_ai_sim_metrics_grid';
            stepData.metrics.forEach((m) => {
                const card = document.createElement('div');
                card.className = 'mayhen_admin_ai_sim_metric_card';
                const lbl = document.createElement('div');
                lbl.className = 'mayhen_admin_ai_sim_metric_label';
                lbl.textContent = m.label || '';
                const val = document.createElement('div');
                val.className = 'mayhen_admin_ai_sim_metric_val';
                if (m.tone) val.classList.add(`is_${m.tone}`);
                val.textContent = m.value || '—';
                card.appendChild(lbl);
                card.appendChild(val);
                grid.appendChild(card);
            });
            inspBody.appendChild(grid);
        }

        // Hiển thị danh sách bằng chứng BM25 thật ở Bước 3
        const details = stepData.details || {};
        if (Number(stepData.step) === 3) {
            const list = document.createElement('div');
            list.className = 'mayhen_admin_ai_sim_detail_list';
            (details.top_docs || []).forEach((doc) => {
                const row = document.createElement('div');
                row.className = 'mayhen_admin_ai_sim_detail_row';
                const left = document.createElement('span');
                left.textContent = `[Docs] ${doc.title} (${doc.url})`;
                const right = document.createElement('span');
                right.className = 'mayhen_admin_ai_sim_detail_score';
                right.textContent = `BM25 ${doc.score}`;
                row.appendChild(left);
                row.appendChild(right);
                list.appendChild(row);
            });
            (details.top_knowledge || []).forEach((k) => {
                const row = document.createElement('div');
                row.className = 'mayhen_admin_ai_sim_detail_row';
                const left = document.createElement('span');
                left.textContent = `[Tri thức #${k.id}] ${k.title}`;
                const right = document.createElement('span');
                right.className = 'mayhen_admin_ai_sim_detail_score';
                right.textContent = `BM25 ${k.score}`;
                row.appendChild(left);
                row.appendChild(right);
                list.appendChild(row);
            });
            if (list.childNodes.length > 0) {
                inspBody.appendChild(list);
            }
        }

        // Hiển thị danh sách tiêu chí đối chiếu kịch bản (nếu có)
        const assertions = (trace && Array.isArray(trace.assertions)) ? trace.assertions : [];
        if (assertions.length > 0) {
            assertionsBox.hidden = false;
            assertionsBox.textContent = '';
            assertions.forEach((a) => {
                const row = document.createElement('div');
                row.className = `mayhen_admin_ai_sim_assertion_row ${a.passed ? 'is_ok' : 'is_fail'}`;
                const left = document.createElement('strong');
                left.textContent = `${a.passed ? '✓ PASS' : '✗ FAIL'} • ${a.label}`;
                const right = document.createElement('span');
                right.textContent = a.detail || '';
                row.appendChild(left);
                row.appendChild(right);
                assertionsBox.appendChild(row);
            });
        } else {
            assertionsBox.hidden = true;
            assertionsBox.textContent = '';
        }

        if (trace && trace.telemetry) {
            const tel = trace.telemetry;
            telemetryLeft.textContent = '';
            telemetryLeft.appendChild(document.createTextNode('Tổng độ trễ: '));
            const sMs = document.createElement('strong');
            sMs.textContent = `${tel.total_latency_ms}ms`;
            telemetryLeft.appendChild(sMs);
            telemetryLeft.appendChild(document.createTextNode(' • Token: '));
            const sTok = document.createElement('strong');
            sTok.textContent = `${tel.total_tokens} (~${tel.cost_vnd}đ)`;
            telemetryLeft.appendChild(sTok);

            telemetryRight.textContent = '';
            telemetryRight.appendChild(document.createTextNode('Model: '));
            const sMod = document.createElement('strong');
            sMod.textContent = tel.draft_model || 'Fallback nội bộ';
            telemetryRight.appendChild(sMod);
        }
    }

    function appendUserBubble(text) {
        const wrap = document.createElement('div');
        wrap.className = 'mayhen_admin_ai_sim_msg mayhen_admin_ai_sim_msg_user';
        const bubble = document.createElement('div');
        bubble.className = 'mayhen_admin_ai_sim_msg_bubble';
        bubble.textContent = text;
        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function createTypingBubble() {
        const wrap = document.createElement('div');
        wrap.className = 'mayhen_admin_ai_sim_msg mayhen_admin_ai_sim_msg_bot';
        const box = document.createElement('div');
        box.className = 'mayhen_admin_ai_sim_typing';

        const dots = document.createElement('span');
        dots.className = 'mayhen_admin_ai_sim_typing_dots';
        dots.appendChild(document.createElement('i'));
        dots.appendChild(document.createElement('i'));
        dots.appendChild(document.createElement('i'));

        const label = document.createElement('span');
        label.textContent = 'Đang chạy thật luồng 7 bước...';

        box.appendChild(dots);
        box.appendChild(label);
        wrap.appendChild(box);
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;

        return { wrap, label };
    }

    function appendBotResultBubble(trace) {
        const wrap = document.createElement('div');
        wrap.className = 'mayhen_admin_ai_sim_msg mayhen_admin_ai_sim_msg_bot';

        const bubble = document.createElement('div');
        bubble.className = 'mayhen_admin_ai_sim_msg_bubble';

        const textDiv = document.createElement('div');
        appendFormattedText(textDiv, trace.reply || '');
        bubble.appendChild(textDiv);

        if (Array.isArray(trace.templates) && trace.templates.length > 0) {
            const grp = document.createElement('div');
            grp.className = 'mayhen_admin_ai_sim_cards_group';
            const lbl = document.createElement('div');
            lbl.className = 'mayhen_admin_ai_sim_cards_label';
            lbl.textContent = `Mẫu thiệp hiển thị (${trace.templates.length})`;
            grp.appendChild(lbl);

            trace.templates.forEach((tpl) => {
                const row = document.createElement('div');
                row.className = 'mayhen_admin_ai_sim_tpl_card';
                const name = document.createElement('span');
                name.className = 'mayhen_admin_ai_sim_tpl_name';
                name.textContent = tpl.name || tpl.slug;
                const a = document.createElement('a');
                a.className = 'mayhen_admin_ai_sim_tpl_link';
                a.href = tpl.preview_url || `/xem-thu/${tpl.slug}`;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
                a.textContent = 'Xem thử →';
                row.appendChild(name);
                row.appendChild(a);
                grp.appendChild(row);
            });
            bubble.appendChild(grp);
        }

        if (Array.isArray(trace.docs) && trace.docs.length > 0) {
            const grp = document.createElement('div');
            grp.className = 'mayhen_admin_ai_sim_cards_group';
            const lbl = document.createElement('div');
            lbl.className = 'mayhen_admin_ai_sim_cards_label';
            lbl.textContent = `Hướng dẫn chi tiết (${trace.docs.length})`;
            grp.appendChild(lbl);

            trace.docs.forEach((doc) => {
                const a = document.createElement('a');
                a.className = 'mayhen_admin_ai_sim_doc_card';
                a.href = doc.url || '/docs';
                a.target = '_blank';
                a.rel = 'noopener noreferrer';

                const t = document.createElement('div');
                t.className = 'mayhen_admin_ai_sim_doc_title';
                t.textContent = `${doc.title} →`;
                const u = document.createElement('div');
                u.className = 'mayhen_admin_ai_sim_doc_url';
                u.textContent = doc.url || '';

                a.appendChild(t);
                a.appendChild(u);
                grp.appendChild(a);
            });
            bubble.appendChild(grp);
        }

        const meta = document.createElement('div');
        meta.className = 'mayhen_admin_ai_sim_msg_meta';

        const statusTag = document.createElement('span');
        statusTag.className = `mayhen_admin_ai_sim_tag ${trace.overall_status === 'passed' ? 'mayhen_admin_ai_sim_tag_ok' : 'mayhen_admin_ai_sim_tag_fail'}`;
        statusTag.textContent = trace.overall_status === 'passed'
            ? 'PASS ✓'
            : (trace.failed_step ? `FAIL Bước ${trace.failed_step} ✗` : 'FAIL ✗');
        meta.appendChild(statusTag);

        const modeTag = document.createElement('span');
        modeTag.className = 'mayhen_admin_ai_sim_tag mayhen_admin_ai_sim_tag_neutral';
        modeTag.textContent = trace.mode || '';
        meta.appendChild(modeTag);

        if (trace.telemetry && trace.telemetry.total_latency_ms) {
            const msTag = document.createElement('span');
            msTag.className = 'mayhen_admin_ai_sim_tag mayhen_admin_ai_sim_tag_neutral';
            msTag.textContent = `${trace.telemetry.total_latency_ms}ms`;
            meta.appendChild(msTag);
        }

        bubble.appendChild(meta);
        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    /**
     * Trình diễn luồng truyền năng lượng thật từng bước 1 → 7 theo đúng dữ liệu server trả về.
     */
    async function animateTraceSequence(trace, typingHandle = null) {
        const factor = getSpeedFactor();
        const pulseDur = Math.round(440 * factor);
        const holdDur = Math.round(240 * factor);

        resetBoardVisuals();
        const steps = Array.isArray(trace.steps) ? trace.steps : [];

        for (let i = 0; i < steps.length; i++) {
            const stepData = steps[i];
            const num = Number(stepData.step);

            if (stepData.status === 'halted') {
                applyNodeState(stepData, false);
                if (num > 1) {
                    conduits.setSegmentState(num - 1, num, 'idle');
                }
                continue;
            }

            if (num > 1) {
                const prevStep = steps[i - 1];
                if (prevStep && prevStep.status !== 'halted') {
                    const pulseTone =
                        stepData.status === 'failed'
                            ? 'fail'
                            : (stepData.status === 'skipped' ? 'skipped' : 'ok');

                    if (stepData.status === 'skipped') {
                        conduits.setSegmentState(num - 1, num, 'skipped');
                        await sleep(Math.round(160 * factor));
                    } else {
                        await conduits.pulseEnergy(num - 1, num, pulseTone, pulseDur);
                    }
                }
            }

            if (typingHandle) {
                typingHandle.label.textContent = `Bước ${num}/7: ${stepData.title}...`;
            }

            // Sáng nhẹ trạng thái đang tiếp nhận trước khi khóa kết quả
            applyNodeState({ ...stepData, status: 'active' }, true);
            renderInspector(stepData, trace);
            await sleep(holdDur);

            applyNodeState(stepData, false);
            nodeMap.forEach((el, n) => el.classList.toggle('is_selected', n === num));
            renderInspector(stepData, trace);

            // Nếu bước này bị FAIL và luồng dừng hẳn tại đây -> tạm dừng 1 nhịp để nhấn mạnh điểm gãy
            if (stepData.status === 'failed') {
                await sleep(Math.round(280 * factor));
            }
        }

        // Chọn hiển thị bước bị fail (nếu có) hoặc Bước 7 cuối cùng
        const focusStep = trace.failed_step || trace.pipeline_failed_step || 7;
        selectNodeForInspector(focusStep);
        setVerdict(trace.overall_status, trace.verdict_label || (trace.overall_status === 'passed' ? 'PASS ✓' : 'FAIL ✗'));
    }

    async function executeMessage(promptText, scenarioObj = null) {
        const message = String(promptText || '').trim();
        if (!message || isRunning) return null;

        isRunning = true;
        sendBtn.disabled = true;
        runAllBtn.disabled = true;
        runEditorBtn.disabled = true;
        replayBtn.disabled = true;
        lastUserPrompt = message;

        appendUserBubble(message);
        const typingHandle = createTypingBubble();
        setVerdict('running', 'Đang thực thi thật trên hệ thống Mây Hẹn...');
        resetBoardVisuals();

        // Kích hoạt hiệu ứng truyền năng lượng sơ khởi Bước 1 trong khi chờ API phản hồi
        applyNodeState({ step: 1, status: 'active', duration_ms: 0 }, true);

        const payload = {
            message,
            sandbox_session: sandboxSession,
        };
        if (scenarioObj && scenarioObj.id) {
            payload.scenario_id = scenarioObj.id;
            payload.expectations = {
                expected_mode: scenarioObj.expected_mode || 'any_answer',
                expected_cards: scenarioObj.expected_cards || 'any',
                must_contain: scenarioObj.must_contain || '',
                must_not_contain: scenarioObj.must_not_contain || '',
            };
        }

        try {
            const resp = await fetch(urls.run, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(payload),
            });

            const data = await resp.json();
            if (!resp.ok || !data.ok) {
                throw new Error(data.message || 'Không thể chạy kiểm thử trên máy chủ.');
            }

            lastTrace = data;
            await animateTraceSequence(data, typingHandle);

            typingHandle.wrap.remove();
            appendBotResultBubble(data);
            replayBtn.disabled = false;

            // Cập nhật lại trạng thái kịch bản vừa chạy vào danh sách
            if (data.scenario) {
                upsertScenarioLocal(data.scenario);
                renderScenarioList();
            } else if (autoCaptureToggle && autoCaptureToggle.checked && !scenarioObj) {
                await autoCreateScenarioFromChat(message, data);
            }

            return data;
        } catch (err) {
            typingHandle.wrap.remove();
            setVerdict('failed', `Lỗi kết nối: ${err.message || 'Không xác định'}`);
            return null;
        } finally {
            isRunning = false;
            sendBtn.disabled = false;
            runAllBtn.disabled = false;
            runEditorBtn.disabled = false;
        }
    }

    // ===== QUẢN LÝ & TỰ LƯU KỊCH BẢN TEST =====
    function setSaveState(mode, text) {
        saveStateEl.className = 'mayhen_admin_ai_sim_save_state';
        if (mode === 'saving') saveStateEl.classList.add('is_saving');
        if (mode === 'error') saveStateEl.classList.add('is_error');
        saveStateEl.textContent = text;
    }

    function upsertScenarioLocal(sc) {
        const idx = scenarios.findIndex((item) => Number(item.id) === Number(sc.id));
        if (idx >= 0) {
            scenarios[idx] = sc;
        } else {
            scenarios.push(sc);
        }
    }

    function renderScenarioList() {
        scenarioListEl.textContent = '';

        let passCount = 0;
        let failCount = 0;
        scenarios.forEach((sc) => {
            if (sc.last_status === 'passed') passCount++;
            else if (sc.last_status === 'failed') failCount++;
        });
        suiteStatEl.textContent = `${passCount} PASS • ${failCount} FAIL • ${scenarios.length} kịch bản`;

        if (scenarios.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'mayhen_admin_ai_sim_insp_summary';
            empty.textContent = 'Chưa có kịch bản nào. Hãy viết bên phải để hệ thống tự lưu!';
            scenarioListEl.appendChild(empty);
            return;
        }

        scenarios.forEach((sc) => {
            const item = document.createElement('div');
            item.className = 'mayhen_admin_ai_sim_sc_item';
            if (Number(selectedScenarioId) === Number(sc.id)) {
                item.classList.add('is_active');
            }

            const top = document.createElement('div');
            top.className = 'mayhen_admin_ai_sim_sc_top';

            const name = document.createElement('div');
            name.className = 'mayhen_admin_ai_sim_sc_name';
            name.textContent = sc.name;

            const statusBadge = document.createElement('span');
            if (sc.last_status === 'passed') {
                statusBadge.className = 'mayhen_admin_ai_sim_tag mayhen_admin_ai_sim_tag_ok';
                statusBadge.textContent = 'PASS ✓';
            } else if (sc.last_status === 'failed') {
                statusBadge.className = 'mayhen_admin_ai_sim_tag mayhen_admin_ai_sim_tag_fail';
                statusBadge.textContent = sc.last_failed_step ? `FAIL B${sc.last_failed_step}` : 'FAIL ✗';
            } else {
                statusBadge.className = 'mayhen_admin_ai_sim_tag mayhen_admin_ai_sim_tag_neutral';
                statusBadge.textContent = 'Chưa chạy';
            }

            top.appendChild(name);
            top.appendChild(statusBadge);

            const promptEl = document.createElement('div');
            promptEl.className = 'mayhen_admin_ai_sim_sc_prompt';
            promptEl.textContent = sc.prompt;

            const foot = document.createElement('div');
            foot.className = 'mayhen_admin_ai_sim_sc_foot';

            const meta = document.createElement('span');
            const msPart = sc.last_latency_ms ? ` • ${sc.last_latency_ms}ms` : '';
            meta.textContent = `${sc.category_label || sc.category}${msPart}`;

            const runBtn = document.createElement('button');
            runBtn.type = 'button';
            runBtn.className = 'mayhen_admin_ai_sim_sc_run_btn';
            runBtn.textContent = '▶ Chạy';
            runBtn.addEventListener('click', (ev) => {
                ev.stopPropagation();
                loadScenarioIntoEditor(sc);
                executeMessage(sc.prompt, sc);
            });

            foot.appendChild(meta);
            foot.appendChild(runBtn);

            item.appendChild(top);
            item.appendChild(promptEl);
            item.appendChild(foot);

            item.addEventListener('click', () => {
                loadScenarioIntoEditor(sc);
            });

            scenarioListEl.appendChild(item);
        });
    }

    function loadScenarioIntoEditor(sc) {
        selectedScenarioId = sc ? sc.id : null;
        fieldId.value = sc ? String(sc.id) : '';
        fieldName.value = sc ? sc.name : '';
        fieldCategory.value = sc ? sc.category : 'support';
        fieldPrompt.value = sc ? sc.prompt : '';
        fieldMode.value = sc ? sc.expected_mode : 'any_answer';
        fieldCards.value = sc ? sc.expected_cards : 'any';
        fieldContain.value = sc ? (sc.must_contain || '') : '';
        fieldNotContain.value = sc ? (sc.must_not_contain || '') : '';

        editorHeading.textContent = sc ? `Đang sửa kịch bản #${sc.id}` : 'Tạo kịch bản kiểm thử mới';
        deleteScenarioBtn.hidden = !sc;
        setSaveState('saved', sc ? '✓ Đã đồng bộ' : 'Gõ nội dung để tự lưu');

        if (activeScenarioHint) {
            activeScenarioHint.textContent = sc
                ? `Đang chọn kịch bản: "${sc.name}"`
                : 'Đang ở chế độ chat tự do (chưa gắn tiêu chí kịch bản)';
        }

        renderScenarioList();
    }

    function collectEditorPayload() {
        return {
            name: fieldName.value.trim(),
            category: fieldCategory.value || 'support',
            prompt: fieldPrompt.value.trim(),
            expected_mode: fieldMode.value || 'any_answer',
            expected_cards: fieldCards.value || 'any',
            must_contain: fieldContain.value.trim(),
            must_not_contain: fieldNotContain.value.trim(),
        };
    }

    async function persistScenarioFromEditor() {
        const body = collectEditorPayload();
        if (body.name.length < 2 || body.prompt.length < 2) {
            setSaveState('error', 'Cần nhập Tên và Câu hỏi (>= 2 ký tự)');
            return null;
        }

        const existingId = fieldId.value.trim();
        const isUpdate = existingId !== '';
        const targetUrl = isUpdate
            ? urls.updateScenarioTpl.replace('__ID__', existingId)
            : urls.storeScenario;

        setSaveState('saving', '● Đang tự lưu...');

        try {
            const resp = await fetch(targetUrl, {
                method: isUpdate ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(body),
            });

            const data = await resp.json();
            if (!resp.ok || !data.ok || !data.scenario) {
                throw new Error(data.message || 'Không thể lưu kịch bản.');
            }

            upsertScenarioLocal(data.scenario);
            selectedScenarioId = data.scenario.id;
            fieldId.value = String(data.scenario.id);
            editorHeading.textContent = `Đang sửa kịch bản #${data.scenario.id}`;
            deleteScenarioBtn.hidden = false;
            setSaveState('saved', '✓ Đã tự lưu vào hệ thống');
            renderScenarioList();
            return data.scenario;
        } catch (err) {
            setSaveState('error', `Lỗi lưu: ${err.message || 'Thử lại'}`);
            return null;
        }
    }

    function scheduleAutoSave() {
        if (autoSaveTimer) clearTimeout(autoSaveTimer);
        const body = collectEditorPayload();
        if (body.name.length < 2 || body.prompt.length < 2) return;
        setSaveState('saving', '● Chuẩn bị tự lưu...');
        autoSaveTimer = window.setTimeout(() => {
            autoSaveTimer = 0;
            persistScenarioFromEditor();
        }, 800);
    }

    async function autoCreateScenarioFromChat(promptText, trace = null) {
        const shortName = promptText.length > 48 ? `${promptText.slice(0, 48)}...` : promptText;
        const inferredMode = trace && trace.mode === 'out_of_scope'
            ? 'out_of_scope'
            : (trace && trace.mode === 'blocked_security' ? 'blocked_security' : 'any_answer');

        const body = {
            name: shortName,
            category: inferredMode === 'out_of_scope' ? 'out_of_scope' : (inferredMode === 'blocked_security' ? 'security' : 'support'),
            prompt: promptText,
            expected_mode: inferredMode,
            expected_cards: 'any',
            must_contain: '',
            must_not_contain: '',
        };

        try {
            const resp = await fetch(urls.storeScenario, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(body),
            });
            const data = await resp.json();
            if (resp.ok && data.ok && data.scenario) {
                upsertScenarioLocal(data.scenario);
                loadScenarioIntoEditor(data.scenario);
                setSaveState('saved', '✓ Đã tự lưu thành kịch bản mới');
            }
        } catch {
            // Bỏ qua lỗi mạng nền
        }
    }

    // ===== GẮN SỰ KIỆN =====
    nodeMap.forEach((el, num) => {
        el.addEventListener('click', () => selectNodeForInspector(num));
    });

    chatForm.addEventListener('submit', (ev) => {
        ev.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;
        chatInput.value = '';
        executeMessage(text, null);
    });

    root.querySelectorAll('[data-quick-prompt]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const q = btn.getAttribute('data-quick-prompt') || '';
            if (q) executeMessage(q, null);
        });
    });

    replayBtn.addEventListener('click', async () => {
        if (!lastTrace || isRunning) return;
        isRunning = true;
        replayBtn.disabled = true;
        await animateTraceSequence(lastTrace, null);
        replayBtn.disabled = false;
        isRunning = false;
    });

    resetBtn.addEventListener('click', async () => {
        if (isRunning) return;
        await fetch(urls.reset, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ sandbox_session: sandboxSession }),
        }).catch(() => {});

        sandboxSession = 'lab_' + Date.now().toString(36);
        messagesEl.textContent = '';
        const welcome = document.createElement('div');
        welcome.className = 'mayhen_admin_ai_sim_msg mayhen_admin_ai_sim_msg_bot';
        const bub = document.createElement('div');
        bub.className = 'mayhen_admin_ai_sim_msg_bubble';
        bub.textContent = 'Đã làm mới phiên kiểm thử sandbox. Bạn có thể bắt đầu luồng hội thoại mới ngay bây giờ!';
        welcome.appendChild(bub);
        messagesEl.appendChild(welcome);

        lastTrace = null;
        replayBtn.disabled = true;
        resetBoardVisuals();
        setVerdict('idle', 'Sẵn sàng chạy thử thật 7 bước');
    });

    saveFromChatBtn.addEventListener('click', async () => {
        const text = chatInput.value.trim() || lastUserPrompt;
        if (!text) {
            chatInput.focus();
            return;
        }
        await autoCreateScenarioFromChat(text, lastTrace);
    });

    newScenarioBtn.addEventListener('click', () => {
        loadScenarioIntoEditor(null);
        fieldName.focus();
    });

    [fieldName, fieldCategory, fieldPrompt, fieldMode, fieldCards, fieldContain, fieldNotContain].forEach((input) => {
        input.addEventListener('input', scheduleAutoSave);
        input.addEventListener('change', scheduleAutoSave);
    });

    scForm.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        if (autoSaveTimer) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = 0;
        }
        await persistScenarioFromEditor();
    });

    runEditorBtn.addEventListener('click', async () => {
        if (isRunning) return;
        if (autoSaveTimer) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = 0;
        }
        const saved = await persistScenarioFromEditor();
        if (saved) {
            executeMessage(saved.prompt, saved);
        }
    });

    deleteScenarioBtn.addEventListener('click', async () => {
        const id = fieldId.value.trim();
        if (!id || !window.confirm('Bạn có chắc muốn xóa kịch bản kiểm thử này không?')) return;

        try {
            const resp = await fetch(urls.deleteScenarioTpl.replace('__ID__', id), {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });
            if (resp.ok) {
                scenarios = scenarios.filter((s) => Number(s.id) !== Number(id));
                loadScenarioIntoEditor(scenarios[0] || null);
            }
        } catch {
            setSaveState('error', 'Không thể xóa kịch bản.');
        }
    });

    runAllBtn.addEventListener('click', async () => {
        if (isRunning || scenarios.length === 0) return;

        let passed = 0;
        let failed = 0;
        const listSnapshot = [...scenarios];

        for (let i = 0; i < listSnapshot.length; i++) {
            const sc = listSnapshot[i];
            loadScenarioIntoEditor(sc);
            // Tách phiên riêng cho từng kịch bản độc lập để không lẫn ngữ cảnh
            sandboxSession = `suite_${sc.id}_${Date.now().toString(36)}`;
            const res = await executeMessage(sc.prompt, sc);
            if (res && res.overall_status === 'passed') passed++;
            else failed++;
            await sleep(260);
        }

        setVerdict(
            failed === 0 ? 'passed' : 'failed',
            `Hoàn tất bộ test: ${passed}/${listSnapshot.length} PASS${failed > 0 ? ` • ${failed} FAIL` : ' ✓'}`
        );
    });

    // Khởi tạo trạng thái ban đầu
    renderScenarioList();
    if (scenarios.length > 0) {
        loadScenarioIntoEditor(scenarios[0]);
    }
};

document.addEventListener('DOMContentLoaded', initAdminAiSimulator);
