/**
 * MAYHEN.VN — HOME PAGE INTERACTIVE CONTROLLER
 * Vanilla ES Module
 */
import { $, on } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    const previewStamp = $('.mayhen_home_card_envelope_stamp');
    if (previewStamp) {
        on(previewStamp, 'click', () => {
            previewStamp.style.transform = 'scale(1.2) rotate(10deg)';
            setTimeout(() => {
                previewStamp.style.transform = 'scale(1) rotate(0deg)';
            }, 300);
        });
    }

    // Micro-interactions for trio mockup seals & wax seals
    const seals = document.querySelectorAll('.mayhen_home_mockup_monogram_seal, .mayhen_home_mockup_hysymbol, .mayhen_home_mockup_wax_seal');
    seals.forEach(seal => {
        on(seal, 'click', () => {
            seal.style.transition = 'transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
            seal.style.transform = 'scale(1.25) rotate(12deg)';
            setTimeout(() => {
                seal.style.transform = 'scale(1) rotate(0deg)';
            }, 350);
        });
    });

    // RSVP Button Interactive Click Demo
    const rsvpBtn = $('.mayhen_home_mockup_rsvp_cta');
    if (rsvpBtn) {
        on(rsvpBtn, 'click', (e) => {
            e.stopPropagation();
            const originalText = rsvpBtn.innerHTML;
            rsvpBtn.innerHTML = '<span>✓ Đã Nhận Phản Hồi! ✨</span>';
            rsvpBtn.style.background = '#4A7C59';
            setTimeout(() => {
                rsvpBtn.innerHTML = originalText;
                rsvpBtn.style.background = '';
            }, 2500);
        });
    }

    // Envelope CTA Button Click Demo
    const envCta = $('.mayhen_home_mockup_envelope_cta');
    if (envCta) {
        on(envCta, 'click', (e) => {
            e.stopPropagation();
            envCta.style.transform = 'scale(0.95)';
            setTimeout(() => {
                envCta.style.transform = '';
            }, 200);
        });
    }

    // Initialize 3D Coverflow Invitation Showcase
    initShowcaseCoverflow();

    // Initialize Accessible Editorial FAQ Accordion
    initFaqAccordion();

    // Initialize Real Workflow Step Cards Interactivity & Keyboard Navigation
    initStepsInteractivity();
});

/**
 * 3D Coverflow Invitation Showcase Controller
 * Native Vanilla ES Module — High-Performance GPU Accelerated
 */
/**
 * HAUTE COUTURE 3D COVERFLOW INVITATION SHOWCASE CONTROLLER
 * Native Vanilla ES Modules — Zero External Libraries — 60FPS Hardware Accelerated
 */
