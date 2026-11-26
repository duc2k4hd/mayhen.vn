/**
 * Mây Hẹn Cookie & Data Privacy Consent Banner (Pure Vanilla JS ES Module)
 */
(function () {
    'use strict';

    const banner = document.getElementById('mayhenCookieBanner');
    if (!banner) return;

    const prefsPanel = document.getElementById('mayhenCookiePrefsPanel');
    const consentUrl = banner.getAttribute('data-consent-url');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    async function submitConsent(payload) {
        try {
            await fetch(consentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    analytics: !!payload.analytics,
                    personalization: !!payload.personalization,
                    marketing: !!payload.marketing,
                    source_url: window.location.href.slice(0, 300),
                }),
            });
        } catch (e) {
            // Fallback local cookie nếu mạng gián đoạn
            document.cookie = 'mayhen_cookie_consent=1; path=/; max-age=' + (60 * 60 * 24 * 180) + '; SameSite=Lax';
        }
        banner.classList.remove('is-visible');
    }

    banner.addEventListener('click', (ev) => {
        const btn = ev.target.closest('[data-cookie-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-cookie-action');
        if (action === 'customize') {
            if (prefsPanel) prefsPanel.hidden = !prefsPanel.hidden;
            return;
        }
        if (action === 'accept-all') {
            submitConsent({ analytics: true, personalization: true, marketing: true });
            return;
        }
        if (action === 'essential') {
            submitConsent({ analytics: false, personalization: false, marketing: false });
            return;
        }
        if (action === 'save-custom') {
            submitConsent({
                analytics: !!document.getElementById('mayhenCookieOptAnalytics')?.checked,
                personalization: !!document.getElementById('mayhenCookieOptPersonalization')?.checked,
                marketing: !!document.getElementById('mayhenCookieOptMarketing')?.checked,
            });
        }
    });

    document.addEventListener('click', (ev) => {
        if (ev.target.closest('[data-mayhen-open-cookie-settings]')) {
            banner.classList.add('is-visible');
            if (prefsPanel) prefsPanel.hidden = false;
        }
    });
})();
