/**
 * MAYHEN.VN — INVITATION EDITOR V2
 * Action Types & Action Creators
 * Strict: Pure Vanilla JavaScript ES Modules
 */

export const ActionTypes = {
    // Khởi tạo & phục hồi
    INIT_STORE: 'INIT_STORE',
    RESTORE_DRAFT: 'RESTORE_DRAFT',

    // Cập nhật thông tin cưới & trường dữ liệu
    UPDATE_FIELD: 'UPDATE_FIELD',
    UPDATE_WEDDING: 'UPDATE_WEDDING',
    UPDATE_EXTRA_DATA: 'UPDATE_EXTRA_DATA',

    // Quản lý Sections
    REORDER_SECTIONS: 'REORDER_SECTIONS',
    TOGGLE_SECTION_VISIBILITY: 'TOGGLE_SECTION_VISIBILITY',
    SELECT_SECTION: 'SELECT_SECTION',

    // Mẫu & Giao diện (Theme / Con Dấu)
    SWITCH_TEMPLATE: 'SWITCH_TEMPLATE',
    SET_THEME_PALETTE: 'SET_THEME_PALETTE',
    UPDATE_CUSTOM_CONFIG: 'UPDATE_CUSTOM_CONFIG',

    // Điều hướng & UI Studio
    SET_ACTIVE_TAB: 'SET_ACTIVE_TAB',
    SET_PREVIEW_DEVICE: 'SET_PREVIEW_DEVICE',
    SET_SAVE_STATUS: 'SET_SAVE_STATUS',
    UPDATE_REVISION: 'UPDATE_REVISION',

    // Lịch sử
    UNDO: 'UNDO',
    REDO: 'REDO',
};

export const createAction = (type, payload = {}) => ({
    type,
    payload,
    timestamp: Date.now(),
});
