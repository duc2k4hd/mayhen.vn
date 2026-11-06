/**
 * MAYHEN.VN — INVITATION EDITOR V2
 * Single State Store Engine (Unidirectional Data Flow)
 * Strict: Pure Vanilla JavaScript (ES Modules), Zero external dependencies
 */

import { ActionTypes } from './actions.js';
import { HistoryManager } from './history.js';
import { Autosaver } from './autosaver.js';

export class EditorStore {
    static parseCustomConfig(value) {
        try {
            const config = typeof value === 'string' ? JSON.parse(value) : value;
            return config && typeof config === 'object' && !Array.isArray(config) ? config : {};
        } catch (error) {
            return {};
        }
    }

    static flattenWeddingFields(wedding) {
        const fields = {};
        const visit = (value, path) => {
            if (path && path.split('.').some((part) => !/^[A-Za-z0-9_-]+$/.test(part)
                || ['__proto__', 'prototype', 'constructor'].includes(part))) return;
            if (value && typeof value === 'object') {
                const keys = Object.keys(value).sort((a, b) => Number(a.includes('.')) - Number(b.includes('.')));
                keys.forEach((key) => visit(value[key], path ? `${path}.${key}` : key));
            } else if (path && value !== undefined && !Object.hasOwn(fields, path)) {
                fields[path] = value;
            }
        };
        visit(wedding, '');
        return fields;
    }

    static canonicalWeddingPath(key) {
        const path = key.replace(/^wedding\./, '');
        const rootFields = ['groom_name', 'bride_name', 'groom_parents', 'bride_parents', 'wedding_date',
            'venue_name', 'venue_address', 'story', 'cover_photo_url', 'music_url', 'bank_name',
            'bank_account_number', 'bank_account_name'];
        return rootFields.includes(path) || path.startsWith('extra_data.') ? path : `extra_data.${path}`;
    }

    static normalizeWeddingFields(wedding) {
        const normalized = {};
        Object.entries(EditorStore.flattenWeddingFields(wedding)).forEach(([key, value]) => {
            EditorStore.setByPath(normalized, EditorStore.canonicalWeddingPath(key), value);
        });
        return normalized;
    }

    constructor(initialState = {}) {
        this.state = this.getInitialState(initialState);
        this.listeners = new Set();
        this.pendingMedia = new Set();
        this.mediaTokens = new Map();
        this.history = new HistoryManager();
        this.autosaver = new Autosaver(this);
    }

    getInitialState(override = {}) {
        return {
            invitation_id: override.invitation_id || null,
            template_id: override.template_id || null,
            template_slug: override.template_slug || '',
            revision_id: override.revision_id || 0,
            schema_version: override.schema_version || '2.0',

            wedding: {
                groom_name: '',
                bride_name: '',
                groom_parents: '',
                bride_parents: '',
                wedding_date: '',
                venue_name: '',
                venue_address: '',
                story: '',
                cover_photo_url: '',
                music_url: '',
                bank_name: '',
                bank_account_number: '',
                bank_account_name: '',
                extra_data: {},
                ...EditorStore.normalizeWeddingFields(override.wedding || {}),
            },

            custom_config: {
                sections_order: [],
                sections_hidden: [],
                theme: {
                    palette_id: '',
                    font_heading: 'Playfair Display',
                    font_body: 'Inter',
                },
                wax_seal: {},
                ...(override.custom_config || {}),
            },

            ui: {
                selectedSectionId: null,
                activeTab: 'content', // 'content' | 'sections' | 'theme' | 'music' | 'templates'
                previewDevice: 'mobile', // 'mobile' | 'desktop'
                saveStatus: 'idle', // 'idle' | 'pending' | 'saving' | 'saved' | 'conflict' | 'offline' | 'local_only'
                saveStatusMeta: null,
                isDirty: false,
                canUndo: false,
                canRedo: false,
            },
        };
    }

    getState() {
        return this.state;
    }

