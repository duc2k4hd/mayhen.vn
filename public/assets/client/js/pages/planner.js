/**
 * MAY HEN - WEDDING PLANNER & BUDGET ENGINE
 * Native ES Modules, Zero External Libraries
 */

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. Task Checkbox Toggle Realtime
    document.querySelectorAll('.mayhen_planner_checkbox').forEach(box => {
        box.addEventListener('click', async () => {
            const row = box.closest('.mayhen_planner_task_row');
            const toggleUrl = box.getAttribute('data-toggle-url');
            if (!toggleUrl) return;

            // Optimistic UI update
            const isCurrentlyChecked = box.classList.contains('checked');
            box.classList.toggle('checked', !isCurrentlyChecked);
            row.classList.toggle('is_completed', !isCurrentlyChecked);

            try {
                const res = await fetch(toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    // Update progress UI if elements exist
                    const progressFill = document.getElementById('planner-progress-fill');
                    const progressText = document.getElementById('planner-progress-text');
                    const completedKpi = document.getElementById('planner-completed-kpi');

                    if (progressFill && data.summary) {
                        progressFill.style.width = data.summary.progress_percent + '%';
                    }
                    if (progressText && data.summary) {
                        progressText.innerText = `${data.summary.completed}/${data.summary.total} việc (${data.summary.progress_percent}%)`;
                    }
                    if (completedKpi && data.summary) {
                        completedKpi.innerText = data.summary.completed;
                    }
                } else {
                    // Revert on error
                    box.classList.toggle('checked', isCurrentlyChecked);
                    row.classList.toggle('is_completed', isCurrentlyChecked);
                }
            } catch (err) {
                console.error('Lỗi cập nhật tiến độ:', err);
                box.classList.toggle('checked', isCurrentlyChecked);
                row.classList.toggle('is_completed', isCurrentlyChecked);
            }
        });
    });

    // 2. Modals Management
    window.openModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('is_open');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('is_open');
            document.body.style.overflow = '';
        }
    };

    // Close on backdrop click & ESC key
    document.querySelectorAll('.mayhen_planner_modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('is_open');
                document.body.style.overflow = '';
            }
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.mayhen_planner_modal.is_open').forEach(m => {
                m.classList.remove('is_open');
            });
            document.body.style.overflow = '';
        }
    });

    // 3. Edit Task Modal Setup
    window.openEditTaskModal = function(task) {
        const modal = document.getElementById('modal-edit-task');
        if (!modal) return;

        const form = document.getElementById('form-edit-task');
        if (form && task.update_url) {
            form.action = task.update_url;
        }

        const titleInput = document.getElementById('edit-task-title');
        const phaseSelect = document.getElementById('edit-task-phase');
        const prioritySelect = document.getElementById('edit-task-priority');
        const assigneeInput = document.getElementById('edit-task-assignee');
        const dueDateInput = document.getElementById('edit-task-due-date');
        const descInput = document.getElementById('edit-task-desc');

        if (titleInput) titleInput.value = task.title || '';
        if (phaseSelect) phaseSelect.value = task.phase || 'wedding_day';
        if (prioritySelect) prioritySelect.value = task.priority || 'medium';
        if (assigneeInput) assigneeInput.value = task.assignee_name || '';
        if (dueDateInput) dueDateInput.value = task.due_date || '';
        if (descInput) descInput.value = task.description || '';

        openModal('modal-edit-task');
    };

    // 4. Edit Budget Item Modal Setup
    window.openEditBudgetModal = function(item) {
        const modal = document.getElementById('modal-edit-budget');
        if (!modal) return;

        const form = document.getElementById('form-edit-budget');
        if (form && item.update_url) {
            form.action = item.update_url;
        }

        const nameInput = document.getElementById('edit-budget-name');
        const catSelect = document.getElementById('edit-budget-cat');
        const estInput = document.getElementById('edit-budget-est');
        const actInput = document.getElementById('edit-budget-act');
        const paidInput = document.getElementById('edit-budget-paid');
        const vendorInput = document.getElementById('edit-budget-vendor');
        const contactInput = document.getElementById('edit-budget-contact');
        const notesInput = document.getElementById('edit-budget-notes');

        if (nameInput) nameInput.value = item.item_name || '';
        if (catSelect) catSelect.value = item.category || 'other';
        if (estInput) estInput.value = item.estimated_cost || 0;
        if (actInput) actInput.value = item.actual_cost || 0;
        if (paidInput) paidInput.value = item.paid_amount || 0;
        if (vendorInput) vendorInput.value = item.vendor_name || '';
        if (contactInput) contactInput.value = item.vendor_contact || '';
        if (notesInput) notesInput.value = item.notes || '';

        openModal('modal-edit-budget');
    };
});