function initShowcaseCoverflow() {
    const stage = document.querySelector('.mayhen_home_showcase_stage');
    if (!stage) return;

    const cards = Array.from(stage.querySelectorAll('.mayhen_home_showcase_card'));
    const total = cards.length;
    if (total === 0) return;

    const prevBtn = stage.querySelector('[data-mayhen-showcase-prev]');
    const nextBtn = stage.querySelector('[data-mayhen-showcase-next]');
    const dots = Array.from(document.querySelectorAll('.mayhen_home_showcase_dot'));
    const filterPills = Array.from(document.querySelectorAll('.mayhen_home_showcase_filter_pill'));
    
    // Curator suite elements
    const infoCat = document.querySelector('[data-mayhen-showcase-info-cat]');
    const infoName = document.querySelector('[data-mayhen-showcase-info-name]');
    const infoDesc = document.querySelector('[data-mayhen-showcase-info-desc]');
    const fontBadge = document.querySelector('[data-mayhen-showcase-font-badge]');
    const featureChipsWrap = document.querySelector('[data-mayhen-showcase-features]');
    const previewBtn = document.querySelector('[data-mayhen-showcase-preview-btn]');
    const createBtn = document.querySelector('[data-mayhen-showcase-create-btn]');
    const paletteSwatches = Array.from(document.querySelectorAll('.mayhen_home_showcase_swatch'));

    let currentIndex = 0;
    let autoplayTimer = null;
    let isPaused = false;
    let isTransitioning = false;

    // Check prefers-reduced-motion
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /**
     * Compute circular relative distance from currentIndex
     */
    function getDiff(index, activeIdx, count) {
        let diff = index - activeIdx;
        if (diff > count / 2) diff -= count;
        if (diff < -count / 2) diff += count;
        return diff;
    }

    /**
     * Render 3D transforms for all cards based on viewport and relative position
     */
    function updateStage() {
        const vw = window.innerWidth;
        const isMobile = vw <= 480;
        const isTablet = vw > 480 && vw <= 768;

        cards.forEach((card, i) => {
            const diff = getDiff(i, currentIndex, total);
            const absDiff = Math.abs(diff);
            const sign = diff >= 0 ? 1 : -1;
            const isCenter = diff === 0;

            card.classList.toggle('is-active', isCenter);
            card.setAttribute('aria-hidden', isCenter ? 'false' : 'true');

            let xPercent = 0;
            let rotYDeg = 0;
            let zPx = 0;
            let scale = 1;
            let opacity = 1;

            if (isCenter) {
                // Thẻ chính giữa: Giữ Z = 0px để không bị méo góc nhìn 3D perspective làm lệch hitbox DOM
                xPercent = 0;
                rotYDeg = 0;
                zPx = 0;
                scale = isMobile ? 1.08 : 1.18;
                opacity = 1;
            } else if (absDiff === 1) {
                // Thẻ kề bên 1: Dãn cách để không chạm mép thẻ chính, hơi nghiêng chéo
                xPercent = isMobile ? 64 : (isTablet ? 68 : 74);
                rotYDeg = 34;
                zPx = isMobile ? -65 : -90;
                scale = 0.82;
                opacity = 0.82;
            } else if (absDiff === 2) {
                // Thẻ vị trí 2: Hiển thị chéo sắc nét, nối tiếp mượt mà
                xPercent = isMobile ? 116 : (isTablet ? 126 : 138);
                rotYDeg = 44;
                zPx = isMobile ? -160 : -200;
                scale = 0.70;
                opacity = 0.60;
            } else if (absDiff === 3) {
                // Thẻ vị trí 3: Xếp tầng xa hơn
                xPercent = isMobile ? 158 : (isTablet ? 174 : 194);
                rotYDeg = 50;
                zPx = isMobile ? -240 : -295;
                scale = 0.60;
                opacity = 0.42;
            } else if (absDiff === 4) {
                xPercent = isMobile ? 194 : (isTablet ? 218 : 244);
                rotYDeg = 54;
                zPx = isMobile ? -310 : -380;
                scale = 0.52;
                opacity = 0.30;
            } else {
                const base = isMobile ? 194 : (isTablet ? 218 : 244);
                const step = isMobile ? 32 : 44;
                xPercent = base + (absDiff - 4) * step;
                rotYDeg = Math.min(58, 54 + (absDiff - 4) * 1.2);
                zPx = -Math.min(480, 380 + (absDiff - 4) * 30);
                scale = 0.48;
                opacity = Math.max(0.15, 0.30 - (absDiff - 4) * 0.04);
            }

            // Ẩn các thẻ quá xa trên màn hình điện thoại nhỏ để tránh tràn viền
            if (isMobile && absDiff > 2) {
                opacity = 0;
            }

            const zIndex = 100 - absDiff;
            const transform = `translateX(${sign * xPercent}%) translateZ(${zPx}px) rotateY(${sign * rotYDeg}deg) scale(${scale})`;

            card.style.transform = transform;
            card.style.opacity = opacity;
            card.style.zIndex = zIndex;
            card.style.pointerEvents = (opacity > 0) ? 'auto' : 'none';
        });

        // Update pagination dots
        dots.forEach((dot, idx) => {
            dot.classList.toggle('is-active', idx === currentIndex);
            dot.setAttribute('aria-current', idx === currentIndex ? 'true' : 'false');
        });

        // Update Curator Suite info
        updateCuratorInfo();
    }

    /**
     * Update active card info and feature chips
     */
    function updateCuratorInfo() {
        const activeCard = cards[currentIndex];
        if (!activeCard) return;

        const name = activeCard.getAttribute('data-name') || '';
        const cat = activeCard.getAttribute('data-category') || '';
        const desc = activeCard.getAttribute('data-desc') || '';
        const fontSerif = activeCard.getAttribute('data-font-serif') || 'Playfair Display';
        const createUrl = activeCard.getAttribute('data-create-url') || '#';
        const featuresRaw = activeCard.getAttribute('data-features') || '[]';

        if (infoCat) infoCat.textContent = cat;
        if (infoName) infoName.textContent = name;
        if (infoDesc) infoDesc.textContent = desc;
        if (fontBadge) fontBadge.textContent = `Font: ${fontSerif} × Inter`;
        if (createBtn) createBtn.setAttribute('href', createUrl);

        // Render 4 Feature Chips dynamically
        if (featureChipsWrap) {
            try {
                const features = JSON.parse(featuresRaw);
                featureChipsWrap.innerHTML = features.map(ft => `
                    <span class="mayhen_home_showcase_feature_chip">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>${ft}</span>
                    </span>
                `).join('');
            } catch (err) {
                // fallback if parsing error
            }
        }
    }

    /**
     * Switch to specific card index
     */
    function goTo(index) {
        if (isTransitioning) return;
        isTransitioning = true;
        currentIndex = (index + total) % total;
        updateStage();
        setTimeout(() => {
            isTransitioning = false;
        }, 320);
    }

    function goNext() { goTo(currentIndex + 1); }
    function goPrev() { goTo(currentIndex - 1); }

    // Click on neighbor card brings it to center
    cards.forEach((card, idx) => {
        card.addEventListener('click', (e) => {
            if (idx !== currentIndex) {
                e.preventDefault();
                goTo(idx);
            }
        });
    });

    // Navigation buttons
    if (prevBtn) prevBtn.addEventListener('click', (e) => { e.preventDefault(); goPrev(); });
    if (nextBtn) nextBtn.addEventListener('click', (e) => { e.preventDefault(); goNext(); });

    // Pagination dots
    dots.forEach((dot, idx) => {
        dot.addEventListener('click', (e) => {
            e.preventDefault();
            goTo(idx);
        });
    });

    // Filter Pills interaction
    filterPills.forEach(pill => {
        pill.addEventListener('click', (e) => {
            e.preventDefault();
            filterPills.forEach(p => p.classList.remove('is-active'));
            pill.classList.add('is-active');

            const filter = pill.getAttribute('data-filter');
            if (filter === 'all') {
                goTo(0);
            } else {
                // Find first card matching category slug
                const matchIdx = cards.findIndex(c => c.getAttribute('data-category-slug') === filter);
                if (matchIdx !== -1) {
                    goTo(matchIdx);
                }
            }
        });
    });

    // Color Palette Swatches interaction
    paletteSwatches.forEach(swatch => {
        swatch.addEventListener('click', (e) => {
            e.preventDefault();
            paletteSwatches.forEach(s => s.classList.remove('is-active'));
            swatch.classList.add('is-active');

            const color = swatch.getAttribute('data-color');
            if (color) {
                document.documentElement.style.setProperty('--mayhen-color-primary', color);
                const activeCard = cards[currentIndex];
                if (activeCard) {
                    const dot = activeCard.querySelector('.mayhen_home_showcase_card_dot_accent');
                    if (dot) dot.style.backgroundColor = color;
                }
            }
        });
    });

    // Interactive Wax Seal Monogram Click Bounce
    cards.forEach(card => {
        const waxWrap = card.querySelector('.mayhen_home_showcase_card_wax_wrap');
        if (waxWrap) {
            waxWrap.addEventListener('click', (e) => {
                e.stopPropagation();
                const stamp = waxWrap.querySelector('.mayhen_home_showcase_card_wax_stamp');
                if (stamp) {
                    stamp.style.transform = 'scale(1.3) rotate(18deg)';
                    stamp.style.boxShadow = '0 0 20px rgba(197, 168, 128, 0.8)';
                    setTimeout(() => {
                        stamp.style.transform = '';
                        stamp.style.boxShadow = '';
                    }, 400);
                }
                // Also trigger live preview modal if active card
                if (card.classList.contains('is-active')) {
                    openLivePreviewModal(card);
                }
            });
        }
    });

    // Peek Button (Xem Mẫu) & Quick action trigger modal
    cards.forEach(card => {
        const peekBtn = card.querySelector('[data-mayhen-showcase-peek-btn]');
        if (peekBtn) {
            peekBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                openLivePreviewModal(card);
            });
        }
        const quickAction = card.querySelector('.mayhen_home_showcase_card_quick_action');
        if (quickAction) {
            quickAction.addEventListener('click', (e) => {
                e.stopPropagation();
                openLivePreviewModal(card);
            });
        }
    });

    if (previewBtn) {
        previewBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const activeCard = cards[currentIndex];
            if (activeCard) openLivePreviewModal(activeCard);
        });
    }

    // Interactive Holographic Sheen on Active Card (giữ nguyên phẳng Z=0 để tuyệt đối không lệch DOM)
    stage.addEventListener('mousemove', (e) => {
        if (prefersReducedMotion || window.innerWidth < 768) return;
        const activeCard = cards[currentIndex];
        if (!activeCard) return;

        // Giữ thẻ luôn phẳng tuyệt đối ở Z = 0px để DOM và pixel hiển thị trùng khớp 100%
        activeCard.style.transform = 'translateX(0%) translateZ(0px) rotateX(0deg) rotateY(0deg) scale(1.18)';

        const rect = activeCard.getBoundingClientRect();
        const cardX = e.clientX - rect.left;
        const cardY = e.clientY - rect.top;

        if (cardX >= 0 && cardX <= rect.width && cardY >= 0 && cardY <= rect.height) {
            const sheen = activeCard.querySelector('.mayhen_home_showcase_card_sheen');
            if (sheen) {
                sheen.style.opacity = '0.6';
                sheen.style.backgroundPosition = `${(cardX / rect.width) * 100}% ${(cardY / rect.height) * 100}%`;
            }
        }
    });

    stage.addEventListener('mouseleave', () => {
        const activeCard = cards[currentIndex];
        if (activeCard) {
            activeCard.style.transform = 'translateX(0%) translateZ(0px) rotateX(0deg) rotateY(0deg) scale(1.18)';
            const sheen = activeCard.querySelector('.mayhen_home_showcase_card_sheen');
            if (sheen) sheen.style.opacity = '0';
        }
    });

    // Touch Swipe
    let touchStartX = 0;
    let touchStartY = 0;
    let isSwiping = false;

    stage.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            isSwiping = true;
            pauseAutoplay();
        }
    }, { passive: true });

    stage.addEventListener('touchend', (e) => {
        if (!isSwiping) return;
        isSwiping = false;
        const deltaX = e.changedTouches[0].clientX - touchStartX;
        const deltaY = e.changedTouches[0].clientY - touchStartY;

        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 42) {
            if (deltaX < 0) goNext();
            else goPrev();
        }
        resumeAutoplay();
    }, { passive: true });

    // Mouse Drag
    let mouseStartX = 0;
    let isDragging = false;

    stage.addEventListener('mousedown', (e) => {
        if (e.button !== 0) return;
        mouseStartX = e.clientX;
        isDragging = true;
        pauseAutoplay();
    });

    window.addEventListener('mouseup', (e) => {
        if (!isDragging) return;
        isDragging = false;
        const deltaX = e.clientX - mouseStartX;
        if (Math.abs(deltaX) > 48) {
            if (deltaX < 0) goNext();
            else goPrev();
        }
        resumeAutoplay();
    });

    // Keyboard navigation
    window.addEventListener('keydown', (e) => {
        const modal = document.getElementById('mayhen_home_showcase_modal');
        if (modal && modal.classList.contains('is-open')) {
            if (e.key === 'Escape') closeLivePreviewModal();
            return;
        }

        const showcaseRect = stage.getBoundingClientRect();
        if (showcaseRect.top < window.innerHeight && showcaseRect.bottom > 0) {
            if (e.key === 'ArrowLeft') { e.preventDefault(); goPrev(); }
            else if (e.key === 'ArrowRight') { e.preventDefault(); goNext(); }
        }
    });

    // Helper: CHỈ kiểm tra xem chuột có đang nằm trên bất kỳ thẻ mayhen_home_showcase_card hay không
    function isHoveringCard() {
        try {
            return cards.some(c => c.matches(':hover'));
        } catch {
            return false;
        }
    }

    // Autoplay Engine: Tự động chuyển mẫu sau mỗi 2s, CHỈ DỪNG khi rê chuột vào thẻ
    function startAutoplay() {
        stopAutoplay();
        autoplayTimer = setInterval(() => {
            if (isHoveringCard()) {
                return;
            }
            goNext();
        }, 2000);
    }

    function stopAutoplay() {
        if (autoplayTimer) {
            clearInterval(autoplayTimer);
            autoplayTimer = null;
        }
    }

    function pauseAutoplay() {
        stopAutoplay();
    }

    function resumeAutoplay() {
        if (isHoveringCard()) return;
        startAutoplay();
    }

    // CHỈ lắng nghe hover trực tiếp trên các thẻ .mayhen_home_showcase_card
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            pauseAutoplay();
        });
        card.addEventListener('mouseleave', () => {
            setTimeout(() => {
                if (!isHoveringCard()) {
                    startAutoplay();
                }
            }, 60);
        });
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            pauseAutoplay();
        } else if (!isHoveringCard()) {
            startAutoplay();
        }
    });

    // Resize Debouncer
    let resizeTimer = null;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => { updateStage(); }, 120);
    });

    // Initialize Subsystems
    initPetalsCanvas();
    initRomanticAudio();
    initLivePreviewModalEvents();
    initCardImageHoverScroll();

    // Start
    updateStage();
    startAutoplay();
}

