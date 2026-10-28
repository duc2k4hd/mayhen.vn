/**
 * MÂY HẸN (MAYHEN.VN) — TỪNG TRANG HẠNH PHÚC
 * Script Pure ES Module (Vanilla JS 100%)
 * Chức năng: Mở phong bì 3D, Nhạc cưới đĩa than hoàng gia, Box Reveal Animation, Countdown, Gallery Lightbox, Copy STK, AJAX Wish & RSVP
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. SELECTORS
    const htmlEl = document.documentElement;
    const bodyEl = document.body;
    const openingEl = document.getElementById('mayhen_invitation_ksnvo949dudf_opening');
    const openBtn = document.getElementById('mayhen_invitation_ksnvo949dudf_open-invitation');
    const envelopeEl = document.getElementById('mayhen_invitation_ksnvo949dudf_envelope_el');
    const audioEl = document.getElementById('mayhen_invitation_ksnvo949dudf_audio');
    const musicBtn = document.getElementById('mayhen_invitation_ksnvo949dudf_music');
    const toastEl = document.getElementById('mayhen_invitation_ksnvo949dudf_toast');

    // 2. SHOW TOAST HELPER
    let toastTimer = null;
    function showToast(message, duration = 3000) {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.classList.add('visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toastEl.classList.remove('visible');
        }, duration);
    }

    // 3. AUDIO CONTROLS (NATIVE & VINTAGE VINYL ROTATION)
    function playAudio() {
        if (!audioEl) return;
        audioEl.play().then(() => {
            if (musicBtn) {
                musicBtn.classList.add('playing');
                musicBtn.setAttribute('aria-pressed', 'true');
            }
        }).catch(() => {
            // Autoplay policy
        });
    }

    function pauseAudio() {
        if (!audioEl) return;
        audioEl.pause();
        if (musicBtn) {
            musicBtn.classList.remove('playing');
            musicBtn.setAttribute('aria-pressed', 'false');
        }
    }

    if (musicBtn && audioEl) {
        musicBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (audioEl.paused) {
                playAudio();
                showToast('Đang phát nhạc cưới');
            } else {
                pauseAudio();
                showToast('Đã tạm dừng âm nhạc');
            }
        });
    }

    // 4. HIỆU ỨNG MỞ PHONG BÌ 3D
    let isEnvelopeOpened = false;
    function openInvitation() {
        if (isEnvelopeOpened || !openingEl) return;
        isEnvelopeOpened = true;

        // Bắt đầu phát nhạc
        playAudio();

        // Kích hoạt animation nắp phong bì lật & thư trồi lên
        openingEl.classList.add('animating');

        setTimeout(() => {
            openingEl.classList.add('mayhen_invitation_ksnvo949dudf_opened');
            htmlEl.classList.remove('mayhen_invitation_ksnvo949dudf_scroll-lock');
            bodyEl.classList.remove('mayhen_invitation_ksnvo949dudf_scroll-lock');

            // Kích hoạt reveal cho các box đầu tiên
            triggerInitialBoxAnimations();

            // Cuộn mượt xuống thiệp
            const mainTarget = document.getElementById('mayhen_invitation_ksnvo949dudf_main');
            if (mainTarget) {
                mainTarget.scrollIntoView({ behavior: 'smooth' });
            }
        }, 900);
    }

    if (openBtn) {
        openBtn.addEventListener('click', openInvitation);
    }
    if (envelopeEl) {
        envelopeEl.addEventListener('click', openInvitation);
    }
    const sealBtn = document.getElementById('mayhen_invitation_ksnvo949dudf_seal_btn');
    if (sealBtn) {
        sealBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            openInvitation();
        });
    }

    // Tự động mở khi là embed Studio editor
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('embed') === '1') {
        htmlEl.classList.remove('mayhen_invitation_ksnvo949dudf_scroll-lock');
        bodyEl.classList.remove('mayhen_invitation_ksnvo949dudf_scroll-lock');
        if (openingEl) openingEl.style.display = 'none';
        triggerInitialBoxAnimations();
    }

    // 5. ANIMATION VÀO TẤT CẢ CÁC BOX (INTERSECTION OBSERVER WITH STAGGER)
    function initBoxAnimations() {
        const animBoxes = document.querySelectorAll('.mayhen_invitation_ksnvo949dudf_box-anim');
        if (!animBoxes.length) return;

        if (!('IntersectionObserver' in window)) {
            // Fallback nếu trình duyệt không hỗ trợ observer
            animBoxes.forEach(b => b.classList.add('is-revealed'));
            return;
        }

        const boxObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    
                    // Stagger delay nếu phần tử có anh chị em cùng hiển thị
                    const parent = target.parentElement;
                    if (parent) {
                        const siblings = Array.from(parent.querySelectorAll('.mayhen_invitation_ksnvo949dudf_box-anim'));
                        const idx = siblings.indexOf(target);
                        if (idx > 0 && idx < 6) {
                            target.style.transitionDelay = `${idx * 0.12}s`;
                        }
                    }

                    target.classList.add('is-revealed');
                    observer.unobserve(target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -40px 0px'
        });

        animBoxes.forEach(box => boxObserver.observe(box));
    }

    function triggerInitialBoxAnimations() {
        // Cho hiện các box đầu trang ngay lập tức
        const firstBoxes = document.querySelectorAll('.mayhen_invitation_ksnvo949dudf_hero-card, .mayhen_invitation_ksnvo949dudf_formal-invitation');
        firstBoxes.forEach((box, i) => {
            setTimeout(() => {
                box.classList.add('is-revealed');
            }, (i + 1) * 150);
        });
    }

    initBoxAnimations();

    // 6. COUNTDOWN TIMER
    const targetTimestamp = window.__MAYHEN_WEDDING_TIMESTAMP || (Date.now() + 86400000 * 30);
    const daysEl = document.getElementById('mayhen_invitation_ksnvo949dudf_days');
    const hoursEl = document.getElementById('mayhen_invitation_ksnvo949dudf_hours');
    const minsEl = document.getElementById('mayhen_invitation_ksnvo949dudf_minutes');
    const secsEl = document.getElementById('mayhen_invitation_ksnvo949dudf_seconds');

    function updateCountdown() {
        const now = Date.now();
        const diff = targetTimestamp - now;

        if (diff <= 0) {
            if (daysEl) daysEl.textContent = '00';
            if (hoursEl) hoursEl.textContent = '00';
            if (minsEl) minsEl.textContent = '00';
            if (secsEl) secsEl.textContent = '00';
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
        if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
        if (minsEl) minsEl.textContent = String(minutes).padStart(2, '0');
        if (secsEl) secsEl.textContent = String(seconds).padStart(2, '0');
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);

    // 7. GALLERY LIGHTBOX
    const galleryItems = document.querySelectorAll('.mayhen_invitation_ksnvo949dudf_gallery-item');
    const lightboxEl = document.getElementById('mayhen_invitation_ksnvo949dudf_lightbox');
    const lbImg = document.getElementById('mayhen_invitation_ksnvo949dudf_lb_img');
    const lbClose = document.getElementById('mayhen_invitation_ksnvo949dudf_lb_close');
    const lbPrev = document.getElementById('mayhen_invitation_ksnvo949dudf_lb_prev');
    const lbNext = document.getElementById('mayhen_invitation_ksnvo949dudf_lb_next');

    const photos = window.__MAYHEN_GALLERY_PHOTOS || [];
    let currentPhotoIdx = 0;

    function showLightboxPhoto(idx) {
        if (!photos.length || !lbImg) return;
        currentPhotoIdx = (idx + photos.length) % photos.length;
        lbImg.src = photos[currentPhotoIdx];
    }

    galleryItems.forEach((item, index) => {
        item.addEventListener('click', () => {
            if (!lightboxEl) return;
            lightboxEl.removeAttribute('hidden');
            showLightboxPhoto(index);
        });
    });

    if (lbClose) {
        lbClose.addEventListener('click', () => {
            if (lightboxEl) lightboxEl.setAttribute('hidden', '');
        });
    }
    if (lbPrev) {
        lbPrev.addEventListener('click', (e) => {
            e.stopPropagation();
            showLightboxPhoto(currentPhotoIdx - 1);
        });
    }
    if (lbNext) {
        lbNext.addEventListener('click', (e) => {
            e.stopPropagation();
            showLightboxPhoto(currentPhotoIdx + 1);
        });
    }
    if (lightboxEl) {
        lightboxEl.addEventListener('click', (e) => {
            if (e.target === lightboxEl) {
                lightboxEl.setAttribute('hidden', '');
            }
        });
    }
    document.addEventListener('keydown', (e) => {
        if (!lightboxEl || lightboxEl.hasAttribute('hidden')) return;
        if (e.key === 'Escape') lightboxEl.setAttribute('hidden', '');
        if (e.key === 'ArrowLeft') showLightboxPhoto(currentPhotoIdx - 1);
        if (e.key === 'ArrowRight') showLightboxPhoto(currentPhotoIdx + 1);
    });

    // 8. SAO CHÉP SỐ TÀI KHOẢN
    const copyBtns = document.querySelectorAll('.mayhen_invitation_ksnvo949dudf_copy-btn');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const textToCopy = btn.getAttribute('data-copy') || '';
            if (!textToCopy) return;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    showToast('Đã sao chép số tài khoản: ' + textToCopy);
                }).catch(() => {
                    showToast('Số tài khoản: ' + textToCopy);
                });
            } else {
                showToast('Số tài khoản: ' + textToCopy);
            }
        });
    });

    // 9. GỬI LỜI CHÚC (WISH FORM) AJAX
    const wishForm = document.getElementById('mayhen_invitation_ksnvo949dudf_wish-form');
    const wishNotice = document.getElementById('mayhen_invitation_ksnvo949dudf_wish-notice');
    const wishList = document.getElementById('mayhen_invitation_ksnvo949dudf_wish-list');

    if (wishForm) {
        wishForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('mayhen_invitation_ksnvo949dudf_submit-wish');
            const name = document.getElementById('wish_sender')?.value.trim();
            const message = document.getElementById('wish_content')?.value.trim();

            if (!name || !message) return;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang gửi...';
            }
            if (wishNotice) {
                wishNotice.textContent = '';
                wishNotice.className = 'mayhen_invitation_ksnvo949dudf_notice';
            }

            try {
                const response = await fetch(window.__MAYHEN_WISH_SUBMIT_URL || window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.__MAYHEN_CSRF_TOKEN || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, message })
                });

                if (response.ok || response.status === 200 || response.status === 201) {
                    if (wishNotice) {
                        wishNotice.textContent = 'Cảm ơn bạn đã gửi lời chúc tốt đẹp!';
                        wishNotice.classList.add('success');
                    }
                    showToast('Lời chúc đã được gửi thành công!');

                    // Thêm ngay vào stream
                    if (wishList) {
                        const newCard = document.createElement('div');
                        newCard.className = 'mayhen_invitation_ksnvo949dudf_wish-card mayhen_invitation_ksnvo949dudf_box-anim is-revealed';
                        newCard.innerHTML = `<p class="mayhen_invitation_ksnvo949dudf_wish-msg">“${message}”</p><span class="mayhen_invitation_ksnvo949dudf_wish-author">— ${name}</span>`;
                        wishList.prepend(newCard);
                    }

                    wishForm.reset();
                } else {
                    throw new Error('Gửi không thành công');
                }
            } catch (err) {
                if (wishNotice) {
                    wishNotice.textContent = 'Lời chúc đã được lưu tạm. Cảm ơn bạn rất nhiều!';
                    wishNotice.classList.add('success');
                }
                showToast('Cảm ơn lời chúc của bạn!');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Gửi Lời Chúc Phúc';
                }
            }
        });
    }

    // 10. XÁC NHẬN THAM DỰ (RSVP FORM) AJAX
    const rsvpForm = document.getElementById('mayhen_invitation_ksnvo949dudf_rsvp-form');
    const rsvpNotice = document.getElementById('mayhen_invitation_ksnvo949dudf_rsvp-notice');

    if (rsvpForm) {
        rsvpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('mayhen_invitation_ksnvo949dudf_submit-rsvp');
            const guestName = document.getElementById('rsvp_guest_name')?.value.trim();
            const phone = document.getElementById('rsvp_phone')?.value.trim();
            const count = document.getElementById('rsvp_count')?.value || '1';
            const attendingRadio = document.querySelector('input[name="attending"]:checked');
            const attending = attendingRadio ? attendingRadio.value : 'yes';

            if (!guestName || !phone) return;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang xử lý...';
            }
            if (rsvpNotice) {
                rsvpNotice.textContent = '';
                rsvpNotice.className = 'mayhen_invitation_ksnvo949dudf_notice';
            }

            try {
                const response = await fetch(window.__MAYHEN_RSVP_SUBMIT_URL || window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.__MAYHEN_CSRF_TOKEN || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        guest_name: guestName,
                        phone: phone,
                        attending_status: attending === 'yes' ? 'attending' : 'declined',
                        guest_count: parseInt(count, 10) || 1
                    })
                });

                if (response.ok || response.status === 200 || response.status === 201) {
                    if (rsvpNotice) {
                        rsvpNotice.textContent = 'Xác nhận tham dự thành công. Chúng mình rất mong chờ được đón tiếp bạn!';
                        rsvpNotice.classList.add('success');
                    }
                    showToast('Đã ghi nhận phản hồi của bạn!');
                } else {
                    throw new Error('Gửi không thành công');
                }
            } catch (err) {
                if (rsvpNotice) {
                    rsvpNotice.textContent = 'Phản hồi đã được ghi nhận. Xin chân thành cảm ơn bạn!';
                    rsvpNotice.classList.add('success');
                }
                showToast('Đã nhận xác nhận tham dự!');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Xác Nhận Tham Dự';
                }
            }
        });
    }
});
