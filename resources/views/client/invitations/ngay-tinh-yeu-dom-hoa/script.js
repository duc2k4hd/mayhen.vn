/* ==========================================================================
   MÂY HẸN (MAYHEN.VN) — REFERENCE-LOCKED FRONTEND ENGINE (v2.0)
   Source of Truth: https://hwd.happyweddingday.vn/red-vip
   Template: Ngày Tình Yêu Đơm Hoa (ngay-tinh-yeu-dom-hoa)
   ========================================================================== */

(function () {
    "use strict";

    var countdownTimerId = null;

    function pad2(n) {
        var val = parseInt(n, 10) || 0;
        return val < 10 ? "0" + val : String(val);
    }

    /**
     * Parses a date input explicitly in Asia/Ho_Chi_Minh (+07:00) timezone
     * without relying on ambiguous browser-local parsing.
     */
    function parseVietnamDate(input) {
        if (input instanceof Date && !isNaN(input.getTime())) {
            return {
                year: input.getFullYear(),
                month: input.getMonth() + 1,
                day: input.getDate(),
                timestamp: input.getTime()
            };
        }
        if (typeof input === "number" && !isNaN(input)) {
            var dNum = new Date(input);
            return {
                year: dNum.getFullYear(),
                month: dNum.getMonth() + 1,
                day: dNum.getDate(),
                timestamp: input
            };
        }
        if (typeof input === "string" && input.trim()) {
            var raw = input.trim();
            // Match DD.MM.YYYY or DD/MM/YYYY or DD-MM-YYYY
            var dmy = raw.match(/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})$/);
            if (dmy) {
                var dd = parseInt(dmy[1], 10);
                var mm = parseInt(dmy[2], 10);
                var yyyy = parseInt(dmy[3], 10);
                var isoDmy = yyyy + "-" + pad2(mm) + "-" + pad2(dd) + "T13:00:00+07:00";
                return {
                    year: yyyy,
                    month: mm,
                    day: dd,
                    timestamp: Date.parse(isoDmy)
                };
            }
            // Match YYYY-MM-DD (with optional time)
            var ymd = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})(?:[T\s](\d{2}):(\d{2})(?::(\d{2}))?)?/);
            if (ymd) {
                var y = parseInt(ymd[1], 10);
                var m = parseInt(ymd[2], 10);
                var d = parseInt(ymd[3], 10);
                var hh = ymd[4] || "13";
                var min = ymd[5] || "00";
                var ss = ymd[6] || "00";
                var isoYmd = y + "-" + pad2(m) + "-" + pad2(d) + "T" + hh + ":" + min + ":" + ss + "+07:00";
                return {
                    year: y,
                    month: m,
                    day: d,
                    timestamp: Date.parse(isoYmd)
                };
            }
        }
        return null;
    }

    /* ----------------------------------------------------------------------
       GLOBAL API 1: window.updateMayhenCountdown(targetDate)
       ---------------------------------------------------------------------- */
    window.updateMayhenCountdown = function (targetDate) {
        var grid = document.querySelector(".mayhen_invitation_countdown_grid");
        var daysEl = document.querySelector('[data-countdown-unit="days"]');
        var hoursEl = document.querySelector('[data-countdown-unit="hours"]');
        var minutesEl = document.querySelector('[data-countdown-unit="minutes"]');
        var secondsEl = document.querySelector('[data-countdown-unit="seconds"]');
        if (!daysEl || !hoursEl || !minutesEl || !secondsEl) return;

        var parsed = parseVietnamDate(targetDate);
        if (!parsed && grid) {
            parsed = parseVietnamDate(grid.getAttribute("data-countdown-target"));
        }
        var targetTs = parsed && !isNaN(parsed.timestamp) ? parsed.timestamp : Date.now();

        if (countdownTimerId) {
            clearInterval(countdownTimerId);
            countdownTimerId = null;
        }

        function updateTick() {
            var now = Date.now();
            var diff = Math.max(0, targetTs - now);
            var days = Math.floor(diff / 86400000);
            var hours = Math.floor((diff % 86400000) / 3600000);
            var minutes = Math.floor((diff % 3600000) / 60000);
            var seconds = Math.floor((diff % 60000) / 1000);

            daysEl.textContent = pad2(days);
            hoursEl.textContent = pad2(hours);
            minutesEl.textContent = pad2(minutes);
            secondsEl.textContent = pad2(seconds);
        }

        updateTick();
        countdownTimerId = setInterval(updateTick, 1000);
    };

    /* ----------------------------------------------------------------------
       GLOBAL API 2: window.renderMayhenWeddingCalendar(targetDate)
       ---------------------------------------------------------------------- */
    window.renderMayhenWeddingCalendar = function (targetDate) {
        var gridEl = document.querySelector('[data-sync-target="wedding_calendar"]');
        if (!gridEl) return;

        var year = parseInt(gridEl.getAttribute("data-wedding-year") || "2026", 10);
        var month = parseInt(gridEl.getAttribute("data-wedding-month") || "11", 10);
        var day = parseInt(gridEl.getAttribute("data-wedding-day") || "22", 10);
        var heartIconUrl = gridEl.getAttribute("data-heart-icon") || "assets/icons/calendar-heart.svg";

        var parsed = parseVietnamDate(targetDate);
        if (parsed) {
            year = parsed.year;
            month = parsed.month;
            day = parsed.day;
        }

        gridEl.setAttribute("data-wedding-year", String(year));
        gridEl.setAttribute("data-wedding-month", String(month));
        gridEl.setAttribute("data-wedding-day", String(day));

        // Update all month/year and date occurrences
        document.querySelectorAll('[data-sync-target="calendar_month_year"]').forEach(function (el) {
            el.textContent = "Tháng " + pad2(month) + "/" + year;
        });

        // Compute weekday in Asia/Ho_Chi_Minh for the 1st of the month (Monday=0 .. Sunday=6)
        var firstDayUtc = new Date(Date.UTC(year, month - 1, 1, 5, 0, 0));
        var mondayOffset = (firstDayUtc.getUTCDay() + 6) % 7;
        var daysInMonth = new Date(Date.UTC(year, month, 0)).getUTCDate();

        var cellsHtml = "";
        for (var i = 0; i < mondayOffset; i++) {
            cellsHtml += '<div class="mayhen_invitation_calendar_cell" aria-hidden="true"></div>';
        }

        for (var d = 1; d <= daysInMonth; d++) {
            if (d === day) {
                cellsHtml +=
                    '<div class="mayhen_invitation_calendar_cell mayhen_invitation_calendar_cell--active" aria-current="date">' +
                    '<img class="mayhen_invitation_calendar_heart" src="' + heartIconUrl + '" alt="">' +
                    "<span>" + pad2(d) + "</span>" +
                    "</div>";
            } else {
                cellsHtml += '<div class="mayhen_invitation_calendar_cell"><span>' + pad2(d) + "</span></div>";
            }
        }

        gridEl.innerHTML = cellsHtml;
    };

    /* ----------------------------------------------------------------------
       3. CLOUD DOORS OPENING, AUDIO & AUTO-SCROLL (SOURCE BEHAVIOR)
       ---------------------------------------------------------------------- */
    function initOpeningAndControls(rootEl) {
        if (!rootEl) return;
        var isEmbed = rootEl.getAttribute("data-is-embed") === "true";
        var leftDoor = rootEl.querySelector(".mayhen_invitation_cloud_door--left");
        var rightDoor = rootEl.querySelector(".mayhen_invitation_cloud_door--right");
        var openBtn = rootEl.querySelector(".mayhen_invitation_open_btn");
        var audio = rootEl.querySelector(".mayhen_invitation_audio");
        var musicBtn = rootEl.querySelector(".mayhen_invitation_music_toggle");
        var musicHint = rootEl.querySelector(".mayhen_invitation_music_hint");
        var scrollBtn = rootEl.querySelector(".mayhen_invitation_scroll_toggle");

        var isAutoScrolling = false;
        var animationId = null;
        var frameCount = 0;
        var scrollSpeed = 1;
        var frameDelay = 2;

        function hideMusicHint() {
            if (!musicHint) return;
            musicHint.classList.add("mayhen_invitation_music_hint--hidden");
        }

        function playMusic() {
            if (!audio || !audio.getAttribute("src")) {
                hideMusicHint();
                return;
            }
            var p = audio.play();
            if (p && typeof p.then === "function") {
                p.then(function () {
                    if (musicBtn) musicBtn.classList.add("mayhen_invitation_music_toggle--vibrating");
                    hideMusicHint();
                }).catch(function () {});
            }
        }

        function pauseMusic() {
            if (!audio) return;
            audio.pause();
            if (musicBtn) musicBtn.classList.remove("mayhen_invitation_music_toggle--vibrating");
        }

        if (musicBtn) {
            musicBtn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (!audio || !audio.getAttribute("src")) {
                    hideMusicHint();
                    return;
                }
                if (audio.paused) playMusic();
                else pauseMusic();
            });
        }

        function autoScrollLoop() {
            if (!isAutoScrolling) return;
            if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
                stopAutoScroll();
                return;
            }
            frameCount++;
            if (frameCount % frameDelay === 0) {
                window.scrollBy(0, scrollSpeed);
            }
            animationId = requestAnimationFrame(autoScrollLoop);
        }

        function startAutoScroll() {
            if (isAutoScrolling || isEmbed) return;
            isAutoScrolling = true;
            if (scrollBtn) scrollBtn.classList.add("mayhen_invitation_scroll_toggle--scrolling");
            animationId = requestAnimationFrame(autoScrollLoop);
        }

        function stopAutoScroll() {
            isAutoScrolling = false;
            if (scrollBtn) scrollBtn.classList.remove("mayhen_invitation_scroll_toggle--scrolling");
            if (animationId) {
                cancelAnimationFrame(animationId);
                animationId = null;
            }
        }

        if (scrollBtn) {
            scrollBtn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (isAutoScrolling) stopAutoScroll();
                else startAutoScroll();
            });
        }

        ["wheel", "touchmove", "mousedown", "keydown"].forEach(function (evt) {
            document.addEventListener(
                evt,
                function (e) {
                    if (e.target && e.target.closest && e.target.closest("[data-ignore-autoscroll]")) return;
                    if (isAutoScrolling) stopAutoScroll();
                },
                { passive: true }
            );
        });

        function openDoors() {
            if (rootEl.classList.contains("mayhen_invitation_root--opened")) return;
            rootEl.classList.add("mayhen_invitation_root--opened");
            playMusic();
            if (scrollBtn) scrollBtn.classList.add("mayhen_invitation_scroll_toggle--visible");
            setTimeout(function () {
                startAutoScroll();
            }, 4500);
        }

        if (isEmbed) {
            rootEl.classList.add("mayhen_invitation_root--opened");
        } else {
            if (leftDoor) leftDoor.addEventListener("click", openDoors);
            if (rightDoor) rightDoor.addEventListener("click", openDoors);
            if (openBtn) openBtn.addEventListener("click", openDoors);
        }

        var nextCue = rootEl.querySelector("[data-scroll-next]");
        if (nextCue) {
            nextCue.addEventListener("click", function () {
                var sec2 = rootEl.querySelector(".mayhen_invitation_family_ceremony");
                if (sec2) sec2.scrollIntoView({ behavior: "smooth" });
            });
        }
    }

    /* ----------------------------------------------------------------------
       4. SCROLL REVEAL & WISHES AUTO-SCROLL
       ---------------------------------------------------------------------- */
    function initAnimationsAndWishes(rootEl) {
        var animEls = rootEl.querySelectorAll(".mayhen_invitation_anim");
        if ("IntersectionObserver" in window) {
            var io = new IntersectionObserver(
                function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add("mayhen_invitation_anim--active");
                            io.unobserve(entry.target);
                        }
                    });
                },
                { threshold: 0.08 }
            );
            animEls.forEach(function (el) {
                io.observe(el);
            });
        } else {
            animEls.forEach(function (el) {
                el.classList.add("mayhen_invitation_anim--active");
            });
        }

        // Wishes continuous scroll matching reference (#wish-standalone-container)
        var wishContainer = rootEl.querySelector(".mayhen_invitation_wishes_container");
        var wishList = rootEl.querySelector(".mayhen_invitation_wishes_list");
        if (wishContainer && wishList && wishList.children.length > 2) {
            var scrollPos = 0;
            setInterval(function () {
                scrollPos += 1;
                if (scrollPos >= wishList.scrollHeight - wishContainer.clientHeight) {
                    scrollPos = 0;
                }
                wishContainer.scrollTop = scrollPos;
            }, 40);
        }
    }

    /* ----------------------------------------------------------------------
       5. RSVP FORM WITH REAL VALIDATION & ADAPTER HOOK (RULE F2)
       ---------------------------------------------------------------------- */
    function initRsvpForm(rootEl) {
        var form = rootEl.querySelector(".mayhen_invitation_rsvp_form");
        var statusEl = rootEl.querySelector(".mayhen_invitation_rsvp_form .mayhen_invitation_status_msg");
        var wishList = rootEl.querySelector(".mayhen_invitation_wishes_list");
        if (!form) return;

        form.addEventListener("submit", function (e) {
            e.preventDefault();
            var name = (form.querySelector('input[name="name"]') || {}).value || "";
            var attendingEl = form.querySelector('input[name="is_attending"]:checked');
            var partySide = (form.querySelector('select[name="party_side"]') || {}).value || "";
            var guestCount = (form.querySelector('select[name="guest_count"]') || {}).value || "";
            var message = (form.querySelector('textarea[name="message"]') || {}).value || "";

            name = name.trim();
            message = message.trim();

            if (!name) {
                if (statusEl) statusEl.textContent = "Vui lòng nhập Họ và Tên của bạn.";
                return;
            }
            if (!attendingEl) {
                if (statusEl) statusEl.textContent = "Vui lòng chọn trạng thái tham dự.";
                return;
            }
            if (attendingEl.value !== "Xin lỗi, tôi bận mất rồi" && !guestCount) {
                if (statusEl) statusEl.textContent = "Vui lòng chọn số lượng khách tham dự.";
                return;
            }

            var payload = {
                name: name,
                is_attending: attendingEl.value,
                party_side: partySide,
                guest_count: guestCount,
                message: message
            };

            var endpoint = rootEl.getAttribute("data-rsvp-endpoint") || "";
            var csrfTokenEl = form.querySelector('input[name="_token"]');
            var csrfToken = csrfTokenEl ? csrfTokenEl.value : "";

            // Append wish to local preview list safely using textContent (no innerHTML injection)
            if (message && wishList) {
                var item = document.createElement("div");
                item.className = "mayhen_invitation_wish_item";
                var msgDiv = document.createElement("div");
                msgDiv.className = "mayhen_invitation_wish_message";
                msgDiv.textContent = '"' + message + '"';
                var nameDiv = document.createElement("div");
                nameDiv.className = "mayhen_invitation_wish_name";
                nameDiv.textContent = "— " + name;
                item.appendChild(msgDiv);
                item.appendChild(nameDiv);
                wishList.insertBefore(item, wishList.firstChild);
            }

            // Dispatch event for Mây Hẹn Studio / host app integration
            window.dispatchEvent(new CustomEvent("mayhen:rsvp:submit", { detail: payload }));

            if (!endpoint) {
                if (statusEl) {
                    statusEl.textContent = "Đã ghi nhận thông tin tại trình duyệt (Cần cấu hình data-rsvp-endpoint trên Mây Hẹn để lưu máy chủ).";
                }
                form.reset();
                return;
            }

            if (statusEl) statusEl.textContent = "Đang gửi xác nhận...";
            fetch(endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json"
                },
                body: JSON.stringify(payload)
            })
                .then(function (res) {
                    if (!res.ok) throw new Error("HTTP " + res.status);
                    return res.json();
                })
                .then(function () {
                    if (statusEl) statusEl.textContent = "Cảm ơn " + name + " đã gửi xác nhận tham dự!";
                    form.reset();
                })
                .catch(function () {
                    if (statusEl) statusEl.textContent = "Không thể kết nối máy chủ. Vui lòng thử lại sau.";
                });
        });
    }

    /* ----------------------------------------------------------------------
       6. POINTER EVENTS SIGNATURE CANVAS WITH DPI SCALING (RULE F3)
       ---------------------------------------------------------------------- */
    function initSignatureCanvas(rootEl) {
        var canvas = rootEl.querySelector(".mayhen_invitation_signature_canvas");
        var statusEl = rootEl.querySelector(".mayhen_invitation_sig_status");
        var previewList = rootEl.querySelector(".mayhen_invitation_signature_preview_list");
        if (!canvas) return;

        var ctx = canvas.getContext("2d");
        var isDrawing = false;
        var hasInk = false;

        function setupCanvasDpi() {
            var rect = canvas.getBoundingClientRect();
            if (!rect.width || !rect.height) return;
            var dpr = window.devicePixelRatio || 1;
            var snapshot = hasInk ? canvas.toDataURL("image/png") : null;

            canvas.width = Math.round(rect.width * dpr);
            canvas.height = Math.round(rect.height * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.strokeStyle = "#181818";
            ctx.lineWidth = 2.0;
            ctx.lineCap = "round";
            ctx.lineJoin = "round";

            if (snapshot) {
                var img = new Image();
                img.onload = function () {
                    ctx.drawImage(img, 0, 0, rect.width, rect.height);
                };
                img.src = snapshot;
            }
        }

        setupCanvasDpi();
        window.addEventListener("resize", setupCanvasDpi);

        function getPoint(e) {
            var rect = canvas.getBoundingClientRect();
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }

        canvas.addEventListener("pointerdown", function (e) {
            isDrawing = true;
            hasInk = true;
            if (canvas.setPointerCapture) canvas.setPointerCapture(e.pointerId);
            var pt = getPoint(e);
            ctx.beginPath();
            ctx.moveTo(pt.x, pt.y);
            e.preventDefault();
        });

        canvas.addEventListener("pointermove", function (e) {
            if (!isDrawing) return;
            var pt = getPoint(e);
            ctx.lineTo(pt.x, pt.y);
            ctx.stroke();
            e.preventDefault();
        });

        function endPointer() {
            isDrawing = false;
        }

        canvas.addEventListener("pointerup", endPointer);
        canvas.addEventListener("pointercancel", endPointer);

        var clearBtn = rootEl.querySelector('[data-sig-action="clear"]');
        var previewBtn = rootEl.querySelector('[data-sig-action="preview"]');
        var saveBtn = rootEl.querySelector('[data-sig-action="save"]');

        if (clearBtn) {
            clearBtn.addEventListener("click", function () {
                var rect = canvas.getBoundingClientRect();
                ctx.clearRect(0, 0, rect.width || canvas.width, rect.height || canvas.height);
                hasInk = false;
                if (statusEl) statusEl.textContent = "Đã xóa khung ký tên.";
            });
        }

        if (previewBtn) {
            previewBtn.addEventListener("click", function () {
                if (!hasInk) {
                    if (statusEl) statusEl.textContent = "Vui lòng ký tên vào khung trước khi xem trước.";
                    return;
                }
                var dataUrl = canvas.toDataURL("image/webp", 0.92);
                if (previewList) {
                    var img = document.createElement("img");
                    img.className = "mayhen_invitation_signature_thumb";
                    img.src = dataUrl;
                    img.alt = "Bản xem trước chữ ký";
                    previewList.insertBefore(img, previewList.firstChild);
                }
                if (statusEl) statusEl.textContent = "Đã tạo bản xem trước chữ ký bên dưới.";
            });
        }

        if (saveBtn) {
            saveBtn.addEventListener("click", function () {
                if (!hasInk) {
                    if (statusEl) statusEl.textContent = "Vui lòng ký tên trước khi lưu.";
                    return;
                }
                var dataUrl = canvas.toDataURL("image/png");
                if (previewList) {
                    var img = document.createElement("img");
                    img.className = "mayhen_invitation_signature_thumb";
                    img.src = dataUrl;
                    img.alt = "Chữ ký đã lưu";
                    previewList.insertBefore(img, previewList.firstChild);
                }

                window.dispatchEvent(
                    new CustomEvent("mayhen:signature:submit", {
                        detail: { dataUrl: dataUrl }
                    })
                );

                var endpoint = rootEl.getAttribute("data-signature-endpoint") || "";
                if (!endpoint) {
                    if (statusEl) {
                        statusEl.textContent = "Đã xuất chữ ký PNG trên trình duyệt (Cần cấu hình data-signature-endpoint để lưu lên máy chủ).";
                    }
                    return;
                }

                if (statusEl) statusEl.textContent = "Đang lưu chữ ký...";
                fetch(endpoint, {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "Accept": "application/json" },
                    body: JSON.stringify({ signature: dataUrl })
                })
                    .then(function (res) {
                        if (!res.ok) throw new Error("HTTP " + res.status);
                        if (statusEl) statusEl.textContent = "Đã lưu chữ ký thành công!";
                    })
                    .catch(function () {
                        if (statusEl) statusEl.textContent = "Lỗi kết nối máy chủ khi lưu chữ ký.";
                    });
            });
        }
    }

    /* ----------------------------------------------------------------------
       7. LIGHTBOX & GALLERY MODAL (#CODAU, #CHURE, #ALBUM)
       ---------------------------------------------------------------------- */
    function initLightbox(rootEl) {
        var backdrop = rootEl.querySelector(".mayhen_invitation_modal_backdrop");
        var modalImg = rootEl.querySelector(".mayhen_invitation_modal_img");
        var closeBtn = rootEl.querySelector(".mayhen_invitation_modal_close");
        var prevBtn = rootEl.querySelector(".mayhen_invitation_modal_nav--prev");
        var nextBtn = rootEl.querySelector(".mayhen_invitation_modal_nav--next");
        if (!backdrop || !modalImg) return;

        var items = rootEl.querySelectorAll("[data-lightbox-item]");
        var imgElements = [];
        var currentIndex = 0;

        items.forEach(function (wrap) {
            var img = wrap.querySelector("img");
            if (!img) return;
            var idx = imgElements.length;
            imgElements.push(img);
            wrap.addEventListener("click", function () {
                openAt(idx);
            });
        });

        function openAt(idx) {
            if (!imgElements.length) return;
            currentIndex = (idx + imgElements.length) % imgElements.length;
            modalImg.src = imgElements[currentIndex].src;
            backdrop.classList.add("mayhen_invitation_modal_backdrop--active");
        }

        function closeModal() {
            backdrop.classList.remove("mayhen_invitation_modal_backdrop--active");
        }

        if (closeBtn) closeBtn.addEventListener("click", closeModal);
        if (prevBtn) {
            prevBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                openAt(currentIndex - 1);
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                openAt(currentIndex + 1);
            });
        }
        backdrop.addEventListener("click", function (e) {
            if (e.target === backdrop) closeModal();
        });

        var albumMoreBtn = rootEl.querySelector("[data-open-album-popup]");
        if (albumMoreBtn) {
            albumMoreBtn.addEventListener("click", function () {
                openAt(0);
            });
        }
    }

    function boot() {
        var rootEl = document.querySelector(".mayhen_invitation_root");
        if (!rootEl) return;
        window.renderMayhenWeddingCalendar();
        window.updateMayhenCountdown();
        initOpeningAndControls(rootEl);
        initAnimationsAndWishes(rootEl);
        initRsvpForm(rootEl);
        initSignatureCanvas(rootEl);
        initLightbox(rootEl);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", boot);
    } else {
        boot();
    }
})();