/**
 * Tính toán độ cuộn của ảnh đại diện mẫu thiệp khi hover
 */
function initCardImageHoverScroll() {
    const images = document.querySelectorAll('.mayhen_home_showcase_card_img');
    images.forEach(img => {
        function calculateDelta() {
            const media = img.closest('.mayhen_home_showcase_card_media');
            if (!media) return;
            const mediaHeight = media.clientHeight;
            const imgHeight = img.clientHeight;
            if (imgHeight > mediaHeight) {
                const delta = imgHeight - mediaHeight;
                img.style.setProperty('--mayhen-scroll-delta', `-${delta}px`);
                const duration = Math.min(12, Math.max(6.5, (delta / 100) * 1.8));
                img.style.setProperty('--mayhen-scroll-duration', `${duration}s`);
            }
        }

        if (img.complete && img.naturalHeight > 0) {
            calculateDelta();
        } else {
            img.addEventListener('load', calculateDelta);
        }
    });

    window.addEventListener('resize', () => {
        images.forEach(img => {
            const media = img.closest('.mayhen_home_showcase_card_media');
            if (!media) return;
            const delta = img.clientHeight - media.clientHeight;
            if (delta > 0) {
                img.style.setProperty('--mayhen-scroll-delta', `-${delta}px`);
            }
        });
    });
}

