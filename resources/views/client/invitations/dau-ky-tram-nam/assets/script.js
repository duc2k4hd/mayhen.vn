/* MAYHEN — Dấu Ký Trăm Năm. Native ES Pure JS, không phụ thuộc thư viện ngoài. */
(() => {
    "use strict";
    const P = "mayhen_invitation_jeowinids483fr_";
    const C = window.MAYHEN_INVITATION_CONFIG || {};
    const isEmbed = Boolean(
        C.isEmbed || new URLSearchParams(location.search).get("embed") === "1",
    );
    const one = (name, root = document) => root.querySelector(`.${P}${name}`);
    const all = (name, root = document) => [
        ...root.querySelectorAll(`.${P}${name}`),
    ];

    const toast = one("toast");
    let noticeTimer;
    const showMessage = (text) => {
        if (!toast) return;
        toast.textContent = text;
        toast.classList.add(P + "visible");
        clearTimeout(noticeTimer);
        noticeTimer = setTimeout(
            () => toast.classList.remove(P + "visible"),
            4000,
        );
    };

    const stage = one("stage");
    const spacer = one("stage-spacer");
    const syncStage = () => {
        if (!stage || !spacer) return;
        const clientWidth = spacer.clientWidth || stage.parentElement?.clientWidth || window.innerWidth || 420;
        const scale = Math.min(1, clientWidth / 420);
        stage.style.setProperty("--mayhen-stage-scale", scale);
        const sections = [...stage.children].filter((x) =>
            x.classList.contains(P + "ladi-section"),
        );
        const totalHeight = sections.reduce(
            (sum, s) => {
                const h = parseFloat(window.getComputedStyle(s).height) || s.offsetHeight || (scale > 0 ? s.getBoundingClientRect().height / scale : 0);
                return sum + h;
            },
            0,
        );
        if (totalHeight > 0) {
            spacer.style.height = Math.ceil(totalHeight * scale) + "px";
        }
    };
    window.addEventListener("resize", syncStage, { passive: true });
    if ("ResizeObserver" in window && spacer)
        new ResizeObserver(syncStage).observe(spacer);

    const dialogBackdrop = one("modal-backdrop");
    const closeDialogButton = one("modal-close");
    const intro = one("popup10");
    const giftDialog = one("gift-dialog");
    const royalGate = one("royal_gate_wrapper");
    const doorStage = document.getElementById("mayhen-door-stage") || one("door_stage");
    let modal = null;
    let isOpening = false;
    let petalsAnimationId = null;

    /* ==========================================================================
     HIỆU ỨNG CÁNH HOA HỒNG NHUNG & BỤI SAO VÀNG HOÀNG GIA (60FPS NATIVE CANVAS)
     ========================================================================== */
    const canvas = one("petals_canvas");
    let ctx = canvas ? canvas.getContext("2d") : null;
    let petals = [];
    const PETAL_COUNT = 28;

    const initPetals = () => {
        if (!canvas || !ctx || isEmbed) return;
        const resizeCanvas = () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        };
        resizeCanvas();
        window.addEventListener("resize", resizeCanvas, { passive: true });

        petals = Array.from({ length: PETAL_COUNT }, () => createPetal(false));

        let lastTime = performance.now();
        const render = (time) => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            for (let i = 0; i < petals.length; i++) {
                const p = petals[i];
                p.y += p.speedY;
                p.x += Math.sin(p.angle) * p.speedX;
                p.angle += p.spin;
                p.rotX += p.rotSpeed;

                if (p.y > canvas.height + 20) {
                    petals[i] = createPetal(true);
                }

                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.angle);
                ctx.scale(Math.cos(p.rotX), 1);

                if (p.isGold) {
                    ctx.beginPath();
                    ctx.arc(0, 0, p.size, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(255, 224, 130, ${p.opacity * (0.6 + 0.4 * Math.sin(p.angle * 2))})`;
                    ctx.shadowColor = "#D4AF37";
                    ctx.shadowBlur = 6;
                    ctx.fill();
                } else {
                    ctx.beginPath();
                    ctx.moveTo(0, -p.size);
                    ctx.bezierCurveTo(
                        p.size * 0.8,
                        -p.size * 0.6,
                        p.size * 0.8,
                        p.size * 0.6,
                        0,
                        p.size,
                    );
                    ctx.bezierCurveTo(
                        -p.size * 0.8,
                        p.size * 0.6,
                        -p.size * 0.8,
                        -p.size * 0.6,
                        0,
                        -p.size,
                    );
                    const grad = ctx.createLinearGradient(
                        -p.size,
                        -p.size,
                        p.size,
                        p.size,
                    );
                    grad.addColorStop(0, "#A81B32");
                    grad.addColorStop(0.6, "#8A1526");
                    grad.addColorStop(1, "#4A050E");
                    ctx.fillStyle = grad;
                    ctx.shadowColor = "rgba(74, 5, 14, 0.4)";
                    ctx.shadowBlur = 4;
                    ctx.fill();
                }
                ctx.restore();
            }

            if (modal === "intro" || isOpening) {
                petalsAnimationId = requestAnimationFrame(render);
            }
        };
        petalsAnimationId = requestAnimationFrame(render);
    };

    const createPetal = (fromTop = false) => {
        const isGold = Math.random() < 0.25;
        return {
            x: Math.random() * (window.innerWidth || 420),
            y: fromTop ? -20 : Math.random() * (window.innerHeight || 800),
            size: isGold ? 1.5 + Math.random() * 2.5 : 8 + Math.random() * 8,
            speedY: isGold
                ? 0.6 + Math.random() * 1.2
                : 1.2 + Math.random() * 2,
            speedX: 0.6 + Math.random() * 1.2,
            angle: Math.random() * Math.PI * 2,
            spin: (Math.random() - 0.5) * 0.03,
            rotX: Math.random() * Math.PI,
            rotSpeed: 0.02 + Math.random() * 0.03,
            opacity: 0.5 + Math.random() * 0.5,
            isGold,
        };
    };

    const triggerPetalsBurst = () => {
        if (!ctx) return;
        const centerX = (window.innerWidth || 420) / 2;
        const centerY = (window.innerHeight || 800) / 2;
        for (let i = 0; i < 40; i++) {
            const angle = Math.random() * Math.PI * 2;
            const speed = 3 + Math.random() * 6;
            petals.push({
                x: centerX,
                y: centerY,
                size:
                    Math.random() < 0.3
                        ? 2 + Math.random() * 2
                        : 10 + Math.random() * 6,
                speedY: Math.sin(angle) * speed + 1,
                speedX: Math.cos(angle) * speed,
                angle: Math.random() * Math.PI * 2,
                spin: (Math.random() - 0.5) * 0.08,
                rotX: Math.random() * Math.PI,
                rotSpeed: 0.05 + Math.random() * 0.05,
                opacity: 0.9,
                isGold: Math.random() < 0.35,
            });
        }
    };

    const setBackdrop = (active) =>
        dialogBackdrop?.classList.toggle(P + "visible", active);
    const closeModal = () => {
        intro?.classList.remove(P + "modal-active");
        giftDialog?.setAttribute("hidden", "");
        closeDialogButton?.classList.remove(P + "visible");
        setBackdrop(false);
        document.body.style.overflow = "";
        modal = null;
        isOpening = false;
        if (petalsAnimationId) {
            cancelAnimationFrame(petalsAnimationId);
            petalsAnimationId = null;
        }
    };

    /* Chuỗi mở thiệp hoàng gia 3D nhiều cảnh chuyển động lộng lẫy */
    const openWeddingCeremony = () => {
        if (isOpening) return;
        isOpening = true;

        if (audio) {
            audio.play().catch(() => {});
        }

        triggerPetalsBurst();

        const stageEl = document.getElementById("mayhen-door-stage") || one("door_stage") || doorStage;
        if (stageEl) {
            stageEl.classList.add("opened");
        }

        const gate = royalGate || one("royal_gate_wrapper");
        if (gate) {
            gate.classList.add(P + "opening");
        }
        triggerInitialAnimations();

        setTimeout(() => {
            if (stageEl) {
                stageEl.classList.add("mayhen_door_hidden");
            }
            if (gate) {
                gate.classList.add(P + "hidden");
            }
            closeModal();
            document.body.style.overflow = "";
            window.scrollTo({ top: 0, behavior: "smooth" });
        }, 1100);
    };

    /* Kích hoạt tức thì animation cho section đầu tiên ngay khi mở thiệp */
    const triggerInitialAnimations = () => {
        if (!stage) return;
        const firstSection =
            one("section3", stage) ||
            stage.querySelector("." + P + "ladi-section");
        if (!firstSection) return;
        const elements = [
            ...firstSection.querySelectorAll(
                `.${P}ladi-animation, [data-animation-pending]`,
            ),
        ];
        elements.forEach((el, index) => {
            setTimeout(() => {
                el.classList.add(P + "ladi-animation");
                delete el.dataset.animationPending;
            }, index * 50);
        });
    };

    const bindOpeningTriggers = () => {
        const stageEl = document.getElementById("mayhen-door-stage") || one("door_stage") || doorStage;
        const card = document.getElementById("mayhen-door-center") || one("door_center") || one("center_card");
        const seal = document.getElementById("mayhen-wax-seal") || one("wax_seal_wrap");
        const btn = one("open_hint") || one("open_btn");
        const legacyBtn = one("button14");

        const handleOpen = (e) => {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            openWeddingCeremony();
        };

        [stageEl, card, seal, btn, legacyBtn].filter(Boolean).forEach((el) => {
            el.style.cursor = "pointer";
            el.addEventListener("click", handleOpen);
            el.addEventListener("touchend", handleOpen, { passive: false });
        });
    };

    const openIntro = () => {
        if (isEmbed) {
            const stageEl = document.getElementById("mayhen-door-stage") || one("door_stage") || doorStage;
            if (stageEl) {
                stageEl.classList.add("opened", "mayhen_door_hidden");
            }
            triggerInitialAnimations();
            return;
        }
        const stageEl = document.getElementById("mayhen-door-stage") || one("door_stage") || doorStage;
        if (stageEl) {
            stageEl.classList.remove("opened", "mayhen_door_hidden");
            document.body.style.overflow = "hidden";
            initPetals();
            bindOpeningTriggers();
            return;
        }
        if (!intro) return;
        closeModal();
        intro.classList.add(P + "modal-active");
        setBackdrop(true);
        document.body.style.overflow = "hidden";
        modal = "intro";
        isOpening = false;
        const gate = royalGate || one("royal_gate_wrapper");
        gate?.classList.remove(P + "hidden", P + "opening");
        initPetals();
        bindOpeningTriggers();
    };

    const openGift = () => {
        closeModal();
        if (!giftDialog) return;
        giftDialog.removeAttribute("hidden");
        setBackdrop(true);
        closeDialogButton?.classList.add(P + "visible");
        document.body.style.overflow = "hidden";
        modal = "gift";
        updateGift("bride");
    };

    const updateGift = (person) => {
        all("gift-tab").forEach((button) =>
            button.classList.toggle(
                P + "selected",
                button.dataset.person === person,
            ),
        );
        const bank = (C.bank && C.bank[person]) || {};
        const details = one("gift-details");
        const note = one("gift-note");
        const configured = Boolean(bank.holder && bank.account && bank.name);
        if (details) {
            details.hidden = !configured;
            details.textContent = configured
                ? `${bank.name}\n${bank.holder}\n${bank.account}`
                : "";
        }
        if (note) note.hidden = configured;
    };

    const initApp = () => {
        syncStage();
        openIntro();
        if (isEmbed) {
            document.body.classList.remove(P + "locked");
            document.documentElement.classList.remove(P + "locked");
            triggerInitialAnimations();
        }
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initApp, { once: true });
    } else {
        initApp();
    }
    window.addEventListener("load", syncStage, { passive: true });

    const params = new URLSearchParams(location.search);
    const guest = (
        params.get("to") ||
        params.get("guest") ||
        C.guestDefault ||
        "Bạn và Gia Đình"
    )
        .trim()
        .slice(0, 90);
    for (const el of all("class_tenkhachmoi")) {
        const target = el.querySelector("span") || el.querySelector("h3");
        if (target) target.textContent = guest;
    }
    const cardNames = one("card_names");
    if (cardNames && C.groom && C.bride && !cardNames.querySelector('[data-sync-target="groom_name"]')) {
        cardNames.innerHTML = `<span data-sync-target="groom_name">${C.groom}</span> <span>&amp;</span> <span data-sync-target="bride_name">${C.bride}</span>`;
    }

    const fitCoupleNames = () => {
        const brideEl = document.querySelector('.mayhen_invitation_jeowinids483fr_headline110 .mayhen_invitation_jeowinids483fr_ladi-headline');
        const groomEl = document.querySelector('.mayhen_invitation_jeowinids483fr_headline111 .mayhen_invitation_jeowinids483fr_ladi-headline');
        [brideEl, groomEl].forEach(el => {
            if (!el) return;
            const text = (el.textContent || '').trim();
            const len = text.length;
            if (len >= 22) {
                el.style.setProperty('font-size', '34px', 'important');
            } else if (len >= 17) {
                el.style.setProperty('font-size', '38px', 'important');
            } else if (len >= 12) {
                el.style.setProperty('font-size', '44px', 'important');
            } else if (len >= 8) {
                el.style.setProperty('font-size', '48px', 'important');
            } else {
                el.style.setProperty('font-size', '52px', 'important');
            }
        });
    };
    fitCoupleNames();
    window.addEventListener('message', (e) => {
        if (e.data && e.data.type === 'MAYHEN_SYNC_INPUT') {
            setTimeout(fitCoupleNames, 30);
        }
    });

    const audio = C.music ? new Audio(C.music) : null;
    if (audio) {
        audio.loop = true;
        audio.preload = "none";
    }
    const toggleMusic = async () => {
        if (!audio) return showMessage("Chưa cấu hình bài hát nền.");
        if (audio.paused) {
            try {
                await audio.play();
                showMessage("Đã bật nhạc nền.");
            } catch {
                showMessage("Trình duyệt chặn tự động phát nhạc.");
            }
        } else {
            audio.pause();
            showMessage("Đã tắt nhạc nền.");
        }
    };

    const startDate = new Date(C.weddingDate || "2026-03-21T10:00:00+07:00");
    const countdown = one("countdown4");
    const cells = countdown
        ? [...countdown.querySelectorAll("." + P + "ladi-countdown-text")]
        : [];
    const remaining = () => {
        const left = Math.max(0, startDate.getTime() - Date.now());
        const values = [
            Math.floor(left / 86400000),
            Math.floor(left / 3600000) % 24,
            Math.floor(left / 60000) % 60,
            Math.floor(left / 1000) % 60,
        ];
        if (cells.length >= 4)
            cells
                .slice(0, 4)
                .forEach((cell, i) =>
                    cell.replaceChildren(
                        Object.assign(document.createElement("span"), {
                            textContent: String(values[i]).padStart(2, "0"),
                        }),
                    ),
                );
    };
    remaining();
    setInterval(remaining, 1000);

    // Đón đầu cuộn trang: Lướt tới đâu là animation kích hoạt mượt mà tới đó, không trễ nhịp
    const animated = [
        ...(stage
            ? stage.querySelectorAll(
                  `.${P}ladi-animation, .${P}ladi-animation-hidden`,
              )
            : []),
    ];
    animated.forEach((el) => {
        el.dataset.animationPending = "true";
        el.classList.remove(P + "ladi-animation-hidden", P + "ladi-animation");
    });
    if ("IntersectionObserver" in window) {
        const io = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (!entry.isIntersecting) continue;
                    entry.target.classList.add(P + "ladi-animation");
                    delete entry.target.dataset.animationPending;
                    io.unobserve(entry.target);
                }
            },
            { rootMargin: "80px 0px 80px 0px", threshold: 0.01 },
        );
        animated.forEach((el) => io.observe(el));
    } else animated.forEach((el) => el.classList.add(P + "ladi-animation"));

    const goTo = (sectionName) => {
        closeModal();
        const target = one(sectionName);
        if (!target) return;
        const scale = Math.min(1, spacer.clientWidth / 420);
        let offset = 0;
        for (const element of stage.children) {
            if (element === target) break;
            offset += Number.parseFloat(getComputedStyle(element).height) || 0;
        }
        const scrollTarget =
            spacer.getBoundingClientRect().top +
            window.scrollY +
            offset * scale;
        window.scrollTo({
            top: scrollTarget,
            behavior: matchMedia("(prefers-reduced-motion: reduce)").matches
                ? "auto"
                : "smooth",
        });
    };

    const galleryElements = [
        ...(stage ? stage.querySelectorAll("[data-mayhen-gallery]") : []),
    ];
    const photos = galleryElements.map((el) => {
        const layer = one("ladi-image-background", el);
        const bg = layer ? getComputedStyle(layer).backgroundImage : "none";
        const match = bg.match(/url\(["']?(.*?)["']?\)/);
        return match ? match[1] : "";
    });
    let photoIndex = 0;
    const box = one("lightbox");
    const photo = one("lightbox-photo");
    const navigatePhoto = (delta) => {
        if (!photos.length) return;
        for (let attempt = 0; attempt < photos.length; attempt++) {
            photoIndex = (photoIndex + delta + photos.length) % photos.length;
            if (photos[photoIndex]) break;
        }
        if (photo) photo.src = photos[photoIndex] || "";
    };
    const showPhoto = (i) => {
        photoIndex = i;
        if (!photos[i]) return showMessage("Ảnh chưa được cấu hình.");
        photo.src = photos[i];
        box?.classList.add(P + "visible");
        document.body.style.overflow = "hidden";
    };
    const closePhoto = () => {
        box?.classList.remove(P + "visible");
        document.body.style.overflow = "";
    };
    let swipeStart = null;
    box?.addEventListener(
        "touchstart",
        (event) => {
            swipeStart = event.changedTouches[0]?.screenX || 0;
        },
        { passive: true },
    );
    box?.addEventListener(
        "touchend",
        (event) => {
            if (swipeStart == null) return;
            const delta = (event.changedTouches[0]?.screenX || 0) - swipeStart;
            if (Math.abs(delta) > 45) navigatePhoto(delta > 0 ? -1 : 1);
            swipeStart = null;
        },
        { passive: true },
    );

    const form = one("form3")?.querySelector("form");
    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "";
    form?.addEventListener("submit", async (event) => {
        event.preventDefault();
        const name = (form.elements.namedItem("name")?.value || "").trim();
        const status = form.elements.namedItem("form_item6")?.value || "";
        if (!name || !status)
            return showMessage(
                "Vui lòng nhập tên và xác nhận khả năng tham dự.",
            );
        const recipient = [
            ...form.querySelectorAll('input[name="form_item13"]:checked'),
        ].map((input) => input.value);
        const message = (
            form.elements.namedItem("message")?.value || ""
        ).trim();

        const submitUrl = C.rsvpSubmitUrl;
        if (submitUrl) {
            try {
                const res = await fetch(submitUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        name,
                        attendance: status,
                        guest_count: 1,
                        recipient,
                        message,
                    }),
                });
                const result = await res.json().catch(() => ({}));
                if (res.ok) {
                    form.reset();
                    return showMessage(
                        result.message ||
                            "Cảm ơn bạn đã phản hồi xác nhận tham dự!",
                    );
                }
            } catch (e) {
                // Fallback
            }
        }
        form.reset();
        showMessage("Đã ghi nhận thông tin phản hồi tham dự của bạn!");
    });

    document.addEventListener("click", (event) => {
        const doorStageEl = document.getElementById("mayhen-door-stage") || one("door_stage");
        if (doorStageEl && !doorStageEl.classList.contains("opened") && !doorStageEl.classList.contains("mayhen_door_hidden")) {
            const openTrigger = event.target.closest(
                "#mayhen-door-stage, #mayhen-wax-seal, #mayhen-door-center, ." +
                    P +
                    "door_stage, ." +
                    P +
                    "door_center, ." +
                    P +
                    "wax_seal_wrap, ." +
                    P +
                    "open_hint, ." +
                    P +
                    "open_btn, ." +
                    P +
                    "center_card, ." +
                    P +
                    "button14",
            );
            if (openTrigger) {
                event.preventDefault();
                event.stopPropagation();
                openWeddingCeremony();
                return;
            }
        }

        if (
            modal === "intro" ||
            intro?.classList.contains(P + "modal-active")
        ) {
            const openTrigger = event.target.closest(
                "." +
                    P +
                    "button14, ." +
                    P +
                    "wax_seal_wrap, ." +
                    P +
                    "open_btn, ." +
                    P +
                    "center_card",
            );
            if (openTrigger) {
                event.preventDefault();
                openWeddingCeremony();
                return;
            }
        }

        const node = event.target.closest(
            "[data-go], [data-menu], [data-person], [data-mayhen-gallery], button, a, ." +
                P +
                "ladi-element, ." +
                P +
                "modal-close, ." +
                P +
                "gift-close, ." +
                P +
                "popup-close",
        );
        if (!node) return;
        const go = node.closest("[data-go]");
        if (go) {
            const menuBtns = document.querySelectorAll("." + P + "cta-menu button[data-go]");
            menuBtns.forEach(b => b.classList.remove("mayhen_active"));
            go.classList.add("mayhen_active");
            return goTo(go.dataset.go);
        }
        const gift = node.closest('[data-menu="gift"]');
        if (gift) return openGift();
        const person = node.closest("[data-person]");
        if (person) return updateGift(person.dataset.person);
        const pic = node.closest("[data-mayhen-gallery]");
        if (pic) return showPhoto(galleryElements.indexOf(pic));
        if (
            node.closest(
                "#mayhen-wax-seal, #mayhen-door-center, ." +
                    P +
                    "door_stage, ." +
                    P +
                    "door_center, ." +
                    P +
                    "wax_seal_wrap, ." +
                    P +
                    "open_hint, ." +
                    P +
                    "open_btn, ." +
                    P +
                    "button14",
            )
        ) {
            openWeddingCeremony();
            return;
        }
        if (
            node.closest(
                "." +
                    P +
                    "modal-close, ." +
                    P +
                    "gift-close, ." +
                    P +
                    "popup-close",
            )
        )
            return closeModal();
        if (node.closest("." + P + "lightbox-close")) return closePhoto();
        if (node.closest("." + P + "lightbox-prev")) return navigatePhoto(-1);
        if (node.closest("." + P + "lightbox-next")) return navigatePhoto(1);
        if (node.closest("." + P + "button7")) {
            event.preventDefault();
            return form?.requestSubmit();
        }
        if (node.closest("." + P + "button23")) {
            const url =
                C.mapUrl ||
                "https://www.google.com/maps/search/?api=1&query=" +
                    encodeURIComponent(C.address || "Đồng Tháp, Việt Nam");
            window.open(url, "_blank", "noopener,noreferrer");
        }
        if (node.closest("." + P + "shape10, ." + P + "shape11"))
            return toggleMusic();
    });
    dialogBackdrop?.addEventListener("click", closeModal);
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeModal();
            closePhoto();
        }
        if (box?.classList.contains(P + "visible")) {
            if (event.key === "ArrowLeft") navigatePhoto(-1);
            if (event.key === "ArrowRight") navigatePhoto(1);
        }
        const actionButton = event.target.closest(
            "." +
                P +
                "button7, ." +
                P +
                "button23, ." +
                P +
                "button14, ." +
                P +
                "wax_seal_wrap, ." +
                P +
                "open_btn",
        );
        if (actionButton && (event.key === "Enter" || event.key === " ")) {
            event.preventDefault();
            actionButton.click();
        }
        const photoNode = event.target.closest("[data-mayhen-gallery]");
        if (photoNode && (event.key === "Enter" || event.key === " ")) {
            event.preventDefault();
            showPhoto(galleryElements.indexOf(photoNode));
        }
    });
})();