    beginMedia(key) {
        const token = Symbol(key);
        this.mediaTokens.set(key, token);
        this.pendingMedia.add(key);
        return token;
    }

    isCurrentMedia(key, token) {
        return this.mediaTokens.get(key) === token;
    }

    finishMedia(key, token) {
        if (this.isCurrentMedia(key, token)) this.pendingMedia.delete(key);
    }

    subscribe(listener) {
        if (typeof listener === 'function') {
            this.listeners.add(listener);
            return () => this.listeners.delete(listener);
        }
        return () => {};
    }

    notify(action) {
        this.listeners.forEach((listener) => {
            try {
                listener(this.state, action);
            } catch (err) {
                console.error('[EditorStore] Listener notification error:', err);
            }
        });
    }

    setSaveStatus(status, meta = null) {
        this.state.ui.saveStatus = status;
        this.state.ui.saveStatusMeta = meta;
        if (status === 'saved') {
            this.state.ui.isDirty = false;
        }
        this.notify({ type: ActionTypes.SET_SAVE_STATUS, payload: { status, meta } });
    }

    updateRevision(newRevisionId) {
        this.state.revision_id = newRevisionId;
        this.notify({ type: ActionTypes.UPDATE_REVISION, payload: { revision_id: newRevisionId } });
    }

    /**
     * Helper cập nhật thuộc tính lồng nhau theo đường dẫn dạng chuỗi (e.g. 'wedding.groom_name')
     */
    static setByPath(obj, path, value) {
        const parts = path.split('.');
        if (parts.some((part) => !/^[A-Za-z0-9_-]+$/.test(part)
            || ['__proto__', 'prototype', 'constructor'].includes(part))) return;
        let current = obj;
        for (let i = 0; i < parts.length - 1; i++) {
            const p = parts[i];
            if (!current[p] || typeof current[p] !== 'object') {
                current[p] = /^\d+$/.test(parts[i + 1]) ? [] : {};
            }
            current = current[p];
        }
        current[parts[parts.length - 1]] = value;
    }