/**
 * Romantic Floating Petals Canvas Particle Engine
 */
function initPetalsCanvas() {
    const canvas = document.getElementById('mayhen_home_showcase_canvas_petals');
    const toggleBtn = document.getElementById('mayhen_home_showcase_petals_btn');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width = canvas.width = canvas.offsetWidth;
    let height = canvas.height = canvas.offsetHeight;
    let isActive = true;
    let animationId = null;

    const petals = [];
    const petalColors = [
        'rgba(244, 219, 216, 0.75)', // Dusty Rose
        'rgba(247, 237, 226, 0.8)',  // Warm Ivory
        'rgba(224, 187, 187, 0.7)',  // Soft Rose
        'rgba(216, 226, 220, 0.65)'  // Sage Tint
    ];

    for (let i = 0; i < 24; i++) {
        petals.push({
            x: Math.random() * width,
            y: Math.random() * height,
            size: 6 + Math.random() * 8,
            speedY: 0.6 + Math.random() * 0.9,
            speedX: -0.4 + Math.random() * 0.8,
            angle: Math.random() * Math.PI * 2,
            angularSpeed: (Math.random() - 0.5) * 0.02,
            color: petalColors[Math.floor(Math.random() * petalColors.length)]
        });
    }

    function renderPetals() {
        if (!isActive) return;
        ctx.clearRect(0, 0, width, height);

        petals.forEach(p => {
            p.y += p.speedY;
            p.x += p.speedX;
            p.angle += p.angularSpeed;

            if (p.y > height + 20) { p.y = -20; p.x = Math.random() * width; }
            if (p.x > width + 20) p.x = -20;
            if (p.x < -20) p.x = width + 20;

            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(p.angle);
            ctx.fillStyle = p.color;
            ctx.beginPath();
            ctx.ellipse(0, 0, p.size, p.size * 0.55, 0, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        });

        animationId = requestAnimationFrame(renderPetals);
    }

    window.addEventListener('resize', () => {
        width = canvas.width = canvas.offsetWidth;
        height = canvas.height = canvas.offsetHeight;
    });

    renderPetals();

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            isActive = !isActive;
            toggleBtn.classList.toggle('is-active', isActive);
            canvas.classList.toggle('is-hidden', !isActive);
            const textSpan = toggleBtn.querySelector('.mayhen_home_showcase_petals_text');
            if (textSpan) {
                textSpan.textContent = isActive ? 'Cánh Hoa Bay: Đang Bật' : 'Cánh Hoa Bay: Đã Tắt';
            }
            if (isActive) renderPetals();
            else if (animationId) cancelAnimationFrame(animationId);
        });
    }
}

