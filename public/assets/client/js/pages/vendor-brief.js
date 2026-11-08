/**
 * Vendor Brief Tab Switcher
 * Pure Native ES Module
 */
document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('.mayhen_vendor_brief_tab_btn');
    const sections = {
        mc: document.getElementById('brief-mc'),
        venue: document.getElementById('brief-venue'),
        photo: document.getElementById('brief-photo')
    };

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const targetTab = btn.getAttribute('data-tab');

            if (targetTab === 'all') {
                Object.values(sections).forEach(s => s && (s.style.display = 'block'));
            } else {
                Object.entries(sections).forEach(([key, sec]) => {
                    if (!sec) return;
                    sec.style.display = (key === targetTab) ? 'block' : 'none';
                });
            }
        });
    });
});

