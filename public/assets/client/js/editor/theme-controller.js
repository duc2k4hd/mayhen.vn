/**
 * MAYHEN.VN — INVITATION EDITOR V2
 * Curated 6 Quiet Luxury Palettes & Wax Seal Customizer
 * Strict: Pure Vanilla JavaScript ES Modules
 */

import { ActionTypes } from './actions.js';

export const CURATED_PALETTES = {
    'quiet-luxury': {
        name: 'Quiet Luxury',
        desc: 'Sắc vàng cát ấm áp trên nền giấy ngà thủ công',
        bg: '#FAF8F5',
        primary: '#B45309',
        accent: '#D4AF37',
        text: '#1C1917',
    },
    'sage-botanic': {
        name: 'Sage Botanic',
        desc: 'Hơi thở thảo mộc thanh tao và xanh rêu ô-liu êm đềm',
        bg: '#F5F7F5',
        primary: '#3A4D39',
        accent: '#739072',
        text: '#232D20',
    },
    'warm-terracotta': {
        name: 'Warm Terracotta',
        desc: 'Tông đất nung Địa Trung Hải nồng nàn quyến rũ',
        bg: '#FAF4EF',
        primary: '#C85A32',
        accent: '#E07A5F',
        text: '#35231C',
    },
    'rose-vintage': {
        name: 'Rose Vintage',
        desc: 'Hồng khói cổ điển Pháp dịu dàng và lãng mạn',
        bg: '#FCF7F7',
        primary: '#9D5C63',
        accent: '#D8A47F',
        text: '#382226',
    },
    'champagne-glam': {
        name: 'Champagne Glam',
        desc: 'Vàng rượu sâm panh hoàng gia sang trọng',
        bg: '#FAF6F0',
        primary: '#A67C37',
        accent: '#C5A059',
        text: '#201A10',
    },
    'noir-chic': {
        name: 'Noir Chic',
        desc: 'Tương phản đen huyền bí và vàng ánh kim hiện đại',
        bg: '#F5F5F5',
        primary: '#1F1F1F',
        accent: '#BFA46F',
        text: '#0D0D0D',
    },
};

export class ThemeController {
    constructor(store, options = {}) {
        this.store = store;
        this.paletteGrid = options.paletteGrid || document.querySelector('.mayhen_editor_palette_grid');
        this.sealSwatches = options.sealSwatches || document.querySelector('.mayhen_editor_seal_swatches');
        this.sealInput = options.sealInput || document.getElementById('mayhen_seal_monogram_input');
        this.previewIframe = options.previewIframe || document.getElementById('mayhen_editor_preview_iframe');
        this.mobileIframe = options.mobileIframe || document.getElementById('mayhen_editor_mobile_preview_iframe');

        this.init();
    }

    init() {
        // 1. Lắng nghe chọn bảng màu
        if (this.paletteGrid) {
            this.paletteGrid.addEventListener('click', (e) => {
                const card = e.target.closest('.mayhen_editor_palette_card');
                if (!card) return;

                const paletteId = card.getAttribute('data-palette-id');
                if (!paletteId || !CURATED_PALETTES[paletteId]) return;

                this.setPalette(paletteId);
            });
        }

        // 2. Lắng nghe chọn màu con dấu sáp
        if (this.sealSwatches) {
            this.sealSwatches.addEventListener('click', (e) => {
                const btn = e.target.closest('.mayhen_editor_seal_btn');
                if (!btn) return;

                const color = btn.getAttribute('data-seal-color');
                if (!color) return;

                this.setSealColor(color);
            });
        }

        // 3. Lắng nghe nhập chữ dập nổi tem sáp
        if (this.sealInput) {
            this.sealInput.addEventListener('input', (e) => {
                const monogram = e.target.value.toUpperCase().slice(0, 4);
                this.setSealMonogram(monogram);
            });
        }
    }

    setPalette(paletteId) {
        const palette = CURATED_PALETTES[paletteId];
        if (!palette) return;

        // Cập nhật UI active
        if (this.paletteGrid) {
            const cards = this.paletteGrid.querySelectorAll('.mayhen_editor_palette_card');
            cards.forEach((c) => {
                if (c.getAttribute('data-palette-id') === paletteId) {
                    c.classList.add('active');
                } else {
                    c.classList.remove('active');
                }
            });
        }

        // Dispatch vào Store
        this.store.dispatch({
            type: ActionTypes.SET_THEME_PALETTE,
            payload: {
                palette_id: paletteId,
                palette_data: palette,
            },
        });

        // Bắn postMessage sang Iframe
        this.sendSyncMessage('MAYHEN_CHANGE_THEME', {
            palette_id: paletteId,
            palette: palette,
        });
    }

    setSealColor(color) {
        if (this.sealSwatches) {
            const btns = this.sealSwatches.querySelectorAll('.mayhen_editor_seal_btn');
            btns.forEach((b) => {
                if (b.getAttribute('data-seal-color') === color) {
                    b.classList.add('active');
                } else {
                    b.classList.remove('active');
                }
            });
        }

        const currentConfig = this.store.getState().custom_config;
        const currentSeal = currentConfig.wax_seal || {};

        this.store.dispatch({
            type: ActionTypes.UPDATE_CUSTOM_CONFIG,
            payload: {
                wax_seal: {
                    ...currentSeal,
                    color: color,
                },
            },
        });

        this.sendSyncMessage('MAYHEN_UPDATE_WAX_SEAL', {
            wax_seal: {
                ...currentSeal,
                color: color,
            },
        });
    }

    setSealMonogram(monogram) {
        const currentConfig = this.store.getState().custom_config;
        const currentSeal = currentConfig.wax_seal || {};

        this.store.dispatch({
            type: ActionTypes.UPDATE_CUSTOM_CONFIG,
            payload: {
                wax_seal: {
                    ...currentSeal,
                    monogram: monogram,
                },
            },
        });

        this.sendSyncMessage('MAYHEN_UPDATE_WAX_SEAL', {
            wax_seal: {
                ...currentSeal,
                monogram: monogram,
            },
        });
    }

    sendSyncMessage(type, payload) {
        const msg = { type, ...payload };
        try {
            if (this.previewIframe?.contentWindow) {
                this.previewIframe.contentWindow.postMessage(msg, '*');
            }
            if (this.mobileIframe?.contentWindow) {
                this.mobileIframe.contentWindow.postMessage(msg, '*');
            }
        } catch (err) {
            // Safety
        }
    }
}
