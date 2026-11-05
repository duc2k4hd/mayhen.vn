/* ==========================================================================
   MÂY HẸN (MAYHEN.VN) — NATIVE INTERACTIVE ENGINE & STUDIO LIVE SYNC
   Template: "Hạnh Phúc Mang Tên Chúng Mình"
   Prefix: mayhen_invitation_*
   ========================================================================== */

(function () {
    "use strict";

    var countdownInterval = null;

    function showMayhenToast(message) {
        var toast = document.querySelector(".mayhen_invitation_toast");
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add("mayhen_invitation_toast--show");
        if (toast._timer) clearTimeout(toast._timer);
        toast._timer = setTimeout(function () {
            toast.classList.remove("mayhen_invitation_toast--show");
        }, 3200);
    }

    function pad2(n) {
        var num = parseInt(n, 10) || 0;
        return num < 10 ? "0" + num : String(num);
    }

    /* ----------------------------------------------------------------------
       1. GLOBAL CONTRACT: window.updateMayhenCountdown(targetDate)
       ---------------------------------------------------------------------- */
    window.updateMayhenCountdown = function (targetDate) {
        var container = document.querySelector(".mayhen_invitation_countdown");
        var daysEl = document.querySelector('[data-countdown-unit="days"]');
        var hoursEl = document.querySelector('[data-countdown-unit="hours"]');
        var minutesEl = document.querySelector('[data-countdown-unit="minutes"]');
        var secondsEl = document.querySelector('[data-countdown-unit="seconds"]');
        if (!daysEl || !hoursEl || !minutesEl || !secondsEl) return;

        var parsedTime = NaN;
        if (targetDate instanceof Date) {
            parsedTime = targetDate.getTime();
        } else if (typeof targetDate === "number") {
            parsedTime = targetDate;
        } else if (typeof targetDate === "string" && targetDate.trim()) {
            var raw = targetDate.trim();
            // Support DD.MM.YYYY or DD/MM/YYYY or YYYY-MM-DD
            var dmyMatch = raw.match(/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})$/);
            if (dmyMatch) {
                parsedTime = new Date(
                    parseInt(dmyMatch[3], 10),
                    parseInt(dmyMatch[2], 10) - 1,
                    parseInt(dmyMatch[1], 10),
                    13, 0, 0
                ).getTime();
            } else {
                parsedTime = new Date(raw).getTime();
            }
        }

        if (isNaN(parsedTime) && container) {
            var attrTarget = container.getAttribute("data-wedding-target");
            if (attrTarget) parsedTime = new Date(attrTarget).getTime();
        }

        if (isNaN(parsedTime)) {
            parsedTime = Date.now() + 30 * 86400000;
        }

        if (countdownInterval) clearInterval(countdownInterval);

        function tick() {
            var diff = Math.max(0, parsedTime - Date.now());
            var d = Math.floor(diff / 86400000);
            var h = Math.floor((diff % 86400000) / 3600000);
            var m = Math.floor((diff % 3600000) / 60000);
            var s = Math.floor((diff % 60000) / 1000);

            daysEl.textContent = pad2(d);
            hoursEl.textContent = pad2(h);
            minutesEl.textContent = pad2(m);
            secondsEl.textContent = pad2(s);
        }

        tick();
        countdownInterval = setInterval(tick, 1000);
    };

    /* ----------------------------------------------------------------------
       2. GLOBAL CONTRACT: window.renderMayhenWeddingCalendar(targetDate)
       ---------------------------------------------------------------------- */
    window.renderMayhenWeddingCalendar = function (targetDate) {
        var gridEl = document.querySelector('[data-sync-target="wedding_calendar"]');
        if (!gridEl) return;

        var year = parseInt(gridEl.getAttribute("data-wedding-year") || "2026", 10);
        var month = parseInt(gridEl.getAttribute("data-wedding-month") || "11", 10);
        var day = parseInt(gridEl.getAttribute("data-wedding-day") || "22", 10);

        if (targetDate) {
            if (targetDate instanceof Date && !isNaN(targetDate.getTime())) {
                year = targetDate.getFullYear();
                month = targetDate.getMonth() + 1;
                day = targetDate.getDate();
            } else if (typeof targetDate === "string" && targetDate.trim()) {
                var raw = targetDate.trim();
                var dmy = raw.match(/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})$/);
                var ymd = raw.match(/^(\d{4})[.\/-](\d{1,2})[.\/-](\d{1,2})/);
                if (dmy) {
                    day = parseInt(dmy[1], 10);
                    month = parseInt(dmy[2], 10);
                    year = parseInt(dmy[3], 10);
                } else if (ymd) {
                    year = parseInt(ymd[1], 10);
                    month = parseInt(ymd[2], 10);
                    day = parseInt(ymd[3], 10);
                } else {
                    var dt = new Date(raw);
                    if (!isNaN(dt.getTime())) {
                        year = dt.getFullYear();
                        month = dt.getMonth() + 1;
                        day = dt.getDate();
                    }
                }
            }
        }

        gridEl.setAttribute("data-wedding-year", String(year));
        gridEl.setAttribute("data-wedding-month", String(month));
        gridEl.setAttribute("data-wedding-day", String(day));

        var monthTitleEl = document.querySelector('[data-sync-target="calendar_month_year"]');
        if (monthTitleEl) {
            monthTitleEl.textContent = "Tháng " + pad2(month) + "/" + year;
        }

        // Calculate Monday-based offset (T2 = 0 ... CN = 6)
        var firstDayOfMonth = new Date(year, month - 1, 1).getDay();
        var mondayOffset = (firstDayOfMonth + 6) % 7;
        var daysInMonth = new Date(year, month, 0).getDate();

        var html = "";
        for (var i = 0; i < mondayOffset; i++) {
            html += '<div class="mayhen_invitation_calendar_day mayhen_invitation_calendar_day--empty"></div>';
        }

        for (var d = 1; d <= daysInMonth; d++) {
            var isWeddingDay = d === day;
            if (isWeddingDay) {
                html +=
                    '<div class="mayhen_invitation_calendar_day mayhen_invitation_calendar_day--active">' +
                    '<svg class="mayhen_invitation_calendar_heart" viewBox="0 0 24 24" aria-hidden="true">' +
                    '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>' +
                    "</svg>" +
                    "<span>" + pad2(d) + "</span>" +
                    "</div>";
            } else {
                html += '<div class="mayhen_invitation_calendar_day"><span>' + pad2(d) + "</span></div>";
            }
        }

        gridEl.innerHTML = html;
    };

    /* ----------------------------------------------------------------------
       3. OPENING SCREEN, MUSIC & AUTO-SCROLL ENGINE
       ---------------------------------------------------------------------- */
    function initOpeningAndAudio() {
        var appEl = document.querySelector(".mayhen_invitation_app");
        var isEmbed = appEl && appEl.getAttribute("data-is-embed") === "true";
        var openingEl = document.querySelector(".mayhen_invitation_opening");
        var openBtn = document.querySelector(".mayhen_invitation_open_btn");
        var audioEl = document.querySelector(".mayhen_invitation_audio");
        var musicBtn = document.querySelector(".mayhen_invitation_music_btn");
        var scrollBtn = document.querySelector(".mayhen_invitation_scroll_btn");

        var isAutoScrolling = false;
        var scrollRaf = null;
        var frameCounter = 0;

        function playMusic() {
            if (!audioEl) return;
            var p = audioEl.play();
            if (p && typeof p.then === "function") {
                p.then(function () {
                    if (musicBtn) musicBtn.classList.add("mayhen_invitation_float_btn--playing");
                }).catch(function () {});
            }
        }

        function pauseMusic() {
            if (!audioEl) return;
            audioEl.pause();
            if (musicBtn) musicBtn.classList.remove("mayhen_invitation_float_btn--playing");
        }

        if (musicBtn && audioEl) {
            musicBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                if (audioEl.paused) playMusic();
                else pauseMusic();
            });
        }

        function stepAutoScroll() {
            if (!isAutoScrolling) return;
            if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
                stopAutoScroll();
                return;
            }
            frameCounter++;
            if (frameCounter % 2 === 0) {
                window.scrollBy(0, 1);
            }
            scrollRaf = requestAnimationFrame(stepAutoScroll);
        }

        function startAutoScroll() {
            if (isAutoScrolling) return;
            isAutoScrolling = true;
            if (scrollBtn) scrollBtn.classList.add("mayhen_invitation_float_btn--playing");
            scrollRaf = requestAnimationFrame(stepAutoScroll);
        }

        function stopAutoScroll() {
            isAutoScrolling = false;
            if (scrollBtn) scrollBtn.classList.remove("mayhen_invitation_float_btn--playing");
            if (scrollRaf) {
                cancelAnimationFrame(scrollRaf);
                scrollRaf = null;
            }
        }

        if (scrollBtn) {
            scrollBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                if (isAutoScrolling) stopAutoScroll();
                else startAutoScroll();
            });
        }

        ["wheel", "touchmove", "keydown", "mousedown"].forEach(function (evt) {
            window.addEventListener(
                evt,
                function (e) {
                    if (e.target && e.target.closest && e.target.closest("[data-ignore-autoscroll]")) return;
                    if (isAutoScrolling) stopAutoScroll();
                },
                { passive: true }
            );
        });

        function openInvitation() {
            if (!openingEl || openingEl.classList.contains("mayhen_invitation_opening--opened")) return;
            openingEl.classList.add("mayhen_invitation_opening--opened");
            document.body.classList.remove("mayhen_invitation_lock_scroll");
            playMusic();
            setTimeout(function () {
                startAutoScroll();
            }, 2200);
        }

        if (isEmbed) {
            if (openingEl) {
                openingEl.classList.add("mayhen_invitation_opening--opened");
                openingEl.style.setProperty("display", "none", "important");
            }
            document.body.classList.remove("mayhen_invitation_lock_scroll");
        } else if (openingEl) {
            if (openBtn) openBtn.addEventListener("click", openInvitation);
            openingEl.addEventListener("click", openInvitation);
        }

        var scrollCue = document.querySelector("[data-scroll-to]");
        if (scrollCue) {
            scrollCue.addEventListener("click", function () {
                var targetSel = scrollCue.getAttribute("data-scroll-to");
                var targetEl = targetSel ? document.querySelector(targetSel) : null;
                if (targetEl) targetEl.scrollIntoView({ behavior: "smooth" });
            });
        }
    }

    /* ----------------------------------------------------------------------
       4. SCROLL REVEAL OBSERVER (60FPS)
       ---------------------------------------------------------------------- */
    function initScrollReveal() {
        var items = document.querySelectorAll(".mayhen_invitation_reveal");
        if (!("IntersectionObserver" in window)) {
            items.forEach(function (el) {
                el.classList.add("mayhen_invitation_visible");
            });
            return;
        }

        var observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("mayhen_invitation_visible");
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12 }
        );

        items.forEach(function (el) {
            observer.observe(el);
        });
    }

    /* ----------------------------------------------------------------------
       5. ELECTRONIC SIGNATURE PAD & DRAGGABLE BOARD
       ---------------------------------------------------------------------- */
    function initSignaturePad() {
        var canvas = document.querySelector(".mayhen_invitation_signature_canvas");
        var board = document.querySelector(".mayhen_invitation_signature_board");
        if (!canvas || !board) return;

        var ctx = canvas.getContext("2d");
        var currentInk = "#233628";
        var drawing = false;
        var hasStrokes = false;
        var previewItem = null;
        var STORAGE_KEY = "mayhen_invitation_signatures_hpmtcm_v1";

        function configureCtx() {
            ctx.strokeStyle = currentInk;
            ctx.lineWidth = 2.2;
            ctx.lineCap = "round";
            ctx.lineJoin = "round";
        }
        configureCtx();

        var inkDots = document.querySelectorAll(".mayhen_invitation_ink_dot");
        inkDots.forEach(function (btn) {
            btn.addEventListener("click", function () {
                inkDots.forEach(function (b) {
                    b.classList.remove("mayhen_invitation_ink_dot--active");
                });
                btn.classList.add("mayhen_invitation_ink_dot--active");
                currentInk = btn.getAttribute("data-ink") || "#233628";
                configureCtx();
            });
        });

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            var clientX = e.touches && e.touches.length ? e.touches[0].clientX : e.clientX;
            var clientY = e.touches && e.touches.length ? e.touches[0].clientY : e.clientY;
            return {
                x: ((clientX - rect.left) * canvas.width) / rect.width,
                y: ((clientY - rect.top) * canvas.height) / rect.height
            };
        }

        function startDraw(e) {
            drawing = true;
            hasStrokes = true;
            configureCtx();
            var p = getPos(e);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            if (e.cancelable) e.preventDefault();
        }

        function moveDraw(e) {
            if (!drawing) return;
            var p = getPos(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            if (e.cancelable) e.preventDefault();
        }

        function stopDraw() {
            drawing = false;
        }

        canvas.addEventListener("mousedown", startDraw);
        canvas.addEventListener("mousemove", moveDraw);
        window.addEventListener("mouseup", stopDraw);
        canvas.addEventListener("touchstart", startDraw, { passive: false });
        canvas.addEventListener("touchmove", moveDraw, { passive: false });
        window.addEventListener("touchend", stopDraw);

        function makeDraggable(el) {
            var isDragging = false;
            var offsetX = 0;
            var offsetY = 0;

            function onStart(e) {
                isDragging = true;
                var rect = el.getBoundingClientRect();
                var cx = e.touches && e.touches.length ? e.touches[0].clientX : e.clientX;
                var cy = e.touches && e.touches.length ? e.touches[0].clientY : e.clientY;
                offsetX = cx - rect.left;
                offsetY = cy - rect.top;
                if (e.cancelable) e.preventDefault();
            }

            function onMove(e) {
                if (!isDragging) return;
                var bRect = board.getBoundingClientRect();
                var cx = e.touches && e.touches.length ? e.touches[0].clientX : e.clientX;
                var cy = e.touches && e.touches.length ? e.touches[0].clientY : e.clientY;
                var left = Math.max(0, Math.min(board.clientWidth - el.offsetWidth, cx - bRect.left - offsetX));
                var top = Math.max(0, Math.min(board.clientHeight - el.offsetHeight, cy - bRect.top - offsetY));
                el.style.left = left + "px";
                el.style.top = top + "px";
            }

            function onEnd() {
                if (isDragging) {
                    isDragging = false;
                    persistSignatures();
                }
            }

            el.addEventListener("mousedown", onStart);
            window.addEventListener("mousemove", onMove);
            window.addEventListener("mouseup", onEnd);
            el.addEventListener("touchstart", onStart, { passive: false });
            window.addEventListener("touchmove", onMove, { passive: false });
            window.addEventListener("touchend", onEnd);
        }

        function placeSignatureOnBoard(dataUrl, rx, ry) {
            var item = document.createElement("div");
            item.className = "mayhen_invitation_signature_item";
            var img = document.createElement("img");
            img.src = dataUrl;
            img.alt = "Chữ ký khách mời";
            item.appendChild(img);
            board.appendChild(item);

            var bw = board.clientWidth || 320;
            var bh = board.clientHeight || 180;
            var xRatio = typeof rx === "number" ? rx : 0.08 + Math.random() * 0.58;
            var yRatio = typeof ry === "number" ? ry : 0.10 + Math.random() * 0.55;
            item.style.left = Math.round(xRatio * bw) + "px";
            item.style.top = Math.round(yRatio * bh) + "px";

            makeDraggable(item);
            return item;
        }

        function persistSignatures() {
            try {
                var items = board.querySelectorAll(".mayhen_invitation_signature_item");
                var bw = board.clientWidth || 320;
                var bh = board.clientHeight || 180;
                var saved = [];
                items.forEach(function (el) {
                    var img = el.querySelector("img");
                    if (!img) return;
                    saved.push({
                        src: img.src,
                        rx: parseFloat(el.style.left || "20") / bw,
                        ry: parseFloat(el.style.top || "20") / bh
                    });
                });
                localStorage.setItem(STORAGE_KEY, JSON.stringify(saved.slice(-16)));
            } catch (e) {}
        }

        function createSampleCalligraphy(text, color) {
            var c = document.createElement("canvas");
            c.width = 170;
            c.height = 72;
            var cctx = c.getContext("2d");
            cctx.font = "italic 24px 'Dancing Script', cursive";
            cctx.fillStyle = color;
            cctx.fillText(text, 10, 42);
            cctx.beginPath();
            cctx.strokeStyle = color;
            cctx.lineWidth = 1.3;
            cctx.moveTo(12, 50);
            cctx.quadraticCurveTo(75, 42, 142, 52);
            cctx.stroke();
            return c.toDataURL("image/png");
        }

        // Load existing or seed sample signatures
        var hasLoaded = false;
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                var list = JSON.parse(raw);
                if (Array.isArray(list) && list.length) {
                    list.forEach(function (item) {
                        placeSignatureOnBoard(item.src, item.rx, item.ry);
                    });
                    hasLoaded = true;
                }
            }
        } catch (e) {}

        if (!hasLoaded) {
            placeSignatureOnBoard(createSampleCalligraphy("Minh Khang ♥", "#233628"), 0.07, 0.14);
            placeSignatureOnBoard(createSampleCalligraphy("Thanh Thảo", "#967332"), 0.58, 0.16);
            placeSignatureOnBoard(createSampleCalligraphy("Hoàng Bách", "#4f644b"), 0.14, 0.58);
        }

        var clearBtn = document.querySelector('[data-sig-action="clear"]');
        var previewBtn = document.querySelector('[data-sig-action="preview"]');
        var saveBtn = document.querySelector('[data-sig-action="save"]');

        if (clearBtn) {
            clearBtn.addEventListener("click", function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasStrokes = false;
                if (previewItem && previewItem.parentNode) {
                    previewItem.parentNode.removeChild(previewItem);
                    previewItem = null;
                }
            });
        }

        if (previewBtn) {
            previewBtn.addEventListener("click", function () {
                if (!hasStrokes) {
                    showMayhenToast("Bạn hãy vẽ chữ ký vào khung trước khi xem thử nhé!");
                    return;
                }
                var url = canvas.toDataURL("image/png");
                if (previewItem && previewItem.parentNode) {
                    var img = previewItem.querySelector("img");
                    if (img) img.src = url;
                } else {
                    previewItem = placeSignatureOnBoard(url);
                }
                showMayhenToast("Bạn có thể kéo thả chữ ký tới vị trí yêu thích trên bảng!");
            });
        }

        if (saveBtn) {
            saveBtn.addEventListener("click", function () {
                if (!hasStrokes) {
                    showMayhenToast("Vui lòng ký tên vào khung trước khi lưu nhé!");
                    return;
                }
                var url = canvas.toDataURL("image/png");
                if (!previewItem) {
                    previewItem = placeSignatureOnBoard(url);
                } else {
                    var img = previewItem.querySelector("img");
                    if (img) img.src = url;
                }
                persistSignatures();
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasStrokes = false;
                previewItem = null;
                showMayhenToast("Đã lưu chữ ký của bạn vào Sổ Lưu Bút Mây Hẹn!");
            });
        }
    }

    /* ----------------------------------------------------------------------
       6. RSVP FORM & LIVE WISHES WALL
       ---------------------------------------------------------------------- */
    function initRsvpAndWishes() {
        var form = document.querySelector(".mayhen_invitation_form");
        var wishesList = document.querySelector(".mayhen_invitation_wishes_list");
        var WISH_KEY = "mayhen_invitation_wishes_hpmtcm_v1";

        function prependWish(name, message) {
            if (!wishesList) return;
            var item = document.createElement("div");
            item.className = "mayhen_invitation_wish_item";
            var p = document.createElement("p");
            p.className = "mayhen_invitation_wish_msg";
            p.textContent = "“" + message + "”";
            var author = document.createElement("span");
            author.className = "mayhen_invitation_wish_author";
            author.textContent = "— " + name;
            item.appendChild(p);
            item.appendChild(author);
            wishesList.insertBefore(item, wishesList.firstChild);
        }

        try {
            var stored = JSON.parse(localStorage.getItem(WISH_KEY) || "[]");
            if (Array.isArray(stored)) {
                stored.forEach(function (w) {
                    if (w && w.name && w.message) prependWish(w.name, w.message);
                });
            }
        } catch (e) {}

        if (!form) return;
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            var nameInput = form.querySelector('input[name="name"]');
            var msgInput = form.querySelector('textarea[name="message"]');
            var name = nameInput ? nameInput.value.trim() : "";
            var msg = msgInput ? msgInput.value.trim() : "";

            if (!name) {
                showMayhenToast("Vui lòng nhập Họ và Tên của bạn nhé!");
                return;
            }

            var finalMsg = msg || "Chúc hai bạn trăm năm hạnh phúc, trọn vẹn yêu thương!";
            prependWish(name, finalMsg);

            try {
                var arr = JSON.parse(localStorage.getItem(WISH_KEY) || "[]");
                arr.push({ name: name, message: finalMsg });
                localStorage.setItem(WISH_KEY, JSON.stringify(arr.slice(-30)));
            } catch (err) {}

            form.reset();
            showMayhenToast("Cảm ơn " + name + " đã xác nhận tham dự và gửi lời chúc phúc!");
        });
    }

    /* ----------------------------------------------------------------------
       7. LIGHTBOX MODAL & BANK COPY HELPERS
       ---------------------------------------------------------------------- */
    function initLightboxAndCopy() {
        var lightbox = document.querySelector(".mayhen_invitation_lightbox");
        var lightboxImg = document.querySelector(".mayhen_invitation_lightbox_img");
        var closeBtn = document.querySelector(".mayhen_invitation_lightbox_close");
        var prevBtn = document.querySelector(".mayhen_invitation_lightbox_nav--prev");
        var nextBtn = document.querySelector(".mayhen_invitation_lightbox_nav--next");
        var triggers = document.querySelectorAll(".mayhen_invitation_lightbox_trigger");

        var gallerySources = [];
        var activeIdx = 0;

        triggers.forEach(function (trigger) {
            var img = trigger.querySelector("img");
            if (!img) return;
            var idx = gallerySources.length;
            gallerySources.push(img);
            trigger.addEventListener("click", function () {
                openAt(idx);
            });
        });

        function openAt(index) {
            if (!lightbox || !lightboxImg || !gallerySources.length) return;
            activeIdx = (index + gallerySources.length) % gallerySources.length;
            lightboxImg.src = gallerySources[activeIdx].src;
            lightbox.classList.add("mayhen_invitation_lightbox--open");
        }

        function closeLightbox() {
            if (lightbox) lightbox.classList.remove("mayhen_invitation_lightbox--open");
        }

        if (closeBtn) closeBtn.addEventListener("click", closeLightbox);
        if (prevBtn) {
            prevBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                openAt(activeIdx - 1);
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                openAt(activeIdx + 1);
            });
        }
        if (lightbox) {
            lightbox.addEventListener("click", function (e) {
                if (e.target === lightbox) closeLightbox();
            });
        }

        var moreBtn = document.querySelector("[data-open-gallery]");
        if (moreBtn) {
            moreBtn.addEventListener("click", function () {
                openAt(0);
            });
        }

        // Copy bank account number buttons
        var copyBtns = document.querySelectorAll("[data-copy-target]");
        copyBtns.forEach(function (btn) {
            btn.addEventListener("click", function () {
                var targetKey = btn.getAttribute("data-copy-target");
                var targetEl = document.querySelector('[data-sync-target="' + targetKey + '"]');
                var text = targetEl ? targetEl.textContent.trim() : "";
                if (!text) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function () {
                        showMayhenToast("Đã sao chép số tài khoản: " + text);
                    }).catch(function () {
                        showMayhenToast("Số tài khoản: " + text);
                    });
                } else {
                    showMayhenToast("Số tài khoản: " + text);
                }
            });
        });
    }

    /* ----------------------------------------------------------------------
       8. STUDIO LIVE SYNC MESSAGE LISTENER (data-sync-target)
       ---------------------------------------------------------------------- */
    function initStudioLiveSync() {
        window.addEventListener("message", function (event) {
            var payload = event.data;
            if (!payload || typeof payload !== "object") return;

            var fields = payload.data || payload.fields || payload;
            if (!fields || typeof fields !== "object") return;

            Object.keys(fields).forEach(function (key) {
                var val = fields[key];
                if (val === undefined || val === null) return;
                var targets = document.querySelectorAll('[data-sync-target="' + key + '"]');
                targets.forEach(function (el) {
                    if (el.tagName === "IMG") {
                        el.src = String(val);
                    } else if (el.tagName === "A") {
                        el.href = String(val);
                    } else {
                        el.textContent = String(val);
                    }
                });
            });

            if (fields.groom_name || fields.bride_name) {
                var g = fields.groom_name || (document.querySelector('[data-sync-target="groom_name"]') || {}).textContent || "";
                var b = fields.bride_name || (document.querySelector('[data-sync-target="bride_name"]') || {}).textContent || "";
                if (g && b) {
                    document.querySelectorAll('[data-sync-target="couple_names"]').forEach(function (el) {
                        el.textContent = g + " & " + b;
                    });
                }
            }

            if (fields.wedding_date || fields.wedding_date_digits) {
                var dStr = fields.wedding_date || fields.wedding_date_digits;
                window.updateMayhenCountdown(dStr);
                window.renderMayhenWeddingCalendar(dStr);
            }
        });
    }

    /* ----------------------------------------------------------------------
       BOOT ALL MODULES
       ---------------------------------------------------------------------- */
    function boot() {
        window.renderMayhenWeddingCalendar();
        window.updateMayhenCountdown();
        initOpeningAndAudio();
        initScrollReveal();
        initSignaturePad();
        initRsvpAndWishes();
        initLightboxAndCopy();
        initStudioLiveSync();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", boot);
    } else {
        boot();
    }
})();