    /**
     * Dispatch an action và chuyển đổi State
     */
    dispatch(action) {
        if (!action || !action.type) return;

        const { type, payload } = action;
        if (type === ActionTypes.UPDATE_FIELD) {
            const key = payload?.key;
            if (!key || key.split('.').some((part) => !/^[A-Za-z0-9_-]+$/.test(part)
                || ['__proto__', 'prototype', 'constructor'].includes(part))) return;
            const path = key.includes('.') ? key : key in this.state.wedding ? `wedding.${key}` : key;
            const current = path.split('.').reduce((value, part) => value?.[part], this.state);
            if (current === payload.value) return;
        }

        // Các action làm thay đổi dữ liệu cần lưu snapshot vào Undo History
        const dataMutatingActions = [
            ActionTypes.UPDATE_FIELD,
            ActionTypes.UPDATE_WEDDING,
            ActionTypes.UPDATE_EXTRA_DATA,
            ActionTypes.REORDER_SECTIONS,
            ActionTypes.TOGGLE_SECTION_VISIBILITY,
            ActionTypes.SWITCH_TEMPLATE,
            ActionTypes.SET_THEME_PALETTE,
            ActionTypes.UPDATE_CUSTOM_CONFIG,
        ];

        if (dataMutatingActions.includes(type)) {
            this.history.push({
                wedding: this.state.wedding,
                custom_config: this.state.custom_config,
            });
            this.state.ui.isDirty = true;
        }

        switch (type) {
            case ActionTypes.INIT_STORE: {
                this.state = this.getInitialState(payload);
                this.history.clear();
                break;
            }

            case ActionTypes.UPDATE_FIELD: {
                const { key, value } = payload;
                if (!key) break;

                if (key.includes('.')) {
                    EditorStore.setByPath(this.state, key, value);
                } else if (key in this.state.wedding) {
                    this.state.wedding[key] = value;
                } else {
                    this.state[key] = value;
                }

                this.autosaver.schedule();
                break;
            }

            case ActionTypes.UPDATE_WEDDING: {
                this.state.wedding = {
                    ...this.state.wedding,
                    ...payload,
                };
                this.autosaver.schedule();
                break;
            }

            case ActionTypes.UPDATE_EXTRA_DATA: {
                this.state.wedding.extra_data = {
                    ...(this.state.wedding.extra_data || {}),
                    ...payload,
                };
                this.autosaver.schedule();
                break;
            }

            case ActionTypes.REORDER_SECTIONS: {
                const { sections_order } = payload;
                if (Array.isArray(sections_order)) {
                    this.state.custom_config.sections_order = [...sections_order];
                    this.autosaver.schedule();
                }
                break;
            }

            case ActionTypes.TOGGLE_SECTION_VISIBILITY: {
                const { section_id, is_hidden } = payload;
                let hidden = new Set(this.state.custom_config.sections_hidden || []);
                if (is_hidden) {
                    hidden.add(section_id);
                } else {
                    hidden.delete(section_id);
                }
                this.state.custom_config.sections_hidden = Array.from(hidden);
                this.autosaver.schedule();
                break;
            }

            case ActionTypes.SWITCH_TEMPLATE: {
                const { template_id, template_slug, default_config } = payload;
                this.state.template_id = template_id;
                this.state.template_slug = template_slug;

                if (default_config && default_config.sections_order) {
                    this.state.custom_config.sections_order = [...default_config.sections_order];
                }
                this.autosaver.schedule();
                break;
            }

            case ActionTypes.SET_THEME_PALETTE: {
                const { palette_id, palette_data } = payload;
                this.state.custom_config.theme = {
                    ...(this.state.custom_config.theme || {}),
                    palette_id: palette_id,
                    ...(palette_data || {}),
                };
                this.autosaver.schedule();
                break;
            }

            case ActionTypes.UPDATE_CUSTOM_CONFIG: {
                this.state.custom_config = {
                    ...this.state.custom_config,
                    ...payload,
                };
                this.autosaver.schedule();
                break;
            }

            case ActionTypes.SELECT_SECTION: {
                this.state.ui.selectedSectionId = payload.section_id || null;
                break;
            }

            case ActionTypes.SET_ACTIVE_TAB: {
                this.state.ui.activeTab = payload.tab || 'content';
                break;
            }

            case ActionTypes.SET_PREVIEW_DEVICE: {
                this.state.ui.previewDevice = payload.device === 'desktop' ? 'desktop' : 'mobile';
                break;
            }

            case ActionTypes.RESTORE_DRAFT: {
                const { draft } = payload;
                if (draft) {
                    if (draft.wedding) this.state.wedding = { ...this.state.wedding, ...draft.wedding };
                    if (draft.custom_config) this.state.custom_config = { ...this.state.custom_config, ...draft.custom_config };
                    if (draft.revision_id) this.state.revision_id = draft.revision_id;
                }
                break;
            }

            case ActionTypes.UNDO: {
                const restored = this.history.undo({
                    wedding: this.state.wedding,
                    custom_config: this.state.custom_config,
                });
                if (restored) {
                    this.state.wedding = restored.wedding;
                    this.state.custom_config = restored.custom_config;
                    this.state.ui.isDirty = true;
                    this.autosaver.schedule();
                }
                break;
            }

            case ActionTypes.REDO: {
                const restored = this.history.redo({
                    wedding: this.state.wedding,
                    custom_config: this.state.custom_config,
                });
                if (restored) {
                    this.state.wedding = restored.wedding;
                    this.state.custom_config = restored.custom_config;
                    this.state.ui.isDirty = true;
                    this.autosaver.schedule();
                }
                break;
            }

            default:
                break;
        }

        // Cập nhật trạng thái canUndo / canRedo
        this.state.ui.canUndo = this.history.canUndo();
        this.state.ui.canRedo = this.history.canRedo();

        this.notify(action);
    }
}