/**
 * Romantic Acoustic Piano Synthesizer via Web Audio API
 * Plays gentle chord progression of "Canon in D" without external audio files
 */
function initRomanticAudio() {
    const audioBtn = document.getElementById('mayhen_home_showcase_audio_btn');
    if (!audioBtn) return;

    let audioCtx = null;
    let isPlaying = false;
    let chordTimer = null;

    // Chord frequencies in Hz: D -> A -> Bm -> F#m -> G -> D -> G -> A
    const chords = [
        [293.66, 369.99, 440.00], // D major
        [220.00, 277.18, 329.63], // A major
        [246.94, 293.66, 369.99], // B minor
        [185.00, 220.00, 277.18], // F# minor
        [196.00, 246.94, 293.66], // G major
        [220.00, 293.66, 369.99], // D major
        [196.00, 246.94, 293.66], // G major
        [220.00, 277.18, 329.63]  // A major
    ];
    let chordIndex = 0;

    function playNote(freq, startTime, duration = 1.4) {
        if (!audioCtx) return;
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();

        osc.type = 'triangle'; // warm piano acoustic timbre
        osc.frequency.setValueAtTime(freq, startTime);

        gain.gain.setValueAtTime(0.0001, startTime);
        gain.gain.exponentialRampToValueAtTime(0.045, startTime + 0.08); // attack
        gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration); // decay

        osc.connect(gain);
        gain.connect(audioCtx.destination);

        osc.start(startTime);
        osc.stop(startTime + duration);
    }

    function playNextChord() {
        if (!isPlaying || !audioCtx) return;
        const chord = chords[chordIndex];
        const now = audioCtx.currentTime;

        // Arpeggiate chord notes
        chord.forEach((note, nIdx) => {
            playNote(note, now + nIdx * 0.16, 1.6);
        });

        chordIndex = (chordIndex + 1) % chords.length;
        chordTimer = setTimeout(playNextChord, 1600);
    }

    audioBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (!isPlaying) {
            if (!audioCtx) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                audioCtx = new AudioContextClass();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            isPlaying = true;
            audioBtn.classList.add('is-playing');
            const textSpan = audioBtn.querySelector('.mayhen_home_showcase_audio_text');
            if (textSpan) textSpan.textContent = 'Đang Phát: Canon in D (Piano)';
            playNextChord();
        } else {
            isPlaying = false;
            audioBtn.classList.remove('is-playing');
            const textSpan = audioBtn.querySelector('.mayhen_home_showcase_audio_text');
            if (textSpan) textSpan.textContent = 'Nhạc Lễ Cưới: A Thousand Years';
            if (chordTimer) clearTimeout(chordTimer);
        }
    });
}

