/**
 * MAYHEN.VN — INVITATION EDITOR V2
 * Zero-Loss Template Switcher
 * Chuyển đổi qua lại giữa các mẫu thiệp cưới tức thì không reload trang & bảo toàn 100% dữ liệu cưới
 * Strict: Pure Vanilla JavaScript ES Modules
 */

import { ActionTypes } from './actions.js';

export class TemplateSwitcher {
    constructor(store, options = {}) {
        this.store = store;
        this.previewIframe = options.previewIframe || document.getElementById('mayhen_editor_preview_iframe');
        this.mobileIframe = options.mobileIframe || document.getElementById('mayhen_editor_mobile_preview_iframe');
        this.modal = document.getElementById('mayhen_template_switcher_modal');
        this.chipName = document.querySelector('.mayhen_editor_template_chip_name');
        this.chipThumb = document.querySelector('.mayhen_editor_template_chip_thumb');

        this.init();
    }

    init() {
        if (!this.modal) return;

        // Bắt sự kiện chọn mẫu trong modal
        const templateCards = this.modal.querySelectorAll('[data-template-switch]');
        templateCards.forEach((card) => {
            card.addEventListener('click', (e) => {
                e.preventDefault();
                const templateId = parseInt(card.getAttribute('data-template-id'), 10);
                const templateSlug = card.getAttribute('data-template-slug');
                const templateName = card.getAttribute('data-template-name');
                const templateThumb = card.getAttribute('data-template-thumb');
                const templateTier = card.getAttribute('data-template-tier') || 'free';
                const isLocked = card.getAttribute('data-template-locked') === 'true';

                if (isLocked) {
                    const loginUrl = card.getAttribute('href');
                    if (confirm(`Mẫu "${templateName}" thuộc gói cao cấp. Bạn cần đăng nhập để sử dụng. Chuyển đến trang đăng nhập ngay?`)) {
                        window.location.href = loginUrl;
                    }
                    return;
                }

                this.switchTemplate({
                    id: templateId,
                    slug: templateSlug,
                    name: templateName,
                    thumb: templateThumb,
                    tier: templateTier,
                });
            });
        });
    }

    /**
     * Thực hiện đổi mẫu thiệp
     */
    switchTemplate({ id, slug, name, thumb, tier }) {
        const state = this.store.getState();

        // Nếu trùng mẫu hiện tại thì chỉ đóng modal
        if (state.template_id === id) {
            this.closeModal();
            return;
        }

        // 1. Dispatch action lưu trữ vào Store
        this.store.dispatch({
            type: ActionTypes.SWITCH_TEMPLATE,
            payload: {
                template_id: id,
                template_slug: slug,
            },
        });

        // 1.1 Đồng bộ trực tiếp vào Form Input #template_id để khi bấm submit sẽ gửi template_id mới lên máy chủ
        const templateInput = document.getElementById('template_id');
        if (templateInput) {
            templateInput.value = id;
        }
        if (window.__MAYHEN_TEMPLATE_ID !== undefined) {
            window.__MAYHEN_TEMPLATE_ID = id;
        }
        if (window.__MAYHEN_TEMPLATE_SLUG !== undefined) {
            window.__MAYHEN_TEMPLATE_SLUG = slug;
        }

        // 2. Cập nhật Chip trên Topbar
        if (this.chipName) this.chipName.textContent = name;
        if (this.chipThumb && thumb) this.chipThumb.src = thumb;

        // Cập nhật card được chọn trong modal
        const cards = this.modal.querySelectorAll('[data-template-switch]');
        cards.forEach((c) => {
            if (parseInt(c.getAttribute('data-template-id'), 10) === id) {
                c.classList.add('mayhen_editor_template_radio_selected');
            } else {
                c.classList.remove('mayhen_editor_template_radio_selected');
            }
        });

        // 3. Cập nhật src cho Iframe Canvas và đồng bộ dữ liệu cưới ngay khi load xong
        const newPreviewUrl = `/xem-thu/${slug}?embed=1&mode=editor`;

        const syncToIframe = (iframe) => {
            if (!iframe) return;
            iframe.src = newPreviewUrl;

            const handleLoad = () => {
                const currentState = this.store.getState();
                const wedding = currentState.wedding;

                // Bắn toàn bộ trường cưới qua postMessage
                Object.keys(wedding).forEach((key) => {
                    const val = wedding[key];
                    if (val !== undefined && val !== null) {
                        iframe.contentWindow?.postMessage({
                            type: 'MAYHEN_SYNC_INPUT',
                            key: `wedding.${key}`,
                            value: val,
                        }, '*');
                    }
                });

                iframe.removeEventListener('load', handleLoad);
            };

            iframe.addEventListener('load', handleLoad);
        };

        syncToIframe(this.previewIframe);
        syncToIframe(this.mobileIframe);

        // 4. Đóng modal
        this.closeModal();

        // 5. Hiện toast thông báo chúc mừng
        this.showToast(`Đã chuyển sang mẫu "${name}" thành công! Mọi thông tin bạn vừa nhập đều được giữ nguyên.`);
    }

    closeModal() {
        if (this.modal) {
            this.modal.classList.remove('mayhen_editor_modal_open');
        }
    }

    showToast(message) {
        let toast = document.getElementById('mayhen_editor_toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'mayhen_editor_toast';
            toast.style.cssText = `
                position: fixed;
                bottom: 24px;
                left: 50%;
                transform: translateX(-50%) translateY(100px);
                background-color: #1c1917;
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 9999px;
                font-size: 0.8125rem;
                font-weight: 500;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                z-index: 9999;
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
                opacity: 0;
                pointer-events: none;
                max-width: 90vw;
                text-align: center;
            `;
            document.body.appendChild(toast);
        }

        toast.textContent = message;
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(100px)';
        }, 3500);
    }
}
