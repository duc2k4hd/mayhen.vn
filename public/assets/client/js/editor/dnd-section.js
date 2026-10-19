/**
 * MAYHEN.VN — INVITATION EDITOR V2
 * Native Pointer Events Section Drag-and-Drop Engine
 * Kéo thả sắp xếp thứ tự section mượt mà trên cả máy tính & điện thoại (Zero external libraries)
 * Strict: Pure Vanilla JavaScript ES Modules
 */

import { ActionTypes } from './actions.js';

export class SectionDnDManager {
    constructor(store, options = {}) {
        this.store = store;
        this.listEl = options.listEl || document.querySelector('.mayhen_editor_sections_list');
        this.previewIframe = options.previewIframe || document.getElementById('mayhen_editor_preview_iframe');
        this.mobileIframe = options.mobileIframe || document.getElementById('mayhen_editor_mobile_preview_iframe');

        this.dragItem = null;
        this.dropIndicator = null;
        this.startY = 0;
        this.isDragging = false;

        this.init();
    }

    init() {
        if (!this.listEl) return;

        // Tạo sẵn thanh drop indicator
        this.dropIndicator = document.createElement('div');
        this.dropIndicator.className = 'mayhen_editor_drop_indicator';
        this.dropIndicator.style.display = 'none';

        // Lắng nghe sự kiện kéo thả trên danh sách
        this.listEl.addEventListener('pointerdown', (e) => this.handlePointerDown(e));
        this.listEl.addEventListener('pointermove', (e) => this.handlePointerMove(e));
        this.listEl.addEventListener('pointerup', (e) => this.handlePointerUp(e));
        this.listEl.addEventListener('pointercancel', (e) => this.handlePointerUp(e));

        // Lắng nghe các nút hành động (Ẩn/Hiện, Mũi tên Lên/Xuống)
        this.listEl.addEventListener('click', (e) => this.handleClickAction(e));
    }

    handlePointerDown(e) {
        const handle = e.target.closest('.mayhen_editor_drag_handle');
        if (!handle) return;

        const item = handle.closest('.mayhen_editor_section_card');
        if (!item) return;

        this.dragItem = item;
        this.startY = e.clientY;
        this.isDragging = false;

        handle.setPointerCapture(e.pointerId);
    }

    handlePointerMove(e) {
        if (!this.dragItem) return;

        const deltaY = Math.abs(e.clientY - this.startY);
        if (!this.isDragging && deltaY > 6) {
            this.isDragging = true;
            this.dragItem.classList.add('is-dragging');
            this.dragItem.style.opacity = '0.4';
        }

        if (this.isDragging) {
            e.preventDefault();
            const targetItem = this.getCardUnderPointer(e.clientY);
            if (targetItem && targetItem !== this.dragItem) {
                const rect = targetItem.getBoundingClientRect();
                const mid = rect.top + rect.height / 2;

                if (e.clientY < mid) {
                    this.listEl.insertBefore(this.dropIndicator, targetItem);
                } else {
                    this.listEl.insertBefore(this.dropIndicator, targetItem.nextSibling);
                }
                this.dropIndicator.style.display = 'block';
            }
        }
    }

    handlePointerUp(e) {
        if (!this.dragItem) return;

        if (this.isDragging && this.dropIndicator.parentNode) {
            // Chèn phần tử vào vị trí của indicator
            this.listEl.insertBefore(this.dragItem, this.dropIndicator);
            this.dropIndicator.style.display = 'none';
            if (this.dropIndicator.parentNode) {
                this.dropIndicator.parentNode.removeChild(this.dropIndicator);
            }

            this.commitNewOrder();
        }

        this.dragItem.classList.remove('is-dragging');
        this.dragItem.style.opacity = '1';
        this.dragItem = null;
        this.isDragging = false;
    }

    getCardUnderPointer(y) {
        const cards = Array.from(this.listEl.querySelectorAll('.mayhen_editor_section_card'));
        return cards.find((card) => {
            if (card === this.dragItem) return false;
            const rect = card.getBoundingClientRect();
            return y >= rect.top && y <= rect.bottom;
        });
    }

    /**
     * Xử lý click nút ẩn/hiện hoặc di chuyển nhanh
     */
    handleClickAction(e) {
        const target = e.target;

        // Nút Ẩn/Hiện
        const toggleBtn = target.closest('[data-section-action="toggle-visibility"]');
        if (toggleBtn) {
            const card = toggleBtn.closest('.mayhen_editor_section_card');
            const sectionId = card.getAttribute('data-section-id');
            const isCurrentlyHidden = card.classList.contains('is-hidden');
            const newHiddenState = !isCurrentlyHidden;

            card.classList.toggle('is-hidden', newHiddenState);

            this.store.dispatch({
                type: ActionTypes.TOGGLE_SECTION_VISIBILITY,
                payload: {
                    section_id: sectionId,
                    is_hidden: newHiddenState,
                },
            });

            this.sendSyncMessage('MAYHEN_TOGGLE_SECTION', {
                section_id: sectionId,
                is_hidden: newHiddenState,
            });
            return;
        }

        // Nút Di chuyển lên
        const moveUpBtn = target.closest('[data-section-action="move-up"]');
        if (moveUpBtn) {
            const card = moveUpBtn.closest('.mayhen_editor_section_card');
            const prev = card.previousElementSibling;
            if (prev && prev.classList.contains('mayhen_editor_section_card')) {
                this.listEl.insertBefore(card, prev);
                this.commitNewOrder();
            }
            return;
        }

        // Nút Di chuyển xuống
        const moveDownBtn = target.closest('[data-section-action="move-down"]');
        if (moveDownBtn) {
            const card = moveDownBtn.closest('.mayhen_editor_section_card');
            const next = card.nextElementSibling;
            if (next && next.classList.contains('mayhen_editor_section_card')) {
                this.listEl.insertBefore(next, card);
                this.commitNewOrder();
            }
            return;
        }
    }

    /**
     * Xác nhận và lưu thứ tự mới vào Store & báo sang Canvas Iframe
     */
    commitNewOrder() {
        const cards = Array.from(this.listEl.querySelectorAll('.mayhen_editor_section_card'));
        const newOrder = cards.map((c) => c.getAttribute('data-section-id')).filter(Boolean);

        this.store.dispatch({
            type: ActionTypes.REORDER_SECTIONS,
            payload: {
                sections_order: newOrder,
            },
        });

        this.sendSyncMessage('MAYHEN_REORDER_SECTIONS', {
            order: newOrder,
        });
    }

    sendSyncMessage(type, payload) {
        const msg = { type, ...payload };
        try {
            if (this.previewIframe && this.previewIframe.contentWindow) {
                this.previewIframe.contentWindow.postMessage(msg, '*');
            }
            if (this.mobileIframe && this.mobileIframe.contentWindow) {
                this.mobileIframe.contentWindow.postMessage(msg, '*');
            }
        } catch (err) {
            // Cross-origin safety
        }
    }
}
