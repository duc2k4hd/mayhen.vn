/**
 * MAYHEN.VN — WEDDING GIFTS & BANKING SECURITY JAVASCRIPT MODULE
 * 100% Native Pure Vanilla JavaScript (ES Module)
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Quản lý Modal Chỉnh Sửa Khoản Mừng Cưới
    const editModal = document.getElementById('mayhen-edit-gift-modal');
    const editForm = document.getElementById('mayhen-edit-gift-form');
    const nameInput = document.getElementById('modal-sender-name');
    const groupInput = document.getElementById('modal-sender-group');
    const amountInput = document.getElementById('modal-amount');
    const methodSelect = document.getElementById('modal-payment-method');
    const notesInput = document.getElementById('modal-notes');
    const ackCheckbox = document.getElementById('modal-acknowledged');

    document.querySelectorAll('.mayhen-edit-gift-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const updateUrl = btn.getAttribute('data-update-url');
            const name = btn.getAttribute('data-sender-name') || '';
            const group = btn.getAttribute('data-sender-group') || '';
            const amount = btn.getAttribute('data-amount') || '0';
            const method = btn.getAttribute('data-payment-method') || 'cash';
            const notes = btn.getAttribute('data-notes') || '';
            const ack = btn.getAttribute('data-acknowledged') === '1';

            if (editForm) editForm.action = updateUrl;
            if (nameInput) nameInput.value = name;
            if (groupInput) groupInput.value = group;
            if (amountInput) amountInput.value = amount;
            if (methodSelect) methodSelect.value = method;
            if (notesInput) notesInput.value = notes;
            if (ackCheckbox) ackCheckbox.checked = ack;

            if (editModal) {
                editModal.style.setProperty('display', 'flex', 'important');
                editModal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // 2. Quản lý Modal Cài Đặt Đa Tài Khoản Ngân Hàng & QR Mừng Cưới
    const bankingModal = document.getElementById('mayhen-banking-modal');
    const openBankingBtn = document.getElementById('mayhen-open-banking-btn');

    if (openBankingBtn && bankingModal) {
        openBankingBtn.addEventListener('click', () => {
            bankingModal.style.setProperty('display', 'flex', 'important');
            bankingModal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        });
    }

    // Nút thêm tài khoản nhận mừng mới
    const addAccountBtn = document.getElementById('mayhen-add-bank-row-btn');
    const accountsContainer = document.getElementById('mayhen-bank-accounts-container');

    if (addAccountBtn && accountsContainer) {
        addAccountBtn.addEventListener('click', () => {
            const count = accountsContainer.querySelectorAll('.mayhen_bank_account_row').length;
            const newIndex = count;
            const newRow = document.createElement('div');
            newRow.className = 'mayhen_bank_account_row';
            newRow.style.cssText = 'background: #FFFFFF; border: 1px solid #E8DFD3; border-radius: 12px; padding: 16px; margin-bottom: 14px; position: relative;';
            newRow.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 13px; font-weight: 600; color: #9C7A4A;">Tài Khoản #${newIndex + 1}</span>
                    <button type="button" class="mayhen-remove-bank-row-btn" style="background: none; border: none; color: #C62828; font-size: 12px; cursor: pointer; padding: 4px 8px;">✕ Xóa bỏ</button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label class="mayhen_account_form_label" style="font-size: 12px;">Nhãn tài khoản *</label>
                        <input type="text" name="accounts[${newIndex}][label]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="Ví dụ: Nhà Trai (Chú Rể)" value="Tài Khoản Phụ">
                    </div>
                    <div>
                        <label class="mayhen_account_form_label" style="font-size: 12px;">Tên ngân hàng *</label>
                        <input type="text" name="accounts[${newIndex}][bank_name]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="Ví dụ: MB Bank, Vietcombank...">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label class="mayhen_account_form_label" style="font-size: 12px;">Số tài khoản *</label>
                        <input type="text" name="accounts[${newIndex}][bank_account_number]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="Nhập số tài khoản...">
                    </div>
                    <div>
                        <label class="mayhen_account_form_label" style="font-size: 12px;">Tên chủ tài khoản (In hoa) *</label>
                        <input type="text" name="accounts[${newIndex}][account_holder]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px; text-transform: uppercase;" placeholder="NGUYEN VAN A">
                    </div>
                </div>
                <div>
                    <label class="mayhen_account_form_label" style="font-size: 12px;">Ảnh mã QR tùy chọn (Để trống sẽ tự sinh mã VietQR chuẩn)</label>
                    <input type="url" name="accounts[${newIndex}][qr_image_url]" class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="https://...">
                </div>
            `;
            accountsContainer.appendChild(newRow);

            // Gắn event xóa cho nút mới
            const removeBtn = newRow.querySelector('.mayhen-remove-bank-row-btn');
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    newRow.remove();
                });
            }
        });
    }

    // Gắn event cho các nút xóa tài khoản hiện có
    document.querySelectorAll('.mayhen-remove-bank-row-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const row = e.target.closest('.mayhen_bank_account_row');
            const totalRows = document.querySelectorAll('.mayhen_bank_account_row').length;
            if (totalRows <= 1) {
                alert('Bạn cần giữ lại ít nhất một tài khoản nhận mừng cưới.');
                return;
            }
            if (row) row.remove();
        });
    });

    // 3. Phím Escape đóng tất cả các modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            window.closeMayhenModals();
        }
    });
});

window.closeMayhenModals = function () {
    const editModal = document.getElementById('mayhen-edit-gift-modal');
    const bankingModal = document.getElementById('mayhen-banking-modal');

    if (editModal) {
        editModal.style.setProperty('display', 'none', 'important');
        editModal.classList.remove('is-open');
    }
    if (bankingModal) {
        bankingModal.style.setProperty('display', 'none', 'important');
        bankingModal.classList.remove('is-open');
    }
    document.body.style.overflow = '';
};

window.closeEditGiftModal = window.closeMayhenModals;
window.closeBankingModal = window.closeMayhenModals;

