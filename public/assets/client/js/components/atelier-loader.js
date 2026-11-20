/**
 * Mây Hẹn — Reusable Atelier Wax-Seal Preloader Controller
 * Pure Vanilla ES Module
 * - Mặc định hiển thị tối thiểu 500ms (0.5s) để có thời gian tải nội dung & chuyển cảnh mượt.
 * - KHÔNG tự tắt khi nội dung chưa tải xong hoặc bị lỗi (hiệu ứng tiếp tục chạy cho tới khi load xong).
 * - Tự động fallback về con dấu chữ "MH" nếu ảnh favicon chưa cấu hình hoặc lỗi tải.
 */

const loaderStates = new Map();

/**
 * Tự động chuyển sang con dấu chữ "MH" nếu ảnh favicon chưa cấu hình hoặc lỗi tải.
 */
export function bindAtelierLoaderFavicons(root = document) {
    const imgs = root.querySelectorAll('[data-mayhen-loader-favicon]');
    imgs.forEach((img) => {
        if (img.dataset.mayhenFallbackBound === '1') {
            return;
        }
        img.dataset.mayhenFallbackBound = '1';

        const activateFallback = () => {
            const seal = img.closest('[data-mayhen-loader-seal]');
            if (seal) {
                seal.classList.add('is-fallback');
            }
        };

        img.addEventListener('error', activateFallback, { once: true });

        if (img.complete && img.naturalWidth === 0) {
            activateFallback();
        }
    });
}

/**
 * Hiển thị tức thì hiệu ứng chờ tải Mây Hẹn Atelier.
 *
 * @param {string} loaderId
 * @param {{ title?: string, status?: string, minDurationMs?: number }} [options]
 */
export function showAtelierLoader(loaderId, options = {}) {
    const loader = document.getElementById(loaderId);
    if (!loader) {
        return;
    }

    bindAtelierLoaderFavicons(loader);

    const prevState = loaderStates.get(loaderId);
    if (prevState && prevState.hideTimer) {
        clearTimeout(prevState.hideTimer);
    }

    if (typeof options.title === 'string' && options.title.trim() !== '') {
        const titleEl = loader.querySelector('[data-mayhen-loader-title]');
        if (titleEl) {
            titleEl.textContent = options.title;
        }
    }

    if (typeof options.status === 'string' && options.status.trim() !== '') {
        const statusEl = loader.querySelector('[data-mayhen-loader-status]');
        if (statusEl) {
            statusEl.textContent = options.status;
        }
    }

    const minDurationMs = typeof options.minDurationMs === 'number' && options.minDurationMs >= 0
        ? options.minDurationMs
        : 500;

    loaderStates.set(loaderId, {
        shownAt: performance.now(),
        minDurationMs,
        hideTimer: null,
    });

    loader.classList.add('is-visible');
    loader.setAttribute('aria-hidden', 'false');
}

/**
 * Ẩn mượt hiệu ứng chờ tải Mây Hẹn Atelier.
 * Đảm bảo đã hiển thị đủ tối thiểu minDurationMs (mặc định 0.5s) trừ khi truyền { immediate: true }.
 *
 * @param {string} loaderId
 * @param {{ immediate?: boolean }} [options]
 */
export function hideAtelierLoader(loaderId, options = {}) {
    const loader = document.getElementById(loaderId);
    const state = loaderStates.get(loaderId);

    if (state && state.hideTimer) {
        clearTimeout(state.hideTimer);
        state.hideTimer = null;
    }

    if (!loader) {
        loaderStates.delete(loaderId);
        return;
    }

    const doHide = () => {
        loader.classList.remove('is-visible');
        loader.setAttribute('aria-hidden', 'true');
        loaderStates.delete(loaderId);
    };

    if (options.immediate || !state) {
        doHide();
        return;
    }

    const elapsed = performance.now() - state.shownAt;
    const remaining = Math.max(0, state.minDurationMs - elapsed);

    if (remaining <= 0) {
        doHide();
    } else {
        state.hideTimer = window.setTimeout(doHide, remaining);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => bindAtelierLoaderFavicons());
} else {
    bindAtelierLoaderFavicons();
}