/**
 * Instant Live Preview Modal Drawer (ChungDoi-inspired Phone & Desktop Experience)
 */
function openLivePreviewModal(card) {
    const modal = document.getElementById('mayhen_home_showcase_modal');
    if (!modal) return;

    const previewUrl = card.getAttribute('data-preview-url') || '';
    const name = card.getAttribute('data-name') || 'Mẫu Thiệp Cưới';
    const cat = card.getAttribute('data-category') || 'MẪU THIỆP CƯỚI';
    const createUrl = card.getAttribute('data-create-url') || '#';

    const titleEl = document.getElementById('mayhen_home_showcase_modal_title');
    const catEl = document.getElementById('mayhen_home_showcase_modal_bottom_cat');
    const iframe = document.getElementById('mayhen_home_showcase_modal_iframe');
    const newtabLink = document.getElementById('mayhen_home_showcase_modal_newtab_link');
    const createLink = document.getElementById('mayhen_home_showcase_modal_create_link');

    if (titleEl) titleEl.textContent = name;
    if (catEl) catEl.textContent = cat;
    if (iframe && previewUrl) iframe.src = previewUrl;
    if (newtabLink) newtabLink.href = previewUrl;
    if (createLink) createLink.href = createUrl;

    modal.setAttribute('data-current-preview-url', previewUrl);
    modal.classList.add('is-open');
    document.body.style.overflow = 'hidden';
}

function closeLivePreviewModal() {
    const modal = document.getElementById('mayhen_home_showcase_modal');
    if (!modal) return;
    modal.classList.remove('is-open');
    document.body.style.overflow = '';
    const iframe = document.getElementById('mayhen_home_showcase_modal_iframe');
    if (iframe) iframe.src = 'about:blank';
}

