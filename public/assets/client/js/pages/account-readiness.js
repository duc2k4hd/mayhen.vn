/**
 * Account Wedding Readiness Modal & Shared Account Clipboard Helpers
 * Pure Native Vanilla JS (ES Modules) - Zero External Libraries
 */

const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

async function copyTextSafe(text) {
    if (!text) return false;
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            return true;
        }
    } catch {
        // Fallback below
    }

    try {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        const ok = document.execCommand('copy');
        document.body.removeChild(ta);
        return ok;
    } catch {
        return false;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // 1. Delegated safe copy handler for Account URL buttons (preserves SVG icons)
    document.addEventListener('click', async (e) => {
        const copyBtn = e.target.closest('[data-copy-invitation-url]');
        if (!copyBtn) return;
        e.preventDefault();

        const url = copyBtn.getAttribute('data-copy-invitation-url') || '';
        const defaultLabel = copyBtn.getAttribute('data-default-label') || 'Sao chép';
        const labelEl = copyBtn.querySelector('.mayhen_copy_label');
        const ok = await copyTextSafe(url);

        if (labelEl) {
            labelEl.textContent = ok ? 'Đã chép!' : 'Hãy chép tay';
        } else {
            copyBtn.textContent = ok ? 'Đã chép!' : 'Hãy chép tay';
        }

        window.setTimeout(() => {
            if (labelEl) {
                labelEl.textContent = defaultLabel;
            } else {
                copyBtn.textContent = defaultLabel;
            }
        }, 2000);
    });

    // 2. Wedding Readiness Checklist Modal
    const modal = document.getElementById('mayhen_readiness_modal');
    if (!modal) return;

    const circleEl = document.getElementById('readiness_summary_circle');
    const titleEl = document.getElementById('readiness_summary_title');
    const descEl = document.getElementById('readiness_summary_desc');
    const itemsContainer = document.getElementById('readiness_items_container');
    const subtitleEl = document.getElementById('readiness_modal_subtitle');

    const openModal = () => {
        modal.style.removeProperty('display');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.style.removeProperty('display');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    modal.querySelectorAll('[data-close-readiness]').forEach((btn) => {
        btn.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    document.querySelectorAll('.mayhen_readiness_toggle_btn').forEach((btn) => {
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

            const invId = btn.getAttribute('data-invitation-id');
            if (invId) {
                fetch(`/api/editor/readiness/${encodeURIComponent(invId)}`)
                    .then((res) => res.json())
                    .then((res) => {
                        if (res.success && res.data) {
                            renderReadinessData(res.data);
                            openModal();
                        }
                    })
                    .catch((e) => console.error('Lỗi nạp dữ liệu readiness:', e));
            }
        });
    });

    function renderReadinessData(data) {
        const score = Number(data.score) || 0;
        const passedCount = Number(data.passed_count) || 0;
        const totalCount = Number(data.total_count) || 7;
        const items = Array.isArray(data.items) ? data.items : [];

        if (circleEl) {
            circleEl.textContent = `${score}%`;
            circleEl.style.background = score >= 85 ? '#2E7D32' : (score >= 50 ? '#C5A880' : '#C62828');
        }

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

        if (itemsContainer) {
            itemsContainer.innerHTML = '';
            items.forEach((item, index) => {
                const itemEl = document.createElement('div');
                itemEl.className = 'mayhen_readiness_item';

                const iconHtml = item.passed
                    ? `<div class="mayhen_readiness_item_icon passed" title="Đã hoàn thành">✓</div>`
                    : `<div class="mayhen_readiness_item_icon pending" title="Cần hoàn thiện">!</div>`;

                const safeUrl = typeof item.action_url === 'string' && (item.action_url.startsWith('/') || item.action_url.startsWith('http'))
                    ? escapeHtml(item.action_url)
                    : '';
                const actionHtml = safeUrl && safeUrl !== '#'
                    ? `<a href="${safeUrl}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm mayhen_readiness_item_action">
                           ${escapeHtml(item.action_text || 'Thực hiện')}
                       </a>`
                    : '';

                itemEl.innerHTML = `
                    ${iconHtml}
                    <div class="mayhen_readiness_item_body">
                        <div class="mayhen_readiness_item_title">${index + 1}. ${escapeHtml(item.title || '')}</div>
                        <div class="mayhen_readiness_item_desc">${escapeHtml(item.description || '')}</div>
                    </div>
                    ${actionHtml}
                `;

                itemsContainer.appendChild(itemEl);
            });
        }
    }
});
