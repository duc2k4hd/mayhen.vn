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
        return `mayhen_draft_${state.invitation_id || 'new'}`;
    }

    /**
     * Lưu tức thì vào LocalStorage (chống mất điện / tắt tab đột ngột)
     */
    saveToLocalStorage(state) {
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

    /**
     * Đọc bản nháp từ LocalStorage nếu có
     */
    getLocalDraft(invitationId) {
        try {
            const key = `mayhen_draft_${invitationId || 'new'}`;
            const raw = localStorage.getItem(key);
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
    clearLocalDraft(invitationId) {
        try {
            const key = `mayhen_draft_${invitationId || 'new'}`;
            localStorage.removeItem(key);
        } catch (err) {
            // Ignore
        }
    }

    /**
     * Lập lịch lưu nháp lên máy chủ (Debounced)
     */
    schedule() {
        const state = this.store.getState();

        // 1. Phản xạ lưu ngay vào LocalStorage
        this.saveToLocalStorage(state);

        // 2. Cập nhật UI trạng thái
        this.store.setSaveStatus('pending');

        if (this.timer) {
            clearTimeout(this.timer);
        }

        this.timer = setTimeout(() => {
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

        if (this.isSaving) return;
        this.isSaving = true;
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
                    revision_id: state.revision_id,
                    draft_config: state.custom_config,
                    wedding_delta: state.wedding,
                }),
            });

            const data = await response.json();

            if (response.status === 409) {
                // Xung đột phiên bản (Được chỉnh sửa từ tab khác)
                this.store.setSaveStatus('conflict', {
                    current_revision: data.current_revision,
                    message: data.message || 'Phiên làm việc đã cũ do có chỉnh sửa từ một thiết bị hoặc tab khác.',
                });
                return;
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Lưu nháp máy chủ thất bại.');
            }

            // Cập nhật revision_id mới
            if (data.new_revision_id) {
                this.store.updateRevision(data.new_revision_id);
            }

            this.store.setSaveStatus('saved', { saved_at: data.saved_at });
        } catch (error) {
            console.error('[Autosaver] Sync error:', error);
            // Vẫn giữ an toàn dữ liệu trên client
            this.store.setSaveStatus('offline', {
                message: 'Không có kết nối mạng. Dữ liệu đang được lưu an toàn trên trình duyệt của bạn.',
            });
        } finally {
            this.isSaving = false;
        }
    }
}
