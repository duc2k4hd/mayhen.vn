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

    // 2. Thẻ Mời Zalo & Messenger Khắc Tên (TASK-V1-05: Social Share Card Modal)
    const shareModal = document.getElementById('mayhen-share-zalo-modal');
    const shareCardName = document.getElementById('share-card-guest-name');
    const shareCardCouple = document.getElementById('share-card-couple');
    const shareCardGroup = document.getElementById('share-card-group');
    const shareCardDate = document.getElementById('share-card-date');
    const shareMessageText = document.getElementById('share-message-text');
    const shareCopyToast = document.getElementById('share-copy-toast');
    const shareStyleBtns = document.querySelectorAll('.mayhen_guest_share_style_btn');
    const shareCopyMsgBtn = document.getElementById('share-copy-message-btn');
    const shareCopyLinkOnlyBtn = document.getElementById('share-copy-link-only-btn');
    const shareOpenZaloBtn = document.getElementById('share-open-zalo-btn');

    let currentShareData = {
        guestName: '',
        salutation: 'Kính mời',
        groupName: '',
        guestLink: '',
        coupleNames: '',
        weddingDate: '',
        currentStyle: 'formal'
    };

    function generateShareMessage(style, data) {
        const { guestName, salutation, coupleNames, weddingDate, guestLink } = data;
        const dateStr = weddingDate ? ` vào ngày ${weddingDate}` : '';

        if (style === 'intimate') {
            return `Thân gửi ${guestName} ơi!\n\nNgày vui của ${coupleNames} sắp đến rồi${dateStr}. Tụi mình gửi tấm thiệp cưới này cùng tất cả sự trân quý, rất mong bạn sẽ bớt chút thời gian tới chung vui và quẩy hết mình cùng tụi mình nhé!\n\nXem thiệp và xác nhận tham dự tại đây nha:\n${guestLink}\n\nHẹn gặp bạn ngày vui nha!`;
        } else if (style === 'warm') {
            return `Mến gửi ${salutation} ${guestName},\n\n${coupleNames} trân trọng gửi thiệp mời tham dự tiệc cưới của chúng mình${dateStr}. Thật hạnh phúc nếu được đón tiếp bạn cùng người thương trong ngày trọng đại này.\n\nKính mời bạn xem thông tin chi tiết và gửi lời chúc phúc tại:\n${guestLink}\n\nTrân trọng & Cảm ơn bạn rất nhiều!`;
        }

        // Mặc định: Trang trọng & Kính cẩn (formal)
        return `Kính gửi ${salutation} ${guestName},\n\nGia đình chúng tôi xin trân trọng kính mời quý khách tới tham dự lễ thành hôn của ${coupleNames}${dateStr}.\n\nKính mời xem chi tiết thiệp cưới và xác nhận tham dự tại:\n${guestLink}\n\nSự hiện diện của quý khách là niềm vinh hạnh lớn nhất của gia đình chúng tôi!`;
    }

    function openShareModal(btn) {
        if (!shareModal) return;

        currentShareData.guestName = btn.getAttribute('data-guest-name') || 'Quý Khách';
        currentShareData.salutation = btn.getAttribute('data-guest-salutation') || 'Kính mời';
        currentShareData.groupName = btn.getAttribute('data-guest-group') || 'Chung';
        currentShareData.guestLink = btn.getAttribute('data-guest-link') || window.location.href;
        currentShareData.coupleNames = btn.getAttribute('data-couple-names') || 'Cô Dâu & Chú Rể';
        currentShareData.weddingDate = btn.getAttribute('data-wedding-date') || '';

        // Tự động suy luận phong cách phù hợp theo nhóm khách
        const grpLower = currentShareData.groupName.toLowerCase();
        if (grpLower.includes('bạn') || grpLower.includes('thân')) {
            currentShareData.currentStyle = 'intimate';
        } else if (grpLower.includes('gia đình') || grpLower.includes('họ hàng') || grpLower.includes('vip') || grpLower.includes('người lớn')) {
            currentShareData.currentStyle = 'formal';
        } else {
            currentShareData.currentStyle = 'warm';
        }

        // Cập nhật thẻ xem trước bưu thiếp mini
        if (shareCardName) shareCardName.innerText = `${currentShareData.salutation} ${currentShareData.guestName}`;
        if (shareCardCouple && currentShareData.coupleNames) shareCardCouple.innerText = currentShareData.coupleNames;
        if (shareCardGroup) shareCardGroup.innerText = `Nhóm: ${currentShareData.groupName}`;
        if (shareCardDate && currentShareData.weddingDate) shareCardDate.innerText = currentShareData.weddingDate;

        // Cập nhật trạng thái active của nút chọn phong cách
        shareStyleBtns.forEach(b => {
            const isMatch = b.getAttribute('data-style') === currentShareData.currentStyle;
            b.classList.toggle('is-active', isMatch);
            b.style.borderColor = isMatch ? '#C5A880' : '#E5DCCD';
            b.style.background = isMatch ? '#FAF8F5' : '#FFFFFF';
            b.style.fontWeight = isMatch ? '600' : 'normal';
        });

        // Tạo nội dung tin nhắn và đưa vào textarea
        if (shareMessageText) {
            shareMessageText.value = generateShareMessage(currentShareData.currentStyle, currentShareData);
        }

        if (shareCopyToast) shareCopyToast.style.display = 'none';

        shareModal.classList.add('is-open');
        shareModal.setAttribute('aria-hidden', 'false');
    }

    // Gán sự kiện mở modal cho các nút Chép Mời Zalo
    const copyZaloBtns = document.querySelectorAll('[data-mayhen-action="copy-zalo"]');
    copyZaloBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openShareModal(btn);
        });
    });

    // Chuyển đổi phong cách lời mời trong modal
    shareStyleBtns.forEach(b => {
        b.addEventListener('click', () => {
            const style = b.getAttribute('data-style');
            currentShareData.currentStyle = style;

            shareStyleBtns.forEach(btn => {
                const isMatch = btn === b;
                btn.classList.toggle('is-active', isMatch);
                btn.style.borderColor = isMatch ? '#C5A880' : '#E5DCCD';
                btn.style.background = isMatch ? '#FAF8F5' : '#FFFFFF';
                btn.style.fontWeight = isMatch ? '600' : 'normal';
            });

            if (shareMessageText) {
                shareMessageText.value = generateShareMessage(style, currentShareData);
            }
        });
    });

    function showToast(msg) {
        if (!shareCopyToast) return;
        shareCopyToast.innerText = msg;
        shareCopyToast.style.display = 'inline';
        setTimeout(() => {
            shareCopyToast.style.display = 'none';
        }, 3000);
    }

    // Sao chép toàn bộ lời mời kèm link
    if (shareCopyMsgBtn) {
        shareCopyMsgBtn.addEventListener('click', () => {
            const text = shareMessageText ? shareMessageText.value : '';
            if (navigator.clipboard && text) {
                navigator.clipboard.writeText(text).then(() => {
                    showToast('✓ Đã sao chép lời mời vào bộ nhớ tạm!');
                });
            }
        });
    }

    // Sao chép riêng đường link thiệp
    if (shareCopyLinkOnlyBtn) {
        shareCopyLinkOnlyBtn.addEventListener('click', () => {
            const link = currentShareData.guestLink;
            if (navigator.clipboard && link) {
                navigator.clipboard.writeText(link).then(() => {
                    showToast('✓ Đã sao chép đường link!');
                });
            }
        });
    }

    // Mở Zalo: tự động sao chép trước khi mở app / web Zalo
    if (shareOpenZaloBtn) {
        shareOpenZaloBtn.addEventListener('click', () => {
            const text = shareMessageText ? shareMessageText.value : '';
            if (navigator.clipboard && text) {
                navigator.clipboard.writeText(text);
            }
        });
    }

    // Đóng share modal
    if (shareModal) {
        const closeBtns = shareModal.querySelectorAll('.mayhen_guest_modal_close, .mayhen_guest_modal_cancel, .mayhen_guest_modal_backdrop');
        closeBtns.forEach(cb => {
            cb.addEventListener('click', () => {
                shareModal.classList.remove('is-open');
                shareModal.setAttribute('aria-hidden', 'true');
            });
        });
    }

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
        const tableInput = document.getElementById('edit-guest-table');
        const phoneInput = document.getElementById('edit-guest-phone');
        const attendeesInput = document.getElementById('edit-guest-attendees');

        document.querySelectorAll('.mayhen_guest_open_edit_btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const updateUrl = btn.getAttribute('data-update-url');
                const name = btn.getAttribute('data-guest-name') || '';
                const salutation = btn.getAttribute('data-guest-salutation') || '';
                const group = btn.getAttribute('data-guest-group') || '';
                const table = btn.getAttribute('data-guest-table') || '';
                const phone = btn.getAttribute('data-guest-phone') || '';
                const attendees = btn.getAttribute('data-guest-attendees') || '1';

                if (editForm) editForm.action = updateUrl;
                if (nameInput) nameInput.value = name;
                if (salutationInput) salutationInput.value = salutation;
                if (groupInput) groupInput.value = group;
                if (tableInput) tableInput.value = table;
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
