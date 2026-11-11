/**
 * Mây Hẹn (mayhen.vn) — Post-Wedding Keepsake & Memorial Mode JavaScript
 * Quy chuẩn: Native ES Modules, Vanilla JavaScript, Zero Frameworks.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Xử lý Toggle Switch Bật/Tắt Chế Độ Kỷ Niệm (Memorial Mode)
    const toggleInput = document.getElementById('mayhen_keepsake_toggle_input');
    const badgeElement = document.getElementById('mayhen_keepsake_status_badge');

    if (toggleInput) {
        toggleInput.addEventListener('change', async (e) => {
            const isEnabled = e.target.checked;
            const invitationId = toggleInput.getAttribute('data-invitation-id');
            const toggleUrl = toggleInput.getAttribute('data-toggle-url');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            toggleInput.disabled = true;

            try {
                const response = await fetch(toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                    },
                    body: JSON.stringify({ is_enabled: isEnabled })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (badgeElement) {
                        badgeElement.className = data.is_enabled
                            ? 'mayhen_keepsake_badge mayhen_keepsake_badge--active'
                            : 'mayhen_keepsake_badge mayhen_keepsake_badge--inactive';
                        badgeElement.textContent = data.is_enabled ? 'Đang Bật' : 'Chưa Bật';
                    }

                    showToast(data.message || 'Cập nhật trạng thái thành công.');
                } else {
                    // Hoàn tác lại trạng thái switch nếu lỗi
                    toggleInput.checked = !isEnabled;
                    showToast(data.message || 'Không thể cập nhật trạng thái.', 'error');
                }
            } catch (err) {
                console.error('Error toggling keepsake mode:', err);
                toggleInput.checked = !isEnabled;
                showToast('Lỗi kết nối máy chủ khi chuyển chế độ.', 'error');
            } finally {
                toggleInput.disabled = false;
            }
        });
    }

    // 2. Modal Lời Cảm Ơn Cá Nhân Hóa Từng Khách Mời
    const modal = document.getElementById('mayhen_keepsake_modal');
    const modalGuestName = document.getElementById('mayhen_modal_guest_name');
    const modalMessageText = document.getElementById('mayhen_modal_message_text');
    const modalCopyBtn = document.getElementById('mayhen_modal_copy_btn');
    const modalCloseBtns = document.querySelectorAll('.mayhen_modal_close');

    const thankYouBtns = document.querySelectorAll('.mayhen_keepsake_btn_thank_you');
    thankYouBtns.forEach((btn) => {
        btn.addEventListener('click', async () => {
            const guestId = btn.getAttribute('data-guest-id');
            const url = btn.getAttribute('data-url');

            btn.disabled = true;
            btn.textContent = 'Đang tải...';

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (modalGuestName) modalGuestName.textContent = data.guest_name;
                    if (modalMessageText) modalMessageText.value = data.message;
                    if (modal) modal.classList.add('is-active');
                } else {
                    showToast('Không thể tải tin nhắn cảm ơn.', 'error');
                }
            } catch (err) {
                console.error('Error fetching thank you message:', err);
                showToast('Lỗi khi tải mẫu tin nhắn.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Lời Cảm Ơn';
            }
        });
    });

    // Đóng Modal
    modalCloseBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (modal) modal.classList.remove('is-active');
        });
    });

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('is-active');
            }
        });
    }

    // Sao chép tin nhắn trong modal
    if (modalCopyBtn && modalMessageText) {
        modalCopyBtn.addEventListener('click', () => {
            modalMessageText.select();
            navigator.clipboard.writeText(modalMessageText.value).then(() => {
                const oldText = modalCopyBtn.textContent;
                modalCopyBtn.textContent = '✓ Đã Sao Chép!';
                setTimeout(() => {
                    modalCopyBtn.textContent = oldText;
                }, 2000);
            }).catch(() => {
                showToast('Không thể sao chép văn bản vào bộ nhớ tạm.', 'error');
            });
        });
    }

    // Helper Toast
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `mayhen_toast mayhen_toast_${type}`;
        toast.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: ${type === 'error' ? '#C0392B' : '#1F1D1A'};
            color: #FFFFFF;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            box-shadow: 0 4px 14px rgba(0,0,0,0.25);
            z-index: 10000;
            transition: opacity 0.3s ease;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});

