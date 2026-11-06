/**
 * MAYHEN.VN — INVITATION EDITOR V2
 * Autosaver Engine: LocalStorage Instant Mirror + 1.5s Debounced Server Sync
 * Strict: Pure Vanilla JavaScript, Zero external dependencies
 */

export class Autosaver {
    constructor(store, options = {}) {
        this.store = store;
        this.debounceMs = options.debounceMs || 1500;
        this.timer = null;
        this.isSaving = false;
        this.changeVersion = 0;
        this.hasConflict = false;
        this.protectedLocalDraft = null;
        this.endpoint = options.endpoint || '/api/editor/autosave';

        this.init();
    }

    init() {
        // Cảnh báo khi người dùng tắt tab trong lúc đang có thay đổi chưa lưu lên server
        window.addEventListener('beforeunload', (e) => {
            const state = this.store.getState();
            if (state.ui.isDirty) {
                // Lưu ngay bản mới nhất vào LocalStorage trước khi tắt tab
                this.saveToLocalStorage(state);
                e.preventDefault();
                e.returnValue = 'Bạn có những thay đổi chưa được đồng bộ lên máy chủ!';
            }
        });
    }

    getDraftKey(state) {
        if (state.invitation_id) {
            return `mayhen_draft_${state.invitation_id}`;
        }
        return `mayhen_draft_new_${state.template_slug || 'default'}`;
    }

    /**
     * Lưu tức thì vào LocalStorage (chống mất điện / tắt tab đột ngột)
     */
    saveToLocalStorage(state) {
        if (this.protectedLocalDraft) return;
        try {
            const key = this.getDraftKey(state);
            const payload = {
                invitation_id: state.invitation_id,
                template_id: state.template_id,
                template_slug: state.template_slug,
                revision_id: state.revision_id,
                wedding: state.wedding,
                custom_config: state.custom_config,
                updated_at: new Date().toISOString(),
            };
            localStorage.setItem(key, JSON.stringify(payload));
        } catch (err) {
            console.warn('[Autosaver] LocalStorage save failed:', err);
        }
    }

    protectStaleLocalDraft(serverRevision) {
        const state = this.store.getState();
        if (!state.invitation_id) return false;

        try {
            const key = this.getDraftKey(state);
            const raw = localStorage.getItem(key);
            if (raw === null) return false;

            let draft;
            try {
                draft = JSON.parse(raw);
            } catch (error) {
                draft = null;
            }

            if (draft && String(draft.invitation_id) === String(state.invitation_id)
                && Number.isSafeInteger(draft.revision_id) && draft.revision_id === serverRevision) {
                return false;
            }

            this.protectedLocalDraft = { key, raw };
            this.hasConflict = true;
            return true;
        } catch (error) {
            console.warn('[Autosaver] LocalStorage read failed:', error);
            return false;
        }
    }

    archiveProtectedLocalDraft() {
        const protectedDraft = this.protectedLocalDraft;
        if (!protectedDraft) return false;

        try {
            if (localStorage.getItem(protectedDraft.key) !== protectedDraft.raw) return false;
            const backupKey = `${protectedDraft.key}_recovery_${Date.now()}_${Math.random().toString(36).slice(2, 12)}`;
            if (localStorage.getItem(backupKey) !== null) return false;
            localStorage.setItem(backupKey, protectedDraft.raw);
            if (localStorage.getItem(backupKey) !== protectedDraft.raw) return false;
            if (localStorage.getItem(protectedDraft.key) !== protectedDraft.raw) return false;
            localStorage.removeItem(protectedDraft.key);
            if (localStorage.getItem(protectedDraft.key) !== null) return false;
            this.protectedLocalDraft = null;
            return true;
        } catch (error) {
            console.warn('[Autosaver] Local draft backup failed:', error);
            return false;
        }
    }

    /**
     * Đọc bản nháp từ LocalStorage nếu có
     */
    getLocalDraft(invitationId, templateSlug = '') {
        try {
            let key = invitationId ? `mayhen_draft_${invitationId}` : `mayhen_draft_new_${templateSlug || 'default'}`;
            let raw = localStorage.getItem(key);
            // Fallback bản nháp cũ mayhen_draft_new nếu chưa có theo slug
            if (!raw && !invitationId) {
                raw = localStorage.getItem('mayhen_draft_new');
            }
            if (!raw) return null;
            return JSON.parse(raw);
        } catch (err) {
            console.warn('[Autosaver] LocalStorage read failed:', err);
            return null;
        }
    }

