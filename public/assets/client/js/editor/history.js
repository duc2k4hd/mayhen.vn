/**
 * MAYHEN.VN — INVITATION EDITOR V2
 * History Stack (Undo / Redo Engine)
 * Dung lượng tối đa: 20 snapshots
 * Strict: Pure Vanilla JavaScript, Zero external dependencies
 */

const MAX_HISTORY = 20;

export class HistoryManager {
    constructor() {
        this.past = [];
        this.future = [];
    }

    /**
     * Tạo bản sao sâu (deep clone) an toàn không dính prototype
     */
    static clone(data) {
        if (typeof structuredClone === 'function') {
            try {
                return structuredClone(data);
            } catch (e) {
                // Fallback nếu có đối tượng không thể serialize
            }
        }
        return JSON.parse(JSON.stringify(data));
    }

    /**
     * Ghi nhận 1 snapshot trước khi có thay đổi dữ liệu
     * @param {Object} snapshot - { wedding, custom_config }
     */
    push(snapshot) {
        if (!snapshot) return;

        this.past.push(HistoryManager.clone(snapshot));
        if (this.past.length > MAX_HISTORY) {
            this.past.shift();
        }

        // Khi có thay đổi mới, toàn bộ lịch sử Redo bị xóa
        this.future = [];
    }

    /**
     * Hoàn tác (Undo)
     * @param {Object} currentSnapshot - Dữ liệu hiện tại để đưa vào future
     * @returns {Object|null} Snapshot được phục hồi
     */
    undo(currentSnapshot) {
        if (!this.canUndo()) return null;

        const previous = this.past.pop();
        this.future.push(HistoryManager.clone(currentSnapshot));
        return HistoryManager.clone(previous);
    }

    /**
     * Làm lại (Redo)
     * @param {Object} currentSnapshot - Dữ liệu hiện tại để đưa vào past
     * @returns {Object|null} Snapshot tương lai
     */
    redo(currentSnapshot) {
        if (!this.canRedo()) return null;

        const next = this.future.pop();
        this.past.push(HistoryManager.clone(currentSnapshot));
        return HistoryManager.clone(next);
    }

    canUndo() {
        return this.past.length > 0;
    }

    canRedo() {
        return this.future.length > 0;
    }

    clear() {
        this.past = [];
        this.future = [];
    }
}