function initLivePreviewModalEvents() {
    const modal = document.getElementById('mayhen_home_showcase_modal');
    if (!modal) return;

    // Close buttons and backdrop click
    const closeTriggers = document.querySelectorAll('[data-mayhen-modal-close]');
    closeTriggers.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeLivePreviewModal();
        });
    });

    // Escape key closes modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeLivePreviewModal();
        }
    });

    // Device View Mode Toggles (Mobile vs Desktop)
    const deviceBtns = document.querySelectorAll('[data-mayhen-device-mode]');
    deviceBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const mode = btn.getAttribute('data-mayhen-device-mode');
            deviceBtns.forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            if (mode === 'desktop') {
                modal.classList.add('is-desktop-mode');
            } else {
                modal.classList.remove('is-desktop-mode');
            }
        });
    });

    // Copy Link with Toast notification
    const copyBtn = document.getElementById('mayhen_home_showcase_modal_copy_btn');
    const toast = document.getElementById('mayhen_home_showcase_modal_toast');
    let toastTimer = null;
    if (copyBtn) {
        copyBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const relUrl = modal.getAttribute('data-current-preview-url') || '';
            const fullUrl = relUrl.startsWith('http') ? relUrl : (window.location.origin + relUrl);
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(fullUrl);
                } else {
                    const temp = document.createElement('input');
                    temp.value = fullUrl;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                }
                if (toast) {
                    toast.classList.add('show');
                    if (toastTimer) clearTimeout(toastTimer);
                    toastTimer = setTimeout(() => {
                        toast.classList.remove('show');
                    }, 2400);
                }
            } catch (err) {
                // copy fallback
            }
        });
    }

    // Reload iframe button
    const reloadBtn = document.getElementById('mayhen_home_showcase_modal_reload_btn');
    if (reloadBtn) {
        reloadBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const iframe = document.getElementById('mayhen_home_showcase_modal_iframe');
            if (iframe && iframe.src && iframe.src !== 'about:blank') {
                const currentSrc = iframe.src;
                iframe.src = 'about:blank';
                setTimeout(() => {
                    iframe.src = currentSrc;
                }, 60);
            }
        });
    }
}

/**
 * ========================================================
 * 3. ACCESSIBLE FAQ ACCORDION CONTROLLER
 * Style: Quiet Luxury, Smooth CSS-Grid Motion
 * WAI-ARIA Accordion Pattern (Click + Full Keyboard Support)
 * ========================================================
 */
function initFaqAccordion() {
    const wrap = document.querySelector('[data-mayhen-component="faq-accordion"]');
    if (!wrap) return;

    const items = Array.from(wrap.querySelectorAll('[data-mayhen-faq-item]'));
    const triggers = Array.from(wrap.querySelectorAll('.mayhen_home_faq_trigger'));

    triggers.forEach((trigger, idx) => {
        // Toggle on Click
        trigger.addEventListener('click', () => {
            const currentItem = items[idx];
            if (!currentItem) return;

            const isCurrentlyOpen = currentItem.classList.contains('is-open');

            // Close other items for a clean, focused reading experience
            items.forEach((item, i) => {
                const btn = triggers[i];
                item.classList.remove('is-open');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            });

            // Toggle current item
            if (!isCurrentlyOpen) {
                currentItem.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });

        // WAI-ARIA Keyboard Navigation
        trigger.addEventListener('keydown', (e) => {
            let targetIdx = null;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                targetIdx = (idx + 1) % triggers.length;
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                targetIdx = (idx - 1 + triggers.length) % triggers.length;
            } else if (e.key === 'Home') {
                e.preventDefault();
                targetIdx = 0;
            } else if (e.key === 'End') {
                e.preventDefault();
                targetIdx = triggers.length - 1;
            }

            if (targetIdx !== null && triggers[targetIdx]) {
                triggers[targetIdx].focus();
            }
        });
    });

    // Interactive Wedding Postcard Wax Seal Burst
    const sealBtn = document.getElementById('mayhen_home_faq_seal_btn');
    const sealText = document.getElementById('mayhen_home_faq_seal_text');
    const burstsWrap = document.getElementById('mayhen_home_faq_bursts');

    if (sealBtn && burstsWrap) {
        const blessingIcons = ['🌸', '💖', '✨', '💍', '🕊️', '💐', '囍'];
        let resetTimer = null;

        sealBtn.addEventListener('click', (e) => {
            e.preventDefault();

            // Seal bounce animation
            sealBtn.style.transform = 'scale(1.22) rotate(14deg)';
            setTimeout(() => {
                sealBtn.style.transform = '';
            }, 350);

            // Change label temporarily
            if (sealText) {
                const originalText = 'Chạm con dấu sáp 💌';
                sealText.textContent = 'Trăm năm hạnh phúc! 🌸✨';
                sealText.style.color = '#B59569';
                if (resetTimer) clearTimeout(resetTimer);
                resetTimer = setTimeout(() => {
                    sealText.textContent = originalText;
                    sealText.style.color = '';
                }, 3000);
            }

            // Spawn lovely floating burst icons
            const rect = sealBtn.getBoundingClientRect();
            const parentRect = burstsWrap.getBoundingClientRect();
            const originX = rect.left - parentRect.left + rect.width / 2;
            const originY = rect.top - parentRect.top + rect.height / 2;

            for (let i = 0; i < 7; i++) {
                const burstEl = document.createElement('span');
                burstEl.className = 'mayhen_home_faq_burst_item';
                burstEl.textContent = blessingIcons[Math.floor(Math.random() * blessingIcons.length)];

                const dx = (Math.random() - 0.5) * 120;
                const dy = -(40 + Math.random() * 80);
                const rot = (Math.random() - 0.5) * 60;
                const size = 16 + Math.random() * 10;

                burstEl.style.left = `${originX}px`;
                burstEl.style.top = `${originY}px`;
                burstEl.style.fontSize = `${size}px`;
                burstEl.style.setProperty('--burst-dx', `${dx}px`);
                burstEl.style.setProperty('--burst-dy', `${dy}px`);
                burstEl.style.setProperty('--burst-rot', `${rot}deg`);

                burstsWrap.appendChild(burstEl);

                setTimeout(() => {
                    if (burstEl.parentNode) {
                        burstEl.parentNode.removeChild(burstEl);
                    }
                }, 1400);
            }
        });
    }
}

