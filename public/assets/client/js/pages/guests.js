/**
 * MAYHEN.VN — GUEST MANAGEMENT & ZALO SHARING CONTROLLER
 * Vanilla ES Module (Zero External Library)
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Chuyển đổi Tabs thêm khách (Single vs Batch)
    const tabButtons = document.querySelectorAll('.mayhen_guest_add_tab_btn');
    const singlePane = document.getElementById('tab-single');
    const batchPane = document.getElementById('tab-batch');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-tab-target');

            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            if (target === 'single') {
                if (singlePane) singlePane.style.display = 'block';
                if (batchPane) batchPane.style.display = 'none';
            } else if (target === 'batch') {
                if (singlePane) singlePane.style.display = 'none';
                if (batchPane) batchPane.style.display = 'block';
            }
        });
    });

    // 2. Sao chép lời mời Zalo kèm link cá nhân hóa
    const copyZaloBtns = document.querySelectorAll('[data-mayhen-action="copy-zalo"]');
    copyZaloBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const guestName = btn.getAttribute('data-guest-name') || '';
            const salutation = btn.getAttribute('data-guest-salutation') || 'Kính mời';
            const guestLink = btn.getAttribute('data-guest-link') || '';
            const coupleNames = btn.getAttribute('data-couple-names') || 'Cô Dâu & Chú Rể';
            const weddingDate = btn.getAttribute('data-wedding-date') || '';

            const dateStr = weddingDate ? ` vào ngày ${weddingDate}` : '';
            const message = `${salutation} ${guestName},\n\nTrân trọng kính mời quý khách tới tham dự lễ thành hôn của ${coupleNames}${dateStr}.\n\nXem chi tiết thiệp cưới và gửi lời chúc phúc tại:\n${guestLink}\n\nRất hân hạnh được đón tiếp!`;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(message).then(() => {
                    const originalHtml = btn.innerHTML;
                    btn.innerText = 'Đã chép lời mời!';
                    btn.style.backgroundColor = '#1F1D1A';
                    btn.style.color = '#FAF8F5';
                    btn.style.borderColor = '#1F1D1A';

                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        btn.style.backgroundColor = '';
                        btn.style.color = '';
                        btn.style.borderColor = '';
                    }, 2000);
                });
            }
        });
    });

    // 3. Sao chép trực tiếp đường link thiệp của khách (không dùng alert)
    const copyLinkBtns = document.querySelectorAll('[data-mayhen-action="copy-link"]');
    copyLinkBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const link = btn.getAttribute('data-guest-link');
            if (navigator.clipboard && link) {
                navigator.clipboard.writeText(link).then(() => {
                    const originalText = btn.innerText;
                    btn.innerText = 'Đã chép link!';
                    btn.style.backgroundColor = '#FAF8F5';
                    btn.style.borderColor = '#C5A880';
                    btn.style.color = '#9C7A4A';

                    setTimeout(() => {
                        btn.innerText = originalText;
                        btn.style.backgroundColor = '';
                        btn.style.borderColor = '';
                        btn.style.color = '';
                    }, 2000);
                });
            }
        });
    });

    // 4. Modal Chỉnh Sửa Thông Tin Khách Mời
    const editModal = document.getElementById('mayhen-edit-guest-modal');
    if (editModal) {
        const editForm = document.getElementById('mayhen-edit-guest-form');
        const nameInput = document.getElementById('edit-guest-name');
        const salutationInput = document.getElementById('edit-guest-salutation');
        const groupInput = document.getElementById('edit-guest-group');
        const phoneInput = document.getElementById('edit-guest-phone');
        const attendeesInput = document.getElementById('edit-guest-attendees');

        document.querySelectorAll('.mayhen_guest_open_edit_btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const updateUrl = btn.getAttribute('data-update-url');
                const name = btn.getAttribute('data-guest-name') || '';
                const salutation = btn.getAttribute('data-guest-salutation') || '';
                const group = btn.getAttribute('data-guest-group') || '';
                const phone = btn.getAttribute('data-guest-phone') || '';
                const attendees = btn.getAttribute('data-guest-attendees') || '1';

                if (editForm) editForm.action = updateUrl;
                if (nameInput) nameInput.value = name;
                if (salutationInput) salutationInput.value = salutation;
                if (groupInput) groupInput.value = group;
                if (phoneInput) phoneInput.value = phone;
                if (attendeesInput) attendeesInput.value = attendees;

                openModal(editModal);
            });
        });

        setupModalDismiss(editModal);
    }

    // 5. Modal Cập Nhật Trạng Thái RSVP Thủ Công Hộ Khách
    const rsvpModal = document.getElementById('mayhen-rsvp-guest-modal');
    if (rsvpModal) {
        const rsvpForm = document.getElementById('mayhen-rsvp-guest-form');
        const subtitleEl = document.getElementById('mayhen-rsvp-guest-subtitle');
        const statusSelect = document.getElementById('rsvp-status-select');
        const adultsInput = document.getElementById('rsvp-adults-count');
        const childrenInput = document.getElementById('rsvp-children-count');
        const dietaryInput = document.getElementById('rsvp-dietary');
        const notesInput = document.getElementById('rsvp-notes');
        const headcountBox = document.getElementById('rsvp-headcount-fields');

        document.querySelectorAll('.mayhen_guest_open_rsvp_btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const rsvpUrl = btn.getAttribute('data-rsvp-url');
                const name = btn.getAttribute('data-guest-name') || '';
                const status = btn.getAttribute('data-rsvp-status') || 'pending';
                const adults = btn.getAttribute('data-rsvp-adults') || '1';
                const children = btn.getAttribute('data-rsvp-children') || '0';
                const dietary = btn.getAttribute('data-rsvp-dietary') || '';
                const notes = btn.getAttribute('data-rsvp-notes') || '';

                if (rsvpForm) rsvpForm.action = rsvpUrl;
                if (subtitleEl) subtitleEl.textContent = `Khách mời: ${name}`;
                if (statusSelect) statusSelect.value = status;
                if (adultsInput) adultsInput.value = adults;
                if (childrenInput) childrenInput.value = children;
                if (dietaryInput) dietaryInput.value = dietary;
                if (notesInput) notesInput.value = notes;

                toggleHeadcountDisplay(status);
                openModal(rsvpModal);
            });
        });

        if (statusSelect) {
            statusSelect.addEventListener('change', () => {
                toggleHeadcountDisplay(statusSelect.value);
            });
        }

        function toggleHeadcountDisplay(status) {
            if (headcountBox) {
                headcountBox.style.display = status === 'attending' ? 'grid' : 'none';
            }
        }

        setupModalDismiss(rsvpModal);
    }

    function openModal(modalEl) {
        modalEl.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalEl) {
        modalEl.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function setupModalDismiss(modalEl) {
        const backdrop = modalEl.querySelector('.mayhen_guest_modal_backdrop');
        const closeBtns = modalEl.querySelectorAll('.mayhen_guest_modal_close, .mayhen_guest_modal_cancel');

        if (backdrop) backdrop.addEventListener('click', () => closeModal(modalEl));
        closeBtns.forEach(b => b.addEventListener('click', () => closeModal(modalEl)));

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalEl.classList.contains('is-open')) {
                closeModal(modalEl);
            }
        });
    }
});
