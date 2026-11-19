/**
 * Mây Hẹn Admin — AI Chatbot Knowledge Base Interactions
 * Pure Vanilla ES Module
 */

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const msg = form.getAttribute('data-confirm-delete') || 'Bạn chắc chắn muốn xóa mục này?';
            if (!window.confirm(msg)) {
                event.preventDefault();
            }
        });
    });
});
