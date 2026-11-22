/**
 * MAYHEN.VN — CLIENT FAQ PAGE SCRIPT
 * Quy chuẩn: 100% Vanilla JavaScript Native ES Modules
 * Tính năng: Accordion tương tác, Instant Search tiếng Việt không dấu, Category Tabs Filter, Deep Linking qua URL Hash.
 */
import { normalizeVietnameseSearch as normalizeVietnamese } from '../common/vietnamese-text.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Phân giải các phần tử DOM
    const searchInput = document.getElementById('mayhen_faq_search_input');
    const searchClear = document.getElementById('mayhen_faq_search_clear');
    const tabBtns = document.querySelectorAll('.mayhen_faq_tab_btn');
    const faqGroups = document.querySelectorAll('.mayhen_faq_group');
    const faqCards = document.querySelectorAll('.mayhen_faq_card');
    const emptyState = document.getElementById('mayhen_faq_empty_state');
    const emptyKeywordSpan = document.getElementById('mayhen_faq_empty_keyword');
    const resetSearchBtn = document.getElementById('mayhen_faq_reset_search_btn');

    let currentCategory = 'all';
    let currentSearchTerm = '';

    /**
     * Xử lý đóng/mở Accordion
     */
    function toggleCard(card, shouldOpen) {
        const isOpen = card.classList.contains('is-open');
        const nextState = shouldOpen !== undefined ? shouldOpen : !isOpen;
        const btn = card.querySelector('.mayhen_faq_question_btn');

        if (nextState) {
            card.classList.add('is-open');
            if (btn) btn.setAttribute('aria-expanded', 'true');
        } else {
            card.classList.remove('is-open');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    }

    // Gắn sự kiện click cho từng card câu hỏi
    faqCards.forEach((card) => {
        const btn = card.querySelector('.mayhen_faq_question_btn');
        if (btn) {
            btn.addEventListener('click', () => {
                toggleCard(card);
            });
        }
    });

    /**
     * Lọc danh sách câu hỏi dựa trên Tab và Từ khóa tìm kiếm
     */
    function filterFaqs() {
        const normTerm = normalizeVietnamese(currentSearchTerm);
        let visibleTotal = 0;

        // Cập nhật trạng thái hiển thị của nút xóa tìm kiếm
        if (searchClear) {
            searchClear.style.display = currentSearchTerm.length > 0 ? 'inline-flex' : 'none';
        }

        faqGroups.forEach((group) => {
            const groupCategory = group.getAttribute('data-group-category');
            const groupCards = group.querySelectorAll('.mayhen_faq_card');
            let visibleInGroup = 0;

            // Kiểm tra nhóm có khớp với Category Tab hiện tại không
            const matchesCategory = currentCategory === 'all' || currentCategory === groupCategory;

            groupCards.forEach((card) => {
                const cardCategory = card.getAttribute('data-category');
                const cardQuestion = card.getAttribute('data-question') || '';
                const cardAnswer = card.getAttribute('data-answer') || '';

                const matchesCardCat = currentCategory === 'all' || currentCategory === cardCategory;

                // Kiểm tra từ khóa tìm kiếm (trong cả câu hỏi lẫn câu trả lời)
                let matchesSearch = true;
                if (normTerm) {
                    const normQ = normalizeVietnamese(cardQuestion);
                    const normA = normalizeVietnamese(cardAnswer);
                    matchesSearch = normQ.includes(normTerm) || normA.includes(normTerm);
                }

                if (matchesCategory && matchesCardCat && matchesSearch) {
                    card.classList.remove('is-hidden');
                    visibleInGroup++;
                    visibleTotal++;
                } else {
                    card.classList.add('is-hidden');
                }
            });

            // Nếu nhóm không có câu hỏi nào khớp -> ẩn cả nhóm
            if (visibleInGroup > 0) {
                group.classList.remove('is-hidden');
            } else {
                group.classList.add('is-hidden');
            }
        });

        // Hiển thị hoặc ẩn Empty State
        if (emptyState) {
            if (visibleTotal === 0) {
                emptyState.classList.add('is-visible');
                if (emptyKeywordSpan) {
                    emptyKeywordSpan.textContent = currentSearchTerm ? `"${currentSearchTerm}"` : 'tiêu chí đã chọn';
                }
            } else {
                emptyState.classList.remove('is-visible');
            }
        }
    }

    /**
     * Sự kiện tìm kiếm Realtime (Debounce nhẹ)
     */
    let searchDebounceTimer = null;
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchDebounceTimer);
            currentSearchTerm = e.target.value.trim();
            searchDebounceTimer = setTimeout(() => {
                filterFaqs();
            }, 100);
        });

        // Hỗ trợ phím Escape để xóa tìm kiếm
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                searchInput.value = '';
                currentSearchTerm = '';
                filterFaqs();
            }
        });
    }

    // Nút Clear Search
    if (searchClear) {
        searchClear.addEventListener('click', () => {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
            currentSearchTerm = '';
            filterFaqs();
        });
    }

    // Nút Reset Search trong Empty State
    if (resetSearchBtn) {
        resetSearchBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            currentSearchTerm = '';
            currentCategory = 'all';

            // Reset tab active
            tabBtns.forEach((btn) => {
                btn.classList.toggle('is-active', btn.getAttribute('data-tab') === 'all');
            });

            filterFaqs();
        });
    }

    /**
     * Sự kiện chuyển đổi Category Tabs
     */
    tabBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const tab = btn.getAttribute('data-tab');
            if (tab === currentCategory) return;

            tabBtns.forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            currentCategory = tab;
            filterFaqs();
        });
    });

    /**
     * Xử lý mở câu hỏi qua URL Hash (Deep Linking)
     * Ví dụ: /cau-hoi-thuong-gap#faq-vietqr
     */
    function handleUrlHash() {
        const hash = window.location.hash;
        if (!hash) return;

        const targetId = hash.replace('#', '');
        const targetCard = document.getElementById(targetId);

        if (targetCard && targetCard.classList.contains('mayhen_faq_card')) {
            // Đảm bảo card không bị ẩn do bộ lọc
            const cardCategory = targetCard.getAttribute('data-category');
            if (currentCategory !== 'all' && currentCategory !== cardCategory) {
                currentCategory = 'all';
                tabBtns.forEach((b) => b.classList.toggle('is-active', b.getAttribute('data-tab') === 'all'));
                filterFaqs();
            }

            // Mở câu hỏi
            toggleCard(targetCard, true);

            // Cuộn mượt tới vị trí câu hỏi
            setTimeout(() => {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 250);
        }
    }

    handleUrlHash();
    window.addEventListener('hashchange', handleUrlHash);
});
