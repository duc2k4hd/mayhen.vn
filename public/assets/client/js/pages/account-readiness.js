/**
 * Account Wedding Readiness Modal Logic
 * Pure Native Vanilla JS (ES Modules) - Zero External Libraries
 */

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('mayhen_readiness_modal');
    if (!modal) return;

    const circleEl = document.getElementById('readiness_summary_circle');
    const titleEl = document.getElementById('readiness_summary_title');
    const descEl = document.getElementById('readiness_summary_desc');
    const itemsContainer = document.getElementById('readiness_items_container');
    const subtitleEl = document.getElementById('readiness_modal_subtitle');

    const openModal = () => {
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    // Đóng khi click backdrop hoặc nút đóng
    modal.querySelectorAll('[data-close-readiness]').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    // Đóng khi nhấn phím Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
    });

    // Lắng nghe click các nút mở bảng độ sẵn sàng
    document.querySelectorAll('.mayhen_readiness_toggle_btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const rawData = btn.getAttribute('data-readiness');
            const coupleName = btn.getAttribute('data-couple-name') || 'Thiệp Cưới';
            if (subtitleEl) {
                subtitleEl.textContent = `Rà soát 7 hạng mục ngày cưới cho đôi uyên ương: ${coupleName}`;
            }

            if (rawData) {
                try {
                    const data = JSON.parse(rawData);
                    renderReadinessData(data);
                    openModal();
                    return;
                } catch (err) {
                    console.error('Lỗi phân tích JSON readiness:', err);
                }
            }

            // Fallback gọi API nếu cần
            const invId = btn.getAttribute('data-invitation-id');
            if (invId) {
                fetch(`/api/editor/readiness/${invId}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.success && res.data) {
                            renderReadinessData(res.data);
                            openModal();
                        }
                    })
                    .catch(e => console.error('Lỗi nạp dữ liệu readiness:', e));
            }
        });
    });

    function renderReadinessData(data) {
        const score = data.score || 0;
        const passedCount = data.passed_count || 0;
        const totalCount = data.total_count || 7;
        const items = data.items || [];

        // 1. Cập nhật vòng tròn điểm số
        if (circleEl) {
            circleEl.textContent = `${score}%`;
            circleEl.style.background = score >= 85 ? '#2E7D32' : (score >= 50 ? '#C5A880' : '#C62828');
        }

        // 2. Cập nhật tiêu đề & mô tả
        if (titleEl) {
            if (score >= 85) {
                titleEl.textContent = `Đám cưới đã sẵn sàng! (${passedCount}/${totalCount} bước đạt chuẩn)`;
                titleEl.style.color = '#2E7D32';
            } else if (score >= 50) {
                titleEl.textContent = `Cần lưu ý bổ sung (${passedCount}/${totalCount} bước đạt chuẩn)`;
                titleEl.style.color = '#E65100';
            } else {
                titleEl.textContent = `Chưa hoàn tất khâu chuẩn bị (${passedCount}/${totalCount} bước đạt chuẩn)`;
                titleEl.style.color = '#C62828';
            }
        }

        if (descEl) {
            descEl.textContent = score >= 85
                ? 'Tuyệt vời! Hai bạn đã hoàn tất hầu hết các thông tin và tài liệu quan trọng trước ngày cưới.'
                : 'Hãy hoàn thiện các mục màu vàng bên dưới để khách mời và gia đình hai bên an tâm tuyệt đối.';
        }

        // 3. Render danh sách 7 hạng mục
        if (itemsContainer) {
            itemsContainer.innerHTML = '';
            items.forEach((item, index) => {
                const itemEl = document.createElement('div');
                itemEl.className = 'mayhen_readiness_item';

                const iconHtml = item.passed
                    ? `<div class="mayhen_readiness_item_icon passed" title="Đã hoàn thành">✓</div>`
                    : `<div class="mayhen_readiness_item_icon pending" title="Cần hoàn thiện">!</div>`;

                const actionHtml = item.action_url && item.action_url !== '#'
                    ? `<a href="${item.action_url}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm mayhen_readiness_item_action">
                           ${item.action_text || 'Thực hiện'}
                       </a>`
                    : '';

                itemEl.innerHTML = `
                    ${iconHtml}
                    <div class="mayhen_readiness_item_body">
                        <div class="mayhen_readiness_item_title">${index + 1}. ${item.title}</div>
                        <div class="mayhen_readiness_item_desc">${item.description}</div>
                    </div>
                    ${actionHtml}
                `;

                itemsContainer.appendChild(itemEl);
            });
        }
    }
});