/**
 * REAL WORKFLOW STEP CARDS INTERACTIVITY
 * Keyboard navigation (Arrow keys, Home, End) & Gentle micro-interactions
 * Fully honors prefers-reduced-motion
 */
function initStepsInteractivity() {
    const cards = Array.from(document.querySelectorAll('.mayhen_home_steps_card'));
    if (!cards.length) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Keyboard navigation across cards
    cards.forEach((card, index) => {
        card.addEventListener('keydown', (e) => {
            let targetIndex = -1;

            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                targetIndex = (index + 1) % cards.length;
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                targetIndex = (index - 1 + cards.length) % cards.length;
            } else if (e.key === 'Home') {
                targetIndex = 0;
            } else if (e.key === 'End') {
                targetIndex = cards.length - 1;
            }

            if (targetIndex !== -1) {
                e.preventDefault();
                cards[targetIndex].focus();
            }
        });

        // Touch feedback on mobile
        card.addEventListener('touchstart', () => {
            card.classList.add('mayhen_home_steps_card_touch');
        }, { passive: true });

        card.addEventListener('touchend', () => {
            setTimeout(() => {
                card.classList.remove('mayhen_home_steps_card_touch');
            }, 300);
        }, { passive: true });
    });

    if (prefersReducedMotion) return;

    // Step 01: Mini wax seal click/tap interaction
    const miniSeal = document.querySelector('.mayhen_home_steps_visual_seal');
    if (miniSeal) {
        miniSeal.style.cursor = 'pointer';
        miniSeal.addEventListener('click', (e) => {
            e.stopPropagation();
            miniSeal.style.transition = 'transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1)';
            miniSeal.style.transform = 'scale(1.25) rotate(15deg)';
            setTimeout(() => {
                miniSeal.style.transform = 'scale(1) rotate(0deg)';
            }, 350);
        });
    }

    // Step 03: Mini QR box scan-line pulse
    const qrBox = document.querySelector('.mayhen_home_steps_visual_qr_box');
    const scanLine = document.querySelector('.mayhen_home_steps_visual_qr_scan_line');
    if (qrBox && scanLine) {
        qrBox.style.cursor = 'pointer';
        qrBox.addEventListener('click', (e) => {
            e.stopPropagation();
            scanLine.style.transition = 'top 0.4s ease-in-out';
            scanLine.style.top = '10%';
            setTimeout(() => {
                scanLine.style.top = '85%';
                setTimeout(() => {
                    scanLine.style.top = '50%';
                }, 300);
            }, 400);
        });
    }
}

