/**
 * MAYHEN.VN — INTERACTIVE FAMILY TREE ENGINE (GIA PHẢ ONLINE)
 * 100% Pure Vanilla JS • Zero External Libraries
 */
import { showDraftRestoredBanner } from '../common/tool-draft.js';

const FAMILY_TREE_STORAGE_KEY = 'mayhen_family_tree_draft_v1';

/**
 * Xóa sạch toàn bộ khóa storage liên quan đến Gia Phả (tránh phình/nặng bộ nhớ trình duyệt).
 */
function clearAllFamilyTreeStorage() {
    const shouldRemove = (key) => (
        key === FAMILY_TREE_STORAGE_KEY
        || key.startsWith('mayhen_family_tree_')
        || key.startsWith('mayhen_tool_draft_v1:family_tree')
    );

    [window.localStorage, window.sessionStorage].forEach((store) => {
        if (!store) return;
        try {
            const keysToDelete = [];
            for (let i = 0; i < store.length; i++) {
                const k = store.key(i);
                if (k && shouldRemove(k)) keysToDelete.push(k);
            }
            keysToDelete.forEach((k) => store.removeItem(k));
        } catch {
            // Bỏ qua nếu trình duyệt hạn chế storage
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Dọn sạch storage gia phả khi người dùng xóa gia phả trên trang /gia-pha/cua-toi
    document.querySelectorAll('[data-ft-clear-storage]').forEach((formEl) => {
        formEl.addEventListener('submit', () => {
            clearAllFamilyTreeStorage();
        });
    });

    const app = document.getElementById('mayhenFamilyTreeApp');
    if (!app) return;

    const bootstrapEl = document.getElementById('mayhenFamilyTreeBootstrap');
    if (!bootstrapEl) return;

    let state = null;
    try {
        state = JSON.parse(bootstrapEl.textContent || '{}');
    } catch {
        return;
    }

    const mode = app.getAttribute('data-mode') || 'viewer';
    const isStudio = mode === 'studio';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const storageKey = FAMILY_TREE_STORAGE_KEY;
    const urlParams = new URLSearchParams(window.location.search);
    let hasRestoredDraft = false;
    let isBootstrapping = true;

    if (isStudio && urlParams.has('reset')) {
        clearAllFamilyTreeStorage();
    }

    // Nếu đang ở Studio tạo mới (chưa có id từ CSDL), luôn khôi phục nháp localStorage (kể cả khi URL có ?mau=... sau khi quay lại từ trang Đăng nhập)
    if (isStudio && !state.id && !urlParams.has('reset')) {
        try {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                const parsed = JSON.parse(saved);
                if (parsed && Array.isArray(parsed.members) && parsed.members.length > 0) {
                    state = { ...state, ...parsed, id: null };
                    hasRestoredDraft = true;
                }
            }
        } catch {
            // Bỏ qua lỗi đọc localStorage
        }
    }

    let selectedMemberId = (state.selected_member_id && (state.members || []).some((m) => m.id === state.selected_member_id))
        ? state.selected_member_id
        : (state.members?.[0]?.id || null);
    let activeTab = state.active_tab || 'info';
    let focusRootId = null;
    let layoutMode = state.layout_mode || 'vertical';
    let zoom = 0.92;
    let panX = 80;
    let panY = 70;
    let isPanning = false;
    let startPanX = 0;
    let startPanY = 0;
    let lastRenderPositions = {};

    const viewport = document.getElementById('mayhenFtViewport');
    const world = document.getElementById('mayhenFtWorld');
    const svgLines = document.getElementById('mayhenFtSvgLines');
    const nodesContainer = document.getElementById('mayhenFtNodes');
    const chronicleView = document.getElementById('mayhenFtChronicleView');
    const drawer = document.getElementById('mayhenFtDrawer');
    const drawerTitle = document.getElementById('mayhenFtDrawerTitle');
    const drawerBody = document.getElementById('mayhenFtDrawerBody');
    const toastEl = document.getElementById('mayhenFtToast');

    function showToast(msg) {
        if (!toastEl) return;
        toastEl.textContent = msg;
        toastEl.classList.add('is-visible');
        clearTimeout(showToast._timer);
        showToast._timer = setTimeout(() => toastEl.classList.remove('is-visible'), 2800);
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function saveDraftToLocal(force = false) {
        if (!isStudio || state.id) return;
        if (isBootstrapping && !force) return;
        const payload = {
            title: state.title,
            family_surname: state.family_surname,
            ancestral_hall: state.ancestral_hall,
            template_style: state.template_style,
            layout_mode: layoutMode,
            visibility: state.visibility || 'public',
            active_tab: activeTab,
            selected_member_id: selectedMemberId,
            intro_chronicle: state.intro_chronicle,
            clan_rules: state.clan_rules,
            members: state.members,
            saved_at: Date.now(),
        };
        try {
            localStorage.setItem(storageKey, JSON.stringify(payload));
        } catch {
            // Nếu vượt hạn mức storage (ví dụ do nhiều ảnh base64), tự động lưu bản gọn không kèm data URL ảnh
            try {
                const compactMembers = (state.members || []).map((m) => ({
                    ...m,
                    avatar_url: typeof m.avatar_url === 'string' && m.avatar_url.startsWith('data:') ? '' : (m.avatar_url || ''),
                }));
                localStorage.setItem(storageKey, JSON.stringify({ ...payload, members: compactMembers }));
            } catch {
                // Bỏ qua nếu bộ nhớ vẫn đầy
            }
        }
    }

    // Hệ thống Lịch sử Hoàn tác (Undo) & Làm lại (Redo) — tối đa 40 bước
    const MAX_HISTORY_STEPS = 40;
    const undoStack = [];
    const redoStack = [];
    let isApplyingHistory = false;
    let lastTextUndoKey = '';
    let lastTextUndoAt = 0;

    function createHistorySnapshot(actionLabel = 'Thao tác trước') {
        return {
            label: actionLabel,
            data: JSON.parse(JSON.stringify({
                title: state.title || '',
                family_surname: state.family_surname || '',
                ancestral_hall: state.ancestral_hall || '',
                template_style: state.template_style || 'hoang-kim-tu-duong',
                layout_mode: layoutMode || 'vertical',
                visibility: state.visibility || 'public',
                intro_chronicle: state.intro_chronicle || '',
                clan_rules: state.clan_rules || '',
                members: state.members || [],
                selected_member_id: selectedMemberId || null,
                focus_root_id: focusRootId || null,
            })),
        };
    }

    function updateHistoryButtons() {
        const undoBtn = document.getElementById('mayhenFtUndoBtn');
        const redoBtn = document.getElementById('mayhenFtRedoBtn');
        if (undoBtn) {
            undoBtn.disabled = undoStack.length === 0;
            const last = undoStack[undoStack.length - 1];
            undoBtn.title = last
                ? `Hoàn tác: ${last.label} (Ctrl+Z)`
                : 'Chưa có thao tác để hoàn tác (Ctrl+Z)';
        }
        if (redoBtn) {
            redoBtn.disabled = redoStack.length === 0;
            const next = redoStack[redoStack.length - 1];
            redoBtn.title = next
                ? `Làm lại: ${next.label} (Ctrl+Y)`
                : 'Chưa có thao tác để làm lại (Ctrl+Y)';
        }
    }

    function pushUndoSnapshot(actionLabel) {
        if (!isStudio || isBootstrapping || isApplyingHistory) return;
        undoStack.push(createHistorySnapshot(actionLabel));
        if (undoStack.length > MAX_HISTORY_STEPS) {
            undoStack.shift();
        }
        redoStack.length = 0;
        updateHistoryButtons();
    }

    function recordTextUndoSnapshot(fieldKey, actionLabel) {
        if (!isStudio || isBootstrapping || isApplyingHistory) return;
        const now = Date.now();
        if (lastTextUndoKey !== fieldKey || (now - lastTextUndoAt) > 900) {
            pushUndoSnapshot(actionLabel);
            lastTextUndoKey = fieldKey;
        }
        lastTextUndoAt = now;
    }

    function syncStudioInputsFromState() {
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el && typeof val === 'string') el.value = val;
        };
        setVal('mayhenFtTitleInput', state.title || '');
        setVal('mayhenFtSurnameInput', state.family_surname || '');
        setVal('mayhenFtHallInput', state.ancestral_hall || '');
        setVal('mayhenFtIntroInput', state.intro_chronicle || '');
        setVal('mayhenFtRulesInput', state.clan_rules || '');

        const visSelectEl = document.getElementById('mayhenFtVisibilitySelect');
        const passWrapEl = document.getElementById('mayhenFtPasswordWrap');
        if (visSelectEl && state.visibility) {
            visSelectEl.value = state.visibility;
            if (passWrapEl) passWrapEl.style.display = visSelectEl.value === 'password' ? 'block' : 'none';
        }
        if (state.template_style) {
            app.className = app.className.replace(/mayhen_family_tree_theme_[a-z0-9-]+/g, '').trim() + ` mayhen_family_tree_theme_${state.template_style}`;
            document.querySelectorAll('[data-theme-slug]').forEach((c) => {
                c.classList.toggle('is-active', c.getAttribute('data-theme-slug') === state.template_style);
            });
        }
        document.querySelectorAll('[data-layout-btn]').forEach((b) => {
            b.classList.toggle('is-active', b.getAttribute('data-layout-btn') === layoutMode);
        });
    }

    function applyHistoryData(data) {
        isApplyingHistory = true;
        try {
            state.title = data.title;
            state.family_surname = data.family_surname;
            state.ancestral_hall = data.ancestral_hall;
            state.template_style = data.template_style;
            state.visibility = data.visibility;
            state.intro_chronicle = data.intro_chronicle;
            state.clan_rules = data.clan_rules;
            state.members = JSON.parse(JSON.stringify(data.members || []));
            layoutMode = data.layout_mode || 'vertical';
            focusRootId = (data.focus_root_id && getMember(data.focus_root_id)) ? data.focus_root_id : null;
            selectedMemberId = (data.selected_member_id && getMember(data.selected_member_id))
                ? data.selected_member_id
                : (state.members[0]?.id || null);

            syncStudioInputsFromState();
            renderTreeCanvas();
            if (layoutMode !== 'chronicle') fitTreeToScreen();
            refreshSidebarStatsAndList();
            if (drawer?.classList.contains('is-open')) {
                if (selectedMemberId && getMember(selectedMemberId)) {
                    openMemberDrawer(selectedMemberId);
                } else {
                    drawer.classList.remove('is-open');
                }
            }
            saveDraftToLocal(true);
        } finally {
            isApplyingHistory = false;
            lastTextUndoKey = '';
            updateHistoryButtons();
        }
    }

    function performUndo() {
        if (!isStudio || undoStack.length === 0) {
            showToast('Không còn thao tác trước đó để hoàn tác.');
            return;
        }
        const prevSnap = undoStack.pop();
        redoStack.push(createHistorySnapshot(prevSnap.label));
        applyHistoryData(prevSnap.data);
        showToast(`↶ Đã hoàn tác: ${prevSnap.label}`);
    }

    function performRedo() {
        if (!isStudio || redoStack.length === 0) {
            showToast('Không có thao tác nào để làm lại.');
            return;
        }
        const nextSnap = redoStack.pop();
        undoStack.push(createHistorySnapshot(nextSnap.label));
        applyHistoryData(nextSnap.data);
        showToast(`↷ Đã làm lại: ${nextSnap.label}`);
    }

    function getMember(id) {
        return (state.members || []).find((m) => m.id === id) || null;
    }

    function getChildrenOf(memberId) {
        return (state.members || [])
            .filter((m) => Array.isArray(m.parent_ids) && m.parent_ids.includes(memberId))
            .sort((a, b) => (a.birth_order || 1) - (b.birth_order || 1));
    }

    function getSubtreeMembers() {
        if (!focusRootId || !getMember(focusRootId)) {
            return state.members || [];
        }
        const included = new Set();
        const queue = [focusRootId];
        while (queue.length > 0) {
            const curr = queue.shift();
            if (included.has(curr)) continue;
            included.add(curr);
            const m = getMember(curr);
            if (m && Array.isArray(m.spouse_ids)) {
                m.spouse_ids.forEach((sid) => {
                    if (getMember(sid)) included.add(sid);
                });
            }
            getChildrenOf(curr).forEach((c) => queue.push(c.id));
        }
        return (state.members || []).filter((m) => included.has(m.id));
    }

    /**
     * Thuật toán bố cục cây gia phả nhiều đời + cặp vợ chồng (Zero Libraries).
     */
    function computeTreeLayout() {
        const activeMembers = getSubtreeMembers();
        const memberMap = new Map(activeMembers.map((m) => [m.id, m]));
        const isHorizontal = layoutMode === 'horizontal';

        const nodeW = 196;
        const nodeH = 96;
        const spouseGap = 18;
        const siblingGap = isHorizontal ? 28 : 34;
        const levelGap = isHorizontal ? 255 : 155;

        // Xác định các node gốc (không có parent thuộc activeMembers và ưu tiên trưởng tộc / nam giới làm đại diện nhánh)
        const visitedUnit = new Set();
        const childrenByUnit = new Map();

        function getPrimarySpouse(m) {
            if (!Array.isArray(m.spouse_ids)) return null;
            for (const sid of m.spouse_ids) {
                if (memberMap.has(sid)) return memberMap.get(sid);
            }
            return null;
        }

        function getUnitKey(m) {
            const sp = getPrimarySpouse(m);
            if (!sp) return m.id;
            const hasParentsA = Array.isArray(m.parent_ids) && m.parent_ids.some((p) => memberMap.has(p));
            const hasParentsB = Array.isArray(sp.parent_ids) && sp.parent_ids.some((p) => memberMap.has(p));
            if (hasParentsA && !hasParentsB) return m.id;
            if (hasParentsB && !hasParentsA) return sp.id;
            return m.gender === 'M' ? m.id : sp.id;
        }

        const roots = activeMembers.filter((m) => {
            const hasParentInSet = Array.isArray(m.parent_ids) && m.parent_ids.some((p) => memberMap.has(p));
            if (hasParentInSet) return false;
            const sp = getPrimarySpouse(m);
            if (sp && Array.isArray(sp.parent_ids) && sp.parent_ids.some((p) => memberMap.has(p))) {
                return false;
            }
            return getUnitKey(m) === m.id;
        }).sort((a, b) => {
            const gDiff = (Number(a.generation || 1)) - (Number(b.generation || 1));
            if (gDiff !== 0) return gDiff;
            return (Number(a.birth_order || 1)) - (Number(b.birth_order || 1));
        });

        // Fallback nếu dữ liệu có vòng lặp
        const unitRoots = roots.length > 0 ? roots : (activeMembers[0] ? [activeMembers[0]] : []);

        function buildUnitTree(primaryMember) {
            if (!primaryMember || visitedUnit.has(primaryMember.id)) return null;
            visitedUnit.add(primaryMember.id);
            const spouse = getPrimarySpouse(primaryMember);
            if (spouse) visitedUnit.add(spouse.id);

            const childIds = new Set();
            activeMembers.forEach((cand) => {
                if (!Array.isArray(cand.parent_ids)) return;
                if (cand.parent_ids.includes(primaryMember.id) || (spouse && cand.parent_ids.includes(spouse.id))) {
                    const key = getUnitKey(cand);
                    if (!visitedUnit.has(key)) childIds.add(key);
                }
            });

            const childUnits = Array.from(childIds)
                .map((cid) => memberMap.get(cid))
                .filter(Boolean)
                .sort((a, b) => (a.birth_order || 1) - (b.birth_order || 1))
                .map((cm) => buildUnitTree(cm))
                .filter(Boolean);

            const unit = {
                primary: primaryMember,
                spouse,
                children: childUnits,
                width: spouse ? (isHorizontal ? nodeW : nodeW * 2 + spouseGap) : nodeW,
                height: spouse ? (isHorizontal ? nodeH * 2 + spouseGap : nodeH) : nodeH,
                span: 0,
                x: 0,
                y: 0,
            };
            childrenByUnit.set(primaryMember.id, unit);
            return unit;
        }

        const trees = unitRoots.map((r) => buildUnitTree(r)).filter(Boolean);

        // Nếu còn thành viên rời rạc chưa nối vào cây chính
        activeMembers.forEach((m) => {
            if (!visitedUnit.has(m.id)) {
                const extra = buildUnitTree(m);
                if (extra) trees.push(extra);
            }
        });

        function measureSpan(unit) {
            const ownBreadth = isHorizontal ? unit.height : unit.width;
            if (unit.children.length === 0) {
                unit.span = ownBreadth;
                return unit.span;
            }
            let sum = 0;
            unit.children.forEach((c, idx) => {
                sum += measureSpan(c);
                if (idx > 0) sum += siblingGap;
            });
            unit.span = Math.max(ownBreadth, sum);
            return unit.span;
        }

        function assignCoords(unit, depth, offset) {
            const ownBreadth = isHorizontal ? unit.height : unit.width;
            const center = offset + unit.span / 2;

            if (isHorizontal) {
                unit.x = depth * levelGap;
                unit.y = center - ownBreadth / 2;
            } else {
                unit.x = center - ownBreadth / 2;
                unit.y = depth * levelGap;
            }

            let childCursor = offset + Math.max(0, (unit.span - unit.children.reduce((acc, c, i) => acc + c.span + (i > 0 ? siblingGap : 0), 0)) / 2);
            unit.children.forEach((c) => {
                assignCoords(c, depth + 1, childCursor);
                childCursor += c.span + siblingGap;
            });
        }

        const minGen = activeMembers.length > 0
            ? Math.min(...activeMembers.map((m) => Math.max(1, Number(m.generation || 1))))
            : 1;

        let totalCursor = 0;
        trees.forEach((t) => {
            measureSpan(t);
            const rootDepth = Math.max(0, Number(t.primary?.generation || 1) - minGen);
            assignCoords(t, rootDepth, totalCursor);
            totalCursor += t.span + siblingGap * 1.6;
        });

        const positions = {};
        const connectors = [];

        function collectRenderables(unit) {
            if (unit.spouse) {
                if (isHorizontal) {
                    positions[unit.primary.id] = { x: unit.x, y: unit.y, w: nodeW, h: nodeH, member: unit.primary };
                    positions[unit.spouse.id] = { x: unit.x, y: unit.y + nodeH + spouseGap, w: nodeW, h: nodeH, member: unit.spouse };
                    connectors.push({
                        type: 'spouse',
                        x1: unit.x + nodeW / 2,
                        y1: unit.y + nodeH,
                        x2: unit.x + nodeW / 2,
                        y2: unit.y + nodeH + spouseGap,
                    });
                } else {
                    positions[unit.primary.id] = { x: unit.x, y: unit.y, w: nodeW, h: nodeH, member: unit.primary };
                    positions[unit.spouse.id] = { x: unit.x + nodeW + spouseGap, y: unit.y, w: nodeW, h: nodeH, member: unit.spouse };
                    connectors.push({
                        type: 'spouse',
                        x1: unit.x + nodeW,
                        y1: unit.y + nodeH / 2,
                        x2: unit.x + nodeW + spouseGap,
                        y2: unit.y + nodeH / 2,
                    });
                }
            } else {
                positions[unit.primary.id] = { x: unit.x, y: unit.y, w: nodeW, h: nodeH, member: unit.primary };
            }

            if (unit.children.length > 0) {
                const px = isHorizontal ? unit.x + nodeW : unit.x + unit.width / 2;
                const py = isHorizontal ? unit.y + unit.height / 2 : unit.y + nodeH;

                unit.children.forEach((c) => {
                    const cx = isHorizontal ? c.x : c.x + c.width / 2;
                    const cy = isHorizontal ? c.y + c.height / 2 : c.y;
                    connectors.push({
                        type: 'child',
                        x1: px,
                        y1: py,
                        x2: cx,
                        y2: cy,
                    });
                    collectRenderables(c);
                });
            }
        }

        trees.forEach((t) => collectRenderables(t));

        return { positions, connectors };
    }

    function applyTransform() {
        if (!world) return;
        world.style.transform = `translate3d(${panX}px, ${panY}px, 0) scale(${zoom})`;
        const zoomLabel = document.getElementById('mayhenFtZoomLabel');
        if (zoomLabel) zoomLabel.textContent = `${Math.round(zoom * 100)}%`;
    }

    function fitTreeToScreen() {
        if (!viewport) return;
        const keys = Object.keys(lastRenderPositions);
        if (keys.length === 0) return;

        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        keys.forEach((id) => {
            const p = lastRenderPositions[id];
            minX = Math.min(minX, p.x);
            minY = Math.min(minY, p.y);
            maxX = Math.max(maxX, p.x + p.w);
            maxY = Math.max(maxY, p.y + p.h);
        });

        const vw = viewport.clientWidth || 900;
        const vh = viewport.clientHeight || 580;
        const treeW = Math.max(200, maxX - minX);
        const treeH = Math.max(160, maxY - minY);

        zoom = Math.min(1.05, Math.max(0.32, Math.min((vw - 120) / treeW, (vh - 140) / treeH)));
        panX = (vw - treeW * zoom) / 2 - minX * zoom;
        panY = Math.max(68, (vh - treeH * zoom) / 2 - minY * zoom);
        applyTransform();
    }

    function renderTreeCanvas() {
        if (!nodesContainer || !svgLines) return;

        if (layoutMode === 'chronicle') {
            if (viewport) viewport.style.display = 'none';
            if (chronicleView) {
                chronicleView.classList.add('is-active');
                renderChronicleBook();
            }
            saveDraftToLocal();
            return;
        }

        if (viewport) viewport.style.display = '';
        if (chronicleView) chronicleView.classList.remove('is-active');

        const { positions, connectors } = computeTreeLayout();
        lastRenderPositions = positions;

        // Vẽ đường nối SVG Bezier
        const isHorizontal = layoutMode === 'horizontal';
        const pathsHtml = connectors.map((c) => {
            if (c.type === 'spouse') {
                return `<line x1="${c.x1}" y1="${c.y1}" x2="${c.x2}" y2="${c.y2}" stroke="var(--mh-ft-primary)" stroke-width="2.2" stroke-dasharray="4 3" />`;
            }
            if (isHorizontal) {
                const mx = (c.x1 + c.x2) / 2;
                return `<path d="M ${c.x1} ${c.y1} C ${mx} ${c.y1}, ${mx} ${c.y2}, ${c.x2} ${c.y2}" fill="none" stroke="var(--mh-ft-line)" stroke-width="2" />`;
            }
            const my = (c.y1 + c.y2) / 2;
            return `<path d="M ${c.x1} ${c.y1} C ${c.x1} ${my}, ${c.x2} ${my}, ${c.x2} ${c.y2}" fill="none" stroke="var(--mh-ft-line)" stroke-width="2" />`;
        }).join('');
        svgLines.innerHTML = pathsHtml;

        // Vẽ các Node Thành Viên
        nodesContainer.innerHTML = Object.values(positions).map(({ x, y, member }) => {
            const initial = escapeHtml((member.name || '?').trim().split(/\s+/).pop().charAt(0).toUpperCase());
            const avatarInner = member.avatar_url
                ? `<img src="${escapeHtml(member.avatar_url)}" alt="">`
                : initial;
            const lifeYears = `${escapeHtml(member.birth_solar ? String(member.birth_solar).slice(0, 4) : '?')} – ${member.is_alive ? 'Nay' : escapeHtml(member.death_solar ? String(member.death_solar).slice(0, 4) : '?')}`;
            const selectedCls = member.id === selectedMemberId ? 'is-selected' : '';
            const deceasedCls = !member.is_alive ? 'is-deceased' : '';
            const femaleCls = member.gender === 'F' ? 'is-female' : '';

            return `
                <div class="mayhen_family_tree_node ${selectedCls} ${deceasedCls}"
                     data-member-id="${escapeHtml(member.id)}"
                     style="left: ${x}px; top: ${y}px;">
                    <div class="mayhen_family_tree_node_top">
                        <div class="mayhen_family_tree_avatar ${femaleCls}">${avatarInner}</div>
                        <div class="mayhen_family_tree_node_meta">
                            <div class="mayhen_family_tree_node_name">${escapeHtml(member.name)}</div>
                            <div class="mayhen_family_tree_node_other">${escapeHtml(member.other_name || member.occupation || (member.gender === 'F' ? 'Nữ' : 'Nam'))}</div>
                        </div>
                    </div>
                    <div class="mayhen_family_tree_node_badges">
                        <span class="mayhen_family_tree_gen_pill">Đời ${Number(member.generation || 1)}</span>
                        <span>${lifeYears}${!member.is_alive && member.death_anniversary ? ` • Giỗ ${escapeHtml(member.death_anniversary)}` : ''}</span>
                    </div>
                </div>
            `;
        }).join('');

        // Cập nhật tiêu đề hiển thị trên sân khấu
        const headTitle = document.getElementById('mayhenFtCanvasTitle');
        const headSub = document.getElementById('mayhenFtCanvasSub');
        if (headTitle) headTitle.textContent = state.title || 'Gia Phả Online';
        if (headSub) {
            const focusNote = focusRootId && getMember(focusRootId) ? ` • Đang lọc nhánh: ${getMember(focusRootId).name}` : '';
            headSub.textContent = `${(state.members || []).length} thành viên${state.ancestral_hall ? ` • ${state.ancestral_hall}` : ''}${focusNote}`;
        }

        // Gắn click mở Drawer & kéo thả trực tiếp trên sân khấu cây (Studio)
        nodesContainer.querySelectorAll('.mayhen_family_tree_node').forEach((el) => {
            const nodeId = el.getAttribute('data-member-id');
            if (!nodeId) return;

            if (!isStudio) {
                el.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openMemberDrawer(nodeId);
                });
                return;
            }

            el.addEventListener('mousedown', (downEv) => {
                if (downEv.button !== 0) return;
                downEv.stopPropagation();

                const startClientX = downEv.clientX;
                const startClientY = downEv.clientY;
                const origPos = lastRenderPositions[nodeId];
                if (!origPos) return;

                let isDraggingNode = false;
                let dragBadge = null;
                let currentTargetId = null;
                let currentDropAction = null;
                let currentTargetGen = null;

                const clearCanvasDropHighlights = () => {
                    nodesContainer.querySelectorAll('.is-canvas-drop-child, .is-canvas-drop-before, .is-canvas-drop-after').forEach((n) => {
                        n.classList.remove('is-canvas-drop-child', 'is-canvas-drop-before', 'is-canvas-drop-after');
                    });
                };

                const ensureBadge = () => {
                    if (!dragBadge) {
                        dragBadge = document.createElement('div');
                        dragBadge.className = 'mayhen_family_tree_canvas_drag_badge';
                        document.body.appendChild(dragBadge);
                    }
                    return dragBadge;
                };

                const onMouseMove = (moveEv) => {
                    const dx = moveEv.clientX - startClientX;
                    const dy = moveEv.clientY - startClientY;

                    if (!isDraggingNode && Math.hypot(dx, dy) < 6) return;

                    if (!isDraggingNode) {
                        isDraggingNode = true;
                        el.classList.add('is-canvas-dragging');
                    }

                    const newLeft = origPos.x + dx / zoom;
                    const newTop = origPos.y + dy / zoom;
                    el.style.left = `${newLeft}px`;
                    el.style.top = `${newTop}px`;

                    const badge = ensureBadge();
                    badge.style.left = `${moveEv.clientX}px`;
                    badge.style.top = `${moveEv.clientY}px`;

                    // Kiểm tra xem đang rê chuột đè lên thành viên nào trên cây
                    clearCanvasDropHighlights();
                    currentTargetId = null;
                    currentDropAction = null;
                    currentTargetGen = null;

                    const hitEl = document.elementFromPoint(moveEv.clientX, moveEv.clientY)?.closest('.mayhen_family_tree_node');
                    const hitId = hitEl?.getAttribute('data-member-id') || null;

                    if (hitEl && hitId && hitId !== nodeId) {
                        const targetMember = getMember(hitId);
                        const rect = hitEl.getBoundingClientRect();
                        const xRatio = (moveEv.clientX - rect.left) / Math.max(1, rect.width);

                        currentTargetId = hitId;
                        if (xRatio < 0.22) {
                            currentDropAction = 'before';
                            hitEl.classList.add('is-canvas-drop-before');
                            badge.className = 'mayhen_family_tree_canvas_drag_badge is-reorder';
                            badge.textContent = `⇄ Đặt trước ${targetMember?.name || ''}`;
                        } else if (xRatio > 0.78) {
                            currentDropAction = 'after';
                            hitEl.classList.add('is-canvas-drop-after');
                            badge.className = 'mayhen_family_tree_canvas_drag_badge is-reorder';
                            badge.textContent = `⇄ Đặt sau ${targetMember?.name || ''}`;
                        } else {
                            currentDropAction = 'child';
                            hitEl.classList.add('is-canvas-drop-child');
                            badge.className = 'mayhen_family_tree_canvas_drag_badge';
                            badge.textContent = `↳ Gán làm Con của ${targetMember?.name || ''}`;
                        }
                    } else {
                        // Thả ở khoảng trống: tự động tính tầng Đời theo vị trí kéo trên sân khấu
                        const activeMembers = getSubtreeMembers();
                        const minGen = activeMembers.length > 0
                            ? Math.min(...activeMembers.map((item) => Math.max(1, Number(item.generation || 1))))
                            : 1;
                        const levelGap = layoutMode === 'horizontal' ? 255 : 155;
                        const axisPos = layoutMode === 'horizontal' ? newLeft : newTop;
                        const computedGen = Math.max(1, Math.min(30, minGen + Math.round(axisPos / levelGap)));

                        currentTargetGen = computedGen;
                        badge.className = 'mayhen_family_tree_canvas_drag_badge is-gen';
                        badge.textContent = `↕ Chuyển sang Đời ${computedGen}`;
                    }
                };

                const onMouseUp = () => {
                    window.removeEventListener('mousemove', onMouseMove);
                    window.removeEventListener('mouseup', onMouseUp);
                    clearCanvasDropHighlights();
                    el.classList.remove('is-canvas-dragging');
                    if (dragBadge) {
                        dragBadge.remove();
                        dragBadge = null;
                    }

                    if (!isDraggingNode) {
                        openMemberDrawer(nodeId);
                        return;
                    }

                    if (currentTargetId && currentDropAction) {
                        if (currentDropAction === 'before' || currentDropAction === 'after') {
                            reorderMemberRelativeTo(nodeId, currentTargetId, currentDropAction);
                        } else {
                            assignMemberAsChildOf(nodeId, currentTargetId);
                        }
                    } else if (currentTargetGen !== null) {
                        const draggedMember = getMember(nodeId);
                        if (draggedMember && Number(draggedMember.generation || 1) !== currentTargetGen) {
                            moveMemberToGeneration(nodeId, currentTargetGen);
                        } else {
                            renderTreeCanvas();
                        }
                    } else {
                        renderTreeCanvas();
                    }
                };

                window.addEventListener('mousemove', onMouseMove);
                window.addEventListener('mouseup', onMouseUp);
            });
        });

        saveDraftToLocal();
    }

    function renderChronicleBook() {
        if (!chronicleView) return;
        const generations = {};
        (state.members || []).forEach((m) => {
            const g = Number(m.generation || 1);
            if (!generations[g]) generations[g] = [];
            generations[g].push(m);
        });

        const genHtml = Object.keys(generations)
            .sort((a, b) => Number(a) - Number(b))
            .map((g) => {
                const rows = generations[g].map((m) => `
                    <div style="padding: 10px 12px; border: 1px solid #EADFD0; border-radius: 10px; margin-bottom: 8px; background: #FCFBF9;">
                        <strong>${escapeHtml(m.name)}</strong> ${m.other_name ? `<span style="color:#85182A;">(${escapeHtml(m.other_name)})</span>` : ''}
                        <div style="font-size: 12.5px; color: #5C5349; margin-top: 3px;">
                            ${m.birth_solar ? `Sinh: ${escapeHtml(m.birth_solar)}` : ''}${!m.is_alive && m.death_anniversary ? ` • Ngày giỗ Âm lịch: <strong>${escapeHtml(m.death_anniversary)}</strong>` : ''}${m.occupation ? ` • ${escapeHtml(m.occupation)}` : ''}
                        </div>
                        ${m.bio ? `<p style="font-size: 13px; margin: 6px 0 0; color: #3D352E;">${escapeHtml(m.bio)}</p>` : ''}
                    </div>
                `).join('');
                return `<div class="mayhen_family_tree_book_section"><h3>Đời Thứ ${g}</h3>${rows}</div>`;
            }).join('');

        chronicleView.innerHTML = `
            <div class="mayhen_family_tree_book_sheet">
                <h2>${escapeHtml(state.title)}</h2>
                <p style="text-align:center; color:#6A635B; font-size:13.5px; margin:0 0 18px;">${escapeHtml(state.ancestral_hall || '')}</p>
                ${state.intro_chronicle ? `<div class="mayhen_family_tree_book_section"><h3>Lời Nói Đầu & Phả Ký</h3><p style="white-space: pre-line; line-height: 1.75; font-size: 14.5px;">${escapeHtml(state.intro_chronicle)}</p></div>` : ''}
                ${state.clan_rules ? `<div class="mayhen_family_tree_book_section"><h3>Tộc Ước & Gia Huấn</h3><p style="white-space: pre-line; line-height: 1.75; font-size: 14.5px;">${escapeHtml(state.clan_rules)}</p></div>` : ''}
                ${genHtml}
            </div>
        `;
    }

    /**
     * Mở Drawer xem chi tiết hoặc chỉnh sửa thành viên.
     */
    function openMemberDrawer(memberId) {
        const m = getMember(memberId);
        if (!m || !drawer || !drawerBody || !drawerTitle) return;
        selectedMemberId = memberId;
        renderTreeCanvas();
        if (isStudio) refreshSidebarStatsAndList();

        drawerTitle.textContent = isStudio ? `Chỉnh sửa: ${m.name}` : m.name;

        if (!isStudio) {
            const parents = (m.parent_ids || []).map((pid) => getMember(pid)?.name).filter(Boolean).join(' & ');
            const spouses = (m.spouse_ids || []).map((sid) => getMember(sid)?.name).filter(Boolean).join(', ');
            drawerBody.innerHTML = `
                <div style="margin-bottom: 14px;">
                    <span class="mayhen_family_tree_gen_pill">Đời thứ ${Number(m.generation || 1)}</span>
                    ${m.other_name ? `<p style="color:#85182A; font-weight:600; margin:8px 0 4px;">${escapeHtml(m.other_name)}</p>` : ''}
                </div>
                <div style="font-size: 13.5px; line-height: 1.75; color:#3D352E;">
                    ${parents ? `<div><strong>Cha / Mẹ:</strong> ${escapeHtml(parents)}</div>` : ''}
                    ${spouses ? `<div><strong>Vợ / Chồng:</strong> ${escapeHtml(spouses)}</div>` : ''}
                    ${m.birth_solar || m.birth_lunar ? `<div><strong>Ngày sinh:</strong> ${escapeHtml(m.birth_solar)} ${m.birth_lunar ? `(${escapeHtml(m.birth_lunar)})` : ''}</div>` : ''}
                    ${!m.is_alive ? `<div><strong>Ngày mất / Giỗ Âm lịch:</strong> ${escapeHtml(m.death_anniversary || m.death_lunar || m.death_solar || 'Chưa rõ')}</div>` : '<div><strong>Tình trạng:</strong> Còn sống</div>'}
                    ${m.occupation ? `<div><strong>Nghề nghiệp / Vai trò:</strong> ${escapeHtml(m.occupation)}</div>` : ''}
                    ${m.hometown ? `<div><strong>Quê quán:</strong> ${escapeHtml(m.hometown)}</div>` : ''}
                    ${m.burial_place ? `<div><strong>Nơi an táng:</strong> ${escapeHtml(m.burial_place)}</div>` : ''}
                    ${m.bio ? `<div style="margin-top:12px; padding:12px; background:#FAF6EE; border-radius:10px;"><strong>Tiểu sử:</strong><br>${escapeHtml(m.bio)}</div>` : ''}
                </div>
                <div style="margin-top: 16px;">
                    <button type="button" class="mayhen_family_tree_btn_outline" style="width:100%;" id="mayhenFtBtnFocusBranch">
                        ${focusRootId === m.id ? '↺ Hiện lại toàn bộ gia phả' : '🔍 Chỉ xem nhánh từ người này'}
                    </button>
                </div>
            `;
            drawerBody.querySelector('#mayhenFtBtnFocusBranch')?.addEventListener('click', () => {
                focusRootId = focusRootId === m.id ? null : m.id;
                renderTreeCanvas();
                fitTreeToScreen();
                openMemberDrawer(m.id);
            });
            drawer.classList.add('is-open');
            return;
        }

        // Mode Studio: có nút thêm nhanh + form sửa trực tiếp
        drawerBody.innerHTML = `
            <div class="mayhen_family_tree_quick_actions">
                <button type="button" class="mayhen_family_tree_pill_btn" data-quick-add="child">+ Thêm Con</button>
                <button type="button" class="mayhen_family_tree_pill_btn" data-quick-add="spouse">+ Vợ/Chồng</button>
                <button type="button" class="mayhen_family_tree_pill_btn" data-quick-add="parent">+ Cha/Mẹ</button>
                <button type="button" class="mayhen_family_tree_pill_btn" data-quick-add="sibling">+ Anh/Chị/Em</button>
                <button type="button" class="mayhen_family_tree_pill_btn" id="mayhenFtBtnFocusStudio">
                    ${focusRootId === m.id ? '↺ Hiện Toàn Cây' : '🔍 Lọc Nhánh'}
                </button>
            </div>
            <form id="mayhenFtEditMemberForm">
                <div class="mayhen_family_tree_field">
                    <label>Họ và tên *</label>
                    <input type="text" class="mayhen_family_tree_input" name="name" value="${escapeHtml(m.name)}" required>
                </div>
                <div class="mayhen_family_tree_grid_2">
                    <div class="mayhen_family_tree_field">
                        <label>Tên tự / Tên húy / Vai vế</label>
                        <input type="text" class="mayhen_family_tree_input" name="other_name" value="${escapeHtml(m.other_name)}">
                    </div>
                    <div class="mayhen_family_tree_field">
                        <label>Giới tính</label>
                        <select class="mayhen_family_tree_select" name="gender">
                            <option value="M" ${m.gender === 'M' ? 'selected' : ''}>Nam</option>
                            <option value="F" ${m.gender === 'F' ? 'selected' : ''}>Nữ</option>
                        </select>
                    </div>
                </div>
                <div class="mayhen_family_tree_grid_2">
                    <div class="mayhen_family_tree_field">
                        <label>Đời thứ</label>
                        <input type="number" class="mayhen_family_tree_input" name="generation" min="1" max="30" value="${Number(m.generation || 1)}">
                    </div>
                    <div class="mayhen_family_tree_field">
                        <label>Con thứ mấy</label>
                        <input type="number" class="mayhen_family_tree_input" name="birth_order" min="1" max="30" value="${Number(m.birth_order || 1)}">
                    </div>
                </div>
                <div class="mayhen_family_tree_grid_2">
                    <div class="mayhen_family_tree_field">
                        <label>Ngày/Năm sinh (Dương)</label>
                        <input type="text" class="mayhen_family_tree_input" name="birth_solar" placeholder="VD: 1980-05-12" value="${escapeHtml(m.birth_solar)}">
                    </div>
                    <div class="mayhen_family_tree_field">
                        <label>Năm sinh (Âm lịch)</label>
                        <input type="text" class="mayhen_family_tree_input" name="birth_lunar" placeholder="VD: Canh Thân" value="${escapeHtml(m.birth_lunar)}">
                    </div>
                </div>
                <div class="mayhen_family_tree_field">
                    <label>
                        <input type="checkbox" name="is_alive" ${m.is_alive ? 'checked' : ''}> Thành viên còn sống
                    </label>
                </div>
                <div class="mayhen_family_tree_grid_2">
                    <div class="mayhen_family_tree_field">
                        <label>Ngày giỗ (Âm lịch)</label>
                        <input type="text" class="mayhen_family_tree_input" name="death_anniversary" placeholder="VD: 15/08" value="${escapeHtml(m.death_anniversary)}">
                    </div>
                    <div class="mayhen_family_tree_field">
                        <label>Năm mất (Dương)</label>
                        <input type="text" class="mayhen_family_tree_input" name="death_solar" placeholder="VD: 2018-09-24" value="${escapeHtml(m.death_solar)}">
                    </div>
                </div>
                <div class="mayhen_family_tree_field">
                    <label>Nghề nghiệp / Công đức</label>
                    <input type="text" class="mayhen_family_tree_input" name="occupation" value="${escapeHtml(m.occupation)}">
                </div>
                <div class="mayhen_family_tree_field">
                    <label>Quê quán / Nơi an táng</label>
                    <input type="text" class="mayhen_family_tree_input" name="burial_place" value="${escapeHtml(m.burial_place || m.hometown)}">
                </div>
                <div class="mayhen_family_tree_field">
                    <label>Ảnh chân dung (tải lên từ máy)</label>
                    <input type="file" class="mayhen_family_tree_input" id="mayhenFtAvatarFile" accept="image/*">
                </div>
                <div class="mayhen_family_tree_field">
                    <label>Tiểu sử ngắn</label>
                    <textarea class="mayhen_family_tree_textarea" name="bio" rows="3">${escapeHtml(m.bio)}</textarea>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="mayhen_family_tree_btn_primary" style="flex:1;">Lưu Thành Viên</button>
                    <button type="button" class="mayhen_family_tree_btn_outline" id="mayhenFtDeleteMemberBtn" style="color:#B91C1C;">Xóa</button>
                </div>
            </form>
        `;

        drawerBody.querySelectorAll('[data-quick-add]').forEach((btn) => {
            btn.addEventListener('click', () => addRelative(m.id, btn.getAttribute('data-quick-add')));
        });

        drawerBody.querySelector('#mayhenFtBtnFocusStudio')?.addEventListener('click', () => {
            focusRootId = focusRootId === m.id ? null : m.id;
            renderTreeCanvas();
            fitTreeToScreen();
            openMemberDrawer(m.id);
        });

        drawerBody.querySelector('#mayhenFtAvatarFile')?.addEventListener('change', (ev) => {
            const file = ev.target.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = () => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = 160;
                    canvas.height = 160;
                    const ctx = canvas.getContext('2d');
                    const side = Math.min(img.width, img.height);
                    ctx.drawImage(img, (img.width - side) / 2, (img.height - side) / 2, side, side, 0, 0, 160, 160);
                    pushUndoSnapshot(`Đổi ảnh ${m.name}`);
                    m.avatar_url = canvas.toDataURL('image/webp', 0.85);
                    renderTreeCanvas();
                    showToast('Đã cập nhật ảnh chân dung!');
                };
                img.src = String(reader.result || '');
            };
            reader.readAsDataURL(file);
        });

        drawerBody.querySelector('#mayhenFtEditMemberForm')?.addEventListener('submit', (ev) => {
            ev.preventDefault();
            pushUndoSnapshot(`Sửa thông tin ${m.name}`);
            const fd = new FormData(ev.target);
            m.name = String(fd.get('name') || '').trim() || m.name;
            m.other_name = String(fd.get('other_name') || '').trim();
            m.gender = fd.get('gender') === 'F' ? 'F' : 'M';
            m.generation = Math.max(1, Number(fd.get('generation') || 1));
            m.birth_order = Math.max(1, Number(fd.get('birth_order') || 1));
            m.birth_solar = String(fd.get('birth_solar') || '').trim();
            m.birth_lunar = String(fd.get('birth_lunar') || '').trim();
            m.is_alive = fd.get('is_alive') === 'on';
            m.death_anniversary = String(fd.get('death_anniversary') || '').trim();
            m.death_solar = String(fd.get('death_solar') || '').trim();
            m.occupation = String(fd.get('occupation') || '').trim();
            m.burial_place = String(fd.get('burial_place') || '').trim();
            m.bio = String(fd.get('bio') || '').trim();

            renderTreeCanvas();
            refreshSidebarStatsAndList();
            showToast(`Đã lưu thông tin ${m.name}`);
        });

        drawerBody.querySelector('#mayhenFtDeleteMemberBtn')?.addEventListener('click', () => {
            if ((state.members || []).length <= 1) {
                showToast('Gia phả cần có ít nhất 1 thành viên.');
                return;
            }
            pushUndoSnapshot(`Xóa ${m.name}`);
            state.members = state.members.filter((item) => item.id !== m.id).map((item) => ({
                ...item,
                parent_ids: (item.parent_ids || []).filter((id) => id !== m.id),
                spouse_ids: (item.spouse_ids || []).filter((id) => id !== m.id),
            }));
            if (focusRootId === m.id) focusRootId = null;
            selectedMemberId = state.members[0]?.id || null;
            drawer.classList.remove('is-open');
            renderTreeCanvas();
            refreshSidebarStatsAndList();
            showToast('Đã xóa thành viên khỏi cây gia phả.');
        });

        drawer.classList.add('is-open');
    }

    const MAX_FAMILY_TREE_MEMBERS = 500;

    function addRelative(anchorId, relType) {
        const anchor = getMember(anchorId);
        if (!anchor) return;
        if ((state.members || []).length >= MAX_FAMILY_TREE_MEMBERS) {
            showToast(`Cây gia phả hỗ trợ tối đa ${MAX_FAMILY_TREE_MEMBERS} thành viên để đảm bảo hiển thị mượt mà.`);
            return;
        }
        const labelMap = {
            child: `Thêm con cho ${anchor.name}`,
            spouse: `Thêm vợ/chồng cho ${anchor.name}`,
            parent: `Thêm cha/mẹ cho ${anchor.name}`,
            sibling: `Thêm anh/chị/em cho ${anchor.name}`,
        };
        pushUndoSnapshot(labelMap[relType] || 'Thêm thành viên');
        const newId = 'm_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 5);
        const surname = (state.family_surname || anchor.name.split(' ')[0] || 'Nguyễn').split(' ')[0];

        if (relType === 'child') {
            const spouseId = anchor.spouse_ids?.[0];
            const parentIds = spouseId ? [anchor.id, spouseId] : [anchor.id];
            const existingChildren = getChildrenOf(anchor.id);
            const newMember = {
                id: newId,
                name: `${surname} Thành Viên Mới`,
                other_name: `Con thứ ${existingChildren.length + 1}`,
                gender: 'M',
                generation: Number(anchor.generation || 1) + 1,
                birth_order: existingChildren.length + 1,
                is_alive: true,
                birth_solar: '',
                birth_lunar: '',
                death_solar: '',
                death_lunar: '',
                death_anniversary: '',
                hometown: anchor.hometown || '',
                burial_place: '',
                occupation: '',
                bio: '',
                avatar_url: '',
                parent_ids: parentIds,
                spouse_ids: [],
            };
            state.members.push(newMember);
            renderTreeCanvas();
            refreshSidebarStatsAndList();
            openMemberDrawer(newId);
            showToast('Đã thêm người con mới vào nhánh!');
        } else if (relType === 'spouse') {
            const newMember = {
                id: newId,
                name: anchor.gender === 'M' ? 'Phu Nhân Mới' : 'Phu Quân Mới',
                other_name: anchor.gender === 'M' ? 'Vợ' : 'Chồng',
                gender: anchor.gender === 'M' ? 'F' : 'M',
                generation: Number(anchor.generation || 1),
                birth_order: 1,
                is_alive: true,
                birth_solar: '',
                birth_lunar: '',
                death_solar: '',
                death_lunar: '',
                death_anniversary: '',
                hometown: '',
                burial_place: '',
                occupation: '',
                bio: '',
                avatar_url: '',
                parent_ids: [],
                spouse_ids: [anchor.id],
            };
            anchor.spouse_ids = [...(anchor.spouse_ids || []), newId];
            state.members.push(newMember);
            renderTreeCanvas();
            refreshSidebarStatsAndList();
            openMemberDrawer(newId);
            showToast('Đã thêm Vợ / Chồng!');
        } else if (relType === 'parent') {
            const newGen = Math.max(1, Number(anchor.generation || 1) - 1);
            if (Number(anchor.generation || 1) === 1) {
                state.members.forEach((item) => {
                    item.generation = Number(item.generation || 1) + 1;
                });
            }
            const newMember = {
                id: newId,
                name: `${surname} Bậc Tiền Nhân`,
                other_name: 'Thân Sinh',
                gender: 'M',
                generation: newGen,
                birth_order: 1,
                is_alive: false,
                birth_solar: '',
                birth_lunar: '',
                death_solar: '',
                death_lunar: '',
                death_anniversary: '',
                hometown: anchor.hometown || '',
                burial_place: '',
                occupation: '',
                bio: '',
                avatar_url: '',
                parent_ids: [],
                spouse_ids: [],
            };
            anchor.parent_ids = [...(anchor.parent_ids || []), newId];
            state.members.push(newMember);
            renderTreeCanvas();
            refreshSidebarStatsAndList();
            openMemberDrawer(newId);
            showToast('Đã thêm Cha / Mẹ phía trên!');
        } else if (relType === 'sibling') {
            const gen = Number(anchor.generation || 1);
            const sharedParents = Array.isArray(anchor.parent_ids) ? [...anchor.parent_ids] : [];
            const peers = sharedParents.length > 0
                ? (state.members || []).filter((item) => Array.isArray(item.parent_ids) && item.parent_ids.includes(sharedParents[0]))
                : (state.members || []).filter((item) => Number(item.generation || 1) === gen);
            const newMember = {
                id: newId,
                name: `${surname} Anh / Chị / Em`,
                other_name: `Cùng Đời ${gen}`,
                gender: 'M',
                generation: gen,
                birth_order: peers.length + 1,
                is_alive: true,
                birth_solar: '',
                birth_lunar: '',
                death_solar: '',
                death_lunar: '',
                death_anniversary: '',
                hometown: anchor.hometown || '',
                burial_place: '',
                occupation: '',
                bio: '',
                avatar_url: '',
                parent_ids: sharedParents,
                spouse_ids: [],
            };
            state.members.push(newMember);
            renderTreeCanvas();
            fitTreeToScreen();
            refreshSidebarStatsAndList();
            openMemberDrawer(newId);
            showToast(`Đã thêm Anh / Chị / Em cùng Đời ${gen}!`);
        }
    }

    /**
     * Xuất cây gia phả đang hiển thị ra ảnh PNG độ phân giải cao (Canvas 2x HiDPI thuần).
     */
    function exportTreeToPng() {
        const keys = Object.keys(lastRenderPositions);
        if (keys.length === 0) {
            showToast('Hãy chuyển sang chế độ Sơ đồ Cây để xuất ảnh PNG.');
            return;
        }
        const prevMode = layoutMode;
        if (prevMode === 'chronicle') {
            layoutMode = 'vertical';
            renderTreeCanvas();
        }
        const { positions, connectors } = computeTreeLayout();
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        Object.values(positions).forEach((p) => {
            minX = Math.min(minX, p.x);
            minY = Math.min(minY, p.y);
            maxX = Math.max(maxX, p.x + p.w);
            maxY = Math.max(maxY, p.y + p.h);
        });

        const pad = 70;
        const headerH = 85;
        const width = Math.max(820, maxX - minX + pad * 2);
        const height = Math.max(520, maxY - minY + pad * 2 + headerH);
        const scale = 2;

        const canvas = document.createElement('canvas');
        canvas.width = width * scale;
        canvas.height = height * scale;
        const ctx = canvas.getContext('2d');
        ctx.scale(scale, scale);

        const styles = getComputedStyle(app);
        const bgColor = styles.getPropertyValue('--mh-ft-bg').trim() || '#FAF5EB';
        const primary = styles.getPropertyValue('--mh-ft-primary').trim() || '#85182A';
        const accent = styles.getPropertyValue('--mh-ft-accent').trim() || '#CFA860';
        const lineCol = styles.getPropertyValue('--mh-ft-line').trim() || '#B89254';

        ctx.fillStyle = bgColor;
        ctx.fillRect(0, 0, width, height);

        // Khung viền cổ điển
        ctx.strokeStyle = accent;
        ctx.lineWidth = 4;
        ctx.strokeRect(18, 18, width - 36, height - 36);

        ctx.fillStyle = primary;
        ctx.font = 'bold 26px Georgia, serif';
        ctx.textAlign = 'center';
        ctx.fillText(state.title || 'GIA PHẢ DÒNG HỌ', width / 2, 58);

        ctx.fillStyle = '#5C5349';
        ctx.font = '13px sans-serif';
        ctx.fillText(state.ancestral_hall || 'mayhen.vn • Gia Phả Online', width / 2, 82);

        const ox = pad - minX;
        const oy = pad + headerH - minY;
        const isHoriz = layoutMode === 'horizontal';

        // Đường nối
        connectors.forEach((c) => {
            ctx.beginPath();
            if (c.type === 'spouse') {
                ctx.setLineDash([5, 4]);
                ctx.strokeStyle = primary;
                ctx.moveTo(c.x1 + ox, c.y1 + oy);
                ctx.lineTo(c.x2 + ox, c.y2 + oy);
            } else {
                ctx.setLineDash([]);
                ctx.strokeStyle = lineCol;
                ctx.lineWidth = 2;
                ctx.moveTo(c.x1 + ox, c.y1 + oy);
                if (isHoriz) {
                    const mx = (c.x1 + c.x2) / 2 + ox;
                    ctx.bezierCurveTo(mx, c.y1 + oy, mx, c.y2 + oy, c.x2 + ox, c.y2 + oy);
                } else {
                    const my = (c.y1 + c.y2) / 2 + oy;
                    ctx.bezierCurveTo(c.x1 + ox, my, c.x2 + ox, my, c.x2 + ox, c.y2 + oy);
                }
            }
            ctx.stroke();
        });
        ctx.setLineDash([]);

        // Các node
        Object.values(positions).forEach(({ x, y, w, h, member }) => {
            const nx = x + ox;
            const ny = y + oy;
            ctx.fillStyle = member.is_alive ? '#FFFFFF' : '#FCF9F2';
            ctx.strokeStyle = accent;
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.roundRect(nx, ny, w, h, 12);
            ctx.fill();
            ctx.stroke();

            ctx.fillStyle = '#1F1D1A';
            ctx.font = 'bold 13px sans-serif';
            ctx.textAlign = 'left';
            ctx.fillText(String(member.name || '').slice(0, 24), nx + 12, ny + 26);

            ctx.fillStyle = primary;
            ctx.font = '11px sans-serif';
            ctx.fillText(String(member.other_name || member.occupation || '').slice(0, 26), nx + 12, ny + 44);

            ctx.fillStyle = '#6A635B';
            ctx.font = '11px sans-serif';
            const life = `Đời ${member.generation || 1} • ${member.birth_solar ? String(member.birth_solar).slice(0, 4) : '?'} – ${member.is_alive ? 'Nay' : (member.death_solar ? String(member.death_solar).slice(0, 4) : '?')}`;
            ctx.fillText(life, nx + 12, ny + 72);
        });

        const link = document.createElement('a');
        link.download = `${(state.slug || 'gia-pha-mayhen')}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
        showToast('Đã tải xuống ảnh Phả Đồ PNG độ nét cao!');
    }

    /**
     * Tính quan hệ xưng hô Việt Nam giữa 2 thành viên qua LCA (Lowest Common Ancestor).
     */
    function calculateKinship(idA, idB) {
        if (!idA || !idB) return 'Vui lòng chọn đủ 2 thành viên.';
        if (idA === idB) return 'Đây là cùng một thành viên.';
        const a = getMember(idA);
        const b = getMember(idB);
        if (!a || !b) return 'Không tìm thấy dữ liệu thành viên.';

        if (Array.isArray(a.spouse_ids) && a.spouse_ids.includes(b.id)) {
            return `${a.name} và ${b.name} là **Vợ / Chồng**.`;
        }

        // BFS tìm đường đi tổ tiên gần nhất
        function getAncestors(startId) {
            const dist = new Map([[startId, 0]]);
            const q = [startId];
            while (q.length > 0) {
                const curr = q.shift();
                const m = getMember(curr);
                if (!m || !Array.isArray(m.parent_ids)) continue;
                m.parent_ids.forEach((pid) => {
                    if (!dist.has(pid)) {
                        dist.set(pid, dist.get(curr) + 1);
                        q.push(pid);
                    }
                });
            }
            return dist;
        }

        const ancA = getAncestors(idA);
        const ancB = getAncestors(idB);
        let bestLca = null;
        let bestSum = Infinity;

        ancA.forEach((da, aid) => {
            if (ancB.has(aid)) {
                const db = ancB.get(aid);
                if (da + db < bestSum) {
                    bestSum = da + db;
                    bestLca = { id: aid, da, db };
                }
            }
        });

        if (!bestLca) {
            const genDiff = (a.generation || 1) - (b.generation || 1);
            if (genDiff === 0) return `${a.name} và ${b.name} cùng **Đời thứ ${a.generation || 1}** (họ hàng/dâu rể đồng vai).`;
            return `${a.name} ở **Đời ${a.generation || 1}**, ${b.name} ở **Đời ${b.generation || 1}** (cách nhau ${Math.abs(genDiff)} đời).`;
        }

        const { da, db } = bestLca;
        if (da === 0) {
            if (db === 1) return `${a.name} là **${a.gender === 'F' ? 'Mẹ' : 'Cha'}** của ${b.name}.`;
            if (db === 2) return `${a.name} là **${a.gender === 'F' ? 'Bà' : 'Ông'}** của ${b.name}.`;
            return `${a.name} là **Tổ tiên (cách ${db} đời)** của ${b.name}.`;
        }
        if (db === 0) {
            if (da === 1) return `${a.name} là **Con** của ${b.name}.`;
            if (da === 2) return `${a.name} là **Cháu** của ${b.name}.`;
            return `${a.name} là **Hậu duệ (cách ${da} đời)** của ${b.name}.`;
        }
        if (da === 1 && db === 1) {
            return `${a.name} và ${b.name} là **Anh / Chị / Em ruột**.`;
        }
        if (da === 1 && db === 2) {
            return `${a.name} là **${a.gender === 'F' ? 'Cô / Dì' : 'Bác / Chú'}** của ${b.name} (${b.name} gọi ${a.name} bằng ${a.gender === 'F' ? 'Cô/Dì' : 'Bác/Chú'}).`;
        }
        if (da === 2 && db === 1) {
            return `${a.name} là **Cháu** của ${b.name} (gọi ${b.name} là ${b.gender === 'F' ? 'Cô/Dì' : 'Bác/Chú'}).`;
        }
        if (da === db) {
            return `${a.name} và ${b.name} là **Anh / Chị / Em họ** (cùng Đời ${a.generation || 1}).`;
        }
        return `${a.name} (cách tổ chung ${da} đời) và ${b.name} (cách tổ chung ${db} đời).`;
    }

    function openToolsModal(tabType) {
        const backdrop = document.getElementById('mayhenFtToolModal');
        const titleEl = document.getElementById('mayhenFtToolModalTitle');
        const bodyEl = document.getElementById('mayhenFtToolModalBody');
        if (!backdrop || !titleEl || !bodyEl) return;

        if (tabType === 'anniversary') {
            titleEl.textContent = 'Lịch Giỗ Âm Lịch Dòng Họ';
            const memorials = (state.members || [])
                .filter((m) => !m.is_alive && (m.death_anniversary || m.death_lunar))
                .sort((a, b) => String(a.death_anniversary || '').localeCompare(String(b.death_anniversary || '')));

            bodyEl.innerHTML = memorials.length === 0
                ? '<p style="color:#6A635B;">Chưa có thành viên nào được ghi ngày giỗ Âm lịch.</p>'
                : memorials.map((m) => `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; border:1px solid #EADFD0; border-radius:10px; margin-bottom:8px;">
                        <div>
                            <strong>${escapeHtml(m.name)}</strong> <small style="color:#6A635B;">(Đời ${Number(m.generation || 1)})</small>
                            <div style="font-size:12px; color:#6A635B;">${escapeHtml(m.other_name || m.burial_place || '')}</div>
                        </div>
                        <span style="padding:4px 10px; border-radius:999px; background:#85182A; color:#FAF5EB; font-size:12px; font-weight:700;">
                            ${escapeHtml(m.death_anniversary || m.death_lunar)} ÂL
                        </span>
                    </div>
                `).join('');
        } else {
            titleEl.textContent = 'Tra Cứu Xưng Hô Họ Hàng Việt Nam';
            const opts = (state.members || []).map((m) => `<option value="${escapeHtml(m.id)}">${escapeHtml(m.name)} (Đời ${Number(m.generation || 1)})</option>`).join('');
            bodyEl.innerHTML = `
                <div class="mayhen_family_tree_grid_2" style="margin-bottom:12px;">
                    <div class="mayhen_family_tree_field">
                        <label>Thành viên A</label>
                        <select class="mayhen_family_tree_select" id="mayhenFtKinA">${opts}</select>
                    </div>
                    <div class="mayhen_family_tree_field">
                        <label>Thành viên B</label>
                        <select class="mayhen_family_tree_select" id="mayhenFtKinB">${opts}</select>
                    </div>
                </div>
                <button type="button" class="mayhen_family_tree_btn_primary" style="width:100%;" id="mayhenFtCalcKinBtn">Tra Cứu Cách Xưng Hô</button>
                <div id="mayhenFtKinResult" style="margin-top:14px; padding:12px; border-radius:10px; background:#FAF6EE; border:1px solid #E5DAC9; font-size:14px;">
                    Chọn 2 thành viên rồi bấm Tra cứu.
                </div>
            `;
            const selB = bodyEl.querySelector('#mayhenFtKinB');
            if (selB && selB.options.length > 1) selB.selectedIndex = 1;
            bodyEl.querySelector('#mayhenFtCalcKinBtn')?.addEventListener('click', () => {
                const aId = bodyEl.querySelector('#mayhenFtKinA')?.value;
                const bId = bodyEl.querySelector('#mayhenFtKinB')?.value;
                const res = calculateKinship(aId, bId).replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
                bodyEl.querySelector('#mayhenFtKinResult').innerHTML = res;
            });
        }

        backdrop.classList.add('is-open');
    }

    function normalizeViSearch(str) {
        return String(str || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .trim();
    }

    function isDescendantOf(ancestorId, candidateId) {
        if (!ancestorId || !candidateId) return false;
        if (ancestorId === candidateId) return true;
        const visited = new Set([ancestorId]);
        const queue = [ancestorId];
        while (queue.length > 0) {
            const currId = queue.shift();
            const children = (state.members || []).filter(
                (item) => Array.isArray(item.parent_ids) && item.parent_ids.includes(currId)
            );
            for (const ch of children) {
                if (ch.id === candidateId) return true;
                if (!visited.has(ch.id)) {
                    visited.add(ch.id);
                    queue.push(ch.id);
                }
            }
        }
        return false;
    }

    function shiftBranchGenerations(rootId, delta) {
        if (!delta) return;
        const visited = new Set();
        const queue = [rootId];
        while (queue.length > 0) {
            const currId = queue.shift();
            if (visited.has(currId)) continue;
            visited.add(currId);
            const node = getMember(currId);
            if (!node) continue;
            node.generation = Math.max(1, Math.min(30, Number(node.generation || 1) + delta));

            // Đồng bộ đời cho vợ/chồng cùng thế hệ
            if (Array.isArray(node.spouse_ids)) {
                node.spouse_ids.forEach((sid) => {
                    if (!visited.has(sid)) {
                        const sp = getMember(sid);
                        if (sp) {
                            visited.add(sid);
                            sp.generation = node.generation;
                        }
                    }
                });
            }

            // Dịch chuyển toàn bộ con cháu bên dưới theo cùng biên độ
            (state.members || []).forEach((cand) => {
                if (!visited.has(cand.id) && Array.isArray(cand.parent_ids)) {
                    const isChildOfNode = cand.parent_ids.includes(currId)
                        || (Array.isArray(node.spouse_ids) && node.spouse_ids.some((sid) => cand.parent_ids.includes(sid)));
                    if (isChildOfNode) {
                        queue.push(cand.id);
                    }
                }
            });
        }
    }

    function normalizeGenerationOrders(genNumber) {
        const peers = (state.members || [])
            .filter((item) => Number(item.generation || 1) === genNumber)
            .sort((a, b) => Number(a.birth_order || 1) - Number(b.birth_order || 1));
        peers.forEach((p, idx) => {
            p.birth_order = idx + 1;
        });
    }

    function moveMemberToGeneration(memberId, targetGen) {
        const m = getMember(memberId);
        if (!m) return;
        const clampedGen = Math.max(1, Math.min(30, Number(targetGen || 1)));
        pushUndoSnapshot(`Chuyển ${m.name} sang Đời ${clampedGen}`);
        const oldGen = Math.max(1, Number(m.generation || 1));
        const delta = clampedGen - oldGen;

        if (delta !== 0) {
            shiftBranchGenerations(m.id, delta);
        } else {
            m.generation = clampedGen;
        }

        // Gỡ liên kết cha/mẹ nếu đời cha/mẹ không còn nhỏ hơn đời mới
        if (Array.isArray(m.parent_ids) && m.parent_ids.length > 0) {
            m.parent_ids = m.parent_ids.filter((pid) => {
                const p = getMember(pid);
                return p && Number(p.generation || 1) < m.generation;
            });
        }

        const peers = (state.members || []).filter((item) => item.id !== m.id && Number(item.generation || 1) === clampedGen);
        m.birth_order = peers.length + 1;
        normalizeGenerationOrders(oldGen);
        normalizeGenerationOrders(clampedGen);

        selectedMemberId = m.id;
        renderTreeCanvas();
        fitTreeToScreen();
        refreshSidebarStatsAndList();
        showToast(`Đã chuyển ${m.name} sang Đời ${clampedGen}`);
    }

    function assignMemberAsChildOf(childId, newParentId) {
        if (!childId || !newParentId || childId === newParentId) return;
        const child = getMember(childId);
        const parent = getMember(newParentId);
        if (!child || !parent) return;

        if (isDescendantOf(child.id, parent.id)) {
            showToast('Không thể gán làm con của chính con cháu mình.');
            return;
        }
        if (Array.isArray(child.spouse_ids) && child.spouse_ids.includes(parent.id)) {
            showToast('Hai thành viên đang là Vợ/Chồng của nhau.');
            return;
        }

        pushUndoSnapshot(`Gán ${child.name} là con của ${parent.name}`);

        const primarySpouseId = Array.isArray(parent.spouse_ids) && parent.spouse_ids.length > 0
            ? parent.spouse_ids[0]
            : null;
        const newParentIds = primarySpouseId && getMember(primarySpouseId)
            ? [parent.id, primarySpouseId]
            : [parent.id];

        const targetGen = Math.min(30, Number(parent.generation || 1) + 1);
        const oldGen = Math.max(1, Number(child.generation || 1));
        const delta = targetGen - oldGen;

        child.parent_ids = newParentIds;
        if (delta !== 0) {
            shiftBranchGenerations(child.id, delta);
        } else {
            child.generation = targetGen;
        }

        const siblings = getChildrenOf(parent.id).filter((s) => s.id !== child.id);
        child.birth_order = siblings.length + 1;
        normalizeGenerationOrders(oldGen);
        normalizeGenerationOrders(targetGen);

        selectedMemberId = child.id;
        renderTreeCanvas();
        fitTreeToScreen();
        refreshSidebarStatsAndList();
        showToast(`Đã gán ${child.name} là con của ${parent.name} (Đời ${targetGen})`);
    }

    function reorderMemberRelativeTo(draggedId, targetId, position) {
        if (!draggedId || !targetId || draggedId === targetId) return;
        const dragged = getMember(draggedId);
        const target = getMember(targetId);
        if (!dragged || !target) return;

        const targetGen = Math.max(1, Number(target.generation || 1));
        const oldGen = Math.max(1, Number(dragged.generation || 1));
        const delta = targetGen - oldGen;

        pushUndoSnapshot(delta !== 0 ? `Chuyển ${dragged.name} sang Đời ${targetGen}` : `Đổi thứ tự ${dragged.name}`);

        if (delta !== 0) {
            shiftBranchGenerations(dragged.id, delta);
            if (Array.isArray(dragged.parent_ids) && dragged.parent_ids.length > 0) {
                dragged.parent_ids = dragged.parent_ids.filter((pid) => {
                    const p = getMember(pid);
                    return p && Number(p.generation || 1) < targetGen;
                });
            }
        }

        // Nếu cả 2 cùng có cha/mẹ hoặc target có cha/mẹ mà dragged đang mồ côi khi chuyển vào giữa nhóm anh chị em, giữ nguyên parent hợp lệ
        const peers = (state.members || [])
            .filter((item) => Number(item.generation || 1) === targetGen && item.id !== dragged.id)
            .sort((a, b) => Number(a.birth_order || 1) - Number(b.birth_order || 1));

        const targetIdx = peers.findIndex((item) => item.id === target.id);
        const insertAt = targetIdx === -1 ? peers.length : (position === 'before' ? targetIdx : targetIdx + 1);
        peers.splice(insertAt, 0, dragged);
        peers.forEach((p, idx) => {
            p.birth_order = idx + 1;
        });

        // Đồng bộ lại thứ tự trong mảng state.members cho tự nhiên
        const peerOrderMap = new Map(peers.map((p, idx) => [p.id, idx]));
        state.members.sort((a, b) => {
            const gA = Number(a.generation || 1);
            const gB = Number(b.generation || 1);
            if (gA !== gB) return gA - gB;
            if (gA === targetGen && peerOrderMap.has(a.id) && peerOrderMap.has(b.id)) {
                return peerOrderMap.get(a.id) - peerOrderMap.get(b.id);
            }
            return Number(a.birth_order || 1) - Number(b.birth_order || 1);
        });

        if (oldGen !== targetGen) {
            normalizeGenerationOrders(oldGen);
        }

        selectedMemberId = dragged.id;
        renderTreeCanvas();
        fitTreeToScreen();
        refreshSidebarStatsAndList();
        showToast(
            oldGen !== targetGen
                ? `Đã chuyển ${dragged.name} sang Đời ${targetGen} (vị trí #${dragged.birth_order})`
                : `Đã đổi vị trí ${dragged.name} trong Đời ${targetGen}`
        );
    }

    let draggedMemberId = null;

    function buildMemberSubtitle(m) {
        const parents = (m.parent_ids || []).map((pid) => getMember(pid)?.name).filter(Boolean);
        const spouses = (m.spouse_ids || []).map((sid) => getMember(sid)?.name).filter(Boolean);
        if (parents.length > 0) {
            return `Con của ${parents[0]}${spouses.length > 0 ? ` • Vợ/Chồng: ${spouses[0]}` : ''}`;
        }
        if (spouses.length > 0) {
            return `Vợ/Chồng: ${spouses[0]}`;
        }
        return m.other_name || (Number(m.generation || 1) === 1 ? 'Nhánh gốc dòng họ' : 'Chưa gán cha/mẹ');
    }

    function refreshSidebarStatsAndList() {
        const listEl = document.getElementById('mayhenFtSidebarMemberList');
        if (!listEl) return;

        const allMembers = state.members || [];
        const statsEl = document.getElementById('mayhenFtSidebarMemberStats');
        const addBtn = document.getElementById('mayhenFtAddStandaloneBtn');
        const searchEl = document.getElementById('mayhenFtSidebarMemberSearch');
        const query = normalizeViSearch(searchEl?.value || '');

        const genSet = new Set(allMembers.map((m) => Math.max(1, Number(m.generation || 1))));
        const maxGen = genSet.size > 0 ? Math.max(...genSet) : 1;

        if (statsEl) {
            statsEl.textContent = `${allMembers.length} người • ${genSet.size} đời`;
        }
        const selectedNameEl = document.getElementById('mayhenFtSelectedMemberName');
        if (selectedNameEl) {
            const sel = getMember(selectedMemberId);
            selectedNameEl.textContent = sel
                ? `${sel.name} (Đời ${Number(sel.generation || 1)})`
                : 'Chưa chọn thành viên';
        }

        const filtered = query
            ? allMembers.filter((m) =>
                normalizeViSearch(m.name).includes(query)
                || normalizeViSearch(m.other_name).includes(query)
                || normalizeViSearch(buildMemberSubtitle(m)).includes(query)
            )
            : allMembers;

        if (filtered.length === 0) {
            listEl.innerHTML = `<div class="mayhen_family_tree_member_empty">Không tìm thấy thành viên khớp với "${escapeHtml(searchEl?.value || '')}".</div>`;
            return;
        }

        // Phân nhóm theo từng Đời tăng dần và giữ cặp vợ/chồng gần nhau
        const groups = new Map();
        filtered.forEach((m) => {
            const g = Math.max(1, Number(m.generation || 1));
            if (!groups.has(g)) groups.set(g, []);
            groups.get(g).push(m);
        });

        const sortedGens = Array.from(groups.keys()).sort((a, b) => a - b);

        function sortGenerationMembers(membersInGen) {
            const base = [...membersInGen].sort((a, b) => Number(a.birth_order || 1) - Number(b.birth_order || 1));
            const placed = new Set();
            const ordered = [];
            const idMap = new Map(base.map((m) => [m.id, m]));
            base.forEach((m) => {
                if (placed.has(m.id)) return;
                placed.add(m.id);
                ordered.push(m);
                if (Array.isArray(m.spouse_ids)) {
                    m.spouse_ids.forEach((sid) => {
                        if (!placed.has(sid) && idMap.has(sid)) {
                            placed.add(sid);
                            ordered.push(idMap.get(sid));
                        }
                    });
                }
            });
            return ordered;
        }

        const groupsHtml = sortedGens.map((g) => {
            const orderedMembers = sortGenerationMembers(groups.get(g));
            const cardsHtml = orderedMembers.map((m) => {
                const initial = escapeHtml((m.name || '?').trim().split(/\s+/).pop().charAt(0).toUpperCase());
                const isSel = m.id === selectedMemberId;
                const dotCls = [
                    'mayhen_family_tree_member_dot',
                    m.gender === 'F' ? 'is-female' : '',
                    !m.is_alive ? 'is-deceased' : '',
                ].filter(Boolean).join(' ');
                const subtitle = buildMemberSubtitle(m);
                const orderTag = Number(m.birth_order || 0) > 0 ? `#${Number(m.birth_order)}` : '';

                return `
                    <div class="mayhen_family_tree_member_card ${isSel ? 'is-selected' : ''}"
                         draggable="true"
                         data-member-card="${escapeHtml(m.id)}"
                         data-member-gen="${g}">
                        <span class="mayhen_family_tree_member_drag_handle" title="Kéo để chuyển đời, gán cha/mẹ hoặc đổi thứ tự" aria-hidden="true">⠿</span>
                        <span class="${dotCls}">${initial}</span>
                        <div class="mayhen_family_tree_member_info">
                            <div class="mayhen_family_tree_member_name_row">
                                <span class="mayhen_family_tree_member_name">${escapeHtml(m.name)}</span>
                                ${orderTag ? `<span class="mayhen_family_tree_member_order">${orderTag}</span>` : ''}
                            </div>
                            <div class="mayhen_family_tree_member_rel">${escapeHtml(subtitle)}</div>
                        </div>
                        <div class="mayhen_family_tree_member_actions">
                            <button type="button" class="mayhen_family_tree_member_icon_btn" data-quick-child="${escapeHtml(m.id)}" title="Thêm con cho ${escapeHtml(m.name)}">+</button>
                            <button type="button" class="mayhen_family_tree_member_icon_btn" data-jump-member="${escapeHtml(m.id)}" title="Chỉnh sửa ${escapeHtml(m.name)}">✎</button>
                        </div>
                    </div>
                `;
            }).join('');

            return `
                <section class="mayhen_family_tree_gen_group" data-drop-gen="${g}">
                    <header class="mayhen_family_tree_gen_group_head">
                        <span class="mayhen_family_tree_gen_group_title">Đời thứ ${g}</span>
                        <span class="mayhen_family_tree_gen_group_count">${orderedMembers.length} người</span>
                    </header>
                    <div class="mayhen_family_tree_gen_group_list">${cardsHtml}</div>
                </section>
            `;
        }).join('');

        const nextGenDropHtml = !query && maxGen < 30
            ? `<div class="mayhen_family_tree_new_gen_dropzone" data-drop-gen="${maxGen + 1}">+ Thả thành viên vào đây để chuyển sang Đời thứ ${maxGen + 1}</div>`
            : '';

        listEl.innerHTML = groupsHtml + nextGenDropHtml;

        function clearDropIndicators() {
            listEl.querySelectorAll('.is-drop-before, .is-drop-after, .is-drop-as-child, .is-drag-over').forEach((el) => {
                el.classList.remove('is-drop-before', 'is-drop-after', 'is-drop-as-child', 'is-drag-over');
            });
        }

        function focusAndOpenMember(id) {
            const pos = lastRenderPositions[id];
            if (pos && viewport && layoutMode !== 'chronicle') {
                panX = (viewport.clientWidth / 2) - (pos.x + pos.w / 2) * zoom;
                panY = (viewport.clientHeight / 2) - (pos.y + pos.h / 2) * zoom;
                applyTransform();
            }
            openMemberDrawer(id);
        }

        listEl.querySelectorAll('[data-quick-child]').forEach((btn) => {
            btn.addEventListener('click', (ev) => {
                ev.stopPropagation();
                addRelative(btn.getAttribute('data-quick-child'), 'child');
            });
        });

        listEl.querySelectorAll('[data-jump-member]').forEach((btn) => {
            btn.addEventListener('click', (ev) => {
                ev.stopPropagation();
                focusAndOpenMember(btn.getAttribute('data-jump-member'));
            });
        });

        // Gắn Drag & Drop cho từng thẻ thành viên
        listEl.querySelectorAll('[data-member-card]').forEach((card) => {
            const cardId = card.getAttribute('data-member-card');

            card.addEventListener('click', () => {
                focusAndOpenMember(cardId);
            });

            card.addEventListener('dragstart', (ev) => {
                draggedMemberId = cardId;
                card.classList.add('is-dragging');
                if (ev.dataTransfer) {
                    ev.dataTransfer.effectAllowed = 'move';
                    ev.dataTransfer.setData('text/plain', cardId);
                }
            });

            card.addEventListener('dragend', () => {
                draggedMemberId = null;
                card.classList.remove('is-dragging');
                clearDropIndicators();
            });

            card.addEventListener('dragover', (ev) => {
                if (!draggedMemberId || draggedMemberId === cardId) return;
                ev.preventDefault();
                ev.stopPropagation();
                if (ev.dataTransfer) ev.dataTransfer.dropEffect = 'move';

                const rect = card.getBoundingClientRect();
                const ratio = (ev.clientY - rect.top) / Math.max(1, rect.height);
                clearDropIndicators();

                if (ratio < 0.28) {
                    card.classList.add('is-drop-before');
                    card.dataset.dropAction = 'before';
                } else if (ratio > 0.72) {
                    card.classList.add('is-drop-after');
                    card.dataset.dropAction = 'after';
                } else {
                    card.classList.add('is-drop-as-child');
                    card.dataset.dropAction = 'child';
                }
            });

            card.addEventListener('drop', (ev) => {
                if (!draggedMemberId || draggedMemberId === cardId) return;
                ev.preventDefault();
                ev.stopPropagation();
                const action = card.dataset.dropAction || 'child';
                const sourceId = draggedMemberId;
                draggedMemberId = null;
                clearDropIndicators();

                if (action === 'before' || action === 'after') {
                    reorderMemberRelativeTo(sourceId, cardId, action);
                } else {
                    assignMemberAsChildOf(sourceId, cardId);
                }
            });
        });

        // Gắn Drag & Drop cho từng nhóm Đời & vùng Tạo Đời mới
        listEl.querySelectorAll('[data-drop-gen]').forEach((zone) => {
            zone.addEventListener('dragover', (ev) => {
                if (!draggedMemberId) return;
                ev.preventDefault();
                if (ev.dataTransfer) ev.dataTransfer.dropEffect = 'move';
                clearDropIndicators();
                zone.classList.add('is-drag-over');
            });

            zone.addEventListener('dragleave', (ev) => {
                if (!zone.contains(ev.relatedTarget)) {
                    zone.classList.remove('is-drag-over');
                }
            });

            zone.addEventListener('drop', (ev) => {
                if (!draggedMemberId) return;
                ev.preventDefault();
                const targetGen = Number(zone.getAttribute('data-drop-gen') || 1);
                const sourceId = draggedMemberId;
                draggedMemberId = null;
                clearDropIndicators();
                moveMemberToGeneration(sourceId, targetGen);
            });
        });
    }

    // Gắn các điều khiển Toolbar (Layout, Zoom, Tìm kiếm, Đóng Drawer, Modal)
    document.querySelectorAll('[data-layout-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const nextLayout = btn.getAttribute('data-layout-btn') || 'vertical';
            if (nextLayout === layoutMode) return;
            pushUndoSnapshot('Đổi kiểu hiển thị phả đồ');
            layoutMode = nextLayout;
            document.querySelectorAll('[data-layout-btn]').forEach((b) => b.classList.toggle('is-active', b === btn));
            renderTreeCanvas();
            if (layoutMode !== 'chronicle') fitTreeToScreen();
        });
    });

    document.getElementById('mayhenFtZoomIn')?.addEventListener('click', () => { zoom = Math.min(1.8, zoom + 0.14); applyTransform(); });
    document.getElementById('mayhenFtZoomOut')?.addEventListener('click', () => { zoom = Math.max(0.28, zoom - 0.14); applyTransform(); });
    document.getElementById('mayhenFtZoomFit')?.addEventListener('click', fitTreeToScreen);
    document.getElementById('mayhenFtExportPng')?.addEventListener('click', exportTreeToPng);
    document.getElementById('mayhenFtPrintBtn')?.addEventListener('click', () => window.print());
    document.getElementById('mayhenFtBtnAnniversary')?.addEventListener('click', () => openToolsModal('anniversary'));
    document.getElementById('mayhenFtBtnKinship')?.addEventListener('click', () => openToolsModal('kinship'));
    document.getElementById('mayhenFtDrawerClose')?.addEventListener('click', () => drawer?.classList.remove('is-open'));
    document.getElementById('mayhenFtToolModalClose')?.addEventListener('click', () => document.getElementById('mayhenFtToolModal')?.classList.remove('is-open'));
    document.getElementById('mayhenFtPremiumModalClose')?.addEventListener('click', () => document.getElementById('mayhenFtPremiumModal')?.classList.remove('is-open'));

    // Tìm kiếm thành viên
    const searchInput = document.getElementById('mayhenFtSearchInput');
    const searchResults = document.getElementById('mayhenFtSearchResults');
    if (searchInput && searchResults) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            if (!q) {
                searchResults.classList.remove('is-open');
                return;
            }
            const matched = (state.members || []).filter((m) =>
                (m.name || '').toLowerCase().includes(q) || (m.other_name || '').toLowerCase().includes(q)
            ).slice(0, 8);
            searchResults.innerHTML = matched.length === 0
                ? '<div style="padding:8px 11px; font-size:12px; color:#6A635B;">Không tìm thấy</div>'
                : matched.map((m) => `<button type="button" class="mayhen_family_tree_search_item" data-found-id="${escapeHtml(m.id)}"><strong>${escapeHtml(m.name)}</strong> <small>(Đời ${Number(m.generation || 1)})</small></button>`).join('');
            searchResults.classList.add('is-open');
            searchResults.querySelectorAll('[data-found-id]').forEach((itemBtn) => {
                itemBtn.addEventListener('click', () => {
                    const id = itemBtn.getAttribute('data-found-id');
                    searchResults.classList.remove('is-open');
                    const pos = lastRenderPositions[id];
                    if (pos && viewport) {
                        zoom = 1;
                        panX = (viewport.clientWidth / 2) - (pos.x + pos.w / 2);
                        panY = (viewport.clientHeight / 2) - (pos.y + pos.h / 2);
                        applyTransform();
                    }
                    openMemberDrawer(id);
                });
            });
        });
    }

    // Pan kéo thả bằng chuột / cảm ứng trên canvas
    if (viewport) {
        viewport.addEventListener('mousedown', (e) => {
            if (e.target.closest('.mayhen_family_tree_node') || e.target.closest('.mayhen_family_tree_zoom_dock')) return;
            isPanning = true;
            startPanX = e.clientX - panX;
            startPanY = e.clientY - panY;
        });
        window.addEventListener('mousemove', (e) => {
            if (!isPanning) return;
            panX = e.clientX - startPanX;
            panY = e.clientY - startPanY;
            applyTransform();
        });
        window.addEventListener('mouseup', () => { isPanning = false; });
        viewport.addEventListener('wheel', (e) => {
            e.preventDefault();
            const delta = e.deltaY < 0 ? 0.08 : -0.08;
            zoom = Math.min(1.8, Math.max(0.28, zoom + delta));
            applyTransform();
        }, { passive: false });
    }

    // Logic riêng của Studio (Tabs, Binding inputs, Thêm thành viên gốc, Xuất bản Link Thật)
    if (isStudio) {
        function activateStudioTab(targetTab) {
            activeTab = targetTab || 'info';
            document.querySelectorAll('.mayhen_family_tree_tab_btn').forEach((b) => {
                b.classList.toggle('is-active', b.getAttribute('data-tab') === activeTab);
            });
            document.querySelectorAll('.mayhen_family_tree_tab_panel').forEach((p) => {
                p.classList.toggle('is-active', p.getAttribute('data-panel') === activeTab);
            });
        }

        document.querySelectorAll('.mayhen_family_tree_tab_btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                activateStudioTab(btn.getAttribute('data-tab'));
                if (hasRestoredDraft || localStorage.getItem(storageKey)) {
                    saveDraftToLocal();
                }
            });
        });

        const titleInput = document.getElementById('mayhenFtTitleInput');
        const surnameInput = document.getElementById('mayhenFtSurnameInput');
        const hallInput = document.getElementById('mayhenFtHallInput');
        const introInput = document.getElementById('mayhenFtIntroInput');
        const rulesInput = document.getElementById('mayhenFtRulesInput');
        const visSelect = document.getElementById('mayhenFtVisibilitySelect');
        const passWrap = document.getElementById('mayhenFtPasswordWrap');
        const passInput = document.getElementById('mayhenFtPasswordInput');

        // Đồng bộ toàn bộ giao diện Studio nếu đã khôi phục bản nháp từ localStorage
        if (hasRestoredDraft) {
            if (titleInput && typeof state.title === 'string') titleInput.value = state.title;
            if (surnameInput && typeof state.family_surname === 'string') surnameInput.value = state.family_surname;
            if (hallInput && typeof state.ancestral_hall === 'string') hallInput.value = state.ancestral_hall;
            if (introInput && typeof state.intro_chronicle === 'string') introInput.value = state.intro_chronicle;
            if (rulesInput && typeof state.clan_rules === 'string') rulesInput.value = state.clan_rules;
            if (visSelect && state.visibility) {
                visSelect.value = state.visibility;
                if (passWrap) passWrap.style.display = visSelect.value === 'password' ? 'block' : 'none';
            }
            if (state.template_style) {
                app.className = app.className.replace(/mayhen_family_tree_theme_[a-z0-9-]+/g, '').trim() + ` mayhen_family_tree_theme_${state.template_style}`;
                document.querySelectorAll('[data-theme-slug]').forEach((c) => {
                    c.classList.toggle('is-active', c.getAttribute('data-theme-slug') === state.template_style);
                });
            }
            document.querySelectorAll('[data-layout-btn]').forEach((b) => {
                b.classList.toggle('is-active', b.getAttribute('data-layout-btn') === layoutMode);
            });
            if (activeTab && activeTab !== 'info') {
                activateStudioTab(activeTab);
            }

            const tabBody = document.querySelector('.mayhen_family_tree_tab_body');
            if (tabBody) {
                showDraftRestoredBanner(tabBody, {
                    message: '✦ Đã tự động khôi phục toàn bộ cây gia phả và thông tin bạn đã dựng trước đó.',
                    onReset: () => {
                        clearAllFamilyTreeStorage();
                        hasRestoredDraft = false;
                        window.location.href = `${window.location.pathname}?mau=${encodeURIComponent(state.template_style || 'hoang-kim-tu-duong')}&reset=1`;
                    },
                });
            }
        }

        titleInput?.addEventListener('input', () => { recordTextUndoSnapshot('title', 'Sửa tên cuốn gia phả'); state.title = titleInput.value; renderTreeCanvas(); });
        surnameInput?.addEventListener('input', () => { recordTextUndoSnapshot('surname', 'Sửa tên dòng họ'); state.family_surname = surnameInput.value; saveDraftToLocal(); });
        hallInput?.addEventListener('input', () => { recordTextUndoSnapshot('hall', 'Sửa từ đường / quê gốc'); state.ancestral_hall = hallInput.value; renderTreeCanvas(); });
        introInput?.addEventListener('input', () => { recordTextUndoSnapshot('intro', 'Sửa lời nói đầu / phả ký'); state.intro_chronicle = introInput.value; saveDraftToLocal(); });
        rulesInput?.addEventListener('input', () => { recordTextUndoSnapshot('rules', 'Sửa tộc ước / gia huấn'); state.clan_rules = rulesInput.value; saveDraftToLocal(); });
        visSelect?.addEventListener('change', () => {
            pushUndoSnapshot('Đổi chế độ bảo mật');
            state.visibility = visSelect.value;
            if (passWrap) passWrap.style.display = visSelect.value === 'password' ? 'block' : 'none';
            saveDraftToLocal();
        });

        document.querySelectorAll('[data-theme-slug]').forEach((chip) => {
            chip.addEventListener('click', () => {
                const slug = chip.getAttribute('data-theme-slug');
                if (!slug || slug === state.template_style) return;
                pushUndoSnapshot('Đổi mẫu phong cách phả đồ');
                state.template_style = slug;
                app.className = app.className.replace(/mayhen_family_tree_theme_[a-z0-9-]+/g, '').trim() + ` mayhen_family_tree_theme_${slug}`;
                document.querySelectorAll('[data-theme-slug]').forEach((c) => c.classList.toggle('is-active', c === chip));
                saveDraftToLocal();
            });
        });

        document.getElementById('mayhenFtUndoBtn')?.addEventListener('click', performUndo);
        document.getElementById('mayhenFtRedoBtn')?.addEventListener('click', performRedo);
        updateHistoryButtons();

        window.addEventListener('keydown', (ev) => {
            const isMod = ev.ctrlKey || ev.metaKey;
            if (!isMod || ev.altKey) return;
            const tag = (ev.target?.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || ev.target?.isContentEditable) return;

            const key = String(ev.key || '').toLowerCase();
            if (key === 'z' && !ev.shiftKey) {
                ev.preventDefault();
                performUndo();
            } else if (key === 'y' || (key === 'z' && ev.shiftKey)) {
                ev.preventDefault();
                performRedo();
            }
        });

        // Đảm bảo link Đăng nhập / Đăng ký trong Modal luôn mang đúng URL Studio hiện tại & khóa lưu nháp trước khi rời trang
        function syncAuthModalLinks(loginUrlFromServer, registerUrlFromServer) {
            const currentStudioPath = window.location.pathname + window.location.search;
            const loginLink = document.getElementById('mayhenFtModalLoginLink');
            const registerLink = document.getElementById('mayhenFtModalRegisterLink');
            if (loginLink) {
                loginLink.setAttribute('href', loginUrlFromServer || `/dang-nhap?redirect=${encodeURIComponent(currentStudioPath)}`);
            }
            if (registerLink) {
                registerLink.setAttribute('href', registerUrlFromServer || `/dang-ky?redirect=${encodeURIComponent(currentStudioPath)}`);
            }
        }

        ['mayhenFtModalLoginLink', 'mayhenFtModalRegisterLink'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => {
                activeTab = 'publish';
                saveDraftToLocal(true);
            });
        });

        window.addEventListener('pagehide', () => {
            if (hasRestoredDraft || localStorage.getItem(storageKey)) {
                saveDraftToLocal();
            }
        });

        document.getElementById('mayhenFtSidebarMemberSearch')?.addEventListener('input', () => {
            refreshSidebarStatsAndList();
        });

        document.querySelectorAll('[data-toolbar-add]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const relType = btn.getAttribute('data-toolbar-add') || 'child';
                const anchorId = selectedMemberId || state.members?.[0]?.id;
                if (!anchorId) {
                    showToast('Hãy chọn một thành viên trước khi thêm người thân.');
                    return;
                }
                addRelative(anchorId, relType);
            });
        });

        document.getElementById('mayhenFtResetDraftBtn')?.addEventListener('click', () => {
            clearAllFamilyTreeStorage();
            hasRestoredDraft = false;
            window.location.href = `${window.location.pathname}?mau=${encodeURIComponent(state.template_style || 'hoang-kim-tu-duong')}&reset=1`;
        });

        // Xuất bản Link Thật (Kiểm tra gói Premium ở Backend)
        async function handlePublishRealLink() {
            const btn = document.getElementById('mayhenFtPublishBtn');
            const publishUrl = btn?.getAttribute('data-publish-url');
            if (!publishUrl) return;

            activeTab = 'publish';
            saveDraftToLocal(true);
            btn.disabled = true;
            const prevText = btn.textContent;
            btn.textContent = 'Đang xuất bản...';

            try {
                const res = await fetch(publishUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        tree_id: state.id || null,
                        title: state.title || 'Gia Phả Dòng Họ',
                        family_surname: state.family_surname || '',
                        ancestral_hall: state.ancestral_hall || '',
                        template_style: state.template_style || 'hoang-kim-tu-duong',
                        layout_mode: layoutMode,
                        intro_chronicle: state.intro_chronicle || '',
                        clan_rules: state.clan_rules || '',
                        visibility: visSelect?.value || 'public',
                        access_password: passInput?.value || null,
                        members: state.members || [],
                    }),
                });
                const data = await res.json();

                if (res.status === 401 || res.status === 403) {
                    activeTab = 'publish';
                    saveDraftToLocal(true);
                    const currentStudioPath = window.location.pathname + window.location.search;
                    const fallbackLoginUrl = `/dang-nhap?redirect=${encodeURIComponent(currentStudioPath)}`;
                    const fallbackRegisterUrl = `/dang-ky?redirect=${encodeURIComponent(currentStudioPath)}`;
                    const resolvedLoginUrl = currentStudioPath.includes('?') ? fallbackLoginUrl : (data.login_url || fallbackLoginUrl);
                    const resolvedRegisterUrl = currentStudioPath.includes('?') ? fallbackRegisterUrl : (data.register_url || fallbackRegisterUrl);

                    if (res.status === 401 || data.code === 'PREMIUM_PLAN_REQUIRED') {
                        syncAuthModalLinks(resolvedLoginUrl, resolvedRegisterUrl);
                        document.getElementById('mayhenFtPremiumModal')?.classList.add('is-open');
                        showToast('Bản nháp gia phả đã được lưu an toàn. Hãy đăng nhập để xuất bản link thật!');
                    } else {
                        showToast(data.message || 'Không thể tạo link thật.');
                    }
                    return;
                }

                if (!res.ok || !data.success) {
                    showToast(data.message || 'Dữ liệu chưa hợp lệ, vui lòng kiểm tra lại.');
                    return;
                }

                state.id = data.tree_id;
                state.slug = data.slug;
                hasRestoredDraft = false;
                clearAllFamilyTreeStorage();
                document.querySelectorAll('.mayhen_client_draft_notice').forEach((el) => el.remove());
                if (data.studio_url && window.history?.replaceState) {
                    window.history.replaceState(null, '', data.studio_url);
                }

                const resultBox = document.getElementById('mayhenFtPublishResult');
                if (resultBox) {
                    resultBox.style.display = 'block';
                    resultBox.innerHTML = `
                        <strong style="color:#1E4D3B;">✓ Đã tạo link gia phả thật thành công!</strong>
                        <p style="font-size:12px; margin:6px 0;">Hạn lưu trữ theo gói hiện tại: <strong>${escapeHtml(data.expires_at_label)}</strong></p>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:8px;">
                            <a href="${escapeHtml(data.public_url)}" target="_blank" class="mayhen_family_tree_btn_primary" style="padding:8px 12px; font-size:12px;">Mở Link Gia Phả &rarr;</a>
                            <button type="button" class="mayhen_family_tree_btn_outline" style="padding:8px 12px; font-size:12px;" id="mayhenFtCopyLinkBtn">Sao chép link</button>
                        </div>
                    `;
                    resultBox.querySelector('#mayhenFtCopyLinkBtn')?.addEventListener('click', () => {
                        navigator.clipboard?.writeText(data.public_url);
                        showToast('Đã sao chép đường dẫn gia phả!');
                    });
                }
                showToast('Đã xuất bản link Gia Phả Online thành công!');
            } catch {
                showToast('Lỗi kết nối máy chủ. Vui lòng thử lại.');
            } finally {
                btn.disabled = false;
                btn.textContent = prevText;
            }
        }

        document.getElementById('mayhenFtPublishBtn')?.addEventListener('click', handlePublishRealLink);
        document.getElementById('mayhenFtTopPublishBtn')?.addEventListener('click', handlePublishRealLink);
        refreshSidebarStatsAndList();
    }

    renderTreeCanvas();
    fitTreeToScreen();
    isBootstrapping = false;
});
