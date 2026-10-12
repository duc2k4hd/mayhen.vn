/**
 * MAYHEN.VN — LIVE INVITATION CONTROLLER
 * Vanilla ES Module
 */
import { $, on } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Envelope Unseal Motion
    const sealBtn = $('[data-mayhen-action="unseal-envelope"]');
    const envelope = $('[data-mayhen-component="envelope"]');
    const invitationCard = $('[data-mayhen-component="invitation-card"]');

    if (sealBtn && envelope && invitationCard) {
        on(sealBtn, 'click', () => {
            sealBtn.style.transform = 'scale(0.85) rotate(15deg)';
            sealBtn.style.opacity = '0.5';
            
            setTimeout(() => {
                envelope.style.opacity = '0';
                envelope.style.transform = 'translateY(-20px) scale(0.95)';
                setTimeout(() => {
                    envelope.style.display = 'none';
                    invitationCard.style.display = 'block';
                    invitationCard.style.opacity = '1';
                    invitationCard.scrollIntoView({ behavior: 'smooth' });
                }, 400);
            }, 300);
        });
    }

    // 2. Music Player Toggle
    const musicBtn = $('[data-mayhen-action="toggle-music"]');
    let isPlaying = false;
    if (musicBtn) {
        on(musicBtn, 'click', () => {
            isPlaying = !isPlaying;
            if (isPlaying) {
                musicBtn.classList.add('mayhen_invitation_music_playing');
                musicBtn.setAttribute('title', 'Tạm dừng nhạc');
            } else {
                musicBtn.classList.remove('mayhen_invitation_music_playing');
                musicBtn.setAttribute('title', 'Bật nhạc nền');
            }
        });
    }

    // 3. Live Countdown Clock
    const daysEl = $('[data-mayhen-countdown="days"]');
    const hoursEl = $('[data-mayhen-countdown="hours"]');
    const minutesEl = $('[data-mayhen-countdown="minutes"]');
    const secondsEl = $('[data-mayhen-countdown="seconds"]');

    if (daysEl && hoursEl && minutesEl && secondsEl) {
        // Set target date: 24 October 2026
        const targetDate = new Date('2026-10-24T10:00:00').getTime();

        const updateClock = () => {
            const now = new Date().getTime();
            const diff = targetDate - now;

            if (diff > 0) {
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                daysEl.textContent = String(days).padStart(2, '0');
                hoursEl.textContent = String(hours).padStart(2, '0');
                minutesEl.textContent = String(minutes).padStart(2, '0');
                secondsEl.textContent = String(seconds).padStart(2, '0');
            }
        };

        updateClock();
        setInterval(updateClock, 1000);
    }

    // 4. RSVP Simulation
    const rsvpForm = $('[data-mayhen-component="rsvp-form"]');
    const rsvpSuccessAlert = $('[data-mayhen-component="rsvp-success"]');

    if (rsvpForm && rsvpSuccessAlert) {
        on(rsvpForm, 'submit', (e) => {
            e.preventDefault();
            rsvpForm.style.display = 'none';
            rsvpSuccessAlert.style.display = 'block';
        });
    }
});
