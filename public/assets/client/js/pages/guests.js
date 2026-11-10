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
        const maxAttendeesInput = document.getElementById('edit-guest-max-attendees');

        document.querySelectorAll('.mayhen_guest_open_edit_btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const updateUrl = btn.getAttribute('data-update-url');
                const name = btn.getAttribute('data-guest-name') || '';
                const salutation = btn.getAttribute('data-guest-salutation') || '';
                const group = btn.getAttribute('data-guest-group') || '';
                const table = btn.getAttribute('data-guest-table') || '';
                const phone = btn.getAttribute('data-guest-phone') || '';
                const attendees = btn.getAttribute('data-guest-attendees') || '1';
                const maxAttendees = btn.getAttribute('data-guest-max-attendees') || '';

                if (editForm) editForm.action = updateUrl;
                if (nameInput) nameInput.value = name;
                if (salutationInput) salutationInput.value = salutation;
                if (groupInput) groupInput.value = group;
                if (tableInput) tableInput.value = table;
                if (phoneInput) phoneInput.value = phone;
                if (attendeesInput) attendeesInput.value = attendees;
                if (maxAttendeesInput) maxAttendeesInput.value = maxAttendees;

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

    // 6. Bàn Soát Lỗi Trước Khi Gửi & Trợ Lý Xưng Hô (Pre-Send Validation Desk - TASK-VAL-10)
    const presendBtn = document.getElementById('btn-open-presend-modal');
    const presendModal = document.getElementById('mayhen_modal_presend_validation');
    if (presendBtn && presendModal) {
        const loadingEl = document.getElementById('presend-loading');
        const contentEl = document.getElementById('presend-content');
        const scoreValEl = document.getElementById('presend-score-val');
        const scoreBadgeEl = document.getElementById('presend-score-badge');
        const readyCountEl = document.getElementById('presend-ready-count');
        const flaggedCountEl = document.getElementById('presend-flagged-count');
        const issuesListEl = document.getElementById('presend-issues-list');
        const etiquetteTableEl = document.getElementById('presend-etiquette-table');
        const doneBtn = document.getElementById('presend-btn-done');

        presendBtn.addEventListener('click', async () => {
            openModal(presendModal);
            if (loadingEl) loadingEl.style.display = 'block';
            if (contentEl) contentEl.style.display = 'none';

            try {
                const endpoint = window.location.pathname.replace(/\/+$/, '') + '/kiem-tra-truoc-gui';
                const res = await fetch(endpoint, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const json = await res.json();
                if (!json.success || !json.data) throw new Error('Không thể tải dữ liệu kiểm tra.');

                const data = json.data;

                // Render điểm số và thống kê
                if (scoreValEl) scoreValEl.textContent = `${data.readiness_score}%`;
                if (scoreBadgeEl) {
                    if (data.readiness_score >= 90) {
                        scoreBadgeEl.textContent = 'Hoàn hảo để gửi';
                        scoreBadgeEl.style.background = '#E8F5E9';
                        scoreBadgeEl.style.color = '#2E7D32';
                    } else if (data.readiness_score >= 70) {
                        scoreBadgeEl.textContent = 'Khá tốt';
                        scoreBadgeEl.style.background = '#FFF8E1';
                        scoreBadgeEl.style.color = '#F57F17';
                    } else {
                        scoreBadgeEl.textContent = 'Cần lưu ý';
                        scoreBadgeEl.style.background = '#FFEBEE';
                        scoreBadgeEl.style.color = '#C62828';
                    }
                }
                if (readyCountEl) readyCountEl.textContent = data.ready_guests_count;
                if (flaggedCountEl) flaggedCountEl.textContent = data.flagged_guests_count;

                // Render danh sách lỗi
                if (issuesListEl) {
                    issuesListEl.innerHTML = '';
                    const issueKeys = Object.keys(data.issues || {});
                    if (issueKeys.length === 0) {
                        issuesListEl.innerHTML = `
                            <div style="background: #E8F5E9; border: 1px solid #C8E6C9; border-radius: 10px; padding: 14px; color: #2E7D32; font-size: 13px;">
                                ✨ <strong>Tuyệt vời!</strong> Danh sách khách mời của bạn hoàn toàn chuẩn xác, đầy đủ danh xưng lễ nghi và sẵn sàng gửi thiệp ngay lúc này.
                            </div>
                        `;
                    } else {
                        issueKeys.forEach(key => {
                            const iss = data.issues[key];
                            const bg = iss.severity === 'critical' ? '#FFEBEE' : (iss.severity === 'warning' ? '#FFF8E1' : '#F5F5F5');
                            const border = iss.severity === 'critical' ? '#FFCDD2' : (iss.severity === 'warning' ? '#FFE082' : '#E0E0E0');
                            const color = iss.severity === 'critical' ? '#C62828' : (iss.severity === 'warning' ? '#E65100' : '#424242');

                            let guestListHtml = '';
                            if (iss.guests && iss.guests.length > 0) {
                                const previewGuests = iss.guests.slice(0, 3).map(g => `<code>${g.name}</code>`).join(', ');
                                const moreCount = iss.guests.length - 3;
                                guestListHtml = `<div style="margin-top: 6px; font-size: 12px; color: #555;">Khách liên quan: ${previewGuests} ${moreCount > 0 ? `và ${moreCount} khách khác...` : ''}</div>`;
                            }

                            const item = document.createElement('div');
                            item.style.cssText = `background: ${bg}; border: 1px solid ${border}; border-radius: 10px; padding: 12px 14px;`;
                            item.innerHTML = `
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <strong style="color: ${color}; font-size: 13px;">${iss.title}</strong>
                                    <span style="font-size: 12px; font-weight: 700; color: ${color};">${iss.count} trường hợp</span>
                                </div>
                                <p style="font-size: 12.5px; color: #4A453E; margin: 4px 0 0 0; line-height: 1.45;">${iss.description}</p>
                                ${guestListHtml}
                            `;
                            issuesListEl.appendChild(item);
                        });
                    }
                }

                // Render gợi ý danh xưng (Family Etiquette Assistant)
                if (etiquetteTableEl && data.etiquette_suggestions) {
                    etiquetteTableEl.innerHTML = '';
                    Object.entries(data.etiquette_suggestions).forEach(([grp, sugg]) => {
                        const cell = document.createElement('div');
                        cell.style.cssText = 'background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 8px; padding: 8px 12px;';
                        cell.innerHTML = `
                            <strong style="color: #1F1D1A; display: block; font-size: 12px;">Nhóm: ${grp}</strong>
                            <span style="color: #9C7A4A; font-weight: 500;">Gợi ý: ${sugg}</span>
                        `;
                        etiquetteTableEl.appendChild(cell);
                    });
                }

                if (loadingEl) loadingEl.style.display = 'none';
                if (contentEl) contentEl.style.display = 'flex';
            } catch (err) {
                if (loadingEl) loadingEl.innerHTML = `<span style="color: #C62828;">⚠️ ${err.message || 'Đã có lỗi xảy ra.'}</span>`;
            }
        });

        if (doneBtn) doneBtn.addEventListener('click', () => closeModal(presendModal));
        setupModalDismiss(presendModal);
    }

    // 7. Trợ Lý Lên Lịch Nhắc Nhở Tế Nhị (Gentle Reminder Planner - TASK-VAL-16)
    const reminderBtn = document.getElementById('btn-open-reminder-modal');
    const reminderModal = document.getElementById('mayhen_modal_reminder_planner');
    if (reminderBtn && reminderModal) {
        const loadingEl = document.getElementById('reminder-loading');
        const contentEl = document.getElementById('reminder-content');
        const unresponsiveCountEl = document.getElementById('reminder-unresponsive-count');
        const unsentCountEl = document.getElementById('reminder-unsent-count');
        const unseatedCountEl = document.getElementById('reminder-unseated-count');
        const timingWindowsEl = document.getElementById('reminder-timing-windows');
        const timingTipEl = document.getElementById('reminder-timing-tip');
        const listContainerEl = document.getElementById('reminder-list-container');
        const doneBtn = document.getElementById('reminder-btn-done');

        reminderBtn.addEventListener('click', async () => {
            openModal(reminderModal);
            if (loadingEl) loadingEl.style.display = 'block';
            if (contentEl) contentEl.style.display = 'none';

            try {
                const endpoint = window.location.pathname.replace(/\/+$/, '') + '/ke-hoach-nhac-nho';
                const res = await fetch(endpoint, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const json = await res.json();
                if (!json.success || !json.data) throw new Error('Không thể tải kế hoạch nhắc nhở.');

                const data = json.data;

                // 1. Thống kê số lượng
                if (unresponsiveCountEl) unresponsiveCountEl.textContent = data.stats.unresponsive_count || 0;
                if (unsentCountEl) unsentCountEl.textContent = data.stats.unsent_count || 0;
                if (unseatedCountEl) unseatedCountEl.textContent = data.stats.attending_unseated_count || 0;

                // 2. Khung giờ vàng
                if (timingWindowsEl && data.recommended_time && data.recommended_time.time_windows) {
                    timingWindowsEl.innerHTML = data.recommended_time.time_windows.map(tw => `<div>• ${tw}</div>`).join('');
                }
                if (timingTipEl && data.recommended_time && data.recommended_time.tip) {
                    timingTipEl.textContent = `💡 Lưu ý: ${data.recommended_time.tip}`;
                }

                // 3. Render danh sách khách
                if (listContainerEl) {
                    listContainerEl.innerHTML = '';
                    const reminders = data.reminders || [];

                    if (reminders.length === 0) {
                        listContainerEl.innerHTML = `
                            <div style="text-align: center; padding: 24px; color: #2E7D32; background: #E8F5E9; border-radius: 8px;">
                                <strong>🎉 Tuyệt vời!</strong> Tất cả khách mời đều đã được gửi thiệp, phản hồi đầy đủ và xếp bàn chu đáo.
                            </div>
                        `;
                    } else {
                        reminders.forEach(r => {
                            const card = document.createElement('div');
                            card.style.cssText = 'background: #FAF8F5; border: 1px solid #E5DCCD; border-radius: 10px; padding: 12px 16px;';

                            let badgeHtml = '';
                            if (r.status_type === 'unresponsive') {
                                badgeHtml = '<span style="background: #FFF3E0; color: #E65100; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Chưa phản hồi RSVP</span>';
                            } else if (r.status_type === 'unsent') {
                                badgeHtml = '<span style="background: #E8F5E9; color: #2E7D32; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Chưa gửi thiệp</span>';
                            } else {
                                badgeHtml = '<span style="background: #F3E5F5; color: #6A1B9A; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Chưa xếp bàn</span>';
                            }

                            const zaloBtnHtml = r.zalo_link
                                ? `<a href="${r.zalo_link}" target="_blank" rel="noopener noreferrer" class="mayhen_guest_btn mayhen_guest_btn_gold" style="padding: 4px 10px; font-size: 11px;">💬 Mở Zalo</a>`
                                : '';

                            card.innerHTML = `
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <div>
                                        <strong style="font-size: 13.5px; color: #1F1D1A;">${r.salutation ? r.salutation + ' ' : ''}${r.guest_name}</strong>
                                        <span style="font-size: 11px; color: #7A726A; margin-left: 6px;">(${r.group_name})</span>
                                    </div>
                                    <div>${badgeHtml}</div>
                                </div>
                                <div style="background: #FFFFFF; border: 1px solid #DCD5CB; border-radius: 6px; padding: 8px 10px; font-size: 12px; color: #4A453E; line-height: 1.4; margin-bottom: 8px;">
                                    ${r.suggested_message}
                                </div>
                                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <button type="button" class="mayhen_guest_btn mayhen_guest_btn_outline btn-copy-reminder-msg" data-msg="${encodeURIComponent(r.suggested_message)}" style="padding: 4px 10px; font-size: 11px;">
                                        📋 Sao Chép Tin Nhắn
                                    </button>
                                    ${zaloBtnHtml}
                                </div>
                            `;

                            listContainerEl.appendChild(card);
                        });

                        // Event sao chép tin nhắn
                        listContainerEl.querySelectorAll('.btn-copy-reminder-msg').forEach(b => {
                            b.addEventListener('click', () => {
                                const msg = decodeURIComponent(b.getAttribute('data-msg'));
                                navigator.clipboard.writeText(msg).then(() => {
                                    const origText = b.innerText;
                                    b.innerText = 'Đã chép!';
                                    setTimeout(() => b.innerText = origText, 2000);
                                });
                            });
                        });
                    }
                }

                if (loadingEl) loadingEl.style.display = 'none';
                if (contentEl) contentEl.style.display = 'flex';
            } catch (err) {
                if (loadingEl) loadingEl.innerHTML = `<span style="color: #C62828;">⚠️ ${err.message || 'Đã có lỗi xảy ra.'}</span>`;
            }
        });

        if (doneBtn) doneBtn.addEventListener('click', () => closeModal(reminderModal));
        setupModalDismiss(reminderModal);
    }

    // 8. Trợ Lý Soạn Lời Cảm Ơn Sau Cưới Cá Nhân Hóa (Personal Thank-you Builder - TASK-VAL-17)
    const thankyouBtn = document.getElementById('btn-open-thankyou-modal');
    const thankyouModal = document.getElementById('mayhen_modal_thankyou_builder');
    if (thankyouBtn && thankyouModal) {
        const loadingEl = document.getElementById('thankyou-loading');
        const contentEl = document.getElementById('thankyou-content');
        const attendedCountEl = document.getElementById('thankyou-attended-count');
        const giftOnlyCountEl = document.getElementById('thankyou-gift-only-count');
        const generalCountEl = document.getElementById('thankyou-general-count');
        const albumUrlEl = document.getElementById('thankyou-album-url');
        const listContainerEl = document.getElementById('thankyou-list-container');
        const doneBtn = document.getElementById('thankyou-btn-done');
        const filterBtns = thankyouModal.querySelectorAll('[data-thankyou-filter]');

        let allThankYouMessages = [];
        let currentFilter = 'all';

        const renderThankYouList = () => {
            if (!listContainerEl) return;
            listContainerEl.innerHTML = '';

            const filtered = allThankYouMessages.filter(m => {
                if (currentFilter === 'all') return true;
                return m.category === currentFilter;
            });

            if (filtered.length === 0) {
                listContainerEl.innerHTML = '<div style="text-align: center; padding: 24px; color: #7A726A; font-size: 13px;">Không có khách nào trong phân loại này.</div>';
                return;
            }

            filtered.forEach(m => {
                const card = document.createElement('div');
                card.style.cssText = 'background: #FAF8F5; border: 1px solid #E5DCCD; border-radius: 10px; padding: 12px 16px;';

                let badgeColor = '#2E7D32';
                let badgeBg = '#E8F5E9';
                if (m.category === 'gift_only') {
                    badgeColor = '#E65100';
                    badgeBg = '#FFF3E0';
                } else if (m.category === 'general') {
                    badgeColor = '#6A1B9A';
                    badgeBg = '#F3E5F5';
                }

                const badgeHtml = `<span style="background: ${badgeBg}; color: ${badgeColor}; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">${m.category_label}</span>`;

                const zaloBtnHtml = m.zalo_link
                    ? `<a href="${m.zalo_link}" target="_blank" rel="noopener noreferrer" class="mayhen_guest_btn mayhen_guest_btn_gold" style="padding: 4px 10px; font-size: 11px;">💬 Mở Zalo</a>`
                    : '';

                card.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <div>
                            <strong style="font-size: 13.5px; color: #1F1D1A;">${m.salutation ? m.salutation + ' ' : ''}${m.guest_name}</strong>
                            <span style="font-size: 11px; color: #7A726A; margin-left: 6px;">(${m.group_name})</span>
                        </div>
                        <div>${badgeHtml}</div>
                    </div>
                    <div style="background: #FFFFFF; border: 1px solid #DCD5CB; border-radius: 6px; padding: 10px 12px; font-size: 12.5px; color: #4A453E; line-height: 1.5; margin-bottom: 8px;">
                        ${m.suggested_message}
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                        <button type="button" class="mayhen_guest_btn mayhen_guest_btn_outline btn-copy-thankyou-msg" data-msg="${encodeURIComponent(m.suggested_message)}" style="padding: 4px 10px; font-size: 11px;">
                            📋 Sao Chép Lời Cảm Ơn
                        </button>
                        ${zaloBtnHtml}
                    </div>
                `;

                listContainerEl.appendChild(card);
            });

            // Gán sự kiện copy
            listContainerEl.querySelectorAll('.btn-copy-thankyou-msg').forEach(b => {
                b.addEventListener('click', () => {
                    const msg = decodeURIComponent(b.getAttribute('data-msg'));
                    navigator.clipboard.writeText(msg).then(() => {
                        const origText = b.innerText;
                        b.innerText = 'Đã chép!';
                        setTimeout(() => b.innerText = origText, 2000);
                    });
                });
            });
        };

        // Gán sự kiện filter tab
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => {
                    b.classList.remove('active');
                    b.style.background = '#fff';
                    b.style.color = '#4A453E';
                    b.style.borderColor = '#E5DCCD';
                });
                btn.classList.add('active');
                btn.style.background = '#C5A880';
                btn.style.color = '#fff';
                btn.style.borderColor = '#C5A880';

                currentFilter = btn.getAttribute('data-thankyou-filter');
                renderThankYouList();
            });
        });

        thankyouBtn.addEventListener('click', async () => {
            openModal(thankyouModal);
            if (loadingEl) loadingEl.style.display = 'block';
            if (contentEl) contentEl.style.display = 'none';

            try {
                const endpoint = window.location.pathname.replace(/\/+$/, '') + '/loi-cam-on-sau-cuoi';
                const res = await fetch(endpoint, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const json = await res.json();
                if (!json.success || !json.data) throw new Error('Không thể tải dữ liệu lời cảm ơn.');

                const data = json.data;
                allThankYouMessages = data.messages || [];

                if (attendedCountEl) attendedCountEl.innerText = data.stats.attended_count || 0;
                if (giftOnlyCountEl) giftOnlyCountEl.innerText = data.stats.gift_only_count || 0;
                if (generalCountEl) generalCountEl.innerText = data.stats.general_count || 0;
                if (albumUrlEl) albumUrlEl.innerText = data.album_link || '';

                renderThankYouList();

                if (loadingEl) loadingEl.style.display = 'none';
                if (contentEl) contentEl.style.display = 'flex';
            } catch (err) {
                if (loadingEl) loadingEl.innerHTML = `<span style="color: #C62828;">⚠️ ${err.message || 'Đã có lỗi xảy ra.'}</span>`;
            }
        });

        if (doneBtn) doneBtn.addEventListener('click', () => closeModal(thankyouModal));
        setupModalDismiss(thankyouModal);
    }

    // 10. Toggle Đã Gửi Thiệp Cho Khách Mời (Guest Open & Device Tracking)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    document.querySelectorAll('.mayhen_guest_toggle_sent_btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const url = btn.getAttribute('data-sent-url');
            if (!url) return;
            btn.disabled = true;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    const dot = btn.querySelector('.mayhen_sent_dot');
                    const text = btn.querySelector('.mayhen_sent_text');
                    if (data.is_sent) {
                        btn.classList.add('mayhen_guest_sent_active');
                        if (dot) dot.style.background = '#16A34A';
                        if (text) text.innerText = 'Đã gửi';
                        btn.title = `Đã gửi lúc ${data.sent_at || ''} - Bấm để hủy`;
                    } else {
                        btn.classList.remove('mayhen_guest_sent_active');
                        if (dot) dot.style.background = '#9CA3AF';
                        if (text) text.innerText = 'Chưa gửi';
                        btn.title = 'Bấm để đánh dấu đã gửi thiệp cho khách này';
                    }
                }
            } catch (err) {
                console.error('Lỗi cập nhật trạng thái gửi:', err);
            } finally {
                btn.disabled = false;
            }
        });
    });

    // 10. Trung Tâm Phân Phối Thiệp Cưới (Section 30 Campaign Dispatch Cockpit)
    const deliveryModal = document.getElementById('mayhen_modal_delivery_cockpit');
    const openDeliveryBtn = document.getElementById('btn-open-delivery-modal');
    const closeDeliveryBtn = document.getElementById('delivery-modal-close');
    const doneDeliveryBtn = document.getElementById('delivery-btn-done');
    const deliveryBackdrop = document.getElementById('delivery-modal-backdrop');
    const dispatchBatchBtn = document.getElementById('delivery-btn-dispatch-batch');
    const historyContainer = document.getElementById('delivery-history-container');
    const historyCount = document.getElementById('delivery-history-count');

    async function loadDeliveryHistory() {
        if (!historyContainer || !dispatchBatchBtn) return;
        const batchUrl = dispatchBatchBtn.getAttribute('data-batch-url') || '';
        // Trích xuất invitationId từ batchUrl (/tai-khoan/thiep/{id}/phan-phoi-hang-loat)
        const match = batchUrl.match(/\/thiep\/(\d+)\//);
        if (!match) return;
        const invId = match[1];

        historyContainer.innerHTML = '<div style="padding: 16px; text-align: center; color: #8C827A; font-size: 13px;">Đang tải nhật ký gửi thiệp...</div>';

        try {
            const res = await fetch(`/tai-khoan/thiep/${invId}/phan-phoi/lich-su`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success && data.data && data.data.data) {
                const list = data.data.data;
                if (historyCount) historyCount.textContent = `Tổng cộng: ${data.data.total} lượt`;

                if (list.length === 0) {
                    historyContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #8C827A; font-size: 13px;">Chưa có lượt gửi thiệp nào được ghi nhận.</div>';
                    return;
                }

                let html = '<table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">';
                html += '<tr style="background: #F8F6F2; border-bottom: 1px solid #E5DCCD; font-weight: 600; color: #4A453E;"><th style="padding: 8px 12px;">Khách Mời</th><th style="padding: 8px 12px;">Kênh</th><th style="padding: 8px 12px;">Trạng Thái</th><th style="padding: 8px 12px;">Thời Gian</th></tr>';

                list.forEach(item => {
                    const guestName = item.guest ? `${item.guest.salutation || ''} ${item.guest.name}` : 'Khách mời';
                    const channelBadge = `<span style="padding: 2px 6px; border-radius: 4px; background: #EAE6DF; font-weight: 600; text-transform: uppercase; font-size: 10px;">${item.channel}</span>`;
                    const statusBadge = item.status === 'sent' || item.status === 'delivered'
                        ? '<span style="color: #2E7D32; font-weight: 600;">✓ Thành công</span>'
                        : '<span style="color: #C62828; font-weight: 600;">✗ Thất bại</span>';
                    const sentAt = item.sent_at ? new Date(item.sent_at).toLocaleString('vi-VN') : '—';

                    html += `<tr style="border-bottom: 1px solid #F0ECE4;">
                        <td style="padding: 8px 12px; font-weight: 500; color: #1F1D1A;">${guestName}</td>
                        <td style="padding: 8px 12px;">${channelBadge}</td>
                        <td style="padding: 8px 12px;">${statusBadge}</td>
                        <td style="padding: 8px 12px; color: #7A726A;">${sentAt}</td>
                    </tr>`;
                });

                html += '</table>';
                historyContainer.innerHTML = html;
            }
        } catch (err) {
            console.error('Lỗi tải lịch sử gửi:', err);
            historyContainer.innerHTML = '<div style="padding: 16px; text-align: center; color: #C62828; font-size: 13px;">Không thể tải lịch sử gửi. Vui lòng thử lại.</div>';
        }
    }

    if (openDeliveryBtn && deliveryModal) {
        openDeliveryBtn.addEventListener('click', () => {
            deliveryModal.style.display = 'block';
            openModal(deliveryModal);
            loadDeliveryHistory();
        });
    }

    if (closeDeliveryBtn && deliveryModal) {
        closeDeliveryBtn.addEventListener('click', () => {
            closeModal(deliveryModal);
            deliveryModal.style.display = 'none';
        });
    }

    if (doneDeliveryBtn && deliveryModal) {
        doneDeliveryBtn.addEventListener('click', () => {
            closeModal(deliveryModal);
            deliveryModal.style.display = 'none';
        });
    }

    if (deliveryBackdrop && deliveryModal) {
        deliveryBackdrop.addEventListener('click', () => {
            closeModal(deliveryModal);
            deliveryModal.style.display = 'none';
        });
    }

    if (dispatchBatchBtn) {
        dispatchBatchBtn.addEventListener('click', async () => {
            const batchUrl = dispatchBatchBtn.getAttribute('data-batch-url');
            const channel = document.getElementById('delivery-batch-channel')?.value || 'copy';
            const template = document.getElementById('delivery-batch-template')?.value || '';
            const scope = document.getElementById('delivery-batch-scope')?.value || 'unsent';

            // Thu thập guest IDs từ DOM
            const guestRows = document.querySelectorAll('.mayhen_guest_toggle_sent_btn');
            const guestIds = [];
            guestRows.forEach(btn => {
                const id = btn.getAttribute('data-guest-id');
                const isSent = btn.classList.contains('mayhen_guest_sent_active');
                if (id) {
                    if (scope === 'all' || (scope === 'unsent' && !isSent)) {
                        guestIds.push(parseInt(id));
                    }
                }
            });

            if (guestIds.length === 0) {
                alert('Không tìm thấy khách mời nào phù hợp với điều kiện để gửi thiệp.');
                return;
            }

            if (!confirm(`Bạn có chắc chắn muốn phát hành thiệp cưới qua kênh [${channel.toUpperCase()}] cho ${guestIds.length} khách mời?`)) {
                return;
            }

            dispatchBatchBtn.disabled = true;
            dispatchBatchBtn.textContent = 'Đang xử lý phân phối...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(batchUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        guest_ids: guestIds,
                        channel: channel,
                        custom_template: template || null,
                    }),
                });

                const data = await res.json();
                if (data.success) {
                    alert(`Thành công! Đã ghi nhận gửi ${data.result.successful}/${data.result.total} thiệp cưới.`);
                    // Tải lại trang để đồng bộ toàn bộ bảng
                    window.location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi phân phối thiệp.');
                }
            } catch (err) {
                console.error('Lỗi dispatch batch:', err);
                alert('Lỗi kết nối khi gửi thiệp hàng loạt.');
            } finally {
                dispatchBatchBtn.disabled = false;
                dispatchBatchBtn.textContent = 'Bắt Đầu Ghi Nhận Phân Phối Hàng Loạt';
            }
        });
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
