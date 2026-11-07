/**
 * Mây Hẹn (mayhen.vn) — Seating Planner Studio Native JavaScript
 * ES Modules & Zero External Libraries
 */

document.addEventListener('DOMContentLoaded', () => {
    const appEl = document.getElementById('mayhen_seating_app');
    if (!appEl) return;

    const assignGuestUrl = appEl.dataset.assignUrl;
    const updatePositionsUrl = appEl.dataset.positionsUrl;
    const csrfToken = appEl.dataset.csrf;

    const canvasEl = document.getElementById('floorplan_canvas');
    const drawerListEl = document.getElementById('drawer_guests_list');
    const drawerSearchInput = document.getElementById('drawer_search_input');

    // 1. Live Filter tìm nhanh khách trong ngăn kéo
    if (drawerSearchInput && drawerListEl) {
        drawerSearchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const cards = drawerListEl.querySelectorAll('.guest_drag_card');
            cards.forEach(card => {
                const name = card.dataset.guestName.toLowerCase();
                const group = (card.dataset.guestGroup || '').toLowerCase();
                if (name.includes(query) || group.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // 2. Drag & Drop Khách Mời vào Bàn Tiệc
    let draggedGuestId = null;

    document.querySelectorAll('.guest_drag_card').forEach(card => {
        card.addEventListener('dragstart', (e) => {
            draggedGuestId = card.dataset.guestId;
            e.dataTransfer.setData('text/plain', draggedGuestId);
            e.dataTransfer.effectAllowed = 'move';
            card.style.opacity = '0.5';
        });

        card.addEventListener('dragend', () => {
            card.style.opacity = '1';
        });
    });

    const tables = document.querySelectorAll('.seat_table_item');
    tables.forEach(tableEl => {
        tableEl.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            tableEl.classList.add('is-dragover');
        });

        tableEl.addEventListener('dragleave', () => {
            tableEl.classList.remove('is-dragover');
        });

        tableEl.addEventListener('drop', async (e) => {
            e.preventDefault();
            tableEl.classList.remove('is-dragover');

            const guestId = e.dataTransfer.getData('text/plain') || draggedGuestId;
            const tableId = tableEl.dataset.tableId;

            if (!guestId || !tableId) return;

            try {
                const res = await fetch(assignGuestUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        guest_id: guestId,
                        table_id: tableId
                    })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    // Reload nhẹ nhàng để đồng bộ số lượng ghế & badge chính xác
                    window.location.reload();
                } else {
                    alert(data.message || 'Không thể xếp khách vào bàn này.');
                }
            } catch (err) {
                console.error('Lỗi khi gán khách:', err);
                alert('Lỗi kết nối khi xếp khách vào bàn.');
            }
        });
    });

    // 3. Kéo thả di chuyển vị trí bàn trên Canvas (Position Dragging)
    let activeDragTable = null;
    let initialX = 0;
    let initialY = 0;
    let currentX = 0;
    let currentY = 0;
    let savePositionsTimeout = null;

    tables.forEach(tableEl => {
        tableEl.addEventListener('mousedown', (e) => {
            // Không kích hoạt kéo bàn nếu bấm vào nút chi tiết hoặc link
            if (e.target.closest('.table_foot_actions') || e.target.closest('button') || e.target.closest('a')) {
                return;
            }

            activeDragTable = tableEl;
            initialX = e.clientX - tableEl.offsetLeft;
            initialY = e.clientY - tableEl.offsetTop;
            tableEl.style.zIndex = '50';
        });
    });

    document.addEventListener('mousemove', (e) => {
        if (!activeDragTable || !canvasEl) return;

        e.preventDefault();
        const canvasRect = canvasEl.getBoundingClientRect();

        currentX = e.clientX - initialX;
        currentY = e.clientY - initialY;

        // Giới hạn trong phạm vi canvas
        currentX = Math.max(10, Math.min(currentX, canvasEl.scrollWidth - activeDragTable.offsetWidth - 20));
        currentY = Math.max(10, Math.min(currentY, canvasEl.scrollHeight - activeDragTable.offsetHeight - 20));

        activeDragTable.style.left = `${currentX}px`;
        activeDragTable.style.top = `${currentY}px`;
        activeDragTable.dataset.posX = currentX;
        activeDragTable.dataset.posY = currentY;
    });

    document.addEventListener('mouseup', () => {
        if (activeDragTable) {
            activeDragTable.style.zIndex = '10';
            activeDragTable = null;

            // Debounce lưu tọa độ toàn bộ bàn
            clearTimeout(savePositionsTimeout);
            savePositionsTimeout = setTimeout(saveTablePositions, 600);
        }
    });

    async function saveTablePositions() {
        const positions = [];
        document.querySelectorAll('.seat_table_item').forEach(tbl => {
            positions.push({
                id: tbl.dataset.tableId,
                pos_x: parseInt(tbl.dataset.posX || tbl.offsetLeft, 10),
                pos_y: parseInt(tbl.dataset.posY || tbl.offsetTop, 10)
            });
        });

        try {
            await fetch(updatePositionsUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ positions })
            });
        } catch (err) {
            console.error('Không thể lưu tọa độ bàn tiệc:', err);
        }
    }

    // 4. Mở Modal Thêm Bàn Mới
    const addTableBtn = document.getElementById('btn_open_add_table');
    const addTableModal = document.getElementById('modal_add_table');
    const closeAddTableBtn = document.getElementById('btn_close_add_table');

    if (addTableBtn && addTableModal) {
        addTableBtn.addEventListener('click', () => {
            addTableModal.style.display = 'flex';
        });
    }

    if (closeAddTableBtn && addTableModal) {
        closeAddTableBtn.addEventListener('click', () => {
            addTableModal.style.display = 'none';
        });
    }

    // 5. Gỡ khách khỏi bàn qua Modal Chi Tiết Bàn
    window.removeGuestFromTable = async function(guestId) {
        if (!confirm('Bạn có chắc muốn gỡ khách này về danh sách chưa xếp bàn?')) return;

        try {
            const res = await fetch(assignGuestUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    guest_id: guestId,
                    table_id: null
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Không thể gỡ khách.');
            }
        } catch (err) {
            console.error('Lỗi khi gỡ khách:', err);
        }
    };
});

