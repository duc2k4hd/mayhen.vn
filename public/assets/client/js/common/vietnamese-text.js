/**
 * MAYHEN.VN — VIETNAMESE TEXT & HTML ESCAPING UTILITIES
 * Pure, zero-DOM ES Module reusable across Editor, FAQ, and Node unit tests.
 */

const HTML_ESCAPE_MAP = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
};

/**
 * Bỏ toàn bộ dấu tiếng Việt (kể cả đ/Đ), giữ nguyên chữ hoa/thường và khoảng trắng.
 */
export const stripVietnameseDiacritics = (str) => {
    if (!str || typeof str !== 'string') {
        return '';
    }

    return str
        .replace(/đ/g, 'd')
        .replace(/Đ/g, 'D')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
};

/**
 * Chuẩn hóa họ tên tiếng Việt (dùng cho chủ tài khoản VietQR):
 * chuyển thành chữ HOA KHÔNG DẤU, loại bỏ ký tự đặc biệt và chuẩn hóa khoảng trắng.
 */
export const normalizeVietnameseName = (str) => {
    const stripped = stripVietnameseDiacritics(str);
    if (!stripped) {
        return '';
    }

    return stripped
        .replace(/[^a-zA-Z0-9\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .toUpperCase();
};

/**
 * Chuẩn hóa chuỗi tiếng Việt để tìm kiếm tức thì không phân biệt dấu / hoa thường.
 */
export const normalizeVietnameseSearch = (str) => {
    return stripVietnameseDiacritics(str).toLowerCase().trim();
};

/**
 * Escape ký tự HTML đặc biệt để chống XSS khi bắt buộc phải tạo chuỗi HTML.
 */
export const escapeHtml = (value) => {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value).replace(/[&<>"']/g, (ch) => HTML_ESCAPE_MAP[ch]);
};
