/**
 * Family Review & Blessing Approval (TASK-VAL-19)
 * Native Vanilla ES Module
 */

document.addEventListener('DOMContentLoaded', () => {
    const btnShowApprove = document.getElementById('btn-show-approve-form');
    const btnShowFeedback = document.getElementById('btn-show-feedback-form');
    const panelApprove = document.getElementById('panel-approve-note');
    const panelFeedback = document.getElementById('panel-feedback-note');

    if (btnShowApprove && panelApprove) {
        btnShowApprove.addEventListener('click', () => {
            panelApprove.style.display = 'block';
            if (panelFeedback) panelFeedback.style.display = 'none';
            panelApprove.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }

    if (btnShowFeedback && panelFeedback) {
        btnShowFeedback.addEventListener('click', () => {
            panelFeedback.style.display = 'block';
            if (panelApprove) panelApprove.style.display = 'none';
            panelFeedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }
});

