/**
 * MÂY HẸN — MODULE GIỮ NHÁP CÔNG CỤ QUA LUỒNG ĐĂNG NHẬP (Pure Vanilla ES Module)
 * - Lưu trong localStorage kèm hạn 24 giờ (sống sót cả khi đăng nhập Email/Google lẫn xác thực Email ở tab mới).
 * - Không bao giờ lưu mật khẩu, CSRF token hay trường nhạy cảm.
 */

const PREFIX = 'mayhen_tool_draft_v1:';
const DEFAULT_TTL_MS = 24 * 60 * 60 * 1000;
const IGNORED_FIELD_NAMES = new Set(['_token', '_method', 'password', 'password_confirmation', 'access_password']);

function getKey(toolKey) {
    return `${PREFIX}${String(toolKey || '').trim()}`;
}

export function saveToolDraft(toolKey, payload, ttlMs = DEFAULT_TTL_MS) {
    if (!toolKey || !payload || typeof payload !== 'object') return false;
    try {
        const now = Date.now();
        localStorage.setItem(getKey(toolKey), JSON.stringify({
            saved_at: now,
            expires_at: now + Math.max(60_000, Number(ttlMs) || DEFAULT_TTL_MS),
            data: payload,
        }));
        return true;
    } catch {
        return false;
    }
}

export function loadToolDraft(toolKey) {
    if (!toolKey) return null;
    try {
        const raw = localStorage.getItem(getKey(toolKey));
        if (!raw) return null;
        const parsed = JSON.parse(raw);
        if (!parsed || typeof parsed !== 'object' || !parsed.data || typeof parsed.data !== 'object') {
            localStorage.removeItem(getKey(toolKey));
            return null;
        }
        if (parsed.expires_at && Date.now() > Number(parsed.expires_at)) {
            localStorage.removeItem(getKey(toolKey));
            return null;
        }
        return parsed.data;
    } catch {
        return null;
    }
}

export function clearToolDraft(toolKey) {
    if (!toolKey) return;
    try {
        localStorage.removeItem(getKey(toolKey));
    } catch {
        // Bỏ qua nếu storage không khả dụng
    }
}

/**
 * Tự động lưu & khôi phục toàn bộ trường nhập trong 1 thẻ <form> chuẩn HTML.
 *
 * @param {HTMLFormElement|null} formEl
 * @param {string} toolKey
 * @param {{ ignoreNames?: string[], onRestored?: (data: Record<string, any>) => void }} [options]
 */
export function bindFormDraft(formEl, toolKey, options = {}) {
    const noop = { save: () => {}, restore: () => false, clear: () => {}, hasRestored: false };
    if (!formEl || !toolKey) return noop;

    const extraIgnored = new Set((options.ignoreNames || []).map(String));

    function shouldSkip(el) {
        if (!el || !el.name) return true;
        const type = (el.type || '').toLowerCase();
        if (type === 'password' || type === 'file' || type === 'submit' || type === 'button' || type === 'reset') {
            return true;
        }
        if (IGNORED_FIELD_NAMES.has(el.name) || extraIgnored.has(el.name)) {
            return true;
        }
        return false;
    }

    function snapshot() {
        const values = {};
        const elements = Array.from(formEl.elements || []);
        for (const el of elements) {
            if (shouldSkip(el)) continue;
            const type = (el.type || '').toLowerCase();
            if (type === 'checkbox') {
                values[el.name] = Boolean(el.checked);
            } else if (type === 'radio') {
                if (el.checked) values[el.name] = el.value;
            } else if (el.tagName === 'SELECT' && el.multiple) {
                values[el.name] = Array.from(el.selectedOptions).map((o) => o.value);
            } else {
                values[el.name] = el.value;
            }
        }
        return values;
    }

    function save() {
        const data = snapshot();
        if (Object.keys(data).length > 0) {
            saveToolDraft(toolKey, data);
        }
    }

    function restore() {
        const saved = loadToolDraft(toolKey);
        if (!saved) return false;
        let restoredAny = false;
        const elements = Array.from(formEl.elements || []);

        for (const el of elements) {
            if (shouldSkip(el)) continue;
            if (!Object.prototype.hasOwnProperty.call(saved, el.name)) continue;
            const val = saved[el.name];
            const type = (el.type || '').toLowerCase();

            if (type === 'checkbox') {
                const nextChecked = Boolean(val);
                if (el.checked !== nextChecked) {
                    el.checked = nextChecked;
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
                restoredAny = true;
            } else if (type === 'radio') {
                if (String(el.value) === String(val)) {
                    el.checked = true;
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                    restoredAny = true;
                }
            } else if (el.tagName === 'SELECT' && el.multiple && Array.isArray(val)) {
                const set = new Set(val.map(String));
                Array.from(el.options).forEach((o) => { o.selected = set.has(o.value); });
                el.dispatchEvent(new Event('change', { bubbles: true }));
                restoredAny = true;
            } else if (val !== null && val !== undefined) {
                const strVal = String(val);
                if (el.value !== strVal) {
                    el.value = strVal;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
                restoredAny = true;
            }
        }

        if (restoredAny && typeof options.onRestored === 'function') {
            options.onRestored(saved);
        }
        return restoredAny;
    }

    function clear() {
        clearToolDraft(toolKey);
    }

    const hasRestored = restore();

    let saveTimer = 0;
    const scheduleSave = () => {
        window.clearTimeout(saveTimer);
        saveTimer = window.setTimeout(save, 180);
    };

    formEl.addEventListener('input', scheduleSave);
    formEl.addEventListener('change', save);

    return { save, restore, clear, hasRestored };
}

/**
 * Hiển thị dải thông báo nhỏ gọn khi vừa khôi phục thông tin đã nhập trước đó.
 *
 * @param {HTMLElement|null} anchorEl
 * @param {{ message?: string, onReset?: () => void }} [options]
 */
export function showDraftRestoredBanner(anchorEl, options = {}) {
    if (!anchorEl || !anchorEl.parentNode) return null;
    const existing = anchorEl.parentNode.querySelector('[data-mayhen-draft-notice]');
    if (existing) existing.remove();

    const box = document.createElement('div');
    box.className = 'mayhen_client_draft_notice';
    box.setAttribute('data-mayhen-draft-notice', '1');
    box.setAttribute('role', 'status');

    const textSpan = document.createElement('span');
    textSpan.className = 'mayhen_client_draft_notice_text';
    textSpan.textContent = options.message || '✦ Đã tự động giữ lại toàn bộ thông tin bạn đã nhập và lựa chọn trước đó.';
    box.appendChild(textSpan);

    const actions = document.createElement('span');
    actions.className = 'mayhen_client_draft_notice_actions';

    if (typeof options.onReset === 'function') {
        const resetBtn = document.createElement('button');
        resetBtn.type = 'button';
        resetBtn.className = 'mayhen_client_draft_notice_reset';
        resetBtn.textContent = 'Nhập lại mới';
        resetBtn.addEventListener('click', () => {
            box.remove();
            options.onReset();
        });
        actions.appendChild(resetBtn);
    }

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'mayhen_client_draft_notice_close';
    closeBtn.setAttribute('aria-label', 'Đóng thông báo');
    closeBtn.textContent = '×';
    closeBtn.addEventListener('click', () => box.remove());
    actions.appendChild(closeBtn);

    box.appendChild(actions);
    anchorEl.parentNode.insertBefore(box, anchorEl);
    return box;
}
