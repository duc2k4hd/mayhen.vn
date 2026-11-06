/**
 * MAYHEN.VN — INVITATION EDITOR V2
 * Template Switcher: lưu dữ liệu chuyển tiếp trước khi tải lại mẫu mới
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
     * Thực hiện đổi mẫu thiệp: Bảo toàn 100% dữ liệu cưới và tải đúng schema của mẫu mới
     */
    switchTemplate({ id, slug, name, thumb, tier }) {
        const state = this.store.getState();

        // Nếu trùng mẫu hiện tại thì chỉ đóng modal
        if (state.template_id === id) {
            this.closeModal();
            return;
        }

        if (this.store.autosaver?.hasConflict) {
            this.showToast('Thiệp đang có xung đột. Hãy kiểm tra bản nháp trước khi đổi mẫu.');
            return;
        }
        if (state.invitation_id && (this.store.autosaver?.isSaving || this.store.autosaver?.timer)) {
            this.showToast('Thiệp đang được lưu. Vui lòng đợi lưu xong trước khi đổi mẫu.');
            return;
        }

        // 1. Thu thập toàn bộ dữ liệu người dùng đã nhập trên form hiện tại
        const draftData = {};
        let hasPendingImages = false;
        const isUserImage = (value) => typeof value === 'string' && (
            value.startsWith('data:image') || value.startsWith('http://') || value.startsWith('https://') ||
            value.startsWith('/storage/') || value.startsWith('/uploads/')
        );
        const inputs = document.querySelectorAll('[data-sync-key]');
        inputs.forEach((inp) => {
            const key = inp.getAttribute('data-sync-key');
            if (key && inp.value !== undefined) {
                const val = inp.value;
                const isPhoto = key.includes('photo') || key.includes('cover') || key.includes('image') || key.includes('gallery') || key.includes('album');
                // Nếu là trường ảnh, chỉ giữ lại nếu người dùng đã tự upload (data:image hoặc http), không mang asset mặc định của mẫu cũ sang mẫu mới
                if (isPhoto) {
                    if (typeof val === 'string' && val.startsWith('blob:')) {
                        hasPendingImages = true;
                    } else if (isUserImage(val)) {
                        draftData[key] = val;
                    }
                } else {
                    draftData[key] = val;
                }
            }
        });

        // Hợp nhất cả state hiện có trong store
        const weddingState = state.wedding || {};
        Object.keys(weddingState).forEach((wKey) => {
            const val = weddingState[wKey];
            const isPhoto = wKey.includes('photo') || wKey.includes('cover') || wKey.includes('image') || wKey.includes('gallery') || wKey.includes('album');
            if (val && typeof val === 'string' && !Object.hasOwn(draftData, `wedding.${wKey}`)) {
                if (isPhoto) {
                    if (val.startsWith('blob:')) {
                        hasPendingImages = true;
                    } else if (isUserImage(val)) {
                        draftData[`wedding.${wKey}`] = val;
                    }
                } else {
                    draftData[`wedding.${wKey}`] = val;
                }
            }
        });

        if (hasPendingImages) {
            this.showToast('Ảnh đang được xử lý. Vui lòng đợi ảnh hoàn tất trước khi đổi mẫu.');
            return;
        }

        // Sinh thêm alias cho các trường tương đương để chuyển đổi giữa các mẫu khác nhau không bao giờ bị mất dữ liệu
        const fieldAliases = {
            'groom_bank_account': 'groom_account_number',
            'groom_account_number': 'groom_bank_account',
            'bride_bank_account': 'bride_account_number',
            'bride_account_number': 'bride_bank_account',
            'groom_bank_holder': 'groom_account_name',
            'groom_account_name': 'groom_bank_holder',
            'bride_bank_holder': 'bride_account_name',
            'bride_account_name': 'bride_bank_holder',
            'event_party_address': 'ceremony_address',
            'ceremony_address': 'event_party_address',
            'party_address': 'event_party_address',
            'groom_party_address': 'event_party_address',
            'bride_party_address': 'event_party_address',
            'event_party_date': 'wedding_date',
            'wedding_date': 'event_party_date',
            'event_party_time': 'party_time',
            'party_time': 'event_party_time',
            'groom_party_time': 'event_party_time'
        };

        Object.keys(fieldAliases).forEach((fromKey) => {
            const toKey = fieldAliases[fromKey];
            const fromPath = [`wedding.extra_data.${fromKey}`, `wedding.${fromKey}`, fromKey]
                .find((key) => Object.hasOwn(draftData, key));
            const toPaths = [`wedding.extra_data.${toKey}`, `wedding.${toKey}`, toKey];
            if (fromPath && !toPaths.some((key) => Object.hasOwn(draftData, key))) {
                draftData[toPaths[0]] = draftData[fromPath];
            }
        });

        // Lưu vào sessionStorage để trang mẫu mới tự động khôi phục toàn vẹn dữ liệu
        try {
            const existingDraft = sessionStorage.getItem('mayhen_transferred_draft');
            if (existingDraft !== null) {
                const backupKey = `mayhen_transferred_draft_recovery_${Date.now()}_${Math.random().toString(36).slice(2, 12)}`;
                if (sessionStorage.getItem(backupKey) !== null) throw new Error('Bản dự phòng đã tồn tại.');
                sessionStorage.setItem(backupKey, existingDraft);
                if (sessionStorage.getItem(backupKey) !== existingDraft
                    || sessionStorage.getItem('mayhen_transferred_draft') !== existingDraft) {
                    throw new Error('Không thể giữ bản nháp chuyển tiếp.');
                }
            }
            const transferredDraft = JSON.stringify({
                invitation_id: state.invitation_id ?? null,
                revision_id: state.revision_id,
                destination_template_slug: slug,
                wedding: draftData,
                custom_config: state.custom_config || {},
                timestamp: Date.now()
            });
            sessionStorage.setItem('mayhen_transferred_draft', transferredDraft);
            if (sessionStorage.getItem('mayhen_transferred_draft') !== transferredDraft) {
                throw new Error('Không thể đọc dữ liệu chuyển tiếp.');
            }
            sessionStorage.removeItem('mayhen_skip_transferred_draft');
        } catch (error) {
            this.showToast('Không thể lưu dữ liệu để đổi mẫu. Vui lòng giữ trang hiện tại và thử lại.');
            return;
        }

        this.closeModal();
        this.showToast(`Đang chuyển sang mẫu "${name}"...`);

        // 2. Chuyển hướng sang URL của mẫu mới với đầy đủ schema form và preview đồng nhất
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('template', slug);

        setTimeout(() => {
            window.location.href = currentUrl.toString();
        }, 120);
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
