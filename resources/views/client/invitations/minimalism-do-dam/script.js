/**
 * MÂY HẸN (MAYHEN.VN) — TEMPLATE: minimalism-do-dam
 * 100% Pure Vanilla Native JavaScript • Zero External Libraries
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initEnvelopeOpening();
        initCountdown();
        init3DGalleryCoverflow();
        initLightbox();
        initCopyAccount();
        initSignatureCanvas();
        initRsvpAndWishes();
        initEditorBridgeHandshake();
    });

    /**
     * 1. Hiệu ứng Mở Phong Bì Hoàng Gia (Web Audio Chime, Particle Burst & Envelope Fly-away)
     */
    function initEnvelopeOpening() {
        var envelope = document.getElementById('invite-envelope');
        var btn = document.getElementById('invite-open-btn');
        if (!envelope || !btn) return;

        function playChime() {
            try {
                var AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (!AudioContextClass) return;
                var ctx = new AudioContextClass();
                var t0 = ctx.currentTime;

                // C-Major arpeggio
                [523.25, 659.25, 783.99, 1046.5].forEach(function(freq, i) {
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    var start = t0 + 0.1 * i;
                    osc.frequency.setValueAtTime(freq, start);
                    gain.gain.setValueAtTime(0, t0);
                    gain.gain.linearRampToValueAtTime(0.035 - 0.005 * i, start + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, start + 1.8);
                    osc.start(start);
                    osc.stop(start + 1.8);
                });

                // Sparkle high notes
                [1567.98, 2093].forEach(function(freq, i) {
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    var start = t0 + 0.35 + 0.1 * i;
                    osc.frequency.setValueAtTime(freq, start);
                    gain.gain.setValueAtTime(0, t0);
                    gain.gain.linearRampToValueAtTime(0.015, start + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.001, start + 1.2);
                    osc.start(start);
                    osc.stop(start + 1.2);
                });
            } catch (e) {
                // AudioContext blocked by browser policy
            }
        }

        function spawnBurst() {
            var burst = document.createElement('div');
            burst.style.cssText = 'position:absolute;inset:0;pointer-events:none;z-index:100;';
            envelope.appendChild(burst);

            var colors = ['#a8323b', '#7a1f26', '#c9a24a', '#ece4d8'];
            for (var i = 0; i < 30; i++) {
                var angle = (2 * Math.PI * i) / 30 + (Math.random() - 0.5) * 0.4;
                var r = 30 + 55 * Math.random();
                var x = 50 + Math.cos(angle) * r;
                var y = 50 + Math.sin(angle) * r;
                var color = colors[Math.floor(Math.random() * colors.length)];
                var size = 10 + 16 * Math.random();

                var el = document.createElement('div');
                el.style.cssText = 'position:absolute;left:50%;top:50%;color:' + color + ';font-size:' + size + 'px;text-shadow:0 0 10px ' + color + ';';
                el.style.setProperty('--dx', (x - 50) * 5 + 'px');
                el.style.setProperty('--dy', (y - 50) * 5 + 'px');
                el.style.setProperty('--rot-start', (Math.random() - 0.5) * 30 + 'deg');
                el.style.setProperty('--rot-end', (Math.random() - 0.5) * 360 + 'deg');
                el.style.animation = 'mayhenParticleBurst 1.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) ' + (0.15 * Math.random()) + 's forwards';
                el.innerHTML = '❤';
                burst.appendChild(el);
            }
        }

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (envelope.classList.contains('invite-closing')) return;

            envelope.classList.add('invite-closing');
            playChime();

            var seal = envelope.querySelector('.mayhen_invitation_envelope_seal');
            if (seal) {
                seal.style.animation = 'mayhenSealBreak 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards';
            }

            setTimeout(function() {
                spawnBurst();
                var card = envelope.querySelector('.mayhen_invitation_envelope');
                if (card) {
                    card.style.animation = 'mayhenEnvelopeAway 0.8s ease-in forwards';
                }
            }, 500);

            setTimeout(function() {
                envelope.classList.add('is-opened');
                setTimeout(function() {
                    envelope.style.display = 'none';
                    document.body.style.overflow = '';
                    var hero = document.querySelector('.mayhen_invitation_hero');
                    if (hero) hero.scrollIntoView({ behavior: 'smooth' });
                }, 400);
            }, 1300);
        });
    }

    /**
     * 2. Đồng hồ đếm ngược thời gian thực
     */
    function initCountdown() {
        var cdContainer = document.querySelector('.mayhen_invitation_countdown');
        if (!cdContainer) return;

        var targetStr = cdContainer.getAttribute('data-countdown');
        if (!targetStr) return;

        var targetDate = new Date(targetStr).getTime();
        if (isNaN(targetDate)) return;

        var daysEl = cdContainer.querySelector('.cd-days');
        var hoursEl = cdContainer.querySelector('.cd-hours');
        var minsEl = cdContainer.querySelector('.cd-mins');
        var secsEl = cdContainer.querySelector('.cd-secs');

        function update() {
            var now = new Date().getTime();
            var distance = targetDate - now;

            if (distance < 0) {
                if (daysEl) daysEl.textContent = '00';
                if (hoursEl) hoursEl.textContent = '00';
                if (minsEl) minsEl.textContent = '00';
                if (secsEl) secsEl.textContent = '00';
                return;
            }

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var mins = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var secs = Math.floor((distance % (1000 * 60)) / 1000);

            if (daysEl) daysEl.textContent = days < 10 ? '0' + days : days;
            if (hoursEl) hoursEl.textContent = hours < 10 ? '0' + hours : hours;
            if (minsEl) minsEl.textContent = mins < 10 ? '0' + mins : mins;
            if (secsEl) secsEl.textContent = secs < 10 ? '0' + secs : secs;
        }

        update();
        setInterval(update, 1000);
    }

    /**
     * 3. Sân khấu 3D Coverflow cho Album 10 Ảnh Cưới
     */
    function init3DGalleryCoverflow() {
        var stage = document.getElementById('mayhenMddGalleryStage');
        if (!stage) return;

        var items = Array.prototype.slice.call(stage.querySelectorAll('.mayhen_invitation_gallery_item'));
        if (items.length < 2) return;

        var total = items.length;
        var currentIndex = 0;

        var prevBtn = document.querySelector('.mayhen_invitation_gallery_prev_btn');
        var nextBtn = document.querySelector('.mayhen_invitation_gallery_next_btn');
        var dots = Array.prototype.slice.call(document.querySelectorAll('.mayhen_invitation_mdd_dot'));

        function renderPositions() {
            items.forEach(function(card, i) {
                var offset = i - currentIndex;
                if (offset > total / 2) offset -= total;
                if (offset < -total / 2) offset += total;

                if (offset === 0) {
                    card.style.transform = 'translateX(0%) translateZ(0px) rotateY(0deg) scale(1)';
                    card.style.opacity = '1';
                    card.style.zIndex = '100';
                    card.style.pointerEvents = 'auto';
                } else if (offset === 1) {
                    card.style.transform = 'translateX(60%) translateZ(-150px) rotateY(45deg) scale(0.85)';
                    card.style.opacity = '0.75';
                    card.style.zIndex = '99';
                    card.style.pointerEvents = 'auto';
                } else if (offset === -1) {
                    card.style.transform = 'translateX(-60%) translateZ(-150px) rotateY(-45deg) scale(0.85)';
                    card.style.opacity = '0.75';
                    card.style.zIndex = '99';
                    card.style.pointerEvents = 'auto';
                } else if (offset === 2) {
                    card.style.transform = 'translateX(120%) translateZ(-300px) rotateY(90deg) scale(0.7)';
                    card.style.opacity = '0.5';
                    card.style.zIndex = '98';
                    card.style.pointerEvents = 'auto';
                } else if (offset === -2) {
                    card.style.transform = 'translateX(-120%) translateZ(-300px) rotateY(-90deg) scale(0.7)';
                    card.style.opacity = '0.5';
                    card.style.zIndex = '98';
                    card.style.pointerEvents = 'auto';
                } else {
                    var sign = offset > 0 ? 1 : -1;
                    card.style.transform = 'translateX(' + (sign * 180) + '%) translateZ(-450px) rotateY(' + (sign * 135) + 'deg) scale(0.7)';
                    card.style.opacity = '0.2';
                    card.style.zIndex = '90';
                    card.style.pointerEvents = 'none';
                }
            });

            dots.forEach(function(dot, idx) {
                if (idx === currentIndex) {
                    dot.classList.add('is-active');
                } else {
                    dot.classList.remove('is-active');
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                currentIndex = (currentIndex + 1) % total;
                renderPositions();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                currentIndex = (currentIndex - 1 + total) % total;
                renderPositions();
            });
        }

        dots.forEach(function(dot, idx) {
            dot.addEventListener('click', function(e) {
                e.preventDefault();
                currentIndex = idx;
                renderPositions();
            });
        });

        items.forEach(function(card, idx) {
            card.addEventListener('click', function() {
                if (idx !== currentIndex) {
                    currentIndex = idx;
                    renderPositions();
                }
            });
        });

        // Touch swipe trên mobile
        var touchStartX = 0;
        stage.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        stage.addEventListener('touchend', function(e) {
            var touchEndX = e.changedTouches[0].screenX;
            var diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 40) {
                if (diff > 0) {
                    currentIndex = (currentIndex + 1) % total;
                } else {
                    currentIndex = (currentIndex - 1 + total) % total;
                }
                renderPositions();
            }
        }, { passive: true });

        renderPositions();
    }

    /**
     * 4. Lightbox Dialog phóng to ảnh
     */
    function initLightbox() {
        var modal = document.getElementById('mayhenMddLightbox');
        if (!modal) return;

        var imgEl = document.getElementById('mayhenMddLightboxImg');
        var counterEl = document.getElementById('mayhenMddLightboxCounter');
        var closeBtn = document.getElementById('mayhenMddLightboxClose');
        var prevBtn = document.getElementById('mayhenMddLightboxPrev');
        var nextBtn = document.getElementById('mayhenMddLightboxNext');

        var galleryImgs = Array.prototype.slice.call(document.querySelectorAll('.mayhen_invitation_gallery_img'));
        var photoUrls = galleryImgs.map(function(img) { return img.src; });
        var activeIndex = 0;

        function showLightbox(idx) {
            if (idx < 0) idx = photoUrls.length - 1;
            if (idx >= photoUrls.length) idx = 0;
            activeIndex = idx;

            if (imgEl && photoUrls[idx]) imgEl.src = photoUrls[idx];
            if (counterEl) counterEl.textContent = (activeIndex + 1) + ' / ' + photoUrls.length;
        }

        galleryImgs.forEach(function(img, idx) {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function(e) {
                e.stopPropagation();
                showLightbox(idx);
                if (typeof modal.showModal === 'function') modal.showModal();
            });
        });

        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                showLightbox(activeIndex + 1);
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                showLightbox(activeIndex - 1);
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                if (typeof modal.close === 'function') modal.close();
            });
        }

        modal.addEventListener('click', function(e) {
            if (e.target === modal && typeof modal.close === 'function') {
                modal.close();
            }
        });
    }

    /**
     * 5. Sao chép số tài khoản 1-chạm
     */
    function initCopyAccount() {
        var copyBtns = document.querySelectorAll('.mayhen_invitation_copy_btn');
        copyBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var acc = btn.getAttribute('data-copy') || '';
                if (!acc) return;

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(acc).then(function() {
                        var originalText = btn.textContent;
                        btn.textContent = '✓ Đã sao chép!';
                        setTimeout(function() {
                            btn.textContent = originalText;
                        }, 2000);
                    }).catch(function() {
                        prompt('Sao chép số tài khoản:', acc);
                    });
                } else {
                    prompt('Sao chép số tài khoản:', acc);
                }
            });
        });
    }

    /**
     * 6. Sổ ký tên Canvas điện tử
     */
    function initSignatureCanvas() {
        var canvas = document.getElementById('mayhenSignatureCanvas');
        if (!canvas) return;

        var ctx = canvas.getContext('2d');
        if (!ctx) return;

        ctx.strokeStyle = '#c9a24a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        var isDrawing = false;
        var lastX = 0;
        var lastY = 0;

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            var clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
            var clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function start(e) {
            isDrawing = true;
            var pos = getPos(e);
            lastX = pos.x;
            lastY = pos.y;
        }

        function draw(e) {
            if (!isDrawing) return;
            if (e.type.startsWith('touch')) e.preventDefault();
            var pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            lastX = pos.x;
            lastY = pos.y;
        }

        function stop() {
            isDrawing = false;
        }

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stop);
        canvas.addEventListener('mouseleave', stop);

        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stop);

        var clearBtn = document.getElementById('mayhenClearSignatureBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                var successMsg = document.getElementById('mayhenSignatureSuccessMsg');
                if (successMsg) successMsg.style.display = 'none';
            });
        }

        var saveBtn = document.getElementById('mayhenSaveSignatureBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                var successMsg = document.getElementById('mayhenSignatureSuccessMsg');
                if (successMsg) {
                    successMsg.style.display = 'block';
                    saveBtn.textContent = '✓ Đã lưu';
                    setTimeout(function() {
                        saveBtn.textContent = 'Lưu chữ ký';
                    }, 2500);
                }
            });
        }
    }

    /**
     * 7. Form RSVP & Lời Chúc (Wishes)
     */
    function initRsvpAndWishes() {
        var rsvpForm = document.getElementById('mayhenRsvpForm');
        if (rsvpForm) {
            rsvpForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var successBox = document.getElementById('mayhenRsvpSuccess');
                if (successBox) successBox.style.display = 'block';
                var submitBtn = rsvpForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = '✓ ĐÃ XÁC NHẬN';
                }
            });
        }

        var wishForm = document.getElementById('mayhenWishForm');
        var wishList = document.getElementById('mayhenWishList');
        if (wishForm && wishList) {
            wishForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var nameInput = wishForm.querySelector('input[name="name"]');
                var msgInput = wishForm.querySelector('textarea[name="message"]');
                if (!nameInput || !msgInput) return;

                var nameVal = nameInput.value.trim();
                var msgVal = msgInput.value.trim();
                if (!nameVal || !msgVal) return;

                var item = document.createElement('div');
                item.className = 'mayhen_invitation_wish_item mayhen_invitation_mdd_wish_item';
                item.innerHTML = '<strong>' + escapeHtml(nameVal) + ':</strong><p>' + escapeHtml(msgVal) + '</p>';
                wishList.prepend(item);

                msgInput.value = '';
                var btn = wishForm.querySelector('button[type="submit"]');
                if (btn) {
                    var oldText = btn.textContent;
                    btn.textContent = '✓ ĐÃ GỬI LỜI CHÚC';
                    setTimeout(function() { btn.textContent = oldText; }, 2000);
                }
            });
        }
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * 8. Handshake 2 chiều với Studio Editor (`editor-bridge.blade.php`)
     */
    function initEditorBridgeHandshake() {
        try {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    type: 'MAYHEN_BRIDGE_READY',
                    source: 'minimalism-do-dam',
                    sectionsCount: 10
                }, '*');
            }
        } catch (e) {}

        // Lắng nghe cập nhật Live Sync từ Studio
        window.addEventListener('message', function(event) {
            var data = event.data;
            if (!data || data.type !== 'MAYHEN_SYNC_FIELD') return;

            var target = data.target;
            var value = data.value;
            if (!target) return;

            var elements = document.querySelectorAll('[data-sync-target="' + target + '"]');
            elements.forEach(function(el) {
                if (el.tagName === 'IMG') {
                    el.src = value;
                } else {
                    el.textContent = value;
                }
            });
        });
    }
})();
