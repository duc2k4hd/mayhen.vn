/**
 * MAYHEN.VN — ZALO SHARE & SOCIAL DISPATCH STUDIO (MỤC 2.5 MASTER DOCX)
 * Strict Pure Vanilla ES Modules • Zero external libraries
 */

export function initShareStudio() {
    const appEl = document.getElementById('mayhen_share_app');
    if (!appEl) return;

    const composeUrl = appEl.getAttribute('data-compose-url');
    const logUrl = appEl.getAttribute('data-log-url');
    const csrfToken = appEl.getAttribute('data-csrf');

    let currentTemplateKey = 'formal';
    let currentGuestId = null;

    const salutationSelect = document.getElementById('share_salutation');
    const guestNameInput = document.getElementById('share_guest_name');
    const messageTextarea = document.getElementById('share_message_content');
    const zaloBtn = document.getElementById('mayhen_share_zalo_btn');
    const copyBtn = document.getElementById('mayhen_share_copy_btn');
    const nativeBtn = document.getElementById('mayhen_share_native_btn');
    const toastEl = document.getElementById('mayhen_share_toast');

    const receiverNameMockup = document.getElementById('mockup_receiver_name');
    const bubbleTextMockup = document.getElementById('mockup_bubble_text');
    const templateBtns = document.querySelectorAll('.mayhen_share_template_btn');
    const guestChips = document.querySelectorAll('.mayhen_share_chip');

    function showToast(message, type = 'success') {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.style.display = 'block';
        toastEl.style.background = type === 'success' ? '#E8F5E9' : '#FDEDEC';
        toastEl.style.color = type === 'success' ? '#1B5E20' : '#922B21';
        toastEl.style.borderColor = type === 'success' ? '#A5D6A7' : '#F5B7B1';

        setTimeout(() => {
            toastEl.style.display = 'none';
        }, 3500);
    }

    // Gửi log thống kê chia sẻ
    async function logShareEvent(channel, recipientName = null) {
        if (!logUrl) return;
        try {
            await fetch(logUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    channel: channel,
                    template_key: currentTemplateKey,
                    recipient_name: recipientName || guestNameInput?.value || null,
                }),
            });
        } catch (e) {
            console.warn('Could not log share event:', e);
        }
    }

    // Cập nhật nội dung tin nhắn qua Ajax
    async function updateComposedMessage() {
        const salutation = salutationSelect ? salutationSelect.value : 'Bạn';
        const guestName = guestNameInput ? guestNameInput.value.trim() : 'Khách Quý';

        if (receiverNameMockup) {
            receiverNameMockup.textContent = `${salutation} ${guestName}`;
        }

        try {
            const res = await fetch(composeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    salutation: salutation,
                    guest_name: guestName,
                    template_key: currentTemplateKey,
                    guest_id: currentGuestId,
                }),
            });

            const data = await res.json();
            if (data.success && data.message) {
                if (messageTextarea) {
                    messageTextarea.value = data.message;
                }
                if (bubbleTextMockup) {
                    bubbleTextMockup.textContent = data.message;
                }
                if (zaloBtn && data.zalo_share_url) {
                    zaloBtn.href = data.zalo_share_url;
                }
            }
        } catch (err) {
            console.error('Error composing message:', err);
        }
    }

    // Chọn kịch bản mẫu
    templateBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            templateBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentTemplateKey = btn.getAttribute('data-template-key') || 'formal';
            updateComposedMessage();
        });
    });

    // Chọn nhanh khách từ danh bạ
    guestChips.forEach(chip => {
        chip.addEventListener('click', () => {
            currentGuestId = chip.getAttribute('data-guest-id');
            const sal = chip.getAttribute('data-salutation');
            const name = chip.getAttribute('data-name');

            if (salutationSelect && sal) {
                salutationSelect.value = sal;
            }
            if (guestNameInput && name) {
                guestNameInput.value = name;
            }
            updateComposedMessage();
        });
    });

    // Thay đổi danh xưng hoặc tên khách
    if (salutationSelect) {
        salutationSelect.addEventListener('change', () => {
            currentGuestId = null;
            updateComposedMessage();
        });
    }

    if (guestNameInput) {
        let timer = null;
        guestNameInput.addEventListener('input', () => {
            currentGuestId = null;
            clearTimeout(timer);
            timer = setTimeout(updateComposedMessage, 300);
        });
    }

    // Sửa trực tiếp trong textarea
    if (messageTextarea) {
        messageTextarea.addEventListener('input', () => {
            if (bubbleTextMockup) {
                bubbleTextMockup.textContent = messageTextarea.value;
            }
        });
    }

    // Sao chép tin nhắn vào Clipboard
    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            const textToCopy = messageTextarea ? messageTextarea.value : '';
            if (!textToCopy) return;

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(textToCopy);
                } else {
                    messageTextarea.select();
                    document.execCommand('copy');
                }
                showToast('✓ Đã sao chép toàn bộ lời mời kèm liên kết thiệp!');
                logShareEvent('clipboard');
            } catch (err) {
                console.error('Copy failed:', err);
                showToast('Không thể sao chép, vui lòng thử bôi đen văn bản.', 'error');
            }
        });
    }

    // Nút mở Zalo
    if (zaloBtn) {
        zaloBtn.addEventListener('click', () => {
            logShareEvent('zalo');
        });
    }

    // Nút Web Share API native
    if (nativeBtn) {
        nativeBtn.addEventListener('click', async () => {
            const text = messageTextarea ? messageTextarea.value : '';
            const url = appEl.getAttribute('data-invitation-url');

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: 'Thiệp Mời Cưới Mây Hẹn',
                        text: text,
                        url: url,
                    });
                    showToast('✓ Đã chia sẻ thành công!');
                    logShareEvent('web_share');
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        console.warn('Share failed:', err);
                    }
                }
            } else {
                // Fallback nếu trình duyệt không hỗ trợ Web Share API
                if (copyBtn) {
                    copyBtn.click();
                }
            }
        });
    }

    // Khởi chạy lần đầu
    updateComposedMessage();
}

// Tự động khởi tạo khi DOM sẵn sàng
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initShareStudio);
} else {
    initShareStudio();
}