    /**
     * Xóa bản nháp LocalStorage sau khi đã tạo thiệp thành công
     */
    clearLocalDraft(invitationId, templateSlug = '') {
        try {
            const key = invitationId ? `mayhen_draft_${invitationId}` : `mayhen_draft_new_${templateSlug || 'default'}`;
            localStorage.removeItem(key);
            localStorage.removeItem('mayhen_draft_new');
        } catch (err) {
            // Ignore
        }
    }

    /**
     * Lập lịch lưu nháp lên máy chủ (Debounced)
     */
    schedule() {
        const state = this.store.getState();
        this.changeVersion += 1;

        // 1. Phản xạ lưu ngay vào LocalStorage
        this.saveToLocalStorage(state);

        if (this.hasConflict) return;

        // 2. Cập nhật UI trạng thái
        this.store.setSaveStatus('pending');

        if (this.timer) {
            clearTimeout(this.timer);
        }

        this.timer = setTimeout(() => {
            this.timer = null;
            this.syncToServer();
        }, this.debounceMs);
    }

    /**
     * Gửi request lưu nháp ngầm lên máy chủ
     */
    async syncToServer() {
        const state = this.store.getState();

        // Nếu chưa có thiệp trong DB (đang tạo thiệp mới lần đầu), chỉ lưu LocalStorage
        if (!state.invitation_id) {
            this.store.setSaveStatus('local_only');
            return;
        }

        if (this.isSaving || this.hasConflict) return;
        if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
        }
        this.isSaving = true;
        const sentVersion = this.changeVersion;
        const sentRevision = state.revision_id;
        let shouldFlush = false;
        this.store.setSaveStatus('saving');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                          document.querySelector('input[name="_token"]')?.value || '';

        try {
            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    invitation_id: state.invitation_id,
                    revision_id: sentRevision,
                    draft_config: state.custom_config,
                    wedding_delta: state.wedding,
                }),
            });

            const data = await response.json();

            if (response.status === 409) {
                // Xung đột phiên bản (Được chỉnh sửa từ tab khác)
                this.hasConflict = true;
                if (this.timer) {
                    clearTimeout(this.timer);
                    this.timer = null;
                }
                this.store.setSaveStatus('conflict', {
                    current_revision: data.current_revision,
                    message: data.message || 'Phiên làm việc đã cũ do có chỉnh sửa từ một thiết bị hoặc tab khác.',
                });
                return;
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Lưu nháp máy chủ thất bại.');
            }

            if (!Number.isSafeInteger(sentRevision) || !Number.isSafeInteger(data.new_revision_id) || data.new_revision_id !== sentRevision + 1) {
                this.hasConflict = true;
                if (this.timer) {
                    clearTimeout(this.timer);
                    this.timer = null;
                }
                this.store.setSaveStatus('conflict', {
                    message: 'Máy chủ trả về phiên bản lưu không hợp lệ. Hãy giữ bản nháp và kiểm tra trước khi tiếp tục.',
                });
                return;
            }
            this.store.updateRevision(data.new_revision_id);

            this.saveToLocalStorage(this.store.getState());
            shouldFlush = this.changeVersion !== sentVersion;
            if (shouldFlush) {
                if (this.timer) {
                    clearTimeout(this.timer);
                    this.timer = null;
                }
                this.store.setSaveStatus('pending');
            } else {
                this.store.setSaveStatus('saved', { saved_at: data.saved_at });
            }
        } catch (error) {
            console.error('[Autosaver] Sync error:', error);
            // Vẫn giữ an toàn dữ liệu trên client
            this.store.setSaveStatus('offline', {
                message: 'Không có kết nối mạng. Dữ liệu đang được lưu an toàn trên trình duyệt của bạn.',
            });
        } finally {
            this.isSaving = false;
            if (shouldFlush) {
                this.syncToServer();
            }
        }
    }
}
